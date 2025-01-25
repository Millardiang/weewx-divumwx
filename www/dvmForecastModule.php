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
<style>
.forecast4 {
  position: absolute;
  font-family: arial, system;
  z-index: 20;
  padding-top: 1px;
  margin-left: 0;
  font-size: .67em;
  color: var(--col-6);
  margin-top: 159px;
  width: 300px;
  padding-left: 10px;
  text-align: left;
}
.forecast4:hover {
  color: #90b12a
}
</style>
<?php 
include_once('dvmCombinedData.php');
$iconset = "icon2";
error_reporting(0); date_default_timezone_set($TZ);
header('Content-type: text/html; charset=UTF-8');
if ($windunit=='kts'){$windunit="kts";}
$jsonfile="jsondata/awd.txt";if(!file_exists($jsonfile)) {return;}

?>
    <div class="forecast4">
      <span class="yearpopup"><a alt="Forecast Menu" title="Forecast Menu" href="dvmForecastHourlyPopup.php" data-lity><?php echo $chartinfo;?> Forecasts and Meteogram</a></span>
      
</div>
    <span class='moduletitle'><?php echo $lang['forecastModule'];?> (<valuetitleunit>&deg;<?php echo $temp["units"];?></valuetitleunit>)</span>


<div class="updatedtime1"><?php $forecastime=filemtime('jsondata/awd.txt');

$forecasturl = file_get_contents("jsondata/awd.txt");
if(filesize('jsondata/awd.txt')<1){echo "".$offline. "";}
else echo $online,"";echo " ",	date($timeFormat,$forecastime);	?></div>



<div class="aerisforecasthome" ><div class="aerisdiv">
<?php //begin ad stuff 
$jsonIcon = 'jsondata/lookupTable.json';
$jsonIcon = file_get_contents($jsonIcon);
$parsed_icon = json_decode($jsonIcon, true);
$forecasturl=file_get_contents($jsonfile);
$parsed_forecastjson = json_decode($forecasturl,true);
$wucount = 0;
for ($k=0;$k<=2;$k++) 
{
     $pngicon[$k] = $parsed_forecastjson['response'][0]['periods'][$k]['icon'];
     $forecastIcon[$k] = $parsed_icon[$pngicon[$k]][$iconset];
     $Time[$k] = date("H", $parsed_forecastjson['response'][0]['periods'][$k]['timestamp']);
     if($Time[0] ==="07"){$forecastdayTime[0] = "Today"; $forecastdayTime[1] = "Tonight"; $forecastdayTime[2] = "Tomorrow";}
	 else if($Time[0] ==="19"){$forecastdayTime[0] = "Tonight"; $forecastdayTime[1] = "Tomorrow"; $forecastdayTime[2] = "Tomorrow Night";}
     $forecastdayTempHigh = $parsed_forecastjson['response'][0]['periods'][$k]['maxTempC'];
     $forecastdayTempLow = $parsed_forecastjson['response'][0]['periods'][$k]['minTempC'];
     $forecastHumidity = $parsed_forecastjson['response'][0]['periods'][$k]['humidity'].'%';
     if($forecastdayTempHigh ===null){$forecastdayTempHigh = $forecastdayTempLow;}
     if ($windunit == 'kts') {
     $forecastdayWindGust = $parsed_forecastjson['response'][0]['periods'][$k]['windSpeedMaxKTS'];   
     }else {
     $forecastdayWindGust = $parsed_forecastjson['response'][0]['periods'][$k]['windSpeedMaxKPH'];
    }
     //$forecastdayWinddir = $parsed_forecastjson->{'daypart'}[0]->{'windDirection'}[$k];
	 $forecastdayWinddircardinal = $parsed_forecastjson['response'][0]['periods'][$k]['windDir']; 
     //$forecastdayacumm = $parsed_forecastjson->{'daypart'}[0]->{'snowRange'}[$k];
	 //$forecastdayPrecipType = $parsed_forecastjson->{'daypart'}[0]->{'precipType'}[$k];
     $forecastdayprecipIntensity = $parsed_forecastjson['response'][0]['periods'][$k]['precipMM'];
	 $forecastdayPrecipProb = $parsed_forecastjson['response'][0]['periods'][$k]['pop'];
     $forecastdayUV = $parsed_forecastjson['response'][0]['periods'][$k]['uvi'];
	 //$forecastdayUVdesc = $parsed_forecastjson->{'daypart'}[0]->{'uvDescription'}[$k];
     //$forecastdaysnow = $parsed_forecastjson->{'daypart'}[0]->{'qpfSnow'}[$k];
	 $forecastdaysummary = $parsed_forecastjson['response'][0]['periods'][$k]['weatherPrimary'];
     $daynight = $parsed_forecastjson['response'][0]['periods'][$k]['isDay'];
     if ($daynight !== false)
    {
        $forecastdaynight = "D";
    }
    else $forecastdaynight = "N";
	//metric to F
	//aw convert temps-rain
    //metric to F
    if ($tempunit == 'F')
    {
        $forecastdayTempHigh = round(($forecastdayTempHigh * 9 / 5) + 32, 0);
    }

    //heatindex
    if ($tempunit == 'F')
    {
        $aerisheatindex = ($aerisheatindex * 9 / 5) + 32;
    }

    //rain inches to mm
    if ($rainunit == 'in')
    {
        $forecastdayprecipIntensity = $forecastdayprecipIntensity * 0.0393701;
    }

    //kmh to ms
    if ($windunit == 'm/s')
    {
        $forecastdayWindGust = round((number_format($forecastdayWindGust, 1) * 0.277778) , 0);
        $forecastdayWindSpeed = round((number_format($forecastdayWindSpeed, 1) * 0.277778) , 0);
    }
    //kmh to mph
    if ($windunit == 'mph')
    {
        $forecastdayWindGust = round((number_format($forecastdayWindGust, 1) * 0.621371) , 0);
        $forecastdayWindSpeed = round((number_format($forecastdayWindSpeed, 1) * 0.621371) , 0);
    }   
    //convert lightning index shorter phrases
	if ( $forecastthunder==0 ){$forecastthunder='';}else if ( $forecastthunder==1 ){$forecastthunder=$lightningalert4.' Thunder Risk';}else if ( $forecastthunder==2 ){$forecastthunder=$lightningalert4.' Thunder';}else if ( $forecastthunder>=3 ){$forecastthunder=$lightningalert4.' Severe Tstorm';}	
	//icon + day
	echo '<div class="aerisforecastinghome" style="border:0px">';echo '<div class="aerisweekdayhome">'.$forecastdayTime[$k].'</div><div class=aerishomeicons>';
	echo '<img src="img/meteocons/' . $forecastIcon[$k] . '" width="45%" ></img>';	
	echo '</div><div class="aeristempdesc" style="font-size: 0.53rem"}>'.$forecastdaysummary.'</div>';
	//temp non metric
	if($tempunit=='F' && $forecastdayTempHigh<44.6){echo '<aeristemphihome><bluet>'.number_format($forecastdayTempHigh,0).'°'.$tempunit.'</bluet></aeristemphihome>';}
	else if($tempunit=='F' && $forecastdayTempHigh>104){echo '<aeristemphihome><purplet>'.number_format($forecastdayTempHigh,0).'°'.$tempunit.'</purplet></aeristemphihome>';}
	else if($tempunit=='F' && $forecastdayTempHigh>80.6){echo '<aeristemphihome><redt>'.number_format($forecastdayTempHigh,0).'°'.$tempunit.'</redt></aeristemphihome>';}
	else if($tempunit=='F' && $forecastdayTempHigh>64.4){echo '<aeristemphihome><oranget>'.number_format($forecastdayTempHigh,0).'°'.$tempunit.'</oranget></aeristemphihome>';}
	else if($tempunit=='F' && $forecastdayTempHigh>55){echo '<aeristemphihome><yellowt>'.number_format($forecastdayTempHigh,0).'°'.$tempunit.'</yellowt></aeristemphihome>';}
	else if($tempunit=='F' && $forecastdayTempHigh>=44.6){echo '<aeristemphihome><greent>'.number_format($forecastdayTempHigh,0).'°'.$tempunit.'</greent></aeristemphihome>';}
	//temp metric
	else if($forecastdayTempHigh<7){echo '<aeristemphihome><bluet>'.number_format($forecastdayTempHigh,0).'°'.$tempunit.'</bluet></aeristemphihome>';}
	else if($forecastdayTempHigh>40){echo '<aeristemphihome><purplet>'.number_format($forecastdayTempHigh,0).'°'.$tempunit.'</purplet></aeristemphihome>';}
	else if($forecastdayTempHigh>27){echo '<aeristemphihome><redt>'.number_format($forecastdayTempHigh,0).'°'.$tempunit.'</redt></aeristemphihome>';}
	else if($forecastdayTempHigh>18){echo '<aeristemphihome><oranget>'.number_format($forecastdayTempHigh,0).'°'.$tempunit.'</oranget></aeristemphihome>';}
	else if($forecastdayTempHigh>12.7){echo '<aeristemphihome><yellowt>'.number_format($forecastdayTempHigh,0).'°'.$tempunit.'</yellowt></aeristemphihome>';}
	else if($forecastdayTempHigh>=7){echo '<aeristemphihome><greent>'.number_format($forecastdayTempHigh,0).'°'.$tempunit.'</greent></aeristemphihome>';}
	//wind
	echo "<div class='aeriswindspeedicon'>";
	echo $windalert2." ".$forecastdayWinddircardinal; 
	echo " ".number_format($forecastdayWindGust,0)," <valuewindunit>".$windunit;echo  '</div>';'<br>';
	//snow
	if ( $forecastdaysnow>0 && $rainunit=='in'){ echo '<precip>'.$snowflakesvg.'&nbsp;<aeristempwindhome><span><oblue>&nbsp;'.$forecastdaysnow.'</oblue><valuewindunit> in</valuewindunit></aeriswindhome></span></precip>';}
	else if ( $forecastdaysnow>0 && $rainunit=='mm'){ echo '<precip>'.$snowflakesvg.'&nbsp;<aeristempwindhome><span><oblue>&nbsp;'.$forecastdaysnow.'</oblue><valuewindunit> cm</valuewindunit></aeriswindhome></span></precip>';}
	
	
	//rain
	else if ($forecastdayPrecipType='rain' && $rainunit=='in'){echo '<precip>'.$rainsvg.'&nbsp;<aeristempwindhome><span><oblue>&nbsp;'. number_format($forecastdayprecipIntensity,2).'</oblue>&nbsp;<valuewindunit>'.$rainunit.'</valuewindunit></aeriswindhome></span></precip>';}
	else if ($forecastdayPrecipType='rain' && $rainunit=='mm'){echo '<precip>'.$rainsvg.'&nbsp;<aeristempwindhome><span><oblue>&nbsp;'. number_format($forecastdayprecipIntensity,2).'</oblue>&nbsp;<valuewindunit>'.$rainunit.'</valuewindunit></aeriswindhome></span></precip>';}
	//uvi
if ($forecastdaynight=='D'){echo '<br><wuuvicon>&#9788;</wuuvicon>&nbsp;<aeristemplohome><uv>UVI <uvspan>';if ($forecastdayUV>=10){echo "<purpleu>".$forecastdayUV. '</purpleu><greyu> '.$forecastdayUVdesc;}else  if ($forecastdayUV>=7){echo "<redu>".$forecastdayUV. '</redu><greyu> '.$forecastdayUVdesc;}else if ($forecastdayUV>5){ echo "<orangeu>".$forecastdayUV. '</orangeu><greyu> '.$forecastdayUVdesc;}else if ($forecastdayUV>2){  echo "<yellowu>".$forecastdayUV. '</yellowu><greyu> '.$forecastdayUVdesc;}else if ($forecastdayUV>=0){ echo "<greenu>".$forecastdayUV. '</greenu><greyu> '.$forecastdayUVdesc;}echo '</uvspan></uv>';}
else if ($forecastdaynight=='N'){echo '<br><blueu>'.$humidity.'&nbsp;<aeristemplohome><uv>Hum <uvspan>'.$forecastHumidity. '</blueu>';}
	//lightning
	echo '<thunder>'.$forecastthunder;echo '</aeristemplohome></div>';
} // end for loop for icons
?>
</div></div></div>