"""
##############################################################################################
# divumwx_boltek.py version 1.2.0
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################

Boltek lightning detector support for DivumWX.

Boltek's NexStorm writes ngxarchive.json, its native record of recent
strikes, and keeps rewriting it while it runs:

    {"TimestampEpoch": 1792751132017,          when written (Unix ms)
     "StationLat": 51.00, "StationLon": -3.11,
     "SoftwareVersion": "1.9.6.8317", "DetectorModel": "LD-350",
     "AntennaAlignment": 0,
     "StrikeCount": 4623,                       a NexStorm total, NOT used (see below)
     "StormCount": 0,
     "StrikeData": [                            one entry per strike, recent ones
                                                (a rolling window, ~2 hours)
        {"time": 35105,                         seconds after local midnight
         "millis": 1792745105000,               strike time (Unix ms)
         "dist": 617,                           km
         "bng": 141.1,                          bearing, degrees true
         "lat": 46.566, "lon": 1.939,           strike position
         "type": 1, "polarity": 1,              1 = cloud-to-ground; 1 = +, 0 = -
         "compoundType": 2, "correlated": false, "NMEAChecksum": 255}, ...],
     "StormData": []}

Distances and bearings are measured from the station position in weewx.conf
([Station] latitude/longitude), worked out from each strike's lat/lon, so
they're right even if NexStorm's StationLat/Lon differ (NexStorm's own
dist/bng are used only for a strike without a position, or if weewx.conf
has no position). boltek.json's station_lat/lon are weewx.conf's.

(dist and bng agree with the strike's lat/lon to within 1 km and 0.1 deg.
type/polarity are read as above; compoundType, correlated and NMEAChecksum
aren't identified yet and are kept but not used.)

BoltekLightningService reads it every poll_interval seconds and:

  * counts each listed strike once, at its own time, in DivumWX's own Boltek
    database (a separate SQLite file, divumwx_boltek.sdb, created
    automatically). Strikes that land in an interval already saved (a
    strike read just after an interval closed, the first run, or after
    WeeWX was stopped) are added to that interval's record, so the hourly
    chart shows them when they happened;
  * ignores StrikeCount: it doesn't follow the listed strikes (it stayed
    the same while strikes came and went from the list), so it can't be
    used to count them. It's passed on in boltek.json for information only;
  * works out the strike rate, average distance over the last hour,
    approaching/receding and today's peak rate from the strikes' times and
    distances;
  * writes jsondata/boltek.json for the dashboard's Boltek Lightning card
    (including the latest strikes, for maps), and jsondata/boltek_history.json
    (hourly and daily totals) for its chart & records modal.

It runs on its own background thread, so a slow or missing file never
holds up WeeWX's loop. If the file isn't there yet the service just waits
quietly (one log line), so it's safe on stations without a Boltek.

weewx.conf (written by install.py on first install; every key optional):

    [BoltekLightning]
        # Where NexStorm puts ngxarchive.json. Relative paths are taken
        # from the DivumWX site folder; absolute paths are used as-is.
        ngxarchive_file = ngxarchive.json
        # Strikes this close (km) count as close strikes.
        close_distance = 16
        # Seconds between reads of the file.
        poll_interval = 10
        # Seconds of strikes gathered into each database record.
        archive_interval = 300
        # A file older than this many seconds counts as stale.
        stale_after = 600
        # DivumWX site folder below HTML_ROOT ('' if HTML_ROOT is it).
        site_root = divumwx
        # Output for the card, relative to the site folder.
        json_file = jsondata/boltek.json
        history_file = jsondata/boltek_history.json
        binding = boltek_binding

    [DataBindings]
        [[boltek_binding]]
            database = boltek_sqlite
            table_name = archive
            manager = weewx.manager.Manager
            schema = user.divumwx_boltek.schema

    [Databases]
        [[boltek_sqlite]]
            database_name = divumwx_boltek.sdb
            database_type = SQLite
"""

import json
import logging
import math
import os
import threading
import time

import weewx
import weewx.manager
from weewx.engine import StdService
from weeutil.weeutil import to_int

log = logging.getLogger(__name__)

BOLTEK_VERSION = '1.2.0'

# One record per archive_interval (dateTime is the END of the interval).
# 'strikes', 'cg', 'ic', 'close' and 'noise' are counts during the interval;
# 'max_rate' is the highest strikes-per-minute seen and 'min_distance' the
# closest strike. The rest are the readings at the end of the interval.
# session_total keeps NexStorm's StrikeCount for reference only.
# (Same layout as 1.0.0, so existing databases carry on as they are.)
schema = [
    ('dateTime', 'INTEGER NOT NULL UNIQUE PRIMARY KEY'),
    ('usUnits', 'INTEGER NOT NULL'),
    ('interval', 'INTEGER NOT NULL'),
    ('strikes', 'INTEGER'),
    ('cg', 'INTEGER'),
    ('ic', 'INTEGER'),
    ('close', 'INTEGER'),
    ('noise', 'INTEGER'),
    ('strike_rate', 'REAL'),
    ('max_rate', 'REAL'),
    ('min_distance', 'REAL'),
    ('peak_rate', 'REAL'),
    ('last_distance', 'REAL'),
    ('last_bearing', 'REAL'),
    ('last_type', 'INTEGER'),
    ('last_strike', 'INTEGER'),
    ('avg_distance', 'REAL'),
    ('tendency', 'INTEGER'),
    ('alarm', 'INTEGER'),
    ('session_total', 'INTEGER'),
    ('session_cg', 'INTEGER'),
    ('session_ic', 'INTEGER'),
    ('session_close', 'INTEGER'),
    ('session_noise', 'INTEGER'),
]

CG_TYPE = 1                 # StrikeData 'type' for cloud-to-ground
RECENT_IN_JSON = 100        # latest strikes passed on in boltek.json
TENDENCY = {0: 'Steady', 1: 'Approaching', 2: 'Receding'}


def _num(v, cast=float):
    try:
        return cast(float(v))
    except (TypeError, ValueError):
        return None


def type_label(stype, polarity):
    """'+CG' / '-CG' for cloud-to-ground strikes; 'Type n' otherwise."""
    if stype is None:
        return None
    sign = '+' if polarity == 1 else '-' if polarity == 0 else ''
    return sign + 'CG' if stype == CG_TYPE else 'Type %d' % stype


def distance_bearing(lat1, lon1, lat2, lon2):
    """Great-circle distance (km) and initial bearing (degrees true) from
    point 1 to point 2."""
    p1, p2 = math.radians(lat1), math.radians(lat2)
    dl = math.radians(lon2 - lon1)
    a = math.sin((p2 - p1) / 2) ** 2 + math.cos(p1) * math.cos(p2) * math.sin(dl / 2) ** 2
    km = 2 * 6371.0088 * math.asin(min(1.0, math.sqrt(a)))
    y = math.sin(dl) * math.cos(p2)
    x = math.cos(p1) * math.sin(p2) - math.sin(p1) * math.cos(p2) * math.cos(dl)
    return km, (math.degrees(math.atan2(y, x)) + 360) % 360


def station_position(config_dict):
    """(lat, lon) from weewx.conf [Station], or None."""
    try:
        st = config_dict.get('Station', {})
        lat, lon = float(st['latitude']), float(st['longitude'])
    except (KeyError, TypeError, ValueError, AttributeError):
        return None
    return (lat, lon) if -90 <= lat <= 90 and -180 <= lon <= 180 else None


def parse_strike(entry, station=None):
    """One StrikeData entry -> dict, or None if it has no usable time.
    With station (lat, lon), distance and bearing are measured from it,
    using the strike's own lat/lon; otherwise NexStorm's dist/bng."""
    if not isinstance(entry, dict):
        return None
    ms = _num(entry.get('millis'), int)
    if not ms:
        return None
    stype = _num(entry.get('type'), int)
    pol = _num(entry.get('polarity'), int)
    lat, lon = _num(entry.get('lat')), _num(entry.get('lon'))
    km = _num(entry.get('dist'))
    bng = _num(entry.get('bng'))
    if station and lat is not None and lon is not None:
        km, bng = distance_bearing(station[0], station[1], lat, lon)
    return {
        'ms': ms,
        't': ms // 1000,
        'km': None if km is None else round(km, 1),
        'bearing': None if bng is None else round(bng % 360, 1),
        'lat': lat, 'lon': lon,
        'type': stype, 'polarity': pol,
        'label': type_label(stype, pol),
        # A strike's identity: its time plus position (two strikes in the
        # same millisecond would be at different places).
        'key': '%d/%s/%s' % (ms, None if lat is None else round(lat, 4), None if lon is None else round(lon, 4)),
    }


def parse_ngxarchive(text, station=None):
    """ngxarchive.json's contents -> dict. Raises ValueError if it can't be
    read. station: (lat, lon) from weewx.conf to measure strikes from."""
    try:
        doc = json.loads(text.lstrip('﻿'))
    except ValueError as e:
        raise ValueError('not valid JSON (%s)' % e)
    if not isinstance(doc, dict) or 'StrikeData' not in doc:
        raise ValueError("not NexStorm's ngxarchive.json (no StrikeData)")
    raw = doc.get('StrikeData') if isinstance(doc.get('StrikeData'), list) else []
    strikes = sorted((s for s in (parse_strike(e, station) for e in raw) if s), key=lambda s: s['ms'])
    ts = _num(doc.get('TimestampEpoch'))
    return {
        'file_time': None if not ts else int(ts / 1000.0 if ts > 1e11 else ts),
        'strike_count': _num(doc.get('StrikeCount'), int),
        'storm_count': _num(doc.get('StormCount'), int),
        'storms': doc.get('StormData') if isinstance(doc.get('StormData'), list) else [],
        'strikes': strikes,
        'detector_model': doc.get('DetectorModel') or None,
        'software_version': doc.get('SoftwareVersion') or None,
        'antenna_alignment': _num(doc.get('AntennaAlignment')),
        # weewx.conf's position (what distances are measured from), or
        # NexStorm's if weewx.conf has none; NexStorm's is kept as well.
        'station_lat': station[0] if station else _num(doc.get('StationLat')),
        'station_lon': station[1] if station else _num(doc.get('StationLon')),
        'nexstorm_lat': _num(doc.get('StationLat')),
        'nexstorm_lon': _num(doc.get('StationLon')),
        'station_source': 'weewx.conf' if station else 'NexStorm',
    }


class BoltekLightningService(StdService):

    def __init__(self, engine, config_dict):
        super(BoltekLightningService, self).__init__(engine, config_dict)
        bc = config_dict.get('BoltekLightning', {})

        html_root = config_dict.get('StdReport', {}).get('HTML_ROOT', 'public_html')
        weewx_root = config_dict.get('WEEWX_ROOT', '.')
        if not os.path.isabs(html_root):
            html_root = os.path.join(weewx_root, html_root)
        site_root = str(bc.get('site_root', 'divumwx')).strip()
        site = os.path.join(html_root, site_root) if site_root else html_root

        self.ngxarchive_file = os.path.join(site, bc.get('ngxarchive_file', 'ngxarchive.json'))
        self.json_file = os.path.join(site, bc.get('json_file', 'jsondata/boltek.json'))
        self.history_file = os.path.join(site, bc.get('history_file', 'jsondata/boltek_history.json'))
        self.close_km = float(bc.get('close_distance', 16))
        # Strikes are measured from weewx.conf's station position.
        self.station = station_position(config_dict)
        self.poll_interval = max(2, to_int(bc.get('poll_interval', 10)))
        self.archive_interval = max(60, to_int(bc.get('archive_interval', 300)))
        self.stale_after = max(60, to_int(bc.get('stale_after', 600)))
        self.binding = bc.get('binding', 'boltek_binding')
        self.config_dict = config_dict

        self._stop = threading.Event()
        self._thread = threading.Thread(target=self._run, name='BoltekLightning', daemon=True)
        self._thread.start()
        log.info("Boltek lightning %s: reading %s every %ss, writing %s, database binding %s, "
                 "distances from %s"
                 % (BOLTEK_VERSION, self.ngxarchive_file, self.poll_interval, self.json_file, self.binding,
                    "weewx.conf's station position %.4f, %.4f" % self.station if self.station
                    else "NexStorm's station position (no position in weewx.conf [Station])"))

    def shutDown(self):
        self._stop.set()
        if self._thread.is_alive():
            self._thread.join(timeout=10)

    def _run(self):
        try:
            # initialize=True creates the database and its table the first time.
            dbm = weewx.manager.open_manager_with_config(self.config_dict, self.binding, initialize=True)
        except Exception as e:
            log.error("Boltek lightning: can't open database binding %s: %s" % (self.binding, e))
            return
        try:
            BoltekRecorder(self, dbm).run(self._stop)
        except Exception as e:  # never take WeeWX down with us
            log.exception("Boltek lightning stopped: %s" % e)
        finally:
            try:
                dbm.close()
            except Exception:
                pass


class BoltekRecorder(object):
    """Does the reading, recording and publishing. Kept apart from the
    service so it can be driven directly in tests."""

    COUNTS = ('strikes', 'cg', 'ic', 'close')

    def __init__(self, cfg, dbm, clock=time.time):
        self.cfg = cfg
        self.dbm = dbm
        self.clock = clock
        self.reading = None         # the last good read of the file
        self.last_mtime = None
        self.missing_logged = False
        self.error_logged = None
        self.read_error = None      # why the last read failed, for boltek.json
        self.strike_count = None    # NexStorm's StrikeCount (for reference only)
        self.baseline_s = 0         # strikes at or before this were saved by an earlier run
        self.counted = {}           # key -> time, of strikes counted this run
        self.recent = {}            # key -> (time, km), last hour, for rate/average/tendency
        self.last_strike = {}       # newest listed strike
        self.session = {'listed': 0}
        self.peak = {'day': None, 'rate': None, 'time': None}
        self.totals = {'today': 0, 'year': 0, 'alltime': 0}
        self.totals_day = None
        self.interval_start = self._floor(self.clock())
        self._new_pending()
        self._resume()
        self._refresh_totals()
        self._write_history()

    # ---- intervals ----

    def _floor(self, ts):
        ai = self.cfg.archive_interval
        return int(ts // ai * ai)

    def _end_for(self, t):
        """End of the interval a strike at time t belongs to; a record at
        dateTime E covers (E - archive_interval, E]."""
        ai = self.cfg.archive_interval
        return int(-(-int(t) // ai) * ai)

    def _new_pending(self):
        self.pending = dict((c, 0) for c in self.COUNTS)
        self.pending_min_km = None
        self.pending_max_rate = None

    def _resume(self):
        """Carry on from the database: the newest strike already saved, so
        strikes still listed in the file aren't counted twice."""
        try:
            row = self.dbm.getSql("SELECT MAX(last_strike) FROM archive WHERE last_strike <= ?",
                                  (int(self.clock()) + 300,))
            if row and row[0]:
                self.baseline_s = int(row[0])
            row = self.dbm.getSql("SELECT last_strike, last_distance, last_bearing, last_type FROM archive "
                                  "WHERE last_strike IS NOT NULL ORDER BY last_strike DESC LIMIT 1")
            if row:
                self.last_strike = {'t': row[0], 'km': row[1], 'bearing': row[2], 'type': row[3],
                                    'label': type_label(row[3], None)}
        except Exception as e:
            log.debug("Boltek lightning: nothing to resume from (%s)" % e)

    # ---- main loop ----

    def run(self, stop):
        while not stop.is_set():
            self.tick()
            stop.wait(self.cfg.poll_interval)
        # Shutting down: save what's been counted so far.
        self._write_record(self.clock(), exact=True)

    def tick(self):
        now = self.clock()
        # Close the finished interval first, so strikes read after a
        # boundary go in the right record (and day).
        if now >= self.interval_start + self.cfg.archive_interval:
            self._write_record(now)
        reading = self._read()
        if reading is not None:
            self._count(reading, now)
            self.reading = reading
        self._publish(now)

    def _count(self, reading, now):
        # New = not counted yet, newer than what's saved, and not in the
        # future (a strike time ahead of this clock by more than 5 minutes
        # means a clock is wrong: it would block later strikes).
        new = [s for s in reading['strikes']
               if s['key'] not in self.counted and self.baseline_s < s['t'] <= now + 300]
        for s in new:
            self.counted[s['key']] = s['t']
        self.strike_count = reading['strike_count']

        past = {}
        current_end = self.interval_start + self.cfg.archive_interval
        for s in new:
            close = s['km'] is not None and s['km'] <= self.cfg.close_km
            counts = {'strikes': 1, 'cg': 1 if s['type'] == CG_TYPE else 0, 'ic': 0, 'close': 1 if close else 0}
            end = self._end_for(s['t'])
            if end >= current_end:
                for c in self.COUNTS:
                    self.pending[c] += counts[c]
                if s['km'] is not None and (self.pending_min_km is None or s['km'] < self.pending_min_km):
                    self.pending_min_km = s['km']
            else:
                b = past.setdefault(end, {'strikes': 0, 'cg': 0, 'ic': 0, 'close': 0, 'min_km': None, 'last': s})
                for c in self.COUNTS:
                    b[c] += counts[c]
                if s['km'] is not None and (b['min_km'] is None or s['km'] < b['min_km']):
                    b['min_km'] = s['km']
                b['last'] = s
            if s['t'] > now - 3600:
                self.recent[s['key']] = (s['t'], s['km'])
        if past:
            self._add_to_saved(past)
        self.session['listed'] += len(new)

        listed = [s for s in reading['strikes'] if s['t'] <= now + 300]
        if listed:
            newest = listed[-1]
            if not self.last_strike or newest['t'] >= (self.last_strike.get('t') or 0):
                self.last_strike = newest
        # Forget what can no longer matter.
        self.recent = dict((k, v) for k, v in self.recent.items() if v[0] > now - 3600)
        oldest = min([s['t'] for s in reading['strikes']] or [now])
        self.counted = dict((k, t) for k, t in self.counted.items() if t >= oldest - 1)

        rate = self._rate(now)
        if rate is not None and (self.pending_max_rate is None or rate > self.pending_max_rate):
            self.pending_max_rate = rate
        day = time.localtime(now)[:3]
        if self.peak['day'] != day:
            self.peak = {'day': day, 'rate': None, 'time': None}
        if rate and (self.peak['rate'] is None or rate > self.peak['rate']):
            self.peak.update(rate=rate, time=int(now))
        if new:
            log.debug("Boltek lightning: %d new strike(s)" % len(new))

    def _add_to_saved(self, past):
        """Add strikes whose interval has already been saved (or was never
        saved: the first run, or while WeeWX was stopped) to that interval's
        record."""
        for end, b in sorted(past.items()):
            last = b['last']
            try:
                row = self.dbm.getSql("SELECT dateTime FROM archive WHERE dateTime = ?", (end,))
                if row:
                    # The last_* columns also say which strikes are saved
                    # (see _resume), so they follow the newest strike.
                    self._execute(
                        "UPDATE archive SET strikes = COALESCE(strikes, 0) + ?, cg = COALESCE(cg, 0) + ?, "
                        "ic = COALESCE(ic, 0) + ?, close = COALESCE(close, 0) + ?, "
                        "min_distance = CASE WHEN min_distance IS NULL OR min_distance > ? THEN ? "
                        "ELSE min_distance END WHERE dateTime = ?",
                        (b['strikes'], b['cg'], b['ic'], b['close'], b['min_km'], b['min_km'], end))
                    self._execute(
                        "UPDATE archive SET last_strike = ?, last_distance = ?, last_bearing = ?, last_type = ? "
                        "WHERE dateTime = ? AND (last_strike IS NULL OR last_strike < ?)",
                        (last['t'], last['km'], last['bearing'], last['type'], end, last['t']))
                else:
                    self.dbm.addRecord({
                        'dateTime': end, 'usUnits': weewx.METRIC,
                        'interval': self.cfg.archive_interval // 60,
                        'strikes': b['strikes'], 'cg': b['cg'], 'ic': b['ic'], 'close': b['close'], 'noise': 0,
                        'min_distance': b['min_km'],
                        'last_distance': last['km'], 'last_bearing': last['bearing'],
                        'last_type': last['type'], 'last_strike': last['t'],
                    })
            except Exception as e:
                log.error("Boltek lightning: couldn't add earlier strikes to the record at %s: %s"
                          % (time.strftime('%Y-%m-%d %H:%M', time.localtime(end)), e))
        log.info("Boltek lightning: added %d strike(s) to %d earlier record(s)"
                 % (sum(b['strikes'] for b in past.values()), len(past)))
        self._refresh_totals()
        self._write_history()

    def _execute(self, sql, args):
        conn = getattr(self.dbm, 'connection', None)
        if conn is None:            # test double
            self.dbm.execute(sql, args)
            return
        import weedb
        with weedb.Transaction(conn) as cursor:
            cursor.execute(sql, args)

    # ---- worked-out readings ----

    def _rate(self, now):
        """Strikes per minute: strikes in the last 60 seconds."""
        if self.reading is None and not self.recent:
            return None
        return sum(1 for t, _km in self.recent.values() if now - 60 < t <= now + 5)

    def _avg_km(self, now):
        kms = [km for t, km in self.recent.values() if km is not None and t > now - 3600]
        return round(sum(kms) / len(kms), 1) if kms else None

    def _tendency(self, now):
        """Approaching (1) / receding (2) / steady (0): average distance of
        the last 15 minutes' strikes against the 15 before (3+ each)."""
        a = [km for t, km in self.recent.values() if km is not None and t > now - 900]
        b = [km for t, km in self.recent.values() if km is not None and now - 1800 < t <= now - 900]
        if len(a) < 3 or len(b) < 3:
            return None
        ma, mb = sum(a) / len(a), sum(b) / len(b)
        return 1 if ma < mb * 0.9 else 2 if ma > mb * 1.1 else 0

    # ---- the file ----

    def _read(self):
        path = self.cfg.ngxarchive_file
        try:
            st = os.stat(path)
        except OSError as e:
            self.read_error = "can't open %s (%s)" % (path, e.strerror or e)
            if not self.missing_logged:
                log.info("Boltek lightning: waiting for %s (NexStorm's ngxarchive.json)" % path)
                self.missing_logged = True
            return None
        if self.missing_logged:
            log.info("Boltek lightning: found %s" % path)
            self.missing_logged = False
        if st.st_mtime == self.last_mtime:
            return None
        try:
            with open(path, 'r', encoding='utf-8', errors='replace') as fh:
                reading = parse_ngxarchive(fh.read(), getattr(self.cfg, 'station', None))
        except (OSError, ValueError) as e:
            # NexStorm may be part-way through rewriting it: try again next time.
            msg = str(e)
            self.read_error = "can't read %s: %s" % (os.path.basename(path), msg)
            if msg != self.error_logged:
                log.error("Boltek lightning: can't read %s: %s" % (path, msg))
                self.error_logged = msg
            return None
        self.error_logged = None
        self.read_error = None
        self.last_mtime = st.st_mtime
        return reading

    # ---- the database ----

    def _write_record(self, now, exact=False):
        end = int(now) if exact else self._floor(now)
        if end <= self.interval_start:
            return
        if exact and not any(self.pending.values()):
            return
        ls = self.last_strike or {}
        rec = {
            'dateTime': end,
            'usUnits': weewx.METRIC,
            'interval': max(1, (end - self.interval_start) // 60),
            'noise': 0,
            'strike_rate': self._rate(now),
            'max_rate': self.pending_max_rate,
            'min_distance': self.pending_min_km,
            'peak_rate': self.peak['rate'],
            'last_distance': ls.get('km'),
            'last_bearing': ls.get('bearing'),
            'last_type': ls.get('type'),
            'last_strike': ls.get('t'),
            'avg_distance': self._avg_km(now),
            'tendency': self._tendency(now),
            'session_total': self.strike_count,
        }
        for c in self.COUNTS:
            rec[c] = self.pending[c]
        try:
            self.dbm.addRecord(rec)
        except Exception as e:
            log.error("Boltek lightning: couldn't save record: %s" % e)
            return
        self._new_pending()
        self.interval_start = end
        self._refresh_totals()
        self._write_history()

    def _recent_total(self, now, seconds):
        try:
            row = self.dbm.getSql("SELECT SUM(strikes) FROM archive WHERE dateTime > ? AND interval > 0",
                                  (int(now - seconds),))
        except Exception:
            return 0
        return int(row[0] or 0) if row else 0

    def _refresh_totals(self):
        now = self.clock()
        lt = time.localtime(now)
        midnight = int(time.mktime((lt.tm_year, lt.tm_mon, lt.tm_mday, 0, 0, 0, 0, 0, -1)))
        new_year = int(time.mktime((lt.tm_year, 1, 1, 0, 0, 0, 0, 0, -1)))

        def total(since):
            try:
                row = self.dbm.getSql("SELECT SUM(strikes) FROM archive WHERE dateTime > ?", (since,))
            except Exception:
                return 0
            return int(row[0] or 0) if row else 0

        self.totals = {'today': total(midnight), 'year': total(new_year), 'alltime': total(0)}
        self.totals_day = (lt.tm_year, lt.tm_yday)

    # ---- boltek.json for the card ----

    def _publish(self, now):
        lt = time.localtime(now)
        if self.totals_day != (lt.tm_year, lt.tm_yday):
            self._refresh_totals()   # new day (or year): restart the counts
        r = self.reading
        pending = self.pending.get('strikes', 0)
        file_time = r.get('file_time') if r else None
        # Fresh if the file changed recently, going by when it last changed
        # here (or NexStorm's own timestamp): the NexStorm PC's clock may
        # not match this one.
        newest = max([t for t in (file_time, self.last_mtime) if t] or [0])
        stale = r is None or now - newest > self.cfg.stale_after
        error = self.read_error
        if error is None and stale and r is not None:
            error = "%s hasn't changed for %d minutes" % (
                os.path.basename(self.cfg.ngxarchive_file), (now - newest) // 60)
        ls = self.last_strike or {}
        realtime = None
        if r is not None:
            tendency = self._tendency(now)
            realtime = {
                'format': 'ngxarchive',
                'file_time': file_time,
                'strike_count': r['strike_count'],
                'storm_count': r['storm_count'],
                'strikes_per_minute': self._rate(now),
                'last_strike_time': ls.get('t'),
                'last_distance_km': ls.get('km'),
                'last_bearing': ls.get('bearing'),
                'last_type': ls.get('type'),
                'last_type_label': ls.get('label'),
                'last_polarity': ls.get('polarity'),
                'last_lat': ls.get('lat'),
                'last_lon': ls.get('lon'),
                'avg_distance_km': self._avg_km(now),
                'tendency': tendency,
                'tendency_label': TENDENCY.get(tendency),
                'peak_rate': self.peak['rate'],
                'peak_rate_time': self.peak['time'],
                'detector_model': r['detector_model'],
                'software_version': r['software_version'],
                'antenna_alignment': r['antenna_alignment'],
                'station_lat': r['station_lat'],
                'station_lon': r['station_lon'],
                'station_source': r['station_source'],
                'nexstorm_lat': r['nexstorm_lat'],
                'nexstorm_lon': r['nexstorm_lon'],
                'strikes_listed': len(r['strikes']),
                # Strikes counted since the service started.
                'counted': self.session['listed'],
                'storms': r['storms'],
                # Newest first: [time, km, bearing, lat, lon, label]
                'recent_strikes': [[s['t'], s['km'], s['bearing'], s['lat'], s['lon'], s['label']]
                                   for s in reversed(r['strikes'][-RECENT_IN_JSON:])],
            }
        data = {
            'version': BOLTEK_VERSION,
            'generated': int(now),
            'source_file': os.path.basename(self.cfg.ngxarchive_file),
            'file_found': os.path.exists(self.cfg.ngxarchive_file),
            'file_time': file_time,
            'stale': stale,
            'error': error,
            'realtime': realtime,
            'totals': {
                'today': self.totals['today'] + pending,
                'year': self.totals['year'] + pending,
                'alltime': self.totals['alltime'] + pending,
                # From the database plus what's been counted since its last record.
                'last_15min': self._recent_total(now, 900) + pending,
                'last_30min': self._recent_total(now, 1800) + pending,
                'last_hour': self._recent_total(now, 3600) + pending,
            },
        }
        path = self.cfg.json_file
        try:
            os.makedirs(os.path.dirname(path), exist_ok=True)
            tmp = path + '.tmp'
            with open(tmp, 'w') as fh:
                json.dump(data, fh)
            os.replace(tmp, path)
        except OSError as e:
            if str(e) != self.error_logged:
                log.error("Boltek lightning: can't write %s: %s" % (path, e))
                self.error_logged = str(e)

    # ---- history for the chart & records modal ----

    @staticmethod
    def _local_ms(key, fmt):
        """'YYYY-MM-DD' or 'YYYY-MM-DD HH' (local time) -> epoch milliseconds."""
        tm = time.strptime(key, fmt)
        return int(time.mktime((tm.tm_year, tm.tm_mon, tm.tm_mday, tm.tm_hour, 0, 0, 0, 0, -1))) * 1000

    def _history_rows(self, fmt, since):
        """Sum the records into local hours or days. dateTime is the END of
        each record's interval, so dateTime - 1 puts it in the right bucket.
        Each row: [start ms, strikes, cg, ic, closest km, highest rate/min]."""
        sql = ("SELECT strftime('%s', dateTime - 1, 'unixepoch', 'localtime') AS k, "
               "SUM(strikes), SUM(cg), SUM(ic), "
               "MIN(CASE WHEN strikes > 0 THEN min_distance END), MAX(max_rate) "
               "FROM archive WHERE dateTime > ?%s GROUP BY k ORDER BY k"
               # Hourly: leave out 1.0.0's 'earlier today' records
               # (interval 0) -- their strikes have no real hour.
               % (fmt, ' AND interval > 0' if '%H' in fmt else ''))
        py_fmt = '%Y-%m-%d %H' if '%H' in fmt else '%Y-%m-%d'
        rows = []
        for k, strikes, cg, ic, closest, rate in self.dbm.genSql(sql, (since,)):
            rows.append([self._local_ms(k, py_fmt), int(strikes or 0), int(cg or 0), int(ic or 0),
                         None if closest is None else round(closest, 1),
                         None if rate is None else round(rate, 1)])
        return rows

    def _write_history(self):
        now = self.clock()
        lt = time.localtime(now)
        this_hour = int(time.mktime((lt.tm_year, lt.tm_mon, lt.tm_mday, lt.tm_hour, 0, 0, 0, 0, -1)))
        try:
            data = {
                'version': BOLTEK_VERSION,
                'generated': int(now),
                'columns': ['start', 'strikes', 'cg', 'ic', 'closest_km', 'max_rate'],
                # The last 48 hours, so the modal can show any 24 of them.
                'hourly': self._history_rows('%Y-%m-%d %H', this_hour - 47 * 3600),
                'daily': self._history_rows('%Y-%m-%d', 0),
            }
        except Exception as e:
            log.error("Boltek lightning: couldn't read history: %s" % e)
            return
        path = self.cfg.history_file
        try:
            os.makedirs(os.path.dirname(path), exist_ok=True)
            tmp = path + '.tmp'
            with open(tmp, 'w') as fh:
                json.dump(data, fh, separators=(',', ':'))
            os.replace(tmp, path)
        except OSError as e:
            log.error("Boltek lightning: can't write %s: %s" % (path, e))
