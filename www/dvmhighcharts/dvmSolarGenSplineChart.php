<?php
include ('../fixedSettings.php');
#####################################################################################################################                                                                                 #                                                                                                                   #
# weewx-divumwx Skin Template maintained by The DivumWX Team                                                        #
#                                                                                                                   #
# Copyright (C) 2023 Ian Millard, Steven Sheeley, Sean Balfour. All rights reserved                                 #
#                                                                                                                   #
# Distributed under terms of the GPLv3. See the file LICENSE.txt for your rights.                                   #
#                                                                                                                   #
# Issues for weewx-divumwx skin template should be addressed to https://github.com/Millardiang/weewx-divumwx/issues # 
#                                                                                                                   #
#####################################################################################################################
$json_string = file_get_contents('./json/solar.json');
$parsed_json = json_decode($json_string,true);

$offset = $parsed_json[0]["utcoffset"];
$utcoffset = json_encode($offset);

// for column or spline
$solarGenDecode = $parsed_json[0]['solarGenTotalsPlot']['series']['watts']['name'];
$solarGen = json_encode($solarGenDecode);
?>

<!DOCTYPE html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Highcharts Week graph for weewx</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://code.highcharts.com/stock/highstock.js"></script>
    <script src="https://code.highcharts.com/modules/boost.js"></script>
    <script src="https://code.highcharts.com/stock/highcharts-more.js"></script>
    <script src="https://code.highcharts.com/stock/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="scripts/brand-<?php echo $theme;?>.js" type="text/javascript"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <style>
    body {
    background-color: 'transparent',
}
#airDensity-chart {
    width: 100%;
    height: 500px;
}
</style>

</head>
<body>

<style>
    body {
    background-color: 'transparent',
}
#solarGen-chart {
    width: 100%;
    height: 500px;
}
</style>

<div id="solarGen-chart"></div>

<script>
var units = "W";
var solarGen = <?php echo $solarGen;?>;
var utcoffset = <?php echo $utcoffset;?>;
 
Highcharts.setOptions({
    lang: {
    thousandsSep: ""
  }
});
Highcharts.chart('solarGen-chart', {
    time: {
        timezoneOffset: - utcoffset
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
        buttonSpacing: 0 
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
        type: 'datetime'
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
            type: 'all',
            text: 'YTD'
        }],
            selected: 2
     },
    title: {
        text: 'Solar Power Generation Totals'
    },
    tooltip: { 
        valueDecimals: 0,
        backgroundColor: '#222328' 
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
            text: '(' + units + ')'
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
            borderWidth: 0 
        }
    },
    borderRadius: { 
        radius: 0 
    },
    series: [{
        marker: {
            radius: 2
            },
        name: 'Daily Power Generation Totals',
        type: 'column',
        data: solarGen,
        tooltip: { 
            valueSuffix: ' ' + units 
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