# DivumWX Changelog

## 1.0.1 — Unreleased — Maintenance release

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
