# Welcome to the DivumWX skin for WeeWX Version 1.0.1
Copyright :copyright: 2026 Ian Millard and Sean Balfour, [GNU GENERAL PUBLIC LICENSE Version 3](https://github.com/Millardiang/weewx-divumwx/blob/main/license.txt)
 
# Features
* Compatible with WeeWX version 5.5.0 onwards and Python version 3.13.5 onwards. (Not tested with earlier versions).
* Tested with Pip and Deb installs.
* Fully prompted install process allowing user custom settings.
* Realtime gauges and range gauges
* Live (realtime) loop json data.
* Live (realtime) almanac json data from Skyfield.
* Astronomy section including Sean Balfour's visualisations. 
* Direct data injection of external weather data into WeeWX database (a kind of souped-up weewx-FilePile).
* Unlike previous beta versions PHP code has been removed. HTML/JS Libraries/CSS only.
* Ability to reorder dashboard cards locally in browser by drag and drop.
* Switch between seven unit groups in real time (UK, US, Metric, Canadian, Scandinavian, Aviation, Beaufort).
* 24/7 Statiion forecast.
* 24/7 Alternative Locations forecasts.
* Animated graphics.
* Charts, heatmaps, gauges, card images all rendered using D3.
* weewx.conf configurable weather APIs.
* Toggle between themes - Auto (day and night - default), Light or Dark. The dark and light themes are inspired by John Kline's styling of his Celestial Live and Skyfield extensions.
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
* Tom Keffer, Matthew Wall and colleagues for their unstinting development, support and maintainence of WeeWX.
* Sean Balfour, a long time collaborator who created by far the greater majority of the images/visualisations used in the cards and modals rendered using D3.
* The late David Marshall, my very first collaborator, for his ingenious method of estimating cloud cover by counting pixels on radar images.
* Mike Isacson for providing test server resources and being brave enough to test early developments.
* Vince Skahan constantly giving me food for thought.
* Early adopters, in particular Kjell, Gert, Jon, Gary, Alex and many others for helping me squash some bugs, helpful suggestions and excellent feedback.
* Open-Meteo for weather forecasts, pollen data, greenhouse gas data.
* OpenWeather for global weather alerts.
* Aviation Weather for METAR data.
* UK MetOffice for UK weather alerts and warnings.
* UK Health Security Agency for heat and cold weather alerts.
* UK Environmental Agency for flood alerts and warnings.
* Meteocons for animated weather icons.
* D3.js for data visualisations.
* Brian Underdown for the original design concept of Weather34 from which weewx-Weather34 and weewx-DivumWX have evolved. None of the original Weather34 code remains however.
