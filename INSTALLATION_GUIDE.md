# DivumWX Installation Guide

This guide covers DivumWX **2.4.0**. It assumes you already have a stable
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
sudo weectl extension install https://github.com/Millardiang/weewx-divumwx/archive/refs/tags/ver.2.4.0.zip
sudo systemctl restart weewx
sudo weectl report run DivumWXReport
```

The installer creates `/var/www/html/divumwx` and sets its ownership to the
account WeeWX runs as (normally `weewx`). No manual `chown` is needed.

### Check the installation

```
weectl extension list          # should show divumwx 2.4.0
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
weectl extension install https://github.com/Millardiang/weewx-divumwx/archive/refs/tags/ver.2.4.0.zip
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

### Quick update with an update package

From 2.2.0 onwards, each release on the
[releases page](https://github.com/Millardiang/weewx-divumwx/releases) also
has an update package, named for the two releases it goes between, e.g.
`divumwx-update-2.1.3-to-2.2.0.zip`. It holds only the files that changed,
and it installs with the same `weectl` command as a full release.

> **⚠️ You must already be on DivumWX 2.1.3 or later.**
> Update packages start with 2.1.3 → 2.2.0. If your station is on 2.1.2 or
> earlier, an update package will refuse to install. Upgrade to 2.1.3 or
> later with a full install first (see "Upgrading from an earlier version"
> above). After that, you can use update packages for every later release.
>
> Each package also only works from the exact release in its name: the
> `2.1.3-to-2.2.0` package needs 2.1.3. To check what you have, run
> `weectl extension list` and look at the version next to `divumwx`.

What an update package does:

- it asks no questions and leaves your `weewx.conf` exactly as it is;
- it replaces only the changed files, and removes any the new release
  deleted;
- it first checks that you're on the release it was built from. If you
  aren't, it stops without changing anything. In that case, apply the update
  packages for the releases in between, or do a full upgrade as above.

Download it, then install it the usual way for your layout:

```
wget https://github.com/Millardiang/weewx-divumwx/releases/download/ver.2.2.0/divumwx-update-2.1.3-to-2.2.0.zip

sudo weectl extension install divumwx-update-2.1.3-to-2.2.0.zip      # A. Debian package
weectl extension install divumwx-update-2.1.3-to-2.2.0.zip           # B. pip (venv active)

sudo systemctl restart weewx
```

`weectl extension list` then shows the new version, and
`weectl extension uninstall divumwx` still removes everything as before.

If the update prints a note that the installer's settings logic changed, the
release notes say whether the release brings new settings. If it does, run
the full upgrade above as well to be asked for them.

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

## Site visitors (Cloudflare Web Analytics)

The footer can show **Visits: N today · M in 30 days**; clicking it opens a
report with visits and page views for today, 7 days, 30 days and since the
service started, a 30-day chart, and the top pages, countries, referring
sites, browsers, operating systems and devices.

The figures come from [Cloudflare Web Analytics](https://www.cloudflare.com/web-analytics/)
(free). It counts page views in the visitor's browser, so it's right however
the site is reached, **Cloudflare Tunnel (`cloudflared`) included**: nothing
is read from Apache's or nginx's log, no web server settings change, and no
IP address reaches the station. Bots aren't counted.

1. **Turn on Web Analytics for the site.** In the Cloudflare dashboard go to
   **Web Analytics > Add a site**, choose your site's hostname (the one
   your tunnel serves, e.g. `weather.example.com`) and finish. With a
   tunnel the hostname is already proxied by Cloudflare, so the automatic
   setup works: Cloudflare adds its counting script to your pages itself
   and nothing in DivumWX needs editing. (A site not proxied by Cloudflare
   uses the manual setup instead: paste the `<script>` snippet Cloudflare
   gives you just before `</body>` in the DivumWX pages.)
2. **Make an API token** so WeeWX can read the figures: **My Profile >
   API Tokens > Create Token > Create Custom Token**, with one permission,
   **Account > Account Analytics > Read**, for your account. Copy the token:
   it's shown only once, and is 40 characters or starts with `cfut_`.
3. **Give it to DivumWX.** The full installer asks *Do you use Cloudflare
   Web Analytics? (y/n)* and then for the token, checks it looks like an
   API token, and saves it. On a later run Enter keeps the saved token
   (which is never shown), and answering `n` clears it, switching the
   report off. Without the installer (after an update package, say), add
   it to `weewx.conf` yourself and restart WeeWX:

   ```
   [DivumWXVisitors]
       api_token = <the token>
   ```

   If the `[Engine] [[Services]] data_services` line doesn't already end in
   `user.divumwx_visitors.VisitorsService` (an update package doesn't change
   `weewx.conf`), add `, user.divumwx_visitors.VisitorsService` to it.

After a few minutes the footer shows the figures. Everything else is found
from the token: the account, and the Web Analytics site matching
`[Station] station_url`. All settings are optional:

```
[DivumWXVisitors]
    api_token = ...
    account_id = auto      # the Account ID, if the token reaches more than one account
    hostname = auto        # the site's public hostname; auto = [Station] station_url
    site_tag = auto        # or the Web Analytics site tag
    poll_interval = 900    # seconds between updates (minimum 300)
```

To check a token without waiting for WeeWX:

```
# WeeWX installed with pip:
~/weewx-venv/bin/python3 ~/weewx-data/bin/user/divumwx_visitors.py --token <token> --hostname weather.example.com
# WeeWX installed from a Debian/RPM package:
python3 /etc/weewx/bin/user/divumwx_visitors.py --token <token> --hostname weather.example.com
```

It prints the account and site it found and the figures, or what's wrong.

Notes:
- A *visit* is Cloudflare's measure: someone arriving from another site, a
  search, a link or a bookmark. Moving between pages of the site adds page
  views, not visits. There's no count of unique people.
- Your own views count when you open the site at its public address. Open
  it at the station's local address (e.g. `http://192.168.1.20/divumwx/`)
  and Cloudflare never sees them.
- Ad and tracker blockers stop some browsers from being counted.
- Busy sites may be sampled by Cloudflare; the report then says the figures
  are estimates.
- Cloudflare keeps 30 days that can be read at once, so DivumWX keeps its
  own daily totals in `divumwx_visitors_state.json` beside the WeeWX
  database; the "Since" total grows from when the service first ran.
- "Invalid request headers" means Cloudflare couldn't read the token. An
  API token is 40 characters, or (since 2026) starts `cfut_` or `cfat_`,
  and is shown only once, when it's created. The `"token"` in the Web
  Analytics JavaScript snippet (32 characters) is not it, and neither is
  the Global API Key; the report says which one it has been given.
- If something goes wrong (an expired token, say), the report keeps the
  last figures and shows Cloudflare's reason, and the WeeWX log has a
  `visitors:` line.

## Webcams

When you include the webcam card, the installer asks how many webcams you
have (up to four) and, for each one, its title, the path of its image
(relative to the DivumWX web folder, e.g. `img/picam.jpg`) and how it's
shown:

- `daylight`: the live image from sunrise to sunset, and a night-time
  image after dark. The installer then asks for that image's path
  (default `img/nighttime.jpg`, relative to the DivumWX web folder; put
  your own picture there). If it isn't there or can't be loaded, the
  built-in night-time page (`img/nightTime.svg`) is shown instead.
- `24hr`: the live image all day and night, for a camera that sees in the
  dark.

Webcam 1's card opens the timelapse, which is made from its image; the
other webcams open a larger live view. An upgrade that already shows the
webcam card is asked these questions once, with its current title and
image offered as the defaults; one whose daylight webcams were set up before
the night-time image is asked just for that, once. To change them later,
edit `webcam_title`, `webcam_image`, `webcam_mode` and `webcam_night_image`
(webcam 1) or `webcam2_*` to `webcam4_*`
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

1. On the NexStorm PC, run **NSD Bridge** (it reads NexStorm's live data and
   writes `NSDRealtime.json`). Get the file to WeeWX in one of these ways,
   and set `source_file` under `[BoltekLightning]` to match:
   - **Over HTTP** (simplest): in `nsdbridge.ini` set
     `http_listen = 0.0.0.0:8090` (and allow port 8090 in Windows Firewall),
     then set `source_file = http://<nexstorm-pc>:8090/data/NSDRealtime.json`.
   - **As a file**: have NSD Bridge copy (`mirror_dir`) or FTP it into the
     DivumWX web folder (next to `index.html`); the default
     `source_file = NSDRealtime.json` then finds it. If it goes somewhere
     else, give its full path.

   NSD Bridge's `station_lat` / `station_lon` should be set, so each strike
   has a position. `realtime_json_strikes` must stay on (the default).
   NexStorm's own `ngxarchive.json` still works too: point `source_file` at
   it. Before this version the key was called `ngxarchive_file`, which is
   still read.
2. Distances come from the file in kilometres. The card, the strike map and
   the chart follow the dashboard's unit setting (km, miles or nautical
   miles). Strikes within `close_distance` (always in km, default 16) count as
   close strikes.
   **Already have `boltek.json` and `boltek_history.json`?** If these are
   made somewhere else (for example by another DivumWX station), set
   `convert = false` under `[BoltekLightning]`. DivumWX then doesn't read
   `source_file`, convert anything or keep a Boltek database; the card and
   modal use the files as supplied. If they don't arrive straight in
   `jsondata/`, set `direct_json_source` and `direct_history_source` to their
   paths or URLs, and they're copied into place whenever they change (only
   complete, valid files are copied).
3. Restart WeeWX. The card turns green once the file is updating. The
   strikes already listed in the file (with NSD Bridge, all of today's) are
   picked up the first time, at the times they happened; the totals for the year and all time build up from
   then on. If the dot stays red, hover over it to see why.
   Click the card for its live strike map (each new strike flashes and
   traces to the station) and its chart & records, built from the same
   database.

## Sensor batteries card (Ecowitt)

For stations using the [weewx-ecowittGateway](https://github.com/Millardiang/weewx-ecowittGateway)
driver or service (2.1.x). The card shows a battery for each Ecowitt sensor
in use, filled to its level, with each sensor's reading alongside and any low
ones listed first:

- WH25, WH26, WN31, WN32, WH65 and WH69 report **OK** or **Low**;
- the other sensors report a level out of 5 (**0/5** or **1/5** is low), plus
  the battery voltage where the gateway gives one (`4/5 · 1.52 V`); **DC
  power** means a sensor running from mains (level 6);
- a sensor on the card's list that the gateway can't hear (signal 0, or no
  battery value) shows as **No signal**, so a dead battery doesn't just
  make the sensor disappear.

It reads the `<sensor>_batt`, `_sig` and `_volt` fields in `loop.json`, and
the standard columns the driver can fill instead (`inTempBatteryStatus`,
`outTempBatteryStatus`, `batteryStatus1`–`8`). The installer offers the card
when `weewx.conf` has an `[EcowittGateway]` section (an upgrade asks once).

**Which sensors.** Like the Ecowitt console emulator, the installer works
out which sensors have batteries, every time it runs: those reporting now
(DivumWX's `loop.json`, and the driver's `ecwLoop.json` if it's turned on)
and those with readings in the last 30 days (the station's database and the
driver's additions database). It shows the list (sensors already on it are
kept, new ones added) and asks for a name for each channel sensor:

```
Sensor batteries found (loop.json, wx_binding (last 30 days)): WS90, WH40, WN31 #2, WH51 #3
Sensors on the Sensor Batteries card (Enter keeps this list; or type yours, e.g. WH40, WH51 #2) [WS90, WH40, WN31 #2, WH51 #3]:
Name for WN31 #2 (Enter keeps none, 'none' for none) []: Garage
Name for WH51 #3 (Enter keeps none, 'none' for none) []: Greenhouse
```

The answers are saved in `weewx.conf`, where you can also change them:

```
[DivumWXCards]
    battery_sensors = ws90, wh40, wn31_ch2, wh51_ch3
    [[battery_names]]
        wn31_ch2 = Garage
        wh51_ch3 = Greenhouse
```

A sensor is its driver field without `_batt` (`wh40`, `wh51_ch3`). The card
shows exactly these sensors, a name in place of the sensor's type. Without
`battery_sensors` (before WeeWX has run, say) it shows every sensor it hears.

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
