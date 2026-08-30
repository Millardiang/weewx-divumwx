/*
##############################################################################################
# units.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

const SYSTEMS = {
  uk:       {temp:'C', wind:'mph', pressure:'hpa',  rain:'mm', dist:'km', vis:'mi', conc:'µg/m³'},
  us:       {temp:'F', wind:'mph', pressure:'inhg', rain:'in', dist:'mi', vis:'mi', conc:'µg/m³'},
  metric:   {temp:'C', wind:'kmh', pressure:'hpa',  rain:'mm', dist:'km', vis:'km', conc:'µg/m³'},
  scandi:   {temp:'C', wind:'ms',  pressure:'hpa',  rain:'mm', dist:'km', vis:'km', conc:'µg/m³'},
  canada:   {temp:'C', wind:'kmh', pressure:'kpa',  rain:'mm', dist:'km', vis:'km', conc:'µg/m³'},
  aviation: {temp:'C', wind:'kt',  pressure:'mbar', rain:'mm', dist:'nm', vis:'nm', conc:'µg/m³'},
  beaufort: {temp:'C', wind:'bf',  pressure:'hpa',  rain:'mm', dist:'km', vis:'km', conc:'µg/m³'},
};

const ms2mph  = ms  => ms*2.23694;
const ms2kmh  = ms  => ms*3.6;
const ms2kt   = ms  => ms*1.94384;
const kmh2ms  = kmh => kmh/3.6;
const mm2in   = mm  => mm*0.0393701;
const hpa2inhg = hpa => hpa*0.0295301;
const hpa2mmhg = hpa => hpa*0.750062;
const hpa2kpa  = hpa => hpa/10;
const km2mi   = km  => km*0.621371;
const km2nm   = km  => km*0.539957;
function C2F(c){ return c*9/5+32; }

function beaufort(ms){
  const scale=[
    [0.5,0,"Calm"],[1.5,1,"Light air"],[3.3,2,"Light breeze"],[5.5,3,"Gentle breeze"],
    [7.9,4,"Moderate breeze"],[10.7,5,"Fresh breeze"],[13.8,6,"Strong breeze"],
    [17.1,7,"Near gale"],[20.7,8,"Gale"],[24.4,9,"Strong gale"],[28.4,10,"Storm"],
    [32.6,11,"Violent storm"],[999,12,"Hurricane"]
  ];
  for(const [max,f,label] of scale){ if(ms<max) return {force:f,label}; }
  return {force:12,label:"Hurricane"};
}