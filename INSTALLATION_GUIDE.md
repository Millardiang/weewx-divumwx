# DivumWX Installation Guide

This guide covers DivumWX **2.0.3**. It assumes you already have a stable
WeeWX 5 installation running with your own hardware driver.

Tested on Debian 13 (Trixie) with:

* WeeWX 5.5.1 installed from the official WeeWX APT repository (Debian package)
* WeeWX 5.4.0 installed with pip into a virtual environment

**Always back up your database and `weewx.conf` before installing or upgrading.**
It is not a nice-to-have; it is a necessity whenever you change your WeeWX installation.

> **Install the tagged release, not `main`.** The download links below point at
> a fixed release tag. The `main` branch moves as development continues, so
> installing `main.zip` can give you code newer than, and different from, the
> release described here. For the newest release, check the
> [Releases page](https://github.com/Millardiang/weewx-divumwx/releases) and
> substitute its tag in the URLs below.

## Before you start

The installer asks several questions. Have these ready:

* An [OpenWeather API key](https://home.openweathermap.org/api_keys) for global
  weather warnings and alerts (optional; leave blank to configure later).
* Your nearest airport [METAR (ICAO) code](https://metar-taf.com/), e.g. `EGTK`.
* If you live in England, your UK Health Security Agency region. The installer
  lists the nine regions and asks for the **last digit** of the code only
  (e.g. `8` for South East, code `E12000008`).
* If you live in the UK, your Met Office region (listed by the installer).

When upgrading, every prompt defaults to the value already in `weewx.conf`,
so pressing Enter keeps your existing setting.

---

## A. WeeWX installed from the Debian package (APT)

This is the recommended layout. WeeWX, Skyfield and Requests are all managed by
APT; no pip or virtual environment is needed.

### Dependencies

```
sudo apt update
sudo apt install python3-skyfield python3-requests
```

### Install or upgrade

```
sudo weectl extension install https://github.com/Millardiang/weewx-divumwx/archive/refs/tags/ver.2.0.3.zip
sudo systemctl restart weewx
sudo weectl report run DivumWXReport
```

The installer creates `/var/www/html/divumwx` and sets its ownership to the
account WeeWX runs as (normally `weewx`). No manual `chown` is needed.

### Check the installation

```
weectl extension list          # should show divumwx 2.0.3
sudo journalctl -u weewx -n 100 --no-pager | grep -i divumwx
```

### Uninstall

```
cd /etc/weewx
sudo python3 divumwx_uninstall_helper.py
sudo weectl extension uninstall divumwx
sudo systemctl restart weewx
```

---

## B. WeeWX installed with pip (virtual environment)

### Dependencies

```
source ~/weewx-venv/bin/activate
python3 -m pip install skyfield requests
```

### Install or upgrade

A pip install runs `weectl` as your own user, which cannot normally write to
`/var/www/html`. Create the web directory and give it to your user **before**
installing:

```
sudo mkdir -p /var/www/html/divumwx
sudo chown -R $(whoami):$(whoami) /var/www/html/divumwx

source ~/weewx-venv/bin/activate
weectl extension install https://github.com/Millardiang/weewx-divumwx/archive/refs/tags/ver.2.0.3.zip
sudo systemctl restart weewx
weectl report run DivumWXReport
```

### Uninstall

```
source ~/weewx-venv/bin/activate
cd ~/weewx-data
python3 divumwx_uninstall_helper.py
weectl extension uninstall divumwx
sudo systemctl restart weewx
```

---

## Upgrading from an earlier version

1. Back up `weewx.conf`, your WeeWX database, and the `divumwx` web directory.
2. Stop WeeWX (`sudo systemctl stop weewx`), so the installer can work on the
   databases without WeeWX writing to them at the same time.
3. Run the install command for your layout (A or B above) with the new tag.
4. Press Enter at each prompt to keep your existing settings, or type a new
   value to change it. Changed values are applied.
5. The installer removes frontend files that an earlier DivumWX release
   installed but this release no longer ships. Your own files in the web
   directory (webcam images, timelapse output and generated data) are left
   alone. The list of files removed is printed during the install.
6. Start WeeWX again (`sudo systemctl start weewx`).

### DivumWX's own database

DivumWX keeps its extra data (pollen, dust, cloud cover, sunshine, VPD, air
density, piezo rain, solar and battery readings, ...) in a database of its
own, `divumwx_extras.sdb`, next to WeeWX's. It doesn't add anything to
WeeWX's database. The installer adds `[DataBindings][[divumwx_extras_binding]]`
and `[Databases][[divumwx_extras_db]]` to `weewx.conf` and creates the
database.

Earlier 2.0.0 test builds (betas and release candidates) kept this data as
extra columns in WeeWX's own database. When you upgrade from one of them, the
installer offers to copy that history across into `divumwx_extras.sdb`
(WeeWX's database is only read). Afterwards it prints a
`weectl database drop-columns ...` command. You can use that to remove the old
columns from WeeWX's database once you're happy the data has been copied:
back up `weewx.sdb` first and stop WeeWX. Leaving the columns does no harm.

## Weather warnings (MeteoAlarm)

The installer asks where the alert bar's weather warnings come from:

- `meteoalarm`: [MeteoAlarm](https://www.meteoalarm.org/), the official
  warnings of Europe's national weather services (the EUMETNET countries,
  the UK included). No key is needed. This is the default for stations in
  Europe.
- `openweather`: OpenWeatherMap's alerts, which need an OpenWeatherMap
  app_id (with the Met Office RSS as a fallback in the UK).

For MeteoAlarm you choose your warning areas on the area map from
[weewx-meteoalarm](https://github.com/Millardiang/weewx-meteoalarm): the
installer prints a link that opens the map at your station (your position
stays in the part of the link after `#`, which the browser doesn't send).
Pick your areas or draw a polygon, press **Copy setup code** and paste the
code (it looks like `MA1:UK258,UK259:2:`) into the installer. The areas go
in `[Meteoalarm]` in `weewx.conf`, the same section weewx-meteoalarm uses,
so if you already have weewx-meteoalarm its areas are used and you aren't
asked again. To choose again later:

```
# WeeWX installed with pip:
~/weewx-venv/bin/python3 ~/weewx-data/bin/user/divumwx_meteoalarm.py --setup
# WeeWX installed from a Debian/RPM package:
sudo python3 /etc/weewx/bin/user/divumwx_meteoalarm.py --setup
```

then restart WeeWX. `[Meteoalarm]` `min_level` (1 green, 2 yellow, 3 orange,
4 red; default 2) sets the lowest level shown. Warnings are fetched every 10
minutes (`[DivumWXMeteoalarm]` `poll_interval`) and shown in the
dashboard's language when the national service publishes one, otherwise in
English.

## Webcams

When you include the webcam card, the installer asks how many webcams you
have (up to four) and, for each one, its title, the path of its image
(relative to the DivumWX web folder, e.g. `img/picam.jpg`) and how it's
shown:

- `daylight`: the live image from sunrise to sunset, and the night-time
  page (`img/nightTime.svg`, which you can replace with your own) after
  dark. This is how the webcam card has always worked.
- `24hr`: the live image all day and night, for a camera that sees in the
  dark.

Webcam 1's card opens the timelapse, which is made from its image; the
other webcams open a larger live view. An upgrade that already shows the
webcam card is asked these questions once, with its current title and
image offered as the defaults. To change them later, edit `webcam_title`,
`webcam_image` and `webcam_mode` (webcam 1) or `webcam2_*` to `webcam4_*`
under `[DivumWXCards]` in `weewx.conf`, and add or remove `cardWebcam2` to
`cardWebcam4` in its `enabled_cards`.

## Boltek lightning detectors

DivumWX can show a Boltek detector's readings on its own **Boltek Lightning**
card. When the installer asks about the Lightning card and you answer yes,
it then asks whether your detector is a Boltek (NexStorm); answer yes and
you get the Boltek Lightning card in its place. Upgrades from an earlier
DivumWX that show the Lightning card are asked this once. Everything else is
set up automatically: a `[BoltekLightning]` section in `weewx.conf` and a
separate Boltek database, `divumwx_boltek.sdb`, which WeeWX creates next to
its own database the first time it starts. Stations without a Boltek can
ignore all of this; the service just waits for a file that never arrives.

1. In NexStorm, have it write or upload its `ngxarchive.json` to the
   DivumWX web folder (next to `index.html`). If it goes somewhere else,
   set `ngxarchive_file` under `[BoltekLightning]` to its full path.
2. Distances come from the file in kilometres. The card, the strike map and
   the chart follow the dashboard's unit setting (km, miles or nautical
   miles). Strikes within `close_distance` (always in km, default 16) count as
   close strikes.
3. Restart WeeWX. The card turns green once the file is updating. The
   strikes already listed in the file are picked up the first time, at the
   times they happened; the totals for the year and all time build up from
   then on. If the dot stays red, hover over it to see why.
   Click the card for its live strike map (each new strike flashes and
   traces to the station) and its chart & records, built from the same
   database.

## Solar energy cards (SolarAssistant)

The **Solar Energy** and **Solar Energy Flow** cards show a home solar system's
PV output, battery, grid and house load from
[SolarAssistant](https://solar-assistant.io). They're only for
SolarAssistant subscribers, and the installer marks them that way when it asks
about them. Answer yes to either one and the installer then asks for your
array's rated size (for example `4050` or `4.05kW`). The cards use it for the
PV efficiency figure. It's kept as `solar_array_watts` under `[DivumWXCards]`.

The cards read `jsondata/solar_data.json`, which DivumWX's SolarAssistant
service writes from SolarAssistant's MQTT feed. The installer copies the
service but doesn't switch it on, because it needs your MQTT broker's details:

1. Install the MQTT library: `sudo apt install python3-paho-mqtt` (Debian
   package install) or `pip install paho-mqtt` in WeeWX's virtual environment.
2. Add the service to `weewx.conf`, under `[Engine]` `[[Services]]`, at the
   end of `data_services`: `user.solar_assistant_mqtt.SolarAssistantMQTT`
3. Add its section to `weewx.conf`, pointing `json_output_path` at the
   DivumWX web folder's `jsondata` directory:

   ```
   [SolarAssistant]
       mqtt_broker_host = 192.168.1.50      # your SolarAssistant / MQTT broker
       mqtt_broker_port = 1883
       mqtt_username = ""
       mqtt_password = ""
       json_output_path = /var/www/html/divumwx/jsondata/solar_data.json
   ```
4. Restart WeeWX.

The service also adds the solar readings to WeeWX's loop packets, so DivumWX
saves them in its own database along with its other extras.

## Sensor field names (Ecowitt and other drivers)

DivumWX reads WeeWX's standard field names. Some drivers publish a reading
under another name; for example, some Ecowitt drivers give PM2.5 as
`pm25_1` and lightning distance as `lightning_dist`, where WeeWX expects
`pm2_5` and `lightning_distance`. You can see the names your driver uses
in `jsondata/loop.json`.

To map them, add the standard names to `[StdCalibrate]` in `weewx.conf`
(add the lines to `[[Corrections]]` if the section is already there):

```
[StdCalibrate]
    [[Corrections]]
        pm2_5 = pm25_1
        lightning_distance = lightning_dist
```

Then restart WeeWX. The readings are saved under the standard names from
then on, so they appear in the charts, records and Lightning card. Only add
a line if your driver doesn't already provide the standard name. If your
driver has its own field map (e.g. `field_map_extensions`), mapping the
field there works too.

## Troubleshooting

See [TROUBLESHOOTING.md](TROUBLESHOOTING.md).

## Feedback

Please use the [Issues log](https://github.com/Millardiang/weewx-divumwx/issues)
for feedback. Reports from installs using methods other than those above are
especially welcome.
