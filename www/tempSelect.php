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
function openTempCharts(chart) {
    
        window.location = chart;
    
}
</script>

<div id="title_bar" style="margin-top: 10px; margin-left 14px; margin-bottom: 7px;">
  <div id="reports">
    <select name="reports" onchange="openTempCharts(value)"  style="background-color: rgb(194,102,58); color: white; border: 2px solid rgb(194,102,58); border-radius: 5px;">
      <option value="dvmTemperatureRecords.php">Records</option>
      <option value="dvmhighcharts/tempCharts.php?chart='temperatureplot'&span='yearly'&temp='<?php echo $temp["units"];?>'&pressure='<?php echo $barom["units"];?>'&wind='<?php echo $wind["units"];?>'&rain='<?php echo $rain["units"];?>" frameborder="0" scrolling="no" width="100%" height="100%">Yearly Temperature</option>
      <option value="dvmhighcharts/tempCharts.php?chart='tempderivedplot'&span='yearly'&temp='<?php echo $temp["units"];?>'&pressure='<?php echo $barom["units"];?>'&wind='<?php echo $wind["units"];?>'&rain='<?php echo $rain["units"];?>" frameborder="0" scrolling="no" width="100%" height="100%">Yearly Feels Like</option>
      <option value="dvmhighcharts/tempCharts.php?chart='humidityplot'&span='yearly'&temp='<?php echo $temp["units"];?>'&pressure='<?php echo $barom["units"];?>'&wind='<?php echo $wind["units"];?>'&rain='<?php echo $rain["units"];?>" frameborder="0" scrolling="no" width="100%" height="100%">Yearly Humidity</option>
      <option value="dvmhighcharts/tempCharts.php?chart='temperatureplot'&span='weekly'&temp='<?php echo $temp["units"];?>'&pressure='<?php echo $barom["units"];?>'&wind='<?php echo $wind["units"];?>'&rain='<?php echo $rain["units"];?>" frameborder="0" scrolling="no" width="100%" height="100%">Weekly Temperature</option>
      <option value="dvmhighcharts/tempCharts.php?chart='tempderivedplot'&span='weekly'&temp='<?php echo $temp["units"];?>'&pressure='<?php echo $barom["units"];?>'&wind='<?php echo $wind["units"];?>'&rain='<?php echo $rain["units"];?>" frameborder="0" scrolling="no" width="100%" height="100%">Weekly Feels Like</option>
      <option value="dvmhighcharts/tempCharts.php?chart='humidityplot'&span='weekly'&temp='<?php echo $temp["units"];?>'&pressure='<?php echo $barom["units"];?>'&wind='<?php echo $wind["units"];?>'&rain='<?php echo $rain["units"];?>" frameborder="0" scrolling="no" width="100%" height="100%">Weekly Humidity</option>
      <option value="dvmhighcharts/tempCharts.php?chart='indoorplot'&span='yearly'&temp='<?php echo $temp["units"];?>'&pressure='<?php echo $barom["units"];?>'&wind='<?php echo $wind["units"];?>'&rain='<?php echo $rain["units"];?>" frameborder="0" scrolling="no" width="100%" height="100%">Indoor Temperature and Humidty</option>
      <option value="heatmaps/select.php">Heatmaps</option>
      <option selected> -Select Chart- </option>
    </select>
  </div>
</div>
