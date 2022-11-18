<!-- begin updater.php  14-Nov-2022 -->
<?php
include_once "settings1.php";
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

//update the modules position 1
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position1").show()}});var c=a("#position1");c.load("<?php echo $position1; ?>");
var b=setInterval(function(){c.load("<?php echo $position1; ?>")},<?php echo $sensorcycle5; ?>)})})(jQuery);//60 minutes
    
//update the modules position 2
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position2").show()}});var c=a("#position2");c.load("<?php echo $position2; ?>");
var b=setInterval(function(){c.load("<?php echo $position2; ?>")},<?php echo $sensorcycle4; ?>)})})(jQuery);//60 seconds
  
//update the modules  position 3
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position3").show()}});var c=a("#position3");c.load("<?php echo $position3; ?>");
var b=setInterval(function(){c.load("<?php echo $position3; ?>")},<?php echo $sensorcycle4; ?>)})})(jQuery);//60 seconds
  
//update the modules  position 4
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position4").show()}});var c=a("#position4");c.load("<?php echo $position4; ?>");
var b=setInterval(function(){c.load("<?php echo $position4; ?>")},<?php echo $sensorcycle5; ?>)})})(jQuery);//60 minutes

//update the modules position 5
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#temperature").show()}});var c=a("#temperature");c.load("dvmTemperatureModule.php");
var b=setInterval(function(){c.load("dvmTemperatureModule.php")},<?php echo $sensorcycle4; ?>)})})(jQuery);//60 seconds

//update the modules position 6
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#currentfore").show()}});var c=a("#currentfore");c.load("<?php echo $position6; ?>");
var b=setInterval(function(){c.load("<?php echo $position6; ?>")},<?php echo $sensorcycle4; ?>)})})(jQuery);//60 seconds

//update the modules position 7
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#currentsky").show()}});var c=a("#currentsky");c.load("dvmCurrentModule.php");
var b=setInterval(function(){c.load("dvmCurrentModule.php")},<?php echo $sensorcycle4; ?>)})})(jQuery);//60 seconds

//update the modules position 8
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#windspeed").show()}});var c=a("#windspeed");c.load("dvmWindModule.php");
var b=setInterval(function(){c.load("dvmWindModule.php")},<?php echo $sensorcycle1; ?>)})})(jQuery);//4.8 seconds
     

//update the modules position 9
var refreshId;$(document).ready(function(){barometer()});function barometer(){$.ajax({cache:false,success:function(a){$("#barometer").html(a);<?php if (
    $baroRefresh > 0
) {
    echo "setTimeout(barometer," . $sensorcycle4 . ")";
} ?>},
	type:"GET",url:"dvmBarometerModule.php"})};

//update the modules position 10
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#solardial").show()}});var c=a("#solardial");c.load("dvmSolarDialModule.php");
var b=setInterval(function(){c.load("dvmSolarDialModule.php")},<?php echo $sensorcycle5; ?>)})})(jQuery);//60 minutes

//update the modules position 11
var refreshId;$(document).ready(function(){rainfall()});function rainfall(){$.ajax({cache:false,success:function(a){$("#rainfall").html(a);<?php if (
    $rainRefresh > 0
) {
    echo "setTimeout(rainfall," . $sensorcycle3 . ")";
} ?>},
	type:"GET",url:"dvmRainfallModule.php"})};

//update the modules position 12
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position12").show()}});var c=a("#position12");c.load("<?php echo $position12; ?>");
var b=setInterval(function(){c.load("<?php echo $position12; ?>")},<?php echo $sensorcycle4; ?>)})})(jQuery);//60 seconds

//update the modules position 13
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position13").show()}});var c=a("#position13");c.load("<?php echo $position13; ?>");
var b=setInterval(function(){c.load("<?php echo $position13; ?>")},<?php echo $sensorcycle5; ?>)})})(jQuery);//60 minutes

//update the moonphase image
var refreshId;$(document).ready(function(){moonimage()});function moonimage(){$.ajax({cache:false,success:function(a){$("#moonimage").html(a);<?php if (
    $moonRefresh > 0
) {
    echo "setTimeout(moonimage, 3600000)";
} ?>},
	type:"GET",url:"dvmGetMoon.php"})};			

</script>
<?php if ($position1 == "dvmWeatherClockModule.php") { ?>
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

