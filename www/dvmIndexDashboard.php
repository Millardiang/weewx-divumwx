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

if (!file_exists("userSettings.php")) { 
copy("initial_userSettings.php", "userSettings.php");}
include_once ('dvmCombinedData.php');
include_once ('common.php');
include_once ('webserver_ip_address.php');
include ('userSettings.php');
include ('fixedSettings.php');
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
    <!-- position 1 - Fixed Position --->
    <div class="weather34box clock">
    <div class="weatherbox-top-border">
      <div class="title"><?php echo $info;?><?php echo $lang['timeTop'];?></div>
      <div class="value">
        <div id="position1"></div>
      </div>
    </div>
   </div>
    <!-- position 2--->
    <div class="weather34box indoor">
     <div class="weatherbox-top-border">
      <div class="value">
        <div id="position2"></div>
      </div>
    </div>
   </div>
    <!-- position 3--->
    <div class="weather34box earthquake">
     <div class="weatherbox-top-border">
           <div class="value">
        <div id="position3"></div>
      </div>
    </div>
   </div>
    <!-- position 4 - Fixed Position --->
    <div class="weather34box alert">
     <div class="weatherbox-top-border">
      <div class="title"><?php echo $info;?><?php echo $lang['advisoriesTop'];?></div>
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
    <div id="position5">
    </div>
  </div>
 
  <!-- position 6--->
  <div class="weather-item">
    <div id="position6">
    </div>
  </div>
 
  <!-- position 7--->
  <div class="weather-item">
    <div id="position7">
     </div>
   </div>
  </div>
 
<!-- position 8--->

<div class="weather-container">
  <div class="weather-item">
    <div id="position8">
    </div>
  </div>
 
  <!-- position 9--->
  <div class="weather-item">
    <div id="position9">
    </div>
  </div>
 
  <!-- position 10--->
  <div class="weather-item">
    <div id="position10">
     </div>
    </div>
   </div>
  
<!-- position 11--->
<div class="weather-container"> 
  <div class="weather-item">
    <div id="position11">
    </div>
  </div>
 
  <!--position 12-->
  <div class="weather-item">
    <div id="position12">
    </div>
  </div>
 
  <!--position 13-->
  <div class="weather-item">

    <div id="position13">
      </div>
     </div>
   </div>
<!-- position 14--->
<div class="weather-container"> 
  <div class="weather-item">
    <div id="position14">
    </div>
  </div>
 
  <!--position 15-->
  <div class="weather-item">
    <div id="position15">
    </div>
  </div>
 
  <!--position 16-->
  <div class="weather-item">
    <div id="position16">
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
include_once ('dvmMenuDashboard.php');
?>
</html>