<?php 
include('dvmCombinedData.php');

if ($wind["units"] == "kts") { echo $wind["units"] = "kts"; }
?>
<meta http-equiv="Content-Type: text/html; charset=UTF-8"/>

   <div class="chartforecast2">
      <span class="yearpopup"><a alt="wind charts" title="wind charts" href="dvmMenuWind.php" data-lity><?php echo $menucharticonpage;?> Wind Almanac and Charts</a></span>
    </div>
    <span class='moduletitle2'><?php echo $lang['Anemometer'], " (<valuetitleunit>", $wind["units"];?></valuetitleunit>)</span>
  

<div class="updatedtime2"><span><?php if(file_exists($livedata)&&time() - filemtime($livedata)>300) echo $offline. '<offline> Offline </offline>'; else echo $online." ".$divum["time"];?></div><br />
<div class="windspeedvalues">
<div class="windspeedvalue">
<div class="windunitspeed"></div></div>
<div class="windgustvalue">
<div class="windunitgust2"></div>
<div class="windunitidgust"></div></span></div></div>
<div class="windspeedtrend1">
<?php echo "<valuetext>Max "."<max><value><maxred>".number_format($wind["gust_max"],1)."</maxred></max></span>"."<supmb> ".$wind["units"]."</supmb><br> ".$lang['Gust']." (".$wind["gust_maxtime"].")</valuetext>";?></div>
<div class="windconverter">
<?php

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

echo ' <div class=divumwxwindrun>'.$windalert3.' &nbsp;<grey><valuetext1>',number_format($wind["wind_run"],1);?>
<grey><divumwxwindrunspan></valuetext>
<?php if ($wind["units"] == 'mph') echo 'mi'; else if ($wind["units"] == 'm/s') echo 'km'; else if ($wind["units"] == 'kts') echo 'mi';else echo 'km';?></divumwxwindrunspan>
</div></div><br /><div class=windrun1><?php echo  $lang['Wind Run'];?></div>
<?php // beaufort
if ($wind["speed_bft"] >= 12) {
  echo '<div class=divumwxbeaufort6>' . $beaufort12 . "&nbsp; " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 11) {
  echo '<div class=divumwxbeaufort6>' . $beaufort11 . "&nbsp; " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 10) {
  echo '<div class=divumwxbeaufort6>' . $beaufort10 . "&nbsp; " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 9) {
  echo '<div class=divumwxbeaufort6>' . $beaufort9 . "&nbsp; " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 8) {
  echo '<div class=divumwxbeaufort6>' . $beaufort8 . "&nbsp; " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 7) {
  echo '<div class=divumwxbeaufort6>' . $beaufort7 . "&nbsp; " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 6) {
  echo '<div class=divumwxbeaufort6>' . $beaufort6 . "&nbsp; " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 5) {
  echo '<div class=divumwxbeaufort4-5>' . $beaufort5 . "&nbsp; " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 4) {
  echo '<div class=divumwxbeaufort4-5>' . $beaufort4 . "&nbsp; " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 3) {
  echo '<div class=divumwxbeaufort3-4>' . $beaufort3 . "&nbsp; " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 2) {
  echo '<div class=divumwxbeaufort1-3>' . $beaufort2 . "&nbsp; " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 1) {
  echo '<div class=divumwxbeaufort1-3>' . $beaufort1 . "&nbsp; " . $wind["speed_bft"];
} else if ($wind["speed_bft"] >= 0) {
  echo '<div class=divumwxbeaufort1-3>' . $beaufort0 . "&nbsp; " . $wind["speed_bft"];
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

<html>
<script src="js/d3.min.js"></script>
<script src="js/iopctrl.js"></script>

<?php 

if ($theme == "dark") { echo
    
    '<style>
    
      .anemometer {
        position: relative; 
        margin-top: -35px; 
        margin-left: -1px;
      }
        
        .unselectable {
            -moz-user-select: -moz-none;
            -khtml-user-select: none;
            -webkit-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        .gauge .domain {
            stroke-width: 0px;
            stroke: rgba(59, 60, 63, 1);
        }
        
        .gauge .tick line {
            stroke: rgba(255,99,71,1);
            stroke-width: 1px;
            stroke-linecap: round;
        }
        
        .gauge line {
            stroke: rgba(59, 60, 63, 1);
            stroke-width: 0.75px;
            stroke-linecap: round; 
        }
                
        .gauge .arc, .gauge .cursor {
          stroke: rgba(59, 60, 63, 0);
          stroke-width: 2px;
          fill: rgba(59, 60, 63, 0);
        }
        .gauge .major {
            fill: rgba(147, 147, 147, 1);
            font-size: 8px;
            font-family: arial;
            font-weight: normal;
            letter-spacing: .015rem;
        }
        
        .gauge .indicator {
            stroke: rgba(255,0,0,1);
            fill: #000;
            stroke-width: 1px;
        }
                
         .gauge circle {
          stroke: rgba(59, 60, 63, 1);
          fill: rgba(59, 60, 63, 1);                
    }         
       
  </style>';
  
  } else { echo
  
    '<style>
    
        .anemometer {
        position: relative; 
        margin-top: -35px; 
        margin-left: -1px;
      }
      
        .unselectable {
            -moz-user-select: -moz-none;
            -khtml-user-select: none;
            -webkit-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        .gauge .domain {
            stroke-width: 0px;
            stroke: rgba(59, 60, 63, 1);
        }
        
        .gauge .tick line {
            stroke: rgba(255,99,71,1);
            stroke-width: 1px;
            stroke-linecap: round;
        }
        
        .gauge line {
            stroke: rgba(230, 232, 239, 1);
            stroke-width: 0.75px;
            stroke-linecap: round; 
        }
        .gauge .arc, .gauge .cursor {
          stroke: rgba(59, 60, 63, 0);
          stroke-width: 2px;
          fill: rgba(59, 60, 63, 0);
        }
        .gauge .major {
            fill: rgba(147, 147, 147, 1);
            font-size: 8px;
            font-family: arial;
            font-weight: normal;
            letter-spacing: .015rem;
        }
        
        .gauge .indicator {
            stroke: rgba(255,0,0,1);
            fill: #000;
            stroke-width: 1px;
        }
        
        .gauge circle {
          stroke: rgba(230, 232, 239, 1);
          fill: rgba(230, 232, 239, 1);                
    }
           
  </style>';
  
}
?>
<div class="anemometer"></div>
<div id="svg"></div>

   
     <script>

    var Ordinal = "<?php echo $wind["cardinal"];?>";
    Ordinal = Ordinal || 0;
    
    var current_direction = "<?php echo $wind["direction"];?>";  
    current_direction = current_direction || 0;

    var current_wind_speed = "<?php echo number_format($wind["speed"],1);?>";
    current_wind_speed = current_wind_speed || 0;

    var current_wind_gust = "<?php echo $wind["gust"];?>";
    current_wind_gust = current_wind_gust || 0;

    var gust_max = "<?php echo $wind["gust_max"];?>";
    gust_max = gust_max || 0;
        
    var units = "<?php echo $wind["units"];?>";
    
    var theme = "<?php echo $theme;?>";

     
      if (units == "km/h") {
                   
           var svg = d3.select(".anemometer")
                .append("svg")
                //.style("background", "#292E35")
                .attr("width", 310)
                .attr("height", 150);
                
             if (theme == "dark") {
             
             svg.append("text") // current wind speed text output
              .attr("x", 155)
              .attr("y", 127)
              .style("fill", "silver")
              .style("font-family", "Helvetica")
              .style("font-size", "11px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(current_wind_speed + " " + units);

            svg.append("text") // Bearing text output
              .attr("x", 45)
              .attr("y", 55)
              .style("fill", "silver")
              .style("font-family", "Helvetica")
              .style("font-size", "12px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text("Bearing");

          svg.append("text") // Ordinal text output
              .attr("x", 265)
              .attr("y", 55)
              .style("fill", "silver")
              .style("font-family", "Helvetica")
              .style("font-size", "12px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text("Ordinal");

          svg.append("text") // Bearing text output
              .attr("x", 45)
              .attr("y", 80)
              .style("fill", "silver")
              .style("font-family", "Helvetica")
              .style("font-size", "24px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(current_direction+"°");

          svg.append("text") // Ordinal text output
              .attr("x", 265)
              .attr("y", 80)
              .style("fill", "silver")
              .style("font-family", "Helvetica")
              .style("font-size", "24px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(Ordinal);
          
        } else {
        
        svg.append("text") // current wind speed text output
              .attr("x", 155)
              .attr("y", 127)
              .style("fill", "#2d3a4b")
              .style("font-family", "Helvetica")
              .style("font-size", "11px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(current_wind_speed + " " + units);

          svg.append("text") // Bearing text output
              .attr("x", 45)
              .attr("y", 55)
              .style("fill", "#2d3a4b")
              .style("font-family", "Helvetica")
              .style("font-size", "12px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text("Bearing");

          svg.append("text") // Ordinal text output
              .attr("x", 265)
              .attr("y", 55)
              .style("fill", "#2d3a4b")
              .style("font-family", "Helvetica")
              .style("font-size", "12px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text("Ordinal");

          svg.append("text") // Bearing text output
              .attr("x", 45)
              .attr("y", 80)
              .style("fill", "#2d3a4b")
              .style("font-family", "Helvetica")
              .style("font-size", "24px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(current_direction+"°");

          svg.append("text") // Ordinal text output
              .attr("x", 265)
              .attr("y", 80)
              .style("fill", "#2d3a4b")
              .style("font-family", "Helvetica")
              .style("font-size", "24px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(Ordinal);

        }
         
                                                                      
        var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {
                
            g.append("line")
                 .attr("y1", - width - 3) // needle length
                 .attr("y2", width - 42) // needle tail length
                 .style("stroke", "rgba(0,127,255,1)")
                 .style("stroke-linecap", "round")
                 .style("stroke-width", 1);


                 g.append("circle")
                 .attr("cx", 0) // center circle
                 .attr("cy", 0)
                 .attr("r", 5);
                                                                               
            });
                                                                    
        gauge.axis().orient("out").tickFormat(d3.format("d"))
                .normalize(false)
                .ticks(12)
                .tickSubdivide(10)
                .tickSize(7, 7, 10)
                .tickPadding(3)
                .scale(d3.scale.linear()
                        .domain([0, 120]) // min max text scale current wind speed
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));
                                                                                                     
        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(52, -29)')
                .call(gauge);
                
                 
        gauge.value(current_wind_speed);
        
         var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {
            g.append("line")
                 .attr("y1", - width - 7) // needle length
                 .attr("y2", - width + 0) // needle tail length
                 .style("stroke", "rgba(46,139,87,1)")
                 .style("stroke-linecap", "round")
                 .style("stroke-width", 2);
                                         
            });
                                              
        gauge.axis().orient("in").tickFormat(d3.format("d"))
                .normalize(true)
                .ticks(0)
                .tickSubdivide(0)
                .tickSize(7, 7, 10)
                .tickPadding(3)
                .scale(d3.scale.linear()
                        .domain([0, 120]) // min max text scale current wind gust
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));
                                                
        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(52, -29)')
                .call(gauge);


        gauge.value(current_wind_gust);
        
          var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {
            g.append("line")
                 .attr("y1", - width - 7) // needle length
                 .attr("y2", - width + 0) // needle tail length
                 .style("stroke", "rgba(255, 124, 57, 1)")
                 .style("stroke-linecap", "round")
                 .style("stroke-width", 2);                                
                                               
            });
                                              
        gauge.axis().orient("in").tickFormat(d3.format("d"))
                .normalize(true)
                .ticks(0)
                .tickSubdivide(0)
                .tickSize(7, 7, 10)
                .tickPadding(3)
                .scale(d3.scale.linear()
                        .domain([0, 120]) // min max text scale max gust
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));
                                                
        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(52, -29)')
                .call(gauge);


        gauge.value(gust_max);
        
        } else if (units == "mph") {
        
         var svg = d3.select(".anemometer")
                .append("svg")
                //.style("background", "#292E35")
                .attr("width", 310)
                .attr("height", 150);
                
             if (theme == "dark") {
             
             svg.append("text") // current wind speed text output
              .attr("x", 155)
              .attr("y", 127)
              .style("fill", "silver")
              .style("font-family", "Helvetica")
              .style("font-size", "11px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(current_wind_speed + " " + units);

            svg.append("text") // Bearing text output
              .attr("x", 45)
              .attr("y", 55)
              .style("fill", "silver")
              .style("font-family", "Helvetica")
              .style("font-size", "12px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text("Bearing");

          svg.append("text") // Ordinal text output
              .attr("x", 265)
              .attr("y", 55)
              .style("fill", "silver")
              .style("font-family", "Helvetica")
              .style("font-size", "12px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text("Ordinal");

          svg.append("text") // Bearing text output
              .attr("x", 45)
              .attr("y", 80)
              .style("fill", "silver")
              .style("font-family", "Helvetica")
              .style("font-size", "24px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(current_direction+"°");

          svg.append("text") // Ordinal text output
              .attr("x", 265)
              .attr("y", 80)
              .style("fill", "silver")
              .style("font-family", "Helvetica")
              .style("font-size", "24px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(Ordinal);
          
        } else {
        
        svg.append("text") // current wind speed text output
              .attr("x", 155)
              .attr("y", 127)
              .style("fill", "#2d3a4b")
              .style("font-family", "Helvetica")
              .style("font-size", "11px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(current_wind_speed + " " + units);

          svg.append("text") // Bearing text output
              .attr("x", 45)
              .attr("y", 55)
              .style("fill", "#2d3a4b")
              .style("font-family", "Helvetica")
              .style("font-size", "12px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text("Bearing");

          svg.append("text") // Ordinal text output
              .attr("x", 265)
              .attr("y", 55)
              .style("fill", "#2d3a4b")
              .style("font-family", "Helvetica")
              .style("font-size", "12px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text("Ordinal");

          svg.append("text") // Bearing text output
              .attr("x", 45)
              .attr("y", 80)
              .style("fill", "#2d3a4b")
              .style("font-family", "Helvetica")
              .style("font-size", "24px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(current_direction+"°");

          svg.append("text") // Ordinal text output
              .attr("x", 265)
              .attr("y", 80)
              .style("fill", "#2d3a4b")
              .style("font-family", "Helvetica")
              .style("font-size", "24px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(Ordinal);
        }
         
                                                                      
        var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {
                
            g.append("line")
                 .attr("y1", - width - 3) // needle length
                 .attr("y2", width - 42) // needle tail length
                 .style("stroke", "rgba(0,127,255,1)")
                 .style("stroke-linecap", "round")
                 .style("stroke-width", 1);
                

                 g.append("circle")
                 .attr("cx", 0) // center circle
                 .attr("cy", 0)
                 .attr("r", 5);
                                                                               
            });
                                                                    
        gauge.axis().orient("out").tickFormat(d3.format("d"))
                .normalize(false)
                .ticks(12)
                .tickSubdivide(10)
                .tickSize(7, 7, 10)
                .tickPadding(3)
                .scale(d3.scale.linear()
                        .domain([0, 100]) // min max text scale current wind speed
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));
                                                                                                     
        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(52, -29)')
                .call(gauge);
                
                 
        gauge.value(current_wind_speed);
        
         var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {
            g.append("line")
                 .attr("y1", - width - 7) // needle length
                 .attr("y2", - width + 0) // needle tail length
                 .style("stroke", "rgba(46,139,87,1)")
                 .style("stroke-linecap", "round")
                 .style("stroke-width", 2);
                                         
            });
                                              
        gauge.axis().orient("in").tickFormat(d3.format("d"))
                .normalize(true)
                .ticks(0)
                .tickSubdivide(0)
                .tickSize(7, 7, 10)
                .tickPadding(3)
                .scale(d3.scale.linear()
                        .domain([0, 100]) // min max text scale current wind gust
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));
                                                
        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(52, -29)')
                .call(gauge);


        gauge.value(current_wind_gust);
        
          var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {
            g.append("line")
                 .attr("y1", - width - 7) // needle length
                 .attr("y2", - width + 0) // needle tail length
                 .style("stroke", "rgba(255, 124, 57, 1)")
                 .style("stroke-linecap", "round")
                 .style("stroke-width", 2);                                
                                               
            });
                                              
        gauge.axis().orient("in").tickFormat(d3.format("d"))
                .normalize(true)
                .ticks(0)
                .tickSubdivide(0)
                .tickSize(7, 7, 10)
                .tickPadding(3)
                .scale(d3.scale.linear()
                        .domain([0, 100]) // min max text scale max gust
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));
                                                
        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(52, -29)')
                .call(gauge);


        gauge.value(gust_max);
        
        } else if (units == "m/s") {
        
         var svg = d3.select(".anemometer")
                .append("svg")
                .attr("width", 310)
                .attr("height", 150);
           
            if (theme == "dark") {
             
             svg.append("text") // current wind speed text output
              .attr("x", 155)
              .attr("y", 127)
              .style("fill", "silver")
              .style("font-family", "Helvetica")
              .style("font-size", "11px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(current_wind_speed + " " + units);

            svg.append("text") // Bearing text output
              .attr("x", 45)
              .attr("y", 55)
              .style("fill", "silver")
              .style("font-family", "Helvetica")
              .style("font-size", "12px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text("Bearing");

          svg.append("text") // Ordinal text output
              .attr("x", 265)
              .attr("y", 55)
              .style("fill", "silver")
              .style("font-family", "Helvetica")
              .style("font-size", "12px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text("Ordinal");

          svg.append("text") // Bearing text output
              .attr("x", 45)
              .attr("y", 80)
              .style("fill", "silver")
              .style("font-family", "Helvetica")
              .style("font-size", "24px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(current_direction+"°");

          svg.append("text") // Ordinal text output
              .attr("x", 265)
              .attr("y", 80)
              .style("fill", "silver")
              .style("font-family", "Helvetica")
              .style("font-size", "24px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(Ordinal);
          
        } else {
        
        svg.append("text") // current wind speed text output
              .attr("x", 155)
              .attr("y", 127)
              .style("fill", "#2d3a4b")
              .style("font-family", "Helvetica")
              .style("font-size", "11px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(current_wind_speed + " " + units);

          svg.append("text") // Bearing text output
              .attr("x", 45)
              .attr("y", 55)
              .style("fill", "#2d3a4b")
              .style("font-family", "Helvetica")
              .style("font-size", "12px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text("Bearing");

          svg.append("text") // Ordinal text output
              .attr("x", 265)
              .attr("y", 55)
              .style("fill", "#2d3a4b")
              .style("font-family", "Helvetica")
              .style("font-size", "12px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text("Ordinal");

          svg.append("text") // Bearing text output
              .attr("x", 45)
              .attr("y", 80)
              .style("fill", "#2d3a4b")
              .style("font-family", "Helvetica")
              .style("font-size", "24px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(current_direction+"°");

          svg.append("text") // Ordinal text output
              .attr("x", 265)
              .attr("y", 80)
              .style("fill", "#2d3a4b")
              .style("font-family", "Helvetica")
              .style("font-size", "24px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(Ordinal);
        }     

        var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {
            g.append("line")
                 .attr("y1", - width - 3) // needle length
                 .attr("y2", width - 42) // needle tail length
                 .style("stroke", "rgba(0,127,255,1)")
                 .style("stroke-linecap", "round")
                 .style("stroke-width", 1);
          

                 g.append("circle")
                 .attr("cx", 0) // center circle
                 .attr("cy", 0)
                 .attr("r", 5);
                 
            });
                   
        gauge.axis().orient("out")
                .normalize(false)
                .ticks(9)
                .tickSubdivide(10)
                .tickSize(7, 7, 10)
                .tickPadding(3)
                .scale(d3.scale.linear()
                        .domain([0, 50]) // min max text scale current wind speed
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));

        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(52, -29)')
                .call(gauge);

        gauge.value(current_wind_speed);
        
         var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {
            g.append("line")
                 .attr("y1", - width - 7) // needle length
                 .attr("y2", - width + 0) // needle tail length
                 .style("stroke", "rgba(46,139,87,1)")
                 .style("stroke-linecap", "round")
                 .style("stroke-width", 2);
                 
            });
                   
        gauge.axis().orient("in")
                .normalize(true)
                .ticks(0)
                .tickSubdivide(0)
                .tickSize(7, 7, 10)
                .tickPadding(3)
                .scale(d3.scale.linear()
                        .domain([0, 50]) // min max text scale current wind gust
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));

        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(52, -29)')
                .call(gauge);

        gauge.value(current_wind_gust);
        
        var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {
            g.append("line")
                 .attr("y1", - width - 7) // needle length
                 .attr("y2", - width + 0) // needle tail length
                 .style("stroke", "rgba(255, 124, 57, 1)")
                 .style("stroke-linecap", "round")
                 .style("stroke-width", 2);                                
                                               
            });
                                              
        gauge.axis().orient("in").tickFormat(d3.format("d"))
                .normalize(true)
                .ticks(0)
                .tickSubdivide(0)
                .tickSize(7, 7, 10)
                .tickPadding(3)
                .scale(d3.scale.linear()
                        .domain([0, 50]) // min max text scale max gust
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));
                                                
        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(52, -29)')
                .call(gauge);


        gauge.value(gust_max);
                   
      } else if (units == "kts") { 

        var svg = d3.select(".anemometer")
                .append("svg")
                .attr("width", 310)
                .attr("height", 150);
           
            if (theme == "dark") {
             
             svg.append("text") // current wind speed text output
              .attr("x", 155)
              .attr("y", 127)
              .style("fill", "silver")
              .style("font-family", "Helvetica")
              .style("font-size", "11px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(current_wind_speed + " " + units);

            svg.append("text") // Bearing text output
              .attr("x", 45)
              .attr("y", 55)
              .style("fill", "silver")
              .style("font-family", "Helvetica")
              .style("font-size", "12px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text("Bearing");

          svg.append("text") // Ordinal text output
              .attr("x", 265)
              .attr("y", 55)
              .style("fill", "silver")
              .style("font-family", "Helvetica")
              .style("font-size", "12px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text("Ordinal");

          svg.append("text") // Bearing text output
              .attr("x", 45)
              .attr("y", 80)
              .style("fill", "silver")
              .style("font-family", "Helvetica")
              .style("font-size", "24px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(current_direction+"°");

          svg.append("text") // Ordinal text output
              .attr("x", 265)
              .attr("y", 80)
              .style("fill", "silver")
              .style("font-family", "Helvetica")
              .style("font-size", "24px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(Ordinal);
          
        } else {
        
        svg.append("text") // current wind speed text output
              .attr("x", 155)
              .attr("y", 127)
              .style("fill", "#2d3a4b")
              .style("font-family", "Helvetica")
              .style("font-size", "11px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(current_wind_speed + " " + units);

          svg.append("text") // Bearing text output
              .attr("x", 45)
              .attr("y", 55)
              .style("fill", "#2d3a4b")
              .style("font-family", "Helvetica")
              .style("font-size", "12px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text("Bearing");

          svg.append("text") // Ordinal text output
              .attr("x", 265)
              .attr("y", 55)
              .style("fill", "#2d3a4b")
              .style("font-family", "Helvetica")
              .style("font-size", "12px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text("Ordinal");

          svg.append("text") // Bearing text output
              .attr("x", 45)
              .attr("y", 80)
              .style("fill", "#2d3a4b")
              .style("font-family", "Helvetica")
              .style("font-size", "24px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(current_direction+"°");

          svg.append("text") // Ordinal text output
              .attr("x", 265)
              .attr("y", 80)
              .style("fill", "#2d3a4b")
              .style("font-family", "Helvetica")
              .style("font-size", "24px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(Ordinal);
        }     

        var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {
            g.append("line")
                 .attr("y1", - width - 3) // needle length
                 .attr("y2", width - 42) // needle tail length
                 .style("stroke", "rgba(0,127,255,1)")
                 .style("stroke-linecap", "round")
                 .style("stroke-width", 1);
            

                 g.append("circle")
                 .attr("cx", 0) // center circle
                 .attr("cy", 0)
                 .attr("r", 5);
                 
            });
                   
        gauge.axis().orient("out")
                .normalize(false)
                .ticks(9)
                .tickSubdivide(10)
                .tickSize(7, 7, 10)
                .tickPadding(3)
                .scale(d3.scale.linear()
                        .domain([0, 100]) // min max text scale current wind speed
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));

        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(52, -29)')
                .call(gauge);

        gauge.value(current_wind_speed);
        
         var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {
            g.append("line")
                 .attr("y1", - width - 7) // needle length
                 .attr("y2", - width + 0) // needle tail length
                 .style("stroke", "rgba(46,139,87,1)")
                 .style("stroke-linecap", "round")
                 .style("stroke-width", 2);
                 
            });
                   
        gauge.axis().orient("in")
                .normalize(true)
                .ticks(0)
                .tickSubdivide(0)
                .tickSize(7, 7, 10)
                .tickPadding(3)
                .scale(d3.scale.linear()
                        .domain([0, 100]) // min max text scale current wind gust
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));

        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(52, -29)')
                .call(gauge);

        gauge.value(current_wind_gust);
        
        var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {
            g.append("line")
                 .attr("y1", - width - 7) // needle length
                 .attr("y2", - width + 0) // needle tail length
                 .style("stroke", "rgba(255, 124, 57, 1)")
                 .style("stroke-linecap", "round")
                 .style("stroke-width", 2);                                
                                               
            });
                                              
        gauge.axis().orient("in").tickFormat(d3.format("d"))
                .normalize(true)
                .ticks(0)
                .tickSubdivide(0)
                .tickSize(7, 7, 10)
                .tickPadding(3)
                .scale(d3.scale.linear()
                        .domain([0, 100]) // min max text scale max gust
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));
                                                
        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(52, -29)')
                .call(gauge);


        gauge.value(gust_max);

      }
            
</script>
</html>
