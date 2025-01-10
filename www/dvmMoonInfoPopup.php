<!DOCTYPE html>
<html lang="en">
<?php 
include('dvmCombinedData.php');
if ($theme === "dark") {
    echo '<style>@font-face{font-family:weathertext;src:url(css/fonts/verbatim-regular.woff)format("woff"),url(fonts/verbatim-regular.woff2)format("woff2"),url(fonts/verbatim-regular.ttf)format("truetype");}html,body{font-size:13px;font-family:"weathertext",Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;}.grid{display:grid;
  grid-template-columns:repeat(auto-fill,minmax(200px,2fr));grid-gap:5px;align-items:stretch;color:#f5f7fc;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;}.grid > article{
border:1px solid rgba(245,247,252,0.02);box-shadow:2px 2px 6px 0px rgba(0,0,0,0.6);padding:5px;font-size:0.8em;-webkit-border-radius:4px;border-radius:4px;background:0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing:grayscale;}.grid > article img{max-width:100%;}.moony{margin-top:-15px;margin-left:-1px;}.divumwxdarkbrowser{position:relative;background:0;width:97%;height:30px;margin: auto;margin-top:-5px;margin-left:0px;border-top-left-radius:5px;border-top-right-radius:5px;padding-top:10px;}.divumwxdarkbrowser[url]:after{content:attr(url);color:silver;font-size:14px;text-align: center;position:absolute;left:0;right:0;top:0;padding:4px 15px;margin:11px 10px 0 auto;font-family:arial;height:20px;}blue{color:#01a4b4;}orange{color:#009bb4;}orange1{position:relative;color:#009bb4;margin: 0 auto;text-align:center;margin-left:5%;font-size:1.1rem;}green{color:#aaa;}red{color:#f37867;}red6{color:#d65b4a;}value{color:#fff;}yellow{color:#cc0;}purple{color:#916392;}meteotextshowertext{font-size: 1.2rem;color:#009bb4;}meteorsvgicon{color:#f5f7fc;}.moonphasesvg{align-items:right;justify-content:center;display:flex;max-height:120px;}.moonphasetext{font-size:0.8rem;color:#f5f7fc;position: absolute;display:inline;left:125px;top:100px;}moonphaseriseset{font-size:0.75rem;}credit{position:relative;font-size:0.7em;top:10%;}.actualt{position:relative;left:5px;-webkit-border-radius:3px;-moz-border-radius:3px;-o-border-radius:3px;border-radius:3px;background:teal;padding:5px;font-family:Arial,Helvetica,sans-serif;width:100px;height:0.8em;font-size:0.8rem;padding-top:2px;color:white;align-items:center;justify-content: center;margin-bottom:10px;top:0;}.actualw{position:relative;left:5px;-webkit-border-radius:3px;-moz-border-radius:3px;-o-border-radius:3px;border-radius:3px;background:rgba(74,99,111,0.1);padding:5px;font-family:Arial,Helvetica,sans-serif;width:100px;height:0.8em;font-size:0.8rem;padding-top:2px;color:#aaa;align-items:center;justify-content:center;margin-bottom:10px;top:0;}
</style>';
} elseif ($theme === "light") {
    echo '<style>@font-face {font-family:weathertext;src:url(css/fonts/verbatim-regular.woff)format("woff"),url(fonts/verbatim-regular.woff2)format("woff2"),url(fonts/verbatim-regular.ttf) format("truetype");
}html,body{font-size:13px;font-family:"weathertext",Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;background-color:white;}.grid{display:grid;
  grid-template-columns:repeat(auto-fill,minmax(200px,2fr));grid-gap:5px;align-items:stretch;color:black;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;}.grid > article{
  border:1px solid rgba(245,247,252,0.02);box-shadow:2px 2px 6px 0px rgba(0,0,0,0.6);padding:5px;font-size:0.8em;-webkit-border-radius:4px;border-radius:4px;background:0;-webkit-font-smoothing:antialiased;
  -moz-osx-font-smoothing:grayscale;}.grid > article img{max-width:100%;}.moony{margin-top:-15px;margin-left:-1px;}.divumwxdarkbrowser{position:relative;background:0;width:97%;height:30px;margin:auto;
margin-top:-5px;margin-left:0px;border-top-left-radius:5px;border-top-right-radius:5px;padding-top:10px;}.divumwxdarkbrowser[url]:after{content:attr(url);color:black;font-size:14px;text-align:center;
position:absolute;left:0;right:0;top:0;padding:4px 15px;margin:11px 10px 0 auto;font-family:arial;height:20px;}blue{color:#01a4b4;}orange{color:#009bb4;}orange1{position:relative;color:#009bb4;margin:0 auto;
text-align:center;margin-left:5%;font-size:1.1rem;}green{color:#aaa;}red{color:#f37867;}red6{color:#d65b4a;}value{color:#fff;}yellow{color:#cc0;}purple{color:#916392;}meteotextshowertext{font-size:1.2rem;
color:#009bb4;}meteorsvgicon{color:#f5f7fc;}.moonphasesvg{align-items:right;justify-content:center;display:flex;max-height:120px;}.moonphasetext{font-size:0.8rem;color:black;position:absolute;display:inline;
left:125px;top:100px;}moonphaseriseset{font-size:0.75rem;}credit{position:relative;font-size:0.7em;top:10%;}.actualt{position:relative;left:5px;-webkit-border-radius:3px;-moz-border-radius:3px;
-o-border-radius:3px;border-radius:3px;background:teal;padding:5px;font-family:Arial,Helvetica,sans-serif;width:100px;height:0.8em;font-size:0.8rem;padding-top:2px;color:white;align-items:center;
justify-content:center;margin-bottom:10px;top:0;}.actualw{position:relative;left:5px;-webkit-border-radius:3px;-moz-border-radius:3px;-o-border-radius:3px;border-radius:3px;background:rgba(74,99,111,0.1);
padding:5px;font-family:Arial,Helvetica,sans-serif;width:100px;height:0.8em;font-size:0.8rem;padding-top:2px;color:#aaa;align-items:center;justify-content:center;margin-bottom:10px;top:0;}
</style>';
}
?>
<head>
  <meta charset="UTF-8">
  <title>Moon Phase Information</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

<div class="divumwxdarkbrowser" url="Moon Phase Information"></div> 
<main class="grid">
<article>
       
<?php echo $info;?> Current Moon Phase<br><br>  

<div id="divumwxmoonphases" class="moonphasesvg"></div>

<script src='js/d3.7.9.0.min.js'></script>
<style>
    .moony {
        margin-top: 0px;
        margin-left: 71px;
    }
</style>
<div class="moony">
<div class="moonphaze"></div>
</div>

<script>
 var theme = "<?php echo $theme;?>";
    if (theme === 'dark') {
    var baseTextColor = "silver";
    } else {
    baseTextColor = "#2d3a4b";
    }
      
function toDegrees(x) {
  return x * (180.0 / Math.PI);
}

function toRadians(x) {
  return x * (Math.PI / 180.0);
}

function constrain(x) {
    let t = x % 360;
    if(t < 0) {
      t += 360;
    }
  return t;  
}

    var dx = new Date();

    // latitude and longitude from weewx dvmCombinedData.php
    var latitude = <?php echo $lat;?>;
    var longitude = <?php echo $lon;?>;
  
    // Establish current time
    var dayCurrent = dx.getUTCDate();
    var monthCurrent = dx.getUTCMonth() + 1;
    var yearCurrent = dx.getUTCFullYear();
    var hourCurrent = dx.getUTCHours();
    var minuteCurrent = dx.getUTCMinutes();
    var secondCurrent = dx.getUTCSeconds();
    var millisecondCurrent = dx.getUTCMilliseconds();   
    var timezoneOffset = dx.getTimezoneOffset() / 60;
    var UTCString = dx.toUTCString();
    
    // Julian date
    var dayJulian = dayCurrent + ((hourCurrent * 3600 + minuteCurrent * 60 + secondCurrent + millisecondCurrent / 1000) / 86400);
    
    if (monthCurrent <= 2) {
      var monthJulian = monthCurrent + 12;
      var yearJulian = yearCurrent - 1;
    }
    else {
      var monthJulian = monthCurrent;
      var yearJulian = yearCurrent;
    }
    
    // Assuming the date is after the start of the Gregorian calendar (15/10/1582)
    var valueA = Math.floor(yearJulian / 100);
    var valueB = 2 - valueA + Math.floor(valueA / 4);
    
    var valueC = Math.floor(365.25 * yearJulian);
    var valueD = Math.floor(30.6001 * (monthJulian + 1));
    // JD
    var JD = valueB + valueC + valueD + dayJulian + 1720994.5;

    var T = (JD - 2451545) / 36525.0;

// Establish current Moon Distance using ELP82 Table class
// Establish current Sun Distance using ELP82 Table class
// Establish current Moon Illumination in % using ELP82 Table class
// The moon's latitude and longitude is also calculated but not needed for final output
Lat = latitude;
Lon = longitude; 
var ELP82 = class ELP82 {
    // Chapront ELP2000/82 truncated implementation from Meeus
    static elp82(T) {
        // Sun's mean longitude
        const Lp = ELP82.toRadians(constrain(218.3164477 + 481267.88123421 * T 
          - 0.0015786 * T * T + 1.0 / 538841.0 * T * T * T 
          - 1.0 / 65194000.0 * T * T * T * T));
        // Mean elongation of the Moon
        const D = ELP82.toRadians(constrain(297.8501921 + 445267.1114034 * T 
          - 0.0018819 * T * T + 1.0 / 545868.0 * T * T * T 
          - 1.0 / 113065000.0 * T * T * T * T));
        // Sun's mean anomaly
        const M = ELP82.toRadians(constrain(357.5291092 + 35999.0502909 * T 
          - 0.0001536 * T * T + 1.0 / 24490000.0 * T * T * T));
        // Moon's mean anomaly
        const Mp = ELP82.toRadians(constrain(134.9633964 + 477198.8675055 * T 
          + 0.0087414 * T * T + 1.0 / 69699.0 * T * T * T 
          - 1.0 / 14712000.0 * T * T * T * T));
        // Moon's argument of latitude (mean distance of the moon from it's ascending node)
        const F = ELP82.toRadians(constrain(93.2720950 + 483202.0175233 * T 
          - 0.0036539 * T * T - 1.0 / 3526000.0 * T * T * T 
          + 1.0 / 863310000.0 * T * T * T * T));
        // Earth's eccentricity of its orbit around the Sun
        const E = 1 - 0.002516 * T - 0.0000074 * T * T;
        // further arguments
        const A1 = ELP82.toRadians(constrain(119.75 + 131.849 * T));
        const A2 = ELP82.toRadians(constrain(53.09 + 479264.290 * T));
        const A3 = ELP82.toRadians(constrain(313.45 + 481266.484 * T));

        const LongitudeRadius = [ 
           // D   M  Mp   F    Long     Radius
            [ 0,  0,  1,  0,  6288774, -20905335 ], 
            [ 2,  0, -1,  0,  1274027,  -3699111 ], 
            [ 2,  0,  0,  0,   658314,  -2955968 ], 
            [ 0,  0,  2,  0,   213618,   -569925 ], 
            [ 0,  1,  0,  0,  -185116,     48888 ], 
            [ 0,  0,  0,  2,  -114332,     -3149 ], 
            [ 2,  0, -2,  0,    58793,    246158 ], 
            [ 2, -1, -1,  0,    57066,   -152138 ], 
            [ 2,  0,  1,  0,    53322,   -170733 ], 
            [ 2, -1,  0,  0,    45758,   -204586 ], 
            [ 0,  1, -1,  0,   -40923,   -129620 ], 
            [ 1,  0,  0,  0,   -34720,    108743 ], 
            [ 0,  1,  1,  0,   -30383,    104755 ], 
            [ 2,  0,  0, -2,    15327,     10321 ], 
            [ 0,  0,  1,  2,   -12528,         0 ], 
            [ 0,  0,  1, -2,    10980,     79661 ], 
            [ 4,  0, -1,  0,    10675,    -34782 ], 
            [ 0,  0,  3,  0,    10034,    -23210 ], 
            [ 4,  0, -2,  0,     8548,    -21636 ], 
            [ 2,  1, -1,  0,    -7888,     24208 ], 
            [ 2,  1,  0,  0,    -6766,     30824 ], 
            [ 1,  0, -1,  0,    -5163,     -8379 ], 
            [ 1,  1,  0,  0,     4987,    -16675 ], 
            [ 2, -1,  1,  0,     4036,    -12831 ], 
            [ 2,  0,  2,  0,     3994,    -10445 ], 
            [ 4,  0,  0,  0,     3861,    -11650 ], 
            [ 2,  0, -3,  0,     3665,     14403 ], 
            [ 0,  1, -2,  0,    -2689,     -7003 ], 
            [ 2,  0, -1,  2,    -2602,         0 ], 
            [ 2, -1, -2,  0,     2390,     10056 ], 
            [ 1,  0,  1,  0,    -2348,      6322 ], 
            [ 2, -2,  0,  0,     2236,     -9884 ], 
            [ 0,  1,  2,  0,    -2120,      5751 ], 
            [ 0,  2,  0,  0,    -2069,         0 ], 
            [ 2, -2, -1,  0,     2048,     -4950 ], 
            [ 2,  0,  1, -2,    -1773,      4130 ], 
            [ 2,  0,  0,  2,    -1595,         0 ], 
            [ 4, -1, -1,  0,     1215,     -3958 ], 
            [ 0,  0,  2,  2,    -1110,         0 ], 
            [ 3,  0, -1,  0,     -892,      3258 ], 
            [ 2,  1,  1,  0,     -810,      2616 ], 
            [ 4, -1, -2,  0,      759,     -1897 ], 
            [ 0,  2, -1,  0,     -713,     -2117 ], 
            [ 2,  2, -1,  0,     -700,      2354 ], 
            [ 2,  1, -2,  0,      691,         0 ], 
            [ 2, -1,  0, -2,      596,         0 ], 
            [ 4,  0,  1,  0,      549,     -1423 ], 
            [ 0,  0,  4,  0,      537,     -1117 ], 
            [ 4, -1,  0,  0,      520,     -1571 ], 
            [ 1,  0, -2,  0,     -487,     -1739 ], 
            [ 2,  1,  0, -2,     -399,         0 ], 
            [ 0,  0,  2, -2,     -381,     -4421 ], 
            [ 1,  1,  1,  0,      351,         0 ], 
            [ 3,  0, -2,  0,     -340,         0 ], 
            [ 4,  0, -3,  0,      330,         0 ], 
            [ 2, -1,  2,  0,      327,         0 ], 
            [ 0,  2,  1,  0,     -323,      1165 ], 
            [ 1,  1, -1,  0,      299,         0 ], 
            [ 2,  0,  3,  0,      294,         0 ], 
            [ 2,  0, -1, -2,        0,      8752 ] 
        ]; 

        const Latitude = [ 
            [ 0,  0,  0,  1, 5128122 ], 
            [ 0,  0,  1,  1,  280602 ], 
            [ 0,  0,  1, -1,  277693 ], 
            [ 2,  0,  0, -1,  173237 ], 
            [ 2,  0, -1,  1,   55413 ], 
            [ 2,  0, -1, -1,   46271 ], 
            [ 2,  0,  0,  1,   32573 ], 
            [ 0,  0,  2,  1,   17198 ], 
            [ 2,  0,  1, -1,    9266 ], 
            [ 0,  0,  2, -1,    8822 ], 
            [ 2, -1,  0, -1,    8216 ], 
            [ 2,  0, -2, -1,    4324 ], 
            [ 2,  0,  1,  1,    4200 ], 
            [ 2,  1,  0, -1,   -3359 ], 
            [ 2, -1, -1,  1,    2463 ], 
            [ 2, -1,  0,  1,    2211 ], 
            [ 2, -1, -1, -1,    2065 ], 
            [ 0,  1, -1, -1,   -1870 ], 
            [ 4,  0, -1, -1,    1828 ], 
            [ 0,  1,  0,  1,   -1794 ], 
            [ 0,  0,  0,  3,   -1749 ], 
            [ 0,  1, -1,  1,   -1565 ], 
            [ 1,  0,  0,  1,   -1491 ], 
            [ 0,  1,  1,  1,   -1475 ], 
            [ 0,  1,  1, -1,   -1410 ], 
            [ 0,  1,  0, -1,   -1344 ], 
            [ 1,  0,  0, -1,   -1335 ], 
            [ 0,  0,  3,  1,    1107 ], 
            [ 4,  0,  0, -1,    1021 ], 
            [ 4,  0, -1,  1,     833 ], 
            [ 0,  0,  1, -3,     777 ], 
            [ 4,  0, -2,  1,     671 ], 
            [ 2,  0,  0, -3,     607 ], 
            [ 2,  0,  2, -1,     596 ], 
            [ 2, -1,  1, -1,     491 ], 
            [ 2,  0, -2,  1,    -451 ], 
            [ 0,  0,  3, -1,     439 ], 
            [ 2,  0,  2,  1,     422 ], 
            [ 2,  0, -3, -1,     421 ], 
            [ 2,  1, -1,  1,    -366 ], 
            [ 2,  1,  0,  1,    -351 ], 
            [ 4,  0,  0,  1,     331 ], 
            [ 2, -1,  1,  1,     315 ], 
            [ 2, -2,  0, -1,     302 ], 
            [ 0,  0,  1,  3,    -283 ], 
            [ 2,  1,  1, -1,    -229 ], 
            [ 1,  1,  0, -1,     223 ], 
            [ 1,  1,  0,  1,     223 ], 
            [ 0,  1, -2, -1,    -220 ], 
            [ 2,  1, -2, -1,    -220 ], 
            [ 1,  0,  1,  1,    -185 ], 
            [ 2, -1, -2, -1,     181 ], 
            [ 0,  1,  2,  1,    -177 ], 
            [ 4,  0, -2, -1,     176 ], 
            [ 4, -1, -1, -1,     166 ], 
            [ 1,  0,  1, -1,    -164 ], 
            [ 4,  0,  1, -1,     132 ], 
            [ 1,  0, -1, -1,    -119 ], 
            [ 4, -1,  0, -1,     115 ], 
            [ 2, -2,  0,  1,     107 ] 
        ]; 

        let Lon = 0;
        let Radius = 0;
        for(let i = 0; i < LongitudeRadius.length; i++) {
            const t = LongitudeRadius[i];
            const a = D * t[0] + M * t[1] + Mp * t[2] + F * t[3];

            let e = 1;
            if(t[1] == 1 || t[1] == -1){e = E;}
            if(t[1] == 2 || t[1] == -2){e = E * E;}

            Lon += e * t[4] * Math.sin(a);
            Radius += e * t[5] * Math.cos(a);
        }

        let Lat = 0;
        for(let i = 0; i < Latitude.length; i++) {
            const t = Latitude[i];
            const a = D * t[0] + M * t[1] + Mp * t[2] + F * t[3];

            let e = 1;
            if(t[1] == 1 || t[1] == -1){e = E;}
            if(t[1] == 2 || t[1] == -2){e = E * E;}

            Lat += e * t[4] * Math.sin(a);
        }

        const aLon = 3958 * Math.sin(A1) + 1962 * Math.sin(Lp - F) + 318 * Math.sin(A2);
        const aLat = -2235 * Math.sin(Lp) + 382 * Math.sin(A3) + 175 * Math.sin(A1 - F) 
        + 175 * Math.sin(A1 + F) + 127 * Math.sin(Lp - Mp) - 115 * Math.sin(Lp + Mp);

        Lon = toDegrees(Lp) + (Lon + aLon) / 1000000;
        Radius = 385000.56 + Radius / 1000;
        Lat = (Lat + aLat) / 1000000;

        // Selenocentric elongation of the Earth from the Sun "The phase angle (i)"
        const i = toRadians(constrain(180 - D * 180 / Math.PI - 6.289 * Math.sin(Mp) 
        + 2.1 * Math.sin(M) - 1.274 * Math.sin(2 * D - Mp) - 0.658 * Math.sin(2 * D) 
        - 0.214 * Math.sin(2 * Mp) - 0.11 * Math.sin(D)));

        // moon Illumination * 100
        const K = (1 + Math.cos(i)) / 2;

        // Sun distance
        const S = (1.00014 - 0.01671 * Math.cos(M) - 0.00014 * Math.cos(2 * M));
      
        return [Lon, Radius, Lat, K, S];
    }
static constrain(d) {
        let t = d;
        t = t % 360;
        if (t < 0) {
        t += 360;
        }
        return t;
    }
static toRadians(x) {
  return x * (Math.PI / 180.0);
 }
}
var value = ELP82.elp82(T);
// Moon distance in km
var Rm = value[1];
//document.getElementById("distance").innerHTML = Rm;
//var distance = document.getElementById("distance");
//distance.innerHTML = Rm;
// Illumination in %
// var perc = value[3] * 100;
// Sun distance in km
// var Rs = value[4] * 1.496e+8;

var svg = d3.select(".moonphaze")
    .append("svg")
    //.style("background", "#292E35")
    .attr("width", 110)
    .attr("height", 110);

var wi = 110;
var hi = 110;
var radx = wi / 2 * 0.91;
var gradient = 2;
var mAxis = <?php echo $alm["parallacticAngle"];?>

function getX(phase, angle) {
    const f = Math.cos(toRadians(phase));

    let x;
    const cosi = Math.cos(toRadians(angle));
    x = f * radx * cosi + wi / 2;

    if((phase <= 180 && cosi < 0) || (phase > 180 && cosi > 0)) {
        x = radx * cosi + wi / 2;
    }
    return x;    
}

function drawDarkSide(phase) {
    const gradientPoints = [];

    let x = getX(phase - gradient, 0);
    let y = radx * Math.sin(0) + hi / 2;

    var path = d3.path();
    path.moveTo(x, y);

    for(let i = 0; i <= 360; i += 1) {
        x = getX(phase + gradient, i);
        let x2 = getX(phase - gradient, i);

        if(phase > 180) {
            const temp = x;
            x = x2;
            x2 = temp; 
        }
        y = radx * Math.sin(toRadians(i)) + hi / 2;

        gradientPoints.push([x, x2, y, phase]);

        path.lineTo(x, y);
    }
    path.closePath();
    svg.append("path")
    .attr("d", path)
    //.style('filter','blur(2px)')
    .style('fill');
    return gradientPoints;
}
 
function fillGradient(points) {  

    if(Math.abs(points[0][3] - 180) <= gradient) {
         return;
    } 
    for(let i = 0; i < points.length - 1; i++) {
        let x1 = points[i][0];
        let x2 = points[i][1];
        let y1 = points[i][2];
        let y2 = points[i+1][2];

        if(points[i][3] > 180) {
            x1++;
        }

        var defs = svg.append('defs');

        const gradientx = defs.append('linearGradient')
        .attr('id', 'Gradients')
        .attr('x1', '0%')
        .attr('x2', '0%')
        .attr('y1', '0%')
        .attr('y2', '0%');

        gradientx.append('stop')
        .attr('offset', '0%')
        .attr('class', 'stop-left')
        .attr('stop-color', '#9bf')
        .attr('stop-opacity', 0.1)

        gradientx.append('stop')
        .attr('offset', '0%')
        .attr('class', 'stop-right')        
        .attr('stop-color', '#292e35')
        .attr('stop-opacity', 0.3);

        const shadow = d3.path();
        shadow.moveTo(x1, y1);
        shadow.lineTo(x2, y1);
        shadow.lineTo(x2, y2);
        shadow.lineTo(x1, y2);
        shadow.closePath();
        svg
          .append("path")            
          .attr("d", shadow)          
          .attr('fill', 'none');
          //.style('filter','blur(2px)')
          //.style('fill', "url(#Gradients)");
    }
}

function drawPhase(phase) {
    svg.attr("transform", "rotate("+ mAxis +", 0, 0)")
    svg.style('fill', 'rgba(41, 46, 53, 0.8)');
    const gradientPoints = drawDarkSide(phase);
    fillGradient(gradientPoints);

}

function display(phase) {

    svg
        .append("circle") // background circle
        .attr("cx", 55)
        .attr("cy", 55)
        .attr("r", 49.25)
        .style('stroke', "#9bf")
        .style('fill', "#9bf");

  drawPhase(phase);    
}

phase = <?php echo $alm["moon_ecliptic_angle"];?>;
display(phase);
</script>
 
<div class=moonphasetext>    
<?php echo "<aqivalue1>".$moon_phase." </aqivalue1>";?>             
<br>           
</div>      
</article>  
  
<article>
 <moonphaseriseset>
<?php echo $info;?> Moon Rise/Set Information<br><br>
<svg id="i-ban" viewBox="0 0 32 32" width="10" height="10" fill="#39739f" stroke="#39739f" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg> Moonrise:

<?php echo $alm["moonrise"];?>&nbsp;
<svg id="i-chevron-top" viewBox="0 0 32 32" width="10" height="10" fill="none" stroke="#01a4b5" stroke-linecap="round" stroke-linejoin="round" stroke-width="10%"><path d="M30 20 L16 8 2 20" /></svg>
</span><br>
<svg id="i-ban" viewBox="0 0 32 32" width="10" height="10" fill="#D46842" stroke="#D46842" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%"><circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg> Moonset: 
<?php echo $alm["moonset"];?>&nbsp;
<svg id="i-chevron-bottom" viewBox="0 0 32 32" width="10" height="10" fill="none" stroke="#ff8841" stroke-linecap="round" stroke-linejoin="round" stroke-width="10%"><path d="M30 12 L16 24 2 12" /></svg>
</span><br>

<svg id="i-ban" viewBox="0 0 32 32" width="10" height="10" fill="rgba(255, 136, 65, 1.00)" stroke="rgba(255, 136, 65, 1.00)" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg>
<?php echo "Next Full Moon: <span style='color:#01a4b5'>", $alm["fullmoon"];?></span><br>
<svg id="i-ban" viewBox="0 0 32 32" width="10" height="10" fill="#777" stroke="#777" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /> <path d="M6 6 L26 26" /></svg>
<?php echo "Next New Moon: <span style='color:#01a4b5'>", $alm["newmoon"],"</span>";?>
</span><br /><svg id="i-ban" viewBox="0 0 32 32" width="10" height="10" fill="rgba(154, 186, 47, 1.00)" stroke="rgba(154, 186, 47, 1.00)" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg> 
<?php echo "Current Moon cycle is: <span style='color:#ff8841'>", number_format($alm["moon_age"],2),"</span> days old";?>
</span><br /><svg id="i-ban" viewBox="0 0 32 32" width="10" height="10" fill="#007fff" stroke="#007fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg> 
<?php echo "Illumination: <span style='color:#ff8841'>", number_format($alm["luminance"],2),"</span> %";?>
</span><br /><svg id="i-ban" viewBox="0 0 32 32" width="10" height="10" fill="#9bf" stroke="#9bf" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg> 
<?php echo "Moonphase: <span style='color:#9bf'>", $alm["moonphase"],"</span>";?>
</span><br /><svg id="i-ban" viewBox="0 0 32 32" width="10" height="10" fill="#007fff" stroke="#007fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg> 
<?php echo "Moon Distance: <span style='color:#ff8841'>", number_format(($alm["moon_distance"]),2),"</span> km";?>
</span><br /><svg id="i-ban" viewBox="0 0 32 32" width="10" height="10" fill="#9bf" stroke="#9bf" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg> 
<?php echo "Ecliptic Longitude: <span style='color:#9bf'>", $alm["moon_ecliptic_angle"],"&deg;</span>";?>
</span><br /><svg id="i-ban" viewBox="0 0 32 32" width="10" height="10" fill="#9bf" stroke="#9bf" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg> 
<?php echo "Parallactic Angle: <span style='color:#9bf'>", $alm["parallacticAngle"],"&deg;</span>";?>
</article>  

<article>
<?php echo $info;?> Moon Facts: <orange>Did you Know?</orange><br><br>
<svg id="i-ban" viewBox="0 0 32 32" width="8" height="8" fill="#3b9cac" stroke="#3b9cac" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg> The Moon was approximately formed 4.5 billion years ago  .<br>
<svg id="i-ban" viewBox="0 0 32 32" width="8" height="8" fill="#3b9cac" stroke="#3b9cac" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg> High and Low tides are caused by the Moons gravitational pull.<br>
<svg id="i-ban" viewBox="0 0 32 32" width="8" height="8" fill="#3b9cac" stroke="#3b9cac" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg> The Moon orbits the Earth every 29.53 days approximately. <br>
<svg id="i-ban" viewBox="0 0 32 32" width="8" height="8" fill="#3b9cac" stroke="#3b9cac" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg> As sunlight hits the moon's surface,temperatures can reach 260&deg;F (127&deg;C).<br>
<svg id="i-ban" viewBox="0 0 32 32" width="8" height="8" fill="#3b9cac" stroke="#3b9cac" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg> On the dark side of the moon,temperatures can drop to minus -280&deg;F (-173&deg;C).<br>

</article> 
  
<article>
<?php echo $info ;?> Moon Photography Guide<br><br> <svg id="i-ban" viewBox="0 0 32 32" width="8" height="8" fill="#3b9cac" stroke="#3b9cac" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg>
Use a Tripod
<br>
<svg id="i-ban" viewBox="0 0 32 32" width="8" height="8" fill="#3b9cac" stroke="#3b9cac" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg>
Use a Zoom Lens
<br>
<svg id="i-ban" viewBox="0 0 32 32" width="8" height="8" fill="#3b9cac" stroke="#3b9cac" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg>
Measure the exposure 
<br>
<svg id="i-ban" viewBox="0 0 32 32" width="8" height="8" fill="#3b9cac" stroke="#3b9cac" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg>
Avoid ambient nearby lighting 
<br>
<svg id="i-ban" viewBox="0 0 32 32" width="8" height="8" fill="#3b9cac" stroke="#3b9cac" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg>
Use a shutter remote release 
<br>
<svg id="i-ban" viewBox="0 0 32 32" width="8" height="8" fill="#3b9cac" stroke="#3b9cac" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg>
Always shoot in RAW for post processing              
</article>    
<article>
<?php echo $info ;?> Radio Ham Guide (<orange>EME</orange>)<br><br>
Earth–Moon–Earth communication (<orange>EME</orange>), also known as Moon bounce, is a radio communications technique that relies on the propagation of radio waves from an Earth-based transmitter directed via reflection from the surface of the Moon back to an Earth-based receiver using VHF and UHF amateur radio bands.
</article> 
<article>
<div class=actualt>&nbsp;&nbsp; &copy; Information</div>
<svg id="i-ban" viewBox="0 0 32 32" width="8" height="8" fill="#3b9cac" stroke="#3b9cac" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg>
Moon Data generated from python-pyephem
<br>
<svg id="i-ban" viewBox="0 0 32 32" width="8" height="8" fill="#3b9cac" stroke="#3b9cac" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg>
Moonphase Graphic Powered by D3.js   
</main>
