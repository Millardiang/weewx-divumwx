<script>
function openForecastCharts(chart) {
    
        window.location = chart;
    
}
</script>

<div id="title_bar" style="margin-top: 10px; margin-left 14px; margin-bottom: 7px;">
  <div id="reports">
    <select name="reports" onchange="openForecastCharts(value)"  style="background-color: rgb(194,102,58); color: white; border: 2px solid rgb(194,102,58); border-radius: 5px;">
      <option value="dvmForecastHourlyPopup.php">Hourly Forecast</option>
      <!--option value="dvmOmForecastHourlyPopup.php">O-M Hourly Forecast</option-->
      <option value="dvmForecastHourlyTablePopup.php">Hourly Forecast Table</option>
      <option value="dvmForecastDaynightPopup.php">Daily Forecast</option>
      <option value="dvmForecastDaynightTablePopup.php">Daily Forecast Table</option>
      <option value="dvmMeteogramPopup.php">Meteogram</option>
      <option selected> -Select Forecast- </option>
    </select>
  </div>
</div>
