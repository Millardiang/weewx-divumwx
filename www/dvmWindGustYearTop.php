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

$image["image"] = "img/meteocons/umbrella-wind-static.svg";

if($wind["gust_year_max"]>=0){$colormax = "#487ea9";}
else if($wind["gust_year_max"]>=5){$colormax = "#3b9cac";}
else if($wind["gust_year_max"]>10){$colormax = "#9aba2f";}
else if($wind["gust_year_max"]>20){$colormax = "#e6a141";}
else if($wind["gust_year_max"]>25){$colormax = "#ec5a34";}
else if($wind["gust_year_max"]>30){$colormax = "#d05f2d";}
else if($wind["gust_year_max"]>35){$colormax = "#d65b4a";}
else if($wind["gust_year_max"]>40){$colormax = "#dc4953";}
else {$wind["gust_year_max"] = $colormax = "#e26870";}

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
<div id="svg"></div>

	<script>

	var theme = "<?php echo $theme;?>";
	var image = "<?php echo $image["image"];?>";
	
	var year = "<?php echo date('Y');?>";		
	var maxcolor = "<?php echo $colormax;?>";
	 	
	var units = "<?php echo $wind["units"];?>";
	
	if (units === 'km/h') {
	
	var maxGustY = "<?php echo $wind["gust_year_max"];?>";	
	var maxGustM = "<?php echo $wind["gust_month_max"];?>";
	
	} else if (units === 'mph') {
	 		
	var maxGustY = "<?php echo number_format($wind["gust_year_max"]*0.621371,0);?>";	
	var maxGustM = "<?php echo number_format($wind["gust_month_max"]*0.621371,0);?>";
	
	} else if (units === 'm/s') {
	
	var maxGustY = "<?php echo number_format($wind["gust_year_max"]*0.277778,0);?>";	
	var maxGustM = "<?php echo number_format($wind["gust_month_max"]*0.277778,0);?>";
	}
		
	var maxdateGustYear = "<?php echo $wind["gust_year_maxtime2"];?>";	
	var maxdateGustMonth = "<?php echo $wind["gust_month_maxtime2"];?>";
 
		
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
			.style('stroke', maxcolor)
			.style('fill', maxcolor);
			
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
			.style("font-size", "12px")
			.style("text-anchor", "middle")
			.style("font-weight", "bold")
			.text(maxGustM+" "+units);
			
		// max gust month date text	
		svg.append("text")		
			.attr("x", 50)
			.attr("y", 47)
			.style("fill", "black")
			.style("font-family", "Helvetica")
			.style("font-size", "9px")
			.style("text-anchor", "middle")
			.style("font-weight", "bold")
			.text(maxdateGustMonth);

		// max Gust circle			
		svg.append("circle")
			.attr("cx", 180)			
			.attr("cy", 30)
			.attr("r", 25)
			.style('stroke', maxcolor)
			.style('fill', maxcolor);
			
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
			.style("font-size", "12px")
			.style("text-anchor", "middle")
			.style("font-weight", "bold")
			.text(maxGustY+" "+units);
			
		// max gust year date text	
		svg.append("text")		
			.attr("x", 180)
			.attr("y", 47)
			.style("fill", "black")
			.style("font-family", "Helvetica")
			.style("font-size", "9px")
			.style("text-anchor", "middle")
			.style("font-weight", "bold")
			.text(maxdateGustYear);

</script> 
