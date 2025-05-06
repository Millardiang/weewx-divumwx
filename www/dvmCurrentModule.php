<?php
include('dvmCombinedData.php');
error_reporting(0);
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>weather current conditions</title>
</head>
<body>

<div class="chartforecast">
<span class="yearpopup"><a alt="Airport Metar Station" title="Airport - Metar" href="dvmMetarPopup.php" data-lity><?php echo $chartinfo;?><?php echo ' Airport | Metar';?>
<?php if (filesize('jsondata/me.txt') < 160) { echo "&nbsp;" , $offline;} else echo "";?></a></span>
<span class="monthpopup"><a href="dvmWindyRadarPopup.php" title="Windy.com Radar Map" alt="Windy.com Radar" data-lity><?php echo $chartinfo;?> Radar</a></span>
<span class="monthpopup"><a href="dvmWindyWindPopup.php" title="Windy.com Wind Map" alt="Windy.com Wind Map" data-lity><?php echo $chartinfo;?> Wind Map</a></span>
</div>

<span class='moduletitle'><?php echo $lang['currentModule'];?></span>
<div class="updatedtime1">
<?php $forecastime=filemtime('jsondata/awc.txt');$divumwxwuurl=file_get_contents("jsondata/awc.txt");if(filesize('jsondata/awc.txt')<10){echo $offline;}
else echo $online,"";echo " ",  date($timeFormat,$forecastime);?>    
</div>

<?php
$clouds = "Cloudbase";
if ($windunit == 'mph' || $windunit == 'kts'){$sky["cloud_base"] = round($sky["cloud_base"] * 3.281);}
if ($windunit == 'mph' || $windunit == 'kts'){$distance = "ft";}
else if ($windunit == 'km/h' || $windunit == 'm/s'){$distance = "m";}
?>

<?php 
$json_visibility = file_get_contents("jsondata/awc.txt");
$parsed_visibility = json_decode($json_visibility, true);

if ($windunit == 'km/h') {
$snow = $parsed_visibility['response'][0]['periods'][0]['snowRateCM'];
} else {
$snow = $parsed_visibility['response'][0]['periods'][0]['snowRateIN'];   
}

if ($windunit == 'mph') {
$visibility = round($parsed_visibility['response'][0]['periods'][0]['visibilityMI'],0,PHP_ROUND_HALF_UP)." miles";
} else {
$visibility = round($parsed_visibility['response'][0]['periods'][0]['visibilityKM'],0,PHP_ROUND_HALF_UP)." km";
}
$sky["cloud_cover"] = $parsed_visibility['response'][0]['periods'][0]['sky'];
        
//rain-divumwx
if($rain["rate"] > 0 && $wind["speed_avg"] > 15){$current["image"] ="img/meteocons/umbrella-wind.svg";}
else if($rain["rate"] > 10){$current["image"] ="img/meteocons/umbrella-wind-alt.svg";}
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
if($rain["rate"] > 0 && $wind["speed_avg"] > 15){$sky["summary"] = "Rain Showers Windy Conditions";}
else if($rain["rate"] >= 20){$sky["summary"] = "Flooding Possible";}
else if($rain["rate"] >= 10){$sky["summary"] ="Heavy Rain";}
else if($rain["rate"] >= 5){$sky["summary"] = "Moderate Rain";}
else if($rain["rate"] >= 1){$sky["summary"] = "Steady Rain";}
else if($rain["rate"] > 0){$sky["summary"] = "Light Rain";}
// snow-divumwx
else if($snow >= 2){$sky["summary"] = "Heavy Snow";}
else if($snow >= 1){$sky["summary"] = "Moderate Snow";}
else if($snow > 0){$sky["summary"] = "Light Snow";}
//fog-divumwx
else if($temp["outside_now"] - $dew["now"] < 0.5 && $dayPartNatural == "night" && $temp["outside_now"] > 5){$sky["summary"] = "Misty Conditions";}
else if($temp["outside_now"] - $dew["now"] < 0.5 && $temp["outside_now"] > 5){$sky["summary"] = "Misty Conditions";}
//misty-divumwx
else if($temp["outside_now"] - $dew["now"] < 0.8 && $dayPartNatural == "night" && $temp["outside_now"] > 5){$sky["summary"] = "Misty Hazy Conditions";}
else if($temp["outside_now"] - $dew["now"] < 0.8 && $temp["outside_now"] > 5){$sky["summary"] = "Misty Hazy Conditions";}
//windy-divumwx
else if($wind["speed_avg"] >= 40){$sky["summary"] = "Strong Wind Conditions";}
else if($wind["speed_avg"] >= 30){$sky["summary"] = "Very Windy Conditions";}
else if($wind["speed_avg"] >= 22){$sky["summary"] = "Moderate Wind Conditions";}
else if($wind["speed_avg"] >= 15){$sky["summary"] = "Breezy Conditions";}
//cloud-description
else if($sky["cloud_cover"] < 7 && $sky["cloud_cover"] > 0) {$sky["summary"] = "Clear Sky";}
else if ($sky["cloud_cover"] < 7 && $sky["cloud_cover"] > 0) {
if ($dayPartNatural == "night" ){$sky["summary"] = "Clear Sky";} 
else $sky["summary"] = "Clear Sky";
} 
else if ($sky["cloud_cover"] < 32) {
if ($dayPartNatural == "night"){$sky["summary"] = "Mostly Clear Conditions";} 
else $sky["summary"] = "Mostly Sunny Conditions";
}
else if($sky["cloud_cover"] < 70) {$sky["summary"] = "Partly Cloudy Conditions";}
else if($sky["cloud_cover"] < 95) {$sky["summary"] = "Mostly Cloudy Conditions";}
else if($sky["cloud_cover"] >= 95) {$sky["summary"] = "Overcast Conditions";}
else if(filesize('jsondata/me.txt') < 160){$sky["summary"] = "Conditions Not Available";} 
//oktas
if($sky["cloud_cover"] < 5 && $sky["cloud_cover"] > 0) {$sky["cloud_oktas"] = "0 oktas";}
else if($sky["cloud_cover"] <= 12.5) {$sky["cloud_oktas"] = "1 okta";}
else if($sky["cloud_cover"] <= 25) {$sky["cloud_oktas"] = "2 oktas";}
else if($sky["cloud_cover"] <= 37.5) {$sky["cloud_oktas"] = "3 oktas";}
else if($sky["cloud_cover"] <= 50) {$sky["cloud_oktas"] = "4 oktas";}
else if($sky["cloud_cover"] <= 62.5) {$sky["cloud_oktas"] = "5 oktas";}
else if($sky["cloud_cover"] <= 75) {$sky["cloud_oktas"] = "6 oktas";}
else if($sky["cloud_cover"] <= 87.5) {$sky["cloud_oktas"] = "7 oktas";}
else if($sky["cloud_cover"] <= 100) {$sky["cloud_oktas"] = "8 oktas";}
?>

<script src="js/d3.7.9.0.min.js"></script>

<div class="current-module"></div>

<script>

var baseTextColor = "var(--col-6)";
    
var currentImage = "<?php echo $current["image"];?>";

var bearing = "<?php echo $wind["direction_10m_avg"];?>";

if (bearing <= 11.25) {
    bearing = 'North';
} else if (bearing <= 33.75) {
    bearing = 'NNE';
} else if (bearing <= 56.25) {
    bearing = 'NE';
} else if (bearing <= 78.75) {
    bearing = 'ENE';
} else if (bearing <= 101.25) {
    bearing = 'East';
} else if (bearing <= 123.75) {
    bearing = 'ESE';
} else if (bearing <= 146.25) {
    bearing = 'SE';
} else if (bearing <= 168.75) {
    bearing = 'SSE';
} else if (bearing <= 191.25) {
    bearing = 'South';
} else if (bearing <= 213.75) {
    bearing = 'SSW';
} else if (bearing <= 236.25) {
    bearing = 'SW';
} else if (bearing <= 261.25) {
    bearing = 'WSW';
} else if (bearing <= 281.25) {
    bearing = 'West';
} else if (bearing <= 303.75) {
    bearing = 'WNW';
} else if (bearing <= 326.25) {
    bearing = 'NW';
} else if (bearing <= 348.75) {
    bearing = 'NNW';
} else { bearing = 'North'; }

var tempColor = "<?php echo $color["outTemp_60min_avg"];?>";
var avg_sp_ten_min_color = "<?php echo $color["windSpeed_10min_avg"];?>";
var max_gust_ten_min_color = "<?php echo $color["windGust_10min_max"];?>";
var colorRain = "<?php echo $colorRainDaySum;?>";

var windunits = "<?php echo $wind['units'];?>";
var rainunits = "<?php echo $rain['units'];?>";
var tempunits = "<?php echo $temp['units'];?>"; 

var svg = d3.select(".current-module")
    .append("svg")
    //.style("background", "red")
    .attr("width", 310)
    .attr("height", 150);

var data = ["<?php echo $sky["summary"];?>"];

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 155)
    .attr("y", function(d, i) { return 40 + i * 40; })

    .style("fill", baseTextColor)
    .style("font-family", "Helvetica") 
    .style("font-size", "11px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[0]; });

svg.append('image') // image output
    .attr('xlink:href', currentImage)

    .attr('height', 95)
    .attr('x', 117)
    .attr('y', 40);

var visibility = "<?php echo $visibility;?>";

svg.append("text")
    .attr("x", 283)
    .attr("y", 70)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("Visibility");

svg.append("text")
    .attr("x", 283)
    .attr("y", 80)
    .style("fill", "#ff7c39")
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(visibility);

var cloud_cover = "<?php echo $sky["cloud_cover"];?>";
var cloud_oktas = "<?php echo $sky["cloud_oktas"];?>";

svg.append("text")
    .attr("x", 35)
    .attr("y", 70)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("Cloud Cover");

svg.append("text")
    .attr("x", 35)
    .attr("y", 80)
    .style("fill", "#ff7c39")
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(cloud_cover + "% " + " " + cloud_oktas);

var outside_day_avg_sixtyM = "<?php echo $temp["outside_day_avg_60mn"];?>";

svg.append("text")
    .attr("x", 37)
    .attr("y", 10)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("60min Temp Avg");

svg.append("text")
    .attr("x", 37)
    .attr("y", 20)
    .style("fill", tempColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(d3.format(".1f")(outside_day_avg_sixtyM) + "\u00B0" + tempunits);

var gust_tenM_max = <?php echo $wind["gust_10m_max"];?>;

svg.append("text")
    .attr("x", 37)
    .attr("y", 136)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("10min Gust Max");

svg.append("text")
    .attr("x", 37)
    .attr("y", 146)
    .style("fill", max_gust_ten_min_color)
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(d3.format(".1f")(gust_tenM_max) + " " + windunits);

var speed_tenM_avg = <?php echo $wind["speed_10m_avg"];?>;

svg.append("text")
    .attr("x", 272)
    .attr("y", 136)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("10min Speed Avg");

svg.append("text")
    .attr("x", 272)
    .attr("y", 146)
    .style("fill", avg_sp_ten_min_color)
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(d3.format(".1f")(speed_tenM_avg) + " " + windunits);

var rain_last_tenM = "<?php echo $rain["last_10min"];?>";

svg.append("text")
    .attr("x", 272)
    .attr("y", 10)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("10min Rainfall");

svg.append("text")
    .attr("x", 272)
    .attr("y", 20)
    .style("fill", colorRain)
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(d3.format(".1f")(rain_last_tenM) + " " + rainunits);

var direction_tenM_avg = "<?php echo $wind["direction_10m_avg"];?>";

svg.append("text")
    .attr("x", 155)
    .attr("y", 136)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("10min Wind Dir");

svg.append("text")
    .attr("x", 143.5)
    .attr("y", 146)
    .style("fill", "#007FFF")
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(bearing);

svg.append("text")
    .attr("x", 166.5)
    .attr("y", 146)
    .style("fill", "#ff7c39")
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(direction_tenM_avg + "\u00B0");

var cloudbase = "<?php echo $sky["cloud_base"]." ".$distance;?>";
var data = ["Cloud Base " + "-" + cloudbase];

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 155)
    .attr("y", function(d, i) { return 10 + i * 10; })

    .style("fill", baseTextColor)
    .style("font-family", "Helvetica") 
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[0]; })

    .append("tspan")
    .style("fill", "#ff7c39")
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[1]; });

</script>
</body>
</html>