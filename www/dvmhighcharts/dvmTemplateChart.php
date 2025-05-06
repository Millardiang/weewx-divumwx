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
$json_string = file_get_contents('<path_to_data_here>');
$decode_json = json_decode($json_string,true);
$nameOne = "<nameOne_here>";
$nameTwo = "<nameTwo_here>";
$nameThree = "<nameThree_here>";
$nameFour = "<nameFour_here>";
$nameFive = "<nameFive_here>";
$nameSix = "<nameSix_here>";
$nameOne  = json_encode($decode_json["<json_object_path1_here>"]);
$nameTwo = json_encode($decode_json["<json_object_path2_here>"]); 
$nameThree = json_encode($decode_json["<json_object_path3_here>"]);
$nameFour = json_encode($decode_json["<json_object_path4_here>"]);
$nameFive = json_encode($decode_json["<json_object_path5_here>"]);
$nameSix = json_encode($decode_json["<json_object_path6_here>"]);
$utcoffset = json_encode($decode_json["<json_object_path_UTC_offset_here>"]);
$chartTitle = "<Chart_Title_Here">;
$selectMenuName = "<select_menu_name_here>Select.php";
/*$alderWeek = json_encode($alder);
$birchWeek = json_encode($birch); 
$oliveWeek = json_encode($olive);
$grassWeek = json_encode($grass);
$mugwortWeek = json_encode($mugwort);
$ragweedWeek = json_encode($ragweed);
*/
include('../fixedSettings.php');
include('$selectMenuName');
if ($theme === "dark") {
    echo '<style>@font-face{font-family: weathertext2; src: url(css/fonts/verbatim-regular.woff) format("woff"), url(fonts/verbatim-regular.woff2) format("woff2"), url(fonts/verbatim-regular.ttf) format("truetype");}html,body{font-size: 13px; font-family: "weathertext2", Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.grid{display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 2fr)); grid-gap: 5px; align-items: stretch; color: white;}.grid > article{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.grid1{display: grid; grid-template-columns: repeat(auto-fill, minmax(100%, 1fr)); grid-gap: 5px; align-items: stretch; color: #f5f7fc; margin-top: 5px;}.grid1 > articlegraph{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; height: 360px;}.divumwxbrowser-footer{flex-basis: auto; height: 35px; background: #ebebeb; background: rgba(56, 56, 60, 1); border-bottom: 0; display: flex; bottom: -20px; width: 97.4%; -webkit-border-bottom-right-radius: 5px; -webkit-border-bottom-left-radius: 5px; -moz-border-radius-bottomright: 5px; -moz-border-radius-bottomleft: 5px; border-bottom-right-radius: 5px; border-bottom-left-radius: 5px;}a{color: white; text-decoration: none;}.divumwxdarkbrowser{position: relative; background: 0; width: 97%; height: 30px; margin: auto; margin-top: -5px; margin-left: 0px; border-top-left-radius: 5px; border-top-right-radius: 5px; padding-top: 10px;}.divumwxdarkbrowser[url]:after{content: attr(url); color: white; font-size: 14px; text-align: center; position: absolute; left: 0; right: 0; top: 0; padding: 4px 15px; margin: 11px 10px 0 auto; font-family: arial; height: 20px;}blue{color: #01a4b4;}orange{color: #009bb4;}orange1{color: rgba(255, 131, 47, 1);}green{color: #aaa;}red{color: #f37867;}red6{color: #d65b4a;}value{color: #fff;}yellow{color: #cc0;}purple{color: #916392;}.windcontainer1{left: 5px; top: 0;}.windtoday,.windtoday10,.windtoday30,.windtoday40,.windtoday60{font-family: weathertext2, Arial, Helvetica, system; width: 4rem; height: 1.25rem; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; font-size: 1rem; padding-top: 5px; color: #fff; border-bottom: 13px solid rgba(56, 56, 60, 1); align-items: center; justify-content: center; text-align: center; border-radius: 3px;}.windcaution,.windtrend{position: absolute; font-size: 1rem;}.windtoday{background: #9aba2f;}.windtoday10{background: rgba(230, 161, 65, 1);}.windtoday30{background: rgba(255, 124, 57, 0.8);}.windtoday40{background: rgba(255, 124, 57, 0.8);}.windtoday60{background: rgba(211, 93, 78, 1);}.windcaution{margin-left: 120px; margin-top: 112px; font-family: Arial, Helvetica, system;}.windtrend{margin-left: 135px; margin-top: 48px; z-index: 1; color: #fff;}smalluvunit{font-size: 0.55rem; font-family: Arial, Helvetica, system;}.almanac{font-size: 1.25em; margin-top: 30px; color: rgba(56, 56, 60, 1); width: 12em;}metricsblue{color: #44a6b5; font-family: "weathertext2", Helvetica, Arial, sans-serif; background: rgba(86, 95, 103, 0.5); -webkit-border-radius: 2px; border-radius: 2px; align-items: center; justify-content: center; font-size: 0.9em; left: 10px; padding: 0 3px 0 3px;}.dvmconvertrain{position: relative; font-size: 0.7em; top: 4px; color: white; margin-left: 5px; margin-top: -2px;}.lotemp{color: white; font-size: 0.65rem;}.hitempy{position: relative; background: 0; color: white; width: 70px; padding: 1px; -webit-border-radius: 2px; border-radius: 2px; margin-top: -34px; margin-left: 52px; padding-left: 3px; line-height: 11px; font-size: 9px;}.actualt{position: relative; left: 0px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: #555555; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: max-content; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: white; align-items: center; justify-content: center; margin-bottom: 10px; top: 0; text-align: center;}.actualw{position: relative; left: 5px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: rgba(74, 99, 111, 0.1); padding: 5px; font-family: Arial, Helvetica, sans-serif; width: max-content; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: #aaa; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}.svgimage{background: rgba(0, 155, 171, 1); -webit-border-radius: 2px; border-radius: 2px;}.actual{position: relative; left: 5px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: 95%; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: #aaa; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}
    </style>';
} elseif ($theme === "light") {
    echo '<style>@font-face{font-family: weathertext2; src: url(css/fonts/verbatim-regular.woff) format("woff"), url(fonts/verbatim-regular.woff2) format("woff2"), url(fonts/verbatim-regular.ttf) format("truetype");}html,body{font-size: 13px; font-family: "weathertext2", Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; background-color: white;}.grid{display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 2fr)); grid-gap: 5px; align-items: stretch; color: #f5f7fc;}.grid > article{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.grid1{display: grid; grid-template-columns: repeat(auto-fill, minmax(100%, 1fr)); grid-gap: 5px; align-items: stretch; color: #f5f7fc; margin-top: 5px;}.grid1 > articlegraph{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; height: 360px;}.divumwxbrowser-footer{flex-basis: auto; height: 35px; background: #ebebeb; background: rgba(56, 56, 60, 1); border-bottom: 0; display: flex; bottom: -20px; width: 97.4%; -webkit-border-bottom-right-radius: 5px; -webkit-border-bottom-left-radius: 5px; -moz-border-radius-bottomright: 5px; -moz-border-radius-bottomleft: 5px; border-bottom-right-radius: 5px; border-bottom-left-radius: 5px;}a{color: black; text-decoration: none;}.divumwxdarkbrowser{position: relative; background: 0; width: 97%; height: 30px; margin: auto; margin-top: -5px; margin-left: 0px; border-top-left-radius: 5px; border-top-right-radius: 5px; padding-top: 10px;}.divumwxdarkbrowser[url]:after{content: attr(url); color: black; font-size: 14px; text-align: center; position: absolute; left: 0; right: 0; top: 0; padding: 4px 15px; margin: 11px 10px 0 auto; font-family: arial; height: 20px;}blue{color: #01a4b4;}orange{color: #009bb4;}orange1{color: rgba(255, 131, 47, 1);}green{color: #aaa;}red{color: #f37867;}red6{color: #d65b4a;}value{color: #fff;}yellow{color: #cc0;}purple{color: #916392;}.windcontainer1{left: 5px; top: 0;}.windtoday,.windtoday10,.windtoday30,.windtoday40,.windtoday60{font-family: weathertext2, Arial, Helvetica, system; width: 4rem; height: 1.25rem; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; font-size: 1rem; padding-top: 5px; color: white; border-bottom: 14px solid #555555; align-items: center; justify-content: center; text-align: center; border-radius: 3px;}.windcaution,.windtrend{position: absolute; font-size: 1rem;}.windtoday{background: #9aba2f;}.windtoday10{background: rgba(230, 161, 65, 1);}.windtoday30{background: rgba(255, 124, 57, 0.8);}.windtoday40{background: rgba(255, 124, 57, 0.8);}.windtoday60{background: rgba(211, 93, 78, 1);}.windcaution{margin-left: 120px; margin-top: 112px; font-family: Arial, Helvetica, system;}.windtrend{margin-left: 135px; margin-top: 48px; z-index: 1; color: #fff;}smalluvunit{font-size: 0.55rem; font-family: Arial, Helvetica, system;}.almanac{font-size: 1.25em; margin-top: 30px; color: rgba(56, 56, 60, 1); width: 12em;}metricsblue{color: #44a6b5; font-family: "weathertext2", Helvetica, Arial, sans-serif; background: rgba(86, 95, 103, 0.5); -webkit-border-radius: 2px; border-radius: 2px; align-items: center; justify-content: center; font-size: 0.9em; left: 10px; padding: 0 3px 0 3px;}.dvmconvertrain{position: relative; font-size: 0.7em; top: 4px; color: white; margin-left: 5px; margin-top: -2px;}.lotemp{color: black; font-size: 0.65rem;}.hitempy{position: relative; background: 0; color: black; width: 70px; padding: 1px; -webit-border-radius: 2px; border-radius: 2px; margin-top: -34px; margin-left: 52px; padding-left: 3px; line-height: 11px; font-size: 9px;}.actualt{position: relative; left: 0px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: #555555; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: max-content; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: white; align-items: center; justify-content: center; margin-bottom: 10px; top: 0; text-align: center;}.actualw{position: relative; left: 5px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: rgba(74, 99, 111, 0.1); padding: 5px; font-family: Arial, Helvetica, sans-serif; width: max-content; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: #aaa; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}.svgimage{background: rgba(0, 155, 171, 1); -webit-border-radius: 2px; border-radius: 2px;}.actual{position: relative; left: 5px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: 95%; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: #aaa; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}
    </style>';
}
?>
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
    
<figure class="highcharts-figure">
    <div id="container"></div>
    
</figure>

<script>
var name1 = <?php echo $nameOne;?>;
var name2 = <?php echo $nameTwo;?>;
var name3 = <?php echo $nameThree;?>;
var name4= <?php echo $nameFour;?>;
var name5= <?php echo $nameFive;?>;
var name6 = <?php echo $nameSix;?>;
var data1 = <?php echo $dataOne;?>;
var data2 = <?php echo $dataTwo;?>;
var data3 = <?php echo $dataThree;?>;
var data4= <?php echo $dataFour;?>;
var data5= <?php echo $dataFive;?>;
var data6 = <?php echo $dataSix;?>;
var type1 = <?php echo $typeOne;?>;
var type2 = <?php echo $typeTwo;?>;
var type = <?php echo $typeThree;?>;
var type4= <?php echo $typeFour;?>;
var type5= <?php echo $typeFive;?>;
var type6 = <?php echo $typeSix;?>;
var utcoffset = <?php echo $utcoffset;?>;

Highcharts.chart('container', {
        time: {
        timezoneOffset: - utcoffset
    },
    chart: {
        backgroundColor: "transparent"
    },
    title: {
        text: 'Pollen Count Week Chart',
        align: 'center'
    },
    chart: {
        height: 500,
        type: 'line'
    },

    tooltip: {
        dateTimeLabelFormats: {
            minute: '%e %B %Y %H:%M',
            hour: '%e %B %Y %H:%M',
            day: '%A %e %B %Y'
        },
        shared: true,
        // need to set valueSuffix so we can set it later if needed
        valueSuffix: ' grains/㎥'
    },
    yAxis: {
        title: {
            text: 'Pollen Count (grains/㎥)'
        }
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

    legend: {
        layout: 'horizontal',
        align: 'center',
        verticalAlign: 'bottom'
    },

    plotOptions: {
        series: {
            label: {
                connectorAllowed: false
            },
            marker: {
                radius: 3
            },
            lineWidth: 0.5,
            pointStart: 2010
        }
    },
    rangeSelector: {
        enabled: true,
        inputEnabled: true,
        inputDateFormat: '%e %b %y',
        inputEditDateFormat: '%e %b %y',
        buttons: [ {type: 'hour',
                    count: 24,
                    text: '24h'
                    },{
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
            selected: 0
        },

    exporting: {
        enabled: true
    },
    series: [{
        name: 'Alder',
        type: 'spline',
        data: alderWeek
    }, {
        name: 'Birch',
        type: 'spline',
        data: birchWeek
    }, {
        name: 'Olive',
        type: 'spline',
        data: oliveWeek
    }, {
        name: 'Grass',
        type: 'spline',
        data: grassWeek
    }, {
        name: 'Mugwort',
        type: 'spline',
        data: mugwortWeek
    }, {
        name: 'Ragweed',
        type: 'spline',
        data: ragweedWeek
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
    }

});
</script>
</html>