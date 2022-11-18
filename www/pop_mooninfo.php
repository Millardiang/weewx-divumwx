<?php 
//###################################################################################################################
//	weewx-Weather34 Template maintained by Ian Millard (Steepleian)                                 				#
//	                                                                                                				#
//  Contains original legacy code (by agreement) created and developed by Brian Underdown (https://weather34.com)   #
//  for the (now superseeded) original Weather34 Template which is no longer maintained by its creator              #
//  © weather34.com original CSS/SVG/PHP 2015-2019                                                                  #
// 	                                                                                                				#
//  Contains original code by Ian Millard and collaborators															#
//  © claydonsweather.org.uk original CSS/SVG/PHP 2020-2021                                                         #
// 	                                                                                                				#
// 	Issues for weewx-Weather34 template should be addressed to https://github.com/steepleian/weewx-Weather34/issues #                                                                                              #
// 	                                                                                                				#
//###################################################################################################################
include('w34CombinedData.php');
include('serverdata/archivedata.php');
if ($theme === "dark") {
    echo '<style>@font-face {
  font-family: weathertext;
  src: url(css/fonts/verbatim-regular.woff) format("woff"),
    url(fonts/verbatim-regular.woff2) format("woff2"),
    url(fonts/verbatim-regular.ttf) format("truetype");
}
html,
body {
  font-size: 13px;
  font-family: "weathertext", Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
.grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 2fr));
  grid-gap: 5px;
  align-items: stretch;
  color: #f5f7fc;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
.grid > article {
  border: 1px solid rgba(245, 247, 252, 0.02);
  box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6);
  padding: 5px;
  font-size: 0.8em;
  -webkit-border-radius: 4px;
  border-radius: 4px;
  background: 0;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
.grid > article img {
  max-width: 100%;
}

.weather34darkbrowser {
  position: relative;
  background: 0;
  width: 97%;
  height: 30px;
  margin: auto;
  margin-top: -5px;
  margin-left: 0px;
  border-top-left-radius: 5px;
  border-top-right-radius: 5px;
  padding-top: 10px;
}
.weather34darkbrowser[url]:after {
  content: attr(url);
  color: white;
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
blue {
  color: #01a4b4;
}
orange {
  color: #009bb4;
}
orange1 {
  position: relative;
  color: #009bb4;
  margin: 0 auto;
  text-align: center;
  margin-left: 5%;
  font-size: 1.1rem;
}
green {
  color: #aaa;
}
red {
  color: #f37867;
}
red6 {
  color: #d65b4a;
}
value {
  color: #fff;
}
yellow {
  color: #cc0;
}
purple {
  color: #916392;
}
meteotextshowertext {
  font-size: 1.2rem;
  color: #009bb4;
}
meteorsvgicon {
  color: #f5f7fc;
}
.moonphasesvg {
  align-items: right;
  justify-content: center;
  display: flex;
  max-height: 120px;
}
.moonphasetext {
  font-size: 0.8rem;
  color: #f5f7fc;
  position: absolute;
  display: inline;
  left: 125px;
  top: 100px;
}
moonphaseriseset {
  font-size: 0.75rem;
}
credit {
  position: relative;
  font-size: 0.7em;
  top: 10%;
}
.actualt {
  position: relative;
  left: 5px;
  -webkit-border-radius: 3px;
  -moz-border-radius: 3px;
  -o-border-radius: 3px;
  border-radius: 3px;
  background: teal;
  padding: 5px;
  font-family: Arial, Helvetica, sans-serif;
  width: 100px;
  height: 0.8em;
  font-size: 0.8rem;
  padding-top: 2px;
  color: white;
  align-items: center;
  justify-content: center;
  margin-bottom: 10px;
  top: 0;
}
.actualw {
  position: relative;
  left: 5px;
  -webkit-border-radius: 3px;
  -moz-border-radius: 3px;
  -o-border-radius: 3px;
  border-radius: 3px;
  background: rgba(74, 99, 111, 0.1);
  padding: 5px;
  font-family: Arial, Helvetica, sans-serif;
  width: 100px;
  height: 0.8em;
  font-size: 0.8rem;
  padding-top: 2px;
  color: #aaa;
  align-items: center;
  justify-content: center;
  margin-bottom: 10px;
  top: 0;
}

    </style>';
} elseif ($theme === "light") {
    echo '<style>@font-face {
  font-family: weathertext;
  src: url(css/fonts/verbatim-regular.woff) format("woff"),
    url(fonts/verbatim-regular.woff2) format("woff2"),
    url(fonts/verbatim-regular.ttf) format("truetype");
}
html,
body {
  font-size: 13px;
  font-family: "weathertext", Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  background-color: white;
}
.grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 2fr));
  grid-gap: 5px;
  align-items: stretch;
  color: black;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
.grid > article {
  border: 1px solid rgba(245, 247, 252, 0.02);
  box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6);
  padding: 5px;
  font-size: 0.8em;
  -webkit-border-radius: 4px;
  border-radius: 4px;
  background: 0;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
.grid > article img {
  max-width: 100%;
}

.weather34darkbrowser {
  position: relative;
  background: 0;
  width: 97%;
  height: 30px;
  margin: auto;
  margin-top: -5px;
  margin-left: 0px;
  border-top-left-radius: 5px;
  border-top-right-radius: 5px;
  padding-top: 10px;
}
.weather34darkbrowser[url]:after {
  content: attr(url);
  color: black;
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
blue {
  color: #01a4b4;
}
orange {
  color: #009bb4;
}
orange1 {
  position: relative;
  color: #009bb4;
  margin: 0 auto;
  text-align: center;
  margin-left: 5%;
  font-size: 1.1rem;
}
green {
  color: #aaa;
}
red {
  color: #f37867;
}
red6 {
  color: #d65b4a;
}
value {
  color: #fff;
}
yellow {
  color: #cc0;
}
purple {
  color: #916392;
}
meteotextshowertext {
  font-size: 1.2rem;
  color: #009bb4;
}
meteorsvgicon {
  color: #f5f7fc;
}
.moonphasesvg {
  align-items: right;
  justify-content: center;
  display: flex;
  max-height: 120px;
}
.moonphasetext {
  font-size: 0.8rem;
  color: black;
  position: absolute;
  display: inline;
  left: 125px;
  top: 100px;
}
moonphaseriseset {
  font-size: 0.75rem;
}
credit {
  position: relative;
  font-size: 0.7em;
  top: 10%;
}
.actualt {
  position: relative;
  left: 5px;
  -webkit-border-radius: 3px;
  -moz-border-radius: 3px;
  -o-border-radius: 3px;
  border-radius: 3px;
  background: teal;
  padding: 5px;
  font-family: Arial, Helvetica, sans-serif;
  width: 100px;
  height: 0.8em;
  font-size: 0.8rem;
  padding-top: 2px;
  color: white;
  align-items: center;
  justify-content: center;
  margin-bottom: 10px;
  top: 0;
}
.actualw {
  position: relative;
  left: 5px;
  -webkit-border-radius: 3px;
  -moz-border-radius: 3px;
  -o-border-radius: 3px;
  border-radius: 3px;
  background: rgba(74, 99, 111, 0.1);
  padding: 5px;
  font-family: Arial, Helvetica, sans-serif;
  width: 100px;
  height: 0.8em;
  font-size: 0.8rem;
  padding-top: 2px;
  color: #aaa;
  align-items: center;
  justify-content: center;
  margin-bottom: 10px;
  top: 0;
}

</style>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Weather34 Home Weather Moon Phase Information</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

<div class="weather34darkbrowser" url="Moon Phase Information"></div>
  
<main class="grid">
<article>
       
<?php echo $info;?> Current Moon Phase<br><br>  
 
<div id="weather34moonphases" class="moonphasesvg"></div>
<svg id="weather34 simple moonphase"><circle cx="50" cy="50" r="49.5" fill="rgba(86, 95, 103, .4)"/><path id="weather34shape" fill="rgba(230, 232, 239, .5)"/></svg>
<script> //simple moonphase for weather34
weather34Moon();
function weather34Moon() {
var day = Date.now() / 86400000;
var referenceweather34Moon = Date.UTC(2018, 0, 17, 2, 17, 0, 0);
var refweather34Day = referenceweather34Moon / 86400000;
var phase = (day - refweather34Day) % 29.530588853 * 1.008;
var s = String;
switch (Math.round(phase / 3.75)){}document.getElementById("weather34moonphases");
var weather34moonCurve;
var lf = Math.min(3 - 4 * (phase / 30), 1);
var lc = Math.abs(lf * 50);	
var lb = (lf < 0) ? "0" : "1";
var rf = Math.min(3 + 4 * ((phase - 30) / 30), 1);	
var rc = Math.abs(rf * 50);	
var rb = (rf < 0) ? "0" : "1";
weather34moonCurve = "M 50,0 "+ "a "+s(lc)+",50 0 0 "+lb+" 0,100 "+ "a "+s(rc)+",50 0 0 "+rb+" 0,-100";
document.getElementById("weather34shape").setAttribute("d",weather34moonCurve);}
</script>
 
<div class=moonphasetext>    
<?php echo "<aqivalue1>".$moon_phase." </aqivalue1>";?>             
<br>
<?php echo" Luminance <orange>" .round($moon_fullness,2)."</orange> %";?>            
</div>      
</article>  
  
<article>
 <moonphaseriseset>
<?php echo $info;?> Moon Rise/Set Information<br><br>
<svg id="i-ban" viewBox="0 0 32 32" width="10" height="10" fill="#39739f" stroke="#39739f" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg> Moonrise:

<?php echo $moon_rise," ";?>
<svg id="i-chevron-top" viewBox="0 0 32 32" width="10" height="10" fill="none" stroke="#ff8841" stroke-linecap="round" stroke-linejoin="round" stroke-width="10%"><path d="M30 20 L16 8 2 20" /></svg>
</span><br>
<svg id="i-ban" viewBox="0 0 32 32" width="10" height="10" fill="#D46842" stroke="#D46842" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%"><circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg> Moonset: 
<?php echo $moon_set," ";?>
<svg id="i-chevron-bottom" viewBox="0 0 32 32" width="10" height="10" fill="none" stroke="#ff8841" stroke-linecap="round" stroke-linejoin="round" stroke-width="10%"><path d="M30 12 L16 24 2 12" /></svg>
</span><br>

<svg id="i-ban" viewBox="0 0 32 32" width="10" height="10" fill="rgba(255, 136, 65, 1.00)" stroke="rgba(255, 136, 65, 1.00)" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg>
Next Full Moon: <?php echo$full_moon ;?></span><br>
<svg id="i-ban" viewBox="0 0 32 32" width="10" height="10" fill="#777" stroke="#777" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /> <path d="M6 6 L26 26" /></svg>
Next New Moon: <?php echo $new_moon;?>
</span><br /><svg id="i-ban" viewBox="0 0 32 32" width="10" height="10" fill="rgba(154, 186, 47, 1.00)" stroke="rgba(154, 186, 47, 1.00)" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg> 
<?php echo "Current Moon cycle is <span style='color:#ff8841'>", $moonAge," days old";?>
                 
</article>  
  
<article>
<?php echo $info;?> Moon Facts: <orange>Did you Know?</orange><br><br>
<svg id="i-ban" viewBox="0 0 32 32" width="8" height="8" fill="#3b9cac" stroke="#3b9cac" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg> The Moon was approximately formed 4.5 billion years ago  .<br>
<svg id="i-ban" viewBox="0 0 32 32" width="8" height="8" fill="#3b9cac" stroke="#3b9cac" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg> High and Low tides are caused by the Moons gravitational pull.<br>
<svg id="i-ban" viewBox="0 0 32 32" width="8" height="8" fill="#3b9cac" stroke="#3b9cac" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg> The Moon orbits the Earth every 27.3 days approximately. <br>
<svg id="i-ban" viewBox="0 0 32 32" width="8" height="8" fill="#3b9cac" stroke="#3b9cac" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg> As sunlight hits the moon's surface,temperatures can reach 260&deg;F (127&deg;C).<br>
<svg id="i-ban" viewBox="0 0 32 32" width="8" height="8" fill="#3b9cac" stroke="#3b9cac" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
<circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg> On the dark side of the moon,temperatures can drop to minus -280&deg;F (-173&deg;C)..<br>

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
<div class=actualt>&nbsp;&nbsp &copy; Information</div>  
<?php echo $info?> CSS/SVG/PHP scripts were developed by <a href="https://weather34.com" title="weather34.com" target="_blank" style="font-size:9px;">weather34.com</a>  for use in the weather34 template &copy; 2015-<?php echo date('Y');?></span></article> 
</main>
