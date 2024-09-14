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
$airqual["pm_units"] = "μg/㎥";
//$aqSource = "weewx";
//PM10 is particulate matter 10 micrometers or less in diameter, PM25 is particulate matter 2.5 micrometers or less in diameter.
//PM2.5 is generally described as fine particles. By way of comparison, a human hair is about 100 micrometres, so roughly
//40 fine particles could be placed on its width.

//PurpleAir Sensor source
if ($aqSource == "purple") {
$json_string = file_get_contents("jsondata/pu.txt");
$parsed_json = json_decode($json_string, true);
$airqual["pm25"] = $parsed_json["sensor"]["stats"]["pm2.5_24hour"];
$airqual["pm10"] = $parsed_json["sensor"]["pm10.0"];
$airqual["city"] = $parsed_json["sensor"]["name"].$airqual["subtitle"];
}
//WSeeWX Source
else if ($aqSource == "weewx") {
$airqual["pm25"] = $air["24h.rollingavg.pm2_5"];
$airqual["pm10"] = $air["24h.rollingavg.pm10_0"];
$airqual["city"] = $stationlocation.$airqual["subtitle"];
}
//WAQI Source
else if ($aqSource == "waqi") {
$json_string = file_get_contents("jsondata/aq.txt");
$parsed_json = json_decode($json_string, true);
$airqual["pm25"] = $parsed_json["data"]["iaqi"]["pm25"]["v"];
$airqual["pm10"] = $parsed_json["data"]["iaqi"]["pm10"]["v"];
$airqual["city"] = $parsed["data"]["city"]["name"].$airqual["subtitle"];
}
//SDS Source
else if ($aqSource == "sds"){
$json_string = file_get_contents("jsondata/aqiJson.txt");
$parsed_json = json_decode($json_string, true);
$airqual["pm25"] = round($parsed_json['pm25'],1);
$airqual["pm10"] = round($parsed_json['pm10'],1);
$airqual["city"] = $stationlocation.$airqual["subtitle"];
}
//open meteo api source
else if ($aqSource == "openmeteo"){
	$json_string = file_get_contents("jsondata/airquality.txt");
	$parsed_json = json_decode($json_string, true);
	$airqual["pm25"] = round($parsed_json["current"]["pm2_5"],1);
	$airqual["pm10"] = round($parsed_json["current"]["pm10"],1);
	$airqual["city"] = $stationlocation;
	}
	

//Europe EAQI
if ($aqZone == "ei"){
	if ($airqual["pm25"] < 11 ){
		$airqual["image25"] = "./css/aqi/goodair.svg?ver=1.4";
		$airqual["color25"] = "#51F0E6";
		$airqual["text25"] = "Good Air Quality";
		$airqual["priority25"] = 1;
	}
	else if ($airqual["pm25"] < 21){
		$airqual["image25"] = "./css/aqi/goodair.svg?ver=1.4";
		$airqual["color25"] = "#51CBA9";
		$airqual["text25"] = "Fair Air Quality";
		$airqual["priority25"] = 2;
	}
	else if ($airqual["pm25"] < 26){
		$airqual["image25"] = "./css/aqi/modair.svg?ver=1.4";
		$airqual["color25"] = "#F0E640";
		$airqual["text25"] = "Moderate Air Quality";
		$airqual["priority25"] = 3;
	}
	else if ($airqual["pm25"] < 51 ){
		$airqual["image25"] = "./css/aqi/uhair.svg?ver=1.4";
		$airqual["color25"] = "#FF5050";
		$airqual["text25"] = "Poor Air Quality";
		$airqual["priority25"] = 4;
	}
	else if ($airqual["pm25"] < 76 ){
		$airqual["image25"] = "./css/aqi/uhair.svg?ver=1.4";
		$airqual["color25"] = "#960032";
		$airqual["text25"] = "Very Poor Air Quality";
		$airqual["priority25"] = 5;
	}
	else {
		$airqual["image25"] = "./css/aqi/hazair.svg?ver=1.4";
		$airqual["color25"] = "#7d2181";
		$airqual["text25"] = "Extremely Poor Air Quality";
		$airqual["priority25"] = 6;
	}

	if ($airqual["pm10"] < 21){
		$airqual["image10"] = "./css/aqi/goodair.svg?ver=1.4";
		$airqual["color10"] = "#51F0E6";
		$airqual["text10"] = "Good Air Quality";
		$airqual["priority10"] = 1;
	}
	else if ($airqual["pm10"] < 41 ){
		$airqual["image10"] = "./css/aqi/modair.svg?ver=1.4";
		$airqual["color10"] = "#F0E640";
		$airqual["text10"] = "Moderate Air Quality";
		$airqual["priority10"] = 2;
	}
	else if ($airqual["pm10"] < 51 ){
		$airqual["image10"] = "./css/aqi/uhfsair.svg?ver=1.4";
		$airqual["color10"] = "#FF5050";
		$airqual["text10"] = "Poor Air Quality";
		$airqual["priority10"] = 3;
	}
	else if ($airqual["pm10"] < 151 ){
		$airqual["image10"] = "./css/aqi/uhair.svg?ver=1.4";
		$airqual["color10"] = "#960032";
		$airqual["text10"] = "Very Poor Air Quality";
		$airqual["priority10"] = 4;
	}
	else
	{
		$airqual["image10"] = "./css/aqi/hazair.svg?ver=1.4";
		$airqual["color10"] = "#7D2181";
		$airqual["text10"] = "Extremely Poor Air Quality";
		$airqual["priority10"] = 5;
	}
		$airqual["aqi25"] = $airqual["priority25"];
		$airqual["aqi10"] = $airqual["priority10"];
}

//Europe CAQI
if ($aqZone == "ci"){
	if ($airqual["pm25"] < 16 ){
		$airqual["image25"] = "./css/aqi/goodair.svg?ver=1.4";
		$airqual["color25"] = "#7ABC6A";
		$airqual["text25"] = "Very Low Air Pollution";
		$airqual["priority25"] = 1;
	}
	else if ($airqual["pm25"] < 31){
		$airqual["image25"] = "./css/aqi/goodair.svg?ver=1.4";
		$airqual["color25"] = "#BBCF4C";
		$airqual["text25"] = "Low Air Pollution";
		$airqual["priority25"] = 2;
	}
	else if ($airqual["pm25"] < 56){
		$airqual["image25"] = "./css/aqi/modair.svg?ver=1.4";
		$airqual["color25"] = "#EEC209";
		$airqual["text25"] = "Medium Air Pollution";
		$airqual["priority25"] = 3;
	}
	else if ($airqual["pm25"] < 111 ){
		$airqual["image25"] = "./css/aqi/uhair.svg?ver=1.4";
		$airqual["color25"] = "#DB8503";
		$airqual["text25"] = "High Air Pollution";
		$airqual["priority25"] = 4;
	}
	else {
		$airqual["image25"] = "./css/aqi/uhair.svg?ver=1.4";
		$airqual["color25"] = "#E8416F";
		$airqual["text25"] = "Very High Air Pollution";
		$airqual["priority25"] = 5;
	}

	if ($airqual["pm10"] < 25){
		$airqual["image10"] = "./css/aqi/goodair.svg?ver=1.4";
		$airqual["color10"] = "#7ABC6A";
		$airqual["text10"] = "Very Low Air Pollution";
		$airqual["priority10"] = 1;
	}
	else if ($airqual["pm10"] < 50 ){
		$airqual["image10"] = "./css/aqi/modair.svg?ver=1.4";
		$airqual["color10"] = "#BBCF4C";
		$airqual["text10"] = "Low Air Pollution";
		$airqual["priority10"] = 2;
	}
	else if ($airqual["pm10"] < 90 ){
		$airqual["image10"] = "./css/aqi/uhfsair.svg?ver=1.4";
		$airqual["color10"] = "#EEC209";
		$airqual["text10"] = "Medium Air Pollution";
		$airqual["priority10"] = 3;
	}
	else if ($airqual["pm10"] < 180 ){
		$airqual["image10"] = "./css/aqi/uhair.svg?ver=1.4";
		$airqual["color10"] = "#DB8503";
		$airqual["text10"] = "High Air Pollution";
		$airqual["priority10"] = 4;
	}
	else
	{
		$airqual["image10"] = "./css/aqi/hazair.svg?ver=1.4";
		$airqual["color10"] = "#E8416F";
		$airqual["text10"] = "Very High Air Pollution";
		$airqual["priority10"] = 5;
	}
		$airqual["aqi25"] = $airqual["priority25"];
		$airqual["aqi10"] = $airqual["priority10"];
}

//UK AQI
if ($aqZone == "uk"){
	if ($airqual["pm25"] < 12 ){
		$airqual["image25"] = "./css/aqi/goodair.svg?ver=1.4";
		$airqual["color25"] = "#CCFFCC";
		$airqual["text25"] = " Low Pollution";
		$airqual["aqi25"] = "1";
		$airqual["priority25"] = 1;
	}
	else if ($airqual["pm25"] < 24){
		$airqual["image25"] = "./css/aqi/goodair.svg?ver=1.4";
		$airqual["color25"] = "#66FF66";
		$airqual["text25"] = " Low Pollution";
		$airqual["aqi25"] = "2";
		$airqual["priority25"] = 2;
	}
	else if ($airqual["pm25"] < 36){
		$airqual["image25"] = "./css/aqi/goodair.svg?ver=1.4";
		$airqual["color25"] = "#00FF00";
		$airqual["text25"] = " Low Pollution";
		$airqual["aqi25"] = "3";
		$airqual["priority25"] = 3;
	}
	else if ($airqual["pm25"] < 42 ){
		$airqual["image25"] = "./css/aqi/modair.svg?ver=1.4";
		$airqual["color25"] = "#99FF00";
		$airqual["text25"] = " Moderate Pollution";
		$airqual["aqi25"] = "4echo '<sub>PM2.5</sub>'";
		$airqual["priority25"] = 4;
	}
	else if ($airqual["pm25"] < 48 ){
		$airqual["image25"] = "./css/aqi/modair.svg?ver=1.4";
		$airqual["color25"] = "#FFFF00";
		$airqual["text25"] = " Moderate Pollution";
		$airqual["aqi25"] = "5";
		$airqual["priority25"] = 5;
	}
	else if ($airqual["pm25"] < 54 ){
		$airqual["image25"] = "./css/aqi/modair.svg?ver=1.4";
		$airqual["color25"] = "#FFCC00";
		$airqual["text25"] = " Moderate Pollution";
		$airqual["aqi25"] = "6";
		$airqual["priority25"] = 6;
	}
	else if ($airqual["pm25"] < 59 ){
		$airqual["image25"] = "./css/aqi/uhfsair.svg?ver=1.4";
		$airqual["color25"] = "#FF6600";
		$airqual["text25"] = " High Pollution";
		$airqual["aqi25"] = "7";
		$airqual["priority25"] = 7;
	}
	else if ($airqual["pm25"] < 65 ){
		$airqual["image25"] = "./css/aqi/uhair.svg?ver=1.4";
		$airqual["color25"] = "#FF3300";
		$airqual["text25"] = " High Pollution";
		$airqual["aqi25"] = "8";
		$airqual["priority25"] = 8;
	}
	else if ($airqual["pm25"] < 71 ){
		$airqual["image25"] = "./css/aqi/uhair.svg?ver=1.4";
		$airqual["color25"] = "#FF0000";
		$airqual["text25"] = " High Pollution";
		$airqual["aqi25"] = "9";
		$airqual["priority25"] = 9;
	}
	else {
		$airqual["image25"] = "./css/aqi/vhair.svg?ver=1.4";
		$airqual["color25"] = "#FF0066";
		$airqual["text25"] = "Very High Pollution";
		$airqual["aqi25"] = "10";
		$airqual["priority25"] = 10;
	}

	if ($airqual["pm10"] < 17 ){
		$airqual["image10"] = "./css/aqi/goodair.svg?ver=1.4";
		$airqual["color10"] = "#CCFFCC";
		$airqual["text10"] = " Low Pollution";
		$airqual["aqi10"] = "1";
		$airqual["priority10"] = 1;
	}
	else if ($airqual["pm10"] < 34){
		$airqual["image10"] = "./css/aqi/goodair.svg?ver=1.4";
		$airqual["color10"] = "#66FF66";
		$airqual["text10"] = " Low Pollution";
		$airqual["aqi10"] = "2";
		$airqual["priority10"] = 2;
	}
	else if ($airqual["pm10"] < 51){
		$airqual["image10"] = "./css/aqi/goodair.svg?ver=1.4";
		$airqual["color10"] = "#00FF00";
		$airqual["text10"] = " Low Pollution";
		$airqual["aqi10"] = "3";
		$airqual["priority10"] = 3;
	}
	else if ($airqual["pm10"] < 59 ){
		$airqual["image10"] = "./css/aqi/modair.svg?ver=1.4";
		$airqual["color10"] = "#99FF00";
		$airqual["text10"] = " Moderate Pollution";
		$airqual["aqi10"] = "4";
		$airqual["priority10"] = 4;
	}
	else if ($airqual["pm10"] < 67 ){
		$airqual["image10"] = "./css/aqi/modair.svg?ver=1.4";
		$airqual["color10"] = "#FFFF00";
		$airqual["text10"] = " Moderate Pollution";
		$airqual["aqi10"] = "5";
		$airqual["priority10"] = 5;
	}
	else if ($airqual["pm10"] < 76 ){
		$airqual["image10"] = "./css/aqi/modair.svg?ver=1.4";
		$airqual["color10"] = "#FFCC00";
		$airqual["text10"] = " Moderate Pollution";
		$airqual["aqi10"] = "6";
		$airqual["priority10"] = 6;
	}
	else if ($airqual["pm10"] < 84 ){
		$airqual["image10"] = "./css/aqi/uhfsair.svg?ver=1.4";
		$airqual["color10"] = "#FF6600";
		$airqual["text10"] = " High Pollution";
		$airqual["aqi10"] = "7";
		$airqual["priority10"] = 7;
	}
	else if ($airqual["pm10"] < 92 ){
		$airqual["image10"] = "./css/aqi/uhair.svg?ver=1.4";
		$airqual["color10"] = "#FF3300";
		$airqual["text10"] = " High Pollution";
		$airqual["aqi10"] = "8";
		$airqual["priority10"] = 8;
	}
	else if ($airqual["pm10"] < 101 ){
		$airqual["image10"] = "./css/aqi/uhair.svg?ver=1.4";
		$airqual["color10"] = "#FF0000";
		$airqual["text10"] = " High Pollution9";
		$airqual["aqi10"] = "9";
		$airqual["priority10"] = 9;
	}
	else {
		$airqual["image10"] = "./css/aqi/vhair.svg?ver=1.4";
		$airqual["color10"] = "#FF0066";
		$airqual["text10"] = "Very High Pollution";
		$airqual["aqi10"] = "10";
		$airqual["priority10"] = 10;
	}
}

//USA & WAQI
if ($aqZone == "us"){

function pm25_to_aqi($pm25){
	if ($pm25 > 500.5) {
	  $aqi25 = 500;
	} else if ($pm25 > 350.5 && $pm25 <= 500.5 ) {
	  $aqi25 = map($pm25, 350.5, 500.5, 400, 500);
	} else if ($pm25 > 250.5 && $pm25 <= 350.5 ) {
	  $aqi25 = map($pm25, 250.5, 350.5, 300, 400);
	} else if ($pm25 > 150.5 && $pm25 <= 250.5 ) {
	  $aqi25 = map($pm25, 150.5, 250.5, 200, 300);
	} else if ($pm25 > 55.5 && $pm25 <= 150.5 ) {
	  $aqi25 = map($pm25, 55.5, 150.5, 150, 200);
	} else if ($pm25 > 35.5 && $pm25 <= 55.5 ) {
	  $aqi25 = map($pm25, 35.5, 55.5, 100, 150);
	} else if ($pm25 > 12 && $pm25 <= 35.5 ) {
	  $aqi25 = map($pm25, 12, 35.5, 50, 100);
	} else if ($pm25 > 0 && $pm25 <= 12 ) {
	  $aqi25 = map($pm25, 0, 12, 0, 50);
	}
	return $aqi25;
}

function pm10_to_aqi($pm10){
	if ($pm10 > 604) {
	  $aqi10 = 500;
	} else if ($pm10 > 505 && $pm10 <= 604 ) {
	  $aqi10 = map($pm10, 505, 600.4, 400, 500);
	} else if ($pm10 > 424 && $pm10 <= 504 ) {
	  $aqi10 = map($pm10, 425, 504, 300, 400);
	} else if ($pm10 > 354 && $pm10 <= 424 ) {
	  $aqi10 = map($pm10, 355, 424, 200, 300);
	} else if ($pm10 > 254 && $pm10 <= 354 ) {
	  $aqi10 = map($pm10, 255, 354, 150, 200);
	} else if ($pm10 > 154 && $pm10 <= 254 ) {
	  $aqi10 = map($pm10, 155, 254, 100, 150);
	} else if ($pm10 > 54 && $pm10 <= 154 ) {
	  $aqi10 = map($pm10, 55, 154, 50, 100);
	} else if ($pm10 > 0 && $pm10 <= 54 ) {
	  $aqi10 = map($pm10, 0, 54, 0, 50);
	}
	return $aqi10;
}

function map($value, $fromLow, $fromHigh, $toLow, $toHigh){
    $fromRange = $fromHigh - $fromLow;
    $toRange = $toHigh - $toLow;
    $scaleFactor = $toRange / $fromRange;

    // Re-zero the value within the from range
    $tmpValue = $value - $fromLow;
    // Rescale the value to the to range
    $tmpValue *= $scaleFactor;
    // Re-zero back to the to range
    return $tmpValue + $toLow;
}

$airqual["aqi25"] = number_format($airqual["aqi25"],1);
$airqual["aqi25"] = pm25_to_aqi($airqual["pm25"]);
$airqual["aqi10"] = number_format($airqual["aqi10"]);
$airqual["aqi10"] = pm10_to_aqi($airqual["pm10"]);

if ($airqual["aqi25"] < 51 ){
$airqual["image25"] = "./css/aqi/goodair.svg?ver=1.4";
$airqual["color25"] = "#00e400";
$airqual["text25"] = "Good Air Quality";
$airqual["priority25"] = 1;

}
else if ($airqual["aqi25"] < 101){
$airqual["image25"] = "./css/aqi/modair.svg?ver=1.4";
$airqual["color25"] = "#ffff00";
$airqual["text25"] = "Moderate Air Quality";
$airqual["priority25"] = 2;

}
else if ($airqual["aqi25"] < 151 ){
$airqual["image25"] = "./css/aqi/uhfsair.svg?ver=1.4";
$airqual["color25"] = "#ff7e00";
$airqual["text25"] = "Unhealthy for Sensitive Groups";
$airqual["priority25"] = 3;

}
else if ($airqual["aqi25"] < 201 ){
$airqual["image25"] = "./css/aqi/uhair.svg?ver=1.4";
$airqual["color25"] = "#ff0000";
$airqual["text25"] = "Unhealthy Air Quality";
$airqual["priority25"] = 4;

}
else if ($airqual["aqi25"] < 301 ){
$airqual["image25"] = "./css/aqi/vhair.svg?ver=1.4";
$airqual["color25"] = "#8f3f97";
$airqual["text25"] = "Very Unhealthy Air Quality";
$airqual["priority25"] = 5;
}
else {
$airqual["image25"] = "./css/aqi/hazair.svg?ver=1.4";
$airqual["color25"] = "#7e0023";
$airqual["text25"] = "Hazardous Air Quality";
$airqual["priority25"] = 6;
}

if ($airqual["aqi10"] < 55 ){
    $airqual["image10"] = "./css/aqi/goodair.svg?ver=1.4";
    $airqual["color10"] = "#00e400";
    $airqual["text10"] = "Good Air Quality";
    $airqual["priority10"] = 1;
    }
    else if ($airqual["aqi10"] < 155){
    $airqual["image10"] = "./css/aqi/modair.svg?ver=1.4";
    $airqual["color10"] = "#ffff00";
    $airqual["text10"] = "Moderate Air Quality";
    $airqual["priority10"] = 2;
    }
    else if ($airqual["aqi10"] < 255 ){
    $airqual["image10"] = "./css/aqi/uhfsair.svg?ver=1.4";
    $airqual["color10"] = "#ff7e00";
    $airqual["text10"] = "Unhealthy for Sensitive Groups";
    $airqual["priority10"] = 3;
    }
    else if ($airqual["aqi10"] < 355 ){
    $airqual["image10"] = "./css/aqi/uhair.svg?ver=1.4";
    $airqual["color10"] = "#ff0000";
    $airqual["text10"] = "Unhealthy Air Quality";
    $airqual["priority10"] = 4;
    }
    else if ($airqual["aqi10"] < 425 ){
    $airqual["image25"] = "./css/aqi/vhair.svg?ver=1.4";
    $airqual["color25"] = "#8f3f97";
    $airqual["text25"] = "Very Unhealthy Air Quality";
    $airqual["priority25"] = 5;
    }
    else {
    $airqual["image25"] = "./css/aqi/hazair.svg?ver=1.4";
    $airqual["color25"] = "#7e0023";
    $airqual["text25"] = "Hazardous Air Quality";
    $airqual["priority25"] = 6;
    }
    
}

//Australia AQI
if ($aqZone == "au"){
	$airqual["aqi25"] = round($airqual["pm25"]*4, 0);
	if ($airqual["aqi25"] < 34 ){
		$airqual["image25"] = "./css/aqi/goodair.svg?ver=1.4";
		$airqual["color25"] = "#32ADD3";
		$airqual["text25"] = "Very Good Air Quality";
		$airqual["priority25"] = 1;
	}
	else if ($airqual["aqi25"] < 67){
		$airqual["image25"] = "./css/aqi/goodair.svg?ver=1.4";
		$airqual["color25"] = "#99B964";
		$airqual["text25"] = "Good Air Quality";
		$airqual["priority25"] = 2;
	}
	else if ($airqual["aqi25"] < 100 ){
		$airqual["image25"] = "./css/aqi/modhair.svg?ver=1.4";
		$airqual["color25"] = "#FFD235";
		$airqual["text25"] = "Fair Air Quality";
		$airqual["priority25"] = 3;
	}
	else if ($airqual["aqi25"] < 150 ){
		$airqual["image25"] = "./css/aqi/uhair.svg?ver=1.4";
		$airqual["color25"] = "#EC783A";
		$airqual["text25"] = "Poor Air Quality";
		$airqual["priority25"] = 4;
	}
	else if ($airqual["aqi25"] < 200 ){
		$airqual["image25"] = "./css/aqi/vhair.svg?ver=1.4";
		$airqual["color25"] = "#782D49";
		$airqual["text25"] = "Very Poor Air Quality";
		$airqual["priority25"] = 5;
	}
	else {
		$airqual["image25"] = "./css/aqi/hazair.svg?ver=1.4";
		$airqual["color25"] = "#D04730";
		$airqual["text25"] = "Hazardous Air Quality";
		$airqual["priority25"] = 6;
	}
	$airqual["aqi10"] = round($airqual["pm10"]*2, 0);
	if ($airqual["aqi10"] < 34 ){
		$airqual["image10"] = "./css/aqi/goodair.svg?ver=1.4";
		$airqual["color10"] = "#32ADD3";
		$airqual["text10"] = "Very Good Air Quality";
		$airqual["priority10"] = 1;
		}
	else if ($airqual["aqi10"] < 67){
		$airqual["image10"] = "./css/aqi/goodair.svg?ver=1.4";
		$airqual["color10"] = "#99B964";
		$airqual["text10"] = "Good Air Quality";
		$airqual["priority10"] = 2;
	}
	else if ($airqual["aqi10"] < 100 ){
		$airqual["image10"] = "./css/aqi/modhair.svg?ver=1.4";
		$airqual["color10"] = "#FFD235";
		$airqual["text10"] = "Fair Air Quality";
		$airqual["priority10"] = 3;
	}
	else if ($airqual["aqi10"] < 150 ){
		$airqual["image10"] = "./css/aqi/uhair.svg?ver=1.4";
		$airqual["color10"] = "#EC783A";
		$airqual["text10"] = "Poor Air Quality";
		$airqual["priority10"] = 4;
	}
	else if ($airqual["aqi10"] < 200 ){
		$airqual["image10"] = "./css/aqi/vhair.svg?ver=1.4";
		$airqual["color10"] = "#782D49";
		$airqual["text10"] = "Very Poor Air Quality";
		$airqual["priority10"] = 5;
	}
	else {
		$airqual["image10"] = "./css/aqi/hazair.svg?ver=1.4";
		$airqual["color10"] = "#D04730";
		$airqual["text10"] = "Hazardous Air Quality";
		$airqual["priority10"] = 6;
	}
}

if ($airqual["priority25"] > $airqual["priority10"])
	{$airqual["text"] = $airqual["text25"];
	$airqual["qualColor"] = $airqual["color25"];
}
else {$airqual["text"] = $airqual["text10"];
	$airqual["qualColor"] = $airqual["color10"];
}

?>

    <div class="chartforecast2">
      

      <span class="yearpopup" style="background-colr: red" ><a alt="airquality charts" title="Airquality Charts" href="dvmhighcharts/dvmAirQualityWeekChart.php" data-lity><?php echo $menucharticonpage;?> Airquality Charts and Information</a></span>
      

    </div>
    <span class='moduletitle2'><?php echo $lang['airqualityModule'];?></span>


<div class="updatedtime1"><?php if(file_exists($livedata)&&time() - filemtime($livedata)>300) echo $offline. '<offline> Offline </offline>'; else echo $online.' '.date($timeFormat);?></div>

<script src="js/d3.v3.min.js"></script>
<html>
<style>

.aqi {
  position: relative; 
  margin-top: -1.5px; 
  margin-left: 0px;
}

</style>

<script>
var theme = "<?php echo $theme;?>";
	if (theme == 'dark') {
var cityTextFill = "silver";}
else
{var cityTextFill = "rgba(85,85,85,1)";}
</script>

<div class="aqi"></div>
<div id="svg"></div>

<script>

	var aqiA = "<?php echo round($airqual["aqi25"]);?>";
	var aqiB = "<?php echo round($airqual["aqi10"]);?>";
	var pmA = "<?php echo $airqual["pm25"];?>";
	var pmB = "<?php echo $airqual["pm10"];?>";
      
	var city = "<?php echo $airqual["city"];?>";
	
	var qualityA = "<?php echo $airqual["text"];?>";
	var qualityB = "<?php echo $airqual["text"];?>";
	  	
	var colorA = "<?php echo $airqual["color25"];?>";
	var colorB = "<?php echo $airqual["color10"];?>"; 
    var colorQ = "<?php echo $airqual["qualColor"];?>";
		
	var imageA = "<?php echo $airqual["image25"];?>";
	var imageB = "<?php echo $airqual["image10"];?>";
	
	
   	var svg = d3.select(".aqi")
                .append("svg")
                //.style("background", "#292E35")
                .attr("width", 300)
                .attr("height", 150);

	
            svg.append("text") // City text output
             	.attr("x", 150)
            	.attr("y", 20)
            	.style("fill", cityTextFill)
            	.style("font-family", "Helvetica")
            	.style("font-size", "12px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "normal")   				
   				.text(city);

           svg.append("foreignObject")
     			.attr("x", 70)
              	.attr("y", 30)
    			.attr("width", 50)
    			.attr("height", 25)    			
    			.style("fill", "silver")
    			.style("font-size", "10px")
    			.style("text-anchor", "middle")
              	.style("font-weight", "normal")
              	.append("xhtml:div")          
   				.html("<p>PM<sub>2.5</sub></p>");

			svg.append("foreignObject")
     			.attr("x", 220)
              	.attr("y", 30)
    			.attr("width", 50)
    			.attr("height", 25)   			
    			.style("fill", "silver")
    			.style("font-family", "Helvetica")
    			.style("font-size", "10px")
    			.style("text-anchor", "middle")
              	.style("font-weight", "normal")
              	.append("xhtml:div")          
    			.html("<p>PM<sub>10</sub></p>");


   			 svg.append("line") // horizontal lozenge left
    			.attr("x1", 82)
    			.attr("x2", 135)
    			.attr("y1", 104)
    			.attr("y2", 104)
    			.style("stroke", "silver")
    			.style("stroke-width", "12px")
    			.style("stroke-linecap", "round"); 

    		svg.append("line") // horizontal lozenge right
    			.attr("x1", 234)
    			.attr("x2", 287)
    			.attr("y1", 104)
    			.attr("y2", 104)
    			.style("stroke", "silver")
    			.style("stroke-width", "12px")
    			.style("stroke-linecap", "round");

			 svg.append("text") // pm 2.5 micro gram text output
             	.attr("x", 108.5)
            	.attr("y", 107)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "10px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "bold")
   				.text(d3.format(".1f")(pmA)+" "+"μg/m³");

   			svg.append("text") // pm 10 micro gram text output
             	.attr("x", 261)
            	.attr("y", 107)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "10px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "bold")
   				.text(d3.format(".1f")(pmB)+" "+"μg/m³");


      		// begin pm 2.5
			svg.append("circle")
            	.attr("cx", 50) // main circle
            	.attr("cy", 75)
            	.attr("r", 32)
            	.style('stroke', colorA)
            	.style('fill', colorA);
	 
            svg.append("rect") // fill rectangle
    			.attr("x", 69)
    			.attr("y", 54.5)    			
    			.attr("height", 41)
    			.attr("width", 40)
    			.style("fill", colorA);
    				              		 
           	svg.append("circle")
            	.attr("cx", 110) // side circle
            	.attr("cy", 75)
            	.attr("r", 20)
            	.style('stroke', colorA)
            	.style('fill', colorA);
            		 
            svg.append("circle")
            	.attr("cx", 50) // center dark ring
            	.attr("cy", 75)
            	.attr("r", 34)
            	.style('stroke', '#1e2024')
            	.style('fill', 'none')
            	.style('stroke-width', 3);		             		 
            		             		 
            svg.append('image') // image output
    			.attr('xlink:href', imageA)
    			.attr('width', 110)
    			.attr('height', 100)
    			.attr('x', -4)
    			.attr('y', 24);
    				
    		svg.append("text") // Air Quality text output
             	.attr("x", 150)
            	.attr("y", 135)
            	.style("fill", colorQ)
            	.style("font-family", "Helvetica")
            	.style("font-size", "12px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "normal")
   				.text(qualityA);
   				
			svg.append("text") // AQ Index text output
             	.attr("x", 106)
            	.attr("y", 79)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "10px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "bold")
   				.text("AQI"+" "+(aqiA));
			           		             		 
			// begin pm 10		
			svg.append("circle")
            	.attr("cx", 202) // main circle
            	.attr("cy", 75)
            	.attr("r", 32)
            	.style('stroke', colorB)
            	.style('fill', colorB);
            		 
            svg.append("rect") // fill rectangle
    			.attr("x", 220)
    			.attr("y", 54.5)
    			.attr("rx", 0)    			
    			.attr("height", 41)
    			.attr("width", 40)
    			.style("fill", colorB);
    				              		 
           	svg.append("circle")
            	.attr("cx", 261) // side circle
            	.attr("cy", 75)
            	.attr("r", 20)
            	.style('stroke', colorB)
            	.style('fill', colorB);
           		 
            svg.append("circle")
            	.attr("cx", 202) // center dark ring
            	.attr("cy", 75)
            	.attr("r", 34)
            	.style('stroke', '#1e2024')
            	.style('fill', 'none')
            	.style('stroke-width', 3);		             		 
             		             		 
            svg.append('image') // image output
    			.attr('xlink:href', imageB)
    			.attr('width', 110)
    			.attr('height', 100)
    			.attr('x', 148)
    			.attr('y', 24);
    				   				
   			svg.append("text") // AQ Index text output
             	.attr("x", 258)
            	.attr("y", 79)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "10px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "bold")
   				.text("AQI"+" "+(aqiB));
		          
</script>
</html>