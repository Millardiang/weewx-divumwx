<?php 
include('dvmCombinedData.php');
?>

<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>High Chart Gauge</title>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=yes">


    
<style>
#container {
  height: 400px;
}

.highcharts-figure,
.highcharts-data-table table {
  min-width: 310px;
  max-width: 500px;
  margin: 1em auto;
}

.highcharts-data-table table {
  font-family: Verdana, sans-serif;
  border-collapse: collapse;
  border: 1px solid #ebebeb;
  margin: 10px auto;
  text-align: center;
  width: 100%;
  max-width: 500px;
}

.highcharts-data-table caption {
  padding: 1em 0;
  font-size: 1.2em;
  color: #555;
}

.highcharts-data-table th {
  font-weight: 600;
  padding: 0.5em;
}

.highcharts-data-table td,
.highcharts-data-table th,
.highcharts-data-table caption {
  padding: 0.5em;
}

.highcharts-data-table thead tr,
.highcharts-data-table tr:nth-child(even) {
  background: #f8f8f8;
}

.highcharts-data-table tr:hover {
  background: #f1f7ff;
}
</style>

</head>
<body>

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/highcharts-more.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>

<figure class="highcharts-figure">
  <div id="container"></div>

</figure>

<!--div id="sundial" width="130" height="130" style="position:relative; top: -8.5px; left: 76.75px; border:0px solid #2E8B57;"></div-->

<script>
Highcharts.chart('container', {

  chart: {
    type: 'gauge',
    alignTicks: false,
    plotBackgroundColor: null,
    plotBackgroundImage: null,
    plotBorderWidth: 0,
    plotShadow: false
  },

  title: {
    text: ''
  },

  pane: {
    startAngle: -150,
    endAngle: 150
  },

  yAxis: [{
    min: 0,
    max: 200,
    lineColor: '#339',
    tickColor: '#339',
    minorTickColor: '#339',
    offset: -25,
    lineWidth: 2,
    labels: {
      distance: -20,
      rotation: 'auto'
    },
    tickLength: 5,
    minorTickLength: 5,
    endOnTick: false
  }, {
    min: 0,
    max: 124,
    tickPosition: 'outside',
    lineColor: '#933',
    lineWidth: 2,
    minorTickPosition: 'outside',
    tickColor: '#933',
    minorTickColor: '#933',
    tickLength: 5,
    minorTickLength: 5,
    labels: {
      distance: 12,
      rotation: 'auto'
    },
    offset: -20,
    endOnTick: false
  }],

  series: [{
    name: 'Speed',
    data: [80],
    dataLabels: {
      formatter: function () {
        var kmh = this.y,
          mph = Math.round(kmh * 0.621);
        return '<span style="color:#339">' + kmh + ' km/h</span><br/>' +
          '<span style="color:#933">' + mph + ' mph</span>';
      },
      backgroundColor: {
        linearGradient: {
          x1: 0,
          y1: 0,
          x2: 0,
          y2: 1
        },
        stops: [
          [0, '#DDD'],
          [1, '#FFF']
        ]
      }
    },
    tooltip: {
      valueSuffix: ' km/h'
    }
  }]

},
// Add some life
function (chart) {
  setInterval(function () {
    if (chart.axes) { // not destroyed
      var point = chart.series[0].points[0],
        newVal,
        inc = Math.round((Math.random() - 0.5) * 20);

      newVal = point.y + inc;
      if (newVal < 0 || newVal > 200) {
        newVal = point.y - inc;
      }

      point.update(newVal);
    }
  }, 3000);

});

</script>

</body>
</html>
