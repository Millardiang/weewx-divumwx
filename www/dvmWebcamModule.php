<?php
################################################################################################
##        ________   __  ___      ___  ____  ____  ___      ___    __   __  ___  ___  ___     ##
##       |"      "\ |" \|"  \    /"  |("  _||_ " ||"  \    /"  |  |"  |/  \|  "||"  \/"  |    ##
##       (.  ___  :)||  |\   \  //  / |   (  ) : | \   \  //   |  |'  /    \:  | \   \  /     ##
##       |: \   ) |||:  | \\  \/. ./  (:  |  | . ) /\\  \/.    |  |: /'        |  \\  \/      ##
##       (| (___\ |||.  |  \.    //    \\ \__/ // |: \.        |   \//  /\'    |  /\.  \      ##
##       |:       :)/\  |\  \\   /     /\\ __ //\ |.  \    /:  |   /   /  \\   | /  \   \     ##
##       (________/(__\_|_)  \__/     (__________)|___|\__/|___|  |___/    \___||___/\___|    ##
##                                                                                            ##
##     Copyright (C) 2023 Ian Millard, Steven Sheeley, Sean Balfour. All rights reserved      ##
##      Distributed under terms of the GPLv3.  See the file LICENSE.txt for your rights.      ##
##    Issues for weewx-divumwx skin template are only addressed via the issues register at    ##
##                    https://github.com/Millardiang/weewx-divumwx/issues                     ##
################################################################################################ 
include('dvmCombinedData.php');
error_reporting(0);
if($dayPartCivil == "night"){$webcamurl = "img/nightTime.svg";$lang['webcamModule']="Timelapse";}
$file_headers = @get_headers($webcamurl); ?>

    <div class="chartforecast">

<?php if($dayPartCivil == "day"){

       echo '<span class="yearpopup"><a alt="webcam" title="Webcam" href="dvmTimelapsePopup.php" data-lity>'. $webcamicon.'Timelapse</a></span>';
}?>
    </div>
    <span class='moduletitle'><?php echo $lang['webcamModule'];?></span>


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
<a href="dvmTimelapsePopup.php" data-lity><img src="<?php echo $webcamurl?>?v=<?php echo date('YmdGis');?>>" style="border:solid transparent 1px; border-radius:5px;width: 280px;margin-top:1px;" alt="weathercam" class="webcam"></a->
</span>