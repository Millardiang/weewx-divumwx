# DivumWX Changelog

## Versioning scheme

- **Collection version** — tracked in `index.html`'s own header banner
  (`# index.html version X.Y.Z`). This is the release number for the whole
  DivumWX collection.
- **Per-file version** — every `.html`, `.js` and `.css` file carries its own
  `# <filename> version X.Y.Z` line in its header banner. Bump that file's
  own number by one (patch for a fix/tweak, minor for a new feature, major
  for a breaking change) every time the file is amended — independently of
  the collection version in `index.html`.
- `cardsBundleNew.js` is a single concatenated file (built from several card
  modules); it carries one version number for the whole bundle rather than
  a separate number per embedded module.

## 1.0.0 — 2026-09-20 — Full Release

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
