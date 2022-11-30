<?php
include("dvmCombinedData.php");
if ($lat >= "0" && $lat <= "180"){$NS = "North";}
else {$NS = "South";}
if ($lon >= "0" && $lat <= "180"){$NS = "East";}
else {$EW = "West";}
$url_to_image = "https://www.fourmilab.ch/cgi-bin/Earth?img=LRO_100m.evif&imgsize=320&dynimg=y&gamma=1.32&opt=-l&lat=".$lat."&ns=".$NS."&lon=".$lon."&ew=".$EW."&alt=35785&tle=&date=0&utc=&jd=/Earth.jpg";
$my_save_dir = '/var/www/html/weewx/divumwx/img/';
$filename = 'moon-1.jpg';
$complete_save_loc = $my_save_dir . $filename;
//file_put_contents($complete_save_loc, file_get_contents($url_to_image));

//echo $lon;
echo $url_to_image;
//echo $NS."</br>";
//echo $EW;
?>
