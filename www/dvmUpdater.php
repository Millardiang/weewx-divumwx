<!-- begin updater.php  14-Nov-2022 -->
<?php
include_once "userSettings.php";

include_once "fixedSettings.php";

include_once "common.php";
date_default_timezone_set($TZ);
//check which hemisphere for sun option and moonphase option
if ($sta["latitude"] > "90") {
    $sunoption = "sun4.php";
    $hemisphere = "180";
} else {
    $sunoption = "sun3.php";
    $hemisphere = "0";
}
?>

<script src="js/jquery.js"></script>
<script>

//update the modules

//update the modules position 1 - fixed position
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position1").show()}});var c=a("#position1");c.load("dvmWeatherClockTop.php");
var b=setInterval(function(){c.load("dvmWeatherClockTop.php")},<?php echo $cycles1; ?>)})})(jQuery); // 24 hours 
    
//update the modules position 2
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position2").show()}});var c=a("#position2");c.load("<?php echo $position2; ?>");
var b=setInterval(function(){c.load("<?php echo $position2; ?>")},<?php echo $cycles2; ?>)})})(jQuery); // 60 seconds
  
//update the modules  position 3
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position3").show()}});var c=a("#position3");c.load("<?php echo $position3; ?>");
var b=setInterval(function(){c.load("<?php echo $position3; ?>")},<?php echo $cycles3; ?>)})})(jQuery); // 60 seconds
  
//update the modules  position 4 - fixed position
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position4").show()}});var c=a("#position4");c.load("dvmAdvisoryOutlookTop.php");
var b=setInterval(function(){c.load("dvmAdvisoryOutlookTop.php")},<?php echo $cycles4; ?>)})})(jQuery); // 60 seconds

//update the modules position 5
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position5").show()}});var c=a("#position5");c.load("<?php echo $position5; ?>");
var b=setInterval(function(){c.load("<?php echo $position5; ?>")},<?php echo $cycles5; ?>)})})(jQuery); // 60 seconds

//update the modules position 6
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position6").show()}});var c=a("#position6");c.load("<?php echo $position6; ?>");
var b=setInterval(function(){c.load("<?php echo $position6; ?>")},<?php echo $cycles6; ?>)})})(jQuery); // 60 seconds

//update the modules position 7
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position7").show()}});var c=a("#position7");c.load("<?php echo $position7; ?>");
var b=setInterval(function(){c.load("<?php echo $position7; ?>")},<?php echo $cycles7; ?>)})})(jQuery); // 60 seconds

//update the modules position 8
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position8").show()}});var c=a("#position8");c.load("<?php echo $position8; ?>");
var b=setInterval(function(){c.load("<?php echo $position8; ?>")},<?php echo $cycles8; ?>)})})(jQuery); // 4 seconds (wind module)
     
//update the modules position 9
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position9").show()}});var c=a("#position9");c.load("<?php echo $position9; ?>");
var b=setInterval(function(){c.load("<?php echo $position9; ?>")},<?php echo $cycles9; ?>)})})(jQuery); // 60 seconds

//update the modules position 10
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position10").show()}});var c=a("#position10");c.load("<?php echo $position10; ?>");
var b=setInterval(function(){c.load("<?php echo $position10; ?>")},<?php echo $cycles10; ?>)})})(jQuery); // 60 seconds

//update the modules position 11
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position11").show()}});var c=a("#position11");c.load("<?php echo $position11; ?>");
var b=setInterval(function(){c.load("<?php echo $position11; ?>")},<?php echo $cycles11; ?>)})})(jQuery); // 60 seconds

//update the modules position 12
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position12").show()}});var c=a("#position12");c.load("<?php echo $position12; ?>");
var b=setInterval(function(){c.load("<?php echo $position12; ?>")},<?php echo $cycles12; ?>)})})(jQuery); // 60 seconds

//update the modules position 13
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position13").show()}});var c=a("#position13");c.load("<?php echo $position13; ?>");
var b=setInterval(function(){c.load("<?php echo $position13; ?>")},<?php echo $cycles13; ?>)})})(jQuery); // 60 seconds

//update the modules position 14
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position14").show()}});var c=a("#position14");c.load("<?php echo $position14; ?>");
var b=setInterval(function(){c.load("<?php echo $position14; ?>")},<?php echo $cycles14; ?>)})})(jQuery); // 60 seconds

//update the modules position 15
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position15").show()}});var c=a("#position15");c.load("<?php echo $position15; ?>");
var b=setInterval(function(){c.load("<?php echo $position15; ?>")},<?php echo $cycles15; ?>)})})(jQuery); // 60 seconds

//update the modules position 16
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position16").show()}});var c=a("#position16");c.load("<?php echo $position16; ?>");
var b=setInterval(function(){c.load("<?php echo $position16; ?>")},<?php echo $cycles16; ?>)})})(jQuery); // 60 seconds

//update the modules position 16
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position16").show()}});var c=a("#position16");c.load("<?php echo $position16; ?>");
var b=setInterval(function(){c.load("<?php echo $position16; ?>")},<?php echo $cycles16; ?>)})})(jQuery); // 60 seconds

//update the modules position 17
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position17").show()}});var c=a("#position17");c.load("<?php echo $position17; ?>");
var b=setInterval(function(){c.load("<?php echo $position17; ?>")},<?php echo $cycles17; ?>)})})(jQuery); // 60 seconds

//update the modules position 18
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position18").show()}});var c=a("#position18");c.load("<?php echo $position18; ?>");
var b=setInterval(function(){c.load("<?php echo $position18; ?>")},<?php echo $cycles18; ?>)})})(jQuery); // 60 seconds

//update the modules position 19
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position19").show()}});var c=a("#position19");c.load("<?php echo $position19; ?>");
var b=setInterval(function(){c.load("<?php echo $position19; ?>")},<?php echo $cycles19; ?>)})})(jQuery); // 60 seconds

//update the moonphase and earth image
var refreshId;$(document).ready(function(){moonearthimage()});function moonearthimage(){$.ajax({cache:true,success:function(a){$("#moonearthimage").html(a);<?php if (
    $moonRefresh > 0
) {
    echo "setTimeout(moonearthimage, 3600000)";
} ?>},
	type:"GET",url:"dvmGetMoonEarth.php"})};

//update the AQI data
var refreshId;$(document).ready(function(){aqidata()});function aqidata(){$.ajax({cache:true,success:function(a){$("#aqidata").html(a);<?php if (
    $aqiRefresh > 0
) {
    echo "setTimeout(aqidata, 60000)";
} ?>},
	type:"GET",url:"aqitextcreate.php"})};

			

</script>
<?php 
$position1 = "dvmWeatherClockTop.php";//fixed position
if ($position1 == "dvmWeatherClockTop.php") { ?>
<script>
var clockID;
var yourTimeZoneFrom=<?php echo $UTC_offset; ?>;
var d=new Date();
var weekdays=["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
var months=["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
var tzDifference=yourTimeZoneFrom*60+d.getTimezoneOffset();
var offset=tzDifference*60*1000;
function UpdateClock(){
  var e=new Date(new Date().getTime()+offset);
  var c=e.getHours()<?php if ($clockformat == "12") {
      echo "% 12 || 12";
  } else {
      echo "% 24 || 00";
  } ?>;
  <?php if ($clockformat == "12") {
      echo "if(e.getHours()<12){amorpm=' am'}else{amorpm=' pm'}";
  } else {
      echo "amorpm='';";
  } ?>
  var a=e.getMinutes();
  var g=e.getSeconds();
  var f=e.getFullYear();
  var h=months[e.getMonth()];
  var b=e.getDate();
  var i=weekdays[e.getDay()];
  if(a<10){
    a="0"+a
  }
  if(g<10){
    g="0"+g
  }
  if(c<10){
    c="0"+c
  }
  document.getElementById("theTime").innerHTML="<div class='weatherclock34'> "+i+" "+b+" "+h+" "+f+"<div class='orangeclock'>"+c+":"+a+":"+g+amorpm
}
function StartClock(){clockID=setInterval(UpdateClock,500)}
function KillClock(){clearTimeout(clockID)}
window.onload=function(){StartClock()};
</script>
<?php } ?>

<!-- end updater.php -->
