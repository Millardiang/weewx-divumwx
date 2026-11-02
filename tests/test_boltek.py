"""Tests for divumwx_boltek.py with NSD Bridge's NSDRealtime.json."""
import json, os, sqlite3, sys, tempfile, threading, time, http.server, socketserver
sys.path.insert(0, os.path.join(os.path.dirname(__file__), 'stubs'))
sys.path.insert(0, os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'bin', 'user'))
import divumwx_boltek as B

class FakeDB(object):
    """Enough of weewx.manager.Manager on sqlite3."""
    def __init__(self):
        self.c = sqlite3.connect(':memory:')
        self.c.execute('CREATE TABLE archive (%s)' % ', '.join('%s %s' % x for x in B.schema))
    def getSql(self, sql, args=()):
        return self.c.execute(sql, args).fetchone()
    def genSql(self, sql, args=()):
        return self.c.execute(sql, args).fetchall()
    def execute(self, sql, args):
        self.c.execute(sql, args); self.c.commit()
    def addRecord(self, rec):
        cols = list(rec); self.c.execute('INSERT OR REPLACE INTO archive (%s) VALUES (%s)' % (','.join(cols), ','.join('?'*len(cols))), [rec[k] for k in cols]); self.c.commit()

class Cfg(object):
    archive_interval = 300; poll_interval = 10; stale_after = 600; close_km = 16.0
    station = (51.4545, -2.5879); source_is_url = False
    def __init__(self, d):
        self.ngxarchive_file = os.path.join(d, 'NSDRealtime.json')
        self.json_file = os.path.join(d, 'boltek.json'); self.history_file = os.path.join(d, 'hist.json')

def nsd_doc(now, strikes, today=None):
    return {"timestamp": int(now), "strikes_today": len(strikes) if today is None else today, "rate_per_min": 0,
            "station": {"lat": 51.4545, "lon": -2.5879, "distance_unit": "km"},
            "strikes_window": "today", "strikes_count": len(strikes),
            "strikes": sorted(strikes, key=lambda s: -s['timestamp'])}

def stk(t, d=30, b=200.0, typ='cg-', pos=True):
    import math
    s = {"timestamp": int(t), "direction": int(b), "bearing": b, "distance": d, "type": typ, "lat": None, "lon": None}
    if pos:
        # same formula as NSD Bridge
        r=6371.0; p1,l1,th,dl=math.radians(51.4545),math.radians(-2.5879),math.radians(b),d/r
        p2=math.asin(math.sin(p1)*math.cos(dl)+math.cos(p1)*math.sin(dl)*math.cos(th))
        l2=l1+math.atan2(math.sin(th)*math.sin(dl)*math.cos(p1),math.cos(dl)-math.sin(p1)*math.sin(p2))
        s['lat'], s['lon'] = round(math.degrees(p2),4), round(math.degrees(l2),4)
    return s

fails = 0
def check(cond, msg):
    global fails
    print(('ok    ' if cond else 'FAIL  ') + msg)
    if not cond: fails += 1

# --- format detection & parsing
now = time.time()
doc = nsd_doc(now, [stk(now-5, 30, 200.0, 'cg-'), stk(now-3, 10, 90.0, 'cc+'), stk(now-1, 120, 270.0, 'cg+', pos=False)])
r = B.parse_source(json.dumps(doc), (51.4545, -2.5879))
check(r['format'] == 'nsdbridge' and len(r['strikes']) == 3 and r['strike_count'] == 3, 'NSDRealtime.json recognised and read')
s0, s1, s2 = r['strikes']
check(s0['type'] == B.CG_TYPE and s0['polarity'] == 0 and s0['label'] == '-CG' and not s0['ic'], 'cg- -> CG, polarity 0, label -CG')
check(s1['type'] == B.IC_TYPE and s1['ic'] and s1['label'] == '+IC', 'cc+ -> in-cloud, label +IC')
check(abs(s0['km'] - 30) < 0.2 and abs(s0['bearing'] - 200) < 0.2, 'distance/bearing re-measured from lat/lon (%.1f km, %.1f deg)' % (s0['km'], s0['bearing']))
check(s2['km'] == 120 and s2['bearing'] == 270 and s2['lat'] is None, 'strike without position uses NSD distance/bearing')
mi = nsd_doc(now, [stk(now, 10, 0.0, pos=False)]); mi['station']['distance_unit'] = 'mi'
check(abs(B.parse_source(json.dumps(mi))['strikes'][0]['km'] - 16.1) < 0.05, 'miles converted to km')
ngx = {"TimestampEpoch": int(now*1000), "StrikeCount": 1, "StrikeData": [{"millis": int(now*1000), "dist": 5, "bng": 10, "type": 1, "polarity": 1}]}
check(B.parse_source(json.dumps(ngx))['format'] == 'ngxarchive', 'ngxarchive.json still recognised')
try:
    B.parse_source(json.dumps({"timestamp": 1, "strikes_today": 3})); check(False, 'missing list rejected')
except ValueError as e:
    check('realtime_json_strikes' in str(e), 'JSON without strike list explains the setting')
try:
    B.parse_source('{"foo": 1}'); check(False, 'unknown rejected')
except ValueError:
    check(True, 'unknown JSON rejected')

# --- recorder end to end
d = tempfile.mkdtemp()
cfg = Cfg(d)
clock = [time.time()]
base = clock[0] - 3000
strikes = [stk(base + i*60, 8 if i % 5 == 0 else 40, 200.0 + i, 'cc-' if i % 3 == 0 else 'cg+') for i in range(30)]
# two strokes of one flash, same second & place
strikes.append(dict(strikes[-1])); strikes.append(dict(strikes[-1]))
json.dump(nsd_doc(clock[0], strikes), open(cfg.ngxarchive_file, 'w'))
db = FakeDB()
rec = B.BoltekRecorder(cfg, db, clock=lambda: clock[0])
rec.tick()
out = json.load(open(cfg.json_file))
tot = db.getSql('SELECT SUM(strikes), SUM(cg), SUM(ic), SUM(close) FROM archive')
pend = rec.pending
all_ = (tot[0] or 0) + pend['strikes']; ic = (tot[2] or 0) + pend['ic']; cg = (tot[1] or 0) + pend['cg']
check(all_ == 32, 'all 32 strikes counted, incl. both repeated strokes (%d)' % all_)
check(ic == 10 and cg == 22, 'IC and CG split counted (cg %d, ic %d)' % (cg, ic))
check(out['realtime']['format'] == 'nsdbridge' and out['realtime']['strike_count'] == 32, 'boltek.json: format nsdbridge, strike_count = strikes_today')
check(out['realtime']['last_type_label'] in ('+CG', '-CG', '+IC', '-IC') and out['realtime']['last_lat'] is not None, 'last strike label and position in boltek.json')
check(len(out['realtime']['recent_strikes']) == 32 and out['realtime']['recent_strikes'][0][5] in ('+CG','-IC','+IC','-CG'), 'recent_strikes listed newest first')
check(out['stale'] is False and out['error'] is None, 'not stale, no error')
check(out['totals']['today'] >= 32 or time.localtime(base).tm_mday != time.localtime(clock[0]).tm_mday, 'today total (%s)' % out['totals']['today'])

# rewrite with same strikes + 2 new: only the 2 counted
clock[0] += 10
strikes2 = strikes + [stk(clock[0]-2, 12, 45.0, 'cg-'), stk(clock[0]-1, 300, 10.0, 'cc+')]
json.dump(nsd_doc(clock[0], strikes2), open(cfg.ngxarchive_file, 'w'))
os.utime(cfg.ngxarchive_file, (clock[0], clock[0]))
rec.tick()
tot = db.getSql('SELECT SUM(strikes) FROM archive'); all2 = (tot[0] or 0) + rec.pending['strikes']
check(all2 == 34, 'rewrite: only the 2 new strikes added (%d)' % all2)

# restart: nothing counted twice
rec._write_record(clock[0], exact=True)
clock[0] += 10
os.utime(cfg.ngxarchive_file, (clock[0], clock[0]))
rec2 = B.BoltekRecorder(cfg, db, clock=lambda: clock[0])
rec2.tick()
tot = db.getSql('SELECT SUM(strikes) FROM archive'); all3 = (tot[0] or 0) + rec2.pending['strikes']
check(all3 == 34, 'after a restart nothing is counted twice (%d)' % all3)
check(rec2.last_strike.get('label') in ('+IC', '-IC', '+CG', '-CG'), 'last strike label after restart: %s' % rec2.last_strike.get('label'))

# history file has ic column filled
h = json.load(open(cfg.history_file))
check(sum(r[3] for r in h['hourly']) >= 10, 'history: hourly IC totals present')

# --- URL source (NSD Bridge's web server)
class H(http.server.SimpleHTTPRequestHandler):
    def __init__(self, *a, **k): super().__init__(*a, directory=d, **k)
    def log_message(self, *a): pass
srv = socketserver.TCPServer(('127.0.0.1', 0), H); port = srv.server_address[1]
threading.Thread(target=srv.serve_forever, daemon=True).start()
cfg2 = Cfg(d); cfg2.source_is_url = True; cfg2.ngxarchive_file = 'http://127.0.0.1:%d/NSDRealtime.json' % port
cfg2.json_file = os.path.join(d, 'boltek2.json'); cfg2.history_file = os.path.join(d, 'hist2.json')
db2 = FakeDB(); rec3 = B.BoltekRecorder(cfg2, db2, clock=lambda: clock[0]); rec3.tick()
o3 = json.load(open(cfg2.json_file))
n3 = (db2.getSql('SELECT SUM(strikes) FROM archive')[0] or 0) + rec3.pending['strikes']
check(n3 == 34 and o3['file_found'] and o3['error'] is None and o3['source_file'].startswith('http'), 'reads over HTTP (%d strikes)' % n3)
check(rec3._read() is None, 'unchanged URL content skipped')
cfg2.ngxarchive_file = 'http://127.0.0.1:1/NSDRealtime.json'
rec3.tick(); o4 = json.load(open(cfg2.json_file))
check(o4['error'] and 'fetch' in o4['error'], 'unreachable URL reported: %s' % o4['error'])
srv.shutdown()

# --- strike map gets every strike from the last 2 hours via boltek.json
now2 = time.time()
many = [{'t': int(now2 - 7200 + i * 2), 'km': 10.0, 'bearing': 1.0, 'lat': 1.0, 'lon': 1.0, 'label': '+CG'} for i in range(3500)]
old = [{'t': int(now2 - 20000 + i), 'km': 1.0, 'bearing': 1.0, 'lat': 1.0, 'lon': 1.0, 'label': '+CG'} for i in range(300)]
m = B.BoltekRecorder._map_strikes(old + many, now2)
check(len(m) == 3499 and m[0]['t'] > now2 - 7200, 'map list: every strike of the last 2 h (%d), none older' % len(m))
m = B.BoltekRecorder._map_strikes(old + many[-10:], now2)
check(len(m) == 100, 'map list: at least the newest 100 when the last 2 h are quiet (%d)' % len(m))
big = [{'t': int(now2 - 3600 + i * 0.5), 'km': 1.0} for i in range(7000)]
m = B.BoltekRecorder._map_strikes(big, now2)
check(len(m) == B.MAP_MAX and m[-1] is big[-1], 'map list capped at %d, newest kept' % B.MAP_MAX)

print('\n%d failure(s)' % fails); sys.exit(1 if fails else 0)
