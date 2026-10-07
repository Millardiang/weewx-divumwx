# Changelog

All notable changes to weewx-ecowitt_console_emulator are recorded here.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and
version numbers follow the rules in [VERSIONING.md](VERSIONING.md).

## [Unreleased]

## [1.0.0] – 2026-10-07

First public release. A WeeWX 5 skin and extension that turns a tablet, phone or
browser into a full-screen emulation of the Ecowitt WH2560/HP2560 console.

### Dashboard
- Console-style layout: outdoor temperature ring, wind dial, a temperature/humidity
  channel pair (Indoor and WN31 CH1–8), soil moisture and leaf wetness, lightning,
  leak indicators, PM2.5 and CO₂, barometer with 3-hour trend and pressure-tendency
  forecast, sun arc with a moon track, moon phase, and a readings row that includes
  feels-like, dew point, VPD (kPa), 10-minute average wind, max daily gust and Beaufort.
- Wind gauge with a blue arrow for the current direction and a grey arrow for the
  10-minute average direction.
- Rain section for a tipping gauge, a piezo gauge or both, with daily, rate, event,
  hourly, weekly, monthly and yearly totals.
- Signal bars next to each reading. A battery icon appears only when a battery is low.
- Three layouts chosen from the screen's shape: landscape 1280 × 800, portrait
  800 × 1518 and phone 540 wide. Settings → Layout can force one of them.
- Three themes: Navy (console), Black (OLED / night) and Light.
- All times are always shown in the station's time zone, whatever the time zone of
  the viewing device. The zone is read from the WeeWX machine and can be set in
  `skin.conf` (`timezone`) if that comes out wrong.

### Charts
- Full-screen Day, Week, Month and Year charts drawn in the browser, following the
  theme and display units, with tooltips by mouse or touch.
- Temperature, humidity, VPD, wind, wind direction (Day tab), rain, barometer,
  solar radiation and UV.
- One rain chart with a separate column per gauge in each interval (tipping blue,
  piezo violet). The tooltip shows both values and the difference, and the legend
  shows the same period totals as the rain table.
- The Year chart covers 12 calendar months.

### Panels
- Windy radar centred on the station, in a panel over the dashboard or as a link.
- Sensors panel with battery and signal for every sensor that reports them.
- Settings: language, units, layout, theme, 12/24-hour clock, seconds, keep screen awake,
  full screen on touch, and data status.
- Every panel opens inside the dashboard, so the page never leaves full screen.

### Languages
- Everything on the dashboard can be shown in 30 languages, the same as weewx-divumwx:
  Arabic, Basque, Breton, Catalan, Chinese (simplified), Czech, Danish, Dutch, English,
  English (US), Finnish, French, German, Greek, Hindi, Hungarian, Icelandic, Italian,
  Japanese, Norwegian, Polish, Portuguese, Spanish, Swedish, Tamil, Thai, Turkish,
  Ukrainian, Urdu and Welsh.
- Chosen under Settings → Language, with a default set by `language` in `skin.conf`.
  The choice is shared with weewx-divumwx through the `dashboardLanguage` browser
  setting when both are served from the same web server.
- Day and month names follow the language. Where weewx-divumwx already translates
  the same text, its wording is used.
- Labels that would not fit their place in a language are made smaller, or put on two
  lines, to fit. In Arabic and Urdu each label reads right to left.

### Units
- Data feeds are always written in METRICWX and converted in the browser to the
  DivumWX presets: uk, us, metric, scandi, canada and icao.
- The unit choice is shared with weewx-divumwx through the `dashboardUnitSystem`
  browser setting when both are served from the same web server.

### Data
- The skin keeps its own database (`ecowitt_console_emulator.sdb`, binding
  `ecce_binding`) and never changes the station's main database.
- The skin's database is created only if missing, with the main database's schema
  plus extra Ecowitt fields. It is filled with history from the main database
  (400 days by default), then gaps are filled at each start and every new archive
  record is written to it.
- Piezo rain history is read from `p_rain` and from `hail`, so records copied from
  the main database keep their piezo rain.
- Live data every few seconds from the skin's own `live.json`, or from
  weewx-EcowittGateway's `ecwLoop.json` when that driver is installed and chosen.
- Missing driver fields are worked out from the archive: feels-like, 10-minute wind
  direction, max daily gust, VPD and period rain totals.

### Installation
- `weectl extension install` installer that asks which rain sensor(s) the station
  has and, only when weewx-EcowittGateway is installed, where live data comes from.
- Unattended installs with `--yes --rain=tipping|piezo|both --live=driver|skin`.
- weewx-EcowittGateway is optional; the skin works with any driver for an Ecowitt
  gateway or console.

[Unreleased]: https://github.com/Millardiang/weewx-ecowitt_console_emulator/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/Millardiang/weewx-ecowitt_console_emulator/releases/tag/v1.0.0

---

Copyright (c) Ian Millard 2026
