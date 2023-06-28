<?php
include('dvmCombinedData.php');
date_default_timezone_set($TZ);

if ($theme === "dark") {

    echo '<style>

.stationtime {
  position: relative;
  margin-top: 23.5px; 
  margin-left: 1.75px;
}
#digitalclock {
  position: relative;
  font-family: "Helvetica";
  font-size: 15px;
  color: white;
  margin-top: -43px; 
  margin-left: 50px;
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
 
.stationtime {
  position: relative;
  margin-top: 23.5px; 
  margin-left: 1.75px;
}
#digitalclock {
  position: relative;
  font-family: "Helvetica";
  font-size: 15px;
  color: white;
  margin-top: -43px; 
  margin-left: 50px;
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

<!doctype html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Weather Clock</title>
</head>
<body>
      
<script src="js/d3.4.2.2.min.js"></script>

<div class="stationtime"></div>

<script>

var theme = "<?php echo $theme;?>";

if (theme === 'dark') {

    var ringColor = "rgba(230,232,239,0.2)";
    var dateColor = "silver";

} else {

    var ringColor = "#e7e9ef";
    var dateColor = "#2d3a4b";

}

</script>

<script>

var date = "<?php echo date('l d M Y');?>";

var svg = d3.select(".stationtime")
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

var now = new Date();
var yourTimeZoneFrom = "<?php echo $UTC_offset;?>";
var tzDifference = yourTimeZoneFrom * 60 + now.getTimezoneOffset();
var offset = tzDifference * 60 * 1000;
var now2 = new Date(new Date().getTime() + offset);

handData[0].value = (now2.getHours() % 12) + (now2.getMinutes() + now2.getSeconds() / 60) / 60;
handData[1].value = (now2.getMinutes() % 60) + now2.getSeconds() / 60;
handData[2].value = now2.getSeconds() + now2.getMilliseconds() / 1000;

d3.selectAll('.hands').data(handData) 
.attr('transform',function(d){return 'rotate('+ d.scale(d.value) +')';});    
}
setInterval(update, 1);

update();

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

svg.append("rect")
    .attr("x", 86)
    .attr("y", 22)
    .attr("rx", 2)
    .attr("height", 20.5)
    .attr("width", 103)
    .style("fill", "rgba(46,139,87,1)");

</script>

<div id="digitalclock"></div>
<script type ="text/javascript">

function clock() {

var now = new Date();
var yourTimeZoneFrom = <?php echo $UTC_offset?>;
var tzDifference = yourTimeZoneFrom * 60 + now.getTimezoneOffset();
var offset = tzDifference * 60 * 1000;
var now2 = new Date(new Date().getTime() + offset);

var hours = now2.getHours();
var minutes = now2.getMinutes();
var seconds = now2.getSeconds();

var digitalclock = document.getElementById('digitalclock').innerHTML = skynet(hours) + ":" + skynet(minutes) + ":" + skynet(seconds);

  function skynet(tiktok) {
    if (tiktok < 10) {
      tiktok = '0' + tiktok;
    }
    return tiktok;
  }     
}
setInterval(clock, 1000);

clock();

</script>

</body>
</html>

