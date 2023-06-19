<?php

/*
Astronomical clock
 
This clock has been translated from an old lua script,
coded by a good friend of mine named Paramvir Likhari.

Based on the Orloj clock which can be found in the City of Prague,
it acts like a precision instrument when fed with the correct data.
This is the proper way to display the sun and moon going around a circle.

The sun posision is spot on, the moon is accurate to within 3-4 arc minutes
which is extremely good !
The data has been pre-calculated using the python-ephem library
and parsed using json.

I have a small TODO.

No.1
The sun and moon should animate with a one second tick
to show you how the Kaleidoscope sun works.
The moon animation is not quite so elaborate but
fits nicely in the sun at the moment of a new moon.
(the duration is about an hour or so)

I would be eternally grateful for any help with these functions.
I hope you like the clock enjoy the nice graphics.

I am also open to suggestions to improve the code in any way.

Cheers Sean 

Created by Sean Balfour in Dresden March 2022
contact: seanbalfourdresden@googlemail.com

*/
include('dvmCombinedData.php');
date_default_timezone_set($TZ);
error_reporting(0);
header('Content-type: text/html; charset = utf-8');
echo "<body style='background-color:#292E35'>";
?>

<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Astroclock</title>
<meta name="viewport" content="width = device-width, initial-scale = 1, shrink-to-fit = yes">
<link rel="stylesheet" href="css/astroclock.css">
</head>
<script src="js/two.js"></script>

<style>
@font-face {
    font-family: 'AstroDotBasic';
    src: url('css/fonts/AstroDotBasic.ttf') format('truetype');
}
</style>

<body>

<!--svg Sun Ring --> 
<svg width="780" height="500" style="position:relative; top: 25px; left: -130px; border:0px solid #007FFF;"> 
  <defs>
    <linearGradient id="grad1" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="rgba(255,255,0,1)" /> <!--yellow-->
      <stop offset="50%" stop-color="rgba(255,0,0,1)" />  <!--red-->
      <stop offset="100%" stop-color="rgba(0,0,255,1)" /> <!--blue-->
    </linearGradient>
  </defs>
  <g fill="none">
    <circle cx="250" cy="250" r="185" stroke="url(#grad1)" stroke-width="15" />
  </g>
</svg>
<div id="astroclock" width="780" height="500" style="position:relative; top: -225px; left: -520px; border:0px solid #007FFF;">

<script>

// The svg sun ring is started before anything else !

// refresh the page every 60 seconds
window.setInterval('refresh()', 60000); 	
    function refresh() {
        window.location.reload();
    }
 
    var eclipticWidth = 0.75;
    
    var color_M1 = "rgba(41,46,53,1)";
	var color_M = "rgba(255,255,255,1)";
		
	
function toDegrees(x) {
  return x * (180.0 / Math.PI);
}

function toRadians(x) {
  return x * (Math.PI / 180.0);
}

function toDegRad(x, f) { 
  return toRadians(x * f); 
}
		
	var hour_sun = <?php echo $alm["hour_sun"];?>; // correct value
		
	var hourSun = toDegrees(hour_sun); // convert to degrees
	 
	var sun_Ra = <?php echo $alm["sun_right_ascension"];?>; // correct value
			             
    var hour_moon = <?php echo $alm["hour_moon"];?>; // correct value
        
    var hourMoon = toDegrees(hour_moon); // convert to degress
    
    var hours_arc = <?php echo $alm["hour_sun"];?>; // correct value

	var lmst = (hourSun / 15.0) + (sun_Ra / 15.0); // correct value 
  
    var Lha = (lmst * 15.0) - 90.0; // correct value
            
    var hourAries = toRadians(Lha); // correct value
    
    // split some time strings and get them ready to be used as Radians angles
       
    var sr = "<?php echo $alm["sunrise"];?>"; // string
    
	let srarr = sr.split(":");
	let srhour = parseInt(srarr[0]);
	let srmin = parseInt(srarr[1]);
	var srmins = 360.0 / 60.0 * srmin;
	var sunrise = (360.0 / 24.0 * srhour + srmins / 24.0);
    
    let smt = "<?php echo $alm["sun_meridian_transit"];?>"; // string

	let smtarr = smt.split(":");
	let smthour = parseInt(smtarr[0]);
	let smtmin = parseInt(smtarr[1]);
	var smtmins = 360.0 / 60.0 * smtmin;
	var sun_meridian_transit = (360.0 / 24.0 * smthour + smtmins / 24.0);
	
	let ss = "<?php echo $alm["sunset"];?>"; // string
	
	let ssarr = ss.split(":");
	let sshour = parseInt(ssarr[0]);
	let ssmin = parseInt(ssarr[1]);
	var ssmins = 360.0 / 60.0 * ssmin;
	var sunset = (360.0 / 24.0 * sshour + ssmins / 24.0);
	
	let mr = "<?php echo $alm["moonrise"];?>"; // string
	
	let mrarr = mr.split(":");
	let mrhour = parseInt(mrarr[0]);
	let mrmin = parseInt(mrarr[1]);
	var mrmins = 360.0 / 60.0 * mrmin;
	var moonrise = (360.0 / 24.0 * mrhour + mrmins / 24.0);
	
	let mmt = "<?php echo $alm["moon_meridian_transit"];?>"; // string
	
	let mmtarr = mmt.split(":");
	let mmthour = parseInt(mmtarr[0]);
	let mmtmin = parseInt(mmtarr[1]);
	var mmtmins = 360.0 / 60.0 * mmtmin;
	var moon_meridian_transit = (360.0 / 24.0 * mmthour + mmtmins / 24.0);
	
	let ms = "<?php echo $alm["moonset"];?>"; // string
	
	let msarr = ms.split(":");
	let mshour = parseInt(msarr[0]);
	let msmin = parseInt(msarr[1]);
	var msmins = 360.0 / 60.0 * msmin;
	var moonset = (360.0 / 24.0 * mshour + msmins / 24.0);
	
	let ctr = "<?php echo $alm["civil_twilight_begin"];?>"; // string
	
	let ctrarr = ctr.split(":");
	let ctrhour = parseInt(ctrarr[0]);
	let ctrmin = parseInt(ctrarr[1]);
	var ctrmins = 360.0 / 60.0 * ctrmin;
	var civil_twilight_rise = (360.0 / 24.0 * ctrhour + ctrmins / 24.0);
	
	let cts = "<?php echo $alm["civil_twilight_end"];?>"; // string
	
	let ctsarr = cts.split(":");
	let ctshour = parseInt(ctsarr[0]);
	let ctsmin = parseInt(ctsarr[1]);
	var ctsmins = 360.0 / 60.0 * ctsmin;
	var civil_twilight_set = (360.0 / 24.0 * ctshour + ctsmins / 24.0);
	
	let ntr = "<?php echo $alm["nautical_twilight_begin"];?>"; // string
	
	let ntrarr = ntr.split(":");
	let ntrhour = parseInt(ntrarr[0]);
	let ntrmin = parseInt(ntrarr[1]);
	var ntrmins = 360.0 / 60.0 * ntrmin;
	var nautical_twilight_rise = (360.0 / 24.0 * ntrhour + ntrmins / 24.0);
	
	let nts = "<?php echo $alm["nautical_twilight_end"];?>"; // string
	
	let ntsarr = nts.split(":");
	let ntshour = parseInt(ntsarr[0]);
	let ntsmin = parseInt(ntsarr[1]);
	var ntsmins = 360.0 / 60.0 * ntsmin;
	var nautical_twilight_set = (360.0 / 24.0 * ntshour + ntsmins / 24.0);
	
    let atr = "<?php echo $alm["astronomical_twilight_begin"];?>"; // string
	
	let atrarr = atr.split(":");
	let atrhour = parseInt(atrarr[0]);
	let atrmin = parseInt(atrarr[1]);
	var atrmins = 360.0 / 60.0 * atrmin;
	var astro_twilight_rise = (360.0 / 24.0 * atrhour + atrmins / 24.0);
	
	let ats = "<?php echo $alm["astronomical_twilight_end"];?>"; // string
	
	let atsarr = ats.split(":");
	let atshour = parseInt(atsarr[0]);
	let atsmin = parseInt(atsarr[1]);
	var atsmins = 360.0 / 60.0 * atsmin;
	var astro_twilight_set = (360.0 / 24.0 * atshour + atsmins / 24.0);
	
		     
var canvasWidth = 780;
var canvasHeight = 500;

// Create an instance of Two.js
var skynet = document.getElementById("astroclock");

var params = { width: canvasWidth, 
			   height: canvasHeight,
			   fullscreen: false,
			   autostart: true,
			   type: Two.Types.svg };
var two = new Two(params).appendTo(skynet);

// Define clock elements
const radius = Math.min(two.width, two.height) * 0.30;
	const styles = {
  	size: radius * 0.08,
  	weight: "bold",
  	family: "Helvetica",
  	fill: "rgba(255,255,0,1)", // yellow
  	opacity: 1.0
};

// the first two rings after the svg sun ring
var blueRing = two.makeCircle(0, 0, 174);
blueRing.noFill();
blueRing.linewidth = 8;
blueRing.stroke = "rgba(42,86,147,1)"; // sky blue

// clock numbers background ring
var clockRing2 = two.makeCircle(0, 0, 154);
clockRing2.noFill();
clockRing2.linewidth = 32;
clockRing2.stroke = "rgba(9,70,62,1)"; // green

var clockRing = two.makeCircle(0, 0, 171);
clockRing.noFill();
clockRing.linewidth = 1.50;
clockRing.stroke = "rgba(255,99,71,1)"; // tomato


// loop Ticks outer Ring // gray
for (let i = 0; i < 120; i++) {

  var x = - 165.5 * Math.sin(i / 120 * Math.PI * 2);
  var y = + 165.5 * Math.cos(i / 120 * Math.PI * 2);
  var Ogx = x - 3 * Math.sin(i / 120 * Math.PI * 2);
  var Ogy = y + 3 * Math.cos(i / 120 * Math.PI * 2);
  
  var ticks1 = two.makeLine(x, y, Ogx, Ogy);
  
  ticks1.noFill();
  ticks1.stroke = "#888"; // gray
  ticks1.linewidth = 1.5;
  ticks1.cap = "round";
}

// loop Ticks inner Ring // gray
for (let i = 0; i < 120; i++) {

  var x = - 142.5 * Math.sin(i / 120 * Math.PI * 2);
  var y = + 142.5 * Math.cos(i / 120 * Math.PI * 2);
  var Igx = x - 3 * Math.sin(i / 120 * Math.PI * 2);
  var Igy = y + 3 * Math.cos(i / 120 * Math.PI * 2);
  
  var ticks2 = two.makeLine(x, y, Igx, Igy);
  
  ticks2.noFill();
  ticks2.stroke = "#888"; // gray
  ticks2.linewidth = 1.5;
  ticks2.cap = "round";
}

// loop hourly Ticks outer Ring // Red
for (let i = 0; i < 24; i++) {

  var x = - 165.5 * Math.sin(i / 24 * Math.PI * 2);
  var y = + 165.5 * Math.cos(i / 24 * Math.PI * 2);
  var Ox = x - 3 * Math.sin(i / 24 * Math.PI * 2);
  var Oy = y + 3 * Math.cos(i / 24 * Math.PI * 2);
  
  var ticks3 = two.makeLine(x, y, Ox, Oy);
  
  ticks3.noFill();
  ticks3.stroke = "rgba(255,0,0,1)"; // red
  ticks3.linewidth = 2.5;
  ticks3.cap = "round";
}

// loop hourly Ticks inner Ring // Red
for (let i = 0; i < 24; i++) {

  var x = - 142.5 * Math.sin(i / 24 * Math.PI * 2);
  var y = + 142.5 * Math.cos(i / 24 * Math.PI * 2);
  var Ix = x - 3 * Math.sin(i / 24 * Math.PI * 2);
  var Iy = y + 3 * Math.cos(i / 24 * Math.PI * 2);
  
  var ticks4 = two.makeLine(x, y, Ix, Iy);
  
  ticks4.noFill();
  ticks4.stroke = "rgba(255,0,0,1)"; // red
  ticks4.linewidth = 2.5;
  ticks4.cap = "round";
}

// loop to create the clock numbers
for (let i = 0; i < 24; i++) {

  const x = - 155 * Math.sin(i / 24 * Math.PI * 2);
  const y = + 155 * Math.cos(i / 24 * Math.PI * 2);
  const number = new Two.Text(i === 0 ? 0 : i, x, y, styles);

  number.position.set(x, y + 1);
  two.add(number);

}

 // various rise and set markers coverted to Radians
function Markers() {

var x = - 177 * Math.sin(toRadians(sunrise));
var y = + 177 * Math.cos(toRadians(sunrise));

var srx = x - 17 * Math.sin(toRadians(sunrise));
var sry = y + 17 * Math.cos(toRadians(sunrise));
var srise = two.makeLine(x, y, srx, sry);

	srise.noFill();
    srise.stroke = "rgba(255,99,71,1)"; // tomato
    srise.linewidth = 2.5;
    srise.cap = "round"; 

 
var x = - 177 * Math.sin(toRadians(sun_meridian_transit));
var y = + 177 * Math.cos(toRadians(sun_meridian_transit));

var smtx = x - 17 * Math.sin(toRadians(sun_meridian_transit));
var smty = y + 17 * Math.cos(toRadians(sun_meridian_transit));
var sm_t = two.makeLine(x, y, smtx, smty);

	sm_t.noFill();
    sm_t.stroke = "rgba(255,99,71,1)"; // tomato
    sm_t.linewidth = 2.5;
    sm_t.cap = "round";


var x = - 177 * Math.sin(toRadians(sunset));
var y = + 177 * Math.cos(toRadians(sunset));

var ssx = x - 17 * Math.sin(toRadians(sunset));
var ssy = y + 17 * Math.cos(toRadians(sunset));
var sset = two.makeLine(x, y, ssx, ssy);

	sset.noFill();
    sset.stroke = "rgba(255,99,71,1)"; // tomato
    sset.linewidth = 2.5;
    sset.cap = "round";
    
var x = - 177 * Math.sin(toRadians(moonrise || 0));
var y = + 177 * Math.cos(toRadians(moonrise || 0));

var mrx = x - 17 * Math.sin(toRadians(moonrise || 0));
var mry = y + 17 * Math.cos(toRadians(moonrise || 0));
var mrise = two.makeLine(x, y, mrx, mry);

	mrise.noFill();
    mrise.stroke = "rgba(255,255,255,1)"; // white
    mrise.linewidth = 2.5;
    mrise.cap = "round";   

var x = - 177 * Math.sin(toRadians(moon_meridian_transit || 0));
var y = + 177 * Math.cos(toRadians(moon_meridian_transit || 0));

var mmtx = x - 17 * Math.sin(toRadians(moon_meridian_transit || 0));
var mmty = y + 17 * Math.cos(toRadians(moon_meridian_transit || 0));
var mm_t = two.makeLine(x, y, mmtx, mmty);

	mm_t.noFill();
    mm_t.stroke = "rgba(255,255,255,1)"; // white
    mm_t.linewidth = 2.5;
    mm_t.cap = "round";
    
var x = - 177 * Math.sin(toRadians(moonset || 0));
var y = + 177 * Math.cos(toRadians(moonset || 0));

var msx = x - 17 * Math.sin(toRadians(moonset || 0));
var msy = y + 17 * Math.cos(toRadians(moonset || 0));
var mset = two.makeLine(x, y, msx, msy);

	mset.noFill();
    mset.stroke = "rgba(255,255,255,1)"; // white
    mset.linewidth = 2.5;
    mset.cap = "round";
    
var x = - 177 * Math.sin(toRadians(civil_twilight_rise));
var y = + 177 * Math.cos(toRadians(civil_twilight_rise));

var ctrx = x - 17 * Math.sin(toRadians(civil_twilight_rise));
var ctry = y + 17 * Math.cos(toRadians(civil_twilight_rise));
var ctrise = two.makeLine(x, y, ctrx, ctry);

	ctrise.noFill();
    ctrise.stroke = "rgba(74,227,82,1)"; // lime
    ctrise.linewidth = 2.5;
    ctrise.cap = "round";
    
var x = - 177 * Math.sin(toRadians(civil_twilight_set));
var y = + 177 * Math.cos(toRadians(civil_twilight_set));

var ctsx = x - 17 * Math.sin(toRadians(civil_twilight_set));
var ctsy = y + 17 * Math.cos(toRadians(civil_twilight_set));
var ctset = two.makeLine(x, y, ctsx, ctsy);

	ctset.noFill();
    ctset.stroke = "rgba(74,227,82,1)"; // lime
    ctset.linewidth = 2.5;
    ctset.cap = "round";
    
var x = - 177 * Math.sin(toRadians(nautical_twilight_rise));
var y = + 177 * Math.cos(toRadians(nautical_twilight_rise));

var ntrx = x - 17 * Math.sin(toRadians(nautical_twilight_rise));
var ntry = y + 17 * Math.cos(toRadians(nautical_twilight_rise));
var ntrise = two.makeLine(x, y, ntrx, ntry);

	ntrise.noFill();
    ntrise.stroke = "rgba(74,227,82,1)"; // lime
    ntrise.linewidth = 2.5;
    ntrise.cap = "round";
    
var x = - 177 * Math.sin(toRadians(nautical_twilight_set));
var y = + 177 * Math.cos(toRadians(nautical_twilight_set));

var ntsx = x - 17 * Math.sin(toRadians(nautical_twilight_set));
var ntsy = y + 17 * Math.cos(toRadians(nautical_twilight_set));
var ntset = two.makeLine(x, y, ntsx, ntsy);

	ntset.noFill();
    ntset.stroke = "rgba(74,227,82,1)"; // lime
    ntset.linewidth = 2.5;
    ntset.cap = "round";
    
var x = - 177 * Math.sin(toRadians(astro_twilight_rise || 0));
var y = + 177 * Math.cos(toRadians(astro_twilight_rise || 0));

var atrx = x - 17 * Math.sin(toRadians(astro_twilight_rise || 0));
var atry = y + 17 * Math.cos(toRadians(astro_twilight_rise || 0));
var atrise = two.makeLine(x, y, atrx, atry);

	atrise.noFill();
    atrise.stroke = "rgba(74,227,82,1)"; // lime
    atrise.linewidth = 2.5;
    atrise.cap = "round";
    
var x = - 177 * Math.sin(toRadians(astro_twilight_set || 0));
var y = + 177 * Math.cos(toRadians(astro_twilight_set || 0));

var atsx = x - 17 * Math.sin(toRadians(astro_twilight_set || 0));
var atsy = y + 17 * Math.cos(toRadians(astro_twilight_set || 0));
var atset = two.makeLine(x, y, atsx, atsy);

	atset.noFill();
    atset.stroke = "rgba(74,227,82,1)"; // lime
    atset.linewidth = 2.5;
    atset.cap = "round";      
    
}

Markers();

// define clock hands and group together
var x = - 144 * Math.sin(Math.PI * 2);
var y = + 144 * Math.cos(Math.PI * 2);
// hour
var hx = x - 39 * Math.sin(Math.PI * 2);
var hy = y + 39 * Math.cos(Math.PI * 2);
// minute
var mx = x - 39 * Math.sin(Math.PI * 2);
var my = y + 39 * Math.cos(Math.PI * 2);
// second
var sx = x - 39 * Math.sin(Math.PI * 2);
var sy = y + 39 * Math.cos(Math.PI * 2);

const hands = {
  
  hour: new Two.Line(x, y, hx, hy),
  minute: new Two.Line(x, y, mx, my),
  second: new Two.Line(x, y, sx, sy)
};

hands.hour.noFill();
hands.hour.stroke = "rgba(74,227,82,1)"; // Lime green
hands.hour.linewidth = 5;
hands.hour.cap = "round";

hands.minute.noFill();
hands.minute.stroke = "rgba(0,255,255,1)"; // Aqua
hands.minute.linewidth = 4;
hands.minute.cap = "round";

hands.second.noFill();
hands.second.stroke = "rgba(255,0,255,1)"; // Cyan
hands.second.linewidth = 2.5;
hands.second.cap = "round";

two.add(hands.hour, hands.minute, hands.second);

two.bind("resize", resize);
two.bind("update", update);

resize();

function resize() {
  two.scene.position.set(two.width / 2 - 140, two.height / 2);
}

// Create the clock and get it rolling
function update(frameCount, timeDelta) {

  // 24 hour clock with a sweeping second hand
  const now = new Date();
  
  const ms = now.getMilliseconds(); 
  const S = 2 * Math.PI * (now.getSeconds() * 1000 + ms) / 1000 / 60 - Math.PI; // make it sweep
  const M = 2 * Math.PI * (now.getMinutes() + now.getSeconds() / 60) / 60 - Math.PI;  
  const H = 2 * Math.PI * (now.getHours() + 12 + (now.getMinutes() + now.getSeconds() / 60) / 60) / 24 - Math.PI;

  hands.hour.rotation += H - hands.hour.rotation;
  hands.minute.rotation += M - hands.minute.rotation;
  hands.second.rotation += S - hands.second.rotation;
   
}

// create the sky
var skyRing = two.makeCircle(0, 0, 71.5);
skyRing.fill = "rgba(42,86,147,1)";
skyRing.linewidth = 137;
skyRing.stroke = "rgba(42,86,147,1)"; // sky blue

// -- create wire frame with arc segments, this was a nightmare ! --

var arcj = two.makeArcSegment(0, -560, 430, 430, 0.674, 0.468);
arcj.closed = false;
arcj.rotation = 1;
arcj.noFill();
arcj.linewidth = 0.5;
arcj.stroke = "rgba(137,142,143,1)";

var arcr = two.makeArcSegment(0, -564, 450, 450, 0.730, 0.412);
arcr.closed = false;
arcr.rotation = 1;
arcr.noFill();
arcr.linewidth = 0.5;
arcr.stroke = "rgba(137,142,143,1)";

var arce = two.makeArcSegment(0, -579, 480, 480, 0.758, 0.381);
arce.closed = false;
arce.rotation = 1;
arce.noFill();
arce.linewidth = 0.5;
arce.stroke = "rgba(137,142,143,1)";

var arcee = two.makeArcSegment(0, -572.5, 490, 490, 0.782, 0.359);
arcee.closed = false;
arcee.rotation = 1;
arcee.noFill();
arcee.linewidth = 0.5;
arcee.stroke = "rgba(137,142,143,1)";

var arca = two.makeArcSegment(0, 420, 480, 480, 1.280, 0.715);
arca.closed = false;
arca.rotation = 10;
arca.noFill();
arca.linewidth = 0.5;
arca.stroke = "rgba(137,142,143,1)";

var arcn = two.makeArcSegment(0, 180, 230, 230, 1.650, 0.340);
arcn.closed = false;
arcn.rotation = 10;
arcn.noFill();
arcn.linewidth = 0.5;
arcn.stroke = "rgba(137,142,143,1)";

var arcu = two.makeArcSegment(0, 142, 180, 180, 1.860, 0.130);
arcu.closed = false;
arcu.rotation = 10;
arcu.noFill();
arcu.linewidth = 0.5;
arcu.stroke = "rgba(137,142,143,1)";

var arcy = two.makeArcSegment(0, -140, 140, 140, 1.610, -0.470);
arcy.closed = false;
arcy.rotation = 1;
arcy.noFill();
arcy.linewidth = 0.5;
arcy.stroke = "rgba(137,142,143,1)";

var arcq = two.makeArcSegment(30, -136.5, 140, 140, 1.830, -0.260);
arcq.closed = false;
arcq.rotation = 1;
arcq.noFill();
arcq.linewidth = 0.5;
arcq.stroke = "rgba(137,142,143,1)";

var arcp = two.makeArcSegment(-30, -136.5, 140, 140, 1.400, -0.690);
arcp.closed = false;
arcp.rotation = 1;
arcp.noFill();
arcp.linewidth = 0.5;
arcp.stroke = "rgba(137,142,143,1)";

var arcl = two.makeArcSegment(60, -126.5, 140, 140, 2.060, -0.030);
arcl.closed = false;
arcl.rotation = 1;
arcl.noFill();
arcl.linewidth = 0.5;
arcl.stroke = "rgba(137,142,143,1)";

var arcg = two.makeArcSegment(-60, -126.5, 140, 140, 1.170, -0.910);
arcg.closed = false;
arcg.rotation = 1;
arcg.noFill();
arcg.linewidth = 0.5;
arcg.stroke = "rgba(137,142,143,1)";

var arck = two.makeArcSegment(90, -106.5, 140, 140, 2.320, 0.230);
arck.closed = false;
arck.rotation = 1;
arck.noFill();
arck.linewidth = 0.5;
arck.stroke = "rgba(137,142,143,1)";

var arch = two.makeArcSegment(-90, -106.5, 140, 140, 0.910, -1.170);
arch.closed = false;
arch.rotation = 1;
arch.noFill();
arch.linewidth = 0.5;
arch.stroke = "rgba(137,142,143,1)";

var arcx = two.makeArcSegment(275, -120.5, 300, 300, 2.197, 1.932);
arcx.closed = false;
arcx.rotation = 1;
arcx.noFill();
arcx.linewidth = 0.5;
arcx.stroke = "rgba(137,142,143,1)";

var arcz = two.makeArcSegment(-275, -120.5, 300, 300, -0.720, -1.058);
arcz.closed = false;
arcz.rotation = 1;
arcz.noFill();
arcz.linewidth = 0.5;
arcz.stroke = "rgba(137,142,143,1)";

// create both vertical and horizontal lines inside the wire frame
var vline = two.makeLine(0, -139, 0, -61);
vline.noFill();
vline.linewidth = 0.5;
vline.stroke = "rgba(137,142,143,1)";

var hline = two.makeLine(-121, -70, 121, -70);
hline.noFill();
hline.linewidth = 0.5;
hline.stroke = "rgba(137,142,143,1)";

// -- end wire frame --

// create the filled arc segments under the horizon
var arcf = two.makeArcSegment(0, 110, 140, 0, 2.160, -0.170);
arcf.closed = false;
arcf.rotation = 10;
arcf.fill = "rgba(41,46,53,1)"; // dark color
arcf.linewidth = 1;
arcf.stroke = "rgba(41,46,53,1)";

var arcs = two.makeArcSegment(0, -1.5, 140, 0, 1.720, -0.580);
arcs.closed = false;
arcs.rotation = 1;
arcs.fill = "rgba(41,46,53,1)";
arcs.linewidth = 1;
arcs.stroke = "rgba(41,46,53,1)"; // dark color

// create the main horizion arc, this sits on top of the filled arc segments
var horizion = two.makeArcSegment(0, 110, 140, 140, 2.160, -0.170);
horizion.closed = false;
horizion.rotation = 10;
horizion.noFill();
horizion.linewidth = 1;
horizion.stroke = "rgba(255,99,71,1)"; // tomato

// create the ring of Cancer this is the first ring past the clock numbers
var CancerR = two.makeCircle(0, 0, 140);
CancerR.noFill();
CancerR.linewidth = 1.25;
CancerR.stroke = "rgba(255,99,71,1)"; // tomato

// create the three arcs under the horizon, these are the twilight markers
var arci = two.makeArcSegment(0, 100, 120, 120, 2.360, -0.370);
arci.closed = false;
arci.rotation = 10;
arci.noFill();
arci.linewidth = 0.5;
arci.stroke = "rgba(137,142,143,1)";

var arct = two.makeArcSegment(0, 90, 100, 100, 2.640, -0.650);
arct.closed = false;
arct.rotation = 10;
arct.noFill();
arct.linewidth = 0.5;
arct.stroke = "rgba(137,142,143,1)";

var arcd = two.makeArcSegment(0, 80, 80, 80, 3.110, -1.120);
arcd.closed = false;
arcd.rotation = 10;
arcd.noFill();
arcd.linewidth = 0.5;
arcd.stroke = "rgba(137,142,143,1)";


// create the earth and fill it with a dark color
var Earth = two.makeCircle(0, 0, 60);
Earth.fill = "rgba(41,46,53,1)";
Earth.linewidth = 1.25;
Earth.stroke = "rgba(41,46,53,1)"; // dark color


// create the Equator ring 
var Equator = two.makeCircle(0, 0, 92);
Equator.noFill();
Equator.linewidth = 1.5;
Equator.stroke = "rgba(255,99,71,1)"; // tomato

// create the ring of Capricorn
var CapricornR = two.makeCircle(0, 0, 60);
CapricornR.noFill();
CapricornR.linewidth = 1.25;
CapricornR.stroke = "rgba(255,99,71,1)"; // tomato


// zodiac symbols to the right of the clock

const styles2 = {
  size: 20,
  weight: "normal",
  family: "AstroDotBasic",
  fill: "rgba(46,139,87,1)", // earth green
  opacity: 1.0 
};

var aries = "a";
var Aries = new Two.Text(aries, 240, -110, styles2);
		
var taurus = "b";
var Taurus = new Two.Text(taurus, 240, -90, styles2);
		
var gemini = "c";
var Gemini = new Two.Text(gemini, 240, -70, styles2);
		
var cancer = "d";
var Cancer = new Two.Text(cancer, 240, -50, styles2);
		
var leo = "e";
var Leo = new Two.Text(leo, 240, -30, styles2);
		
var virgo = "f";
var Virgo = new Two.Text(virgo, 240, -10, styles2);
		
var libra = "g";
var Libra = new Two.Text(libra, 240, 10, styles2);
		
var scorpio = "h";
var Scorpio = new Two.Text(scorpio, 240, 30, styles2);
		
var sagittarius = "i";
var Sagittarius = new Two.Text(sagittarius, 240, 50, styles2);
		
var capricorn = "j";
var Capricorn = new Two.Text(capricorn, 240, 70, styles2);
		
var aquarius = "k";
var Aquarius = new Two.Text(aquarius, 240, 90, styles2);
		
var pisces = "l";
var Pisces = new Two.Text(pisces, 240, 110, styles2);
		
two.scene.add(Aries, Taurus, Gemini, Cancer, Leo, Virgo, Libra, Scorpio, Sagittarius, Capricorn, Aquarius, Pisces);

// Data output Text

var Title = "Astronomical Clock";
var title = new Two.Text(Title, 260, - 111);
  	title.size = 14;
  	title.weight = "bold";
  	title.family = "Helvetica";
  	title.fill = "rgba(255,99,71,1)"; // tomato
  	title.alignment = "left";
  	
var Sub_Title = "Based on the Orloj in the City of Prague";
var subt = new Two.Text(Sub_Title, 260, - 93);
  	subt.size = 14;
  	subt.weight = "normal";
  	subt.family = "Helvetica";
  	subt.fill = "rgba(46,139,87,1)"; // earth green
  	subt.alignment = "left";

var underline = two.makeLine(260, - 79, 506,  - 79);
	underline.noFill();
  	underline.stroke = "rgba(0,127,255,1)"; // arch blue
  	underline.linewidth = 1.75;
  	underline.cap = "round";
  	  	
var Delta = "Location: <?php echo $stationlocation;?>";
var loc = new Two.Text(Delta, 260, - 63);
  	loc.size = 14;
  	loc.weight = "normal";
  	loc.family = "Helvetica";
  	loc.fill = "#FFA54F"; // tan
  	loc.alignment = "left";

var Latitude = "Latitude: <?php echo $lat;?>°";
var lat = new Two.Text(Latitude, 260, - 47);
  	lat.size = 14;
  	lat.weight = "normal";
  	lat.family = "Helvetica";
  	lat.fill = "rgba(0,127,255,1)"; // arch blue
  	lat.alignment = "left";  	

var Longitude = "Longitude: <?php echo $lon;?>°";
var lon = new Two.Text(Longitude, 260, - 31);
  	lon.size = 14;
  	lon.weight = "normal";
  	lon.family = "Helvetica";
  	lon.fill = "rgba(0,127,255,1)"; // arch blue
  	lon.alignment = "left";

var Elevation = "Elevation: <?php echo $elevation;?> m";
var elev = new Two.Text(Elevation, 260, - 15);
  	elev.size = 14;
  	elev.weight = "normal";
  	elev.family = "Helvetica";
  	elev.fill = "rgba(0,127,255,1)"; // arch blue
  	elev.alignment = "left";

var Equinox = "Equinox: <?php echo $alm["next_equinox"];?>";
var equinox = new Two.Text(Equinox, 260, 1);
  	equinox.size = 14;
  	equinox.weight = "normal";
  	equinox.family = "Helvetica";
  	equinox.fill = "#FFA54F"; // tan
  	equinox.alignment = "left";

var Solstice = "Solstice: <?php echo $alm["next_solstice"];?>";
var solstice = new Two.Text(Solstice, 260, 17);
  	solstice.size = 14;
  	solstice.weight = "normal";
  	solstice.family = "Helvetica";
  	solstice.fill = "rgba(255,99,71,1)"; // tomato
  	solstice.alignment = "left";

var Luminance = "Luminance: <?php echo $alm["luminance"];?> %";
var lum = new Two.Text(Luminance, 260, 33);
  	lum.size = 14;
  	lum.weight = "normal";
  	lum.family = "Helvetica";
  	lum.fill = "rgba(255,255,255,1)"; // white
  	lum.alignment = "left";
  	
var Moonphase = "Moon phase: <?php echo $alm["moonphase"];?>";
var mph = new Two.Text(Moonphase, 260, 48);
  	mph.size = 14;
  	mph.weight = "normal";
  	mph.family = "Helvetica";
  	mph.fill = "rgba(255,255,255,1)"; // white
  	mph.alignment = "left";
  	
var MoonAge = "Moon age: <?php echo $alm["moon_age"];?> Days old";
var mage = new Two.Text(MoonAge, 260, 64); // 64
  	mage.size = 14;
  	mage.weight = "normal";
  	mage.family = "Helvetica";
  	mage.fill = "rgba(255,255,255,1)"; // white
  	mage.alignment = "left";   	
  	
var Hmoon = "Moon azimuth: <?php echo $alm["moon_azimuth"];?>°";
var hmoon = new Two.Text(Hmoon, 260, 80); // 80
  	hmoon.size = 14;
  	hmoon.weight = "normal";
  	hmoon.family = "Helvetica";
  	hmoon.fill = "rgba(255,255,255,1)"; // white
  	hmoon.alignment = "left";

var Hsun = "Sun azimuth: <?php echo $alm["sun_azimuth"];?>°";
var hsun = new Two.Text(Hsun, 260, 96); // 96
  	hsun.size = 14;
  	hsun.weight = "normal";
  	hsun.family = "Helvetica";
  	hsun.fill = "rgba(255,99,71,1)"; // tomato
  	hsun.alignment = "left";

var LST = "Hand of Aries: <?php echo $alm["sidereal_time"];?>°";
var lst = new Two.Text(LST, 260, 112); // 112
  	lst.size = 14;
  	lst.weight = "normal";
  	lst.family = "Helvetica";
  	lst.fill = "rgba(0,127,255,1)"; // arch blue
  	lst.alignment = "left";

var PB = "Powered by Two.js";
var pb = new Two.Text(PB, 260, 135); 
  	pb.size = 14;
  	pb.weight = "normal";
  	pb.family = "Helvetica";
  	pb.fill = "rgba(46,139,87,1)"; // earth green
  	pb.alignment = "left";
	
	two.scene.add(title, subt, underline, loc, lat, lon, elev, equinox, solstice, lum, mph, mage, hmoon, hsun, lst, pb);
		
// visibility function for variation of the sun Kaleidoscope during the night time
	function Visibility(V) {
  
  if (V) {
    // coefficient
    const a0 = 35 / 51;
    const a1 = - 0.3495;
    const a2 = - 0.1254;
    const a3 = - 0.0955;
    const a4 = - 0.03134;
    const a5 = - 0.01398;
    var retV =
      a0 +
      a1 * Math.cos(V) +
      a2 * Math.cos(2 * V) +
      a3 * Math.cos(3 * V) +
      a4 * Math.cos(4 * V) +
      a5 * Math.cos(5 * V);

    return retV;
  }
  return;
}

// function to calculate the moon phase shape
    function phase(t, phaze) {
	
  var T = t - 2 * Math.PI * Math.floor(t / (2 * Math.PI));

  if (T < Math.PI) {
    phaze = Math.cos(t);
  }
  return phaze;
}

// define some things for the Astro clock (dont't delete anything here !!)
	var clockRadius = 140;

	// center of the clock
	var xc = 0;
	var yc = 0;
	
	const MoonRings = true;
	const MoonMutatis = true;
	const Kaleidoscope = true;
	const SunMutatis = true;
	
	var MoonMov = 8;
	var Velocity = 100;
			    
    const time = new Date();
   
	const mins = time.getMinutes();
	const secs = time.getSeconds();
		
	var K_Sun = 2 * Math.PI * (mins + secs / 60) / 60;
	var R_Moon = 2 * Math.PI * (mins - secs / 60) / 60;	       
/*	
 create and draw the ecliptic ring and turn it using hourAries
 the markers on this ring are in two parts, the first part is built into
 the eclipticRing function, the second part is
 the DayMarks function also turned using hourAries
*/ 
   
function EclipticRing() {

    var Rd = 0.28467 * clockRadius;
    var Re = 0.71533 * clockRadius;

    var xe = xc + (Rd * Math.sin(hourAries));
    var ye = yc - (Rd * Math.cos(hourAries));

    var arcb = two.makeCircle(xe, ye, 0.48 * (Re + eclipticWidth * Re), 0, 180 / Math.PI);
    arcb.noFill();
    arcb.linewidth = 35;
    arcb.stroke = "rgba(20,22,276,0.60)"; // blue

    var arc1 = two.makeCircle(xe, ye, Re, 0, 180 / Math.PI);
    arc1.noFill();
    arc1.linewidth = 2.5;
    arc1.stroke = "rgba(255,255,0,0.75)"; // yellow

    var arc2 = two.makeCircle(xe, ye, eclipticWidth * 1.25 * Re, 0, 180 / Math.PI);
    arc2.noFill();
    arc2.linewidth = 1.5;
    arc2.stroke = "rgba(255,255,0,1)"; // yellow

    var arc3 = two.makeCircle(xe, ye, eclipticWidth * Re * 0.9, 0, 180 / Math.PI);
    arc3.noFill();
    arc3.linewidth = 3.0;
    arc3.stroke = "rgba(255,255,0,0.75)"; // yellow
    
    // -- Zodiac Marker lines * 12 --
    
    var Lep = Re - Re * eclipticWidth;
      	
    var a = 0.2735 * clockRadius;
    var b = 2.51284;

  
let i = 0;
    while (i < 60) {

        var f = 12;
        var d = toDegRad(i,f);
        var sd = Math.sin(d);
        var a = 0.285 * clockRadius;
        var Re = a * (Math.sqrt((b * b) - (sd * sd)) + Math.cos(d));
        var Lep1 = Lep + 9;

        var factA = hourAries + d;
        var sinfactA = Math.sin(factA);
        var cosfactA = Math.cos(factA);
        var xa = xc + Re * sinfactA;
        var ya = yc - Re * cosfactA;
        var xb = xc + (Re - Lep1) * sinfactA;
        var yb = yc - (Re - Lep1) * cosfactA;
      
        var linez = two.makeLine(xa, ya, xb, yb);
  
        linez.noFill();
        linez.stroke = "rgb(255,255,0,0.75)"; // yellow
        linez.linewidth = 1.5;
        linez.cap = "round";
                       
        i = i + 2.5;
      }     
    }
      
EclipticRing();

// second part of the ecliptic ring the day markers * 60
function DayMarks() {

  var Rd = 0.28467 * clockRadius;
  var Re = 0.71533 * clockRadius;

  var xe = xc + Rd * Math.sin(hourAries);
  var ye = yc - Rd * Math.cos(hourAries);

    var Lep = Re - Re * eclipticWidth;
    var a = 0.283 * clockRadius;
    var b = 2.51284;


let i = 0;
    while (i < 60) {
        var f = 6;       
        var d = toDegRad(i, f);
        var sd = Math.sin(d);        
        var Re = a * (Math.sqrt((b * b) - (sd * sd)) + Math.cos(d));
        var Lep2 = 5;

        var factA = hourAries + d;
        var sinfactA = Math.sin(factA);
        var cosfactA = Math.cos(factA);
        var xg = xc + Re * sinfactA;
        var yg = yc - Re * cosfactA;
        var xh = xc + (Re - Lep2) * sinfactA;
        var yh = yc - (Re - Lep2) * cosfactA;
    
        var linex = two.makeLine(xg, yg, xh, yh);
  
        linex.noFill();
        linex.stroke = "rgb(255,255,0,0.75)"; // yellow
        linex.linewidth = 1;
        linex.cap = "round";
        
        i = i + 0.5;
      }  
    }

DayMarks();

// create and draw the 12 zodiac symbols inside the ecliptic ring

function zodiacAries() {

	var Rd = 0.28467 * clockRadius;
  	var Re = 0.71533 * clockRadius;

  	var xe = xc + Rd * Math.sin(hourAries);
  	var ye = yc - Rd * Math.cos(hourAries);
  		
  		var Lep = Re - Re * eclipticWidth;
  		var a  = 0.275 * clockRadius;
		var b = 2.51284;
		 
	        //  ('Ar','Ta','Ge','Ca','Le','Vi','Li','Sc','Sa','Cap','Aq','Pi')	       
	  var ast = [ "", "c", "", "", "", "", "", "", "", "", "", "", "" ];

	  
let i = 1;
    while (i < 30) {
        	
        var f = 12           
        var d  = toDegRad(i,f);
        var sd = Math.sin(d);        
        var Re = (a * (Math.sqrt((b * b) - (sd * sd)) + Math.cos(d))) - 3.9;
        		
        var factA = hourAries + d;
        var sinfactA = Math.sin(factA);
        var cosfactA = Math.cos(factA);
        var xd = xc + Re * sinfactA;
        var yd = yc - Re * cosfactA;
        var xf = xc + (Re - Lep) * sinfactA;
        var yf = yc - (Re - Lep) * cosfactA;
        var sign = ast[Math.floor(i % ast.length)];
        var zodiac = two.makeText(sign, (xd + xf) / 2, (yd + yf) / 2);

        zodiac.family = "AstroDotBasic";
        zodiac.weight = "bold";
		zodiac.size = "20";
		zodiac.fill = "rgb(255,255,0,1)";
		zodiac.noStroke = "rgb(255,255,0,1)"; // yellow
		
		i = i + 2.54;
	}
		
	two.scene.add(zodiac); 
}

zodiacAries();

function zodiacTaurus() {

	var Rd = 0.28467 * clockRadius;
  	var Re = 0.71533 * clockRadius;

  	var xe = xc + Rd * Math.sin(hourAries);
  	var ye = yc - Rd * Math.cos(hourAries);
  		
  		var Lep = Re - Re * eclipticWidth;
  		var a  = 0.275 * clockRadius;
		var b = 2.51284;
		 
	        //  ('Ar','Ta','Ge','Ca','Le','Vi','Li','Sc','Sa','Cap','Aq','Pi')

	  var ast = [ "", "", "d", "", "", "", "", "", "", "", "", "", "" ];
	  
let i = 1;
    while (i < 30) {
    	
        var f = 12           
        var d  = toDegRad(i,f);
        var sd = Math.sin(d);        
        var Re = (a * (Math.sqrt((b * b) - (sd * sd)) + Math.cos(d))) - 3.9;
        		
        var factA = hourAries + d;
        var sinfactA = Math.sin(factA);
        var cosfactA = Math.cos(factA);
        var xd = xc + Re * sinfactA;
        var yd = yc - Re * cosfactA;
        var xf = xc + (Re - Lep) * sinfactA;
        var yf = yc - (Re - Lep) * cosfactA;
        var sign = ast[Math.floor(i % ast.length)];
        var zodiac = two.makeText(sign, (xd + xf) / 2, (yd + yf) / 2);

        zodiac.family = "AstroDotBasic";
        zodiac.weight = "bold";
		zodiac.size = "20";
		zodiac.fill = "rgb(255,255,0,1)";
		zodiac.noStroke = "rgb(255,255,0,1)"; // yellow
		
		i = i + 2.54;
	}
		
	two.scene.add(zodiac); 
}

zodiacTaurus();

function zodiacGemini() {

	var Rd = 0.28467 * clockRadius;
  	var Re = 0.71533 * clockRadius;

  	var xe = xc + Rd * Math.sin(hourAries);
  	var ye = yc - Rd * Math.cos(hourAries);
  		
  		var Lep = Re - Re * eclipticWidth;
  		var a  = 0.275 * clockRadius;
		var b = 2.51284;
		 
	        //  ('Ar','Ta','Ge','Ca','Le','Vi','Li','Sc','Sa','Cap','Aq','Pi')

	  var ast = [ "", "", "e", "", "", "", "", "", "", "", "", "" ];
	  
let i = 1;
    while (i < 30) {
    	
        var f = 12           
        var d  = toDegRad(i,f);
        var sd = Math.sin(d);        
        var Re = (a * (Math.sqrt((b * b) - (sd * sd)) + Math.cos(d))) - 3.9;
        		
        var factA = hourAries + d;
        var sinfactA = Math.sin(factA);
        var cosfactA = Math.cos(factA);
        var xd = xc + Re * sinfactA;
        var yd = yc - Re * cosfactA;
        var xf = xc + (Re - Lep) * sinfactA;
        var yf = yc - (Re - Lep) * cosfactA;
        var sign = ast[Math.floor(i % ast.length)];
        var zodiac = two.makeText(sign, (xd + xf) / 2, (yd + yf) / 2);

        zodiac.family = "AstroDotBasic";
        zodiac.weight = "bold";
		zodiac.size = "20";
		zodiac.fill = "rgb(255,255,0,1)";
		zodiac.noStroke = "rgb(255,255,0,1)"; // yellow
		
		i = i + 2.54;
	}
		
	two.scene.add(zodiac); 
}

zodiacGemini();

function zodiacCancer() {

	var Rd = 0.28467 * clockRadius;
  	var Re = 0.71533 * clockRadius;

  	var xe = xc + Rd * Math.sin(hourAries);
  	var ye = yc - Rd * Math.cos(hourAries);
  		
  		var Lep = Re - Re * eclipticWidth;
  		var a  = 0.275 * clockRadius;
		var b = 2.51284;
		 
	        //  ('Ar','Ta','Ge','Ca','Le','Vi','Li','Sc','Sa','Cap','Aq','Pi')

	  var ast = [ "", "", "", "b", "", "", "", "", "", "", "", "" ];
	  
let i = 1;
    while (i < 30) {
    	
        var f = 12           
        var d  = toDegRad(i,f);
        var sd = Math.sin(d);        
        var Re = (a * (Math.sqrt((b * b) - (sd * sd)) + Math.cos(d))) - 3.9;
        		
        var factA = hourAries + d;
        var sinfactA = Math.sin(factA);
        var cosfactA = Math.cos(factA);
        var xd = xc + Re * sinfactA;
        var yd = yc - Re * cosfactA;
        var xf = xc + (Re - Lep) * sinfactA;
        var yf = yc - (Re - Lep) * cosfactA;
        var sign = ast[Math.floor(i % ast.length)];
        var zodiac = two.makeText(sign, (xd + xf) / 2, (yd + yf) / 2);

        zodiac.family = "AstroDotBasic";
        zodiac.weight = "bold";
		zodiac.size = "20";
		zodiac.fill = "rgb(255,255,0,1)";
		zodiac.noStroke = "rgb(255,255,0,1)"; // yellow
		
		i = i + 2.54;
	}
		
	two.scene.add(zodiac); 
}

zodiacCancer();

function zodiacLeo() {

	var Rd = 0.28467 * clockRadius;
  	var Re = 0.71533 * clockRadius;

  	var xe = xc + Rd * Math.sin(hourAries);
  	var ye = yc - Rd * Math.cos(hourAries);
  		
  		var Lep = Re - Re * eclipticWidth;
  		var a  = 0.275 * clockRadius;
		var b = 2.51284;
		 
	        //  ('Ar','Ta','Ge','Ca','Le','Vi','Li','Sc','Sa','Cap','Aq','Pi')

	  var ast = [ "", "", "", "", "", "", "a", "", "", "", "", "", "" ];
	  
let i = 1;
    while (i < 30) {
    	
        var f = 12           
        var d  = toDegRad(i,f);
        var sd = Math.sin(d);        
        var Re = (a * (Math.sqrt((b * b) - (sd * sd)) + Math.cos(d))) - 3.9;
        		
        var factA = hourAries + d;
        var sinfactA = Math.sin(factA);
        var cosfactA = Math.cos(factA);
        var xd = xc + Re * sinfactA;
        var yd = yc - Re * cosfactA;
        var xf = xc + (Re - Lep) * sinfactA;
        var yf = yc - (Re - Lep) * cosfactA;
        var sign = ast[Math.floor(i % ast.length)];
        var zodiac = two.makeText(sign, (xd + xf) / 2, (yd + yf) / 2);

        zodiac.family = "AstroDotBasic";
        zodiac.weight = "bold";
		zodiac.size = "20";
		zodiac.fill = "rgb(255,255,0,1)";
		zodiac.noStroke = "rgb(255,255,0,1)"; // yellow
		
		i = i + 2.54;
	}
		
	two.scene.add(zodiac); 
}

zodiacLeo();

function zodiacVirgo() {

	var Rd = 0.28467 * clockRadius;
  	var Re = 0.71533 * clockRadius;

  	var xe = xc + Rd * Math.sin(hourAries);
  	var ye = yc - Rd * Math.cos(hourAries);
  		
  		var Lep = Re - Re * eclipticWidth;
  		var a  = 0.275 * clockRadius;
		var b = 2.51284;
		 
	        //  ('Ar','Ta','Ge','Ca','Le','Vi','Li','Sc','Sa','Cap','Aq','Pi')

	  var ast = [ "", "", "", "", "", "", "", "", "l", "", "", "" ];
	  
let i = 1;
    while (i < 30) {
    	
        var f = 12           
        var d  = toDegRad(i,f);
        var sd = Math.sin(d);        
        var Re = (a * (Math.sqrt((b * b) - (sd * sd)) + Math.cos(d))) - 3.9;
        		
        var factA = hourAries + d;
        var sinfactA = Math.sin(factA);
        var cosfactA = Math.cos(factA);
        var xd = xc + Re * sinfactA;
        var yd = yc - Re * cosfactA;
        var xf = xc + (Re - Lep) * sinfactA;
        var yf = yc - (Re - Lep) * cosfactA;
        var sign = ast[Math.floor(i % ast.length)];
        var zodiac = two.makeText(sign, (xd + xf) / 2, (yd + yf) / 2);

        zodiac.family = "AstroDotBasic";
        zodiac.weight = "bold";
		zodiac.size = "20";
		zodiac.fill = "rgb(255,255,0,1)";
		zodiac.noStroke = "rgb(255,255,0,1)"; // yellow
		
		i = i + 2.54;
	}
		
	two.scene.add(zodiac); 
}

zodiacVirgo();

function zodiacLibra() {

	var Rd = 0.28467 * clockRadius;
  	var Re = 0.71533 * clockRadius;

  	var xe = xc + Rd * Math.sin(hourAries);
  	var ye = yc - Rd * Math.cos(hourAries);
  		
  		var Lep = Re - Re * eclipticWidth;
  		var a  = 0.275 * clockRadius;
		var b = 2.51284;
		 
	        //  ('Ar','Ta','Ge','Ca','Le','Vi','Li','Sc','Sa','Cap','Aq','Pi')

	  var ast = [ "", "", "", "", "", "", "", "", "", "", "", "k", "" ];
	  
let i = 1;
    while (i < 30) {
    	
        var f = 12           
        var d  = toDegRad(i,f);
        var sd = Math.sin(d);        
        var Re = (a * (Math.sqrt((b * b) - (sd * sd)) + Math.cos(d))) - 3.9;
        		
        var factA = hourAries + d;
        var sinfactA = Math.sin(factA);
        var cosfactA = Math.cos(factA);
        var xd = xc + Re * sinfactA;
        var yd = yc - Re * cosfactA;
        var xf = xc + (Re - Lep) * sinfactA;
        var yf = yc - (Re - Lep) * cosfactA;
        var sign = ast[Math.floor(i % ast.length)];
        var zodiac = two.makeText(sign, (xd + xf) / 2, (yd + yf) / 2);

        zodiac.family = "AstroDotBasic";
        zodiac.weight = "bold";
		zodiac.size = "20";
		zodiac.fill = "rgb(255,255,0,1)";
		zodiac.noStroke = "rgb(255,255,0,1)"; // yellow
		
		i = i + 2.54;
	}
		
	two.scene.add(zodiac); 
}

zodiacLibra();

function zodiacScorpio() {

	var Rd = 0.28467 * clockRadius;
  	var Re = 0.71533 * clockRadius;

  	var xe = xc + Rd * Math.sin(hourAries);
  	var ye = yc - Rd * Math.cos(hourAries);
  		
  		var Lep = Re - Re * eclipticWidth;
  		var a  = 0.275 * clockRadius;
		var b = 2.51284;
		 
	        //  ('Ar','Ta','Ge','Ca','Le','Vi','Li','Sc','Sa','Cap','Aq','Pi')

	  var ast = [ "", "", "", "", "", "", "", "", "", "", "f", "", "" ];
	  
let i = 1;
    while (i < 30) {
    	
        var f = 12           
        var d  = toDegRad(i,f);
        var sd = Math.sin(d);        
        var Re = (a * (Math.sqrt((b * b) - (sd * sd)) + Math.cos(d))) - 3.9;
        		
        var factA = hourAries + d;
        var sinfactA = Math.sin(factA);
        var cosfactA = Math.cos(factA);
        var xd = xc + Re * sinfactA;
        var yd = yc - Re * cosfactA;
        var xf = xc + (Re - Lep) * sinfactA;
        var yf = yc - (Re - Lep) * cosfactA;
        var sign = ast[Math.floor(i % ast.length)];
        var zodiac = two.makeText(sign, (xd + xf) / 2, (yd + yf) / 2);

        zodiac.family = "AstroDotBasic";
        zodiac.weight = "bold";
		zodiac.size = "20";
		zodiac.fill = "rgb(255,255,0,1)";
		zodiac.noStroke = "rgb(255,255,0,1)"; // yellow
		
		i = i + 2.54;
	}
		
	two.scene.add(zodiac); 
}

zodiacScorpio();

function zodiacSagittarius() {

	var Rd = 0.28467 * clockRadius;
  	var Re = 0.71533 * clockRadius;

  	var xe = xc + Rd * Math.sin(hourAries);
  	var ye = yc - Rd * Math.cos(hourAries);
  		
  		var Lep = Re - Re * eclipticWidth;
  		var a  = 0.275 * clockRadius;
		var b = 2.51284;
		 
	        //  ('Ar','Ta','Ge','Ca','Le','Vi','Li','Sc','Sa','Cap','Aq','Pi')

	  var ast = [ "", "", "", "", "", "", "", "", "", "", "g" ];
	  
let i = 1;
    while (i < 30) {
    	
        var f = 12           
        var d  = toDegRad(i,f);
        var sd = Math.sin(d);        
        var Re = (a * (Math.sqrt((b * b) - (sd * sd)) + Math.cos(d))) - 3.9;
        		
        var factA = hourAries + d;
        var sinfactA = Math.sin(factA);
        var cosfactA = Math.cos(factA);
        var xd = xc + Re * sinfactA;
        var yd = yc - Re * cosfactA;
        var xf = xc + (Re - Lep) * sinfactA;
        var yf = yc - (Re - Lep) * cosfactA;
        var sign = ast[Math.floor(i % ast.length)];
        var zodiac = two.makeText(sign, (xd + xf) / 2, (yd + yf) / 2);

        zodiac.family = "AstroDotBasic";
        zodiac.weight = "bold";
		zodiac.size = "20";
		zodiac.fill = "rgb(255,255,0,1)";
		zodiac.noStroke = "rgb(255,255,0,1)"; // yellow
		
		i = i + 2.54;
	}
		
	two.scene.add(zodiac); 
}

zodiacSagittarius();

function zodiacCapricorn() {

	var Rd = 0.28467 * clockRadius;
  	var Re = 0.71533 * clockRadius;

  	var xe = xc + Rd * Math.sin(hourAries);
  	var ye = yc - Rd * Math.cos(hourAries);
  		
  		var Lep = Re - Re * eclipticWidth;
  		var a  = 0.275 * clockRadius;
		var b = 2.51284;
		 
	        //  ('Ar','Ta','Ge','Ca','Le','Vi','Li','Sc','Sa','Cap','Aq','Pi')

	  var ast = [  "", "", "", "", "h", "", "", "", "", "", "", "", "", "" ];
	  
let i = 1;
    while (i < 30) {
    	
        var f = 12           
        var d  = toDegRad(i,f);
        var sd = Math.sin(d);        
        var Re = (a * (Math.sqrt((b * b) - (sd * sd)) + Math.cos(d))) - 3.9;
        		
        var factA = hourAries + d;
        var sinfactA = Math.sin(factA);
        var cosfactA = Math.cos(factA);
        var xd = xc + Re * sinfactA;
        var yd = yc - Re * cosfactA;
        var xf = xc + (Re - Lep) * sinfactA;
        var yf = yc - (Re - Lep) * cosfactA;
        var sign = ast[Math.floor(i % ast.length)];
        var zodiac = two.makeText(sign, (xd + xf) / 2, (yd + yf) / 2);

        zodiac.family = "AstroDotBasic";
        zodiac.weight = "bold";
		zodiac.size = "20";
		zodiac.fill = "rgb(255,255,0,1)";
		zodiac.noStroke = "rgb(255,255,0,1)"; // yellow
		
		i = i + 2.54;
	}
		
	two.scene.add(zodiac); 
}

zodiacCapricorn();

function zodiacAquarius() {

	var Rd = 0.28467 * clockRadius;
  	var Re = 0.71533 * clockRadius;

  	var xe = xc + Rd * Math.sin(hourAries);
  	var ye = yc - Rd * Math.cos(hourAries);
  		
  		var Lep = Re - Re * eclipticWidth;
  		var a  = 0.275 * clockRadius;
		var b = 2.51284;
		 
	        //  ('Ar','Ta','Ge','Ca','Le','Vi','Li','Sc','Sa','Cap','Aq','Pi')

	  var ast = [  "", "", "i", "", "", "", "", "", "", "", "", "", "", "" ];
	  
let i = 1;
    while (i < 30) {
    	
        var f = 12           
        var d  = toDegRad(i,f);
        var sd = Math.sin(d);        
        var Re = (a * (Math.sqrt((b * b) - (sd * sd)) + Math.cos(d))) - 3.9;
        		
        var factA = hourAries + d;
        var sinfactA = Math.sin(factA);
        var cosfactA = Math.cos(factA);
        var xd = xc + Re * sinfactA;
        var yd = yc - Re * cosfactA;
        var xf = xc + (Re - Lep) * sinfactA;
        var yf = yc - (Re - Lep) * cosfactA;
        var sign = ast[Math.floor(i % ast.length)];
        var zodiac = two.makeText(sign, (xd + xf) / 2, (yd + yf) / 2);

        zodiac.family = "AstroDotBasic";
        zodiac.weight = "bold";
		zodiac.size = "20";
		zodiac.fill = "rgb(255,255,0,1)";
		zodiac.noStroke = "rgb(255,255,0,1)"; // yellow
		
		i = i + 2.54;
	}
		
	two.scene.add(zodiac); 
}

zodiacAquarius();

function zodiacPisces() {

	var Rd = 0.28467 * clockRadius;
  	var Re = 0.71533 * clockRadius;

  	var xe = xc + Rd * Math.sin(hourAries);
  	var ye = yc - Rd * Math.cos(hourAries);
  		
  		var Lep = Re - Re * eclipticWidth;
  		var a  = 0.275 * clockRadius;
		var b = 2.51284;
		 
	        //  ('Ar','Ta','Ge','Ca','Le','Vi','Li','Sc','Sa','Cap','Aq','Pi')

	  var ast = [  "", "", "", "", "", "", "", "", "", "", "", "", "", "j" ];
	  
let i = 1;
    while (i < 30) {
    	
        var f = 12           
        var d  = toDegRad(i,f);
        var sd = Math.sin(d);        
        var Re = (a * (Math.sqrt((b * b) - (sd * sd)) + Math.cos(d))) - 3.9;
        		
        var factA = hourAries + d;
        var sinfactA = Math.sin(factA);
        var cosfactA = Math.cos(factA);
        var xd = xc + Re * sinfactA;
        var yd = yc - Re * cosfactA;
        var xf = xc + (Re - Lep) * sinfactA;
        var yf = yc - (Re - Lep) * cosfactA;
        var sign = ast[Math.floor(i % ast.length)];
        var zodiac = two.makeText(sign, (xd + xf) / 2, (yd + yf) / 2);

        zodiac.family = "AstroDotBasic";
        zodiac.weight = "bold";
		zodiac.size = "20";
		zodiac.fill = "rgb(255,255,0,1)";
		zodiac.noStroke = "rgb(255,255,0,1)"; // yellow
		
		i = i + 2.54;
	}
		
	two.scene.add(zodiac); 
}

zodiacPisces();

// create and draw the blue cross onto the ecliptic ring and turn it using hourAries
function AriesHand() {

  var ariesHourFactor = hourAries + Math.PI / 2;
  var sinAriesHand = Math.sin(ariesHourFactor) * clockRadius;
  var cosAriesHand = Math.cos(ariesHourFactor) * clockRadius;
  var xa = xc + 0.87 * sinAriesHand;
  var ya = yc - 0.87 * cosAriesHand;
  var Xa = xc - 0.654 * sinAriesHand;
  var Ya = yc + 0.654 * cosAriesHand;
   
  var line1 = two.makeLine(xa, ya, Xa, Ya); // spring equinox/Aries hand one end, autumn equinox the other end

  line1.noFill();
  line1.stroke = "rgba(0,127,255,1)"; // arch blue
  line1.linewidth = 3;
  line1.cap = "round";

  var dot1 = two.makeCircle(Xa, Ya, 4, 0, Math.PI * 2); // autumn equinox dot
  dot1.fill = "rgba(255,99,71,1)";
  dot1.linewidth = 1;
  dot1.stroke = "rgba(255,99,71,1)"; // tomato

  var dot2 = two.makeCircle(xa, ya, 4.5, 0, Math.PI * 2); // dot on the end of Aries hand
  dot2.fill = "rgba(255,99,71,1)";
  dot2.linewidth = 1;
  dot2.stroke = "rgba(255,99,71,1)"; // tomato
  
  var ariesHourFactor = hourAries - 0.011 + Math.PI / 270;
  var sinAriesHand = Math.sin(ariesHourFactor) * clockRadius;
  var cosAriesHand = Math.cos(ariesHourFactor) * clockRadius;
  var xa = xc + sinAriesHand;
  var ya = yc - cosAriesHand;
  var Xa = xc - 0.43 * sinAriesHand;
  var Ya = yc + 0.43 * cosAriesHand;
  
  var line2 = two.makeLine(xa, ya, Xa, Ya); // summer solstice one end, winter solstice the other end

  line2.noFill();
  line2.stroke = "rgba(0,127,255,1)"; // arch blue
  line2.linewidth = 3;
  line2.cap = "round";

  var dot3 = two.makeCircle(Xa, Ya, 4, 0, Math.PI * 2); // winter solstice dot
  dot3.fill = "rgba(255,99,71,1)";
  dot3.linewidth = 1;
  dot3.stroke = "rgba(255,99,71,1)"; // tomato

  var dot4 = two.makeCircle(xa, ya, 4, 0, Math.PI * 2); // summer solstice dot
  dot4.fill = "rgba(255,99,71,1)";
  dot4.linewidth = 1;
  dot4.stroke = "rgba(255,99,71,1)"; // tomato
}

AriesHand();

/* create and draw the sun needle and the sun */
function SunNeedle() {

    var as = 0.28467 * clockRadius;
    var bs = 2.51284;
    var ds = - hourAries + hours_arc;
    var sds = Math.sin(ds);
    var Rs = as * (Math.sqrt(bs * bs - sds * sds) + Math.cos(ds));
    		
	var Xs = xc - 0.17 * clockRadius * Math.sin(hours_arc);
	var Ys = yc + 0.17 * clockRadius * Math.cos(hours_arc);
  
    var line3 = two.makeLine(xc, yc, Xs, Ys);
  
    line3.noFill();
    line3.stroke = "rgba(255,255,0,1)"; // yellow
    line3.linewidth = 3;
    line3.cap = "round";

				
	var spots = two.makeCircle(Xs, Ys, 3, 0, Math.PI * 2);
	spots.fill = "rgba(255,255,0,1)";
    spots.linewidth = 1;
    spots.stroke = "rgba(255,255,0,1)"; // yellow
			
	var Xs = xc + 1.31 * clockRadius * Math.sin(hours_arc);
	var Ys = yc - 1.31 * clockRadius * Math.cos(hours_arc);

	var line4 = two.makeLine(xc, yc, Xs, Ys);
  
    line4.noFill();
    line4.stroke = "rgba(255,255,0,1)"; // yellow
    line4.linewidth = 3;
    line4.cap = "round";
        
    var Xs = xc + Rs * Math.sin(hours_arc);
	var Ys = yc - Rs * Math.cos(hours_arc);
				            
    var layers = {
  	background: two.makeGroup()
};

	var Rad = 38;
	var glow = new Two.Circle(Xs, Ys, Rad);
	glow.fill = "rgba(255, 85, 85, 1.0)"; // sunset red
	glow.fill = new Two.RadialGradient(0.5, 0.5, 0.5, [
  	new Two.Stop(0.15, "rgba(255, 85, 85, 0.80)"), // sunset red
  	new Two.Stop(1.00, "rgba(255, 85, 85, 0.0)") // sunset red
]);
	glow.noStroke();

	layers.background.add(glow);

           
    // -- sun Visibility variation during the day and night    
    if (SunMutatis == true) {
	var Alpha_S = Visibility(hours_arc + Math.PI);
	} else {
	  Alpha_S = 1;
	}
	
		var opacity = Alpha_S; // night time alpha variation
		
	// create the rings and kaleidoscope for the sun
	if (Kaleidoscope == true) { // all this should have a one second tick to animate it
    
    	var RmS = 7 * (1 - Alpha_S);

		// -- Draw inner and outer rings --
		
		var outR = two.makeCircle(Xs, Ys, 30, 0, 2 * Math.PI); // static size
		outR.stroke = "rgba(255,255,0,0.80)";
  		outR.linewidth = 2;
  		outR.noFill();
		
		var innR = two.makeCircle(Xs, Ys, 22 + RmS, 0, 2 * Math.PI); // ring size variation at night time
		innR.stroke = "rgba(255,255,0,0.80)";
  		innR.linewidth = 2;
  		innR.noFill();

		// -- color fill between the two rings --
		 
		var colF = two.makeCircle(Xs, Ys, 0.5 * (30 + 22 + RmS), 0, 2 * Math.PI); // size variation at night time
		colF.stroke = "rgba(255,255,0,0.20)";
  		colF.linewidth = 30 - 22 - RmS; // width variation at night time
  		colF.noFill();
  		 		     		  		
		let i = 0;

		while (i <= 11) {

			var xzx = Xs + 30 * Math.sin(hourAries + i * Math.PI / 6 - K_Sun);
			var yzx = Ys - 30 * Math.cos(hourAries + i * Math.PI / 6 - K_Sun);
			var Xzx = Xs + (22 + RmS) * Math.sin(hourAries + i * Math.PI / 6 + Velocity * K_Sun);
			var Yzx = Ys - (22 + RmS) * Math.cos(hourAries + i * Math.PI / 6 + Velocity * K_Sun);

			var strokes = two.makeLine(xzx ,yzx ,Xzx ,Yzx);
						 			
			strokes.stroke = "rgba(255,255,0,"+opacity+")";
    		strokes.linewidth = 1.5;
    		strokes.cap = "round";
    		strokes.noFill();

			i = i + 1;
		}
	}
	
	// sun center spots
	var suns = two.makeCircle(Xs, Ys, 10, 0, 2 * Math.PI);
	suns.stroke = "rgba(255,255,0,"+opacity+")"; // night time alpha variation
	suns.linewidth = 0.001;
	suns.fill = "rgba(255,255,0,"+opacity+")";
	
	var suns = two.makeCircle(Xs, Ys, 3.25, 0, 2 * Math.PI);
	suns.stroke = "rgba(255,255,0,1)";
	suns.linewidth = 1;
	suns.fill = "rgba(255,255,0,1)";
 	
} // -- end sun --

SunNeedle();


/* create and draw the moon needle and the moon */
function MoonNeedle() {
  
  	var hour_MD = toRadians(hourMoon);

    var am = 0.28467 * clockRadius;
    var bm = 2.51284;
    var dm = hourAries - hour_MD;
    var mdm = Math.sin(dm);
    var Rm = am * (Math.sqrt(bm * bm - mdm * mdm) + Math.cos(dm));
		
	var Xm = xc - 0.17 * clockRadius * Math.sin(hour_MD);
	var Ym = yc + 0.17 * clockRadius * Math.cos(hour_MD);
 
    var line4 = two.makeLine(xc, yc, Xm, Ym); // short end of the needle 
  
    line4.noFill();
    line4.stroke = "rgba(255,0,0,1)"; // red
    line4.linewidth = 3;
    line4.cap = "round";
				
	var spotm = two.makeCircle(Xm, Ym, 3, 0, Math.PI * 2);
	spotm.fill = "rgba(255,0,0,1)";
    spotm.linewidth = 1;
    spotm.stroke = "rgba(255,0,0,1)"; // red	
		
	var Xm = xc + 1.31 * clockRadius * Math.sin(hour_MD);
	var Ym = yc - 1.31 * clockRadius * Math.cos(hour_MD);
  
	var line5 = two.makeLine(xc, yc, Xm, Ym); // long end of the needle
  
    line5.noFill();
    line5.stroke = "rgba(255,0,0,1)"; // red
    line5.linewidth = 3;
    line5.cap = "round";
    
    var Xm = xc + Rm * Math.sin(hour_MD);
	var Ym = yc - Rm * Math.cos(hour_MD);
		
	var moonD = two.makeCircle(Xm, Ym, 10, 0, 2 * Math.PI);
	moonD.stroke = "rgba(41,46,53,1)"; // "rgba(41,46,53,1)"
  	moonD.linewidth = 1;
  	moonD.fill = "rgba(41,46,53,1)"; // -- moon background dark --
	 	
  	// -- moon Visibility --
  	          
	if (MoonMutatis == true) {
  		var Alpha_M = 1;
  		//color_M1;	 		  		  
	} else {	
  	 var V = hours_arc - hour_MD;
  		Alpha_M = Visibility(V);
   		//color_M;
	}
 	
// draw the moonphase shape + rotation calculation
 	if (MoonMutatis == true) {
  
 	const Rmo = 10.55;
  	var temp = - hour_MD + hours_arc;

  	var amp_Right = Rmo * phase(temp + Math.PI, 1);
  	var amp_Left = Rmo * phase(temp, -1);

  	var Rot = hour_MD + Math.PI;

  	var Xf = Xm + Math.sin(Rot) * Rmo;
  	var Yf = Ym - Math.cos(Rot) * Rmo;

  	var path0 = two.makePath(Xf, Yf);
  	
  let i = 0;
  
  	while (i < 2 * Math.PI) {
    	if (i <= Math.PI) {
      	var Rx = Math.sin(i) * amp_Right;
    } else {
      	var Rx = - Math.sin(i) * amp_Left;
    }

    	var Ry = Math.cos(i) * Rmo;

    	var Xg = Xm - Rx * Math.cos(Rot) + Ry * Math.sin(Rot);
    	var Yg = Ym - Rx * Math.sin(Rot) - Ry * Math.cos(Rot);

    	var path = two.makeLine(Xg, Yg, Xm, Ym);

    	//path.closed = true;
    	path.stroke = color_M;
  		path.linewidth = 1;
  		path.fill = color_M;


     	i = i + Math.PI / 64;
  	}		
}
	
	// Variation of the Day
	
 	if (MoonMutatis == true) {
  		var D = 0.5 + 0.5 * Visibility(hours_arc);
 	} else {
  		D = 1;	  	
}
  	
	if (MoonRings == true) { // all this should have a one second tick to animate it
	 
  	var RmL = MoonMov * Alpha_M;
		var RmB = 12;
		var Rms = 12 - 7 * D;

	    // color fill between the two rings
  		var colF = two.makeCircle(Xm, Ym, 0.5 * (RmB + Rms) + RmL, 0, 2 * Math.PI); // with size variation
  		colF.stroke = "rgba(255,255,255,0.10)"; // white
  		colF.linewidth = RmB - Rms;
  		colF.noFill();

  		// Draw outer ring
  		var outR = two.makeCircle(Xm, Ym, RmB + RmL, 0, 2 * Math.PI); // with size variation
  		outR.stroke = "rgba(255,255,255,1.0)"; // white
  		outR.linewidth = 1.5;
  		outR.noFill();

  		// Draw inner ring
  		var innR = two.makeCircle(Xm, Ym, Rms + RmL, 0, 2 * Math.PI); // with size variation
  		innR.stroke = "rgba(255,255,255,1.0)"; // white
  		innR.linewidth = 1.5;
  		innR.noFill();

	let i = 0;

  		while (i <= 11) {

			var xzz = Xm + (Rms + RmL) * Math.sin(hourAries + i * Math.PI / 6 - 2 * Velocity * R_Moon);
			var yzz = Ym - (Rms + RmL) * Math.cos(hourAries + i * Math.PI / 6 - 2 * Velocity * R_Moon);
			var Xzz = Xm + (RmB + RmL) * Math.sin(hourAries + i * Math.PI / 6 - 2 * Velocity * R_Moon);
			var Yzz = Ym - (RmB + RmL) * Math.cos(hourAries + i * Math.PI / 6 - 2 * Velocity * R_Moon);

			var Mstrokes = two.makeLine(xzz, yzz, Xzz, Yzz);
			Mstrokes.stroke = "rgba(255,255,255,1.0)"; // white
      		Mstrokes.linewidth = 1.5;
      		Mstrokes.cap = "round";
      		Mstrokes.noFill();

			i = i + 1;
  		}
	}
}
        
	
MoonNeedle();

// -- this covers the center of the blue cross and the needles, it must be last ! --
var Ringf = two.makeCircle(0, 0, 12);
Ringf.fill = "rgba(41,46,53,1)";
Ringf.linewidth = 1;
Ringf.stroke = "rgb(41,46,53,1)"; // dark cover

var Ringb = two.makeCircle(0, 0, 12);
Ringb.noFill();
Ringb.linewidth = 3;
Ringb.stroke = "rgba(0,127,255,1)"; // arch blue ring

var center = two.makeCircle(0, 0, 4); // center point
center.fill = "rgba(255,99,71,1)";
center.linewidth = 1;
center.stroke = "rgba(255,99,71,1)"; // tomato



// now it's showtime :-)
two.update();

</script>
</div>

</body>
</html>
