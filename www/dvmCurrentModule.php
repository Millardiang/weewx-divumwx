<!DOCTYPE html>
<title>weather34 current conditions</title>
<?php
include('settings1.php');
include('dvmCombinedData.php');
$iconset = "icon2";
$cloud_region = explode("/", $TZ);
error_reporting(0);

if ($theme == "dark") {

echo
    
    '<style>
        uppercase{ text-transform:capitalize;}
        tyellow{
        color: #e6a141;
        tblue{
        color: #07727d;      
	</style>';
	
	} else if ($theme == "light"){
	
echo
	
    '<style>
    
        uppercase{ text-transform:capitalize;}
        tyellow,
        tblue {
        color: #ffffff;
}
   
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
<div class="updatedtimecurrent">
<?php $forecastime=filemtime('jsondata/awc.txt');$weather34wuurl = file_get_contents("jsondata/awc.txt");if(filesize('jsondata/awc.txt')<10){echo $online;}
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
else if ($windunit =='mph' || $windunit =='kts' && $sky["cloud_base"]<1999){echo "<div class=cloudconvertercircle>".$clouds."<tblue> ".$sky["cloud_base"]."</tblue><smalltempunit2> ".$distance."</tblue><smalltempunit2>" ;}
else if ($windunit =='km/h' || $windunit =='m/s' && $sky["cloud_base"]>=609){echo "<div class=cloudconvertercircle2000>".$clouds."<tyellow> ".$sky["cloud_base"]."</tyellow><smalltempunit2> ".$distance."</tblue><smalltempunit2>" ;}
else if ($windunit =='km/h' || $windunit =='m/s' && $sky["cloud_base"]<609){echo "<div class=cloudconvertercircle>".$clouds."<tblue> ".$sky["cloud_base"]."</tblue><smalltempunit2> ".$distance."</tblue><smalltempunit2>" ;}}
?>
</div></div>
<div class="darkskyiconcurrent"><span1>
<?php 
//homeweatherstation weather34 current conditions using hardware values
if ($windunit=='kts'){$windunit="kn";}       
//rain-weather34
if($rain["rate"]>0 && $wind["speed_avg"]>15){echo "<img rel='prefetch' src='img/meteocons/umbrella-wind.svg' width='60px' height='55px' margin-bottom='15px' alt='weather34 windy rain icon'>";}
else if($rain["rate"]>10){echo "<img rel='prefetch' src='img/meteocons/umbrella-wind-alt.svg' width='70px' height='55px' alt='weather34 heavy rain icon'>";}
else if($rain["rate"]>0){echo "<img rel='prefetch' src='img/meteocons/overcast-rain.svg' width='70px' height='55px' alt='weather34 rain icon'>";}
//fog-weather34
else if($temp["outside_now"] -$dew["now"] <0.8  && $dayPartNatural == "night" && $temp["outside_now"]>5){echo "<img rel='prefetch' src='img/meteocons/fog-night.svg' width='70px' height='55px' alt='weather34 fog icon'>";}
else if($temp["outside_now"] -$dew["now"] <0.8  && $temp["outside_now"]>5){echo "<img rel='prefetch' src='img/meteocons/fog-day.svg' width='70px' height='55px' alt='weather34 fog'>";}
//windy moderate-weather34
else if($wind["speed_avg"]>=15 && $dayPartNatural == "night" && $sky["cloud_cover"]<20){echo "<img rel='prefetch' src='img/meteocons/wind.svg' width='70px' height='55px' alt='weather34 windy icon'>";}
else if($wind["speed_avg"]>=15 && $sky["cloud_cover"]<20){echo "<img rel='prefetch' src='img/meteocons/wind.svg' width='70px' height='55px' alt='weather34 windy icon'>";}
//windy moderate-weather34
else if($wind["speed_avg"]>=15 && $dayPartNatural == "night"){echo "<img rel='prefetch' src='img/meteocons/wind.svg' width='70px' height='55px' alt='weather34 windy icon'>";}
else if($wind["speed_avg"]>=15){echo "<img rel='prefetch' src='img/meteocons/wind.svg' width='70px' height='55px' alt='weather34 windy icon'>";}

//cloud-icon
else if ($sky["cloud_cover"]<7 and $sky["cloud_cover"]>0) {
if ($dayPartNatural == "night" ){echo "<img rel='prefetch' src='img/meteocons/clear-night.svg' width='70px' height='55px' alt='weather34 windy icon'>";} 
else echo "<img rel='prefetch' src='img/meteocons/clear-day.svg' width='70px' height='55px' alt='weather34 windy icon'>"; 
} 
else if ($sky["cloud_cover"]<32) {
if ($dayPartNatural == "night" ){echo "<img rel='prefetch' src='img/meteocons/mostly-clear-night.svg' width='70px' height='55px' alt='weather34 windy icon'>";} 
else echo "<img rel='prefetch' src='img/meteocons/mostly-clear-day.svg' width='70px' height='55px' alt='weather34 windy icon'>"; 
}
else if ($sky["cloud_cover"]<70) {
if ($dayPartNatural == "night" ){echo "<img rel='prefetch' src='img/meteocons/partly-cloudy-night.svg' width='70px' height='55px' alt='weather34 windy icon'>";} 
else echo "<img rel='prefetch' src='img/meteocons/partly-cloudy-day.svg' width='70px' height='55px' alt='weather34 windy icon'>"; 
}
else if ($sky["cloud_cover"]<95) {
if ($dayPartNatural == "night" ){echo "<img rel='prefetch' src='img/meteocons/overcast-day.svg' width='70px' height='55px' alt='weather34 windy icon'>";} 
else echo "<img rel='prefetch' src='img/meteocons/overcast-night.svg' width='70px' height='55px' alt='weather34 windy icon'>"; 
}
else if($sky["cloud_cover"]>=95) {
if ($dayPartNatural == "night" ){echo "<img rel='prefetch' src='img/meteocons/overcast.svg' width='70px' height='55px' alt='weather34 windy icon'>";} 
else echo "<img rel='prefetch' src='img/meteocons/overcast.svg' width='70px' height='55px' alt='weather34 windy icon'>"; 
}

?>
</div>
<div class="darkskysummary"><span>
<?php echo '';
if ($windunit=='kts'){$windunit="kn";}
//rain-weather34
if($rain["rate"]>0 && $wind["speed_avg"]>15){echo "Rain Showers"; echo '<br>';echo "Windy Conditions";}
else if($rain["rate"]>=20){echo "Heavy Rain"; echo '<br>';echo "Flooding Possible";}
else if($rain["rate"]>=10){echo "Heavy Rain"; echo '<br>Showers';}
else if($rain["rate"]>=5){echo "Moderate Rain"; echo '<br>Showers';}
else if($rain["rate"]>=1){echo "Steady Rain";echo '<br>Showers';}
else if($rain["rate"]>0){echo "Light Rain";echo '<br>Showers';}
//fog-weather34
else if($temp["outside_now"] -$dew["now"] <0.5  && $dayPartNatural == "night" && $temp["outside_now"]>5){echo "Misty Fog<br>Conditions ".$alert."";}
else if($temp["outside_now"] -$dew["now"] <0.5  && $temp["outside_now"]>5){echo "Misty Fog<br>Conditions ".$alert."";}
//misty-weather34
else if($temp["outside_now"] -$dew["now"] <0.8  && $dayPartNatural == "night" && $temp["outside_now"]>5){echo "Fog Hazy<br>Conditions";}
else if($temp["outside_now"] -$dew["now"] <0.8  && $temp["outside_now"]>5){echo "Misty Hazy<br>Conditions";}
//windy-weather34
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
//metar conditions-weather34

?>
</span></div>
 <!-- weather34 generated Data--> 
<div class="darkskynexthours" style="margin: 60px auto auto; margin-top: 55px; margin-left: 35px; text-align: center">
<?php //weather34 average station data
//echo "Average <oblue>Cloud Cover</oblue> last 5 minutes <ogreen>" .$sky["cloud_cover"]."</ogreen><valuetext>".$cloudcoverunit. "(".$sky["cloud_oktas"].")";

if ($visibility!==0){echo "</br>Visibility <oorange>".$visibility."</oorange></br>";}
if (strpos($sky["cloud_cover"],"N/A") == false){
echo "<oblue>Cloud Cover</oblue><ogreen> " .$sky["cloud_cover"]."</ogreen><valuetext>".$cloudcoverunit. " (".$sky["cloud_oktas"].")";
}


echo "<br>Average <oorange>Temperature</oorange> last 60 minutes ";if($temp["outside_day_avg_60mn"]>=20){echo "<oorange>" .$temp["outside_day_avg_60mn"]."</oorange>°<valuetext>".$tempunit;} else if($temp["outside_day_avg_60mn"]<=10){echo "<oblue>" .$temp["outside_day_avg_60mn"]."</oblue>°<valuetext>".$tempunit;}else if($temp["outside_day_avg_60mn"]<20){echo "<ogreen>" .$temp["outside_day_avg_60mn"]."</ogreen>°<valuetext>".$tempunit;}echo "</valuetext><br>";
echo  "Max <oblue>Wind Gust last 10 minutes</oblue> ";
if ($windunit=='kts'){$windunit="kn";}
if($wind["gust_10m_max"]>=50){echo "<ored>" .$wind["gust_10m_max"]."</ored> ".$windunit;}
else if($wind["gust_10m_max"]>=30){echo "<oorange>" .$wind["gust_10m_max"]."</oorange><valuetext> ".$windunit;}
else if($wind["gust_10m_max"]>=0){echo "<ogreen>" .$wind["gust_10m_max"]."</ogreen><valuetext> ".$windunit;}echo " </valuetext> ";
echo  " <br>Average <oblue>Wind Speed</oblue> last 10 minutes ";if($wind["speed_10m_avg"]>=30){echo "<ored>" .$wind["speed_10m_avg"]."</ored> ".$windunit;}else if($wind["speed_10m_avg"]>=20){echo "<oorange>" .$wind["speed_10m_avg"]."</oorange><valuetext> ".$windunit;}
else if($wind["speed_10m_avg"]>=0){echo "<ogreen>" .$wind["speed_10m_avg"]."</ogreen><valuetext> ".$windunit;}
echo "</valuetext>";
if($wind["direction_10m_avg"]>0){echo "<br>Wind Average Dir last 10 minutes <oorange>"; if($wind["direction_10m_avg"]<=11.25){echo "North";}else if($wind["direction_10m_avg"]<=33.75){echo "NNE";}else if($wind["direction_10m_avg"]<=56.25){echo "NE";}else if($wind["direction_10m_avg"]<=78.75){echo "ENE";}else if($wind["direction_10m_avg"]<=101.25){echo "East";}else if($wind["direction_10m_avg"]<=123.75){echo "ESE";}else if($wind["direction_10m_avg"]<=146.25){echo "SE";}
else if($wind["direction_10m_avg"]<=168.75){echo "SSE";}else if($wind["direction_10m_avg"]<=191.25){echo "South";}else if($wind["direction_10m_avg"]<=213.75){echo "SSW";}else if($wind["direction_10m_avg"]<=236.25){echo "SW";}else if($wind["direction_10m_avg"]<=258.75){echo "WSW";}else if($wind["direction_10m_avg"]<=281.25){echo "West";}else if($wind["direction_10m_avg"]<=303.75){echo "WNW";}else if($wind["direction_10m_avg"]<=326.25){echo "NW";}else if($wind["direction_10m_avg"]<=348.75){echo "NNW";}else{echo "North";}
echo " </oorange><oblue> ".$wind["direction_10m_avg"]."</oblue>°";}
echo "</oorange><br><oblue>Rainfall</oblue> for the last 10 minutes <oblue> " .$rain["last_10min"]."</oblue><valuetext> " .$rainunit;
?></valuetext></div></div></div>
