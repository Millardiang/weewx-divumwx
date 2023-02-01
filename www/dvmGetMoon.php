<?php
include("dvmCombinedData.php");
$url_to_image = "https://www.fourmilab.ch/cgi-bin/Earth?img=LRO_100m.evif&imgsize=320&dynimg=y&gamma=1.32&opt=-l&lat=".abs($lat)."&ns=".$NS."&lon=".abs($lon)."&ew=".$EW."&alt=8527&tle=&date=0&utc=&jd=/Earth.jpg";
$filename = 'img/moon-1.jpg';
file_put_contents($filename, file_get_contents($url_to_image));
?>