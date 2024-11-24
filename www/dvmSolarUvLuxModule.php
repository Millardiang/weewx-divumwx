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
date_default_timezone_set($TZ);
?>

<div class="chartforecast">
<span class="yearpopup"><a alt="solar" title="solar" href="dvmSolarRecords.php" data-lity><?php echo $menucharticonpage;?> UV and Solar | UVI Records and Charts</a></span>
</div>
<span class='moduletitle'><?php echo $lang['solarUvLuxModule'];?></span>


<div class="updatedtime1"><?php if(file_exists($livedata)&&time() - filemtime($livedata)>300)echo $offline. '<offline> Offline </offline>';else echo $online." ".$divum["time"];?></div>

<div class="uvcautionbig2">
<?php 
if ($uv["now"]>=10) {
echo $uviclear.'<span>UVI</span> Extreme';}
else if ($uv["now"]>=8) {echo $uviclear.'<span>UVI</span> Very High';}
else if ($uv["now"]>=6) {echo $uviclear.'<span>UVI</span> High';}
else if ($uv["now"]>=3) {echo $uviclear.'<span>UVI</span> Moderate';}
else if ($alm["sun_altitude"] < 0.0 && $uv["now"]>=0 ) {echo $uviclear,"Below Horizon";} 
else if ($uv["now"]>=0 ) {echo $uviclear,'<span>UVI</span> Low';}
?></div>

<html>

<script src="js/d3.7.9.0.min.js"></script>   

       
<style>
.sunshineposx {margin-top: -3.5px; margin-left: -0px;}
.solarposx {margin-top: -10px; margin-left: -210px;}
.uvipos {margin-top: -149.5px; margin-left: 0px;}
.luxpos {margin-top: -150px; margin-left: 210px;}
</style>

<div class="sunshineposx">
<div class="sunshine"></div>
</div>
<script>
var svg = d3.select(".sunshine")
    .append("svg")
    //.style("background", "#292E35")
    .attr("width", 310)
    .attr("height", 15);


var sunshine_duration = "<?php echo $solar["sun_duration_hours_minutes"];?>";

var threshold = "<?php echo $solar["threshold"];?>";

var solarunits = "W/m²";

var data = ["Sunshine Duration " + "-" + sunshine_duration];

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
    .style("fill", "red")
    .style("font-weight", "bold")
    .text(function(d) { return d.split("-")[1]; })

</script>

<script>
            
    var theme = "<?php echo $theme;?>";

    
    var tubeFillColor = "var(--col-10)";
    var baseTextColor = "var(--col-6)";
    var colorS = "var(--col-6)";
   

</script>

<div class="solarposx">
<div class="solarx"></div>
</div>
        
<script>
        
    var width = 95,
    height = 150;
        
    var currentSolar = "<?php echo $solar["now"];?>";
    currentSolar = currentSolar || 0;
    
    var maxSolar = "<?php echo $solar["day_max"];?>";
    maxSolar = maxSolar || 0;
    
    var minSolar = 0;
    
    var maxThreshold = "<?php echo $solar["threshold"];?>";
    maxThreshold = maxThreshold || 0;
    
    var minThreshold = 0;
    
    var solarMaxTime = "<?php echo $solar["day_maxtime"];?>";
    solarMaxTime = solarMaxTime || 0;
       
    var solarunits = "W/m²";    
    
var bottomY = height + 10,
    topY = 0,
    bulbRadius = 25.5,
    tubeWidth = 35,
    tubeBorderWidth = 1,
    solarColor = "<?php echo $colorSolarCurrent;?>",
    tubeBorderColor = "#999999";

var bulb_cy = bottomY - bulbRadius,
    bulb_cx = width / 2,
    top_cy = topY + tubeWidth / 2;

var svg = d3.select(".solarx")
    .append("svg")
    //.style("background", "#292E35") // box background to be commented out
    .attr("width", width)
    .attr("height", height);

var defs = svg.append("defs");

svg.append("rect")
    .attr("x", width / 2 - 7.75)
    .attr("y", 13)
    .attr("rx", 2)
    .style("stroke", tubeBorderColor)
    .attr("height", 104.85)
    .attr("width", tubeWidth - 10)
    .style("fill", "none");    
    
svg.append("rect")
    .attr("x", width / 2 - 7.75)
    .attr("y", 13)
    .attr("rx", 2)
    .style("fill", tubeFillColor)
    .attr("height", 104.85)
    .attr("width", tubeWidth - 10);

// Scale step size
var step = 200;

// Determine a suitable range for the solar scale
var domain = [
  step * Math.floor(minSolar / step),
  step * Math.ceil(maxSolar / step)
  ];

if (minSolar - domain[0] < 0.0 * step)
  domain[0] -= step;

if (domain[1] - maxSolar < 0.66 * step)
  domain[1] += step;

// D3 scale object
var scale = d3.scaleLinear()
    .range([bulb_cy - bulbRadius / 2 - 8.5, top_cy])
    .domain(domain);
    
[maxSolar].forEach(function(t) {

  var isMax = (t == maxSolar),
      label = (isMax ? "Max" : "min"),
      textCol = (isMax ? colorS : colorS),
      textOffset = (isMax ? - 3 : 3);
      
  svg.append("line")
    .attr("id", label + "Line")
    .attr("x1", width / 2 + 38 - tubeWidth / 2)
    .attr("x2", width / 2 + 38 + tubeWidth / 2 - 18)
    .attr("y1", scale(t))
    .attr("y2", scale(t))
    .style("stroke", tubeBorderColor)
    .style("stroke-width", "1px")
    .style("stroke-linecap", "round");

  svg.append("text")
    .attr("x", width / 2 + tubeWidth / 2 + 4)
    .attr("y", scale(t) + textOffset)
    .attr("dy", isMax ? null : "0.75em")
    .text(label)
    .style("fill", textCol)
    .style("font-family", "Helvetica")
    .style("font-size", "8px");

});
/*
if (maxThreshold > 120) {

[maxThreshold].forEach(function(t) {

  var isMax = (t == maxThreshold),
      label = (isMax ? "T'hold" : "min"),
      textCol = (isMax ? "silver" : "silver"),
      textOffset = (isMax ? - 3 : 3);
      
  svg.append("line")
    .attr("id", label + "Line")
    .attr("x1", width / 2 + 38 - tubeWidth / 2)
    .attr("x2", width / 2 + 38 + tubeWidth / 2 - 11)
    .attr("y1", scale(t))
    .attr("y2", scale(t))
    .style("stroke", tubeBorderColor)
    .style("stroke-width", "1px")
    .style("stroke-linecap", "round");


  svg.append("text")
    .attr("x", width / 2 + tubeWidth / 2 + 4)
    .attr("y", scale(t) + textOffset)
    .attr("dy", isMax ? null : "0.75em")
    .text(label)
    .style("fill", textCol)
    .style("font-size", "8px");

});

} else {}
*/
var tubeFill_bottom = bulb_cy,
    tubeFill_top = scale(currentSolar);

// Rect element for the solar tube
svg.append("rect")
    .attr("x", width / 2 - (tubeWidth - 20.5) / 2)
    .attr("y", tubeFill_top)
    .attr("rx", 2)
    .attr("width", tubeWidth - 11)
    .attr("height", tubeFill_bottom - 17 - tubeFill_top)
    .style("fill", solarColor);

// Values to use along the scale ticks up the solar tube
var tickValues = d3.range((domain[1] - domain[0]) / step + 1).map(function(v) { return domain[0] + v * step; });

// D3 axis object for the solar scale
var axis = d3.axisLeft(scale)
    .tickSize(7)
    .tickValues(tickValues)
    .tickFormat(d3.format("d"));

// Add the axis to the image
var svgAxis = svg.append("g")
    .attr("id", "SolarScale")
    .attr("transform", "translate(" + (width / 2 + 5 - tubeWidth / 2) + ", 0)")
    .call(axis);

// Format text labels
svgAxis.selectAll(".tick text")
    .style("fill", "var(--col-6)")
    .style("font-family", "Helvetica")
    .style("font-size", "7px");

// Set main axis line to no stroke or fill
svgAxis.select("path")
    .style("stroke", "none")
    .style("fill", "none");

// Set the style of the ticks 
svgAxis.selectAll(".tick line")
    .style("stroke", tubeBorderColor)
    .style("stroke-linecap", "round")
    .style("stroke-width", "2px");

// text output current solar  
svg.append("text")
    .text("Solar" + " " + currentSolar + " " + solarunits )
    .attr("x", width / 2)
    .attr("y", 133)
    .attr("text-anchor", "middle")
    .style("font-size", "9px")
    .style("font-family", "Helvetica")
    .style("fill", baseTextColor);
    
svg.append("text") // max solar text output
    .text("Max " + maxSolar + " " + "(" + solarMaxTime + ")")
    .attr("x", width / 2)
    .attr("y", 145)
    .style("text-anchor", "middle")
    .style("font-size", "8px")
    .style("font-family", "Helvetica")    
    .style("fill", baseTextColor);
             
</script>

<div class="uvipos">
<div class="uvi"></div>
</div>
        
<script>
        
    var width = 95,
    height = 150;
    
    var currentUVI = "<?php echo $uv["now"];?>";
    currentUVI = currentUVI || 0;
    
    var maxUVI = "<?php echo $uv["day_max"];?>";
    maxUVI = maxUVI || 0;
    
    var minUVI = 0.0;
    
    var uviMaxTime = "<?php echo $uv["day_maxtime"];?>";
    uviMaxTime = uviMaxTime || 0;
        
    var uviunits = "UVI";    
    
var bottomY = height + 10,
    topY = 0,
    bulbRadius = 25.5,
    tubeWidth = 35,
    tubeBorderWidth = 1,
    uviColor = "<?php echo $colorUVCurrent;?>",
    tubeBorderColor = "var(--col-14)";

var bulb_cy = bottomY - bulbRadius,
    bulb_cx = width / 2,
    top_cy = topY + tubeWidth / 2;

var svg = d3.select(".uvi")
    .append("svg")
    //.style("background", "#292E35") // box background to be commented out
    .attr("width", width)
    .attr("height", height);

var defs = svg.append("defs");

svg.append("rect")
    .attr("x", width / 2 - 7.75)
    .attr("y", 13)
    .attr("rx", 2)
    .style("stroke", tubeBorderColor)
    .attr("height", 104.85)
    .attr("width", tubeWidth - 10)
    .style("fill", "none");    
    
svg.append("rect")
    .attr("x", width / 2 - 7.75)
    .attr("y", 13)
    .attr("rx", 2)
    .style("fill", tubeFillColor)
    .attr("height", 104.85)
    .attr("width", tubeWidth - 10);
       
// Scale step size
var step = 2;

// Determine a suitable range for the uvi scale
var domain = [
  step * Math.floor(minUVI / step),
  step * Math.ceil(maxUVI / step)
  ];

if (minUVI - domain[0] < 0.0 * step)
  domain[0] -= step;

if (domain[1] - maxUVI < 0.66 * step)
  domain[1] += step;

// D3 scale object
var scale = d3.scaleLinear()
    .range([bulb_cy - bulbRadius / 2 - 8.5, top_cy])
    .domain(domain);
    
[maxUVI].forEach(function(t) {

  var isMax = (t == maxUVI),
      label = (isMax ? "Max" : "min"),
      textCol = (isMax ? colorS : colorS),
      textOffset = (isMax ? - 3 : 3);
      
    svg.append("line")
    .attr("id", label + "Line")
    .attr("x1", width / 2 + 38 - tubeWidth / 2)
    .attr("x2", width / 2 + 38 + tubeWidth / 2 - 18)
    .attr("y1", scale(t))
    .attr("y2", scale(t))
    .style("stroke", tubeBorderColor)
    .style("stroke-width", "1px")
    .style("stroke-linecap", "round");

  svg.append("text")
    .attr("x", width / 2 + tubeWidth / 2 + 4)
    .attr("y", scale(t) + textOffset)
    .attr("dy", isMax ? null : "0.75em")
    .text(label)
    .style("fill", textCol)
    .style("font-family", "Helvetica")
    .style("font-size", "8px");

});

var tubeFill_bottom = bulb_cy,
    tubeFill_top = scale(currentUVI);

// Rect element for the uvi tube
svg.append("rect")
    .attr("x", width / 2 - (tubeWidth - 20.5) / 2)
    .attr("y", tubeFill_top)
    .attr("rx", 2)
    .attr("width", tubeWidth - 11)
    .attr("height", tubeFill_bottom - 17 - tubeFill_top)
    .style("fill", uviColor);

// Values to use along the scale ticks up the uvi tube
var tickValues = d3.range((domain[1] - domain[0]) / step + 1).map(function(v) { return domain[0] + v * step; });

// D3 axis object for the lux scale
var axis = d3.axisLeft(scale)
    .tickSize(7)
    .tickValues(tickValues)
    .tickFormat(d3.format("d"));

// Add the axis to the image
var svgAxis = svg.append("g")
    .attr("id", "UVIScale")
    .attr("transform", "translate(" + (width / 2 + 5 - tubeWidth / 2) + ", 0)")
    .call(axis);

// Format text labels
svgAxis.selectAll(".tick text")
    .style("fill", "var(--col-6)")
    .style("font-family", "Helvetica")
    .style("font-size", "7px");

// Set main axis line to no stroke or fill
svgAxis.select("path")
    .style("stroke", "none")
    .style("fill", "none");

// Set the style of the ticks 
svgAxis.selectAll(".tick line")
    .style("stroke", tubeBorderColor)
    .style("stroke-linecap", "round")
    .style("stroke-width", "2px");

// text output current uvi  
svg.append("text")
    .text( uviunits + " " + currentUVI )
    .attr("x", width / 2)
    .attr("y", 133)
    .attr("text-anchor", "middle")
    .style("font-size", "9px")
    .style("font-family", "Helvetica")
    .style("fill", baseTextColor);
    
 svg.append("text") // max uvi text output
    .attr("x", width / 2)
    .attr("y", 145)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "8px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("Max " + maxUVI + " " + "(" + uviMaxTime + ")");
              
</script>

<div class="luxpos">
<div class="lux"></div>
</div>
        
<script>
        
    var width = 95,
    height = 150;
    
    var currentLux = "<?php echo $solar["lux"];?>";
    currentLux = currentLux || 0;
    
    var maxLux = "<?php echo $solar["day_lux_max"];?>";
    maxLux = maxLux || 0;

    var maxLuxTime = "<?php echo $solar["day_maxtime"];?>";
   
    var minLux = 0;    
    
var bottomY = height + 10,
    topY = 0,
    bulbRadius = 25.5,
    tubeWidth = 35,
    tubeBorderWidth = 1,
    LuxColor = "<?php echo $colorLuxCurrent;?>",
    tubeBorderColor = "var(--col-14)";

var bulb_cy = bottomY - bulbRadius,
    bulb_cx = width / 2,
    top_cy = topY + tubeWidth / 2;

var svg = d3.select(".lux")
    .append("svg")
    //.style("background", "#292E35") // box background to be commented out
    .attr("width", width)
    .attr("height", height);

var defs = svg.append("defs");

svg.append("rect")
    .attr("x", width / 2 - 7.75)
    .attr("y", 13)
    .attr("rx", 2)
    .style("stroke", tubeBorderColor)
    .attr("height", 104.85)
    .attr("width", tubeWidth - 10)
    .style("fill", "none");    
    
svg.append("rect")
    .attr("x", width / 2 - 7.75)
    .attr("y", 13)
    .attr("rx", 2)
    .style("fill", tubeFillColor)
    .attr("height", 104.85)
    .attr("width", tubeWidth - 10);

// Scale step size
var step = 30000;

// Determine a suitable range for the lux scale
var domain = [
  step * Math.floor(minLux / step),
  step * Math.ceil(maxLux / step)
  ];

if (minLux - domain[0] < 0.0 * step)
  domain[0] -= step;

if (domain[1] - maxLux < 0.66 * step)
  domain[1] += step;

// D3 scale object
var scale = d3.scaleLinear()
    .range([bulb_cy - bulbRadius / 2 - 8.5, top_cy])
    .domain(domain);

[maxLux].forEach(function(t) {

  var isMax = (t == maxLux),
      label = (isMax ? "Max" : "min"),
      textCol = (isMax ? colorS : colorS),
      textOffset = (isMax ? - 3 : 3);

 svg.append("line")
    .attr("id", label + "Line")
    .attr("x1", width / 2 + 38 - tubeWidth / 2)
    .attr("x2", width / 2 + 38 + tubeWidth / 2 - 18)
    .attr("y1", scale(t))
    .attr("y2", scale(t))
    .style("stroke", tubeBorderColor)
    .style("stroke-width", "1px")
    .style("stroke-linecap", "round");

  svg.append("text")
    .attr("x", width / 2 + tubeWidth / 2 + 4)
    .attr("y", scale(t) + textOffset)
    .attr("dy", isMax ? null : "0.75em")
    .text(label)
    .style("fill", textCol)
    .style("font-family", "Helvetica")
    .style("font-size", "8px");

});

var tubeFill_bottom = bulb_cy,
    tubeFill_top = scale(currentLux);

// Rect element for the lux tube
svg.append("rect")
    .attr("x", width / 2 - (tubeWidth - 20.50) / 2)
    .attr("y", tubeFill_top)
    .attr("rx", 2)
    .attr("width", tubeWidth - 11)
    .attr("height", tubeFill_bottom - 17 - tubeFill_top)
    .style("fill", LuxColor);

// Values to use along the scale ticks up the lux tube
var tickValues = d3.range((domain[1] - domain[0]) / step + 1).map(function(v) { return domain[0] + v * step; });

// D3 axis object for the lux scale
var axis = d3.axisLeft(scale)
    .tickSize(7)
    .tickValues(tickValues)
    .tickFormat(d3.format("d"));

// Add the axis to the image
var svgAxis = svg.append("g")
    .attr("id", "LuxScale")
    .attr("transform", "translate(" + (width / 2 + 5 - tubeWidth / 2) + ", 0)")
    .call(axis);

// Format text labels
svgAxis.selectAll(".tick text")
    .style("fill", "var(--col-6)")
    .style("font-family", "Helvetica")
    .style("font-size", "7px");

// Set main axis line to no stroke or fill
svgAxis.select("path")
    .style("stroke", "none")
    .style("fill", "none");

// Set the style of the ticks 
svgAxis.selectAll(".tick line")
    .style("stroke", tubeBorderColor)
    .style("stroke-linecap", "round")
    .style("stroke-width", "2px");

// text output current lux  
svg.append("text")
    .text("Illuminance " + currentLux + " Lux")
    .attr("x", width / 2)
    .attr("y", 133)
    .attr("text-anchor", "middle")
    .style("font-size", "9px")
    .style("font-family", "Helvetica")
    .style("fill", baseTextColor);

svg.append("text") // max solar text output
    .text("Max " + maxLux + " " + "(" + maxLuxTime + ")")
    .attr("x", width / 2)
    .attr("y", 145)
    .style("text-anchor", "middle")
    .style("font-size", "8px")
    .style("font-family", "Helvetica")    
    .style("fill", baseTextColor);
            
</script>
           
</html>
