<?php
include('sunsynkData.php');
$solarGeneration = (int)$sunsynkData[2];
$solarBattery = (int)$sunsynkData[1];
$gridProduction = (int)$sunsynkData[0];
if($solarBattery<0){$batteryCharge=abs($solarBattery);}
else{$batteryCharge=0;}
if($gridProduction<0){$gridExport=abs($gridProduction);}
else{$gridExport=0;}
$kwh = $solarGeneration * 24 / 1000;
$json_cloud = file_get_contents("/var/www/html/divumwx/jsondata/awc.txt");
$cloud = json_decode($json_cloud, true);
$cloudcover = $cloud['response'][0]['periods'][0]['sky'];
$json = "/var/www/html/divumwx/jsondata/airquality.txt";
$jsonobj = file_get_contents($json);
$arr = json_decode($jsonobj, true);
$pm25 = $arr["current"]["pm2_5"];
$pm10 = $arr["current"]["pm10"];
$co = $arr["current"]["carbon_monoxide"];
$no2 = $arr["current"]["nitrogen_dioxide"];
$so2 = $arr["current"]["sulphur_dioxide"];
$o3 = $arr["current"]["ozone"];
$nh3 = $arr["current"]["ammonia"];
$aod = $arr["current"]["aerosol_optical_depth"];
$dust = $arr["current"]["dust"];
$alder = $arr["current"]["alder_pollen"];
$birch = $arr["current"]["birch_pollen"];
$olive = $arr["current"]["olive_pollen"];
$grass = $arr["current"]["grass_pollen"];
$mugwort = $arr["current"]["mugwort_pollen"];
$ragweed = $arr["current"]["ragweed_pollen"];
?>