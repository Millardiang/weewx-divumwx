<?php 
$xmlString = file_get_contents("jsondata/aurora.txt");
$xml = simplexml_load_string($xmlString);
$json = json_encode($xml);
$array = json_decode($json,TRUE);
$auroraColor = $array["activity"][23]["@attributes"]["status_id"];
$auroraValue = $array["activity"][23]["@attributes"]["value"];
//$auroraValue = 100;
if($auroraColor === 'green'){$auroraBackground='#35FF28';$auroraDescription='No significant activity';$auroraMeaning='Aurora is unlikely to be visible by eye or camera from anywhere in the UK.';}
else if($auroraColor === 'yellow'){$auroraBackground='#FFFF09';$auroraDescription='Minor geomagnetic activity';$auroraMeaning='Aurora may be visible by eye from Scotland and may be visible by camera from Scotland, northern England and Northern Ireland.';}
else if($auroraColor === 'amber'){$auroraBackground='#ff9933';$auroraDescription='Amber alert: possible aurora';$auroraMeaning='Aurora is likely to be visible by eye from Scotland, northern England and Northern Ireland; possibly visible from elsewhere in the UK. Photographs of aurora are likely from anywhere in the UK.';}
else if($auroraColor === 'red'){$auroraBackground='#F30006';$auroraDescription='Red alert: aurora likely';$auroraMeaning='It is likely that aurora will be visible by eye and camera from anywhere in the UK.';}
//$auroraBackground = '#FD8706';
if($auroraBackground !== '#35FF28'){
echo'<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8">
  <title>AuroraWatch UK</title>
<style>
iframe_container {
    position: relative;
    top: 50%;
    left:50%;
    transform: translate(-50%, -50%); 
}
</style>
</head>
<body>
      <section>
      <div class="alertbar" style="margin-bottom:4px;padding-bottom:10px;background-color:'.$auroraBackground.';color:black;border-radius:5px;">
      <div class="alert-text-box" style="padding-left:20px;padding-right:20px;display:flex;margin: 0 auto;">
		<div class="post" style="font-weight:500; font-size:16px; color:black;"><img src="img/auroraWatchBlackText.svg"style="margin-bottom:0px; width:90px;"></br>'.$auroraDescription.' 
			
			<span class="more" style="padding-top:-20px;display:none; font-size:16px;"><p>'.$auroraMeaning.'.</p> <p class="iframe_container" style="text-align:center">
  <iframe position="relative" scrolling="no" frameborder="0" allowtransparency="true" display="block" width="600" height="530" src="https://aurorawatch.lancs.ac.uk/external/rolling_status_text"></iframe>
  </p></span>
            

			<more-button class="read">More</more-button>
		</div></div></div>
	</section>	
</body>
</html>';}
?>

