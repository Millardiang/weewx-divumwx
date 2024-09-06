<?php
include('dvmCombinedData.php');
if($theme=='dark'){$textColor = 'silver';}
elseif($theme=='light'){$textColor = 'rgb(44,44,44)';} 
?>
<!DOCTYPE html>
<html lang="en" >
<link href="css/weather-icons.min.css" rel="stylesheet prefetch">
<link href="css/weather-icons-wind.min.css" rel="stylesheet prefetch">

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
  width: 60%;
  overflow: hidden;
  height: 6rem;
  background-color: transparent;
  padding-left: 40%;
  box-sizing: content-box;
  margin-left: -300px;
}
.ticker-wrap .ticker {
  display: inline-block;
  margin-top: -30px;
  height: 6rem;
  line-height: 6rem;
  white-space: nowrap;
  padding-right: 40%;
  box-sizing: content-box;
  -webkit-animation-iteration-count: infinite;
  animation-iteration-count: infinite;
  -webkit-animation-timing-function: linear;
  animation-timing-function: linear;
  -webkit-animation-name: ticker;
  animation-name: ticker;
  -webkit-animation-duration: 90s;
  animation-duration: 50s;
}
.ticker-wrap .ticker__item {
  display: inline-block;
  padding: 0 0.75rem;
  font-size: 1.5rem;
  font-weight: lighter;
  color: <?php echo $textColor;?>;
}

body {
  padding-bottom: 6rem;
  font-family: IBM Plex Sans, sans-serif;
}

h1, h2, p {
  padding: 0 5%;
}

</style>
<head>
  <meta charset="UTF-8">
  <title></title>
 

</head>
<body>
<!-- partial:index.partial.html -->
<h1></h1>
<h2></h2>

<div class="ticker-wrap">
<div class="ticker">
  <div class="ticker__item"><i class="wi wi-thermometer" style="color: <?php echo $colorOutTemp;?>"></i> <?php echo $temp["outside_now"];?>&deg<?php echo $temp["units"];?></div>
  <div class="ticker__item"><i class="wi wi-humidity" style="color: <?php echo $colorHumidity;?>"></i> <?php echo $humid["now"];?>%</div>
  <div class="ticker__item"><i class="wi wi-barometer" style="color: <?php echo $colorBarometerCurrent;?>"></i> <?php echo $barom["now"];?> <?php echo $barom["units"];?></div>
  <div class="ticker__item"><i class="wi wi-strong-wind" style="color: <?php echo $color["windSpeed"];?>"></i> <?php echo $wind["speed"];?> <?php echo $wind["units"];?></div>
  <div class="ticker__item"><i class="wi wi-strong-wind" style="color: <?php echo $color["windGust"];?>">Gust </i> <?php echo $wind["gust"];?> <?php echo $wind["units"];?></div>
  <div class="ticker__item"><i class="wi wi-umbrella" style="color: <?php echo $colorRainDaySum;?>"></i> <?php echo $rain["current"];?> <?php echo $rain["units"];?></div>
  <div class="ticker__item" style="color: <?php echo $colorUVCurrent;?>">UV <a style="color: <?php echo $textColor;?>"><?php echo $uv["now"];?></a></div>
  <div class="ticker__item"><i class="wi wi-sunrise" style="color: orange"></i> <?php echo $alm["sunrise"];?></div>
  <div class="ticker__item"><i class="wi wi-sunset" style="color: orange"></i> <?php echo $alm["sunset"];?></div>
  <div class="ticker__item"><i class="wi wi-moonrise" style="color: lightblue"></i> <?php echo $alm["moonrise"];?></div>
  <div class="ticker__item"><i class="wi wi-moonset" style="color: lightblue"></i> <?php echo $alm["moonset"];?></div>
  
</div>
</div>

<p>
</p>


</body>
</html>
