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

            $validpos = strpos($description, "valid");
            $validtext = substr($description, $validpos);
            $datestring = explode(" ", $validtext);
            $hourFrom = substr($datestring[2], 0, 2);
            $hourTo = substr($datestring[7], 0, 2);
            $minFrom = substr($datestring[2], 2, 2);
            $minTo = substr($datestring[7], 2, 2);
            $dayFrom = substr($datestring[4], 0, 2);
            $dayTo = substr($datestring[9], 0, 2);
            $monthFrom = substr($datestring[5], 0, 3);
            $monthTo = substr($datestring[10], 0, 3);
            $fromTime = $hourFrom . ":" . $minFrom . " " . $dayFrom . " " . $monthFrom . " " . date("Y");
            $toTime = $hourTo . ":" . $minTo . " " . $dayTo . " " . $monthTo . " " . date("Y");
            $fromTimeStamp = strtotime($fromTime);
            $toTimeStamp = strtotime($toTime);

            $from = date_create($fromTime, new DateTimeZone("GMT"))->setTimezone(new DateTimeZone("Europe/London"))
                ->format("H:i l j F");
            $to = date_create($toTime, new DateTimeZone("GMT"))->setTimezone(new DateTimeZone("Europe/London"))
                ->format("H:i l j F");

            $fromto = "Valid from " . "$from" . " to " . "$to" . " ";
            $description = substr($description, 0, ($validpos - 1)) . ".";
      
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
       //$lowercasealert="yellow";
       //$alertlevel="Yellow";
       //$alerttype="Thunderstorms";

       $warnimage = $parsed_icon[$lowercasealert][$alerttype];

///MetOffice
if ($alertlevel !== "none")
{$alertPhrase = " ".$alertlevel." Warning ".$alerttype.". ".$fromto;". ".$description;}
  

  else if ($alertlevel === "none")
  {$alertPhrase = "No Warnings in Force";} 
    
}
//North America
else if($advisoryzone == "na")
{
$name = $parsed_json["response"][0]["details"]["name"];
$color = $parsed_json["response"][0]["details"]["color"];
$begins = $parsed_json["response"][0]["timestamps"]["beginsISO"];
$expires = $parsed_json["response"][0]["timestamps"]["expiresISO"];
$code = $parsed_json["error"]["code"];
if ($code == "warn_no_data")
  {$alertPhrase = "No Warnings in Force"; }  
else if ($code == "")
{$alertPhrase = " ".$name." Warning ".$alerttype;}
}

?>

