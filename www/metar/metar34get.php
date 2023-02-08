<?php
error_reporting(0); 
$result = date_sun_info(time(), $lat, $lon);
$sunr=date_sunrise(time(), SUNFUNCS_RET_STRING, $lat, $lon, $rise_zenith, $UTC_offset);
$suns=date_sunset(time(), SUNFUNCS_RET_STRING, $lat, $lon, $set_zenith, $UTC_offset);
$suns2 =date('G.i', $result['sunset']);
$sunrs2 =date('G.i', $result['sunrise']);
$now =date('G.i');
 //divumwx wxcheck API aviation metar script May 2018 
$json_string             = file_get_contents("jsondata/me.txt");
$parsed_json             = json_decode($json_string);
$metartime       = $parsed_json->{'data'}[0]->{'observed'};
$metarraw       = $parsed_json->{'data'}[0]->{'raw_text'};
$metarstationid       = $parsed_json->{'data'}[0]->{'icao'};	
$metarstationname       = $parsed_json->{'data'}[0]->{'name'};	
$metarpressurehg       = $parsed_json->{'data'}[0]->{'barometer'}->{'hg'};	
$metarpressuremb       = $parsed_json->{'data'}[0]->{'barometer'}->{'mb'};
$metarconditions         = $parsed_json->{'data'}[0]->{'conditions'}[0]->{'code'};
$metarconditionstext         = $parsed_json->{'data'}[0]->{'conditions'}[0]->{'text'};
$metarclouds          = $parsed_json->{'data'}[0]->{'clouds'}[0]->{'code'};
$metarcloudstext          = $parsed_json->{'data'}[0]->{'clouds'}[0]->{'text'};
$metardewpointc          = $parsed_json->{'data'}[0]->{'dewpoint'}->{'celsius'};
$metardewpointf          = $parsed_json->{'data'}[0]->{'dewpoint'}->{'fahrenheit'};
$metartemperaturec          = $parsed_json->{'data'}[0]->{'temperature'}->{'celsius'};
$metartemperaturef          = $parsed_json->{'data'}[0]->{'temperature'}->{'fahrenheit'};
$metarhumidity          = $parsed_json->{'data'}[0]->{'humidity_percent'};
$metarvisibility        = $parsed_json->{'data'}[0]->{'visibility'}->{'meters'};
$metarwindir          = $parsed_json->{'data'}[0]->{'wind'}->{'degrees'};
$metarwindspeedmph          = $parsed_json->{'data'}[0]->{'wind'}->{'speed_mph'};
$metarwindspeedkmh          = number_format($metarwindspeedmph*1.60934,0);//kmh
$metarwindspeedkts          = $parsed_json->{'data'}[0]->{'wind'}->{'speed_kts'};
$metarraininches          = $parsed_json->{'data'}[0]->{'rain_in'};
$metarrainmm          = number_format($metarraininches*25.4,2) ;
$metarvisibility=str_replace(',', '', $metarvisibility);
$metarvismiles        = number_format($metarvisibility*0.000621371,1) ;
$metarviskm        = number_format($metarvisibility*0.00099999969062399994,0) ;
// start the divumwx icon output and descriptions
if($metarconditions =='-SHRA'){
if ($now >$suns2 ){$sky_icon='rain.svg';} 
else if ($now <$sunrs2 ){$sky_icon='rain.svg';} 
else $sky_icon='rain.svg'; 
$sky_desc='Light Rain <br>Showers';
}
//rain 
else if($metarconditions =='SHRA'){
if ($now >$suns2 ){$sky_icon='rain.svg';} 
else if ($now <$sunrs2 ){$sky_icon='rain.svg';} 
else $sky_icon='rain.svg'; 
$sky_desc='Light Rain <br>Showers';
}
//rain heavy
else if($metarconditions =='+SHRA'){
if ($now >$suns2 ){$sky_icon='rain.svg';} 
else if ($now <$sunrs2 ){$sky_icon='rain.svg';} 
else $sky_icon='rain.svg'; 
$sky_desc='Heavy Rain <br>Showers';
}
//rain light
else if($metarconditions=='-RA'){
if ($now >$suns2 ){$sky_icon='rain.svg';} 
else if ($now <$sunrs2 ){$sky_icon='rain.svg';} 
else $sky_icon='rain.svg'; 
$sky_desc='Light Rain <br>Showers';
}
//rain moderate
else if($metarconditions=='+RA'){
if ($now >$suns2 ){$sky_icon='rain.svg';} 
else if ($now <$sunrs2 ){$sky_icon='rain.svg';} 
else $sky_icon='rain.svg'; 
$sky_desc='Moderate Rain <br>Showers';
}
//rain
else if($metarconditions=='RA'){
if ($now >$suns2 ){$sky_icon='rain.svg';} 
else if ($now <$sunrs2 ){$sky_icon='rain.svg';} 
else $sky_icon='rain.svg'; 
$sky_desc='Light Rain <br>Showers';
}
//rain squalls
else if($metarconditions=='SQ'){
if ($now >$suns2 ){$sky_icon='rain.svg';} 
else if ($now <$sunrs2 ){$sky_icon='rain.svg';} 
else $sky_icon='rain.svg'; 
$sky_desc='Rain Squall<br>Showers';
}
//snow light
else if($metarconditions=='-SN'){
if ($now >$suns2 ){$sky_icon='snow.svg';} 
else if ($now <$sunrs2 ){$sky_icon='snow.svg';} 
else $sky_icon='snow.svg'; 
$sky_desc='Light Snow <br>Showers';
}
//snow moderate
else if($metarconditions=='+SN'){
if ($now >$suns2 ){$sky_icon='snow.svg';} 
else if ($now <$sunrs2 ){$sky_icon='snow.svg';} 
else $sky_icon='snow.svg'; 
$sky_desc='Moderate Snow <br>Showers';
}
//snow
else if($metarconditions=='SN'){
if ($now >$suns2 ){$sky_icon='snow.svg';} 
else if ($now <$sunrs2 ){$sky_icon='snow.svg';} 
else $sky_icon='snow.svg'; 
$sky_desc='Snow Showers <br>';
}
//snow grains
else if($metarconditions=='SG'){
if ($now >$suns2 ){$sky_icon='snow.svg';} 
else if ($now <$sunrs2 ){$sky_icon='snow.svg';} 
else $sky_icon='snow.svg'; 
$sky_desc='Snow Grains <br>';
}
//snow grains
else if($metarconditions=='SNINCR'){
if ($now >$suns2 ){$sky_icon='snow.svg';} 
else if ($now <$sunrs2 ){$sky_icon='snow.svg';} 
else $sky_icon='snow.svg'; 
$sky_desc='Snow Showers <br>';
}
//sleet
else if($metarconditions=='IP'){
if ($now >$suns2 ){$sky_icon='sleet.svg';} 
else if ($now <$sunrs2 ){$sky_icon='sleet.svg';} 
else $sky_icon='sleet.svg'; 
$sky_desc='Sleet Showers';
}
//Haze
else if($metarconditions=='HZ'){
if ($now >$suns2 ){$sky_icon='nt_haze.svg';} 
else if ($now <$sunrs2 ){$sky_icon='nt_haze.svg';} 
else $sky_icon='haze.svg'; 
$sky_desc='Hazy <br>Conditions';
}
//Batches Fog
else if($metarconditions=='BCFG'){
if ($now >$suns2 ){$sky_icon='nt_fog.svg';} 
else if ($now <$sunrs2 ){$sky_icon='nt_fog.svg';} 
else $sky_icon='fog.svg'; 
$sky_desc='Foggy <br>Conditions';
}
//Fog
else if($metarconditions=='FG'){
if ($now >$suns2 ){$sky_icon='nt_fog.svg';} 
else if ($now <$sunrs2 ){$sky_icon='nt_fog.svg';} 
else $sky_icon='fog.svg'; 
$sky_desc='Foggy <br>Conditions';
}
//Fog-NIGHT
else if($metarconditions=='NFG'){
if ($now >$suns2 ){$sky_icon='nt_fog.svg';} 
else if ($now <$sunrs2 ){$sky_icon='nt_fog.svg';} 
else $sky_icon='fog.svg'; 
$sky_desc='Foggy <br>Conditions';
}
//Mist-Night
else if($metarconditions=='BR'){
if ($now >$suns2 ){$sky_icon='nt_fog.svg';} 
else if ($now <$sunrs2 ){$sky_icon='nt_fog.svg';} 
else $sky_icon='fog.svg'; 
$sky_desc='Misty <br>Conditions';
}
//Mist
else if($metarconditions=='NBR'){
if ($now >$suns2 ){$sky_icon='nt_fog.svg';} 
else if ($now <$sunrs2 ){$sky_icon='nt_fog.svg';} 
else $sky_icon='fog.svg'; 
$sky_desc='Misty <br>Conditions';
}
//Hail
else if($metarconditions=='GR'){
if ($now >$suns2 ){$sky_icon='hail.svg';} 
else if ($now <$sunrs2 ){$sky_icon='hail.svg';} 
else $sky_icon='hail.svg'; 
$sky_desc='Hail and Rain <br>Conditions';
}
//Hail GS
else if($metarconditions=='GS'){
if ($now >$suns2 ){$sky_icon='hail.svg';} 
else if ($now <$sunrs2 ){$sky_icon='hail.svg';} 
else $sky_icon='hail.svg'; 
$sky_desc='Hail <br>Conditions';
}
//ICE CYSTALS
else if($metarconditions=='IC'){
if ($now >$suns2 ){$sky_icon='hail.svg';} 
else if ($now <$sunrs2 ){$sky_icon='hail.svg';} 
else $sky_icon='hail.svg'; 
$sky_desc='Ice Crystals';
}
//ICE PELLETS
else if($metarconditions=='PL'){
if ($now >$suns2 ){$sky_icon='hail.svg';} 
else if ($now <$sunrs2 ){$sky_icon='hail.svg';} 
else $sky_icon='hail.svg'; 
$sky_desc='Ice Pellets <br>';
}
//Thunderstorms
else if($metarconditions=='TS'){
if ($now >$suns2 ){$sky_icon='tstorm.svg';} 
else if ($now <$sunrs2 ){$sky_icon='tstorm.svg';} 
else $sky_icon='tstorm.svg'; 
$sky_desc='Thunderstorm <br>Conditions';
}
//Thunderstorms
else if($metarconditions=='-TS'){
if ($now >$suns2 ){$sky_icon='tstorm.svg';} 
else if ($now <$sunrs2 ){$sky_icon='tstorm.svg';} 
else $sky_icon='tstorm.svg'; 
$sky_desc='Thunderstorm <br>Conditions';
}
//Thunderstorms
else if($metarconditions=='+TS'){
if ($now >$suns2 ){$sky_icon='tstorm.svg';} 
else if ($now <$sunrs2 ){$sky_icon='tstorm.svg';} 
else $sky_icon='tstorm.svg'; 
$sky_desc='Heavy <br>Thunderstorms';
}
//Thunderstorms
else if($metarconditions=='TSRA'){
if ($now >$suns2 ){$sky_icon='tstorm.svg';} 
else if ($now <$sunrs2 ){$sky_icon='tstorm.svg';} 
else $sky_icon='tstorm.svg'; 
$sky_desc='Thunderstorm <br>Conditions';
}
//Scattered Thunderstorms
else if($metarconditions=='SCTTSRA'){
if ($now >$suns2 ){$sky_icon='tstorm.svg';} 
else if ($now <$sunrs2 ){$sky_icon='tstorm.svg';} 
else $sky_icon='tstorm.svg'; 
$sky_desc='Scattered <br>Thunderstorms';
}
//Scattered Thunderstorms
else if($metarconditions=='NTSRA'){
if ($now >$suns2 ){$sky_icon='tstorm.svg';} 
else if ($now <$sunrs2 ){$sky_icon='tstorm.svg';} 
else $sky_icon='tstorm.svg'; 
$sky_desc='Scattered <br>Thunderstorms';
}
//Dust
else if($metarconditions=='DS'){
if ($now >$suns2 ){$sky_icon='dust.svg';} 
else if ($now <$sunrs2 ){$sky_icon='dust.svg';} 
else $sky_icon='dust.svg'; 
$sky_desc='Dust Storm <br>Conditions';
}
//Widespread Dust
else if($metarconditions=='DU'){
if ($now >$suns2 ){$sky_icon='dust.svg';} 
else if ($now <$sunrs2 ){$sky_icon='dust.svg';} 
else $sky_icon='dust.svg'; 
$sky_desc='Widespread Dust <br>Conditions';
}
//Dust-Sand Whirls
else if($metarconditions=='PO'){
if ($now >$suns2 ){$sky_icon='dust.svg';} 
else if ($now <$sunrs2 ){$sky_icon='dust.svg';} 
else $sky_icon='dust.svg'; 
$sky_desc='Dust-Sand Whirls <br>Conditions';
}
//Sand
else if($metarconditions=='SA'){
if ($now >$suns2 ){$sky_icon='dust.svg';} 
else if ($now <$sunrs2 ){$sky_icon='dust.svg';} 
else $sky_icon='dust.svg'; 
$sky_desc='Dust-Sand <br>Conditions';
}
//Sandstorm
else if($metarconditions=='SS'){
if ($now >$suns2 ){$sky_icon='dust.svg';} 
else if ($now <$sunrs2 ){$sky_icon='dust.svg';} 
else $sky_icon='dust.svg'; 
$sky_desc='Sandstorm <br>Conditions';
}
//Volcanic Ash
else if($metarconditions=='VA'){
if ($now >$suns2 ){$sky_icon='volcanoe.svg';} 
else if ($now <$sunrs2 ){$sky_icon='volcanoe.svg';} 
else $sky_icon='volcanoe.svg'; 
$sky_desc='Volcanic Ash <br>Conditions';
}

//+FC
else if($metarconditions=='+FC'){
if ($now >$suns2 ){$sky_icon='nsvrtsa.svg';} 
else if ($now <$sunrs2 ){$sky_icon='nsvrtsa.svg';} 
else $sky_icon='nsvrtsat.svg'; 
$sky_desc='Tornado <br> Water Sprout';
}
//2nd part clouds
//clear
else if ($metarclouds=='SKC') {
if ($now >$suns2 ){$sky_icon='nt_clear.svg';} 
else if ($now <$sunrs2 ){$sky_icon='nt_clear.svg';} 
else $sky_icon='clear.svg'; 
$sky_desc='Clear <br>Conditions';
}
//clear
else if($metarclouds=='CLR'){
if ($now >$suns2 ){$sky_icon='nt_clear.svg';} 
else if ($now <$sunrs2 ){$sky_icon='nt_clear.svg';} 
else $sky_icon='clear.svg'; 
$sky_desc='Clear <br>Conditions';
}
//clear
else if($metarclouds=='CAVOK'){
if ($now >$suns2 ){$sky_icon='nt_clear.svg';} 
else if ($now <$sunrs2 ){$sky_icon='nt_clear.svg';} 
else $sky_icon='clear.svg'; 
$sky_desc='Clear <br>Conditions';
}
//few
else if($metarclouds=='FEW'){
if ($now >$suns2 ){$sky_icon='partlycloudy.svg';} 
else if ($now <$sunrs2 ){$sky_icon='partlycloudy.svg';} 
else $sky_icon='partlysunny.svg'; 
$sky_desc='Partly Cloudy <br>Conditions';
}
//scattered clouds
else if($metarclouds=='SCT'){
if ($now >$suns2 ){$sky_icon='nt_scatteredclouds.svg';} 
else if ($now <$sunrs2 ){$sky_icon='nt_scatteredclouds.svg';} 
else $sky_icon='scatteredclouds.svg'; 	
$sky_desc='Mostly Scattered <br>Clouds';
}
//mostly cloudy
else if($metarclouds=='BKN'){		
if ($now >$suns2 ){$sky_icon='nt_mostlycloudy.svg';} 
else if ($now <$sunrs2 ){$sky_icon='nt_mostlycloudy.svg';} 
else $sky_icon='mostlycloudy.svg'; 	
$sky_desc='Mostly Cloudy <br>Conditions';
}
//overcast
else if($metarclouds=='OVC'){
if ($now >$suns2 ){$sky_icon='nt_overcast.svg';} 
else if ($now <$sunrs2 ){$sky_icon='nt_overcast.svg';} 
else $sky_icon='overcast.svg'; 
$sky_desc='Overcast <br>Conditions';
}
//overcast
else if($metarclouds=='OVX'){
if ($now >$suns2 ){$sky_icon='nt_overcast.svg';} 
else if ($now <$sunrs2 ){$sky_icon='nt_overcast.svg';} 
else $sky_icon='overcast.svg'; 
$sky_desc='Overcast Conditions';
}
//offline
else{
	$sky_icon='offline.svg';
	$sky_desc='Data Offline';
};
//end divumwx metar aviation script API	 

?>
