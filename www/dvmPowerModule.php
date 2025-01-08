<?php
##############################################################################################
#        ________   __  ___      ___  ____  ____  ___      ___    __   __  ___  ___  ___     #
#       |"      "\ |" \|"  \    /"  |("  _||_ " ||"  \    /"  |  |"  |/  \|  "||"  \/"  |    #
#       (.  ___  :)||  |\   \  //  / |   (  ) : | \   \  //   |  |'  /    \:  | \   \  /     #
#       |: \   ) |||:  | \\  \/. ./  (:  |  | . ) /\\  \/.    |  |: /'        |  \\  \/      #
#       (| (___\ |||.  |  \.    //    \\ \__/ // |: \.        |   \//  /\'    |  /\.  \      #
#       |:       :)/\  |\  \\   /     /\\ __ //\ |.  \    /:  |   /   /  \\   | /  \   \     #
#       (________/(__\_|_)  \__/     (__________)|___|\__/|___|  |___/    \___||___/\___|    #
#                                                                                            #
#     Copyright (C) 2023 Ian Millard, Steven Sheeley, Sean Balfour. All rights reserved      #
#      Distributed under terms of the GPLv3.  See the file LICENSE.txt for your rights.      #
#    Issues for weewx-divumwx skin template are only addressed via the issues register at    #
#                    https://github.com/Millardiang/weewx-divumwx/issues                     #
##############################################################################################

include_once('dvmCombinedData.php');
$power = '<img src="./img/power.svg">'; 
error_reporting(0); date_default_timezone_set($TZ);
header('Content-type: text/html; charset=UTF-8');


?>
    <div class="forecast4">
      <!--span class="yearpopup"><a alt="Forecast Menu" title="Forecast Menu" href="dvmForecastHourlyPopup.php" data-lity><?php echo $chartinfo;?> Forecasts and Meteogram</a></span>
       <span class="yearpopup"><a alt="Forecast Menu" title="Forecast Menu" href="alert.html"><?php echo $chartinfo;?> Weather Health</a></span-->
</div>
    <span class='moduletitle'>Current Power | Daily Energy</span>


<div class="updatedtime1"><?php $forecastime=filemtime('dvmCustomData.php');

$forecasturl = file_get_contents("jsondata/awd.txt");
if(filesize('jsondata/awd.txt')<1){echo "".$offline. "";}
else echo $online,"";echo " ",	date($timeFormat,$forecastime);	?></div>


<table class="power">
<tbody>
<tr>
<td rowspan="2" style="border: transparent;"><img src="./img/battery.svg" style="height:26px;border: transparent;"></td>
<td style="border: transparent;">Power</td>
<td style="border: transparent;">SOC</td>
<td style="border: transparent;">Day Charge</td>
<td style="border: transparent;">Discharge</td>
</tr>
<tr>
<td style="border-left: 5px solid rgb(59,134,166);"><?php echo $battery["power"];?>W</td>
<td style="border-left: 5px solid rgb(59,134,166);"><?php echo $battery["soc"];?>%</td>
<td style="border-left: 5px solid rgb(59,134,166);"><?php echo $battery["daily_charge"];?>kWh</td>
<td style="border-left: 5px solid rgb(59,134,166);"><?php echo $battery["daily_discharge"];?>kWh</td>
</tr>
<tr>
<td rowspan="2" style="border: transparent;"><img src="./img/grid.svg" style="height:26px;border: transparent;"></td>
<td style="border: transparent;">Power</td>
<td style="border: transparent;"></td>
<td style="border: transparent;">Day Export</td>
<td style="border: transparent;">Day Import</td>
</tr>
<tr>
<td style="border-left: 5px solid rgb(128,128,38);"><?php echo $grid["power"];?>W</td>
<td style="border: transparent;"></td>
<td style="border-left: 5px solid rgb(128,128,38);"><?php echo $grid["daily_export"];?>kWh</td>
<td style="border-left: 5px solid rgb(128,128,38);"><?php echo $grid["daily_import"];?>kWh</td>
</tr>
<tr>
<td rowspan="2" style="border: transparent;"><img src="./img/house.svg" style="height:26px;border: transparent;"></td>
<td style="border: transparent;">Power</td>
<td style="border: transparent;">UPS Power</td>
<td style="border: transparent;">Total Power</td>
<td style="border: transparent;">Daily Energy</td>
</tr>
<tr>
<td style="border-left: 5px solid rgb(158,74,27);"><?php echo $load["power"];?>W</td>
<td style="border-left: 5px solid rgb(158,74,27);"><?php echo $load["total_ups_power"];?>W</td>
<td style="border-left: 5px solid rgb(158,74,27);"><?php echo $load["total_power"];?>W</td>
<td style="border-left: 5px solid rgb(158,74,27);"><?php echo $load["daily_energy"];?>kWh</td>
</tr>
<tr>
<td rowspan="2" style="border: transparent;"><img src="./img/pvClear.svg" style="height:26px;border: transparent;"></td>
<td style="border: transparent;">Power</td>
<td style="border: transparent;"></td>
<td style="border: transparent;">Day Energy</td>
<td style="border: transparent;"></td>
</tr>
<tr>
<td style="border-left: 5px solid rgb(237,190,117);"><?php echo $solar["power"];?>W</td>
<td style="border: transparent;"></td>
<td style="border-left: 5px solid rgb(237,190,117);"><?php echo $solar["daily_energy"];?>kWh</td>
<td style="border: transparent;"></td>
</tr>
</tbody>
</table>
