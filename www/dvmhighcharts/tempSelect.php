<script>
function openTempCharts(chart) {
    
        window.location = chart;
    
}
</script>

<div id="title_bar" style="margin-top: 10px; margin-left 14px; margin-bottom: 7px;">
  <div id="reports">
    <select name="reports" onchange="openTempCharts(value)"  style="background-color: rgb(194,102,58); color: white; border: 2px solid rgb(194,102,58); border-radius: 5px;">
      <option value="../dvmTemperatureRecords.php">Records</option>
      <option value="tempCharts.php?chart='temperatureplot'&span='yearly'&temp='<?php echo $temp["units"];?>'&pressure='<?php echo $barom["units"];?>'&wind='<?php echo $wind["units"];?>'&rain='<?php echo $rain["units"];?>" frameborder="0" scrolling="no" width="100%" height="100%">Yearly Temperature</option>
      <option value="tempCharts.php?chart='tempderivedplot'&span='yearly'&temp='<?php echo $temp["units"];?>'&pressure='<?php echo $barom["units"];?>'&wind='<?php echo $wind["units"];?>'&rain='<?php echo $rain["units"];?>" frameborder="0" scrolling="no" width="100%" height="100%">Yearly Feels Like</option>
      <option value="tempCharts.php?chart='humidityplot'&span='yearly'&temp='<?php echo $temp["units"];?>'&pressure='<?php echo $barom["units"];?>'&wind='<?php echo $wind["units"];?>'&rain='<?php echo $rain["units"];?>" frameborder="0" scrolling="no" width="100%" height="100%">Yearly Humidity</option>
      <option value="tempCharts.php?chart='temperatureplot'&span='weekly'&temp='<?php echo $temp["units"];?>'&pressure='<?php echo $barom["units"];?>'&wind='<?php echo $wind["units"];?>'&rain='<?php echo $rain["units"];?>" frameborder="0" scrolling="no" width="100%" height="100%">Weekly Temperature</option>
      <option value="tempCharts.php?chart='tempderivedplot'&span='weekly'&temp='<?php echo $temp["units"];?>'&pressure='<?php echo $barom["units"];?>'&wind='<?php echo $wind["units"];?>'&rain='<?php echo $rain["units"];?>" frameborder="0" scrolling="no" width="100%" height="100%">Weekly Feels Like</option>
      <option value="tempCharts.php?chart='humidityplot'&span='weekly'&temp='<?php echo $temp["units"];?>'&pressure='<?php echo $barom["units"];?>'&wind='<?php echo $wind["units"];?>'&rain='<?php echo $rain["units"];?>" frameborder="0" scrolling="no" width="100%" height="100%">Weekly Humidity</option>
      <option value="tempCharts.php?chart='indoorplot'&span='yearly'&temp='<?php echo $temp["units"];?>'&pressure='<?php echo $barom["units"];?>'&wind='<?php echo $wind["units"];?>'&rain='<?php echo $rain["units"];?>" frameborder="0" scrolling="no" width="100%" height="100%">Indoor Temperature and Humidty</option>
      <option selected> -Select Chart- </option>
    </select>
  </div>
</div>
