<!DOCTYPE html>
<?php
include "dvmCombinedData.php"; 
date_default_timezone_set($TZ);
error_reporting(0);

if ($theme === "dark") {
    echo '<style>@font-face{font-family: weathertext2; src: url(css/fonts/verbatim-regular.woff) format("woff"), url(fonts/verbatim-regular.woff2) format("woff2"), url(fonts/verbatim-regular.ttf) format("truetype");}html,body{font-size: 13px; font-family: "weathertext2", Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.grid{display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 2fr)); grid-gap: 5px; align-items: stretch; color: #f5f7fc;}.grid > article{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.grid1{display: grid; grid-template-columns: repeat(auto-fill, minmax(100%, 1fr)); grid-gap: 5px; align-items: stretch; color: #f5f7fc; margin-top: 5px;}.grid1 > articlegraph{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; height: 360px;}/* unvisited link */a:link{color: white;}/* visited link */a:visited{color: white;}/* mouse over link */a:hover{color: white;}/* selected link */a:active{color: white;}.divumwxdarkbrowser{position: relative; background: 0; width: 97%; height: 30px; margin: auto; margin-top: -5px; margin-left: 0px; border-top-left-radius: 5px; border-top-right-radius: 5px; padding-top: 10px;}.divumwxdarkbrowser[url]:after{content: attr(url); color: white; font-size: 14px; text-align: center; position: absolute; left: 0; right: 0; top: 0; padding: 4px 15px; margin: 11px 10px 0 auto; font-family: arial; height: 20px;}a{color: #aaa; text-decoration: none;}blue{color: #01a4b4;}orange{color: #009bb4;}orange1{position: relative; color: #009bb4; margin: 0 auto; text-align: center; margin-left: 5%; font-size: 1.1rem;}green{color: #aaa;}red{color: #f37867;}red6{color: #d65b4a;}value{color: #fff;}yellow{color: #cc0;}purple{color: #916392;}.actualt{position: relative; left: 0px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: #555; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: max-content; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: white; align-items: center; justify-content: center; margin-bottom: 10px; top: 0; text-align: center;}.actual{position: relative; left: 5px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: 95%; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: #aaa; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}<!-- divumwx rain beaker css -- > .rainfallcontainer1{left: 5px; top: 0;}.rainfalltoday1{font-family: weathertext2, Arial, Helvetica, system; width: 4.25rem; height: 1.5rem; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; font-weight: normal; font-size: 0.9rem; padding-top: 5px; color: #fff; border-bottom: 12px solid #555; align-items: center; justify-content: center; text-align: center; border-radius: 3px; background: rgba(68, 166, 181, 1); -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.rainfallcaution,.rainfalltrend{position: absolute; font-size: 1rem;}smalluvunit{font-size: 0.6rem; font-family: Arial, Helvetica, system;}.lotemp{color: white; font-size: 0.6rem;}.almanac{font-size: 1.25em; margin-top: 30px; color: rgba(56, 56, 60, 1); width: 12em;}metricsblue{color: #44a6b5; font-family: "weathertext2", Helvetica, Arial, sans-serif; background: rgba(86, 95, 103, 0.5); -webkit-border-radius: 2px; border-radius: 2px; align-items: center; justify-content: center; font-size: 0.9em; left: 10px; padding: 0 3px 0 3px;}.dvmconvertrain{position: relative; font-size: 0.8em; top: 6px; color: white; margin-left: auto; margin-right: auto;}.hitempy{position: relative; background: rgba(61, 64, 66, 0.5); color: white; width: 75px; padding: 1px; -webit-border-radius: 2px; border-radius: 2px; margin-top: -33px; margin-left: 56px; padding-left: 3px; line-height: 11px; font-size: 9px;}


.rainfalltoday2{font-family: weathertext2, Arial, Helvetica, system; width: 5.25rem; height: 1.5rem; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; font-weight: normal; font-size: 0.9rem; padding-top: 5px; color: #fff; border-bottom: 12px solid #555; align-items: center; justify-content: center; text-align: center; border-radius: 3px; background: rgba(68, 166, 181, 1); -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.rainfallcaution,.rainfalltrend{position: absolute; font-size: 1rem;}smalluvunit{font-size: 0.6rem; font-family: Arial, Helvetica, system;}.lotemp{color: white; font-size: 0.6rem;}.almanac{font-size: 1.25em; margin-top: 30px; color: rgba(56, 56, 60, 1); width: 12em;}metricsblue{color: #44a6b5; font-family: "weathertext2", Helvetica, Arial, sans-serif; background: rgba(86, 95, 103, 0.5); -webkit-border-radius: 2px; border-radius: 2px; align-items: center; justify-content: center; font-size: 0.9em; left: 10px; padding: 0 3px 0 3px;}.dvmconvertrain{position: relative; font-size: 0.8em; top: 6px; color: white; margin-left: auto; margin-right: auto;}.hitempy{position: relative; background: rgba(61, 64, 66, 0.5); color: white; width: 75px; padding: 1px; -webit-border-radius: 2px; border-radius: 2px; margin-top: -33px; margin-left: 56px; padding-left: 3px; line-height: 11px; font-size: 9px;}

    </style>';
} elseif ($theme === "light") {
    echo '<style>@font-face{font-family: weathertext2; src: url(css/fonts/verbatim-regular.woff) format("woff"), url(fonts/verbatim-regular.woff2) format("woff2"), url(fonts/verbatim-regular.ttf) format("truetype");}html,body{font-size: 13px; font-family: "weathertext2", Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; background-color: white;}.grid{display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 2fr)); grid-gap: 5px; align-items: stretch; color: #f5f7fc;}.grid > article{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.grid1{display: grid; grid-template-columns: repeat(auto-fill, minmax(100%, 1fr)); grid-gap: 5px; align-items: stretch; color: #f5f7fc; margin-top: 5px;}.grid1 > articlegraph{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; height: 360px;}/* unvisited link */a:link{color: black;}/* visited link */a:visited{color: black;}/* mouse over link */a:hover{color: black;}/* selected link */a:active{color: black;}.divumwxdarkbrowser{position: relative; background: 0; width: 97%; height: 30px; margin: auto; margin-top: -5px; margin-left: 0px; border-top-left-radius: 5px; border-top-right-radius: 5px; padding-top: 10px;}.divumwxdarkbrowser[url]:after{content: attr(url); color: black; font-size: 14px; text-align: center; position: absolute; left: 0; right: 0; top: 0; padding: 4px 15px; margin: 11px 10px 0 auto; font-family: arial; height: 20px;}a{color: #aaa; text-decoration: none;}blue{color: #01a4b4;}orange{color: #009bb4;}orange1{position: relative; color: #009bb4; margin: 0 auto; text-align: center; margin-left: 5%; font-size: 1.1rem;}green{color: #aaa;}red{color: #f37867;}red6{color: #d65b4a;}value{color: #fff;}yellow{color: #cc0;}purple{color: #916392;}.actualt{position: relative; left: 0px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: #555555; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: max-content; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: white; align-items: center; justify-content: center; margin-bottom: 10px; top: 0; text-align: center;}.actual{position: relative; left: 5px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: 95%; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: #aaa; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}<!-- divumwx rain beaker css -- > .rainfallcontainer1{left: 5px; top: 0;}.rainfalltoday1{font-family: weathertext2, Arial, Helvetica, system; width: 4.25rem; height: 1.5rem; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; font-weight: normal; font-size: 0.9rem; padding-top: 5px; color: #fff; border-bottom: 12px solid #555555; align-items: center; justify-content: center; text-align: center; border-radius: 3px; background: rgba(68, 166, 181, 1); -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.rainfallcaution,.rainfalltrend{position: absolute; font-size: 1rem;}smalluvunit{font-size: 0.6rem; font-family: Arial, Helvetica, system;}.lotemp{color: black; font-size: 0.6rem;}.almanac{font-size: 1.25em; margin-top: 30px; color: rgba(56, 56, 60, 1); width: 12em;}metricsblue{color: #44a6b5; font-family: "weathertext2", Helvetica, Arial, sans-serif; background: rgba(86, 95, 103, 0.5); -webkit-border-radius: 2px; border-radius: 2px; align-items: center; justify-content: center; font-size: 0.9em; left: 10px; padding: 0 3px 0 3px;}.dvmconvertrain{position: relative; font-size: 0.8em; top: 6px; color: white; margin-left: auto; margin-right: auto;}.hitempy{position: relative; background: 0; color: black; width: 75px; padding: 1px; -webit-border-radius: 2px; border-radius: 2px; margin-top: -33px; margin-left: 56px; padding-left: 3px; line-height: 11px; font-size: 9px;}

.rainfalltoday2{font-family: weathertext2, Arial, Helvetica, system; width: 5.25rem; height: 1.5rem; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; font-weight: normal; font-size: 0.9rem; padding-top: 5px; color: #fff; border-bottom: 12px solid #555555; align-items: center; justify-content: center; text-align: center; border-radius: 3px; background: rgba(68, 166, 181, 1); -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.rainfallcaution,.rainfalltrend{position: absolute; font-size: 1rem;}smalluvunit{font-size: 0.6rem; font-family: Arial, Helvetica, system;}.lotemp{color: black; font-size: 0.6rem;}.almanac{font-size: 1.25em; margin-top: 30px; color: rgba(56, 56, 60, 1); width: 12em;}metricsblue{color: #44a6b5; font-family: "weathertext2", Helvetica, Arial, sans-serif; background: rgba(86, 95, 103, 0.5); -webkit-border-radius: 2px; border-radius: 2px; align-items: center; justify-content: center; font-size: 0.9em; left: 10px; padding: 0 3px 0 3px;}.dvmconvertrain{position: relative; font-size: 0.8em; top: 6px; color: white; margin-left: auto; margin-right: auto;}.hitempy{position: relative; background: 0; color: black; width: 75px; padding: 1px; -webit-border-radius: 2px; border-radius: 2px; margin-top: -33px; margin-left: 56px; padding-left: 3px; line-height: 11px; font-size: 9px;}

    </style>';
}
include "LightningSelect.php";
 ?>

 <?php
 if ($lightningSource == 1) {

$lightninglivedata = 'jsondata/NSDRealtime.txt';
$file_live = file_get_contents($lightninglivedata);
$lightningBolt = explode( ',',$file_live);

if (empty($lightningBolt[0])) {
  $lightningBolt[0] = 0;
}
if (empty($lightningBolt[1])) {
  $lightningBolt[1] = 0;
}
if (empty($lightningBolt[2])) {
  $lightningBolt[2] = 0;
}
if (empty($lightningBolt[3])) {
  $lightningBolt[3] = 0;
}
if (empty($lightningBolt[4])) {
  $lightningBolt[4] = 0;
}
if (empty($lightningBolt[5])) {
  $lightningBolt[5] = 0;
}
if (empty($lightningBolt[6])) {
  $lightningBolt[6] = 0;
}
if (empty($lightningBolt[7])) {
  $lightningBolt[7] = 0;
}
if (empty($lightningBolt[8])) {
  $lightningBolt[8] = 0;
}
if (empty($lightningBolt[9])) {
  $lightningBolt[9] = 0;
}
if (empty($lightningBolt[10])) {
  $lightningBolt[10] = 0;
}
if (empty($lightningBolt[11])) {
  $lightningBolt[11] = 0;
}
if (empty($lightningBolt[12])) {
  $lightningBolt[12] = 0;
}
if (empty($lightningBolt[13])) {
  $lightningBolt[13] = 0;
}
if (empty($lightningBolt[14])) {
  $lightningBolt[14] = 0;
}
if (empty($lightningBolt[15])) {
  $lightningBolt[15] = 0;
}
if (empty($lightningBolt[16])) {
  $lightningBolt[16] = 0;
}
if (empty($lightningBolt[17])) {
  $lightningBolt[17] = 0;
}
if (empty($lightningBolt[18])) {
  $lightningBolt[18] = 0;
}
if (empty($lightningBolt[19])) {
  $lightningBolt[19] = 0;
}
if (empty($lightningBolt[20])) {
  $lightningBolt[20] = 0;
}
if (empty($lightningBolt[21])) {
  $lightningBolt[21] = 0;
}
if (empty($lightningBolt[22])) {
  $lightningBolt[22] = 0;
}

$lightning["unixtimestamp"]             = $lightningBolt[0]; // unix timestamp
$lightning["rate_per_min"]              = $lightningBolt[1]; // current rate/min
$lightning["close_rate_per_min"]        = $lightningBolt[2]; // current close rate/min (< 50km)
$lightning['last_time']                 = $lightningBolt[3]; // last strike date and time
$lightning["bearing"]                   = $lightningBolt[4]; // Bearing number
$lightning["bearingx"]                  = $lightningBolt[4]; // Bearing ordinal 
$lightning["last_distance"]             = $lightningBolt[5]; // last strike distance 
$lightning["last_strike_type"]          = $lightningBolt[6]; // last strike type ( CC+, CC-, CG+, CG- )
$lightning["hour_strike_count"]         = $lightningBolt[7]; // strikes last hour
$lightning["today_strikes"]             = $lightningBolt[8]; // strikes today
$lightning["month_strike_count"]        = $lightningBolt[9]; // strikes this month
$lightning["year_strike_count"]         = $lightningBolt[10]; // strikes this year
$lightning["max_rate_per_min"]          = $lightningBolt[11]; // max rate/min
$lightning["max_ratetime"]              = $lightningBolt[12]; // max ratetime (hh:mm)
$lightning["max_burst"]                 = $lightningBolt[13]; // max burst/s
$lightning["max_burst_time"]            = $lightningBolt[14]; // max bursttime (hh:mm)
$lightning["CG+_strikes"]               = $lightningBolt[15]; // CG+ strikes today
$lightning["CG-_strikes"]               = $lightningBolt[16]; // CG- strikes today
$lightning["CC+_strikes"]               = $lightningBolt[17]; // CC+ strikes today
$lightning["CC-_strikes"]               = $lightningBolt[18]; // CC- strikes today
$lightning["uptime"]                    = $lightningBolt[19]; // uptime (x days x hours x mins)
$lightning["unitsx"]                    = $lightningBolt[20]; // km or miles (set in config.ini)
$lightning["persistence"]               = $lightningBolt[21]; // Persistence in minutes set 60 mins
$lightning["nsdcrop"]                   = $lightningBolt[22]; // Max strikes in NSDStrikes file (set to 5000)
}
?>

<head>
    <meta charset="UTF-8">
    <title>Strikes Almanac Information</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<!--div class="divumwxdarkbrowser" url="Lightning Strike Almanac"></div-->

<main class="grid">
<article>
     <div class=actualt>Last Hour</div>
    <?php echo "<div class='rainfalltoday1'>",$lightning["hour_strike_count"]. "</value>"?></div>
</article>

<article>
    <div class=actualt>Strikes Today</div>
    <?php echo "<div class='rainfalltoday1'>",$lightning["today_strike_count"]. "</value>"?></div>
</article>

<article>
    <div class=actualt>Strikes <?php echo date('M Y');?> </div>
    <?php echo "<div class='rainfalltoday1'>",$lightning["month_strike_count"]. "</value>"?></div>
</article>

<article>
     <div class=actualt>Strikes <?php echo date("Y");?> </div>
    <?php echo "<div class='rainfalltoday1'>",$lightning["year_strike_count"]. "</value>"?></div>
</article>

<article>
    <div class=actualt>&nbsp;Strikes All-Time </div>
    <?php echo "<div class='rainfalltoday2'>",$lightning["year_strike_count"] + $lightning["alltime_strike_count"]. "</value>"?></div>
</article> </main>
 <main class="grid1">
<articlegraph style="height:430px">
<iframe width="100%" height="100%" scrolling="no" src="highcharts/dvmLightningRadarChart.php" frameborder="0"></iframe>
</articlegraph>  
</main>
</head>
</html>
