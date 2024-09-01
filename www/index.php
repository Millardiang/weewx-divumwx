<?php
##############################################################################################
#        ________   __  ___      ___  ____  ____  ___      ___    __   __  ___  ___  ___     #
#       |"      "\ |" \|"  \    /"  |("  _||_ " ||"  \    /"  |  |"  |/  \|  "||"  \/"  |    #
#       (.  ___  :)||  |\   \  //  / |   (  ) : | \   \  //   |  |'  /    \:  | \   \  /     #
#       |: \   ) |||:  | \\  \/. ./  (:  |  | . ) /\\  \/.    |  |: /'        |  \\  \/      #
#       (| (___\ |||.  |  \.    //    \\ \__/ // |: \.        |   \//  /\'    |  /\.  \      #
#       |:       :)/\  |\  \\   /     /\\ __ //\ |.  \    /:  |   /   /  \\   | /  \   \     #
#       (________/(__\_|_)  \__/     (__________)|___|\__/|___|  |___/    \___||___/\___|    #
#                                                                                            #
#     Copyright (C) 2023 Ian Millard, Steven Sheeley, Sean Balfour. All rights reserved      #
#      Distributed under terms of the GPLv3.  See the file LICENSE.txt for your rights.      #
#    Issues for weewx-divumwx skin template are only addressed via the issues register at    #
#                    https://github.com/Millardiang/weewx-divumwx/issues                     #
##############################################################################################

  if (!file_exists("./userSettings.php")) {
    copy("./initial_userSettings.php", "./userSettings.php");
  }
  include_once ('dvmCombinedData.php');
  include_once ('webserver_ip_address.php');
  require_once ('admin/assets/classes/geoplugin.class.php');
  include_once ('dvmUpdater.php');
  include_once ('dvmSideMenu.php');    
  //include_once ('dvmAdvisory.php');
  date_default_timezone_set($TZ);
  header('Content-type: text/html; charset=utf-8');
  error_reporting(0);
?>
<!DOCTYPE html>
<head>
  <!--title><?php echo $stationlocation;?> Weather Station</title-->
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
  <link href="css/main.dark.css?version=<?php echo filemtime('css/main.dark.css');?>" rel="stylesheet prefetch">
<html lang="en" >
  <meta charset="UTF-8">
  <title>DivumWX - Proposed Responsive CSS Grid Layout Scheme</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<link rel="stylesheet" href="style.css"><link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css'>
<link rel="stylesheet" href="./css/main.dark.css">
<link rel="stylesheet" href="./css/responsive.css">
</head>
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
<body>

<div class="titlebar">
	<h1>A DivumWX Weather Station</h1>
</div>
  
<div class="alert">
  <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span> 
  <span><strong>  Danger!</strong> Red Warning for Thunder and Lightning.</span>
</div>

  
<section class="card-container">

	<div class="cardP"><div class="module"><div id="position1"></div></div></div>

	<div class="cardP"><div class="module"><div id="position2"></div></div></div>

    <div class="cardP"><div class="module"><div id="position3"></div></div></div>

	<div class="cardP"><div class="module"><div id="position4"></div></div></div>

    <div class="cardP"><div class="module"><div id="position5"></div></div></div>

	<div class="cardP"><div class="module"><div id="position6"></div></div></div>
	
    <div class="cardP"><div class="module"><div id="position7"></div></div></div>

	<div class="cardP"><div class="module"><div id="position8"></div></div></div>

    <div class="cardP"><div class="module"><div id="position9"></div></div></div>

	<div class="cardE"><div class="module"><div id="position10"></div></div></div>

    <div class="cardE"><div class="module"><div id="position11"></div></div></div>

	<div class="cardE"><div class="module"><div id="position12"></div></div></div>
	
    <div class="cardE"><div class="module"><div id="position13"></div></div></div>

	<div class="cardE"><div class="module"><div id="position14"></div></div></div>

    <div class="cardE"><div class="module"><div id="position15"></div></div></div>

	<div class="cardE"><div class="module"><div id="position16"></div></div></div>

    <div class="cardE"><div class="module"><div id="position17"></div></div></div>

	<div class="cardE"><div class="module"><div id="position18"></div></div></div>
    
    <div class="cardE"><div class="module"><div id="position19"></div></div></div>

	<div class="cardE"><div class="module"><div id="position20"></div></div></div>

</section>

<footer>
	<p><em><small>Psst... go ahead and squish your browser!</small></em></p>
</footer>

  
</body>
</html>
