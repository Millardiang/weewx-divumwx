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

$image["image"] = "img/meteocons/umbrella-wind-static.svg";

?>

<div class="title"><?php echo $info; echo $lang["WindTop"]; ?></div>

<script src="js/d3.min.js"></script>

<style>
.windTop {
  position: relative;
  margin-top: 20px;
  margin-left: 0px;
}
</style>

<div class="windTop"></div>

<script>

	var theme = "<?php echo $theme;?>";
	var image = "<?php echo $image["image"];?>";		
	var maxcolorM = "<?php echo $color["windGust_month_max"];?>";
    var maxcolorY = "<?php echo $color["windGust_year_max"];?>";
	var units = "<?php echo $wind["units"];?>";
	var maxGustY = "<?php echo $wind["gust_year_max"];?>";	
	var maxGustM = "<?php echo $wind["gust_month_max"];?>";
	var Year = "<?php echo date('Y');?>";	
	var Month = "<?php echo date('M');?>";
 	
		var svg = d3.select(".windTop")
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
			.attr('width', 60)
			.attr('height', 60)
			.attr('x', 85)
			.attr('y', 0);
		
		// max gust month circle			
		svg.append("circle")
			.attr("cx", 50)			
			.attr("cy", 30)
			.attr("r", 25)
			.style('stroke', maxcolorM)
			.style('fill', maxcolorM);
			
		svg.append("text")		
			.attr("x", 50)
			.attr("y", 18)
			.style("fill", "black")
			.style("font-family", "Helvetica")
			.style("font-size", "9px")
			.style("text-anchor", "middle")
			.style("font-weight", "bold")
			.text("Max");
		
		// max gust month text	
		svg.append("text")		
			.attr("x", 50)
			.attr("y", 33)
			.style("fill", "black")
			.style("font-family", "Helvetica")
			.style("font-size", "10px")
			.style("text-anchor", "middle")
			.style("font-weight", "bold")
			.text(d3.format(".1f")(maxGustM) + " " + units);
			
		// max gust month date text	
		svg.append("text")		
			.attr("x", 50)
			.attr("y", 47)
			.style("fill", "black")
			.style("font-family", "Helvetica")
			.style("font-size", "9px")
			.style("text-anchor", "middle")
			.style("font-weight", "bold")
			.text(Month);

		// max Gust circle			
		svg.append("circle")
			.attr("cx", 180)			
			.attr("cy", 30)
			.attr("r", 25)
			.style('stroke', maxcolorY)
			.style('fill', maxcolorY);
			
		svg.append("text")		
			.attr("x", 180)
			.attr("y", 18)
			.style("fill", "black")
			.style("font-family", "Helvetica")
			.style("font-size", "9px")
			.style("text-anchor", "middle")
			.style("font-weight", "bold")
			.text("Max");
			
		// max Gust year text	
		svg.append("text")		
			.attr("x", 180)
			.attr("y", 33)
			.style("fill", "black")
			.style("font-family", "Helvetica")
			.style("font-size", "10px")
			.style("text-anchor", "middle")
			.style("font-weight", "bold")
			.text(d3.format(".1f")(maxGustY) + " " + units);
			
		// max gust year date text	
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
