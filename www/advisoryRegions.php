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

error_reporting(0);
$advisoryzone = "eu";
$json_icon = file_get_contents("jsondata/lookupTable.json");
$parsed_icon = json_decode($json_icon, true);
switch ($advisoryzone) {
case "eu": 
$json_string = file_get_contents("jsondata/awa.txt");
$parsed_json = json_decode($json_string, true);
$cnt = count($parsed_json["response"]);
$code = $parsed_json["error"]["code"];
if($code == "warn_no_data")
{$aPhrase = "There are currently no weather advisories, alerts or warnings in force.";
echo'<div class="alertbar"><bold>'.$aPhrase.'</bold></div>';}
else
{$aPhrase = 'The following Weather Alert(s) Currently in Force for South East England.';
echo'<div class="alertbar"><b>'.$aPhrase.'</b></div>';

    for ($i = 0;$i < $cnt;$i++)
    {

        $name[$i] = $parsed_json["response"][$i]["details"]["name"];
        $alerttype[$i] = explode(" ", $name[$i]);
        $bodyFull[$i] = str_replace("No Special Awareness Required", "", $parsed_json["response"][$i]["details"]["bodyFull"]);
        $type[$i] = $parsed_json["response"][$i]["details"]["type"];
        $description[$i] = $parsed_json["response"][$i]["details"]["bodyFull"];
        $level[$i] = substr($type[$i], -2);
        if ($level[$i] == "MN")
        {
            $background[$i] = "white";
            $alertColor[$i] = "black";
            $alertlevel[$i] = "MINOR WEATHER  ALERT";
        }
        else if ($level[$i] == "MD")
        {
            $background[$i] = "yellow";
            $alertColor[$i] = "black";
            $alertlevel[$i] = "MODERATE WEATHER  ALERT";
        }
        else if ($level[$i] == "SV")
        {
            $background[$i] = "orange";
            $alertColor[$i] = "black";
            $alertlevel[$i] = "SEVERE WEATHER  ALERT";
        }
        else if ($level[$i] == "EX")
        {
            $background[$i] = "red";
            $alertColor[$i] = "white";
            $alertlevel[$i] = "EXTREME WEATHER ALERT";
        }
        $alerttype[$i] = $parsed_icon['pop']['alertdesc'][$alertdesc[$i]];
        $warnimage[$i] = "css/svg/" . $parsed_icon[$background[$i]][$type[$i]];
        $begins[$i] = date("D j M H:i", strtotime($parsed_json["response"][$i]["timestamps"]["beginsISO"]));
        $expires[$i] = date("D j M H:i", strtotime($parsed_json["response"][$i]["timestamps"]["expiresISO"]));
        //$alertPhrase[$i]  = $alertlevel[$i].". ".$description[$i] .".<br>From ".$begins[$i] ." to ".$expires[$i] ."." ;
        $alertPhrase[$i]  = $alertlevel[$i].". From ".$begins[$i]." to ".$expires[$i] .".<br>".$description[$i]."." ;
      ?>
      <div class="alertbar" style="background-color:<?php echo $background[$i]?>;color:<?php echo $alertColor[$i]?>;"><div class="alert-logo-box"><img src="<?php echo $warnimage[$i]; ?>"style="width:36px";></div><div class="alert-text-box"><?php echo $alertPhrase[$i];?></div></div><?php
    }
}

break;

case "na": 

break;

case "au": 

break;

case "rw": 

break;

}
?>