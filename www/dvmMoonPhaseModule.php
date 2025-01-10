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
header('Content-type: text/html; charset=utf-8');
$meteor_default="No Meteor Showers";
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 1, 1),"event_title"=>"Quadrantids","event_end"=>mktime(23, 59, 59, 1, 2),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 1, 3),"event_title"=>"Quadrantids peak","event_end"=>mktime(23, 59, 59, 1, 4),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 1, 5),"event_title"=>"Quadrantids","event_end"=>mktime(23, 59, 59, 1, 12),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 4, 9),"event_title"=>"Approaching Lyrids","event_end"=>mktime(23, 59, 59, 4, 20),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 4, 21),"event_title"=>"Lyrids peak","event_end"=>mktime(23, 59, 59, 4, 22),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 5, 5),"event_title"=>"ETA Aquarids","event_end"=>mktime(23, 59, 59, 5, 6),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 7, 20),"event_title"=>"Approaching Delta Aquarids","event_end"=>mktime(23, 59, 59, 7, 27),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 7, 28),"event_title"=>"Delta Aquarids peak","event_end"=>mktime(23, 59, 59, 7, 29),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 8, 1),"event_title"=>"Perseids Aug 1st-24th","event_end"=>mktime(23, 59, 59, 8, 10),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 8, 11),"event_title"=>"Perseids peak","event_end"=>mktime(23, 59, 59, 8, 13),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 8, 14),"event_title"=>"Perseids passed","event_end"=>mktime(23, 59, 59, 8, 18),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 10, 7),"event_title"=>"Draconids peak","event_end"=>mktime(23, 59, 59, 10, 7),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 10, 20),"event_title"=>"Orionids peak","event_end"=>mktime(23, 59, 59, 10, 21),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 11, 4),"event_title"=>"South Taurids peak","event_end"=>mktime(23, 59, 59, 11, 5),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 11, 11),"event_title"=>"North Taurids peak","event_end"=>mktime(23, 59, 59, 11, 11),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 11, 17),"event_title"=>"Leonids peak","event_end"=>mktime(23, 59, 59, 11, 18),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 12, 13),"event_title"=>"Geminids peak","event_end"=>mktime(23, 59, 59, 12, 14),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 12, 17),"event_title"=>"Ursids 17th-25th","event_end"=>mktime(23, 59, 59, 12, 20),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 12, 21),"event_title"=>"Ursids peak","event_end"=>mktime(23, 59, 59, 12, 22),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 12, 23),"event_title"=>"Ursids 17th-25th","event_end"=>mktime(23, 59, 59, 12, 25),);
$meteorNow = time();
$meteorOP = false;
foreach ($meteor_events as $meteor_check) {
    if ($meteor_check["event_start"] <= $meteorNow && $meteorNow <= $meteor_check["event_end"]) {
        $meteorOP = true;
        $meteor_default = $meteor_check["event_title"];
    }
};?>
<!DOCTYPE html>
<html lang="en">
<head> 
<meta charset="utf-8">
<title>moon phase</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<div class="chartforecast">
<span class="yearpopup"><a alt="Moon Info" title="Moon Info" href="dvmMenuCelestialPopup.php" data-lity><?php echo $info;?> Celestial Data</a></span>
</div>
<span class='moduletitle'><?php echo $lang['moonPhaseModule'];?></span>
<div class="updatedtime1"><span>
<?php if(file_exists($livedata)&&time() - filemtime($livedata)>300) echo $offline. '<offline> Offline </offline>'; else echo $online." ".$divum["time"];?>
</span></div>

<script src="js/d3.7.9.0.min.js"></script>

<div class="moonphasemoduleposition">
<div class="moonrise1">
<svg id="divumwx moon rise" viewBox="0 0 32 32" width="6" height="6" fill="none" stroke="#01a4b5" stroke-linecap="round" stroke-linejoin="round" stroke-width="10%">    
<path d="M30 20 L16 8 2 20" /></svg>
 <?php echo $lang['Moon'];?> <br /><?php  echo 'Rise<blueu> ' .$alm["moonrise"].'</blueu>';?>

<div class="moonmodulepos">
<div class="moonphasexx"></div>
</div></div>

<div class="fullmoon1">
<svg id="divumwx full moon" viewBox="0 0 32 32" width="6" height="6" fill="#aaa" stroke="#aaa" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%"><circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg>
<?php echo $lang['Nextfullmoon'];?> <br /> <div class="nextfullmoon"><value><moonm>
<?php echo $alm["fullmoon"] ;?></value></div>
</span>
</div>

<div class="newmoon1">
<svg id="divumwx new moon" viewBox="0 0 32 32" width="6" height="6" fill="#777" stroke="#777" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%"><circle cx="16" cy="16" r="14" /> <path d="M6 6 L26 26" /></svg>
<?php echo $lang['Nextnewmoon'];?> <div class="nextnewmoon"><value><moonm>
<?php echo $alm["newmoon"] ;?></value></div>
</span>
</div>

<div class="moonset1">
<svg id="divumwx moon set" viewBox="0 0 32 32" width="6" height="6" fill="none" stroke="#f26c4f" stroke-linecap="round" stroke-linejoin="round" stroke-width="10%">
<path d="M30 12 L16 24 2 12" /></svg>
<?php echo $lang['Moon']?><div class="nextnewmoon">
<?php echo 'Set<maxred> '.$alm["moonset"].'</maxred>' ;?></span> 

</div></div>
<div class="meteorshower"><svg xmlns='http://www.w3.org/2000/svg' width='10px' height='10px' viewBox='0 0 16 16'><path fill='currentcolor' d='M0 0l14.527 13.615s.274.382-.081.764c-.355.382-.82.055-.82.055L0 0zm4.315 1.364l11.277 10.368s.274.382-.081.764c-.355.382-.82.055-.82.055L4.315 1.364zm-3.032 2.92l11.278 10.368s.273.382-.082.764c-.355.382-.819.054-.819.054L1.283 4.284zm6.679-1.747l7.88 7.244s.19.267-.058.534-.572.038-.572.038l-7.25-7.816zm-5.68 5.13l7.88 7.244s.19.266-.058.533-.572.038-.572.038l-7.25-7.815zm9.406-3.438l3.597 3.285s.094.125-.029.25c-.122.125-.283.018-.283.018L11.688 4.23zm-7.592 7.04l3.597 3.285s.095.125-.028.25-.283.018-.283.018l-3.286-3.553z'/></svg>
<?php echo $meteor_default;?>
</div>
<?php echo'<div class="divumwxmoonphasem2">Moon Phase<br>'.$alm["moonphase"].'</div>
<div class="divumwxluminancem2">Illumination<br>'.number_format($alm["luminance"],2).' %</div>';?>

<script>

var moon = "<?php echo $moon;?>";

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
// Illumination in %
// var perc = value[3] * 100;
// Sun distance in km
// var Rs = value[4] * 1.496e+8;
    
var svg = d3.select(".distance")
    .append("svg")
    //.style("background", "#292e35")
    .attr("width", 120)
    .attr("height", 20);

var data = ["Distance " + "-" + d3.format(",.2f")(Rm) + " " + "-" + "km"];

var text = svg.selectAll(null)
    .data(data)
    .enter() 
    .append("text")
    .attr("x", 60)
    .attr("y", function(d, i) { return 10 + i * 10; })

    .style("fill", "var(--col-6)")
    .style("font-family", "Helvetica") 
    .style("font-size", "8.5px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[0]; })

    .append("tspan")
    .style("fill", "#ff7c39")
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[1]; })

    .append("tspan")
    .style("fill", "var(--col-6)")
    .style("font-weight", "normal")
    .text(function(d) { return d.split("-")[2]; });


var svg = d3.select(".moonphasexx")
    .append("svg")
    //.style("background", "#292e35")
    .attr("width", 110)
    .attr("height", 110);

var wi = 110;
var hi = 110;
var radx = wi / 2 * 0.91;
var gradient = 2;
var mAxis = <?php echo $alm["parallacticAngle"];?>; // 5.145396; // Inclination of Moon's orbit

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
    }
}

function drawPhase(phase) {
    svg.attr("transform", "rotate("+ mAxis +", 0, 0)")
    .style('fill', 'rgba(41, 46, 53, 0.8)');
    const gradientPoints = drawDarkSide(phase);
    fillGradient(gradientPoints);

}

function display(phase) {

    svg
        .append("circle") // background circle
        .attr("cx", 55)
        .attr("cy", 55)
        .attr("r", 49.25)
        .style('stroke', "#99bbff")
        .style('fill', "#99bbff");

  drawPhase(phase);    
}

phase = <?php echo $alm["moon_ecliptic_angle"];?>;
display(phase);

</script>
<div class="distance"></div>
</body>
</html>