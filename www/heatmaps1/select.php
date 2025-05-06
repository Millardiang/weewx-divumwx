
<script>
    
function openHeatmapFile(date) {
    if (date.match(/^\d\d\d\d/)) {
        window.location = "./heatmap-" + date + ".php";
    }
}
</script>

<div id="title_bar">
  <div id="reports">
    <select name="reports" onchange="openHeatmapFile(value)"  style="background-color: rgb(194,102,58); color: white; border: 2px solid rgb(194,102,58); border-radius: 5px;">
      <option value="2020">2020</option>
      <option value="2021">2021</option>
      <option value="2022">2022</option>
      <option value="2023">2023</option>
      <option value="2024">2024</option>
      <option value="2025">2025</option>
      
      <option selected> -Select Year- </option>
    </select>
    <select name="reports" onchange="openHeatmapFile(value)"  style="background-color: rgb(194,102,58); color: white; border: 2px solid rgb(194,102,58); border-radius: 5px;">
      <option value="2020-05">2020-05</option>
      <option value="2020-06">2020-06</option>
      <option value="2020-07">2020-07</option>
      <option value="2020-08">2020-08</option>
      <option value="2020-09">2020-09</option>
      <option value="2020-10">2020-10</option>
      <option value="2020-11">2020-11</option>
      <option value="2020-12">2020-12</option>
      <option value="2021-01">2021-01</option>
      <option value="2021-02">2021-02</option>
      <option value="2021-03">2021-03</option>
      <option value="2021-04">2021-04</option>
      <option value="2021-05">2021-05</option>
      <option value="2021-06">2021-06</option>
      <option value="2021-07">2021-07</option>
      <option value="2021-08">2021-08</option>
      <option value="2021-09">2021-09</option>
      <option value="2021-10">2021-10</option>
      <option value="2021-11">2021-11</option>
      <option value="2021-12">2021-12</option>
      <option value="2022-01">2022-01</option>
      <option value="2022-02">2022-02</option>
      <option value="2022-03">2022-03</option>
      <option value="2022-04">2022-04</option>
      <option value="2022-05">2022-05</option>
      <option value="2022-06">2022-06</option>
      <option value="2022-07">2022-07</option>
      <option value="2022-08">2022-08</option>
      <option value="2022-09">2022-09</option>
      <option value="2022-10">2022-10</option>
      <option value="2022-11">2022-11</option>
      <option value="2022-12">2022-12</option>
      <option value="2023-01">2023-01</option>
      <option value="2023-02">2023-02</option>
      <option value="2023-03">2023-03</option>
      <option value="2023-04">2023-04</option>
      <option value="2023-05">2023-05</option>
      <option value="2023-06">2023-06</option>
      <option value="2023-07">2023-07</option>
      <option value="2023-08">2023-08</option>
      <option value="2023-09">2023-09</option>
      <option value="2023-10">2023-10</option>
      <option value="2023-11">2023-11</option>
      <option value="2023-12">2023-12</option>
      <option value="2024-01">2024-01</option>
      <option value="2024-02">2024-02</option>
      <option value="2024-03">2024-03</option>
      <option value="2024-04">2024-04</option>
      <option value="2024-05">2024-05</option>
      <option value="2024-06">2024-06</option>
      <option value="2024-07">2024-07</option>
      <option value="2024-08">2024-08</option>
      <option value="2024-09">2024-09</option>
      <option value="2024-10">2024-10</option>
      <option value="2024-11">2024-11</option>
      <option value="2024-12">2024-12</option>
      <option value="2025-01">2025-01</option>
      <option value="2025-02">2025-02</option>
      <option value="2025-03">2025-03</option>
      <option value="2025-04">2025-04</option>
      
      <option selected> -Select Month- </option>
    </select>
    
    <br/>
  </div>
</div>
