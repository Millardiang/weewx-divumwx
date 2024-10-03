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

/*
Astronomical clock coded in D3.js
 
This clock has been translated from an old lua script,
coded by a good friend of mine named Paramvir Likhari.
Based on the Orloj Astronomical clock which can be found in the City of Prague,
it acts like a precision instrument when fed with the correct data.
This is the proper way to display the sun and moon going around a circle.
The sun posision is spot on, the moon is accurate to within 3-4 arc minutes
which is extremely good.
The data has been pre-calculated using the python-ephem library

Created by Sean Balfour in Dresden July 2023
contact: seanbalfourdresden@googlemail.com

Cheers Sean 
*/

include('dvmCombinedData.php');
//echo "<body style='background-color:#292E35'>";
if($theme === "light"){ echo "<body style='background-color:#FFFFFF'>";}
else if($theme === "dark"){ echo "<body style='background-color:#292E35'>";}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Astronomical Clock</title>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=yes">
</head>
<body>

<script src="js/d3.7.9.0.min.js"></script>

<style>
.clockpos {
    margin-top: 0px; 
    margin-left: 0px;
}
@font-face {
    font-family: 'AstroDotBasic';
    src: url('css/fonts/AstroDotBasic.ttf') format('truetype');
}
body {
    overflow: auto;
}
.analog-hours {
    stroke-width: 5;
    stroke: #32CD32;
    stroke-linecap: round;
}
.analog-minutes {
    stroke-width: 4;
    stroke: #00FFFF;
    stroke-linecap: round;
}
.analog-seconds {
    stroke-width: 2.5;
    stroke: #FF00FF;
    stroke-linecap: round;
}
</style>

<div class="clockpos">
<div class="astroclock"></div>
</div>

<script>

var theme = "<?php echo $theme;?>";

if (theme === 'dark') {
    var moonTextColor = "white";
} else {
    moonTextColor = "#2d3a4b";
}
    
// refresh the script every 60 seconds
window.setInterval('refresh()', 60000);     
    function refresh() {
        window.location.reload();
    }
       
function toDegrees(x) {
  return x * (180.0 / Math.PI);
}

function toRadians(x) {
  return x * (Math.PI / 180.0);
}

function toDegRad(x, f) { 
  return toRadians(x * f); 
}

var hour_sun = <?php echo $alm["hour_sun"];?>;       
var hourSun = toDegrees(hour_sun);    
var sun_Ra = <?php echo $alm["sun_right_ascension"];?>;                        
var hour_moon = <?php echo $alm["hour_moon"];?>;        
var hourMoon = toDegrees(hour_moon);    
var hours_arc = <?php echo $alm["hour_sun"];?>;
var lmst = (hourSun / 15.0) + (sun_Ra / 15.0);  
var Lha = (lmst * 15.0) - 90.0;            
var hourAries = toRadians(Lha);
       
let sr = "<?php echo $alm["sunrise"];?>";
    
    let srarr = sr.split(":");
    let srhour = parseInt(srarr[0]);
    let srmin = parseInt(srarr[1]);
    var srmins = 360.0 / 60.0 * srmin;
    var sunrise = (360.0 / 24.0 * srhour + srmins / 24.0);
    
let smt = "<?php echo $alm["sun_meridian_transit"];?>";

    let smtarr = smt.split(":");
    let smthour = parseInt(smtarr[0]);
    let smtmin = parseInt(smtarr[1]);
    var smtmins = 360.0 / 60.0 * smtmin;
    var sun_meridian_transit = (360.0 / 24.0 * smthour + smtmins / 24.0);
    
let ss = "<?php echo $alm["sunset"];?>";
    
    let ssarr = ss.split(":");
    let sshour = parseInt(ssarr[0]);
    let ssmin = parseInt(ssarr[1]);
    var ssmins = 360.0 / 60.0 * ssmin;
    var sunset = (360.0 / 24.0 * sshour + ssmins / 24.0);
    
let mr = "<?php echo $alm["moonrise"];?>";
    
    let mrarr = mr.split(":");
    let mrhour = parseInt(mrarr[0]);
    let mrmin = parseInt(mrarr[1]);
    var mrmins = 360.0 / 60.0 * mrmin;
    var moonrise = (360.0 / 24.0 * mrhour + mrmins / 24.0);
    
let mmt = "<?php echo $alm["moon_meridian_transit"];?>";
    
    let mmtarr = mmt.split(":");
    let mmthour = parseInt(mmtarr[0]);
    let mmtmin = parseInt(mmtarr[1]);
    var mmtmins = 360.0 / 60.0 * mmtmin;
    var moon_meridian_transit = (360.0 / 24.0 * mmthour + mmtmins / 24.0);
    
let ms = "<?php echo $alm["moonset"];?>";
    
    let msarr = ms.split(":");
    let mshour = parseInt(msarr[0]);
    let msmin = parseInt(msarr[1]);
    var msmins = 360.0 / 60.0 * msmin;
    var moonset = (360.0 / 24.0 * mshour + msmins / 24.0);
    
let ctr = "<?php echo $alm["civil_twilight_begin"];?>";
    
    let ctrarr = ctr.split(":");
    let ctrhour = parseInt(ctrarr[0]);
    let ctrmin = parseInt(ctrarr[1]);
    var ctrmins = 360.0 / 60.0 * ctrmin;
    var civil_twilight_rise = (360.0 / 24.0 * ctrhour + ctrmins / 24.0);
    
let cts = "<?php echo $alm["civil_twilight_end"];?>";
    
    let ctsarr = cts.split(":");
    let ctshour = parseInt(ctsarr[0]);
    let ctsmin = parseInt(ctsarr[1]);
    var ctsmins = 360.0 / 60.0 * ctsmin;
    var civil_twilight_set = (360.0 / 24.0 * ctshour + ctsmins / 24.0);
    
let ntr = "<?php echo $alm["nautical_twilight_begin"];?>";
    
    let ntrarr = ntr.split(":");
    let ntrhour = parseInt(ntrarr[0]);
    let ntrmin = parseInt(ntrarr[1]);
    var ntrmins = 360.0 / 60.0 * ntrmin;
    var nautical_twilight_rise = (360.0 / 24.0 * ntrhour + ntrmins / 24.0);
    
let nts = "<?php echo $alm["nautical_twilight_end"];?>";
    
    let ntsarr = nts.split(":");
    let ntshour = parseInt(ntsarr[0]);
    let ntsmin = parseInt(ntsarr[1]);
    var ntsmins = 360.0 / 60.0 * ntsmin;
    var nautical_twilight_set = (360.0 / 24.0 * ntshour + ntsmins / 24.0);
    
let atr = "<?php echo $alm["astronomical_twilight_begin"];?>";
    
    let atrarr = atr.split(":");
    let atrhour = parseInt(atrarr[0]);
    let atrmin = parseInt(atrarr[1]);
    var atrmins = 360.0 / 60.0 * atrmin;
    var astro_twilight_rise = (360.0 / 24.0 * atrhour + atrmins / 24.0);
    
let ats = "<?php echo $alm["astronomical_twilight_end"];?>";
    
    let atsarr = ats.split(":");
    let atshour = parseInt(atsarr[0]);
    let atsmin = parseInt(atsarr[1]);
    var atsmins = 360.0 / 60.0 * atsmin;
    var astro_twilight_set = (360.0 / 24.0 * atshour + atsmins / 24.0);
     
var svg = d3.select(".astroclock")
    .append("svg")
    .attr("width", 780)
    .attr("height", 500);

var aries = "a";
svg.append("text")
    .attr("x", 500)
    .attr("y", 171)
    .style("fill", "rgba(46,139,87,1)")
    .style("font-family", "AstroDotBasic")
    .style("font-size", "20px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(aries);

var taurus = "b";
svg.append("text")
    .attr("x", 500)
    .attr("y", 191)
    .style("fill", "rgba(46,139,87,1)")
    .style("font-family", "AstroDotBasic")
    .style("font-size", "20px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(taurus);

var gemini = "c";
svg.append("text")
    .attr("x", 500)
    .attr("y", 211)
    .style("fill", "rgba(46,139,87,1)")
    .style("font-family", "AstroDotBasic")
    .style("font-size", "20px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(gemini);

var cancer = "d";
svg.append("text")
    .attr("x", 500)
    .attr("y", 231)
    .style("fill", "rgba(46,139,87,1)")
    .style("font-family", "AstroDotBasic")
    .style("font-size", "20px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(cancer);

var leo = "e";
svg.append("text")
    .attr("x", 500)
    .attr("y", 251)
    .style("fill", "rgba(46,139,87,1)")
    .style("font-family", "AstroDotBasic")
    .style("font-size", "20px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(leo);

var virgo = "f";
svg.append("text")
    .attr("x", 500)
    .attr("y", 271)
    .style("fill", "rgba(46,139,87,1)")
    .style("font-family", "AstroDotBasic")
    .style("font-size", "20px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(virgo);

var libra = "g";
svg.append("text")
    .attr("x", 500)
    .attr("y", 291)
    .style("fill", "rgba(46,139,87,1)")
    .style("font-family", "AstroDotBasic")
    .style("font-size", "20px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(libra);

var scorpio = "h";
svg.append("text")
    .attr("x", 500)
    .attr("y", 311)
    .style("fill", "rgba(46,139,87,1)")
    .style("font-family", "AstroDotBasic")
    .style("font-size", "20px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(scorpio);

var sagittarius = "i";
svg.append("text")
    .attr("x", 500)
    .attr("y", 331)
    .style("fill", "rgba(46,139,87,1)")
    .style("font-family", "AstroDotBasic")
    .style("font-size", "20px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(sagittarius);

var capricorn = "j";
svg.append("text")
    .attr("x", 500)
    .attr("y", 351)
    .style("fill", "rgba(46,139,87,1)")
    .style("font-family", "AstroDotBasic")
    .style("font-size", "20px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(capricorn);

var aquarius = "k";
svg.append("text")
    .attr("x", 500)
    .attr("y", 371)
    .style("fill", "rgba(46,139,87,1)")
    .style("font-family", "AstroDotBasic")
    .style("font-size", "20px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(aquarius);

var pisces = "l";
svg.append("text")
    .attr("x", 500)
    .attr("y", 391)
    .style("fill", "rgba(46,139,87,1)")
    .style("font-family", "AstroDotBasic")
    .style("font-size", "20px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(pisces);

var Title = "Astronomical Clock";
svg.append("text")
    .attr("x", 520)
    .attr("y", 168)
    .style("fill", "rgba(255,99,71,1)")
    .style("font-family", "Helvetica")
    .style("font-size", "14px")
    .style("text-anchor", "left")
    .style("font-weight", "bold")
    .text(Title);

var data = ["Based on the "+"-"+"Orloj"+"-"+" in the City of Prague"];

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 520)
    .attr("y", function(d, i) {
    return 186 + i * 186;
    })

    .style("fill", "rgba(46,139,87,1)")
    .style("font-family", "Helvetica")
    .style("font-size", "14px")
    .style("text-anchor", "left")
    .style("font-weight", "normal")
    .text(function(d) {
    return d.split("-")[0];
    })

    .append("tspan")
    .style("fill", "rgba(0,127,255,1)")
    .text(function(d) {
    return d.split("-")[1];
    })

    .append("tspan")
    .style("fill", "rgba(46,139,87,1)")
    .text(function(d) {
    return d.split("-")[2];
    });

svg.append("line")
    .attr("x1", 520)
    .attr("x2", 766)
    .attr("y1", 196)
    .attr("y2", 196)
    .style("stroke", "rgba(0,127,255,1)")
    .style("stroke-width", 1.75)
    .style("stroke-linecap", "round");

var Delta = "Location: <?php echo $stationlocation;?>";
svg.append("text")
    .attr("x", 520)
    .attr("y", 216)
    .style("fill", "#FFA54F")
    .style("font-family", "Helvetica")
    .style("font-size", "14px")
    .style("text-anchor", "left")
    .style("font-weight", "normal")
    .text(Delta);

var Latitude = "Latitude: <?php echo $lat;?>°";
svg.append("text")
    .attr("x", 520)
    .attr("y", 232)
    .style("fill", "rgba(0,127,255,1)")
    .style("font-family", "Helvetica")
    .style("font-size", "14px")
    .style("text-anchor", "left")
    .style("font-weight", "normal")
    .text(Latitude);

var Longitude = "Longitude: <?php echo $lon;?>°";
svg.append("text")
    .attr("x", 520)
    .attr("y", 248)
    .style("fill", "rgba(0,127,255,1)")
    .style("font-family", "Helvetica")
    .style("font-size", "14px")
    .style("text-anchor", "left")
    .style("font-weight", "normal")
    .text(Longitude);

var Elevation = "Elevation: <?php echo $elevation;?>m";
svg.append("text")
    .attr("x", 520)
    .attr("y", 264)
    .style("fill", "rgba(0,127,255,1)")
    .style("font-family", "Helvetica")
    .style("font-size", "14px")
    .style("text-anchor", "left")
    .style("font-weight", "normal")
    .text(Elevation);

var Equinox = "Equinox: <?php echo $alm["next_equinox"];?>";
svg.append("text")
    .attr("x", 520)
    .attr("y", 280)
    .style("fill", "#FFA54F")
    .style("font-family", "Helvetica")
    .style("font-size", "14px")
    .style("text-anchor", "left")
    .style("font-weight", "normal")
    .text(Equinox);

var Solstice = "Solstice: <?php echo $alm["next_solstice"];?>";
svg.append("text")
    .attr("x", 520)
    .attr("y", 296)
    .style("fill", "rgba(255,99,71,1)")
    .style("font-family", "Helvetica")
    .style("font-size", "14px")
    .style("text-anchor", "left")
    .style("font-weight", "normal")
    .text(Solstice);

var Luminance = "Luminance: <?php echo number_format($alm["luminance"],2);?> %";
svg.append("text")
    .attr("x", 520)
    .attr("y", 312)
    .style("fill", moonTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "14px")
    .style("text-anchor", "left")
    .style("font-weight", "normal")
    .text(Luminance);

var Moonphase = "Moon phase: <?php echo $alm["moonphase"];?>";
svg.append("text")
    .attr("x", 520)
    .attr("y", 326.75)
    .style("fill", moonTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "14px")
    .style("text-anchor", "left")
    .style("font-weight", "normal")
    .text(Moonphase);

var MoonAge = "Moon cycle: <?php echo number_format($alm["moon_age"],2);?> Days old";
svg.append("text")
    .attr("x", 520)
    .attr("y", 342.75)
    .style("fill", moonTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "14px")
    .style("text-anchor", "left")
    .style("font-weight", "normal")
    .text(MoonAge);

var Hmoon = "Moon azimuth: <?php echo $alm["moon_azimuth"];?>°";
svg.append("text")
    .attr("x", 520)
    .attr("y", 358.75)
    .style("fill", moonTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "14px")
    .style("text-anchor", "left")
    .style("font-weight", "normal")
    .text(Hmoon);

var Hsun = "Sun azimuth: <?php echo $alm["sun_azimuth"];?>°";
svg.append("text")
    .attr("x", 520)
    .attr("y", 374.75)
    .style("fill", "rgba(255,99,71,1)")
    .style("font-family", "Helvetica")
    .style("font-size", "14px")
    .style("text-anchor", "left")
    .style("font-weight", "normal")
    .text(Hsun);

var LST = "Hand of Aries: <?php echo $alm["sidereal_time"];?>°";
svg.append("text")
    .attr("x", 520)
    .attr("y", 390.75)
    .style("fill", "rgba(0,127,255,1)")
    .style("font-family", "Helvetica")
    .style("font-size", "14px")
    .style("text-anchor", "left")
    .style("font-weight", "normal")
    .text(LST);

var PB = "Powered by D3.js";
svg.append("text")
    .attr("x", 520)
    .attr("y", 414)
    .style("fill", "rgba(46,139,87,1)")
    .style("font-family", "Helvetica")
    .style("font-size", "14px")
    .style("text-anchor", "left")
    .style("font-weight", "normal")
    .text(PB);

 svg.append("circle")
    .attr("fill", "rgba(42,86,147,1)")    
    .attr("r", 140)
    .attr("cx", 260)
    .attr("cy", 275);

var arc = d3.arc().innerRadius(100).outerRadius(100);
var sector = svg.append("path")
    .attr("fill", "none")
    .attr("stroke-width", 0.5)
    .attr("stroke", "rgba(137,142,143,1)")
    .attr("d", arc({ startAngle: - 17 * Math.PI / 180, endAngle: + 17 * Math.PI / 180 }))
    .attr("transform", "translate(260,42) rotate(180)");    

var arc = d3.arc().innerRadius(165).outerRadius(165);
var sector = svg.append("path")
    .attr("fill", "none")
    .attr("stroke-width", 0.5)
    .attr("stroke", "rgba(137,142,143,1)")
    .attr("d", arc({ startAngle: - 20 * Math.PI / 180, endAngle: + 20 * Math.PI / 180 }))
    .attr("transform", "translate(260,-8.5) rotate(180)");

var arc = d3.arc().innerRadius(242.5).outerRadius(242.5);
var sector = svg.append("path")
    .attr("fill", "none")
    .attr("stroke-width", 0.5)
    .attr("stroke", "rgba(137,142,143,1)")
    .attr("d", arc({ startAngle: - 18 * Math.PI / 180, endAngle: + 18 * Math.PI / 180 }))
    .attr("transform", "translate(260,-72.5) rotate(180)");

var arc = d3.arc().innerRadius(388).outerRadius(388);
var sector = svg.append("path")
    .attr("fill", "none")
    .attr("stroke-width", 0.5)
    .attr("stroke", "rgba(137,142,143,1)")
    .attr("d", arc({ startAngle: - 14 * Math.PI / 180, endAngle: + 14 * Math.PI / 180 }))
    .attr("transform", "translate(260,-205) rotate(180)");

var arc = d3.arc().innerRadius(770).outerRadius(770);
var sector = svg.append("path")
    .attr("fill", "none")
    .attr("stroke-width", 0.5)
    .attr("stroke", "rgba(137,142,143,1)")
    .attr("d", arc({ startAngle: - 8.1 * Math.PI / 180, endAngle: + 8.1 * Math.PI / 180 }))
    .attr("transform", "translate(260,-575) rotate(180)");

svg.append("line")
    .attr("x1", 138)
    .attr("x2", 382)
    .attr("y1", 207.5)
    .attr("y2", 207.5)
    .style("stroke", "rgba(137,142,143,1)")
    .style("stroke-width", 0.5)
    .style("stroke-linecap", "round");

svg.append("line")
    .attr("x1", 260)
    .attr("x2", 260)
    .attr("y1", 136)
    .attr("y2", 215)
    .style("stroke", "rgba(137,142,143,1)")
    .style("stroke-width", 0.5)
    .style("stroke-linecap", "round");

var arc = d3.arc().innerRadius(770).outerRadius(770);
var sector = svg.append("path")
    .attr("fill", "none")
    .attr("stroke-width", 0.5)
    .attr("stroke", "rgba(137,142,143,1)")
    .attr("d", arc({ startAngle: - 9.85 * Math.PI / 180, endAngle: + 9.85 * Math.PI / 180 }))
    .attr("transform", "translate(260,989.5)");

var arc = d3.arc().innerRadius(388).outerRadius(388);
var sector = svg.append("path")
    .attr("fill", "none")
    .attr("stroke-width", 0.5)
    .attr("stroke", "rgba(137,142,143,1)")
    .attr("d", arc({ startAngle: - 20.9 * Math.PI / 180, endAngle: + 20.9 * Math.PI / 180 }))
    .attr("transform", "translate(260,620)");

var arc = d3.arc().innerRadius(242.5).outerRadius(242.5);
var sector = svg.append("path")
    .attr("fill", "none")
    .attr("stroke-width", 0.5)
    .attr("stroke", "rgba(137,142,143,1)")
    .attr("d", arc({ startAngle: - 35 * Math.PI / 180, endAngle: + 35 * Math.PI / 180 }))
    .attr("transform", "translate(260,487.5)");

var arc = d3.arc().innerRadius(142).outerRadius(142);
var sector = svg.append("path")
    .attr("fill", "none")
    .attr("stroke-width", 0.5)
    .attr("stroke", "rgba(137,142,143,1)")
    .attr("d", arc({ startAngle: - 74 * Math.PI / 180, endAngle: + 74 * Math.PI / 180 }))
    .attr("transform", "translate(260,205) rotate(180)");

var arc = d3.arc().innerRadius(145.5).outerRadius(145.5);
var sector = svg.append("path")
    .attr("fill", "none")
    .attr("stroke-width", 0.5)
    .attr("stroke", "rgba(137,142,143,1)")
    .attr("d", arc({ startAngle: - 94 * Math.PI / 180, endAngle: + 51 * Math.PI / 180 }))
    .attr("transform", "translate(233.5,208) rotate(180)");

var arc = d3.arc().innerRadius(145.5).outerRadius(145.5);
var sector = svg.append("path")
    .attr("fill", "none")
    .attr("stroke-width", 0.5)
    .attr("stroke", "rgba(137,142,143,1)")
    .attr("d", arc({ startAngle: - 51 * Math.PI / 180, endAngle: + 94 * Math.PI / 180 }))
    .attr("transform", "translate(286.5,208) rotate(180)");

var arc = d3.arc().innerRadius(142).outerRadius(142);
var sector = svg.append("path")
    .attr("fill", "none")
    .attr("stroke-width", 0.5)
    .attr("stroke", "rgba(137,142,143,1)")
    .attr("d", arc({ startAngle: - 35.5 * Math.PI / 180, endAngle: + 104.6 * Math.PI / 180 }))
    .attr("transform", "translate(303,208) rotate(180)");

var arc = d3.arc().innerRadius(142).outerRadius(142);
var sector = svg.append("path")
    .attr("fill", "none")
    .attr("stroke-width", 0.5)
    .attr("stroke", "rgba(137,142,143,1)")
    .attr("d", arc({ startAngle: - 23 * Math.PI / 180, endAngle: + 22 * Math.PI / 180 }))
    .attr("transform", "translate(320,208) rotate(270)");

var arc = d3.arc().innerRadius(162).outerRadius(162);
var sector = svg.append("path")
    .attr("fill", "none")
    .attr("stroke-width", 0.5)
    .attr("stroke", "rgba(137,142,143,1)")
    .attr("d", arc({ startAngle: - 18 * Math.PI / 180, endAngle: + 23 * Math.PI / 180 }))
    .attr("transform", "translate(356,208) rotate(270)");

var arc = d3.arc().innerRadius(220).outerRadius(220);
var sector = svg.append("path")
    .attr("fill", "none")
    .attr("stroke-width", 0.5)
    .attr("stroke", "rgba(137,142,143,1)")
    .attr("d", arc({ startAngle: - 8 * Math.PI / 180, endAngle: + 17.7 * Math.PI / 180 }))
    .attr("transform", "translate(430,208) rotate(270)");

var arc = d3.arc().innerRadius(280).outerRadius(280);
var sector = svg.append("path")
    .attr("fill", "none")
    .attr("stroke-width", 0.5)
    .attr("stroke", "rgba(137,142,143,1)")
    .attr("d", arc({ startAngle: - 4 * Math.PI / 180, endAngle: + 14.6 * Math.PI / 180 }))
    .attr("transform", "translate(503,208) rotate(270)");

var arc = d3.arc().innerRadius(350).outerRadius(350);
var sector = svg.append("path")
    .attr("fill", "none")
    .attr("stroke-width", 0.5)
    .attr("stroke", "rgba(137,142,143,1)")
    .attr("d", arc({ startAngle: - 2.1 * Math.PI / 180, endAngle: + 12 * Math.PI / 180 }))
    .attr("transform", "translate(585,208) rotate(270)");

var arc = d3.arc().innerRadius(650).outerRadius(650);
var sector = svg.append("path")
    .attr("fill", "none")
    .attr("stroke-width", 0.5)
    .attr("stroke", "rgba(137,142,143,1)")
    .attr("d", arc({ startAngle: - 0.75 * Math.PI / 180, endAngle: + 6.5 * Math.PI / 180 }))
    .attr("transform", "translate(897.5,208) rotate(270)");

var arc = d3.arc().innerRadius(142).outerRadius(142);
var sector = svg.append("path")
    .attr("fill", "none")
    .attr("stroke-width", 0.5)
    .attr("stroke", "rgba(137,142,143,1)")
    .attr("d", arc({ startAngle: - 14.5 * Math.PI / 180, endAngle: + 27 * Math.PI / 180 }))
    .attr("transform", "translate(216.5,208) rotate(90)");

var arc = d3.arc().innerRadius(142).outerRadius(142);
var sector = svg.append("path")
    .attr("fill", "none")
    .attr("stroke-width", 0.5)
    .attr("stroke", "rgba(137,142,143,1)")
    .attr("d", arc({ startAngle: - 22 * Math.PI / 180, endAngle: + 23 * Math.PI / 180 }))
    .attr("transform", "translate(199.5,208) rotate(90)");

var arc = d3.arc().innerRadius(162).outerRadius(162);
var sector = svg.append("path")
    .attr("fill", "none")
    .attr("stroke-width", 0.5)
    .attr("stroke", "rgba(137,142,143,1)")
    .attr("d", arc({ startAngle: - 22 * Math.PI / 180, endAngle: + 18 * Math.PI / 180 }))
    .attr("transform", "translate(164,208) rotate(90)");

var arc = d3.arc().innerRadius(220).outerRadius(220);
var sector = svg.append("path")
    .attr("fill", "none")
    .attr("stroke-width", 0.5)
    .attr("stroke", "rgba(137,142,143,1)")
    .attr("d", arc({ startAngle: - 17.5 * Math.PI / 180, endAngle: + 8 * Math.PI / 180 }))
    .attr("transform", "translate(90.5,208) rotate(90)");

var arc = d3.arc().innerRadius(280).outerRadius(280);
var sector = svg.append("path")
    .attr("fill", "none")
    .attr("stroke-width", 0.5)
    .attr("stroke", "rgba(137,142,143,1)")
    .attr("d", arc({ startAngle: - 14.5 * Math.PI / 180, endAngle: + 4 * Math.PI / 180 }))
    .attr("transform", "translate(17.5,208) rotate(90)");

var arc = d3.arc().innerRadius(350).outerRadius(350);
var sector = svg.append("path")
    .attr("fill", "none")
    .attr("stroke-width", 0.5)
    .attr("stroke", "rgba(137,142,143,1)")
    .attr("d", arc({ startAngle: - 11.7 * Math.PI / 180, endAngle: + 2.1 * Math.PI / 180 }))
    .attr("transform", "translate(-65,208) rotate(90)");

var arc = d3.arc().innerRadius(650).outerRadius(650);
var sector = svg.append("path")
    .attr("fill", "none")
    .attr("stroke-width", 0.5)
    .attr("stroke", "rgba(137,142,143,1)")
    .attr("d", arc({ startAngle: - 6.5 * Math.PI / 180, endAngle: + 0.75 * Math.PI / 180 }))
    .attr("transform", "translate(-377.5,208) rotate(90)");

var arc = d3.arc().innerRadius(0).outerRadius(140);
var sector = svg.append("path")
    .attr("fill", "rgba(41,46,53,1)")
    .attr("stroke-width", 1)
    .attr("stroke", "rgba(41,46,53,1)")
    .attr("d", arc({ startAngle: - 66 * Math.PI / 180, endAngle: + 66 * Math.PI / 180 }))
    .attr("transform", "translate(260,386)");

var arc = d3.arc().innerRadius(0).outerRadius(142);
var sector = svg.append("path")
    .attr("fill", "rgba(41,46,53,1)")
    .attr("stroke-width", 1)
    .attr("stroke", "rgba(41,46,53,1)")
    .attr("d", arc({ startAngle: - 66 * Math.PI / 180, endAngle: + 66 * Math.PI / 180 }))
    .attr("transform", "translate(260,273) rotate(180)");

var arc = d3.arc().innerRadius(120).outerRadius(120);
var sector = svg.append("path")
    .attr("fill", "none")
    .attr("stroke-width", 0.5)
    .attr("stroke", "rgba(137,142,143,1)")
    .attr("d", arc({ startAngle: - 78 * Math.PI / 180, endAngle: + 78 * Math.PI / 180 }))
    .attr("transform", "translate(260,375)");

var arc = d3.arc().innerRadius(100).outerRadius(100);
var sector = svg.append("path")
    .attr("fill", "none")
    .attr("stroke-width", 0.5)
    .attr("stroke", "rgba(137,142,143,1)")
    .attr("d", arc({ startAngle: - 95 * Math.PI / 180, endAngle: + 95 * Math.PI / 180 }))
    .attr("transform", "translate(260,365)");

var arc = d3.arc().innerRadius(80).outerRadius(80);
var sector = svg.append("path")
    .attr("fill", "none")
    .attr("stroke-width", 0.5)
    .attr("stroke", "rgba(137,142,143,1)")
    .attr("d", arc({ startAngle: - 121 * Math.PI / 180, endAngle: + 121 * Math.PI / 180 }))
    .attr("transform", "translate(260,355)");

var sunRing = svg.append("defs");

var gRing = sunRing.append("linearGradient")
    .attr("id","colorBleed")
    .attr("x1","0")
    .attr("y1","0")
    .attr("x2","0")
    .attr("y2","1"); 

gRing.append('stop')
    .attr('stop-color', "rgba(255,255,0,1)")
    .attr('offset', '0%');

gRing.append('stop')
    .attr('stop-color', "rgba(255,0,0,1)")
    .attr('offset', '50%');

gRing.append('stop')
    .attr('stop-color', "rgba(0,0,255,1)")
    .attr('offset', '100%');

svg.append("circle")
    .attr("stroke", "url(#colorBleed)")    
    .style('stroke-width', "15")
    .style('fill', "none")
    .attr("r", 185)
    .attr("cx", 260)
    .attr("cy", 275);
  
svg.append("circle")
    .attr("stroke", "rgba(42,86,147,1)")    
    .style('stroke-width', "8")
    .style('fill', "none")
    .attr("r", 174)
    .attr("cx", 260)
    .attr("cy", 275);

svg.append("circle")
    .attr("stroke", "rgba(9,70,62,1)")    
    .style('stroke-width', "31")
    .style('fill', "none")
    .attr("r", 155)
    .attr("cx", 260)
    .attr("cy", 275);

svg.append("circle")
    .attr("stroke", "rgba(255,99,71,1)")    
    .style('stroke-width', "1.5")
    .style('fill', "none")
    .attr("r", 171)
    .attr("cx", 260)
    .attr("cy", 275);

svg.append("circle.tropic-of-cancer");
    svg.append("circle")
    .attr("class", "tropic-of-cancer")
    .attr("stroke", "rgba(255,99,71,1)")    
    .style('stroke-width', "1.5")
    .style('fill', "none")
    .attr("r", 140)
    .attr("cx", 260)
    .attr("cy", 275);

svg.append("circle.tropic-of-capricorn");
    svg.append("circle")
    .attr("class", "tropic-of-capricorn")
    .attr("stroke", "rgba(255,99,71,1)")    
    .style('stroke-width', "1.5")
    .style('fill', "none")
    .attr("r", 92)
    .attr("cx", 260)
    .attr("cy", 275);

var arc = d3.arc().innerRadius(140).outerRadius(140);
var sector = svg.append("path")
    .attr("fill", "none")
    .attr("stroke-width", 1)
    .attr("stroke", "rgba(255,99,71,1)")
    .attr("d", arc({ startAngle: - 67 * Math.PI / 180, endAngle: + 67 * Math.PI / 180 }))
    .attr("transform", "translate(260,385)");

svg.append("circle.earth-background");
    svg.append("circle")
    .attr("class", "earth-background")   
    .style('fill', "rgba(41,46,53,1)")
    .attr("r", 60)
    .attr("cx", 260)
    .attr("cy", 275);

svg.append("circle.earth");
    svg.append("circle")
    .attr("class", "earth")
    .attr("stroke", "rgba(255,99,71,1)")    
    .style('stroke-width', "1.5")
    .style('fill', "none")
    .attr("r", 60)
    .attr("cx", 260)
    .attr("cy", 275);

var clockCenter = svg
    .append('g')
    .attr('class','clock-center')
    .attr("transform", "translate(260,275)");

var Axis = clockCenter
    .append('g')
    .attr('class','Axis');

Axis.selectAll("text.numbers")
    .data(d3.range(24))
    .enter().append("text")
    .attr("class", "numbers")
    .attr("x", function(d, i){return (155.5) * Math.cos(i * 0.2616 + 45.552);})
    .attr("y", function(d, i){return (155.5) * Math.sin(i * 0.2616 + 45.552);})
    .attr("alignment-baseline", "middle")
    .attr("text-anchor", "middle")
    .style("fill", "yellow")
    .style("font-family", "Helvetica")     
    .style("font-size", "12px")
    .style("font-weight", "bold")
    .style("dominant-baseline", "central")
    .attr("transform", function(d, i){return "translate(0, 1.0)";})
    .text(function(d, i){return (i % 1) ? '' : d ;});

Axis.selectAll("line.Sticksin")
    .data(d3.range(120))
    .enter().append("line")
    .attr("class", "Sticksin")
    .attr("x1", function(d, i){return (142.5) * Math.cos(i / 120 * Math.PI * 2);})
    .attr("y1", function(d, i){return (142.5) * Math.sin(i / 120 * Math.PI * 2);})
    .attr("x2", function(d, i){return (145.5) * Math.cos(i / 120 * Math.PI * 2);})
    .attr("y2", function(d, i){return (145.5) * Math.sin(i / 120 * Math.PI * 2);})
    .style("stroke-width", "1.5px")
    .style("stroke", "#888")
    .style("stroke-linecap", "round")
    .attr("transform", function(d, i){return "translate(0, 0)";});

Axis.selectAll("line.Sticksout")
    .data(d3.range(120))
    .enter().append("line")
    .attr("class", "Sticksout")
    .attr("x1", function(d, i){return (168.5) * Math.cos(i / 120 * Math.PI * 2);})
    .attr("y1", function(d, i){return (168.5) * Math.sin(i / 120 * Math.PI * 2);})
    .attr("x2", function(d, i){return (165.5) * Math.cos(i / 120 * Math.PI * 2);})
    .attr("y2", function(d, i){return (165.5) * Math.sin(i / 120 * Math.PI * 2);})
    .style("stroke-width", "1.5px")
    .style("stroke", "#888")
    .style("stroke-linecap", "round")
    .attr("transform", function(d, i){return "translate(0, 0)";});

Axis.selectAll("line.ticksout")
    .data(d3.range(24))
    .enter().append("line")
    .attr("class", "ticksout")
    .attr("x1", function(d, i){return (165.5) * Math.cos(i / 24 * Math.PI * 2);})
    .attr("y1", function(d, i){return (165.5) * Math.sin(i / 24 * Math.PI * 2);})
    .attr("x2", function(d, i){return (168.5) * Math.cos(i / 24 * Math.PI * 2);})
    .attr("y2", function(d, i){return (168.5) * Math.sin(i / 24 * Math.PI * 2);})
    .style("stroke-width", "2.5px")
    .style("stroke", "red")
    .style("stroke-linecap", "round")
    .attr("transform", function(d, i){return "translate(0, 0)";});

Axis.selectAll("line.ticksin")
    .data(d3.range(24))
    .enter().append("line")
    .attr("class", "ticksin")
    .attr("x1", function(d, i){return (142.5) * Math.cos(i / 24 * Math.PI * 2);})
    .attr("y1", function(d, i){return (142.5) * Math.sin(i / 24 * Math.PI * 2);})
    .attr("x2", function(d, i){return (145.5) * Math.cos(i / 24 * Math.PI * 2);})
    .attr("y2", function(d, i){return (145.5) * Math.sin(i / 24 * Math.PI * 2);})
    .style("stroke-width", "2.5px")
    .style("stroke", "red")
    .style("stroke-linecap", "round")
    .attr("transform", function(d, i){return "translate(0, 0)";});

Axis.selectAll("line.sunrise")
    .data(d3.range(24))
    .enter().append("line")
    .attr("class", "sunrise")
    .attr("x1", function(d, i){return - 177 * Math.sin(toRadians(sunrise));})
    .attr("y1", function(d, i){return + 177 * Math.cos(toRadians(sunrise));})
    .attr("x2", function(d, i){return - 194 * Math.sin(toRadians(sunrise));})
    .attr("y2", function(d, i){return + 194 * Math.cos(toRadians(sunrise));})
    .style("stroke-width", "2.5px")
    .style("stroke", "rgba(255,99,71,1)")
    .style("stroke-linecap", "round")
    .attr("transform", function(d, i){return "translate(0, 0)";});

Axis.selectAll("line.sun_meridian_transit")
    .data(d3.range(24))
    .enter().append("line")
    .attr("class", "sun_meridian_transit")
    .attr("x1", function(d, i){return - 177 * Math.sin(toRadians(sun_meridian_transit));})
    .attr("y1", function(d, i){return + 177 * Math.cos(toRadians(sun_meridian_transit));})
    .attr("x2", function(d, i){return - 194 * Math.sin(toRadians(sun_meridian_transit));})
    .attr("y2", function(d, i){return + 194 * Math.cos(toRadians(sun_meridian_transit));})
    .style("stroke-width", "2.5px")
    .style("stroke", "rgba(255,99,71,1)")
    .style("stroke-linecap", "round")
    .attr("transform", function(d, i){return "translate(0, 0)";});

Axis.selectAll("line.sunset")
    .data(d3.range(24))
    .enter().append("line")
    .attr("class", "sunset")
    .attr("x1", function(d, i){return - 177 * Math.sin(toRadians(sunset));})
    .attr("y1", function(d, i){return + 177 * Math.cos(toRadians(sunset));})
    .attr("x2", function(d, i){return - 194 * Math.sin(toRadians(sunset));})
    .attr("y2", function(d, i){return + 194 * Math.cos(toRadians(sunset));})
    .style("stroke-width", "2.5px")
    .style("stroke", "rgba(255,99,71,1)")
    .style("stroke-linecap", "round")
    .attr("transform", function(d, i){return "translate(0, 0)";});

Axis.selectAll("line.moonrise")
    .data(d3.range(24))
    .enter().append("line")
    .attr("class", "moonrise")
    .attr("x1", function(d, i){return - 177 * Math.sin(toRadians(moonrise || 0));})
    .attr("y1", function(d, i){return + 177 * Math.cos(toRadians(moonrise || 0));})
    .attr("x2", function(d, i){return - 194 * Math.sin(toRadians(moonrise || 0));})
    .attr("y2", function(d, i){return + 194 * Math.cos(toRadians(moonrise || 0));})
    .style("stroke-width", "2.5px")
    .style("stroke", "rgba(255,255,255,1)")
    .style("stroke-linecap", "round")
    .attr("transform", function(d, i){return "translate(0, 0)";});

Axis.selectAll("line.moon_meridian_transit")
    .data(d3.range(24))
    .enter().append("line")
    .attr("class", "moon_meridian_transit")
    .attr("x1", function(d, i){return - 177 * Math.sin(toRadians(moon_meridian_transit || 0));})
    .attr("y1", function(d, i){return + 177 * Math.cos(toRadians(moon_meridian_transit || 0));})
    .attr("x2", function(d, i){return - 194 * Math.sin(toRadians(moon_meridian_transit || 0));})
    .attr("y2", function(d, i){return + 194 * Math.cos(toRadians(moon_meridian_transit || 0));})
    .style("stroke-width", "2.5px")
    .style("stroke", "rgba(255,255,255,1)")
    .style("stroke-linecap", "round")
    .attr("transform", function(d, i){return "translate(0, 0)";});

Axis.selectAll("line.moonset")
    .data(d3.range(24))
    .enter().append("line")
    .attr("class", "moonset")
    .attr("x1", function(d, i){return - 177 * Math.sin(toRadians(moonset || 0));})
    .attr("y1", function(d, i){return + 177 * Math.cos(toRadians(moonset || 0));})
    .attr("x2", function(d, i){return - 194 * Math.sin(toRadians(moonset || 0));})
    .attr("y2", function(d, i){return + 194 * Math.cos(toRadians(moonset || 0));})
    .style("stroke-width", "2.5px")
    .style("stroke", "rgba(255,255,255,1)")
    .style("stroke-linecap", "round")
    .attr("transform", function(d, i){return "translate(0, 0)";});

Axis.selectAll("line.civil_twilight_rise")
    .data(d3.range(24))
    .enter().append("line")
    .attr("class", "civil_twilight_rise")
    .attr("x1", function(d, i){return - 177 * Math.sin(toRadians(civil_twilight_rise));})
    .attr("y1", function(d, i){return + 177 * Math.cos(toRadians(civil_twilight_rise));})
    .attr("x2", function(d, i){return - 194 * Math.sin(toRadians(civil_twilight_rise));})
    .attr("y2", function(d, i){return + 194 * Math.cos(toRadians(civil_twilight_rise));})
    .style("stroke-width", "2.5px")
    .style("stroke", "rgba(74,227,82,1)")
    .style("stroke-linecap", "round")
    .attr("transform", function(d, i){return "translate(0, 0)";});

Axis.selectAll("line.civil_twilight_set")
    .data(d3.range(24))
    .enter().append("line")
    .attr("class", "civil_twilight_set")
    .attr("x1", function(d, i){return - 177 * Math.sin(toRadians(civil_twilight_set));})
    .attr("y1", function(d, i){return + 177 * Math.cos(toRadians(civil_twilight_set));})
    .attr("x2", function(d, i){return - 194 * Math.sin(toRadians(civil_twilight_set));})
    .attr("y2", function(d, i){return + 194 * Math.cos(toRadians(civil_twilight_set));})
    .style("stroke-width", "2.5px")
    .style("stroke", "rgba(74,227,82,1)")
    .style("stroke-linecap", "round")
    .attr("transform", function(d, i){return "translate(0, 0)";});

Axis.selectAll("line.nautical_twilight_rise")
    .data(d3.range(24))
    .enter().append("line")
    .attr("class", "nautical_twilight_rise")
    .attr("x1", function(d, i){return - 177 * Math.sin(toRadians(nautical_twilight_rise));})
    .attr("y1", function(d, i){return + 177 * Math.cos(toRadians(nautical_twilight_rise));})
    .attr("x2", function(d, i){return - 194 * Math.sin(toRadians(nautical_twilight_rise));})
    .attr("y2", function(d, i){return + 194 * Math.cos(toRadians(nautical_twilight_rise));})
    .style("stroke-width", "2.5px")
    .style("stroke", "rgba(74,227,82,1)")
    .style("stroke-linecap", "round")
    .attr("transform", function(d, i){return "translate(0, 0)";});

Axis.selectAll("line.nautical_twilight_set")
    .data(d3.range(24))
    .enter().append("line")
    .attr("class", "nautical_twilight_set")
    .attr("x1", function(d, i){return - 177 * Math.sin(toRadians(nautical_twilight_set));})
    .attr("y1", function(d, i){return + 177 * Math.cos(toRadians(nautical_twilight_set));})
    .attr("x2", function(d, i){return - 194 * Math.sin(toRadians(nautical_twilight_set));})
    .attr("y2", function(d, i){return + 194 * Math.cos(toRadians(nautical_twilight_set));})
    .style("stroke-width", "2.5px")
    .style("stroke", "rgba(74,227,82,1)")
    .style("stroke-linecap", "round")
    .attr("transform", function(d, i){return "translate(0, 0)";});

Axis.selectAll("line.astro_twilight_rise")
    .data(d3.range(24))
    .enter().append("line")
    .attr("class", "astro_twilight_rise")
    .attr("x1", function(d, i){return - 177 * Math.sin(toRadians(astro_twilight_rise || 0));})
    .attr("y1", function(d, i){return + 177 * Math.cos(toRadians(astro_twilight_rise || 0));})
    .attr("x2", function(d, i){return - 194 * Math.sin(toRadians(astro_twilight_rise || 0));})
    .attr("y2", function(d, i){return + 194 * Math.cos(toRadians(astro_twilight_rise || 0));})
    .style("stroke-width", "2.5px")
    .style("stroke", "rgba(74,227,82,1)")
    .style("stroke-linecap", "round")
    .attr("transform", function(d, i){return "translate(0, 0)";});

Axis.selectAll("line.astro_twilight_set")
    .data(d3.range(24))
    .enter().append("line")
    .attr("class", "astro_twilight_set")
    .attr("x1", function(d, i){return - 177 * Math.sin(toRadians(astro_twilight_set || 0));})
    .attr("y1", function(d, i){return + 177 * Math.cos(toRadians(astro_twilight_set || 0));})
    .attr("x2", function(d, i){return - 194 * Math.sin(toRadians(astro_twilight_set || 0));})
    .attr("y2", function(d, i){return + 194 * Math.cos(toRadians(astro_twilight_set || 0));})
    .style("stroke-width", "2.5px")
    .style("stroke", "rgba(74,227,82,1)")
    .style("stroke-linecap", "round")
    .attr("transform", function(d, i){return "translate(0, 0)";});

var analogContent = svg
    .append('g')
    .attr('class','analog-content')
    .attr("transform", "translate(260,275)");

var hourScale = d3.scaleLinear()
    .range([0,360])
    .domain([0,24]);

var minuteScale = d3.scaleLinear()
    .range([0,360])
    .domain([0,60]);

var secondScale = d3.scaleLinear()
    .range([0,360])
    .domain([0,60]);

var handData = [
    {'label':'hours','scale':hourScale},
    {'label':'minutes','scale':minuteScale},
    {'label':'seconds','scale':secondScale}
];

var analogHands = analogContent.selectAll('.hands');
    analogHands.data(handData)
    .enter()
    .append('g')
    .attr('class',function(d){ return'hands analog-' + d.label;})
    .append('line')
    .attr('x1', function(d){return - 144 * Math.sin(Math.PI);})
    .attr('y1', function(d){return + 144 * Math.cos(Math.PI);})
    .attr('x2', function(d){return - 184 * Math.sin(Math.PI);})
    .attr('y2', function(d){return + 184 * Math.cos(Math.PI);});

function update() {

var now = new Date();
var yourTimeZoneFrom = <?php echo $UTC_offset?>;
var tzDifference = yourTimeZoneFrom * 60 + now.getTimezoneOffset();
var offset = tzDifference * 60 * 1000;
var now2 = new Date(new Date().getTime() + offset);

handData[0].value = (now2.getHours() % 24) + 12 + (now2.getMinutes() + now2.getSeconds() / 60) / 60;
handData[1].value = (now2.getMinutes() % 60) + now2.getSeconds() / 60;
handData[2].value = now2.getSeconds() + now2.getMilliseconds() / 1000;
d3.selectAll('.hands').data(handData) 
.attr('transform',function(d){return 'rotate('+ d.scale(d.value) +')';});   
}
setInterval(update, 1);
update();

function Visibility(V) {
  
  if (V) {
    // coefficient
    const a0 = 35 / 51;
    const a1 = - 0.3495;
    const a2 = - 0.1254;
    const a3 = - 0.0955;
    const a4 = - 0.03134;
    const a5 = - 0.01398;
    var retV =
      a0 +
      a1 * Math.cos(V) +
      a2 * Math.cos(2 * V) +
      a3 * Math.cos(3 * V) +
      a4 * Math.cos(4 * V) +
      a5 * Math.cos(5 * V);
  }
  return retV;
}

function phase(t, phaze) {
    
var T = t - 2 * Math.PI * Math.floor(t / (2 * Math.PI));

  if (T < Math.PI) {
    phaze = Math.cos(t);
  }
  return phaze;
}

var xc = 260;
var yc = 275;

var MoonRings = true;
var MoonMutatis = true;
var Kaleidoscope = true;
var SunMutatis = true;
    
var MoonMov = 8;
var Velocity = 100;

var eclipticWidth = 0.75;
var clockRadius = 140;

function EclipticRing() {

var Rd = 0.28467 * clockRadius;
var Re = 0.71533 * clockRadius;

var xe = xc + (Rd * Math.sin(hourAries));
var ye = yc - (Rd * Math.cos(hourAries));

svg.append("circle")
    .attr("stroke", "rgba(20,22,276,0.60)")    
    .style('stroke-width', 34)
    .style('fill', "none")
    .attr("r", 0.48 * (Re + eclipticWidth * Re))
    .attr("cx", xe)
    .attr("cy", ye);

svg.append("circle")
    .attr("stroke", "rgba(255,255,0,0.75)")    
    .style('stroke-width', 2.5)
    .style('fill', "none")
    .attr("r", Re)
    .attr("cx", xe)
    .attr("cy", ye);

svg.append("circle")
    .attr("stroke", "rgba(255,255,0,1)")    
    .style('stroke-width', 1.5)
    .style('fill', "none")
    .attr("r", eclipticWidth * 1.25 * Re)
    .attr("cx", xe)
    .attr("cy", ye);

svg.append("circle")
    .attr("stroke", "rgba(255,255,0,0.75)")    
    .style('stroke-width', 3.0)
    .style('fill', "none")
    .attr("r", eclipticWidth * Re * 0.9)
    .attr("cx", xe)
    .attr("cy", ye);

var Lep = Re - Re * eclipticWidth;   

let i = 0;
    while (i < 60) {

        var f = 12;
        var d = toDegRad(i,f);
        var sd = Math.sin(d);
        var a = 0.285 * clockRadius;
        var b = 2.51284;
        var Ret = a * (Math.sqrt((b * b) - (sd * sd)) + Math.cos(d));
        
        var Lep1 = Lep + 9;

        var factA = hourAries + d;
        var sinfactA = Math.sin(factA);
        var cosfactA = Math.cos(factA);
        var xa = xc + Ret * sinfactA;
        var ya = yc - Ret * cosfactA;
        var xb = xc + (Ret - Lep1) * sinfactA;
        var yb = yc - (Ret - Lep1) * cosfactA;

svg.append("line")
    .attr("x1", xa)
    .attr("x2", xb)
    .attr("y1", ya)
    .attr("y2", yb)
    .style("stroke", "rgb(255,255,0,0.75)")
    .style("stroke-width", 1.5)
    .style("stroke-linecap", "round");
                       
        i = i + 2.5;
      }     
    }
  
EclipticRing();
 
function DayMarks() {

var Rd = 0.28467 * clockRadius;
var Re = 0.71533 * clockRadius;

var xe = xc + Rd * Math.sin(hourAries);
var ye = yc - Rd * Math.cos(hourAries);

var Lep = Re - Re * eclipticWidth;
var a = 0.283 * clockRadius;
var b = 2.51284;

let i = 0;
    while (i < 60) {
        
        var f = 6;       
        var d = toDegRad(i, f);
        var sd = Math.sin(d);        
        var Ret = a * (Math.sqrt((b * b) - (sd * sd)) + Math.cos(d));
        var Lep2 = 5;

        var factA = hourAries + d;
        var sinfactA = Math.sin(factA);
        var cosfactA = Math.cos(factA);
        var xg = xc + Ret * sinfactA;
        var yg = yc - Ret * cosfactA;
        var xh = xc + (Ret - Lep2) * sinfactA;
        var yh = yc - (Ret - Lep2) * cosfactA;
    
svg.append("line")
    .attr("x1", xg)
    .attr("x2", xh)
    .attr("y1", yg)
    .attr("y2", yh)
    .style("stroke", "rgb(255,255,0,0.75)")
    .style("stroke-width", 1.0)
    .style("stroke-linecap", "round");
        
        i = i + 0.5;
      }  
    }

DayMarks();

function zodiacAries() {

var Rd = 0.28467 * clockRadius;
var Re = 0.71533 * clockRadius;

var xe = xc + Rd * Math.sin(hourAries);
var ye = yc - Rd * Math.cos(hourAries);
        
var Lep = Re - Re * eclipticWidth;
var a  = 0.275 * clockRadius;
var b = 2.51284;
                
var Array = [ "", "c", "", "", "", "", "", "", "", "", "", "", "" ];
      
let i = 1;
    while (i < 30) {

        var f = 12;          
        var d  = toDegRad(i,f);
        var sd = Math.sin(d);
        var Ret = (a * (Math.sqrt((b * b) - (sd * sd)) + Math.cos(d))) - 3.9;
                
        var factA = hourAries + d;
        var sinfactA = Math.sin(factA);
        var cosfactA = Math.cos(factA);
        var xd = xc + Ret * sinfactA;
        var yd = yc - Ret * cosfactA;
        var xf = xc + (Ret - Lep) * sinfactA;
        var yf = yc - (Ret - Lep) * cosfactA;
        var sign = Array[Math.floor(i % Array.length)];

svg.append("text")    
    .attr("x", (xd + xf) / 2)
    .attr("y", (yd + yf) / 2)
    .style("fill", "rgb(255,255,0,1)")
    .style("font-family", "AstroDotBasic")
    .style("font-size", "20px")
    .style("text-anchor", "middle")
    .style("font-weight", "bold")
    .style("dominant-baseline", "central")
    .text(sign);
          
        i = i + 2.54;
    }
}

zodiacAries();

function zodiacTaurus() {

var Rd = 0.28467 * clockRadius;
var Re = 0.71533 * clockRadius;

var xe = xc + Rd * Math.sin(hourAries);
var ye = yc - Rd * Math.cos(hourAries);
        
var Lep = Re - Re * eclipticWidth;
var a  = 0.275 * clockRadius;
var b = 2.51284;
         
var Array = [ "", "", "d", "", "", "", "", "", "", "", "", "", "" ];
      
let i = 1;
    while (i < 30) {
        
        var f = 12;           
        var d  = toDegRad(i,f);
        var sd = Math.sin(d);        
        var Ret = (a * (Math.sqrt((b * b) - (sd * sd)) + Math.cos(d))) - 3.9;
                
        var factA = hourAries + d;
        var sinfactA = Math.sin(factA);
        var cosfactA = Math.cos(factA);
        var xd = xc + Ret * sinfactA;
        var yd = yc - Ret * cosfactA;
        var xf = xc + (Ret - Lep) * sinfactA;
        var yf = yc - (Ret - Lep) * cosfactA;
        var sign = Array[Math.floor(i % Array.length)];

svg.append("text")
    .attr("x", (xd + xf) / 2)
    .attr("y", (yd + yf) / 2)
    .style("fill", "rgb(255,255,0,1)")
    .style("font-family", "AstroDotBasic")
    .style("font-size", "20px")
    .style("text-anchor", "middle")
    .style("font-weight", "bold")
    .style("dominant-baseline", "central")
    .text(sign);
        
        i = i + 2.54;
    }         
}

zodiacTaurus();

function zodiacGemini() {

var Rd = 0.28467 * clockRadius;
var Re = 0.71533 * clockRadius;

var xe = xc + Rd * Math.sin(hourAries);
var ye = yc - Rd * Math.cos(hourAries);
        
var Lep = Re - Re * eclipticWidth;
var a  = 0.275 * clockRadius;
var b = 2.51284;
         
var Array = [ "", "", "e", "", "", "", "", "", "", "", "", "" ];
      
let i = 1;
    while (i < 30) {
        
        var f = 12;           
        var d  = toDegRad(i,f);
        var sd = Math.sin(d);        
        var Ret = (a * (Math.sqrt((b * b) - (sd * sd)) + Math.cos(d))) -3.9;
                
        var factA = hourAries + d;
        var sinfactA = Math.sin(factA);
        var cosfactA = Math.cos(factA);
        var xd = xc + Ret * sinfactA;
        var yd = yc - Ret * cosfactA;
        var xf = xc + (Ret - Lep) * sinfactA;
        var yf = yc - (Ret - Lep) * cosfactA;
        var sign = Array[Math.floor(i % Array.length)];

svg.append("text")
    .attr("x", (xd + xf) / 2)
    .attr("y", (yd + yf) / 2)
    .style("fill", "rgb(255,255,0,1)")
    .style("font-family", "AstroDotBasic")
    .style("font-size", "20px")
    .style("text-anchor", "middle")
    .style("font-weight", "bold")
    .style("dominant-baseline", "central")
    .text(sign);
        
        i = i + 2.54;
    }         
}

zodiacGemini();

function zodiacCancer() {

var Rd = 0.28467 * clockRadius;
var Re = 0.71533 * clockRadius;

var xe = xc + Rd * Math.sin(hourAries);
var ye = yc - Rd * Math.cos(hourAries);
        
var Lep = Re - Re * eclipticWidth;
var a  = 0.275 * clockRadius;
var b = 2.51284;
         
var Array = [ "", "", "", "b", "", "", "", "", "", "", "", "" ];
      
let i = 1;
    while (i < 30) {
        
        var f = 12;           
        var d  = toDegRad(i,f);
        var sd = Math.sin(d);        
        var Ret = (a * (Math.sqrt((b * b) - (sd * sd)) + Math.cos(d))) - 3.9;
                
        var factA = hourAries + d;
        var sinfactA = Math.sin(factA);
        var cosfactA = Math.cos(factA);
        var xd = xc + Ret * sinfactA;
        var yd = yc - Ret * cosfactA;
        var xf = xc + (Ret - Lep) * sinfactA;
        var yf = yc - (Ret - Lep) * cosfactA;
        var sign = Array[Math.floor(i % Array.length)];

svg.append("text")
    .attr("x", (xd + xf) / 2)
    .attr("y", (yd + yf) / 2)
    .style("fill", "rgb(255,255,0,1)")
    .style("font-family", "AstroDotBasic")
    .style("font-size", "20px")
    .style("text-anchor", "middle")
    .style("font-weight", "bold")
    .style("dominant-baseline", "central")
    .text(sign);
        
        i = i + 2.54;
    }        
}

zodiacCancer();

function zodiacLeo() {

var Rd = 0.28467 * clockRadius;
var Re = 0.71533 * clockRadius;

var xe = xc + Rd * Math.sin(hourAries);
var ye = yc - Rd * Math.cos(hourAries);
        
var Lep = Re - Re * eclipticWidth;
var a  = 0.275 * clockRadius;
var b = 2.51284;
         
var Array = [ "", "", "", "", "", "", "a", "", "", "", "", "", "" ];
      
let i = 1;
    while (i < 30) {
        
        var f = 12;           
        var d  = toDegRad(i,f);
        var sd = Math.sin(d);        
        var Ret = (a * (Math.sqrt((b * b) - (sd * sd)) + Math.cos(d))) - 3.9;
                
        var factA = hourAries + d;
        var sinfactA = Math.sin(factA);
        var cosfactA = Math.cos(factA);
        var xd = xc + Ret * sinfactA;
        var yd = yc - Ret * cosfactA;
        var xf = xc + (Ret - Lep) * sinfactA;
        var yf = yc - (Ret - Lep) * cosfactA;
        var sign = Array[Math.floor(i % Array.length)];

svg.append("text")
    .attr("x", (xd + xf) / 2)
    .attr("y", (yd + yf) / 2)
    .style("fill", "rgb(255,255,0,1)")
    .style("font-family", "AstroDotBasic")
    .style("font-size", "20px")
    .style("text-anchor", "middle")
    .style("font-weight", "bold")
    .style("dominant-baseline", "central")
    .text(sign);
        
        i = i + 2.54;
    }        
}

zodiacLeo();

function zodiacVirgo() {

var Rd = 0.28467 * clockRadius;
var Re = 0.71533 * clockRadius;

var xe = xc + Rd * Math.sin(hourAries);
var ye = yc - Rd * Math.cos(hourAries);
        
var Lep = Re - Re * eclipticWidth;
var a  = 0.275 * clockRadius;
var b = 2.51284;
         
var Array = [ "", "", "", "", "", "", "", "", "l", "", "", "" ];
      
let i = 1;
    while (i < 30) {
        
        var f = 12;           
        var d  = toDegRad(i,f);
        var sd = Math.sin(d);        
        var Ret = (a * (Math.sqrt((b * b) - (sd * sd)) + Math.cos(d))) - 3.9;
                
        var factA = hourAries + d;
        var sinfactA = Math.sin(factA);
        var cosfactA = Math.cos(factA);
        var xd = xc + Ret * sinfactA;
        var yd = yc - Ret * cosfactA;
        var xf = xc + (Ret - Lep) * sinfactA;
        var yf = yc - (Ret - Lep) * cosfactA;
        var sign = Array[Math.floor(i % Array.length)];

svg.append("text")
    .attr("x", (xd + xf) / 2)
    .attr("y", (yd + yf) / 2)
    .style("fill", "rgb(255,255,0,1)")
    .style("font-family", "AstroDotBasic")
    .style("font-size", "20px")
    .style("text-anchor", "middle")
    .style("font-weight", "bold")
    .style("dominant-baseline", "central")
    .text(sign);
        
        i = i + 2.54;
    }         
}

zodiacVirgo();

function zodiacLibra() {

var Rd = 0.28467 * clockRadius;
var Re = 0.71533 * clockRadius;

var xe = xc + Rd * Math.sin(hourAries);
var ye = yc - Rd * Math.cos(hourAries);
        
var Lep = Re - Re * eclipticWidth;
var a  = 0.275 * clockRadius;
var b = 2.51284;
         
var Array = [ "", "", "", "", "", "", "", "", "", "", "", "k", "" ];
      
let i = 1;
    while (i < 30) {
        
        var f = 12;           
        var d  = toDegRad(i,f);
        var sd = Math.sin(d);        
        var Ret = (a * (Math.sqrt((b * b) - (sd * sd)) + Math.cos(d))) - 3.9;
                
        var factA = hourAries + d;
        var sinfactA = Math.sin(factA);
        var cosfactA = Math.cos(factA);
        var xd = xc + Ret * sinfactA;
        var yd = yc - Ret * cosfactA;
        var xf = xc + (Ret - Lep) * sinfactA;
        var yf = yc - (Ret - Lep) * cosfactA;
        var sign = Array[Math.floor(i % Array.length)];

svg.append("text")
    .attr("x", (xd + xf) / 2)
    .attr("y", (yd + yf) / 2)
    .style("fill", "rgb(255,255,0,1)")
    .style("font-family", "AstroDotBasic")
    .style("font-size", "20px")
    .style("text-anchor", "middle")
    .style("font-weight", "bold")
    .style("dominant-baseline", "central")
    .text(sign);
        
        i = i + 2.54;
    }        
}

zodiacLibra();

function zodiacScorpio() {

var Rd = 0.28467 * clockRadius;
var Re = 0.71533 * clockRadius;

var xe = xc + Rd * Math.sin(hourAries);
var ye = yc - Rd * Math.cos(hourAries);
        
var Lep = Re - Re * eclipticWidth;
var a  = 0.275 * clockRadius;
var b = 2.51284;
         
var Array = [ "", "", "", "", "", "", "", "", "", "", "f", "", "" ];
      
let i = 1;
    while (i < 30) {
        
        var f = 12;           
        var d  = toDegRad(i,f);
        var sd = Math.sin(d);        
        var Ret = (a * (Math.sqrt((b * b) - (sd * sd)) + Math.cos(d))) - 3.9;
                
        var factA = hourAries + d;
        var sinfactA = Math.sin(factA);
        var cosfactA = Math.cos(factA);
        var xd = xc + Ret * sinfactA;
        var yd = yc - Ret * cosfactA;
        var xf = xc + (Ret - Lep) * sinfactA;
        var yf = yc - (Ret - Lep) * cosfactA;
        var sign = Array[Math.floor(i % Array.length)];

svg.append("text")
    .attr("x", (xd + xf) / 2)
    .attr("y", (yd + yf) / 2)
    .style("fill", "rgb(255,255,0,1)")
    .style("font-family", "AstroDotBasic")
    .style("font-size", "20px")
    .style("text-anchor", "middle")
    .style("font-weight", "bold")
    .style("dominant-baseline", "central")
    .text(sign);
        
        i = i + 2.54;
    }        
}

zodiacScorpio();

function zodiacSagittarius() {

var Rd = 0.28467 * clockRadius;
var Re = 0.71533 * clockRadius;

var xe = xc + Rd * Math.sin(hourAries);
var ye = yc - Rd * Math.cos(hourAries);
        
var Lep = Re - Re * eclipticWidth;
var a  = 0.275 * clockRadius;
var b = 2.51284;
         
var Array = [ "", "", "", "", "", "", "", "", "", "", "g" ];
      
let i = 1;
    while (i < 30) {
        
        var f = 12;           
        var d  = toDegRad(i,f);
        var sd = Math.sin(d);        
        var Ret = (a * (Math.sqrt((b * b) - (sd * sd)) + Math.cos(d))) - 3.9;
                
        var factA = hourAries + d;
        var sinfactA = Math.sin(factA);
        var cosfactA = Math.cos(factA);
        var xd = xc + Ret * sinfactA;
        var yd = yc - Ret * cosfactA;
        var xf = xc + (Ret - Lep) * sinfactA;
        var yf = yc - (Ret - Lep) * cosfactA;
        var sign = Array[Math.floor(i % Array.length)];

svg.append("text")
    .attr("x", (xd + xf) / 2)
    .attr("y", (yd + yf) / 2)
    .style("fill", "rgb(255,255,0,1)")
    .style("font-family", "AstroDotBasic")
    .style("font-size", "20px")
    .style("text-anchor", "middle")
    .style("font-weight", "bold")
    .style("dominant-baseline", "central")
    .text(sign);
        
        i = i + 2.54;
    }         
}

zodiacSagittarius();

function zodiacCapricorn() {

var Rd = 0.28467 * clockRadius;
var Re = 0.71533 * clockRadius;

var xe = xc + Rd * Math.sin(hourAries);
var ye = yc - Rd * Math.cos(hourAries);
        
var Lep = Re - Re * eclipticWidth;
var a  = 0.275 * clockRadius;
var b = 2.51284;
         
var Array = [ "", "", "", "", "h", "", "", "", "", "", "", "", "", "" ];
      
let i = 1;
    while (i < 30) {
        
        var f = 12;          
        var d  = toDegRad(i,f);
        var sd = Math.sin(d);        
        var Ret = (a * (Math.sqrt((b * b) - (sd * sd)) + Math.cos(d))) - 3.9;
                
        var factA = hourAries + d;
        var sinfactA = Math.sin(factA);
        var cosfactA = Math.cos(factA);
        var xd = xc + Ret * sinfactA;
        var yd = yc - Ret * cosfactA;
        var xf = xc + (Ret - Lep) * sinfactA;
        var yf = yc - (Ret - Lep) * cosfactA;
        var sign = Array[Math.floor(i % Array.length)];

svg.append("text")
    .attr("x", (xd + xf) / 2)
    .attr("y", (yd + yf) / 2)
    .style("fill", "rgb(255,255,0,1)")
    .style("font-family", "AstroDotBasic")
    .style("font-size", "20px")
    .style("text-anchor", "middle")
    .style("font-weight", "bold")
    .style("dominant-baseline", "central")
    .text(sign);
        
        i = i + 2.54;
    }         
}

zodiacCapricorn();

function zodiacAquarius() {

var Rd = 0.28467 * clockRadius;
var Re = 0.71533 * clockRadius;

var xe = xc + Rd * Math.sin(hourAries);
var ye = yc - Rd * Math.cos(hourAries);
        
var Lep = Re - Re * eclipticWidth;
var a  = 0.275 * clockRadius;
var b = 2.51284;
         
var Array = [ "", "", "i", "", "", "", "", "", "", "", "", "", "", "" ];
      
let i = 1;
    while (i < 30) {
        
        var f = 12;           
        var d  = toDegRad(i,f);
        var sd = Math.sin(d);        
        var Ret = (a * (Math.sqrt((b * b) - (sd * sd)) + Math.cos(d))) - 3.9;
                
        var factA = hourAries + d;
        var sinfactA = Math.sin(factA);
        var cosfactA = Math.cos(factA);
        var xd = xc + Ret * sinfactA;
        var yd = yc - Ret * cosfactA;
        var xf = xc + (Ret - Lep) * sinfactA;
        var yf = yc - (Ret - Lep) * cosfactA;
        var sign = Array[Math.floor(i % Array.length)];

svg.append("text")
    .attr("x", (xd + xf) / 2)
    .attr("y", (yd + yf) / 2)
    .style("fill", "rgb(255,255,0,1)")
    .style("font-family", "AstroDotBasic")
    .style("font-size", "20px")
    .style("text-anchor", "middle")
    .style("font-weight", "bold")
    .style("dominant-baseline", "central")
    .text(sign);
        
        i = i + 2.54;
    }        
}

zodiacAquarius();

function zodiacPisces() {

var Rd = 0.28467 * clockRadius;
var Re = 0.71533 * clockRadius;

var xe = xc + Rd * Math.sin(hourAries);
var ye = yc - Rd * Math.cos(hourAries);
        
var Lep = Re - Re * eclipticWidth;
var a  = 0.275 * clockRadius;
var b = 2.51284;

var Array = [ "", "", "", "", "", "", "", "", "", "", "", "", "", "j" ];
      
let i = 1;
    while (i < 30) {
        
        var f = 12;           
        var d  = toDegRad(i,f);
        var sd = Math.sin(d);        
        var Ret = (a * (Math.sqrt((b * b) - (sd * sd)) + Math.cos(d))) - 3.9;
                
        var factA = hourAries + d;
        var sinfactA = Math.sin(factA);
        var cosfactA = Math.cos(factA);
        var xd = xc + Ret * sinfactA;
        var yd = yc - Ret * cosfactA;
        var xf = xc + (Ret - Lep) * sinfactA;
        var yf = yc - (Ret - Lep) * cosfactA;
        var sign = Array[Math.floor(i % Array.length)];

svg.append("text")
    .attr("x", (xd + xf) / 2)
    .attr("y", (yd + yf) / 2)
    .style("fill", "rgb(255,255,0,1)")
    .style("font-family", "AstroDotBasic")
    .style("font-size", "20px")
    .style("text-anchor", "middle")
    .style("font-weight", "bold")
    .style("dominant-baseline", "central")
    .text(sign);
        
        i = i + 2.54;
    }        
}

zodiacPisces();

function AriesHand() {

var ariesHourFactor = hourAries + Math.PI / 2;
var sinAriesHand = Math.sin(ariesHourFactor) * clockRadius;
var cosAriesHand = Math.cos(ariesHourFactor) * clockRadius;
var xa = xc + 0.87 * sinAriesHand;
var ya = yc - 0.87 * cosAriesHand;
var Xa = xc - 0.654 * sinAriesHand;
var Ya = yc + 0.654 * cosAriesHand;

svg.append("line")
    .attr("x1", xa)
    .attr("x2", Xa)
    .attr("y1", ya)
    .attr("y2", Ya)
    .style("stroke", "rgba(0,127,255,1)")
    .style("stroke-width", 3.0)
    .style("stroke-linecap", "round");

svg.append("circle")
    .style('fill', "rgba(255,99,71,1)")
    .attr("r", 4)
    .attr("cx", Xa)
    .attr("cy", Ya);

svg.append("circle")
    .style('fill', "rgba(255,99,71,1)")
    .attr("r", 4.5)
    .attr("cx", xa)
    .attr("cy", ya);

ariesHourFactor = hourAries - 0.011 + Math.PI / 270;
sinAriesHand = Math.sin(ariesHourFactor) * clockRadius;
cosAriesHand = Math.cos(ariesHourFactor) * clockRadius;
xa = xc + sinAriesHand;
ya = yc - cosAriesHand;
Xa = xc - 0.43 * sinAriesHand;
Ya = yc + 0.43 * cosAriesHand;

svg.append("line")
    .attr("x1", xa)
    .attr("x2", Xa)
    .attr("y1", ya)
    .attr("y2", Ya)
    .style("stroke", "rgba(0,127,255,1)")
    .style("stroke-width", 3.0)
    .style("stroke-linecap", "round");

svg.append("circle")
    .style('fill', "rgba(255,99,71,1)")
    .attr("r", 4)
    .attr("cx", Xa)
    .attr("cy", Ya); 
    
svg.append("circle")
    .style('fill', "rgba(255,99,71,1)")
    .attr("r", 4)
    .attr("cx", xa)
    .attr("cy", ya);  
}

AriesHand();

function SunNeedle() {

var as = 0.28467 * clockRadius;
var bs = 2.51284;
var ds = - hourAries + hours_arc;
var sds = Math.sin(ds);
var Rs = as * (Math.sqrt(bs * bs - sds * sds) + Math.cos(ds));
            
var Xs = xc - 0.17 * clockRadius * Math.sin(hours_arc);
var Ys = yc + 0.17 * clockRadius * Math.cos(hours_arc);

svg.append("line")
    .attr("x1", xc)
    .attr("x2", Xs)
    .attr("y1", yc)
    .attr("y2", Ys)
    .style("stroke", "rgba(255,255,0,1)")
    .style("stroke-width", 3.0)
    .style("stroke-linecap", "round");

svg.append("circle")
    .style('fill', "rgba(255,255,0,1)")
    .attr("r", 3)
    .attr("cx", Xs)
    .attr("cy", Ys);

Xs = xc + 1.31 * clockRadius * Math.sin(hours_arc);
Ys = yc - 1.31 * clockRadius * Math.cos(hours_arc);

svg.append("line")
    .attr("x1", xc)
    .attr("x2", Xs)
    .attr("y1", yc)
    .attr("y2", Ys)
    .style("stroke", "rgba(255,255,0,1)")
    .style("stroke-width", 3.0)
    .style("stroke-linecap", "round");

Xs = xc + Rs * Math.sin(hours_arc);
Ys = yc - Rs * Math.cos(hours_arc);

var sunRed = svg.append("defs");

var sRing = sunRed.append("radialGradient")
    .attr("id","sunsetRed");

sRing.append('stop')
    .attr('stop-color', "rgba(255, 85, 85, 1.0)")
    .attr('offset', '0');

sRing.append('stop')
    .attr('stop-color', "rgba(255, 85, 85, 0.5)")
    .attr('offset', '0.5');

sRing.append('stop')
    .attr('stop-color', "rgba(255, 85, 85, 0)")
    .attr('offset', '1');

svg.append("circle")
    .style('fill', "url(#sunsetRed)")
    .attr("r", 38)
    .attr("cx", Xs)
    .attr("cy", Ys);

var Alpha_S;

if (SunMutatis == true) {

    Alpha_S = Visibility(hours_arc + Math.PI);

    } else {

    Alpha_S = 1;
}
    
var opacity = Alpha_S;
      
if (Kaleidoscope == true) {
   
var RmS = 7 * (1 - Alpha_S);

svg.append("circle")
    .attr("stroke", "rgba(255,255,0,0.80)")
    .attr("stroke-width", 2)    
    .style('fill', "none")
    .attr("r", 30)
    .attr("cx", Xs)
    .attr("cy", Ys);

svg.append("circle")
    .attr("stroke", "rgba(255,255,0,0.80)")
    .attr("stroke-width", 2)    
    .style('fill', "none")
    .attr("r", 22 + RmS)
    .attr("cx", Xs)
    .attr("cy", Ys);

svg.append("circle")
    .attr("stroke", "rgba(255,255,0,0.20)")
    .attr("stroke-width", 30 - 22 - RmS)    
    .style('fill', "none")
    .attr("r", 0.5 * (30 + 22 + RmS))
    .attr("cx", Xs)
    .attr("cy", Ys);

var kaleidoscope = svg
    .append('g')
    .attr('class','sun-center')
    .attr("transform", "translate(0,0)");

var Axis_k = kaleidoscope
    .append('g')
    .attr('class','Axis_k');

function update_sun() {
var sun_tiktok = new Date();
var sun_mins = sun_tiktok.getMinutes();
var sun_secs = sun_tiktok.getSeconds();   
var K_Sun = 2 * Math.PI * (sun_mins + sun_secs / 60) / 60;

d3.selectAll("line.sunscope").remove();

Axis_k.selectAll("line.sunscope")
    .data(d3.range(12))
    .enter().append("line")
    .attr("class", "sunscope")
    .attr("x1", function(d, i){return Xs + 30 * Math.sin(hourAries + i * Math.PI / 6 - K_Sun);})
    .attr("y1", function(d, i){return Ys - 30 * Math.cos(hourAries + i * Math.PI / 6 - K_Sun);})
    .attr("x2", function(d, i){return Xs + (22 + RmS) * Math.sin(hourAries + i * Math.PI / 6 + Velocity * K_Sun);})
    .attr("y2", function(d, i){return Ys - (22 + RmS) * Math.cos(hourAries + i * Math.PI / 6 + Velocity * K_Sun);})
    .style("stroke-width", "1.5")
    .style("stroke", "rgba(255,255,0," + opacity + ")")
    .style("stroke-linecap", "round")
    .attr("transform", function(d, i){return "translate(0, 0)";});
    }
    setInterval(update_sun, 1000);
    update_sun();
}

svg.append("circle")  
    .style('fill', "rgba(255,255,0," + opacity + ")")
    .attr("r", 10.55)
    .attr("cx", Xs)
    .attr("cy", Ys);

svg.append("circle")   
    .style('fill', "rgba(255,255,0,1)")
    .attr("r", 3.25)
    .attr("cx", Xs)
    .attr("cy", Ys);
}  

SunNeedle();

function MoonNeedle() {
  
var hour_MD = toRadians(hourMoon);

var am = 0.28467 * clockRadius;
var bm = 2.51284;
var dm = hourAries - hour_MD;
var mdm = Math.sin(dm);
var Rm = am * (Math.sqrt(bm * bm - mdm * mdm) + Math.cos(dm));
        
var Xm = xc - 0.17 * clockRadius * Math.sin(hour_MD);
var Ym = yc + 0.17 * clockRadius * Math.cos(hour_MD);

svg.append("line")
    .attr("x1", xc)
    .attr("x2", Xm)
    .attr("y1", yc)
    .attr("y2", Ym)
    .style("stroke", "rgba(255,0,0,1)")
    .style("stroke-width", 3.0)
    .style("stroke-linecap", "round");

svg.append("circle")
    .style('fill', "rgba(255,0,0,1)")
    .attr("r", 3)
    .attr("cx", Xm)
    .attr("cy", Ym);

Xm = xc + 1.31 * clockRadius * Math.sin(hour_MD);
Ym = yc - 1.31 * clockRadius * Math.cos(hour_MD);

svg.append("line")
    .attr("x1", xc)
    .attr("x2", Xm)
    .attr("y1", yc)
    .attr("y2", Ym)
    .style("stroke", "rgba(255,0,0,1)")
    .style("stroke-width", 3.0)
    .style("stroke-linecap", "round");

Xm = xc + Rm * Math.sin(hour_MD);
Ym = yc - Rm * Math.cos(hour_MD);
  
var Alpha_M, color_M; 

if (MoonMutatis == true) {

    Alpha_M = 1;
    color_M = "rgba(41,46,53," + Alpha_M + ")";

svg.append("circle")
    .style('fill', color_M)
    .attr("r", 10.55)
    .attr("cx", Xm)
    .attr("cy", Ym);

    } else {

    var V = hours_arc - hour_MD;
    Alpha_M = Visibility(V);
    color_M = "rgba(255,255,255," + Alpha_M + ")";

svg.append("circle")
    .style('fill', color_M)
    .attr("r", 10.55)
    .attr("cx", Xm)
    .attr("cy", Ym);
}

if (MoonMutatis == true) {
  
var Rmo = 10.55;
var temp = - hour_MD + hours_arc;

var amp_Right = Rmo * phase(temp + Math.PI, 1);
var amp_Left = Rmo * phase(temp, - 1);

var Rot = hour_MD + Math.PI;

var Xf = Xm + Math.sin(Rot) * Rmo;
var Yf = Ym - Math.cos(Rot) * Rmo;

var path = d3.path();
path.moveTo(Xf, Yf);
   
let i = 0; 

    var Rx, Ry, Xg, Yg;

    while (i < 2 * Math.PI) {

    if (i <= Math.PI) {

    Rx = Math.sin(i) * amp_Right;

    } else {

    Rx = - Math.sin(i) * amp_Left;
}

    Ry = Math.cos(i) * Rmo;

    Xg = Xm - Rx * Math.cos(Rot) + Ry * Math.sin(Rot);
    Yg = Ym - Rx * Math.sin(Rot) - Ry * Math.cos(Rot);

path.lineTo(Xg, Yg);

        i = i + Math.PI / 64;
    }

path.closePath();

svg.append("path")
    .attr("d", path)
    .style('fill', "rgba(255,255,255,1.0)");
}

var D;

if (MoonMutatis == true) {

    D = 0.5 + 0.5 * Visibility(hours_arc);

    } else {

    D = 1;      
}
    
if (MoonRings == true) {
     
var RmL = MoonMov * Alpha_M;
var RmB = 12;
var Rms = 12 - 7 * D;

svg.append("circle")
    .attr("stroke", "rgba(255,255,255,0.1)")    
    .style('stroke-width', RmB - Rms)
    .style('fill', "none")
    .attr("r", 0.5 * (RmB + Rms) + RmL)
    .attr("cx", Xm)
    .attr("cy", Ym);

svg.append("circle")
    .attr("stroke", "rgba(255,255,255,1.0)")    
    .style('stroke-width', 1.5)
    .style('fill', "none")
    .attr("r", RmB + RmL)
    .attr("cx", Xm)
    .attr("cy", Ym);

svg.append("circle")
    .attr("stroke", "rgba(255,255,255,1.0)")    
    .style('stroke-width', 1.5)
    .style('fill', "none")
    .attr("r", Rms + RmL)
    .attr("cx", Xm)
    .attr("cy", Ym);

var moonscope = svg
    .append('g')
    .attr('class','moon-center')
    .attr("transform", "translate(0,0)");

var Axis_M = moonscope
    .append('g')
    .attr('class','Axis_M');

function update_moon() {
var moon_tiktok = new Date();
var moon_mins = moon_tiktok.getMinutes();
var moon_secs = moon_tiktok.getSeconds();   
var R_Moon = 2 * Math.PI * (moon_mins + moon_secs / 60) / 60;

d3.selectAll("line.moonscope").remove();

Axis_M.selectAll("line.moonscope")
    .data(d3.range(12))
    .enter().append("line")
    .attr("class", "moonscope")
    .attr("x1", function(d, i){return Xm + (Rms + RmL) * Math.sin(hourAries + i * Math.PI / 6 - 2 * Velocity * R_Moon);})
    .attr("y1", function(d, i){return Ym - (Rms + RmL) * Math.cos(hourAries + i * Math.PI / 6 - 2 * Velocity * R_Moon);})
    .attr("x2", function(d, i){return Xm + (RmB + RmL) * Math.sin(hourAries + i * Math.PI / 6 - 2 * Velocity * R_Moon);})
    .attr("y2", function(d, i){return Ym - (RmB + RmL) * Math.cos(hourAries + i * Math.PI / 6 - 2 * Velocity * R_Moon);})
    .style("stroke-width", "1.5")
    .style("stroke", "rgba(255,255,255,1.0)")
    .style("stroke-linecap", "round")
    .attr("transform", function(d, i){return "translate(0, 0)";});
    }
    setInterval(update_moon, 1000);
    update_moon();
  }
}

MoonNeedle();

svg.append("circle")
    .style('fill', "rgba(41,46,53,1)")
    .attr("r", 12)
    .attr("cx", xc)
    .attr("cy", yc);

svg.append("circle")
    .attr("stroke", "rgba(0,127,255,1)")    
    .style('stroke-width', 3)
    .style('fill', "none")
    .attr("r", 12)
    .attr("cx", xc)
    .attr("cy", yc);

svg.append("circle")
    .style('fill', "rgba(255,99,71,1)")
    .attr("r", 4)
    .attr("cx", xc)
    .attr("cy", yc);

</script>
</body>
</html>