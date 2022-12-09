<?php
include("dvmCombinedData.php");
$url_to_image = "https://www.fourmilab.ch/cgi-bin/Earth?img=LRO_100m.evif&imgsize=320&dynimg=y&gamma=1.32&opt=-l&lat=".$lat."&ns=".$NS."&lon=".$lon."&ew=".$EW."&alt=35785&tle=&date=0&utc=&jd=/Earth.jpg";
$filename = 'img/moon-1.jpg';
file_put_contents($filename, file_get_contents($url_to_image));
?>
