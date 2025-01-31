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
?>
<script>
function openSolarCharts(chart) {
    
        window.location = chart;
    
}
</script>

<div id="title_bar" style="margin-top: 10px; margin-bottom: 7px;">
  <div id="reports">
    <select name="reports" onchange="openSolarCharts(value)"  style="background-color: rgb(194,102,58); color: white; border: 2px solid rgb(194,102,58); border-radius: 5px;">
      <option value="dvmSolarRecords.php">Solar Records</option> 
      <option value="charts/solarCharts.php?chart='radiationplot'&span='yearly'&temp='<?php echo $temp[
    "units"
]; ?>'&pressure='<?php echo $barom[
    "units"
]; ?>'&wind='<?php echo $wind[
	"units"
]; ?>'&rain='<?php echo $rain[
    "units"
]; ?>">Solar</option>
      <option value="dvmhighcharts/solarCharts.php?chart='uvplot'&span='weekly'&temp='<?php echo $temp['units'];?>'&pressure='<?php echo $barom['units'];?>'&wind='<?php echo $wind['units'];?>'&rain='<?php echo $rain['units']?>">UV Index</option>
      <option value="dvmUVIRecords.php">UVI Records</option>
      <option value="dvmhighcharts/dvmSunlightDurationChart.php">Sun Duration</option>
      <option value="dvmhighcharts/dvmSolarGenMaxChart.php">Daily Electrical Power Generation</option>
      <option value="dvmhighcharts/solarBellChart.php">Solar Power Generation</option>
      <option selected> -Select Chart- </option>
    </select>
  </div>
</div>
