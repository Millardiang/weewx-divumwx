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
// 	Issues for weewx-Weather34 template should be addressed to https://github.com/steepleian/weewx-Weather34/issues #                                                                                              
// 	                                                                                                				#
//###################################################################################################################

if (!file_exists("settings1.php")) { 
copy("initial_settings1.php", "settings1.php");}
include_once ('dvmCombinedData.php');
include_once ('common.php');
include_once ('webserver_ip_address.php');
include ('settings1.php');
include ('settings.php');
date_default_timezone_set($TZ);
header('Content-type: text/html; charset=utf-8');
error_reporting(0);
?>

<!DOCTYPE html>
<html>

<head>
  
  <title><?php echo $stationlocation;?> Weather Station</title>
  <!--Google / Search Engine Tags -->
  <meta itemprop="image" content="img/divumMeta-1.png">
  <meta itemprop="name" content="Home Weather Station <?php echo $stationlocation;?>">
  <meta content="Home weather station providing current weather conditions for <?php echo $stationlocation;?>" name="description">
  <meta itemprop="description" content="Home weather station providing current weather conditions for <?php echo $stationlocation;?>">
  <meta content="DivumWX" name="author">
  <meta content="place" property="og:type">
  <meta content="INDEX,FOLLOW" name="robots">
  <meta name="theme-color" content="#ffffff">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name=apple-mobile-web-app-title content="HOME WEATHER STATION">
  <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1, viewport-fit=cover">
  <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
  <link rel="manifest" href="site.webmanifest">
  <link href="css/main.<?php echo $theme;?>.css?version=<?php echo filemtime('css/main.' . $theme . '.css');?>" rel="stylesheet prefetch">

  <script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('sw.js')
          .then(registration => {
            console.log('SW registered with scope:', registration.scope);
          })
          .catch(err => {
            console.error('Registration failed:', err);
          });
      });
    }
  </script>
</head>

<!-- begin top layout-->
<div class="weather2-container">
  <div class="container weather34box-toparea">
    <!-- position 1 --->
    <div class="weather34box clock">
    <div class="weatherbox-top-border">
      <div class="title"><?php echo $info;?><?php echo $position1title;?></div>
      <div class="value">
        <div id="position1"></div>
      </div>
    </div>
   </div>
    <!-- position 2--->
    <div class="weather34box indoor">
     <div class="weatherbox-top-border">
      <div class="title"><?php echo $info;?><?php echo $position2title;?></div>
      <div class="value">
        <div id="position2"></div>
      </div>
    </div>
   </div>
    <!-- position 3--->
    <div class="weather34box earthquake">
     <div class="weatherbox-top-border">
      <div class="title"><?php echo $info;?><?php echo $position3title;?></div>
      <div class="value">
        <div id="position3"></div>
      </div>
    </div>
   </div>
    <!-- position 4--->
    <div class="weather34box alert">
     <div class="weatherbox-top-border">
      <div class="title"><?php echo $info;?><?php echo $position4title;?></div>
      <div class="value">
        <div id="position4"></div>
       </div>
     </div>
   </div>
 </div>
</div>
<!--begin outside station data-->

<!-- position 5--->
<div class="weather-container">
  <div class="weather-item">
    <div class="chartforecast">
      <span class="yearpopup"><a alt="temp charts" title="temp charts" href="pop_menu_temperature.php" data-lity><?php echo $menucharticonpage;?> Temperature Almanac and Derived Charts</a></span>
    </div>    
    <span class='moduletitle'><?php echo $lang['Temperature'];?> (<valuetitleunit>&deg;<?php echo $temp["units"];?></valuetitleunit>)</span>
    <div id="position5">
    </div>
  </div>
 
  <!-- position 6--->
  <div class="weather-item">
    <div class="chartforecast">
      <span class="yearpopup"><a alt="Forecast Menu" title="Forecast Menu" href="pop_menu_forecast.php" data-lity><?php echo $menucharticonpage;?> Forecasts</a></span>
      <span class="yearpopup"><a alt="Advisories" title="Advisories" href="<?php echo $advisory;?>" . data-lity>&nbsp;<?php echo $menucharticonpage;?> Advisories</a></span>
        <span class="yearpopup"><a alt="Meteogram" title="Meteogram" href="pop_dvmMeteogram.php" data-lity><?php echo $menucharticonpage;?> Meteogram</a></span>
    </div>
    <span class='moduletitle'><?php echo $position6title;?> (<valuetitleunit>&deg;<?php echo $temp["units"];?></valuetitleunit>)</span>
    <div id="position6">
    </div>
  </div>
 
  <!-- position 7--->
  <div class="weather-item">
    <div class="chartforecast">
      <span class="yearpopup"><a alt="nearby metar station" title="nearby metar station" href="pop_dvmMetar.php" data-lity><?php echo $chartinfo;?><?php echo ' Nearby Metar';?>
          <?php if (filesize('jsondata/me.txt') < 160) { echo "&nbsp;" , $offline;} else echo "";?></a></span>
      <span class="monthpopup"><a href="pop_windyradar.php" title="Windy.com Radar" alt="Windy.com Radar" data-lity><?php echo $chartinfo;?> Radar</a></span>
      <span class="monthpopup"><a href="pop_windywind.php" title="Windy.com Wind Map" alt="Windy.com Wind Map" data-lity><?php echo $chartinfo;?> Wind Map</a></span>
      <span class="todaypopup"><a alt="cloud cover" title="cloud cover" href="<?php echo $chartsource;?>/<?php echo $theme1;?>-charts.html?chart='cloudcoverplot'&span='weekly'&temp='<?php 
echo $temp['units'];?>'&pressure='<?php echo $barom['units'];?>'&wind='<?php echo $wind['units'];?>'&rain='<?php echo $rain['units'];?>" data-lity><?php echo $menucharticonpage;?> Cloud Cover</a></span>
    </div>
    <span class='moduletitle'><?php echo 'Current Conditions';?></span>
    <div id="position7">
     </div>
   </div>
  </div>
 
<!-- position 8--->
<?php if ($wind["units"] == "kts") { echo $wind["units"] = "kts"; };?>
<div class="weather-container">
  <div class="weather-item">
    <div class="chartforecast">
      <span class="yearpopup"><a alt="wind charts" title="wind charts" href="pop_menu_wind.php" data-lity><?php echo $menucharticonpage;?> Wind Almanac and Charts</a></span>
    </div>
    <span class='moduletitle'><?php echo $lang['Direction'];?> | <?php echo $lang['Windspeed'], " (<valuetitleunit>", $wind["units"];?></valuetitleunit>)</span>
    <div id="position8">
    </div>
  </div>
 
  <!-- position 9--->
  <div class="weather-item">
    <div class="chartforecast">
      <span class="yearpopup"><a alt="barometer charts" title="barometer charts" href="pop_menu_barometer.php" data-lity><?php echo $menucharticonpage;?> Barometer Almanac and Charts</a></span>
    </div>    
    <span class='moduletitle'><?php echo $lang['Barometer'], " (<valuetitleunit>", $barom["units"];?></valuetitleunit>)</span>
    <div id="position9">
    </div>
  </div>
 
  <!-- position 10--->
  <div class="weather-item">
    <div class="chartforecast">
      <span class="yearpopup"><a alt="orrery" title="orrery" href="dvmOrreyPopup.php" data-lity><?php echo $info;?> Orrery</a></span>
      <span class="yearpopup"><a alt="Astroclock" title="Astroclock" href="dvmAstroclockPopup.php" data-lity><?php echo $info;?> Astroclock</a></span>
      <span class="yearpopup"><a alt="daylightmap" title="daylightmap" href="dvmDaylightMapPopup.php" data-lity><?php echo $info;?> World Daylight Map</a></span>
    </div>
    <span class='moduletitle'><?php echo 'Solar Dial';?></span>
    <div id="position10">
     </div>
    </div>
   </div>
  
<!-- position 11--->
<div class="weather-container"> 
  <div class="weather-item">
    <div class="chartforecast">
      <span class="yearpopup"><a alt="rain charts" title="rain charts" href="pop_menu_rain.php" data-lity><?php echo $menucharticonpage;?> Rainfall Almanac and Charts</a></span>
    </div>
    <span class='moduletitle'><?php echo $lang['Rainfalltoday'], " (<valuetitleunit>" . $rain["units"];?></valuetitleunit>)</span>
    <div id="position11">
    </div>
  </div>
 
  <!--position 12-->
  <div class="weather-item">
    <div class="chartforecast">
       <span class="yearpopup"><a alt="solar" title="UV Guide" href="pop_menu_solar.php" data-lity><?php echo $menucharticonpage;?> UV and Solar Almanacs and Guide</a></span>
    </div>
    <span class='moduletitle'><?php echo $position12title;?></span>
    <div id="position12">
    </div>
  </div>
 
  <!--position 13-->
  <div class="weather-item">
    <div class="chartforecast">
        <span class="yearpopup"><a alt="celestial" title="celestial" href="pop_menu_celestial.php" data-lity><?php echo $info;?> Celestial Data</a></span>
        <span class="yearpopup"><a alt="Earthquakes Worldwide" title="Earthquakes Worldwide" href="pop_eqlist.php" data-lity><?php echo $chartinfo;?> Worldwide Earthquakes</a></span>
    </div>
    <span class='moduletitle'><?php echo $position13title;?></span>
    <div id="position13">
      </div>
     </div>
   </div>

<!--end outdoor data-->

<!--footer area for homeweatherstation template warning dont mess with this below this line unless you really know what you are doing-->
<div class="weatherfooter-container">
  <div class="weatherfooter-item">
    <div class="hardwarelogo1"><a href="http://weewx.com" alt="http://weewx.com" title="http://weewx.com">
        <?php echo '<img src="img/icon-weewx.svg" alt="WeeWX" title="WeeWX" width="150px" height="55px"><div class="hardwarelogo1text"></div>';?></a>
      </div>

    <div class="hardwarelogo2">
      <?php
echo '<a href="https://https://claydonsweather.org.uk/" title="https://claydonsweather.org.uk/" target="_blank"><br><img src="img/divumLogo.svg" width="40px" alt="https://https://claydonsweather.org.uk/" class="homeweatherstationlogo" ><weather34>Team DivumWX design in progress 2021-' . date('Y') . '</weather34></a>';?>
    </div>

    <div class="footertext">
      &nbsp;<?php echo $info;?>&nbsp;(<value><?php echo $templateversion;?></value>)&nbsp;<?php echo "WeeWX";?>-(<value>
        <maxred><?php echo $weather["swversion"];?>
      </value>)&nbsp;<?php echo $info . "&nbsp;" . $weatherhardware;?></div>
    <div class="footertext"><a href="https://github.com/steepleian/weewx-Weather34"><?php echo $github;?>&nbsp; WeeWX Version Repository at https://github.com/steepleian/weewx-Weather34 &nbsp;<img src="img/flags/<?php echo $flag;?>.svg" width="20px"></a></div>
    <div class="footertext">
      <a href="https://www.aerisweather.com/"><img src="img/aerisweather-attribution-h-<?php echo $theme;?>.png" width="75px"></a></br><a href="https://developer.yr.no/featured-products/forecast/">&nbsp; &nbsp; Meteogram Data by <img src="img/yr.svg" width="14px"></a></br><a href="https://bas.dev/work/meteocons">&nbsp; &nbsp; Animated Icons by <img src="img/bm.svg" width="14px"></a>
    </div>
  </div>
</div>

<?php 
include_once ('dvmUpdater.php');
include_once ('menu.php');
?>
</html>
