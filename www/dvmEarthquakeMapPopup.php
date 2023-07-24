<?php
include('dvmCombinedData.php');
date_default_timezone_set($TZ);
echo "<body style='background-color:#292E35'>";
?>

<!DOCTYPE html>
<html lang="en" >
<head>
<meta charset="UTF-8">
  <title>Earthquake Map</title>
  <link rel="stylesheet" href="css/reset.min.css">
<meta name="viewport" content="width = device-width, initial-scale = 1, shrink-to-fit = yes">
</head>
<body>

<h1>Earthquakes in the last 24 Hours</h1>

<style>
.map {
  position: relative;
  margin-top: 16px;
  margin-left: 0px;
}
body {
  overflow: hidden;
}
h1 {
  text-align: center;
  color: silver;
  font-family: Helvetica;
  margin: 1.2em 0;
  font-size: 18px;
}
svg {
  margin: 0 auto;
  display: block;
}
.sphere {
  fill: #bbb;
}
.land {
  fill: #2e8b57;
}
.boundry {
  fill: none;
  stroke: #292E35;
  stroke-linejoin: round;
  stroke-linecap: round;
  stroke-width: 0.5px;
}
.overlay {
  fill: none;
  pointer-events: all;
}
.axisSilver line{
  stroke: silver;
}
.axisSilver path{
  stroke: silver;
}
.axisSilver text{
  fill: silver;
}
</style>

<div class="map"></div>

<script src='js/d3.4.2.2.min.js'></script>
<script src="js/topojson.min.js"></script>

<script>

 // Setup the svg element size and margins
var margin = {top: 20, right: 20, bottom: 20, left: 20},
    width = 780 - margin.left - margin.right,
    height = 550 - margin.top - margin.bottom;

// Set the projection methods for the world map
var projection = d3.geoMercator().translate([width / 2, height / 1.5]).scale((width - 1) / 2 / Math.PI);

// Set the world map path
var path = d3.geoPath().projection(projection);

// Create a variable to hold the main svg element
var svg = d3.select(".map")
    .append("svg")
    .attr("width", width)
    .attr("height", height);

// Group to hold the maps and borders
var g = svg.append('g').attr('id', 'world-map');

// Add a clip path element to the world map group
// for the x axis 
g.append('clipPath')
 .attr('id', 'clip-path')
 .append('rect')
 .attr('x', 0)
 .attr('y', 30)
 .attr('width', width)
 .attr('height', height - 30)

// Group to hold all of the earthquake elements
var gQuakes = svg.append('g').attr('id', 'all-quakes');

// Import the geoJSON file for the world map
// d3.json('https://s3-us-west-2.amazonaws.com/s.cdpn.io/25240/world-110m.json', function(error, world) {
  d3.json('jsondata/worldmap.json', function(error, world) {
  if(error) throw error;
  
  // Append the World Map
  var worldMap = g.append('path')
   .attr('clip-path', 'url(#clip-path)') // attaches the clip path to not draw the map underneath the x axis
   .datum(topojson.merge(world, world.objects.countries.geometries)) // draws a single land object for the entire map
   .attr('class', 'land')
   .attr('d', path)
 
  // Append the World Map Country Borders
  g.append('path')
   .datum(topojson.mesh(world, world.objects.countries, function(a, b) { return a !== b; }))
   .attr('class', 'boundry')
   .attr('d', path);

  // Setup 24 hours ago object
  var dateObj = new Date();
  dateObj.setDate(dateObj.getDate() - 1);

  // Create the x scale based on the domain of the 24 hour ago object and now
  var x = d3.scaleTime()
      .domain([dateObj, new Date()])
      .range([0, width - margin.right - margin.left]);

  var formatTime = d3.timeFormat("%H:%M"); // output 24 hour  

  // Append the xAxis on top
  var xAxis = svg.append('g')
      .attr('id', 'xAxis')
      .attr("class", "axisSilver")
      .attr('transform', 'translate(20, 20)')
      .call(d3.axisTop(x).tickFormat(formatTime)); // add the time formatting to the axis
      
            
  // Import the last 24 hours of earthquake data from USGS
  d3.json('https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/all_day.geojson', function(error, data) {
    if(error) throw error;
    var quake = data.features.reverse();
    
    // Create a group with the quake id to hold the quake circle and pulse circle
    var earthquakeGroups = gQuakes.selectAll('g')
       .data(quake)
       .enter().append('g')
       .attr('id', function(d) {
           return d.id;
       })
       .attr('class', 'quake-group');
    
    // Create the pulse-circle circles for the earthquake pulse
    gQuakes.selectAll('.quake-group')
       .append('circle')
       .attr('class', 'circle pulse-circle')
       .attr('cx', function(d) {
           return projection([d.geometry.coordinates[0], d.geometry.coordinates[1]])[0];
       })
       .attr('cy', function(d) {
           return projection([d.geometry.coordinates[0], d.geometry.coordinates[1]])[1];
       })
       .attr('r', function(d) {
           return 0;
       })
       .attr('fill', '#fff');
    
    // Create the main quake circle with title
    gQuakes.selectAll('.quake-group')
      .append('circle')
      .attr('cx', function(d) {
           return projection([d.geometry.coordinates[0], d.geometry.coordinates[1]])[0];
       })
      .attr('cy', function(d) {
           return projection([d.geometry.coordinates[0], d.geometry.coordinates[1]])[1];
       })
      .attr('r', 0 )
      .attr('class', 'circle quake-circle')
      .style('fill', 'red')
      .style('opacity', 0.75)
      .append('title')
      .text(function(d) {
        return 'Magnitue ' + d.properties.mag + ' ' + d.properties.place;
      });

    // Function that calculates the difference between the earthquake and 24 hours ago and
    // creates a delay property.
    var setQuakeDelay = function() {
      for(var i = 0, max = quake.length; i < max; i++){
        var timeDiff = quake[i].properties.time - dateObj;
        var timeDiffObj = new Date(timeDiff);
        quake[i].delay = Date.parse(timeDiffObj) / 5000; // Speed up the animation, otherwise it would take 24 hours ><
      }
    }
    setQuakeDelay();
    
    // Grab the longest delay for the xAxis marker
    var longestDelay = quake[quake.length - 1].delay;
    
    // Changes the radius of the earthquakes to their magnitue using a transition
    // and the delay created from the setQuakeDelay function
    var quakeCircles = svg.selectAll('.quake-circle')
       .data(quake)
       .transition()
       .delay(function(d) {
         return d.delay;
       })
       .duration(1000)
       .attr('r', function(d) {
         if(d.properties.mag < 0) {
           return 0.1;
         } else {
           return d.properties.mag         
         }
       });
    
    // Changes the radius of the pulse circle to eight times the magnitude
    // and fades out as it expands over two seconds
    var pulseCircles = svg.selectAll('.pulse-circle')
       .data(quake)
       .transition()
       .delay(function(d) {
         return d.delay;
       })
       .duration(2000)
       .attr('r', function(d) {
         if(d.properties.mag < 0) {
           return 0.1 * 8;
         } else {
           return d.properties.mag * 8;
         }
       })
       .style('opacity', 0)
       .remove()
    
    // Add the time marker that moves across the xAxis while the animation it playing.
    // It's not perfectly in sync, but it's close enough for this example. The length of 
    // the animation is equal to the longest delay that we calculated earlier.
    var timeline = xAxis.append('circle')
         .attr('class', 'transition-circle')
         .attr('cx', 0)
         .attr('cy', 0)
         .attr('r', 3)
         .style('fill', 'red')
         .transition()
         .ease(d3.easeLinear)
         .duration(longestDelay + 1000)
         .attr('cx', 701)
  })
})

svg.append("text")  
    .attr("x", 390)
    .attr("y", 480)
    .attr("text-anchor", "middle")
    .style("font-size", "12px")
    .style("font-family", "Helvetica")
    .style("font-weight", "normal")
    .style("fill", "#2E8B57")
    .text("Powered by D3.js");
 
</script>

</body>
</html>