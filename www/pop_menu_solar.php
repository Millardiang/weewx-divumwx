<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php
//###################################################################################################################
//	weewx-Weather34 Template maintained by Ian Millard (Steepleian)                                 				#
//	                                                                                                				#
//  Contains original code by Ian Millard and collaborators															#
//  © claydonsweather.org.uk original CSS/SVG/PHP 2020-2021                                                            #
// 	                                                                                                				#
// 	Issues for weewx-Weather34 template should be addressed to https://github.com/steepleian/weewx-Weather34/issues #                                                                                              #
// 	                                                                                                				#
//###################################################################################################################
  
include_once ('settings.php');
include ('w34CombinedData.php');
date_default_timezone_set($TZ);
if($theme==="light"){$background="white";$text="black";}
else if($theme==="dark"){$background="rgba(34, 35, 40)";$text="white";}
?>  
  <style>
* {box-sizing: border-box}

/* Set height of body and the document to 100% */
body, html {
  height: 100%;
  margin: 0;
  font-family: Arial;
  overflow: hidden;
}

/* Style tab links */
.tablink {
  background-color: #555;
  color: white;
  float: left;
  border: 2px solid <?php echo $background ?>;
  border-radius: 5px;
  margin-top: 5px;
  margin-left:5px;
  outline: none;
  cursor: pointer;
  padding: 6px 6px;
  font-size: 10px;
  
}

.tablink:hover {
  background-color: #777;
}

/* Style the tab content (and add height:100% for full page content) */
.tabcontent {
  color: white;
  display: none;
  padding: 0px 0px;
  height: 570px;
}

#Tab1 {background-color: <?php echo $background ?>;}
#Tab2 {background-color: <?php echo $background ?>;}
#Tab3 {background-color: <?php echo $background ?>;}
#Tab4 {background-color: <?php echo $background ?>;}
#Tab5 {background-color: <?php echo $background ?>;}    

</style>
</head>
<body>

<button class="tablink" onclick="openPage('Tab1', this, 'rgba(194, 102, 58)')" id="defaultOpen">UV Index Almanac</button>
<button class="tablink" onclick="openPage('Tab2', this, 'rgba(194, 102, 58)')">Solar Almanac</button>
<button class="tablink" onclick="openPage('Tab3', this, 'rgba(194, 102, 58)')">UV Guide</button>  
  
  <div id="Tab1" class="tabcontent">
  
  <iframe width="100%" height="92%" scrolling="no" src="pop_uvalmanac.php" frameborder="0"></iframe>
</div>

<div id="Tab2" class="tabcontent">
  
  <iframe width="100%" height="92%" scrolling="no" src="pop_solaralmanac.php" frameborder="0"></iframe>
</div>
  
  
  
  <div id="Tab3" class="tabcontent">
  
  <iframe width="100%" height="92%" scrolling="no" src="pop_uvindex.php" frameborder="0"></iframe>
</div>
  
  
  

<script>
function openPage(pageName,elmnt,color) {
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablink");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].style.backgroundColor = "";
  }
  document.getElementById(pageName).style.display = "block";
  elmnt.style.backgroundColor = color;
}

// Get the element with id="defaultOpen" and click on it
document.getElementById("defaultOpen").click();
</script>
   
</body>
</html> 
