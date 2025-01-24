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
if ($magnitude < 4.0){$category = "Minor";$magnitudeColor="#2e8b57";}
else if ($magnitude < 5.0){$category = "Light";$magnitudeColor="#ef9f70";}
else if ($magnitude < 6.0){$category = "Moderate";$magnitudeColor="#ff964f";}
else if ($magnitude < 7.0){$category = "Strong";$magnitudeColor="#ff6181";}
else if ($magnitude < 8.0){$category = "Great";$magnitudeColor="#be688b";}
else if ($magnitude > 8.0){$category = "Major";$magnitudeColor="#007FFF";}
$depthraw       = $parsed_json['features'][0]['properties']['depth'];
$depth          = round($depthraw, 1);
$time           = $parsed_json['features'][0]['properties']['time'];
$lati           = $parsed_json['features'][0]['properties']['lat'];
$longi          = $parsed_json['features'][0]['properties']['lon'];
$eventime       = date("H:i:s", strtotime($time) );
$eqdist         = round(distance($lat, $lon, $lati, $longi), 1) ;

$eqdist; if ($wind["units"] == 'mph') {$eqdist = round(distance($lat, $lon, $lati, $longi) * 0.621371, 1) ."mi";
} else {$eqdist = round(distance($lat, $lon, $lati, $longi), 1)."km";}
$eqdista; if ($wind["units"] == 'mph') {$eqdista = round(distance($lat, $lon, $lati, $longi), 1) ."<smallrainunit>&nbsp;km";
} else {$eqdista = round(distance($lat, $lon, $lati, $longi) * 0.621371, 1)."<smallrainunit>&nbsp;miles";} 
?>

<!DOCTYPE html>
<style>
table.earthquake {
  background-color: transparent;
  width: 90%;
  text-align: center;
  border-spacing:0.35em;
}
table.earthquake td {
  border: 1px solid var(--col-13);border-radius:2px;
  padding: 1px 1px;
}
table.earthquake tbody td {
  font-size: 10px;
}
</style>
<html lang="en">

<div class="chartforecast">
<span class="yearpopup"><a alt="Earthquakes Worldwide" title="Earthquakes Worldwide" href="dvmEarthquakePopup.php" data-lity><?php echo $chartinfo;?> Worldwide Earthquakes</a></span>
<span class="yearpopup"><a alt="Earthquake Map" title="Earthquakes Map" href="dvmEarthquakeMapPopup.php" data-lity><?php echo $chartinfo;?> World Earthquake Map</a></span>
</div>
<span class='moduletitle'><?php echo $lang['earthquakeModule']; ?></valuetitleunit></span>

<div class= "updatedtime1"><span><?php if(file_exists('jsondata/eq.txt')&&time() - filemtime('jsondata/eq.txt')>3600) echo $offline. '<offline> Offline </offline>';else echo $online," ",date($timeFormat, filemtime('jsondata/eq.txt'));?></span></div>


<div class="earthquake-title" style="font-size:10px;"><?php echo $eqtitle;?></div>
<div class="earthquake-module" style="display:grid;grid-template-columns: auto auto;">
<div class="quakes" style="top:-25px;margin-left:-10px;"></div>
<div class="earthquake-table" style="margin-top:17px;margin-left:-30px;">
<table class="earthquake">
<tbody>
<tr>
<td style="border: transparent;">Time</td>
<td style="border: transparent;">Depth</td>
</tr>
<tr>
<td style="border-left: 5px solid <?php echo $magnitudeColor;?>;"><?php echo $eventime;?></td>
<td style="border-left: 5px solid <?php echo $magnitudeColor;?>;"><?php echo $depth;?>km</td>
</tr>
<tr>
<td style="border: transparent;">Epicenter</td>
<td style="border: transparent;">Category</td>
</tr>
<tr>
<td style="border-left: 5px solid <?php echo $magnitudeColor;?>;"><?php echo $eqdist;?></td>
<td style="border-left: 5px solid <?php echo $magnitudeColor;?>;"><?php echo $category;?></td>
</tr>
</tbody>
</table>
</div>
</div>
</html>

<script src="js/d3.7.9.0.min.js"></script>

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

    var Category = "<?php echo $category;?>";


                   
           var svg = d3.select(".quakes")
                .append("svg")
                //.style("background", "#292E35")
                .attr("width", 130)
                .attr("height", 150);
                     
           
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
                     .attr('stroke', '#ef9f70')
                     .attr('fill', 'none')
                     .attr('stroke-width', 2.5);
                     
            svg.append("circle")
                     .attr("cx", 60) // center circle
                     .attr("cy", 95)
                     .attr("r", 27)
                     .attr('stroke', '#ef9f70')
                     .attr('fill', 'none')
                     .attr('stroke-width', 2);
                
            svg.append("circle")
                     .attr("cx", 60) // center circle
                     .attr("cy", 95)
                     .attr("r", 37)
                     .attr('stroke', '#ef9f70')
                     .attr('fill', 'none')
                     .attr('stroke-width', 1);
                     
            svg.append("circle")
                     .attr("cx", 60) // center circle
                     .attr("cy", 95)
                     .attr("r", 47)
                     .attr('stroke', '#ef9f70')
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
                    .style('stroke', "#ef9f70")
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
