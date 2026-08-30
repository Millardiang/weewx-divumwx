/*
##############################################################################################
# forecast.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== forecast.js =====================

// ===================== Mock dataset (SI base units) — fallback only =====================
const mockDays = [
  {name:"Today", icon:"partly", cond:"Sunny intervals", hi:23, lo:14, rainPct:40, gust:8.5, warnSeverity:null},
  {name:"Sun 21", icon:"partly", cond:"Sunny intervals", hi:28, lo:16, rainPct:10, gust:7.6, warnSeverity:null},
  {name:"Mon 22", icon:"sun", cond:"Sunny day", hi:29, lo:16, rainPct:20, gust:8.5, warnSeverity:'amber'},
  {name:"Tue 23", icon:"sun", cond:"Sunny day", hi:34, lo:17, rainPct:10, gust:8.5, warnSeverity:'red'},
  {name:"Wed 24", icon:"sun", cond:"Sunny day", hi:33, lo:18, rainPct:5, gust:7.6, warnSeverity:null},
  {name:"Thu 25", icon:"sun", cond:"Sunny day", hi:32, lo:18, rainPct:5, gust:10.3, warnSeverity:null},
  {name:"Fri 26", icon:"sun", cond:"Sunny day", hi:32, lo:19, rainPct:10, gust:10.3, warnSeverity:null},
];

function mockHourlyForDay(seed){
  const hours=[]; const baseTemp=15+seed*1.3;
  for(let h=0;h<24;h+=1){
    const t = baseTemp + 8*Math.sin((h-6)/24*Math.PI*2) ;
    const windMs = 2 + Math.abs(Math.sin(h/24*Math.PI*2))*5;
    hours.push({
      h, tempC: Math.round(t*10)/10,
      icon: h>=6&&h<20 ? (h%6===0?'sun':'partly') : (h%4===0?'cloud':'moon'),
      rainMm: Math.max(0, Math.round((1.5*Math.sin(h/24*Math.PI*3)+ (seed%3)*0.3))*10)/10,
      windMs,
      gustMs: windMs * 1.6,
      windDirDeg: Math.round((200 + seed*15 + h*7) % 360)
    });
  }
  return hours.filter(x=>x.h%2===0);
}

function getDays(){ return (forecastData && forecastData.days) || mockDays; }
function getHourly(dayIndex){
  if(forecastData && forecastData.hourlyByDay && forecastData.hourlyByDay[dayIndex]){
    return forecastData.hourlyByDay[dayIndex];
  }
  return mockHourlyForDay(dayIndex);
}

// ===================== Forecast wiring (Open-Meteo, via forecastcard.txt) =====================
const FORECAST_JSON_URL = './jsondata/forecastcard.txt';
const FORECAST_POLL_MS  = 10 * 60 * 1000;

let forecastData = null;
let forecastStatus = 'connecting';

function dayLabel(dateStr, index){
  if(index===0) return 'Today';
  const d = new Date(dateStr+'T00:00:00');
  return d.toLocaleDateString('en-GB', {weekday:'short'}) + ' ' + d.getDate();
}

async function pollForecast(){
  try{
    const res = await fetch(FORECAST_JSON_URL + ((FORECAST_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'});
    if(!res.ok) throw new Error('HTTP '+res.status);
    const j = await res.json();

    const cur = j.current || {};
    const current = {
      tempC: cur.temperature_2m,
      isDay: cur.is_day===1,
      windMs: kmh2ms(cur.wind_speed_10m),
      gustMs: kmh2ms(cur.wind_gusts_10m),
      windDirDeg: cur.wind_direction_10m,
      precipMm: cur.precipitation,
      cloudCoverPct: cur.cloud_cover,
      weatherCode: cur.weather_code,
      time: cur.time,
    };

    const h = j.hourly || {};
    const hourlyByDate = {};
    (h.time || []).forEach((iso, i) => {
      const dateStr = iso.slice(0,10);
      const hour = parseInt(iso.slice(11,13), 10);
      (hourlyByDate[dateStr] = hourlyByDate[dateStr] || []).push({
        h: hour,
        tempC: h.temperature_2m ? h.temperature_2m[i] : null,
        // Prefers Open-Meteo's own per-hour is_day (sunrise/sunset-accurate)
        // if the server-side poller that builds forecastcard.txt happens to
        // have requested it -- falls back to the fixed 6am-8pm clock guess
        // otherwise, unchanged from before. h.is_day here would come from
        // that same raw Open-Meteo passthrough (this file only reads
        // forecastcard.txt, it doesn't build the Open-Meteo request itself),
        // so getting this fully correct year-round (not just approximately
        // right outside of DST/seasonal daylight swings) requires the
        // server-side poller's own `hourly` parameter list to include
        // is_day -- not something visible or editable from this file.
        icon: wmoToIconKey(h.weather_code ? h.weather_code[i] : current.weatherCode,
          (h.is_day && h.is_day[i] != null) ? h.is_day[i] === 1 : (hour>=6 && hour<20)),
        rainMm: h.precipitation ? h.precipitation[i] : 0,
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
        dateStr,
        name: dayLabel(dateStr, i),
        icon: wmoToIconKey(code, true),
        cond: wmoText(code),
        hi: d.temperature_2m_max ? d.temperature_2m_max[i] : null,
        lo: d.temperature_2m_min ? d.temperature_2m_min[i] : null,
        rainProbPct: d.precipitation_probability_max ? d.precipitation_probability_max[i] : null,
        gustMs: d.wind_speed_10m_max ? kmh2ms(d.wind_speed_10m_max[i]) : null,
        warnSeverity: null,
      };
    });

    forecastData = {
      current,
      days,
      hourlyByDay: dailyDates.map(dateStr => hourlyByDate[dateStr] || []),
    };
    applyAlertWarnFlags();
    forecastStatus = 'live';
  }catch(e){
    console.warn('BirchesWX: forecast poll failed —', e.message);
    forecastStatus = forecastData ? 'stale' : 'demo';
  }
  render();
}

// ===================== Open-Meteo WMO weather-code mapping =====================
const WMO_TEXT = {
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
function wmoText(code){ return WMO_TEXT[code] || 'Unknown'; }
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
