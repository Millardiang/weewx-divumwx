# $Id: divumwx.py mofied for DivumWX by Ian Millard based on crt.py by mwall $
# DivumWX WebServices added by Jerry Dietrich
# Copyright 2013-2016 Matthew Wall

"""Emit loop data to file in DivumWX realtime format.
Put this file in bin/user , then add this to your weewx.conf:
[Engine]
    [[Services]]
        archive_services = ..., user.divumwx.DivumWXRealTime
        report_services = ...., user.divumwx.SensorData
        xtype_services = ...., user.divumwx.LastNonZeroService
If no unit_system is specified, the units will be those of the database.
Units and other parameters may be specified:
[DivumWXRealTime]
    unit_system = (US | METRIC | METRICWX)
    wind_units = (meter_per_second | mile_per_hour | km_per_hour | knot)
    temperature_units = (degree_C | degree_F)
    pressure_units = (hPa | hPa | inHg)
    rain_units = (mm | inch)
    cloudbase_units = (foot | meter)
Note that most of the code in this file is to calculate/lookup data that
are not directly provided by weewx in a LOOP packet.
"""

# FIXME: consider in-memory caching so that database queries are not
#        necessary after the first invocation

import math
import os
import re
import sys
import json
import time
import weeutil.rsyncupload
try: import xmltodict
except: pass
from distutils.version import StrictVersion
try:
    import urllib2 as urllib
except ImportError:
    import urllib.request as urllib
try:
    from PIL import Image
except ImportError:
    pass
import functools
import threading
import socket
import subprocess

import weewx
import weewx.almanac
import weewx.manager
import weewx.wxformulas
import weeutil.weeutil
import weedb
from weewx.engine import StdService

try:
    # Test for new-style weewx logging by trying to import weeutil.logger
    import weeutil.logger
    import logging
    log = logging.getLogger(__name__)

    def logdbg(msg):
        log.debug(msg)

    def loginf(msg):
        log.info(msg)

    def logerr(msg):
        log.error(msg)

except ImportError:
    # Old-style weewx logging
    import syslog

    def logmsg(level, msg):
        syslog.syslog(level, 'dvmrt: %s' % msg)

    def logdbg(msg):
        logmsg(syslog.LOG_DEBUG, msg)

    def loginf(msg):
        logmsg(syslog.LOG_INFO, msg)

    def logerr(msg):
        logmsg(syslog.LOG_ERR, msg)

VERSION = "0.0.1"

REQUIRED_WEEWX = "4.6.0"
if StrictVersion(weewx.__version__) < StrictVersion(REQUIRED_WEEWX):
    raise weewx.UnsupportedFeature("weewx %s or greater is required, found %s"
                                   % (REQUIRED_WEEWX, weewx.__version__))

COMPASS_POINTS = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE',
                  'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW', 'N']

# map weewx unit names to divumwx unit names
UNITS_PRES = {'inHg': 'in', 'mbar': 'mb', 'hPa': 'hPa'}
UNITS_TEMP = {'degree_C': 'C', 'degree_F': 'F'}
UNITS_RAIN = {'inch': 'in', 'mm': 'mm'}
UNITS_WIND = {'mile_per_hour': 'mph',
              'meter_per_second': 'm/s',
              'km_per_hour': 'km/h',
              'knot': 'kts'}
UNITS_CLOUDBASE = {'foot': 'ft', 'meter': 'm'}
# categorize the weewx-to-divumwx unit mappings
UNITS = {'pressure': UNITS_PRES,
         'temperature': UNITS_TEMP,
         'rain': UNITS_RAIN,
         'wind': UNITS_WIND,
         'cloudbase': UNITS_CLOUDBASE}
SPEED_TO_RUN = {'mile_per_hour': 'mile',
                'meter_per_second': 'km',
                'km_per_hour': 'km',
                'knot': 'mile'}

def _convert(from_v, from_units, to_units, group):
    """Given a value, units and unit group convert to different units."""
    vt = (from_v, from_units, group)
    return weewx.units.convert(vt, to_units)[0]

def _convert_us(from_v, from_us, to_units, obs, group):
    """Given obs type, value, unit system and unit group convert to units."""
    from_units = weewx.units.getStandardUnitType(from_us, obs)[0]
    return _convert(from_v, from_units, to_units, group)

def clamp_degrees(x):
    """Convert a bearing to a DivumWX bearing.
    
    weewx uses 0.0 inclusive to 360.0 (exclusive) bearings whilst DivumWX 
    uses 0.0 (exclusive) to 360.0 (inclusive) bearings. When the wind is 
    calm DivumWX emits a wind direction of 0 degrees.
    """
    if x is not None:
        return x if x != 0.0 else 360.0
    return None

def degree_to_compass(x):
    try:
        if x is None:
            return '---'
        idx = int((x + 11.25) / 22.5)
        return COMPASS_POINTS[idx]
    except:
        return '---'

def get_db_units(dbm):
    val = dbm.getSql("SELECT usUnits FROM %s LIMIT 1" % dbm.table_name)
    return val[0] if val is not None else None

def calc_avg_windspeed(dbm, ts, interval=600):
    sts = ts - interval
    val = dbm.getSql("SELECT AVG(windSpeed) FROM %s "
                     "WHERE dateTime>? AND dateTime<=?" % dbm.table_name,
                     (sts, ts))
    return val[0] if val is not None else None

def calc_avg_winddir(dbm, ts, interval=600):
    sts = ts - interval
    val = dbm.getSql("SELECT AVG(windDir) FROM %s "
                     "WHERE dateTime>? AND dateTime<=?" % dbm.table_name,
                     (sts, ts))
    return clamp_degrees(val[0]) if val is not None else None

def calc_max_gust_10min(dbm, ts):
    sts = ts - 600
    val = dbm.getSql("SELECT MAX(windGust) FROM %s "
                     "WHERE dateTime>? AND dateTime<=?" % dbm.table_name,
                     (sts, ts))
    return val[0] if val is not None else None

def calc_avg_gust_10min(dbm, ts):
    sts = ts - 600
    val = dbm.getSql("SELECT AVG(windGust) FROM %s "
                     "WHERE dateTime>? AND dateTime<=?" % dbm.table_name,
                     (sts, ts))
    return val[0] if val is not None else None

def calc_avg_winddir_10min(dbm, ts):
    sts = ts - 600
    val = dbm.getSql("SELECT AVG(windDir) FROM %s "
                     "WHERE dateTime>? AND dateTime<=?" % dbm.table_name,
                     (sts, ts))
    return clamp_degrees(val[0]) if val is not None else None

def calc_windrun(dbm, ts, db_us):
    """Calculate windrun since midnight.  returns a value in the distance units
    of the database (thus the temporal normalization).
    """
    run = 0
    sod_ts = weeutil.weeutil.startOfDay(ts)
    for row in dbm.genSql("SELECT `interval`,windSpeed FROM %s "
                          "WHERE dateTime>? AND dateTime<=?" % dbm.table_name,
                          (sod_ts, ts)):
        if row[1] is not None:
            inc = row[1] * row[0]
            if db_us == weewx.METRICWX:
                inc *= 60.0
            else:
                inc /= 60.0
            run += inc
    return run

def get_trend(label, dbm, ts, n=3):
    """Calculate the trend over past n hours, default to 3 hour window."""
    lastts = ts - n * 3600
    old_val = dbm.getSql("SELECT %s FROM %s "
                         "WHERE dateTime>? AND dateTime<=?" %
                         (label, dbm.table_name), (lastts, ts))
    if old_val is None or old_val[0] is None:
        return None
    return old_val[0]

def calc_trend(newval, oldval):
    if newval is None or oldval is None:
        return None
    return newval - oldval

def calc_rain_hour(dbm, ts):
    sts = ts - 3600
    val = dbm.getSql("SELECT SUM(rain) FROM %s "
                     "WHERE dateTime>? AND dateTime<=?" % dbm.table_name,
                     (sts, ts))
    return val[0] if val is not None else None

def calc_rain_month(dbm, ts):
    span = weeutil.weeutil.archiveMonthSpan(ts)
    val = dbm.getSql("SELECT SUM(rain) FROM %s "
                     "WHERE dateTime>? AND dateTime<=?" % dbm.table_name,
                     (span.start, ts))
    return val[0] if val is not None else None

def calc_rain_year(dbm, ts):
    span = weeutil.weeutil.archiveYearSpan(ts)
    val = dbm.getSql("SELECT SUM(rain) FROM %s "
                     "WHERE dateTime>? AND dateTime<=?" % dbm.table_name,
                     (span.start, ts))
    return val[0] if val is not None else None

def calc_rain_yesterday(dbm, ts):
    ts = weeutil.weeutil.startOfDay(ts)
    sts = ts - 3600 * 24
    val = dbm.getSql("SELECT SUM(rain) FROM %s "
                     "WHERE dateTime>? AND dateTime<=?" % dbm.table_name,
                     (sts, ts))
    return val[0] if val is not None else None

def calc_rain_day(dbm, ts):
    sts = weeutil.weeutil.startOfDay(ts)
    val = dbm.getSql("SELECT SUM(rain) FROM %s "
                     "WHERE dateTime>? AND dateTime<=?" % dbm.table_name, 
                     (sts, ts))
    return val[0] if val is not None else None

def calc_ET_today(dbm, ts):
    sts = weeutil.weeutil.startOfDay(ts)
    val = dbm.getSql("SELECT SUM(ET) FROM %s "
                     "WHERE dateTime>? AND dateTime<=?" % dbm.table_name,
                     (sts, ts))
    return val[0] if val is not None else None

def calc_minmax(label, dbm, ts, minmax='MAX'):
    sts = weeutil.weeutil.startOfDay(ts)
    val = dbm.getSql("SELECT %s(%s) FROM %s "
                     "WHERE dateTime>? AND dateTime<=?" %
                     (minmax, label, dbm.table_name), (sts, ts))
    if val is None:
        return None, None
    t = dbm.getSql("SELECT dateTime FROM %s "
                   "WHERE dateTime>? AND dateTime<=? AND %s=?" %
                   (dbm.table_name, label), (sts, ts, val[0]))
    if t is None:
        return None, None
    tstr = time.strftime("%H:%M", time.localtime(t[0]))
    return val[0], tstr

def calc_is_daylight(alm):
    sunrise = alm.sunrise.raw
    sunset = alm.sunset.raw
    if sunrise < alm.time_ts < sunset:
        return 1
    return 0

def calc_daylight_hours(alm):
    sunrise = alm.sunrise.raw
    sunset = alm.sunset.raw
    if alm.time_ts <= sunrise:
        return 0
    elif alm.time_ts < sunset:
        return (alm.time_ts - sunrise) / 3600.0
    return (sunset - sunrise) / 3600.0

def calc_is_sunny(rad, max_rad, threshold):
    if not rad or not max_rad:
        return 0
    if rad <= threshold * max_rad:
        return 0
    return 1

# indication of sensor contact depens on the weather station.  if the station
# has more than one indicator, then indicate failure if contact is lost with
# any one of them.
#
# Vantage
#   packet['rxCheckPercent'] == 0
#
# FineOffset
#   packet['status'] & 0x40
#
# TE923
#   packet['sensorX_state'] == STATE_MISSING_LINK
#   packet['wind_state'] == STATE_MISSING_LINK
#   packet['rain_state'] == STATE_MISSING_LINK
#   packet['uv_state'] == STATE_MISSING_LINK
#
# WMR100
# WMR200
# WMR9x8
#
# WS28xx
#   packet['rxCheckPercent'] == 0
#
# WS23xx
#   packet['cn'] == 'lost'
#
def lost_sensor_contact(packet):
    if 'rxCheckPercent' in packet and packet['rxCheckPercent'] == 0:
        return 1
    if 'cn' in packet and packet['cn'] == 'lost':
        return 1
    if (('windspeed_state' in packet and packet['windspeed_state'] == 'no_link') or
        ('rain_state' in packet and packet['rain_state'] == 'no_link') or
        ('uv_state' in packet and packet['uv_state'] == 'no_link') or
        ('h_1_state' in packet and packet['h_1_state'] == 'no_link') or
        ('h_2_state' in packet and packet['h_2_state'] == 'no_link') or
        ('h_3_state' in packet and packet['h_3_state'] == 'no_link') or
        ('h_4_state' in packet and packet['h_4_state'] == 'no_link') or
        ('h_5_state' in packet and packet['h_5_state'] == 'no_link')):
        return 1
    return 0

def do_file_transfer(mode, rpath, conn, address, lpath, user, port):
    logdbg("do_file_transfer: Port = " + str(port))
    try:
        if mode == 'rsync':
            weeutil.rsyncupload.RsyncUpload(
                local_root=rpath if lpath == None else lpath,
                remote_root=rpath,
                server=address,
                user=user,
                port=int(port) if port != None else None,
                ssh_options= None,
                compress=False,
                delete=False,
                log_success=True if weewx.debug >= 1 else False).run()
            logdbg("do_file_transfer: Rsync complete")
        elif mode == 'socket':
            with open(rpath, 'r') as f:
                conn.sendall(f.read())
    except Exception as e:
        logerr("do_file_transfer " + str(e))
    finally:
        try:
            if conn != None:
                conn.close()
        except Exception as e:
            logdbg("do_file_transfer " + str(e))

def do_rsync_transfer(webserver_addresses, rpath, lpath, user, port):
    if len(webserver_addresses) > 0:
        for web_address in webserver_addresses:
            threading.Thread(target=do_file_transfer, args=("rsync", rpath, None, socket.gethostbyname(web_address), lpath, user, port)).start()
             
class ZambrettiForecast():
    DEFAULT_FORECAST_BINDING = 'forecast_binding'
    DEFAULT_BINDING_DICT = {
        'database': 'forecast_sqlite',
        'manager': 'weewx.manager.Manager',
        'table_name': 'archive',
        'schema': 'user.forecast.schema'}

    def __init__(self, config_dict):
        self.forecasting_installed = False
        self.db_max_tries = 3
        self.db_retry_wait = 3
        try:
            self.dbm_dict = weewx.manager.get_manager_dict(
                config_dict['DataBindings'],
                config_dict['Databases'],
                ZambrettiForecast.DEFAULT_FORECAST_BINDING,
                default_binding_dict=ZambrettiForecast.DEFAULT_BINDING_DICT)
            weewx.manager.open_manager(self.dbm_dict)
            self.forecasting_installed = True
        except (weedb.DatabaseError, weewx.UnsupportedFeature,
                weewx.UnknownBinding, KeyError):
            pass

    def is_installed(self):
        return self.forecasting_installed

    def get_zambretti_code(self):
        if not self.forecasting_installed:
            return 0

        # FIXME: add api to forecast instead of doing all the work here
        with weewx.manager.open_manager(self.dbm_dict) as dbm:
            sql = "select dateTime,zcode from %s where method = 'Zambretti' order by dateTime desc limit 1" % dbm.table_name
#            sql = "select zcode from %s where method = 'Zambretti' and dateTime = (select max(dateTime) from %s where method = 'Zambretti')" % (dbm.table_name, dbm.table_name)
            for count in range(self.db_max_tries):
                try:
                    record = dbm.getSql(sql)
                    if record is not None:
                        return ZambrettiForecast.alpha_to_number(record[1])
                except Exception as e:
                    logerr('get zambretti failed (attempt %d of %d): %s' %
                           ((count + 1), self.db_max_tries, e))
                    logdbg('waiting %d seconds before retry' %
                           self.db_retry_wait)
                    time.sleep(self.db_retry_wait)
        return 0

    # given a zambretti letter code A-Z, convert to digit 1-26
    @staticmethod
    def alpha_to_number(x):
        return ord(x) - 64
      
class ForecastData():    
    def __init__(self, config_dict, webserver_addresses):
        self.settings_dict = config_dict.get('DivumWXWebServices', {})
        if self.settings_dict == None or len(self.settings_dict) == 0:
            raise Exception("ForecastData section not found")
        services_str = self.settings_dict.get("services")
        if services_str == None or len(services_str) == 0:
            raise Exception("ForecastData No services found")
        self.services_dict = {} 
        for w in services_str.split("."):
            self.services_dict[w] = (int(self.settings_dict.get(w + "_interval", "3600")), int(time.time()/2))
        self.html_root = config_dict['StdReport']['DivumWXReport'].get('HTML_ROOT', '')
        self.remote_html_root = config_dict['DivumWXRealTime'].get('HTML_ROOT', self.html_root)
        if len(self.remote_html_root) == 0:
            self.remote_html_root = self.html_root
        self.user = config_dict['StdReport']['RSYNC'].get('user', None) 
        self.rsync_port = config_dict['StdReport']['RSYNC'].get('port', None) 
        self.webserver_addresses = webserver_addresses

    def monitor_webservices(self):
        while True:
            check_rsync = False
            services = []
            current_time = int(time.time())
            for s in self.services_dict:
                if self.services_dict[s][1] + self.services_dict[s][0] < current_time:
                    services.append(s) 
                    self.services_dict[s] = (self.services_dict[s][0], current_time)
            while services:
                service = services.pop(0)
                url = self.settings_dict.get(service + "_url")
                try: service_xml = self.settings_dict.get(service + "_xml_data", False)
                except: service_xml = False
                header = self.settings_dict.get(service + "_header", "User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/98.0.4758.102 Safari/537.36")
                header = {hdr[0:hdr.index(":")].strip():hdr[hdr.index(":")+1:].strip() for hdr in header.split("|")}
                try:
                    if url == None or header == None:
                        logerr("Error Invalid Webservice Data: %s, %s" % (url, header))
                        continue
                    if isinstance(url, list):
                        url = ",".join(url)
                    filename = os.path.join(self.html_root, "jsondata", service + ".txt")
                    lfilename = filename if len(self.webserver_addresses) == 0 else os.path.join("/tmp/divumwx/jsondata", os.path.basename(filename)) 
                    if len(self.webserver_addresses) > 0  and not os.path.exists(os.path.dirname(lfilename)):
                        os.mkdir(os.path.dirname(lfilename), 0o777)
                    loginf("Web Service: %s is running" % (service,))
                    try:
                        response = urllib.urlopen(urllib.Request(url, None, header), timeout = 15).read().decode('utf-8')
                        if service_xml:
                            try:
                                response = json.dumps(xmltodict.parse(response))
                            except Exception as err:
                                response = ""
                                logerr("Web service " + service + " Failed XML2Json parsing with error: " + err)
                        try:
                            with open(lfilename, 'w+') as file_handle:
                                file_handle.write(str(response))
                        except Exception as err:
                            logerr("Error writing web service file: %s, Error: %s" % (lfilename, err))
                            continue
                    except Exception as err:
                        logerr("Failed getting web service data. URL: %s Header: %s, Error: %s" % (url, header, err))
                        continue
                    finally:
                        try: response.close()
                        except: pass
                    check_rsync = True
                except Exception as err:
                    logerr("Failed to run webservice: %s, Error: %s" % (service, err)) 
            if check_rsync:
                do_rsync_transfer(self.webserver_addresses, os.path.join(self.remote_html_root, "jsondata/"), os.path.dirname(lfilename), self.user, self.rsync_port)
            time.sleep(60)

class CloudCover():
    def __init__(self, weewx_dict):
        if weewx_dict.get('DivumWXCloudCover').get('enable').upper() != 'TRUE':
            raise Exception("Not Enabled")
        self.cloud_cover_percent = 0.0
        try:
            self.cloud_field = weewx_dict.get('DivumWXCloudCover').get('db_field')
            thread = threading.Thread(target = self.calculate_cloud_cover, args = (weewx_dict.get('DivumWXCloudCover'),))
            thread.daemon = True
            thread.start()
            logdbg("CloudCover service has started")
        except Exception as err:
            raise Exception(err)

    def update_cloud_cover(self, event):
        try:
            event.record[self.cloud_field] = self.cloud_cover_percent
            logdbg("CloudCover: Percent value is " + str(self.cloud_cover_percent))
        except Exception as e:
            logerr("CloudCover: Cannot read value: %s" % e)
    
    def calculate_cloud_cover(self, settings_dict):
        try:
            url1 = settings_dict.get('cc1_url')
            parts = url1.split("=")
            lat = parts[2].split("&")[0]
            lon = parts[3].split("&")[0]
            url2 = settings_dict.get('cc2_url')
            file1 = "/tmp/divumwx/sat1.png"
            file2 = "/tmp/divumwx/sat2.png"
            time_interval = int(settings_dict.get('cc_interval', 600))
            logdbg("CloudCover Url 1 " + url1)
            logdbg("CloudCover Url 2 " + url2)
            logdbg("CloudCover File 1 " + file1)
            logdbg("CloudCover File 2 " + file2)
            while True:
                logdbg("CloudCover url1 exit code " + str(subprocess.call("wget -r -O " + "'" + file1 + "'" + " '" + url1 + "'", shell = True)))
                os.chmod(file1, 0o666)
                logdbg("CloudCover url2 exit code " + str(subprocess.call("wget -r -O " + "'" + file2 + "'" + " '" + url2 + "'", shell = True)))
                os.chmod(file2, 0o666)
                if os.stat(file1).st_size > 5000 and os.stat(file2).st_size > 5000:
                    alt = weewx.almanac.Almanac(time.time(), float(lat), float(lon)).sun.alt
                    pixarray = []
                    if alt > 5:
                        f = open(file1, 'rb')
                        im = Image.open(f)
                        xpos1 = 149
                        xpos2 = 155
                        ypos1 = 145
                        ypos2 = 155
                        min = 80
                        max = 250
                    else:
                        f = open(file2, 'rb')
                        im = Image.open(f)
                        xpos1 = 148
                        xpos2 = 152
                        ypos1 = 148
                        ypos2 = 152
                        min = 100
                        max = 250
                    pix = im.convert('L').load()
                    im.close()
                    f.close()
                    for y in range(ypos1,ypos2):
                        for x in range(xpos1,xpos2):
                            pixarray.append(pix[x,y])
                    pixavg = sum(pixarray) / float(len(pixarray))
                    self.cloud_cover_percent = (pixavg - min)*100 / (max - min)
                    if self.cloud_cover_percent < 1:
                        self.cloud_cover_percent = 1
                    if self.cloud_cover_percent > 99:
                        self.cloud_cover_percent = 99
                    im = None
                    pixarray = None
                time.sleep(time_interval)
        except Exception as e:
            logdbg("CloudCover:calculate_cloud_cover " + str(e))
               
class Webserver():
    def __init__(self, config_dict, webserver_addresses):
        self.webserver_addresses = webserver_addresses
        thread = threading.Thread(target=self.listen_data_requests, args=(int(config_dict['DivumWXRealTime'].get('weewx_port', '25252')), config_dict['DivumWXRealTime'].get('weewxserver_address', '')))
        thread.daemon = True
        thread.start()

    def listen_data_requests(self, port, host):
        #Wait for system startup before getting host ip address
        time.sleep(10)
        while True:
            try:
                if len(host) < 5:
                    host = socket.gethostbyname(socket.gethostname())
                    if host.startswith('127.'):
                        host = subprocess.check_output(['hostname', '-s', '-I']).split(b" ")[0].decode()
                else:
                    host = socket.gethostbyname(host)
                s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
                s.bind((host, port))
                s.listen(2)
                logdbg("Webserver: weewx host ip " + host + " listening on port " + str(port))
                break
            except:
                time.sleep(5)
        while True:
            try:
                conn, address = s.accept()
                webserver_address = str(address).split(",")[0][1:].replace("'","")
                if webserver_address != host:
                    self.webserver_addresses[webserver_address] = conn
                    logdbg("Webserver: webserver ip " + webserver_address)
                recv_data = conn.recv(256)
                if recv_data:
                    thread = threading.Thread(target=self.execute_report, args=(str(recv_data).split(" "), conn, webserver_address))
                    thread.daemon = True
                    thread.start()
                else:
                    conn.close();
            except Exception as e:
                if not "time" in str(e):
                    logdbg("Webserver Error in listen_data_requests:" + str(e))
    
    def execute_report(self, args, conn, address):
        import weecfg
        import weewx.station
        import weewx.reportengine
        try:
            _config_path, config_dict = weecfg.read_config(None, None)
            ts = float(args[1])
            html_root = args[3]
            stn_info = weewx.station.StationInfo(**config_dict['Station'])
            save_entries = ["SKIN_ROOT","HTML_ROOT","RSYNC","data_binding","log_success","log_failure","dvmHighcharts"]
            for key in config_dict['StdReport']:
                if key not in save_entries:
                    del config_dict['StdReport'][key]
            config_dict['StdReport']['dvmHighcharts']['skin'] = 'dvmHighcharts-day'
            config_dict['StdReport']['dvmHighcharts']['CheetahGenerator'] =  {'search_list_extensions': 'user.dvmhighchartsSearchX.dvmhighcharts_' + args[2].split('/')[-1].split('.')[0], 'encoding': 'strict_ascii', 'ToDate': {'DayJSON': {'template': args[2],'HTML_ROOT': html_root}}}
            try:
                binding = config_dict['StdArchive']['data_binding']
            except KeyError:
                binding = 'wx_binding'
            with weewx.manager.DBBinder(config_dict) as db_binder:
                db_manager = db_binder.get_manager(binding)
                for _ in range(1500):
                    record = db_manager.getRecord(ts)
                    if record == None:
                        ts = ts + 60
                    else:
                        break;
                if record == None:
                    ts = db_manager.firstGoodStamp()
                    record = db_manager.getRecord(ts)
                weewx.reportengine.StdReportEngine(config_dict, stn_info, record=record, gen_ts=ts).run()
                logdbg("Webserver: Report complete")
                do_file_transfer(args[4], os.path.join(html_root,args[2].split(".tmpl")[0]), conn, address, None, config_dict['StdReport']['RSYNC'].get('user',None), config_dict['StdReport']['RSYNC'].get('port',None))
        except Exception as e:
            logerr("Webserver Error in execute_report: " + str(e))
       
class DivumWXRealTime(StdService):
    """Service retains previous loop packet values updating any value that isn't None from new
    packets. It then replaces the original packet with a new packet that contains all of the values."""

    def __init__(self, engine, config_dict):
        super(DivumWXRealTime, self).__init__(engine, config_dict)
        loginf("service version is %s" % VERSION)
        self.altitude_m = weewx.units.convert(engine.stn_info.altitude_vt, "meter")[0]
        self.latitude = engine.stn_info.latitude_f
        self.longitude = engine.stn_info.longitude_f
        self.config_dict = config_dict 
        self.html_root = config_dict['StdReport']['DivumWXReport'].get('HTML_ROOT', '')
        self.remote_html_root = config_dict['DivumWXRealTime'].get('HTML_ROOT', self.html_root)
        if len(self.remote_html_root) == 0:
            self.remote_html_root = self.html_root
        self.sunny_threshold = 0.75
        self.nonesub = 'NULL'
        loginf("'None' values will be displayed as %s" % self.nonesub)
        self.prev_archive_time = time.time()
        weewx_file_transfer = config_dict['DivumWXRealTime'].get('weewx_file_transfer', '')
        weewxserver_ip = config_dict['DivumWXRealTime'].get('weewxserver_address', '')
        if len(weewxserver_ip) == 0:
            try:
                weewxserver_ip = socket.gethostbyname(socket.gethostname())
            except:
                logerr("Gethostbyname failed for " + socket.gethostname())
        try:
            if weewxserver_ip.startswith('127.'):
                weewxserver_ip = subprocess.check_output(['hostname', '-s', '-I']).split(b" ")[0].decode()
        except:
            s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
            try:
                s.connect(('10.255.255.255', 1))
                weewxserver_ip = s.getsockname()[0]
            except:
                logerr("Cannot get local IP of weewx machine.")
            finally:
                s.close() 
        bin_path = os.path.dirname(os.path.realpath(__file__)).split("/user")[0]
        # source unit system is the database unit system
        self.db_us = None
        # initialise packet unit system
        self.pkt_us = None

        # get the unit system for display
        us = None
        us_label = config_dict['DivumWXRealTime'].get('unit_system', None)
        if us_label is not None:
            if us_label in weewx.units.unit_constants:
                loginf("units will be displayed as %s" % us_label)
                us = weewx.units.unit_constants[us_label]
            else:
                logerr("unknown unit_system %s" % us_label)
        self.unit_system = us

        # get any overrides to the display units
        self.units = dict()
        for x in UNITS:
            ukey = '%s_units' % x
            if ukey in config_dict['DivumWXRealTime']:
                if config_dict['DivumWXRealTime'][ukey] in UNITS[x]:
                    loginf("%s units will be displayed as %s" % (x, config_dict['DivumWXRealTime'][ukey]))
                    self.units[x] = config_dict['DivumWXRealTime'][ukey]
                else:
                    logerr("unknown unit '%s' for %s" % (config_dict['DivumWXRealTime'][ukey], ukey))

        # configure forecasting
        self.forecast = ZambrettiForecast(config_dict)
        loginf("zambretti forecast: %s" % self.forecast.is_installed())
    
        try:
            self.webserver_addresses = {}
            if len(self.webserver_addresses) == 0:
                addresses = config_dict['DivumWXRealTime'].get('webserver_address')
                if len(addresses) > 0 and not isinstance(addresses,list):
                    addresses = [addresses]
                if len(addresses) > 0:
                    for address in addresses:
                        self.webserver_addresses[address] = 0
            if len(self.webserver_addresses) > 0 and len(weewx_file_transfer) < 5:
                weewx_file_transfer = 'rsync'
            self.webserver_addresses = Webserver(config_dict, self.webserver_addresses).webserver_addresses
        except Exception as e:
            logdbg("DivumWXRealtime: Cannot get webserver addresses at startup due to error " + str(e))

        try:
            fc = ForecastData(config_dict, self.webserver_addresses)
            try:
                thread = threading.Thread(target = fc.monitor_webservices)
                thread.daemon = True
                thread.start()
            except Exception as err:
                logerr("Failed to start monitor_webservices thread. Error: " + err)
        except Exception as e:
            logerr("Forecast Not Installed " + str(e))
        
        try:
            self.cc = CloudCover(self.config_dict)
        except Exception as e:
            self.cc = None
            loginf("CloudCover Not Installed due to " + str(e))

        try:
            self.chk_lightning_cnt = True if config_dict['DivumWXRealTime'].get('chk_lightning_cnt').upper() == 'TRUE' else False; 
        except:
            self.chk_lightning_cnt = False

        try:
            self.cache_debug = True if config_dict['DivumWXRealTime'].get('cache_debug').upper() == 'TRUE' else False; 
        except:
            self.cache_debug = False

        try:
            lfilename = os.path.join(self.html_root, "serverdata","weewxserverinfo.txt") if len(self.webserver_addresses) == 0 else '/tmp/divumwx/serverdata/weewxserverinfo.txt'
            data = str(weewxserver_ip) + ":" + str(config_dict['DivumWXRealTime'].get('weewx_port', '25252')) + ":" + weewx_file_transfer + ":" + bin_path
            if len(self.webserver_addresses) > 0 and not os.path.exists(os.path.dirname(lfilename)):
                os.mkdir(os.path.dirname(lfilename), 0o777)
            with open(lfilename, 'w') as f:
                f.write(data)
            do_rsync_transfer(self.webserver_addresses, os.path.join(self.remote_html_root, "serverdata/"), os.path.dirname(lfilename), self.config_dict['StdReport']['RSYNC'].get('user', None), self.config_dict['StdReport']['RSYNC'].get('port',None))
        except Exception as e:
            loginf("Cannot write to weewxserverinfo.txt due to error " + str(e))

        # setup caching
        self.cache_enable = False 
        self.cache_stale_time = 900
        self.cache_file = '/tmp/divumwx/RetainedLoopValues.txt'
        self.retainedLoopValues = {}
        self.excludeFields = set([])
        if not os.path.exists(os.path.dirname(self.cache_file)):
            os.mkdir(os.path.dirname(self.cache_file), 0o777)
        if 'DivumWXRealTime' in config_dict:
            if 'cache_enable' in config_dict['DivumWXRealTime']:
                self.cache_enable = True if config_dict['DivumWXRealTime'].get('cache_enable').upper() == 'TRUE' else False; 
            if 'cache_stale_time' in config_dict['DivumWXRealTime']:
                self.cache_stale_time = int(config_dict['DivumWXRealTime'].get('cache_stale_time')) 
            if 'exclude_fields' in config_dict['DivumWXRealTime']:
                self.excludeFields = set(weeutil.weeutil.option_as_list(config_dict['DivumWXRealTime'].get('exclude_fields', [])))
                logdbg("excluding fields: %s" % (self.excludeFields,))
        loginf("DivumWX DivumWXRealTime in cache is: %s" % self.cache_enable)

        # configure the binding
        self.bind(weewx.NEW_ARCHIVE_RECORD, self.handle_new_archive)
        self.bind(weewx.NEW_LOOP_PACKET, self.handle_new_loop)

    def handle_new_archive(self, event):
        if self.prev_archive_time + 50 < time.time():
            self.prev_archive_time = time.time()
            do_rsync_transfer(self.webserver_addresses, os.path.join(self.remote_html_root, "dvmHighcharts", "json/"), os.path.join(self.config_dict['StdReport']['dvmHighcharts'].get('HTML_ROOT'), 'json/'), self.config_dict['StdReport']['RSYNC'].get('user', None), self.config_dict['StdReport']['RSYNC'].get('port',None))
            #do_rsync_transfer(self.webserver_addresses, os.path.join(self.remote_html_root, "serverdata/"), os.path.join(self.config_dict['StdReport']['DivumWXReport'].get('HTML_ROOT'), 'serverdata/') if len(self.webserver_addresses) == 0 else '/tmp/divumwx/serverdata/', self.config_dict['StdReport']['RSYNC'].get('user', None), self.config_dict['StdReport']['RSYNC'].get('port',None))
        if self.cc != None:
            self.cc.update_cloud_cover(event)

    def handle_new_loop(self, event):
        if self.cache_enable:
            self.newLoopPacket(event)
        self.handle_data(event.packet)

    def newLoopPacket(self, event):
        if self.retainedLoopValues == None or len(self.retainedLoopValues) == 0:
            try:
                if (time.time() - os.path.getmtime(self.cache_file)) < self.cache_stale_time: 
                    with open(self.cache_file, 'r') as in_file:
                        self.retainedLoopValues = eval(in_file.read())
                else:
                    loginf("Cache values not use since they are past the sell by date")	
            except Exception as e:
                logerr(str(e))	
        if self.cache_debug:
            logdbg("Event packet before: %s" % (event.packet,))
        # replace the values in the retained packet if they have a value other than None or the field is listed in excludeFields
        self.retainedLoopValues.update( dict((k,v) for k,v in event.packet.items() if (v is not None or k in self.excludeFields)) )
        # if the new packet doesn't contain one of the excludeFields then remove it from the retainedLoopValues
        for k in self.excludeFields - set(event.packet.keys()):
            if k in self.retainedLoopValues:
                self.retainedLoopValues.pop(k)
        event.packet = self.retainedLoopValues.copy()
        try:
            with open(self.cache_file, 'w') as out_file:
                out_file.write(str(self.retainedLoopValues))
        except Exception as e:
            logerr(str(e))	
        if self.chk_lightning_cnt and event.packet['lightning_strike_count'] == 0:
            event.packet['lightning_distance'] = None
            event.packet['lightning_energy'] = None
        if self.cache_debug:
            logdbg("Event packet after: %s" % (event.packet,))

    def handle_data(self, event_data):
        try:
            dbm = self.engine.db_binder.get_manager('wx_binding')
            data = self.calculate(event_data, dbm)
            self.write_data(data)
        except Exception as e:
            logdbg("dvmrt: Exception while handling data: %s" % e)
            raise

    def write_data(self, data):
        data = self.create_realtime_string(data)
        try:
            realtime_path = self.config_dict['DivumWXRealTime'].get('realtime_path_only',"")
            if len(realtime_path):
                lfilename = os.path.join(self.html_root, realtime_path, "dvmRealtime.txt") if len(self.webserver_addresses) == 0 else "/tmp/divumwx/serverdata/dvmRealtime.txt"
            else:
                lfilename = os.path.join(self.html_root, "serverdata", "dvmRealtime.txt") if len(self.webserver_addresses) == 0 else "/tmp/divumwx/serverdata/dvmRealtime.txt"
            if len(self.webserver_addresses) > 0  and not os.path.exists(os.path.dirname(lfilename)):
                os.mkdir(os.path.dirname(lfilename), 0o777)
            with open(lfilename, 'w') as f:
                f.write(data + "\n")
            do_rsync_transfer(self.webserver_addresses, os.path.join(self.remote_html_root, "serverdata/"), os.path.dirname(lfilename), self.config_dict['StdReport']['RSYNC'].get('user', None), self.config_dict['StdReport']['RSYNC'].get('port',None))
        except Exception as err:
            logerr("Error writing file: Error: %s" % (err,))

    # convert from database unit system to specified units
    def _cvt(self, from_v, to_units, obs, group):
        if self.db_us is None:
            return None
        return _convert_us(from_v, self.db_us, to_units, obs, group)

    # convert from database unit system to specified unit system
    def _cvt_us(self, from_v, to_us, obs, group):
        to_units = weewx.units.getStandardUnitType(to_us, obs)[0]
        return self._cvt(from_v, to_units, obs, group)

    # convert observation in group pressure
    def _cvt_p(self, obs, packet, unit):
        return _convert_us(packet.get(obs), self.pkt_us, unit, 
                           obs, 'group_pressure')

    # convert observation in group temperature
    def _cvt_t(self, obs, packet, unit):
        return _convert_us(packet.get(obs), self.pkt_us, unit, 
                           obs, 'group_temperature')

    # convert observation in group rainrate
    def _cvt_rr(self, obs, packet, unit):
        return _convert_us(packet.get(obs), self.pkt_us, unit, 
                           obs, 'group_rainrate')

    # convert observation in group speed
    def _cvt_w(self, obs, packet, unit):
        return _convert_us(packet.get(obs), self.pkt_us, unit, 
                           obs, 'group_speed')

    # convert observation group altitude
    def _cvt_a(self, obs, packet, unit):
        return _convert_us(packet.get(obs), self.pkt_us, unit, 
                           obs, 'group_altitude')

    # calculate the data elements that that weewx does not provide directly.
    def calculate(self, packet, dbm):
        ts = packet.get('dateTime')

        # the 'from' unit system is whatever the database is using.  get it
        # from the database once then cache it for use in conversions.  if
        # there is not yet a database, return an empty dict and we'll get it
        # the next time around.
        if self.db_us is None:
            try:
                self.db_us = get_db_units(dbm)
            except weedb.DatabaseError as e:
                logerr("cannot determine database units: %s" % e)
                return dict()

        # the 'to' unit system defaults to the unit system of the packet
        # (typically the same unit system as the database, but it might not
        # be), but if a different unit system is specified, use that...
        self.pkt_us = packet.get('usUnits')
        if self.unit_system is not None and self.unit_system != self.pkt_us:
            packet = weewx.units.to_std_system(packet, self.unit_system)
            self.pkt_us = self.unit_system

        # ... then get any other specialized units.
        p_u = self.units.get(
            'pressure',
            weewx.units.getStandardUnitType(self.pkt_us, 'barometer')[0])
        t_u = self.units.get(
            'temperature',
            weewx.units.getStandardUnitType(self.pkt_us, 'outTemp')[0])
        r_u = self.units.get(
            'rain',
            weewx.units.getStandardUnitType(self.pkt_us, 'rain')[0])
        w_u = self.units.get(
            'wind',
            weewx.units.getStandardUnitType(self.pkt_us, 'windSpeed')[0])
        cb_u = self.units.get(
            'cloudbase',
            weewx.units.getStandardUnitType(self.pkt_us, 'altitude')[0])

        # divumwx does not use cm for rain, but weewx might
        if r_u == 'cm':
            r_u = 'mm'
        # infer rain rate units from rain units
        rr_u = '%s_per_hour' % r_u
        # infer windrun units from wind units
        wr_u = SPEED_TO_RUN.get(w_u, 'mile')

        # now create the dictionary of data
        data = dict(packet)
        data['units_wind'] = UNITS_WIND.get(w_u, w_u)
        data['units_temperature'] = UNITS_TEMP.get(t_u, t_u)
        data['units_pressure'] = UNITS_PRES.get(p_u, p_u)
        data['units_rain'] = UNITS_RAIN.get(r_u, r_u)
        data['units_cloudbase'] = UNITS_CLOUDBASE.get(cb_u, cb_u)

        data['barometer'] = self._cvt_p('barometer', packet, p_u)
        data['inTemp'] = self._cvt_t('inTemp', packet, t_u)
        data['outTemp'] = self._cvt_t('outTemp', packet, t_u)
        data['dewpoint'] = self._cvt_t('dewpoint', packet, t_u)
        data['heatindex'] = self._cvt_t('heatindex', packet, t_u)
        data['humidex'] = self._cvt_t('humidex', packet, t_u)
        data['windchill'] = self._cvt_t('windchill', packet, t_u)
        data['appTemp'] = self._cvt_t('appTemp', packet, t_u)
        data['windSpeed'] = self._cvt_w('windSpeed', packet, w_u)
        data['rainRate'] = self._cvt_rr('rainRate', packet, rr_u)
        data['divumwx_windDir'] = clamp_degrees(packet.get('windDir'))
        data['windDir_compass'] = degree_to_compass(packet.get('windDir'))
        data['windSpeed_avg'] = self._cvt(
            calc_avg_windspeed(dbm, ts), w_u, 'windSpeed', 'group_speed')
        v = _convert_us(packet.get('windSpeed'), self.pkt_us, 'knot',
                        'windSpeed', 'group_speed')
        data['windSpeed_beaufort'] = weewx.wxformulas.beaufort(v)
        wr = calc_windrun(dbm, ts, self.db_us)
        data['windrun'] = self._cvt(wr, wr_u, 'windrun', 'group_distance')
        # weewx does not know of nautical miles so if wind speed units are knot
        # then we have a windrun in miles and we need to manually convert it to
        # nautical miles
        if w_u == 'knot' and data['windrun'] is not None:
            data['windrun'] /= 1.15077945
        data['cloudbase'] = self._cvt_a('cloudbase', packet, cb_u)
        p1 = packet.get('barometer')
        p2 = get_trend('barometer', dbm, ts)
        p2 = self._cvt_us(p2, self.pkt_us, 'barometer', 'group_pressure')
        data['pressure_trend'] = calc_trend(p1, p2)
        t1 = packet.get('outTemp')
        t2 = get_trend('outTemp', dbm, ts)
        t2 = self._cvt_us(t2, self.pkt_us, 'outTemp', 'group_temperature')
        data['temperature_trend'] = calc_trend(t1, t2)

        data['rain_month'] = self._cvt(
            calc_rain_month(dbm, ts), r_u, 'rain', 'group_rain')
        data['rain_year'] = self._cvt(
            calc_rain_year(dbm, ts), r_u, 'rain', 'group_rain')
        data['rain_yesterday'] = self._cvt(
            calc_rain_yesterday(dbm, ts), r_u, 'rain', 'group_rain')
        data['dayRain'] = self._cvt(
            calc_rain_day(dbm, ts), r_u, 'rain', 'group_rain')

        v, t = calc_minmax('outTemp', dbm, ts, 'MAX')
        data['outTemp_max'] = self._cvt(
            v, t_u, 'outTemp', 'group_temperature')
        data['outTemp_max_time'] = t
        v, t = calc_minmax('outTemp', dbm, ts, 'MIN')
        data['outTemp_min'] = self._cvt(
            v, t_u, 'outTemp', 'group_temperature')
        data['outTemp_min_time'] = t
        v, t = calc_minmax('barometer', dbm, ts, 'MAX')
        data['pressure_max'] = self._cvt(
            v, p_u, 'barometer', 'group_pressure')
        data['pressure_max_time'] = t
        v, t = calc_minmax('barometer', dbm, ts, 'MIN')
        data['pressure_min'] = self._cvt(
            v, p_u, 'barometer', 'group_pressure')
        data['pressure_min_time'] = t
        v, t = calc_minmax('windSpeed', dbm, ts, 'MAX')
        data['windSpeed_max'] = self._cvt(
            v, w_u, 'windSpeed', 'group_speed')
        data['windSpeed_max_time'] = t
        v, t = calc_minmax('windGust', dbm, ts, 'MAX')
        data['windGust_max'] = self._cvt(
            v, w_u, 'windGust', 'group_speed')
        data['windGust_max_time'] = t

        data['10min_high_gust'] = self._cvt(
            calc_max_gust_10min(dbm, ts), w_u, 'windSpeed', 'group_speed')

        data['10min_avg_gust'] = self._cvt(
            calc_avg_gust_10min(dbm, ts), w_u, 'windSpeed', 'group_speed')

        data['10min_avg_wind_bearing'] = calc_avg_winddir_10min(dbm, ts)
        data['avg_wind_dir'] = degree_to_compass(v)

        data['rain_hour'] = self._cvt(
            calc_rain_hour(dbm, ts), r_u, 'rain', 'group_rain')

        data['ET_today'] = calc_ET_today(dbm, ts)
        data['lost_sensors_contact'] = lost_sensor_contact(packet)

        t_C = _convert_us(packet.get('outTemp'), self.pkt_us, 'degree_C',
                          'outTemp', 'group_temperature')
        p_mbar = _convert_us(packet.get('barometer'), self.pkt_us, 'mbar',
                             'barometer', 'group_pressure')
        alm = weewx.almanac.Almanac(ts, self.latitude, self.longitude,
                                    self.altitude_m, t_C, p_mbar)
        data['is_daylight'] = calc_is_daylight(alm)
        data['sunshine_hours'] = calc_daylight_hours(alm)
        data['is_sunny'] = calc_is_sunny(data.get('radiation'),
                                         data.get('max_rad'),
                                         self.sunny_threshold)
        data['zambretti_code'] = self.forecast.get_zambretti_code()
        return data

    def format(self, data, label, places=None):
        value = data.get(label)
        if value is None:
            value = self.nonesub
        elif places is not None:
            try:
                v = float(value)
                fmt = "%%.%df" % places
                value = fmt % v
            except ValueError:
                pass
        return str(value)

    # the * indicates a field that is not part of a typical LOOP packet
    # the ## indicates calculation is not yet implemented
    def create_realtime_string(self, data):
        fields = []
        p_dp = 2 if data['units_pressure'] == 'in' else 1
        r_dp = 2 if data['units_rain'] == 'in' else 1
        datefmt = "%%d%s%%m%s%%y" % ("/", "/")
        tstr = time.strftime(datefmt, time.localtime(data['dateTime']))
        fields.append(tstr)                                           # 1
        tstr = time.strftime("%H:%M:%S", time.localtime(data['dateTime']))
        fields.append(tstr)                                           # 2
        fields.append(self.format(data, 'outTemp', 1))                # 3
        fields.append(self.format(data, 'outHumidity', 0))            # 4
        fields.append(self.format(data, 'dewpoint', 1))               # 5
        fields.append(self.format(data, 'windSpeed_avg', 1))          # 6  *
        fields.append(self.format(data, 'windSpeed', 1))              # 7
        fields.append(self.format(data, 'divumwx_windDir', 0))      # 8
        fields.append(self.format(data, 'rainRate', r_dp))            # 9
        fields.append(self.format(data, 'dayRain', r_dp))             # 10
        fields.append(self.format(data, 'barometer', p_dp))           # 11
        fields.append(self.format(data, 'windDir_compass'))           # 12 *
        fields.append(self.format(data, 'windSpeed_beaufort'))        # 13 *
        fields.append(self.format(data, 'units_wind'))                # 14 *
        fields.append(self.format(data, 'units_temperature'))         # 15 *
        fields.append(self.format(data, 'units_pressure'))            # 16 *
        fields.append(self.format(data, 'units_rain'))                # 17 *
        fields.append(self.format(data, 'windrun', 1))                # 18 *
        fields.append(self.format(data, 'pressure_trend', p_dp))      # 19 *
        fields.append(self.format(data, 'rain_month', r_dp))          # 20 *
        fields.append(self.format(data, 'rain_year', r_dp))           # 21 *
        fields.append(self.format(data, 'rain_yesterday', r_dp))      # 22 *
        fields.append(self.format(data, 'inTemp', 1))                 # 23
        fields.append(self.format(data, 'inHumidity', 0))             # 24
        fields.append(self.format(data, 'windchill', 1))              # 25
        fields.append(self.format(data, 'temperature_trend', 1))      # 26 *
        fields.append(self.format(data, 'outTemp_max', 1))            # 27 *
        fields.append(self.format(data, 'outTemp_max_time'))          # 28 *
        fields.append(self.format(data, 'outTemp_min', 1))            # 29 *
        fields.append(self.format(data, 'outTemp_min_time'))          # 30 *
        fields.append(self.format(data, 'windSpeed_max', 1))          # 31 *
        fields.append(self.format(data, 'windSpeed_max_time'))        # 32 *
        fields.append(self.format(data, 'windGust_max', 1))           # 33 *
        fields.append(self.format(data, 'windGust_max_time'))         # 34 *
        fields.append(self.format(data, 'pressure_max', p_dp))        # 35 *
        fields.append(self.format(data, 'pressure_max_time'))         # 36 *
        fields.append(self.format(data, 'pressure_min', p_dp))        # 37 *
        fields.append(self.format(data, 'pressure_min_time'))         # 38 *
        fields.append('%s' % weewx.__version__)                       # 39
        fields.append('0')                                            # 40
        fields.append(self.format(data, '10min_high_gust', 1))        # 41 *
        fields.append(self.format(data, 'heatindex', 1))              # 42 *
        fields.append(self.format(data, 'humidex', 1))                # 43 *
        fields.append(self.format(data, 'UV', 1))                     # 44
        fields.append(self.format(data, 'ET_today', r_dp))            # 45 *
        fields.append(self.format(data, 'radiation', 0))              # 46
        fields.append(self.format(data, '10min_avg_wind_bearing', 0)) # 47 *
        fields.append(self.format(data, 'rain_hour', r_dp))           # 48 *
        fields.append(self.format(data, 'zambretti_code'))            # 49 *
        fields.append(self.format(data, 'is_daylight'))               # 50 *
        fields.append(self.format(data, 'lost_sensors_contact'))      # 51 *
        fields.append(self.format(data, 'avg_wind_dir'))              # 52 *
        fields.append(self.format(data, 'cloudbase', 0))              # 53 *
        fields.append(self.format(data, 'units_cloudbase'))           # 54 *
        fields.append(self.format(data, 'appTemp', 1))                # 55 *
        fields.append(self.format(data, 'sunshine_hours', 1))         # 56 *
        fields.append(self.format(data, 'maxSolarRad', 1))            # 57
        fields.append(self.format(data, 'lightning_distance'))        # 58 *
        fields.append(self.format(data, 'lightning_energy'))          # 59 *
        fields.append(self.format(data, 'lightning_strike_count'))    # 60 *
        fields.append(self.format(data, 'lightning_noise_count'))     # 61 *
        fields.append(self.format(data, 'lightning_disturber_count')) # 62 *
        fields.append(self.format(data, '10min_avg_gust', 1))         # 63 *
        return ' '.join(fields)
      
      
class CachedValues(object):
    """Dictionary of value-timestamp pairs.  Each timestamp indicates when the
    corresponding value was last updated."""

    def __init__(self):
        self.unit_system = None
        self.values = dict()

    def update(self, packet, ts):
        # update the cache with values from the specified packet, using the
        # specified timestamp.
        for k in packet:
            if k is None:
                # well-formed packets do not have None as key, but just in case
                continue
            elif k == 'dateTime':
                # do not cache the timestamp
                continue
            elif k == 'usUnits':
                # assume unit system of first packet, then enforce consistency
                if self.unit_system is None:
                    self.unit_system = packet['usUnits']
                elif packet['usUnits'] != self.unit_system:
                    raise ValueError("Mixed units encountered in cache. %s vs %s" % \
                                     (self.unit_sytem, packet['usUnits']))
            elif packet[k] is None:
                # FIXME: the cache should not check for values of None.
                # however, if we blindly accept None as a possible value, then
                # any partial packet will break the cache.  the fix probably
                # requires changes to the StdWXCalculate service and the
                # drivers themselves.  if a driver knows a sensor has a bad
                # value, only then it should use a value of None.  if a driver
                # sends partial packets, it should send only the observed
                # values, not a full packet with None in the unobserved fields.
                # similarly for calculate service.  if the service does not
                # have all the inputs for a given derived, it should not insert
                # a derived with value of None.  it should insert a value of
                # None only if all the inputs exist and the result is None.
                continue
            else:
                # cache each value, associating it with the it was cached
                self.values[k] = {'value': packet[k], 'ts': ts}

    def get_value(self, k, ts, stale_age):
        # get the value for the specified key.  if the value is older than
        # stale_age (seconds) then return None.
        if k in self.values and ts - self.values[k]['ts'] < stale_age:
            return self.values[k]['value']
        return None

    def get_packet(self, ts=None, stale_age=960):
        if ts is None:
            ts = int(time.time() + 0.5)
        pkt = {'dateTime': ts, 'usUnits': self.unit_system}
        for k in self.values:
            pkt[k] = self.get_value(k, ts, stale_age)
        return pkt


#
#    Copyright (c) 2020 Tom Keffer <tkeffer@gmail.com>
#
#    See the file LICENSE.txt for your full rights.
#
"""This example shows how to extend the XTypes system with a new type, lastnonzero, the last non-null or non-zero in a record

REQUIRES WeeWX V4.2 OR LATER!

To use:
    1. Stop weewxd
    2. Put this file in your user subdirectory.
    3. In weewx.conf, subsection [Engine][[Services]], add LastNonZero to the list
    "xtype_services". For example, this means changing this

        [Engine]
            [[Services]]
                xtype_services = weewx.wxxtypes.StdWXXTypes, weewx.wxxtypes.StdPressureCooker, weewx.wxxtypes.StdRainRater

    to this:

        [Engine]
            [[Services]]
                xtype_services = weewx.wxxtypes.StdWXXTypes, weewx.wxxtypes.StdPressureCooker, weewx.wxxtypes.StdRainRater, user.lastnonzero.LastNonZeroService

    4. Optionally, add the following section to weewx.conf:
        [LastNonZero]
            algorithm = simple   # Or tetens

    5. Restart weewxd

"""
from weewx.engine import StdService
import weedb
import weewx.xtypes
import datetime

class LastNonZero(weewx.xtypes.XType):
   
    def get_aggregate(self, obs_type, timespan, aggregate_type, db_manager, **option_dict):
        if aggregate_type != 'lastnonzero':
            raise weewx.UnknownAggregation(aggregate_type)
       
        interpolate_dict = {
            'aggregate_type': aggregate_type,
            'obs_type': obs_type,
            'table_name': db_manager.table_name,
            'start': timespan.start,
            'stop': timespan.stop
        }

        select_stmt = "SELECT %(obs_type)s FROM %(table_name)s " \
                      "WHERE dateTime > %(start)s AND dateTime <= %(stop)s " \
                      "AND %(obs_type)s IS NOT NULL " \
                      "AND %(obs_type)s != 0 " \
                      "ORDER BY dateTime DESC LIMIT 1" % interpolate_dict

        try:
            row = db_manager.getSql(select_stmt)
        except weedb.NoColumnError:
            raise weewx.UnknownType(obs_type)

        value = row[0] if row else None

        u, g = weewx.units.getStandardUnitType(db_manager.std_unit_system, obs_type,
                                               aggregate_type)
        return weewx.units.ValueTuple(value, u, g)

class LastNonZeroService(StdService):
    """ WeeWX service whose job is to register the XTypes extension LastNonZero with the
    XType system.
    """

    def __init__(self, engine, config_dict):
        super(LastNonZeroService, self).__init__(engine, config_dict)

        # Instantiate an instance of LastNonZero:
        self.nz = LastNonZero()
        # Register it:
        weewx.xtypes.xtypes.append(self.nz)

    def shutDown(self):
        # Remove the registered instance:
        weewx.xtypes.xtypes.remove(self.nz)        


"""
masterdata.py

Copyright (C)2020 by John A Kline (john@johnkline.com)
Distributed under the terms of the GNU Public License (GPLv3)

SensorData is a WeeWX service that generates a json file (loop-data.txt)
containing values for the observations in the loop packet; along with
today's high, low, sum, average and weighted averages for each observation
in the packet.
"""

import copy
import configobj
import json
import logging
import math
import os
import queue
import shutil
import sys
import tempfile
import threading
import time

from dataclasses import dataclass
from typing import Any, Dict, Generator, List, Optional, Tuple, Union
from enum import Enum
from sortedcontainers import SortedDict

import weewx
import weewx.defaults
import weewx.manager
import weewx.reportengine
import weewx.units
import weewx.wxxtypes
import weeutil.config
import weeutil.logger
import weeutil.rsyncupload
import weeutil.weeutil


from weeutil.weeutil import timestamp_to_string
from weeutil.weeutil import to_bool
from weeutil.weeutil import to_float
from weeutil.weeutil import to_int
from weewx.engine import StdService

# get a logger object
log = logging.getLogger(__name__)

LOOP_DATA_VERSION = '3.1'

if sys.version_info[0] < 3 or (sys.version_info[0] == 3 and sys.version_info[1] < 7):
    raise weewx.UnsupportedFeature(
        "weewx-loopdata requires Python 3.7 or later, found %s.%s" % (sys.version_info[0], sys.version_info[1]))

if weewx.__version__ < "4":
    raise weewx.UnsupportedFeature(
        "weewx-loopdata requires WeeWX, found %s" % weewx.__version__)

windrun_bucket_suffixes: List[str] = [ 'N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE',
                                       'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW' ]

# Set up windrun_<dir> observation types.
for suffix in windrun_bucket_suffixes:
    weewx.units.obs_group_dict['windrun_%s' % suffix] = 'group_distance'

@dataclass
class CheetahName:
    field      : str           # $day.outTemp.avg.formatted
    prefix     : Optional[str] # unit or None
    prefix2    : Optional[str] # label or None
    period     : Optional[str] # 2m, 10m, 24h, hour, day, week, month, year, rainyear, alltime, current, trend
    obstype    : str           # e.g,. outTemp
    agg_type   : Optional[str] # avg, sum, etc. (required if period, other than current, is specified, else None)
    format_spec: Optional[str] # formatted (formatted value sans label), raw or ordinal_compass (could be on direction), or None

@dataclass
class Configuration:
    queue                    : queue.SimpleQueue
    config_dict              : Dict[str, Any]
    unit_system              : int
    archive_interval         : int
    loop_data_dir            : str
    filename                 : str
    target_report            : str
    loop_frequency           : float
    specified_fields         : List[str]
    fields_to_include        : List[CheetahName]
    formatter                : weewx.units.Formatter
    converter                : weewx.units.Converter
    tmpname                  : str
    enable                   : bool
    remote_server            : str
    remote_port              : int
    remote_user              : str
    remote_dir               : str
    compress                 : bool
    log_success              : bool
    ssh_options              : str
    skip_if_older_than       : int
    timeout                  : int
    time_delta               : int # Used for trend.
    week_start               : int
    rainyear_start           : int
    current_obstypes         : List[str]
    trend_obstypes           : List[str]
    alltime_obstypes         : List[str]
    rainyear_obstypes        : List[str]
    year_obstypes            : List[str]
    month_obstypes           : List[str]
    week_obstypes            : List[str]
    day_obstypes             : List[str]
    hour_obstypes            : List[str]
    twentyfour_hour_obstypes : List[str]
    ten_min_obstypes         : List[str]
    two_min_obstypes         : List[str]
    baro_trend_descs         : Any # Dict[BarometerTrend, str]

# ===============================================================================
#                             ContinuousScalarStats
# ===============================================================================

@dataclass
class ScalarDebit:
    timestamp : int
    expiration: int
    value     : float
    weight    : float

class ContinuousScalarStats(object):
    """Accumulates statistics (min, max, average, etc.) for a scalar value.

    Property 'first' is the first non-None value seen. Property 'firsttime' is
    the time it was seen.

    Property 'last' is the last non-None value seen. Property 'lasttime' is
    the time it was seen.

    The accumulator collects a rolling number of observations spanning timelength
    seconds.

    addSum(ts, val, weight)
              |                          future_debits (List)
              |                          --------------------
              '------------------------> ts|expiration(ts+timelength)|value|weight
              |
              |
              v
        values_dict (Sorted Dict)
        key         value
        ----------- ------------------------
        val         timestamp_list (List)
                    --------------
                    ts

    Every time an observation is added (with addSum), a future
    debit is created with the same information and an expiration of ts + timelength.
    In the continous accumulator addRecord function, after addSum is called on all
    continous stats instances, trimExpiredEntries(ts) is called on
    all continous stats instances.

    The list of future debits is stored in a List.  Each time trimExpiredEntries is
    called, the top of the list is iterated on looking for any entries where
    the expiration is <= the current dateTime.

    In addition to the future debit list, a values_dict (SortedDict) is maintained where:
    key  : the value specified in the call to addSum
    value: timestamp_list, a list of timestamps (as specified in an addSum call)
           for the particular value of the key
    When addSum is called:
    1. If the value does not already exist in values_dict, it is created as the key and an
       empty timestamp_list is created for the value part of the key/value pair.
    2. a new ts is added to the end of the time_stamp list.
    When trimExpiredEntries is called,
    1. The timestamp_list is retrieved in values_dict by looking up the value.
    2. The creation timestamp is removed from the timestamp_list (it will be the first)
    3. If the timestamp_list is now empty, the key/value pair is removed from values_dict.
    As the values_dict is sorted by value, it is used to efficiently find the min and max
    values when getStatsTuple is called.  For max, maxtime is the first entry in the
    timestamp_list for that value.  As expected, for min, mintime is the first entry in the
    timestamp_list for that value.
    """

    def __init__(self, timelength: int):
        self.timelength: int = timelength
        self.future_debits: List[ScalarDebit] = []
        self.values_dict: SortedDict[float, List[int]] = SortedDict()
        self.sum = 0.0
        self.count = 0
        self.wsum = 0.0
        self.sumtime = 0.0

    def getStatsTuple(self):
        # min is key of first element in values_dict
        # mintime is first element of the timestamp list contained in the value of the first element in values_dict
        # max is key of last element in dict
        # maxtime is first element of the timestamp list contained in the value of the last element in values_dict
        min, timelist = self.values_dict.peekitem(0)
        mintime: int = timelist[0]
        max, timelist = self.values_dict.peekitem(-1)
        maxtime: int = timelist[0]
        sum = SensorData.massage_near_zero(self.sum)
        wsum = SensorData.massage_near_zero(self.wsum)
        return (min, mintime, max, maxtime,
                sum, self.count, wsum, self.sumtime)

    def addSum(self, ts, val, weight=1):
        """Add a scalar value to my running sum and count.
           Also add debit to be deducted self.timelength seconds in the future.
        """

        # If necessary, convert to float. Be prepared to catch an exception if not possible.
        try:
            val = to_float(val)
        except ValueError:
            val = None

        # Check for None and NaN:
        if val is not None and val == val:
            self.sum += val
            self.count += 1
            self.wsum += val * weight
            self.sumtime += weight
            # Add to values_dict
            if not val in self.values_dict:
                self.values_dict[val] = []
            timestamp_list: List[int] = self.values_dict[val]
            timestamp_list.append(ts)
            # Add future debit
            debit= ScalarDebit(
                timestamp  = ts,
                expiration = ts + self.timelength,
                value    = val,
                weight   = weight)
            self.future_debits.append(debit)

    def trimExpiredEntries(self, ts):
        # Remove any debits that may have matured.
        while len(self.future_debits) > 0 and self.future_debits[0].expiration <= ts:
            # Apply this debit.
            debit = self.future_debits.pop(0)
            log.debug('Applying debit: %s value: %f, weight: %f' % (timestamp_to_string(debit.timestamp), debit.value, debit.weight))
            self.sum -= debit.value
            self.count -= 1
            self.wsum -= debit.value * debit.weight
            self.sumtime -= debit.weight
            # Remove the debit entry in the values_dict.
            timestamp_list: List[int] = self.values_dict[debit.value]
            first_timestamp = timestamp_list.pop(0)
            assert first_timestamp == debit.timestamp
            if len(timestamp_list) == 0:
                self.values_dict.pop(debit.value)

    @property
    def avg(self):
        return self.wsum / self.sumtime if self.count else None

    @property
    def first(self):
        if len(self.future_debits) != 0:
          return self.future_debits[0].value
        else:
            return None

    @property
    def firsttime(self):
        if len(self.future_debits) != 0:
            return self.future_debits[0].timestamp
        else:
            return None

    @property
    def last(self):
        if len(self.future_debits) != 0:
          return self.future_debits[-1].value
        else:
            return None

    @property
    def lasttime(self):
        if len(self.future_debits) != 0:
            return self.future_debits[-1].timestamp
        else:
            return None

# ===============================================================================
#                             ContinuousVecStats
# ===============================================================================

@dataclass
class VecDebit:
    timestamp : int
    expiration: int
    speed     : float
    dirN      : float
    weight    : float

class ContinuousVecStats(object):
    """Accumulates statistics for a vector value.
    The accumulator collects a rolling number of observations spanning timelength
    seconds.

    addSum(ts, val(speed,dirN), weight)
              |                          future_debits (List)
              |                          --------------------
              '------------------------> ts|expiration(ts+timelength)|value|weight
              |
              |
              v
        speed_dict (Sorted Dict)
        key         value
        ----------- ------------------------
        speed       timestamp_dirn_list (List)
                    -------------------------
                    tuple(ts, dirN)

    Every time an observation is added (with addSum), a future
    debit is created with the same information and an expiration of ts + timelength.
    In the continous accumulator addRecord function, after addSum is called on all
    continous stats instances, trimExpiredEntries(ts) is called on
    all continous stats instances.

    The list of future debits is stored in a List.  Each time trimExpiredEntries is
    called, the top of the list is iterated on looking for any entries where
    the expiration is <= the current dateTime.

    In addition to the future debit list, a speed_dict (SortedDict) is maintained where:
    key  : the value specified in the call to addSum
    value: timestamp_dirn_list, a List of (ts, dirN) tuples
    When addSum is called:
    1. If the speed does not already exist in speed_dict, it is created as the key and an
       empty timestamp_dirn_list is created for the value part of the key/value pair.
    2. a new (ts, dirN) tuple is added to the timestamp_dirn_list.

    When trimExpiredEntries is called,
    1. The timestamp_dirn_list is retrieved in speed_dict by looking up the speed.
    2. The timestamp, dirN tuple (which is the first) entry is removed from the timestamp_dirn_list.
    3. If the timestamp_dirn_lisat is now empty, the speed entry is removed from speed_dict.
    As the speed_dict is sorted by value, it is used to efficiently find the min and max
    values when getStatsTuple is called.  For max, maxtime is the first entry in the
    timestamp_dirn_list for that value (with dirN being the dirN that is paired with that
    first timestamp.  As expected, for min, mintime is the first entry in the
    timestamp_dirn_list with dirN being the value paired with the mintime.
    """

    def __init__(self, timelength: int):
        self.timelength: int = timelength
        self.future_debits: List[VecDebit] = []
        self.speed_dict: SortedDict[float, List[Tuple[int, float]]] = SortedDict()
        self.sum = 0.0
        self.count = 0
        self.wsum = 0.0
        self.sumtime = 0
        self.xsum = 0.0
        self.ysum = 0.0
        self.dirsumtime = 0
        self.squaresum = 0.0
        self.wsquaresum = 0.0

    def getStatsTuple(self):
        # min is key of first key in speed_dict
        # mintime is first entry of the timestamp_dirn_list contained in the value of the first element in speed_dict
        # max is key of last key in speed_dict
        # max is key of last element in speed_dict
        # maxtime is first entry of the timestamp_dirn_list contained in the value of the last element in speed_dict
        if len(self.speed_dict) != 0:
            min, time_dirn_list_min = self.speed_dict.peekitem(0)
            mintime, dummy = time_dirn_list_min[0]
            max, time_dirn_list_max = self.speed_dict.peekitem(-1)
            maxtime, maxdir = time_dirn_list_max[-1]
        else:
            min, mintime, max, maxtime = None, None, None, None

        sum  = SensorData.massage_near_zero(self.sum)
        wsum = SensorData.massage_near_zero(self.wsum)
        sumtime = SensorData.massage_near_zero(self.sumtime)
        dirsumtime = SensorData.massage_near_zero(self.dirsumtime)
        squaresum = SensorData.massage_near_zero(self.squaresum)
        wsquaresum = SensorData.massage_near_zero(self.wsquaresum)

        return (min, mintime,
                max, maxtime,
                sum, self.count,
                wsum, sumtime,
                maxdir, self.xsum, self.ysum,
                dirsumtime, squaresum, wsquaresum)


    def addSum(self, ts, val, weight=1):
        """Add a vector value to my sum and squaresum.
        val: A vector value. It is a 2-way tuple (mag, dir)
        """
        speed, dirN = val


        # If necessary, convert to float. Be prepared to catch an exception if not possible.
        try:
            speed = to_float(speed)
        except ValueError:
            speed = None
        try:
            dirN = to_float(dirN)
        except ValueError:
            dirN = None

        # Check for None and NaN:
        if speed is not None and speed == speed:
            self.sum += speed
            self.count += 1
            self.wsum += weight * speed
            self.sumtime += weight
            self.squaresum += speed ** 2
            self.wsquaresum += weight * speed ** 2
            if dirN is not None:
                self.xsum += weight * speed * math.cos(math.radians(90.0 - dirN))
                self.ysum += weight * speed * math.sin(math.radians(90.0 - dirN))
            # It's OK for direction to be None, provided speed is zero:
            if dirN is not None or speed == 0:
                self.dirsumtime += weight
            # Add to speed_dict
            if not speed in self.speed_dict:
                self.speed_dict[speed] = []
            timestamp_dirn_list: List[Tuple[int, float]] = self.speed_dict[speed]
            timestamp_dirn_list.append((ts, dirN))
            # Add future debit
            debit = VecDebit(
                timestamp  = ts,
                expiration = ts + self.timelength,
                speed      = speed,
                dirN       = dirN,
                weight     = weight)
            self.future_debits.append(debit)

    def trimExpiredEntries(self, ts):
        # Remove any debits that may have matured.
        while len(self.future_debits) > 0 and self.future_debits[0].expiration <= ts:
            debit = self.future_debits.pop(0)
            log.debug('Applying ContinuousVecStats debit: %s speed: %f, dirN: %r, weight: %f' % (timestamp_to_string(debit.timestamp), debit.speed, debit.dirN, debit.weight))
            # Apply this debit.
            self.sum -= debit.speed
            self.count -= 1
            self.wsum -= debit.weight * debit.speed
            self.sumtime -= debit.weight
            self.squaresum -= debit.speed ** 2
            self.wsquaresum -= debit.weight * debit.speed ** 2
            if debit.dirN is not None:
                self.xsum += debit.weight * debit.speed * math.cos(math.radians(90.0 - debit.dirN))
                self.ysum += debit.weight * debit.speed * math.sin(math.radians(90.0 - debit.dirN))
            # Remove the debit entry in the speed_dict.
            timestamp_dirn_list: List[Tuple[int, float]] = self.speed_dict[debit.speed]
            timestamp, dirN = timestamp_dirn_list.pop(0)
            assert timestamp == debit.timestamp
            if len(timestamp_dirn_list) == 0:
                self.speed_dict.pop(debit.speed)

    @property
    def avg(self):
        return self.wsum / self.sumtime if self.count else None

    @property
    def rms(self):
        return math.sqrt(self.wsquaresum / self.sumtime) if self.count else None

    @property
    def vec_avg(self):
        if self.count:
            return math.sqrt((self.xsum ** 2 + self.ysum ** 2) / self.sumtime ** 2)

    @property
    def vec_dir(self):
        if self.dirsumtime and (self.ysum or self.xsum):
            _result = 90.0 - math.degrees(math.atan2(self.ysum, self.xsum))
            if _result < 0.0:
                _result += 360.0
            return _result
        # Return the last known direction when our vector sum is 0
        return self.last[1]

    @property
    def first(self):
        if len(self.future_debits) != 0:
            return self.future_debits[0].speed, self.future_debits[-1].dirN
        else:
            return None

    @property
    def firsttime(self):
        if len(self.future_debits) != 0:
            return self.future_debits[0].timestamp
        else:
            return None

    @property
    def last(self):
        if len(self.future_debits) != 0:
            return self.future_debits[-1].speed, self.future_debits[-1].dirN
        else:
            return None

    @property
    def lasttime(self):
        if len(self.future_debits) != 0:
            return self.future_debits[-1].timestamp
        else:
            return None


# ===============================================================================
#                             ContinuousFirstLastAccum
# ===============================================================================

@dataclass
class FirstLastEntry:
    dateTime: int
    value   : str

class ContinuousFirstLastAccum(object):
    """Minimal accumulator, suitable for strings.
    It can only return the first and last strings it has seen, along with their timestamps.

    The accumulator collects a rolling number of observations spanning timelength
    seconds.

    addSum(ts, val, weight)
              |
              v
        values_list (List)
        FirstLastEntry
        --------------
        dateTime|value

    In the continous accumulator addRecord function, after addSum is called on all
    continous stats instances, trimExpiredEntries(ts) is called on
    all continous stats instances.

    When addSum is called, FirstLastEntry is added to values_list.

    When trimExpiredEntries is called,
    1. the values_list is iterated over while FirstLastEntry.dateTime <= ts
    2.     the FirstLastEntry is deleted

    first/firsttime is the dateTime value and dateTime of the first entry in values_list
    last/lasttime is the dateTime value and dateTime of the last entry in values_list
    """

    def __init__(self, timelength: int):
        self.timelength = timelength
        self.values_list: List[FirstLastEntry] = []

    def getStatsTuple(self):
        """Return a stats-tuple. That is, a tuple containing the gathered statistics."""
        return self.values_list[0].value, self.values_list[0].dateTime, self.values_list[-1].value, self.values_list[-1].dateTime,

    def addSum(self, ts, val, weight=1):
        """Add a scalar value to my running count."""
        if val is not None:
            string_val = str(val)
            self.values_list.append(FirstLastEntry(
                dateTime = ts,
                value = string_val))

    def trimExpiredEntries(self, ts):
        # Remove any expired entries
        while len(self.values_list) > 0 and self.values_list[0].dateTime + self.timelength <= ts:
            self.values_list.pop(0)


# ===============================================================================
#                             Class ContinuousAccum
# ===============================================================================

class ContinuousAccum(dict):
    """Accumulates statistics for a set of observation types.

    ContinousAccum is a lot like WeeWX's accum, but a timelength (rather than
    a timespan) is specified and the ContinousAccum gives stats on a rolling
    timelength number of seconds.

    ContinuousAccums never expire.  In their steady state, for every loop packet,
    they add the new packet and drop the olest packet.
    """

    def __init__(self, timelength: int, unit_system=None):
        """Initialize a Accum.

        timelength: The length of time the accumulator will keep data for (rolling).
        unit_system: The unit system used by the accumulator"""

        self.timelength = timelength
        # Set the accumulator's unit system. Usually left unspecified until the
        # first observation comes in for normal operation or pre-set if
        # obtaining a historical accumulator.
        self.unit_system = unit_system

    def addRecord(self, record, weight=1):
        """Add a record to running statistics.

        The record must have keys 'dateTime' and 'usUnits'."""

        for obs_type in record:
            # Get the proper function ...
            func = get_add_function(obs_type)
            # ... then call it.
            func(self, record, obs_type, weight)

        # Trim the expired entries.
        for stats in self.keys():
            self[stats].trimExpiredEntries(record['dateTime'])

    def getRecord(self):
        """Extract a record out of the results in the accumulator."""

        # All records have a timestamp and unit type
        record = {'dateTime': self.timespan.stop,
                  'usUnits': self.unit_system}

        return self.augmentRecord(record)

    def augmentRecord(self, record):

        # Go through all observation types.
        for obs_type in self:
            # If the type does not appear in the record, then add it:
            if obs_type not in record:
                # Get the proper extraction function...
                func = weewx.accum.get_extract_function(obs_type)
                # ... then call it
                func(self, record, obs_type)

        return record

    #
    # Begin add functions. These add a record to the accumulator.
    #

    def add_value(self, record, obs_type, weight):
        """Add a single observation to myself."""

        val = record[obs_type]

        # If the type has not been seen before, initialize it
        self._init_type(self.timelength, obs_type)
        self[obs_type].addSum(record['dateTime'], val, weight=weight)

    def add_wind_value(self, record, obs_type, weight):
        """Add a single observation of type wind to myself."""

        if obs_type in ['windDir', 'windGust', 'windGustDir']:
            return
        if weewx.debug:
            assert obs_type == 'windSpeed'

        # First add it to regular old 'windSpeed', then
        # treat it like a vector.
        self.add_value(record, obs_type, weight)

        # If the type has not been seen before, initialize it.
        self._init_type(self.timelength, 'wind')

        # Add to the running sum.
        self['wind'].addSum(record['dateTime'], (record['windSpeed'], record.get('windDir')), weight=weight)

    def check_units(self, record, obs_type, weight):
        if weewx.debug:
            assert obs_type == 'usUnits'
        self._check_units(record['usUnits'])

    def noop(self, record, obs_type, weight=1):
        pass

    #
    # Begin extraction functions. These extract a record out of the accumulator.
    #

    def extract_wind(self, record, obs_type):
        """Extract wind values from myself, and put in a record."""
        # Wind records must be flattened into the separate categories:
        if 'windSpeed' not in record:
            record['windSpeed'] = self[obs_type].avg
        if 'windDir' not in record:
            record['windDir'] = self[obs_type].vec_dir
        if 'windGust' not in record:
            record['windGust'] = self[obs_type].max
        if 'windGustDir' not in record:
            record['windGustDir'] = self[obs_type].max_dir

    def extract_sum(self, record, obs_type):
        record[obs_type] = self[obs_type].sum if self[obs_type].count else None

    def extract_last(self, record, obs_type):
        record[obs_type] = self[obs_type].last

    def extract_avg(self, record, obs_type):
        record[obs_type] = self[obs_type].avg

    def extract_min(self, record, obs_type):
        record[obs_type] = self[obs_type].min

    def extract_max(self, record, obs_type):
        record[obs_type] = self[obs_type].max

    def extract_count(self, record, obs_type):
        record[obs_type] = self[obs_type].count

    #
    # Miscellaneous, utility functions
    #

    def _init_type(self, timelength: int, obs_type):
        """Add a given observation type to my dictionary."""
        # Do nothing if this type has already been initialized:
        if obs_type in self:
            return

        # Get a new accumulator of the proper type
        self[obs_type] = new_continuous_accumulator(timelength, obs_type)

    def _check_units(self, new_unit_system):
        # If no unit system has been specified for me yet, adopt the incoming
        # system
        if self.unit_system is None:
            self.unit_system = new_unit_system
        else:
            # Otherwise, make sure they match
            if self.unit_system != new_unit_system:
                raise ValueError("Unit system mismatch %d v. %d" % (self.unit_system,
                                                                    new_unit_system))

    @property
    def isEmpty(self):
        return self.unit_system is None

def new_continuous_accumulator(timelength, obs_type):
    """Instantiate an accumulator, appropriate for type 'obs_type'."""
    # global accum_dict
    # Get the options for this type. Substitute the defaults if they have not been specified
    obs_options = weewx.accum.accum_dict.get(obs_type, weewx.accum.OBS_DEFAULTS)
    # Get the nickname of the accumulator. Default is 'scalar'
    accum_nickname = obs_options.get('accumulator', 'scalar')
    # Instantiate and return the accumulator.
    # If we don't know this nickname, then fail hard with a KeyError
    return ACCUM_TYPES[accum_nickname](timelength)

ACCUM_TYPES = {
    'scalar': ContinuousScalarStats,
    'vector': ContinuousVecStats,
    'firstlast': ContinuousFirstLastAccum
}

ADD_FUNCTIONS = {
    'add': ContinuousAccum.add_value,
    'add_wind': ContinuousAccum.add_wind_value,
    'check_units': ContinuousAccum.check_units,
    'noop': ContinuousAccum.noop
}

def get_add_function(obs_type):
    """Get an adder function appropriate for type 'obs_type'."""
    # global accum_dict
    # Get the options for this type. Substitute the defaults if they have not been specified
    obs_options = weewx.accum.accum_dict.get(obs_type, weewx.accum.OBS_DEFAULTS)
    # Get the nickname of the adder. Default is 'add'
    add_nickname = obs_options.get('adder', 'add')
    # If we don't know this nickname, then fail hard with a KeyError
    return ADD_FUNCTIONS[add_nickname]

@dataclass
class AccumulatorPayload:
    alltime_accum        : Optional[weewx.accum.Accum]
    rainyear_accum       : Optional[weewx.accum.Accum]
    year_accum           : Optional[weewx.accum.Accum]
    month_accum          : Optional[weewx.accum.Accum]
    week_accum           : Optional[weewx.accum.Accum]
    day_accum            : Optional[weewx.accum.Accum]
    hour_accum           : Optional[weewx.accum.Accum]
    twentyfour_hour_accum: Optional[ContinuousAccum]
    ten_min_accum        : Optional[ContinuousAccum]
    two_min_accum        : Optional[ContinuousAccum]
    trend_accum          : Optional[ContinuousAccum]

class BarometerTrend(Enum):
    RISING_VERY_RAPIDLY  =  4
    RISING_QUICKLY       =  3
    RISING               =  2
    RISING_SLOWLY        =  1
    STEADY               =  0
    FALLING_SLOWLY       = -1
    FALLING              = -2
    FALLING_QUICKLY      = -3
    FALLING_VERY_RAPIDLY = -4

@dataclass
class Reading:
    dateTime: int
    value   : Any

@dataclass
class PeriodPacket:
    timestamp: int
    packet   : Dict[str, Any]

class SensorData(StdService):
    def __init__(self, engine, config_dict):
        super(SensorData, self).__init__(engine, config_dict)
        log.info("Service version is %s." % LOOP_DATA_VERSION)

        if sys.version_info[0] < 3:
            raise Exception("Python 3 is required for the loopdata plugin.")

        self.loop_processor_started = False
        self.day_packets: List[Dict[str, Any]] = []

        station_dict             = config_dict.get('Station', {})
        std_archive_dict         = config_dict.get('StdArchive', {})
        loop_config_dict         = config_dict.get('SensorData', {})
        file_spec_dict           = loop_config_dict.get('FileSpec', {})
        formatting_spec_dict     = loop_config_dict.get('Formatting', {})
        loop_frequency_spec_dict = loop_config_dict.get('LoopFrequency', {})
        rsync_spec_dict          = loop_config_dict.get('RsyncSpec', {})
        include_spec_dict        = loop_config_dict.get('Include', {})
        baro_trend_trans_dict    = loop_config_dict.get('BarometerTrendDescriptions', {})

        # Get the unit_system as specified by StdConvert->target_unit.
        # Note: this value will be overwritten if the day accumulator has a a unit_system.
        db_binder = weewx.manager.DBBinder(config_dict)
        default_binding = config_dict.get('StdReport')['data_binding']
        dbm = db_binder.get_manager(default_binding)
        unit_system = dbm.std_unit_system
        if unit_system is None:
            unit_system = weewx.units.unit_constants[self.config_dict['StdConvert'].get('target_unit', 'US').upper()]
        # Get the column names of the archive table.
        self.archive_columns: List[str] = dbm.connection.columnsOf('archive')

        # Get a temporay file in which to write data before renaming.
        tmp = tempfile.NamedTemporaryFile(prefix='SensorData', delete=False)
        tmp.close()

        # Get a target report dictionary we can use for converting units and formatting.
        target_report = formatting_spec_dict.get('target_report', 'SensorDataReport')
        try:
            target_report_dict = SensorData.get_target_report_dict(
                config_dict, target_report)
        except Exception as e:
            log.error('Could not find target_report: %s.  SensorData is exiting. Exception: %s' % (target_report, e))
            return

        loop_data_dir = SensorData.compose_loop_data_dir(config_dict, target_report_dict, file_spec_dict)

        # Get the loop frequency seconds to be passed as the weight to accumulators.
        loop_frequency = to_float(loop_frequency_spec_dict.get('seconds', '2.0'))

        # Get [possibly localized] strings for trend.barometer.desc
        baro_trend_descs = SensorData.construct_baro_trend_descs(baro_trend_trans_dict)

        # Process fields line of SensorData section.
        specified_fields = include_spec_dict.get('fields', [])
        (fields_to_include, current_obstypes, trend_obstypes, alltime_obstypes, rainyear_obstypes,
            year_obstypes, month_obstypes, week_obstypes, day_obstypes, hour_obstypes,
            twentyfour_hour_obstypes, ten_min_obstypes, two_min_obstypes) = SensorData.get_fields_to_include(specified_fields)

        # Get the time_delta (number of seconds) to use for trend_accum.
        try:
            time_delta: int = to_int(target_report_dict['Units']['Trend']['time_delta'])
            if time_delta > 259200:
                log.info('time_delta of %d specified, SensorData will use max value of 259200.' % time_delta)
                time_delta = 259200
        except KeyError:
            time_delta = 10800

        # Get week_start
        try:
            week_start: int = to_int(station_dict['week_start'])
        except KeyError:
            week_start = 6

        # Get rainyear_start (in weewx.conf, it is rain_year_start)
        try:
            rainyear_start: int = to_int(station_dict['rain_year_start'])
        except KeyError:
            rainyear_start = 1

        self.cfg: Configuration = Configuration(
            queue                    = queue.SimpleQueue(),
            config_dict              = config_dict,
            unit_system              = unit_system,
            archive_interval         = to_int(std_archive_dict.get('archive_interval')),
            loop_data_dir            = loop_data_dir,
            filename                 = file_spec_dict.get('filename', 'loop-data.txt'),
            target_report            = target_report,
            loop_frequency           = loop_frequency,
            specified_fields         = specified_fields,
            fields_to_include        = fields_to_include,
            formatter                = weewx.units.Formatter.fromSkinDict(target_report_dict),
            converter                = weewx.units.Converter.fromSkinDict(target_report_dict),
            tmpname                  = tmp.name,
            enable                   = to_bool(rsync_spec_dict.get('enable')),
            remote_server            = rsync_spec_dict.get('remote_server'),
            remote_port              = to_int(rsync_spec_dict.get('remote_port')) if rsync_spec_dict.get(
                                      'remote_port') is not None else None,
            remote_user              = rsync_spec_dict.get('remote_user'),
            remote_dir               = rsync_spec_dict.get('remote_dir'),
            compress                 = to_bool(rsync_spec_dict.get('compress')),
            log_success              = to_bool(rsync_spec_dict.get('log_success')),
            ssh_options              = rsync_spec_dict.get('ssh_options', '-o ConnectTimeout     =1'),
            timeout                  = to_int(rsync_spec_dict.get('timeout', 1)),
            skip_if_older_than       = to_int(rsync_spec_dict.get('skip_if_older_than', 3)),
            time_delta               = time_delta,
            week_start               = week_start,
            rainyear_start           = rainyear_start,
            current_obstypes         = current_obstypes,
            trend_obstypes           = trend_obstypes,
            alltime_obstypes         = alltime_obstypes,
            rainyear_obstypes        = rainyear_obstypes,
            year_obstypes            = year_obstypes,
            month_obstypes           = month_obstypes,
            week_obstypes            = week_obstypes,
            day_obstypes             = day_obstypes,
            hour_obstypes            = hour_obstypes,
            twentyfour_hour_obstypes = twentyfour_hour_obstypes,
            ten_min_obstypes         = ten_min_obstypes,
            two_min_obstypes         = two_min_obstypes,
            baro_trend_descs         = baro_trend_descs)

        if not os.path.exists(self.cfg.loop_data_dir):
            os.makedirs(self.cfg.loop_data_dir)

        log.info('SensorData file is: %s' % os.path.join(self.cfg.loop_data_dir, self.cfg.filename))

        self.bind(weewx.PRE_LOOP, self.pre_loop)
        self.bind(weewx.NEW_LOOP_PACKET, self.new_loop)

    @staticmethod
    def massage_near_zero(val: float)-> float:
        if val > -0.0000000001 and val < 0.0000000001:
            return 0.0
        else:
            return val

    @staticmethod
    def compose_loop_data_dir(config_dict: Dict[str, Any],
            target_report_dict: Dict[str, Any], file_spec_dict: Dict[str, Any]
            ) -> str:
        # Compose the directory in which to write the file (if
        # relative it is relative to the target_report_directory).
        weewx_root   : str = str(config_dict.get('WEEWX_ROOT'))
        html_root    : str = str(target_report_dict.get('HTML_ROOT'))
        loop_data_dir: str = str(file_spec_dict.get('loop_data_dir', '.'))
        return os.path.join(weewx_root, html_root, loop_data_dir)

    @staticmethod
    def construct_baro_trend_descs(baro_trend_trans_dict: Dict[str, str]) -> Dict[BarometerTrend, str]:
        baro_trend_descs: Dict[BarometerTrend, str] = {}
        baro_trend_descs[BarometerTrend.RISING_VERY_RAPIDLY]  = baro_trend_trans_dict.get('RISING_VERY_RAPIDLY', 'Rising Very Rapidly')
        baro_trend_descs[BarometerTrend.RISING_QUICKLY]       = baro_trend_trans_dict.get('RISING_QUICKLY',       'Rising Quickly')
        baro_trend_descs[BarometerTrend.RISING]               = baro_trend_trans_dict.get('RISING',               'Rising')
        baro_trend_descs[BarometerTrend.RISING_SLOWLY]        = baro_trend_trans_dict.get('RISING_SLOWLY',        'Rising Slowly')
        baro_trend_descs[BarometerTrend.STEADY]               = baro_trend_trans_dict.get('STEADY',               'Steady')
        baro_trend_descs[BarometerTrend.FALLING_SLOWLY]       = baro_trend_trans_dict.get('FALLING_SLOWLY',       'Falling Slowly')
        baro_trend_descs[BarometerTrend.FALLING]              = baro_trend_trans_dict.get('FALLING',              'Falling')
        baro_trend_descs[BarometerTrend.FALLING_QUICKLY]      = baro_trend_trans_dict.get('FALLING_QUICKLY',      'Falling Quickly')
        baro_trend_descs[BarometerTrend.FALLING_VERY_RAPIDLY] = baro_trend_trans_dict.get('FALLING_VERY_RAPIDLY', 'Falling Very Rapidly')
        return baro_trend_descs

    @staticmethod
    def get_fields_to_include(specified_fields: List[str]
            ) -> Tuple[List[CheetahName], List[str], List[str], List[str], List[str], List[str],
            List[str], List[str], List[str], List[str], List[str], List[str], List[str]]:
        """
        Return fields_to_include, current_obstypes, trend_obstypes, alltime_obstypes,
               rainyear_obstypes, year_obstypes, month_obstypes, week_obstypes,
               day_obstypes, hour_obstypes, twentyfour_hour_obstypes,ten_min_obstypes, two_min_obstypes
        """
        specified_fields = list(dict.fromkeys(specified_fields))
        fields_to_include: List[CheetahName] = []
        for field in specified_fields:
            cname: Optional[CheetahName] = SensorData.parse_cname(field)
            if cname is not None:
                fields_to_include.append(cname)
        current_obstypes  : List[str] = SensorData.compute_period_obstypes(
            fields_to_include, 'current')
        trend_obstypes  : List[str] = SensorData.compute_period_obstypes(
            fields_to_include, 'trend')
        alltime_obstypes    : List[str] = SensorData.compute_period_obstypes(
            fields_to_include, 'alltime')
        rainyear_obstypes    : List[str] = SensorData.compute_period_obstypes(
            fields_to_include, 'rainyear')
        year_obstypes    : List[str] = SensorData.compute_period_obstypes(
            fields_to_include, 'year')
        month_obstypes    : List[str] = SensorData.compute_period_obstypes(
            fields_to_include, 'month')
        week_obstypes    : List[str] = SensorData.compute_period_obstypes(
            fields_to_include, 'week')
        day_obstypes    : List[str] = SensorData.compute_period_obstypes(
            fields_to_include, 'day')
        hour_obstypes    : List[str] = SensorData.compute_period_obstypes(
            fields_to_include, 'hour')
        twentyfour_hour_obstypes: List[str] = SensorData.compute_period_obstypes(
            fields_to_include, '24h')
        ten_min_obstypes: List[str] = SensorData.compute_period_obstypes(
            fields_to_include, '10m')
        two_min_obstypes: List[str] = SensorData.compute_period_obstypes(
            fields_to_include, '2m')

        # current_obstypes is special because current observations are
        # needed to feed all the others.  As such, take the union of all.
        current_obstypes = current_obstypes + trend_obstypes + alltime_obstypes + \
            rainyear_obstypes + year_obstypes + month_obstypes + \
            week_obstypes + day_obstypes + hour_obstypes + twentyfour_hour_obstypes + ten_min_obstypes + two_min_obstypes
        current_obstypes = list(dict.fromkeys(current_obstypes))

        return (fields_to_include, current_obstypes, trend_obstypes, alltime_obstypes,
            rainyear_obstypes, year_obstypes, month_obstypes, week_obstypes,
            day_obstypes, hour_obstypes, twentyfour_hour_obstypes, ten_min_obstypes, two_min_obstypes)

    @staticmethod
    def compute_period_obstypes(fields_to_include: List[CheetahName], period: str) -> List[str]:
        period_obstypes: List[str] = []
        for cname in fields_to_include:
            if cname.period == period:
                period_obstypes.append(cname.obstype)
                if cname.obstype == 'wind':
                    period_obstypes.append('windSpeed')
                    period_obstypes.append('windDir')
                    period_obstypes.append('windGust')
                    period_obstypes.append('windGustDir')
                if cname.obstype == 'appTemp':
                    period_obstypes.append('outTemp')
                    period_obstypes.append('outHumidity')
                    period_obstypes.append('windSpeed')
                if cname.obstype.startswith('windrun'):
                    period_obstypes.append('windSpeed')
                    period_obstypes.append('windDir')
                if cname.obstype == 'beaufort':
                    period_obstypes.append('windSpeed')
        return list(dict.fromkeys(period_obstypes))

    @staticmethod
    def get_target_report_dict(config_dict, report) -> Dict[str, Any]:
        try:
            return weewx.reportengine._build_skin_dict(config_dict, report)
        except AttributeError:
            pass # Load the report dict the old fashioned way below
        try:
            skin_dict = weeutil.config.deep_copy(weewx.defaults.defaults)
        except Exception:
            # Fall back to copy.deepcopy for earlier than weewx 4.1.2 installs.
            skin_dict = copy.deepcopy(weewx.defaults.defaults)
        skin_dict['REPORT_NAME'] = report
        skin_config_path = os.path.join(
            config_dict['WEEWX_ROOT'],
            config_dict['StdReport']['SKIN_ROOT'],
            config_dict['StdReport'][report].get('skin', ''),
            'skin.conf')
        try:
            merge_dict = configobj.ConfigObj(skin_config_path, file_error=True, encoding='utf-8')
            log.debug("Found configuration file %s for report '%s'", skin_config_path, report)
            # Merge the skin config file in:
            weeutil.config.merge_config(skin_dict, merge_dict)
        except IOError as e:
            log.debug("Cannot read skin configuration file %s for report '%s': %s",
                      skin_config_path, report, e)
        except SyntaxError as e:
            log.error("Failed to read skin configuration file %s for report '%s': %s",
                      skin_config_path, report, e)
            raise

        # Now add on the [StdReport][[Defaults]] section, if present:
        if 'Defaults' in config_dict['StdReport']:
            # Because we will be modifying the results, make a deep copy of the [[Defaults]]
            # section.
            try:
                merge_dict = weeutil.config.deep_copy(config_dict['StdReport']['Defaults'])
            except Exception:
                # Fall back to copy.deepcopy for earlier weewx 4 installs.
                merge_dict = copy.deepcopy(config_dict['StdReport']['Defaults'])
            weeutil.config.merge_config(skin_dict, merge_dict)

        # Inject any scalar overrides. This is for backwards compatibility. These options should now go
        # under [StdReport][[Defaults]].
        for scalar in config_dict['StdReport'].scalars:
            skin_dict[scalar] = config_dict['StdReport'][scalar]

        # Finally, inject any overrides for this specific report. Because this is the last merge, it will have the
        # final say.
        weeutil.config.merge_config(skin_dict, config_dict['StdReport'][report])

        return skin_dict

    def pre_loop(self, event):
        if self.loop_processor_started:
            return
        # Start the loop processor thread.
        self.loop_processor_started = True

        try:
            binder = weewx.manager.DBBinder(self.config_dict)
            binding = self.config_dict.get('StdReport')['data_binding']
            dbm = binder.get_manager(binding)

            # Get archive packets to prime accumulators.  First find earliest
            # record we need to fetch.

            # Fetch them just once with the greatest time period.
            now = time.time()

            # We want the earliest time needed.
            start_of_day: int = weeutil.weeutil.startOfDay(now)
            log.debug('Earliest time selected is %s' % timestamp_to_string(start_of_day))

            # Fetch the records.
            start = time.time()
            archive_pkts: List[Dict[str, Any]] = SensorData.get_archive_packets(
                dbm, self.archive_columns, start_of_day)

            # Save packets as appropriate.
            pkt_count: int = 0
            for pkt in archive_pkts:
                pkt_time = pkt['dateTime']
                if 'windrun' in pkt and 'windDir' in pkt and pkt['windDir'] is not None:
                    bkt = LoopProcessor.get_windrun_bucket(pkt['windDir'])
                    pkt['windrun_%s' % windrun_bucket_suffixes[bkt]] = pkt['windrun']
                if len(self.cfg.day_obstypes) > 0 and pkt_time >= start_of_day:
                    self.day_packets.append(pkt)
                pkt_count += 1
            log.debug('Collected %d archive packets in %f seconds.' % (pkt_count, time.time() - start))

            # accumulator_payload_sent is used to only create accumulators on first new_loop packet
            self.accumulator_payload_sent = False
            lp: LoopProcessor = LoopProcessor(self.cfg)
            t: threading.Thread = threading.Thread(target=lp.process_queue)
            t.setName('SensorData')
            t.setDaemon(True)
            t.start()
        except Exception as e:
            # Print problem to log and give up.
            log.error('Error in SensorData setup.  SensorData is exiting. Exception: %s' % e)
            weeutil.logger.log_traceback(log.error, "    ****  ")

    @staticmethod
    def day_summary_records_generator(dbm, obstype: str, earliest_time: int
            ) -> Generator[Dict[str, Any], None, None]:
        table_name = 'archive_day_%s' % obstype
        cols: List[str] = dbm.connection.columnsOf(table_name)
        for row in dbm.genSql('SELECT * FROM %s' \
                ' WHERE dateTime >= %d ORDER BY dateTime ASC' % (table_name, earliest_time)):
            record: Dict[str, Any] = {}
            for i in range(len(cols)):
                record[cols[i]] = row[i]
            log.debug('get_day_summary_records: record(%s): %s' % (
                timestamp_to_string(record['dateTime']), record))
            yield record

    @staticmethod
    def get_archive_packets(dbm, archive_columns: List[str],
            earliest_time: int) -> List[Dict[str, Any]]:
        packets = []
        for cols in dbm.genSql('SELECT * FROM archive' \
                ' WHERE dateTime > %d ORDER BY dateTime ASC' % earliest_time):
            pkt: Dict[str, Any] = {}
            for i in range(len(cols)):
                pkt[archive_columns[i]] = cols[i]
            packets.append(pkt)
            log.debug('get_archive_packets: pkt(%s): %s' % (
                timestamp_to_string(pkt['dateTime']), pkt))
        return packets

    def new_loop(self, event):
        log.debug('new_loop: event: %s' % event)
        if not self.accumulator_payload_sent:
            self.accumulator_payload_sent = True
            binder = weewx.manager.DBBinder(self.config_dict)
            binding = self.config_dict.get('StdReport')['data_binding']
            dbm = binder.get_manager(binding)
            pkt_time = to_int(event.packet['dateTime'])

            # Init day accumulator from day_summary
            day_summary = dbm._get_day_summary(time.time())
            # Init an accumulator
            timespan = weeutil.weeutil.archiveDaySpan(pkt_time)
            unit_system = day_summary.unit_system
            if unit_system is not None:
                # Database has a unit_system already (true unless the db just got intialized.)
                self.cfg.unit_system = unit_system
            day_accum = weewx.accum.Accum(timespan, unit_system=self.cfg.unit_system)
            for k in day_summary:
                day_accum.set_stats(k, day_summary[k].getStatsTuple())
            # Need to add the windrun_<bucket> accumulators.
            for pkt in self.day_packets:
                if day_accum.timespan.includesArchiveTime(pkt['dateTime']):
                    for suffix in windrun_bucket_suffixes:
                        obs = 'windrun_%s' % suffix
                        if obs in pkt:
                            day_accum.add_value(pkt, obs, True, pkt['interval'] * 60)
                            continue
            self.day_packets = []

            alltime_accum, self.cfg.alltime_obstypes = SensorData.create_alltime_accum(
                self.cfg.unit_system, self.cfg.archive_interval, self.cfg.alltime_obstypes, day_accum, dbm)
            rainyear_accum, self.cfg.rainyear_obstypes = SensorData.create_rainyear_accum(
                self.cfg.unit_system, self.cfg.archive_interval, self.cfg.rainyear_obstypes, pkt_time, self.cfg.rainyear_start, day_accum, dbm)
            year_accum, self.cfg.year_obstypes = SensorData.create_year_accum(
                self.cfg.unit_system, self.cfg.archive_interval, self.cfg.year_obstypes, pkt_time, day_accum, dbm)
            month_accum, self.cfg.month_obstypes = SensorData.create_month_accum(
                self.cfg.unit_system, self.cfg.archive_interval, self.cfg.month_obstypes, pkt_time, day_accum, dbm)
            week_accum, self.cfg.week_obstypes = SensorData.create_week_accum(
                self.cfg.unit_system, self.cfg.archive_interval, self.cfg.week_obstypes, pkt_time, self.cfg.week_start, day_accum, dbm)
            hour_accum, self.cfg.hour_obstypes = SensorData.create_hour_accum(
                self.cfg.unit_system, self.cfg.archive_interval, self.cfg.hour_obstypes, pkt_time, day_accum, dbm)
            twentyfour_hour_accum, self.cfg.twentyfour_hour_obstypes = SensorData.create_continuous_accum(
                '24h', self.cfg.unit_system, self.cfg.archive_interval, self.cfg.twentyfour_hour_obstypes, 86400, day_accum, dbm)
            ten_min_accum, self.cfg.ten_min_obstypes = SensorData.create_continuous_accum(
                '10m', self.cfg.unit_system, self.cfg.archive_interval, self.cfg.ten_min_obstypes, 600, day_accum, dbm)
            two_min_accum, self.cfg.two_min_obstypes = SensorData.create_continuous_accum(
                '2m', self.cfg.unit_system, self.cfg.archive_interval, self.cfg.two_min_obstypes, 120, day_accum, dbm)
            trend_accum, self.cfg.trend_obstypes = SensorData.create_continuous_accum(
                'trend', self.cfg.unit_system, self.cfg.archive_interval, self.cfg.trend_obstypes, self.cfg.time_delta, day_accum, dbm)
            self.cfg.queue.put(AccumulatorPayload(
                alltime_accum         = alltime_accum,
                rainyear_accum        = rainyear_accum,
                year_accum            = year_accum,
                month_accum           = month_accum,
                week_accum            = week_accum,
                day_accum             = day_accum,
                hour_accum            = hour_accum,
                twentyfour_hour_accum = twentyfour_hour_accum,
                ten_min_accum         = ten_min_accum,
                two_min_accum         = two_min_accum,
                trend_accum           = trend_accum))
        self.cfg.queue.put(event)

    @staticmethod
    def create_alltime_accum(unit_system: int, archive_interval: int, obstypes: List[str], 
            day_accum: weewx.accum.Accum, dbm) -> Tuple[Optional[weewx.accum.Accum], List[str]]:
        log.debug('Creating alltime_accum')
        # Pick a timespan such that all observations will be included
        # Span from Friday, January 2, 1970 12:00:00 AM UTC to January 1, 2525 12:00:00 AM UTC
        span = weeutil.weeutil.TimeSpan(86400, 17514144000)
        return SensorData.create_period_accum('alltime', unit_system, archive_interval, obstypes, span, day_accum, dbm)

    @staticmethod
    def create_rainyear_accum(unit_system: int, archive_interval: int, obstypes: List[str], pkt_time: int,
            rainyear_start: int, day_accum: weewx.accum.Accum, dbm) -> Tuple[Optional[weewx.accum.Accum], List[str]]:
        log.debug('Creating initial rainyear_accum')
        span = weeutil.weeutil.archiveRainYearSpan(pkt_time, rainyear_start)
        return SensorData.create_period_accum('rainyear', unit_system, archive_interval, obstypes, span, day_accum, dbm)

    @staticmethod
    def create_year_accum(unit_system: int, archive_interval: int, obstypes: List[str], pkt_time: int, day_accum: weewx.accum.Accum, dbm
            ) -> Tuple[Optional[weewx.accum.Accum], List[str]]:
        log.debug('Creating initial year_accum')
        span = weeutil.weeutil.archiveYearSpan(pkt_time)
        return SensorData.create_period_accum('year', unit_system, archive_interval, obstypes, span, day_accum, dbm)

    @staticmethod
    def create_month_accum(unit_system: int, archive_interval: int, obstypes: List[str], pkt_time: int, day_accum: weewx.accum.Accum, dbm
            ) -> Tuple[Optional[weewx.accum.Accum], List[str]]:
        log.debug('Creating initial month_accum')
        span = weeutil.weeutil.archiveMonthSpan(pkt_time)
        return SensorData.create_period_accum('month', unit_system, archive_interval, obstypes, span, day_accum, dbm)

    @staticmethod
    def create_week_accum(unit_system: int, archive_interval: int, obstypes: List[str], pkt_time: int,
            week_start: int, day_accum: weewx.accum.Accum, dbm) -> Tuple[Optional[weewx.accum.Accum], List[str]]:
        log.debug('Creating initial week_accum')
        span = weeutil.weeutil.archiveWeekSpan(pkt_time, week_start)
        return SensorData.create_period_accum('week', unit_system, archive_interval, obstypes, span, day_accum, dbm)

    @staticmethod
    def create_hour_accum(unit_system: int, archive_interval: int, obstypes: List[str], pkt_time: int, day_accum: weewx.accum.Accum, dbm
            ) -> Tuple[Optional[weewx.accum.Accum], List[str]]:
        log.debug('Creating initial hour_accum')
        span = weeutil.weeutil.archiveHoursAgoSpan(pkt_time)
        return SensorData.create_period_accum('hour', unit_system, archive_interval, obstypes, span, day_accum, dbm)

    @staticmethod
    def create_period_accum(name: str, unit_system: int, archive_interval: int, obstypes: List[str],
            span: weeutil.weeutil.TimeSpan, day_accum: weewx.accum.Accum, dbm) -> Tuple[Optional[weewx.accum.Accum], List[str]]:
        """return period accumulator and (possibly trimmed) obstypes"""

        if len(obstypes) == 0:
            return None, []

        start = time.time()
        accum = weewx.accum.Accum(span, unit_system)

        # valid observation types will be returned
        valid_obstypes: List[str] = []

        # for each obstype, create the appropriate stats.
        for obstype in obstypes:
            stats: Optional[Any] = None
            if obstype not in day_accum:
                # Obstypes implemented with xtypes will fall out here.
                # As well as typos or any obstype that is not in day_accum.
                log.info('Ignoring %s for %s time period as this observation has no day accumulator.'
                    % (obstype, name))
                continue
            valid_obstypes.append(obstype)
            if type(day_accum[obstype]) == weewx.accum.ScalarStats:
                stats = weewx.accum.ScalarStats()
            elif type(day_accum[obstype]) == weewx.accum.VecStats:
                stats = weewx.accum.VecStats()
            elif type(day_accum[obstype]) == weewx.accum.FirstLastAccum:
                stats = weewx.accum.FirstLastAccum()
            else:
                return None, []
            record_count = 0
            # For periods > day, accumulate from day summary records.
            # hour accumulator is handled by reading archive records (see below).
            if  name != 'hour':
                for record in SensorData.day_summary_records_generator(dbm, obstype, span.start):
                    record_count += 1
                    # TODO(jkline): From above, it appears that stats cannot be None.
                    if stats is None:
                        # Figure out the stats type
                        if 'squaresum' in record:
                            stats = weewx.accum.VecStats()
                        elif 'wsum' in record:
                            stats = weewx.accum.ScalarStats()
                        elif 'last' in record:
                            stats = weewx.accum.FirstLastAccum()
                        else:
                            return None, []
                    if type(stats) == weewx.accum.ScalarStats:
                        sstat = weewx.accum.ScalarStats((record['min'], record['mintime'],
                            record['max'], record['maxtime'],
                            record['sum'], record['count'],
                            record['wsum'], record['sumtime']))
                        stats.mergeHiLo(sstat)
                        stats.mergeSum(sstat)
                    elif type(stats) == weewx.accum.VecStats:
                        vstat = weewx.accum.VecStats((record['min'], record['mintime'],
                            record['max'], record['maxtime'],
                            record['sum'], record['count'],
                            record['wsum'], record['sumtime'],
                            record['max_dir'], record['xsum'], record['ysum'],
                            record['dirsumtime'], record['squaresum'], record['wsquaresum']))
                        stats.mergeHiLo(vstat)
                        stats.mergeSum(vstat)
                    else:  # FirstLastAccum():
                        fstat = weewx.accum.FirstLastAccum((record['first'], record['firsttime'],
                            record['last'], record['lasttime']))
                        stats.mergeHiLo(fstat)
                        stats.mergeSum(fstat)
                # Add in today's stats
                stats.mergeHiLo(day_accum[obstype])
                stats.mergeSum(day_accum[obstype])
            accum[obstype] = stats

        if  name == 'hour':
            # Fetch archive records to prime the hour accumulator.
            earliest_time = span[0]
            start = time.time()
            pkt_count: int = 0
            archive_columns: List[str] = dbm.connection.columnsOf('archive')
            archive_pkts: List[Dict[str, Any]] = SensorData.get_archive_packets(
                dbm, archive_columns, earliest_time)
            for pkt in archive_pkts:
                pkt_time = pkt['dateTime']
                pkt['usUnits'] = unit_system
                pruned_pkt = LoopProcessor.prune_period_packet(pkt_time, pkt, obstypes)
                accum.addRecord(pruned_pkt, weight=archive_interval * 60)
                pkt_count += 1
            log.debug('Primed hour_accum with %d archive packets in %f seconds.' % (pkt_count, time.time() - start))

        log.debug('Created %s accum in %f seconds (read %d records).' % (name, time.time() - start, record_count))
        return accum, valid_obstypes

    @staticmethod
    def create_continuous_accum(name: str, unit_system: int, archive_interval: int, obstypes: List[str],
            timelength, day_accum: weewx.accum.Accum, dbm) -> Tuple[Optional[ContinuousAccum], List[str]]:
        """return continously accumulator and (possibly trimmed) obstypes"""

        if len(obstypes) == 0:
            return None, []

        accum = ContinuousAccum(timelength, unit_system)

        # valid observation types will be returned
        valid_obstypes: List[str] = []

        # for each obstype, create the appropriate stats.
        for obstype in obstypes:
            stats: Optional[Any] = None
            if obstype not in day_accum:
                # Obstypes implemented with xtypes will fall out here.
                # As well as typos or any obstype that is not in day_accum.
                log.info('Ignoring %s for %s time period as this observation has no day accumulator.'
                    % (obstype, name))
                continue
            valid_obstypes.append(obstype)
            if type(day_accum[obstype]) == weewx.accum.ScalarStats:
                stats = ContinuousScalarStats(timelength)
            elif type(day_accum[obstype]) == weewx.accum.VecStats:
                stats = ContinuousVecStats(timelength)
            elif type(day_accum[obstype]) == weewx.accum.FirstLastAccum:
                stats = ContinuousFirstLastAccum(timelength)
            else:
                return None, []
            accum[obstype] = stats

        # Fetch archive records to prime the accumulator.
        start = time.time()
        earliest_time = start - timelength
        pkt_count: int = 0
        archive_columns: List[str] = dbm.connection.columnsOf('archive')
        archive_pkts: List[Dict[str, Any]] = SensorData.get_archive_packets(
            dbm, archive_columns, earliest_time)
        for pkt in archive_pkts:
            pkt_time = pkt['dateTime']
            pkt['usUnits'] = unit_system
            pruned_pkt = LoopProcessor.prune_period_packet(pkt_time, pkt, obstypes)
            accum.addRecord(pruned_pkt, weight=archive_interval * 60)
            pkt_count += 1
        log.debug('Primed ContinousAccum(%s) with %d archive packets in %f seconds.' % (name, pkt_count, time.time() - start))

        log.debug('Created %s accum in %f seconds (read %d records).' % (name, time.time() - start, pkt_count))
        return accum, valid_obstypes

    @staticmethod
    def parse_cname(field: str) -> Optional[CheetahName]:
        valid_prefixes    : List[str] = [ 'unit' ]
        valid_prefixes2   : List[str] = [ 'label' ]
        valid_periods     : List[str] = [ 'alltime', 'rainyear', 'year', 'month', 'week',
                                          'current', 'hour', '2m', '10m', '24h', 'day',
                                          'trend' ]
        valid_agg_types   : List[str] = [ 'max', 'min', 'maxtime', 'mintime',
                                          'gustdir', 'avg', 'sum', 'vecavg',
                                          'vecdir', 'rms' ]
        valid_format_specs: List[str] = [ 'formatted', 'raw', 'ordinal_compass',
                                          'desc', 'code' ]

        segment: List[str] = field.split('.')
        if len(segment) < 2:
            return None

        next_seg = 0

        prefix = None
        prefix2 = None
        if segment[next_seg] in valid_prefixes:
            prefix = segment[next_seg]
            next_seg += 1
            if segment[next_seg] in valid_prefixes2:
                prefix2 = segment[next_seg]
                next_seg += 1
            else:
                return None

        period = None
        if prefix is None: # All but $unit must have a period.
            if len(segment) < next_seg:
                return None
            if segment[next_seg] in valid_periods:
                period = segment[next_seg]
                next_seg += 1
            else:
                return  None

        if len(segment) < next_seg:
            # need an obstype, but none there
            return None
        obstype = segment[next_seg]
        next_seg += 1

        agg_type = None
        # 2m/10m/24h/hour/day/week/month/year/rainyear/alltime must have an agg_type
        if period in [ '2m', '10m', '24h', 'hour', 'day', 'week','month', 'year', 'rainyear', 'alltime' ]:
            if len(segment) <= next_seg:
                return None
            if segment[next_seg] not in valid_agg_types:
                return None
            agg_type = segment[next_seg]
            next_seg += 1

        format_spec = None
        # check for a format spec
        if prefix is None and len(segment) > next_seg:
            if segment[next_seg] in valid_format_specs:
                format_spec = segment[next_seg]
                next_seg += 1

        # windrun_<dir> is not supported for week, month, year, rainyear and alltime
        if obstype.startswith('windrun_') and (
                period == 'week' or period == 'month' or period == 'year' or period == 'rainyear' or period == 'alltime'):
            return None

        if len(segment) > next_seg:
            # There is more.  This is unexpected.
            return None

        return CheetahName(
            field       = field,
            prefix      = prefix,
            prefix2     = prefix2,
            period      = period,
            obstype     = obstype,
            agg_type    = agg_type,
            format_spec = format_spec)

class LoopProcessor:
    def __init__(self, cfg: Configuration):
        self.cfg = cfg
        self.archive_start: float = time.time()

    def process_queue(self) -> None:
        try:
            while True:
                event               = self.cfg.queue.get()

                if type(event) == AccumulatorPayload:
                    LoopProcessor.log_configuration(self.cfg)
                    self.alltime_accum = event.alltime_accum
                    self.rainyear_accum = event.rainyear_accum
                    self.year_accum = event.year_accum
                    self.month_accum = event.month_accum
                    self.week_accum = event.week_accum
                    self.day_accum = event.day_accum
                    self.hour_accum = event.hour_accum
                    self.twentyfour_hour_accum = event.twentyfour_hour_accum
                    self.ten_min_accum = event.ten_min_accum
                    self.two_min_accum = event.two_min_accum
                    self.trend_accum = event.trend_accum
                    continue

                # This is a loop packet.
                assert event.event_type == weewx.NEW_LOOP_PACKET

                pkt: Dict[str, Any] = event.packet
                pkt_time: int       = to_int(pkt['dateTime'])
                pkt['interval']     = self.cfg.loop_frequency / 60.0

                log.debug('Dequeued loop event(%s): %s' % (event, timestamp_to_string(pkt_time)))
                log.debug(pkt)

                try:
                    windrun_val = weewx.wxxtypes.WXXTypes.calc_windrun('windrun', pkt)
                    pkt['windrun'] = windrun_val[0]
                    if windrun_val[0] > 0.00 and 'windDir' in pkt and pkt['windDir'] is not None:
                        bkt = LoopProcessor.get_windrun_bucket(pkt['windDir'])
                        pkt['windrun_%s' % windrun_bucket_suffixes[bkt]] = windrun_val[0]
                except weewx.CannotCalculate:
                    log.info('Cannot calculate windrun.')
                    pass

                try:
                    beaufort_val = weewx.wxxtypes.WXXTypes.calc_beaufort('beaufort', pkt)
                    pkt['beaufort'] = beaufort_val[0]
                except weewx.CannotCalculate:
                    log.info('Cannot calculate beaufort.')
                    pass

                # Process new packet.
                (loopdata_pkt, self.alltime_accum, self.rainyear_accum, self.year_accum,
                    self.month_accum, self.week_accum, self.day_accum,
                    self.hour_accum) = LoopProcessor.generate_loopdata_dictionary(
                    pkt, pkt_time, self.cfg.unit_system,
                    self.cfg.loop_frequency, self.cfg.converter, self.cfg.formatter,
                    self.cfg.fields_to_include, self.cfg.current_obstypes,
                    self.alltime_accum, self.cfg.alltime_obstypes,
                    self.rainyear_accum, self.cfg.rainyear_start, self.cfg.rainyear_obstypes,
                    self.year_accum, self.cfg.year_obstypes,
                    self.month_accum, self.cfg.month_obstypes,
                    self.week_accum, self.cfg.week_start, self.cfg.week_obstypes,
                    self.day_accum, self.cfg.day_obstypes,
                    self.hour_accum, self.cfg.hour_obstypes,
                    self.twentyfour_hour_accum, self.cfg.twentyfour_hour_obstypes,
                    self.ten_min_accum, self.cfg.ten_min_obstypes,
                    self.two_min_accum, self.cfg.two_min_obstypes,
                    self.cfg.time_delta,
                    self.trend_accum, self.cfg.trend_obstypes,
                    self.cfg.baro_trend_descs)

                # Write the loop-data.txt file.
                LoopProcessor.write_packet_to_file(loopdata_pkt,
                    self.cfg.tmpname, self.cfg.loop_data_dir, self.cfg.filename)
                if self.cfg.enable:
                    # Rsync the loop-data.txt file.
                    LoopProcessor.rsync_data(pkt_time,
                        self.cfg.skip_if_older_than, self.cfg.loop_data_dir,
                        self.cfg.filename, self.cfg.remote_dir,
                        self.cfg.remote_server, self.cfg.remote_port,
                        self.cfg.timeout, self.cfg.remote_user,
                        self.cfg.ssh_options, self.cfg.compress,
                        self.cfg.log_success)
        except Exception:
            weeutil.logger.log_traceback(log.critical, "    ****  ")
            raise
        finally:
            os.unlink(self.cfg.tmpname)

    @staticmethod
    def generate_loopdata_dictionary(
            in_pkt: Dict[str, Any], pkt_time: int, unit_system: int,
            loop_frequency: float,
            converter: weewx.units.Converter, formatter: weewx.units.Formatter,
            fields_to_include: List[CheetahName], current_obstypes: List[str],
            alltime_accum: Optional[weewx.accum.Accum], alltime_obstypes: List[str],
            rainyear_accum: Optional[weewx.accum.Accum], rainyear_start: int, rainyear_obstypes: List[str],
            year_accum: Optional[weewx.accum.Accum], year_obstypes: List[str],
            month_accum: Optional[weewx.accum.Accum], month_obstypes: List[str],
            week_accum: Optional[weewx.accum.Accum], week_start: int, week_obstypes: List[str],
            day_accum: weewx.accum.Accum, day_obstypes: List[str],
            hour_accum: weewx.accum.Accum, hour_obstypes: List[str],
            twentyfour_hour_accum: ContinuousAccum, twentyfour_hour_obstypes: List[str],
            ten_min_accum: ContinuousAccum, ten_min_obstypes: List[str],
            two_min_accum: ContinuousAccum, two_min_obstypes: List[str],
            time_delta: int,
            trend_accum: ContinuousAccum, trend_obstypes: List[str],
            baro_trend_descs: Dict[BarometerTrend, str]
            ) -> Tuple[Dict[str, Any], Optional[weewx.accum.Accum], Optional[weewx.accum.Accum], Optional[weewx.accum.Accum],
            Optional[weewx.accum.Accum], Optional[weewx.accum.Accum], Optional[weewx.accum.Accum],
            Optional[weewx.accum.Accum]]:

        # pkt needs to be in the units that the accumulators are expecting.
        pruned_pkt = LoopProcessor.prune_period_packet(pkt_time, in_pkt, current_obstypes)
        pkt = weewx.units.StdUnitConverters[unit_system].convertDict(pruned_pkt)
        pkt['usUnits'] = unit_system

        # Add packet to alltime accumulator.
        # There will never be an OutOfSpan exception.
        if len(alltime_obstypes) > 0 and alltime_accum is not None:
            pruned_pkt = LoopProcessor.prune_period_packet(pkt_time, pkt, alltime_obstypes)
            alltime_accum.addRecord(pruned_pkt, weight=loop_frequency)

        # Add packet to rainyear accumulator.
        try:
          if len(rainyear_obstypes) > 0 and rainyear_accum is not None:
              pruned_pkt = LoopProcessor.prune_period_packet(pkt_time, pkt, rainyear_obstypes)
              rainyear_accum.addRecord(pruned_pkt, weight=loop_frequency)
        except weewx.accum.OutOfSpan:
            timespan = weeutil.weeutil.archiveRainYearSpan(pkt['dateTime'], rainyear_start)
            rainyear_accum = weewx.accum.Accum(timespan, unit_system=unit_system)
            # Try again:
            rainyear_accum.addRecord(pkt, weight=loop_frequency)

        # Add packet to year accumulator.
        try:
          if len(year_obstypes) > 0 and year_accum is not None:
              pruned_pkt = LoopProcessor.prune_period_packet(pkt_time, pkt, year_obstypes)
              year_accum.addRecord(pruned_pkt, weight=loop_frequency)
        except weewx.accum.OutOfSpan:
            timespan = weeutil.weeutil.archiveYearSpan(pkt['dateTime'])
            year_accum = weewx.accum.Accum(timespan, unit_system=unit_system)
            # Try again:
            year_accum.addRecord(pkt, weight=loop_frequency)

        # Add packet to month accumulator.
        try:
          if len(month_obstypes) > 0 and month_accum is not None:
              pruned_pkt = LoopProcessor.prune_period_packet(pkt_time, pkt, month_obstypes)
              month_accum.addRecord(pruned_pkt, weight=loop_frequency)
        except weewx.accum.OutOfSpan:
            timespan = weeutil.weeutil.archiveMonthSpan(pkt['dateTime'])
            month_accum = weewx.accum.Accum(timespan, unit_system=unit_system)
            # Try again:
            month_accum.addRecord(pkt, weight=loop_frequency)

        # Add packet to week accumulator.
        try:
          if len(week_obstypes) > 0 and week_accum is not None:
              pruned_pkt = LoopProcessor.prune_period_packet(pkt_time, pkt, week_obstypes)
              week_accum.addRecord(pruned_pkt, weight=loop_frequency)
        except weewx.accum.OutOfSpan:
            timespan = weeutil.weeutil.archiveWeekSpan(pkt['dateTime'], week_start)
            week_accum = weewx.accum.Accum(timespan, unit_system=unit_system)
            # Try again:
            week_accum.addRecord(pkt, weight=loop_frequency)

        # Add packet to day accumulator.
        try:
          if len(day_obstypes) > 0:
              pruned_pkt = LoopProcessor.prune_period_packet(pkt_time, pkt, day_obstypes)
              day_accum.addRecord(pruned_pkt, weight=loop_frequency)
        except weewx.accum.OutOfSpan:
            timespan = weeutil.weeutil.archiveDaySpan(pkt['dateTime'])
            day_accum = weewx.accum.Accum(timespan, unit_system=unit_system)
            # Try again:
            day_accum.addRecord(pkt, weight=loop_frequency)

        # Add packet to hour accumulator.
        try:
          if len(hour_obstypes) > 0:
              pruned_pkt = LoopProcessor.prune_period_packet(pkt_time, pkt, hour_obstypes)
              hour_accum.addRecord(pruned_pkt, weight=loop_frequency)
        except weewx.accum.OutOfSpan:
            timespan = weeutil.weeutil.archiveHoursAgoSpan(pkt['dateTime'])
            hour_accum = weewx.accum.Accum(timespan, unit_system=unit_system)
            # Try again:
            hour_accum.addRecord(pkt, weight=loop_frequency)

        # Add packet to 24h accumulator.
        if len(twentyfour_hour_obstypes) > 0:
            pruned_pkt = LoopProcessor.prune_period_packet(pkt_time, pkt, twentyfour_hour_obstypes)
            twentyfour_hour_accum.addRecord(pruned_pkt, weight=loop_frequency)

        # Add packet to 10m accumulator.
        if len(ten_min_obstypes) > 0:
            pruned_pkt = LoopProcessor.prune_period_packet(pkt_time, pkt, ten_min_obstypes)
            ten_min_accum.addRecord(pruned_pkt, weight=loop_frequency)

        # Add packet to 2m accumulator.
        if len(two_min_obstypes) > 0:
            pruned_pkt = LoopProcessor.prune_period_packet(pkt_time, pkt, two_min_obstypes)
            two_min_accum.addRecord(pruned_pkt, weight=loop_frequency)

        # Add packet to trend accumulator.
        if len(trend_obstypes) > 0:
            pruned_pkt = LoopProcessor.prune_period_packet(pkt_time, pkt, trend_obstypes)
            trend_accum.addRecord(pruned_pkt, weight=loop_frequency)

        # Create the loopdata dictionary.
        return (LoopProcessor.create_loopdata_packet(pkt,
            fields_to_include, alltime_accum, rainyear_accum,
            year_accum, month_accum, week_accum, day_accum, hour_accum,
            twentyfour_hour_accum, ten_min_accum, two_min_accum, time_delta, trend_accum, baro_trend_descs, converter, formatter),
            alltime_accum, rainyear_accum, year_accum, month_accum, week_accum, day_accum, hour_accum)

    @staticmethod
    def add_unit_obstype(cname: CheetahName, loopdata_pkt: Dict[str, Any],
            converter: weewx.units.Converter,
            formatter: weewx.units.Formatter) -> None:

        if cname.prefix2 == 'label':
            # agg_type not allowed
            # tgt_type, tgt_group = converter.getTargetUnit(cname.obstype, agg_type=cname.agg_type)
            tgt_type, tgt_group = converter.getTargetUnit(cname.obstype)
            loopdata_pkt[cname.field] = formatter.get_label_string(tgt_type)

    @staticmethod
    def add_current_obstype(cname: CheetahName, pkt: Dict[str, Any],
            loopdata_pkt: Dict[str, Any], converter: weewx.units.Converter,
            formatter: weewx.units.Formatter) -> None:

        if cname.obstype not in pkt:
            log.debug('%s not found in packet, skipping %s' % (cname.obstype, cname.field))
            return

        value, unit_type, group_type = LoopProcessor.convert_current_obs(
                converter, cname.obstype, pkt)

        if value is None:
            log.debug('%s not found in loop packet.' % cname.field)
            return

        if cname.format_spec == 'ordinal_compass':
            loopdata_pkt[cname.field] = formatter.to_ordinal_compass(
                (value, unit_type, group_type))
            return

        if cname.format_spec == 'formatted':
            fmt_str = formatter.get_format_string(unit_type)
            try:
                loopdata_pkt[cname.field] = fmt_str % value
            except Exception as e:
                log.debug('%s: %s, %s, %s' % (e, cname.field, fmt_str, value))
            return

        if cname.format_spec == 'raw':
            loopdata_pkt[cname.field] = value
            return

        loopdata_pkt[cname.field] = formatter.toString((value, unit_type, group_type))

    @staticmethod
    def add_period_obstype(cname: CheetahName, period_accum: Union[weewx.accum.Accum, ContinuousAccum],
            loopdata_pkt: Dict[str, Any], converter: weewx.units.Converter,
            formatter: weewx.units.Formatter) -> None:
        if cname.obstype not in period_accum:
            log.debug('No %s stats for %s, skipping %s' % (cname.period, cname.obstype, cname.field))
            return

        stats = period_accum[cname.obstype]

        if (isinstance(stats, weewx.accum.ScalarStats) or isinstance(stats, ContinuousScalarStats))  and stats.lasttime is not None:
            min, mintime, max, maxtime, sum, count, wsum, sumtime = stats.getStatsTuple()
            if cname.agg_type == 'min':
                src_value = min
            elif cname.agg_type == 'mintime':
                src_value = mintime
            elif cname.agg_type == 'max':
                src_value = max
            elif cname.agg_type == 'maxtime':
                src_value = maxtime
            elif cname.agg_type == 'sum':
                src_value = sum
            elif cname.agg_type == 'avg':
                src_value = stats.avg
            else:
                return

        elif (isinstance(stats, weewx.accum.VecStats) or isinstance(stats, ContinuousVecStats)) and stats.count != 0:
            min, mintime, max, maxtime, sum, count, wsum, sumtime, max_dir, xsum, ysum, dirsumtime, squaresum, wsquaresum = stats.getStatsTuple()
            if cname.agg_type == 'maxtime':
                src_value = maxtime
            elif cname.agg_type == 'max':
                src_value = max
            elif cname.agg_type == 'gustdir':
                src_value = max_dir
            elif cname.agg_type == 'mintime':
                src_value = mintime
            elif cname.agg_type == 'min':
                src_value = min
            elif cname.agg_type == 'count':
                src_value = count
            elif cname.agg_type == 'avg':
                src_value = stats.avg
            elif cname.agg_type == 'sum':
                src_value = stats.sum
            elif cname.agg_type == 'rms':
                src_value = stats.rms
            elif cname.agg_type == 'vecavg':
                src_value = stats.vec_avg
            elif cname.agg_type == 'vecdir':
                src_value = stats.vec_dir
            else:
                return

        else:
            # firstlast not currently supported
            return

        if src_value is None:
            log.debug('Currently no %s stats for %s.' % (cname.period, cname.field))
            return

        src_type, src_group = weewx.units.getStandardUnitType(period_accum.unit_system, cname.obstype, agg_type=cname.agg_type)

        tgt_value, tgt_type, tgt_group = converter.convert((src_value, src_type, src_group))

        if cname.format_spec == 'ordinal_compass':
            loopdata_pkt[cname.field] = formatter.to_ordinal_compass(
                (tgt_value, tgt_type, tgt_group))
            return

        if cname.format_spec == 'formatted':
            fmt_str = formatter.get_format_string(tgt_type)
            try:
                loopdata_pkt[cname.field] = fmt_str % tgt_value
            except Exception as e:
                log.debug('%s: %s, %s, %s' % (e, cname.field, fmt_str, tgt_value))
            return

        if cname.format_spec == 'raw':
            loopdata_pkt[cname.field] = tgt_value
            return

        loopdata_pkt[cname.field] = formatter.toString((tgt_value, tgt_type, tgt_group))

    @staticmethod
    def add_trend_obstype(cname: CheetahName, trend_accum: ContinuousAccum,
            pkt: Dict[str, Any], loopdata_pkt: Dict[str, Any], time_delta: int,
            baro_trend_descs, converter: weewx.units.Converter,
            formatter: weewx.units.Formatter) -> None:

        value, unit_type, group_type = LoopProcessor.get_trend(cname, pkt, trend_accum, converter, time_delta)
        if value is None:
            log.debug('add_trend_obstype: %s: get_trend returned None.' % cname.field)
            return

        if cname.obstype == 'barometer' and (cname.format_spec == 'code' or cname.format_spec == 'desc'):
            baroTrend: BarometerTrend = LoopProcessor.get_barometer_trend(value, unit_type, group_type, time_delta)
            if cname.format_spec == 'code':
                loopdata_pkt[cname.field] = baroTrend.value
            else: # cname.format_spec == 'desc':
                loopdata_pkt[cname.field] = baro_trend_descs[baroTrend]
            return
        elif cname.format_spec == 'code' or cname.format_spec == 'desc':
            # code and desc are only supported for trend.barometer
            return

        if cname.format_spec == 'formatted':
            fmt_str = formatter.get_format_string(unit_type)
            try:
                loopdata_pkt[cname.field] = fmt_str % value
            except Exception as e:
                log.debug('%s: %s, %s, %s' % (e, cname.field, fmt_str, value))
            return

        if cname.format_spec == 'raw':
            loopdata_pkt[cname.field] = value
            return

        loopdata_pkt[cname.field] = formatter.toString((value, unit_type, group_type))


    @staticmethod
    def convert_current_obs(converter: weewx.units.Converter, obstype: str,
            pkt: Dict[str, Any]) -> Tuple[Any, Any, Any]:
        """ Returns value, format_str, label_str """

        v_t = weewx.units.as_value_tuple(pkt, obstype)
        _, original_unit_type, original_group_type = v_t
        value, unit_type, group_type = converter.convert(v_t)

        return value, unit_type, group_type

    @staticmethod
    def create_loopdata_packet(pkt: Dict[str, Any],
            fields_to_include: List[CheetahName], alltime_accum: Optional[weewx.accum.Accum],
            rainyear_accum: Optional[weewx.accum.Accum], year_accum: Optional[weewx.accum.Accum],
            month_accum: Optional[weewx.accum.Accum], week_accum: Optional[weewx.accum.Accum],
            day_accum: weewx.accum.Accum,
            hour_accum: weewx.accum.Accum,
            twentyfour_hour_accum: Optional[ContinuousAccum],
            ten_min_accum: Optional[ContinuousAccum],
            two_min_accum: Optional[ContinuousAccum],
            time_delta: int,
            trend_accum: Optional[ContinuousAccum],
            baro_trend_descs: Dict[BarometerTrend, str],
            converter: weewx.units.Converter, formatter: weewx.units.Formatter) -> Dict[str, Any]:

        loopdata_pkt: Dict[str, Any] = {}

        # Iterate through fields.
        for cname in fields_to_include:
            if cname is None:
                continue
            if cname.prefix == 'unit':
                LoopProcessor.add_unit_obstype(cname, loopdata_pkt, converter, formatter)
                continue
            if cname.period == 'current':
                LoopProcessor.add_current_obstype(cname, pkt, loopdata_pkt, converter, formatter)
                continue
            if cname.period == 'trend' and trend_accum is not None:
                LoopProcessor.add_trend_obstype(cname, trend_accum, pkt,
                    loopdata_pkt, time_delta, baro_trend_descs, converter, formatter)
                continue
            if cname.period == 'alltime' and alltime_accum is not None:
                LoopProcessor.add_period_obstype(cname, alltime_accum, loopdata_pkt, converter, formatter)
                continue
            if cname.period == 'rainyear' and rainyear_accum is not None:
                LoopProcessor.add_period_obstype(cname, rainyear_accum, loopdata_pkt, converter, formatter)
                continue
            if cname.period == 'year' and year_accum is not None:
                LoopProcessor.add_period_obstype(cname, year_accum, loopdata_pkt, converter, formatter)
                continue
            if cname.period == 'month' and month_accum is not None:
                LoopProcessor.add_period_obstype(cname, month_accum, loopdata_pkt, converter, formatter)
                continue
            if cname.period == 'week' and week_accum is not None:
                LoopProcessor.add_period_obstype(cname, week_accum, loopdata_pkt, converter, formatter)
                continue
            if cname.period == 'day':
                LoopProcessor.add_period_obstype(cname, day_accum, loopdata_pkt, converter, formatter)
                continue
            if cname.period == 'hour':
                LoopProcessor.add_period_obstype(cname, hour_accum, loopdata_pkt, converter, formatter)
                continue
            if cname.period == '24h' and twentyfour_hour_accum is not None:
                LoopProcessor.add_period_obstype(cname, twentyfour_hour_accum, loopdata_pkt, converter, formatter)
                continue
            if cname.period == '10m' and ten_min_accum is not None:
                LoopProcessor.add_period_obstype(cname, ten_min_accum, loopdata_pkt, converter, formatter)
                continue
            if cname.period == '2m' and two_min_accum is not None:
                LoopProcessor.add_period_obstype(cname, two_min_accum, loopdata_pkt, converter, formatter)
                continue

        return loopdata_pkt

    @staticmethod
    def write_packet_to_file(selective_pkt: Dict[str, Any], tmpname: str,
            loop_data_dir: str, filename: str) -> None:
        log.debug('Writing packet to %s' % tmpname)
        with open(tmpname, "w") as f:
            f.write(json.dumps(selective_pkt))
            f.flush()
            os.fsync(f.fileno())
        log.debug('Wrote to %s' % tmpname)
        # move it to filename
        shutil.move(tmpname, os.path.join(loop_data_dir, filename))
        log.debug('Moved to %s' % os.path.join(loop_data_dir, filename))

    @staticmethod
    def log_configuration(cfg: Configuration):
        # queue
        # config_dict
        log.info('unit_system             : %d' % cfg.unit_system)
        log.info('archive_interval        : %d' % cfg.archive_interval)
        log.info('loop_data_dir           : %s' % cfg.loop_data_dir)
        log.info('filename                : %s' % cfg.filename)
        log.info('target_report           : %s' % cfg.target_report)
        log.info('loop_frequency          : %s' % cfg.loop_frequency)
        log.info('specified_fields        : %s' % cfg.specified_fields)
        # fields_to_include
        # formatter
        # converter
        log.info('tmpname                 : %s' % cfg.tmpname)
        log.info('enable                  : %d' % cfg.enable)
        log.info('remote_server           : %s' % cfg.remote_server)
        log.info('remote_port             : %r' % cfg.remote_port)
        log.info('remote_user             : %s' % cfg.remote_user)
        log.info('remote_dir              : %s' % cfg.remote_dir)
        log.info('compress                : %d' % cfg.compress)
        log.info('log_success             : %d' % cfg.log_success)
        log.info('ssh_options             : %s' % cfg.ssh_options)
        log.info('timeout                 : %d' % cfg.timeout)
        log.info('skip_if_older_than      : %d' % cfg.skip_if_older_than)
        log.info('time_delta              : %d' % cfg.time_delta)
        log.info('week_start              : %d' % cfg.week_start)
        log.info('rainyear_start          : %d' % cfg.rainyear_start)
        log.info('trend_obstypes          : %s' % cfg.trend_obstypes)
        log.info('alltime_obstypes        : %s' % cfg.alltime_obstypes)
        log.info('rainyear_obstypes       : %s' % cfg.rainyear_obstypes)
        log.info('year_obstypes           : %s' % cfg.year_obstypes)
        log.info('month_obstypes          : %s' % cfg.month_obstypes)
        log.info('week_obstypes           : %s' % cfg.week_obstypes)
        log.info('day_obstypes            : %s' % cfg.day_obstypes)
        log.info('hour_obstypes           : %s' % cfg.hour_obstypes)
        log.info('twentyfour_hour_obstypes: %s' % cfg.twentyfour_hour_obstypes)
        log.info('ten_min_obstypes        : %s' % cfg.ten_min_obstypes)
        log.info('two_min_obstypes        : %s' % cfg.two_min_obstypes)
        log.info('baro_trend_descs        : %s' % cfg.baro_trend_descs)

    @staticmethod
    def rsync_data(pktTime: int, skip_if_older_than: int, loop_data_dir: str,
            filename: str, remote_dir: str, remote_server: str,
            remote_port: int, timeout: int, remote_user: str, ssh_options: str,
            compress: bool, log_success: bool) -> None:
        log.debug('rsync_data(%d) start' % pktTime)
        # Don't upload if more than skip_if_older_than seconds behind.
        if skip_if_older_than != 0:
            age = time.time() - pktTime
            if age > skip_if_older_than:
                log.info('skipping packet (%s) with age: %f' % (timestamp_to_string(pktTime), age))
                return
        rsync_upload = weeutil.rsyncupload.RsyncUpload(
            local_root= os.path.join(loop_data_dir, filename),
            remote_root = os.path.join(remote_dir, filename),
            server=remote_server,
            user=remote_user,
            port=int(remote_port) if remote_port is not None else None,
            ssh_options=ssh_options,
            compress=compress,
            delete=False,
            log_success=log_success,
            timeout=timeout)
        try:
            rsync_upload.run()
        except IOError as e:
            (cl, unused_ob, unused_tr) = sys.exc_info()
            log.error("rsync_data: Caught exception %s: %s" % (cl, e))

    @staticmethod
    def get_barometer_trend(value, unit_type, group_type, time_delta: int) -> BarometerTrend:

        # Forecast descriptions for the 3 hour change in barometer readings.
        # Falling (or rising) slowly: 0.1 - 1.5mb in 3 hours
        # Falling (or rising): 1.6 - 3.5mb in 3 hours
        # Falling (or rising) quickly: 3.6 - 6.0mb in 3 hours
        # Falling (or rising) very rapidly: More than 6.0mb in 3 hours

        # Convert to mbars as that is the standard we have for descriptions.
        converter = weewx.units.Converter(weewx.units.MetricUnits)
        delta_mbar, _, _ = converter.convert((value, unit_type, group_type))
        log.debug('Converted to mbar/h: %f' % delta_mbar)

        # Normalize to three hours.
        delta_three_hours = time_delta / 10800.0
        delta_mbar = delta_mbar / delta_three_hours

        if delta_mbar > 6.0:
            baroTrend = BarometerTrend.RISING_VERY_RAPIDLY
        elif delta_mbar > 3.5:
            baroTrend = BarometerTrend.RISING_QUICKLY
        elif delta_mbar > 1.5:
            baroTrend = BarometerTrend.RISING
        elif delta_mbar >= 0.1:
            baroTrend = BarometerTrend.RISING_SLOWLY
        elif delta_mbar > -0.1:
            baroTrend = BarometerTrend.STEADY
        elif delta_mbar >= -1.5:
            baroTrend = BarometerTrend.FALLING_SLOWLY
        elif delta_mbar >= -3.5:
            baroTrend = BarometerTrend.FALLING
        elif delta_mbar >= -6.0:
            baroTrend = BarometerTrend.FALLING_QUICKLY
        else:
            baroTrend = BarometerTrend.FALLING_VERY_RAPIDLY

        return baroTrend

    @staticmethod
    def get_trend(cname: CheetahName, pkt: Dict[str, Any], trend_accum: ContinuousAccum,
            converter, time_delta: int) -> Tuple[Optional[Any], Optional[str], Optional[str]]:
        first = trend_accum[cname.obstype].first
        firsttime = trend_accum[cname.obstype].firsttime
        last = trend_accum[cname.obstype].last
        lasttime = trend_accum[cname.obstype].lasttime
        if first is None or last is None:
            return None, None, None
        if firsttime == lasttime:
            # Need atleast two readings to get a trend.
            return None, None, None
        try:
            # Trend needs to be in report target units.
            start_value, unit_type, group_type = LoopProcessor.convert_current_obs(
                converter, cname.obstype, { 'dateTime': firsttime, 'usUnits': pkt['usUnits'], cname.obstype: first })
            end_value, unit_type, group_type = LoopProcessor.convert_current_obs(
                converter, cname.obstype, { 'dateTime': lasttime, 'usUnits': pkt['usUnits'], cname.obstype: last })

            log.debug('get_trend: %s: start_value: %s' % (cname.obstype, start_value))
            log.debug('get_trend: %s: end_value: %s' % (cname.obstype, end_value))
            if start_value is not None and end_value is not None:
                trend = end_value - start_value
                # This may not be over the entire range of time_delta (e.g., new station startup)
                # Adjust to spread over entire range.
                actual_time_delta = lasttime - firsttime
                adj_trend = time_delta / actual_time_delta * trend
                log.debug('get_trend: %s: %s unadjusted(%s)' % (cname.obstype, adj_trend, trend))
                return adj_trend, unit_type, group_type
        except:
            # Perhaps not a scalar value
            log.debug('Could not compute trend for %s' % cname.obstype)

        return None, None, None

    @staticmethod
    def prune_period_packet(pkt_time: int, pkt: Dict[str, Any], in_use_obstypes: List[str]
            ) -> Dict[str, Any]:
        # Prune to only the observations needed.
        new_pkt: Dict[str, Any] = {}
        new_pkt['dateTime'] = pkt['dateTime']
        new_pkt['usUnits'] = pkt['usUnits']
        if 'interval' in pkt:
            # Probably not needed.
            new_pkt['interval'] = pkt['interval']
        for obstype in in_use_obstypes:
            if obstype in pkt:
                new_pkt[obstype] = pkt[obstype]
        return new_pkt

    @staticmethod
    def get_windrun_bucket(wind_dir: float) -> int:
        bucket_count = len(windrun_bucket_suffixes)
        slice_size: float = 360.0 / bucket_count
        bucket: int = to_int((wind_dir + slice_size / 2.0) / slice_size)
        if bucket >= bucket_count:
            bucket = 0
        log.debug('get_windrun_bucket: wind_dir: %d, bucket: %d' % (wind_dir, bucket))
        return bucket
