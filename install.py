"""weewx-DivumWX extension installer.

Structured as a real WeeWX ExtensionInstaller (weecfg.extension).
Single, self-contained file — no separate merge_helpers.py to import.
This file's job:
  1. verify WeeWX version and required dependencies before touching
     anything (see check_all_dependencies below),
  2. declare the pieces WeeWX's own installer machinery already handles
     natively (Services lists — see weewx.all_service_groups),
  3. interactively prompt for anything that can't have a sane invented
     default (secrets, region choices, airport/region codes),
  4. call the merge functions below in order, in the additions-file order.
"""

import importlib.util
import os
import re
import shutil

import weewx  # for weewx.__version__
import weecfg
from weecfg.extension import ExtensionInstaller
from weeutil.weeutil import y_or_n


# =====================================================================
# Pre-flight checks: WeeWX version + required Python packages.
#
# Run once, at the very top of configure(), before anything is written.
#
# NOTE -- this used to also check for the third-party weewx-skyfield /
# weewx-celestial WeeWX extensions, conditional on whether the person
# opted into the DivumWXSkyfield/DivumWXCelestial report pages. Both the
# check and those two report pages are currently removed from this
# installer -- see check_all_dependencies()'s docstring below for why
# and how to re-enable.
# =====================================================================

MIN_WEEWX_VERSION = (5, 0, 0)

REQUIRED_PYTHON_PACKAGES = ['requests', 'skyfield']


def _version_tuple(version_string):
    """'5.4.0' -> (5, 4, 0). Tolerates non-numeric suffixes (e.g. '5.5.0rc1')."""
    parts = []
    for chunk in version_string.split('.')[:3]:
        digits = ''
        for ch in chunk:
            if ch.isdigit():
                digits += ch
            else:
                break
        parts.append(int(digits) if digits else 0)
    while len(parts) < 3:
        parts.append(0)
    return tuple(parts)


def check_weewx_version(printer, min_version=MIN_WEEWX_VERSION):
    """
    Returns True if the running WeeWX is >= min_version. DivumWX (and the
    astronomy skins it depends on) require WeeWX 5's pip-installed layout
    and StdWXCalculate/StdWXXTypes API -- refuses to configure against
    WeeWX 4.x rather than writing config that fails at report-generation
    time in a way that's much harder to diagnose than a clear refusal now.
    """
    running = _version_tuple(weewx.__version__)
    if running < min_version:
        printer.out(
            f"ERROR: DivumWX requires WeeWX {'.'.join(map(str, min_version))} "
            f"or later. This system is running WeeWX {weewx.__version__}. "
            f"Please upgrade WeeWX before installing DivumWX.", level=1)
        return False
    return True


def _module_available(module_name):
    """
    importlib.util.find_spec raises ModuleNotFoundError (rather than
    returning None) when the PARENT package doesn't exist -- e.g.
    find_spec('user.wxskyfield') raises if 'user' itself isn't a package
    yet, which happens on a bare WeeWX install with no extensions at all.
    Wrapped here so every caller gets a plain True/False either way.
    """
    try:
        return importlib.util.find_spec(module_name) is not None
    except ModuleNotFoundError:
        return False


def check_python_dependencies(packages=REQUIRED_PYTHON_PACKAGES):
    """Returns a list of required Python packages NOT importable on this system."""
    return [pkg for pkg in packages if not _module_available(pkg)]


def check_all_dependencies(printer):
    """
    Runs all pre-flight checks and prints ONE consolidated report covering
    every problem found, rather than failing on the first issue -- so the
    person doesn't have to re-run this repeatedly to discover each missing
    piece one at a time.

    HARD requirements (abort install if missing): WeeWX version, the
    Python packages Requests/Skyfield -- DivumWX's own code imports these
    directly, so their absence is a guaranteed crash with no fallback.
    The Skyfield *Python package* is what DivumWX's own SkyfieldLoopData
    service uses directly for the main dashboard's almanac data (moon
    phase, sun events, etc.) -- still required even with the item below.

    NOTE -- weewx-skyfield / weewx-celestial (third-party WeeWX
    extensions) and the DivumWXSkyfield/DivumWXCelestial report pages
    that depended on them are REMOVED from this installer for now (see
    the DIVUMWX_SKYFIELD_/DIVUMWX_CELESTIAL_* merge functions and
    DivumwxInstaller.__init__'s files= manifest, both below, for the
    full removal). Root cause: SkyfieldLoopData's _find_sky() located
    weewx-skyfield's already-loaded ephemeris by reaching into
    weewx.almanac.almanacs and matching an exact class-module string --
    confirmed, on a real deployment with otherwise-correct config and
    permissions, to silently never match (weewx-skyfield's actual
    internal module layout didn't match the hardcoded string), with the
    only symptom being a DEBUG-level log line invisible at normal
    verbosity and an almanac.json that never got written, no error
    anywhere. Re-enable by restoring the install_astro_reports prompt
    and its three call sites in configure() (git-blame/diff against an
    earlier revision of this file to find them) once SkyfieldLoopData
    loads its own ephemeris directly (see divumwx.py's SkyfieldLoopData)
    rather than depending on weewx-skyfield's internals, at which point
    this hard dependency on weewx-skyfield/weewx-celestial goes away
    entirely regardless.

    Returns True unless a HARD requirement is missing.
    """
    ok = check_weewx_version(printer)

    missing_packages = check_python_dependencies()
    if missing_packages:
        ok = False
        printer.out(
            f"ERROR: missing required Python package(s): {', '.join(missing_packages)}. "
            f"Install with: pip install {' '.join(missing_packages)}", level=1)

    return ok


# =====================================================================
# Merge helpers.
# =====================================================================

def as_list(value):
    """
    configobj returns a comma-separated config value as:
      - a plain string, if there's exactly one item
      - a list, if there are 0 or 2+ items (data_services = , -> [])
    Normalize any of these shapes to a list of non-empty, stripped strings.
    """
    if value is None:
        return []
    if isinstance(value, str):
        value = value.strip()
        return [value] if value else []
    # already a list
    return [v.strip() for v in value if v and v.strip()]


DIVUMWX_CALCULATIONS = {
    'vpd': 'software',
    'AirDensity': 'software',
    'ET': 'software',
    'lightning_strike_count': 'prefer_hardware',
    # windrun/beaufort/abs_humidity/GTS/GTSdate/utcoffsetLMT/dayET/ET24/
    # yearGDD/seasonGDD/rain/hail (seen in one sample weewx.conf) are
    # driver-specific settings (GW1000/Ecowitt), not related to DivumWX
    # at all -- deliberately NOT declared here. DivumWX's installer
    # should not enforce or override driver-level calculation choices.
}


def is_fresh_divumwx_install(cfg):
    """
    'Fresh install' = [StdReport][[DivumWXReport]] doesn't exist yet --
    nothing else in this file ever creates that subsection. Kept as the
    single source of truth for fresh-install detection (see configure()
    below); WeeWX's own installer engine merges Services entries BEFORE
    configure() runs on every install, fresh or not, so checking for
    DivumWX's own service entries would always report False and is not
    used here.
    """
    return 'DivumWXReport' not in cfg.get('StdReport', {})


def apply_calculation_merges(cfg, calculations=DIVUMWX_CALCULATIONS, fresh_install=None):
    """
    Mutates cfg['StdWXCalculate']['Calculations'] in place.

    - A key that doesn't exist yet is added outright (no conflict possible).
    - A key that already exists with the SAME value is left alone, reported
      as 'unchanged'.
    - A key that already exists with a DIFFERENT value:
        * fresh_install=True  -> forced to DivumWX's value, reported as 'forced'
        * fresh_install=False -> left as-is, reported as 'conflicts'
      If fresh_install is None, it's auto-detected via is_fresh_divumwx_install(cfg).

    Returns a dict: {'added': {...}, 'unchanged': {...}, 'forced': {...}, 'conflicts': {...}}
    """
    if fresh_install is None:
        fresh_install = is_fresh_divumwx_install(cfg)

    calc = cfg['StdWXCalculate']['Calculations']
    report = {'added': {}, 'unchanged': {}, 'forced': {}, 'conflicts': {}, 'fresh_install': fresh_install}

    for key, wanted in calculations.items():
        if key not in calc:
            calc[key] = wanted
            report['added'][key] = wanted
        elif calc[key] == wanted:
            report['unchanged'][key] = wanted
        elif fresh_install:
            report['forced'][key] = (calc[key], wanted)
            calc[key] = wanted
        else:
            report['conflicts'][key] = (calc[key], wanted)
            # deliberately NOT changed here

    return report


DIVUMWX_STANDALONE_SECTIONS = {
    'AirDensity': {'algorithm': 'simple'},
    'vpd': {'algorithm': 'tetens'},
    'RadiationDays': {'min_sunshine': '120'},
    'Sunduration': {'global_coeff': '1.0', 'B_coeff': '0.06'},
    'LastNonZero': {'algorithm': 'simple'},
}


def _apply_default_only_subsection(parent_section, subsection_name, default_keys):
    """
    Ensure parent_section[subsection_name] exists. For each key in
    default_keys: set it ONLY if missing from the subsection. Existing
    values (however they got there — DivumWX's own prior run, or a
    user hand-edit) are never overwritten.

    Returns a report dict: {'created_subsection': bool, 'defaulted': {...}}
    """
    report = {'created_subsection': False, 'defaulted': {}}

    if subsection_name not in parent_section:
        parent_section[subsection_name] = {}
        report['created_subsection'] = True

    sub = parent_section[subsection_name]
    for key, wanted in default_keys.items():
        if key not in sub:
            sub[key] = wanted
            report['defaulted'][key] = wanted

    return report


def apply_standalone_sections_merge(cfg, sections=DIVUMWX_STANDALONE_SECTIONS):
    """
    Applies the set-once-only policy to each of the standalone top-level
    sections. Returns {section_name: report} for all five.
    """
    return {
        name: _apply_default_only_subsection(cfg, name, keys)
        for name, keys in sections.items()
    }


def _enforce_flat_subsection(section, wanted):
    """
    Sets every key in `wanted` onto `section`, overwriting anything
    different. Returns the list of keys that actually changed. Used for
    structural blocks that must not diverge from the skin's own required
    shape (e.g. DivumWXReport's [[[Defaults]]] blocks below) -- same
    always-enforced policy already used for report Units/Groups.
    """
    changed = []
    for key, wanted_value in wanted.items():
        if section.get(key) != wanted_value:
            section[key] = wanted_value
            changed.append(key)
    return changed


# =====================================================================
# [StdReport][[DivumWXReport]]
# =====================================================================

DIVUMWX_REPORT_UNITS_GROUPS = {
    'group_altitude': 'meter',
    'group_degree_day': 'degree_C_day',
    'group_pressure': 'hPa',
    'group_rain': 'mm',
    'group_rainrate': 'mm_per_hour',
    'group_speed': 'meter_per_second',
    'group_speed2': 'meter_per_second2',
    'group_temperature': 'degree_C',
    # Unit-label overrides -- same [[[[[Groups]]]]] subsection, not a
    # separate block. Quoted because these are literal display strings
    # (e.g. 'in' for inHg), not group-name -> unit-name assignments like
    # the group_* keys above.
    'mbar': '"mbar"',
    'hPa': '"hPa"',
    'inHg': '"in"',
    'kPa': '"kPa"',
    'mmHg': '"mmHg"',
    'mm_per_hour': '"mm"',
    'cm_per_hour': '"cm"',
    'inch_per_hour': '"in"',
    'km_per_hour': '"km/h"',
    'knot': '"kts"',
    'meter_per_second': '"m/s"',
    'mile_per_hour': '"mph"',
}

DIVUMWX_REPORT_LABELS = {
    'day': '" day", " days"',
    'hour': '" hour", " hours"',
    'minute': '" minute", " minutes"',
    'second': '" second", " seconds"',
    'NONE': '""',
}

DIVUMWX_REPORT_GENERIC_LABELS = {
    'txBatteryStatus': 'Transmitter',
    'windBatteryStatus': 'Wind',
    'rainBatteryStatus': 'Rain',
    'outTempBatteryStatus': 'Outside Temperature',
    'inTempBatteryStatus': 'Inside Temperature',
    'consBatteryVoltage': 'Console',
    'heatingVoltage': 'Heating',
    'supplyVoltage': 'Supply',
    'referenceVoltage': 'Reference',
    'rain_today': 'Rain Today',
    'wind': 'Wind',
}

DIVUMWX_REPORT_ORDINATES = ('North, NNE, NE, ENE, East, ESE, SE, SSE, South, '
                             'SSW, SW, WSW, West, WNW, NW, NNW, N/A')

# Must match DIVUMWX_LIVEDATA_UNIT_SYSTEM below -- loop.json and every
# report-generated JSON file (charts.json, archive.json) need to agree on
# one canonical unit system, since the front-end (cardsBundleNew.js etc.)
# does its own display-unit conversion/formatting from a known, fixed
# baseline rather than reading whatever unit system happened to be active
# per station. See apply_divumwx_report_merge()'s docstring for why this
# is enforced unconditionally, and why it's written with an explicit
# in-file weewx.conf comment rather than silently.
DIVUMWX_REPORT_UNIT_SYSTEM = 'METRICWX'

DIVUMWX_REPORT_UNIT_SYSTEM_COMMENT = [
    '',
    '# DO NOT CHANGE THIS VALUE. DivumWX requires unit_system = METRICWX',
    '# here, regardless of this station\'s own configured unit system.',
    '#',
    '# loop.json (via [LiveData], see its own unit_system a little further',
    '# down this file) is hardcoded to METRICWX and always has been. If',
    '# DivumWXReport uses anything else, charts.json and archive.json will',
    '# disagree with loop.json on units for the exact same observations --',
    '# the front-end has no way to reconcile that; it expects one',
    '# consistent baseline throughout and does its own conversion to the',
    '# visitor\'s preferred display units from there.',
    '#',
    '# Concretely: on a station whose own unit_system is US, changing this',
    '# back to US (or removing it) makes every number in charts.json and',
    '# archive.json come through in US units while loop.json stays metric',
    '# -- this is the exact "bizarre things happen" bug reported by a US',
    '# user in beta and fixed by pinning this value.',
    '#',
    '# If DivumWX is ever updated to do its own explicit per-field unit',
    '# conversion in every .tmpl file (rather than relying on one ambient',
    "# report-level baseline), this constraint could be lifted -- but that's",
    '# a substantial rewrite of charts_json.tmpl/archive_json.tmpl, not a',
    '# config change, and hasn\'t happened as of this fix.',
]

DIVUMWX_REPORT_SEARCH_LIST_EXTENSIONS = [
    'user.stats.MyStats', 'user.divumwx.TimeSince', 'user.divumwx_cards.DivumwxCards',
]

# Language codes DivumWX ships a skins/DivumWX/lang/<code>.conf for, in the
# order offered at install time. 'en' is the reference dictionary (see that
# file's own header comment) and is always available even though it's not a
# translation. Adding a new language later means: (1) drop a new
# lang/<code>.conf next to these (en.conf is the template to copy), (2) add
# its files= entry in DivumwxInstaller.__init__ below, (3) add it here.
#
# All 25 below carry the full 267-key set (every card, not just the
# barometer trend phrases) -- verified via ConfigObj parse + Cheetah
# render before being added here.
DIVUMWX_LANG_CHOICES = {
    'en':    'English',
    'en_US': 'English (US)',
    'ar':    'العربية (Arabic)',
    'br':    'Brezhoneg (Breton)',
    'ca':    'Català (Catalan)',
    'cn':    '中文 (Chinese, Simplified)',
    'cy':    'Cymraeg (Welsh)',
    'cz':    'Čeština (Czech)',
    'da':    'Dansk (Danish)',
    'de':    'Deutsch (German)',
    'es':    'Español (Spanish)',
    'eu':    'Euskara (Basque)',
    'fr':    'Français (French)',
    'gr':    'Ελληνικά (Greek)',
    'hi':    'हिन्दी (Hindi)',
    'it':    'Italiano (Italian)',
    'nl':    'Nederlands (Dutch)',
    'no':    'Norsk (Norwegian)',
    'pl':    'Polski (Polish)',
    'pt':    'Português (Portuguese)',
    'ta':    'தமிழ் (Tamil)',
    'th':    'ไทย (Thai)',
    'tr':    'Türkçe (Turkish)',
    'uk':    'Українська (Ukrainian)',
    'ur':    'اردو (Urdu)',
}


def apply_divumwx_report_merge(cfg, html_root, lang=None):
    """
    Mutates cfg['StdReport']['DivumWXReport'] in place, creating it if
    needed.

    IMPORTANT: the real target shape nests Units/Groups THREE levels
    below DivumWXReport, via an intermediate [[[Defaults]]] block --
    [[[Defaults]]][[[[Units]]]][[[[[Groups]]]]] -- not two levels as an
    earlier version of this function assumed. Also now covers
    Labels/Generic/Ordinates (same [[[Defaults]]] block), skin_semantics,
    CheetahGenerator's search_list_extensions and DVMDATA template
    mapping, and Generators.

    skin/enable/HTML_ROOT/skin_semantics: set-once (never overwrite a
    user's deliberate override on reinstall).

    Everything under [[[Defaults]]], plus CheetahGenerator, Generators,
    and unit_system: always enforced -- these are the skin's own required
    wiring, not user-tunable preference (same policy the Units/Groups
    handling already used, and per the skin's own stated reasoning that
    these "must not be changed, to avoid affecting other skins").

    unit_system specifically: WeeWX reports default their unit_system from
    [StdReport]'s top-level setting (which in turn usually follows the
    station's own configured unit system) unless a report overrides it.
    DivumWXReport was never overriding it, so a US-configured station got
    a US-unit-system report -- while [LiveData] (loop.json) has always
    been hardcoded to METRICWX (see DIVUMWX_LIVEDATA_UNIT_SYSTEM below).
    That mismatch is exactly what caused "bizarre things" for US users.
    Enforced unconditionally (not set-once), same as LiveData's, so an
    existing install with a stale/wrong value gets corrected on upgrade
    too, not just on fresh install -- and written with an explicit
    DIVUMWX_REPORT_UNIT_SYSTEM_COMMENT directly above it in weewx.conf
    itself (not just here in the Python source), since this line sits in
    a file people hand-edit and the reasoning needs to be visible right
    where someone would go to change it.
    """
    report = {
        'created_subsection': False,
        'skin_enforced': False,
        'enable_defaulted': False,
        'html_root_set': False,
        'skin_semantics_defaulted': False,
        'unit_system_enforced': False,
        'lang_enforced': False,
        'defaults_enforced': [],
        'cheetah_enforced': [],
        'generators_enforced': False,
    }

    if 'DivumWXReport' not in cfg['StdReport']:
        cfg['StdReport']['DivumWXReport'] = {}
        report['created_subsection'] = True
    dr = cfg['StdReport']['DivumWXReport']

    if dr.get('skin') != 'DivumWX':
        dr['skin'] = 'DivumWX'
        report['skin_enforced'] = True
    if 'enable' not in dr:
        dr['enable'] = 'true'
        report['enable_defaulted'] = True
    if 'HTML_ROOT' not in dr or not dr['HTML_ROOT']:
        dr['HTML_ROOT'] = html_root
        report['html_root_set'] = True
    if 'skin_semantics' not in dr:
        dr['skin_semantics'] = '2'
        report['skin_semantics_defaulted'] = True

    # lang: this is WeeWX's own real [StdReport][[<report>]] lang setting
    # (see https://weewx.com/docs/latest/custom/localization/), not a
    # DivumWX invention -- CheetahGenerator uses it to pick which
    # skins/DivumWX/lang/<code>.conf to merge in as the active [Texts]
    # dictionary for $gettext(...) lookups in the two .tmpl files. Passed
    # in from configure()'s prompt; enforced (not set-once) so re-running
    # the installer is the supported way to change language later, same
    # policy as unit_system just below. A caller that doesn't pass lang at
    # all (e.g. an isolated unit test) leaves whatever's already there
    # untouched rather than forcing a default -- only a real, deliberately
    # chosen code overwrites it.
    if lang is not None and dr.get('lang') != lang:
        dr['lang'] = lang
        report['lang_enforced'] = True

    if dr.get('unit_system') != DIVUMWX_REPORT_UNIT_SYSTEM:
        dr['unit_system'] = DIVUMWX_REPORT_UNIT_SYSTEM
        report['unit_system_enforced'] = True
    # Comment is (re-)applied every run, independent of unit_system_enforced
    # above, so it survives even if some other tool or a manual edit strips
    # comments but leaves the value -- cheap, and there's no reason for it
    # to ever be missing.
    try:
        dr.comments['unit_system'] = list(DIVUMWX_REPORT_UNIT_SYSTEM_COMMENT)
    except AttributeError:
        # cfg isn't a real ConfigObj (e.g. a plain dict in a unit test) --
        # comments aren't representable, harmless to skip.
        pass

    # [[[Defaults]]] -- structural, always enforced
    dr.setdefault('Defaults', {})
    defaults = dr['Defaults']

    defaults.setdefault('Units', {})
    defaults['Units'].setdefault('Groups', {})
    report['defaults_enforced'] += _enforce_flat_subsection(
        defaults['Units']['Groups'], DIVUMWX_REPORT_UNITS_GROUPS)

    defaults.setdefault('Labels', {})
    report['defaults_enforced'] += _enforce_flat_subsection(
        defaults['Labels'], DIVUMWX_REPORT_LABELS)

    defaults.setdefault('Generic', {})
    report['defaults_enforced'] += _enforce_flat_subsection(
        defaults['Generic'], DIVUMWX_REPORT_GENERIC_LABELS)

    defaults.setdefault('Ordinates', {})
    if defaults['Ordinates'].get('directions') != DIVUMWX_REPORT_ORDINATES:
        defaults['Ordinates']['directions'] = DIVUMWX_REPORT_ORDINATES
        report['defaults_enforced'].append('Ordinates.directions')

    # [[[CheetahGenerator]]] -- structural, always enforced
    dr.setdefault('CheetahGenerator', {})
    cg = dr['CheetahGenerator']
    if cg.get('encoding') != 'html_entities':
        cg['encoding'] = 'html_entities'
        report['cheetah_enforced'].append('encoding')
    if cg.get('search_list_extensions') != DIVUMWX_REPORT_SEARCH_LIST_EXTENSIONS:
        cg['search_list_extensions'] = list(DIVUMWX_REPORT_SEARCH_LIST_EXTENSIONS)
        report['cheetah_enforced'].append('search_list_extensions')

    cg.setdefault('DVMDATA', {})
    dvmdata = cg['DVMDATA']
    if dvmdata.get('encoding') != 'html_entities':
        dvmdata['encoding'] = 'html_entities'
        report['cheetah_enforced'].append('DVMDATA.encoding')
    dvmdata.setdefault('AllCharts', {})
    if dvmdata['AllCharts'].get('template') != 'jsondata/charts.json.tmpl':
        dvmdata['AllCharts']['template'] = 'jsondata/charts.json.tmpl'
        report['cheetah_enforced'].append('DVMDATA.AllCharts.template')
    dvmdata.setdefault('WeatherData', {})
    if dvmdata['WeatherData'].get('template') != 'jsondata/archive.json.tmpl':
        dvmdata['WeatherData']['template'] = 'jsondata/archive.json.tmpl'
        report['cheetah_enforced'].append('DVMDATA.WeatherData.template')
    dvmdata.setdefault('Strings', {})
    if dvmdata['Strings'].get('template') != 'jsondata/strings.json.tmpl':
        dvmdata['Strings']['template'] = 'jsondata/strings.json.tmpl'
        report['cheetah_enforced'].append('DVMDATA.Strings.template')

    # [[[Generators]]] -- structural, always enforced
    dr.setdefault('Generators', {})
    if dr['Generators'].get('generator_list') != 'weewx.cheetahgenerator.CheetahGenerator':
        dr['Generators']['generator_list'] = 'weewx.cheetahgenerator.CheetahGenerator'
        report['generators_enforced'] = True

    return report


# =====================================================================
# [StdReport][[DivumWXSkyfield]]
# =====================================================================

DIVUMWX_SKYFIELD_DEFAULTS = {
    'lang': 'en',
    'star_mag_limit': '5.0',
    'star_label_mag': '2.5',
    'constellation_lines': 'true',
}

DIVUMWX_SKYFIELD_SEARCH_LIST_EXTENSIONS = ['user.wxskyfield_sky.SkyfieldSky']


# NOT CALLED FROM configure() AT THIS STAGE -- see check_all_dependencies()'s
# docstring near the top of this file. Kept defined, not deleted, so the fixes
# already reviewed here aren't lost when this gets re-enabled.
def apply_divumwx_skyfield_report_merge(cfg, html_root):
    """
    Mutates cfg['StdReport']['DivumWXSkyfield'] in place. html_root here
    is the FULL resolved path (already including the 'skyfield' segment)
    -- this function doesn't append it itself, the caller controls that.

    skin/enable/HTML_ROOT: set-once.
    lang/star_mag_limit/star_label_mag/constellation_lines: treated as
    user-tunable DISPLAY preferences (set only if missing, never
    overwritten) rather than always-enforced structural fields -- unlike
    Units/Groups elsewhere in this file, nothing marks these as "must
    never change" in the source config, so this is a judgment call;
    revisit if that's wrong.
    CheetahGenerator/CopyGenerator/Generators: structural, always enforced.
    """
    report = {
        'created_subsection': False,
        'skin_enforced': False,
        'enable_defaulted': False,
        'html_root_set': False,
        'defaults_set': [],
        'cheetah_enforced': [],
        'copy_enforced': False,
        'generators_enforced': False,
    }

    if 'DivumWXSkyfield' not in cfg['StdReport']:
        cfg['StdReport']['DivumWXSkyfield'] = {}
        report['created_subsection'] = True
    ds = cfg['StdReport']['DivumWXSkyfield']

    if ds.get('skin') != 'DivumWXSkyfield':
        ds['skin'] = 'DivumWXSkyfield'
        report['skin_enforced'] = True
    if 'enable' not in ds:
        ds['enable'] = 'true'
        report['enable_defaulted'] = True
    if 'HTML_ROOT' not in ds or not ds['HTML_ROOT']:
        ds['HTML_ROOT'] = html_root
        report['html_root_set'] = True

    for key, wanted in DIVUMWX_SKYFIELD_DEFAULTS.items():
        if key not in ds:
            ds[key] = wanted
            report['defaults_set'].append(key)

    ds.setdefault('CheetahGenerator', {})
    cg = ds['CheetahGenerator']
    if cg.get('encoding') != 'html_entities':
        cg['encoding'] = 'html_entities'
        report['cheetah_enforced'].append('encoding')
    if cg.get('search_list_extensions') != DIVUMWX_SKYFIELD_SEARCH_LIST_EXTENSIONS:
        cg['search_list_extensions'] = list(DIVUMWX_SKYFIELD_SEARCH_LIST_EXTENSIONS)
        report['cheetah_enforced'].append('search_list_extensions')
    cg.setdefault('ToDate', {})
    cg['ToDate'].setdefault('index', {})
    if cg['ToDate']['index'].get('template') != 'index.html.tmpl':
        cg['ToDate']['index']['template'] = 'index.html.tmpl'
        report['cheetah_enforced'].append('ToDate.index.template')

    ds.setdefault('CopyGenerator', {})
    wanted_copy_once = ['sky.css', 'sky.js', 'astro-nav.js']
    if ds['CopyGenerator'].get('copy_once') != wanted_copy_once:
        ds['CopyGenerator']['copy_once'] = list(wanted_copy_once)
        report['copy_enforced'] = True

    ds.setdefault('Generators', {})
    # Real list, not a comma-joined string -- assigning a plain str with
    # an embedded comma here writes ambiguously to weewx.conf and gets
    # read back as ONE value containing a literal comma (confirmed via a
    # real weewxd run: 'Unable to instantiate generator... not a
    # package', because get_object() received the whole two-generator
    # string as a single dotted import path). A real list serializes as
    # a genuine multi-value config option, same as copy_once just above.
    wanted_generators = ['weewx.cheetahgenerator.CheetahGenerator', 'weewx.reportengine.CopyGenerator']
    if as_list(ds['Generators'].get('generator_list', [])) != wanted_generators:
        ds['Generators']['generator_list'] = list(wanted_generators)
        report['generators_enforced'] = True

    return report


# =====================================================================
# [StdReport][[DivumWXCelestial]]
# =====================================================================

DIVUMWX_CELESTIAL_SEARCH_LIST_EXTENSIONS = ['user.celestial_sky.CelestialSkyPage']

# Template filename for each ToDate sub-report, in the order they appear
# in the reference config. 5 levels of bracket nesting overall --
# [[DivumWXCelestial]] -> [[[CheetahGenerator]]] -> [[[[ToDate]]]] ->
# [[[[[dome_N]]]]] -- the deepest structure anywhere in this file.
DIVUMWX_CELESTIAL_TODATE_TEMPLATES = {
    'index': 'index.html.tmpl',
    'dome': 'dome-svg.txt.tmpl',
    'dome_1': 'dome-svg-1.txt.tmpl',
    'dome_2': 'dome-svg-2.txt.tmpl',
    'dome_3': 'dome-svg-3.txt.tmpl',
    'dome_4': 'dome-svg-4.txt.tmpl',
    'dome_5': 'dome-svg-5.txt.tmpl',
    'dome_6': 'dome-svg-6.txt.tmpl',
    'dome_7': 'dome-svg-7.txt.tmpl',
    'dome_8': 'dome-svg-8.txt.tmpl',
    'dome_9': 'dome-svg-9.txt.tmpl',
    'pass_chart': 'pass-chart.txt.tmpl',
}

DIVUMWX_CELESTIAL_EXTRAS_STATIC = {
    'version': '8.3.4',
    'refresh_rate': '2',
    'expiration_time': '24',
}


# NOT CALLED FROM configure() AT THIS STAGE -- see check_all_dependencies()'s
# docstring near the top of this file. Kept defined, not deleted, so the fixes
# already reviewed here aren't lost when this gets re-enabled.
def apply_divumwx_celestial_report_merge(cfg, html_root, page_update_pwd=None):
    """
    Mutates cfg['StdReport']['DivumWXCelestial'] in place. html_root is
    the full resolved path (already including the 'celestial' segment).

    page_update_pwd: secret, set-once, prompted by the caller -- same
    treatment as OpenWeatherMap's app_id elsewhere in this file. Never
    invented; left unset (flagged needs_prompt) if not supplied and not
    already present. Stored as a PLAIN string -- no wrapping quote
    characters -- see loop_data_file's comment just below for the exact
    same bug this mirrored (and the reason there must be no f-string
    quote-wrapping here either).

    loop_data_file: fixed relative path -- ../jsondata/almanac.json,
    relative to DivumWXCelestial's own HTML_ROOT (one level up = the
    DivumWX root, matching where SkyfieldLoopData actually writes
    almanac.json). Structural, always enforced. NOT the same file as the
    standalone weewx-celestial extension's own loop_data_file
    (../loop-data.txt) -- that extension's own live-data mechanism is
    explicitly out of scope for DivumWX (confirmed: not required by this
    setup).

    IMPORTANT -- this value must be a bare relative path with NO quote
    characters embedded in the Python string. An earlier version of this
    function wrote "'../jsondata/almanac.json'" (double-quoted Python
    string literal whose *contents* were themselves wrapped in single
    quotes) -- those inner single quotes are not string delimiters, they
    are literal characters that end up IN the config value ConfigObj
    writes to weewx.conf, producing the on-disk line
    `loop_data_file = "'../jsondata/almanac.json'"` and pointing
    CheetahGenerator at a path that doesn't exist (confirmed via a real
    weewxd run: FileNotFoundError on archive.json.tmpl's compile step,
    plus a WeeWX server log entry showing dome DVM template.tmpl
    failures). Do not reintroduce nested quoting here.

    copy_once deliberately excludes 'sky.js' -- confirmed via the actual
    skin archive that DivumWXCelestial/ contains no sky.js file at all.
    Listing it in copy_once (as an earlier reference config did) is a
    bug that would either silently no-op or error depending on
    CopyGenerator's handling of a missing source file.
    """
    report = {
        'created_subsection': False,
        'skin_enforced': False,
        'enable_defaulted': False,
        'html_root_set': False,
        'extras_enforced': [],
        'page_update_pwd_set': False,
        'page_update_pwd_needs_prompt': False,
        'cheetah_enforced': [],
        'todate_templates_enforced': [],
        'copy_enforced': False,
        'generators_enforced': False,
    }

    if 'DivumWXCelestial' not in cfg['StdReport']:
        cfg['StdReport']['DivumWXCelestial'] = {}
        report['created_subsection'] = True
    dc = cfg['StdReport']['DivumWXCelestial']

    if dc.get('skin') != 'DivumWXCelestial':
        dc['skin'] = 'DivumWXCelestial'
        report['skin_enforced'] = True
    if 'enable' not in dc:
        dc['enable'] = 'true'
        report['enable_defaulted'] = True
    if 'HTML_ROOT' not in dc or not dc['HTML_ROOT']:
        dc['HTML_ROOT'] = html_root
        report['html_root_set'] = True
    if 'lang' not in dc:
        dc['lang'] = 'en'

    # [[[Extras]]]
    dc.setdefault('Extras', {})
    extras = dc['Extras']
    report['extras_enforced'] += _enforce_flat_subsection(extras, DIVUMWX_CELESTIAL_EXTRAS_STATIC)
    # Bare relative path, no embedded quote characters -- see the
    # IMPORTANT note in this function's docstring above.
    if extras.get('loop_data_file') != '../jsondata/almanac.json':
        extras['loop_data_file'] = '../jsondata/almanac.json'
        report['extras_enforced'].append('loop_data_file')

    if 'page_update_pwd' not in extras or not extras['page_update_pwd']:
        if page_update_pwd:
            # Plain value, no f-string quote-wrapping -- see docstring.
            extras['page_update_pwd'] = page_update_pwd
            report['page_update_pwd_set'] = True
        else:
            report['page_update_pwd_needs_prompt'] = True

    # [[[CheetahGenerator]]]
    dc.setdefault('CheetahGenerator', {})
    cg = dc['CheetahGenerator']
    if cg.get('encoding') != 'html_entities':
        cg['encoding'] = 'html_entities'
        report['cheetah_enforced'].append('encoding')
    if cg.get('search_list_extensions') != DIVUMWX_CELESTIAL_SEARCH_LIST_EXTENSIONS:
        cg['search_list_extensions'] = list(DIVUMWX_CELESTIAL_SEARCH_LIST_EXTENSIONS)
        report['cheetah_enforced'].append('search_list_extensions')

    cg.setdefault('ToDate', {})
    for section_name, template in DIVUMWX_CELESTIAL_TODATE_TEMPLATES.items():
        cg['ToDate'].setdefault(section_name, {})
        if cg['ToDate'][section_name].get('template') != template:
            cg['ToDate'][section_name]['template'] = template
            report['todate_templates_enforced'].append(section_name)

    # [[[CopyGenerator]]]
    dc.setdefault('CopyGenerator', {})
    wanted_copy_once = ['celestial.css', 'astro-nav.js']
    if dc['CopyGenerator'].get('copy_once') != wanted_copy_once:
        dc['CopyGenerator']['copy_once'] = list(wanted_copy_once)
        report['copy_enforced'] = True

    # [[[Generators]]]
    dc.setdefault('Generators', {})
    # Real list, not a comma-joined string -- see identical fix +
    # rationale in apply_divumwx_skyfield_report_merge() just above.
    wanted_generators = ['weewx.cheetahgenerator.CheetahGenerator', 'weewx.reportengine.CopyGenerator']
    if as_list(dc['Generators'].get('generator_list', [])) != wanted_generators:
        dc['Generators']['generator_list'] = list(wanted_generators)
        report['generators_enforced'] = True

    return report


# NOT CALLED FROM configure() AT THIS STAGE -- see check_all_dependencies()'s
# docstring near the top of this file. Kept defined, not deleted, so the fixes
# already reviewed here aren't lost when this gets re-enabled.
def disable_thirdparty_astronomy_reports(cfg, printer, report_names=('SkyfieldReport', 'CelestialReport')):
    """
    Sets enable = false on the standalone weewx-skyfield/weewx-celestial
    reports -- DivumWX's own DivumWXSkyfield/DivumWXCelestial supersede
    them rather than running both side by side (running both would
    double report-generation work and produce two divergent output
    trees at different HTML_ROOT depths).

    Always enforced, not set-once: if this ran only on first install, a
    later reinstall wouldn't re-disable a report a user had manually
    re-enabled by hand for testing, silently resurrecting the
    duplicate-generation problem. Re-asserting it every run keeps that
    from recurring by surprise.
    """
    changed = []
    for name in report_names:
        section = cfg.get('StdReport', {}).get(name)
        if section is not None and section.get('enable') != 'false':
            section['enable'] = 'false'
            changed.append(name)
    if changed:
        printer.out(f"Disabled third-party report(s) superseded by DivumWX: "
                    f"{', '.join(changed)}", level=1)


# Custom archive fields DivumWX needs, added directly onto the MAIN
# weewx archive table (wx_binding) via ALTER TABLE, rather than a
# separate divumwx_extras database + data binding.
DIVUMWX_EXTRA_COLUMNS = [
    ('aerosol_optical_depth', 'REAL'),
    ('AirDensity', 'REAL'),
    ('cloudcover', 'REAL'),
    ('dust', 'REAL'),
    ('lightning_last_det_time', 'INTEGER'),
    ('alder_pollen', 'REAL'),
    ('birch_pollen', 'REAL'),
    ('olive_pollen', 'REAL'),
    ('grass_pollen', 'REAL'),
    ('mugwort_pollen', 'REAL'),
    ('ragweed_pollen', 'REAL'),
    ('p_rain', 'REAL'),
    ('p_rainRate', 'REAL'),
    ('p_hourRain', 'REAL'),
    ('p_dayRain', 'REAL'),
    ('p_weekRain', 'REAL'),
    ('p_monthRain', 'REAL'),
    ('p_yearRain', 'REAL'),
    ('p_stormRain', 'REAL'),
    ('isRaining', 'REAL'),
    ('hourRain', 'REAL'),
    ('dayRain', 'REAL'),
    ('weekRain', 'REAL'),
    ('monthRain', 'REAL'),
    ('yearRain', 'REAL'),
    ('stormRain', 'REAL'),
    ('sunshine_time', 'REAL'),
    ('sunshine_time_hours', 'REAL'),
    ('is_sunshine', 'REAL'),
    ('threshold', 'REAL'),
    ('vpd', 'REAL'),
    ('pm4_0', 'REAL'),
    ('pm2_5_SDS', 'REAL'),
    ('pm10_0_SDS', 'REAL'),
]


def add_missing_extras_columns(cfg, printer):
    """
    Check the main archive table (wx_binding) for DivumWX's custom
    columns, and add whichever ones are missing. Fully idempotent --
    existing columns are left completely untouched.

    NOTE for uninstall: this is a ONE-WAY operation. weectl extension
    uninstall has no mechanism to drop columns, and older SQLite doesn't
    support DROP COLUMN without a full table rebuild -- see
    divumwx_uninstall_helper.py for how this is documented at removal time.
    """
    import weewx.manager

    try:
        dbmanager = weewx.manager.open_manager_with_config(cfg, 'wx_binding')
    except Exception as e:
        printer.out(f"WARNING: could not open the main archive database to "
                    f"check/add DivumWX's extra columns ({e}). Skipping — "
                    f"columns may need to be added by hand.", level=1)
        return

    try:
        existing_cols = set(dbmanager.connection.columnsOf(dbmanager.table_name))
        added, failed = [], []
        for col_name, col_type in DIVUMWX_EXTRA_COLUMNS:
            if col_name in existing_cols:
                continue
            try:
                dbmanager.add_column(col_name, col_type)
                added.append(col_name)
            except Exception as e:
                failed.append(col_name)
                printer.out(f"WARNING: failed to add column '{col_name}' to the "
                            f"archive table: {e}", level=1)

        if added:
            printer.out(f"Added {len(added)} new column(s) to the main archive "
                        f"table: {', '.join(added)}", level=1)
        else:
            printer.out("All DivumWX extra columns already present in the "
                        "main archive table — nothing to add.", level=2)
        if failed:
            printer.out(f"{len(failed)} column(s) could not be added: "
                        f"{', '.join(failed)}. DivumwxExtrasService will skip "
                        f"writing these fields until they exist.", level=1)
    finally:
        dbmanager.close()


DIVUMWX_DATAINJECT_SOURCES = {
    'source_airquality': {
        'path_suffix': 'jsondata/airquality.txt',
        'json_path': 'current',
        'mapping': {
            'carbon_monoxide': 'co',
            'nitrogen_dioxide': 'no2',
            'sulphur_dioxide': 'so2',
            'ozone': 'o3',
            'ammonia': 'nh3',
            'aerosol_optical_depth': 'aerosol_optical_depth',
            'dust': 'dust',
            'alder_pollen': 'alder_pollen',
            'birch_pollen': 'birch_pollen',
            'olive_pollen': 'olive_pollen',
            'grass_pollen': 'grass_pollen',
            'mugwort_pollen': 'mugwort_pollen',
            'ragweed_pollen': 'ragweed_pollen',
        },
    },
    'source_forecast': {
        'path_suffix': 'jsondata/forecastcard.txt',
        'json_path': 'current',
        'mapping': {
            'cloud_cover': 'cloudcover',
        },
    },
}


def apply_datainject_merge(cfg, html_root=None, sources=DIVUMWX_DATAINJECT_SOURCES):
    """
    Mutates cfg['DataInjectService'] in place, creating source_airquality
    and source_forecast subsections as needed.
    """
    if 'DataInjectService' not in cfg:
        cfg['DataInjectService'] = {}
    dis = cfg['DataInjectService']

    reports = {}
    for source_name, source_def in sources.items():
        report = {
            'created_subsection': False,
            'path_set': False,
            'path_needs_prompt': False,
            'json_path_enforced': False,
            'mapping_enforced': [],
        }

        if source_name not in dis:
            dis[source_name] = {}
            report['created_subsection'] = True
        src = dis[source_name]

        if 'path' not in src or not src['path']:
            if html_root:
                src['path'] = html_root.rstrip('/') + '/' + source_def['path_suffix']
                report['path_set'] = True
            else:
                report['path_needs_prompt'] = True

        if src.get('json_path') != source_def['json_path']:
            src['json_path'] = source_def['json_path']
            report['json_path_enforced'] = True

        if 'mapping' not in src:
            src['mapping'] = {}
        mapping = src['mapping']
        for key, wanted in source_def['mapping'].items():
            if mapping.get(key) != wanted:
                mapping[key] = wanted
                report['mapping_enforced'].append(key)

        reports[source_name] = report

    return reports


DIVUMWX_LIVEDATA_JSON_FILE_SUFFIX = 'jsondata/loop.json'
DIVUMWX_SKYFIELDLOOPDATA_TARGET_SUFFIX = 'jsondata/almanac.json'
DIVUMWX_LIVEDATA_DEFAULT_UPDATE_INTERVAL = '2'
DIVUMWX_LIVEDATA_UNIT_SYSTEM = 'METRICWX'


def apply_livedata_merge(cfg, html_root=None, update_interval=None):
    """Mutates cfg['LiveData'] in place. Returns a report dict."""
    report = {
        'created_section': False,
        'json_file_set': False,
        'json_file_needs_prompt': False,
        'update_interval_set': False,
        'unit_system_enforced': False,
    }

    if 'LiveData' not in cfg:
        cfg['LiveData'] = {}
        report['created_section'] = True
    ld = cfg['LiveData']

    if 'json_file' not in ld or not ld['json_file']:
        if html_root:
            ld['json_file'] = html_root.rstrip('/') + '/' + DIVUMWX_LIVEDATA_JSON_FILE_SUFFIX
            report['json_file_set'] = True
        else:
            report['json_file_needs_prompt'] = True

    if 'update_interval' not in ld:
        ld['update_interval'] = str(update_interval) if update_interval is not None \
            else DIVUMWX_LIVEDATA_DEFAULT_UPDATE_INTERVAL
        report['update_interval_set'] = True

    if ld.get('unit_system') != DIVUMWX_LIVEDATA_UNIT_SYSTEM:
        ld['unit_system'] = DIVUMWX_LIVEDATA_UNIT_SYSTEM
        report['unit_system_enforced'] = True

    return report


def apply_skyfieldloopdata_merge(cfg, html_root=None):
    """
    Mutates cfg['SkyfieldLoopData'] in place. Returns a report dict.

    SkyfieldLoopData is registered in data_services (runs every loop) but
    divumwx.py's own __init__ falls back to a hardcoded literal --
    '/srv/http/html/divumwx/jsondata/almanac.json', apparently a leftover
    dev-machine path -- if [SkyfieldLoopData] target_path isn't set. No
    merge function wrote that section at all before, so every install
    silently inherited that literal, unrelated to the site's actual
    HTML_ROOT, and crashed with FileNotFoundError the moment the service
    tried to write there. almanac.json lives at the DivumWX root (one
    level above skyfield/ and celestial/), same directory LiveData's own
    json_file uses -- matches the comment already in configure().
    """
    report = {
        'created_section': False,
        'target_path_set': False,
        'target_path_needs_prompt': False,
    }

    if 'SkyfieldLoopData' not in cfg:
        cfg['SkyfieldLoopData'] = {}
        report['created_section'] = True
    sld = cfg['SkyfieldLoopData']

    if 'target_path' not in sld or not sld['target_path']:
        if html_root:
            sld['target_path'] = html_root.rstrip('/') + '/' + DIVUMWX_SKYFIELDLOOPDATA_TARGET_SUFFIX
            report['target_path_set'] = True
        else:
            report['target_path_needs_prompt'] = True

    return report


DIVUMWX_CLOUDCOVERAGEOVERRIDE_JSON_FILE_SUFFIX = 'jsondata/cloud_coverage.json'
DIVUMWX_CLOUDCOVERAGEOVERRIDE_DEFAULT_MAX_AGE = '300'


def apply_cloudcoverageoverride_merge(cfg, html_root=None):
    """
    Mutates cfg['CloudCoverageOverride'] in place. Returns a report dict.

    Same shape and same reasoning as apply_skyfieldloopdata_merge() just
    above: CloudCoverageOverrideService is registered in data_services
    (see DIVUMWX_DATA_SERVICES) and needs an explicit json_file pointing
    at the SEPARATE weewx-Cloud_coverage extension's own
    json_output_path (that extension's own config, not DivumWX's --
    this only reads the file, it doesn't produce it). No hardcoded
    fallback is written into divumwx.py itself for the same reason
    SkyfieldLoopData's target_path has none -- a guessed path being
    silently wrong is worse than a clear one-time setup step. Default
    assumes the two extensions share the same jsondata directory, which
    matches every other DivumWX JSON source (loop.json, archive.json,
    almanac.json, etc.) and is the natural place for a webserver-
    readable file like this to live anyway, given the whole point is
    for it to be readable by the dashboard's own client-side cards too.
    """
    report = {
        'created_section': False,
        'json_file_set': False,
        'json_file_needs_prompt': False,
        'max_age_seconds_set': False,
    }

    if 'CloudCoverageOverride' not in cfg:
        cfg['CloudCoverageOverride'] = {}
        report['created_section'] = True
    cco = cfg['CloudCoverageOverride']

    if 'json_file' not in cco or not cco['json_file']:
        if html_root:
            cco['json_file'] = html_root.rstrip('/') + '/' + DIVUMWX_CLOUDCOVERAGEOVERRIDE_JSON_FILE_SUFFIX
            report['json_file_set'] = True
        else:
            report['json_file_needs_prompt'] = True

    if 'max_age_seconds' not in cco:
        cco['max_age_seconds'] = DIVUMWX_CLOUDCOVERAGEOVERRIDE_DEFAULT_MAX_AGE
        report['max_age_seconds_set'] = True

    return report


DIVUMWX_WEATHERAPI_ALERTS_API_TYPE = 'openweather'
DIVUMWX_WEATHERAPI_ALERTS_DEFAULT_POLL_INTERVAL = '1800'
DIVUMWX_WEATHERAPI_ALERTS_PATH_SUFFIX = 'jsondata/openweathermap.txt'


def apply_weatherapi_alerts_merge(cfg, html_root=None, app_id=None, poll_interval=None):
    """Mutates cfg['WeatherAPI']['Alerts'] in place. Returns a report dict."""
    if 'WeatherAPI' not in cfg:
        cfg['WeatherAPI'] = {}
    if 'Alerts' not in cfg['WeatherAPI']:
        cfg['WeatherAPI']['Alerts'] = {}
        created = True
    else:
        created = False
    alerts = cfg['WeatherAPI']['Alerts']

    report = {
        'created_subsection': created,
        'enabled_enforced': False,
        'enabled_disabled_no_app_id': False,
        'api_type_enforced': False,
        'app_id_set': False,
        'app_id_needs_prompt': False,
        'poll_interval_set': False,
        'data_path_set': False,
        'data_path_needs_prompt': False,
    }

    if 'app_id' not in alerts or not alerts['app_id']:
        if app_id:
            alerts['app_id'] = app_id
            report['app_id_set'] = True
        else:
            report['app_id_needs_prompt'] = True

    # Alerts requires OpenWeatherMap credentials to function at all -- if no
    # app_id is (or will be) present, force enabled=False rather than
    # unconditionally forcing True. Without this, an install where the app_id
    # prompt is explicitly left blank (a supported path -- see prompt text)
    # still enabled Alerts, producing continuous HTTP 401s until fixed
    # manually (see beta report, Kjell, Norway).
    have_app_id = bool(alerts.get('app_id'))
    if not have_app_id:
        if alerts.get('enabled') != 'False':
            alerts['enabled'] = 'False'
            report['enabled_disabled_no_app_id'] = True
    elif alerts.get('enabled') != 'True':
        alerts['enabled'] = 'True'
        report['enabled_enforced'] = True
    if alerts.get('api_type') != DIVUMWX_WEATHERAPI_ALERTS_API_TYPE:
        alerts['api_type'] = DIVUMWX_WEATHERAPI_ALERTS_API_TYPE
        report['api_type_enforced'] = True

    if 'poll_interval' not in alerts:
        alerts['poll_interval'] = str(poll_interval) if poll_interval is not None \
            else DIVUMWX_WEATHERAPI_ALERTS_DEFAULT_POLL_INTERVAL
        report['poll_interval_set'] = True

    if 'data_path' not in alerts or not alerts['data_path']:
        if html_root:
            alerts['data_path'] = html_root.rstrip('/') + '/' + DIVUMWX_WEATHERAPI_ALERTS_PATH_SUFFIX
            report['data_path_set'] = True
        else:
            report['data_path_needs_prompt'] = True

    return report


def apply_weatherapi_simple_merge(cfg, section_name, api_type, path_suffix, html_root=None):
    """Mutates cfg['WeatherAPI'][section_name] in place for the "simple mandatory" shape."""
    if 'WeatherAPI' not in cfg:
        cfg['WeatherAPI'] = {}
    if section_name not in cfg['WeatherAPI']:
        cfg['WeatherAPI'][section_name] = {}
        created = True
    else:
        created = False
    sub = cfg['WeatherAPI'][section_name]

    report = {
        'created_subsection': created,
        'enabled_enforced': False,
        'api_type_enforced': False,
        'data_path_set': False,
        'data_path_needs_prompt': False,
    }

    if sub.get('enabled') != 'True':
        sub['enabled'] = 'True'
        report['enabled_enforced'] = True
    if sub.get('api_type') != api_type:
        sub['api_type'] = api_type
        report['api_type_enforced'] = True

    if 'data_path' not in sub or not sub['data_path']:
        if html_root:
            sub['data_path'] = html_root.rstrip('/') + '/' + path_suffix
            report['data_path_set'] = True
        else:
            report['data_path_needs_prompt'] = True

    return report


DIVUMWX_WEATHERAPI_SIMPLE_SECTIONS = {
    'Airquality': {'api_type': 'airquality', 'path_suffix': 'jsondata/airquality.txt'},
    'Earthquakes': {'api_type': 'earthquakes', 'path_suffix': 'jsondata/eq.txt'},
    'Ki': {'api_type': 'ki', 'path_suffix': 'jsondata/ki.txt'},
    'K2': {'api_type': 'k2', 'path_suffix': 'jsondata/k2.txt'},
    'Ovation': {'api_type': 'ovation', 'path_suffix': 'jsondata/ovation.txt'},
}


DIVUMWX_WEATHERAPI_FORECAST_API_TYPE = 'openmeteo'
DIVUMWX_WEATHERAPI_FORECAST_PATH_SUFFIX = 'jsondata/forecastcard.txt'

DIVUMWX_OPENMETEO_MODEL_CHOICES = {
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


def apply_weatherapi_forecast_merge(cfg, html_root=None, forecast_model=None):
    """
    Mutates cfg['WeatherAPI']['Forecast'] in place. Returns a report dict.
    Writes the key as 'forecast_model' -- NOT '&models'. An earlier
    reference weewx.conf sample had a stray '&models' key which
    divumwx.py's WeatherAPIPoller does not read; that was a documentation
    error in the sample, not a code path this function ever produced.
    """
    if 'WeatherAPI' not in cfg:
        cfg['WeatherAPI'] = {}
    if 'Forecast' not in cfg['WeatherAPI']:
        cfg['WeatherAPI']['Forecast'] = {}
        created = True
    else:
        created = False
    fc = cfg['WeatherAPI']['Forecast']

    report = {
        'created_subsection': created,
        'enabled_enforced': False,
        'api_type_enforced': False,
        'data_path_set': False,
        'data_path_needs_prompt': False,
        'forecast_model_set': False,
        'forecast_model_invalid': False,
    }

    if fc.get('enabled') != 'True':
        fc['enabled'] = 'True'
        report['enabled_enforced'] = True
    if fc.get('api_type') != DIVUMWX_WEATHERAPI_FORECAST_API_TYPE:
        fc['api_type'] = DIVUMWX_WEATHERAPI_FORECAST_API_TYPE
        report['api_type_enforced'] = True

    if 'data_path' not in fc or not fc['data_path']:
        if html_root:
            fc['data_path'] = html_root.rstrip('/') + '/' + DIVUMWX_WEATHERAPI_FORECAST_PATH_SUFFIX
            report['data_path_set'] = True
        else:
            report['data_path_needs_prompt'] = True

    if 'forecast_model' not in fc:
        if forecast_model in DIVUMWX_OPENMETEO_MODEL_CHOICES:
            fc['forecast_model'] = forecast_model
            report['forecast_model_set'] = True
        elif forecast_model:
            report['forecast_model_invalid'] = True
            fc['forecast_model'] = ''
        else:
            fc['forecast_model'] = ''
            report['forecast_model_set'] = True

    return report


DIVUMWX_WEATHERAPI_METAR_API_TYPE = 'custom'
DIVUMWX_WEATHERAPI_METAR_URL_TEMPLATE = 'https://aviationweather.gov/api/data/metar?ids={icao_code}&format=json'
DIVUMWX_WEATHERAPI_METAR_DEFAULT_POLL_INTERVAL = '300'
DIVUMWX_WEATHERAPI_METAR_PATH_SUFFIX = 'jsondata/me.txt'


def apply_weatherapi_metar_merge(cfg, html_root=None, icao_code=None, poll_interval=None):
    """Mutates cfg['WeatherAPI']['Metar'] in place. Returns a report dict."""
    if 'WeatherAPI' not in cfg:
        cfg['WeatherAPI'] = {}
    if 'Metar' not in cfg['WeatherAPI']:
        cfg['WeatherAPI']['Metar'] = {}
        created = True
    else:
        created = False
    metar = cfg['WeatherAPI']['Metar']

    report = {
        'created_subsection': created,
        'enabled_enforced': False,
        'api_type_enforced': False,
        'url_set': False,
        'url_needs_prompt': False,
        'poll_interval_set': False,
        'data_path_set': False,
        'data_path_needs_prompt': False,
    }

    if metar.get('enabled') != 'True':
        metar['enabled'] = 'True'
        report['enabled_enforced'] = True
    if metar.get('api_type') != DIVUMWX_WEATHERAPI_METAR_API_TYPE:
        metar['api_type'] = DIVUMWX_WEATHERAPI_METAR_API_TYPE
        report['api_type_enforced'] = True

    if 'url' not in metar or not metar['url']:
        if icao_code:
            metar['url'] = DIVUMWX_WEATHERAPI_METAR_URL_TEMPLATE.format(icao_code=icao_code)
            report['url_set'] = True
        else:
            report['url_needs_prompt'] = True

    if 'poll_interval' not in metar:
        metar['poll_interval'] = str(poll_interval) if poll_interval is not None \
            else DIVUMWX_WEATHERAPI_METAR_DEFAULT_POLL_INTERVAL
        report['poll_interval_set'] = True

    if 'data_path' not in metar or not metar['data_path']:
        if html_root:
            metar['data_path'] = html_root.rstrip('/') + '/' + DIVUMWX_WEATHERAPI_METAR_PATH_SUFFIX
            report['data_path_set'] = True
        else:
            report['data_path_needs_prompt'] = True

    return report


DIVUMWX_WEATHERAPI_FLOOD_API_TYPE = 'flood'
DIVUMWX_WEATHERAPI_FLOOD_PATH_SUFFIX = 'jsondata/flood.txt'


def apply_weatherapi_flood_merge(cfg, html_root=None, in_england=None):
    """Mutates cfg['WeatherAPI']['Flood'] in place. Returns a report dict."""
    if 'WeatherAPI' not in cfg:
        cfg['WeatherAPI'] = {}
    if 'Flood' not in cfg['WeatherAPI']:
        cfg['WeatherAPI']['Flood'] = {}
        created = True
    else:
        created = False
    flood = cfg['WeatherAPI']['Flood']

    report = {
        'created_subsection': created,
        'api_type_enforced': False,
        'enabled_defaulted': False,
        'enabled_needs_prompt': False,
        'data_path_set': False,
        'data_path_needs_prompt': False,
    }

    if flood.get('api_type') != DIVUMWX_WEATHERAPI_FLOOD_API_TYPE:
        flood['api_type'] = DIVUMWX_WEATHERAPI_FLOOD_API_TYPE
        report['api_type_enforced'] = True

    if 'enabled' not in flood:
        if in_england is not None:
            flood['enabled'] = 'True' if in_england else 'False'
            report['enabled_defaulted'] = True
        else:
            report['enabled_needs_prompt'] = True

    if 'data_path' not in flood or not flood['data_path']:
        if html_root:
            flood['data_path'] = html_root.rstrip('/') + '/' + DIVUMWX_WEATHERAPI_FLOOD_PATH_SUFFIX
            report['data_path_set'] = True
        else:
            report['data_path_needs_prompt'] = True

    return report


DIVUMWX_WEATHERAPI_AURORAWATCH_API_TYPE = 'aurorawatch'
DIVUMWX_WEATHERAPI_AURORAWATCH_DEFAULT_POLL_INTERVAL = '120'
DIVUMWX_WEATHERAPI_AURORAWATCH_PATH_SUFFIX = 'jsondata/aurora.txt'


def apply_weatherapi_aurorawatch_merge(cfg, html_root=None, in_northern_hemisphere=None, poll_interval=None):
    """Mutates cfg['WeatherAPI']['AuroraWatch'] in place. Returns a report dict."""
    if 'WeatherAPI' not in cfg:
        cfg['WeatherAPI'] = {}
    if 'AuroraWatch' not in cfg['WeatherAPI']:
        cfg['WeatherAPI']['AuroraWatch'] = {}
        created = True
    else:
        created = False
    aw = cfg['WeatherAPI']['AuroraWatch']

    report = {
        'created_subsection': created,
        'api_type_enforced': False,
        'enabled_defaulted': False,
        'enabled_needs_prompt': False,
        'poll_interval_set': False,
        'data_path_set': False,
        'data_path_needs_prompt': False,
    }

    if aw.get('api_type') != DIVUMWX_WEATHERAPI_AURORAWATCH_API_TYPE:
        aw['api_type'] = DIVUMWX_WEATHERAPI_AURORAWATCH_API_TYPE
        report['api_type_enforced'] = True

    if 'enabled' not in aw:
        if in_northern_hemisphere is not None:
            aw['enabled'] = 'True' if in_northern_hemisphere else 'False'
            report['enabled_defaulted'] = True
        else:
            report['enabled_needs_prompt'] = True

    if 'poll_interval' not in aw:
        aw['poll_interval'] = str(poll_interval) if poll_interval is not None \
            else DIVUMWX_WEATHERAPI_AURORAWATCH_DEFAULT_POLL_INTERVAL
        report['poll_interval_set'] = True

    if 'data_path' not in aw or not aw['data_path']:
        if html_root:
            aw['data_path'] = html_root.rstrip('/') + '/' + DIVUMWX_WEATHERAPI_AURORAWATCH_PATH_SUFFIX
            report['data_path_set'] = True
        else:
            report['data_path_needs_prompt'] = True

    return report


DIVUMWX_WEATHERAPI_HEATALERT_API_TYPE = 'heatalert'
DIVUMWX_WEATHERAPI_HEATALERT_PATH_SUFFIX = 'jsondata/heat.txt'
DIVUMWX_WEATHERAPI_COLDALERT_API_TYPE = 'coldalert'
DIVUMWX_WEATHERAPI_COLDALERT_PATH_SUFFIX = 'jsondata/cold.txt'

DIVUMWX_HEALTH_ALERT_LOCATIONS = {
    'E12000001': 'North East',
    'E12000002': 'North West',
    'E12000003': 'Yorkshire and The Humber',
    'E12000004': 'East Midlands',
    'E12000005': 'West Midlands',
    'E12000006': 'East of England',
    'E12000007': 'London',
    'E12000008': 'South East',
    'E12000009': 'South West',
}

# All 9 codes share this identical 8-character prefix and differ only in
# the final digit -- derived from the dict itself (not a second hardcoded
# copy) so the two can't drift apart. Asserted, not assumed: if someone
# ever edits DIVUMWX_HEALTH_ALERT_LOCATIONS to a shape that breaks this
# (a differently-structured code, a inserted/removed entry), this fails
# loudly at import time rather than silently prompting for a digit that
# reconstructs a nonexistent/wrong code.
_health_alert_codes = list(DIVUMWX_HEALTH_ALERT_LOCATIONS.keys())
assert all(len(c) == 9 and c[-1].isdigit() for c in _health_alert_codes), \
    "DIVUMWX_HEALTH_ALERT_LOCATIONS: expected all 9-char codes ending in a single digit"
DIVUMWX_HEALTH_ALERT_LOCATION_PREFIX = _health_alert_codes[0][:-1]
assert all(c[:-1] == DIVUMWX_HEALTH_ALERT_LOCATION_PREFIX for c in _health_alert_codes), \
    "DIVUMWX_HEALTH_ALERT_LOCATIONS: expected all codes to share the same prefix"
assert {c[-1] for c in _health_alert_codes} == set('123456789'), \
    "DIVUMWX_HEALTH_ALERT_LOCATIONS: expected last digits to be exactly 1-9"
del _health_alert_codes


def apply_weatherapi_health_alert_merge(cfg, section_name, api_type, path_suffix,
                                          html_root=None, in_england=None, location_code=None):
    """Generic merge for the England-conditional + location_code shape, used for HeatAlert/ColdAlert."""
    if 'WeatherAPI' not in cfg:
        cfg['WeatherAPI'] = {}
    if section_name not in cfg['WeatherAPI']:
        cfg['WeatherAPI'][section_name] = {}
        created = True
    else:
        created = False
    sub = cfg['WeatherAPI'][section_name]

    report = {
        'created_subsection': created,
        'api_type_enforced': False,
        'enabled_defaulted': False,
        'enabled_needs_prompt': False,
        'location_code_set': False,
        'location_code_needs_prompt': False,
        'location_code_invalid': False,
        'data_path_set': False,
        'data_path_needs_prompt': False,
    }

    if sub.get('api_type') != api_type:
        sub['api_type'] = api_type
        report['api_type_enforced'] = True

    if 'enabled' not in sub:
        if in_england is not None:
            sub['enabled'] = 'True' if in_england else 'False'
            report['enabled_defaulted'] = True
        else:
            report['enabled_needs_prompt'] = True

    if 'location_code' not in sub or not sub['location_code']:
        if location_code and location_code in DIVUMWX_HEALTH_ALERT_LOCATIONS:
            sub['location_code'] = location_code
            report['location_code_set'] = True
        elif location_code:
            report['location_code_invalid'] = True
            report['location_code_needs_prompt'] = True
        else:
            report['location_code_needs_prompt'] = True

    if 'data_path' not in sub or not sub['data_path']:
        if html_root:
            sub['data_path'] = html_root.rstrip('/') + '/' + path_suffix
            report['data_path_set'] = True
        else:
            report['data_path_needs_prompt'] = True

    return report


def apply_weatherapi_heatalert_merge(cfg, html_root=None, in_england=None, location_code=None):
    return apply_weatherapi_health_alert_merge(
        cfg, 'HeatAlert', DIVUMWX_WEATHERAPI_HEATALERT_API_TYPE, DIVUMWX_WEATHERAPI_HEATALERT_PATH_SUFFIX,
        html_root, in_england, location_code)


def apply_weatherapi_coldalert_merge(cfg, html_root=None, in_england=None, location_code=None):
    return apply_weatherapi_health_alert_merge(
        cfg, 'ColdAlert', DIVUMWX_WEATHERAPI_COLDALERT_API_TYPE, DIVUMWX_WEATHERAPI_COLDALERT_PATH_SUFFIX,
        html_root, in_england, location_code)


DIVUMWX_WEATHERAPI_METOFFICERSS_API_TYPE = 'metofficerss'

DIVUMWX_METOFFICE_REGIONS = {
    'os': 'Orkney & Shetland',
    'he': 'Highlands & Eilean Siar',
    'gr': 'Grampian',
    'st': 'Strathclyde',
    'ta': 'Central, Tayside & Fife',
    'dg': 'SW Scotland, Lothian & Borders',
    'ni': 'Northern Ireland',
    'wl': 'Wales',
    'nw': 'North West England',
    'ne': 'North East England',
    'yh': 'Yorkshire & Humber',
    'wm': 'West Midlands',
    'em': 'East Midlands',
    'ee': 'East of England',
    'sw': 'South West England',
    'se': 'London & South East England',
}

DIVUMWX_WEATHERAPI_METOFFICERSS_PATH_SUFFIX = 'jsondata/metofficerss.txt'


def apply_weatherapi_metofficerss_merge(cfg, html_root=None, in_uk=None, region_code=None):
    """Mutates cfg['WeatherAPI']['MetOfficeRSS'] in place. Returns a report dict."""
    if 'WeatherAPI' not in cfg:
        cfg['WeatherAPI'] = {}
    if 'MetOfficeRSS' not in cfg['WeatherAPI']:
        cfg['WeatherAPI']['MetOfficeRSS'] = {}
        created = True
    else:
        created = False
    mo = cfg['WeatherAPI']['MetOfficeRSS']

    report = {
        'created_subsection': created,
        'api_type_enforced': False,
        'enabled_defaulted': False,
        'enabled_needs_prompt': False,
        'region_code_set': False,
        'region_code_needs_prompt': False,
        'region_code_invalid': False,
        'data_path_set': False,
        'data_path_needs_prompt': False,
    }

    if mo.get('api_type') != DIVUMWX_WEATHERAPI_METOFFICERSS_API_TYPE:
        mo['api_type'] = DIVUMWX_WEATHERAPI_METOFFICERSS_API_TYPE
        report['api_type_enforced'] = True

    if 'enabled' not in mo:
        if in_uk is not None:
            mo['enabled'] = 'True' if in_uk else 'False'
            report['enabled_defaulted'] = True
        else:
            report['enabled_needs_prompt'] = True

    if 'region_code' not in mo or not mo['region_code']:
        if region_code and region_code in DIVUMWX_METOFFICE_REGIONS:
            mo['region_code'] = region_code
            report['region_code_set'] = True
        elif region_code:
            report['region_code_invalid'] = True
            report['region_code_needs_prompt'] = True
        else:
            report['region_code_needs_prompt'] = True

    if 'data_path' not in mo or not mo['data_path']:
        if html_root:
            mo['data_path'] = html_root.rstrip('/') + '/' + DIVUMWX_WEATHERAPI_METOFFICERSS_PATH_SUFFIX
            report['data_path_set'] = True
        else:
            report['data_path_needs_prompt'] = True

    return report


DIVUMWX_MANDATORY_CARDS = [
    'cardClockOutlook', 'cardCurrent', 'cardForecast', 'cardTemperature',
    'cardAnemometer', 'cardWindCompass', 'cardBarometer', 'cardHumidity',
]

DIVUMWX_NO_SENSOR_OPTIONAL_CARDS = [
    'cardEarthDaylight', 'cardSolarDial', 'cardGeocentric', 'cardMoonPhase',
    'cardPollen', 'cardGreenhouseGas', 'cardEarthquake',
]

DIVUMWX_RAIN_CARD_CHOICES = {
    # 'cardRainfall' (mount id rainCard9) has NO matching HTML wrapper
    # element anywhere in index.html -- confirmed by grep, there is no
    # <div id="rainCard9" data-card-id="cardRainfall" ...> on that page
    # at all, so cardRainfall.js's getElementById('rainCard9') always
    # returns null and the card can never render on the main dashboard,
    # regardless of enabled_cards. 'cardTippingRain' (mount id
    # tippingRainCard9) IS present and wired up correctly, and is what
    # actually appeared as a working, populated "Tipping Rain (mm)" card
    # in this deployment's own dashboard screenshot from earlier in this
    # conversation -- confirming it's the currently-correct target, not
    # cardRainfall. (kiosk.html, separately, still has a wrapper for
    # cardRainfall but NOT cardTippingRain -- the opposite inconsistency
    # -- which looks like a frontend page that wasn't kept in sync with
    # index.html's refactor; worth reporting upstream, not addressed
    # here since it's a kiosk.html-specific frontend fix, not an
    # install.py config-mapping one.)
    'tipping': ['cardTippingRain'],
    'piezo': ['cardPiezoRain'],
    'both': ['cardTippingRain', 'cardPiezoRain'],
}

DIVUMWX_OPTIONAL_CARDS_ORDER = [
    'cardSolarRadiation', 'cardUvIndex', 'cardLightning', 'cardAirquality',
    'cardVapourPressureDeficit', 'cardEvapoTranspiration', 'cardWebcam', 'cardStationImage',
]

DIVUMWX_CARD_TARGET_TOTAL = 25  # theoretical max (8 mandatory + 7 automatic + 2 rain "both" + 8 optional) -- every optional card now always gets asked about

DIVUMWX_WEBCAM_DEFAULT_TITLE = 'Webcam'
DIVUMWX_WEBCAM_DEFAULT_IMAGE = 'img/picam.jpg'

DIVUMWX_STATION_IMAGE_DEFAULT_TITLE = 'Station Image'
DIVUMWX_STATION_IMAGE_DEFAULT_PATH = 'img/stationImage.jpg'


def compute_enabled_cards(rain_choice, optional_selected):
    """Pure assembly function -- the "stop at 20" interactive loop lives in install.py."""
    cards = list(DIVUMWX_MANDATORY_CARDS)
    cards += DIVUMWX_NO_SENSOR_OPTIONAL_CARDS
    cards += DIVUMWX_RAIN_CARD_CHOICES.get(rain_choice, [])
    selected_set = set(optional_selected or [])
    cards += [c for c in DIVUMWX_OPTIONAL_CARDS_ORDER if c in selected_set]
    return cards, len(cards)


def apply_divumwx_cards_merge(cfg, rain_choice=None, optional_selected=None,
                               webcam_title=None, webcam_image=None,
                               station_image_title=None, station_image_path=None):
    """Mutates cfg['DivumWXCards'] in place. Set-once."""
    if 'DivumWXCards' not in cfg:
        cfg['DivumWXCards'] = {}
        created = True
    else:
        created = False
    dc = cfg['DivumWXCards']

    report = {
        'created_subsection': created,
        'rain_sensor_set': False,
        'rain_sensor_invalid': False,
        'rain_sensor_needs_prompt': False,
        'enabled_cards_set': False,
        'enabled_cards_needs_prompt': False,
        'card_count': None,
        'invalid_optional_cards': [],
        'webcam_title_set': False,
        'webcam_image_set': False,
        'station_image_title_set': False,
        'station_image_path_set': False,
    }

    if 'rain_sensor' not in dc or not dc['rain_sensor']:
        if rain_choice in DIVUMWX_RAIN_CARD_CHOICES:
            dc['rain_sensor'] = rain_choice
            report['rain_sensor_set'] = True
        elif rain_choice:
            report['rain_sensor_invalid'] = True
            report['rain_sensor_needs_prompt'] = True
        else:
            report['rain_sensor_needs_prompt'] = True

    if 'enabled_cards' not in dc or not dc['enabled_cards']:
        effective_rain_choice = dc.get('rain_sensor')
        if effective_rain_choice:
            optional_selected = optional_selected or []
            invalid = [c for c in optional_selected if c not in DIVUMWX_OPTIONAL_CARDS_ORDER]
            valid_selected = [c for c in optional_selected if c in DIVUMWX_OPTIONAL_CARDS_ORDER]
            report['invalid_optional_cards'] = invalid

            cards, count = compute_enabled_cards(effective_rain_choice, valid_selected)
            dc['enabled_cards'] = cards
            report['enabled_cards_set'] = True
            report['card_count'] = count
        else:
            report['enabled_cards_needs_prompt'] = True

    if webcam_title and ('webcam_title' not in dc or not dc['webcam_title']):
        dc['webcam_title'] = webcam_title
        report['webcam_title_set'] = True
    if webcam_image and ('webcam_image' not in dc or not dc['webcam_image']):
        dc['webcam_image'] = webcam_image
        report['webcam_image_set'] = True

    if station_image_title and ('station_image_title' not in dc or not dc['station_image_title']):
        dc['station_image_title'] = station_image_title
        report['station_image_title_set'] = True
    if station_image_path and ('station_image_path' not in dc or not dc['station_image_path']):
        dc['station_image_path'] = station_image_path
        report['station_image_path_set'] = True

    return report


DIVUMWX_TIMELAPSE_DEFAULT_OUTPUT_DIR = 'webcam-timelapse'


def apply_timelapse_merge(cfg, site_root=None, source_image=None, output_dir=None,
                           ffmpeg_available=None):
    """
    Mutates cfg['Timelapse'] in place. Set-once.

    TimelapseService is unconditionally registered in DIVUMWX_DATA_SERVICES
    (see DIVUMWX_DATA_SERVICES above), so it always runs -- but without a
    [Timelapse] section it falls back to defaults relative to the global
    StdReport HTML_ROOT rather than DivumWX's own site subdirectory
    (DIVUMWX_ROOT_SUBDIR), and to a hardcoded 'img/picam.jpg' regardless of
    what webcam image path was actually configured. This produced a real
    beta failure (Kjell, Norway): source_image pointed at a file that
    doesn't exist and output_dir hit a PermissionError on first run.

    site_root defaults to DIVUMWX_ROOT_SUBDIR, matching where DivumWX's site
    files (and TimelapseService's divumwx_root join point) normally live --
    but the caller must pass '' explicitly when the site's StdReport
    HTML_ROOT already resolves to a path ending in DIVUMWX_ROOT_SUBDIR (see
    the html_root computation earlier in this file), otherwise
    TimelapseService would join 'divumwx' onto a HTML_ROOT that already ends
    in 'divumwx' at runtime and double-nest the path.
    """
    if 'Timelapse' not in cfg:
        cfg['Timelapse'] = {}
        created = True
    else:
        created = False
    tl = cfg['Timelapse']

    report = {
        'created_subsection': created,
        'site_root_set': False,
        'source_image_set': False,
        'output_dir_set': False,
        'ffmpeg_missing': ffmpeg_available is False,
    }

    if 'site_root' not in tl or not tl['site_root']:
        tl['site_root'] = DIVUMWX_ROOT_SUBDIR if site_root is None else site_root
        report['site_root_set'] = True

    if 'source_image' not in tl or not tl['source_image']:
        tl['source_image'] = source_image or DIVUMWX_WEBCAM_DEFAULT_IMAGE
        report['source_image_set'] = True

    if 'output_dir' not in tl or not tl['output_dir']:
        tl['output_dir'] = output_dir or DIVUMWX_TIMELAPSE_DEFAULT_OUTPUT_DIR
        report['output_dir_set'] = True

    return report


def create_timelapse_output_dir(divumwx_root, output_dir, printer):
    """
    Creates the Timelapse output directory at install time, the same way
    copy_divumwx_frontend() pre-creates the frontend directory -- so
    TimelapseService (which runs as the weewx service user, not necessarily
    whoever ran the installer) finds it already there with usable
    permissions instead of hitting a PermissionError trying to create it
    itself on first NEW_LOOP_PACKET. Mirrors that function's PermissionError
    handling rather than inventing a new pattern.

    divumwx_root must already be the fully-resolved DivumWX site directory
    (site HTML_ROOT + DIVUMWX_ROOT_SUBDIR) -- the same value install.py's
    own `html_root` local variable holds at the call site below, NOT the
    raw global StdReport HTML_ROOT. TimelapseService itself does its own
    separate site_root join against the raw global HTML_ROOT at runtime
    (see divumwx.py); this is install-time only and must land on the same
    final path or the directory gets created in the wrong place.
    """
    full_path = os.path.join(divumwx_root.rstrip('/'), output_dir)
    try:
        os.makedirs(full_path, exist_ok=True)
    except PermissionError as e:
        printer.out(
            f"WARNING: permission denied creating Timelapse output directory "
            f"{full_path} ({e}). TimelapseService will retry creating it "
            f"itself at runtime as the weewx service user, which may also "
            f"fail. Either:\n"
            f"    (a) re-run this install with sudo, or\n"
            f"    (b) fix it by hand first:\n"
            f"        sudo mkdir -p {full_path}\n"
            f"        sudo chown -R <weewx-service-user> {full_path}\n",
            level=1)
        return False
    printer.out(f"Timelapse output directory ready: {full_path}", level=2)
    return True


DIVUMWX_ROOT_SUBDIR = 'divumwx'

DIVUMWX_FRONTEND_EXCLUDED_FILES = {'bootstrap.min.js'}


def copy_divumwx_frontend(source_dir, dest_dir, printer):
    """
    Recursively copies source_dir (the extension's bundled divumwx/
    static frontend) to dest_dir (the resolved HTML_ROOT + 'divumwx').
    Symlinks are skipped, not followed.
    """
    count = 0
    skipped_symlinks = 0
    try:
        for root, dirs, files in os.walk(source_dir):
            rel_root = os.path.relpath(root, source_dir)
            dest_root = os.path.join(dest_dir, rel_root) if rel_root != '.' else dest_dir
            os.makedirs(dest_root, exist_ok=True)
            for filename in files:
                if filename in DIVUMWX_FRONTEND_EXCLUDED_FILES:
                    continue
                src_path = os.path.join(root, filename)
                if os.path.islink(src_path):
                    skipped_symlinks += 1
                    continue
                shutil.copy2(src_path, os.path.join(dest_root, filename))
                count += 1
    except PermissionError as e:
        printer.out(
            f"WARNING: permission denied writing to {dest_dir} ({e}). The "
            f"dashboard frontend was NOT installed/updated this run (only "
            f"{count} file(s) got copied before this happened). This "
            "directory isn't writable by your user account. Either:\n"
            f"    (a) re-run this install with sudo, or\n"
            f"    (b) fix it by hand first:\n"
            f"        sudo mkdir -p {dest_dir}\n"
            f"        sudo chown -R $(whoami):$(whoami) {dest_dir}\n"
            "        then re-run weectl extension install.\n"
            "The rest of this install (weewx.conf settings, database "
            "columns, etc.) will continue normally.", level=1)
        printer.out(f"Copied {count} files to {dest_dir} before the permission "
                    f"error" + (f" ({skipped_symlinks} symlinks skipped)" if skipped_symlinks else ""),
                    level=2)
        return count

    printer.out(f"Copied {count} files to {dest_dir}"
                + (f" ({skipped_symlinks} symlinks skipped)" if skipped_symlinks else ""),
                level=2)
    return count


DIVUMWX_FRONTEND_MODE = 0o775


def set_divumwx_permissions(dest_dir, printer, mode=DIVUMWX_FRONTEND_MODE):
    """Recursively chmods dest_dir and everything under it."""
    all_ok = True

    def _apply(path):
        nonlocal all_ok
        try:
            os.chmod(path, mode)
        except OSError as e:
            printer.out(f"WARNING: could not chmod {path}: {e}", level=1)
            all_ok = False

    _apply(dest_dir)
    for root, dirs, files in os.walk(dest_dir):
        for name in dirs:
            _apply(os.path.join(root, name))
        for name in files:
            _apply(os.path.join(root, name))

    if all_ok:
        printer.out(f"Set {oct(mode)} recursively on {dest_dir}", level=2)
    return all_ok


# NOT CALLED FROM configure() AT THIS STAGE -- see check_all_dependencies()'s
# docstring near the top of this file. Kept defined, not deleted, so the fixes
# already reviewed here aren't lost when this gets re-enabled.
def remove_astro_report_skins(engine, printer, skin_names=('DivumWXSkyfield', 'DivumWXCelestial')):
    """
    Removes the DivumWXSkyfield/DivumWXCelestial skin directories from
    disk when the person declined the dedicated astronomy report pages.

    WeeWX's own install_from_dir() copies every files= manifest entry --
    including these two skins -- BEFORE configure() is ever called, so
    by the time this runs they're already sitting on disk regardless of
    what the person answered. There's no files= mechanism to make that
    initial copy conditional (see remove_divumwx_services() for the same
    root cause on the [Engine][[Services]] side), so this cleans them
    back up here instead, the same "copy happens either way, configure()
    undoes it if declined" pattern.
    """
    skin_dir = engine.root_dict.get('SKIN_DIR')
    if not skin_dir:
        printer.out("WARNING: could not determine SKIN_DIR -- leaving "
                    f"{'/'.join(skin_names)} skin director{'y' if len(skin_names)==1 else 'ies'} "
                    "in place. Remove manually if not wanted.", level=1)
        return
    for name in skin_names:
        path = os.path.join(skin_dir, name)
        if os.path.isdir(path):
            try:
                shutil.rmtree(path)
                printer.out(f"Removed {path} (astronomy report pages declined)", level=1)
            except OSError as e:
                printer.out(f"WARNING: could not remove {path}: {e}", level=1)


def strip_astro_nav_links(html_root, printer):
    """
    Removes the "Celestial Live" and "Skyfield" links from the deployed
    astronomyNavbar.html -- DivumWXSkyfield/DivumWXCelestial are
    permanently absent at this stage (see check_all_dependencies()'s
    docstring near the top of this file), so without this the navbar
    links to two pages that don't exist on disk, a dead end for anyone
    who clicks them.

    Runs AFTER copy_divumwx_frontend() has already copied astronomyNavbar.html
    to disk (this edits the deployed copy in place, not the source file
    still sitting in the extension's own directory).
    """
    path = os.path.join(html_root, 'astronomyNavbar.html')
    if not os.path.isfile(path):
        return
    try:
        with open(path, 'r', encoding='utf-8') as f:
            content = f.read()
    except OSError as e:
        printer.out(f"WARNING: could not read {path} to strip astronomy links: {e}", level=1)
        return

    # Matches the single-line <a class="site-nav-link" href="...">Label</a>
    # entries astronomyNavbar.html actually uses for these two links --
    # not a generic HTML parse, just these two specific known lines.
    patterns = [
        r'\s*<a class="site-nav-link" href="[^"]*celestial/index\.html">Celestial Live</a>\n?',
        r'\s*<a class="site-nav-link" href="[^"]*skyfield">Skyfield</a>\n?',
    ]
    new_content = content
    removed = 0
    for pattern in patterns:
        new_content, n = re.subn(pattern, '\n', new_content)
        removed += n

    if removed and new_content != content:
        try:
            with open(path, 'w', encoding='utf-8') as f:
                f.write(new_content)
            printer.out(f"Removed {removed} astronomy nav link(s) from {path} "
                        f"(DivumWXSkyfield/DivumWXCelestial not installed at this stage)", level=1)
        except OSError as e:
            printer.out(f"WARNING: could not write {path} to strip astronomy links: {e}", level=1)
    elif removed == 0:
        printer.out(f"WARNING: expected astronomy nav links not found in {path} -- "
                    f"navbar left unchanged (astronomyNavbar.html may have changed "
                    f"shape; update strip_astro_nav_links()'s patterns to match).", level=1)


# Services this extension used to declare but no longer does. WeeWX's own
# native service-list merge only ever ADDS entries -- it has no concept
# of removal -- so anything dropped here still lingers forever in an
# already-configured weewx.conf unless explicitly stripped out.
DIVUMWX_OBSOLETE_SERVICES = {
    'prep_services': ['user.skyfieldalmanac.SkyfieldService', 'user.skymapalmanac.SkymapService'],
    'data_services': ['user.skyfieldalmanac.LiveService', 'user.moonimage.MoonService'],
    'report_services': ['user.loopdata.LoopData'],
}


def remove_obsolete_services(cfg, printer, obsolete=DIVUMWX_OBSOLETE_SERVICES):
    """Strips specific, known-obsolete service entries out of [Engine][[Services]]."""
    if 'Engine' not in cfg or 'Services' not in cfg['Engine']:
        return
    services = cfg['Engine']['Services']
    removed_any = []
    for list_key, obsolete_entries in obsolete.items():
        if list_key not in services:
            continue
        current = as_list(services[list_key])
        new_list = [s for s in current if s not in obsolete_entries]
        if new_list != current:
            removed_any.extend(s for s in current if s in obsolete_entries)
            services[list_key] = new_list
    if removed_any:
        printer.out(f"Removed {len(removed_any)} obsolete service(s) from "
                    f"[Engine][[Services]]: {', '.join(removed_any)}", level=1)


def remove_obsolete_weatherapi_sections(cfg, printer, obsolete_sections=('Xweather',)):
    """Deletes specific, no-longer-supported [WeatherAPI][[...]] subsections outright."""
    if 'WeatherAPI' not in cfg:
        return
    removed = []
    for name in obsolete_sections:
        if name in cfg['WeatherAPI']:
            del cfg['WeatherAPI'][name]
            removed.append(name)
    if removed:
        printer.out(f"Removed {len(removed)} obsolete [WeatherAPI] "
                    f"subsection(s): {', '.join(removed)}", level=1)


DIVUMWX_PREP_SERVICES = []
# CloudCoverageOverrideService MUST come before LiveDataService in this
# list -- WeeWX dispatches NEW_LOOP_PACKET to services in data_services
# list order, and LiveDataService reads event.packet to build loop.json.
# If CloudCoverageOverrideService ran after it, loop.json would still
# show the pre-override value even though the eventual archive record
# (built independently, in a later service group) would be correct --
# see that service's own docstring in divumwx.py for the full reasoning.
DIVUMWX_DATA_SERVICES = ['user.divumwx.SkyfieldLoopData', 'user.divumwx.DataInjectService',
                         'user.divumwx.CloudCoverageOverrideService',
                         'user.divumwx.LiveDataService', 'user.divumwx.WeatherAPIService',
                         'user.divumwx.TimelapseService']
DIVUMWX_XTYPE_SERVICES = ['user.divumwx.AirDensityService', 'user.divumwx.vpdService',
                          'user.divumwx.LastNonZeroService']
DIVUMWX_ARCHIVE_SERVICES = ['user.divumwx.SunshineDuration', 'user.divumwx.DivumwxExtrasService']
DIVUMWX_REPORT_SERVICES = []

# Single source of truth for what DivumWX contributes to [Engine][[Services]],
# keyed by the same service_group names weewx.all_service_groups uses. Fed
# both to ExtensionInstaller.__init__ below (so WeeWX's own install_from_dir
# registers them) AND to remove_divumwx_services() (so a failed pre-flight
# check can roll them back out again) -- one list, two consumers, so they
# can't drift apart the way DIVUMWX_SERVICES/the kwargs did before.
DIVUMWX_DECLARED_SERVICES = {
    'prep_services': DIVUMWX_PREP_SERVICES,
    'data_services': DIVUMWX_DATA_SERVICES,
    'xtype_services': DIVUMWX_XTYPE_SERVICES,
    'archive_services': DIVUMWX_ARCHIVE_SERVICES,
    'report_services': DIVUMWX_REPORT_SERVICES,
}


def remove_divumwx_services(cfg, printer):
    """
    Strips DivumWX's own service entries back out of [Engine][[Services]].

    WeeWX's own install_from_dir() adds every service_group declared on
    ExtensionInstaller.__init__ to [Engine][[Services]] BEFORE configure()
    is ever called, and sets save_config=True as soon as it does -- that
    flag is OR'd (not overwritten) with whatever configure() itself
    returns. So if configure() aborts on a failed pre-flight check,
    weewx.conf still gets saved with DivumWX's services registered to
    load on the next weewxd start, even though none of the report
    stanzas/HTML_ROOT/etc. those services depend on were ever written --
    a broken half-installed state. Called on abort, before returning
    False, to undo that registration so an aborted install leaves
    [Engine][[Services]] exactly as it found it.
    """
    if 'Engine' not in cfg or 'Services' not in cfg['Engine']:
        return
    services = cfg['Engine']['Services']
    removed_any = []
    for list_key, declared_entries in DIVUMWX_DECLARED_SERVICES.items():
        if list_key not in services or not declared_entries:
            continue
        current = as_list(services[list_key])
        new_list = [s for s in current if s not in declared_entries]
        if new_list != current:
            removed_any.extend(s for s in current if s in declared_entries)
            services[list_key] = new_list
    if removed_any:
        printer.out(f"Rolled back {len(removed_any)} DivumWX service registration(s) "
                    f"from [Engine][[Services]] after aborted install: "
                    f"{', '.join(removed_any)}", level=1)


def loader():
    return DivumwxInstaller()


class DivumwxInstaller(ExtensionInstaller):

    def __init__(self):
        super(DivumwxInstaller, self).__init__(
            version="0.1.0",
            name='divumwx',
            description='DivumWX weather dashboard',
            author="",
            author_email="",
            # These are handled natively by WeeWX's own installer engine
            # (weewx.all_service_groups) -- it creates the list if missing
            # and appends with de-duplication, AND it's what weectl
            # extension uninstall reads back to know which entries to
            # remove. This is the single, live source of truth for
            # DivumWX's own service declarations -- there is no separate
            # dict duplicating this elsewhere in the file anymore (an
            # earlier version had one, DIVUMWX_SERVICES, which silently
            # fell out of sync with this list -- that's exactly how
            # SkyfieldLoopData/TimelapseService went missing before).
            #
            # user.wxskyfield.WxSkyfield is deliberately NOT declared
            # here -- it belongs to the separate weewx-skyfield
            # extension's own installer, not DivumWX's. Declaring it here
            # would make DivumWX's uninstall remove an entry it doesn't
            # own.
            #
            # Only groups DivumWX actually populates get passed at all --
            # NOT prep_services/report_services=[] for empty groups.
            # WeeWX's own uninstall_extension() iterates
            # config_dict['Engine']['Services'][service_group] directly,
            # without normalizing through option_as_list() first (unlike
            # its install-side counterpart). A single-entry option like
            # 'prep_services = weewx.engine.StdTimeSynch' is stored by
            # ConfigObj as a bare string, not a list -- and iterating a
            # string in Python walks it character by character. Merely
            # having the key present (even with an empty list value) is
            # enough to trigger that loop and explode the string into
            # one list entry per character. Omitting empty groups here
            # keeps WeeWX's uninstall code from ever touching them.
            **{k: v for k, v in DIVUMWX_DECLARED_SERVICES.items() if v},
            # bin/user and the three skins/ directories -- every entry
            # below was confirmed against the actual extension source
            # tree. de421.bsp, solar.html.tmpl, boltek_strikes.php.tmpl,
            # and water.html.tmpl were removed on purpose (no longer
            # needed / superseded).
            files=[
                ('bin/user', [
                    'bin/user/divumwx.py',
                    'bin/user/divumwx_cards.py',
                    'bin/user/lastrain.py',
                    'bin/user/stats.py',
                    'bin/user/time_since.py',
                    'bin/user/CustomSimulator.py',
                    # Source lives at the extension's repo root, not
                    # under bin/user/ -- _gen_file_paths() still resolves
                    # this correctly to bin/user/divumwx_uninstall_helper.py
                    # on disk (verified: os.path.commonpath(['bin/user',
                    # 'divumwx_uninstall_helper.py']) is '', so the
                    # relpath step is a no-op and the plain filename is
                    # appended under dest_dir as-is). Needs to actually be
                    # installed somewhere real -- the closing message in
                    # configure() below tells people to run
                    # 'bin/user/divumwx_uninstall_helper.py' before
                    # uninstalling, and that path didn't exist on disk at
                    # all until this entry was added.
                    'divumwx_uninstall_helper.py',
                ]),
                ('skins/DivumWX', [
                    'skins/DivumWX/jsondata/archive.json.tmpl',
                    'skins/DivumWX/jsondata/charts.json.tmpl',
                    'skins/DivumWX/jsondata/strings.json.tmpl',
                    # en.conf is the reference dictionary (see its own header
                    # comment), not just "the English translation" -- keep it
                    # first. All 25 below carry the full 267-key set (every
                    # card's phrases, not just the original barometer-only
                    # subset fr/de/es/it/no started with) -- verified against
                    # the real Cheetah/ConfigObj engines before being added
                    # here. DIVUMWX_LANG_CHOICES above is the single place a
                    # new one gets added alongside its own lang/<code>.conf
                    # file.
                    'skins/DivumWX/lang/en.conf',
                    'skins/DivumWX/lang/en_US.conf',
                    'skins/DivumWX/lang/ar.conf',
                    'skins/DivumWX/lang/br.conf',
                    'skins/DivumWX/lang/ca.conf',
                    'skins/DivumWX/lang/cn.conf',
                    'skins/DivumWX/lang/cy.conf',
                    'skins/DivumWX/lang/cz.conf',
                    'skins/DivumWX/lang/da.conf',
                    'skins/DivumWX/lang/de.conf',
                    'skins/DivumWX/lang/es.conf',
                    'skins/DivumWX/lang/eu.conf',
                    'skins/DivumWX/lang/fr.conf',
                    'skins/DivumWX/lang/gr.conf',
                    'skins/DivumWX/lang/hi.conf',
                    'skins/DivumWX/lang/it.conf',
                    'skins/DivumWX/lang/nl.conf',
                    'skins/DivumWX/lang/no.conf',
                    'skins/DivumWX/lang/pl.conf',
                    'skins/DivumWX/lang/pt.conf',
                    'skins/DivumWX/lang/ta.conf',
                    'skins/DivumWX/lang/th.conf',
                    'skins/DivumWX/lang/tr.conf',
                    'skins/DivumWX/lang/uk.conf',
                    'skins/DivumWX/lang/ur.conf',
                ]),
                # skins/DivumWXSkyfield and skins/DivumWXCelestial are
                # deliberately NOT listed here -- see check_all_dependencies()'s
                # docstring near the top of this file for why. Leaving them out
                # of this manifest means WeeWX's own install_from_dir() never
                # copies them to disk in the first place, so configure() no
                # longer needs remove_astro_report_skins() to clean them back up.
                # strip_astro_nav_links() is still needed, though, and now runs
                # UNCONDITIONALLY in configure() -- see the call site there --
                # because the main divumwx/ frontend (copied by
                # copy_divumwx_frontend(), a completely separate step from this
                # files= manifest) still ships astronomyNavbar.html with links
                # to these two now-permanently-absent pages baked in.
            ],
        )

    def configure(self, engine):
        """
        Everything WeeWX's native static-config injection can't express:
        pre-flight checks, conflict-aware merges, region-conditional
        defaults, secrets, and always-enforced structural fields.
        Returns True (config modified), False if pre-flight checks fail.
        """
        cfg = engine.config_dict
        printer = engine.printer

        printer.out("Configuring DivumWX...", level=1)

        # NOTE -- the DivumWXSkyfield/DivumWXCelestial astronomy report
        # pages (and the interactive prompt asking whether to install
        # them) are currently removed from this installer -- see
        # check_all_dependencies()'s docstring above for the root cause
        # and how to re-enable once it's fixed. The skyfield Python
        # PACKAGE is still required (see REQUIRED_PYTHON_PACKAGES) --
        # only the third-party weewx-skyfield/weewx-celestial WeeWX
        # extensions and the two report pages that depended on them are
        # affected.

        if not check_all_dependencies(printer):
            remove_divumwx_services(cfg, printer)
            printer.out("DivumWX installation aborted -- see errors above.", level=1)
            return False

        remove_obsolete_services(cfg, printer)
        remove_obsolete_weatherapi_sections(cfg, printer)

        fresh_install = is_fresh_divumwx_install(cfg)
        printer.out(f"Fresh install: {fresh_install}", level=2)

        # Absolute install root, derived from the site's existing HTML_ROOT
        # + a fixed subdirectory.
        site_html_root = cfg['StdReport']['HTML_ROOT']
        if not os.path.isabs(site_html_root):
            site_html_root = os.path.join(engine.root_dict['WEEWX_ROOT'], site_html_root)

        if os.path.basename(os.path.normpath(site_html_root)) == DIVUMWX_ROOT_SUBDIR:
            html_root = os.path.normpath(site_html_root)
        else:
            html_root = os.path.join(site_html_root, DIVUMWX_ROOT_SUBDIR)
        printer.out(f"DivumWX install root: {html_root}", level=2)

        # Copy the static frontend (divumwx/).
        extension_dir = os.path.dirname(os.path.abspath(__file__))
        divumwx_source = os.path.join(extension_dir, 'divumwx')
        if os.path.isdir(divumwx_source):
            copy_divumwx_frontend(divumwx_source, html_root, printer)
            set_divumwx_permissions(html_root, printer)
            # Unconditional now, not gated behind a declined-prompt branch --
            # DivumWXSkyfield/DivumWXCelestial are permanently absent at this
            # stage (see check_all_dependencies()'s docstring above), not
            # conditionally opted out of, so the shipped astronomyNavbar.html's
            # "Celestial Live"/"Skyfield" links need stripping on every install,
            # not only when a person happened to answer a prompt that no
            # longer exists.
            strip_astro_nav_links(html_root, printer)
        else:
            printer.out(f"WARNING: {divumwx_source} not found, skipping frontend copy "
                        f"(expected if testing configure() logic in isolation).", level=1)

        # --- In additions-file order ---

        printer.out("Available languages:", level=1)
        for code, label in DIVUMWX_LANG_CHOICES.items():
            printer.out(f"  {code}: {label}", level=1)
        # Default to whatever's already configured (re-running the installer
        # without changing anything should be a no-op here), or 'en' on a
        # genuinely fresh install. prompt_with_options enforces the answer
        # is one of the known codes, so a typo can't silently set an
        # unsupported lang value with no matching lang/<code>.conf on disk.
        existing_lang = cfg.get('StdReport', {}).get('DivumWXReport', {}).get('lang', 'en')
        if existing_lang not in DIVUMWX_LANG_CHOICES:
            existing_lang = 'en'
        lang = weecfg.prompt_with_options(
            "Language for the DivumWX report (translates the barometer "
            "trend text in archive.json; see readme for what this does "
            "and doesn't cover)",
            default=existing_lang, options=list(DIVUMWX_LANG_CHOICES.keys()))

        report_merge_result = apply_divumwx_report_merge(cfg, html_root=html_root, lang=lang)
        if report_merge_result['unit_system_enforced']:
            printer.out(
                "DivumWXReport unit_system set to METRICWX (required -- "
                "loop.json and the report's own generated JSON files must "
                "agree on one unit system; the front-end handles display "
                "conversion from there). See the comment above this "
                "setting in weewx.conf before changing it.", level=2)
        if report_merge_result['lang_enforced']:
            printer.out(f"DivumWXReport lang set to '{lang}'.", level=2)

        apply_calculation_merges(cfg, fresh_install=fresh_install)

        add_missing_extras_columns(cfg, printer)

        apply_standalone_sections_merge(cfg)

        apply_datainject_merge(cfg, html_root=html_root)

        update_interval = weecfg.prompt_with_limits(
            "LiveData update interval, in seconds", default='2', low_limit=1, high_limit=3600)
        apply_livedata_merge(cfg, html_root=html_root, update_interval=update_interval)

        apply_skyfieldloopdata_merge(cfg, html_root=html_root)

        apply_cloudcoverageoverride_merge(cfg, html_root=html_root)

        # --- [WeatherAPI] ---

        app_id = weecfg.prompt_with_options(
            "OpenWeatherMap app_id (for weather alerts, leave blank to configure later)", default='')
        alerts_poll_interval = weecfg.prompt_with_limits(
            "Alerts poll interval, in seconds", default='1800', low_limit=60, high_limit=86400)
        alerts_report = apply_weatherapi_alerts_merge(
            cfg, html_root=html_root, app_id=app_id, poll_interval=alerts_poll_interval)
        if alerts_report['enabled_disabled_no_app_id']:
            printer.out(
                "No OpenWeatherMap app_id given -- Alerts left disabled "
                "(enabled = False) to avoid unauthenticated polling. Add an "
                "app_id and set enabled = True under [WeatherAPI][[Alerts]] "
                "in weewx.conf later to turn it on.", level=1)

        model_options = list(DIVUMWX_OPENMETEO_MODEL_CHOICES.keys())
        printer.out("Open-Meteo forecast models (see https://open-meteo.com/en/docs "
                    "for the full current list):", level=1)
        for code, label in DIVUMWX_OPENMETEO_MODEL_CHOICES.items():
            printer.out(f"  {code if code else '(blank)'}: {label}", level=1)
        forecast_model = weecfg.prompt_with_options(
            "Open-Meteo forecast model, leave blank for best match",
            default='', options=model_options)
        apply_weatherapi_forecast_merge(cfg, html_root=html_root, forecast_model=forecast_model)

        for section_name, spec in DIVUMWX_WEATHERAPI_SIMPLE_SECTIONS.items():
            apply_weatherapi_simple_merge(
                cfg, section_name, spec['api_type'], spec['path_suffix'], html_root=html_root)

        icao_code = weecfg.prompt_with_options(
            "ICAO airport code for local METAR conditions (e.g. EGTK), leave blank to configure later", default='').upper()
        metar_poll_interval = weecfg.prompt_with_limits(
            "Metar poll interval, in seconds", default='300', low_limit=60, high_limit=86400)
        apply_weatherapi_metar_merge(
            cfg, html_root=html_root, icao_code=icao_code, poll_interval=metar_poll_interval)

        in_northern_hemisphere = y_or_n("Are you in the Northern Hemisphere? (y/n) ") == 'y'
        in_england = y_or_n("Are you in England? (y/n) ") == 'y'
        in_uk = in_england or (
            y_or_n("Are you in the United Kingdom (but not England)? (y/n) ") == 'y')

        # Persisted unconditionally, every run -- NOT inside the
        # "if 'enabled_cards' not in ..." gate a few lines below that
        # apply_divumwx_cards_merge() lives behind, since in_uk is asked
        # fresh on every single install/reconfigure run (not gated behind
        # a "have we asked before" check the way card selection is), and
        # would otherwise never get persisted at all for an existing
        # install re-running this script. Previously in_uk only existed
        # transiently in this function, used solely to gate individual
        # [WeatherAPI][[...]] sub-services' own enabled flags (flood,
        # heat/cold alert, MetOfficeRSS) -- nothing kept the actual
        # answer anywhere a template could read it back. See
        # cardsBundleNew.js's alertBar.js: it needs to know whether a
        # station is actually in the UK to decide whether to show its
        # UK-specific alert card (Met Office link, UKHSA health alerts,
        # UK flood data, AuroraWatch) at all -- a lat/lon bounding-box
        # guess is a reasonable fallback, but this explicit answer is
        # more accurate and should be preferred when available.
        cfg.setdefault('DivumWXCards', {})
        cfg['DivumWXCards']['in_uk'] = 'True' if in_uk else 'False'

        apply_weatherapi_flood_merge(cfg, html_root=html_root, in_england=in_england)

        apply_weatherapi_aurorawatch_merge(
            cfg, html_root=html_root, in_northern_hemisphere=in_northern_hemisphere)

        location_code = None
        if in_england:
            printer.out("UK Health Security Agency regions:", level=1)
            for code, label in DIVUMWX_HEALTH_ALERT_LOCATIONS.items():
                printer.out(f"  {code}: {label}", level=1)
            # All 9 codes share the same 'E1200000' prefix and differ only
            # in the final digit -- only ask for that digit rather than
            # making the person type/paste the full 9-character code.
            location_digit = weecfg.prompt_with_options(
                "UK Health Security Agency location code -- last digit only "
                "(1-9), leave blank to configure later",
                default='', options=[str(n) for n in range(1, 10)] + [''])
            if location_digit:
                location_code = DIVUMWX_HEALTH_ALERT_LOCATION_PREFIX + location_digit
        apply_weatherapi_heatalert_merge(
            cfg, html_root=html_root, in_england=in_england, location_code=location_code)
        apply_weatherapi_coldalert_merge(
            cfg, html_root=html_root, in_england=in_england, location_code=location_code)

        region_code = None
        if in_uk:
            region_options = list(DIVUMWX_METOFFICE_REGIONS.keys())
            printer.out("Met Office regions:", level=1)
            for code, label in DIVUMWX_METOFFICE_REGIONS.items():
                printer.out(f"  {code}: {label}", level=1)
            region_code = weecfg.prompt_with_options(
                "Met Office region code", default='', options=region_options + [''])
        apply_weatherapi_metofficerss_merge(cfg, html_root=html_root, in_uk=in_uk, region_code=region_code)

        # --- [DivumWXCards] ---
        if 'enabled_cards' not in cfg.get('DivumWXCards', {}):
            printer.out("8 mandatory cards will always be included: "
                        + ", ".join(DIVUMWX_MANDATORY_CARDS), level=1)
            printer.out("7 more cards don't need any hardware sensor and are "
                        "included automatically: "
                        + ", ".join(DIVUMWX_NO_SENSOR_OPTIONAL_CARDS), level=1)

            rain_choice = weecfg.prompt_with_options(
                "Rain sensor: tipping bucket, piezo, or both?",
                default='tipping', options=['tipping', 'piezo', 'both'])

            optional_selected = []
            _, mandatory_and_rain_count = compute_enabled_cards(rain_choice, [])
            running_total = mandatory_and_rain_count
            # Told up front, not discovered mid-loop: the mandatory + automatic
            # + rain cards already claim most of the DIVUMWX_CARD_TARGET_TOTAL
            # budget, so there are usually FEWER optional slots available than
            # there are optional cards to ask about (8 possible cards, but
            # e.g. only 4 slots left with a single rain sensor chosen). Cards
            # later in DIVUMWX_OPTIONAL_CARDS_ORDER (VPD, ET, webcam, station
            # image) are the ones actually at risk of never being asked about
            # at all -- not declined, just never reached. Printed before the
            # loop starts so a "yes" run of answers doesn't silently eat every
            # remaining slot before getting to a card the person actually
            # wanted; this message plus the "Reached N cards" one below used
            # to be the ONLY signal this could happen, and both were easy to
            # miss (the fixed order penalizes the same handful of cards on
            # every default-order install; the "Reached" message used to be
            # level=2, invisible at default installer verbosity).
            available_optional_slots = max(0, DIVUMWX_CARD_TARGET_TOTAL - mandatory_and_rain_count)
            printer.out(
                f"{mandatory_and_rain_count} cards are already committed (mandatory + "
                f"automatic + rain sensor), leaving {available_optional_slots} of "
                f"{DIVUMWX_CARD_TARGET_TOTAL} slot(s) for the {len(DIVUMWX_OPTIONAL_CARDS_ORDER)} "
                f"optional cards below, asked in this order: "
                + ", ".join(DIVUMWX_OPTIONAL_CARDS_ORDER)
                + ". Saying yes to the earlier ones may mean later ones in this "
                "list are never asked about -- answer no to any you don't need "
                "to leave room for the ones you do.", level=1)
            for card_name in DIVUMWX_OPTIONAL_CARDS_ORDER:
                if running_total >= DIVUMWX_CARD_TARGET_TOTAL:
                    printer.out(f"Reached {DIVUMWX_CARD_TARGET_TOTAL} cards, stopping -- "
                                f"the remaining optional card(s) were never asked about "
                                f"(not declined): "
                                + ", ".join(DIVUMWX_OPTIONAL_CARDS_ORDER[DIVUMWX_OPTIONAL_CARDS_ORDER.index(card_name):]),
                                level=1)
                    break
                include = y_or_n(f"Include {card_name}? ({running_total}/"
                                  f"{DIVUMWX_CARD_TARGET_TOTAL} so far) (y/n) ") == 'y'
                if include:
                    optional_selected.append(card_name)
                    running_total += 1

            webcam_title = None
            webcam_image = None
            if 'cardWebcam' in optional_selected:
                webcam_title = weecfg.prompt_with_options(
                    "Webcam title/description, shown on the webcam card and its modal "
                    "(e.g. 'Looking Towards North West of Steeple Claydon, UK')",
                    default=DIVUMWX_WEBCAM_DEFAULT_TITLE)
                webcam_image = weecfg.prompt_with_options(
                    "Webcam image path, relative to the DivumWX html root "
                    "(e.g. 'img/picam.jpg')",
                    default=DIVUMWX_WEBCAM_DEFAULT_IMAGE)

            station_image_title = None
            station_image_path = None
            if 'cardStationImage' in optional_selected:
                station_image_title = weecfg.prompt_with_options(
                    "Station image title/description (used as the image's alt text)",
                    default=DIVUMWX_STATION_IMAGE_DEFAULT_TITLE)
                station_image_path = weecfg.prompt_with_options(
                    "Station image path, relative to the DivumWX html root "
                    "(e.g. 'img/stationImage.jpg')",
                    default=DIVUMWX_STATION_IMAGE_DEFAULT_PATH)

            apply_divumwx_cards_merge(
                cfg, rain_choice=rain_choice, optional_selected=optional_selected,
                webcam_title=webcam_title, webcam_image=webcam_image,
                station_image_title=station_image_title, station_image_path=station_image_path)

        # --- [Timelapse] ---
        # Deliberately OUTSIDE the "if 'enabled_cards' not in ..." gate above:
        # TimelapseService is always registered (see DIVUMWX_DATA_SERVICES)
        # regardless of card selection, so it can be running unconfigured on
        # an existing beta install that already went through card selection
        # in an earlier version of this installer, before this section
        # existed -- an upgrade run needs to fill this in too, not just
        # fresh installs. apply_timelapse_merge() is itself set-once (checks
        # 'not in tl' per key), so this is safe to call every run. Reuses
        # whatever webcam_image is already on record in [DivumWXCards]
        # (freshly set above, or from a prior run) rather than asking again.
        ffmpeg_available = shutil.which('ffmpeg') is not None
        if not ffmpeg_available:
            printer.out(
                "WARNING: 'ffmpeg' not found on PATH. TimelapseService "
                "will start but timelapse capture will stay disabled until "
                "ffmpeg is installed (e.g. sudo apt install ffmpeg) and "
                "weewx is restarted.", level=1)
        timelapse_site_root = '' if os.path.basename(os.path.normpath(site_html_root)) \
            == DIVUMWX_ROOT_SUBDIR else DIVUMWX_ROOT_SUBDIR
        existing_webcam_image = cfg.get('DivumWXCards', {}).get('webcam_image')
        apply_timelapse_merge(
            cfg, site_root=timelapse_site_root, source_image=existing_webcam_image,
            ffmpeg_available=ffmpeg_available)
        create_timelapse_output_dir(
            html_root, cfg['Timelapse']['output_dir'], printer)

        printer.out("DivumWX configuration complete.", level=1)
        printer.out(
            "NOTE: 'weectl extension uninstall divumwx' will remove the files "
            "and service entries declared above, but will NOT remove the "
            f"copied frontend at {html_root}, will NOT revert the weewx.conf "
            "sections this installer wrote (WeatherAPI, DataInjectService, "
            "LiveData, DivumWXCards, the DivumWXReport/DivumWXSkyfield/"
            "DivumWXCelestial report stanzas, etc.), will NOT re-enable "
            "SkyfieldReport/CelestialReport, and cannot drop the archive "
            "table columns it added. Run bin/user/divumwx_uninstall_helper.py "
            "BEFORE uninstalling if you want those cleaned up.", level=1)

        return True
