<?php
include ('userSettings.php');
include ('dvmVersion.php');
error_reporting(0);

###########################################################################
# THE SETTINGS BELOW MUST BE LEFT UNTOUCHED UNLESS YOU REALLY NEED  #######
# TO MAKE THE CHANGES HERE TO MAKE IT WORK ON YOUR SERVER// ###############
# TAKE THE TIME TO STUDY THEM DONT TAKE IT FOR GRANTED      ###############
# USE THE EASY SETUP PANEL TO MAKE CHANGES IN THE TEMPLATE  ###############
###########################################################################

$TZconf = $TZ; // PLEASE LEAVE it is fixed
$UTC_offset = timezone_offset_get(timezone_open($TZ), new DateTime()) / 3600; // DO NOT CHANGE
$rise_zenith = 90 + 40 / 60; // try 50/60 or something/60 until it matches correctly to your sunrise .this allows you to fine tune the sunrise
$set_zenith = 90 + 36 / 60; // try 50/60 or something/60 until it matches correctly to your sunset .this allows you to fine tune the sunset
$forecastlocation = $stationlocation; //
$version = $livedataFormat; // template version and type of source: Clientraw, MeteoBridge, Cumulus, etc (for display only)
$emailform = $email; // PLEASE LEAVE FIXED
$showFeelsLike = true; // whether to always show either the heat index (when temp > 80F/27C) or real feel (when temp between 50F/10C and 80F/27C) even when no concern
$lightLeft = true; // shows amount of light/darkness left rather than total amount per day in the moon display

$theme = isset($theme) ? $theme : "dark";
$theme1 = $theme;

$charttheme = $theme;
$livedata = "serverdata/dvmRealtime.txt";
$chartsource =  "dvmhighcharts"; 
####################################################################################################
// Refresh Data Main Page  //																	   #
// Automatic refresh times (in seconds) of each panel on the main dashboard						   #
####################################################################################################
// Updater timing cycles

$json_string = file_get_contents("jsondata/dvmPositionCycles.json");
$parsed_json = json_decode($json_string, true);

$cycles1 = "8640000";
$cycles2 = $parsed_json[$position2]["cycle"];
$cycles3 = $parsed_json[$position3]["cycle"];
$cycles4 = "60000";
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


$copyYear = 2023;
$curYear = date("Y");
$copyrightcredit =
    "&copy; DivumWX Team " .
    $copyYear .
    ($copyYear != $curYear ? "-" . $curYear : "Copyright");

$moonRefresh = 3600;



####################################################################################################
// Probably won't have to change anything past this line IF SO BE VER VERY CAREFUL!!!!!!		   #
####################################################################################################
$scriptcredits =
    "Original CSS/SVG ICONS/PHP scripts by <a href='https://divumwx.com' title='divumwx.com' target='_blank'>divumwx.com 2015 - " .
    date("Y") .
    ""; // for modules
$creditsEnabled = "true"; // for chart pages only

$chartsource = "dvmhighcharts";
$creditsURL = ""; // for chart pages only




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
        $_GET["units"] == "ca" ||
        $_GET["units"] == "uk" ||
        $_GET["units"] == "metric" ||
        $_GET["units"] == "scandinavia")
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
