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
<?php
include('dvmCombinedData.php');

$image["image"] = "img/meteocons/umbrella-rain.svg";

?>

<div class="title"><?php echo $info; echo $lang["RainfallTop"];?></div>

<script src="js/d3.min.js"></script>

<style>
.rainfallTop {
  position: relative;
  margin-top: 20px;
  margin-left: 0px;
}
</style>

<div class="rainfallTop"></div>

	<script>

	var theme = "<?php echo $theme;?>";
	var image = "<?php echo $image["image"];?>";	
	var color = "#487ea9";	 	
	var units = "<?php echo $rain["units"];?>";		
	var rainY = "<?php echo $rain["year_total"];?>";	
	var rainM = "<?php echo $rain["month_total"];?>";			
	var Year = "<?php echo date('Y');?>";	
	var Month = "<?php echo date('M');?>";
 
		
		var svg = d3.select(".rainfallTop")
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
			.attr('width', 50)
			.attr('height', 50)
			.attr('x', 89)
			.attr('y', 5);
		
		// max rainfall month circle			
		svg.append("circle")
			.attr("cx", 50)			
			.attr("cy", 30)
			.attr("r", 25)
			.style('stroke', color)
			.style('fill', color);
			
		svg.append("text")		
			.attr("x", 50)
			.attr("y", 18)
			.style("fill", "black")
			.style("font-family", "Helvetica")
			.style("font-size", "9px")
			.style("text-anchor", "middle")
			.style("font-weight", "bold")
			.text("Total");
		
		// max rainfall month text	
		svg.append("text")		
			.attr("x", 50)
			.attr("y", 33)
			.style("fill", "black")
			.style("font-family", "Helvetica")
			.style("font-size", "11px")
			.style("text-anchor", "middle")
			.style("font-weight", "bold")
			.text(rainM+" "+units);
			
		// max rainfall month text	
		svg.append("text")		
			.attr("x", 50)
			.attr("y", 47)
			.style("fill", "black")
			.style("font-family", "Helvetica")
			.style("font-size", "9px")
			.style("text-anchor", "middle")
			.style("font-weight", "bold")
			.text(Month);
	
		// max rainfall year circle			
		svg.append("circle")
			.attr("cx", 180)			
			.attr("cy", 30)
			.attr("r", 25)
			.style('stroke', color)
			.style('fill', color);
			
		svg.append("text")		
			.attr("x", 180)
			.attr("y", 18)
			.style("fill", "black")
			.style("font-family", "Helvetica")
			.style("font-size", "9px")
			.style("text-anchor", "middle")
			.style("font-weight", "bold")
			.text("Total");
			
		// max rainfall year text	
		svg.append("text")		
			.attr("x", 180)
			.attr("y", 33)
			.style("fill", "black")
			.style("font-family", "Helvetica")
			.style("font-size", "11px")
			.style("text-anchor", "middle")
			.style("font-weight", "bold")
			.text(rainY+" "+units);
			
		//  year text	
		svg.append("text")		
			.attr("x", 180)
			.attr("y", 47)
			.style("fill", "black")
			.style("font-family", "Helvetica")
			.style("font-size", "9px")
			.style("text-anchor", "middle")
			.style("font-weight", "bold")
			.text(Year);

</script> 
