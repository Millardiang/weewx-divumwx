"""
##############################################################################################
# divumwx_boltek.py version 1.3.0
#  Copyright (C) 2026 Ian Millard, Mike Isaac
#  GPLv3
##############################################################################################

Boltek lightning detector support for DivumWX.

Strikes come from one of two files; the format is recognised from the
contents, so either works with the same settings:

1. NSD Bridge's NSDRealtime.json (NSD Bridge replaces the old NSBackend /
   NSList on the NexStorm PC). Rewritten every 10 seconds:

    {"timestamp": 1793180215,                   when written (Unix s)
     "strikes_today": 102,                      NSD Bridge's day total
     "rate_per_min": 18, ...,                   (other realtime values)
     "station": {"lat": 51.4545, "lon": -2.5879, "distance_unit": "km"},
     "strikes_window": "today",                 or "N min"
     "strikes_count": 102,
     "strikes": [                               newest first
        {"timestamp": 1793180213,              strike time (Unix s)
         "direction": 183, "bearing": 183.4,    degrees true (alignment applied)
         "distance": 261,                       in station.distance_unit
         "type": "cc+",                         cg+ cg- cc+ cc- (cc = in-cloud)
         "lat": 49.1112, "lon": -2.8005}, ...]}   strike position (null if
                                                NSD Bridge has no station set)

   NSD Bridge can also serve it over HTTP, so source_file may be a URL such
   as http://nexstorm-pc:8090/data/NSDRealtime.json (set http_listen =
   0.0.0.0:8090 in nsdbridge.ini).

2. NexStorm's own ngxarchive.json (used by 1.1.x - 1.2.x):

    {"TimestampEpoch": 1792751132017,          when written (Unix ms)
     "StationLat": 51.00, "StationLon": -3.11,
     "SoftwareVersion": "1.9.6.8317", "DetectorModel": "LD-350",
     "AntennaAlignment": 0,
     "StrikeCount": 4623,                       NexStorm's day total
     "StormCount": 0,
     "StrikeData": [                            recent strikes (~2 hours)
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
they're right even if the file's station position differs (the file's own
distance/bearing are used only for a strike without a position, or if
weewx.conf has no position). boltek.json's station_lat/lon are weewx.conf's.

The file's day total (strikes_today / StrikeCount) is passed on in
boltek.json as realtime.strike_count for the card's Today; it isn't used to
count strikes. NSDRealtime.json tells cloud-to-ground from in-cloud, so its
strikes fill the database's cg and ic columns; ngxarchive.json's only cg.

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

It runs on its own background thread, so a slow or missing file (or URL) never
holds up WeeWX's loop. If the file isn't there yet the service just waits
quietly (one log line), so it's safe on stations without a Boltek.

weewx.conf (written by install.py on first install; every key optional):

    [BoltekLightning]
        # NSD Bridge's NSDRealtime.json (or NexStorm's ngxarchive.json):
        # a path relative to the DivumWX site folder, an absolute path, or
        # an http(s):// URL. (ngxarchive_file, its old name, still works.)
        source_file = NSDRealtime.json
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
        # false: boltek.json and boltek_history.json are supplied ready-made
        # (e.g. by another DivumWX station), so source_file isn't read,
        # nothing is converted and the Boltek database isn't used.
        convert = true
        # With convert = false, where the ready-made files come from (a path
        # relative to the site folder, an absolute path, or a URL). They're
        # copied to json_file / history_file whenever they change. Leave
        # blank if they're already written straight to those paths.
        direct_json_source =
        direct_history_source =
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
import hashlib
import threading
import time
import urllib.request

import weewx
import weewx.manager
from weewx.engine import StdService
from weeutil.weeutil import to_int

log = logging.getLogger(__name__)

BOLTEK_VERSION = '1.3.0'

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

CG_TYPE = 1                 # 'type' for cloud-to-ground (ngxarchive's value)
IC_TYPE = 0                 # 'type' given to NSDRealtime.json's in-cloud strikes
RECENT_IN_JSON = 100        # strikes always passed on in boltek.json (newest)
MAP_WINDOW = 2 * 3600       # ...plus every strike from this long ago, for the strike map
MAP_MAX = 5000              # but never more than this many
TENDENCY = {0: 'Steady', 1: 'Approaching', 2: 'Receding'}


def _num(v, cast=float):
    try:
        return cast(float(v))
    except (TypeError, ValueError):
        return None


def type_label(stype, polarity, ic=False):
    """'+CG' / '-CG' for cloud-to-ground strikes, '+IC' / '-IC' for in-cloud
    ones (known only from NSDRealtime.json); 'Type n' otherwise."""
    if stype is None:
        return None
    sign = '+' if polarity == 1 else '-' if polarity == 0 else ''
    if stype == CG_TYPE:
        return sign + 'CG'
    if ic:
        return sign + 'IC'
    return 'Type %d' % stype


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
        'type': stype, 'polarity': pol, 'ic': False,
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
        'format': 'ngxarchive',
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


NSD_TYPES = {'cg+': (CG_TYPE, 1, False), 'cg-': (CG_TYPE, 0, False),
             'cc+': (IC_TYPE, 1, True), 'cc-': (IC_TYPE, 0, True)}
MILE_KM = 1.609344


def parse_nsd_strike(entry, station=None, km_per_unit=1.0):
    """One NSDRealtime.json 'strikes' entry -> dict (as parse_strike), or
    None if it has no usable time."""
    if not isinstance(entry, dict):
        return None
    t = _num(entry.get('timestamp'), int)
    if not t:
        return None
    stype, pol, ic = NSD_TYPES.get(str(entry.get('type', '')).lower(), (None, None, False))
    lat, lon = _num(entry.get('lat')), _num(entry.get('lon'))
    km = _num(entry.get('distance'))
    if km is not None:
        km *= km_per_unit
    bng = _num(entry.get('bearing'))
    if bng is None:
        bng = _num(entry.get('direction'))
    direction = _num(entry.get('direction'), int)
    if station and lat is not None and lon is not None:
        km, bng = distance_bearing(station[0], station[1], lat, lon)
    return {
        'ms': t * 1000,
        't': t,
        'km': None if km is None else round(km, 1),
        'bearing': None if bng is None else round(bng % 360, 1),
        'lat': lat, 'lon': lon,
        'type': stype, 'polarity': pol, 'ic': ic,
        'label': type_label(stype, pol, ic),
        # Identity: time (whole seconds), plus NSD Bridge's whole-degree
        # direction and distance as it stores them, so the same strike is
        # recognised however it reappears (e.g. restored after a restart).
        'key': 'nsd/%d/%s/%s/%s' % (t, direction, entry.get('distance'), entry.get('type')),
    }


def parse_nsdrealtime(doc, station=None):
    """NSD Bridge's NSDRealtime.json (already decoded) -> dict, as
    parse_ngxarchive returns."""
    st = doc.get('station') if isinstance(doc.get('station'), dict) else {}
    unit = str(st.get('distance_unit') or 'km').lower()
    km_per_unit = MILE_KM if unit == 'mi' else 1.0
    raw = doc.get('strikes')
    if not isinstance(raw, list):
        raise ValueError("NSDRealtime.json has no strike list: set realtime_json_strikes=true "
                         "in nsdbridge.ini (NSD Bridge 1.2 or later)")
    strikes = sorted((s for s in (parse_nsd_strike(e, station, km_per_unit) for e in raw) if s),
                     key=lambda s: s['ms'])
    ts = _num(doc.get('timestamp'), int)
    file_lat, file_lon = _num(st.get('lat')), _num(st.get('lon'))
    return {
        'format': 'nsdbridge',
        'file_time': ts or None,
        'strike_count': _num(doc.get('strikes_today'), int),
        'storm_count': None,
        'storms': [],
        'strikes': strikes,
        'strikes_window': doc.get('strikes_window'),
        'detector_model': None,
        'software_version': 'NSD Bridge',
        'antenna_alignment': None,
        'station_lat': station[0] if station else file_lat,
        'station_lon': station[1] if station else file_lon,
        'nexstorm_lat': file_lat,
        'nexstorm_lon': file_lon,
        'station_source': 'weewx.conf' if station else ('NSD Bridge' if file_lat is not None else None),
    }


def parse_source(text, station=None):
    """The file's contents -> dict, whichever format it is. Raises
    ValueError if it can't be read."""
    try:
        doc = json.loads(text.lstrip('\ufeff'))
    except ValueError as e:
        raise ValueError('not valid JSON (%s)' % e)
    if isinstance(doc, dict) and 'StrikeData' in doc:
        return parse_ngxarchive(text, station)
    if isinstance(doc, dict) and ('strikes_today' in doc or 'strikes' in doc) and 'timestamp' in doc:
        return parse_nsdrealtime(doc, station)
    raise ValueError("neither NSD Bridge's NSDRealtime.json nor NexStorm's ngxarchive.json")


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

        # source_file (ngxarchive_file before 1.3.0): a path or an http(s) URL.
        src = str(bc.get('source_file') or bc.get('ngxarchive_file') or 'NSDRealtime.json').strip()
        self.source_is_url = src.lower().startswith(('http://', 'https://'))
        self.ngxarchive_file = src if self.source_is_url else os.path.join(site, src)
        self.source_file = self.ngxarchive_file
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
        # convert = false: boltek.json and boltek_history.json are supplied
        # ready-made, so nothing is read, converted or stored here. If they
        # are somewhere else (a path or a URL), they're copied into place.
        self.convert = str(bc.get('convert', 'true')).strip().lower() not in ('false', 'no', 'off', '0')
        self.direct_sources = []
        for key, dst in (('direct_json_source', self.json_file), ('direct_history_source', self.history_file)):
            v = str(bc.get(key) or '').strip()
            if v:
                is_url = v.lower().startswith(('http://', 'https://'))
                path = v if is_url else os.path.join(site, v)
                if is_url or os.path.abspath(path) != os.path.abspath(dst):
                    self.direct_sources.append((path, dst))

        self._stop = threading.Event()
        if not self.convert:
            self._thread = threading.Thread(target=self._run_direct, name='BoltekLightning', daemon=True)
            self._thread.start()
            if self.direct_sources:
                log.info("Boltek lightning %s: convert = false; copying %s every %ss"
                         % (BOLTEK_VERSION, ', '.join('%s -> %s' % x for x in self.direct_sources), self.poll_interval))
            else:
                log.info("Boltek lightning %s: convert = false; using %s and %s as supplied (nothing converted)"
                         % (BOLTEK_VERSION, self.json_file, self.history_file))
            return
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

    def _run_direct(self):
        """convert = false: keep the ready-made files in place."""
        if not self.direct_sources:
            return              # they're already where the dashboard reads them
        copier = DirectCopier(self.direct_sources, self.poll_interval)
        while not self._stop.is_set():
            try:
                copier.tick()
            except Exception as e:  # never take WeeWX down with us
                log.error("Boltek lightning: copying ready-made files: %s" % e)
            self._stop.wait(self.poll_interval)

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


class DirectCopier(object):
    """With convert = false: copies ready-made boltek.json /
    boltek_history.json from where they're supplied (a path or a URL) into
    the dashboard's jsondata folder, unchanged. Only valid JSON is copied,
    so a half-written source never replaces a good file, and only when it
    has changed."""

    def __init__(self, pairs, poll_interval=10):
        self.pairs = pairs
        self.timeout = min(30, max(5, poll_interval))
        self.last = {}          # source -> digest of what was last copied
        self.logged = {}        # source -> last problem logged

    def _fetch(self, src):
        if src.lower().startswith(('http://', 'https://')):
            req = urllib.request.Request(src, headers={'User-Agent': 'DivumWX-Boltek/' + BOLTEK_VERSION,
                                                       'Cache-Control': 'no-cache'})
            with urllib.request.urlopen(req, timeout=self.timeout) as resp:
                return resp.read()
        with open(src, 'rb') as fh:
            return fh.read()

    def tick(self):
        for src, dst in self.pairs:
            try:
                body = self._fetch(src)
                json.loads(body.decode('utf-8', 'replace').lstrip('\ufeff'))
            except Exception as e:
                msg = str(getattr(e, 'reason', None) or e)
                if self.logged.get(src) != msg:
                    log.info("Boltek lightning: can't copy %s (%s); will keep trying" % (src, msg))
                    self.logged[src] = msg
                continue
            if self.logged.pop(src, None):
                log.info("Boltek lightning: copying %s again" % src)
            digest = hashlib.sha1(body).hexdigest()
            if digest == self.last.get(src):
                continue
            try:
                os.makedirs(os.path.dirname(dst), exist_ok=True)
                tmp = dst + '.tmp'
                with open(tmp, 'wb') as fh:
                    fh.write(body)
                os.replace(tmp, dst)
                self.last[src] = digest
            except OSError as e:
                if self.logged.get(dst) != str(e):
                    log.error("Boltek lightning: can't write %s: %s" % (dst, e))
                    self.logged[dst] = str(e)


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
        self.counted = {}           # key -> [time, how many listed with that key were counted], this run
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
                if row[3] == IC_TYPE and self.cfg_source_is_nsd():
                    self.last_strike['label'] = type_label(row[3], None, ic=True)
        except Exception as e:
            log.debug("Boltek lightning: nothing to resume from (%s)" % e)

    def cfg_source_is_nsd(self):
        src = str(getattr(self.cfg, 'ngxarchive_file', '')).lower()
        return 'ngxarchive' not in src

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
        # NexStorm gives strike times to the whole second, and the strokes
        # of one flash can come in the same second at the same place, so
        # several listed strikes can share a key. Each is a strike of its
        # own: count the n-th strike with a key once n of them are listed
        # (a later rewrite of the file that adds another counts it too).
        new = []
        seen = {}
        for s in reading['strikes']:
            if not (self.baseline_s < s['t'] <= now + 300):
                continue
            k = s['key']
            seen[k] = seen.get(k, 0) + 1
            done = self.counted.get(k)
            if done is None or seen[k] > done[1]:
                new.append(dict(s, nth=seen[k]))
        for k, n in seen.items():
            done = self.counted.get(k)
            if done is None or n > done[1]:
                t = next(x['t'] for x in reading['strikes'] if x['key'] == k)
                self.counted[k] = [t, n]
        self.strike_count = reading['strike_count']

        past = {}
        current_end = self.interval_start + self.cfg.archive_interval
        for s in new:
            close = s['km'] is not None and s['km'] <= self.cfg.close_km
            counts = {'strikes': 1, 'cg': 1 if s['type'] == CG_TYPE else 0, 'ic': 1 if s.get('ic') else 0,
                      'close': 1 if close else 0}
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
                self.recent[(s['key'], s['nth'])] = (s['t'], s['km'])
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
        self.counted = dict((k, v) for k, v in self.counted.items() if v[0] >= oldest - 1)

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
        if getattr(self.cfg, 'source_is_url', False):
            return self._read_url()
        path = self.cfg.ngxarchive_file
        try:
            st = os.stat(path)
        except OSError as e:
            self.read_error = "can't open %s (%s)" % (path, e.strerror or e)
            if not self.missing_logged:
                log.info("Boltek lightning: waiting for %s" % path)
                self.missing_logged = True
            return None
        if self.missing_logged:
            log.info("Boltek lightning: found %s" % path)
            self.missing_logged = False
        if st.st_mtime == self.last_mtime:
            return None
        try:
            with open(path, 'r', encoding='utf-8', errors='replace') as fh:
                reading = parse_source(fh.read(), getattr(self.cfg, 'station', None))
        except (OSError, ValueError) as e:
            # The file may be part-way through being rewritten: try again next time.
            return self._read_failed(path, str(e))
        self.error_logged = None
        self.read_error = None
        self.last_mtime = st.st_mtime
        return reading

    def _read_failed(self, where, msg):
        self.read_error = "can't read %s: %s" % (os.path.basename(where.rstrip('/')) or where, msg)
        if msg != self.error_logged:
            log.error("Boltek lightning: can't read %s: %s" % (where, msg))
            self.error_logged = msg
        return None

    def _read_url(self):
        """Fetch the file from a URL (e.g. NSD Bridge's built-in web
        server). Unchanged content is skipped, like an unchanged file."""
        url = self.cfg.ngxarchive_file
        req = urllib.request.Request(url, headers={'User-Agent': 'DivumWX-Boltek/' + BOLTEK_VERSION,
                                                   'Cache-Control': 'no-cache'})
        try:
            with urllib.request.urlopen(req, timeout=min(30, max(5, self.cfg.poll_interval))) as resp:
                body = resp.read()
        except Exception as e:
            msg = str(getattr(e, 'reason', None) or e)
            if self.missing_logged != msg:
                log.info("Boltek lightning: can't fetch %s (%s); will keep trying" % (url, msg))
                self.missing_logged = msg
            self.read_error = "can't fetch %s (%s)" % (url, msg)
            return None
        if self.missing_logged:
            log.info("Boltek lightning: fetching %s again" % url)
            self.missing_logged = False
        digest = hashlib.sha1(body).hexdigest()
        if digest == getattr(self, '_last_digest', None):
            return None
        try:
            reading = parse_source(body.decode('utf-8', 'replace'), getattr(self.cfg, 'station', None))
        except ValueError as e:
            return self._read_failed(url, str(e))
        self._last_digest = digest
        self.error_logged = None
        self.read_error = None
        # When it last changed, by this machine's clock (for "stale").
        self.last_mtime = self.clock()
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
                os.path.basename(self.cfg.ngxarchive_file.rstrip('/')), (now - newest) // 60)
        ls = self.last_strike or {}
        realtime = None
        if r is not None:
            tendency = self._tendency(now)
            realtime = {
                'format': r.get('format', 'ngxarchive'),
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
                                   for s in reversed(self._map_strikes(r['strikes'], now))],
            }
        data = {
            'version': BOLTEK_VERSION,
            'generated': int(now),
            'source_file': (self.cfg.ngxarchive_file if getattr(self.cfg, 'source_is_url', False)
                            else os.path.basename(self.cfg.ngxarchive_file)),
            'file_found': (r is not None and self.read_error is None if getattr(self.cfg, 'source_is_url', False)
                           else os.path.exists(self.cfg.ngxarchive_file)),
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

    @staticmethod
    def _map_strikes(strikes, now):
        """Strikes for boltek.json's recent_strikes (oldest first): every
        strike from the strike map's last two hours, and at least the newest
        RECENT_IN_JSON, capped at MAP_MAX. NSDRealtime.json lists a whole
        day, so a busy storm would otherwise be cut short on the map."""
        recent = [s for s in strikes if s['t'] > now - MAP_WINDOW]
        if len(recent) < RECENT_IN_JSON:
            recent = strikes[-RECENT_IN_JSON:]
        return recent[-MAP_MAX:]

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
