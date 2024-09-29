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
echo '<style>#alert {background-color:transparent;color:var(--col-6);opacity:1;transition:opacity 0.6s;margin-top:4px;margin-bottom:4px;border-radius:5px;}</style>';
error_reporting(0);
if ($advisoryzone == "eu") {
    $advisoryzoneMapping = "europe";
} elseif ($advisoryzone == "uk") {
    $advisoryzoneMapping = "unitedkingdom";
} else {
    $advisoryzoneMapping = "restofworld";
}
$json_icon = file_get_contents("jsondata/lookupTable.json");
$parsed_icon = json_decode($json_icon, true);
switch ($advisoryzoneMapping) {
    case "unitedkingdom":
        $json_string = file_get_contents("jsondata/awa.txt");
        $parsed_json = json_decode($json_string, true);
        $cnt = count($parsed_json["response"]);
        $flood_string = file_get_contents("jsondata/flood.txt");
        $flood_json = json_decode($flood_string, true);
        $fCnt = count($flood_json["items"]);
        $aHeader = "Alert(s) or warning(s) currently in force potentially affecting the ";
        echo '<div id="alert">';
        if ($cnt + $fCnt > 0) {
            $aPhrase = $aHeader . $stationlocation . " area.";
            echo '<div class="alertbar" style="margin-bottom:4px;"><b>' . $aPhrase . '</b></div>';
        }
        //weather alerts
        for ($i = 0; $i < $cnt; $i++) {

            $name[$i] = $parsed_json["response"][$i]["details"]["name"];
            $alerttype[$i] = explode(" ", $name[$i]);
            $bodyFull[$i] = str_replace("No Special Awareness Required", "", $parsed_json["response"][$i]["details"]["bodyFull"]);
            $type[$i] = $parsed_json["response"][$i]["details"]["type"];
            $description[$i] = $parsed_json["response"][$i]["details"]["bodyFull"];
            $level[$i] = substr($type[$i], -2);
            $color[$i] = $parsed_json["response"][$i]["details"]["color"];
            if ($level[$i] == "MN") {
                $background[$i] = "white";
                $alertColor[$i] = "black";
                $alertlevel[$i] = "MINOR";
                $alertlevelColor[$i] = "MINOR";
            } elseif ($level[$i] == "MD") {
                $background[$i] = "yellow";
                $alertColor[$i] = "black";
                $alertlevel[$i] = "MODERATE";
                $alertlevelColor[$i] = "YELLOW";
            } elseif ($level[$i] == "SV") {
                $background[$i] = "#FFBF00";
                $alertColor[$i] = "black";
                $alertlevel[$i] = "SEVERE";
                $alertlevelColor[$i] = "AMBER";
            } elseif ($level[$i] == "EX") {
                $background[$i] = "red";
                $alertColor[$i] = "white";
                $alertlevel[$i] = "EXTREME";
                $alertlevelColor[$i] = "RED";
            }
            $nameColor[$i] = str_replace($alertlevel[$i], $alertlevelColor[$i], $name[$i]);
            $alerttype[$i] = $parsed_icon['pop']['alertdesc'][$alertdesc[$i]];
            $warnimage[$i] = "css/svg/" . $parsed_icon[$background[$i]][$type[$i]];
            $begins[$i] = date("D j M H:i", strtotime($parsed_json["response"][$i]["timestamps"]["beginsISO"]));
            $expires[$i] = date("D j M H:i", strtotime($parsed_json["response"][$i]["timestamps"]["expiresISO"]));
            $alertHeadline[$i] = "  " . $nameColor[$i] . " ALERT. From " . $begins[$i] . " to " . $expires[$i] . ".  ";
            ?>
 
      <section>
      <div class="alertbar" style="margin-bottom:4px;padding-bottom:10px;background-color:<?php echo $background[$i]; ?>;color:<?php echo $alertColor[$i]; ?>;border-radius:5px;">
      <div class="alert-text-box" style="padding-left:20px;padding-right:20px;display:flex;margin: 0 auto;">
		<div class="post" style="font-weight:500; font-size:14px; color:<?php echo $alertColor[$i]; ?>;">
			<img src="<?php echo $warnimage[$i]; ?>"style="margin-bottom:-10px;"><?php echo $alertHeadline[$i]; ?><img src="<?php echo $warnimage[$i]; ?>"style="margin-bottom:-10px;">
			
			<span class="more" style="padding-top:-20px;display:none;"><p><?php echo $description[$i] . "."; ?></p></span>

			<more-button class="read">More</more-button>
		</div></div></div></div>
	</section>	
	
            
      <?php
        }
        //english flood alerts

        for ($i = 0; $i < $fCnt; $i++) {
            $floodDescription[$i] = $flood_json["items"][$i]["description"];
            $floodUpdated[$i] = date("D j M H:i", strtotime($flood_json["items"][$i]["timeMessageChanged"]));
            $floodBodyFull[$i] = str_replace("No Special Awareness Required", "", $flood_json["items"][$i]["message"]);
            $floodMessage[$i] = $flood_json["items"][$i]["message"];
            $floodLevel[$i] = $flood_json["items"][$i]["severityLevel"];
            $floodAlertlevel[$i] = $flood_json["items"][$i]["severity"];
            if ($floodLevel[$i] == 4) {
                $floodBackground[$i] = "white";
                $floodAlertColor[$i] = "black";
            } elseif ($floodLevel[$i] == 3) {
                $floodBackground[$i] = "orange";
                $floodAlertColor[$i] = "black";
            } elseif ($floodLevel[$i] == 2) {
                $floodBackground[$i] = "red";
                $floodAlertColor[$i] = "white";
            } elseif ($floodFevel[$i] == 1) {
                $floodBackground[$i] = "red";
                $floodAlertColor[$i] = "white";
            }
            $floodHeadline[$i] = "  " . $floodAlertlevel[$i] . " for " . $floodDescription[$i] . ".  Updated ".$floodUpdated[$i].".";
 
            if (str_contains($floodMessage[$i], $englishFloodLocation)) { ?>
 
      <section>
      <div class="alertbar" style="margin-bottom:4px;padding-bottom:10px;background-color:<?php echo $floodBackground[$i]; ?>;color:<?php echo $floodAlertColor[$i]; ?>;border-radius:5px;">
      <div class="alert-text-box" style="padding-left:20px;padding-right:20px;display:flex;margin: 0 auto;">
		<div class="post" style="font-weight:500; font-size:14px; color:<?php echo $floodAlertColor[$i]; ?>;"><img src="css/svg/icon-warning-flood-<?php echo $floodBackground[$i]; ?>.svg"style="margin-bottom:-10px;"><?php echo $floodHeadline[
    $i
]; ?><img src="css/svg/icon-warning-flood-<?php echo $floodBackground[$i]; ?>.svg"style="margin-bottom:-10px;">
			
			<span class="more" style="padding-top:-20px;display:none;"><p><?php echo $floodMessage[$i] . "."; ?></p></span>

			<more-button class="read">More</more-button>
		</div></div></div></div>
	</section>	
	
            
      <?php }
        }

        break;
    case "europe":
        $json_string = file_get_contents("jsondata/awa.txt");
        $parsed_json = json_decode($json_string, true);
        $cnt = count($parsed_json["response"]);
        if ($cnt === 1) {
            $aHeader = "The following Weather Alert is Currently in Force for ";
        } else {
            $aHeader = "The following Weather Alerts are Currently in Force for ";
        }
        $code = $parsed_json["error"]["code"];
        echo '<div id="alert">';
        if ($code == "warn_no_data") {
            $aPhrase = "There are currently no weather advisories, alerts or warnings in force for " . $advisoryregion . ".";
            echo '<div class="alertbar"><bold>' . $aPhrase . '</bold></div>';
        } else {
            $aPhrase = $aHeader . $advisoryregion . ".";
            echo '<div class="alertbar" style="margin-bottom:4px;"><b>' . $aPhrase . '</b></div>';
        }
        for ($i = 0; $i < $cnt; $i++) {

            $name[$i] = $parsed_json["response"][$i]["details"]["name"];
            $alerttype[$i] = explode(" ", $name[$i]);
            $bodyFull[$i] = str_replace("No Special Awareness Required", "", $parsed_json["response"][$i]["details"]["bodyFull"]);
            $type[$i] = $parsed_json["response"][$i]["details"]["type"];
            $description[$i] = $parsed_json["response"][$i]["details"]["bodyFull"];
            $level[$i] = substr($type[$i], -2);
            $color[$i] = $parsed_json["response"][$i]["details"]["color"];
            if ($level[$i] == "MN") {
                $background[$i] = "white";
                $alertColor[$i] = "black";
                $alertlevel[$i] = "MINOR";
                $alertlevelColor[$i] = "MINOR";
            } elseif ($level[$i] == "MD") {
                $background[$i] = "yellow";
                $alertColor[$i] = "black";
                $alertlevel[$i] = "MODERATE";
                $alertlevelColor[$i] = "YELLOW";
            } elseif ($level[$i] == "SV") {
                $background[$i] = "orange";
                $alertColor[$i] = "black";
                $alertlevel[$i] = "SEVERE";
                $alertlevelColor[$i] = "ORANGE";
            } elseif ($level[$i] == "EX") {
                $background[$i] = "red";
                $alertColor[$i] = "white";
                $alertlevel[$i] = "EXTREME";
                $alertlevelColor[$i] = "RED";
            }
            $nameColor[$i] = str_replace($alertlevel[$i], $alertlevelColor[$i], $name[$i]);
            $alerttype[$i] = $parsed_icon['pop']['alertdesc'][$alertdesc[$i]];
            $warnimage[$i] = "css/svg/" . $parsed_icon[$background[$i]][$type[$i]];
            $begins[$i] = date("D j M H:i", strtotime($parsed_json["response"][$i]["timestamps"]["beginsISO"]));
            $expires[$i] = date("D j M H:i", strtotime($parsed_json["response"][$i]["timestamps"]["expiresISO"]));
            $alertHeadline[$i] = "  " . $nameColor[$i] . " ALERT. From " . $begins[$i] . " to " . $expires[$i] . ".  ";
            ?>
 
      <section>
      <div class="alertbar" style="margin-bottom:4px;padding-bottom:10px;background-color:<?php echo $background[$i]; ?>;color:<?php echo $alertColor[$i]; ?>;border-radius:5px;">
      <div class="alert-text-box" style="padding-left:20px;padding-right:20px;display:flex;margin: 0 auto;
">
		<div class="post" style="font-weight:500; font-size:14px; color:<?php echo $alertColor[$i]; ?>;">
			<img src="<?php echo $warnimage[$i]; ?>"style="margin-bottom:-10px;"><?php echo $alertHeadline[$i]; ?><img src="<?php echo $warnimage[$i]; ?>"style="margin-bottom:-10px;">
			
			<span class="more" style="padding-top:-20px;display:none;"><p><?php echo $description[$i] . "."; ?></p></span>

			<more-button class="read">More</more-button>
		</div></div></div></div>
	</section>	
      
      <?php
        }
        break;

    case "restofworld":
        $json_string = file_get_contents("jsondata/awa.txt");
        $parsed_json = json_decode($json_string, true);
        $cnt = count($parsed_json["response"]);
        if ($cnt === 1) {
            $aHeader = "The following Weather Alert is Currently in Force for ";
        } else {
            $aHeader = "The following Weather Alerts are Currently in Force for ";
        }
        $code = $parsed_json["error"]["code"];
        echo '<div id="alert">';
        if ($code == "warn_no_data") {
            $aPhrase = "There are currently no weather advisories, alerts or warnings in force for " . $advisoryregion . ".";
            echo '<div class="alertbar"><bold>' . $aPhrase . '</bold></div>';
        } else {
            $aPhrase = $aHeader . $advisoryregion . ".";
            echo '<div class="alertbar" style="margin-bottom:4px;"><b>' . $aPhrase . '</b></div>';
        }
        for ($i = 0; $i < $cnt; $i++) {

            $name[$i] = $parsed_json["response"][$i]["details"]["name"];
            $alerttype[$i] = explode(" ", $name[$i]);
            $bodyFull[$i] = str_replace("No Special Awareness Required", "", $parsed_json["response"][$i]["details"]["bodyFull"]);
            $type[$i] = $parsed_json["response"][$i]["details"]["type"];
            $description[$i] = $parsed_json["response"][$i]["details"]["bodyFull"];
            $level[$i] = substr($type[$i], -2);
            $color[$i] = $parsed_json["response"][$i]["details"]["color"];

            $background[$i] = "$color[$i]";
            $alertColor[$i] = "black";
            $nameColor[$i] = str_replace($alertlevel[$i], $alertlevelColor[$i], $name[$i]);
            $alerttype[$i] = $parsed_icon['pop']['alertdesc'][$alertdesc[$i]];
            $warnimage[$i] = "css/svg/" . $parsed_icon[$background[$i]][$type[$i]];
            $begins[$i] = date("D j M H:i", strtotime($parsed_json["response"][$i]["timestamps"]["beginsISO"]));
            $expires[$i] = date("D j M H:i", strtotime($parsed_json["response"][$i]["timestamps"]["expiresISO"]));
            $alertHeadline[$i] = "  " . $nameColor[$i] . " ALERT. From " . $begins[$i] . " to " . $expires[$i] . ".";
            ?>
 
      <section>
      <div class="alertbar" style="margin-bottom:4px;padding-bottom:10px;background-color:<?php echo $background[$i]; ?>;color:<?php echo $alertColor[$i]; ?>;border-radius:5px;">
      <div class="alert-text-box" style="padding-left:20px;padding-right:20px;display:flex;margin: 0 auto;
">
		<div class="post" style="font-weight:500; font-size:14px; color:<?php echo $alertColor[$i]; ?>;"><?php echo $alertHeadline[$i]; ?>
			
			<span class="more" style="padding-top:-20px;display:none;"><p><?php echo $description[$i] . "."; ?></p></span>

			<more-button class="read">More</more-button>
		</div></div></div></div>
	</section>	
      
      <?php
        }
        break;
}
?> 
<script>
var close = document.getElementsByClassName("closebtn");
var i;

for (i = 0; i < close.length; i++) {
  close[i].onclick = function(){
    var div = this.parentElement;
    div.style.opacity = "0";
    setTimeout(function(){ div.style.display = "none"; }, 600);
  }
}

$(document).ready(function(){
	$(".read").click(function(){
		$(this).prev().toggle();
		$(this).siblings('.dots').toggle();
		if($(this).text()=='More'){
			$(this).text('Less');
		}
		else{
			$(this).text('More');
		}
	});
});
</script>
<script>
function myFunction() {
  var x = document.getElementById("alert");
  if (x.style.display === "none") {
    x.style.display = "block";
  } else {
    x.style.display = "none";
  }
}
</script>

