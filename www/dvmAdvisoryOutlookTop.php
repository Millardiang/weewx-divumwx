<?php include('dvmCombinedData.php');error_reporting(0);
$json_string = file_get_contents("jsondata/awa.txt");
$parsed_json = json_decode($json_string, true);

$json_string2 = file_get_contents("jsondata/awp.txt");
$parsed_json2 = json_decode($json_string2, true);
$outlookmet = $parsed_json2["response"][0]["phrases"]["longMET"];
$outlookusa = $parsed_json2["response"][0]["phrases"]["long"];

$json_icon = file_get_contents("jsondata/lookupTable.json");
$parsed_icon = json_decode($json_icon, true); 

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
       else {$uppercasealert="No Alerts"; $alerttype='In Force'; $alertlevel="White"; }
       
       if(strpos($description, "heat") !== false) {$alerttype='Extreme Heat';}
       else if(strpos($description, "wind") !== false) {$alerttype='Wind';}
       else if(strpos($description, "snow") !== false) {$alerttype='Snow';}
       else if(strpos($description, "ice") !== false) {$alerttype='Ice';}
       else if(strpos($description, "fog") !== false) {$alerttype='Fog';}
       else if(strpos($description, "rain") !== false) {$alerttype='Rain';}
       else if(strpos($description, "lightning") !== false) {$alerttype='Lightning';}
       else if(strpos($description, "thunder") !== false) {$alerttype='Thunderstorms';}
       else {$alerttype='none';}

       $warnimage = "css/svg/" . $parsed_icon[$lowercasealert][$alerttype];

?>


<div class="wulargeforecasthome"><div class="wulargediv">
<div class="eqcirclehomeregional"><div class="eqtexthomeregional">

<?php
///MetOffice
if ($alertlevel !== "none")
{echo '<spanelightning><alertadvisory><a alt="Alerts" title="Alerts" href="'.$advisory.'" data-lity><img class="iconpos" src='.$warnimage.' width="100%" ></img></alertadvisory><alertpos><alertvalue>'.$uppercasealert.'<br> '.$alerttype.'</alertvalue></alertpos>
   </spanelightning></div></div></div>';}
  
  //outlook
  else if ($alertlevel === "none")
  {echo '<outlook-panel><p>'.$outlookmet.'</p></outlook-panel></div></div></div>';} 
    
} 
  
else if($advisoryzone == "na")
{
$name = $parsed_json["response"][0]["details"]["name"];
$color = $parsed_json["response"][0]["details"]["color"];
$begins = $parsed_json["response"][0]["timestamps"]["beginsISO"];
$expires = $parsed_json["response"][0]["timestamps"]["expiresISO"];
$code = $parsed_json["error"]["code"];
?>

<div class="wulargeforecasthome"><div class="wulargediv">
<div class="eqcirclehomeregional"><div class="eqtexthomeregional" style="color:<?php echo $color;?>;">
<?php
///aw alerts
if ($code == "warn_no_data")
  {if ($units == "us"){echo '<outlook-panel><p>'.$outlookusa.'</p></outlook-panel></div></div></div>';}
  else {echo '<outlook-panel><p>'.$outlookmet.'</p></outlook-panel></div></div></div>';} 
 }  
else if ($code == "")
{echo '<spanelightning><alertadvisory><a alt="Alerts" title="Alerts" href="'.$advisory.'" data-lity></alertadvisory><alertpos><alertvalue>'.$name.'<br> '.$alerttype.'</alertvalue></alertpos>
   </spanelightning></div></div></div>';}

}

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
?>



<div class="wulargeforecasthome"><div class="wulargediv">
<div class="eqcirclehomeregional"><div class="eqtexthomeregional">
<?php
///aw alerts
if ($alertlevel != "")
{echo '<spanelightning><alertadvisory2><a alt="Alerts" title="Alerts" href="'.$advisory.'" data-lity><img src="css/svg/' . $warnimage . '" width="100%" class="iconpos"></img></alertadvisory2><alertpos><alertvalue>'.$alertlevel.'<br> '.$alerttype.'</alertvalue></alertpos>
   </spanelightning></div></div></div>';}
//outlook
  else if ($level === "")
  {echo '<outlook-panel><p>'.$outlookmet.'</p></outlook-panel></div></div></div>';}   

}

else if($advisoryzone == "au")
{
$xml = simplexml_load_file("jsondata/au.txt") or die("Error: Cannot create object");
$jsonData = json_encode($xml, JSON_PRETTY_PRINT);
$parsed_json = json_decode($jsonData, true);
if(($parsed_json['channel']['item'][0]['title'])!==null){$alertlevel="Yellow";}
else $alertlevel="LightGreen";

?>

<div class="wulargeforecasthome"><div class="wulargediv">
<div class="eqcirclehomeregional"><div class="eqtexthomeregional">

<?php 
///BOM Warning
if (strpos($alertlevel,'Yellow') !== false)
 {echo '<spanelightning><alertadvisory2><a alt="Alerts" title="Alerts" href="'.$advisory.'" data-lity>'.$newalertyellow.'</alertadvisory2><alertvalue>Warning(s)<br>In Force</alertvalue>
   </spanelightning></div></div></div>';}  

 
else if (strpos($alertlevel,'LightGreen') !== false)
  {echo '<spanelightning><alertadvisory><a alt="Alerts" title="Alerts" href="'.$advisory.'" data-lity>'.$newalertgreen.'</alertadvisory><alertvalue> Currently <lightgreen>No Alerts</lightgreen></alertvalue>
  </spanelightning></div></div></div>';} 

}

else if($advisoryzone == "rw")
{
?>

<div class="wulargeforecasthome"><div class="wulargediv">
<div class="eqcirclehomeregional"><div class="eqtexthomeregional">
<?php
 
//outlook
  
  if ($units == "us"){echo '<outlook-panel><p>'.$outlookusa.'</p></outlook-panel></div></div></div>';}
  
  else if ($units !== "us"){echo '<outlook-panel><p>'.$outlookmet.'</p></outlook-panel></div></div></div>';}
}

  //solar eclipse events and no alerts 
  else {echo '<spanelightning><alertvalue>'.$eclipse_default.'</spanelightning></div></div></div>';}


?></noalert></div></div>


