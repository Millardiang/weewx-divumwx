<?php
include('dvmCombinedData.php');
//current eq
date_default_timezone_set($TZ);
$json_string	= file_get_contents('jsondata/eq.txt');
$parsed_json	= json_decode($json_string,true);
$eqtitle 		= $parsed_json['features'][0]['properties']['flynn_region'];
$magnitude      = $parsed_json['features'][0]['properties']['mag'];
$depthraw 		= $parsed_json['features'][0]['properties']['depth'];
$depth          = round($depthraw, 1);
$time       	= $parsed_json['features'][0]['properties']['time'];
$lati			= $parsed_json['features'][0]['properties']['lat'];
$longi			= $parsed_json['features'][0]['properties']['lon'];
$eventime		= date("H:i:s j M y", strtotime($time) );
$eqdist 		= round(distance($lat, $lon, $lati, $longi), 1) ;
?>
<?php

$eqdist; if ($wind["units"] == 'mph') {$eqdist = round(distance($lat, $lon, $lati, $longi) * 0.621371, 1) ." miles";
} else {$eqdist = round(distance($lat, $lon, $lati, $longi), 1)." km";}
$eqdista; if ($wind["units"] == 'mph') {$eqdista = round(distance($lat, $lon, $lati, $longi), 1) ."<smallrainunit>&nbsp;km";
} else {$eqdista = round(distance($lat, $lon, $lati, $longi) * 0.621371, 1)."<smallrainunit>&nbsp;miles";} 
?>

    <div class="chartforecast2">
        <span class="yearpopup"><a alt="Earthquakes Worldwide" title="Earthquakes Worldwide" href="dvmEarthquakePopup.php" data-lity><?php echo $chartinfo;?> Worldwide Earthquakes</a></span>
    </div>
    <span class='moduletitle2'><?php echo $lang['earthquakeModule']; ?></valuetitleunit></span>


<div class= "updatedtime1"<span><?php if(file_exists('jsondata/eq.txt')&&time()- filemtime('jsondata/eq.txt')>1800)echo $offline. '<offline> Offline </offline>';else echo $online," ",date($timeFormat, filemtime('jsondata/eq.txt'));?></span></div>

<html>
<script src="js/d3.v3.min.js"></script>

<style>

.quakes {
  position: relative; 
  margin-top: -1.5px; 
  margin-left: 0px;
  z-index: auto;
}

</style>

<div class="quakes"></div>
<div id="svg"></div>

<script>

	var theme = "<?php echo $theme;?>";
	           
    var Location = "<?php echo $eqtitle;?>";
    
	var Magnitude = "<?php echo $magnitude;?>";
	
	var units = "<?php echo $wind["units"];?>";
	
	var Time = "<?php echo $eventime;?>";
	
	var Station = "<?php echo $stationlocation;?>";
	
	var Distance = "<?php echo $eqdist;?>";
	
	var Depth = "<?php echo $depth;?>";
                   
           var svg = d3.select(".quakes")
                .append("svg")
                //.style("background", "#292E35")
                .attr("width", 300)
                .attr("height", 150);
                
          if (theme == 'dark') {      
             
             svg.append("text") // Earthquake Location text output
             	.attr("x", 150)
            	.attr("y", 20)
            	.style("fill", "silver")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "normal")
   				.text(Location);
   			
   			svg.append("text") // Magnitude word output
             	.attr("x", 60)
            	.attr("y", 40)
            	.style("fill", "silver")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "normal")
   				.text("Magnitude");
   				
   			svg.append("text") // Earthquake Magnitude text output
             	.attr("x", 60)
            	.attr("y", 100)
            	.style("fill", "silver")
            	.style("font-family", "Helvetica")
            	.style("font-size", "16px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "normal")
   				.text(d3.format(".1f")(Magnitude));
   		   				
   			svg.append("text") // Time and Date text output
             	.attr("x", 145)
            	.attr("y", 70)
            	.style("fill", "silver")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text(Time);
   				
   			svg.append("text") // Depth text output
             	.attr("x", 145)
            	.attr("y", 85)
            	.style("fill", "silver")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Depth");
   				
   			svg.append("text") // Depth text output
             	.attr("x", 177.5)
            	.attr("y", 85)
            	.style("fill", "#2e8b57")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text(Depth+" km");
   				
   			svg.append("text") // Distance text output
             	.attr("x", 145)
            	.attr("y", 100)
            	.style("fill", "silver")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Epicenter");
   				
   			svg.append("text") // Distance text output
             	.attr("x", 194.5)
            	.attr("y", 100)
            	.style("fill", "#ff964f")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text(Distance);
   				
   			svg.append("text") // Station text output
             	.attr("x", 145)
            	.attr("y", 115)
            	.style("fill", "silver")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("From"+" "+Station);
   			
   		if (Magnitude < 4.0) {	
   			
   			svg.append("text") // category text output
             	.attr("x", 145)
            	.attr("y", 130)
            	.style("fill", "silver")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Category");
   				
   			svg.append("text") // category text output
             	.attr("x", 193)
            	.attr("y", 130)
            	.style("fill", "#2e8b57")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Minor");
   				
   		} else if (Magnitude < 5.0) {
   		
   			svg.append("text") // Category text output
             	.attr("x", 145)
            	.attr("y", 130)
            	.style("fill", "silver")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Category");
   				
   			svg.append("text") // Category text output
             	.attr("x", 193)
            	.attr("y", 130)
            	.style("fill", "#fde396")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Light");
   				
   		} else if (Magnitude < 6.0) {
   		
   			svg.append("text") // Category text output
             	.attr("x", 145)
            	.attr("y", 130)
            	.style("fill", "silver")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Category");
   				
   			svg.append("text") // Category text output
             	.attr("x", 193)
            	.attr("y", 130)
            	.style("fill", "#ff964f")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Moderate");
   				
   		} else if (Magnitude < 7.0) {
   		
   			svg.append("text") // Category text output
             	.attr("x", 145)
            	.attr("y", 130)
            	.style("fill", "silver")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Category");
   				
   			svg.append("text") // Category text output
             	.attr("x", 193)
            	.attr("y", 130)
            	.style("fill", "#ff6181")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Strong");
   				
   		 } else if (Magnitude < 8.0) {
   		 
   		 	svg.append("text") // Category text output
             	.attr("x", 145)
            	.attr("y", 130)
            	.style("fill", "silver")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Category");
   				
   			svg.append("text") // Category text output
             	.attr("x", 193)
            	.attr("y", 130)
            	.style("fill", "#be688b")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Great");
   				
   		 } else if (Magnitude > 8.0) {
   		 
   			svg.append("text") // Category text output
             	.attr("x", 145)
            	.attr("y", 130)
            	.style("fill", "silver")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Category");
   				
   			svg.append("text") // Category text output
             	.attr("x", 193)
            	.attr("y", 130)
            	.style("fill", "#007FFF")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Major");
   				
   		}		
   			   		            
          } else {
                                
             svg.append("text") // Earthquake Location text output
             	.attr("x", 150)
            	.attr("y", 20)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "normal")
   				.text(Location);
   			
   			svg.append("text") // Magnitude word output
             	.attr("x", 60)
            	.attr("y", 40)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "normal")
   				.text("Magnitude");
   				
   			svg.append("text") // Earthquake Magnitude text output
             	.attr("x", 60)
            	.attr("y", 100)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "16px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "normal")
   				.text(d3.format(".1f")(Magnitude));
   		   				
   			svg.append("text") // Time and Date text output
             	.attr("x", 145)
            	.attr("y", 70)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text(Time);
   				
   			svg.append("text") // Depth text output
             	.attr("x", 145)
            	.attr("y", 85)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Depth");
   				
   			svg.append("text") // Depth text output
             	.attr("x", 177.5)
            	.attr("y", 85)
            	.style("fill", "#2e8b57")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text(Depth+" km");
   				
   			svg.append("text") // Distance text output
             	.attr("x", 145)
            	.attr("y", 100)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Epicenter");
   				
   			svg.append("text") // Distance text output
             	.attr("x", 194.5)
            	.attr("y", 100)
            	.style("fill", "#ff964f")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text(Distance);
   				
   			svg.append("text") // Station text output
             	.attr("x", 145)
            	.attr("y", 115)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("From"+" "+Station);
   			
   		if (Magnitude < 4.0) {	
   			
   			svg.append("text") // category text output
             	.attr("x", 145)
            	.attr("y", 130)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Category");
   				
   			svg.append("text") // category text output
             	.attr("x", 193)
            	.attr("y", 130)
            	.style("fill", "#2e8b57")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Minor");
   				
   		} else if (Magnitude < 5.0) {
   		
   			svg.append("text") // Category text output
             	.attr("x", 145)
            	.attr("y", 130)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Category");
   				
   			svg.append("text") // Category text output
             	.attr("x", 193)
            	.attr("y", 130)
            	.style("fill", "#fde396")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Light");
   				
   		} else if (Magnitude < 6.0) {
   		
   			svg.append("text") // Category text output
             	.attr("x", 145)
            	.attr("y", 130)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Category");
   				
   			svg.append("text") // Category text output
             	.attr("x", 193)
            	.attr("y", 130)
            	.style("fill", "#ff964f")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Moderate");
   				
   		} else if (Magnitude < 7.0) {
   		
   			svg.append("text") // Category text output
             	.attr("x", 145)
            	.attr("y", 130)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Category");
   				
   			svg.append("text") // Category text output
             	.attr("x", 193)
            	.attr("y", 130)
            	.style("fill", "#ff6181")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Strong");
   				
   		 } else if (Magnitude < 8.0) {
   		 
   		 	svg.append("text") // Category text output
             	.attr("x", 145)
            	.attr("y", 130)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Category");
   				
   			svg.append("text") // Category text output
             	.attr("x", 193)
            	.attr("y", 130)
            	.style("fill", "#be688b")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Great");
   				
   		 } else if (Magnitude > 8.0) {
   		 
   			svg.append("text") // Category text output
             	.attr("x", 145)
            	.attr("y", 130)
            	.style("fill", "black")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Category");
   				
   			svg.append("text") // Category text output
             	.attr("x", 193)
            	.attr("y", 130)
            	.style("fill", "#007FFF")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "left")
            	.style("font-weight", "normal")
   				.text("Major");
   				
   		}		
   				   	   		     
      }
      
      if (Magnitude < 4.0) {		
   				
   			svg.append("circle")
            		 .attr("cx", 60) // center circle
            		 .attr("cy", 95)
            		 .attr("r", 17)
            		 .attr('stroke', '#2e8b57')
            		 .attr('fill', 'none')
            		 .attr('stroke-width', 2.5);
            		 
            svg.append("circle")
            		 .attr("cx", 60) // center circle
            		 .attr("cy", 95)
            		 .attr("r", 27)
            		 .attr('stroke', '#2e8b57')
            		 .attr('fill', 'none')
            		 .attr('stroke-width', 2);
   				
			svg.append("circle")
            		 .attr("cx", 60) // center circle
            		 .attr("cy", 95)
            		 .attr("r", 37)
            		 .attr('stroke', '#2e8b57')
            		 .attr('fill', 'none')
            		 .attr('stroke-width', 1);
            		 
            svg.append("circle")
            		 .attr("cx", 60) // center circle
            		 .attr("cy", 95)
            		 .attr("r", 47)
            		 .attr('stroke', '#2e8b57')
            		 .attr('fill', 'none')
            		 .attr('stroke-width', 0.5);
            		 
            } else if (Magnitude < 5.0) {
            
            svg.append("circle")
            		 .attr("cx", 60) // center circle
            		 .attr("cy", 95)
            		 .attr("r", 17)
            		 .attr('stroke', '#fde396')
            		 .attr('fill', 'none')
            		 .attr('stroke-width', 2.5);
            		 
            svg.append("circle")
            		 .attr("cx", 60) // center circle
            		 .attr("cy", 95)
            		 .attr("r", 27)
            		 .attr('stroke', '#fde396')
            		 .attr('fill', 'none')
            		 .attr('stroke-width', 2);
   				
			svg.append("circle")
            		 .attr("cx", 60) // center circle
            		 .attr("cy", 95)
            		 .attr("r", 37)
            		 .attr('stroke', '#fde396')
            		 .attr('fill', 'none')
            		 .attr('stroke-width', 1);
            		 
            svg.append("circle")
            		 .attr("cx", 60) // center circle
            		 .attr("cy", 95)
            		 .attr("r", 47)
            		 .attr('stroke', '#fde396')
            		 .attr('fill', 'none')
            		 .attr('stroke-width', 0.5);
            
           } else if (Magnitude < 6.0) { 
            
            svg.append("circle")
            		 .attr("cx", 60) // center circle
            		 .attr("cy", 95)
            		 .attr("r", 17)
            		 .attr('stroke', '#ff964f')
            		 .attr('fill', 'none')
            		 .attr('stroke-width', 2.5);
            		 
            svg.append("circle")
            		 .attr("cx", 60) // center circle
            		 .attr("cy", 95)
            		 .attr("r", 27)
            		 .attr('stroke', '#ff964f')
            		 .attr('fill', 'none')
            		 .attr('stroke-width', 2);
   				
			svg.append("circle")
            		 .attr("cx", 60) // center circle
            		 .attr("cy", 95)
            		 .attr("r", 37)
            		 .attr('stroke', '#ff964f')
            		 .attr('fill', 'none')
            		 .attr('stroke-width', 1);
            		 
            svg.append("circle")
            		 .attr("cx", 60) // center circle
            		 .attr("cy", 95)
            		 .attr("r", 47)
            		 .attr('stroke', '#ff964f')
            		 .attr('fill', 'none')
            		 .attr('stroke-width', 0.5);
                        
            } else if (Magnitude < 7.0) {
                        
            svg.append("circle")
            		 .attr("cx", 60) // center circle
            		 .attr("cy", 95)
            		 .attr("r", 17)
            		 .attr('stroke', '#ff6181')
            		 .attr('fill', 'none')
            		 .attr('stroke-width', 2.5);
            		 
            svg.append("circle")
            		 .attr("cx", 60) // center circle
            		 .attr("cy", 95)
            		 .attr("r", 27)
            		 .attr('stroke', '#ff6181')
            		 .attr('fill', 'none')
            		 .attr('stroke-width', 2);
   				
			svg.append("circle")
            		 .attr("cx", 60) // center circle
            		 .attr("cy", 95)
            		 .attr("r", 37)
            		 .attr('stroke', '#ff6181')
            		 .attr('fill', 'none')
            		 .attr('stroke-width', 1);
            		 
            svg.append("circle")
            		 .attr("cx", 60) // center circle
            		 .attr("cy", 95)
            		 .attr("r", 47)
            		 .attr('stroke', '#ff6181')
            		 .attr('fill', 'none')
            		 .attr('stroke-width', 0.5);
            
            } else if (Magnitude < 8.0) {
            
            svg.append("circle")
            		 .attr("cx", 60) // center circle
            		 .attr("cy", 95)
            		 .attr("r", 17)
            		 .attr('stroke', '#be688b')
            		 .attr('fill', 'none')
            		 .attr('stroke-width', 2.5);
            		 
            svg.append("circle")
            		 .attr("cx", 60) // center circle
            		 .attr("cy", 95)
            		 .attr("r", 27)
            		 .attr('stroke', '#be688b')
            		 .attr('fill', 'none')
            		 .attr('stroke-width', 2);
   				
			svg.append("circle")
            		 .attr("cx", 60) // center circle
            		 .attr("cy", 95)
            		 .attr("r", 37)
            		 .attr('stroke', '#be688b')
            		 .attr('fill', 'none')
            		 .attr('stroke-width', 1);
            		 
            svg.append("circle")
            		 .attr("cx", 60) // center circle
            		 .attr("cy", 95)
            		 .attr("r", 47)
            		 .attr('stroke', '#be688b')
            		 .attr('fill', 'none')
            		 .attr('stroke-width', 0.5);
            
            } else if (Magnitude > 8.0) {
            
            svg.append("circle")
            		 .attr("cx", 60) // center circle
            		 .attr("cy", 95)
            		 .attr("r", 17)
            		 .attr('stroke', '#007FFF')
            		 .attr('fill', 'none')
            		 .attr('stroke-width', 2.5);
            		 
            svg.append("circle")
            		 .attr("cx", 60) // center circle
            		 .attr("cy", 95)
            		 .attr("r", 27)
            		 .attr('stroke', '#007FFF')
            		 .attr('fill', 'none')
            		 .attr('stroke-width', 2);
   				
			svg.append("circle")
            		 .attr("cx", 60) // center circle
            		 .attr("cy", 95)
            		 .attr("r", 37)
            		 .attr('stroke', '#007FFF')
            		 .attr('fill', 'none')
            		 .attr('stroke-width', 1);
            		 
            svg.append("circle")
            		 .attr("cx", 60) // center circle
            		 .attr("cy", 95)
            		 .attr("r", 47)
            		 .attr('stroke', '#007FFF')
            		 .attr('fill', 'none')
            		 .attr('stroke-width', 0.5);
            
            }
</script>
</html>
