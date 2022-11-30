<?php
include('metar34get.php');
date_default_timezone_set($TZ);
error_reporting(0);
header('Content-type: text/html; charset = utf-8');
?>

<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>compass</title>
<meta name="viewport" content="width = device-width, initial-scale = 1, shrink-to-fit = yes">
</head>

<body>  
  
<style>

body {
overflow: hidden;
}

.wrap {
  position: relative;
  top: 0px;
  left: 0px;  
}

</style>

<div style="overflow: hidden">
<div class="wrap">
  <svg id="compass" width="140" height="140" xmlns="http://www.w3.org/2000/svg">
  </svg>
</div>
</div>
 
<script>

var svgNS = "http://www.w3.org/2000/svg";

var svg = document.getElementById("compass");

var theme = "<?php echo $theme;?>";

	if (theme == 'dark') {
    
	if (angle == 0) {
		var angle = 0;
	} else {
		var angle = "<?php echo $metar34windir;?>";
	}
    
	DirectionAngle(70, 74, angle + "°"); // Direction in degrees


	CardinalNorth(66.75, 29, "N");
	CardinalDirection(111, 72.75, "E");
	CardinalDirection(67, 116, "S");
	CardinalDirection(23, 72.75, "W");


for (var i = 0; i < 360; i += 2) {
  // draw degree lines
  var s = "rgba(59, 60, 63, 1)"; // dark grey
  if (i == 0 || i % 30 == 0) {
    w = 1;
    s = "rgba(255, 99, 71, 1)"; // tomato
    y2 = 17;
  } else {
    w = 0.75;
    y2 = 17;
  }
  
var ticks = document.createElementNS(svgNS, "line");
	ticks.setAttributeNS(null, "x1", 70);
	ticks.setAttributeNS(null, "y1", 10);
	ticks.setAttributeNS(null, "x2", 70);
	ticks.setAttributeNS(null, "y2", y2);
	ticks.setAttributeNS(null, "stroke", s);
	ticks.setAttributeNS(null, "stroke-width", w);
	ticks.setAttributeNS(null, "stroke-linecap", "round");
	ticks.setAttributeNS(null, "transform", "rotate(" + i + ", 70, 70)");
	svg.appendChild(ticks);

  // draw degree value every 30 degrees
  if (i % 30 == 0) {
    var t1 = document.createElementNS(svgNS, "text");
    if (i > 100) {
      t1.setAttributeNS(null, "x", 64);
    } else if (i > 0) {
      t1.setAttributeNS(null, "x", 66);
    } else {
      t1.setAttributeNS(null, "x", 68.25);
    }
    t1.setAttributeNS(null, "y", 7);
    t1.setAttributeNS(null, "font-size", "6px");
    t1.setAttributeNS(null, "font-family", "Helvetica");
    t1.setAttributeNS(null, "fill", "rgba(147,147,147,1)");
    t1.setAttributeNS(null, "style", "letter-spacing: 1.0");
    t1.setAttributeNS(null, "transform", "rotate(" + i + ", 70, 70)");
    var textNode = document.createTextNode(i);
    t1.appendChild(textNode);
    svg.appendChild(t1);
  }
}

function CardinalNorth(x, y, displayText) {
	var direction = document.createElementNS(svgNS, "text");
  	direction.setAttributeNS(null, "x", x);
  	direction.setAttributeNS(null, "y", y);
  	direction.setAttributeNS(null, "font-size", "9px");
  	direction.setAttributeNS(null, "font-weight", "bold");
  	direction.setAttributeNS(null, "font-family", "Helvetica");
  	direction.setAttributeNS(null, "fill", "red");
	var textNode = document.createTextNode(displayText);
  	direction.appendChild(textNode);
  	svg.appendChild(direction);
}

function CardinalDirection(x, y, displayText) {
	var direction = document.createElementNS(svgNS, "text");
  	direction.setAttributeNS(null, "x", x);
  	direction.setAttributeNS(null, "y", y);
  	direction.setAttributeNS(null, "font-size", "8px");
  	direction.setAttributeNS(null, "font-family", "Helvetica");
  	direction.setAttributeNS(null, "fill", "rgba(192,192,192,1)");
	var textNode = document.createTextNode(displayText);
  	direction.appendChild(textNode);
  	svg.appendChild(direction);
}

function DirectionAngle(x, y, displayText) {
  	var anglen = document.createElementNS(svgNS, "text");
  	anglen.setAttributeNS(null, "x", x);
  	anglen.setAttributeNS(null, "y", y);
  	anglen.setAttributeNS(null, "font-size", "12px");
  	anglen.setAttributeNS(null, "font-family", "Helvetica");
  	anglen.setAttributeNS(null, "fill", "rgba(192,192,192,1)");  
  	anglen.setAttributeNS(null, "text-anchor", "middle");  
  	var textNode = document.createTextNode(displayText);
  	anglen.appendChild(textNode);
  	svg.appendChild(anglen);
}

var polypointer = document.createElementNS(svgNS, "polygon"); // wind direction arrow
	polypointer.setAttributeNS(null, "points", "70,22 75,2 70,8 65,2");
	polypointer.setAttributeNS(null, "fill", "rgba(0,127,255,1)"); // arch blue
	polypointer.setAttributeNS(null, "transform", "rotate("+ angle +", 70, 70)");
	svg.appendChild(polypointer);

} else {

var svgNS = "http://www.w3.org/2000/svg";

var svg = document.getElementById("compass");

	if (angle == 0) {
		var angle = 0;
	} else {
		var angle = "<?php echo $metar34windir;?>";
	}
    
	DirectionAngle(70, 74, angle + "°"); // Direction in degrees

	
	CardinalNorth(66.75, 29, "N");
	CardinalDirection(111, 73, "E");
	CardinalDirection(67, 116, "S");
	CardinalDirection(23, 73, "W");

for (var i = 0; i < 360; i += 2) {
  // draw degree lines
  var s = "rgba(230, 232, 239, 1)"; // silver
  if (i == 0 || i % 30 == 0) {
    w = 1;
    s = "rgba(255,99,71,1)"; // tomato
    y2 = 17;
  } else {
    w = 0.75;
    y2 = 17;
  }
  
var ticks = document.createElementNS(svgNS, "line");
	ticks.setAttributeNS(null, "x1", 70);
	ticks.setAttributeNS(null, "y1", 10);
	ticks.setAttributeNS(null, "x2", 70);
	ticks.setAttributeNS(null, "y2", y2);
	ticks.setAttributeNS(null, "stroke", s);
	ticks.setAttributeNS(null, "stroke-width", w);
	ticks.setAttributeNS(null, "stroke-linecap", "round");
	ticks.setAttributeNS(null, "transform", "rotate(" + i + ", 70, 70)");
	svg.appendChild(ticks);
  
  // draw degree value every 30 degrees
  if (i % 30 == 0) {
    var t1 = document.createElementNS(svgNS, "text");
    if (i > 100) {
      t1.setAttributeNS(null, "x", 64);
    } else if (i > 0) {
      t1.setAttributeNS(null, "x", 66);
    } else {
      t1.setAttributeNS(null, "x", 68.25);
    }
    t1.setAttributeNS(null, "y", 7);
    t1.setAttributeNS(null, "font-size", "6px");
    t1.setAttributeNS(null, "font-family", "Helvetica");
    t1.setAttributeNS(null, "fill", "rgba(147, 147, 147, 1)");
    t1.setAttributeNS(null, "style", "letter-spacing: 1.0");
    t1.setAttributeNS(null, "transform", "rotate(" + i + ", 70, 70)");
    var textNode = document.createTextNode(i);
    t1.appendChild(textNode);
    svg.appendChild(t1);
  }
}

function CardinalNorth(x, y, displayText) {
	var direction = document.createElementNS(svgNS, "text");
  	direction.setAttributeNS(null, "x", x);
  	direction.setAttributeNS(null, "y", y);
  	direction.setAttributeNS(null, "font-size", "9px");
  	direction.setAttributeNS(null, "font-weight", "bold");
  	direction.setAttributeNS(null, "font-family", "Helvetica");
  	direction.setAttributeNS(null, "fill", "red");
	var textNode = document.createTextNode(displayText);
  	direction.appendChild(textNode);
  	svg.appendChild(direction);
}

function CardinalDirection(x, y, displayText) {
  	var direction = document.createElementNS(svgNS, "text");
  	direction.setAttributeNS(null, "x", x);
  	direction.setAttributeNS(null, "y", y);
  	direction.setAttributeNS(null, "font-size", "8px");
  	direction.setAttributeNS(null, "font-family", "Helvetica");
  	direction.setAttributeNS(null, "fill", "silver");
  	var textNode = document.createTextNode(displayText);
  	direction.appendChild(textNode);
  	svg.appendChild(direction);
}

function DirectionAngle(x, y, displayText) {
  	var anglen = document.createElementNS(svgNS, "text");
  	anglen.setAttributeNS(null, "x", x);
  	anglen.setAttributeNS(null, "y", y);
  	anglen.setAttributeNS(null, "font-size", "12px");
  	anglen.setAttributeNS(null, "font-family", "Helvetica");
  	anglen.setAttributeNS(null, "fill", "silver");  
  	anglen.setAttributeNS(null, "text-anchor", "middle");  
  	var textNode = document.createTextNode(displayText);
  	anglen.appendChild(textNode);
  	svg.appendChild(anglen);
}

var polypointer = document.createElementNS(svgNS, "polygon"); // wind direction arrow
	polypointer.setAttributeNS(null, "points", "70,22 75,2 70,8 65,2");
	polypointer.setAttributeNS(null, "fill", "rgba(0,127,255,1)"); // arch blue
	polypointer.setAttributeNS(null, "transform", "rotate("+ angle +", 70, 70)");
	svg.appendChild(polypointer);
   
}

</script>
</body>
</html>
