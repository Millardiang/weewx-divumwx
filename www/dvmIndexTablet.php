<?php

if (!file_exists("userSettings.php")) { 
copy("initial_userSettings.php", "userSettings.php");}
include_once ('dvmCombinedData.php');
include_once ('webserver_ip_address.php');
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
  <meta itemprop="name" content="Private Weather Station <?php echo $stationlocation;?>">
  <meta content="Home weather station providing current weather conditions for <?php echo $stationlocation;?>" name="description">
  <meta itemprop="description" content="Private weather station providing current weather conditions for <?php echo $stationlocation;?>">
  <meta content="DivumWX" name="author">
  <meta content="place" property="og:type">
  <meta content="INDEX,FOLLOW" name="robots">
  <meta name="theme-color" content="#ffffff">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name=apple-mobile-web-app-title content="WEATHER STATION">
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
<div class="divum2-container">
  <div class="container divumwxbox-toparea">
    <!-- position 1 --->
    <div class="divumwxbox clock">
    <div class="divumbox-top-border">
      <div class="title"><?php echo $info;?><?php echo $lang['timeTop'];?></div>
      <div class="value">
        <div id="position1"></div>
      </div>
    </div>
   </div>
    <!-- position 2--->
    <div class="divumwxbox indoor">
     <div class="divumbox-top-border">
      <div class="value">
        <div id="position2"></div>
      </div>
    </div>
   </div>
    <!-- position 3--->
    <div class="divumwxbox earthquake">
     <div class="divumbox-top-border">
           <div class="value">
        <div id="position3"></div>
      </div>
    </div>
   </div>
    <!-- position 4--->
    <div class="divumwxbox alert">
     <div class="divumbox-top-border">
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
<div class="divum-container">
  <div class="divum-item">
    <div id="position5">
    </div>
  </div>
 
  <!-- position 6--->
  <div class="divum-item">
    <div id="position6">
    </div>
  </div>
 
  <!-- position 7--->
  <div class="divum-item">
    <div id="position7">
     </div>
   </div>
  </div>
 
<!-- position 8--->

<div class="divum-container">
  <div class="divum-item">
    <div id="position8">
    </div>
  </div>
 
  <!-- position 9--->
  <div class="divum-item">
    <div id="position9">
    </div>
  </div>
 
  <!-- position 10--->
  <div class="divum-item">
    <div id="position10">
     </div>
    </div>
   </div>
  
<!-- position 11--->
<div class="divum-container"> 
  <div class="divum-item">
    <div id="position11">
    </div>
  </div>
 
  <!--position 12-->
  <div class="divum-item">
    <div id="position12">
    </div>
  </div>
 
  <!--position 13-->
  <div class="divum-item">

    <div id="position13">
      </div>
     </div>
   </div>

<!--end outdoor data-->

<!--footer area for homeweatherstation template warning dont mess with this below this line unless you really know what you are doing-->
<div class="divumfooter-container">
  <div class="divumfooter-item">
    <div class="hardwarelogo1"><a href="http://weewx.com" alt="http://weewx.com" title="http://weewx.com">
        <?php echo '<img src="img/icon-weewx.svg" alt="WeeWX" title="WeeWX" width="150px" height="55px"><div class="hardwarelogo1text"></div>';?></a>
      </div>

    <div class="hardwarelogo2">
      <?php
echo '<a href="https://https://claydonsweather.org.uk/" title="https://claydonsweather.org.uk/" target="_blank"><br><img src="img/divumLogo.svg" width="40px" alt="https://https://claydonsweather.org.uk/" class="homeweatherstationlogo" ><divumwx>Team DivumWX design in progress 2021-' . date('Y') . '</divumwx></a>';?>
    </div>

    <div class="footertext">
      &nbsp;<?php echo $info;?>&nbsp;(<value><?php echo $templateversion;?></value>)&nbsp;<?php echo "WeeWX";?>-(<value>
        <maxred><?php echo $divum["swversion"];?>
      </value>)&nbsp;<?php echo $info . "&nbsp;" . $weatherhardware;?></div>
    <div class="footertext"><a href="https://github.com/steepleian/weewx-divumwx"><?php echo $github;?>&nbsp; WeeWX Version Repository at https://github.com/steepleian/weewx-divumwx &nbsp;<img src="img/flags/<?php echo $flag;?>.svg" width="20px"></a></div>
    <div class="footertext">
      <a href="https://www.aerisweather.com/"><img src="img/aerisweather-attribution-h-<?php echo $theme;?>.png" width="75px"></a></br><a href="https://developer.yr.no/featured-products/forecast/">&nbsp; &nbsp; Meteogram Data by <img src="img/yr.svg" width="14px"></a></br><a href="https://bas.dev/work/meteocons">&nbsp; &nbsp; Animated Icons by <img src="img/bm.svg" width="14px"></a>
    </div>
  </div>
</div>
<div class="menuadmin">
  
  <!-- Top Bar -->
  <header class="menuadmin__header">
    <div class="menutoolbar">
      <div class="menutoolbar__left">
        <button class="menubutton menubutton--primary"></button>
      </div>
      <div class="menutoolbar__center">
        <button class="menubutton menubutton--primary">
          <menutoptitle><?php echo strtoupper($stationlocation); ?>&nbsp; WEATHER STATION</menutoptitle>
        </button>
      </div>
      <div class="menutoolbar__right">
        <menuuptime>
          <?php echo "Server " . (shell_exec('uptime -p')); ?>
        </menuuptime>

            <a href="dvmIndexDashboard.php" title="select dashboard mode"><topbarimperial>D</topbarimperial></a>

      </div>
    </div>
  </header>
<?php 
include_once ('dvmUpdater.php');
include_once ('dvmSideMenu.php');
?>

</html>
