<?php
#####################################################################################################################                                                                                                        #
#                                                                                                                   #
# weewx-divumwx Skin Template maintained by The DivumWX Team                                                        #
#                                                                                                                   #
# Copyright (C) 2023 Sean Balfour. All rights reserved                                 #
#                                                                                                                   #
# Distributed under terms of the GPLv3. See the file LICENSE.txt for your rights.                                   #
#                                                                                                                   #
# Issues for weewx-divumwx skin template should be addressed to https://github.com/Millardiang/weewx-divumwx/issues # 
#                                                                                                                   #
#####################################################################################################################
?><?php
include('dvmCombinedData.php');
date_default_timezone_set($TZ);
echo "<body style='background-color:#292E35'>";
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Orrery</title>
</head>
<style>

  .orrery {
    position: relative; 
    margin-top: -275px; 
    margin-left: -10px;
    }

  body {
    overflow: hidden;
    }

</style>

<body>
<script src="js/two.js"></script>
<script src="js/d3.4.2.2.min.js"></script>
<div id="particlemap" width="280" height="280" style="position: relative; top: 140px; left: 110px;"></div>
<div class="orrery"></div>

<script>

var Mercury = <?php echo $alm["mercury_hlongitude"];?>;
var Venus = <?php echo $alm["venus_hlongitude"];?>;
var Earth = <?php echo $alm["earth_hlongitude"];?>;
var Mars = <?php echo $alm["mars_hlongitude"];?>;
var Moon = <?php echo $alm["moon_azimuth"];?>; 
var Jupiter = <?php echo $alm["jupiter_hlongitude"];?>;
var Saturn = <?php echo $alm["saturn_hlongitude"];?>;
var Uranus = <?php echo $alm["uranus_hlongitude"];?>;
var Neptune = <?php echo $alm["neptune_hlongitude"];?>;
var Pluto = <?php echo $alm["pluto_hlongitude"];?>;
var Moonx = Moon + Earth;

Date.prototype.getJulian = function() {
  return (this / 86400000) - (this.getTimezoneOffset() / 1440) + 2440587.5;
}

var now = new Date(); // Julian Date Now
var jd = now.getJulian();

var j2000 = new Date("Jan 01 2000 00:00:00"); // Julian Date on the 1st Jan Year 2000 Midnight 00:00:00
var jd2000 = j2000.getJulian();

var jdy = (jd - jd2000) / 365;  // Julian years since the year 2000 midnight
var jday = (jd - jd2000); // Julian days since the year 2000 midnight
var jsec = (jd - jd2000) * 86400; // Julian seconds since the year 2000 midnight

var svg = d3.select(".orrery")
    .append("svg")
    //.style("background", "#292E35")
    .attr("width", 780)
    .attr("height", 500);

// backplate inner
 svg.append("circle")   
    .attr("cx", 260)
    .attr("cy", 275)
    .attr("r", 60)
    .attr('stroke-width', "78px")        
    .attr('stroke', '#292E35')
    .attr('fill', 'none');

// backplate outer
 svg.append("circle")   
    .attr("cx", 260)
    .attr("cy", 275)
    .attr("r", 164.5)
    .attr('stroke-width', "50px")        
    .attr('stroke', '#292E35')
    .attr('fill', 'none');   

// Center of Orrery
var centerX = 260;
var centerY = 275;

// Mercury Orbit
 svg.append("circle")   
    .attr("cx", 260)
    .attr("cy", 275)
    .attr("r", 40)
    .attr('stroke-width', "1px")        
    .attr('stroke', '#424140')
    .attr('fill', 'none');

var Radius = 40;
var mercuryX = centerX + ((Radius) * Math.sin((90-Mercury) * Math.PI / 180.0));
var mercuryY = centerY - ((Radius) * Math.cos((90-Mercury) * Math.PI / 180.0));

svg.append("line") // needle
    .attr("x1", centerX)
    .attr("x2", mercuryX)
    .attr("y1", centerY)
    .attr("y2", mercuryY)
    .style("stroke", "#424140")
    .style("stroke-width", 1);

// Mercury Planet
svg.append("circle")   
    .attr("cx", mercuryX)
    .attr("cy", mercuryY)
    .attr("r", 4)
    .attr('fill', '#F618CC');

// Venus Orbit
 svg.append("circle")   
    .attr("cx", 260)
    .attr("cy", 275)
    .attr("r", 60)
    .attr('stroke-width', 1)        
    .attr('stroke', '#424140')
    .attr('fill', 'none');

var Radius = 60;
var venusX = centerX + ((Radius) * Math.sin((90-Venus) * Math.PI / 180.0));
var venusY = centerY - ((Radius) * Math.cos((90-Venus) * Math.PI / 180.0));

svg.append("line") // needle
    .attr("x1", centerX)
    .attr("x2", venusX)
    .attr("y1", centerY)
    .attr("y2", venusY)
    .style("stroke", "#424140")
    .style("stroke-width", 1);

// Venus Planet
svg.append("circle")   
    .attr("cx", venusX)
    .attr("cy", venusY)
    .attr("r", 4)
    .attr('fill', '#2E8B57');    

// Earth Orbit
 svg.append("circle")   
    .attr("cx", 260)
    .attr("cy", 275)
    .attr("r", 80)
    .attr('stroke-width', 1)        
    .attr('stroke', '#424140')
    .attr('fill', 'none');

var Radius = 80;
var earthX = centerX + ((Radius) * Math.sin((90-Earth) * Math.PI / 180.0));
var earthY = centerY - ((Radius) * Math.cos((90-Earth) * Math.PI / 180.0));

svg.append("line") // needle
    .attr("x1", centerX)
    .attr("x2", earthX)
    .attr("y1", centerY)
    .attr("y2", earthY)
    .style("stroke", "#424140")
    .style("stroke-width", 1);

// moon Orbit
 svg.append("circle")   
    .attr("cx", earthX)
    .attr("cy", earthY)
    .attr("r", 12)
    .attr('stroke-width', 1)        
    .attr('stroke', '#424140')
    .attr('fill', 'none');

var Radius = 12;
var moonX = earthX + ((Radius) * Math.sin((90-Moonx) * Math.PI / 180.0));
var moonY = earthY - ((Radius) * Math.cos((90-Moonx) * Math.PI / 180.0));

svg.append("line") // needle
    .attr("x1", earthX)
    .attr("x2", moonX)
    .attr("y1", earthY)
    .attr("y2", moonY)
    .style("stroke", "#424140")
    .style("stroke-width", 1);

// Earth Planet
svg.append("circle")   
    .attr("cx", earthX)
    .attr("cy", earthY)
    .attr("r", 5.5)
    .attr('fill', '#007FFF');

// Moon Planet
svg.append("circle")   
    .attr("cx", moonX)
    .attr("cy", moonY)
    .attr("r", 2)
    .attr('fill', 'white');

// Mars Orbit
 svg.append("circle")   
    .attr("cx", 260)
    .attr("cy", 275)
    .attr("r", 100)
    .attr('stroke-width', 1)        
    .attr('stroke', '#424140')
    .attr('fill', 'none');

var Radius = 100;
var marsX = centerX + ((Radius) * Math.sin((90-Mars) * Math.PI / 180.0));
var marsY = centerY - ((Radius) * Math.cos((90-Mars) * Math.PI / 180.0));

svg.append("line") // needle
    .attr("x1", centerX)
    .attr("x2", marsX)
    .attr("y1", centerY)
    .attr("y2", marsY)
    .style("stroke", "#424140")
    .style("stroke-width", 1);

// Mars Planet
svg.append("circle")   
    .attr("cx", marsX)
    .attr("cy", marsY)
    .attr("r", 4)
    .attr('fill', '#FF0000');

// Jupiter Orbit
 svg.append("circle")   
    .attr("cx", 260)
    .attr("cy", 275)
    .attr("r", 140)
    .attr('stroke-width', 1)        
    .attr('stroke', '#424140')
    .attr('fill', 'none');

var Radius = 140;
var jupiterX = centerX + ((Radius) * Math.sin((90-Jupiter) * Math.PI / 180.0));
var jupiterY = centerY - ((Radius) * Math.cos((90-Jupiter) * Math.PI / 180.0));

svg.append("line") // needle
    .attr("x1", centerX)
    .attr("x2", jupiterX)
    .attr("y1", centerY)
    .attr("y2", jupiterY)
    .style("stroke", "#424140")
    .style("stroke-width", 1);

// Jupiter Planet
svg.append("circle")   
    .attr("cx", jupiterX)
    .attr("cy", jupiterY)
    .attr("r", 10)
    .attr('fill', '#BE688B');

// Saturn Orbit
 svg.append("circle")   
    .attr("cx", 260)
    .attr("cy", 275)
    .attr("r", 160)
    .attr('stroke-width', 1)        
    .attr('stroke', '#424140')
    .attr('fill', 'none');

var Radius = 160;
var saturnX = centerX + ((Radius) * Math.sin((90-Saturn) * Math.PI / 180.0));
var saturnY = centerY - ((Radius) * Math.cos((90-Saturn) * Math.PI / 180.0));

svg.append("line") // needle
    .attr("x1", centerX)
    .attr("x2", saturnX)
    .attr("y1", centerY)
    .attr("y2", saturnY)
    .style("stroke", "#424140")
    .style("stroke-width", 1);

// Saturn Planet
svg.append("circle")   
    .attr("cx", saturnX)
    .attr("cy", saturnY)
    .attr("r", 6.5)
    .attr('fill', '#FFA54F');

// inner ring
svg.append("circle")   
    .attr("cx", saturnX)
    .attr("cy", saturnY)
    .attr("r", 12.5)
    .attr('fill', 'none')
    .attr('stroke', '#FFA54F')
    .attr('stroke-width', 4.5);

// outer ring
svg.append("circle")   
    .attr("cx", saturnX)
    .attr("cy", saturnY)
    .attr("r", 17.5)
    .attr('fill', 'none')
    .attr('stroke', '#FFA54F')
    .attr('stroke-width', 1.5);

// Uranus Orbit
 svg.append("circle")   
    .attr("cx", 260)
    .attr("cy", 275)
    .attr("r", 180)
    .attr('stroke-width', 1)        
    .attr('stroke', '#424140')
    .attr('fill', 'none');

var Radius = 180;
var uranusX = centerX + ((Radius) * Math.sin((90-Uranus) * Math.PI / 180.0));
var uranusY = centerY - ((Radius) * Math.cos((90-Uranus) * Math.PI / 180.0));

svg.append("line") // needle
    .attr("x1", centerX)
    .attr("x2", uranusX)
    .attr("y1", centerY)
    .attr("y2", uranusY)
    .style("stroke", "#424140")
    .style("stroke-width", 1);

// Uranus Planet
svg.append("circle")   
    .attr("cx", uranusX)
    .attr("cy", uranusY)
    .attr("r", 6)
    .attr('fill', '#F67F40');

// Neptune Orbit
 svg.append("circle")   
    .attr("cx", 260)
    .attr("cy", 275)
    .attr("r", 200)
    .attr('stroke-width', 1)        
    .attr('stroke', '#424140')
    .attr('fill', 'none');

var Radius = 200;
var neptuneX = centerX + ((Radius) * Math.sin((90-Neptune) * Math.PI / 180.0));
var neptuneY = centerY - ((Radius) * Math.cos((90-Neptune) * Math.PI / 180.0));

svg.append("line") // needle
    .attr("x1", centerX)
    .attr("x2", neptuneX)
    .attr("y1", centerY)
    .attr("y2", neptuneY)
    .style("stroke", "#424140")
    .style("stroke-width", 1);

// Neptune Planet
svg.append("circle")   
    .attr("cx", neptuneX)
    .attr("cy", neptuneY)
    .attr("r", 6)
    .attr('fill', '#5FC6C6');

// Pluto Orbit
 svg.append("circle")   
    .attr("cx", 260)
    .attr("cy", 275)
    .attr("r", 220)
    .attr('stroke-width', 1)        
    .attr('stroke', '#424140')
    .attr('fill', 'none');

var Radius = 220;
var plutoX = centerX + ((Radius) * Math.sin((90-Pluto) * Math.PI / 180.0));
var plutoY = centerY - ((Radius) * Math.cos((90-Pluto) * Math.PI / 180.0));

svg.append("line") // needle
    .attr("x1", centerX)
    .attr("x2", plutoX)
    .attr("y1", centerY)
    .attr("y2", plutoY)
    .style("stroke", "#424140")
    .style("stroke-width", 1);

// Pluto Planet
svg.append("circle")   
    .attr("cx", plutoX)
    .attr("cy", plutoY)
    .attr("r", 3)
    .attr('fill', '#B6F131');   

// Sun with noise filter
var defSun = svg.append("defs");

var filterSun = defSun.append("filter")
    .attr("id","sunFilter");

var feTurbulence = filterSun.append("feTurbulence")
    .attr("id","turbolence")
    .attr("type","fractalNoise")
    .attr("baseFrequency","5")
    .attr("numOctaves","10")
    .attr("seed","10")
    .attr("stitchTiles","noStitch")
    .attr("result","noise");

var feDisplacementMap = filterSun.append("feDisplacementMap")
    .attr("in","SourceGraphic")
    .attr("in2","noise")
    .attr("scale","20")
    .attr("xChannelSelector","R")
    .attr("yChannelSelector","B")
    .attr("filterUnits","userSpaceOnUse");

var animate = filterSun.append("animate")
    .attr("xlink:href","#turbolence")
    .attr("attributeName","baseFrequency")
    .attr("dur","100")
    .attr("keyTimes","0;0.5;1")
    .attr("values","5;5.1;5;")
    .attr("repeatCount","indefinite");
      
svg.append("circle")
    .attr("filter", "url(#sunFilter)")    
    .style('stroke', "none")
    .style('fill', "#FF6347")
    .attr("r", 21)
    .attr("cx", 260)
    .attr("cy", 275);

// Text output

svg.append("text")  
    .attr("x", 510)
    .attr("y", 136)
    .attr("text-anchor", "left")
    .style("font-size", "14px")
    .style("font-family", "Helvetica")
    .style("font-weight", "bold")
    .style("fill", "#FF6347")
    .text("Orrery");

svg.append("text")  
    .attr("x", 510)
    .attr("y", 152)
    .attr("text-anchor", "left")
    .style("font-size", "14px")
    .style("font-family", "Helvetica")
    .style("font-weight", "normal")
    .style("fill", "#2E8B57")
    .text("Kepler's Equation of Time");

svg.append("line")
    .attr("x1", 510)
    .attr("x2", 670)
    .attr("y1", 159)
    .attr("y2", 159)
    .style("stroke", "#007FFF")
    .style("stroke-width", "2px")
    .style("stroke-linecap", "round");

svg.append("text")  
    .attr("x", 510)
    .attr("y", 175)
    .attr("text-anchor", "left")
    .style("font-size", "14px")
    .style("font-family", "Helvetica")
    .style("font-weight", "normal")
    .style("fill", "#FFA54F")
    .text("UT - Julian Date: " + jd);

svg.append("text")  
    .attr("x", 510)
    .attr("y", 190)
    .attr("text-anchor", "left")
    .style("font-size", "14px")
    .style("font-family", "Helvetica")
    .style("font-weight", "normal")
    .style("fill", "#FFA54F")
    .text("UT - Years - J2000: " + jdy);

svg.append("text")  
    .attr("x", 510)
    .attr("y", 205)
    .attr("text-anchor", "left")
    .style("font-size", "14px")
    .style("font-family", "Helvetica")
    .style("font-weight", "normal")
    .style("fill", "#FFA54F")
    .text("UT - Days - J2000: " + jday);

svg.append("text")  
    .attr("x", 510)
    .attr("y", 220)
    .attr("text-anchor", "left")
    .style("font-size", "14px")
    .style("font-family", "Helvetica")
    .style("font-weight", "normal")
    .style("fill", "#FFA54F")
    .text("UT - /s - J2000: " + Math.round(jsec));

svg.append("text")  
    .attr("x", 510)
    .attr("y", 235)
    .attr("text-anchor", "left")
    .style("font-size", "14px")
    .style("font-family", "Helvetica")
    .style("font-weight", "normal")
    .style("fill", "#2E8B57")
    .text("Heliocentric Mean Longitude in Degrees");

svg.append("line")
    .attr("x1", 510)
    .attr("x2", 757)
    .attr("y1", 242)
    .attr("y2", 242)
    .style("stroke", "#007FFF")
    .style("stroke-width", "2px")
    .style("stroke-linecap", "round");

svg.append("text")  
    .attr("x", 510)
    .attr("y", 261)
    .attr("text-anchor", "left")
    .style("font-size", "14px")
    .style("font-family", "Helvetica")
    .style("font-weight", "normal")
    .style("fill", "#F618CC")
    .text("Mercury: " + Mercury + "°");

svg.append("text")  
    .attr("x", 510)
    .attr("y", 277)
    .attr("text-anchor", "left")
    .style("font-size", "14px")
    .style("font-family", "Helvetica")
    .style("font-weight", "normal")
    .style("fill", "#2E8B57")
    .text("Venus: " + Venus + "°");

svg.append("text")  
    .attr("x", 510)
    .attr("y", 292)
    .attr("text-anchor", "left")
    .style("font-size", "14px")
    .style("font-family", "Helvetica")
    .style("font-weight", "normal")
    .style("fill", "#007FFF")
    .text("Earth: " + Earth + "°");

svg.append("text")  
    .attr("x", 510)
    .attr("y", 307)
    .attr("text-anchor", "left")
    .style("font-size", "14px")
    .style("font-family", "Helvetica")
    .style("font-weight", "normal")
    .style("fill", "white")
    .text("Moon: " + Moon + "°");

svg.append("text")  
    .attr("x", 510)
    .attr("y", 322)
    .attr("text-anchor", "left")
    .style("font-size", "14px")
    .style("font-family", "Helvetica")
    .style("font-weight", "normal")
    .style("fill", "#FF0000")
    .text("Mars: " + Mars + "°");

svg.append("text")  
    .attr("x", 510)
    .attr("y", 337)
    .attr("text-anchor", "left")
    .style("font-size", "14px")
    .style("font-family", "Helvetica")
    .style("font-weight", "normal")
    .style("fill", "#BE688B")
    .text("Jupiter: " + Jupiter + "°");

svg.append("text")  
    .attr("x", 510)
    .attr("y", 352.5)
    .attr("text-anchor", "left")
    .style("font-size", "14px")
    .style("font-family", "Helvetica")
    .style("font-weight", "normal")
    .style("fill", "#FFA54F")
    .text("Saturn: " + Saturn + "°");

svg.append("text")  
    .attr("x", 510)
    .attr("y", 368)
    .attr("text-anchor", "left")
    .style("font-size", "14px")
    .style("font-family", "Helvetica")
    .style("font-weight", "normal")
    .style("fill", "#F67F40")
    .text("Uranus: " + Uranus + "°");

svg.append("text")  
    .attr("x", 510)
    .attr("y", 384)
    .attr("text-anchor", "left")
    .style("font-size", "14px")
    .style("font-family", "Helvetica")
    .style("font-weight", "normal")
    .style("fill", "#5FC6C6")
    .text("Neptune: " + Neptune + "°");

svg.append("text")  
    .attr("x", 510)
    .attr("y", 399)
    .attr("text-anchor", "left")
    .style("font-size", "14px")
    .style("font-family", "Helvetica")
    .style("font-weight", "normal")
    .style("fill", "#B6F131")
    .text("Pluto: " + Pluto + "°");

svg.append("text")  
    .attr("x", 510)
    .attr("y", 425)
    .attr("text-anchor", "left")
    .style("font-size", "14px")
    .style("font-family", "Helvetica")
    .style("font-weight", "normal")
    .style("fill", "#2E8B57")
    .text("Powered by D3.js");

</script>
<script>

// wall size
var canvasSize = 280;

// How many Particles 
var Particles = 3000;

// Create an instance of Two.js
var sky = document.getElementById("particlemap");

var params = { width: canvasSize, 
               height: canvasSize,
               fullscreen: false,
               type: Two.Types.svg };
var two = new Two(params).appendTo(sky);

// asteroid belt particle map ----------------------------

var outerRadius = 128;
var innerRadius = 112;
var xc = two.width/2;
var yc = two.height/2; 

// Determine if anything is outside of the canvas
function isOutside(x1, y1) {
  return x1 < 0 || x1 > two.width || y1 < 0 || y1 > two.height;
}

// Is the point inside of an invisible set of two circles ?
function insideRing(x1, y1) {
  return (Math.pow(x1 - xc, 2) + Math.pow(y1 - yc, 2) < Math.pow(outerRadius, 2)) && (Math.pow(x1 - xc, 2) + Math.pow(y1 - yc, 2) > Math.pow(innerRadius, 2));
}

// Return a random integer between min and max, inclusive
function randBetween(min, max) {
  min = Math.ceil(min);
  max = Math.floor(max);
  return Math.floor(Math.random() * (max - min + 1)) + min;
}

// Input: an integer between 1 and 4, corresponding to the 4 out-of-bounds areas
// Output: a (constrained) random point out-of-bounds of the box

// This is where the circles will originate
function genCoords(region) {
  if (region == 1) { // West
    return [-10, randBetween(two.height * .2, two.height * .8)];
  } else if (region == 2) { // North
    return [randBetween(two.width * .2, two.width * .8), -10]
  } else if (region == 3) { // East
    return [two.width + 10, randBetween(two.height * .2, two.height * .8)];
  } else { // South
    return [randBetween(two.width * .2, two.width * .8), two.height + 10];
  }
}

// Input the region used to generate the starting coords
// Output the return of genCoords for some region besides that one

// This will be where the circles will be moving to
function genTarget(region) {
  let regions = [1, 2, 3, 4];
  regions.splice(region - 1, 1);
  return genCoords(regions[randBetween(0, 2)]);
}

// Circle constructor
function Circ() {

  // Determine the circles origin and destination
  
  this.makeCoords = () => {
    this.region = randBetween(1, 4);
    this.coords = genCoords(this.region);
    this.targetCoords = genTarget(this.region);
    this.x = this.coords[0];
    this.y = this.coords[1];
    this.target = {
      x: this.targetCoords[0],
      y: this.targetCoords[1]
    };
  }

  // Make it pretty
  this.colorize = () => {
    this.c.fill = randBetween(1, 2) == 1 ? "white" : "rgb("+randBetween(150, 255)+","+0+","+randBetween(150, 255)+")";
  }

  // Create it!
  this.setup = () => {
  
    // Create it somewhere!
    this.makeCoords();
    // Make the circle svg! (It has eight vertices!!!) Third param = radius.
    this.c = two.makeCircle(this.x, this.y, 0.5);
    this.c.noStroke();
    
    // Make it pretty!
    this.colorize();
    this.vel = .0025;
    
    // This will be used later to determine whether to remove it
    this.beenInside = false;
  }

  this.setup();

  this.realign = () => {
    this.makeCoords();
    this.c.translation.set(this.x, this.y);
    this.colorize();
    this.beenInside = false;
  }
}

// Array for use in the animation bits
var circs = [];

// Add a bunch of circles
for (var i = 0; i<= Particles; i++) {
  circs.push(new Circ());
}

two.bind("update", function(frameCount, timeDelta) {
  var ct = circs.length;

  // Speed 
  for (var i = 0, l = circs.length; i < l; i++) {
    let cir = circs[i];
    if (insideRing(cir.c.translation.x, cir.c.translation.y)) {
      cir.insideRing = true;
      cir.oldVel = cir.vel;
      cir.vel = .0005;
    } else {
      cir.insideRing = false;
      cir.vel = .005;
    }
  }
    
  for (var i = 0, l = circs.length; i < l; i++) {
  
    // Move each circle a bit to the left/right and up/down
    
    let cir = circs[i];
    let xDelta = cir.vel * (cir.target.x - cir.x);
    let yDelta = cir.vel * (cir.target.y - cir.y);

    xDelta += (randBetween(-5, 5) / 50);
    yDelta += (randBetween(-5, 5) / 50);

    cir.c.translation.set(cir.c.translation.x + xDelta,  cir.c.translation.y + yDelta);
  }    
 
  for (var i = 0, l = circs.length; i < l; i++) {
  
    // If the circle has not been inside and currently is inside,
    // Mark it as having been inside
     
    let cir = circs[i]; 
    if (!cir.beenInside && isOutside(cir.c.translation.x, cir.c.translation.y) == false) {
      cir.beenInside = true;
    }
  }
  // If the circle has been inside and no longer is...
  for (var i = 0, l = circs.length; i < l; i++) {
    let cir = circs[i];
    if (cir.beenInside && isOutside(cir.c.translation.x, cir.c.translation.y)) {
    
      // ... move the circle to a new starting position.
      cir.realign();
    }
  }
 // showtime :-) 
});
two.play();

</script>
</body>
</html>