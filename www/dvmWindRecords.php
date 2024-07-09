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
$gustColorMax = $color["windGust_max"];
$gustColorYesterdayMax = $color["windGust_yesterday_max"];
$gustColorMonthMax = $color["windGust_month_max"];
$gustColorYearMax = $color["windGust_year_max"];
$gustColorAlltimeMax = $color["windGust_alltime_max"];
$textColor = "White";

if ($theme === "dark") {
    echo '<style>@font-face{font-family: weathertext2; src: url(css/fonts/verbatim-regular.woff) format("woff"), url(fonts/verbatim-regular.woff2) format("woff2"), url(fonts/verbatim-regular.ttf) format("truetype");}html,body{font-size: 13px; font-family: "weathertext2", Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.grid{display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 2fr)); grid-gap: 5px; align-items: stretch; color: white;}.grid > article{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.grid1{display: grid; grid-template-columns: repeat(auto-fill, minmax(100%, 1fr)); grid-gap: 5px; align-items: stretch; color: #f5f7fc; margin-top: 5px;}.grid1 > articlegraph{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; height: 360px;}.divumwxbrowser-footer{flex-basis: auto; height: 35px; background: #ebebeb; background: rgba(56, 56, 60, 1); border-bottom: 0; display: flex; bottom: -20px; width: 97.4%; -webkit-border-bottom-right-radius: 5px; -webkit-border-bottom-left-radius: 5px; -moz-border-radius-bottomright: 5px; -moz-border-radius-bottomleft: 5px; border-bottom-right-radius: 5px; border-bottom-left-radius: 5px;}a{color: white; text-decoration: none;}.divumwxdarkbrowser{position: relative; background: 0; width: 97%; height: 30px; margin: auto; margin-top: -5px; margin-left: 0px; border-top-left-radius: 5px; border-top-right-radius: 5px; padding-top: 10px;}.divumwxdarkbrowser[url]:after{content: attr(url); color: white; font-size: 14px; text-align: center; position: absolute; left: 0; right: 0; top: 0; padding: 4px 15px; margin: 11px 10px 0 auto; font-family: arial; height: 20px;}blue{color: #01a4b4;}orange{color: #009bb4;}orange1{color: rgba(255, 131, 47, 1);}green{color: #aaa;}red{color: #f37867;}red6{color: #d65b4a;}value{color: #fff;}yellow{color: #cc0;}purple{color: #916392;}.windcontainer1{left: 5px; top: 0;}.windtoday,.windtoday10,.windtoday30,.windtoday40,.windtoday60{font-family: weathertext2, Arial, Helvetica, system; width: 4rem; height: 1.25rem; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; font-size: 1rem; padding-top: 5px; color: #fff; border-bottom: 13px solid rgba(56, 56, 60, 1); align-items: center; justify-content: center; text-align: center; border-radius: 3px;}.windcaution,.windtrend{position: absolute; font-size: 1rem;}.windtoday{background: #9aba2f;}.windtoday10{background: rgba(230, 161, 65, 1);}.windtoday30{background: rgba(255, 124, 57, 0.8);}.windtoday40{background: rgba(255, 124, 57, 0.8);}.windtoday60{background: rgba(211, 93, 78, 1);}.windcaution{margin-left: 120px; margin-top: 112px; font-family: Arial, Helvetica, system;}.windtrend{margin-left: 135px; margin-top: 48px; z-index: 1; color: #fff;}smalluvunit{font-size: 0.55rem; font-family: Arial, Helvetica, system;}.almanac{font-size: 1.25em; margin-top: 30px; color: rgba(56, 56, 60, 1); width: 12em;}metricsblue{color: #44a6b5; font-family: "weathertext2", Helvetica, Arial, sans-serif; background: rgba(86, 95, 103, 0.5); -webkit-border-radius: 2px; border-radius: 2px; align-items: center; justify-content: center; font-size: 0.9em; left: 10px; padding: 0 3px 0 3px;}.dvmconvertrain{position: relative; font-size: 0.7em; top: 4px; color: white; margin-left: 5px; margin-top: -2px;}.lotemp{color: white; font-size: 0.65rem;}.hitempy{position: relative; background: 0; color: white; width: 70px; padding: 1px; -webit-border-radius: 2px; border-radius: 2px; margin-top: -34px; margin-left: 52px; padding-left: 3px; line-height: 11px; font-size: 9px;}.actualt{position: relative; left: 0px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: #555555; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: max-content; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: white; align-items: center; justify-content: center; margin-bottom: 10px; top: 0; text-align: center;}.actualw{position: relative; left: 5px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: rgba(74, 99, 111, 0.1); padding: 5px; font-family: Arial, Helvetica, sans-serif; width: max-content; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: #aaa; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}.svgimage{background: rgba(0, 155, 171, 1); -webit-border-radius: 2px; border-radius: 2px;}.actual{position: relative; left: 5px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: 95%; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: #aaa; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}
    </style>';
} elseif ($theme === "light") {
    echo '<style>@font-face{font-family: weathertext2; src: url(css/fonts/verbatim-regular.woff) format("woff"), url(fonts/verbatim-regular.woff2) format("woff2"), url(fonts/verbatim-regular.ttf) format("truetype");}html,body{font-size: 13px; font-family: "weathertext2", Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; background-color: white;}.grid{display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 2fr)); grid-gap: 5px; align-items: stretch; color: #f5f7fc;}.grid > article{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.grid1{display: grid; grid-template-columns: repeat(auto-fill, minmax(100%, 1fr)); grid-gap: 5px; align-items: stretch; color: #f5f7fc; margin-top: 5px;}.grid1 > articlegraph{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; height: 360px;}.divumwxbrowser-footer{flex-basis: auto; height: 35px; background: #ebebeb; background: rgba(56, 56, 60, 1); border-bottom: 0; display: flex; bottom: -20px; width: 97.4%; -webkit-border-bottom-right-radius: 5px; -webkit-border-bottom-left-radius: 5px; -moz-border-radius-bottomright: 5px; -moz-border-radius-bottomleft: 5px; border-bottom-right-radius: 5px; border-bottom-left-radius: 5px;}a{color: black; text-decoration: none;}.divumwxdarkbrowser{position: relative; background: 0; width: 97%; height: 30px; margin: auto; margin-top: -5px; margin-left: 0px; border-top-left-radius: 5px; border-top-right-radius: 5px; padding-top: 10px;}.divumwxdarkbrowser[url]:after{content: attr(url); color: black; font-size: 14px; text-align: center; position: absolute; left: 0; right: 0; top: 0; padding: 4px 15px; margin: 11px 10px 0 auto; font-family: arial; height: 20px;}blue{color: #01a4b4;}orange{color: #009bb4;}orange1{color: rgba(255, 131, 47, 1);}green{color: #aaa;}red{color: #f37867;}red6{color: #d65b4a;}value{color: #fff;}yellow{color: #cc0;}purple{color: #916392;}.windcontainer1{left: 5px; top: 0;}.windtoday,.windtoday10,.windtoday30,.windtoday40,.windtoday60{font-family: weathertext2, Arial, Helvetica, system; width: 4rem; height: 1.25rem; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; font-size: 1rem; padding-top: 5px; color: white; border-bottom: 14px solid #555555; align-items: center; justify-content: center; text-align: center; border-radius: 3px;}.windcaution,.windtrend{position: absolute; font-size: 1rem;}.windtoday{background: #9aba2f;}.windtoday10{background: rgba(230, 161, 65, 1);}.windtoday30{background: rgba(255, 124, 57, 0.8);}.windtoday40{background: rgba(255, 124, 57, 0.8);}.windtoday60{background: rgba(211, 93, 78, 1);}.windcaution{margin-left: 120px; margin-top: 112px; font-family: Arial, Helvetica, system;}.windtrend{margin-left: 135px; margin-top: 48px; z-index: 1; color: #fff;}smalluvunit{font-size: 0.55rem; font-family: Arial, Helvetica, system;}.almanac{font-size: 1.25em; margin-top: 30px; color: rgba(56, 56, 60, 1); width: 12em;}metricsblue{color: #44a6b5; font-family: "weathertext2", Helvetica, Arial, sans-serif; background: rgba(86, 95, 103, 0.5); -webkit-border-radius: 2px; border-radius: 2px; align-items: center; justify-content: center; font-size: 0.9em; left: 10px; padding: 0 3px 0 3px;}.dvmconvertrain{position: relative; font-size: 0.7em; top: 4px; color: white; margin-left: 5px; margin-top: -2px;}.lotemp{color: black; font-size: 0.65rem;}.hitempy{position: relative; background: 0; color: black; width: 70px; padding: 1px; -webit-border-radius: 2px; border-radius: 2px; margin-top: -34px; margin-left: 52px; padding-left: 3px; line-height: 11px; font-size: 9px;}.actualt{position: relative; left: 0px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: #555555; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: max-content; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: white; align-items: center; justify-content: center; margin-bottom: 10px; top: 0; text-align: center;}.actualw{position: relative; left: 5px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: rgba(74, 99, 111, 0.1); padding: 5px; font-family: Arial, Helvetica, sans-serif; width: max-content; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: #aaa; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}.svgimage{background: rgba(0, 155, 171, 1); -webit-border-radius: 2px; border-radius: 2px;}.actual{position: relative; left: 5px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: 95%; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: #aaa; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}
    </style>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>divumwx Windspeed Almanac Information</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
include('windSelect.php');
?>

<main class="grid">
<article>
<div class=actualt>Max Today </div>
<?php
// wind day km/h
	echo "<div class='windtoday' style='background: $gustColorMax;'>",$wind["gust_max"] . "</value>";
	echo "<smalluvunit>".$wind["units"]."</smalluvunit>";
?>
<div></div>

<div class='dvmconvertrain'>
<?php //convert rain
if($wind["units"] =='km/h'){echo number_format($wind["gust_max"]*0.621371,1)." <smalluvunit>mph</smalluvunit";}
if($wind["units"] =='mph'){ echo number_format($wind["gust_max"]*1.60934,1)."<smalluvunit>km/h</smalluvunit>";}
if($wind["units"] =='m/s'){ echo number_format($wind["gust_max"]*3.5999988862317131577,1)."<smalluvunit>km/h</smalluvunit>";}
if($wind["units"] =='kts'){ echo number_format($wind["gust_max"]*1.8519994254280931489,1)."<smalluvunit>km/h</smalluvunit>";}
?>
</div>

<div class="hitempy">
Max Recorded <blue><?php echo $wind["gust_maxtime"];?></blue></div>
</smalluvunit>
</article>
<article>
<div class=actualt>Max Yesterday </div>
<?php
   // wind yesterday km/h
	echo "<div class='windtoday' style='background: $gustColorYesterdayMax;'>",$wind["gust_yesterday_max"] . "</value>";
	echo "<smalluvunit>".$wind["units"]."</smalluvunit>";
?>
<div></div>
<div class='dvmconvertrain'>
<?php //convert rain
if($wind["units"] =='km/h'){echo number_format($wind["gust_yesterday_max"]*0.621371,1)." <smalluvunit>mph</smalluvunit";}
if($wind["units"] =='mph'){ echo number_format($wind["gust_yesterday_max"]*1.60934,1)."<smalluvunit>km/h</smalluvunit>";}
if($wind["units"] =='m/s'){ echo number_format($wind["gust_yesterday_max"]*3.5999988862317131577,1)."<smalluvunit>km/h</smalluvunit>";}
if($wind["units"] =='kts'){ echo number_format($wind["gust_yesterday_max"]*1.8519994254280931489,1)."<smalluvunit>km/h</smalluvunit>";}
?>
</div>

<div class="hitempy">
Max Recorded <br><blue><?php echo $wind["gust_yesterday_maxtime"];?></blue></div>

</article>

<article>
<div class=actualt>Max <?php echo date('F Y')?> </div>
<?php
    // wind month km/h
	echo "<div class='windtoday' style='background: $gustColorMonthMax;'>",$wind["gust_month_max"] . "</value>";
	echo "<smalluvunit>".$wind["units"]."</smalluvunit>";
?>
<div></div>
<div class='dvmconvertrain'>
<?php //convert rain
if($wind["units"] =='km/h'){echo number_format($wind["gust_month_max"]*0.621371,1)." <smalluvunit>mph</smalluvunit";}
if($wind["units"] =='mph'){ echo number_format($wind["gust_month_max"]*1.60934,1)."<smalluvunit>km/h</smalluvunit>";}
if($wind["units"] =='m/s'){ echo number_format($wind["gust_month_max"]*3.5999988862317131577,1)."<smalluvunit>km/h</smalluvunit>";}
if($wind["units"] =='kts'){ echo number_format($wind["gust_month_max"]*1.8519994254280931489,1)."<smalluvunit>km/h</smalluvunit>";}
?>
</div>

<div class="hitempy">
Max Recorded <br><blue><?php echo $wind["gust_month_maxtime"];?></blue></div>

</article>
<article>
<div class=actualt>Max <?php echo date('Y')?> </div>
<?php
	// wind year km/h
	echo "<div class='windtoday' style='background: $gustColorYearMax;'>",$wind["gust_year_max"] . "</value>";
	echo "<smalluvunit>".$wind["units"]."</smalluvunit>";
?>
<div></div>
<div class='dvmconvertrain'>
<?php //convert rain
if($wind["units"] =='km/h'){echo number_format($wind["gust_year_max"]*0.621371,1)." <smalluvunit>mph</smalluvunit";}
if($wind["units"] =='mph'){ echo number_format($wind["gust_year_max"]*1.60934,1)."<smalluvunit>km/h</smalluvunit>";}
if($wind["units"] =='m/s'){ echo number_format($wind["gust_year_max"]*3.5999988862317131577,1)."<smalluvunit>km/h</smalluvunit>";}
if($wind["units"] =='kts'){ echo number_format($wind["gust_year_max"]*1.8519994254280931489,1)."<smalluvunit>km/h</smalluvunit>";}
?>
</div>

<div class="hitempy">
Max Recorded <br><blue><?php echo $wind["gust_year_maxtime"];?></blue></div>

</article>
<article>
<div class=actualt>Max All-Time</div>
<?php
	// wind year km/h
	echo "<div class='windtoday' style='background: $gustColorAlltimeMax;'>",$wind["gust_alltime_max"] . "</value>";
	echo "<smalluvunit>".$wind["units"]."</smalluvunit>";
?>
<div></div>
<div class='dvmconvertrain'>
<?php //convert rain
if($wind["units"] =='km/h'){echo number_format($wind["gust_alltime_max"]*0.621371,1)." <smalluvunit>mph</smalluvunit";}
if($wind["units"] =='mph'){ echo number_format($wind["gust_alltime_max"]*1.60934,1)."<smalluvunit>km/h</smalluvunit>";}
if($wind["units"] =='m/s'){ echo number_format($wind["gust_alltime_max"]*3.5999988862317131577,1)."<smalluvunit>km/h</smalluvunit>";}
if($wind["units"] =='kts'){ echo number_format($wind["gust_alltime_max"]*1.8519994254280931489,1)."<smalluvunit>km/h</smalluvunit>";}
?>
</div>

<div class="hitempy">
Max Recorded <br><blue><?php echo $wind["gust_alltime_maxtime"];?></blue></div>

</article>  
</main>
<main class="grid1">
<articlegraph style="margin-top: 10px; background-color: transparent; height: 420px">
<iframe  src="dvmhighcharts/windSmallCharts.php?chart='windsmallplot'&span='yearly'&temp='<?php echo $temp['units'];?>'&pressure='<?php echo $barom['units'];?>'&wind='<?php echo $wind['units'];?>'&rain='<?php echo $rain['units']?>" frameborder="0" scrolling="no" width="100%" height="120%"></iframe>
</articlegraph> 

</main>
</html>