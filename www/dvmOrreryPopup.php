<?php
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
include('dvmCombinedData.php');
date_default_timezone_set($TZ);
//echo "<body style='background-color:#292E35'>";
if($theme === "light"){ echo "<body style='background-color:e0eafb'>";}
else if($theme === "dark"){ echo "<body style='background-color:#292E35'>";}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Solar System Orrery</title>
</head>
<body>

<style>
body { overflow: auto; }
</style>

<script src="js/d3.7.9.0.min.js"></script>

<div class="Orrery"></div>

<script>

var theme = "<?php echo $theme;?>";

if (theme === 'dark') {
    var moonTextColor = "white";
} else {
    moonTextColor = "#2d3a4b";
}


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

var svg = d3.select(".Orrery")
    .append("svg")
    .attr("width", 780)
    .attr("height", 500);

// new d3 particle map to replace the older Two.js particle map.
var rocks = 1000;

var colors = ["#F618CC","#2E8B57","#007FFF","#FF0000","#BE688B","#FFA54F","#F67F40","#5FC6C6","#B6F131","white"];
var color = d3.scaleOrdinal().range(colors).domain(d3.range(0,10));
var nodes = d3.range(rocks).map(function() { return {type: "asteroids"}; });

var node = svg.append("g")
  .selectAll("circle")
  .data(nodes).enter()
  .append("circle")  
    .attr("r", 0.6)
    .attr("fill", function(d) { return d3.rgb(color(d)).darker(0); })   
    .attr('transform', 'translate(260, 275)');
    
var simulation = d3.forceSimulation(nodes)
    .force("charge", d3.forceCollide().radius(2).strength(0.1))
    .force("charge", d3.forceManyBody().distanceMin(1).distanceMax(2))
    .force("radial", d3.forceRadial(function(d) { return d.type = "asteroids", 120; }))
    .velocityDecay(0.5)    
    .on("tick", ticked);

function ticked() {
  node
      .attr("cx", function(d) { return d.x; })
      .attr("cy", function(d) { return d.y; });
}

// Center of Orrery
var centerX = 260;
var centerY = 275;

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
    .attr('fill', '#BE688B')
    .on("mouseover", jupiterMouseOver)
    .on("mousemove", jupiterMouseMove)
    .on("mouseout", jupiterMouseOut);

var tooltipJupiter = d3.select(".Orrery")
    .append("div")
    .style("position", "absolute")
    .style("visibility", "hidden")
    .style("font-family", "Helvetica")
    .style("color", "#BE688B")
    .style("font-size", "7.5px")
    .style("background-color", "#292e35")
    .style("border", "solid #BE688B")
    .style("border-width", "1px")
    .style("border-radius", "5px")
    .style("padding", "10px")
    .style("box-shadow", "2px 2px 20px")
    .style("opacity", "0.9")
    .attr("id", "tooltip");

  function jupiterMouseOver (event, d) {
       d3.select(this);
       tooltipJupiter.style("visibility", "visible");
  };
  
  function jupiterMouseMove (event, d) {
      tooltipJupiter
        .style("top", (event.pageY)+"px").style("left",(event.pageX)+"px")
        .html("Jupiter: " + "<br>" + Jupiter + "°");
  };

  function jupiterMouseOut (event, d) {
       d3.select(this);
       tooltipJupiter.style("visibility", "hidden");
  }; 

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
    .attr('fill', '#FFA54F')
    .on("mouseover", saturnMouseOver)
    .on("mousemove", saturnMouseMove)
    .on("mouseout", saturnMouseOut);

var tooltipSaturn = d3.select(".Orrery")
    .append("div")
    .style("position", "absolute")
    .style("visibility", "hidden")
    .style("font-family", "Helvetica")
    .style("color", "#FFA54F")
    .style("font-size", "7.5px")
    .style("background-color", "#292e35")
    .style("border", "solid #FFA54F")
    .style("border-width", "1px")
    .style("border-radius", "5px")
    .style("padding", "10px")
    .style("box-shadow", "2px 2px 20px")
    .style("opacity", "0.9")
    .attr("id", "tooltip");

  function saturnMouseOver (event, d) {
       d3.select(this);
       tooltipSaturn.style("visibility", "visible");
  };
  
  function saturnMouseMove (event, d) {
      tooltipSaturn
        .style("top", (event.pageY)+"px").style("left",(event.pageX)+"px")
        .html("Saturn: " + "<br>" + Saturn + "°");
  };

  function saturnMouseOut (event, d) {
       d3.select(this);
       tooltipSaturn.style("visibility", "hidden");
  };

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
    .attr('fill', '#F67F40')
    .on("mouseover", uranusMouseOver)
    .on("mousemove", uranusMouseMove)
    .on("mouseout", uranusMouseOut);

var tooltipUranus = d3.select(".Orrery")
    .append("div")
    .style("position", "absolute")
    .style("visibility", "hidden")
    .style("font-family", "Helvetica")
    .style("color", "#F67F40")
    .style("font-size", "7.5px")
    .style("background-color", "#292e35")
    .style("border", "solid #F67F40")
    .style("border-width", "1px")
    .style("border-radius", "5px")
    .style("padding", "10px")
    .style("box-shadow", "2px 2px 20px")
    .style("opacity", "0.9")
    .attr("id", "tooltip");

  function uranusMouseOver (event, d) {
       d3.select(this);
       tooltipUranus.style("visibility", "visible");
  };
  
  function uranusMouseMove (event, d) {
      tooltipUranus
        .style("top", (event.pageY)+"px").style("left",(event.pageX)+"px")
        .html("Uranus: " + "<br>" + Uranus + "°");
  };

  function uranusMouseOut (event, d) {
       d3.select(this);
       tooltipUranus.style("visibility", "hidden");
  };

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
    .attr('fill', '#5FC6C6')
    .on("mouseover", neptuneMouseOver)
    .on("mousemove", neptuneMouseMove)
    .on("mouseout", neptuneMouseOut);

var tooltipNeptune = d3.select(".Orrery")
    .append("div")
    .style("position", "absolute")
    .style("visibility", "hidden")
    .style("font-family", "Helvetica")
    .style("color", "#5FC6C6")
    .style("font-size", "7.5px")
    .style("background-color", "#292e35")
    .style("border", "solid #5FC6C6")
    .style("border-width", "1px")
    .style("border-radius", "5px")
    .style("padding", "10px")
    .style("box-shadow", "2px 2px 20px")
    .style("opacity", "0.9")
    .attr("id", "tooltip");

  function neptuneMouseOver (event, d) {
       d3.select(this);
       tooltipNeptune.style("visibility", "visible");
  };
  
  function neptuneMouseMove (event, d) {
      tooltipNeptune
        .style("top", (event.pageY)+"px").style("left",(event.pageX)+"px")
        .html("Neptune: " + "<br>" + Neptune + "°");
  };

  function neptuneMouseOut (event, d) {
       d3.select(this);
       tooltipNeptune.style("visibility", "hidden");
  };

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
    .attr('fill', '#B6F131')
    .on("mouseover", plutoMouseOver)
    .on("mousemove", plutoMouseMove)
    .on("mouseout", plutoMouseOut);

var tooltipPluto = d3.select(".Orrery")
    .append("div")
    .style("position", "absolute")
    .style("visibility", "hidden")
    .style("font-family", "Helvetica")
    .style("color", "#B6F131")
    .style("font-size", "7.5px")
    .style("background-color", "#292e35")
    .style("border", "solid #B6F131")
    .style("border-width", "1px")
    .style("border-radius", "5px")
    .style("padding", "10px")
    .style("box-shadow", "2px 2px 20px")
    .style("opacity", "0.9")
    .attr("id", "tooltip");

  function plutoMouseOver (event, d) {
       d3.select(this);
       tooltipPluto.style("visibility", "visible");
  };
  
  function plutoMouseMove (event, d) {
      tooltipPluto
        .style("top", (event.pageY)+"px").style("left",(event.pageX)+"px")
        .html("Pluto: " + "<br>" + Pluto + "°");
  };

  function plutoMouseOut (event, d) {
       d3.select(this);
       tooltipPluto.style("visibility", "hidden");
  };

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
    .attr('fill', '#F618CC')
    .on("mouseover", mercuryMouseOver)
    .on("mousemove", mercuryMouseMove)
    .on("mouseout", mercuryMouseOut);

var tooltipMercury = d3.select(".Orrery")
    .append("div")
    .style("position", "absolute")
    .style("visibility", "hidden")
    .style("font-family", "Helvetica")
    .style("color", "#F618CC")
    .style("font-size", "7.5px")
    .style("background-color", "#292e35")
    .style("border", "solid #F618CC")
    .style("border-width", "1px")
    .style("border-radius", "5px")
    .style("padding", "10px")
    .style("box-shadow", "2px 2px 20px")
    .style("opacity", "0.9")
    .attr("id", "tooltip");

  function mercuryMouseOver (event, d) {
       d3.select(this);
       tooltipMercury.style("visibility", "visible");
  };
  
  function mercuryMouseMove (event, d) {
      tooltipMercury
        .style("top", (event.pageY)+"px").style("left",(event.pageX)+"px")
        .html("Mercury: " + "<br>" + Mercury + "°");
  };

  function mercuryMouseOut (event, d) {
       d3.select(this);
       tooltipMercury.style("visibility", "hidden");
  };

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
    .attr('fill', '#2E8B57')
    .on("mouseover", venusMouseOver)
    .on("mousemove", venusMouseMove)
    .on("mouseout", venusMouseOut);

var tooltipVenus = d3.select(".Orrery")
    .append("div")
    .style("position", "absolute")
    .style("visibility", "hidden")
    .style("font-family", "Helvetica")
    .style("color", "#2E8B57")
    .style("font-size", "7.5px")
    .style("background-color", "#292e35")
    .style("border", "solid #2E8B57")
    .style("border-width", "1px")
    .style("border-radius", "5px")
    .style("padding", "10px")
    .style("box-shadow", "2px 2px 20px")
    .style("opacity", "0.9")
    .attr("id", "tooltip");

  function venusMouseOver (event, d) {
       d3.select(this);
       tooltipVenus.style("visibility", "visible");
  };
  
  function venusMouseMove (event, d) {
      tooltipVenus
        .style("top", (event.pageY)+"px").style("left",(event.pageX)+"px")
        .html("Venus: " + "<br>" + Venus + "°");
  };

  function venusMouseOut (event, d) {
       d3.select(this);
       tooltipVenus.style("visibility", "hidden");
  };    

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
    .attr('fill', '#FF0000')
    .on("mouseover", marsMouseOver)
    .on("mousemove", marsMouseMove)
    .on("mouseout", marsMouseOut);

var tooltipMars = d3.select(".Orrery")
    .append("div")
    .style("position", "absolute")
    .style("visibility", "hidden")
    .style("font-family", "Helvetica")
    .style("color", "#FF0000")
    .style("font-size", "7.5px")
    .style("background-color", "#292e35")
    .style("border", "solid #FF0000")
    .style("border-width", "1px")
    .style("border-radius", "5px")
    .style("padding", "10px")
    .style("box-shadow", "2px 2px 20px")
    .style("opacity", "0.9")
    .attr("id", "tooltip");

  function marsMouseOver (event, d) {
       d3.select(this);
       tooltipMars.style("visibility", "visible");
  };
  
  function marsMouseMove (event, d) {
      tooltipMars
        .style("top", (event.pageY)+"px").style("left",(event.pageX)+"px")
        .html("Mars: " + "<br>" + Mars + "°");
  };

  function marsMouseOut (event, d) {
       d3.select(this);
       tooltipMars.style("visibility", "hidden");
  };

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
    .attr('fill', '#007FFF')
    .on("mouseover", earthMouseOver)
    .on("mousemove", earthMouseMove)
    .on("mouseout", earthMouseOut);

var tooltipEarth = d3.select(".Orrery")
    .append("div")
    .style("position", "absolute")
    .style("visibility", "hidden")
    .style("font-family", "Helvetica")
    .style("color", "#007FFF")
    .style("font-size", "7.5px")
    .style("background-color", "#292e35")
    .style("border", "solid #007FFF")
    .style("border-width", "1px")
    .style("border-radius", "5px")
    .style("padding", "10px")
    .style("box-shadow", "2px 2px 20px")
    .style("opacity", "0.9")
    .attr("id", "tooltip");

  function earthMouseOver (event, d) {
       d3.select(this);
       tooltipEarth.style("visibility", "visible");
  };
  
  function earthMouseMove (event, d) {
      tooltipEarth
        .style("top", (event.pageY)+"px").style("left",(event.pageX)+"px")
        .html("Earth: " + "<br>" + Earth + "°");
  };

  function earthMouseOut (event, d) {
       d3.select(this);
       tooltipEarth.style("visibility", "hidden");
  };

if (theme === 'dark') {

// Moon Planet
svg.append("circle")   
    .attr("cx", moonX)
    .attr("cy", moonY)
    .attr("r", 2)
    .attr('fill', 'white')
    .on("mouseover", moonMouseOver)
    .on("mousemove", moonMouseMove)
    .on("mouseout", moonMouseOut);

var tooltipMoon = d3.select(".Orrery")
    .append("div")
    .style("position", "absolute")
    .style("visibility", "hidden")
    .style("font-family", "Helvetica")
    .style("color", "white")
    .style("font-size", "7.5px")
    .style("background-color", "#292e35")
    .style("border", "solid white")
    .style("border-width", "1px")
    .style("border-radius", "5px")
    .style("padding", "10px")
    .style("box-shadow", "2px 2px 20px")
    .style("opacity", "0.9")
    .attr("id", "tooltip");

  function moonMouseOver (event, d) {
       d3.select(this);
       tooltipMoon.style("visibility", "visible");
  };
  
  function moonMouseMove (event, d) {
      tooltipMoon
        .style("top", (event.pageY)+"px").style("left",(event.pageX)+"px")
        .html("Moon: " + "<br>" + Moon + "°");
  };

  function moonMouseOut (event, d) {
       d3.select(this);
       tooltipMoon.style("visibility", "hidden");
  };

} else {

// Moon Planet
svg.append("circle")   
    .attr("cx", moonX)
    .attr("cy", moonY)
    .attr("r", 2)
    .attr('fill', 'white');    

// Moon Planet
svg.append("circle")   
    .attr("cx", moonX)
    .attr("cy", moonY)
    .attr("r", 2)
    .attr('stroke', moonTextColor)
    .attr('fill', 'none') 
    .style("stroke-width", 0.5)
    .on("mouseover", moonMouseOver)
    .on("mousemove", moonMouseMove)
    .on("mouseout", moonMouseOut);

var tooltipMoon = d3.select(".Orrery")
    .append("div")
    .style("position", "absolute")
    .style("visibility", "hidden")
    .style("font-family", "Helvetica")
    .style("color", "white")
    .style("font-size", "7.5px")
    .style("background-color", "#292e35")
    .style("border", "solid white")
    .style("border-width", "1px")
    .style("border-radius", "5px")
    .style("padding", "10px")
    .style("box-shadow", "2px 2px 20px")
    .style("opacity", "0.9")
    .attr("id", "tooltip");

  function moonMouseOver (event, d) {
       d3.select(this);
       tooltipMoon.style("visibility", "visible");
  };
  
  function moonMouseMove (event, d) {
      tooltipMoon
        .style("top", (event.pageY)+"px").style("left",(event.pageX)+"px")
        .html("Moon: " + "<br>" + Moonx + "°");
  };

  function moonMouseOut (event, d) {
       d3.select(this);
       tooltipMoon.style("visibility", "hidden");
  };   
}

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
    .style("fill", moonTextColor)
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
</body>
</html>