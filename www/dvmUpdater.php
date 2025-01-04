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

$filepileRefresh = 60;
include_once "userSettings.php";

include_once "fixedSettings.php";

include_once "common.php";
date_default_timezone_set($TZ);

?>

<script src="js/jquery.js"></script>
<script>

//update the modules

//update the modules position 1 - fixed position
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position1").show()}});var c=a("#position1");c.load("<?php echo $position1; ?>");
var b=setInterval(function(){c.load("<?php echo $position1; ?>")},<?php echo $cycles1; ?>)})})(jQuery); // 24 hours 
    
//update the modules position 2
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position2").show()}});var c=a("#position2");c.load("<?php echo $position2; ?>");
var b=setInterval(function(){c.load("<?php echo $position2; ?>")},<?php echo $cycles2; ?>)})})(jQuery); // 60 seconds
  
//update the modules  position 3
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position3").show()}});var c=a("#position3");c.load("<?php echo $position3; ?>");
var b=setInterval(function(){c.load("<?php echo $position3; ?>")},<?php echo $cycles3; ?>)})})(jQuery); // 60 seconds
  
//update the modules  position 4 - fixed position
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position4").show()}});var c=a("#position4");c.load("<?php echo $position4; ?>");
var b=setInterval(function(){c.load("<?php echo $position4; ?>")},<?php echo $cycles4; ?>)})})(jQuery); // 60 seconds

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

//update the modules position 20
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position20").show()}});var c=a("#position20");c.load("<?php echo $position20; ?>");
var b=setInterval(function(){c.load("<?php echo $position20; ?>")},<?php echo $cycles20; ?>)})})(jQuery); // 60 seconds

//update the modules position 21
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position21").show()}});var c=a("#position21");c.load("<?php echo $position21; ?>");
var b=setInterval(function(){c.load("<?php echo $position21; ?>")},<?php echo $cycles21; ?>)})})(jQuery); // 60 seconds

//update the modules position 22
(function(a){a(document).ready(function(){a.ajaxSetup({cache:true,success:function(){a("#position22").show()}});var c=a("#position22");c.load("<?php echo $position22; ?>");
var b=setInterval(function(){c.load("<?php echo $position22; ?>")},<?php echo $cycles22; ?>)})})(jQuery); // 60 seconds

//update the webcam image file
var refreshId;$(document).ready(function(){webcamcron()});function webcamcron(){$.ajax({cache:false,
  success:function(a){$("#blank")
  .html(a);<?php if ($filepileRefresh >0) {
  echo 'setTimeout(webcamcron,' . 1000*$filepileRefresh.')';}?>},
  contentType: "application/x-www-form-urlencoded;charset=ISO-8859-15",
  type:"GET",url:"dvmGetWebcamImg.php"})}; 

//update the filepileTextData.txt
var refreshId;$(document).ready(function(){stationcron()});function stationcron(){$.ajax({cache:false,
  success:function(a){$("#blank")
  .html(a);<?php if ($filepileRefresh >0) {
  echo 'setTimeout(stationcron,' . 1000*$filepileRefresh.')';}?>},
  contentType: "application/x-www-form-urlencoded;charset=ISO-8859-15",
  type:"GET",url:"filepileTextCreate.php"})};

</script>

<!-- end updater.php -->
