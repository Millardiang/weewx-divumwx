<?php
include('dvmCombinedData.php');
date_default_timezone_set($TZ);
?>

<?php

//check for any strikes to set last_time correctly

if (empty ($lightning["alltime_strike_count"])) {$lightning['last_time'] =  "None";} else {$lightning['last_time'] =  date('jS M H:i',$lightning['last_time']);}

if ($lightning["source"] == "Boltek") {

$lightninglivedata = 'jsondata/NSDRealtime.txt';
$file_live = file_get_contents($lightninglivedata);
$lightningBolt = explode( ',',$file_live);

if (!empty($lightningBolt)) {
$lightning["unixtimestamp"]           = $lightningBolt[0]; // unix timestamp
$lightning["rate_per_min"]            = $lightningBolt[1]; // current rate/min
$lightning["close_rate_per_min"]      = $lightningBolt[2]; // current close rate/min (< 50km)
$lightning['last_time']      					= $lightningBolt[3]; // last strike date and time
$lightning["bearing"] 								= $lightningBolt[4]; // Bearing number
$lightning["bearingx"] 								= $lightningBolt[4]; // Bearing ordinals 
$lightning["last_distance"]  					= $lightningBolt[5]; // last strike distance 
$lightning["last_strike_type"]      	= $lightningBolt[6]; // last strike type ( CC+, CC-, CG+, CG- )
$lightning["hour_strike_count"]     	= $lightningBolt[7]; // strikes last hour
$lightning["today_strike_count"]  		= $lightningBolt[8]; // strikes today
$lightning["month_strike_count"]   		= $lightningBolt[9]; // strikes this month
$lightning["year_strike_count"]    		= $lightningBolt[10]; // strikes this year
$lightning["max_rate_per_min"]				= $lightningBolt[11]; // max rate/min
$lightning["max_ratetime"]						= $lightningBolt[12]; // max ratetime (hh:mm)
$lightning["max_burst"]								= $lightningBolt[13]; // max burst/s
$lightning["max_burst_time"]					= $lightningBolt[14]; // max bursttime (hh:mm)
$lightning["CG+_strikes"]							= $lightningBolt[15]; // CG+ strikes today
$lightning["CG-_strikes"]							= $lightningBolt[16]; // CG- strikes today
$lightning["CC+_strikes"]							= $lightningBolt[17]; // CC+ strikes today
$lightning["CC-_strikes"]							= $lightningBolt[18]; // CC- strikes today
$lightning["uptime"]									= $lightningBolt[19]; // uptime (x days x hours x mins)
$lightning["unitsx"]									= $lightningBolt[20]; // km or miles (set in config.ini)
$lightning["persistence"]							= $lightningBolt[21]; // Persistence in minutes set 60 mins
$lightning["nsdcrop"]									= $lightningBolt[22]; // Max strikes in NSDStrikes file (set to 2000)
}

		// Bearing
		if ($lightning["bearingx"]<=11.25){
			$lightning["bearingx"]='North';
		} else if ($lightning["bearingx"]<=33.75){
			$lightning["bearingx"]='NNE';
		} else if ($lightning["bearingx"]<=56.25){
			$lightning["bearingx"]='NE';
		} else if ($lightning["bearingx"]<=78.75){
			$lightning["bearingx"]='ENE';
		} else if ($lightning["bearingx"]<=101.25){
			$lightning["bearingx"]='East';
		} else if ($lightning["bearingx"]<=123.75){
			$lightning["bearingx"]='ESE';
		} else if ($lightning["bearingx"]<= 146.25){
			$lightning["bearingx"] = 'SE';
		} else if ($lightning["bearingx"]<=168.75){
			$lightning["bearingx"]='SSE';
		} else if ($lightning["bearingx"]<=191.25){
			$lightning["bearingx"]='South';
		} else if ($lightning["bearingx"]<=213.75){
			$lightning["bearingx"]='SSW';
		} else if ($lightning["bearingx"]<=236.25){
			$lightning["bearingx"]='SW';
		} else if ($lightning["bearingx"]<=281.25){
			$lightning["bearingx"]='West';
		} else if ($lightning["bearingx"]<=303.75){
			$lightning["bearingx"]='WNW';
		} else if ($lightning["bearingx"]<=326.25){
			$lightning["bearingx"]='NW';
		} else if ($lightning["bearingx"]<=348.75){
			$lightning["bearingx"]='NWN';
		} else {$lightning["bearingx"]='North';}
	} 

	if ($wind["units"] == "mph"){$lightning["last_distance"] = $lightning["last_distance"] * 0.621371; $lightning["distunit"] = "mi";} else {$lightning["distunit"] = "km";}
?>

<style>

.StrikesTop {
  position: relative; 
  margin-top: 20px; 
  margin-left: 0px;
}
.baseTop {
 position: relative;
  margin-top: -47.5px; 
  margin-left: -177px;
}

</style>

<script src="js/d3.min.js"></script>

<script>
	if (theme == 'dark') {
var textFill = "silver";}
else
{var textFill = "rgba(85,85,85,1)";}
</script>


<div class="title"><?php echo $info;?><?php echo $lang['lightningTop'];?></div>      
<div class="StrikesTop"></div>

<div class="baseTop">
<svg id="base" width="25" height="50" viewBox="0 0 88.7 90">
<g><path fill="grey" d="M57.1,63.4c9.7,1.8,14.8,6.2,11.5,10.3c-3.6,4.5-16,7-27.6,5.6c-10.7-1.3-17-5.5-15.2-9.6c0,0,0,0,0,0 c-4.1,5.1,3.2,10.4,16.2,12c13,1.6,26.9-1.2,30.9-6.3C76.9,70.3,69.8,65,57.1,63.4z"></path></g>
<g><path fill="grey" d="M54.8,82.5C40.5,83.9,26,80.5,22.3,75c-3.2-4.9,3-9.8,14.1-11.9c-13.7,2-21.7,7.7-18,13.3 c3.9,6,19.6,9.6,35.1,8.1C68,83,77.2,77.6,75,71.9C75.5,76.8,67.3,81.3,54.8,82.5z"></path></g>
<g><path fill="grey" d="M85.1,71.8c0,8.3-17.3,15-38.7,15c-21.4,0-38.7-6.7-38.7-15c0-4.3,4.7-8.2,12.1-10.9 c-9.4,3-15.5,7.6-15.5,12.7C4.3,82.7,23.2,90,46.5,90s42.2-7.3,42.2-16.4c0-5.3-6.6-10.1-16.8-13.1C80,63.3,85.1,67.3,85.1,71.8 z"></path></g>
</svg>
</div>

<script>

	var theme = "<?php echo $theme;?>";
	var source = "<?php echo $lightning["source"];?>";

	var month = "<?php echo date('F Y');?>";	
	var year = "<?php echo date('Y');?>";

	var Strikes_last_one_hour = "<?php echo $lightning["hour_strike_count"];?>";	
	var Strikes_this_month = "<?php echo $lightning["month_strike_count"];?>";
	var Strikes_this_year = "<?php echo $lightning["year_strike_count"];?>";
        var Last_detected = "<?php echo ($lightning['last_time']);?>";
	var Last_distance = "<?php echo number_format($lightning["last_distance"],1);?>";
	var Bearing = "<?php echo $lightning["bearing"];?>";
	var Bearingx = "<?php echo $lightning["bearingx"];?>";
	var unit = "<?php echo $lightning["distunit"];?>";

	var svg = d3.select(".StrikesTop")
    					.append("svg")
    					//.style("background", "#292E35")
    					.attr("width", 230)
    					.attr("height", 60);
			
    svg.append("circle")
            	.attr("cx", 73) // main circle
            	.attr("cy", 37.5)
            	.attr("r", 16)
            	.style('stroke', "#999999")
            	.style("stroke-width", "1px")
            	.style('fill', "none");
            	
    svg.append("line") // upright post
    					.attr("x1", 27)
    					.attr("x2", 27)
    					.attr("y1", 30)
    					.attr("y2", 55)
    					.style("stroke", "#2e8b57")
    					.style("stroke-width", "2px")
    					.style("stroke-linecap", "round");
    			
    svg.append('polyline') // Lightning bolt
     					.attr("cx", 40)
            	.attr("cy", 0)
              .attr('points', "27 0 31 5 26 10 29 15 32 20 26 25 27 28")
              .transition()
  						.duration(250)  				
              .style('fill', "none")
              .style("stroke-width", "0.5px")
              .style('stroke', '#6CA6CD');                  
    			
    
    if (source == 'Boltek') {
    
    svg.append("text") // Last hour
             	.attr("x", 73)
            	.attr("y", 15)
            	.style("fill", textFill)
            	.style("font-family", "Helvetica")
            	.style("font-size", "9px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "normal")
   				.text("Last Hour");
       				
   	svg.append("text") // Last detected strike time
             	.attr("x", 110)
            	.attr("y", 10)
            	.style("fill", textFill)
            	.style("font-family", "Helvetica")
            	.style("font-size", "9px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
				.text("Last Strike");

  var data = ["Total "+month+" "+"-"+Strikes_this_month];

	var text = svg.selectAll(null)
  						.data(data)
  						.enter() 
  						.append("text")
  						.attr("x", 110)
  						.attr("y", function(d, i) {
    					return 22 + i * 22
  						})
  						.style("fill", textFill)
  						.style("font-family", "Helvetica")
  						.style("font-size", "9px")
  						.style("text-anchor", "left")
  						.style("font-weight", "normal")
  						.text(function(d) {
    					return d.split("-")[0]
  						})
  						.append("tspan")
  						.style("fill", "#ff964f")
  						.text(function(d) {
    					return d.split("-")[1]
  						})
   				
   	svg.append("text") // Year
             	.attr("x", 110)
            	.attr("y", 34)
            	.style("fill", textFill)
            	.style("font-family", "Helvetica")
            	.style("font-size", "9px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
				.text("Total"+" "+year);
				
	svg.append("text") // Last Distance
             	.attr("x", 110)
            	.attr("y", 46)
            	.style("fill", textFill)
            	.style("font-family", "Helvetica")
            	.style("font-size", "9px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
				.text("Distance @");
					
	svg.append("text") // Last Bearing
             	.attr("x", 110)
            	.attr("y", 58)
            	.style("fill", textFill)
            	.style("font-family", "Helvetica")
            	.style("font-size", "9px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
				.text("Bearing");
				
	} else {
	
	svg.append("text") // Last 3 hours
             	.attr("x", 73)
            	.attr("y", 15)
            	.style("fill", textFill)
            	.style("font-family", "Helvetica")
            	.style("font-size", "9px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "normal")
   				.text("Last 1 Hour");
       				
   	svg.append("text") // Last detected strike time
             	.attr("x", 110)
            	.attr("y", 16)
            	.style("fill", textFill)
            	.style("font-family", "Helvetica")
            	.style("font-size", "9px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
				.text("Last Strike");

  var data = ["Total "+month+" "+"-"+Strikes_this_month];

	var text = svg.selectAll(null)
  						.data(data)
  						.enter() 
  						.append("text")
  						.attr("x", 110)
  						.attr("y", function(d, i) {
    					return 28 + i * 28
  						})
  						.style("fill", textFill)
  						.style("font-family", "Helvetica")
  						.style("font-size", "9px")
  						.style("text-anchor", "left")
  						.style("font-weight", "normal")
  						.text(function(d) {
    					return d.split("-")[0]
  						})
  						.append("tspan")
  						.style("fill", "#ff964f")
  						.text(function(d) {
    					return d.split("-")[1]
  						})
   				
   	svg.append("text") // Year
             	.attr("x", 110)
            	.attr("y", 40)
            	.style("fill", textFill)
            	.style("font-family", "Helvetica")
            	.style("font-size", "9px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
				.text("Total"+" "+year);
				
	svg.append("text") // Last Distance
             	.attr("x", 110)
            	.attr("y", 52)
            	.style("fill", textFill)
            	.style("font-family", "Helvetica")
            	.style("font-size", "9px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
				.text("Distance @");
				
		}
        

    
    if (source == 'Boltek') {
    
     svg.append("text") // Last hour
             	.attr("x", 73)
            	.attr("y", 43)
            	.style("fill", "#ff964f")
            	.style("font-family", "Helvetica")
            	.style("font-size", "15px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "normal")
   				.text(Strikes_last_one_hour);
    
    svg.append("text") // Last detected strike time
             	.attr("x", 157)
            	.attr("y", 10)
            	.style("fill", "#ff964f")
            	.style("font-family", "Helvetica")
            	.style("font-size", "9px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
				.text(Last_detected);
				
   	svg.append("text") // Year
             	.attr("x", 155)
            	.attr("y", 34)
            	.style("fill", "#ff964f")
            	.style("font-family", "Helvetica")
            	.style("font-size", "9px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
				.text(Strikes_this_year);
				
	svg.append("text") // Last Distance
             	.attr("x", 161)
            	.attr("y", 46)
            	.style("fill", "#ff964f")
            	.style("font-family", "Helvetica")
            	.style("font-size", "9px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
				.text(Last_distance+" "+unit);
												
	svg.append("text") // Last Bearing
             	.attr("x", 146)
            	.attr("y", 58)
            	.style("fill", "#2e8b57")
            	.style("font-family", "Helvetica")
            	.style("font-size", "9px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
				.text(Bearing+"°"+" "+Bearingx);
				
	} else {
	
	svg.append("text") // Last hour
             	.attr("x", 73)
            	.attr("y", 43)
            	.style("fill", "#ff964f")
            	.style("font-family", "Helvetica")
            	.style("font-size", "15px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "normal")
   				.text(Strikes_last_one_hour);
    
    svg.append("text") // Last detected strike time
             	.attr("x", 157)
            	.attr("y", 16)
            	.style("fill", "#ff964f")
            	.style("font-family", "Helvetica")
            	.style("font-size", "9px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
				.text(Last_detected);
								
   	svg.append("text") // Year
             	.attr("x", 155)
            	.attr("y", 40)
            	.style("fill", "#ff964f")
            	.style("font-family", "Helvetica")
            	.style("font-size", "9px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
				.text(Strikes_this_year);
				
	svg.append("text") // Last Distance
             	.attr("x", 161)
            	.attr("y", 52)
            	.style("fill", "#ff964f")
            	.style("font-family", "Helvetica")
            	.style("font-size", "9px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
				.text(Last_distance+" "+unit);
	
	}
</script>
