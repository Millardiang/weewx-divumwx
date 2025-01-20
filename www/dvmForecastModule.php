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

//include_once('dvmCombinedData.php');
include "dvmForecastData.php";

error_reporting(0);
date_default_timezone_set($TZ);
header("Content-type: text/html; charset=UTF-8");
?>
    <div class="forecast4">
      <span class="yearpopup"><a alt="Forecast Menu" title="Forecast Menu" href="dvmForecastHourlyPopup.php" data-lity><?php echo $chartinfo; ?> Forecasts and Meteogram</a></span>
      <span class="yearpopup"><a alt="rss" title="MetOffice RSS" href="rss.php" data-lity><?php echo $chartinfo; ?> MetOffice RSS</a></span>
 
      <span class="yearpopup"><a alt="Health" title="Health" href="alert.html"><?php echo $chartinfo; ?> Weather Health</a></span>
</div>
<span class='moduletitle'><?php echo $lang[
    "forecastModule"
]; ?> (<valuetitleunit>&deg;<?php echo $temp[
     "units"
 ]; ?></valuetitleunit>)</span>


<div class="updatedtime1"><?php
$forecastime = filemtime("jsondata/awd.txt");

$forecasturl = file_get_contents("jsondata/awd.txt");
if (filesize("jsondata/awd.txt") < 1) {
    echo "" . $offline . "";} else {
    echo $online. "";
}
echo " ". date($timeFormat, $forecastime);
?></div>
<style>
.forecast4 {
  position: absolute;
  font-family: arial, system;
  z-index: 20;
  padding-top: 1px;
  margin-left: 0;
  font-size: .67em;
  color: var(--col-6);
  margin-top: 159px;
  width: 300px;
  padding-left: 10px;
  text-align: left;
}
.forecast4:hover {
  color: #90b12a
}
table.forecast5{width:98%;height:70%;text-align:center;border-spacing:3px;}
table.forecast5 td, table.power th{border:1px solid var(--col-13);border-radius:2px;padding:0px 0px;width:15%;}
table.forecast5 tbody td{font-size:0.500em;}
</style>

<table class="forecast5">
<tbody>
<tr>
<td style="border: transparent;width:10%;"></td>
<td style="border: transparent;"><?php echo $forecastTime[0]; ?></td>
<td style="border: transparent;"><?php echo $forecastTime[1]; ?></td>
<td style="border: transparent;"><?php echo $forecastTime[2]; ?></td>
<td style="border: transparent;"><?php echo $forecastTime[3]; ?></td>
<td style="border: transparent;"><?php echo $forecastTime[4]; ?></td>
</tr>
<tr>
<td style="border: transparent;width:10%;"></td>
<td style="border: transparent;"><?php echo $forecastIcon[0]; ?></td>
<td style="border: transparent;"><?php echo $forecastIcon[1]; ?></td>
<td style="border: transparent;"><?php echo $forecastIcon[2]; ?></td>
<td style="border: transparent;"><?php echo $forecastIcon[3]; ?></td>
<td style="border: transparent;"><?php echo $forecastIcon[4]; ?></td>
</tr>
<tr>
<td style="border: transparent;width:5%;"><?php echo $thermIcon; ?></td>
<td style="border-left: 5px solid <?php echo $colorOutTemp; ?>;"><?php echo $forecastTempMax[0]; ?>&deg;<?php echo $tempunit; ?></td>
<td style="border-left: 5px solid <?php echo $colorOutTemp; ?>;"><?php echo $forecastTempMax[1]; ?>&deg;<?php echo $tempunit; ?></td>
<td style="border-left: 5px solid <?php echo $colorOutTemp; ?>;"><?php echo $forecastTempMax[2]; ?>&deg;<?php echo $tempunit; ?></td>
<td style="border-left: 5px solid <?php echo $colorOutTemp; ?>;"><?php echo $forecastTempMax[3]; ?>&deg;<?php echo $tempunit; ?></td>
<td style="border-left: 5px solid <?php echo $colorOutTemp; ?>;"><?php echo $forecastTempMax[4]; ?>&deg;<?php echo $tempunit; ?></td>
</tr>
<tr>
<td style="border: transparent;width:5%;"><?php echo $windalert2; ?></td></td>
<td style="border-left: 5px solid <?php echo $color[
    "windGust"
]; ?>;"><?php echo $forecastWindDirMax[0]; ?>&nbsp;<?php
echo $forecastWindGust[0];
echo $windunit;
?></td>
<td style="border-left: 5px solid <?php echo $color[
    "windGust"
]; ?>;"><?php echo $forecastWindDirMax[1]; ?>&nbsp;<?php
echo $forecastWindGust[1];
echo $windunit;
?></td>
<td style="border-left: 5px solid <?php echo $color[
    "windGust"
]; ?>;"><?php echo $forecastWindDirMax[2]; ?>&nbsp;<?php
echo $forecastWindGust[2];
echo $windunit;
?></td>
<td style="border-left: 5px solid <?php echo $color[
    "windGust"
]; ?>;"><?php echo $forecastWindDirMax[3]; ?>&nbsp;<?php
echo $forecastWindGust[3];
echo $windunit;
?></td>
<td style="border-left: 5px solid <?php echo $color[
    "windGust"
]; ?>;"><?php echo $forecastWindDirMax[4]; ?>&nbsp;<?php
echo $forecastWindGust[4];
echo $windunit;
?></td>
</tr>
<tr>
<td style="border: transparent;width:5%;"><?php echo $rainsvg; ?></td>
<td style="border-left: 5px solid <?php echo $color["rain"]; ?>;"><?php
echo $forecastPrecip[0];
echo $rainunit;
?></td>
<td style="border-left: 5px solid <?php echo $color["rain"]; ?>;"><?php
echo $forecastPrecip[1];
echo $rainunit;
?></td>
<td style="border-left: 5px solid <?php echo $color["rain"]; ?>;"><?php
echo $forecastPrecip[2];
echo $rainunit;
?></td>
<td style="border-left: 5px solid <?php echo $color["rain"]; ?>;"><?php
echo $forecastPrecip[3];
echo $rainunit;
?></td>
<td style="border-left: 5px solid <?php echo $color["rain"]; ?>;"><?php
echo $forecastPrecip[4];
echo $rainunit;
?></td>
</tr>
<tr>
<td style="border: transparent;width:5%;"><?php echo $uvicon; ?></td>
<td style="border-left: 5px solid <?php echo $color[
    "UVI"
]; ?>;"><?php echo $forecastUVI[0]; ?></td>
<td style="border-left: 5px solid <?php echo $color[
    "UVI"
]; ?>;"><?php echo $forecastUVI[1]; ?></td>
<td style="border-left: 5px solid <?php echo $color[
    "UVI"
]; ?>;"><?php echo $forecastUVI[2]; ?></td>
<td style="border-left: 5px solid <?php echo $color[
    "UVI"
]; ?>;"><?php echo $forecastUVI[3]; ?></td>
<td style="border-left: 5px solid <?php echo $color[
    "UVI"
]; ?>;"><?php echo $forecastUVI[4]; ?></td>
</tr>
<tr>
<td style="border: transparent;width:5%;"><?php echo $humidity; ?></td>
<td style="border-left: 5px solid <?php echo $colorOutHumidity; ?>;"><?php echo $forecastHumid[0]; ?>%</td>
<td style="border-left: 5px solid <?php echo $colorOutHumidity; ?>;"><?php echo $forecastHumid[1]; ?>%</td>
<td style="border-left: 5px solid <?php echo $colorOutHumidity; ?>;"><?php echo $forecastHumid[2]; ?>%</td>
<td style="border-left: 5px solid <?php echo $colorOutHumidity; ?>;"><?php echo $forecastHumid[3]; ?>%</td>
<td style="border-left: 5px solid <?php echo $colorOutHumidity; ?>;"><?php echo $forecastHumid[4]; ?>%</td>
</tr>
</tbody>
</table>
