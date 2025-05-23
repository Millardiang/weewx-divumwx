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
include('dvmCombinedData.php');

if ($theme === "dark") {
    echo '<style>@font-face{font-family: weathertext2; src: url(css/fonts/verbatim-regular.woff) format("woff"), url(fonts/verbatim-regular.woff2) format("woff2"), url(fonts/verbatim-regular.ttf) format("truetype");}html,body{font-size: 13px; font-family: "weathertext2", Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.grid{display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 2fr)); grid-gap: 5px; align-items: stretch; color: #f5f7fc;}.grid > article{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.grid1{display: grid; grid-template-columns: repeat(auto-fill, minmax(100%, 1fr)); grid-gap: 5px; align-items: stretch; color: #f5f7fc; margin-top: 5px;}.grid1 > articlegraph{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; height: 360px;}/* unvisited link */a:link{color: white;}/* visited link */a:visited{color: white;}/* mouse over link */a:hover{color: white;}/* selected link */a:active{color: white;}.divumwxdarkbrowser{position: relative; background: 0; width: 97%; height: 30px; margin: auto; margin-top: -5px; margin-left: 0px; border-top-left-radius: 5px; border-top-right-radius: 5px; padding-top: 10px;}.divumwxdarkbrowser[url]:after{content: attr(url); color: white; font-size: 14px; text-align: center; position: absolute; left: 0; right: 0; top: 0; padding: 4px 15px; margin: 11px 10px 0 auto; font-family: arial; height: 20px;}a{color: #aaa; text-decoration: none;}blue{color: #01a4b4;}orange{color: #009bb4;}orange1{position: relative; color: #009bb4; margin: 0 auto; text-align: center; margin-left: 5%; font-size: 1.1rem;}green{color: #aaa;}red{color: #f37867;}red6{color: #d65b4a;}value{color: #fff;}yellow{color: #cc0;}purple{color: #916392;}.actualt{position: relative; left: 0px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: #555; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: max-content; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: white; align-items: center; justify-content: center; margin-bottom: 10px; top: 0; text-align: center;}.actual{position: relative; left: 5px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: 95%; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: #aaa; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}<!-- divumwx rain beaker css -- > .rainfallcontainer1{left: 5px; top: 0;}.rainfalltoday1{font-family: weathertext2, Arial, Helvetica, system; width: 4.25rem; height: 1.5rem; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; font-weight: normal; font-size: 0.9rem; padding-top: 5px; color: #fff; border-bottom: 12px solid #555; align-items: center; justify-content: center; text-align: center; border-radius: 3px; background: rgba(68, 166, 181, 1); -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.rainfallcaution,.rainfalltrend{position: absolute; font-size: 1rem;}smalluvunit{font-size: 0.6rem; font-family: Arial, Helvetica, system;}.lotemp{color: white; font-size: 0.6rem;}.almanac{font-size: 1.25em; margin-top: 30px; color: rgba(56, 56, 60, 1); width: 12em;}metricsblue{color: #44a6b5; font-family: "weathertext2", Helvetica, Arial, sans-serif; background: rgba(86, 95, 103, 0.5); -webkit-border-radius: 2px; border-radius: 2px; align-items: center; justify-content: center; font-size: 0.9em; left: 10px; padding: 0 3px 0 3px;}.dvmconvertrain{position: relative; font-size: 0.8em; top: 6px; color: white; margin-left: auto; margin-right: auto;}.hitempy{position: relative; background: rgba(61, 64, 66, 0.5); color: white; width: 75px; padding: 1px; -webit-border-radius: 2px; border-radius: 2px; margin-top: -33px; margin-left: 56px; padding-left: 3px; line-height: 11px; font-size: 9px;}


.rainfalltoday2{font-family: weathertext2, Arial, Helvetica, system; width: 5.25rem; height: 1.5rem; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; font-weight: normal; font-size: 0.9rem; padding-top: 5px; color: #fff; border-bottom: 12px solid #555; align-items: center; justify-content: center; text-align: center; border-radius: 3px; background: rgba(68, 166, 181, 1); -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.rainfallcaution,.rainfalltrend{position: absolute; font-size: 1rem;}smalluvunit{font-size: 0.6rem; font-family: Arial, Helvetica, system;}.lotemp{color: white; font-size: 0.6rem;}.almanac{font-size: 1.25em; margin-top: 30px; color: rgba(56, 56, 60, 1); width: 12em;}metricsblue{color: #44a6b5; font-family: "weathertext2", Helvetica, Arial, sans-serif; background: rgba(86, 95, 103, 0.5); -webkit-border-radius: 2px; border-radius: 2px; align-items: center; justify-content: center; font-size: 0.9em; left: 10px; padding: 0 3px 0 3px;}.dvmconvertrain{position: relative; font-size: 0.8em; top: 6px; color: white; margin-left: auto; margin-right: auto;}.hitempy{position: relative; background: rgba(61, 64, 66, 0.5); color: white; width: 75px; padding: 1px; -webit-border-radius: 2px; border-radius: 2px; margin-top: -33px; margin-left: 56px; padding-left: 3px; line-height: 11px; font-size: 9px;}

    </style>';
} elseif ($theme === "light") {
    echo '<style>@font-face{font-family: weathertext2; src: url(css/fonts/verbatim-regular.woff) format("woff"), url(fonts/verbatim-regular.woff2) format("woff2"), url(fonts/verbatim-regular.ttf) format("truetype");}html,body{font-size: 13px; font-family: "weathertext2", Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; background-color: white;}.grid{display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 2fr)); grid-gap: 5px; align-items: stretch; color: #f5f7fc;}.grid > article{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.grid1{display: grid; grid-template-columns: repeat(auto-fill, minmax(100%, 1fr)); grid-gap: 5px; align-items: stretch; color: #f5f7fc; margin-top: 5px;}.grid1 > articlegraph{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; height: 360px;}/* unvisited link */a:link{color: black;}/* visited link */a:visited{color: black;}/* mouse over link */a:hover{color: black;}/* selected link */a:active{color: black;}.divumwxdarkbrowser{position: relative; background: 0; width: 97%; height: 30px; margin: auto; margin-top: -5px; margin-left: 0px; border-top-left-radius: 5px; border-top-right-radius: 5px; padding-top: 10px;}.divumwxdarkbrowser[url]:after{content: attr(url); color: black; font-size: 14px; text-align: center; position: absolute; left: 0; right: 0; top: 0; padding: 4px 15px; margin: 11px 10px 0 auto; font-family: arial; height: 20px;}a{color: #aaa; text-decoration: none;}blue{color: #01a4b4;}orange{color: #009bb4;}orange1{position: relative; color: #009bb4; margin: 0 auto; text-align: center; margin-left: 5%; font-size: 1.1rem;}green{color: #aaa;}red{color: #f37867;}red6{color: #d65b4a;}value{color: #fff;}yellow{color: #cc0;}purple{color: #916392;}.actualt{position: relative; left: 0px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: #555555; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: max-content; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: white; align-items: center; justify-content: center; margin-bottom: 10px; top: 0; text-align: center;}.actual{position: relative; left: 5px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: 95%; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: #aaa; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}<!-- divumwx rain beaker css -- > .rainfallcontainer1{left: 5px; top: 0;}.rainfalltoday1{font-family: weathertext2, Arial, Helvetica, system; width: 4.25rem; height: 1.5rem; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; font-weight: normal; font-size: 0.9rem; padding-top: 5px; color: #fff; border-bottom: 12px solid #555555; align-items: center; justify-content: center; text-align: center; border-radius: 3px; background: rgba(68, 166, 181, 1); -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.rainfallcaution,.rainfalltrend{position: absolute; font-size: 1rem;}smalluvunit{font-size: 0.6rem; font-family: Arial, Helvetica, system;}.lotemp{color: black; font-size: 0.6rem;}.almanac{font-size: 1.25em; margin-top: 30px; color: rgba(56, 56, 60, 1); width: 12em;}metricsblue{color: #44a6b5; font-family: "weathertext2", Helvetica, Arial, sans-serif; background: rgba(86, 95, 103, 0.5); -webkit-border-radius: 2px; border-radius: 2px; align-items: center; justify-content: center; font-size: 0.9em; left: 10px; padding: 0 3px 0 3px;}.dvmconvertrain{position: relative; font-size: 0.8em; top: 6px; color: white; margin-left: auto; margin-right: auto;}.hitempy{position: relative; background: 0; color: black; width: 75px; padding: 1px; -webit-border-radius: 2px; border-radius: 2px; margin-top: -33px; margin-left: 56px; padding-left: 3px; line-height: 11px; font-size: 9px;}

.rainfalltoday2{font-family: weathertext2, Arial, Helvetica, system; width: 5.25rem; height: 1.5rem; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; font-weight: normal; font-size: 0.9rem; padding-top: 5px; color: #fff; border-bottom: 12px solid #555555; align-items: center; justify-content: center; text-align: center; border-radius: 3px; background: rgba(68, 166, 181, 1); -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.rainfallcaution,.rainfalltrend{position: absolute; font-size: 1rem;}smalluvunit{font-size: 0.6rem; font-family: Arial, Helvetica, system;}.lotemp{color: black; font-size: 0.6rem;}.almanac{font-size: 1.25em; margin-top: 30px; color: rgba(56, 56, 60, 1); width: 12em;}metricsblue{color: #44a6b5; font-family: "weathertext2", Helvetica, Arial, sans-serif; background: rgba(86, 95, 103, 0.5); -webkit-border-radius: 2px; border-radius: 2px; align-items: center; justify-content: center; font-size: 0.9em; left: 10px; padding: 0 3px 0 3px;}.dvmconvertrain{position: relative; font-size: 0.8em; top: 6px; color: white; margin-left: auto; margin-right: auto;}.hitempy{position: relative; background: 0; color: black; width: 75px; padding: 1px; -webit-border-radius: 2px; border-radius: 2px; margin-top: -33px; margin-left: 56px; padding-left: 3px; line-height: 11px; font-size: 9px;}

    </style>';
}
include('rainSelect.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>divumwx Rainfall Almanac Information</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<main class="grid">
<article style="height:65px">
<div class=actualt>Rainfall Today</div>
<?php // rain today
echo "<div class='rainfalltoday1' style='background: $colorRainDaySum';>".$rain["day"]."</value><smalluvunit>".$rain["units"]."</smalluvunit>";?>
</div>

<div class="hitempy"><?php echo $raininfo."Last Hour<br><blue> ".$rain["last_hour"]."</blue> ".$rain["units"];?></div>
</article>

<article style="height:65px">
<div class=actualt>Rainfall Yesterday</div>
<?php // rain yesterday
echo "<div class='rainfalltoday1' style='background: $colorRainYesterdaySum';>".$rain["yesterday"]."</value><smalluvunit>".$rain["units"]."</smalluvunit>";?>
</div>

<div class="hitempy"><?php echo $raininfo."Yesterday<br/><blue> ".$rain["yesterday"]."</blue> ".$rain["units"];?></div>
</article>

<article style="height:65px">
<div class=actualt>Rainfall <?php echo date('M Y');?></div>
<?php // rain month
echo "<div class='rainfalltoday1' style='background: $colorRainMonthSum';>".$rain["month_total"]."</value><smalluvunit>".$rain["units"]."</smalluvunit>";?>
</div>

<div class="hitempy"><?php echo $raininfo."Last Rainfall<br/><blue> ".$rain["last_rain"]."</blue>";?></div>
</article>

<article style="height:65px">
<div class=actualt>Rainfall <?php echo date("Y");?></div>
<?php // rain year
echo "<div class='rainfalltoday1' style='background: $colorRainYearSum';>".$rain["year_total"]."</value><smalluvunit>".$rain["units"]."</smalluvunit>";?>
</div>

<div class="hitempy"><?php echo $raininfo."Since<br/><blue>Jan ".date('Y')."</blue";?></div>
</article>

<article>
<div class=actualt> Rainfall All-Time</div>
<?php
// rain alltime
echo "<div class='rainfalltoday1' style='background: $colorRainAlltimeSum';>".$rain["alltime_total"]."</value><smalluvunit>".$rain["units"]."</smalluvunit>";?>
</div>
<div class="hitempy"><?php echo $raininfo."Since<br/><blue>1st ".$divum["since"]."</blue>";?></div>
</article> </main>


 <main class="grid1">
    <articlegraph> 
   
  <iframe  src="dvmhighcharts/smallCharts.php?chart='rainsmallplot'&span='yearly'&temp='<?php echo $temp['units'];?>'&pressure='<?php echo $barom['units'];?>'&wind='<?php echo $wind['units'];?>'&rain='<?php echo $rain['units'];?>" frameborder="0" scrolling="no" width="100%"  height="100%"></iframe>
   
  </articlegraph> 
  
  
</main>