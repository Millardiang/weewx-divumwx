<?php
#####################################################################################################################
#                                                                                                                   #
# weewx-divumwx Skin Template maintained by The DivumWX Team                                                        #
#                                                                                                                   #
# Copyright (C) 2023 Ian Millard, Steven Sheeley, Sean Balfour. All rights reserved                                 #
#                                                                                                                   #
# Distributed under terms of the GPLv3. See the file LICENSE.txt for your rights.                                   #
#                                                                                                                   #
# Issues for weewx-divumwx skin template should be addressed to https://github.com/Millardiang/weewx-divumwx/issues # 
#                                                                                                                   #
#####################################################################################################################
include('dvmCombinedData.php');
date_default_timezone_set($TZ);
header('Content-type: text/html; charset=utf-8');
$earthimg = "img/earth-1.jpg";
if ($theme === "dark")
{$circleborder = "rgb(30,32,36.1)";}
else if ($theme === "light")
{$circleborder = "#c9daf9";} 
$light = $alm["daylight"]; 
$daylight = ltrim($light, '0'); 
$dark = 24 - str_replace(':', '.', $alm["daylight"]);
$lighthours = substr($alm["daylight"], 0, 2); 
$lightmins = substr($alm["daylight"], - 2);
$darkhours = 23 - $lighthours; 
$darkminutes = 60 - $lightmins;
$darkminutes = ($darkminutes < 10) ? '0' .$darkminutes : $darkminutes;

if (round($sun_alt,2) >= 0) { 
$sun_elevation = round($sun_alt,2)."°<div class=sunabovedivumwx> </div>";
} else if (round($sun_alt,2) < 0) { 
$sun_elevation = round($sun_alt,2)."°<div class=sunbelowdivumwx> </div>"; 
}?>

   <div class="chartforecast2">
        <span class="yearpopup"><a alt="daylightmap" title="daylightmap" href="dvmDaylightMapPopup.php" data-lity><?php echo $info;?> World Daylight Map</a></span>
        <span class="yearpopup"><a alt="celestial" title="celestial" href="dvmMenuCelestialPopup.php" data-lity><?php echo $info;?> Celestial Data</a></span>

    </div>
    <span class='moduletitle'><?php echo $lang['earthDaylightModule'];?></span>
    
<div class="updatedtime1"><?php if(file_exists($earthimg)&&time() - filemtime($earthimg)>3600) echo $offline. '<offline> Offline </offline>'; else echo $online." ".date("H:i:s", filemtime($earthimg));?></div>

<div class="daylightmoduleposition"> 
<?php echo 
'<div class="divumwxsunlightday"><divumwxdaylightdaycircle></divumwxdaylightdaycircle> '.$alm["daylight"].' hrs<br>'.$lang['TotalDaylight'].'</div>
<div class="divumwxsundarkday">'.$darkhours.':'.$darkminutes.' hrs <divumwxdarkdaycircle></divumwxdarkdaycircle><br>'.$lang['TotalDarkness'].'</div>
<div class="divumwxsunriseday">'.$sunuphalf.''.$lang['Sunrise'].'<br>Today: '.$alm["sunrise"].'<br>First Light: (<blueu>'.$alm["civil_twilight_begin"] .'</blueu>)</div>
<div class="divumwxsunsetday">'.$sundownhalf.''.$lang['Sunset'].'<br>Tonight: '.$alm["sunset"].'<br>Last Light: (<blueu>'.$alm["civil_twilight_end"].'</blueu>)</div>

<!--div class="daylightword"><value>Sun Azimuth<span><value><maxred> '.round($alm["sun_azimuth"],2).'°</maxred></value></span></div-->

<!--div class="elevationword"><value>Sun Elevation<span><value><maxred> '.$alm["sun_altitude"].'°</maxred></value></span></div-->

<div class="sundialcontainerdiv2" ><div id="sundialcontainer" class=sundialcontainer><div class="suncanvasstyle"></div></div>

<!--div class="divumwxmoonphasem">Moon Phase <br>'.$alm["moonphase"].'<br>'.$lang['Moonrise'].'<br>'.'<blueu> '.$alm["moonrise"].'</blueu></div-->
<!--div class="divumwxluminancem">Luminance<br> '.$alm["luminance"].' %<br>'.$lang['Moonset'].'<br>'.'<maxred> '.$alm["moonset"].'</maxred></div-->';

?>

<style>
.earthmodulepos {
  position: absolute;
top: 6px;
left: 112.5px
}
.earthpos {
  position: absolute;
top: -14px;
left: 72.5px
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
