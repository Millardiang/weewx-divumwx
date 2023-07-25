<?php
#####################################################################################################################                                                                                                        #
#                                                                                                                   #
# weewx-divumwx Skin Template maintained by The DivumWX Team                                                        #
#                                                                                                                   #
# Copyright (C) 2023 Ian Millard, Steven Sheeley, Sean Balfour. All rights reserved                                 #
#                                                                                                                   #
# Distributed under terms of the GPLv3. See the file LICENSE.txt for your rights.                                   #
#                                                                                                                   #
# Issues for weewx-divumwx skin template should be addressed to https://github.com/Millardiang/weewx-divumwx/issues # 
#                                                                                                                   #
#####################################################################################################################
?>
<?php

include ('fixedSettings.php');
include ('dvmShared.php');
error_reporting(0);

$jsonL = 'jsondata/dvmLoopData.json';
$jsonL = file_get_contents($jsonL);
$ldata = json_decode($jsonL, true);

$barom_units = $ldata["unit.label.barometer"];
if($barom_units == " hPa"){$barom["units"] = "hPa";}
else if($barom_units == " kPa"){$barom["units"] = "kPa";} 
else if($barom_units == " inHg"){$barom["units"] = "inHg";} 
else if($barom_units == " mmHg"){$barom["units"] = "mmHg";} 
else if($barom_units == " mbar"){$barom["units"] = "mbar";} 

$temp_units = $ldata["unit.label.outTemp"];
if($temp_units == "°C"){$temp["units"] = "C";}
else if($temp_units == "°F"){$temp["units"] = "F";}

$rain_units = $ldata["unit.label.rain"];
if($rain_units == " mm"){$rain["units"] = "mm";}
else if($rain_units == " cm"){$rain["units"] = "cm";}  
else if($rain_units == " in"){$rain["units"] = "in";}

$wind_units = $ldata["unit.label.windSpeed"];
if($wind_units == " m/s"){$wind["units"] = "m/s";}
else if($wind_units == " km/h"){$wind["units"] = "km/h";} 
else if($wind_units == " mph"){$wind["units"] = "mph";} 
else if($wind_units == " kn"){$wind["units"] = "knots";} 

echo $barom["units"]."</br>";
echo $temp["units"]."</br>";
echo $rain["units"]."</br>";
echo $wind["units"]."</br>";




?>