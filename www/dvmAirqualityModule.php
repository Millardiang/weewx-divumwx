<?php
include('dvmCombinedData.php');
include('common.php');

$airqual["units"] = $air["pm_units"];
$airqual["city"] = $stationlocation;

if ($airqual["source"] == "purple") {
$json_string = file_get_contents("jsondata/pu.txt");
$parsed_json = json_decode($json_string, true);
$airqual["pm25"] = $parsed_json["sensor"]["stats"]["pm2.5_24hour"];
$airqual["pm10"] = $parsed_json["sensor"]["pm10.0"];
}
else if ($airqual["source"] == "weewx") {
$airqual["pm25"] = $air["24h.rollingavg.pm2_5"];
$airqual["pm10"] = $air["24h.rollingavg.pm10_0"];
}
else if ($airqual["source"] == "waqi") {
$json_string = file_get_contents("jsondata/aq.txt");
$parsed_json = json_decode($json_string, true);
$airqual["pm25"] = $parsed_json['data']['iaqi']['pm25']['v'];
$airqual["pm10"] = $parsed_json['data']['iaqi']['pm10']['v'];
}
else if ($airqual["source"] == "sds"){
$json_string = file_get_contents("jsondata/aqiJson.txt");
$parsed_json = json_decode($json_string, true);
$airqual["pm25"] = round($parsed_json['pm25'],1);
$airqual["pm10"] = round($parsed_json['pm10'],1);
}

//Europe
if ($airqual["zone"] == "eu"){

if ($airqual["pm25"] < 11 ){
$airqual["image25"] = "./css/aqi/goodair.svg?ver=1.4";
$airqual["color25"] = "#51F0E6";
$airqual["text25"] = "Good Air Quality";
$airqual["priority25"] = 1;
}
else if ($airqual["pm25"] < 21){
$airqual["image25"] = "./css/aqi/goodair.svg?ver=1.4";
$airqual["color25"] = "#51CBA9";
$airqual["text25"] = "Fair Air Quality";
$airqual["priority25"] = 2;
}
else if ($airqual["pm25"] < 26){
$airqual["image25"] = "./css/aqi/modair.svg?ver=1.4";
$airqual["color25"] = "#F0E640";
$airqual["text25"] = "Moderate Air Quality";
$airqual["priority25"] = 3;
}
else if ($airqual["pm25"] < 51 ){
$airqual["image25"] = "./css/aqi/uhair.svg?ver=1.4";
$airqual["color25"] = "#FF5050";
$airqual["text25"] = "Poor Air Quality";
$airqual["priority25"] = 4;
}
else if ($airqual["pm25"] < 76 ){
$airqual["image25"] = "./css/aqi/uhair.svg?ver=1.4";
$airqual["color25"] = "#960032";
$airqual["text25"] = "Very Poor Air Quality";
$airqual["priority25"] = 5;
}
else {
$airqual["image25"] = "./css/aqi/hazair.svg?ver=1.4";
$airqual["color25"] = "#7d2181";
$airqual["text25"] = "Extremely Poor Air Quality";
$airqual["priority25"] = 6;
}

if ($airqual["pm10"] < 21){
$airqual["image10"] = "./css/aqi/goodair.svg?ver=1.4";
$airqual["color10"] = "#51F0E6";
$airqual["text10"] = "Good Air Quality";
$airqual["priority10"] = 1;
}
else if ($airqual["pm10"] < 41 ){
$airqual["image10"] = "./css/aqi/modair.svg?ver=1.4";
$airqual["color10"] = "#F0E640";
$airqual["text10"] = "Moderate Air Quality";
$airqual["priority10"] = 2;
}
else if ($airqual["pm10"] < 51 ){
$airqual["image10"] = "./css/aqi/uhfsair.svg?ver=1.4";
$airqual["color10"] = "#FF5050";
$airqual["text10"] = "Poor Air Quality";
$airqual["priority10"] = 3;
}
else if ($airqual["pm10"] < 151 ){
$airqual["image10"] = "./css/aqi/uhair.svg?ver=1.4";
$airqual["color10"] = "#960032";
$airqual["text10"] = "Very Poor Air Quality";
$airqual["priority10"] = 4;}
else 
{
$airqual["image10"] = "./css/aqi/hazair.svg?ver=1.4";
$airqual["color10"] = "#7D2181";
$airqual["text10"] = "Extremely Poor Air Quality";
$airqual["priority10"] = 5;
}

}
//UK
if ($airqual["zone"] == "uk"){

if ($airqual["pm25"] < 12 ){
$airqual["image25"] = "./css/aqi/goodair.svg?ver=1.4";
$airqual["color25"] = "#CCFFCC";
$airqual["text25"] = " Low Pollution - AQI 1";
$airqual["priority25"] = 1;
}
else if ($airqual["pm25"] < 24){
$airqual["image25"] = "./css/aqi/goodair.svg?ver=1.4";
$airqual["color25"] = "#66FF66";
$airqual["text25"] = " Low Pollution - AQI 2";
$airqual["priority25"] = 2;
}
else if ($airqual["pm25"] < 36){
$airqual["image25"] = "./css/aqi/goodair.svg?ver=1.4";
$airqual["color25"] = "#00FF00";
$airqual["text25"] = " Low Pollution - AQI 3";
$airqual["priority25"] = 3;
}
else if ($airqual["pm25"] < 42 ){
$airqual["image25"] = "./css/aqi/modair.svg?ver=1.4";
$airqual["color25"] = "#99FF00";
$airqual["text25"] = " Moderate Pollution - AQI 4";
$airqual["priority25"] = 4;
}
else if ($airqual["pm25"] < 48 ){
$airqual["image25"] = "./css/aqi/modair.svg?ver=1.4";
$airqual["color25"] = "#FFFF00";
$airqual["text25"] = " Moderate Pollution - AQI 5";
$airqual["priority25"] = 5;
}
else if ($airqual["pm25"] < 54 ){
$airqual["image25"] = "./css/aqi/modair.svg?ver=1.4";
$airqual["color25"] = "#FFCC00";
$airqual["text25"] = " Moderate Pollution - AQI 6";
$airqual["priority25"] = 6;
}
else if ($airqual["pm25"] < 59 ){
$airqual["image25"] = "./css/aqi/uhfsair.svg?ver=1.4";
$airqual["color25"] = "#FF6600";
$airqual["text25"] = " High Pollution - AQI 7";
$airqual["priority25"] = 7;
}
else if ($airqual["pm25"] < 65 ){
$airqual["image25"] = "./css/aqi/uhair.svg?ver=1.4";
$airqual["color25"] = "#FF3300";
$airqual["text25"] = " High Pollution - AQI 8";
$airqual["priority25"] = 8;
}
else if ($airqual["pm25"] < 71 ){
$airqual["image25"] = "./css/aqi/uhair.svg?ver=1.4";
$airqual["color25"] = "#FF0000";
$airqual["text25"] = " High Pollution - AQI 9";
$airqual["priority25"] = 9;
}
else {
$airqual["image25"] = "./css/aqi/vhair.svg?ver=1.4";
$airqual["color25"] = "#FF0066";
$airqual["text25"] = "Very High Pollution - AQI 10";
$airqual["priority25"] = 10;
}

if ($airqual["pm10"] < 17 ){
$airqual["image10"] = "./css/aqi/goodair.svg?ver=1.4";
$airqual["color10"] = "#CCFFCC";
$airqual["text10"] = " Low Pollution - AQI 1";
$airqual["priority10"] = 1;
}
else if ($airqual["pm10"] < 34){
$airqual["image10"] = "./css/aqi/goodair.svg?ver=1.4";
$airqual["color10"] = "#66FF66";
$airqual["text10"] = " Low Pollution - AQI 2";
$airqual["priority10"] = 2;
}
else if ($airqual["pm10"] < 51){
$airqual["image10"] = "./css/aqi/goodair.svg?ver=1.4";
$airqual["color10"] = "#00FF00";
$airqual["text10"] = " Low Pollution - AQI 3";
$airqual["priority10"] = 3;
}
else if ($airqual["pm10"] < 59 ){
$airqual["image10"] = "./css/aqi/modair.svg?ver=1.4";
$airqual["color10"] = "#99FF00";
$airqual["text10"] = " Moderate Pollution - AQI 4";
$airqual["priority10"] = 4;
}
else if ($airqual["pm10"] < 67 ){
$airqual["image10"] = "./css/aqi/modair.svg?ver=1.4";
$airqual["color10"] = "#FFFF00";
$airqual["text10"] = " Moderate Pollution - AQI 5";
$airqual["priority10"] = 5;
}
else if ($airqual["pm10"] < 76 ){
$airqual["image10"] = "./css/aqi/modair.svg?ver=1.4";
$airqual["color10"] = "#FFCC00";
$airqual["text10"] = " Moderate Pollution - AQI 6";
$airqual["priority10"] = 6;
}
else if ($airqual["pm10"] < 84 ){
$airqual["image10"] = "./css/aqi/uhfsair.svg?ver=1.4";
$airqual["color10"] = "#FF6600";
$airqual["text10"] = " High Pollution - AQI 7";
$airqual["priority10"] = 7;
}
else if ($airqual["pm10"] < 92 ){
$airqual["image10"] = "./css/aqi/uhair.svg?ver=1.4";
$airqual["color10"] = "#FF3300";
$airqual["text10"] = " High Pollution - AQI 8";
$airqual["priority10"] = 8;
}
else if ($airqual["pm10"] < 101 ){
$airqual["image10"] = "./css/aqi/uhair.svg?ver=1.4";
$airqual["color10"] = "#FF0000";
$airqual["text10"] = " High Pollution - AQI 9";
$airqual["priority10"] = 9;
}
else {
$airqual["image10"] = "./css/aqi/vhair.svg?ver=1.4";
$airqual["color10"] = "#FF0066";
$airqual["text10"] = "Very High Pollution - AQI 10";
$airqual["priority10"] = 10;
}


}

//USA & WAQI
if ($airqual["zone"] == "us"){

if ($airqual["pm25"] < 51 ){
$airqual["image25"] = "./css/aqi/goodair.svg?ver=1.4";
$airqual["color25"] = "#00e400";
$airqual["text25"] = "Good Air Quality";
$airqual["priority25"] = 1;
}
else if ($airqual["pm25"] < 101){
$airqual["image25"] = "./css/aqi/modair.svg?ver=1.4";
$airqual["color25"] = "#ffff00";
$airqual["text25"] = "Moderate Air Quality";
$airqual["priority25"] = 2;
}
else if ($airqual["pm25"] < 151 ){
$airqual["image25"] = "./css/aqi/uhsfhair.svg?ver=1.4";
$airqual["color25"] = "#ff7e00";
$airqual["text25"] = "Unhealthy for Sensitive Groups";
$airqual["priority25"] = 3;
}
else if ($airqual["pm25"] < 201 ){
$airqual["image25"] = "./css/aqi/uhair.svg?ver=1.4";
$airqual["color25"] = "#ff0000";
$airqual["text25"] = "Unhealthy Air Quality";
$airqual["priority25"] = 4;
}
else if ($airqual["pm25"] < 301 ){
$airqual["image25"] = "./css/aqi/vhair.svg?ver=1.4";
$airqual["color25"] = "#8f3f97";
$airqual["text25"] = "Very Unhealthy Air Quality";
$airqual["priority25"] = 5;
}
else {
$airqual["image25"] = "./css/aqi/hazair.svg?ver=1.4";
$airqual["color25"] = "#7e0023";
$airqual["text25"] = "Hazardous Air Quality";
$airqual["priority25"] = 6;
}

if ($airqual["pm10"] < 51 ){
    $airqual["image10"] = "./css/aqi/goodair.svg?ver=1.4";
    $airqual["color10"] = "#00e400";
    $airqual["text10"] = "Good Air Quality";
    $airqual["priority10"] = 1;
    }
    else if ($airqual["pm10"] < 101){
    $airqual["image10"] = "./css/aqi/modair.svg?ver=1.4";
    $airqual["color10"] = "#ffff00";
    $airqual["text10"] = "Moderate Air Quality";
    $airqual["priority10"] = 2;
    }
    else if ($airqual["pm10"] < 151 ){
    $airqual["image10"] = "./css/aqi/uhsfhair.svg?ver=1.4";
    $airqual["color10"] = "#ff7e00";
    $airqual["text10"] = "Unhealthy for Sensitive Groups";
    $airqual["priority10"] = 3;
    }
    else if ($airqual["pm10"] < 201 ){
    $airqual["image10"] = "./css/aqi/uhair.svg?ver=1.4";
    $airqual["color10"] = "#ff0000";
    $airqual["text10"] = "Unhealthy Air Quality";
    $airqual["priority10"] = 4;
    }
    else if ($airqual["pm10"] < 301 ){
    $airqual["image25"] = "./css/aqi/vhair.svg?ver=1.4";
    $airqual["color25"] = "#8f3f97";
    $airqual["text25"] = "Very Unhealthy Air Quality";
    $airqual["priority25"] = 5;
    }
    else {
    $airqual["image25"] = "./css/aqi/hazair.svg?ver=1.4";
    $airqual["color25"] = "#7e0023";
    $airqual["text25"] = "Hazardous Air Quality";
    $airqual["priority25"] = 6;
    }
    
}

//Australia
if ($airqual["zone"] == "au"){

if ($airqual["pm25"] < 34 ){
$airqual["image25"] = "./css/aqi/goodair.svg?ver=1.4";
$airqual["color25"] = "#32ADD3";
$airqual["text25"] = "Very Good Air Quality";
$airqual["priority25"] = 1;
}
else if ($airqual["pm25"] < 67){
$airqual["image25"] = "./css/aqi/goodair.svg?ver=1.4";
$airqual["color25"] = "#99B964";
$airqual["text25"] = "Good Air Quality";
$airqual["priority25"] = 2;
}
else if ($airqual["pm25"] < 100 ){
$airqual["image25"] = "./css/aqi/modhair.svg?ver=1.4";
$airqual["color25"] = "#FFD235";
$airqual["text25"] = "Fair Air Quality";
$airqual["priority25"] = 3;
}
else if ($airqual["pm25"] < 150 ){
$airqual["image25"] = "./css/aqi/uhair.svg?ver=1.4";
$airqual["color25"] = "#EC783A";
$airqual["text25"] = "Poor Air Quality";
$airqual["priority25"] = 4;
}
else if ($airqual["pm25"] < 200 ){
$airqual["image25"] = "./css/aqi/vhair.svg?ver=1.4";
$airqual["color25"] = "#782D49";
$airqual["text25"] = "Very Poor Air Quality";
$airqual["priority25"] = 5;
}
else {
$airqual["image25"] = "./css/aqi/hazair.svg?ver=1.4";
$airqual["color25"] = "#D04730";
$airqual["text25"] = "Hazardous Air Quality";
$airqual["priority25"] = 6;
}

if ($airqual["pm10"] < 34 ){
    $airqual["image10"] = "./css/aqi/goodair.svg?ver=1.4";
    $airqual["color10"] = "#32ADD3";
    $airqual["text10"] = "Very Good Air Quality";
    $airqual["priority10"] = 1;
    }
    else if ($airqual["pm10"] < 67){
    $airqual["image10"] = "./css/aqi/goodair.svg?ver=1.4";
    $airqual["color10"] = "#99B964";
    $airqual["text10"] = "Good Air Quality";
    $airqual["priority10"] = 2;
    }
    else if ($airqual["pm10"] < 100 ){
    $airqual["image10"] = "./css/aqi/modhair.svg?ver=1.4";
    $airqual["color10"] = "#FFD235";
    $airqual["text10"] = "Fair Air Quality";
    $airqual["priority10"] = 3;
    }
    else if ($airqual["pm10"] < 150 ){
    $airqual["image10"] = "./css/aqi/uhair.svg?ver=1.4";
    $airqual["color10"] = "#EC783A";
    $airqual["text10"] = "Poor Air Quality";
    $airqual["priority10"] = 4;
    }
    else if ($airqual["pm10"] < 200 ){
    $airqual["image10"] = "./css/aqi/vhair.svg?ver=1.4";
    $airqual["color10"] = "#782D49";
    $airqual["text10"] = "Very Poor Air Quality";
    $airqual["priority10"] = 5;
    }
    else {
    $airqual["image10"] = "./css/aqi/hazair.svg?ver=1.4";
    $airqual["color10"] = "#D04730";
    $airqual["text10"] = "Hazardous Air Quality";
    $airqual["priority10"] = 6;
    }
}

if ($airqual["priority25"] > $airqual["priority10"])
{$airqual["text"] = $airqual["text25"];
$airqual["qualColor"] = $airqual["color25"];
}
else {$airqual["text"] = $airqual["text10"];
$airqual["qualColor"] = $airqual["color10"];
}


?>


<div class="updatedtime1"><?php if(file_exists($livedata)&&time() - filemtime($livedata)>300) echo $offline. '<offline> Offline </offline>'; else echo $online.' '.date($timeFormat);?></div>

<script src="js/d3.v3.min.js"></script>
<html>
<style>

.aqi {
  position: relative; 
  margin-top: -1.5px; 
  margin-left: 0px;
  //z-index: auto;
}

</style>

<div class="aqi"></div>
<div id="svg"></div>

<script>

	var aqiA = "<?php echo $airqual["pm25"];?>";
	var aqiB = "<?php echo $airqual["pm10"];?>";
	var city = "<?php echo $airqual["city"];?>";
	
	var qualityA = "<?php echo $airqual["text"];?>";
	var qualityB = "<?php echo $airqual["text"];?>";
	  	
	var colorA = "<?php echo $airqual["color25"];?>";
	var colorB = "<?php echo $airqual["color10"];?>"; 
        var colorQ = "<?php echo $airqual["qualColor"];?>";
		
	var imageA = "<?php echo $airqual["image25"];?>";
	var imageB = "<?php echo $airqual["image10"];?>";
	
	
   	var svg = d3.select(".aqi")
                .append("svg")
                //.style("background", "#292E35")
                .attr("width", 300)
                .attr("height", 150);
  
            svg.append("text") // City text output
             	.attr("x", 150)
            	.attr("y", 20)
            	.style("fill", "silver")
            	.style("font-family", "Helvetica")
            	.style("font-size", "12px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "normal")   				
   				.text(city);
   				                 				   				
   			 svg.append("line") // horizontal lozenge left
    			.attr("x1", 82)
    			.attr("x2", 135)
    			.attr("y1", 104)
    			.attr("y2", 104)
    			.style("stroke", "silver")
    			.style("stroke-width", "12px")
    			.style("stroke-linecap", "round"); 
    			
    		svg.append("line") // horizontal lozenge right
    			.attr("x1", 234)
    			.attr("x2", 287)
    			.attr("y1", 104)
    			.attr("y2", 104)
    			.style("stroke", "silver")
    			.style("stroke-width", "12px")
    			.style("stroke-linecap", "round");
                
			 svg.append("text") // pm 2.5 micro gram text output
             	.attr("x", 108.5)
            	.attr("y", 107)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "10px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "bold")
   				.text(d3.format(".1f")(aqiA)+" "+"μg/m³");
   				
   			svg.append("text") // pm 10 micro gram text output
             	.attr("x", 261)
            	.attr("y", 107)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "10px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "bold")
   				.text(d3.format(".1f")(aqiB)+" "+"μg/m³");
	
   				               
      		// begin pm 2.5          	
			svg.append("circle")
            	.attr("cx", 50) // main circle
            	.attr("cy", 75)
            	.attr("r", 32)
            	.style('stroke', colorA)
            	.style('fill', colorA);
	 
            svg.append("rect") // fill rectangle
    			.attr("x", 65)
    			.attr("y", 54.5)    			
    			.attr("height", 41)
    			.attr("width", 40)
    			.style("fill", colorA);
    				              		 
           	svg.append("circle")
            	.attr("cx", 105) // side circle
            	.attr("cy", 75)
            	.attr("r", 20)
            	.style('stroke', colorA)
            	.style('fill', colorA);
            		 
            svg.append("circle")
            	.attr("cx", 50) // center dark ring
            	.attr("cy", 75)
            	.attr("r", 34)
            	.style('stroke', '#1e2024')
            	.style('fill', 'none')
            	.style('stroke-width', 3);		             		 
            		             		 
            svg.append('image') // image output
    			.attr('xlink:href', imageA)
    			.attr('width', 110)
    			.attr('height', 100)
    			.attr('x', -4)
    			.attr('y', 24);
    				
    		svg.append("text") // Air Quality text output
             	.attr("x", 150)
            	.attr("y", 135)
            	.style("fill", colorQ)
            	.style("font-family", "Helvetica")
            	.style("font-size", "12px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "normal")
   				.text(qualityA);
   				
   			svg.append("text") // AQ Index text output
             	.attr("x", 90)
            	.attr("y", 82)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "18px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("2.5");
		 
		           		             		 
			// begin pm 10		
			svg.append("circle")
            	.attr("cx", 202) // main circle
            	.attr("cy", 75)
            	.attr("r", 32)
            	.style('stroke', colorB)
            	.style('fill', colorB);
            		 
            svg.append("rect") // fill rectangle
    			.attr("x", 215)
    			.attr("y", 54.5)
    			.attr("rx", 0)    			
    			.attr("height", 41)
    			.attr("width", 40)
    			.style("fill", colorB);
    				              		 
           	svg.append("circle")
            	.attr("cx", 257) // side circle
            	.attr("cy", 75)
            	.attr("r", 20)
            	.style('stroke', colorB)
            	.style('fill', colorB);
           		 
            svg.append("circle")
            	.attr("cx", 202) // center dark ring
            	.attr("cy", 75)
            	.attr("r", 34)
            	.style('stroke', '#1e2024')
            	.style('fill', 'none')
            	.style('stroke-width', 3);		             		 
             		             		 
            svg.append('image') // image output
    			.attr('xlink:href', imageB)
    			.attr('width', 110)
    			.attr('height', 100)
    			.attr('x', 148)
    			.attr('y', 24);
    				   				
   			svg.append("text") // AQ Index text output
             	.attr("x", 242)
            	.attr("y", 82)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "18px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("10");
		          
</script>
</html>
