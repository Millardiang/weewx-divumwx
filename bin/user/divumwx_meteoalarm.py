"""
##############################################################################################
# divumwx_meteoalarm.py version 1.0.0
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################

MeteoAlarm weather warnings for DivumWX: Europe's warnings (www.meteoalarm.org,
the EUMETNET countries, the UK included) in the dashboard's alert bar.

Built from Ian Millard's weewx-meteoalarm (github.com/Millardiang/weewx-meteoalarm,
GPLv3): its feed download, area matching (EMMA codes, geocode aliases, polygons),
caching, warning model and area-map setup code are used as they are. weewx-meteoalarm
was inspired by get-meteoalarm-warning-inc.php by Ken True (saratoga-weather.org),
adapted with permission from wrnWarningEU-CAP.php by Wim van der Kuil
(pwsdashboard.com).

MeteoalarmService runs on its own thread. Every poll_interval seconds it fetches
the feeds of the countries watched (cached for cache_max_age seconds) and writes
jsondata/meteoalarm.json in the DivumWX site for alertBar.js. It does nothing
unless [DivumWXCards] warnings_source = meteoalarm and areas are chosen.

Areas are shared with weewx-meteoalarm, so a station using both chooses them once:

[Meteoalarm]
    # EMMA_IDs to watch, comma separated
    emma_ids = DK002, DK004
    # and/or a polygon, as CAP-style "lat,lon lat,lon ..." pairs; areas it
    # touches are added, and alerts drawn as polygons/circles that cross it match
    polygon = ""
    min_level = 2          # 1 green, 2 yellow, 3 orange, 4 red
    cache_max_age = 300    # seconds
    timeout = 30           # seconds per feed download

[DivumWXMeteoalarm]        # all optional
    poll_interval = 600    # seconds between updates of meteoalarm.json
    site_root = divumwx    # DivumWX site folder below HTML_ROOT ('' if HTML_ROOT is it)
    json_file = jsondata/meteoalarm.json

Choose the areas again later (prints the link to the area map, reads its setup code):

    python3 divumwx_meteoalarm.py --setup [--config /path/to/weewx.conf]

Test a configuration:

    python3 divumwx_meteoalarm.py --emma-ids UK258 [--polygon "..."] [--test-file feed.json] [--json]
"""

import html
import json
import logging
import math
import os
import re
import time
import urllib.error
import urllib.request
from datetime import datetime, timezone
from urllib.parse import urlparse

import threading

try:
    import weewx
    from weewx.engine import StdService
except ImportError:  # running standalone
    weewx = None
    StdService = object

log = logging.getLogger(__name__)

VERSION = "1.0.0"   # divumwx_meteoalarm; based on weewx-meteoalarm 1.0.0
FEED_URL = "https://feeds.meteoalarm.org/api/v1/warnings/feeds-{slug}"
# Bump when matching changes, so cached results from older code are thrown away
# (the cache outlives a restart, and would otherwise hide new matches for cache_max_age).
MATCH_VERSION = 2
USER_AGENT = "DivumWX-meteoalarm/%s" % VERSION

# area-code prefix -> feed name
FEEDS = {
    "AT": "austria", "BA": "bosnia-herzegovina", "BE": "belgium", "BG": "bulgaria",
    "CH": "switzerland", "CY": "cyprus", "CZ": "czechia", "DE": "germany",
    "DK": "denmark", "EE": "estonia", "EI": "ireland", "ES": "spain", "FI": "finland",
    "FR": "france", "GR": "greece", "HR": "croatia", "HU": "hungary",
    "IE": "ireland", "IL": "israel", "IS": "iceland", "IT": "italy",
    "LT": "lithuania", "LU": "luxembourg", "LV": "latvia", "MD": "moldova",
    "ME": "montenegro", "MK": "republic-of-north-macedonia", "MT": "malta",
    "NL": "netherlands", "NO": "norway", "PL": "poland", "PT": "portugal",
    "RO": "romania", "RS": "serbia", "SE": "sweden", "SI": "slovenia",
    "SK": "slovakia", "UK": "united-kingdom",
}

# rough extents of countries whose areas have no shapes yet (lon/lat box)
NO_SHAPE_BOXES = {"CH": (5.9, 45.8, 10.5, 47.8)}

LEVEL_COLORS = {1: "#29d660", 2: "#FFDB23", 3: "#FF9500", 4: "#FF0100"}
LEVEL_NAMES = {1: "Green", 2: "Yellow", 3: "Orange", 4: "Red"}
SEVERITY = {1: "Minor", 2: "Moderate", 3: "Severe", 4: "Extreme"}
LEVEL_ADVICE = {
    2: "The weather is potentially dangerous. Be attentive if you practise activities exposed to "
       "meteorological risks and keep informed about the expected conditions.",
    3: "The weather is dangerous. Damage and casualties are likely. Be very vigilant, keep regularly "
       "informed and follow any advice given by your authorities.",
    4: "The weather is very dangerous. Major damage and threat to life are likely over a wide area. "
       "Follow orders and advice from your authorities under all circumstances.",
}
COLOR_WORDS = ("Green", "Yellow", "Orange", "Red", "Amber", "Moderate", "Severe", "Extreme")
ICONS = {1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 12, 13}   # icons/meteoalarm_N.svg that exist

LANG_NAMES = {
    "bg": "Български", "ca": "Català", "cs": "Čeština", "cy": "Cymraeg", "da": "Dansk",
    "de": "Deutsch", "el": "Ελληνικά", "en": "English", "es": "Español", "et": "Eesti",
    "fi": "Suomi", "fr": "Français", "ga": "Gaeilge", "he": "עברית", "hr": "Hrvatski",
    "hu": "Magyar", "is": "Íslenska", "it": "Italiano", "lb": "Lëtzebuergesch",
    "lt": "Lietuvių", "lv": "Latviešu", "mk": "Македонски", "mt": "Malti", "nl": "Nederlands",
    "no": "Norsk", "nb": "Norsk", "nn": "Nynorsk", "pl": "Polski", "pt": "Português",
    "ro": "Română", "ru": "Русский", "sk": "Slovenčina", "sl": "Slovenščina", "sr": "Srpski",
    "sv": "Svenska", "uk": "Українська",
}


# ----------------------------------------------------------------------------- config

def to_list(value):
    if value is None:
        return []
    if isinstance(value, (list, tuple)):
        value = ",".join(value)
    return [v.strip().upper() for v in str(value).split(",") if v.strip()]


class Options:
    """Settings from [Meteoalarm] in weewx.conf, overridable per skin."""

    def __init__(self, conf, weewx_root=".", skin_root="skins", sqlite_root="archive", station=None):
        conf = dict(conf or {})
        self.station = station_position(station)
        self.emma_ids = to_list(conf.get("emma_ids"))
        polygon = conf.get("polygon") or ""
        if isinstance(polygon, (list, tuple)):
            # an unquoted "lat,lon lat,lon" line in weewx.conf is split at the commas; rejoin it
            polygon = ",".join(polygon)
        self.polygon_text = " ".join(str(polygon).split())
        self.polygon = parse_cap_polygon(self.polygon_text) if self.polygon_text else []
        try:
            self.min_level = min(4, max(1, int(conf.get("min_level", 2))))
        except ValueError:
            self.min_level = 2
        self.cache_max_age = int(conf.get("cache_max_age", 300))
        self.timeout = int(conf.get("timeout", 30))
        self.date_format = conf.get("date_format", "%Y-%m-%d")
        self.time_format = conf.get("time_format", "%H:%M")
        self.test_file = conf.get("test_file")
        self.areas_dir = conf.get("areas_dir") or os.path.join(weewx_root, skin_root, "DivumWX", "meteoalarm", "areas")
        # Its own cache, so it never races weewx-meteoalarm's report thread for the same file.
        self.cache_file = conf.get("cache_file") or os.path.join(weewx_root, sqlite_root, "divumwx-meteoalarm-cache.json")


def station_position(station):
    """[lat, lon] from the [Station] section of weewx.conf, or None."""
    try:
        lat, lon = float(station["latitude"]), float(station["longitude"])
    except (TypeError, KeyError, ValueError):
        return None
    return [lat, lon] if -90 <= lat <= 90 and -180 <= lon <= 180 else None


# ----------------------------------------------------------------------------- geometry
# Points are (lon, lat). Only outer rings are used; good enough for "does it touch".

def parse_cap_polygon(text):
    """CAP polygon "lat,lon lat,lon ..." -> [(lon, lat), ...]."""
    pts = []
    for pair in str(text).replace(";", " ").split():
        try:
            lat, lon = (float(v) for v in pair.split(","))
        except ValueError:
            continue
        pts.append((lon, lat))
    return pts if len(pts) >= 3 else []


def circle_to_polygon(text, sides=24):
    """CAP circle "lat,lon radius_km" -> polygon approximating it."""
    try:
        centre, radius = str(text).split()
        lat, lon = (float(v) for v in centre.split(","))
        r = float(radius)
    except ValueError:
        return []
    dlat = r / 111.32
    dlon = r / (111.32 * max(0.01, math.cos(math.radians(lat))))
    return [(lon + dlon * math.cos(2 * math.pi * i / sides), lat + dlat * math.sin(2 * math.pi * i / sides))
            for i in range(sides)]


def bbox(ring):
    xs, ys = [p[0] for p in ring], [p[1] for p in ring]
    return min(xs), min(ys), max(xs), max(ys)


def bbox_overlap(a, b):
    return a[0] <= b[2] and b[0] <= a[2] and a[1] <= b[3] and b[1] <= a[3]


def point_in_ring(pt, ring):
    x, y = pt
    inside = False
    j = len(ring) - 1
    for i in range(len(ring)):
        xi, yi = ring[i]
        xj, yj = ring[j]
        if (yi > y) != (yj > y) and x < (xj - xi) * (y - yi) / (yj - yi) + xi:
            inside = not inside
        j = i
    return inside


def _segments_cross(p1, p2, q1, q2):
    def orient(a, b, c):
        return (b[0] - a[0]) * (c[1] - a[1]) - (b[1] - a[1]) * (c[0] - a[0])
    d1, d2 = orient(q1, q2, p1), orient(q1, q2, p2)
    d3, d4 = orient(p1, p2, q1), orient(p1, p2, q2)
    return (d1 > 0) != (d2 > 0) and (d3 > 0) != (d4 > 0)


def rings_intersect(a, b):
    """True if two simple polygons (outer rings) overlap or touch."""
    if not a or not b or not bbox_overlap(bbox(a), bbox(b)):
        return False
    if point_in_ring(a[0], b) or point_in_ring(b[0], a):
        return True
    for i in range(len(a)):
        p1, p2 = a[i - 1], a[i]
        for j in range(len(b)):
            if _segments_cross(p1, p2, b[j - 1], b[j]):
                return True
    return False


def outer_rings(geometry):
    if geometry["type"] == "Polygon":
        return [[tuple(p) for p in geometry["coordinates"][0]]]
    if geometry["type"] == "MultiPolygon":
        return [[tuple(p) for p in poly[0]] for poly in geometry["coordinates"]]
    return []


class Areas:
    """Area names, shapes and geocode aliases shipped in skins/DivumWX/meteoalarm/areas."""

    def __init__(self, directory):
        self.dir = directory
        self.index = self._load("index.json")
        self.aliases = self._load("aliases.json")
        self._shapes = {}

    def _load(self, name):
        path = os.path.join(self.dir, name)
        try:
            with open(path, encoding="utf-8") as fh:
                return json.load(fh)
        except (OSError, ValueError) as e:
            log.error("meteoalarm: cannot read %s: %s", path, e)
            return {}

    def name(self, code):
        entry = self.index.get(code)
        return entry[0] if entry else code

    def shapes(self, country):
        if country not in self._shapes:
            data = self._load("%s.geojson" % country) if self.index else {}
            self._shapes[country] = {f["properties"]["code"]: outer_rings(f["geometry"])
                                     for f in data.get("features", [])}
        return self._shapes[country]

    def touching(self, polygon):
        """EMMA_IDs whose shape overlaps the polygon."""
        if not polygon:
            return []
        box = bbox(polygon)
        hits = []
        for code, (_name, country, area_box) in sorted(self.index.items()):
            if area_box and bbox_overlap(box, area_box):
                if any(rings_intersect(polygon, ring) for ring in self.shapes(country).get(code, [])):
                    hits.append(code)
        return hits


# ----------------------------------------------------------------------------- feeds

def parse_time(value):
    if not value:
        return None
    try:
        dt = datetime.fromisoformat(str(value).replace("Z", "+00:00"))
    except ValueError:
        return None
    return dt if dt.tzinfo else dt.replace(tzinfo=timezone.utc)


def as_list(value):
    if value is None:
        return []
    return value if isinstance(value, list) else [value]


def fetch_feed(feed, opts):
    if opts.test_file:   # a saved feed, or a folder of <feed name>.json files
        path = opts.test_file
        if os.path.isdir(path):
            path = os.path.join(path, feed + ".json")
            if not os.path.exists(path):
                return None
        with open(path, encoding="utf-8") as fh:
            return json.load(fh)
    url = FEED_URL.format(slug=feed)
    start = time.time()
    req = urllib.request.Request(url, headers={"User-Agent": USER_AGENT})
    try:
        with urllib.request.urlopen(req, timeout=opts.timeout) as resp:
            data = json.loads(resp.read().decode("utf-8", errors="replace"))
    except (urllib.error.URLError, OSError, ValueError) as e:
        log.error("meteoalarm: failed to fetch %s: %s", url, e)
        return None
    log.debug("meteoalarm: fetched %s in %.2fs", url, time.time() - start)
    return data


def area_hits(info, codes, aliases, polygon, code_shapes=None):
    """Which of our areas an alert <info> block covers, as dicts code/desc/note.

    code_shapes ({EMMA_ID: [ring, ...]}) lets an alert that names no area of ours,
    but is drawn as a polygon or circle (the Met Office tags warnings by forecast
    region, e.g. "London & South East England"), match the areas its outline crosses."""
    hits = []
    for area in as_list(info.get("area")):
        desc = area.get("areaDesc", "")
        matched = False
        for geo in as_list(area.get("geocode")):
            value, name = geo.get("value"), geo.get("valueName")
            if not isinstance(value, str) or not isinstance(name, str):
                continue
            if name == "EMMA_ID" and value in codes:
                hits.append({"code": value, "desc": desc, "note": ""})
                matched = True
                continue
            alias = aliases.get("%s|%s" % (value, name))
            if alias in codes:
                hits.append({"code": alias + "*", "desc": desc,
                             "note": "EMMA_ID %s matched through %s geocode %s." % (alias, name, value)})
                matched = True
        if matched:
            continue
        outlines = [s for s in [parse_cap_polygon(p) for p in as_list(area.get("polygon"))]
                    + [circle_to_polygon(c) for c in as_list(area.get("circle"))] if s]
        if not outlines:
            continue
        for code, rings in sorted((code_shapes or {}).items()):
            if any(rings_intersect(o, r) for o in outlines for r in rings):
                hits.append({"code": code, "desc": desc,
                             "note": "EMMA_ID %s matched because the warning's outline crosses it." % code})
                matched = True
        if polygon and not matched and any(rings_intersect(polygon, s) for s in outlines):
            hits.append({"code": "polygon", "desc": desc,
                         "note": "Matched because the alert's own outline crosses your polygon."})
    return hits


def collect(feeds, codes, aliases, polygon, now, code_shapes=None):
    """{warning id: {language: info}} for current alerts (or starting within 24 h) in our areas."""
    warns = {}
    for feed_name, feed in feeds.items():
        for warning in feed.get("warnings") or []:
            wid = "%s/%s" % (feed_name, warning.get("uuid", ""))
            for alert in warning.values():
                if not isinstance(alert, dict) or "info" not in alert:
                    continue
                infos = as_list(alert["info"])
                if not infos:
                    continue
                expires = parse_time(infos[0].get("expires"))
                if expires is None or now > expires:
                    continue
                effective = parse_time(infos[0].get("onset")) or parse_time(infos[0].get("effective")) or now
                if (effective - now).total_seconds() > 24 * 3600:
                    continue
                for info in infos:
                    hits = area_hits(info, codes, aliases, polygon, code_shapes)
                    if hits:
                        entry = {k: v for k, v in info.items() if k != "area"}
                        entry.update(forus=hits, sent=alert.get("sent"))
                        warns.setdefault(wid, {})[info.get("language", "")] = entry
    return warns


def load_warnings(opts, areas, codes):
    """Cached warnings, refreshed from the feeds when older than cache_max_age."""
    signature = {"codes": sorted(codes), "polygon": opts.polygon_text, "match": MATCH_VERSION}
    try:
        with open(opts.cache_file, encoding="utf-8") as fh:
            cache = json.load(fh)
        fresh = time.time() - cache.get("fetched", 0) <= opts.cache_max_age
        if fresh and cache.get("signature") == signature and not opts.test_file:
            return cache["warnings"], cache["fetched"]
    except (OSError, ValueError, KeyError):
        pass

    feeds = {}
    wanted = {FEEDS[c[:2]] for c in codes if c[:2] in FEEDS}
    if opts.polygon:   # alerts drawn as polygons can come from any feed the polygon reaches
        box = bbox(opts.polygon)
        wanted |= {FEEDS[v[1]] for v in areas.index.values()
                   if v[2] and v[1] in FEEDS and bbox_overlap(box, v[2])}
        wanted |= {FEEDS[c] for c, b in NO_SHAPE_BOXES.items() if bbox_overlap(box, b)}
    for name in sorted(wanted):
        data = fetch_feed(name, opts)
        if data:
            feeds[name] = data
    code_shapes = {}
    for c in codes:
        entry = areas.index.get(c)
        if entry:
            code_shapes[c] = areas.shapes(entry[1]).get(c, [])
    code_shapes = {c: r for c, r in code_shapes.items() if r}
    warnings = collect(feeds, set(codes), areas.aliases, opts.polygon, datetime.now(timezone.utc), code_shapes)
    fetched = time.time()
    try:
        with open(opts.cache_file, "w", encoding="utf-8") as fh:
            json.dump({"fetched": fetched, "signature": signature, "warnings": warnings}, fh)
    except OSError as e:
        log.error("meteoalarm: cannot write cache %s: %s", opts.cache_file, e)
    return warnings, fetched


# ----------------------------------------------------------------------------- template model

def esc(text):
    return html.escape(str(text or ""), quote=True)


def text_to_html(text, url=""):
    out = esc(str(text or "").strip())
    if url:
        e = esc(url)
        out = out.replace(e, '<a href="%s" target="_blank" rel="noopener">%s</a>' % (e, e))
    return out.replace("\r\n", "\n").replace("\n", "<br/>")


def leading_int(value):
    try:
        return int(str(value).split(";")[0].strip())
    except ValueError:
        return 0


class Alert:
    """One warning, ready for a template. Text attributes are already HTML-escaped."""

    def __init__(self, languages, number, opts):
        info = next(iter(languages.values()))
        level_raw = type_raw = ""
        for p in as_list(info.get("parameter")):
            if p.get("valueName") == "awareness_level":
                level_raw = p.get("value", "")
            elif p.get("valueName") == "awareness_type" or not type_raw:
                type_raw = p.get("value", "")
        self.level = leading_int(level_raw)
        self.type_no = leading_int(type_raw)
        parts = type_raw.split(";")
        slug = parts[1].strip() if len(parts) > 1 else ""
        self.event_type = esc(slug.replace("-", " ").title())
        self.icon = "meteoalarm_%s.svg" % (self.type_no if self.type_no in ICONS else "000")
        self.color = LEVEL_COLORS.get(self.level, "#fff")
        self.level_name = LEVEL_NAMES.get(self.level, "")
        self.severity = SEVERITY.get(self.level, "")
        self.advice = LEVEL_ADVICE.get(self.level, "")
        title = str(info.get("event", ""))
        for word in COLOR_WORDS:
            title = title.replace(word, "")
        title = title.strip()
        self.title = esc(title[:1].upper() + title[1:]) or self.event_type
        self.anchor = "alert%d" % number

        def local(value):
            dt = parse_time(value)
            return dt.astimezone() if dt else None

        self.onset = local(info.get("onset") or info.get("effective"))
        self.expires = local(info.get("expires"))
        self.sent = local(info.get("sent"))
        fmt_full = "%s %s" % (opts.date_format, opts.time_format)
        self.onset_text = esc(self.onset.strftime(fmt_full)) if self.onset else ""
        if self.expires:
            same_day = self.onset and self.onset.date() == self.expires.date()
            self.expires_text = esc(self.expires.strftime(opts.time_format if same_day else fmt_full))
        else:
            self.expires_text = ""
        self.sent_text = esc(self.sent.strftime(fmt_full)) if self.sent else ""

        web = str(info.get("web") or "").strip()
        u = urlparse(web)
        self.web = esc(web)
        self.origin_url = esc("%s://%s/" % (u.scheme, u.netloc)) if u.scheme and u.netloc else ""
        self.sender_name = esc(info.get("senderName") or "")
        self.areas = [{"code": esc(h["code"]), "desc": esc(h["desc"]), "note": esc(h["note"]),
                       "country": h["code"][:2] if h["code"] != "polygon" else ""}
                      for h in info["forus"]]
        self.languages = []
        for i, (lang, t) in enumerate(languages.items()):
            headline = esc(str(t.get("headline") or "").strip())
            description = text_to_html(t.get("description"), web)
            if description == headline:
                description = ""
            self.languages.append({
                "code": esc(lang), "id": "%s-%d" % (self.anchor, i),
                "name": esc(LANG_NAMES.get(lang[:2].lower(), lang)),
                "headline": headline, "description": description,
                "instruction": text_to_html(t.get("instruction"), web),
            })


class Meteoalarm:
    """The $meteoalarm tag. Work is done on first use, so unused skins pay nothing."""

    def __init__(self, opts):
        self.opts = opts
        self._done = False

    def _load(self):
        if self._done:
            return
        self._done = True
        opts = self.opts
        self._areas = Areas(opts.areas_dir)
        self._polygon_codes = self._areas.touching(opts.polygon)
        self._codes = list(dict.fromkeys(opts.emma_ids + self._polygon_codes))
        self._alerts, self._max_level, self._updated = [], 0, None
        if not self._codes and not opts.polygon:
            return
        warnings, fetched = load_warnings(opts, self._areas, self._codes)
        self._updated = datetime.fromtimestamp(fetched).astimezone()
        n = 0
        for languages in warnings.values():
            n += 1
            alert = Alert(languages, n, opts)
            if not 1 <= alert.level <= 4:
                continue
            self._max_level = max(self._max_level, alert.level)
            if alert.level >= opts.min_level:
                self._alerts.append(alert)
        self._alerts.sort(key=lambda a: (-a.level, a.onset or datetime.max.replace(tzinfo=timezone.utc)))

    # --- configuration -----------------------------------------------------
    @property
    def configured(self):
        return bool(self.opts.emma_ids or self.opts.polygon)

    @property
    def area_codes(self):
        self._load()
        return self._codes

    @property
    def polygon_area_codes(self):
        self._load()
        return self._polygon_codes

    @property
    def area_list(self):
        """[{code, name}] for every area watched (escaped)."""
        self._load()
        return [{"code": c, "name": esc(self._areas.name(c))} for c in self._codes]

    @property
    def polygon(self):
        return esc(self.opts.polygon_text)

    @property
    def min_level(self):
        return self.opts.min_level

    @property
    def min_level_name(self):
        return LEVEL_NAMES[self.opts.min_level]

    # --- warnings ----------------------------------------------------------
    @property
    def alerts(self):
        self._load()
        return self._alerts

    @property
    def has_alerts(self):
        return bool(self.alerts)

    @property
    def count(self):
        return len(self.alerts)

    @property
    def max_level(self):
        """Highest level of any current warning, including ones below min_level."""
        self._load()
        return self._max_level

    @property
    def max_color(self):
        return LEVEL_COLORS.get(self.max_level, LEVEL_COLORS[1])

    @property
    def updated(self):
        self._load()
        fmt = "%s %s" % (self.opts.date_format, self.opts.time_format)
        return esc(self._updated.strftime(fmt)) if self._updated else ""

    @property
    def summary(self):
        """Alerts grouped by area for a compact display:
        [{area, country, icons: [{color, icon, title, anchor, level_name}]}]"""
        groups = {}
        for a in self.alerts:
            for area in a.areas:
                key = (area["desc"], area["country"])
                icons = groups.setdefault(key, {})
                icons.setdefault((a.level, a.icon), {"color": a.color, "icon": a.icon, "title": a.event_type,
                                                     "anchor": a.anchor, "level_name": a.level_name})
        return [{"area": desc, "country": esc(FEEDS.get(country, country).replace("-", " ").title()) if country else "",
                 "icons": sorted(icons.values(), key=lambda i: i["color"])}
                for (desc, country), icons in groups.items()]

    @property
    def json(self):
        """Current warnings as a JSON document, for other programs."""
        out = []
        for a in self.alerts:
            out.append({
                "level": a.level, "level_name": a.level_name, "type": html.unescape(a.event_type),
                "title": html.unescape(a.title),
                "onset": a.onset.isoformat() if a.onset else None,
                "expires": a.expires.isoformat() if a.expires else None,
                "areas": [{k: html.unescape(v) for k, v in ar.items()} for ar in a.areas],
                "headline": {l["code"]: html.unescape(l["headline"]) for l in a.languages},
            })
        return json.dumps({"updated": self._updated.isoformat() if self._updated else None,
                           "max_level": self.max_level, "alerts": out}, ensure_ascii=False, indent=1)


# ----------------------------------------------------------------------------- choosing areas
# Areas are chosen on the area map published on GitHub Pages. The installer (and
# "meteoalarm.py --setup") prints a link to it with the station and current choice
# in the URL fragment, which browsers never send to the server, and reads back the
# setup code the map shows: MA1:<EMMA codes>:<min level>:<lat,lon;lat,lon;...>

MAX_CORNERS = 200
# The area map from weewx-meteoalarm, on GitHub Pages.
MAP_URL = "https://millardiang.github.io/weewx-meteoalarm/v1/map/"


def clean_polygon(text):
    """Validate a polygon "lat,lon lat,lon ..." (or ; separated); return it as a closed CAP string."""
    pts = []
    for pair in str(text or "").replace(";", " ").split():
        try:
            lat, lon = (float(v) for v in pair.split(","))
        except ValueError:
            raise ValueError("polygon corner %r is not lat,lon" % pair)
        if not (-90 <= lat <= 90 and -180 <= lon <= 180):
            raise ValueError("polygon corner %r is off the map" % pair)
        pts.append((lat, lon))
    if pts and pts[0] == pts[-1]:
        pts.pop()
    if not pts:
        return ""
    if len(pts) < 3:
        raise ValueError("a polygon needs at least 3 corners")
    if len(pts) > MAX_CORNERS:
        raise ValueError("a polygon can have at most %d corners" % MAX_CORNERS)
    pts.append(pts[0])
    return " ".join("%.4f,%.4f" % p for p in pts)


def clean_selection(emma_ids, polygon, min_level, known_codes=None):
    """Check a selection; returns {"emma_ids": [...], "polygon": str, "min_level": int}."""
    if isinstance(emma_ids, str):
        emma_ids = emma_ids.split(",")
    codes = []
    for c in emma_ids or []:
        c = str(c).strip().upper()
        if not c:
            continue
        if not re.fullmatch(r"[A-Z]{2}[0-9]{3,4}", c):
            raise ValueError("%s is not an EMMA code" % c)
        if known_codes and c not in known_codes:
            raise ValueError("unknown area code %s" % c)
        if c not in codes:
            codes.append(c)
    polygon = clean_polygon(polygon)
    try:
        level = int(min_level)
    except (TypeError, ValueError):
        raise ValueError("the level must be 1 to 4")
    if not 1 <= level <= 4:
        raise ValueError("the level must be 1 to 4")
    if not codes and not polygon:
        raise ValueError("choose at least one area or draw a polygon")
    return {"emma_ids": sorted(codes), "polygon": polygon, "min_level": level}


def parse_setup_code(text, known_codes=None):
    """Read the code shown on the map page."""
    code = str(text or "").strip().strip("'\"` ")
    if not code.upper().startswith("MA1:"):
        raise ValueError("a setup code starts with MA1:  (copy it from the map page)")
    parts = code.split(":", 3)
    if len(parts) != 4:
        raise ValueError("the setup code is incomplete; copy the whole line")
    return clean_selection(parts[1], parts[3], parts[2], known_codes)


def describe_selection(sel, names=None):
    names = names or {}
    out = ["%s (%s)" % (c, names[c][0]) if c in names else c for c in sel["emma_ids"]]
    if sel["polygon"]:
        out.append("a polygon with %d corners" % (len(sel["polygon"].split()) - 1))
    return ", ".join(out) + "; warnings from %s up" % LEVEL_NAMES[sel["min_level"]].lower()


def apply_selection(config_dict, sel):
    """Write a selection into [Meteoalarm] of a ConfigObj dictionary."""
    if "Meteoalarm" not in config_dict:
        config_dict["Meteoalarm"] = {}
    sec = config_dict["Meteoalarm"]
    sec["emma_ids"] = ", ".join(sel["emma_ids"])
    sec["polygon"] = sel["polygon"]
    sec["min_level"] = str(sel["min_level"])


def map_link(config_dict):
    """Link to the area map, opening on the station and the current settings.
    Everything personal is in the fragment (after #), which the browser keeps to itself."""
    sec = config_dict.get("Meteoalarm", {}) or {}
    base = str(sec.get("map_url") or MAP_URL).split("#")[0]
    parts = []
    station = station_position(config_dict.get("Station"))
    if station:
        parts.append("lat=%s&lon=%s" % (round(station[0], 4), round(station[1], 4)))
    codes = to_list(sec.get("emma_ids"))
    if codes:
        parts.append("areas=" + ",".join(codes))
    try:
        parts.append("level=%d" % min(4, max(1, int(sec.get("min_level", 2)))))
    except (TypeError, ValueError):
        parts.append("level=2")
    polygon = sec.get("polygon") or ""
    if isinstance(polygon, (list, tuple)):
        polygon = ",".join(polygon)
    pts = str(polygon).split()
    if len(pts) >= 4 and pts[0] == pts[-1]:
        pts = pts[:-1]
    if len(pts) >= 3:
        parts.append("poly=" + ";".join(pts))
    return base + "#" + "&".join(parts)


def interactive_setup(config_dict, weewx_root, skin_dir, out=print, ask=input):
    """Show the link to the area map and ask for the setup code. Returns the selection or None."""
    try:
        with open(os.path.join(skin_dir, "areas", "index.json"), encoding="utf-8") as fh:
            known = json.load(fh)
    except (OSError, ValueError):
        known = None
    out("")
    out("Please select your location(s) from the map. Open this link in a browser on any device:")
    out("")
    out("    " + map_link(config_dict))
    out("")
    out("It opens at your station. Pick your areas or draw a polygon, press 'Copy setup code',")
    out("and paste the code here. (Your station's position is in the part after '#', which")
    out("the browser keeps to itself; nothing about your station is sent anywhere.)")
    for _attempt in range(5):
        try:
            answer = ask("Setup code (or press Enter to skip): ").strip()
        except EOFError:
            return None
        if not answer:
            return None
        try:
            sel = parse_setup_code(answer, known)
        except ValueError as e:
            out("That didn't work: %s. Try again." % e)
            continue
        out("Warning areas: " + describe_selection(sel, known))
        return sel
    return None


def _save_config(config, config_path):
    import shutil
    backup = "%s.%s" % (config_path, time.strftime("%Y%m%d%H%M%S"))
    shutil.copy2(config_path, backup)
    config.write()
    print("Saved in %s (previous version kept as %s)." % (config_path, backup))


def setup_from_command_line(config_path):
    """meteoalarm.py --setup: choose areas again and save them in weewx.conf."""
    config, weewx_root = _load_config(config_path)
    skin_dir = os.path.join(weewx_root, config.get("StdReport", {}).get("SKIN_ROOT", "skins"), "DivumWX", "meteoalarm")
    sel = interactive_setup(config, weewx_root, skin_dir)
    if not sel:
        print("Nothing changed.")
        return 1
    apply_selection(config, sel)
    _save_config(config, config_path)
    print("Restart WeeWX to use the new areas, e.g.  sudo systemctl restart weewx")
    return 0



# ----------------------------------------------------------------------------- DivumWX service

def alerts_document(m, configured=True, error=None):
    """jsondata/meteoalarm.json for alertBar.js. Plain text is unescaped (the page
    escapes it); *_html fields are escaped HTML with line breaks and links."""
    out = []
    if configured:
        for a in m.alerts:
            out.append({
                "level": a.level, "level_name": a.level_name, "severity": a.severity, "color": a.color,
                "type": html.unescape(a.event_type), "type_no": a.type_no, "icon": a.icon,
                "title": html.unescape(a.title),
                "onset": int(a.onset.timestamp()) if a.onset else None,
                "expires": int(a.expires.timestamp()) if a.expires else None,
                "sent": int(a.sent.timestamp()) if a.sent else None,
                "sender": html.unescape(a.sender_name), "web": html.unescape(a.web),
                "advice": a.advice,
                "areas": [{"code": html.unescape(ar["code"]), "desc": html.unescape(ar["desc"]),
                           "country": ar["country"]} for ar in a.areas],
                "languages": [{"code": html.unescape(l["code"]), "name": html.unescape(l["name"]),
                               "headline": html.unescape(l["headline"]),
                               "description_html": l["description"],
                               "instruction_html": l["instruction"]} for l in a.languages],
            })
    return {
        "version": VERSION,
        "generated": int(time.time()),
        "configured": bool(configured),
        "updated": int(m._updated.timestamp()) if configured and getattr(m, "_updated", None) else None,
        "min_level": m.opts.min_level,
        "max_level": m.max_level if configured else 0,
        "areas": [{"code": c["code"], "name": html.unescape(c["name"])} for c in m.area_list] if configured else [],
        "error": error,
        "alerts": out,
    }


class MeteoalarmService(StdService):
    """Writes jsondata/meteoalarm.json for the DivumWX alert bar."""

    def __init__(self, engine, config_dict):
        super().__init__(engine, config_dict)
        dc = config_dict.get("DivumWXCards", {})
        self.enabled = str(dc.get("warnings_source", "")).strip().lower() == "meteoalarm"
        own = config_dict.get("DivumWXMeteoalarm", {})
        html_root = config_dict.get("StdReport", {}).get("HTML_ROOT", "public_html")
        weewx_root = config_dict.get("WEEWX_ROOT", ".")
        if not os.path.isabs(html_root):
            html_root = os.path.join(weewx_root, html_root)
        site_root = str(own.get("site_root", "divumwx")).strip()
        site = os.path.join(html_root, site_root) if site_root else html_root
        self.json_file = os.path.join(site, own.get("json_file", "jsondata/meteoalarm.json"))
        try:
            self.poll_interval = max(120, int(own.get("poll_interval", 600)))
        except ValueError:
            self.poll_interval = 600
        sqlite_root = config_dict.get("DatabaseTypes", {}).get("SQLite", {}).get("SQLITE_ROOT", "archive")
        self.opts = Options(config_dict.get("Meteoalarm", {}), weewx_root=weewx_root,
                            skin_root=config_dict.get("StdReport", {}).get("SKIN_ROOT", "skins"),
                            sqlite_root=sqlite_root if os.path.isabs(sqlite_root) else os.path.join(weewx_root, sqlite_root),
                            station=config_dict.get("Station"))
        if self.opts.cache_file and not os.path.isabs(self.opts.cache_file):
            self.opts.cache_file = os.path.join(weewx_root, self.opts.cache_file)
        self._stop = threading.Event()
        if not self.enabled:
            log.debug("meteoalarm: not the DivumWX warnings source; not running")
            return
        self._thread = threading.Thread(target=self._run, name="DivumWXMeteoalarm", daemon=True)
        self._thread.start()
        log.info("meteoalarm %s: watching %s, writing %s every %ss",
                 VERSION, ", ".join(self.opts.emma_ids) or "-", self.json_file, self.poll_interval)

    def shutDown(self):
        self._stop.set()

    def _run(self):
        while not self._stop.is_set():
            try:
                self.update()
            except Exception as e:  # never take WeeWX down
                log.exception("meteoalarm: update failed: %s", e)
            self._stop.wait(self.poll_interval)

    def update(self):
        m = Meteoalarm(self.opts)
        configured = m.configured
        error = None if configured else "No MeteoAlarm areas chosen: run divumwx_meteoalarm.py --setup"
        doc = alerts_document(m, configured, error)
        if configured and doc["updated"] is None:
            doc["error"] = "No warnings downloaded yet"
        write_json(self.json_file, doc)
        return doc


def write_json(path, doc):
    try:
        import tempfile
        folder = os.path.dirname(path)
        os.makedirs(folder, exist_ok=True)
        fd, tmp = tempfile.mkstemp(prefix=".meteoalarm-", suffix=".tmp", dir=folder)
        with os.fdopen(fd, "w", encoding="utf-8") as fh:
            json.dump(doc, fh, ensure_ascii=False)
        os.chmod(tmp, 0o644)   # readable by the web server, like the other jsondata files
        os.replace(tmp, path)
    except OSError as e:
        log.error("meteoalarm: cannot write %s: %s", path, e)


def _load_config(config_path):
    try:
        import configobj
    except ImportError:
        raise SystemExit("Run this with WeeWX's Python, e.g. ~/weewx-venv/bin/python3 %s"
                         % os.path.abspath(__file__))
    config = configobj.ConfigObj(config_path, encoding="utf-8", interpolation=False, file_error=True)
    weewx_root = os.path.join(os.path.dirname(os.path.abspath(config_path)),
                              os.path.expanduser(config.get("WEEWX_ROOT") or "."))
    return config, weewx_root



# ----------------------------------------------------------------------------- command line

if __name__ == "__main__":
    import argparse
    import sys

    here = os.path.dirname(os.path.abspath(__file__))
    default_areas = os.path.normpath(os.path.join(here, "..", "..", "skins", "DivumWX", "meteoalarm", "areas"))
    p = argparse.ArgumentParser(description="Show MeteoAlarm warnings for a configuration, "
                                            "or (--setup) choose your warning areas again.")
    p.add_argument("--setup", action="store_true", help="choose your areas on the map and save them in weewx.conf")
    p.add_argument("--map-link", action="store_true", help="print the link to the area map for your settings")
    p.add_argument("--config", default=os.path.normpath(os.path.join(here, "..", "..", "weewx.conf")),
                   help="weewx.conf for --setup (default: the one this file is installed under)")
    p.add_argument("--emma-ids", default="")
    p.add_argument("--polygon", default="", help='CAP style "lat,lon lat,lon ..."')
    p.add_argument("--min-level", type=int, default=1)
    p.add_argument("--areas-dir", default=default_areas)
    p.add_argument("--test-file", help="use a saved feed instead of downloading")
    p.add_argument("--json", action="store_true", help="print the meteoalarm.json document DivumWX writes")
    args = p.parse_args()
    if args.setup:
        sys.exit(setup_from_command_line(args.config))
    if args.map_link:
        print(map_link(_load_config(args.config)[0]))
        sys.exit(0)
    logging.basicConfig(level=logging.INFO)
    import tempfile
    opts = Options({"emma_ids": args.emma_ids, "polygon": args.polygon, "min_level": args.min_level,
                    "areas_dir": args.areas_dir, "test_file": args.test_file, "cache_max_age": 0,
                    "cache_file": os.path.join(tempfile.gettempdir(), "divumwx-meteoalarm-test-cache.json")})
    m = Meteoalarm(opts)
    if args.json:
        print(json.dumps(alerts_document(m, m.configured), ensure_ascii=False, indent=1))
        sys.exit(0)
    print("Watching:", ", ".join("%s (%s)" % (a["code"], html.unescape(a["name"])) for a in m.area_list) or "-")
    for a in m.alerts:
        print("%-6s %-20s %s - %s  %s" % (a.level_name, html.unescape(a.event_type), a.onset_text,
                                         a.expires_text, "; ".join(html.unescape(x["desc"]) for x in a.areas)))
    if not m.alerts:
        print("No current warnings at %s level or above." % m.min_level_name)
