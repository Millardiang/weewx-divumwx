<?php
include('dvmCombinedData.php');
date_default_timezone_set($TZ);

if ($theme === "dark") {
    echo '<style>

.stationclock {
  position: relative;
  margin-top: 23.5px; 
  margin-left: 1.75px;
}
.digitalclock {
  position: relative;
  font-family: "Helvetica";
  font-size: 15px;
  color: white;
  margin-top: -43px; 
  margin-left: 49px;
}
.analog-hours {
    stroke-width: 3.5;
    stroke: rgba(170,170,170,1);
    stroke-linecap: round;
}
.analog-minutes {
    stroke-width: 2;
    stroke: rgba(170,170,170,1);
    stroke-linecap: round;
}
.analog-seconds {
    stroke-width: 1;
    stroke: #ff7c39;
    stroke-linecap: round;
}
    </style>';

} else {

    echo '<style>
 
.stationclock {
  position: relative;
  margin-top: 23.5px; 
  margin-left: 1.75px;
}
.digitalclock {
  position: relative;
  font-family: "Helvetica";
  font-size: 15px;
  color: #2d3a4b;
  margin-top: -43px; 
  margin-left: 49px;
}
.analog-hours {
    stroke-width: 3.5;
    stroke: rgba(170,170,170,1);
    stroke-linecap: round;
}
.analog-minutes {
    stroke-width: 2;
    stroke: rgba(170,170,170,1);
    stroke-linecap: round;
}
.analog-seconds {
    stroke-width: 1;
    stroke: #ff7c39;
    stroke-linecap: round;
}
    </style>';
}
?>
      
<script src="js/d3.4.2.2.min.js"></script>

<div class="stationclock"></div>
<div class="digitalclock"></div>

<script>

var theme = "<?php echo $theme;?>";

if (theme === 'dark') {

    var ringColor = "rgba(230, 232, 239, 0.2)";
    var dateColor = "silver";

} else {

    var ringColor = "#e7e9ef";
    var dateColor = "#2d3a4b";

}
</script>

<script>

var date = "<?php echo date('l d M Y');?>";

var svg = d3.select(".stationclock")
    .append("svg")
    .attr("width", 226)
    .attr("height", 55);

 svg.append("circle")
    .style("stroke", ringColor)
    .style("stroke-width", 2)
    .style("fill", "none")    
    .attr("r", 23)
    .attr("cx", 27.5)
    .attr("cy", 27.5);

var analogContent = svg
    .append('g')
    .attr('class','analog-content')
    .attr("transform", "translate(27.5,27.5)");

var hourScale = d3.scaleLinear()
    .range([0,360])
    .domain([0,12]);

var minuteScale = d3.scaleLinear()
    .range([0,360])
    .domain([0,60]);

var secondScale = d3.scaleLinear()
    .range([0,360])
    .domain([0,60]);

var handData = [

    {'label':'hours', 'scale':hourScale, 'length': -13},
    {'label':'minutes', 'scale':minuteScale, 'length': -18},
    {'label':'seconds', 'scale':secondScale, 'length': -19}
]

var analogHands = analogContent.selectAll('.hands')
    analogHands.data(handData)
    .enter()
    .append('g')
    .attr('class',function(d){ return'hands analog-' + d.label;})
    .append('line')
    .attr('x1', 0)
    .attr('y1', 0)
    .attr('x2', 0)
    .attr('y2',function(d){return d.length})

function update() {
var time = new Date();
handData[0].value = (time.getHours() % 12) + (time.getMinutes() + time.getSeconds() / 60) / 60;
handData[1].value = (time.getMinutes() % 60) + time.getSeconds() / 60;
handData[2].value = time.getSeconds() + time.getMilliseconds() / 1000;
d3.selectAll('.hands').data(handData) 
.attr('transform',function(d){return 'rotate('+ d.scale(d.value) +')';});    
}
setInterval(update, 1);

svg.append("circle")
    .style("fill", "#ff7c39")
    .attr("r", 2.5)    
    .attr("cx", 27.5)
    .attr("cy", 27.5);

svg.append("text")
    .attr("x", 138)
    .attr("y", 15)
    .style("fill", dateColor)
    .style("font-family", "Helvetica")
    .style("font-size", "12.25px")
    .style("text-anchor", "middle")
    .style("font-weight", "normal")
    .text(date);

if (theme === 'dark') {

svg.append("rect")
    .attr("x", 86)
    .attr("y", 22)
    .attr("rx", 1)
    .attr("height", 20.5)
    .attr("width", 103)
    .style("fill", "rgba(46,139,87,1)");

} else {

svg.append("rect")
    .attr("x", 86)
    .attr("y", 22)
    .attr("rx", 1)
    .style("stroke", "#999999")
    .attr("height", 20.5)
    .attr("width", 103)
    .style("fill", "none");

svg.append("rect")
    .attr("x", 86)
    .attr("y", 22)
    .attr("rx", 1)
    .attr("height", 20.5)
    .attr("width", 103)
    .style("fill", "rgba(230,232,239,1)");
}

function clock() {

var time = new Date(),
hours = time.getHours(),
minutes = time.getMinutes(),
seconds = time.getSeconds();

document.querySelectorAll('.digitalclock')[0].innerHTML = skynet(hours) + ":" + skynet(minutes) + ":" + skynet(seconds);
  
  function skynet(standIn) {
    if (standIn < 10) {
      standIn = '0' + standIn
    }
    return standIn;
  }
}
setInterval(clock, 1000);
    
</script>