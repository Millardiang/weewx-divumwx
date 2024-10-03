<script>
function openAirQualityCharts(chart) {
    
        window.location = chart;
    
}
</script>

<div id="title_bar" style="margin-top: -5px; margin-bottom: 7px;">
  <div id="reports">
    <select name="reports" onchange="openAirQualityCharts(value)"  style="background-color: rgb(194,102,58); color: white; border: 2px solid rgb(194,102,58); border-radius: 5px;">
      <option value="dvmAirQualityWeekChart.php">Air Quality Weekly</option>
      <option value="dvmAirQualityYearChart.php">Air Quality Yearly</option>
      <option value="../dvmAirQualityInformation.php">Air Quality Information</option>
      <option selected> -Select Chart- </option>
    </select>
  </div>
</div>
