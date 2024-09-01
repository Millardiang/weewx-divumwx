<?php
#####################################################################################################################                                                                                                        #
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


if ($theme === "dark") {
    echo '<style>@font-face{font-family: weathertext2; src: url(css/fonts/verbatim-regular.woff) format("woff"), url(fonts/verbatim-regular.woff2) format("woff2"), url(fonts/verbatim-regular.ttf) format("truetype");}html,body{font-size: 13px; font-family: "weathertext2", Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; background: rgba(40, 45, 52,.4);}.grid{display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); grid-gap: 5px; align-items: stretch; color: #f5f7fc; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.grid > article{border: 1px solid #212428; box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.3); padding: 4px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.grid1{display: grid; grid-template-columns: repeat(auto-fill, minmax(100%, 1fr)); grid-gap: 5px; color: #f5f7fc;}.grid1 > articlegraph{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; height: 225px;}/* unvisited link */a:link{color: white;}/* visited link */a:visited{color: white;}/* mouse over link */a:hover{color: white;}/* selected link */a:active{color: white;}.divumwxdarkbrowser{position: relative; background: 0; width: 97%; height: 30px; margin: auto; margin-top: -5px; margin-left: 0px; border-top-left-radius: 5px; border-top-right-radius: 5px; padding-top: 10px;}.divumwxdarkbrowser[url]:after{content: attr(url); color: white; font-size: 14px; text-align: center; position: absolute; left: 0; right: 0; top: 0; padding: 4px 15px; margin: 11px 10px 0 auto; font-family: arial; height: 20px;}blue{color: #01a4b4;}orange{color: #009bb4;}orange1{position: relative; color: #009bb4; margin: 0 auto; text-align: center; margin-left: 5%; font-size: 1.1rem;}green{color: #aaa;}red{color: #f37867;}red6{color: #d65b4a;}value{color: #fff;}yellow{color: #cc0;}purple{color: #916392;}.temperaturecontainer1{position: relative; left: 0px; margin-top: 0px;}.temperaturecontainer2{position: relative; left: 0px; margin-top: 0px;}.temperaturetoday0,.temperaturetoday10,.temperaturetoday18,.temperaturetoday24,.temperaturetoday30{font-family: weathertext2, Arial, Helvetica, system; width: 5rem; height: 1.5rem; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; display: flex; font-size: 0.9rem; padding-top: 2px; color: #fff; border-bottom: 7px solid #555555; align-items: center; justify-content: center; border-radius: 3px; margin-bottom: 10px;}.temperaturecaution,.temperaturetrend,.temperaturetrend1{position: absolute; font-size: 1rem;}.temperaturetoday0{background: rgba(68, 166, 181, 1);}.temperaturetoday10{background: rgba(144, 177, 42, 1);}.temperaturetoday18{background: rgba(230, 161, 65, 1);}.temperaturetoday24{background: rgba(255, 124, 57, 1);}.temperaturetoday30{background: rgba(211, 93, 78, 1);}.temperaturetrend{margin-left: 67px; margin-top: -38px; z-index: 1; color: white; font-size: 0.65rem; width: 70px; text-align: center;}.temperaturetrend1{margin-left: 67px; margin-top: -38px; z-index: 1; color: #fff; font-size: 0.65rem; width: 70px; text-align: center;}smalluvunit{font-size: 0.65rem; font-family: Arial, Helvetica, system;}.dvmconvertrain{position: relative; font-size: 0.5em; top: 10px; color: #c0c0c0; margin-left: 5px;}.hitempy{position: relative; background: rgba(61, 64, 66, 0.5); color: #aaa; width: 40px; padding: 1px; -webit-border-radius: 2px; border-radius: 2px; margin-top: -40px; margin-left: 130px; padding-left: 3px; line-height: 11px; font-size: 8px;}.actualt{position: relative; left: 0px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: #555555; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: max-content; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: white; align-items: center; justify-content: center; margin-bottom: 10px; top: 0; text-align: center;}
    </style>';
} elseif ($theme === "light") {
    echo '<style>@font-face{font-family: weathertext2; src: url(css/fonts/verbatim-regular.woff) format("woff"), url(fonts/verbatim-regular.woff2) format("woff2"), url(fonts/verbatim-regular.ttf) format("truetype");}html,body{font-size: 13px; font-family: "weathertext2", Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; background-color: white;}.grid{display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); grid-gap: 5px; align-items: stretch; color: #f5f7fc; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.grid > article{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 4px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.grid1{display: grid; grid-template-columns: repeat(auto-fill, minmax(100%, 1fr)); grid-gap: 5px; color: black;}.grid1 > articlegraph{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 4px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; height: 225px;}/* unvisited link */a:link{color: black;}/* visited link */a:visited{color: black;}/* mouse over link */a:hover{color: black;}/* selected link */a:active{color: black;}.divumwxdarkbrowser{position: relative; background: 0; width: 97%; height: 30px; margin: auto; margin-top: -5px; margin-left: 0px; border-top-left-radius: 5px; border-top-right-radius: 5px; padding-top: 10px;}.divumwxdarkbrowser[url]:after{content: attr(url); color: black; font-size: 14px; text-align: center; position: absolute; left: 0; right: 0; top: 0; padding: 4px 15px; margin: 11px 10px 0 auto; font-family: arial; height: 20px;}blue{color: #01a4b4;}orange{color: #009bb4;}orange1{position: relative; color: #009bb4; margin: 0 auto; text-align: center; margin-left: 5%; font-size: 1.1rem;}green{color: #aaa;}red{color: #f37867;}red6{color: #d65b4a;}value{color: #fff;}yellow{color: #cc0;}purple{color: #916392;}.temperaturecontainer1{position: relative; left: 0px; margin-top: 0px;}.temperaturecontainer2{position: relative; left: 0px; margin-top: 0px;}.temperaturetoday0,.temperaturetoday10,.temperaturetoday18,.temperaturetoday24,.temperaturetoday30{font-family: weathertext2, Arial, Helvetica, system; width: 5rem; height: 1.5rem; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; display: flex; font-size: 0.9rem; padding-top: 2px; color: #fff; border-bottom: 7px solid #555555; align-items: center; justify-content: center; border-radius: 3px; margin-bottom: 10px;}.temperaturecaution,.temperaturetrend,.temperaturetrend1{position: absolute; font-size: 1rem;}.temperaturetoday0{background: rgba(68, 166, 181, 1);}.temperaturetoday10{background: rgba(144, 177, 42, 1);}.temperaturetoday18{background: rgba(230, 161, 65, 1);}.temperaturetoday24{background: rgba(255, 124, 57, 1);}.temperaturetoday30{background: rgba(211, 93, 78, 1);}.temperaturetrend{margin-left: 67px; margin-top: -38px; z-index: 1; color: black; font-size: 0.65rem; width: 70px; text-align: center;}.temperaturetrend1{margin-left: 67px; margin-top: -38px; z-index: 1; color: #fff; font-size: 0.65rem; width: 70px; text-align: center;}smalluvunit{font-size: 0.65rem; font-family: Arial, Helvetica, system;}.dvmconvertrain{position: relative; font-size: 0.5em; top: 10px; color: #c0c0c0; margin-left: 5px;}.hitempy{position: relative; background: rgba(61, 64, 66, 0.5); color: #aaa; width: 40px; padding: 1px; -webit-border-radius: 2px; border-radius: 2px; margin-top: -40px; margin-left: 130px; padding-left: 3px; line-height: 11px; font-size: 8px;}.actualt{position: relative; left: 0px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: #555555; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: max-content; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: white; align-items: center; justify-content: center; margin-bottom: 10px; top: 0; text-align: center;}
    </style>';
}
include('tempSelect.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>divumwx Temperature Almanac Information</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
            
<main class="grid">
  <article>
   <div class=actualt> Today </div>
   <div class="temperaturecontainer1">

        <?php

  ////temp max today
  
  echo "<div class='temperaturetoday24' style='background:$colorOutTempDayMax;'>".$temp["outside_day_max"] . "</value>";
  echo "<smalluvunit>˚".$temp["units"]."</smalluvunit>";
  ?> </div>

    <div class="temperaturetrend"><span style='color:rgba(255, 124, 57, 1.000)'><b>Temp Max</b></span><br><?php echo $temp["outside_day_maxtime"];?></span></div>
  </div>  
<div class="temperaturecontainer2">
 <?php
  //temp min today
  
  echo "<div class='temperaturetoday0' style='background:$colorOutTempDayMin;'>",$temp["outside_day_min"] . "</value>";

  echo "<smalluvunit>˚".$temp["units"]."</smalluvunit>";
  ?>  </div>
<div class="temperaturetrend"><span style='color:rgba(68, 166, 181, 1.000)'><b>Temp Min</b></span><br><?php echo $temp["outside_day_mintime"];?></span></div>
</div>  
  
   <div class="temperaturecontainer1">

        <?php
  ////dew max today
  
  echo "<div class='temperaturetoday24'style='background:$colorDewpointDayMax;'>",$dew["day_max"] . "</value>";
  echo "<smalluvunit>˚".$temp["units"]."</smalluvunit>";
  ?> </div>

    <div class="temperaturetrend"><span style='color:rgba(255, 124, 57, 1.000)'><b>Dew Max</b></span><br><?php echo $dew["day_maxtime"];?></span></div>
  </div>
<div class="temperaturecontainer2">
 <?php
  //dew min today
  
  echo "<div class='temperaturetoday0' style='background:$colorDewpointDayMax;'>",$dew["day_min"] . "</value>";

  echo "<smalluvunit>˚".$temp["units"]."</smalluvunit>";
  ?>  </div>
<div class="temperaturetrend"><span style='color:rgba(68, 166, 181, 1.000)'><b>Dew Min</b></span><br><?php echo $dew["day_mintime"];?></span></div>
</div>
<div class="temperaturecontainer1">

        <?php
  ////humidity max today
  
  echo "<div class='temperaturetoday24' style='background:$colorHumidityDayMax;'>",$humid["day_max"] . "</value>";
  echo "<smalluvunit>%</smalluvunit>";
  ?> </div>

    <div class="temperaturetrend"><span style='color:rgba(255, 124, 57, 1.000)'><b>Hum Max</b></span><br><?php echo $humid["day_maxtime"];?></span></div>
</div>    
<div class="temperaturecontainer2">
 <?php
  //humidity min today
  
  echo "<div class='temperaturetoday0' style='background:$colorHumidityDayMin;'>",$humid["day_min"] . "</value>";

  echo "<smalluvunit>%</smalluvunit>";
  ?>  </div>
<div class="temperaturetrend"><span style='color:rgba(68, 166, 181, 1.000)'><b>Hum Min</b></span><br><?php echo $humid["day_mintime"];?></span></div>
</div>  
</article>

 <article>
   <div class=actualt> Yesterday </div>
   <div class="temperaturecontainer1">

        <?php
  ////temp max yesterday
  
  echo "<div class='temperaturetoday24' style='background:$colorOutTempYesterdayMax;'>",$temp["outside_yesterday_max"] . "</value>";
  echo "<smalluvunit>˚".$temp["units"]."</smalluvunit>";
  ?> </div>

    <div class="temperaturetrend"><span style='color:rgba(255, 124, 57, 1.000)'><b>Temp Max</b></span><br><?php echo $temp["outside_yesterday_maxtime"];?></span></div>
  </div>
<div class="temperaturecontainer2">
 <?php
  //temp min yesterday
  
  echo "<div class='temperaturetoday0' style='background:$colorOutTempYesterdayMin;'>",$temp["outside_yesterday_min"] . "</value>";

  echo "<smalluvunit>˚".$temp["units"]."</smalluvunit>";
  ?>  </div>
<div class="temperaturetrend"><span style='color:rgba(68, 166, 181, 1.000)'><b>Temp Min</b></span><br><?php echo $temp["outside_yesterday_mintime"];?></span></div>
  </div>
  
   <div class="temperaturecontainer1">

        <?php
  ////dew max yesterday
  
  echo "<div class='temperaturetoday24' style='background:$colorDewpointYesterdayMax;'>",$dew["yesterday_max"] . "</value>";
  echo "<smalluvunit>˚".$temp["units"]."</smalluvunit>";
  ?> </div>

    <div class="temperaturetrend"><span style='color:rgba(255, 124, 57, 1.000)'><b>Dew Max</b></span><br><?php echo $dew["yesterday_maxtime"];?></span></div>
  </div>
<div class="temperaturecontainer2">
 <?php
  //dew min yesterday
  
  echo "<div class='temperaturetoday0' style='background:$colorDewpointYesterdayMin;'>",$dew["yesterday_min"] . "</value>";

  echo "<smalluvunit>˚".$temp["units"]."</smalluvunit>";
  ?>  </div>
<div class="temperaturetrend"><span style='color:rgba(68, 166, 181, 1.000)'><b>Dew Min</b></span><br><?php echo $dew["yesterday_mintime"];?></span></div>
</div>
<div class="temperaturecontainer1">

        <?php
  ////humidity max yesterday
  
  echo "<div class='temperaturetoday24' style='background:$colorHumidityYesterdayMax;'>",$humid["yesterday_max"] . "</value>";
  echo "<smalluvunit>%</smalluvunit>";
  ?> </div>

    <div class="temperaturetrend"><span style='color:rgba(255, 124, 57, 1.000)'><b>Hum Max</b></span><br><?php echo $humid["yesterday_maxtime"];?></span></div>
  </div>  
<div class="temperaturecontainer2">
 <?php
  //humidity min yesterday
  
  echo "<div class='temperaturetoday0' style='background:$colorHumidityYesterdayMin;'>",$humid["yesterday_min"] . "</value>";

  echo "<smalluvunit>%</smalluvunit>";
  ?>  </div>
<div class="temperaturetrend"><span style='color:rgba(68, 166, 181, 1.000)'><b>Hum Min</b></span><br><?php echo $humid["yesterday_mintime"];?></span></div>
</div>
</article>


  <article>
  <div class=actualt> <?php echo date('F Y')?> </div>
   <div class="temperaturecontainer1">

        <?php

  ////temp max today
  
  echo "<div class='temperaturetoday24' style='background:$colorOutTempMonthMax;'>",$temp["outside_month_max"] . "</value>";
  echo "<smalluvunit>˚".$temp["units"]."</smalluvunit>";
  ?> </div>

    <div class="temperaturetrend"><span style='color:rgba(255, 124, 57, 1.000)'><b>Temp Max</b></span><br><?php echo $temp["outside_month_maxtime"];?></span></div>
  </div>  
<div class="temperaturecontainer2">
 <?php
  //temp min today
  
  echo "<div class='temperaturetoday0' style='background:$colorOutTempMonthMin;'>",$temp["outside_month_min"] . "</value>";

  echo "<smalluvunit>˚".$temp["units"]."</smalluvunit>";
  ?>  </div>
<div class="temperaturetrend"><span style='color:rgba(68, 166, 181, 1.000)'><b>Temp Min</b></span><br><?php echo $temp["outside_month_mintime"];?></span></div>
</div>  
  
   <div class="temperaturecontainer1">

        <?php
  ////dew max month
  
  echo "<div class='temperaturetoday24' style='background:$colorDewpointMonthMax;'>",$dew["month_max"] . "</value>";
  echo "<smalluvunit>˚".$temp["units"]."</smalluvunit>";
  ?> </div>

    <div class="temperaturetrend"><span style='color:rgba(255, 124, 57, 1.000)'><b>Dew Max</b></span><br><?php echo $dew["month_maxtime"];?></span></div>
  </div>
<div class="temperaturecontainer2">
 <?php
  //dew min month
  
  echo "<div class='temperaturetoday0' style='background:$colorDewpointMonthMin;'>",$dew["month_min"] . "</value>";

  echo "<smalluvunit>˚".$temp["units"]."</smalluvunit>";
  ?>  </div>
<div class="temperaturetrend"><span style='color:rgba(68, 166, 181, 1.000)'><b>Dew Min</b></span><br><?php echo $dew["month_mintime"];?></span></div>
</div>
<div class="temperaturecontainer1">

        <?php
  ////humidity max month
  
  echo "<div class='temperaturetoday24' style='background:$colorHumidityMonthMax;'>",$humid["month_max"] . "</value>";
  echo "<smalluvunit>%</smalluvunit>";
  ?> </div>

    <div class="temperaturetrend"><span style='color:rgba(255, 124, 57, 1.000)'><b>Hum Max</b></span><br><?php echo $humid["month_maxtime"];?></span></div>
  </div>  
<div class="temperaturecontainer2">
 <?php
  //humidity min month
 
  echo "<div class='temperaturetoday0' style='background:$colorHumidityMonthMin;'>",$humid["month_min"] . "</value>";

  echo "<smalluvunit>%</smalluvunit>";
  ?>  </div>
<div class="temperaturetrend"><span style='color:rgba(68, 166, 181, 1.000)'><b>Hum Min</b></span><br><?php echo $humid["month_mintime"];?></span></div>
</div>
</article>


   <article>
   <div class=actualt> <?php echo date('Y')?> </div>
   <div class="temperaturecontainer1">

        <?php
  ////temp max year
  
  echo "<div class='temperaturetoday24' style='background:$colorOutTempYearMax;'>",$temp["outside_year_max"] . "</value>";
  echo "<smalluvunit>˚".$temp["units"]."</smalluvunit>";
  ?> </div>

    <div class="temperaturetrend"><span style='color:rgba(255, 124, 57, 1.000)'><b>Temp Max</b></span><br><?php echo $temp["outside_year_maxtime"];?></span></div>
  </div>
<div class="temperaturecontainer2">
 <?php
  //temp min year
  
  echo "<div class='temperaturetoday0' style='background:$colorOutTempYearMin;'>",$temp["outside_year_min"] . "</value>";

  echo "<smalluvunit>˚".$temp["units"]."</smalluvunit>";
  ?>  </div>
<div class="temperaturetrend"><span style='color:rgba(68, 166, 181, 1.000)'><b>Temp Min</b></span><br><?php echo $temp["outside_year_mintime"];?></span></div>
  </div>
  
   <div class="temperaturecontainer1">

        <?php
  ////dew max year
  
  echo "<div class='temperaturetoday24' style='background:$colorDewpointYearMax;'>",$dew["year_max"] . "</value>";
  echo "<smalluvunit>˚".$temp["units"]."</smalluvunit>";
  ?> </div>

    <div class="temperaturetrend"><span style='color:rgba(255, 124, 57, 1.000)'><b>Dew Max</b></span><br><?php echo $dew["year_maxtime"];?></span></div>
  </div>
<div class="temperaturecontainer2">
 <?php
  //dew min year
  
  echo "<div class='temperaturetoday0' style='background:$colorDewpointYearMin;'>",$dew["year_min"] . "</value>";

  echo "<smalluvunit>˚".$temp["units"]."</smalluvunit>";
  ?>  </div>
<div class="temperaturetrend"><span style='color:rgba(68, 166, 181, 1.000)'><b>Dew Min</b></span><br><?php echo $dew["year_mintime"];?></span></div>
</div>
<div class="temperaturecontainer1">

        <?php
  ////humidity max year
  
  echo "<div class='temperaturetoday24' style='background:$colorHumidityYearMax;'>",$humid["year_max"] . "</value>";
  echo "<smalluvunit>%</smalluvunit>";
  ?> </div>

    <div class="temperaturetrend"><span style='color:rgba(255, 124, 57, 1.000)'><b>Hum Max</b></span><br><?php echo $humid["year_maxtime"];?></span></div>
  </div>  
<div class="temperaturecontainer2">
 <?php
  //humidity min year
  
  echo "<div class='temperaturetoday0' style='background:$colorHumidityYearMin;'>",$humid["year_min"] . "</value>";

  echo "<smalluvunit>%</smalluvunit>";
  ?>  </div>
<div class="temperaturetrend"><span style='color:rgba(68, 166, 181, 1.000)'><b>Hum Min</b></span><br><?php echo $humid["year_mintime"];?></span></div>
</div>
</article>


<article>
   <div class=actualt> All-Time </div>
   <div class="temperaturecontainer1">

        <?php
  ////temp max all
  
  echo "<div class='temperaturetoday24' style='background:$colorOutTempAlltimeMax;'>",$temp["outside_alltime_max"] . "</value>";
  echo "<smalluvunit>˚".$temp["units"]."</smalluvunit>";
  ?> </div>

    <div class="temperaturetrend"><span style='color:rgba(255, 124, 57, 1.000)'><b>Temp Max</b></span><br><?php echo $temp["outside_alltime_maxtime"];?></span></div>
  </div>
<div class="temperaturecontainer2">
 <?php
  //temp min all
  
  echo "<div class='temperaturetoday0' style='background:$colorOutTempAlltimeMin;'>",$temp["outside_alltime_min"] . "</value>";

  echo "<smalluvunit>˚".$temp["units"]."</smalluvunit>";
  ?>  </div>
<div class="temperaturetrend"><span style='color:rgba(68, 166, 181, 1.000)'><b>Temp Min</b></span><br><?php echo $temp["outside_alltime_mintime"];?></span></div>
  </div>
  
   <div class="temperaturecontainer1">

        <?php
  ////dew max all
  
  echo "<div class='temperaturetoday24' style='background:$colorDewpointAlltimeMax;'>",$dew["alltime_max"] . "</value>";
  echo "<smalluvunit>˚".$temp["units"]."</smalluvunit>";
  ?> </div>

    <div class="temperaturetrend"><span style='color:rgba(255, 124, 57, 1.000)'><b>Dew Max</b></span><br><?php echo $dew["alltime_maxtime"];?></span></div>
  </div>
<div class="temperaturecontainer2">
 <?php
  //dew min all
  
  echo "<div class='temperaturetoday0' style='background:$colorDewpointAlltimeMin;'>",$dew["alltime_min"] . "</value>";

  echo "<smalluvunit>˚".$temp["units"]."</smalluvunit>";
  ?>  </div>
<div class="temperaturetrend"><span style='color:rgba(68, 166, 181, 1.000)'><b>Dew Min</b></span><br><?php echo $dew["alltime_mintime"];?></span></div>
</div>
<div class="temperaturecontainer1">

        <?php
  ////humidity max all
  
  echo "<div class='temperaturetoday24' style='background:$colorHumidityAlltimeMax;'>",$humid["alltime_max"] . "</value>";
  echo "<smalluvunit>%</smalluvunit>";
  ?> </div>

    <div class="temperaturetrend"><span style='color:rgba(255, 124, 57, 1.000)'><b>Hum Max</b></span><br><?php echo $humid["alltime_maxtime"];?></span></div>
  </div>  
<div class="temperaturecontainer2">
 <?php
  //humidity min all
  
  echo "<div class='temperaturetoday0' style='background:$colorHumidityAlltimeMin;'>",$humid["alltime_min"] . "</value>";

  echo "<smalluvunit>%</smalluvunit>";
  ?>  </div>
<div class="temperaturetrend"><span style='color:rgba(68, 166, 181, 1.000)'><b>Hum Min</b></span><br><?php echo $humid["alltime_mintime"];?></span></div>
</div>
</article>
</article>   
  </main>
 <main class="grid1">
<articlegraph style="margin-top: 10px; background-color: transparent; height: 232px"> 
     <iframe  src="charts/tempSmallCharts.php?chart='tempsmallplot'&span='yearly'&temp='<?php echo $temp['units'];?>'&pressure='<?php echo $barom['units'];?>'&wind='<?php echo $wind['units'];?>'&rain='<?php echo $rain['rain_units']?>"frameborder="0" scrolling="no" width="99.5%" height="800%"></iframe>   
  </articlegraph> 
   </main>