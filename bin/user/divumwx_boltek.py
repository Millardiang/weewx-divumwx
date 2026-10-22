"""
##############################################################################################
# divumwx_boltek.py version 1.0.0
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################

Boltek lightning detector support for DivumWX.

Boltek's NexStorm software writes its live readings to NSDRealtime.txt
(one line of 23 comma-separated values, see NEXSTORM_FIELDS below; an
older 22-value layout, FIELDS, is also accepted). BoltekLightningService:

  * reads that file every poll_interval seconds;
  * keeps strike totals in DivumWX's own Boltek database (a separate
    SQLite file, divumwx_boltek.sdb, created automatically the first time
    the service runs -- nothing to set up by hand);
  * writes jsondata/boltek.json for the dashboard's Boltek Lightning card,
    and jsondata/boltek_history.json (hourly and daily strike history) for
    its chart & records modal, modalBoltekLightning.html.

It runs on its own background thread, so a slow or missing file never
holds up WeeWX's loop. If NSDRealtime.txt isn't there yet the service just
waits quietly (one log line), so it's safe on stations without a Boltek.

weewx.conf (written by install.py on first install; every key optional):

    [BoltekLightning]
        # Where NexStorm puts NSDRealtime.txt. Relative paths are taken
        # from the DivumWX site folder; absolute paths are used as-is.
        realtime_file = NSDRealtime.txt
        # Distance unit, only used if the file doesn't say (NexStorm's own
        # layout does, in its 21st value): km or mi.
        distance_unit = km
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
import os
import re
import threading
import time

import weewx
import weewx.manager
from weewx.engine import StdService
from weeutil.weeutil import to_int

log = logging.getLogger(__name__)

BOLTEK_VERSION = '1.0.0'

# One record per archive_interval. 'strikes', 'cg', 'ic', 'close' and 'noise'
# are counts *during* that interval (worked out from NexStorm's running
# session totals). 'max_rate' is the highest strikes-per-minute seen in the
# interval and 'min_distance' the closest last-strike distance seen while
# strikes were arriving. The rest are the readings at the end of the
# interval. session_total lets a restart carry on from where it left off.
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

# NexStorm's NSDRealtime.txt: one line of 23 comma-separated values,
# e.g. (worked out from a real file; the four counts by type add up to the
# day's total):
#   1792706279,0,0,1792701774,181,455,cc-,0,207,17661,168921,5,02:06,5,02:06,
#   62,65,23,57,0 days 4 hours 17 min,km,60,5000
NEXSTORM_FIELDS = [
    'file_time',            # 1  time of the reading (Unix time)
    'strikes_per_minute',   # 2  strike rate now
    'close_per_minute',     # 3  close-strike rate now
    'last_strike_time',     # 4  last strike (Unix time)
    'last_bearing',         # 5  degrees
    'last_distance',        # 6  in the unit of field 21
    'last_type',            # 7  cg+, cg-, cc+ or cc-
    'close_count',          # 8  close strikes today
    'total_strikes',        # 9  strikes today (NexStorm resets it at midnight)
    'unknown_10',           # 10 not identified yet
    'unknown_11',           # 11 not identified yet
    'peak_rate',            # 12 highest strikes/minute today
    'peak_rate_time',       # 13 HH:MM
    'peak_burst',           # 14 highest strikes/second today
    'peak_burst_time',      # 15 HH:MM
    'cg_plus',              # 16 today's +CG strikes
    'cg_minus',             # 17 today's -CG strikes
    'cc_plus',              # 18 today's +CC (in-cloud) strikes
    'cc_minus',             # 19 today's -CC (in-cloud) strikes
    'uptime',               # 20 NexStorm's uptime, e.g. "0 days 4 hours 17 min"
    'distance_unit',        # 21 km or mi
    'unknown_22',           # 22 not identified yet
    'unknown_23',           # 23 not identified yet
]
NEXSTORM_TYPES = {'cg-': (1, 'CG-'), 'cg+': (2, 'CG+'), 'cc-': (3, 'CC-'), 'cc+': (3, 'CC+')}

# Older layout from the first spec (22 values starting DD/MM/YYYY, HH:MM:SS).
# Still accepted if a line looks like it.
FIELDS = [
    'date', 'time', 'total_strikes', 'strikes_per_minute',
    'last_distance', 'last_bearing', 'last_type', 'last_strike_time',
    'cg_total', 'ic_total', 'close_count', 'tendency', 'alarm',
    'avg_distance', 'peak_rate', 'peak_rate_time', 'hardware_status',
    'noise_count', 'strikes_5min', 'strikes_15min', 'strikes_30min',
    'strikes_60min',
]

STRIKE_TYPES = {0: 'Noise', 1: '-CG', 2: '+CG', 3: 'IC'}
ALARMS = {0: 'Clear', 1: 'Caution', 2: 'High alarm'}
KM_PER_MILE = 1.609344

_DATE_RE = re.compile(r'^(\d{1,2})[/.-](\d{1,2})[/.-](\d{4})$')
_TIME_RE = re.compile(r'^(\d{1,2}):(\d{2})(?::(\d{2}))?$')


def split_line(line):
    """Split one NSDRealtime.txt line into its values, whatever separates
    them (comma, semicolon, tab or spaces)."""
    line = line.strip().lstrip('﻿')
    for sep in (',', ';', '\t'):
        if sep in line:
            return [v.strip() for v in line.split(sep)]
    parts = line.split()
    # With spaces as the separator, a full "date time" in a time field
    # arrives as two values: join them back up.
    out = []
    i = 0
    while i < len(parts):
        if (len(out) >= 2 and i + 1 < len(parts) and _DATE_RE.match(parts[i])
                and _TIME_RE.match(parts[i + 1]) and len(parts) > len(FIELDS)):
            out.append(parts[i] + ' ' + parts[i + 1])
            i += 2
        else:
            out.append(parts[i])
            i += 1
    return out


def _local_ts(day, month, year, hh, mm, ss):
    return int(time.mktime((year, month, day, hh, mm, ss, 0, 0, -1)))


def parse_datetime(date_str, time_str):
    """'DD/MM/YYYY', 'HH:MM:SS' (station local time) -> epoch seconds."""
    d = _DATE_RE.match(date_str or '')
    t = _TIME_RE.match(time_str or '')
    if not d or not t:
        return None
    return _local_ts(int(d.group(1)), int(d.group(2)), int(d.group(3)),
                     int(t.group(1)), int(t.group(2)), int(t.group(3) or 0))


def parse_event_time(value, file_ts):
    """A strike/peak time: either 'HH:MM:SS' (on the file's date, or the day
    before if that would be in the future) or a full 'DD/MM/YYYY HH:MM:SS'."""
    value = (value or '').strip()
    if not value or file_ts is None:
        return None
    parts = value.split()
    if len(parts) == 2:
        return parse_datetime(parts[0], parts[1])
    t = _TIME_RE.match(value)
    if not t:
        return None
    lt = time.localtime(file_ts)
    ts = _local_ts(lt.tm_mday, lt.tm_mon, lt.tm_year,
                   int(t.group(1)), int(t.group(2)), int(t.group(3) or 0))
    if ts > file_ts + 60:
        prev = time.localtime(file_ts - 86400)
        ts = _local_ts(prev.tm_mday, prev.tm_mon, prev.tm_year,
                       int(t.group(1)), int(t.group(2)), int(t.group(3) or 0))
    return ts


_EPOCH_RE = re.compile(r'^\d{9,11}$')


def _num(v, cast=float):
    try:
        return cast(float(v))
    except (TypeError, ValueError):
        return None


def parse_nexstorm(values, distance_unit='km'):
    """NexStorm's 23-value NSDRealtime.txt line -> readings (km)."""
    if len(values) < 21:
        raise ValueError('expected 23 values, found %d' % len(values))
    raw = dict(zip(NEXSTORM_FIELDS, values))
    file_ts = _num(raw['file_time'], int)
    unit = (raw.get('distance_unit') or distance_unit or 'km').strip().lower()
    to_km = KM_PER_MILE if unit.startswith('mi') else 1.852 if unit in ('nm', 'nmi') else 1.0

    def km(key):
        v = _num(raw.get(key))
        return None if v is None else round(v * to_km, 2)

    type_code, type_label = NEXSTORM_TYPES.get((raw['last_type'] or '').strip().lower(), (None, None))
    if type_label is None and raw['last_type'].strip():
        type_label = raw['last_type'].strip().upper()
    parts = [_num(raw.get(k), int) for k in ('cg_plus', 'cg_minus', 'cc_plus', 'cc_minus')]
    cg = None if parts[0] is None and parts[1] is None else (parts[0] or 0) + (parts[1] or 0)
    ic = None if parts[2] is None and parts[3] is None else (parts[2] or 0) + (parts[3] or 0)
    last = _num(raw['last_strike_time'], int)
    return {
        'format': 'nexstorm',
        'file_time': file_ts,
        'total_strikes': _num(raw['total_strikes'], int),
        'strikes_per_minute': _num(raw['strikes_per_minute']),
        'close_per_minute': _num(raw['close_per_minute']),
        'last_distance_km': km('last_distance'),
        'last_bearing': _num(raw['last_bearing']),
        'last_type': type_code,
        'last_type_label': type_label,
        'last_strike_time': last if last else None,
        'cg_total': cg,
        'ic_total': ic,
        'cg_plus': parts[0], 'cg_minus': parts[1], 'cc_plus': parts[2], 'cc_minus': parts[3],
        'close_count': _num(raw['close_count'], int),
        'peak_rate': _num(raw['peak_rate']),
        'peak_rate_time': parse_event_time(raw['peak_rate_time'], file_ts),
        'peak_burst': _num(raw['peak_burst']),
        'peak_burst_time': parse_event_time(raw['peak_burst_time'], file_ts),
        'uptime': raw.get('uptime') or None,
        'distance_unit': unit,
        # Not in this format:
        'tendency': None, 'alarm': None, 'alarm_label': None, 'avg_distance_km': None,
        'hardware_status': None, 'noise_count': None,
        'strikes_5min': None, 'strikes_15min': None, 'strikes_30min': None, 'strikes_60min': None,
        'unidentified': [raw.get(k) for k in ('unknown_10', 'unknown_11', 'unknown_22', 'unknown_23')],
    }


def parse_realtime(text, distance_unit='km'):
    """Parse NSDRealtime.txt's contents into a dict of readings, distances in
    km. Raises ValueError if the line can't be read."""
    lines = [ln for ln in text.splitlines() if ln.strip()]
    if not lines:
        raise ValueError('file is empty')
    values = split_line(lines[-1])
    # NexStorm's own layout starts with a Unix time.
    if values and _EPOCH_RE.match(values[0]):
        return parse_nexstorm(values, distance_unit)
    if len(values) < len(FIELDS):
        raise ValueError("not NexStorm's layout (23 comma-separated values starting with a "
                         "Unix time); found %d value(s) starting %r" % (len(values), (values or [''])[0][:20]))
    raw = dict(zip(FIELDS, values[:len(FIELDS)]))

    file_ts = parse_datetime(raw['date'], raw['time'])
    if file_ts is None:
        raise ValueError("can't read date/time %r %r" % (raw['date'], raw['time']))
    to_km = KM_PER_MILE if str(distance_unit).lower().startswith('mi') else 1.0

    def i(key):
        try:
            return int(float(raw[key]))
        except (TypeError, ValueError):
            return None

    def f(key):
        try:
            return float(raw[key])
        except (TypeError, ValueError):
            return None

    def km(key):
        v = f(key)
        return None if v is None else round(v * to_km, 2)

    last_type = i('last_type')
    alarm = i('alarm')
    return {
        'format': 'spec22',
        'file_time': file_ts,
        'total_strikes': i('total_strikes'),
        'strikes_per_minute': f('strikes_per_minute'),
        'last_distance_km': km('last_distance'),
        'last_bearing': f('last_bearing'),
        'last_type': last_type,
        'last_type_label': STRIKE_TYPES.get(last_type),
        'last_strike_time': parse_event_time(raw['last_strike_time'], file_ts),
        'cg_total': i('cg_total'),
        'ic_total': i('ic_total'),
        'close_count': i('close_count'),
        'tendency': i('tendency'),
        'alarm': alarm,
        'alarm_label': ALARMS.get(alarm),
        'avg_distance_km': km('avg_distance'),
        'peak_rate': f('peak_rate'),
        'peak_rate_time': parse_event_time(raw['peak_rate_time'], file_ts),
        'hardware_status': raw['hardware_status'],
        'noise_count': i('noise_count'),
        'strikes_5min': i('strikes_5min'),
        'strikes_15min': i('strikes_15min'),
        'strikes_30min': i('strikes_30min'),
        'strikes_60min': i('strikes_60min'),
    }


def counter_delta(new, old):
    """Strikes added since the last reading of a running total. A total that
    went down means NexStorm started a new session, so everything in the
    new total is new."""
    if new is None:
        return 0
    if old is None:
        return 0
    return new - old if new >= old else new


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

        self.realtime_file = os.path.join(site, bc.get('realtime_file', 'NSDRealtime.txt'))
        self.json_file = os.path.join(site, bc.get('json_file', 'jsondata/boltek.json'))
        self.history_file = os.path.join(site, bc.get('history_file', 'jsondata/boltek_history.json'))
        self.distance_unit = bc.get('distance_unit', 'km')
        self.poll_interval = max(2, to_int(bc.get('poll_interval', 10)))
        self.archive_interval = max(60, to_int(bc.get('archive_interval', 300)))
        self.stale_after = max(60, to_int(bc.get('stale_after', 600)))
        self.binding = bc.get('binding', 'boltek_binding')
        self.config_dict = config_dict

        self._stop = threading.Event()
        self._thread = threading.Thread(target=self._run, name='BoltekLightning', daemon=True)
        self._thread.start()
        log.info("Boltek lightning %s: reading %s every %ss, writing %s, database binding %s"
                 % (BOLTEK_VERSION, self.realtime_file, self.poll_interval, self.json_file, self.binding))

    def shutDown(self):
        self._stop.set()
        if self._thread.is_alive():
            self._thread.join(timeout=10)

    # ---- background thread ----

    def _run(self):
        try:
            # Opening with initialize=True creates the database and its table
            # the first time -- this is what sets up the Boltek database.
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

    COUNTERS = (('total_strikes', 'strikes', 'session_total'),
                ('cg_total', 'cg', 'session_cg'),
                ('ic_total', 'ic', 'session_ic'),
                ('close_count', 'close', 'session_close'),
                ('noise_count', 'noise', 'session_noise'))

    def __init__(self, cfg, dbm, clock=time.time):
        self.cfg = cfg
        self.dbm = dbm
        self.clock = clock
        self.last_counts = {}       # running totals at the last reading
        self.pending = {}           # counts gathered since the last record
        self.last_reading = None
        self.last_mtime = None
        self.missing_logged = False
        self.error_logged = None
        self.read_error = None      # why the last read failed, for boltek.json
        self.db_was_empty = False   # never read NexStorm before: count today's strikes too
        self.totals = {'today': 0, 'year': 0, 'alltime': 0}
        self.totals_day = None
        self.interval_start = self._interval_floor(self.clock())
        self._reset_interval_extremes()
        self._resume()
        self._refresh_totals()
        self._write_history()

    def _reset_interval_extremes(self):
        self.max_rate = None
        self.min_distance = None

    def _interval_floor(self, ts):
        return int(ts // self.cfg.archive_interval * self.cfg.archive_interval)

    def _resume(self):
        """Pick up the running totals from the last record, so strikes
        between a restart and the first reading aren't lost or double
        counted."""
        try:
            row = self.dbm.getSql("SELECT session_total, session_cg, session_ic, session_close, "
                                  "session_noise FROM archive ORDER BY dateTime DESC LIMIT 1")
        except Exception:
            row = None
        if row:
            for (src, _col, sess), val in zip(self.COUNTERS, row):
                if val is not None:
                    self.last_counts[src] = val
        if not row or all(v is None for v in row):
            # Nothing ever read from NexStorm yet (a brand-new database, or
            # only empty records written while the file couldn't be read).
            self.db_was_empty = True
        self.pending = dict((col, 0) for _src, col, _sess in self.COUNTERS)

    def run(self, stop):
        while not stop.is_set():
            self.tick()
            stop.wait(self.cfg.poll_interval)
        # Shutting down: save what's been counted so far, so a restart
        # doesn't lose up to an archive_interval of strikes.
        self._write_record(self.clock(), exact=True)

    def tick(self):
        now = self.clock()
        # Close the finished interval before counting this reading, so
        # strikes read after a boundary (midnight included) land in the new
        # interval and the right day.
        if now >= self.interval_start + self.cfg.archive_interval:
            self._write_record(now)
        reading = self._read()
        if reading is not None:
            if self.db_was_empty and not self.last_counts:
                self._seed_from_today(reading, now)
            new_strikes = 0
            for src, col, _sess in self.COUNTERS:
                new = reading.get(src)
                delta = counter_delta(new, self.last_counts.get(src))
                self.pending[col] += delta
                if col == 'strikes':
                    new_strikes = delta
                if new is not None:
                    self.last_counts[src] = new
            rate = reading.get('strikes_per_minute')
            if rate is not None and (self.max_rate is None or rate > self.max_rate):
                self.max_rate = rate
            dist = reading.get('last_distance_km')
            if new_strikes > 0 and dist is not None and (self.min_distance is None or dist < self.min_distance):
                self.min_distance = dist
            self.last_reading = reading
        self._publish(now)

    def _seed_from_today(self, reading, now):
        """First reading ever (new database, or one with only empty
        records). NexStorm's counts are today's so far (it resets them at
        midnight). Those strikes happened at unknown times earlier today, so
        they're saved as one record at the very start of the day (00:00):
        they count towards today, the year and all time, but not towards the
        last hour or the hour they're read in. Older-layout session counts
        aren't day-based, so they're only used as the starting point."""
        self.db_was_empty = False
        ft = reading.get('file_time')
        if reading.get('format') != 'nexstorm' or ft is None:
            return
        if time.localtime(ft)[:3] != time.localtime(now)[:3]:
            return
        lt = time.localtime(now)
        midnight = int(time.mktime((lt.tm_year, lt.tm_mon, lt.tm_mday, 0, 0, 0, 0, 0, -1)))
        rec = {'dateTime': midnight + 1, 'usUnits': weewx.METRIC, 'interval': 0}
        for src, col, sess in self.COUNTERS:
            val = reading.get(src)
            rec[col] = val or 0
            rec[sess] = val
            if val is not None:
                self.last_counts[src] = val     # count from here on
        if not rec['strikes']:
            return
        try:
            self.dbm.addRecord(rec)
            log.info("Boltek lightning: saved NexStorm's %d strikes from earlier today" % rec['strikes'])
        except Exception as e:
            log.error("Boltek lightning: couldn't save today's earlier strikes: %s" % e)
        self._refresh_totals()

    def _read(self):
        path = self.cfg.realtime_file
        try:
            st = os.stat(path)
        except OSError as e:
            self.read_error = "can't open %s (%s)" % (path, e.strerror or e)
            if not self.missing_logged:
                log.info("Boltek lightning: waiting for %s (NexStorm's realtime file)" % path)
                self.missing_logged = True
            return None
        if self.missing_logged:
            log.info("Boltek lightning: found %s" % path)
            self.missing_logged = False
        if st.st_mtime == self.last_mtime:
            return None
        try:
            with open(path, 'r', encoding='utf-8', errors='replace') as fh:
                reading = parse_realtime(fh.read(), self.cfg.distance_unit)
        except (OSError, ValueError) as e:
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

    def _write_record(self, now, exact=False):
        end = int(now) if exact else self._interval_floor(now)
        if end <= self.interval_start:
            return
        if exact and not any(self.pending.values()):
            return
        r = self.last_reading or {}
        rec = {
            'dateTime': end,
            'usUnits': weewx.METRIC,
            'interval': max(1, (end - self.interval_start) // 60),   # 0 is reserved for the 'earlier today' record
            'strike_rate': r.get('strikes_per_minute'),
            'max_rate': self.max_rate,
            'min_distance': self.min_distance,
            'peak_rate': r.get('peak_rate'),
            'last_distance': r.get('last_distance_km'),
            'last_bearing': r.get('last_bearing'),
            'last_type': r.get('last_type'),
            'last_strike': r.get('last_strike_time'),
            'avg_distance': r.get('avg_distance_km'),
            'tendency': r.get('tendency'),
            'alarm': r.get('alarm'),
        }
        for src, col, sess in self.COUNTERS:
            rec[col] = self.pending.get(col, 0)
            rec[sess] = self.last_counts.get(src)
        try:
            self.dbm.addRecord(rec)
        except Exception as e:
            log.error("Boltek lightning: couldn't save record: %s" % e)
            return
        self.pending = dict((col, 0) for _src, col, _sess in self.COUNTERS)
        self._reset_interval_extremes()
        self.interval_start = end
        self._refresh_totals()
        self._write_history()

    def _recent(self, now, seconds):
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

    def _publish(self, now):
        lt = time.localtime(now)
        if self.totals_day != (lt.tm_year, lt.tm_yday):
            self._refresh_totals()   # new day (or year): restart the counts
        r = self.last_reading
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
                os.path.basename(self.cfg.realtime_file), (now - newest) // 60)
        data = {
            'version': BOLTEK_VERSION,
            'generated': int(now),
            'source_file': os.path.basename(self.cfg.realtime_file),
            'file_found': os.path.exists(self.cfg.realtime_file),
            'file_time': file_time,
            'stale': stale,
            'error': error,
            'realtime': r,
            'totals': {
                'today': self.totals['today'] + pending,
                'year': self.totals['year'] + pending,
                'alltime': self.totals['alltime'] + pending,
                # From the database (5-minute records) plus what's been
                # counted since the last one. NexStorm's own layout doesn't
                # include these counts.
                'last_15min': self._recent(now, 900) + pending,
                'last_30min': self._recent(now, 1800) + pending,
                'last_hour': self._recent(now, 3600) + pending,
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
               # Hourly: leave out the 'earlier today' record (interval 0,
               # see _seed_from_today) -- its strikes have no real hour.
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
