<?php
##############################################################################################
#        ________   __  ___      ___  ____  ____  ___      ___    __   __  ___  ___  ___     #
#       |"      "\ |" \|"  \    /"  |("  _||_ " ||"  \    /"  |  |"  |/  \|  "||"  \/"  |    #
#       (.  ___  :)||  |\   \  //  / |   (  ) : | \   \  //   |  |'  /    \:  | \   \  /     #
#       |: \   ) |||:  | \\  \/. ./  (:  |  | . ) /\\  \/.    |  |: /'        |  \\  \/      #
#       (| (___\ |||.  |  \.    //    \\ \__/ // |: \.        |   \//  /\'    |  /\.  \      #
#       |:       :)/\  |\  \\   /     /\\ __ //\ |.  \    /:  |   /   /  \\   | /  \   \     #
#       (________/(__\_|_)  \__/     (__________)|___|\__/|___|  |___/    \___||___/\___|    #
#                                                                                            #
#     Copyright (C) 2023 Ian Millard, Steven Sheeley, Sean Balfour. All rights reserved      #
#      Distributed under terms of the GPLv3.  See the file LICENSE.txt for your rights.      #
#    Issues for weewx-divumwx skin template are only addressed via the issues register at    #
#                    https://github.com/Millardiang/weewx-divumwx/issues                     #
############################################################################################## 
include('dvmCombinedData.php');
date_default_timezone_set($TZ);
?>

<!DOCTYPE html>
<html lang="en">

<div class="chartforecast">
<span class="yearpopup"><a alt="aquinfo" title="Lightning Almanac" href="dvmLightningRecords.php" data-lity><?php echo $menucharticonpage;?> Lightning Records | Radar | Maps</a></span>
</div>
<span class='moduletitle'>Recorded&nbsp;<?php echo $lang['lightningModule'];?>&nbsp;Strikes<?php if (filesize('jsondata/NSDRealtime.txt') < 100) { echo "&nbsp;" , $offline;} else echo "";?></span>

<div class="updatedtime1"><span><?php if(file_exists($livedata)&&time() - filemtime($livedata)>300) echo $offline. '<offline> Offline </offline>'; else echo $online." ".$divum["time"];?></div>

<?php

if ($lightningSource == 0) {

$lightninglivedata = 'jsondata/NSDRealtime.txt';
$file_live = file_get_contents($lightninglivedata);
$lightningBolt = explode( ',',$file_live);

if (empty($lightningBolt[0])) {
  $lightningBolt[0] = 0;
}
if (empty($lightningBolt[1])) {
  $lightningBolt[1] = 0;
}
if (empty($lightningBolt[2])) {
  $lightningBolt[2] = 0;
}
if (empty($lightningBolt[3])) {
  $lightningBolt[3] = 0;
}
if (empty($lightningBolt[4])) {
  $lightningBolt[4] = 0;
}
if (empty($lightningBolt[5])) {
  $lightningBolt[5] = 0;
}
if (empty($lightningBolt[6])) {
  $lightningBolt[6] = 0;
}
if (empty($lightningBolt[7])) {
  $lightningBolt[7] = 0;
}
if (empty($lightningBolt[8])) {
  $lightningBolt[8] = 0;
}
if (empty($lightningBolt[9])) {
  $lightningBolt[9] = 0;
}
if (empty($lightningBolt[10])) {
  $lightningBolt[10] = 0;
}
if (empty($lightningBolt[11])) {
  $lightningBolt[11] = 0;
}
if (empty($lightningBolt[12])) {
  $lightningBolt[12] = 0;
}
if (empty($lightningBolt[13])) {
  $lightningBolt[13] = 0;
}
if (empty($lightningBolt[14])) {
  $lightningBolt[14] = 0;
}
if (empty($lightningBolt[15])) {
  $lightningBolt[15] = 0;
}
if (empty($lightningBolt[16])) {
  $lightningBolt[16] = 0;
}
if (empty($lightningBolt[17])) {
  $lightningBolt[17] = 0;
}
if (empty($lightningBolt[18])) {
  $lightningBolt[18] = 0;
}
if (empty($lightningBolt[19])) {
  $lightningBolt[19] = 0;
}
if (empty($lightningBolt[20])) {
  $lightningBolt[20] = 0;
}
if (empty($lightningBolt[21])) {
  $lightningBolt[21] = 0;
}
if (empty($lightningBolt[22])) {
  $lightningBolt[22] = 0;
}

$lightning["unixtimestamp"]           	= $lightningBolt[0]; // unix timestamp
$lightning["rate_per_min"]            	= $lightningBolt[1]; // current rate/min
$lightning["close_rate_per_min"]      	= $lightningBolt[2]; // current close rate/min (< 50km)
$lightning['last_time']      						= $lightningBolt[3]; // last strike date and time
$lightning["bearing"] 									= $lightningBolt[4]; // Bearing number
$lightning["bearingx"] 									= $lightningBolt[4]; // Bearing ordinals 
$lightning["last_distance"]  						= $lightningBolt[5]; // last strike distance 
$lightning["last_strike_type"]      		= $lightningBolt[6]; // last strike type ( CC+, CC-, CG+, CG- )
$lightning["hour_strike_count"]     		= $lightningBolt[7]; // strikes last hour
$lightning["today_strike_count"]  			= $lightningBolt[8]; // strikes today
$lightning["month_strike_count"]   			= $lightningBolt[9]; // strikes this month
$lightning["year_strike_count"]    			= $lightningBolt[10]; // strikes this year
$lightning["max_rate_per_min"]					= $lightningBolt[11]; // max rate/min
$lightning["max_ratetime"]							= $lightningBolt[12]; // max ratetime (hh:mm)
$lightning["max_burst"]									= $lightningBolt[13]; // max burst/s
$lightning["max_burst_time"]						= $lightningBolt[14]; // max bursttime (hh:mm)
$lightning["CG+_strikes"]								= $lightningBolt[15]; // CG+ strikes today
$lightning["CG-_strikes"]								= $lightningBolt[16]; // CG- strikes today
$lightning["CC+_strikes"]								= $lightningBolt[17]; // CC+ strikes today
$lightning["CC-_strikes"]								= $lightningBolt[18]; // CC- strikes today
$lightning["uptime"]										= $lightningBolt[19]; // uptime (x days x hours x mins)
$lightning["unitsx"]										= $lightningBolt[20]; // km or miles (set in config.ini)
$lightning["persistence"]								= $lightningBolt[21]; // Persistence in minutes set 60 mins
$lightning["nsdcrop"]										= $lightningBolt[22]; // Max strikes in NSDStrikes file (set to 2000)


		// Bearing
		if ($lightning["bearingx"]<=11.25){
			$lightning["bearingx"]='North';
		}else if ($lightning["bearingx"] <= 33.75){
			$lightning["bearingx"]='NNE';
		}else if ($lightning["bearingx"] <= 56.25){
			$lightning["bearingx"]='NE';
		}else if ($lightning["bearingx"] <= 78.75){
			$lightning["bearingx"]='ENE';
		}else if ($lightning["bearingx"] <= 101.25){
			$lightning["bearingx"]='East';
		}else if ($lightning["bearingx"] <= 123.75){
			$lightning["bearingx"]='ESE';
		}else if ($lightning["bearingx"] <= 146.25){
			$lightning["bearingx"] = 'SE';
		}else if ($lightning["bearingx"] <= 168.75){
			$lightning["bearingx"]='SSE';
		}else if ($lightning["bearingx"] <= 191.25){
			$lightning["bearingx"]='South';
		}else if ($lightning["bearingx"] <= 213.75){
			$lightning["bearingx"]='SSW';
		}else if ($lightning["bearingx"] <= 236.25){
			$lightning["bearingx"]='SW';
		} else if ($lightning["bearingx"] <= 261.25){
			$lightning["bearingx"]='WSW';
		}else if ($lightning["bearingx"] <= 281.25){
			$lightning["bearingx"]='West';
		}else if ($lightning["bearingx"] <= 303.75){
			$lightning["bearingx"]='WNW';
		}else if ($lightning["bearingx"] <= 326.25){
			$lightning["bearingx"]='NW';
		}else if ($lightning["bearingx"] <= 348.75){
			$lightning["bearingx"]='NNW';
		}else {$lightning["bearingx"]='North';}
	} 
if ($wind["units"] == "mph"){$lightning["last_distance"] = $lightning["last_distance"] * 0.621371; $lightning["distunit"] = "miles";} else {$lightning["distunit"] = "km";}?>

<script src="js/d3.7.9.0.min.js"></script>
<style>
table.lightning {
  border-spacing: 0.25em;
  color: var(--col-6);
  text-align: center;
}
table.lightning td, table.lightning th {
  padding: 1px 2px;
  border: 1px solid var(--col-13);
  border-radius:2px;
  border-left: 5px solid #7DF9FF;
}
table.lightning tbody td {
  font-size: 10px;
}
</style>
<div class="lightning-grid" style="display:grid;grid-template-columns: auto auto;">
<div class="image-container">
<div class="Strikes"></div>
<div class="base">
<svg id="base" width="50" height="100" viewBox="0 0 88.7 90">
<g><path fill="grey" d="M57.1,63.4c9.7,1.8,14.8,6.2,11.5,10.3c-3.6,4.5-16,7-27.6,5.6c-10.7-1.3-17-5.5-15.2-9.6c0,0,0,0,0,0 c-4.1,5.1,3.2,10.4,16.2,12c13,1.6,26.9-1.2,30.9-6.3C76.9,70.3,69.8,65,57.1,63.4z"></path></g>
<g><path fill="grey" d="M54.8,82.5C40.5,83.9,26,80.5,22.3,75c-3.2-4.9,3-9.8,14.1-11.9c-13.7,2-21.7,7.7-18,13.3 c3.9,6,19.6,9.6,35.1,8.1C68,83,77.2,77.6,75,71.9C75.5,76.8,67.3,81.3,54.8,82.5z"></path></g>
<g><path fill="grey" d="M85.1,71.8c0,8.3-17.3,15-38.7,15c-21.4,0-38.7-6.7-38.7-15c0-4.3,4.7-8.2,12.1-10.9 c-9.4,3-15.5,7.6-15.5,12.7C4.3,82.7,23.2,90,46.5,90s42.2-7.3,42.2-16.4c0-5.3-6.6-10.1-16.8-13.1C80,63.3,85.1,67.3,85.1,71.8 z"></path></g>
</svg>
</div>
</div>
<div class="table-container">
<table class="lightning" style="margin-left:-180px;margin-top:-1px;">
<tbody>
<tr>
<td style="border:transparent;">Last Strike</td>
<td style="border:transparent;">Distance</td>
</tr>
<tr>
<td><?php echo date('jS M H:i',$lightning["last_time"]);?></td>
<td><?php echo number_format($lightning["last_distance"],1);?></td>
</tr>
<tr>
<td style="border:transparent;">Last Hour</td>
<td style="border:transparent;">Month</td>
</tr>
<tr>
<td><?php echo $lightning["hour_strike_count"];?></td>
<td><?php echo $lightning["month_strike_count"];?></td>
</tr>
<tr>
<td style="border:transparent;">Year</td>
<td style="border:transparent;">Alltime</td>
</tr>
<tr>
<td><?php echo $lightning["year_strike_count"];?></td>
<td><?php echo $lightning["alltime_strike_count"];?></td>
</tr>
<?php
if ($lightningSource == 0)
{echo '<tr>
<td style="border:transparent;">Bearing</td>
<td><?php echo $lightning["bearingx"];?>&nbsp;<?php echo $lightning["bearing"];?></td>
</tr>';}
 ?>
</tbody>
</table>
</div>
</div>

<script>
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

		svg.append('polyline') // Lightning bolt
					.attr("cx", 40)
					.attr("cy", 0)
					.attr('points', "52 0 54 6 59 9 56 15 49 23 55 28 38 37 45 44 49 52 41 55 52 70 52 73")
					.transition()
					.duration(250)
					.attr('fill', "none")
					.attr('stroke', '#7DF9FF');

		svg.append('polyline') // Lightning bolt
					.attr("cx", 40)
					.attr("cy", 0)
					.attr('points', "54 18 62 27 67 34 58 39 70 45 77 55")
					.transition()
					.duration(250)
					.attr('fill', "none")
					.attr('stroke', '#7DF9FF');

		svg.append('polyline') // Lightning bolt
					.attr("cx", 40)
					.attr("cy", 0)
					.attr('points', "41 40 35 45 32 55 24 62")
					.transition()
					.duration(250)
					.attr('fill', "none")
					.attr('stroke', '#7DF9FF');


</script>
</html>
