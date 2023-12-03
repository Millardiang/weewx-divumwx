/*****************************************************************************
plots.js

/*****************************************************************************

Set names of div ids to which the various plots will be rendered

*****************************************************************************/
var plotIds = {
    strikes: 'strikeplot',
    distance: 'distanceplot'
};

/*****************************************************************************

Set paths/names of our week and year JSON data files

Paths are relative to the web server root

*****************************************************************************/
var week_json = './json/bar_rain_week.json';
var year_json = './json/year.json';

/*****************************************************************************

Set default plot options

These are common plot options across all plots. Change them by all means but
make sure you know what you are doing. The Highcharts API documentation is
your reference.

*****************************************************************************/
var commonOptions = {
    chart: {
        backgroundColor: 'transparent',
        height: 350,
        width: 750
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
                    hour: ['%e %B %Y hour to %H:%M', '%e %B %Y %H:%M', '-%H:%M'],
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
                    hour: ['%e %B %Y hour to %H:%M', '%e %b %Y %H:%M', '-%H:%M'],
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
                    hour: ['%e %B %Y hour to %H:%M', '%e %b %Y %H:%M', '-%H:%M'],
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
                    hour: ['%e %B %Y hour to %H:%M', '%e %b %Y %H:%M', '-%H:%M'],
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
        lineColor: '#555',
        lineWidth: 1,
        minorGridLineWidth: 0,
        minorTickColor: '#555',
        minorTickLength: 2,
        minorTickPosition: 'inside',
        minorTickWidth: 1,
        tickColor: '#555',
        tickLength: 4,
        tickPosition: 'inside',
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
        minorTickPosition: 'inside',
        minorTickWidth: 1,
        opposite: false,
        showLastLabel: true,
        startOnTick: true,
        tickColor: '#555',
        tickLength: 4,
        tickPosition: 'inside',
        tickWidth: 1,
        title: {
            text: ''
        }
    }
};

function clone(obj) {
/*****************************************************************************

Function to clone an object

As found at http://stackoverflow.com/questions/728360/most-elegant-way-to-clone-a-javascript-object

*****************************************************************************/
    var copy;
    // Handle the 3 simple types, and null or undefined
    if (null === obj || 'object' !== typeof obj) {
        return obj;
    }

    // Handle Date
    if (obj instanceof Date) {
        copy = new Date();
        copy.setTime(obj.getTime());
        return copy;
    }

    // Handle Array
    if (obj instanceof Array) {
        copy = [];
        for (var i = 0, len = obj.length; i < len; i++) {
            copy[i] = clone(obj[i]);
        }
        return copy;
    }

    // Handle Object
    if (obj instanceof Object) {
        copy = {};
        for (var attr in obj) {
            if (obj.hasOwnProperty(attr)) {
                copy[attr] = clone(obj[attr]);
            }
        }
        return copy;
    }

    throw new Error('Unable to copy obj! Its type isn\'t supported.');
};

function addWeekOptions(obj) {
/*****************************************************************************

Function to add/set various plot options specific to the 'week' plot.

*****************************************************************************/
    // set range selector buttons
    obj.rangeSelector.buttons = [{
        type: 'hour',
        count: 1,
        text: '1h'
    }, {
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
    // set default range selector button
    obj.rangeSelector.selected = 3;
    // turn off data grouping for each plot type
    obj.plotOptions.column.dataGrouping.enabled = false;
    obj.plotOptions.spline.dataGrouping.enabled = false;
    obj.plotOptions.scatter.dataGrouping.enabled = false;
    return obj
};


function addYearOptions(obj) {
/*****************************************************************************

Function to add/set various plot options specific to the 'year' plot.

*****************************************************************************/
    // set range selector buttons
    obj.rangeSelector.buttons = [{
        type: 'day',
        count: 1,
        text: '1d'
    }, {
        type: 'week',
        count: 1,
        text: '1w'
    }, {
        type: 'month',
        count: 1,
        text: '1m'
    }, {
        type: 'month',
        count: 6,
        text: '6m'
    }, {
        type: 'all',
        text: '1y'
    }],
    // set default range selector button
    obj.rangeSelector.selected = 3;
    // turn off data grouping for each plot type
    obj.plotOptions.spline.dataGrouping.enabled = false;
    obj.plotOptions.column.dataGrouping.enabled = false;
    obj.plotOptions.columnrange.dataGrouping.enabled = false;
    return obj
};

function setStrikes(obj) {
/*****************************************************************************

Function to add/set various plot options specific to strike plots

*****************************************************************************/
    obj.chart.renderTo = plotIds.strikes;
    obj.chart.type = 'column';

    obj.plotOptions.column.color = '#90ee7e';
    obj.title = {
        text: ''
    };
    obj.xAxis = {
        crosshair: true,
        crosshair: {
            width: 2,
            color: '#90ee7e',
            dashStyle: 'shortdot'
        }
    };
    
    obj.xAxis.minRange = 900000;
    obj.xAxis.minTickInterval = 900000;

    return obj
};

function setStrikesStock(obj) {
/*****************************************************************************

Function to add/set various plot options specific to combined columnrange
spline strike plots

*****************************************************************************/
    obj = setStrikes(obj);
    obj.chart.type = 'column';
    obj.series = [{
        name: '',
        type: 'column',
        color: '#E0C2FF',
    }];
    obj.tooltip.valueDecimals = 0;
    return obj
};

function setDistance(obj) {
/*****************************************************************************

Function to add/set various plot options specific to distance plots

*****************************************************************************/
    obj.chart.renderTo = plotIds.distance;
    obj.chart.type = 'spline';

    obj.plotOptions.column.color = 'blue';
    obj.title = {
        text: 'Strike Distance'
    };
    obj.xAxis = {
        crosshair: true,
        crosshair: {
            width: 2,
            color: '#0575e6',
            dashStyle: 'shortdot'
        }
    };

    obj.xAxis.minRange = 900000;
    obj.xAxis.minTickInterval = 900000;

    return obj
};

function setDistanceStock(obj) {
/*****************************************************************************

Function to add/set various plot options specific to combined columnrange
spline distance plots

*****************************************************************************/
    obj = setDistance(obj);
    obj.chart.type = 'column';
    obj.series = [{
        name: '',
        type: 'column',
        color: '#E0C2FF',
    }];
    obj.tooltip.valueDecimals = 0;
    return obj
};

function weekly () {
/*****************************************************************************

Function to add/set various plot options and then plot each week plot

*****************************************************************************/
    // gather all fixed plot options for each plot
    
    var optionsStrikes = clone(commonOptions);
    optionsStrikes = addWeekOptions(optionsStrikes);
    optionsStrikes = setStrikes(optionsStrikes);

    var optionsDistance = clone(commonOptions);
    optionsDistance = addWeekOptions(optionsDistance);
    optionsDistance = setDistance(optionsDistance);

    /*
    jquery function call to get the week JSON data, set plot series and
    other 'variable' plot options (eg units of measure) obtain from the JSON
    data file and then display the actual plots
    */
    $.getJSON(week_json, function(seriesData) {
        
        optionsStrikes.series[0] = seriesData[0].strikeplot.series.lightning_strike_count;
        optionsStrikes.yAxis.minRange = seriesData[0].strikeplot.minRange;
        optionsStrikes.xAxis.min = seriesData[0].timespan.start;
        optionsStrikes.xAxis.max = seriesData[0].timespan.stop;
        optionsStrikes.yAxis.title.text = "(" + seriesData[0].strikeplot.units + ")";
        optionsStrikes.tooltip.valueSuffix = " " + seriesData[0].strikeplot.units;

        optionsDistance.series[0] = seriesData[0].distanceplot.series.lightning_distance_max;
        optionsDistance.yAxis.minRange = seriesData[0].distanceplot.minRange;
        optionsDistance.xAxis.min = seriesData[0].timespan.start;
        optionsDistance.xAxis.max = seriesData[0].timespan.stop;
        optionsDistance.yAxis.title.text = "(" + seriesData[0].distanceplot.units + ")";
        optionsDistance.tooltip.valueSuffix = " " + seriesData[0].distanceplot.units;


        Highcharts.setOptions({
            global: {
                timezoneOffset: -seriesData[0].utcoffset,
            },
            accessibility: { enabled: false },
        });
        // generate/display the actual plots
        if (document.getElementById(optionsStrikes.chart.renderTo)){
            var chart = new Highcharts.StockChart(optionsStrikes);
        };
        if (document.getElementById(optionsDistance.chart.renderTo)){
            var chart = new Highcharts.StockChart(optionsDistance);
        };
    });
};

function yearly () {
/*****************************************************************************

Function to add/set various plot options and then plot each year plot

*****************************************************************************/
    // gather all fixed plot options for each plot

    var optionsStrikes = clone(commonOptions);
    optionsStrikes = addYearOptions(optionsStrikes);
    optionsStrikes = setStrikesStock(optionsStrikes);

    var optionsDistance = clone(commonOptions);
    optionsDistance = addYearOptions(optionsDistance);
    optionsDistance = setDistanceStock(optionsDistance);

    /*
    jquery function call to get the year JSON data, set plot series and
    other 'variable' plot options (eg units of measure) obtain from the JSON
    data file and then display the actual plots
    */
    $.getJSON(year_json, function(seriesData) {
    
        optionsStrikes.series[0] = seriesData[0].strikeplot.series.lightning_strike_count;
        optionsStrikes.yAxis.minRange = seriesData[0].strikeplot.minRange;
        optionsStrikes.yAxis.title.text = "(" + seriesData[0].strikeplot.units + ")";
        optionsStrikes.tooltip.valueSuffix = " " + seriesData[0].strikeplot.units;
        optionsStrikes.xAxis.min = seriesData[0].timespan.start;
        optionsStrikes.xAxis.max = seriesData[0].timespan.stop;

        optionsDistance.series[0] = seriesData[0].distanceplot.series.lightning_distance_max;
        optionsDistance.yAxis.minRange = seriesData[0].distanceplot.minRange;
        optionsDistance.xAxis.min = seriesData[0].timespan.start;
        optionsDistance.xAxis.max = seriesData[0].timespan.stop;
        optionsDistance.yAxis.title.text = "(" + seriesData[0].distanceplot.units + ")";
        optionsDistance.tooltip.valueSuffix = " " + seriesData[0].distanceplot.units;



        Highcharts.setOptions({
            global: {
                timezoneOffset: -seriesData[0].utcoffset,
            },
            accessibility: { enabled: false },
        });
        // generate/display the actual plots
        if (document.getElementById(optionsStrikes.chart.renderTo)){
            var chart = new Highcharts.StockChart(optionsStrikes);
        };
        if (document.getElementById(optionsDistance.chart.renderTo)){
            var chart = new Highcharts.StockChart(optionsDistance);
        };
    });
};