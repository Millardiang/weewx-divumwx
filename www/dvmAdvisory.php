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
       //$lowercasealert="yellow";
       //$alertlevel="Yellow";
       //$alerttype="Thunderstorms";

       $warnimage = $parsed_icon[$lowercasealert][$alerttype];

///MetOffice
if ($alertlevel !== "none")
{$alertPhrase = " ".$alertlevel." Warning ".$alerttype;}
  

  else if ($alertlevel === "none")
  {$alertPhrase = "No Warnings in Force";} 
    
} 
// north america  
/*else if($advisoryzone == "na")
{
$name = $parsed_json["response"][0]["details"]["name"];
$color = $parsed_json["response"][0]["details"]["color"];
$begins = $parsed_json["response"][0]["timestamps"]["beginsISO"];
$expires = $parsed_json["response"][0]["timestamps"]["expiresISO"];
$code = $parsed_json["error"]["code"];
}
//aw alerts
if ($code == "warn_no_data")
  {$alertPhrase = "No Warnings in Force"; $alertlevel = "none";}  
else if ($code == ""){$alertPhrase = $name." ".$alerttype;}

else if($advisoryzone == "eu")
{
$name = $parsed_json['response'][0]['details']['name'];
$type = $parsed_json["response"][0]["details"]["type"];
$alertdesc = substr($type, 0 ,5);
$level = substr($type, -2);
            if ($level == "MD") {
                $background = "yellow";
                $alertlevel = "Yellow Alert";
            } elseif ($level == "SV") {
                $background = "orange";
                $alertlevel = "Orange Alert";
            } elseif ($level == "EX") {
                $background = "red";
                $alertlevel = "Red Alert";
            }
$alerttime = $parsed_json['response'][0]['timestamps']['begins'];
$alertexp = $parsed_json['response'][0]['timestamps']['expires']; 
$alertissued = $parsed_json['response'][0]['timestamps']['issued'];
$warnimage = $parsed_icon[$background][$type];
$alerttype = $parsed_icon['top']['alertdesc'][$alertdesc];

if ($alertlevel != "")
{$alertPhrase = $alertlevel." Warning ".$alerttype." ".$warnimage;}

  else if ($level === "")
  {$alertPhrase = "No Warnings in Force"; $alertlevel = "none";}   

}

else if($advisoryzone == "au")
{
$xml = simplexml_load_file("jsondata/au.txt") or die("Error: Cannot create object");
$jsonData = json_encode($xml, JSON_PRETTY_PRINT);
$parsed_json = json_decode($jsonData, true);
if(($parsed_json["channel"]["title"])!==null){$alertlevel="Yellow";}
else {$alertlevel="none";}
///BOM Warning
if (strpos($alertlevel,'Yellow') !== false)
 {$alertPhrase = $newalertyellow;}  

 
//outlook
  else if ($alertlevel == "none")
  {$alertPhrase = "No Wrnings in Force";}  

}

else ($advisoryzone == "rw")
{$alertPhrase = "No Warnings in Force";}*/

//echo $alertPhrase;
//echo $warnimage;
?>

