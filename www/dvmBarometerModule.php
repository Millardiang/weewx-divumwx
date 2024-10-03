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

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Barometer module</title>
</head>

<div class="chartforecast">
<span class="yearpopup"><a alt="barometer charts" title="barometer charts" href="dvmBarometerRecords.php" data-lity><?php echo $menucharticonpage;?> Air Density | Barometer Records and Charts</a></span>   
</div>    
<span class='moduletitle'><?php echo $lang['barometerModule'], " (<valuetitleunit>", $barom["units"];?></valuetitleunit>)</span>
<div class="updatedtime1">
<?php if(file_exists($livedata)&&time() - filemtime($livedata)>300) echo $offline. '<offline> Offline </offline>'; else echo $online." ".$divum["time"];?>
</div>
 
<?php
if ($barom["units"]==="kPa") { 
$barom["now"]=$barom["now"]*0.1; 
$barom["trend_code"]=$barom["trend_code"]*0.1; 
$barom["min"]=$barom["min"]*0.1; 
$barom["max"]=$barom["max"]*0.1;}
?>

<div class="barometerconverter">
<?php echo "<div class=barometerconvertercircleblue style='$convertStyle $colorBarometerCurrent;'>";
if ($barom["units"]=='mbar' OR $barom["units"]=="hPa"){echo number_format($barom["now"]*0.029529983071445,2),"<smallrainunit> inHg</smallrainunit>";
} else if ($barom["units"]=="kPa") { echo number_format($barom["now"]*0.29529983071445,2),"<smallrainunit> inHg</smallrainunit>";
} else if ($barom["units"]=='inHg') { echo round($barom["now"]*33.863886666667,1),"<smallrainunit> hPa</smallrainunit>";}?>
</div></div>

<script src="js/d3.v3.min.js"></script>
<script src="js/iopctrl.js"></script>

<div class="barometer"></div>

<script>

var baseTextColor = "var(--col-6)";

var trend_code = "<?php echo $barom["trend_code"];?>";
if(trend_code == 0) {
    var trend_color = "#90b12a";
} else if (trend_code > 0 ) {
    trend_color = "#3b9cac";
} else if(trend_code < 0) {
    trend_color = "#ff7c39";
}

var dayMaxColor = "<?php echo $colorBarometerDayMax;?>";
var dayMinColor = "<?php echo $colorBarometerDayMin;?>";
var currentPColor = "<?php echo $colorBarometerCurrent;?>";

var altitude = "<?php echo $elevation;?>"; 

var air_density = "<?php echo $air_density["now"];?>";

var trend_desc = "<?php echo $barom["trend_desc"];?>";
   
var currentP = "<?php echo $barom["now"];?>";
currentP = currentP || 0;

var maxT = "<?php echo $barom["maxtime"];?>";
maxT = maxT || 0;

var maxP = "<?php echo $barom["max"];?>";
maxP = maxP || 0;

var minT = "<?php echo $barom["mintime"];?>";
minT = minT || 0; 

var minP = "<?php echo $barom["min"];?>";
minP = minP || 0;
    
var currentMax = "<?php echo $barom["max"];?>";
currentMax = currentMax || 0;
    
var currentMin = "<?php echo $barom["min"];?>";
currentMin = currentMin || 0;
    
var units = "<?php echo $barom["units"];?>";
    
var svg = d3.select(".barometer")
                .append("svg")
                //.style("background", "#292E35")
                .attr("width", 310)
                .attr("height", 150);

            svg.append("text") // station altitude
                .attr("x", 273)
                .attr("y", 70)
                .style("fill", baseTextColor)
                .style("font-family", "Helvetica")
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text("Air Density");

            var data = [air_density + "-" + " kg/m³"];

            var text = svg.selectAll(null)
                .data(data)
                .enter() 
                .append("text")
                .attr("x", 273)
                .attr("y", function(d, i) { return 79 + i * 79; })

                .style("fill", "#007FFF")
                .style("font-family", "Helvetica") 
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text(function(d) { return d.split("-")[0]; })

                .append("tspan")
                .style("fill", baseTextColor)
                .text(function(d) { return d.split("-")[1]; });
    
        if (units === "hPa") {
                   
            svg.append("text") // station altitude
                .attr("x", 35)
                .attr("y", 70)
                .style("fill", baseTextColor)
                .style("font-family", "Helvetica")
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text("Station Altitude");

            var data = [d3.format(".2f")(altitude) + "-" + " m"];

            var text = svg.selectAll(null)
                .data(data)
                .enter() 
                .append("text")
                .attr("x", 35)
                .attr("y", function(d, i) { return 79 + i * 79; })

                .style("fill", "#007FFF")
                .style("font-family", "Helvetica") 
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text(function(d) { return d.split("-")[0]; })

                .append("tspan")
                .style("fill", baseTextColor)
                .text(function(d) { return d.split("-")[1]; });
   
            svg.append("text") // maxtime pressure text output
                .attr("x", 37)
                .attr("y", 10)
                .style("fill", baseTextColor)
                .style("font-family", "Helvetica")
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text("Max " + "("+ maxT +")");

            var data = [d3.format(".1f")(maxP) + "-" + " " + units]; // max pressure text output

            var text = svg.selectAll(null)
                .data(data)
                .enter() 
                .append("text")
                .attr("x", 37)
                .attr("y", function(d, i) { return 19 + i * 19; })

                .style("fill", dayMaxColor)
                .style("font-family", "Helvetica") 
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text(function(d) { return d.split("-")[0]; })

                .append("tspan")
                .style("fill", baseTextColor)
                .style("font-weight", "normal")
                .text(function(d) { return d.split("-")[1]; });

            svg.append("text") // mintime pressure text output
                .attr("x", 273)
                .attr("y", 133)
                .style("fill", baseTextColor)
                .style("font-family", "Helvetica")
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text("Min " + "("+ minT +")");

            var data = [d3.format(".1f")(minP) + "-" + " " + units]; // min pressure text output

            var text = svg.selectAll(null)
                .data(data)
                .enter() 
                .append("text")
                .attr("x", 272)
                .attr("y", function(d, i) { return 143 + i * 143; })

                .style("fill", dayMinColor)
                .style("font-family", "Helvetica") 
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text(function(d) { return d.split("-")[0]; })

                .append("tspan")
                .style("fill", baseTextColor)
                .style("font-weight", "normal")
                .text(function(d) { return d.split("-")[1]; });
             
            svg.append("text") // Trend text output
                .attr("x", 39)
                .attr("y", 134)
                .style("fill", baseTextColor)
                .style("font-family", "Helvetica")
                .style("font-size", "9.5px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text("Trend");

            svg.append("text") // Trend text output
                .attr("x", 40.5)
                .attr("y", 143)
                .style("fill", trend_color)
                .style("font-family", "Helvetica")
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text(trend_desc);

        if (trend_code > 0) {
        
         svg.append('polyline') // trend rising
                .attr('points', "54 133 56.5 130 59 132 62 128")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', trend_color);
              
            svg.append('polyline') // trend rising
                .attr('points', "59.5 128.25 61 128 62 128 63 130")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', trend_color);         
             
         } else if (trend_code < 0) {
              
         svg.append('polyline') // trend falling
                .attr('points', "54.15 128 56.5 131 59 129.5 61.75 132.75")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', trend_color);
                
            svg.append('polyline') // trend falling
                .attr('points', "59 133 62 133.25 62 133.25 63 131")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', trend_color);
                
        } else if (trend_code == 0) {
        
            svg.append('polyline') // steady trend
                .attr('points', "54 128 57 131 54 133.5")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', trend_color);        
        }
        
             svg.append("text") // barometer now pressure text output
                .attr("x", 155)
                .attr("y", 132)
                .style("fill", currentPColor)
                .style("font-family", "Helvetica")
                .style("font-size", "11px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text(currentP + " " + units);
                                                                                              
        var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {

                g.append('polyline') // needle
                    .attr('points', "1.5 11 0 -56 -1.5 11 0 11 1.5 11 -1.5 0")
                    .style('fill', 'red')
                    .style('stroke', 'red')
                    .style("stroke-width", 0.5)
                    .style("stroke-linecap", "round");
                     
                g.append("circle")
                    .attr("cx", 0) // center circle
                    .attr("cy", 0)
                    .attr("r", 5);
                                                                                                 
            });
                                                                    
        gauge.axis().orient("out").tickFormat(d3.format("d"))
                .normalize(false)
                .ticks(12)
                .tickSubdivide(10)
                .tickSize(7, 7, 10)
                .tickPadding(3)
                .scale(d3.scale.linear()
                        .domain([940, 1060]) // min max text scale hPa
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));
                                                                                                     
        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(52.5, -29)')
                .call(gauge);
                                
        gauge.value(currentP);
        
         var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {

                    g.append("line")
                     .attr("y1", - width - 7) // needle length
                     .attr("y2", - width + 0) // needle tail length
                     .style("stroke", "rgba(59, 156, 172, 1)")
                     .style("stroke-linecap", "round")
                     .style("stroke-width", 2);
                                                     
            });
                                              
        gauge.axis().orient("in").tickFormat(d3.format("d"))
                .normalize(true)
                .ticks(0)
                .tickSubdivide(0)
                .tickSize(7, 7, 10)
                .tickPadding(3)
                .scale(d3.scale.linear()
                        .domain([940, 1060]) // min max text scale hPa
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));
                                                
        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(52.5, -29)')
                .call(gauge);

        gauge.value(currentMin);
        
          var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {

                    g.append("line")
                     .attr("y1", - width - 7) // needle length
                     .attr("y2", - width + 0) // needle tail length
                     .style("stroke", "rgba(255, 124, 57, 1)")
                     .style("stroke-linecap", "round")
                     .style("stroke-width", 2);                                      
                                                         
            });
                                              
        gauge.axis().orient("in").tickFormat(d3.format("d"))
                .normalize(true)
                .ticks(0)
                .tickSubdivide(0)
                .tickSize(7, 7, 10)
                .tickPadding(3)
                .scale(d3.scale.linear()
                        .domain([940, 1060]) // min max text scale hPa
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));
                                                
        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(52.5, -29)')
                .call(gauge);        

        gauge.value(currentMax);
        
        } else if (units === "mbar") {

            svg.append("text") // station altitude
                .attr("x", 35)
                .attr("y", 70)
                .style("fill", baseTextColor)
                .style("font-family", "Helvetica")
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text("Station Altitude");

            var data = [d3.format(".2f")(altitude) + "-" + " m"];

            var text = svg.selectAll(null)
                .data(data)
                .enter() 
                .append("text")
                .attr("x", 35)
                .attr("y", function(d, i) { return 79 + i * 79; })

                .style("fill", "#007FFF")
                .style("font-family", "Helvetica") 
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text(function(d) { return d.split("-")[0]; })

                .append("tspan")
                .style("fill", baseTextColor)
                .text(function(d) { return d.split("-")[1]; });
                                    
             svg.append("text") // maxtime pressure text output
                .attr("x", 37)
                .attr("y", 10)
                .style("fill", baseTextColor)
                .style("font-family", "Helvetica")
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text("Max " + "("+ maxT +")");

            var data = [d3.format(".1f")(maxP) + "-" + " " + units]; // max pressure text output

            var text = svg.selectAll(null)
                .data(data)
                .enter() 
                .append("text")
                .attr("x", 37)
                .attr("y", function(d, i) { return 19 + i * 19; })

                .style("fill", dayMaxColor)
                .style("font-family", "Helvetica") 
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text(function(d) { return d.split("-")[0]; })

                .append("tspan")
                .style("fill", baseTextColor)
                .style("font-weight", "normal")
                .text(function(d) { return d.split("-")[1]; });

            svg.append("text") // mintime pressure text output
                .attr("x", 273)
                .attr("y", 133)
                .style("fill", baseTextColor)
                .style("font-family", "Helvetica")
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text("Min " + "("+ minT +")");

            var data = [d3.format(".1f")(minP) + "-" + " " + units]; // min pressure text output

            var text = svg.selectAll(null)
                .data(data)
                .enter() 
                .append("text")
                .attr("x", 272)
                .attr("y", function(d, i) { return 143 + i * 143; })

                .style("fill", dayMinColor)
                .style("font-family", "Helvetica") 
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text(function(d) { return d.split("-")[0]; })

                .append("tspan")
                .style("fill", baseTextColor)
                .style("font-weight", "normal")
                .text(function(d) { return d.split("-")[1]; });
             
            svg.append("text") // Trend text output
                .attr("x", 39)
                .attr("y", 134)
                .style("fill", baseTextColor)
                .style("font-family", "Helvetica")
                .style("font-size", "9.5px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text("Trend");

            svg.append("text") // Trend text output
                .attr("x", 40.5)
                .attr("y", 143)
                .style("fill", trend_color)
                .style("font-family", "Helvetica")
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text(trend_desc);

        if (trend_code > 0) {
        
         svg.append('polyline') // trend rising
                .attr('points', "54 133 56.5 130 59 132 62 128")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', trend_color);
              
            svg.append('polyline') // trend rising
                .attr('points', "59.5 128.25 61 128 62 128 63 130")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', trend_color);         
             
         } else if (trend_code < 0) {
              
         svg.append('polyline') // trend falling
                .attr('points', "54 128 56.5 131 59 129.5 61.5 133")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', trend_color);
                
            svg.append('polyline') // trend falling
                .attr('points', "59.5 133.5 61 133.5 62 133.5 63 131.5")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', trend_color);
                
        } else if (trend_code == 0) {
        
            svg.append('polyline') // steady trend
                .attr('points', "54 128 57 131 54 133.5")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', trend_color);        
        }
        
             svg.append("text") // barometer now pressure text output
                .attr("x", 155)
                .attr("y", 132)
                .style("fill", currentPColor)
                .style("font-family", "Helvetica")
                .style("font-size", "11px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text(currentP + " " + units);
                                                                                           
        var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {

                     g.append('polyline') // needle
                    .attr('points', "1.5 11 0 -56 -1.5 11 0 11 1.5 11 -1.5 0")
                    .style('fill', 'red')
                    .style('stroke', 'red')
                    .style("stroke-width", 0.5)
                    .style("stroke-linecap", "round");

                     g.append("circle")
                     .attr("cx", 0) // center circle
                     .attr("cy", 0)
                     .attr("r", 5);
                                                                                                 
            });
                                                                    
        gauge.axis().orient("out").tickFormat(d3.format("d"))
                .normalize(false)
                .ticks(12)
                .tickSubdivide(10)
                .tickSize(7, 7, 10)
                .tickPadding(3)
                .scale(d3.scale.linear()
                        .domain([940, 1060]) // min max text scale mbar
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));
                                                                                                     
        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(52.5, -29)')
                .call(gauge);
                                 
        gauge.value(currentP);
        
         var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {

                    g.append("line")
                     .attr("y1", - width - 7) // needle length
                     .attr("y2", - width + 0) // needle tail length
                     .style("stroke", "rgba(59, 156, 172, 1)")
                     .style("stroke-linecap", "round")
                     .style("stroke-width", 2);
                                                     
            });
                                              
        gauge.axis().orient("in").tickFormat(d3.format("d"))
                .normalize(true)
                .ticks(0)
                .tickSubdivide(0)
                .tickSize(7, 7, 10)
                .tickPadding(3)
                .scale(d3.scale.linear()
                        .domain([940, 1060]) // min max text scale mbar
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));
                                                
        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(52.5, -29)')
                .call(gauge);

        gauge.value(currentMin);
        
          var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {

                    g.append("line")
                     .attr("y1", - width - 7) // needle length
                     .attr("y2", - width + 0) // needle tail length
                     .style("stroke", "rgba(255, 124, 57, 1)")
                     .style("stroke-linecap", "round")
                     .style("stroke-width", 2);                                      
                                                         
            });
                                              
        gauge.axis().orient("in").tickFormat(d3.format("d"))
                .normalize(true)
                .ticks(0)
                .tickSubdivide(0)
                .tickSize(7, 7, 10)
                .tickPadding(3)
                .scale(d3.scale.linear()
                        .domain([940, 1060]) // min max text scale mbar
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));
                                                
        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(52.5, -29)')
                .call(gauge);

        gauge.value(currentMax);
        
        } else if (units === "inHg") {

            svg.append("text") // station altitude
                .attr("x", 35)
                .attr("y", 70)
                .style("fill", baseTextColor)
                .style("font-family", "Helvetica")
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text("Station Altitude");

            var data = [d3.format(".2f")(altitude*3.281) + "-" + " ft"];

            var text = svg.selectAll(null)
                .data(data)
                .enter() 
                .append("text")
                .attr("x", 35)
                .attr("y", function(d, i) { return 79 + i * 79; })

                .style("fill", "#007FFF")
                .style("font-family", "Helvetica") 
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text(function(d) { return d.split("-")[0]; })

                .append("tspan")
                .style("fill", baseTextColor)
                .text(function(d) { return d.split("-")[1]; });
                              
            svg.append("text") // maxtime pressure text output
                .attr("x", 37)
                .attr("y", 10)
                .style("fill", baseTextColor)
                .style("font-family", "Helvetica")
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text("Max " + "("+ maxT +")");

            var data = [d3.format(".1f")(maxP) + "-" + " " + units]; // max pressure text output

            var text = svg.selectAll(null)
                .data(data)
                .enter() 
                .append("text")
                .attr("x", 37)
                .attr("y", function(d, i) { return 19 + i * 19; })

                .style("fill", dayMaxColor)
                .style("font-family", "Helvetica") 
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text(function(d) { return d.split("-")[0]; })

                .append("tspan")
                .style("fill", baseTextColor)
                .style("font-weight", "normal")
                .text(function(d) { return d.split("-")[1]; });

            svg.append("text") // mintime pressure text output
                .attr("x", 273)
                .attr("y", 133)
                .style("fill", baseTextColor)
                .style("font-family", "Helvetica")
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text("Min " + "("+ minT +")");

            var data = [d3.format(".1f")(minP) + "-" + " " + units]; // min pressure text output

            var text = svg.selectAll(null)
                .data(data)
                .enter() 
                .append("text")
                .attr("x", 272)
                .attr("y", function(d, i) { return 143 + i * 143; })

                .style("fill", dayMinColor)
                .style("font-family", "Helvetica") 
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text(function(d) { return d.split("-")[0]; })

                .append("tspan")
                .style("fill", baseTextColor)
                .style("font-weight", "normal")
                .text(function(d) { return d.split("-")[1]; });
             
            svg.append("text") // Trend text output
                .attr("x", 39)
                .attr("y", 134)
                .style("fill", baseTextColor)
                .style("font-family", "Helvetica")
                .style("font-size", "9.5px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text("Trend");

            svg.append("text") // Trend text output
                .attr("x", 40.5)
                .attr("y", 143)
                .style("fill", trend_color)
                .style("font-family", "Helvetica")
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text(trend_desc);

        if (trend_code > 0) {
        
         svg.append('polyline') // trend rising
                .attr('points', "54 133 56.5 130 59 132 62 128")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', trend_color);
              
            svg.append('polyline') // trend rising
                .attr('points', "59.5 128.25 61 128 62 128 63 130")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', trend_color);         
             
         } else if (trend_code < 0) {
              
         svg.append('polyline') // trend falling
                .attr('points', "54 128 56.5 131 59 129.5 61.5 133")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', trend_color);
                
            svg.append('polyline') // trend falling
                .attr('points', "59.5 133.5 61 133.5 62 133.5 63 131.5")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', trend_color);
                
        } else if (trend_code == 0) {
        
            svg.append('polyline') // steady trend
                .attr('points', "54 128 57 131 54 133.5")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', trend_color);        
        }
        
             svg.append("text") // barometer now pressure text output
                .attr("x", 155)
                .attr("y", 132)
                .style("fill", currentPColor)
                .style("font-family", "Helvetica")
                .style("font-size", "11px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text(currentP + " " + units);     

        var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {

                     g.append('polyline') // needle
                    .attr('points', "1.5 11 0 -56 -1.5 11 0 11 1.5 11 -1.5 0")
                    .style('fill', 'red')
                    .style('stroke', 'red')
                    .style("stroke-width", 0.5)
                    .style("stroke-linecap", "round");

                     g.append("circle")
                     .attr("cx", 0) // center circle
                     .attr("cy", 0)
                     .attr("r", 5);
                     
            });
                   
        gauge.axis().orient("out")
                .normalize(false)
                .ticks(9)
                .tickSubdivide(10)
                .tickSize(7, 7, 10)
                .tickPadding(3)
                .scale(d3.scale.linear()
                        .domain([27.5, 31.5]) // min max text scale inHg
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));

        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(52.5, -29)')
                .call(gauge);

        gauge.value(currentP);
        
         var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {

                    g.append("line")
                     .attr("y1", - width - 7) // needle length
                     .attr("y2", - width + 0) // needle tail length
                     .style("stroke", "rgba(59, 156, 172, 1)")
                     .style("stroke-linecap", "round")
                     .style("stroke-width", 2);
                     
            });
                   
        gauge.axis().orient("in")
                .normalize(true)
                .ticks(0)
                .tickSubdivide(0)
                .tickSize(7, 7, 10)
                .tickPadding(3)
                .scale(d3.scale.linear()
                        .domain([27.5, 31.5]) // min max text scale inHg
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));

        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(52.5, -29)')
                .call(gauge);

        gauge.value(currentMin);
        
        var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {

                    g.append("line")
                     .attr("y1", - width - 7) // needle length
                     .attr("y2", - width + 0) // needle tail length
                     .style("stroke", "rgba(255, 124, 57, 1)")
                     .style("stroke-linecap", "round")
                     .style("stroke-width", 2);                  

            });
                   
        gauge.axis().orient("in")
                .normalize(true)
                .ticks(0)
                .tickSubdivide(0)
                .tickSize(7, 7, 10)
                .tickPadding(3)
                .scale(d3.scale.linear()
                        .domain([27.5, 31.5]) // min max text scale inHg
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));

        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(52.5, -29)')
                .call(gauge);

        gauge.value(currentMax);
                   
      } else {

            svg.append("text") // station altitude
                .attr("x", 35)
                .attr("y", 70)
                .style("fill", baseTextColor)
                .style("font-family", "Helvetica")
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text("Station Altitude");

            var data = [d3.format(".2f")(altitude) + "-" + " m"];

            var text = svg.selectAll(null)
                .data(data)
                .enter() 
                .append("text")
                .attr("x", 35)
                .attr("y", function(d, i) { return 79 + i * 79; })

                .style("fill", "#007FFF")
                .style("font-family", "Helvetica") 
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text(function(d) { return d.split("-")[0]; })

                .append("tspan")
                .style("fill", baseTextColor)
                .text(function(d) { return d.split("-")[1]; });
                            
             svg.append("text") // maxtime pressure text output
                .attr("x", 37)
                .attr("y", 10)
                .style("fill", baseTextColor)
                .style("font-family", "Helvetica")
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text("Max " + "("+ maxT +")");

            var data = [d3.format(".1f")(maxP) + "-" + " " + units]; // max pressure text output

            var text = svg.selectAll(null)
                .data(data)
                .enter() 
                .append("text")
                .attr("x", 37)
                .attr("y", function(d, i) { return 19 + i * 19; })

                .style("fill", dayMaxColor)
                .style("font-family", "Helvetica") 
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text(function(d) { return d.split("-")[0]; })

                .append("tspan")
                .style("fill", baseTextColor)
                .style("font-weight", "normal")
                .text(function(d) { return d.split("-")[1]; });

            svg.append("text") // mintime pressure text output
                .attr("x", 273)
                .attr("y", 133)
                .style("fill", baseTextColor)
                .style("font-family", "Helvetica")
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text("Min " + "("+ minT +")");

            var data = [d3.format(".1f")(minP) + "-" + " " + units]; // min pressure text output

            var text = svg.selectAll(null)
                .data(data)
                .enter() 
                .append("text")
                .attr("x", 272)
                .attr("y", function(d, i) { return 143 + i * 143; })

                .style("fill", dayMinColor)
                .style("font-family", "Helvetica") 
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text(function(d) { return d.split("-")[0]; })

                .append("tspan")
                .style("fill", baseTextColor)
                .style("font-weight", "normal")
                .text(function(d) { return d.split("-")[1]; });
             
            svg.append("text") // Trend text output
                .attr("x", 39)
                .attr("y", 134)
                .style("fill", baseTextColor)
                .style("font-family", "Helvetica")
                .style("font-size", "9.5px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text("Trend");

            svg.append("text") // Trend text output
                .attr("x", 40.5)
                .attr("y", 143)
                .style("fill", trend_color)
                .style("font-family", "Helvetica")
                .style("font-size", "9px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text(trend_desc);

        if (trend_code > 0) {
        
         svg.append('polyline') // trend rising
                .attr('points', "54 133 56.5 130 59 132 62 128")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', trend_color);
              
            svg.append('polyline') // trend rising
                .attr('points', "59.5 128.25 61 128 62 128 63 130")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', trend_color);         
             
         } else if (trend_code < 0) {
              
         svg.append('polyline') // trend falling
                .attr('points', "54 128 56.5 131 59 129.5 61.5 133")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', trend_color);
                
            svg.append('polyline') // trend falling
                .attr('points', "59.5 133.5 61 133.5 62 133.5 63 131.5")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', trend_color);
                
        } else if (trend_code == 0) {
        
            svg.append('polyline') // steady trend
                .attr('points', "54 128 57 131 54 133.5")
                .attr('stroke-width', "0.75px")
                .style("fill", "none")
                .style("stroke-linecap", "round")
                .attr('stroke', trend_color);        
        }
        
             svg.append("text") // barometer now pressure text output
                .attr("x", 155)
                .attr("y", 132)
                .style("fill", currentPColor)
                .style("font-family", "Helvetica")
                .style("font-size", "11px")
                .style("text-anchor", "middle")
                .style("font-weight", "normal")
                .text(currentP + " " + units);
                                                                      
        var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {

                    g.append('polyline') // needle
                    .attr('points', "1.5 11 0 -56 -1.5 11 0 11 1.5 11 -1.5 0")
                    .style('fill', 'red')
                    .style('stroke', 'red')
                    .style("stroke-width", 0.5)
                    .style("stroke-linecap", "round");

                     g.append("circle")
                     .attr("cx", 0) // center circle
                     .attr("cy", 0)
                     .attr("r", 5);
                                                                                                 
            });
                                                         
        gauge.axis().orient("out").tickFormat(d3.format("d"))
                .normalize(false)
                .ticks(12)
                .tickSubdivide(10)
                .tickSize(7, 7, 10)
                .tickPadding(3)
                .scale(d3.scale.linear()
                        .domain([95, 105]) // min max text scale kPa
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));                                                                                                
        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(52.5, -29)')
                .call(gauge);
                
        gauge.value(currentP);
        
         var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {

                    g.append("line")
                     .attr("y1", - width - 7) // needle length
                     .attr("y2", - width + 0) // needle tail length
                     .style("stroke", "rgba(59, 156, 172, 1)")
                     .style("stroke-linecap", "round")
                     .style("stroke-width", 2);
                                                     
            });
                                              
        gauge.axis().orient("in").tickFormat(d3.format("d"))
                .normalize(true)
                .ticks(0)
                .tickSubdivide(0)
                .tickSize(7, 7, 10)
                .tickPadding(3)
                .scale(d3.scale.linear()
                        .domain([95, 105]) // min max text scale kPa
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));
                                                
        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(52.5, -29)')
                .call(gauge);

        gauge.value(currentMin);
        
          var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {

                    g.append("line")
                     .attr("y1", - width - 7) // needle length
                     .attr("y2", - width + 0) // needle tail length
                     .style("stroke", "rgba(255, 124, 57, 1)")
                     .style("stroke-linecap", "round")
                     .style("stroke-width", 2);                                      
                                                         
            });
                                              
        gauge.axis().orient("in").tickFormat(d3.format("d"))
                .normalize(true)
                .ticks(0)
                .tickSubdivide(0)
                .tickSize(7, 7, 10)
                .tickPadding(3)
                .scale(d3.scale.linear()
                        .domain([95, 105]) // min max text scale kPa
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));
                                                
        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(52.5, -29)')
                .call(gauge);

        gauge.value(currentMax);
      
      }
      
</script>
</html>
