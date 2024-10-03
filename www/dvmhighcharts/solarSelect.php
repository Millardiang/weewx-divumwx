<script>
function openSolarCharts(chart) {
    
        window.location = chart;
    
}
</script>

<div id="title_bar" style="margin-top: 10px; margin-bottom: 7px;">
  <div id="reports">
    <select name="reports" onchange="openSolarCharts(value)"  style="background-color: rgb(194,102,58); color: white; border: 2px solid rgb(194,102,58); border-radius: 5px;">
      <option value="../dvmSolarRecords.php">Solar Records</option>
      <option value="solarCharts.php?chart='radiationplot'&span='yearly'&temp='<?php echo $temp[
    "units"
]; ?>'&pressure='<?php echo $barom[
    "units"
]; ?>'&wind='<?php echo $wind[
	"units"
]; ?>'&rain='<?php echo $rain[
    "units"
]; ?>">Solar</option>
      <option value="solarCharts.php?chart='uvplot'&span='weekly'&temp='<?php echo $temp['units'];?>'&pressure='<?php echo $barom['units'];?>'&wind='<?php echo $wind['units'];?>'&rain='<?php echo $rain['units']?>">UV Index</option>
      <option value="../dvmUVIRecords.php">UVI Records</option>
      <option value="dvmSunlightDurationChart.php">Sun Duration</option>
      <option selected> -Select Chart- </option>
    </select>
  </div>
</div>
