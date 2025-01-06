<?php
##############################################################################################
#        ________   __  ___      ___  ____  ____  ___      ___    __   __  ___  ___  ___     #
#       |"      "\ |" \|"  \    /"  |("  _||_ " ||"  \    /"  |  |"  |/  \|  "||"  \/"  |    #
#       (.  ___  :)||  |\   \  //  / |   (  ) : | \   \  //   |  |'  /    \:  | \   \  /     #
#       |: \   ) |||:  | \\  \/. ./  (:  |  | . ) /\\  \/.    |  |: /'        |  \\  \/      #
#       (| (___\ |||.  |  \.    //    \\ \__/ // |: \.        |   \//  /\'    |  /\.  \      #
#       |:       :)/\  |\  \\   /     /\\ __ //\ |.  \    /:  |   /   /  \\   | /  \   \     #
#       (________/(__\_|_)  \__/     (__________)|___|\__/|___|  |___/    \___||___/\___|    #
#                                                                                            #
#     Copyright (C) 2023 Ian Millard, Steven Sheeley, Sean Balfour. All rights reserved      #
#      Distributed under terms of the GPLv3.  See the file LICENSE.txt for your rights.      #
#    Issues for weewx-divumwx skin template are only addressed via the issues register at    #
#                    https://github.com/Millardiang/weewx-divumwx/issues                     #
##############################################################################################
include('dvmCombinedData.php');
error_reporting(0);
$result = date_sun_info(time(), $lat, $lon);
$suns2 = date('G.i', $result['sunset']);
$sunrs2 = date('G.i', $result['sunrise']);
$now = date('G.i');
 // divumwx wxcheck API
$json_string = file_get_contents("jsondata/me.txt");
$parsed_json = json_decode($json_string, true);
if(!empty($parsed_json["data"][0])) {
$metartime = $parsed_json["data"][0]["observed"];
$metarraw = $parsed_json["data"][0]["raw_text"];
$metarstationid = $parsed_json["data"][0]["icao"];
$metarstationname = $parsed_json["data"][0]["station"]["name"];
$metarlat = $parsed_json["data"][0]["station"]["geometry"]["coordinates"][1];
$metarlon = $parsed_json["data"][0]["station"]["geometry"]["coordinates"][0];
$airport1dist = round(distance($lat, $lon, $metarlat, $metarlon));
$metarpressurehg = $parsed_json["data"][0]["barometer"]["hg"];  
$metarpressuremb = $parsed_json["data"][0]["barometer"]["mb"];
$metarclouds = $parsed_json["data"][0]["clouds"][0]["code"];
$metarcloudstext = $parsed_json["data"][0]["clouds"][0]["text"];
$metardewpointc = $parsed_json["data"][0]["dewpoint"]["celsius"];
$metardewpointf = $parsed_json["data"][0]["dewpoint"]["fahrenheit"];
$metartemperaturec = $parsed_json["data"][0]["temperature"]["celsius"];
$metartemperaturef = $parsed_json["data"][0]["temperature"]["fahrenheit"];
$metarhumidity = $parsed_json["data"][0]["humidity"]["percent"];
$metarwindir = $parsed_json["data"][0]["wind"]["degrees"];
$metarwindspeedmph = $parsed_json["data"][0]["wind"]["speed_mph"];
$metarwindspeedkmh = $parsed_json["data"][0]["wind"]["speed_kph"];
$metarwindspeedkts = $parsed_json["data"][0]["wind"]["speed_kts"];
$metarwindspeedms = $parsed_json["data"][0]["wind"]["speed_mps"];
$metarvisibility = str_replace(',', '', $metarvisibility);
$metarvisibility = $parsed_json["data"][0]["visibility"]["meters"];
$metarvismiles = $parsed_json["data"][0]["visibility"]["miles"];
$metarviskm = $metarvisibility;
} else {;}
// start the divumwx icon output and descriptions
if($metarclouds == '-SHRA'){
if ($now >$suns2 ){$sky_icon = 'rain.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'rain.svg';} 
else $sky_icon = 'rain.svg'; 
$sky_desc = 'Light Rain <br>Showers';
}
//rain 
else if($metarclouds == 'SHRA'){
if ($now >$suns2 ){$sky_icon =' rain.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'rain.svg';} 
else $sky_icon = 'rain.svg'; 
$sky_desc = 'Light Rain <br>Showers';
}
//rain heavy
else if($metarclouds == '+SHRA'){
if ($now >$suns2 ){$sky_icon = 'extreme-night-rain.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'extreme-night-rain.svg';} 
else $sky_icon = 'extreme-rain.svg'; 
$sky_desc = 'Heavy Rain <br>Showers';
}
//rain light
else if($metarclouds == '-RA'){
if ($now >$suns2 ){$sky_icon = 'rain.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'rain.svg';} 
else $sky_icon = 'rain.svg'; 
$sky_desc = 'Light Rain <br>Showers';
}
//rain moderate
else if($metarclouds == '+RA'){
if ($now >$suns2 ){$sky_icon = 'overcast-rain.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'overcast-rain.svg';} 
else $sky_icon = 'overcast-rain.svg'; 
$sky_desc = 'Moderate Rain <br>Showers';
}
//rain
else if($metarclouds == 'RA'){
if ($now >$suns2 ){$sky_icon = 'overcast-rain.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'overcast-rain.svg';} 
else $sky_icon = 'overcast-rain.svg'; 
$sky_desc = 'Light Rain <br>Showers';
}
//rain squalls
else if($metarclouds == 'SQ'){
if ($now >$suns2 ){$sky_icon = 'extreme-rain.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'extreme-rain.svg';} 
else $sky_icon = 'extreme-rain.svg'; 
$sky_desc = 'Rain Squall<br>Showers';
}
//snow light
else if($metarclouds == '-SN'){
if ($now >$suns2 ){$sky_icon = 'snow.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'snow.svg';} 
else $sky_icon = 'snow.svg'; 
$sky_desc = 'Light Snow <br>Showers';
}
//snow moderate
else if($metarclouds == '+SN'){
if ($now >$suns2 ){$sky_icon = 'snow.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'snow.svg';} 
else $sky_icon = 'snow.svg'; 
$sky_desc = 'Moderate Snow <br>Showers';
}
//snow
else if($metarclouds == 'SN'){
if ($now >$suns2 ){$sky_icon = 'snow.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'snow.svg';} 
else $sky_icon = 'snow.svg'; 
$sky_desc = 'Snow Showers <br>';
}
//snow grains
else if($metarclouds == 'SG'){
if ($now >$suns2 ){$sky_icon = 'snow.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'snow.svg';} 
else $sky_icon = 'snow.svg'; 
$sky_desc = 'Snow Grains <br>';
}
//snow grains
else if($metarclouds == 'SNINCR'){
if ($now >$suns2 ){$sky_icon = 'snow.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'snow.svg';} 
else $sky_icon = 'snow.svg'; 
$sky_desc = 'Snow Showers <br>';
}
//sleet
else if($metarclouds == 'IP'){
if ($now >$suns2 ){$sky_icon = 'sleet.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'sleet.svg';} 
else $sky_icon = 'sleet.svg'; 
$sky_desc = 'Sleet Showers';
}
//Haze
else if($metarclouds == 'HZ'){
if ($now >$suns2 ){$sky_icon = 'haze-night.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'haze-night.svg';} 
else $sky_icon = 'haze-day.svg'; 
$sky_desc = 'Hazy <br>Conditions';
}
//Batches Fog
else if($metarclouds == 'BCFG'){
if ($now >$suns2 ){$sky_icon = 'fog-day.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'fog-night.svg';} 
else $sky_icon = 'fog-day.svg'; 
$sky_desc = 'Foggy <br>Conditions';
}
//Fog
else if($metarclouds == 'FG'){
if ($now >$suns2 ){$sky_icon = 'fog-day.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'fog-night.svg';} 
else $sky_icon = 'fog-day.svg'; 
$sky_desc = 'Foggy <br>Conditions';
}
//Fog-NIGHT
else if($metarclouds == 'NFG'){
if ($now >$suns2 ){$sky_icon = 'fog-night.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'fog-night.svg';} 
else $sky_icon = 'fog-night.svg'; 
$sky_desc = 'Foggy <br>Conditions';
}
//Mist-Night
else if($metarclouds == 'BR'){
if ($now >$suns2 ){$sky_icon = 'mist.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'mist.svg';} 
else $sky_icon = 'mist.svg'; 
$sky_desc = 'Misty <br>Conditions';
}
//Mist
else if($metarclouds == 'NBR'){
if ($now >$suns2 ){$sky_icon = 'mist.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'mist.svg';} 
else $sky_icon = 'mist.svg'; 
$sky_desc = 'Misty <br>Conditions';
}
//Hail
else if($metarclouds == 'GR'){
if ($now >$suns2 ){$sky_icon = 'hail.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'hail.svg';} 
else $sky_icon = 'hail.svg'; 
$sky_desc = 'Hail and Rain <br>Conditions';
}
//Hail GS
else if($metarclouds == 'GS'){
if ($now >$suns2 ){$sky_icon = 'hail.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'hail.svg';} 
else $sky_icon = 'hail.svg'; 
$sky_desc = 'Hail <br>Conditions';
}
//ICE CYSTALS
else if($metarclouds == 'IC'){
if ($now >$suns2 ){$sky_icon = 'extreme-day-hail.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'extreme-day-hail.svg';} 
else $sky_icon = 'extreme-day-hail.svg'; 
$sky_desc = 'Ice Crystals';
}
//ICE PELLETS
else if($metarclouds == 'PL'){
if ($now >$suns2 ){$sky_icon = 'extreme-day-hail.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'extreme-day-hail.svg';} 
else $sky_icon = 'extreme-day-hail.svg'; 
$sky_desc = 'Ice Pellets <br>';
}
//Thunderstorms
else if($metarclouds == 'TS'){
if ($now >$suns2 ){$sky_icon = 'thunderstorms.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'thunderstorms.svg';} 
else $sky_icon = 'thunderstorms.svg'; 
$sky_desc = 'Thunderstorm <br>Conditions';
}
//Thunderstorms
else if($metarclouds == '-TS'){
if ($now >$suns2 ){$sky_icon = 'thunderstorms.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'thunderstorms.svg';} 
else $sky_icon = 'thunderstorms.svg'; 
$sky_desc = 'Thunderstorm <br>Conditions';
}
//Thunderstorms
else if($metarclouds == '+TS'){
if ($now >$suns2 ){$sky_icon = 'thunderstorms-day-extreme';} 
else if ($now <$sunrs2 ){$sky_icon = 'thunderstorms-day-extreme';} 
else $sky_icon = 'thunderstorms-day-extreme.svg'; 
$sky_desc = 'Heavy <br>Thunderstorms';
}
//Thunderstorms
else if($metarclouds == 'TSRA'){
if ($now >$suns2 ){$sky_icon = 'thunderstorms.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'thunderstorms.svg';} 
else $sky_icon = 'thunderstorms.svg'; 
$sky_desc = 'Thunderstorm <br>Conditions';
}
//Scattered Thunderstorms
else if($metarclouds == 'SCTTSRA'){
if ($now >$suns2 ){$sky_icon = 'thunderstorms-day.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'thunderstorms-day.svg';} 
else $sky_icon = 'thunderstorms-day.svg'; 
$sky_desc = 'Scattered <br>Thunderstorms';
}
//Scattered Thunderstorms
else if($metarclouds == 'NTSRA'){
if ($now >$suns2 ){$sky_icon = 'thunderstorms-day.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'thunderstorms-day.svg';} 
else $sky_icon = 'thunderstorms-day.svg'; 
$sky_desc = 'Scattered <br>Thunderstorms';
}
//Dust
else if($metarclouds == 'DS'){
if ($now >$suns2 ){$sky_icon = 'dust-night.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'dust-night.svg';} 
else $sky_icon = 'dust-day.svg'; 
$sky_desc = 'Dust Storm <br>Conditions';
}
//Widespread Dust
else if($metarclouds == 'DU'){
if ($now >$suns2 ){$sky_icon = 'dust-night.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'dust-night.svg';} 
else $sky_icon = 'dust-day.svg'; 
$sky_desc = 'Widespread Dust <br>Conditions';
}
//Dust-Sand Whirls
else if($metarclouds == 'PO'){
if ($now >$suns2 ){$sky_icon = 'dust-night.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'dust-night.svg';} 
else $sky_icon = 'dust-day.svg'; 
$sky_desc = 'Dust-Sand Whirls <br>Conditions';
}
//Sand
else if($metarclouds == 'SA'){
if ($now >$suns2 ){$sky_icon = 'dust-night.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'dust-night.svg';} 
else $sky_icon = 'dust-day.svg'; 
$sky_desc = 'Dust-Sand <br>Conditions';
}
//Sandstorm
else if($metarclouds == 'SS'){
if ($now >$suns2 ){$sky_icon = 'dust-night.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'dust-night.svg';} 
else $sky_icon = 'dust-day.svg'; 
$sky_desc = 'Sandstorm <br>Conditions';
}
//Volcanic Ash
else if($metarclouds == 'VA'){
if ($now >$suns2 ){$sky_icon = 'dust-night.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'dust-night.svg';} 
else $sky_icon = 'dust-day.svg'; 
$sky_desc = 'Volcanic Ash <br>Conditions';
}
//+FC
else if($metarclouds == '+FC'){
if ($now >$suns2 ){$sky_icon = 'tornado.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'tornado.svg';} 
else $sky_icon = 'tornado.svg'; 
$sky_desc = 'Tornado <br> Water Sprout';
}
//2nd part clouds
//clear
else if ($metarclouds == 'SKC') {
if ($now >$suns2 ){$sky_icon = 'clear-night.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'clear-night.svg';} 
else $sky_icon = 'clear-day.svg'; 
$sky_desc = 'Clear <br>Conditions';
}
//clear
else if($metarclouds == 'CLR'){
if ($now >$suns2 ){$sky_icon = 'clear-night.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'clear-night.svg';} 
else $sky_icon = 'clear-day.svg'; 
$sky_desc = 'Clear <br>Conditions';
}
//clear
else if($metarclouds == 'CAVOK'){
if ($now >$suns2 ){$sky_icon = 'clear-night.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'clear-night.svg';} 
else $sky_icon = 'clear-day.svg'; 
$sky_desc = 'Clear <br>Conditions';
}
//few
else if($metarclouds == 'FEW'){
if ($now >$suns2 ){$sky_icon = 'partly-cloudy-night.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'partly-cloudy-night.svg';} 
else $sky_icon = 'partly-cloudy-day.svg'; 
$sky_desc = 'Partly Cloudy <br>Conditions';
}
//scattered clouds
else if($metarclouds == 'SCT'){
if ($now >$suns2 ){$sky_icon = 'partly-cloudy-night.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'partly-cloudy-night.svg';} 
else $sky_icon = 'partly-cloudy-day.svg';   
$sky_desc = 'Mostly Scattered <br>Clouds';
}
//mostly cloudy
else if($metarclouds == 'BKN'){   
if ($now >$suns2 ){$sky_icon = 'overcast-night.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'overcast-night.svg';} 
else $sky_icon = 'overcast-day.svg';   
$sky_desc = 'Mostly Cloudy <br>Conditions';
}
//overcast
else if($metarclouds == 'OVC'){
if ($now >$suns2 ){$sky_icon = 'overcast-night.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'overcast-night.svg';} 
else $sky_icon = 'overcast-day.svg'; 
$sky_desc = 'Overcast <br>Conditions';
}
//overcast
else if($metarclouds == 'OVX'){
if ($now >$suns2 ){$sky_icon = 'overcast-night.svg';} 
else if ($now <$sunrs2 ){$sky_icon = 'overcast-night.svg';} 
else $sky_icon = 'overcast-day.svg'; 
$sky_desc = 'Overcast Conditions';
}
//offline
else{
  $sky_icon = 'not-available.svg';
  $sky_desc = 'Data Offline';
};
?>
<?php
if($theme === "light"){$background = "white"; $text = "black";}
else if($theme === "dark"){$background = "rgba(33, 34, 39, .8)"; $text = "white";}
?>
<!DOCTYPE html>
<html lang = "en">
<head>
<meta charset = "utf-8">
<title>divumwx Nearby Metar</title>
<meta name = "viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<style>
@font-face{font-family:weathertext2;src:url(css/fonts/verbatim-regular.woff) format("woff"),url(fonts/verbatim-regular.woff2) format("woff2"),url(fonts/verbatim-regular.ttf) format("truetype")}
html,body
  {
    font-size:13px;
    font-family: "weathertext2", Helvetica, Arial, sans-serif;
    -webkit-font-smoothing: antialiased;  
    -moz-osx-font-smoothing: grayscale;
    background-color:<?php echo $background; ?>;
  }
  /* unvisited link */
a:link {
  color: <?php echo $text; ?>;
}

/* visited link */
a:visited {
  color: <?php echo $text; ?>;
}

/* mouse over link */
a:hover {
  color: <?php echo $text; ?>;
}

/* selected link */
a:active {
  color: <?php echo $text; ?>;
}
.grid { 
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 2fr));
  grid-gap: 2px;
  align-items: stretch;
  color:<?php echo $text; ?>;  
  }
.grid > article {
  border: 1px solid rgba(245, 247, 252,.02);
  box-shadow: 2px 2px 6px 0px  rgba(0,0,0,0.6);
  padding:5px;
  font-size:0.8em;
  -webkit-border-radius:4px;
  border-radius:4px;
  background:0;-webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;
}
.grid > article img {
  max-width: 100%;
}
.divumwxdarkbrowser {
  position: relative;
  width: 97%;
  height: 30px;
  margin: auto;
  margin-top: -5px;
  margin-left: 0px;
  border-top-left-radius: 5px;
  border-top-right-radius: 5px;
  padding-top: 10px;
  color: <?php echo $text;?>;
}

.divumwxdarkbrowser[url]:after {
  content: attr(url);
  font-size: 14px;
  text-align: center;
  position: absolute;
  left: 0;
  right: 0;
  top: 0;
  padding: 4px 15px;
  margin: 11px 10px 0 auto;
  font-family: arial;
  height: 20px;
} 
blue{color:#01a4b4}
orange{color:#009bb4}
orange1{position:relative;color:#009bb4;margin:0 auto;text-align:center;margin-left:5%;font-size:1.1rem}
green{color:#aaa}
red{color:#f37867}
red6{color:#d65b4a}
value{color:#fff}
yellow{color:#CC0}
purple{color:#916392}

.text1{font-family:weathertext2,Arial;font-size:20px;margin-left:3px;}
.windvalue1{font-family:weathertext2,Arial;font-size:20px;margin-left:3px;}
.windseparator{color:rgba(57,61,64,1)}
.text1,.windvalue1{color:#aaa}
text1{z-index:10;text-align:center;margin:5px 0 auto}

.metartempcontainer1{left:70px;top:0}
.metartempcontainer2{left:10px;top:90px;position:absolute}
.metartempcontainer3{left:85px;top:125px;position:absolute}
.metartempcontainer4{left:85px;top:76px;position:absolute}
.metartempcontainer5{left:85px;top:142px;position:absolute}
.metarwindcontainer1{margin-top:0;margin-left:5px;position:relative}
/*kts*/
.metarwindcontainer2{margin-top:10px;margin-left:0px;position:relative}
/*mph*/
.metarwindcontainer3{margin-top:-95px;margin-left:85px;position:relative}
/*ms*/
.metarwindcontainer4{margin-top:10px;margin-left:85px;position:relative}
.metarwindcontainer5{margin-top:-50px;margin-left:5px;position:relative}
.metartemptoday0,
.metartemptoday5,
.metartemptoday10,
.metartemptoday20,
.metartemptoday25,
.metartemptoday30{font-family:weathertext2,Arial,Helvetica,system;width:4.5rem;height:2.5rem;-webkit-border-radius:3px;-moz-border-radius:3px;border-radius:3px;display:flex}
.metartemptoday0,
.metartemptoday5,
.metartemptoday10,
.metartemptoday15,
.metartemptoday20,
.metartemptoday25,
.metartemptoday30{font-size:1.1rem;padding-top:0;color:#fff;border-bottom:12px solid rgba(56,56,60,1);align-items:center;justify-content:center;border-radius:3px;margin-bottom:21px;}
.metartemptoday0{background:rgba(68, 166, 181, 1.000)}
.metartemptoday5{background:rgba(144, 177, 42, 1.000)}
.metartemptoday10{background:rgba(230, 161, 65, 1.000)}
.metartemptoday20{background:rgba(255, 124, 57, 1.000)}
.metartemptoday25{background:rgba(255, 124, 57, 0.7)}
.metartemptoday30{background:rgba(211, 93, 78, 1.000)}
.metardewcontainer1{left:70px;margin-top:10px}

.metardewtoday0,
.metardewtoday5,
.metardewtoday10,
.metardewtoday20,
.metardewtoday25,
.metardewtoday30{font-family:weathertext2,Arial,Helvetica,system;width:4.5rem;height:2.5rem;-webkit-border-radius:3px;-moz-border-radius:3px;border-radius:3px;display:flex}
.metardewtoday0,
.metardewtoday5,
.metardewtoday10,
.metardewtoday15,
.metardewtoday20,
.metardewtoday25,
.metardewtoday30{font-size:1.1rem;padding-top:0;color:#fff;border-bottom:12px solid rgba(56,56,60,1);align-items:center;justify-content:center;border-radius:3px;margin-bottom:21px;}
.metardewtoday0{background:rgba(68, 166, 181, 1.000)}
.metardewtoday5{background:rgba(144, 177, 42, 1.000)}
.metardewtoday10{background:rgba(230, 161, 65, 1.000)}
.metardewtoday20{background:rgba(255, 124, 57, 1.000)}
.metardewtoday25{background:rgba(255, 124, 57, 0.7)}
.metardewtoday30{background:rgba(211, 93, 78, 1.000)}

.metarhumcontainer1{position:relative;top:-100px;font-size:.7rem;z-index:1;color:#fff;margin-left:92px;display:inline-block;}
.metarhumcontainer2{left:70px;margin-top:10px}
.humword{position:relative;top:-90px;font-size:.65rem;z-index:1;color:#fff;margin-left:102px}
.metarhumtoday0-35,
.metarhumtoday35-70,
.metarhumtoday70-85,
.metarhumtoday85-100{font-family:weathertext2,Arial,Helvetica,system;width:4.5rem;height:2.5rem;-webkit-border-radius:3px;-moz-border-radius:3px;border-radius:3px;display:flex}
.metarhumtoday0-35,
.metarhumtoday35-70,
.metarhumtoday70-85,
.metarhumtoday85-100{font-size:1.1rem;padding-top:2px;color:#fff;border-bottom:12px solid rgba(56,56,60,1);align-items:center;justify-content:center;border-radius:3px;margin-bottom:-70px;}
.metarhumtoday0-35{background:rgba(211, 93, 78, 1.000)}
.metarhumtoday35-70{background:rgba(230, 161, 65, 1.000)}
.metarhumtoday70-85{background:rgba(230, 161, 65, 1.000)}
.metarhumtoday85-100{background:rgba(68, 166, 181, 1.000)}
.dewword,.tword{position:absolute;margin-top:-33px;font-size:.7rem;z-index:1;color:#fff}
.dewword{margin-left:8px}.tword{margin-left:20px}.tword2{position:absolute;margin-top:-32px;font-size:.65rem;z-index:1;color:#fff}
.dewword2{position:absolute;margin-top:33px;font-size:.65rem;z-index:1;color:#fff;margin-left:75px}.tword2{margin-left:70px}
.maxword{position:absolute;margin-top:-32px;font-size:.65rem;z-index:1;color:#fff}.maxword{margin-left:10px}

.windword{position:absolute;margin-top:32px;font-size:.65rem;z-index:1;color:#fff;margin-left:7px}
.metarwindtoday0,
.metarwindtoday5,
.metarwindtoday10,
.metarwindtoday20,
.metarwindtoday25,
.metarwindtoday30{font-family:weathertext2,Arial,Helvetica,system;width:5rem;height:2.5rem;-webkit-border-radius:3px;-moz-border-radius:3px;border-radius:3px;display:flex}
.metarwindtoday0,
.metarwindtoday5,
.metarwindtoday10,
.metarwindtoday15,
.metarwindtoday20,
.metarwindtoday25,
.metarwindtoday30{font-size:1.1rem;padding-top:0;color:#fff;border-bottom:10px solid rgba(56,56,60,1);align-items:center;justify-content:center;border-radius:3px;display:flex}
.metarwindtodaykts0,
.metarwindtodaykts5,
.metarwindtodaykts10,
.metarwindtodaykts20,
.metarwindtodaykts25,
.metarwindtodaykts30{font-family:weathertext2,Arial,Helvetica,system;width:5rem;height:2.5rem;-webkit-border-radius:3px;-moz-border-radius:3px;border-radius:3px;display:flex}
.metarwindtodaykts0,
.metarwindtodaykts5,
.metarwindtodaykts10,
.metarwindtodaykts15,
.metarwindtodaykts20,
.metarwindtodaykts25,
.metarwindtodaykts30{font-size:1.5rem;padding-top:0;color:#fff;border-bottom:12px solid rgba(56,56,60,1);align-items:center;justify-content:center;border-radius:3px;display:flex}
.actualt{position:relative;left:5px;-webkit-border-radius:3px;-moz-border-radius:3px;border-radius:3px;border-radius:3px;background:rgba(74, 99, 111, 0.1);
padding:5px;font-family:Arial, Helvetica, sans-serif;width:100px;height:0.8em;font-size:0.8rem;padding-top:2px;color:#aaa;
align-items:center;justify-content:center;margin-bottom:10px;top:0}
.actualw{position:relative;left:5px;-webkit-border-radius:3px;-moz-border-radius:3px;border-radius:3px;border-radius:3px;background:rgba(74, 99, 111, 0.1);
padding:5px;font-family:Arial, Helvetica, sans-serif;width:100px;height:0.8em;font-size:0.8rem;padding-top:2px;color:#aaa;
align-items:center;justify-content:center;margin-bottom:10px;top:0}
.metarwindtodaykts0{background:rgba(68, 166, 181, 1.000)}
.metarwindtodaykts5{background:rgba(144, 177, 42, 1.000)}
.metarwindtodaykts10{background:rgba(230, 161, 65, 1.000)}
.metarwindtodaykts20{background:rgba(255, 124, 57, 1.000)}
.metarwindtodaykts25{background:rgba(255, 124, 57, 0.7)}
.metarwindtodaykts30{background:rgba(211, 93, 78, 1.000)}
.metarwindtoday0{background:rgba(68, 166, 181, 1.000)}
.metarwindtoday5{background:rgba(144, 177, 42, 1.000)}
.metarwindtoday10{background:rgba(230, 161, 65, 1.000)}
.metarwindtoday20{background:rgba(255, 124, 57, 1.000)}
.metarwindtoday25{background:rgba(255, 124, 57, 0.7)}
.metarwindtoday30{background:rgba(211, 93, 78, 1.000)}
smalluvunit{font-size:.8rem;font-family:Arial,Helvetica,system;}
valuecalm{font-size:.8em;font-family:weathertext2;}
stationid{font-size:1.4em;font-family:weathertext2;color:#009bb4}
.hitemp,.lotemp{font-size:9px;}
.iconcondition{float:left;margin-bottom:65px;}
.icontext{position:absolute;float:left;margin-top:60px;text-align:left;}
.pressure{position:absolute;float:left;margin-top:60px;text-align:left;}
</style>

<div class="divumwxdarkbrowser" url="<?php echo $metarstationid;?> <?php echo $metarstationname;?> Conditions"></div>
  
<main class="grid">

 <article>
<div class=actualt style="background:teal;color:white;">&nbsp;&nbsp Current Conditions </div>
<div class="iconcondition"><?php echo "<img src='img/meteocons/".$sky_icon."' width='60px'>";?></div>
<div class="icontext"><?php echo $sky_desc;?> </div>
<br><br><br>

<div class="pressure">
<blue>Pressure</blue> <br>
<?php
if ($pressureunit == 'mbar' || $pressureunit == 'hPa' || $pressureunit == 'kPa' ) {
  echo $metarpressuremb ," (".$pressureunit.")";
} else {
  echo $metarpressurehg ," (inHG)";
}
?> - 
<?php
if ($pressureunit == 'mbar' || $pressureunit == 'hPa' || $pressureunit == 'kPa') {
  echo $metarpressurehg ," (inHG)";
} else {
  echo $metarpressuremb ," (mbar)";
}
?>
<blue><br>Visibility</blue> <br>
<?php
if ($units == 'us') {
  echo $metarvismiles  ," miles";
} else {
  echo $metarviskm ," meters";
}
?>
</div>
</article> 
  
  <article>       
<div class=actualt style="background:teal;color:white;">&nbsp;&nbsp Temperature </div>   

 <div class="metartempcontainer1"><?php
 if ($tempunit == 'C') {
  if ($metartemperaturec >30) {echo '<div class=metartemptoday30>'.$metartemperaturec."<smalluvunit> &nbsp;&deg;C";}
  else if ($metartemperaturec >25) {echo '<div class=metartemptoday25>'.$metartemperaturec."<smalluvunit> &nbsp;&deg;C";}
  else if ($metartemperaturec >20) {echo '<div class=metartemptoday20>'.$metartemperaturec."<smalluvunit> &nbsp;&deg;C";}
  else if ($metartemperaturec >10) {echo '<div class=metartemptoday10>'.$metartemperaturec."<smalluvunit> &nbsp;&deg;C";}
  else if ($metartemperaturec >5) {echo '<div class=metartemptoday5>'.$metartemperaturec."<smalluvunit> &nbsp;&deg;C";}
  else if ($metartemperaturec >-50) {echo '<div class=metartemptoday0>'.$metartemperaturec."<smalluvunit> &nbsp;&deg;C";}
  else if ($metartemperaturec =='') {echo '<div class=metartemptoday0>'.$metartemperaturec."<smalluvunit> N/A";}
 } else {
   if ($metartemperaturef >86) {echo '<div class=metartemptoday30>'.$metartemperaturef."<smalluvunit> &nbsp;&deg;F";}
  else if ($metartemperaturef >77) {echo '<div class=metartemptoday25>'.$metartemperaturef."<smalluvunit> &nbsp;&deg;F";}
  else if ($metartemperaturef >68) {echo '<div class=metartemptoday20>'.$metartemperaturef."<smalluvunit> &nbsp;&deg;F";}
  else if ($metartemperaturef >50) {echo '<div class=metartemptoday10>'.$metartemperaturef."<smalluvunit> &nbsp;&deg;F";}
  else if ($metartemperaturef >41) {echo '<div class=metartemptoday5>'.$metartemperaturef."<smalluvunit> &nbsp;&deg;F";}
  else if ($metartemperaturef >-50) {echo '<div class=metartemptoday0>'.$metartemperaturef."<smalluvunit> &nbsp;&deg;F";}
  else if ($metartemperaturef =='') {echo '<div class=metartemptoday0>'.$metartemperaturef."<smalluvunit> N/A";}
 }
?></smalluvunit></div></div>
<div class="tword"><?php if ($tempunit == 'F') {echo $metartemperaturec."&deg;C";} else if ($tempunit == 'C'){echo $metartemperaturef."&deg;F";}?></div>
</div>
   
<div class="lotemp">

<div class="metardewcontainer1"><?php
if ($tempunit == 'C') {
  if ($metardewpointc >30) {echo '<div class=metardewtoday30>'.$metardewpointc."<smalluvunit> &nbsp;&deg;C";}
  else if ($metardewpointc >25) {echo '<div class=metardewtoday25>'.$metardewpointc."<smalluvunit> &nbsp;&deg;C";}
  else if ($metardewpointc >20) {echo '<div class=metardewtoday20>'.$metardewpointc."<smalluvunit> &nbsp;&deg;C";}
  else if ($metardewpointc >10) {echo '<div class=metardewtoday10>'.$metardewpointc."<smalluvunit> &nbsp;&deg;C";}
  else if ($metardewpointc >5) {echo '<div class=metardewtoday5>'.$metardewpointc."<smalluvunit> &nbsp;&deg;C";}
  else if ($metardewpointc >-50) {echo '<div class=metardewtoday0>'.$metardewpointc."<smalluvunit> &nbsp;&deg;C";}
  else if ($metardewpointc=='') {echo '<div class=metartemptoday0>'.$metardewpointc."<smalluvunit> N/A";}
} else {
  if ($metardewpointf>86) {echo '<div class=metartemptoday30>'.$metardewpointf."<smalluvunit> &nbsp;&deg;F";}
  else if ($metardewpointf>77) {echo '<div class=metartemptoday25>'.$metardewpointf."<smalluvunit> &nbsp;&deg;F";}
  else if ($metardewpointf>68) {echo '<div class=metartemptoday20>'.$metardewpointf."<smalluvunit> &nbsp;&deg;F";}
  else if ($metardewpointf>50) {echo '<div class=metartemptoday10>'.$metardewpointf."<smalluvunit> &nbsp;&deg;F";}
  else if ($metardewpointf>41) {echo '<div class=metartemptoday5>'.$metardewpointf."<smalluvunit> &nbsp;&deg;F";}
  else if ($metardewpointf>-50) {echo '<div class=metartemptoday0>'.$metardewpointf."<smalluvunit> &nbsp;&deg;F";}
  else if ($metardewpointf=='') {echo '<div class=metartemptoday0>'.$metardewpointf."<smalluvunit> N/A";}
}
?></smalluvunit></div></div> 
 <div class="dewword">Dewpoint</div>

 <div class="metarhumcontainer1"><?php 
if ($metarhumidity >85) {echo '<div class=metarhumtoday85-100>'.$metarhumidity ."<smalluvunit> &nbsp;%";}
else if ($metarhumidity >70) {echo '<div class=metarhumtoday70-85>'.$metarhumidity ."<smalluvunit> &nbsp;%";}
else if ($metarhumidity  >35) {echo '<div class=metarhumtoday35-70>'.$metarhumidity ."<smalluvunit> &nbsp;%";}
else if ($metarhumidity >=0) {echo '<div class=metarhumtoday0-35>'.$metarhumidity ."<smalluvunit> &nbsp;%";}
else if ($metarhumidity=='') {echo '<div class=metarhumtoday0-35><smalluvunit> N/A';}
?></smalluvunit></div></div> 
<div class="humword">&nbsp;Humidity</div>
</div>
  
</article>  
   
  <article>
  <div class=actualw style="background:teal;color:white;">&nbsp;&nbsp Wind Speed</div>   
   <?php
//set windspeed variables
if ($windunit == 'km/h') {
  $metarwind1 = 'kmh';
  $metarwind2 = 'kts';
  $metarwind3 = 'mph';
  $metarwind4 = 'ms';
} else if ($windunit == 'mph') {
  $metarwind1 = 'mph';
  $metarwind2 = 'kts';
  $metarwind3 = 'kmh';
  $metarwind4 = 'ms';
} else if ($windunit == 'kts') {
  $metarwind1 = 'kts';
  $metarwind2 = 'mph';
  $metarwind3 = 'kmh';
  $metarwind4 = 'ms';
} else {
  $metarwind1 = 'ms';
  $metarwind2 = 'mph';
  $metarwind3 = 'kmh';
  $metarwind4 = 'kts';
}
  if ($metarwindspeedkmh >=50) {$metarkmh = '<div class=metarwindtoday30>'.$metarwindspeedkmh."<smalluvunit> &nbsp;km/h";}
  else if ($metarwindspeedkmh >=40) {$metarkmh = '<div class=metarwindtoday25>'.$metarwindspeedkmh."<smalluvunit>&nbsp; km/h";}
  else if ($metarwindspeedkmh >=30) {$metarkmh = '<div class=metarwindtoday20>'.$metarwindspeedkmh."<smalluvunit>&nbsp; km/h";}
  else if ($metarwindspeedkmh >0) {$metarkmh = '<div class=metarwindtoday10>'.$metarwindspeedkmh."<smalluvunit>&nbsp; km/h";}
  else {$metarkmh = '<div class=metarwindtoday10>'.'0'."<smalluvunit>&nbsp; km/h";}
  if ($metarwindspeedmph >=31.06) {$metarmph = '<div class=metarwindtoday30>'.$metarwindspeedmph."<smalluvunit> &nbsp;mph";}
  else if ($metarwindspeedmph >=24.85) {$metarmph = '<div class=metarwindtoday25>'.$metarwindspeedmph."<smalluvunit> &nbsp;mph";}
  else if ($metarwindspeedmph >=18.6) {$metarmph = '<div class=metarwindtoday20>'.$metarwindspeedmph."<smalluvunit> &nbsp;mph";}
  else if ($metarwindspeedmph >0) {$metarmph = '<div class=metarwindtoday10>'.$metarwindspeedmph."<smalluvunit> &nbsp;mph";}
  else {$metarmph = '<div class=metarwindtoday10>'.'0'."<smalluvunit> &nbsp;mph";}
  if ($metarwindspeedkts >=26.9) {$metarkts = '<div class=metarwindtoday30>'.$metarwindspeedkts."<smalluvunit> &nbsp;kts";}
  else if ($metarwindspeedkts >=21.5) {$metarkts = '<div class=metarwindtoday25>'.$metarwindspeedkts."<smalluvunit> &nbsp;kts";}
  else if ($metarwindspeedkts >=16.19) {$metarkts = '<div class=metarwindtoday20>'.$metarwindspeedkts."<smalluvunit> &nbsp;kts";}
  else if ($metarwindspeedkts >0) {$metarkts = '<div class=metarwindtoday10>'.$metarwindspeedkts."<smalluvunit> &nbsp;kts";}
  else {$metarkts = '<div class=metarwindtoday10>'.'0'."<smalluvunit> &nbsp;kts";}
  if ($metarwindspeedms >=13.8) {$metarms = '<div class=metarwindtoday30>'.$metarwindspeedms."<smalluvunit> &nbsp;m/s";}
  else if ($metarwindspeedms >=11.1) {$metarms = '<div class=metarwindtoday25>'.$metarwindspeedms."<smalluvunit> &nbsp;m/s";}
  else if ($metarwindspeedms >=8.3) {$metarms = '<div class=metarwindtoday20>'.$metarwindspeedms."<smalluvunit> &nbsp;m/s";}
  else if ($metarwindspeedms >0) {$metarms = '<div class=metarwindtoday10>'.$metarwindspeedms."<smalluvunit> &nbsp;m/s";}
  else {$metarms = '<div class=metarwindtoday10>'.'0'."<smalluvunit> &nbsp;m/s";}
$metarspot1 = 'metar'.$metarwind1;
$metarspot2 = 'metar'.$metarwind2;
$metarspot3 = 'metar'.$metarwind3;
$metarspot4 = 'metar'.$metarwind4;
?></div></div></div></div></smalluvunit>

<div class="metarwindcontainer1">
<?php
//wind1 kph
echo $$metarspot1;
?>
</div>
<div class="metarwindcontainer2">
<?php 
//wind2 mph
echo $$metarspot2;
?></smalluvunit></div>
</div>
<div class="metarwindcontainer3">
<?php 
//wind3 kts
echo $$metarspot3;
?></smalluvunit></div>
</div>
<div class="metarwindcontainer4">
<?php 
//wind4 ms
echo $$metarspot4;
?></smalluvunit></div>
</div>
</div>
</article>

<article>
<div class=actualw style="background:teal;color:white;">&nbsp;&nbsp Wind Direction</div> 
</div>
</div>

<style>
.wrap {
  position: relative;
  top: 10px;
  left: 7.5px;
  width: 157px;
  height: 165px;
  margin: 0 auto;
  z-index: 1;
  align-items: center;
  justify-content: center;
}
</style>

<div style="overflow: hidden">
<div class="wrap">
  <svg id="Metarcompass" width="140" height="140" xmlns="http://www.w3.org/2000/svg">
  </svg>
</div>
</div>

 <script>
            
    var theme = "<?php echo $theme;?>";

    if (theme === 'dark') {
    var baseTextColor = "silver";
    var ringColor = "rgba(59,60,63,1)";
    } else {
    var baseTextColor = "#2d3a4b";
    var ringColor = "rgba(230,232,239,1)";
    }

</script>
 
<script>

var svgNS = "http://www.w3.org/2000/svg";

var svg = document.getElementById("Metarcompass");

var theme = "<?php echo $theme;?>";

  var angle = "<?php echo $metarwindir;?>";
  angle = angle || 0;
   
  var Bearing = "<?php echo $metarwindir;?>";
  Bearing = Bearing || 0;
    
  // Bearing  
  if (Bearing <= 11.25) { 
    Bearing = "North";
    } else if (Bearing <= 33.75) {
    Bearing = "NNE";
    } else if (Bearing <= 56.25) {
    Bearing = "NE";
    } else if (Bearing <= 78.75) {
    Bearing = "ENE";
    } else if (Bearing <= 101.25) {
    Bearing = "East";
    } else if (Bearing <= 123.75) { 
    Bearing = "ESE";
    } else if (Bearing <= 146.25) { 
    Bearing = "SE";
    } else if (Bearing <= 168.75) {
    Bearing = "SSE";
    } else if (Bearing <= 191.25) {
    Bearing = "South";
    } else if (Bearing <= 213.75) {
    Bearing = "SSW";
    } else if (Bearing <= 236.25) { 
    Bearing = "SW";
    } else if (Bearing <= 281.25) {
    Bearing = "West";
    } else if (Bearing <= 303.75) { 
    Bearing = "WNW";
    } else if (Bearing <= 326.25) {
    Bearing = "NW";
    } else if (Bearing <= 348.75) {
    Bearing = "NNW";
    } else { Bearing = "North"; }
  
  DirectionBearing(70, 80, Bearing); // Bearing
    
  DirectionAngle(70, 65, angle + "\u00B0"); // Direction in degrees

  CardinalNorth(66.75, 29, "N");
  CardinalDirection(111, 72.75, "E");
  CardinalDirection(67, 116, "S");
  CardinalDirection(23, 72.75, "W");


for (var i = 0; i < 360; i += 2) {
  // draw degree lines
  var s = ringColor; // dark grey
  if (i == 0 || i % 30 == 0) {
    w = 1;
    s = "rgba(255, 99, 71, 1)"; // tomato
    y2 = 17;
  } else {
    w = 0.75;
    y2 = 17;
  }
  
var ticks = document.createElementNS(svgNS, "line");
  ticks.setAttributeNS(null, "x1", 70);
  ticks.setAttributeNS(null, "y1", 10);
  ticks.setAttributeNS(null, "x2", 70);
  ticks.setAttributeNS(null, "y2", y2);
  ticks.setAttributeNS(null, "stroke", s);
  ticks.setAttributeNS(null, "stroke-width", w);
  ticks.setAttributeNS(null, "stroke-linecap", "round");
  ticks.setAttributeNS(null, "transform", "rotate(" + i + ", 70, 70)");
  svg.appendChild(ticks);

  // draw degree value every 30 degrees
  if (i % 30 == 0) {
    var t1 = document.createElementNS(svgNS, "text");
    if (i > 100) {
      t1.setAttributeNS(null, "x", 62.50);
    } else if (i > 0) {
      t1.setAttributeNS(null, "x", 65);
    } else {
      t1.setAttributeNS(null, "x", 67.75);
    }
    t1.setAttributeNS(null, "y", 7);
    t1.setAttributeNS(null, "font-size", "8px");
    t1.setAttributeNS(null, "font-family", "Helvetica");
    t1.setAttributeNS(null, "fill", "rgba(147,147,147,1)");
    t1.setAttributeNS(null, "style", "letter-spacing: 1.0");
    t1.setAttributeNS(null, "transform", "rotate(" + i + ", 70, 70)");
    var textNode = document.createTextNode(i);
    t1.appendChild(textNode);
    svg.appendChild(t1);
  }
}

function CardinalNorth(x, y, displayText) {
  var direction = document.createElementNS(svgNS, "text");
    direction.setAttributeNS(null, "x", x);
    direction.setAttributeNS(null, "y", y);
    direction.setAttributeNS(null, "font-size", "9px");
    direction.setAttributeNS(null, "font-weight", "bold");
    direction.setAttributeNS(null, "font-family", "Helvetica");
    direction.setAttributeNS(null, "fill", "red");
  var textNode = document.createTextNode(displayText);
    direction.appendChild(textNode);
    svg.appendChild(direction);
}

function CardinalDirection(x, y, displayText) {
  var direction = document.createElementNS(svgNS, "text");
    direction.setAttributeNS(null, "x", x);
    direction.setAttributeNS(null, "y", y);
    direction.setAttributeNS(null, "font-size", "8px");
    direction.setAttributeNS(null, "font-family", "Helvetica");
    direction.setAttributeNS(null, "fill", baseTextColor);
  var textNode = document.createTextNode(displayText);
    direction.appendChild(textNode);
    svg.appendChild(direction);
}

function DirectionAngle(x, y, displayText) {
    var anglen = document.createElementNS(svgNS, "text");
    anglen.setAttributeNS(null, "x", x);
    anglen.setAttributeNS(null, "y", y);
    anglen.setAttributeNS(null, "font-size", "12px");
    anglen.setAttributeNS(null, "font-family", "Helvetica");
    anglen.setAttributeNS(null, "fill", baseTextColor);  
    anglen.setAttributeNS(null, "text-anchor", "middle");  
    var textNode = document.createTextNode(displayText);
    anglen.appendChild(textNode);
    svg.appendChild(anglen);
}

function DirectionBearing(x, y, displayText) {
    var bearing = document.createElementNS(svgNS, "text");
    bearing.setAttributeNS(null, "x", x);
    bearing.setAttributeNS(null, "y", y);
    bearing.setAttributeNS(null, "font-size", "12px");
    bearing.setAttributeNS(null, "font-family", "Helvetica");
    bearing.setAttributeNS(null, "fill", baseTextColor);  
    bearing.setAttributeNS(null, "text-anchor", "middle");  
    var textNode = document.createTextNode(displayText);
    bearing.appendChild(textNode);
    svg.appendChild(bearing);
}

var polypointer = document.createElementNS(svgNS, "polygon"); // wind direction arrow
  polypointer.setAttributeNS(null, "points", "70,22 75,2 70,8 65,2");
  polypointer.setAttributeNS(null, "fill", "rgba(0,127,255,1)"); // arch blue
  polypointer.setAttributeNS(null, "transform", "rotate("+ angle +", 70, 70)");
  svg.appendChild(polypointer);

</script>
  </article> 
 
  <article>
  <div class=actualt style="background:teal;color:white;">&nbsp;&nbsp Airport Data </div>   
  <stationid><?php echo $metarstationid ; ?></stationid><br>
  <div class="lotemp">
   <?php

if ($distanceunit == 'km') {$metdist1 = round($airport1dist,0,PHP_ROUND_HALF_UP); $metdist2 = round($airport1dist / 1.609,0,PHP_ROUND_HALF_UP); $metunit1 = 'km'; $metunit2 = 'mi';}
else if ($distanceunit == 'mi') {$metdist1 = round($airport1dist / 1.609,0,PHP_ROUND_HALF_UP); $metdist2 = round($airport1dist,0,PHP_ROUND_HALF_UP); $metunit1 = 'mi'; $metunit2 = 'km';}
echo "Location <orange>",$metarstationname  ;
echo '</orange> <orange>'.$metdist1.'</orange> '.$metunit1.' (<orange>';
echo $metdist2;
echo '</orange>'.$metunit2.')';
    ?>
 <div class="lotemp">
<?php //metar raw
echo "Metar: " .$metarraw."";?>
</div>

<style>
  a { text-decoration: none; }
.yricons {
  position: relative;
  margin-top: 20px;
  margin-left: 40px;
}
</style>

<div class="hitemp">
<?php //update timestamp
date_default_timezone_set($tz);$date = $metartime;$date = str_replace('@', ' ', $date);
$date = strtotime($date) + 60 * 60 * $UTC; echo date('jS M H:i',$date);
?> </div></div>
  </article>  
  <article>
  <div class=actualt style="background:teal;color:white;">&nbsp;&nbsp Raw Metar Info</div>  
  <div class="lotemp">
  <?php echo $info?> Raw METAR is the most common format in the world for the transmission of observational weather data. It is highly standardized through the International Civil Aviation Organization (ICAO), which allows it to be understood throughout most of the world.</span></div>
  </article>   
  <article>
  <div class=actualt style="background:teal;color:white;">&nbsp;&nbsp API  Info</div>  
  <div class="lotemp">
  <?php echo $info?> Data Provided by </span><a href="https://www.checkwx.com/<?php echo $icao1;?>" title="https://www.checkwx.com/<?php echo $icao1;?>" target="_blank" ><br><img src=img/checkwx.svg width=130px alt="https://www.checkwx.com/<?php echo $icao1;?>"></a></span> 
  </article>  
  <article>
  <div class=actualt style="background:teal;color:white;">&nbsp;&nbsp &copy; Info</div>  
  <div class="lotemp">
   <br><br>
  <?php echo $info?> Guide Info provided by <a href="https://en.wikipedia.org/wiki/METAR" title="https://en.wikipedia.org/wiki/METAR" target="_blank" style="font-size:9px;">Metar-Wikipedia </a>
   </span><br><div class="yricons"><a href="https://bas.dev/work/meteocons" target="_blank">&nbsp;&nbsp; Animated Icons by <img src="img/bm.svg" width="14px"></a></div></span></div> 
  </div>
</article> 
   
</main>

