<?php
include('dvmCombinedData.php');
//current eq
date_default_timezone_set($TZ);
$json_string	= file_get_contents('jsondata/eq.txt');
$parsed_json	= json_decode($json_string,true);
$eqtitle 	= $parsed_json['features'][0]['properties']['flynn_region'];
$magnitude      = $parsed_json['features'][0]['properties']['mag'];
$depthraw 	= $parsed_json['features'][0]['properties']['depth'];
$depth          = round($depthraw, 1);
$time       	= $parsed_json['features'][0]['properties']['time'];
$lati		= $parsed_json['features'][0]['properties']['lat'];
$longi		= $parsed_json['features'][0]['properties']['lon'];
$eventime	= date("H:i:s j M y", strtotime($time) );
$eqdist 	= round(distance($lat, $lon, $lati, $longi), 1) ;
?>
<?php

$eqdist; if ($wind["units"] == 'mph') {$eqdist = round(distance($lat, $lon, $lati, $longi) * 0.621371, 1) ." mi";} else {$eqdist = round(distance($lat, $lon, $lati, $longi), 1)." km";}
$eqdista; if ($wind["units"] == 'mph') {$eqdista = round(distance($lat, $lon, $lati, $longi), 1) ."<smallrainunit>&nbsp;km";} else {$eqdista = round(distance($lat, $lon, $lati, $longi) * 0.621371, 1)."<smallrainunit>&nbsp;mi";} ?>
<div class="updatedtime1"><span><?php $updated = filemtime('jsondata/eq.txt'); echo $online, " ",date($timeFormat, $updated);?></span></div>

<div class="eqconverterx">

<?php //chuck
if($eqdista <= 200){echo "<div class=tempconvertercircleredx>".$eqdista ;}
else if($eqdista <= 500){echo "<div class=tempconvertercircleorangex>".$eqdista ;}
else if($magnitude <=1000){echo "<div class=tempconvertercircleyellowx>".$eqdista;}
else if($magnitude >= 1000){echo "<div class=tempconvertercirclegreenx>".$eqdista ;}
?></smalltempunit2>
</div>
</div>
<br>

<div class='eqcontainerMag'>

<?php
// EQ Latest earthquake 
if ($magnitude < 4.0) {
    echo "<div class='eqcautionMag'>".number_format($magnitude,1)."</div>	
        <div class='eqtx'>Minor</div></div><div class=\"eqtextx\"><value> $eqtitle <br><value>$eventime<br><depth>Depth: ";
        if ($wind["units"] == 'mph') echo round($depth * 0.621371, 1)."mi"; else echo "$depth km"; echo "</depth><br>
        Epicenter: <value><maxred>$eqdist</maxred>  <valueearthquake>From: $stationlocation</valueearthquake></value></div>";
} else if ($magnitude < 5.0) {
    echo "<div class='eqcautionMag'>".number_format($magnitude,1)."</div>	
	<div class='eqtx'>Light</div></div><div class=\"eqtext\"><value> $eqtitle <br><value>$eventime<br><depth>Depth: ";
        if ($wind["units"] == 'mph') echo round($depth * 0.621371, 1)."mi"; else echo "$depth km"; echo "</depth><br>
	Epicenter: <value><maxred>$eqdist</maxred>  <valueearthquake>From: $stationlocation</valueearthquake></value></div>";
} else if ($magnitude < 6.0) {
    echo "<div class='eqcautionMag'>".number_format($magnitude,1)."</div>	
	<div class='eqtx'>Moderate</div></div><div class=\"eqtext\"><value> $eqtitle <br><value>$eventime<br><depth>Depth: ";
        if ($wind["units"] == 'mph') echo round($depth * 0.621371, 1)."mi"; else echo "$depth km"; echo "</depth><br>
	Epicenter: <value><maxred>$eqdist</maxred>  <valueearthquake>From: $stationlocation</valueearthquake></value></div>";
} else if ($magnitude < 7.0) {
    echo "<div class='eqcautionMag'>".number_format($magnitude,1)."</div>	
	<div class='eqtx'>Strong</div></div><div class=\"eqtext\"><value> $eqtitle <br><value>$eventime<br><depth>Depth: ";
        if ($wind["units"] == 'mph') echo round($depth * 0.621371, 1)."mi"; else echo "$depth km"; echo "</depth><br>
	Epicenter: <value><maxred>$eqdist</maxred> <valueearthquake>From: $stationlocation</valueearthquake></value></div>";
} else if ($magnitude < 8.0) {
    echo "<div class='eqcautionMag'>".number_format($magnitude,1)."</div>	
	<div class='eqtx'>Great</div></div><div class=\"eqtext\"><value> $eqtitle <br><value>$eventime<br><depth>Depth: ";
        if ($wind["units"] == 'mph') echo round($depth * 0.621371, 1)."mi"; else echo "$depth km"; echo "</depth><br>
	Epicenter: <value><maxred>$eqdist</maxred></maxred> <valueearthquake>From: $stationlocation</valueearthquake></value></div>";
} else if ($magnitude > 8.0) {
    echo "<div class='eqcautionMag'>".number_format($magnitude,1)."</div>	
	<div class='eqtx'>Major</div></div><div class=\"eqtext\"><value> $eqtitle <br><value>$eventime<br><depth>Depth: ";
        if ($wind["units"] == 'mph') echo round($depth * 0.621371, 1)."mi"; else echo "$depth km"; echo "</depth><br>
	Epicenter: <value><maxred>$eqdist</maxred></maxred> <valueearthquake>From: $stationlocation</valueearthquake></value></div>";
}
?></div>

<html>

<?php

if($theme == 'dark') {

echo  

'<style>
.eqconverterx {
  margin-top: 0px;
  margin-left: 237px;
}

.tempconvertercirclegreenx,
.tempconvertercircleorangex,
.tempconvertercircleredx,
.tempconvertercircleyellowx {
  align-items: center;
  width: 4.2rem;
  color: silver;
  font-family: weathertext2;
  height: 1rem;
  line-height: 16px;
  overflow: hidden;
}

.tempconvertercirclegreenx,
.tempconvertercircleorangex,
.tempconvertercircleredx,
.tempconvertercircleyellowx {
  display: flex;
  justify-content: center;
  border: 1px solid #38383c;
  border-radius: 2px;
  font-size: .65em  
}

svg.pulse-svg {
  position: relative; 
  top: -81px; 
  margin-left: -180px;
  overflow: visible;
}

.eqcautionMag {
  position: absolute;
  font-family: Arial, Helvetica, system;
  left: 26px;
  margin-top: -32px;
  color: #fff;
  font-size: 1.0rem;
}
.eqcontainerMag {
  position: relative;
  font-family: weathertext2, system;
  margin-left: 30px;
  margin-top: 60px;
}

.eqtx {
  font-family: Arial, Helvetica, system;
  position: relative;
  left: -104px;
  top: 40px;
  color: silver;
  font-size: .65rem
}

.eqtextx {
  float: left;
  width: 100px;
  margin-left: 160px;
  margin-top: -80px;
  font: .7rem arial, system;
  font-family: weathertext2;
  line-height: 10px
}

.eqtextx color,
.eqtextx depth {
  color: #90b12a
}

.mag {
  font-family: Arial, Helvetica, system;
  position: relative;
  left: -90px;
  top: -110px;
  color: silver;
  font-size: .65rem;
}

</style>';

} else {

echo 
'<style>

.eqconverterx {
  margin-top: 5px;
  margin-left: 237px;
}

.tempconvertercirclegreenx,
.tempconvertercircleorangex,
.tempconvertercircleredx,
.tempconvertercircleyellowx {
  align-items: center;
  width: 4.2rem;
  color: #fff;
  font-family: weathertext2;
  height: 1rem;
  line-height: 16px;
  overflow: hidden
}


.tempconvertercirclegreenx,
.tempconvertercircleorangex,
.tempconvertercircleredx,
.tempconvertercircleyellowx {
  display: flex;
  justify-content: center;
  border: 1px solid #e6e8ef;
  border-radius: 2px;
  font-size: .65em;
  color: #fff;
  line-height: 16px;
  font-family: weathertext2;  
}
.tempconvertercircleyellowx {
  background: rgba(230, 161, 65, .8)
}

.tempconvertercircleorangex {
  background: rgba(255, 124, 57, 1)
}

.tempconvertercircleredx {
  background: #d35d4e
}

.tempconvertercirclegreenx {
  background: #90b12a
}

svg.pulse-svg {
  position: relative; 
  top: -81px; 
  margin-left: -180px;
  overflow: visible;
}

.eqcautionMag {
  position: absolute;
  font-family: Arial, Helvetica, system;
  left: 26px;
  margin-top: -32px;
  color: black;
  font-size: 1.0rem;
}
.eqcontainerMag {
  position: relative;
  font-family: weathertext2, system;
  margin-left: 30px;
  margin-top: 60px;
}

.eqtx {
  font-family: Arial, Helvetica, system;
  position: relative;
  left: -104px;
  top: 40px;
  color: black;
  font-size: .65rem
}

.eqtextx {
  float: left;
  width: 100px;
  margin-left: 160px;
  margin-top: -80px;
  font: .7rem arial, system;
  font-family: weathertext2;
  line-height: 10px
}

.eqtextx color,
.eqtextx depth {
  color: #90b12a
}

.mag {
  font-family: Arial, Helvetica, system;
  position: relative;
  left: -90px;
  top: -110px;
  color: black;
  font-size: .65rem;
}

</style>';
}
?>
<div class='mag'>Magnitude</div>

<?php

// EQ Latest earthquake 
if ($magnitude < 4.0) {
echo '<svg class="pulse-svg" width="60px" height="60px"  viewBox="0 0 60 60">
<g fill="none">
    <circle cx="30" cy="30" r="17" stroke="#90b12a" stroke-width="2.5" />
    <circle cx="30" cy="30" r="27" stroke="#90b12a" stroke-width="2" />
    <circle cx="30" cy="30" r="37" stroke="#90b12a" stroke-width="1.5" />
    <circle cx="30" cy="30" r="47" stroke="#90b12a" stroke-width="1" />
    <circle cx="30" cy="30" r="57" stroke="#90b12a" stroke-width="0.5" />
</g>
</svg>';
} else if ($magnitude < 5.0) {
echo '<svg class="pulse-svg" width="60px" height="60px" viewBox="0 0 60 60">
<g fill="none">
    <circle cx="30" cy="30" r="17" stroke="#e6a141" stroke-width="2.5" />
    <circle cx="30" cy="30" r="27" stroke="#e6a141" stroke-width="2" />
    <circle cx="30" cy="30" r="37" stroke="#e6a141" stroke-width="1.5" />
    <circle cx="30" cy="30" r="47" stroke="#e6a141" stroke-width="1" />
    <circle cx="30" cy="30" r="57" stroke="#e6a141" stroke-width="0.5" />
</g>
</svg>';
} else if ($magnitude < 6.0) {
echo '<svg class="pulse-svg" width="60px" height="60px" viewBox="0 0 60 60">
<g fill="none">
    <circle cx="30" cy="30" r="17" stroke="#d05f2d" stroke-width="2.5" />
    <circle cx="30" cy="30" r="27" stroke="#d05f2d" stroke-width="2" />
    <circle cx="30" cy="30" r="37" stroke="#d05f2d" stroke-width="1.5" />
    <circle cx="30" cy="30" r="47" stroke="#d05f2d" stroke-width="1" />
    <circle cx="30" cy="30" r="57" stroke="#d05f2d" stroke-width="0.5" />
</g>
</svg>';
} else if ($magnitude < 7.0) {
echo '<svg class="pulse-svg" width="60px" height="60px" viewBox="0 0 60 60">
<g fill="none">
    <circle cx="30" cy="30" r="17" stroke="#d05f2d" stroke-width="2.5" />
    <circle cx="30" cy="30" r="27" stroke="#d05f2d" stroke-width="2" />
    <circle cx="30" cy="30" r="37" stroke="#d05f2d" stroke-width="1.5" />
    <circle cx="30" cy="30" r="47" stroke="#d05f2d" stroke-width="1" />
    <circle cx="30" cy="30" r="57" stroke="#d05f2d" stroke-width="0.5" />
</g>
</svg>';
} else if ($magnitude < 8.0) {
echo '<svg class="pulse-svg" width="60px" height="60px" viewBox="0 0 60 60">
<g fill="none">
    <circle cx="30" cy="30" r="17" stroke="#d05f2d" stroke-width="2.5" />
    <circle cx="30" cy="30" r="27" stroke="#d05f2d" stroke-width="2" />
    <circle cx="30" cy="30" r="37" stroke="#d05f2d" stroke-width="1.5" />
    <circle cx="30" cy="30" r="47" stroke="#d05f2d" stroke-width="1" />
    <circle cx="30" cy="30" r="57" stroke="#d05f2d" stroke-width="0.5" />
</g>
</svg>';
} else if ($magnitude > 8.0) {
echo '<svg class="pulse-svg" width="60px" height="60px" viewBox="0 0 60 60">
<g fill="none">
    <circle cx="30" cy="30" r="17" stroke="#d05f2d" stroke-width="2.5" />
    <circle cx="30" cy="30" r="27" stroke="#d05f2d" stroke-width="2" />
    <circle cx="30" cy="30" r="37" stroke="#d05f2d" stroke-width="1.5" />
    <circle cx="30" cy="30" r="47" stroke="#d05f2d" stroke-width="1" />
    <circle cx="30" cy="30" r="57" stroke="#d05f2d" stroke-width="0.5" />
</g></svg>';
}?>


  

</html>
