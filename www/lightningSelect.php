<script>
function openLightningCharts(chart) {   
    window.location = chart;   
}
</script>
<div id="title_bar" style="margin-top: 10px; margin-bottom: 7px;">
  <div id="reports">
    <select name="reports" onchange="openLightningCharts(value)"  style="background-color: rgb(194,102,58); color: white; border: 2px solid rgb(194,102,58); border-radius: 5px;">
      <option value="dvmLightningRecords.php">Lightning Strikes</option>
      <!--option value="dvmLightningRadarRecords.php">Lightning Radar</option-->
      <!--option value="dvmLightningEuropePopup.php">European Strikes</option-->
      <!--option value="dvmLightningGermanyPopup.php">Germany Strikes</option-->
      <!--option value="dvmLightningDresdenPopup.php">Dresden Area Strikes</option-->
      <option selected> -Select Chart- </option>
    </select>
  </div>
</div>
