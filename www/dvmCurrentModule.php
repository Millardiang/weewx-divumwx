<?php
include('dvmCombinedData.php');
$cloud_region = explode("/", $TZ);
error_reporting(0);
?>

<!DOCTYPE html>
<html>
<title>weather current conditions</title>

<div class="chartforecast">
<span class="yearpopup"><a alt="Airport Metar Station" title="Airport Metar Station" href="dvmMenuMetarPopup.php" data-lity><?php echo $chartinfo;?><?php echo ' Airport | Metar';?>
<?php if (filesize('jsondata/me.txt') < 160) { echo "&nbsp;" , $offline;} else echo "";?></a></span>
<span class="monthpopup"><a href="dvmWindyRadarPopup.php" title="Windy.com Radar" alt="Windy.com Radar" data-lity><?php echo $chartinfo;?> Radar</a></span>
<span class="monthpopup"><a href="dvmWindyWindPopup.php" title="Windy.com Wind Map" alt="Windy.com Wind Map" data-lity><?php echo $chartinfo;?> Wind Map</a></span>
<span class="todaypopup"><a alt="cloud cover" title="cloud cover" href="<?php echo $chartsource;?>/<?php echo $theme;?>-charts.html?chart='cloudcoverplot'&span='weekly'&temp='<?php 
echo $temp['units'];?>'&pressure='<?php echo $barom['units'];?>'&wind='<?php echo $wind['units'];?>'&rain='<?php echo $rain['units'];?>" data-lity><?php echo $menucharticonpage;?> Cloud Cover</a></span>
</div>
<span class='moduletitle'><?php echo $lang['currentModule'];?></span>

<div class="updatedtimecurrent">
<?php $forecastime=filemtime('jsondata/awc.txt');$divumwxwuurl=file_get_contents("jsondata/awc.txt");if(filesize('jsondata/awc.txt')<10){echo $offline;}
else echo $online,"";echo " ",	date($timeFormat,$forecastime);	?>    
</div>

<div class="cloudconverter">
<?php //cloudbase
$clouds = "Cloudbase";
if ($windunit == 'mph' || $windunit == 'kts'){$sky["cloud_base"] = round($sky["cloud_base"] * 3.281);}
if ($windunit == 'mph' || $windunit == 'kts'){$distance = "ft";}
else if ($windunit == 'km/h' || $windunit == 'm/s'){$distance = "m";}
if ($sky["cloud_base"] > 0) {
if ($windunit == 'mph' || $windunit == 'kts' && $sky["cloud_base"]>=1999){echo "<div class=cloudconvertercircle2000>".$clouds."<tyellow> ".$sky["cloud_base"]."</tyellow><smalltempunit2> ".$distance."</tblue><smalltempunit2>" ;}
else if ($windunit == 'mph' || $windunit == 'kts' && $sky["cloud_base"]<1999){echo "<div class=cloudconvertercircle2000>".$clouds."<tblue> ".$sky["cloud_base"]."</tblue><smalltempunit2> ".$distance."</tblue><smalltempunit2>" ;}
else if ($windunit == 'km/h' || $windunit == 'm/s' && $sky["cloud_base"]>=609){echo "<div class=cloudconvertercircle2000>".$clouds."<tyellow> ".$sky["cloud_base"]."</tyellow><smalltempunit2> ".$distance."</tblue><smalltempunit2>" ;}
else if ($windunit == 'km/h' || $windunit == 'm/s' && $sky["cloud_base"]<609){echo "<div class=cloudconvertercircle2000>".$clouds."<tblue> ".$sky["cloud_base"]."</tblue><smalltempunit2> ".$distance."</tblue><smalltempunit2>" ;}}
?>
</div></div>

<?php 
$json_visibility = file_get_contents("jsondata/awc.txt");
$parsed_visibility = json_decode($json_visibility, true);
$temp["units_label"] = $sdata["unit.label.outTemp"];

if ($windunit =='mph'){
$visibility = round($parsed_visibility['response'][0]['periods'][0]['visibilityMI'],0,PHP_ROUND_HALF_UP)." miles";
}
else
{
$visibility = round($parsed_visibility['response'][0]['periods'][0]['visibilityKM'],0,PHP_ROUND_HALF_UP)." km";
}
if ($cloud_region[0] !== "Europe"){$sky["cloud_cover"] = $parsed_visibility['response'][0]['periods'][0]['sky'];}
      
//rain-divumwx
if($rain["rate"]>0 && $wind["speed_avg"]>15){$current["image"]="./img/meteocons/umbrella-wind.svg";}
else if($rain["rate"]>10){$current["image"]="./img/meteocons/umbrella-wind-alt.svg";}
else if($rain["rate"]>0){$current["image"]= "./img/meteocons/overcast-rain.svg";}
//fog-divumwx
else if($temp["outside_now"] -$dew["now"]<0.8 && $dayPartNatural == "night" && $temp["outside_now"]>5){$current["image"]="./img/meteocons/fog-night.svg";}
else if($temp["outside_now"] -$dew["now"]<0.8 && $temp["outside_now"]>5){$current["image"]= "./img/meteocons/fog-day.svg";}
//windy moderate-divumwx
else if($wind["speed_avg"]>=15 && $dayPartNatural == "night" && $sky["cloud_cover"]<20){$current["image"]= "./img/meteocons/wind.svg";}
else if($wind["speed_avg"]>=15 && $sky["cloud_cover"]<20){$current["image"]= "./img/meteocons/wind.svg";}
//windy moderate-divumwx
else if($wind["speed_avg"]>=15 && $dayPartNatural == "night"){$current["image"]="./img/meteocons/wind.svg";}
else if($wind["speed_avg"]>=15){$current["image"]="./img/meteocons/wind.svg";}

//cloud-icon
else if ($sky["cloud_cover"]<7 and $sky["cloud_cover"]>0) {
if ($dayPartNatural == "night" ){$current["image"]="./img/meteocons/clear-night.svg";} 
else $current["image"]="./img/meteocons/clear-day.svg"; 
} 
else if ($sky["cloud_cover"]<32) {
if ($dayPartNatural == "night" ){$current["image"]= "./img/meteocons/mostly-clear-night.svg";} 
else $current["image"]="./img/meteocons/mostly-clear-day.svg"; 
}
else if ($sky["cloud_cover"]<70) {
if ($dayPartNatural == "night" ){$current["image"]= "./img/meteocons/partly-cloudy-night.svg";} 
else $current["image"]= "./img/meteocons/partly-cloudy-day.svg"; 
}
else if ($sky["cloud_cover"]<95) {
if ($dayPartNatural == "night" ){$current["image"]= "./img/meteocons/overcast-night.svg";} 
else $current["image"]= "./img/meteocons/overcast-day.svg"; 
}
else if($sky["cloud_cover"]>=95) {
if ($dayPartNatural == "night" ){$current["image"]="./img/meteocons/overcast.svg";} 
else $current["image"]= "./img/meteocons/overcast.svg"; 
}

//rain-divumwx
if($rain["rate"]>0 && $wind["speed_avg"]>15){$sky["summary"] = "Rain Showers Windy Conditions";}
else if($rain["rate"]>=20){$sky["summary"] = "Flooding Possible";}
else if($rain["rate"]>=10){$sky["summary"] ="Heavy Showers";}
else if($rain["rate"]>=5){$sky["summary"] = "Moderate Rain Showers";}
else if($rain["rate"]>=1){$sky["summary"] = "Steady Showers";}
else if($rain["rate"]>0){$sky["summary"] = "Light Showers";}
//fog-divumwx
else if($temp["outside_now"]-$dew["now"]<0.5 && $dayPartNatural == "night" && $temp["outside_now"]>5){$sky["summary"] =  "Misty Fog Conditions ".$alert."";}
else if($temp["outside_now"]-$dew["now"]<0.5 && $temp["outside_now"]>5){$sky["summary"] = "Misty Fog Conditions ".$alert."";}
//misty-divumwx
else if($temp["outside_now"] - $dew["now"]<0.8 && $dayPartNatural == "night" && $temp["outside_now"]>5){$sky["summary"] = "Fog Hazy Conditions";}
else if($temp["outside_now"] - $dew["now"]<0.8 && $temp["outside_now"]>5){$sky["summary"] = "Misty Hazy Conditions";}
//windy-divumwx
else if($wind["speed_avg"]>=40){$sky["summary"] = "Strong Wind ".$alert." Conditions" ;}
else if($wind["speed_avg"]>=30){$sky["summary"] = "Very Windy ".$alert." Conditions";}
else if($wind["speed_avg"]>=22){$sky["summary"] = "Moderate Wind Conditions";}
else if($wind["speed_avg"]>=15){$sky["summary"] = "Breezy Conditions";}
//cloud-description
else if($sky["cloud_cover"]<7 and $sky["cloud_cover"]>0) {$sky["summary"] = "Clear Conditions";}
else if ($sky["cloud_cover"]<7 and $sky["cloud_cover"]>0) {
if ($dayPartNatural == "night" ){$sky["summary"] = "Clear Conditions";} 
else $sky["summary"] = "Sunny Conditions"; 
} 
else if ($sky["cloud_cover"]<32) {
if ($dayPartNatural == "night" ){$sky["summary"] = "Mostly Clear Conditions";} 
else $sky["summary"] = "Mostly Sunny Conditions"; 
}
else if($sky["cloud_cover"]<70) {$sky["summary"] = "Partly Cloudy Conditions";}
else if($sky["cloud_cover"]<95) {$sky["summary"] = "Mostly Cloudy Conditions";}
else if($sky["cloud_cover"]>=95) {$sky["summary"] = "Overcast Conditions";}
else if(filesize('jsondata/me.txt')<160){$sky["summary"] = "<uppercase>Conditions Not Available</uppercase>";}
//oktas
if($sky["cloud_cover"]<5 and $sky["cloud_cover"]>0) {$sky["cloud_oktas"]="0 oktas";}
else if($sky["cloud_cover"]<=12.5) {$sky["cloud_oktas"]="1 okta";}
else if($sky["cloud_cover"]<=25) {$sky["cloud_oktas"]="2 oktas";}
else if($sky["cloud_cover"]<=37.5) {$sky["cloud_oktas"]="3 oktas";}
else if($sky["cloud_cover"]<=50) {$sky["cloud_oktas"]="4 oktas";}
else if($sky["cloud_cover"]<=62.5) {$sky["cloud_oktas"]="5 oktas";}
else if($sky["cloud_cover"]<=75) {$sky["cloud_oktas"]="6 oktas";}
else if($sky["cloud_cover"]<=87.5) {$sky["cloud_oktas"]="7 oktas";}
else if($sky["cloud_cover"]<=100) {$sky["cloud_oktas"]="8 oktas";}

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
bearing = bearing || 0;

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

// Windy.com color scale
var avg_sp_ten_min_color = <?php echo number_format($wind["speed_10m_avg"],1);?>;
if (units == 'kts') {
    avg_sp_ten_min_color = <?php echo number_format($wind["speed_10m_avg"]* 0.5399570136727677,1);?>;
}

if ((avg_sp_ten_min_color >= 175) && (avg_sp_ten_min_color <= 200)) {
    avg_sp_ten_min_color = '#FFF9E3';
} else if ((avg_sp_ten_min_color >= 155) && (avg_sp_ten_min_color <= 175)) {
    avg_sp_ten_min_color = '#f1ff6c';
} else if ((avg_sp_ten_min_color >= 120) && (avg_sp_ten_min_color <= 155)) {
    avg_sp_ten_min_color = '#c1fc77';
} else if ((avg_sp_ten_min_color >= 100) && (avg_sp_ten_min_color <= 120)) {
    avg_sp_ten_min_color = '#45698d';
} else if ((avg_sp_ten_min_color >= 90) && (avg_sp_ten_min_color <= 100)) {
    avg_sp_ten_min_color = '#754a92';
} else if ((avg_sp_ten_min_color >= 75) && (avg_sp_ten_min_color <= 90)) {
    avg_sp_ten_min_color = '#af5088';
} else if ((avg_sp_ten_min_color >= 60) && (avg_sp_ten_min_color <= 75)) {
    avg_sp_ten_min_color = '#d20032';
} else if ((avg_sp_ten_min_color >= 50) && (avg_sp_ten_min_color <= 60)) {
    avg_sp_ten_min_color = '#c8420d';
} else if ((avg_sp_ten_min_color >= 40) && (avg_sp_ten_min_color <= 50)) {
    avg_sp_ten_min_color = '#c2863e';
} else if ((avg_sp_ten_min_color >= 30) && (avg_sp_ten_min_color <= 40)) {
    avg_sp_ten_min_color = '#39a239';
} else if ((avg_sp_ten_min_color >= 20) && (avg_sp_ten_min_color <= 30)) {
    avg_sp_ten_min_color = '#0f94a7';
} else if ((avg_sp_ten_min_color >= 10) && (avg_sp_ten_min_color <= 20)) {
    avg_sp_ten_min_color = '#6e90d0';
} else if ((avg_sp_ten_min_color >= 5) && (avg_sp_ten_min_color <= 10)) {
    avg_sp_ten_min_color = '#7e98bb';
} else if ((avg_sp_ten_min_color >= 0) && (avg_sp_ten_min_color <= 5)) {
    avg_sp_ten_min_color = '#85a3aa'; }

// Windy.com color scale
var max_gust_ten_min_color = <?php echo number_format($wind["gust_10m_max"],1);?>;
if (units == 'kts') {
    max_gust_ten_min_color = <?php echo number_format($wind["gust_10m_max"]* 0.5399570136727677,1);?>;
}
if ((max_gust_ten_min_color >= 175) && (max_gust_ten_min_color <= 200)) {
    max_gust_ten_min_color = '#FFF9E3';
} else if ((max_gust_ten_min_color >= 155) && (max_gust_ten_min_color <= 175)) {
    max_gust_ten_min_color = '#f1ff6c';
} else if ((max_gust_ten_min_color >= 120) && (max_gust_ten_min_color <= 155)) {
    max_gust_ten_min_color = '#c1fc77';
} else if ((max_gust_ten_min_color >= 100) && (max_gust_ten_min_color <= 120)) {
    max_gust_ten_min_color = '#45698d';
} else if ((max_gust_ten_min_color >= 90) && (max_gust_ten_min_color <= 100)) {
    max_gust_ten_min_color = '#754a92';
} else if ((max_gust_ten_min_color >= 75) && (max_gust_ten_min_color <= 90)) {
    max_gust_ten_min_color = '#af5088';
} else if ((max_gust_ten_min_color >= 60) && (max_gust_ten_min_color <= 75)) {
    max_gust_ten_min_color = '#d20032';
} else if ((max_gust_ten_min_color >= 50) && (max_gust_ten_min_color <= 60)) {
    max_gust_ten_min_color = '#c8420d';
} else if ((max_gust_ten_min_color >= 40) && (max_gust_ten_min_color <= 50)) {
    max_gust_ten_min_color = '#c2863e';
} else if ((max_gust_ten_min_color >= 30) && (max_gust_ten_min_color <= 40)) {
    max_gust_ten_min_color = '#39a239';
} else if ((max_gust_ten_min_color >= 20) && (max_gust_ten_min_color <= 30)) {
    max_gust_ten_min_color = '#0f94a7';
} else if ((max_gust_ten_min_color >= 10) && (max_gust_ten_min_color <= 20)) {
    max_gust_ten_min_color = '#6e90d0';
} else if ((max_gust_ten_min_color >= 5) && (max_gust_ten_min_color <= 10)) {
    max_gust_ten_min_color = '#7e98bb';
} else if ((max_gust_ten_min_color >= 0) && (max_gust_ten_min_color <= 5)) {
    max_gust_ten_min_color = '#85a3aa'; }

var tempColor = "<?php echo $tempcolor;?>";
var units = "<?php echo $windunit;?>";

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
    .attr("y", function(d, i) { return 40 + i * 40; })

    .style("fill", baseTextColor)
    .style("font-family", "Helvetica") 
    .style("font-size", "11px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[0]; });

svg.append('image') // image output
    .attr('xlink:href', currentImage)
    .attr('width', 70)
    .attr('height', 55)
    .attr('x', 10)
    .attr('y', 10);

var data = ["Visibility "+"-"+"<?php echo $visibility;?>"];

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 155)
    .attr("y", function(d, i) { return 71 + i * 71; })

    .style("fill", baseTextColor)
    .style("font-family", "Helvetica") 
    .style("font-size", "11px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[0]; })

    .append("tspan")
    .style("fill", "#ff7c39")
    .style("font-weight", "bold")
    .text(function(d) { return d.split("-")[1]; });

var data = ["Cloud Cover "+"-"+"<?php echo $sky["cloud_cover"];?> "+"-"+"% "+"-"+"(<?php echo $sky["cloud_oktas"];?>)"];

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 155)
    .attr("y", function(d, i) { return 83 + i * 83; })

    .style("fill", "#01a4b4")
    .style("font-family", "Helvetica") 
    .style("font-size", "11px")
    .style("text-anchor", "middle")
    .style("font-weight", "bold")
    .text(function(d) { return d.split("-")[0]; })

    .append("tspan")
    .style("fill", "#ff7c39")
    .text(function(d) { return d.split("-")[1]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[2]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .text(function(d) { return d.split("-")[3]; });

var data = ["Average "+"-"+"Temperature "+"-"+"last 60 minutes "+"-"+"<?php echo $temp["outside_day_avg_60mn"];?>"+"-"+"°"+"<?php echo $tempunit;?>"];

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 155)
    .attr("y", function(d, i) { return 95 + i * 95; })

    .style("fill", baseTextColor)
    .style("font-family", "Helvetica") 
    .style("font-size", "11px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[0]; })

    .append("tspan")
    .style("fill", tempColor)
    .style("font-weight", "bold")
    .text(function(d) { return d.split("-")[1]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[2]; })

    .append("tspan")
    .style("fill", tempColor)
    .style("font-weight", "bold")
    .text(function(d) { return d.split("-")[3]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .style("font-weight", "normal")
    .style("font-size", "9px")
    .text(function(d) { return d.split("-")[4]; });

var gust_10m_max = <?php echo number_format($wind["gust_10m_max"],1);?>;

var data = ["Max "+"-"+"Wind Gust "+"-"+"last 10 minutes "+"-"+ d3.format(".1f")(gust_10m_max) +"-"+" <?php echo $windunit;?>"];

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 155)
    .attr("y", function(d, i) { return 107 + i * 107; })

    .style("fill", baseTextColor)
    .style("font-family", "Helvetica") 
    .style("font-size", "11px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[0]; })

    .append("tspan")
    .style("fill", max_gust_ten_min_color)
    .style("font-weight", "bold")
    .text(function(d) { return d.split("-")[1]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[2]; })

    .append("tspan")
    .style("fill", max_gust_ten_min_color)
    .style("font-weight", "bold") 
    .text(function(d) { return d.split("-")[3]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .style("font-weight", "normal")
    .style("font-size", "9px")
    .text(function(d) { return d.split("-")[4]; });

var speed_10m_avg = <?php echo number_format($wind["speed_10m_avg"],1);?>;

var data = ["Average "+"-"+"Wind Speed "+"-"+"last 10 minutes "+"-"+ d3.format(".1f")(speed_10m_avg) +"-"+" <?php echo $windunit;?>"];

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 155)
    .attr("y", function(d, i) { return 120 + i * 120; })

    .style("fill", baseTextColor)
    .style("font-family", "Helvetica") 
    .style("font-size", "11px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[0]; })

    .append("tspan")
    .style("fill", avg_sp_ten_min_color)
    .style("font-weight", "bold")
    .text(function(d) { return d.split("-")[1]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[2]; })

    .append("tspan")
    .style("fill", avg_sp_ten_min_color)
    .style("font-weight", "bold")
    .text(function(d) { return d.split("-")[3]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .style("font-weight", "normal")
    .style("font-size", "9px")
    .text(function(d) { return d.split("-")[4]; });

var data = ["Average "+"-"+"Wind Dir "+"-"+"last 10 minutes "+"-"+ bearing +"-"+" <?php echo $wind["direction_10m_avg"];?>"+"°"];

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 155)
    .attr("y", function(d, i) { return 133 + i * 133; })

    .style("fill", baseTextColor)
    .style("font-family", "Helvetica") 
    .style("font-size", "11px")
    .style("text-anchor", "middle")
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

var data = ["Rainfall "+"-"+"last 10 minutes "+"-"+"<?php echo $rain["last_10min"];?> "+"-"+"<?php echo $rainunit;?>"];

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 155)
    .attr("y", function(d, i) { return 146 + i * 146; })

    .style("fill", "#01a4b4")
    .style("font-family", "Helvetica") 
    .style("font-size", "11px")
    .style("text-anchor", "middle")
    .style("font-weight", "bold")
    .text(function(d) { return d.split("-")[0]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[1]; })

    .append("tspan")
    .style("fill", "#01a4b4")
    .style("font-weight", "bold")
    .text(function(d) { return d.split("-")[2]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .style("font-weight", "normal")
    .style("font-size", "9px")
    .text(function(d) { return d.split("-")[3]; });

</script>
</html>