<script>
function openRainCharts(chart) {
    
        window.location = chart;
    
}
</script>

<div id="title_bar" style="margin-top: 0px; margin-bottom: 7px;">
  <div id="reports">
    <select name="reports" onchange="openRainCharts(value)"  style="background-color: rgb(194,102,58); color: white; border: 2px solid rgb(194,102,58); border-radius: 5px;">
      <option value="../dvmRainfallRecords.php">Records</option>
      <option value="rainCharts.php?chart='rainplot'&span='yearly'&temp='<?php echo $temp['units'];?>'&pressure='<?php echo $barom['units'];?>'&wind='<?php echo $wind['units'];?>'&rain='<?php echo $rain['units']?>">Yearly Rainfall</option>
      <option value="rainCharts.php?chart='rainplot'&span='weekly'&temp='<?php echo $temp['units'];?>'&pressure='<?php echo $barom['units'];?>'&wind='<?php echo $wind['units'];?>'&rain='<?php echo $rain['units']?>">Weekly Rainfall</option>
      <option value="dvmStormRainChart.php">Storm Rain</option>
      <option value="dvmRainSensorChart.php">Rain Sensor Comparisson</option>
<option selected> -Select Chart- </option>
    </select>
  </div>
</div>