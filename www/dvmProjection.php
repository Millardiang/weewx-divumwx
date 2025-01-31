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
if($theme === "light"){ echo "<body style='background-color:#FFFFFF'>";}
else if($theme === "dark"){ echo "<body style='background-color:#292E35'>";}
?>
<!DOCTYPE html>
<html lang="en" >
<head>
<meta charset="UTF-8">
<title>projection</title>
</head>
<body>
<style>
body {
  overflow-x: hidden;
}
.projection {
  height: 50;
  position: relative;
  width: 960px;
  top: 30px;
  left: -78px;
}
#projection-menu {
  position: absolute;
  left: 5px;
  top: 5px;
  background-color: rgb(194,102,58); 
  color: white; 
  border: 2px solid rgb(194,102,58); 
  border-radius: 5px;
}
.stroke {
  fill: none;
  stroke: #000;
  stroke-width: 0;
}
.fill {
  fill: <?php if($theme === "dark"){echo "#292E35";} else {echo "#FFFFFF";}?>;
}
.graticule {
  fill: none;
  stroke: silver;
  stroke-width: .5px;
  stroke-opacity: .5;
}
.land {
  fill: #2e8b57;
}
.boundry {
  fill: none;
  stroke: #292E35;
  stroke-width: 0.5px;
}
</style>

<select id="projection-menu"></select>
<script src="https://d3js.org/d3.v4.min.js"></script>
<script src="https://d3js.org/d3-geo-projection.v1.min.js"></script>
<script src="https://d3js.org/topojson.v2.min.js"></script>

<dvi class="projection"></dvi>
<script>
    
var width = 960,
    height = 500;
var options = [
  {name: "Miller", projection: d3.geoMiller().scale(100)},
  {name: "Orthographic", projection: d3.geoOrthographic()},
];
var i = 0;
var n = options.length - 1;
var projection = options[i].projection;
var path = d3.geoPath(projection);
var graticule = d3.geoGraticule();

var svg = d3.select(".projection").append("svg")
    .attr("width", width)
    //.style("background", background)
    .attr("height", height);
svg.append("defs").append("path")
    .datum({type: "Sphere"})
    .attr("id", "sphere")
    .attr("d", path);
svg.append("use")
    .attr("class", "stroke")
    .attr("xlink:href", "#sphere");
svg.append("use")
    .attr("class", "fill")
    .attr("xlink:href", "#sphere");
svg.append("path")
    .datum(graticule)
    .attr("class", "graticule")
    .attr("d", path);
d3.json("https://raw.githubusercontent.com/d3/d3.github.com/master/world-110m.v1.json", function(error, world) {
  if (error) throw error;
  svg.insert("path", ".graticule")
      .datum(topojson.feature(world, world.objects.land))
      .attr("class", "land")
      .attr("d", path);
  svg.append('path')
   .datum(topojson.mesh(world, world.objects.countries, function(a, b) { return a !== b; }))
   .attr('class', 'boundry')
   .attr('d', path);
});
var menu = d3.select("#projection-menu")
    .on("change", change);
menu.selectAll("option")
    .data(options)
  .enter().append("option")
    .text(function(d) { return d.name; });
update(options[0])
function loop() {
  var j = Math.floor(Math.random() * n);
  menu.property("selectedIndex", i = j + (j >= i));
}
function change() {
  update(options[this.selectedIndex]);
}
function update(option) {
  svg.selectAll("path").interrupt().transition()
      .duration(2000).ease(d3.easeLinear)
      .attrTween("d", projectionTween(projection, projection = option.projection))
}
function projectionTween(projection0, projection1) {
  return function(d) {
    var t = 0;
    var projection = d3.geoProjection(project)
        .scale(1)
        .translate([width / 2, height / 2]);
    var path = d3.geoPath(projection);
    function project(λ, φ) {
      λ *= 180 / Math.PI, φ *= 180 / Math.PI;
      var p0 = projection0([λ, φ]), p1 = projection1([λ, φ]);
      return [(1 - t) * p0[0] + t * p1[0], (1 - t) * -p0[1] + t * -p1[1]];
    }
    return function(_) {
      t = _;
      return path(d);
    };
  };
}
</script>
</body>
</html>