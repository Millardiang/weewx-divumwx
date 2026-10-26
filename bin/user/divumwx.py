##############################################################################
#
# divumwx.py is a collection of WeeWX Services, assembled by Ian Millard,
# which generate realtime data in various formats used by the weewx-Divumwx
# skin template.
# 
# The services are listed below.
#
##############################################################################
#
# airdensity.py and vpd.py
#
# Copyright (c) 2024 Sean Balfour <seanbalfourdresden@googlemail.com>
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
from __future__ import annotations

# --------------------------------------------------------
# WeeWX Live JSON Feed Service — version 3.2.6
# © 2026 Ian Millard, Sean Balfour — GPLv3
#
# Features:
#  • Configurable preferred output unit system (US / METRIC / METRICWX)
#  • Automatic numeric conversion, skipping non-physical fields
#  • Per-update conversion stats (converted / skipped)
#  • Cumulative totals persisted across restarts in .stats file
#  • Startup warning if no loop packets received within 20 s
#  • Journald integration for WeeWX 5.x
#  • Uptime seconds since service start included in metadata
#  • Calculated variables: wind cardinal, beaufort, apparent temp, heat index, humidex, windchill, cloud base
# --------------------------------------------------------

import json
import os
import time
import threading
import logging
import datetime
import math
import re
import glob
import shutil
import subprocess
import collections
import weewx
import weewx.almanac
import weewx.units
from weewx.engine import StdService
from weeutil.weeutil import to_bool, to_int, startOfDay
from weeutil.logger import log_traceback
from weewx.units import to_std_system, obs_group_dict
from typing import Any, Dict, Optional

log = logging.getLogger("user.LiveDataService")

try:
    from user.divumwx_version import DIVUMWX_VERSION
except ImportError:  # divumwx_version.py missing (partial manual install)
    DIVUMWX_VERSION = 'unknown'
logging.getLogger("user.divumwx").info("DivumWX %s loaded", DIVUMWX_VERSION)

if weewx.__version__ < "5":
    raise weewx.UnsupportedFeature("This service requires WeeWX 5.2 or later")

# Try to import ephem for sunrise/sunset calculations
EPHEM_AVAILABLE = False
ASTRAL_MODE = False

try:
    import ephem
    EPHEM_AVAILABLE = True
    log.info("ephem library available for sunrise/sunset calculations")
except ImportError:
    try:
        # Try importing the newer version (some systems may have this)
        import astral
        from astral.sun import sun
        from astral import LocationInfo
        EPHEM_AVAILABLE = True
        ASTRAL_MODE = True
        log.info("astral library available for sunrise/sunset calculations")
    except ImportError:
        EPHEM_AVAILABLE = False
        ASTRAL_MODE = False
        log.warning("Neither ephem nor astral library available. isDay feature disabled.")

# Try to import Skyfield, for the SkyfieldLoopData service (a separate,
# optional live sky/almanac data feed -- its own JSON file, not merged into
# LiveDataService's loop.json; see SkyfieldLoopData's own docstring below
# for why). This is unrelated to the ephem/astral try above, which is only
# for the simple sunrise/sunset LiveDataService uses for isDay -- this is
# the full Skyfield-computed ecliptic/planetary/rise-set/twilight data set.
# Note that Skyfield being importable here doesn't by itself mean the
# separate weewx-skyfield extension is installed and running -- that's
# checked at runtime, inside SkyfieldLoopData itself.
SKYFIELD_AVAILABLE = False
try:
    import skyfield.almanac
    import skyfield.api
    import skyfield.framelib
    from skyfield.nutationlib import mean_obliquity
    from skyfield import eclipselib
    import skyfield.searchlib
    SKYFIELD_AVAILABLE = True
    log.info("skyfield library available for SkyfieldLoopData")
except ImportError:
    SKYFIELD_AVAILABLE = False
    log.warning("skyfield library not available. SkyfieldLoopData service will do nothing.")


class CloudCoverageOverrideService(StdService):
    """
    Overrides the loop packet's native 'cloudcover' field with a
    sky-camera-derived reading from cloud_coverage.json (written by the
    separate weewx-Cloud_coverage extension: https://github.com/
    Millardiang/weewx-Cloud_coverage), but ONLY between sunrise and
    sunset -- the camera reading isn't meaningful after dark, per that
    extension's own documented limitation (twilight/color-temperature
    misclassification), and its background thread doesn't run/write at
    night anyway.

    Deliberately its own small service rather than an addition to
    DataInjectService above: DataInjectService's field-mapping injection
    is unconditional (always overwrites if fresh cached data exists) --
    it has no concept of "only during the day" or "leave the existing
    value alone if this source isn't authoritative right now", and
    bolting that onto a generic, already-working service risked breaking
    its existing sources (airquality, forecast) for the sake of one
    source's very different requirements.

    Binds to NEW_LOOP_PACKET only, not NEW_ARCHIVE_RECORD -- WeeWX's own
    native archiving accumulates loop packets over the archive interval
    and extracts the average for continuous numeric types like
    cloudcover, so once this service has (conditionally) overwritten
    event.packet['cloudcover'] on each loop packet, that native
    accumulation does the rest automatically. No separate archive-time
    logic is needed: the database's cloudcover column just naturally
    reflects whichever source (camera vs. station hardware) was
    authoritative for each loop packet that fed into that archive
    interval, including a sensible blended average on the rare interval
    that straddles sunrise/sunset itself.

    Must be registered BEFORE user.divumwx.LiveDataService in
    weewx.conf's [Engine][[Services]] data_services list, since
    LiveDataService reads event.packet to build loop.json -- if this
    service ran after it, loop.json would still show the old value even
    though the eventual archive record (assembled independently by
    WeeWX's own StdArchive, in a later service group) would be correct.
    install.py's own merge function is responsible for this ordering;
    see its own comment for the registration itself.

    "Available" means cloud_coverage.json was read successfully, is
    fresh (mtime within max_age_seconds), AND its cloudPercent value is
    a genuine number -- anything else (file missing, stale, malformed)
    leaves the packet's original cloudcover value completely untouched,
    so a station's native hardware-reported reading is always the safe
    fallback, exactly matching the client-side dashboard cards' own
    identical fallback behavior for the same file.
    """

    def __init__(self, engine, config_dict):
        super(CloudCoverageOverrideService, self).__init__(engine, config_dict)

        cfg = config_dict.get('CloudCoverageOverride', {})
        self.enable = to_bool(cfg.get('enable', True))
        if not self.enable:
            log.info("CloudCoverageOverrideService: disabled via config")
            return

        # No hardcoded fallback -- same reasoning as LiveDataService's
        # own json_file check just below: a guessed path is wrong for
        # virtually every install except whichever machine a literal
        # was written on. install.py's own merge function should always
        # write this from the site's actual jsondata directory; an
        # absent value here means that merge didn't run or the section
        # was hand-stripped.
        self.json_file = cfg.get('json_file', '').strip()
        if not self.json_file:
            log.error("CloudCoverageOverrideService: no 'json_file' set in "
                      "[CloudCoverageOverride] -- disabling (no event "
                      "handlers registered). Re-run the DivumWX installer, "
                      "or set json_file manually to the weewx-Cloud_coverage "
                      "extension's own json_output_path (typically "
                      "<your DivumWX HTML_ROOT>/jsondata/cloud_coverage.json).")
            return

        self.max_age_seconds = to_int(cfg.get('max_age_seconds', 300))

        # Station location -- read once here; a moving (GPS) station
        # would need this refreshed per-packet, which this service does
        # not do. Same pattern as SkyfieldLoopData's own __init__ above.
        self.lat = engine.stn_info.latitude_f
        self.lon = engine.stn_info.longitude_f
        altitude_vt = weewx.units.convert(engine.stn_info.altitude_vt, 'meter')
        self.altitude_m = altitude_vt[0]

        self._warned_missing = False

        self.bind(weewx.NEW_LOOP_PACKET, self.new_loop_packet)
        log.info("CloudCoverageOverrideService: watching %s (max_age=%ds)",
                 self.json_file, self.max_age_seconds)

    def _is_day(self, time_ts):
        """Sun altitude > 0 -- "day" is precisely "between sunrise and
        sunset" by definition, matching the same standard the client-
        side dashboard fix uses throughout (almanac.json's
        almanac.sun.alt). Uses weewx's own built-in almanac (already
        imported at the top of this file as weewx.almanac, no extra
        dependency) rather than duplicating LiveDataService's own
        private ephem/astral sunrise-caching machinery above, which is
        bound to that service's own instance state and not straightforward
        to reuse from here.

        WeeWX 5.x renamed almanac sun/moon/planet properties from the
        older bare name (e.g. .alt) to a longer one (.altitude), and
        made them all return a ValueHelper rather than a bare float --
        confirmed via WeeWX's own 5.0/5.1 changelog ("Most almanac
        properties are now returned as ValueHelpers... use
        $almanac.venus.altitude instead of $almanac.venus.alt") and its
        own mailing list ("To get the embedded number, use the '.raw'
        suffix"). Using the old bare .alt name or skipping .raw would
        silently misbehave rather than raise -- this file already
        requires WeeWX 5.0+ (see install.py's MIN_WEEWX_VERSION check),
        so .altitude.raw is the correct call here, not .alt.
        """
        try:
            alm = weewx.almanac.Almanac(time_ts, self.lat, self.lon, self.altitude_m)
            sun_alt_raw = alm.sun.altitude.raw
            if sun_alt_raw is None:
                return None
            return sun_alt_raw > 0
        except Exception as e:
            log.warning("CloudCoverageOverrideService: almanac calculation "
                       "failed (%s) -- leaving cloudcover untouched this packet.", e)
            return None

    def new_loop_packet(self, event):
        if not self.enable:
            return

        time_ts = event.packet.get('dateTime')
        if time_ts is None:
            return

        is_day = self._is_day(time_ts)
        if not is_day:
            # Night, or the almanac calculation itself failed -- leave
            # the packet's native cloudcover value (whatever the
            # station's own hardware driver reported) completely
            # untouched. This is the expected fallback path, not an
            # error condition.
            return

        try:
            if not os.path.isfile(self.json_file):
                if not self._warned_missing:
                    log.info("CloudCoverageOverrideService: %s not found -- "
                            "falling back to native cloudcover (will keep "
                            "checking silently until it appears).",
                            self.json_file)
                    self._warned_missing = True
                return

            age = time.time() - os.path.getmtime(self.json_file)
            if age > self.max_age_seconds:
                log.debug("CloudCoverageOverrideService: %s is %.0fs old "
                         "(max %ds) -- falling back to native cloudcover.",
                         self.json_file, age, self.max_age_seconds)
                return

            with open(self.json_file, 'r') as f:
                data = json.load(f)

            cloud_pct = data.get('cloudPercent')
            if not isinstance(cloud_pct, (int, float)) or isinstance(cloud_pct, bool):
                log.warning("CloudCoverageOverrideService: %s has no valid "
                           "cloudPercent (%r) -- falling back to native "
                           "cloudcover.", self.json_file, cloud_pct)
                return

            event.packet['cloudcover'] = float(cloud_pct)
            self._warned_missing = False

        except (OSError, ValueError) as e:
            log.warning("CloudCoverageOverrideService: could not read %s "
                       "(%s) -- falling back to native cloudcover.",
                       self.json_file, e)


class LiveDataService(StdService):
    SYSTEM_FIELDS = {'dateTime', 'usUnits', 'interval', 'rxCheckPercent'}
    NON_PHYSICAL_FIELDS = (
        'battery', 'voltage', 'signal', 'rssi', 'tx', 'rx',
        'pm', 'aqi', 'voc', 'co2', 'lightning', 'strike', 'count',
        'vpd'  # VPD is always in kPa, don't convert
    )
    
    # Wind direction mappings
    WIND_DIRECTIONS = [
        'North', 'NNE', 'NE', 'ENE', 'East', 'ESE', 'SE', 'SSE',
        'South', 'SSW', 'SW', 'WSW', 'West', 'WNW', 'NW', 'NNW'
    ]
    
    # Beaufort scale descriptions
    BEAUFORT_DESCRIPTIONS = [
        'Calm', 'Light air', 'Light breeze', 'Gentle breeze',
        'Moderate breeze', 'Fresh breeze', 'Strong breeze', 'Near gale',
        'Gale', 'Strong gale', 'Storm', 'Violent storm', 'Hurricane'
    ]

    # Beaufort scale descriptions
    BEAUFORT_COLORS = [
        '#85a3aa', '#7e98bb', '#6e90d0', '#0f94a7',
        '#39a239', '#c2863e', '#c8420d', '#d20032',
        '#af5088', '#754a92', '#45698d', '#c1fc77', '#f1ff6c'
    ]

    def __init__(self, engine, config_dict):
        super().__init__(engine, config_dict)
        cfg = config_dict.get('LiveData', {})

        # Core config
        # No hardcoded fallback here on purpose -- any guessed path is
        # wrong for virtually every install except whichever machine this
        # literal was originally written on. install.py's own
        # apply_livedata_merge() always writes this from the site's
        # actual HTML_ROOT during configure(), so an absent json_file
        # means the section was hand-stripped or install.py's merge
        # didn't run. LiveDataService has no separate enable/disable
        # flag or gate elsewhere in this class, so bail out here, before
        # any binds are registered -- no handlers means the service sits
        # inert rather than crashing on the first loop packet.
        self.json_file = cfg.get('json_file', '').strip()
        if not self.json_file:
            log.error("LiveDataService: no 'json_file' set in [LiveData] -- disabling "
                      "(no event handlers will be registered). Re-run the DivumWX "
                      "installer, or set json_file manually to <your DivumWX "
                      "HTML_ROOT>/jsondata/loop.json.")
            return
        self.stats_file = f"{self.json_file}.stats"
        self.update_interval = to_int(cfg.get('update_interval', 2))
        self.include_archive = to_bool(cfg.get('include_archive', False))
        self.pretty_print = to_bool(cfg.get('pretty_print', True))
        self.flat_structure = to_bool(cfg.get('flat_structure', False))
        self.max_field_log = to_int(cfg.get('max_field_log', 500))

        # Preferred unit system
        self.unit_system_str = str(cfg.get('unit_system', 'US')).upper().strip()
        self.unit_map = {'US': weewx.US, 'METRIC': weewx.METRIC, 'METRICWX': weewx.METRICWX}
        self.unit_system = self.unit_map.get(self.unit_system_str, weewx.US)

        # Station location for sunrise/sunset calculations
        self.station_config = config_dict.get('Station', {})
        
        # Parse latitude - handle string or numeric
        lat_val = self.station_config.get('latitude', 0.0)
        if isinstance(lat_val, (list, tuple)):
            self.latitude = float(lat_val[0])
        else:
            self.latitude = float(lat_val)
        
        # Parse longitude - handle string or numeric
        lon_val = self.station_config.get('longitude', 0.0)
        if isinstance(lon_val, (list, tuple)):
            self.longitude = float(lon_val[0])
        else:
            self.longitude = float(lon_val)
        
        # Parse altitude - handle list format [value, unit] or simple value
        alt_val = self.station_config.get('altitude', 0.0)
        if isinstance(alt_val, (list, tuple)):
            self.altitude = float(alt_val[0])
        else:
            self.altitude = float(alt_val)
        
        # NOTE: there used to be a manual "tz_offset" config-driven correction
        # applied to sunrise/sunset timestamps here (self.timezone below).
        # Removed entirely -- it was compensating for a real bug in
        # _calculate_sunrise_sunset_ephem/_calculate_sunrise_sunset_astral
        # (time.mktime() misinterpreting naive-UTC datetime fields as local
        # time, silently shifting sunrise/sunset by exactly the system's
        # local UTC offset -- see those functions' docstrings). 'tz_offset'
        # was never a real weewx.conf [Station] key, so this compensation was
        # inert for virtually every install (self.timezone defaulted to 0),
        # which is exactly why the underlying bug went unnoticed until a
        # non-UTC station (Alex, Belgium, UTC+2) reported isDay running 2h
        # early. Fixing the root cause makes any such config-driven
        # compensation not just unnecessary but actively wrong again the
        # moment someone sets tz_offset to "fix" it locally, so it's gone
        # rather than left in place unused.

        log.info(f"Station location: lat={self.latitude}, lon={self.longitude}, alt={self.altitude}m")
        
        # Check if we have a valid location
        if self.latitude == 0.0 and self.longitude == 0.0:
            log.warning("Station location not configured or at 0,0. isDay calculations may be inaccurate.")
        
        # Initialize sunrise/sunset calculator
        self.sun_calculator = None
        self.astral_mode = ASTRAL_MODE
        
        if EPHEM_AVAILABLE:
            if self.astral_mode:
                self.sun_calculator = self._init_astral()
            else:
                self.sun_calculator = self._init_ephem()
        else:
            log.warning("Sunrise/sunset calculations disabled. Install ephem or astral library.")

        # Runtime state
        self.data_cache = {}
        self.known_fields = set()
        self.cache_lock = threading.Lock()
        self.last_write = 0
        self.received_packet = False
        self.last_conv_stats = (0, 0)
        self.total_conv_stats = self._load_persistent_stats()  # persistent totals
        self.json_write_count = 0  # for the periodic INFO summary in _update_json_file()
        self.last_sun_calc_date = None  # Cache sunrise/sunset for current day
        self.cached_sunrise = None
        self.cached_sunset = None

        # Uptime / restart
        self.start_time = time.time()
        self.restart_time_iso = time.strftime('%Y-%m-%dT%H:%M:%SZ', time.gmtime())

        # Persistence
        self.field_file = f"{self.json_file}.fields"
        self._load_known_fields()
        self._initialize_json_file()

        # Bind handlers
        self.bind(weewx.NEW_LOOP_PACKET, self.new_loop_packet)
        if self.include_archive:
            self.bind(weewx.NEW_ARCHIVE_RECORD, self.new_archive_record)

        log.info(f"LiveDataService ready — unit system: {self.unit_system_str}, "
                 f"output: {self.json_file}, interval: {self.update_interval}s")
        threading.Timer(20.0, self._check_first_packet).start()

    # ---------------------------------------------------------------------
    # Sunrise/Sunset calculation initialization
    # ---------------------------------------------------------------------

    def _init_ephem(self):
        """Initialize ephem observer for sunrise/sunset calculations."""
        try:
            observer = ephem.Observer()
            observer.lat = str(self.latitude)
            observer.lon = str(self.longitude)
            observer.elevation = self.altitude
            log.debug("ephem observer initialized for sunrise/sunset calculations")
            return observer
        except Exception as e:
            log.error(f"Failed to initialize ephem: {e}")
            return None

    def _init_astral(self):
        """Initialize astral location for sunrise/sunset calculations."""
        try:
            # Create a location object
            # Note: astral uses meters for elevation
            location = LocationInfo(
                name="Weather Station",
                region="Local",
                timezone="UTC",  # We'll handle timezone offset manually
                latitude=self.latitude,
                longitude=self.longitude
            )
            log.debug("astral location initialized for sunrise/sunset calculations")
            return location
        except Exception as e:
            log.error(f"Failed to initialize astral: {e}")
            return None

    def _calculate_sunrise_sunset_ephem(self, date):
        """
        Calculate sunrise and sunset times using ephem, as true Unix epoch
        timestamps (timezone-invariant -- correct regardless of the system's
        local timezone).

        ephem.Date.datetime() returns a NAIVE datetime whose numeric fields
        are UTC clock values (ephem's internal representation is always UT).
        The previous implementation ran those naive-UTC fields through
        time.mktime(), which assumes its input is LOCAL wall-clock time and
        converts using the system's local timezone -- silently shifting the
        result by exactly the system's local UTC offset, then attempting
        (ineffectively, since it was never actually configured) to correct
        for that via a separate tz_offset compensation. Explicitly attaching
        UTC tzinfo before calling .timestamp() sidesteps local-timezone
        interpretation entirely. See beta report from Alex (Belgium, UTC+2):
        isDay was flipping 2 hours before actual sunrise/sunset.
        """
        try:
            self.sun_calculator.date = date
            sunrise = self.sun_calculator.previous_rising(ephem.Sun())
            sunset = self.sun_calculator.next_setting(ephem.Sun())

            sunrise_ts = sunrise.datetime().replace(tzinfo=datetime.timezone.utc).timestamp()
            sunset_ts = sunset.datetime().replace(tzinfo=datetime.timezone.utc).timestamp()

            return sunrise_ts, sunset_ts
        except Exception as e:
            log.error(f"Error calculating sunrise/sunset with ephem: {e}")
            return None, None

    def _calculate_sunrise_sunset_astral(self, date):
        """
        Calculate sunrise and sunset times using astral, as true Unix epoch
        timestamps. astral's sun() is called with tzinfo=utc below, so
        s['sunrise']/s['sunset'] are already timezone-AWARE UTC datetimes --
        calling .timestamp() directly on them gives the correct epoch
        regardless of the system's local timezone. The previous
        implementation discarded that tzinfo by going through
        .timetuple() + time.mktime(), which reintroduces the same
        local-timezone misinterpretation bug as the ephem path above (see
        that function's docstring) -- .timestamp() on an aware datetime
        never has that problem, so there's nothing to compensate for
        afterwards.
        """
        try:
            # Convert date to datetime at noon (astral expects datetime)
            dt = datetime.datetime(date.year, date.month, date.day, 12, 0, 0)
            
            # Calculate sun times
            s = sun(self.sun_calculator.observer, date=dt, tzinfo=datetime.timezone.utc)
            
            sunrise_ts = s['sunrise'].timestamp()
            sunset_ts = s['sunset'].timestamp()
            
            return sunrise_ts, sunset_ts
        except Exception as e:
            log.error(f"Error calculating sunrise/sunset with astral: {e}")
            return None, None

    def _get_sunrise_sunset_for_date(self, date):
        """Get sunrise and sunset times for a given date, with caching."""
        # Check if we already calculated for this date
        if (self.last_sun_calc_date and 
            self.last_sun_calc_date.year == date.year and
            self.last_sun_calc_date.month == date.month and
            self.last_sun_calc_date.day == date.day and
            self.cached_sunrise and self.cached_sunset):
            return self.cached_sunrise, self.cached_sunset
        
        # Calculate fresh
        if self.sun_calculator:
            if self.astral_mode:
                sunrise_ts, sunset_ts = self._calculate_sunrise_sunset_astral(date)
            else:
                sunrise_ts, sunset_ts = self._calculate_sunrise_sunset_ephem(date)
            
            if sunrise_ts and sunset_ts:
                self.cached_sunrise = sunrise_ts
                self.cached_sunset = sunset_ts
                self.last_sun_calc_date = date
                log.debug(f"Calculated sunrise/sunset for {date}: sunrise={sunrise_ts}, sunset={sunset_ts}")
                return sunrise_ts, sunset_ts
        
        return None, None

    # ---------------------------------------------------------------------
    # Weather Calculations
    # ---------------------------------------------------------------------

    def _calculate_wind_direction_cardinal(self, degrees):
        """Convert wind direction in degrees to cardinal direction."""
        if degrees is None:
            return None
        
        # Normalize to 0-360
        degrees = degrees % 360
        
        # Calculate index (16 directions)
        index = int((degrees + 11.25) / 22.5) % 16
        return self.WIND_DIRECTIONS[index]

    def _calculate_beaufort_scale(self, wind_speed):
        """Calculate Beaufort scale from wind speed.
        Wind speed should be in the current unit system.
        Returns tuple: (beaufort_number, description)
        """
        if wind_speed is None:
            return None, None
        
        # Convert to knots for Beaufort calculation
        if self.unit_system == weewx.US:
            # mph to knots
            knots = wind_speed * 0.868976
        elif self.unit_system == weewx.METRICWX:
            # m/s to knots
            knots = wind_speed * 1.94384
        else:
            # km/h to knots
            knots = wind_speed * 0.539957
        
        # Beaufort scale thresholds in knots
        if knots < 1:
            beaufort = 0
        elif knots < 4:
            beaufort = 1
        elif knots < 7:
            beaufort = 2
        elif knots < 11:
            beaufort = 3
        elif knots < 17:
            beaufort = 4
        elif knots < 22:
            beaufort = 5
        elif knots < 28:
            beaufort = 6
        elif knots < 34:
            beaufort = 7
        elif knots < 41:
            beaufort = 8
        elif knots < 48:
            beaufort = 9
        elif knots < 56:
            beaufort = 10
        elif knots < 64:
            beaufort = 11
        else:
            beaufort = 12
        
        return beaufort, self.BEAUFORT_DESCRIPTIONS[beaufort]

    def _calculate_beaufort_colors(self, wind_speed):
        """Calculate Beaufort scale from wind speed.
        Wind speed should be in the current unit system.
        Returns tuple: (beaufort_number, description)
        """
        if wind_speed is None:
            return None, None
        
        # Convert to knots for Beaufort calculation
        if self.unit_system == weewx.US:
            # mph to knots
            knots = wind_speed * 0.868976
        elif self.unit_system == weewx.METRICWX:
            # m/s to knots
            knots = wind_speed * 1.94384
        else:
            # km/h to knots
            knots = wind_speed * 0.539957
        
        # Beaufort scale thresholds in knots
        if knots < 1:
            beaufort = 0
        elif knots < 4:
            beaufort = 1
        elif knots < 7:
            beaufort = 2
        elif knots < 11:
            beaufort = 3
        elif knots < 17:
            beaufort = 4
        elif knots < 22:
            beaufort = 5
        elif knots < 28:
            beaufort = 6
        elif knots < 34:
            beaufort = 7
        elif knots < 41:
            beaufort = 8
        elif knots < 48:
            beaufort = 9
        elif knots < 56:
            beaufort = 10
        elif knots < 64:
            beaufort = 11
        else:
            beaufort = 12
        
        return beaufort, self.BEAUFORT_COLORS[beaufort]

    def _calculate_barometer_color(self, barometer):
        """Return a color hex string based on barometer value (hPa / mbar).
        Thresholds are upper-bound inclusive, evaluated low-to-high.
        Returns default amber if value is above all thresholds, grey if None.
        """
        if barometer is None:
            return '#808080'  # grey

        # Convert inHg → hPa when running in US unit system
        if self.unit_system == weewx.US:
            baro_hpa = barometer * 33.8639
        else:
            baro_hpa = barometer

        if baro_hpa <= 969.0:
            return '#ff00ff'
        elif baro_hpa <= 989.0:
            return '#f8d747'
        elif baro_hpa <= 1009.0:
            return '#007fff'
        elif baro_hpa <= 1029.0:
            return '#2e8b57'
        elif baro_hpa <= 1059.0:
            return '#ff6347'
        else:
            return '#eba141'  # default

    def _calculate_temperature_color(self, temp):
        """Return a color hex string based on temperature value.
        Thresholds are defined in Celsius, upper-bound inclusive.
        Returns lightgray if None.
        """
        if temp is None:
            return '#d3d3d3'  # lightgray

        # Normalise to Celsius for threshold comparison
        if self.unit_system == weewx.US:
            temp_c = (temp - 32) * 5 / 9
        else:
            temp_c = temp

        if temp_c <= -10:
            return '#8781bd'
        elif temp_c <= 0:
            return '#487ea9'
        elif temp_c <= 5:
            return '#369cac'
        elif temp_c <= 10:
            return '#9aba2f'
        elif temp_c <= 20:
            return '#eba141'
        elif temp_c <= 25:
            return '#ec5a34'
        elif temp_c <= 30:
            return '#d05f2d'
        elif temp_c <= 35:
            return '#d65b4a'
        elif temp_c <= 40:
            return '#dc4953'
        else:
            return '#e26870'

    def _calculate_wind_color(self, wind_speed):
        """Return a color hex string based on wind speed value.
        Thresholds are defined in m/s, upper-bound inclusive.
        Returns lightgray if None.
        """
        if wind_speed is None:
            return '#d3d3d3'  # lightgray

        # Normalise to m/s for threshold comparison
        if self.unit_system == weewx.US:
            wind_ms = wind_speed * 0.44704       # mph → m/s
        elif self.unit_system == weewx.METRIC:
            wind_ms = wind_speed / 3.6           # km/h → m/s
        else:
            wind_ms = wind_speed                 # METRICWX already m/s

        if wind_ms <= 1:
            return '#85a3aa'
        elif wind_ms <= 2:
            return '#7e98bb'
        elif wind_ms <= 3:
            return '#6e90d0'
        elif wind_ms <= 5:
            return '#0f94a7'
        elif wind_ms <= 8:
            return '#39a239'
        elif wind_ms <= 11:
            return '#c2863e'
        elif wind_ms <= 14:
            return '#c8420d'
        elif wind_ms <= 17:
            return '#d20032'
        elif wind_ms <= 21:
            return '#af5088'
        elif wind_ms <= 24:
            return '#754a92'
        elif wind_ms <= 28:
            return '#45698d'
        elif wind_ms <= 32:
            return '#c1fc77'
        else:
            return '#f1ff6c'

    def _calculate_rain_color(self, rain):
        """Return a color hex string based on rain/hail accumulation or rate (mm or mm/hr).
        Thresholds are upper-bound inclusive.
        Returns dark background color for None or zero, default amber above all thresholds.
        """
        if rain is None:
            return 'rgb(59,61,64)'

        # Convert inches → mm when running in US unit system
        if self.unit_system == weewx.US:
            rain_mm = rain * 25.4
        else:
            rain_mm = rain

        if rain_mm <= 0:
            return 'rgb(59,61,64)'
        elif rain_mm <= 1:
            return '#83818e'
        elif rain_mm <= 5:
            return '#615884'
        elif rain_mm <= 10:
            return '#34758e'
        elif rain_mm <= 30:
            return '#0b8c88'
        elif rain_mm <= 40:
            return '#359f35'
        elif rain_mm <= 80:
            return '#a79d51'
        elif rain_mm <= 120:
            return '#9f7f3a'
        elif rain_mm <= 250:
            return '#be4c07'
        elif rain_mm <= 500:
            return '#cf2848'
        elif rain_mm <= 750:
            return '#af5088'
        elif rain_mm <= 1000:
            return '#d476a3'
        elif rain_mm <= 1500:
            return '#fa9dbe'
        elif rain_mm <= 2000:
            return '#dcdcdc'
        else:
            return '#eba141'  # default

    def _calculate_uv_color(self, uv):
        """Return a color hex string based on UV index value.
        UV index is dimensionless — no unit conversion required.
        Thresholds are upper-bound inclusive.
        Returns grey for None, zero, or above all thresholds (default).
        """
        if uv is None:
            return 'grey'

        if uv <= 0:
            return 'grey'
        elif uv <= 2.9:
            return '#6fc77b'
        elif uv <= 5.9:
            return '#fed42d'
        elif uv <= 7.9:
            return '#fd8620'
        elif uv <= 10.9:
            return '#fb1215'
        elif uv <= 20:
            return '#de257b'
        else:
            return 'grey'  # default

    def _calculate_radiation_color(self, radiation):
        """Return a color hex string based on solar radiation value (W/m²).
        W/m² is unit-system independent — no conversion required.
        Thresholds are upper-bound inclusive.
        Returns grey for None, zero, or above all thresholds (default).
        """
        if radiation is None:
            return 'grey'

        if radiation <= 0:
            return 'grey'
        elif radiation <= 300:
            return '#f9de8a'
        elif radiation <= 600:
            return '#ffc367'
        elif radiation <= 900:
            return '#ffa242'
        elif radiation <= 1200:
            return '#fd8b17'
        elif radiation <= 1500:
            return '#ff7400'
        else:
            return 'grey'  # default

    def _calculate_apparent_temperature(self, temp, humidity, wind_speed):
        """Calculate Australian Apparent Temperature (Steadman 1984)."""
        if temp is None or humidity is None or wind_speed is None:
            return None
        
        # Convert to Celsius and m/s for calculation
        if self.unit_system == weewx.US:
            temp_c = (temp - 32) * 5/9
            wind_ms = wind_speed * 0.44704  # mph to m/s
        elif self.unit_system == weewx.METRIC:
            temp_c = temp
            wind_ms = wind_speed / 3.6      # km/h to m/s
        elif self.unit_system == weewx.METRICWX:
            temp_c = temp
            wind_ms = wind_speed            # m/s already
        else:
            # Fallback for unexpected unit systems
            temp_c = temp
            wind_ms = wind_speed
        
        # Water vapor pressure (hPa) using Magnus-Tetens approximation
        e = (humidity / 100) * 6.105 * math.exp(17.27 * temp_c / (237.7 + temp_c))
        
        # Apparent temperature formula (Australian BOM)
        at_c = temp_c + 0.33 * e - 0.7 * wind_ms - 4.0
        
        # Convert back to original unit system
        if self.unit_system == weewx.US:
            return at_c * 9/5 + 32
        else:
            return at_c

    def _calculate_heat_index(self, temp, humidity):
        """Calculate Heat Index (US NWS / Rothfusz formula)."""
        if temp is None or humidity is None:
            return None
        
        # Convert to Fahrenheit for calculation
        if self.unit_system == weewx.US:
            temp_f = temp
        else:
            temp_f = temp * 9/5 + 32
        
        # Heat Index is generally not calculated below 80°F
        # Returning the actual temperature is better than None for "live data"
        if temp_f < 80:
            return temp

        # Rothfusz regression
        hi = (-42.379 + 2.04901523 * temp_f + 10.14333127 * humidity - 
              0.22475541 * temp_f * humidity - 6.83783e-3 * temp_f**2 - 
              5.481717e-2 * humidity**2 + 1.22874e-3 * temp_f**2 * humidity + 
              8.5282e-4 * temp_f * humidity**2 - 1.99e-6 * temp_f**2 * humidity**2)
        
        # Adjustments
        if humidity < 13 and 80 <= temp_f <= 112:
            hi -= ((13 - humidity) / 4) * math.sqrt((17 - abs(temp_f - 95)) / 17)
        elif humidity > 85 and 80 <= temp_f <= 87:
            hi += ((humidity - 85) / 10) * ((87 - temp_f) / 5)
        
        # Convert back
        if self.unit_system == weewx.US:
            return hi
        else:
            return (hi - 32) * 5/9

    def _calculate_humidex(self, temp, humidity):
        """Calculate Canadian Humidex."""
        if temp is None or humidity is None:
            return None
        
        # Convert to Celsius for calculation
        if self.unit_system == weewx.US:
            temp_c = (temp - 32) * 5/9
        else:
            temp_c = temp
        
        # Dewpoint calculation (Magnus-Tetens)
        a = 17.27
        b = 237.7
        # Ensure humidity is not 0 to avoid math.log error
        rh = max(humidity, 0.1)
        alpha = ((a * temp_c) / (b + temp_c)) + math.log(rh / 100)
        dewpoint = (b * alpha) / (a - alpha)
        
        # Vapor pressure calculation (hPa)
        # Using 5417.7530 as the standard Canadian constant
        e = 6.11 * math.exp(5417.7530 * ((1/273.16) - (1/(dewpoint + 273.16))))
        
        # Humidex increment
        h = 0.5555 * (e - 10.0)
        humidex_c = temp_c + h
        
        # Check if we should return the calculated value or just the temp
        # Environment Canada typically only reports Humidex if it is >= 1 higher than temp
        if (humidex_c - temp_c) < 1.0:
            result_c = temp_c
        else:
            result_c = humidex_c
        
        # Convert back to current unit system
        if self.unit_system == weewx.US:
            return result_c * 9/5 + 32
        else:
            return result_c

    def _calculate_windchill(self, temp, wind_speed):
        """Calculate Wind Chill (US/Canadian formula)."""
        if temp is None or wind_speed is None:
            return None
        
        # Convert to Fahrenheit and mph for calculation
        if self.unit_system == weewx.US:
            temp_f = temp
            wind_mph = wind_speed
        elif self.unit_system == weewx.METRICWX:
            temp_f = temp * 9/5 + 32
            wind_mph = wind_speed * 2.23694  # m/s to mph
        else:
            # Assume METRIC (km/h)
            temp_f = temp * 9/5 + 32
            wind_mph = wind_speed * 0.621371  # km/h to mph
        
        # Wind chill is only defined for temps <= 50°F and wind > 3 mph
        if temp_f > 50 or wind_mph <= 3:
            result_f = temp_f
        else:
            # US/Canadian wind chill formula
            result_f = (35.74 + 
                        0.6215 * temp_f - 
                        35.75 * (wind_mph ** 0.16) + 
                        0.4275 * temp_f * (wind_mph ** 0.16))
        
        # Convert back to current unit system
        if self.unit_system == weewx.US:
            return result_f
        else:
            return (result_f - 32) * 5/9

    def _calculate_vpd(self, temp, humidity):
        """Calculate Vapor Pressure Deficit (VPD) in kPa."""
        if temp is None or humidity is None:
            return None
        
        # Convert to Celsius for calculation
        if self.unit_system == weewx.US:
            temp_c = (temp - 32) * 5/9
        else:
            temp_c = temp
        
        # Constrain humidity between 0 and 100
        rh = max(min(humidity, 100.0), 0.0)
        
        # Saturation vapor pressure (SVP) in kPa (Buck 1981)
        svp = 0.61121 * math.exp((18.678 - temp_c / 234.5) * (temp_c / (257.14 + temp_c)))
        
        # VPD = SVP * (1 - RH/100)
        # This is mathematically identical to (SVP - AVP)
        vpd = svp * (1.0 - (rh / 100.0))
        
        return vpd

        # weewx.units.obs_group_dict['wetbulbTemp'] = 'group_temperature'
    def _calculate_wet_bulb_temp(self, temp, humidity):
        if temp is None or humidity is None:
            return None
    
        # Ensure humidity is not negative (math safety)
        H = max(0.0, float(humidity))
    
        # Convert to Celsius: Roland Stull formula requires Celsius and 0-100 humidity
        if self.unit_system == weewx.US:
            T = (temp - 32.0) * 5.0/9.0
        else:
            T = float(temp)

        # Roland Stull Formula
        # Result is in Celsius
        wbt_c = (
                T * math.atan(0.151977 * math.sqrt(H + 8.313659)) 
                + math.atan(T + H) 
                - math.atan(H - 1.676331) 
                + 0.00391838 * math.pow(H, 1.5) * math.atan(0.023101 * H) 
                - 4.686035
        )
    
        # Return in original unit system
        if self.unit_system == weewx.US:
            return wbt_c * 9.0/5.0 + 32.0
        return wbt_c

    def _calculate_cloud_base(self, temp, dewpoint):
        """Calculate estimated cloud base height (Hennig formula) AMSL."""
        if temp is None or dewpoint is None:
            return None

        # 1. Access the station altitude from the config
        # Note: WeeWX stores this as a tuple (value, "unit") e.g., (120.0, "meter")
        stn_alt_tuple = self.config_dict['Station'].get('altitude')
        if stn_alt_tuple is None:
            stn_alt_val = 0.0
        else:
            # Convert tuple to float (e.g., "120.0")
            stn_alt_val = float(stn_alt_tuple[0])

        # 2. Convert units for calculation (Formula constant 4.4 is F-based)
        if self.unit_system == weewx.US:
            temp_f = temp
            dewpoint_f = dewpoint
        else:
            temp_f = (temp * 9/5) + 32
            dewpoint_f = (dewpoint * 9/5) + 32

        # 3. Calculate spread
        spread_f = temp_f - dewpoint_f
        if spread_f < 0:
            spread_f = 0
    
        # 4. Hennig formula: Result in Feet (Height Above Ground Level)
        cloud_base_agl_ft = (spread_f / 4.4) * 1000

        # 5. Add Station Altitude and Convert Back
        if self.unit_system == weewx.US:
            # If config is in meters but system is US, convert altitude first
            if stn_alt_tuple[1] == 'meter':
                stn_alt_val *= 3.28084
            return cloud_base_agl_ft + stn_alt_val
        else:
            # Convert Feet to Meters for AGL
            cloud_base_agl_m = cloud_base_agl_ft * 0.3048
            # If config is in feet but system is Metric, convert altitude first
            if stn_alt_tuple[1] == 'foot':
                stn_alt_val *= 0.3048
            return cloud_base_agl_m + stn_alt_val

    # Helper function to get Dewpoint and Cloud Base data
    def _calculate_dewpoint(self, temp, humidity):
        # Convert to Celsius for standard formula
        if self.unit_system == weewx.US:
            t = (temp - 32) * 5/9
        else:
            t = temp
            
        rh = max(humidity, 0.1)
        # Magnus-Tetens Formula
        a, b = 17.27, 237.7
        alpha = ((a * t) / (b + t)) + math.log(rh / 100.0)
        dp_c = (b * alpha) / (a - alpha)
        
        # Return in original unit system
        if self.unit_system == weewx.US:
            return dp_c * 9/5 + 32
        return dp_c

    def new_loop_packet(self, event):
        # 1. Pull the raw radiation value
        solarRad = event.packet.get('radiation')
    
        # 2. Calculate Lux using your function
        if solarRad is not None:
            event.packet['lux'] = self._calculate_lux(solarRad)

    def _calculate_lux(self, solarRad):
        # Ensure value is float and handle None
        try:
            return float(solarRad) * 126.7
        except (TypeError, ValueError):
            return None

    def _calculate_air_density(self, temp, humidity, barometer):
        if temp is None or humidity is None or barometer is None:
            return None

        # 1. UNIT CONVERSION — based on the packet's actual, known unit
        # system (already normalized to self.unit_system by
        # _convert_packet_safely before this is ever called), not
        # guessed from the value's magnitude. The previous magnitude
        # heuristic ("temp > 60 means Fahrenheit, else already Celsius")
        # was wrong for any ordinary cool/cold UK reading on the default
        # US unit_system — a 45°F morning (≈7°C) was silently treated as
        # if it were already 45°C, badly wrong for roughly half the year.
        if self.unit_system == weewx.US:
            temp_c = (float(temp) - 32.0) * 5.0 / 9.0
            barometer_hPa = float(barometer) * 33.8639
        else:
            # METRIC / METRICWX: temp is already °C, and mbar == hPa
            # numerically, so barometer needs no conversion either.
            temp_c = float(temp)
            barometer_hPa = float(barometer)

        # 2. VAPOR PRESSURE CALCULATION
        a = (17.67 * temp_c) / (temp_c + 243.5)
        vpRH = (float(humidity) / 100.0) * 6.112 * math.exp(a)

        # 3. AIR DENSITY CALCULATION (kg/m³)           
        Rv = 461.4964 
        Rd = 287.0531 
        tk = temp_c + 273.15 
    
        pv = vpRH * 100.0 
        pd = (barometer_hPa - vpRH) * 100.0 
    
        air_density = (pv / (Rv * tk)) + (pd / (Rd * tk))
        return air_density

    def _add_calculated_values(self, packet):
        """Add all calculated weather values to the packet."""
        try:
            # Get base values (use converted values from cache if available)
            temp = packet.get('outTemp') or self.data_cache.get('outTemp')
            barometer = packet.get('barometer') or self.data_cache.get('barometer')
            humidity = packet.get('outHumidity') or self.data_cache.get('outHumidity')
            wind_speed = packet.get('windSpeed') or self.data_cache.get('windSpeed')
            wind_gust = packet.get('windGust') or self.data_cache.get('windGust')
            wind_dir = packet.get('windDir') or self.data_cache.get('windDir')
            dewpoint = packet.get('dewpoint') or self.data_cache.get('dewpoint')
            solarRad = packet.get('radiation') or self.data_cache.get('radiation')
            
            # If dewpoint is missing, calculate it manually so Cloud Base works!
            if dewpoint is None and temp is not None and humidity is not None:
                dewpoint = self._calculate_dewpoint(temp, humidity)
            
            # Wind Cardinal Direction
            if wind_dir is not None:
                wind_cardinal = self._calculate_wind_direction_cardinal(wind_dir)
                if wind_cardinal:
                    self.data_cache['windCardinal'] = wind_cardinal
                    if 'windCardinal' not in self.known_fields:
                        self.known_fields.add('windCardinal')
                        log.info("New calculated field: windCardinal")
            
            # Beaufort Scale
            if wind_speed is not None:
                beaufort_num, beaufort_desc = self._calculate_beaufort_scale(wind_speed)
                if beaufort_num is not None:
                    self.data_cache['beaufortScale'] = beaufort_num
                    self.data_cache['beaufortDesc'] = beaufort_desc
                    if 'beaufortScale' not in self.known_fields:
                        self.known_fields.add('beaufortScale')
                        self.known_fields.add('beaufortDesc')
                        log.info("New calculated fields: beaufortScale, beaufortDesc")

            # Beaufort Colors wind speed
            if wind_speed is not None:
                beaufort_num, bft_colors = self._calculate_beaufort_colors(wind_speed)
                if beaufort_num is not None:
                    self.data_cache['beaufortScale'] = beaufort_num
                    self.data_cache['beaufortColorSpeed'] = bft_colors
                    if 'beaufortScale' not in self.known_fields:
                        self.known_fields.add('beaufortScale')
                        self.known_fields.add('beaufortColorSpeed')
                        log.info("New calculated fields: beaufortColorSpeed")

            # Beaufort Colors gust speed
            if wind_gust is not None:
                beaufort_num, bft_colors = self._calculate_beaufort_colors(wind_gust)
                if beaufort_num is not None:
                    self.data_cache['beaufortScale'] = beaufort_num
                    self.data_cache['beaufortColorGust'] = bft_colors
                    if 'beaufortScale' not in self.known_fields:
                        self.known_fields.add('beaufortScale')
                        self.known_fields.add('beaufortColorGust')
                        log.info("New calculated fields: beaufortColorGust")
            
            # Solar radiation color
            radiation = packet.get('radiation') or self.data_cache.get('radiation')
            self.data_cache['radiationColor'] = self._calculate_radiation_color(radiation)
            if 'radiationColor' not in self.known_fields:
                self.known_fields.add('radiationColor')
                log.info("New calculated field: radiationColor")

            # UV color
            uv = packet.get('UV') or self.data_cache.get('UV')
            self.data_cache['uvColor'] = self._calculate_uv_color(uv)
            if 'uvColor' not in self.known_fields:
                self.known_fields.add('uvColor')
                log.info("New calculated field: uvColor")

            # Rain / hail color fields
            for src_field, color_field in (
                ('rain',     'rainColor'),
                ('rainRate', 'rainRateColor'),
                ('hail',     'hailColor'),
                ('hailRate', 'hailRateColor'),
            ):
                val = packet.get(src_field) or self.data_cache.get(src_field)
                color = self._calculate_rain_color(val)
                self.data_cache[color_field] = color
                if color_field not in self.known_fields:
                    self.known_fields.add(color_field)
                    log.info(f"New calculated field: {color_field}")

            # Wind speed color
            if wind_speed is not None:
                self.data_cache['windSpeedColor'] = self._calculate_wind_color(wind_speed)
                if 'windSpeedColor' not in self.known_fields:
                    self.known_fields.add('windSpeedColor')
                    log.info("New calculated field: windSpeedColor")

            # Wind gust color
            if wind_gust is not None:
                self.data_cache['windGustColor'] = self._calculate_wind_color(wind_gust)
                if 'windGustColor' not in self.known_fields:
                    self.known_fields.add('windGustColor')
                    log.info("New calculated field: windGustColor")

            # Temperature group colors
            for src_field, color_field in (
                ('outTemp',      'outTempColor'),
                ('inTemp',       'inTempColor'),
                ('dewpoint',     'dewpointColor'),
                ('apparentTemp', 'apparentTempColor'),
                ('feelsLike',    'feelsLikeColor'),
                ('heatIndex',    'heatIndexColor'),
                ('humidex',      'humidexColor'),
                ('windChill',    'windChillColor'),
                ('wetbulbTemp',  'wetbulbTempColor'),
            ):
                val = packet.get(src_field) or self.data_cache.get(src_field)
                self.data_cache[color_field] = self._calculate_temperature_color(val)
                if color_field not in self.known_fields:
                    self.known_fields.add(color_field)
                    log.info(f"New calculated field: {color_field}")

            # Barometer color
            barometer = packet.get('barometer') or self.data_cache.get('barometer')
            if barometer is not None:
                baro_color = self._calculate_barometer_color(barometer)
                self.data_cache['barometerColor'] = baro_color
                if 'barometerColor' not in self.known_fields:
                    self.known_fields.add('barometerColor')
                    log.info("New calculated field: barometerColor")

            # Apparent Temperature
            if temp is not None and humidity is not None and wind_speed is not None:
                apparent_temp = self._calculate_apparent_temperature(temp, humidity, wind_speed)
                if apparent_temp is not None:
                    self.data_cache['apparentTemp'] = apparent_temp
                    if 'apparentTemp' not in self.known_fields:
                        self.known_fields.add('apparentTemp')
                        log.info("New calculated field: apparentTemp")
            
            # Heat Index
            if temp is not None and humidity is not None:
                heat_index = self._calculate_heat_index(temp, humidity)
                if heat_index is not None:
                    self.data_cache['heatIndex'] = round(heat_index, 1)
                    if 'heatIndex' not in self.known_fields:
                        self.known_fields.add('heatIndex')
                        log.info("New calculated field: heatIndex")
            
            # Humidex
            if temp is not None and humidity is not None:
                humidex = self._calculate_humidex(temp, humidity)
                if humidex is not None:
                    self.data_cache['humidex'] = round(humidex, 1)
                    if 'humidex' not in self.known_fields:
                        self.known_fields.add('humidex')
                        log.info("New calculated field: humidex")
            
            # Wind Chill
            if temp is not None and wind_speed is not None:
                windchill = self._calculate_windchill(temp, wind_speed)
                if windchill is not None:
                    self.data_cache['windChill'] = round(windchill, 1)
                    if 'windChill' not in self.known_fields:
                        self.known_fields.add('windChill')
                        log.info("New calculated field: windChill")
            
            # VPD (Vapor Pressure Deficit) - always in kPa
            if temp is not None and humidity is not None:
                vpd = self._calculate_vpd(temp, humidity)
                if vpd is not None:
                    self.data_cache['vpd'] = round(vpd, 2)
                    if 'vpd' not in self.known_fields:
                        self.known_fields.add('vpd')
                        log.info("New calculated field: vpd (kPa)")
            
            # Cloud Base
            if temp is not None and dewpoint is not None:
                cloud_base = self._calculate_cloud_base(temp, dewpoint)
                if cloud_base is not None:
                    self.data_cache['cloudBase'] = round(cloud_base, 0)
                    if 'cloudBase' not in self.known_fields:
                        self.known_fields.add('cloudBase')
                        unit = 'ft' if self.unit_system == weewx.US else 'm'
                        log.info(f"New calculated field: cloudBase ({unit})")
            
            # Dewpoint
            if temp is not None and dewpoint is not None:
                dewpoint = self._calculate_dewpoint(temp, humidity)
                if dewpoint is not None:
                    self.data_cache['dewpoint'] = round(dewpoint, 1)
                    if 'dewpoint' not in self.known_fields:
                        self.known_fields.add('dewpoint')
                        log.info(f"New calculated field: dewpoint")

            # Wetbulb Temperature
            if temp is not None and humidity is not None:
                wbt = self._calculate_wet_bulb_temp(temp, humidity)
                if wbt is not None:
                    self.data_cache['wetbulbTemp'] = wbt
                    if 'wetbulbTemp' not in self.known_fields:
                        self.known_fields.add('wetbulbTemp')
                        log.info("New calculated field: wetbulbTemp")

            # Illuminance
            if solarRad is not None:
                lux = self._calculate_lux(solarRad)
                if lux is not None:
                    self.data_cache['illuminance'] = lux
                    if 'illuminance' not in self.known_fields:
                        self.known_fields.add('illuminance')
                        log.info("New calculated field: illuminance")

            # Air Density
            if temp is not None and humidity is not None and barometer is not None:
            # Use the local variables instead of 'record'
                air_density = self._calculate_air_density(temp, humidity, barometer)    
                if air_density is not None:
                    self.data_cache['airDensity'] = round(air_density, 5)
                    if 'airDensity' not in self.known_fields:
                        self.known_fields.add('airDensity')
                        log.info("New calculated field: airDensity")

        except Exception as e:
            log.error(f"Error calculating derived values: {e}", exc_info=True)

    # ---------------------------------------------------------------------
    # Packet handlers
    # ---------------------------------------------------------------------

    def new_loop_packet(self, event):
        self.received_packet = True
        packet, conv_stats = self._convert_packet_safely(event.packet)
        self._update_cumulative_stats(conv_stats)

        with self.cache_lock:
            for k, v in packet.items():
                if v is not None:
                    if k not in self.known_fields:
                        self.known_fields.add(k)
                        log.info(f"New field detected: {k}")
                        self._save_known_fields()
                    self.data_cache[k] = v

            # Add isDay observation based on sunrise/sunset (1 = day, 0 = night)
            self._add_day_observation(packet)
            
            # Add calculated weather values
            self._add_calculated_values(packet)
            
            if time.time() - self.last_write >= self.update_interval:
                self._update_json_file()
                self.last_write = time.time()

    def new_archive_record(self, event):
        record, _ = self._convert_packet_safely(event.record, archive=True)
        with self.cache_lock:
            for k, v in record.items():
                if v is not None and k not in self.SYSTEM_FIELDS:
                    ak = f"archive_{k}"
                    self.data_cache[ak] = v

    # ---------------------------------------------------------------------
    # Day/Night logic
    # ---------------------------------------------------------------------

    def _add_day_observation(self, packet):
        """Add isDay observation (1 = daytime, 0 = nighttime) based on sunrise/sunset times."""
        try:
            # Get current time from packet
            current_time_ts = packet.get('dateTime')
            if not current_time_ts:
                log.warning("No dateTime in packet for isDay calculation")
                return
            
            current_dt = datetime.datetime.fromtimestamp(current_time_ts)
            current_time = current_dt.time()  # Just the time part
            
            # Get sunrise and sunset times
            sunrise_ts = None
            sunset_ts = None
            
            # Check for sunrise/sunset in packet
            for field in ['sunrise', 'daySunrise', 'Sunrise']:
                if field in packet:
                    sunrise_ts = packet[field]
                    break
            
            for field in ['sunset', 'daySunset', 'Sunset']:
                if field in packet:
                    sunset_ts = packet[field]
                    break
            
            # If not found, try cache or calculate
            if sunrise_ts is None or sunset_ts is None:
                sunrise_ts = self.data_cache.get('archive_sunrise')
                sunset_ts = self.data_cache.get('archive_sunset')
            
            if (sunrise_ts is None or sunset_ts is None) and self.sun_calculator:
                current_date = current_dt.date()
                sunrise_ts, sunset_ts = self._get_sunrise_sunset_for_date(current_date)
            
            # If we have times, convert to time objects
            if sunrise_ts is not None and sunset_ts is not None:
                sunrise_dt = datetime.datetime.fromtimestamp(sunrise_ts)
                sunset_dt = datetime.datetime.fromtimestamp(sunset_ts)
                
                sunrise_time = sunrise_dt.time()
                sunset_time = sunset_dt.time()
                
                # Convert times to minutes since midnight for easier comparison
                current_minutes = current_time.hour * 60 + current_time.minute
                sunrise_minutes = sunrise_time.hour * 60 + sunrise_time.minute
                sunset_minutes = sunset_time.hour * 60 + sunset_time.minute
                
                log.debug(f"Time in minutes: current={current_minutes}, sunrise={sunrise_minutes}, sunset={sunset_minutes}")
                
                if sunrise_minutes < sunset_minutes:
                    # Normal day: sunrise before sunset
                    if sunrise_minutes <= current_minutes < sunset_minutes:
                        is_day_value = 1  # Daytime
                        log.debug(f"Normal daytime: {sunrise_time} <= {current_time} < {sunset_time}")
                    else:
                        is_day_value = 0  # Nighttime
                        log.debug(f"Normal nighttime: NOT ({sunrise_time} <= {current_time} < {sunset_time})")
                else:
                    # Sunset is before sunrise (crosses midnight)
                    # This means: sunset yesterday, sunrise today
                    if current_minutes >= sunrise_minutes or current_minutes < sunset_minutes:
                        is_day_value = 1  # Daytime (spans midnight)
                        log.debug(f"Cross-midnight daytime: {current_time} >= {sunrise_time} OR {current_time} < {sunset_time}")
                    else:
                        is_day_value = 0  # Nighttime (between sunset and sunrise)
                        log.debug(f"Cross-midnight nighttime: {sunset_time} <= {current_time} < {sunrise_time}")
                
                # Only log isDay changes at INFO level
                current_is_day = self.data_cache.get('isDay')
                if current_is_day != is_day_value:
                    log.info(f"isDay changed to {is_day_value} (current: {current_time}, sunrise: {sunrise_time}, sunset: {sunset_time})")
                else:
                    log.debug(f"isDay = {is_day_value} (current: {current_time}, sunrise: {sunrise_time}, sunset: {sunset_time})")
            else:
                # Fallback to time-based
                current_hour = current_dt.hour
                if 6 <= current_hour < 18:
                    is_day_value = 1
                else:
                    is_day_value = 0
                log.debug(f"Fallback isDay: {is_day_value} (hour: {current_hour})")
            
            self.data_cache['isDay'] = is_day_value
            
            if 'isDay' not in self.known_fields:
                self.known_fields.add('isDay')
                self._save_known_fields()
            
        except Exception as e:
            log.error(f"Error in isDay calculation: {e}", exc_info=True)
            # Default to nighttime
            self.data_cache['isDay'] = 0

    # ---------------------------------------------------------------------
    # Conversion logic
    # ---------------------------------------------------------------------

    def _convert_packet_safely(self, packet, archive=False):
        converted_count = skipped_count = 0
        try:
            current_us = packet.get('usUnits', weewx.US)
            if current_us == self.unit_system:
                return packet, (0, len(packet))

            convert_me, skip_me = {}, {}
            for k, v in packet.items():
                lname = k.lower()
                if any(x in lname for x in self.NON_PHYSICAL_FIELDS):
                    skip_me[k] = v
                    skipped_count += 1
                elif k in obs_group_dict or lname in obs_group_dict:
                    convert_me[k] = v
                else:
                    skip_me[k] = v
                    skipped_count += 1

            converted = {}
            if convert_me:
                converted = to_std_system(dict(convert_me, usUnits=current_us), self.unit_system)
                converted_count = len(convert_me)

            merged = {**converted, **skip_me}
            return merged, (converted_count, skipped_count)

        except Exception as e:
            log.error(f"Safe conversion error: {e}")
            return packet, (0, 0)

    # ---------------------------------------------------------------------
    # Cumulative stats handling
    # ---------------------------------------------------------------------

    def _update_cumulative_stats(self, conv_stats):
        """Update cumulative conversion statistics."""
        conv, skip = conv_stats
        self.last_conv_stats = conv_stats
        self.total_conv_stats[0] += conv
        self.total_conv_stats[1] += skip
        self._save_persistent_stats()

    # ---------------------------------------------------------------------
    # JSON writing
    # ---------------------------------------------------------------------

    def _update_json_file(self):
        try:
            # Create observations dictionary
            obs = {}
            for k, v in self.data_cache.items():
                if k not in self.SYSTEM_FIELDS and not k.startswith('_'):
                    obs[k] = v
            
            # Check if isDay is in observations (debug only)
            if 'isDay' in obs:
                log.debug(f"isDay = {obs['isDay']} written to JSON")

            conv_last, skip_last = self.last_conv_stats
            conv_total, skip_total = self.total_conv_stats

            data = {
                'updated': int(time.time()),
                'updated_iso': time.strftime('%Y-%m-%dT%H:%M:%SZ', time.gmtime()),
                'observations': obs,
                'metadata': self._build_metadata(conv_last, skip_last, conv_total, skip_total)
            }

            # Write to JSON file
            tmp = f"{self.json_file}.tmp"
            with open(tmp, 'w') as f:
                json.dump(data, f, indent=2 if self.pretty_print else None, sort_keys=self.pretty_print)
            os.replace(tmp, self.json_file)

            # Log every 10th write at INFO as a periodic heartbeat; every
            # other write goes to DEBUG. Previously this branched on
            # "conv_last + skip_last > 0", which is true on virtually every
            # single update (almost every write converts or skips at least
            # one field), so despite the comment above claiming a "every 10
            # updates" throttle, it logged at INFO on nearly every call --
            # once per update_interval (2s by default), regardless of
            # WeeWX's own [debug] verbosity setting. That's what generated
            # the log flood reported in beta feedback (Gary): "Updated
            # loop.json" appearing in syslog every 2-4 seconds at INFO. This
            # is routine per-loop-packet processing, not something that
            # belongs at INFO by WeeWX's own logging conventions -- it
            # should be DEBUG-only, with just an occasional INFO heartbeat
            # so "is this still running" is answerable without turning on
            # full debug logging.
            self.json_write_count += 1
            if self.json_write_count % 10 == 0:
                log.info(f"Updated {os.path.basename(self.json_file)} — {len(obs)} fields "
                         f"(write #{self.json_write_count})")
            else:
                log.debug(f"Updated {os.path.basename(self.json_file)} — {len(obs)} fields ({conv_last} converted, {skip_last} skipped)")

        except Exception as e:
            log.error(f"Error updating JSON file: {e}", exc_info=True)

    # ---------------------------------------------------------------------
    # Metadata and units
    # ---------------------------------------------------------------------

    def _build_metadata(self, conv_last, skip_last, conv_total, skip_total):
        metadata = {
            'service': 'WeeWX Live Data',
            'version': '3.2.6',
            'preferred_unit_system': self.unit_system_str,
            'field_count': len(self.known_fields),
            'restart_time': self.restart_time_iso,
            'uptime_seconds': int(time.time() - self.start_time),
            'units': self._get_unit_labels(),
            'calculated_fields': [
                'windCardinal', 'beaufortScale', 'beaufortDesc', 'beaufortColorSpeed', 'beaufortColorGust',
                'apparentTemp', 'heatIndex', 'humidex', 'windChill', 'vpd', 'cloudBase', 'dewpoint', 'wetbulbTemp',
                'barometerColor', 'outTempColor', 'inTempColor', 'dewpointColor',
                'apparentTempColor', 'feelsLikeColor', 'heatIndexColor',
                'humidexColor', 'windChillColor', 'wetbulbTempColor',
                'windSpeedColor', 'windGustColor',
                'rainColor', 'rainRateColor', 'hailColor', 'hailRateColor',
                'uvColor', 'radiationColor', 'illuminance', 'airDensity'
            ],
            'stats': {
                'converted_last': conv_last,
                'skipped_last': skip_last,
                'converted_total': conv_total,
                'skipped_total': skip_total,
                'last_update': time.strftime('%Y-%m-%dT%H:%M:%SZ', time.gmtime())
            }
        }

        return metadata

    def _get_unit_labels(self):
        if self.unit_system == weewx.US:
            return {'temperature': '°F', 'pressure': 'inHg', 'rain': 'in',
                    'wind': 'mph', 'radiation': 'W/m²', 'distance': 'mi', 'cloudBase': 'ft'}
        elif self.unit_system == weewx.METRIC:
            return {'temperature': '°C', 'pressure': 'mbar', 'rain': 'mm',
                    'wind': 'km/h', 'radiation': 'W/m²', 'distance': 'km', 'cloudBase': 'm'}
        else:
            return {'temperature': '°C', 'pressure': 'mbar', 'rain': 'mm',
                    'wind': 'm/s', 'radiation': 'W/m²', 'distance': 'km', 'cloudBase': 'm'}

    # ---------------------------------------------------------------------
    # Persistence helpers
    # ---------------------------------------------------------------------

    def _initialize_json_file(self):
        try:
            os.makedirs(os.path.dirname(self.json_file), exist_ok=True)
            with open(self.json_file, 'w') as f:
                json.dump({'observations': {}, 'metadata': {}}, f)
        except Exception as e:
            log.error(f"JSON init failed: {e}")

    def _load_known_fields(self):
        self.field_file = getattr(self, 'field_file', f"{self.json_file}.fields")
        if os.path.exists(self.field_file):
            try:
                with open(self.field_file) as f:
                    self.known_fields = set(json.load(f))
            except Exception:
                self.known_fields = set()

    def _save_known_fields(self):
        try:
            with open(self.field_file, 'w') as f:
                json.dump(sorted(list(self.known_fields)), f, indent=2)
        except Exception:
            pass

    # ---- persistent stats handling ----
    def _load_persistent_stats(self):
        if os.path.exists(self.stats_file):
            try:
                with open(self.stats_file) as f:
                    data = json.load(f)
                    return [int(data.get('converted_total', 0)), int(data.get('skipped_total', 0))]
            except Exception as e:
                log.error(f"Failed to load stats file: {e}")
        return [0, 0]

    def _save_persistent_stats(self):
        tmp = f"{self.stats_file}.tmp"
        try:
            data = {
                'converted_total': self.total_conv_stats[0],
                'skipped_total': self.total_conv_stats[1],
                'timestamp': time.strftime('%Y-%m-%dT%H:%M:%SZ', time.gmtime())
            }
            with open(tmp, 'w') as f:
                json.dump(data, f, indent=2)
            os.replace(tmp, self.stats_file)
        except Exception as e:
            log.error(f"Failed to save stats file: {e}")

    # ---------------------------------------------------------------------
    # Safety / shutdown
    # ---------------------------------------------------------------------

    def _check_first_packet(self):
        if not self.received_packet:
            log.warning("⚠ No loop packets received within 20 s — check your driver configuration")

    def shutDown(self):
        log.info("Shutting down LiveDataService — writing final JSON + stats")
        with self.cache_lock:
            self._update_json_file()
            self._save_known_fields()
            self._save_persistent_stats()

        log.info("LiveDataService stopped cleanly")

#!/usr/bin/env python3
"""
WeeWX Service: weatherapi.py
Polls various weather APIs and saves raw JSON responses to text files.

Installation:
1. Copy this file to your WeeWX user directory (e.g., /usr/share/weewx/user/ or ~/weewx-data/bin/user/)
2. Add configuration to weewx.conf (see example below)
3. Add 'user.weatherapi.WeatherAPIService' to the data_services list in [Engine][[Services]]

Example weewx.conf configuration:

[WeatherAPI]
    [[Forecast]]
        api_type = openmeteo
        enabled = True
        poll_interval = 300  # optional: overrides the built-in default for this API type
        data_path = /var/lib/weewx/jsondata/forecastcard.txt
        # Optional. Pins the forecast to one national weather provider's
        # model instead of Open-Meteo's default best_match blend. Leave
        # unset/blank for best_match. See OPENMETEO_MODEL_CHOICES in this
        # file, or https://open-meteo.com/en/docs ("Weather models"), for
        # valid values -- e.g.:
        # forecast_model = ukmo_seamless
    
    [[Airquality]]
        api_type = airquality
        enabled = True
        data_path = /var/www/html/divumwx/jsondata/particles.txt
    
    [[Flood]]
        api_type = flood
        enabled = True
        data_path = /var/www/html/divumwx/jsondata/flood.txt
    
    [[AuroraWatch]]
        api_type = aurorawatch
        enabled = False  # Temporarily disabled
        data_path = /var/www/html/divumwx/jsondata/aurora.txt
    
    [[HeatAlert]]
        api_type = heatalert
        enabled = True
        location_code = E12000008
        data_path = /var/www/html/divumwx/jsondata/heat.txt
    
    [[MetOfficeRSS]]
        api_type = metofficerss
        region_code = se
        data_path = /var/www/html/divumwx/jsondata/metofficerss.txt
    
    [[Xweather]]
        api_type = xweather
        client_id = your_client_id
        client_secret = your_client_secret
        data_path = /var/www/html/divumwx/jsondata/xweather.txt
    
    [[Boltek]]
        api_type = boltek
        enabled = True
        data_path = /var/www/html/divumwx/jsondata/boltek.txt
    
    # Custom API example
    [[MyCustomAPI]]
        api_type = custom
        enabled = True
        url = https://api.example.com/data?format=json
        poll_interval = 600
        data_path = /var/www/html/divumwx/jsondata/custom.txt
    
    # Custom API with headers
    [[HomeAssistant]]
        api_type = custom
        enabled = True
        url = http://homeassistant.local:8123/api/states
        header = content-type: application/json|Authorization: Bearer YOUR_TOKEN_HERE
        poll_interval = 60
        data_path = /var/www/html/divumwx/jsondata/homeassistant.txt

Supported API types:
- openweather: OpenWeatherMap (900s, requires app_id)
- openmeteo: Open-Meteo Forecast (300s; optional forecast_model to pin a single source, default best_match)
- airquality: Open-Meteo Air Quality (900s)
- metar: CheckWX METAR (3600s, requires X-API-Key and metar_name)
- earthquakes: EMSC Earthquakes (3600s)
- ki: NOAA K-Index Forecast (21600s)
- k2: NOAA Geospace DST (43200s)
- flood: UK Environment Agency Flood Warnings (900s)
- aurorawatch: AuroraWatch UK (180s)
- ovation: NOAA OVATION Aurora (300s)
- heatalert: UKHSA Heat Alert (1800s, requires location_code)
- coldalert: UKHSA Cold Alert (1800s, requires location_code)
- metofficerss: Met Office Warnings RSS (300s, requires region_code)
- xweather: Xweather Forecast (300s, requires client_id and client_secret)
- boltek: Boltek NGX Lightning Detector (60s)
- custom: User-defined API (requires url and poll_interval)

For any of the built-in API types above (all except 'custom'), you can override
the default polling interval by adding 'poll_interval = <seconds>' to that
source's section in weewx.conf. If omitted, the default shown above is used.
A minimum of 5 seconds is enforced; invalid or too-low values fall back to the
default and a warning/error is logged. 'custom' API types must specify
poll_interval (minimum 60 seconds) as before.

"""

import os
import time
import requests
import threading
from typing import Dict, Any, Optional

import weewx
from weewx.engine import StdService
from weeutil.weeutil import timestamp_to_string

# WeeWX 5.x logging
try:
    import weeutil.logger
    import logging
    log = logging.getLogger(__name__)
except ImportError:
    import syslog
    log = None


def logmsg(level, msg):
    """Unified logging function for WeeWX 4.x and 5.x compatibility"""
    if log:
        log.log(level, msg)
    else:
        syslog.syslog(level, f'weatherapi: {msg}')


def logdbg(msg):
    logmsg(logging.DEBUG if log else syslog.LOG_DEBUG, msg)


def loginf(msg):
    logmsg(logging.INFO if log else syslog.LOG_INFO, msg)


def logerr(msg):
    logmsg(logging.ERROR if log else syslog.LOG_ERR, msg)


# Curated subset of Open-Meteo's `models=` values (see
# https://open-meteo.com/en/docs -- "Weather models" dropdown) for the
# optional [[Forecast]] forecast_model setting: one entry per national
# weather provider's own combined/best "seamless" product, rather than
# every individual sub-model (icon_d2, gem_hrdps_west, etc.) -- picking a
# single *source* is the use case this setting exists for, not picking
# between a provider's own regional variants. install.py's interactive
# prompt (see that file) presents these same choices, so the two stay in
# sync by construction rather than by keeping two lists updated in
# parallel. '' (blank/unset) means Open-Meteo's own default: best_match,
# which blends whichever models are most suitable for the requested
# location -- this is deliberately not hardcoded as an actual 'best_match'
# string value needing a real &models= param, since omitting the
# parameter entirely (Open-Meteo's own default when it's absent) is
# functionally identical and one fewer thing to keep correct.
OPENMETEO_MODEL_CHOICES = {
    '': 'Best match (default -- Open-Meteo blends the best available models for the location)',
    'ecmwf_ifs025': 'ECMWF IFS (Europe)',
    'gfs_seamless': 'NOAA GFS (USA)',
    'icon_seamless': 'DWD ICON (Germany)',
    'ukmo_seamless': 'UK Met Office',
    'meteofrance_seamless': 'M\u00e9t\u00e9o-France',
    'jma_seamless': 'JMA (Japan)',
    'kma_seamless': 'KMA (South Korea)',
    'gem_seamless': 'GEM (Canada)',
    'bom_access_global': 'BOM ACCESS-G (Australia)',
    'cma_grapes_global': 'CMA GRAPES (China)',
    'knmi_seamless': 'KNMI (Netherlands)',
    'dmi_seamless': 'DMI (Denmark)',
    'metno_seamless': 'MET Norway Nordic Seamless (with ECMWF)',
    'meteoswiss_icon_seamless': 'MeteoSwiss ICON',
}


class APIConfig:
    """Configuration for different API types"""
    
    OPENWEATHER = {
        'name': 'OpenWeatherMap',
        'polling_interval': 900,  # 15 minutes
        'url_template': (
            'https://api.openweathermap.org/data/3.0/onecall?'
            'lat={latitude}&lon={longitude}&exclude=current,minutely,hourly,daily'
            '&appid={app_id}&units=metric'
        ),
        'requires_app_id': True,
        'uses_coordinates': True
    }
    
    OPENMETEO = {
        'name': 'Open-Meteo Forecast',
        'polling_interval': 300,  # 5 minutes
        'url_template': (
            'https://api.open-meteo.com/v1/forecast?'
            'latitude={latitude}&longitude={longitude}'
            '&daily=weather_code,temperature_2m_max,temperature_2m_min,wind_speed_10m_max,'
            'wind_direction_10m_dominant,rain_sum,showers_sum,snowfall_sum,precipitation_sum,'
            'precipitation_hours,precipitation_probability_max'
            '&hourly=temperature_2m,precipitation,rain,showers,snowfall,snow_depth,weather_code,'
            'pressure_msl,surface_pressure,cloud_cover,visibility,evapotranspiration,'
            'vapour_pressure_deficit,wind_speed_10m,wind_direction_10m,wind_gusts_10m,is_day'
            '&current=temperature_2m,is_day,wind_speed_10m,wind_direction_10m,wind_gusts_10m,'
            'precipitation,rain,showers,snowfall,weather_code,cloud_cover,pressure_msl,surface_pressure'
            # Without timezone Open-Meteo returns GMT times; every frontend
            # consumer (forecast.js, cardsBundleNew.js, charts-d3.html)
            # treats these strings as station-local wall-clock time.
            '&timezone=auto'
        ),
        'requires_app_id': False,
        'uses_coordinates': True
    }
    
    AIRQUALITY = {
        'name': 'Open-Meteo Air Quality',
        'polling_interval': 900,  # 15 minutes
        'url_template': (
            'https://air-quality-api.open-meteo.com/v1/air-quality?'
            'latitude={latitude}&longitude={longitude}'
            '&current=european_aqi,us_aqi,pm10,pm2_5,carbon_monoxide,nitrogen_dioxide,'
            'sulphur_dioxide,ozone,aerosol_optical_depth,dust,ammonia,'
            'alder_pollen,birch_pollen,grass_pollen,mugwort_pollen,olive_pollen,'
            'ragweed_pollen'
            '&timezone=auto'  # station-local times, consistent with OPENMETEO
        ),
        'requires_app_id': False,
        'uses_coordinates': True
    }
    
    METAR = {
        'name': 'CheckWX METAR',
        'polling_interval': 3600,  # 60 minutes
        'url_template': 'https://api.checkwx.com/metar/{metar_name}/decoded',
        'requires_app_id': True,
        'uses_coordinates': False,
        'uses_header_auth': True,
        'auth_header_name': 'X-API-Key'
    }
    
    EARTHQUAKES = {
        'name': 'EMSC Earthquakes',
        'polling_interval': 3600,  # 60 minutes
        'url_template': (
            'https://www.seismicportal.eu/fdsnws/event/1/query?'
            'limit=50&lat={latitude}&lon={longitude}&maxradius=180&minradius=10'
            '&format=json&minmag=2'
        ),
        'requires_app_id': False,
        'uses_coordinates': True
    }
    
    KI = {
        'name': 'NOAA K-Index Forecast',
        'polling_interval': 21600,  # 6 hours
        'url_template': 'https://services.swpc.noaa.gov/products/noaa-planetary-k-index-forecast.json',
        'requires_app_id': False,
        'uses_coordinates': False
    }
    
    K2 = {
        'name': 'NOAA Geospace DST',
        'polling_interval': 43200,  # 12 hours
        'url_template': 'https://services.swpc.noaa.gov/json/geospace/geospace_dst_7_day.json',
        'requires_app_id': False,
        'uses_coordinates': False
    }
    
    FLOOD = {
        'name': 'UK Environment Agency Flood',
        'polling_interval': 900,  # 15 minutes
        'url_template': (
            'https://environment.data.gov.uk/flood-monitoring/id/floods?'
            'lat={latitude}&long={longitude}&dist=10&min-severity=3'
        ),
        'requires_app_id': False,
        'uses_coordinates': True
    }
    
    AURORAWATCH = {
        'name': 'AuroraWatch UK',
        'polling_interval': 180,  # 3 minutes
        'url_template': 'http://aurorawatch-api.lancs.ac.uk/0.2.5/status/project/awn/sum-activity.xml',
        'requires_app_id': False,
        'uses_coordinates': False,
        'content_type': 'xml'
    }
    
    OVATION = {
        'name': 'NOAA OVATION Aurora',
        'polling_interval': 300,  # 5 minutes
        'url_template': 'https://services.swpc.noaa.gov/json/ovation_aurora_latest.json',
        'requires_app_id': False,
        'uses_coordinates': False
    }
    
    HEATALERT = {
        'name': 'UKHSA Heat Alert',
        'polling_interval': 1800,  # 30 minutes
        'url_template': 'https://ukhsa-dashboard.data.gov.uk/api/proxy/alerts/v1/heat/{location_code}',
        'requires_app_id': False,
        'uses_coordinates': False,
        'requires_location_code': True
    }
    
    COLDALERT = {
        'name': 'UKHSA Cold Alert',
        'polling_interval': 1800,  # 30 minutes
        'url_template': 'https://ukhsa-dashboard.data.gov.uk/api/proxy/alerts/v1/cold/{location_code}',
        'requires_app_id': False,
        'uses_coordinates': False,
        'requires_location_code': True
    }
    
    METOFFICERSS = {
        'name': 'Met Office Warnings RSS',
        'polling_interval': 300,  # 5 minutes
        'url_template': 'https://weather.metoffice.gov.uk/public/data/PWSCache/WarningsRSS/Region/{region_code}',
        'requires_app_id': False,
        'uses_coordinates': False,
        'requires_region_code': True,
        'content_type': 'xml'
    }
    
    XWEATHER = {
        'name': 'Xweather Forecast',
        'polling_interval': 300,  # 5 minutes
        'url_template': (
            'https://data.api.xweather.com/forecasts/{latitude},{longitude}?'
            'format=json&limit=32&lang=en&client_id={client_id}&client_secret={client_secret}'
        ),
        'requires_app_id': False,
        'uses_coordinates': True,
        'requires_client_credentials': True
    }

    BOLTEK = {
        'name': 'Boltek NGX Lightning Detector',
        'polling_interval': 5,  # 5 seconds - real-time lightning strike data
        'url_template': 'https://weathertest.mdmi.co.uk/divumwx/boltek/data/ngxdata.json',
        'requires_app_id': False,
        'uses_coordinates': False,
        'content_type': 'json'
    }

    CUSTOM = {
        'name': 'Custom API',
        'polling_interval': None,  # Will be set from config
        'url_template': None,  # Will be set from config
        'requires_app_id': False,
        'uses_coordinates': False,
        'is_custom': True
    }
    
    @classmethod
    def get_config(cls, api_type: str) -> Optional[Dict[str, Any]]:
        """Get configuration for a specific API type"""
        api_type_upper = api_type.upper()
        if hasattr(cls, api_type_upper):
            return getattr(cls, api_type_upper)
        return None


class WeatherAPIPoller:
    """Handles polling a single weather API source"""
    
    def __init__(self, name: str, config: Dict[str, Any], 
                 latitude: float, longitude: float):
        """
        Initialize the API poller
        
        Args:
            name: Name of the API source (e.g., 'Alerts', 'Forecast', 'Metar')
            config: Configuration dictionary for this API source
            latitude: Station latitude
            longitude: Station longitude
        """
        self.name = name
        self.latitude = latitude
        self.longitude = longitude
        
        # Check if this source is enabled
        self.enabled = to_bool(config.get('enabled', True))  # Default to True for backward compatibility
        if not self.enabled:
            loginf(f"{self.name}: Disabled in configuration (enabled = False)")
            # Set minimal config for disabled sources
            self.api_config = {'name': 'Disabled'}
            self.data_path = None
            self.thread = None
            self.stop_event = None
            return
        
        # Get API type
        self.api_type = config.get('api_type', '').lower()
        if not self.api_type:
            raise ValueError(f"{self.name}: 'api_type' is required in configuration")
        
        # Get API configuration
        self.api_config = APIConfig.get_config(self.api_type)
        if not self.api_config:
            raise ValueError(f"{self.name}: Unknown api_type '{self.api_type}'")
        
        # Handle custom API configuration
        if self.api_config.get('is_custom', False):
            # Custom APIs require url and poll_interval
            custom_url = config.get('url', '')
            custom_interval = config.get('poll_interval', None)
            custom_headers = config.get('header', '')
            
            if not custom_url:
                raise ValueError(f"{self.name}: Custom API requires 'url' parameter")
            if custom_interval is None:
                raise ValueError(f"{self.name}: Custom API requires 'poll_interval' parameter (in seconds)")
            
            try:
                custom_interval = int(custom_interval)
                if custom_interval < 60:
                    logerr(f"{self.name}: poll_interval should be at least 60 seconds, got {custom_interval}")
            except (ValueError, TypeError):
                raise ValueError(f"{self.name}: poll_interval must be an integer (seconds)")
            
            # Parse custom headers if provided
            parsed_headers = {}
            if custom_headers:
                try:
                    # Split by pipe, then by colon
                    header_pairs = custom_headers.split('|')
                    for pair in header_pairs:
                        pair = pair.strip()
                        if ':' in pair:
                            key, value = pair.split(':', 1)
                            parsed_headers[key.strip()] = value.strip()
                        else:
                            logerr(f"{self.name}: Invalid header format (missing colon): '{pair}'")
                    
                    if parsed_headers:
                        loginf(f"{self.name}: Custom headers configured: {list(parsed_headers.keys())}")
                except Exception as e:
                    logerr(f"{self.name}: Failed to parse custom headers: {e}")
            
            # Override the config for custom API
            self.api_config = {
                'name': f'Custom API ({self.name})',
                'polling_interval': custom_interval,
                'url_template': custom_url,
                'requires_app_id': False,
                'uses_coordinates': False,
                'is_custom': True,
                'custom_headers': parsed_headers
            }
            
            loginf(f"{self.name}: Configured as custom API")
            loginf(f"{self.name}: URL: {custom_url[:100]}...")  # Truncate long URLs
            loginf(f"{self.name}: Polling interval: {custom_interval}s")
        
        # Get app_id if required (or X-API-Key for METAR)
        self.app_id = config.get('app_id', '') or config.get('X-API-Key', '')
        if self.api_config['requires_app_id']:
            if not self.app_id or self.app_id in ['dummy1234567', '<mymetarapikey>']:
                logerr(f"{self.name}: API type '{self.api_type}' requires a valid app_id or X-API-Key")
        
        # Get METAR-specific configuration
        self.metar_name = config.get('metar_name', '')
        if self.api_type == 'metar':
            if not self.metar_name or self.metar_name == '<mymetarname>':
                logerr(f"{self.name}: METAR api_type requires a valid metar_name (e.g., EGTK)")
        
        # Get forecast_model for openmeteo -- pins the forecast to a single
        # national weather provider's model instead of Open-Meteo's default
        # best_match blend. Optional; blank/unset means best_match (see
        # OPENMETEO_MODEL_CHOICES above for why blank isn't itself sent as
        # a literal 'best_match' value). Validated against the same
        # curated list install.py's prompt offers, but NOT rejected if it
        # doesn't match -- Open-Meteo adds new model identifiers faster
        # than this list would get updated, so an unrecognized value is
        # passed through as-is with a warning rather than blocked.
        self.forecast_model = config.get('forecast_model', '').strip()
        if self.api_type == 'openmeteo' and self.forecast_model and self.forecast_model not in OPENMETEO_MODEL_CHOICES:
            logerr(
                f"{self.name}: forecast_model '{self.forecast_model}' isn't in this version's known "
                f"list, but will be sent to Open-Meteo as-is -- check https://open-meteo.com/en/docs "
                f"(\"Weather models\" section) for the current valid values if the forecast looks wrong."
            )
        
        # Get location_code for heat/cold alerts
        self.location_code = config.get('location_code', '')
        if self.api_config.get('requires_location_code', False):
            if not self.location_code or self.location_code == '<code>':
                logerr(f"{self.name}: API type '{self.api_type}' requires a valid location_code (e.g., E12000008)")
        
        # Get region_code for Met Office RSS
        self.region_code = config.get('region_code', '')
        if self.api_config.get('requires_region_code', False):
            if not self.region_code or self.region_code == '<regioncode>':
                logerr(f"{self.name}: API type '{self.api_type}' requires a valid region_code (e.g., se)")
        
        # Get client credentials for Xweather
        self.client_id = config.get('client_id', '')
        self.client_secret = config.get('client_secret', '')
        if self.api_config.get('requires_client_credentials', False):
            if not self.client_id or self.client_id == '<client_id_here>':
                logerr(f"{self.name}: API type '{self.api_type}' requires a valid client_id")
            if not self.client_secret or self.client_secret == '<client_secret_here>':
                logerr(f"{self.name}: API type '{self.api_type}' requires a valid client_secret")
        
        # Get data path
        self.data_path = config.get('data_path', '')
        if not self.data_path:
            self.data_path = f'./jsondata/{name.lower()}.txt'
            logerr(f"{self.name}: no 'data_path' set in weewx.conf for this source — "
                   f"falling back to a relative path ({self.data_path}), which will "
                   f"almost certainly fail to write once weewxd is actually running "
                   f"(its working directory generally isn't your web root). Set "
                   f"data_path to an absolute path, e.g. "
                   f"/var/www/html/divumwx/jsondata/{name.lower()}.txt")
        
        # Get polling interval. Custom APIs already resolved this above (required,
        # minimum 60s). For all other API types the default interval defined in
        # APIConfig is used unless overridden by a 'poll_interval' setting in
        # this source's weewx.conf section.
        if self.api_config.get('is_custom', False):
            self.polling_interval = self.api_config['polling_interval']
        else:
            default_interval = self.api_config['polling_interval']
            configured_interval = config.get('poll_interval', None)
            if configured_interval is None:
                self.polling_interval = default_interval
            else:
                try:
                    configured_interval = int(configured_interval)
                    if configured_interval < 5:
                        logerr(f"{self.name}: poll_interval too low ({configured_interval}s), "
                               f"using default {default_interval}s instead")
                        self.polling_interval = default_interval
                    else:
                        self.polling_interval = configured_interval
                        if self.polling_interval != default_interval:
                            loginf(f"{self.name}: Overriding default poll_interval "
                                   f"({default_interval}s) with configured value "
                                   f"({self.polling_interval}s)")
                except (ValueError, TypeError):
                    logerr(f"{self.name}: invalid poll_interval '{configured_interval}', "
                           f"using default {default_interval}s instead")
                    self.polling_interval = default_interval
        
        self.last_poll_time = 0
        self.thread = None
        self.stop_event = threading.Event()
        
        loginf(f"{self.name}: Configured for {self.api_config['name']} "
               f"(polling every {self.polling_interval}s)")
        
        # Create directory for data file if it doesn't exist
        data_dir = os.path.dirname(self.data_path)
        if data_dir and not os.path.exists(data_dir):
            try:
                os.makedirs(data_dir, exist_ok=True)
                loginf(f"{self.name}: Created directory: {data_dir}")
            except Exception as e:
                logerr(f"{self.name}: Failed to create directory {data_dir}: {e}")
    
    def build_url(self) -> str:
        """Build the API URL with current parameters"""
        url_template = self.api_config['url_template']
        
        # For custom APIs, return URL as-is (user provides complete URL)
        if self.api_config.get('is_custom', False):
            return url_template
        
        # Build parameters dict based on what the API uses
        params = {}
        
        # Add coordinates if the API uses them
        if self.api_config.get('uses_coordinates', True):
            params['latitude'] = self.latitude
            params['longitude'] = self.longitude
        
        # Add app_id if required (for URL-based auth)
        if self.api_config['requires_app_id'] and not self.api_config.get('uses_header_auth', False):
            params['app_id'] = self.app_id
        
        # Add METAR-specific parameters
        if self.api_type == 'metar':
            params['metar_name'] = self.metar_name
        
        # Add location_code for heat/cold alerts
        if self.api_config.get('requires_location_code', False):
            params['location_code'] = self.location_code
        
        # Add region_code for Met Office RSS
        if self.api_config.get('requires_region_code', False):
            params['region_code'] = self.region_code
        
        # Add client credentials for Xweather
        if self.api_config.get('requires_client_credentials', False):
            params['client_id'] = self.client_id
            params['client_secret'] = self.client_secret
        
        # No extra params needed for fixed-URL APIs (Boltek, KI, K2, etc.)
        if params:
            url = url_template.format(**params)
        else:
            url = url_template

        # forecast_model is appended directly rather than folded into the
        # {placeholder} substitution above, since it's optional and only
        # applies to one api_type -- adding it to `params` unconditionally
        # would require every other api_type's url_template to also define
        # an unused {forecast_model} placeholder just to avoid a
        # str.format() KeyError.
        if self.api_type == 'openmeteo' and self.forecast_model:
            url += '&models=' + self.forecast_model

        return url
    
    def fetch_and_save(self) -> bool:
        """
        Fetch data from API and save to file
        
        Returns:
            bool: True if successful, False otherwise
        """
        url = self.build_url()
        
        try:
            logdbg(f"{self.name}: Fetching data from {self.api_config['name']}")
            
            # Build headers
            headers = {
                'User-Agent': 'WeeWX-WeatherAPI/2.0 (https://github.com/weewx/weewx)'
            }
            
            # Add custom headers for custom APIs
            if self.api_config.get('is_custom', False):
                custom_headers = self.api_config.get('custom_headers', {})
                if custom_headers:
                    headers.update(custom_headers)
                    logdbg(f"{self.name}: Added custom headers: {list(custom_headers.keys())}")
            
            # Add authentication header if API uses header-based authentication
            if self.api_config.get('uses_header_auth', False):
                auth_header_name = self.api_config.get('auth_header_name', 'X-API-Key')
                headers[auth_header_name] = self.app_id
                logdbg(f"{self.name}: Using header authentication ({auth_header_name})")
            
            # Make the request
            response = requests.get(url, headers=headers, timeout=30)
            response.raise_for_status()
            
            # Save raw response to file
            data_dir = os.path.dirname(self.data_path)
            if data_dir:
                os.makedirs(data_dir, exist_ok=True)
            with open(self.data_path, 'w') as f:
                f.write(response.text)
            
            # Routine per-poll confirmation -- DEBUG, not INFO. This can
            # fire as often as every poll_interval (as low as 300s, and
            # there can be over a dozen [WeatherAPI] sources configured
            # at once), which adds up to a lot of "it worked" noise at
            # WeeWX's default INFO verbosity for something that isn't
            # actionable unless it stops happening (which already logs
            # its own errors elsewhere). Same treatment as the loop.json
            # write in LiveDataService._update_json_file() (beta
            # feedback, Gary).
            logdbg(f"{self.name}: Saved data to {self.data_path} "
                   f"({len(response.text)} bytes)")
            
            return True
            
        except requests.exceptions.RequestException as e:
            logerr(f"{self.name}: API request failed: {e}")
            if hasattr(e, 'response') and e.response is not None:
                logerr(f"{self.name}: Response status: {e.response.status_code}")
                logerr(f"{self.name}: Response text: {e.response.text[:200]}")
            return False
        except IOError as e:
            logerr(f"{self.name}: Failed to write to {self.data_path}: {e}")
            return False
        except Exception as e:
            logerr(f"{self.name}: Unexpected error: {e}")
            return False
    
    def should_poll(self) -> bool:
        """Check if enough time has elapsed since last poll"""
        current_time = time.time()
        return (current_time - self.last_poll_time) >= self.polling_interval
    
    def poll_if_needed(self):
        """Poll the API if the interval has elapsed"""
        if self.should_poll():
            if self.fetch_and_save():
                self.last_poll_time = time.time()
    
    def start_background_polling(self):
        """Start background thread for periodic polling"""
        if not self.enabled:
            logdbg(f"{self.name}: Skipping background polling (disabled)")
            return
        
        if self.thread is None or not self.thread.is_alive():
            self.stop_event.clear()
            self.thread = threading.Thread(target=self._polling_loop, daemon=True)
            self.thread.start()
            loginf(f"{self.name}: Started background polling thread")
    
    def stop_background_polling(self):
        """Stop the background polling thread"""
        if not self.enabled:
            return
        
        if self.thread and self.thread.is_alive():
            self.stop_event.set()
            self.thread.join(timeout=5)
            loginf(f"{self.name}: Stopped background polling thread")
    
    def _polling_loop(self):
        """Background thread polling loop"""
        # Do initial fetch immediately
        self.fetch_and_save()
        self.last_poll_time = time.time()
        
        while not self.stop_event.is_set():
            # Sleep in small intervals to allow responsive shutdown
            for _ in range(self.polling_interval):
                if self.stop_event.is_set():
                    break
                time.sleep(1)
            
            if not self.stop_event.is_set():
                self.fetch_and_save()
                self.last_poll_time = time.time()


class WeatherAPIService(StdService):
    """
    WeeWX service to poll weather APIs and save data to files
    """
    
    def __init__(self, engine, config_dict):
        """Initialize the service"""
        super(WeatherAPIService, self).__init__(engine, config_dict)
        
        loginf("Initializing WeatherAPI Service v2.0")
        
        # Get station location from WeeWX configuration
        try:
            self.latitude = float(config_dict['Station']['latitude'])
            self.longitude = float(config_dict['Station']['longitude'])
            loginf(f"Station location: {self.latitude}, {self.longitude}")
        except (KeyError, ValueError, TypeError) as e:
            logerr(f"Failed to get station location from config: {e}")
            self.latitude = 0.0
            self.longitude = 0.0
        
        # Get WeatherAPI configuration
        api_config = config_dict.get('WeatherAPI', {})
        
        if not api_config:
            logerr("No [WeatherAPI] section found in weewx.conf")
            self.pollers = []
            return
        
        # Create pollers for each configured API source
        self.pollers = []
        for source_name, source_config in api_config.items():
            if isinstance(source_config, dict):
                try:
                    poller = WeatherAPIPoller(
                        source_name, 
                        source_config,
                        self.latitude,
                        self.longitude
                    )
                    # Only add enabled pollers to the list
                    if poller.enabled:
                        self.pollers.append(poller)
                        loginf(f"Configured API source: {source_name} "
                               f"({poller.api_config['name']})")
                except Exception as e:
                    logerr(f"Failed to configure API source {source_name}: {e}")
        
        if not self.pollers:
            loginf("No API sources configured")
            return
        
        # Start background polling for all sources
        for poller in self.pollers:
            poller.start_background_polling()
        
        loginf(f"WeatherAPI Service initialized with {len(self.pollers)} source(s)")
    
    def shutDown(self):
        """Clean shutdown of the service"""
        loginf("Shutting down WeatherAPI Service")
        for poller in self.pollers:
            poller.stop_background_polling()


# For testing
if __name__ == '__main__':
    print("WeatherAPI Service for WeeWX 5.2")
    print("Version 2.0 - Multi-API Support")
    print("\nThis module should be loaded as a WeeWX service")
    print("\nSupported API types:")
    print("  - openweather: OpenWeatherMap (900s, requires app_id)")
    print("  - openmeteo: Open-Meteo Forecast (300s)")
    print("  - airquality: Open-Meteo Air Quality (900s)")
    print("  - metar: CheckWX METAR (3600s, requires X-API-Key + metar_name)")
    print("  - earthquakes: EMSC Earthquakes (3600s)")
    print("  - ki: NOAA K-Index Forecast (21600s)")
    print("  - k2: NOAA Geospace DST (43200s)")
    print("  - flood: UK Flood Warnings (900s)")
    print("  - aurorawatch: AuroraWatch UK (180s)")
    print("  - ovation: NOAA OVATION Aurora (300s)")
    print("  - heatalert: UKHSA Heat Alert (1800s, requires location_code)")
    print("  - coldalert: UKHSA Cold Alert (1800s, requires location_code)")
    print("  - metofficerss: Met Office Warnings RSS (300s, requires region_code)")
    print("  - xweather: Xweather Forecast (300s, requires client_id + client_secret)")
    print("  - boltek: Boltek NGX Lightning Detector (60s)")
    print("  - custom: User-defined API (requires url + poll_interval)")
    print("\nFor any built-in API type, add 'poll_interval = <seconds>' to its")
    print("section in weewx.conf to override the default interval shown above.")
    print("\nSee README.md for full documentation")
    print("\nExample configuration in weewx.conf:")
    print("""
[WeatherAPI]
    [[Forecast]]
        api_type = openmeteo
        poll_interval = 300  # optional override, defaults to 300s if omitted
        data_path = /var/lib/weewx/jsondata/forecast.txt
    
    [[Flood]]
        api_type = flood
        data_path = /var/www/html/divumwx/jsondata/flood.txt
    
    [[HeatAlert]]
        api_type = heatalert
        location_code = E12000008
        data_path = /var/www/html/divumwx/jsondata/heat.txt
    
    [[MetOfficeRSS]]
        api_type = metofficerss
        region_code = se
        data_path = /var/www/html/divumwx/jsondata/metofficerss.txt
    
    [[Xweather]]
        api_type = xweather
        client_id = your_client_id
        client_secret = your_client_secret
        data_path = /var/www/html/divumwx/jsondata/xweather.txt
    
    [[Boltek]]
        api_type = boltek
        enabled = True
        data_path = /var/www/html/divumwx/jsondata/boltek.txt
    
[Engine]
    [[Services]]
        data_services = user.weatherapi.WeatherAPIService
""")

#!/usr/bin/env python3
#
# Generic Data Inject Service for WeeWX
# Reads data from multiple JSON files and injects into WeeWX loop packets and archive records
#
# Copyright (C) 2025 Ian Millard <weatherboyian@gmail.com>
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <https://www.gnu.org/licenses/>.

import json
import logging
import os
import time
import threading

import weewx
from weewx.engine import StdService
from weeutil.weeutil import to_int, to_bool

log = logging.getLogger(__name__)

# Version history:
# 1.0.0 - Initial release with hardcoded air quality and weather sources
# 1.1.0 - Added SunSynk solar data support
# 1.2.0 - Made generic with configurable data sources and field mappings
# 1.3.0 - Fixed archive-only injection and nested configuration support
# 1.4.0 - Added comprehensive versioning and improved error handling
# 1.5.0 - Modified to inject into loop packets AND archive records

SERVICE_VERSION = "1.5.0"
SERVICE_NAME = "DataInjectService"

class DataInjectService(StdService):
    """
    Generic service to read data from JSON files and inject into WeeWX.
    
    Features:
    - Configurable data sources via weewx.conf
    - Flexible field mappings
    - JSON path navigation for nested data
    - Loop-time injection (for real-time data)
    - Archive-time injection (for historical data)
    - Disable individual data sources
    - Configurable update interval for loop data
    """
    
    def __init__(self, engine, config_dict):
        super(DataInjectService, self).__init__(engine, config_dict)
        
        # Log service version at startup
        log.info("%s version %s initializing", SERVICE_NAME, SERVICE_VERSION)
        
        # Get service configuration
        service_config = config_dict.get(SERVICE_NAME, {})
        
        # Configuration for loop packet injection
        self.loop_update_interval = to_int(service_config.get('loop_update_interval', 5))  # seconds
        self.loop_injection_enabled = to_bool(service_config.get('loop_injection_enabled', True))
        self.archive_injection_enabled = to_bool(service_config.get('archive_injection_enabled', True))
        
        # Cache for loop data to avoid reading files too frequently
        self.data_cache = {}
        self.cache_lock = threading.Lock()
        self.last_update_time = {}
        
        # Parse data sources from configuration
        self.data_sources = self.parse_data_sources(service_config)
        
        # Bind to both loop and archive events based on configuration
        if self.loop_injection_enabled:
            self.bind(weewx.NEW_LOOP_PACKET, self.handle_new_loop_packet)
            log.info("Loop packet injection enabled (interval: %d seconds)", self.loop_update_interval)
        
        if self.archive_injection_enabled:
            self.bind(weewx.NEW_ARCHIVE_RECORD, self.handle_new_archive_record)
            log.info("Archive record injection enabled")
        
        log.info("%s initialized successfully", SERVICE_NAME)
        log.info("Service version: %s", SERVICE_VERSION)
        log.info("Configured data sources: %s", list(self.data_sources.keys()))
        
        # Log each data source configuration
        for source_name, source_config in self.data_sources.items():
            status = "DISABLED" if source_config['disabled'] else f"{source_config['file_path']} ({len(source_config['field_mappings'])} fields)"
            log.info("  %s: %s", source_name, status)
        
        # Start a thread to periodically update loop data (if enabled)
        if self.loop_injection_enabled:
            self._start_loop_update_thread()
        
        log.info("%s ready - data injection configured for loop and archive", SERVICE_NAME)
    
    def _start_loop_update_thread(self):
        """Start a background thread to periodically update loop data."""
        def update_loop_data():
            while True:
                try:
                    # Update all enabled data sources
                    with self.cache_lock:
                        for source_name, source_config in self.data_sources.items():
                            if not source_config['disabled']:
                                current_time = time.time()
                                last_update = self.last_update_time.get(source_name, 0)
                                
                                # Only update if enough time has passed
                                if current_time - last_update >= self.loop_update_interval:
                                    data = self.read_data_file(source_config['file_path'], source_name)
                                    if data:
                                        self.data_cache[source_name] = {
                                            'data': data,
                                            'timestamp': current_time
                                        }
                                        self.last_update_time[source_name] = current_time
                                        log.debug("Updated loop data cache for %s", source_name)
                except Exception as e:
                    log.error("Error in loop update thread: %s", e)
                
                # Sleep for the update interval
                time.sleep(self.loop_update_interval)
        
        # Start the thread
        thread = threading.Thread(target=update_loop_data, daemon=True)
        thread.start()
        log.debug("Started loop data update thread")
    
    def parse_data_sources(self, service_config):
        """Parse data sources from weewx.conf configuration"""
        data_sources = {}
        sources_found = 0
        
        # Look for nested source sections
        for key, value in service_config.items():
            if key.startswith('source_') and isinstance(value, dict):
                source_name = key[7:]  # Remove 'source_' prefix
                data_sources[source_name] = self.parse_source_config(value, source_name)
                sources_found += 1
        
        log.debug("Parsed %d data source(s) from configuration", sources_found)
        return data_sources
    
    def parse_source_config(self, source_config, source_name):
        """Parse individual data source configuration from nested section"""
        config = {
            'file_path': '/path/to/default.json',
            'disabled': False,
            'field_mappings': {},
            'json_path': '',  # Optional JSON path to nested data
            'cache_ttl': to_int(source_config.get('cache_ttl', self.loop_update_interval))  # Cache TTL in seconds
        }
        
        # Get file path
        file_path = source_config.get('path', '/path/to/default.json')
        config['file_path'] = file_path
        config['disabled'] = file_path.lower() == 'disable'
        
        if config['disabled']:
            log.debug("Data source '%s' is disabled", source_name)
            return config
        
        # Get JSON path if specified
        config['json_path'] = source_config.get('json_path', '')
        if config['json_path']:
            log.debug("Data source '%s' using JSON path: %s", source_name, config['json_path'])
        
        # Parse field mappings
        mapping_config = source_config.get('mapping', {})
        if isinstance(mapping_config, dict):
            # Direct dictionary mapping
            config['field_mappings'] = mapping_config
            log.debug("Data source '%s' using dictionary mapping with %d fields", 
                     source_name, len(mapping_config))
        elif isinstance(mapping_config, str):
            # String format: "json_key1:db_col1, json_key2:db_col2"
            config['field_mappings'] = self.parse_field_mappings_string(mapping_config)
            log.debug("Data source '%s' using string mapping with %d fields", 
                     source_name, len(config['field_mappings']))
        elif isinstance(mapping_config, list):
            # List format for complex mappings
            config['field_mappings'] = self.parse_field_mappings_list(mapping_config)
            log.debug("Data source '%s' using list mapping with %d fields", 
                     source_name, len(config['field_mappings']))
        else:
            log.warning("Data source '%s' has invalid mapping configuration type: %s", 
                       source_name, type(mapping_config))
        
        return config
    
    def parse_field_mappings_string(self, field_mappings_str):
        """Parse field mappings from string format"""
        field_mappings = {}
        if field_mappings_str and isinstance(field_mappings_str, str):
            # Support multiple formats: comma, pipe, or space separated
            separators = [',', '|', ' ']
            separator = ','
            for sep in separators:
                if sep in field_mappings_str and ':' in field_mappings_str:
                    separator = sep
                    break
            
            mappings = field_mappings_str.split(separator)
            for mapping in mappings:
                mapping = mapping.strip()
                if ':' in mapping:
                    json_key, db_col = mapping.split(':', 1)
                    field_mappings[json_key.strip()] = db_col.strip()
                elif mapping:  # Non-empty but no colon
                    log.warning("Invalid mapping format (missing colon): '%s'", mapping)
        else:
            log.warning("Invalid field mappings string: %s", field_mappings_str)
        
        return field_mappings
    
    def parse_field_mappings_list(self, field_mappings_list):
        """Parse field mappings from list format"""
        field_mappings = {}
        if not isinstance(field_mappings_list, list):
            log.warning("Expected list for field mappings, got %s", type(field_mappings_list))
            return field_mappings
            
        for item in field_mappings_list:
            if isinstance(item, dict):
                # List of dictionaries: [{"json_key": "db_col"}, ...]
                field_mappings.update(item)
            elif isinstance(item, str) and ':' in item:
                # List of strings: ["json_key:db_col", ...]
                json_key, db_col = item.split(':', 1)
                field_mappings[json_key.strip()] = db_col.strip()
            elif isinstance(item, str) and item:
                log.warning("Invalid mapping format in list (missing colon): '%s'", item)
        
        return field_mappings
    
    def handle_new_loop_packet(self, event):
        """Handle new loop packet event - inject data into loop packets"""
        try:
            log.debug("Loop packet event received - %s version %s injecting external data", 
                     SERVICE_NAME, SERVICE_VERSION)
            
            with self.cache_lock:
                # Get current cached data for all sources
                for source_name, source_config in self.data_sources.items():
                    if not source_config['disabled']:
                        cache_entry = self.data_cache.get(source_name)
                        if cache_entry:
                            # Check if cache is still valid
                            current_time = time.time()
                            cache_age = current_time - cache_entry['timestamp']
                            if cache_age <= source_config.get('cache_ttl', self.loop_update_interval):
                                self.inject_data_into_packet(event.packet, cache_entry['data'], 
                                                            source_config, source_name, "loop")
                            else:
                                log.debug("Cache expired for %s (age: %.1fs)", 
                                         source_name, cache_age)
        
        except Exception as e:
            log.error("Failed to inject external data into loop packet: %s", e)
    
    def handle_new_archive_record(self, event):
        """Handle new archive record event - inject data into archive records"""
        try:
            log.debug("Archive record event received - %s version %s injecting external data", 
                     SERVICE_NAME, SERVICE_VERSION)
            
            # For archive, read fresh data from files (not from cache)
            for source_name, source_config in self.data_sources.items():
                if not source_config['disabled']:
                    data = self.read_data_file(source_config['file_path'], source_name)
                    if data:
                        self.inject_data_into_record(event.record, data, source_config, 
                                                    source_name, "archive")
            
            # Routine per-archive-record confirmation (fires every archive
            # interval, 5 min by default) -- DEBUG, not INFO. Same
            # reasoning as the WeatherAPIPoller "Saved data to" change
            # above (beta feedback, Gary).
            log.debug("Archive data injection completed")
            
        except Exception as e:
            log.error("Failed to inject external data into archive record: %s", e)
    
    def inject_data_into_packet(self, packet, data, source_config, source_name, context="loop"):
        """Inject data into a loop packet"""
        return self._inject_data(packet, data, source_config, source_name, context)
    
    def inject_data_into_record(self, record, data, source_config, source_name, context="archive"):
        """Inject data into an archive record"""
        return self._inject_data(record, data, source_config, source_name, context)
    
    def _inject_data(self, target, data, source_config, source_name, context):
        """Generic method to inject data into either packet or record"""
        added_count = 0
        
        # Navigate to the appropriate level in JSON if json_path is specified
        target_data = data
        if source_config['json_path']:
            try:
                path_parts = source_config['json_path'].split('.')
                for part in path_parts:
                    if part:  # Skip empty parts
                        target_data = target_data[part]
                log.debug("Navigated to JSON path '%s' in %s data for %s", 
                         source_config['json_path'], source_name, context)
            except (KeyError, TypeError) as e:
                log.warning("JSON path '%s' not found in %s data for %s: %s", 
                           source_config['json_path'], source_name, context, e)
                return 0
        
        # Process each field mapping
        injected_fields = []
        for json_key, db_column in source_config['field_mappings'].items():
            if json_key in target_data:
                try:
                    value = target_data[json_key]
                    if value is not None:
                        target[db_column] = float(value)
                        added_count += 1
                        injected_fields.append(f"{db_column}={value}")
                    else:
                        log.debug("Skipping null value for %s.%s in %s", 
                                 source_name, json_key, context)
                except (TypeError, ValueError) as e:
                    log.warning("Could not convert value for %s.%s in %s: %s (value: %s)", 
                               source_name, json_key, context, e, target_data[json_key])
            else:
                log.debug("JSON key '%s' not found in %s data for %s", 
                         json_key, source_name, context)
        
        if added_count > 0:
            log.debug("Injected %d fields from %s into %s: %s", 
                     added_count, source_name, context, ", ".join(injected_fields))
        
        return added_count
    
    def read_data_file(self, file_path, source_name):
        """Read data from JSON file with improved error handling"""
        try:
            if not os.path.exists(file_path):
                log.error("Data file not found for %s: %s", source_name, file_path)
                return None
            
            file_stats = os.stat(file_path)
            file_size = file_stats.st_size
            file_mtime = file_stats.st_mtime
            file_age = time.time() - file_mtime
            
            # Warn only if the file is significantly older than expected
            # for THIS source. Different sources update on very different
            # schedules by design — Boltek lightning every 5s, airquality
            # every 15 minutes, K-index every 6 hours — so a flat 300s
            # threshold was firing false "stale" warnings for nearly every
            # source slower than 5 minutes, airquality included. Falls
            # back to 300s if the source isn't a recognized built-in API
            # type (e.g. a user-defined custom source, whose actual
            # configured interval isn't visible from here) or has no
            # fixed interval of its own (APIConfig.CUSTOM's is None).
            api_config = APIConfig.get_config(source_name)
            expected_interval = (api_config or {}).get('polling_interval') or 300
            stale_threshold = max(300, expected_interval * 2)
            if file_age > stale_threshold:
                log.warning("Data file for %s is old (%.1f minutes, expected refresh every ~%.1f minutes): %s", 
                           source_name, file_age/60, expected_interval/60, file_path)
            
            with open(file_path, 'r') as f:
                data = json.load(f)
            
            log.debug("Successfully read data from %s: %s (%d bytes, modified %.1fs ago)", 
                     source_name, file_path, file_size, file_age)
            return data
            
        except json.JSONDecodeError as e:
            log.error("JSON decode error for %s file %s: %s", source_name, file_path, e)
        except PermissionError as e:
            log.error("Permission error reading %s file %s: %s", source_name, file_path, e)
        except Exception as e:
            log.error("Unexpected error reading %s file %s: %s", source_name, file_path, e)
        
        return None


# Version information for external access
def get_version():
    """Return the service version"""
    return SERVICE_VERSION

def get_service_info():
    """Return service information dictionary"""
    return {
        'name': SERVICE_NAME,
        'version': SERVICE_VERSION,
        'description': 'Generic data injection service for WeeWX (loop and archive)'
    }

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

# The docstring above accidentally trapped its own import statements
# inside the triple-quoted string (see lines 2834-2839) — they never
# actually executed. weewx.xtypes and ValueTuple are both genuinely
# needed below (class AirDensity's base class, and ValueTuple used
# throughout get_scalar()), so import them for real here.
import weewx.xtypes
from weewx.units import ValueTuple

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
            # Routine per-archive-record confirmation (every archive
            # interval, 5 min by default) on the normal, steady-state path
            # -- DEBUG, not INFO. Unlike the "Estimated sunshine duration"
            # branch above (which only fires once, on the first archive
            # record after a restart, and stays at INFO as a startup
            # confirmation), this branch runs on every single archive
            # record for the rest of the service's life (beta feedback,
            # Gary).
            logdbg("Sunshine duration from loop packets = %f min" % (event.record['sunshine_time']))

        self.sunshineSeconds = 0
        self.cum_time = 0

    def sunshineThreshold(self, mydatetime):
        utcdate = datetime.datetime.utcfromtimestamp(mydatetime)
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

#
#    Copyright (c) 2025 Sean Balfour <seanbalfourdresden@googlemail.com>
#
"""This example shows how to extend the XTypes system with a new type, Vapour Pressure Deficit (vpd) in kPa 

REQUIRES WeeWX V5 OR LATER!

To use:
    1. Stop weewx 
    2. Put the vpd.py file in your user subdirectory.
    3. In weewx.conf, subsection [Engine][[Services]], 
    add vpdService to the list
    "xtype_services". For example, this means changing this

        [Engine]
            [[Services]]
                xtype_services = weewx.wxxtypes.StdWXXTypes, weewx.wxxtypes.StdPressureCooker, weewx.wxxtypes.StdRainRater

    to this:
        [Engine]
            [[Services]]
                xtype_services = weewx.wxxtypes.StdWXXTypes, weewx.wxxtypes.StdPressureCooker, weewx.wxxtypes.StdRainRater, user.vpd.vpdService


    4. Add the following stanza to your weewx.conf:

#############################################

    [vpd]
        algorithm = tetens  # in kPa

#############################################

    5. Restart weewx
"""

""" Vapour Pressure Deficit """

# Tell the unit system what group our new observation type, 'vpd', belongs to:
weewx.units.obs_group_dict['vpd'] = "group_pressure"

class vpd(weewx.xtypes.XType):

    def __init__(self, algorithm='simple'):
        # Save the algorithm to be used.
        self.algorithm = algorithm.lower()

    def get_scalar(self, obs_type, record, db_manager):
        # We only know how to calculate 'vpd. For everything else, raise an exception UnknownType
        if obs_type != 'vpd':
            raise weewx.UnknownType(obs_type)

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
            # "simple" algorithm.

            T = outTemp_C
            H = outHumidity

            # vapour pressure of leaf
            a = (17.27 * T) / (T + 237.3)
            vpl = 0.61078 * math.exp(a)

            # vapour pressure of air
            b = (17.27 * T) / (T + 237.3) 
            vpa = 0.61078 * math.exp(b) * (H / 100.0)

            vpd_inHg = vpl - vpa

            # Form a ValueTuple
            vpd = ValueTuple(vpd_inHg, 'inHg', 'group_pressure')

        elif self.algorithm == 'tetens':
            # Use teten's algorithm.

            T = outTemp_C
            H = outHumidity

            # Use the formula. Results will be in kPa:

            # vapour pressure of leaf
            a = (17.27 * T) / (T + 237.3)
            vpl = 0.61078 * math.exp(a)

            # vapour pressure of air
            b = (17.27 * T) / (T + 237.3) 
            vpa = 0.61078 * math.exp(b) * (H / 100.0)

            vpd_kPa = vpl - vpa

            # Form a ValueTuple
            vpd = ValueTuple(vpd_kPa, 'kPa', 'group_pressure')
        else:
            # Don't recognize the exception. Fail hard:
            raise ValueError(self.algorithm)

        return vpd

class vpdService(StdService):

    def __init__(self, engine, config_dict):
        super(vpdService, self).__init__(engine, config_dict)

        # Get the desired algorithm. Default to "simple".
        try:
            algorithm = config_dict['vpd']['algorithm']
        except KeyError:
            algorithm = 'simple'

        # Instantiate an instance of Vapor Pressure Deficit:
        self.vpdx = vpd(algorithm)
        # Register it:
        weewx.xtypes.xtypes.append(self.vpdx)

    def shutDown(self):
        # Remove the registered instance:
        weewx.xtypes.xtypes.remove(self.vpdx)

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

        cp colorize_3_1.py ~/weewx-data/bin/user

    For package installers:

        sudo cp colorize_3_1.py /usr/share/weewx/user

2) Modify the option search_list_extensions in the skin.conf configuration file, adding
the name of this extension.  When you're done, it will look something like this:

    [CheetahGenerator]
        search_list_extensions = user.colorize_3_1.Colorize

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
        
        # Handle None or invalid input
        if value_vh is None:
            return "#cccccc"  # Default gray for None input
            
        # Get the ValueTuple from the incoming ValueHelper
        try:
            value_vt = value_vh.value_t
        except AttributeError:
            # If value_vh doesn't have value_t attribute
            return "#cccccc"
        
        # Handle None value_vt
        if value_vt is None:
            return "#cccccc"
        
        # Check if this is an UnknownObsType (doesn't have group attribute)
        # The safest approach is to check for the presence of group attribute
        if not hasattr(value_vt, 'group'):
            # This is likely an UnknownObsType or similar
            return "#cccccc"
            
        # Now it's safe to access group
        unit_group = value_vt.group

        # Make sure unit_group is in the color table, and that the table
        # specifies a unit system.
        if unit_group not in self.color_tables \
                or 'unit_system' not in self.color_tables[unit_group]:    # 5
            return "#cccccc"

        # Convert the value to the same unit used by the color table:
        unit_system = self.color_tables[unit_group]['unit_system']        # 6
        try:
            converted_vt = weewx.units.convertStdName(value_vt, unit_system)  # 7
        except (AttributeError, TypeError, ValueError, KeyError):
            # If conversion fails, return default color
            return self.color_tables[unit_group].get('default', "#cccccc")

        # Check for a value of None
        if converted_vt.value is None:                                    # 8
            return self.color_tables[unit_group].get('none') \
                   or self.color_tables[unit_group].get('None', "#cccccc")

        # Search for the value in the color table:
        try:
            for upper_bound in self.color_tables[unit_group]['upper_bounds']: # 9
                try:
                    if converted_vt.value <= float(upper_bound):                  # 10
                        return self.color_tables[unit_group]['upper_bounds'][upper_bound]
                except (ValueError, TypeError):
                    # Skip invalid bounds
                    continue
        except KeyError:
            # No upper_bounds defined
            pass

        return self.color_tables[unit_group].get('default', "#cccccc")   # 11

#
#    Copyright (c) 2026 Tom Keffer <tkeffer@gmail.com>
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
        def time_since(expression, data_binding=None):
            """Time since a sql expression evaluted true"""
            db_manager = db_lookup(data_binding=data_binding)
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

        def time_at(expression, data_binding=None):
            """When an sql expression evaluated true"""
            db_manager = db_lookup(data_binding=data_binding)
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

# divumwx_extras.py
# Captures selected loop packet fields and stores them in divumwx_extras.sdb
# Data is written once per archive interval, synchronised with weewx.sdb

import syslog

import weewx
import weewx.manager
from weewx.engine import StdService

# Must match divumwx_extras.sdb exactly
schema = [('dateTime',          'INTEGER NOT NULL UNIQUE PRIMARY KEY'),
        ('usUnits',             'INTEGER NOT NULL'),
        ('interval',            'INTEGER NOT NULL'),
        ('aerosol_optical_depth',           'REAL'),
        ('AirDensity',                      'REAL'),
        ('cloudcover',                      'REAL'),
        ('dust',                            'REAL'),
        ('lightning_last_det_time',         'INTEGER'),
        ('alder_pollen',                    'REAL'),               
        ('birch_pollen',                    'REAL'),
        ('olive_pollen',                    'REAL'),
        ('grass_pollen',                    'REAL'),
        ('mugwort_pollen',                  'REAL'),
        ('ragweed_pollen',                  'REAL'),
        ('p_rain',                          'REAL'),
        ('p_rainRate',                      'REAL'),
        ('p_hourRain',                      'REAL'),
        ('p_dayRain',                       'REAL'),
        ('p_weekRain',                      'REAL'),
        ('p_monthRain',                     'REAL'),
        ('p_yearRain',                      'REAL'),
        ('p_stormRain',                     'REAL'),
        ('isRaining',                       'REAL'),
        ('hourRain',                        'REAL'),
        ('dayRain',                         'REAL'),
        ('weekRain',                        'REAL'),
        ('monthRain',                       'REAL'),
        ('yearRain',                        'REAL'),
        ('stormRain',                       'REAL'),
        ('sunshine_time',                   'REAL'),
        ('sunshine_time_hours',             'REAL'),
        ('is_sunshine',                     'REAL'),
        ('threshold',                       'REAL'),
        ('vpd',                             'REAL'),        
        ('pm4_0',                           'REAL'),
        ('pm2_5_SDS',                       'REAL'),
        ('pm10_0_SDS',                      'REAL'),
        ('pv_power',                        'REAL'),
        ('pv_voltage_1',                    'REAL'),
        ('pv_voltage_2',                    'REAL'),
        ('pv_current_1',                    'REAL'),
        ('pv_current_2',                    'REAL'),
        ('pv_power_1',                      'REAL'),
        ('pv_power_2',                      'REAL'),
        ('pv_energy_today',                 'REAL'),
        ('battery_power',                   'REAL'),
        ('battery_voltage',                 'REAL'),
        ('battery_current',                 'REAL'),
        ('battery_soc',                     'REAL'),
        ('battery_temp',                    'REAL'),
        ('battery_charge_today',            'REAL'),
        ('battery_discharge_today',         'REAL'),
        ('battery_discharge_total',         'REAL'),
        ('grid_power',                      'REAL'),
        ('grid_power_ct',                   'REAL'),
        ('grid_voltage',                    'REAL'),
        ('grid_frequency',                  'REAL'),
        ('grid_import_today',               'REAL'),
        ('grid_import_total',               'REAL'),
        ('grid_export_today',               'REAL'),
        ('load_power',                      'REAL'),
        ('load_power_essential',            'REAL'),
        ('load_power_non_essential',        'REAL'),
        ('load_percentage',                 'REAL'),
        ('inverter_temp',                   'REAL'),
        ('ac_output_frequency',             'REAL'),
        ('ac_output_voltage',               'REAL')]

# Column names derived from schema for filtering loop packets
SCHEMA_COLS = set(col for col, _ in schema)
# Every DivumWX extras column (everything in `schema` but the three key
# columns). They live only in DivumWX's own extras database, so all of them
# can be added there when missing -- including the solar/battery ones, which
# simply stay empty on stations without that kit.
AUTO_ADD_COLS = set(col for col in SCHEMA_COLS if col not in ('dateTime', 'usUnits', 'interval'))

# Where the extras are kept: DivumWX's own database, never WeeWX's main
# archive. WeeWX's guidelines: "Extensions should not modify the database
# schemas. If it requires data not found in the default databases, an
# extension should provide its own database and schema." The installer
# writes these weewx.conf sections (install.py: apply_extras_database_merge):
#
#   [DataBindings]
#       [[divumwx_extras_binding]]
#           database = divumwx_extras_db
#           table_name = archive
#           manager = weewx.manager.DaySummaryManager
#           schema = user.divumwx.schema
#   [Databases]
#       [[divumwx_extras_db]]
#           database_name = divumwx_extras.sdb
#           database_type = SQLite
EXTRAS_BINDING = 'divumwx_extras_binding'
EXTRAS_DATABASE = 'divumwx_extras_db'
EXTRAS_BINDING_DEFAULTS = {
    'database': EXTRAS_DATABASE,
    'table_name': 'archive',
    'manager': 'weewx.manager.DaySummaryManager',
    'schema': 'user.divumwx.schema',
}
EXTRAS_DATABASE_DEFAULTS = {
    'database_name': 'divumwx_extras.sdb',
    'database_type': 'SQLite',
}


def extras_database_exists(config_dict, binding=EXTRAS_BINDING):
    """True if weewx.conf binds `binding` to a database that exists (for
    SQLite, the file is there; any other database type, configured is
    enough)."""
    try:
        import weewx.manager
        db_name = config_dict['DataBindings'][binding]['database']
        db_dict = weewx.manager.get_database_dict_from_config(config_dict, db_name)
    except Exception:
        return False
    if 'sqlite' in str(db_dict.get('driver', '')).lower() or 'SQLITE_ROOT' in db_dict:
        return os.path.isfile(os.path.join(db_dict.get('SQLITE_ROOT', ''), db_dict.get('database_name', '')))
    return True


def ensure_extras_binding(config_dict):
    """Make sure `config_dict` has DivumWX's extras binding and database.
    If weewx.conf lacks them (the files were updated without re-running the
    installer), they're added to this in-memory copy with the defaults --
    WeeWX would otherwise quietly bind an unknown name to the MAIN database.
    Returns (ok, message): ok is False only if the binding points at the
    same database as wx_binding, which DivumWX must never write to."""
    added = []
    bindings = config_dict.setdefault('DataBindings', {})
    if EXTRAS_BINDING not in bindings:
        bindings[EXTRAS_BINDING] = dict(EXTRAS_BINDING_DEFAULTS)
        added.append('[DataBindings][[%s]]' % EXTRAS_BINDING)
    db_name = bindings[EXTRAS_BINDING].get('database', EXTRAS_DATABASE)
    databases = config_dict.setdefault('Databases', {})
    if db_name not in databases:
        databases[db_name] = dict(EXTRAS_DATABASE_DEFAULTS)
        added.append('[Databases][[%s]]' % db_name)
    main_db = bindings.get('wx_binding', {}).get('database', 'archive_sqlite')
    same = db_name == main_db
    if not same:
        try:
            import weewx.manager
            ours = weewx.manager.get_database_dict_from_config(config_dict, db_name)
            theirs = weewx.manager.get_database_dict_from_config(config_dict, main_db)
            same = (ours.get('database_name') == theirs.get('database_name')
                    and ours.get('SQLITE_ROOT') == theirs.get('SQLITE_ROOT')
                    and ours.get('host') == theirs.get('host'))
        except Exception:
            pass
    if same:
        return False, ('[DataBindings][[%s]] points at the same database as wx_binding; DivumWX '
                       'never writes to WeeWX\'s main database, so the extras are not being saved. '
                       'Point it at its own database (normally divumwx_extras.sdb).' % EXTRAS_BINDING)
    if added:
        return True, ('weewx.conf has no %s, so the defaults are being used (extras saved in '
                      'divumwx_extras.sdb). Re-run the DivumWX installer to add them to weewx.conf.'
                      % ' or '.join(added))
    return True, None


class DivumwxExtrasService(StdService):

    # Saves DivumWX's extra fields, one record per archive record, in
    # DivumWX's own extras database (divumwx_extras_binding, normally
    # divumwx_extras.sdb). It never adds columns to, or writes to, WeeWX's
    # main archive (wx_binding).
    # The database is created here if it doesn't exist yet (WeeWX creates it
    # from the binding's schema, user.divumwx.schema -- the module-level
    # `schema` list above, NOT this class: pointing the schema at a class
    # crashes weewxd at startup). Columns missing from an existing extras
    # database are added to it (see AUTO_ADD_COLS).
    EXTRAS_ENABLED = True

    def __init__(self, engine, config_dict):
        super().__init__(engine, config_dict)
        self.dbm = None
        self.binding = EXTRAS_BINDING
        self.available_cols = set()
        self.skip_logged = False
        self.latest_loop_packet = None

        if not self.EXTRAS_ENABLED:
            syslog.syslog(syslog.LOG_WARNING,
                          'DivumwxExtrasService: extras disabled (EXTRAS_ENABLED = False) '
                          '— no extras data will be persisted until this is re-enabled.')
            return

        ok, msg = ensure_extras_binding(config_dict)
        if msg:
            syslog.syslog(syslog.LOG_INFO if ok else syslog.LOG_ERR, 'DivumwxExtrasService: ' + msg)
        if not ok:
            return
        try:
            existed = extras_database_exists(config_dict)
            self.dbm = self.engine.db_binder.get_manager(data_binding=self.binding, initialize=True)
        except Exception as e:
            syslog.syslog(syslog.LOG_ERR,
                          'DivumwxExtrasService: cannot open or create DivumWX\'s extras database '
                          '(%s): %s -- the extras are not being saved' % (self.binding, e))
            self.dbm = None
            return
        syslog.syslog(syslog.LOG_INFO,
                      'DivumwxExtrasService: saving the extras in DivumWX\'s own extras database '
                      '(%s, table %s)%s' % (self.binding, self.dbm.table_name,
                                            '' if existed else ' -- created it'))

        # An extras database from an earlier DivumWX may lack newer columns:
        # add them. This is DivumWX's own database, so that's allowed.
        # add_column() also creates each column's daily-summary table.
        db_cols = set(self.dbm.connection.columnsOf(self.dbm.table_name))
        missing = AUTO_ADD_COLS - db_cols
        if missing:
            col_types = dict(schema)
            added, failed = [], []
            for col in sorted(missing):
                try:
                    self.dbm.add_column(col, col_types.get(col, 'REAL').split()[0])
                    added.append(col)
                except Exception as e:
                    failed.append('%s (%s)' % (col, e))
            if added:
                syslog.syslog(syslog.LOG_INFO,
                              'DivumwxExtrasService: added %d missing column(s) to %s: %s'
                              % (len(added), self.binding, ', '.join(added)))
            if failed:
                syslog.syslog(syslog.LOG_WARNING,
                              'DivumwxExtrasService: %d column(s) could not be added to %s, so they '
                              'won\'t be saved: %s' % (len(failed), self.binding, '; '.join(failed)))
            db_cols = set(self.dbm.connection.columnsOf(self.dbm.table_name))
        self.available_cols = db_cols & SCHEMA_COLS

        self.bind(weewx.NEW_LOOP_PACKET, self.new_loop_packet)
        self.bind(weewx.NEW_ARCHIVE_RECORD, self.new_archive_record)

    def new_loop_packet(self, event):
        # Just cache the latest loop packet; don't write anything yet
        self.latest_loop_packet = event.packet

    def new_archive_record(self, event):
        if self.dbm is None:
            return
        if self.latest_loop_packet is None:
            syslog.syslog(syslog.LOG_WARNING,
                          'DivumwxExtrasService: no loop packet cached, skipping record for dateTime %d'
                          % event.record['dateTime'])
            return

        # Merge loop packet and archive record.
        # Loop packet provides high-frequency sensor data.
        # Archive record provides calculated fields (AirDensity, sunshine_time,
        # vpd, etc.) added by process_services and other NEW_ARCHIVE_RECORD
        # listeners such as SunshineDuration.
        # Archive record values take priority over loop packet values.
        merged = {}
        merged.update(self.latest_loop_packet)
        merged.update(event.record)

        updates = {col: merged[col] for col in self.available_cols
                   if col not in ('dateTime', 'usUnits', 'interval') and col in merged}

        if updates:
            # A record of its own in the extras database, in that database's
            # unit system; replaces any record already there for this time.
            rec = {'dateTime': event.record['dateTime'], 'usUnits': event.record['usUnits'],
                   'interval': event.record.get('interval')}
            rec.update(updates)
            try:
                if self.dbm.std_unit_system is not None and rec['usUnits'] != self.dbm.std_unit_system:
                    rec = weewx.units.to_std_system(rec, self.dbm.std_unit_system)
                try:
                    self.dbm.addRecord(rec, log_success=False, update=True)
                except TypeError:   # a WeeWX without addRecord(update=...)
                    if not self.dbm.getSql('SELECT dateTime FROM %s WHERE dateTime=?' % self.dbm.table_name,
                                           (rec['dateTime'],)):
                        self.dbm.addRecord(rec, log_success=False)
            except Exception as e:
                syslog.syslog(syslog.LOG_ERR,
                              'DivumwxExtrasService: failed to save record for dateTime %d in %s: %s'
                              % (event.record['dateTime'], self.binding, e))
        else:
            # Said once (then only at debug level), with the likely reason,
            # rather than on every archive record.
            usable = len(self.available_cols - {'dateTime', 'usUnits', 'interval'})
            msg = ('DivumwxExtrasService: no extras fields to save for dateTime %d (%s)'
                   % (event.record['dateTime'],
                      'none of the extras columns exist in %s' % self.binding if not usable else
                      '%d extras columns exist, but this record and the last loop packet carry none of '
                      'them; is SunshineDuration listed before DivumwxExtrasService in archive_services?'
                      % usable))
            if not self.skip_logged:
                syslog.syslog(syslog.LOG_WARNING, msg + ' -- further skips are logged at debug level')
                self.skip_logged = True
            else:
                syslog.syslog(syslog.LOG_DEBUG, msg)

    def shutDown(self):
        # The manager comes from the engine's DBBinder, which closes it.
        pass

##############################################################################
#
# SkyfieldLoopData -- a separate, optional live sky/almanac data feed.
#
# Writes a JSON file of live Skyfield data every `interval` seconds --
# independent of report generation entirely, and of LiveDataService above.
# This exists because almanac.json.tmpl (a Cheetah template, run once per
# report cycle by weewx.cheetahgenerator) was not producing output
# reliably; this sidesteps that whole pipeline. It runs as an ordinary
# WeeWX service bound to NEW_LOOP_PACKET, computes everything with direct
# Skyfield calls, and writes straight to a file -- no template, no
# Cheetah, no CheetahGenerator skin.conf stanza to get wrong.
#
# Deliberately writes its OWN JSON file rather than merging into
# LiveDataService's loop.json above: LiveDataService's cheap per-packet
# work (isDay, heat index, etc.) runs unconditionally on every loop
# packet and only throttles the file *write*, but this service's "slow"
# fields (rise/set/twilight/next-equinox searches) measured ~0.5-0.6s
# each in testing -- expensive enough that running them on every packet
# (rather than on their own separate, longer-throttled schedule) would be
# a real, continuous CPU cost. Keeping this as its own service with its
# own independent gating avoids that -- this is unaffected by the change
# below.
#
# It also deliberately does NOT go through weewx.almanac / the
# SkyfieldAlmanacType binder machinery used by report tags
# ($almanac.sun.alt etc.) -- that layer exists to serve report tags, with
# its own per-tag ValueHelper wrapping and caching, and is designed to be
# driven by Cheetah, not called directly from a service on a timer.
# Instead, this computes every value itself with the Skyfield API
# directly, without going through the report-tag plumbing to get there.
#
# CHANGE OF NOTE: this used to reuse the third-party weewx-skyfield
# extension's already-loaded ephemeris/timescale (via its registered
# almanac object's .sky attribute) specifically to avoid loading the same
# multi-MB kernel file twice in one process. That reuse mechanism was
# confirmed, on a real deployment with otherwise-correct config and
# permissions, to silently never find a match and never write anything,
# with the only trace an invisible DEBUG log line. weewx-skyfield and
# weewx-celestial are removed from the installer for now as a result
# (see install.py) -- this service now loads its own ephemeris kernel
# directly instead (see _load_sky()), independent of any other
# extension. See _load_sky()'s own docstring for the full story.
#
# Fields are split into two refresh rates:
#
#   - "fast" fields (altitude/azimuth/distance/right ascension/
#     declination/ecliptic position/illumination/hour angle) are
#     genuinely different every few seconds and are recomputed on every
#     write.
#   - "slow" fields (rise/set/transit, twilight, and the next equinox/
#     solstice/full/new moon) barely change over a day and require an
#     iterative search that is meaningfully more expensive than the fast
#     fields -- these are recomputed only every `slow_refresh_interval`
#     seconds (default one hour) and cached in between, so a short
#     `interval` (e.g. every 5-10 seconds) doesn't force an expensive
#     all-day rise/set/twilight/event search that often.
#
# Configuration (weewx.conf) -- shown with defaults, except target_path
# which is REQUIRED (no fallback -- install.py's apply_skyfieldloopdata_merge()
# always writes this from the site's actual HTML_ROOT; if it's absent
# here, the service logs an error and disables itself rather than
# guessing a path):
#
#     [SkyfieldLoopData]
#         enable = true
#         interval = 10                  # seconds between writes
#         slow_refresh_interval = 3600   # seconds between rise/set/twilight/event refreshes
#         target_path = <your DivumWX HTML_ROOT>/jsondata/almanac.json   # REQUIRED
#         ephemeris_dir = <target_path's HTML_ROOT>/skyfield-data   # optional, see _load_sky()
#
# And register the service itself, alongside whatever's already in
# data_services (comma-separated -- don't replace the existing list):
#
#     [Engine]
#         [[Services]]
#             data_services = user.divumwx.SkyfieldLoopData, ...
#
##############################################################################

SKYFIELDLOOPDATA_VERSION = '0.4.0'

# Skyfield's seasons()/moon_phases() event codes (see the Skyfield almanac
# module docs, and confirmed against weewx-skyfield's own SEASON_EVENTS/
# MOON_EVENTS tables, which use the same codes):
#   seasons:      0 = vernal (spring) equinox, 1 = summer solstice,
#                 2 = autumnal equinox,        3 = winter solstice
#   moon_phases:  0 = new moon, 1 = first quarter, 2 = full moon,
#                 3 = last quarter
SEASON_LABELS = {0: 'vernal_equinox', 1: 'summer_solstice',
                  2: 'autumnal_equinox', 3: 'winter_solstice'}
MOON_PHASE_LABELS = {0: 'new_moon', 1: 'first_quarter_moon',
                      2: 'full_moon', 3: 'last_quarter_moon'}

# How far ahead to search for the next equinox/solstice/moon-phase event.
# 400 days safely covers a full cycle of all four seasonal events and all
# four moon phases even from the least convenient starting point.
_EVENT_SEARCH_DAYS = 400

# Lunar eclipses average roughly one every ~6 months but the actual gap
# varies and can occasionally run longer, so this searches a full 2 years
# ahead to reliably find the next one from any starting point.
_ECLIPSE_SEARCH_DAYS = 730

# Named stars given the same almanac.<name>.az/.alt/.earth_distance
# treatment as the Sun/Moon/planets, in the "dotted" field set (see
# _compute_dotted_fields()). This service no longer depends on
# weewx-skyfield at all (see _load_sky() below) and does not load its
# own star catalog, so `sky.stars` is always None now and these three
# fields always come through as null -- the existing "not available"
# handling in _compute_dotted_fields() (already written defensively via
# getattr(sky, 'stars', None)) covers this without needing changes.
_NAMED_STARS = ('proxima_centauri',)

# The standalone ephemeris kernel this service loads directly, now that
# it no longer reuses weewx-skyfield's already-loaded one (see
# _load_sky()'s docstring for the full story). de421.bsp is the same
# compact JPL kernel DivumWX itself used to bundle before switching to
# reusing weewx-skyfield's -- covers 1899-2053, ~17MB, standard choice
# for this kind of loop-rate solar-system almanac work.
_EPHEMERIS_FILE = 'de421.bsp'

# Proxima Centauri, for the Celestial page's Geocentric dial. Not a
# catalogue lookup -- one hand-entered star (Hipparcos 70890, Gaia DR3
# proper motion/parallax), so no star catalogue file is needed. Filled
# into _Sky.stars by _load_sky(), which is what
# _compute_dotted_fields() already looks in for _NAMED_STARS.
_PROXIMA_CENTAURI = dict(
    ra_hours=(14, 29, 42.9487), dec_degrees=(-62, 40, 46.141),
    ra_mas_per_year=-3781.31, dec_mas_per_year=769.77,
    parallax_mas=768.07, radial_km_per_s=-22.4)

# Major annual meteor showers for the Celestial page's countdown row,
# from the IMO Meteor Shower Calendar: peak as solar longitude (J2000,
# degrees) -- which, unlike a calendar date, is the same every year --
# plus nominal ZHR and radiant (RA, Dec in degrees).
#   (code, name, peak solar longitude, ZHR, radiant RA, radiant Dec)
_METEOR_SHOWERS = (
    ('QUA', 'Quadrantids', 283.15, 80, 230, 49),
    ('LYR', 'Lyrids', 32.32, 18, 271, 34),
    ('ETA', 'Eta Aquariids', 45.5, 50, 338, -1),
    ('SDA', 'Southern Delta Aquariids', 127.0, 25, 340, -16),
    ('PER', 'Perseids', 140.0, 100, 48, 58),
    ('DRA', 'Draconids', 195.4, 10, 262, 54),
    ('ORI', 'Orionids', 208.0, 20, 95, 16),
    ('LEO', 'Leonids', 235.27, 15, 152, 22),
    ('GEM', 'Geminids', 262.2, 150, 112, 33),
    ('URS', 'Ursids', 270.7, 10, 217, 76),
)

# A full moon counts as a supermoon when the Moon is closer than this
# (geocentric, km) -- the threshold most almanacs use. Overridable as
# supermoon_km in [SkyfieldLoopData].
_SUPERMOON_KM = 360000.0

# How many days of sunrise/sunset/astronomical-darkness instants to
# emit for the Celestial page, so its countdown chips can roll on to
# the next event client-side without waiting for the hourly slow refresh.
_CELESTIAL_SUN_EVENT_DAYS = 3

# Name(s) to try, in order, when looking up each body in the loaded
# kernel. de421.bsp (like most JPL kernels) exposes the Sun/Moon/Earth
# directly by name, but the other eight planets only as barycenters --
# Skyfield's SpiceKernel.__getitem__ does fuzzy/substring matching so
# the bare name often also works, but that's not guaranteed across
# kernel files or Skyfield versions, so both forms are tried explicitly
# here rather than assumed.
_BODY_NAME_CANDIDATES = {
    'earth': ('earth', 'earth barycenter'),
    'sun': ('sun',),
    'moon': ('moon',),
    'mercury': ('mercury barycenter', 'mercury'),
    'venus': ('venus barycenter', 'venus'),
    'mars': ('mars barycenter', 'mars'),
    'jupiter': ('jupiter barycenter', 'jupiter'),
    'saturn': ('saturn barycenter', 'saturn'),
    'uranus': ('uranus barycenter', 'uranus'),
    'neptune': ('neptune barycenter', 'neptune'),
    'pluto': ('pluto barycenter', 'pluto'),
}


class _Sky:
    """Minimal stand-in for weewx-skyfield's own Sky object -- holds only
    the attributes SkyfieldLoopData actually reads (grep-confirmed against
    every `self.sky.*`/`sky.*` access in this class: ts, earth, sun, moon,
    orbs, planets, and an optional stars that's always None here -- see
    _NAMED_STARS' comment above). Deliberately not a full reimplementation
    of weewx-skyfield's own (much larger) Sky class -- this only needs to
    satisfy what SkyfieldLoopData itself calls.

    `bodies` is the fully-resolved {name: <Skyfield body>} dict built by
    _load_sky() below (via _lookup_body(), not indexed directly here) --
    covers earth/sun/moon plus all eight planets. `self.orbs` deliberately
    EXCLUDES 'earth' -- matches weewx-skyfield's own documented shape
    ("sun, moon, mercury, ... pluto", per the existing comment at this
    class's dict(sky.orbs) call site) -- the hlongitude loop that consumes
    orbs already special-cases name == 'sun' to mean Earth's own
    heliocentric position by convention, so an extra 'earth' key in orbs
    itself would be a spurious duplicate, not new information."""

    def __init__(self, ts, kernel, bodies):
        self.ts = ts
        self.planets = kernel  # the raw kernel: skyfield.almanac.* functions need this, not a body
        self.earth = bodies['earth']
        self.sun = bodies['sun']
        self.moon = bodies['moon']
        self.orbs = {name: body for name, body in bodies.items() if name != 'earth'}
        self.stars = None      # no star catalog loaded standalone -- see _NAMED_STARS comment


##############################################################################
#
# Satellites and comets for the Celestial page.
#
# Configured in weewx.conf (shown with the defaults used when a section is
# absent; an empty section turns that family off):
#
#     [SkyfieldLoopData]
#         tle_refresh_hours = 12       # CelesTrak asks for no more than every 2 h
#         comet_refresh_hours = 48
#         visible_pass_days = 10       # how far ahead to look for a visible pass
#         [[Satellites]]
#             iss = 25544              # tag = NORAD catalogue number[, display name]
#             tiangong = 48274
#             terra = 25994
#             noaa21 = 54234
#         [[Comets]]
#             halley = 1P              # tag = MPC designation[, display name]
#             hale_bopp = C/1995 O1
#
# Orbital elements are downloaded (TLEs from CelesTrak, falling back to
# SatNOGS and then the TLE API when CelesTrak can't be reached; the comet
# file from the Minor Planet Center) and cached in ephemeris_dir. No
# pandas is needed. Pass predictions
# are computed on a background thread. Nothing here ever blocks a loop
# packet on the network or on a pass search: each write only reads the
# latest results and computes the cheap live positions.
#
##############################################################################

_SATELLITE_DEFAULTS = (
    ('iss', 25544, 'ISS'),
    ('tiangong', 48274, 'Tiangong'),
    ('terra', 25994, 'Terra'),
    ('noaa21', 54234, 'NOAA-21'),
)
_COMET_DEFAULTS = (
    ('halley', '1P', 'Halley'),
    ('hale_bopp', 'C/1995 O1', 'Hale-Bopp'),
)
# Where a satellite's elements come from, tried in order: a source that
# fails is skipped for the rest of that cycle and backed off (so one
# unreachable server doesn't cost a timeout per satellite every cycle).
#   (name, URL with %d for the NORAD number, reply format)
_TLE_SOURCES = (
    ('CelesTrak', 'https://celestrak.org/NORAD/elements/gp.php?CATNR=%d&FORMAT=TLE', 'tle'),
    ('SatNOGS', 'https://db.satnogs.org/api/tle/?norad_cat_id=%d&format=json', 'satnogs'),
    ('TLE API', 'https://tle.ivanstanojevic.me/api/tle/%d', 'tleapi'),
)
_MPC_COMET_URL = 'https://www.minorplanetcenter.net/iau/MPCORB/CometEls.txt'
_FETCH_TIMEOUT_S = 20


def _tle_checksum_ok(line: str) -> bool:
    """A TLE line's last digit is the sum of its digits (minus signs count
    as 1) modulo 10."""
    if len(line) < 69 or not line[68].isdigit():
        return False
    total = sum(int(c) if c.isdigit() else (1 if c == '-' else 0) for c in line[:68])
    return total % 10 == int(line[68])


def _comet_orbit(row, ts):
    """skyfield.data.mpc.comet_orbit() without importing skyfield.data.mpc,
    which needs pandas at import time even though comet_orbit() itself
    doesn't. Falls back to Skyfield's own function if its internals ever
    change shape."""
    from skyfield.constants import GM_SUN_Pitjeva_2005_km3_s2 as GM_SUN
    try:
        from skyfield.keplerlib import _KeplerOrbit
        from skyfield.data.spice import inertial_frames
        e, q = row['eccentricity'], row['perihelion_distance_au']
        p = q * 2.0 if e == 1.0 else (q / (1.0 - e)) * (1.0 - e * e)
        t_peri = ts.tt(row['perihelion_year'], row['perihelion_month'], row['perihelion_day'])
        comet = _KeplerOrbit._from_periapsis(
            p, e, row['inclination_degrees'], row['longitude_of_ascending_node_degrees'],
            row['argument_of_perihelion_degrees'], t_peri, GM_SUN, 10, row['designation'])
        comet._rotation = inertial_frames['ECLIPJ2000'].T
        return comet
    except (ImportError, AttributeError, TypeError, KeyError):
        import skyfield.data.mpc
        return skyfield.data.mpc.comet_orbit(row, ts, GM_SUN)
_COMET_FILE = 'CometEls.txt'
_DOWNLOAD_RETRY_S = 3600          # after a failed download, wait this long before trying again
_PASS_REFRESH_S = 1800            # re-predict passes at least this often
_PASS_TRACK_STEP_S = 10           # sampling of each pass's sky track
_VISIBLE_MIN_ALT = 10.0           # a pass is "visible" when sunlit, at least this high,
_VISIBLE_MAX_SUN_ALT = -6.0       # ...and the station's sky at least civil-twilight dark
_TAG_OK = re.compile(r'^[a-z][a-z0-9_]*$')


class _Row(dict):
    """A comet-file row that skyfield.data.mpc.comet_orbit() can read
    both as row.field and row['field'] -- without needing pandas."""
    __getattr__ = dict.__getitem__


def _parse_comet_line(line: str) -> Optional[_Row]:
    """One line of the MPC's CometEls.txt, by the column layout Skyfield
    itself uses (skyfield.data.mpc._COMET_COLUMNS)."""
    if len(line) < 103:
        return None

    def f(a, b):
        s = line[a:b].strip()
        return float(s) if s else None
    try:
        row = _Row(
            perihelion_year=int(line[14:18]),
            perihelion_month=int(line[19:21]),
            perihelion_day=float(line[22:29]),
            perihelion_distance_au=float(line[30:39]),
            eccentricity=float(line[41:49]),
            argument_of_perihelion_degrees=float(line[51:59]),
            longitude_of_ascending_node_degrees=float(line[61:69]),
            inclination_degrees=float(line[71:79]),
            magnitude_g=f(91, 95),
            magnitude_k=f(96, 100),
            designation=line[102:158].strip(),
        )
        y, m, d = line[81:85].strip(), line[85:87].strip(), line[87:89].strip()
        row['epoch'] = (int(y), int(m), int(d)) if y and m and d else None
        return row
    except ValueError:
        return None


def _comet_matches(designation: str, wanted: str) -> bool:
    """'1P' matches '1P/Halley'; 'C/1995 O1' matches 'C/1995 O1 (Hale-Bopp)'."""
    base = designation.split(' (')[0].strip()
    return base == wanted or base.startswith(wanted + '/')


class _CelestialOrbits:
    """Satellites and comets: downloads, pass predictions (background
    thread) and the live fields SkyfieldLoopData merges into each write."""

    def __init__(self, svc_dict, cache_dir: str, extra_tle_dirs=()):
        self.cache_dir = cache_dir
        self.extra_tle_dirs = [d for d in extra_tle_dirs if d]
        self._status: Dict[str, Dict[str, Any]] = {}
        self.sat_cfg = self._parse_section(svc_dict, 'Satellites', _SATELLITE_DEFAULTS, int)
        self.comet_cfg = self._parse_section(svc_dict, 'Comets', _COMET_DEFAULTS, str)
        try:
            self.tle_refresh_s = max(2.0, float(svc_dict.get('tle_refresh_hours', 12))) * 3600
            self.comet_refresh_s = max(6.0, float(svc_dict.get('comet_refresh_hours', 48))) * 3600
            self.visible_days = min(30.0, max(1.0, float(svc_dict.get('visible_pass_days', 10))))
        except (TypeError, ValueError):
            self.tle_refresh_s, self.comet_refresh_s, self.visible_days = 12 * 3600, 48 * 3600, 10.0
        self.enabled = bool(self.sat_cfg or self.comet_cfg)
        self._lock = threading.Lock()
        self._stop = threading.Event()
        self._thread = None
        self._sats: Dict[str, Dict[str, Any]] = {}
        self._comets: Dict[str, Dict[str, Any]] = {}
        self._passes: Dict[str, Dict[str, Any]] = {}
        self._loaded_mtime: Dict[str, float] = {}
        self._retry_after: Dict[str, float] = {}
        self._source_down: Dict[str, float] = {}
        self._source_why: Dict[str, str] = {}
        self._warned_missing: set = set()

    @staticmethod
    def _parse_section(svc_dict, name, defaults, conv):
        """{tag: (key, label)} in configured order. Absent section ->
        defaults; empty section -> nothing."""
        out: Dict[str, Any] = {}
        if name not in svc_dict:
            for tag, key, label in defaults:
                out[tag] = (key, label)
            return out
        default_labels = {tag: label for tag, _, label in defaults}
        reserved = set(_BODY_NAME_CANDIDATES) | set(_NAMED_STARS)
        for tag, value in svc_dict[name].items():
            tag = str(tag).strip().lower()
            parts = value if isinstance(value, (list, tuple)) else [value]
            parts = [str(p).strip() for p in parts if str(p).strip()]
            if not _TAG_OK.match(tag) or tag in reserved or not parts:
                log.warning("SkyfieldLoopData: ignoring [[%s]] entry '%s' -- the tag must be a "
                            "lowercase identifier that isn't a body name, with a value." % (name, tag))
                continue
            try:
                key = conv(parts[0])
            except (TypeError, ValueError):
                log.warning("SkyfieldLoopData: ignoring [[%s]] %s = %r -- not a valid %s."
                            % (name, tag, value, 'NORAD number' if conv is int else 'designation'))
                continue
            label = ', '.join(parts[1:]) or default_labels.get(tag) or (parts[0] if conv is str else tag.upper())
            out[tag] = (key, label)
        return out

    # -- lifecycle ---------------------------------------------------------

    def start(self, sky, geographic, observer):
        if not self.enabled or self._thread is not None:
            return
        self.sky, self.geo, self.observer = sky, geographic, observer
        os.makedirs(self.cache_dir, exist_ok=True)
        self._thread = threading.Thread(target=self._run, name='SkyfieldLoopData-orbits', daemon=True)
        self._thread.start()
        log.info("SkyfieldLoopData: tracking %d satellite(s) and %d comet(s)"
                 % (len(self.sat_cfg), len(self.comet_cfg)))

    def stop(self):
        self._stop.set()

    def _run(self):
        while not self._stop.is_set():
            try:
                self._cycle()
            except Exception as e:
                log.error("SkyfieldLoopData: satellite/comet update failed: %s" % e)
                log_traceback(log.error, "SkyfieldLoopData: ")
            self._stop.wait(60)

    # -- downloads -----------------------------------------------------------

    def _due(self, path: str, max_age_s: float) -> bool:
        try:
            return time.time() - os.path.getmtime(path) >= max_age_s
        except OSError:
            return True

    @staticmethod
    def _fetch(url: str) -> str:
        """GET url and return the body as text; raises on any failure."""
        import urllib.request
        req = urllib.request.Request(url, headers={'User-Agent': 'DivumWX-SkyfieldLoopData (WeeWX weather station)'})
        with urllib.request.urlopen(req, timeout=_FETCH_TIMEOUT_S) as resp:
            return resp.read().decode('utf-8', errors='replace')

    @staticmethod
    def _why(e: Exception) -> str:
        code = getattr(e, 'code', None)
        if code == 403:
            return 'refused (HTTP 403) \u2014 the server locks out addresses that download too often'
        if code:
            return 'HTTP %s' % code
        reason = getattr(e, 'reason', None)        # URLError: timed out, refused, DNS...
        return str(reason if reason is not None else e)

    @staticmethod
    def _backoff(e: Exception, have_copy: bool) -> float:
        """Six hours after a 403 (retrying sooner only extends a lock-out),
        otherwise an hour, or 15 minutes while there is no copy at all."""
        if getattr(e, 'code', None) == 403:
            return 6 * 3600
        return _DOWNLOAD_RETRY_S if have_copy else 900

    def _parse_tle_reply(self, kind: str, text: str):
        """(name, line1, line2) from one source's reply, checksums verified."""
        if kind == 'tle':
            parsed = self._tle_lines(text)
        else:
            obj = json.loads(text)
            if kind == 'satnogs':
                obj = obj[0] if isinstance(obj, list) and obj else {}
                parsed = (str(obj.get('tle0', '')).lstrip('0 ').strip() or None,
                          obj.get('tle1'), obj.get('tle2')) if obj.get('tle1') else None
            else:
                parsed = (obj.get('name'), obj.get('line1'), obj.get('line2')) if obj.get('line1') else None
        if not parsed:
            first = text.strip().splitlines()[0][:100] if text.strip() else '(empty)'
            raise ValueError('no elements in the reply: %s' % first)
        name, l1, l2 = parsed
        if not (l1.startswith('1 ') and l2.startswith('2 ') and _tle_checksum_ok(l1) and _tle_checksum_ok(l2)):
            raise ValueError('elements failed their checksum')
        return name, l1.rstrip(), l2.rstrip()

    def _download_tle(self, norad: int, key: str) -> bool:
        """Try each source in _TLE_SOURCES until one gives valid elements."""
        now = time.time()
        if now < self._retry_after.get(key, 0):
            return False
        path = os.path.join(self.cache_dir, 'sat_%d.tle' % norad)
        have = os.path.isfile(self._tle_path(norad))
        errors = []
        for name, url, kind in _TLE_SOURCES:
            if now < self._source_down.get(name, 0):
                errors.append('%s: %s' % (name, self._source_why.get(name, 'unavailable')))
                continue
            try:
                n, l1, l2 = self._parse_tle_reply(kind, self._fetch(url % norad))
            except Exception as e:
                why = self._why(e)
                errors.append('%s: %s' % (name, why))
                # A server-level failure (timeout, refusal, 5xx) takes the
                # source out for every satellite; a bad reply for one
                # satellite doesn't.
                if not isinstance(e, ValueError) and getattr(e, 'code', None) != 404:
                    self._source_down[name] = now + self._backoff(e, have)
                    self._source_why[name] = why
                    log.warning("SkyfieldLoopData: %s unreachable (%s) -- trying the next source; "
                                "skipped for %d min" % (name, why, self._backoff(e, have) // 60))
                continue
            tmp = path + '.tmp'
            with open(tmp, 'w', encoding='utf-8') as fh:
                fh.write('%s\n%s\n%s\n' % (n or '', l1, l2))
            os.replace(tmp, path)
            log.info("SkyfieldLoopData: satellite %d elements from %s" % (norad, name))
            self._set_status(key, 'ok', None, source=name)
            return True
        wait = _DOWNLOAD_RETRY_S if have else 900
        self._retry_after[key] = now + wait
        why = '; '.join(errors)
        log.warning("SkyfieldLoopData: no source gave elements for satellite %d (%s) -- %s"
                    % (norad, why, 'using the cached copy' if have else 'next try in %d min' % (wait // 60)))
        self._set_status(key, 'ok' if have else 'error', why, retry=now + wait)
        return False

    def _download_comets(self, path: str) -> bool:
        now = time.time()
        if now < self._retry_after.get('comets', 0):
            return False
        have = os.path.isfile(path)
        try:
            text = self._fetch(_MPC_COMET_URL)
            if text.count('\n') < 100:
                raise ValueError('unexpected reply: %s' % (text.strip().splitlines() or ['(empty)'])[0][:100])
            tmp = path + '.tmp'
            with open(tmp, 'w', encoding='utf-8') as fh:
                fh.write(text)
            os.replace(tmp, path)
            log.info("SkyfieldLoopData: downloaded the MPC comet file")
            self._set_status('comets', 'ok', None)
            return True
        except Exception as e:
            wait = self._backoff(e, have)
            self._retry_after['comets'] = now + wait
            log.warning("SkyfieldLoopData: MPC comet file download failed: %s -- %s"
                        % (self._why(e), 'using the cached copy' if have else 'next try in %d min' % (wait // 60)))
            self._set_status('comets', 'ok' if have else 'error', self._why(e), retry=now + wait)
            return False

    def _set_status(self, key: str, state: str, error: Optional[str], retry: Optional[float] = None,
                    source: Optional[str] = None):
        with self._lock:
            prev = self._status.get(key) or {}
            self._status[key] = {'state': state, 'error': error, 'retry': retry, 'at': time.time(),
                                 'source': source or prev.get('source')}

    def _tle_path(self, norad: int) -> str:
        """Our cached TLE, or weewx-skyfield's own cached copy of the same
        satellite if that is newer (or ours doesn't exist yet) -- a station
        that runs weewx-skyfield too needn't download it twice."""
        ours = os.path.join(self.cache_dir, 'sat_%d.tle' % norad)
        best, best_m = ours, (os.path.getmtime(ours) if os.path.isfile(ours) else -1)
        for d in self.extra_tle_dirs:
            p = os.path.join(d, 'wxskyfield_sat_%d.tle' % norad)
            try:
                m = os.path.getmtime(p)
            except OSError:
                continue
            if m > best_m:
                best, best_m = p, m
        return best

    @staticmethod
    def _tle_lines(text: str):
        lines = [ln.rstrip() for ln in text.strip().splitlines() if ln.strip()]
        for i in range(len(lines) - 1):
            if lines[i].startswith('1 ') and lines[i + 1].startswith('2 '):
                name = lines[i - 1].strip() if i > 0 else None
                return name, lines[i], lines[i + 1]
        return None

    # -- the background cycle ----------------------------------------------------

    def _cycle(self):
        ts = self.sky.ts
        changed = set()
        for tag, (norad, label) in self.sat_cfg.items():
            key = 'sat:' + tag
            if key not in self._status:
                self._set_status(key, 'pending', None)
            if self._due(self._tle_path(norad), self.tle_refresh_s):
                self._download_tle(norad, key)
            path = self._tle_path(norad)
            try:
                mtime = os.path.getmtime(path)
            except OSError:
                continue
            if self._loaded_mtime.get(path) == mtime:
                continue
            parsed = self._tle_lines(open(path, encoding='utf-8', errors='replace').read())
            if not parsed:
                log.warning("SkyfieldLoopData: no usable TLE in %s" % path)
                self._set_status(key, 'error', 'the cached orbit file has no usable elements')
                continue
            if os.path.basename(path).startswith('wxskyfield_'):
                log.info("SkyfieldLoopData: %s -- using weewx-skyfield's cached elements %s" % (tag, path))
            self._set_status(key, 'ok', None)
            name, l1, l2 = parsed
            sat = skyfield.api.EarthSatellite(l1, l2, name or label, ts)
            with self._lock:
                self._sats[tag] = {'sat': sat, 'norad': norad, 'label': label,
                                   'epoch': sat.epoch.utc_datetime().timestamp()}
            self._loaded_mtime[path] = mtime
            changed.add(tag)

        if self.comet_cfg:
            path = os.path.join(self.cache_dir, _COMET_FILE)
            if 'comets' not in self._status:
                self._set_status('comets', 'pending', None)
            if self._due(path, self.comet_refresh_s):
                self._download_comets(path)
            elif self._status['comets']['state'] == 'pending':
                self._set_status('comets', 'ok', None)
            try:
                mtime = os.path.getmtime(path)
            except OSError:
                mtime = None
            if mtime is not None and self._loaded_mtime.get(path) != mtime:
                # Marked as handled even if loading fails, so a bad file or a
                # bug logs once, not every minute until the next download.
                self._loaded_mtime[path] = mtime
                try:
                    self._load_comets(path)
                except Exception as e:
                    log.error("SkyfieldLoopData: could not read the comet file %s: %s" % (path, e))
                    log_traceback(log.error, "SkyfieldLoopData: ")
                    self._set_status('comets', 'error', 'could not read the comet file: %s' % e)

        now = time.time()
        for tag in list(self._sats):
            p = self._passes.get(tag)
            np_ = p and p.get('next_pass')
            if (tag in changed or p is None or now - p['computed'] >= _PASS_REFRESH_S
                    or (np_ and now > np_['set'])):
                result = self._predict(self._sats[tag]['sat'], now)
                with self._lock:
                    self._passes[tag] = result

    def _load_comets(self, path):
        ts = self.sky.ts
        best: Dict[str, _Row] = {}
        with open(path, encoding='utf-8', errors='replace') as fh:
            for line in fh:
                for tag, (wanted, _) in self.comet_cfg.items():
                    if wanted.split('/')[0] not in line:
                        continue
                    row = _parse_comet_line(line.rstrip('\n'))
                    if row and _comet_matches(row['designation'], wanted):
                        prev = best.get(tag)
                        if prev is None or (row['epoch'] or (0,)) > (prev['epoch'] or (0,)):
                            best[tag] = row
        comets = {}
        for tag, (wanted, label) in self.comet_cfg.items():
            row = best.get(tag)
            if row is None:
                if tag not in self._warned_missing:
                    log.warning("SkyfieldLoopData: comet %s (%s) is not in the MPC comet file -- "
                                "the page shows it as absent." % (tag, wanted))
                    self._warned_missing.add(tag)
                continue
            orbit = _comet_orbit(row, ts)
            tp = ts.tt(row['perihelion_year'], row['perihelion_month'], row['perihelion_day'])
            e, q = row['eccentricity'], row['perihelion_distance_au']
            period_days = 365.25 * (q / (1.0 - e)) ** 1.5 if e < 1.0 else None
            comets[tag] = {
                'orbit': orbit, 'body': self.sky.sun + orbit, 'label': label,
                'designation': row['designation'], 'g': row['magnitude_g'], 'k': row['magnitude_k'],
                'perihelion_ts': (tp.tt - 2440587.5) * 86400.0 - 69.184,   # TT -> Unix, close enough for a date
                'period_days': period_days,
                'elements_epoch': row['epoch'],
            }
        with self._lock:
            self._comets = comets

    # -- pass prediction -------------------------------------------------------------

    def _predict(self, sat, now: float) -> Dict[str, Any]:
        ts = self.sky.ts
        t0 = ts.from_datetime(datetime.datetime.fromtimestamp(now - 1200, datetime.timezone.utc))
        t1 = ts.tt_jd(t0.tt + self.visible_days)
        times, events = sat.find_events(self.geo, t0, t1, altitude_degrees=0.0)
        passes, cur = [], None
        for ti, ev in zip(times, events):
            if ev == 0:
                cur = {'rise': ti}
            elif ev == 1 and cur is not None:
                cur.setdefault('culm', ti)
            elif ev == 2 and cur is not None and 'culm' in cur:
                cur['set'] = ti
                passes.append(cur)
                cur = None
        out: Dict[str, Any] = {'computed': now, 'next_pass': None, 'next_visible_pass': None}
        for p in passes:
            if p['set'].utc_datetime().timestamp() <= now:
                continue
            d = self._describe(sat, p)
            if out['next_pass'] is None:
                out['next_pass'] = d
            if d['visible']:
                out['next_visible_pass'] = d
                break
        return out

    def _describe(self, sat, p) -> Dict[str, Any]:
        ts = self.sky.ts
        rise, culm, set_ = p['rise'], p['culm'], p['set']
        unix = lambda tt: tt.utc_datetime().timestamp()
        dur = (set_.tt - rise.tt) * 86400.0
        n = max(2, int(dur // _PASS_TRACK_STEP_S) + 2)
        times = ts.linspace(rise, set_, n)
        alt, az, _ = (sat - self.geo).at(times).altaz()
        lit = sat.at(times).is_sunlit(self.sky.planets)
        sun_alt = self.observer.at(times).observe(self.sky.sun).apparent().altaz()[0].degrees
        seen = lit & (sun_alt <= _VISIBLE_MAX_SUN_ALT) & (alt.degrees >= _VISIBLE_MIN_ALT)
        calt, caz, _ = (sat - self.geo).at(culm).altaz()
        csun = self.observer.at(culm).observe(self.sky.sun).apparent().altaz()[0].degrees
        stamps = [unix(x) for x in times]
        d = {
            'rise': unix(rise), 'culmination': unix(culm), 'set': unix(set_),
            'duration': round(dur, 1),
            'max_altitude': round(float(calt.degrees), 2),
            'rise_azimuth': round(float(az.degrees[0]), 1),
            'culmination_azimuth': round(float(caz.degrees), 1),
            'set_azimuth': round(float(az.degrees[-1]), 1),
            'sun_altitude': round(float(csun), 1),
            'visible': bool(seen.any()),
            'visible_start': None, 'visible_end': None,
            # [seconds after rise, altitude, azimuth, sunlit 0/1]
            'track': [[int(round(s - stamps[0])), round(float(a), 1), round(float(z), 1), int(bool(l))]
                      for s, a, z, l in zip(stamps, alt.degrees, az.degrees, lit)],
        }
        if d['visible']:
            idx = [i for i, v in enumerate(seen) if v]
            d['visible_start'], d['visible_end'] = stamps[idx[0]], stamps[idx[-1]]
        return d

    # -- live fields (loop thread) ---------------------------------------------------

    def fields(self, t, now_ts: float) -> Dict[str, Any]:
        with self._lock:
            sats, comets, passes = dict(self._sats), dict(self._comets), dict(self._passes)
            status = {k: dict(v) for k, v in self._status.items()}
        out: Dict[str, Any] = {
            # Lets the page tell "still starting" from "stopped".
            'celestial.orbits.running': bool(self._thread and self._thread.is_alive()),
        }
        sat_list = []
        for tag, (norad, label) in self.sat_cfg.items():
            p = 'almanac.%s.' % tag
            s = sats.get(tag)
            entry: Dict[str, Any] = {'tag': tag, 'label': label, 'norad': norad,
                                     'status': status.get('sat:' + tag)}
            out[p + 'label'] = label
            if s is None:
                out[p + 'az'] = out[p + 'alt'] = out[p + 'sunlit'] = None
                entry['missing'] = True
            else:
                topo = (s['sat'] - self.geo).at(t)
                alt, az, dist = topo.altaz()
                lit = bool(s['sat'].at(t).is_sunlit(self.sky.planets))
                out[p + 'az'], out[p + 'alt'], out[p + 'sunlit'] = float(az.degrees), float(alt.degrees), lit
                out[p + 'range_km'] = float(dist.km)
                entry['elements_epoch'] = s['epoch']
                pr = passes.get(tag) or {}
                np_ = pr.get('next_pass')
                vp = pr.get('next_visible_pass')
                # The next pass's track is only needed to move the live
                # marker, so it is sent from half an hour before rise; the
                # visible pass's track (the pass chart) is always sent, once.
                if np_ is not None and vp is not np_ and np_['rise'] - now_ts > 1800:
                    np_ = {k: v for k, v in np_.items() if k != 'track'}
                entry['next_pass'] = np_
                entry['next_visible_pass'] = 'next_pass' if (vp is not None and vp is pr.get('next_pass')) else vp
            sat_list.append(entry)
        if self.sat_cfg:
            out['celestial.satellites'] = sat_list

        comet_list = []
        for tag, (wanted, label) in self.comet_cfg.items():
            p = 'almanac.%s.' % tag
            c = comets.get(tag)
            out[p + 'label'] = label
            entry = {'tag': tag, 'label': label, 'designation': wanted, 'status': status.get('comets')}
            if c is None:
                for k in ('az', 'alt', 'earth_distance', 'sun_distance', 'mag', 'perihelion.unix_epoch.raw'):
                    out[p + k] = None
                entry['missing'] = True
            else:
                app = self.observer.at(t).observe(c['body']).apparent()
                alt, az, dist = app.altaz()
                r = float(c['orbit'].at(t).distance().au)
                mag = None
                if c['g'] is not None and c['k'] is not None and r > 0:
                    mag = c['g'] + 5.0 * math.log10(dist.au) + 2.5 * c['k'] * math.log10(r)
                nxt = c['perihelion_ts']
                if c['period_days']:
                    while nxt < now_ts:
                        nxt += c['period_days'] * 86400.0
                elif nxt < now_ts:
                    nxt = None
                out[p + 'az'], out[p + 'alt'] = float(az.degrees), float(alt.degrees)
                out[p + 'earth_distance'], out[p + 'sun_distance'] = float(dist.au), r
                out[p + 'mag'] = None if mag is None else round(mag, 2)
                out[p + 'perihelion.unix_epoch.raw'] = nxt
                entry.update({'full_designation': c['designation'], 'mag': out[p + 'mag'],
                              'sun_distance': r, 'perihelion': nxt,
                              'period_years': round(c['period_days'] / 365.25, 2) if c['period_days'] else None,
                              'elements_epoch': c['elements_epoch']})
            comet_list.append(entry)
        if self.comet_cfg:
            out['celestial.comets'] = comet_list
        return out

    def comet_bodies(self) -> Dict[str, Dict[str, Any]]:
        """The loaded comets, for _SkyAtlas: {tag: {'body', 'orbit', 'label', 'g', 'k'}}."""
        with self._lock:
            return {tag: dict(c) for tag, c in self._comets.items()}


##############################################################################
#
# _SkyAtlas -- the Skyfield page's slow data: jsondata/sky.json.
#
# Everything on the Skyfield page that describes today or the year rather
# than this second: each body's rise, transit and set today and the spans
# it is up, the twilight states, the Sun's and Moon's paths, the solar year
# (sunrise, sunset, noon and the three twilights for every day), the
# equation of time, the analemma, the current lunation, the Moon's next
# perigee and apogee, the orrery, and the almanac table's magnitudes,
# distances, elongations, constellations, Jupiter's central meridians and
# Saturn's ring tilt. (The live positions come from almanac.json.)
#
# Computed on its own thread -- the year's twilight search is a few seconds
# on a Raspberry Pi -- every `sky_atlas_refresh` seconds (default 900) and
# at local midnight; the year part only once a day. Writes beside
# almanac.json. Turned off with sky_atlas = false in [SkyfieldLoopData].
#
# "Today" and clock times are the station computer's local time zone, the
# same as WeeWX's own reports.
#
##############################################################################

_ATLAS_BODIES = (
    ('sun', 'Sun'), ('moon', 'Moon'), ('mercury', 'Mercury'), ('venus', 'Venus'),
    ('mars', 'Mars'), ('jupiter', 'Jupiter'), ('saturn', 'Saturn'),
    ('uranus', 'Uranus'), ('neptune', 'Neptune'),
)
_ATLAS_PATH_STEP_S = 600          # Sun and Moon path samples
_SUN_MAG = -26.74


def _local_midnight(ts: float) -> float:
    lt = time.localtime(ts)
    return time.mktime((lt.tm_year, lt.tm_mon, lt.tm_mday, 0, 0, 0, 0, 0, -1))


def _minutes_of_day(ts: Optional[float]) -> Optional[int]:
    if ts is None:
        return None
    lt = time.localtime(ts)
    return lt.tm_hour * 60 + lt.tm_min + (1 if lt.tm_sec >= 30 else 0)


def _jupiter_cml(earth_at_t, jupiter, t) -> Dict[str, float]:
    """Central meridian longitudes of Jupiter, Systems I and II, in degrees:
    the sub-Earth longitude from light-time corrected geometry, with the
    pole from the IAU Working Group on Cartographic Coordinates and the
    System I/II rotation rates of the Explanatory Supplement."""
    import numpy
    astrometric = earth_at_t.observe(jupiter)
    p = astrometric.position.au
    d = (t.tdb - 2451545.0) - astrometric.light_time      # days, at Jupiter
    T = d / 36525.0
    a0 = math.radians(268.056595 - 0.006499 * T)
    d0 = math.radians(64.495303 + 0.002413 * T)
    pole = numpy.array([math.cos(d0) * math.cos(a0), math.cos(d0) * math.sin(a0), math.sin(d0)])
    node = numpy.cross([0.0, 0.0, 1.0], pole)
    node /= numpy.linalg.norm(node)
    y = numpy.cross(pole, node)
    s = -p / numpy.linalg.norm(p)                           # Jupiter -> Earth
    theta = math.degrees(math.atan2(numpy.dot(s, y), numpy.dot(s, node)))
    return {'cml1': (67.1 + 877.900 * d - theta) % 360.0,
            'cml2': (43.3 + 870.270 * d - theta) % 360.0}


def _saturn_ring_tilt(earth_at_t, saturn, t) -> float:
    """Saturnicentric latitude of the Earth on the ring plane, degrees,
    southern negative (Meeus, Astronomical Algorithms, ch. 45)."""
    T = (t.tt - 2451545.0) / 36525.0
    i = math.radians(28.075216 - 0.012998 * T + 0.000004 * T * T)
    node = 169.508470 + 1.394681 * T + 0.000412 * T * T
    lat, lon, _ = earth_at_t.observe(saturn).apparent().frame_latlon(skyfield.framelib.ecliptic_frame)
    return math.degrees(math.asin(math.sin(i) * math.cos(lat.radians) * math.sin(math.radians(lon.degrees - node))
                                  - math.cos(i) * math.sin(lat.radians)))


class _SkyAtlas:

    def __init__(self, svc_dict, target_path: str):
        self.enabled = to_bool(svc_dict.get('sky_atlas', True))
        self.path = os.path.join(os.path.dirname(target_path), 'sky.json')
        try:
            self.refresh_s = max(120.0, float(svc_dict.get('sky_atlas_refresh', 900)))
        except (TypeError, ValueError):
            self.refresh_s = 900.0
        self._stop = threading.Event()
        self._thread = None
        self._year_cache: Dict[str, Any] = {}

    def start(self, sky, geographic, observer, lat, lon, altitude_m, orbits, supermoon_km):
        if not self.enabled or self._thread is not None:
            return
        self.sky, self.geo, self.observer = sky, geographic, observer
        self.lat, self.lon, self.altitude_m = lat, lon, altitude_m
        self.orbits, self.supermoon_km = orbits, supermoon_km
        self._thread = threading.Thread(target=self._run, name='SkyfieldLoopData-atlas', daemon=True)
        self._thread.start()

    def stop(self):
        self._stop.set()

    def _run(self):
        # Let the first loop-packet write and the orbit downloads go first.
        if self._stop.wait(5):
            return
        comets_seen = None
        while not self._stop.is_set():
            t0 = time.time()
            try:
                self.write(t0)
                log.debug("SkyfieldLoopData: wrote %s in %.1fs" % (self.path, time.time() - t0))
            except Exception as e:
                log.error("SkyfieldLoopData: sky atlas failed: %s" % e)
                log_traceback(log.error, "SkyfieldLoopData: ")
            # Sleep until the next refresh or local midnight, whichever is
            # first -- waking early, once, when the comets first load.
            midnight = _local_midnight(time.time()) + 86400 + 5
            deadline = min(t0 + self.refresh_s, midnight)
            while not self._stop.is_set() and time.time() < deadline:
                self._stop.wait(min(30.0, max(1.0, deadline - time.time())))
                names = tuple(sorted(self.orbits.comet_bodies())) if self.orbits.enabled else ()
                if comets_seen is not None and names != comets_seen:
                    break
                comets_seen = names
            comets_seen = tuple(sorted(self.orbits.comet_bodies())) if self.orbits.enabled else ()

    # ------------------------------------------------------------------

    def _ts(self, unix):
        return self.sky.ts.from_datetime(datetime.datetime.fromtimestamp(unix, datetime.timezone.utc))

    def _ts_many(self, unixes):
        ts = self.sky.ts
        return ts.from_datetimes([datetime.datetime.fromtimestamp(u, datetime.timezone.utc) for u in unixes])

    @staticmethod
    def _unix(t) -> float:
        return t.utc_datetime().timestamp()

    def write(self, now: float) -> None:
        data = self.compute(now)
        tmp = self.path + '.tmp'
        with open(tmp, 'w', encoding='utf-8') as fh:
            json.dump(data, fh, separators=(',', ':'))
        os.replace(tmp, self.path)

    def compute(self, now: float) -> Dict[str, Any]:
        sky = self.sky
        day0 = _local_midnight(now)
        day1 = _local_midnight(day0 + 86400 + 3600)        # DST-safe next midnight
        t_now = self._ts(now)
        out: Dict[str, Any] = {
            'generated': now,
            'version': SKYFIELDLOOPDATA_VERSION,
            'station': {'lat': self.lat, 'lon': self.lon, 'alt_m': self.altitude_m},
            'day': {'start': day0, 'end': day1},
            'tz_offset_std_min': -time.timezone // 60,
        }
        out['sky_states'] = self._sky_states(day0, day1)
        bodies = [(key, name, sky.orbs[key] if key in sky.orbs else getattr(sky, key)) for key, name in _ATLAS_BODIES]
        comets = self.orbits.comet_bodies() if self.orbits.enabled else {}
        out['bodies'] = [self._body(key, name, body, day0, day1, t_now) for key, name, body in bodies]
        for tag, c in comets.items():
            out['bodies'].append(self._body(tag, c['label'], c['body'], day0, day1, t_now, comet=c))
        out['paths'] = self._paths(day0, day1)
        out['orrery'] = self._orrery(t_now, comets)
        out['lunation'] = self._lunation(now)
        year = time.localtime(now).tm_year
        key = '%d|%.3f|%.3f' % (year, self.lat, self.lon)
        if self._year_cache.get('key') != key:
            self._year_cache = {'key': key, 'data': self._year(year)}
        out['year'] = self._year_cache['data']
        out['analemma_today'] = self._noon_sun([self._standard_noon(now)])[0]
        return out

    # -- today -------------------------------------------------------------

    def _sky_states(self, day0, day1):
        """[[unix, state], ...]: the state at day0, then each change.
        0 night, 1 astronomical, 2 nautical, 3 civil twilight, 4 day."""
        f = skyfield.almanac.dark_twilight_day(self.sky.planets, self.geo)
        t0, t1 = self._ts(day0), self._ts(day1)
        times, states = skyfield.almanac.find_discrete(t0, t1, f)
        out = [[day0, int(f(t0))]]
        out += [[round(self._unix(t), 1), int(s)] for t, s in zip(times, states)]
        return out

    def _body(self, key, name, body, day0, day1, t_now, comet=None):
        sky, obs = self.sky, self.observer
        t0, t1 = self._ts(day0), self._ts(day1)
        rises, r_ok = skyfield.almanac.find_risings(obs, body, t0, t1)
        sets, s_ok = skyfield.almanac.find_settings(obs, body, t0, t1)
        transits = skyfield.almanac.find_transits(obs, body, t0, t1)
        events = sorted([(self._unix(t), 1) for t, ok in zip(rises, r_ok) if ok]
                        + [(self._unix(t), 0) for t, ok in zip(sets, s_ok) if ok])
        # Up at midnight? Judged from the first event, or -- with no event
        # all day -- from the failed searches' own verdict.
        if events:
            up = events[0][1] == 0
        else:
            alt = obs.at(t0).observe(body).apparent().altaz()[0].degrees
            up = alt > -0.5667
        spans, start = [], (day0 if up else None)
        for ts_, kind in events:
            if kind == 1 and start is None:
                start = ts_
            elif kind == 0 and start is not None:
                spans.append([round(start, 1), round(ts_, 1)])
                start = None
        if start is not None:
            spans.append([round(start, 1), day1])
        d: Dict[str, Any] = {
            'key': key, 'name': name, 'comet': comet is not None,
            'rise': next((round(t, 1) for t, k in events if k == 1), None),
            'set': next((round(t, 1) for t, k in events if k == 0), None),
            'transit': round(self._unix(transits[0]), 1) if len(transits) else None,
            'up': spans,
            'up_seconds': round(sum(b - a for a, b in spans)),
            'state': (None if events else ('always_up' if up else 'never_up')),
        }
        e = sky.earth.at(t_now)
        app = e.observe(body).apparent()
        d['dist_au'] = float(app.distance().au)
        try:
            d['constellation'] = self._constellation(app)
        except Exception:
            d['constellation'] = None
        if key != 'sun':
            sun_app = e.observe(sky.sun).apparent()
            elong = app.separation_from(sun_app).degrees
            _, lon_b, _ = app.frame_latlon(skyfield.framelib.ecliptic_frame)
            _, lon_s, _ = sun_app.frame_latlon(skyfield.framelib.ecliptic_frame)
            east = ((lon_b.degrees - lon_s.degrees) % 360.0) < 180.0
            d['elong'] = round(elong, 2)
            d['elong_dir'] = 'E' if east else 'W'
        d['mag'] = self._magnitude(key, body, e, app, t_now, comet)
        if key == 'jupiter':
            d.update({k: round(v, 1) for k, v in _jupiter_cml(e, body, t_now).items()})
        elif key == 'saturn':
            d['ring_tilt'] = round(_saturn_ring_tilt(e, body, t_now), 2)
        elif key == 'moon':
            d['illumination'] = round(100.0 * app.fraction_illuminated(sky.sun), 1)
        return d

    def _magnitude(self, key, body, e, app, t_now, comet):
        if key == 'sun':
            return _SUN_MAG
        if key == 'moon':
            # Allen's phase-angle formula (Astrophysical Quantities), scaled
            # to the Moon's actual distance from the station.
            topo = self.observer.at(t_now).observe(self.sky.moon).apparent()
            phase = abs(topo.phase_angle(self.sky.sun).degrees)
            return round(-12.73 + 0.026 * phase + 4e-9 * phase ** 4
                         + 5.0 * math.log10(topo.distance().km / 385000.0), 2)
        if comet is not None:
            if comet.get('g') is None or comet.get('k') is None:
                return None
            r = float(comet['orbit'].at(t_now).distance().au)
            return round(comet['g'] + 5.0 * math.log10(app.distance().au) + 2.5 * comet['k'] * math.log10(r), 2)
        try:
            import skyfield.magnitudelib       # Skyfield 1.40+; a dash on older versions
            return round(float(skyfield.magnitudelib.planetary_magnitude(e.observe(body))), 2)
        except Exception:
            return None

    _cmap = None
    _cnames = None

    def _constellation(self, position) -> Optional[str]:
        if _SkyAtlas._cmap is None:
            _SkyAtlas._cmap = skyfield.api.load_constellation_map()
            _SkyAtlas._cnames = dict(skyfield.api.load_constellation_names())
        abbr = str(_SkyAtlas._cmap(position))
        return _SkyAtlas._cnames.get(abbr, abbr)

    def _paths(self, day0, day1):
        """The Sun's and Moon's altitude and azimuth across today:
        [[unix, alt, az], ...] every _ATLAS_PATH_STEP_S."""
        n = int((day1 - day0) // _ATLAS_PATH_STEP_S) + 1
        stamps = [day0 + i * _ATLAS_PATH_STEP_S for i in range(n)]
        if stamps[-1] < day1:
            stamps.append(day1)
        t = self._ts_many(stamps)
        out = {}
        for key in ('sun', 'moon'):
            alt, az, _ = self.observer.at(t).observe(getattr(self.sky, key)).apparent().altaz()
            out[key] = [[round(s), round(float(a), 2), round(float(z), 2)] for s, a, z in zip(stamps, alt.degrees, az.degrees)]
        return out

    # -- orrery ------------------------------------------------------------

    def _orrery(self, t_now, comets):
        out = []
        sun = self.sky.sun
        for key, body in [('earth', self.sky.earth)] + [(k, self.sky.orbs[k]) for k, _ in _ATLAS_BODIES[2:]]:
            v = (body - sun).at(t_now)
            lat, lon, dist = v.frame_latlon(skyfield.framelib.ecliptic_frame)
            out.append({'key': key, 'hlon': round(lon.degrees, 2), 'r_au': round(float(dist.au), 4)})
        for tag, c in comets.items():
            v = c['orbit'].at(t_now)
            lat, lon, dist = v.frame_latlon(skyfield.framelib.ecliptic_frame)
            out.append({'key': tag, 'name': c['label'], 'comet': True,
                        'hlon': round(lon.degrees, 2), 'r_au': round(float(dist.au), 4)})
        return out

    # -- lunation ------------------------------------------------------------

    def _lunation(self, now):
        sky, ts = self.sky, self.sky.ts
        f = skyfield.almanac.moon_phases(sky.planets)
        times, phases = skyfield.almanac.find_discrete(self._ts(now - 32 * 86400), self._ts(now + 32 * 86400), f)
        ev = [(self._unix(t), int(p)) for t, p in zip(times, phases)]
        news = [u for u, p in ev if p == 0]
        prev_new = max((u for u in news if u <= now), default=None)
        next_new = min((u for u in news if u > now), default=None)
        out: Dict[str, Any] = {'prev_new': prev_new, 'next_new': next_new, 'phases': []}
        if prev_new is None or next_new is None:
            return out
        out['phases'] = [[round(u, 1), p] for u, p in ev if prev_new <= u <= next_new]
        n = 30
        stamps = [prev_new + (next_new - prev_new) * (i + 0.5) / n for i in range(n)]
        t = self._ts_many(stamps)
        app = sky.earth.at(t).observe(sky.moon).apparent()
        frac = app.fraction_illuminated(sky.sun)
        full = next((u for u, p in ev if p == 2 and prev_new <= u <= next_new), None)
        out['discs'] = [[round(s), round(float(k), 4), 1 if (full is not None and s < full) else 0]
                        for s, k in zip(stamps, frac)]
        # The Moon's next perigee and apogee.
        def moon_km(tt):
            return (sky.moon - sky.earth).at(tt).distance().km
        moon_km.step_days = 1.0
        t0, t1 = self._ts(now), self._ts(now + 29 * 86400)
        tmin, vmin = skyfield.searchlib.find_minima(t0, t1, moon_km)
        tmax, vmax = skyfield.searchlib.find_maxima(t0, t1, moon_km)
        if len(tmin):
            out['perigee'], out['perigee_km'] = round(self._unix(tmin[0]), 1), round(float(vmin[0]))
        if len(tmax):
            out['apogee'], out['apogee_km'] = round(self._unix(tmax[0]), 1), round(float(vmax[0]))
        nf = min((u for u, p in ev if p == 2 and u > now), default=None)
        if nf is not None:
            km = float(moon_km(self._ts(nf)))
            out['next_full'], out['next_full_km'] = round(nf, 1), round(km)
            out['supermoon'] = km < self.supermoon_km
        return out

    # -- the year ------------------------------------------------------------

    @staticmethod
    def _standard_noon(ts):
        """Local STANDARD-time (not daylight-saving) noon on ts's date."""
        import calendar
        lt = time.localtime(ts)
        return calendar.timegm((lt.tm_year, lt.tm_mon, lt.tm_mday, 12, 0, 0)) + time.timezone

    def _noon_sun(self, stamps):
        t = self._ts_many(stamps)
        alt, az, _ = self.observer.at(t).observe(self.sky.sun).apparent().altaz()
        return [[round(s), round(float(a), 3), round(float(z), 3)] for s, a, z in zip(stamps, alt.degrees, az.degrees)]

    def _year(self, year):
        import calendar
        sky, ts = self.sky, self.sky.ts
        y0 = time.mktime((year, 1, 1, 0, 0, 0, 0, 0, -1))
        y1 = time.mktime((year + 1, 1, 1, 0, 0, 0, 0, 0, -1))
        ndays = int(round((y1 - y0) / 86400.0))
        # Every twilight boundary of the year, in one search -- sampled every
        # 15 minutes rather than Skyfield's default hour, so the last short
        # nights before high-latitude summer's twilight-all-night aren't stepped over.
        f = skyfield.almanac.dark_twilight_day(sky.planets, self.geo)
        f.step_days = 1.0 / 96.0
        times, states = skyfield.almanac.find_discrete(self._ts(y0 - 3600), self._ts(y1 + 3600), f)
        prev = int(f(self._ts(y0 - 3600)))
        days = [[None] * 12 for _ in range(ndays)]
        # columns: 0 sunrise, 1 sunset, 2 solar noon, 3 civil dawn, 4 civil dusk,
        #          5 nautical dawn, 6 nautical dusk, 7 astronomical dawn, 8 astronomical dusk
        #          (all minutes of local clock time), 9 the day's brightest and
        #          10 darkest state (0 night .. 4 day, at solar noon and midnight),
        #          11 minutes from sunrise to sunset (correct across clock changes)
        rise_unix: Dict[int, float] = {}
        set_unix: Dict[int, float] = {}
        rising = {1: 7, 2: 5, 3: 3, 4: 0}     # entering state s from below
        setting = {3: 1, 2: 4, 1: 6, 0: 8}    # entering state s from above
        for t, s in zip(times, states):
            u, s = self._unix(t), int(s)
            lt = time.localtime(u)
            if lt.tm_year != year:
                prev = s
                continue
            col = rising.get(s) if s > prev else setting.get(s)
            if col is not None:
                days[lt.tm_yday - 1][col] = _minutes_of_day(u)
                if col == 0:
                    rise_unix[lt.tm_yday - 1] = u
                elif col == 1:
                    set_unix[lt.tm_yday - 1] = u
            prev = s
        transits = skyfield.almanac.find_transits(self.observer, sky.sun, self._ts(y0), self._ts(y1))
        transit_unix = []
        for t in transits:
            u = self._unix(t)
            lt = time.localtime(u)
            if lt.tm_year == year:
                days[lt.tm_yday - 1][2] = _minutes_of_day(u)
                transit_unix.append((lt.tm_yday - 1, u))
        if transit_unix:
            noons = self._ts_many([u for _, u in transit_unix])
            nights = self._ts_many([u - 43080.0 for _, u in transit_unix])   # ~ solar midnight
            for (i, _), hi, lo in zip(transit_unix, f(noons), f(nights)):
                days[i][9], days[i][10] = int(hi), int(lo)
        for i in range(ndays):
            r, s_ = rise_unix.get(i), set_unix.get(i)
            if r is not None and s_ is not None and s_ > r:
                days[i][11] = round((s_ - r) / 60.0)
            elif days[i][10] == 4:
                days[i][11] = 1440
            elif days[i][9] is not None and days[i][9] < 4:
                days[i][11] = 0
        # Equation of time at local standard noon, every day: apparent minus
        # mean solar time (positive = the sundial is ahead of the clock).
        noon0 = calendar.timegm((year, 1, 1, 12, 0, 0)) + time.timezone
        stamps = [noon0 + i * 86400 for i in range(ndays)]
        t = self._ts_many(stamps)
        ha, _, _ = self.observer.at(t).observe(sky.sun).apparent().hadec()
        eot = []
        for s, h in zip(stamps, ha.hours):
            apparent = (float(h) + 12.0) % 24.0
            mean = ((s % 86400) / 3600.0 + self.lon / 15.0) % 24.0
            eot.append(round(((apparent - mean + 12.0) % 24.0 - 12.0) * 60.0, 3))
        return {
            'year': year, 'start': y0, 'days': days,
            'eot_noon0': noon0, 'eot': eot,
            'analemma': self._noon_sun([noon0 + w * 7 * 86400 for w in range(53)]),
        }


class SkyfieldLoopData(StdService):
    """Writes a JSON file of live Skyfield data every `interval` seconds."""

    def __init__(self, engine, config_dict):
        super().__init__(engine, config_dict)

        if not SKYFIELD_AVAILABLE:
            log.warning("SkyfieldLoopData: skyfield library not installed -- "
                        "service disabled, doing nothing.")
            self.enabled = False
            return

        svc_dict = config_dict.get('SkyfieldLoopData', {})
        self.enabled = to_bool(svc_dict.get('enable', True))
        if not self.enabled:
            log.info("SkyfieldLoopData: disabled in weewx.conf, doing nothing.")
            return

        # No hardcoded fallback here on purpose -- any guessed path is
        # wrong for virtually every install except whichever machine this
        # literal was originally written on. install.py's own
        # apply_skyfieldloopdata_merge() always writes this from the
        # site's actual HTML_ROOT during configure(), so an absent
        # target_path means the section was hand-stripped or install.py's
        # merge didn't run -- log clearly and disable rather than writing
        # to a path that doesn't exist on this machine.
        self.target_path = svc_dict.get('target_path', '').strip()
        if not self.target_path:
            log.error("SkyfieldLoopData: no 'target_path' set in [SkyfieldLoopData] "
                      "-- disabling. Re-run the DivumWX installer, or set target_path "
                      "manually to <your DivumWX HTML_ROOT>/jsondata/almanac.json.")
            self.enabled = False
            return

        try:
            self.interval = float(svc_dict.get('interval', 10))
            self.slow_refresh_interval = float(svc_dict.get('slow_refresh_interval', 3600))
        except (TypeError, ValueError) as e:
            log.error("SkyfieldLoopData: bad interval/slow_refresh_interval "
                      "in [SkyfieldLoopData]: %s -- disabling." % e)
            self.enabled = False
            return

        # Station location -- read once here; a moving (GPS) station would
        # need this refreshed per-packet, which this service does not do.
        self.lat = engine.stn_info.latitude_f
        self.lon = engine.stn_info.longitude_f
        altitude_vt = weewx.units.convert(engine.stn_info.altitude_vt, 'meter')
        self.altitude_m = altitude_vt[0]

        # Directory Skyfield caches/downloads its ephemeris kernel into
        # (see _load_sky()). No hardcoded fallback for the same reason
        # target_path has none above -- but unlike target_path this one
        # DOES get a derived default, since install.py doesn't currently
        # write an explicit ephemeris_dir: one directory level above
        # target_path's own directory (i.e. next to jsondata/, inside the
        # DivumWX HTML_ROOT) if not set. Override in [SkyfieldLoopData]
        # if that default isn't writable or isn't wanted there.
        self.ephemeris_dir = svc_dict.get('ephemeris_dir', '').strip() or os.path.join(
            os.path.dirname(os.path.dirname(self.target_path)), 'skyfield-data')

        # Resolved lazily on the first loop packet, not here: loading (and
        # potentially downloading, on first run) the ephemeris kernel is
        # slow enough that doing it inside __init__ would delay the whole
        # weewxd engine's startup, blocking every other service. See
        # _load_sky().
        self.sky = None
        self._observer = None

        self._last_write_ts = 0.0
        self._warned_sky_not_found = False
        self._last_slow_refresh_ts = 0.0
        self._slow_cache: Dict[str, Any] = {}
        self._warned_missing_stars = False
        try:
            self.supermoon_km = float(svc_dict.get('supermoon_km', _SUPERMOON_KM))
        except (TypeError, ValueError):
            self.supermoon_km = _SUPERMOON_KM
        # Satellites and comets (Celestial page) -- started once the
        # ephemeris is loaded; see _CelestialOrbits.
        # weewx-skyfield keeps its own TLE copies beside the station
        # database; if it is installed too, those are reused rather than
        # downloading the same satellites twice.
        sqlite_root = None
        try:
            sqlite_root = config_dict.get('DatabaseTypes', {}).get('SQLite', {}).get('SQLITE_ROOT')
            if sqlite_root and not os.path.isabs(sqlite_root):
                sqlite_root = os.path.join(config_dict.get('WEEWX_ROOT', ''), sqlite_root)
        except Exception:
            sqlite_root = None
        self._orbits = _CelestialOrbits(svc_dict, self.ephemeris_dir, [sqlite_root])
        # The Skyfield page's slow data (jsondata/sky.json) -- see _SkyAtlas.
        self._atlas = _SkyAtlas(svc_dict, self.target_path)

        log.info("SkyfieldLoopData: version %s, writing to %s every %.0fs "
                 "(slow fields every %.0fs)"
                 % (SKYFIELDLOOPDATA_VERSION, self.target_path,
                    self.interval, self.slow_refresh_interval))

        self.bind(weewx.NEW_LOOP_PACKET, self.new_loop_packet)

    @staticmethod
    def _lookup_body(kernel, candidates):
        """Try each name in `candidates`, in order, against `kernel`.
        Returns the first match, or raises KeyError with the full list of
        names actually in the kernel -- so a lookup failure is immediately
        diagnosable from the log rather than a bare Skyfield KeyError with
        no context about what WAS available."""
        for name in candidates:
            try:
                return kernel[name]
            except (KeyError, ValueError):
                continue
        try:
            available = sorted(str(n) for n in kernel.names())
        except Exception:
            available = '(could not list kernel contents)'
        raise KeyError(
            "none of %r found in ephemeris kernel; kernel provides: %s"
            % (candidates, available))

    def _load_sky(self):
        """Load this service's OWN Skyfield timescale + ephemeris kernel
        directly, independent of any other extension.

        Formerly (see git history) this reused the weewx-skyfield
        extension's already-loaded ephemeris rather than loading its own,
        specifically to avoid loading the same multi-MB kernel file twice
        in one process. That reuse mechanism (_find_sky(), matching a
        registered almanac by class module/name) was confirmed, on a real
        deployment with otherwise-correct config and permissions, to
        silently never find a match -- weewx-skyfield's actual internal
        module layout didn't match the hardcoded string this checked for
        -- with the only symptom being a DEBUG-level log line invisible
        at normal verbosity and an almanac.json that never got written,
        no error anywhere. weewx-skyfield and weewx-celestial (and the
        DivumWXSkyfield/DivumWXCelestial report pages that depended on
        them) are removed from the installer for now as a result -- see
        install.py's check_all_dependencies() docstring -- so this method
        no longer has another extension's ephemeris to borrow from
        regardless, and loads its own instead. The one-time cost this
        used to specifically avoid (loading a ~17MB kernel) is paid once,
        lazily, on the first loop packet -- not per-packet -- so it's a
        one-off startup cost, not a recurring one.

        May download _EPHEMERIS_FILE into self.ephemeris_dir on first run
        if not already cached there (requires outbound internet access on
        this machine the first time only; cached permanently after that).
        Raises on failure -- caller (new_loop_packet) is responsible for
        catching, logging, and retrying on a later packet.
        """
        os.makedirs(self.ephemeris_dir, exist_ok=True)
        loader = skyfield.api.Loader(self.ephemeris_dir)
        ts = loader.timescale()
        kernel = loader(_EPHEMERIS_FILE)

        bodies = {name: self._lookup_body(kernel, candidates)
                  for name, candidates in _BODY_NAME_CANDIDATES.items()}

        sky = _Sky(ts, kernel, bodies)
        # One hand-entered star (see _PROXIMA_CENTAURI) so the
        # almanac.proxima_centauri.* dotted fields are real values rather
        # than null. Same (Star, parallax_mas) tuple shape
        # _compute_dotted_fields() expects from a catalogue.
        star = skyfield.api.Star(**_PROXIMA_CENTAURI)
        sky.stars = {'proxima_centauri': (star, _PROXIMA_CENTAURI['parallax_mas'])}
        return sky

    def new_loop_packet(self, event):
        if not self.enabled:
            return

        now = time.time()
        if now - self._last_write_ts < self.interval:
            return

        if self.sky is None:
            try:
                self.sky = self._load_sky()
            except Exception as e:
                # Escalates from silent DEBUG (the failure mode this
                # replaced -- weewx-skyfield's almanac never being found,
                # with no visible trace -- was confirmed to persist
                # indefinitely on a real deployment) to one visible
                # WARNING with the actual exception, then falls back to
                # DEBUG for every retry after that so it doesn't spam the
                # log once the operator has been told. The most likely
                # cause here is no outbound internet access on this
                # machine for the ephemeris kernel's first-run download
                # (see _load_sky()'s docstring) -- pre-download
                # _EPHEMERIS_FILE into self.ephemeris_dir by hand if so.
                if not self._warned_sky_not_found:
                    log.warning(
                        "SkyfieldLoopData: failed to load ephemeris kernel "
                        "into %s -- almanac.json will NOT be written until "
                        "this resolves. Most likely cause: no outbound "
                        "internet access on this machine for %s's first-run "
                        "download (subsequent runs use the cached copy) -- "
                        "pre-download it into that directory by hand if so. "
                        "Error: %s. This will be logged once; retries after "
                        "this are at DEBUG level only."
                        % (self.ephemeris_dir, _EPHEMERIS_FILE, e))
                    log_traceback(log.warning, "SkyfieldLoopData: ")
                    self._warned_sky_not_found = True
                else:
                    log.debug("SkyfieldLoopData: ephemeris kernel still not "
                              "loaded, skipping this write: %s" % e)
                self.sky = None
                return
            geographic = skyfield.api.wgs84.latlon(
                self.lat, self.lon, elevation_m=self.altitude_m)
            self._observer = self.sky.earth + geographic
            log.info("SkyfieldLoopData: ephemeris kernel loaded -- "
                     "writing %s from here on." % self.target_path)
            self._orbits.start(self.sky, geographic, self._observer)
            self._atlas.start(self.sky, geographic, self._observer, self.lat, self.lon,
                              self.altitude_m, self._orbits, self.supermoon_km)

        self._last_write_ts = now
        try:
            self._write(now)
        except Exception as e:
            log.error("SkyfieldLoopData: failed to compute/write %s: %s"
                      % (self.target_path, e))
            log_traceback(log.error, "SkyfieldLoopData: ")

    def shutDown(self):
        for name in ('_orbits', '_atlas'):
            worker = getattr(self, name, None)
            if worker is not None:
                worker.stop()

    # ------------------------------------------------------------------
    # Computation
    # ------------------------------------------------------------------

    def _write(self, now_ts: float) -> None:
        t = self.sky.ts.now()

        data: Dict[str, Any] = {
            'generated': int(now_ts * 1000),
        }

        if (now_ts - self._last_slow_refresh_ts >= self.slow_refresh_interval
                or not self._slow_cache):
            self._slow_cache = self._compute_slow(now_ts, t)
            # Celestial page events (countdown row) -- kept separate and
            # guarded so a failure here can never cost the dashboard the
            # slow fields above.
            try:
                self._slow_cache.update(self._compute_celestial_events(now_ts, t))
            except Exception as e:
                log.error("SkyfieldLoopData: celestial events failed: %s" % e)
                log_traceback(log.error, "SkyfieldLoopData: ")
            self._last_slow_refresh_ts = now_ts
        data.update(self._slow_cache)

        data.update(self._compute_fast(t))

        data.update(self._compute_dotted_fields(
            now_ts, t, data.get('fullmoon_ts'), data.get('newmoon_ts')))

        # moonPhase/moonphase both need one field from each of the two
        # dicts just merged in (luminance is "fast", moon_age is "slow"),
        # so they're computed here rather than in either _compute_ method.
        data['moonPhase'] = self._moon_phase_bucket_text(data.get('luminance'), data.get('moon_age'))
        data['moonphase'] = self._moon_phase_index_text(data.get('moon_ecliptic_angle'))
        data['almanac.moon.phase_name'] = data['moonphase']

        # Satellites and comets: guarded so a problem there can never cost
        # the dashboard its almanac.
        if self._orbits.enabled:
            try:
                data.update(self._orbits.fields(t, now_ts))
            except Exception as e:
                log.error("SkyfieldLoopData: satellite/comet fields failed: %s" % e)
                log_traceback(log.error, "SkyfieldLoopData: ")

        tmp_path = self.target_path + '.tmp'
        with open(tmp_path, 'w', encoding='utf-8') as f:
            json.dump(data, f, indent=None, separators=(',', ':'))
        os.replace(tmp_path, self.target_path)

    @staticmethod
    def _moon_phase_bucket_text(illumination: Optional[float],
                                 moon_age_days: Optional[float]) -> Optional[str]:
        """Matches almanac.json.tmpl's moonPhase field exactly: the same
        illumination%/moon-age thresholds, bucketed into the same 8 English
        names. The original template pulled these names from each skin's
        [Almanac] lang-file section ($lang['Waxingcrescent'] etc.) for
        localization; a standalone service has no such mechanism to hook
        into, so this always returns the English text. If localized text
        matters here, translate client-side from these fixed English
        labels, or from the moon_age/luminance values directly."""
        if illumination is None or moon_age_days is None:
            return None
        if 2 < illumination < 48 and moon_age_days < 15:
            return "Waxing crescent"
        elif 48 <= illumination <= 52 and moon_age_days < 15:
            return "First quarter"
        elif 52 < illumination < 98 and moon_age_days < 15:
            return "Waxing gibbous"
        elif illumination >= 98:
            return "Full moon"
        elif 52 < illumination < 98 and moon_age_days > 14:
            return "Waning gibbous"
        elif 48 <= illumination <= 52 and moon_age_days > 14:
            return "Last quarter"
        elif 2 < illumination < 48 and moon_age_days > 14:
            return "Waning crescent"
        else:
            return "New moon"

    _MOON_PHASE_NAMES = ("New Moon", "Waxing Crescent", "First Quarter", "Waxing Gibbous",
                         "Full Moon", "Waning Gibbous", "Last Quarter", "Waning Crescent")

    @classmethod
    def _moon_phase_index_text(cls, moon_ecliptic_angle: Optional[float]) -> Optional[str]:
        """Matches almanac.json.tmpl's moonphase field: weewx-skyfield's own
        get_moon_phase_index() (index = round(angle / 360 * 8) % 8) applied
        to the same Sun-Moon ecliptic-longitude difference this file already
        computes as moon_ecliptic_angle -- same angle, same 8-way bucketing,
        same English names it would otherwise have pulled from each skin's
        moon_phases lang-file entry (see the note on moonPhase above)."""
        if moon_ecliptic_angle is None:
            return None
        index = round(moon_ecliptic_angle / 360.0 * 8) % 8
        return cls._MOON_PHASE_NAMES[index]

    def _compute_fast(self, t: skyfield.timelib.Time) -> Dict[str, Any]:
        """Values that meaningfully change second to second: position,
        distance, illumination. Cheap -- direct vector observations, no
        iterative search."""
        sky = self.sky

        earth_now = sky.earth.at(t)
        sun_geo = earth_now.observe(sky.sun).apparent()
        moon_geo = earth_now.observe(sky.moon).apparent()

        sun_topo = self._observer.at(t).observe(sky.sun).apparent()
        moon_topo = self._observer.at(t).observe(sky.moon).apparent()

        sun_alt, sun_az, sun_dist = sun_topo.altaz()
        moon_alt, moon_az, moon_dist = moon_topo.altaz()

        sun_ra, sun_dec, _ = sun_topo.radec()
        moon_ra, moon_dec, _ = moon_topo.radec()
        sun_g_ra, sun_g_dec, _ = sun_geo.radec()
        moon_g_ra, moon_g_dec, _ = moon_geo.radec()

        sun_ecl_lat, sun_ecl_lon, _ = sun_geo.frame_latlon(skyfield.framelib.ecliptic_frame)
        moon_ecl_lat, moon_ecl_lon, _ = moon_geo.frame_latlon(skyfield.framelib.ecliptic_frame)
        moon_ecliptic_angle = (moon_ecl_lon.degrees - sun_ecl_lon.degrees) % 360.0

        illumination = 100.0 * moon_geo.fraction_illuminated(sky.sun)
        obliquity_deg = mean_obliquity(t.tdb) / 3600.0

        # Matches almanac.json.tmpl's sun_None/daylight_str exactly: purely
        # a function of the current sun altitude's sign, not an actual
        # check for polar day/night (that's what the original did too).
        sun_alt_deg = sun_alt.degrees
        if sun_alt_deg < 0:
            sun_none_text, daylight_str = "Always down", "00:00"
        else:
            sun_none_text, daylight_str = "Always up", "24:00"

        # Moon's parallactic angle -- the angle at the Moon between the
        # direction to the zenith and the direction to the celestial pole.
        # This is deliberately the same formula (and the same .hadec(),
        # not the simpler LST-based hour angle used for hour_moon below)
        # as weewx-skyfield's own SkyfieldAlmanacBinder._parallactic_angle,
        # so this value matches what $almanac.moon.parallactic_angle would
        # report for the same instant.
        moon_ha, moon_ha_dec, _ = moon_topo.hadec()
        lat_rad = math.radians(self.lat)
        moon_parallactic_angle = math.degrees(math.atan2(
            math.sin(moon_ha.radians),
            math.tan(lat_rad) * math.cos(moon_ha_dec.radians)
            - math.sin(moon_ha_dec.radians) * math.cos(moon_ha.radians)))

        # Hour angle: local sidereal time minus right ascension.
        # self._observer is sky.earth + geographic (a VectorSum), so reach
        # the underlying wgs84 GeographicPosition -- which is what actually
        # has lst_hours_at() -- through vector_functions rather than
        # assuming self._observer itself is that object.
        geographic = self._observer.vector_functions[-1]
        lst_hours = geographic.lst_hours_at(t)
        hour_sun = (lst_hours * 15.0 - sun_ra.degrees) % 360.0
        hour_moon = (lst_hours * 15.0 - moon_ra.degrees) % 360.0

        # Heliocentric longitude/latitude for every planet (the Sun's own
        # entry conventionally reports Earth's heliocentric position, per
        # the same XEphem convention weewx-skyfield follows -- there is no
        # "Sun as seen from the Sun").
        hlongitudes = {}
        hlatitudes = {}
        sun_now = sky.sun.at(t)
        for name, body in sky.orbs.items():
            if name == 'sun':
                target = sky.earth
            else:
                target = body
            hlat, hlon, _ = sun_now.observe(target).frame_latlon(skyfield.framelib.ecliptic_frame)
            hlongitudes[name] = hlon.degrees
            hlatitudes[name] = hlat.degrees

        return {
            'sun_altitude': sun_alt.degrees,
            'sun_None': sun_none_text,
            'daylight_str': daylight_str,
            'sun_azimuth': sun_az.degrees,
            'sun_distance': sun_dist.km,
            'sun_right_ascension': sun_ra.degrees,
            'sun_declination': sun_dec.degrees,
            'sun_geo_right_ascension': sun_g_ra.degrees,
            'sun_geo_declination': sun_g_dec.degrees,
            'ecliptic_longitude_sun': sun_ecl_lon.degrees % 360.0,
            'ecliptic_latitude_sun': sun_ecl_lat.degrees,

            'moon_altitude': moon_alt.degrees,
            'moon_azimuth': moon_az.degrees,
            'moon_distance': moon_dist.km,
            'moon_right_ascension': moon_ra.degrees,
            'moon_declination': moon_dec.degrees,
            'moon_geo_right_ascension': moon_g_ra.degrees,
            'moon_geo_declination': moon_g_dec.degrees,
            'ecliptic_longitude_moon': moon_ecl_lon.degrees,
            'ecliptic_latitude_moon': moon_ecl_lat.degrees,
            'moon_ecliptic_angle': moon_ecliptic_angle,
            'parallacticAngle': moon_parallactic_angle,
            'luminance': illumination,

            'ecliptic_angle': obliquity_deg,
            'hour_sun': hour_sun,
            'hour_moon': hour_moon,
            'sidereal_time': lst_hours * 15.0,

            'mercury_hlongitude': hlongitudes.get('mercury'),
            'venus_hlongitude': hlongitudes.get('venus'),
            'earth_hlongitude': hlongitudes.get('sun'),  # Earth via the Sun's convention entry
            'mars_hlongitude': hlongitudes.get('mars'),
            'jupiter_hlongitude': hlongitudes.get('jupiter'),
            'saturn_hlongitude': hlongitudes.get('saturn'),
            'uranus_hlongitude': hlongitudes.get('uranus'),
            'neptune_hlongitude': hlongitudes.get('neptune'),
            'pluto_hlongitude': hlongitudes.get('pluto'),

            # --- Added: standardised key names, pure re-emission of the
            # exact same values already computed above under their
            # original names -- no new computation. ---
            'almanac.sun.dec': sun_dec.degrees,
            'almanac.moon.dec': moon_dec.degrees,
            'almanac.sun.ra': sun_ra.degrees,
            'almanac.moon.ra': moon_ra.degrees,
            'almanac.sun.hour_angle': hour_sun,
            'almanac.moon.hour_angle': hour_moon,
            'almanac.sidereal_time': lst_hours * 15.0,
            'almanac.moon.parallactic_angle': moon_parallactic_angle,
            'almanac.moon.ecliptic_angle': moon_ecliptic_angle,
            'almanac.ecliptic_obliquity': obliquity_deg,
            'almanac.mercury.hlongitude': hlongitudes.get('mercury'),
            'almanac.venus.hlongitude': hlongitudes.get('venus'),
            'almanac.earth.hlongitude': hlongitudes.get('sun'),
            'almanac.mars.hlongitude': hlongitudes.get('mars'),
            'almanac.jupiter.hlongitude': hlongitudes.get('jupiter'),
            'almanac.saturn.hlongitude': hlongitudes.get('saturn'),
            'almanac.uranus.hlongitude': hlongitudes.get('uranus'),
            'almanac.neptune.hlongitude': hlongitudes.get('neptune'),
            'almanac.pluto.hlongitude': hlongitudes.get('pluto'),
        }

    def _compute_slow(self, now_ts: float, t: skyfield.timelib.Time) -> Dict[str, Any]:
        """Values worth recomputing only occasionally: rise/set/transit,
        twilight, and the next seasonal/lunar events. Each involves an
        iterative search over a day (rise/set) or up to a year (events),
        meaningfully more expensive than the fast fields above.

        Time-valued fields are emitted the way almanac.json.tmpl emitted
        them: as local-time strings in the same strftime formats that
        template used (e.g. sunrise "04:25:55", fullmoon "28 Aug 04:18"),
        for drop-in compatibility with whatever already consumes that
        file's field names. Each also gets a "<name>_ts" sibling with the
        raw Unix timestamp, for consumers that would rather not re-parse
        a formatted string."""
        sky = self.sky
        observer = self._observer

        sod_ts = startOfDay(now_ts)
        t0 = sky.ts.from_datetime(datetime.datetime.fromtimestamp(sod_ts, datetime.timezone.utc))
        t1 = sky.ts.from_datetime(datetime.datetime.fromtimestamp(sod_ts + 86400, datetime.timezone.utc))

        def rise_set_ts(target, horizon_degrees=None):
            rise_times, rise_ok = skyfield.almanac.find_risings(
                observer, target, t0, t1, horizon_degrees=horizon_degrees)
            set_times, set_ok = skyfield.almanac.find_settings(
                observer, target, t0, t1, horizon_degrees=horizon_degrees)
            rise_ts = rise_times[rise_ok][0].utc_datetime().timestamp() \
                if any(rise_ok) else None
            set_ts = set_times[set_ok][0].utc_datetime().timestamp() \
                if any(set_ok) else None
            return rise_ts, set_ts

        def transit_ts(target):
            f = skyfield.almanac.meridian_transits(sky.planets, target,
                                                    observer.vector_functions[-1])
            times, events = skyfield.almanac.find_discrete(t0, t1, f)
            for time_, event_ in zip(times, events):
                if event_ == 1:  # upper (meridian) transit, not antitransit
                    return time_.utc_datetime().timestamp()
            return None

        sun_rise_ts, sun_set_ts = rise_set_ts(sky.sun)
        moon_rise_ts, moon_set_ts = rise_set_ts(sky.moon)
        civil_rise_ts, civil_set_ts = rise_set_ts(sky.sun, horizon_degrees=-6.0)
        nautical_rise_ts, nautical_set_ts = rise_set_ts(sky.sun, horizon_degrees=-12.0)
        astro_rise_ts, astro_set_ts = rise_set_ts(sky.sun, horizon_degrees=-18.0)
        sun_transit_ts = transit_ts(sky.sun)
        moon_transit_ts = transit_ts(sky.moon)

        daylight = None
        daylight_seconds = None
        if sun_rise_ts is not None and sun_set_ts is not None:
            daylight_seconds = sun_set_ts - sun_rise_ts
            daylight = time.strftime('%H:%M', time.gmtime(daylight_seconds))

        # Day/night flags for the three horizon definitions -- "day" if the
        # current instant falls between that horizon's rise and set today.
        def day_part(rise_ts, set_ts):
            if rise_ts is None or set_ts is None:
                return 'day' if self._is_up_now(t) else 'night'
            return 'day' if rise_ts <= now_ts < set_ts else 'night'

        # Next equinox/solstice (each specific one, and the nearer of the
        # pair, matching almanac.json.tmpl's next_equinox/next_solstice),
        # and next full/new/quarter moon.
        season_events = self._find_events(
            skyfield.almanac.seasons(sky.planets), t, SEASON_LABELS)
        moon_events = self._find_events(
            skyfield.almanac.moon_phases(sky.planets), t, MOON_PHASE_LABELS)
        next_equinox = self._nearest(season_events.get('vernal_equinox'),
                                     season_events.get('autumnal_equinox'))
        next_solstice = self._nearest(season_events.get('summer_solstice'),
                                      season_events.get('winter_solstice'))

        # moon_age: days since the *previous* new moon -- searches
        # backward, unlike everything else here, since it's the one field
        # that needs a "previous", not a "next".
        previous_new_moon_ts = self._previous_new_moon_ts(t)
        moon_age = ((now_ts - previous_new_moon_ts) / 86400.0
                    if previous_new_moon_ts is not None else None)

        next_eclipse_ts, next_eclipse_type = self._next_lunar_eclipse(t)

        f = self._fmt_local
        return {
            'hasExtras': True,
            'skyfieldExtras': True,

            'daylight': daylight,

            'sunrise': f(sun_rise_ts, '%H:%M:%S'),
            'sunrise_ts': sun_rise_ts,
            'sunrise_date': f(sun_rise_ts, '%b %d %Y %H:%M:%S'),
            'sunset': f(sun_set_ts, '%H:%M:%S'),
            'sunset_ts': sun_set_ts,
            'sunset_date': f(sun_set_ts, '%b %d %Y %H:%M:%S'),
            'moonrise': f(moon_rise_ts, '%H:%M'),
            'moonrise_ts': moon_rise_ts,
            'moonset': f(moon_set_ts, '%H:%M'),
            'moonset_ts': moon_set_ts,
            'sun_meridian_transit': f(sun_transit_ts, '%H:%M'),
            'sun_meridian_transit_ts': sun_transit_ts,
            'moon_meridian_transit': f(moon_transit_ts, '%H:%M'),
            'moon_meridian_transit_ts': moon_transit_ts,
            'civil_twilight_begin': f(civil_rise_ts, '%H:%M'),
            'civil_twilight_begin_ts': civil_rise_ts,
            'civil_twilight_end': f(civil_set_ts, '%H:%M'),
            'civil_twilight_end_ts': civil_set_ts,
            'nautical_twilight_begin': f(nautical_rise_ts, '%H:%M'),
            'nautical_twilight_begin_ts': nautical_rise_ts,
            'nautical_twilight_end': f(nautical_set_ts, '%H:%M'),
            'nautical_twilight_end_ts': nautical_set_ts,
            'astronomical_twilight_begin': f(astro_rise_ts, '%H:%M'),
            'astronomical_twilight_begin_ts': astro_rise_ts,
            'astronomical_twilight_end': f(astro_set_ts, '%H:%M'),
            'astronomical_twilight_end_ts': astro_set_ts,

            'dayPartCivil': day_part(civil_rise_ts, civil_set_ts),
            'dayPartNautical': day_part(nautical_rise_ts, nautical_set_ts),
            'dayPartNatural': day_part(sun_rise_ts, sun_set_ts),

            'next_vernal_equinox': f(season_events.get('vernal_equinox'), '%d-%b-%Y %H:%M'),
            'next_vernal_equinox_ts': season_events.get('vernal_equinox'),
            'next_autumnal_equinox': f(season_events.get('autumnal_equinox'), '%d-%b-%Y %H:%M'),
            'next_autumnal_equinox_ts': season_events.get('autumnal_equinox'),
            'next_equinox': f(next_equinox, '%d-%b-%Y %H:%M'),
            'next_equinox_ts': next_equinox,
            'next_solstice': f(next_solstice, '%d-%b-%Y %H:%M'),
            'next_solstice_ts': next_solstice,
            # Not in the original template, but harmless additions under
            # their own non-conflicting names: the specific solstice the
            # combined next_solstice resolved to (summer or winter).
            'next_summer_solstice': f(season_events.get('summer_solstice'), '%d-%b-%Y %H:%M'),
            'next_summer_solstice_ts': season_events.get('summer_solstice'),
            'next_winter_solstice': f(season_events.get('winter_solstice'), '%d-%b-%Y %H:%M'),
            'next_winter_solstice_ts': season_events.get('winter_solstice'),

            'fullmoon': f(moon_events.get('full_moon'), '%d %b %H:%M'),
            'fullmoon_ts': moon_events.get('full_moon'),
            'newmoon': f(moon_events.get('new_moon'), '%d %b %H:%M'),
            'newmoon_ts': moon_events.get('new_moon'),
            # Not in the original template; kept under non-conflicting names.
            'next_first_quarter_moon': f(moon_events.get('first_quarter_moon'), '%d %b %H:%M'),
            'next_first_quarter_moon_ts': moon_events.get('first_quarter_moon'),
            'next_last_quarter_moon': f(moon_events.get('last_quarter_moon'), '%d %b %H:%M'),
            'next_last_quarter_moon_ts': moon_events.get('last_quarter_moon'),

            'moon_age': moon_age,

            'next_eclipse': f(next_eclipse_ts, '%d-%b-%Y %H:%M'),
            'next_eclipse_ts': next_eclipse_ts,
            'next_eclipse_type': next_eclipse_type,

            # --- Added: standardised almanac.json key names, requested
            # separately -- pure re-emission of the exact same values
            # already computed above, under additional keys. Nothing
            # else in this file has been changed. ---
            'almanac.next_equinox.unix_epoch.raw': next_equinox,
            'almanac.next_solstice.unix_epoch.raw': next_solstice,
            'almanac.next_eclipse.unix_epoch.raw': next_eclipse_ts,
            'almanac.next_eclipse_kind': next_eclipse_type,
            'almanac.sun.daylight_seconds': daylight_seconds,
            'almanac.sun.next_rising.unix_epoch.raw': sun_rise_ts,
            'almanac.sun.next_setting.unix_epoch.raw': sun_set_ts,
            'almanac.moon.next_rising.unix_epoch.raw': moon_rise_ts,
            'almanac.moon.next_setting.unix_epoch.raw': moon_set_ts,
            'almanac.sun.next_transit.unix_epoch.raw': sun_transit_ts,
            'almanac.moon.next_transit.unix_epoch.raw': moon_transit_ts,
            'almanac(horizon=-6).sun.next_rising.unix_epoch.raw': civil_rise_ts,
            'almanac(horizon=-6).sun.next_setting.unix_epoch.raw': civil_set_ts,
            'almanac(horizon=-12).sun.next_rising.unix_epoch.raw': nautical_rise_ts,
            'almanac(horizon=-12).sun.next_setting.unix_epoch.raw': nautical_set_ts,
            'almanac(horizon=-18).sun.next_rising.unix_epoch.raw': astro_rise_ts,
            'almanac(horizon=-18).sun.next_setting.unix_epoch.raw': astro_set_ts,
            'almanac.moon.age_days': moon_age,
        }

    # ------------------------------------------------------------------
    # Celestial page events (countdown row)
    # ------------------------------------------------------------------

    def _compute_celestial_events(self, now_ts: float, t: skyfield.timelib.Time) -> Dict[str, Any]:
        """Forward-looking events for the Celestial page's countdown row.

        Unlike the rise/set fields in _compute_slow() (which are TODAY's
        events and may already be in the past), everything here is
        strictly after now. Sun events are lists covering the next
        _CELESTIAL_SUN_EVENT_DAYS days so the page can roll a chip on to
        the following event itself between hourly refreshes.

        Field names follow weewx-loopdata's flattened-tag style where
        weewx-celestial already uses one (almanac.next_perihelion...,
        almanac.next_meteor_shower...), and a celestial.* prefix for the
        list-valued fields that have no report-tag equivalent."""
        sky = self.sky
        observer = self._observer
        ts = sky.ts
        out: Dict[str, Any] = {
            'celestial.events_computed': now_ts,
            # Station position, for the page's own geometry (which side of
            # the Moon is lit, and the sky dome's projection in phase 2).
            'celestial.station.latitude': self.lat,
            'celestial.station.longitude': self.lon,
        }

        def stamps(times, ok=None):
            if ok is not None:
                times = times[ok]
            return [x.utc_datetime().timestamp() for x in times]

        # --- Sunrise/sunset and astronomical darkness, next few days ---
        t_end = ts.tt_jd(t.tt + _CELESTIAL_SUN_EVENT_DAYS)
        rise, rise_ok = skyfield.almanac.find_risings(observer, sky.sun, t, t_end)
        sett, sett_ok = skyfield.almanac.find_settings(observer, sky.sun, t, t_end)
        out['celestial.sun.risings'] = stamps(rise, rise_ok)
        out['celestial.sun.settings'] = stamps(sett, sett_ok)
        # Astronomical darkness begins when the Sun sets past -18 deg and
        # ends when it rises back through it. Empty lists = no true
        # darkness in the window (e.g. UK summer) -- the page says so.
        drise, drise_ok = skyfield.almanac.find_risings(
            observer, sky.sun, t, t_end, horizon_degrees=-18.0)
        dset, dset_ok = skyfield.almanac.find_settings(
            observer, sky.sun, t, t_end, horizon_degrees=-18.0)
        out['celestial.astro_dark.starts'] = stamps(dset, dset_ok)
        out['celestial.astro_dark.ends'] = stamps(drise, drise_ok)
        sun_alt = observer.at(t).observe(sky.sun).apparent().altaz()[0].degrees
        out['celestial.astro_dark.now'] = bool(sun_alt < -18.0)

        # --- Earth perihelion / aphelion ---
        t_year = ts.tt_jd(t.tt + _EVENT_SEARCH_DAYS)

        def earth_sun_au(tt):
            return (sky.sun - sky.earth).at(tt).distance().au
        earth_sun_au.step_days = 30
        tmin, _ = skyfield.searchlib.find_minima(t, t_year, earth_sun_au)
        tmax, _ = skyfield.searchlib.find_maxima(t, t_year, earth_sun_au)
        out['almanac.next_perihelion.unix_epoch.raw'] = stamps(tmin)[0] if len(tmin) else None
        out['almanac.next_aphelion.unix_epoch.raw'] = stamps(tmax)[0] if len(tmax) else None

        # --- Next supermoon: first full moon closer than supermoon_km ---
        times, codes = skyfield.almanac.find_discrete(
            t, t_year, skyfield.almanac.moon_phases(sky.planets))
        supermoon_ts = supermoon_km = None
        for time_, code in zip(times, codes):
            if int(code) != 2:
                continue
            km = sky.earth.at(time_).observe(sky.moon).distance().km
            if km < self.supermoon_km:
                supermoon_ts, supermoon_km = time_.utc_datetime().timestamp(), km
                break
        out['almanac.next_supermoon.unix_epoch.raw'] = supermoon_ts
        out['celestial.supermoon.distance_km'] = supermoon_km

        # --- Meteor shower peaks ---
        # Solve sun's J2000 ecliptic longitude == peak longitude: a linear
        # first guess at the mean solar rate, then two Newton steps.
        def sun_lon(tt):
            return sky.earth.at(tt).observe(sky.sun).apparent().ecliptic_latlon()[1].degrees

        lon_now = sun_lon(t)
        rate = 360.0 / 365.2422
        showers = []
        for code, name, lam, zhr, ra, dec in _METEOR_SHOWERS:
            jd = t.tt + ((lam - lon_now) % 360.0) / rate
            for _ in range(2):
                diff = ((lam - sun_lon(ts.tt_jd(jd)) + 180.0) % 360.0) - 180.0
                jd += diff / rate
            peak = ts.tt_jd(jd)
            moon_app = sky.earth.at(peak).observe(sky.moon).apparent()
            showers.append({
                'code': code, 'name': name, 'zhr': zhr,
                'radiant_ra': ra, 'radiant_dec': dec,
                'peak': peak.utc_datetime().timestamp(),
                'moon_illumination': round(100.0 * moon_app.fraction_illuminated(sky.sun), 1),
            })
        showers.sort(key=lambda s: s['peak'])
        out['celestial.meteor_showers'] = showers
        nxt = showers[0] if showers else None
        out['almanac.next_meteor_shower.peak.unix_epoch.raw'] = nxt['peak'] if nxt else None
        out['almanac.next_meteor_shower.label'] = nxt['name'] if nxt else None
        return out

    @staticmethod
    def _fmt_local(ts: Optional[float], fmt: str) -> Optional[str]:
        """Format a Unix timestamp as a local-time string, the same way
        WeeWX's own ValueHelper.format() renders group_time values (via
        time.localtime() + strftime) -- so these match what
        almanac.json.tmpl used to produce, assuming the server's OS
        timezone is set to the station's local timezone (the same
        assumption WeeWX itself makes). Returns None (-> JSON null) if ts
        is None (event not found within the search window)."""
        if ts is None:
            return None
        return time.strftime(fmt, time.localtime(ts))

    @staticmethod
    def _nearest(ts_a: Optional[float], ts_b: Optional[float]) -> Optional[float]:
        """The smaller of two optional timestamps, treating None as +inf --
        i.e. whichever of two future events comes first, tolerating either
        (but not both) being unavailable."""
        candidates = [ts for ts in (ts_a, ts_b) if ts is not None]
        return min(candidates) if candidates else None

    def _previous_new_moon_ts(self, t) -> Optional[float]:
        """The most recent new moon before t (searches backward, unlike
        every other event search in this file, since moon_age is the one
        field that needs a "previous" rather than a "next")."""
        ts = t.ts
        t_start = ts.tt_jd(t.tt - _EVENT_SEARCH_DAYS)
        times, events = skyfield.almanac.find_discrete(
            t_start, t, skyfield.almanac.moon_phases(self.sky.planets))
        new_moon_times = [time_ for time_, code in zip(times, events) if int(code) == 0]
        if not new_moon_times:
            return None
        return new_moon_times[-1].utc_datetime().timestamp()

    def _next_lunar_eclipse(self, t) -> tuple[Optional[float], Optional[str]]:
        """The next lunar eclipse (penumbral, partial, or total) of any
        kind, searching up to _ECLIPSE_SEARCH_DAYS ahead. Returns
        (unix_ts, type_label), or (None, None) if none is found in that
        window.

        Deliberately LUNAR ONLY -- a lunar eclipse is visible from the
        entire night-side hemisphere of Earth at once, so "the next lunar
        eclipse" is a single well-defined global answer, which is exactly
        what skyfield.eclipselib.lunar_eclipses() computes. A solar
        eclipse is a different kind of problem: whether one is visible
        (and whether it's partial, annular, or total) depends on the
        observer's exact position relative to the Moon's shadow path
        across Earth's surface, which needs the umbra/penumbra ground
        track, not just an eclipse-occurs/doesn't-occur time. Skyfield's
        public API doesn't provide that as a simple search the way it
        does for lunar eclipses and the season/moon-phase events already
        computed elsewhere in this file, so it isn't attempted here."""
        ts = t.ts
        t_end = ts.tt_jd(t.tt + _ECLIPSE_SEARCH_DAYS)
        times, kinds, _details = eclipselib.lunar_eclipses(t, t_end, self.sky.planets)
        if len(times) == 0:
            return None, None
        eclipse_ts = times[0].utc_datetime().timestamp()
        try:
            type_label = eclipselib.LUNAR_ECLIPSES[int(kinds[0])] + ' Lunar Eclipse'
        except (IndexError, ValueError):
            type_label = 'Lunar Eclipse'
        return eclipse_ts, type_label

    def _compute_dotted_fields(self, now_ts: float, t: skyfield.timelib.Time,
                               fullmoon_ts: Optional[float], newmoon_ts: Optional[float]) -> Dict[str, Any]:
        """A second, differently-named copy of azimuth/altitude/distance
        for the Sun, Moon, every planet, and a couple of named stars --
        "almanac.<body>.az" style dotted keys, matching a sample the
        dashboard's other loop-data mechanism already produces (this looks
        like weewx-loopdata's own flattened-tag naming convention). Kept
        entirely separate from the flat sun_azimuth/moon_altitude/etc.
        fields above rather than replacing them, since both naming
        schemes are apparently already in use by different consumers.

        earth_distance here is in AU (matching the sample's magnitudes,
        e.g. ~1.0 for the Sun, ~0.0026 for the Moon), unlike this file's
        own sun_distance/moon_distance fields above, which are in km."""
        sky = self.sky
        observer = self._observer

        bodies: Dict[str, Any] = dict(sky.orbs)  # sun, moon, mercury, ... pluto
        for star_name in _NAMED_STARS:
            stars = getattr(sky, 'stars', None)
            entry = stars.get(star_name) if stars else None
            if entry is None:
                if not self._warned_missing_stars:
                    if stars is None:
                        # Expected: no star catalogue is loaded (see
                        # _NAMED_STARS), so these fields are always null.
                        log.debug("SkyfieldLoopData: no star catalogue loaded -- "
                                  "almanac.%s.* is null by design." % star_name)
                    else:
                        log.warning("SkyfieldLoopData: named star '%s' not found in the "
                                    "loaded star catalogue -- almanac.%s.* will be null."
                                    % (star_name, star_name))
                    self._warned_missing_stars = True
                bodies[star_name] = None
            else:
                bodies[star_name] = entry[0]  # (Star, parallax_mas) -> Star

        data: Dict[str, Any] = {
            'current.dateTime.raw': now_ts,
            'almanac.next_full_moon.unix_epoch.raw': fullmoon_ts,
            'almanac.next_new_moon.unix_epoch.raw': newmoon_ts,
        }

        for name, body in bodies.items():
            prefix = 'almanac.%s.' % name
            if body is None:
                data[prefix + 'az'] = None
                data[prefix + 'alt'] = None
                data[prefix + 'earth_distance'] = None
                continue
            apparent = observer.at(t).observe(body).apparent()
            alt, az, dist = apparent.altaz()
            data[prefix + 'az'] = az.degrees
            data[prefix + 'alt'] = alt.degrees
            data[prefix + 'earth_distance'] = dist.au

        # moon.phase: percent illuminated -- the same value as this file's
        # own "luminance" field, under the dotted name too.
        moon_app = sky.earth.at(t).observe(sky.moon).apparent()
        data['almanac.moon.phase'] = 100.0 * moon_app.fraction_illuminated(sky.sun)

        return data

    def _is_up_now(self, t) -> bool:
        """Fallback for a body that neither rises nor sets today (polar
        day/night at high latitude): true if it's above the horizon right
        now."""
        alt, _, _ = self._observer.at(t).observe(self.sky.sun).apparent().altaz()
        return alt.degrees > 0

    @staticmethod
    def _find_events(event_function, t_start, labels: Dict[int, str]) -> Dict[str, Optional[float]]:
        """Search forward from t_start for the next occurrence of each event
        code in `labels`, within _EVENT_SEARCH_DAYS. Returns {label: unix_ts}
        for whichever ones are found (codes outside `labels` are ignored --
        e.g. this lets the same seasons() search serve both equinox codes
        while a caller only wants one of them, without a second search)."""
        ts = t_start.ts
        t_end = ts.tt_jd(t_start.tt + _EVENT_SEARCH_DAYS)
        times, events = skyfield.almanac.find_discrete(t_start, t_end, event_function)
        found: Dict[str, Optional[float]] = {}
        for time_, code in zip(times, events):
            label = labels.get(int(code))
            if label and label not in found:
                found[label] = time_.utc_datetime().timestamp()
            if len(found) == len(labels):
                break
        return found

# coding: utf-8
"""
timelapse.py — WeeWX service for the DivumWX webcam Timelapse modal.

This is deliberately NOT a cron job and NOT a PHP endpoint. WeeWX is
already a long-running process with its own event loop — the same one
that drives the site's existing archive.json/loop.json generation — so
this hooks into that instead of adding another external moving part.

WHAT IT DOES
------------
This maintains five separate video files, each a timelapse of the
webcam sampled at a different density, because a video can't be
re-sampled after the fact the way a set of stored images could:

    day.mp4       one frame every capture_interval (default 60s)
    week.mp4      one frame every week_interval    (default 15 min)
    month.mp4     one frame every month_interval   (default 2 hours)
    year.mp4      one frame every year_interval    (default 1 day)
    alltime.mp4   one frame every alltime_interval (default 1 day)

On every NEW_LOOP_PACKET event (which WeeWX fires far more often than
once a minute), each video is checked independently: if its own
interval has elapsed, the current webcam image is briefly copied to a
scratch file, ffmpeg encodes it into a one-frame clip, and ffmpeg
stream-copies that clip onto the end of that video — fast, no
re-encoding of anything already in it. The scratch files are deleted
immediately afterwards. No individual frame images ever accumulate on
disk for any of the five.

Because a video can't easily have its *beginning* trimmed without
re-encoding the whole thing, none of these are a smooth rolling
window. Instead:

    day     resets at midnight — the finished video is kept as
            day-YYYY-MM-DD.mp4 for keep_days days, then deleted.
    week    resets at the start of each ISO week (Monday) — the
            previous week's video is simply discarded, not archived.
    month   resets on the 1st of each month — discarded, not archived.
    year    resets on 1 January — discarded, not archived.
    alltime never resets — grows for as long as the station runs (at
            the default 1 frame/day that's a very slow, small growth:
            roughly 45 seconds of extra video per year at 8fps).

modalTimelapse.html lets the person switch between these five and just
points a plain <video> element at whichever one is selected.

DEPENDENCY
----------
Requires ffmpeg to be installed and on PATH (or pointed at via
ffmpeg_path below):

    sudo apt install ffmpeg

If it isn't found, this service logs an error at startup and simply
does nothing on each loop packet — it will not crash weewx.

INSTALL
-------
1. Copy this file to your WeeWX user extensions directory (wherever
   your other user/*.py files already live — typically
   /home/weewx/bin/user/timelapse.py or similar).

2. Add a [Timelapse] section to weewx.conf:

    [Timelapse]
        # Path to the DivumWX site folder, relative to HTML_ROOT. Leave
        # blank if DivumWX *is* HTML_ROOT (i.e. weewx's reports land
        # straight into the DivumWX site with nothing in between).
        # Example: if your webcam card and modalTimelapse.html live at
        # HTML_ROOT/divumwx/, set this to "divumwx".
        site_root =
        # Path to the live webcam image, relative to site_root above.
        source_image = img/picam.jpg
        # Path to loop.json (used to skip capturing overnight, when the
        # camera has nothing new to show), relative to site_root.
        loop_json = jsondata/loop.json
        # Where the five videos (and any archived previous days) are
        # written, relative to site_root. Created automatically.
        output_dir = webcam-timelapse
        # Seconds between samples for each video. Match capture_interval
        # to how often the webcam image itself actually changes.
        capture_interval = 60
        week_interval = 900
        month_interval = 7200
        year_interval = 86400
        alltime_interval = 86400
        # Playback speed: frames per second of output video, applied to
        # all five. At the day defaults (60s/frame, 8fps), a full day
        # (~1440 frames) plays back in about 3 minutes.
        output_fps = 8
        # How many previous days' archived videos to keep before the
        # oldest is deleted. Only "day" keeps an archive at all.
        keep_days = 3
        # Only needs changing if ffmpeg isn't on PATH for weewx's user.
        ffmpeg_path = ffmpeg
        # Capture overnight too, instead of pausing between sunset and
        # sunrise (per loop.json's isDay). Off by default. Worth turning on
        # at high latitudes, where the day/night gate can remove a large
        # fraction of the year's footage, or if you want night frames kept
        # simply because the weather-data overlay is still useful after dark.
        capture_at_night = False

3. In weewx.conf's [Engine][[Services]], add user.timelapse.TimelapseService
   to report_services (a comma-separated list already there):

    report_services = weewx.engine.StdPrint, weewx.engine.StdReport, user.timelapse.TimelapseService

4. Restart weewx.

WeeWX's own logs (journalctl -u weewx, or wherever your syslog goes)
will show a "Timelapse service started" line on restart if this loaded
correctly, and an error if ffmpeg or the source image couldn't be found.

NOTE ON UPGRADING FROM THE SINGLE-VIDEO VERSION
------------------------------------------------
An older version of this file maintained one file, timelapse.mp4. That
file is unrelated to and untouched by this version — it will just sit
there unused, and can be deleted once you've confirmed day.mp4 is
building correctly.
"""

try:
    import logging
    log = logging.getLogger(__name__)

    def logdbg(msg):
        log.debug(msg)

    def loginf(msg):
        log.info(msg)

    def logerr(msg):
        log.error(msg)
except ImportError:  # pragma: no cover - older WeeWX (pre-4.x) uses syslog directly
    import syslog

    def _logmsg(level, msg):
        syslog.syslog(level, 'timelapse: %s' % msg)

    def logdbg(msg):
        _logmsg(syslog.LOG_DEBUG, msg)

    def loginf(msg):
        _logmsg(syslog.LOG_INFO, msg)

    def logerr(msg):
        _logmsg(syslog.LOG_ERR, msg)


# (bucket name, config key for its interval, default interval seconds,
#  reset period, whether finished periods are archived rather than discarded)
_BUCKET_DEFS = [
    ('day',     'capture_interval',  60,    'daily',   True),
    ('week',    'week_interval',     900,   'weekly',  False),
    ('month',   'month_interval',    7200,  'monthly', False),
    ('year',    'year_interval',     86400, 'yearly',  False),
    ('alltime', 'alltime_interval',  86400, None,      False),
]


class TimelapseService(StdService):

    def __init__(self, engine, config_dict):
        super(TimelapseService, self).__init__(engine, config_dict)

        tl_dict = config_dict.get('Timelapse', {})

        html_root = config_dict.get('StdReport', {}).get('HTML_ROOT', 'public_html')
        weewx_root = config_dict.get('WEEWX_ROOT', '.')
        if not os.path.isabs(html_root):
            html_root = os.path.join(weewx_root, html_root)

        # DivumWX may sit in a subfolder under weewx's HTML_ROOT rather than
        # being HTML_ROOT itself — site_root (relative, may be blank) points
        # at wherever the actual DivumWX site files (index.html,
        # modalTimelapse.html, img/, etc.) live.
        site_root_rel = tl_dict.get('site_root', '').strip()
        divumwx_root = os.path.join(html_root, site_root_rel) if site_root_rel else html_root

        self.source_image = os.path.join(divumwx_root, tl_dict.get('source_image', 'img/picam.jpg'))

        # loop_json is intentionally NOT required in [Timelapse]: LiveDataService's
        # own [LiveData] json_file setting already names the one loop.json that
        # exists, so an explicit [Timelapse] loop_json is only needed to override
        # that (e.g. pointing this service at a different file). When present,
        # it's resolved relative to divumwx_root like the other [Timelapse]
        # paths; when absent, [LiveData] json_file is used as-is, since that
        # setting is already an absolute path (LiveDataService uses it directly,
        # unjoined to any root) rather than one relative to divumwx_root.
        if 'loop_json' in tl_dict:
            self.loop_json = os.path.join(divumwx_root, tl_dict.get('loop_json'))
        else:
            live_data_json = config_dict.get('LiveData', {}).get('json_file', '').strip()
            self.loop_json = live_data_json if live_data_json else os.path.join(divumwx_root, 'jsondata/loop.json')

        self.output_dir = os.path.join(divumwx_root, tl_dict.get('output_dir', 'webcam-timelapse'))
        self.output_fps = max(1, int(tl_dict.get('output_fps', 8)))
        self.keep_days = int(tl_dict.get('keep_days', 3))
        self.ffmpeg_path = tl_dict.get('ffmpeg_path', 'ffmpeg')

        # Feature request (Alex, beta feedback): at high latitudes the
        # day/night gate removes a large fraction of the year's footage, and
        # with weather data burned into the overlay the night frames are
        # still worth keeping. Defaults to False (preserves existing
        # day-only behaviour) so nothing changes for installs that don't set
        # this explicitly.
        self.capture_at_night = to_bool(tl_dict.get('capture_at_night', False))

        if not os.path.isdir(self.output_dir):
            os.makedirs(self.output_dir)

        self.buckets = []
        for name, interval_key, default_interval, reset_period, keep_archives in _BUCKET_DEFS:
            self.buckets.append({
                'name': name,
                'interval': int(tl_dict.get(interval_key, default_interval)),
                'reset_period': reset_period,
                'keep_archives': keep_archives,
                'keep_count': self.keep_days,  # only 'day' currently archives, so this is the only count that matters
                'last_capture': 0.0,
                'current_period_key': None,
            })

        self.ffmpeg_available = shutil.which(self.ffmpeg_path) is not None
        if not self.ffmpeg_available:
            logerr(
                "Timelapse: '%s' not found on PATH — install it (e.g. sudo apt "
                "install ffmpeg) or set ffmpeg_path in weewx.conf. Capture is "
                "disabled until then; restart weewx once it's installed."
                % self.ffmpeg_path
            )

        self.bind(weewx.NEW_LOOP_PACKET, self.new_loop_packet)
        loginf(
            "Timelapse service started: source=%s output=%s loop_json=%s buckets=%s output_fps=%s ffmpeg=%s capture_at_night=%s"
            % (self.source_image, self.output_dir, self.loop_json,
               ','.join('%s@%ss' % (b['name'], b['interval']) for b in self.buckets),
               self.output_fps, 'found' if self.ffmpeg_available else 'MISSING',
               self.capture_at_night)
        )
        if not os.path.isfile(self.source_image):
            logerr("Timelapse: source image not found at %s (will keep checking)" % self.source_image)
        if not os.path.isfile(self.loop_json):
            logerr(
                "Timelapse: loop_json not found at %s — the day/night check will "
                "fail open (treat it as daytime) until this path resolves, meaning "
                "capture won't actually pause overnight. Check [LiveData] json_file "
                "in weewx.conf, or set loop_json explicitly under [Timelapse]."
                % self.loop_json
            )

    def new_loop_packet(self, event):
        if not self.ffmpeg_available:
            return
        now = time.time()
        due = [b for b in self.buckets if now - b['last_capture'] >= b['interval']]
        if not due:
            return
        if not os.path.isfile(self.source_image):
            return
        if not self.capture_at_night and not self._is_daytime():
            return

        # One copy of the source frame is shared across every bucket that's
        # due this tick, rather than re-reading it once per bucket.
        scratch_frame = os.path.join(self.output_dir, '_capture_tmp.jpg')
        try:
            shutil.copy2(self.source_image, scratch_frame)
        except Exception as e:
            logerr("Timelapse: couldn't read source image: %s" % e)
            return

        try:
            for bucket in due:
                try:
                    self._capture_for_bucket(bucket, now, scratch_frame)
                except Exception as e:  # a bad frame/encode in one bucket shouldn't affect the others, or weewx itself
                    logerr("Timelapse (%s) capture failed: %s" % (bucket['name'], e))
        finally:
            if os.path.exists(scratch_frame):
                try:
                    os.remove(scratch_frame)
                except OSError:
                    pass

    # ---- capture ----------------------------------------------------

    def _capture_for_bucket(self, bucket, now, scratch_frame):
        self._rotate_bucket_if_new_period(bucket, now)

        clip = os.path.join(self.output_dir, '_capture_tmp_%s.mp4' % bucket['name'])
        try:
            self._encode_single_frame(scratch_frame, clip)
            self._append_clip(clip, self._bucket_master_path(bucket))
            bucket['last_capture'] = now
        finally:
            if os.path.exists(clip):
                try:
                    os.remove(clip)
                except OSError:
                    pass

    def _is_daytime(self):
        if not os.path.isfile(self.loop_json):
            return True  # fail open — better an occasional spare night frame than never capturing at all
        try:
            with open(self.loop_json) as f:
                data = json.load(f)
            # Missing isDay key gets the same fail-open treatment as a missing
            # file, not fail-closed: data.get('observations', {}).get('isDay')
            # returns None when the key is absent, and None == 1 is False --
            # that silently disabled capture *permanently* on any loop.json
            # that has no isDay key at all (as opposed to isDay explicitly
            # being 0), which is a much worse failure mode than the
            # documented "fail open" behaviour above for a missing file
            # entirely. See beta report from Alex (Belgium).
            return data.get('observations', {}).get('isDay', 1) == 1
        except Exception:
            return True

    def _encode_single_frame(self, image_path, out_path):
        duration = 1.0 / self.output_fps
        cmd = [
            self.ffmpeg_path, '-y', '-loglevel', 'error',
            '-loop', '1', '-i', image_path,
            '-t', '%.3f' % duration,
            '-r', str(self.output_fps),
            '-pix_fmt', 'yuv420p',
            '-c:v', 'libx264', '-profile:v', 'baseline', '-level', '3.0',
            out_path,
        ]
        subprocess.run(cmd, check=True, stdout=subprocess.DEVNULL, stderr=subprocess.PIPE)

    def _append_clip(self, clip_path, master):
        if not os.path.isfile(master):
            shutil.copy2(clip_path, master)
            return

        list_path = master + '.concat_list.txt'
        tmp_master = master + '.tmp.mp4'
        try:
            # The concat demuxer resolves relative paths in this list file
            # relative to the list file's own directory (i.e. output_dir),
            # not the process's working directory — so these have to be
            # absolute, or every entry ends up with output_dir doubled.
            with open(list_path, 'w') as f:
                f.write("file '%s'\n" % os.path.abspath(master))
                f.write("file '%s'\n" % os.path.abspath(clip_path))
            cmd = [
                self.ffmpeg_path, '-y', '-loglevel', 'error',
                '-f', 'concat', '-safe', '0', '-i', list_path,
                '-c', 'copy', tmp_master,
            ]
            subprocess.run(cmd, check=True, stdout=subprocess.DEVNULL, stderr=subprocess.PIPE)
            os.replace(tmp_master, master)  # atomic — the page never sees a half-written video
        finally:
            for path in (list_path, tmp_master):
                if os.path.exists(path):
                    try:
                        os.remove(path)
                    except OSError:
                        pass

    # ---- period rotation ----------------------------------------------

    def _bucket_master_path(self, bucket):
        return os.path.join(self.output_dir, '%s.mp4' % bucket['name'])

    @staticmethod
    def _period_key(reset_period, now):
        if reset_period is None:
            return None
        t = time.localtime(now)
        if reset_period == 'daily':
            return time.strftime('%Y-%m-%d', t)
        if reset_period == 'weekly':
            return time.strftime('%G-W%V', t)  # ISO year+week — resets each Monday
        if reset_period == 'monthly':
            return time.strftime('%Y-%m', t)
        if reset_period == 'yearly':
            return time.strftime('%Y', t)
        return None

    def _rotate_bucket_if_new_period(self, bucket, now):
        if bucket['reset_period'] is None:  # alltime — never rotates
            return

        key = self._period_key(bucket['reset_period'], now)
        if bucket['current_period_key'] is None:
            bucket['current_period_key'] = key
            return
        if key == bucket['current_period_key']:
            return

        master = self._bucket_master_path(bucket)
        if os.path.isfile(master):
            if bucket['keep_archives']:
                archived = os.path.join(
                    self.output_dir, '%s-%s.mp4' % (bucket['name'], bucket['current_period_key'])
                )
                try:
                    os.replace(master, archived)
                    loginf("Timelapse: archived %s to %s" % (bucket['name'], archived))
                except OSError as e:
                    logerr("Timelapse: couldn't archive %s: %s" % (bucket['name'], e))
            else:
                try:
                    os.remove(master)
                except OSError:
                    pass

        bucket['current_period_key'] = key
        if bucket['keep_archives']:
            self._prune_archives(bucket)

    def _prune_archives(self, bucket):
        pattern = os.path.join(self.output_dir, '%s-*.mp4' % bucket['name'])
        archives = sorted(glob.glob(pattern))
        excess = len(archives) - bucket['keep_count']
        for path in archives[:max(excess, 0)]:
            try:
                os.remove(path)
            except OSError:
                pass


##############################################################################
#
# calc_rain_event -- "rain event" (aka "stormRain") continuous-accumulation
# calculation, called from archive.json.tmpl's "rain" block, and again
# from its "p_rain" block for stations with a second, piezo rain gauge.
#
# Replaces reliance on the station/driver's own native stormRain/eventRain
# fields (and, for the piezo gauge, erain_piezo), whose reset behaviour is
# opaque and hardware/firmware-specific, and was found not to match the
# definition actually wanted here. This is computed instead from the
# plain per-interval archive column each gauge already writes to -- 'rain'
# for the standard gauge, 'hail' for the piezo gauge in THIS deployment's
# schema (see the obs_type parameter below, and the "p_rain" block's own
# comment on why piezo readings live under 'hail' -- a common Ecowitt/
# WeeWX workaround, since the default schema has no dedicated piezo-rain
# pair and 'hail' is otherwise unused on most stations) -- so one
# calculation now covers every station and both gauges, rather than
# needing a separate branch per native field.
#
# DEFINITION:
#   - Start: an event begins accumulating as soon as rain is detected.
#   - Continuation: it keeps accumulating the total as long as rain
#     continues.
#   - Reset/End: the event ends (total resets to zero) once EITHER:
#       (a) the trailing 1-hour window has under 1mm of rain (effectively
#           "no further rainfall"), OR
#       (b) the trailing 24-hour window has under 1mm total AND the most
#           recent hour was itself dry.
#     (b) is logically a strict subset of (a) -- if a full day totals
#     under 1mm, the last hour of that day (being part of it) must too,
#     so (b) can never fire without (a) also firing. It's kept as its own
#     explicit check anyway, both to match the specification exactly as
#     given and as a safety net, in case that reasoning misses some edge
#     case (e.g. a clock/timezone boundary oddity) not anticipated here.
#   - A new event begins tracking from zero as soon as rain resumes after
#     a reset.
#
# USAGE from archive.json.tmpl -- see that template's own "rain" block for
# the full pattern, but in short: only call calc_rain_event() once a cheap
# tag-only check ($span($hour_delta=1).rain.sum.mm / $span($hour_delta=24)
# .rain.sum.mm, both well-covered by WeeWX's own tag system already) has
# established that a rain event is CURRENTLY active. That covers "no event
# right now" -- the common case, for most stations, most of the time --
# without this module ever touching the archive database. This function
# is the more expensive part, only reached mid-event, to pin down exactly
# when that event started:
#
#   #import user.divumwx
#   #set $eventMm, $eventStartTs, $_unusedEndTs = \
#       $user.divumwx.calc_rain_event($db_lookup(), $current.dateTime.raw)
#
# For the piezo gauge, pass obs_type='hail' the same way -- see the
# "p_rain" block in archive.json.tmpl for the full pattern:
#
#   #set $pEventMm, $pEventStartTs, $_pUnusedEndTs = \
#       $user.divumwx.calc_rain_event($db_lookup(), $current.dateTime.raw, 'hail')
#
# NOTE ON CONFIGURABILITY: obs_type is a plain function argument, set
# directly in archive.json.tmpl's own template text -- it is NOT read
# from a weewx.conf setting. This matches how the "p_rain" block already
# hardcoded 'hail'/'hailRate' directly in the template before this change
# (and how $data_binding='divumwx_extras_binding' and similar names are
# hardcoded elsewhere in that same file) -- consistent with this
# codebase's existing convention, and correct because there wasn't
# already a config-driven mechanism for this to plug into: a Cheetah
# template only sees whatever tags CheetahGenerator's SearchList exposes,
# it doesn't get generic read access to arbitrary weewx.conf sections the
# way a running StdService does via self.config_dict (see e.g.
# AirDensityService's __init__ elsewhere in this file for that pattern).
# Making obs_type a genuine weewx.conf setting instead would need a small
# custom SearchList extension (this file already has two: Colorize,
# TimeSince) to read a weewx.conf section and expose it as a template
# tag -- real, buildable, but new infrastructure, not a one-line change.
# If a future station's piezo readings ever live somewhere other than
# 'hail', the fix today is editing that one argument in
# archive.json.tmpl, not a config file.
#
# TESTING: this was developed against a standalone test suite covering
# continuous rain, dry gaps of varying length, sporadic trace rain, missing
# (None) readings, multiple archive intervals (1/5/10-minute), and a
# multi-day continuous event exercising the adaptive lookback widening --
# see the project's own test notes for the three real bugs that testing
# against realistic (not conveniently-sized synthetic) data caught before
# this shipped: a false reset in a fresh event's own first ~25 minutes, a
# single dry tick right after a rain burst wiping an event that was only
# minutes old, and an eviction-boundary off-by-one that made the "has this
# window really spanned a full hour" check mathematically impossible to
# ever satisfy for evenly-spaced archive records.
#
##############################################################################

RAINEVENT_MM_THRESHOLD = 1.0        # mm -- the reset threshold from the specification
RAINEVENT_ONE_HOUR = 3600
RAINEVENT_ONE_DAY = 86400
RAINEVENT_DEFAULT_LOOKBACK = 7 * RAINEVENT_ONE_DAY   # generous for virtually any real rain event
RAINEVENT_MAX_LOOKBACK = 30 * RAINEVENT_ONE_DAY       # hard cap so a pathological case can't scan forever


def _rainevent_mm(rec, obs_type='rain'):
    """
    A single archive record's precipitation field, converted to
    millimetres. obs_type defaults to 'rain' (the standard tipping-bucket
    gauge) but is parametrised so the same logic covers a piezo rain
    gauge too, wherever this codebase's schema stores it (see
    calc_rain_event's own docstring). Returns 0.0 for missing/None values
    rather than raising, since a rain gauge legitimately reports nothing
    during a dry interval -- that's data, not an error, and this
    function's job is purely unit conversion.
    """
    raw = rec.get(obs_type)
    if raw is None:
        return 0.0
    unit, group = weewx.units.getStandardUnitType(rec['usUnits'], obs_type)
    value = weewx.units.convert((raw, unit, group), 'mm')[0]
    return value if value is not None else 0.0


def _rainevent_scan(records, obs_type='rain'):
    """
    Walks archive records (oldest first) applying the continuous-
    accumulation / dry-reset logic described above. Returns a dict with
    event_mm (float), start_ts (int or None), end_ts (int or None) -- only
    one of start_ts/end_ts is ever populated, mirroring how the original
    stormRain-based code reported "currently raining" vs "most recently
    closed event" as mutually exclusive states.
    """
    event_mm = 0.0
    event_start_ts = None
    last_rain_ts = None

    # Sliding trailing-window sums, maintained with deques so dropping the
    # oldest entry as the window slides forward is O(1), not O(n) the way
    # popping from the front of a plain list would be -- matters here
    # since this can run over a week or more of 5-minute records.
    window_1h = collections.deque()   # each entry: (dateTime, rain_mm)
    window_24h = collections.deque()
    sum_1h = 0.0
    sum_24h = 0.0

    for rec in records:
        dt = rec['dateTime']
        rain_mm = _rainevent_mm(rec, obs_type)

        window_1h.append((dt, rain_mm))
        sum_1h += rain_mm
        # Strict "<", not "<=": evict only once an entry is MORE than an
        # hour old. Using <= evicts an entry the instant it turns exactly
        # 3600s old, which -- for perfectly evenly-spaced archive records
        # (5-minute intervals are typical) -- means the window's span can
        # never actually reach a full 3600s at all; it gets capped one
        # interval short (3300s for 5-minute records) forever, so the
        # "has this window really observed a full hour" check below could
        # never pass. A real bug caught by testing against realistic
        # evenly-spaced data, not synthetic gaps of convenient sizes.
        while window_1h and window_1h[0][0] < dt - RAINEVENT_ONE_HOUR:
            sum_1h -= window_1h.popleft()[1]

        window_24h.append((dt, rain_mm))
        sum_24h += rain_mm
        while window_24h and window_24h[0][0] < dt - RAINEVENT_ONE_DAY:
            sum_24h -= window_24h.popleft()[1]

        if rain_mm > 0:
            if event_start_ts is None:
                event_start_ts = dt
            event_mm += rain_mm
            last_rain_ts = dt
        else:
            # Only ever evaluate the reset condition on a tick where it is
            # NOT currently raining -- a trailing-window *sum* alone can't
            # tell "it just started raining a few minutes ago" apart from
            # "it's been dry with one stray trace reading a few minutes
            # ago"; gating on "this tick itself is dry" is what actually
            # captures "no further rainfall" from the specification.
            #
            # That alone isn't quite enough, though: a single dry/missing
            # tick immediately after a modest rain burst also has a low
            # trailing-hour SUM simply because barely any time has passed,
            # not because it's genuinely been dry for an hour -- the same
            # underlying ambiguity, just one tick removed. So the window
            # also has to have actually SPANNED the full hour (or day) it
            # claims to summarise before its sum is trusted; a window
            # still shorter than that hasn't observed enough real time
            # yet to rule out "still within the same burst".
            hour_is_dry = (dt - window_1h[0][0] >= RAINEVENT_ONE_HOUR) and (sum_1h < RAINEVENT_MM_THRESHOLD)
            day_is_dry = (dt - window_24h[0][0] >= RAINEVENT_ONE_DAY) and (sum_24h < RAINEVENT_MM_THRESHOLD)  # subset of hour_is_dry; see module docstring
            if hour_is_dry or day_is_dry:
                event_mm = 0.0
                event_start_ts = None

    return {
        'event_mm': event_mm,
        'start_ts': event_start_ts,
        'end_ts': (last_rain_ts if event_start_ts is None else None),
    }


def calc_rain_event(manager, now_ts, obs_type='rain', lookback_seconds=RAINEVENT_DEFAULT_LOOKBACK,
                     max_lookback_seconds=RAINEVENT_MAX_LOOKBACK):
    """
    Entry point called from archive.json.tmpl, only when a cheap trailing-
    window check has already established that a rain event is currently
    active (see the template for that fast path) -- this is the more
    expensive call that pins down exactly when it started.

    manager: a weewx.manager.Manager (or subclass) for the binding that
        holds the archive column named by obs_type -- pass $db_lookup()
        from the template.
    now_ts: unix timestamp to treat as "now" -- pass $current.dateTime.raw
        from the template, matching every other "current" calculation in
        archive.json.tmpl, which is anchored to the latest archive
        record's own time rather than literal wall-clock time.
    obs_type: which archive column to read the precipitation depth from.
        Defaults to 'rain' (the standard tipping-bucket gauge). Pass
        'hail' for the piezo rain gauge's event total -- this codebase's
        schema stores piezo rain readings in the 'hail' column (see the
        "p_rain" block in archive.json.tmpl, and its own comment on why),
        not the native erain_piezo field, which -- like stormRain/
        eventRain for the main gauge -- is a hardware/firmware-defined
        counter this function replaces rather than reads.

    Returns (event_mm, start_ts, end_ts) -- see _rainevent_scan()'s
    docstring for what each means. If the scan reaches the very start of
    its lookback window still mid-event, the window is doubled and
    retried (up to max_lookback_seconds) rather than silently
    under-reporting a longer event's true start.
    """
    lookback = lookback_seconds
    while True:
        records = list(manager.genBatchRecords(now_ts - lookback, now_ts))
        result = _rainevent_scan(records, obs_type)
        truncated = (
            result['start_ts'] is not None
            and records
            and result['start_ts'] <= records[0]['dateTime']
        )
        if truncated and lookback < max_lookback_seconds:
            lookback = min(lookback * 2, max_lookback_seconds)
            continue
        return result['event_mm'], result['start_ts'], result['end_ts']
