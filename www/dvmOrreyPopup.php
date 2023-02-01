<?php
include('dvmCombinedData.php');
date_default_timezone_set($TZ);
error_reporting(0);
header('Content-type: text/html; charset=utf-8');
echo "<body style='background-color:#292E35'>";
?>

<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Orrery</title>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=yes">
<link rel="stylesheet" href="css/orrery.css">
    <script src="js/two.js"></script>
    
</head>

<body>
 
<div id="particlemap" width="280" height="280" style="position:relative; top: 135px; left: -20px;"></div>
<div id="orrery" width="780" height="500" style="position:relative; top: -5px; left: -270px;"></div>

<script>

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

var canvasWidth = 780;
var canvasHeight = 500;

// Create an instance of Two.js
var sky = document.getElementById("orrery");

var params = { width: canvasWidth, 
			   height: canvasHeight,
			   fullscreen: false,
			   type: Two.Types.svg };
var two = new Two(params).appendTo(sky);

// Data --

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

// Data output Text

var Title = "Orrery";
var title = new Two.Text(Title, 500, 98);
  	title.size = 14;
  	title.weight = "bold";
  	title.family = "Helvetica";
  	title.fill = "rgba(255,99,71,1)"; // tomato
  	title.alignment = "left";
  	
var Sub_Title = "Kepler's Equation of Time";
var subt = new Two.Text(Sub_Title, 500, 115);
  	subt.size = 14;
  	subt.weight = "normal";
  	subt.family = "Helvetica";
  	subt.fill = "rgba(46,139,87,1)"; // earth green
  	subt.alignment = "left";

var underline = two.makeLine(500, 130, 760,  130);
	underline.noFill();
  	underline.stroke = "rgba(0,127,255,1)"; // arch blue
  	underline.linewidth = 1.75;
  	underline.cap = "round";
  	  	
var julian = "UT - Julian Date: " + jd;
var jul = new Two.Text(julian, 500, 145);
  	jul.size = 14;
  	jul.weight = "normal";
  	jul.family = "Helvetica";
  	jul.fill = "#FFA54F"; // tan
  	jul.alignment = "left";

var julianY = "UT - Years - J2000: " + jdy;
var julY = new Two.Text(julianY, 500, 163);
  	julY.size = 14;
  	julY.weight = "normal";
  	julY.family = "Helvetica";
  	julY.fill = "#FFA54F"; // tan
  	julY.alignment = "left";
  	
var julianD = "UT - Days - J2000: " + jday;
var julD = new Two.Text(julianD, 500, 181);
  	julD.size = 14;
  	julD.weight = "normal";
  	julD.family = "Helvetica";
  	julD.fill = "#FFA54F"; // tan
  	julD.alignment = "left";
  	
var julianS = "UT - /s - J2000: " + Math.round(jsec);
var julS = new Two.Text(julianS, 500, 199);
  	julS.size = 14;
  	julS.weight = "normal";
  	julS.family = "Helvetica";
  	julS.fill = "#FFA54F"; // tan
  	julS.alignment = "left";     	

var mercury = "Mercury: " + Mercury + "°";
var mer = new Two.Text(mercury, 500, 217);
  	mer.size = 14;
  	mer.weight = "normal";
  	mer.family = "Helvetica";
  	mer.fill = "#F618CC";
  	mer.alignment = "left";

var venus = "Venus: " + Venus + "°";
var ven = new Two.Text(venus, 500, 233);
  	ven.size = 14;
  	ven.weight = "normal";
  	ven.family = "Helvetica";
  	ven.fill = "#2E8B57";
  	ven.alignment = "left";
  	
var earth = "Earth: " + Earth + "°";
var ear = new Two.Text(earth, 500, 249);
  	ear.size = 14;
  	ear.weight = "normal";
  	ear.family = "Helvetica";
  	ear.fill = "#007FFF";
  	ear.alignment = "left";
  	
var moon = "Moon: " + Moon + "°";
var moo = new Two.Text(moon, 500, 265);
  	moo.size = 14;
  	moo.weight = "normal";
  	moo.family = "Helvetica";
  	moo.fill = "rgba(255,255,255,1)";
  	moo.alignment = "left";  	
  	
var mars = "Mars: " + Mars + "°";
var mar = new Two.Text(mars, 500, 280);
  	mar.size = 14;
  	mar.weight = "normal";
  	mar.family = "Helvetica";
  	mar.fill = "#FF0000";
  	mar.alignment = "left";
  	
var jupiter = "Jupiter: " + Jupiter + "°";
var jup = new Two.Text(jupiter, 500, 296);
  	jup.size = 14;
  	jup.weight = "normal";
  	jup.family = "Helvetica";
  	jup.fill = "#BE688B";
  	jup.alignment = "left";
  	
var saturn = "Saturn: " + Saturn + "°";
var sat = new Two.Text(saturn, 500, 312);
  	sat.size = 14;
  	sat.weight = "normal";
  	sat.family = "Helvetica";
  	sat.fill = "#FFA54F";
  	sat.alignment = "left";   	
  	
var uranus = "Uranus: " + Uranus + "°";
var uran = new Two.Text(uranus, 500, 328);
  	uran.size = 14;
  	uran.weight = "normal";
  	uran.family = "Helvetica";
  	uran.fill = "#F67F40";
  	uran.alignment = "left";

var neptune = "Neptune: " + Neptune + "°";
var nep = new Two.Text(neptune, 500, 344); 
  	nep.size = 14;
  	nep.weight = "normal";
  	nep.family = "Helvetica";
  	nep.fill = "#5FC6C6";
  	nep.alignment = "left";

var pluto = "Pluto: " + Pluto + "°";
var plu = new Two.Text(pluto, 500, 360);
  	plu.size = 14;
  	plu.weight = "normal";
  	plu.family = "Helvetica";
  	plu.fill = "#B6F131";
  	plu.alignment = "left";

var PB = "Powered by Two.js";
var pb = new Two.Text(PB, 500, 390); 
  	pb.size = 14;
  	pb.weight = "normal";
  	pb.family = "Helvetica";
  	pb.fill = "rgba(46,139,87,1)"; // earth green
  	pb.alignment = "left";
	
	two.scene.add(title, subt, underline, jul, julY, julD, julS, mer, ven, mar, moo, ear, jup, sat, uran, nep, plu, pb);  

// mercury ----------------------------------------------- 
  
var mercuryAngle = 0 - Mercury,
    distance   = 6,
    radius     = 4,
    padding    = 210,
    orbit      = 40,
    offset     = orbit + padding,
    orbits     = two.makeGroup();

function getPositions(angle, orbit) {
    return {
        x: Math.cos(angle * Math.PI / 180) * orbit,
        y: Math.sin(angle * Math.PI / 180) * orbit
    };
}

var mercuryOrbit = two.makeCircle(offset, offset, orbit);
mercuryOrbit.noFill();
mercuryOrbit.linewidth = 120;
mercuryOrbit.stroke = "#292E35";
orbits.add(mercuryOrbit);

var mercuryOrbit = two.makeCircle(offset, offset, orbit + 138);
mercuryOrbit.noFill();
mercuryOrbit.linewidth = 75;
mercuryOrbit.stroke = "#292E35"; // "#292E35"
orbits.add(mercuryOrbit);

var mercuryOrbit = two.makeCircle(offset, offset, orbit);
mercuryOrbit.noFill();
mercuryOrbit.linewidth = 1;
mercuryOrbit.stroke = "#424140";
orbits.add(mercuryOrbit);
 
var pos = getPositions(mercuryAngle, orbit),
    mercury = two.makeCircle(pos.x + offset, pos.y + offset, radius);
    
var line = two.makeLine(pos.x + offset, pos.y + offset, 250,250);
line.linewidth = 1;
line.fill = "#424140";
line.stroke = "#424140";

var pos = getPositions(mercuryAngle, orbit),
    mercury = two.makeCircle(pos.x + offset, pos.y + offset, radius);
 
mercury.stroke = "#F618CC";
mercury.linewidth = 1;
mercury.fill = "#F618CC";

// venus -----------------------------------------------------------

var venusAngle = 0 - Venus,
    distance   = 6,
    radius     = 4,
    padding    = 190,
    orbit      = 60,
    offset     = orbit + padding,
    orbits     = two.makeGroup();

function getPositions(angle, orbit) {
    return {
        x: Math.cos(angle * Math.PI / 180) * orbit,
        y: Math.sin(angle * Math.PI / 180) * orbit
    };
}

var venusOrbit = two.makeCircle(offset, offset, orbit);
venusOrbit.noFill();
venusOrbit.linewidth = 1;
venusOrbit.stroke = "#424140";
orbits.add(venusOrbit);
 
var pos = getPositions(venusAngle, orbit),
    venus = two.makeCircle(pos.x + offset, pos.y + offset, radius);
    
var line = two.makeLine(pos.x + offset, pos.y + offset, 250,250);
line.linewidth = 1;
line.fill = "#424140";
line.stroke = "#424140";

var pos = getPositions(venusAngle, orbit),
    venus = two.makeCircle(pos.x + offset, pos.y + offset, radius);
 
venus.stroke = "#2E8B57";
venus.linewidth = 1;
venus.fill = "#2E8B57";

// mars -----------------------------------------------------------

var marsAngle = 0 - Mars,
    distance   = 6,
    radius     = 4,
    padding    = 150,
    orbit      = 100,
    offset     = orbit + padding,
    orbits     = two.makeGroup();

function getPositions(angle, orbit) {
    return {
        x: Math.cos(angle * Math.PI / 180) * orbit,
        y: Math.sin(angle * Math.PI / 180) * orbit
    };
}

var marsOrbit = two.makeCircle(offset, offset, orbit);
marsOrbit.noFill();
mercuryOrbit.linewidth = 1;
marsOrbit.stroke = "#424140";
orbits.add(marsOrbit);
 
var pos = getPositions(marsAngle, orbit),
    mars = two.makeCircle(pos.x + offset, pos.y + offset, radius);
    
var line = two.makeLine(pos.x + offset, pos.y + offset, 250,250);
line.linewidth = 1;
line.fill = "#424140";
line.stroke = "#424140";    

var pos = getPositions(marsAngle, orbit),
    mars = two.makeCircle(pos.x + offset, pos.y + offset, radius);
 
mars.stroke = "#FF0000";
mars.linewidth = 1;
mars.fill = "#FF0000";

// jupiter --------------------------------------------------------

var jupiterAngle = 0 - Jupiter,
    distance   = 6,
    radius     = 10,
    padding    = 110,
    orbit      = 140,
    offset     = orbit + padding,
    orbits     = two.makeGroup();

function getPositions(angle, orbit) {
    return {
        x: Math.cos(angle * Math.PI / 180) * orbit,
        y: Math.sin(angle * Math.PI / 180) * orbit
    };
}

var jupiterOrbit = two.makeCircle(offset, offset, orbit);
jupiterOrbit.noFill();
jupiterOrbit.linewidth = 1;
jupiterOrbit.stroke = "#424140";
orbits.add(jupiterOrbit);
 
var pos = getPositions(jupiterAngle, orbit),
    jupiter = two.makeCircle(pos.x + offset, pos.y + offset, radius);
    
var line = two.makeLine(pos.x + offset, pos.y + offset, 250,250);
line.linewidth = 1;
line.fill = "#424140";
line.stroke = "#424140";

var pos = getPositions(jupiterAngle, orbit),
    jupiter = two.makeCircle(pos.x + offset, pos.y + offset, radius);
 
jupiter.stroke = "#BE688B";
jupiter.linewidth = 1;
jupiter.fill = "#BE688B";

// saturn --------------------------------------------------------

var saturnAngle = 0 - Saturn,
    distance   = 6,
    radius     = 6.5,
    padding    = 90,
    orbit      = 160,
    offset     = orbit + padding,
    orbits     = two.makeGroup();

function getPositions(angle, orbit) {
    return {
        x: Math.cos(angle * Math.PI / 180) * orbit,
        y: Math.sin(angle * Math.PI / 180) * orbit
    };
}

var saturnOrbit = two.makeCircle(offset, offset, orbit);
saturnOrbit.noFill();
saturnOrbit.linewidth = 1;
saturnOrbit.stroke = "#424140";
orbits.add(saturnOrbit);
 
var pos = getPositions(saturnAngle, orbit),
    saturn = two.makeCircle(pos.x + offset, pos.y + offset, radius);
    
var line = two.makeLine(pos.x + offset, pos.y + offset, 250,250);
line.stroke = "#424140";
line.linewidth = 1;
line.fill = "#424140";
  
var pos = getPositions(saturnAngle, orbit),
    saturn = two.makeCircle(pos.x + offset, pos.y + offset, radius);
 
saturn.stroke = "#FFA54F";
saturn.linewidth = 1;
saturn.fill = "#FFA54F";

var saturnRing = two.makeCircle(saturn.translation.x, saturn.translation.y, radius + 0 + distance);
saturnRing.noFill();
saturnRing.linewidth = 5;
saturnRing.stroke = "#FFA54F";


var saturnRing = two.makeCircle(saturn.translation.x, saturn.translation.y, radius + 5 + distance);
saturnRing.noFill();
saturnRing.linewidth = 1.5;
saturnRing.stroke = "#FFA54F";

// uranus ----------------------------------------------------------
 
var uranusAngle = 0 - Uranus,
    distance   = 6,
    radius     = 6,
    padding    = 70,
    orbit      = 180,
    offset     = orbit + padding,
    orbits     = two.makeGroup();

function getPositions(angle, orbit) {
    return {
        x: Math.cos(angle * Math.PI / 180) * orbit,
        y: Math.sin(angle * Math.PI / 180) * orbit
    };
}

var uranusOrbit = two.makeCircle(offset, offset, orbit);
uranusOrbit.noFill();
uranusOrbit.linewidth = 1;
uranusOrbit.stroke = "#424140";
orbits.add(uranusOrbit);
 
var pos = getPositions(uranusAngle, orbit),
    uranus = two.makeCircle(pos.x + offset, pos.y + offset, radius);
    
var line = two.makeLine(pos.x + offset, pos.y + offset, 250,250);
line.linewidth = 1;
line.fill = "#424140";
line.stroke = "#424140";
   
var pos = getPositions(uranusAngle, orbit),
    uranus = two.makeCircle(pos.x + offset, pos.y + offset, radius);
 
uranus.stroke = "#F67F40";
uranus.linewidth = 1;
uranus.fill = "#F67F40";

// neptune ------------------------------------------------------

var neptuneAngle = 0 - Neptune,
    distance   = 6,
    radius     = 6,
    padding    = 50,
    orbit      = 200,
    offset     = orbit + padding,
    orbits     = two.makeGroup();

function getPositions(angle, orbit) {
    return {
        x: Math.cos(angle * Math.PI / 180) * orbit,
        y: Math.sin(angle * Math.PI / 180) * orbit
    };
}

var neptuneOrbit = two.makeCircle(offset, offset, orbit);
neptuneOrbit.noFill();
neptuneOrbit.linewidth = 1;
neptuneOrbit.stroke = "#424140";
orbits.add(neptuneOrbit);
 
var pos = getPositions(neptuneAngle, orbit),
    neptune = two.makeCircle(pos.x + offset, pos.y + offset, radius);
    
var line = two.makeLine(pos.x + offset, pos.y + offset, 250,250);
line.linewidth = 1;
line.fill = "#424140";
line.stroke = "#424140";    

var pos = getPositions(neptuneAngle, orbit),
    neptune = two.makeCircle(pos.x + offset, pos.y + offset, radius);
 
neptune.stroke = "#5FC6C6";
neptune.linewidth = 1;
neptune.fill = "#5FC6C6";

// pluto --------------------------------------------------------------

var plutoAngle = 0 - Pluto,
    distance   = 6,
    radius     = 3,
    padding    = 30,
    orbit      = 220,
    offset     = orbit + padding,
    orbits     = two.makeGroup();

function getPositions(angle, orbit) {
    return {
        x: Math.cos(angle * Math.PI / 180) * orbit,
        y: Math.sin(angle * Math.PI / 180) * orbit
    };
}

var plutoOrbit = two.makeCircle(offset, offset, orbit);
plutoOrbit.noFill();
plutoOrbit.linewidth = 1;
plutoOrbit.stroke = "#424140";
orbits.add(plutoOrbit);
 
var pos = getPositions(plutoAngle, orbit),
    pluto = two.makeCircle(pos.x + offset, pos.y + offset, radius);
    
var line = two.makeLine(pos.x + offset, pos.y + offset, 250,250);
line.linewidth = 1;
line.fill = "#424140";
line.stroke = "#424140";   
    
var pos = getPositions(plutoAngle, orbit),
    pluto = two.makeCircle(pos.x + offset, pos.y + offset, radius);
       
pluto.stroke = "#B6F131";
pluto.linewidth = 1;
pluto.fill = "#B6F131";

// earth + moon -----------------------------------------------------

var earthAngle = 0 - Earth,
    moonAngle  = 0 - Moonx,
    distance   = 6,
    radius     = 5.5,
    padding    = 170,
    orbit      = 80,
    offset     = orbit + padding,
    orbits     = two.makeGroup();

function getPositions(angle, orbit) {
    return {
        x: Math.cos(angle * Math.PI / 180) * orbit,
        y: Math.sin(angle * Math.PI / 180) * orbit
    };
}

var earthOrbit = two.makeCircle(offset, offset, orbit);
earthOrbit.noFill();
earthOrbit.linewidth = 1;
earthOrbit.stroke = "#424140";
orbits.add(earthOrbit);
 
var pos = getPositions(earthAngle, orbit),
    earth = two.makeCircle(pos.x + offset, pos.y + offset, radius);
    
var line = two.makeLine(pos.x + offset, pos.y + offset, 250,250);
line.linewidth = 1;
line.fill = "#424140";
line.stroke = "#424140";

var pos = getPositions(moonAngle, radius + distance), 
    moon = two.makeCircle(earth.translation.x + pos.x, earth.translation.y + pos.y, radius / 4);
   
var line = two.makeLine(earth.translation.x + pos.x, earth.translation.y + pos.y, earth.translation.x, earth.translation.y );
line.linewidth = 1;
line.fill = "#424140";
line.stroke = "#424140";

var pos = getPositions(earthAngle, orbit),
    earth = two.makeCircle(pos.x + offset, pos.y + offset, radius);
 
earth.stroke = "#007FFF";
earth.linewidth = 1;
earth.fill = "#007FFF";

var moonOrbit = two.makeCircle(earth.translation.x, earth.translation.y, radius + distance);
moonOrbit.noFill();
moonOrbit.linewidth = 1;
moonOrbit.stroke = "#424140";
orbits.add(earthOrbit);
 
var pos = getPositions(moonAngle, radius + distance), 
    moon = two.makeCircle(earth.translation.x + pos.x, earth.translation.y + pos.y, radius / 4);
 
moon.stroke = "white";
moon.linewidth = 1;
moon.fill = "white";

// sun -------------------------------------------------------

var sunAngle = 200,
    distance   = 6,
    radius     = 22,
    padding    = 250,
    orbit      = 1,
    offset     = orbit + padding,
    orbits     = two.makeGroup();

function getPositions(angle, orbit) {
    return {
        x: Math.cos(angle * Math.PI / 180) * orbit,
        y: Math.sin(angle * Math.PI / 180) * orbit
    };
}

var sunOrbit = two.makeCircle(offset, offset, orbit);
sunOrbit.noFill();
sunOrbit.linewidth = 0;
sunOrbit.stroke = "#424140";
orbits.add(sunOrbit);



/* 
var pos = getPositions(sunAngle, orbit),
    sun = two.makeCircle(pos.x + offset, pos.y + offset, radius);
 
sun.stroke = "#FF6347";
sun.linewidth = 1;
sun.fill = "#FF6347";
*/

orbits.visible = true;
 
 // showtime :-)
two.update();

/*
// Sun animation
var circle = two.makeCircle(0, 0, 18);
circle.fill = "#F67F40";
circle.fill = "#F67F40";

var circle2 = two.makeCircle(0, 0, 11);
circle2.stroke = "#FF6347";
circle2.fill = "#FF6347";

var circle3 = two.makeCircle(0, 0, 5);
circle3.stroke = "#FF0000";
circle3.fill = "#FF0000";
 

var group = two.makeGroup(circle, circle2, circle3);
group.translation.set(pos.x + offset, pos.y + offset);
group.scale = 0;
group.noStroke();

two.bind("update", function(frameCount) {

  if (group.scale > 0.9999) {
    group.scale = group.rotation = 0;
  }
  var t = (1.01 - group.scale) * 0.125;
  group.scale += t;
  group.rotation += t * 4 * Math.PI;
});
two.play();
*/

</script>


</div>

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
			   type: Two.Types.canvas };
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
    let xDelt = cir.vel * (cir.target.x - cir.x);
    let yDelt = cir.vel * (cir.target.y - cir.y);

    xDelt += (randBetween(-5, 5) / 50);
    yDelt += (randBetween(-5, 5) / 50);

    cir.c.translation.set(cir.c.translation.x + xDelt,  cir.c.translation.y + yDelt);
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

 <svg height="50" width="50" style="position:relative; top: -30px; left: -20px;">
  <defs>
    <filter id="distortionFilter">
      <feTurbulence id="turbolence" type="fractalNoise" baseFrequency="5" numOctaves="10" seed="10" stitchTiles="noStitch" x="0%" y="0%" width="100%" height="100%" result="noise"></feTurbulence>
      <feDisplacementMap in="SourceGraphic" in2="noise" scale="20" xChannelSelector="R" yChannelSelector="B" x="0%" y="0%" width="100%" height="100%" filterUnits="userSpaceOnUse"></feDisplacementMap>
      <animate xlink:href="#turbolence" attributeName="baseFrequency" dur="20s" keyTimes="0;0.5;1" values="5;5.1;5;" repeatCount="indefinite"></animate>
    </filter>
  </defs>
  <circle cx="25" cy="25" r="20" fill="#FF6347" stroke="none" filter="url(#distortionFilter)"></circle>
</svg>


</body>
</html>
