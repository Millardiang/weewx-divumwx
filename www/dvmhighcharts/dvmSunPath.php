<?php
include ('dvmCombinedData.php');
include ('getGeocentricData.php');
echo $sunOutput;
error_reporting(0);
##############################################################################################
#        ________   __  ___      ___  ____  ____  ___      ___    __   __  ___  ___  ___     #
#       |"      "\ |" \|"  \    /"  |("  _||_ " ||"  \    /"  |  |"  |/  \|  "||"  \/"  |    #
#       (.  ___  :)||  |\   \  //  / |   (  ) : | \   \  //   |  |'  /    \:  | \   \  /     #
#       |: \   ) |||:  | \\  \/. ./  (:  |  | . ) /\\  \/.    |  |: /'        |  \\  \/      #
#       (| (___\ |||.  |  \.    //    \\ \__/ // |: \.        |   \//  /\'    |  /\.  \      #
#       |:       :)/\  |\  \\   /     /\\ __ //\ |.  \    /:  |   /   /  \\   | /  \   \     #
#       (________/(__\_|_)  \__/     (__________)|___|\__/|___|  |___/    \___||___/\___|    #
#                                                                                            #
#     Copyright (C) 2023 Ian Millard, Steven Sheeley, Sean Balfour. All rights reserved      #
#      Distributed under terms of the GPLv3.  See the file LICENSE.txt for your rights.      #
#    Issues for weewx-divumwx skin template are only addressed via the issues register at    #
#                    https://github.com/Millardiang/weewx-divumwx/issues                     #
##############################################################################################

//$sun2 = file_get_contents('../jsondata/sun2.txt',true);
//$moon2 = file_get_contents('../jsondata/moon2.txt',true);

if ($theme === "dark") {
    echo '<style>@font-face{font-family: weathertext2; src: url(css/fonts/verbatim-regular.woff) format("woff"), url(fonts/verbatim-regular.woff2) format("woff2"), url(fonts/verbatim-regular.ttf) format("truetype");}html,body{font-size: 13px; font-family: "weathertext2", Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.grid{display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 2fr)); grid-gap: 5px; align-items: stretch; color: white;}.grid > article{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.grid1{display: grid; grid-template-columns: repeat(auto-fill, minmax(100%, 1fr)); grid-gap: 5px; align-items: stretch; color: #f5f7fc; margin-top: 5px;}.grid1 > articlegraph{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; height: 360px;}.divumwxbrowser-footer{flex-basis: auto; height: 35px; background: #ebebeb; background: rgba(56, 56, 60, 1); border-bottom: 0; display: flex; bottom: -20px; width: 97.4%; -webkit-border-bottom-right-radius: 5px; -webkit-border-bottom-left-radius: 5px; -moz-border-radius-bottomright: 5px; -moz-border-radius-bottomleft: 5px; border-bottom-right-radius: 5px; border-bottom-left-radius: 5px;}a{color: white; text-decoration: none;}.divumwxdarkbrowser{position: relative; background: 0; width: 97%; height: 30px; margin: auto; margin-top: -5px; margin-left: 0px; border-top-left-radius: 5px; border-top-right-radius: 5px; padding-top: 10px;}.divumwxdarkbrowser[url]:after{content: attr(url); color: white; font-size: 14px; text-align: center; position: absolute; left: 0; right: 0; top: 0; padding: 4px 15px; margin: 11px 10px 0 auto; font-family: arial; height: 20px;}blue{color: #01a4b4;}orange{color: #009bb4;}orange1{color: rgba(255, 131, 47, 1);}green{color: #aaa;}red{color: #f37867;}red6{color: #d65b4a;}value{color: #fff;}yellow{color: #cc0;}purple{color: #916392;}.windcontainer1{left: 5px; top: 0;}.windtoday,.windtoday10,.windtoday30,.windtoday40,.windtoday60{font-family: weathertext2, Arial, Helvetica, system; width: 4rem; height: 1.25rem; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; font-size: 1rem; padding-top: 5px; color: #fff; border-bottom: 13px solid rgba(56, 56, 60, 1); align-items: center; justify-content: center; text-align: center; border-radius: 3px;}.windcaution,.windtrend{position: absolute; font-size: 1rem;}.windtoday{background: #9aba2f;}.windtoday10{background: rgba(230, 161, 65, 1);}.windtoday30{background: rgba(255, 124, 57, 0.8);}.windtoday40{background: rgba(255, 124, 57, 0.8);}.windtoday60{background: rgba(211, 93, 78, 1);}.windcaution{margin-left: 120px; margin-top: 112px; font-family: Arial, Helvetica, system;}.windtrend{margin-left: 135px; margin-top: 48px; z-index: 1; color: #fff;}smalluvunit{font-size: 0.55rem; font-family: Arial, Helvetica, system;}.almanac{font-size: 1.25em; margin-top: 30px; color: rgba(56, 56, 60, 1); width: 12em;}metricsblue{color: #44a6b5; font-family: "weathertext2", Helvetica, Arial, sans-serif; background: rgba(86, 95, 103, 0.5); -webkit-border-radius: 2px; border-radius: 2px; align-items: center; justify-content: center; font-size: 0.9em; left: 10px; padding: 0 3px 0 3px;}.dvmconvertrain{position: relative; font-size: 0.7em; top: 4px; color: white; margin-left: 5px; margin-top: -2px;}.lotemp{color: white; font-size: 0.65rem;}.hitempy{position: relative; background: 0; color: white; width: 70px; padding: 1px; -webit-border-radius: 2px; border-radius: 2px; margin-top: -34px; margin-left: 52px; padding-left: 3px; line-height: 11px; font-size: 9px;}.actualt{position: relative; left: 0px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: #555555; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: max-content; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: white; align-items: center; justify-content: center; margin-bottom: 10px; top: 0; text-align: center;}.actualw{position: relative; left: 5px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: rgba(74, 99, 111, 0.1); padding: 5px; font-family: Arial, Helvetica, sans-serif; width: max-content; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: #aaa; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}.svgimage{background: rgba(0, 155, 171, 1); -webit-border-radius: 2px; border-radius: 2px;}.actual{position: relative; left: 5px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: 95%; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: #aaa; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}
    </style>';
} elseif ($theme === "light") {
    echo '<style>@font-face{font-family: weathertext2; src: url(css/fonts/verbatim-regular.woff) format("woff"), url(fonts/verbatim-regular.woff2) format("woff2"), url(fonts/verbatim-regular.ttf) format("truetype");}html,body{font-size: 13px; font-family: "weathertext2", Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; background-color: white;}.grid{display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 2fr)); grid-gap: 5px; align-items: stretch; color: #f5f7fc;}.grid > article{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.grid1{display: grid; grid-template-columns: repeat(auto-fill, minmax(100%, 1fr)); grid-gap: 5px; align-items: stretch; color: #f5f7fc; margin-top: 5px;}.grid1 > articlegraph{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; height: 360px;}.divumwxbrowser-footer{flex-basis: auto; height: 35px; background: #ebebeb; background: rgba(56, 56, 60, 1); border-bottom: 0; display: flex; bottom: -20px; width: 97.4%; -webkit-border-bottom-right-radius: 5px; -webkit-border-bottom-left-radius: 5px; -moz-border-radius-bottomright: 5px; -moz-border-radius-bottomleft: 5px; border-bottom-right-radius: 5px; border-bottom-left-radius: 5px;}a{color: black; text-decoration: none;}.divumwxdarkbrowser{position: relative; background: 0; width: 97%; height: 30px; margin: auto; margin-top: -5px; margin-left: 0px; border-top-left-radius: 5px; border-top-right-radius: 5px; padding-top: 10px;}.divumwxdarkbrowser[url]:after{content: attr(url); color: black; font-size: 14px; text-align: center; position: absolute; left: 0; right: 0; top: 0; padding: 4px 15px; margin: 11px 10px 0 auto; font-family: arial; height: 20px;}blue{color: #01a4b4;}orange{color: #009bb4;}orange1{color: rgba(255, 131, 47, 1);}green{color: #aaa;}red{color: #f37867;}red6{color: #d65b4a;}value{color: #fff;}yellow{color: #cc0;}purple{color: #916392;}.windcontainer1{left: 5px; top: 0;}.windtoday,.windtoday10,.windtoday30,.windtoday40,.windtoday60{font-family: weathertext2, Arial, Helvetica, system; width: 4rem; height: 1.25rem; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; font-size: 1rem; padding-top: 5px; color: white; border-bottom: 14px solid #555555; align-items: center; justify-content: center; text-align: center; border-radius: 3px;}.windcaution,.windtrend{position: absolute; font-size: 1rem;}.windtoday{background: #9aba2f;}.windtoday10{background: rgba(230, 161, 65, 1);}.windtoday30{background: rgba(255, 124, 57, 0.8);}.windtoday40{background: rgba(255, 124, 57, 0.8);}.windtoday60{background: rgba(211, 93, 78, 1);}.windcaution{margin-left: 120px; margin-top: 112px; font-family: Arial, Helvetica, system;}.windtrend{margin-left: 135px; margin-top: 48px; z-index: 1; color: #fff;}smalluvunit{font-size: 0.55rem; font-family: Arial, Helvetica, system;}.almanac{font-size: 1.25em; margin-top: 30px; color: rgba(56, 56, 60, 1); width: 12em;}metricsblue{color: #44a6b5; font-family: "weathertext2", Helvetica, Arial, sans-serif; background: rgba(86, 95, 103, 0.5); -webkit-border-radius: 2px; border-radius: 2px; align-items: center; justify-content: center; font-size: 0.9em; left: 10px; padding: 0 3px 0 3px;}.dvmconvertrain{position: relative; font-size: 0.7em; top: 4px; color: white; margin-left: 5px; margin-top: -2px;}.lotemp{color: black; font-size: 0.65rem;}.hitempy{position: relative; background: 0; color: black; width: 70px; padding: 1px; -webit-border-radius: 2px; border-radius: 2px; margin-top: -34px; margin-left: 52px; padding-left: 3px; line-height: 11px; font-size: 9px;}.actualt{position: relative; left: 0px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: #555555; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: max-content; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: white; align-items: center; justify-content: center; margin-bottom: 10px; top: 0; text-align: center;}.actualw{position: relative; left: 5px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: rgba(74, 99, 111, 0.1); padding: 5px; font-family: Arial, Helvetica, sans-serif; width: max-content; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: #aaa; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}.svgimage{background: rgba(0, 155, 171, 1); -webit-border-radius: 2px; border-radius: 2px;}.actual{position: relative; left: 5px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: 95%; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: #aaa; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}
    </style>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Highcharts Week graph for weewx</title>
    <script src="dvmhighcharts/scripts/jquery.min.js"></script>
    <script src="dvmhighcharts/scripts/highstock.js"></script>
    <script src="dvmhighcharts/scripts/annotations.js"></script>
    <script src="dvmhighcharts/scripts/boost.js"></script>
    <script src="dvmhighcharts/scripts/highcharts-more.js"></script>
    <script src="dvmhighcharts/scripts/exporting.js"></script>
    <script src="dvmhighcharts/scripts/export-data.js"></script>
    <script src="dvmhighcharts/scripts/Divumwx-<?php echo $theme;?>.js" type="text/javascript"></script>
</head>
<body>

<figure class="highcharts-figure">     
<div id="sun-path"></div>
</figure>

<script>
var baseTextColor = "var(--col-6)";
var sunpath  = [<?php echo $sunOutput;?>];
var moonpath  = [<?php echo $moonOutput;?>];
var sunaz = <?php echo $alm["sun_azimuth"];?>;
var sunalt = <?php echo $alm["sun_altitude"];?>;
var moonaz = <?php echo $alm["moon_azimuth"];?>;
var moonalt = <?php echo $alm["moon_altitude"];?>;

$(function () {
        (function (H) {
        H.wrap(H.Tick.prototype, 'render', function (p, index, old, opacity) {
        p.call(this, index, old, opacity);
        var gridLine = this.gridLine;
        
        if (!this.axis.horiz && this.pos == 0 && gridLine) {
            gridLine.attr({
            stroke: '#007fff'
          });
        }
      });
    })(Highcharts);

$(function () {
        (function (H) {
        H.wrap(H.Tick.prototype, 'render', function (p, index, old, opacity) {
        p.call(this, index, old, opacity);
        var gridLine = this.gridLine;
        
        if (!this.axis.horiz && this.pos == 80 && gridLine) {
            gridLine.attr({
            stroke: '#555'
          });
        }
      });
    })(Highcharts);

Highcharts.setOptions({
    time: {
        timezone: 'Europe/Berlin'
    },
    lang: {
        thousandsSep: ""
  }
});

Highcharts.chart('sun-path', {
    title: {
        text: 'Geocentric Sun and Moon Positions',
        align: 'center'
    },
    chart: {
        height: 500,
        borderWidth: 0,
        type: 'spline',
        events: {           
            load() {
                const xMoon = this.xAxis[0],
                yMoon = this.yAxis[0],
                r2 = 5,
                x2 = xMoon.toPixels(moonaz),
                y2 = yMoon.toPixels(moonalt);                              
                this.renderer.circle(x2, y2, r2).attr({
                fill: 'silver',
                }).add().toFront();

                const xSun = this.xAxis[0],
                ySun = this.yAxis[0],
                r1 = 10,
                x1 = xSun.toPixels(sunaz),
                y1 = ySun.toPixels(sunalt);              
                this.renderer.circle(x1, y1, r1).attr({
                fill: 'orange',
                }).add().toFront();

                this.renderer.image('../img/globe.svg',373,222,30,30)
                .add().toFront();               
                }
          }
    },
    xAxis: {
        crosshair: {
        width: 1,
        color: '#009bab',
        dashStyle: 'shortdash'       
        },      
        lineColor: '#555',
        lineWidth: 1,
        minorGridLineWidth: 0,
        minorTickColor: '#555',
        minorTickLength: 2,
        minorTickPosition: 'outside',
        minorTickWidth: 1,
        tickColor: '#555',
        tickLength: 4,
        tickPosition: 'outside',
        tickWidth: 1,
        title: {
            style: {
                font: 'bold 12px Helvetica'
            }
        },
        labels: {
            x: 0,
            y: 18,
                formatter: function () {
                    return this.value + '°';
            }
        },       
        tickPositions: [0, 45, 90, 135, 180, 225, 270, 315, 360],
        min: 0,
        max: 360,
        plotLines: [{
            color: '#2e8b57',
            width: 1,
            value: 180
        }]
    },
    yAxis: [{
        labels: {
            x: -5,
            y:  3,
                formatter: function () {
                    return this.value + '°';
            }
        },
        min: -80,
        max: 80,
        tickInterval: 20,
        crosshair: {
        width: 1,
        color: '#ff832f',
        dashStyle: 'shortdash'
        },
        lineColor: '#555',
        gridLineColor: 'transparent',
        tickLength: 4,
        tickColor: '#555',
        lineWidth: 1,
        title: {           
            text: '(Altitude)'
        }}, {
        labels: {
            x: 5,
            y: 3
        },           
        tickLength: 4,
        lineWidth: 1,
        lineColor: '#555',
        opposite: true,
        title: {
            text: ''
        }                      
    }],
    tooltip: {
            headerFormat: '<b></b>',       
            pointFormat: '<span style="color:{series.color}">●</span> {series.name} Azimuth: <b>{point.x:.2f}°</b> <br/>' + 
            '<span style="color:{series.color}">●</span> {series.name} Altitude: <b>{point.y:.2f}°</b><br/>',          
        shared: false,  
    },
    annotations: [{
        labelOptions: {
            backgroundColor: 'transparent',
            borderWidth: 0,
            style: {
                color: baseTextColor
            }
        },
        labels: [{
            point: { x: 325.5, y: -5 },
            text: 'Zenith'
        }, {
           point: { x: 10, y: 202.5 },
            text: 'Horizon'
        }, {
            point: { x: 10, y: 400 },
            text: 'Azimuth'
        }],
        draggable: "" // set to disabled
    }],
    plotOptions: {
        series: {
            label: {
                connectorAllowed: false
            },
            marker: {
                enabled: false,
                    radius: 3,
                    symbol: 'circle'
            },
            lineWidth: 1
        },
        spline: {
            dataGrouping: {
                    enabled: false
            }
        },
    },
    series: [{
        name: 'Sun',
        yAxis: 0,
        data: sunpath
    }, {
       name: 'Moon',
        yAxis: 0,
        data: moonpath 
    }],
    responsive: {
        rules: [{
            condition: {
                maxWidth: 500
            },
            chartOptions: {
                legend: {
                    layout: 'horizontal',
                    align: 'center',
                    verticalAlign: 'bottom'
                }
            }
        }]
    },
    exporting: { enabled: false },
        accessibility: { enabled: false }
        });
    });
});
</script>
</html>