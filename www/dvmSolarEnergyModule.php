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
error_reporting(0);
$solar["efficiency"] = $solar["power"]/4050*100;
if ($battery["power"]<0){$battery["state"] = "Charging";}
else {$battery["state"] = "Discharging";}
if ($grid["power"]<0){$grid["state"] = "Exporting to Grid";}
else {$grid["state"] = "Importing from Grid";}
$json_visibility = file_get_contents("jsondata/awc.txt");
$parsed_visibility = json_decode($json_visibility, true);
$sky["cloud_cover"] = $parsed_visibility['response'][0]['periods'][0]['sky'];
//cloud-icon
if ($sky["cloud_cover"] < 7 && $sky["cloud_cover"] > 0) {
if ($dayPartNatural == "night" ){$current["image"] ="img/pvNight.svg";} 
else $current["image"] ="img/pvClearDay.svg";
} 
else if ($sky["cloud_cover"] < 32) {
if ($dayPartNatural == "night" ){$current["image"] = "img/pvNight.svg";} 
else $current["image"] ="img/pvMostlyClearDay.svg";
}
else if ($sky["cloud_cover"] < 70) {
if ($dayPartNatural == "night" ){$current["image"] = "img/pvNight.svg";} 
else $current["image"] = "img/pvPartlyCloudyDay.svg";
}
else if ($sky["cloud_cover"] < 95) {
if ($dayPartNatural == "night" ){$current["image"] = "img/pvNight.svg";} 
else $current["image"] = "img/pvMostlyCloudyDay.svg";
}
else if($sky["cloud_cover"] >= 95) {
if ($dayPartNatural == "night" ){$current["image"] ="img/pvNight.svg";} 
else $current["image"] = "img/pvOvercastDay.svg";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>solar power</title>
<style>
.power-module{position: relative;top:-1px;margin-left: 0px;}
</style>
</head>
<body>

<div class="chartforecast">
<span class="yearpopup"><a alt="energy charts" title="energy charts" href="dvmhighcharts/dvmSolarEnergyChart.php" data-lity><?php echo $menucharticonpage;?> Energy Records and Charts</a></span>
</div>
<span class='moduletitle'>Solar Energy <?php echo $powerHardware;?></span>
<div class="updatedtime1"><span><?php if (file_exists($livedata)&&time() - filemtime($livedata)>300) echo $offline. '<offline> Offline </offline>'; else echo $online." ".$divum["time"];?></div>
<div class="rainconverter">

<scrip?>


</span>
</div>
<script src="js/d3.7.9.0.min.js"></script>

<div class="power-module"></div>
<script>

var baseTextColor = "var(--col-6)";

var reverseTextColor = "#000000";
                   
var powerUnits = "W";
var energyUnits = "kWh";
var percentUnits = "%";

var currentImage = "<?php echo $current["image"];?>";
var batteryState = "<?php echo $battery["state"];?>";
var batteryPower = "<?php echo abs($battery["power"]);?>";
var batterySOC ="<?php echo $battery["soc"];?>";
var batteryDailyCharge ="<?php echo $battery["daily_charge"];?>";
var batteryDailyDischarge ="<?php echo $battery["daily_discharge"];?>"; 
var gridState = "<?php echo $grid["state"];?>";
var gridPower = "<?php echo abs($grid["power"]);?>";
var solarDailyExport = "<?php echo $solar["daily_export"];?>";
var gridDailyImport = "<?php echo $grid["daily_import"];?>";
var loadPower = "<?php echo $load["total_power"];?>";
var loadUPSPower = "<?php echo $load["total_ups_power"];?>";
var loadDailyEnergy = "<?php echo $load["daily_energy"];?>";              
var solarPower = "<?php echo abs($solar["power"]);?>";
var solarDailyEnergy = "<?php echo $solar["daily_energy"];?>"; 
var solarEfficiency = "<?php echo $solar["efficiency"];?>";

var svg = d3.select(".power-module")
    .append("svg")
    .attr("width", 310)
    .attr("height", 150);

svg.append("text")
    .attr("x", 258)
    .attr("y", 10)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(gridState);

svg.append("text")
    .attr("x", 258)
    .attr("y", 20)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(d3.format(".0f")(gridPower) + " " + powerUnits);

svg.append("text")
    .attr("x", 55)
    .attr("y", 10)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("PV Array Generating");

svg.append("text")
    .attr("x", 55)
    .attr("y", 20)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "10px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(d3.format(".0f")(solarPower) + " " + powerUnits);

svg.append("text")
    .attr("x", 45)
    .attr("y", 70)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("Solar Daily Energy");

svg.append("text")
    .attr("x", 45)
    .attr("y", 80)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(d3.format(".2f")(solarDailyEnergy) + " " + energyUnits);

svg.append("text")
    .attr("x", 263)
    .attr("y", 70)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("Solar Daily Export");

svg.append("text")
    .attr("x", 263)
    .attr("y", 80)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(d3.format(".2f")(solarDailyExport) + " " + energyUnits);

svg.append("text")
    .attr("x", 47)
    .attr("y", 136)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("Battery" + " " + batteryState);

svg.append("text")
    .attr("x", 47)
    .attr("y", 148)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(d3.format(".0f")(batteryPower) + " " + powerUnits + "  (SOC " + batterySOC + percentUnits + ")");

svg.append("text")
    .attr("x", 262)
    .attr("y", 136)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("Total House Load");

svg.append("text")
    .attr("x", 262)
    .attr("y", 148)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(d3.format(".0f")(loadPower) + " " + powerUnits);

svg.append("text")
    .attr("x", 155)
    .attr("y", 130)
    .style("fill", baseTextColor)
    .style("font-family", "Helvetica")
    .style("font-size", "9px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text("PV Efficiency " + d3.format(".1f")(solarEfficiency) + percentUnits);

svg.append("image") // image output
    .attr('xlink:href', currentImage)
    .attr('height', 110)
    .attr('x', 104)
    .attr('y', 5);

</script>

</html>