<?php

$battery["power"] = 0;
$battery["soc"] = 0;
$grid["power"] = 0;
$load["power"] = 0;
$load["total_power"] = 0;
$load["total_ups_power"] = 0;
$solar["power"] = 0;
$battery["daily_charge"] = 0;
$battery["daily_discharge"] = 0;
$grid["daily_export"] = 0;
$grid["daily_import"] = 0;
$load["daily_energy"] = 0;
$solar["daily_energy"] = 0;

/*Moon data
$json_string = file_get_contents('jsondata/moonphase.txt');
$moon_json = json_decode($json_string,true);
$alm["moon_time"] = $moon_json[3]["timestamp"];
$alm["moonphase"] = $moon_json[3][3]["phase"];
$alm["luminance"] = $moon_json[3][3]["illumination"];
$alm["moon_age"] = $moon_json[3][3]["moon_age"];
$alm["moon_image"] = $moon_json[3][3]["moon_image"];
$alm["moon_angle"] = $moon_json[3][3]["moon_angle"];*/
?>