<?php
$url_to_image = 'https://www.fourmilab.ch/cgi-bin/Earth?img=LRO_100m.evif&imgsize=320&dynimg=y&gamma=1.32&opt=-l&lat=51.9407&ns=North&lon=-0.987&ew=West&alt=35785&tle=&date=0&utc=&jd=/Earth.jpg';
$my_save_dir = '/var/www/html/weewx/divumwx/img/';
$filename = 'moon-1.jpg';
$complete_save_loc = $my_save_dir . $filename;
file_put_contents($complete_save_loc, file_get_contents($url_to_image));
?>
