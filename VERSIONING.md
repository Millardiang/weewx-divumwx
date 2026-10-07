# Versioning

weewx-ecowitt_console_emulator uses [Semantic Versioning](https://semver.org/):
**MAJOR.MINOR.PATCH**, for example `1.4.2`. Version 1.0.0 is the first public
release.

## What each number means

The "interface" here is everything a user's installation depends on: the
settings in `weewx.conf` and `skin.conf`, the skin's database, the web address
of the page, the installer's options and the settings a browser has saved.

### MAJOR: something an existing installation has to change for

Raise MAJOR when updating could break a working installation or needs the user
to do something by hand, for example:

- renaming or removing a `weewx.conf` section or setting, such as
  `[EcowittConsoleEmulator]`, `[[EcowittConsoleEmulatorReport]]` or `ecce_binding`;
- renaming the skin, the Python module or its services;
- changing the skin database's name, or its schema in a way that needs it rebuilt;
- moving the page to a different web folder (now `public_html/console`);
- renaming or removing a `skin.conf` `[Extras]` option, or changing what it means;
- removing or renaming an installer option (`--rain`, `--live`);
- renaming the browser setting keys (the `ecce.` prefix, `dashboardUnitSystem` or
  `dashboardLanguage`), which resets every device's saved settings;
- removing a language;
- raising the minimum WeeWX version.

### MINOR: new features that work with existing installations

Raise MINOR, and reset PATCH to 0, when adding something without breaking
anything, for example:

- a new chart, panel, gauge, reading or sensor type;
- a new language, or new text to translate (with its translations);
- a new `skin.conf` option with a default that keeps current behaviour;
- a new installer option or question that has a sensible default;
- new fields in `ecowitt.json` or `live.json`;
- a visible change to how something works or looks, if users would notice.

### PATCH: fixes only

Raise PATCH for fixes that don't change settings or add features, for example:

- a wrong value, total or time;
- a layout or display fault on a particular screen or browser;
- a crash, log error or installer fault;
- corrections to translations;
- corrections to the README or comments.

If a change is hard to place, pick the higher level.

## Where the version is set

The version number appears in three files and must be the same in all of them:

| File | Line |
|---|---|
| `install.py` | `VERSION = "x.y.z"` |
| `bin/user/ecowitt_console_emulator.py` | `VERSION = "x.y.z"` |
| `skins/EcowittConsoleEmulator/skin.conf` | `SKIN_VERSION = x.y.z` |

The module's `VERSION` is also added to the page's CSS and JavaScript links
(`?v=x.y.z`), so browsers load the new files after an update instead of using
cached copies. It is shown in Settings and in the WeeWX log at start-up.
`weectl extension list` shows the installer's version.

To check that the three match:

```bash
grep -rn 'VERSION = ' install.py bin/user/ skins/EcowittConsoleEmulator/skin.conf
```

## Making a release

1. Under `[Unreleased]` in `CHANGELOG.md`, list the changes under Added,
   Changed, Fixed or Removed, and decide the new version from the rules above.
2. Set the version in the three files.
3. In `CHANGELOG.md`, rename `[Unreleased]` to the version and date
   (`## [x.y.z] – YYYY-MM-DD`), add an empty `[Unreleased]` above it, and update
   the comparison links at the bottom.
4. For a MAJOR release, explain in the CHANGELOG what users must do to update.
5. Test it on a WeeWX 5 station: install, run for at least two archive intervals,
   open the page, then uninstall and install again.
6. Build the zip with the folder at its top level:
   ```bash
   zip -r weewx-ecowitt_console_emulator-x.y.z.zip weewx-ecowitt_console_emulator \
       -x '*/__pycache__/*'
   ```
7. Commit, tag as `vx.y.z` and publish a GitHub release from the tag, with the
   CHANGELOG section as its notes and the zip attached.

## Pre-releases

Versions for testing before a release add a suffix, for example `1.1.0-beta.1`
or `1.1.0-rc.1`. They sort before the final `1.1.0` and are published as GitHub
pre-releases.

---

Copyright (c) Ian Millard 2026
