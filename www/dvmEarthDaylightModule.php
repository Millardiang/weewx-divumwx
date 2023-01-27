<style>
.earthmodulepos {
  position: absolute;
  top: 60px;
  left: 110px
}
.earthpos {
  position: absolute;
  top: -14px;
  left: -25px
}
</style>
<?php
include('dvmCombinedData.php');
date_default_timezone_set($TZ);
header('Content-type: text/html; charset=utf-8');
?>
    
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
        <span class="yearpopup"><a alt="daylightmap" title="daylightmap" href="dvmDaylightMapPopup.php" data-lity><?php echo $info;?> World Daylight Map</a></span>
        <span class="yearpopup"><a alt="celestial" title="celestial" href="pop_menu_celestial.php" data-lity><?php echo $info;?> Celestial Data</a></span>
    </div>
    <span class='moduletitle2'><?php echo $lang['earthDaylightModule'];?></span>

<div class="updatedtime1"><?php if(file_exists($livedata)&&time() - filemtime($livedata)>3600) echo $offline. '<offline> Offline </offline>'; else echo $online." ".$weather["time"];?></div>

<div class="daylightmoduleposition"> 
<?php echo 
'<div class="weather34sunlightday"><weather34daylightdaycircle></weather34daylightdaycircle> '.$alm["daylight"].' hrs<br>'.$lang['TotalDaylight'].'</div>
<div class="weather34sundarkday">'.$darkhours.':'.$darkminutes.' hrs <weather34darkdaycircle></weather34darkdaycircle><br>'.$lang['TotalDarkness'].'</div>
<div class="weather34sunriseday">'.$sunuphalf.''.$lang['Sunrise'].'<br>Today: '.$alm["sunrise"].'<br>First Light: (<blueu>'.$alm["civil_twilight_begin"] .'</blueu>)</div>
<div class="weather34sunsetday">'.$sundownhalf.''.$lang['Sunset'].'<br>Tonight: '.$alm["sunset"].'<br>Last Light: (<blueu>'.$alm["civil_twilight_end"].'</blueu>)</div>

<!--div class="daylightword"><value>Sun Azimuth<span><value><maxred> '.round($alm["sun_azimuth"],2).'°</maxred></value></span></div-->

<!--div class="elevationword"><value>Sun Elevation<span><value><maxred> '.$alm["sun_altitude"].'°</maxred></value></span></div-->

<div class="sundialcontainerdiv2" ><div id="sundialcontainer" class=sundialcontainer><div class="suncanvasstyle"></div></div>

<!--div class="weather34moonphasem">Moon Phase <br>'.$alm["moonphase"].'<br>'.$lang['Moonrise'].'<br>'.'<blueu> '.$alm["moonrise"].'</blueu></div-->
<!--div class="weather34luminancem">Luminance<br> '.$alm["luminance"].' %<br>'.$lang['Moonset'].'<br>'.'<maxred> '.$alm["moonset"].'</maxred></div-->';

?>
<?php
$earthimg = "img/earth-1.jpg"; 
if ($theme === "dark")
{$circleborder = "rgb(30,32,36.1)";}
else if ($theme === "light")
{$circleborder = "white";} 
?>

<style>
.earthmodulepos {
  position: relative;
top: 15px;
left: 0px
}
.earthpos {
  position: relative;
top: -110px;
left: -5px
}
</style>
<html>
<div class="earthmodulepos">
<div id = "dldata">
<img rel='prefetch' src='<?php echo $earthimg; ?>' width='100px' height='100px' alt='moon image'>

</div></div></div>

<div class="earthpos">
<svg width="160" height="160" viewBox="0 0 160 160">
   <circle cx="80" cy="80" r="60" stroke="<?php echo $circleborder;?>" stroke-width="21.5" fill="none" />
</svg> 
  </div>

</html>
