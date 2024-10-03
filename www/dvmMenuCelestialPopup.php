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
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php
include ('dvmCombinedData.php');
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
  height: 586px;
}

#Tab1 {background-color: <?php echo $background ?>;}
#Tab2 {background-color: <?php echo $background ?>;}
#Tab3 {background-color: <?php echo $background ?>;}
#Tab4 {background-color: <?php echo $background ?>;}
#Tab5 {background-color: <?php echo $background ?>;}
#Tab6 {background-color: <?php echo $background ?>;}    
   

</style>
</head>
<body>

<button class="tablink" onclick="openPage('Tab1', this, 'rgba(194, 102, 58)')" id="defaultOpen">Meteor Showers</button>
<button class="tablink" onclick="openPage('Tab2', this, 'rgba(194, 102, 58)')">Aurora</button>
<button class="tablink" onclick="openPage('Tab3', this, 'rgba(194, 102, 58)')">Moon Data</button>

  
  <div id="Tab1" class="tabcontent">
  
  <iframe width="100%" height="92%" scrolling="no" src="dvmMeteorshowersPopup.php" frameborder="0"></iframe>
</div>

<div id="Tab2" class="tabcontent">
  
  <iframe width="100%" height="92%" scrolling="no" src="dvmAuroraPopup.php" frameborder="0"></iframe>
</div>

<div id="Tab3" class="tabcontent">
  
  <iframe width="100%" height="92%" scrolling="no" src="dvmMoonInfoPopup.php" frameborder="0"></iframe>
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
