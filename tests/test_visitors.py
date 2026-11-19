"""divumwx_visitors.py against a fake Cloudflare API.  python3 tests/test_visitors.py"""
import json, os, re, sys, tempfile, threading, time, http.server, socketserver, logging
from datetime import date, timedelta
sys.path.insert(0, os.path.join(os.path.dirname(__file__), 'stubs'))
sys.path.insert(0, os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'bin', 'user'))
import divumwx_visitors as V
logging.basicConfig(level=logging.WARNING, format='  log: %(message)s')
fails = 0
def check(c, m):
    global fails; print(('ok    ' if c else 'FAIL  ') + m); fails += 0 if c else 1

ACCOUNT = 'a' * 32
SITE = 'b' * 32
today = date.today()
seen = []          # queries received
cfg = {'accounts': [{'id': ACCOUNT, 'name': 'Station'}], 'bot_ok': True, 'token': 'good', 'datetime_ok': True}

def g(count, visits, **dims):
    return {'count': count, 'sum': {'visits': visits}, 'dimensions': dims}

def answer(q):
    if 'bot:' in q and not cfg['bot_ok']:
        return {'data': None, 'errors': [{'message': 'unknown field "bot"'}]}
    if 'datetime_geq' in q and not cfg['datetime_ok']:
        return {'data': None, 'errors': [{'message': 'unknown arg datetime_geq'}]}
    acct = {}
    if re.search(r'\bsites:', q):
        acct['sites'] = [g(500, 90, siteTag=SITE, requestHost='weather.example.com'),
                         g(40, 10, siteTag='c' * 32, requestHost='blog.example.com')]
    if re.search(r'\bdaily:', q):
        acct['daily'] = [dict(g(10 + i, 3 + i % 4, date=(today - timedelta(days=i)).isoformat()),
                              avg={'sampleInterval': 1}) for i in range(30)]
        acct['pages'] = [g(200, 60, requestPath='/divumwx/'), g(50, 5, requestPath='/divumwx/charts-d3.html')]
        acct['countries'] = [g(150, 40, countryName='GB'), g(30, 9, countryName='DE')]
        acct['referrers'] = [g(120, 0, refererHost='weather.example.com'), g(80, 30, refererHost=''),
                             g(20, 12, refererHost='www.google.com')]
        acct['browsers'] = [g(180, 50, userAgentBrowser='Chrome')]
        acct['os'] = [g(100, 30, userAgentOS='Windows')]
        acct['devices'] = [g(160, 44, deviceType='desktop'), g(90, 21, deviceType='mobile')]
        acct['hosts'] = [g(250, 65, requestHost='weather.example.com')]
    if re.search(r'\btoday:', q):
        acct['today'] = [{'count': 7, 'sum': {'visits': 2}}]
    return {'data': {'viewer': {'accounts': [acct]}}, 'errors': None}

class H(http.server.BaseHTTPRequestHandler):
    def log_message(self, *a): pass
    def _send(self, code, doc):
        b = json.dumps(doc).encode(); self.send_response(code)
        self.send_header('Content-Type', 'application/json'); self.send_header('Content-Length', str(len(b)))
        self.end_headers(); self.wfile.write(b)
    def _auth(self):
        if self.headers.get('Authorization') != 'Bearer ' + cfg['token']:
            self._send(403, {'success': False, 'errors': [{'code': 10000, 'message': 'Authentication error'}]}); return False
        return True
    def do_GET(self):
        if self._auth(): self._send(200, {'success': True, 'result': cfg['accounts']})
    def do_POST(self):
        q = json.loads(self.rfile.read(int(self.headers['Content-Length'])))['query']; seen.append(q)
        if self._auth(): self._send(200, answer(q))

srv = socketserver.TCPServer(('127.0.0.1', 0), H); port = srv.server_address[1]
threading.Thread(target=srv.serve_forever, daemon=True).start()
API = 'http://127.0.0.1:%d' % port

# 1. everything found from the token and hostname
v = V.Visitors('good', hostname='weather.example.com', api_base=API)
doc = v.fetch()
check(v.state['account_id'] == ACCOUNT and v.state['site_tag'] == SITE, 'account and site tag found from token + hostname')
check(doc['today'] == {'views': 7, 'visits': 2}, 'today = since local midnight')
check(doc['week']['views'] == sum(10 + i for i in range(7)), '7-day page views summed')
check(len(doc['daily']) == 30 and doc['daily'][-1]['date'] == today.isoformat(), '30-day series ends today')
check([r['name'] for r in doc['referrers']] == ['', 'www.google.com'], "own host dropped from referrers, direct kept")
check(doc['countries'][0] == {'name': 'GB', 'views': 150, 'visits': 40}, 'countries')
check(doc['error'] is None and not doc['sampled'], 'no error, not sampled')
check(all('bot: 0' in q for q in seen if 'daily:' in q), 'bots filtered out')
check(all('siteTag: "%s"' % SITE in q for q in seen if 'daily:' in q), 'queries filtered to the site')

# 2. no hostname: busiest site chosen, with a note
v2 = V.Visitors('good', api_base=API); d2 = v2.fetch()
check(v2.state['site_tag'] == SITE and d2['notes'] and 'busiest' in d2['notes'][0], 'two sites, no hostname: busiest + note')

# 3. hostname with no data: clear message
try:
    V.Visitors('good', hostname='nope.example.com', api_base=API).fetch(); check(False, 'unknown hostname raises')
except V.CloudflareError as e:
    check('weather.example.com' in str(e), 'unknown hostname: lists the hosts Cloudflare has')

# 4. bad token
try:
    V.Visitors('bad', api_base=API).fetch(); check(False, 'bad token raises')
except V.CloudflareError as e:
    check('Account Analytics' in str(e), 'bad token: says which permission is needed')

# 5. two accounts: asks for account_id
cfg['accounts'].append({'id': 'd' * 32, 'name': 'Other'})
try:
    V.Visitors('good', api_base=API).fetch(); check(False, 'two accounts raises')
except V.CloudflareError as e:
    check('account_id' in str(e), 'two accounts: asks for account_id')
cfg['accounts'].pop()

# 6. schema without bot / datetime: falls back
cfg['bot_ok'] = False; cfg['datetime_ok'] = False
d6 = V.Visitors('good', hostname='weather.example.com', api_base=API).fetch()
check(d6['today'] == {'views': 10, 'visits': 3}, 'no bot/datetime filters: still works, UTC day for today')
cfg['bot_ok'] = True; cfg['datetime_ok'] = True

# 7. the service: writes visitors.json and state, keeps figures on a later error
d = tempfile.mkdtemp()
conf = {'WEEWX_ROOT': d, 'StdReport': {'HTML_ROOT': d}, 'Station': {'station_url': 'https://weather.example.com/'},
        'DivumWXVisitors': {'api_token': 'good', 'site_root': '', 'api_base': API}}
svc = V.VisitorsService(None, conf); time.sleep(1.0); svc.shutDown()
out = json.load(open(d + '/jsondata/visitors.json'))
st = json.load(open(d + '/archive/divumwx_visitors_state.json'))
check(out['month']['views'] > 0 and out['host'] == 'weather.example.com', 'service wrote visitors.json (hostname from station_url)')
check(st['site_tag'] == SITE and len(st['days']) == 30, 'state keeps the site tag and daily history')
check(oct(os.stat(d + '/jsondata/visitors.json').st_mode)[-3:] == '644', 'visitors.json readable by the web server')
cfg['token'] = 'changed'
svc.update(); out2 = json.load(open(d + '/jsondata/visitors.json'))
check(out2['error'] and out2['month'] == out['month'], 'on error: last figures kept, error shown')
check('good' not in json.dumps(out2), 'token never published')
cfg['token'] = 'good'

# 8. no token: does nothing
d8 = tempfile.mkdtemp()
V.VisitorsService(None, {'WEEWX_ROOT': d8, 'StdReport': {'HTML_ROOT': d8}, 'DivumWXVisitors': {}}); time.sleep(0.3)
check(not os.path.exists(d8 + '/divumwx/jsondata/visitors.json'), 'no api_token: not running')

# 9. history beyond 30 days carries into total and monthly
v9 = V.Visitors('good', hostname='weather.example.com', api_base=API,
                state={'days': {(today - timedelta(days=90)).isoformat(): [100, 40]}})
d9 = v9.fetch()
check(d9['total']['views'] == 100 + sum(10 + i for i in range(30)) and d9['total']['since'] == (today - timedelta(days=90)).isoformat(),
      'total includes older history')
check(len(d9['monthly']) >= 2, 'monthly totals')

srv.shutdown()
print('\n%s' % ('all passed' if not fails else '%d FAILED' % fails))
sys.exit(1 if fails else 0)
