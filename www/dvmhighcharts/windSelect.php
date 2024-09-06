<script>
function openWindCharts(chart) {
    
        window.location = chart;
    
}
</script>

<div id="title_bar" style="margin-top: 8px; margin-bottom: 7px;">
  <div id="reports">
    <select name="reports" onchange="openWindCharts(value)"  style="background-color: rgb(194,102,58); color: white; border: 2px solid rgb(194,102,58); border-radius: 5px;">
      <option value="../dvmWindRecords.php">Records</option>
      <option value="windCharts.php?chart='windplot'&span='yearly'&temp='<?php echo $temp['units'];?>'&pressure='<?php echo $barom['units'];?>'&wind='<?php echo $wind['units'];?>'&rain='<?php echo $rain['units']?>">Yearly Wind</option>
      <option value="windCharts.php?chart='windplot'&span='weekly'&temp='<?php echo $temp['units'];?>'&pressure='<?php echo $barom['units'];?>'&wind='<?php echo $wind['units'];?>'&rain='<?php echo $rain['units']?>">Weekly Wind</option>
      <option value="windCharts.php?chart='windroseplot'&span='weekly'&temp='<?php echo $temp['units'];?>'&pressure='<?php echo $barom['units'];?>'&wind='<?php echo $wind['units'];?>'&rain='<?php echo $rain['units']?>">Wind Rose</option>
      <option selected> -Select Chart- </option>
    </select>
  </div>
</div>
