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
header('Content-type: text/html; charset = utf-8');
date_default_timezone_set($TZ);

 //$lightninglivedata = 'jsondata/nsd.txt';
 $lightninglivedata = 'https://skynetweather.com/html/divumwx/jsondata/nsd.txt';
 $file_live = file_get_contents($lightninglivedata);
 $lightning = explode( ',',$file_live);

 $lightning["unixtimestamp"]       = $lightning[0]; // unix timestamp
 $lightning["ratepermin"]          = $lightning[1]; // current rate/min
 $lightning["closeratepermin"]     = $lightning[2]; // current close rate/min (< 50km)
 $lightning['last_time']      = $lightning[3]; // last strike date and time 
 $lightning["bearing"]      = $lightning[4]; // bearing as a number 
 $lightning["bearingx"]     = $lightning[4]; // bearing compass text
 $lightning["last_distance"]  = $lightning[5]; // last strike distance 
 $lightning["laststriketype"]      = $lightning[6]; // last strike type (cc+,cc-,cg+,cg-)
 $lightning["hour_strike_count"]     = $lightning[7]; // strikes last hour
 $lightning["today_strike_count"]        = $lightning[8]; // strikes today
 $lightning["month_strike_count"]        = $lightning[9]; // strikes this month
 $lightning["year_strike_count"]         = $lightning[10]; // strikes this year
 $lightning["alltime_strike_count"]      = $sdata["alltime.lightning_strike_count.sum"];
 /*
 11 max rate/min
 12 max ratetime (hh:mm)
 13 max burst/s
 14 max bursttime (hh:mm)
 15 CG+ strikes today
 16 CG- strikes today
 17 CC+ strikes today
 18 CC- strikes today
 19 NSBackend uptime (x days y hours z min)
 20 Measure km or mi
 21 Persistence in minutes
 */

 $lightning["strikedistanceunit"] = $lightning[20]; // default chosen unit

 //output the units to miles or kilometers
 if ($lightning["strikedistanceunit"] == '1'){$lightning["strikedistanceunit"] = 'mi';}
 if ($lightning["strikedistanceunit"] == '0'){$lightning["strikedistanceunit"] = 'km';}
 
 //direction 
		if ($lightning["bearingx"]<=11.25){
			$lightning["bearingx"]='North';
		}else if ($lightning["bearingx"]<=33.75){
			$lightning["bearingx"]='NNE';
		}else if ($lightning["bearingx"]<=56.25){
			$lightning["bearingx"]='NE';
		}else if ($lightning["bearingx"]<=78.75){
			$lightning["bearingx"]='ENE';
		}else if ($lightning["bearingx"]<=101.25){
			$lightning["bearingx"]='East';
		}else if ($lightning["bearingx"]<=123.75){
			$lightning["bearingx"]='ESE';
		}else if ($lightning["bearingx"] <= 146.25){
			$lightning["bearingx"] = 'SE';
		}else if ($lightning["bearingx"]<=168.75){
			$lightning["bearingx"]='SSE';
		}else if ($lightning["bearingx"]<=191.25){
			$lightning["bearingx"]='South';
		}else if ($lightning["bearingx"]<=213.75){
			$lightning["bearingx"]='SSW';
		}else if ($lightning["bearingx"]<=236.25){
			$lightning["bearingx"]='SW';
		}else if ($lightning["bearingx"]<=281.25){
			$lightning["bearingx"]='West';
		}else if ($lightning["bearingx"]<=303.75){
			$lightning["bearingx"]='WNW';
		}else if ($lightning["bearingx"]<=326.25){
			$lightning["bearingx"]='NW';
		}else if ($lightning["bearingx"]<=348.75){
			$lightning["bearingx"]='NWN';
		}else {$lightning["bearingx"]='North';}

echo $lightning["alltime_strike_count"];
	 
?>
<html>
<style>

.boltek {
  position: relative; 
  margin-top: 0px; 
  margin-left: 0px;
  z-index: auto;
}

</style>

<script src="js/d3.min.js"></script>

<div class="boltek"></div>

<script>

    var distance = "<?php echo $lightning["last_distance"];?>";
    var strikes_last_hour = "<?php echo $lightning["hour_strike_count"];?>";
    var strikes_today = "<?php echo $lightning["today_strike_count"];?>";
    var strikes_month = "<?php echo $lightning["month_strike_count"];?>"; 
    var strikes_year = "<?php echo $lightning["year_strike_count"];?>";  
    var time = "<?php echo date('jS M H:i',$lightning['last_time']);?>";
    var bearingN = "<?php echo $lightning["bearing"];?>";
    var bearingT = "<?php echo $lightning["bearingx"];?>";
    var unit = "<?php echo $lightning["strikedistanceunit"];?>"; 

    var svg = d3.select(".boltek")
                .append("svg")
                .style("background", "#292E35")
                .attr("width", 300)
                .attr("height", 150);

     
    
    svg.append("text") // Recorded Strikes
                .attr("x", 150)
                .attr("y", 20)
                .style("fill", "silver")
                .style("font-family", "Helvetica")
                .style("font-size", "12px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text("Distance "+distance+' '+unit);

    svg.append("text") // Recorded Strikes
                .attr("x", 150)
                .attr("y", 35)
                .style("fill", "silver")
                .style("font-family", "Helvetica")
                .style("font-size", "12px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text("Strikes last hour "+strikes_last_hour);

    svg.append("text") // Recorded Strikes
                .attr("x", 150)
                .attr("y", 50)
                .style("fill", "silver")
                .style("font-family", "Helvetica")
                .style("font-size", "12px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text("Last Strike "+ time);

    svg.append("text") // Recorded Strikes
                .attr("x", 150)
                .attr("y", 65)
                .style("fill", "silver")
                .style("font-family", "Helvetica")
                .style("font-size", "12px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text("Bearing "+bearingN+'° '+bearingT);

    svg.append("text") // Recorded Strikes
                .attr("x", 150)
                .attr("y", 80)
                .style("fill", "silver")
                .style("font-family", "Helvetica")
                .style("font-size", "12px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text("Strikes today "+strikes_today);

    svg.append("text") // Recorded Strikes
                .attr("x", 150)
                .attr("y", 95)
                .style("fill", "silver")
                .style("font-family", "Helvetica")
                .style("font-size", "12px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text("Strikes this month "+strikes_month);

    svg.append("text") // Recorded Strikes
                .attr("x", 150)
                .attr("y", 110)
                .style("fill", "silver")
                .style("font-family", "Helvetica")
                .style("font-size", "12px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text("Strikes this year "+strikes_year);
                   
   
                
</script>
</html>
