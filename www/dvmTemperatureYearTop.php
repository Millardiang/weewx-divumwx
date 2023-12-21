<?php
#####################################################################################################################                                                                                 
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

$image["image"] = "img/meteocons/thermometer.svg";
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
	var image = "<?php echo $image["image"];?>";
	var mincolor = "<?php echo $colorOutTempYearMin;?>";	
	var maxcolor = "<?php echo $colorOutTempYearMax;?>"; 	
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
					
}

		svg.append('image') // image output
			.attr('xlink:href', image)
			.attr('width', 80)
			.attr('height', 80)
			.attr('x', 76)
			.attr('y', -8);	

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
			.attr("y", 34)
			.style("fill", "black")
			.style("font-family", "Helvetica")
			.style("font-size", "12px")
			.style("text-anchor", "middle")
			.style("font-weight", "bold")
			.text(d3.format(".1f")(mintemp) + "°" + units);
			
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
			.attr("y", 34)
			.style("fill", "black")
			.style("font-family", "Helvetica")
			.style("font-size", "12px")
			.style("text-anchor", "middle")
			.style("font-weight", "bold")
			.text(d3.format(".1f")(maxtemp) + "°" + units);
			
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
