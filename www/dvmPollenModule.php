<?php
include('dvmCombinedData.php');
date_default_timezone_set($TZ);
$lang['PollenModule'] = "Pollen Risk Index";
$json_string = file_get_contents('jsondata/pollen.txt');
$parsed_json = json_decode($json_string,true);

$pollen["updated_time"] = date("H:i:s",strtotime($parsed_json["data"][0]["updatedAt"]));
$pollen["grass_count"] = $parsed_json["data"][0]["Count"]["grass_pollen"];
$pollen["tree_count"] = $parsed_json["data"][0]["Count"]["tree_pollen"];
$pollen["weed_count"] = $parsed_json["data"][0]["Count"]["weed_pollen"];
$pollen["grass_risk"] = $parsed_json["data"][0]["Risk"]["grass_pollen"];
$pollen["tree_risk"] = $parsed_json["data"][0]["Risk"]["tree_pollen"];
$pollen["weed_risk"] = $parsed_json["data"][0]["Risk"]["weed_pollen"];

if ($pollen["grass_risk"]=="None"){$pollen["grass_index"]="0";$pollen["grass_color"]="#cecece";}
else if ($pollen["grass_risk"]=="Low"){$pollen["grass_index"]="1";$pollen["grass_color"]="#b4e949";}
else if ($pollen["grass_risk"]=="Moderate"){$pollen["grass_index"]="2";$pollen["grass_color"]="#e3bd40";}
else if ($pollen["grass_risk"]=="High"){$pollen["grass_index"]="3";$pollen["grass_color"]="#e27a2e";}
else if ($pollen["grass_risk"]=="Very High"){$pollen["grass_index"]="4";$pollen["grass_color"]="#ea3323";}

if ($pollen["tree_risk"]=="None"){$pollen["tree_index"]="0";$pollen["tree_color"]="#cecece";}
else if ($pollen["tree_risk"]=="Low"){$pollen["tree_index"]="1";$pollen["tree_color"]="#b4e949";}
else if ($pollen["tree_risk"]=="Moderate"){$pollen["tree_index"]="2";$pollen["tree_color"]="#e3bd40";}
else if ($pollen["tree_risk"]=="High"){$pollen["tree_index"]="3";$pollen["tree_color"]="#e27a2e";}
else if ($pollen["tree_risk"]=="Very High"){$pollen["tree_index"]="4";$pollen["tree_color"]="#ea3323";}

if ($pollen["weed_risk"]=="None"){$pollen["weed_index"]="0";$pollen["weed_color"]="#cecece";}
else if ($pollen["weed_risk"]=="Low"){$pollen["weed_index"]="1";$pollen["weed_color"]="#b4e949";}
else if ($pollen["weed_risk"]=="Moderate"){$pollen["weed_index"]="2";$pollen["weed_color"]="#e3bd40";}
else if ($pollen["weed_risk"]=="High"){$pollen["weed_index"]="3";$pollen["weed_color"]="#e27a2e";}
else if ($pollen["weed_risk"]=="Very High"){$pollen["weed_index"]="4";$pollen["weed_color"]="#ea3323";}

?>

    <div class="chartforecast2">
       <span class="yearpopup"><a alt="pollen" title="Pollen Data" href="dvmMenuPollenData.php" data-lity><?php echo $menucharticonpage;?> Pollen Data</a></span>
    </div>
    <span class='moduletitle2'><?php echo $lang['PollenModule'];?></span>


<div class="updatedtime1"><?php if(file_exists('jsondata/pollen.txt')&&time() - filemtime('jsondata/pollen.txt')>1900)echo $offline. '<offline> Offline </offline>';else echo $online." ".$pollen["updated_time"];?></div>


<html>

<script src='js/d3.min.js'></script>    
<style>
.grasspos {margin-top: -2px; margin-left: -210px;}
.treepos {margin-top: -155px; margin-left: 0px;}
.weedpos {margin-top: -155px; margin-left: 210px;}
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
       

<div class="grasspos">
<div class="grass"></div>
</div>
        
    <script>
   
    var width = 95,
    height = 150;
    
    var riskGrass = "<?php echo $pollen["grass_risk"];?>"; 
        
    var currentGrass = "<?php echo $pollen["grass_index"];?>";
    currentGrass = currentGrass || 0;
    
    var maxGrass = 3;
    maxGrass = maxGrass || 0;
    
    var minGrass = 0; 

    var bottomY = height + 10,
    topY = 0,
    bulbRadius = 25.5,
    tubeWidth = 35,
    tubeBorderWidth = 1,
    grassColor = "<?php echo $pollen["grass_color"];?>",
    tubeBorderColor = "#999999";

var bulb_cy = bottomY - bulbRadius,
    bulb_cx = width / 2,
    top_cy = topY + tubeWidth / 2;

var svg = d3.select(".grass")
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
var step = 1;

// Determine a suitable range for the grass scale
var domain = [
  step * Math.floor(minGrass / step),
  step * Math.ceil(maxGrass / step)
  ];

if (minGrass - domain[0] < 0.0 * step)
  domain[0] -= step;

if (domain[1] - maxGrass < 0.66 * step)
  domain[1] += step;

// D3 scale object
var scale = d3.scale.linear()
    .range([bulb_cy - bulbRadius / 2 - 8.5, top_cy])
    .domain(domain);
    
[maxGrass].forEach(function(t) {

  var isMax = (t == maxGrass),
      label = (isMax ? "Max" : "min"),
      textCol = (isMax ? baseTextColor : baseTextColor),
      textOffset = (isMax ? - 3 : 3);
      
});

var tubeFill_bottom = bulb_cy,
    tubeFill_top = scale(currentGrass);

// Rect element for the grass tube
svg.append("rect")
    .attr("x", width / 2 - (tubeWidth - 20.5) / 2)
    .attr("y", tubeFill_top)
    .attr("rx", 2)
    .attr("width", tubeWidth - 11)
    .attr("height", tubeFill_bottom - 17 - tubeFill_top)
    .style("fill", grassColor);

// Values to use along the scale ticks up the grass tube
var tickValues = d3.range((domain[1] - domain[0]) / step + 1).map(function(v) { return domain[0] + v * step; });

// D3 axis object for the grass scale
var axis = d3.svg.axis()
    .scale(scale)
    .innerTickSize(7)
    .outerTickSize(0)
    .tickValues(tickValues)
    .orient("left").tickFormat(d3.format("d"));

// Add the axis to the image
var svgAxis = svg.append("g")
    .attr("id", "GrassScale")
    .attr("transform", "translate(" + (width / 2 + 5 - tubeWidth / 2) + ",0)")
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
    
// text output grass    
svg.append("text")
    .text("Grass")
    .attr("x", width / 2)
    .attr("y", 133)
    .attr("text-anchor", "middle")
    .style("font-size", "9px")
    .style("font-family", "Helvetica")
    .style("fill", baseTextColor);
    
svg.append("text") // risk text output
    .text("Risk " + riskGrass)
    .attr("x", width / 2)
    .attr("y", 143)
    .style("text-anchor", "middle")
    .style("font-size", "8px")
    .style("font-family", "Helvetica")    
    .style("fill", baseTextColor);


            
</script>
<div class="treepos">
<div class="tree"></div>
</div>
        
    <script>
      

    var width = 95,
    height = 150;

    var riskTree = "<?php echo $pollen["tree_risk"];?>"; 
    
    var currentTree = "<?php echo $pollen["tree_index"];?>";
    currentTree = currentTree || 0;
    
    var maxTree = 3;
    maxTree = maxTree || 0;
    
    var minTree = 0;
       
var bottomY = height + 10,
    topY = 0,
    bulbRadius = 25.5,
    tubeWidth = 35,
    tubeBorderWidth = 1,
    treeColor = "<?php echo $pollen["tree_color"];?>",
    tubeBorderColor = "#999999";

var bulb_cy = bottomY - bulbRadius,
    bulb_cx = width / 2,
    top_cy = topY + tubeWidth / 2;

var svg = d3.select(".tree")
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
var step = 1;

// Determine a suitable range for the tree scale
var domain = [
  step * Math.floor(minTree / step),
  step * Math.ceil(maxTree / step)
  ];

if (minTree - domain[0] < 0.0 * step)
  domain[0] -= step;

if (domain[1] - maxTree < 0.66 * step)
  domain[1] += step;

// D3 scale object
var scale = d3.scale.linear()
    .range([bulb_cy - bulbRadius / 2 - 8.5, top_cy])
    .domain(domain);
    
[maxTree].forEach(function(t) {

  var isMax = (t == maxTree),
      label = (isMax ? "Max" : "min"),
      textCol = (isMax ? "silver" : "silver"),
      textOffset = (isMax ? - 3 : 3);
      
});

var tubeFill_bottom = bulb_cy,
    tubeFill_top = scale(currentTree);

// Rect element for the tree tube
svg.append("rect")
    .attr("x", width / 2 - (tubeWidth - 20.5) / 2)
    .attr("y", tubeFill_top)
    .attr("rx", 2)
    .attr("width", tubeWidth - 11)
    .attr("height", tubeFill_bottom - 17 - tubeFill_top)
    .style("fill", treeColor);

// Values to use along the scale ticks up the tree tube
var tickValues = d3.range((domain[1] - domain[0]) / step + 1).map(function(v) { return domain[0] + v * step; });

// D3 axis object for the tree scale
var axis = d3.svg.axis()
    .scale(scale)
    .innerTickSize(7)
    .outerTickSize(0)
    .tickValues(tickValues)
    .orient("left").tickFormat(d3.format("d"));

// Add the axis to the image
var svgAxis = svg.append("g")
    .attr("id", "TreeScale")
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

// text output tree  
svg.append("text")
    .text("Tree ")
    .attr("x", width / 2)
    .attr("y", 133)
    .attr("text-anchor", "middle")
    .style("font-size", "9px")
    .style("font-family", "Helvetica")
    .style("fill", baseTextColor);
   
 svg.append("text") // tree risk text output
    .attr("x", width / 2)
    .attr("y", 143)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "8px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("Risk " + riskTree);
  

           
</script>  
<div class="weedpos">
<div class="weed"></div>
</div>
        
    <script>

    var width = 95,
    height = 150;

    var riskWeed = "<?php echo $pollen["weed_risk"];?>";

    var currentWeed = "<?php echo $pollen["weed_index"];?>";
    currentWeed = currentWeed || 0;
    
    var maxWeed = 3;
    maxWeed = maxWeed || 0;
    
    var minWeed = 0;    
    
var bottomY = height + 10,
    topY = 0,
    bulbRadius = 25.5,
    tubeWidth = 35,
    tubeBorderWidth = 1,
    weedColor = "<?php echo $pollen["weed_color"];?>",
    tubeBorderColor = "#999999";

var bulb_cy = bottomY - bulbRadius,
    bulb_cx = width / 2,
    top_cy = topY + tubeWidth / 2;

var svg = d3.select(".weed")
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
var step = 1;

// Determine a suitable range for the weed scale
var domain = [
  step * Math.floor(minWeed / step),
  step * Math.ceil(maxWeed / step)
  ];

if (minWeed - domain[0] < 0.0 * step)
  domain[0] -= step;

if (domain[1] - maxWeed < 0.66 * step)
  domain[1] += step;

// D3 scale object
var scale = d3.scale.linear()
    .range([bulb_cy - bulbRadius / 2 - 8.5, top_cy])
    .domain(domain);

[maxWeed].forEach(function(t) {

  var isMax = (t == maxWeed),
      label = (isMax ? "max" : "min"),
      textCol = (isMax ? "rgb(230, 0, 0)" : "rgb(0, 0, 230)"),
      textOffset = (isMax ? -4 : 4);

});

var tubeFill_bottom = bulb_cy,
    tubeFill_top = scale(currentWeed);

// Rect element for the weed tube
svg.append("rect")
    .attr("x", width / 2 - (tubeWidth - 20.50) / 2)
    .attr("y", tubeFill_top)
    .attr("rx", 2)
    .attr("width", tubeWidth - 11)
    .attr("height", tubeFill_bottom - 17 - tubeFill_top)
    .style("fill", weedColor);

// Values to use along the scale ticks up the weed tube
var tickValues = d3.range((domain[1] - domain[0]) / step + 1).map(function(v) { return domain[0] + v * step; });

// D3 axis object for the weed scale
var axis = d3.svg.axis()
    .scale(scale)
    .innerTickSize(7)
    .outerTickSize(0)
    .tickValues(tickValues)
    .orient("left").tickFormat(d3.format("d"));

// Add the axis to the image
var svgAxis = svg.append("g")
    .attr("id", "WeedScale")
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

// text output weed  
svg.append("text")
    .text("Weed ")
    .attr("x", width / 2)
    .attr("y", 133)
    .attr("text-anchor", "middle")
    .style("font-size", "9px")
    .style("font-family", "Helvetica")
    .style("fill", baseTextColor);

svg.append("text") // weed risk text output
    .attr("x", width / 2)
    .attr("y", 143)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "8px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("Risk " + riskWeed);
  

            
</script>          
</html>