<?php
include('dvmCombinedData.php');

$speedColor = $color["windSpeed"];
$gustColorMax = $color["windGust_max"];
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
<meta http-equiv="Content-Type: text/html; charset=UTF-8"/>

<div class="chartforecast">
<span class="yearpopup"><a alt="wind charts" title="wind charts" href="dvmWindRecords.php" data-lity><?php echo $menucharticonpage;?> Wind Records and Charts</a></span>
</div>
<span class='moduletitle'><?php echo $lang['Direction'];?> | <?php echo $lang['Windspeed'], " (<valuetitleunit>", $wind["units"];?></valuetitleunit>)</span>
<div class="updatedtime1"><span><?php if(file_exists($livedata)&&time() - filemtime($livedata)>300) echo $offline. '<offline> Offline </offline>'; else echo $online." ".$divum["time"];?></div><br />

<div class="windspeedtrend1">
<?php echo "<valuetext>Max "."<max><maxGust style='color:$gustColorMax;'>".number_format($wind["gust_max"],1)."</maxGust></max></span>"." ".$wind["units"]."<br> ".$lang['Gust']." (".$wind["gust_maxtime"].")</valuetext>";?></div>

<div class="windconverter2">
<?php
if($theme == 'dark') { 
// convert kmh to mph
if ($wind["units"]=="km/h"){echo "<div class=windconvertercircle style='color:$speedColor;'>".number_format($wind["speed"]*0.621371,1)." <smallrainunit>&nbsp;mph</smallrainunit>";}
// convert mph to kmh
else if ($wind["units"]=="mph"){echo "<div class=windconvertercircle style='color:$speedColor;'>".number_format($wind["speed"]*1.609343502101025,1)." <smallrainunit>&nbsp;km/h</smallrainunit>";}
// convert ms to kmh
else if ($wind["units"]=="m/s"){echo "<div class=windconvertercircle style='color:$speedColor;'>".number_format($wind["speed"]*3.60000288,1)." <smallrainunit>&nbsp;km/h</smallrainunit>";}
// knots to kmh
else if ($wind["units"]=="kts"){echo "<div class=windconvertercircle style='color:$speedColor;'>".number_format($wind["speed"]*1.85200,1)." <smallrainunit>&nbsp;km/h</smallrainunit>";}

} else {

// convert kmh to mph
if ($wind["units"]=="km/h"){echo "<div class=windconvertercircle style='background:$speedColor; color:$textColor;'>".number_format($wind["speed"]*0.621371,1)." <smallrainunit>&nbsp;mph</smallrainunit>";}
// convert mph to kmh
else if ($wind["units"]=="mph"){echo "<div class=windconvertercircle style='background:$speedColor; color:$textColor;'>".number_format($wind["speed"]*1.609343502101025,1)." <smallrainunit>&nbsp;km/h</smallrainunit>";}
// convert ms to kmh
else if ($wind["units"]=="m/s"){echo "<div class=windconvertercircle style='background:$speedColor; color:$textColor;'>".number_format($wind["speed"]*3.60000288,1)." <smallrainunit>&nbsp;km/h</smallrainunit>";}
// knots to kmh
else if ($wind["units"]=="kts"){echo "<div class=windconvertercircle style='background:$speedColor; color:$textColor;'>".number_format($wind["speed"]*1.85200,1)." <smallrainunit>&nbsp;km/h</smallrainunit>";}}?>
</div></div>
<?php 
if ($wind["units"] == 'mph'){$wind["wind_run"]=$wind["wind_run"]*0.621371;}
else if ($wind["units"] == 'kts'){$wind["wind_run"]=$wind["wind_run"]*0.621371;}
else {$wind["wind_run"]=$wind["wind_run"]*1;}

echo '<div class=divumwxwindrun>'.$windalert3.' &nbsp;<valuetext1>',number_format($wind["wind_run"],1);?></valuetext1>
<divumwxwindrunspan>
<?php if ($wind["units"] == 'mph') echo 'mi'; else if ($wind["units"] == 'm/s') echo 'km'; else if ($wind["units"] == 'kts') echo 'mi';else echo 'km';?></divumwxwindrunspan>
</div></div><br /><div class=windrun><?php echo $lang['Wind Run'];?></div>


<div class=divumwxbeaufort>
<?php 
if ($wind["speed_bft"] >= 12) {
  echo '<div class=divumwxbeaufort12>' . $beaufort12 . "&nbsp;" . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 11) {
  echo '<div class=divumwxbeaufort11>' . $beaufort11 . "&nbsp;" . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 10) {
  echo '<div class=divumwxbeaufort10>' . $beaufort10 . "&nbsp;" . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 9) {
  echo '<div class=divumwxbeaufort9>' . $beaufort9 . "&nbsp;" . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 8) {
  echo '<div class=divumwxbeaufort8>' . $beaufort8 . "&nbsp;" . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 7) {
  echo '<div class=divumwxbeaufort7>' . $beaufort7 . "&nbsp;" . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 6) {
  echo '<div class=divumwxbeaufort6>' . $beaufort6 . "&nbsp;" . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 5) {
  echo '<div class=divumwxbeaufort5>' . $beaufort5 . "&nbsp;" . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 4) {
  echo '<div class=divumwxbeaufort4>' . $beaufort4 . "&nbsp;" . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 3) {
  echo '<div class=divumwxbeaufort3>' . $beaufort3 . "&nbsp;" . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 2) {
  echo '<div class=divumwxbeaufort2>' . $beaufort2 . "&nbsp;" . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 1) {
  echo '<div class=divumwxbeaufort1>' . $beaufort1 . "&nbsp;" . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 0) {
  echo '<div class=divumwxbeaufort0>' . $beaufort0 . "&nbsp;" . $wind["speed_bft"];
}
?>
<divumwxbftspan>BFT<divumwxbftspan></div>
 
<div class="beaufort">
<?php
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
  echo "Near Gale ";
} else if ($wind["speed_bft"] == 8) {
  echo "Gale Force ";
} else if ($wind["speed_bft"] == 9) {
  echo "Strong Gale ";
} else if ($wind["speed_bft"] == 10) {
  echo "Storm Force ";
} else if ($wind["speed_bft"] == 11) {
  echo "Violent Storm ";
} else if ($wind["speed_bft"] >= 12) {
  echo "Hurricane Force ";
}
?>
</div></div>
   
<div class="vanilla-compass">
<svg id="vanilla-compass" width="300" height="140" viewBox="-80 0 300 140" xmlns="http://www.w3.org/2000/svg"></svg>
<!--svg id="vanilla-compass" width="300" height="140" viewBox="-80 0 300 140" style="border:1px solid red" xmlns="http://www.w3.org/2000/svg"></svg--> <!--don't touch used for positioning-->  
</div>


<script>
var theme = "<?php echo $theme;?>";

  if (theme === 'dark') {
    var ringColor = "#3b3c3f";
  } else {
    ringColor = "#c0c0c0";
  }

var svgNS = "http://www.w3.org/2000/svg";

var svg = document.getElementById("vanilla-compass");
   
var angle = "<?php echo $wind["direction"];?>";
angle = angle || 0;

var tenminAvD = "<?php echo $wind["direction_10m_avg"];?>";
tenminAvD = tenminAvD || 0; 

var units = "<?php echo $wind["units"];?>";

var windspeed = "<?php echo number_format($wind["speed"],1);?>";
windspeed = windspeed || 0;

// Beaufort color scale for wind speed
var wind_speed_color = "<?php echo $color["windSpeed"];?>";

var windgust = "<?php echo number_format($wind["gust"],1);?>";  
windgust = windgust || 0;

// Beaufort color scale for wind Gust
var wind_gust_color = "<?php echo $color["windGust"];?>";

var Bearing = "<?php echo $wind["direction"];?>";
Bearing = Bearing || "North";

 // Bearing  
  if (Bearing <= 11.25) { 
    Bearing = "North";
    } else if (Bearing <= 33.75) {
    Bearing = "NNE";
    } else if (Bearing <= 56.25) {
    Bearing = "NE";
    } else if (Bearing <= 78.75) {
    Bearing = "ENE";
    } else if (Bearing <= 101.25) {
    Bearing = "East";
    } else if (Bearing <= 123.75) { 
    Bearing = "ESE";
    } else if (Bearing <= 146.25) { 
    Bearing = "SE";
    } else if (Bearing <= 168.75) {
    Bearing = "SSE";
    } else if (Bearing <= 191.25) {
    Bearing = "South";
    } else if (Bearing <= 213.75) {
    Bearing = "SSW";
    } else if (Bearing <= 236.25) { 
    Bearing = "SW";
    } else if (Bearing <= 281.25) {
    Bearing = "West";
    } else if (Bearing <= 303.75) { 
    Bearing = "WNW";
    } else if (Bearing <= 326.25) {
    Bearing = "NW";
    } else if (Bearing <= 348.75) {
    Bearing = "NNW";
    } else { Bearing = "North"; }
	
	DirectionBearing(70, 74, Bearing); // Bearing

  Currently(-42, 51, "Currently");
  windSpeed(-43, 77, windspeed);
  unitsLeft(-42, 93, units);

  Gust(182, 51, "Gust");
  windGust(182, 77, windgust);
  unitsRight(182.25, 93, units);
        
	DirectionAngle(70, 58, angle + "\u00B0"); // Direction in degrees

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
  var s = ringColor; // dark grey
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
  	direction.setAttributeNS(null, "fill", "var(--col-6)");
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
  	anglen.setAttributeNS(null, "fill", "var(--col-6)");  
  	anglen.setAttributeNS(null, "text-anchor", "middle");  
  	var textNode = document.createTextNode(displayText);
  	anglen.appendChild(textNode);
  	svg.appendChild(anglen);
}

function DirectionBearing(x, y, displayText) {
  	var bearing = document.createElementNS(svgNS, "text");
  	bearing.setAttributeNS(null, "x", x);
  	bearing.setAttributeNS(null, "y", y);
  	bearing.setAttributeNS(null, "font-size", "12px");
  	bearing.setAttributeNS(null, "font-family", "Helvetica");
  	bearing.setAttributeNS(null, "fill", "var(--col-6)");  
  	bearing.setAttributeNS(null, "text-anchor", "middle");  
  	var textNode = document.createTextNode(displayText);
  	bearing.appendChild(textNode);
  	svg.appendChild(bearing);
}

function Currently(x, y, displayText) {
    var currently = document.createElementNS(svgNS, "text");
    currently.setAttributeNS(null, "x", x);
    currently.setAttributeNS(null, "y", y);
    currently.setAttributeNS(null, "font-size", "12px");
    currently.setAttributeNS(null, "font-family", "Helvetica");
    currently.setAttributeNS(null, "fill", "var(--col-6)");
    currently.setAttributeNS(null, "font-weight", "normal");  
    currently.setAttributeNS(null, "text-anchor", "middle");  
    var textNode = document.createTextNode(displayText);
    currently.appendChild(textNode);
    svg.appendChild(currently);
}

function windSpeed(x, y, displayText) {
    var windspeed = document.createElementNS(svgNS, "text");
    windspeed.setAttributeNS(null, "x", x);
    windspeed.setAttributeNS(null, "y", y);
    windspeed.setAttributeNS(null, "font-size", "24px");
    windspeed.setAttributeNS(null, "font-family", "Helvetica");
    windspeed.setAttributeNS(null, "fill", wind_speed_color);
    windspeed.setAttributeNS(null, "font-weight", "normal");  
    windspeed.setAttributeNS(null, "text-anchor", "middle");  
    var textNode = document.createTextNode(displayText);
    windspeed.appendChild(textNode);
    svg.appendChild(windspeed);
}

function Gust(x, y, displayText) {
    var gust = document.createElementNS(svgNS, "text");
    gust.setAttributeNS(null, "x", x);
    gust.setAttributeNS(null, "y", y);
    gust.setAttributeNS(null, "font-size", "12px");
    gust.setAttributeNS(null, "font-family", "Helvetica");
    gust.setAttributeNS(null, "fill", "var(--col-6)");
    gust.setAttributeNS(null, "font-weight", "normal");  
    gust.setAttributeNS(null, "text-anchor", "middle");  
    var textNode = document.createTextNode(displayText);
    gust.appendChild(textNode);
    svg.appendChild(gust);
}

function windGust(x, y, displayText) {
    var windgust = document.createElementNS(svgNS, "text");
    windgust.setAttributeNS(null, "x", x);
    windgust.setAttributeNS(null, "y", y);
    windgust.setAttributeNS(null, "font-size", "24px");
    windgust.setAttributeNS(null, "font-family", "Helvetica");
    windgust.setAttributeNS(null, "fill", wind_gust_color);
    windgust.setAttributeNS(null, "font-weight", "normal");  
    windgust.setAttributeNS(null, "text-anchor", "middle");  
    var textNode = document.createTextNode(displayText);
    windgust.appendChild(textNode);
    svg.appendChild(windgust);
}

function unitsLeft(x, y, displayText) {
    var unitsleft = document.createElementNS(svgNS, "text");
    unitsleft.setAttributeNS(null, "x", x);
    unitsleft.setAttributeNS(null, "y", y);
    unitsleft.setAttributeNS(null, "font-size", "12px");
    unitsleft.setAttributeNS(null, "font-family", "Helvetica");
    unitsleft.setAttributeNS(null, "fill", "var(--col-6)");
    unitsleft.setAttributeNS(null, "font-weight", "normal");  
    unitsleft.setAttributeNS(null, "text-anchor", "middle");  
    var textNode = document.createTextNode(displayText);
    unitsleft.appendChild(textNode);
    svg.appendChild(unitsleft);
}

function unitsRight(x, y, displayText) {
    var unitsright = document.createElementNS(svgNS, "text");
    unitsright.setAttributeNS(null, "x", x);
    unitsright.setAttributeNS(null, "y", y);
    unitsright.setAttributeNS(null, "font-size", "12px");
    unitsright.setAttributeNS(null, "font-family", "Helvetica");
    unitsright.setAttributeNS(null, "fill", "var(--col-6)");
    unitsright.setAttributeNS(null, "font-weight", "normal");  
    unitsright.setAttributeNS(null, "text-anchor", "middle");  
    var textNode = document.createTextNode(displayText);
    unitsright.appendChild(textNode);
    svg.appendChild(unitsright);
}

var polypointer = document.createElementNS(svgNS, "polygon"); // wind direction arrow
	polypointer.setAttributeNS(null, "points", "70,22 75,2 70,8 65,2"); 
	polypointer.setAttributeNS(null, "fill", "rgba(0,127,255,1)");// arch blue
  polypointer.setAttributeNS(null, "ease", "easeInOut 1s");
	polypointer.setAttributeNS(null, "transform", "rotate("+ angle +", 70, 70)");
	svg.appendChild(polypointer);

</script>

</div>
</html>
