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
            'divumwx_enabled_cards': json.dumps(list(enabled_cards)),
            'divumwx_webcam_title': json.dumps(webcam_title),
            'divumwx_webcam_image': json.dumps(webcam_image),
            'divumwx_station_image_title': json.dumps(station_image_title),
            'divumwx_station_image_path': json.dumps(station_image_path),
            'divumwx_in_uk': json.dumps(in_uk),
            'divumwx_lang': json.dumps(lang),
            'divumwx_strings_payload': json.dumps(strings_payload),
        }
        return [search_list_extension]

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
