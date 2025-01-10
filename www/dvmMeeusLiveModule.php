<?php
include('dvmCombinedData.php');

if($theme==="light"){ echo "<body style='background-color:e0eafb'>";}
else if($theme==="dark"){ echo "<body style='background-color:#292E35'>";}
if($theme==="light"){ $textColor = "#1c4263";}
else if($theme==="dark"){ $textColor = "silver";}
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
<title>Meeus Live</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body> 
<div style="position:relative; top:10px;display:flex;justify-content:center;align-items:center;font-family:Helvetica;">
<?php
if($theme==="light"){echo "<font color='#1c4263'>"."Geocentric Live Meeus Calculation"."</font>";}
else if($theme==="dark"){echo "<font color='silver'>"."Geocentric Live Meeus Calculation"."</font>";}
?>
</div>
<style>
.meeus{display:flex;justify-content:center;align-items:center;margin-top:20px;margin-left:0px;
text{fill:<?php if($theme==="light"){echo "#1c4263";} else if($theme==="dark"){echo "silver";}?>;font-family:Helvetica;font-size:7.5px;}
.axis path{stroke:<?php if($theme==="light"){echo "#1c4263";} else if($theme==="dark"){echo "#555";}?>;stroke-width:1;fill:none;}
.axis line{stroke:<?php if($theme==="light"){echo "#1c4263";} else if($theme==="dark"){echo "#555";}?>;stroke-width:1;fill:none;} 
.horiz.line{stroke:#007fff;stroke-width:1;fill:none;}
.zen.line{stroke:#2e8b57;stroke-width:1;fill:none;}}
.output{display:flex;justify-content:center;align-items:center;margin-top:0px;margin-left:10px;}
.moonx{display:flex;justify-content:center;align-items:center;margin-top:-565px;margin-left:520px;}
</style>
<script src="js/d3.7.9.0.min.js"></script>
<div class="meeus"></div>
<div class="output"></div>
<div class="moonx">
<div class="moonphaze"></div>
</div>
<script>
// text color
var tcolor = "<?php echo $textColor;?>";

// Math functions
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

function skynet(tiktok) {
    if (tiktok < 10) {
      tiktok = '0' + tiktok;
    }
  return tiktok;
} 

function nan(x) {
  if (isNaN(x)) {
    return 180.0;
  }
  return x;
}

    var dx = new Date();
    var start = new Date(dx.getUTCFullYear(), 0, 0);
    var diff = dx - start + (start.getTimezoneOffset() - dx.getTimezoneOffset()) * 60 * 1000;
    var Day = 1000 * 60 * 60 * 24;
    var day = Math.floor(diff / Day); // elapsed days since the beginning of the year

    var delta = dx.getUTCFullYear() - 1949;   
    var leap = delta / 4;

    // Timezone as a text
    var TZ = Intl.DateTimeFormat().resolvedOptions().timeZone; // example Europe/Berlin

    // Establish UT Decimal time for the Sun Calculation
    var UT = (dx.getUTCHours() + dx.getUTCMinutes() / 60 + dx.getUTCSeconds() / 3600.0 + dx.getUTCMilliseconds() / 86400);

    // Your latitude and longitude comes from weewx dvmCombinedData.php
    var latitude = <?php echo $lat;?>;
    var longitude = <?php echo $lon;?>;

    // Your Location, Do you live in the Northern or Southern Hemisphere ?
    var hemisphere;
    if (latitude > 0.0) {
      <?php echo "hemisphere = 0";?>;
    } 
     else {
      <?php echo "hemisphere = 1";?>;
    }

    // Establish Julian date and jd 2451545.0 (noon, 1 January 2000)
    var jd = 32916.5 + delta * 365 + leap + day + UT / 24;
    // centuries
    var t = jd - 51545;

    // Establish Ecliptic coordinates (Sun)  
    // Sun's Mean longitude
    var mnlong = constrain(280.460 + 0.9856474 * t);
    // Sun's Mean anomaly
    var mnanom = toRadians(constrain(357.528 + 0.9856003 * t));
    // Sun's Ecliptic longitude 
    var eclong = toRadians(constrain(mnlong + 1.915 * Math.sin(mnanom) + 0.020 * Math.sin(2 * mnanom)));
    // Obliquity of ecliptic
    var oblqec = toRadians(23.439 - 0.0000004 * t);

    // Establish Celestial coordinates (Sun)     
    var num = Math.cos(oblqec) * Math.sin(eclong);
    var den = Math.cos(eclong);
    var Ra = Math.atan(num / den);

    // Sun Right Ascension
    if (den < 0) {
        Ra = Ra + Math.PI;
    } else if (num < 0) {
        Ra = Ra + 2 * Math.PI;
    }
    // Sun Declination
    var Dec = Math.asin(Math.sin(oblqec) * Math.sin(eclong));

    // Establish Local coordinates

    // Greenwich mean sidereal time
    var gmst = (6.697375 + 0.0657098242 * t + UT);
    // re-evaluate gmst so it stays within the domain 0 to 24.
        var gmst = gmst % 24;
          if (gmst < 0) {
            var gmst = gmst + 24;
          }

    // Local mean sidereal time 
    var lmst = (gmst + longitude / 15);
    // re-evaluate gmst so it stays within the domain 0 to 24.
        var lmst = lmst % 24;
          if (lmst < 0) {
            var lmst = lmst + 24;
          }

    var Lmst = toRadians(15 * lmst);

    // Sun Hour angle
    var Ha = (Lmst - Ra) % (2 * Math.PI);

    // Sun Altitude
    var alt = Math.asin(Math.sin(Dec) * Math.sin(toRadians(latitude)) + Math.cos(Dec) * Math.cos(toRadians(latitude)) * Math.cos(Ha));

    // Sun Azimuth
    var azi = Math.asin( - Math.cos(Dec) * Math.sin(Ha) / Math.cos(alt));

    if (Math.sin(Dec) - Math.sin(alt) * Math.sin(toRadians(latitude)) < 0) {
        azi = Math.PI - azi;
    } else if (Math.sin(azi) < 0) {
        azi += 2 * Math.PI;
    }

    // start Moon calculation

    // Universal Time variables
    // Selected epoch constants for January 2010, 00:00:00

    var epochDays = 2455196.5;          // JD at selected epoch
    var εg = 279.557208;                // Sun's mean ecliptic longitude
    var ωg = 283.112438;                // Longitude of the Sun at perigee
    var eccSunEarth = 0.016705;         // Eccentricity of the Sun-Earth orbit
    var l0 = 91.929336;                 // Moon's mean longitude at epoch
    var P0 = 130.143076;                // Mean longitude of the perigee at epoch
    var N0 = 291.682547;                // Mean longitude of the node at epoch
    var iMoon = 5.145396;               // Inclination of Moon's orbit
    var eMoon = 0.0549;                 // Eccentricity of the Moon's orbit
    var aMoon = 384401;                 // Semi-major axis of Moon's orbit
    var θ0Moon = 0.5181;                // Moon's angular diameter at distance a from Earth
    var π0Moon = 0.9507;                // Moon's parallax at distance a from Earth
    
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

    // Establish JD
    var JD = valueB + valueC + valueD + dayJulian + 1720994.5;
    // Centuries
    var T = (JD - 2451545) / 36525.0;

    // Establish current Moon age in days
    var daysPerLunarMonth = 29.530588853;
    var f = ((JD - 2451550.1) / daysPerLunarMonth) % 1;
    f = (f < 0) ? f + 1 : f;
    var m_days = f * daysPerLunarMonth - 0.385;

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
        // moon's argument of longitude of it's ascending node
        const Ω = ELP82.toRadians(constrain(125.04452 - (1934.136261 * T) 
          + (0.0020708 * T * T) + (T * T * T) / 450000));
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

        // moon Illumination
        const K = (1 + Math.cos(i)) / 2;
        // Sun distance
        const S = (1.00014 - 0.01671 * Math.cos(M) - 0.00014 * Math.cos(2 * M));

        // mean obliquity
        const ε0 = 23.43929 - (0.01300417 * T) - (0.0000001638889 * T * T) - (0.0000005036111 * T * T * T);
        // obliquity nutation
        const Δε = 0.0026 * Math.cos(125.0 - 0.05295 * T) + 0.0002 * Math.cos(200.9 + 1.97129 * T);
        // true obliquity
        const ε = ε0 + Δε;

        return [Lon, Radius, Lat, K, S, ε];
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
const Rm = value[1];
// Illumination in %
const perc = value[3] * 100;
// Sun distance in km
const Rs = value[4] * 1.496e+8;

const Δ = value[5];

var moonphase;
    if (perc > 2 && perc < 48 && m_days < 15) {
        moonphase = "<?php echo "Waxing Crescent";?>";
    } else if (perc >= 48 && perc <= 52 && m_days < 15) { 
        moonphase = "<?php echo "First Quarter";?>";
    } else if (perc > 52 && perc < 98 && m_days < 15) {
        moonphase = "<?php echo "Waxing Gibbous";?>";
    } else if (perc >= 98) {
        moonphase = "<?php echo "Full Moon";?>"; 
    } else if (perc < 98 && perc > 52 && m_days > 14) {
        moonphase = "<?php echo "Waning Gibbous";?>";
    } else if (perc >= 48 && perc <= 52 && m_days > 14) { 
        moonphase = "<?php echo "Last Quarter";?>"; 
    } else if (perc < 48 && perc > 2 && m_days > 14) {
        moonphase = "<?php echo "Waning Crescent";?>";
    } else {
        moonphase = "<?php echo "New Moon";?>";
    }

      // Convert Julian day to the adopted epoch
      var daysSinceEpoch = JD - epochDays;
          
      // Convert UT to Greenwich Mean Sidereal Time, GMST
      var DateAt0h = new Date(yearCurrent, monthCurrent - 1, dayCurrent, - timezoneOffset, 0, 0, 0);

      var dayCurrentAt0h = DateAt0h.getUTCDate();
      var monthCurrentAt0h = DateAt0h.getUTCMonth() + 1;
      var yearCurrentAt0h = DateAt0h.getUTCFullYear();
      var hourCurrentAt0h = DateAt0h.getUTCHours();
      var minuteCurrentAt0h = DateAt0h.getUTCMinutes();
      var secondCurrentAt0h = DateAt0h.getUTCSeconds();
      var millisecondCurrentAt0h = DateAt0h.getUTCMilliseconds();
      var timezoneOffsetAt0h = DateAt0h.getTimezoneOffset() / 60;
      var UTCStringAt0h = DateAt0h.toUTCString();
      
      // Convert JDAt0h to Julian Day
      var dayJulianAt0h = dayCurrentAt0h + ((hourCurrentAt0h * 3600 + minuteCurrentAt0h * 60 + secondCurrentAt0h + millisecondCurrentAt0h / 1000) / 86400);

      if (monthCurrentAt0h <= 2) {
        var monthJulianAt0h = monthCurrentAt0h + 12;
        var yearJulianAt0h = yearCurrentAt0h - 1;
      }
      else {
        var monthJulianAt0h = monthCurrentAt0h;
        var yearJulianAt0h = yearCurrentAt0h;
      }
      
      // Assuming the date is after the start of the Gregorian calendar (15/10/1582)
      var JDAt0h = valueB + valueC + valueD + dayJulianAt0h + 1720994.5;
      
      var valueS = JDAt0h - 2451545;
      var valueT = valueS / 36525;
      
      var valueT0 = 6.697374558 + (2400.051336 * valueT) + (0.000025862 * valueT * valueT);
        // re-evaluate valueT0 so it stays within the domain 0 to 24.
        var valueT0 = valueT0 % 24;
          if (valueT0 < 0) {
            var valueT0 = valueT0 + 24;
          }
      
      // Convert UT to decimal time
      var UTDecimal = ((dx.getUTCSeconds() + dx.getUTCMilliseconds() / 1000) / 60 + dx.getUTCMinutes()) / 60 + dx.getUTCHours();

      var UTDecimal = UTDecimal * 1.002737909;
      
      // Add decimal time to T0
      var GST = valueT0 + UTDecimal;
      
        // re-evaluate GST so it stays within the domain 0 to 24.
        var GST = GST % 24;
          if (GST < 0) {
            var GST = GST + 24;
          }

      // Convert GST to hours, minutes, seconds
      var GSTHours = GST / Math.abs(GST) * Math.floor(Math.abs(GST));
      var GSTMinutes = Math.floor((Math.abs(GST) - Math.abs(GSTHours)) * 60);
      var GSTSeconds = ((Math.abs(GST) - Math.abs(GSTHours)) * 60 - GSTMinutes) * 60;

    // Position of the Sun
    var valueD = JDAt0h - epochDays;
    
    var valueN = (valueD * 360) / 365.242191;
      // re-evaluate N so it stays within the domain 0 to 360.
      var valueN = valueN % 360;
        if (valueN < 0) {
          var valueN = valueN + 360;
        }
    var MSun = valueN + εg - ωg;

      if (MSun < 0) {
        var MSun = MSun + 360;
      }
     
    var Ec = 360 / Math.PI * eccSunEarth * Math.sin(toRadians(MSun));    
    var λSun = valueN + Ec + εg;     
      // re-evaluate λSun so it stays within the domain 0 to 360.
      var λSun = λSun % 360;
        if (λSun < 0) {
          var λSun = λSun + 360;
        }       
    // Convert ecliptic longitude of Sun and λSun to equatorial coordinates
    var valueT = (JD - 2451545) / 36525;
    var εSun = 23.439292 - (((46.815 * valueT) + (0.0006 * valueT * valueT) - (0.00181 * valueT * valueT * valueT)) / 3600);
    var βSun = 0;
    var δSun = toDegrees(Math.asin(Math.sin(toRadians(βSun)) * Math.cos(toRadians(εSun)) + Math.cos(toRadians(βSun)) * Math.sin(toRadians(εSun)) * Math.sin(toRadians(λSun))));
    var YSun = Math.sin(toRadians(λSun)) * Math.cos(toRadians(εSun)) - Math.tan(toRadians(βSun)) * Math.sin(toRadians(εSun));
    var XSun = Math.cos(toRadians(λSun));
    var αSun = toDegrees(Math.atan(YSun/XSun));
    var αSun = αSun % 360;

      // Solve ambiguity which arises from taking the inverse tan. First identify which quadrant the angle should belong to (by checking the signs on XSun and YSun)     
      if (YSun > 0) {
        if (XSun > 0) {
          var LOQuadrant = 0;
          var HIQuadrant = 90;
        }
        else {
          var LOQuadrant = 90;
          var HIQuadrant = 180;
        }
      }
      else {
        if (XSun > 0) {
          var LOQuadrant = 270;
          var HIQuadrant = 360;
        }
        else {
          var LOQuadrant = 180;
          var HIQuadrant = 270;
        }
      } 

      // Add 180 or 360 depending on the quadrant αSun is in.
      if (αSun < HIQuadrant && αSun > LOQuadrant) {
        αSun = αSun;
      }
      else if ((αSun + 180) < HIQuadrant && (αSun + 180) > LOQuadrant) {
         αSun = αSun + 180;
      }
      else if ((αSun + 360) < HIQuadrant && (αSun + 360) > LOQuadrant) {
         αSun = αSun + 360;
      }
      else {
        αSun = "<?php echo "Error";?>";
        // If this is the case, something has gone wrong. αSun should be in the quadrant by either adding 180 or 360.
      }

    // Convert to hours
    var αSun = αSun / 15;   
    var αSunHours = αSun / Math.abs(αSun) * Math.floor(Math.abs(αSun));
    var αSunMinutes = Math.floor((Math.abs(αSun) - Math.abs(αSunHours)) * 60);
    var αSunSeconds = (((Math.abs(αSun) - Math.abs(αSunHours)) * 60) - αSunMinutes) * 60;
    
    var δSunHours = δSun / Math.abs(δSun) * Math.floor(Math.abs(δSun));
    var δSunMinutes = Math.floor((Math.abs(δSun) - Math.abs(δSunHours)) * 60);
    var δSunSeconds = (((Math.abs(δSun) - Math.abs(δSunHours)) * 60) - δSunMinutes) * 60;

    // Convert sun altitude to hours
    var sHours = toDegrees(alt) / (Math.abs(toDegrees(alt))) * Math.floor(Math.abs(toDegrees(alt)));
    var sMinutes = Math.floor((Math.abs(toDegrees(alt)) - Math.abs(sHours)) * 60);
    var sSeconds = (((Math.abs(toDegrees(alt)) - Math.abs(sHours)) * 60) - sMinutes) * 60;

    // Convert sun azimuth to hours
    var SHours = toDegrees(azi) / (Math.abs(toDegrees(azi))) * Math.floor(Math.abs(toDegrees(azi)));
    var SMinutes = Math.floor((Math.abs(toDegrees(azi)) - Math.abs(SHours)) * 60);
    var SSeconds = (((Math.abs(toDegrees(azi)) - Math.abs(SHours)) * 60) - SMinutes) * 60;

    // Calculate Moon position
    
    var lMoon = 13.1763966 * daysSinceEpoch + l0;
      // Constrain lMoon to within 0 to 360
      var lMoon = lMoon % 360;
        if (lMoon < 0) {
          var lMoon = lMoon + 360;
        }
    var Mm = lMoon - 0.1114041 * daysSinceEpoch - P0;
      // Constrain Mm to within 0 to 360
      var Mm = Mm % 360;
        if (Mm < 0) {
          var Mm = Mm + 360;
        }   
    var NMoon = N0 - 0.0529539 * daysSinceEpoch;
      // Constrain Mm to within 0 to 360
      var NMoon = NMoon % 360;
        if (NMoon < 0) {
          var NMoon = NMoon + 360;
        }

    var Ev = 1.2739 * Math.sin(2 * (toRadians(lMoon) - toRadians(λSun)) - toRadians(Mm));
    var Ae = 0.1858 * Math.sin(toRadians(MSun));
    var A3 = 0.37 * Math.sin(toRadians(MSun));
    var MPrimem = Mm + Ev - Ae - A3;
    var Ec = 6.2886 * Math.sin(toRadians(MPrimem));   
    var A4 = 0.214 * Math.sin(2 * toRadians(MPrimem));
    var lPrime = lMoon + Ev + Ec - Ae + A4;
    var VMoon = 0.6583 * Math.sin(2 * (toRadians(lPrime) - toRadians(λSun)));  
    var lPrimePrime = lPrime + VMoon;  
    var NPrime = NMoon - (0.16 * Math.sin(toRadians(MSun)));
    var yMoon = Math.sin(toRadians(lPrimePrime) - toRadians(NPrime)) * Math.cos(toRadians(iMoon));
    var xMoon = Math.cos(toRadians(lPrimePrime) - toRadians(NPrime));
    var λMoon = toDegrees(Math.atan(yMoon / xMoon));

    // Establish Moonphase Angle (0°-360°)
    var MphaseAngle = (lPrime - toDegrees(eclong));
      // Constrain MphaseAngle to within 0 to 360
      MphaseAngle = MphaseAngle % 360;
        if (MphaseAngle < 0) {
           MphaseAngle = MphaseAngle + 360;
        }

      // Solve ambiguity which arises from taking the inverse tan. First identify which quadrant the angle should belong to (by checking the signs on XMoon and YMoon)     
      if (yMoon > 0) {
        if (xMoon > 0) {
          var LOQuadrantMn = 0;
          var HIQuadrantMn = 90;
        }
        else {
          var LOQuadrantMn = 90;
          var HIQuadrantMn = 180;
        }
      }
      else {
        if (xMoon > 0) {
          var LOQuadrantMn = 270;
          var HIQuadrantMn = 360;
        }
        else {
          var LOQuadrantMn = 180;
          var HIQuadrantMn = 270;
        }
      } 

      // Add 180 or 360 depending on the quadrant Moon is in.
      if (λMoon < HIQuadrantMn && αSun > LOQuadrantMn) {
        λMoon = λMoon;
      }
      else if ((λMoon + 180) < HIQuadrantMn && (λMoon + 180) > LOQuadrantMn) {
        λMoon = λMoon + 180;
      }
      else if ((λMoon + 360) < HIQuadrantMn && (λMoon + 360) > LOQuadrantMn) {
        λMoon = λMoon + 360;
      }
      else {
        λMoon = "<?php echo "Error";?>";
        // If this is the case, something has gone wrong. αSun should be in the quadrant by either adding 180 or 360.
      }

    var λMoon = λMoon + NPrime;
    var βMoon = toDegrees(Math.asin(Math.sin(toRadians(lPrimePrime) - toRadians(NPrime)) * Math.sin(toRadians(iMoon))));
    var δMoon = toDegrees(Math.asin(Math.sin(toRadians(βMoon)) * Math.cos(toRadians(εSun)) + Math.cos(toRadians(βMoon)) * Math.sin(toRadians(εSun)) * Math.sin(toRadians(λMoon))));
    var valueYMoon = Math.sin(toRadians(λMoon)) * Math.cos(toRadians(εSun)) - Math.tan(toRadians(βMoon)) * Math.sin(toRadians(εSun));
    var valueXMoon = Math.cos(toRadians(λMoon));  
    var αMoon = toDegrees(Math.atan(valueYMoon / valueXMoon));
    var αMoon = αMoon % 360;

      // Solve ambiguity which arises from taking the inverse tan. First identify which quadrant the angle should belong to (by checking the signs on X and Y)     
      if (valueYMoon > 0) {
        if (valueXMoon > 0) {
          var LOQuadrantMn2 = 0;
          var HIQuadrantMn2 = 90;
        }
        else {
          var LOQuadrantMn2 = 90;
          var HIQuadrantMn2 = 180;
        }
      }
      else {
        if (valueXMoon > 0) {
          var LOQuadrantMn2 = 270;
          var HIQuadrantMn2 = 360;
        }
        else {
          var LOQuadrantMn2 = 180;
          var HIQuadrantMn2 = 270;
        }
      } 

      // Add 180 or 360 depending on the quadrant αSun is in.
      if (αMoon < HIQuadrantMn2 && αMoon > LOQuadrantMn2) {
        αMoon = αMoon;      
      }     
      else if ((αMoon + 180) < HIQuadrantMn2 && (αMoon + 180) > LOQuadrantMn2) {
        αMoon = αMoon + 180;
      }
      else if ((αMoon + 360) < HIQuadrantMn2 && (αMoon + 360) > LOQuadrantMn2) {
        αMoon = αMoon + 360;
      }
      else {
        αMoon = "<?php echo "Error";?>";
        // If this is the case, something has gone wrong. αSun should be in the quadrant by either adding 180 or 360.
      }

    // Convert to hours
    var αMoon = αMoon / 15;
    var αMoonHours = αMoon / (Math.abs(αMoon)) * Math.floor(Math.abs(αMoon));
    var αMoonMinutes = Math.floor((Math.abs(αMoon) - Math.abs(αMoonHours)) * 60);
    var αMoonSeconds = (((Math.abs(αMoon) - Math.abs(αMoonHours)) * 60) - αMoonMinutes) * 60;
    
    var δMoonHours = δMoon / (Math.abs(δMoon)) * Math.floor(Math.abs(δMoon));
    var δMoonMinutes = Math.floor((Math.abs(δMoon) - Math.abs(δMoonHours)) * 60);
    var δMoonSeconds = (((Math.abs(δMoon) - Math.abs(δMoonHours)) * 60) - δMoonMinutes) * 60;
  
    // Convert equatorial coordinates to horizon coordinates
  
    // East longitudes are positive, West are negative

    var LST = GST + longitude / 15;
    // Constrain LST to within 0 to 24
        LST = LST % 24;
      if (LST < 0) {
           LST = LST + 24;
      }

    // Convert to hours
    var LSTHours = LST / (Math.abs(LST)) * Math.floor(Math.abs(LST));
    var LSTMinutes = Math.floor((Math.abs(LST) - Math.abs(LSTHours)) * 60);
    var LSTSeconds = (((Math.abs(LST) - Math.abs(LSTHours)) * 60) - LSTMinutes) * 60;

  var hourAngle = LST - αMoon;
    if (hourAngle < 0) {
      hourAngle = hourAngle + 24;
    }

  // Convert to hours
  var HHours = hourAngle / (Math.abs(hourAngle)) * Math.floor(Math.abs(hourAngle));
  var HMinutes = Math.floor((Math.abs(hourAngle) - Math.abs(HHours)) * 60);
  var HSeconds = (((Math.abs(hourAngle) - Math.abs(HHours)) * 60) - HMinutes) * 60;

  var hourAngle = hourAngle * 15;
  // a = moon altitude
  var a = toDegrees(Math.asin(Math.sin(toRadians(δMoon)) * Math.sin(toRadians(latitude)) + Math.cos(toRadians(δMoon)) * Math.cos(toRadians(latitude)) * Math.cos(toRadians(hourAngle))));
  // APrime = moon azimuth
  var APrime = toDegrees(Math.acos((Math.sin(toRadians(δMoon)) - (Math.sin(toRadians(latitude)) * Math.sin(toRadians(a)))) / (Math.cos(toRadians(latitude)) * Math.cos(toRadians(a)))));

    if ( hourAngle > 0 && hourAngle < 180 ) {
      var azimuth = 360 - APrime;
    }
    else {
      var azimuth = APrime;
    }

  // Convert to hours
  var aHours = a / (Math.abs(a)) * Math.floor(Math.abs(a));
  var aMinutes = Math.floor((Math.abs(a) - Math.abs(aHours)) * 60);
  var aSeconds = (((Math.abs(a) - Math.abs(aHours)) * 60) - aMinutes) * 60;

  // Convert to hours
  var AHours = azimuth / (Math.abs(azimuth)) * Math.floor(Math.abs(azimuth));
  var AMinutes = Math.floor((Math.abs(azimuth) - Math.abs(AHours)) * 60);
  var ASeconds = (((Math.abs(azimuth) - Math.abs(AHours)) * 60) - AMinutes) * 60;

// now we are ready to calculate the sun and moon curves and use the Meeus data

// Sun Declination
var DeclinationS = toDegrees(Dec);

// calculate Sun curve
var sdec = DeclinationS;
var althrtab = [];
var shartab = [];
var total = 1440;

for (var i = 0; i < total; i++) {
  var sha = 180.0 + i * (360.0 / total);  
  var cossha = Math.cos(toRadians(sha)); 
  var cossundec = Math.cos(toRadians(sdec));  
  var coslat = Math.cos(toRadians(latitude)); 
  var cosmath = cossha * cossundec * coslat; 
  var sinsundec = Math.sin(toRadians(sdec)); 
  var sinlat = Math.sin(toRadians(latitude));  
  var sinmath = sinsundec * sinlat;  
  var sinelevation = cosmath + sinmath;  
  var elevation = toDegrees(Math.asin(sinelevation));
  var curaltlong = elevation;  
  var curalt = curaltlong;  
  althrtab[i] = curalt;
  shartab[i] = sha - 360.0;
}

var azitab;
var azmathA;
var azmathB;
var cosazA;
var aziA;
var aziplotA;

if (hemisphere == 0) {
  azitab = [];
  for (var i = 0; i < total; i++) {
    azmathA =
      Math.cos(toRadians(shartab[i])) * cossundec * sinlat - sinsundec * coslat;
    azmathB = Math.cos(toRadians(althrtab[i]));
    cosazA = azmathA / azmathB;
    aziA = toDegrees(Math.acos(cosazA));
    aziplotA = aziA;
    if (shartab[i] < 0) {
      azitab[i] = (aziplotA - 180.0) * -1;
    } else {
      azitab[i] = aziplotA + 180.0;
    }
  }
}
if (hemisphere == 1) {
  azitab = [];
  for (var i = 0; i < total; i++) {
    azmathA =
      Math.cos(toRadians(shartab[i])) * cossundec * sinlat - sinsundec * coslat;
    azmathB = Math.cos(toRadians(althrtab[i]));
    cosazA = azmathA / azmathB;
    aziA = toDegrees(Math.acos(cosazA));
    aziplotA = aziA;
    if (shartab[i] < 0) {
      azitab[i] = (aziplotA - 360.0) * -1;
    } else {
      azitab[i] = aziplotA;
    }
  }
}

// Moon Declination       
var DeclinationM = δMoon;

// calculate Moon curve
var sdecx = DeclinationM;
var shartabx = [];
var althrtabx = [];
var total = 1440;

var cossundecx = Math.cos(toRadians(sdecx));
var coslatx = Math.cos(toRadians(latitude));
var sinlatx = Math.sin(toRadians(latitude));
var sinsundecx = Math.sin(toRadians(sdecx));
var sinmathx = sinsundecx * sinlatx;

for (var i = 0; i < total; i++) {
  var shax = 180.0 + i * (360.0 / total);
  var cosshax = Math.cos(toRadians(shax));
  var cosmathx = cosshax * cossundecx * coslatx;
  var sinelevationx = cosmathx + sinmathx;
  var elevationx = toDegrees(Math.asin(sinelevationx));
  var curaltlongx = elevationx;
  var curaltx = curaltlongx;
  althrtabx[i] = curaltx;
  shartabx[i] = shax - 360.0;
}

var azitabx;
var azmath1;
var azmath2;
var cosazx;
var azix;
var aziplotx;

if (hemisphere == 0) {
  azitabx = [];
  for (var i = 0; i < total; i++) {
    azmath1 =
      Math.cos(toRadians(shartabx[i])) * cossundecx * sinlatx -
      sinsundecx * coslatx;
    azmath2 = Math.cos(toRadians(althrtabx[i]));
    cosazx = azmath1 / azmath2;
    azix = toDegrees(Math.acos(cosazx));
    aziplotx = azix;
    if (shartabx[i] < 0) {
      azitabx[i] = (aziplotx - 180.0) * -1;
    } else {
      azitabx[i] = aziplotx + 180.0;
    }
  }
}
if (hemisphere == 1) {
  azitabx = [];
  for (var i = 0; i < total; i++) {
    azmath1 =
      Math.cos(toRadians(shartabx[i])) * cossundecx * sinlatx -
      sinsundecx * coslatx;
    azmath2 = Math.cos(toRadians(althrtabx[i]));
    cosazx = azmath1 / azmath2;
    azix = toDegrees(Math.acos(cosazx));
    aziplotx = azix;
    if (shartabx[i] < 0) {
      azitabx[i] = (aziplotx - 360.0) * -1;
    } else {
      azitabx[i] = aziplotx;
    }
  }
}

// Create some fake data to populate a d3.js chart scale 
var suncurve = [[0.0, 0.0],[0.0, 0.0]];
var mooncurve = [[0.0, 0.0],[0.0 ,0.0]];

var innerColor = "rgb(230, 200, 200)";

var sunData = [];
for(var i = 1; i < suncurve.length; i++) {
  sunData = [...sunData,[suncurve[i - 1],suncurve[i]]]
};

var moonData = [];
for(var i = 1; i < mooncurve.length; i++) {
  moonData = [...moonData,[mooncurve[i - 1],mooncurve[i]]]
};

// Create the d3 chart and add the sun and moon data 
var w = 620;
var h = 320;
var padding = 25;
var padding_up = 8;

if (latitude < 0.0) {
var xScale = d3.scaleLinear()
    .domain([360, 0])
    .range([padding, w - padding + 16]);

var yScale = d3.scaleLinear()
    .domain([-100, 100])
    .range([h - padding, padding_up]);

} else {
var xScale = d3.scaleLinear()
    .domain([0, 360])
    .range([padding, w - padding + 16]);

var yScale = d3.scaleLinear()
    .domain([-80, 80])
    .range([h - padding, padding_up]);
}

var svg = d3.select('.meeus')
    .append('svg')
    //.style("background", "#292e35")
    .attr('width', w)
    .attr('height', h);

var line = d3.line() 
    .x(d => xScale(d.x))
    .y(d => yScale(d.y))
    .curve(d3.curveBasisOpen());

var xAxis = d3.axisBottom(xScale)
    .ticks(9)
    .tickSize(4)
    .tickPadding(3)
    .tickFormat(function(d) { return d + "°";})
    .tickValues([0, 45, 90, 135, 180, 225, 270, 315, 360]);

if (latitude < 0.0) {
var yAxis = d3.axisLeft(yScale)
    .ticks(9)
    .tickSize(4)
    .tickPadding(2)
    .tickFormat(function(d) { return d + "°";})
    .tickValues([-100, -80, -60, -40, -20, 0, 20, 40, 60, 80, 100]);
} else {
var yAxis = d3.axisLeft(yScale)
    .ticks(9)
    .tickSize(4)
    .tickPadding(2)
    .tickFormat(function(d) { return d + "°";})
    .tickValues([-80, -60, -40, -20, 0, 20, 40, 60, 80]);  
}

  svg
    .append('g')
    .attr("class", "x axis")
    .attr('transform', 'translate(0,' + (h - padding) + ')')
    .call(xAxis);

  svg
    .append('g')
    .attr("class", "x axis")
    .attr('transform', 'translate(' + padding + ',0)')
    .call(yAxis);

  svg
    .selectAll(".horiz.line")
    .data(sunData)
    .enter()
    .append('line')
    .attr("class", "horiz line")
    .attr("x1", xScale(0))
    .attr("y1", yScale(0))
    .attr("x2", xScale(360))
    .attr("y2", yScale(0));

if (latitude < 0.0) {
  svg
    .selectAll(".zen.line")
    .data(sunData)
    .enter()
    .append('line')
    .attr("class", "zen line")
    .attr("x1", xScale(180))
    .attr("y1", yScale(100))
    .attr("x2", xScale(180))
    .attr("y2", yScale(-100));
} else {
  svg
    .selectAll(".zen.line")
    .data(sunData)
    .enter()
    .append('line')
    .attr("class", "zen line")
    .attr("x1", xScale(180))
    .attr("y1", yScale(80))
    .attr("x2", xScale(180))
    .attr("y2", yScale(-80));
}

var defs = svg.append("defs");

var moonGradient = defs.append("radialGradient")
    .attr("id", "moonGradient")
    .attr("cx", "50%")
    .attr("cy", "50%")
    .attr("r", "50%")
    .attr("fx", "50%")
    .attr("fy", "50%");

moonGradient.append("stop")
    .attr("offset", "0%")
    .style("stop-color", innerColor);

moonGradient.append("stop")
    .attr("offset", "90%")
    .style("stop-color", "#555");

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
    .style("stop-color", "tomato");

var sunazi = toDegrees(azi);
var sunalt = toDegrees(alt);

for (var i = 0; i < total; i++) {

var sun_azitab = nan(azitab[i]);
var sun_althrtab = althrtab[i];
  
  svg
    .append("line")
    .attr("x1", xScale(sun_azitab))
    .attr("y1", yScale(sun_althrtab))
    .attr("x2", xScale(sun_azitab))
    .attr("y2", yScale(sun_althrtab))
    .style("stroke-width", 1)
    .style("stroke", "rgba(255,99,71,1)")
    .style("stroke-linecap", "round")
    .attr("transform", "translate(0, 0)");

if (latitude < 0.0) {
if (sunazi < 180.0) {
 svg
    .append("circle")
    .style("fill", "url(#sunGradient)")
    .attr("r", 8)
    .attr("cx", xScale(180 - sunazi))
    .attr("cy", yScale(sunalt))
    .attr("transform", "translate(0, 0)")
    .on("mouseover", sMouseOver)
    .on("mousemove", sMouseMove)
    .on("mouseout", sMouseOut);
} else {
  svg
    .append("circle")
    .style("fill", "url(#sunGradient)")
    .attr("r", 8)
    .attr("cx", xScale(360 + 180 - sunazi))
    .attr("cy", yScale(sunalt))
    .attr("transform", "translate(0, 0)")
    .on("mouseover", sMouseOver)
    .on("mousemove", sMouseMove)
    .on("mouseout", sMouseOut);
  }
} else {
  svg
    .append("circle")
    .style("fill", "url(#sunGradient)")
    .attr("r", 8)
    .attr("cx", xScale(sunazi))
    .attr("cy", yScale(sunalt))
    .attr("transform", "translate(0, 0)")
    .on("mouseover", sMouseOver)
    .on("mousemove", sMouseMove)
    .on("mouseout", sMouseOut);
  }    
}
 

var moonazi = azimuth;
var moonalt = a;
  
for (var i = 0; i < total; i++) {

var moon_azitabx = nan(azitabx[i]);
var moon_althrtabx = althrtabx[i];

  svg
    .append("line")
    .attr("x1", xScale(moon_azitabx))
    .attr("y1", yScale(moon_althrtabx))
    .attr("x2", xScale(moon_azitabx))
    .attr("y2", yScale(moon_althrtabx))
    .style("stroke-width", 1)
    .style("stroke", "silver")
    .style("stroke-linecap", "round")
    .attr("transform", "translate(0, 0)");

if (latitude < 0.0) {
if (moonazi < 180.0) {
  svg
    .append("circle")
    .style("fill", "url(#moonGradient)")
    .attr("r", 5)
    .attr("cx", xScale(180 - moonazi))
    .attr("cy", yScale(moonalt))
    .attr("transform", "translate(0, 0)")
    .on("mouseover", mMouseOver)
    .on("mousemove", mMouseMove)
    .on("mouseout", mMouseOut);
} else {
  svg
    .append("circle")
    .style("fill", "url(#moonGradient)")
    .attr("r", 5)
    .attr("cx", xScale(360 + 180 - moonazi))
    .attr("cy", yScale(moonalt))
    .attr("transform", "translate(0, 0)")
    .on("mouseover", mMouseOver)
    .on("mousemove", mMouseMove)
    .on("mouseout", mMouseOut);
  }
} else {
  svg
    .append("circle")
    .style("fill", "url(#moonGradient)")
    .attr("r", 5)
    .attr("cx", xScale(moonazi))
    .attr("cy", yScale(moonalt))
    .attr("transform", "translate(0, 0)")
    .on("mouseover", mMouseOver)
    .on("mousemove", mMouseMove)
    .on("mouseout", mMouseOut);
  } 
}

var zenith = "Zenith";
  svg
    .append("text")
    .attr("x", 307.5)
    .attr("y", 5)
    .text(zenith);

var horizon = "Horizon";
  svg
    .append("text")
    .attr("x", 30)
    .attr("y", 148)
    .text(horizon);

var tooltipm = d3.select(".meeus")
    .append("div")
    .style("position", "absolute")
    .style("visibility", "hidden")
    .style("font-family", "Helvetica")
    .style("color", "silver")
    .style("font-size", "7.5px")
    .style("background-color", "#292e35")
    .style("border", "solid silver")
    .style("border-width", "1px")
    .style("border-radius", "5px")
    .style("padding", "10px")
    .style("box-shadow", "2px 2px 20px")
    .style("opacity", "0.9")
    .attr("id", "tooltip");

  function mMouseOver (event, d) {
       d3.select(this);
       tooltipm.style("visibility", "visible");
  };
  
  function mMouseMove (event, d) {
      tooltipm
        .style("top", (event.pageY)+"px").style("left",(event.pageX)+"px")
        .html("Moon Azimuth: " + moonazi.toFixed(2) + "°" + "<br>" + "Moon Altitude: " + moonalt.toFixed(2) + "°");
  };

  function mMouseOut (event, d) {
       d3.select(this);
       tooltipm.style("visibility", "hidden");
  };

var tooltips = d3.select(".meeus")
    .append("div")
    .style("position", "absolute")
    .style("visibility", "hidden")
    .style("font-family", "Helvetica")
    .style("color", "tomato")
    .style("font-size", "7.5px")
    .style("background-color", "#292e35")
    .style("border", "solid tomato")
    .style("border-width", "1px")
    .style("border-radius", "5px")
    .style("padding", "10px")
    .style("box-shadow", "2px 2px 20px")
    .style("opacity", "0.9")
    .attr("id", "tooltip");

  function sMouseOver (event, d) {
       d3.select(this);
       tooltips.style("visibility", "visible");
  };
  
  function sMouseMove (event, d) {
      tooltips
        .style("top", (event.pageY)+"px").style("left",(event.pageX)+"px")
        .html("Sun Azimuth: " + sunazi.toFixed(2) + "°" + "<br>" + "Sun Altitude: " + sunalt.toFixed(2) + "°");
  };

  function sMouseOut (event, d) {
       d3.select(this);
       tooltips.style("visibility", "hidden");
  };

// End Chart

// The complete text output from the Meeus calculation starts here
var svg = d3.select('.output')
    .append('svg')
    //.style("background", "#292e35")
    .attr('width', 640)
    .attr('height', 245);

var loc = "Δ Location: ";
  svg
    .append("text")
    .style("fill", "#2e8b57")
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .style("font-weight", "bold")
    .attr("x", 25)
    .attr("y", 8)
    .text(loc);

var city = "Station: <?php echo $stationlocation;?>";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 25)
    .attr("y", 17)
    .text(city);

var tzone = "Timezone: " + TZ;
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 25)
    .attr("y", 25)
    .text(tzone);

var lat = "Latitude: " + latitude + "°";
  svg
    .append("text")
    .style("fill", "#2e8b57")
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .style("font-weight", "bold")
    .attr("x", 25)
    .attr("y", 34)
    .text(lat);

var lon = "Longitude: " + longitude + "°";
  svg
    .append("text")
    .style("fill", "#2e8b57")
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .style("font-weight", "bold")
    .attr("x", 25)
    .attr("y", 43)
    .text(lon);

var time = "Time | Date:";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .style("font-weight", "bold")
    .attr("x", 25)
    .attr("y", 52)
    .text(time);

var utcdate = "UTC - Date: " + dayCurrent + "." + monthCurrent + "." + yearCurrent;
  svg
    .append('text')
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 25)
    .attr("y", 61)
    .text(utcdate);

var utctime = "UTC - Time: " + skynet(hourCurrent) + ":" + skynet(minuteCurrent) + ":" + skynet(secondCurrent) + "." + millisecondCurrent;
  svg
    .append('text')
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 25)
    .attr("y", 70)
    .text(utctime);

var tzoffset = "Timezone Offset: " + timezoneOffset;
  svg
    .append('text')
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 25)
    .attr("y", 79)
    .text(tzoffset);

var utcstring = "UTC - String: " + UTCString;
  svg
    .append('text')
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 25)
    .attr("y", 88)
    .text(utcstring);

var decimaldate = "Decimal - Date: " + dayJulian + "-" + monthJulian+ "-" + yearJulian;
  svg
    .append('text')
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 25)
    .attr("y", 97)
    .text(decimaldate);

var juliandecimaldate = "Julian Decimal Date: " + JD;
  svg
    .append('text')
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 25)
    .attr("y", 106)
    .text(juliandecimaldate);

var adoptedepoch = "Adopted Epoch: " + epochDays;
  svg
    .append('text')
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 25)
    .attr("y", 115)
    .text(adoptedepoch);

var dayssinceepoch = "Days Since Epoch: " + daysSinceEpoch;
svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 25)
    .attr("y", 124)
    .text(dayssinceepoch);

var utdecimal = "UT - Decimal: " + UTDecimal;
  svg
    .append('text')
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 25)
    .attr("y", 133)
    .text(utdecimal);

var jdAt0h = "JDAt0h: " + JDAt0h;
  svg
    .append('text')
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 25)
    .attr("y", 142)
    .text(jdAt0h);

var valuet = "T: " + valueT;
  svg
    .append('text')
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 25)
    .attr("y", 151)
    .text(valuet);

var valuet0 = "T0: " + valueT0;
  svg
    .append('text')
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 25)
    .attr("y", 160)
    .text(valuet0);

var gst = "GMST: " + GST + "°";
  svg
    .append('text')
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 25)
    .attr("y", 169)
    .text(gst);

var gsttime = "GMST - Time: " + GSTHours + "h" + " " + GSTMinutes + "m" + " " + GSTSeconds + "/ms";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 25)
    .attr("y", 178)
    .text(gsttime);

var horizoncoordinates = "Horizon Coordinates:";
  svg
    .append("text")
    .style("fill", "#007fff")
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .style("font-weight", "bold")
    .attr("x", 25)
    .attr("y", 195)
    .text(horizoncoordinates);

var lst = "LMST: " + LST + "°";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 25)
    .attr("y", 204)
    .text(lst); 

var lsttime = "LMST - Time: " + LSTHours + "h" + " " + LSTMinutes + "m" + " " + LSTSeconds + "/ms";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 25)
    .attr("y", 213)
    .text(lsttime);

var h = "Hα: " + hourAngle + "°";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 25)
    .attr("y", 222)
    .text(h);

var htime = "Hα-time: " + HHours + "h" + " " + HMinutes + "m" + " " + HSeconds + "/ms";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 25)
    .attr("y", 231)
    .text(htime);

var sunposition = "Sun Position: ";
  svg
    .append("text")
    .style("fill", "tomato")
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .style("font-weight", "bold")
    .attr("x", 236.5)
    .attr("y", 8)
    .text(sunposition);

var valuen = "N: " + valueN + "°";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 236.5)
    .attr("y", 17)
    .text(valuen);

var msun = "MSun: " + MSun + "°";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 236.5)
    .attr("y", 26)
    .text(msun);

var maxe = "Ec: " + Ec + "°";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 236.5)
    .attr("y", 35)
    .text(maxe);

var ra = "Ra λSun: " + toDegrees(Ra) + "°";
  svg
    .append("text")
    .style("fill", "#007fff")
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .style("font-weight", "bold")
    .attr("x", 236.5)
    .attr("y", 44)
    .text(ra);

var esun = "Ecliptic εSun: " + εSun + "°";
  svg
    .append("text")
    .style("fill", "tomato")
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .style("font-weight", "bold")
    .attr("x", 236.5)
    .attr("y", 53)
    .text(esun);

var sunDec = "Dec δSun: " + δSun + "°";
  svg
    .append("text")
    .style("fill", "#007fff")
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .style("font-weight", "bold")
    .attr("x", 236.5)
    .attr("y", 62)
    .text(sunDec);

var ysun = "YSun: " + YSun;
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 236.5)
    .attr("y", 71)
    .text(ysun);

var xsun = "XSun: " + XSun;
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 236.5)
    .attr("y", 80)
    .text(xsun);       

var loquadrant = "LO-Quadrant: " + LOQuadrant + "°";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 236.5)
    .attr("y", 89)
    .text(loquadrant);

var hiquadrant = "HI-Quadrant: " + HIQuadrant + "°";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 236.5)
    .attr("y", 98)
    .text(hiquadrant);

var asun = "αSun: " + αSun + "°";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 236.5)
    .attr("y", 107)
    .text(asun);

var asuntime = "αSun - Time: " + αSunHours + "h" + " " + αSunMinutes + "m" + " " + αSunSeconds + "/ms";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 236.5)
    .attr("y", 116)
    .text(asuntime);

var asuntime = "δSun - Time: " + δSunHours + "h" + " " + δSunMinutes + "m" + " " + δSunSeconds + "/ms";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 236.5)
    .attr("y", 125)
    .text(asuntime);

var sunazimuth = "Sun Azimuth (Decimal): " + sunazi + "°";
  svg
    .append("text")
    .style("fill", "tomato")
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .style("font-weight", "bold")
    .attr("x", 236.5)
    .attr("y", 134)
    .text(sunazimuth);

var sunaltitude = "Sun Altitude (Decimal): " + sunalt + "°";
  svg
    .append("text")
    .style("fill", "tomato")
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .style("font-weight", "bold")
    .attr("x", 236.5)
    .attr("y", 143)
    .text(sunaltitude);

var sunazimuthdeg = "Sun Azimuth (Dms): " + SHours + "°" + SMinutes + "'" + SSeconds + "/ms";
  svg
    .append("text")
    .style("fill", "tomato")
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 236.5)
    .attr("y", 152)
    .text(sunazimuthdeg);

var sunaltitudedeg = "Sun Altitude (Dms): " + sHours + "°" + sMinutes + "'" + sSeconds + "/ms";
  svg
    .append("text")
    .style("fill", "tomato")
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 236.5)
    .attr("y", 161)
    .text(sunaltitudedeg);

var sundistance = "Sun Distance: " + d3.format(",.3f")(Rs) + " km";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 236.5)
    .attr("y", 170)
    .text(sundistance);

 var moonposition = "Moon Position:";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .style("font-weight", "bold")
    .attr("x", 448)
    .attr("y", 8)
    .text(moonposition);

var lmoon = "lMoon: " + lMoon + "°";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 448)
    .attr("y", 17)
    .text(lmoon);

var mm = "Mm: " + Mm + "°";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 448)
    .attr("y", 26)
    .text(mm);

var nmoon = "NMoon: " + NMoon + "°";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 448)
    .attr("y", 35)
    .text(nmoon);

var ev = "Ev: " + Ev;
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 448)
    .attr("y", 44)
    .text(ev);

var ae = "Ae: " + Ae;
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 448)
    .attr("y", 53)
    .text(ae);

var a3 = "A3: " + A3;
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 448)
    .attr("y", 62)
    .text(a3);

var mmx = "M'm: " + MPrimem + "°";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 448)
    .attr("y", 71)
    .text(mmx);

var ecx = "Ec: " + Ec + "°";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 448)
    .attr("y", 80)
    .text(ecx);

var a4 = "A4: " + A4;
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 448)
    .attr("y", 89)
    .text(a4);

var lp = "lPrime: " + lPrime + "°";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 448)
    .attr("y", 98)
    .text(lp);

var np = "NPrime: " + NPrime + "°";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 448)
    .attr("y", 107)
    .text(np);

var ramoon = "Ra λMoon: " + λMoon + "°";
  svg
    .append("text")
    .style("fill", "#007fff")
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .style("font-weight", "bold")
    .attr("x", 448)
    .attr("y", 116)
    .text(ramoon);

var bmoon = "βMoon: " + βMoon + "°";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 448)
    .attr("y", 125)
    .text(bmoon);

var decmoon = "Dec δMoon: " + δMoon + "°";
  svg
    .append("text")
    .style("fill", "#007fff")
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .style("font-weight", "bold")
    .attr("x", 448)
    .attr("y", 134)
    .text(decmoon);

var valueymoon = "YMoon: " + valueYMoon;
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 448)
    .attr("y", 143)
    .text(valueymoon);

var valuexmoon = "XMoon: " + valueXMoon;
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 448)
    .attr("y", 152)
    .text(valuexmoon);

var loquadrantMn = "LO-Quadrant: " + LOQuadrantMn + "°";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 448)
    .attr("y", 161)
    .text(loquadrantMn);

var hiquadrantMn = "HI-Quadrant: " + HIQuadrantMn + "°";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 448)
    .attr("y", 170)
    .text(hiquadrantMn);

var amoon = "αMoon: " + αMoon + "°";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 448)
    .attr("y", 179)
    .text(amoon);

var amoontime = "αMoon - Time: " + αMoonHours + "h" + " " + αMoonMinutes + "m" + " " + αMoonSeconds + "/ms";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 448)
    .attr("y", 188)
    .text(amoontime);

var decmoontime = "δMoon - Time: " + δMoonHours + "h" + " " + δMoonMinutes + "m" + " " + δMoonSeconds + "/ms";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 448)
    .attr("y", 197)
    .text(decmoontime);

var moonazimuth = "Moon Azimuth (Decimal): " + moonazi + "°";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .style("font-weight", "bold")
    .attr("x", 448)
    .attr("y", 206)
    .text(moonazimuth);

var moonaltitude = "Moon Altitude (Decimal): "  + moonalt + "°";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .style("font-weight", "bold")
    .attr("x", 448)
    .attr("y", 215)
    .text(moonaltitude);

var moonazimuthdeg = "Moon Azimuth (Dms): " + AHours + "º" + AMinutes + "'" + ASeconds + "/ms";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 448)
    .attr("y", 224)
    .text(moonazimuthdeg);

var moonaltitudedeg = "Moon Altitude (Dms): " + aHours + "º" + aMinutes + "'" + aSeconds + "/ms";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 448)
    .attr("y", 233)
    .text(moonaltitudedeg);

var moonp = "Moonphase:";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .style("font-weight", "bold")
    .attr("x", 236.5)
    .attr("y", 187)
    .text(moonp);

var moondistance = "Moon Distance: " + d3.format(",.3f")(Rm) + " km";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 236.5)
    .attr("y", 196)
    .text(moondistance);

var mdays = "Moon Age: " + m_days + " Days";
  svg
    .append("text")
    .style("fill", tcolor)
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 236.5)
    .attr("y", 205)
    .text(mdays);

var frac = "Illumination: " + perc + " %";
  svg
    .append("text")
    .style("fill", tcolor)    
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 236.5)
    .attr("y", 214)
    .text(frac);

var mphase = "Moonphase: " + moonphase;
  svg
    .append("text")
    .style("fill", tcolor)    
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 236.5)
    .attr("y", 223)
    .text(mphase);

var mphaseA = ["Moonphase Angle: " + MphaseAngle + "°"];
  svg
    .append("text")
    .style("fill", tcolor)    
    .style("font-family", "Helvetica")
    .style("font-size", "7.5px")
    .attr("x", 236.5)
    .attr("y", 232)
    .text(mphaseA)
// --end text output--

// create an svg moonphase graphic
var svg = d3.select(".moonphaze")
    .append("svg")
    //.style("background", "#292e35")
    .attr("width", 100)
    .attr("height", 100);

var wi = 100;
var hi = 100;
var radx = wi / 2 * 0.51;
var gradient = 2;
var mAxis = <?php echo $alm["parallacticAngle"];?>;

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
        .attr('stop-opacity', 0.1);

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
        .attr("cx", 50)
        .attr("cy", 50)
        .attr("r", 24.75)
        .style('stroke', "#9bf")
        .style('fill', "#9bf");

  drawPhase(phase);    
}

phase = MphaseAngle;
display(phase);
// wow ! job done enjoy :-)
</script>
</body>
</html> 