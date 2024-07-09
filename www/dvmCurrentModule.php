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

<div class="chartforecast">
<span class="yearpopup"><a alt="Airport Metar Station" title="Airport Metar Station" href="dvmMetarPopup.php" data-lity><?php echo $chartinfo;?><?php echo ' Airport | Metar';?>
<?php if (filesize('jsondata/me.txt') < 160) { echo "&nbsp;" , $offline;} else echo "";?></a></span>
<span class="monthpopup"><a href="dvmWindyRadarPopup.php" title="Windy.com Radar" alt="Windy.com Radar" data-lity><?php echo $chartinfo;?> Radar</a></span>
<span class="monthpopup"><a href="dvmWindyWindPopup.php" title="Windy.com Wind Map" alt="Windy.com Wind Map" data-lity><?php echo $chartinfo;?> Wind Map</a></span>
<span class="todaypopup"><a alt="cloud cover" title="cloud cover" href="dvmhighcharts/cloudCharts.php?chart='cloudcoverplot'&span='weekly'&temp='<?php echo $temp['units'];?>'&pressure='<?php echo $barom['units'];?>'&wind='<?php echo $wind['units'];?>'&rain='<?php echo $rain['units']?>
" data-lity><?php echo $menucharticonpage;?> Cloud Cover</a></span>
</div>
<span class='moduletitle'><?php echo $lang['currentModule'];?></span>

<div class="updatedtimecurrent">
<?php $forecastime=filemtime('jsondata/awc.txt');$divumwxwuurl=file_get_contents("jsondata/awc.txt");if(filesize('jsondata/awc.txt')<10){echo $offline;}
else echo $online,"";echo " ",  date($timeFormat,$forecastime);?>    
</div></div>

<?php //cloudbase
$clouds = "Cloudbase";
if ($windunit == 'mph' || $windunit == 'kts'){$sky["cloud_base"] = round($sky["cloud_base"] * 3.281);}
if ($windunit == 'mph' || $windunit == 'kts'){$distance = "ft";}
else if ($windunit == 'km/h' || $windunit == 'm/s'){$distance = "m";}
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
$visibility = round($parsed_visibility['response'][0]['periods'][0]['visibilityMI'],0,PHP_ROUND_HALF_UP)." miles";
} else {
$visibility = round($parsed_visibility['response'][0]['periods'][0]['visibilityKM'],0,PHP_ROUND_HALF_UP)." km";
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
if($sky["cloud_cover"] < 5 && $sky["cloud_cover"] > 0) {$sky["cloud_oktas"] = "0 oktas";}
else if($sky["cloud_cover"] <= 12.5) {$sky["cloud_oktas"] = "1 okta";}
else if($sky["cloud_cover"] <= 25) {$sky["cloud_oktas"] = "2 oktas";}
else if($sky["cloud_cover"] <= 37.5) {$sky["cloud_oktas"] = "3 oktas";}
else if($sky["cloud_cover"] <= 50) {$sky["cloud_oktas"] = "4 oktas";}
else if($sky["cloud_cover"] <= 62.5) {$sky["cloud_oktas"] = "5 oktas";}
else if($sky["cloud_cover"] <= 75) {$sky["cloud_oktas"] = "6 oktas";}
else if($sky["cloud_cover"] <= 87.5) {$sky["cloud_oktas"] = "7 oktas";}
else if($sky["cloud_cover"] <= 100) {$sky["cloud_oktas"] = "8 oktas";}

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
?>

<script src="js/d3.min.js"></script>

<style>
.current {
    position: relative; 
    margin-top: 5px; 
    margin-left: 0px;
    }  
</style>

<script>            
    var theme = "<?php echo $theme;?>";
    if (theme === 'dark') {
    var baseTextColor = "silver";
    } else {
    var baseTextColor = "#2d3a4b";
    }
</script>

<div class="current"></div>

<script>

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

var tempColor = "<?php echo $tempcolor;?>";
var avg_sp_ten_min_color = "<?php echo $color["windSpeed_10min_avg"];?>";
var max_gust_ten_min_color = "<?php echo $color["windGust_10min_max"];?>";

var windunits = "<?php echo $wind['units'];?>";
var rainunits = "<?php echo $rain['units'];?>";
var tempunits = "<?php echo $temp['units'];?>"; 

var svg = d3.select(".current")
    .append("svg")
    //.style("background", "#292E35")
    .attr("width", 310)
    .attr("height", 150);
 
var data = ["<?php echo $sky["summary"];?>"];

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 155)
    .attr("y", function(d, i) { return 20 + i * 20; })

    .style("fill", baseTextColor)
    .style("font-family", "Helvetica") 
    .style("font-size", "14px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[0]; });

svg.append('image') // image output
    .attr('xlink:href', currentImage)
    .attr('width', 130)
    .attr('height', 110)
    .attr('x', 10)
    .attr('y', 0);

var visibility = "<?php echo $visibility;?>";

var data = ["Visibility " + "-" + visibility];

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 300)
    .attr("y", function(d, i) { return 48 + i * 48; })

    .style("fill", baseTextColor)
    .style("font-family", "Helvetica") 
    .style("font-size", "11px")
    .style("text-anchor", "end")
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[0]; })

    .append("tspan")
    .style("fill", "#ff7c39")
    .style("font-weight", "bold")
    .text(function(d) { return d.split("-")[1]; });

var cloudbase = "<?php echo $sky["cloud_base"];?>";
var cloudbase_units = "<?php echo $distance;?>";

var data = ["Cloudbase " + "-" + cloudbase + "-" + " " + cloudbase_units];

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 300)
    .attr("y", function(d, i) { return 61 + i * 61; })

    .style("fill", baseTextColor)
    .style("font-family", "Helvetica") 
    .style("font-size", "11px")
    .style("text-anchor", "end")
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[0]; })

    .append("tspan")
    .style("fill", "#ff7c39")
    .style("font-weight", "bold")
    .text(function(d) { return d.split("-")[1]; })

    .append("tspan")
    .style("fill", "#ff7c39")
    .style("font-weight", "bold")
    .text(function(d) { return d.split("-")[2]; });

var cloud_cover = "<?php echo $sky["cloud_cover"];?>";

var cloud_oktas = "<?php echo $sky["cloud_oktas"];?>";

var data = ["Cloud Cover " + "-" + cloud_cover + "-" + " % " + "-" + "(" + cloud_oktas + ")"];

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 300)
    .attr("y", function(d, i) { return 74 + i * 74; })

    .style("fill", baseTextColor)
    .style("font-family", "Helvetica") 
    .style("font-size", "11px")
    .style("text-anchor", "end")
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[0]; })

    .append("tspan")
    .style("font-weight", "bold")
    .style("fill", "#ff7c39")
    .text(function(d) { return d.split("-")[1]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[2]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .text(function(d) { return d.split("-")[3]; });


var rain_last_tenM = "<?php echo $rain["last_10min"];?>";

var data = ["10 min " + "-" + "Rainfall " + "-" + "Total " + "-" + d3.format(".1f")(rain_last_tenM) + " " + "-" + rainunits];

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 300)
    .attr("y", function(d, i) { return 87 + i * 87; })

    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "11px")
    .style("text-anchor", "end")
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[0]; })

    .append("tspan")
    .style("fill", "#01a4b4")       
    .style("font-weight", "bold")
    .text(function(d) { return d.split("-")[1]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[2]; })

    .append("tspan")
    .style("fill", "#01a4b4")
    .style("font-weight", "bold")
    .text(function(d) { return d.split("-")[3]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .style("font-weight", "normal")
    .style("font-size", "9px")
    .text(function(d) { return d.split("-")[4]; });

var outside_day_avg_sixtyM = "<?php echo $temp["outside_day_avg_60mn"];?>";

var data = ["1 hr " + "x" + "Temperature " + "x" + "Average " + "x" + d3.format(".1f")(outside_day_avg_sixtyM) + "x" + "\u00B0" + tempunits];

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 300)
    .attr("y", function(d, i) { return 100.5 + i * 100.5; })

    .style("fill", baseTextColor)
    .style("font-family", "Helvetica") 
    .style("font-size", "11px")
    .style("text-anchor", "end")
    .style("font-weight", "normal")
    .text(function(d) { return d.split("x")[0]; })

    .append("tspan")
    .style("fill", tempColor)
    .style("font-weight", "bold")
    .text(function(d) { return d.split("x")[1]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .style("font-weight", "normal")
    .text(function(d) { return d.split("x")[2]; })

    .append("tspan")
    .style("fill", tempColor)
    .style("font-weight", "bold")
    .text(function(d) { return d.split("x")[3]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .style("font-weight", "normal")
    .style("font-size", "9px")
    .text(function(d) { return d.split("x")[4]; });

var direction_tenM_avg = "<?php echo $wind["direction_10m_avg"];?>";
direction_tenM_avg = direction_tenM_avg || 0;

var data = ["10 min " + "-" + "Wind Dir " + "-" + "Average " + "-" + bearing + "-" + " " + direction_tenM_avg + "\u00B0"];

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 300)
    .attr("y", function(d, i) { return 114.5 + i * 114.5; })

    .style("fill", baseTextColor)
    .style("font-family", "Helvetica") 
    .style("font-size", "11px")
    .style("text-anchor", "end")
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[0]; })

    .append("tspan")
    .style("fill", "#007FFF")
    .style("font-weight", "bold")
    .text(function(d) { return d.split("-")[1]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[2]; })

    .append("tspan")
    .style("fill", "#ff7c39")
    .style("font-weight", "bold")
    .text(function(d) { return d.split("-")[3]; })

    .append("tspan")
    .style("fill", "#007FFF")
    .text(function(d) { return d.split("-")[4]; });

var speed_tenM_avg = <?php echo $wind["speed_10m_avg"];?>;

var gust_tenM_max = <?php echo $wind["gust_10m_max"];?>;

var data = ["10 min Wind: -" + "-" + "Speed " + "-" + "Avg " + "-" + d3.format(".1f")(speed_tenM_avg) + "-" + " " + windunits + ", " + "-" + "Gust " + "-" + "Max " + "-" + d3.format(".1f")(gust_tenM_max) + "-" + " " + windunits];

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 300)
    .attr("y", function(d, i) { return 128.5 + i * 128.5; }) // 114.5

    .style("fill", baseTextColor)
    .style("font-family", "Helvetica") 
    .style("font-size", "11px")
    .style("text-anchor", "end")
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[0]; })

    .append("tspan")
    .style("fill", avg_sp_ten_min_color)
    .style("font-weight", "bold")
    .text(function(d) { return d.split("-")[1]; })

    .append("tspan")
    .style("fill", avg_sp_ten_min_color)
    .style("font-weight", "bold")
    .text(function(d) { return d.split("-")[2]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[3]; })

    .append("tspan")
    .style("fill", avg_sp_ten_min_color)
    .style("font-weight", "bold")
    .text(function(d) { return d.split("-")[4]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .style("font-weight", "normal")
    .style("font-size", "9px")
    .text(function(d) { return d.split("-")[5]; })

    .append("tspan")
    .style("fill", max_gust_ten_min_color)
    .style("font-weight", "bold")
    .style("font-size", "11px")
    .text(function(d) { return d.split("-")[6]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .style("font-weight", "normal")
    .style("font-size", "11px")
    .text(function(d) { return d.split("-")[7]; })

    .append("tspan")
    .style("fill", max_gust_ten_min_color)
    .style("font-weight", "bold") 
    .text(function(d) { return d.split("-")[8]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .style("font-weight", "normal")
    .style("font-size", "9px")
    .text(function(d) { return d.split("-")[9]; });

var sunshine_duration = "<?php echo $solar["sun_duration_hours_minutes"];?>";

var threshold = "<?php echo $solar["threshold"];?>";

var solarunits = "W/m²";

var data = ["Sunshine Duration " + "-" + sunshine_duration + "-" + " Radiation Threshold " + "-" + threshold + solarunits];

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 300)
    .attr("y", function(d, i) { return 142.5 + i * 142.5; }) // 114.5

    .style("fill", baseTextColor)
    .style("font-family", "Helvetica") 
    .style("font-size", "11px")
    .style("text-anchor", "end")
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[0]; })

    .append("tspan")
    .style("fill", "red")
    .style("font-weight", "bold")
    .text(function(d) { return d.split("-")[1]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[2]; })

    .append("tspan")
    .style("fill", "Green")
    .style("font-weight", "bold")
    .text(function(d) { return d.split("-")[3]; })

    .append("tspan")
    .style("fill", "red")
    .style("font-weight", "bold")
    .text(function(d) { return d.split("-")[4]; })

    
</script>
</html>
