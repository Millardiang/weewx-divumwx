<?php 
include('dvmCombinedData.php');

$sunScript = exec('python3 /var/www/html/divumwx/sun2.py $lat $lon $elevation'); $sunOutput = shell_exec($sunScript);
$moonScript = exec('python3 /var/www/html/divumwx/moon2.py $lat $lon $elevation'); $moonOutput = shell_exec($moonScript);
//echo $sunOutput."</br>";
echo $moonOutput;?>