<?php include('dvmCombinedData.php');
date_default_timezone_set($TZ);

?>

    <div class="chartforecast">
       <span class="yearpopup"><a alt="aquinfo" title="Lightning Almanac" href="dvmLightningAlmanac.php" data-lity><?php echo $info;?> Lightning Almanac</a></span>
    </div>
    <span class='moduletitle'><?php echo $lang['lightningModule'];?></span>


<div class="updatedtime1"><span><?php if(file_exists($livedata)&&time() - filemtime($livedata)>300) echo $offline. '<offline> Offline </offline>'; else echo $online." ".$weather["time"];?></div>

<html>
<?php

if ($lightning["source"] == "Boltek") {

$json = "jsondata/ngxarchive.json";
$jsonobj = file_get_contents($json);
$arr = json_decode($jsonobj, true); 
$lightning["bearingx"] = $arr['StrikeData'][0]['bng'];
$lightning["bearing"] = $arr['StrikeData'][0]['bng'];

	if (!isset($lightning["bearingx"])) {$lightning["bearingx"] = $lightning["bearingx"];}
		
	// Bearing	
	if ($lightning["bearingx"]<=11.25){$lightning["bearingx"]='North';}
	else if ($lightning["bearingx"]<=33.75){$lightning["bearingx"]='NNE';}
	else if ($lightning["bearingx"]<=56.25){$lightning["bearingx"]='NE';}
	else if ($lightning["bearingx"]<=78.75){$lightning["bearingx"]='ENE';}	
	else if ($lightning["bearingx"]<=101.25){$lightning["bearingx"]='East';}
	else if ($lightning["bearingx"]<=123.75){$lightning["bearingx"]='ESE';}
	else if ($lightning["bearingx"]<=146.25){$lightning["bearingx"]='SE';}	
	else if ($lightning["bearingx"]<=168.75){$lightning["bearingx"]='SSE';}
	else if ($lightning["bearingx"]<=191.25){$lightning["bearingx"]='South';}	
	else if ($lightning["bearingx"]<=213.75){$lightning["bearingx"]='SSW';}
	else if ($lightning["bearingx"]<=236.25){$lightning["bearingx"]='SW';}	
	else if ($lightning["bearingx"]<=281.25){$lightning["bearingx"]='West';}
	else if ($lightning["bearingx"]<=303.75){$lightning["bearingx"]='WNW';}	
	else if ($lightning["bearingx"]<=326.25){$lightning["bearingx"]='NW';}
	else if ($lightning["bearingx"]<=348.75){$lightning["bearingx"]='NWN';}
	else {$lightning["bearingx"]='North';}
	
	} else { $source = '0';}
        
        if ($wind["units"] == "mph"){$lightning["last_distance"]=$lightning["last_distance"]*0.621371;$lightning["distunit"]="mi";}
        else {$lightning["distunit"]="km";}
?>

<style>

.Strikes {
  position: relative; 
  margin-top: -1.5px; 
  margin-left: 0px;
  z-index: auto;
}
.base {
 position: relative;
  margin-top: -83.5px; 
  margin-left: -200px;
}

</style>

<script src="js/d3.v3.min.js"></script>

<div class="Strikes"></div>

<div class="base">
<svg id="base" width="50" height="100" viewBox="0 0 88.7 90">
<g><path fill="grey" d="M57.1,63.4c9.7,1.8,14.8,6.2,11.5,10.3c-3.6,4.5-16,7-27.6,5.6c-10.7-1.3-17-5.5-15.2-9.6c0,0,0,0,0,0 c-4.1,5.1,3.2,10.4,16.2,12c13,1.6,26.9-1.2,30.9-6.3C76.9,70.3,69.8,65,57.1,63.4z"></path></g>
<g><path fill="grey" d="M54.8,82.5C40.5,83.9,26,80.5,22.3,75c-3.2-4.9,3-9.8,14.1-11.9c-13.7,2-21.7,7.7-18,13.3 c3.9,6,19.6,9.6,35.1,8.1C68,83,77.2,77.6,75,71.9C75.5,76.8,67.3,81.3,54.8,82.5z"></path></g>
<g><path fill="grey" d="M85.1,71.8c0,8.3-17.3,15-38.7,15c-21.4,0-38.7-6.7-38.7-15c0-4.3,4.7-8.2,12.1-10.9 c-9.4,3-15.5,7.6-15.5,12.7C4.3,82.7,23.2,90,46.5,90s42.2-7.3,42.2-16.4c0-5.3-6.6-10.1-16.8-13.1C80,63.3,85.1,67.3,85.1,71.8 z"></path></g>
</svg>
</div>
<script>

	var Strikes_last_hour = "<?php echo $lightning["hour_strike_count"];?>";
	
	var month = "<?php echo date('F Y');?>";
	
	var year = "<?php echo date('Y');?>";
		
	var Strikes_this_month = "<?php echo $lightning["month_strike_count"];?>";
	
	var Strikes_this_year = "<?php echo $lightning["year_strike_count"];?>";
	
	var Alltime_strikes = "<?php echo $lightning["alltime_strike_count"];?>";
	
	var Last_detected = "<?php echo date('jS M H:i',$lightning['last_time']);?>";
	
	var Last_distance = "<?php echo number_format($lightning["last_distance"],1);?>";
	
	var Bearing = "<?php echo $lightning["bearing"];?>";
	
	var Bearingx = "<?php echo $lightning["bearingx"];?>";  
	
	var theme = "<?php echo $theme;?>";
	
	var source = "<?php echo $source;?>";

        var distunit = "<?php echo $lightning["distunit"];?>";

  var svg = d3.select(".Strikes")
    			.append("svg")
    			//.style("background", "#292E35")
    			.attr("width", 300)
    			.attr("height", 150);

     svg.append("line") // upright post
    			.attr("x1", 52)
    			.attr("x2", 52)
    			.attr("y1", 75)
    			.attr("y2", 136)
    			.style("stroke", "#2e8b57")
    			.style("stroke-width", "3px")
    			.style("stroke-linecap", "round");
    
    	   	
   	if (theme == 'dark') {
   	
   	svg.append('polyline') // Lightning bolt
     			.attr("cx", 40)
            	.attr("cy", 0)
                .attr('points', "52 0 54 6 59 9 56 15 49 23 55 28 38 37 45 44 49 52 41 55 52 70 52 73")
                .transition()
  				.duration(250)
                .attr('fill', "none")
                .attr('stroke', '#6CA6CD');
                                
	svg.append('polyline') // Lightning bolt
     			.attr("cx", 40)
            	.attr("cy", 0)
                .attr('points', "54 18 62 27 67 34 58 39 70 45 77 55")
                .transition()
  				.duration(250)
                .attr('fill', "none")
                .attr('stroke', '#6CA6CD');
                                
	svg.append('polyline') // Lightning bolt
     			.attr("cx", 40)
            	.attr("cy", 0)
                .attr('points', "41 40 35 45 32 55 24 62")
                .transition()
  				.duration(250)
                .attr('fill', "none")
                .attr('stroke', '#6CA6CD');
   	
   	svg.append("text") // Recorded Strikes
             	.attr("x", 150)
            	.attr("y", 22.5)
            	.style("fill", "silver")
            	.style("font-family", "Helvetica")
            	.style("font-size", "12px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "normal")
   				.text("Recorded Strikes");
   	   				
   	svg.append("text") // Last 1 hour
             	.attr("x", 130)
            	.attr("y", 45)
            	.style("fill", "silver")
            	.style("font-family", "Helvetica")
            	.style("font-size", "10px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Last Hour");
   				
   	svg.append("text") // Month
             	.attr("x", 130)
            	.attr("y", 60)
            	.style("fill", "silver")
            	.style("font-family", "Helvetica")
            	.style("font-size", "10px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Total"+" "+month);			
   				
   	svg.append("text") // Year
             	.attr("x", 130)
            	.attr("y", 75)
            	.style("fill", "silver")
            	.style("font-family", "Helvetica")
            	.style("font-size", "10px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
				.text("Total"+" "+year);
				
	svg.append("text") // Alltime
             	.attr("x", 130)
            	.attr("y", 90)
            	.style("fill", "silver")
            	.style("font-family", "Helvetica")
            	.style("font-size", "10px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
				.text("All-time Strike Total");
				
	svg.append("text") // Last detected strike time
             	.attr("x", 130)
            	.attr("y", 105)
            	.style("fill", "silver")
            	.style("font-family", "Helvetica")
            	.style("font-size", "10px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
				.text("Last Detected");
								
	svg.append("text") // Last Distance
             	.attr("x", 130)
            	.attr("y", 120)
            	.style("fill", "silver")
            	.style("font-family", "Helvetica")
            	.style("font-size", "10px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
				.text("Distance");
	
	if (source == 'Boltek') {
										
	svg.append("text") // Last Bearing
             	.attr("x", 130)
            	.attr("y", 135)
            	.style("fill", "silver")
            	.style("font-family", "Helvetica")
            	.style("font-size", "10px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
				.text("Bearing");
				
		} else {}						
		
	} else {
	
	svg.append('polyline') // Lightning bolt
     			.attr("cx", 40)
            	.attr("cy", 0)
                .attr('points', "52 0 54 6 59 9 56 15 49 23 55 28 38 37 45 44 49 52 41 55 52 70 52 73")
                .transition()
  				.duration(250)
                .attr('fill', "none")
                .attr('stroke', '#6CA6CD');
                
	svg.append('polyline') // Lightning bolt
     			.attr("cx", 40)
            	.attr("cy", 0)
                .attr('points', "54 18 62 27 67 34 58 39 70 45 77 55")
                .transition()
  				.duration(250)
                .attr('fill', "none")
                .attr('stroke', '#6CA6CD');
                                
	svg.append('polyline') // Lightning bolt
     			.attr("cx", 40)
            	.attr("cy", 0)
                .attr('points', "41 40 35 45 32 55 24 62")
                .transition()
  				.duration(250)
                .attr('fill', "none")
                .attr('stroke', '#6CA6CD');
	
	svg.append("text") // Recorded Strikes
             	.attr("x", 150)
            	.attr("y", 22.5)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "12px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "normal")
   				.text("Recorded Strikes");
		
	svg.append("text") // Last 1 hour1
             	.attr("x", 130)
            	.attr("y", 45)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "10px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Last Hour");
   				
   	svg.append("text") // Month
             	.attr("x", 130)
            	.attr("y", 60)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "10px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Total"+" "+month);			
   				
   	svg.append("text") // Year
             	.attr("x", 130)
            	.attr("y", 75)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "10px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
				.text("Total"+" "+year);
				
	svg.append("text") // Alltime
             	.attr("x", 130)
            	.attr("y", 90)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "10px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
				.text("All-time Strike Total");
				
	svg.append("text") // Last detected strike time
             	.attr("x", 130)
            	.attr("y", 105)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "10px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
				.text("Last Detected");
								
	svg.append("text") // Last Distance
             	.attr("x", 130)
            	.attr("y", 120)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "10px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
				.text("Distance");
				
	if (source == 'Boltek') {
										
	svg.append("text") // Last Bearing
             	.attr("x", 130)
            	.attr("y", 135)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "10px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
				.text("Bearing");
				
		} else {}	
	}
	
	// Begin color Text output			
						
   	svg.append("text") // Last 1 hour
             	.attr("x", 191)
            	.attr("y", 45)
            	.style("fill", "#ff964f")
            	.style("font-family", "Helvetica")
            	.style("font-size", "10px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text(Strikes_last_hour);				
   				
   	svg.append("text") // Month
             	.attr("x", 220)
            	.attr("y", 60)
            	.style("fill", "#ff964f")
            	.style("font-family", "Helvetica")
            	.style("font-size", "10px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text(Strikes_this_month);
   								
	svg.append("text") // Year
             	.attr("x", 182)
            	.attr("y", 75)
            	.style("fill", "#ff964f")
            	.style("font-family", "Helvetica")
            	.style("font-size", "10px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
				.text(Strikes_this_year);
								
	svg.append("text") // Alltime
             	.attr("x", 220)
            	.attr("y", 90)
            	.style("fill", "#ff964f")
            	.style("font-family", "Helvetica")
            	.style("font-size", "10px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
				.text(Alltime_strikes);
								
	svg.append("text") // Last detected strike time
             	.attr("x", 197)
            	.attr("y", 105)
            	.style("fill", "#ff964f")
            	.style("font-family", "Helvetica")
            	.style("font-size", "10px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
				.text(Last_detected);
								
	svg.append("text") // Last Distance
             	.attr("x", 175)
            	.attr("y", 120)
            	.style("fill", "#ff964f")
            	.style("font-family", "Helvetica")
            	.style("font-size", "10px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
				.text(Last_distance+" "+(distunit));
	
	if (source == "Boltek") {
								
	svg.append("text") // Last Bearing
             	.attr("x", 169)
            	.attr("y", 135)
            	.style("fill", "#2e8b57")
            	.style("font-family", "Helvetica")
            	.style("font-size", "10px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
				.text(Bearing+"°"+" "+Bearingx);
	} else {}
                
</script>
</html>
