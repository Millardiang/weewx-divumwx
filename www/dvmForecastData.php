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

include_once('dvmCombinedData.php');
error_reporting(0); date_default_timezone_set($TZ);
header('Content-type: text/html; charset=UTF-8');
$iconset = "icon2";
$jsonIcon = file_get_contents('jsondata/lookupTable.json');
$iconjson = json_decode($jsonIcon, true);
$json_string  = file_get_contents('jsondata/awd.txt');
$forecastjson  = json_decode($json_string,true);
for ($k = 0; $k <= 10; $k++) 
{
$forecastIsDay[$k] = $forecastjson["response"][0]["periods"][$k]["isDay"];
$forecastTimeStamp[$k] = $forecastjson["response"][0]["periods"][$k]["timestamp"];
$forecastTime[$k] = date('D', $forecastTimeStamp[$k]);$forecastWeather[$k] = $forecastjson["response"][0]["periods"][$k]["weatherPrimary"];
$forecastTempMaxC[$k] = $forecastjson["response"][0]["periods"][$k]["maxTempC"];
$forecastTempMaxF[$k] = $forecastjson["response"][0]["periods"][$k]["maxTempF"];
$forecastTempMinC[$k] = $forecastjson["response"][0]["periods"][$k]["minTempC"];
$forecastTempMinF[$k] = $forecastjson["response"][0]["periods"][$k]["minTempF"];
if($forecastIsDay[$k]==0)
{$forecastTime[$k] = $forecastTime[$k]." Night";
$forecastTempMaxF[$k]=$forecastTempMinF[$k];
$forecastTempMaxC[$k]=$forecastTempMinC[$k];
$forecastTime[0] = "Tonight";
$forecastTime[1] = "Tomorrow";
$forecastTime[2] = "Tom. Night";
$forecastTime[3] = date('D', $forecastTimeStamp[3]); 
}
else
{$forecastTime[0] = "Today";
$forecastTime[1] = "Tonight";
$forecastTime[2] = "Tomorrow";
$forecastTime[3] = "Tom. Night";}

if ($tempunit == 'F')
    {
        $forecastTempMax[$k] = round($forecastTempMaxF[$k], 0);
    }
else if ($tempunit == 'C')
    {
        $forecastTempMax[$k] = round($forecastTempMaxC[$k], 0);
    }
if($tempunit=='F' && $forecastTempMaxF[$k]<44.6 || $tempunit=='C' && $forecastTempMaxC[$k]<7 ){$tempColor[$k]="bluet";}
else if($tempunit=='F' && $forecastTempMaxF[$k]>104 || $tempunit=='C' && $forecastTempMaxC[$k]>40 ){$tempColor[$k]="purplet";}
else if($tempunit=='F' && $forecastTempMaxF[$k]>80.6 || $tempunit=='C' && $forecastTempMaxC[$k]>27 ){$tempColor[$k]="redt";}
else if($tempunit=='F' && $forecastTempMaxF[$k]>64.6 || $tempunit=='C' && $forecastTempMaxC[$k]>18){$tempColor[$k]="oranget";}	
else if($tempunit=='F' && $forecastTempMaxF[$k]>55 || $tempunit=='C' && $forecastTempMaxC[$k]>12.7){$tempColor[$k]="yellowt";}
else if($tempunit=='F' && $forecastTempMaxF[$k]>=44.6 || $tempunit=='C' && $forecastTempMaxC[$k]>=7){$tempColor[$k]="greent";}

$pngicon[$k] = $forecastjson["response"][0]["periods"][$k]["icon"];
$forecastIcon[$k] = '<img src="img/meteocons/'.$iconjson[$pngicon[$k]][$iconset].'" width="30px" ></img>';

$forecastHumid[$k] = $forecastjson["response"][0]["periods"][$k]["humidity"];
$forecastPrecipMM[$k] = $forecastjson["response"][0]["periods"][$k]["precipMM"];
$forecastPrecipIN[$k] = $forecastjson["response"][0]["periods"][$k]["precipIN"];
    if ($rainunit == 'mm')
    {
        $forecastPrecip[$k] = $forecastPrecipMM[$k];
    }
else if ($rainunit == 'in')
    {
        $forecastPrecip[$k] = $forecastPrecipIN[$k];
    }
$forecastWindDirMax[$k] = $forecastjson["response"][0]["periods"][$k]["windDirMax"];
$forecastWindDirMaxDEG[$k] = $forecastjson["response"][0]["periods"][$k]["windDirMaxDEG"];
$forecastWindSpeedKTS[$k] = $forecastjson["response"][0]["periods"][$k]["windSpeedKTS"];
$forecastWindSpeedKPH[$k] = $forecastjson["response"][0]["periods"][$k]["windSpeedKPH"];
$forecastWindSpeedMPH[$k] = $forecastjson["response"][0]["periods"][$k]["windSpeedMPH"];
$forecastWindSpeedMPS[$k] = $forecastjson["response"][0]["periods"][$k]["windSpeedMPS"];
$forecastWindGustKTS[$k] = $forecastjson["response"][0]["periods"][$k]["windGustKTS"];
$forecastWindGustKPH[$k] = $forecastjson["response"][0]["periods"][$k]["windGustKPH"];
$forecastWindGustMPH[$k] = $forecastjson["response"][0]["periods"][$k]["windGustMPH"];
$forecastWindGustMPS[$k] = $forecastjson["response"][0]["periods"][$k]["windGustMPS"];
    if ($windunit == 'm/s')
    {
        $forecastWindGust[$k] = $forecastWindGustMPS[$k];        
    }
else if ($windunit == 'mph')
    {
        $forecastWindGust[$k] = $forecastWindGustMPH[$k];
    }
else if ($windunit == 'kts')
    {
        $forecastWindGust[$k] = $forecastWindGustKTS[$k];
    }
else  if ($windunit == 'km/h')
    {
        $forecastWindGust[$k] = $forecastWindGustKPH[$k];
    }
$forecastSky[$k] = $forecastjson["response"][0]["periods"][$k]["sky"];
$forecastCloudsCoded[$k] = $forecastjson["response"][0]["periods"][$k]["cloudsCoded"];
$forecastVisibilityKM[$k] = $forecastjson["response"][0]["periods"][$k]["visibilityKM"];
$forecastVisibilityMI[$k] = $forecastjson["response"][0]["periods"][$k]["visibilityMI"];
$forecastUVI[$k] = $forecastjson["response"][0]["periods"][$k]["uvi"];
    if ($windunit == 'mph')
    {
        $forecastVisibility[$k] = round(number_format($forecastVisibilityMI[$k] , 0));
    }
else
    {   
        $forecastVisibility[$k] = round(number_format($forecastVisibilityKM[$k] , 0));
    }
$forecastSnowCM[$k] = $forecastjson["response"][0]["periods"][$k]["snowCM"];
$forecastSnowIN[$k] = $forecastjson["response"][0]["periods"][$k]["snowIN"];
if ($tempunit=="F") {$forecastSnow[$k]=$forecastSnowIN[$k];$snowunit="in";}
else {$forecastSnow[$k]=$forecastSnowCM[$k];$snowunit="cm";}
//temp color
//if($weewxrt[2]<= -10){$colorOutTemp = "#8781bd";}
if($forecastTempMaxC[$k]<=0){$colorOutTemp = "#487ea9";}
else if($forecastTempMaxC[$k]<=5){$colorOutTemp = "#3b9cac";}
else if($forecastTempMaxC[$k]<10){$colorOutTemp = "#9aba2f";}
else if($forecastTempMaxC[$k]<20){$colorOutTemp = "#e6a141";}
else if($forecastTempMaxC[$k]<25){$colorOutTemp = "#ec5a34";}
else if($forecastTempMaxC[$k]<30){$colorOutTemp = "#d05f2d";}
else if($forecastTempMaxC[$k]<35){$colorOutTemp = "#d65b4a";}
else if($forecastTempMaxC[$k]<40){$colorOutTemp = "#dc4953";}
else if($forecastTempMaxC[$k]<100){$colorOutTemp = "#e26870";}

//wind gust color
if($forecastWindGustMPS[$k] <= 2){$color["windGust"]="#7e98bb";}
else if($forecastWindGustMPS[$k] <= 3){$color["windGust"]="#6e90d0";}
else if($forecastWindGustMPS[$k] <= 5){$color["windGust"]="#0f94a7";}
else if($forecastWindGustMPS[$k] <= 8){$color["windGust"]="#39a239";}
else if($forecastWindGustMPS[$k] <= 11){$color["windGust"]="#c2863e";}
else if($forecastWindGustMPS[$k] <= 14){$color["windGust"]="#c8420d";}
else if($forecastWindGustMPS[$k] <= 17){$color["windGust"]="#d20032";}
else if($forecastWindGustMPS[$k] <= 21){$color["windGust"]="#af5088";}
else if($forecastWindGustMPS[$k] <= 24){$color["windGust"]="#754a92";}
else if($forecastWindGustMPS[$k] <= 28){$color["windGust"]="#45698d";}
else if($forecastWindGustMPS[$k] <= 32){$color["windGust"]="#c1fc77";}
else if($forecastWindGustMPS[$k] <= 100){$color["windGust"]="#f1ff6c";}
//rain
if($forecastPrecipMM[$k] >= 0){$color["rain"]="#3A3D40";}
else if($forecastPrecipMM[$k] >= 1){$color["rain"]="#83818e";}
else if($forecastPrecipMM[$k] >= 5){$color["rain"]="#615884";}
else if($forecastPrecipMM[$k] >= 10){$color["rain"]="#34758e";}
else if($forecastPrecipMM[$k] >= 30){$color["rain"]="#0b8c88";}
else if($forecastPrecipMM[$k] >= 40){$color["rain"]="#359f35";}
else if($forecastPrecipMM[$k] >= 80){$color["rain"]="#a79d51";}
else if($forecastPrecipMM[$k] >= 120){$color["rain"]="#9f7f3a";}
else if($forecastPrecipMM[$k] >= 250){$color["rain"]="#be4c07";}
else if($forecastPrecipMM[$k] >= 500){$color["rain"]="#cf2848";}
else if($forecastPrecipMM[$k] >= 750){$color["rain"]="#af5088";}
else if($forecastPrecipMM[$k] >= 1000){$color["rain"]="#d476a3";}
//humidity
if($forecastHumid[$k]<30){$colorOutHumidity="blue";}
else if($forecastHumid[$k]<60){$colorOutHumidity="green";}
else if($forecastHumid[$k]<100){$colorOutHumidity="red";}
//uv
if($forecastUVI[$k] <= 0){$color["UVI"]="grey";}
else if($forecastUVI[$k] >= 2.9){$color["UVI"]="#6fc77b";}
else if($forecastUVI[$k] >= 5.9){$color["UVI"]="#fed42d";}
else if($forecastUVI[$k] >= 7.9){$color["UVI"]="#fd8620";}
else if($forecastUVI[$k] >=10.9){$color["UVI"]="#fb1215";}
else if($forecastUVI[$k] >= 20){$color["UVI"]="#de257b";}
}
?>
