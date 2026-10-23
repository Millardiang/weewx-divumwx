# DivumWX Installation Guide

This guide covers DivumWX **1.7.0**. It assumes you already have a stable
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
sudo weectl extension install https://github.com/Millardiang/weewx-divumwx/archive/refs/tags/ver.1.7.0.zip
sudo systemctl restart weewx
sudo weectl report run DivumWXReport
```

The installer creates `/var/www/html/divumwx` and sets its ownership to the
account WeeWX runs as (normally `weewx`). No manual `chown` is needed.

### Check the installation

```
weectl extension list          # should show divumwx 1.0.1
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
weectl extension install https://github.com/Millardiang/weewx-divumwx/archive/refs/tags/ver.1.7.0.zip
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

## Upgrading from a beta or 1.0.0

1. Back up `weewx.conf`, your WeeWX database, and the `divumwx` web directory.
2. Run the install command for your layout (A or B above) with the new tag.
3. Press Enter at each prompt to keep your existing settings, or type a new
   value to change it. Changed values are applied.
4. The installer removes frontend files that an earlier DivumWX release
   installed but this release no longer ships. Your own files in the web
   directory (webcam images, timelapse output and generated data) are left
   alone. The list of files removed is printed during the install.
5. Restart WeeWX.

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
2. Distances come from the file in kilometres. The card shows kilometres;
   the chart page follows the dashboard's unit setting. Strikes within
   `close_distance` (default 16 km) count as close strikes.
3. Restart WeeWX. The card turns green once the file is updating. The
   strikes already listed in the file are picked up the first time, at the
   times they happened; the totals for the year and all time build up from
   then on. If the dot stays red, hover over it to see why.
   Click the card for its live strike map (each new strike flashes and
   traces to the station) and its chart & records, built from the same
   database.

## Troubleshooting

See [TROUBLESHOOTING.md](TROUBLESHOOTING.md).

## Feedback

Please use the [Issues log](https://github.com/Millardiang/weewx-divumwx/issues)
for feedback. Reports from installs using methods other than those above are
especially welcome.
