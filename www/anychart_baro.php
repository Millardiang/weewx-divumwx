<?php 
include('dvmCombinedData.php');
echo "<body style='background-color:#292E35'>";
$baro = $barom["now"];
?>
<!doctype html>
<html>
<html lang="en" >
<head>
  <meta charset="UTF-8">
  <title>Baro gauge</title>
  <!--link rel="stylesheet" href="darkgauge/dist/style.css"-->

  <!--script src="https://cdn.anychart.com/releases/8.11.0/js/anychart-core.min.js" type="text/javascript"></script-->
  <!--script src="https://cdn.anychart.com/releases/8.11.0/js/anychart-circular-gauge.min.js" type="text/javascript"></script-->
</head>

  <style>
    html, body, #container {
      width: 100%;
      height: 100%;
      margin: 0;
      padding: 0;
      overflow: hidden;
    }
  </style>

<body>
<script src="https://cdn.anychart.com/releases/8.11.0/js/anychart-core.min.js"></script>
<script src="https://cdn.anychart.com/releases/8.11.0/js/anychart-circular-gauge.min.js"></script>

<div id="container" width="500" height="400" style="position:relative; top: 25px; left: 130px; border:0px solid #007FFF;"></div>


<script>
anychart.onDocumentReady(function () {
 
  //create data set on our data
  dataSet = anychart.data.set([81, 34.5]);
 
  // chart type
  gauge = anychart.circularGauge();
 
  //set series padding
  gauge.data(dataSet).padding('4%');
 
  //set axis scale settings
  var scale = gauge.axis().scale();
  scale.minimum(0)
      .maximum(100)
      .ticks()
      .interval(10);
 
  //set major axis ticks
  var axis = gauge.axis();
  axis.ticks()
      .enabled(true)
      .fill('white')
      .stroke('#888')
      .type('trapezoid')
      .length(20);
 
  //set minor axis ticks
  axis.minorTicks()
      .enabled(true)
      .fill('white')
      .stroke('#ccc')
      .type('trapezoid')
      .length(10);
 
  //set the look of the bar that presents data
  gauge.bar(0)
      .position('i')
      .fill('#F0673B .9')
      .stroke('#F0673B')
      .radius(65);
 
  // draw chart
  gauge.container('container').draw();
});
</script>

</body>
</html>
