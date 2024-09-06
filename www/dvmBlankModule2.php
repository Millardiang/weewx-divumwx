<?php
#####################################################################################################################                                                                                                        #
#                                                                                                                   #
# weewx-divumwx Skin Template maintained by The DivumWX Team                                                        #
#                                                                                                                   #
# Copyright (C) 2023 Ian Millard, Steven Sheeley, Sean Balfour. All rights reserved                                 #
#                                                                                                                   #
# Distributed under terms of the GPLv3. See the file LICENSE.txt for your rights.                                   #
#                                                                                                                   #
# Issues for weewx-divumwx skin template should be addressed to https://github.com/Millardiang/weewx-divumwx/issues # 
#                                                                                                                   #
#####################################################################################################################

include('dvmCombinedData.php');
date_default_timezone_set($TZ);
?>
<!doctype html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link href="css/weather-icons.min.css" rel="stylesheet prefetch">
    <title>Ticker Tape</title>
</head>
<body>
      
<div class="title" style="margin-top: -14px; font-size: 9px;"><?php echo $info;?><?php echo $lang['weather_live'];?></div> 


<iframe  src="dvmTickerTapeFrame.php"frameborder="0" scrolling="no" width="99.5%" height="800%"></iframe> 

  </div>


</body>
</html>

