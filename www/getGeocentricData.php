<?php
$lat="$lat";
$lon="$lon";
$elev="$elevation";
$sunScript = escapeshellcmd("python3 sun.py  {$lat} {$lon} {$elev}"); 
$sunOutput = shell_exec($sunScript);
$moonScript = escapeshellcmd("python3 moon.py  {$lat} {$lon} {$elev}");
$moonOutput = shell_exec($moonScript);
?>