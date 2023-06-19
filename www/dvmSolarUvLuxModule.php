<?php
include('dvmCombinedData.php');
date_default_timezone_set($TZ);
//align colours with Total sky UV index (provided by CAMS) https://climate-adapt.eea.europa.eu/en/observatory/evidence/projections-and-tools/cams-uv-index-forecast
if ($uv["now"]<1){$uv["color"]="grey";}
else if ($uv["now"]<2){$uv["color"]="#379925";} // darker green
else if ($uv["now"]<3){$uv["color"]="#8bbe09";} // lighter green
else if ($uv["now"]<4){$uv["color"]="#fff306";} // lighter yellow
else if ($uv["now"]<5){$uv["color"]="#ffce0b";} // darker yellow
else if ($uv["now"]<6){$uv["color"]="#f1a40a";} // lighter orange
else if ($uv["now"]<7){$uv["color"]="#e87408";} // mid orange
else if ($uv["now"]<8){$uv["color"]="#de5404";} // darker orange
else if ($uv["now"]<9){$uv["color"]="#dd1d0f";} // red
else if ($uv["now"]<10){$uv["color"]="#de006f";} // magenta
else if ($uv["now"]<11){$uv["color"]="#a44c92";} // lighter purple
else if ($uv["now"]<13){$uv["color"]="##6e67a6";} // darker purple
else if ($uv["now"]<15){$uv["color"]="#6cceff";} // lighter cyan
else {$uv["color"]="#40d2ff";} // darker cyan
?>

    <div class="chartforecast2">
       <span class="yearpopup"><a alt="solar" title="UV Guide" href="dvmMenuSolarUvLux.php" data-lity><?php echo $menucharticonpage;?> UV and Solar Almanacs and Guide</a></span>
    </div>
    <span class='moduletitle2'><?php echo $lang['solarUvLuxModule'];?> </span>


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

<script src='js/d3.min.js'></script>    

       
<style>
.solarposx {margin-top: -2px; margin-left: -210px;}
.uvipos {margin-top: -155px; margin-left: 0px;}
.luxpos {margin-top: -155px; margin-left: 210px;}
</style>
    <script>
       
    var theme = "<?php echo $theme;?>";

    if (theme === 'dark') {
    var tubeFillColor = "rgba(45,47,50,1)";
    var baseTextColor = "silver";
    } else {
    var tubeFillColor = "rgba(230, 232, 239, 1)";
    var baseTextColor = "#2d3a4b";
    }

    </script>

<div class="solarposx">
<div id="solarx"></div>
</div>
		
	<script>
		

    var width = 95,
    height = 150;
        
    var currentSolar = "<?php echo $solar["now"];?>";
    currentSolar = currentSolar || 0;
    
    var maxSolar = "<?php echo $solar["day_max"];?>";
    maxSolar = maxSolar || 0;
    
    var minSolar = 0;
    
    var solarMaxTime = "<?php echo $solar["day_maxtime"];?>";
    solarMaxTime = solarMaxTime || 0;
       
    var solarunits = "W/m²";    
    
var bottomY = height + 10,
    topY = 0,
    bulbRadius = 25.5,
    tubeWidth = 35,
    tubeBorderWidth = 1,
    solarColor = "rgba(255,124,57,1)",
    tubeBorderColor = "#999999";

var bulb_cy = bottomY - bulbRadius,
    bulb_cx = width / 2,
    top_cy = topY + tubeWidth / 2;

var svg = d3.select("#solarx")
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
var scale = d3.scale.linear()
    .range([bulb_cy - bulbRadius / 2 - 8.5, top_cy])
    .domain(domain);
    
[maxSolar].forEach(function(t) {

  var isMax = (t == maxSolar),
      label = (isMax ? "Max" : "min"),
      textCol = (isMax ? "silver" : "silver"),
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
    .style("font-size", "8px");

});

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
var axis = d3.svg.axis()
    .scale(scale)
    .innerTickSize(7)
    .outerTickSize(0)
    .tickValues(tickValues)
    .orient("left").tickFormat(d3.format("d"));

// Add the axis to the image
var svgAxis = svg.append("g")
    .attr("id", "SolarScale")
    .attr("transform", "translate(" + (width / 2 + 5 - tubeWidth / 2) + ", 0)")
    .call(axis);

// Format text labels
svgAxis.selectAll(".tick text")
    .style("fill", "#777777")
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
    .attr("y", 143)
    .style("text-anchor", "middle")
    .style("font-size", "8px")
    .style("font-family", "Helvetica")    
   	.style("fill", baseTextColor);
  

			
</script>
<div class="uvipos">
<div id="uvi"></div>
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
    uviColor = "<?php echo $uv["color"];?>",
    tubeBorderColor = "#999999";

var bulb_cy = bottomY - bulbRadius,
    bulb_cx = width / 2,
    top_cy = topY + tubeWidth / 2;

var svg = d3.select("#uvi")
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
var scale = d3.scale.linear()
    .range([bulb_cy - bulbRadius / 2 - 8.5, top_cy])
    .domain(domain);
    
[maxUVI].forEach(function(t) {

  var isMax = (t == maxUVI),
      label = (isMax ? "Max" : "min"),
      textCol = (isMax ? "silver" : "silver"),
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
var axis = d3.svg.axis()
    .scale(scale)
    .innerTickSize(7)
    .outerTickSize(0)
    .tickValues(tickValues)
    .orient("left").tickFormat(d3.format("d"));

// Add the axis to the image
var svgAxis = svg.append("g")
    .attr("id", "UVIScale")
    .attr("transform", "translate(" + (width / 2 + 5 - tubeWidth / 2) + ", 0)")
    .call(axis);

// Format text labels
svgAxis.selectAll(".tick text")
    .style("fill", "#777777")
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
    .attr("y", 143)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "8px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("Max " + maxUVI + " " + "(" + uviMaxTime + ")");
  

			
</script>  
<div class="luxpos">
<div id="lux"></div>
</div>
		
	<script>
		

    var width = 95,
    height = 150;
    
    var currentLux = "<?php echo $sky["lux"];?>";
    currentLux = currentLux || 0;
    
    var maxLux = currentLux;
    maxLux = maxLux || 0;
    
    var minLux = 0;    
    
var bottomY = height + 10,
    topY = 0,
    bulbRadius = 25.5,
    tubeWidth = 35,
    tubeBorderWidth = 1,
    LuxColor = "rgba(255, 85, 85, 1)",
    tubeBorderColor = "#999999";

var bulb_cy = bottomY - bulbRadius,
    bulb_cx = width / 2,
    top_cy = topY + tubeWidth / 2;

var svg = d3.select("#lux")
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
var step = 20000;

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
var scale = d3.scale.linear()
    .range([bulb_cy - bulbRadius / 2 - 8.5, top_cy])
    .domain(domain);

[minLux, maxLux].forEach(function(t) {

  var isMax = (t == maxLux),
      label = (isMax ? "max" : "min"),
      textCol = (isMax ? "rgb(230, 0, 0)" : "rgb(0, 0, 230)"),
      textOffset = (isMax ? -4 : 4);

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
var axis = d3.svg.axis()
    .scale(scale)
    .innerTickSize(7)
    .outerTickSize(0)
    .tickValues(tickValues)
    .orient("left").tickFormat(d3.format("d"));

// Add the axis to the image
var svgAxis = svg.append("g")
    .attr("id", "LuxScale")
    .attr("transform", "translate(" + (width / 2 + 5 - tubeWidth / 2) + ", 0)")
    .call(axis);

// Format text labels
svgAxis.selectAll(".tick text")
    .style("fill", "#777777")
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
	.text("Lux" + " " + currentLux )
	.attr("x", width / 2)
	.attr("y", 133)
	.attr("text-anchor", "middle")
	.style("font-size", "9px")
	.style("font-family", "Helvetica")
	.style("fill", baseTextColor);
  

			
</script>
           
</html>
