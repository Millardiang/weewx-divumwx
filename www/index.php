<?php
#####################################################################################################################
# index.php                                                                                                         #
#                                                                                                                   #
# weewx-divumwx Skin Template maintained by The DivumWX Team                                                        #
#                                                                                                                   #
# Copyright (C) 2023 Ian Millard, Steven Sheeley, Sean Balfour                                                      #
#                                                                                                                   #
# Distributed under terms of the GPLv3. See the file LICENSE.txt for your rights.                                   #
#                                                                                                                   #
# Issues for weewx-divumwx skin template should be addressed to https://github.com/Millardiang/weewx-divumwx/issues # 
#                                                                                                                   #
#####################################################################################################################

  if (!file_exists("userSettings.php")) {
    copy("initial_userSettings.php", "userSettings.php");
  }
  include_once ('dvmCombinedData.php');
  include_once ('webserver_ip_address.php');
  date_default_timezone_set($TZ);
  header('Content-type: text/html; charset=utf-8');
  error_reporting(0);
?>
<!DOCTYPE html>
<style>
.headerflag {
    margin-left: 270px;
    margin-top: -14.5px;
}
</style>
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
  <!-- Row 1 -->
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
<!-- Row 2 -->
<div class="divum-container">
  <!-- position 5--->
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
<div class="divum-container">
<!-- Row 3 -->
  <!-- position 8--->
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
<div class="divum-container">
<!-- Row 4 -->
  <!-- position 11--->
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
<div class="divum-container">
<!-- Row 5 -->
  <!-- position 14--->
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
<div class="divum-container">
<!-- Row 6 -->
  <!-- position 17--->
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
          <menutoptitle><?php echo ($stationlocation); ?>  Weather Station  <div class="headerflag"><img src="./img/flags/<?php echo $flag?>.svg"  width="20px"></div></menutoptitle>
        </button>
      </div>
      <div class="menutoolbar__right">
            <a href="dvmIndexTablet.php" title="Select Tablet Mode"><topbarbutton>T</topbarbutton></a>
      </div>
    </div>
  </header>
    <?php
      //Add visits by country to admin database. No personal info is kept by this, ip is discarded
      $geoplugin = new geoPlugin();
      $geoplugin->locate($_SERVER['REMOTE_ADDR']);
      $countryCode = $geoplugin->countryCode;
      $regionName = $geoplugin->regionName;
      $cityCode = $geoplugin->city;
      $lat = $geoplugin->latitude;
      $long = $geoplugin->longitude;
      $adminDB = __DIR__ . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR . 'dvmAdmin.db3';
      try {
          $db = new PDO("sqlite:" . $adminDB);
          $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          $regionName = empty($regionName) ? "Unknown" : $regionName;
          $cityCode = empty($cityCode) ? "Unknown" : $cityCode;
          $query = $db->prepare("SELECT * FROM visits WHERE countryCode = :countryCode AND regionName = :regionName AND cityName = :cityName");
          $query->bindValue(':countryCode', $countryCode, PDO::PARAM_STR);
          $query->bindValue(':regionName', $regionName, PDO::PARAM_STR);
          $query->bindValue(':cityName', $cityCode, PDO::PARAM_STR);
          $query->execute();
          $row = $query->fetch(PDO::FETCH_ASSOC);
          if ($row) {
              $updateStmt = $db->prepare("UPDATE visits SET visit_count = visit_count + 1 WHERE countryCode = :countryCode AND regionName = :regionName AND cityName = :cityName");
              $updateStmt->bindValue(':countryCode', $countryCode, PDO::PARAM_STR);
              $updateStmt->bindValue(':regionName', $regionName, PDO::PARAM_STR);
              $updateStmt->bindValue(':cityName', $cityCode, PDO::PARAM_STR);
              $updateStmt->execute();
          } else {
              // Entry does not exist, insert a new one with visit_count set to 1
              $insertStmt = $db->prepare("INSERT INTO visits (countryCode, regionName, cityName, lat, long, visit_count) VALUES (:countryCode, :regionName, :cityName, :lat, :long, 1)");
              $insertStmt->bindValue(':countryCode', $countryCode, PDO::PARAM_STR);
              $insertStmt->bindValue(':regionName', $regionName, PDO::PARAM_STR);
              $insertStmt->bindValue(':cityName', $cityCode, PDO::PARAM_STR);
              $insertStmt->bindValue(':lat', $lat, PDO::PARAM_STR);
              $insertStmt->bindValue(':long', $long, PDO::PARAM_STR);
              $insertStmt->execute();
          }
          $db = null;
      } catch (PDOException $e) {
          echo "Database error: " . $e->getMessage();
          exit;
      }
  	  include_once ('dvmUpdater.php');
  	  include_once ('dvmSideMenu.php');
    ?>
</html>