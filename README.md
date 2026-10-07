# weewx-ecowitt_console_emulator — an Ecowitt console emulator skin for WeeWX 5

A full-screen, landscape dashboard for a wall or desk tablet, laid out like the
Ecowitt WH2560 / HP2560 console, in a navy-blue palette: outdoor temperature
ring, wind dial, selectable temperature/humidity channel, soil moisture / leaf
wetness and lightning line, leak indicators, PM2.5 and CO₂, sun arc and moon
phase, rain (traditional or piezo), and barometer with 3-hour trend and a
pressure-tendency forecast (a moon replaces the sun between sunset and sunrise). The sky panel has a sun arc and, inside it, a moon track with moonrise/moonset times; the moon is worked out in the browser for the station's position, so WeeWX doesn't need the `ephem` package. The readings row includes VPD, always in kPa. Like the console, signal bars sit next to each
reading and a battery icon only appears when a battery is low.

It works with any WeeWX 5 station fed by an Ecowitt gateway or console, whatever
driver brings the data in. It needs no other extension: the skin has its own
database, its own live-data service and its own chart data.

If the **[weewx-EcowittGateway](https://github.com/Millardiang/weewx-ecowittGateway)**
driver/service happens to be installed, the skin also makes use of what it
provides: its `ecwLoop.json` live file, its `rain_source` setting, and the extra
fields it adds (10-minute wind direction, max daily gust, feels-like, the second
rain gauge in `p_rain`, sensor batteries and signal). Without it the skin uses
its own live file and works the extras out itself (see
[Fields](#fields-the-dashboard-uses)). Only the sensors you actually have are shown.

### Layouts

The page picks a layout from the shape of the screen and switches when the
device is rotated:

| Layout | Used when | Design size | Arrangement |
|---|---|---|---|
| Landscape | wider than tall | 1280 × 800, fitted to the screen | the console layout |
| Portrait | tablet held upright (600 px wide or more) | 800 × 1518, fitted to the screen, no scrolling | outdoor and wind gauges side by side, readings, sun/moon, channel rings, rain, barometer |
| Phone | narrow screens (under 600 px) | 540 wide, fitted to the width, scrolls | one column; readings in 3 × 2; header on two rows; a narrower sun/moon drawing with larger text |

**Settings → Layout** can force one of them (Auto, Landscape, Portrait, Phone);
the choice is kept in the browser. "Add to Home screen" apps can rotate freely.

## What's in the package

| Part | What it does |
|---|---|
| `skins/EcowittConsoleEmulator` | The skin: the dashboard page, chart data (day/week/month/year), CSS/JS, translations (`lang/`), manifest for "Add to home screen" |
| Its own database | `ecowitt_console_emulator.sdb` (binding `ecce_binding`), kept by the `EcowittConsoleEmulatorArchive` service: the same schema as the station's main database plus extra fields for Ecowitt sensors. See [The skin's database](#the-skins-database) |
| `bin/user/ecowitt_console_emulator.py` | **Search-list extension** that builds the dashboard data (today's highs/lows, rain totals for hour/day/week/month/year/event, 10-min average wind, 3 h pressure trend, feels-like, lightning summary) and the **live service** that writes `live.json` |
| `install.py` | Installer for `weectl extension install` |

### Two data feeds

* `ecowitt.json` – written by WeeWX every archive interval (and embedded in
  `index.html`). Highs/lows, rain totals, trends, almanac.
* **Live data, every few seconds** – the skin's `EcowittConsoleEmulatorLive` service
  writes each loop packet to **`live.json`**, whatever the driver. If
  weewx-EcowittGateway is installed, the installer offers its **`ecwLoop.json`**
  (its `[[loop_json]]` option) instead, and the skin's service is switched off.
  Either way the browser converts the units. Live data is the only source of
  **battery and signal** levels, since those aren't archived.

The page uses live values while they are fresh and falls back to the archive
otherwise. The Wi-Fi icon in the header shows which: green = live, yellow =
archive only, red = stale.

## Requirements

* WeeWX 5.4 or later, with any driver for an Ecowitt gateway or console
* Nothing else. weewx-EcowittGateway is optional: if it is already installed,
  the installer reads its settings and can use its live file.
* For live updates the tablet must load the page from a web server that serves
  WeeWX's `public_html` directly (e.g. nginx/Apache on the WeeWX machine).

### Fields the dashboard uses

The standard WeeWX fields (`outTemp`, `outHumidity`, `dewpoint`, `windSpeed`,
`windDir`, `windGust`, `rain`, `rainRate`, `barometer`, `pressure`, `inTemp`,
`inHumidity`, `radiation`, `UV`, `extraTemp1–8`, `extraHumid1–8`, `soilMoist1–4`,
`leafWet1–2`, `pm2_5`, `co2`, `lightning_strike_count`, `hail`) are enough for the
dashboard. The names below are the ones weewx-EcowittGateway uses; another driver
may use other names, which `[[FieldMap]]` in `skin.conf` maps. When a field is
missing the dashboard works it out or leaves it out:

* feels like – worked out from temperature, humidity and wind
* 10-minute wind direction – the vector average of the last 10 minutes of the archive
* max daily gust – the highest gust in today's archive
* VPD – worked out from temperature and humidity
* period rain totals – summed from the archive
* piezo gauge – `hail` when `p_rain` isn't there
* battery and signal – shown only for sensors whose fields are found

| Dashboard | Fields (weewx-EcowittGateway names) |
|---|---|
| Outdoor / feels like / dew point | `outTemp`, `outHumidity`, `feelslike`, `dewpoint` |
| Wind, 10-min direction, max daily gust | `windSpeed`, `windDir`, `windGust`, `windDir10`, `maxdailygust` |
| Channel rings | `inTemp`/`inHumidity` (WH25), `extraTemp1–8`/`extraHumid1–8` (WN31) |
| Soil / leaf | `soilMoist1–16` (WH51), `leafWet1–8` (WN35) |
| Leak, PM2.5, CO₂ | `leak_1–4` (WH55), `pm25_1–4` (WH41/43), `pm2_5` and `co2` (WH45) |
| Lightning | `lightning_dist`, `lightning_disturber_count` (last strike time), `lightningcount` (today); archive `lightning_strike_count` |
| Tipping gauge | `rain`, `rainRate`, `eventRain`, `hourRain`, `dayRain`, `weekRain`, `monthRain`, `yearRain` |
| Piezo gauge | `p_rain` (or `hail`, which the standard database archives), `p_rainrate`, `erain_piezo`, `hrain_piezo`, `drain_piezo`, `wrain_piezo`, `mrain_piezo`, `yrain_piezo` |
| Batteries / signal | weewx-EcowittGateway's names (other drivers: list yours in `[[Sensors]]`), e.g. `ws90_batt`, `wh40_batt`, `batteryStatus1`, `soilMoistBatt1`, `leafWetBatt1`, `lightning_Batt`, `co2_Batt`, `leak_Batt1`, `pm25_Batt1`, with `<sensor>_sig` / `<sensor>_ch<n>_sig` |

When weewx-EcowittGateway is set to `rain_source = piezo`, `rain`/`rainRate`
hold the piezo gauge, and the dashboard treats them that way.

## Install

```bash
weectl extension install weewx-ecowitt_console_emulator.zip
sudo systemctl restart weewx
```

The installer asks which rain sensor(s) the station has:

```
Which rain sensor does this station have?
  tipping - traditional tipping-bucket gauge (WH40, WS69, WN20 ...)
  piezo   - piezoelectric haptic gauge (WS90, WS85 ...)
  both    - both; the rain section shows a column for each
Rain sensor (tipping/piezo/both) [tipping]:
```

The first letter is enough. With **both**, the rain section becomes a table with
a **Tipping** and a **Piezo** column (Daily, Rate, Event, Hourly, Weekly, Monthly,
Yearly), and the rain chart shows a column for each.

Live data then comes from the skin's own `live.json`; no question is asked.

**If weewx-EcowittGateway is installed** (an `[EcowittGateway]` section in
weewx.conf), the rain question defaults to the driver's `rain_source`, and a
second question is asked:

```
Where should the tablet get live (every few seconds) data from?
  driver - the driver's ecwLoop.json (its loop_json option, already on)
  skin   - this skin's own live.json
Live data source (driver/skin) [driver]:
```

With **driver**, the skin reads the driver's `ecwLoop.json` and its own live
service is switched off. If the driver's `loop_json` is off, the installer turns
it on in the web folder (METRICWX units). If it's written somewhere the web
server can't reach (the `data`, `tmp` or a custom folder), the installer uses
the skin's own `live.json` instead. If you answer `both` to the rain question
but the driver only records one gauge, the installer says so.

For unattended installs, answer on the command line (`--live` only matters
when weewx-EcowittGateway is installed):

```bash
weectl extension install weewx-ecowitt_console_emulator.zip --yes --rain=both
weectl extension install weewx-ecowitt_console_emulator.zip --yes --rain=both --live=driver
```

The answers are stored in `weewx.conf` under
`[StdReport][[EcowittConsoleEmulatorReport]][[[Extras]]]` (`rain_sensors`, `live_url`).
Reinstalling offers your current answers as the defaults.

This adds (shown without weewx-EcowittGateway; with its `ecwLoop.json`,
`live_url = ../ecwLoop.json` and `enable = false`):

```ini
[StdReport]
    [[EcowittConsoleEmulatorReport]]
        skin = EcowittConsoleEmulator
        HTML_ROOT = public_html/console
        enable = true
        [[[Extras]]]
            rain_sensors = both
            live_url = live.json

[EcowittConsoleEmulator]                # the skin's own live service
    enable = true              # false when weewx-EcowittGateway's ecwLoop.json is used instead
    report = EcowittConsoleEmulatorReport
    interval = 5
    filename = live.json

[Engine]
    [[Services]]
        report_services = ..., user.ecowitt_console_emulator.EcowittConsoleEmulatorLive
```

Then open `http://<your-weewx-host>/weewx/console/` (or wherever your
`public_html` is served) on the tablet.

### Full screen

Every panel (Charts, Radar, Sensors, Settings) opens over the whole screen inside
the dashboard, so the page never leaves full screen. Browsers only allow full
screen after a tap, so the first touch anywhere switches it on, and again if the
browser leaves it (Settings → "Full screen on touch" turns this off). Escape or ×
closes a panel. On an iPhone, which has no full-screen mode for web pages, use
Share → "Add to Home Screen".

### Tablet tips

* **Android:** Chrome → ⋮ → *Add to Home screen* opens it full screen. For a
  permanent wall display, a kiosk browser (e.g. Fully Kiosk Browser) works well.
* **iPad:** Safari → Share → *Add to Home Screen*.
* Settings (⚙) → *Keep screen awake* stops the screen dimming (Wake Lock API;
  needs HTTPS or `localhost` on most browsers).

## Using the dashboard

| Toolbar button | Action |
|---|---|
| Charts | Full-screen charts with Day, Week, Month and Year tabs: temperature, humidity, VPD (kPa), wind, wind direction (day), rain, barometer, solar radiation, UV. Rain is one chart with a separate column for the tipping gauge (blue) and the piezo sensor (violet) in each hour, day or month and the difference in the tooltip. The legend gives each gauge's total for today, this week, this month or this year (by tab): the same figures as the rain table. The bars cover the chart's whole span (27 hours, 7 days, 31 days, 12 months). Every chart has a tooltip: hover with a mouse, or tap/drag on a touch screen. Drawn in the current theme and display units |
| Theme | Navy (console) → Black (OLED / night) → Light |
| CH | Switches the second pair of rings between the temperature/humidity channels that are reporting: Indoor, then each WN31 (CH1–CH8). Greyed out when only one channel reports |
| Battery | Battery and signal of every sensor that reports them |
| Refresh | Reload data now |
| Settings | Language, units, layout, theme, 12/24 h clock, seconds, keep awake, full screen on touch, data status |

Tapping works too: the channel gauges cycle channels, the soil/leaf line cycles
through every soil-moisture and leaf-wetness channel, the PM2.5 readout cycles PM
sensors, the barometer reading switches between REL and ABS, with `rain_sensors = auto`
and both gauges reporting, the tag under the rain icon switches between them,
and the red battery icon in the header opens the sensor list.

On the wind gauge, the blue arrow is the current direction and the small grey
arrow is the 10-minute average direction.

All times (clock, date, sunrise/sunset, moonrise/moonset, chart axes) are shown in
the **station's** time zone, whatever the time zone of the tablet or phone viewing
the page.

## Configuration (`skins/EcowittConsoleEmulator/skin.conf`, `[Extras]`)

* `title` – the name at the top of the dashboard; empty = the station name (`location` in `[Station]` of weewx.conf)
* `language` – the default language (see [Languages](#languages-same-as-weewx-divumwx))
* `theme`, `clock_24h`
* `timezone` – the station's time zone is read from the WeeWX machine; set it here
  (e.g. `timezone = Europe/Paris`) only if that comes out wrong
* `windy_radar`, `windy_mode`, `windy_zoom` – the **Radar** button at the top left shows
  Windy's radar centred on the station's `[Station]` latitude/longitude, with a
  marker on the station. `windy_mode = panel` (default) opens the map in a panel
  over the dashboard, so a full-screen tablet never leaves the page; the map's
  wind and temperature units follow the dashboard's unit preset, and the panel
  has an "Open in Windy" link. `windy_mode = link` opens windy.com instead.
  `windy_embed_url` / `windy_url` can be changed (`{lat}`, `{lon}`, `{zoom}`,
  `{wind}`, `{temp}` are filled in). The tablet needs internet access for this.
* `rain_sensors` – `tipping`, `piezo`, `both` or `auto` (set by the installer); `baro_mode` – `REL` or `ABS`
* `[[Links]]` – chips at the top left, e.g. `Windy = https://www.windy.com/station/…`
* `[[FieldMap]]` – only needed if your driver names a field differently (or you've remapped it)
* `[[Channels]]` – which T&H channels the CH button cycles through, their names and
  sensor ids, e.g. `Greenhouse = extraTemp1, extraHumid1, wn31_ch1`
* `[[SoilChannels]]`, `[[LeafChannels]]`, `[[LeakChannels]]`, `[[PMChannels]]` – custom labels/fields
* `[[Sensors]]` – battery/signal list; `kind` is `binary` (0 = OK), `level` (0–5, 6 = DC) or `volt:<low volts>`

### Languages (same as weewx-divumwx)

Everything on the dashboard can be shown in any of the 30 languages weewx-divumwx
has: labels, tooltips, messages, panels, charts, compass points, Beaufort names,
moon phases, and day and month names.

| Code | Language | Code | Language | Code | Language |
|---|---|---|---|---|---|
| `ar` | العربية (Arabic) | `eu` | Euskara (Basque) | `nl` | Nederlands (Dutch) |
| `br` | Brezhoneg (Breton) | `fi` | Suomi (Finnish) | `no` | Norsk (Norwegian) |
| `ca` | Català (Catalan) | `fr` | Français (French) | `pl` | Polski (Polish) |
| `cn` | 中文 (Chinese, simplified) | `gr` | Ελληνικά (Greek) | `pt` | Português (Portuguese) |
| `cy` | Cymraeg (Welsh) | `hi` | हिन्दी (Hindi) | `sv` | Svenska (Swedish) |
| `cz` | Čeština (Czech) | `hu` | Magyar (Hungarian) | `ta` | தமிழ் (Tamil) |
| `da` | Dansk (Danish) | `is` | Íslenska (Icelandic) | `th` | ไทย (Thai) |
| `de` | Deutsch (German) | `it` | Italiano (Italian) | `tr` | Türkçe (Turkish) |
| `en` | English | `ja` | 日本語 (Japanese) | `uk` | Українська (Ukrainian) |
| `en_US` | English (US) | `es` | Español (Spanish) | `ur` | اردو (Urdu) |

Pick one on the tablet under **Settings → Language**. Like the unit choice, it is
stored in the browser under DivumWX's key, `dashboardLanguage`, so if this skin and
DivumWX are served from the same web server they share one language. `language` in
`skin.conf` `[Extras]` sets the default for browsers that haven't chosen yet.

The translations are in `skins/EcowittConsoleEmulator/lang/<code>.json`, keyed by
the English text; English is built into the page. Where DivumWX already translates
the same text, its wording is used, so the two skins read alike. Names you set
yourself in `skin.conf` (channels, sensors, links) are shown as you wrote them.
In Arabic and Urdu the console layout stays the same, and each label, row and
message reads right to left. Labels that are longer in some languages are made a
little smaller, or put on two lines, so they fit their place on the console.

### Units (same model as weewx-divumwx)

Like weewx-divumwx, the data feeds are always written in **METRICWX** (°C, m/s,
mm, hPa, km), whatever `unit_system` your station or `weewx.conf` uses. The
browser converts them to the same display presets DivumWX offers (Beaufort is
shown as its own reading on the dashboard instead of as a unit choice; a
DivumWX `beaufort` choice shows metric units here):

| Preset | Temp | Wind | Pressure | Rain | Distance |
|---|---|---|---|---|---|
| `uk` (default) | °C | mph | hPa | mm | km |
| `us` | °F | mph | inHg | in | mi |
| `metric` | °C | km/h | hPa | mm | km |
| `scandi` | °C | m/s | hPa | mm | km |
| `canada` | °C | km/h | kPa | mm | km |
| `icao` | °C | kt | hPa | mm | NM |

Pick one on the tablet under **Settings → Units**. The choice is stored in the
browser under DivumWX's key, `dashboardUnitSystem`, and announced with the same
`unitsystemchange` event. If this skin and DivumWX are served from the
same web server, they share one unit choice. `unit_system` in `skin.conf`
`[Extras]` sets the default for browsers that haven't chosen yet.

The charts are drawn in the browser too, so they follow the unit preset and the theme.

## The skin's database

The skin keeps its own database, so it never changes the station's main one.

**Created only if it doesn't exist.** The installer checks for the
`[[ecce_binding]]` binding, its `[Databases]` entry and the database itself,
and leaves anything it finds untouched (it reports what it found). At each
start, `EcowittConsoleEmulatorArchive` checks again: an existing database is used as it
is; only a missing one is created.

**Same schema.** A new database gets the same columns and daily summaries as
the station's main database (`wx_binding`), read from that database itself,
so columns you've added are included. Added to that are the extra fields the
dashboard can use that the standard schema doesn't have (they stay empty if your
driver doesn't supply them): `feelslike`, `windDir10`,
`maxdailygust`, `p_rain`, `p_rainrate`, `lightning_dist`, `lightningcount`,
`soilMoist5–16`, `leafWet3–8`, `pm25_1–4` and `leak_1–4`. Set
`driver_fields = false` for an exact copy of the main schema. The database type
(SQLite or MySQL) also follows the main database.

**History.** A new database is filled from the main one, the last 400 days by
default (`backfill_days`, or `all`), with a bulk SQL copy: a year takes about a
second, plus a one-time daily-summary rebuild. On every later start, any gap
since its last record is filled the same way. From then on each archive record
is written to it as it arrives, extra fields included.

```ini
[EcowittConsoleEmulator]
    data_binding = ecce_binding    # the skin's database
    source_binding = wx_binding     # the station's main database
    backfill_days = 400             # history copied into a new database ('all' for everything)
    driver_fields = true            # add the extra fields to a new database
```

The dashboard and the charts both read this database
(`data_binding = ecce_binding` in the report section). Uninstalling the skin
removes these settings but keeps the database file; reinstalling picks it up
again.

## Troubleshooting

* **Wi-Fi icon yellow** – live data isn't arriving. With the skin's own
  `live.json`, check that `[EcowittConsoleEmulator] enable = true` and look for
  `ecowitt_console_emulator: ... writing live data to ...` in the log. With
  weewx-EcowittGateway's `ecwLoop.json`, check that the file exists and that
  `live_url` in weewx.conf points to it (relative to the dashboard page, e.g.
  `../ecwLoop.json`).
* **No batteries in Sensors** – battery fields only come from live data, and only
  under the names listed in `[[Sensors]]`. Add your driver's names there. With
  weewx-EcowittGateway, its `show_all_batt` option may help.
* **A sensor is missing** – it is hidden until it has a value. Check what your
  driver calls the field and add it to `[[FieldMap]]` or the relevant channel
  section.
* Turn on `debug = 1` in `weewx.conf` to see how long the data build takes.

## Changes and versions

See [CHANGELOG.md](CHANGELOG.md) for what changed in each release, and
[VERSIONING.md](VERSIONING.md) for how version numbers are chosen.

## Licence

Copyright (c) Ian Millard 2026

GPLv3. Ecowitt is a trademark of its owner; this project is not affiliated with
Ecowitt. All icons are original SVG drawings.
