<?php 
include('dvmCombinedData.php');
//include('userSettings.php');
if ($wind["units"] == "kts") { echo $wind["units"] = "kts"; }


if ($theme === "dark") { echo
    
    '<style>

    .divumwxbeaufort0 {
    color: #85a3aa;
  }
  .divumwxbeaufort1 {
    color: #7e98bb;
  }
  .divumwxbeaufort2 {
    color: #6e90d0;
  }
  .divumwxbeaufort3 {
    color: #0f94a7;
  }
  .divumwxbeaufort4 {
    color: #39a239;
  }
  .divumwxbeaufort5 {
    color: #c2863e;
  }
  .divumwxbeaufort6 {
    color: #c8420d;
  }
  .divumwxbeaufort7 {
    color: #d20032;
  }
  .divumwxbeaufort8 {
    color: #af5088;
  }
  .divumwxbeaufort9 {
    color: #754a92;
  }
  .divumwxbeaufort10 {
    color: #45698d;
  }
  .divumwxbeaufort11 {
    color: #c1fc77;
  }
  .divumwxbeaufort12 {
    color: #f1ff6c;
  }
  .divumwxbeaufort0,
  .divumwxbeaufort1,
  .divumwxbeaufort2,
  .divumwxbeaufort3,
  .divumwxbeaufort4,
  .divumwxbeaufort5,
  .divumwxbeaufort6,
  .divumwxbeaufort7,
  .divumwxbeaufort8,
  .divumwxbeaufort9,
  .divumwxbeaufort10,
  .divumwxbeaufort11,
  .divumwxbeaufort12 {
    font-size: .7rem;
    position: absolute;
    margin-top: 85px;
    margin-left: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 1rem;
    width: 4rem;
    border: 1px solid rgba(74, 99, 111, .2);
    overflow: hidden;
    border-radius: 2px;
    font-family: weathertext2;
  }

    </style>';
  
  } else { echo
  
    '<style>

       .divumwxbeaufort0 {
    background: #85a3aa;
  }
  .divumwxbeaufort1 {
    background: #7e98bb;
  }
  .divumwxbeaufort2 {
    background: #6e90d0;
  }
  .divumwxbeaufort3 {
    background: #0f94a7;
  }
  .divumwxbeaufort4 {
    background: #39a239;
  }
  .divumwxbeaufort5 {
    background: #c2863e;
  }
  .divumwxbeaufort6 {
    background: #c8420d;
  }
  .divumwxbeaufort7 {
    background: #d20032;
  }
  .divumwxbeaufort8 {
    background: #af5088;
  }
  .divumwxbeaufort9 {
    background: #754a92;
  }
  .divumwxbeaufort10 {
    background: #45698d;
  }
  .divumwxbeaufort11 {
    background: #c1fc77;
  }
  .divumwxbeaufort12 {
    background: #f1ff6c;
  }
  .divumwxbeaufort0,
  .divumwxbeaufort1,
  .divumwxbeaufort2,
  .divumwxbeaufort3,
  .divumwxbeaufort4,
  .divumwxbeaufort5,
  .divumwxbeaufort6,
  .divumwxbeaufort7,
  .divumwxbeaufort8,
  .divumwxbeaufort9,
  .divumwxbeaufort10,
  .divumwxbeaufort11,
  .divumwxbeaufort12 {
    font-size: .7rem;
    position: absolute;
    margin-top: 85px;
    margin-left: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 1.05rem;
    width: 4rem;
    border: 1px solid #e6e8ef;
    overflow: hidden;
    border-radius: 2px;
    color: #fff;
    font-family: weathertext2
  }

    </style>';
  
}
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
if ($wind["speed"]<10){echo " ".number_format($wind["speed"],1);}else echo number_format($wind["speed"],1);?>
<div class="windunitidspeed"><?php echo $lang['Currently'];?></div><div class="windunitspeed"><?php echo $wind["units"]?></div></div>
<div class="windgustvalue">
<?php 
// windgust
if ($wind["gust"]*$toKnots>=26.9978){echo "<windred>",number_format($wind["gust"],1),"</span>";}else if ($wind["gust"]*$toKnots>=21.5983){echo "<windorange>",number_format($wind["gust"],1),"</span>";}
else if ($wind["gust"]*$toKnots>=16.1987){echo "<windgreen>",number_format($wind["gust"],1),"</span>";}else if ($wind["gust"]<10){echo " ",number_format($wind["gust"],1);}else echo number_format($wind["gust"],1);?>
<div class="windunitgust2"><?php echo  $wind["units"]?></div>
<div class="windunitidgust"><?php echo $lang['Gust']; ?></div></span></div></div>
<div class="windspeedtrend1">
<?php echo "<valuetext>Max "."<max><value><maxred>".number_format($wind["gust_max"],1)."</maxred></max></span>"."<supmb> ".$wind["units"]."</supmb><br> ".$lang['Gust']." (".$wind["gust_maxtime"].")</valuetext>";?></div>
<div class="windconverter"><?php

if($theme == 'dark') { 
//divumwx-convert kmh to mph
if ($wind["units"]=="km/h" && $wind["gust"]*$toKnots>=26.9978){echo "<div class=windconvertercirclered1><tred>".number_format($wind["gust"]*0.621371,1)." </tred><smallrainunit>mph</smallrainunit>";}
else if ($wind["units"]=="km/h" && $wind["gust"]*$toKnots>=21.5983){echo "<div class=windconvertercircleorange1><torange>".number_format($wind["gust"]*0.621371,1)." </torange><smallrainunit> mph</smallrainunit>";}
else if ($wind["units"]=="km/h" && $wind["gust"]*$toKnots>=16.1987){echo "<div class=windconvertercirclegreen1><tgreen>".number_format($wind["gust"]*0.621371,1)." </tgreen><smallrainunit> mph</smallrainunit>";}
else if ($wind["units"]=="km/h" && $wind["gust"]*$toKnots<16.1987){echo "<div class=windconvertercircleblue1><tblue>".number_format($wind["gust"]*0.621371,1)." </tblue><smallrainunit> mph</smallrainunit>";}
//divumwx-convert mph to kmh
else if ($wind["units"]=="mph" && $wind["gust"]*$toKnots>=26.9978){echo "<div class=windconvertercirclered1><tred>".number_format($wind["gust"]*1.609343502101025,1)." </tred><smallrainunit> kmh</smallrainunit>";}
else if ($wind["units"]=="mph" && $wind["gust"]*$toKnots>=21.5983){echo "<div class=windconvertercircleorange1><torange>".number_format($wind["gust"]*1.609343502101025,1)." </torange><smallrainunit> kmh</smallrainunit>";}
else if ($wind["units"]=="mph" && $wind["gust"]*$toKnots>=16.1987){echo "<div class=windconvertercircleblue1><tgreen>".number_format($wind["gust"]*1.609343502101025,1)." </tgreen><smallrainunit> kmh</smallrainunit>";}
else if ($wind["units"]=="mph" && $wind["gust"]*$toKnots<16.1987){echo "<div class=windconvertercirclegreen1><tblue>".number_format($wind["gust"]*1.609343502101025,1)." </tblue><smallrainunit> kmh</smallrainunit>";}
//divumwx-convert ms to kmh
else if ($wind["units"]=="m/s" && $wind["gust"]*$toKnots>=26.9978){echo "<div class=windconvertercirclered1><tred>".number_format($wind["gust"]*3.60000288,1)." </tred><smallrainunit> kmh</smallrainunit>";}
else if ($wind["units"]=="m/s" && $wind["gust"]*$toKnots>=21.5983){echo "<div class=windconvertercircleorange1><torange>".number_format($wind["gust"]*3.60000288,1)." </torange><smallrainunit> kmh</smallrainunit>";}
else if ($wind["units"]=="m/s" && $wind["gust"]*$toKnots>=16.1987){echo "<div class=windconvertercircleblue1><tgreen>".number_format($wind["gust"]*3.60000288,1)." </tgreen><smallrainunit> kmh</smallrainunit>";}
else if ($wind["units"]=="m/s" && $wind["gust"]*$toKnots<16.1987){echo "<div class=windconvertercirclegreen1><tblue>".number_format($wind["gust"]*3.60000288,1)." </tblue><smallrainunit> kmh</smallrainunit>";}

} else {

// convert kmh to mph
if ($wind["units"]=="km/h" && $wind["gust"]*$toKnots>=26.9978){echo "<div class=windconvertercirclered1><weathertext2>".number_format($wind["gust"]*0.621371,1)." </weathertext2><smallrainunit>mph</smallrainunit>";}
else if ($wind["units"]=="km/h" && $wind["gust"]*$toKnots>=21.5983){echo "<div class=windconvertercircleorange1><weathertext2>".number_format($wind["gust"]*0.621371,1)." </weathertext2><smallrainunit> mph</smallrainunit>";}
else if ($wind["units"]=="km/h" && $wind["gust"]*$toKnots>=16.1987){echo "<div class=windconvertercirclegreen1><weathertext2>".number_format($wind["gust"]*0.621371,1)." </weathertext2><smallrainunit> mph</smallrainunit>";}
else if ($wind["units"]=="km/h" && $wind["gust"]*$toKnots<16.1987){echo "<div class=windconvertercircleblue1><weathertext2>".number_format($wind["gust"]*0.621371,1)." </weathertext2><smallrainunit> mph</smallrainunit>";}
// convert mph to kmh
else if ($wind["units"]=="mph" && $wind["gust"]*$toKnots>=26.9978){echo "<div class=windconvertercirclered1><weathertext2>".number_format($wind["gust"]*1.609343502101025,1)." </weathertext2><smallrainunit> kmh</smallrainunit>";}
else if ($wind["units"]=="mph" && $wind["gust"]*$toKnots>=21.5983){echo "<div class=windconvertercircleorange1><weathertext2>".number_format($wind["gust"]*1.609343502101025,1)." </weathertext2><smallrainunit> kmh</smallrainunit>";}
else if ($wind["units"]=="mph" && $wind["gust"]*$toKnots>=16.1987){echo "<div class=windconvertercircleblue1><weathertext2>".number_format($wind["gust"]*1.609343502101025,1)." </weathertext2><smallrainunit> kmh</smallrainunit>";}
else if ($wind["units"]=="mph" && $wind["gust"]*$toKnots<16.1987){echo "<div class=windconvertercirclegreen1><weathertext2>".number_format($wind["gust"]*1.609343502101025,1)." </weathertext2><smallrainunit> kmh</smallrainunit>";}
// convert ms to kmh
else if ($wind["units"]=="m/s" && $wind["gust"]*$toKnots>=26.9978){echo "<div class=windconvertercirclered1><weathertext2>".number_format($wind["gust"]*3.60000288,1)." </weathertext2><smallrainunit> kmh</smallrainunit>";}
else if ($wind["units"]=="m/s" && $wind["gust"]*$toKnots>=21.5983){echo "<div class=windconvertercircleorange1><weathertext2>".number_format($wind["gust"]*3.60000288,1)." </weathertext2><smallrainunit> kmh</smallrainunit>";}
else if ($wind["units"]=="m/s" && $wind["gust"]*$toKnots>=16.1987){echo "<div class=windconvertercircleblue1><weathertext2>".number_format($wind["gust"]*3.60000288,1)." </weathertext2><smallrainunit> kmh</smallrainunit>";}
else if ($wind["units"]=="m/s" && $wind["gust"]*$toKnots<16.1987){echo "<div class=windconvertercirclegreen1><weathertext2>".number_format($wind["gust"]*3.60000288,1)." </weathertext2><smallrainunit> kmh</smallrainunit>";}}?>
</div></div>
<?php 
if ($wind["units"] == 'mph'){$wind["wind_run"]=$wind["wind_run"]*0.621371;}
else if ($wind["units"] == 'kts'){$wind["wind_run"]=$wind["wind_run"]*0.621371;}
else {$wind["wind_run"]=$wind["wind_run"]*1;}

echo ' <div class=divumwxwindrun>'.$windalert3.'  <grey><valuetext1>',number_format($wind["wind_run"],1);?>
<grey><divumwxwindrunspan></valuetext>
<?php if ($wind["units"] == 'mph') echo 'mi'; else if ($wind["units"] == 'm/s') echo 'km'; else if ($wind["units"] == 'kts') echo 'mi';else echo 'km';?></divumwxwindrunspan>
</div></div><br /><div class=windrun1><?php echo  $lang['Wind Run'];?></div>
<?php // beaufort
if ($wind["speed_bft"] >= 12) {
  echo '<div class=divumwxbeaufort12>' . $beaufort12 . "  " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 11) {
  echo '<div class=divumwxbeaufort11>' . $beaufort11 . "  " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 10) {
  echo '<div class=divumwxbeaufort10>' . $beaufort10 . "  " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 9) {
  echo '<div class=divumwxbeaufort9>' . $beaufort9 . "  " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 8) {
  echo '<div class=divumwxbeaufort8>' . $beaufort8 . "  " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 7) {
  echo '<div class=divumwxbeaufort7>' . $beaufort7 . "  " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 6) {
  echo '<div class=divumwxbeaufort6>' . $beaufort6 . "  " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 5) {
  echo '<div class=divumwxbeaufort5>' . $beaufort5 . "  " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 4) {
  echo '<div class=divumwxbeaufort4>' . $beaufort4 . "  " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 3) {
  echo '<div class=divumwxbeaufort3>' . $beaufort3 . "  " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 2) {
  echo '<div class=divumwxbeaufort2>' . $beaufort2 . "  " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 1) {
  echo '<div class=divumwxbeaufort1>' . $beaufort1 . "  " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 0) {
  echo '<div class=divumwxbeaufort0>' . $beaufort0 . "  " . $wind["speed_bft"];
}
?>
<divumwxbftspan>BFT<divumwxbftspan></div>
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
.wrap {
  position: relative;
  margin-top: -2px;
  margin-right: 0px;
}
.moduletitle2 {
  position: relative;
  top: -20px;
  font-size: .8em;
  float: none;
}
.chartforecast2 {
  position: absolute;
  font-family: arial, system;
  z-index: 20;
  padding-top: 1px;
  margin-left: 0;
  font-size: .67em;
  color: silver;
  margin-top: 159px;
  width: 300px;
  padding-left: 10px;
  text-align: left;
}
.chartforecast2:hover {
  color: #90b12a;
}
.daylightmoduleposition2 {
  position: relative;
  left: 5px;
  margin-top: 0px;
}
</style>    
<!DOCTYPE html>
<html>
<div class="compass1">
<svg id="compass" width="140" height="140" viewBox="0 0 140 140" xmlns="http://www.w3.org/2000/svg"></svg>  
</div>

 <script>
            
    var theme = "<?php echo $theme;?>";

    if (theme === 'dark') {
    var baseTextColor = "silver";
    var ringColor = "rgba(59,60,63,1)";
    } else {
    var baseTextColor = "#2d3a4b";
    var ringColor = "rgba(230,232,239,1)";
    }

</script>

<script>

var svgNS = "http://www.w3.org/2000/svg";

var svg = document.getElementById("compass");
   
var angle = "<?php echo $wind["direction"];?>";
angle = angle || 0;

var tenminAvD = "<?php echo $wind["direction_10m_avg"];?>";
tenminAvD = tenminAvD || 0; 

var Bearing = "<?php echo $wind["cardinal"];?>";
Bearing = Bearing || "North";
	
	DirectionBearing(70, 74, Bearing); // Bearing
        
	DirectionAngle(70, 58, angle + "°"); // Direction in degrees

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
  	direction.setAttributeNS(null, "fill", baseTextColor);
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
  	anglen.setAttributeNS(null, "fill", baseTextColor);  
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
  	bearing.setAttributeNS(null, "fill", baseTextColor);  
  	bearing.setAttributeNS(null, "text-anchor", "middle");  
  	var textNode = document.createTextNode(displayText);
  	bearing.appendChild(textNode);
  	svg.appendChild(bearing);
}

var polypointer = document.createElementNS(svgNS, "polygon"); // wind direction arrow
	polypointer.setAttributeNS(null, "points", "70,22 75,2 70,8 65,2"); 
	polypointer.setAttributeNS(null, "fill", "rgba(0,127,255,1)");// arch blue
	polypointer.setAttributeNS(null, "transform", "rotate("+ angle +", 70, 70)");
	svg.appendChild(polypointer);

</script>


</div>
</html>