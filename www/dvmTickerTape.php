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
<style>
* {
  box-sizing: border-box;
}

@-webkit-keyframes ticker {
  0% {
    -webkit-transform: translate3d(0, 0, 0);
    transform: translate3d(0, 0, 0);
    visibility: visible;
  }
  100% {
    -webkit-transform: translate3d(-100%, 0, 0);
    transform: translate3d(-100%, 0, 0);
  }
}
@keyframes ticker {
  0% {
    -webkit-transform: translate3d(0, 0, 0);
    transform: translate3d(0, 0, 0);
    visibility: visible;
  }
  100% {
    -webkit-transform: translate3d(-100%, 0, 0);
    transform: translate3d(-100%, 0, 0);
  }
}
.ticker-wrap {
  position: relative;
  bottom: 0;
  width: 100%;
  overflow: hidden;
  height: 4rem;
  background-color: transparent;
  padding-left: 100%;
  box-sizing: content-box;
}
.ticker-wrap .ticker {
  display: inline-block;
  height: 4rem;
  line-height: 4rem;
  white-space: nowrap;
  padding-right: 100%;
  box-sizing: content-box;
  -webkit-animation-iteration-count: infinite;
  animation-iteration-count: infinite;
  -webkit-animation-timing-function: linear;
  animation-timing-function: linear;
  -webkit-animation-name: ticker;
  animation-name: ticker;
  -webkit-animation-duration: 45s;
  animation-duration: 45s;
}
.ticker-wrap .ticker__item {
  display: inline-block;
  padding: 0 2rem;
  font-size: 1rem;
  color: silver;
}

body {
  padding-bottom: 5rem;
  font-family: Helvetica;
}

h1, h2, p {
  padding: 0 5%;
}

</style>
<!doctype html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link href="css/weather-icons.min.css" rel="stylesheet prefetch">
    <title>Ticker Tape</title>
</head>
<body>
      
<div class="title"><?php echo $info;?><?php echo $lang['weather_live'];?></div> 

<div style="margin-left -5px;">
<iframe  src="dvmTickerTop.php"frameborder="0" scrolling="no" width="99.5%" height="800%"></iframe> 


  </div>


</body>
</html>

