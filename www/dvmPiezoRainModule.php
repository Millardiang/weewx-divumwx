<?php
include('dvmCombinedData.php');
error_reporting(0);
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
<head>
<meta charset="utf-8">
<title>weather piezo rain</title>
<style>
.piezo-module{position: relative;top:-1px;margin-left: 0px;}
</style>
</head>
<body>

<div class="chartforecast">
<span class="yearpopup"><a alt="rain charts" title="rain charts" href="dvmRainfallRecords.php" data-lity><?php echo $menucharticonpage;?> Rainfall Records and Charts</a></span>     
</div>
<span class='moduletitle'><?php echo $lang['rainfallModule'], " Piezo Sensor (<valuetitleunit>" . $rain["units"];?></valuetitleunit>)</span>
<div class="updatedtime1"><span><?php if (file_exists($livedata)&&time() - filemtime($livedata)>300) echo $offline. '<offline> Offline </offline>'; else echo $online." ".$divum["time"];?></div>
<div class="rainconverter">
<?php 
if($theme == 'dark') {
if ($rain["units"] =='in'){echo "<div class=rainconvertercircle style='color:$colorRainDaySum;'>".number_format($rain["day"]*25.400013716,1)." <smallrainunit>mm";} else if ($rain["units"] =='mm'){echo "<div class=rainconvertercircle style='color:$colorRainDaySum;'>".number_format($rain["day"]*0.0393701,2)." <smallrainunit>in";}
} else {
if ($rain["units"] =='in'){echo "<div class=rainconvertercircle style='background:$colorRainDaySum;'>".number_format($rain["day"]*25.400013716,1)." <smallrainunit>mm";} else if ($rain["units"] =='mm'){echo "<div class=rainconvertercircle style='background:$colorRainDaySum;'>".number_format($rain["day"]*0.0393701,2)." <smallrainunit>in";}
}
?></span>
</div></div>
<script src="js/d3.7.9.0.min.js"></script>

<div class="piezo-module"></div>
<script>

var baseTextColor = "var(--col-6)";
    
var colorRain = "<?php echo $colorRainDaySum;?>";
                   
var units = "<?php echo $rain["units"];?>";

if (units == 'in') {
var stormRain = <?php echo $rain["storm_rain"]/25.4;?>;    
} else {
    stormRain = <?php echo $rain["storm_rain"];?>;
}

var currentRain = "<?php echo $rain["current"];?>";
    //currentRain = currentRain || 0;                   
var stormRainColor ="<?php echo $colorStormRain;?>";
var rainRateColor = "<?php echo $colorRainRate;?>";
var lastHourColor = "<?php echo $colorRain1hrSum;?>";
var last24HoursColor = "<?php echo $colorRain24hrSum;?>"; 
var rainMonthColor = "<?php echo $colorRainMonthSum;?>";
var rainYearColor = "<?php echo $colorRainYearSum;?>";

var stormStart = "<?php echo $rain["storm_start"];?>"; 
var rainRate = <?php echo $rain["rate"];?>;
var lastHour = <?php echo $rain["last_hour"];?>;
var last24Hours = <?php echo round($rain["24h_total"],1);?>;
var rainMonth = <?php echo $rain["month_total"];?>; 
var rainYear = <?php echo $rain["year_total"];?>;
var month = "<?php echo date('F');?>"; 
var year = <?php echo date('Y');?>;                   

var svg = d3.select(".piezo-module")
    .append("svg")
    .attr("width", 310)
    .attr("height", 150);

svg.append("text")
    .attr("x", 283)
    .attr("y", 70)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("Storm Rain");

svg.append("text")
    .attr("x", 283)
    .attr("y", 82)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(stormRain + " " + units);

svg.append("text")
    .attr("x", 35)
    .attr("y", 70)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("Last 24 Hours");

svg.append("text")
    .attr("x", 35)
    .attr("y", 82)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(last24Hours + " " + units);

svg.append("text")
    .attr("x", 37)
    .attr("y", 10)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("Last Hour");

svg.append("text")
    .attr("x", 37)
    .attr("y", 20)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(d3.format(".1f")(lastHour) + " " + units);

svg.append("text")
    .attr("x", 37)
    .attr("y", 136)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(month);

svg.append("text")
    .attr("x", 37)
    .attr("y", 148)
    .style("fill", rainMonthColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(d3.format(".1f")(rainMonth) + " " + units);

svg.append("text")
    .attr("x", 272)
    .attr("y", 136)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(year);

svg.append("text")
    .attr("x", 272)
    .attr("y", 148)
    .style("fill", rainYearColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(d3.format(".1f")(rainYear) + " " + units);

svg.append("text")
    .attr("x", 155)
    .attr("y", 105)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "14px")
    .style("text-anchor", "middle")
    .style("font-weight", "bold")
    .text(d3.format(".1f")(currentRain) + " " + rainunits);

svg.append("text")
    .attr("x", 155)
    .attr("y", 136)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("Rain Rate");

svg.append("text")
    .attr("x", 153.5)
    .attr("y", 148)
    .style("fill", rainRateColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(rainRate + " " + units + "/hr");

svg.append("rect")
    .attr("x", 113)
    .attr("y", 52)
    .attr("rx", 2)
    .attr("width", 85)
    .attr("height", 28)
    .style("fill", colorRain);     

svg.append("rect")
    .attr("x", 113)
    .attr("y", 52)
    .attr("rx", 2)
    .attr("width", 85)
    .attr("height", 28)
    .style("stroke", "black")
    .style("stroke-width", 1.5)
    .style("fill", "none");

svg.append("rect")
    .attr("x", 105)
    .attr("y", 10)
    .attr("rx", 10)
    .attr("width", 102)
    .attr("height", 48)
    .style("fill", colorRain);

svg.append("rect")
    .attr("x", 105)
    .attr("y", 10)
    .attr("rx", 10)
    .attr("width", 102)
    .attr("height", 48)
    .style("stroke", "black")
    .style("stroke-width", 1.5)
    .style("fill", "none");

</script>

<div id="raindropsX" width="300" height="150" style="position: relative; top: -156px; left: 0px;"></div>
<script>

var raining = <?php echo $rain["rate"];?>;

if ( raining > 0 ) {
  
var ns = "http://www.w3.org/2000/svg";
var xlink = "http://www.w3.org/1999/xlink";

function randBetween(min, max) {
  return Math.floor(Math.random() * (max - min + 1)) + min;
}

class Rain {
  constructor(element) {
    this.el = element;
    this.width = 300;
    this.height = 150;

    this.drops = 128;
    this.dropDurationMin = 400;
    this.dropDurationMax = 800;
    this.dropHeightMin = 2;
    this.dropHeightMax = 5;
    this.dropWidthMin = 3;
    this.dropWidthMax = 6;

    this.splashes = 16;
    this.splashDuration = 500;
    this.splashRadiusXMin = 6;
    this.splashRadiusXMax = 10;
    this.splashRadiusY = 2;

    this.svg = document.createElementNS(ns, 'svg');
    this.svg.setAttribute('viewBox', `0 0 ${this.width} ${this.height}`);
    this.svg.setAttribute('xmlns', ns);
    this.svg.setAttribute('xmlns:xlink', xlink);
    this.svg.setAttribute('id', 'rain');

    this.filters = false;

    if (this.filters) {
      this.appendFilters();
    }

    this.appendTrack();
  }

  appendFilters() {
    let blur = document.createElementNS(ns, 'feGaussianBlur');
    blur.setAttribute('in', 'SourceGraphic');
    blur.setAttribute('stdDeviation', 1);

    let filter = document.createElementNS(ns, 'filter');
    filter.setAttribute('id', `blur`);
    filter.setAttribute('height', '300%');
    filter.setAttribute('width', '300%');
    filter.setAttribute('x', '-100%');
    filter.setAttribute('y', '-100%');
    filter.appendChild(blur);

    this.svg.appendChild(filter);
  }

  appendTrack() {
    let track = document.createElementNS(ns, 'path');
    track.setAttribute('id', 'track');
    track.setAttribute('fill', 'none');
    track.setAttribute('stroke', 'none');
    track.setAttribute('d', `M 0 -${this.height * 0.1} V ${this.height * 1.1}`);

    this.svg.appendChild(track);
  }

  makeDrop(index) {
    let drop = document.createElementNS(ns, 'rect');
    drop.setAttribute('fill', `rgba(59, 156, 172, ${randBetween(1, 3) * 0.4})`);
    drop.setAttribute('height', randBetween(this.dropHeightMin, this.dropHeightMax));
    drop.setAttribute('id', `drop-${index}`);
    drop.setAttribute('rx', 1);
    drop.setAttribute('width', randBetween(this.dropWidthMin, this.dropWidthMax) * 0.1);
    drop.setAttribute('x', 0);
    drop.setAttribute('y', 0);

    if (this.filters) {
      drop.setAttribute('filter', 'url(#blur)');
    }

    let group = document.createElementNS(ns, 'g');
    group.setAttribute('transform', `translate(${randBetween(0, this.width)}, -${this.height * 0.05})`);
    group.appendChild(drop);

    return group;
  }

  makeMotion(index) {
    let motionPath = document.createElementNS(ns, 'mpath');
    motionPath.setAttribute('xlink:href', '#track');

    let motion = document.createElementNS(ns, 'animateMotion');
    motion.setAttribute('xlink:href', `#drop-${index}`);
    motion.setAttribute('dur', `${randBetween(this.dropDurationMin, this.dropDurationMax)}ms`);
    motion.setAttribute('begin', `${randBetween(0, this.dropDurationMin)}ms`);
    motion.setAttribute('repeatCount', 'indefinite');
    motion.appendChild(motionPath);

    return motion;
  }

  makeSplash(index) {
    let begin = randBetween(0, this.splashDuration);
    let animateStroke = document.createElementNS(ns, 'animate');
    animateStroke.setAttribute('attributeName', 'stroke-width');
    animateStroke.setAttribute('dur', `${this.splashDuration}ms`);
    animateStroke.setAttribute('values', '0; 1; 0.5; 0.25; 0');
    animateStroke.setAttribute('repeatCount', 'indefinite');
    animateStroke.setAttribute('begin', `${begin}ms`);

    let randomRadiusX = randBetween(this.splashRadiusXMin, this.splashRadiusXMax);
    let animateRadiusX = document.createElementNS(ns, 'animate');
    animateRadiusX.setAttribute('attributeName', 'rx');
    animateRadiusX.setAttribute('dur', `${this.splashDuration}ms`);
    animateRadiusX.setAttribute('values', `0; ${randomRadiusX}`);
    animateRadiusX.setAttribute('repeatCount', 'indefinite');
    animateRadiusX.setAttribute('additive', 'sum');
    animateRadiusX.setAttribute('begin', `${begin}ms`);

    let ellipse = document.createElementNS(ns, 'ellipse');
    ellipse.setAttribute('stroke', `rgba(59, 156, 172, 0.4)`);
    ellipse.setAttribute('stroke-width', 1);
    ellipse.setAttribute('fill', 'none');
    ellipse.setAttribute('cx', randBetween(0, this.width));
    ellipse.setAttribute('cy', randBetween(this.height * 0.78, this.height));
    ellipse.setAttribute('rx', randomRadiusX);
    ellipse.setAttribute('ry', this.splashRadiusY);

    if (this.filters) {
      ellipse.setAttribute('filter', 'url(#blur)');
    }

    ellipse.appendChild(animateStroke);
    ellipse.appendChild(animateRadiusX);

    return ellipse;
  }

  render() {
    for (let i = 0; i < this.drops; i++) {
      let drop = this.makeDrop(i);
      let motion = this.makeMotion(i);

      this.svg.appendChild(drop);
      this.svg.appendChild(motion);
    }

    for (let i = 0; i < this.splashes; i++) {
      let splash = this.makeSplash(i);

      this.svg.appendChild(splash);
    }

    this.el.innerHTML = this.svg.outerHTML;
  }}


const el = document.getElementById('raindropsX');
const rain = new Rain(el);

rain.render();
}

</script>
</body>
</html>