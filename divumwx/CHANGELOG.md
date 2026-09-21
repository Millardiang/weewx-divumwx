# DivumWX Changelog

## Versioning scheme

- **Collection version** — tracked in `index.html`'s own header banner
  (`# index.html version X.Y.Z`). This is the release number for the whole
  DivumWX collection. Bump it when a set of per-file changes is being
  called a "release" of the site as a whole — not on every individual file
  edit.
- **Per-file version** — every `.html`, `.js` and `.css` file carries its own
  `# <filename> version X.Y.Z` line in its header banner, and bumps
  independently of the collection version whenever *that file* is amended.
  `cardsBundleNew.js` is a single concatenated file (built from several card
  modules); it carries one version number for the whole bundle rather than
  a separate number per embedded module.

### What bumps which number (X.Y.Z = MAJOR.MINOR.PATCH)

**PATCH** (`x.y.Z` → `x.y.Z+1`) — a fix or refinement that changes nothing
a caller/user has to react to:
- Bug fixes (wrong output, broken layout, a crash, a stuck UI state).
- Visual/CSS-only tweaks (spacing, colour, sizing) that don't change what a
  section contains or how it's used.
- Making an *existing* feature more accurate, precise or reliable, without
  adding a new capability, new UI element, or new option — e.g. a reverse
  geocode call returning a more detailed place name than before, using the
  same lookup the page already had.
- Copy/wording changes, comment cleanup, refactors with no behaviour change.

**MINOR** (`x.Y.0` → `x.Y+1.0`, patch resets to 0) — new, backward-compatible
capability:
- A new feature, control, data source, card, or search method that wasn't
  there before (e.g. adding UK postcode lookup alongside the existing
  place-name search).
- A meaningful new option a user can choose (a new unit system, a new
  language, a new page/view).
- Any patch-level fixes bundled into the same round of changes are covered
  by the minor bump — they don't also need their own separate patch bump.

**MAJOR** (`X.0.0` → `X+1.0.0`, minor and patch reset to 0) — a breaking
change: something else in the collection (or a user's saved settings/
bookmarks) would need to change to keep working. On this static,
no-build-step site that typically means:
- Renaming or removing a DOM `id`/class, a global function, or a
  `localStorage` key that another file (or a saved link) depends on.
- Changing a JSON data file's shape/fields in a way older consumers can't
  read.
- Removing a page/file, or changing a URL/query-param contract another
  page relies on.
- In practice this should be rare on a site this size — most work here is
  PATCH or MINOR.

## Unreleased (still 1.0.0 — not yet public)

`divumwf.html` and `divumwf.js` have had further work since the 1.0.0 pass
below, but as 1.0.0 hasn't shipped publicly yet, these fold into it rather
than bumping to 1.1.0 — there's no released version to be backward-compatible
with yet. Once 1.0.0 actually ships, the next behavioural change bumps a
real version per the rules above.

- **Fix:** location search input (and two settings-panel fields) had
  `font-size` under 16px, which triggers iOS Safari's auto-zoom-on-focus —
  selecting a location could leave the page zoomed in and looking "wider
  than the screen". Raised to 16px.
- **Fix:** GPS reverse-geocode now returns a neighbourhood/suburb-level
  place name (e.g. "Fitzrovia, London") instead of just the city — same
  lookup, more detail, no new UI.
- **Added:** UK postcode search. Typing a full or partial postcode
  (`SW1A 1AA`, `SW1A`, `sw1a1aa`) now resolves via postcodes.io alongside
  the existing Open-Meteo place-name search:
  - A complete postcode does an exact single-postcode lookup (pins that
    postcode's real coordinates); a partial/outward code does a fuzzy
    autocomplete-style search with several candidates.
  - Suggestions show ward-level detail (e.g. "St James's, Westminster")
    where available.
  - Accepts case, spacing and formatting variations while typing.
  - Falls back to the existing place-name search if it doesn't look like a
    postcode, or if the postcode lookup fails.

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
