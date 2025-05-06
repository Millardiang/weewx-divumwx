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
$json_string2 = file_get_contents("jsondata/awp.txt");
$parsed_json2 = json_decode($json_string2, true);
if($temp["units"]=="F"){$phrase=$parsed_json2["response"][0]["phrases"]["long"];}
else if($temp["units"]=="C"){$phrase=$parsed_json2["response"][0]["phrases"]["longMET"];}
$outlookPhrase = str_replace(" degrees","&deg".$temp["units"],$phrase);
?>

<!doctype html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Weather Clock</title>
</head>
<body>
<div class="chartforecast">
</div>
<span class='moduletitle'><?php echo $lang['timeTop']; ?></valuetitleunit></span>
    
      
<script src="js/d3.7.9.0.min.js"></script>

<div class="stationtime"></div>

<script>

var theme = "<?php echo $theme;?>";

    var ringColor = "var(--col-12)";
    var dateColor = "var(--col-6)";

var date = "<?php echo date('l jS M Y');?>";

var svg = d3.select(".stationtime")
    .append("svg")
    .attr("width", 226)
    /*.style("background", "#292e35")*/
    .attr("height", 56);

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
    .style("fill", "var(--col-4)");

</script>

<div id="digitalclock"></div>
<!--outlook-->
  
<div class="clockOutlook"><?php echo $outlookPhrase?></div>

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
<!--outlook-->
  

</body>
</html>

