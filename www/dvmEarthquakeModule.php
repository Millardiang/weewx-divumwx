<?php
##############################################################################################
#        ________   __  ___      ___  ____  ____  ___      ___    __   __  ___  ___  ___     #
#       |"      "\ |" \|"  \    /"  |("  _||_ " ||"  \    /"  |  |"  |/  \|  "||"  \/"  |    #
#       (.  ___  :)||  |\   \  //  / |   (  ) : | \   \  //   |  |'  /    \:  | \   \  /     #
#       |: \   ) |||:  | \\  \/. ./  (:  |  | . ) /\\  \/.    |  |: /'        |  \\  \/      #
#       (| (___\ |||.  |  \.    //    \\ \__/ // |: \.        |   \//  /\'    |  /\.  \      #
#       |:       :)/\  |\  \\   /     /\\ __ //\ |.  \    /:  |   /   /  \\   | /  \   \     #
#       (________/(__\_|_)  \__/     (__________)|___|\__/|___|  |___/    \___||___/\___|    #
#                                                                                            #
#     Copyright (C) 2023 Ian Millard, Steven Sheeley, Sean Balfour. All rights reserved      #
#      Distributed under terms of the GPLv3.  See the file LICENSE.txt for your rights.      #
#    Issues for weewx-divumwx skin template are only addressed via the issues register at    #
#                    https://github.com/Millardiang/weewx-divumwx/issues                     #
##############################################################################################
include('dvmCombinedData.php');
date_default_timezone_set($TZ);
$json_string    = file_get_contents('jsondata/eq.txt');
$parsed_json    = json_decode($json_string,true);
$eqtitle        = $parsed_json['features'][0]['properties']['flynn_region'];
$magnitude      = $parsed_json['features'][0]['properties']['mag'];
$depthraw       = $parsed_json['features'][0]['properties']['depth'];
$depth          = round($depthraw, 1);
$time           = $parsed_json['features'][0]['properties']['time'];
$lati           = $parsed_json['features'][0]['properties']['lat'];
$longi          = $parsed_json['features'][0]['properties']['lon'];
$eventime       = date("H:i:s j M y", strtotime($time) );
$eqdist         = round(distance($lat, $lon, $lati, $longi), 1) ;

$eqdist; if ($wind["units"] == 'mph') {$eqdist = round(distance($lat, $lon, $lati, $longi) * 0.621371, 1) ." miles";
} else {$eqdist = round(distance($lat, $lon, $lati, $longi), 1)." km";}
$eqdista; if ($wind["units"] == 'mph') {$eqdista = round(distance($lat, $lon, $lati, $longi), 1) ."<smallrainunit>&nbsp;km";
} else {$eqdista = round(distance($lat, $lon, $lati, $longi) * 0.621371, 1)."<smallrainunit>&nbsp;miles";} 
?>

<!DOCTYPE html>
<html lang="en">

<div class="chartforecast">
<span class="yearpopup"><a alt="Earthquakes Worldwide" title="Earthquakes Worldwide" href="dvmEarthquakePopup.php" data-lity><?php echo $chartinfo;?> Worldwide Earthquakes</a></span>
<span class="yearpopup"><a alt="Earthquake Map" title="Earthquakes Map" href="dvmEarthquakeMapPopup.php" data-lity><?php echo $chartinfo;?> World Earthquake Map</a></span>
</div>
<span class='moduletitle'><?php echo $lang['earthquakeModule']; ?></valuetitleunit></span>

<div class= "updatedtime1"><span><?php if(file_exists('jsondata/eq.txt')&&time() - filemtime('jsondata/eq.txt')>3600) echo $offline. '<offline> Offline </offline>';else echo $online," ",date($timeFormat, filemtime('jsondata/eq.txt'));?></span></div>

<script src="js/d3.7.9.0.min.js"></script>


<div class="quakes"></div>


<script>

    var baseTextColor = "var(--col-6)";

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
                     
             
             svg.append("text") // Earthquake Location text output
                .attr("x", 150)
                .attr("y", 20)
                .style("fill", baseTextColor)
                .style("font-family", "Helvetica")
                .style("font-size", "11px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text(Location);
            
            svg.append("text") // Magnitude word output
                .attr("x", 60)
                .attr("y", 40)
                .style("fill", baseTextColor)
                .style("font-family", "Helvetica")
                .style("font-size", "11px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text("Magnitude");
                
            svg.append("text") // Earthquake Magnitude text output
                .attr("x", 60)
                .attr("y", 100)
                .style("fill", baseTextColor)
                .style("font-family", "Helvetica")
                .style("font-size", "16px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text(d3.format(".1f")(Magnitude));
                        
            svg.append("text") // Time and Date text output
                .attr("x", 145)
                .attr("y", 70)
                .style("fill", baseTextColor)
                .style("font-family", "Helvetica")
                .style("font-size", "11px")
                .style("text-anchor", "left")
                .style("font-weight", "normal")
                .text(Time);
                
            svg.append("text") // Depth text output
                .attr("x", 145)
                .attr("y", 85)
                .style("fill", baseTextColor)
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
                .style("fill", baseTextColor)
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
                .style("fill", baseTextColor)
                .style("font-family", "Helvetica")
                .style("font-size", "11px")
                .style("text-anchor", "left")
                .style("font-weight", "normal")
                .text("From"+" "+Station);
            
        if (Magnitude < 4.0) {  
            
            svg.append("text") // category text output
                .attr("x", 145)
                .attr("y", 130)
                .style("fill", baseTextColor)
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
                .style("fill", baseTextColor)
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
                .style("fill", baseTextColor)
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
                .style("fill", baseTextColor)
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
                .style("fill", baseTextColor)
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
                .style("fill", baseTextColor)
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
                     .style('stroke', '#2e8b57')
                     .style('fill', 'none')
                     .style('stroke-width', 2);
                                        
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

            var height = 150;   
            var y = d3.scaleOrdinal().domain(d3.range(1)).range([0, height]);

            svg.selectAll("circle.one")
                    .data(y.domain())
                    .enter()
                    .append("circle")
                    .attr("class", "one")
                    .attr("stroke-width", 2.5)
                    .style('stroke', "#2e8b57")
                    .style('fill', "none")
                    .attr("r", 17)
                    .attr("cx", 60)
                    .attr("cy", 95)
                    .each(pulse);

            function pulse() {
            var circle = svg.select("circle.one");
            circle = circle.transition()
                    .attr("stroke-width", 2.5)
                    .attr("r", 17)
                    .transition()
                    .duration(3000)
                    .attr('stroke-width', 0.0)
                    .attr("r", 47)
                    .ease(d3.easeSin);
                    }
                     
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

            var height = 150;   
            var y = d3.scaleOrdinal().domain(d3.range(1)).range([0, height]);

            svg.selectAll("circle.one")
                    .data(y.domain())
                    .enter()
                    .append("circle")
                    .attr("class", "one")
                    .attr("stroke-width", 2.5)
                    .style('stroke', "#fde396")
                    .style('fill', "none")
                    .attr("r", 17)
                    .attr("cx", 60)
                    .attr("cy", 95)
                    .each(pulse);

            function pulse() {
            var circle = svg.select("circle.one");
            circle = circle.transition()
                    .attr("stroke-width", 2.5)
                    .attr("r", 17)
                    .transition()
                    .duration(3000)
                    .attr('stroke-width', 0.0)
                    .attr("r", 47)
                    .ease(d3.easeSin);        
                    }
            
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

            var height = 150;   
            var y = d3.scaleOrdinal().domain(d3.range(1)).range([0, height]);

            svg.selectAll("circle.one")
                    .data(y.domain())
                    .enter()
                    .append("circle")
                    .attr("class", "one")
                    .attr("stroke-width", 2.5)
                    .style('stroke', "#ff964f")
                    .style('fill', "none")
                    .attr("r", 17)
                    .attr("cx", 60)
                    .attr("cy", 95)
                    .each(pulse);

            function pulse() {
            var circle = svg.select("circle.one");
            circle = circle.transition()
                    .attr("stroke-width", 2.5)
                    .attr("r", 17)
                    .transition()
                    .duration(3000)
                    .attr('stroke-width', 0.0)
                    .attr("r", 47)
                    .ease(d3.easeSin);        
                    }
                        
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

            var height = 150;   
            var y = d3.scaleOrdinal().domain(d3.range(1)).range([0, height]);

            svg.selectAll("circle.one")
                    .data(y.domain())
                    .enter()
                    .append("circle")
                    .attr("class", "one")
                    .attr("stroke-width", 2.5)
                    .style('stroke', "#ff6181")
                    .style('fill', "none")
                    .attr("r", 17)
                    .attr("cx", 60)
                    .attr("cy", 95)
                    .each(pulse);

            function pulse() {
            var circle = svg.select("circle.one");
            circle = circle.transition()
                    .attr("stroke-width", 2.5)
                    .attr("r", 17)
                    .transition()
                    .duration(3000)
                    .attr('stroke-width', 0.0)
                    .attr("r", 47)
                    .ease(d3.easeSin);        
                    }
            
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

            var height = 150;   
            var y = d3.scaleOrdinal().domain(d3.range(1)).range([0, height]);

            svg.selectAll("circle.one")
                    .data(y.domain())
                    .enter()
                    .append("circle")
                    .attr("class", "one")
                    .attr("stroke-width", 2.5)
                    .style('stroke', "#be688b")
                    .style('fill', "none")
                    .attr("r", 17)
                    .attr("cx", 60)
                    .attr("cy", 95)
                    .each(pulse);

            function pulse() {
            var circle = svg.select("circle.one");
            circle = circle.transition()
                    .attr("stroke-width", 2.5)
                    .attr("r", 17)
                    .transition()
                    .duration(3000)
                    .attr('stroke-width', 0.0)
                    .attr("r", 47)
                    .ease(d3.easeSin);        
                    }
            
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

            var height = 150;   
            var y = d3.scaleOrdinal().domain(d3.range(1)).range([0, height]);

            svg.selectAll("circle.one")
                    .data(y.domain())
                    .enter()
                    .append("circle")
                    .attr("class", "one")
                    .attr("stroke-width", 2.5)
                    .style('stroke', "#007FFF")
                    .style('fill', "none")
                    .attr("r", 17)
                    .attr("cx", 60)
                    .attr("cy", 95)
                    .each(pulse);

            function pulse() {
            var circle = svg.select("circle.one");
            circle = circle.transition()
                    .attr("stroke-width", 2.5)
                    .attr("r", 17)
                    .transition()
                    .duration(3000)
                    .attr('stroke-width', 0.0)
                    .attr("r", 47)
                    .ease(d3.easeSin);        
                    }
            
            }
</script>
</html>