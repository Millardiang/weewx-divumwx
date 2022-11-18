<body>
<?php
include('shared.php');
include('common.php');
//include('settings.php'); 
//include('settings1.php');

$json_string             = file_get_contents("jsondata/pu.txt");
$parsed_json             = json_decode($json_string, true);
$aqiweather["aqi"]       = $parsed_json['sensor']['stats']['pm2.5'];
$aqiweather["pm_units"]  = " μg/m<sup><b>3</b></sup>";
?>

<div class="updatedtime1"><?php if(file_exists($livedata)&&time() - filemtime($livedata)>300)echo $offline. '<offline> Offline </offline>';else echo $online.' '.date($timeFormat);?></div>

<div class="airqualitywordbig">Air Quality</div>
<div class="pm25">
<div class="tempconvertercirclepurple">PM 2.5</div>
</div>

<div class="airqualitymoduleposition">
<div class="tempcontainer">
<?php //WEATHER34 AIR QAULITY SVG
if ($aqiweather["aqi"]>250){echo "<div class=air300><img src='css/aqi/hazair.svg?ver=1.4' width='110px' height='100px' alt='weather34 hazardous air quality' title='weather34 hazardous air quality' ";}
else if ($aqiweather["aqi"]>150){echo "<div class=air200><img src='css/aqi/vhair.svg?ver=1.4' width='110px' height='100px' alt='weather34 very unhealthy air quality' title='weather34 very unhealthy air quality' ";}
else if ($aqiweather["aqi"]>55){echo "<div class=air150><img src='css/aqi/uhair.svg?ver=1.4' width='110px' height='100px' alt='weather34 unhealthy air quality' title='weather34 unhealthy air quality' ";}
else if ($aqiweather["aqi"]>35){echo "<div class=air100><img src='css/aqi/uhfsair.svg?ver=1.4' width='110px' height='100px'  alt='weather34 unhealthy for some air quality' title='weather34 unhealthy for some air quality' ";}
else if ($aqiweather["aqi"]>12){echo "<div class=air50><img src='css/aqi/modair.svg?ver=1.4' width='110px' height='100px' alt='weather34 moderate air quality' title='weather34 moderate air quality' ";}
else if ($aqiweather["aqi"]>=0){echo "<div class=air0><img src='css/aqi/goodair.svg?ver=1.4' width='110px' height='100px' alt='weather34 good air quality' title='weather34 good air quality' ";}
?>
</div></div></div> 
<div class="airsvg">
<?php 
if ($aqiweather["aqi"]>250){echo "<div class=dottedcirclered>";}
else if ($aqiweather["aqi"]>150){echo "<div class=dottedcirclepurple>";}
else if ($aqiweather["aqi"]>55){echo "<div class=dottedcirclered>";}
else if ($aqiweather["aqi"]>35){echo "<div class=dottedcircleorange>";}
else if ($aqiweather["aqi"]>12){echo "<div class=dottedcircleyellow>";}
else if ($aqiweather["aqi"]>=0){echo "<div class=dottedcirclegreen>";}
?>
<div class="airvalue">
<?php //WEATHER34 AIR QAULITY VALUE
 if ($aqiweather["aqi"] >250){echo $aqiweather["aqi"] ; echo $aqiweather["pm_units"];} 
 else if ($aqiweather["aqi"] >150){echo $aqiweather["aqi"] ; echo $aqiweather["pm_units"];}
 else if ($aqiweather["aqi"] >55){echo $aqiweather["aqi"] ; echo $aqiweather["pm_units"];}
 else if ($aqiweather["aqi"] >35){echo $aqiweather["aqi"] ; echo $aqiweather["pm_units"];}
 else if ($aqiweather["aqi"] >12){echo $aqiweather["aqi"] ; echo $aqiweather["pm_units"];}
 else if ($aqiweather["aqi"] >=0){echo $aqiweather["aqi"] ; echo $aqiweather["pm_units"];}
 //WEATHER34 air quality description
 if ($aqiweather["aqi"]>250){echo "<br><airdescription><indoorred>&nbsp;".$lang['Hazordous']."</airdescription>";}
 else if ($aqiweather["aqi"]>150){echo "<br><airdescription><indoorpurple>".$lang['VeryUnhealthy']."</airdescription>  ";}
 else if ($aqiweather["aqi"]>55){echo "<br><airdescription><indoorred>&nbsp;".$lang['Unhealthy']."</airdescription>";}
 else if ($aqiweather["aqi"]>35){echo "<br><airdescription><indoororange>".$lang['UnhealthyFS']."</airdescription>";} 
 else if ($aqiweather["aqi"]>12){echo "<br><airdescription><indooryellow>&nbsp;".$lang['Moderate']."</airdescription>";} 
 else if ($aqiweather["aqi"]>=0){echo "<br><airdescription><indoorgreen>&nbsp; &nbsp;".$lang['Good']."</airdescription>";} 
 ?>
</div></div></div>
<div class="airwarning"><?php 
if($aqiweather["aqi"]>250)echo $airalertred ;
else if($aqiweather["aqi"]>150)echo $airalertpurple ;
else if($aqiweather["aqi"]>55)echo $airalertred ;
else if($aqiweather["aqi"]>35)echo $airalertorange ;
else if($aqiweather["aqi"]>12)echo $airokyellow ;
else echo $airok ;?></div>
</body>
