<?php
/*
Include a stanza like this in your weewx.conf file: -

###############################################################################

# Options for extension 'filepile'
[FilePile]
    filename = /var/www/html/divumwx/serverdata/filepileTextData.txt
    unit_system = US
    [[label_map]]
       
        pm25 = pm2_5
        pm10 = pm10_0
        co = co
        no2 = no2
        so2 = so2
        o3 = o3
        nh3 = nh3
        cloudCover = cloudcover

################################################################################

You must also create a dummy file in your serverdata folder: -

filepileTextData.txt with permissions set to 0777 to make it writeable and executable

*/
#
# If you are importing and parsing aqi json data from an external device, you must un-comment
# lines 33 to 37 and lines 39 to 42 and set the correct path to the source at line 10
# Configure arrays at lines 36 and 37 to your json data as required
#
include('getFilePileData.php');
/*$json_cloud = file_get_contents("/var/www/html/divumwx/jsondata/awc.txt");
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
*/
//$solarGeneration = file_get_contents("/var/www/html/divumwx/serverdata/solarGeneration.txt");
$myfile = fopen("/var/www/html/divumwx/serverdata/filepileTextData.txt", "w") or die("Unable to open file!");
$txt = "pm25 = $pm25\n";
fwrite($myfile, $txt);
$txt = "pm10 = $pm10\n";
fwrite($myfile, $txt);
$txt = "co = $co\n";
fwrite($myfile, $txt);
$txt = "no2 = $no2\n";
fwrite($myfile, $txt);
$txt = "so2 = $so2\n";
fwrite($myfile, $txt);
$txt = "o3 = $o3\n";
fwrite($myfile, $txt);
$txt = "nh3 = $nh3\n";
fwrite($myfile, $txt);
$txt = "aod = $aod\n";
fwrite($myfile, $txt);
$txt = "dust = $dust\n";
fwrite($myfile, $txt);
$txt = "alder = $alder\n";
fwrite($myfile, $txt);
$txt = "birch = $birch\n";
fwrite($myfile, $txt);
$txt = "olive = $birch\n";
fwrite($myfile, $txt);
$txt = "grass = $grass\n";
fwrite($myfile, $txt);
$txt = "mugwort = $mugwort\n";
fwrite($myfile, $txt);
$txt = "ragweed = $ragweed\n";
fwrite($myfile, $txt);
$txt = "cloudCover = $cloudcover\n";
fwrite($myfile, $txt);
$txt = "batteryPower = $batteryPower\n";
fwrite($myfile, $txt);
$txt = "batterySOC = $batterySOC\n";
fwrite($myfile, $txt);
$txt = "dailySolarExport = $dailySolarExport\n";
fwrite($myfile, $txt);
$txt = "dailySolarEnergy = $dailySolarEnergy\n";
fwrite($myfile, $txt);
$txt = "gridPower = $gridPower\n";
fwrite($myfile, $txt);
$txt = "loadTotalPower = $loadTotalPower\n";
fwrite($myfile, $txt);
$txt = "solarPower = $solarPower\n";
fwrite($myfile, $txt);
$txt = "solarEfficiency = $solarEfficiency";
fwrite($myfile, $txt);fclose($myfile);
?>


