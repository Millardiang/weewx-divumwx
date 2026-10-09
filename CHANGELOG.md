# DivumWX Changelog

## 1.3.0 — Equal Earth map

A new **Equal Earth Map** page in the Astronomy section: an interactive world
map in the equal-area Equal Earth projection (Šavrič, Jenny & Patterson,
2018), drawn with D3.

### Map
- Day and night drawn live, with civil, nautical and astronomical twilight
  bands and a marker at the subsolar point (where the Sun is overhead).
  Recalculated from the current time every minute.
- The station is marked from `archive.json` (`meta.latitude` /
  `meta.longitude`), falling back to the page's built-in coordinates.
- Click a country for its true area in km² and its size on the map relative
  to the equator. This reads ×1.00 on Equal Earth. A **Mercator** toggle
  shows the difference (Greenland ×8.6, Russia ×4.8).
- Optional **Tissot circles** (500 km radius) show how each projection
  distorts area and shape.
- Centre the map on 0°, the station or 150°E, drag it sideways, use the
  arrow keys, or let it rotate.
- Follows the DivumWX theme (light, dark, auto) and uses the shared
  astronomy navbar.

### Data
- Country boundaries come from the new `jsondata/countries-110m.json`
  (Natural Earth 1:110m, from world-atlas 2.0.2). The existing
  `worldmap.json` has no country names, so it is left unchanged.
- Uses the bundled `js/d3.7.9.0.min.js` and `js/topojson.3.0.2.min.js`, so
  no new external scripts are loaded.

### Pages and files
- The astronomy navbar has an **Equal Earth Map** link after
  Visualisations, and the Astronomy hub page has an Equal Earth Map card.
- "Equal Earth Map" is translated in all 30 language files in
  `skins/DivumWX/lang`.
- New: `equalEarthMap.html` 1.0.0, `jsondata/countries-110m.json`.
  Changed: `astronomy.html` 1.1.0, `astronomyNavbar.html` 1.1.0,
  `index.html` 1.3.0, `divumwf.html`, `skins/DivumWX/lang/*.conf`,
  `bin/user/divumwx_version.py`, `README.md`, `INSTALLATION_GUIDE.md`.

## 1.2.0 — Live gauges

The Live Gauges and Range Gauges pages are replaced by a single **Live
Gauges** page using the gauges from weewx-carbonsteel-series, fed by DivumWX's
own data.

### Gauges
- Twelve gauges: temperature (outside or inside), dew point (or feels like,
  wind chill, heat index, humidex), humidity (outside or inside), barometer,
  wind speed, wind direction, wind rose, rain today, rain rate, UV index,
  solar radiation and cloud base. A gauge whose sensor reports nothing, or
  whose card is turned off in the installer, is left out.
- Today's low and high on each gauge, with their times; 10-minute average
  wind and gust; the range of wind direction over the last 10 minutes;
  3-hour pressure change and tendency; rain this month and year.
- A 24-hour sparkline under each gauge; click it for a detail chart.
- **Year at a glance**: a calendar heat-map of the last 366 days (max, mean
  and min temperature, rain, max gust, UV, solar).

### Data, theme and units
- Live readings come from `jsondata/loop.json`, today's highs and lows,
  rain totals and 10-minute wind from `archive.json`, and the 24-hour
  history, wind rose and calendar from `charts.json`. No CarbonSteel
  service or `realtime.json` is needed.
- The gauges follow the DivumWX theme: light gives chrome bezels and beige
  faces, dark gives black-metal bezels and carbon-fibre faces, and auto
  switches with day and night.
- Units follow the navbar's unit selector, including ICAO (knots, hPa,
  cloud base in feet) and Beaufort.
- The wind rose covers the last 24 hours, from the hourly averages in
  `charts.json`.

### Pages and files
- The navbar has one **Live Gauges** link. `gauges2.html` now just forwards
  to `gauges.html`, so old links and bookmarks still work.
- New: `csGauges.js` 1.0.0, `csGauges.css` 1.0.0. Changed: `gauges.html`
  1.1.0, `gauges2.html` 1.1.0, `navbar.html` 1.0.1, `index.html` 1.2.0.
- Removed: `iopctrl.js` and `gaugeDiverging.js` (only the old gauge pages
  used them). Upgrades remove them automatically.

## 1.1.0 — Feature release

Installer now offers the choice of solid colour or seasonal switching theme for body background.

## 1.0.3 — Maintenance release

Renames the Aviation unit group to **ICAO** and corrects its units.

### Units
- **Aviation is now ICAO.** The unit selector reads
  "ICAO (°C, kt, hPa, NM, ft)". A browser that had Aviation selected is
  moved to ICAO automatically on its next visit; no action is needed.
- **Pressure / altimeter setting in hPa** (was mbar).
- **Horizontal speed in knots (kt)** and **horizontal distance in nautical
  miles**, now labelled "NM" instead of "nm" on the charts, gauges and
  forecast pages.
- **Altitude / elevation in feet.** Under ICAO, the barometer card's
  Station Alt row and the climate page's ELEV line show the station
  elevation in ft. All other unit groups keep metres.
- **Cloud base in feet.** The current conditions card shows cloud base in ft
  under ICAO (UK and US already used ft).
- **Visibility in NM.** Under ICAO, visibility on the current conditions card
  and in the METAR modal is shown in nautical miles, converted from the
  METAR report; unlimited visibility ("10+" statute miles) shows as
  "> 8.7 NM".
- **METAR airport distance in NM.** Under ICAO, the METAR modal gives the
  distance to the reporting airport in NM, with km in brackets.
- **Vertical speed in ft/min.** Every unit group now defines `alt` and
  `vspeed` units in `units.js`, with `m2ft`, `ms2fpm`, `fmtAlt` and
  `fmtVSpeed` helpers. No page displays a vertical speed yet.

### Versioning
- Release version set to 1.0.3 in `bin/user/divumwx_version.py`, which
  had not been raised for 1.0.2.

### Files changed
`units.js` 1.0.1, `header.js` 1.0.1, `siteHeader.js` 1.0.1,
`charts-d3.html` 1.0.1, `climate.html` 1.0.1, `gauges.html` 1.0.1,
`divumwf.js` 1.0.1, `modalMetar.html` 1.0.1,
`cardsBundleNew.js` 1.0.3 (cardBarometer 1.0.1, cardCurrent 1.0.1),
`index.html` 1.0.3, `divumwf.html`, `README.md`, `INSTALLATION_GUIDE.md`,
`bin/user/divumwx_version.py`.

## 1.0.2 — Maintenance release

Fixes timezone shift issues for forecast, earth daylight and terminator.

## 1.0.1 — Maintenance release

Fixes the findings from the 1.0.0 APT upgrade test (Debian 13, WeeWX 5.5.1
from the official APT repository). No change to dashboard behaviour.

### Upgrade behaviour
- **Prompts now default to your existing settings.** Every installer prompt
  (LiveData interval, alert/METAR poll intervals, forecast model, METAR
  airport, hemisphere/England/UK, UKHSA and Met Office regions) offers the
  value already in `weewx.conf`; press Enter to keep it. Previously most
  prompts offered generic or blank defaults.
- **Changed answers are applied.** A new value typed at an upgrade prompt
  used to be silently ignored because existing settings are never
  overwritten; deliberate changes are now written and listed.
- The stored OpenWeatherMap key is never shown; Enter keeps it.
- The hemisphere and England answers are now saved in `[DivumWXCards]`
  (alongside `in_uk`) so later upgrades can offer them as defaults.

### Files and permissions
- **Obsolete frontend files are removed.** The installer writes
  `.divumwx-manifest.txt` into the web root and, on the next upgrade,
  removes files the previous release installed that the new one no longer
  ships. Upgrading from 1.0.0 or a beta removes known retired files and
  lists any other leftover page/script files for you to review; your own
  images, timelapse output and generated data are never touched.
- **Ownership is set automatically.** After `sudo weectl extension install`
  the web root is owned by the WeeWX service account (e.g. `weewx`), so no
  manual `chown` is needed.
- The installer no longer changes ownership of unrelated files in the
  `weewx.conf` directory; only the files it copies there.

### Versioning
- `weectl extension list` now reports the real release version (1.0.0
  reported `0.1.0`). `bin/user/divumwx_version.py` is the single source,
  used by the installer, logged at startup, and shown in the dashboard
  footer via `archive.json` (`meta.divumwx_version`).
- New `tools/check_version.py` release check.

### Other
- Installation guide rewritten: tagged download URLs instead of the moving
  `main` branch, Debian/APT first, upgrade steps, corrected UKHSA prompt.
- Missing-dependency error now gives the APT command first, then pip.
- SkyfieldLoopData (0.1.1): the always-null named-star fields no longer log
  a startup warning that referred to weewx-skyfield; logged at debug level.

## 1.0.0 — 2026-09-21 — Full Release

First full release. Highlights:

- **Collection version reset to 1.0.0** in `index.html`, marking this as the
  1.0.0 baseline for the whole site.
- **Every file's version reset to 1.0.0** (previously a mix of 0.0.1–0.3.0
  across different files) to mark this common release point. Going forward,
  each file's version increments independently on its own amendments (see
  Versioning scheme above).
- **Comment cleanup across the entire collection.** Removed explanatory
  "why"/narrative comments from every `.html`, `.js` and `.css` file. Kept:
  - License, copyright and version-header banners.
  - Short section-divider comments (e.g. `// ===== Section =====`) used to
    organise code for readability.
- **Added missing license/version banners** to five files that had none:
  `astro-nav.js`, `moonDisc.js` (version line added to its existing banner),
  `modalSkymap.html`, `modalZodiacMap.html`, `sunDisc.html`.
- **Release/version labels updated for 1.0.0:**
  - `index.html` footer: `DivumWX-H-Beta-3` → `DivumWX v1.0.0`.
  - `divumwf.html` version badge: `v0.0.1` → `v1.0.0`.

### Known issues in 1.0.0 (fixed in 1.0.1)
- `weectl extension list` reports `divumwx 0.1.0`.
- The bundled installation guide still describes the beta and links to the
  `main` branch archive; install from the `ver.1.0.0` tag instead.
- Upgrade prompts don't default to existing values; re-enter your METAR
  code and region answers when upgrading.
- Frontend files removed since a beta are not deleted.
- After a `sudo` install, run `sudo chown -R weewx:weewx /var/www/html/divumwx`.
