##############################################################################
#
# divumwx.py is a collection of WeeWX Services, assembled by Ian Millard,
# which generate realtime data in various formats used by the weewx-Divumwx
# skin template.
# 
# The services are listed below.
#
##############################################################################
# crt.py
# Copyright 2013-2020 Matthew Wall
# Distributed under terms of the GPLv3
# thanks to gary roderick for significant contributions to this code
#
##############################################################################
#
# lastnonzero.py
#
# Copyright (c) 2020 Tom Keffer <tkeffer@gmail.com>
#
# See the file LICENSE.txt for your full rights.
#
##############################################################################
#
# airdensity.py
#
# Copyright (c) 2024 Sean Balfour <seanbalfourdresden@googlemail.com>
#
##############################################################################
#
# WebServices added by Jerry Dietrich and Ian Millard
#
##############################################################################
# 
# Other adaptations by Ian Millard
#
##############################################################################
"""
divumwx.py

A WeeWX extension for the weewx-DivumWX dashboard template.

"""
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
import weewx.units
import weewx.xtypes
from weewx.units import ValueTuple


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


DIVUMWX_VERSION = "0.0.1"

#REQUIRED_WEEWX = "4.6.0"
#if StrictVersion(weewx.__version__) < StrictVersion(REQUIRED_WEEWX):
    #raise weewx.UnsupportedFeature("weewx %s or greater is required, found %s"
                                   #% (REQUIRED_WEEWX, weewx.__version__))

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
weewx.units.obs_group_dict['sunshine_time'] = 'group_interval'

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

class SkyObject():
    def __init__(self, weewx_dict):
        if weewx_dict.get('DivumWXSkyObject').get('enable').upper() != 'TRUE':
            raise Exception("Not Enabled")
        self.sky_object_percent = 0.0
        try:
            thread = threading.Thread(target = self.calculate_sky_object, args = (weewx_dict.get('DivumWXSkyObject'),))
            thread.daemon = True
            thread.start()
            logdbg("SkyObject service has started")
        except Exception as err:
            raise Exception(err)

    
    def calculate_sky_object(self, settings_dict):
        try:
            url_so1 = settings_dict.get('so1_url')
            url_so2 = settings_dict.get('so2_url')
            file_so1 = settings_dict.get('so1_filename')
            file_so2 = settings_dict.get('so2_filename')
            time_interval_so = int(settings_dict.get('so_interval', 600))
            logdbg("SkyObject Url_so 1 " + url_so1)
            logdbg("SkyObject Url_so 2 " + url_so2)
            logdbg("SkyObject File_so 1 " + file_so1)
            logdbg("SkyObject File_so 2 " + file_so2)
            while True:
                logdbg("SkyObject url_so1 exit code " + str(subprocess.call("wget -r -O " + "'" + file_so1 + "'" + " '" + url_so1 + "'", shell = True)))
                os.chmod(file_so1, 0o666)
                logdbg("SkyObject url_so2 exit code " + str(subprocess.call("wget -r -O " + "'" + file_so2 + "'" + " '" + url_so2 + "'", shell = True)))
                os.chmod(file_so2, 0o666)

                time.sleep(time_interval_so)
        except Exception as e:
            logdbg("SkyObject:calculate_sky_object " + str(e))


               
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
            self.so = SkyObject(self.config_dict)
        except Exception as e:
            self.so = None
            loginf("SkyObject Not Installed due to " + str(e))

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
            do_rsync_transfer(self.webserver_addresses, os.path.join(self.remote_html_root, "dvmhighcharts", "json/"), os.path.join(self.config_dict['StdReport']['dvmHighcharts'].get('HTML_ROOT'), 'json/'), self.config_dict['StdReport']['RSYNC'].get('user', None), self.config_dict['StdReport']['RSYNC'].get('port',None))
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
        fields.append(self.format(data, 'maxSolarRad', 1))            # 57 *
        fields.append(self.format(data, 'windGust', 1))               # 58 *
        fields.append(self.format(data, 'stormRain', 1))               # 58 *
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




#
#    Copyright (c) 2019 Tom Keffer <tkeffer@gmail.com>
#
#    See the file LICENSE.txt for your full rights.
#
"""
A WeeWX that parses a file, adding its contents to a WeeWX record.

To use:

1. Include a stanza in your weewx.conf configuration file:

[FilePile]
    filename = /var/tmp/filepile.txt
    unit_system = METRIC  # Or, 'US' or 'METRICWX'
    ignore_value_error = False
    # Map from incoming names, to WeeWX names.
    [[label_map]]
        temp1 = extraTemp1
        humid1 = extraHumid1


2. Add the FilePile service to the list of data_services to be run:

[Engine]
  [[Services]]
    ...
    data_services = user.filepile.FilePile

3. Put this file (filepile.py) in your WeeWX user subdirectory.
For example, if you installed using setup.py,

    cp filepile.py /home/weewx/bin/user

4. Have your external data source write values to the file
('/var/tmp/filepile.txt' in the example above) using the following
format:

    key = value

where 'key' is an observation name, and 'value' is its value.
The value can be 'None'.
"""

import weewx
import weewx.units
from weewx.wxengine import StdService
from weeutil.weeutil import to_float, to_bool

VERSION = "0.4"

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
        syslog.syslog(level, 'filepile: %s:' % msg)

    def logdbg(msg):
        logmsg(syslog.LOG_DEBUG, msg)

    def loginf(msg):
        logmsg(syslog.LOG_INFO, msg)

    def logerr(msg):
        logmsg(syslog.LOG_ERR, msg)


class FilePile(StdService):
    """WeeWX service for augmenting a record with data parsed from a file."""

    def __init__(self, engine, config_dict):
        # Initialize my superclass:
        super(FilePile, self).__init__(engine, config_dict)

        # Extract our stanza from the configuration dicdtionary
        filepile_dict = config_dict.get('FilePile', {})
        # Get the location of the file ...
        self.filename = filepile_dict.get('filename', '/var/tmp/filepile.txt')
        # ... and the unit system it will use
        unit_system_name = filepile_dict.get('unit_system', 'METRICWX').strip().upper()
        # Make sure we know about the unit system. If not, raise an exception.
        if unit_system_name not in weewx.units.unit_constants:
            raise ValueError("FilePile: Unknown unit system: %s" % unit_system_name)
        # Use the numeric code for the unit system
        self.unit_system = weewx.units.unit_constants[unit_system_name]
        self.ignore_value_error = to_bool(filepile_dict.get('ignore_value_error', False))

        # Mapping from variable names to weewx names
        self.label_map = filepile_dict.get('label_map', {})

        loginf("Using %s with the '%s' unit system" % (self.filename, unit_system_name))
        loginf("Label map is %s" % self.label_map)

        # Bind to the NEW_ARCHIVE_RECORD event
        self.bind(weewx.NEW_ARCHIVE_RECORD, self.new_archive_record)

    def new_archive_record(self, event):
        new_record_data = {}
        try:
            with open(self.filename, 'r') as fd:
                for line in fd:
                    eq_index = line.find('=')
                    # Ignore all lines that do not have an equal sign
                    if eq_index == -1:
                        continue
                    name = line[:eq_index].strip()
                    value = line[eq_index + 1:].strip()
                    # Values which are empty strings are interpreted as None
                    if value == '':
                        value_f = None
                    else:
                        try:
                            value_f = to_float(value)
                        except ValueError as e:
                            if self.ignore_value_error:
                                continue
                            logerr("Could not convert to float: %s" % value)
                            raise
                    new_record_data[self.label_map.get(name, name)] = value_f
                # Supply a unit system if one wasn't included in the file
                if 'usUnits' not in new_record_data:
                    new_record_data['usUnits'] = self.unit_system
                # Convert the new values to the same unit system as the record
                target_data = weewx.units.to_std_system(new_record_data, event.record['usUnits'])
                # Add the converted values to the record:
                event.record.update(target_data)
        except IOError as e:
            logerr("Cannot open file. Reason: %s" % e)

#    Copyright (c) 2022 Tom Keffer <tkeffer@gmail.com>
#    See the file LICENSE.txt for your rights.

"""Pick a color on the basis of a value. This version uses information from
the skin configuration file.

*******************************************************************************

This search list extension offers an extra tag:

    'colorize': Returns a color depending on a value

*******************************************************************************

To use this search list extension:

1) Copy this file to the user directory.

    For example, for pip installs:

        cp colorize_3.py ~/weewx-data/bin/user

    For package installers:

        sudo cp colorize_3.py /usr/share/weewx/user

2) Modify the option search_list_extensions in the skin.conf configuration file, adding
the name of this extension.  When you're done, it will look something like this:

    [CheetahGenerator]
        search_list_extensions = user.colorize_3.Colorize

3) Add a section [Colorize] to skin.conf. For example, this version would
allow you to colorize both temperature and UV values:

    [Colorize]
        [[group_temperature]]
            unit_system = metricwx
            default = tomato
            None = lightgray
            [[[upper_bounds]]]
                -10 = magenta
                0 = violet
                10 = lavender
                20 = moccasin
                30 = yellow
                40 = coral
        [[group_uv]]
            unit_system = metricwx
            default = darkviolet
            [[[upper_bounds]]]
                2.4 = limegreen
                5.4 = yellow
                7.4 = orange
                10.4 = red

You can then colorize backgrounds. For example, to colorize an HTML table cell:

<table>
  <tr>
    <td>Outside temperature</td>
    <td style="background-color:$colorize($current.outTemp)">$current.outTemp</td>
  </tr>
</table>

*******************************************************************************
"""

import weewx.units
from weewx.cheetahgenerator import SearchList

class Colorize(SearchList):                                               # 1

    def __init__(self, generator):                                        # 2
        SearchList.__init__(self, generator)
        self.color_tables = self.generator.skin_dict.get('Colorize', {})

    def colorize(self, value_vh):
        """
        Pick a color on the basis of a value. The color table will be obtained
        from the configuration file.

        Args:
            value_vh (ValueHelper): The value, represented as ValueHelper.

        Returns:
            str: A color string.
        """

        # Get the ValueTuple and unit group from the incoming ValueHelper
        value_vt = value_vh.value_t                                       # 3
        unit_group = value_vt.group                                       # 4

        # Make sure unit_group is in the color table, and that the table
        # specifies a unit system.
        if unit_group not in self.color_tables \
                or 'unit_system' not in self.color_tables[unit_group]:    # 5
            return "#00000000"

        # Convert the value to the same unit used by the color table:
        unit_system = self.color_tables[unit_group]['unit_system']        # 6
        converted_vt = weewx.units.convertStdName(value_vt, unit_system)  # 7

        # Check for a value of None
        if converted_vt.value is None:                                    # 8
            return self.color_tables[unit_group].get('none') \
                   or self.color_tables[unit_group].get('None', "#00000000")

        # Search for the value in the color table:
        for upper_bound in self.color_tables[unit_group]['upper_bounds']: # 9
            if converted_vt.value <= float(upper_bound):                  # 10
                return self.color_tables[unit_group]['upper_bounds'][upper_bound]

        return self.color_tables[unit_group].get('default', "#00000000")  # 11


#
#    Copyright (c) 2023 Tom Keffer <tkeffer@gmail.com>
#
#    See the file LICENSE.txt for your full rights.
#

"""Search list extension to calculate when an SQL statement last evaluted true, or how long since it evaluated true.

Example:

    <p>It last rained at $time_at('rain>0') ($time_since('rain>0') ago).</p>

would result in

    <p>It last rained 20 June 2020 (81 days, 1 hour, 35 minutes ago).</p>

"""
from weewx.cheetahgenerator import SearchList

from weewx.units import ValueTuple, ValueHelper

VERSION = "0.4"


class TimeSince(SearchList):
    def get_extension_list(self, timespan, db_lookup):
        def time_since(expression):
            """Time since a sql expression evaluted true"""
            db_manager = db_lookup()
            sql_stmt = "SELECT dateTime FROM %s WHERE %s AND dateTime <= %d ORDER BY dateTime DESC LIMIT 1" \
                       % (db_manager.table_name, expression, timespan.stop)

            row = db_manager.getSql(sql_stmt)
            val = timespan.stop - row[0] if row else None
            vt = ValueTuple(val, 'second', 'group_deltatime')
            vh = ValueHelper(vt,
                             context='month',
                             formatter=self.generator.formatter,
                             converter=self.generator.converter)
            return vh

        def time_at(expression):
            """When an sql expression evaluated true"""
            db_manager = db_lookup()
            sql_stmt = "SELECT dateTime FROM %s WHERE %s AND dateTime <= %d ORDER BY dateTime DESC LIMIT 1" \
                       % (db_manager.table_name, expression, timespan.stop)

            row = db_manager.getSql(sql_stmt)
            val = row[0] if row else None
            vt = ValueTuple(val, 'unix_epoch', 'group_time')
            vh = ValueHelper(vt,
                             formatter=self.generator.formatter,
                             converter=self.generator.converter)
            return vh

        return [{
            'time_since': time_since,
            'time_at': time_at,
        }]

#
#    Copyright (c) 2024 Sean Balfour <seanbalfourdresden@googlemail.com>
#
"""This example shows how to extend the XTypes system with a new type, AirDensity in kg/m³ 

REQUIRES WeeWX V4.2 OR LATER!

To use:
    1. Stop weewx
    2. Put the unitsExtra.py file in your user subdirectory. 
    3. Put the airdensity.py file in your user subdirectory.

    4. In weewx.conf, subsection [Engine][[Services]], 
    add AirDensityService to the list
    "xtype_services". For example, this means changing this

        [Engine]
            [[Services]]
                xtype_services = weewx.wxxtypes.StdWXXTypes, weewx.wxxtypes.StdPressureCooker, weewx.wxxtypes.StdRainRater

    to this:
        [Engine]
            [[Services]]
                xtype_services = weewx.wxxtypes.StdWXXTypes, weewx.wxxtypes.StdPressureCooker, weewx.wxxtypes.StdRainRater, user.airdensity.AirDensityService


    5. Add the following to your weewx.conf:

    [[[[Groups]]]]
                
        group_density = kg_per_meter_cubed   # No Option simply 'kg_per_meter_cubed'

     [[[[StringFormats]]]]
                
        kg_per_meter_cubed = %.5f           

#############################################

    [AirDensity]
        algorithm = simple  # in kg/m³

#############################################

    [StdWXCalculate]
    
        [[Calculations]]
        
            AirDensity = software


you can call the value in your tmpl like this:

// air density
$air_density["air_density"] = $current.AirDensity.format(add_label=False);

    6. Restart weewx

you can call the value in your tmpl like this:

// air density
$air_density["air_density"] = $current.AirDensity.format(add_label=False);

    6. Restart weewx


import math
import weewx
import weewx.units
import weewx.xtypes
from weewx.engine import StdService
from weewx.units import ValueTuple

"""

# Tell the unit system what group our new observation type, 'AirDensity', belongs to:
weewx.units.obs_group_dict['AirDensity'] = "group_density"
weewx.units.USUnits['group_density'] = 'kg_per_meter_cubed'
weewx.units.MetricUnits['group_density'] = 'kg_per_meter_cubed'
weewx.units.MetricWXUnits['group_density'] = 'kg_per_meter_cubed'
weewx.units.default_unit_format_dict['kg_per_meter_cubed'] = '%.5f'
weewx.units.conversionDict['kg_per_meter_cubed'] = {'kg_per_meter_cubed':  lambda x : x * 1.0}
weewx.units.default_unit_label_dict['kg_per_meter_cubed']  = ' kg/m³'

class AirDensity(weewx.xtypes.XType):

    def __init__(self, algorithm='simple'):
        # Save the algorithm to be used.
        self.algorithm = algorithm.lower()

    def get_scalar(self, obs_type, record, db_manager):
        # We only know how to calculate 'AirDensity'. For everything else, raise an exception UnknownType
        if obs_type != 'AirDensity':
            raise weewx.UnknownType(obs_type)

# pressure in hPa 
        if 'barometer' not in record or record['barometer'] is None:
            raise weewx.CannotCalculate(obs_type)
        unit_and_group = weewx.units.getStandardUnitType(record['usUnits'], 'barometer')
        outBarometer_vt = ValueTuple(record['barometer'], *unit_and_group)
        outBarometer_hPa_vt = weewx.units.convert(outBarometer_vt, 'hPa')
        outBarometer_hPa = outBarometer_hPa_vt[0]

# out humidity in %
        if 'outHumidity' not in record or record['outHumidity'] is None:
            raise weewx.CannotCalculate(obs_type)
        unit_and_group = weewx.units.getStandardUnitType(record['usUnits'], 'outHumidity')
        outHumidity_vt = ValueTuple(record['outHumidity'], *unit_and_group)
        outHumidity = outHumidity_vt[0]        

# out temp in °C
        if 'outTemp' not in record or record['outTemp'] is None:
            raise weewx.CannotCalculate(obs_type)        
        unit_and_group = weewx.units.getStandardUnitType(record['usUnits'], 'outTemp')
        outTemp_vt = ValueTuple(record['outTemp'], *unit_and_group)
        outTemp_C_vt = weewx.units.convert(outTemp_vt, 'degree_C')
        outTemp_C = outTemp_C_vt[0]

        if self.algorithm == 'simple':
        # "Simple" algorithm.

            # formula to get vapor pressure in °C
            vpRH = (outHumidity / 100.0) * 6.112 * math.exp(17.67 * outTemp_C / (outTemp_C + 243.5))

            # formula to get air density in kg/m³           
            T = outTemp_C
            P = outBarometer_hPa
            Es = vpRH # vapor pressure in °C
            Rv = 461.4964 # gas constant
            Rd = 287.0531 # gas constant
            tk = T + 273.15
            pv = Es * 100.0
            pd = (P - Es) * 100.0
            Density = (pv / (Rv * tk)) + (pd / (Rd * tk))
            Air_Density = ValueTuple(Density, 'kg_per_meter_cubed', 'group_density')

        return Air_Density

class AirDensityService(StdService):
    """ WeeWX service whose job is to register the XTypes extension AirDensity with the
    XType system.
    """
    def __init__(self, engine, config_dict):
        super(AirDensityService, self).__init__(engine, config_dict)

        # Get the desired algorithm. Default to "simple".
        try:
            algorithm = config_dict['AirDensity']['algorithm']
        except KeyError:
            algorithm = 'simple'

        # Instantiate an instance of AirDensity:
        self.ad = AirDensity(algorithm)
        # Register it:
        weewx.xtypes.xtypes.append(self.ad)

    def shutDown(self):
        # Remove the registered instance:
        weewx.xtypes.xtypes.remove(self.ad)

""" sunshine duration """

import syslog
from math import sin, cos, pi, asin
from datetime import datetime
import time
import weewx
from weewx.wxengine import StdService


weewx.units.obs_group_dict['sunshine_time'] = 'group_interval'

class SunshineDuration(StdService):
    def __init__(self, engine, config_dict):
        # Pass the initialization information on to my superclass:
        super(SunshineDuration, self).__init__(engine, config_dict)

        # Start intercepting events:
        self.bind(weewx.NEW_LOOP_PACKET, self.newLoopPacket)
        self.bind(weewx.NEW_ARCHIVE_RECORD, self.newArchiveRecord)
        self.lastdateTime = 0
        self.LoopDuration = 0
        self.sunshineSeconds = 0
        self.lastThreshold = 0
        self.firstArchive = True
        self.cum_time=0

    def newLoopPacket(self, event):
        """Gets called on a new loop packet event."""
        radiation = event.packet.get('radiation')
        if radiation is not None:
            if self.lastdateTime == 0:
                self.lastdateTime = event.packet.get('dateTime')
            self.LoopDuration = event.packet.get('dateTime') - self.lastdateTime
            self.lastdateTime = event.packet.get('dateTime')
            threshold = self.sunshineThreshold(event.packet.get('dateTime'))
            if radiation > threshold and threshold > 0:
                self.sunshineSeconds += self.LoopDuration
            self.cum_time += self.LoopDuration
            self.lastThreshold = threshold
            logdbg("Calculated LOOP sunshine_time = %f, based on radiation = %f, and threshold = %f" % (
                self.LoopDuration, radiation, threshold))

    def newArchiveRecord(self, event):
        """Gets called on a new archive record event."""
        radiation = event.record.get('radiation')
        threshold = self.sunshineThreshold(event.record.get('dateTime'))
        
        if self.lastdateTime == 0 or self.firstArchive:  # LOOP packets not yet captured : missing archive record extracted from datalogger at start OR first archive record after weewx start
            event.record['sunshine_time'] = 0.0
            event.record['sunshine_time_hours'] = 0.0
            event.record['threshold'] = self.lastThreshold
            if radiation is not None:
                self.lastThreshold = threshold
                if radiation > threshold and threshold > 0:
                    event.record['sunshine_time'] = event.record['interval']
                    event.record['sunshine_time_hours'] = event.record['interval'] / 60
                    event.record['is_sunshine']=1
                    event.record['threshold'] = self.lastThreshold
                else:
                     event.record['is_sunshine']=0
                     event.record['threshold'] = self.lastThreshold
                if self.lastdateTime != 0:  # LOOP already started, this is the first regular archive after weewx start
                    self.firstArchive = False
                loginf("Estimated sunshine duration from archive record= %f min, radiation = %f, and threshold = %f" % (
                    event.record['sunshine_time'], event.record['radiation'], self.lastThreshold))
        else:
            if radiation is not None:
                if radiation > threshold and threshold > 0:
                    event.record['is_sunshine']=1
                    event.record['threshold'] = self.lastThreshold
                else:
                    event.record['is_sunshine']=0
                    event.record['threshold'] = self.lastThreshold
            if self.cum_time > 0:  # do not divide by zero!
                event.record['sunshine_time'] = self.sunshineSeconds/self.cum_time * event.record['interval']
                event.record['sunshine_time_hours'] = self.sunshineSeconds/self.cum_time * event.record['interval'] / 60
            else: 
                 event.record['sunshine_time'] = 0
                 event.record['sunshine_time_hours'] = 0
            loginf("Sunshine duration from loop packets = %f min" % (event.record['sunshine_time']))

        self.sunshineSeconds = 0
        self.cum_time = 0

    def sunshineThreshold(self, mydatetime):
        utcdate = datetime.utcfromtimestamp(mydatetime)
        dayofyear = int(time.strftime("%j", time.gmtime(mydatetime)))
        theta = 360 * dayofyear / 365
        equatorialtime = 0.0172 + 0.4281 * cos((pi / 180) * theta) - 7.3515 * sin(
            (pi / 180) * theta) - 3.3495 * cos(2 * (pi / 180) * theta) - 9.3619 * sin(
            2 * (pi / 180) * theta)

        latitude = float(self.config_dict["Station"]["latitude"])
        longitude = float(self.config_dict["Station"]["longitude"])
        correctedtime = longitude * 4
        declination = asin(0.006918 - 0.399912 * cos((pi / 180) * theta) + 0.070257 * sin(
            (pi / 180) * theta) - 0.006758 * cos(2 * (pi / 180) * theta) + 0.000908 * sin(
            2 * (pi / 180) * theta)) * (180 / pi)
        minutesday = utcdate.hour * 60 + utcdate.minute
        solartime = (minutesday + correctedtime + equatorialtime) / 60
        hourly_angle = (solartime - 12) * 15
        sun_height = asin(sin((pi / 180) * latitude) * sin((pi / 180) * declination) + cos(
            (pi / 180) * latitude) * cos((pi / 180) * declination) * cos((pi / 180) * hourly_angle)) * (180 / pi)
        if sun_height > 3:
            threshold = (0.73 + 0.06 * cos((pi / 180) * 360 * dayofyear / 365)) * 1080 * pow(
                (sin(pi / 180 * sun_height)), 1.25) 
        else :
            threshold=0
        return threshold

