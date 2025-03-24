<?php
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

$json_string = file_get_contents('json/lightning_week.json');
$parsed_json = json_decode($json_string,true);
$offset = $parsed_json[0]["utcoffset"];
$utcoffset = json_encode($offset);
$strikes = $parsed_json[0]["strikeplot"]["series"]["lightning_strike_count"]["name"];
$distance = $parsed_json[0]["strikeplot"]["series"]["lightning_distance_max"]["name"]; 
$strikesWeek = json_encode($strikes);
$distanceWeek = json_encode($distance); 

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>

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
#week-chart {
    width: 100%;
    height: 345px;
}

</style>
<div id="week-chart"></div>
<script>

var strikesWeek = <?php echo $strikesWeek;?>;
var distanceWeek = <?php echo $distanceWeek;?>;
var utcoffset = <?php echo $utcoffset;?>;

Highcharts.chart('week-chart', {
    time: {
        timezoneOffset: - utcoffset,
    },
    chart: {
        marginRight: 62.5,
        backgroundColor: 'transparent',
        spacing: [15, 20, 10, 0],
        zoomType: 'x'
    },
  
    legend: {
        enabled: true
    },
    plotOptions: {
        backgroundColor: 'transparent',
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
            day: '%A %e %B %Y'
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
                font: 'bold 12px Arial'
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
                        type: 'hour',
                        count: 6,
                        text: '6h'
                    }, {
                        type: 'hour',
                        count: 12,
                        text: '12h'
                    }, {
                        type: 'hour',
                        count: 24,
                        text: '24h'
                    }, {
                        type: 'hour',
                        count: 36,
                        text: '36h'
                    }, {
                        type: 'all',
                        text: '7d'
                    }],
                    selected: 3
                },
    title: {
        text: ''
    },
    tooltip: {
        valueDecimals: 0
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
            text: '(Strikes)'
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
            text: '(Distance km)'
        }
    }],
    plotOptions: {
        backgroundColor: 'transparent',
        series: {
            borderWidth: 0,
            marker: {
                enabled: false,
                states: {
                    hover: {
                        enabled: true
                    }
                }
            }

        }
    },
    borderRadius: {
            radius: 0
        },
    series: [{
        name: 'Strikes',
        type: 'column',
        data: strikesWeek
    }, {
        name: 'Distance',
        type: 'spline',
        data: distanceWeek,
        yAxis: 1
    }],
    accessibility: {
    enabled: false
  }
});



</script>
</body>
</html>