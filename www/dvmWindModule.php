<?php 
require('dvmCombinedData.php');
include('userSettings.php');
if ($wind["units"] == "kts") { echo $wind["units"] = "kts"; }
?>
<meta http-equiv="Content-Type: text/html; charset=UTF-8"/>

   <div class="chartforecast2">
      <span class="yearpopup"><a alt="wind charts" title="wind charts" href="dvmMenuWind.php" data-lity><?php echo $menucharticonpage;?> Wind Almanac and Charts</a></span>
    </div>
    <span class='moduletitle2'><?php echo $lang['Direction'];?> | <?php echo $lang['Windspeed'], " (<valuetitleunit>", $wind["units"];?></valuetitleunit>)</span>
  

<div class="updatedtime2"><span><?php if(file_exists($livedata)&&time() - filemtime($livedata)>300) echo $offline. '<offline> Offline </offline>'; else echo $online." ".$divum["time"];?></div><br />

<div class="windspeedvalues"><div class="windspeedvalue">
<?php  
// windspeed instantaneous
if ($wind["speed"]<10){echo "&nbsp;".number_format($wind["speed"],1);}else echo number_format($wind["speed"],1);?>
<div class="windunitidspeed"><?php echo $lang['Currently'];?></div><div class="windunitspeed"><?php echo $wind["units"]?></div></div>
<div class="windgustvalue">
<?php 
// windgust
if ($wind["gust"]*$toKnots>=26.9978){echo "<windred>",number_format($wind["gust"],1),"</span>";}else if ($wind["gust"]*$toKnots>=21.5983){echo "<windorange>",number_format($wind["gust"],1),"</span>";}
else if ($wind["gust"]*$toKnots>=16.1987){echo "<windgreen>",number_format($wind["gust"],1),"</span>";}else if ($wind["gust"]<10){echo "&nbsp;",number_format($wind["gust"],1);}else echo number_format($wind["gust"],1);?>
<div class="windunitgust2"><?php echo  $wind["units"]?></div>
<div class="windunitidgust"><?php echo $lang['Gust']; ?></div></span></div></div>
<div class="windspeedtrend1">
<?php echo "<valuetext>Max "."<max><value><maxred>".number_format($wind["gust_max"],1)."</maxred></max></span>"."<supmb> ".$wind["units"]."</supmb><br> ".$lang['Gust']." (".$wind["gust_maxtime"].")</valuetext>";?></div>
<div class="windconverter"><?php

if($theme == 'dark') { 
//divumwx-convert kmh to mph
if ($wind["units"]=="km/h" && $wind["gust"]*$toKnots>=26.9978){echo "<div class=windconvertercirclered1><tred>".number_format($wind["gust"]*0.621371,1)." </tred><smallrainunit>mph</smallrainunit>";}
else if ($wind["units"]=="km/h" && $wind["gust"]*$toKnots>=21.5983){echo "<div class=windconvertercircleorange1><torange>".number_format($wind["gust"]*0.621371,1)." </torange><smallrainunit>&nbsp;mph</smallrainunit>";}
else if ($wind["units"]=="km/h" && $wind["gust"]*$toKnots>=16.1987){echo "<div class=windconvertercirclegreen1><tgreen>".number_format($wind["gust"]*0.621371,1)." </tgreen><smallrainunit>&nbsp;mph</smallrainunit>";}
else if ($wind["units"]=="km/h" && $wind["gust"]*$toKnots<16.1987){echo "<div class=windconvertercircleblue1><tblue>".number_format($wind["gust"]*0.621371,1)." </tblue><smallrainunit>&nbsp;mph</smallrainunit>";}
//divumwx-convert mph to kmh
else if ($wind["units"]=="mph" && $wind["gust"]*$toKnots>=26.9978){echo "<div class=windconvertercirclered1><tred>".number_format($wind["gust"]*1.609343502101025,1)." </tred><smallrainunit>&nbsp;kmh</smallrainunit>";}
else if ($wind["units"]=="mph" && $wind["gust"]*$toKnots>=21.5983){echo "<div class=windconvertercircleorange1><torange>".number_format($wind["gust"]*1.609343502101025,1)." </torange><smallrainunit>&nbsp;kmh</smallrainunit>";}
else if ($wind["units"]=="mph" && $wind["gust"]*$toKnots>=16.1987){echo "<div class=windconvertercircleblue1><tgreen>".number_format($wind["gust"]*1.609343502101025,1)." </tgreen><smallrainunit>&nbsp;kmh</smallrainunit>";}
else if ($wind["units"]=="mph" && $wind["gust"]*$toKnots<16.1987){echo "<div class=windconvertercirclegreen1><tblue>".number_format($wind["gust"]*1.609343502101025,1)." </tblue><smallrainunit>&nbsp;kmh</smallrainunit>";}
//divumwx-convert ms to kmh
else if ($wind["units"]=="m/s" && $wind["gust"]*$toKnots>=26.9978){echo "<div class=windconvertercirclered1><tred>".number_format($wind["gust"]*3.60000288,1)." </tred><smallrainunit>&nbsp;kmh</smallrainunit>";}
else if ($wind["units"]=="m/s" && $wind["gust"]*$toKnots>=21.5983){echo "<div class=windconvertercircleorange1><torange>".number_format($wind["gust"]*3.60000288,1)." </torange><smallrainunit>&nbsp;kmh</smallrainunit>";}
else if ($wind["units"]=="m/s" && $wind["gust"]*$toKnots>=16.1987){echo "<div class=windconvertercircleblue1><tgreen>".number_format($wind["gust"]*3.60000288,1)." </tgreen><smallrainunit>&nbsp;kmh</smallrainunit>";}
else if ($wind["units"]=="m/s" && $wind["gust"]*$toKnots<16.1987){echo "<div class=windconvertercirclegreen1><tblue>".number_format($wind["gust"]*3.60000288,1)." </tblue><smallrainunit>&nbsp;kmh</smallrainunit>";}

} else {

// convert kmh to mph
if ($wind["units"]=="km/h" && $wind["gust"]*$toKnots>=26.9978){echo "<div class=windconvertercirclered1><weathertext2>".number_format($wind["gust"]*0.621371,1)." </weathertext2><smallrainunit>mph</smallrainunit>";}
else if ($wind["units"]=="km/h" && $wind["gust"]*$toKnots>=21.5983){echo "<div class=windconvertercircleorange1><weathertext2>".number_format($wind["gust"]*0.621371,1)." </weathertext2><smallrainunit>&nbsp;mph</smallrainunit>";}
else if ($wind["units"]=="km/h" && $wind["gust"]*$toKnots>=16.1987){echo "<div class=windconvertercirclegreen1><weathertext2>".number_format($wind["gust"]*0.621371,1)." </weathertext2><smallrainunit>&nbsp;mph</smallrainunit>";}
else if ($wind["units"]=="km/h" && $wind["gust"]*$toKnots<16.1987){echo "<div class=windconvertercircleblue1><weathertext2>".number_format($wind["gust"]*0.621371,1)." </weathertext2><smallrainunit>&nbsp;mph</smallrainunit>";}
// convert mph to kmh
else if ($wind["units"]=="mph" && $wind["gust"]*$toKnots>=26.9978){echo "<div class=windconvertercirclered1><weathertext2>".number_format($wind["gust"]*1.609343502101025,1)." </weathertext2><smallrainunit>&nbsp;kmh</smallrainunit>";}
else if ($wind["units"]=="mph" && $wind["gust"]*$toKnots>=21.5983){echo "<div class=windconvertercircleorange1><weathertext2>".number_format($wind["gust"]*1.609343502101025,1)." </weathertext2><smallrainunit>&nbsp;kmh</smallrainunit>";}
else if ($wind["units"]=="mph" && $wind["gust"]*$toKnots>=16.1987){echo "<div class=windconvertercircleblue1><weathertext2>".number_format($wind["gust"]*1.609343502101025,1)." </weathertext2><smallrainunit>&nbsp;kmh</smallrainunit>";}
else if ($wind["units"]=="mph" && $wind["gust"]*$toKnots<16.1987){echo "<div class=windconvertercirclegreen1><weathertext2>".number_format($wind["gust"]*1.609343502101025,1)." </weathertext2><smallrainunit>&nbsp;kmh</smallrainunit>";}
// convert ms to kmh
else if ($wind["units"]=="m/s" && $wind["gust"]*$toKnots>=26.9978){echo "<div class=windconvertercirclered1><weathertext2>".number_format($wind["gust"]*3.60000288,1)." </weathertext2><smallrainunit>&nbsp;kmh</smallrainunit>";}
else if ($wind["units"]=="m/s" && $wind["gust"]*$toKnots>=21.5983){echo "<div class=windconvertercircleorange1><weathertext2>".number_format($wind["gust"]*3.60000288,1)." </weathertext2><smallrainunit>&nbsp;kmh</smallrainunit>";}
else if ($wind["units"]=="m/s" && $wind["gust"]*$toKnots>=16.1987){echo "<div class=windconvertercircleblue1><weathertext2>".number_format($wind["gust"]*3.60000288,1)." </weathertext2><smallrainunit>&nbsp;kmh</smallrainunit>";}
else if ($wind["units"]=="m/s" && $wind["gust"]*$toKnots<16.1987){echo "<div class=windconvertercirclegreen1><weathertext2>".number_format($wind["gust"]*3.60000288,1)." </weathertext2><smallrainunit>&nbsp;kmh</smallrainunit>";}}?>
</div></div>
<?php 
if ($wind["units"] == 'mph'){$wind["wind_run"]=$wind["wind_run"]*0.621371;}
else if ($wind["units"] == 'kts'){$wind["wind_run"]=$wind["wind_run"]*0.621371;}
else {$wind["wind_run"]=$wind["wind_run"]*1;}

echo ' <div class=weather34windrun>'.$windalert3.' &nbsp;<grey><valuetext1>',number_format($wind["wind_run"],1);?>
<grey><weather34windrunspan></valuetext>
<?php if ($wind["units"] == 'mph') echo 'mi'; else if ($wind["units"] == 'm/s') echo 'km'; else if ($wind["units"] == 'kts') echo 'mi';else echo 'km';?></weather34windrunspan>
</div></div><br /><div class=windrun1><?php echo  $lang['Wind Run'];?></div>
<?php // beaufort
if ($wind["speed_bft"] >= 12) {
  echo '<div class=weather34beaufort6>' . $beaufort12 . "&nbsp; " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 11) {
  echo '<div class=weather34beaufort6>' . $beaufort11 . "&nbsp; " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 10) {
  echo '<div class=weather34beaufort6>' . $beaufort10 . "&nbsp; " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 9) {
  echo '<div class=weather34beaufort6>' . $beaufort9 . "&nbsp; " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 8) {
  echo '<div class=weather34beaufort6>' . $beaufort8 . "&nbsp; " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 7) {
  echo '<div class=weather34beaufort6>' . $beaufort7 . "&nbsp; " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 6) {
  echo '<div class=weather34beaufort6>' . $beaufort6 . "&nbsp; " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 5) {
  echo '<div class=weather34beaufort4-5>' . $beaufort5 . "&nbsp; " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 4) {
  echo '<div class=weather34beaufort4-5>' . $beaufort4 . "&nbsp; " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 3) {
  echo '<div class=weather34beaufort3-4>' . $beaufort3 . "&nbsp; " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 2) {
  echo '<div class=weather34beaufort1-3>' . $beaufort2 . "&nbsp; " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 1) {
  echo '<div class=weather34beaufort1-3>' . $beaufort1 . "&nbsp; " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 0) {
  echo '<div class=weather34beaufort1-3>' . $beaufort0 . "&nbsp; " . $wind["speed_bft"];
}
?>
<weather34bftspan>BFT<weather34bftspan></div>
<div class="beaufort1"><?php
if ($wind["speed_bft"] == 0) {
  echo "Calm";
} else if ($wind["speed_bft"] == 1) {
  echo "Light Air";
} else if ($wind["speed_bft"] == 2) {
  echo "Light Breeze";
} else if ($wind["speed_bft"] == 3) {
  echo "Gentle Breeze";
} else if ($wind["speed_bft"] == 4) {
  echo "Moderate Breeze";
} else if ($wind["speed_bft"] == 5) {
  echo "Fresh Breeze";
} else if ($wind["speed_bft"] == 6) {
  echo "Strong Breeze";
} else if ($wind["speed_bft"] == 7) {
  echo "Near Gale " . $alert . "";
} else if ($wind["speed_bft"] == 8) {
  echo "Gale Force " . $alert . "";
} else if ($wind["speed_bft"] == 9) {
  echo "Strong Gale " . $alert . "";
} else if ($wind["speed_bft"] == 10) {
  echo "Storm Force " . $alert . "";
} else if ($wind["speed_bft"] == 11) {
  echo "Violent Storm " . $alert . "";
} else if ($wind["speed_bft"] >= 12) {
  echo "Hurricane Force " . $alert . "";
}
?>
</div>

<style>
.compass1 {
  margin-top: -31px;
  margin-left: 0px;
}
</style>
<!DOCTYPE html>
<html>
<div class="compass1">
<svg id="compass" width="140" height="140" viewBox="0 0 140 140" xmlns="http://www.w3.org/2000/svg"></svg>  
</div>

 
<script>

var svgNS = "http://www.w3.org/2000/svg";

var svg = document.getElementById("compass");

var theme = "<?php echo $theme;?>";

	if (theme == 'dark') {
   
var angle = "<?php echo $wind["direction"];?>";
angle = angle || 0;

var tenminAvD = "<?php echo $wind["direction_10m_avg"];?>";
tenminAvD = tenminAvD || 0;

var cardinal = "<?php echo $wind["cardinal"];?>";
cardinal = cardinal || 0;
    
    
	DirectionAngle(70, 58, angle + "°"); // Direction in degrees
	DirectionCardinal(70, 74, cardinal); // Direction in text

	CardinalNorth(66.75, 29, "N");
	CardinalDirection(111, 72.75, "E");
	CardinalDirection(67, 116, "S");
	CardinalDirection(23, 72.75, "W");
	
var polygon3 = document.createElementNS(svgNS, "polygon"); // 10 minute avarage direction arrow
	polygon3.setAttributeNS(null, "points", "70,43 73,32 70,35 67,32");
	polygon3.setAttributeNS(null, "fill", "rgba(46,139,87,1)"); // earth green
	polygon3.setAttributeNS(null, "transform", "rotate("+ tenminAvD +", 70, 70)");
	svg.appendChild(polygon3);

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
      t1.setAttributeNS(null, "x", 62.50);
    } else if (i > 0) {
      t1.setAttributeNS(null, "x", 65);
    } else {
      t1.setAttributeNS(null, "x", 67.75);
    }
    t1.setAttributeNS(null, "y", 7);
    t1.setAttributeNS(null, "font-size", "8px");
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

function DirectionCardinal(x, y, displayText) {
  	var anglet = document.createElementNS(svgNS, "text");
  	anglet.setAttributeNS(null, "x", x);
  	anglet.setAttributeNS(null, "y", y);
  	anglet.setAttributeNS(null, "font-size", "12px");
  	anglet.setAttributeNS(null, "font-family", "Helvetica");
  	anglet.setAttributeNS(null, "fill", "rgba(192,192,192,1)");  
  	anglet.setAttributeNS(null, "text-anchor", "middle");  
  	var textNode = document.createTextNode(displayText);
  	anglet.appendChild(textNode);
  	svg.appendChild(anglet);
}

var polypointer = document.createElementNS(svgNS, "polygon"); // wind direction arrow
	polypointer.setAttributeNS(null, "points", "70,22 75,2 70,8 65,2"); 
	polypointer.setAttributeNS(null, "fill", "rgba(0,127,255,1)");// arch blue
	polypointer.setAttributeNS(null, "transform", "rotate("+ angle +", 70, 70)");
	svg.appendChild(polypointer);
				
} else {

var svgNS = "http://www.w3.org/2000/svg";

var svg = document.getElementById("compass");

var angle = "<?php echo $wind["direction"];?>";
angle = angle || 0;

var tenminAvD = "<?php echo $wind["direction_10m_avg"];?>";
tenminAvD = tenminAvD || 0;

var cardinal = "<?php echo $wind["cardinal"];?>";
cardinal = cardinal || 0;

    
	DirectionAngle(70, 58, angle + "°"); // Direction in degrees
	DirectionCardinal(70, 74, cardinal); // Direction in text
	
	CardinalNorth(66.75, 29, "N");
	CardinalDirection(111, 73, "E");
	CardinalDirection(67, 116, "S");
	CardinalDirection(23, 73, "W");
		
var polygon3 = document.createElementNS(svgNS, "polygon"); // 10 minute avarage direction arrow
	polygon3.setAttributeNS(null, "points", "70,43 73,32 70,35 67,32");
	polygon3.setAttributeNS(null, "fill", "rgba(46,139,87,1)"); // earth green
	polygon3.setAttributeNS(null, "transform", "rotate("+ tenminAvD +", 70, 70)");
	svg.appendChild(polygon3);

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
      t1.setAttributeNS(null, "x", 62.50);
    } else if (i > 0) {
      t1.setAttributeNS(null, "x", 65);
    } else {
      t1.setAttributeNS(null, "x", 67.75);
    }
    t1.setAttributeNS(null, "y", 7);
    t1.setAttributeNS(null, "font-size", "8px");
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

function DirectionCardinal(x, y, displayText) {
  	var anglet = document.createElementNS(svgNS, "text");
  	anglet.setAttributeNS(null, "x", x);
  	anglet.setAttributeNS(null, "y", y);
  	anglet.setAttributeNS(null, "font-size", "12px");
  	anglet.setAttributeNS(null, "font-family", "Helvetica");
  	anglet.setAttributeNS(null, "fill", "silver");  
  	anglet.setAttributeNS(null, "text-anchor", "middle");  
  	var textNode = document.createTextNode(displayText);
  	anglet.appendChild(textNode);
  	svg.appendChild(anglet);
}
 
var polypointer = document.createElementNS(svgNS, "polygon"); // wind direction arrow
	polypointer.setAttributeNS(null, "points", "70,22 75,2 70,8 65,2");
	polypointer.setAttributeNS(null, "fill", "rgba(0,127,255,1)"); // arch blue
	polypointer.setAttributeNS(null, "transform", "rotate("+ angle +", 70, 70)");
	svg.appendChild(polypointer);
   
}

</script>

</div>
</html>
