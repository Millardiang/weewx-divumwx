#!/usr/bin/env python3
"""
build_card_bundle.py

Concatenates the 26 dashboard card scripts (index.html's script tags from
cardClockOutlook.js through cardStationImage.js — the ones that already
load consecutively at the very end of the page) into a single file,
cardsBundle.js, and prints the content hash to use as its cache-busting
"?v=" query string.

Why this exists: index.html previously loaded these as 26 separate
<script src="..."> tags, one HTTP request each. Flagged by Pingdom/GTMetrix
as "make fewer HTTP requests" — this collapses them into one file, one
request, same execution order, same global scope (they're plain classic
scripts, not modules, so concatenation is behaviorally identical to
loading them as separate tags in sequence).

FAILURE ISOLATION — important, and the reason for this file's second
version: with 26 separate <script> tags, an uncaught error in any one
card only killed that card — every other tag still ran independently,
including any inline <script> block later in the page (e.g. whatever
renders the header/navbar). Naively concatenating them into a single
tag threw that isolation away: an uncaught error partway through the
bundle stops the *rest of that one script* from executing, silently
skipping every card after the failure point and anything else queued
in that tag. Each card is already its own `(function(){...})();` IIFE
(confirmed across all 26 files), so wrapping each one in try/catch here
restores the original isolation without changing scoping at all — the
try/catch just wraps the call to each already-self-contained IIFE.

units.js and stationTime.js are deliberately NOT included here — they load
much earlier in index.html (before the card markup even exists in the
DOM), and moving them would change page-load ordering for no real benefit
(they're only 2 requests either way). Only the 26 scripts that already sit
together at the end are bundled.

Usage:
    python3 build_card_bundle.py /path/to/source/js/dir /path/to/output/cardsBundle.js

IMPORTANT — this project has no other build step. Whenever ANY of the 26
files listed in CARD_FILES below is edited, this script must be re-run and
index.html's bundle <script> tag's "?v=" updated to the new hash it prints,
or the site will keep serving the old, stale bundle.
"""
import hashlib
import sys
import os

CARD_FILES = [
    'cardClockOutlook.js',
    'alertBar.js',
    'cardCurrent.js',
    'cardTemperature.js',
    'cardForecast.js',
    'cardAnemometer.js',
    'cardWindCompass.js',
    'cardBarometer.js',
    'cardPiezoRain.js',
    'cardRainfall.js',
    'cardSolarRadiation.js',
    'cardUvIndex.js',
    'cardHumidity.js',
    'cardEarthDaylight.js',
    'cardSolarDial.js',
    'cardGeocentric.js',
    'cardMoonPhase.js',
    'cardLightning.js',
    'cardPollen.js',
    'cardGreenhouseGas.js',
    'cardAirquality.js',
    'cardVapourPressureDeficit.js',
    'cardEvapoTranspiration.js',
    'cardEarthquake.js',
    'cardWebcam.js',
    'cardStationImage.js',
]


def build(src_dir, out_path):
    parts = []
    for name in CARD_FILES:
        path = os.path.join(src_dir, name)
        with open(path, 'r', encoding='utf-8') as f:
            content = f.read()
        if not content.endswith('\n'):
            content += '\n'
        wrapped = (
            '/* ===== ' + name + ' ===== */\n'
            'try {\n'
            + content +
            '} catch (e) {\n'
            '  console.error("cardsBundle: ' + name + ' failed:", e);\n'
            '}\n'
        )
        parts.append(wrapped)

    combined = '\n'.join(parts)

    with open(out_path, 'w', encoding='utf-8') as f:
        f.write(combined)

    content_hash = hashlib.sha256(combined.encode('utf-8')).hexdigest()[:8]
    print(f"Wrote {out_path} ({len(combined)} bytes, {len(CARD_FILES)} files combined)")
    print(f"Cache-busting hash: {content_hash}")
    print(f'Update index.html to: <script src="cardsBundle.js?v={content_hash}"></script>')
    return content_hash


if __name__ == '__main__':
    if len(sys.argv) != 3:
        print("Usage: python3 build_card_bundle.py <source_js_dir> <output_bundle_path>")
        sys.exit(1)
    build(sys.argv[1], sys.argv[2])
