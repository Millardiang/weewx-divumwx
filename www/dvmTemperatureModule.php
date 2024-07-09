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
#####################################################################################################################
#
# Thermometer image based on an idea by David Banks
#
# Copyright (c) 2023 by David Banks (https://codepen.io/davidbanks/pen/LYBrNb)
#
# Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated 
# documentation files (the "Software"), to deal in the Software without restriction, ncluding without limitation
# the rights to use, copy, modify, merge, # publish, distribute, sublicense, and/or sell copies of the Software,
# and to permit persons to whom the Software is furnished to do so,subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in all copies or substantial portions
# of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED
# TO THE WARRANTIES OF # # MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
# THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE # FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
# CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN # # # CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
# DEALINGS IN THE SOFTWARE.
#
#####################################################################################################################                                                                                 
?>
<!DOCTYPE html>
<title>divumwx current conditions</title>
<style>
.moduletitle4 {
  position: relative;
  top: -21px;
  font-size: .8em;
  float: none;
}
.chartforecast4 {
  position: absolute;
  font-family: arial, system;
  z-index: 20;
  padding-top: 1px;
  margin-left: 0;
  font-size: .67em;
  color: silver;
  margin-top: 159px;
  width: 300px;
  padding-left: 10px;
  text-align: left
}
.chartforecast4:hover {
  color: #90b12a
}
</style>
<?php

include('dvmCombinedData.php');
$yearMonth = date("Y-m");
if ($theme === "dark")
{$bordercolor = "#393d40";}
else if ($theme === "light")
{$bordercolor = "#e9ebf1";}


?>
<div class="chartforecast4">
<span class="yearpopup"><a alt="temp charts" title="temp charts" href="dvmTemperatureRecords.php" data-lity><?php echo $menucharticonpage;?> Temperature Records and Charts</a></span>
<<<<<<< HEAD:www/dvmTemperatureModule(1).php
<span class="yearpopup"><a alt="heat map" title="heat map" href="heatmaps/heatmap-<?php echo $yearMonth;?>.php" data-lity><?php echo $menucharticonpage;?> Heat Map</a></span>
=======
<span class="yearpopup"><a alt="heat map" title="heat map" href="dvmHeatMapPopup.php" data-lity><?php echo $menucharticonpage;?> Heat Map</a></span>
>>>>>>> 05cf9b46c8090e15d2f1791b74d7d24935aab35a:www/dvmTemperatureModule.php
</div>    
<span class='moduletitle4'><?php echo $lang['temperatureModule'];?> (<valuetitleunit>°<?php echo $temp["units"];?></valuetitleunit>)</span>
<div class="updatedtime1"><?php if(file_exists($livedata)&&time()- filemtime($livedata)>300)echo $offline. '<offline> Offline </offline>';else echo $online." ".$divum["time"];?></div>
</div>

<!DOCTYPE html>
<script src='js/d3.min.js'></script>

<style>
.temppos {
  margin-top: -1.5px;
  margin-left: -230px;
}

</style>

<div class="temppos">
<div class="thermometer"></div>
</div>
		
	<script>
		
	var theme = "<?php echo $theme;?>";

	if (theme == 'dark') {

    var width = 80,
    height = 150;
    
    var maxTemp = "<?php echo $temp["outside_day_max"];?>";
    maxTemp = maxTemp || 0;
    
    var minTemp = "<?php echo $temp["outside_day_min"];?>";
    minTemp = minTemp || 0;
    
    var currentTemp = "<?php echo $temp["outside_now"];?>";
    currentTemp = currentTemp || 0;
    
    var mercuryColor = "<?php echo $colorOutTemp;?>";

var bottomY = height - 5,
    topY = 5,
    bulbRadius = 25.5,
    tubeWidth = 25.5,
    tubeBorderWidth = 1,    
    innerBulbColor = "rgb(230, 200, 200)",
    tubeBorderColor = "#999999";

var bulb_cy = bottomY - bulbRadius,
    bulb_cx = width / 2,
    top_cy = topY + tubeWidth / 2;


var svg = d3.select(".thermometer")
  .append("svg")
  //.style("background", "#292E35") // box background to be commented out
  .attr("width", width)
  .attr("height", height);

var defs = svg.append("defs");

// Define the radial gradient for the bulb fill colour
var bulbGradient = defs.append("radialGradient")
  .attr("id", "bulbGradient")
  .attr("cx", "50%")
  .attr("cy", "50%")
  .attr("r", "50%")
  .attr("fx", "50%")
  .attr("fy", "50%");

bulbGradient.append("stop")
  .attr("offset", "0%")
  .style("stop-color", innerBulbColor);

bulbGradient.append("stop")
  .attr("offset", "90%")
  .style("stop-color", mercuryColor); 

svg.append("line")
    .attr("x1", width / 2)
    .attr("x2", width / 2)
    .attr("y1", 15)
    .attr("y2", 120)
    .style("stroke", tubeBorderColor)
    .style("stroke-width", "15.75px")
    .style("fill", "none")    
    .style("stroke-linecap", "round");
    
svg.append("line")
    .attr("x1", width / 2)
    .attr("x2", width / 2)
    .attr("y1", 15)
    .attr("y2", 120)
    .style("stroke", "rgba(45,47,50,1)")
    .style("stroke-width", "14.75px")
    .style("stroke-linecap", "round");
    
    
// Scale step size
var step = 5;

// Determine a suitable range of the temperature scale
var domain = [
  step * Math.floor(minTemp / step),
  step * Math.ceil(maxTemp / step)
  ];

if (minTemp - domain[0] < 0.66 * step)
  domain[0] -= step;

if (domain[1] - maxTemp < 0.66 * step)
  domain[1] += step;

// D3 scale object
var scale = d3.scale.linear()
  .range([bulb_cy - bulbRadius / 2 - 8.5, top_cy])
  .domain(domain);

// Max and min temperature lines
[minTemp, maxTemp].forEach(function(t) {

  var isMax = (t == maxTemp),
      label = (isMax ? "Max" : "Min"),
      textCol = (isMax ? "silver" : "silver"),
      textOffset = (isMax ? - 3 : 3);

  svg.append("line")
    .attr("id", label + "Line")
    .attr("x1", width / 2 + 25 - tubeWidth / 2)
    .attr("x2", width / 2 + 25 + tubeWidth / 2 - 8)
    .attr("y1", scale(t))
    .attr("y2", scale(t))
    .style("stroke", tubeBorderColor)
    .style("stroke-width", "1px")
    .style("stroke-linecap", "round");


  svg.append("text")
    .attr("x", width / 2 + tubeWidth / 2 + 1)
    .attr("y", scale(t) + textOffset)
    .attr("dy", isMax ? null : "0.75em")
    .text(label)
    .style("fill", textCol)
    .style("font-size", "8px");

});

var tubeFill_bottom = bulb_cy,
    tubeFill_top = scale(currentTemp);

// Rect element for the red mercury column
svg.append("rect")
  .attr("x", width / 2 - (tubeWidth - 10) / 2)
  .attr("y", tubeFill_top)
  .attr("width", tubeWidth - 10)
  .attr("height", tubeFill_bottom - tubeFill_top)
  .style("fill", mercuryColor);

// Main thermometer bulb fill
svg.append("circle")
  .attr("r", bulbRadius - 6)
  .attr("cx", bulb_cx)
  .attr("cy", bulb_cy)
  .style("fill", "url(#bulbGradient)")
  .style("stroke", mercuryColor)
  .style("stroke-width", "2px");

// Values to use along the scale ticks up the thermometer
var tickValues = d3.range((domain[1] - domain[0]) / step + 1).map(function(v) { return domain[0] + v * step; });

// D3 axis object for the temperature scale
var axis = d3.svg.axis()
  .scale(scale)
  .innerTickSize(7)
  .outerTickSize(0)
  .tickValues(tickValues)
  .orient("left");

// Add the axis to the image
var svgAxis = svg.append("g")
  .attr("id", "tempScale")
  .attr("transform", "translate(" + (width / 2 - tubeWidth / 2) + ", 0)")
  .call(axis);

// Format text labels
svgAxis.selectAll(".tick text")
    .style("fill", "#777777")
    .style("font-size", "8px");

// Set main axis line to no stroke or fill
svgAxis.select("path")
  .style("stroke", "none")
  .style("fill", "none");

// Set the style of the ticks 
svgAxis.selectAll(".tick line")
  .style("stroke", tubeBorderColor)
  .style("stroke-linecap", "round")
  .style("stroke-width", "2px");
  
svg.append("text")
	.text( d3.format(".1f")(currentTemp) )
	.attr("x", width / 2)
	.attr("y", 126)
	.attr("text-anchor", "middle")
	.style("font-size", "18px")
	.style("font-family", "Helvetica")
	.style("font-weight", "600")
	.style("fill", "rgba(30, 32, 36, 1)");
  
  } else {
   
    var width = 80,
    height = 150;
    
    var maxTemp = "<?php echo $temp["outside_day_max"];?>";
    maxTemp = maxTemp || 0;
    
    var minTemp = "<?php echo $temp["outside_day_min"];?>";
    minTemp = minTemp || 0;
    
    var currentTemp = "<?php echo $temp["outside_now"];?>";
    currentTemp = currentTemp || 0;
    
    var mercuryColor = "<?php echo $colorOutTemp;?>";

var bottomY = height - 5,
    topY = 5,
    bulbRadius = 25.5,
    tubeWidth = 25.5,
    tubeBorderWidth = 1,    
    innerBulbColor = "rgb(230, 200, 200)",
    tubeBorderColor = "#999999";

var bulb_cy = bottomY - bulbRadius,
    bulb_cx = width / 2,
    top_cy = topY + tubeWidth / 2;

var svg = d3.select(".thermometer")
  .append("svg")
  //.style("background", "#292E35") // box background to be commented out
  .attr("width", width)
  .attr("height", height);

var defs = svg.append("defs");

// Define the radial gradient for the bulb fill colour
var bulbGradient = defs.append("radialGradient")
  .attr("id", "bulbGradient")
  .attr("cx", "50%")
  .attr("cy", "50%")
  .attr("r", "50%")
  .attr("fx", "50%")
  .attr("fy", "50%");

bulbGradient.append("stop")
  .attr("offset", "0%")
  .style("stop-color", innerBulbColor);

bulbGradient.append("stop")
  .attr("offset", "90%")
  .style("stop-color", mercuryColor); 

svg.append("line")
    .attr("x1", width / 2)
    .attr("x2", width / 2)
    .attr("y1", 15)
    .attr("y2", 120)
    .style("stroke", tubeBorderColor)
    .style("stroke-width", "15.75px")
    .style("fill", "none")    
    .style("stroke-linecap", "round");
    
svg.append("line")
    .attr("x1", width / 2)
    .attr("x2", width / 2)
    .attr("y1", 15)
    .attr("y2", 120)
    .style("stroke", "rgba(230, 232, 239, 1)")
    .style("stroke-width", "14.75px")   
    .style("stroke-linecap", "round");
       
// Scale step size
var step = 5;

// Determine a suitable range of the temperature scale
var domain = [
  step * Math.floor(minTemp / step),
  step * Math.ceil(maxTemp / step)
  ];

if (minTemp - domain[0] < 0.66 * step)
  domain[0] -= step;

if (domain[1] - maxTemp < 0.66 * step)
  domain[1] += step;


// D3 scale object
var scale = d3.scale.linear()
  .range([bulb_cy - bulbRadius / 2 - 8.5, top_cy])
  .domain(domain);


// Max and min temperature lines
[minTemp, maxTemp].forEach(function(t) {

  var isMax = (t == maxTemp),
      label = (isMax ? "Max" : "Min"),
      textCol = (isMax ? "silver" : "silver"),
      textOffset = (isMax ? - 3 : 3);

  svg.append("line")
    .attr("id", label + "Line")
    .attr("x1", width / 2 + 25 - tubeWidth / 2)
    .attr("x2", width / 2 + 25 + tubeWidth / 2 - 8)
    .attr("y1", scale(t))
    .attr("y2", scale(t))
    .style("stroke", tubeBorderColor)
    .style("stroke-width", "1px")
    .style("stroke-linecap", "round");


  svg.append("text")
    .attr("x", width / 2 + tubeWidth / 2 + 1)
    .attr("y", scale(t) + textOffset)
    .attr("dy", isMax ? null : "0.75em")
    .text(label)
    .style("fill", textCol)
    .style("font-size", "8px");

});

var tubeFill_bottom = bulb_cy,
    tubeFill_top = scale(currentTemp);

// Rect element for the red mercury column
svg.append("rect")
  .attr("x", width / 2 - (tubeWidth - 10) / 2)
  .attr("y", tubeFill_top)
  .attr("width", tubeWidth - 10)
  .attr("height", tubeFill_bottom - tubeFill_top)
  .style("fill", mercuryColor);


// Main thermometer bulb fill
svg.append("circle")
  .attr("r", bulbRadius - 6)
  .attr("cx", bulb_cx)
  .attr("cy", bulb_cy)
  .style("fill", "url(#bulbGradient)")
  .style("stroke", mercuryColor)
  .style("stroke-width", "2px");

// Values to use along the scale ticks up the thermometer
var tickValues = d3.range((domain[1] - domain[0]) / step + 1).map(function(v) { return domain[0] + v * step; });

// D3 axis object for the temperature scale
var axis = d3.svg.axis()
  .scale(scale)
  .innerTickSize(7)
  .outerTickSize(0)
  .tickValues(tickValues)
  .orient("left");

// Add the axis to the image
var svgAxis = svg.append("g")
  .attr("id", "tempScale")
  .attr("transform", "translate(" + (width / 2 - tubeWidth / 2) + ",0)")
  .call(axis);

// Format text labels
svgAxis.selectAll(".tick text")
    .style("fill", "#777777")
    .style("font-size", "8px");

// Set main axis line to no stroke or fill
svgAxis.select("path")
  .style("stroke", "none")
  .style("fill", "none");

// Set the style of the ticks 
svgAxis.selectAll(".tick line")
  .style("stroke", tubeBorderColor)
  .style("stroke-linecap", "round")
  .style("stroke-width", "2px");
  
svg.append("text")
	.text( d3.format(".1f")(currentTemp) )
	.attr("x", width / 2)
	.attr("y", 126)
	.attr("text-anchor", "middle")
	.style("font-size", "18px")
	.style("font-family", "Helvetica")
	.style("font-weight", "600")
	.style("fill", "rgba(30, 32, 36, 1)");

}			
   </script>
   
   
<style>
div.temperature {
  
  font-family: weathertext2;
  text-align: center;
  border-collapse: separate;
  border-spacing: 12px 1px;
}
.divTable.temperature .divTableCell, .divTable.temperature .divTableHead {
  padding: 1px 1px;
   
}
.divTable.temperature .divTableBody .divTableCell {
  font-size: .65em;
}

.divTable.temperature .divTableHeading .divTableHead {
  font-size: .7em;
  font-weight: normal;
  text-align: center;
}
/* DivTable.com */
.divTable{ display: table; }
.divTableRow { display: table-row; }
.divTableHeading { display: table-header-group;}
.divTableCell, .divTableHead { display: table-cell;}
.divTableCell { 
border: 1px solid <?php echo $bordercolor;?>;
border-left: 5px solid <?php echo $bordercolor;?>;
border-radius: 2px;

}
.divTableHeading { display: table-header-group;}
.divTableFoot { display: table-footer-group;}
.divTableBody { display: table-row-group;}

.tempconverter3 {
  position: absolute;
  margin-top: -25px;
  font-size: 12px;
  margin-left: 167px;
}

</style>
<div class="Table" style="position: relative; top: -129px; left: 85px;"> <!--top -130px-->

<div class="tempconverter3">
<?php
if ($temp["units"]=='C'){echo "<div class=tempconvertercircleminus10 style='$convertStyle $colorOutTemp;'>".number_format(($temp["outside_now"]*9/5)+32,1).'°<smalltempunit2>F';} 
else if ($temp["units"]=='F'){echo "<div class=tempconvertercircleminus10 style='$convertStyle $colorOutTemp;'>".number_format(($temp["outside_now"]-32)*5/9,1).'°<smalltempunit2>C';}
?>
</smalltempunit2></div></div>

<div class="divTable temperature">
<div class="divTableHeading">
<div class="divTableRow">
<div class="divTableHead">Max | Min</div>
<div class="divTableHead">Apparent</div>
<div class="divTableHead">Avg Today</div>
</div>
</div>
<div class="divTableBody">
<div class="divTableRow">
<div class="divTableCell"><?php 
if ($temp["outside_day_max"]<10){echo " ".$temp["outside_day_max"]."°".$temp["units"]."\n";?> | <?php echo $temp["outside_day_min"]."°".$temp["units"];}else if ($temp["outside_day_max"]>=10){echo $temp["outside_day_max"]."°".$temp["units"]."\n";?> | <?php echo $temp["outside_day_min"]."°".$temp["units"];}?>
</div>

<div class="divTableCell" style="border-left: 5px solid <?php echo $colorAppTemp; ?>; padding: 1px 1px;"><?php echo $temp["apptemp"]."°".$temp["units"];?></div>
<div class="divTableCell" style="border-left: 5px solid <?php echo $colorOutTempDayAvg; ?>;"><?php //avg today
     echo $temp["outside_day_avg"]."°".$temp["units"];?></div>
</div>
</div>
  <div class="divTableHeading">
<div class="divTableRow">
<div class="divTableHead">Trend</div>
<div class="divTableHead">Humidity</div>
<div class="divTableHead">Dewpoint</div>
</div>
</div>
  <div class="divTableBody">
<div class="divTableRow">
<div class="divTableCell"><?php echo $temp["outside_trend"].'°' ?><smalltempunit2><?php echo $temp["units"];?></smalltempunit2><?php 
if($temp["outside_trend"]>0){echo ' '.$risingsymbol;}else if($temp["outside_trend"]<0){echo ' '.$fallingsymbol;}else{ echo ' '.$steadysymbol;}?></div>

<div class="divTableCell" style="border-left: 5px solid <?php echo $colorHumidity; ?>;"><?php echo $humid["now"]; ?><smalltempunit2>%</smalltempunit2><?php //humidity trend
if($humid["trend"]>0){echo ' '.$risingsymbol;}else if($humid["trend"]<0){echo ' '.$fallingsymbol;}else{ echo '';}?></div>

<div class="divTableCell" style="border-left: 5px solid <?php echo $colorDewpoint;?>;"><?php //dewpoint
echo " ".$dew["now"].'°<smalltempunit2>'.$temp["units"];?><?php //dewpoint trend
if($dew["trend"]>0){echo ' '.$risingsymbol;}else if($dew["trend"]<0){echo ' '.$fallingsymbol;}else{ echo '';}?></div>
</div>
</div>

  <div class="divTableHeading">
<div class="divTableRow">
<div class="divTableHead">Indoor Temp</div>
<div class="divTableHead">Heat Index</div>
<div class="divTableHead">Windchill</div>
</div>
</div>
    <div class="divTableBody">
<div class="divTableRow">
<div class="divTableCell" style="border-left: 5px solid <?php echo $colorOutTemp; ?>;"><?php echo " ".$hometemp." ".$temp["indoor_now"]. "°" .$temp["units"];?></div>
<!--?php if($temp["indoor_trend"]>0){echo ' '.$risingsymbol;}else if($temp["indoor_trend"]<0){echo ' '.$fallingsymbol;}else{ echo '';}?-->
<div class="divTableCell" style="border-left: 5px solid <?php echo $colorHeatindex; ?>;"><?php echo $temp["heatindex"]."°".$temp["units"];?></div>
<div class="divTableCell" style="border-left: 5px solid <?php echo $colorWindchill; ?>;"><?php echo $temp["windchill"]."°".$temp["units"];?></div>

</div>
</div>
</div>
</html>
