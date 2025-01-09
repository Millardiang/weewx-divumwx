<?php
include ('../fixedSettings.php');

####################################################################################################################                                                                                  #                                                                                                                   #
# weewx-divumwx Skin Template maintained by The DivumWX Team                                                        #
#                                                                                                                   #
# Copyright (C) 2023 Ian Millard, Steven Sheeley, Sean Balfour. All rights reserved                                 #
#                                                                                                                   #
# Distributed under terms of the GPLv3. See the file LICENSE.txt for your rights.                                   #
#                                                                                                                   #
# Issues for weewx-divumwx skin template should be addressed to https://github.com/Millardiang/weewx-divumwx/issues # 
#                                                                                                                   #
#####################################################################################################################
include('../fixedSettings');
$json_string = file_get_contents('../dvmhighcharts/json/year.json');
$parsed_json = json_decode($json_string,true);

$offset = $parsed_json[0]["utcoffset"];
$utcoffset = json_encode($offset);

$strikes = $parsed_json[0]["strikeplot"]["series"]["lightning_strike_count"];

if($windunit == "mph"){$distanceminmax = $parsed_json[0]["strikeplot"]["series"]["lightning_distance_minmax_mile"]; $y2unit = "mi";}
else {$distanceminmax = $parsed_json[0]["strikeplot"]["series"]["lightning_distance_minmax"]; $y2unit = "km";} 
$strikesYear = json_encode($strikes);
$distanceYear = json_encode($distance);
$distanceminmaxYear = json_encode($distanceminmax);
?>

<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Highcharts Year graph for weewx</title>
    <script src="scripts/jquery.min.js"></script>
    <script src="scripts/highstock.js"></script>
    <script src="scripts/boost.js"></script>
    <script src="scripts/highcharts-more.js"></script>
    <script src="scripts/exporting.js"></script>
    <script src="scripts/export-data.js"></script>
    <script src="scripts/brand-<?php echo $theme;?>.js" type="text/javascript"></script>
</head>
<body>

<style>
    body {
    background-color: 'transparent',
}
#year-chart {
    width: 100%;
    height: 420px;
}
</style>

<div id="year-chart"></div>

<script>
var y2units  = "<?php echo $y2unit;?>";
console.log(y2units);
var strikesYear = <?php echo $strikesYear;?>;
var distanceYear = <?php echo $distanceYear;?>;
var distanceminmaxYear = <?php echo $distanceminmaxYear;?>;
var utcoffset = <?php echo $utcoffset;?>;

Highcharts.setOptions({
    lang: {
    thousandsSep: ""
  }
});
Highcharts.chart('year-chart', {
    time: {
        timezoneOffset: - utcoffset
    },
    chart: {
        borderWidth: 0,
        marginRight: 62.5,
        backgroundColor: 'transparent',
        spacing: [15, 20, 10, 0],
        zoomType: 'x'
    },
    legend: {
        enabled: true
    },
    plotOptions: {backgroundColor: 'transparent',
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
            lineWidth: 1,
            marker: {
                radius: 1,
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
        buttonSpacing: 0,
    },
    series: [{
    }],
    tooltip: {
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
        type: 'datetime',
    },
    yAxis: {
        endOnTick: true,
        labels: {
            x: -8,
            y: 3
        },
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
            type: 'ytd',
            text: 'YTD'
        }, {
            type: 'year',
            count: 1,
            text: '1y'
        }, {
            type: 'all',
            text: 'All'
        }],
            selected: 5
        },
    title: {
        text: ''
    },
    tooltip: {
        valueDecimals: 0,
    },
    yAxis: [{
        labels: {
            x: -5,
            y: 3
        },
        tickLength: 4,
        lineWidth: 1,
        type: 'column',
        title: {           
            text: '(Strike Count)'
        }
    }, {
        labels: {
            x: 5,
            y: 3
        },
        tickLength: 4,
        lineWidth: 1,
        opposite: true,
        title: {
            text: '(Distance ' + y2units + ')'
        }
    }],
    plotOptions: {
        backgroundColor: 'transparent',
        series: {
            borderWidth: 0
        }
    },
    borderRadius: {
            radius: 0
        },
    series: [{
        name: 'Strikes',
        type: 'spline',
        data: strikesYear,
    }, {
        name: 'Distance',
        type: 'columnrange',
        data: distanceminmaxYear,
        yAxis: 1,
        tooltip: {
        valueSuffix: y2units
    }
        }],
    exporting: {
        enabled: false
    },
    accessibility: {
    enabled: false
  }
});

</script>
</body>
</html>