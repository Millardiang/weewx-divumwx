# DivumWX Changelog

## Unreleased — for 2.0.0

### Version 2.0.0-RC2
- `DIVUMWX_VERSION` is now `2.0.0-RC2`.
- The last 1.7.0 labels now say 2.0.0-RC2: the `index.html` header and footer
  fallback, the `divumwf.html` brand badge, and `INSTALLATION_GUIDE.md`.
- The install guide downloads from the release tag `ver.2.0.0-RC2` instead
  of the `version-2.0.0-RC1` branch. `tools/check_version.py` passes.

### Lightning distances follow the dashboard's unit system
- Lightning and Boltek Lightning cards (`cardLightning.js` 1.0.2,
  `cardBoltekLightning.js` 1.1.2): Last Distance was always shown in km,
  whatever the unit system. It's now converted to km, mi or NM, and the cards
  update straight away when the unit system is changed.
- Lightning chart (`charts-d3.html` 1.0.3):
  - The coloured distance bands behind the distance line were drawn at their
    km values on an axis in mi or NM (the "very close" band reached 5 mi
    instead of 5 km). They're now converted.
  - The tooltip's Very Close / Close / Moderate / Far category compared the
    converted value with km limits. It now uses the km value.
  - The daily "Nearest" line had the visibility bands. It now has the
    lightning bands.
- Boltek Lightning modal (`modalBoltekLightning.html` 1.3.1): with the ICAO
  unit system the strike map's rings and range buttons were in km while its
  readouts were in NM. The map now has NM ranges (150 / 300 / 500 NM), and NM
  is labelled "NM" throughout.
- Live lightning map (`js/lightningLive.js` 1.0.1): NM labelled "NM", and the
  script now has a `?v=` cache-buster where it's loaded.

### Lightning and Boltek Lightning cards: taller conductor
- The green lightning conductor is now 2.5 times as tall (45 instead of 18
  SVG units), and the flash above it is 27 units shorter to match, so the
  graphic's top and ground point stay where they were
  (`cardLightning.js` 1.0.1, `cardBoltekLightning.js` 1.1.1, in
  `cardsBundleNew.js`; cache-buster updated in all four dashboard pages).

### Fix: card modals collapsing to a thin strip
- On browsers whose scrollbars take up space (Windows, and Linux desktops
  without overlay scrollbars), some card modals shrank to a narrow strip as
  soon as they opened. It was reported on Solar Dial and Earth Daylight, and
  also affected METAR, Constellations and Meteor Showers.
- Cause: the modal reports its page width so the dashboard can size the box.
  That width leaves out the modal's own vertical scrollbar, so each resize
  made the box a scrollbar narrower, which triggered another report, and so
  on until the box collapsed.
- Fix: the dashboard now adds the modal's scrollbar width back before
  resizing the box, and never makes the box narrower than 320 px (or the
  window, if smaller). Changed in `index.html`, `hyperIndex.html`,
  `lexendIndex.html` and `kiosk.html`; no modal pages needed changing.

### Live lightning map (Blitzortung.org network)
- New live lightning map, `js/lightningLive.js` 1.0.0: strikes from the
  Blitzortung.org community network, as they happen, on a D3 Equal Earth map
  centred on the station (weewx.conf's position and name). It opens on the
  station's region and can zoom out to the whole world.
  - Each strike flashes, with brief lines to the detectors that located it,
    as on Blitzortung's own map.
  - A strike within 100 km of the station sends a tracer to the station.
  - Dots are coloured by age, over the last hour.
  - A panel shows the counts for the last minute and the last 10 minutes,
    the strikes within 100 km, and the nearest strike.
  - Hovering over a strike shows its time, position, detector count and
    distance.
  - Night shading.
  - Distances follow the dashboard's units.
  - Colours follow the Equal Earth map's palette, and the dashboard's theme
    and season.
  - It uses the local D3, TopoJSON and coastline files, and needs no key.
  - The feed is only connected while the map is showing.
  - Blitzortung's credit line is always shown. Their data is for private,
    non-commercial use.
- Boltek Lightning modal (`modalBoltekLightning.html` 1.3.0): new
  **Live Network** tab, next to Strike Map and Chart & Records, to compare
  the network's strikes with the station's own. The chosen tab is
  remembered.
- Lightning chart modal (`charts-d3.html` 1.0.2): new **Chart & Records /
  Live Map** buttons, on the lightning chart only. The live map's scripts
  are loaded only when it's first opened. The choice is remembered.
  - Fix: the timeframe highlight no longer clears other buttons' highlights
    (the wind rose period buttons, for one).

### MeteoAlarm weather warnings (Europe)
- New warnings source for the alert bar: **MeteoAlarm**
  (www.meteoalarm.org), the official warnings of the European national
  weather services (the EUMETNET countries, the UK included), with no key
  needed. Until now stations outside the UK had no weather warnings unless
  they had an OpenWeatherMap app_id.
- New service `user.divumwx_meteoalarm.MeteoalarmService`, built from
  Ian Millard's [weewx-meteoalarm](https://github.com/Millardiang/weewx-meteoalarm)
  1.0.0: its feed download, area matching (EMMA codes, geocode aliases and
  polygons, including warnings drawn as their own outline, as the Met
  Office does), caching, warning model and area-map setup code are used
  as they are. Every 10 minutes it writes `jsondata/meteoalarm.json`. It
  only runs when MeteoAlarm is the chosen source.
- Alert bar (`alertBar.js` 1.1.0): with MeteoAlarm chosen, its warnings
  replace OpenWeatherMap's (and the Met Office RSS fallback): one bar per
  warning in its level colour (yellow, orange, red), with weewx-meteoalarm's
  icon for the type, the areas and times (station time); **More** shows the
  official headline, description and instructions in the dashboard's
  language when the service publishes it (else English, else the first
  given), MeteoAlarm's advice for the level, the issuing service and links.
  Warnings that have ended since the file was written are left out. For
  UK locations (a station in the UK, or a warning for UK areas) level 3 is
  labelled **Amber**, the Met Office's name, instead of Orange. The UK
  heat/cold health, flood and aurora bars are unchanged.
- Installer: asks "Weather warnings source: meteoalarm or openweather?"
  (default meteoalarm for stations in Europe, else openweather; kept as
  `[DivumWXCards]` `warnings_source`, published in `archive.json`). For
  MeteoAlarm it prints the link to weewx-meteoalarm's area map, opening at
  the station, and reads the setup code back into `[Meteoalarm]` (shared
  with weewx-meteoalarm, whose areas are kept if already set). The
  OpenWeatherMap app_id questions are skipped and its alerts stay off while
  MeteoAlarm is used. Areas can be chosen again later with
  `divumwx_meteoalarm.py --setup`.
- New: `bin/user/divumwx_meteoalarm.py` 1.0.0,
  `skins/DivumWX/meteoalarm/areas/` (area names, outlines and geocode
  aliases, 2.7 MB, from weewx-meteoalarm), `img/meteoalarm/` (warning type
  icons). Changed: `cardsBundleNew.js`, `install.py`,
  `bin/user/divumwx_cards.py`, `jsondata/archive.json.tmpl`, `lang/en.conf`,
  `lang/en_US.conf`, `INSTALLATION_GUIDE.md`; the bundle's `?v=` in
  `index.html`, `hyperIndex.html`, `lexendIndex.html` and `kiosk.html`
  (kiosk's had been left on an older bundle).

### Fix: archive.json and charts.json failing on the night the clocks go back
- On the autumn clock change (e.g. 01:00-02:00 on 25 October 2026 in the UK)
  archive.json.tmpl failed with "start time ... is greater than stop time"
  and wasn't written for the repeated hour. WeeWX's `$span(time_delta=...)`
  / `$span(hour_delta=...)` works the start out as a local clock time; in
  the repeated hour that time happens twice and the system may pick the
  later one, after the report time. (WeeWX 5.5.2; it depends on the
  system's C library, so not every machine shows it.)
- The templates now use `$divumwx_last(seconds)` (new tag from
  `divumwx_cards.py`) for every trailing window: the last hour, 10 minutes
  and 24 hours in `archive.json.tmpl` and the 24-hour charts in
  `charts.json.tmpl`. It counts back in real seconds, so it can't fail, and
  "last 24 hours" is now 24 real hours across a clock change.
- Changed: `bin/user/divumwx_cards.py`, `jsondata/archive.json.tmpl`,
  `jsondata/charts.json.tmpl`.

### DivumWX extras: its own database again when the station has one
- Restored: stations with DivumWX's own extras database
  (`[DataBindings]` `[[divumwx_extras_binding]]`, normally
  `divumwx_extras.sdb` from earlier versions) keep their extra fields there
  again. DivumwxExtrasService had been saving them only as extra columns on
  WeeWX's main archive table, so the extras database stopped getting
  records (on 31 July 2026 on Steeple Claydon's). Now, when that binding is
  set and its database exists (for SQLite, the file is there), each
  archive record's extras (sunshine time, air density, cloud cover, VPD,
  pollen, piezo rain, ...) are saved as a record of their own in it, in its
  own unit system, replacing any record already there for that time.
  Otherwise they go on the main archive table as before. The log says which
  at startup.
- The installer makes the same choice: missing extra columns are added to
  the extras database when there is one, and the main archive is left
  alone.
- Charts and `archive.json` read the extras from the same place:
  `divumwx_cards.py` publishes `$divumwx_extras_binding` (the extras
  database's binding, or `wx_binding`), used by every extras series in
  `charts.json.tmpl` (air density, cloud cover, pollen, VPD, aerosol,
  pm4_0) and by `archive.json.tmpl`'s pm4_0; its other extras already fall
  back to the extras database.
- Missing extra columns are added by the service itself when WeeWX starts
  (with their daily summaries), in whichever database it uses, and logged.
  Only the 34 the installer adds; the solar/battery/inverter columns are
  never added automatically.
- "No extras fields" is logged once, with the reason, then only at debug
  level, instead of a warning on every archive record.
- Changed: `bin/user/divumwx.py`, `bin/user/divumwx_cards.py`, `install.py`,
  `jsondata/charts.json.tmpl`, `jsondata/archive.json.tmpl`.

### Climate report: colour-coded temperatures
- On **climate.html**, every temperature (mean, high and low, on the
  Monthly and Yearly tabs, including the summary and annual rows) is now
  colour-coded on the same scale as the forecast pages: blue at -10°C or
  below, through teal, green, yellow and orange, to deep red at 42°C or
  above. Each cell is tinted with its temperature's colour, so the figures
  stay readable in light and dark themes, and the colour goes by the value
  whatever unit is shown.
- A colour key under each table's title shows the scale, ticked in °C or
  °F to match the units selected.
- Changed: `climate.html` 1.1.0.

### Up to four webcams, each 24-hour or daylight
- Up to four webcam cards: `cardWebcam` (webcam 1, as before) and the new
  `cardWebcam2`, `cardWebcam3` and `cardWebcam4`. Each has its own title,
  image and display:
  - **daylight**: the live image from sunrise to sunset and the night-time
    page after dark (the webcam card's behaviour until now, and the
    default);
  - **24hr**: the live image all the time.
- Installer: after "Include cardWebcam?" (yes) it asks how many webcams
  (1-4), then each one's title, image path and display (`24hr` or
  `daylight`). Upgrades that already show the webcam card are asked once,
  with the current title and image as the defaults. Settings are in
  `[DivumWXCards]`: `webcam_title` / `webcam_image` / `webcam_mode` for
  webcam 1 (its existing keys), `webcam2_*` to `webcam4_*` for the others,
  and `webcam_count`. Webcams 2-4 go in `enabled_cards` straight after
  `cardWebcam`. They don't use up the optional-card slots.
- `archive.json` `meta.webcams` lists each webcam (`n`, `card`, `title`,
  `image`, `mode`); `webcam_title` / `webcam_image` are still published.
- Webcam 1's card still opens the timelapse, which is made from its image
  (daytime only, unless `[Timelapse]` `capture_at_night = true`). Webcams
  2-4 open the new **modalWebcam.html**: a larger live view refreshed every
  30 seconds, with the camera's title, its display mode and when the image
  last changed (station time); a daylight camera shows the night-time page
  there after dark too.
- Status dot: green while the image loads and has changed in the last 10
  minutes (webcam 1 in daylight mode still goes by its timelapse, as
  before); red at night for daylight cameras, as before.
- Webcam cards 2-4 only run once they're set up, hidden cards don't poll,
  and the cards share one day/night check.
- Changed: `cardWebcam.js` 1.1.0 (in `cardsBundleNew.js`), `index.html`,
  `hyperIndex.html`, `lexendIndex.html` (three new card mounts),
  `install.py`, `bin/user/divumwx_cards.py`, `jsondata/archive.json.tmpl`,
  `INSTALLATION_GUIDE.md`. New: `modalWebcam.html` 1.0.0.

### Boltek Lightning: live strike map
- **modalBoltekLightning.html** opens on a new **Strike Map** tab (the
  chart and records are on the **Chart & Records** tab; the choice is
  remembered). The map is drawn in the Equal Earth projection, centred on
  the station so its range rings are round and north is up, in the Equal
  Earth map's colours (`css/equalEarthMap.css`), with the night side shaded
  the same way and light/dark theme followed.
- It shows the strikes NexStorm lists (the last two hours, from
  `boltek.json`), coloured by age like Blitzortung: white under 5 minutes,
  then yellow, amber, orange, red and dark red by 2 hours. Hovering a
  strike gives its type, time, age, distance and bearing.
- **Each new strike flashes, and a tracer streaks along the great circle
  from the strike to the station**, which pulses when it arrives. Several
  new strikes play in time order. **Replay** plays the strikes on the map
  again in order.
- Range rings with distance labels (300 / 600 / 1000 km, or 200 / 400 /
  600 mi with the dashboard's mile units; picked to fit the strikes and
  changeable), N/E/S/W, and the station named from `archive.json`. A panel
  gives strikes in the last 10 minutes, hour and 2 hours, the nearest in
  the last hour and the latest; a note says when there are no strikes or
  NexStorm's data isn't updating.
- The live strip and map refresh every 10 seconds (boltek.json's rate).
  The map fills the width on a desktop and turns nearly square on a phone.
- Add `?demo=1` to the page's address to try it with a made-up storm.
- Coastlines come from the new `jsondata/countries-50m.json` (world-atlas
  2.0.2, the same source as `countries-110m.json`, which is used if it's
  missing), since 110m is too coarse at this scale.
- Changed: `modalBoltekLightning.html` 1.2.0. New: `jsondata/countries-50m.json`.

### Earthquake map in the Equal Earth projection
- The world map in **modalEarthquakeMap.html** (opened from the Earthquake
  card) is now drawn in the Equal Earth projection instead of Mercator, so
  countries keep their true relative size and the whole world fits the
  width. It looks the same as DivumWX's Equal Earth map: it loads that
  map's own `css/equalEarthMap.css` for its colours (so the two can't drift
  apart), draws the same `countries-110m.json` coastlines, shades the night
  side in the same twilight steps, sits on the card background, and
  follows the light/dark theme.
- Unchanged behaviour: the last 24 hours of USGS earthquakes replay along
  the timeline at the top, pulsing as they appear and sized by magnitude.
  The station is now marked in amber, the timeline shows station time,
  and the hover text gives the time and fixes the "Magnitue" typo.
- The page now uses DivumWX's D3 7.9 and TopoJSON 3 copies. No page uses
  `js/d3.4.2.2.min.js` or `js/topojson.min.js` any more.
- Changed: `modalEarthquakeMap.html` 1.1.1.

### Boltek Lightning card (new, optional)
- Installer: after "Include cardLightning?" (yes), it asks "Is your
  lightning detector a Boltek (NexStorm)?". Yes puts the Boltek Lightning
  card in the lightning slot *instead of* the Lightning card; the answer
  is kept as `[DivumWXCards]` `lightning_detector = boltek | other`. An
  upgrade from before this release that shows a lightning card is asked
  once and the cards are swapped if it's a Boltek.
- A card for Boltek lightning detectors, in the Lightning card's place. It
  shows the same readouts (Current, Today, Last Hour, the year's total,
  All-Time Total, Last Detected, Last Distance) plus **Bearing** of the
  last strike (degrees and compass point). Current is the strikes in the
  last minute; Last Hour comes from the Boltek database. The status dot
  is red when NexStorm's file is missing, unreadable or hasn't changed for
  10 minutes; hovering it says which.
- Data comes from NexStorm's native **`ngxarchive.json`**, which lists the
  recent strikes (a rolling window of about two hours), each with its time
  (`millis`), distance in km (`dist`), bearing (`bng`), position
  (`lat`/`lon`), type and polarity, plus `StormCount`, the detector model
  and NexStorm version. Its `StrikeCount` isn't used: it doesn't follow
  the listed strikes. It's read every 10 seconds by the new
  `user.divumwx_boltek.BoltekLightningService` (`[BoltekLightning]`
  `ngxarchive_file`), which writes `jsondata/boltek.json` for the card,
  including the reason whenever the file can't be read (shown when
  hovering the card's status dot) and the latest 100 strikes with their
  positions.
- Each listed strike is counted once, at its own time: a strike read after
  its 5-minute interval was saved, or listed while WeeWX was stopped, or
  already in the file the first time it's read, is added to the record
  for when it happened, so the hourly chart and records show it there.
  Strikes dated more than 5 minutes ahead of the WeeWX computer's clock
  are ignored. If WeeWX is stopped for longer than the file's window,
  strikes that left the list meanwhile can't be recovered. From the
  strikes' times and distances it works out strikes per minute, the
  average distance over the last hour, approaching/receding (the last 15
  minutes against the 15 before) and today's peak rate. Type 1 is read as cloud-to-ground (+CG /
  -CG by polarity); `compoundType`, `correlated` and `NMEAChecksum` aren't
  identified yet and aren't used. Last hour, 15 and 30 minutes come from
  the database.
- Strikes are kept in DivumWX's own Boltek database,
  `divumwx_boltek.sdb` (SQLite, one record every 5 minutes), separate
  from the WeeWX archive. The installer adds `[BoltekLightning]`,
  `[DataBindings]` `[[boltek_binding]]` and `[Databases]`
  `[[boltek_sqlite]]` silently on every install or upgrade (set-once, hand
  edits kept), and WeeWX creates the database the first time it starts.
  Totals survive restarts and NexStorm session resets. On stations
  without a Boltek the service just waits for the file.
- Clicking the Boltek card opens **modalBoltekLightning.html**, its own
  chart & records page in the same style as the Lightning one:
  - a live strip: status, strike rate, approaching/receding, last 15/30
    minutes and last hour, last strike type and time, its bearing and
    distance, today's peak rate and its time, average distance, active
    storms, and the detector model and NexStorm version;
  - a chart for 24hr (hourly), Week, Month, Year or any past year (daily):
    bars split into cloud-to-ground (CG) and in-cloud (IC) strikes, the
    closest strike as a line on the right axis over the distance bands,
    a tooltip per hour/day, and series that can be hidden from the legend;
  - Records for 24hr, Week, Month, Year and All-time: total strikes (with
    the CG/IC split), busiest hour/day, closest strike and highest rate,
    each with its date.
  Distances follow the dashboard's unit system; times are station time.
- For that, the service also writes `jsondata/boltek_history.json` (hourly
  totals for 48 hours and daily totals for all time, from the Boltek
  database) every 5 minutes, and the database records the closest strike
  and highest strikes-per-minute rate in each 5-minute interval
  (`min_distance`, `max_rate`).
- `divumwx_uninstall_helper.py` removes those three entries; the database
  file is left in place so strike history isn't lost.
- New: `bin/user/divumwx_boltek.py` 1.1.1, `modalBoltekLightning.html`
  1.1.0. Changed: `cardsBundleNew.js`
  (new `cardBoltekLightning` module 1.1.0), `index.html`, `hyperIndex.html`,
  `lexendIndex.html` (card mount), `install.py`, `build_card_bundle.py`,
  `divumwx_uninstall_helper.py`, `lang/en.conf`, `lang/en_US.conf`,
  `INSTALLATION_GUIDE.md`.

### Windy Radar card (new, on by default)
- A new dashboard card showing Windy.com's live weather radar centred on
  the station, laid out like the Webcam card: title bar, status dot and
  time, and a 175px map.
- The radar plays through once whenever the card or the larger map
  loads (on page load, every 15 minutes, and on each tab in the larger
  map), then stops on a still map. Windy's frame can't
  be paused from the dashboard, so it's loaded with `play=true` and,
  after `PLAY_ONCE_SECONDS` (15, set at the top of the card module and of
  modalWindyRadar.html), swapped for the same map without it, loaded
  underneath so there's no flash. In the larger map, if a visitor has
  clicked into the map while it plays, it's left alone. Windy's playback
  speed can't be set.
- Like other cards, clicking anywhere on the card opens the larger map,
  and hovering shows a tooltip saying so (plus what the amber pin is and
  the usual drag hint). The card's map is look-only.
- The card opens **modalWindyRadar.html**, the same map at 1350 × 900
  with tabs for Radar, Satellite, Rain & thunder, Wind and Temperature,
  an "Open in Windy.com" link, and the dashboard's light/dark theme and
  seasonal colours. On a shorter window the map shrinks so the whole
  modal still fits.
- The map is centred on the station's latitude and longitude (weewx.conf
  `[Station]`, via archive.json), shown in the larger map's footer, with
  an amber station pin drawn by DivumWX, its tip at the centre. It's a
  pin, not a dot, so it isn't mistaken for Windy's own pulsing blue
  "your location" dot (each viewer's location as Windy guesses it from
  their IP address, which can't be moved or hidden) or for rain. In the
  larger map the pin hides once a visitor clicks into the map (it would
  no longer be on the station) and returns with the next map load.
  Windy's map only loads once it's on
  screen and laid out, because Windy can centre in the wrong place if it
  loads hidden.
- The Windy link is kept to the parameters confirmed to centre on the
  station: no `product` for radar or satellite (with `product=radar`
  Windy centred on the viewer's IP location instead), no unit settings
  (`metricRain`/`metricTemp`/`metricWind`; the map went back off-centre
  with them, so Windy picks its own units), no `marker` (it marks the
  viewer's location, not the station) and no `detailLat`/`detailLon`.
- A default card: it needs no sensor, so like Moon Phase or Earthquake
  it's included automatically (now 8 automatic cards) and sits after the
  Earthquake card on index.html, hyperIndex.html and lexendIndex.html.
- **Existing installs** get it when upgraded: the installer adds
  `cardWindyRadar` to `[DivumWXCards]` `enabled_cards` once, and records
  that in a new `default_cards_added` key, so if you later remove it from
  `enabled_cards` it stays removed on future upgrades. To turn it on
  without upgrading, add `cardWindyRadar` to `enabled_cards` yourself and
  restart WeeWX.
- Changed: `cardsBundleNew.js` 1.1.0, `hyperIndex.html` 1.1.0,
  `lexendIndex.html` 1.1.0, `index.html` (card mount, bundle hash),
  `install.py`, `build_card_bundle.py`, `lang/en.conf`,
  `lang/en_US.conf`. New: `modalWindyRadar.html` 1.0.0.
- Fixed: the card could show "Station location unavailable" while the
  modal worked. It read `archive.json` once, as the dashboard loaded, and
  gave up if that one read failed (a busy server, or the file caught while
  WeeWX was rewriting it). It now keeps trying (after 5 s, then less often,
  up to once a minute) until it gets the position, and remembers the last
  position in the browser so the radar shows straight away meanwhile;
  hovering the red status dot says why. `cardWindyRadar.js` 1.0.1.

## 1.7.0 — Celestial and Skyfield pages

Two live astronomy pages, linked from the Astronomy navbar and hub, drawn
with D3 from DivumWX's own Skyfield service. They replace the old Cheetah
report pages that needed the weewx-celestial, weewx-skyfield and
weewx-loopdata extensions; none of those, and no pandas, is needed.

### Celestial (Astronomy → Celestial)
- **Countdowns**: the next visible satellite pass (live while it's on),
  sunset or sunrise, astronomical darkness, the next meteor shower's peak
  with the Moon's illumination, and — within 30 days — equinox or solstice,
  Earth's perihelion or aphelion, a supermoon, an eclipse and a comet's
  perihelion. Each counts down and rolls on to the next event by itself.
- **The Geocentric**: Earth at the centre, the Sun, Moon, planets, Proxima
  Centauri and comets at their compass bearing and distance, an hour-long
  trail behind each, and a roster whose distances tick between updates.
- **The Sky Overhead**: the live sky dome — 2,851 stars, the constellation
  figures, the ecliptic, the planets, comets, and any satellite crossing it
  (solid when sunlit, hollow in Earth's shadow) — with what's up now and
  the constellation overhead.
- **The Next Visible Pass**: the sky at the pass's highest point with its
  arc and times, the satellite moving along it live during the pass, and
  every satellite's next pass and next visible pass.

### Skyfield (Astronomy → Skyfield)
- The Moon with its phase, rise and set, and dated chips for the next new
  and full moon, equinox, solstice, eclipse, meteor shower and comet
  perihelion.
- The live sky dome beside each body's day: up now or its next rise or
  set, constellation, magnitude, distance and angle from the Sun, with
  Jupiter's central meridians and Saturn's ring tilt.
- Rise & set ribbons over today's twilight, the Sun's and Moon's paths,
  an orrery, the solar year day by day (sunrise, sunset, solar noon and
  every twilight, clock changes included), the equation of time, the
  analemma, the lunar month with perigee, apogee and supermoons, and the
  almanac table with live altitude and azimuth.
- Checked against weewx-skyfield's own published sample page: rise,
  transit and set to the minute, magnitudes, distances, constellations,
  elongations, Jupiter's CML, Saturn's ring tilt, twilight, the equation
  of time and the Moon's apsides all agree.

### SkyfieldLoopData 0.4.0 (bin/user/divumwx.py)
- New almanac.json fields for the countdowns: the next few sunrises,
  sunsets and astronomical darkness, Earth's perihelion and aphelion, the
  next supermoon, meteor shower peaks, Proxima Centauri and the station's
  position.
- **Satellites**: orbital elements from CelesTrak, falling back to SatNOGS
  and then the TLE API when CelesTrak can't be reached; checksums verified;
  weewx-skyfield's cached copies reused when present. Passes are predicted
  on a background thread.
- **Comets**: elements from the Minor Planet Center, with magnitudes.
- **jsondata/sky.json**: the Skyfield page's daily almanac, computed on its
  own thread every 15 minutes and at local midnight (the year's twilight
  once a day).
- All optional in `[SkyfieldLoopData]`: `[[Satellites]]`, `[[Comets]]`,
  `tle_refresh_hours`, `comet_refresh_hours`, `visible_pass_days`,
  `supermoon_km`, `sky_atlas`, `sky_atlas_refresh`. Defaults: the ISS,
  Tiangong, Terra and NOAA-21; Halley and Hale-Bopp.
- Downloads back off after a failure (six hours after an HTTP 403), and
  the pages say why a satellite or comet has no data rather than guessing.

### Header and languages
- The language selector now works on pages in a subdirectory
  (`cardI18n.js` finds its strings and flags relative to itself).
- The navbar no longer sometimes stays in English when the translations
  load before it does (`siteHeader.js`).

### Pages and files
- New: `celestial/celestial.js` 1.0.0, `celestial/dome.js` 1.0.0,
  `celestial/sky-data.json`, `skyfield/index.html` 1.0.1,
  `skyfield/skyfield.js` 1.0.1, `skyfield/skyfield.css` 1.0.1,
  `tools/build_sky_data.py`.
- Replaced: `celestial/index.html` 1.0.1 (was a placeholder),
  `celestial/celestial.css` 1.0.1.
- Changed: `cardI18n.js` 1.0.1, `siteHeader.js` 1.0.3, `index.html` 1.7.0,
  `divumwf.html`, `bin/user/divumwx.py`, `bin/user/divumwx_version.py`,
  `README.md`, `INSTALLATION_GUIDE.md`.
- Removed: `celestial/astro-nav.js`, `skyfield/astro-nav.js`,
  `skyfield/sky.css`, `skyfield/sky.js` (from the old report pages), and
  `js/daylightMap.js`, which 1.6.0 meant to remove but still shipped.
  Upgrades remove them automatically.

### Installer
- Turns off (`enable = false`) any report that would overwrite the new
  pages every report cycle: the retired DivumWXSkyfield/DivumWXCelestial
  reports, and a weewx-skyfield or weewx-celestial report whose HTML_ROOT
  is DivumWX's `skyfield/` or `celestial/` folder. A report writing
  anywhere else is left alone. Set `enable = true` to undo.
- Changed: `install.py`.

### Credits
- Page designs after John A Kline's weewx-celestial and weewx-skyfield;
  each page carries his copyright line: weewx-celestial / weewx-skyfield
  Copyright (C)2022-2026 by John A Kline (john@johnkline.com).
- Star catalogue and constellation figures from Olaf Frohn's d3-celestial
  (BSD licence).

## 1.6.0 — World Daylight Map

The World Daylight Map that opens from the dashboard's daylight card is
replaced by the flat Equal Earth map from the Astronomy section.

### World Daylight Map
- Live day and night with civil, nautical and astronomical twilight bands,
  the Sun and Moon with their ground tracks and the Moon's phase, and the
  station marked from weewx.conf.
- The flat-map tools: Equal Earth / Mercator, Tissot circles, click a
  country for its true area and map-size ratio, hover for its name.
- Auto view, Face station / Sun / Moon, the time slider and the readouts
  work as on the Equal Earth Map page. The globe stays on that page.
- Follows the dashboard's theme, including the seasonal card colours, and
  switches with it while open.
- No longer loads anything from outside the site. The old map fetched D3 3,
  jQuery, moment, SunCalc, its world map and its city list from public CDNs
  and a CodePen storage bucket, and linked a `css/daylightmap.css` that was
  never shipped. The city-lights layer, which depended on that city list,
  is gone.

### Code
- The map's script and styles move out of `equalEarthMap.html` into
  `js/equalEarthMap.js` and `css/equalEarthMap.css`, shared by both pages.
  A page sets `window.EE_MAP_OPTIONS = { flatOnly: true }` to get the flat
  map without the globe.

### Pages and files
- New: `js/equalEarthMap.js` 1.0.0, `css/equalEarthMap.css` 1.0.0.
  Changed: `modalDaylightMap.html` 2.0.0, `equalEarthMap.html` 1.3.0,
  `index.html` 1.6.0, `divumwf.html`, `bin/user/divumwx_version.py`,
  `README.md`, `INSTALLATION_GUIDE.md`.
- Removed: `js/daylightMap.js` (only the old modal used it). Upgrades
  remove it automatically.

## 1.5.0 — Flat map tools

The measuring tools from the original Equal Earth map are back, shown
whenever the map is fully flat (View → Flat map, or Wrap at 0%).

### Flat map
- **Equal Earth / Mercator** switch, to compare an equal-area map with a
  conformal one. Wrapping back to the globe always starts from Equal Earth.
- **Tissot circles**: a grid of identical 500 km circles. Equal Earth keeps
  their area and stretches their shape toward the edges; Mercator keeps them
  round and lets them grow toward the poles.
- **Click a country** for its true area in km² and its size on the map
  compared with the same area at the equator. Equal Earth reads ×1.00
  everywhere; under Mercator Greenland reads about ×16, Russia about ×5,
  and Antarctica, which reaches the pole, ∞. Areas are measured on the
  whole country, even where Mercator's top and bottom edges cut it off.
  Click the country again, or the sea, to clear it.
- **Hover** over a country to see its name, with the cursor's latitude
  and longitude in the readout.
- The day and night shading, Sun and Moon tracks and station marker all
  carry on as before on both flat projections.

### Files changed
`equalEarthMap.html` 1.2.0, `index.html` 1.5.0, `divumwf.html`,
`bin/user/divumwx_version.py`, `README.md`, `INSTALLATION_GUIDE.md`.

## 1.4.0 — Equal Earth globe

The Equal Earth Map page now opens as a rotating globe, with the Sun and Moon
and their ground tracks. The flat Equal Earth map is still one click away.
The navbar also gains a full-screen button.

### Globe
- **Flat map / Globe.** The map wraps smoothly from the Equal Earth
  projection onto a globe and back. A Wrap slider can stop it partway.
- **Auto view** (on by default) keeps the station, and the Sun and Moon
  whenever they are above the station's horizon, on the visible side of the
  globe, and follows them as they move. Dragging, Spin, the arrow keys or
  the Face station / Face Sun / Face Moon buttons switch it off; press
  Auto view to return.
- **Sun and Moon tracks** show the points each passes directly over during
  one full trip round the Earth (24 hours for the Sun, about 24 h 50 min for
  the Moon), with a dot for each hour.
- **The Moon** is drawn with its current phase. New readouts give the
  sublunar point, the Moon's altitude at the station, and the phase with the
  percentage lit.
- **Sun at station** gives the Sun's altitude with Day, civil, nautical or
  astronomical twilight, or Night.
- A **Time** slider moves the Sun and Moon up to 12 hours either side of now.
- Day and night shading, tracks and readouts update every 5 seconds, and as
  soon as you return to the tab.
- Drawn on a canvas for smooth rotation. The Mercator comparison, Tissot
  circles and click-for-area readout from 1.3 are removed.

### Station position
- The page takes the station's latitude and longitude only from weewx.conf
  (`[Station]`), via `archive.json` (`meta.latitude` / `meta.longitude`).
  The coordinates previously written into the page are gone, including the
  ones its auto theme used before `loop.json` reports day or night. Until
  `archive.json` loads, the globe centres on the daylit side and the station
  readouts show a dash.

### Full screen
- A **full-screen button** on the navbar, next to the theme button, on the
  dashboard and on every astronomy page. The choice is remembered, so the
  site returns to full screen on the first click, tap or key press on each
  new page (browsers only allow full screen from a user action). Press the
  button again, or Esc, to turn it off. Browsers without the Fullscreen API,
  such as Safari on iPhone, don't show the button.
- The Skyfield and Celestial pages don't load `siteHeader.js`, so their
  `astro-nav.js` removes the button there rather than show one that does
  nothing.

### Files changed
`equalEarthMap.html` 1.1.0, `astronomy.html` 1.1.1,
`astronomyNavbar.html` 1.2.0, `navbar.html` 1.0.2, `header.css` 1.0.1,
`siteHeader.js` 1.0.2, `skyfield/astro-nav.js`, `celestial/astro-nav.js`,
`index.html` 1.4.0, `divumwf.html`, `bin/user/divumwx_version.py`,
`README.md`, `INSTALLATION_GUIDE.md`.

## 1.3.1 — Maintenance release

Fixes the Equal Earth map's day and night shading, which stopped updating
after the page was opened.

### Equal Earth map
- **Night and twilight shading now update every minute.** In 1.3.0 the
  first one-minute refresh failed with a script error, so the shading stayed
  where it was when the page loaded until the page was reloaded. The
  subsolar point readout kept updating, which hid the problem.
- The map also redraws as soon as you return to its tab, or come back to it
  with the browser's Back button. Browsers slow or pause timers in background
  tabs, so the shading could otherwise be up to several minutes out of date.

### Files changed
`equalEarthMap.html` 1.0.1, `index.html` 1.3.1, `divumwf.html`,
`bin/user/divumwx_version.py`, `README.md`, `INSTALLATION_GUIDE.md`.

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
