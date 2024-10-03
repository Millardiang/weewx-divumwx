<script>
function openBaromCharts(chart) {
    
        window.location = chart;
    
}
</script>

<div id="title_bar" style="margin-top: 0px; margin-bottom: 7px;">
  <div id="reports">
    <select name="reports" onchange="openBaromCharts(value)"  style="background-color: rgb(194,102,58); color: white; border: 2px solid rgb(194,102,58); border-radius: 5px;">
      <option value="../dvmBarometerRecords.php">Records</option>
      <option value="baromCharts.php?chart='barometerplot'&span='yearly'&temp='<?php echo $temp['units'];?>'&pressure='<?php echo $barom['units'];?>'&wind='<?php echo $wind['units'];?>'&rain='<?php echo $rain['units']?>">Yearly Barometer</option>
      <option value="baromCharts.php?chart='barometerplot'&span='weekly'&temp='<?php echo $temp['units'];?>'&pressure='<?php echo $barom['units'];?>'&wind='<?php echo $wind['units'];?>'&rain='<?php echo $rain['units']?>">Weekly Barometer</option>
      <option value="../dvmAirDensityRecords.php">Air Density</option>
      <option selected> -Select Chart- </option>
    </select>
  </div>
</div>

