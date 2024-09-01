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
//include('dvmCombinedData.php');error_reporting(0);
$json_string = file_get_contents("jsondata/awa.txt");
$parsed_json = json_decode($json_string, true);

$json_string2 = file_get_contents("jsondata/awp.txt");
$parsed_json2 = json_decode($json_string2, true);
if($temp["units"]=="F"){$outlookphrase=$parsed_json2["response"][0]["phrases"]["long"];}
else if($temp["units"]=="C"){$outlookphrase=$parsed_json2["response"][0]["phrases"]["longMET"];}
$json_icon = file_get_contents("jsondata/lookupTable.json");
$parsed_icon = json_decode($json_icon, true); 
$advisoryzone = "uk";
if($advisoryzone == "uk")
{
$xml=simplexml_load_file("jsondata/uk.txt") or die("Error: Cannot create object");
$jsonData = json_encode($xml, JSON_PRETTY_PRINT);
$parsed_json = json_decode($jsonData, true);

if(($parsed_json['channel']['item'][0]['description'])!==null){$description=$parsed_json['channel']['item'][0]['description'];}
else if (($parsed_json['channel']['item']['description']) !== null){$description=$parsed_json['channel']['item']['description'];}
      
       if (strpos($description, "Red") === 0) {$alertlevel="Red"; $lowercasealert="red"; $uppercasealert="Red Alert";}
       else if (strpos($description, "Amber") === 0) {$alertlevel="Orange"; $lowercasealert="amber"; $uppercasealert="Amber Alert";}
       else if (strpos($description, "Yellow") === 0) {$alertlevel="Yellow"; $lowercasealert="yellow"; $uppercasealert="Yellow Alert";}
       else {$alertlevel="none";}
       $alerttype ;$alertlevel;
       
       if(strpos($description, "heat") !== false) {$alerttype='Extreme Heat';}
       else if(strpos($description, "wind") !== false) {$alerttype='Wind';}
       else if(strpos($description, "snow") !== false) {$alerttype='Snow';}
       else if(strpos($description, "ice") !== false) {$alerttype='Ice';}
       else if(strpos($description, "fog") !== false) {$alerttype='Fog';}
       else if(strpos($description, "rain") !== false) {$alerttype='Rain';}
       else if(strpos($description, "lightning") !== false) {$alerttype='Lightning';}
       else if(strpos($description, "thunder") !== false) {$alerttype='Thunderstorms';}
       else {$alertlevel="none";}
       $warnimage = $parsed_icon[$lowercasealert][$alerttype];
?>


