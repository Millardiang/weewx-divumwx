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
?>
<head> 
<meta charset="utf-8">
<title>Geocentric View for weewx</title>
<div class="chartforecast">
<span class="yearpopup"><a alt="SunMoon" title="SunMoon" href="dvmSunPath.php" data-lity><?php echo $menucharticonpage;?> Geocentric</a></span>
</div>
<span class='moduletitle'><?php echo 'Geocentric';?></span>
<div class="updatedtime1"><span><?php if(file_exists($livedata)&&time() - filemtime($livedata)>300) echo $offline. '<offline> Offline </offline>'; else echo $online." ".$divum["time"];?></div>
</head>
<body>

<!--style>

// make sure your have these variables in your dvmCombinedData.php.tmpl

$alm["moon_azimuth"] = $almanac.moon.az;
$alm["sun_altitude"] = $almanac.sun.alt;

$alm["moon_azimuth"] = $almanac.moon.az;
$alm["moon_altitude"] = $almanac.moon.alt;

$alm["sun_right_ascension"] = $almanac.sun.ra;
$alm["sun_declination"] = $almanac.sun.dec;

$alm["moon_right_ascension"] = $almanac.moon.ra;
$alm["moon_declination"] = $almanac.moon.dec;

// divumwx.main.css
// comment out or delete your old Geocentric css then add the new css below.
// once you have done everything you can delete this text within the style brackets and enjoy !

.Geocentric{position:relative;top:-2px;left:0px;text{fill:"var(--col-6)";font-family:Helvetica;font-size:7.5px;}
path{stroke:#555;stroke-width:1;fill:none;}.horizon.line{stroke:#007fff;stroke-width:1;fill:none;stroke-linecap:round;}
line{stroke:#555;stroke-width:1;stroke-linecap:round;fill:none;}.zenith.line{stroke:#2e8b57;stroke-width:1;fill:none;stroke-linecap:round;}}

</style--> 

<script src="js/d3.7.9.0.min.js"></script>

<div class="Geocentric"></div>

<script>

var latitude = <?php echo $lat;?>; 
var longitude = <?php echo $lon;?>;
var hemisphere;

if (latitude >= "0") {
  <?php echo "hemisphere = 0";?>;
} else {
  <?php echo "hemisphere = 1";?>;
}

function toDegrees(x) {
  return x * (180.0 / Math.PI);
}

function toRadians(x) {
  return x * (Math.PI / 180.0);
}

var delta = <?php echo $alm["sun_declination"];?>;

var sdec = delta;
var althrtab = [];
var shartab = [];
var total = 1440.0;

for (var i = 0; i < total; i++) {
  var sha = 180.0 + i * (360.0 / total);  
  var cossha = Math.cos(toRadians(sha)); 
  var cossundec = Math.cos(toRadians(sdec));  
  var coslat = Math.cos(toRadians(latitude)); 
  var cosmath = cossha * cossundec * coslat; 
  var sinsundec = Math.sin(toRadians(sdec)); 
  var sinlat = Math.sin(toRadians(latitude));  
  var sinmath = sinsundec * sinlat;  
  var sinelevation = cosmath + sinmath;  
  var elevation = toDegrees(Math.asin(sinelevation)); 
  var curaltlong = elevation;  
  var curalt = curaltlong;  
  althrtab[i] = curalt;
  shartab[i] = sha - 360.0;
}

var azitab;
var azmathA;
var azmathB;
var cosazA;
var aziA;
var aziplotA;

if (hemisphere == 0) {
  azitab = [];
  for (var i = 0; i < total; i++) {
    azmathA =
      Math.cos(toRadians(shartab[i])) * cossundec * sinlat - sinsundec * coslat;
    azmathB = Math.cos(toRadians(althrtab[i]));
    cosazA = azmathA / azmathB;
    aziA = toDegrees(Math.acos(cosazA));
    aziplotA = aziA;
    if (shartab[i] < 0) {
      azitab[i] = (aziplotA - 180.0) * -1;
    } else {
      azitab[i] = aziplotA + 180.0;
    }
  }
}
if (hemisphere == 1) {
  azitab = [];
  for (var i = 0; i < total; i++) {
    azmathA =
      Math.cos(toRadians(shartab[i])) * cossundec * sinlat - sinsundec * coslat;
    azmathB = Math.cos(toRadians(althrtab[i]));
    cosazA = azmathA / azmathB;
    aziA = toDegrees(Math.acos(cosazA));
    aziplotA = aziA;
    if (shartab[i] < 0) {
      azitab[i] = (aziplotA - 360.0) * -1;
    } else {
      azitab[i] = aziplotA;
    }
  }
}

deltax = <?php echo $alm["moon_declination"];?>; 

var sdecx = deltax;
var shartabx = [];
var althrtabx = [];
var total = 1440.0;

var cossundecx = Math.cos(toRadians(sdecx));
var coslatx = Math.cos(toRadians(latitude));
var sinlatx = Math.sin(toRadians(latitude));
var sinsundecx = Math.sin(toRadians(sdecx));
var sinmathx = sinsundecx * sinlatx;

for (var i = 0; i < total; i++) {
  var shax = 180.0 + i * (360.0 / total);
  var cosshax = Math.cos(toRadians(shax));
  var cosmathx = cosshax * cossundecx * coslatx;
  var sinelevationx = cosmathx + sinmathx;
  var elevationx = toDegrees(Math.asin(sinelevationx));
  var curaltlongx = elevationx;
  var curaltx = curaltlongx;
  althrtabx[i] = curaltx;
  shartabx[i] = shax - 360.0;
}

var azitabx;
var azmath1;
var azmath2;
var cosazx;
var azix;
var aziplotx;

if (hemisphere == 0) {
  azitabx = [];
  for (var i = 0; i < total; i++) {
    azmath1 =
      Math.cos(toRadians(shartabx[i])) * cossundecx * sinlatx -
      sinsundecx * coslatx;
    azmath2 = Math.cos(toRadians(althrtabx[i]));
    cosazx = azmath1 / azmath2;
    azix = toDegrees(Math.acos(cosazx));
    aziplotx = azix;
    if (shartabx[i] < 0) {
      azitabx[i] = (aziplotx - 180.0) * -1;
    } else {
      azitabx[i] = aziplotx + 180.0;
    }
  }
}
if (hemisphere == 1) {
  azitabx = [];
  for (var i = 0; i < total; i++) {
    azmath1 =
      Math.cos(toRadians(shartabx[i])) * cossundecx * sinlatx -
      sinsundecx * coslatx;
    azmath2 = Math.cos(toRadians(althrtabx[i]));
    cosazx = azmath1 / azmath2;
    azix = toDegrees(Math.acos(cosazx));
    aziplotx = azix;
    if (shartabx[i] < 0) {
      azitabx[i] = (aziplotx - 360.0) * -1;
    } else {
      azitabx[i] = aziplotx;
    }
  }
}

/*
At 180 degrees (The Meridian Transit, the sun and moon's highest point), 
in the loop data set for both of the curves there is a NaN.
We can test for this and replace it with 180.0 otherwise 
the curve(s) will not exist for 60 seconds.
*/
function nan(x) {
  if (isNaN(x)) {
    return 180.00000000000000;
  }
  return x;
}

// Create some fake data to populate the chart scale 

var suncurve = [[0.0, 0.0],[0.0, 0.0]];
var mooncurve = [[0.0, 0.0],[0.0 ,0.0]];

var sunData = [];
for(var i = 1; i < suncurve.length; i++) {
  sunData = [...sunData,[suncurve[i - 1],suncurve[i]]]
};

var moonData = [];
for(var i = 1; i < mooncurve.length; i++) {
  moonData = [...moonData,[mooncurve[i - 1],mooncurve[i]]]
};

var w = 310;
var h = 160;
var padding = 25;
var padding_up = 8;

var xScale = d3.scaleLinear()
    .domain([0, 360])
    .range([padding, w - padding + 16]);

var yScale = d3.scaleLinear()
    .domain([-80, 80])
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

var xAxis = d3.axisBottom(xScale)
    .ticks(9)
    .tickSize(4)
    .tickPadding(3)
    .tickFormat(function(d) { return d + "\u00B0";})
    .tickValues([0, 45, 90, 135, 180, 225, 270, 315, 360]);

var yAxis = d3.axisLeft(yScale)
    .ticks(9)
    .tickSize(4)
    .tickPadding(2)
    .tickFormat(function(d) { return d + "\u00B0";})
    .tickValues([-80, -60, -40, -20, 0, 20, 40, 60, 80]);

  svg
    .append('g')
    .attr('transform', 'translate(0,' + (h - padding) + ')')
    .call(xAxis);

  svg
    .append('g')
    .attr('transform', 'translate(' + padding + ',0)')
    .call(yAxis);

  svg
    .selectAll(".horizon.line")
    .data(sunData)
    .enter()
    .append('line')
    .attr("class", "horizon line")
    .attr("x1", xScale(0))
    .attr("y1", yScale(0))
    .attr("x2", xScale(360))
    .attr("y2", yScale(0));

  svg
    .selectAll(".zenith.line")
    .data(sunData)
    .enter()
    .append('line')
    .attr("class", "zenith line")
    .attr("x1", xScale(180))
    .attr("y1", yScale(80))
    .attr("x2", xScale(180))
    .attr("y2", yScale(-80));

var innerColor = "rgb(230, 200, 200)";

var defs = svg.append("defs");

var moonGradient = defs.append("radialGradient")
    .attr("id", "moonGradient")
    .attr("cx", "50%")
    .attr("cy", "50%")
    .attr("r", "50%")
    .attr("fx", "50%")
    .attr("fy", "50%");

moonGradient.append("stop")
    .attr("offset", "0%")
    .style("stop-color", innerColor);

moonGradient.append("stop")
    .attr("offset", "90%")
    .style("stop-color", "#555");

var sunGradient = defs.append("radialGradient")
    .attr("id", "sunGradient")
    .attr("cx", "50%")
    .attr("cy", "50%")
    .attr("r", "50%")
    .attr("fx", "50%")
    .attr("fy", "50%");

sunGradient.append("stop")
    .attr("offset", "0%")
    .style("stop-color", innerColor);

sunGradient.append("stop")
    .attr("offset", "90%")
    .style("stop-color", "tomato");

var sunazi = <?php echo $alm["sun_azimuth"];?>;
var sunalt = <?php echo $alm["sun_altitude"];?>;

// get the data for the sun curve
for (var i = 0; i < total; i++) {

var sun_azitab = nan(azitab[i]);
var sun_althrtab = althrtab[i];
  
  svg
    .append("line")
    .attr("x1", xScale(sun_azitab))
    .attr("y1", yScale(sun_althrtab))
    .attr("x2", xScale(sun_azitab))
    .attr("y2", yScale(sun_althrtab))
    .style("stroke", "tomato");

  svg
    .append("circle")
    .attr("r", 6.5)
    .attr("cx", xScale(sunazi))
    .attr("cy", yScale(sunalt))
    .style("fill", "url(#sunGradient)")
    .style("stroke-width", "2px");
}

var moonazi = <?php echo $alm["moon_azimuth"];?>;
var moonalt = <?php echo $alm["moon_altitude"];?>;

// get the data for the moon curve  
for (var i = 0; i < total; i++) {

var moon_azitabx = nan(azitabx[i]);
var moon_althrtabx = althrtabx[i];

  svg
    .append("line")
    .attr("x1", xScale(moon_azitabx))
    .attr("y1", yScale(moon_althrtabx))
    .attr("x2", xScale(moon_azitabx))
    .attr("y2", yScale(moon_althrtabx))
    .style("stroke", "silver");

  svg
    .append("circle")
    .attr("r", 4)
    .attr("cx", xScale(moonazi))
    .attr("cy", yScale(moonalt))
    .style("fill", "url(#moonGradient)")
    .style("stroke-width", "1px");  
}

var zenith = "Zenith";
  svg
    .append("text")
    .style("fill", "var(--col-6)")
    .attr("x", xScale(167))
    .attr("y", yScale(83))
    .text(zenith);

var horizon = "Horizon";
  svg
    .append("text")
    .style("fill", "var(--col-6)")
    .attr("x", xScale(5))
    .attr("y", yScale(5))
    .text(horizon);
    
</script> 
</body>
</html> 