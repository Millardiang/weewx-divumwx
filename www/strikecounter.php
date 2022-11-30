#!/usr/bin/php

<!--?php
$json = "/srv/http/html/weather34/jsondata/wf.txt";
$jsonobj = file_get_contents($json);
$arr = json_decode($jsonobj, true);
$lightning_strike_count = $arr['obs'][0]['lightning_strike_count'];
$lightning_strike_last_distance = $arr['obs'][0]['lightning_strike_last_distance'];  
$myfile = fopen("/srv/http/html/weather34/jsondata/strikecounter.txt", "w") or die("Unable to open file!");
$txt = "lightning strike = $lightning_strike_count"."\n";
fwrite($myfile, $txt);
$txt = "lightning distance = $lightning_strike_last_distance";
fwrite($myfile, $txt);
fclose($myfile);
?-->


<?php
$json = "/srv/http/html/weather34/boltek/data/ngxdata.json";
$jsonobj = file_get_contents($json);
$arr = json_decode($jsonobj, true); 
$lightning_strike_count = $arr['StrikeCount'];
$json = "/srv/http/html/weather34/boltek/data/ngxdata.json";
$jsonobj = file_get_contents($json);
$arr = json_decode($jsonobj, true);
$lightning_strike_last_distance = $arr['StrikeData'][0]['dist']; 
$myfile = fopen("/srv/http/html/weather34/jsondata/strikecounter.txt", "w") or die("Unable to open file!");
$txt = "lightning strike = $lightning_strike_count"."\n";
fwrite($myfile, $txt);
$txt = "lightning distance = $lightning_strike_last_distance";
fwrite($myfile, $txt);
fclose($myfile);
// if(empty($var1)){
?>


