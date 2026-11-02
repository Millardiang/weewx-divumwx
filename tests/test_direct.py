import json, os, sys, tempfile, time, threading, http.server, socketserver, logging
sys.path.insert(0, os.path.join(os.path.dirname(__file__), 'stubs'))
sys.path.insert(0, os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'bin', 'user'))
import weewx.manager
opened = []
weewx.manager.open_manager_with_config = lambda *a, **k: opened.append(1) or (_ for _ in ()).throw(RuntimeError('db'))
import divumwx_boltek as B
logging.basicConfig(level=logging.INFO, format='  log: %(message)s')
fails = 0
def check(c, m):
    global fails; print(('ok    ' if c else 'FAIL  ') + m); fails += 0 if c else 1

def conf(d, **bl):
    b = {'site_root': '', 'poll_interval': '2'}; b.update(bl)
    return {'WEEWX_ROOT': d, 'StdReport': {'HTML_ROOT': d}, 'BoltekLightning': b}

# 1. convert = false, files supplied straight into jsondata: nothing touched
d = tempfile.mkdtemp(); os.makedirs(d + '/jsondata')
open(d + '/jsondata/boltek.json', 'w').write('{"mine": 1}')
svc = B.BoltekLightningService(None, conf(d, convert='false')); time.sleep(0.5); svc.shutDown()
check(open(d + '/jsondata/boltek.json').read() == '{"mine": 1}' and not os.path.exists(d + '/jsondata/boltek_history.json'), 'convert=false: supplied boltek.json left untouched')
check(not opened, 'convert=false: Boltek database not opened, source not read')

# 2. convert = false with sources elsewhere (a file and a URL)
src = tempfile.mkdtemp()
json.dump({'version': 'x', 'realtime': {'strike_count': 5}}, open(src + '/boltek.json', 'w'))
json.dump({'hourly': [[1, 2]]}, open(src + '/boltek_history.json', 'w'))
class H(http.server.SimpleHTTPRequestHandler):
    def __init__(self, *a, **k): super().__init__(*a, directory=src, **k)
    def log_message(self, *a): pass
srv = socketserver.TCPServer(('127.0.0.1', 0), H); port = srv.server_address[1]
threading.Thread(target=srv.serve_forever, daemon=True).start()
d2 = tempfile.mkdtemp()
svc = B.BoltekLightningService(None, conf(d2, convert='no', direct_json_source=src + '/boltek.json',
                                            direct_history_source='http://127.0.0.1:%d/boltek_history.json' % port))
time.sleep(1)
check(json.load(open(d2 + '/jsondata/boltek.json'))['realtime']['strike_count'] == 5, 'copied boltek.json from a path')
check(json.load(open(d2 + '/jsondata/boltek_history.json'))['hourly'] == [[1, 2]], 'copied boltek_history.json from a URL')
open(src + '/boltek.json', 'w').write('{"version": "x", "realt')   # half-written
time.sleep(2.5)
check(json.load(open(d2 + '/jsondata/boltek.json'))['realtime']['strike_count'] == 5, 'half-written source does not replace the good copy')
json.dump({'version': 'x', 'realtime': {'strike_count': 9}}, open(src + '/boltek.json', 'w'))
time.sleep(2.5)
check(json.load(open(d2 + '/jsondata/boltek.json'))['realtime']['strike_count'] == 9, 'changed source copied again')
svc.shutDown(); srv.shutdown()
check(not opened, 'still no database or conversion')

# 3. default (convert = true) still converts
svc = B.BoltekLightningService(None, conf(tempfile.mkdtemp())); time.sleep(0.5); svc.shutDown()
check(opened, 'convert=true (default): normal conversion path started')
print('\n%d failure(s)' % fails); sys.exit(1 if fails else 0)
