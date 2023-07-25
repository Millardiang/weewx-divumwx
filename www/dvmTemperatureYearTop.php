<?php
#####################################################################################################################                                                                                                        #
#                                                                                                                   #
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
<?php include('dvmCombinedData.php');

if(anyToC($temp["outside_year_max"])<=-10){$tempcolormax = "#8781bd";}
else if(anyToC($temp["outside_year_max"])<=0){$tempcolormax = "#487ea9";}
else if(anyToC($temp["outside_year_max"])<=5){$tempcolormax = "#3b9cac";}
else if(anyToC($temp["outside_year_max"])<10){$tempcolormax = "#9aba2f";}
else if(anyToC($temp["outside_year_max"])<20){$tempcolormax = "#e6a141";}
else if(anyToC($temp["outside_year_max"])<25){$tempcolormax = "#ec5a34";}
else if(anyToC($temp["outside_year_max"])<30){$tempcolormax = "#d05f2d";}
else if(anyToC($temp["outside_year_max"])<35){$tempcolormax = "#d65b4a";}
else if(anyToC($temp["outside_year_max"])<40){$tempcolormax = "#dc4953";}
else if(anyToC($temp["outside_year_max"])<100){$tempcolormax = "#e26870";}

if(anyToC($temp["outside_year_min"])<=-10){$tempcolormin = "#8781bd";}
else if(anyToC($temp["outside_year_min"])<=0){$tempcolormin = "#487ea9";}
else if(anyToC($temp["outside_year_min"])<=5){$tempcolormin = "#3b9cac";}
else if(anyToC($temp["outside_year_min"])<10){$tempcolormin = "#9aba2f";}
else if(anyToC($temp["outside_year_min"])<20){$tempcolormin = "#e6a141";}
else if(anyToC($temp["outside_year_min"])<25){$tempcolormin = "#ec5a34";}
else if(anyToC($temp["outside_year_min"])<30){$tempcolormin = "#d05f2d";}
else if(anyToC($temp["outside_year_min"])<35){$tempcolormin = "#d65b4a";}
else if(anyToC($temp["outside_year_min"])<40){$tempcolormin = "#dc4953";}
else if(anyToC($temp["outside_year_min"])<100){$tempcolormin = "#e26870";}

?>

<div class="title"><?php echo $info; echo $lang["TemperatureTop"]; ?></div>

<script src="js/d3.min.js"></script>

<style>
.tempTop {
  position: relative;
  margin-top: 20px;
  margin-left: 0px;
}
</style>

<div class="tempTop"></div>

	<script>

	var theme = "<?php echo $theme;?>";
	
	var mincolor = "<?php echo $tempcolormin;?>";	
	var maxcolor = "<?php echo $tempcolormax;?>"; 	
	var units = "<?php echo $temp["units"];?>";	
	var maxtemp = "<?php echo $temp["outside_year_max"];?>";	
	var mintemp = "<?php echo $temp["outside_year_min"];?>";	
	var maxdate = "<?php echo $temp["outside_year_maxtime2"];?>";	
	var mindate = "<?php echo $temp["outside_year_mintime2"];?>";  
		
		var svg = d3.select(".tempTop")
					.append("svg")
					//.style("background", "#292E35")
					.attr("width", 230)
					.attr("height", 60);
		
if (theme === 'dark') {

			svg.append("circle")
			.attr("cx", 180)			
			.attr("cy", 30)
			.attr("r", 27.5)
			.style('stroke', "#999999")
			.style("stroke-width", 1.0)
			.style('fill', "none");

			svg.append("circle")
			.attr("cx", 50)			
			.attr("cy", 30)
			.attr("r", 27.5)
			.style('stroke', "#999999")
			.style("stroke-width", 1.0)
			.style('fill', "none");

			var arc = d3.svg.arc() // top arc
    			.innerRadius(4.35)
    			.outerRadius(4.35)
    			.startAngle(-90 * (Math.PI/180))
    			.endAngle(+90 * (Math.PI/180))    			
    	svg.append("path")
    			.attr("d", arc)
    			.style("stroke", "#999999")
  				.style("fill", "#999999")
    			.attr("transform", "translate(115.65,9)");

  		svg.append("line") // left horizontal line
    			.attr("x1", 111.25)
    			.attr("x2", 111.25)
    			.attr("y1", 9)
    			.attr("y2", 45)
    			.style("stroke", "#999999")
    			.style("stroke-width", "1.0");

    	svg.append("line") // left horizontal line
    			.attr("x1", 120.1)
    			.attr("x2", 120.1)
    			.attr("y1", 9)
    			.attr("y2", 45)
    			.style("stroke", "#999999")
    			.style("stroke-width", "1.0");

var data = d3.range(0, 20);
// color fill (blended)
var colors = d3.scale.linear()
    	.domain([20, 15, 10, 5, 0])
    	.range([ 
      "#8781bd",
      "#3b9cac", 
      "#e6a141", 
      "#9aba2f",  
      "#e26870"
      ]);
    
var rects = svg.selectAll(".rects.two")
  		.data(data)
  		.enter()
  		.append("rect")
  		.attr("class", "two")
  		.attr("y", (d,i) => 10.5 + i * 1.7)
  		.attr("height", 5)
  		.attr("x", 111.25)
  		.attr("width", 9)
  		.attr("fill", d => colors(d));

  svg.append("circle")
			.attr("cx", 115.75)			
			.attr("cy", 47.5)
			.attr("r", 9)
			.style('stroke', "#3b9cac")
			.style('fill', "#3b9cac");

} else {

svg.append("circle")
			.attr("cx", 180)			
			.attr("cy", 30)
			.attr("r", 27.5)
			.style('stroke', "black")
			.style("stroke-width", 1.0)
			.style('fill', "none");

			svg.append("circle")
			.attr("cx", 50)			
			.attr("cy", 30)
			.attr("r", 27.5)
			.style('stroke', "black")
			.style("stroke-width", 1.0)
			.style('fill', "none");
					
			var arc = d3.svg.arc() // top arc
    			.innerRadius(4.35)
    			.outerRadius(4.35)
    			.startAngle(-90 * (Math.PI/180))
    			.endAngle(+90 * (Math.PI/180))    			
    	svg.append("path")
    			.attr("d", arc)
    			.style("stroke", "black")
  				.style("fill", "black")
    			.attr("transform", "translate(115.75,9)");

  		svg.append("line") // left horizontal line
    			.attr("x1", 111.25)
    			.attr("x2", 111.25)
    			.attr("y1", 9)
    			.attr("y2", 45)
    			.style("stroke", "black")
    			.style("stroke-width", "1.0");

    	svg.append("line") // left horizontal line
    			.attr("x1", 120.1)
    			.attr("x2", 120.1)
    			.attr("y1", 9)
    			.attr("y2", 45)
    			.style("stroke", "black")
    			.style("stroke-width", "1.0");

var data = d3.range(0, 20);
// color fill (blended)
var colors = d3.scale.linear()
    	.domain([20, 15, 10, 5, 0])
    	.range([ 
      "#8781bd",
      "#3b9cac", 
      "#e6a141",  
      "#9aba2f",  
      "#e26870"]);
    
var rects = svg.selectAll(".rects.two")
  		.data(data)
  		.enter()
  		.append("rect")
  		.attr("class", "two")
  		.attr("y", (d,i) => 10.5 + i * 1.7)
  		.attr("height", 5)
  		.attr("x", 111.25)
  		.attr("width", 9)
  		.attr("fill", d => colors(d));

  svg.append("circle")
			.attr("cx", 115.75)			
			.attr("cy", 47.5)
			.attr("r", 8)
			.style('stroke', "#3b9cac")
			.style('fill', "#3b9cac");
}	
			// min temp circle			
		svg.append("circle")
			.attr("cx", 50)			
			.attr("cy", 30)
			.attr("r", 25)
			.style('stroke', mincolor)
			.style('fill', mincolor);	 

		// max temp circle			
		svg.append("circle")
			.attr("cx", 180)			
			.attr("cy", 30)
			.attr("r", 25)
			.style('stroke', maxcolor)
			.style('fill', maxcolor);

				// min temp text	
		svg.append("text")		
			.attr("x", 50)
			.attr("y", 35)
			.style("fill", "black")
			.style("font-family", "Helvetica")
			.style("font-size", "15px")
			.style("text-anchor", "middle")
			.style("font-weight", "bold")
			.text(d3.format(".1f")(mintemp)+"°"+units);
			
		// min date text	
		svg.append("text")		
			.attr("x", 50)
			.attr("y", 47)
			.style("fill", "black")
			.style("font-family", "Helvetica")
			.style("font-size", "9px")
			.style("text-anchor", "middle")
			.style("font-weight", "bold")
			.text(mindate);

			svg.append("text")		
			.attr("x", 50)
			.attr("y", 18)
			.style("fill", "black")
			.style("font-family", "Helvetica")
			.style("font-size", "9px")
			.style("text-anchor", "middle")
			.style("font-weight", "bold")
			.text("Min");

		svg.append("text")		
			.attr("x", 180)
			.attr("y", 18)
			.style("fill", "black")
			.style("font-family", "Helvetica")
			.style("font-size", "9px")
			.style("text-anchor", "middle")
			.style("font-weight", "bold")
			.text("Max");
			
		// max temp text	
		svg.append("text")		
			.attr("x", 180)
			.attr("y", 35)
			.style("fill", "black")
			.style("font-family", "Helvetica")
			.style("font-size", "15px")
			.style("text-anchor", "middle")
			.style("font-weight", "bold")
			.text(d3.format(".1f")(maxtemp)+"°"+units);
			
		// max date text	
		svg.append("text")		
			.attr("x", 180)
			.attr("y", 47)
			.style("fill", "black")
			.style("font-family", "Helvetica")
			.style("font-size", "9px")
			.style("text-anchor", "middle")
			.style("font-weight", "bold")
			.text(maxdate);

</script>
