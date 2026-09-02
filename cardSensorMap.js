/*
##############################################################################################
# cardSensorMap.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################

Shared card <-> sensor mapping, used by gauges.html, gauges2.html, records.html,
and charts-d3.html to hide gauges/tabs/record-cards for a sensor whose card
wasn't selected during the installer's card-selection prompts.

WHY THIS EXISTS
----------------
Previously, none of these four pages (nor charts.json/archive.json's field
list itself) had any concept of "was this card actually enabled" -- they
all read whatever fields happen to be present in charts.json/loop.json/
archive.json and rendered a gauge/tab/record-card for every one of them
unconditionally. Deselecting a card at install time only ever hid that
card's tile on the MAIN DASHBOARD (index.html, via its own
'card-not-enabled' CSS class) -- the exact same sensor still showed up in
Gauges, Range Gauges, Records, and the Charts page, regardless.

DIVUMWX_CARD_TO_BASES maps each cardXxx name to the charts.json/records
"base" series name(s) it's responsible for (the same base names records.html
already parses out of keys like "outTempMaxHourly" -- see parseSeriesKey()
there). isBaseCardEnabled(base, enabledCards) is the single function all
four pages call to decide whether to render or hide something.

DELIBERATELY UNMAPPED (left in DIVUMWX_ALWAYS_SHOWN_BASES, never hidden):
  - airDen (air density): a software-calculated value (see install.py's
    DIVUMWX_CALCULATIONS_KEYS, 'AirDensity': 'software'), not a discrete
    hardware sensor with its own enable/disable card.
  - cloudCover: likewise not gated behind any single card in the
    enabled_cards lists seen so far.
  - solarGeneration / solarExport: no confirmed dedicated card name for
    these in any enabled_cards sample seen this session (they may belong
    to a separate Solar Assistant / inverter integration not covered by
    the standard card list) -- rather than guess a card name and risk
    hiding legitimate data, these are always shown.
  - lux / dust / sunshine-duration style fields: same reasoning -- no
    confirmed dedicated card.
If any of these turn out to have a real dedicated card after all, add them
to DIVUMWX_CARD_TO_BASES below rather than guessing blind.

'aurora' / 'meteogram' / 'windrose' (charts-d3.html's three non-METRICS-
table categories) are likewise NOT gated here -- aurora is part of the
UK-only alertBar card (see alertBar.js's isUkLocation gating, a separate
mechanism), and meteogram/windrose are synthesised views over base wind/
temp data that's always present when DivumWX runs at all, not a
separately-selectable card.
*/

var DIVUMWX_CARD_TO_BASES = {
  cardTemperature:            ['outTemp', 'outDew'],
  cardHumidity:                ['outHumid'],
  cardAnemometer:              ['windSpeed', 'windGust'],
  cardWindCompass:             ['windDir'],
  cardBarometer:                ['barom'],
  cardTippingRain:             ['rain', 'rainRate'],
  cardPiezoRain:                ['prain', 'prainRate'],
  cardSolarRadiation:          ['solar', 'solarRadiation'],
  cardUvIndex:                  ['uv'],
  cardVapourPressureDeficit:   ['vpd'],
  cardEvapoTranspiration:      ['evt'],
  cardLightning:                ['lightCount', 'lightDist'],
  cardAirquality:               ['pm1_0', 'pm2_5', 'pm4_0', 'pm10_0', 'aod'],
  cardGreenhouseGas:            ['co', 'co2', 'no2', 'nh3', 'o3', 'so2'],
  cardPollen:                    ['alder', 'birch', 'olive', 'grass', 'mugwort', 'ragweed'],
};

// Reverse index: base (lowercase) -> owning card name. Built once, reused
// by isBaseCardEnabled() below rather than rescanning DIVUMWX_CARD_TO_BASES
// on every call.
var DIVUMWX_BASE_TO_CARD = {};
Object.keys(DIVUMWX_CARD_TO_BASES).forEach(function(card){
  DIVUMWX_CARD_TO_BASES[card].forEach(function(base){
    DIVUMWX_BASE_TO_CARD[base.toLowerCase()] = card;
  });
});

/**
 * Should a gauge/tab/record-card for this base series be shown?
 *
 * @param {string} base - a charts.json/records base series name, e.g.
 *   'outTemp', 'windSpeed', 'pm2_5' -- case-insensitive.
 * @param {Array<string>|null|undefined} enabledCards - archive.json's
 *   meta.enabled_cards. If this is missing, empty, or not an array (e.g.
 *   an older archive.json, or the fetch failed), everything is shown --
 *   fail open, same convention as index.html's own card-visibility script
 *   (a missing/short enabled_cards list must never hide legitimate data).
 * @returns {boolean}
 */
function isBaseCardEnabled(base, enabledCards) {
  if (!Array.isArray(enabledCards) || enabledCards.length === 0) return true;
  var card = DIVUMWX_BASE_TO_CARD[String(base).toLowerCase()];
  if (!card) return true; // unmapped base -- see DELIBERATELY UNMAPPED above
  return enabledCards.indexOf(card) !== -1;
}
