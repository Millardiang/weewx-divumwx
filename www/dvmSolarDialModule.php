<?php
include('dvmCombinedData.php');
date_default_timezone_set($TZ);
header('Content-type: text/html; charset=utf-8');
?>
<style>
.wrap {
  position: relative;
  margin-top: -2px;
  margin-right: 0px;
}
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
  text-align: left
}
.chartforecast2:hover, {
  color: #90b12a
}
.daylightmoduleposition2 {
  position: relative;
  left: 5px;
  margin-top: 0px;
}
</style>    
<?php
$light = $alm["daylight"]; 
$daylight = ltrim($light, '0'); 
$dark = 24 - str_replace(':', '.', $alm["daylight"]);
$lighthours = substr($alm["daylight"], 0, 2); 
$lightmins = substr($alm["daylight"], - 2);
$darkhours = 23 - $lighthours; 
$darkminutes = 60 - $lightmins;
if ($darkminutes < 10) $darkminutes = '0' .$darkminutes;
else $darkminutes = $darkminutes;

if (round($sun_alt,2) >= 0) { 
$sun_elevation = round($sun_alt,2)."&deg;<div class=sunaboveweather34>&nbsp;</div>";
} else if (round($sun_alt,2) < 0) { 
$sun_elevation = round($sun_alt,2)."&deg;<div class=sunbelowweather34>&nbsp;</div>"; 
}?>
<div class="chartforecast2">
      <span class="yearpopup"><a alt="orrery" title="orrery" href="dvmOrreyPopup.php" data-lity><?php echo $info;?> Orrery</a></span>
      <span class="yearpopup"><a alt="Astroclock" title="Astroclock" href="dvmAstroclockPopup.php" data-lity><?php echo $info;?> Astroclock</a></span>
      <span class="yearpopup"><a alt="celestial" title="celestial" href="dvmMenuCelestialPopup.php" data-lity><?php echo $info;?> Celestial Data</a></span>

</div>

<span class="moduletitle2"><?php echo $lang['solarDialModule'];?></span>

<div class="updatedtime1"><?php if(file_exists($livedata)&&time() - filemtime($livedata)>300) echo $offline. '<offline> Offline </offline>'; else echo $online." ".$weather["time"];?></div>



    </div>

<div class="daylightmoduleposition2"> 
<?php echo 
'<div class="weather34sunlightday"><weather34daylightdaycircle></weather34daylightdaycircle> '.$alm["daylight"].' hrs<br>'.$lang['TotalDaylight'].'</div>
<div class="weather34sundarkday">'.$darkhours.':'.$darkminutes.' hrs <weather34darkdaycircle></weather34darkdaycircle><br>'.$lang['TotalDarkness'].'</div>
<div class="weather34sunriseday">'.$sunuphalf.''.$lang['Sunrise'].'<br><value>'.$lang["Today"].'<span><value>: '.$alm["sunrise"].'<br>First Light: (<blueu>'.$alm["civil_twilight_begin"] .'</blueu>)</div>
<div class="weather34sunsetday">'.$sundownhalf.''.$lang['Sunset'].'<br><value>'.$lang["Tonight"].'<span><value>: '.$alm["sunset"].'<br>Last Light: (<blueu>'.$alm["civil_twilight_end"].'</blueu>)</div>

<div class="daylightword"><value>Sun Azimuth<span><value><maxred> '.round($alm["sun_azimuth"],2).'°</maxred></value></span></div>

<div class="elevationword"><value>Sun Elevation<span><value><maxred> '.$alm["sun_altitude"].'°</maxred></value></span></div>

<div class="sundialcontainerdiv2" ><div id="sundialcontainer" class=sundialcontainer><div class="suncanvasstyle"></div></div>

<div class="weather34moonphasem"><value>'.$lang["Moonphase"].'<span><value><br>'.$alm["moonphase"].'<br>'.$lang['Moonrise'].'<br>'.'<blueu> '.$alm["moonrise"].'</blueu></div>
<div class="weather34luminancem"><value>'.$lang["Luminance"].'<span><value><br> '.$alm["luminance"].' %<br>'.$lang['Moonset'].'<br>'.'<maxred> '.$alm["moonset"].'</maxred></div>';?>

<html>

<script src="js/two.js"></script>
    


<div style="overflow: hidden">
<div class="wrap">
<div id="sundial" width="130" height="130"></div>
</div></div>

<script>
function toDegrees(x) {
  return x * (180.0 / Math.PI);
}

function toRadians(x) {
  return x * (Math.PI / 180.0);
}
				                 
    var sr = "<?php echo $alm["sunrise"];?>"; // string
    
	var srarr = sr.split(":");
	var srhour = parseInt(srarr[0]);
	var srmin = parseInt(srarr[1]);
	var srmins = 360.0 / 60.0 * srmin;
	var sunrise = (360.0 / 24.0 * srhour + srmins / 24.0);
	
	var smt = "<?php echo $alm["sun_meridian_transit"];?>"; // string

	var smtarr = smt.split(":");
	var smthour = parseInt(smtarr[0]);
	var smtmin = parseInt(smtarr[1]);
	var smtmins = 360.0 / 60.0 * smtmin;
	var sun_meridian_transit = (360.0 / 24.0 * smthour + smtmins / 24.0);
    	
	var ss = "<?php echo $alm["sunset"];?>"; // string
	
	var ssarr = ss.split(":");
	var sshour = parseInt(ssarr[0]);
	var ssmin = parseInt(ssarr[1]);
	var ssmins = 360.0 / 60.0 * ssmin;
	var sunset = (360.0 / 24.0 * sshour + ssmins / 24.0);
	
</script>

<script>

var hour_moon = <?php echo $alm["hour_moon"];?>; // correct value
        
var hourMoon = toDegrees(hour_moon); // convert to degress
    
var hours_arc = <?php echo $alm["hour_sun"];?>; // correct value
	    
var Altitude = <?php echo $alm["sun_altitude"];?>; // correct value
    
var canvasSize = 130;

// Create an instance of Two.js
var skynet = document.getElementById("sundial");

var params = { width: canvasSize, 
			   height: canvasSize,
			   fullscreen: false,
			   autostart: true,
			   type: Two.Types.svg };
var two = new Two(params).appendTo(skynet);

var theme = "<?php echo $theme;?>";

	if (theme == 'dark') {
	
	var BGRing = two.makeCircle(0, 0, 52.5, 0, 2 * Math.PI);
	
	BGRing.stroke = "rgba(59, 60, 63, 1)";
	BGRing.linewidth = 7;
	BGRing.noFill();
		
 	if (Altitude > 0.0) {
 	
 	function updateTimer1() {
 	
 	future1 = Date.parse("<?php echo $alm["sunset_date"];?>"); // string
 	now = Date.parse("<?php echo $sundial_time;?>"); // string
 	diff = future1 - now;

 	days = Math.floor(diff / (1000 * 60 * 60 * 24));
 	hours = Math.floor(diff / (1000 * 60 * 60));
 	mins = Math.floor(diff / (1000 * 60));
 	secs = Math.floor(diff / 1000);

 	d = days;
 	h = hours - days * 24;
 	m = mins - hours * 60;
 	s = secs - mins * 60;
	
 	var SunSet = "Sun Set";
	var sunsc = new Two.Text(SunSet, 0, -10);
  	sunsc.size = 10;
  	sunsc.weight = "normal";
  	sunsc.family = "Helvetica";
  	sunsc.fill = "silver"; // gray
  	sunsc.alignment = "center";
  	
  	var CountDown = h + " hrs " + m + " mins";
	var countdown = new Two.Text(CountDown, 0, 5);
  	countdown.size = 8;
  	countdown.weight = "normal";
  	countdown.family = "Helvetica";
  	countdown.fill = "silver"; // gray
  	countdown.alignment = "center";
  	
  	two.scene.add(countdown, sunsc);
  	
  	}
  	updateTimer1();
  	
  	} else {
  	
  	function updateTimer2() {
  	
  	future2 = Date.parse("<?php echo $alm["sunrise_date"];?>"); // string
 	now = Date.parse("<?php echo $sundial_time;?>"); // string
 	diff = future2 - now;

 	days = Math.floor(diff / (1000 * 60 * 60 * 24));
 	hours = Math.floor(diff / (1000 * 60 * 60));
 	mins = Math.floor(diff / (1000 * 60));
 	secs = Math.floor(diff / 1000);

 	d = days;
 	h = hours - days * 24;
 	m = mins - hours * 60;
 	s = secs - mins * 60;
  	
  	var SunRise = "Sun Rise";
	var sunrc = new Two.Text(SunRise, 0, -10);
  	sunrc.size = 10;
  	sunrc.weight = "normal";
  	sunrc.family = "Helvetica";
  	sunrc.fill = "silver"; // gray
  	sunrc.alignment = "center";
  	
 	var CountUp = h + " hrs " + m + " mins";
	var countup = new Two.Text(CountUp, 0, 5);
  	countup.size = 8;
  	countup.weight = "normal";
  	countup.family = "Helvetica";
  	countup.fill = "silver"; // gray
  	countup.alignment = "center";
  	
  	two.scene.add(countup, sunrc);
  	}
  	updateTimer2();
}


} else {

	var BGRing = two.makeCircle(0, 0, 52.5, 0, 2 * Math.PI);
	
	BGRing.stroke = "rgba(230, 232, 239, 1)";
	BGRing.linewidth = 7;
	BGRing.noFill();

 	if (Altitude > 0.0) {
 	
 	function updateTimer1() {
 	
 	future1 = Date.parse("<?php echo $alm["sunset_date"];?>"); // string
 	now = Date.parse("<?php echo $sundial_time;?>"); // string
 	diff = future1 - now;

 	days = Math.floor(diff / (1000 * 60 * 60 * 24));
 	hours = Math.floor(diff / (1000 * 60 * 60));
 	mins = Math.floor(diff / (1000 * 60));
 	secs = Math.floor(diff / 1000);

 	d = days;
 	h = hours - days * 24;
 	m = mins - hours * 60;
 	s = secs - mins * 60;
	
 	var SunSet = "Sun Set";
	var sunsc = new Two.Text(SunSet, 0, -10);
  	sunsc.size = 10;
  	sunsc.weight = "normal";
  	sunsc.family = "Helvetica";
  	sunsc.fill = "silver"; // gray
  	sunsc.alignment = "center";
  	
  	var CountDown = h + " hrs " + m + " mins";
	var countdown = new Two.Text(CountDown, 0, 5);
  	countdown.size = 8;
  	countdown.weight = "normal";
  	countdown.family = "Helvetica";
  	countdown.fill = "silver"; // gray
  	countdown.alignment = "center";
  	
  	two.scene.add(countdown, sunsc);
  	
  	}
  	updateTimer1();
  	
  	} else {
  	
  	function updateTimer2() {
  	
  	future2 = Date.parse("<?php echo $alm["sunrise_date"];?>"); // string
 	now = Date.parse("<?php echo $sundial_time;?>"); // string
 	diff = future2 - now;

 	days = Math.floor(diff / (1000 * 60 * 60 * 24));
 	hours = Math.floor(diff / (1000 * 60 * 60));
 	mins = Math.floor(diff / (1000 * 60));
 	secs = Math.floor(diff / 1000);

 	d = days;
 	h = hours - days * 24;
 	m = mins - hours * 60;
 	s = secs - mins * 60;
  	
  	var SunRise = "Sun Rise";
	var sunrc = new Two.Text(SunRise, 0, -10);
  	sunrc.size = 10;
  	sunrc.weight = "normal";
  	sunrc.family = "Helvetica";
  	sunrc.fill = "silver"; // gray
  	sunrc.alignment = "center";
  	
 	var CountUp = h + " hrs " + m + " mins";
	var countup = new Two.Text(CountUp, 0, 5);
  	countup.size = 8;
  	countup.weight = "normal";
  	countup.family = "Helvetica";
  	countup.fill = "silver"; // gray
  	countup.alignment = "center";
  	
  	two.scene.add(countup, sunrc);
  	}
  	updateTimer2();
  }

}

	// center
	var xc = 0;
	var yc = 0;
		
	var arc = two.makeArcSegment(xc, yc, 52.5, 52.5, toRadians(sunset), toRadians(sunrise));
	arc.closed = false;
	arc.rotation = 1.565;
	arc.noFill();
	arc.linewidth = 1;
	arc.stroke = "#007FFF";
	arc.cap = "round";

two.bind("resize", resize);
resize();

function resize() {
  two.scene.position.set(two.width / 2, two.height / 2);
}

	/*
	// image test
	var img = new Two.Texture('img/globe.svg', function() {
    var shape = two.makeRectangle(0, 25, img.image.width, img.image.height);
    shape.noStroke().fill = img;
    //scale
    shape.scale = 0.045;
    //clone and png blending
    //var shape2 = shape.clone();
    //translate
    //shape2.translation.addSelf(new Two.Vector(125, 50));
    })
    */

var x = - 48 * Math.sin(toRadians(sunrise));
var y = + 48 * Math.cos(toRadians(sunrise));

var srx = x - 9 * Math.sin(toRadians(sunrise));
var sry = y + 9 * Math.cos(toRadians(sunrise));
var srise = two.makeLine(x, y, srx, sry);

	srise.noFill();
    srise.stroke = "rgba(255,99,71,1)"; // tomato
    srise.linewidth = 1;
    srise.cap = "round"; 	

var x = - 48 * Math.sin(toRadians(sun_meridian_transit));
var y = + 48 * Math.cos(toRadians(sun_meridian_transit));

var smtx = x - 9 * Math.sin(toRadians(sun_meridian_transit));
var smty = y + 9 * Math.cos(toRadians(sun_meridian_transit));
var sm_t = two.makeLine(x, y, smtx, smty);

	sm_t.noFill();
    sm_t.stroke = "rgba(255,99,71,1)"; // tomato
    sm_t.linewidth = 1;
    sm_t.cap = "round";


var x = - 48 * Math.sin(toRadians(sunset));
var y = + 48 * Math.cos(toRadians(sunset));

var ssx = x - 9 * Math.sin(toRadians(sunset));
var ssy = y + 9 * Math.cos(toRadians(sunset));
var sset = two.makeLine(x, y, ssx, ssy);

	sset.noFill();
    sset.stroke = "rgba(255,99,71,1)"; // tomato
    sset.linewidth = 1;
    sset.cap = "round";

// hourly Ticks inner Ring
for (let i = 0; i < 24; i++) {

  var x = - 43 * Math.sin(i / 24 * Math.PI * 2);
  var y = + 43 * Math.cos(i / 24 * Math.PI * 2);
  var Ix = x - 3 * Math.sin(i / 24 * Math.PI * 2);
  var Iy = y + 3 * Math.cos(i / 24 * Math.PI * 2);
  
  var ticks = two.makeLine(x, y, Ix, Iy);
  
  ticks.noFill();
  ticks.stroke = "silver"; // gray
  ticks.linewidth = 1.5;
  ticks.cap = "round";
}

function Moon() {
  
  	var hour_MD = toRadians(hourMoon);
				
	var Xm = xc + 52.5  * Math.sin(hour_MD);
	var Ym = yc - 52.5  * Math.cos(hour_MD);
   		
	var moonm = two.makeCircle(Xm, Ym, 3, 0, 2 * Math.PI);
	moonm.stroke = "rgba(255,255,255,1)";
  	moonm.linewidth = 1;
  	moonm.fill = "rgba(255,255,255,1)";
  	
  	var Ring = two.makeCircle(Xm, Ym, 3.5, 0, 2 * Math.PI);
	Ring.stroke = "black";
  	Ring.linewidth = 0.5;
  	Ring.noFill = "black";

}
		
Moon();

function Sun() {
    					
	var Xs = xc + 52.5 * Math.sin(hours_arc);
	var Ys = yc - 52.5 * Math.cos(hours_arc);
    	
	var suns = two.makeCircle(Xs, Ys, 5.5, 0, 2 * Math.PI);
	
	if (Altitude <= 0.5 && Altitude > - 4.0 ) {
	
		suns.stroke = "rgba(255, 112, 50, 0.5)";
		suns.linewidth = 1;
		suns.fill = "rgba(255, 112, 50, 0.5)";
		
	} else if (Altitude <= 0) {
	
		suns.stroke = "rgba(86, 95, 103, 0.7)";
		suns.linewidth = 1;
		suns.fill = "rgba(86, 95, 103, 0.7)";	
		
	} else {
	
		suns.stroke = "rgba(255,124,57,1)";
		suns.linewidth = 1;
		suns.fill = "rgba(255,124,57,1)";
  }
    	  	
}
 	
Sun();
 
two.update();

</script>


</html>