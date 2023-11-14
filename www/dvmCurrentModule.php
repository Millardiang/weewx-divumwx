<?php
#####################################################################################################################                                                                                                        #
#                                                                                                                   #
# weewx-divumwx Skin Template maintained by The DivumWX Team                                                        #
#                                                                                                                   #
# Copyright (C) 2023 Ian Millard, Steven Sheeley, Sean Balfour. All rights reserved                                 #
#                                                                                                                   #
# Distributed under terms of the GPLv3. See the file LICENSE.txt for your rights.                                   #
#                                                                                                                   #
# Issues for weewx-divumwx skin template should be addressed to https://github.com/Millardiang/weewx-divumwx/issues # 
#                                                                                                                   #
#####################################################################################################################
?>
<!DOCTYPE html>
<meta charset="utf-8">
<title>weather current conditions</title>
<?php
include('dvmCombinedData.php');
$iconset = "icon2";
$cloud_region = explode("/", $TZ);
error_reporting(0);

if ($theme == "light"){
	
echo
	
    '<style>
    
        uppercase{ text-transform:capitalize;}
        tyellow,
        tblue {
        color: #ffffff;

   
	</style>';
	
}

$json_visibility = file_get_contents("jsondata/awc.txt");
$parsed_visibility = json_decode($json_visibility, true);

if ($windunit =='mph'){
$visibility = round($parsed_visibility['response'][0]['periods'][0]['visibilityMI'],0,PHP_ROUND_HALF_UP)."mi";
}
else
{
$visibility = round($parsed_visibility['response'][0]['periods'][0]['visibilityKM'],0,PHP_ROUND_HALF_UP)."km";
}
if ($cloud_region[0] !== "Europe"){$sky["cloud_cover"] = $parsed_visibility['response'][0]['periods'][0]['sky'];}
?>

<div class="chartforecast">
      <span class="yearpopup"><a alt="nearby metar station" title="nearby metar station" href="dvmMetarPopup.php" data-lity><?php echo $chartinfo;?><?php echo ' Nearby Metar';?>
      <span class="monthpopup"><a href="dvmWindyRadarPopup.php" title="Windy.com Radar" alt="Windy.com Radar" data-lity><?php echo $chartinfo;?> Radar</a></span>
      <span class="monthpopup"><a href="dvmWindyWindPopup.php" title="Windy.com Wind Map" alt="Windy.com Wind Map" data-lity><?php echo $chartinfo;?> Wind Map</a></span>
      <span class="yearpopup"><a alt="cloud cover" title="Cloud Cover" href="dvmMenuCloudcover.php" data-lity><?php echo $chartinfo;?><?php echo ' Cloud Cover';?>
    </div>
    <span class='moduletitle'><?php echo $lang['currentModule'];?></span>



<div class="updatedtimecurrent">
<?php $forecastime=filemtime('jsondata/awc.txt');$divumwxwuurl = file_get_contents("jsondata/awc.txt");if(filesize('jsondata/awc.txt')<10){echo $online;}
else echo $online,"";echo " ",	date($timeFormat,$forecastime);	?></div>
    




<div class="cloudconverter">
<?php //cloudbase

$cloudcoverunit = '%';
$clouds = "Cloudbase";
if ($windunit =='mph' || $windunit =='kts'){$sky["cloud_base"] = round($sky["cloud_base"]*3.281);}
if ($windunit =='mph' || $windunit =='kts'){$distance="ft";}
else if ($windunit =='km/h' || $windunit =='m/s'){$distance="m";}

if ($sky["cloud_base"] > 0){
if ($windunit =='mph' || $windunit =='kts' && $sky["cloud_base"]>=1999){echo "<div class=cloudconvertercircle2000>".$clouds."<tyellow> ".$sky["cloud_base"]."</tyellow><smalltempunit2> ".$distance."</tblue><smalltempunit2>" ;}
else if ($windunit =='mph' || $windunit =='kts' && $sky["cloud_base"]<1999){echo "<div class=cloudconvertercircle2000>".$clouds."<tblue> ".$sky["cloud_base"]."</tblue><smalltempunit2> ".$distance."</tblue><smalltempunit2>" ;}
else if ($windunit =='km/h' || $windunit =='m/s' && $sky["cloud_base"]>=609){echo "<div class=cloudconvertercircle2000>".$clouds."<tyellow> ".$sky["cloud_base"]."</tyellow><smalltempunit2> ".$distance."</tblue><smalltempunit2>" ;}
else if ($windunit =='km/h' || $windunit =='m/s' && $sky["cloud_base"]<609){echo "<div class=cloudconvertercircle2000>".$clouds."<tblue> ".$sky["cloud_base"]."</tblue><smalltempunit2> ".$distance."</tblue><smalltempunit2>" ;}}
?>
</div></div>
<div class="aerisiconcurrent"><span1>
<?php 
//current conditions using hardware values
      
//rain-divumwx
if($rain["rate"]>0 && $wind["speed_avg"]>15){echo "<img rel='prefetch' src='img/meteocons/umbrella-wind.svg' width='60px' height='55px' margin-bottom='15px' alt='divumwx windy rain icon'>";}
else if($rain["rate"]>10){echo "<img rel='prefetch' src='img/meteocons/umbrella-wind-alt.svg' width='70px' height='55px' alt='divumwx heavy rain icon'>";}
else if($rain["rate"]>0){echo "<img rel='prefetch' src='img/meteocons/overcast-rain.svg' width='70px' height='55px' alt='divumwx rain icon'>";}
//fog-divumwx
else if($temp["outside_now"] -$dew["now"] <0.8  && $dayPartNatural == "night" && $temp["outside_now"]>5){echo "<img rel='prefetch' src='img/meteocons/fog-night.svg' width='70px' height='55px' alt='divumwx fog icon'>";}
else if($temp["outside_now"] -$dew["now"] <0.8  && $temp["outside_now"]>5){echo "<img rel='prefetch' src='img/meteocons/fog-day.svg' width='70px' height='55px' alt='divumwx fog'>";}
//windy moderate-divumwx
else if($wind["speed_avg"]>=15 && $dayPartNatural == "night" && $sky["cloud_cover"]<20){echo "<img rel='prefetch' src='img/meteocons/wind.svg' width='70px' height='55px' alt='divumwx windy icon'>";}
else if($wind["speed_avg"]>=15 && $sky["cloud_cover"]<20){echo "<img rel='prefetch' src='img/meteocons/wind.svg' width='70px' height='55px' alt='divumwx windy icon'>";}
//windy moderate-divumwx
else if($wind["speed_avg"]>=15 && $dayPartNatural == "night"){echo "<img rel='prefetch' src='img/meteocons/wind.svg' width='70px' height='55px' alt='divumwx windy icon'>";}
else if($wind["speed_avg"]>=15){echo "<img rel='prefetch' src='img/meteocons/wind.svg' width='70px' height='55px' alt='divumwx windy icon'>";}

//cloud-icon
else if ($sky["cloud_cover"]<7 and $sky["cloud_cover"]>0) {
if ($dayPartNatural == "night" ){echo "<img rel='prefetch' src='img/meteocons/clear-night.svg' width='70px' height='55px' alt='divumwx windy icon'>";} 
else echo "<img rel='prefetch' src='img/meteocons/clear-day.svg' width='70px' height='55px' alt='divumwx windy icon'>"; 
} 
else if ($sky["cloud_cover"]<32) {
if ($dayPartNatural == "night" ){echo "<img rel='prefetch' src='img/meteocons/mostly-clear-night.svg' width='70px' height='55px' alt='divumwx windy icon'>";} 
else echo "<img rel='prefetch' src='img/meteocons/mostly-clear-day.svg' width='70px' height='55px' alt='divumwx windy icon'>"; 
}
else if ($sky["cloud_cover"]<70) {
if ($dayPartNatural == "night" ){echo "<img rel='prefetch' src='img/meteocons/partly-cloudy-night.svg' width='70px' height='55px' alt='divumwx windy icon'>";} 
else echo "<img rel='prefetch' src='img/meteocons/partly-cloudy-day.svg' width='70px' height='55px' alt='divumwx windy icon'>"; 
}
else if ($sky["cloud_cover"]<95) {
if ($dayPartNatural == "night" ){echo "<img rel='prefetch' src='img/meteocons/overcast-day.svg' width='70px' height='55px' alt='divumwx windy icon'>";} 
else echo "<img rel='prefetch' src='img/meteocons/overcast-night.svg' width='70px' height='55px' alt='divumwx windy icon'>"; 
}
else if($sky["cloud_cover"]>=95) {
if ($dayPartNatural == "night" ){echo "<img rel='prefetch' src='img/meteocons/overcast.svg' width='70px' height='55px' alt='divumwx windy icon'>";} 
else echo "<img rel='prefetch' src='img/meteocons/overcast.svg' width='70px' height='55px' alt='divumwx windy icon'>"; 
}

?>
</div>
<div class="aerissummary"><span>
<?php echo '';

//rain-divumwx
if($rain["rate"]>0 && $wind["speed_avg"]>15){echo "Rain Showers"; echo '<br>';echo "Windy Conditions";}
else if($rain["rate"]>=20){echo "Heavy Rain"; echo '<br>';echo "Flooding Possible";}
else if($rain["rate"]>=10){echo "Heavy Rain"; echo '<br>Showers';}
else if($rain["rate"]>=5){echo "Moderate Rain"; echo '<br>Showers';}
else if($rain["rate"]>=1){echo "Steady Rain";echo '<br>Showers';}
else if($rain["rate"]>0){echo "Light Rain";echo '<br>Showers';}
//fog-divumwx
else if($temp["outside_now"] -$dew["now"] <0.5  && $dayPartNatural == "night" && $temp["outside_now"]>5){echo "Misty Fog<br>Conditions ".$alert."";}
else if($temp["outside_now"] -$dew["now"] <0.5  && $temp["outside_now"]>5){echo "Misty Fog<br>Conditions ".$alert."";}
//misty-divumwx
else if($temp["outside_now"] -$dew["now"] <0.8  && $dayPartNatural == "night" && $temp["outside_now"]>5){echo "Fog Hazy<br>Conditions";}
else if($temp["outside_now"] -$dew["now"] <0.8  && $temp["outside_now"]>5){echo "Misty Hazy<br>Conditions";}
//windy-divumwx
else if($wind["speed_avg"]>=40){echo "Strong Wind ".$alert."<br>Conditions" ;}
else if($wind["speed_avg"]>=30){echo "Very Windy ".$alert."<br>Conditions";}
else if($wind["speed_avg"]>=22){echo "Moderate Wind <br>Conditions";}
else if($wind["speed_avg"]>=15){echo "Breezy <br>Conditions";}
//cloud-description
else if($sky["cloud_cover"]<7 and $sky["cloud_cover"]>0) {echo "Clear <br>Conditions";}
else if ($sky["cloud_cover"]<7 and $sky["cloud_cover"]>0) {
if ($dayPartNatural == "night" ){echo "Clear <br>Conditions";} 
else echo "Sunny <br>Conditions"; 
} 
else if ($sky["cloud_cover"]<32) {
if ($dayPartNatural == "night" ){echo "Mostly Clear <br>Conditions";} 
else echo "Mostly Sunny <br>Conditions"; 
}
else if($sky["cloud_cover"]<70) {echo "Partly Cloudy <br>Conditions";}
else if($sky["cloud_cover"]<95) {echo "Mostly Cloudy <br>Conditions";}
else if($sky["cloud_cover"]>=95) {echo "Overcast <br>Conditions";}
else if(filesize('jsondata/me.txt')<160){echo "<uppercase>Conditions<br>Not Available</uppercase>";}
//oktas
if($sky["cloud_cover"]<5 and $sky["cloud_cover"]>0) {$sky["cloud_oktas"]="0 oktas";}
else if($sky["cloud_cover"]<=12.5) {$sky["cloud_oktas"]="1 okta";}
else if($sky["cloud_cover"]<=25) {$sky["cloud_oktas"]="2 oktas";}
else if($sky["cloud_cover"]<=37.5) {$sky["cloud_oktas"]="3 oktas";}
else if($sky["cloud_cover"]<=50) {$sky["cloud_oktas"]="4 oktas";}
else if($sky["cloud_cover"]<=62.5) {$sky["cloud_oktas"]="5 oktas";}
else if($sky["cloud_cover"]<=75) {$sky["cloud_oktas"]="6 oktas";}
else if($sky["cloud_cover"]<=87.5) {$sky["cloud_oktas"]="7 oktas";}
else if($sky["cloud_cover"]<=100) {$sky["cloud_oktas"]="8 oktas";}
//metar conditions-divumwx

?>
</span></div>
 <!-- weewx generated Data--> 
<div class="aerisnexthours" style="margin: 60px auto auto; margin-top: 55px; margin-left: 35px; text-align: center">
<?php //weewx average station data

if ($visibility!==0){echo "</br>Visibility <oorange>".$visibility."</oorange></br>";}
if (strpos($sky["cloud_cover"],"N/A") == false){
echo "<oblue>Cloud Cover</oblue><ogreen> " .$sky["cloud_cover"]."</ogreen><valuetext>".$cloudcoverunit. " (".$sky["cloud_oktas"].")";
}
if ($tempunit=="C"){$tempunit="&deg;C";}
else {$tempunit="&deg;F";}
$directionSymbol="&deg;";
echo "<br>Average <oorange>Temperature</oorange> last 60 minutes ";if($temp["outside_day_avg_60mn"]>=20){echo "<oorange>" .$temp["outside_day_avg_60mn"]."</oorange><valuetext>".$tempunit;} else if($temp["outside_day_avg_60mn"]<=10){echo "<oblue>" .$temp["outside_day_avg_60mn"]."</oblue><valuetext>".$tempunit;}else if($temp["outside_day_avg_60mn"]<20){echo "<ogreen>" .$temp["outside_day_avg_60mn"]."</ogreen><valuetext>".$tempunit;}echo "</valuetext><br>";
echo  "Max <oblue>Wind Gust last 10 minutes</oblue> ";
if($wind["gust_10m_max"]>=50){echo "<ored>" .$wind["gust_10m_max"]."</ored> ".$windunit;}
else if($wind["gust_10m_max"]>=30){echo "<oorange>" .$wind["gust_10m_max"]."</oorange><valuetext> ".$windunit;}
else if($wind["gust_10m_max"]>=0){echo "<ogreen>" .$wind["gust_10m_max"]."</ogreen><valuetext> ".$windunit;}echo " </valuetext> ";
echo  " <br>Average <oblue>Wind Speed</oblue> last 10 minutes ";if($wind["speed_10m_avg"]>=30){echo "<ored>" .$wind["speed_10m_avg"]."</ored> ".$windunit;}else if($wind["speed_10m_avg"]>=20){echo "<oorange>" .$wind["speed_10m_avg"]."</oorange><valuetext> ".$windunit;}
else if($wind["speed_10m_avg"]>=0){echo "<ogreen>" .$wind["speed_10m_avg"]."</ogreen><valuetext> ".$windunit;}
echo "</valuetext>";
if($wind["direction_10m_avg"]>0){echo "<br>Wind Average Dir last 10 minutes <oorange>"; if($wind["direction_10m_avg"]<=11.25){echo "North";}else if($wind["direction_10m_avg"]<=33.75){echo "NNE";}else if($wind["direction_10m_avg"]<=56.25){echo "NE";}else if($wind["direction_10m_avg"]<=78.75){echo "ENE";}else if($wind["direction_10m_avg"]<=101.25){echo "East";}else if($wind["direction_10m_avg"]<=123.75){echo "ESE";}else if($wind["direction_10m_avg"]<=146.25){echo "SE";}
else if($wind["direction_10m_avg"]<=168.75){echo "SSE";}else if($wind["direction_10m_avg"]<=191.25){echo "South";}else if($wind["direction_10m_avg"]<=213.75){echo "SSW";}else if($wind["direction_10m_avg"]<=236.25){echo "SW";}else if($wind["direction_10m_avg"]<=258.75){echo "WSW";}else if($wind["direction_10m_avg"]<=281.25){echo "West";}else if($wind["direction_10m_avg"]<=303.75){echo "WNW";}else if($wind["direction_10m_avg"]<=326.25){echo "NW";}else if($wind["direction_10m_avg"]<=348.75){echo "NNW";}else{echo "North";}
echo " </oorange><oblue> ".$wind["direction_10m_avg"]."</oblue>".$directionSymbol;}
echo "</oorange><br><oblue>Rainfall</oblue> for the last 10 minutes <oblue> " .$rain["last_10min"]."</oblue><valuetext> " .$rainunit;
?></valuetext></div></div></div>