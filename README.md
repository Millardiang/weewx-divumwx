# Welcome to the DivumWX skin for WeeWX Version 2.0.0
Copyright :copyright: 2026 Ian Millard and Sean Balfour, [GNU GENERAL PUBLIC LICENSE Version 3](https://github.com/Millardiang/weewx-divumwx/blob/main/license.txt)
 
# What's new in 2.0.0
* **MeteoAlarm weather warnings for Europe** - the official warnings of the European national weather services (the EUMETNET countries, the UK included) in the dashboard's alert bar, with no API key needed. Choose your warning areas on the [weewx-meteoalarm](https://github.com/Millardiang/weewx-meteoalarm) area map during installation; warnings are shown in the dashboard's language where the national service publishes it. OpenWeatherMap alerts remain available as the alternative.
* **Boltek lightning detectors** - a Boltek Lightning card fed by NexStorm's native ngxarchive.json, with its own strike database, and a modal with a **live strike map** (Equal Earth, centred on your station): strikes coloured by age, and each new strike flashes and sends a tracer streaking to the station. Charts and records for 24 hours to all time on a second tab.
* **Windy Radar card** - live precipitation radar centred on your station, with a full-size modal.
* **Up to four webcams** - each card can show its live image around the clock, or from sunrise to sunset with a night-time page after dark. Webcam 1 opens the timelapse; the others open a larger live view.
* **Earthquake map in the Equal Earth projection** - the last 24 hours of USGS earthquakes replayed on a map matching DivumWX's Equal Earth map, with live day and night shading.
* **Colour-coded climate reports** - every temperature in the monthly and yearly climate summaries is tinted on the same scale as the forecast pages, with a colour key in your chosen units.
* The installer asks about each of these; on an upgrade your current settings are offered as the defaults. See [CHANGELOG.md](CHANGELOG.md) for the details.

# Installation
* See [INSTALLATION_GUIDE.md](INSTALLATION_GUIDE.md) for installing, upgrading and uninstalling (pip and Debian package installs), and for setting up Boltek detectors, MeteoAlarm warnings and webcams.

# Features
* Compatible with WeeWX version 5.5.0 onwards and Python version 3.13.5 onwards. (Not tested with earlier versions).
* Tested with Pip and Deb installs.
* Fully prompted install process allowing user custom settings.
* Live gauges in the CarbonSteel Series style, with 24-hour sparklines and a year-at-a-glance calendar
* Live (realtime) loop json data.
* Live (realtime) almanac json data from Skyfield.
* Astronomy section including Sean Balfour's visualisations, and an Equal Earth map that wraps onto a rotating globe, with live day and night, twilight bands, the Sun and Moon with their ground tracks and the station marked.
* Live Celestial and Skyfield pages drawn with D3: a live sky dome, the Geocentric dial, satellite passes (ISS, Tiangong, Terra and NOAA-21 by default) and comets, rise and set ribbons, the Sun's path, the solar year, the equation of time, the analemma, the lunar month, an orrery and an almanac table — all computed on the station by Skyfield, with no weewx-skyfield, weewx-celestial or weewx-loopdata needed.
* Direct data injection of external weather data into WeeWX database (a kind of souped-up weewx-FilePile).
* Unlike previous beta versions PHP code has been removed. HTML/JS Libraries/CSS only.
* Ability to reorder dashboard cards locally in browser by drag and drop.
* Switch between seven unit groups in real time (UK, US, Metric, Canadian, Scandinavian, ICAO, Beaufort).
* 24/7 Station forecast.
* 24/7 Alternative Locations forecasts.
* Animated graphics.
* Charts, heatmaps, gauges, card images all rendered using D3.
* weewx.conf configurable weather APIs.
* Weather warnings from MeteoAlarm (Europe) or OpenWeatherMap, plus UK heat and cold health alerts, flood alerts, Met Office warnings and aurora alerts.
* Lightning: a standard lightning card, or a Boltek (NexStorm) card with its own database, a live strike map and chart and records.
* Windy precipitation radar and a live Equal Earth earthquake map.
* Monthly and yearly climate reports with colour-coded temperatures.
* Toggle between themes - Auto (day and night - default), Light or Dark. The dark and light themes are inspired by John Kline's styling of his Celestial Live and Skyfield extensions.
* Up to four webcam cards, each live 24 hours or from sunrise to sunset.
* Automatic timelapse video generation from webcam images.
* Kiosk mode, 3 x 3 grid for displaying on tablet sized screens.
* Instant language selection for homepage dashboard from drop down menu
* Introducing DivumWF Forecast - install on your mobile phone desktop as a take-anywhere forecasting app.
* Rain event: -

        Definition
        A rain event is calculated using a continuous accumulation logic:
        Start: The event counter begins tracking rainfall as soon as precipitation is detected.
        Continuation: It keeps accumulating the total as long as the rain continues.
        Reset / End Condition: The rain event value resets to zero (signaling that the event has ended) when accumulated rainfall drops below 1 mm (0.039 in) and the preceding 1-hour window registers no further rainfall (or when the last 24-hour rainfall total remains under 1 mm with a dry final hour).

        If a new storm or shower starts after that threshold is met, a new "rain event" begins tracking from zero.

        Sensor types
        When tipping rain only selected at install, rain values used
        When piezo rain install selected on install, rain values used
        When both tipping and piezo selected on install, p_rain values used for piezo rain sensor

        Rain Event field only appears in either of the rain cards when value is not null and >0

# Languages supported
* Arabic
* Basque
* Breton
* Catalan
* Chinese (Simplified)
* Czech
* Danish
* Dutch
* English
* English (US)
* Finnish
* French
* German
* Greek
* Hindi
* Hungarian
* Icelandic
* Italian
* Japanese
* Norwegian
* Polish
* Portuguese
* Spanish
* Swedish
* Tamil
* Thai
* Turkish
* Ukrainian
* Urdu
* Welsh

# Repository
* https://github.com/Millardiang/weewx-divumwx

# Live Website
* https://steepleclaydonweather.uk

# Screenshot

<img width="1462" height="1047" alt="Screenshot 2026-08-28 at 23 25 27" src="https://github.com/user-attachments/assets/4d917240-2b00-4e0e-9fad-8a7008f80d39" />

# DivumWF - Forecasts and Current Conditions Anywhere

<img width="590" height="1278" alt="Screenshot 2026-09-14 at 10 01 46" src="https://github.com/user-attachments/assets/c2d73daf-f8b5-4bd3-995e-80fd1cb14117" />

* Save ..../divumwf.html to your mobile phone desktop as an app.

# Credits
* Tom Keffer, Matthew Wall and colleagues for their unstinting development, support and maintenance of WeeWX.
* Sean Balfour, a long time collaborator who created by far the greater majority of the images/visualisations used in the cards and modals rendered using D3.
* The late David Marshall, my very first collaborator, for his ingenious method of estimating cloud cover by counting pixels on radar images.
* Mike Isaac for providing test server resources and being brave enough to test early developments.
* John A Kline for his superb weewx-celestial and weewx-skyfield extensions on which the Celestial and Skyfield page designs are based.
* Vince Skahan constantly giving me food for thought.
* Early adopters, in particular Kjell, Gert, Jon, Gary, Alex and many others for helping me squash some bugs, helpful suggestions and excellent feedback.
* Open-Meteo for weather forecasts, pollen data, greenhouse gas data.
* OpenWeather for global weather alerts.
* MeteoAlarm and EUMETNET, and the national weather services that contribute to them, for European weather warnings. The MeteoAlarm integration is built from weewx-meteoalarm, which was inspired by Ken True's get-meteoalarm-warning-inc.php (saratoga-weather.org) and adapted with permission from Wim van der Kuil's wrnWarningEU-CAP.php (pwsdashboard.com).
* Windy for the precipitation radar.
* USGS for the earthquake feed.
* Boltek and Astrogenic (NexStorm) for lightning detection data.
* Natural Earth and world-atlas (Mike Bostock) for the country outlines used by the maps.
* Aviation Weather for METAR data.
* UK MetOffice for UK weather alerts and warnings.
* UK Health Security Agency for heat and cold weather alerts.
* UK Environmental Agency for flood alerts and warnings.
* Meteocons for animated weather icons.
* D3.js for data visualisations.
* Brian Underdown for the original design concept of Weather34 from which weewx-Weather34 and weewx-DivumWX have evolved. None of the original Weather34 code remains however.
