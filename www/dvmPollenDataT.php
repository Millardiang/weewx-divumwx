<?php
include('dvmCombinedData.php');
if($theme === "light"){ echo "<body style='background-color:e0eafb'>";}
else if($theme === "dark"){ echo "<body style='background-color:#292E35'>";}

$textColor = "var(--col-6)";
date_default_timezone_set($TZ);
$openmeteo = "./img/openmeteo.jpg";

$json_string = file_get_contents('jsondata/airquality.txt');
$parsed_json = json_decode($json_string,true);
$pollen["updated_time"] = $pollen["updated_time"] = date($timeFormat,filemtime('jsondata/open_meteo.txt'));
$pollen["grass"] = $parsed_json["current"]["grass_pollen"];
$a = $parsed_json["current"]["alder_pollen"];
$b = $parsed_json["current"]["birch_pollen"];
$o = $parsed_json["current"]["olive_pollen"];
$m = $parsed_json["current"]["mugwort_pollen"];
$r = $parsed_json["current"]["ragweed_pollen"];

//grass
if ($pollen["grass"]==0){$pollen["grass_risk"] = "None"; $pollen["grass_color"]="#59C239";}
else if ($pollen["grass"]<5){$pollen["grass_risk"] = "Low"; $pollen["grass_color"]="#ffea00";}
else if ($pollen["grass"]<20){$pollen["grass_risk"] = "Moderate"; $pollen["grass_color"]="#F19E38";}
else if ($pollen["grass"]<200){$pollen["grass_risk"] = "High"; $pollen["grass_color"]="#EA3323";}
else if ($pollen["grass"]>200){$pollen["grass_risk"] = "Very High"; $pollen["grass_color"]="#621e2f";}
else {$pollen["grass_risk"] = "No Data"; $pollen["grass_color"]=$noColor;}
//tree find highest value across tree varieties
if ($a > $b && $a > $o){$pollen["tree"]= $a;}
else if ($b > $a && $b > $o){$pollen["tree"] = $b;}
else if ($o > $a && $o > $b){$pollen["tree"] = $o;}
else if ($o == 0 && $a == 0 && $b == 0){$pollen["tree"] = 0;}
else if ($o == $a && $o == $b && $a == $b){$pollen["tree"] = $o;}
//calculate risk based on https://www.webmd.com/allergies/what-to-know-about-pollen-count
if ($pollen["tree"]==0){$pollen["tree_risk"] = "None"; $pollen["tree_color"]="#59C239";}
else if ($pollen["tree"]<10){$pollen["tree_risk"] = "Low"; $pollen["tree_color"]="#ffea00";}
else if ($pollen["tree"]<50){$pollen["tree_risk"] = "Moderate"; $pollen["tree_color"]="#F19E38";}
else if ($pollen["tree"]<500){$pollen["tree_risk"] = "High"; $pollen["tree_color"]="#EA3323";}
else if ($pollen["tree"]>500){$pollen["tree_risk"] = "Very High"; $pollen["tree_color"]="#621e2f";}
else {$pollen["tree_risk"] = "No Data"; $pollen["tree_color"]=$noColor;}
//weed find highest value across tree varieties
if ($m > $r){$pollen["weed"] = $m;}
else if ($r > $m){$pollen["weed"] = $r;}
else if ($m == 0 && $r == 0){$pollen["weed"] = 0;}
else if ($m === $r){$pollen["weed"] = $m;}
//calculate risk based on https://www.webmd.com/allergies/what-to-know-about-pollen-count
if ($pollen["weed"]==0){$pollen["weed_risk"] = "None"; $pollen["weed_color"]="#59C239";}
else if ($pollen["weed"]<10){$pollen["weed_risk"] = "Low"; $pollen["weed_color"]="#ffea00";}
else if ($pollen["weed"]<50){$pollen["weed_risk"] = "Moderate"; $pollen["weed_color"]="#F19E38";}
else if ($pollen["weed"]<500){$pollen["weed_risk"] = "High"; $pollen["weed_color"]="#EA3323";}
else if ($pollen["weed"]>500){$pollen["weed_risk"] = "Very High"; $pollen["weed_color"]="#621e2f";}
else {$pollen["weed_risk"] = "No Data"; $pollen["weed_color"]=$noColor;}
$openmeteologo='<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="var(--col-6)" viewBox="0 0 16 16"><path d="M7 8a3.5 3.5 0 0 1 3.5 3.555.5.5 0 0 0 .624.492A1.503 1.503 0 0 1 13 13.5a1.5 1.5 0 0 1-1.5 1.5H3a2 2 0 1 1 .1-3.998.5.5 0 0 0 .51-.375A3.502 3.502 0 0 1 7 8zm4.473 3a4.5 4.5 0 0 0-8.72-.99A3 3 0 0 0 3 16h8.5a2.5 2.5 0 0 0 0-5h-.027z"></path><path d="M10.5 1.5a.5.5 0 0 0-1 0v1a.5.5 0 0 0 1 0v-1zm3.743 1.964a.5.5 0 1 0-.707-.707l-.708.707a.5.5 0 0 0 .708.708l.707-.708zm-7.779-.707a.5.5 0 0 0-.707.707l.707.708a.5.5 0 1 0 .708-.708l-.708-.707zm1.734 3.374a2 2 0 1 1 3.296 2.198c.199.281.372.582.516.898a3 3 0 1 0-4.84-3.225c.352.011.696.055 1.028.129zm4.484 4.074c.6.215 1.125.59 1.522 1.072a.5.5 0 0 0 .039-.742l-.707-.707a.5.5 0 0 0-.854.377zM14.5 6.5a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1h-1z"></path></svg>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset='utf-8'>    
<title>Pollen Data</title>
</head>
<body>

<script src="js/ramda.min.js"></script>
<script src="js/d3.4.2.2.min.js"></script>
<style>
 body { 
    padding-top: 30px;
    padding-left: 22.5px;
    overflow: hidden;
}

a:link {
    text-decoration: none;
  color: <?php echo $textColor;?>;
}
a:visited {
    text-decoration: none;
  color: <?php echo $textColor;?>;
}
a:hover {
    text-decoration: none;
  color: <?php echo "green";?>;
}
a:active {
    text-decoration: none;
  color: <?php echo $textColor;?>;
}

.openmeteologo {
    position: relative; 
    margin-top: -15px; 
    margin-left: 348px; 
}
.header {
   text-align: center;
}
table.pollen{width:95%;text-align:center;border-spacing:3px;}
table.pollen td, table.pollen th{border:1px solid var(--col-13);border-radius:2px;padding:1px 1px;}
table.pollen tbody td{font-size:0.585em;width:50px;}
</style>

<!--div class="header">Pollen Data <?php echo $stationlocation;?></div-->
<table class="power">
<tbody>
<tr>
<th style="background-color: green;">SOC</th>
<th style="border: transparent;">Day Charge</th>
<th style="border: transparent;">Discharge</th>
</tr>
<tr>
<td style="border-left: 5px solid rgb(59,134,166);"><?php echo $battery["soc"];?>%</td>
<td style="border-left: 5px solid rgb(59,134,166);"><?php echo $battery["daily_charge"];?>kWh</td>
<td style="border-left: 5px solid rgb(59,134,166);"><?php echo $battery["daily_discharge"];?>kWh</td>
</tr>
<tr>
<td style="border: transparent;"></td>
<td style="border: transparent;">Day Export</td>
<td style="border: transparent;">Day Import</td>
</tr>
<tr>
<td style="border: transparent;"></td>
<td style="border-left: 5px solid rgb(128,128,38);"><?php echo $grid["daily_export"];?>kWh</td>
<td style="border-left: 5px solid rgb(128,128,38);"><?php echo $grid["daily_import"];?>kWh</td>
</tr>
<tr>
<td style="border: transparent;">UPS Power</td>
<td style="border: transparent;">Total Power</td>
<td style="border: transparent;">Daily Energy</td>
</tr>
<tr>
<td style="border-left: 5px solid rgb(158,74,27);"><?php echo $load["total_ups_power"];?>W</td>
<td style="border-left: 5px solid rgb(158,74,27);"><?php echo $load["total_power"];?>W</td>
<td style="border-left: 5px solid rgb(158,74,27);"><?php echo $load["daily_energy"];?>kWh</td>
</tr>
<tr>
<td style="border: transparent;"></td>
<td style="border: transparent;">Day Energy</td>
<td style="border: transparent;"></td>
</tr>
<tr>
<td style="border: transparent;"></td>
<td style="border-left: 5px solid rgb(237,190,117);"><?php echo $solar["daily_energy"];?>kWh</td>
<td style="border: transparent;"></td>
</tr>
</tbody>
</table>
</body>
</html>