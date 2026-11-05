"""Search list extension exposing [DivumWXCards] to Cheetah templates.

Kept as its own small module rather than added to the large divumwx.py,
so this one change is easy to review in isolation.

To use: add 'user.divumwx_cards.DivumwxCards' to skin.conf's
search_list_extensions (see skins/DivumWX/skin.conf's [CheetahGenerator]
section, alongside user.stats.MyStats etc).

Provides three tags:

    $divumwx_enabled_cards  — a pre-formatted JSON array STRING (not a
                               Python list) of enabled card names, e.g.
                               '["cardClockOutlook", "cardCurrent", ...]'
                               Pre-formatted so it can be embedded directly
                               into archive.json.tmpl's raw JSON output
                               without any extra quoting:

                                 "enabled_cards": $divumwx_enabled_cards,

                               If [DivumWXCards] enabled_cards isn't set
                               yet (install.py hasn't run, or its card
                               prompts were skipped), returns an empty
                               JSON array '[]' rather than failing the
                               whole template render.

    $divumwx_webcam_title,
    $divumwx_webcam_image  — same pre-formatted-JSON-literal pattern as
                               above: each is either a properly quoted/
                               escaped JSON string (via json.dumps, not
                               hand-rolled escaping) or the bare JSON
                               literal null if [DivumWXCards]
                               webcam_title/webcam_image is unset or
                               empty. Embed directly, no extra quoting:

                                 "webcam_title": $divumwx_webcam_title,
                                 "webcam_image": $divumwx_webcam_image

                               These replace an earlier archive.json.tmpl
                               attempt to read the same config keys via a
                               bare $config['DivumWXCards']['webcam_title']
                               template lookup -- $config was never
                               actually registered in this skin's search
                               list (nothing here or elsewhere exposed a
                               raw config dict to templates), so that
                               lookup silently threw on every single
                               render and fell into its own #except null
                               fallback, regardless of what was actually
                               configured. Confirmed via a real
                               archive.json capture: webcam_title/image
                               both null despite both being set correctly
                               in weewx.conf.

    $divumwx_webcams         — JSON array of the (up to four) webcams, one
                               object each: {"n", "card", "title",
                               "image", "mode"}, in order (webcam 1 =
                               cardWebcam from webcam_title/webcam_image/
                               webcam_mode; 2-4 = cardWebcam2..4 from
                               webcam2_* .. webcam4_*). Only webcams with
                               an image set are listed. mode is 'daylight'
                               (live from sunrise to sunset, night-time
                               page after dark; the default) or '24hr'.

                                 "webcams": $divumwx_webcams,

    $divumwx_station_image_title,
    $divumwx_station_image_path  — same pre-formatted-JSON-literal pattern
                               as webcam_title/webcam_image above, reading
                               [DivumWXCards] station_image_title/
                               station_image_path. Embed directly:

                                 "station_image_title": $divumwx_station_image_title,
                                 "station_image_path": $divumwx_station_image_path

                               cardStationImage.js previously had no
                               config-driven path at all -- it hardcoded
                               'img/stationImage.jpg' as sample data (see
                               that file's own top-of-block comment). This
                               is the config-side half of wiring it up for
                               real, matching how cardWebcam.js already
                               works; the frontend fetch is the other
                               half (see cardStationImage.js).

    $divumwx_country_code    — the station's country as two lower-case
                               letters (e.g. "gb"), for the flag after the
                               site title: [DivumWXCards] country_code if
                               set ('none' hides the flag), else the time
                               zone's country from the system's zone.tab.
                               JSON null when unknown.

    $divumwx_in_uk           — the installer's own "are you in the UK?"
                               answer ([DivumWXCards] in_uk, set by
                               install.py's in_england/in_uk prompts),
                               exposed as the bare JSON literal true,
                               false, or null (not a string) -- null when
                               the install predates this field or the
                               value is otherwise unset/blank. Embed
                               directly:

                                 "in_uk": $divumwx_in_uk

                               Consumed by alertBar.js to decide whether
                               to show its UK-specific alert card (Met
                               Office link on OpenWeatherMap alerts, UKHSA
                               health alerts, UK flood data, AuroraWatch)
                               -- this explicit answer is preferred over
                               alertBar.js's own lat/lon bounding-box
                               guess when present, since it's what the
                               person who set up the station actually
                               said rather than an approximation from
                               station coordinates.

    $divumwx_lang             — the report's active WeeWX `lang` setting
                               ([StdReport][[DivumWXReport]] lang, set by
                               install.py's language prompt -- see
                               DIVUMWX_LANG_CHOICES there), as a plain JSON
                               string, e.g. "fr". Defaults to "en" if the
                               setting is missing entirely (an install that
                               predates this field). This is the SAME value
                               CheetahGenerator itself already used to pick
                               skins/DivumWX/lang/<code>.conf for this
                               template render's own $gettext(...) lookups
                               (see archive.json.tmpl's "barom".trend_desc)
                               -- exposing it here just lets the front-end
                               read back which language that was, without
                               it needing to independently know or guess.
                               Embed directly:

                                 "lang": $divumwx_lang

    $divumwx_strings_payload  — the entire jsondata/strings.json content,
                               pre-formatted as one JSON object string:
                               '{"_default": "da", "da": {...}, "fr": {...},
                               ...}'. "_default" is this report's own
                               configured lang (same value as $divumwx_lang
                               above); every other top-level key is a
                               language code mapped to that language's full
                               [Texts] dictionary, read directly from
                               skins/DivumWX/lang/*.conf via configobj --
                               NOT via $gettext(), which only ever resolves
                               against this one report's single configured
                               lang, so it can't be asked for a different
                               language mid-render. This is what makes
                               DivumWX's client-side language switcher
                               possible: cardI18n.js can switch which
                               language's dictionary it reads from
                               instantly in the browser, the same way its
                               existing unit-system dropdown switches units,
                               with no server round trip, since every
                               language's text already arrived in this one
                               payload. Built entirely here (not spread
                               across Cheetah template logic) so the whole
                               structure is assembled with plain Python
                               json.dumps -- reliable escaping, no risk of
                               the kind of Cheetah-side JSON-construction
                               bugs documented elsewhere in this codebase's
                               history. Embed directly (already valid JSON):

                                 $divumwx_strings_payload
"""
import glob
import json
import logging
import os.path

import configobj

from weeutil.weeutil import to_bool
from weewx.cheetahgenerator import SearchList

log = logging.getLogger(__name__)

try:
    from user.divumwx_version import DIVUMWX_VERSION
except ImportError:  # divumwx_version.py missing (partial manual install)
    DIVUMWX_VERSION = None


# ---------------------------------------------------------------------------
# The station's country, for the flag after the site title.
ZONEINFO = '/usr/share/zoneinfo'


def _zone_name(value):
    """'Europe/Copenhagen' from a TZ value or a path such as
    '/usr/share/zoneinfo/Europe/Copenhagen' or '../usr/share/zoneinfo/...';
    None if it isn't one."""
    value = (value or '').strip().lstrip(':')
    if 'zoneinfo/' in value:
        value = value.split('zoneinfo/')[-1]
    if not value or value.startswith('/') or ' ' in value:
        return None
    return value


def system_timezone(environ=None, etc='/etc'):
    """The system time zone name, as the user set it. Published as
    archive.json's "timezone" and used for the flag's country.

    Tried in order: the TZ environment variable; /etc/timezone (Debian,
    Raspberry Pi OS); the zone /etc/localtime points to, following ONE link
    only. Not os.path.realpath(): since tzdata 2022b several zones are
    aliases of others (Europe/Copenhagen, Oslo, Stockholm -> Berlin;
    Amsterdam, Luxembourg -> Brussels; Monaco -> Paris ...), and where
    tzdata installs those as symlinks, following the whole chain turned a
    Danish station into a German one."""
    environ = os.environ if environ is None else environ
    name = _zone_name(environ.get('TZ'))
    if name:
        return name
    try:
        with open(os.path.join(etc, 'timezone')) as fh:
            name = _zone_name(fh.read())
        if name:
            return name
    except OSError:
        pass
    localtime = os.path.join(etc, 'localtime')
    try:
        name = _zone_name(os.readlink(localtime))
        if name:
            return name
    except OSError:
        pass
    try:   # last resort (e.g. a link to a link that isn't under zoneinfo)
        name = _zone_name(os.path.realpath(localtime))
        if name:
            return name
    except OSError:
        pass
    return 'UTC'


def timezone_country(tz_name, zoneinfo=ZONEINFO):
    """ISO 3166 country code (lower case) of an IANA time zone, from the
    system's zone.tab, or None (UTC, Etc/..., or not found). Old names such
    as 'GB' or 'US/Eastern' are followed to the zone they link to."""
    if not tz_name:
        return None
    zones = {}
    for tab in ('zone.tab', 'zone1970.tab'):   # zone1970.tab lists several codes: keep the first
        try:
            with open(os.path.join(zoneinfo, tab), encoding='utf-8') as fh:
                for line in fh:
                    if line.startswith('#') or not line.strip():
                        continue
                    parts = line.split('\t')
                    if len(parts) >= 3:
                        zones.setdefault(parts[2].strip(), parts[0].split(',')[0].strip().lower())
        except OSError:
            continue
    if tz_name in zones:
        return zones[tz_name]
    links = {}
    try:
        with open(os.path.join(zoneinfo, 'tzdata.zi'), encoding='utf-8') as fh:
            for line in fh:
                if line.startswith('L '):
                    _l, target, alias = line.split()[:3]
                    links[alias] = target
    except OSError:
        pass
    seen = set()
    while tz_name in links and tz_name not in seen:
        seen.add(tz_name)
        tz_name = links[tz_name]
        if tz_name in zones:
            return zones[tz_name]
    try:   # a zone file that is a symlink to another
        real = os.path.relpath(os.path.realpath(os.path.join(zoneinfo, tz_name)), zoneinfo)
        return zones.get(real)
    except (OSError, ValueError):
        return None


def station_country(cards_section):
    """[DivumWXCards] country_code if set ('none' hides the flag), else the
    time zone's country. Two lower-case letters, or None."""
    raw = str(cards_section.get('country_code', '') or '').strip().lower()
    if raw in ('none', 'off', 'no', 'false'):
        return None
    if raw:
        return raw if (len(raw) == 2 and raw.isalpha()) else None
    return timezone_country(system_timezone())


class DivumwxCards(SearchList):

    def __init__(self, generator):
        SearchList.__init__(self, generator)

    def get_extension_list(self, timespan, db_lookup):
        cards_section = self.generator.config_dict.get('DivumWXCards', {})
        enabled_cards = cards_section.get('enabled_cards', [])
        # configobj returns a plain string (not a list) for a single-item
        # comma-list, same quirk we handled in merge_helpers.as_list() —
        # normalize here too, so a station with exactly one enabled card
        # doesn't break the JSON output.
        if isinstance(enabled_cards, str):
            enabled_cards = [enabled_cards] if enabled_cards else []

        # json.dumps handles quoting/escaping properly (control characters,
        # unicode, embedded quotes) -- safer than the hand-rolled
        # str().replace('\\', ...).replace('"', ...) escaping the earlier
        # archive.json.tmpl attempt used for these same two values.
        # ConfigObj returns '' (not missing) for a key present but left
        # blank in weewx.conf, so `or None` treats blank the same as
        # unset -- both become JSON null, not an empty-string title/image.
        webcam_title = cards_section.get('webcam_title', '') or None
        webcam_image = cards_section.get('webcam_image', '') or None
        webcams = []
        for n in range(1, 5):
            prefix = 'webcam' if n == 1 else 'webcam%d' % n
            image = cards_section.get(prefix + '_image', '') or None
            if not image:
                continue
            mode = str(cards_section.get(prefix + '_mode', '') or '').strip().lower()
            webcams.append({
                'n': n,
                'card': 'cardWebcam' if n == 1 else 'cardWebcam%d' % n,
                'title': cards_section.get(prefix + '_title', '') or None,
                # Optional webcamN_description: a short write-up shown
                # when hovering over the card.
                'description': cards_section.get(prefix + '_description', '') or None,
                'image': image,
                'mode': mode if mode in ('daylight', '24hr') else 'daylight',
            })
        station_image_title = cards_section.get('station_image_title', '') or None
        station_image_path = cards_section.get('station_image_path', '') or None

        # in_uk: the installer's own "are you in the UK?" answer
        # (install.py, [DivumWXCards] in_uk), NOT re-derived here. Left
        # as JSON null (rather than defaulting to false) when unset --
        # e.g. an existing install that hasn't been re-run since this
        # field was added -- so the front-end can tell "explicitly
        # answered no" apart from "we don't actually know" and fall back
        # to its own lat/lon-based guess only in the latter case.
        # ConfigObj stores this as the string 'True'/'False' (matching
        # every other installer-collected yes/no answer in this codebase,
        # e.g. [WeatherAPI][[Alerts]] enabled), so it needs to go through
        # to_bool() rather than a raw string comparison -- to_bool()
        # accepts 'true'/'yes'/'1' etc. too, same as the rest of WeeWX.
        in_uk_raw = cards_section.get('in_uk', None)
        in_uk = to_bool(in_uk_raw) if in_uk_raw not in (None, '') else None

        # country_code: the station's country for the flag after the site
        # title -- [DivumWXCards] country_code, else from the time zone.
        country_code = station_country(cards_section)

        # page_background: the installer's "seasonal or solid" answer
        # ([DivumWXCards] page_background). Anything unset or unrecognised
        # is published as 'seasonal', the installer's default, so
        # seasonalBackground.js always gets a known value.
        # warnings_source: where the alert bar's weather warnings come from
        # ([DivumWXCards] warnings_source, set by the installer):
        # 'openweather' (OpenWeatherMap alerts, Met Office RSS fallback; the
        # default) or 'meteoalarm' (user.divumwx_meteoalarm).
        warnings_source = str(cards_section.get('warnings_source', '') or '').strip().lower()
        if warnings_source not in ('openweather', 'meteoalarm'):
            warnings_source = 'openweather'
        # Which data binding DivumWX's extra fields are read from: its own
        # extras database when the station has one (see
        # user.divumwx.extras_database_exists, the same test the
        # DivumwxExtrasService uses to decide where to save them), else the
        # main archive. Used by charts.json.tmpl / archive.json.tmpl as
        # $divumwx_extras_binding.
        try:
            from user.divumwx import extras_database_exists, EXTRAS_BINDING
            extras_binding = EXTRAS_BINDING if extras_database_exists(self.generator.config_dict) else 'wx_binding'
        except Exception:
            extras_binding = 'wx_binding'
        page_background = str(cards_section.get('page_background', '') or '').strip().lower()
        if page_background not in ('seasonal', 'solid'):
            page_background = 'seasonal'
        # solar_array_watts: the solar array's rated size in watts, from the
        # installer ([DivumWXCards] solar_array_watts), for the solar cards'
        # PV efficiency. Published as null when unset or not a positive number.
        try:
            solar_array_watts = float(str(cards_section.get('solar_array_watts', '') or '').strip().rstrip('Ww'))
            solar_array_watts = int(round(solar_array_watts)) if solar_array_watts > 0 else None
        except ValueError:
            solar_array_watts = None

        # Read from [StdReport][[DivumWXReport]], not [DivumWXCards] -- this
        # is WeeWX's own real report-level lang setting (the same one
        # CheetahGenerator itself consulted to select this render's
        # lang/<code>.conf), not a DivumWX-specific config key. Defaults to
        # 'en' rather than None/null so front-end code can always treat this
        # as a real language code without a separate null check.
        divumwx_report = self.generator.config_dict.get('StdReport', {}).get('DivumWXReport', {})
        lang = divumwx_report.get('lang', 'en') or 'en'

        strings_payload = self._read_all_lang_strings()
        strings_payload['_default'] = lang

        search_list_extension = {
            # $divumwx_last(seconds[, data_binding=...]): the last `seconds`
            # before the report time, counted in real seconds -- use in
            # place of $span(time_delta=...)/$span(hour_delta=...).
            # WeeWX's $span works the start out as a local clock time, which
            # on the night the clocks go back can fall in the repeated hour
            # and come out an hour late, after the report time ("start time
            # is greater than stop time"), failing the whole template.
            'divumwx_last': self._last_span(timespan, db_lookup),
            'divumwx_enabled_cards': json.dumps(list(enabled_cards)),
            'divumwx_webcam_title': json.dumps(webcam_title),
            'divumwx_webcam_image': json.dumps(webcam_image),
            'divumwx_webcams': json.dumps(webcams),
            'divumwx_station_image_title': json.dumps(station_image_title),
            'divumwx_station_image_path': json.dumps(station_image_path),
            'divumwx_in_uk': json.dumps(in_uk),
            'divumwx_country_code': json.dumps(country_code),
            # The station's time zone name for archive.json meta.timezone.
            'divumwx_timezone': json.dumps(system_timezone()),
            'divumwx_page_background': json.dumps(page_background),
            'divumwx_solar_array_watts': json.dumps(solar_array_watts),
            # A plain string (not JSON): a data binding name for template tags.
            'divumwx_extras_binding': extras_binding,
            'divumwx_warnings_source': json.dumps(warnings_source),
            'divumwx_lang': json.dumps(lang),
            'divumwx_strings_payload': json.dumps(strings_payload),
            # Release version for the dashboard footer (archive.json meta).
            'divumwx_version': json.dumps(DIVUMWX_VERSION),
        }
        return [search_list_extension]

    def _last_span(self, timespan, db_lookup):
        from weewx.tags import TimespanBinder
        from weeutil.weeutil import TimeSpan
        gen = self.generator
        stop = timespan.stop

        def divumwx_last(seconds, data_binding=None):
            # Same as WeeWX's $span (context 'day', the report's formatter,
            # converter and skin options), but start = stop - seconds.
            return TimespanBinder(TimeSpan(stop - int(seconds), stop), db_lookup,
                                  data_binding=data_binding, context='day',
                                  formatter=gen.formatter, converter=gen.converter,
                                  skin_dict=gen.skin_dict)
        return divumwx_last

    def _read_all_lang_strings(self):
        """Read every skins/DivumWX/lang/*.conf file's [Texts] section
        directly via configobj, keyed by language code (the filename minus
        '.conf'). This deliberately does NOT go through WeeWX's own
        $gettext()/Gettext search-list class -- that mechanism only ever
        has access to the ONE lang file matching this report's own
        configured `lang` setting (it's loaded as part of building
        self.generator.skin_dict, before any template ever runs), so there
        is no way to ask it for a *different* language's text mid-render.
        Reading the files ourselves, with the same configobj library WeeWX
        itself uses internally, is the only way to get every language's
        text into one JSON payload for the client-side switcher.

        The skin's own directory is derived from skin_dict, the same
        SKIN_ROOT/skin fields weewx.cheetahgenerator.SkinInfo exposes as
        $SKIN_ROOT/$skin -- not hardcoded, so this keeps working regardless
        of install layout (pip vs setup.py, a relocated WEEWX_ROOT, etc).
        """
        skin_dict = getattr(self.generator, 'skin_dict', {})
        weewx_root = self.generator.config_dict.get('WEEWX_ROOT', '')
        skin_root = skin_dict.get('SKIN_ROOT', 'skins')
        skin_name = skin_dict.get('skin', 'DivumWX')
        lang_dir = os.path.join(weewx_root, skin_root, skin_name, 'lang')

        result = {}
        for path in sorted(glob.glob(os.path.join(lang_dir, '*.conf'))):
            code = os.path.splitext(os.path.basename(path))[0]
            try:
                conf = configobj.ConfigObj(path, encoding='utf-8')
                texts = conf.get('Texts', {})
                # configobj can hand back nested Section objects for [Texts]
                # sub-blocks (pgettext-style contexts) as well as plain
                # string values -- only the plain [Texts] key = "value"
                # entries are relevant to $gettext()/DivumWXI18N.t(), so
                # skip anything that isn't a plain string rather than
                # letting json.dumps choke on a non-serializable Section.
                result[code] = {
                    k: v for k, v in texts.items() if isinstance(v, str)
                }
            except Exception as e:
                # One malformed .conf file must not take down
                # archive.json/charts.json generation too -- they share
                # this same search list extension instance.
                log.warning("divumwx_cards: skipping unreadable lang file %s: %s", path, e)
                continue
        return result
