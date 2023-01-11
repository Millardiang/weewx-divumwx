<?php include('dvmCombinedData.php');date_default_timezone_set($TZ);?>
<div class="updatedtime"><span><?php if(file_exists($livedata)&&time()- filemtime($livedata)>300)echo $offline. '<offline> Offline </offline>';else echo $online." ".$weather["time"];?></div>

<div class="simsekcontainer">
<div class="simsekdata">Strikes</div> 
<?php
// Detected Lightning Last 3 Hours 
echo '<div class=simsek>'.$lightning['strike_count_last_3hr'];?></div>

<div class="simsektoday"><valuetext>Last 3 Hrs</valuetext></div>
</div>

<div class="lightninginfo">Strikes Recorded
<?php 
// Lightning Month Current
echo "<lightningannualx>".date('F Y').":<orange> " .str_replace(",","",$lightning["month_strike_count"])." </orange></lightningannual>";?>
<?php  
// Lightning Year Current
echo "<lightningannualx1> Total ".date('Y').":<orange> " .str_replace(",","",$lightning["year_strike_count"])." </orange>";?>
<?php  
// Last Strike Detected
echo "<timeago>Last Strike Detected<br> <agolightning><orange>".date('jS M H:i',$lightning["last_strike_time"])." </orange> ";?></div>

<div class="rainconverter">
<?php
// Last Distance Detected
if ($windunit == 'mph'){ echo "<div class=tempconvertercircleyellow><orange> " .number_format($lightning["last_distance"] * 0.621371,1)."</orange><smallrainunit>&nbsp; miles</smallrainunit>";
} else { echo "<div class=tempconvertercircleyellow><orange> " .number_format($lightning["last_distance"],0). "</orange><smallrainunit>&nbsp; km</smallrainunit>";}
?></div>
<div class="lightningiconx">
<?php if ($lightning['strike_count_last_3hr'] > 0) echo '<img src="img/lightningalert.svg" width="20" height="20" align="right"/>';?>
</div>
</div>
