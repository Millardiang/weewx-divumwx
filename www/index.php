<?php
//#############################################################################################
//        ________   __  ___      ___  ____  ____  ___      ___    __   __  ___  ___  ___     #
//       |"      "\ |" \|"  \    /"  |("  _||_ " ||"  \    /"  |  |"  |/  \|  "||"  \/"  |    #
//       (.  ___  :)||  |\   \  //  / |   (  ) : | \   \  //   |  |'  /    \:  | \   \  /     #
//       |: \   ) |||:  | \\  \/. ./  (:  |  | . ) /\\  \/.    |  |: /'        |  \\  \/      #
//       (| (___\ |||.  |  \.    //    \\ \__/ // |: \.        |   \//  /\'    |  /\.  \      #
//       |:       :)/\  |\  \\   /     /\\ __ //\ |.  \    /:  |   /   /  \\   | /  \   \     #
//       (________/(__\_|_)  \__/     (__________)|___|\__/|___|  |___/    \___||___/\___|    #
//                                                                                            #
//     Copyright (C) 2023 Ian Millard, Steven Sheeley, Sean Balfour. All rights reserved      #
//      Distributed under terms of the GPLv3.  See the file LICENSE.txt for your rights.      #
//    Issues for weewx-divumwx skin template are only addressed via the issues register at    #
//                    https://github.com/Millardiang/weewx-divumwx/issues                     #
//#############################################################################################
//  Ian Millard 05/11/24 added styling to links for popup charts and records                  #
//                                                                                            #
//#############################################################################################
if (!file_exists("userSettings.php")) {
    copy("initial_userSettings.php", "userSettings.php");
}


include_once ('dvmCombinedData.php');
//include_once ('webserver_ip_address.php');
include ('dvmUpdater.php');

date_default_timezone_set($TZ);
header('Content-type: text/html; charset=utf-8');
error_reporting(0);

?>
<!DOCTYPE html>
<head>
 
  <title><?php
echo $stationlocation;
?> Weather Station</title>
  <!--Google / Search Engine Tags -->
  <meta itemprop="image" content="/my-favicon/favicon.svg">
  <meta itemprop="name" content="Private Weather Station <?php
echo $stationlocation;
?>">
  <meta content="Private weather station providing current weather conditions for <?php
echo $stationlocation;
?>" name="description">
  <meta itemprop="description" content="Private weather station providing current weather conditions for <?php
echo $stationlocation;
?>">
  <meta content="DivumWX" name="author">
  <meta content="place" property="og:type">
  <meta content="INDEX,FOLLOW" name="robots">
  <meta name="theme-color" content="#ffffff">
  <link rel="icon" type="image/png" href="/my-favicon/favicon-96x96.png" sizes="96x96" />
  <link rel="icon" type="image/svg+xml" href="/my-favicon/favicon.svg" />
  <link rel="shortcut icon" href="/my-favicon/favicon.ico" />
  <link rel="apple-touch-icon" sizes="180x180" href="/my-favicon/apple-touch-icon.png" />
  <meta name="apple-mobile-web-app-title" content="DivumWX" />
  <link rel="manifest" href="/my-favicon/site.webmanifest" />  
<!--title>DivumWX - Proposed Responsive CSS Grid Layout Scheme</title-->
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<link rel="stylesheet" href="./css/divumwx.main.css?version=<?php
echo filemtime('./css/divumwx.main.css');
?>" rel="stylesheet prefetch">
<link rel="stylesheet" href="./css/divumwx.themes.css?version=<?php
echo filemtime('./css/divumwx.themes.css');
?>" rel="stylesheet prefetch">
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
<body>
  <!--start of header section-->
  <!--?php include('aheader.html');?-->
  <!-- start of theme switch -->
  <div class="theme-switch-wrapper">
      <label class="theme-switch" for="checkbox">
    <input type="checkbox" id="checkbox" />
    <div class="slider round"></div>
  </label>
    
      
  </div>
<!-- end of theme switch -->      

  <div class="titlebar"style="background-color:transparent;">
  <div class="titlebar-item">
   </div>
  <div class="titlebar-item-center">
	        <div class="stationLongname">
          <div class="headerflag"><object data="./img/flags/<?php
echo $flag;
?>.svg" style="width:20px;"></object>&nbsp;&nbsp;<?php
echo $stationlocation;
?>&nbsp; Local Weather&nbsp;&nbsp;<object data="./img/flags/<?php
echo $flag;
?>.svg" width="20px"></object></div>         
                 </div> 
        <div class="stationShortname">
          <div class="headerflag"><object data="./img/flags/<?php
echo $flag;
?>.svg" width="20px"></object>&nbsp;&nbsp;<?php
echo $manifestShortName;
?></object></div>         
                 </div> 
</div>
          <div class="titlebar-item"></div>
          </div>
<!--end of header section-->
<!--start of alert section-->

        <?php
include ("advisoryRegions.php");
error_reporting(0);
?>
          

<!--end of alert section-->
<!--start of grid section--> 
<style>a:link{color:var(--col-6);}a:visited{color:var(--col-6);}a:hover{color:var(--col-22);}a:active{color:blue;}</style>
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


</section>
<!--end of grid section-->
<!--start of footer section-->

<div class="titlebar" style="padding: 10px;  height: 105px;">
<!--section1-->
<div class="stationLongname">
  <div class="titlebar-item"> 
<div class="divumwxLogoFooter" style="width: 60px; margin-top: 1px;">
<a href="https://divumwxweather.org/" title="https://divumwxweather.org/" target="_blank"><?php echo $divumwxLogo2;?>
<!--div class="divumwxLogoFooter-text-block" style="padding-left: 4px;"><?php
echo '<a>Copyright &copy; 2022-' . date('Y') . '<br>Team DivumWX<br>All rights reserved</divumwx></a>';
?> </div-->

</div></div>
</div>
<!--end section1-->
<!--section2-->

<div class="titlebar-item-center" style="font-size:11px;">
        <p><red><?php
echo "Never base important decisions that could result in harm to people or property on this weather information." ?></red></p>
<p><?php
echo "Operational Since " . $divum["since"] . " - ";
$info;
?> <?php
echo $templateversion;
?> <?php
echo " - WeeWX";
?>(<?php
echo $divum["swversion"];
?>)  - OS- <?php
echo " " . $os_version." - PHP( " . substr($phpVersion, 0, 7);
?>)</value></p>        
<div class="stationLongname" ><a href="https://www.xweather.com/" target="_blank" title="Forecasts Powered by Vaisala Xweather"><?php echo $vaisalaLogo;?></a><a href="https://developer.yr.no/featured-products/forecast/">    Meteogram Data by <img src="img/yr.svg" width="14px"></a><a href="https://bas.dev/work/meteocons">     Animated Icons by <img src="img/bm.svg" width="14px"></p>
</div>        
</div>
<!--end section2-->
<!--section3-->
<div class="stationLongname" >
<div class="titlebar-item">      <div class="weewxLogoFooter" style="width: 30px;"><a href="http://weewx.com" alt="http://weewx.com" title="http://weewx.com">
          <?php echo $weewxLogo;?>
</a>
      </div>
</div>
          </div>
<!--end section3-->
</div>
          
          
<!--end of footer section-->


          
<script>
const toggleSwitch = document.querySelector('.theme-switch input[type="checkbox"]');
const currentTheme = localStorage.getItem('theme');

if (currentTheme) {
    document.documentElement.setAttribute('data-theme', currentTheme);
  
    if (currentTheme === 'dark') {
        toggleSwitch.checked = true;
        document.cookie = "theme=dark";
        
    }
}

function switchTheme(e) {
    if (e.target.checked) {
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('theme', 'dark','true', 2628000000,'/',false);
        document.cookie = "theme=dark";
        location.reload();
    }
    else {        document.documentElement.setAttribute('data-theme', 'light');
          localStorage.setItem('theme', 'light', 'true', 2628000000, '/',false);
          document.cookie = "theme=light";
          location.reload();
    }    
}

toggleSwitch.addEventListener('change', switchTheme, false);

//pre fetch images
function preload_image(im_url) {
  let img = new Image();
  img.src = im_url;
}

preload_image("./img/icon-weewx.svg");
preload_image("./img/vaisala-xweather-logo-dark.svg");
preload_image("./img/vaisala-xweather-logo-light.svg");

</script>

  
</body>
<?php
include_once ('dvmSideMenu.php');
?>

</html>