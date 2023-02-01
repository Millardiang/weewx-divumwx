<?php include('dvmCombinedData.php');error_reporting(0);

//$webcamurl = "https://webcams.windy.com/webcams/public/embed/player/1643100141/day?autoresize=1";

$webcamurl = "img/picam.jpg";

?>
<style>
.webcam{
-webkit-border-radius:4px;	-moz-border-radius:4px;	-o-border-radius:4px;	-ms-border-radius:4px;border-radius:4px;border:solid RGBA(84, 85, 86, 1.00) 2px;width:275px;height:145px;margin:4px;}
</style>

<?php $file_headers = @get_headers($webcamurl); ?>

    <div class="chartforecast2">
       <span class="yearpopup"><a alt="aquinfo" title="Webcam" href="dvmWebcamPopup.php" data-lity><?php echo $webcamicon;?> Webcam</a></span>
    </div>
    <span class='moduletitle2'><?php echo $lang['webcamModule'];?></span>


<div class="updatedtime1"><span>
<?php if($file_headers && $file_headers[0] != 'HTTP/1.1 404 Not Found') {
  echo $online.' '.date($timeFormat);
} else if (file_exists($webcamurl)&&time()- filemtime($webcamurl)<300) {
  echo $online. ' <online>'.date($timeFormat,filemtime($webcamurl)).'</online>';
} else {
  echo $offline. '<offline> Offline </offline>';
}
?>
</span></div>
<!-- HOMEWEATHER STATION TEMPLATE SIMPLE WEBCAM -add your url as shown below do NOT delete the class='webcam' !!! -->
<a href="dvmWebcamPopup.php" data-lity><img src="<?php echo $webcamurl?>?v=<?php echo date('YmdGis');?>>" alt="weathercam" class="webcam"></a>
</span>