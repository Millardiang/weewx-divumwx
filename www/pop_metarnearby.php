<?php include('dvmGetMetar.php');
//weather34 original metarnearby script 201-2019//
if($theme==="light"){$background="white";$text="black";}
else if($theme==="dark"){$background="rgba(33, 34, 39, .8)";$text="white";}
$iconset = "icon2";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Weather34 Nearby Metar</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
@font-face{font-family:weathertext2;src:url(css/fonts/verbatim-regular.woff) format("woff"),url(fonts/verbatim-regular.woff2) format("woff2"),url(fonts/verbatim-regular.ttf) format("truetype")}
html,body
  {
    font-size:13px;
    font-family: "weathertext2", Helvetica, Arial, sans-serif;
    -webkit-font-smoothing: antialiased;	
    -moz-osx-font-smoothing: grayscale;
    background-color:<?php echo $background; ?>;
  }
  /* unvisited link */
a:link {
  color: <?php echo $text; ?>;
}

/* visited link */
a:visited {
  color: <?php echo $text; ?>;
}

/* mouse over link */
a:hover {
  color: <?php echo $text; ?>;
}

/* selected link */
a:active {
  color: <?php echo $text; ?>;
}
.grid { 
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 2fr));
  grid-gap: 2px;
  align-items: stretch;
  color:<?php echo $text; ?>;  
  }
.grid > article {
  border: 1px solid rgba(245, 247, 252,.02);
  box-shadow: 2px 2px 6px 0px  rgba(0,0,0,0.6);
  padding:5px;
  font-size:0.8em;
  -webkit-border-radius:4px;
  border-radius:4px;
  background:0;-webkit-font-smoothing: antialiased;	-moz-osx-font-smoothing: grayscale;
}
.grid > article img {
  max-width: 100%;
}
.weather34darkbrowser {
  position: relative;
  width: 97%;
  height: 30px;
  margin: auto;
  margin-top: -5px;
  margin-left: 0px;
  border-top-left-radius: 5px;
  border-top-right-radius: 5px;
  padding-top: 10px;
  color: <?php echo $text; ?>;
}

.weather34darkbrowser[url]:after {
  content: attr(url);
  font-size: 14px;
  text-align: center;
  position: absolute;
  left: 0;
  right: 0;
  top: 0;
  padding: 4px 15px;
  margin: 11px 10px 0 auto;
  font-family: arial;
  height: 20px;
} 
blue{color:#01a4b4}orange{color:#009bb4}orange1{position:relative;color:#009bb4;margin:0 auto;text-align:center;margin-left:5%;font-size:1.1rem}green{color:#aaa}red{color:#f37867}red6{color:#d65b4a}value{color:#fff}yellow{color:#CC0}purple{color:#916392}
.text1{font-family:weathertext2,Arial;font-size:20px;margin-left:3px;}
.windvalue1{font-family:weathertext2,Arial;font-size:20px;margin-left:3px;}
.windseparator{color:rgba(57,61,64,1)}
.text1,.windvalue1{color:#aaa}
text1{z-index:10;text-align:center;margin:5px 0 auto}

.metartempcontainer1{left:70px;top:0}
.metartempcontainer2{left:10px;top:90px;position:absolute}
.metartempcontainer3{left:85px;top:125px;position:absolute}
.metartempcontainer4{left:85px;top:76px;position:absolute}
.metartempcontainer5{left:85px;top:142px;position:absolute}
.metarwindcontainer1{margin-top:0;margin-left:5px;position:relative}
/*kts*/
.metarwindcontainer2{margin-top:10px;margin-left:0px;position:relative}
/*mph*/
.metarwindcontainer3{margin-top:-95px;margin-left:85px;position:relative}
/*ms*/
.metarwindcontainer4{margin-top:10px;margin-left:85px;position:relative}
.metarwindcontainer5{margin-top:-50px;margin-left:5px;position:relative}
.metartemptoday0,.metartemptoday5,.metartemptoday10,.metartemptoday20,.metartemptoday25,.metartemptoday30{font-family:weathertext2,Arial,Helvetica,system;width:4.5rem;height:2.5rem;-webkit-border-radius:3px;-moz-border-radius:3px;-o-border-radius:3px;display:flex}.metartemptoday0,.metartemptoday5,.metartemptoday10,.metartemptoday15,.metartemptoday20,.metartemptoday25,.metartemptoday30{font-size:1.1rem;padding-top:0;color:#fff;border-bottom:12px solid rgba(56,56,60,1);align-items:center;justify-content:center;border-radius:3px;margin-bottom:21px;}
.metartemptoday0{background:rgba(68, 166, 181, 1.000)}.metartemptoday5{background:rgba(144, 177, 42, 1.000)}.metartemptoday10{background:rgba(230, 161, 65, 1.000)}.metartemptoday20{background:rgba(255, 124, 57, 1.000)}.metartemptoday25{background:rgba(255, 124, 57, 0.7)}.metartemptoday30{background:rgba(211, 93, 78, 1.000)}.metardewcontainer1{left:70px;margin-top:10px}
.metardewtoday0,.metardewtoday5,.metardewtoday10,.metardewtoday20,.metardewtoday25,.metardewtoday30{font-family:weathertext2,Arial,Helvetica,system;width:4.5rem;height:2.5rem;-webkit-border-radius:3px;-moz-border-radius:3px;-o-border-radius:3px;display:flex}.metardewtoday0,.metardewtoday5,.metardewtoday10,.metardewtoday15,.metardewtoday20,.metardewtoday25,.metardewtoday30{font-size:1.1rem;padding-top:0;color:#fff;border-bottom:12px solid rgba(56,56,60,1);align-items:center;justify-content:center;border-radius:3px;margin-bottom:21px;}
.metardewtoday0{background:rgba(68, 166, 181, 1.000)}.metardewtoday5{background:rgba(144, 177, 42, 1.000)}.metardewtoday10{background:rgba(230, 161, 65, 1.000)}.metardewtoday20{background:rgba(255, 124, 57, 1.000)}.metardewtoday25{background:rgba(255, 124, 57, 0.7)}.metardewtoday30{background:rgba(211, 93, 78, 1.000)}
.metarhumcontainer1{position:relative;top:-100px;font-size:.7rem;z-index:1;color:#fff;margin-left:92px;display:inline-block;}
.metarhumcontainer2{left:70px;margin-top:10px}
.humword{position:relative;top:-90px;font-size:.65rem;z-index:1;color:#fff;margin-left:102px}
.metarhumtoday0-35,.metarhumtoday35-70,.metarhumtoday70-85,.metarhumtoday85-100{font-family:weathertext2,Arial,Helvetica,system;width:4.5rem;height:2.5rem;-webkit-border-radius:3px;-moz-border-radius:3px;-o-border-radius:3px;display:flex}
.metarhumtoday0-35,.metarhumtoday35-70,.metarhumtoday70-85,.metarhumtoday85-100{font-size:1.1rem;padding-top:2px;color:#fff;border-bottom:12px solid rgba(56,56,60,1);align-items:center;justify-content:center;border-radius:3px;margin-bottom:-70px;}
.metarhumtoday0-35{background:rgba(211, 93, 78, 1.000)}.metarhumtoday35-70{background:rgba(230, 161, 65, 1.000)}.metarhumtoday70-85{background:rgba(230, 161, 65, 1.000)}.metarhumtoday85-100{background:rgba(68, 166, 181, 1.000)}
.dewword,.tword{position:absolute;margin-top:-33px;font-size:.7rem;z-index:1;color:#fff}
.dewword{margin-left:8px}.tword{margin-left:20px}.tword2{position:absolute;margin-top:-32px;font-size:.65rem;z-index:1;color:#fff}
.dewword2{position:absolute;margin-top:33px;font-size:.65rem;z-index:1;color:#fff;margin-left:75px}.tword2{margin-left:70px}
.maxword{position:absolute;margin-top:-32px;font-size:.65rem;z-index:1;color:#fff}.maxword{margin-left:10px}
.windword{position:absolute;margin-top:32px;font-size:.65rem;z-index:1;color:#fff;margin-left:7px}
.metarwindtoday0,.metarwindtoday5,.metarwindtoday10,.metarwindtoday20,.metarwindtoday25,.metarwindtoday30{font-family:weathertext2,Arial,Helvetica,system;width:5rem;height:2.5rem;-webkit-border-radius:3px;-moz-border-radius:3px;-o-border-radius:3px;display:flex}
.metarwindtoday0,.metarwindtoday5,.metarwindtoday10,.metarwindtoday15,.metarwindtoday20,.metarwindtoday25,.metarwindtoday30{font-size:1.1rem;padding-top:0;color:#fff;border-bottom:10px solid rgba(56,56,60,1);align-items:center;justify-content:center;border-radius:3px;display:flex}
.metarwindtodaykts0,.metarwindtodaykts5,.metarwindtodaykts10,.metarwindtodaykts20,.metarwindtodaykts25,.metarwindtodaykts30{font-family:weathertext2,Arial,Helvetica,system;width:5rem;height:2.5rem;-webkit-border-radius:3px;-moz-border-radius:3px;-o-border-radius:3px;display:flex}
.metarwindtodaykts0,.metarwindtodaykts5,.metarwindtodaykts10,.metarwindtodaykts15,.metarwindtodaykts20,.metarwindtodaykts25,.metarwindtodaykts30{font-size:1.5rem;padding-top:0;color:#fff;border-bottom:12px solid rgba(56,56,60,1);align-items:center;justify-content:center;border-radius:3px;display:flex}
.actualt{position:relative;left:5px;-webkit-border-radius:3px;-moz-border-radius:3px;-o-border-radius:3px;border-radius:3px;background:rgba(74, 99, 111, 0.1);
padding:5px;font-family:Arial, Helvetica, sans-serif;width:100px;height:0.8em;font-size:0.8rem;padding-top:2px;color:#aaa;
align-items:center;justify-content:center;margin-bottom:10px;top:0}
.actualw{position:relative;left:5px;-webkit-border-radius:3px;-moz-border-radius:3px;-o-border-radius:3px;border-radius:3px;background:rgba(74, 99, 111, 0.1);
padding:5px;font-family:Arial, Helvetica, sans-serif;width:100px;height:0.8em;font-size:0.8rem;padding-top:2px;color:#aaa;
align-items:center;justify-content:center;margin-bottom:10px;top:0}
.metarwindtodaykts0{background:rgba(68, 166, 181, 1.000)}
.metarwindtodaykts5{background:rgba(144, 177, 42, 1.000)}
.metarwindtodaykts10{background:rgba(230, 161, 65, 1.000)}
.metarwindtodaykts20{background:rgba(255, 124, 57, 1.000)}
.metarwindtodaykts25{background:rgba(255, 124, 57, 0.7)}
.metarwindtodaykts30{background:rgba(211, 93, 78, 1.000)}
.metarwindtoday0{background:rgba(68, 166, 181, 1.000)}
.metarwindtoday5{background:rgba(144, 177, 42, 1.000)}
.metarwindtoday10{background:rgba(230, 161, 65, 1.000)}
.metarwindtoday20{background:rgba(255, 124, 57, 1.000)}
.metarwindtoday25{background:rgba(255, 124, 57, 0.7)}
.metarwindtoday30{background:rgba(211, 93, 78, 1.000)}
smalluvunit{font-size:.8rem;font-family:Arial,Helvetica,system;}
valuecalm{font-size:.8em;font-family:weathertext2;}
stationid{font-size:1.4em;font-family:weathertext2;color:#009bb4}
.hitemp,.lotemp{font-size:9px;}
.iconcondition{float:left;margin-bottom:65px;}
.icontext{position:absolute;float:left;margin-top:60px;text-align:left;}
.pressure{position:absolute;float:left;margin-top:60px;text-align:left;}
</style>
<div class="weather34darkbrowser" url="<?php echo $metar34stationname;?> Conditions"></div>
  
<main class="grid">

 <article>
  <div class=actualt style="background:teal;color:white;">&nbsp;&nbsp Current Conditions </div>
  <div class="iconcondition"><?php echo "<img rel='prefetch' src='css/svg/".$sky_icon."' width='60px'>";?></div>
  <div class="icontext"><?php  echo $sky_desc; ?> </div>
<br><br><br>
<div class="pressure">
<blue>Pressure</blue> <br>
<?php
if ($pressureunit == 'mb' || $pressureunit == 'hPa' || $pressureunit == 'kPa' ) {
	echo $metar34pressuremb ," (".$pressureunit.")";
} else {
	echo $metar34pressurehg ," (inHG)";
}
?> - 
<?php
if ($pressureunit == 'mb' || $pressureunit == 'hPa' || $pressureunit == 'kPa') {
	echo $metar34pressurehg ," (inHG)";
} else {
	echo $metar34pressuremb ," (mb)";
}
?>
<blue><br>Visibility</blue> <br>
<?php
if ($distanceunit == 'mi') {
	echo $metar34vismiles  ," (miles)";
    echo $metar34cloudbasefeet;
} else {
	echo $metar34viskm ," (km)";
}
?> - 
<?php
if ($distanceunit =='mi') {
	echo $metar34viskm  ," (km)";
} else {
	echo $metar34vismiles ," (miles)";
}
?>
</div>
  </article> 
  
  <article>       
<div class=actualt style="background:teal;color:white;">&nbsp;&nbsp Temperature </div>   

 <div class="metartempcontainer1"><?php
 if ($tempunit == 'C') {
	if ($metar34temperaturec >30) {echo '<div class=metartemptoday30>'.$metar34temperaturec."<smalluvunit> &nbsp;&deg;C";}
	else if ($metar34temperaturec >25) {echo '<div class=metartemptoday25>'.$metar34temperaturec."<smalluvunit> &nbsp;&deg;C";}
	else if ($metar34temperaturec >20) {echo '<div class=metartemptoday20>'.$metar34temperaturec."<smalluvunit> &nbsp;&deg;C";}
	else if ($metar34temperaturec >10) {echo '<div class=metartemptoday10>'.$metar34temperaturec."<smalluvunit> &nbsp;&deg;C";}
	else if ($metar34temperaturec >5) {echo '<div class=metartemptoday5>'.$metar34temperaturec."<smalluvunit> &nbsp;&deg;C";}
	else if ($metar34temperaturec >-50) {echo '<div class=metartemptoday0>'.$metar34temperaturec."<smalluvunit> &nbsp;&deg;C";}
	else if ($metar34temperaturec =='') {echo '<div class=metartemptoday0>'.$metar34temperaturec."<smalluvunit> N/A";}
 } else {
	 if ($metar34temperaturef >86) {echo '<div class=metartemptoday30>'.$metar34temperaturef."<smalluvunit> &nbsp;&deg;F";}
	else if ($metar34temperaturef >77) {echo '<div class=metartemptoday25>'.$metar34temperaturef."<smalluvunit> &nbsp;&deg;F";}
	else if ($metar34temperaturef >68) {echo '<div class=metartemptoday20>'.$metar34temperaturef."<smalluvunit> &nbsp;&deg;F";}
	else if ($metar34temperaturef >50) {echo '<div class=metartemptoday10>'.$metar34temperaturef."<smalluvunit> &nbsp;&deg;F";}
	else if ($metar34temperaturef >41) {echo '<div class=metartemptoday5>'.$metar34temperaturef."<smalluvunit> &nbsp;&deg;F";}
	else if ($metar34temperaturef >-50) {echo '<div class=metartemptoday0>'.$metar34temperaturef."<smalluvunit> &nbsp;&deg;F";}
	else if ($metar34temperaturef =='') {echo '<div class=metartemptoday0>'.$metar34temperaturef."<smalluvunit> N/A";}
 }
?></smalluvunit></div></div>
<div class="tword"><?php if ($tempunit == 'F') {echo $metar34temperaturec."&deg;C";} else if ($tempunit == 'C'){echo $metar34temperaturef."&deg;F";}?></div>
</div>
	 
<div class="lotemp">

<div class="metardewcontainer1"><?php
if ($tempunit == 'C') {
	if ($metar34dewpointc >30) {echo '<div class=metardewtoday30>'.$metar34dewpointc."<smalluvunit> &nbsp;&deg;C";}
	else if ($metar34dewpointc >25) {echo '<div class=metardewtoday25>'.$metar34dewpointc."<smalluvunit> &nbsp;&deg;C";}
	else if ($metar34dewpointc >20) {echo '<div class=metardewtoday20>'.$metar34dewpointc."<smalluvunit> &nbsp;&deg;C";}
	else if ($metar34dewpointc >10) {echo '<div class=metardewtoday10>'.$metar34dewpointc."<smalluvunit> &nbsp;&deg;C";}
	else if ($metar34dewpointc >5) {echo '<div class=metardewtoday5>'.$metar34dewpointc."<smalluvunit> &nbsp;&deg;C";}
	else if ($metar34dewpointc >-50) {echo '<div class=metardewtoday0>'.$metar34dewpointc."<smalluvunit> &nbsp;&deg;C";}
	else if ($metar34dewpointc=='') {echo '<div class=metartemptoday0>'.$metar34dewpointc."<smalluvunit> N/A";}
} else {
	if ($metar34dewpointf>86) {echo '<div class=metartemptoday30>'.$metar34dewpointf."<smalluvunit> &nbsp;&deg;F";}
	else if ($metar34dewpointf>77) {echo '<div class=metartemptoday25>'.$metar34dewpointf."<smalluvunit> &nbsp;&deg;F";}
	else if ($metar34dewpointf>68) {echo '<div class=metartemptoday20>'.$metar34dewpointf."<smalluvunit> &nbsp;&deg;F";}
	else if ($metar34dewpointf>50) {echo '<div class=metartemptoday10>'.$metar34dewpointf."<smalluvunit> &nbsp;&deg;F";}
	else if ($metar34dewpointf>41) {echo '<div class=metartemptoday5>'.$metar34dewpointf."<smalluvunit> &nbsp;&deg;F";}
	else if ($metar34dewpointf>-50) {echo '<div class=metartemptoday0>'.$metar34dewpointf."<smalluvunit> &nbsp;&deg;F";}
	else if ($metar34dewpointf=='') {echo '<div class=metartemptoday0>'.$metar34dewpointf."<smalluvunit> N/A";}
}
?></smalluvunit></div></div> 
 <div class="dewword">Dewpoint</div>

 <div class="metarhumcontainer1"><?php 
if ($metar34humidity >85) {echo '<div class=metarhumtoday85-100>'.$metar34humidity ."<smalluvunit> &nbsp;%";}
else if ($metar34humidity >70) {echo '<div class=metarhumtoday70-85>'.$metar34humidity ."<smalluvunit> &nbsp;%";}
else if ($metar34humidity  >35) {echo '<div class=metarhumtoday35-70>'.$metar34humidity ."<smalluvunit> &nbsp;%";}
else if ($metar34humidity >=0) {echo '<div class=metarhumtoday0-35>'.$metar34humidity ."<smalluvunit> &nbsp;%";}
else if ($metar34humidity=='') {echo '<div class=metarhumtoday0-35><smalluvunit> N/A';}
?></smalluvunit></div></div> 
<div class="humword">&nbsp;Humidity</div>
</div>
  
</article>  
   
  <article>
  <div class=actualw style="background:teal;color:white;">&nbsp;&nbsp Wind Speed</div>   
   <?php
//set windspeed variables
if ($windunit == 'km/h') {
	$metarwind1 = 'kmh';
	$metarwind2 = 'kts';
	$metarwind3 = 'mph';
	$metarwind4 = 'ms';
} else if ($windunit == 'mph') {
	$metarwind1 = 'mph';
	$metarwind2 = 'kts';
	$metarwind3 = 'kmh';
	$metarwind4 = 'ms';
} else if ($windunit == 'kts') {
	$metarwind1 = 'kts';
	$metarwind2 = 'mph';
	$metarwind3 = 'kmh';
	$metarwind4 = 'ms';
} else {
	$metarwind1 = 'ms';
	$metarwind2 = 'mph';
	$metarwind3 = 'kmh';
	$metarwind4 = 'kts';
}
	if ($metar34windspeedkmh >=50) {$metarkmh = '<div class=metarwindtoday30>'.$metar34windspeedkmh."<smalluvunit> &nbsp;km/h";}
	else if ($metar34windspeedkmh >=40) {$metarkmh = '<div class=metarwindtoday25>'.$metar34windspeedkmh."<smalluvunit>&nbsp; km/h";}
	else if ($metar34windspeedkmh >=30) {$metarkmh = '<div class=metarwindtoday20>'.$metar34windspeedkmh."<smalluvunit>&nbsp; km/h";}
	else if ($metar34windspeedkmh >0) {$metarkmh = '<div class=metarwindtoday10>'.$metar34windspeedkmh."<smalluvunit>&nbsp; km/h";}
	else {$metarkmh = '<div class=metarwindtoday10>'.'0'."<smalluvunit>&nbsp; km/h";}
	if ($metar34windspeedmph >=31.06) {$metarmph = '<div class=metarwindtoday30>'.$metar34windspeedmph."<smalluvunit> &nbsp;mph";}
	else if ($metar34windspeedmph >=24.85) {$metarmph = '<div class=metarwindtoday25>'.$metar34windspeedmph."<smalluvunit> &nbsp;mph";}
	else if ($metar34windspeedmph >=18.6) {$metarmph = '<div class=metarwindtoday20>'.$metar34windspeedmph."<smalluvunit> &nbsp;mph";}
	else if ($metar34windspeedmph >0) {$metarmph = '<div class=metarwindtoday10>'.$metar34windspeedmph."<smalluvunit> &nbsp;mph";}
	else {$metarmph = '<div class=metarwindtoday10>'.'0'."<smalluvunit> &nbsp;mph";}
	if ($metar34windspeedkts >=26.9) {$metarkts = '<div class=metarwindtoday30>'.$metar34windspeedkts."<smalluvunit> &nbsp;kts";}
	else if ($metar34windspeedkts >=21.5) {$metarkts = '<div class=metarwindtoday25>'.$metar34windspeedkts."<smalluvunit> &nbsp;kts";}
	else if ($metar34windspeedkts >=16.19) {$metarkts = '<div class=metarwindtoday20>'.$metar34windspeedkts."<smalluvunit> &nbsp;kts";}
	else if ($metar34windspeedkts >0) {$metarkts = '<div class=metarwindtoday10>'.$metar34windspeedkts."<smalluvunit> &nbsp;kts";}
	else {$metarkts = '<div class=metarwindtoday10>'.'0'."<smalluvunit> &nbsp;kts";}
	if ($metar34windspeedms >=13.8) {$metarms = '<div class=metarwindtoday30>'.$metar34windspeedms."<smalluvunit> &nbsp;m/s";}
	else if ($metar34windspeedms >=11.1) {$metarms = '<div class=metarwindtoday25>'.$metar34windspeedms."<smalluvunit> &nbsp;m/s";}
	else if ($metar34windspeedms >=8.3) {$metarms = '<div class=metarwindtoday20>'.$metar34windspeedms."<smalluvunit> &nbsp;m/s";}
	else if ($metar34windspeedms >0) {$metarms = '<div class=metarwindtoday10>'.$metar34windspeedms."<smalluvunit> &nbsp;m/s";}
	else {$metarms = '<div class=metarwindtoday10>'.'0'."<smalluvunit> &nbsp;m/s";}
$metarspot1 = 'metar'.$metarwind1;
$metarspot2 = 'metar'.$metarwind2;
$metarspot3 = 'metar'.$metarwind3;
$metarspot4 = 'metar'.$metarwind4;
?></div></div></div></div></smalluvunit>

<div class="metarwindcontainer1">
<?php
//wind1 kph
echo $$metarspot1;
?>
</div>
<div class="metarwindcontainer2">
<?php 
//wind2 mph
echo $$metarspot2;
?></smalluvunit></div>
</div>
<div class="metarwindcontainer3">
<?php 
//wind3 kts
echo $$metarspot3;
?></smalluvunit></div>
</div>
<div class="metarwindcontainer4">
<?php 
//wind4 ms
echo $$metarspot4;
?></smalluvunit></div>
</div>
</div>
</article>

<article>
<div class=actualw style="background:teal;color:white;">&nbsp;&nbsp Wind Direction</div> 
</div>
</div>

<style>
.compassx {
  position: relative;
  width: 157px;
  height: 165px;
  margin: 0 auto;
  z-index: 1;
  align-items: center;
  justify-content: center;
}
</style>

<div style="overflow: hidden">
<div class="compassx">
<iframe style="width: 140; height: 150px; border: none;" src="dvmMetarCompass.php"></iframe>
</div>
</div>

  </article> 
 
  <article>
  <div class=actualt style="background:teal;color:white;">&nbsp;&nbsp Airport Data </div>   
  <stationid><?php echo $metar34stationid ; ?></stationid><br>
  <div class="lotemp">
   <?php

if ($distanceunit == 'km') {$metdist1 = round($airport1dist,0,PHP_ROUND_HALF_UP); $metdist2 = round($airport1dist / 1.609,0,PHP_ROUND_HALF_UP); $metunit1 = 'km'; $metunit2 = 'mi';}
else if ($distanceunit == 'mi') {$metdist1 = round($airport1dist / 1.609,0,PHP_ROUND_HALF_UP); $metdist2 = round($airport1dist,0,PHP_ROUND_HALF_UP); $metunit1 = 'mi'; $metunit2 = 'km';}
echo "Location <orange>",$metar34stationname  ;
echo '</orange> <orange>'.$metdist1.'</orange> '.$metunit1.' (<orange>';
echo $metdist2;
echo '</orange>'.$metunit2.')';
    ?>
 <div class="lotemp">
<?php //metar raw
echo "Metar: " .$metar34raw."";?>
</div>
<div class="hitemp">
<?php //update timestamp
date_default_timezone_set($tz);$date = $metar34time;$date = str_replace('@', ' ', $date);
$date = strtotime($date) + 60 * 60 * $UTC; echo date('jS M H:i',$date);
?> </div></div>

  </article> 
  
  <article>
  <div class=actualt style="background:teal;color:white;">&nbsp;&nbsp Raw Metar Info</div>  
  <div class="lotemp">
  <?php echo $info?> Raw METAR is the most common format in the world for the transmission of observational weather data. It is highly standardized through the International Civil Aviation Organization (ICAO), which allows it to be understood throughout most of the world.</span></div>
  </article> 
  
  <article>
  <div class=actualt style="background:teal;color:white;">&nbsp;&nbsp API  Info</div>  
  <div class="lotemp">
  <?php echo $info?> Data Provided by </span><a href="https://www.checkwx.com/weather/<?php echo $icao1;?>" title="https://www.checkwx.com/weather/<?php echo $icao1;?>" target="_blank" ><br><img src=img/checkwx.svg width=130px alt="https://www.checkwx.com/weather/<?php echo $icao1;?>"></a></span></div>
  </article> 
  
  
  <article>
  <div class=actualt style="background:teal;color:white;">&nbsp;&nbsp &copy; Info</div>  
  <div class="lotemp">
  <?php echo $info?> CSS/SVG/PHP scripts were developed by <a href="https://weather34.com" title="weather34.com" target="_blank" style="font-size:9px;">weather34.com</a>  for use in the weather34 template &copy; 2015-<?php echo date('Y');?>
  <br><br>
  <?php echo $info?> Guide Info provided  by <a href="https://en.wikipedia.org/wiki/METAR" title="https://en.wikipedia.org/wiki/METAR" target="_blank" style="font-size:9px;">Metar-Wikipedia </a>  
  </div></article> 
   
</main>
