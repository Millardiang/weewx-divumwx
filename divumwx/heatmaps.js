/*
##############################################################################################
# heatmaps.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== heatmaps.js =====================

(function(){
  const CHARTS_JSON_URL = './jsondata/charts.json';

  const WIND_CONV = { mph: 2.23694, kmh: 3.6, kt: 1.94384, ms: 1, bf: 1 };
  const WIND_LABEL = { mph: 'mph', kmh: 'km/h', kt: 'kt', ms: 'm/s', bf: 'm/s' };

  const PRESSURE_CONV = { hpa: 1, mbar: 1, inhg: 0.029529983071445, kpa: 0.1, mmhg: 0.750062 };
  const PRESSURE_LABEL = { hpa: 'hPa', mbar: 'mbar', inhg: 'inHg', kpa: 'kPa', mmhg: 'mmHg' };
  const PRESSURE_DECIMALS = { hpa: 1, mbar: 1, inhg: 2, kpa: 2, mmhg: 1 };
  const PRESSURE_GRADIENT = ['#4575b4', '#74add1', '#abd9e9', '#e0f3f8', '#ffffbf', '#fee090', '#fdae61', '#f46d43', '#d73027'];

  const TEMP_PIECES = {
    C: [
      { max: -10, color: '#8781bd', label: '\u2264\u221210\u00B0C' },
      { min: -10, max: 0, color: '#487ea9', label: '\u221210\u20130\u00B0C' },
      { min: 0, max: 5, color: '#3b9cac', label: '0\u20135\u00B0C' },
      { min: 5, max: 10, color: '#9aba2f', label: '5\u201310\u00B0C' },
      { min: 10, max: 20, color: '#e6a141', label: '10\u201320\u00B0C' },
      { min: 20, max: 25, color: '#ec5a34', label: '20\u201325\u00B0C' },
      { min: 25, max: 30, color: '#d05f2d', label: '25\u201330\u00B0C' },
      { min: 30, max: 35, color: '#d65b4a', label: '30\u201335\u00B0C' },
      { min: 35, max: 40, color: '#dc4953', label: '35\u201340\u00B0C' },
      { min: 40, color: '#e26870', label: '>40\u00B0C' },
    ],
    F: [
      { max: 14, color: '#8781bd', label: '\u226414\u00B0F' },
      { min: 14, max: 32, color: '#487ea9', label: '14\u201332\u00B0F' },
      { min: 32, max: 41, color: '#3b9cac', label: '32\u201341\u00B0F' },
      { min: 41, max: 50, color: '#9aba2f', label: '41\u201350\u00B0F' },
      { min: 50, max: 68, color: '#e6a141', label: '50\u201368\u00B0F' },
      { min: 68, max: 77, color: '#ec5a34', label: '68\u201377\u00B0F' },
      { min: 77, max: 86, color: '#d05f2d', label: '77\u201386\u00B0F' },
      { min: 86, max: 95, color: '#d65b4a', label: '86\u201395\u00B0F' },
      { min: 95, max: 104, color: '#dc4953', label: '95\u2013104\u00B0F' },
      { min: 104, color: '#e26870', label: '>104\u00B0F' },
    ],
  };

  const WIND_PIECES = {
    ms: [
      { min: 0, max: 1, color: '#85a3aa', label: '0\u20131 m/s' },
      { min: 1, max: 2, color: '#7e98bb', label: '1\u20132 m/s' },
      { min: 2, max: 3, color: '#6e90d0', label: '2\u20133 m/s' },
      { min: 3, max: 5, color: '#0f94a7', label: '3\u20135 m/s' },
      { min: 5, max: 8, color: '#39a239', label: '5\u20138 m/s' },
      { min: 8, max: 11, color: '#c2863e', label: '8\u201311 m/s' },
      { min: 11, max: 14, color: '#c8420d', label: '11\u201314 m/s' },
      { min: 14, max: 17, color: '#d20032', label: '14\u201317 m/s' },
      { min: 17, max: 21, color: '#af5088', label: '17\u201321 m/s' },
      { min: 21, max: 24, color: '#754a92', label: '21\u201324 m/s' },
      { min: 24, max: 28, color: '#45698d', label: '24\u201328 m/s' },
      { min: 28, max: 32, color: '#c1fc77', label: '28\u201332 m/s' },
      { min: 32, color: '#f1ff6c', label: '>32 m/s' },
    ],
    mph: [
      { min: 0, max: 2.2, color: '#85a3aa', label: '0\u20132.2 mph' },
      { min: 2.2, max: 4.5, color: '#7e98bb', label: '2.2\u20134.5 mph' },
      { min: 4.5, max: 6.7, color: '#6e90d0', label: '4.5\u20136.7 mph' },
      { min: 6.7, max: 11.2, color: '#0f94a7', label: '6.7\u201311.2 mph' },
      { min: 11.2, max: 17.9, color: '#39a239', label: '11.2\u201317.9 mph' },
      { min: 17.9, max: 24.6, color: '#c2863e', label: '17.9\u201324.6 mph' },
      { min: 24.6, max: 31.3, color: '#c8420d', label: '24.6\u201331.3 mph' },
      { min: 31.3, max: 38.0, color: '#d20032', label: '31.3\u201338 mph' },
      { min: 38.0, max: 47.0, color: '#af5088', label: '38\u201347 mph' },
      { min: 47.0, max: 53.7, color: '#754a92', label: '47\u201353.7 mph' },
      { min: 53.7, max: 62.6, color: '#45698d', label: '53.7\u201362.6 mph' },
      { min: 62.6, max: 71.6, color: '#c1fc77', label: '62.6\u201371.6 mph' },
      { min: 71.6, color: '#f1ff6c', label: '>71.6 mph' },
    ],
    kmh: [
      { min: 0, max: 3.6, color: '#85a3aa', label: '0\u20133.6 km/h' },
      { min: 3.6, max: 7.2, color: '#7e98bb', label: '3.6\u20137.2 km/h' },
      { min: 7.2, max: 10.8, color: '#6e90d0', label: '7.2\u201310.8 km/h' },
      { min: 10.8, max: 18.0, color: '#0f94a7', label: '10.8\u201318 km/h' },
      { min: 18.0, max: 28.8, color: '#39a239', label: '18\u201328.8 km/h' },
      { min: 28.8, max: 39.6, color: '#c2863e', label: '28.8\u201339.6 km/h' },
      { min: 39.6, max: 50.4, color: '#c8420d', label: '39.6\u201350.4 km/h' },
      { min: 50.4, max: 61.2, color: '#d20032', label: '50.4\u201361.2 km/h' },
      { min: 61.2, max: 75.6, color: '#af5088', label: '61.2\u201375.6 km/h' },
      { min: 75.6, max: 86.4, color: '#754a92', label: '75.6\u201386.4 km/h' },
      { min: 86.4, max: 100.8, color: '#45698d', label: '86.4\u2013100.8 km/h' },
      { min: 100.8, max: 115.2, color: '#c1fc77', label: '100.8\u2013115.2 km/h' },
      { min: 115.2, color: '#f1ff6c', label: '>115.2 km/h' },
    ],
    kt: [
      { min: 0, max: 1.9, color: '#85a3aa', label: '0\u20131.9 kt' },
      { min: 1.9, max: 3.9, color: '#7e98bb', label: '1.9\u20133.9 kt' },
      { min: 3.9, max: 5.8, color: '#6e90d0', label: '3.9\u20135.8 kt' },
      { min: 5.8, max: 9.7, color: '#0f94a7', label: '5.8\u20139.7 kt' },
      { min: 9.7, max: 15.6, color: '#39a239', label: '9.7\u201315.6 kt' },
      { min: 15.6, max: 21.4, color: '#c2863e', label: '15.6\u201321.4 kt' },
      { min: 21.4, max: 27.2, color: '#c8420d', label: '21.4\u201327.2 kt' },
      { min: 27.2, max: 33.0, color: '#d20032', label: '27.2\u201333 kt' },
      { min: 33.0, max: 40.8, color: '#af5088', label: '33\u201340.8 kt' },
      { min: 40.8, max: 46.7, color: '#754a92', label: '40.8\u201346.7 kt' },
      { min: 46.7, max: 54.4, color: '#45698d', label: '46.7\u201354.4 kt' },
      { min: 54.4, max: 62.2, color: '#c1fc77', label: '54.4\u201362.2 kt' },
      { min: 62.2, color: '#f1ff6c', label: '>62.2 kt' },
    ],
  };

  const SOLAR_PIECES = [
    { max: 100, color: '#808080', label: '0\u2013100 W/m\u00B2' },
    { min: 100, max: 300, color: '#6abc62', label: '100\u2013300 W/m\u00B2' },
    { min: 300, max: 600, color: '#f8d747', label: '300\u2013600 W/m\u00B2' },
    { min: 600, max: 900, color: '#f36633', label: '600\u2013900 W/m\u00B2' },
    { min: 900, max: 1200, color: '#ff0000', label: '900\u20131200 W/m\u00B2' },
    { min: 1200, max: 1500, color: '#b8125f', label: '1200\u20131500 W/m\u00B2' },
    { min: 1500, color: '#ff00ff', label: '>1500 W/m\u00B2' },
  ];

  const UV_PIECES = [
    { max: 1, color: '#808080', label: '0\u20131 UV' },
    { min: 1, max: 3, color: '#6abc62', label: '1\u20133 UV' },
    { min: 3, max: 6, color: '#f8d747', label: '3\u20136 UV' },
    { min: 6, max: 9, color: '#f36633', label: '6\u20139 UV' },
    { min: 9, max: 12, color: '#ff0000', label: '9\u201312 UV' },
    { min: 12, max: 15, color: '#b8125f', label: '12\u201315 UV' },
    { min: 15, color: '#ff00ff', label: '>15 UV' },
  ];

  const HUMID_PIECES = [
    { max: 30, color: '#ff6347', label: '0\u201330% (Dry)' },
    { min: 30, max: 70, color: '#2e8b57', label: '30\u201370% (Comfortable)' },
    { min: 70, color: '#007fff', label: '>70% (Humid)' },
  ];

  const RAIN_CONV = { mm: 1, in: 1 / 25.4 };
  const RAIN_PIECES = {
    mm: [
      { max: 0.2, color: '#cccccc', label: '0 mm' },
      { min: 0.2, max: 1, color: '#a8d5e8', label: '0.2\u20131 mm' },
      { min: 1, max: 4, color: '#6fa8d0', label: '1\u20134 mm' },
      { min: 4, max: 10, color: '#3b7fc4', label: '4\u201310 mm' },
      { min: 10, max: 20, color: '#39a239', label: '10\u201320 mm' },
      { min: 20, max: 40, color: '#e6a141', label: '20\u201340 mm' },
      { min: 40, max: 64, color: '#d05f2d', label: '40\u201364 mm' },
      { min: 64, color: '#d20032', label: '>64 mm' },
    ],
    in: [
      { max: 0.008, color: '#cccccc', label: '0 in' },
      { min: 0.008, max: 0.04, color: '#a8d5e8', label: '0.01\u20130.04 in' },
      { min: 0.04, max: 0.16, color: '#6fa8d0', label: '0.04\u20130.16 in' },
      { min: 0.16, max: 0.39, color: '#3b7fc4', label: '0.16\u20130.39 in' },
      { min: 0.39, max: 0.79, color: '#39a239', label: '0.39\u20130.79 in' },
      { min: 0.79, max: 1.57, color: '#e6a141', label: '0.79\u20131.57 in' },
      { min: 1.57, max: 2.52, color: '#d05f2d', label: '1.57\u20132.52 in' },
      { min: 2.52, color: '#d20032', label: '>2.52 in' },
    ],
  };
  function makeRainCategory(title, seriesKey){
    return {
      title: title,
      metrics: [
        { key: 'day', series: seriesKey, label: 'Daily Total', icon: '\uD83C\uDF27\uFE0F' },
      ],
      cellMetric: 'day',
      showMetricSelector: false,
      unitOf: sys => sys.rain,
      toDisplay: (mm, unit) => mm * (RAIN_CONV[unit] || 1),
      decimals: () => 2,
      pieces: unit => RAIN_PIECES[unit] || RAIN_PIECES.mm,
      unitLabel: unit => ' ' + (unit === 'in' ? 'in' : 'mm'),
      fmt(mm, unit){ return this.toDisplay(mm, unit).toFixed(this.decimals(unit)) + ' ' + (unit === 'in' ? 'in' : 'mm'); },
      buildStats(metricData, unit){
        const dayData = metricData.day || {};
        const dates = Object.keys(dayData);
        if (!dates.length) return null;
        let wettest = { valueC: -1, date: '' };
        let total = 0;
        dates.forEach(d => { const v = dayData[d].valueC; total += v; if (v > wettest.valueC) wettest = { valueC: v, date: d }; });
        const avg = total / dates.length;
        const sortedDates = dates.slice().sort();
        return [
          { icon: '\uD83C\uDF27\uFE0F', label: 'Wettest Day', color: '#3b7fc4', value: this.fmt(wettest.valueC, unit), sub: fmtDateFull(wettest.date) },
          { icon: '\uD83D\uDCA7', label: 'Total Rainfall', color: null, value: this.fmt(total, unit), sub: dates.length + ' days' },
          { icon: '\uD83D\uDCCA', label: 'Average Daily', color: null, value: this.fmt(avg, unit), sub: 'Overall' },
          { icon: '\uD83D\uDCC5', label: 'Data Range', color: null, value: fmtDateMonth(sortedDates[0]), sub: 'to ' + fmtDateMonth(sortedDates[sortedDates.length - 1]) },
        ];
      },
    };
  }

  function fmtDateFull(ds){ return new Date(ds + 'T00:00:00').toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }); }
  function fmtDateMonth(ds){ return new Date(ds + 'T00:00:00').toLocaleDateString('en-GB', { month: 'short', year: 'numeric' }); }

  // ===================== Category config =====================

  const HEATMAP_CATEGORIES = {
    temperature: {
      title: 'Temperature',
      metrics: [
        { key: 'max', series: 'outTempMax', label: 'Max', icon: '\uD83C\uDF21\uFE0F' },
        { key: 'min', series: 'outTempMin', label: 'Min', icon: '\u2744\uFE0F' },
        { key: 'avg', series: 'outTempAvg', label: 'Avg', icon: '\uD83D\uDCCA' },
      ],
      cellMetric: 'max',
      showMetricSelector: false,
      unitOf: sys => sys.temp,
      toDisplay: (c, unit) => unit === 'F' ? C2F(c) : c,
      decimals: () => 1,
      pieces: unit => TEMP_PIECES[unit] || TEMP_PIECES.C,
      unitLabel: unit => '\u00B0' + unit,
      fmt: (c, unit) => (unit === 'F' ? C2F(c) : c).toFixed(1) + '\u00B0' + unit,
      buildStats(metricData, unit){
        const maxData = metricData.max || {}, minData = metricData.min || {};
        const maxDates = Object.keys(maxData), minDates = Object.keys(minData);
        if (!maxDates.length) return null;
        let hottest = { valueC: -999, date: '' }, coldest = { valueC: 999, date: '' };
        maxDates.forEach(d => { if (maxData[d].valueC > hottest.valueC) hottest = { valueC: maxData[d].valueC, date: d }; });
        minDates.forEach(d => { if (minData[d].valueC < coldest.valueC) coldest = { valueC: minData[d].valueC, date: d }; });
        const values = maxDates.map(d => maxData[d].valueC);
        const avgC = values.reduce((a, b) => a + b, 0) / values.length;
        const sortedDates = maxDates.slice().sort();
        return [
          { icon: '\uD83D\uDD25', label: 'Hottest Day', color: '#e26870', value: this.toDisplay(hottest.valueC, unit).toFixed(1) + '\u00B0', sub: fmtDateFull(hottest.date) },
          { icon: '\u2744\uFE0F', label: 'Coldest Day', color: '#487ea9', value: minDates.length ? this.toDisplay(coldest.valueC, unit).toFixed(1) + '\u00B0' : 'N/A', sub: minDates.length ? fmtDateFull(coldest.date) : '' },
          { icon: '\uD83D\uDCCA', label: 'Average Max', color: null, value: this.toDisplay(avgC, unit).toFixed(1) + '\u00B0', sub: 'Overall' },
          { icon: '\uD83D\uDCC5', label: 'Data Range', color: null, value: fmtDateMonth(sortedDates[0]), sub: 'to ' + fmtDateMonth(sortedDates[sortedDates.length - 1]) },
        ];
      },
    },
    wind: {
      title: 'Wind',
      metrics: [
        { key: 'gustmax', series: 'windGustMax', label: 'Gust Max', icon: '\uD83D\uDCA8' },
        { key: 'gustavg', series: 'windGustAvg', label: 'Gust Avg', icon: '\uD83C\uDF2C\uFE0F' },
      ],
      cellMetric: 'gustmax',
      showMetricSelector: true,
      unitOf: sys => sys.wind,
      toDisplay: (ms, unit) => ms * (WIND_CONV[unit] || 1),
      decimals: () => 1,
      pieces: unit => WIND_PIECES[unit] || WIND_PIECES.ms,
      unitLabel: unit => ' ' + (WIND_LABEL[unit] || 'm/s'),
      fmt(ms, unit){ return this.toDisplay(ms, unit).toFixed(1) + ' ' + (WIND_LABEL[unit] || 'm/s'); },
      buildStats(metricData, unit){
        const maxData = metricData.gustmax || {}, avgData = metricData.gustavg || {};
        const maxDates = Object.keys(maxData);
        if (!maxDates.length) return null;
        let peak = { valueC: -999, date: '' };
        maxDates.forEach(d => { if (maxData[d].valueC > peak.valueC) peak = { valueC: maxData[d].valueC, date: d }; });
        const avgDates = Object.keys(avgData);
        const avgSourceDates = avgDates.length ? avgDates : maxDates;
        const avgSource = avgDates.length ? avgData : maxData;
        const avgValues = avgSourceDates.map(d => avgSource[d].valueC);
        const avgMs = avgValues.reduce((a, b) => a + b, 0) / avgValues.length;
        const allDates = maxDates.concat(avgDates).sort();
        return [
          { icon: '\uD83D\uDCA8', label: 'Peak Gust', color: '#c8420d', value: this.toDisplay(peak.valueC, unit).toFixed(1) + ' ' + (WIND_LABEL[unit] || 'm/s'), sub: fmtDateFull(peak.date) },
          { icon: '\uD83C\uDF2C\uFE0F', label: avgDates.length ? 'Avg Gust' : 'Avg Gust (from Max)', color: '#39a239', value: this.toDisplay(avgMs, unit).toFixed(1) + ' ' + (WIND_LABEL[unit] || 'm/s'), sub: 'Overall average' },
          { icon: '\uD83D\uDCCA', label: 'Data Points', color: null, value: String(maxDates.length), sub: avgDates.length ? (avgDates.length + ' avg gusts') : 'Gust Max only' },
          { icon: '\uD83D\uDCC5', label: 'Data Range', color: null, value: fmtDateMonth(allDates[0]), sub: 'to ' + fmtDateMonth(allDates[allDates.length - 1]) },
        ];
      },
    },
    barometer: {
      title: 'Barometric Pressure',
      metrics: [
        { key: 'max', series: 'baromMax', label: 'Max', icon: '\uD83D\uDCC8' },
        { key: 'min', series: 'baromMin', label: 'Min', icon: '\uD83D\uDCC9' },
        { key: 'avg', series: 'baromAvg', label: 'Avg', icon: '\uD83D\uDCCA' },
      ],
      cellMetric: 'max',
      showMetricSelector: true,

      visualMapType: 'continuous',
      gradientColors: PRESSURE_GRADIENT,
      unitOf: sys => sys.pressure,
      toDisplay: (hpa, unit) => hpa * (PRESSURE_CONV[unit] || 1),
      decimals: unit => PRESSURE_DECIMALS[unit] != null ? PRESSURE_DECIMALS[unit] : 1,
      unitLabel: unit => ' ' + (PRESSURE_LABEL[unit] || 'hPa'),
      fmt(hpa, unit){ return this.toDisplay(hpa, unit).toFixed(this.decimals(unit)) + ' ' + (PRESSURE_LABEL[unit] || 'hPa'); },
      buildStats(metricData, unit){
        const maxData = metricData.max || {}, minData = metricData.min || {}, avgData = metricData.avg || {};
        const maxDates = Object.keys(maxData), minDates = Object.keys(minData), avgDates = Object.keys(avgData);
        if (!maxDates.length && !minDates.length) return null;
        let highest = { valueC: -999, date: '' }, lowest = { valueC: 9999, date: '' };
        maxDates.forEach(d => { if (maxData[d].valueC > highest.valueC) highest = { valueC: maxData[d].valueC, date: d }; });
        minDates.forEach(d => { if (minData[d].valueC < lowest.valueC) lowest = { valueC: minData[d].valueC, date: d }; });
        const avgValues = avgDates.length ? avgDates.map(d => avgData[d].valueC) : maxDates.map(d => maxData[d].valueC);
        const avgVal = avgValues.reduce((a, b) => a + b, 0) / avgValues.length;
        const allDates = maxDates.concat(minDates, avgDates).sort();
        const dp = this.decimals(unit);
        return [
          { icon: '\uD83D\uDCC8', label: 'Highest Pressure', color: '#d73027', value: maxDates.length ? this.toDisplay(highest.valueC, unit).toFixed(dp) + ' ' + (PRESSURE_LABEL[unit] || 'hPa') : 'N/A', sub: maxDates.length ? fmtDateFull(highest.date) : '' },
          { icon: '\uD83D\uDCC9', label: 'Lowest Pressure', color: '#4575b4', value: minDates.length ? this.toDisplay(lowest.valueC, unit).toFixed(dp) + ' ' + (PRESSURE_LABEL[unit] || 'hPa') : 'N/A', sub: minDates.length ? fmtDateFull(lowest.date) : '' },
          { icon: '\uD83D\uDCCA', label: 'Average Pressure', color: null, value: this.toDisplay(avgVal, unit).toFixed(dp) + ' ' + (PRESSURE_LABEL[unit] || 'hPa'), sub: avgDates.length ? 'Overall' : 'From Max' },
          { icon: '\uD83D\uDCC5', label: 'Data Range', color: null, value: fmtDateMonth(allDates[0]), sub: 'to ' + fmtDateMonth(allDates[allDates.length - 1]) },
        ];
      },
    },
    solarRadiation: {
      title: 'Solar Radiation',
      metrics: [
        { key: 'max', series: 'solarMax', label: 'Max', icon: '\u2600\uFE0F' },
        { key: 'avg', series: 'solarAvg', label: 'Avg', icon: '\uD83D\uDCCA' },
      ],
      cellMetric: 'max',
      showMetricSelector: true,
      unitOf: () => 'wm2',
      toDisplay: v => v,
      decimals: () => 0,
      unitLabel: () => ' W/m\u00B2',
      pieces: () => SOLAR_PIECES,
      fmt(v){ return Math.round(v) + ' W/m\u00B2'; },
      buildStats(metricData, unit){
        const maxData = metricData.max || {}, avgData = metricData.avg || {};
        const maxDates = Object.keys(maxData), avgDates = Object.keys(avgData);
        if (!maxDates.length) return null;
        let peak = { valueC: -1, date: '' };
        maxDates.forEach(d => { if (maxData[d].valueC > peak.valueC) peak = { valueC: maxData[d].valueC, date: d }; });
        const avgSourceDates = avgDates.length ? avgDates : maxDates;
        const avgSource = avgDates.length ? avgData : maxData;
        const avgVal = avgSourceDates.map(d => avgSource[d].valueC).reduce((a, b) => a + b, 0) / avgSourceDates.length;
        const allDates = maxDates.concat(avgDates).sort();
        return [
          { icon: '\u2600\uFE0F', label: 'Peak Radiation', color: '#ff0000', value: Math.round(peak.valueC) + ' W/m\u00B2', sub: fmtDateFull(peak.date) },
          { icon: '\uD83D\uDCCA', label: avgDates.length ? 'Average Radiation' : 'Average (from Max)', color: null, value: Math.round(avgVal) + ' W/m\u00B2', sub: avgDates.length ? 'Overall' : 'From Max' },
          { icon: '\uD83D\uDCC8', label: 'Data Points', color: null, value: String(maxDates.length), sub: avgDates.length ? (avgDates.length + ' avg readings') : 'Max only' },
          { icon: '\uD83D\uDCC5', label: 'Data Range', color: null, value: fmtDateMonth(allDates[0]), sub: 'to ' + fmtDateMonth(allDates[allDates.length - 1]) },
        ];
      },
    },
    uv: {
      title: 'UV Index',
      metrics: [
        { key: 'max', series: 'UVMax', label: 'Max', icon: '\u2600\uFE0F' },
        { key: 'avg', series: 'UVAvg', label: 'Avg', icon: '\uD83D\uDCCA' },
      ],
      cellMetric: 'max',
      showMetricSelector: true,
      unitOf: () => 'uv',
      toDisplay: v => v,
      decimals: () => 0,
      unitLabel: () => ' UV',
      pieces: () => UV_PIECES,
      fmt(v){ return v.toFixed(0) + ' UV'; },
      buildStats(metricData, unit){
        const maxData = metricData.max || {}, avgData = metricData.avg || {};
        const maxDates = Object.keys(maxData), avgDates = Object.keys(avgData);
        if (!maxDates.length) return null;
        let peak = { valueC: -1, date: '' };
        maxDates.forEach(d => { if (maxData[d].valueC > peak.valueC) peak = { valueC: maxData[d].valueC, date: d }; });
        const avgSourceDates = avgDates.length ? avgDates : maxDates;
        const avgSource = avgDates.length ? avgData : maxData;
        const avgVal = avgSourceDates.map(d => avgSource[d].valueC).reduce((a, b) => a + b, 0) / avgSourceDates.length;
        const allDates = maxDates.concat(avgDates).sort();
        return [
          { icon: '\u2600\uFE0F', label: 'Peak UV', color: '#ff0000', value: peak.valueC.toFixed(0) + ' UV', sub: fmtDateFull(peak.date) },
          { icon: '\uD83D\uDCCA', label: avgDates.length ? 'Average UV' : 'Average (from Max)', color: null, value: avgVal.toFixed(0) + ' UV', sub: avgDates.length ? 'Overall' : 'From Max' },
          { icon: '\uD83D\uDCC8', label: 'Data Points', color: null, value: String(maxDates.length), sub: avgDates.length ? (avgDates.length + ' avg readings') : 'Max only' },
          { icon: '\uD83D\uDCC5', label: 'Data Range', color: null, value: fmtDateMonth(allDates[0]), sub: 'to ' + fmtDateMonth(allDates[allDates.length - 1]) },
        ];
      },
    },
    humidity: {
      title: 'Humidity',
      metrics: [
        { key: 'max', series: 'outHumidMax', label: 'Max', icon: '\uD83D\uDCA7' },
        { key: 'min', series: 'outHumidMin', label: 'Min', icon: '\u2600\uFE0F' },
        { key: 'avg', series: 'outHumidAvg', label: 'Avg', icon: '\uD83D\uDCCA' },
      ],
      cellMetric: 'max',
      showMetricSelector: true,
      unitOf: () => 'pct',
      toDisplay: v => v,
      decimals: () => 0,
      unitLabel: () => '%',
      pieces: () => HUMID_PIECES,
      fmt(v){ return Math.round(v) + '%'; },
      buildStats(metricData, unit){
        const maxData = metricData.max || {}, minData = metricData.min || {}, avgData = metricData.avg || {};
        const maxDates = Object.keys(maxData), minDates = Object.keys(minData), avgDates = Object.keys(avgData);
        if (!maxDates.length && !minDates.length) return null;
        let highest = { valueC: -1, date: '' }, lowest = { valueC: 101, date: '' };
        maxDates.forEach(d => { if (maxData[d].valueC > highest.valueC) highest = { valueC: maxData[d].valueC, date: d }; });
        minDates.forEach(d => { if (minData[d].valueC < lowest.valueC) lowest = { valueC: minData[d].valueC, date: d }; });
        const avgSourceDates = avgDates.length ? avgDates : maxDates;
        const avgSource = avgDates.length ? avgData : maxData;
        const avgVal = avgSourceDates.map(d => avgSource[d].valueC).reduce((a, b) => a + b, 0) / avgSourceDates.length;
        const allDates = maxDates.concat(minDates, avgDates).sort();
        return [
          { icon: '\uD83D\uDCA7', label: 'Most Humid', color: '#007fff', value: maxDates.length ? Math.round(highest.valueC) + '%' : 'N/A', sub: maxDates.length ? fmtDateFull(highest.date) : '' },
          { icon: '\u2600\uFE0F', label: 'Driest', color: '#ff6347', value: minDates.length ? Math.round(lowest.valueC) + '%' : 'N/A', sub: minDates.length ? fmtDateFull(lowest.date) : '' },
          { icon: '\uD83D\uDCCA', label: avgDates.length ? 'Average Humidity' : 'Average (from Max)', color: null, value: Math.round(avgVal) + '%', sub: avgDates.length ? 'Overall' : 'From Max' },
          { icon: '\uD83D\uDCC5', label: 'Data Range', color: null, value: fmtDateMonth(allDates[0]), sub: 'to ' + fmtDateMonth(allDates[allDates.length - 1]) },
        ];
      },
    },
    rain: makeRainCategory('Rainfall', 'rain'),
    prain: makeRainCategory('Piezo Rainfall', 'prain'),
  };

  function getInitialCategory(){
    const t = new URL(window.location.href).searchParams.get('type');
    return HEATMAP_CATEGORIES[t] ? t : 'temperature';
  }
  let categoryKey = getInitialCategory();
  let category = HEATMAP_CATEGORIES[categoryKey];
  let cellMetric = category.cellMetric;
  let stationLocation = '';

  const style = document.createElement('style');
  style.textContent = `
    [data-embed="1"] .hm-tabs{ display:none; }
    [data-embed="1"] .site-navbar-include{ display:none; }

    .hm-tabs{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:18px;}
    .hm-tab{
      font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:13px;font-weight:600;
      padding:9px 18px;border-radius:999px;border:1px solid var(--bs-border-color);
      background-color:var(--bs-card-bg);color:var(--bs-body-color);
      cursor:pointer;transition:.15s;
    }
    .hm-tab:hover{border-color:var(--bw-accent);}
    .hm-tab.active{background-color:var(--bw-accent);color:#1B1407;border-color:var(--bw-accent);}

    :root, [data-bs-theme="light"], html:not([data-bs-theme]) {
      --bs-body-bg: #EFECE2;
      --bs-body-color: #1D2C4E;
      --bs-secondary-color: #5C6672;
      --bs-card-bg: #FFFFFF;
      --bs-border-color: #C9CFD8;
      --bw-radius-sm: 10px;
      --bw-accent: #B45309;
      --bw-shadow: 0 1px 2px rgba(0,0,0,0.3), 0 8px 24px -8px rgba(0,0,0,0.5);
    }
    [data-bs-theme="dark"] {
      --bs-body-bg: #0A0F22;
      --bs-body-color: #E9E4D4;
      --bs-secondary-color: #8B93B8;
      --bs-card-bg: #111834;
      --bs-border-color: #2A3358;
      --bw-accent: #D3A94C;
    }
    /* Seasonal accent mode — inactive tabs (.hm-tab), buttons (.hm-btn),
       and panels (.hm-stats-card, .hm-tooltip, etc.) already read
       --bs-card-bg, so aliasing it to the site's seasonal card tint
       (--card-bg, from header.css) covers all of them at once. Header/
       navbar chrome picks up the darker seasonal colour automatically
       via header.css once body has the season-* class. */
    body.season-winter, body.season-spring, body.season-summer, body.season-autumn {
      --bs-card-bg: var(--card-bg);
    }
    * { box-sizing: border-box; }
    html, body { margin: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
    body {
      background: var(--bs-body-bg);
      color: var(--bs-body-color);
      max-width: 1100px;
      margin: 0 auto;
      padding: 16px;
      transition: background-color .2s, color .2s;
    }
    .hm-title-row { display:flex; align-items:baseline; justify-content:space-between; flex-wrap:wrap; gap:8px; margin-bottom:14px; }
    .hm-title-row h1 { font-size:20px; font-weight:700; margin:0; }
    .hm-secondary { color: var(--bs-secondary-color); }
    .hm-live-dot { display:inline-block; width:7px; height:7px; border-radius:50%; background: var(--bs-secondary-color); margin-right:6px; }

    .hm-toolbar { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:10px; }
    .hm-nav-group { display:flex; gap:8px; align-items:center; }
    .hm-btn {
      border:none; border-radius:6px; padding:6px 14px; font-size:13px; cursor:pointer;
      background: var(--bs-card-bg); color: var(--bs-body-color); border:1px solid var(--bs-border-color);
    }
    .hm-btn:hover { filter: brightness(1.08); }
    .hm-current-month { font-size:17px; font-weight:700; padding:0 6px; cursor:pointer; }
    .hm-data-info { font-size:12px; padding:4px 10px; background: var(--bs-card-bg); border-radius:20px; color: var(--bs-secondary-color); border:1px solid var(--bs-border-color); }

    .hm-metric-selector { display:flex; gap:10px; align-items:center; background: var(--bs-card-bg); border:1px solid var(--bs-border-color); border-radius:20px; padding:4px 12px; }
    .hm-metric-option { display:flex; align-items:center; gap:5px; font-size:12px; cursor:pointer; }
    .hm-metric-option input { accent-color: var(--bw-accent); cursor:pointer; }
    .hm-metric-option.is-disabled { opacity:.4; cursor:not-allowed; }

    #hmChart { width:100%; height:420px; }

    .hm-legend-wrap { display:flex; flex-wrap:wrap; justify-content:center; gap:6px 12px; padding:10px 12px 4px; }
    .hm-legend-item { display:flex; align-items:center; gap:5px; font-size:11px; font-weight:600; white-space:nowrap; }
    .hm-legend-swatch { width:22px; height:16px; border-radius:3px; flex-shrink:0; }

    .hm-stats-card { margin-top:16px; background: var(--bs-card-bg); border-radius: var(--bw-radius-sm); box-shadow: var(--bw-shadow); padding:16px; }
    .hm-stats-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap:12px; }
    .hm-stat-item { text-align:center; padding:10px 6px; }
    .hm-stat-label { font-size:11px; font-weight:600; color: var(--bs-secondary-color); margin-bottom:6px; text-transform:uppercase; letter-spacing:.05em; }
    .hm-stat-value { font-size:20px; font-weight:700; }
    .hm-stat-date { font-size:10px; color: var(--bs-secondary-color); margin-top:6px; font-family:'JetBrains Mono',monospace; }

    .hm-scale-bar { display:flex; align-items:center; gap:12px; margin-top:14px; }
    .hm-color-gradient { flex:1; height:20px; border-radius:10px; }
    .hm-scale-label { font-size:12px; font-weight:700; }

    .hm-empty-state { text-align:center; padding:60px 0; color: var(--bs-secondary-color); }

    .hm-tooltip {
      position:fixed; pointer-events:none; font-size:12px; line-height:1.5; z-index:9999;
      background: var(--bs-card-bg); border:1px solid var(--bs-border-color); border-radius:8px;
      padding:10px 14px; box-shadow: var(--bw-shadow); color: var(--bs-body-color); display:none;
    }
    #hmChart svg { width:100%; height:100%; display:block; }
  `;
  document.head.appendChild(style);

  document.body.innerHTML = `
    <div class="site-navbar-include" w3-include-html="navbar.html"></div>
    <div class="hm-tabs" id="hmTabs">
      ${Object.keys(HEATMAP_CATEGORIES).map(k =>
        `<button class="hm-tab${k === categoryKey ? ' active' : ''}" data-cat="${k}">${HEATMAP_CATEGORIES[k].title}</button>`
      ).join('')}
    </div>
    <div class="hm-title-row">
      <h1 id="hmPageTitle"></h1>
      <span class="hm-secondary" style="font-size:12px;"><span class="hm-live-dot" id="hmLiveDot"></span><span id="hmLiveLabel">Connecting\u2026</span></span>
    </div>
    <div class="hm-toolbar">
      <div class="hm-nav-group">
        <button class="hm-btn" id="hmPrev">\u25C0 Prev</button>
        <span class="hm-current-month" id="hmCurrentMonth" title="Double-click to jump to the current month"></span>
        <button class="hm-btn" id="hmNext">Next \u25B6</button>
      </div>
      <div class="hm-metric-selector" id="hmMetricSelector" style="display:none;"></div>
      <span class="hm-data-info" id="hmDataInfo">\u2013</span>
    </div>
    <div id="hmChart"></div>
    <div class="hm-legend-wrap" id="hmLegend"></div>
    <div class="hm-stats-card" id="hmStatsCard" style="display:none;">
      <div class="hm-stats-grid" id="hmStatsGrid"></div>
      <div class="hm-scale-bar">
        <span class="hm-scale-label" id="hmScaleMin"></span>
        <div class="hm-color-gradient" id="hmColorGradient"></div>
        <span class="hm-scale-label" id="hmScaleMax"></span>
      </div>
      <div class="hm-secondary" id="hmScaleCaption" style="text-align:center;font-size:10.5px;margin-top:8px;display:none;"></div>
    </div>
    <div class="hm-empty-state" id="hmEmptyState" style="display:none;"></div>
    <div class="hm-tooltip" id="hmTooltip"></div>
  `;

  const els = {
    tabs: document.getElementById('hmTabs'),
    pageTitle: document.getElementById('hmPageTitle'),
    liveDot: document.getElementById('hmLiveDot'),
    liveLabel: document.getElementById('hmLiveLabel'),
    prev: document.getElementById('hmPrev'),
    next: document.getElementById('hmNext'),
    currentMonth: document.getElementById('hmCurrentMonth'),
    dataInfo: document.getElementById('hmDataInfo'),
    metricSelector: document.getElementById('hmMetricSelector'),
    chart: document.getElementById('hmChart'),
    legend: document.getElementById('hmLegend'),
    statsCard: document.getElementById('hmStatsCard'),
    statsGrid: document.getElementById('hmStatsGrid'),
    colorGradient: document.getElementById('hmColorGradient'),
    scaleMin: document.getElementById('hmScaleMin'),
    scaleMax: document.getElementById('hmScaleMax'),
    scaleCaption: document.getElementById('hmScaleCaption'),
    emptyState: document.getElementById('hmEmptyState'),
    tooltip: document.getElementById('hmTooltip'),
  };

  function updatePageTitle(){
    els.pageTitle.textContent = (stationLocation ? stationLocation + ' \u2014 ' : '') + category.title + ' Heatmap';
    els.emptyState.textContent = 'No ' + category.title.toLowerCase() + ' history available yet.';
  }
  updatePageTitle();

  function currentUnit(){
    try {
      const key = localStorage.getItem('dashboardUnitSystem') || 'uk';
      if (typeof SYSTEMS !== 'undefined' && SYSTEMS[key]) return category.unitOf(SYSTEMS[key]);
    } catch (e) {}
    return category.unitOf({ temp: 'C', wind: 'mph' });
  }

  let lastIsDay = null;
  let themeMode = localStorage.getItem('dashboardThemeMode') || 'auto';
  const THEME_ICONS = { auto: '\u{1F313}', light: '\u2600\uFE0F', dark: '\u{1F319}' };
  const THEME_ORDER = ['dark', 'light', 'auto'];
  if (!THEME_ORDER.includes(themeMode)) themeMode = 'dark';
  let currentSeason = null;
  function resolveAutoIsDay(){
    if (lastIsDay != null) return !!lastIsDay;
    return !window.matchMedia('(prefers-color-scheme: dark)').matches;
  }
  function resolveTheme(){

    if (themeMode === 'seasonal') return 'light';
    return themeMode === 'light' ? 'light' : themeMode === 'dark' ? 'dark' : (resolveAutoIsDay() ? 'light' : 'dark');
  }
  function applyTheme(){
    document.documentElement.setAttribute('data-bs-theme', resolveTheme());
    currentSeason = applySeasonClass(themeMode);
    syncThemeNavUI();
    if (dataAvailable) buildHeatmap();
  }
  function syncThemeNavUI(){
    const btn = document.getElementById('themeToggle');
    if (!btn) return;
    const resolved = resolveTheme();
    if (themeMode === 'seasonal') {
      btn.textContent = SEASON_ICONS[currentSeason];
      btn.title = 'Seasonal (' + seasonLabel(currentSeason) + ')';
    } else {
      btn.textContent = THEME_ICONS[themeMode];
      btn.title = themeMode === 'auto' ? ('Auto (currently ' + resolved + ')') : (themeMode.charAt(0).toUpperCase() + themeMode.slice(1));
    }
  }
  function pollIsDayForTheme(){
    fetch('./jsondata/loop.json?_=' + Date.now(), { cache: 'no-store' })
      .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(j => {
        const o = (j && j.observations) || {};
        if (o.isDay != null) { lastIsDay = o.isDay; if (themeMode === 'auto') applyTheme(); }
      })
      .catch(() => {});
  }
  function isDarkTheme(){ return document.documentElement.getAttribute('data-bs-theme') === 'dark'; }

  function reportHeight(){
    if (window.parent === window) return;
    window.parent.postMessage({ type: 'chartModalContentHeight', height: document.body.scrollHeight }, '*');
  }
  if (window.parent !== window) new ResizeObserver(reportHeight).observe(document.body);

  function setLive(status){
    if (status === 'live') { els.liveDot.style.background = '#5BBB8A'; els.liveLabel.textContent = 'Live'; }
    else { els.liveDot.style.background = 'var(--bs-secondary-color)'; els.liveLabel.textContent = 'Unavailable'; }
  }

  fetch('./jsondata/archive.json?_=' + Date.now(), { cache: 'no-store' })
    .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
    .then(data => {
      const loc = data && data.meta && data.meta.station_location;
      if (loc) { stationLocation = loc; updatePageTitle(); updateBrandText(); }
    })
    .catch(() => {});

  function updateBrandText(){
    const el = document.querySelector('.brand-text');
    if (stationLocation && el) el.textContent = stationLocation;
  }

  let metricDataByKey = {};
  let monthDataByMonth = {};
  let dataAvailable = false;

  function extractSeries(series, key){
    const out = {};
    const arr = series && series[key];
    if (!Array.isArray(arr)) return out;
    arr.forEach(item => {
      if (!Array.isArray(item) || item.length < 2 || item[1] == null) return;
      const timestamp = item[0];
      const valueC = parseFloat(item[1]);
      if (isNaN(valueC)) return;
      const d = new Date(timestamp);
      const dateStr = d.toISOString().slice(0, 10);
      out[dateStr] = { timestamp, valueC, date: dateStr };
    });
    return out;
  }

  function buildMonthly(dataByKey){
    const byMonth = {};
    Object.keys(dataByKey).forEach(metricKey => {
      const data = dataByKey[metricKey];
      Object.keys(data).forEach(dateStr => {
        const item = data[dateStr];
        const d = new Date(item.timestamp);
        const year = d.getFullYear(), month = d.getMonth(), day = d.getDate();
        const key = year + '-' + month;
        if (!byMonth[key]) byMonth[key] = {};
        if (!byMonth[key][day]) byMonth[key][day] = {};
        byMonth[key][day][metricKey] = { valueC: item.valueC, date: item.date };
      });
    });
    return byMonth;
  }

  function buildMetricSelector(){
    if (!els.metricSelector) return;
    if (!category.showMetricSelector) { els.metricSelector.innerHTML = ''; els.metricSelector.style.display = 'none'; return; }
    els.metricSelector.style.display = '';
    els.metricSelector.innerHTML = category.metrics.map(m => {
      const hasData = Object.keys(metricDataByKey[m.key] || {}).length > 0;
      const disabled = !hasData;
      const checked = m.key === cellMetric ? 'checked' : '';
      return '<label class="hm-metric-option' + (disabled ? ' is-disabled' : '') + '" title="' + (disabled ? 'No ' + m.label + ' data available' : '') + '">' +
        '<input type="radio" name="hmMetric" value="' + m.key + '" ' + checked + (disabled ? ' disabled' : '') + '> ' + m.icon + ' ' + m.label +
        '</label>';
    }).join('');
    els.metricSelector.querySelectorAll('input[name="hmMetric"]').forEach(radio => {
      radio.addEventListener('change', e => { cellMetric = e.target.value; buildHeatmap(); });
    });
  }

  let rawSeries = null;

  function fetchChartsJson(){
    return fetch(CHARTS_JSON_URL + '?_=' + Date.now(), { cache: 'no-store' })
      .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(json => {
        const series = json && json[0] && json[0].chartPlot && json[0].chartPlot.series;
        if (!series) throw new Error('Unexpected charts.json shape \u2014 no chartPlot.series found');
        rawSeries = series;
      });
  }

  function applyCategoryData(){
    if (!rawSeries) {
      metricDataByKey = {}; monthDataByMonth = {}; dataAvailable = false;
      els.dataInfo.textContent = 'Unavailable';
      setLive('down');
      els.statsCard.style.display = 'none';
      buildMetricSelector();
      return;
    }
    try {
      metricDataByKey = {};
      category.metrics.forEach(m => { metricDataByKey[m.key] = extractSeries(rawSeries, m.series); });
      const primaryCount = Object.keys(metricDataByKey[category.cellMetric] || {}).length;
      if (!primaryCount) throw new Error('No ' + category.metrics.find(m => m.key === category.cellMetric).series + ' data in charts.json');
      monthDataByMonth = buildMonthly(metricDataByKey);
      dataAvailable = true;
      els.dataInfo.textContent = primaryCount + ' days';
      setLive('live');
      buildMetricSelector();
      buildStats();
    } catch (e) {
      console.warn('heatmaps (' + categoryKey + '): ' + e.message);
      metricDataByKey = {}; monthDataByMonth = {}; dataAvailable = false;
      els.dataInfo.textContent = 'Unavailable';
      setLive('down');
      els.statsCard.style.display = 'none';
      buildMetricSelector();
    }
  }

  function switchCategory(key){
    if (!HEATMAP_CATEGORIES[key] || key === categoryKey) return;
    categoryKey = key;
    category = HEATMAP_CATEGORIES[key];
    cellMetric = category.cellMetric;

    const url = new URL(window.location.href);
    url.searchParams.set('type', key);
    history.replaceState(null, '', url);

    document.querySelectorAll('.hm-tab').forEach(b => b.classList.toggle('active', b.dataset.cat === key));
    updatePageTitle();
    applyCategoryData();
    buildHeatmap();
  }

  function buildStats(){
    if (!dataAvailable) { els.statsCard.style.display = 'none'; return; }
    const unit = currentUnit();
    const tiles = category.buildStats(metricDataByKey, unit);
    if (!tiles) { els.statsCard.style.display = 'none'; return; }
    els.statsCard.style.display = '';
    els.statsGrid.innerHTML = tiles.map(t =>
      '<div class="hm-stat-item">' +
        '<div class="hm-stat-label">' + t.icon + ' ' + t.label + '</div>' +
        '<div class="hm-stat-value"' + (t.color ? ' style="color:' + t.color + ';"' : '') + '>' + t.value + '</div>' +
        '<div class="hm-stat-date">' + (t.sub || '') + '</div>' +
      '</div>'
    ).join('');

    const primaryKey = category.cellMetric;
    const primaryData = metricDataByKey[primaryKey] || {};
    const rawValues = Object.keys(primaryData).map(d => primaryData[d].valueC);
    const displayValues = rawValues.length ? rawValues.map(v => category.toDisplay(v, unit)) : [0];
    const scaleLo = Math.floor(Math.min.apply(null, displayValues));
    const scaleHiRaw = Math.ceil(Math.max.apply(null, displayValues));
    const scaleHi = scaleHiRaw > scaleLo ? scaleHiRaw : scaleLo + 10;
    const gradientColors = category.visualMapType === 'continuous' ? category.gradientColors : category.pieces(unit).map(p => p.color);
    els.scaleMin.textContent = scaleLo + (category.unitLabel ? category.unitLabel(unit) : '');
    els.scaleMin.style.color = gradientColors[0];
    els.scaleMax.textContent = scaleHi + (category.unitLabel ? category.unitLabel(unit) : '');
    els.scaleMax.style.color = gradientColors[gradientColors.length - 1];
    els.colorGradient.style.background = 'linear-gradient(to right, ' + gradientColors.join(', ') + ')';
    if (category.visualMapType === 'continuous'){
      els.scaleCaption.style.display = '';
      els.scaleCaption.textContent = '\uD83D\uDD35 Low \u00B7 \u26AA Normal \u00B7 \uD83D\uDD34 High';
    } else {
      els.scaleCaption.style.display = 'none';
    }
  }

  function buildLegend(unit){
    if (category.visualMapType === 'continuous') { els.legend.innerHTML = ''; return; }
    const pieces = category.pieces(unit);
    els.legend.innerHTML = pieces.map(p =>
      '<div class="hm-legend-item"><div class="hm-legend-swatch" style="background:' + p.color + ';"></div><span>' + p.label + '</span></div>'
    ).join('');
  }

  let currentYear = new Date().getFullYear();
  let currentMonth = new Date().getMonth();

  function responsiveFontSizes(){
    const w = els.chart.offsetWidth || window.innerWidth;
    if (w < 360) return { value: 9, day: 7 };
    if (w < 480) return { value: 11, day: 8 };
    if (w < 600) return { value: 13, day: 9 };
    return { value: 16, day: 10 };
  }

  function getDayOfWeek(year, month, day){
    return new Date(year, month, day).toLocaleDateString('en-GB', { weekday: 'short' });
  }

  function colorForValue(display, unit){
    if (category.visualMapType === 'continuous') {
      const primaryData = metricDataByKey[cellMetric] || {};
      const rawVals = Object.keys(primaryData).map(d => primaryData[d].valueC);
      const rawLo = rawVals.length ? Math.min.apply(null, rawVals) - 5 : 980;
      const rawHi = rawVals.length ? Math.max.apply(null, rawVals) + 5 : 1040;
      const dispLo = category.toDisplay(rawLo, unit), dispHi = category.toDisplay(rawHi, unit);
      const t = dispHi > dispLo ? (display - dispLo) / (dispHi - dispLo) : 0.5;
      const interp = d3.interpolateRgbBasis(category.gradientColors);
      return interp(Math.max(0, Math.min(1, t)));
    }
    const pieces = category.pieces(unit);
    const match = pieces.find(p => (p.min == null || display >= p.min) && (p.max == null || display < p.max));
    return match ? match.color : pieces[pieces.length - 1].color;
  }

  function positionHmTooltip(event){
    const TW = 230, TH = 130;
    let tx = event.clientX + 14, ty = event.clientY - 10;
    if (tx + TW > window.innerWidth) tx = event.clientX - TW - 14;
    if (ty + TH > window.innerHeight) ty = event.clientY - TH - 10;
    els.tooltip.style.left = tx + 'px';
    els.tooltip.style.top = ty + 'px';
  }
  function hideHmTooltip(){ els.tooltip.style.display = 'none'; }
  function showHmTooltip(d, unit, months, event){
    const dow = getDayOfWeek(currentYear, currentMonth, d.dayNumber);
    let tip = '<b>' + dow + ', ' + months[currentMonth] + ' ' + d.dayNumber + ', ' + currentYear + '</b><hr style="margin:6px 0;opacity:.3;">';
    if (!d.cellData) {
      tip += '<span style="opacity:.7;">No data</span>';
    } else {
      category.metrics.forEach(m => {
        const mv = d.cellData[m.key];
        if (mv) tip += m.icon + ' ' + m.label + ': <b>' + category.fmt(mv.valueC, unit) + '</b><br>';
      });
    }
    els.tooltip.innerHTML = tip;
    els.tooltip.style.display = 'block';
    positionHmTooltip(event);
  }

  function buildHeatmap(){
    if (!dataAvailable) {
      els.emptyState.style.display = '';
      els.chart.style.display = 'none';
      els.legend.innerHTML = '';
      return;
    }
    els.emptyState.style.display = 'none';
    els.chart.style.display = '';
    els.chart.innerHTML = '';
    const unit = currentUnit();
    const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
    const firstDay = new Date(currentYear, currentMonth, 1).getDay();
    const startOffset = firstDay === 0 ? 6 : firstDay - 1;
    const weeks = Math.ceil((daysInMonth + startOffset) / 7);
    const monthKey = currentYear + '-' + currentMonth;
    const monthData = monthDataByMonth[monthKey] || {};
    const dayLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    const weekLabels = [];
    for (let w = 0; w < weeks; w++) weekLabels.push('Week ' + (weeks - w));
    const fontSizes = responsiveFontSizes();
    const activeMetric = category.metrics.find(m => m.key === cellMetric) || category.metrics[0];
    const dp = category.decimals ? category.decimals(unit) : 1;

    const cells = [];
    for (let d = 0; d < 7; d++){
      for (let w = 0; w < weeks; w++){
        const dayNumber = (w * 7) + d - startOffset + 1;
        const yPos = weeks - 1 - w;
        const valid = dayNumber >= 1 && dayNumber <= daysInMonth;
        const cellData = valid ? monthData[dayNumber] : null;
        const metricVal = cellData && cellData[cellMetric];
        const display = metricVal ? Number(category.toDisplay(metricVal.valueC, unit).toFixed(dp)) : null;
        cells.push({d, yPos, dayNumber, valid, display, cellData});
      }
    }

    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    els.currentMonth.textContent = months[currentMonth] + ' ' + currentYear;

    buildLegend(unit);
    const dark = isDarkTheme();
    const textColor = dark ? '#e6eaf9' : '#111';
    const emptyFill = dark ? 'rgba(255,255,255,0.04)' : 'rgba(0,0,0,0.03)';

    const {width} = els.chart.getBoundingClientRect();
    const height = els.chart.clientHeight || 420;
    const margin = {top: category.showMetricSelector ? 46 : 30, right: 8, bottom: 4, left: 46};
    const innerW = Math.max(10, width - margin.left - margin.right);
    const innerH = Math.max(10, height - margin.top - margin.bottom);
    const cellW = innerW / 7;
    const cellH = innerH / weeks;
    const gap = 3;

    const svg = d3.select(els.chart).append('svg').attr('viewBox', `0 0 ${width} ${height}`);

    if (category.showMetricSelector){
      svg.append('text').attr('x', width/2).attr('y', 18).attr('text-anchor','middle')
        .attr('font-size',13).attr('fill', textColor)
        .text(months[currentMonth] + ' ' + currentYear + ' \u2014 Daily ' + activeMetric.label);
    }

    const g = svg.append('g').attr('transform', `translate(${margin.left},${margin.top})`);

    dayLabels.forEach((lbl,i) => {
      g.append('text').attr('x', i*cellW + cellW/2).attr('y', -10).attr('text-anchor','middle')
        .attr('font-size',13).attr('font-weight','bold').attr('fill', textColor).text(lbl);
    });
    weekLabels.forEach((lbl,i) => {
      g.append('text').attr('x', -8).attr('y', i*cellH + cellH/2).attr('text-anchor','end')
        .attr('dominant-baseline','middle').attr('font-size',12).attr('font-weight',500).attr('fill', textColor).text(lbl);
    });

    const cellGroup = g.selectAll('.hm-cell').data(cells).join('g')
      .attr('transform', d => `translate(${d.d*cellW},${d.yPos*cellH})`);

    cellGroup.append('rect')
      .attr('width', Math.max(0, cellW-gap)).attr('height', Math.max(0, cellH-gap)).attr('rx',4).attr('ry',4)
      .attr('fill', d => d.valid ? (d.display != null ? colorForValue(d.display, unit) : emptyFill) : 'transparent')
      .attr('stroke', dark ? 'rgba(255,255,255,0.15)' : 'rgba(0,0,0,0.1)')
      .attr('stroke-width', d => d.valid ? 1.5 : 0)
      .style('cursor', d => d.valid ? 'pointer' : 'default')
      .on('mousemove', function(event, d){
        if (!d.valid) { hideHmTooltip(); return; }
        showHmTooltip(d, unit, months, event);
      })
      .on('mouseleave', hideHmTooltip);

    cellGroup.filter(d => d.valid && d.display != null).append('text')
      .attr('x', (cellW-gap)/2).attr('y', (cellH-gap)/2).attr('text-anchor','middle').attr('dominant-baseline','middle')
      .attr('font-size', fontSizes.value).attr('font-weight','bold').attr('fill','#000')
      .style('text-shadow','0 0 4px rgba(255,255,255,0.9), 0 0 4px rgba(255,255,255,0.9)')
      .text(d => d.display.toFixed(dp));

    cellGroup.filter(d => d.valid).append('text')
      .attr('x', 6).attr('y', 6+fontSizes.day).attr('text-anchor','start')
      .attr('font-size', fontSizes.day).attr('font-weight','bold').attr('fill','#000')
      .style('text-shadow','0 0 4px rgba(255,255,255,0.9), 0 0 4px rgba(255,255,255,0.9)')
      .text(d => d.dayNumber);

    buildStats();
    reportHeight();
  }

  els.prev.addEventListener('click', () => {
    currentMonth--;
    if (currentMonth < 0) { currentMonth = 11; currentYear--; }
    buildHeatmap();
  });
  els.next.addEventListener('click', () => {
    currentMonth++;
    if (currentMonth > 11) { currentMonth = 0; currentYear++; }
    buildHeatmap();
  });
  els.currentMonth.addEventListener('dblclick', () => {
    const now = new Date();
    currentYear = now.getFullYear();
    currentMonth = now.getMonth();
    buildHeatmap();
  });
  document.querySelectorAll('.hm-tab').forEach(btn => {
    btn.addEventListener('click', () => switchCategory(btn.dataset.cat));
  });
  window.addEventListener('resize', () => { if (dataAvailable) buildHeatmap(); });
  window.addEventListener('unitsystemchange', () => { if (dataAvailable) buildHeatmap(); });
  window.addEventListener('storage', e => {
    if (e.key === 'dashboardUnitSystem' && dataAvailable) buildHeatmap();
    if (e.key === 'dashboardThemeMode') { themeMode = e.newValue || 'auto'; applyTheme(); }
  });
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', applyTheme);

  function includeHTML(callback){
    const elements = document.querySelectorAll('[w3-include-html]');
    let pending = elements.length;
    if (pending === 0) { if (callback) callback(); return; }
    elements.forEach(el => {
      const file = el.getAttribute('w3-include-html');
      fetch(file, { cache: 'no-store' })
        .then(res => { if (!res.ok) throw new Error('HTTP ' + res.status); return res.text(); })
        .then(html => { el.innerHTML = html; el.removeAttribute('w3-include-html'); })
        .catch(e => {
          console.warn('heatmaps: include failed for', file, '\u2014', e.message);
          el.innerHTML = '';
        })
        .finally(() => { pending--; if (pending === 0 && callback) callback(); });
    });
  }

  includeHTML(() => {
    if (typeof initSharedHeader === 'function') initSharedHeader();
    updateBrandText();
    const unitSelect = document.getElementById('unitSystem');
    if (unitSelect) {
      unitSelect.value = localStorage.getItem('dashboardUnitSystem') || 'uk';
      unitSelect.addEventListener('change', e => {
        localStorage.setItem('dashboardUnitSystem', e.target.value);
        if (dataAvailable) buildHeatmap();
      });
    }
    const themeBtn = document.getElementById('themeToggle');
    if (themeBtn) {
      themeBtn.addEventListener('click', () => {
        themeMode = THEME_ORDER[(THEME_ORDER.indexOf(themeMode) + 1) % THEME_ORDER.length];
        localStorage.setItem('dashboardThemeMode', themeMode);
        applyTheme();
      });
    }
    syncThemeNavUI();
  });

  applyTheme();
  pollIsDayForTheme();
  setInterval(pollIsDayForTheme, 5 * 60 * 1000);
  setLive('connecting');
  fetchChartsJson()
    .then(() => { applyCategoryData(); buildHeatmap(); })
    .catch(e => {
      console.warn('heatmaps: charts.json unavailable \u2014', e.message);
      applyCategoryData();
      buildHeatmap();
    });
})();