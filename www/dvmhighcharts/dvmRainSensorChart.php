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


include ('../fixedSettings.php');
$json_string = file_get_contents('./json/rain.json');
$parsed_json = json_decode($json_string,true);

$offset = $parsed_json[0]["utcoffset"];
$utcoffset = json_encode($offset);
if($rainunit == "in"){$dataDecodeOne = $parsed_json[0]["rainPlot"]["series"]["dailyRainMax_inch"]; $dataDecodeTwo = $parsed_json[0]["rainPlot"]["series"]["dailyPiezoRainMax_inch"]; $unit = "inches";}
else {$dataDecodeOne = $parsed_json[0]["rainPlot"]["series"]["dailyRainMax_mm"]; $dataDecodeTwo = $parsed_json[0]["rainPlot"]["series"]["dailyPiezoRainMax_mm"]; $unit = "mm";}
$dataOne = json_encode($dataDecodeOne);
$dataTwo = json_encode($dataDecodeTwo);
if ($theme === "dark") {
    echo '<style>@font-face{font-family: weathertext2; src: url(css/fonts/verbatim-regular.woff) format("woff"), url(fonts/verbatim-regular.woff2) format("woff2"), url(fonts/verbatim-regular.ttf) format("truetype");}html,body{font-size: 13px; font-family: "weathertext2", Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.grid{display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 2fr)); grid-gap: 5px; align-items: stretch; color: white;}.grid > article{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.grid1{display: grid; grid-template-columns: repeat(auto-fill, minmax(100%, 1fr)); grid-gap: 5px; align-items: stretch; color: #f5f7fc; margin-top: 5px;}.grid1 > articlegraph{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; height: 360px;}.divumwxbrowser-footer{flex-basis: auto; height: 35px; background: #ebebeb; background: rgba(56, 56, 60, 1); border-bottom: 0; display: flex; bottom: -20px; width: 97.4%; -webkit-border-bottom-right-radius: 5px; -webkit-border-bottom-left-radius: 5px; -moz-border-radius-bottomright: 5px; -moz-border-radius-bottomleft: 5px; border-bottom-right-radius: 5px; border-bottom-left-radius: 5px;}a{color: white; text-decoration: none;}.divumwxdarkbrowser{position: relative; background: 0; width: 97%; height: 30px; margin: auto; margin-top: -5px; margin-left: 0px; border-top-left-radius: 5px; border-top-right-radius: 5px; padding-top: 10px;}.divumwxdarkbrowser[url]:after{content: attr(url); color: white; font-size: 14px; text-align: center; position: absolute; left: 0; right: 0; top: 0; padding: 4px 15px; margin: 11px 10px 0 auto; font-family: arial; height: 20px;}blue{color: #01a4b4;}orange{color: #009bb4;}orange1{color: rgba(255, 131, 47, 1);}green{color: #aaa;}red{color: #f37867;}red6{color: #d65b4a;}value{color: #fff;}yellow{color: #cc0;}purple{color: #916392;}.windcontainer1{left: 5px; top: 0;}.windtoday,.windtoday10,.windtoday30,.windtoday40,.windtoday60{font-family: weathertext2, Arial, Helvetica, system; width: 4rem; height: 1.25rem; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; font-size: 1rem; padding-top: 5px; color: #fff; border-bottom: 13px solid rgba(56, 56, 60, 1); align-items: center; justify-content: center; text-align: center; border-radius: 3px;}.windcaution,.windtrend{position: absolute; font-size: 1rem;}.windtoday{background: #9aba2f;}.windtoday10{background: rgba(230, 161, 65, 1);}.windtoday30{background: rgba(255, 124, 57, 0.8);}.windtoday40{background: rgba(255, 124, 57, 0.8);}.windtoday60{background: rgba(211, 93, 78, 1);}.windcaution{margin-left: 120px; margin-top: 112px; font-family: Arial, Helvetica, system;}.windtrend{margin-left: 135px; margin-top: 48px; z-index: 1; color: #fff;}smalluvunit{font-size: 0.55rem; font-family: Arial, Helvetica, system;}.almanac{font-size: 1.25em; margin-top: 30px; color: rgba(56, 56, 60, 1); width: 12em;}metricsblue{color: #44a6b5; font-family: "weathertext2", Helvetica, Arial, sans-serif; background: rgba(86, 95, 103, 0.5); -webkit-border-radius: 2px; border-radius: 2px; align-items: center; justify-content: center; font-size: 0.9em; left: 10px; padding: 0 3px 0 3px;}.dvmconvertrain{position: relative; font-size: 0.7em; top: 4px; color: white; margin-left: 5px; margin-top: -2px;}.lotemp{color: white; font-size: 0.65rem;}.hitempy{position: relative; background: 0; color: white; width: 70px; padding: 1px; -webit-border-radius: 2px; border-radius: 2px; margin-top: -34px; margin-left: 52px; padding-left: 3px; line-height: 11px; font-size: 9px;}.actualt{position: relative; left: 0px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: #555555; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: max-content; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: white; align-items: center; justify-content: center; margin-bottom: 10px; top: 0; text-align: center;}.actualw{position: relative; left: 5px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: rgba(74, 99, 111, 0.1); padding: 5px; font-family: Arial, Helvetica, sans-serif; width: max-content; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: #aaa; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}.svgimage{background: rgba(0, 155, 171, 1); -webit-border-radius: 2px; border-radius: 2px;}.actual{position: relative; left: 5px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: 95%; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: #aaa; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}
    </style>';
} else if ($theme === "light") {
    echo '<style>@font-face{font-family: weathertext2; src: url(css/fonts/verbatim-regular.woff) format("woff"), url(fonts/verbatim-regular.woff2) format("woff2"), url(fonts/verbatim-regular.ttf) format("truetype");}html,body{font-size: 13px; font-family: "weathertext2", Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; background-color: white;}.grid{display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 2fr)); grid-gap: 5px; align-items: stretch; color: #f5f7fc;}.grid > article{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.grid1{display: grid; grid-template-columns: repeat(auto-fill, minmax(100%, 1fr)); grid-gap: 5px; align-items: stretch; color: #f5f7fc; margin-top: 5px;}.grid1 > articlegraph{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; height: 360px;}.divumwxbrowser-footer{flex-basis: auto; height: 35px; background: #ebebeb; background: rgba(56, 56, 60, 1); border-bottom: 0; display: flex; bottom: -20px; width: 97.4%; -webkit-border-bottom-right-radius: 5px; -webkit-border-bottom-left-radius: 5px; -moz-border-radius-bottomright: 5px; -moz-border-radius-bottomleft: 5px; border-bottom-right-radius: 5px; border-bottom-left-radius: 5px;}a{color: black; text-decoration: none;}.divumwxdarkbrowser{position: relative; background: 0; width: 97%; height: 30px; margin: auto; margin-top: -5px; margin-left: 0px; border-top-left-radius: 5px; border-top-right-radius: 5px; padding-top: 10px;}.divumwxdarkbrowser[url]:after{content: attr(url); color: black; font-size: 14px; text-align: center; position: absolute; left: 0; right: 0; top: 0; padding: 4px 15px; margin: 11px 10px 0 auto; font-family: arial; height: 20px;}blue{color: #01a4b4;}orange{color: #009bb4;}orange1{color: rgba(255, 131, 47, 1);}green{color: #aaa;}red{color: #f37867;}red6{color: #d65b4a;}value{color: #fff;}yellow{color: #cc0;}purple{color: #916392;}.windcontainer1{left: 5px; top: 0;}.windtoday,.windtoday10,.windtoday30,.windtoday40,.windtoday60{font-family: weathertext2, Arial, Helvetica, system; width: 4rem; height: 1.25rem; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; font-size: 1rem; padding-top: 5px; color: white; border-bottom: 14px solid #555555; align-items: center; justify-content: center; text-align: center; border-radius: 3px;}.windcaution,.windtrend{position: absolute; font-size: 1rem;}.windtoday{background: #9aba2f;}.windtoday10{background: rgba(230, 161, 65, 1);}.windtoday30{background: rgba(255, 124, 57, 0.8);}.windtoday40{background: rgba(255, 124, 57, 0.8);}.windtoday60{background: rgba(211, 93, 78, 1);}.windcaution{margin-left: 120px; margin-top: 112px; font-family: Arial, Helvetica, system;}.windtrend{margin-left: 135px; margin-top: 48px; z-index: 1; color: #fff;}smalluvunit{font-size: 0.55rem; font-family: Arial, Helvetica, system;}.almanac{font-size: 1.25em; margin-top: 30px; color: rgba(56, 56, 60, 1); width: 12em;}metricsblue{color: #44a6b5; font-family: "weathertext2", Helvetica, Arial, sans-serif; background: rgba(86, 95, 103, 0.5); -webkit-border-radius: 2px; border-radius: 2px; align-items: center; justify-content: center; font-size: 0.9em; left: 10px; padding: 0 3px 0 3px;}.dvmconvertrain{position: relative; font-size: 0.7em; top: 4px; color: white; margin-left: 5px; margin-top: -2px;}.lotemp{color: black; font-size: 0.65rem;}.hitempy{position: relative; background: 0; color: black; width: 70px; padding: 1px; -webit-border-radius: 2px; border-radius: 2px; margin-top: -34px; margin-left: 52px; padding-left: 3px; line-height: 11px; font-size: 9px;}.actualt{position: relative; left: 0px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: #555555; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: max-content; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: white; align-items: center; justify-content: center; margin-bottom: 10px; top: 0; text-align: center;}.actualw{position: relative; left: 5px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: rgba(74, 99, 111, 0.1); padding: 5px; font-family: Arial, Helvetica, sans-serif; width: max-content; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: #aaa; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}.svgimage{background: rgba(0, 155, 171, 1); -webit-border-radius: 2px; border-radius: 2px;}.actual{position: relative; left: 5px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: 95%; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: #aaa; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}
    </style>';
}
?>

<!DOCTYPE html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Highcharts Week graph for weewx</title>
    <script src="./scripts/jquery.min.js"></script>
    <script src="./scripts//highstock.js"></script>
    <script src="./scripts//boost.js"></script>
    <script src="./scripts/highcharts-more.js"></script>
    <script src="./scripts/exporting.js"></script>
    <script src="./scripts/export-data.js"></script>
    <script src="./scripts/divumwx-<?php echo $theme;?>.js" type="text/javascript"></script>
    <script src="./scripts/accessibility.js"></script>
</head>
    <body>
    <style>
    body {
    background-color: 'transparent',
}
#powerchart {
    width: 100%;
    height: 500px;
}
</style>



<?php include ('rainSelect.php');?>
<div id="divumwx-chart"></div>

<script>
 
Highcharts.setOptions({
    lang: {
    thousandsSep: ""
  }
});
Highcharts.chart('divumwx-chart', {
    time: {
        timezoneOffset: - <?php echo $utcoffset;?>
    },
    chart: {
        type: 'column',
        borderWidth: 0,
        marginRight: 10,
        marginLeft: 70,
        backgroundColor: 'transparent',
        plotBackgroundColor: 'transparent',
        spacing: [15, 20, 10, 0],
        zoomType: 'x'
    },
    legend: { 
        enabled: true 
    },
    plotOptions: {
        area: {
            lineWidth: 1,
            marker: {
                enabled: false,
                radius: 2,
                symbol: 'circle'
            },
            fillOpacity: 0.05
        },
        column: {
            dataGrouping: {
                dateTimeLabelFormats: {
                    hour: ['%e %B %Y %H:%M', '%e %B %Y %H:%M', '-%H:%M'],
                    day: ['%e %B %Y', '%e %B', '-%e %B %Y'],
                    week: ['Week starting %e %B %Y', '%e %B', '-%e %B %Y'],
                    month: ['%B %Y', '%B', '-%B %Y'],
                    year: ['%Y', '%Y', '-%Y']
                },
                enabled: true,
                forced: false,
                units: [[
                    'hour',
                        [1]
                    ], [
                    'day',
                        [1]
                    ], [
                    'week',
                        [1]
                    ]
                ]
            },
        },
        columnrange: {
            dataGrouping: {
                dateTimeLabelFormats: {
                    hour: ['%e %B %Y %H:%M', '%e %b %Y %H:%M', '-%H:%M'],
                    day: ['%e %B %Y', '%e %B', '-%e %B %Y'],
                    week: ['Week from %e %B %Y', '%e %B', '-%e %B %Y'],
                    month: ['%B %Y', '%B', '-%B %Y'],
                    year: ['%Y', '%Y', '-%Y']
                },
                enabled: true,
                forced: true,
                units: [[
                    'day',
                        [1]
                    ], [
                    'week',
                        [1]
                    ]
                ]
            },
        },
        series: {
            states: {
                hover: {
                    halo: {
                        size: 0,
                    }
                }
            }
        },
        scatter: {
            dataGrouping: {
                dateTimeLabelFormats: {
                    hour: ['%e %B %Y %H:%M', '%e %b %Y %H:%M', '-%H:%M'],
                    day: ['%e %b %Y', '%e %b', '-%e %b %Y'],
                    week: ['Week from %e %b %Y', '%e %b', '-%e %b %Y'],
                    month: ['%B %Y', '%B', '-%B %Y'],
                    year: ['%Y', '%Y', '-%Y']
                },
                enabled: true,
                forced: true,
                units: [[
                    'hour',
                        [1]
                    ], [
                    'day',
                        [1]
                    ], [
                    'week',
                        [1]
                    ]
                ]
            },
            marker: {
                enabled: false,
                radius: 1,
                symbol: 'circle'
            },
            shadow: false,
            states: {
                hover: {
                    halo: false,
                }
            }
        },
        spline: {
            dataGrouping: {
                dateTimeLabelFormats: {
                    hour: ['%e %B %Y %H:%M', '%e %b %Y %H:%M', '-%H:%M'],
                    day: ['%e %b %Y', '%e %b', '-%e %b %Y'],
                    week: ['Week from %e %b %Y', '%e %b', '-%e %b %Y'],
                    month: ['%B %Y', '%B', '-%B %Y'],
                    year: ['%Y', '%Y', '-%Y']
                },
                enabled: true,
                forced: true,
                units: [[
                    'hour',
                        [1]
                    ], [
                    'day',
                        [1]
                    ], [
                    'week',
                        [1]
                    ]
                ]
            },
            lineWidth: 0.5,
            marker: {
                radius: 0.25,
                enabled: false,
                symbol: 'circle'
            },
            shadow: false,
            states: {
                hover: {
                    lineWidth: 1,
                    lineWidthPlus: 1
                }
            }
        },
    },
    rangeSelector: { 
        buttonSpacing: 0 
    },
    series: [{
    }],
    tooltip: {
        style: {
            color: '#000000'},
        backgroundColor: '#ffffff',

        dateTimeLabelFormats: {
            minute: '%e %B %Y %H:%M',
            hour: '%e %B %Y %H:%M',
            day: '%A %e %B %Y',
        },
        shared: true,
        // need to set valueSuffix so we can set it later if needed
        valueSuffix: ''
    },
    xAxis: {
        dateTimeLabelFormats: {
            day: '%e %b',
            week: '%e %b',
            month: '%b %y',
        },
        labels: {
            x: 0,
            y: 18
        },
        crosshair: true,
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
                font: 'bold 12px Lucida Grande, Lucida Sans Unicode, Verdana, Arial, Helvetica, sans-serif'
            }
        },
        type: 'datetime'
    },
    yAxis: {
        endOnTick: true,
        labels: {
            x: -8,
            y: 3
        },
        crosshair: true,
        lineColor: '#555',
        lineWidth: 1,
        minorGridLineWidth: 0,
        minorTickColor: '#555',
        minorTickLength: 2,
        minorTickPosition: 'outside',
        minorTickWidth: 1,
        opposite: false,
        showLastLabel: true,
        startOnTick: true,
        tickColor: '#555',
        tickLength: 4,
        tickPosition: 'outside',
        tickWidth: 1,
        title: {
            text: ''
        }
    },
    rangeSelector: {
        enabled: true,
        inputEnabled: true,
        inputDateFormat: '%e %b %y',
        inputEditDateFormat: '%e %b %y',
        buttons: [{
            type: 'week',
            count: 1,
            text: '1w'
        }, {
            type: 'month',
            count: 1,
            text: '1m'
        }, {
            type: 'month',
            count: 3,
            text: '3m'
        }, {
            type: 'month',
            count: 6,
            text: '6m'
        }, {
            type: 'all',
            text: 'YTD'
        }],
            selected: 0
     },
    title: {
        text: 'Rain Sensors Piezo and Tipping Types - Comparisson'
    },
    tooltip: { 
        valueDecimals: 2,
        style: {
            color: '#000000'},
        backgroundColor: '#ffffff',
        valueSuffix: ' <?php echo $unit?>',
    },
    yAxis: [{
        labels: {
            x: -5,
            y: 3
        },
        tickLength: 4,
        lineWidth: 1,
        type: 'circle',
        title: {           
            text: '(' + '<?php echo $unit?>' + ')'
        }
        }, {
        labels: {
            x: 5,
            y: 3
        },
        tickLength: 0,
        lineWidth: 1,
        opposite: true,
        title: {
            text: ''
        }
    }],
    plotOptions: {
        series: { 
            borderWidth: 0,
            marker: {
                enabled: false
            }
        }
        
    },
    borderRadius: { 
        radius: 0 
    },
    series: [{
        name: 'Piezo Rain Sensor',
        type: 'spline',
        data: <?php echo $dataTwo;?>
    }, {
        name: 'Tipping Rain Sensor',
        type: 'spline',
        data: <?php echo $dataOne;?>
        
    }],

    exporting: { 
        enabled: true 
    },
    accessibility: { 
        enabled: false 
    }
});

</script>
</body>
</html>