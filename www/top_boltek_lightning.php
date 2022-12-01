<?php
include('dvmCombinedData.php');
date_default_timezone_set($TZ);
header('Content-type: text/html; charset=utf-8');
error_reporting(0);
?>

<div class="wfstrike">
<?php
// Detected Lightning Last Hour
echo "<wfstriketoday>",$lightning["hour_strike_count"];?>
 </wfstriketoday></div>
<div class="minwordl">Strikes</div></div>
<div class="mintimedatex"><value>&nbsp;Last Hour<value></div>

<div class='wflaststrike'>
<?php
// Last Detected Strike Distance 
if ($windunit == 'mph'){echo "<spanfeelstitle>Last Distance @<orange> " .number_format($lightning["last_distance"] * 0.621371,1). " </orange>miles";
} else { echo "<spanfeelstitle>Last Distance @<orange> " .number_format($lightning["last_distance"],0). " </orange>km";
}
?><br>
<?php 
// Alltime Strike count from weewx database
echo "<spanfeelstitle>All-time Strike Total: <orange> ".$lightning["alltime_strike_count"]." </orange> ";?><br>  
<?php
// Date and Time Last Detected Strike
echo "<spanfeelstitle>Last Strike: <orange> ".date('jS M H:i',$lightning["last_strike_time"])." </orange> ";?>
</div>  
<div class="lightningicon">
<?php
 // display an icon when strike(s) are detected
if ($lightning['strike_count_last_3hr'] > 0) echo '<img src="img/lightningalert.svg" width="20" height="20" align="right"/>';?>
</div>

