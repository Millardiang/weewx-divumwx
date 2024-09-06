<?php
include('dvmCombinedData.php');
?>
<!DOCTYPE html>
<html lang="en" >
<link href="css/weather-icons.min.css" rel="stylesheet prefetch">

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
  -webkit-animation-duration: 20s;
  animation-duration: 20s;
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
  <div class="ticker__item"><i class="wi wi-thermometer"></i> <?php echo $temp["outside_now"];?>&deg<?php echo $temp["units"];?></div>
  <div class="ticker__item"><i class="wi wi-barometer"></i> <?php echo $barom["now"];?> <?php echo $barom["units"];?></div>
  <div class="ticker__item"><i class="wi wi-windy"></i> <?php echo $wind["speed"];?> <?php echo $wind["units"];?></div>
  <div class="ticker__item"><i class="wi wi-strong-wind"></i> <?php echo $wind["gust"];?> <?php echo $wind["units"];?></div>
  <div class="ticker__item"><i class="wi wi-raindrops"></i> <?php echo $rain["current"];?> <?php echo $rain["units"];?></div>
  
</div>
</div>

<p>
</p>


</body>
</html>
