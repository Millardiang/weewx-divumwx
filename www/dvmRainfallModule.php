<?php
#####################################################################################################################                                                                                 #                                                                                                                   #
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
include('dvmCombinedData.php');
?>

<div class="chartforecast2">
<span class="yearpopup"><a alt="rain charts" title="rain charts" href="dvmMenuRainfall.php" data-lity><?php echo $menucharticonpage;?> Rainfall Almanac and Charts</a></span>     
</div>
<span class='moduletitle2'><?php echo $lang['rainfallModule'], " (<valuetitleunit>" . $rain["units"];?></valuetitleunit>)</span>
<div class="updatedtime1"><span><?php if (file_exists($livedata)&&time() - filemtime($livedata)>300) echo $offline. '<offline> Offline </offline>'; else echo $online." ".$divum["time"];?></div>
<div class="rainconverter">
<?php 
if($theme == 'dark') {
if ($rain["units"] =='in'){echo "<div class=rainconvertercircle style='color:$colorRainDaySum;'>".number_format($rain["day"]*25.400013716,1)." <smallrainunit>mm";} else if ($rain["units"] =='mm'){echo "<div class=rainconvertercircle style='color:$colorRainDaySum;'>".number_format($rain["day"]*0.0393701,2)." <smallrainunit>in";}
} else {
if ($rain["units"] =='in'){echo "<div class=rainconvertercircle style='background:$colorRainDaySum;'>".number_format($rain["day"]*25.400013716,1)." <smallrainunit>mm";} else if ($rain["units"] =='mm'){echo "<div class=rainconvertercircle style='background:$colorRainDaySum;'>".number_format($rain["day"]*0.0393701,2)." <smallrainunit>in";}
}
?></span>
</div></div>

<!DOCTYPE html>
<script src="js/d3.min.js"></script>


<style>
.rainposs {
  margin-top: -5px;
  margin-left: -160px;
}
.stormRain {
  margin-top: -152.5px;
  margin-left: 130px;
}
</style>

<div class="rainposs">
<div id="raingaugex"></div>
</div>
		
	<script>

	var theme = "<?php echo $theme;?>";

	if (theme == 'dark') {
	
	var currentRain = "<?php echo $rain["day"];?>";
	currentRain = currentRain || 0;
	
	var maxRain = currentRain;
	maxRain = maxRain || 0;
	
	var minRain = 0.0;	
	var rainColor = "<?php echo $colorRainDaySum;?>";
	
	var width = 105,
    height = 150;
    	
var bottomY = height + 38,
    topY = 48,
    bulbRadius = 25.5,
    tubeWidth = 25.5,
    tubeBorderWidth = 1,    
    tubeBorderColor = "rgba(153, 153, 153, 1)";

var bulb_cy = bottomY - bulbRadius,
    bulb_cx = width / 2,
    top_cy = topY + tubeWidth / 2;

var svg = d3.select("#raingaugex")
    .append("svg")
  //.style("background", "#292E35") // box background to be commented out
    .attr("width", width)
    .attr("height", height);

var defs = svg.append("defs");

svg.append("line")
    .attr("x1", width / 2)
    .attr("x2", width / 2)
    .attr("y1", 55)
    .attr("y2", 144)
    .style("stroke", tubeBorderColor)
    .style("stroke-width", "41px")
    .style("fill", "none");
	
svg.append("line")
    .attr("x1", width / 2 + 6 - 35)
    .attr("x2", width / 2)
    .attr("y1", 13.5)
    .attr("y2", 95)
    .style("stroke", "rgba(45,47,50,1)")
    .style("stroke-width", "10px")
    .style("fill", "none"); 
    
svg.append("line")
    .attr("x1", width /2)
    .attr("x2", width / 2 - 6 + 35)
    .attr("y1", 95)
    .attr("y2", 13.5)
    .style("stroke", "rgba(45,47,50,1)")
    .style("stroke-width", "10px")
    .style("fill", "none");     
    
svg.append("line")
    .attr("x1", width / 2 - 37)
    .attr("x2", width / 2 + 37)
    .attr("y1", 13.5)
    .attr("y2", 13.5)
    .style("stroke", tubeBorderColor)
    .style("stroke-width", "3px")
    .style("fill", "none")    
    .style("stroke-linecap", "round");
    
svg.append("line")
    .attr("x1", width / 2 - 35)
    .attr("x2", width / 2)
    .attr("y1", 13.5)
    .attr("y2", 110)
    .style("stroke", tubeBorderColor)
    .style("stroke-width", "1.5px")
    .style("fill", "none");    

svg.append("line")
    .attr("x1", width / 2)
    .attr("x2", width / 2 + 35)
    .attr("y1", 110)
    .attr("y2", 13.5)
    .style("stroke", tubeBorderColor)
    .style("stroke-width", "1.5px")
    .style("fill", "none"); 
  
svg.append("line")
    .attr("x1", width / 2)
    .attr("x2", width / 2)
    .attr("y1", 15)
    .attr("y2", 144)
    .style("stroke", "rgba(45,47,50,1)")
    .style("stroke-width", "38px");
    
svg.append("rect")
    .attr("x", width / 2 - 25.5 )
    .attr("y", 15)
    .attr("width", 50)
    .attr("height", 11.5)
    .style("fill", "rgba(45,47,50,1)");
    
var units = "<?php echo $rain["units"];?>";

if (units == 'mm') {    
	var step = 5;
} else {
	var step = 5 / 25.4;
}

// Determine a suitable range of the scale
var domain = [
  step * Math.floor(minRain / step),
  step * Math.ceil(maxRain / step)
  ];

if (minRain - domain[0] < 0.00 * step)
  domain[0] -= step;

if (domain[1] - maxRain < 0.66 * step)
  domain[1] += step;

// D3 scale object
var scale = d3.scale.linear()
    .range([bulb_cy - bulbRadius / 2 - 8.5, top_cy])
    .domain(domain);

[minRain, maxRain].forEach(function(t) {

  var isMax = (t == maxRain),
      label = (isMax ? "max" : "min"),
      textCol = (isMax ? "rgb(230, 0, 0)" : "rgb(0, 0, 230)"),
      textOffset = (isMax ? - 4 : 4);

});

var tubeFill_bottom = bulb_cy,
    tubeFill_top = scale(currentRain);

// Rect element 
svg.append("rect")
    .attr("x", width / 2 - 18.75 )
    .attr("y", tubeFill_top - 2)
    .attr("width", 37.5)
    .attr("height", tubeFill_bottom - 17.5 - tubeFill_top)
    .style("fill", rainColor);
    
svg.append("rect")
    .attr("x", width / 2 - 18.75 )
    .attr("y", tubeFill_top - 4)
    .attr("rx", 3)
    .attr("width", 37.5)
    .attr("height", 4)
    .style("fill", "rgba(45,47,50,1)");    

svg.append("line")
    .attr("x1", width / 2 - 20.5)
    .attr("x2", width / 2 + 20.5)
    .attr("y1", 145)
    .attr("y2", 145)
    .style("stroke", tubeBorderColor)
    .style("stroke-width", "3.5px")
    .style("fill", "none");    
  
svg.append("line")
    .attr("x1", width / 2 - 30)
    .attr("x2", width / 2 + 30)
    .attr("y1", 148)
    .attr("y2", 148)
    .style("stroke", tubeBorderColor)
    .style("stroke-width", "3px")
    .style("fill", "none")    
    .style("stroke-linecap", "round");

// Values to use along the scale ticks up the tube
var tickValues = d3.range((domain[1] - domain[0]) / step + 1).map(function(v) { return domain[0] + v * step; });

// D3 axis object for the scale
var axis = d3.svg.axis()
    .scale(scale)
    .innerTickSize(7)
    .outerTickSize(0)
    .tickValues(tickValues)
    .orient("left");

// Add the axis to the image
var svgAxis = svg.append("g")
    .attr("id", "RainScale")
    .attr("transform", "translate(" + (width / 2 - tubeWidth / 2 - 12) + ", 0)")
    .call(axis);

// Format text labels
svgAxis.selectAll(".tick text")
    .style("fill", "rgba(119, 119, 119, 1)")
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
	.text( d3.format(".2f")(currentRain) )
	.attr("x", width / 2)
	.attr("y", 40)
	.attr("text-anchor", "middle")
	.style("font-size", "18px")
	.style("font-family", "Helvetica")
	.style("font-weight", "600")
	.style("fill", "rgba(230, 232,239, 1)");
		
} else {

	var currentRain = "<?php echo $rain["day"];?>";
	currentRain = currentRain || 0;
	
	var maxRain = currentRain;
	maxRain = maxRain || 0;
	
	var minRain = 0.0;	
	var rainColor = "<?php echo $colorRainDaySum;?>";
	
	var width = 105,
    height = 150;
    
var bottomY = height + 38,
    topY = 48,
    bulbRadius = 25.5,
    tubeWidth = 25.5,
    tubeBorderWidth = 1,
    tubeBorderColor = "rgba(153, 153, 153, 1)";

var bulb_cy = bottomY - bulbRadius,
    bulb_cx = width / 2,
    top_cy = topY + tubeWidth / 2;

var svg = d3.select("#raingaugex")
    .append("svg")
    .attr("width", width)
    .attr("height", height);

var defs = svg.append("defs");

svg.append("line")
    .attr("x1", width / 2)
    .attr("x2", width / 2)
    .attr("y1", 55)
    .attr("y2", 144)
    .style("stroke", tubeBorderColor)
    .style("stroke-width", "41px")
    .style("fill", "none");    

svg.append("line")
    .attr("x1", width / 2 + 6 - 35)
    .attr("x2", width / 2)
    .attr("y1", 13.5)
    .attr("y2", 95)
    .style("stroke", "rgba(230, 232, 239, 1)")
    .style("stroke-width", "10px")
    .style("fill", "none"); 
    
svg.append("line")
    .attr("x1", width /2)
    .attr("x2", width / 2 - 6 + 35)
    .attr("y1", 95)
    .attr("y2", 13.5)
    .style("stroke", "rgba(230, 232, 239, 1)")
    .style("stroke-width", "10px")
    .style("fill", "none"); 
    
svg.append("line")
    .attr("x1", width / 2 - 37)
    .attr("x2", width / 2 + 37)
    .attr("y1", 13.5)
    .attr("y2", 13.5)
    .style("stroke", tubeBorderColor)
    .style("stroke-width", "3px")
    .style("fill", "none")    
    .style("stroke-linecap", "round");
    
svg.append("line")
    .attr("x1", width / 2 - 35)
    .attr("x2", width / 2)
    .attr("y1", 13.5)
    .attr("y2", 110)
    .style("stroke", tubeBorderColor)
    .style("stroke-width", "1.5px")
    .style("fill", "none");    

svg.append("line")
    .attr("x1", width / 2)
    .attr("x2", width / 2 + 35)
    .attr("y1", 110)
    .attr("y2", 13.5)
    .style("stroke", tubeBorderColor)
    .style("stroke-width", "1.5px")
    .style("fill", "none"); 
  
svg.append("line")
    .attr("x1", width / 2)
    .attr("x2", width / 2)
    .attr("y1", 15)
    .attr("y2", 144)
    .style("stroke", "rgba(230, 232, 239, 1)")
    .style("stroke-width", "38px");

svg.append("rect")
    .attr("x", width / 2 - 25.5 )
    .attr("y", 15)
    .attr("width", 50)
    .attr("height", 11.5)
    .style("fill", "rgba(230, 232, 239, 1)");
    
var units = "<?php echo $rain["units"];?>";

if (units == 'mm') {    
	var step = 5;
} else {
	var step = 5 / 25.4;
}

// Determine a suitable range of the scale
var domain = [
  step * Math.floor(minRain / step),
  step * Math.ceil(maxRain / step)
  ];

if (minRain - domain[0] < 0.0 * step)
  domain[0] -= step;

if (domain[1] - maxRain < 0.66 * step)
  domain[1] += step;

// D3 scale object
var scale = d3.scale.linear()
  .range([bulb_cy - bulbRadius / 2 - 8.5, top_cy])
  .domain(domain);

[minRain, maxRain].forEach(function(t) {

  var isMax = (t == maxRain),
      label = (isMax ? "max" : "min"),
      textCol = (isMax ? "rgb(230, 0, 0)" : "rgb(0, 0, 230)"),
      textOffset = (isMax ? - 4 : 4);

});
   
var tubeFill_bottom = bulb_cy,
    tubeFill_top = scale(currentRain);

// Rect element 
svg.append("rect")
    .attr("x", width / 2 - 18.75 )
    .attr("y", tubeFill_top - 2)
    .attr("width", 37.5)
    .attr("height", tubeFill_bottom - 17.5 - tubeFill_top)
    .style("fill", rainColor);
    
svg.append("rect")
    .attr("x", width / 2 - 18.75 )
    .attr("y", tubeFill_top - 4)
    .attr("rx", 3)
    .attr("width", 37.5)
    .attr("height", 4)
    .style("fill", "rgba(230, 232, 239, 1)");

svg.append("line")
    .attr("x1", width / 2 - 20.5)
    .attr("x2", width / 2 + 20.5)
    .attr("y1", 145)
    .attr("y2", 145)
    .style("stroke", tubeBorderColor)
    .style("stroke-width", "3.5px")
    .style("fill", "none");    

svg.append("line")
    .attr("x1", width / 2 - 30)
    .attr("x2", width / 2 + 30)
    .attr("y1", 148)
    .attr("y2", 148)
    .style("stroke", tubeBorderColor)
    .style("stroke-width", "3px")
    .style("fill", "none")    
    .style("stroke-linecap", "round");

// Values to use along the scale ticks up the tube
var tickValues = d3.range((domain[1] - domain[0]) / step + 1).map(function(v) { return domain[0] + v * step; });

// D3 axis object for the scale
var axis = d3.svg.axis()
    .scale(scale)
    .innerTickSize(7)
    .outerTickSize(0)
    .tickValues(tickValues)
    .orient("left");

// Add the axis to the image
var svgAxis = svg.append("g")
    .attr("id", "RainScale")
    .attr("transform", "translate(" + (width / 2 - tubeWidth / 2 - 12) + ", 0)")
    .call(axis);

// Format text labels
svgAxis.selectAll(".tick text")
    .style("fill", "rgba(119, 119, 119, 1)")
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
	.text( d3.format(".2f")(currentRain) )
	.attr("x", width / 2)
	.attr("y", 40)
	.attr("text-anchor", "middle")
	.style("font-size", "18px")
	.style("font-family", "Helvetica")
	.style("font-weight", "600")
	.style("fill", "rgba(30, 32, 36, 1)");
}			
</script>

<div class="stormRain"></div>

<script>            
var theme = "<?php echo $theme;?>";
    if (theme === 'dark') {
        var boxColor = "#38383c";
        var baseTextColor = "silver";
} else {
        boxColor = "#e6e8ef";
        baseTextColor = "#2d3a4b";
}
</script>

<script>
// script to display rain data
var svg = d3.select(".stormRain")
    .append("svg")
    //.style("background", "#292E35") // box background to be commented out
    .attr("width", 180)
    .attr("height", 150);

var units = "<?php echo $rain["units"];?>";

if (units == 'in') {
var stormRain = <?php echo $rain["storm_rain"]/25.4;?>;    
} else {
    stormRain = <?php echo $rain["storm_rain"];?>;
}

var stormRainColor ="<?php echo $colorStormRain;?>";
var rainRateColor = "<?php echo $colorRainRate;?>";
var lastHourColor = "<?php echo $colorRain1hrSum;?>";
var last24HoursColor = "<?php echo $colorRain24hrSum;?>"; 
var rainMonthColor = "<?php echo $colorRainMonthSum;?>";
var rainYearColor = "<?php echo $colorRainYearSum;?>";
 
var rainRate = <?php echo $rain["rate"];?>;
var lastHour = <?php echo $rain["last_hour"];?>;
var last24Hours = <?php echo $rain["24h_total"];?>;
var rainMonth = <?php echo $rain["month_total"];?>; 
var rainYear = <?php echo $rain["year_total"];?>;
var month = "<?php echo date('F');?>"; 
var year = <?php echo date('Y');?>;

if (stormRain > 0.0) {

svg.append("rect") // stormRain box    
    .attr("x", 12 )
    .attr("y", 127)
    .attr("rx", 1.25)
    .style("stroke", boxColor)
    .attr("width", 68)
    .attr("height", 17)
    .style("fill", "none");

svg.append("rect")
    .attr("x", 12.75)
    .attr("y", 128)
    .attr("rx", 0.5)
    .style("stroke", stormRainColor)
    .attr("height", 15)
    .attr("width", 3)
    .style("fill", stormRainColor);

svg.append("text") // Title text above box
    .attr("x", 45.5)
    .attr("y", 122)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("Storm Rain");

// storm Rain text output with two different colors on the same line
var data = [d3.format(".2f")(stormRain) +" "+"-"+ units]; 

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 48)
    .attr("y", function(d, i) {return 138.5 + i * 138.5;})

    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9.75px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(function(d) {return d.split("-")[0];})

    .append("tspan")
    .style("fill", baseTextColor)
    .text(function(d) {return d.split("-")[1];});

} else {}

if (stormRain > 0.0) {

svg.append("rect") // rain Rate box
    .attr("x", 93 )
    .attr("y", 127)
    .attr("rx", 1.25)
    .style("stroke", boxColor)
    .attr("width", 68)
    .attr("height", 17)
    .style("fill", "none");

svg.append("rect")
    .attr("x", 93.75)
    .attr("y", 128)
    .attr("rx", 0.5)
    .style("stroke", rainRateColor)
    .attr("height", 15)
    .attr("width", 3)
    .style("fill", rainRateColor);

svg.append("text") // rain Rate Title text above box
    .attr("x", 126)
    .attr("y", 122)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("Rain Rate");

// rainRate text output with two different colors on the same line
var data = [d3.format(".2f")(rainRate) +" "+"-"+ units]; 

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 128)
    .attr("y", function(d, i) {return 138.5 + i * 138.5;})

    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9.75px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(function(d) {return d.split("-")[0];})

    .append("tspan")
    .style("fill", baseTextColor)
    .text(function(d) {return d.split("-")[1];});

} else {

svg.append("rect") // rain Rate box
    .attr("x", 56 )
    .attr("y", 127)
    .attr("rx", 1.25)
    .style("stroke", boxColor)
    .attr("width", 63)
    .attr("height", 17)
    .style("fill", "none");

svg.append("rect")
    .attr("x", 56.75)
    .attr("y", 128)
    .attr("rx", 0.5)
    .style("stroke", rainRateColor)
    .attr("height", 15)
    .attr("width", 3)
    .style("fill", rainRateColor);

svg.append("text") // rain Rate Title text above box
    .attr("x", 87)
    .attr("y", 122)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("Rain Rate");
 
// rainRate text output with two different colors on the same line
var data = [d3.format(".2f")(rainRate) +" "+"-"+ units]; 

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 89)
    .attr("y", function(d, i) {return 138.5 + i * 138.5;})

    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9.75px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(function(d) {return d.split("-")[0];})

    .append("tspan")
    .style("fill", baseTextColor)
    .text(function(d) {return d.split("-")[1];});

}

svg.append("rect") // last hour box
    .attr("x", 12 )
    .attr("y", 88)
    .attr("rx", 1.25)
    .style("stroke", boxColor)
    .attr("width", 68)
    .attr("height", 17)
    .style("fill", "none");

svg.append("rect")
    .attr("x", 12.75)
    .attr("y", 89)
    .attr("rx", 0.5)
    .style("stroke", lastHourColor)
    .attr("height", 15)
    .attr("width", 3)
    .style("fill", lastHourColor);

svg.append("text") // last hour Title text above box
    .attr("x", 45.5)
    .attr("y", 83)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("Last Hour");

// lastHour text output with two different colors on the same line
var data = [d3.format(".2f")(lastHour) +" "+"-"+ units]; 

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 48)
    .attr("y", function(d, i) {return 100 + i * 100;})

    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9.75px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(function(d) {return d.split("-")[0];})

    .append("tspan")
    .style("fill", baseTextColor)
    .text(function(d) {return d.split("-")[1];});

svg.append("rect") // last 24hr box
    .attr("x", 93 )
    .attr("y", 88)
    .attr("rx", 1.25)
    .style("stroke", boxColor)
    .attr("width", 68)
    .attr("height", 17)
    .style("fill", "none");

svg.append("rect")
    .attr("x", 93.75)
    .attr("y", 89)
    .attr("rx", 0.5)
    .style("stroke", last24HoursColor)
    .attr("height", 15)
    .attr("width", 3)
    .style("fill", last24HoursColor);

svg.append("text") // last 24hr Title text above box
    .attr("x", 125.5)
    .attr("y", 83)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("Last 24hr");

// last 24 Hours text output with two different colors on the same line
var data = [d3.format(".2f")(last24Hours) +" "+"-"+ units]; 

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 128)
    .attr("y", function(d, i) {return 100 + i * 100;})

    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9.75px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(function(d) {return d.split("-")[0];})

    .append("tspan")
    .style("fill", baseTextColor)
    .text(function(d) {return d.split("-")[1];});

svg.append("rect") // rain year box
    .attr("x", 12 )
    .attr("y", 48)
    .attr("rx", 1.25)
    .style("stroke", boxColor)
    .attr("width", 68)
    .attr("height", 17)
    .style("fill", "none");

svg.append("rect")
    .attr("x", 12.75)
    .attr("y", 49)
    .attr("rx", 0.5)
    .style("stroke", rainYearColor)
    .attr("height", 15)
    .attr("width", 3)
    .style("fill", rainYearColor);

svg.append("text") // year Title text above box
    .attr("x", 48)
    .attr("y", 43)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(year);

// rain Year text output with two different colors on the same line
var data = [d3.format(".2f")(rainYear) +" "+"-"+ units]; 

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 48)
    .attr("y", function(d, i) {return 60.5 + i * 60.5;})

    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9.75px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(function(d) {return d.split("-")[0];})


    .append("tspan")
    .style("fill", baseTextColor)
    .text(function(d) {return d.split("-")[1];});

svg.append("rect") // rain month box
    .attr("x", 93 )
    .attr("y", 48)
    .attr("rx", 1.25)
    .style("stroke", boxColor)
    .attr("width", 68)
    .attr("height", 17)
    .style("fill", "none");

svg.append("rect")
    .attr("x", 93.75)
    .attr("y", 49)
    .attr("rx", 0.5)
    .style("stroke", rainMonthColor)
    .attr("height", 15)
    .attr("width", 3)
    .style("fill", rainMonthColor);

svg.append("text") // month Title text above box
    .attr("x", 126)
    .attr("y", 43)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(month);

// rain Month text output with two different colors on the same line
var data = [d3.format(".2f")(rainMonth) +" "+"-"+ units]; 

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 128)
    .attr("y", function(d, i) {return 60.5 + i * 60.5;})

    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9.75px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(function(d) {return d.split("-")[0];})

    .append("tspan")
    .style("fill", baseTextColor)
    .text(function(d) {return d.split("-")[1];});

   

</script>

<div id="raindrops" width="300" height="150" style="position: relative; top: -159px; left: 0px;"></div>

<script>

var raining = <?php echo $rain["rate"];?>;

if ( raining > 0 ) {
  
var ns = "http://www.w3.org/2000/svg";
var xlink = "http://www.w3.org/1999/xlink";

function randBetween(min, max) {
  return Math.floor(Math.random() * (max - min + 1)) + min;
}

class Rain {
  constructor(element) {
    this.el = element;
    this.width = 300;
    this.height = 150;

    this.drops = 128;
    this.dropDurationMin = 400;
    this.dropDurationMax = 800;
    this.dropHeightMin = 2;
    this.dropHeightMax = 5;
    this.dropWidthMin = 3;
    this.dropWidthMax = 6;

    this.splashes = 16;
    this.splashDuration = 500;
    this.splashRadiusXMin = 6;
    this.splashRadiusXMax = 10;
    this.splashRadiusY = 2;

    this.svg = document.createElementNS(ns, 'svg');
    this.svg.setAttribute('viewBox', `0 0 ${this.width} ${this.height}`);
    this.svg.setAttribute('xmlns', ns);
    this.svg.setAttribute('xmlns:xlink', xlink);
    this.svg.setAttribute('id', 'rain');

    this.filters = false;

    if (this.filters) {
      this.appendFilters();
    }

    this.appendTrack();
  }

  appendFilters() {
    let blur = document.createElementNS(ns, 'feGaussianBlur');
    blur.setAttribute('in', 'SourceGraphic');
    blur.setAttribute('stdDeviation', 1);

    let filter = document.createElementNS(ns, 'filter');
    filter.setAttribute('id', `blur`);
    filter.setAttribute('height', '300%');
    filter.setAttribute('width', '300%');
    filter.setAttribute('x', '-100%');
    filter.setAttribute('y', '-100%');
    filter.appendChild(blur);

    this.svg.appendChild(filter);
  }

  appendTrack() {
    let track = document.createElementNS(ns, 'path');
    track.setAttribute('id', 'track');
    track.setAttribute('fill', 'none');
    track.setAttribute('stroke', 'none');
    track.setAttribute('d', `M 0 -${this.height * 0.1} V ${this.height * 1.1}`);

    this.svg.appendChild(track);
  }

  makeDrop(index) {
    let drop = document.createElementNS(ns, 'rect');
    drop.setAttribute('fill', `rgba(59, 156, 172, ${randBetween(1, 3) * 0.4})`);
    drop.setAttribute('height', randBetween(this.dropHeightMin, this.dropHeightMax));
    drop.setAttribute('id', `drop-${index}`);
    drop.setAttribute('rx', 1);
    drop.setAttribute('width', randBetween(this.dropWidthMin, this.dropWidthMax) * 0.1);
    drop.setAttribute('x', 0);
    drop.setAttribute('y', 0);

    if (this.filters) {
      drop.setAttribute('filter', 'url(#blur)');
    }

    let group = document.createElementNS(ns, 'g');
    group.setAttribute('transform', `translate(${randBetween(0, this.width)}, -${this.height * 0.05})`);
    group.appendChild(drop);

    return group;
  }

  makeMotion(index) {
    let motionPath = document.createElementNS(ns, 'mpath');
    motionPath.setAttribute('xlink:href', '#track');

    let motion = document.createElementNS(ns, 'animateMotion');
    motion.setAttribute('xlink:href', `#drop-${index}`);
    motion.setAttribute('dur', `${randBetween(this.dropDurationMin, this.dropDurationMax)}ms`);
    motion.setAttribute('begin', `${randBetween(0, this.dropDurationMin)}ms`);
    motion.setAttribute('repeatCount', 'indefinite');
    motion.appendChild(motionPath);

    return motion;
  }

  makeSplash(index) {
    let begin = randBetween(0, this.splashDuration);
    let animateStroke = document.createElementNS(ns, 'animate');
    animateStroke.setAttribute('attributeName', 'stroke-width');
    animateStroke.setAttribute('dur', `${this.splashDuration}ms`);
    animateStroke.setAttribute('values', '0; 1; 0.5; 0.25; 0');
    animateStroke.setAttribute('repeatCount', 'indefinite');
    animateStroke.setAttribute('begin', `${begin}ms`);

    let randomRadiusX = randBetween(this.splashRadiusXMin, this.splashRadiusXMax);
    let animateRadiusX = document.createElementNS(ns, 'animate');
    animateRadiusX.setAttribute('attributeName', 'rx');
    animateRadiusX.setAttribute('dur', `${this.splashDuration}ms`);
    animateRadiusX.setAttribute('values', `0; ${randomRadiusX}`);
    animateRadiusX.setAttribute('repeatCount', 'indefinite');
    animateRadiusX.setAttribute('additive', 'sum');
    animateRadiusX.setAttribute('begin', `${begin}ms`);

    let ellipse = document.createElementNS(ns, 'ellipse');
    ellipse.setAttribute('stroke', `rgba(59, 156, 172, 0.4)`);
    ellipse.setAttribute('stroke-width', 1);
    ellipse.setAttribute('fill', 'none');
    ellipse.setAttribute('cx', randBetween(0, this.width));
    ellipse.setAttribute('cy', randBetween(this.height * 0.78, this.height));
    ellipse.setAttribute('rx', randomRadiusX);
    ellipse.setAttribute('ry', this.splashRadiusY);

    if (this.filters) {
      ellipse.setAttribute('filter', 'url(#blur)');
    }

    ellipse.appendChild(animateStroke);
    ellipse.appendChild(animateRadiusX);

    return ellipse;
  }

  render() {
    for (let i = 0; i < this.drops; i++) {
      let drop = this.makeDrop(i);
      let motion = this.makeMotion(i);

      this.svg.appendChild(drop);
      this.svg.appendChild(motion);
    }

    for (let i = 0; i < this.splashes; i++) {
      let splash = this.makeSplash(i);

      this.svg.appendChild(splash);
    }

    this.el.innerHTML = this.svg.outerHTML;
  }}


const el = document.getElementById('raindrops');
const rain = new Rain(el);

rain.render();
}

</script>
</html>
