  <?php
include('dvmCombinedData.php');


$airqual["pm_units"] = "μg/㎥";


if ($airqual["source"] == "purple") {
$json_string = file_get_contents("jsondata/pu.txt");
$parsed_json = json_decode($json_string, true);
$airqual["pm25"] = $parsed_json["sensor"]["stats"]["pm2.5"];
$airqual["city"] = $parsed_json["sensor"]["name"];

}
else if ($airqual["source"] == "weewx") {
$airqual["pm25"] = $air["current.pm2_5"];
$airqual["city"] = $stationlocation;
}
else if ($airqual["source"] == "waqi") {
$json_string = file_get_contents("jsondata/aq.txt");
$parsed_json = json_decode($json_string, true);
$airqual["pm25"] = $parsed_json['data']['iaqi']['pm25']['v'];
$airqual["city"] = $parsed["data"]["city"]["name"];
}
else if ($airqual["source"] == "sds"){
$json_string = file_get_contents("jsondata/aqiJson.txt");
$parsed_json = json_decode($json_string, true);
$airqual["pm25"] = round($parsed_json['pm25'],1);
$airqual["city"] = $stationlocation;
}

//Europe EAQI
if ($airqual["zone"] == "ei"){
 
if ($airqual["pm25"] < 11 ){
$airqual["image25"] = "./css/aqi/goodair.svg?ver=1.4";
$airqual["color25"] = "#51F0E6";
$airqual["text"] = "Good Air Quality";
}
else if ($airqual["pm25"] < 21){
$airqual["image25"] = "./css/aqi/goodair.svg?ver=1.4";
$airqual["color25"] = "#51CBA9";
$airqual["text"] = "Fair Air Quality";
}
else if ($airqual["pm25"] < 26){
$airqual["image25"] = "./css/aqi/modair.svg?ver=1.4";
$airqual["color25"] = "#F0E640";
$airqual["text"] = "Moderate Air Quality";
}
else if ($airqual["pm25"] < 51 ){
$airqual["image25"] = "./css/aqi/uhair.svg?ver=1.4";
$airqual["color25"] = "#FF5050";
$airqual["text"] = "Poor Air Quality";
}
else if ($airqual["pm25"] < 76 ){
$airqual["image25"] = "./css/aqi/uhair.svg?ver=1.4";
$airqual["color25"] = "#960032";
$airqual["text"] = "Very Poor Air Quality";

}
else {
$airqual["image25"] = "./css/aqi/hazair.svg?ver=1.4";
$airqual["color25"] = "#7d2181";
$airqual["text"] = "Extremely Poor Air Quality";

}

}

//Europe CAQI
if ($airqual["zone"] == "ci"){
 
if ($airqual["pm25"] < 16 ){
$airqual["image25"] = "./css/aqi/goodair.svg?ver=1.4";
$airqual["color25"] = "#7ABC6A";
$airqual["text"] = "Very Low Air Pollution";

}
else if ($airqual["pm25"] < 31){
$airqual["image25"] = "./css/aqi/goodair.svg?ver=1.4";
$airqual["color25"] = "#BBCF4C";
$airqual["text"] = "Low Air Pollution";

}
else if ($airqual["pm25"] < 56){
$airqual["image25"] = "./css/aqi/modair.svg?ver=1.4";
$airqual["color25"] = "#EEC209";
$airqual["text"] = "Medium Air Pollution";

}
else if ($airqual["pm25"] < 111 ){
$airqual["image25"] = "./css/aqi/uhair.svg?ver=1.4";
$airqual["color25"] = "#DB8503";
$airqual["text"] = "High Air Pollution";

}
else {
$airqual["image25"] = "./css/aqi/uhair.svg?ver=1.4";
$airqual["color25"] = "#E8416F";
$airqual["text"] = "Very High Air Pollution";


}

}

//UK
if ($airqual["zone"] == "uk"){

if ($airqual["pm25"] < 12 ){
$airqual["image25"] = "./css/aqi/goodair.svg?ver=1.4";
$airqual["color25"] = "#CCFFCC";
$airqual["text"] = " Low Pollution";

}
else if ($airqual["pm25"] < 24){
$airqual["image25"] = "./css/aqi/goodair.svg?ver=1.4";
$airqual["color25"] = "#66FF66";
$airqual["text"] = " Low Pollution";

}
else if ($airqual["pm25"] < 36){
$airqual["image25"] = "./css/aqi/goodair.svg?ver=1.4";
$airqual["color25"] = "#00FF00";
$airqual["text"] = " Low Pollution";

}
else if ($airqual["pm25"] < 42 ){
$airqual["image25"] = "./css/aqi/modair.svg?ver=1.4";
$airqual["color25"] = "#99FF00";
$airqual["text"] = " Moderate Pollution";

}
else if ($airqual["pm25"] < 48 ){
$airqual["image25"] = "./css/aqi/modair.svg?ver=1.4";
$airqual["color25"] = "#FFFF00";
$airqual["text"] = " Moderate Pollution";

}
else if ($airqual["pm25"] < 54 ){
$airqual["image25"] = "./css/aqi/modair.svg?ver=1.4";
$airqual["color25"] = "#FFCC00";
$airqual["text"] = " Moderate Pollution";

}
else if ($airqual["pm25"] < 59 ){
$airqual["image25"] = "./css/aqi/uhfsair.svg?ver=1.4";
$airqual["color25"] = "#FF6600";
$airqual["text"] = " High Pollution";

}
else if ($airqual["pm25"] < 65 ){
$airqual["image25"] = "./css/aqi/uhair.svg?ver=1.4";
$airqual["color25"] = "#FF3300";
$airqual["text"] = " High Pollution";

}
else if ($airqual["pm25"] < 71 ){
$airqual["image25"] = "./css/aqi/uhair.svg?ver=1.4";
$airqual["color25"] = "#FF0000";
$airqual["text"] = " High Pollution";

}
else {
$airqual["image25"] = "./css/aqi/vhair.svg?ver=1.4";
$airqual["color25"] = "#FF0066";
$airqual["text"] = "Very High Pollution";

}

}

//USA & WAQI
if ($airqual["zone"] == "us"){

function pm25_to_aqi($pm25){
	if ($pm25 > 500.5) {
	  $aqi25 = 500;
	} else if ($pm25 > 350.5 && $pm25 <= 500.5 ) {
	  $aqi25 = map($pm25, 350.5, 500.5, 400, 500);
	} else if ($pm25 > 250.5 && $pm25 <= 350.5 ) {
	  $aqi25 = map($pm25, 250.5, 350.5, 300, 400);
	} else if ($pm25 > 150.5 && $pm25 <= 250.5 ) {
	  $aqi25 = map($pm25, 150.5, 250.5, 200, 300);
	} else if ($pm25 > 55.5 && $pm25 <= 150.5 ) {
	  $aqi25 = map($pm25, 55.5, 150.5, 150, 200);
	} else if ($pm25 > 35.5 && $pm25 <= 55.5 ) {
	  $aqi25 = map($pm25, 35.5, 55.5, 100, 150);
	} else if ($pm25 > 12 && $pm25 <= 35.5 ) {
	  $aqi25 = map($pm25, 12, 35.5, 50, 100);
	} else if ($pm25 > 0 && $pm25 <= 12 ) {
	  $aqi25 = map($pm25, 0, 12, 0, 50);
	}
	return $aqi25;
}


function map($value, $fromLow, $fromHigh, $toLow, $toHigh){
    $fromRange = $fromHigh - $fromLow;
    $toRange = $toHigh - $toLow;
    $scaleFactor = $toRange / $fromRange;

    // Re-zero the value within the from range
    $tmpValue = $value - $fromLow;
    // Rescale the value to the to range
    $tmpValue *= $scaleFactor;
    // Re-zero back to the to range
    return $tmpValue + $toLow;
}

$airqual["aqi25"] = number_format($airqual["pm25"],1);
$airqual["aqi25"] = pm25_to_aqi($airqual["pm25"]);

if ($airqual["aqi25"] < 51 ){
$airqual["image25"] = "./css/aqi/goodair.svg?ver=1.4";
$airqual["color25"] = "#00e400";
$airqual["text"] = "Good Air Quality";

}
else if ($airqual["aqi25"] < 101){
$airqual["image25"] = "./css/aqi/modair.svg?ver=1.4";
$airqual["color25"] = "#ffff00";
$airqual["text"] = "Moderate Air Quality";

}
else if ($airqual["aqi25"] < 151 ){
$airqual["image25"] = "./css/aqi/uhsfhair.svg?ver=1.4";
$airqual["color25"] = "#ff7e00";
$airqual["text"] = "Unhealthy for Sensitive Groups";

}
else if ($airqual["aqi25"] < 201 ){
$airqual["image25"] = "./css/aqi/uhair.svg?ver=1.4";
$airqual["color25"] = "#ff0000";
$airqual["text"] = "Unhealthy Air Quality";

}
else if ($airqual["aqi25"] < 301 ){
$airqual["image25"] = "./css/aqi/vhair.svg?ver=1.4";
$airqual["color25"] = "#8f3f97";
$airqual["text"] = "Very Unhealthy Air Quality";

}
else {
$airqual["image25"] = "./css/aqi/hazair.svg?ver=1.4";
$airqual["color25"] = "#7e0023";
$airqual["text"] = "Hazardous Air Quality";

}


    
}

//Australia
if ($airqual["zone"] == "au"){
$airqual["aqi25"] = round($airqual["pm25"]*4, 0);
if ($airqual["aqi25"] < 34 ){
$airqual["image25"] = "./css/aqi/goodair.svg?ver=1.4";
$airqual["color25"] = "#32ADD3";
$airqual["text"] = "Very Good Air Quality";

}
else if ($airqual["aqi25"] < 67){
$airqual["image25"] = "./css/aqi/goodair.svg?ver=1.4";
$airqual["color25"] = "#99B964";
$airqual["text"] = "Good Air Quality";

}
else if ($airqual["aqi25"] < 100 ){
$airqual["image25"] = "./css/aqi/modhair.svg?ver=1.4";
$airqual["color25"] = "#FFD235";
$airqual["text"] = "Fair Air Quality";

}
else if ($airqual["aqi25"] < 150 ){
$airqual["image25"] = "./css/aqi/uhair.svg?ver=1.4";
$airqual["color25"] = "#EC783A";
$airqual["text"] = "Poor Air Quality";

}
else if ($airqual["aqi25"] < 200 ){
$airqual["image25"] = "./css/aqi/vhair.svg?ver=1.4";
$airqual["color25"] = "#782D49";
$airqual["text"] = "Very Poor Air Quality";

}
else {
$airqual["image25"] = "./css/aqi/hazair.svg?ver=1.4";
$airqual["color25"] = "#D04730";
$airqual["text"] = "Hazardous Air Quality";

}

}


$airqual["qualColor"] = $airqual["color25"];


?>

      <div class="title"><?php echo $info;?><?php echo $lang['airQualityTop'];?></div>

<!--div class="updatedtime1"><?php if(file_exists($livedata)&&time() - filemtime($livedata)>300) echo $offline. '<offline> Offline </offline>'; else echo $online.' '.date($timeFormat);?></div-->

<script src="js/d3.v3.min.js"></script>
<html>
<style>

.aqiTop {
  position: relative; 
  margin-top: 20px; 
  margin-left: 0px;
}

</style>
<script>
	if (theme == 'dark') {
var cityTextFill = "silver";}
else
{var cityTextFill = "rgba(85,85,85,1)";}
</script>

<div class="aqiTop"></div>
<div id="svg"></div>

<script>

	var city = "<?php echo $airqual["city"];?>";

        var aqiA = "<?php echo $airqual["aqi25"];?>";

	var pmA = "<?php echo $airqual["pm25"];?>";
      	
	var qualityA = "<?php echo $airqual["text"];?>";
	  	
	var colorA = "<?php echo $airqual["color25"];?>";

        var colorQ = "<?php echo $airqual["color25"];?>";
		
	var imageA = "<?php echo $airqual["image25"];?>";

	var theme = "<?php echo $theme;?>";
	
   	var svg = d3.select(".aqiTop")
    			.append("svg")
    			//.style("background", "#292E35")
    			.attr("width", 230)
    			.attr("height", 60);

	
	

	
            svg.append("text") // City text output
             	.attr("x", 150)
            	.attr("y", 16)
            	.style("fill", cityTextFill)
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "normal")   				
   				.text(city);
   				
	
	  				                 				   				
   			 svg.append("line") // horizontal lozenge pm 2.5
    			.attr("x1", 120)
    			.attr("x2", 180)
    			.attr("y1", 32.5)
    			.attr("y2", 32.5)
    			.style("stroke", "silver")
    			.style("stroke-width", "12px")
    			.style("stroke-linecap", "round"); 
    			               
			 svg.append("text") // pm 2.5 micro gram text output
             	.attr("x", 150)
            	.attr("y", 36)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "10px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "bold")
   				.text(d3.format(".1f")(pmA)+" "+"μg/m³");
   				
   				               
      		// begin pm 2.5          	
			svg.append("circle")
            	.attr("cx", 50) // main circle
            	.attr("cy", 30)
            	.attr("r", 25)
            	.style('stroke', colorA)
            	.style('fill', colorA);
	            		 	             		            		             		 
            svg.append('image') // image output
    			.attr('xlink:href', imageA)
    			.attr('width', 85)
    			.attr('height', 80)
    			.attr('x', 8)
    			.attr('y', -11);
    				
    		svg.append("text") // Air Quality text output
             	.attr("x", 150)
            	.attr("y", 55)
            	.style("fill", colorQ)
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "normal")
   				.text(qualityA);
   				       
</script>
</html>