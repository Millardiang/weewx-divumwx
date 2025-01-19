<?php
include('dvmCombinedData.php');
if($theme==="light"){ echo "<body style='background-color:e0eafb'>"; }
else if($theme==="dark"){ echo "<body style='background-color:#292E35'>"; }
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
#      Distributed under terms of the GPLv3. See the file LICENSE.txt for your rights.       #
#    Issues for weewx-divumwx skin template are only addressed via the issues register at    #
#                    https://github.com/Millardiang/weewx-divumwx/issues                     #
##############################################################################################
?>
<!DOCTYPE html>
<html lang="en">
<head> 
<meta charset="utf-8">
<title>Solar Terminator</title>
</head>
<body>
<style>
body {overflow:hidden;}  
.defs { position:absolute;width:0;height:0;visibility:hidden; }
.solar-oscillator svg { margin-Top:42px;margin-left:111.5px;width:var(--earth-width);height:var(--earth-height); }
.solar-oscillator .stroke { fill:none;stroke:rgba(41, 46, 53, 0.8);stroke-width:1px; }
.solar-oscillator .ocean { fill:#99bbff; }
.solar-oscillator .graticule { fill:none;stroke:#444;stroke-width:0.2px;stroke-opacity:1.0; }
.solar-oscillator .land { fill:#2e8b57; }
.solar-oscillator .borders { fill:none;stroke:#292E35;stroke-width:0.2px; }
.solar-oscillator .night { stroke-width:0.1px;stroke:#292e35;fill:#292e35;fill-opacity:0.1; }
.solar-oscillator .civiltwilight { stroke-width:0.1px;stroke:#292e35;fill:#292e35;fill-opacity:0.3; }
.solar-oscillator .nauticaltwilight { stroke-width:0.1px;stroke:#292e35;fill:#292e35;fill-opacity:0.5; }
.solar-oscillator .astronomicaltwilight { stroke-width:0.1px;stroke:#292e35;fill:#292e35;fill-opacity:0.6; }
.ecliptic { stroke:yellow;stroke-width:0.5px;fill:none;}
.dot { fill:red; }
.ring { fill:none;stroke:red; }
</style>
<script src="js/d3.7.9.0.min.js"></script>
<script src="js/d3-geo-projection.4.0.0.min.js"></script>
<script src="js/topojson.3.0.2.min.js"></script>
<figure class="earth">
<div id="Globe" class="solar-oscillator"></div>
</figure>

<script>

// refresh the script every 60 seconds
window.setInterval('refresh()', 60000);     
    function refresh() {
        window.location.reload();
    }

    var π = Math.PI;

function toDegrees(x) {
  return x * (180.0 / π);
}

function toRadians(x) {
  return x * (π / 180.0);
}

    var moonDec = <?php echo $alm["moon_declination"];?>;
	var Ecliptic = <?php echo $alm["ecliptic_angle"];?>;
    var lunarTerminator = <?php echo $alm["moon_ecliptic_angle"];?>; 
	var lat = <?php echo $lat;?>;
	var long = <?php echo $lon;?>;

  	var w = 500;
  	var h = 450;

  	var circle0 = d3.geoCircle().radius(90); // night time (sunrise sunset)
    var circle1 = d3.geoCircle().radius(84); // civil twilight (dawn dusk) -6°
    var circle2 = d3.geoCircle().radius(78); // nautical twilight (dawn dusk) -12°
    var circle3 = d3.geoCircle().radius(72); // astronomical twilight (dawn dusk) -18°
 	  
if (lat > 0.0) {
  	var projection = d3.geoOrthographic()
      	.scale(200)
      	.translate([w/2, h/2])
        .center([0,0])
        .rotate([-long, -Ecliptic, -Ecliptic])
      	.precision(0.1)
      	.clipAngle(90);

    var sunProjection = d3.geoOrthographic()
        .scale(230)
        .rotate([-long, -Ecliptic, -Ecliptic])
        .precision(0.1)
        .translate(projection.translate());

    var moonProjection = d3.geoOrthographic()
        .scale(210)
        .rotate([-long, -Ecliptic, -Ecliptic])
        .precision(0.1)
        .translate(projection.translate());
} else {
    var projection = d3.geoOrthographic()
        .scale(200)
        .translate([w/2, h/2])
        .rotate([-long, Ecliptic, -Ecliptic])
        .precision(0.1)
        .clipAngle(90);

    var sunProjection = d3.geoOrthographic()
        .scale(230)
        .rotate([-long, Ecliptic, -Ecliptic])
        .precision(0.1)
        .translate(projection.translate());

    var moonProjection = d3.geoOrthographic()
        .scale(210)
        .rotate([-long, Ecliptic, -Ecliptic])
        .precision(0.1)
        .translate(projection.translate());
}

  	var path = d3.geoPath()
      	.projection(projection);

    var center = [w/2, h/2];         

  	var graticule = d3.geoGraticule(); 

  	var svg = d3.select("#Globe")
      	.append("svg")
      	//.style("background", "red")      	  
      	.attr("width", 500)      	
      	.attr("height", 450);

    var defs = svg.append("defs");

    var innerColor = "rgb(230, 200, 200)";

    var sunGradient = defs.append("radialGradient")
        .attr("id", "sunGradient")
        .attr("cx", "50%")
        .attr("cy", "50%")
        .attr("r", "50%")
        .attr("fx", "50%")
        .attr("fy", "50%");

    sunGradient.append("stop")
        .attr("offset", "0%")
        .style("stop-color", innerColor);

    sunGradient.append("stop")
        .attr("offset", "90%")
        .style("stop-color", "yellow");

  	svg.append("defs")
      	.append("path")
      	.datum({type: "Sphere"})
      	.attr("id", "sphere")
      	.attr("d", path);

  	var earth = svg.append("g")
      	.attr("id", "earth");

    earth.append("use")
      	.attr("class", "stroke")
      	.attr("xlink:href", "#sphere");    

    earth.append("use")
      	.attr("class", "ocean")
      	.attr("xlink:href", "#sphere");

  	var g = earth.append("g")

  	g.append("path")
      	.datum(graticule)
      	.attr("class", "graticule")
      	.attr("d", path);

    var meridian180 = d3.geoGraticule()
        .step([179.9999, 0])
        .extentMajor([[179.9999, -90 + 1e-6], [180, 90 - 1e-6]])
        .extentMinor([[179.9999, -90 - 1e-6], [180, 90 + 1e-6]]);

    g.append("path")
        .datum(meridian180)
        .style("stroke", "red")
        .style("fill", "none")  
        .style("stroke-width", 0.5)
        .attr("d", path);

    var gmt_meridian = d3.geoGraticule()
        .step([90, 0])
        .extent([[-0.1, -90 -1e-6], [0.1, 90 +1e-6]]);

    g.append("path")
        .datum(gmt_meridian)
        .style("stroke", "red")
        .style("fill", "none") 
        .style("stroke-width", 0.5)
        .attr("d", path);

    var equator = d3.geoGraticule()
        .step([0, 90])
        .extent([[-180, -1], [180, 1]]);

    g.append("path")
        .datum(equator)
        .style("stroke", "red")
        .style("fill", "none") 
        .style("stroke-width", 0.5)
        .attr("d", path);

    var capricorn = d3.geoGraticule()
        .step([0, 23.436556])
        .extentMajor([[-180, -23.536556 + 1e-6], [180, 0]])
        .extentMinor([[-180, -23.536556 - 1e-6], [180, -23.536556 + 1e-6]]);

    g.append("path")
        .datum(capricorn)
        .attr("class", "capricorn")
        .style("stroke", "blue")
        .style("fill", "none")
        .style("stroke-dasharray", ("3, 3")) 
        .style("stroke-width", 0.5)
        .attr("d", path);

    var cancer = d3.geoGraticule()
        .step([0, 23.436556])
        .extentMajor([[-180,  23.436556 + 1e-6], [180, 23.436556 - 1e-6]])
        .extentMinor([[-180, -23.436556 - 1e-6], [180, 23.436556 + 1e-6]]);

    g.append("path")
        .datum(cancer)
        .style("stroke", "blue")
        .style("fill", "none")
        .style("stroke-dasharray", ("3, 3")) 
        .style("stroke-width", 0.5)
        .attr("d", path);

    var arctic = d3.geoGraticule()
        .step([0, 66.563444])
        .extentMajor([[-180, 66.563444 + 1e-6], [180, 66.563444 - 1e-6]])
        .extentMinor([[-180, 0], [180, 0]])();

    g.append("path")
        .datum(arctic)
        .style("stroke", "magenta")
        .style("fill", "none")
        .style("stroke-dasharray", ("3, 3")) 
        .style("stroke-width", 0.5)
        .attr("d", path);

    var antarctic = d3.geoGraticule()
        .step([0, 66.563444])
        .extentMajor([[-180, -66.563444], [180, 0]])
        .extentMinor([[-180, -66.563444 - 1e-6], [180, -66.563444 + 1e-6]]);
     
    g.append("path")
        .datum(antarctic)
        .style("stroke", "magenta")
        .style("fill", "none")
        .style("stroke-dasharray", ("3, 3")) 
        .style("stroke-width", 0.5)
        .attr("d", path);

    g.append("path")
        .datum({type: "LineString", coordinates: [[-180, 0], [-90, -Ecliptic], [0, 0], [90, Ecliptic], [180, 0]]})
        .attr("class", "ecliptic")
        .attr("d", path);

    svg.append("circle")
        .attr("class", "dot")
        .attr("transform", "translate(" + projection([long, lat]) + ")")
        .attr("r", 1);

    setInterval(function() {
    svg.append("circle")
        .attr("class", "ring")
        .attr("transform", "translate(" + projection([long, lat]) + ")")
        .attr("r", 2)
        .style("stroke-width", 1)
        .style("stroke", "red")
        .transition()
        .ease(d3.easeLinear)
        .duration(6000)
        .style("stroke-opacity", 1e-6)
        .style("stroke-width", 1)
        .style("stroke", "red")
        .attr("r", 20)
        .remove();
 }, 1000);

    d3.json("jsondata/worldmap.json").then(function(world) {

    g.insert("path", ".graticule")
        .datum(topojson.feature(world, world.objects.land))
        .attr("class", "land")
        .attr("d", path);

    g.insert("path", ".graticule")
        .datum(topojson.mesh(world, world.objects.countries, function(a, b) { return a !== b; }))
        .attr("class", "borders")
        .attr("d", path); 
 });

    var Δ = 0; // delta
    var solarTerminator = solarPosition(new Date(Date.now() + Δ));

    var Sun = [[solarTerminator[0], solarTerminator[1], 'Solar Terminator']];
console.log(Sun);
    var Moon = [[solarTerminator[0] + lunarTerminator, moonDec, 'Moon']]; 
console.log(Moon);
    svg.selectAll("circle.moon") // floating moon
        .data(Moon).enter()
        .append("circle")
        .attr("cx", d => moonProjection(d)[0])
        .attr("cy", d => moonProjection(d)[1])
        .attr("r", 4) 
        .attr('fill', d => {
    var moonCoordinates = [solarTerminator[0] + lunarTerminator, moonDec];
        moonDistance = d3.geoDistance(moonCoordinates, moonProjection.invert(center));
        return (moonDistance > π / 2) ? 'none' : 'white'; })
        .attr("class", d => { if (d[2] == "Moon") return "moon"; });

    svg.selectAll("circle.sun") // floating sun
        .data(Sun).enter()
        .append("circle")
        .attr("cx", d => sunProjection(d)[0])
        .attr("cy", d => sunProjection(d)[1])
        .attr("r", 10)
        .attr('fill', d => {
    var sunCoordinates = [solarTerminator[0], solarTerminator[1]];
        sunDistance = d3.geoDistance(sunCoordinates, sunProjection.invert(center));
        return (sunDistance > π / 2) ? 'none' : 'url(#sunGradient)'; })
        .attr("class", d => { if (d[2] == "Solar Terminator") return "solar-terminator"; });
         
    var night = g.append("path")
        .attr("class", "night");

    var civil_twilight = g.append("path")
        .attr("class", "civiltwilight");

    var nautical_twilight = g.append("path")        
        .attr("class", "nauticaltwilight");

    var astronomical_twilight = g.append("path")
        .attr("class", "astronomicaltwilight");

    var now = new Date(Date.now() + Δ );

    night.datum(circle0.center(antipode(solarPosition(now)))).attr("d", path);

    civil_twilight.datum(circle1.center(antipode(solarPosition(now)))).attr("d", path);

    nautical_twilight.datum(circle2.center(antipode(solarPosition(now)))).attr("d", path);

    astronomical_twilight.datum(circle3.center(antipode(solarPosition(now)))).attr("d", path);

    Δ += 1;   

function antipode(position) { 
    return [position[0] + 180.0, - position[1]];
 }

function solarPosition(time) { 
    var T = (time - Date.UTC(2000, 0, 1, 12)) / 864e5 / 36525.0, // J2000 noon
        longitude = (d3.utcDay.floor(time) - time) / 864e5 * 360.0 - 180.0;
    return [
      longitude - toDegrees(equationOfTime(T)),
      toDegrees(solarDeclination(T))
    ];
 }

 // Kepler's equation of time
function equationOfTime(T) { 
    var e = eccentricityEarthOrbit(T),
        m = solarGeometricMeanAnomaly(T),
        l = solarGeometricMeanLongitude(T),
        y = Math.tan(obliquityCorrection(T) / 2);
    y *= y;
    return y * Math.sin(2 * l) - 2 * e * Math.sin(m) + 4 * e * y * Math.sin(m) * Math.cos(2 * l) - 0.5 * y * y * Math.sin(4 * l) - 1.25 * e * e * Math.sin(2 * m);
 }

function solarDeclination(T) { 
    return Math.asin(Math.sin(obliquityCorrection(T)) * Math.sin(solarApparentLongitude(T)));
 }

function solarApparentLongitude(T) { 
    return solarTrueLongitude(T) - toRadians((0.00569 + 0.00478 * Math.sin((125.04 - 1934.136 * T) * π / 180.0)));
 }

function solarTrueLongitude(T) { 
    return solarGeometricMeanLongitude(T) + solarEquationOfCenter(T);
 }

function solarGeometricMeanAnomaly(T) { 
    return toRadians((357.52911 + T * (35999.05029 - 0.0001537 * T)));
 }

function solarGeometricMeanLongitude(T) { 
    var l = (280.46646 + T * (36000.76983 + T * 0.0003032)) % 360.0;
    return (l < 0 ? l + 360.0 : l) / 180.0 * π;
 }

function solarEquationOfCenter(T) { 
    var m = solarGeometricMeanAnomaly(T);
    return toRadians((Math.sin(m) * (1.914602 - T * (0.004817 + 0.000014 * T)) + Math.sin(m + m) * (0.019993 - 0.000101 * T) + Math.sin(m + m + m) * 0.000289));
 }

function obliquityCorrection(T) { 
    return meanObliquityOfEcliptic(T) + 0.00256 * toRadians(Math.cos((125.04 - 1934.136 * T) * π / 180.0));
 }

function meanObliquityOfEcliptic(T) { 
    return toRadians((23 + (26 + (21.448 - T * (46.8150 + T * (0.00059 - T * 0.001813))) / 60.0) / 60.0));
 }

function eccentricityEarthOrbit(T) { 
    return 0.016708634 - T * (0.000042037 + 0.0000001267 * T);
 }

</script>
</body>
</html>