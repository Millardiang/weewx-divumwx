<script>
function openPollenCharts(chart) {
    
        window.location = chart;
    
}
</script>

<div id="title_bar" style="margin-top: -5px; margin-bottom: 7px;">
  <div id="reports">
    <select name="reports" onchange="openPollenCharts(value)"  style="background-color: rgb(194,102,58); color: white; border: 2px solid rgb(194,102,58); border-radius: 5px;">
      <option value="dvmPollenWeekChart.php">Pollen Count Weekly</option>
      <option value="dvmPollenYearChart.php">Pollen Count Yearly</option>
      <option value="../dvmPollenInformationPopup.php">Pollen Information</option>
      <option selected> -Select Chart- </option>
    </select>
  </div>
</div>
