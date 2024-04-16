<?php 
include('dvmCombinedData.php');
if($temp["units"] == "C"){
$temp["indoor_now_feels"] = ($temp["indoor_now_feels"]-32)/9*5;}
?>
<!DOCTYPE html>
<head>
<meta charset="utf-8">
<title>Indoor Temperature conditions</title>
</head>

<div class="chartforecast">
<span class="yearpopup"><a alt="Indoor Chart" title="Indoor Chart" href="dvmMenuIndoorTemperaturePopup.php" data-lity><?php echo $menucharticonpage;?> Indoor Temp | Humidity Chart</a></span>      
</div>
<span class='moduletitle'><?php echo $lang['indoorTempModule'];?> (<valuetitleunit>&deg;<?php echo $temp["units"];?></valuetitleunit>)</span> </span>

<div class="updatedtime1"><span><?php if(file_exists($livedata)&&time() - filemtime($livedata)>300)echo $offline. '<offline> Offline </offline>'; else echo $online." ".$divum["time"];?></div><br/>
<style>
silver {
	color: silver;
}
</style> 
<div class="tempindoorconverter">
<?php
  if ($temp["units"]=='C'){echo "<div class=tempconvertercircleminus10 style='$convertStyle $colorInTemp;'>".number_format(($temp["indoor_now"]*9/5)+32,1).'<silver>&deg<smalltempunit2>F</silver>';} else if ($temp["units"]=='F'){echo "<div class=tempconvertercircleminus10 style='$convertStyle $colorInTemp;'>".number_format(($temp["indoor_now"]-32)*5/9,1).'<silver>&deg<smalltempunit2>C</silver>';}
?>
</smalltempunit2></div></div>


<style>

.tempindoorconverter {
  margin-left: 251px;
  margin-top: -20px
}

.house {
  position: relative; 
  margin-top: -21.5px; 
  margin-left: 0px;
}
.idtemppos {
  margin-top: -153px;
  margin-left: -135.5px;
}
</style>

<script src="js/d3.min.js"></script>

<div class="house"></div>
<div class="idtemppos">
<div class="idthermometer"></div>
</div>

<script>
            
    var theme = "<?php echo $theme;?>";

    if (theme === 'dark') {
    var tubeFillColor = "rgba(45,47,50,1)";
    } else {
    var tubeFillColor = "rgba(230,232,239,1)";
    }

</script>

<script>

	var theme = "<?php echo $theme;?>";

	var humidity = "<?php echo $humid["indoors_now"];?>";
	humidity = humidity || 0;

	var feels = "<?php echo number_format($temp["indoor_now_feels"],1);?>";
	feels = feels || 0;

	var h_trend = "<?php echo $humid["indoors_trend"];?>";
	h_trend = h_trend || 0; 
		
	var hcolor = "<?php echo $colorInHumidity;?>";

	var fcolor = "<?php echo $colorFeels;?>";
	
	var units = "<?php echo $temp["units"];?>";
 

var svg = d3.select(".house")
    			.append("svg")
    			//.style("background", "#292E35")
    			.attr("width", 310)
    			.attr("height", 150);
    			    			
    			// begin drawing a house of sorts ( Das ist das Hause von Nikolaus und neben an wohnt der Weihnachtsmann :-) )
    			    			
    			svg.append("line") // floor
    			.attr("x1", 25)
    			.attr("x2", 150)
    			.attr("y1", 145)
    			.attr("y2", 145)
    			.style("stroke", "#38383c")
    			.style("stroke-width", "3px")
    			.style("stroke-linecap", "round");
    			    			
    			svg.append("line") // left wall
    			.attr("x1", 25)
    			.attr("x2", 25)
    			.attr("y1", 71)
    			.attr("y2", 145)
    			.style("stroke", "#38383c")
    			.style("stroke-width", "3px")
    			.style("stroke-linecap", "round");
    			    			    			
    			// roof structure left
    			svg.append("line") // left horizontal line
    			.attr("x1", 9)
    			.attr("x2", 25)
    			.attr("y1", 70.25)
    			.attr("y2", 70.25)
    			.style("stroke", "#38383c")
    			.style("stroke-width", "3px")
    			.style("stroke-linecap", "round");
    			
    			var arc = d3.svg.arc() // bottom left arc
    			.innerRadius(5)
    			.outerRadius(8)
    			.startAngle(-45 * (Math.PI/180))
    			.endAngle(-3)    			
    			svg.append("path")
    			.attr("d", arc)
    			.attr("fill", "#38383c")
    			.attr("transform", "translate(10,63.75)");

    			svg.append("line") //  roof left diagonal line
    			.attr("x1", 5)
    			.attr("x2", 83)
    			.attr("y1", 59.5)
    			.attr("y2", 5)
    			.style("stroke", "#38383c")
    			.style("stroke-width", "3px")
    			.style("stroke-linecap", "round");
    			
    			var arc = d3.svg.arc() // Apex arc left
    			.innerRadius(5)
    			.outerRadius(8)
    			.startAngle(-45 * (Math.PI/180))
    			.endAngle(0)   			
    			svg.append("path")
    			.attr("d", arc)
    			.attr("fill", "#38383c")
    			.attr("transform", "translate(87.5,9.75)");
    			
    			svg.append("line") // right wall
    			.attr("x1", 150)
    			.attr("x2", 150)
    			.attr("y1", 71)
    			.attr("y2", 145)
    			.style("stroke", "#38383c")
    			.style("stroke-width", "3px")
    			.style("stroke-linecap", "round");
    			    		   			
    			// roof structure right
    			svg.append("line") // right horizontal line
    			.attr("x1", 150)
    			.attr("x2", 165)
    			.attr("y1", 70.25)
    			.attr("y2", 70.25)
    			.style("stroke", "#38383c")
    			.style("stroke-width", "3px")
    			.style("stroke-linecap", "round");
    			    
				var arc = d3.svg.arc() // bottom right arc
    			.innerRadius(5)
    			.outerRadius(8)
    			.startAngle(45 * (Math.PI/180))
    			.endAngle(3)   			
    			svg.append("path")
    			.attr("d", arc)
    			.attr("fill", "#38383c")
    			.attr("transform", "translate(164,63.75)");
    			
    			svg.append("line") //  roof diagonal to Chimney left line
    			.attr("x1", 92)
    			.attr("x2", 127)
    			.attr("y1", 5)
    			.attr("y2", 30)
    			.style("stroke", "#38383c")
    			.style("stroke-width", "3px")
    			.style("stroke-linecap", "round");
    			
    			svg.append("line") //  roof diagonal to Chimney right line
    			.attr("x1", 145)
    			.attr("x2", 169)
    			.attr("y1", 42)
    			.attr("y2", 59.5)
    			.style("stroke", "#38383c")
    			.style("stroke-width", "3px")
    			.style("stroke-linecap", "round");
    			    			    			   			
    			var arc = d3.svg.arc() // Apex arc right
    			.innerRadius(5)
    			.outerRadius(8)
    			.startAngle(45 * (Math.PI/180))
    			.endAngle(0)   			
    			svg.append("path")
    			.attr("d", arc)
    			.attr("fill", "#38383c")
    			.attr("transform", "translate(87.5,9.75)");
    			
    			// Chimney 
    			svg.append("line") //  chimney left virtical line
    			.attr("x1", 127)
    			.attr("x2", 127)
    			.attr("y1", 10)
    			.attr("y2", 30)
    			.style("stroke", "#38383c")
    			.style("stroke-width", "3px")
    			.style("stroke-linecap", "round");
    			
    			svg.append("line") //  chimney right virtical line
    			.attr("x1", 145)
    			.attr("x2", 145)
    			.attr("y1", 10)
    			.attr("y2", 42)
    			.style("stroke", "#38383c")
    			.style("stroke-width", "3px")
    			.style("stroke-linecap", "round");
    			
    			svg.append("line") //  chimney top horizontal line
    			.attr("x1", 127)
    			.attr("x2", 145)
    			.attr("y1", 10)
    			.attr("y2", 10)
    			.style("stroke", "#38383c")
    			.style("stroke-width", "3px")
    			.style("stroke-linecap", "round");

		if (theme === 'dark') {
    			
    			// Humidity
    		svg.append("text")	
				.attr("x", 235)
				.attr("y", 59)
				.attr("text-anchor", "middle")
				.style("font-size", "12px")
				.style("font-family", "Helvetica")
				.style("font-weight", "normal")
				.style("fill", "silver")
				.text("Humidity");
				
			svg.append("text")	
				.attr("x", 230)
				.attr("y", 77)
				.attr("text-anchor", "middle")
				.style("font-size", "10px")
				.style("font-family", "Helvetica")
				.style("font-weight", "normal")
				.style("fill", "silver")
				.text(humidity+"%");
				
			// feels temp	
    		svg.append("text")	
				.attr("x", 235)
				.attr("y", 95)
				.attr("text-anchor", "middle")
				.style("font-size", "12px")
				.style("font-family", "Helvetica")
				.style("font-weight", "normal")
				.style("fill", "silver")
				.text("Feels");
				
			svg.append("text")	
				.attr("x", 235)
				.attr("y", 113)
				.attr("text-anchor", "middle")
				.style("font-size", "10px")
				.style("font-family", "Helvetica")
				.style("font-weight", "normal")
				.style("fill", "silver")
				.text(feels + "\u00B0" + units);
						
				
		if (h_trend > 0) {
    	
    	 svg.append('polyline') // trend rising
                .attr('points', "245 77 248 73 251 75 255 70")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', "#ff7c39");
                
            svg.append('polyline') // trend rising
                .attr('points', "255 70 251.5 70 255 70 256 73")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', "#ff7c39");   		
                
		 } else if (h_trend < 0) {
		 		 
		 svg.append('polyline') // trend falling
                .attr('points', "245 69 248 73 251 71 255 76")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', "#3b9cac");
                
            svg.append('polyline') // trend falling
                .attr('points', "255 76 251.5 76 255 76 256 73")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', "#3b9cac");
		           
		} else if (h_trend == 0) {
		
		 	svg.append('polyline') // steady trend
                .attr('points', "247 70.5 250 73.5 247 76.5")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', "#90b12a");
		}
				
				
		} else {
		
				// Humidity
			svg.append("text")	
				.attr("x", 235)
				.attr("y", 59)
				.attr("text-anchor", "middle")
				.style("font-size", "12px")
				.style("font-family", "Helvetica")
				.style("font-weight", "normal")
				.style("fill", "black")
				.text("Humidity");
				
			svg.append("text")	
				.attr("x", 230)
				.attr("y", 77)
				.attr("text-anchor", "middle")
				.style("font-size", "10px")
				.style("font-family", "Helvetica")
				.style("font-weight", "normal")
				.style("fill", "black")
				.text(humidity+"%");
				
			// feels temp	
    		svg.append("text")	
				.attr("x", 235)
				.attr("y", 95)
				.attr("text-anchor", "middle")
				.style("font-size", "12px")
				.style("font-family", "Helvetica")
				.style("font-weight", "normal")
				.style("fill", "black")
				.text("Feels");
				
			if (units === 'C') {
				
			svg.append("text")	
				.attr("x", 235)
				.attr("y", 113)
				.attr("text-anchor", "middle")
				.style("font-size", "10px")
				.style("font-family", "Helvetica")
				.style("font-weight", "normal")
				.style("fill", "black")
				.text(feels+"\u00B0");
				
		} else {
		
			svg.append("text")	
				.attr("x", 235)
				.attr("y", 113)
				.attr("text-anchor", "middle")
				.style("font-size", "10px")
				.style("font-family", "Helvetica")
				.style("font-weight", "normal")
				.style("fill", "black")
				.text(feels+"��F");
				
			}
				
		if (h_trend > 0) {
    	
    	 svg.append('polyline') // trend rising
                .attr('points', "245 77 248 73 251 75 255 70")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', "#ff7c39");
                
            svg.append('polyline') // trend rising
                .attr('points', "255 70 251.5 70 255 70 256 73")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', "#ff7c39");   		
                
		 } else if (h_trend < 0) {
		 		 
		 svg.append('polyline') // trend falling
                .attr('points', "245 69 248 73 251 71 255 76")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', "#3b9cac");
                
            svg.append('polyline') // trend falling
                .attr('points', "255 76 251.5 76 255 76 256 73")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', "#3b9cac");
		           
		} else if (h_trend == 0) {
		
		 	svg.append('polyline') // steady trend
                .attr('points', "247 70.5 250 73.5 247 76.5")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', "#90b12a");
		}
								
			}

			var theme = "<?php echo $theme;?>";

			if (theme === 'dark') {
						
			svg.append("rect")
    			.attr("x", 206)
    			.attr("y", 66)
    			.attr("rx", 2)
    			.style("stroke", "#38383c")
    			.attr("height", 15)
    			.attr("width", 55)
    			.style("fill", "none");
    			
    		svg.append("rect")
    			.attr("x", 207)
    			.attr("y", 67)
    			.attr("rx", 0.5)
    			.style("stroke", hcolor)
    			.attr("height", 13)
    			.attr("width", 3)
    			.style("fill", hcolor);
    		    					
			svg.append("rect")
    			.attr("x", 206)
    			.attr("y", 102)
    			.attr("rx", 2)
    			.style("stroke", "#38383c")
    			.attr("height", 15)
    			.attr("width", 55)
    			.style("fill", "none");
    			
    		svg.append("rect")
    			.attr("x", 207)
    			.attr("y", 103)
    			.attr("rx", 0.5)
    			.style("stroke", fcolor)
    			.attr("height", 13)
    			.attr("width", 3)
    			.style("fill", fcolor);

    		} else {

    			svg.append("rect")
    			.attr("x", 206)
    			.attr("y", 66)
    			.attr("rx", 2)
    			.style("stroke", "#999999")
    			.attr("height", 15)
    			.attr("width", 55)
    			.style("fill", "none");
    			
    		svg.append("rect")
    			.attr("x", 207)
    			.attr("y", 67)
    			.attr("rx", 0.5)
    			.style("stroke", hcolor)
    			.attr("height", 13)
    			.attr("width", 3)
    			.style("fill", hcolor);
    		    					
			svg.append("rect")
    			.attr("x", 206)
    			.attr("y", 102)
    			.attr("rx", 2)
    			.style("stroke", "#999999")
    			.attr("height", 15)
    			.attr("width", 55)
    			.style("fill", "none");
    			
    		svg.append("rect")
    			.attr("x", 207)
    			.attr("y", 103)
    			.attr("rx", 0.5)
    			.style("stroke", fcolor)
    			.attr("height", 13)
    			.attr("width", 3)
    			.style("fill", fcolor);

    		}
    			
       			      		   		  			
</script>

<script>
    			
    var width = 80,
    height = 150;
        
    var units = "<?php echo $temp["units"];?>";

	var maxTemp = "<?php echo number_format($temp["indoor_day_max"],1);?>";
	maxTemp = maxTemp || 0;
	var minTemp = "<?php echo number_format($temp["indoor_day_min"],1);?>";
	minTemp = minTemp || 0;
   
    var currentTemp = "<?php echo $temp["indoor_now"];?>";
    currentTemp = currentTemp || 0;
    
    var mercuryColor = "<?php echo $colorInTemp;?>";

var bottomY = height - 5,
    topY = 15,
    bulbRadius = 25.5,
    tubeWidth = 25.5,
    tubeBorderWidth = 1,    
    innerBulbColor = "rgb(230, 200, 200)",
    tubeBorderColor = "#999999";

var bulb_cy = bottomY - bulbRadius,
    bulb_cx = width / 2,
    top_cy = topY + tubeWidth / 2;


var svg = d3.select(".idthermometer")
  .append("svg")
  .attr("width", width)
  .attr("height", height);

var defs = svg.append("defs");

// Define the radial gradient for the bulb fill colour
var bulbGradientx = defs.append("radialGradient")
  .attr("id", "bulbGradientx")
  .attr("cx", "50%")
  .attr("cy", "50%")
  .attr("r", "50%")
  .attr("fx", "50%")
  .attr("fy", "50%");

bulbGradientx.append("stop")
  .attr("offset", "0%")
  .style("stop-color", innerBulbColor);

bulbGradientx.append("stop")
  .attr("offset", "90%")
  .style("stop-color", mercuryColor); 

svg.append("line")
    .attr("x1", width / 2)
    .attr("x2", width / 2)
    .attr("y1", 20)
    .attr("y2", 120)
    .style("stroke", tubeBorderColor)
    .style("stroke-width", "15.75px")
    .style("fill", "none")    
    .style("stroke-linecap", "round");
    
svg.append("line")
    .attr("x1", width / 2)
    .attr("x2", width / 2)
    .attr("y1", 20)
    .attr("y2", 120)
    .style("stroke", tubeFillColor)
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
  .style("fill", "url(#bulbGradientx)")
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
	.attr("x", width / 2)
	.attr("y", 126)
	.attr("text-anchor", "middle")
	.style("font-size", "18px")
	.style("font-family", "Helvetica")
	.style("font-weight", "600")
	.style("fill", "rgba(30, 32, 36, 1)")
	.text(d3.format(".1f")(currentTemp));	
     			   			
</script>
