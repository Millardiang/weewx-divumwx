<style>
.moonmodulepos {
  position: absolute;
  top: 16px;
  left: 110px
}
.svgpos {
  position: absolute;
  top: -14px;
  left: -25px
}
</style>
<?php 
include('weather34skydata.php');
include('common.php');
include('settings1.php');
header('Content-type: text/html; charset=utf-8');
$json = 'jsondata/dvmSkyData.json';
$json = file_get_contents($json);
$adata = json_decode($json, true);
$alm["moonrise"] = $adata["almanac"]["moon rise"]["at"];
$alm["moonset"] = $adata["almanac"]["moon set"]["at"];
$alm["moonphase"] = $adata["almanac"]["moon phase"]["value"];
$alm["moonphase_no"] = $adata["almanac"]["moon phase number"]["value"];
$alm["luminance"] = round($adata["almanac"]["moon fullness"]["value"],2);
$alm["luminance2"] = round($adata["almanac"]["moon fullness"]["value"],0);
$alm["fullmoon"] = $adata["almanac"]["full moon"]["at"];
$alm["newmoon"] = $adata["almanac"]["new moon"]["at"];
if($alm["moonphase_no"]>4){$waxwan = "wan";}
else{$waxwan = "wax";}
  
$moonimg = "img/moon-1.jpg"; 
if ($theme === "dark")
{$circleborder = "rgb(30,32,36";}
else if ($theme === "light")
{$circleborder = "white";}  
?>
<?php 
$meteor_default="No Meteor Showers";
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 1, 1),"event_title"=>"Quadrantids","event_end"=>mktime(23, 59, 59, 1, 2),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 1, 3),"event_title"=>"Quadrantids peak","event_end"=>mktime(23, 59, 59, 1, 4),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 1, 5),"event_title"=>"Quadrantids","event_end"=>mktime(23, 59, 59, 1, 12),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 4, 9),"event_title"=>"Approaching Lyrids","event_end"=>mktime(23, 59, 59, 4, 20),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 4, 21),"event_title"=>"Lyrids peak","event_end"=>mktime(23, 59, 59, 4, 22),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 5, 5),"event_title"=>"ETA Aquarids","event_end"=>mktime(23, 59, 59, 5, 6),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 7, 20),"event_title"=>"Approaching Delta Aquarids","event_end"=>mktime(23, 59, 59, 7, 27),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 7, 28),"event_title"=>"Delta Aquarids peak","event_end"=>mktime(23, 59, 59, 7, 29),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 8, 1),"event_title"=>"Perseids Aug 1st-24th","event_end"=>mktime(23, 59, 59, 8, 10),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 8, 11),"event_title"=>"Perseids peak","event_end"=>mktime(23, 59, 59, 8, 13),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 8, 14),"event_title"=>"Perseids passed","event_end"=>mktime(23, 59, 59, 8, 18),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 10, 7),"event_title"=>"Draconids peak","event_end"=>mktime(23, 59, 59, 10, 7),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 10, 20),"event_title"=>"Orionids peak","event_end"=>mktime(23, 59, 59, 10, 21),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 11, 4),"event_title"=>"South Taurids peak","event_end"=>mktime(23, 59, 59, 11, 5),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 11, 11),"event_title"=>"North Taurids peak","event_end"=>mktime(23, 59, 59, 11, 11),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 11, 17),"event_title"=>"Leonids peak","event_end"=>mktime(23, 59, 59, 11, 18),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 12, 13),"event_title"=>"Geminids peak","event_end"=>mktime(23, 59, 59, 12, 14),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 12, 17),"event_title"=>"Ursids 17th-25th","event_end"=>mktime(23, 59, 59, 12, 20),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 12, 21),"event_title"=>"Ursids peak","event_end"=>mktime(23, 59, 59, 12, 22),);
$meteor_events[]=array("event_start"=>mktime(0, 0, 0, 12, 23),"event_title"=>"Ursids 17th-25th","event_end"=>mktime(23, 59, 59, 12, 25),);
$meteorNow=time();
$meteorOP=false;
foreach ($meteor_events as $meteor_check) {
    if ($meteor_check["event_start"]<=$meteorNow&&$meteorNow<=$meteor_check["event_end"]) {
        $meteorOP=true;
        $meteor_default=$meteor_check["event_title"];
    }
};?>
<div class="updatedtime1"><span><?php if(file_exists($livedata2)&&time()- filemtime($livedata2)>300)echo $offline. '<offline> Offline </offline>';else echo $online." ".$weather["time"];?></span></div>
<div class="moonphasemoduleposition">
<div class="moonrise1">
<svg id="weather34 moon rise" viewBox="0 0 32 32" width="6" height="6" fill="none" stroke="#01a4b5" stroke-linecap="round" stroke-linejoin="round" stroke-width="10%">    
<path d="M30 20 L16 8 2 20" /></svg>
 <?php echo $lang['Moon'];?> <br /><?php  echo 'Rise<blueu> ' .$alm["moonrise"].'</blueu>';?>

<div class="moonmodulepos">
<div id = "dldata">
<img rel='prefetch' src='<?php echo $moonimg; ?>' width='100px' height='100px' alt='moon image'>

</div></div></div>

<div class="svgpos">
<svg width="160" height="160" viewBox="0 0 160 160">
   <circle cx="80" cy="80" r="60" stroke="<?php echo $circleborder;?>" stroke-width="21" fill="none" />
</svg> 
  </div>

<div class="fullmoon1">
<svg id="weather34 full moon" viewBox="0 0 32 32" width="6" height="6" fill="#aaa" stroke="#aaa" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%"><circle cx="16" cy="16" r="14" /><path d="M6 6 L26 26" /></svg>
<?php echo $lang['Nextfullmoon'];?>	<br /> <div class="nextfullmoon"><value><moonm>
<?php echo $alm["fullmoon"] ;?></value></div>



 </span>
</div>


<div class="newmoon1">
<svg id="weather34 new moon" viewBox="0 0 32 32" width="6" height="6" fill="#777" stroke="#777" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%"><circle cx="16" cy="16" r="14" /> <path d="M6 6 L26 26" /></svg>
<?php echo $lang['Nextnewmoon'];?> <div class="nextnewmoon"><value><moonm>
<?php echo $alm["newmoon"] ;?></value></div>

 </span>
</div>
<div class="moonset1">
<svg id="weather34 moon set" viewBox="0 0 32 32" width="6" height="6" fill="none" stroke="#f26c4f" stroke-linecap="round" stroke-linejoin="round" stroke-width="10%">
    <path d="M30 12 L16 24 2 12" /></svg>
<?php echo $lang['Moon']?><div class="nextnewmoon">
<?php echo 'Set<maxred> '.$alm["moonset"].'</maxred>' ;?></span> 

</div></div>
<div class="meteorshower"><svg xmlns='http://www.w3.org/2000/svg' width='10px' height='10px' viewBox='0 0 16 16'><path fill='currentcolor' d='M0 0l14.527 13.615s.274.382-.081.764c-.355.382-.82.055-.82.055L0 0zm4.315 1.364l11.277 10.368s.274.382-.081.764c-.355.382-.82.055-.82.055L4.315 1.364zm-3.032 2.92l11.278 10.368s.273.382-.082.764c-.355.382-.819.054-.819.054L1.283 4.284zm6.679-1.747l7.88 7.244s.19.267-.058.534-.572.038-.572.038l-7.25-7.816zm-5.68 5.13l7.88 7.244s.19.266-.058.533-.572.038-.572.038l-7.25-7.815zm9.406-3.438l3.597 3.285s.094.125-.029.25c-.122.125-.283.018-.283.018L11.688 4.23zm-7.592 7.04l3.597 3.285s.095.125-.028.25-.283.018-.283.018l-3.286-3.553z'/></svg>
<?php // simple meteor shower output of major shower events // 'Set<orange> ' .$moon_set.'</orange>'
echo $meteor_default;?>
</div>

<?php echo'<div class="weather34moonphasem2">Moon Phase<br>'.$alm["moonphase"].'</div>
<div class="weather34luminancem2">Luminance<br>'.round($alm["luminance"],2).' %</div>';?>
