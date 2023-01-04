<?php 
include('common.php');
include('dvmCombinedData.php');
include('userfixedSettings.php');
header('Content-type: text/html; charset=utf-8');
?>

<div class="updatedtime2">
<?php if(file_exists($livedata)&&time() - filemtime($livedata)>300) echo $offline. '<offline> Offline </offline>'; else echo $online." ".$weather["time"];?>
</div>
 
<?php
if ($barom["units"]==="kPa") { 
$barom["now"]=$barom["now"]*0.1; 
$barom["trend_code"]=$barom["trend_code"]*0.1; 
$barom["min"]=$barom["min"]*0.1; 
$barom["max"]=$barom["max"]*0.1;
}
?>
 
<div class='barometermax'>
<?php echo '<div class=barometerorange><valuetext>Max ('.$barom["maxtime"].')<br><maxred>',$barom["max"],'</maxred>&nbsp;',$barom["units"],' </valuetext></div>';?></div>
<div class='barometermin'>
<?php echo '<div class=barometerblue><valuetext>Min ('.$barom["mintime"].')<br><minblue>',$barom["min"],'</minblue>&nbsp;',$barom["units"],' </valuetext></div>';?></div>
</div>

<div class="barometertrend10">
<?php  echo "<valuetext>&nbsp;&nbsp;Trend";
if ($barom["trend_code"] > 20  && $barom["trend_code"] < 100) { echo '<rising><rise><maxred> '.$risingsymbol.' </rise><maxred><br>'; echo $barom["trend_desc"], '<maxred></rising> '; 
} else if ($barom["trend_code"] < 0) { echo '<falling><fall><minblue> '.$fallingsymbol.'</fall><minblue><br>'; echo $barom["trend_desc"], '</minblue></falling>';
} else if ($barom["trend_code"] > 0 && $barom["trend_code"] < 100) { echo '<rising><rise></maxred> '.$risingsymbol.'</rise><maxred><br>'; echo $barom["trend_desc"], '</maxred></rising> '; 
} else echo '<ogreen>'.$steadysymbol.'<br>Steady</ogreen></valuetext>';?></div>

<div class="barometerconverter">
<?php echo "<div class=barometerconvertercircleblue>";
if ($barom["units"]=='mb' OR $barom["units"]=="hPa"){echo number_format($barom["now"]*0.029529983071445,2),"<smallrainunit>&nbsp;inHg</smallrainunit>";
} else if ($barom["units"]=="kPa") { echo number_format($barom["now"]*0.29529983071445,2),"<smallrainunit>&nbsp;inHg</smallrainunit>";
} else if ($barom["units"]=='inHg') { echo round($barom["now"]*33.863886666667,1),"<smallrainunit>&nbsp;hPa</smallrainunit>";}
?>
</div></div>

<html>
<script src="js/d3.v3.min.js"></script>
<script src="js/iopctrl.js"></script>

<?php 

if ($theme == "dark") {

echo
    
    '<style>
    
    	.barometer {
    		position: relative; 
    		margin-top: -12.50px; 
    		margin-left: -2.75px;
    		z-index: auto;
    	}
    	
    	.moduletitlebaro {
			margin-top: -18px; 
			color: silver;
  			font-size: .8em;
  			float: none;
  			z-index: auto;
		}
		
		.yearpopupbaro {
  			font-size: .7em;
  			margin-left: -135px;
  			margin-top: 2px;
		}
		.weather-itembaro {
  			width: 32.84%;
  			height: 195px;
  			border: 0;
  			border-bottom: 18px solid rgba(97, 106, 114, .1);
  			-webkit-box-shadow: inset 0 20px rgba(97, 106, 114, .1);
  			box-shadow: inset 0 20px rgba(97, 106, 114, .1);
  			-webkit-box-sizing: border-box;
  			box-sizing: border-box;
  			font-size: 1em;
  			padding: 0;
  			float: none;
    		width: 315px;
    		margin: 2.5px auto 0;
    		overflow: hidden;
		}
		   	
        .unselectable {
            -moz-user-select: -moz-none;
            -khtml-user-select: none;
            -webkit-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        .gauge .domain {
            stroke-width: 0px;
            stroke: rgba(59, 60, 63, 1);
        }
        
        .gauge .tick line {
            stroke: rgba(255,99,71,1);
            stroke-width: 1px;
            stroke-linecap: round;
        }
        
        .gauge line {
            stroke: rgba(59, 60, 63, 1);
            stroke-width: 0.75px;
            stroke-linecap: round; 
        }
                
        .gauge .arc, .gauge .cursor {
        	stroke: rgba(59, 60, 63, 0);
        	stroke-width: 2px;
        	fill: rgba(59, 60, 63, 0);
        }

        .gauge .major {
            fill: rgba(147, 147, 147, 1);
            font-size: 8px;
            font-family: arial;
            font-weight: normal;
            letter-spacing: .015rem;
        }
        
        .gauge .indicator {
            stroke: rgba(255,0,0,1);
            fill: #000;
            stroke-width: 1px;
        }
                
         .gauge circle {
        	stroke: rgba(59, 60, 63, 1);
        	fill: rgba(59, 60, 63, 1);                
		}				  
       
	</style>';
	
	} else {
	
echo
	
    '<style>
    
        .barometer {
    		position: relative; 
    		margin-top: -14px; 
    		margin-left: -2.75px;
    		z-index: auto;
    	}
    	
    	.moduletitlebaro {
			margin-top: -18px; 
			color: black;
  			font-size: .8em;
  			float: none;
  			z-index: auto;
		}
		
		.yearpopupbaro {
  			font-size: .7em;
  			margin-left: -135px;
  			margin-top: 3px;
  			color: black;
		}
		
		.weather-itembaro {
  			height: 200px;
  			width: 33.3333%;  			
  			border: 0;
  			border-bottom: 18px solid #f6f8fc;
  			-webkit-box-shadow: inset 0 20px #f6f8fc;
  			box-shadow: inset 0 20px #f6f8fc;
  			-webkit-box-sizing: border-box;
  			box-sizing: border-box;
  			font-size: 1em;
  			padding: 0;
  			float: none;
  			width: 315px;
    		margin: 4px auto 0;
    		overflow: hidden;
		}
    
        .unselectable {
            -moz-user-select: -moz-none;
            -khtml-user-select: none;
            -webkit-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        .gauge .domain {
            stroke-width: 0px;
            stroke: rgba(59, 60, 63, 1);
        }
        
        .gauge .tick line {
            stroke: rgba(255,99,71,1);
            stroke-width: 1px;
            stroke-linecap: round;
        }
        
        .gauge line {
            stroke: rgba(230, 232, 239, 1);
            stroke-width: 0.75px;
            stroke-linecap: round; 
        }

        .gauge .arc, .gauge .cursor {
        	stroke: rgba(59, 60, 63, 0);
        	stroke-width: 2px;
        	fill: rgba(59, 60, 63, 0);
        }

        .gauge .major {
            fill: rgba(147, 147, 147, 1);
            font-size: 8px;
            font-family: arial;
            font-weight: normal;
            letter-spacing: .015rem;
        }
        
        .gauge .indicator {
            stroke: rgba(255,0,0,1);
            fill: #000;
            stroke-width: 1px;
        }
        
        .gauge circle {
        	stroke: rgba(230, 232, 239, 1);
        	fill: rgba(230, 232, 239, 1);                
		}
		       
	</style>';
	
}
?>
<div class="barometer"></div>
<div id="svg"></div>

   
    <script>
    
    var currentP = "<?php echo $barom["now"];?>";
    currentP = currentP || 0;
    
    var currentMax = "<?php echo $barom["max"];?>";
    currentMax = currentMax || 0;
    
    var currentMin = "<?php echo $barom["min"];?>";
    currentMin = currentMin || 0;
    
    var units = "<?php echo $barom["units"];?>";
    
    var theme = "<?php echo $theme;?>";
    
    	if (units == "hPa") {
                   
           var svg = d3.select(".barometer")
                .append("svg")
                //.style("background", "#292E35")
                .attr("width", 140)
                .attr("height", 140);
                
             if (theme == "dark") {
             
             svg.append("text") // barometer now pressure text output
             	.attr("x", 70)
            	.attr("y", 127)
            	.style("fill", "silver")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "normal")
   				.text(currentP + " " + units);
   				
   			} else {
   			
   			svg.append("text") // barometer now pressure text output
             	.attr("x", 70)
            	.attr("y", 127)
            	.style("fill", "#2d3a4b")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "normal")
   				.text(currentP + " " + units);
   			}
         
                                                                      
        var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {
                
    				g.append("line")
            		 .attr("y1", - width - 3) // needle length
            		 .attr("y2", width - 42) // needle tail length
            		 .style("stroke", "red")
            		 .style("stroke-linecap", "round")
            		 .style("stroke-width", 1);
            		 

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
                .attr('transform', 'translate(-32.5, -32.5)')
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
                .attr('transform', 'translate(-32.5, -32.5)')
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
                .attr('transform', 'translate(-32.5, -32.5)')
                .call(gauge);


        gauge.value(currentMax);
        
        } else if (units == "mb") {
        
         var svg = d3.select(".barometer")
                .append("svg")
                //.style("background", "#292E35")
                .attr("width", 140)
                .attr("height", 140);
                
             if (theme == "dark") {
             
             svg.append("text") // barometer now pressure text output
             	.attr("x", 70)
            	.attr("y", 127)
            	.style("fill", "silver")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "normal")
   				.text(currentP + " " + units);
   				
   			} else {
   			
   			svg.append("text") // barometer now pressure text output
             	.attr("x", 70)
            	.attr("y", 127)
            	.style("fill", "#2d3a4b")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "normal")
   				.text(currentP + " " + units);
   			}
         
                                                                      
        var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {
                
    				g.append("line")
            		 .attr("y1", - width - 3) // needle length
            		 .attr("y2", width - 42) // needle tail length
            		 .style("stroke", "red")
            		 .style("stroke-linecap", "round")
            		 .style("stroke-width", 1);
            		 

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
                .attr('transform', 'translate(-32.5, -32.5)')
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
                .attr('transform', 'translate(-32.5, -32.5)')
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
                .attr('transform', 'translate(-32.5, -32.5)')
                .call(gauge);


        gauge.value(currentMax);
        
        } else if (units == "inHg") {
        
         var svg = d3.select(".barometer")
                .append("svg")
                .attr("width", 140)
                .attr("height", 140);
           
            if (theme == "dark") {
             
             svg.append("text") // barometer now pressure text output
             	.attr("x", 70)
            	.attr("y", 127)
            	.style("fill", "silver")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "normal")
   				.text(currentP + " " + units);
   				
   			} else {
   			
   			svg.append("text") // barometer now pressure text output
             	.attr("x", 70)
            	.attr("y", 127)
            	.style("fill", "#2d3a4b")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "normal")
   				.text(currentP + " " + units);
   			}     

        var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {
    				g.append("line")
            		 .attr("y1", - width - 3) // needle length
            		 .attr("y2", width - 42) // needle tail length
            		 .style("stroke", "red")
            		 .style("stroke-linecap", "round")
            		 .style("stroke-width", 1);
            		 
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
                .attr('transform', 'translate(-32.5, -32.5)')
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
                .attr('transform', 'translate(-32.5, -32.5)')
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
                .attr('transform', 'translate(-32.5, -32.5)')
                .call(gauge);

        gauge.value(currentMax);
                   
      } else {
      
       var svg = d3.select(".barometer")
                .append("svg")
                .attr("width", 140)
                .attr("height", 140);
                
              if (theme == "dark") {
             
             svg.append("text") // barometer now pressure text output
             	.attr("x", 70)
            	.attr("y", 127)
            	.style("fill", "silver")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "normal")
   				.text(currentP + " " + units);
   				
   			} else {
   			
   			svg.append("text") // barometer now pressure text output
             	.attr("x", 70)
            	.attr("y", 127)
            	.style("fill", "#2d3a4b")
            	.style("font-family", "Helvetica")
            	.style("font-size", "11px")
            	.style("text-anchor", "middle")
            	.style("font-weight", "normal")
   				.text(currentP + " " + units);
   			}
                                                                      
        var gauge = iopctrl.arcslider()
                .radius(52.5)
                .events(false)
                .transitionDuration(0) // needle speed, a higher value makes it slower
                .indicator(function(g, width) {
    				g.append("line")
            		 .attr("y1", - width - 3) // needle length
            		 .attr("y2", width - 42) // needle tail length
            		 .style("stroke", "red")
            		 .style("stroke-linecap", "round")
            		 .style("stroke-width", 1);

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
                        .domain([95, 105]) // min max text scale inHg
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));                                                   
                                                                    
        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(-32.5, -32.5)')
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
                        .domain([95, 105]) // min max text scale inHg
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));
                                                
        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(-32.5, -32.5)')
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
                        .domain([95, 105]) // min max text scale inHg
                        .range([- 3 * Math.PI / 4, 3 * Math.PI / 4]));
                                                
        svg.append("g")
                .attr("class", "gauge")
                .attr('transform', 'translate(-32.5, -32.5)')
                .call(gauge);


        gauge.value(currentMax);
      
      }
      
</script>
</html>
