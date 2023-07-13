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
  <meta itemprop="image" content="img/divumMeta.png">
  <meta itemprop="name" content="Private Weather Station <?php echo $stationlocation;?>">
  <meta content="Private weather station providing current weather conditions for <?php echo $stationlocation;?>" name="description">
  <meta itemprop="description" content="Private weather station providing current weather conditions for <?php echo $stationlocation;?>">
  <meta content="DivumWX" name="author">
  <meta content="place" property="og:type">
  <meta content="INDEX,FOLLOW" name="robots">
  <meta name="theme-color" content="#ffffff">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name=apple-mobile-web-app-title content="WEATHER STATION">
  <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1, viewport-fit=cover">


<link rel="apple-touch-icon" sizes="180x180" href="./apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="./favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="./favicon-16x16.png">
<link rel="manifest" href="./site.webmanifest">
<link rel="mask-icon" href="./safari-pinned-tab.svg" color="#5bbad5">
<link rel="shortcut icon" href="./favicon.ico">
<meta name="msapplication-TileColor" content="#da532c">
<meta name="msapplication-config" content="./browserconfig.xml">
<meta name="theme-color" content="#ffffff">

  <link rel="manifest" href="./site.webmanifest">
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

<!-- Top Grid Area-->
<div class="divum2-container">
  <div class="container divumwxbox-toparea">
    <!-- position 1 - Fixed Position --->
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
    <!-- position 4 - Fixed Position --->
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

<!--Main Grid Area-->

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
<?php
if($themelayout == "4" || $themelayout == "5"){?>
<!-- position 14--->
<div class="divum-container">
  <div class="divum-item">
    <div id="position14">
    </div>
  </div>

  <!--position 15-->
  <div class="divum-item">
    <div id="position15">
    </div>
  </div>

  <!--position 16-->
  <div class="divum-item">
    <div id="position16">
      </div>
     </div>
   </div>

<?php
}


if($themelayout == "5"){?>

<!-- position 17--->
<div class="divum-container">
  <div class="divum-item">
    <div id="position17">
    </div>
  </div>

  <!--position 18-->
  <div class="divum-item">
    <div id="position18">
    </div>
  </div>

  <!--position 19-->
  <div class="divum-item">
    <div id="position19">
      </div>
     </div>
   </div>
<?php
}
?>


<!--End Main Grid area-->

<!--footer area -->
<?php

include_once ('dvmFooter.php');

?>

<!--end of footer area -->

<div class="menuadmin">

  <!-- Top Bar -->
  <header class="menuadmin__header">
    <div class="menutoolbar">
      <div class="menutoolbar__left">
        <button class="menubutton menubutton--primary"></button>
      </div>
      <div class="menutoolbar__center">
        <button class="menubutton menubutton--primary">
          <menutoptitle><?php echo strtoupper($stationlocation); ?>  WEATHER STATION</menutoptitle>
        </button>
      </div>
      <div class="menutoolbar__right">
        <menuuptime>
          <?php echo "Operational Since " . $divum["since"]; ?>
        </menuuptime>

            <a href="dvmIndexTablet.php" title="Select Tablet Mode"><topbarbutton>T</topbarbutton></a>

      </div>
    </div>
  </header>
<?php
include_once ('dvmUpdater.php');
include_once ('dvmSideMenu.php');
?>
</html>