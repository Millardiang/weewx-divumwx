<?php
include('dvmCombinedData.php');
error_reporting(0);
$etCurrent = number_format($et["current"],2);
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>weather piezo rain</title>
<style>
.et-module{position: relative;top:-1px;margin-left: 0px;}
.et-current{position: relative;font-size:12px;font-weight:bold;font-family:Helvetica;color:var(--col-6);top:-32px;margin-left:-10px;}
</style>
</head>
<body>

<div class="chartforecast">
<!--span class="yearpopup"><a alt="rain charts" title="rain charts" href="dvmRainfallRecords.php" data-lity><?php echo $menucharticonpage;?> Rainfall Records and Charts</a></span-->
</div>
<span class='moduletitle'>Evapotranspiration (<valuetitleunit>mm</valuetitleunit>)</span>
<div class="updatedtime1"><span><?php if (file_exists($livedata)&&time() - filemtime($livedata)>300) echo $offline. '<offline> Offline </offline>'; else echo $online." ".$divum["time"];?></div>
<div class="rainconverter">

<scrip/>


</span>
</div>
<script src="js/d3.7.9.0.min.js"></script>

<div class="et-module"></div>
<script>

var baseTextColor = "var(--col-6)";

var reverseTextColor = "#000000";
    
var colorRain = "<?php echo $colorRainDaySum;?>";
                   
var units = "<?php echo $rainunit;?>";

var currentImage = "img/etLeaf.svg";

var etCurrent = "<?php echo round($rain["et_current"],2);?>";                   
var etHourColor = "<?php echo $colorRain1hrSum;?>";
var et24HoursColor = "<?php echo $colorRain24hrSum;?>"; 
var etMonthColor = "<?php echo $colorRainMonthSum;?>";
var etYearColor = "<?php echo $colorRainYearSum;?>";

var etHour = <?php echo $rain["et_hour"];?>;
var et24Hours = <?php echo $rain["et_24hr"];?>;
var etWeek = <?php echo $rain["et_week"];?>;
var etMonth = <?php echo $rain["et_month"];?>; 
var etYear = <?php echo $rain["et_year"];?>;
var etAlltime = <?php echo $rain["et_alltime"];?>;
var month = "<?php echo date('F');?>"; 
var year = <?php echo date('Y');?>;                   

var svg = d3.select(".et-module")
    .append("svg")
    .attr("width", 310)
    .attr("height", 150);

svg.append("text")
    .attr("x", 272)
    .attr("y", 10)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("Last 24 Hours");

svg.append("text")
    .attr("x", 272)
    .attr("y", 20)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(d3.format(".2f")(et24Hours) + " " + units);

svg.append("text")
    .attr("x", 37)
    .attr("y", 10)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("Last Hour");

svg.append("text")
    .attr("x", 37)
    .attr("y", 20)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(d3.format(".2f")(etHour) + " " + units);

svg.append("text")
    .attr("x", 37)
    .attr("y", 136)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(month);

svg.append("text")
    .attr("x", 37)
    .attr("y", 148)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(d3.format(".2f")(etMonth) + " " + units);

svg.append("text")
    .attr("x", 272)
    .attr("y", 136)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(year);

svg.append("text")
    .attr("x", 272)
    .attr("y", 148)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(d3.format(".2f")(etYear) + " " + units);

svg.append("image") // image output
    .attr('xlink:href', currentImage)
    .attr('height', 90)
    .attr('x', 115)
    .attr('y', 19);
            

</script>
<div class="et-current"><?php echo round($rain["et_current"],4);?>&nbsp;<?php echo $rainunit;?></div>
</html>