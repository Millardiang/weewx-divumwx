<?php 
include('dvmCombinedData.php');
$speedColor = $color["windSpeed"];
$gustColorMax = $color["windGust_max"];
$textColor = "White";
?>
<meta http-equiv="Content-Type: text/html; charset=UTF-8"/>
<style>
maxGust {
 color: <?php echo $gustColorMax;?>;
}
</style>


   <div class="chartforecast2">
      <span class="yearpopup"><a alt="wind charts" title="wind charts" href="dvmWindRecords.php" data-lity><?php echo $menucharticonpage;?> Wind Records and Charts</a></span>
    </div>
    <span class='moduletitle'><?php echo $lang['Anemometer']. " (<valuetitleunit>". $wind["units"];?></valuetitleunit>)</span>
  

<div class="updatedtime2" style="margin-top: -33px;"><span><?php if(file_exists($livedata)&&time() - filemtime($livedata)>300) echo $offline. '<offline> Offline </offline>'; else echo $online." ".$divum["time"];?></div><br />

<div class="windspeedtrend1">
<?php echo "<valuetext>Max "."<max><value><maxGust>".number_format($wind["gust_max"],1)."</maxGust></max></span>"."<supmb> ".$wind["units"]."</supmb><br> ".$lang['Gust']." (".$wind["gust_maxtime"].")</valuetext>";?></div>

<div class="windconverter">

<?php
if($theme == 'dark') { 
// convert kmh to mph
if ($wind["units"]=="km/h"){echo "<div class=windconvertercirclered1 style='color:$speedColor;'>".number_format($wind["speed"]*0.621371,1)." <smallrainunit>mph</smallrainunit>";}
// convert mph to kmh
else if ($wind["units"]=="mph"){echo "<div class=windconvertercirclered1 style='color:$speedColor;'>".number_format($wind["speed"]*1.609343502101025,1)." <smallrainunit>&nbsp;km/h</smallrainunit>";}
// convert ms to kmh
else if ($wind["units"]=="m/s"){echo "<div class=windconvertercirclered1 style='color:$speedColor;'>".number_format($wind["speed"]*3.60000288,1)." <smallrainunit>&nbsp;km/h</smallrainunit>";}
// knots to kmh
else if ($wind["units"]=="kts"){echo "<div class=windconvertercirclered1 style='color:$speedColor;'>".number_format($wind["speed"]*1.85200,1)." <smallrainunit>&nbsp;km/h</smallrainunit>";}

} else {

// convert kmh to mph
if ($wind["units"]=="km/h"){echo "<div class=windconvertercirclered1 style='background:$speedColor; color:$textColor;'>".number_format($wind["speed"]*0.621371,1)." <smallrainunit>mph</smallrainunit>";}
// convert mph to kmh
else if ($wind["units"]=="mph"){echo "<div class=windconvertercirclered1 style='background:$speedColor; color:$textColor;'>".number_format($wind["speed"]*1.609343502101025,1)." <smallrainunit>&nbsp;km/h</smallrainunit>";}
// convert ms to kmh
else if ($wind["units"]=="m/s"){echo "<div class=windconvertercirclered1 style='background:$speedColor; color:$textColor;'>".number_format($wind["speed"]*3.60000288,1)." <smallrainunit>&nbsp;km/h</smallrainunit>";}
// knots to kmh
else if ($wind["units"]=="kts"){echo "<div class=windconvertercirclered1 style='background:$speedColor; color:$textColor;'>".number_format($wind["speed"]*1.85200,1)." <smallrainunit>&nbsp;km/h</smallrainunit>";}}?>
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
<div class="beaufort1">
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

</div>
<style>

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

<script src="js/d3.v3.min.js"></script>

<script src="js/iopctrl.js"></script>

<?php 

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
    
      .anemometer {
        position: relative; 
        margin-top: -33px; 
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
    
        .anemometer {
        position: relative; 
        margin-top: -33px; 
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

<script>            
    var theme = "<?php echo $theme;?>";

    if (theme === 'dark') {
    var baseTextColor = "silver";
    } else {
    var baseTextColor = "#2d3a4b";
    }
</script>

<div class="anemometer"></div>
   
     <script>

var ordinal = "<?php echo $wind["cardinal"];?>";
ordinal = ordinal || "North";
 
var current_direction = "<?php echo $wind["direction"];?>";  
current_direction = current_direction || 0;

var current_wind_speed = "<?php echo number_format($wind["speed"],1);?>";
current_wind_speed = current_wind_speed || 0;

// Beaufort color scale for wind speed
var wind_speed_color = "<?php echo $color["windSpeed"];?>";

var current_wind_gust = "<?php echo number_format($wind["gust"],1);?>";
current_wind_gust = current_wind_gust || 0;

// Beaufort color scale for wind Gust
var wind_gust_color = "<?php echo $color["windGust"];?>";

var gust_max = "<?php echo $wind["gust_max"];?>";
gust_max = gust_max || 0;
        
var units = "<?php echo $wind["units"];?>";
   
      if (units === "km/h") {

           var svg = d3.select(".anemometer")
                .append("svg")
                //.style("background", "#292E35")
                .attr("width", 310)
                .attr("height", 150);

      var anglePercentage = d3.scale.linear()
        .domain([0, 36])
        .range([-135 * Math.PI/180, +135 * Math.PI/180]);

      var color = d3.scale.ordinal()
        .range([
        "#f1ff6c",
        "#c1fc77",
        "#45698d",
        "#754a92",
        "#af5088",
        "#d20032",
        "#c8420d",
        "#c2863e", 
        "#39a239", 
        "#0f94a7",
        "#6e90d0",
        "#7e98bb",
        "#85a3aa"]);

         windy = [ // 0 - 36 m/s
            [33.0, 36.0, 0],
            [28.0, 33.0, 1],
            [24.0, 28.0, 2],
            [21.0, 24.0, 3],
            [17.0, 21.0, 4],
            [14.0, 17.0, 5],
            [11.0, 14.0, 6],
            [8.0, 11.0, 7],
            [5.0, 8.0, 8],
            [3.0, 5.0, 9],
            [2.0, 3.0, 10],
            [1.0, 2.0, 11],
            [0, 1.0, 12]]

        var arc = d3.svg.arc()
        .innerRadius(45)
        .outerRadius(50)
        .startAngle(function(d){return anglePercentage(d[0]);})
        .endAngle(function(d){return anglePercentage(d[1]);});

        svg.selectAll("path.windy")
        .data(windy)
        .enter()
        .append("path")
        .attr("class", "windy")
        .attr("d", arc)
        .style("fill", function(d){return color(d[2]);})
        .attr("transform", "translate(154.5,73.5)");
             
             svg.append("text") // current wind speed text output
              .attr("x", 155)
              .attr("y", 127)
              .style("fill", wind_speed_color)
              .style("font-family", "Helvetica")
              .style("font-size", "11px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(current_wind_speed + " " + units);

          svg.append("text") // current wind gust speed text output
              .attr("x", 155)
              .attr("y", 142.5)
              .style("fill", wind_gust_color)
              .style("font-family", "Helvetica")
              .style("font-size", "10px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text("Gusting @ "+ current_wind_gust + " " + units);

            svg.append("text") // Bearing text output
              .attr("x", 45)
              .attr("y", 55)
              .style("fill", baseTextColor)
              .style("font-family", "Helvetica")
              .style("font-size", "12px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text("Bearing");

          svg.append("text") // Cardinal text output
              .attr("x", 265)
              .attr("y", 55)
              .style("fill", baseTextColor)
              .style("font-family", "Helvetica")
              .style("font-size", "12px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text("Ordinal");

          svg.append("text") // Bearing text output
              .attr("x", 45)
              .attr("y", 80)
              .style("fill", baseTextColor)
              .style("font-family", "Helvetica")
              .style("font-size", "24px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(current_direction + "\u00B0");

          svg.append("text") // Cardinal text output
              .attr("x", 265)
              .attr("y", 80)
              .style("fill", baseTextColor)
              .style("font-family", "Helvetica")
              .style("font-size", "24px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(ordinal);
                  
        var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {
        
            g.append('polyline') // needle
                .attr('points', "1.5 11 0 -56 -1.5 11 0 11 1.5 11 -1.5 0")
                .style('fill', 'rgba(0,127,255,1)')
                .style('stroke', 'rgba(0,127,255,1)')
                .style("stroke-width", 0.5)
                .style("stroke-linecap", "round");
 
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
                        .domain([0, 130]) // min max text scale current wind speed
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
                        .domain([0, 130]) // min max text scale current wind gust
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
                        .domain([0, 130]) // min max text scale max gust
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));
                                               
        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(52, -29)')
                .call(gauge);

        gauge.value(gust_max);
                
        } else if (units === "mph") {
        
         var svg = d3.select(".anemometer")
                .append("svg")
                //.style("background", "#292E35")
                .attr("width", 310)
                .attr("height", 150);

      var anglePercentage = d3.scale.linear()
        .domain([0, 36])
        .range([-135 * Math.PI/180, +135 * Math.PI/180]);

      var color = d3.scale.ordinal()
        .range([
        "#f1ff6c",
        "#c1fc77",
        "#45698d",
        "#754a92",
        "#af5088",
        "#d20032",
        "#c8420d",
        "#c2863e", 
        "#39a239", 
        "#0f94a7",
        "#6e90d0",
        "#7e98bb",
        "#85a3aa"]);

         windy = [ // 0 - 36 m/s
            [33.0, 36.0, 0],
            [28.0, 33.0, 1],
            [24.0, 28.0, 2],
            [21.0, 24.0, 3],
            [17.0, 21.0, 4],
            [14.0, 17.0, 5],
            [11.0, 14.0, 6],
            [8.0, 11.0, 7],
            [5.0, 8.0, 8],
            [3.0, 5.0, 9],
            [2.0, 3.0, 10],
            [1.0, 2.0, 11],
            [0, 1.0, 12]]

        var arc = d3.svg.arc()
        .innerRadius(45)
        .outerRadius(50)
        .startAngle(function(d){return anglePercentage(d[0]);})
        .endAngle(function(d){return anglePercentage(d[1]);});

        svg.selectAll("path.windy")
        .data(windy)
        .enter()
        .append("path")
        .attr("class", "windy")
        .attr("d", arc)
        .style("fill", function(d){return color(d[2]);})
        .attr("transform", "translate(154.5,73.5)");
             
             svg.append("text") // current wind speed text output
              .attr("x", 155)
              .attr("y", 127)
              .style("fill", wind_speed_color)
              .style("font-family", "Helvetica")
              .style("font-size", "11px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(current_wind_speed + " " + units);

            svg.append("text") // current wind gust speed text output
              .attr("x", 155)
              .attr("y", 142.5)
              .style("fill", wind_gust_color)
              .style("font-family", "Helvetica")
              .style("font-size", "10px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text("Gusting @ "+ current_wind_gust + " " + units);

            svg.append("text") // Bearing text output
              .attr("x", 45)
              .attr("y", 55)
              .style("fill", baseTextColor)
              .style("font-family", "Helvetica")
              .style("font-size", "12px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text("Bearing");

          svg.append("text") // Cardinal text output
              .attr("x", 265)
              .attr("y", 55)
              .style("fill", baseTextColor)
              .style("font-family", "Helvetica")
              .style("font-size", "12px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text("Ordinal");

          svg.append("text") // Bearing text output
              .attr("x", 45)
              .attr("y", 80)
              .style("fill", baseTextColor)
              .style("font-family", "Helvetica")
              .style("font-size", "24px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(current_direction + "\u00B0");

          svg.append("text") // Cardinal text output
              .attr("x", 265)
              .attr("y", 80)
              .style("fill", baseTextColor)
              .style("font-family", "Helvetica")
              .style("font-size", "24px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(ordinal);
                                                         
        var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {

            g.append('polyline') // needle
                .attr('points', "1.5 11 0 -56 -1.5 11 0 11 1.5 11 -1.5 0")
                .style('fill', 'rgba(0,127,255,1)')
                .style('stroke', 'rgba(0,127,255,1)')
                .style("stroke-width", 0.5)
                .style("stroke-linecap", "round");                

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
                        .domain([0, 80]) // min max text scale current wind speed
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
                        .domain([0, 80]) // min max text scale current wind gust
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
                        .domain([0, 80]) // min max text scale max gust
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));
                                                
        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(52, -29)')
                .call(gauge);

        gauge.value(gust_max);
        
        } else if (units === "m/s") {
        
         var svg = d3.select(".anemometer")
                .append("svg")
                .attr("width", 310)
                .attr("height", 150);

        var anglePercentage = d3.scale.linear()
        .domain([0, 36])
        .range([-135 * Math.PI/180, +135 * Math.PI/180]);

      var color = d3.scale.ordinal()
        .range([
        "#f1ff6c",
        "#c1fc77",
        "#45698d",
        "#754a92",
        "#af5088",
        "#d20032",
        "#c8420d",
        "#c2863e", 
        "#39a239", 
        "#0f94a7",
        "#6e90d0",
        "#7e98bb",
        "#85a3aa"]);

         windy = [ // 0 - 36 m/s
            [33.0, 36.0, 0],
            [28.0, 33.0, 1],
            [24.0, 28.0, 2],
            [21.0, 24.0, 3],
            [17.0, 21.0, 4],
            [14.0, 17.0, 5],
            [11.0, 14.0, 6],
            [8.0, 11.0, 7],
            [5.0, 8.0, 8],
            [3.0, 5.0, 9],
            [2.0, 3.0, 10],
            [1.0, 2.0, 11],
            [0, 1.0, 12]]

        var arc = d3.svg.arc()
        .innerRadius(45)
        .outerRadius(50)
        .startAngle(function(d){return anglePercentage(d[0]);})
        .endAngle(function(d){return anglePercentage(d[1]);});

        svg.selectAll("path.windy")
        .data(windy)
        .enter()
        .append("path")
        .attr("class", "windy")
        .attr("d", arc)
        .style("fill", function(d){return color(d[2]);})
        .attr("transform", "translate(154.5,73.5)");
            
             svg.append("text") // current wind speed text output
              .attr("x", 155)
              .attr("y", 127)
              .style("fill", wind_speed_color)
              .style("font-family", "Helvetica")
              .style("font-size", "11px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(current_wind_speed + " " + units);

            svg.append("text") // current wind gust speed text output
              .attr("x", 155)
              .attr("y", 142.5)
              .style("fill", wind_gust_color)
              .style("font-family", "Helvetica")
              .style("font-size", "10px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text("Gusting @ "+ current_wind_gust + " " + units);

            svg.append("text") // Bearing text output
              .attr("x", 45)
              .attr("y", 55)
              .style("fill", baseTextColor)
              .style("font-family", "Helvetica")
              .style("font-size", "12px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text("Bearing");

          svg.append("text") // Cardinal text output
              .attr("x", 265)
              .attr("y", 55)
              .style("fill", baseTextColor)
              .style("font-family", "Helvetica")
              .style("font-size", "12px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text("Ordinal");

          svg.append("text") // Bearing text output
              .attr("x", 45)
              .attr("y", 80)
              .style("fill", baseTextColor)
              .style("font-family", "Helvetica")
              .style("font-size", "24px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(current_direction + "\u00B0");

          svg.append("text") // Cardinal text output
              .attr("x", 265)
              .attr("y", 80)
              .style("fill", baseTextColor)
              .style("font-family", "Helvetica")
              .style("font-size", "24px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(ordinal);
    
        var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {

            g.append('polyline') // needle
                .attr('points', "1.5 11 0 -56 -1.5 11 0 11 1.5 11 -1.5 0")
                .style('fill', 'rgba(0,127,255,1)')
                .style('stroke', 'rgba(0,127,255,1)')
                .style("stroke-width", 0.5)
                .style("stroke-linecap", "round");

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
                        .domain([0, 35]) // min max text scale current wind speed
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
                        .domain([0, 35]) // min max text scale current wind gust
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
                        .domain([0, 35]) // min max text scale max gust
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));
                                                
        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(52, -29)')
                .call(gauge);

        gauge.value(gust_max);
                   
      } else if (units === "kts") { 

        var svg = d3.select(".anemometer")
                .append("svg")
                .attr("width", 310)
                .attr("height", 150);

      var anglePercentage = d3.scale.linear()
        .domain([0, 36])
        .range([-135 * Math.PI/180, +135 * Math.PI/180]);

      var color = d3.scale.ordinal()
        .range([
        "#f1ff6c",
        "#c1fc77",
        "#45698d",
        "#754a92",
        "#af5088",
        "#d20032",
        "#c8420d",
        "#c2863e", 
        "#39a239", 
        "#0f94a7",
        "#6e90d0",
        "#7e98bb",
        "#85a3aa"]);

         windy = [ // 0 - 36 m/s
            [33.0, 36.0, 0],
            [28.0, 33.0, 1],
            [24.0, 28.0, 2],
            [21.0, 24.0, 3],
            [17.0, 21.0, 4],
            [14.0, 17.0, 5],
            [11.0, 14.0, 6],
            [8.0, 11.0, 7],
            [5.0, 8.0, 8],
            [3.0, 5.0, 9],
            [2.0, 3.0, 10],
            [1.0, 2.0, 11],
            [0, 1.0, 12]]

        var arc = d3.svg.arc()
        .innerRadius(45)
        .outerRadius(50)
        .startAngle(function(d){return anglePercentage(d[0]);})
        .endAngle(function(d){return anglePercentage(d[1]);});

        svg.selectAll("path.windy")
        .data(windy)
        .enter()
        .append("path")
        .attr("class", "windy")
        .attr("d", arc)
        .style("fill", function(d){return color(d[2]);})
        .attr("transform", "translate(154.5,73.5)");

             svg.append("text") // current wind speed text output
              .attr("x", 155)
              .attr("y", 127)
              .style("fill", wind_speed_color)
              .style("font-family", "Helvetica")
              .style("font-size", "11px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(current_wind_speed + " " + units);

            svg.append("text") // current wind gust speed text output
              .attr("x", 155)
              .attr("y", 142.5)
              .style("fill", wind_gust_color)
              .style("font-family", "Helvetica")
              .style("font-size", "10px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text("Gusting @ "+ current_wind_gust + " " + units);

            svg.append("text") // Bearing text output
              .attr("x", 45)
              .attr("y", 55)
              .style("fill", baseTextColor)
              .style("font-family", "Helvetica")
              .style("font-size", "12px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text("Bearing");

          svg.append("text") // Cardinal text output
              .attr("x", 265)
              .attr("y", 55)
              .style("fill", baseTextColor)
              .style("font-family", "Helvetica")
              .style("font-size", "12px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text("Ordinal");

          svg.append("text") // Bearing text output
              .attr("x", 45)
              .attr("y", 80)
              .style("fill", baseTextColor)
              .style("font-family", "Helvetica")
              .style("font-size", "24px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(current_direction + "\u00B0");

          svg.append("text") // Cardinal text output
              .attr("x", 265)
              .attr("y", 80)
              .style("fill", baseTextColor)
              .style("font-family", "Helvetica")
              .style("font-size", "24px")
              .style("text-anchor", "middle")
              .style("font-weight", "normal")
          .text(ordinal);
     
        var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {

            g.append('polyline') // needle
                .attr('points', "1.5 11 0 -56 -1.5 11 0 11 1.5 11 -1.5 0")
                .style('fill', 'rgba(0,127,255,1)')
                .style('stroke', 'rgba(0,127,255,1)')
                .style("stroke-width", 0.5)
                .style("stroke-linecap", "round");
            
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
                        .domain([0, 70]) // min max text scale current wind speed
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
                        .domain([0, 70]) // min max text scale current wind gust
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
                        .domain([0, 70]) // min max text scale max gust
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));
                                                
        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(52, -29)')
                .call(gauge);

        gauge.value(gust_max);

      }
            
</script>
</html>
