/*
##############################################################################################
# divumwf.js version 1.0.0
#  DivumWF -- weather forecast for anywhere in the world, mobile-first.
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== divumwf.js =====================

// ===================== Formatting helpers (mirrors locationforecast.html) =====================
function round1(v){ return v==null ? null : Math.round(v*10)/10; }
function round2(v){ return v==null ? null : Math.round(v*100)/100; }
function fmtTemp(c, unit){ return c==null ? '--' : (unit==='F' ? round1(C2F(c))+'\u00B0F' : round1(c)+'\u00B0C'); }

const TEMP_COLOR_STOPS = [
  [-10, [91,127,227]], [0,[79,168,208]], [10,[79,184,155]], [18,[111,184,90]],
  [24,[201,178,62]], [30,[224,138,61]], [36,[216,90,61]], [42,[184,48,42]],
];
function tempColor(c){
  if(c==null) return null;
  const stops = TEMP_COLOR_STOPS;
  if(c <= stops[0][0]) return `rgb(${stops[0][1].join(',')})`;
  if(c >= stops[stops.length-1][0]) return `rgb(${stops[stops.length-1][1].join(',')})`;
  for(let i=0;i<stops.length-1;i++){
    const [t0,c0] = stops[i], [t1,c1] = stops[i+1];
    if(c>=t0 && c<=t1){
      const f = (c-t0)/(t1-t0);
      return `rgb(${c0.map((v,idx)=>Math.round(v+(c1[idx]-v)*f)).join(',')})`;
    }
  }
}
function tempSpan(c, unit){ return `<span style="color:${tempColor(c)};">${fmtTemp(c, unit)}</span>`; }

function fmtWind(ms, unit){
  if(ms==null) return '--';
  switch(unit){
    case 'mph': return round1(ms2mph(ms))+' mph';
    case 'kmh': return round1(ms2kmh(ms))+' km/h';
    case 'kt':  return round1(ms2kt(ms))+' kt';
    case 'ms':  return round1(ms)+' m/s';
    case 'bf': { const b=beaufort(ms); return 'F'+b.force+' \u00B7 '+b.label; }
    default: return round1(ms)+' m/s';
  }
}
const WIND_COLOR_STOPS = [
  [0.0,[125,150,165]],[0.5,[110,168,189]],[1.5,[97,184,178]],[3.3,[99,184,114]],
  [5.5,[156,182,80]],[7.9,[205,171,62]],[10.7,[224,138,61]],[13.8,[217,98,61]],
  [17.1,[195,62,48]],[20.7,[170,45,60]],[24.4,[150,40,90]],[28.4,[120,40,120]],[32.6,[90,40,140]],
];
function windColor(ms){
  if(ms==null) return null;
  const stops = WIND_COLOR_STOPS;
  if(ms <= stops[0][0]) return `rgb(${stops[0][1].join(',')})`;
  if(ms >= stops[stops.length-1][0]) return `rgb(${stops[stops.length-1][1].join(',')})`;
  for(let i=0;i<stops.length-1;i++){
    const [s0,c0] = stops[i], [s1,c1] = stops[i+1];
    if(ms>=s0 && ms<=s1){
      const f = (ms-s0)/(s1-s0);
      return `rgb(${c0.map((v,idx)=>Math.round(v+(c1[idx]-v)*f)).join(',')})`;
    }
  }
}
function fmtWindValueOnly(ms, unit){
  if(ms==null) return '--';
  switch(unit){
    case 'mph': return round1(ms2mph(ms));
    case 'kmh': return round1(ms2kmh(ms));
    case 'kt':  return round1(ms2kt(ms));
    case 'ms':  return round1(ms);
    case 'bf':  return beaufort(ms).force;
    default:    return round1(ms);
  }
}
function windUnitAbbr(unit){
  switch(unit){
    case 'mph': return 'mph'; case 'kmh': return 'km/h'; case 'kt': return 'kt';
    case 'ms': return 'm/s'; case 'bf': return 'Bf'; default: return 'm/s';
  }
}
function windDirBadge(dirDeg, gustMs, unit){
  const col = windColor(gustMs) || 'var(--bs-secondary-color)';
  const val = fmtWindValueOnly(gustMs, unit);
  const arrow = dirDeg!=null ? `
      <svg viewBox="0 0 44 44" class="wind-badge-ring" aria-hidden="true">
        <circle cx="22" cy="22" r="13" fill="none" stroke="${col}" stroke-width="1.5" opacity=".35"/>
        <g transform="rotate(${dirDeg} 22 22)">
          <path d="M22 1 L31.5 10.4 L22 5 L12.5 10.4 Z" fill="${col}"/>
        </g>
      </svg>` : '';
  return `<div class="wind-badge" style="--wind-col:${col}" title="${dirDeg!=null ? DivumWXI18N.t('From')+' '+degToCompass(dirDeg) : ''}">
      ${arrow}<span class="wind-badge-value">${val}</span>
    </div>`;
}
function fmtRain(mm, unit){ if(mm==null) return '--'; return unit==='in' ? round2(mm2in(mm))+' in' : round2(mm)+' mm'; }
function fmtPressure(hpa, unit){
  if(hpa==null) return '--';
  switch(unit){
    case 'inhg': return round2(hpa2inhg(hpa))+' inHg';
    case 'mbar': return round1(hpa)+' mbar';
    case 'kpa':  return round2(hpa2kpa(hpa))+' kPa';
    default:     return round1(hpa)+' hPa';
  }
}
function fmtVis(km, unit){
  if(km==null) return '--';
  return unit==='mi' || unit==='nm' ? round1(unit==='nm'?km2nm(km):km2mi(km))+' '+unit : round1(km)+' km';
}
function degToCompass(deg){
  if(deg==null) return '\u2014';
  const dirs=['N','NNE','NE','ENE','E','ESE','SE','SSE','S','SSW','SW','WSW','W','WNW','NW','NNW'];
  return DivumWXI18N.t(dirs[Math.round(deg/22.5)%16]);
}
function escapeHtml(s){ return (s==null?'':String(s)).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

// ===================== UI-chrome icons (inline SVG, not emoji) =====================

const WF_ICON_PATHS = {
  gear: '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06c.5.5 1.24.65 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09c0 .69.4 1.32 1 1.51.58.32 1.32.17 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06c-.5.5-.65 1.24-.33 1.82V9c.32.58.95.99 1.64.99H21a2 2 0 0 1 0 4h-.09c-.69 0-1.32.4-1.64 1z"/>',
  pin: '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
  sun: '<circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>',
  haze: '<path d="M9.59 4.59A2 2 0 1 1 11 8H2M12.59 19.41A2 2 0 1 0 14 16H2M17.73 7.73A2.5 2.5 0 1 1 19.5 12H2"/>',
  drop: '<path d="M12 2.69s6 6.32 6 10.31a6 6 0 0 1-12 0c0-3.99 6-10.31 6-10.31z"/>',
  sunrise: '<path d="M17 18a5 5 0 0 0-10 0"/><line x1="12" y1="2" x2="12" y2="9"/><line x1="4.22" y1="10.22" x2="5.64" y2="11.64"/><line x1="1" y1="18" x2="3" y2="18"/><line x1="21" y1="18" x2="23" y2="18"/><line x1="18.36" y1="11.64" x2="19.78" y2="10.22"/><line x1="23" y1="22" x2="1" y2="22"/><polyline points="8 6 12 2 16 6"/>',
  sunset: '<path d="M17 18a5 5 0 0 0-10 0"/><line x1="1" y1="18" x2="3" y2="18"/><line x1="21" y1="18" x2="23" y2="18"/><line x1="18.36" y1="11.64" x2="19.78" y2="10.22"/><line x1="4.22" y1="10.22" x2="5.64" y2="11.64"/><line x1="23" y1="22" x2="1" y2="22"/><polyline points="16 5 12 9 8 5"/>',
  moon: '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>',
  auto: '<circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 3a9 9 0 0 1 0 18z"/>',
  close: '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
};

const WF_ICON_STROKE = { gear:1, sun:1, haze:1, sunrise:1, sunset:1, close:1 };
function wfIcon(name, size){
  size = size || 16;
  const stroke = WF_ICON_STROKE[name];
  const attrs = stroke
    ? `fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"`
    : `fill="currentColor" stroke="none"`;
  return `<svg class="wf-icon" width="${size}" height="${size}" viewBox="0 0 24 24" ${attrs} aria-hidden="true">${WF_ICON_PATHS[name]||''}</svg>`;
}

// ===================== Icons (mirrors allseasons.js's ICON_MAP) =====================
const METEOCONS_BASE = 'https://cdn.meteocons.com/3.0.0-next.10/svg/fill/';
const ICON_MAP = {
  sun:'clear-day', moon:'clear-night', partly:'partly-cloudy-day', partlyNight:'partly-cloudy-night',
  cloud:'cloudy', rain:'rain', drizzle:'drizzle', snow:'snow', sleet:'sleet',
  thunder:'thunderstorms-day', thunderNight:'thunderstorms-night', fog:'fog-day', fogNight:'fog-night',
};
function iconUrl(name){ return METEOCONS_BASE + (ICON_MAP[name] || ICON_MAP.partly) + '.svg'; }
function iconImg(name, cls, label){ return `<img class="${cls}" src="${iconUrl(name)}" alt="${label||name}" loading="lazy">`; }

// ===================== WMO weather-code mapping (mirrors forecast.js) =====================
const WMO_TEXT_KEYS = {
  0:'Clear sky', 1:'Mainly clear', 2:'Partly cloudy', 3:'Overcast',
  45:'Fog', 48:'Freezing fog',
  51:'Light drizzle', 53:'Drizzle', 55:'Dense drizzle',
  56:'Light freezing drizzle', 57:'Freezing drizzle',
  61:'Light rain', 63:'Rain', 65:'Heavy rain',
  66:'Light freezing rain', 67:'Freezing rain',
  71:'Light snow', 73:'Snow', 75:'Heavy snow', 77:'Snow grains',
  80:'Rain showers', 81:'Rain showers', 82:'Violent rain showers',
  85:'Snow showers', 86:'Heavy snow showers',
  95:'Thunderstorm', 96:'Thunderstorm with hail', 99:'Severe thunderstorm with hail',
};
function wmoText(code){ return DivumWXI18N.t(WMO_TEXT_KEYS[code] || 'Unknown'); }
function wmoToIconKey(code, isDay){
  if(code===0 || code===1) return isDay ? 'sun' : 'moon';
  if(code===2) return isDay ? 'partly' : 'partlyNight';
  if(code===3) return 'cloud';
  if(code===45 || code===48) return isDay ? 'fog' : 'fogNight';
  if(code===51 || code===53 || code===55) return 'drizzle';
  if(code===56 || code===57 || code===66 || code===67) return 'sleet';
  if(code===61 || code===63 || code===65 || code===80 || code===81 || code===82) return 'rain';
  if(code===71 || code===73 || code===75 || code===77 || code===85 || code===86) return 'snow';
  if(code===95 || code===96 || code===99) return isDay ? 'thunder' : 'thunderNight';
  return isDay ? 'partly' : 'partlyNight';
}
const WEEKDAYS = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
function dayLabel(dateStr, index){
  if(index===0) return DivumWXI18N.t('Today');
  const d = new Date(dateStr+'T00:00:00');
  return DivumWXI18N.t(WEEKDAYS[d.getDay()]) + ' ' + d.getDate();
}
function hm(iso){ return iso ? iso.slice(11,16) : '--:--'; }

function localIsoToUtcMs(iso, utcOffsetSeconds){
  if(!iso) return null;
  const [datePart, timePart] = iso.split('T');
  const [y,m,d] = datePart.split('-').map(Number);
  const [hh,mm] = (timePart||'00:00').split(':').map(Number);
  return Date.UTC(y, m-1, d, hh, mm) - (utcOffsetSeconds||0)*1000;
}

// ===================== UV index category (EPA/WHO scale) =====================
function uvCategory(uv){
  if(uv==null) return {tier:'low', key:'--'};
  if(uv<3)  return {tier:'low',  key:'Low'};
  if(uv<6)  return {tier:'mod',  key:'Moderate'};
  if(uv<8)  return {tier:'high', key:'High'};
  if(uv<11) return {tier:'high', key:'Very High'};
  return {tier:'high', key:'Extreme'};
}

// ===================== US AQI category (EPA scale) =====================
function usAqiCategory(aqi){
  if(aqi==null) return {tier:'low', key:'--'};
  if(aqi<=50)  return {tier:'low',  key:'Good'};
  if(aqi<=100) return {tier:'mod',  key:'Moderate'};
  if(aqi<=150) return {tier:'high', key:'Unhealthy for sensitive groups'};
  if(aqi<=200) return {tier:'high', key:'Unhealthy'};
  if(aqi<=300) return {tier:'high', key:'Very unhealthy'};
  return {tier:'high', key:'Hazardous'};
}

// ===================== Next-60-minutes rain forecast =====================

const RAIN_INTENSITY = [
  {max:0.1, tier:'dry',      key:'Dry'},
  {max:2.5, tier:'light',    key:'Light'},
  {max:7.6, tier:'moderate', key:'Moderate'},
  {max:Infinity, tier:'heavy', key:'Heavy'},
];
function rainRateTier(mmPerHour){
  if(mmPerHour==null) return RAIN_INTENSITY[0];
  for(const level of RAIN_INTENSITY){ if(mmPerHour <= level.max) return level; }
  return RAIN_INTENSITY[RAIN_INTENSITY.length-1];
}

function buildMinutelyRainWindow(forecastData){
  const m = forecastData && forecastData.minutely15;
  if(!m || !m.time || !m.time.length) return null;
  const offset = forecastData.utcOffsetSeconds || 0;
  const nowMs = Date.now();

  let anchorIdx = -1;
  for(let i=0;i<m.time.length;i++){
    const t = localIsoToUtcMs(m.time[i], offset);
    if(t<=nowMs) anchorIdx = i; else break;
  }
  if(anchorIdx===-1) anchorIdx = 0;
  if(anchorIdx + 4 >= m.time.length) return null;
  const anchorRates = [];
  for(let k=0;k<5;k++){
    const mm15 = m.precipMm[anchorIdx+k];
    anchorRates.push(mm15==null ? 0 : mm15*4);
  }
  const minutes = [];
  for(let seg=0; seg<4; seg++){
    const v0 = anchorRates[seg], v1 = anchorRates[seg+1];
    for(let step=0; step<15; step++){
      const f = step/15;
      const rate = Math.max(0, v0 + (v1-v0)*f);
      minutes.push(rate);
    }
  }
  minutes.push(Math.max(0, anchorRates[4]));
  return minutes;
}

// ===================== Rolling 24-hour window (meteogram) =====================

function buildRolling24h(forecastData){
  if(!forecastData || !forecastData.hourlyByDay) return null;
  const flat = [].concat(...forecastData.hourlyByDay);
  if(!flat.length) return null;
  const offset = forecastData.utcOffsetSeconds || 0;
  const nowMs = Date.now();
  let anchorIdx = -1;
  for(let i=0;i<flat.length;i++){
    const t = localIsoToUtcMs(flat[i].iso, offset);
    if(t<=nowMs) anchorIdx = i; else break;
  }
  if(anchorIdx===-1) anchorIdx = 0;
  const window = flat.slice(anchorIdx, anchorIdx+24);
  return window.length >= 2 ? window : null;
}

function summarizeMinutelyRain(minutes){
  const tiers = minutes.map(rainRateTier);
  const isWet = t => t.tier!=='dry';
  const anyWet = tiers.some(isWet);
  if(!anyWet) return {key:'No rain expected in the next hour.', vars:{}};
  const allWet = tiers.every(isWet);
  const worst = tiers.reduce((a,b)=> RAIN_INTENSITY.indexOf(b) > RAIN_INTENSITY.indexOf(a) ? b : a);
  if(allWet) return {key:'{tier} rain for the hour.', vars:{tier: worst.key}};
  const firstWet = tiers.findIndex(isWet);
  const lastWet = tiers.length - 1 - [...tiers].reverse().findIndex(isWet);
  if(firstWet===0 && lastWet<tiers.length-1){
    return {key:'{tier} rain easing after {mins} min.', vars:{tier: worst.key, mins: lastWet}};
  }
  if(firstWet>0 && lastWet===tiers.length-1){
    return {key:'{tier} rain arriving in {mins} min.', vars:{tier: worst.key, mins: firstWet}};
  }
  return {key:'{tier} rain on and off through the hour.', vars:{tier: worst.key}};
}

// ===================== Moon phase (simple synodic-month approximation) =====================

const MOON_PHASE_KEYS = [
  'New Moon','Waxing Crescent','First Quarter','Waxing Gibbous',
  'Full Moon','Waning Gibbous','Last Quarter','Waning Crescent',
];
function moonPhaseFor(date){
  const synodic = 29.530588853;
  const knownNewMoon = Date.UTC(2000,0,6,18,14,0);
  const days = (date.getTime() - knownNewMoon) / 86400000;
  let age = days % synodic;
  if(age < 0) age += synodic;
  const illum = round1((1 - Math.cos(2*Math.PI*age/synodic)) / 2 * 100);
  const idx = Math.floor((age / synodic) * 8 + 0.5) % 8;
  return { age: round1(age), illum, key: MOON_PHASE_KEYS[idx] };
}

// ===================== Geocoding (Open-Meteo) =====================
async function geocodeSearch(query){
  const url = `https://geocoding-api.open-meteo.com/v1/search?name=${encodeURIComponent(query)}&count=8&language=en&format=json`;
  const res = await fetch(url);
  if(!res.ok) throw new Error('HTTP '+res.status);
  const j = await res.json();
  return j.results || [];
}
function resultToLocation(r){
  const parts = [r.name, r.admin1, r.country].filter((v,i,arr)=> v && arr.indexOf(v)===i);
  return { label: parts.join(', '), latitude:r.latitude, longitude:r.longitude, source:r.source || 'geocode' };
}

// ===================== UK postcode search (postcodes.io) =====================
// Open-Meteo's geocoder is a place-name gazetteer -- it doesn't resolve
// postcodes. Matches full ("SW1A 1AA") and partial/outward ("SW1A", "M1")
// UK postcodes, tolerating case, no space, and irregular spacing, so
// results start appearing while the user is still typing.
const UK_POSTCODE_PARTIAL_RE = /^[A-Z]{1,2}\d[A-Z\d]?(\s*\d[A-Z]{0,2})?$/i;
const UK_POSTCODE_FULL_RE = /^[A-Z]{1,2}\d[A-Z\d]?\s*\d[A-Z]{2}$/i;
function normalizePostcodeQuery(query){
  return query.trim().replace(/\s+/g, ' ').toUpperCase();
}
function looksLikeUkPostcode(query){
  const s = normalizePostcodeQuery(query);
  return s.length >= 2 && s.length <= 8 && UK_POSTCODE_PARTIAL_RE.test(s);
}
function postcodeResultToEntry(r){
  // Ward is the finer-grained area; district/region is what it sits in --
  // same granular-first, no-repeat pairing as reverseGeocode() below.
  const ward = r.admin_ward;
  const district = r.admin_district || r.region || '';
  const admin1 = ward && district && ward !== district ? `${ward}, ${district}` : (district || ward || '');
  return {
    name: r.postcode,
    admin1,
    country: 'United Kingdom',
    latitude: r.latitude,
    longitude: r.longitude,
    source: 'postcode',
  };
}
async function postcodeSearch(query){
  const s = normalizePostcodeQuery(query);
  if(UK_POSTCODE_FULL_RE.test(s)){
    // A complete postcode gets its own exact lookup -- pins that
    // postcode's real address point instead of a fuzzy nearby match.
    const url = `https://api.postcodes.io/postcodes/${encodeURIComponent(s)}`;
    const res = await fetch(url);
    if(res.status === 404) return [];
    if(!res.ok) throw new Error('HTTP '+res.status);
    const j = await res.json();
    return j.result ? [postcodeResultToEntry(j.result)] : [];
  }
  // Partial/outward code: fuzzy autocomplete-style search, several candidates.
  const url = `https://api.postcodes.io/postcodes?q=${encodeURIComponent(s)}`;
  const res = await fetch(url);
  if(!res.ok) throw new Error('HTTP '+res.status);
  const j = await res.json();
  return (j.result || []).map(postcodeResultToEntry);
}
async function reverseGeocode(lat, lon){
  try{
    const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=14&addressdetails=1`;
    const res = await fetch(url, {headers:{'Accept':'application/json'}});
    if(!res.ok) throw new Error('HTTP '+res.status);
    const j = await res.json();
    const a = j.address || {};
    // Most granular first (neighbourhood/suburb), then the settlement it
    // sits in if that's a different name, then region/country.
    const granular = a.neighbourhood || a.quarter || a.suburb || a.city_district || a.hamlet;
    const settlement = a.city || a.town || a.village || a.municipality;
    const place = granular && settlement && granular !== settlement
      ? `${granular}, ${settlement}`
      : (granular || settlement || a.county || j.name);
    const region = a.state || (settlement ? a.county : '') || '';
    const country = a.country || '';
    return [place, region, country].filter(Boolean).join(', ') || null;
  }catch(e){
    console.warn('DivumWF: reverse geocode failed \u2014', e.message);
    return null;
  }
}

// ===================== Forecast (Open-Meteo) =====================
async function fetchForecastFor(lat, lon){
  const params = new URLSearchParams({
    latitude: lat, longitude: lon,
    current: 'temperature_2m,relative_humidity_2m,apparent_temperature,is_day,precipitation,weather_code,cloud_cover,pressure_msl,wind_speed_10m,wind_gusts_10m,wind_direction_10m',
    hourly: 'temperature_2m,weather_code,precipitation,precipitation_probability,wind_speed_10m,wind_gusts_10m,wind_direction_10m,is_day,visibility',
    daily: 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max,precipitation_sum,wind_speed_10m_max,wind_gusts_10m_max,uv_index_max,sunrise,sunset',
    minutely_15: 'precipitation,weather_code',
    timezone: 'auto',
    forecast_days: '7',
  });
  const res = await fetch(`https://api.open-meteo.com/v1/forecast?${params.toString()}`);
  if(!res.ok) throw new Error('HTTP '+res.status);
  return res.json();
}
async function fetchAirQualityFor(lat, lon){
  const params = new URLSearchParams({
    latitude: lat, longitude: lon,
    current: 'us_aqi,european_aqi,pm2_5,pm10,ozone,nitrogen_dioxide',
    timezone: 'auto',
  });
  const res = await fetch(`https://air-quality-api.open-meteo.com/v1/air-quality?${params.toString()}`);
  if(!res.ok) throw new Error('HTTP '+res.status);
  return res.json();
}
function parseForecastJson(j){
  const cur = j.current || {};
  const h = j.hourly || {};
  const hourlyByDate = {};
  (h.time || []).forEach((iso, i) => {
    const dateStr = iso.slice(0,10);
    const hour = parseInt(iso.slice(11,13), 10);
    (hourlyByDate[dateStr] = hourlyByDate[dateStr] || []).push({
      iso, h: hour,
      tempC: h.temperature_2m ? h.temperature_2m[i] : null,
      icon: wmoToIconKey(h.weather_code ? h.weather_code[i] : cur.weather_code,
        (h.is_day && h.is_day[i] != null) ? h.is_day[i] === 1 : (hour>=6 && hour<20)),
      code: h.weather_code ? h.weather_code[i] : null,
      rainMm: h.precipitation ? h.precipitation[i] : 0,
      rainProbPct: h.precipitation_probability ? h.precipitation_probability[i] : null,
      windMs: h.wind_speed_10m ? kmh2ms(h.wind_speed_10m[i]) : null,
      gustMs: h.wind_gusts_10m ? kmh2ms(h.wind_gusts_10m[i]) : null,
      windDirDeg: h.wind_direction_10m ? h.wind_direction_10m[i] : null,
      visKm: h.visibility ? h.visibility[i]/1000 : null,
    });
  });
  const d = j.daily || {};
  const dailyDates = d.time || [];
  const days = dailyDates.map((dateStr, i) => {
    const code = d.weather_code ? d.weather_code[i] : 0;
    return {
      dateStr, code,
      icon: wmoToIconKey(code, true),
      hi: d.temperature_2m_max ? d.temperature_2m_max[i] : null,
      lo: d.temperature_2m_min ? d.temperature_2m_min[i] : null,
      rainProbPct: d.precipitation_probability_max ? d.precipitation_probability_max[i] : null,
      rainSumMm: d.precipitation_sum ? d.precipitation_sum[i] : null,
      windMaxMs: d.wind_speed_10m_max ? kmh2ms(d.wind_speed_10m_max[i]) : null,
      gustMaxMs: d.wind_gusts_10m_max ? kmh2ms(d.wind_gusts_10m_max[i]) : null,
      uvMax: d.uv_index_max ? d.uv_index_max[i] : null,
      sunrise: d.sunrise ? d.sunrise[i] : null,
      sunset: d.sunset ? d.sunset[i] : null,
    };
  });
  return {
    current: {
      tempC: cur.temperature_2m ?? null,
      feelsC: cur.apparent_temperature ?? null,
      humidity: cur.relative_humidity_2m ?? null,
      isDay: cur.is_day===1,
      precip: cur.precipitation ?? null,
      code: cur.weather_code ?? null,
      icon: wmoToIconKey(cur.weather_code, cur.is_day!==0),
      cloudPct: cur.cloud_cover ?? null,
      pressureHpa: cur.pressure_msl ?? null,
      windMs: cur.wind_speed_10m!=null ? kmh2ms(cur.wind_speed_10m) : null,
      gustMs: cur.wind_gusts_10m!=null ? kmh2ms(cur.wind_gusts_10m) : null,
      windDirDeg: cur.wind_direction_10m ?? null,
      time: cur.time || null,
    },
    days, hourlyByDay: dailyDates.map(dateStr => hourlyByDate[dateStr] || []),
    timezone: j.timezone || null, utcOffsetSeconds: j.utc_offset_seconds || 0,
    minutely15: {
      time: (j.minutely_15 && j.minutely_15.time) || [],
      precipMm: (j.minutely_15 && j.minutely_15.precipitation) || [],
    },
  };
}
// ===================== Pirate Weather (optional, user-supplied key) =====================

const PIRATE_WEATHER_BASE = 'https://api.pirateweather.net/forecast/';
async function fetchPirateWeatherMinutely(lat, lon, apiKey, lang){
  const langParam = lang ? `&lang=${encodeURIComponent(lang)}` : '';
  const url = `${PIRATE_WEATHER_BASE}${encodeURIComponent(apiKey)}/${lat},${lon}?exclude=currently,hourly,daily,alerts,flags&units=si${langParam}`;
  const res = await fetch(url);
  if(!res.ok) throw new Error('HTTP '+res.status);
  return res.json();
}
function parsePirateWeatherMinutely(json){
  const block = json && json.minutely;
  if(!block || !Array.isArray(block.data) || !block.data.length) return null;
  return {
    summaryText: block.summary || null,
    minutes: block.data.map(d => ({ tMs: d.time*1000, rateMmH: d.precipIntensity==null ? 0 : d.precipIntensity })),
  };
}

function parseAirQualityJson(j){
  const c = (j && j.current) || {};
  return {
    usAqi: c.us_aqi ?? null,
    euAqi: c.european_aqi ?? null,
    pm25: c.pm2_5 ?? null,
    pm10: c.pm10 ?? null,
    ozone: c.ozone ?? null,
    no2: c.nitrogen_dioxide ?? null,
  };
}
