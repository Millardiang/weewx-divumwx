<?php
include('dvmCombinedData.php');
error_reporting(0);
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
?> 
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>weather vpd</title>
</head>
<body>
 <style>
.vpd-module{ position: relative; margin-top: -2.5px; margin-left: 0px; }
</style>   

<div class="chartforecast">
<!--span class="yearpopup"><a alt="rain charts" title="rain charts" href="dvmRainfallRecords.php" data-lity><svg width="9.6051521" height="9.5999393" viewBox="0 0 0.28815456 0.28799818" version="1.1" id="svg829" sodipodi:docname="graphicon2.svg" inkscape:version="1.1 (c4e8f9e, 2021-05-24)" xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape" xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd" xmlns="http://www.w3.org/2000/svg" xmlns:svg="http://www.w3.org/2000/svg"><sodipodi:namedview id="namedview831" pagecolor="#ffffff" bordercolor="#666666" borderopacity="1.0" inkscape:pageshadow="2" inkscape:pageopacity="0.0" inkscape:pagecheckerboard="0" showgrid="false" inkscape:zoom="19.148201" inkscape:cx="8.2253158" inkscape:cy="6.0318982" inkscape:window-width="2041" inkscape:window-height="1081" inkscape:window-x="0" inkscape:window-y="25" inkscape:window-maximized="0" inkscape:current-layer="svg829" /><defs id="defs826"><style id="style824"> .cls-1 { fill: #101010; fill-rule: evenodd; } </style></defs><path id="graph1" class="cls-1" d="m 0.28152,0.13306617 -0.093,0.055104 v 0 a 0.011772,0.011772 0 0 1 -0.00672,0.00312 0.010308,0.010308 0 0 1 -0.01056,-0.00336 L 0.12,0.13719417 0.06864,0.18814617 a 0.012216,0.012216 0 0 1 -0.01716,0 v 0 a 0.012,0.012 0 0 1 0,-0.016968 l 0.05988,-0.0594 a 0.012372,0.012372 0 0 1 0.01728,0 l 0.05196,0.051516 0.09036,-0.053604 a 0.011004,0.011004 0 0 1 0.01596,0.00528 v 0 a 0.014484,0.014484 0 0 1 -0.0054,0.018024 z M 0.276,0.26399817 a 0.012,0.012 0 0 1 0,0.024 H 0.012 A 0.012,0.012 0 0 1 0,0.27599817 v -0.264 a 0.012,0.012 0 0 1 0.024,0 v 0.252 z" style="fill:#008000;stroke-width:0.012" /></svg>
 Rainfall Records and Charts</a></span-->
</div>
<span class='moduletitle'>Vapour Pressure Deficit (<valuetitleunit>kPa</valuetitleunit>)</span>
<div class="updatedtime1"><span><?php if (file_exists($livedata)&&time() - filemtime($livedata)>300) echo $offline. '<offline> Offline </offline>'; else echo $online." ".$divum["time"];?></div>

<script src="js/d3.7.9.0.min.js"></script>

<div class="vpd-module"></div>

<script>

var baseTextColor = "var(--col-6)";
                  
var units = "kPa";

var currentImage = "img/vpdTree.svg";

var maxColor = "#ff5555";

var vpdCurrent = "<?php echo $barom["vpd_current"];?>";                   
var vpdDayMin = "<?php echo $barom["vpd_day_min"];?>";
var vpdDayMax = "<?php echo $barom["vpd_day_max"];?>";
var vpdWeekMin = "<?php echo $barom["vpd_week_min"];?>";
var vpdWeekMax = "<?php echo $barom["vpd_week_max"];?>";
var vpdMonthMin = "<?php echo $barom["vpd_month_min"];?>";
var vpdMonthMax = "<?php echo $barom["vpd_month_max"];?>";
var vpdYearMax = "<?php echo $barom["vpd_year_max"];?>"; 
var vpdYearMin = "<?php echo $barom["vpd_year_min"];?>";
                 
var svg = d3.select(".vpd-module")
    .append("svg")
    //.style("background", "red")
    .attr("width", 310)
    .attr("height", 150);

svg.append("text")
    .attr("x", 37)
    .attr("y", 10)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("Day Min");

var data = [d3.format(".2f")(vpdDayMin) + "-" + " " + units];

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 37)
    .attr("y", function(d, i) { return 20 + i * 20; })

    .style("fill", "#007FFF")
    .style("font-family", "Helvetica") 
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[0]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[1]; });

svg.append("text")
    .attr("x", 272)
    .attr("y", 10)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("Day Max");

var data = [d3.format(".2f")(vpdDayMax) + "-" + " " + units];

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 272)
    .attr("y", function(d, i) { return 20 + i * 20; })

    .style("fill", maxColor)
    .style("font-family", "Helvetica") 
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[0]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[1]; });

svg.append("text")
    .attr("x", 37)
    .attr("y", 52)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("Week Min");

var data = [d3.format(".2f")(vpdWeekMin) + "-" + " " + units];

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 37)
    .attr("y", function(d, i) { return 62 + i * 62; })

    .style("fill", "#007FFF")
    .style("font-family", "Helvetica") 
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[0]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[1]; });

svg.append("text")
    .attr("x", 272)
    .attr("y", 52)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("Week Max");

var data = [d3.format(".2f")(vpdWeekMax) + "-" + " " + units];

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 272)
    .attr("y", function(d, i) { return 62 + i * 62; })

    .style("fill", maxColor)
    .style("font-family", "Helvetica") 
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[0]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[1]; });

svg.append("text")
    .attr("x", 37)
    .attr("y", 96)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("Month Min");

var data = [d3.format(".2f")(vpdMonthMin) + "-" + " " + units];

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 37)
    .attr("y", function(d, i) { return 106 + i * 106; })

    .style("fill", "#007FFF")
    .style("font-family", "Helvetica") 
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[0]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[1]; });

svg.append("text")
    .attr("x", 272)
    .attr("y", 96)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("Month Max");

var data = [d3.format(".2f")(vpdMonthMax) + "-" + " " + units];

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 272)
    .attr("y", function(d, i) { return 106 + i * 106; })

    .style("fill", maxColor)
    .style("font-family", "Helvetica") 
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[0]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[1]; });

svg.append("text")
    .attr("x", 37)
    .attr("y", 136)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("Year Min");

var data = [d3.format(".2f")(vpdYearMin) + "-" + " " + units];

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 37)
    .attr("y", function(d, i) { return 146 + i * 146; })

    .style("fill", "#007FFF")
    .style("font-family", "Helvetica") 
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[0]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[1]; });

svg.append("text")
    .attr("x", 272)
    .attr("y", 136)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("Year Max");

var data = [d3.format(".2f")(vpdYearMax) + "-" + " " + units];

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 272)
    .attr("y", function(d, i) { return 146 + i * 146; })

    .style("fill", maxColor)
    .style("font-family", "Helvetica") 
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[0]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[1]; });

svg.append("image") // image output
    .attr('xlink:href', currentImage)
    .attr('height', 105)
    .attr('x', 95)
    .attr('y', 10);

var data = [d3.format(".2f")(vpdCurrent) + "-" + " " + units];

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 155)
    .attr("y", function(d, i) { return 140 + i * 140; })

    .style("fill", baseTextColor)
    .style("font-family", "Helvetica") 
    .style("font-size", "13px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[0]; })

    .append("tspan")
    .style("fill", baseTextColor)
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[1]; });

</script>
</body>
</html>
