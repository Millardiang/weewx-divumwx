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

include('dvmCombinedData.php');
$cloud_region = explode("/", $TZ);
error_reporting(0);
?>

<!DOCTYPE html>
<head>
<meta charset="utf-8">
<title>weather current conditions</title>
</head>



<?php //cloudbase
$clouds = "Cloudbase";
if ($windunit == 'mph' || $windunit == 'kts'){$sky["cloud_base"] = round($sky["cloud_base"] * 3.281);$distance = "ft";}
else {$distance = "m";}
?>

<?php
$json_visibility = file_get_contents("jsondata/awc.txt");
$parsed_visibility = json_decode($json_visibility, true);

if ($tempunit !== 'F') {
$snow = $parsed_visibility['response'][0]['periods'][0]['snowCM'];
} else {
$snow = $parsed_visibility['response'][0]['periods'][0]['snowIN'];   
}

if ($windunit == 'mph') {
$visibility = round($parsed_visibility['response'][0]['periods'][0]['visibilityMI'],0,PHP_ROUND_HALF_UP)."mi";
} else {
$visibility = round($parsed_visibility['response'][0]['periods'][0]['visibilityKM'],0,PHP_ROUND_HALF_UP)."km";
}
$sky["cloud_cover"] = $parsed_visibility['response'][0]['periods'][0]['sky'];
        
//rain-divumwx
if($rain["rate"] > 0 && $wind["speed_avg"] > 15){$current["image"] ="img/meteocons/umbrella-wind.svg";}
else if($rain["rate"] > 10){$current["image"] = "img/meteocons/umbrella-wind-alt.svg";}
else if($rain["rate"] > 0){$current["image"] = "img/meteocons/overcast-rain.svg";}
//fog-divumwx
else if($temp["outside_now"] - $dew["now"] < 0.5 && $dayPartNatural == "night" && $temp["outside_now"] > 5){$current["image"] ="img/meteocons/fog-night.svg";}
else if($temp["outside_now"] - $dew["now"] < 0.5 && $temp["outside_now"] > 5){$current["image"] = "img/meteocons/fog-day.svg";}
//misty-divumwx
else if($temp["outside_now"] - $dew["now"] < 0.8 && $dayPartNatural == "night" && $temp["outside_now"] > 5){$current["image"] ="img/meteocons/haze-night.svg";}
else if($temp["outside_now"] - $dew["now"] < 0.8 && $temp["outside_now"] > 5){$current["image"] = "img/meteocons/haze-day.svg";}
//windy moderate-divumwx
else if($wind["speed_avg"] >= 15 && $dayPartNatural == "night" && $sky["cloud_cover"] < 20){$current["image"] = "img/meteocons/wind.svg";}
else if($wind["speed_avg"] >= 15 && $sky["cloud_cover"] < 20){$current["image"] = "img/meteocons/wind.svg";}
//windy moderate-divumwx
else if($wind["speed_avg"] >= 15 && $dayPartNatural == "night"){$current["image"] ="img/meteocons/wind.svg";}
else if($wind["speed_avg"] >= 15){$current["image"] ="img/meteocons/wind.svg";}
// snow-divumwx
else if($snow > 0.0 && $dayPartNatural == "night"){$current["image"] ="img/meteocons/snow.svg";}
else if($snow > 0.0){$current["image"] ="img/meteocons/snow.svg";}

//cloud-icon
else if ($sky["cloud_cover"] < 7 && $sky["cloud_cover"] > 0) {
if ($dayPartNatural == "night" ){$current["image"] ="img/meteocons/clear-night.svg";} 
else $current["image"] ="img/meteocons/clear-day.svg";
} 
else if ($sky["cloud_cover"] < 32) {
if ($dayPartNatural == "night" ){$current["image"] = "img/meteocons/mostly-clear-night.svg";} 
else $current["image"] ="img/meteocons/mostly-clear-day.svg";
}
else if ($sky["cloud_cover"] < 70) {
if ($dayPartNatural == "night" ){$current["image"] = "img/meteocons/partly-cloudy-night.svg";} 
else $current["image"] = "img/meteocons/partly-cloudy-day.svg";
}
else if ($sky["cloud_cover"] < 95) {
if ($dayPartNatural == "night" ){$current["image"] = "img/meteocons/overcast-night.svg";} 
else $current["image"] = "img/meteocons/overcast-day.svg";
}
else if($sky["cloud_cover"] >= 95) {
if ($dayPartNatural == "night" ){$current["image"] ="img/meteocons/overcast.svg";} 
else $current["image"] = "img/meteocons/overcast.svg";
}

//rain-divumwx
if($rain["rate"] > 0 && $wind["speed_avg"] > 15){$sky["summary"] = "Rain Showers Windy";}
else if($rain["rate"] >= 20){$sky["summary"] = "Flooding Possible";}
else if($rain["rate"] >= 10){$sky["summary"] ="Heavy Rain";}
else if($rain["rate"] >= 5){$sky["summary"] = "Moderate Rain";}
else if($rain["rate"] >= 1){$sky["summary"] = "Steady Rain";}
else if($rain["rate"] > 0){$sky["summary"] = "Light Rain";}
//snow-divumwx
else if($snow > 0.0){$sky["summary"] = "Light Snow";}
//fog-divumwx
else if($temp["outside_now"] - $dew["now"] < 0.5 && $dayPartNatural == "night" && $temp["outside_now"] > 5){$sky["summary"] = "Misty";}
else if($temp["outside_now"] - $dew["now"] < 0.5 && $temp["outside_now"] > 5){$sky["summary"] = "Misty";}
//misty-divumwx
else if($temp["outside_now"] - $dew["now"] < 0.8 && $dayPartNatural == "night" && $temp["outside_now"] > 5){$sky["summary"] = "Misty Hazy";}
else if($temp["outside_now"] - $dew["now"] < 0.8 && $temp["outside_now"] > 5){$sky["summary"] = "Misty Hazy";}
//windy-divumwx
else if($wind["speed_avg"] >= 40){$sky["summary"] = "Strong Wind";}
else if($wind["speed_avg"] >= 30){$sky["summary"] = "Very Windy";}
else if($wind["speed_avg"] >= 22){$sky["summary"] = "Moderate Wind";}
else if($wind["speed_avg"] >= 15){$sky["summary"] = "Breezy";}
//cloud-description
else if($sky["cloud_cover"] < 7 && $sky["cloud_cover"] > 0) {$sky["summary"] = "Clear";}
else if ($sky["cloud_cover"] < 7 && $sky["cloud_cover"] > 0) {
if ($dayPartNatural == "night" ){$sky["summary"] = "Clear";} 
else $sky["summary"] = "Sunny";
} 
else if ($sky["cloud_cover"] < 32) {
if ($dayPartNatural == "night"){$sky["summary"] = "Mostly Clear";} 
else $sky["summary"] = "Mostly Sunny";
}
else if($sky["cloud_cover"] < 70) {$sky["summary"] = "Partly Cloudy";}
else if($sky["cloud_cover"] < 95) {$sky["summary"] = "Mostly Cloudy";}
else if($sky["cloud_cover"] >= 95) {$sky["summary"] = "Overcast";}
else if(filesize('jsondata/me.txt') < 160){$sky["summary"] = "Conditions Not Available";} 
//oktas
if($sky["cloud_cover"] < 5 && $sky["cloud_cover"] > 0) {$sky["cloud_oktas"] = "0oktas";}
else if($sky["cloud_cover"] <= 12.5) {$sky["cloud_oktas"] = "1okta";}
else if($sky["cloud_cover"] <= 25) {$sky["cloud_oktas"] = "2oktas";}
else if($sky["cloud_cover"] <= 37.5) {$sky["cloud_oktas"] = "3oktas";}
else if($sky["cloud_cover"] <= 50) {$sky["cloud_oktas"] = "4oktas";}
else if($sky["cloud_cover"] <= 62.5) {$sky["cloud_oktas"] = "5oktas";}
else if($sky["cloud_cover"] <= 75) {$sky["cloud_oktas"] = "6oktas";}
else if($sky["cloud_cover"] <= 87.5) {$sky["cloud_oktas"] = "7oktas";}
else if($sky["cloud_cover"] <= 100) {$sky["cloud_oktas"] = "8oktas";}

if(anyToC($temp["outside_day_avg_60mn"])<=-10){$tempcolor = "#8781bd";}
else if(anyToC($temp["outside_day_avg_60mn"])<=0){$tempcolor = "#487ea9";}
else if(anyToC($temp["outside_day_avg_60mn"])<=5){$tempcolor = "#3b9cac";}
else if(anyToC($temp["outside_day_avg_60mn"])<10){$tempcolor = "#9aba2f";}
else if(anyToC($temp["outside_day_avg_60mn"])<20){$tempcolor = "#e6a141";}
else if(anyToC($temp["outside_day_avg_60mn"])<25){$tempcolor = "#ec5a34";}
else if(anyToC($temp["outside_day_avg_60mn"])<30){$tempcolor = "#d05f2d";}
else if(anyToC($temp["outside_day_avg_60mn"])<35){$tempcolor = "#d65b4a";}
else if(anyToC($temp["outside_day_avg_60mn"])<40){$tempcolor = "#dc4953";}
else if(anyToC($temp["outside_day_avg_60mn"])<100){$tempcolor = "#e26870";}
//ordinal
if ($wind["direction_10m_avg"] <= 11.25) {
    $$bearing = 'N';
} else if ($wind["direction_10m_avg"] <= 33.75) {
    $bearing = 'NNE';
} else if ($wind["direction_10m_avg"] <= 56.25) {
    $bearing = 'NE';
} else if ($wind["direction_10m_avg"] <= 78.75) {
    $bearing = 'ENE';
} else if ($wind["direction_10m_avg"] <= 101.25) {
    $bearing = 'E';
} else if ($wind["direction_10m_avg"] <= 123.75) {
    $bearing = 'ESE';
} else if ($wind["direction_10m_avg"] <= 146.25) {
    $bearing = 'SE';
} else if ($wind["direction_10m_avg"] <= 168.75) {
    $bearing = 'SSE';
} else if ($wind["direction_10m_avg"] <= 191.25) {
    $bearing = 'S';
} else if ($wind["direction_10m_avg"] <= 213.75) {
    $bearing = 'SSW';
} else if ($wind["direction_10m_avg"] <= 236.25) {
    $bearing = 'SW';
} else if ($wind["direction_10m_avg"] <= 261.25) {
    $bearing = 'WSW';
} else if ($wind["direction_10m_avg"] <= 281.25) {
    $bearing = 'W';
} else if ($wind["direction_10m_avg"] <= 303.75) {
    $bearing = 'WNW';
} else if ($wind["direction_10m_avg"] <= 326.25) {
    $bearing = 'NW';
} else if ($wind["direction_10m_avg"] <= 348.75) {
    $bearing = 'NNW';
} else { $bearing = 'North'; }

?>

<!DOCTYPE html>
<head>
<meta charset="utf-8">
<title>weather current conditions</title>
<style>
table.current {
  font-family: Arial, Helvetica, sans-serif;
  background-color: transparent;
  width: 94%;
  text-align: center;
}
table.current td, table.current th {
  border:1px solid var(--col-13);
  border-radius:2px;
  padding: 1px 0px;
  column-gap: 20px;
}
table.current tbody td {
  font-size: 0.6em;
  color: var(--col-6);
}
</style>
</head>

<div class="chartforecast">
<span class="yearpopup"><a alt="Airport Metar Station" title="Airport Metar Station" href="dvmMetarPopup.php" data-lity><?php echo $chartinfo;?><?php echo ' Airport | Metar';?>
<?php if (filesize('jsondata/me.txt') < 160) { echo "&nbsp;" , $offline;} else echo "";?></a></span>
<span class="monthpopup"><a href="dvmWindyRadarPopup.php" title="Windy.com Radar" alt="Windy.com Radar" data-lity><?php echo $chartinfo;?> Radar</a></span>
<span class="monthpopup"><a href="dvmWindyWindPopup.php" title="Windy.com Wind Map" alt="Windy.com Wind Map" data-lity><?php echo $chartinfo;?> Wind Map</a></span>
<span class="todaypopup"><a alt="cloud cover" title="cloud cover" href="dvmhighcharts/cloudCharts.php?chart='cloudcoverplot'&span='weekly'&temp='<?php echo $temp['units'];?>'&pressure='<?php echo $barom['units'];?>'&wind='<?php echo $wind['units'];?>'&rain='<?php echo $rain['units']?>
" data-lity><?php echo $menucharticonpage;?> Cloud Cover</a></span>
</div>
<span class='moduletitle'><?php echo $lang["currentModule"]; ?> (<valuetitleunit>&deg;<?php echo $temp["units"]; ?></valuetitleunit>)</span>
<div class="updatedtime1">
<?php $forecastime=filemtime('jsondata/awc.txt');$divumwxwuurl=file_get_contents("jsondata/awc.txt");if(filesize('jsondata/awc.txt')<10){echo $offline;}
else echo $online,"";echo " ",  date($timeFormat,$forecastime);?>    
</div></div>
 
<div class="current-module" style="display:grid;grid-template-columns: auto auto;">
<div><?php echo $sky["summary"];?></br><img src="<?php echo $current["image"];?>" style="width:120px;"</img></div>
<table class="current">
<tbody>
<tr>

<td style="border:transparent;">Cloudbase</td>
<td style="border:transparent;">Cloud Cover</td>
</tr>
<tr>
<td style="width:auto;text-align: center;border-left: 5px solid rgb(109,100,136);"><?php echo $sky["cloud_base"];?><?php echo $distance;?></td>
<td style="width:auto;text-align: center;border-left: 5px solid #cccccc;"><?php echo $sky["cloud_cover"];?>%&nbsp;(<?php echo $sky["cloud_oktas"];?>)</td>
</tr>
<tr>
<td style="border:transparent;">10min Rainfall</td>
<td style="border:transparent;">60min Temp Avg</td>
</tr>
<tr>
<td style="width:40%;text-align: center;border-left: 5px solid <?php echo $colorRainDaySum;?>;"><?php echo $rain["last_10min"];?><?php echo $rainunit;?></td>
<td style="width:40%;text-align: center;border-left: 5px solid <?php echo $tempcolor;?>;"><?php echo $temp["outside_day_avg_60mn"];?>&deg;<?php echo $tempunit;?></td>
</tr>
<tr>
<td style="border:transparent;">Visibility</td>
<td style="border:transparent;">10min Bearing</td>
</tr>
<tr>
<td style="width:40%;text-align: center;border-left: 5px solid rgb(214,57,129);"><?php echo $visibility;?></td>
<td style="width:40%;text-align: center;border-left: 5px solid <?php echo $color["windSpeed_10min_avg"];?>;"><?php echo $bearing;?>&nbsp;<?php echo $wind["direction_10m_avg"];?>&deg;</td>
</tr>
<tr>
<td style="border:transparent;">10min Speed Avg</td>
<td style="border:transparent;">10min Gust Max</td>
</tr>
<tr>
<tr>
<td style="width:40%;text-align: center;border-left: 5px solid <?php echo $color["windSpeed_10min_avg"];?>;"><?php echo $wind["speed_10m_avg"];?><?php echo $windunit;?></td>
<td style="width:40%;text-align: center;border-left: 5px solid <?php echo $color["windGust_10min_max"];?>;"><?php echo $wind["gust_10m_max"];?><?php echo $windunit;?></td>
</tr>
</tbody>
</table>
</div>
</html>




