<?php
$lang['pollenModule'] = "Pollen Risk | Index";
include('dvmCombinedData.php');
date_default_timezone_set($TZ);
$json_string	= file_get_contents('jsondata/pollen.txt');
$parsed_json	= json_decode($json_string,true);

$pollen["grass_count"] = $parsed_json["data"][0]["Count"]["grass_pollen"];
$pollen["tree_count"] = $parsed_json["data"][0]["Count"]["tree_pollen"];
$pollen["weed_count"] = $parsed_json["data"][0]["Count"]["weed_pollen"];
$pollen["grass_risk"] = $parsed_json["data"][0]["Risk"]["grass_pollen"];
$pollen["tree_risk"] = $parsed_json["data"][0]["Risk"]["tree_pollen"];
$pollen["weed_risk"] = $parsed_json["data"][0]["Risk"]["weed_pollen"];
$pollen["update_time"] = $parsed_json["data"][0]["updatedAt"];

if ($pollen["grass_risk"]=="None"){$pollen["grass_index"]="0";$pollen["grass_color"]="#cecece";}
else if ($pollen["grass_risk"]=="Very Low"){$pollen["grass_index"]="1";$pollen["grass_color"]="#6ae456";}
else if ($pollen["grass_risk"]=="Low"){$pollen["grass_index"]="2";$pollen["grass_color"]="#b4e949";}
else if ($pollen["grass_risk"]=="Moderate"){$pollen["grass_index"]="3";$pollen["grass_color"]="#e3bd40";}
else if ($pollen["grass_risk"]=="High"){$pollen["grass_index"]="4";$pollen["grass_color"]="#e27a2e";}
else if ($pollen["grass_risk"]=="Very High"){$pollen["grass_index"]="5";$pollen["grass_color"]="#ea3323";}

if ($pollen["tree_risk"]=="None"){$pollen["tree_index"]="0";$pollen["tree_color"]="#cecece";}
else if ($pollen["tree_risk"]=="Very Low"){$pollen["tree_index"]="1";$pollen["tree_color"]="#6ae456";}
else if ($pollen["tree_risk"]=="Low"){$pollen["tree_index"]="2";$pollen["tree_color"]="#b4e949";}
else if ($pollen["tree_risk"]=="Moderate"){$pollen["tree_index"]="3";$pollen["tree_color"]="#e3bd40";}
else if ($pollen["tree_risk"]=="High"){$pollen["tree_index"]="4";$pollen["tree_color"]="#e27a2e";}
else if ($pollen["tree_risk"]=="Very High"){$pollen["tree_index"]="5";$pollen["tree_color"]="#ea3323";}

if ($pollen["weed_risk"]=="None"){$pollen["weed_index"]="0";$pollen["weed_color"]="#cecece";}
else if ($pollen["weed_risk"]=="Very Low"){$pollen["weed_index"]="1";$pollen["weed_color"]="#6ae456";}
else if ($pollen["weed_risk"]=="Low"){$pollen["weed_index"]="2";$pollen["weed_color"]="#b4e949";}
else if ($pollen["weed_risk"]=="Moderate"){$pollen["weed_index"]="3";$pollen["weed_color"]="#e3bd40";}
else if ($pollen["weed_risk"]=="High"){$pollen["weed_index"]="4";$pollen["weed_color"]="#e27a2e";}
else if ($pollen["weed_risk"]=="Very High"){$pollen["weed_index"]="5";$pollen["weed_color"]="#ea3323";}

?>

    <div class="chartforecast2">
        <!--span class="yearpopup"><a alt="Pollen Data" title="Pollen Data" href="dvmPollenPopup.php" data-lity><?php echo $chartinfo;?> Pollen Data</a></span-->
    </div>
    <span class='moduletitle2'><?php echo $lang['pollenModule']; ?></valuetitleunit></span>


<div class= "updatedtime1"<span><?php if(file_exists('jsondata/pollen.txt')&&time()- filemtime('jsondata/pollen.txt')>1800)echo $offline. '<offline> Offline </offline>';else echo $online," ",date($timeFormat, filemtime('jsondata/pollen.txt'));?></span></div>


    