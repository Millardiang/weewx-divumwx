<!DOCTYPE html>
<html lang="en">
<?php
include ('dvmCombinedData.php');
date_default_timezone_set($TZ);
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
$sun2 = file_get_contents('jsondata/sun2.txt',true);
$moon2 = file_get_contents('jsondata/moon2.txt',true);
?>

<head>
<meta charset="utf-8">
<title>Geocentric for weewx</title>
</head>
<span class='moduletitle'><?php echo 'Geocentric';?></span>
<div class="updatedtime2"><span><?php if(file_exists($livedata)&&time() - filemtime($livedata)>300) echo $offline. '<offline> Offline </offline>'; else echo $online." ".$divum["time"];?></div>
<body>

<script src="js/d3.7.9.0.min.js"></script> 


<div class="Geocentric"></div>

<script>

var suncurve = [<?php echo $sun2?>];
var mooncurve = [<?php echo $moon2?>];
var sunaz = <?php echo $alm["sun_azimuth"]?>;
var sunalt = <?php echo $alm["sun_altitude"]?>;
var moonaz = <?php echo $alm["moon_azimuth"]?>;
var moonalt = <?php echo $alm["moon_altitude"]?>;

var sunData = [];
for(let i = 1; i< suncurve.length; i++) {
  sunData = [...sunData,[suncurve[i - 1],suncurve[i]]]
}

var moonData = [];
for(let i = 1; i< mooncurve.length; i++) {
  moonData = [...moonData,[mooncurve[i - 1],mooncurve[i]]]
}

var w = 310;
var h = 150;
var padding = 25;
var padding_up = 10;

var xScale = d3.scaleLinear()
    .domain([0, 360])
    .range([padding, w - padding + 12]);

var yScale = d3.scaleLinear()
    .domain([-60, 60])
    .range([h - padding, padding_up]);

var svg = d3.select('.Geocentric')
    .append('svg')
    //.style("background", "#292e35")
    .attr('width', w)
    .attr('height', h);

var line = d3.line() 
    .x(d => xScale(d.x))
    .y(d => yScale(d.y))
    .curve(d3.curveBasisOpen());

svg.selectAll(".horizon.line")
    .data(sunData)
    .enter()
    .append('line')
    .attr("class", "horizon line")
    .attr("x1", xScale(0))
    .attr("y1", yScale(0))
    .attr("x2", xScale(360))
    .attr("y2", yScale(0));

svg.selectAll(".zenith.line")
    .data(sunData)
    .enter()
    .append('line')
    .attr("class", "zenith line")
    .attr("x1", xScale(180))
    .attr("y1", yScale(60))
    .attr("x2", xScale(180))
    .attr("y2", yScale(-60));

svg.selectAll('.moon.line')
    .data(moonData)
    .enter()
    .append('line')
    .attr("class", "moon line")
    .attr('x1',(d)=>xScale(d[0][0]))
    .attr('y1',(d)=>yScale(d[0][1]))
    .attr('x2',(d)=>xScale(d[1][0]))
    .attr('y2',(d)=>yScale(d[1][1]));

svg.selectAll('.moon.circle')
    .data(moonData)
    .enter()
    .append('circle')
    .attr("class", "moon circle")
    .attr('cx', xScale(moonaz))
    .attr('cy', yScale(moonalt))
    .attr('r', 3); 

svg.selectAll('.sun.line')
    .data(sunData)
    .enter()
    .append('line')
    .attr("class", "sun line")
    .attr('x1',(d)=>xScale(d[0][0]))
    .attr('y1',(d)=>yScale(d[0][1]))
    .attr('x2',(d)=>xScale(d[1][0]))
    .attr('y2',(d)=>yScale(d[1][1]));

svg.selectAll('.sun.circle')
    .data(sunData)
    .enter()
    .append('circle')
    .attr("class", "sun circle")
    .attr('cx', xScale(sunaz))
    .attr('cy', yScale(sunalt))
    .attr('r', 6.5);

var zenith = "Zenith";
svg.append("text")
    .attr("x", xScale(167))
    .attr("y", yScale(63))
    .text(zenith);

var horizon = "Horizon";
svg.append("text")
    .attr("x", xScale(5))
    .attr("y", yScale(5))
    .text(horizon);

var xAxis = d3.axisBottom(xScale)
    .ticks(9)
    .tickFormat(function(d) { return d + "°";})
    .tickValues([0, 45, 90, 135, 180, 225, 270, 315, 360]);

var yAxis = d3.axisLeft(yScale)
    .ticks(7)
    .tickFormat(function(d) { return d + "°";})
    .tickValues([-60, -40, -20, 0, 20, 40, 60]);

svg.append('g')
    .attr('transform', 'translate(0,' + (h - padding) + ')')
    .call(xAxis);

svg.append('g')
    .attr('transform', 'translate(' + padding + ',0)')
    .call(yAxis);

</script>
</body>
</html>