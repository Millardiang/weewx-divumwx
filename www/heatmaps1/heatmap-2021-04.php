<?php
include("../fixedSettings.php");
include("select.php");
$unitTemp = "&deg;".$tempunit;
if($tempunit == 'C'){
$heatData = 
"[
{
date: '2021-04-01',
temperature: 10.9
},
{
date: '2021-04-02',
temperature: 9.5
},
{
date: '2021-04-03',
temperature: 8.6
},
{
date: '2021-04-04',
temperature: 15.8
},
{
date: '2021-04-05',
temperature: 8.5
},
{
date: '2021-04-06',
temperature: 7.1
},
{
date: '2021-04-07',
temperature: 5.9
},
{
date: '2021-04-08',
temperature: 12.7
},
{
date: '2021-04-09',
temperature: 12.0
},
{
date: '2021-04-10',
temperature: 6.2
},
{
date: '2021-04-11',
temperature: 7.4
},
{
date: '2021-04-12',
temperature: 8.7
},
{
date: '2021-04-13',
temperature: 12.2
},
{
date: '2021-04-14',
temperature: 12.1
},
{
date: '2021-04-15',
temperature: 10.9
},
{
date: '2021-04-16',
temperature: 10.2
},
{
date: '2021-04-17',
temperature: 12.9
},
{
date: '2021-04-18',
temperature: 13.7
},
{
date: '2021-04-19',
temperature: 16.8
},
{
date: '2021-04-20',
temperature: 18.0
},
{
date: '2021-04-21',
temperature: 14.0
},
{
date: '2021-04-22',
temperature: 15.0
},
{
date: '2021-04-23',
temperature: 16.6
},
{
date: '2021-04-24',
temperature: 17.2
},
{
date: '2021-04-25',
temperature: 13.8
},
{
date: '2021-04-26',
temperature: 13.1
},
{
date: '2021-04-27',
temperature: 12.7
},
{
date: '2021-04-28',
temperature: 11.3
},
{
date: '2021-04-29',
temperature: 10.7
},
{
date: '2021-04-30',
temperature: 11.1
},
];";}
else {
$heatData = 
"[
{
date: '2021-04-01',
temperature: 51.7
},
{
date: '2021-04-02',
temperature: 49.1
},
{
date: '2021-04-03',
temperature: 47.5
},
{
date: '2021-04-04',
temperature: 60.4
},
{
date: '2021-04-05',
temperature: 47.3
},
{
date: '2021-04-06',
temperature: 44.7
},
{
date: '2021-04-07',
temperature: 42.7
},
{
date: '2021-04-08',
temperature: 54.8
},
{
date: '2021-04-09',
temperature: 53.6
},
{
date: '2021-04-10',
temperature: 43.1
},
{
date: '2021-04-11',
temperature: 45.3
},
{
date: '2021-04-12',
temperature: 47.7
},
{
date: '2021-04-13',
temperature: 53.9
},
{
date: '2021-04-14',
temperature: 53.8
},
{
date: '2021-04-15',
temperature: 51.6
},
{
date: '2021-04-16',
temperature: 50.3
},
{
date: '2021-04-17',
temperature: 55.2
},
{
date: '2021-04-18',
temperature: 56.7
},
{
date: '2021-04-19',
temperature: 62.3
},
{
date: '2021-04-20',
temperature: 64.5
},
{
date: '2021-04-21',
temperature: 57.1
},
{
date: '2021-04-22',
temperature: 59.0
},
{
date: '2021-04-23',
temperature: 61.9
},
{
date: '2021-04-24',
temperature: 63.0
},
{
date: '2021-04-25',
temperature: 56.8
},
{
date: '2021-04-26',
temperature: 55.6
},
{
date: '2021-04-27',
temperature: 54.9
},
{
date: '2021-04-28',
temperature: 52.3
},
{
date: '2021-04-29',
temperature: 51.2
},
{
date: '2021-04-30',
temperature: 51.9
},
];";
};
?>
<html>
<style>
.highcharts-figure {
    min-width: 375px;
    max-width: 800px;
    margin: 1em auto;
}
</style>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/heatmap.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<script src="../dvmhighcharts/scripts/divumwx-<?php echo $theme;?>.js"></script>
<?php if ($theme === "dark") {
    echo '<style>@font-face{font-family: weathertext2; src: url(css/fonts/verbatim-regular.woff) format("woff"), url(fonts/verbatim-regular.woff2) format("woff2"), url(fonts/verbatim-regular.ttf) format("truetype");}html,body{font-size: 13px; font-family: "weathertext2", Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}/* unvisited link */a:link{color: white;}/* visited link */a:visited{color: white;}/* mouse over link */a:hover{color: white;}/* selected link */a:active{color: white;}.grid{display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 2fr)); grid-gap: 10px; align-items: stretch; color: #f5f7fc; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.grid > article{border: 1px solid #212428; box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.3); padding: 20px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; height: 90px;}.grid1{display: grid; grid-template-columns: repeat(auto-fill, minmax(100%, 1fr)); grid-gap: 5px; align-items: stretch; color: #f5f7fc; margin-top: 5px;}.grid1 > articlegraph{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; height: 300px;}.grid1 > articlegraph2{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background-color: lightgreen; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; height: 300px;}/* unvisited link */a:link{color: white;}/* visited link */a:visited{color: white;}/* mouse over link */a:hover{color: white;}/* selected link */a:active{color: white;}.divumwxdarkbrowser{position: relative; background: 0; width: 97%; height: 30px; margin: auto; margin-top: -5px; margin-left: 0px; border-top-left-radius: 5px; border-top-right-radius: 5px; padding-top: 10px;}.divumwxdarkbrowser[url]:after{content: attr(url); color: white; font-size: 14px; text-align: center; position: absolute; left: 0; right: 0; top: 0; padding: 4px 15px; margin: 11px 10px 0 auto; font-family: arial; height: 20px;}blue{color: #01a4b4;}orange{color: #009bb4;}orange1{color: rgba(255, 131, 47, 1);}green{color: #aaa;}red{color: #f37867;}red6{color: #d65b4a;}value{color: #fff;}yellow{color: #cc0;}purple{color: #916392;}.hitempyposx{position: relative; top: -90px; margin-left: 40px; margin-bottom: -30px;}.hitempypos{position: absolute; margin-top: -100px; margin-left: 40px; margin-bottom: 20px; display: block;}.hitempd{position: absolute; font-family: weathertext2, Arial, Helvetica, sans-serif; background: rgba(86, 95, 103, 0.3); color: #aaa; font-size: 0.7rem; width: 140px; padding: 0; margin-left: 30px; padding-left: 3px; align-items: center; justify-content: center; display: block; margin-top: 5px;}.hitempd1{position: absolute; font-family: weathertext2, Arial, Helvetica, sans-serif; background: rgba(86, 95, 103, 0.3); color: #aaa; font-size: 0.7rem; width: 140px; padding: 0; margin-left: 30px; padding-left: 3px; align-items: center; justify-content: center; display: block; margin-top: 40px; margin-bottom: 5px;}.uvmaxi3{position: absolute; left: -30px; color: rgba(0, 154, 171, 1); margin-top: -40px; font-size: 16px; width: 240px;}.uvmaxi3 span{color: #aaa;}.higust{position: relative; left: 0; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: rgba(74, 99, 111, 0.1); padding: 5px; font-family: Arial, Helvetica, sans-serif; width: 100px; height: 2em; font-size: 0.8rem; padding-top: 2px; color: #aaa; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}blue{color: rgba(0, 154, 171, 1);}.temperaturecontainer1{position: absolute; left: 20px; margin-top: -5px; margin-bottom: 20px;}.temperaturecontainer2{position: absolute; left: 20px; margin-top: 60px;}smalluvunit{font-size: 0.85rem; font-family: Arial, Helvetica, system;}.uvcontainer1{left: 70px; top: 0;}.uvtoday1,.uvtoday1-3,.uvtoday11,.uvtoday4-5,.uvtoday6-8,.uvtoday9-10{font-family: weathertext2, Arial, Helvetica, system; width: 5rem; height: 2.5rem; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; display: flex;}.uvtoday1,.uvtoday1-3,.uvtoday11,.uvtoday4-5,.uvtoday6-8,.uvtoday9-10{font-size: 1.25rem; padding-top: 2px; color: #fff; border-bottom: 5px solid rgba(56, 56, 60, 1); align-items: center; justify-content: center; border-radius: 3px; margin-bottom: 10px;}.uvcaution,.uvtrend{position: absolute; font-size: 1rem;}.uvtoday1,.uvtoday1-3{background: #9aba2f;}.uvtoday4-5{background: #ff7c39; background: -webkit-linear-gradient(90deg, #90b12a, #ff7c39); background: linear-gradient(90deg, #90b12a, #ff7c39);}.uvtoday6-8{background: #efa80f; background: -webkit-linear-gradient(90deg, #efa80f, #d86858); background: linear-gradient(90deg, #efa80f, #d86858);}.uvtoday9-10{background: #d05f2d; background: -webkit-linear-gradient(90deg, #d65b4a, #ac2816); background: linear-gradient(90deg, #d65b4a, #ac2816);}.uvtoday11{background: #95439f; background: -webkit-linear-gradient(90deg, #95439f, #a475cb); background: linear-gradient(90deg, #95439f, #a475cb);}.uvcaution{margin-left: 120px; margin-top: 112px; font-family: Arial, Helvetica, system;}.uvtrend{margin-left: 135px; margin-top: 48px; z-index: 1; color: #fff;}.simsekcontainer{float: left; font-family: weathertext, system; -o-font-smoothing: antialiased; left: 0; bottom: 0; right: 0; position: relative; margin: 40px 10px 10px 40px; left: -10px; top: 13px;}.simsek{font-size: 1.55rem; padding-top: 12px; color: #f8f8f8; background: rgba(230, 161, 65, 1); border-bottom: 18px solid rgba(56, 56, 60, 1); align-items: center; justify-content: center; border-radius: 3px;}smalluvunit{font-size: 0.65rem; font-family: Arial, Helvetica, system;}sup{font-size: 1em;}supwm2{font-size: 0.7em; vertical-align: super;}.dvmconvertrain{position: relative; font-size: 0.5em; top: 10px; color: #c0c0c0; margin-left: 5px;}.hitempy{position: relative; background: rgba(61, 64, 66, 0.5); color: #aaa; width: 90px; padding: 1px; -webit-border-radius: 2px; border-radius: 2px; margin-top: -20px; margin-left: 92px; padding-left: 3px; line-height: 11px; font-size: 9px;}.actualt{position: relative; left: 0; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: teal; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: 100px; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: white; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}.actualw{position: relative; left: 5px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: rgba(74, 99, 111, 0.1); padding: 5px; font-family: Arial, Helvetica, sans-serif; width: 100px; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: #aaa; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}
    </style>';
} elseif ($theme === "light") {
    echo '<style>@font-face{font-family: weathertext2; src: url(css/fonts/verbatim-regular.woff) format("woff"), url(fonts/verbatim-regular.woff2) format("woff2"), url(fonts/verbatim-regular.ttf) format("truetype");}html,body{font-size: 13px; font-family: "weathertext2", Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; background-color: white;}/* unvisited link */a:link{color: black;}/* visited link */a:visited{color: black;}/* mouse over link */a:hover{color: black;}/* selected link */a:active{color: black;}.grid{display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 2fr)); grid-gap: 10px; align-items: stretch; color: #f5f7fc; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.grid > article{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 20px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; height: 90px;}.grid1{display: grid; grid-template-columns: repeat(auto-fill, minmax(100%, 1fr)); grid-gap: 5px; align-items: stretch; color: black; margin-top: 5px;}.grid1 > articlegraph{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: white; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; height: 300px;}.divumwxdarkbrowser{position: relative; background: 0; width: 97%; height: 30px; margin: auto; margin-top: -5px; margin-left: 0px; border-top-left-radius: 5px; border-top-right-radius: 5px; padding-top: 10px;}.divumwxdarkbrowser[url]:after{content: attr(url); color: black; font-size: 14px; text-align: center; position: absolute; left: 0; right: 0; top: 0; padding: 4px 15px; margin: 11px 10px 0 auto; font-family: arial; height: 20px;}blue{color: #01a4b4;}orange{color: #009bb4;}orange1{color: rgba(255, 131, 47, 1);}green{color: #aaa;}red{color: #f37867;}red6{color: #d65b4a;}value{color: #fff;}yellow{color: #cc0;}purple{color: #916392;}.hitempyposx{position: relative; top: -90px; margin-left: 40px; margin-bottom: -30px;}.hitempypos{position: absolute; margin-top: -100px; margin-left: 40px; margin-bottom: 20px; display: block;}.hitempd{position: absolute; font-family: weathertext2, Arial, Helvetica, sans-serif; background: rgba(86, 95, 103, 0.3); color: #aaa; font-size: 0.7rem; width: 140px; padding: 0; margin-left: 30px; padding-left: 3px; align-items: center; justify-content: center; display: block; margin-top: 5px;}.hitempd1{position: absolute; font-family: weathertext2, Arial, Helvetica, sans-serif; background: rgba(86, 95, 103, 0.3); color: #aaa; font-size: 0.7rem; width: 140px; padding: 0; margin-left: 30px; padding-left: 3px; align-items: center; justify-content: center; display: block; margin-top: 40px; margin-bottom: 5px;}.uvmaxi3{position: absolute; left: -30px; color: rgba(0, 154, 171, 1); margin-top: -40px; font-size: 16px; width: 240px;}.uvmaxi3 span{color: #aaa;}.higust{position: relative; left: 0; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: 0; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: 100px; height: 2em; font-size: 0.8rem; padding-top: 2px; color: black; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}blue{color: rgba(0, 154, 171, 1);}.temperaturecontainer1{position: absolute; left: 20px; margin-top: -5px; margin-bottom: 20px;}.temperaturecontainer2{position: absolute; left: 20px; margin-top: 60px;}smalluvunit{font-size: 0.85rem; font-family: Arial, Helvetica, system;}.uvcontainer1{left: 70px; top: 0;}.uvtoday1,.uvtoday1-3,.uvtoday11,.uvtoday4-5,.uvtoday6-8,.uvtoday9-10{font-family: weathertext2, Arial, Helvetica, system; width: 5rem; height: 2.5rem; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; display: flex;}.uvtoday1,.uvtoday1-3,.uvtoday11,.uvtoday4-5,.uvtoday6-8,.uvtoday9-10{font-size: 1.25rem; padding-top: 2px; color: #fff; border-bottom: 5px solid rgba(56, 56, 60, 1); align-items: center; justify-content: center; border-radius: 3px; margin-bottom: 10px;}.uvcaution,.uvtrend{position: absolute; font-size: 1rem;}.uvtoday1,.uvtoday1-3{background: #9aba2f;}.uvtoday4-5{background: #ff7c39; background: -webkit-linear-gradient(90deg, #90b12a, #ff7c39); background: linear-gradient(90deg, #90b12a, #ff7c39);}.uvtoday6-8{background: #efa80f; background: -webkit-linear-gradient(90deg, #efa80f, #d86858); background: linear-gradient(90deg, #efa80f, #d86858);}.uvtoday9-10{background: #d05f2d; background: -webkit-linear-gradient(90deg, #d65b4a, #ac2816); background: linear-gradient(90deg, #d65b4a, #ac2816);}.uvtoday11{background: #95439f; background: -webkit-linear-gradient(90deg, #95439f, #a475cb); background: linear-gradient(90deg, #95439f, #a475cb);}.uvcaution{margin-left: 120px; margin-top: 112px; font-family: Arial, Helvetica, system;}.uvtrend{margin-left: 135px; margin-top: 48px; z-index: 1; color: #fff;}.simsekcontainer{float: left; font-family: weathertext, system; -o-font-smoothing: antialiased; left: 0; bottom: 0; right: 0; position: relative; margin: 40px 10px 10px 40px; left: -10px; top: 13px;}.simsek{font-size: 1.55rem; padding-top: 12px; color: #f8f8f8; background: rgba(230, 161, 65, 1); border-bottom: 18px solid rgba(56, 56, 60, 1); align-items: center; justify-content: center; border-radius: 3px;}smalluvunit{font-size: 0.65rem; font-family: Arial, Helvetica, system;}sup{font-size: 1em;}supwm2{font-size: 0.7em; vertical-align: super;}.dvmconvertrain{position: relative; font-size: 0.5em; top: 10px; color: #c0c0c0; margin-left: 5px;}.hitempy{position: relative; background: rgba(61, 64, 66, 0.5); color: #aaa; width: 90px; padding: 1px; -webit-border-radius: 2px; border-radius: 2px; margin-top: -20px; margin-left: 92px; padding-left: 3px; line-height: 11px; font-size: 9px;}.actualt{position: relative; left: 0; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: teal; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: 100px; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: white; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}.actualw{position: relative; left: 5px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: rgba(74, 99, 111, 0.1); padding: 5px; font-family: Arial, Helvetica, sans-serif; width: 100px; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: #aaa; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}
    </style>';
}?>
<figure class="highcharts-figure">
    <div id="container"></div>
    <!--p class="highcharts-description">
        Heatmap with over 31 data points, visualizing the maximum temperature
        every day. The blue colors indicate colder days, and the
        orange colors indicate warmer days.
    </p-->
</figure>
<script>
const data = <?php echo $heatData;?>;
const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

// The function takes in a dataset and calculates how many empty tiles needed
// before and after the dataset is plotted.
function generateChartData(data) {

    // Calculate the starting weekday index (0-6 of the first date in the given
    // array)
    const firstWeekday = new Date(data[0].date).getDay(),
        monthLength = data.length,
        lastElement = data[monthLength - 1].date,
        lastWeekday = new Date(lastElement).getDay(),
        lengthOfWeek = 6,
        emptyTilesFirst = firstWeekday,
        chartData = [];

    // Add the empty tiles before the first day of the month with null values to
    // take up space in the chart
    for (let emptyDay = 0; emptyDay < emptyTilesFirst; emptyDay++) {
        chartData.push({
            x: emptyDay,
            y: 5,
            value: null,
            date: null,
            custom: {
                empty: true
            }
        });
    }

    // Loop through and populate with temperature and dates from the dataset
    for (let day = 1; day <= monthLength; day++) {
        // Get date from the given data array
        const date = data[day - 1].date;
        // Offset by thenumber of empty tiles
        const xCoordinate = (emptyTilesFirst + day - 1) % 7;
        const yCoordinate = Math.floor((firstWeekday + day - 1) / 7);
        const id = day;

        // Get the corresponding temperature for the current day from the given
        // array
        const temperature = data[day - 1].temperature;

        chartData.push({
            x: xCoordinate,
            y: 5 - yCoordinate,
            value: temperature,
            date: new Date(date).getTime(),
            custom: {
                monthDay: id
            }
        });
    }

    // Fill in the missing values when dataset is looped through.
    const emptyTilesLast = lengthOfWeek - lastWeekday;
    for (let emptyDay = 1; emptyDay <= emptyTilesLast; emptyDay++) {
        chartData.push({
            x: (lastWeekday + emptyDay) % 7,
            y: 0,
            value: null,
            date: null,
            custom: {
                empty: true
            }
        });
    }
    return chartData;
}
const chartData = generateChartData(data);

Highcharts.chart('container', {
    chart: {
        type: 'heatmap',
        height: 500,
        backgroundColor: 'transparent'
    },

    title: {
        text: 'Maximum Day Temperature (<?php echo $unitTemp;?>) in Steeple Claydon, UK Apr 2021',
        align: 'center'
    },

    subtitle: {
        text: 'Daily variation throughout the month',
        align: 'center'
    },

    accessibility: {
        landmarkVerbosity: 'one'
    },

    tooltip: {
        enabled: true,
        outside: true,
        zIndex: 20,
        headerFormat: '',
        pointFormat: '{%unless point.custom.empty}{point.date:%A, %e %b, ' +
            '%Y}{/unless}',
        nullFormat: 'No data'
    },

    xAxis: {
        categories: weekdays,
        opposite: true,
        lineWidth: 26,
        offset: 13,
        lineColor: 'rgba(27, 26, 37, 0.2)',
        labels: {
            rotation: 0,
            y: 20,
            style: {
                textTransform: 'uppercase',
                fontWeight: 'bold'
            }
        },
        accessibility: {
            description: 'weekdays',
            rangeDescription: 'X Axis is showing all 7 days of the week, ' +
                'starting with Sunday.'
        }
    },

    yAxis: {
        min: 0,
        max: 5,
        accessibility: {
            description: 'weeks'
        },
        visible: false
    },

    legend: {
        align: 'right',
        layout: 'vertical',
        verticalAlign: 'middle'
    },

    colorAxis: {
        min: 0,
        stops: [
            [0.2, 'lightblue'],
            [0.4, '#CBDFC8'],
            [0.6, '#F3E99E'],
            [0.9, '#F9A05C']
        ],
        labels: {
            format: '{value} \xb0'
        }
    },
    
    exporting: {
        enabled: false
    },


    series: [{
        keys: ['x', 'y', 'value', 'date', 'id'],
        data: chartData,
        nullColor: 'rgba(196, 196, 196, 0.2)',
        borderWidth: 2,
        borderColor: 'rgba(196, 196, 196, 0.2)',
        dataLabels: [{
            enabled: true,
            format: '{%unless point.custom.empty}{point.value:.1f}{/unless}',
            style: {
                textOutline: 'none',
                fontWeight: 'normal',
                fontSize: '1rem'
            },
            y: 4
        }, {
            enabled: true,
            align: 'left',
            verticalAlign: 'top',
            format: '{%unless ' +
                'point.custom.empty}{point.custom.monthDay}{/unless}',
            backgroundColor: 'whitesmoke',
            padding: 2,
            style: {
                textOutline: 'none',
                color: 'rgba(70, 70, 92, 1)',
                fontSize: '0.8rem',
                fontWeight: 'bold',
                opacity: 0.5
            },
            x: 1,
            y: 1
        }]
    }]
});

</script>
</html>