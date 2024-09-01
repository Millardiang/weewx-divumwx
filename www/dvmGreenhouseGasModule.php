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
$lang['Greenhouse'] = "Greenhouse Gas";
$json_string  = file_get_contents('jsondata/airquality.txt');
$parsed_json  = json_decode($json_string,true);
$greenhouse["updated_time"] = $greenhouse["updated_time"] = date($timeFormat,filemtime('jsondata/airquality.txt'));
$greenhouse["carbon_monoxide"] = $parsed_json["current"]["carbon_monoxide"];
$greenhouse["nitrogen_dioxide"] = $parsed_json["current"]["nitrogen_dioxide"];
$greenhouse["sulphur_dioxide"] = $parsed_json["current"]["sulphur_dioxide"];
$greenhouse["ozone"] = $parsed_json["current"]["ozone"];
$greenhouse["aerosol_optical_depth"] = $parsed_json["current"]["aerosol_optical_depth"];
$greenhouse["dust"] = $parsed_json["current"]["dust"];
$greenhouse["ammonia"] = $parsed_json["current"]["ammonia"];
?>

<div class="chartforecast2">
<span class="yearpopup"><a alt="airquality charts" title="Airquality Charts" href="dvmhighcharts/dvmAirQualityWeekChart.php" data-lity><?php echo $menucharticonpage;?> Airquality Charts and Information</a></span>
</div>
<span class='moduletitle2'><?php echo $lang['Greenhouse'];?></span>

<div class="updatedtime1"><?php echo $online." ".$greenhouse["updated_time"];?></div>

<script src='js/d3.min.js'></script>    
      
<style>
.Gas {
  position: relative;
  margin-top: -3px; 
  margin-left: -0px;}
</style>

<div class="Gas">
<div class="GreenhouseGas"></div>
</div>
      
<script>

var no2 = "<?php echo $greenhouse["nitrogen_dioxide"];?>";
var so2 = "<?php echo $greenhouse["sulphur_dioxide"];?>";
var co = "<?php echo $greenhouse["carbon_monoxide"];?>";
var o3 = "<?php echo $greenhouse["ozone"];?>";
var aod = "<?php echo $greenhouse["aerosol_optical_depth"];?>";
var nh3 = "<?php echo $greenhouse["ammonia"];?>";

var no2Color = "rgba(255,99,71,1)";
var so2Color = "rgba(97,88,132,1)";
var coColor = "rgba(46,139,87,1)";
var o3Color = "rgba(0,127,255,1)";
var aodColor = "rgba(233,0,118,1)";
var nh3Color = "rgba(207,40,72,1)";
     
var innerColor = "rgb(230, 200, 200)";

var svg = d3.select(".GreenhouseGas")
    .append("svg")
    //.style("background", "#292E35")
    .attr("width", 310)
    .attr("height", 151.5);

// Draw some cool looking greenhouse gas bubbles using color gradients

// NO2 Gas    
var defs = svg.append("defs");

var no2Gradient = defs.append("radialGradient")
  .attr("id", "no2Gradient")
  .attr("cx", "50%")
  .attr("cy", "50%")
  .attr("r", "50%")
  .attr("fx", "50%")
  .attr("fy", "50%");

no2Gradient.append("stop")
  .attr("offset", "0%")
  .style("stop-color", innerColor);

no2Gradient.append("stop")
  .attr("offset", "90%")
  .style("stop-color", no2Color);

svg.append("circle")
  .attr("r", 20)
  .attr("cx", 40)
  .attr("cy", 40)
  .style("fill", "url(#no2Gradient)")
  .style("stroke", no2Color)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 6)
  .attr("cx", 40)
  .attr("cy", 10)
  .style("fill", "url(#no2Gradient)")
  .style("stroke", no2Color)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 10)
  .attr("cx", 54)
  .attr("cy", 60)
  .style("fill", "url(#no2Gradient)")
  .style("stroke", no2Color)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 5.5)
  .attr("cx", 65)
  .attr("cy", 25)
  .style("fill", "url(#no2Gradient)")
  .style("stroke", no2Color)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 2.5)
  .attr("cx", 70)
  .attr("cy", 45)
  .style("fill", "url(#no2Gradient)")
  .style("stroke", no2Color)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 7.5)
  .attr("cx", 17)
  .attr("cy", 48)
  .style("fill", "url(#no2Gradient)")
  .style("stroke", no2Color)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 3)
  .attr("cx", 32)
  .attr("cy", 67)
  .style("fill", "url(#no2Gradient)")
  .style("stroke", no2Color)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 3)
  .attr("cx", 18)
  .attr("cy", 24)
  .style("fill", "url(#no2Gradient)")
  .style("stroke", no2Color)
  .style("stroke-width", "2px");

svg.append("text")
  .text( d3.format(".1f")(no2))
  .attr("x", 40)
  .attr("y", 45.5)
  .attr("text-anchor", "middle")
  .style("font-size", "14px")
  .style("font-family", "Helvetica")
  .style("font-weight", "bold")
  .style("fill", "black");

  svg.append("text")
  .text("NO2")
  .attr("x", 54)
  .attr("y", 63.5)
  .attr("text-anchor", "middle")
  .style("font-size", "8px")
  .style("font-family", "Helvetica")
  .style("font-weight", "bold")
  .style("fill", "black");

// CO Gas

svg.append("line")
  .attr("x1", 168)
  .attr("x2", 158.5)
  .attr("y1", 7)
  .attr("y2", 30)
  .style("stroke", coColor)
  .style("stroke-width", "3px")
  .style("stroke-linecap", "round");

svg.append("line")
  .attr("x1", 115)
  .attr("x2", 135)
  .attr("y1", 47)
  .attr("y2", 44)
  .style("stroke", coColor)
  .style("stroke-width", "3px")
  .style("stroke-linecap", "round");

var coGradient = defs.append("radialGradient")
  .attr("id", "coGradient")
  .attr("cx", "50%")
  .attr("cy", "50%")
  .attr("r", "50%")
  .attr("fx", "50%")
  .attr("fy", "50%");

coGradient.append("stop")
  .attr("offset", "0%")
  .style("stop-color", innerColor);

coGradient.append("stop")
  .attr("offset", "90%")
  .style("stop-color", coColor);

svg.append("circle")
  .attr("r", 20)
  .attr("cx", 155)
  .attr("cy", 40)
  .style("fill", "url(#coGradient)")
  .style("stroke", coColor)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 6)
  .attr("cx", 168)
  .attr("cy", 7)
  .style("fill", "url(#coGradient)")
  .style("stroke", coColor)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 7)
  .attr("cx", 108)
  .attr("cy", 48)
  .style("fill", "url(#coGradient)")
  .style("stroke", coColor)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 10)
  .attr("cx", 168)
  .attr("cy", 60)
  .style("fill", "url(#coGradient)")
  .style("stroke", coColor)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 3.5)
  .attr("cx", 140)
  .attr("cy", 15)
  .style("fill", "url(#coGradient)")
  .style("stroke", coColor)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 2.5)
  .attr("cx", 180)
  .attr("cy", 30)
  .style("fill", "url(#coGradient)")
  .style("stroke", coColor)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 2.5)
  .attr("cx", 138)
  .attr("cy", 63)
  .style("fill", "url(#coGradient)")
  .style("stroke", coColor)
  .style("stroke-width", "2px");

svg.append("text")
  .text(co)
  .attr("x", 155)
  .attr("y", 45.5)
  .attr("text-anchor", "middle")
  .style("font-size", "14px")
  .style("font-family", "Helvetica")
  .style("font-weight", "bold")
  .style("fill", "black");

svg.append("text")
  .text("CO")
  .attr("x", 168.5)
  .attr("y", 63.5)
  .attr("text-anchor", "middle")
  .style("font-size", "8px")
  .style("font-family", "Helvetica")
  .style("font-weight", "bold")
  .style("fill", "black");

// O3 Gas

svg.append("line")
  .attr("x1", 210)
  .attr("x2", 265)
  .attr("y1", 40)
  .attr("y2", 40)
  .style("stroke", o3Color)
  .style("stroke-width", "3px")
  .style("stroke-linecap", "round");

svg.append("line")
  .attr("x1", 300)
  .attr("x2", 270)
  .attr("y1", 18)
  .attr("y2", 40)
  .style("stroke", o3Color)
  .style("stroke-width", "3px")
  .style("stroke-linecap", "round");

svg.append("line")
  .attr("x1", 240)
  .attr("x2", 265)
  .attr("y1", 9)
  .attr("y2", 40)
  .style("stroke", o3Color)
  .style("stroke-width", "3px")
  .style("stroke-linecap", "round");

var o3Gradient = defs.append("radialGradient")
  .attr("id", "o3Gradient")
  .attr("cx", "50%")
  .attr("cy", "50%")
  .attr("r", "50%")
  .attr("fx", "50%")
  .attr("fy", "50%");

o3Gradient.append("stop")
  .attr("offset", "0%")
  .style("stop-color", innerColor);

o3Gradient.append("stop")
  .attr("offset", "90%")
  .style("stop-color", o3Color);

svg.append("circle")
  .attr("r", 20)
  .attr("cx", 265)
  .attr("cy", 40)
  .style("fill", "url(#o3Gradient)")
  .style("stroke", o3Color)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 5)
  .attr("cx", 240)
  .attr("cy", 9)
  .style("fill", "url(#o3Gradient)")
  .style("stroke", o3Color)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 5)
  .attr("cx", 300)
  .attr("cy", 18)
  .style("fill", "url(#o3Gradient)")
  .style("stroke", o3Color)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 7)
  .attr("cx", 210)
  .attr("cy", 40)
  .style("fill", "url(#o3Gradient)")
  .style("stroke", o3Color)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 10)
  .attr("cx", 277.5)
  .attr("cy", 60)
  .style("fill", "url(#o3Gradient)")
  .style("stroke", o3Color)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 3)
  .attr("cx", 242.5)
  .attr("cy", 62.5)
  .style("fill", "url(#o3Gradient)")
  .style("stroke", o3Color)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 3.5)
  .attr("cx", 272)
  .attr("cy", 12.5)
  .style("fill", "url(#o3Gradient)")
  .style("stroke", o3Color)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 2.5)
  .attr("cx", 295)
  .attr("cy", 40)
  .style("fill", "url(#o3Gradient)")
  .style("stroke", o3Color)
  .style("stroke-width", "2px");

svg.append("text")
  .text(o3)
  .attr("x", 265)
  .attr("y", 45.5)
  .attr("text-anchor", "middle")
  .style("font-size", "14px")
  .style("font-family", "Helvetica")
  .style("font-weight", "bold")
  .style("fill", "black");

svg.append("text")
  .text("O3")
  .attr("x", 277.5)
  .attr("y", 63.5)
  .attr("text-anchor", "middle")
  .style("font-size", "8px")
  .style("font-family", "Helvetica")
  .style("font-weight", "bold")
  .style("fill", "black");

// SO2 Gas

svg.append("line")
  .attr("x1", 15)
  .attr("x2", 40)
  .attr("y1", 90)
  .attr("y2", 117.5)
  .style("stroke", so2Color)
  .style("stroke-width", "3px")
  .style("stroke-linecap", "round");

svg.append("line")
  .attr("x1", 40)
  .attr("x2", 85)
  .attr("y1", 117.5)
  .attr("y2", 117.5)
  .style("stroke", so2Color)
  .style("stroke-width", "3px")
  .style("stroke-linecap", "round");

var so2Gradient = defs.append("radialGradient")
  .attr("id", "so2Gradient")
  .attr("cx", "50%")
  .attr("cy", "50%")
  .attr("r", "50%")
  .attr("fx", "50%")
  .attr("fy", "50%");

so2Gradient.append("stop")
  .attr("offset", "0%")
  .style("stop-color", innerColor);

so2Gradient.append("stop")
  .attr("offset", "90%")
  .style("stop-color", so2Color);

svg.append("circle")
  .attr("r", 20)
  .attr("cx", 40)
  .attr("cy", 117.5)
  .style("fill", "url(#so2Gradient)")
  .style("stroke", so2Color)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 10)
  .attr("cx", 54)
  .attr("cy", 137)
  .style("fill", "url(#so2Gradient)")
  .style("stroke", so2Color)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 6)
  .attr("cx", 20)
  .attr("cy", 143)
  .style("fill", "url(#so2Gradient)")
  .style("stroke", so2Color)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 3)
  .attr("cx", 10)
  .attr("cy", 120)
  .style("fill", "url(#so2Gradient)")
  .style("stroke", so2Color)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 5)
  .attr("cx", 15)
  .attr("cy", 90)
  .style("fill", "url(#so2Gradient)")
  .style("stroke", so2Color)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 5)
  .attr("cx", 85)
  .attr("cy", 117.5)
  .style("fill", "url(#so2Gradient)")
  .style("stroke", so2Color)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 6)
  .attr("cx", 55)
  .attr("cy", 100)
  .style("fill", "url(#so2Gradient)")
  .style("stroke", so2Color)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 2.5)
  .attr("cx", 40)
  .attr("cy", 90)
  .style("fill", "url(#so2Gradient)")
  .style("stroke", so2Color)
  .style("stroke-width", "2px");

svg.append("text")
  .text("SO2")
  .attr("x", 54)
  .attr("y", 140.5)
  .attr("text-anchor", "middle")
  .style("font-size", "8px")
  .style("font-family", "Helvetica")
  .style("font-weight", "bold")
  .style("fill", "black");

svg.append("text")
  .text( d3.format(".1f")(so2))
  .attr("x", 40)
  .attr("y", 122.5)
  .attr("text-anchor", "middle")
  .style("font-size", "14px")
  .style("font-family", "Helvetica")
  .style("font-weight", "bold")
  .style("fill", "black");

// Aerosol Optical Depth 

svg.append("line")
  .attr("x1", 122)
  .attr("x2", 155)
  .attr("y1", 145)
  .attr("y2", 117.5)
  .style("stroke", aodColor)
  .style("stroke-width", "3px")
  .style("stroke-linecap", "round");

var aodGradient = defs.append("radialGradient")
  .attr("id", "aodGradient")
  .attr("cx", "50%")
  .attr("cy", "50%")
  .attr("r", "50%")
  .attr("fx", "50%")
  .attr("fy", "50%");

aodGradient.append("stop")
  .attr("offset", "0%")
  .style("stop-color", innerColor);

aodGradient.append("stop")
  .attr("offset", "90%")
  .style("stop-color", aodColor);

svg.append("circle")
  .attr("r", 20)
  .attr("cx", 155)
  .attr("cy", 117.5)
  .style("fill", "url(#aodGradient)")
  .style("stroke", aodColor)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 10)
  .attr("cx", 168)
  .attr("cy", 137)
  .style("fill", "url(#aodGradient)")
  .style("stroke", aodColor)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 6)
  .attr("cx", 155)
  .attr("cy", 87.5)
  .style("fill", "url(#aodGradient)")
  .style("stroke", aodColor)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 5)
  .attr("cx", 180)
  .attr("cy", 103)
  .style("fill", "url(#aodGradient)")
  .style("stroke", aodColor)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 2.5)
  .attr("cx", 185)
  .attr("cy", 120)
  .style("fill", "url(#aodGradient)")
  .style("stroke", aodColor)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 7)
  .attr("cx", 132)
  .attr("cy", 110)
  .style("fill", "url(#aodGradient)")
  .style("stroke", aodColor)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 5)
  .attr("cx", 122)
  .attr("cy", 145)
  .style("fill", "url(#aodGradient)")
  .style("stroke", aodColor)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 2.5)
  .attr("cx", 145)
  .attr("cy", 142.5)
  .style("fill", "url(#aodGradient)")
  .style("stroke", aodColor)
  .style("stroke-width", "2px");

svg.append("text")
  .text("AOD")
  .attr("x", 168)
  .attr("y", 140.5)
  .attr("text-anchor", "middle")
  .style("font-size", "8px")
  .style("font-family", "Helvetica")
  .style("font-weight", "bold")
  .style("fill", "black");

svg.append("text")
  .text( d3.format(".2f")(aod))
  .attr("x", 155)
  .attr("y", 122.5)
  .attr("text-anchor", "middle")
  .style("font-size", "14px")
  .style("font-family", "Helvetica")
  .style("font-weight", "bold")
  .style("fill", "black");

// NH3 Gas

svg.append("line")
  .attr("x1", 220)
  .attr("x2", 265)
  .attr("y1", 100)
  .attr("y2", 117.5)
  .style("stroke", nh3Color)
  .style("stroke-width", "3px")
  .style("stroke-linecap", "round");

svg.append("line")
  .attr("x1", 265)
  .attr("x2", 290)
  .attr("y1", 117.5)
  .attr("y2", 90)
  .style("stroke", nh3Color)
  .style("stroke-width", "3px")
  .style("stroke-linecap", "round");

var nh3Gradient = defs.append("radialGradient")
  .attr("id", "nh3Gradient")
  .attr("cx", "50%")
  .attr("cy", "50%")
  .attr("r", "50%")
  .attr("fx", "50%")
  .attr("fy", "50%");

nh3Gradient.append("stop")
  .attr("offset", "0%")
  .style("stop-color", innerColor);

nh3Gradient.append("stop")
  .attr("offset", "90%")
  .style("stop-color", nh3Color);

svg.append("circle")
  .attr("r", 20)
  .attr("cx", 265)
  .attr("cy", 117.5)
  .style("fill", "url(#nh3Gradient)")
  .style("stroke", nh3Color)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 10)
  .attr("cx", 277.5)
  .attr("cy", 137)
  .style("fill", "url(#nh3Gradient)")
  .style("stroke", nh3Color)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 6)
  .attr("cx", 220)
  .attr("cy", 100)
  .style("fill", "url(#nh3Gradient)")
  .style("stroke", nh3Color)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 4)
  .attr("cx", 290)
  .attr("cy", 90)
  .style("fill", "url(#nh3Gradient)")
  .style("stroke", nh3Color)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 7)
  .attr("cx", 250)
  .attr("cy", 135)
  .style("fill", "url(#nh3Gradient)")
  .style("stroke", nh3Color)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 2.5)
  .attr("cx", 235)
  .attr("cy", 120)
  .style("fill", "url(#nh3Gradient)")
  .style("stroke", nh3Color)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 5)
  .attr("cx", 255)
  .attr("cy", 90)
  .style("fill", "url(#nh3Gradient)")
  .style("stroke", nh3Color)
  .style("stroke-width", "2px");

svg.append("circle")
  .attr("r", 3)
  .attr("cx", 293)
  .attr("cy", 115)
  .style("fill", "url(#nh3Gradient)")
  .style("stroke", nh3Color)
  .style("stroke-width", "2px");

svg.append("text")
  .text("NH3")
  .attr("x", 277.5)
  .attr("y", 140.5)
  .attr("text-anchor", "middle")
  .style("font-size", "8px")
  .style("font-family", "Helvetica")
  .style("font-weight", "bold")
  .style("fill", "black");

svg.append("text")
  .text( d3.format(".1f")(nh3))
  .attr("x", 265)
  .attr("y", 122.5)
  .attr("text-anchor", "middle")
  .style("font-size", "14px")
  .style("font-family", "Helvetica")
  .style("font-weight", "bold")
  .style("fill", "black");

</script>