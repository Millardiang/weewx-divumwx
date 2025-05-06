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

include ('userSettings.php');
include ('dvmVersion.php');
error_reporting(0);
$TZconf = $TZ; // PLEASE LEAVE it is fixed
$UTC_offset = timezone_offset_get(timezone_open($TZ), new DateTime()) / 3600; // DO NOT CHANGE
$forecastlocation = $stationlocation; //
$emailform = $email; // PLEASE LEAVE FIXED
$theme = $_COOKIE["theme"];
 if($theme == "dark"){$reverseTheme="light";}
  else if($theme == "light"){$reverseTheme="dark";}
$charttheme = $theme;
$livedata = "serverdata/dvmRealtime.txt";
$chartsource =  "dvmhighcharts";
$position99 = "filepileTextCreate.php";
$filepileRefresh = 60;
// Updater timing cycles
$json_string = file_get_contents("jsondata/dvmPositionCycles.json");
$parsed_json = json_decode($json_string, true);
$cycles1 = $parsed_json[$position1]["cycle"];
$cycles2 = $parsed_json[$position2]["cycle"];
$cycles3 = $parsed_json[$position3]["cycle"];
$cycles4 = $parsed_json[$position4]["cycle"];
$cycles5 = $parsed_json[$position5]["cycle"];
$cycles6 = $parsed_json[$position6]["cycle"];
$cycles7 = $parsed_json[$position7]["cycle"];
$cycles8 = $parsed_json[$position8]["cycle"]; 
$cycles9 = $parsed_json[$position9]["cycle"];
$cycles10 = $parsed_json[$position10]["cycle"];
$cycles11 = $parsed_json[$position11]["cycle"];
$cycles12 = $parsed_json[$position12]["cycle"];
$cycles13 = $parsed_json[$position13]["cycle"];
$cycles14 = $parsed_json[$position14]["cycle"];
$cycles15 = $parsed_json[$position15]["cycle"];
$cycles16 = $parsed_json[$position16]["cycle"];
$cycles17 = $parsed_json[$position17]["cycle"];
$cycles18 = $parsed_json[$position18]["cycle"];
$cycles19 = $parsed_json[$position19]["cycle"];
$cycles20 = $parsed_json[$position20]["cycle"];
$cycles21 = $parsed_json[$position21]["cycle"];
$cycles22 = $parsed_json[$position22]["cycle"];
$cycles23 = $parsed_json[$position23]["cycle"];
$cycles24 = $parsed_json[$position24]["cycle"];
$cycles25 = $parsed_json[$position25]["cycle"];
$cycles99 = $parsed_json[$position99]["cycle"];

//$copyYear = 2023;
$curYear = date("Y");
$copyrightcredit =
    "&copy; DivumWX Team " .
    $copyYear .
    ($copyYear != $curYear ? "-" . $curYear : "Copyright");

//$moonRefresh = 3600;
$scriptcredits =
    "Original CSS/SVG ICONS/PHP scripts by <a href='https://divumwx.com' title='divumwx.com' target='_blank'>divumwx.com 2015 - " .
    date("Y") .
    ""; 

$chartsource = "dvmhighcharts";
$software = "WeeWX <span>Hardware</span> Users";
$designedfor = "<br>For WeeWX Users";

if (
    array_key_exists("theme", $_GET) &&
    ($_GET["theme"] == "dark" || $_GET["theme"] == "light")
) {
    SetCookie("theme", $_GET["theme"], time() + 15552000);
    $theme = $_GET["theme"];
} elseif (
    array_key_exists("theme", $_COOKIE) &&
    ($_COOKIE["theme"] == "dark" || $_COOKIE["theme"] == "light")
) {
    $theme = $_COOKIE["theme"];
}
$units = "";
if (array_key_exists("units", $_COOKIE)) {
    $units = $_COOKIE["units"];
}
ini_set("session.use_cookies", "0");
if (
    array_key_exists("units", $_GET) &&
    ($_GET["units"] == "default" || $_GET["units"] == "")
) {
    SetCookie("units", $_GET["units"], time() - 86400); //86400 = 1 day, negative time erases cookie
    $units = "";
} elseif (
    array_key_exists("units", $_GET) &&
    ($_GET["units"] == "us" ||
        $_GET["units"] == "ushpa" ||
        $_GET["units"] == "ca" ||
        $_GET["units"] == "uk" ||
        $_GET["units"] == "metric" ||
        $_GET["units"] == "scandinavia" ||
        $_GET["units"] == "kts")
) {
    SetCookie("units", $_GET["units"], time() + 15552000);
    $units = $_GET["units"];
}
if ($units == "uk") {
    $cloudcoverunit = "%";
    $windunit = "mph";
    $tempunit = "C";
    $rainunit = "mm";
    $pressureunit = "mbar";
    $distanceunit = "mi"; 
    $windconv = "0.621371";
    $rainfallconv = "10";
    $pressureinterval = "0.5";
    $rainfallconvmm = "10"; 
} elseif ($units == "scandinavia") {
    $windunit = "m/s";
    $tempunit = "C";
    $rainunit = "mm";
    $pressureunit = "hPa";
    $distanceunit = "km"; 
    $windconv = "0.277778";
    $rainfallconv = "10";
    $pressureinterval = "0.5";
    $rainfallconvmm = "10";
} elseif ($units == "metric") {
    $windunit = "km/h";
    $tempunit = "C";
    $rainunit = "mm";
    $pressureunit = "hPa";
    $distanceunit = "km"; 
    $windconv = "1";
    $rainfallconv = "10";
    $pressureinterval = "0.5";
    $rainfallconvmm = "10";
} elseif ($units == "ca") {
    $windunit = "km/h";
    $tempunit = "C";
    $rainunit = "mm";
    $pressureunit = "kPa";
    $distanceunit = "km"; 
    $windconv = "1";
    $rainfallconv = "10";
    $pressureinterval = "0.5";
    $rainfallconvmm = "10";
} elseif ($units == "us") {
    $windunit = "mph";
    $tempunit = "F";
    $rainunit = "in";
    $pressureunit = "inHg";
    $distanceunit = "mi"; 
    $windconv = "1";
    $rainfallconv = "1";
    $pressureinterval = "0.5";
    $rainfallconvmm = "1";
} elseif ($units == "ushpa") {
    $windunit = "mph";
    $tempunit = "F";
    $rainunit = "in";
    $pressureunit = "hPa";
    $distanceunit = "mi"; 
    $windconv = "1";
    $rainfallconv = "1";
    $pressureinterval = "0.5";
    $rainfallconvmm = "1";
} elseif ($units == "kts") {
    $windunit = "kts";
    $tempunit = "C";
    $rainunit = "mm";
    $pressureunit = "hPa";
    $distanceunit = "km"; 
    $windconv = "0.5399570136727677";
    $rainfallconv = "10";
    $pressureinterval = "0.5";
    $rainfallconvmm = "10";
}

if ($advisoryzone == "uk") {
    $advisory = "dvmAdvisoryUkPopup.php";
}
else if ($advisoryzone == "eu") {
    $advisory = "dvmAdvisoryEuPopup.php";
}

else if ($advisoryzone == "au") {
    $advisory = "dvmAdvisoryAuPopup.php";
} 
else if ($advisoryzone == "na") {
    $advisory = "dvmAdvisoryNaPopup.php";
}

if ($aqZone == "uk") {
    $airqual["subtitle"] = " - UK DAQI";
}
else if ($aqZone == "ei") {
    $airqual["subtitle"] = " - Europe EAQI";
}

else if ($aqZone == "ci") {
    $airqual["subtitle"] = " - Europe CAQI";
}
else if ($aqZone == "au") {
    $airqual["subtitle"] = " - Australian AQI";
} 
else if ($aqZone == "us") {
    $airqual["subtitle"] = " - USA EPA";
}

?>