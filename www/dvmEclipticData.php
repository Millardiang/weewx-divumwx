<?php
//include('dvmCombinedData.php');
$lat = "$lat";
$lon = "$lon";
$elev = "$elevation";
$Ecliptic = escapeshellcmd("python3 ecliptic.py {$lat} {$lon} {$elev}"); 
$EclipticAngle = shell_exec($Ecliptic);

?>