<?php include('dvmCombinedData.php');error_reporting(0);

if($dayPartCivil == "night"){$webcamurl = "img/nightTime.svg";$lang['webcamModule']="Timelapse";}


?>
<style>
.webcam{
-webkit-border-radius:4px;	-moz-border-radius:4px;	-o-border-radius:4px;	-ms-border-radius:4px;border-radius:4px;width:275px;height:145px;margin:1px;}
</style>

<?php $file_headers = @get_headers($webcamurl); ?>

    <div class="chartforecast2">
<span class="yearpopup"><a alt="cloud radar" title="Cloud Radar" href="dvmCloudSatPopup.php" data-lity><?php echo $webcamicon;?>Cloud Cover Satellite Images Day | Night</a></span>;

<?php if($dayPartCivil == "day"){

       echo '<span class="yearpopup"><a alt="webcam" title="Webcam" href="dvmTimelapsePopup.php" data-lity>'. $webcamicon.'Timelapse</a></span>';
}?>
    </div>
    <span class='moduletitle2'><?php echo $lang['webcamModule'];?></span>


<div class="updatedtime1"><span>
<?php 
if ($dayPartCivil == "night") {
  echo $online. ' <online>'.$alm["civil_twilight_end"].':00</online>';
}
else if($file_headers && $file_headers[0] != 'HTTP/1.1 404 Not Found') {
  echo $online.' '.date($timeFormat);
} else if (file_exists($webcamurl)&&time()- filemtime($webcamurl)<300) {
  echo $online. ' <online>'.date($timeFormat,filemtime($webcamurl)).'</online>';
} 
else {
  echo $offline. '<offline> Offline </offline>';
}
?>
</span></div>
<!-- HOMEWEATHER STATION TEMPLATE SIMPLE WEBCAM -add your url as shown below do NOT delete the class='webcam' !!! -->
<a><img src="<?php echo $webcamurl?>?v=<?php echo date('YmdGis');?>>" alt="weathercam" class="webcam" href="dvmTimelapsePopup.php" data-lity></a>
</span>