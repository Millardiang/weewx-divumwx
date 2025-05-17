<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8">
  <title>Steeple Claydon, UK</title>
  <style>
  #csv {
    display: none;
}

.highcharts-figure,
.highcharts-data-table table {
    min-width: 360px;
    max-width: 1000px;
    margin: 1em auto;
}

.highcharts-data-table table {
    font-family: Helvetica;
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
.highcharts-description {
    font-family: Helvetica;
    font-size: 10px;
    color: silver;
}
</style>
<?php
include("../fixedSettings.php");
include("select.php");
$unitTemp = "&deg;".$tempunit;
;?>
    <meta charset="UTF-8">
    <title>Steeple Claydon, UK Current Weather Conditions</title>
    <link rel="stylesheet" type="text/css" href="weewx.css"/>
    <link rel="icon" type="image/png" href="favicon.ico" />
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/heatmap.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/data.js"></script>
    <script src="https://code.highcharts.com/modules/boost-canvas.js"></script>
    <script src="https://code.highcharts.com/modules/boost.js"></script>
    <script src="../dvmhighcharts/scripts/divumwx-<?php echo $theme;?>.js"></script>
  </head>
<?php if ($theme === "dark") {
    echo '<style>@font-face{font-family: weathertext2; src: url(css/fonts/verbatim-regular.woff) format("woff"), url(fonts/verbatim-regular.woff2) format("woff2"), url(fonts/verbatim-regular.ttf) format("truetype");}html,body{font-size: 13px; font-family: "weathertext2", Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}/* unvisited link */a:link{color: white;}/* visited link */a:visited{color: white;}/* mouse over link */a:hover{color: white;}/* selected link */a:active{color: white;}.grid{display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 2fr)); grid-gap: 10px; align-items: stretch; color: #f5f7fc; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.grid > article{border: 1px solid #212428; box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.3); padding: 20px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; height: 90px;}.grid1{display: grid; grid-template-columns: repeat(auto-fill, minmax(100%, 1fr)); grid-gap: 5px; align-items: stretch; color: #f5f7fc; margin-top: 5px;}.grid1 > articlegraph{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; height: 300px;}.grid1 > articlegraph2{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background-color: lightgreen; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; height: 300px;}/* unvisited link */a:link{color: white;}/* visited link */a:visited{color: white;}/* mouse over link */a:hover{color: white;}/* selected link */a:active{color: white;}.divumwxdarkbrowser{position: relative; background: 0; width: 97%; height: 30px; margin: auto; margin-top: -5px; margin-left: 0px; border-top-left-radius: 5px; border-top-right-radius: 5px; padding-top: 10px;}.divumwxdarkbrowser[url]:after{content: attr(url); color: white; font-size: 14px; text-align: center; position: absolute; left: 0; right: 0; top: 0; padding: 4px 15px; margin: 11px 10px 0 auto; font-family: arial; height: 20px;}blue{color: #01a4b4;}orange{color: #009bb4;}orange1{color: rgba(255, 131, 47, 1);}green{color: #aaa;}red{color: #f37867;}red6{color: #d65b4a;}value{color: #fff;}yellow{color: #cc0;}purple{color: #916392;}.hitempyposx{position: relative; top: -90px; margin-left: 40px; margin-bottom: -30px;}.hitempypos{position: absolute; margin-top: -100px; margin-left: 40px; margin-bottom: 20px; display: block;}.hitempd{position: absolute; font-family: weathertext2, Arial, Helvetica, sans-serif; background: rgba(86, 95, 103, 0.3); color: #aaa; font-size: 0.7rem; width: 140px; padding: 0; margin-left: 30px; padding-left: 3px; align-items: center; justify-content: center; display: block; margin-top: 5px;}.hitempd1{position: absolute; font-family: weathertext2, Arial, Helvetica, sans-serif; background: rgba(86, 95, 103, 0.3); color: #aaa; font-size: 0.7rem; width: 140px; padding: 0; margin-left: 30px; padding-left: 3px; align-items: center; justify-content: center; display: block; margin-top: 40px; margin-bottom: 5px;}.uvmaxi3{position: absolute; left: -30px; color: rgba(0, 154, 171, 1); margin-top: -40px; font-size: 16px; width: 240px;}.uvmaxi3 span{color: #aaa;}.higust{position: relative; left: 0; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: rgba(74, 99, 111, 0.1); padding: 5px; font-family: Arial, Helvetica, sans-serif; width: 100px; height: 2em; font-size: 0.8rem; padding-top: 2px; color: #aaa; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}blue{color: rgba(0, 154, 171, 1);}.temperaturecontainer1{position: absolute; left: 20px; margin-top: -5px; margin-bottom: 20px;}.temperaturecontainer2{position: absolute; left: 20px; margin-top: 60px;}smalluvunit{font-size: 0.85rem; font-family: Arial, Helvetica, system;}.uvcontainer1{left: 70px; top: 0;}.uvtoday1,.uvtoday1-3,.uvtoday11,.uvtoday4-5,.uvtoday6-8,.uvtoday9-10{font-family: weathertext2, Arial, Helvetica, system; width: 5rem; height: 2.5rem; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; display: flex;}.uvtoday1,.uvtoday1-3,.uvtoday11,.uvtoday4-5,.uvtoday6-8,.uvtoday9-10{font-size: 1.25rem; padding-top: 2px; color: #fff; border-bottom: 5px solid rgba(56, 56, 60, 1); align-items: center; justify-content: center; border-radius: 3px; margin-bottom: 10px;}.uvcaution,.uvtrend{position: absolute; font-size: 1rem;}.uvtoday1,.uvtoday1-3{background: #9aba2f;}.uvtoday4-5{background: #ff7c39; background: -webkit-linear-gradient(90deg, #90b12a, #ff7c39); background: linear-gradient(90deg, #90b12a, #ff7c39);}.uvtoday6-8{background: #efa80f; background: -webkit-linear-gradient(90deg, #efa80f, #d86858); background: linear-gradient(90deg, #efa80f, #d86858);}.uvtoday9-10{background: #d05f2d; background: -webkit-linear-gradient(90deg, #d65b4a, #ac2816); background: linear-gradient(90deg, #d65b4a, #ac2816);}.uvtoday11{background: #95439f; background: -webkit-linear-gradient(90deg, #95439f, #a475cb); background: linear-gradient(90deg, #95439f, #a475cb);}.uvcaution{margin-left: 120px; margin-top: 112px; font-family: Arial, Helvetica, system;}.uvtrend{margin-left: 135px; margin-top: 48px; z-index: 1; color: #fff;}.simsekcontainer{float: left; font-family: weathertext, system; -o-font-smoothing: antialiased; left: 0; bottom: 0; right: 0; position: relative; margin: 40px 10px 10px 40px; left: -10px; top: 13px;}.simsek{font-size: 1.55rem; padding-top: 12px; color: #f8f8f8; background: rgba(230, 161, 65, 1); border-bottom: 18px solid rgba(56, 56, 60, 1); align-items: center; justify-content: center; border-radius: 3px;}smalluvunit{font-size: 0.65rem; font-family: Arial, Helvetica, system;}sup{font-size: 1em;}supwm2{font-size: 0.7em; vertical-align: super;}.dvmconvertrain{position: relative; font-size: 0.5em; top: 10px; color: #c0c0c0; margin-left: 5px;}.hitempy{position: relative; background: rgba(61, 64, 66, 0.5); color: #aaa; width: 90px; padding: 1px; -webit-border-radius: 2px; border-radius: 2px; margin-top: -20px; margin-left: 92px; padding-left: 3px; line-height: 11px; font-size: 9px;}.actualt{position: relative; left: 0; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: teal; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: 100px; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: white; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}.actualw{position: relative; left: 5px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: rgba(74, 99, 111, 0.1); padding: 5px; font-family: Arial, Helvetica, sans-serif; width: 100px; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: #aaa; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}
    </style>';
} elseif ($theme === "light") {
    echo '<style>@font-face{font-family: weathertext2; src: url(css/fonts/verbatim-regular.woff) format("woff"), url(fonts/verbatim-regular.woff2) format("woff2"), url(fonts/verbatim-regular.ttf) format("truetype");}html,body{font-size: 13px; font-family: "weathertext2", Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; background-color: white;}/* unvisited link */a:link{color: black;}/* visited link */a:visited{color: black;}/* mouse over link */a:hover{color: black;}/* selected link */a:active{color: black;}.grid{display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 2fr)); grid-gap: 10px; align-items: stretch; color: #f5f7fc; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;}.grid > article{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 20px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; height: 90px;}.grid1{display: grid; grid-template-columns: repeat(auto-fill, minmax(100%, 1fr)); grid-gap: 5px; align-items: stretch; color: black; margin-top: 5px;}.grid1 > articlegraph{border: 1px solid rgba(245, 247, 252, 0.02); box-shadow: 2px 2px 6px 0px rgba(0, 0, 0, 0.6); padding: 5px; font-size: 0.8em; -webkit-border-radius: 4px; border-radius: 4px; background: white; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; height: 300px;}.divumwxdarkbrowser{position: relative; background: 0; width: 97%; height: 30px; margin: auto; margin-top: -5px; margin-left: 0px; border-top-left-radius: 5px; border-top-right-radius: 5px; padding-top: 10px;}.divumwxdarkbrowser[url]:after{content: attr(url); color: black; font-size: 14px; text-align: center; position: absolute; left: 0; right: 0; top: 0; padding: 4px 15px; margin: 11px 10px 0 auto; font-family: arial; height: 20px;}blue{color: #01a4b4;}orange{color: #009bb4;}orange1{color: rgba(255, 131, 47, 1);}green{color: #aaa;}red{color: #f37867;}red6{color: #d65b4a;}value{color: #fff;}yellow{color: #cc0;}purple{color: #916392;}.hitempyposx{position: relative; top: -90px; margin-left: 40px; margin-bottom: -30px;}.hitempypos{position: absolute; margin-top: -100px; margin-left: 40px; margin-bottom: 20px; display: block;}.hitempd{position: absolute; font-family: weathertext2, Arial, Helvetica, sans-serif; background: rgba(86, 95, 103, 0.3); color: #aaa; font-size: 0.7rem; width: 140px; padding: 0; margin-left: 30px; padding-left: 3px; align-items: center; justify-content: center; display: block; margin-top: 5px;}.hitempd1{position: absolute; font-family: weathertext2, Arial, Helvetica, sans-serif; background: rgba(86, 95, 103, 0.3); color: #aaa; font-size: 0.7rem; width: 140px; padding: 0; margin-left: 30px; padding-left: 3px; align-items: center; justify-content: center; display: block; margin-top: 40px; margin-bottom: 5px;}.uvmaxi3{position: absolute; left: -30px; color: rgba(0, 154, 171, 1); margin-top: -40px; font-size: 16px; width: 240px;}.uvmaxi3 span{color: #aaa;}.higust{position: relative; left: 0; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: 0; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: 100px; height: 2em; font-size: 0.8rem; padding-top: 2px; color: black; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}blue{color: rgba(0, 154, 171, 1);}.temperaturecontainer1{position: absolute; left: 20px; margin-top: -5px; margin-bottom: 20px;}.temperaturecontainer2{position: absolute; left: 20px; margin-top: 60px;}smalluvunit{font-size: 0.85rem; font-family: Arial, Helvetica, system;}.uvcontainer1{left: 70px; top: 0;}.uvtoday1,.uvtoday1-3,.uvtoday11,.uvtoday4-5,.uvtoday6-8,.uvtoday9-10{font-family: weathertext2, Arial, Helvetica, system; width: 5rem; height: 2.5rem; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; display: flex;}.uvtoday1,.uvtoday1-3,.uvtoday11,.uvtoday4-5,.uvtoday6-8,.uvtoday9-10{font-size: 1.25rem; padding-top: 2px; color: #fff; border-bottom: 5px solid rgba(56, 56, 60, 1); align-items: center; justify-content: center; border-radius: 3px; margin-bottom: 10px;}.uvcaution,.uvtrend{position: absolute; font-size: 1rem;}.uvtoday1,.uvtoday1-3{background: #9aba2f;}.uvtoday4-5{background: #ff7c39; background: -webkit-linear-gradient(90deg, #90b12a, #ff7c39); background: linear-gradient(90deg, #90b12a, #ff7c39);}.uvtoday6-8{background: #efa80f; background: -webkit-linear-gradient(90deg, #efa80f, #d86858); background: linear-gradient(90deg, #efa80f, #d86858);}.uvtoday9-10{background: #d05f2d; background: -webkit-linear-gradient(90deg, #d65b4a, #ac2816); background: linear-gradient(90deg, #d65b4a, #ac2816);}.uvtoday11{background: #95439f; background: -webkit-linear-gradient(90deg, #95439f, #a475cb); background: linear-gradient(90deg, #95439f, #a475cb);}.uvcaution{margin-left: 120px; margin-top: 112px; font-family: Arial, Helvetica, system;}.uvtrend{margin-left: 135px; margin-top: 48px; z-index: 1; color: #fff;}.simsekcontainer{float: left; font-family: weathertext, system; -o-font-smoothing: antialiased; left: 0; bottom: 0; right: 0; position: relative; margin: 40px 10px 10px 40px; left: -10px; top: 13px;}.simsek{font-size: 1.55rem; padding-top: 12px; color: #f8f8f8; background: rgba(230, 161, 65, 1); border-bottom: 18px solid rgba(56, 56, 60, 1); align-items: center; justify-content: center; border-radius: 3px;}smalluvunit{font-size: 0.65rem; font-family: Arial, Helvetica, system;}sup{font-size: 1em;}supwm2{font-size: 0.7em; vertical-align: super;}.dvmconvertrain{position: relative; font-size: 0.5em; top: 10px; color: #c0c0c0; margin-left: 5px;}.hitempy{position: relative; background: rgba(61, 64, 66, 0.5); color: #aaa; width: 90px; padding: 1px; -webit-border-radius: 2px; border-radius: 2px; margin-top: -20px; margin-left: 92px; padding-left: 3px; line-height: 11px; font-size: 9px;}.actualt{position: relative; left: 0; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: teal; padding: 5px; font-family: Arial, Helvetica, sans-serif; width: 100px; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: white; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}.actualw{position: relative; left: 5px; -webkit-border-radius: 3px; -moz-border-radius: 3px; -o-border-radius: 3px; border-radius: 3px; background: rgba(74, 99, 111, 0.1); padding: 5px; font-family: Arial, Helvetica, sans-serif; width: 100px; height: 0.8em; font-size: 0.8rem; padding-top: 2px; color: #aaa; align-items: center; justify-content: center; margin-bottom: 10px; top: 0;}
    </style>';
}?>
  <body>
    <figure class="highcharts-figure">
    <div id="container"></div>
    <p class="highcharts-description">
    </p>
</figure>
<pre id="csv">Date,Time,Temperature
2020-01-01,0,   N/A
2020-01-01,1,   N/A
2020-01-01,2,   N/A
2020-01-01,3,   N/A
2020-01-01,4,   N/A
2020-01-01,5,   N/A
2020-01-01,6,   N/A
2020-01-01,7,   N/A
2020-01-01,8,   N/A
2020-01-01,9,   N/A
2020-01-01,10,   N/A
2020-01-01,11,   N/A
2020-01-01,12,   N/A
2020-01-01,13,   N/A
2020-01-01,14,   N/A
2020-01-01,15,   N/A
2020-01-01,16,   N/A
2020-01-01,17,   N/A
2020-01-01,18,   N/A
2020-01-01,19,   N/A
2020-01-01,20,   N/A
2020-01-01,21,   N/A
2020-01-01,22,   N/A
2020-01-01,23,   N/A
2020-01-02,0,   N/A
2020-01-02,1,   N/A
2020-01-02,2,   N/A
2020-01-02,3,   N/A
2020-01-02,4,   N/A
2020-01-02,5,   N/A
2020-01-02,6,   N/A
2020-01-02,7,   N/A
2020-01-02,8,   N/A
2020-01-02,9,   N/A
2020-01-02,10,   N/A
2020-01-02,11,   N/A
2020-01-02,12,   N/A
2020-01-02,13,   N/A
2020-01-02,14,   N/A
2020-01-02,15,   N/A
2020-01-02,16,   N/A
2020-01-02,17,   N/A
2020-01-02,18,   N/A
2020-01-02,19,   N/A
2020-01-02,20,   N/A
2020-01-02,21,   N/A
2020-01-02,22,   N/A
2020-01-02,23,   N/A
2020-01-03,0,   N/A
2020-01-03,1,   N/A
2020-01-03,2,   N/A
2020-01-03,3,   N/A
2020-01-03,4,   N/A
2020-01-03,5,   N/A
2020-01-03,6,   N/A
2020-01-03,7,   N/A
2020-01-03,8,   N/A
2020-01-03,9,   N/A
2020-01-03,10,   N/A
2020-01-03,11,   N/A
2020-01-03,12,   N/A
2020-01-03,13,   N/A
2020-01-03,14,   N/A
2020-01-03,15,   N/A
2020-01-03,16,   N/A
2020-01-03,17,   N/A
2020-01-03,18,   N/A
2020-01-03,19,   N/A
2020-01-03,20,   N/A
2020-01-03,21,   N/A
2020-01-03,22,   N/A
2020-01-03,23,   N/A
2020-01-04,0,   N/A
2020-01-04,1,   N/A
2020-01-04,2,   N/A
2020-01-04,3,   N/A
2020-01-04,4,   N/A
2020-01-04,5,   N/A
2020-01-04,6,   N/A
2020-01-04,7,   N/A
2020-01-04,8,   N/A
2020-01-04,9,   N/A
2020-01-04,10,   N/A
2020-01-04,11,   N/A
2020-01-04,12,   N/A
2020-01-04,13,   N/A
2020-01-04,14,   N/A
2020-01-04,15,   N/A
2020-01-04,16,   N/A
2020-01-04,17,   N/A
2020-01-04,18,   N/A
2020-01-04,19,   N/A
2020-01-04,20,   N/A
2020-01-04,21,   N/A
2020-01-04,22,   N/A
2020-01-04,23,   N/A
2020-01-05,0,   N/A
2020-01-05,1,   N/A
2020-01-05,2,   N/A
2020-01-05,3,   N/A
2020-01-05,4,   N/A
2020-01-05,5,   N/A
2020-01-05,6,   N/A
2020-01-05,7,   N/A
2020-01-05,8,   N/A
2020-01-05,9,   N/A
2020-01-05,10,   N/A
2020-01-05,11,   N/A
2020-01-05,12,   N/A
2020-01-05,13,   N/A
2020-01-05,14,   N/A
2020-01-05,15,   N/A
2020-01-05,16,   N/A
2020-01-05,17,   N/A
2020-01-05,18,   N/A
2020-01-05,19,   N/A
2020-01-05,20,   N/A
2020-01-05,21,   N/A
2020-01-05,22,   N/A
2020-01-05,23,   N/A
2020-01-06,0,   N/A
2020-01-06,1,   N/A
2020-01-06,2,   N/A
2020-01-06,3,   N/A
2020-01-06,4,   N/A
2020-01-06,5,   N/A
2020-01-06,6,   N/A
2020-01-06,7,   N/A
2020-01-06,8,   N/A
2020-01-06,9,   N/A
2020-01-06,10,   N/A
2020-01-06,11,   N/A
2020-01-06,12,   N/A
2020-01-06,13,   N/A
2020-01-06,14,   N/A
2020-01-06,15,   N/A
2020-01-06,16,   N/A
2020-01-06,17,   N/A
2020-01-06,18,   N/A
2020-01-06,19,   N/A
2020-01-06,20,   N/A
2020-01-06,21,   N/A
2020-01-06,22,   N/A
2020-01-06,23,   N/A
2020-01-07,0,   N/A
2020-01-07,1,   N/A
2020-01-07,2,   N/A
2020-01-07,3,   N/A
2020-01-07,4,   N/A
2020-01-07,5,   N/A
2020-01-07,6,   N/A
2020-01-07,7,   N/A
2020-01-07,8,   N/A
2020-01-07,9,   N/A
2020-01-07,10,   N/A
2020-01-07,11,   N/A
2020-01-07,12,   N/A
2020-01-07,13,   N/A
2020-01-07,14,   N/A
2020-01-07,15,   N/A
2020-01-07,16,   N/A
2020-01-07,17,   N/A
2020-01-07,18,   N/A
2020-01-07,19,   N/A
2020-01-07,20,   N/A
2020-01-07,21,   N/A
2020-01-07,22,   N/A
2020-01-07,23,   N/A
2020-01-08,0,   N/A
2020-01-08,1,   N/A
2020-01-08,2,   N/A
2020-01-08,3,   N/A
2020-01-08,4,   N/A
2020-01-08,5,   N/A
2020-01-08,6,   N/A
2020-01-08,7,   N/A
2020-01-08,8,   N/A
2020-01-08,9,   N/A
2020-01-08,10,   N/A
2020-01-08,11,   N/A
2020-01-08,12,   N/A
2020-01-08,13,   N/A
2020-01-08,14,   N/A
2020-01-08,15,   N/A
2020-01-08,16,   N/A
2020-01-08,17,   N/A
2020-01-08,18,   N/A
2020-01-08,19,   N/A
2020-01-08,20,   N/A
2020-01-08,21,   N/A
2020-01-08,22,   N/A
2020-01-08,23,   N/A
2020-01-09,0,   N/A
2020-01-09,1,   N/A
2020-01-09,2,   N/A
2020-01-09,3,   N/A
2020-01-09,4,   N/A
2020-01-09,5,   N/A
2020-01-09,6,   N/A
2020-01-09,7,   N/A
2020-01-09,8,   N/A
2020-01-09,9,   N/A
2020-01-09,10,   N/A
2020-01-09,11,   N/A
2020-01-09,12,   N/A
2020-01-09,13,   N/A
2020-01-09,14,   N/A
2020-01-09,15,   N/A
2020-01-09,16,   N/A
2020-01-09,17,   N/A
2020-01-09,18,   N/A
2020-01-09,19,   N/A
2020-01-09,20,   N/A
2020-01-09,21,   N/A
2020-01-09,22,   N/A
2020-01-09,23,   N/A
2020-01-10,0,   N/A
2020-01-10,1,   N/A
2020-01-10,2,   N/A
2020-01-10,3,   N/A
2020-01-10,4,   N/A
2020-01-10,5,   N/A
2020-01-10,6,   N/A
2020-01-10,7,   N/A
2020-01-10,8,   N/A
2020-01-10,9,   N/A
2020-01-10,10,   N/A
2020-01-10,11,   N/A
2020-01-10,12,   N/A
2020-01-10,13,   N/A
2020-01-10,14,   N/A
2020-01-10,15,   N/A
2020-01-10,16,   N/A
2020-01-10,17,   N/A
2020-01-10,18,   N/A
2020-01-10,19,   N/A
2020-01-10,20,   N/A
2020-01-10,21,   N/A
2020-01-10,22,   N/A
2020-01-10,23,   N/A
2020-01-11,0,   N/A
2020-01-11,1,   N/A
2020-01-11,2,   N/A
2020-01-11,3,   N/A
2020-01-11,4,   N/A
2020-01-11,5,   N/A
2020-01-11,6,   N/A
2020-01-11,7,   N/A
2020-01-11,8,   N/A
2020-01-11,9,   N/A
2020-01-11,10,   N/A
2020-01-11,11,   N/A
2020-01-11,12,   N/A
2020-01-11,13,   N/A
2020-01-11,14,   N/A
2020-01-11,15,   N/A
2020-01-11,16,   N/A
2020-01-11,17,   N/A
2020-01-11,18,   N/A
2020-01-11,19,   N/A
2020-01-11,20,   N/A
2020-01-11,21,   N/A
2020-01-11,22,   N/A
2020-01-11,23,   N/A
2020-01-12,0,   N/A
2020-01-12,1,   N/A
2020-01-12,2,   N/A
2020-01-12,3,   N/A
2020-01-12,4,   N/A
2020-01-12,5,   N/A
2020-01-12,6,   N/A
2020-01-12,7,   N/A
2020-01-12,8,   N/A
2020-01-12,9,   N/A
2020-01-12,10,   N/A
2020-01-12,11,   N/A
2020-01-12,12,   N/A
2020-01-12,13,   N/A
2020-01-12,14,   N/A
2020-01-12,15,   N/A
2020-01-12,16,   N/A
2020-01-12,17,   N/A
2020-01-12,18,   N/A
2020-01-12,19,   N/A
2020-01-12,20,   N/A
2020-01-12,21,   N/A
2020-01-12,22,   N/A
2020-01-12,23,   N/A
2020-01-13,0,   N/A
2020-01-13,1,   N/A
2020-01-13,2,   N/A
2020-01-13,3,   N/A
2020-01-13,4,   N/A
2020-01-13,5,   N/A
2020-01-13,6,   N/A
2020-01-13,7,   N/A
2020-01-13,8,   N/A
2020-01-13,9,   N/A
2020-01-13,10,   N/A
2020-01-13,11,   N/A
2020-01-13,12,   N/A
2020-01-13,13,   N/A
2020-01-13,14,   N/A
2020-01-13,15,   N/A
2020-01-13,16,   N/A
2020-01-13,17,   N/A
2020-01-13,18,   N/A
2020-01-13,19,   N/A
2020-01-13,20,   N/A
2020-01-13,21,   N/A
2020-01-13,22,   N/A
2020-01-13,23,   N/A
2020-01-14,0,   N/A
2020-01-14,1,   N/A
2020-01-14,2,   N/A
2020-01-14,3,   N/A
2020-01-14,4,   N/A
2020-01-14,5,   N/A
2020-01-14,6,   N/A
2020-01-14,7,   N/A
2020-01-14,8,   N/A
2020-01-14,9,   N/A
2020-01-14,10,   N/A
2020-01-14,11,   N/A
2020-01-14,12,   N/A
2020-01-14,13,   N/A
2020-01-14,14,   N/A
2020-01-14,15,   N/A
2020-01-14,16,   N/A
2020-01-14,17,   N/A
2020-01-14,18,   N/A
2020-01-14,19,   N/A
2020-01-14,20,   N/A
2020-01-14,21,   N/A
2020-01-14,22,   N/A
2020-01-14,23,   N/A
2020-01-15,0,   N/A
2020-01-15,1,   N/A
2020-01-15,2,   N/A
2020-01-15,3,   N/A
2020-01-15,4,   N/A
2020-01-15,5,   N/A
2020-01-15,6,   N/A
2020-01-15,7,   N/A
2020-01-15,8,   N/A
2020-01-15,9,   N/A
2020-01-15,10,   N/A
2020-01-15,11,   N/A
2020-01-15,12,   N/A
2020-01-15,13,   N/A
2020-01-15,14,   N/A
2020-01-15,15,   N/A
2020-01-15,16,   N/A
2020-01-15,17,   N/A
2020-01-15,18,   N/A
2020-01-15,19,   N/A
2020-01-15,20,   N/A
2020-01-15,21,   N/A
2020-01-15,22,   N/A
2020-01-15,23,   N/A
2020-01-16,0,   N/A
2020-01-16,1,   N/A
2020-01-16,2,   N/A
2020-01-16,3,   N/A
2020-01-16,4,   N/A
2020-01-16,5,   N/A
2020-01-16,6,   N/A
2020-01-16,7,   N/A
2020-01-16,8,   N/A
2020-01-16,9,   N/A
2020-01-16,10,   N/A
2020-01-16,11,   N/A
2020-01-16,12,   N/A
2020-01-16,13,   N/A
2020-01-16,14,   N/A
2020-01-16,15,   N/A
2020-01-16,16,   N/A
2020-01-16,17,   N/A
2020-01-16,18,   N/A
2020-01-16,19,   N/A
2020-01-16,20,   N/A
2020-01-16,21,   N/A
2020-01-16,22,   N/A
2020-01-16,23,   N/A
2020-01-17,0,   N/A
2020-01-17,1,   N/A
2020-01-17,2,   N/A
2020-01-17,3,   N/A
2020-01-17,4,   N/A
2020-01-17,5,   N/A
2020-01-17,6,   N/A
2020-01-17,7,   N/A
2020-01-17,8,   N/A
2020-01-17,9,   N/A
2020-01-17,10,   N/A
2020-01-17,11,   N/A
2020-01-17,12,   N/A
2020-01-17,13,   N/A
2020-01-17,14,   N/A
2020-01-17,15,   N/A
2020-01-17,16,   N/A
2020-01-17,17,   N/A
2020-01-17,18,   N/A
2020-01-17,19,   N/A
2020-01-17,20,   N/A
2020-01-17,21,   N/A
2020-01-17,22,   N/A
2020-01-17,23,   N/A
2020-01-18,0,   N/A
2020-01-18,1,   N/A
2020-01-18,2,   N/A
2020-01-18,3,   N/A
2020-01-18,4,   N/A
2020-01-18,5,   N/A
2020-01-18,6,   N/A
2020-01-18,7,   N/A
2020-01-18,8,   N/A
2020-01-18,9,   N/A
2020-01-18,10,   N/A
2020-01-18,11,   N/A
2020-01-18,12,   N/A
2020-01-18,13,   N/A
2020-01-18,14,   N/A
2020-01-18,15,   N/A
2020-01-18,16,   N/A
2020-01-18,17,   N/A
2020-01-18,18,   N/A
2020-01-18,19,   N/A
2020-01-18,20,   N/A
2020-01-18,21,   N/A
2020-01-18,22,   N/A
2020-01-18,23,   N/A
2020-01-19,0,   N/A
2020-01-19,1,   N/A
2020-01-19,2,   N/A
2020-01-19,3,   N/A
2020-01-19,4,   N/A
2020-01-19,5,   N/A
2020-01-19,6,   N/A
2020-01-19,7,   N/A
2020-01-19,8,   N/A
2020-01-19,9,   N/A
2020-01-19,10,   N/A
2020-01-19,11,   N/A
2020-01-19,12,   N/A
2020-01-19,13,   N/A
2020-01-19,14,   N/A
2020-01-19,15,   N/A
2020-01-19,16,   N/A
2020-01-19,17,   N/A
2020-01-19,18,   N/A
2020-01-19,19,   N/A
2020-01-19,20,   N/A
2020-01-19,21,   N/A
2020-01-19,22,   N/A
2020-01-19,23,   N/A
2020-01-20,0,   N/A
2020-01-20,1,   N/A
2020-01-20,2,   N/A
2020-01-20,3,   N/A
2020-01-20,4,   N/A
2020-01-20,5,   N/A
2020-01-20,6,   N/A
2020-01-20,7,   N/A
2020-01-20,8,   N/A
2020-01-20,9,   N/A
2020-01-20,10,   N/A
2020-01-20,11,   N/A
2020-01-20,12,   N/A
2020-01-20,13,   N/A
2020-01-20,14,   N/A
2020-01-20,15,   N/A
2020-01-20,16,   N/A
2020-01-20,17,   N/A
2020-01-20,18,   N/A
2020-01-20,19,   N/A
2020-01-20,20,   N/A
2020-01-20,21,   N/A
2020-01-20,22,   N/A
2020-01-20,23,   N/A
2020-01-21,0,   N/A
2020-01-21,1,   N/A
2020-01-21,2,   N/A
2020-01-21,3,   N/A
2020-01-21,4,   N/A
2020-01-21,5,   N/A
2020-01-21,6,   N/A
2020-01-21,7,   N/A
2020-01-21,8,   N/A
2020-01-21,9,   N/A
2020-01-21,10,   N/A
2020-01-21,11,   N/A
2020-01-21,12,   N/A
2020-01-21,13,   N/A
2020-01-21,14,   N/A
2020-01-21,15,   N/A
2020-01-21,16,   N/A
2020-01-21,17,   N/A
2020-01-21,18,   N/A
2020-01-21,19,   N/A
2020-01-21,20,   N/A
2020-01-21,21,   N/A
2020-01-21,22,   N/A
2020-01-21,23,   N/A
2020-01-22,0,   N/A
2020-01-22,1,   N/A
2020-01-22,2,   N/A
2020-01-22,3,   N/A
2020-01-22,4,   N/A
2020-01-22,5,   N/A
2020-01-22,6,   N/A
2020-01-22,7,   N/A
2020-01-22,8,   N/A
2020-01-22,9,   N/A
2020-01-22,10,   N/A
2020-01-22,11,   N/A
2020-01-22,12,   N/A
2020-01-22,13,   N/A
2020-01-22,14,   N/A
2020-01-22,15,   N/A
2020-01-22,16,   N/A
2020-01-22,17,   N/A
2020-01-22,18,   N/A
2020-01-22,19,   N/A
2020-01-22,20,   N/A
2020-01-22,21,   N/A
2020-01-22,22,   N/A
2020-01-22,23,   N/A
2020-01-23,0,   N/A
2020-01-23,1,   N/A
2020-01-23,2,   N/A
2020-01-23,3,   N/A
2020-01-23,4,   N/A
2020-01-23,5,   N/A
2020-01-23,6,   N/A
2020-01-23,7,   N/A
2020-01-23,8,   N/A
2020-01-23,9,   N/A
2020-01-23,10,   N/A
2020-01-23,11,   N/A
2020-01-23,12,   N/A
2020-01-23,13,   N/A
2020-01-23,14,   N/A
2020-01-23,15,   N/A
2020-01-23,16,   N/A
2020-01-23,17,   N/A
2020-01-23,18,   N/A
2020-01-23,19,   N/A
2020-01-23,20,   N/A
2020-01-23,21,   N/A
2020-01-23,22,   N/A
2020-01-23,23,   N/A
2020-01-24,0,   N/A
2020-01-24,1,   N/A
2020-01-24,2,   N/A
2020-01-24,3,   N/A
2020-01-24,4,   N/A
2020-01-24,5,   N/A
2020-01-24,6,   N/A
2020-01-24,7,   N/A
2020-01-24,8,   N/A
2020-01-24,9,   N/A
2020-01-24,10,   N/A
2020-01-24,11,   N/A
2020-01-24,12,   N/A
2020-01-24,13,   N/A
2020-01-24,14,   N/A
2020-01-24,15,   N/A
2020-01-24,16,   N/A
2020-01-24,17,   N/A
2020-01-24,18,   N/A
2020-01-24,19,   N/A
2020-01-24,20,   N/A
2020-01-24,21,   N/A
2020-01-24,22,   N/A
2020-01-24,23,   N/A
2020-01-25,0,   N/A
2020-01-25,1,   N/A
2020-01-25,2,   N/A
2020-01-25,3,   N/A
2020-01-25,4,   N/A
2020-01-25,5,   N/A
2020-01-25,6,   N/A
2020-01-25,7,   N/A
2020-01-25,8,   N/A
2020-01-25,9,   N/A
2020-01-25,10,   N/A
2020-01-25,11,   N/A
2020-01-25,12,   N/A
2020-01-25,13,   N/A
2020-01-25,14,   N/A
2020-01-25,15,   N/A
2020-01-25,16,   N/A
2020-01-25,17,   N/A
2020-01-25,18,   N/A
2020-01-25,19,   N/A
2020-01-25,20,   N/A
2020-01-25,21,   N/A
2020-01-25,22,   N/A
2020-01-25,23,   N/A
2020-01-26,0,   N/A
2020-01-26,1,   N/A
2020-01-26,2,   N/A
2020-01-26,3,   N/A
2020-01-26,4,   N/A
2020-01-26,5,   N/A
2020-01-26,6,   N/A
2020-01-26,7,   N/A
2020-01-26,8,   N/A
2020-01-26,9,   N/A
2020-01-26,10,   N/A
2020-01-26,11,   N/A
2020-01-26,12,   N/A
2020-01-26,13,   N/A
2020-01-26,14,   N/A
2020-01-26,15,   N/A
2020-01-26,16,   N/A
2020-01-26,17,   N/A
2020-01-26,18,   N/A
2020-01-26,19,   N/A
2020-01-26,20,   N/A
2020-01-26,21,   N/A
2020-01-26,22,   N/A
2020-01-26,23,   N/A
2020-01-27,0,   N/A
2020-01-27,1,   N/A
2020-01-27,2,   N/A
2020-01-27,3,   N/A
2020-01-27,4,   N/A
2020-01-27,5,   N/A
2020-01-27,6,   N/A
2020-01-27,7,   N/A
2020-01-27,8,   N/A
2020-01-27,9,   N/A
2020-01-27,10,   N/A
2020-01-27,11,   N/A
2020-01-27,12,   N/A
2020-01-27,13,   N/A
2020-01-27,14,   N/A
2020-01-27,15,   N/A
2020-01-27,16,   N/A
2020-01-27,17,   N/A
2020-01-27,18,   N/A
2020-01-27,19,   N/A
2020-01-27,20,   N/A
2020-01-27,21,   N/A
2020-01-27,22,   N/A
2020-01-27,23,   N/A
2020-01-28,0,   N/A
2020-01-28,1,   N/A
2020-01-28,2,   N/A
2020-01-28,3,   N/A
2020-01-28,4,   N/A
2020-01-28,5,   N/A
2020-01-28,6,   N/A
2020-01-28,7,   N/A
2020-01-28,8,   N/A
2020-01-28,9,   N/A
2020-01-28,10,   N/A
2020-01-28,11,   N/A
2020-01-28,12,   N/A
2020-01-28,13,   N/A
2020-01-28,14,   N/A
2020-01-28,15,   N/A
2020-01-28,16,   N/A
2020-01-28,17,   N/A
2020-01-28,18,   N/A
2020-01-28,19,   N/A
2020-01-28,20,   N/A
2020-01-28,21,   N/A
2020-01-28,22,   N/A
2020-01-28,23,   N/A
2020-01-29,0,   N/A
2020-01-29,1,   N/A
2020-01-29,2,   N/A
2020-01-29,3,   N/A
2020-01-29,4,   N/A
2020-01-29,5,   N/A
2020-01-29,6,   N/A
2020-01-29,7,   N/A
2020-01-29,8,   N/A
2020-01-29,9,   N/A
2020-01-29,10,   N/A
2020-01-29,11,   N/A
2020-01-29,12,   N/A
2020-01-29,13,   N/A
2020-01-29,14,   N/A
2020-01-29,15,   N/A
2020-01-29,16,   N/A
2020-01-29,17,   N/A
2020-01-29,18,   N/A
2020-01-29,19,   N/A
2020-01-29,20,   N/A
2020-01-29,21,   N/A
2020-01-29,22,   N/A
2020-01-29,23,   N/A
2020-01-30,0,   N/A
2020-01-30,1,   N/A
2020-01-30,2,   N/A
2020-01-30,3,   N/A
2020-01-30,4,   N/A
2020-01-30,5,   N/A
2020-01-30,6,   N/A
2020-01-30,7,   N/A
2020-01-30,8,   N/A
2020-01-30,9,   N/A
2020-01-30,10,   N/A
2020-01-30,11,   N/A
2020-01-30,12,   N/A
2020-01-30,13,   N/A
2020-01-30,14,   N/A
2020-01-30,15,   N/A
2020-01-30,16,   N/A
2020-01-30,17,   N/A
2020-01-30,18,   N/A
2020-01-30,19,   N/A
2020-01-30,20,   N/A
2020-01-30,21,   N/A
2020-01-30,22,   N/A
2020-01-30,23,   N/A
2020-01-31,0,   N/A
2020-01-31,1,   N/A
2020-01-31,2,   N/A
2020-01-31,3,   N/A
2020-01-31,4,   N/A
2020-01-31,5,   N/A
2020-01-31,6,   N/A
2020-01-31,7,   N/A
2020-01-31,8,   N/A
2020-01-31,9,   N/A
2020-01-31,10,   N/A
2020-01-31,11,   N/A
2020-01-31,12,   N/A
2020-01-31,13,   N/A
2020-01-31,14,   N/A
2020-01-31,15,   N/A
2020-01-31,16,   N/A
2020-01-31,17,   N/A
2020-01-31,18,   N/A
2020-01-31,19,   N/A
2020-01-31,20,   N/A
2020-01-31,21,   N/A
2020-01-31,22,   N/A
2020-01-31,23,   N/A
2020-02-01,0,   N/A
2020-02-01,1,   N/A
2020-02-01,2,   N/A
2020-02-01,3,   N/A
2020-02-01,4,   N/A
2020-02-01,5,   N/A
2020-02-01,6,   N/A
2020-02-01,7,   N/A
2020-02-01,8,   N/A
2020-02-01,9,   N/A
2020-02-01,10,   N/A
2020-02-01,11,   N/A
2020-02-01,12,   N/A
2020-02-01,13,   N/A
2020-02-01,14,   N/A
2020-02-01,15,   N/A
2020-02-01,16,   N/A
2020-02-01,17,   N/A
2020-02-01,18,   N/A
2020-02-01,19,   N/A
2020-02-01,20,   N/A
2020-02-01,21,   N/A
2020-02-01,22,   N/A
2020-02-01,23,   N/A
2020-02-02,0,   N/A
2020-02-02,1,   N/A
2020-02-02,2,   N/A
2020-02-02,3,   N/A
2020-02-02,4,   N/A
2020-02-02,5,   N/A
2020-02-02,6,   N/A
2020-02-02,7,   N/A
2020-02-02,8,   N/A
2020-02-02,9,   N/A
2020-02-02,10,   N/A
2020-02-02,11,   N/A
2020-02-02,12,   N/A
2020-02-02,13,   N/A
2020-02-02,14,   N/A
2020-02-02,15,   N/A
2020-02-02,16,   N/A
2020-02-02,17,   N/A
2020-02-02,18,   N/A
2020-02-02,19,   N/A
2020-02-02,20,   N/A
2020-02-02,21,   N/A
2020-02-02,22,   N/A
2020-02-02,23,   N/A
2020-02-03,0,   N/A
2020-02-03,1,   N/A
2020-02-03,2,   N/A
2020-02-03,3,   N/A
2020-02-03,4,   N/A
2020-02-03,5,   N/A
2020-02-03,6,   N/A
2020-02-03,7,   N/A
2020-02-03,8,   N/A
2020-02-03,9,   N/A
2020-02-03,10,   N/A
2020-02-03,11,   N/A
2020-02-03,12,   N/A
2020-02-03,13,   N/A
2020-02-03,14,   N/A
2020-02-03,15,   N/A
2020-02-03,16,   N/A
2020-02-03,17,   N/A
2020-02-03,18,   N/A
2020-02-03,19,   N/A
2020-02-03,20,   N/A
2020-02-03,21,   N/A
2020-02-03,22,   N/A
2020-02-03,23,   N/A
2020-02-04,0,   N/A
2020-02-04,1,   N/A
2020-02-04,2,   N/A
2020-02-04,3,   N/A
2020-02-04,4,   N/A
2020-02-04,5,   N/A
2020-02-04,6,   N/A
2020-02-04,7,   N/A
2020-02-04,8,   N/A
2020-02-04,9,   N/A
2020-02-04,10,   N/A
2020-02-04,11,   N/A
2020-02-04,12,   N/A
2020-02-04,13,   N/A
2020-02-04,14,   N/A
2020-02-04,15,   N/A
2020-02-04,16,   N/A
2020-02-04,17,   N/A
2020-02-04,18,   N/A
2020-02-04,19,   N/A
2020-02-04,20,   N/A
2020-02-04,21,   N/A
2020-02-04,22,   N/A
2020-02-04,23,   N/A
2020-02-05,0,   N/A
2020-02-05,1,   N/A
2020-02-05,2,   N/A
2020-02-05,3,   N/A
2020-02-05,4,   N/A
2020-02-05,5,   N/A
2020-02-05,6,   N/A
2020-02-05,7,   N/A
2020-02-05,8,   N/A
2020-02-05,9,   N/A
2020-02-05,10,   N/A
2020-02-05,11,   N/A
2020-02-05,12,   N/A
2020-02-05,13,   N/A
2020-02-05,14,   N/A
2020-02-05,15,   N/A
2020-02-05,16,   N/A
2020-02-05,17,   N/A
2020-02-05,18,   N/A
2020-02-05,19,   N/A
2020-02-05,20,   N/A
2020-02-05,21,   N/A
2020-02-05,22,   N/A
2020-02-05,23,   N/A
2020-02-06,0,   N/A
2020-02-06,1,   N/A
2020-02-06,2,   N/A
2020-02-06,3,   N/A
2020-02-06,4,   N/A
2020-02-06,5,   N/A
2020-02-06,6,   N/A
2020-02-06,7,   N/A
2020-02-06,8,   N/A
2020-02-06,9,   N/A
2020-02-06,10,   N/A
2020-02-06,11,   N/A
2020-02-06,12,   N/A
2020-02-06,13,   N/A
2020-02-06,14,   N/A
2020-02-06,15,   N/A
2020-02-06,16,   N/A
2020-02-06,17,   N/A
2020-02-06,18,   N/A
2020-02-06,19,   N/A
2020-02-06,20,   N/A
2020-02-06,21,   N/A
2020-02-06,22,   N/A
2020-02-06,23,   N/A
2020-02-07,0,   N/A
2020-02-07,1,   N/A
2020-02-07,2,   N/A
2020-02-07,3,   N/A
2020-02-07,4,   N/A
2020-02-07,5,   N/A
2020-02-07,6,   N/A
2020-02-07,7,   N/A
2020-02-07,8,   N/A
2020-02-07,9,   N/A
2020-02-07,10,   N/A
2020-02-07,11,   N/A
2020-02-07,12,   N/A
2020-02-07,13,   N/A
2020-02-07,14,   N/A
2020-02-07,15,   N/A
2020-02-07,16,   N/A
2020-02-07,17,   N/A
2020-02-07,18,   N/A
2020-02-07,19,   N/A
2020-02-07,20,   N/A
2020-02-07,21,   N/A
2020-02-07,22,   N/A
2020-02-07,23,   N/A
2020-02-08,0,   N/A
2020-02-08,1,   N/A
2020-02-08,2,   N/A
2020-02-08,3,   N/A
2020-02-08,4,   N/A
2020-02-08,5,   N/A
2020-02-08,6,   N/A
2020-02-08,7,   N/A
2020-02-08,8,   N/A
2020-02-08,9,   N/A
2020-02-08,10,   N/A
2020-02-08,11,   N/A
2020-02-08,12,   N/A
2020-02-08,13,   N/A
2020-02-08,14,   N/A
2020-02-08,15,   N/A
2020-02-08,16,   N/A
2020-02-08,17,   N/A
2020-02-08,18,   N/A
2020-02-08,19,   N/A
2020-02-08,20,   N/A
2020-02-08,21,   N/A
2020-02-08,22,   N/A
2020-02-08,23,   N/A
2020-02-09,0,   N/A
2020-02-09,1,   N/A
2020-02-09,2,   N/A
2020-02-09,3,   N/A
2020-02-09,4,   N/A
2020-02-09,5,   N/A
2020-02-09,6,   N/A
2020-02-09,7,   N/A
2020-02-09,8,   N/A
2020-02-09,9,   N/A
2020-02-09,10,   N/A
2020-02-09,11,   N/A
2020-02-09,12,   N/A
2020-02-09,13,   N/A
2020-02-09,14,   N/A
2020-02-09,15,   N/A
2020-02-09,16,   N/A
2020-02-09,17,   N/A
2020-02-09,18,   N/A
2020-02-09,19,   N/A
2020-02-09,20,   N/A
2020-02-09,21,   N/A
2020-02-09,22,   N/A
2020-02-09,23,   N/A
2020-02-10,0,   N/A
2020-02-10,1,   N/A
2020-02-10,2,   N/A
2020-02-10,3,   N/A
2020-02-10,4,   N/A
2020-02-10,5,   N/A
2020-02-10,6,   N/A
2020-02-10,7,   N/A
2020-02-10,8,   N/A
2020-02-10,9,   N/A
2020-02-10,10,   N/A
2020-02-10,11,   N/A
2020-02-10,12,   N/A
2020-02-10,13,   N/A
2020-02-10,14,   N/A
2020-02-10,15,   N/A
2020-02-10,16,   N/A
2020-02-10,17,   N/A
2020-02-10,18,   N/A
2020-02-10,19,   N/A
2020-02-10,20,   N/A
2020-02-10,21,   N/A
2020-02-10,22,   N/A
2020-02-10,23,   N/A
2020-02-11,0,   N/A
2020-02-11,1,   N/A
2020-02-11,2,   N/A
2020-02-11,3,   N/A
2020-02-11,4,   N/A
2020-02-11,5,   N/A
2020-02-11,6,   N/A
2020-02-11,7,   N/A
2020-02-11,8,   N/A
2020-02-11,9,   N/A
2020-02-11,10,   N/A
2020-02-11,11,   N/A
2020-02-11,12,   N/A
2020-02-11,13,   N/A
2020-02-11,14,   N/A
2020-02-11,15,   N/A
2020-02-11,16,   N/A
2020-02-11,17,   N/A
2020-02-11,18,   N/A
2020-02-11,19,   N/A
2020-02-11,20,   N/A
2020-02-11,21,   N/A
2020-02-11,22,   N/A
2020-02-11,23,   N/A
2020-02-12,0,   N/A
2020-02-12,1,   N/A
2020-02-12,2,   N/A
2020-02-12,3,   N/A
2020-02-12,4,   N/A
2020-02-12,5,   N/A
2020-02-12,6,   N/A
2020-02-12,7,   N/A
2020-02-12,8,   N/A
2020-02-12,9,   N/A
2020-02-12,10,   N/A
2020-02-12,11,   N/A
2020-02-12,12,   N/A
2020-02-12,13,   N/A
2020-02-12,14,   N/A
2020-02-12,15,   N/A
2020-02-12,16,   N/A
2020-02-12,17,   N/A
2020-02-12,18,   N/A
2020-02-12,19,   N/A
2020-02-12,20,   N/A
2020-02-12,21,   N/A
2020-02-12,22,   N/A
2020-02-12,23,   N/A
2020-02-13,0,   N/A
2020-02-13,1,   N/A
2020-02-13,2,   N/A
2020-02-13,3,   N/A
2020-02-13,4,   N/A
2020-02-13,5,   N/A
2020-02-13,6,   N/A
2020-02-13,7,   N/A
2020-02-13,8,   N/A
2020-02-13,9,   N/A
2020-02-13,10,   N/A
2020-02-13,11,   N/A
2020-02-13,12,   N/A
2020-02-13,13,   N/A
2020-02-13,14,   N/A
2020-02-13,15,   N/A
2020-02-13,16,   N/A
2020-02-13,17,   N/A
2020-02-13,18,   N/A
2020-02-13,19,   N/A
2020-02-13,20,   N/A
2020-02-13,21,   N/A
2020-02-13,22,   N/A
2020-02-13,23,   N/A
2020-02-14,0,   N/A
2020-02-14,1,   N/A
2020-02-14,2,   N/A
2020-02-14,3,   N/A
2020-02-14,4,   N/A
2020-02-14,5,   N/A
2020-02-14,6,   N/A
2020-02-14,7,   N/A
2020-02-14,8,   N/A
2020-02-14,9,   N/A
2020-02-14,10,   N/A
2020-02-14,11,   N/A
2020-02-14,12,   N/A
2020-02-14,13,   N/A
2020-02-14,14,   N/A
2020-02-14,15,   N/A
2020-02-14,16,   N/A
2020-02-14,17,   N/A
2020-02-14,18,   N/A
2020-02-14,19,   N/A
2020-02-14,20,   N/A
2020-02-14,21,   N/A
2020-02-14,22,   N/A
2020-02-14,23,   N/A
2020-02-15,0,   N/A
2020-02-15,1,   N/A
2020-02-15,2,   N/A
2020-02-15,3,   N/A
2020-02-15,4,   N/A
2020-02-15,5,   N/A
2020-02-15,6,   N/A
2020-02-15,7,   N/A
2020-02-15,8,   N/A
2020-02-15,9,   N/A
2020-02-15,10,   N/A
2020-02-15,11,   N/A
2020-02-15,12,   N/A
2020-02-15,13,   N/A
2020-02-15,14,   N/A
2020-02-15,15,   N/A
2020-02-15,16,   N/A
2020-02-15,17,   N/A
2020-02-15,18,   N/A
2020-02-15,19,   N/A
2020-02-15,20,   N/A
2020-02-15,21,   N/A
2020-02-15,22,   N/A
2020-02-15,23,   N/A
2020-02-16,0,   N/A
2020-02-16,1,   N/A
2020-02-16,2,   N/A
2020-02-16,3,   N/A
2020-02-16,4,   N/A
2020-02-16,5,   N/A
2020-02-16,6,   N/A
2020-02-16,7,   N/A
2020-02-16,8,   N/A
2020-02-16,9,   N/A
2020-02-16,10,   N/A
2020-02-16,11,   N/A
2020-02-16,12,   N/A
2020-02-16,13,   N/A
2020-02-16,14,   N/A
2020-02-16,15,   N/A
2020-02-16,16,   N/A
2020-02-16,17,   N/A
2020-02-16,18,   N/A
2020-02-16,19,   N/A
2020-02-16,20,   N/A
2020-02-16,21,   N/A
2020-02-16,22,   N/A
2020-02-16,23,   N/A
2020-02-17,0,   N/A
2020-02-17,1,   N/A
2020-02-17,2,   N/A
2020-02-17,3,   N/A
2020-02-17,4,   N/A
2020-02-17,5,   N/A
2020-02-17,6,   N/A
2020-02-17,7,   N/A
2020-02-17,8,   N/A
2020-02-17,9,   N/A
2020-02-17,10,   N/A
2020-02-17,11,   N/A
2020-02-17,12,   N/A
2020-02-17,13,   N/A
2020-02-17,14,   N/A
2020-02-17,15,   N/A
2020-02-17,16,   N/A
2020-02-17,17,   N/A
2020-02-17,18,   N/A
2020-02-17,19,   N/A
2020-02-17,20,   N/A
2020-02-17,21,   N/A
2020-02-17,22,   N/A
2020-02-17,23,   N/A
2020-02-18,0,   N/A
2020-02-18,1,   N/A
2020-02-18,2,   N/A
2020-02-18,3,   N/A
2020-02-18,4,   N/A
2020-02-18,5,   N/A
2020-02-18,6,   N/A
2020-02-18,7,   N/A
2020-02-18,8,   N/A
2020-02-18,9,   N/A
2020-02-18,10,   N/A
2020-02-18,11,   N/A
2020-02-18,12,   N/A
2020-02-18,13,   N/A
2020-02-18,14,   N/A
2020-02-18,15,   N/A
2020-02-18,16,   N/A
2020-02-18,17,   N/A
2020-02-18,18,   N/A
2020-02-18,19,   N/A
2020-02-18,20,   N/A
2020-02-18,21,   N/A
2020-02-18,22,   N/A
2020-02-18,23,   N/A
2020-02-19,0,   N/A
2020-02-19,1,   N/A
2020-02-19,2,   N/A
2020-02-19,3,   N/A
2020-02-19,4,   N/A
2020-02-19,5,   N/A
2020-02-19,6,   N/A
2020-02-19,7,   N/A
2020-02-19,8,   N/A
2020-02-19,9,   N/A
2020-02-19,10,   N/A
2020-02-19,11,   N/A
2020-02-19,12,   N/A
2020-02-19,13,   N/A
2020-02-19,14,   N/A
2020-02-19,15,   N/A
2020-02-19,16,   N/A
2020-02-19,17,   N/A
2020-02-19,18,   N/A
2020-02-19,19,   N/A
2020-02-19,20,   N/A
2020-02-19,21,   N/A
2020-02-19,22,   N/A
2020-02-19,23,   N/A
2020-02-20,0,   N/A
2020-02-20,1,   N/A
2020-02-20,2,   N/A
2020-02-20,3,   N/A
2020-02-20,4,   N/A
2020-02-20,5,   N/A
2020-02-20,6,   N/A
2020-02-20,7,   N/A
2020-02-20,8,   N/A
2020-02-20,9,   N/A
2020-02-20,10,   N/A
2020-02-20,11,   N/A
2020-02-20,12,   N/A
2020-02-20,13,   N/A
2020-02-20,14,   N/A
2020-02-20,15,   N/A
2020-02-20,16,   N/A
2020-02-20,17,   N/A
2020-02-20,18,   N/A
2020-02-20,19,   N/A
2020-02-20,20,   N/A
2020-02-20,21,   N/A
2020-02-20,22,   N/A
2020-02-20,23,   N/A
2020-02-21,0,   N/A
2020-02-21,1,   N/A
2020-02-21,2,   N/A
2020-02-21,3,   N/A
2020-02-21,4,   N/A
2020-02-21,5,   N/A
2020-02-21,6,   N/A
2020-02-21,7,   N/A
2020-02-21,8,   N/A
2020-02-21,9,   N/A
2020-02-21,10,   N/A
2020-02-21,11,   N/A
2020-02-21,12,   N/A
2020-02-21,13,   N/A
2020-02-21,14,   N/A
2020-02-21,15,   N/A
2020-02-21,16,   N/A
2020-02-21,17,   N/A
2020-02-21,18,   N/A
2020-02-21,19,   N/A
2020-02-21,20,   N/A
2020-02-21,21,   N/A
2020-02-21,22,   N/A
2020-02-21,23,   N/A
2020-02-22,0,   N/A
2020-02-22,1,   N/A
2020-02-22,2,   N/A
2020-02-22,3,   N/A
2020-02-22,4,   N/A
2020-02-22,5,   N/A
2020-02-22,6,   N/A
2020-02-22,7,   N/A
2020-02-22,8,   N/A
2020-02-22,9,   N/A
2020-02-22,10,   N/A
2020-02-22,11,   N/A
2020-02-22,12,   N/A
2020-02-22,13,   N/A
2020-02-22,14,   N/A
2020-02-22,15,   N/A
2020-02-22,16,   N/A
2020-02-22,17,   N/A
2020-02-22,18,   N/A
2020-02-22,19,   N/A
2020-02-22,20,   N/A
2020-02-22,21,   N/A
2020-02-22,22,   N/A
2020-02-22,23,   N/A
2020-02-23,0,   N/A
2020-02-23,1,   N/A
2020-02-23,2,   N/A
2020-02-23,3,   N/A
2020-02-23,4,   N/A
2020-02-23,5,   N/A
2020-02-23,6,   N/A
2020-02-23,7,   N/A
2020-02-23,8,   N/A
2020-02-23,9,   N/A
2020-02-23,10,   N/A
2020-02-23,11,   N/A
2020-02-23,12,   N/A
2020-02-23,13,   N/A
2020-02-23,14,   N/A
2020-02-23,15,   N/A
2020-02-23,16,   N/A
2020-02-23,17,   N/A
2020-02-23,18,   N/A
2020-02-23,19,   N/A
2020-02-23,20,   N/A
2020-02-23,21,   N/A
2020-02-23,22,   N/A
2020-02-23,23,   N/A
2020-02-24,0,   N/A
2020-02-24,1,   N/A
2020-02-24,2,   N/A
2020-02-24,3,   N/A
2020-02-24,4,   N/A
2020-02-24,5,   N/A
2020-02-24,6,   N/A
2020-02-24,7,   N/A
2020-02-24,8,   N/A
2020-02-24,9,   N/A
2020-02-24,10,   N/A
2020-02-24,11,   N/A
2020-02-24,12,   N/A
2020-02-24,13,   N/A
2020-02-24,14,   N/A
2020-02-24,15,   N/A
2020-02-24,16,   N/A
2020-02-24,17,   N/A
2020-02-24,18,   N/A
2020-02-24,19,   N/A
2020-02-24,20,   N/A
2020-02-24,21,   N/A
2020-02-24,22,   N/A
2020-02-24,23,   N/A
2020-02-25,0,   N/A
2020-02-25,1,   N/A
2020-02-25,2,   N/A
2020-02-25,3,   N/A
2020-02-25,4,   N/A
2020-02-25,5,   N/A
2020-02-25,6,   N/A
2020-02-25,7,   N/A
2020-02-25,8,   N/A
2020-02-25,9,   N/A
2020-02-25,10,   N/A
2020-02-25,11,   N/A
2020-02-25,12,   N/A
2020-02-25,13,   N/A
2020-02-25,14,   N/A
2020-02-25,15,   N/A
2020-02-25,16,   N/A
2020-02-25,17,   N/A
2020-02-25,18,   N/A
2020-02-25,19,   N/A
2020-02-25,20,   N/A
2020-02-25,21,   N/A
2020-02-25,22,   N/A
2020-02-25,23,   N/A
2020-02-26,0,   N/A
2020-02-26,1,   N/A
2020-02-26,2,   N/A
2020-02-26,3,   N/A
2020-02-26,4,   N/A
2020-02-26,5,   N/A
2020-02-26,6,   N/A
2020-02-26,7,   N/A
2020-02-26,8,   N/A
2020-02-26,9,   N/A
2020-02-26,10,   N/A
2020-02-26,11,   N/A
2020-02-26,12,   N/A
2020-02-26,13,   N/A
2020-02-26,14,   N/A
2020-02-26,15,   N/A
2020-02-26,16,   N/A
2020-02-26,17,   N/A
2020-02-26,18,   N/A
2020-02-26,19,   N/A
2020-02-26,20,   N/A
2020-02-26,21,   N/A
2020-02-26,22,   N/A
2020-02-26,23,   N/A
2020-02-27,0,   N/A
2020-02-27,1,   N/A
2020-02-27,2,   N/A
2020-02-27,3,   N/A
2020-02-27,4,   N/A
2020-02-27,5,   N/A
2020-02-27,6,   N/A
2020-02-27,7,   N/A
2020-02-27,8,   N/A
2020-02-27,9,   N/A
2020-02-27,10,   N/A
2020-02-27,11,   N/A
2020-02-27,12,   N/A
2020-02-27,13,   N/A
2020-02-27,14,   N/A
2020-02-27,15,   N/A
2020-02-27,16,   N/A
2020-02-27,17,   N/A
2020-02-27,18,   N/A
2020-02-27,19,   N/A
2020-02-27,20,   N/A
2020-02-27,21,   N/A
2020-02-27,22,   N/A
2020-02-27,23,   N/A
2020-02-28,0,   N/A
2020-02-28,1,   N/A
2020-02-28,2,   N/A
2020-02-28,3,   N/A
2020-02-28,4,   N/A
2020-02-28,5,   N/A
2020-02-28,6,   N/A
2020-02-28,7,   N/A
2020-02-28,8,   N/A
2020-02-28,9,   N/A
2020-02-28,10,   N/A
2020-02-28,11,   N/A
2020-02-28,12,   N/A
2020-02-28,13,   N/A
2020-02-28,14,   N/A
2020-02-28,15,   N/A
2020-02-28,16,   N/A
2020-02-28,17,   N/A
2020-02-28,18,   N/A
2020-02-28,19,   N/A
2020-02-28,20,   N/A
2020-02-28,21,   N/A
2020-02-28,22,   N/A
2020-02-28,23,   N/A
2020-02-29,0,   N/A
2020-02-29,1,   N/A
2020-02-29,2,   N/A
2020-02-29,3,   N/A
2020-02-29,4,   N/A
2020-02-29,5,   N/A
2020-02-29,6,   N/A
2020-02-29,7,   N/A
2020-02-29,8,   N/A
2020-02-29,9,   N/A
2020-02-29,10,   N/A
2020-02-29,11,   N/A
2020-02-29,12,   N/A
2020-02-29,13,   N/A
2020-02-29,14,   N/A
2020-02-29,15,   N/A
2020-02-29,16,   N/A
2020-02-29,17,   N/A
2020-02-29,18,   N/A
2020-02-29,19,   N/A
2020-02-29,20,   N/A
2020-02-29,21,   N/A
2020-02-29,22,   N/A
2020-02-29,23,   N/A
2020-03-01,0,   N/A
2020-03-01,1,   N/A
2020-03-01,2,   N/A
2020-03-01,3,   N/A
2020-03-01,4,   N/A
2020-03-01,5,   N/A
2020-03-01,6,   N/A
2020-03-01,7,   N/A
2020-03-01,8,   N/A
2020-03-01,9,   N/A
2020-03-01,10,   N/A
2020-03-01,11,   N/A
2020-03-01,12,   N/A
2020-03-01,13,   N/A
2020-03-01,14,   N/A
2020-03-01,15,   N/A
2020-03-01,16,   N/A
2020-03-01,17,   N/A
2020-03-01,18,   N/A
2020-03-01,19,   N/A
2020-03-01,20,   N/A
2020-03-01,21,   N/A
2020-03-01,22,   N/A
2020-03-01,23,   N/A
2020-03-02,0,   N/A
2020-03-02,1,   N/A
2020-03-02,2,   N/A
2020-03-02,3,   N/A
2020-03-02,4,   N/A
2020-03-02,5,   N/A
2020-03-02,6,   N/A
2020-03-02,7,   N/A
2020-03-02,8,   N/A
2020-03-02,9,   N/A
2020-03-02,10,   N/A
2020-03-02,11,   N/A
2020-03-02,12,   N/A
2020-03-02,13,   N/A
2020-03-02,14,   N/A
2020-03-02,15,   N/A
2020-03-02,16,   N/A
2020-03-02,17,   N/A
2020-03-02,18,   N/A
2020-03-02,19,   N/A
2020-03-02,20,   N/A
2020-03-02,21,   N/A
2020-03-02,22,   N/A
2020-03-02,23,   N/A
2020-03-03,0,   N/A
2020-03-03,1,   N/A
2020-03-03,2,   N/A
2020-03-03,3,   N/A
2020-03-03,4,   N/A
2020-03-03,5,   N/A
2020-03-03,6,   N/A
2020-03-03,7,   N/A
2020-03-03,8,   N/A
2020-03-03,9,   N/A
2020-03-03,10,   N/A
2020-03-03,11,   N/A
2020-03-03,12,   N/A
2020-03-03,13,   N/A
2020-03-03,14,   N/A
2020-03-03,15,   N/A
2020-03-03,16,   N/A
2020-03-03,17,   N/A
2020-03-03,18,   N/A
2020-03-03,19,   N/A
2020-03-03,20,   N/A
2020-03-03,21,   N/A
2020-03-03,22,   N/A
2020-03-03,23,   N/A
2020-03-04,0,   N/A
2020-03-04,1,   N/A
2020-03-04,2,   N/A
2020-03-04,3,   N/A
2020-03-04,4,   N/A
2020-03-04,5,   N/A
2020-03-04,6,   N/A
2020-03-04,7,   N/A
2020-03-04,8,   N/A
2020-03-04,9,   N/A
2020-03-04,10,   N/A
2020-03-04,11,   N/A
2020-03-04,12,   N/A
2020-03-04,13,   N/A
2020-03-04,14,   N/A
2020-03-04,15,   N/A
2020-03-04,16,   N/A
2020-03-04,17,   N/A
2020-03-04,18,   N/A
2020-03-04,19,   N/A
2020-03-04,20,   N/A
2020-03-04,21,   N/A
2020-03-04,22,   N/A
2020-03-04,23,   N/A
2020-03-05,0,   N/A
2020-03-05,1,   N/A
2020-03-05,2,   N/A
2020-03-05,3,   N/A
2020-03-05,4,   N/A
2020-03-05,5,   N/A
2020-03-05,6,   N/A
2020-03-05,7,   N/A
2020-03-05,8,   N/A
2020-03-05,9,   N/A
2020-03-05,10,   N/A
2020-03-05,11,   N/A
2020-03-05,12,   N/A
2020-03-05,13,   N/A
2020-03-05,14,   N/A
2020-03-05,15,   N/A
2020-03-05,16,   N/A
2020-03-05,17,   N/A
2020-03-05,18,   N/A
2020-03-05,19,   N/A
2020-03-05,20,   N/A
2020-03-05,21,   N/A
2020-03-05,22,   N/A
2020-03-05,23,   N/A
2020-03-06,0,   N/A
2020-03-06,1,   N/A
2020-03-06,2,   N/A
2020-03-06,3,   N/A
2020-03-06,4,   N/A
2020-03-06,5,   N/A
2020-03-06,6,   N/A
2020-03-06,7,   N/A
2020-03-06,8,   N/A
2020-03-06,9,   N/A
2020-03-06,10,   N/A
2020-03-06,11,   N/A
2020-03-06,12,   N/A
2020-03-06,13,   N/A
2020-03-06,14,   N/A
2020-03-06,15,   N/A
2020-03-06,16,   N/A
2020-03-06,17,   N/A
2020-03-06,18,   N/A
2020-03-06,19,   N/A
2020-03-06,20,   N/A
2020-03-06,21,   N/A
2020-03-06,22,   N/A
2020-03-06,23,   N/A
2020-03-07,0,   N/A
2020-03-07,1,   N/A
2020-03-07,2,   N/A
2020-03-07,3,   N/A
2020-03-07,4,   N/A
2020-03-07,5,   N/A
2020-03-07,6,   N/A
2020-03-07,7,   N/A
2020-03-07,8,   N/A
2020-03-07,9,   N/A
2020-03-07,10,   N/A
2020-03-07,11,   N/A
2020-03-07,12,   N/A
2020-03-07,13,   N/A
2020-03-07,14,   N/A
2020-03-07,15,   N/A
2020-03-07,16,   N/A
2020-03-07,17,   N/A
2020-03-07,18,   N/A
2020-03-07,19,   N/A
2020-03-07,20,   N/A
2020-03-07,21,   N/A
2020-03-07,22,   N/A
2020-03-07,23,   N/A
2020-03-08,0,   N/A
2020-03-08,1,   N/A
2020-03-08,2,   N/A
2020-03-08,3,   N/A
2020-03-08,4,   N/A
2020-03-08,5,   N/A
2020-03-08,6,   N/A
2020-03-08,7,   N/A
2020-03-08,8,   N/A
2020-03-08,9,   N/A
2020-03-08,10,   N/A
2020-03-08,11,   N/A
2020-03-08,12,   N/A
2020-03-08,13,   N/A
2020-03-08,14,   N/A
2020-03-08,15,   N/A
2020-03-08,16,   N/A
2020-03-08,17,   N/A
2020-03-08,18,   N/A
2020-03-08,19,   N/A
2020-03-08,20,   N/A
2020-03-08,21,   N/A
2020-03-08,22,   N/A
2020-03-08,23,   N/A
2020-03-09,0,   N/A
2020-03-09,1,   N/A
2020-03-09,2,   N/A
2020-03-09,3,   N/A
2020-03-09,4,   N/A
2020-03-09,5,   N/A
2020-03-09,6,   N/A
2020-03-09,7,   N/A
2020-03-09,8,   N/A
2020-03-09,9,   N/A
2020-03-09,10,   N/A
2020-03-09,11,   N/A
2020-03-09,12,   N/A
2020-03-09,13,   N/A
2020-03-09,14,   N/A
2020-03-09,15,   N/A
2020-03-09,16,   N/A
2020-03-09,17,   N/A
2020-03-09,18,   N/A
2020-03-09,19,   N/A
2020-03-09,20,   N/A
2020-03-09,21,   N/A
2020-03-09,22,   N/A
2020-03-09,23,   N/A
2020-03-10,0,   N/A
2020-03-10,1,   N/A
2020-03-10,2,   N/A
2020-03-10,3,   N/A
2020-03-10,4,   N/A
2020-03-10,5,   N/A
2020-03-10,6,   N/A
2020-03-10,7,   N/A
2020-03-10,8,   N/A
2020-03-10,9,   N/A
2020-03-10,10,   N/A
2020-03-10,11,   N/A
2020-03-10,12,   N/A
2020-03-10,13,   N/A
2020-03-10,14,   N/A
2020-03-10,15,   N/A
2020-03-10,16,   N/A
2020-03-10,17,   N/A
2020-03-10,18,   N/A
2020-03-10,19,   N/A
2020-03-10,20,   N/A
2020-03-10,21,   N/A
2020-03-10,22,   N/A
2020-03-10,23,   N/A
2020-03-11,0,   N/A
2020-03-11,1,   N/A
2020-03-11,2,   N/A
2020-03-11,3,   N/A
2020-03-11,4,   N/A
2020-03-11,5,   N/A
2020-03-11,6,   N/A
2020-03-11,7,   N/A
2020-03-11,8,   N/A
2020-03-11,9,   N/A
2020-03-11,10,   N/A
2020-03-11,11,   N/A
2020-03-11,12,   N/A
2020-03-11,13,   N/A
2020-03-11,14,   N/A
2020-03-11,15,   N/A
2020-03-11,16,   N/A
2020-03-11,17,   N/A
2020-03-11,18,   N/A
2020-03-11,19,   N/A
2020-03-11,20,   N/A
2020-03-11,21,   N/A
2020-03-11,22,   N/A
2020-03-11,23,   N/A
2020-03-12,0,   N/A
2020-03-12,1,   N/A
2020-03-12,2,   N/A
2020-03-12,3,   N/A
2020-03-12,4,   N/A
2020-03-12,5,   N/A
2020-03-12,6,   N/A
2020-03-12,7,   N/A
2020-03-12,8,   N/A
2020-03-12,9,   N/A
2020-03-12,10,   N/A
2020-03-12,11,   N/A
2020-03-12,12,   N/A
2020-03-12,13,   N/A
2020-03-12,14,   N/A
2020-03-12,15,   N/A
2020-03-12,16,   N/A
2020-03-12,17,   N/A
2020-03-12,18,   N/A
2020-03-12,19,   N/A
2020-03-12,20,   N/A
2020-03-12,21,   N/A
2020-03-12,22,   N/A
2020-03-12,23,   N/A
2020-03-13,0,   N/A
2020-03-13,1,   N/A
2020-03-13,2,   N/A
2020-03-13,3,   N/A
2020-03-13,4,   N/A
2020-03-13,5,   N/A
2020-03-13,6,   N/A
2020-03-13,7,   N/A
2020-03-13,8,   N/A
2020-03-13,9,   N/A
2020-03-13,10,   N/A
2020-03-13,11,   N/A
2020-03-13,12,   N/A
2020-03-13,13,   N/A
2020-03-13,14,   N/A
2020-03-13,15,   N/A
2020-03-13,16,   N/A
2020-03-13,17,   N/A
2020-03-13,18,   N/A
2020-03-13,19,   N/A
2020-03-13,20,   N/A
2020-03-13,21,   N/A
2020-03-13,22,   N/A
2020-03-13,23,   N/A
2020-03-14,0,   N/A
2020-03-14,1,   N/A
2020-03-14,2,   N/A
2020-03-14,3,   N/A
2020-03-14,4,   N/A
2020-03-14,5,   N/A
2020-03-14,6,   N/A
2020-03-14,7,   N/A
2020-03-14,8,   N/A
2020-03-14,9,   N/A
2020-03-14,10,   N/A
2020-03-14,11,   N/A
2020-03-14,12,   N/A
2020-03-14,13,   N/A
2020-03-14,14,   N/A
2020-03-14,15,   N/A
2020-03-14,16,   N/A
2020-03-14,17,   N/A
2020-03-14,18,   N/A
2020-03-14,19,   N/A
2020-03-14,20,   N/A
2020-03-14,21,   N/A
2020-03-14,22,   N/A
2020-03-14,23,   N/A
2020-03-15,0,   N/A
2020-03-15,1,   N/A
2020-03-15,2,   N/A
2020-03-15,3,   N/A
2020-03-15,4,   N/A
2020-03-15,5,   N/A
2020-03-15,6,   N/A
2020-03-15,7,   N/A
2020-03-15,8,   N/A
2020-03-15,9,   N/A
2020-03-15,10,   N/A
2020-03-15,11,   N/A
2020-03-15,12,   N/A
2020-03-15,13,   N/A
2020-03-15,14,   N/A
2020-03-15,15,   N/A
2020-03-15,16,   N/A
2020-03-15,17,   N/A
2020-03-15,18,   N/A
2020-03-15,19,   N/A
2020-03-15,20,   N/A
2020-03-15,21,   N/A
2020-03-15,22,   N/A
2020-03-15,23,   N/A
2020-03-16,0,   N/A
2020-03-16,1,   N/A
2020-03-16,2,   N/A
2020-03-16,3,   N/A
2020-03-16,4,   N/A
2020-03-16,5,   N/A
2020-03-16,6,   N/A
2020-03-16,7,   N/A
2020-03-16,8,   N/A
2020-03-16,9,   N/A
2020-03-16,10,   N/A
2020-03-16,11,   N/A
2020-03-16,12,   N/A
2020-03-16,13,   N/A
2020-03-16,14,   N/A
2020-03-16,15,   N/A
2020-03-16,16,   N/A
2020-03-16,17,   N/A
2020-03-16,18,   N/A
2020-03-16,19,   N/A
2020-03-16,20,   N/A
2020-03-16,21,   N/A
2020-03-16,22,   N/A
2020-03-16,23,   N/A
2020-03-17,0,   N/A
2020-03-17,1,   N/A
2020-03-17,2,   N/A
2020-03-17,3,   N/A
2020-03-17,4,   N/A
2020-03-17,5,   N/A
2020-03-17,6,   N/A
2020-03-17,7,   N/A
2020-03-17,8,   N/A
2020-03-17,9,   N/A
2020-03-17,10,   N/A
2020-03-17,11,   N/A
2020-03-17,12,   N/A
2020-03-17,13,   N/A
2020-03-17,14,   N/A
2020-03-17,15,   N/A
2020-03-17,16,   N/A
2020-03-17,17,   N/A
2020-03-17,18,   N/A
2020-03-17,19,   N/A
2020-03-17,20,   N/A
2020-03-17,21,   N/A
2020-03-17,22,   N/A
2020-03-17,23,   N/A
2020-03-18,0,   N/A
2020-03-18,1,   N/A
2020-03-18,2,   N/A
2020-03-18,3,   N/A
2020-03-18,4,   N/A
2020-03-18,5,   N/A
2020-03-18,6,   N/A
2020-03-18,7,   N/A
2020-03-18,8,   N/A
2020-03-18,9,   N/A
2020-03-18,10,   N/A
2020-03-18,11,   N/A
2020-03-18,12,   N/A
2020-03-18,13,   N/A
2020-03-18,14,   N/A
2020-03-18,15,   N/A
2020-03-18,16,   N/A
2020-03-18,17,   N/A
2020-03-18,18,   N/A
2020-03-18,19,   N/A
2020-03-18,20,   N/A
2020-03-18,21,   N/A
2020-03-18,22,   N/A
2020-03-18,23,   N/A
2020-03-19,0,   N/A
2020-03-19,1,   N/A
2020-03-19,2,   N/A
2020-03-19,3,   N/A
2020-03-19,4,   N/A
2020-03-19,5,   N/A
2020-03-19,6,   N/A
2020-03-19,7,   N/A
2020-03-19,8,   N/A
2020-03-19,9,   N/A
2020-03-19,10,   N/A
2020-03-19,11,   N/A
2020-03-19,12,   N/A
2020-03-19,13,   N/A
2020-03-19,14,   N/A
2020-03-19,15,   N/A
2020-03-19,16,   N/A
2020-03-19,17,   N/A
2020-03-19,18,   N/A
2020-03-19,19,   N/A
2020-03-19,20,   N/A
2020-03-19,21,   N/A
2020-03-19,22,   N/A
2020-03-19,23,   N/A
2020-03-20,0,   N/A
2020-03-20,1,   N/A
2020-03-20,2,   N/A
2020-03-20,3,   N/A
2020-03-20,4,   N/A
2020-03-20,5,   N/A
2020-03-20,6,   N/A
2020-03-20,7,   N/A
2020-03-20,8,   N/A
2020-03-20,9,   N/A
2020-03-20,10,   N/A
2020-03-20,11,   N/A
2020-03-20,12,   N/A
2020-03-20,13,   N/A
2020-03-20,14,   N/A
2020-03-20,15,   N/A
2020-03-20,16,   N/A
2020-03-20,17,   N/A
2020-03-20,18,   N/A
2020-03-20,19,   N/A
2020-03-20,20,   N/A
2020-03-20,21,   N/A
2020-03-20,22,   N/A
2020-03-20,23,   N/A
2020-03-21,0,   N/A
2020-03-21,1,   N/A
2020-03-21,2,   N/A
2020-03-21,3,   N/A
2020-03-21,4,   N/A
2020-03-21,5,   N/A
2020-03-21,6,   N/A
2020-03-21,7,   N/A
2020-03-21,8,   N/A
2020-03-21,9,   N/A
2020-03-21,10,   N/A
2020-03-21,11,   N/A
2020-03-21,12,   N/A
2020-03-21,13,   N/A
2020-03-21,14,   N/A
2020-03-21,15,   N/A
2020-03-21,16,   N/A
2020-03-21,17,   N/A
2020-03-21,18,   N/A
2020-03-21,19,   N/A
2020-03-21,20,   N/A
2020-03-21,21,   N/A
2020-03-21,22,   N/A
2020-03-21,23,   N/A
2020-03-22,0,   N/A
2020-03-22,1,   N/A
2020-03-22,2,   N/A
2020-03-22,3,   N/A
2020-03-22,4,   N/A
2020-03-22,5,   N/A
2020-03-22,6,   N/A
2020-03-22,7,   N/A
2020-03-22,8,   N/A
2020-03-22,9,   N/A
2020-03-22,10,   N/A
2020-03-22,11,   N/A
2020-03-22,12,   N/A
2020-03-22,13,   N/A
2020-03-22,14,   N/A
2020-03-22,15,   N/A
2020-03-22,16,   N/A
2020-03-22,17,   N/A
2020-03-22,18,   N/A
2020-03-22,19,   N/A
2020-03-22,20,   N/A
2020-03-22,21,   N/A
2020-03-22,22,   N/A
2020-03-22,23,   N/A
2020-03-23,0,   N/A
2020-03-23,1,   N/A
2020-03-23,2,   N/A
2020-03-23,3,   N/A
2020-03-23,4,   N/A
2020-03-23,5,   N/A
2020-03-23,6,   N/A
2020-03-23,7,   N/A
2020-03-23,8,   N/A
2020-03-23,9,   N/A
2020-03-23,10,   N/A
2020-03-23,11,   N/A
2020-03-23,12,   N/A
2020-03-23,13,   N/A
2020-03-23,14,   N/A
2020-03-23,15,   N/A
2020-03-23,16,   N/A
2020-03-23,17,   N/A
2020-03-23,18,   N/A
2020-03-23,19,   N/A
2020-03-23,20,   N/A
2020-03-23,21,   N/A
2020-03-23,22,   N/A
2020-03-23,23,   N/A
2020-03-24,0,   N/A
2020-03-24,1,   N/A
2020-03-24,2,   N/A
2020-03-24,3,   N/A
2020-03-24,4,   N/A
2020-03-24,5,   N/A
2020-03-24,6,   N/A
2020-03-24,7,   N/A
2020-03-24,8,   N/A
2020-03-24,9,   N/A
2020-03-24,10,   N/A
2020-03-24,11,   N/A
2020-03-24,12,   N/A
2020-03-24,13,   N/A
2020-03-24,14,   N/A
2020-03-24,15,   N/A
2020-03-24,16,   N/A
2020-03-24,17,   N/A
2020-03-24,18,   N/A
2020-03-24,19,   N/A
2020-03-24,20,   N/A
2020-03-24,21,   N/A
2020-03-24,22,   N/A
2020-03-24,23,   N/A
2020-03-25,0,   N/A
2020-03-25,1,   N/A
2020-03-25,2,   N/A
2020-03-25,3,   N/A
2020-03-25,4,   N/A
2020-03-25,5,   N/A
2020-03-25,6,   N/A
2020-03-25,7,   N/A
2020-03-25,8,   N/A
2020-03-25,9,   N/A
2020-03-25,10,   N/A
2020-03-25,11,   N/A
2020-03-25,12,   N/A
2020-03-25,13,   N/A
2020-03-25,14,   N/A
2020-03-25,15,   N/A
2020-03-25,16,   N/A
2020-03-25,17,   N/A
2020-03-25,18,   N/A
2020-03-25,19,   N/A
2020-03-25,20,   N/A
2020-03-25,21,   N/A
2020-03-25,22,   N/A
2020-03-25,23,   N/A
2020-03-26,0,   N/A
2020-03-26,1,   N/A
2020-03-26,2,   N/A
2020-03-26,3,   N/A
2020-03-26,4,   N/A
2020-03-26,5,   N/A
2020-03-26,6,   N/A
2020-03-26,7,   N/A
2020-03-26,8,   N/A
2020-03-26,9,   N/A
2020-03-26,10,   N/A
2020-03-26,11,   N/A
2020-03-26,12,   N/A
2020-03-26,13,   N/A
2020-03-26,14,   N/A
2020-03-26,15,   N/A
2020-03-26,16,   N/A
2020-03-26,17,   N/A
2020-03-26,18,   N/A
2020-03-26,19,   N/A
2020-03-26,20,   N/A
2020-03-26,21,   N/A
2020-03-26,22,   N/A
2020-03-26,23,   N/A
2020-03-27,0,   N/A
2020-03-27,1,   N/A
2020-03-27,2,   N/A
2020-03-27,3,   N/A
2020-03-27,4,   N/A
2020-03-27,5,   N/A
2020-03-27,6,   N/A
2020-03-27,7,   N/A
2020-03-27,8,   N/A
2020-03-27,9,   N/A
2020-03-27,10,   N/A
2020-03-27,11,   N/A
2020-03-27,12,   N/A
2020-03-27,13,   N/A
2020-03-27,14,   N/A
2020-03-27,15,   N/A
2020-03-27,16,   N/A
2020-03-27,17,   N/A
2020-03-27,18,   N/A
2020-03-27,19,   N/A
2020-03-27,20,   N/A
2020-03-27,21,   N/A
2020-03-27,22,   N/A
2020-03-27,23,   N/A
2020-03-28,0,   N/A
2020-03-28,1,   N/A
2020-03-28,2,   N/A
2020-03-28,3,   N/A
2020-03-28,4,   N/A
2020-03-28,5,   N/A
2020-03-28,6,   N/A
2020-03-28,7,   N/A
2020-03-28,8,   N/A
2020-03-28,9,   N/A
2020-03-28,10,   N/A
2020-03-28,11,   N/A
2020-03-28,12,   N/A
2020-03-28,13,   N/A
2020-03-28,14,   N/A
2020-03-28,15,   N/A
2020-03-28,16,   N/A
2020-03-28,17,   N/A
2020-03-28,18,   N/A
2020-03-28,19,   N/A
2020-03-28,20,   N/A
2020-03-28,21,   N/A
2020-03-28,22,   N/A
2020-03-28,23,   N/A
2020-03-29,0,   N/A
2020-03-29,1,   N/A
2020-03-29,2,   N/A
2020-03-29,3,   N/A
2020-03-29,4,   N/A
2020-03-29,5,   N/A
2020-03-29,6,   N/A
2020-03-29,7,   N/A
2020-03-29,8,   N/A
2020-03-29,9,   N/A
2020-03-29,10,   N/A
2020-03-29,11,   N/A
2020-03-29,12,   N/A
2020-03-29,13,   N/A
2020-03-29,14,   N/A
2020-03-29,15,   N/A
2020-03-29,16,   N/A
2020-03-29,17,   N/A
2020-03-29,18,   N/A
2020-03-29,19,   N/A
2020-03-29,20,   N/A
2020-03-29,21,   N/A
2020-03-29,22,   N/A
2020-03-30,23,   N/A
2020-03-30,0,   N/A
2020-03-30,1,   N/A
2020-03-30,2,   N/A
2020-03-30,3,   N/A
2020-03-30,4,   N/A
2020-03-30,5,   N/A
2020-03-30,6,   N/A
2020-03-30,7,   N/A
2020-03-30,8,   N/A
2020-03-30,9,   N/A
2020-03-30,10,   N/A
2020-03-30,11,   N/A
2020-03-30,12,   N/A
2020-03-30,13,   N/A
2020-03-30,14,   N/A
2020-03-30,15,   N/A
2020-03-30,16,   N/A
2020-03-30,17,   N/A
2020-03-30,18,   N/A
2020-03-30,19,   N/A
2020-03-30,20,   N/A
2020-03-30,21,   N/A
2020-03-30,22,   N/A
2020-03-31,23,   N/A
2020-03-31,0,   N/A
2020-03-31,1,   N/A
2020-03-31,2,   N/A
2020-03-31,3,   N/A
2020-03-31,4,   N/A
2020-03-31,5,   N/A
2020-03-31,6,   N/A
2020-03-31,7,   N/A
2020-03-31,8,   N/A
2020-03-31,9,   N/A
2020-03-31,10,   N/A
2020-03-31,11,   N/A
2020-03-31,12,   N/A
2020-03-31,13,   N/A
2020-03-31,14,   N/A
2020-03-31,15,   N/A
2020-03-31,16,   N/A
2020-03-31,17,   N/A
2020-03-31,18,   N/A
2020-03-31,19,   N/A
2020-03-31,20,   N/A
2020-03-31,21,   N/A
2020-03-31,22,   N/A
2020-04-01,23,   N/A
2020-04-01,0,   N/A
2020-04-01,1,   N/A
2020-04-01,2,   N/A
2020-04-01,3,   N/A
2020-04-01,4,   N/A
2020-04-01,5,   N/A
2020-04-01,6,   N/A
2020-04-01,7,   N/A
2020-04-01,8,   N/A
2020-04-01,9,   N/A
2020-04-01,10,   N/A
2020-04-01,11,   N/A
2020-04-01,12,   N/A
2020-04-01,13,   N/A
2020-04-01,14,   N/A
2020-04-01,15,   N/A
2020-04-01,16,   N/A
2020-04-01,17,   N/A
2020-04-01,18,   N/A
2020-04-01,19,   N/A
2020-04-01,20,   N/A
2020-04-01,21,   N/A
2020-04-01,22,   N/A
2020-04-02,23,   N/A
2020-04-02,0,   N/A
2020-04-02,1,   N/A
2020-04-02,2,   N/A
2020-04-02,3,   N/A
2020-04-02,4,   N/A
2020-04-02,5,   N/A
2020-04-02,6,   N/A
2020-04-02,7,   N/A
2020-04-02,8,   N/A
2020-04-02,9,   N/A
2020-04-02,10,   N/A
2020-04-02,11,   N/A
2020-04-02,12,   N/A
2020-04-02,13,   N/A
2020-04-02,14,   N/A
2020-04-02,15,   N/A
2020-04-02,16,   N/A
2020-04-02,17,   N/A
2020-04-02,18,   N/A
2020-04-02,19,   N/A
2020-04-02,20,   N/A
2020-04-02,21,   N/A
2020-04-02,22,   N/A
2020-04-03,23,   N/A
2020-04-03,0,   N/A
2020-04-03,1,   N/A
2020-04-03,2,   N/A
2020-04-03,3,   N/A
2020-04-03,4,   N/A
2020-04-03,5,   N/A
2020-04-03,6,   N/A
2020-04-03,7,   N/A
2020-04-03,8,   N/A
2020-04-03,9,   N/A
2020-04-03,10,   N/A
2020-04-03,11,   N/A
2020-04-03,12,   N/A
2020-04-03,13,   N/A
2020-04-03,14,   N/A
2020-04-03,15,   N/A
2020-04-03,16,   N/A
2020-04-03,17,   N/A
2020-04-03,18,   N/A
2020-04-03,19,   N/A
2020-04-03,20,   N/A
2020-04-03,21,   N/A
2020-04-03,22,   N/A
2020-04-04,23,   N/A
2020-04-04,0,   N/A
2020-04-04,1,   N/A
2020-04-04,2,   N/A
2020-04-04,3,   N/A
2020-04-04,4,   N/A
2020-04-04,5,   N/A
2020-04-04,6,   N/A
2020-04-04,7,   N/A
2020-04-04,8,   N/A
2020-04-04,9,   N/A
2020-04-04,10,   N/A
2020-04-04,11,   N/A
2020-04-04,12,   N/A
2020-04-04,13,   N/A
2020-04-04,14,   N/A
2020-04-04,15,   N/A
2020-04-04,16,   N/A
2020-04-04,17,   N/A
2020-04-04,18,   N/A
2020-04-04,19,   N/A
2020-04-04,20,   N/A
2020-04-04,21,   N/A
2020-04-04,22,   N/A
2020-04-05,23,   N/A
2020-04-05,0,   N/A
2020-04-05,1,   N/A
2020-04-05,2,   N/A
2020-04-05,3,   N/A
2020-04-05,4,   N/A
2020-04-05,5,   N/A
2020-04-05,6,   N/A
2020-04-05,7,   N/A
2020-04-05,8,   N/A
2020-04-05,9,   N/A
2020-04-05,10,   N/A
2020-04-05,11,   N/A
2020-04-05,12,   N/A
2020-04-05,13,   N/A
2020-04-05,14,   N/A
2020-04-05,15,   N/A
2020-04-05,16,   N/A
2020-04-05,17,   N/A
2020-04-05,18,   N/A
2020-04-05,19,   N/A
2020-04-05,20,   N/A
2020-04-05,21,   N/A
2020-04-05,22,   N/A
2020-04-06,23,   N/A
2020-04-06,0,   N/A
2020-04-06,1,   N/A
2020-04-06,2,   N/A
2020-04-06,3,   N/A
2020-04-06,4,   N/A
2020-04-06,5,   N/A
2020-04-06,6,   N/A
2020-04-06,7,   N/A
2020-04-06,8,   N/A
2020-04-06,9,   N/A
2020-04-06,10,   N/A
2020-04-06,11,   N/A
2020-04-06,12,   N/A
2020-04-06,13,   N/A
2020-04-06,14,   N/A
2020-04-06,15,   N/A
2020-04-06,16,   N/A
2020-04-06,17,   N/A
2020-04-06,18,   N/A
2020-04-06,19,   N/A
2020-04-06,20,   N/A
2020-04-06,21,   N/A
2020-04-06,22,   N/A
2020-04-07,23,   N/A
2020-04-07,0,   N/A
2020-04-07,1,   N/A
2020-04-07,2,   N/A
2020-04-07,3,   N/A
2020-04-07,4,   N/A
2020-04-07,5,   N/A
2020-04-07,6,   N/A
2020-04-07,7,   N/A
2020-04-07,8,   N/A
2020-04-07,9,   N/A
2020-04-07,10,   N/A
2020-04-07,11,   N/A
2020-04-07,12,   N/A
2020-04-07,13,   N/A
2020-04-07,14,   N/A
2020-04-07,15,   N/A
2020-04-07,16,   N/A
2020-04-07,17,   N/A
2020-04-07,18,   N/A
2020-04-07,19,   N/A
2020-04-07,20,   N/A
2020-04-07,21,   N/A
2020-04-07,22,   N/A
2020-04-08,23,   N/A
2020-04-08,0,   N/A
2020-04-08,1,   N/A
2020-04-08,2,   N/A
2020-04-08,3,   N/A
2020-04-08,4,   N/A
2020-04-08,5,   N/A
2020-04-08,6,   N/A
2020-04-08,7,   N/A
2020-04-08,8,   N/A
2020-04-08,9,   N/A
2020-04-08,10,   N/A
2020-04-08,11,   N/A
2020-04-08,12,   N/A
2020-04-08,13,   N/A
2020-04-08,14,   N/A
2020-04-08,15,   N/A
2020-04-08,16,   N/A
2020-04-08,17,   N/A
2020-04-08,18,   N/A
2020-04-08,19,   N/A
2020-04-08,20,   N/A
2020-04-08,21,   N/A
2020-04-08,22,   N/A
2020-04-09,23,   N/A
2020-04-09,0,   N/A
2020-04-09,1,   N/A
2020-04-09,2,   N/A
2020-04-09,3,   N/A
2020-04-09,4,   N/A
2020-04-09,5,   N/A
2020-04-09,6,   N/A
2020-04-09,7,   N/A
2020-04-09,8,   N/A
2020-04-09,9,   N/A
2020-04-09,10,   N/A
2020-04-09,11,   N/A
2020-04-09,12,   N/A
2020-04-09,13,   N/A
2020-04-09,14,   N/A
2020-04-09,15,   N/A
2020-04-09,16,   N/A
2020-04-09,17,   N/A
2020-04-09,18,   N/A
2020-04-09,19,   N/A
2020-04-09,20,   N/A
2020-04-09,21,   N/A
2020-04-09,22,   N/A
2020-04-10,23,   N/A
2020-04-10,0,   N/A
2020-04-10,1,   N/A
2020-04-10,2,   N/A
2020-04-10,3,   N/A
2020-04-10,4,   N/A
2020-04-10,5,   N/A
2020-04-10,6,   N/A
2020-04-10,7,   N/A
2020-04-10,8,   N/A
2020-04-10,9,   N/A
2020-04-10,10,   N/A
2020-04-10,11,   N/A
2020-04-10,12,   N/A
2020-04-10,13,   N/A
2020-04-10,14,   N/A
2020-04-10,15,   N/A
2020-04-10,16,   N/A
2020-04-10,17,   N/A
2020-04-10,18,   N/A
2020-04-10,19,   N/A
2020-04-10,20,   N/A
2020-04-10,21,   N/A
2020-04-10,22,   N/A
2020-04-11,23,   N/A
2020-04-11,0,   N/A
2020-04-11,1,   N/A
2020-04-11,2,   N/A
2020-04-11,3,   N/A
2020-04-11,4,   N/A
2020-04-11,5,   N/A
2020-04-11,6,   N/A
2020-04-11,7,   N/A
2020-04-11,8,   N/A
2020-04-11,9,   N/A
2020-04-11,10,   N/A
2020-04-11,11,   N/A
2020-04-11,12,   N/A
2020-04-11,13,   N/A
2020-04-11,14,   N/A
2020-04-11,15,   N/A
2020-04-11,16,   N/A
2020-04-11,17,   N/A
2020-04-11,18,   N/A
2020-04-11,19,   N/A
2020-04-11,20,   N/A
2020-04-11,21,   N/A
2020-04-11,22,   N/A
2020-04-12,23,   N/A
2020-04-12,0,   N/A
2020-04-12,1,   N/A
2020-04-12,2,   N/A
2020-04-12,3,   N/A
2020-04-12,4,   N/A
2020-04-12,5,   N/A
2020-04-12,6,   N/A
2020-04-12,7,   N/A
2020-04-12,8,   N/A
2020-04-12,9,   N/A
2020-04-12,10,   N/A
2020-04-12,11,   N/A
2020-04-12,12,   N/A
2020-04-12,13,   N/A
2020-04-12,14,   N/A
2020-04-12,15,   N/A
2020-04-12,16,   N/A
2020-04-12,17,   N/A
2020-04-12,18,   N/A
2020-04-12,19,   N/A
2020-04-12,20,   N/A
2020-04-12,21,   N/A
2020-04-12,22,   N/A
2020-04-13,23,   N/A
2020-04-13,0,   N/A
2020-04-13,1,   N/A
2020-04-13,2,   N/A
2020-04-13,3,   N/A
2020-04-13,4,   N/A
2020-04-13,5,   N/A
2020-04-13,6,   N/A
2020-04-13,7,   N/A
2020-04-13,8,   N/A
2020-04-13,9,   N/A
2020-04-13,10,   N/A
2020-04-13,11,   N/A
2020-04-13,12,   N/A
2020-04-13,13,   N/A
2020-04-13,14,   N/A
2020-04-13,15,   N/A
2020-04-13,16,   N/A
2020-04-13,17,   N/A
2020-04-13,18,   N/A
2020-04-13,19,   N/A
2020-04-13,20,   N/A
2020-04-13,21,   N/A
2020-04-13,22,   N/A
2020-04-14,23,   N/A
2020-04-14,0,   N/A
2020-04-14,1,   N/A
2020-04-14,2,   N/A
2020-04-14,3,   N/A
2020-04-14,4,   N/A
2020-04-14,5,   N/A
2020-04-14,6,   N/A
2020-04-14,7,   N/A
2020-04-14,8,   N/A
2020-04-14,9,   N/A
2020-04-14,10,   N/A
2020-04-14,11,   N/A
2020-04-14,12,   N/A
2020-04-14,13,   N/A
2020-04-14,14,   N/A
2020-04-14,15,   N/A
2020-04-14,16,   N/A
2020-04-14,17,   N/A
2020-04-14,18,   N/A
2020-04-14,19,   N/A
2020-04-14,20,   N/A
2020-04-14,21,   N/A
2020-04-14,22,   N/A
2020-04-15,23,   N/A
2020-04-15,0,   N/A
2020-04-15,1,   N/A
2020-04-15,2,   N/A
2020-04-15,3,   N/A
2020-04-15,4,   N/A
2020-04-15,5,   N/A
2020-04-15,6,   N/A
2020-04-15,7,   N/A
2020-04-15,8,   N/A
2020-04-15,9,   N/A
2020-04-15,10,   N/A
2020-04-15,11,   N/A
2020-04-15,12,   N/A
2020-04-15,13,   N/A
2020-04-15,14,   N/A
2020-04-15,15,   N/A
2020-04-15,16,   N/A
2020-04-15,17,   N/A
2020-04-15,18,   N/A
2020-04-15,19,   N/A
2020-04-15,20,   N/A
2020-04-15,21,   N/A
2020-04-15,22,   N/A
2020-04-16,23,   N/A
2020-04-16,0,   N/A
2020-04-16,1,   N/A
2020-04-16,2,   N/A
2020-04-16,3,   N/A
2020-04-16,4,   N/A
2020-04-16,5,   N/A
2020-04-16,6,   N/A
2020-04-16,7,   N/A
2020-04-16,8,   N/A
2020-04-16,9,   N/A
2020-04-16,10,   N/A
2020-04-16,11,   N/A
2020-04-16,12,   N/A
2020-04-16,13,   N/A
2020-04-16,14,   N/A
2020-04-16,15,   N/A
2020-04-16,16,   N/A
2020-04-16,17,   N/A
2020-04-16,18,   N/A
2020-04-16,19,   N/A
2020-04-16,20,   N/A
2020-04-16,21,   N/A
2020-04-16,22,   N/A
2020-04-17,23,   N/A
2020-04-17,0,   N/A
2020-04-17,1,   N/A
2020-04-17,2,   N/A
2020-04-17,3,   N/A
2020-04-17,4,   N/A
2020-04-17,5,   N/A
2020-04-17,6,   N/A
2020-04-17,7,   N/A
2020-04-17,8,   N/A
2020-04-17,9,   N/A
2020-04-17,10,   N/A
2020-04-17,11,   N/A
2020-04-17,12,   N/A
2020-04-17,13,   N/A
2020-04-17,14,   N/A
2020-04-17,15,   N/A
2020-04-17,16,   N/A
2020-04-17,17,   N/A
2020-04-17,18,   N/A
2020-04-17,19,   N/A
2020-04-17,20,   N/A
2020-04-17,21,   N/A
2020-04-17,22,   N/A
2020-04-18,23,   N/A
2020-04-18,0,   N/A
2020-04-18,1,   N/A
2020-04-18,2,   N/A
2020-04-18,3,   N/A
2020-04-18,4,   N/A
2020-04-18,5,   N/A
2020-04-18,6,   N/A
2020-04-18,7,   N/A
2020-04-18,8,   N/A
2020-04-18,9,   N/A
2020-04-18,10,   N/A
2020-04-18,11,   N/A
2020-04-18,12,   N/A
2020-04-18,13,   N/A
2020-04-18,14,   N/A
2020-04-18,15,   N/A
2020-04-18,16,   N/A
2020-04-18,17,   N/A
2020-04-18,18,   N/A
2020-04-18,19,   N/A
2020-04-18,20,   N/A
2020-04-18,21,   N/A
2020-04-18,22,   N/A
2020-04-19,23,   N/A
2020-04-19,0,   N/A
2020-04-19,1,   N/A
2020-04-19,2,   N/A
2020-04-19,3,   N/A
2020-04-19,4,   N/A
2020-04-19,5,   N/A
2020-04-19,6,   N/A
2020-04-19,7,   N/A
2020-04-19,8,   N/A
2020-04-19,9,   N/A
2020-04-19,10,   N/A
2020-04-19,11,   N/A
2020-04-19,12,   N/A
2020-04-19,13,   N/A
2020-04-19,14,   N/A
2020-04-19,15,   N/A
2020-04-19,16,   N/A
2020-04-19,17,   N/A
2020-04-19,18,   N/A
2020-04-19,19,   N/A
2020-04-19,20,   N/A
2020-04-19,21,   N/A
2020-04-19,22,   N/A
2020-04-20,23,   N/A
2020-04-20,0,   N/A
2020-04-20,1,   N/A
2020-04-20,2,   N/A
2020-04-20,3,   N/A
2020-04-20,4,   N/A
2020-04-20,5,   N/A
2020-04-20,6,   N/A
2020-04-20,7,   N/A
2020-04-20,8,   N/A
2020-04-20,9,   N/A
2020-04-20,10,   N/A
2020-04-20,11,   N/A
2020-04-20,12,   N/A
2020-04-20,13,   N/A
2020-04-20,14,   N/A
2020-04-20,15,   N/A
2020-04-20,16,   N/A
2020-04-20,17,   N/A
2020-04-20,18,   N/A
2020-04-20,19,   N/A
2020-04-20,20,   N/A
2020-04-20,21,   N/A
2020-04-20,22,   N/A
2020-04-21,23,   N/A
2020-04-21,0,   N/A
2020-04-21,1,   N/A
2020-04-21,2,   N/A
2020-04-21,3,   N/A
2020-04-21,4,   N/A
2020-04-21,5,   N/A
2020-04-21,6,   N/A
2020-04-21,7,   N/A
2020-04-21,8,   N/A
2020-04-21,9,   N/A
2020-04-21,10,   N/A
2020-04-21,11,   N/A
2020-04-21,12,   N/A
2020-04-21,13,   N/A
2020-04-21,14,   N/A
2020-04-21,15,   N/A
2020-04-21,16,   N/A
2020-04-21,17,   N/A
2020-04-21,18,   N/A
2020-04-21,19,   N/A
2020-04-21,20,   N/A
2020-04-21,21,   N/A
2020-04-21,22,   N/A
2020-04-22,23,   N/A
2020-04-22,0,   N/A
2020-04-22,1,   N/A
2020-04-22,2,   N/A
2020-04-22,3,   N/A
2020-04-22,4,   N/A
2020-04-22,5,   N/A
2020-04-22,6,   N/A
2020-04-22,7,   N/A
2020-04-22,8,   N/A
2020-04-22,9,   N/A
2020-04-22,10,   N/A
2020-04-22,11,   N/A
2020-04-22,12,   N/A
2020-04-22,13,   N/A
2020-04-22,14,   N/A
2020-04-22,15,   N/A
2020-04-22,16,   N/A
2020-04-22,17,   N/A
2020-04-22,18,   N/A
2020-04-22,19,   N/A
2020-04-22,20,   N/A
2020-04-22,21,   N/A
2020-04-22,22,   N/A
2020-04-23,23,   N/A
2020-04-23,0,   N/A
2020-04-23,1,   N/A
2020-04-23,2,   N/A
2020-04-23,3,   N/A
2020-04-23,4,   N/A
2020-04-23,5,   N/A
2020-04-23,6,   N/A
2020-04-23,7,   N/A
2020-04-23,8,   N/A
2020-04-23,9,   N/A
2020-04-23,10,   N/A
2020-04-23,11,   N/A
2020-04-23,12,   N/A
2020-04-23,13,   N/A
2020-04-23,14,   N/A
2020-04-23,15,   N/A
2020-04-23,16,   N/A
2020-04-23,17,   N/A
2020-04-23,18,   N/A
2020-04-23,19,   N/A
2020-04-23,20,   N/A
2020-04-23,21,   N/A
2020-04-23,22,   N/A
2020-04-24,23,   N/A
2020-04-24,0,   N/A
2020-04-24,1,   N/A
2020-04-24,2,   N/A
2020-04-24,3,   N/A
2020-04-24,4,   N/A
2020-04-24,5,   N/A
2020-04-24,6,   N/A
2020-04-24,7,   N/A
2020-04-24,8,   N/A
2020-04-24,9,   N/A
2020-04-24,10,   N/A
2020-04-24,11,   N/A
2020-04-24,12,   N/A
2020-04-24,13,   N/A
2020-04-24,14,   N/A
2020-04-24,15,   N/A
2020-04-24,16,   N/A
2020-04-24,17,   N/A
2020-04-24,18,   N/A
2020-04-24,19,   N/A
2020-04-24,20,   N/A
2020-04-24,21,   N/A
2020-04-24,22,   N/A
2020-04-25,23,   N/A
2020-04-25,0,   N/A
2020-04-25,1,   N/A
2020-04-25,2,   N/A
2020-04-25,3,   N/A
2020-04-25,4,   N/A
2020-04-25,5,   N/A
2020-04-25,6,   N/A
2020-04-25,7,   N/A
2020-04-25,8,   N/A
2020-04-25,9,   N/A
2020-04-25,10,   N/A
2020-04-25,11,   N/A
2020-04-25,12,   N/A
2020-04-25,13,   N/A
2020-04-25,14,   N/A
2020-04-25,15,   N/A
2020-04-25,16,   N/A
2020-04-25,17,   N/A
2020-04-25,18,   N/A
2020-04-25,19,   N/A
2020-04-25,20,   N/A
2020-04-25,21,   N/A
2020-04-25,22,   N/A
2020-04-26,23,   N/A
2020-04-26,0,   N/A
2020-04-26,1,   N/A
2020-04-26,2,   N/A
2020-04-26,3,   N/A
2020-04-26,4,   N/A
2020-04-26,5,   N/A
2020-04-26,6,   N/A
2020-04-26,7,   N/A
2020-04-26,8,   N/A
2020-04-26,9,   N/A
2020-04-26,10,   N/A
2020-04-26,11,   N/A
2020-04-26,12,   N/A
2020-04-26,13,   N/A
2020-04-26,14,   N/A
2020-04-26,15,   N/A
2020-04-26,16,   N/A
2020-04-26,17,   N/A
2020-04-26,18,   N/A
2020-04-26,19,   N/A
2020-04-26,20,   N/A
2020-04-26,21,   N/A
2020-04-26,22,   N/A
2020-04-27,23,   N/A
2020-04-27,0,   N/A
2020-04-27,1,   N/A
2020-04-27,2,   N/A
2020-04-27,3,   N/A
2020-04-27,4,   N/A
2020-04-27,5,   N/A
2020-04-27,6,   N/A
2020-04-27,7,   N/A
2020-04-27,8,   N/A
2020-04-27,9,   N/A
2020-04-27,10,   N/A
2020-04-27,11,   N/A
2020-04-27,12,   N/A
2020-04-27,13,   N/A
2020-04-27,14,   N/A
2020-04-27,15,   N/A
2020-04-27,16,   N/A
2020-04-27,17,   N/A
2020-04-27,18,   N/A
2020-04-27,19,   N/A
2020-04-27,20,   N/A
2020-04-27,21,   N/A
2020-04-27,22,   N/A
2020-04-28,23,   N/A
2020-04-28,0,   N/A
2020-04-28,1,   N/A
2020-04-28,2,   N/A
2020-04-28,3,   N/A
2020-04-28,4,   N/A
2020-04-28,5,   N/A
2020-04-28,6,   N/A
2020-04-28,7,   N/A
2020-04-28,8,   N/A
2020-04-28,9,   N/A
2020-04-28,10,   N/A
2020-04-28,11,   N/A
2020-04-28,12,   N/A
2020-04-28,13,   N/A
2020-04-28,14,   N/A
2020-04-28,15,   N/A
2020-04-28,16,   N/A
2020-04-28,17,   N/A
2020-04-28,18,   N/A
2020-04-28,19,   N/A
2020-04-28,20,   N/A
2020-04-28,21,   N/A
2020-04-28,22,   N/A
2020-04-29,23,   N/A
2020-04-29,0,   N/A
2020-04-29,1,   N/A
2020-04-29,2,   N/A
2020-04-29,3,   N/A
2020-04-29,4,   N/A
2020-04-29,5,   N/A
2020-04-29,6,   N/A
2020-04-29,7,   N/A
2020-04-29,8,   N/A
2020-04-29,9,   N/A
2020-04-29,10,   N/A
2020-04-29,11,   N/A
2020-04-29,12,   N/A
2020-04-29,13,   N/A
2020-04-29,14,   N/A
2020-04-29,15,   N/A
2020-04-29,16,   N/A
2020-04-29,17,   N/A
2020-04-29,18,   N/A
2020-04-29,19,   N/A
2020-04-29,20,   N/A
2020-04-29,21,   N/A
2020-04-29,22,   N/A
2020-04-30,23,   N/A
2020-04-30,0,   N/A
2020-04-30,1,   N/A
2020-04-30,2,   N/A
2020-04-30,3,   N/A
2020-04-30,4,   N/A
2020-04-30,5,   N/A
2020-04-30,6,   N/A
2020-04-30,7,   N/A
2020-04-30,8,   N/A
2020-04-30,9,   N/A
2020-04-30,10,   N/A
2020-04-30,11,   N/A
2020-04-30,12,   N/A
2020-04-30,13,   N/A
2020-04-30,14,   N/A
2020-04-30,15,   N/A
2020-04-30,16,   N/A
2020-04-30,17,   N/A
2020-04-30,18,   N/A
2020-04-30,19,   N/A
2020-04-30,20,   N/A
2020-04-30,21,   N/A
2020-04-30,22,   N/A
2020-05-01,23,   N/A
2020-05-01,0,   N/A
2020-05-01,1,   N/A
2020-05-01,2,   N/A
2020-05-01,3,   N/A
2020-05-01,4,   N/A
2020-05-01,5,   N/A
2020-05-01,6,   N/A
2020-05-01,7,   N/A
2020-05-01,8,   N/A
2020-05-01,9,   N/A
2020-05-01,10,   N/A
2020-05-01,11,   N/A
2020-05-01,12,   N/A
2020-05-01,13,   N/A
2020-05-01,14,   N/A
2020-05-01,15,   N/A
2020-05-01,16,   N/A
2020-05-01,17,   N/A
2020-05-01,18,   N/A
2020-05-01,19,   N/A
2020-05-01,20,   N/A
2020-05-01,21,   N/A
2020-05-01,22,   N/A
2020-05-02,23,   N/A
2020-05-02,0,   N/A
2020-05-02,1,   N/A
2020-05-02,2,   N/A
2020-05-02,3,   N/A
2020-05-02,4,   N/A
2020-05-02,5,   N/A
2020-05-02,6,   N/A
2020-05-02,7,   N/A
2020-05-02,8,   N/A
2020-05-02,9,   N/A
2020-05-02,10,   N/A
2020-05-02,11,   N/A
2020-05-02,12,   N/A
2020-05-02,13,   N/A
2020-05-02,14,   N/A
2020-05-02,15,   N/A
2020-05-02,16,   N/A
2020-05-02,17,   N/A
2020-05-02,18,   N/A
2020-05-02,19,   N/A
2020-05-02,20,   N/A
2020-05-02,21,   N/A
2020-05-02,22,   N/A
2020-05-03,23,   N/A
2020-05-03,0,   N/A
2020-05-03,1,   N/A
2020-05-03,2,   N/A
2020-05-03,3,   N/A
2020-05-03,4,   N/A
2020-05-03,5,   N/A
2020-05-03,6,   N/A
2020-05-03,7,   N/A
2020-05-03,8,   N/A
2020-05-03,9,   N/A
2020-05-03,10,   N/A
2020-05-03,11,   N/A
2020-05-03,12,   N/A
2020-05-03,13,   N/A
2020-05-03,14,   N/A
2020-05-03,15,   N/A
2020-05-03,16,   N/A
2020-05-03,17,   N/A
2020-05-03,18,   N/A
2020-05-03,19,   N/A
2020-05-03,20,   N/A
2020-05-03,21,   N/A
2020-05-03,22,   N/A
2020-05-04,23,   N/A
2020-05-04,0,   N/A
2020-05-04,1,   N/A
2020-05-04,2,   N/A
2020-05-04,3,   N/A
2020-05-04,4,   N/A
2020-05-04,5,   N/A
2020-05-04,6,   N/A
2020-05-04,7,   N/A
2020-05-04,8,   N/A
2020-05-04,9,   N/A
2020-05-04,10,   N/A
2020-05-04,11,   N/A
2020-05-04,12,   N/A
2020-05-04,13,   N/A
2020-05-04,14,   N/A
2020-05-04,15,   N/A
2020-05-04,16,   N/A
2020-05-04,17,   N/A
2020-05-04,18,   N/A
2020-05-04,19,   N/A
2020-05-04,20,   N/A
2020-05-04,21,   N/A
2020-05-04,22,   N/A
2020-05-05,23,   N/A
2020-05-05,0,   N/A
2020-05-05,1,   N/A
2020-05-05,2,   N/A
2020-05-05,3,   N/A
2020-05-05,4,   N/A
2020-05-05,5,   N/A
2020-05-05,6,   N/A
2020-05-05,7,   N/A
2020-05-05,8,   N/A
2020-05-05,9,   N/A
2020-05-05,10,   N/A
2020-05-05,11,   N/A
2020-05-05,12,   N/A
2020-05-05,13,   N/A
2020-05-05,14,   N/A
2020-05-05,15,   N/A
2020-05-05,16,   N/A
2020-05-05,17,   N/A
2020-05-05,18,   N/A
2020-05-05,19,   N/A
2020-05-05,20,   N/A
2020-05-05,21,   N/A
2020-05-05,22,   N/A
2020-05-06,23,   N/A
2020-05-06,0,   N/A
2020-05-06,1,   N/A
2020-05-06,2,   N/A
2020-05-06,3,   N/A
2020-05-06,4,   N/A
2020-05-06,5,   N/A
2020-05-06,6,   N/A
2020-05-06,7,   N/A
2020-05-06,8,   N/A
2020-05-06,9,   N/A
2020-05-06,10,   N/A
2020-05-06,11,   N/A
2020-05-06,12,   N/A
2020-05-06,13,   N/A
2020-05-06,14,   N/A
2020-05-06,15,   N/A
2020-05-06,16,   N/A
2020-05-06,17,   N/A
2020-05-06,18,   N/A
2020-05-06,19,   N/A
2020-05-06,20,   N/A
2020-05-06,21,   N/A
2020-05-06,22,   N/A
2020-05-07,23,   N/A
2020-05-07,0,   N/A
2020-05-07,1,   N/A
2020-05-07,2,   N/A
2020-05-07,3,   N/A
2020-05-07,4,   N/A
2020-05-07,5,   N/A
2020-05-07,6,   N/A
2020-05-07,7,   N/A
2020-05-07,8,   N/A
2020-05-07,9,   N/A
2020-05-07,10,   N/A
2020-05-07,11,   N/A
2020-05-07,12,   N/A
2020-05-07,13,   N/A
2020-05-07,14,   N/A
2020-05-07,15,   N/A
2020-05-07,16,   N/A
2020-05-07,17,   N/A
2020-05-07,18,   N/A
2020-05-07,19,   N/A
2020-05-07,20,   N/A
2020-05-07,21,   N/A
2020-05-07,22,   N/A
2020-05-08,23,   N/A
2020-05-08,0,   N/A
2020-05-08,1,   N/A
2020-05-08,2,   N/A
2020-05-08,3,   N/A
2020-05-08,4,   N/A
2020-05-08,5,   N/A
2020-05-08,6,   N/A
2020-05-08,7,   N/A
2020-05-08,8,   N/A
2020-05-08,9,   N/A
2020-05-08,10,   N/A
2020-05-08,11,   N/A
2020-05-08,12,   N/A
2020-05-08,13,   N/A
2020-05-08,14,   N/A
2020-05-08,15,   N/A
2020-05-08,16,   N/A
2020-05-08,17,   N/A
2020-05-08,18,   N/A
2020-05-08,19,   N/A
2020-05-08,20,   N/A
2020-05-08,21,   N/A
2020-05-08,22,   N/A
2020-05-09,23,   N/A
2020-05-09,0,   N/A
2020-05-09,1,   N/A
2020-05-09,2,   N/A
2020-05-09,3,   N/A
2020-05-09,4,   N/A
2020-05-09,5,   N/A
2020-05-09,6,   N/A
2020-05-09,7,   N/A
2020-05-09,8,   N/A
2020-05-09,9,   N/A
2020-05-09,10,   N/A
2020-05-09,11,   N/A
2020-05-09,12,   N/A
2020-05-09,13,   N/A
2020-05-09,14,   N/A
2020-05-09,15,   N/A
2020-05-09,16,   N/A
2020-05-09,17,   N/A
2020-05-09,18,   N/A
2020-05-09,19,   N/A
2020-05-09,20,   N/A
2020-05-09,21,   N/A
2020-05-09,22,   N/A
2020-05-10,23,   N/A
2020-05-10,0,   N/A
2020-05-10,1,   N/A
2020-05-10,2,   N/A
2020-05-10,3,   N/A
2020-05-10,4,   N/A
2020-05-10,5,   N/A
2020-05-10,6,   N/A
2020-05-10,7,   N/A
2020-05-10,8,   N/A
2020-05-10,9,   N/A
2020-05-10,10,   N/A
2020-05-10,11,   N/A
2020-05-10,12,   N/A
2020-05-10,13,   N/A
2020-05-10,14,   N/A
2020-05-10,15,   N/A
2020-05-10,16,   N/A
2020-05-10,17,   N/A
2020-05-10,18,   N/A
2020-05-10,19,   N/A
2020-05-10,20,   N/A
2020-05-10,21,   N/A
2020-05-10,22,   N/A
2020-05-11,23,   N/A
2020-05-11,0,   N/A
2020-05-11,1,   N/A
2020-05-11,2,   N/A
2020-05-11,3,   N/A
2020-05-11,4,   N/A
2020-05-11,5,   N/A
2020-05-11,6,   N/A
2020-05-11,7,   N/A
2020-05-11,8,   N/A
2020-05-11,9,   N/A
2020-05-11,10,   N/A
2020-05-11,11,   N/A
2020-05-11,12,   N/A
2020-05-11,13,   N/A
2020-05-11,14,   N/A
2020-05-11,15,   N/A
2020-05-11,16,   N/A
2020-05-11,17,   N/A
2020-05-11,18,   N/A
2020-05-11,19,   N/A
2020-05-11,20,   N/A
2020-05-11,21,   N/A
2020-05-11,22,   N/A
2020-05-12,23,   N/A
2020-05-12,0,   N/A
2020-05-12,1,   N/A
2020-05-12,2,   N/A
2020-05-12,3,   N/A
2020-05-12,4,   N/A
2020-05-12,5,   N/A
2020-05-12,6,   N/A
2020-05-12,7,   N/A
2020-05-12,8,   N/A
2020-05-12,9,   N/A
2020-05-12,10,   N/A
2020-05-12,11,   N/A
2020-05-12,12,   N/A
2020-05-12,13,   N/A
2020-05-12,14,   N/A
2020-05-12,15,   N/A
2020-05-12,16,   N/A
2020-05-12,17,   N/A
2020-05-12,18,   N/A
2020-05-12,19,   N/A
2020-05-12,20,   N/A
2020-05-12,21,   N/A
2020-05-12,22,   N/A
2020-05-13,23,   N/A
2020-05-13,0,   N/A
2020-05-13,1,   N/A
2020-05-13,2,   N/A
2020-05-13,3,   N/A
2020-05-13,4,   N/A
2020-05-13,5,   N/A
2020-05-13,6,   N/A
2020-05-13,7,   N/A
2020-05-13,8,   N/A
2020-05-13,9,   N/A
2020-05-13,10,   N/A
2020-05-13,11,   N/A
2020-05-13,12,   N/A
2020-05-13,13,   N/A
2020-05-13,14,   N/A
2020-05-13,15,   N/A
2020-05-13,16,   N/A
2020-05-13,17,   N/A
2020-05-13,18,   N/A
2020-05-13,19,   N/A
2020-05-13,20,   N/A
2020-05-13,21,   N/A
2020-05-13,22,   N/A
2020-05-14,23,   N/A
2020-05-14,0,   N/A
2020-05-14,1,   N/A
2020-05-14,2,   N/A
2020-05-14,3,   N/A
2020-05-14,4,   N/A
2020-05-14,5,   N/A
2020-05-14,6,   N/A
2020-05-14,7,   N/A
2020-05-14,8,   N/A
2020-05-14,9,   N/A
2020-05-14,10,   N/A
2020-05-14,11,   N/A
2020-05-14,12,   N/A
2020-05-14,13,   N/A
2020-05-14,14,   N/A
2020-05-14,15,   N/A
2020-05-14,16,   N/A
2020-05-14,17,   N/A
2020-05-14,18,   N/A
2020-05-14,19,   N/A
2020-05-14,20,   N/A
2020-05-14,21,   N/A
2020-05-14,22,   N/A
2020-05-15,23,   N/A
2020-05-15,0,   N/A
2020-05-15,1,   N/A
2020-05-15,2,   N/A
2020-05-15,3,   N/A
2020-05-15,4,   N/A
2020-05-15,5,   N/A
2020-05-15,6,   N/A
2020-05-15,7,   N/A
2020-05-15,8,   N/A
2020-05-15,9,   N/A
2020-05-15,10,   N/A
2020-05-15,11,   N/A
2020-05-15,12,   N/A
2020-05-15,13,   N/A
2020-05-15,14,   N/A
2020-05-15,15,   N/A
2020-05-15,16,   N/A
2020-05-15,17,   N/A
2020-05-15,18,   N/A
2020-05-15,19,   N/A
2020-05-15,20,   N/A
2020-05-15,21,   N/A
2020-05-15,22,   N/A
2020-05-16,23,   N/A
2020-05-16,0,   N/A
2020-05-16,1,   N/A
2020-05-16,2,   N/A
2020-05-16,3,   N/A
2020-05-16,4,   N/A
2020-05-16,5,   N/A
2020-05-16,6,   N/A
2020-05-16,7,   N/A
2020-05-16,8,   N/A
2020-05-16,9,   N/A
2020-05-16,10,   N/A
2020-05-16,11,   N/A
2020-05-16,12,   N/A
2020-05-16,13,   N/A
2020-05-16,14,   N/A
2020-05-16,15,   N/A
2020-05-16,16,   N/A
2020-05-16,17,   N/A
2020-05-16,18,   N/A
2020-05-16,19,   N/A
2020-05-16,20,   N/A
2020-05-16,21,   N/A
2020-05-16,22,   N/A
2020-05-17,23,   N/A
2020-05-17,0,   N/A
2020-05-17,1,   N/A
2020-05-17,2,   N/A
2020-05-17,3,   N/A
2020-05-17,4,   N/A
2020-05-17,5,   N/A
2020-05-17,6,   N/A
2020-05-17,7,   N/A
2020-05-17,8,   N/A
2020-05-17,9,   N/A
2020-05-17,10,   N/A
2020-05-17,11,   N/A
2020-05-17,12,   N/A
2020-05-17,13,   N/A
2020-05-17,14,   N/A
2020-05-17,15,   N/A
2020-05-17,16,   N/A
2020-05-17,17,   N/A
2020-05-17,18,   N/A
2020-05-17,19,   N/A
2020-05-17,20,   N/A
2020-05-17,21,   N/A
2020-05-17,22,   N/A
2020-05-18,23,   N/A
2020-05-18,0,   N/A
2020-05-18,1,   N/A
2020-05-18,2,   N/A
2020-05-18,3,   N/A
2020-05-18,4,   N/A
2020-05-18,5,   N/A
2020-05-18,6,   N/A
2020-05-18,7,   N/A
2020-05-18,8,   N/A
2020-05-18,9,   N/A
2020-05-18,10,   N/A
2020-05-18,11,   N/A
2020-05-18,12,   N/A
2020-05-18,13,   N/A
2020-05-18,14,   N/A
2020-05-18,15,   N/A
2020-05-18,16,   N/A
2020-05-18,17,   N/A
2020-05-18,18,   N/A
2020-05-18,19,   N/A
2020-05-18,20,   N/A
2020-05-18,21,   N/A
2020-05-18,22,   N/A
2020-05-19,23,   N/A
2020-05-19,0,   N/A
2020-05-19,1,   N/A
2020-05-19,2,   N/A
2020-05-19,3,   N/A
2020-05-19,4,   N/A
2020-05-19,5,   N/A
2020-05-19,6,   N/A
2020-05-19,7,   N/A
2020-05-19,8,   N/A
2020-05-19,9,   N/A
2020-05-19,10,   N/A
2020-05-19,11,   N/A
2020-05-19,12,   N/A
2020-05-19,13,   N/A
2020-05-19,14,   N/A
2020-05-19,15,   N/A
2020-05-19,16,   N/A
2020-05-19,17,   N/A
2020-05-19,18,   N/A
2020-05-19,19,   N/A
2020-05-19,20,   N/A
2020-05-19,21,   N/A
2020-05-19,22,   N/A
2020-05-20,23,   N/A
2020-05-20,0,   N/A
2020-05-20,1,   N/A
2020-05-20,2,   N/A
2020-05-20,3,   N/A
2020-05-20,4,   N/A
2020-05-20,5,   N/A
2020-05-20,6,   N/A
2020-05-20,7,   N/A
2020-05-20,8,   N/A
2020-05-20,9,   N/A
2020-05-20,10,   N/A
2020-05-20,11,   N/A
2020-05-20,12,   N/A
2020-05-20,13,   N/A
2020-05-20,14,   N/A
2020-05-20,15,   N/A
2020-05-20,16,   N/A
2020-05-20,17,   N/A
2020-05-20,18,   N/A
2020-05-20,19,   N/A
2020-05-20,20,   N/A
2020-05-20,21,   N/A
2020-05-20,22,16.2
2020-05-21,23,15.1
2020-05-21,0,14.7
2020-05-21,1,12.4
2020-05-21,2,10.1
2020-05-21,3,9.5
2020-05-21,4,8.7
2020-05-21,5,12.0
2020-05-21,6,15.3
2020-05-21,7,16.0
2020-05-21,8,18.4
2020-05-21,9,20.8
2020-05-21,10,21.8
2020-05-21,11,21.9
2020-05-21,12,22.7
2020-05-21,13,23.5
2020-05-21,14,24.0
2020-05-21,15,23.6
2020-05-21,16,23.8
2020-05-21,17,23.7
2020-05-21,18,22.4
2020-05-21,19,21.6
2020-05-21,20,20.4
2020-05-21,21,18.7
2020-05-21,22,18.3
2020-05-22,23,17.5
2020-05-22,0,17.1
2020-05-22,1,17.2
2020-05-22,2,17.3
2020-05-22,3,17.3
2020-05-22,4,16.8
2020-05-22,5,15.1
2020-05-22,6,16.2
2020-05-22,7,16.8
2020-05-22,8,16.8
2020-05-22,9,17.7
2020-05-22,10,18.4
2020-05-22,11,19.1
2020-05-22,12,19.8
2020-05-22,13,20.0
2020-05-22,14,19.6
2020-05-22,15,19.3
2020-05-22,16,18.1
2020-05-22,17,17.7
2020-05-22,18,16.1
2020-05-22,19,14.9
2020-05-22,20,13.3
2020-05-22,21,12.3
2020-05-22,22,11.3
2020-05-23,23,11.4
2020-05-23,0,11.1
2020-05-23,1,10.4
2020-05-23,2,10.0
2020-05-23,3,9.6
2020-05-23,4,9.5
2020-05-23,5,11.1
2020-05-23,6,12.5
2020-05-23,7,13.8
2020-05-23,8,15.3
2020-05-23,9,15.6
2020-05-23,10,15.6
2020-05-23,11,16.2
2020-05-23,12,16.1
2020-05-23,13,16.7
2020-05-23,14,17.2
2020-05-23,15,16.5
2020-05-23,16,15.9
2020-05-23,17,14.5
2020-05-23,18,13.8
2020-05-23,19,13.0
2020-05-23,20,12.1
2020-05-23,21,11.5
2020-05-23,22,10.6
2020-05-24,23,10.8
2020-05-24,0,10.3
2020-05-24,1,10.9
2020-05-24,2,11.2
2020-05-24,3,11.3
2020-05-24,4,11.6
2020-05-24,5,11.9
2020-05-24,6,13.1
2020-05-24,7,14.9
2020-05-24,8,15.9
2020-05-24,9,16.8
2020-05-24,10,17.6
2020-05-24,11,17.8
2020-05-24,12,18.9
2020-05-24,13,19.4
2020-05-24,14,20.0
2020-05-24,15,20.2
2020-05-24,16,19.3
2020-05-24,17,19.0
2020-05-24,18,19.0
2020-05-24,19,18.5
2020-05-24,20,15.1
2020-05-24,21,13.1
2020-05-24,22,12.5
2020-05-25,23,10.9
2020-05-25,0,10.3
2020-05-25,1,8.6
2020-05-25,2,6.6
2020-05-25,3,5.7
2020-05-25,4,4.9
2020-05-25,5,9.0
2020-05-25,6,13.5
2020-05-25,7,16.3
2020-05-25,8,18.1
2020-05-25,9,20.0
2020-05-25,10,20.9
2020-05-25,11,22.3
2020-05-25,12,22.9
2020-05-25,13,23.5
2020-05-25,14,23.8
2020-05-25,15,23.8
2020-05-25,16,22.8
2020-05-25,17,22.1
2020-05-25,18,21.5
2020-05-25,19,18.6
2020-05-25,20,16.5
2020-05-25,21,13.6
2020-05-25,22,12.5
2020-05-26,23,12.6
2020-05-26,0,10.5
2020-05-26,1,8.4
2020-05-26,2,7.6
2020-05-26,3,6.6
2020-05-26,4,7.4
2020-05-26,5,10.8
2020-05-26,6,15.1
2020-05-26,7,16.5
2020-05-26,8,18.9
2020-05-26,9,19.8
2020-05-26,10,21.1
2020-05-26,11,22.3
2020-05-26,12,22.1
2020-05-26,13,22.5
2020-05-26,14,22.5
2020-05-26,15,22.5
2020-05-26,16,22.7
2020-05-26,17,21.9
2020-05-26,18,21.6
2020-05-26,19,20.6
2020-05-26,20,18.8
2020-05-26,21,17.4
2020-05-26,22,16.4
2020-05-27,23,15.6
2020-05-27,0,14.7
2020-05-27,1,13.4
2020-05-27,2,11.3
2020-05-27,3,11.6
2020-05-27,4,11.0
2020-05-27,5,14.5
2020-05-27,6,17.4
2020-05-27,7,19.0
2020-05-27,8,20.7
2020-05-27,9,21.8
2020-05-27,10,22.3
2020-05-27,11,23.3
2020-05-27,12,21.5
2020-05-27,13,22.6
2020-05-27,14,23.1
2020-05-27,15,22.7
2020-05-27,16,23.5
2020-05-27,17,23.3
2020-05-27,18,21.7
2020-05-27,19,19.1
2020-05-27,20,17.0
2020-05-27,21,15.0
2020-05-27,22,14.8
2020-05-28,23,12.9
2020-05-28,0,11.0
2020-05-28,1,10.5
2020-05-28,2,9.8
2020-05-28,3,8.9
2020-05-28,4,8.8
2020-05-28,5,11.1
2020-05-28,6,11.4
2020-05-28,7,14.4
2020-05-28,8,16.7
2020-05-28,9,17.9
2020-05-28,10,19.1
2020-05-28,11,20.2
2020-05-28,12,21.3
2020-05-28,13,22.2
2020-05-28,14,22.1
2020-05-28,15,22.1
2020-05-28,16,21.2
2020-05-28,17,20.8
2020-05-28,18,19.6
2020-05-28,19,18.3
2020-05-28,20,16.3
2020-05-28,21,14.4
2020-05-28,22,13.1
2020-05-29,23,9.9
2020-05-29,0,8.1
2020-05-29,1,6.7
2020-05-29,2,6.7
2020-05-29,3,5.4
2020-05-29,4,6.0
2020-05-29,5,9.8
2020-05-29,6,14.0
2020-05-29,7,16.4
2020-05-29,8,18.5
2020-05-29,9,19.3
2020-05-29,10,20.8
2020-05-29,11,21.5
2020-05-29,12,22.7
2020-05-29,13,23.2
2020-05-29,14,23.4
2020-05-29,15,23.4
2020-05-29,16,23.0
2020-05-29,17,22.4
2020-05-29,18,21.4
2020-05-29,19,20.1
2020-05-29,20,17.5
2020-05-29,21,15.2
2020-05-29,22,10.7
2020-05-30,23,8.7
2020-05-30,0,8.0
2020-05-30,1,7.1
2020-05-30,2,6.3
2020-05-30,3,5.8
2020-05-30,4,6.3
2020-05-30,5,9.7
2020-05-30,6,12.7
2020-05-30,7,14.8
2020-05-30,8,17.5
2020-05-30,9,19.8
2020-05-30,10,21.1
2020-05-30,11,22.7
2020-05-30,12,23.1
2020-05-30,13,24.3
2020-05-30,14,24.7
2020-05-30,15,24.3
2020-05-30,16,23.6
2020-05-30,17,23.1
2020-05-30,18,21.5
2020-05-30,19,20.0
2020-05-30,20,17.8
2020-05-30,21,14.0
2020-05-30,22,13.4
2020-05-31,23,12.3
2020-05-31,0,11.1
2020-05-31,1,9.5
2020-05-31,2,8.7
2020-05-31,3,8.1
2020-05-31,4,9.1
2020-05-31,5,11.3
2020-05-31,6,13.7
2020-05-31,7,16.8
2020-05-31,8,18.8
2020-05-31,9,20.8
2020-05-31,10,22.8
2020-05-31,11,23.5
2020-05-31,12,24.2
2020-05-31,13,24.2
2020-05-31,14,24.4
2020-05-31,15,23.7
2020-05-31,16,23.0
2020-05-31,17,22.4
2020-05-31,18,21.0
2020-05-31,19,19.6
2020-05-31,20,17.4
2020-05-31,21,14.6
2020-05-31,22,12.6
2020-06-01,23,11.6
2020-06-01,0,10.4
2020-06-01,1,9.0
2020-06-01,2,8.4
2020-06-01,3,8.1
2020-06-01,4,8.7
2020-06-01,5,11.6
2020-06-01,6,13.5
2020-06-01,7,15.9
2020-06-01,8,18.4
2020-06-01,9,19.9
2020-06-01,10,21.8
2020-06-01,11,22.9
2020-06-01,12,23.2
2020-06-01,13,24.3
2020-06-01,14,24.2
2020-06-01,15,24.9
2020-06-01,16,24.7
2020-06-01,17,23.7
2020-06-01,18,22.3
2020-06-01,19,20.7
2020-06-01,20,18.3
2020-06-01,21,16.1
2020-06-01,22,14.2
2020-06-02,23,12.6
2020-06-02,0,11.0
2020-06-02,1,10.1
2020-06-02,2,9.6
2020-06-02,3,9.3
2020-06-02,4,9.1
2020-06-02,5,10.4
2020-06-02,6,13.0
2020-06-02,7,15.7
2020-06-02,8,17.4
2020-06-02,9,18.4
2020-06-02,10,19.7
2020-06-02,11,20.8
2020-06-02,12,21.2
2020-06-02,13,22.0
2020-06-02,14,22.7
2020-06-02,15,22.7
2020-06-02,16,22.7
2020-06-02,17,22.6
2020-06-02,18,21.9
2020-06-02,19,21.0
2020-06-02,20,16.4
2020-06-02,21,13.5
2020-06-02,22,11.9
2020-06-03,23,12.1
2020-06-03,0,   N/A
2020-06-03,1,   N/A
2020-06-03,2,   N/A
2020-06-03,3,   N/A
2020-06-03,4,   N/A
2020-06-03,5,13.0
2020-06-03,6,13.4
2020-06-03,7,13.9
2020-06-03,8,14.0
2020-06-03,9,15.7
2020-06-03,10,16.5
2020-06-03,11,17.0
2020-06-03,12,16.1
2020-06-03,13,14.6
2020-06-03,14,15.0
2020-06-03,15,14.3
2020-06-03,16,13.7
2020-06-03,17,13.6
2020-06-03,18,13.3
2020-06-03,19,12.1
2020-06-03,20,11.6
2020-06-03,21,10.9
2020-06-03,22,10.3
2020-06-04,23,10.1
2020-06-04,0,9.9
2020-06-04,1,9.7
2020-06-04,2,9.6
2020-06-04,3,9.4
2020-06-04,4,9.4
2020-06-04,5,10.1
2020-06-04,6,10.3
2020-06-04,7,10.7
2020-06-04,8,11.2
2020-06-04,9,12.1
2020-06-04,10,13.1
2020-06-04,11,13.0
2020-06-04,12,12.6
2020-06-04,13,13.8
2020-06-04,14,12.8
2020-06-04,15,12.8
2020-06-04,16,12.7
2020-06-04,17,12.8
2020-06-04,18,12.4
2020-06-04,19,11.6
2020-06-04,20,11.2
2020-06-04,21,10.8
2020-06-04,22,10.4
2020-06-05,23,10.3
2020-06-05,0,10.3
2020-06-05,1,10.4
2020-06-05,2,9.9
2020-06-05,3,9.6
2020-06-05,4,9.7
2020-06-05,5,10.2
2020-06-05,6,11.2
2020-06-05,7,12.1
2020-06-05,8,12.4
2020-06-05,9,12.8
2020-06-05,10,13.4
2020-06-05,11,13.9
2020-06-05,12,14.0
2020-06-05,13,13.8
2020-06-05,14,15.3
2020-06-05,15,15.4
2020-06-05,16,14.8
2020-06-05,17,14.4
2020-06-05,18,13.5
2020-06-05,19,12.3
2020-06-05,20,11.3
2020-06-05,21,10.2
2020-06-05,22,9.0
2020-06-06,23,8.0
2020-06-06,0,6.7
2020-06-06,1,6.5
2020-06-06,2,6.1
2020-06-06,3,6.0
2020-06-06,4,6.3
2020-06-06,5,8.5
2020-06-06,6,9.6
2020-06-06,7,9.8
2020-06-06,8,10.2
2020-06-06,9,10.6
2020-06-06,10,11.3
2020-06-06,11,12.2
2020-06-06,12,12.4
2020-06-06,13,14.9
2020-06-06,14,15.0
2020-06-06,15,12.5
2020-06-06,16,12.2
2020-06-06,17,11.2
2020-06-06,18,11.1
2020-06-06,19,10.6
2020-06-06,20,10.4
2020-06-06,21,9.4
2020-06-06,22,8.9
2020-06-07,23,8.7
2020-06-07,0,8.4
2020-06-07,1,7.5
2020-06-07,2,7.2
2020-06-07,3,7.1
2020-06-07,4,6.7
2020-06-07,5,8.3
2020-06-07,6,10.2
2020-06-07,7,13.0
2020-06-07,8,13.7
2020-06-07,9,14.4
2020-06-07,10,15.4
2020-06-07,11,15.9
2020-06-07,12,15.6
2020-06-07,13,14.5
2020-06-07,14,13.4
2020-06-07,15,13.6
2020-06-07,16,13.8
2020-06-07,17,13.7
2020-06-07,18,13.2
2020-06-07,19,12.1
2020-06-07,20,11.2
2020-06-07,21,10.9
2020-06-07,22,10.5
2020-06-08,23,9.0
2020-06-08,0,8.9
2020-06-08,1,9.2
2020-06-08,2,8.5
2020-06-08,3,9.0
2020-06-08,4,9.0
2020-06-08,5,9.2
2020-06-08,6,10.2
2020-06-08,7,11.1
2020-06-08,8,12.6
2020-06-08,9,13.1
2020-06-08,10,13.4
2020-06-08,11,13.7
2020-06-08,12,14.6
2020-06-08,13,14.2
2020-06-08,14,14.9
2020-06-08,15,15.4
2020-06-08,16,16.0
2020-06-08,17,16.0
2020-06-08,18,14.7
2020-06-08,19,13.7
2020-06-08,20,13.1
2020-06-08,21,11.9
2020-06-08,22,11.1
2020-06-09,23,10.8
2020-06-09,0,10.2
2020-06-09,1,9.8
2020-06-09,2,9.2
2020-06-09,3,8.9
2020-06-09,4,9.0
2020-06-09,5,9.9
2020-06-09,6,11.0
2020-06-09,7,13.9
2020-06-09,8,15.0
2020-06-09,9,16.4
2020-06-09,10,16.8
2020-06-09,11,17.2
2020-06-09,12,17.9
2020-06-09,13,18.7
2020-06-09,14,19.2
2020-06-09,15,18.5
2020-06-09,16,18.2
2020-06-09,17,17.9
2020-06-09,18,17.3
2020-06-09,19,16.7
2020-06-09,20,15.5
2020-06-09,21,14.8
2020-06-09,22,13.8
2020-06-10,23,12.8
2020-06-10,0,12.4
2020-06-10,1,12.3
2020-06-10,2,12.1
2020-06-10,3,11.5
2020-06-10,4,11.3
2020-06-10,5,11.1
2020-06-10,6,11.6
2020-06-10,7,12.8
2020-06-10,8,14.0
2020-06-10,9,14.6
2020-06-10,10,15.6
2020-06-10,11,16.3
2020-06-10,12,16.1
2020-06-10,13,15.5
2020-06-10,14,15.8
2020-06-10,15,15.9
2020-06-10,16,14.4
2020-06-10,17,14.0
2020-06-10,18,13.1
2020-06-10,19,11.7
2020-06-10,20,11.5
2020-06-10,21,11.3
2020-06-10,22,10.8
2020-06-11,23,10.5
2020-06-11,0,10.5
2020-06-11,1,10.4
2020-06-11,2,10.5
2020-06-11,3,10.6
2020-06-11,4,10.6
2020-06-11,5,11.1
2020-06-11,6,11.6
2020-06-11,7,12.5
2020-06-11,8,13.2
2020-06-11,9,13.3
2020-06-11,10,13.7
2020-06-11,11,15.1
2020-06-11,12,17.0
2020-06-11,13,17.9
2020-06-11,14,17.7
2020-06-11,15,17.9
2020-06-11,16,15.5
2020-06-11,17,14.1
2020-06-11,18,13.1
2020-06-11,19,12.1
2020-06-11,20,12.2
2020-06-11,21,12.2
2020-06-11,22,11.8
2020-06-12,23,11.8
2020-06-12,0,12.1
2020-06-12,1,12.5
2020-06-12,2,12.6
2020-06-12,3,12.7
2020-06-12,4,13.0
2020-06-12,5,13.7
2020-06-12,6,14.5
2020-06-12,7,15.4
2020-06-12,8,17.4
2020-06-12,9,17.4
2020-06-12,10,18.1
2020-06-12,11,18.9
2020-06-12,12,18.7
2020-06-12,13,20.2
2020-06-12,14,18.7
2020-06-12,15,17.9
2020-06-12,16,17.5
2020-06-12,17,17.6
2020-06-12,18,17.6
2020-06-12,19,17.1
2020-06-12,20,16.4
2020-06-12,21,15.7
2020-06-12,22,14.6
2020-06-13,23,13.7
2020-06-13,0,13.5
2020-06-13,1,12.7
2020-06-13,2,11.5
2020-06-13,3,11.2
2020-06-13,4,12.1
2020-06-13,5,13.5
2020-06-13,6,15.5
2020-06-13,7,17.2
2020-06-13,8,18.5
2020-06-13,9,20.1
2020-06-13,10,21.2
2020-06-13,11,22.3
2020-06-13,12,23.0
2020-06-13,13,24.1
2020-06-13,14,24.4
2020-06-13,15,23.3
2020-06-13,16,23.5
2020-06-13,17,23.8
2020-06-13,18,21.2
2020-06-13,19,16.8
2020-06-13,20,15.3
2020-06-13,21,15.5
2020-06-13,22,14.7
2020-06-14,23,14.6
2020-06-14,0,14.0
2020-06-14,1,13.0
2020-06-14,2,11.8
2020-06-14,3,10.9
2020-06-14,4,10.7
2020-06-14,5,12.5
2020-06-14,6,15.8
2020-06-14,7,18.3
2020-06-14,8,19.4
2020-06-14,9,20.0
2020-06-14,10,20.2
2020-06-14,11,21.4
2020-06-14,12,21.9
2020-06-14,13,21.9
2020-06-14,14,22.4
2020-06-14,15,22.4
2020-06-14,16,22.0
2020-06-14,17,21.4
2020-06-14,18,20.9
2020-06-14,19,20.4
2020-06-14,20,18.6
2020-06-14,21,16.6
2020-06-14,22,15.6
2020-06-15,23,14.2
2020-06-15,0,13.5
2020-06-15,1,12.7
2020-06-15,2,11.9
2020-06-15,3,10.5
2020-06-15,4,10.1
2020-06-15,5,13.0
2020-06-15,6,15.0
2020-06-15,7,17.6
2020-06-15,8,19.6
2020-06-15,9,20.8
2020-06-15,10,22.2
2020-06-15,11,22.6
2020-06-15,12,22.9
2020-06-15,13,23.0
2020-06-15,14,23.9
2020-06-15,15,23.2
2020-06-15,16,22.4
2020-06-15,17,22.7
2020-06-15,18,17.7
2020-06-15,19,15.5
2020-06-15,20,15.3
2020-06-15,21,14.7
2020-06-15,22,14.3
2020-06-16,23,14.0
2020-06-16,0,13.2
2020-06-16,1,13.1
2020-06-16,2,11.6
2020-06-16,3,11.7
2020-06-16,4,12.0
2020-06-16,5,15.1
2020-06-16,6,16.7
2020-06-16,7,18.6
2020-06-16,8,20.1
2020-06-16,9,20.8
2020-06-16,10,21.7
2020-06-16,11,20.9
2020-06-16,12,22.7
2020-06-16,13,23.4
2020-06-16,14,23.6
2020-06-16,15,23.7
2020-06-16,16,22.8
2020-06-16,17,20.3
2020-06-16,18,20.2
2020-06-16,19,18.6
2020-06-16,20,17.3
2020-06-16,21,16.6
2020-06-16,22,14.9
2020-06-17,23,14.0
2020-06-17,0,13.2
2020-06-17,1,12.1
2020-06-17,2,11.5
2020-06-17,3,10.6
2020-06-17,4,11.8
2020-06-17,5,13.0
2020-06-17,6,13.8
2020-06-17,7,14.9
2020-06-17,8,17.5
2020-06-17,9,19.9
2020-06-17,10,20.1
2020-06-17,11,20.1
2020-06-17,12,19.1
2020-06-17,13,17.7
2020-06-17,14,16.7
2020-06-17,15,19.2
2020-06-17,16,19.8
2020-06-17,17,19.8
2020-06-17,18,18.8
2020-06-17,19,17.4
2020-06-17,20,16.2
2020-06-17,21,15.1
2020-06-17,22,14.8
2020-06-18,23,14.7
2020-06-18,0,14.5
2020-06-18,1,14.8
2020-06-18,2,14.5
2020-06-18,3,13.4
2020-06-18,4,12.8
2020-06-18,5,12.8
2020-06-18,6,13.0
2020-06-18,7,13.0
2020-06-18,8,13.2
2020-06-18,9,13.3
2020-06-18,10,13.5
2020-06-18,11,14.2
2020-06-18,12,14.3
2020-06-18,13,14.6
2020-06-18,14,15.0
2020-06-18,15,14.9
2020-06-18,16,16.2
2020-06-18,17,17.0
2020-06-18,18,16.6
2020-06-18,19,15.9
2020-06-18,20,14.9
2020-06-18,21,13.5
2020-06-18,22,13.1
2020-06-19,23,13.0
2020-06-19,0,13.6
2020-06-19,1,13.7
2020-06-19,2,13.7
2020-06-19,3,13.8
2020-06-19,4,14.0
2020-06-19,5,14.0
2020-06-19,6,14.1
2020-06-19,7,13.8
2020-06-19,8,13.6
2020-06-19,9,13.8
2020-06-19,10,15.0
2020-06-19,11,16.0
2020-06-19,12,16.9
2020-06-19,13,17.6
2020-06-19,14,19.2
2020-06-19,15,19.0
2020-06-19,16,19.1
2020-06-19,17,17.5
2020-06-19,18,16.8
2020-06-19,19,16.5
2020-06-19,20,14.6
2020-06-19,21,14.2
2020-06-19,22,14.1
2020-06-20,23,13.1
2020-06-20,0,12.4
2020-06-20,1,11.8
2020-06-20,2,11.7
2020-06-20,3,11.2
2020-06-20,4,11.7
2020-06-20,5,12.7
2020-06-20,6,13.8
2020-06-20,7,16.3
2020-06-20,8,16.5
2020-06-20,9,17.4
2020-06-20,10,18.7
2020-06-20,11,20.1
2020-06-20,12,20.8
2020-06-20,13,21.6
2020-06-20,14,22.0
2020-06-20,15,20.4
2020-06-20,16,20.5
2020-06-20,17,19.7
2020-06-20,18,19.7
2020-06-20,19,18.3
2020-06-20,20,17.1
2020-06-20,21,15.8
2020-06-20,22,14.8
2020-06-21,23,14.6
2020-06-21,0,14.5
2020-06-21,1,14.4
2020-06-21,2,14.1
2020-06-21,3,14.0
2020-06-21,4,13.9
2020-06-21,5,13.5
2020-06-21,6,14.2
2020-06-21,7,15.3
2020-06-21,8,17.1
2020-06-21,9,17.8
2020-06-21,10,18.4
2020-06-21,11,18.6
2020-06-21,12,19.3
2020-06-21,13,20.2
2020-06-21,14,20.6
2020-06-21,15,20.2
2020-06-21,16,20.2
2020-06-21,17,19.1
2020-06-21,18,18.2
2020-06-21,19,17.2
2020-06-21,20,15.3
2020-06-21,21,13.2
2020-06-21,22,12.2
2020-06-22,23,11.0
2020-06-22,0,10.5
2020-06-22,1,9.7
2020-06-22,2,9.5
2020-06-22,3,9.0
2020-06-22,4,9.2
2020-06-22,5,11.3
2020-06-22,6,13.7
2020-06-22,7,15.6
2020-06-22,8,17.3
2020-06-22,9,18.5
2020-06-22,10,19.4
2020-06-22,11,20.7
2020-06-22,12,21.7
2020-06-22,13,22.6
2020-06-22,14,22.6
2020-06-22,15,22.6
2020-06-22,16,22.4
2020-06-22,17,21.8
2020-06-22,18,20.6
2020-06-22,19,19.5
2020-06-22,20,17.8
2020-06-22,21,16.3
2020-06-22,22,15.4
2020-06-23,23,14.2
2020-06-23,0,13.6
2020-06-23,1,13.4
2020-06-23,2,12.2
2020-06-23,3,12.0
2020-06-23,4,11.4
2020-06-23,5,14.0
2020-06-23,6,16.5
2020-06-23,7,18.7
2020-06-23,8,20.1
2020-06-23,9,21.7
2020-06-23,10,23.4
2020-06-23,11,24.5
2020-06-23,12,25.5
2020-06-23,13,26.4
2020-06-23,14,26.9
2020-06-23,15,27.1
2020-06-23,16,27.0
2020-06-23,17,26.8
2020-06-23,18,25.9
2020-06-23,19,25.0
2020-06-23,20,22.7
2020-06-23,21,20.4
2020-06-23,22,19.4
2020-06-24,23,15.0
2020-06-24,0,14.4
2020-06-24,1,13.9
2020-06-24,2,17.0
2020-06-24,3,16.8
2020-06-24,4,15.5
2020-06-24,5,17.0
2020-06-24,6,19.8
2020-06-24,7,22.8
2020-06-24,8,24.5
2020-06-24,9,27.0
2020-06-24,10,27.7
2020-06-24,11,28.4
2020-06-24,12,29.1
2020-06-24,13,29.5
2020-06-24,14,29.9
2020-06-24,15,30.1
2020-06-24,16,29.5
2020-06-24,17,29.0
2020-06-24,18,27.8
2020-06-24,19,26.4
2020-06-24,20,24.2
2020-06-24,21,22.4
2020-06-24,22,19.8
2020-06-25,23,17.7
2020-06-25,0,16.7
2020-06-25,1,16.0
2020-06-25,2,15.3
2020-06-25,3,14.7
2020-06-25,4,14.6
2020-06-25,5,17.5
2020-06-25,6,20.7
2020-06-25,7,23.9
2020-06-25,8,25.9
2020-06-25,9,27.3
2020-06-25,10,28.3
2020-06-25,11,29.2
2020-06-25,12,29.7
2020-06-25,13,30.3
2020-06-25,14,30.8
2020-06-25,15,30.5
2020-06-25,16,30.3
2020-06-25,17,29.6
2020-06-25,18,28.1
2020-06-25,19,26.6
2020-06-25,20,24.7
2020-06-25,21,22.4
2020-06-25,22,20.0
2020-06-26,23,17.4
2020-06-26,0,16.2
2020-06-26,1,16.1
2020-06-26,2,15.4
2020-06-26,3,16.0
2020-06-26,4,16.3
2020-06-26,5,16.2
2020-06-26,6,19.9
2020-06-26,7,22.8
2020-06-26,8,25.9
2020-06-26,9,27.4
2020-06-26,10,28.5
2020-06-26,11,28.6
2020-06-26,12,28.4
2020-06-26,13,29.2
2020-06-26,14,28.3
2020-06-26,15,27.8
2020-06-26,16,27.2
2020-06-26,17,26.4
2020-06-26,18,24.2
2020-06-26,19,22.5
2020-06-26,20,20.0
2020-06-26,21,15.8
2020-06-26,22,15.6
2020-06-27,23,15.3
2020-06-27,0,14.9
2020-06-27,1,14.3
2020-06-27,2,14.8
2020-06-27,3,15.4
2020-06-27,4,15.3
2020-06-27,5,14.8
2020-06-27,6,15.2
2020-06-27,7,16.0
2020-06-27,8,17.0
2020-06-27,9,17.0
2020-06-27,10,16.4
2020-06-27,11,17.3
2020-06-27,12,19.9
2020-06-27,13,20.1
2020-06-27,14,19.4
2020-06-27,15,17.1
2020-06-27,16,18.0
2020-06-27,17,16.2
2020-06-27,18,16.2
2020-06-27,19,15.6
2020-06-27,20,15.1
2020-06-27,21,13.8
2020-06-27,22,13.6
2020-06-28,23,13.6
2020-06-28,0,12.7
2020-06-28,1,11.7
2020-06-28,2,11.3
2020-06-28,3,11.4
2020-06-28,4,11.3
2020-06-28,5,12.4
2020-06-28,6,13.5
2020-06-28,7,14.8
2020-06-28,8,15.8
2020-06-28,9,16.7
2020-06-28,10,18.0
2020-06-28,11,19.0
2020-06-28,12,18.9
2020-06-28,13,18.3
2020-06-28,14,19.2
2020-06-28,15,19.1
2020-06-28,16,17.6
2020-06-28,17,17.5
2020-06-28,18,16.1
2020-06-28,19,15.6
2020-06-28,20,14.0
2020-06-28,21,13.8
2020-06-28,22,13.1
2020-06-29,23,13.1
2020-06-29,0,12.5
2020-06-29,1,12.1
2020-06-29,2,11.9
2020-06-29,3,12.0
2020-06-29,4,12.3
2020-06-29,5,12.4
2020-06-29,6,13.3
2020-06-29,7,15.0
2020-06-29,8,16.9
2020-06-29,9,16.5
2020-06-29,10,17.2
2020-06-29,11,17.3
2020-06-29,12,16.6
2020-06-29,13,18.1
2020-06-29,14,17.9
2020-06-29,15,16.3
2020-06-29,16,16.0
2020-06-29,17,15.6
2020-06-29,18,14.7
2020-06-29,19,14.2
2020-06-29,20,14.0
2020-06-29,21,13.5
2020-06-29,22,13.4
2020-06-30,23,13.0
2020-06-30,0,13.0
2020-06-30,1,12.5
2020-06-30,2,12.6
2020-06-30,3,12.5
2020-06-30,4,12.6
2020-06-30,5,12.8
2020-06-30,6,13.1
2020-06-30,7,13.2
2020-06-30,8,14.4
2020-06-30,9,14.8
2020-06-30,10,16.0
2020-06-30,11,16.3
2020-06-30,12,16.7
2020-06-30,13,19.1
2020-06-30,14,19.4
2020-06-30,15,17.8
2020-06-30,16,18.0
2020-06-30,17,18.2
2020-06-30,18,18.4
2020-06-30,19,18.3
2020-06-30,20,17.8
2020-06-30,21,16.6
2020-06-30,22,16.2
2020-07-01,23,16.0
2020-07-01,0,15.4
2020-07-01,1,15.0
2020-07-01,2,14.3
2020-07-01,3,13.7
2020-07-01,4,13.1
2020-07-01,5,13.7
2020-07-01,6,14.7
2020-07-01,7,15.7
2020-07-01,8,15.8
2020-07-01,9,16.8
2020-07-01,10,17.8
2020-07-01,11,18.4
2020-07-01,12,19.2
2020-07-01,13,19.6
2020-07-01,14,20.4
2020-07-01,15,21.5
2020-07-01,16,21.2
2020-07-01,17,20.7
2020-07-01,18,19.0
2020-07-01,19,17.4
2020-07-01,20,16.2
2020-07-01,21,14.8
2020-07-01,22,14.3
2020-07-02,23,13.4
2020-07-02,0,11.7
2020-07-02,1,10.7
2020-07-02,2,10.6
2020-07-02,3,10.0
2020-07-02,4,10.8
2020-07-02,5,12.7
2020-07-02,6,14.4
2020-07-02,7,14.2
2020-07-02,8,15.8
2020-07-02,9,16.3
2020-07-02,10,18.1
2020-07-02,11,19.2
2020-07-02,12,21.1
2020-07-02,13,20.9
2020-07-02,14,19.7
2020-07-02,15,18.9
2020-07-02,16,19.6
2020-07-02,17,17.7
2020-07-02,18,16.8
2020-07-02,19,16.7
2020-07-02,20,16.3
2020-07-02,21,15.5
2020-07-02,22,14.4
2020-07-03,23,12.1
2020-07-03,0,11.3
2020-07-03,1,11.0
2020-07-03,2,11.0
2020-07-03,3,10.6
2020-07-03,4,10.9
2020-07-03,5,12.2
2020-07-03,6,15.3
2020-07-03,7,15.4
2020-07-03,8,16.3
2020-07-03,9,16.7
2020-07-03,10,16.7
2020-07-03,11,17.7
2020-07-03,12,19.1
2020-07-03,13,18.6
2020-07-03,14,18.5
2020-07-03,15,18.2
2020-07-03,16,18.0
2020-07-03,17,16.0
2020-07-03,18,14.5
2020-07-03,19,14.0
2020-07-03,20,14.2
2020-07-03,21,14.5
2020-07-03,22,14.7
2020-07-04,23,14.8
2020-07-04,0,14.8
2020-07-04,1,14.8
2020-07-04,2,15.0
2020-07-04,3,15.1
2020-07-04,4,15.6
2020-07-04,5,16.1
2020-07-04,6,16.4
2020-07-04,7,16.8
2020-07-04,8,18.4
2020-07-04,9,18.3
2020-07-04,10,18.2
2020-07-04,11,18.8
2020-07-04,12,19.2
2020-07-04,13,19.9
2020-07-04,14,19.2
2020-07-04,15,19.0
2020-07-04,16,18.8
2020-07-04,17,18.7
2020-07-04,18,18.2
2020-07-04,19,17.4
2020-07-04,20,17.1
2020-07-04,21,17.3
2020-07-04,22,17.4
2020-07-05,23,17.2
2020-07-05,0,17.0
2020-07-05,1,16.9
2020-07-05,2,16.9
2020-07-05,3,16.8
2020-07-05,4,15.9
2020-07-05,5,16.3
2020-07-05,6,17.5
2020-07-05,7,17.6
2020-07-05,8,18.6
2020-07-05,9,19.0
2020-07-05,10,18.3
2020-07-05,11,19.6
2020-07-05,12,19.5
2020-07-05,13,20.2
2020-07-05,14,19.3
2020-07-05,15,18.6
2020-07-05,16,17.9
2020-07-05,17,17.3
2020-07-05,18,16.4
2020-07-05,19,15.3
2020-07-05,20,14.4
2020-07-05,21,13.2
2020-07-05,22,12.6
2020-07-06,23,12.2
2020-07-06,0,12.0
2020-07-06,1,11.0
2020-07-06,2,10.9
2020-07-06,3,11.0
2020-07-06,4,11.3
2020-07-06,5,12.3
2020-07-06,6,14.5
2020-07-06,7,15.2
2020-07-06,8,15.5
2020-07-06,9,15.3
2020-07-06,10,15.9
2020-07-06,11,16.1
2020-07-06,12,17.7
2020-07-06,13,17.4
2020-07-06,14,18.0
2020-07-06,15,18.4
2020-07-06,16,19.0
2020-07-06,17,18.7
2020-07-06,18,17.5
2020-07-06,19,16.7
2020-07-06,20,14.3
2020-07-06,21,11.7
2020-07-06,22,10.6
2020-07-07,23,10.5
2020-07-07,0,9.3
2020-07-07,1,9.7
2020-07-07,2,9.9
2020-07-07,3,9.9
2020-07-07,4,10.5
2020-07-07,5,11.7
2020-07-07,6,13.7
2020-07-07,7,16.2
2020-07-07,8,17.4
2020-07-07,9,18.5
2020-07-07,10,18.6
2020-07-07,11,18.8
2020-07-07,12,17.9
2020-07-07,13,17.5
2020-07-07,14,17.4
2020-07-07,15,16.8
2020-07-07,16,16.1
2020-07-07,17,12.5
2020-07-07,18,12.5
2020-07-07,19,12.5
2020-07-07,20,12.6
2020-07-07,21,12.6
2020-07-07,22,12.9
2020-07-08,23,13.4
2020-07-08,0,13.7
2020-07-08,1,13.9
2020-07-08,2,14.0
2020-07-08,3,14.2
2020-07-08,4,14.4
2020-07-08,5,14.6
2020-07-08,6,15.1
2020-07-08,7,15.6
2020-07-08,8,15.9
2020-07-08,9,16.6
2020-07-08,10,17.7
2020-07-08,11,19.1
2020-07-08,12,19.7
2020-07-08,13,19.9
2020-07-08,14,20.1
2020-07-08,15,19.1
2020-07-08,16,17.8
2020-07-08,17,17.0
2020-07-08,18,16.3
2020-07-08,19,16.0
2020-07-08,20,15.6
2020-07-08,21,15.4
2020-07-08,22,15.5
2020-07-09,23,15.5
2020-07-09,0,15.5
2020-07-09,1,15.5
2020-07-09,2,15.5
2020-07-09,3,15.4
2020-07-09,4,15.2
2020-07-09,5,15.2
2020-07-09,6,15.4
2020-07-09,7,15.9
2020-07-09,8,16.9
2020-07-09,9,17.3
2020-07-09,10,18.1
2020-07-09,11,18.7
2020-07-09,12,18.6
2020-07-09,13,19.3
2020-07-09,14,19.3
2020-07-09,15,19.6
2020-07-09,16,16.1
2020-07-09,17,14.9
2020-07-09,18,14.6
2020-07-09,19,14.1
2020-07-09,20,13.7
2020-07-09,21,13.2
2020-07-09,22,12.6
2020-07-10,23,12.3
2020-07-10,0,11.9
2020-07-10,1,11.8
2020-07-10,2,11.7
2020-07-10,3,11.7
2020-07-10,4,11.9
2020-07-10,5,12.1
2020-07-10,6,12.6
2020-07-10,7,13.0
2020-07-10,8,14.2
2020-07-10,9,14.7
2020-07-10,10,15.3
2020-07-10,11,16.1
2020-07-10,12,16.2
2020-07-10,13,17.3
2020-07-10,14,17.6
2020-07-10,15,16.9
2020-07-10,16,17.1
2020-07-10,17,17.4
2020-07-10,18,16.6
2020-07-10,19,15.8
2020-07-10,20,13.8
2020-07-10,21,10.9
2020-07-10,22,10.1
2020-07-11,23,9.5
2020-07-11,0,8.6
2020-07-11,1,8.1
2020-07-11,2,8.2
2020-07-11,3,8.1
2020-07-11,4,8.0
2020-07-11,5,10.6
2020-07-11,6,13.2
2020-07-11,7,14.3
2020-07-11,8,15.4
2020-07-11,9,16.4
2020-07-11,10,17.1
2020-07-11,11,17.9
2020-07-11,12,17.8
2020-07-11,13,18.5
2020-07-11,14,18.6
2020-07-11,15,19.2
2020-07-11,16,18.2
2020-07-11,17,18.7
2020-07-11,18,18.1
2020-07-11,19,18.5
2020-07-11,20,14.3
2020-07-11,21,10.7
2020-07-11,22,10.2
2020-07-12,23,10.0
2020-07-12,0,8.6
2020-07-12,1,7.3
2020-07-12,2,6.6
2020-07-12,3,6.3
2020-07-12,4,6.4
2020-07-12,5,9.2
2020-07-12,6,13.7
2020-07-12,7,15.1
2020-07-12,8,17.1
2020-07-12,9,18.6
2020-07-12,10,20.2
2020-07-12,11,20.6
2020-07-12,12,21.6
2020-07-12,13,21.8
2020-07-12,14,22.8
2020-07-12,15,22.6
2020-07-12,16,22.2
2020-07-12,17,22.6
2020-07-12,18,21.1
2020-07-12,19,19.7
2020-07-12,20,17.5
2020-07-12,21,14.4
2020-07-12,22,13.3
2020-07-13,23,12.3
2020-07-13,0,12.4
2020-07-13,1,12.7
2020-07-13,2,13.0
2020-07-13,3,12.6
2020-07-13,4,12.6
2020-07-13,5,13.9
2020-07-13,6,15.5
2020-07-13,7,17.9
2020-07-13,8,19.7
2020-07-13,9,21.9
2020-07-13,10,20.3
2020-07-13,11,20.4
2020-07-13,12,22.2
2020-07-13,13,22.3
2020-07-13,14,21.9
2020-07-13,15,21.1
2020-07-13,16,18.0
2020-07-13,17,18.2
2020-07-13,18,17.5
2020-07-13,19,16.8
2020-07-13,20,15.8
2020-07-13,21,15.5
2020-07-13,22,15.3
2020-07-14,23,15.1
2020-07-14,0,15.1
2020-07-14,1,15.1
2020-07-14,2,14.9
2020-07-14,3,14.2
2020-07-14,4,13.9
2020-07-14,5,14.0
2020-07-14,6,14.7
2020-07-14,7,15.8
2020-07-14,8,16.5
2020-07-14,9,16.7
2020-07-14,10,17.2
2020-07-14,11,17.6
2020-07-14,12,18.0
2020-07-14,13,18.5
2020-07-14,14,18.6
2020-07-14,15,18.3
2020-07-14,16,17.7
2020-07-14,17,17.6
2020-07-14,18,16.8
2020-07-14,19,16.6
2020-07-14,20,14.5
2020-07-14,21,13.1
2020-07-14,22,12.3
2020-07-15,23,11.9
2020-07-15,0,12.0
2020-07-15,1,11.8
2020-07-15,2,11.7
2020-07-15,3,11.4
2020-07-15,4,11.6
2020-07-15,5,12.9
2020-07-15,6,13.9
2020-07-15,7,14.3
2020-07-15,8,14.7
2020-07-15,9,15.1
2020-07-15,10,15.9
2020-07-15,11,17.8
2020-07-15,12,17.7
2020-07-15,13,17.8
2020-07-15,14,17.3
2020-07-15,15,17.2
2020-07-15,16,16.5
2020-07-15,17,16.5
2020-07-15,18,16.7
2020-07-15,19,16.3
2020-07-15,20,15.4
2020-07-15,21,15.2
2020-07-15,22,15.2
2020-07-16,23,14.8
2020-07-16,0,14.4
2020-07-16,1,14.5
2020-07-16,2,14.6
2020-07-16,3,14.6
2020-07-16,4,14.7
2020-07-16,5,15.3
2020-07-16,6,15.8
2020-07-16,7,16.6
2020-07-16,8,16.6
2020-07-16,9,17.1
2020-07-16,10,17.7
2020-07-16,11,18.4
2020-07-16,12,19.2
2020-07-16,13,21.5
2020-07-16,14,22.0
2020-07-16,15,22.4
2020-07-16,16,22.1
2020-07-16,17,22.3
2020-07-16,18,21.7
2020-07-16,19,20.7
2020-07-16,20,20.0
2020-07-16,21,18.9
2020-07-16,22,18.5
2020-07-17,23,18.4
2020-07-17,0,17.8
2020-07-17,1,17.2
2020-07-17,2,16.6
2020-07-17,3,15.9
2020-07-17,4,14.9
2020-07-17,5,15.1
2020-07-17,6,16.6
2020-07-17,7,17.8
2020-07-17,8,20.7
2020-07-17,9,22.7
2020-07-17,10,23.1
2020-07-17,11,24.2
2020-07-17,12,24.4
2020-07-17,13,25.0
2020-07-17,14,25.5
2020-07-17,15,25.0
2020-07-17,16,23.9
2020-07-17,17,23.5
2020-07-17,18,22.3
2020-07-17,19,20.7
2020-07-17,20,18.6
2020-07-17,21,16.6
2020-07-17,22,15.9
2020-07-18,23,15.3
2020-07-18,0,14.5
2020-07-18,1,14.5
2020-07-18,2,14.9
2020-07-18,3,15.1
2020-07-18,4,15.3
2020-07-18,5,15.3
2020-07-18,6,17.0
2020-07-18,7,17.9
2020-07-18,8,19.5
2020-07-18,9,20.3
2020-07-18,10,20.1
2020-07-18,11,19.4
2020-07-18,12,20.7
2020-07-18,13,21.1
2020-07-18,14,20.7
2020-07-18,15,20.7
2020-07-18,16,21.2
2020-07-18,17,21.5
2020-07-18,18,19.5
2020-07-18,19,18.5
2020-07-18,20,17.8
2020-07-18,21,17.2
2020-07-18,22,16.9
2020-07-19,23,15.9
2020-07-19,0,15.3
2020-07-19,1,15.0
2020-07-19,2,14.8
2020-07-19,3,12.9
2020-07-19,4,12.7
2020-07-19,5,12.5
2020-07-19,6,13.2
2020-07-19,7,14.4
2020-07-19,8,15.2
2020-07-19,9,16.8
2020-07-19,10,18.0
2020-07-19,11,18.5
2020-07-19,12,19.1
2020-07-19,13,19.0
2020-07-19,14,20.2
2020-07-19,15,20.5
2020-07-19,16,19.1
2020-07-19,17,19.4
2020-07-19,18,19.4
2020-07-19,19,17.9
2020-07-19,20,15.6
2020-07-19,21,13.5
2020-07-19,22,12.4
2020-07-20,23,11.9
2020-07-20,0,10.9
2020-07-20,1,10.1
2020-07-20,2,9.3
2020-07-20,3,9.0
2020-07-20,4,9.0
2020-07-20,5,11.3
2020-07-20,6,13.3
2020-07-20,7,14.6
2020-07-20,8,16.0
2020-07-20,9,16.7
2020-07-20,10,17.6
2020-07-20,11,17.9
2020-07-20,12,18.2
2020-07-20,13,18.6
2020-07-20,14,19.2
2020-07-20,15,18.9
2020-07-20,16,18.7
2020-07-20,17,18.8
2020-07-20,18,18.1
2020-07-20,19,16.0
2020-07-20,20,13.8
2020-07-20,21,11.5
2020-07-20,22,9.7
2020-07-21,23,8.0
2020-07-21,0,9.2
2020-07-21,1,8.8
2020-07-21,2,8.2
2020-07-21,3,7.5
2020-07-21,4,6.2
2020-07-21,5,9.6
2020-07-21,6,13.1
2020-07-21,7,15.6
2020-07-21,8,16.2
2020-07-21,9,16.9
2020-07-21,10,18.4
2020-07-21,11,18.2
2020-07-21,12,20.0
2020-07-21,13,20.0
2020-07-21,14,19.9
2020-07-21,15,19.9
2020-07-21,16,19.4
2020-07-21,17,19.3
2020-07-21,18,18.5
2020-07-21,19,17.5
2020-07-21,20,16.7
2020-07-21,21,15.6
2020-07-21,22,14.8
2020-07-22,23,14.6
2020-07-22,0,13.9
2020-07-22,1,13.5
2020-07-22,2,12.3
2020-07-22,3,10.8
2020-07-22,4,10.6
2020-07-22,5,13.2
2020-07-22,6,14.2
2020-07-22,7,15.1
2020-07-22,8,17.8
2020-07-22,9,20.6
2020-07-22,10,22.5
2020-07-22,11,23.3
2020-07-22,12,23.6
2020-07-22,13,24.3
2020-07-22,14,24.3
2020-07-22,15,24.1
2020-07-22,16,22.8
2020-07-22,17,22.2
2020-07-22,18,21.4
2020-07-22,19,20.3
2020-07-22,20,18.8
2020-07-22,21,18.4
2020-07-22,22,16.5
2020-07-23,23,15.7
2020-07-23,0,14.6
2020-07-23,1,13.4
2020-07-23,2,12.7
2020-07-23,3,12.1
2020-07-23,4,12.3
2020-07-23,5,13.2
2020-07-23,6,14.3
2020-07-23,7,16.7
2020-07-23,8,17.8
2020-07-23,9,20.3
2020-07-23,10,21.0
2020-07-23,11,21.5
2020-07-23,12,22.5
2020-07-23,13,22.9
2020-07-23,14,22.6
2020-07-23,15,20.2
2020-07-23,16,19.7
2020-07-23,17,19.5
2020-07-23,18,19.7
2020-07-23,19,18.5
2020-07-23,20,17.7
2020-07-23,21,16.6
2020-07-23,22,16.1
2020-07-24,23,16.0
2020-07-24,0,15.1
2020-07-24,1,14.6
2020-07-24,2,14.6
2020-07-24,3,14.6
2020-07-24,4,14.7
2020-07-24,5,15.2
2020-07-24,6,16.7
2020-07-24,7,18.5
2020-07-24,8,19.3
2020-07-24,9,20.9
2020-07-24,10,21.6
2020-07-24,11,22.4
2020-07-24,12,23.1
2020-07-24,13,23.7
2020-07-24,14,23.9
2020-07-24,15,23.8
2020-07-24,16,23.4
2020-07-24,17,22.8
2020-07-24,18,22.0
2020-07-24,19,20.8
2020-07-24,20,20.0
2020-07-24,21,18.7
2020-07-24,22,18.4
2020-07-25,23,18.1
2020-07-25,0,17.3
2020-07-25,1,17.2
2020-07-25,2,17.1
2020-07-25,3,16.7
2020-07-25,4,16.7
2020-07-25,5,17.0
2020-07-25,6,17.3
2020-07-25,7,18.4
2020-07-25,8,21.3
2020-07-25,9,20.8
2020-07-25,10,21.7
2020-07-25,11,22.7
2020-07-25,12,22.6
2020-07-25,13,21.5
2020-07-25,14,16.8
2020-07-25,15,17.2
2020-07-25,16,17.7
2020-07-25,17,17.1
2020-07-25,18,17.0
2020-07-25,19,16.0
2020-07-25,20,14.9
2020-07-25,21,14.3
2020-07-25,22,13.8
2020-07-26,23,13.2
2020-07-26,0,12.4
2020-07-26,1,11.8
2020-07-26,2,11.7
2020-07-26,3,11.6
2020-07-26,4,12.0
2020-07-26,5,13.0
2020-07-26,6,14.2
2020-07-26,7,16.0
2020-07-26,8,17.2
2020-07-26,9,18.9
2020-07-26,10,19.9
2020-07-26,11,21.1
2020-07-26,12,21.0
2020-07-26,13,21.0
2020-07-26,14,21.0
2020-07-26,15,20.1
2020-07-26,16,19.6
2020-07-26,17,19.3
2020-07-26,18,18.4
2020-07-26,19,17.6
2020-07-26,20,17.3
2020-07-26,21,16.6
2020-07-26,22,16.1
2020-07-27,23,15.8
2020-07-27,0,15.3
2020-07-27,1,14.9
2020-07-27,2,14.9
2020-07-27,3,14.8
2020-07-27,4,15.1
2020-07-27,5,15.1
2020-07-27,6,15.1
2020-07-27,7,14.6
2020-07-27,8,15.7
2020-07-27,9,17.1
2020-07-27,10,16.9
2020-07-27,11,16.9
2020-07-27,12,16.9
2020-07-27,13,18.2
2020-07-27,14,19.4
2020-07-27,15,21.5
2020-07-27,16,21.7
2020-07-27,17,20.9
2020-07-27,18,20.1
2020-07-27,19,18.8
2020-07-27,20,16.4
2020-07-27,21,14.7
2020-07-27,22,13.1
2020-07-28,23,11.9
2020-07-28,0,11.4
2020-07-28,1,11.4
2020-07-28,2,11.6
2020-07-28,3,11.5
2020-07-28,4,10.8
2020-07-28,5,12.5
2020-07-28,6,14.5
2020-07-28,7,14.9
2020-07-28,8,15.5
2020-07-28,9,16.1
2020-07-28,10,17.0
2020-07-28,11,17.9
2020-07-28,12,18.5
2020-07-28,13,19.3
2020-07-28,14,19.5
2020-07-28,15,18.9
2020-07-28,16,18.8
2020-07-28,17,18.0
2020-07-28,18,17.1
2020-07-28,19,16.3
2020-07-28,20,13.5
2020-07-28,21,11.7
2020-07-28,22,10.8
2020-07-29,23,10.0
2020-07-29,0,9.6
2020-07-29,1,8.8
2020-07-29,2,8.2
2020-07-29,3,9.3
2020-07-29,4,9.9
2020-07-29,5,10.9
2020-07-29,6,12.0
2020-07-29,7,14.0
2020-07-29,8,16.9
2020-07-29,9,17.2
2020-07-29,10,18.4
2020-07-29,11,20.1
2020-07-29,12,21.2
2020-07-29,13,20.2
2020-07-29,14,21.3
2020-07-29,15,21.1
2020-07-29,16,20.7
2020-07-29,17,20.5
2020-07-29,18,19.3
2020-07-29,19,18.2
2020-07-29,20,16.6
2020-07-29,21,16.0
2020-07-29,22,16.2
2020-07-30,23,15.0
2020-07-30,0,14.6
2020-07-30,1,13.5
2020-07-30,2,12.2
2020-07-30,3,11.2
2020-07-30,4,10.7
2020-07-30,5,13.6
2020-07-30,6,16.4
2020-07-30,7,19.3
2020-07-30,8,20.6
2020-07-30,9,21.8
2020-07-30,10,23.6
2020-07-30,11,24.4
2020-07-30,12,24.8
2020-07-30,13,26.5
2020-07-30,14,27.0
2020-07-30,15,27.3
2020-07-30,16,26.9
2020-07-30,17,27.3
2020-07-30,18,26.4
2020-07-30,19,25.1
2020-07-30,20,23.0
2020-07-30,21,21.2
2020-07-30,22,14.2
2020-07-31,23,13.2
2020-07-31,0,13.9
2020-07-31,1,18.2
2020-07-31,2,17.8
2020-07-31,3,17.2
2020-07-31,4,15.2
2020-07-31,5,17.8
2020-07-31,6,22.4
2020-07-31,7,25.3
2020-07-31,8,27.7
2020-07-31,9,29.8
2020-07-31,10,31.2
2020-07-31,11,32.9
2020-07-31,12,34.2
2020-07-31,13,35.2
2020-07-31,14,35.6
2020-07-31,15,34.6
2020-07-31,16,30.3
2020-07-31,17,28.6
2020-07-31,18,27.0
2020-07-31,19,23.5
2020-07-31,20,22.3
2020-07-31,21,20.6
2020-07-31,22,20.0
2020-08-01,23,19.4
2020-08-01,0,19.1
2020-08-01,1,17.7
2020-08-01,2,15.9
2020-08-01,3,14.4
2020-08-01,4,13.7
2020-08-01,5,14.8
2020-08-01,6,17.2
2020-08-01,7,19.5
2020-08-01,8,20.2
2020-08-01,9,21.4
2020-08-01,10,22.8
2020-08-01,11,23.3
2020-08-01,12,24.0
2020-08-01,13,24.1
2020-08-01,14,24.2
2020-08-01,15,23.9
2020-08-01,16,23.3
2020-08-01,17,22.0
2020-08-01,18,20.1
2020-08-01,19,19.3
2020-08-01,20,17.7
2020-08-01,21,15.8
2020-08-01,22,14.2
2020-08-02,23,12.5
2020-08-02,0,11.6
2020-08-02,1,10.7
2020-08-02,2,10.4
2020-08-02,3,9.7
2020-08-02,4,9.4
2020-08-02,5,10.8
2020-08-02,6,13.4
2020-08-02,7,16.2
2020-08-02,8,17.9
2020-08-02,9,19.1
2020-08-02,10,20.8
2020-08-02,11,21.1
2020-08-02,12,21.6
2020-08-02,13,22.7
2020-08-02,14,22.5
2020-08-02,15,22.2
2020-08-02,16,21.5
2020-08-02,17,21.4
2020-08-02,18,19.9
2020-08-02,19,18.7
2020-08-02,20,17.7
2020-08-02,21,15.5
2020-08-02,22,14.4
2020-08-03,23,13.5
2020-08-03,0,12.6
2020-08-03,1,13.8
2020-08-03,2,13.7
2020-08-03,3,12.8
2020-08-03,4,11.1
2020-08-03,5,12.2
2020-08-03,6,14.0
2020-08-03,7,15.3
2020-08-03,8,16.5
2020-08-03,9,17.6
2020-08-03,10,18.4
2020-08-03,11,19.5
2020-08-03,12,19.4
2020-08-03,13,20.5
2020-08-03,14,20.4
2020-08-03,15,19.6
2020-08-03,16,19.1
2020-08-03,17,18.7
2020-08-03,18,18.1
2020-08-03,19,17.0
2020-08-03,20,13.1
2020-08-03,21,11.4
2020-08-03,22,9.5
2020-08-04,23,8.2
2020-08-04,0,7.5
2020-08-04,1,6.8
2020-08-04,2,6.0
2020-08-04,3,5.4
2020-08-04,4,5.8
2020-08-04,5,8.5
2020-08-04,6,13.3
2020-08-04,7,15.0
2020-08-04,8,17.5
2020-08-04,9,18.8
2020-08-04,10,17.8
2020-08-04,11,18.9
2020-08-04,12,19.9
2020-08-04,13,19.9
2020-08-04,14,20.5
2020-08-04,15,20.8
2020-08-04,16,21.3
2020-08-04,17,20.8
2020-08-04,18,20.1
2020-08-04,19,19.1
2020-08-04,20,18.3
2020-08-04,21,17.7
2020-08-04,22,16.9
2020-08-05,23,16.2
2020-08-05,0,16.0
2020-08-05,1,16.0
2020-08-05,2,16.2
2020-08-05,3,16.4
2020-08-05,4,16.5
2020-08-05,5,16.7
2020-08-05,6,17.6
2020-08-05,7,17.6
2020-08-05,8,17.9
2020-08-05,9,19.4
2020-08-05,10,22.5
2020-08-05,11,23.6
2020-08-05,12,24.7
2020-08-05,13,24.4
2020-08-05,14,23.7
2020-08-05,15,22.9
2020-08-05,16,21.8
2020-08-05,17,21.3
2020-08-05,18,20.5
2020-08-05,19,19.5
2020-08-05,20,18.9
2020-08-05,21,18.0
2020-08-05,22,17.5
2020-08-06,23,17.6
2020-08-06,0,17.7
2020-08-06,1,17.7
2020-08-06,2,17.7
2020-08-06,3,17.7
2020-08-06,4,17.5
2020-08-06,5,17.9
2020-08-06,6,18.9
2020-08-06,7,19.9
2020-08-06,8,20.0
2020-08-06,9,19.6
2020-08-06,10,21.5
2020-08-06,11,21.6
2020-08-06,12,23.3
2020-08-06,13,24.7
2020-08-06,14,25.0
2020-08-06,15,26.0
2020-08-06,16,25.6
2020-08-06,17,25.0
2020-08-06,18,24.6
2020-08-06,19,23.5
2020-08-06,20,20.6
2020-08-06,21,19.0
2020-08-06,22,17.0
2020-08-07,23,15.2
2020-08-07,0,14.6
2020-08-07,1,13.5
2020-08-07,2,13.0
2020-08-07,3,12.2
2020-08-07,4,11.4
2020-08-07,5,13.3
2020-08-07,6,17.0
2020-08-07,7,21.5
2020-08-07,8,24.1
2020-08-07,9,26.9
2020-08-07,10,28.1
2020-08-07,11,30.2
2020-08-07,12,31.5
2020-08-07,13,32.3
2020-08-07,14,31.9
2020-08-07,15,30.7
2020-08-07,16,28.4
2020-08-07,17,28.4
2020-08-07,18,26.7
2020-08-07,19,24.5
2020-08-07,20,21.8
2020-08-07,21,20.4
2020-08-07,22,20.0
2020-08-08,23,19.4
2020-08-08,0,18.7
2020-08-08,1,17.6
2020-08-08,2,17.4
2020-08-08,3,16.9
2020-08-08,4,17.7
2020-08-08,5,19.0
2020-08-08,6,20.2
2020-08-08,7,21.7
2020-08-08,8,23.0
2020-08-08,9,24.7
2020-08-08,10,26.3
2020-08-08,11,26.5
2020-08-08,12,26.8
2020-08-08,13,27.9
2020-08-08,14,27.8
2020-08-08,15,26.6
2020-08-08,16,25.4
2020-08-08,17,24.4
2020-08-08,18,23.1
2020-08-08,19,21.0
2020-08-08,20,19.3
2020-08-08,21,17.6
2020-08-08,22,16.8
2020-08-09,23,16.4
2020-08-09,0,16.0
2020-08-09,1,15.5
2020-08-09,2,15.4
2020-08-09,3,15.0
2020-08-09,4,14.9
2020-08-09,5,15.1
2020-08-09,6,15.4
2020-08-09,7,16.0
2020-08-09,8,17.3
2020-08-09,9,19.0
2020-08-09,10,21.5
2020-08-09,11,23.7
2020-08-09,12,25.5
2020-08-09,13,27.6
2020-08-09,14,28.8
2020-08-09,15,28.6
2020-08-09,16,27.4
2020-08-09,17,26.1
2020-08-09,18,24.7
2020-08-09,19,23.6
2020-08-09,20,21.4
2020-08-09,21,19.6
2020-08-09,22,18.8
2020-08-10,23,17.9
2020-08-10,0,17.1
2020-08-10,1,17.1
2020-08-10,2,17.1
2020-08-10,3,16.9
2020-08-10,4,16.6
2020-08-10,5,16.6
2020-08-10,6,17.5
2020-08-10,7,18.7
2020-08-10,8,21.0
2020-08-10,9,22.8
2020-08-10,10,25.8
2020-08-10,11,27.3
2020-08-10,12,29.2
2020-08-10,13,30.6
2020-08-10,14,32.1
2020-08-10,15,32.5
2020-08-10,16,32.2
2020-08-10,17,31.9
2020-08-10,18,28.7
2020-08-10,19,28.0
2020-08-10,20,25.7
2020-08-10,21,25.2
2020-08-10,22,24.4
2020-08-11,23,22.3
2020-08-11,0,19.2
2020-08-11,1,18.2
2020-08-11,2,17.6
2020-08-11,3,16.7
2020-08-11,4,16.8
2020-08-11,5,17.4
2020-08-11,6,19.4
2020-08-11,7,21.8
2020-08-11,8,24.7
2020-08-11,9,27.2
2020-08-11,10,29.9
2020-08-11,11,31.1
2020-08-11,12,31.9
2020-08-11,13,33.4
2020-08-11,14,32.9
2020-08-11,15,33.1
2020-08-11,16,32.1
2020-08-11,17,31.6
2020-08-11,18,30.8
2020-08-11,19,28.8
2020-08-11,20,27.9
2020-08-11,21,26.2
2020-08-11,22,26.1
2020-08-12,23,25.5
2020-08-12,0,21.9
2020-08-12,1,20.3
2020-08-12,2,19.7
2020-08-12,3,19.7
2020-08-12,4,19.8
2020-08-12,5,19.8
2020-08-12,6,21.9
2020-08-12,7,24.6
2020-08-12,8,27.8
2020-08-12,9,30.0
2020-08-12,10,31.3
2020-08-12,11,32.3
2020-08-12,12,33.4
2020-08-12,13,34.2
2020-08-12,14,34.6
2020-08-12,15,33.2
2020-08-12,16,23.3
2020-08-12,17,20.7
2020-08-12,18,21.0
2020-08-12,19,21.1
2020-08-12,20,20.1
2020-08-12,21,20.1
2020-08-12,22,20.0
2020-08-13,23,19.5
2020-08-13,0,19.1
2020-08-13,1,18.9
2020-08-13,2,18.4
2020-08-13,3,17.9
2020-08-13,4,17.7
2020-08-13,5,17.3
2020-08-13,6,17.3
2020-08-13,7,17.6
2020-08-13,8,17.9
2020-08-13,9,17.6
2020-08-13,10,17.7
2020-08-13,11,20.2
2020-08-13,12,20.6
2020-08-13,13,20.8
2020-08-13,14,20.9
2020-08-13,15,20.7
2020-08-13,16,19.7
2020-08-13,17,19.4
2020-08-13,18,19.6
2020-08-13,19,18.8
2020-08-13,20,18.2
2020-08-13,21,18.2
2020-08-13,22,17.4
2020-08-14,23,16.9
2020-08-14,0,16.4
2020-08-14,1,16.3
2020-08-14,2,16.3
2020-08-14,3,16.1
2020-08-14,4,16.0
2020-08-14,5,15.9
2020-08-14,6,15.8
2020-08-14,7,15.8
2020-08-14,8,16.0
2020-08-14,9,16.1
2020-08-14,10,16.6
2020-08-14,11,17.1
2020-08-14,12,17.9
2020-08-14,13,18.2
2020-08-14,14,18.6
2020-08-14,15,19.3
2020-08-14,16,19.8
2020-08-14,17,19.9
2020-08-14,18,19.9
2020-08-14,19,19.6
2020-08-14,20,18.9
2020-08-14,21,18.0
2020-08-14,22,17.2
2020-08-15,23,16.7
2020-08-15,0,16.1
2020-08-15,1,15.7
2020-08-15,2,15.4
2020-08-15,3,15.0
2020-08-15,4,14.7
2020-08-15,5,14.6
2020-08-15,6,14.7
2020-08-15,7,14.9
2020-08-15,8,15.6
2020-08-15,9,16.5
2020-08-15,10,16.9
2020-08-15,11,17.1
2020-08-15,12,17.0
2020-08-15,13,17.2
2020-08-15,14,17.4
2020-08-15,15,17.6
2020-08-15,16,17.7
2020-08-15,17,17.5
2020-08-15,18,17.6
2020-08-15,19,17.3
2020-08-15,20,17.1
2020-08-15,21,16.8
2020-08-15,22,16.7
2020-08-16,23,16.6
2020-08-16,0,16.6
2020-08-16,1,16.6
2020-08-16,2,16.6
2020-08-16,3,16.7
2020-08-16,4,16.9
2020-08-16,5,17.2
2020-08-16,6,17.8
2020-08-16,7,18.3
2020-08-16,8,19.1
2020-08-16,9,19.9
2020-08-16,10,21.7
2020-08-16,11,23.0
2020-08-16,12,23.7
2020-08-16,13,23.8
2020-08-16,14,23.5
2020-08-16,15,21.8
2020-08-16,16,20.6
2020-08-16,17,20.0
2020-08-16,18,19.7
2020-08-16,19,18.8
2020-08-16,20,18.5
2020-08-16,21,15.9
2020-08-16,22,15.7
2020-08-17,23,15.6
2020-08-17,0,15.3
2020-08-17,1,14.8
2020-08-17,2,14.9
2020-08-17,3,14.9
2020-08-17,4,15.0
2020-08-17,5,15.4
2020-08-17,6,15.7
2020-08-17,7,16.3
2020-08-17,8,16.7
2020-08-17,9,16.7
2020-08-17,10,18.5
2020-08-17,11,20.3
2020-08-17,12,21.2
2020-08-17,13,20.7
2020-08-17,14,19.7
2020-08-17,15,19.9
2020-08-17,16,20.4
2020-08-17,17,19.5
2020-08-17,18,16.8
2020-08-17,19,16.2
2020-08-17,20,15.4
2020-08-17,21,15.5
2020-08-17,22,15.4
2020-08-18,23,15.3
2020-08-18,0,15.4
2020-08-18,1,15.5
2020-08-18,2,15.6
2020-08-18,3,15.2
2020-08-18,4,15.0
2020-08-18,5,15.0
2020-08-18,6,16.1
2020-08-18,7,17.6
2020-08-18,8,18.4
2020-08-18,9,19.3
2020-08-18,10,21.0
2020-08-18,11,21.7
2020-08-18,12,23.1
2020-08-18,13,23.4
2020-08-18,14,23.4
2020-08-18,15,21.2
2020-08-18,16,21.1
2020-08-18,17,20.8
2020-08-18,18,   N/A
2020-08-18,19,   N/A
2020-08-18,20,   N/A
2020-08-18,21,18.3
2020-08-18,22,18.0
2020-08-19,23,17.5
2020-08-19,0,16.3
2020-08-19,1,15.7
2020-08-19,2,15.5
2020-08-19,3,15.4
2020-08-19,4,15.1
2020-08-19,5,15.2
2020-08-19,6,16.3
2020-08-19,7,16.9
2020-08-19,8,16.9
2020-08-19,9,17.6
2020-08-19,10,18.1
2020-08-19,11,18.5
2020-08-19,12,18.6
2020-08-19,13,19.0
2020-08-19,14,19.1
2020-08-19,15,19.0
2020-08-19,16,19.1
2020-08-19,17,19.1
2020-08-19,18,18.9
2020-08-19,19,18.9
2020-08-19,20,18.9
2020-08-19,21,19.4
2020-08-19,22,19.7
2020-08-20,23,19.8
2020-08-20,0,19.7
2020-08-20,1,19.5
2020-08-20,2,18.0
2020-08-20,3,16.6
2020-08-20,4,15.2
2020-08-20,5,14.6
2020-08-20,6,16.4
2020-08-20,7,17.6
2020-08-20,8,19.2
2020-08-20,9,20.3
2020-08-20,10,21.3
2020-08-20,11,22.2
2020-08-20,12,22.9
2020-08-20,13,23.4
2020-08-20,14,23.7
2020-08-20,15,   N/A
2020-08-20,16,   N/A
2020-08-20,17,   N/A
2020-08-20,18,   N/A
2020-08-20,19,   N/A
2020-08-20,20,   N/A
2020-08-20,21,   N/A
2020-08-20,22,16.5
2020-08-21,23,17.1
2020-08-21,0,17.3
2020-08-21,1,17.7
2020-08-21,2,18.3
2020-08-21,3,18.2
2020-08-21,4,18.3
2020-08-21,5,18.3
2020-08-21,6,18.7
2020-08-21,7,19.2
2020-08-21,8,20.6
2020-08-21,9,20.6
2020-08-21,10,21.3
2020-08-21,11,22.4
2020-08-21,12,22.5
2020-08-21,13,22.1
2020-08-21,14,22.4
2020-08-21,15,21.3
2020-08-21,16,17.9
2020-08-21,17,18.8
2020-08-21,18,18.5
2020-08-21,19,18.2
2020-08-21,20,16.8
2020-08-21,21,16.6
2020-08-21,22,16.3
2020-08-22,23,16.1
2020-08-22,0,15.7
2020-08-22,1,15.2
2020-08-22,2,15.1
2020-08-22,3,14.6
2020-08-22,4,14.4
2020-08-22,5,14.7
2020-08-22,6,15.9
2020-08-22,7,16.9
2020-08-22,8,18.0
2020-08-22,9,17.8
2020-08-22,10,19.8
2020-08-22,11,20.6
2020-08-22,12,21.5
2020-08-22,13,21.5
2020-08-22,14,21.8
2020-08-22,15,20.5
2020-08-22,16,19.7
2020-08-22,17,18.6
2020-08-22,18,17.7
2020-08-22,19,16.7
2020-08-22,20,16.2
2020-08-22,21,14.8
2020-08-22,22,15.0
2020-08-23,23,15.0
2020-08-23,0,14.2
2020-08-23,1,14.2
2020-08-23,2,14.3
2020-08-23,3,14.3
2020-08-23,4,13.8
2020-08-23,5,12.9
2020-08-23,6,15.5
2020-08-23,7,17.6
2020-08-23,8,18.5
2020-08-23,9,19.2
2020-08-23,10,19.8
2020-08-23,11,19.9
2020-08-23,12,20.6
2020-08-23,13,19.9
2020-08-23,14,21.3
2020-08-23,15,20.5
2020-08-23,16,19.7
2020-08-23,17,18.6
2020-08-23,18,16.8
2020-08-23,19,16.2
2020-08-23,20,14.9
2020-08-23,21,13.5
2020-08-23,22,12.5
2020-08-24,23,12.2
2020-08-24,0,11.7
2020-08-24,1,12.0
2020-08-24,2,12.3
2020-08-24,3,12.5
2020-08-24,4,12.6
2020-08-24,5,12.7
2020-08-24,6,13.3
2020-08-24,7,15.7
2020-08-24,8,16.4
2020-08-24,9,18.8
2020-08-24,10,18.5
2020-08-24,11,20.0
2020-08-24,12,19.7
2020-08-24,13,21.3
2020-08-24,14,20.8
2020-08-24,15,20.6
2020-08-24,16,21.1
2020-08-24,17,20.7
2020-08-24,18,19.6
2020-08-24,19,17.5
2020-08-24,20,16.7
2020-08-24,21,16.6
2020-08-24,22,16.3
2020-08-25,23,16.3
2020-08-25,0,16.3
2020-08-25,1,16.3
2020-08-25,2,16.0
2020-08-25,3,15.8
2020-08-25,4,15.7
2020-08-25,5,15.2
2020-08-25,6,15.2
2020-08-25,7,14.9
2020-08-25,8,15.5
2020-08-25,9,16.4
2020-08-25,10,18.2
2020-08-25,11,20.3
2020-08-25,12,21.0
2020-08-25,13,21.4
2020-08-25,14,20.6
2020-08-25,15,19.5
2020-08-25,16,19.7
2020-08-25,17,17.8
2020-08-25,18,17.2
2020-08-25,19,16.2
2020-08-25,20,16.1
2020-08-25,21,16.1
2020-08-25,22,16.3
2020-08-26,23,16.3
2020-08-26,0,16.2
2020-08-26,1,16.2
2020-08-26,2,15.4
2020-08-26,3,15.3
2020-08-26,4,15.2
2020-08-26,5,14.9
2020-08-26,6,15.3
2020-08-26,7,15.8
2020-08-26,8,16.5
2020-08-26,9,17.9
2020-08-26,10,17.9
2020-08-26,11,18.4
2020-08-26,12,18.9
2020-08-26,13,20.6
2020-08-26,14,20.2
2020-08-26,15,19.2
2020-08-26,16,19.1
2020-08-26,17,19.2
2020-08-26,18,18.1
2020-08-26,19,15.8
2020-08-26,20,15.2
2020-08-26,21,14.9
2020-08-26,22,13.7
2020-08-27,23,12.8
2020-08-27,0,12.5
2020-08-27,1,12.5
2020-08-27,2,12.5
2020-08-27,3,12.4
2020-08-27,4,12.3
2020-08-27,5,12.2
2020-08-27,6,12.7
2020-08-27,7,13.6
2020-08-27,8,15.4
2020-08-27,9,15.6
2020-08-27,10,15.4
2020-08-27,11,15.6
2020-08-27,12,15.3
2020-08-27,13,15.5
2020-08-27,14,15.9
2020-08-27,15,15.0
2020-08-27,16,14.9
2020-08-27,17,14.6
2020-08-27,18,14.2
2020-08-27,19,13.6
2020-08-27,20,13.5
2020-08-27,21,13.1
2020-08-27,22,13.1
2020-08-28,23,12.3
2020-08-28,0,12.1
2020-08-28,1,12.0
2020-08-28,2,12.4
2020-08-28,3,12.6
2020-08-28,4,12.8
2020-08-28,5,13.2
2020-08-28,6,13.2
2020-08-28,7,13.3
2020-08-28,8,14.1
2020-08-28,9,15.7
2020-08-28,10,15.5
2020-08-28,11,17.5
2020-08-28,12,14.8
2020-08-28,13,14.8
2020-08-28,14,14.9
2020-08-28,15,13.4
2020-08-28,16,13.6
2020-08-28,17,12.7
2020-08-28,18,12.4
2020-08-28,19,12.1
2020-08-28,20,10.8
2020-08-28,21,10.7
2020-08-28,22,10.8
2020-08-29,23,10.6
2020-08-29,0,10.5
2020-08-29,1,10.5
2020-08-29,2,10.4
2020-08-29,3,10.5
2020-08-29,4,10.5
2020-08-29,5,10.6
2020-08-29,6,10.9
2020-08-29,7,10.9
2020-08-29,8,11.7
2020-08-29,9,12.6
2020-08-29,10,13.5
2020-08-29,11,13.8
2020-08-29,12,13.7
2020-08-29,13,13.5
2020-08-29,14,14.3
2020-08-29,15,13.9
2020-08-29,16,13.1
2020-08-29,17,11.9
2020-08-29,18,11.5
2020-08-29,19,11.4
2020-08-29,20,11.6
2020-08-29,21,11.4
2020-08-29,22,11.3
2020-08-30,23,11.3
2020-08-30,0,11.2
2020-08-30,1,11.0
2020-08-30,2,10.8
2020-08-30,3,10.5
2020-08-30,4,10.0
2020-08-30,5,10.5
2020-08-30,6,10.9
2020-08-30,7,11.5
2020-08-30,8,12.6
2020-08-30,9,13.2
2020-08-30,10,14.5
2020-08-30,11,14.7
2020-08-30,12,15.3
2020-08-30,13,16.0
2020-08-30,14,15.6
2020-08-30,15,16.1
2020-08-30,16,14.8
2020-08-30,17,14.3
2020-08-30,18,13.9
2020-08-30,19,12.3
2020-08-30,20,10.8
2020-08-30,21,9.8
2020-08-30,22,9.3
2020-08-31,23,9.6
2020-08-31,0,9.5
2020-08-31,1,8.8
2020-08-31,2,8.7
2020-08-31,3,8.3
2020-08-31,4,8.2
2020-08-31,5,8.4
2020-08-31,6,9.5
2020-08-31,7,10.8
2020-08-31,8,12.5
2020-08-31,9,14.4
2020-08-31,10,15.3
2020-08-31,11,16.1
2020-08-31,12,17.2
2020-08-31,13,17.4
2020-08-31,14,   N/A
2020-08-31,15,   N/A
2020-08-31,16,   N/A
2020-08-31,17,14.5
2020-08-31,18,14.2
2020-08-31,19,10.9
2020-08-31,20,8.7
2020-08-31,21,7.7
2020-08-31,22,6.5
2020-09-01,23,5.8
2020-09-01,0,5.3
2020-09-01,1,5.0
2020-09-01,2,4.3
2020-09-01,3,3.9
2020-09-01,4,4.0
2020-09-01,5,4.1
2020-09-01,6,6.7
2020-09-01,7,10.9
2020-09-01,8,16.3
2020-09-01,9,17.3
2020-09-01,10,17.8
2020-09-01,11,18.4
2020-09-01,12,18.7
2020-09-01,13,18.9
2020-09-01,14,19.1
2020-09-01,15,18.6
2020-09-01,16,17.9
2020-09-01,17,16.7
2020-09-01,18,16.1
2020-09-01,19,13.4
2020-09-01,20,10.8
2020-09-01,21,9.4
2020-09-01,22,9.1
2020-09-02,23,7.1
2020-09-02,0,6.5
2020-09-02,1,5.9
2020-09-02,2,5.6
2020-09-02,3,5.0
2020-09-02,4,5.0
2020-09-02,5,5.2
2020-09-02,6,9.7
2020-09-02,7,13.1
2020-09-02,8,14.9
2020-09-02,9,18.1
2020-09-02,10,18.4
2020-09-02,11,18.3
2020-09-02,12,18.5
2020-09-02,13,18.4
2020-09-02,14,17.1
2020-09-02,15,15.0
2020-09-02,16,15.5
2020-09-02,17,15.6
2020-09-02,18,15.1
2020-09-02,19,14.7
2020-09-02,20,15.0
2020-09-02,21,15.1
2020-09-02,22,15.4
2020-09-03,23,15.6
2020-09-03,0,15.6
2020-09-03,1,15.8
2020-09-03,2,15.9
2020-09-03,3,16.1
2020-09-03,4,16.6
2020-09-03,5,17.0
2020-09-03,6,17.2
2020-09-03,7,17.7
2020-09-03,8,18.2
2020-09-03,9,18.7
2020-09-03,10,19.1
2020-09-03,11,19.6
2020-09-03,12,20.0
2020-09-03,13,19.7
2020-09-03,14,19.5
2020-09-03,15,19.6
2020-09-03,16,18.8
2020-09-03,17,18.6
2020-09-03,18,17.1
2020-09-03,19,16.2
2020-09-03,20,15.4
2020-09-03,21,14.9
2020-09-03,22,14.1
2020-09-04,23,13.0
2020-09-04,0,12.3
2020-09-04,1,12.2
2020-09-04,2,11.4
2020-09-04,3,10.7
2020-09-04,4,11.3
2020-09-04,5,11.3
2020-09-04,6,11.7
2020-09-04,7,14.4
2020-09-04,8,14.8
2020-09-04,9,16.8
2020-09-04,10,17.1
2020-09-04,11,17.1
2020-09-04,12,17.9
2020-09-04,13,18.5
2020-09-04,14,17.7
2020-09-04,15,17.3
2020-09-04,16,16.3
2020-09-04,17,15.6
2020-09-04,18,14.6
2020-09-04,19,13.8
2020-09-04,20,13.3
2020-09-04,21,13.0
2020-09-04,22,11.9
2020-09-05,23,11.1
2020-09-05,0,9.8
2020-09-05,1,9.5
2020-09-05,2,8.5
2020-09-05,3,8.4
2020-09-05,4,7.7
2020-09-05,5,7.6
2020-09-05,6,9.9
2020-09-05,7,13.1
2020-09-05,8,14.5
2020-09-05,9,14.9
2020-09-05,10,16.0
2020-09-05,11,16.8
2020-09-05,12,17.4
2020-09-05,13,18.1
2020-09-05,14,17.9
2020-09-05,15,17.5
2020-09-05,16,17.0
2020-09-05,17,16.5
2020-09-05,18,15.1
2020-09-05,19,12.2
2020-09-05,20,10.6
2020-09-05,21,9.7
2020-09-05,22,9.5
2020-09-06,23,9.0
2020-09-06,0,8.7
2020-09-06,1,9.6
2020-09-06,2,9.6
2020-09-06,3,9.8
2020-09-06,4,9.8
2020-09-06,5,10.0
2020-09-06,6,10.4
2020-09-06,7,14.0
2020-09-06,8,15.6
2020-09-06,9,16.4
2020-09-06,10,15.8
2020-09-06,11,18.3
2020-09-06,12,16.9
2020-09-06,13,17.6
2020-09-06,14,18.5
2020-09-06,15,18.8
2020-09-06,16,18.6
2020-09-06,17,17.8
2020-09-06,18,16.1
2020-09-06,19,15.1
2020-09-06,20,14.1
2020-09-06,21,13.5
2020-09-06,22,12.3
2020-09-07,23,11.8
2020-09-07,0,10.0
2020-09-07,1,8.7
2020-09-07,2,8.4
2020-09-07,3,8.2
2020-09-07,4,8.3
2020-09-07,5,8.7
2020-09-07,6,10.1
2020-09-07,7,11.0
2020-09-07,8,13.3
2020-09-07,9,14.6
2020-09-07,10,16.0
2020-09-07,11,17.2
2020-09-07,12,18.0
2020-09-07,13,18.4
2020-09-07,14,18.1
2020-09-07,15,16.4
2020-09-07,16,16.5
2020-09-07,17,16.6
2020-09-07,18,16.3
2020-09-07,19,16.0
2020-09-07,20,15.8
2020-09-07,21,15.6
2020-09-07,22,15.5
2020-09-08,23,15.6
2020-09-08,0,15.9
2020-09-08,1,15.9
2020-09-08,2,16.0
2020-09-08,3,16.0
2020-09-08,4,16.0
2020-09-08,5,16.0
2020-09-08,6,16.5
2020-09-08,7,17.5
2020-09-08,8,18.6
2020-09-08,9,19.3
2020-09-08,10,20.9
2020-09-08,11,22.6
2020-09-08,12,24.0
2020-09-08,13,24.5
2020-09-08,14,24.5
2020-09-08,15,24.1
2020-09-08,16,22.7
2020-09-08,17,21.9
2020-09-08,18,20.1
2020-09-08,19,19.3
2020-09-08,20,18.2
2020-09-08,21,17.0
2020-09-08,22,16.4
2020-09-09,23,15.8
2020-09-09,0,15.6
2020-09-09,1,14.7
2020-09-09,2,14.0
2020-09-09,3,13.6
2020-09-09,4,13.4
2020-09-09,5,13.5
2020-09-09,6,14.6
2020-09-09,7,16.0
2020-09-09,8,17.6
2020-09-09,9,18.2
2020-09-09,10,20.3
2020-09-09,11,21.3
2020-09-09,12,20.0
2020-09-09,13,19.1
2020-09-09,14,19.2
2020-09-09,15,19.8
2020-09-09,16,19.8
2020-09-09,17,19.0
2020-09-09,18,16.7
2020-09-09,19,14.2
2020-09-09,20,12.9
2020-09-09,21,12.1
2020-09-09,22,10.7
2020-09-10,23,10.1
2020-09-10,0,9.2
2020-09-10,1,8.3
2020-09-10,2,7.3
2020-09-10,3,6.7
2020-09-10,4,5.2
2020-09-10,5,4.2
2020-09-10,6,7.2
2020-09-10,7,11.4
2020-09-10,8,13.5
2020-09-10,9,15.9
2020-09-10,10,16.7
2020-09-10,11,16.8
2020-09-10,12,17.1
2020-09-10,13,17.0
2020-09-10,14,17.0
2020-09-10,15,16.6
2020-09-10,16,16.0
2020-09-10,17,15.9
2020-09-10,18,14.2
2020-09-10,19,12.5
2020-09-10,20,11.7
2020-09-10,21,10.8
2020-09-10,22,8.8
2020-09-11,23,7.7
2020-09-11,0,7.0
2020-09-11,1,6.1
2020-09-11,2,5.7
2020-09-11,3,6.0
2020-09-11,4,6.9
2020-09-11,5,7.4
2020-09-11,6,8.6
2020-09-11,7,10.2
2020-09-11,8,13.7
2020-09-11,9,15.2
2020-09-11,10,15.6
2020-09-11,11,15.9
2020-09-11,12,16.1
2020-09-11,13,16.4
2020-09-11,14,16.6
2020-09-11,15,16.1
2020-09-11,16,16.2
2020-09-11,17,15.5
2020-09-11,18,14.5
2020-09-11,19,13.4
2020-09-11,20,12.9
2020-09-11,21,12.1
2020-09-11,22,11.7
2020-09-12,23,12.0
2020-09-12,0,11.9
2020-09-12,1,11.7
2020-09-12,2,11.4
2020-09-12,3,11.8
2020-09-12,4,12.6
2020-09-12,5,12.9
2020-09-12,6,13.2
2020-09-12,7,14.4
2020-09-12,8,15.3
2020-09-12,9,17.2
2020-09-12,10,18.3
2020-09-12,11,19.1
2020-09-12,12,18.7
2020-09-12,13,20.3
2020-09-12,14,20.2
2020-09-12,15,19.3
2020-09-12,16,18.8
2020-09-12,17,17.9
2020-09-12,18,16.5
2020-09-12,19,14.8
2020-09-12,20,13.4
2020-09-12,21,13.4
2020-09-12,22,13.5
2020-09-13,23,13.4
2020-09-13,0,12.9
2020-09-13,1,12.5
2020-09-13,2,12.3
2020-09-13,3,12.1
2020-09-13,4,11.7
2020-09-13,5,12.3
2020-09-13,6,13.0
2020-09-13,7,15.2
2020-09-13,8,16.9
2020-09-13,9,18.9
2020-09-13,10,20.3
2020-09-13,11,21.6
2020-09-13,12,22.7
2020-09-13,13,23.3
2020-09-13,14,23.4
2020-09-13,15,23.2
2020-09-13,16,22.4
2020-09-13,17,21.5
2020-09-13,18,18.9
2020-09-13,19,18.5
2020-09-13,20,17.7
2020-09-13,21,16.5
2020-09-13,22,15.8
2020-09-14,23,15.0
2020-09-14,0,12.2
2020-09-14,1,11.5
2020-09-14,2,9.6
2020-09-14,3,8.5
2020-09-14,4,7.2
2020-09-14,5,6.7
2020-09-14,6,7.8
2020-09-14,7,12.2
2020-09-14,8,17.0
2020-09-14,9,21.8
2020-09-14,10,23.7
2020-09-14,11,25.4
2020-09-14,12,26.4
2020-09-14,13,27.1
2020-09-14,14,27.1
2020-09-14,15,26.7
2020-09-14,16,25.8
2020-09-14,17,25.2
2020-09-14,18,20.4
2020-09-14,19,16.5
2020-09-14,20,14.5
2020-09-14,21,13.3
2020-09-14,22,12.9
2020-09-15,23,13.1
2020-09-15,0,12.7
2020-09-15,1,12.2
2020-09-15,2,11.7
2020-09-15,3,12.1
2020-09-15,4,12.1
2020-09-15,5,12.1
2020-09-15,6,13.3
2020-09-15,7,15.9
2020-09-15,8,19.8
2020-09-15,9,23.0
2020-09-15,10,25.5
2020-09-15,11,26.6
2020-09-15,12,27.5
2020-09-15,13,27.9
2020-09-15,14,26.9
2020-09-15,15,27.1
2020-09-15,16,26.4
2020-09-15,17,24.6
2020-09-15,18,23.1
2020-09-15,19,21.2
2020-09-15,20,20.4
2020-09-15,21,19.5
2020-09-15,22,18.2
2020-09-16,23,18.0
2020-09-16,0,16.6
2020-09-16,1,16.4
2020-09-16,2,15.7
2020-09-16,3,15.4
2020-09-16,4,15.0
2020-09-16,5,13.9
2020-09-16,6,14.0
2020-09-16,7,15.6
2020-09-16,8,20.4
2020-09-16,9,22.7
2020-09-16,10,22.7
2020-09-16,11,23.2
2020-09-16,12,23.5
2020-09-16,13,23.7
2020-09-16,14,23.7
2020-09-16,15,22.0
2020-09-16,16,20.2
2020-09-16,17,17.5
2020-09-16,18,16.1
2020-09-16,19,15.2
2020-09-16,20,15.3
2020-09-16,21,15.1
2020-09-16,22,14.7
2020-09-17,23,14.2
2020-09-17,0,13.8
2020-09-17,1,13.6
2020-09-17,2,12.8
2020-09-17,3,12.3
2020-09-17,4,11.5
2020-09-17,5,10.5
2020-09-17,6,10.5
2020-09-17,7,13.7
2020-09-17,8,15.4
2020-09-17,9,16.8
2020-09-17,10,17.8
2020-09-17,11,18.9
2020-09-17,12,20.1
2020-09-17,13,20.7
2020-09-17,14,20.9
2020-09-17,15,20.5
2020-09-17,16,19.6
2020-09-17,17,18.5
2020-09-17,18,16.1
2020-09-17,19,14.7
2020-09-17,20,13.4
2020-09-17,21,12.2
2020-09-17,22,   N/A
2020-09-18,23,   N/A
2020-09-18,0,   N/A
2020-09-18,1,   N/A
2020-09-18,2,   N/A
2020-09-18,3,   N/A
2020-09-18,4,   N/A
2020-09-18,5,   N/A
2020-09-18,6,   N/A
2020-09-18,7,   N/A
2020-09-18,8,   N/A
2020-09-18,9,   N/A
2020-09-18,10,   N/A
2020-09-18,11,   N/A
2020-09-18,12,   N/A
2020-09-18,13,   N/A
2020-09-18,14,   N/A
2020-09-18,15,   N/A
2020-09-18,16,   N/A
2020-09-18,17,   N/A
2020-09-18,18,   N/A
2020-09-18,19,   N/A
2020-09-18,20,   N/A
2020-09-18,21,   N/A
2020-09-18,22,   N/A
2020-09-19,23,   N/A
2020-09-19,0,   N/A
2020-09-19,1,   N/A
2020-09-19,2,   N/A
2020-09-19,3,   N/A
2020-09-19,4,   N/A
2020-09-19,5,   N/A
2020-09-19,6,   N/A
2020-09-19,7,   N/A
2020-09-19,8,   N/A
2020-09-19,9,   N/A
2020-09-19,10,   N/A
2020-09-19,11,   N/A
2020-09-19,12,   N/A
2020-09-19,13,   N/A
2020-09-19,14,   N/A
2020-09-19,15,   N/A
2020-09-19,16,   N/A
2020-09-19,17,   N/A
2020-09-19,18,   N/A
2020-09-19,19,   N/A
2020-09-19,20,   N/A
2020-09-19,21,   N/A
2020-09-19,22,   N/A
2020-09-20,23,   N/A
2020-09-20,0,   N/A
2020-09-20,1,   N/A
2020-09-20,2,   N/A
2020-09-20,3,   N/A
2020-09-20,4,   N/A
2020-09-20,5,   N/A
2020-09-20,6,   N/A
2020-09-20,7,   N/A
2020-09-20,8,   N/A
2020-09-20,9,   N/A
2020-09-20,10,   N/A
2020-09-20,11,   N/A
2020-09-20,12,   N/A
2020-09-20,13,   N/A
2020-09-20,14,   N/A
2020-09-20,15,   N/A
2020-09-20,16,   N/A
2020-09-20,17,   N/A
2020-09-20,18,   N/A
2020-09-20,19,   N/A
2020-09-20,20,   N/A
2020-09-20,21,   N/A
2020-09-20,22,   N/A
2020-09-21,23,   N/A
2020-09-21,0,   N/A
2020-09-21,1,   N/A
2020-09-21,2,   N/A
2020-09-21,3,   N/A
2020-09-21,4,   N/A
2020-09-21,5,   N/A
2020-09-21,6,   N/A
2020-09-21,7,   N/A
2020-09-21,8,11.9
2020-09-21,9,12.9
2020-09-21,10,15.0
2020-09-21,11,18.2
2020-09-21,12,20.3
2020-09-21,13,20.8
2020-09-21,14,22.3
2020-09-21,15,21.3
2020-09-21,16,21.1
2020-09-21,17,20.4
2020-09-21,18,18.8
2020-09-21,19,15.3
2020-09-21,20,13.9
2020-09-21,21,12.4
2020-09-21,22,12.0
2020-09-22,23,11.3
2020-09-22,0,11.1
2020-09-22,1,10.9
2020-09-22,2,9.7
2020-09-22,3,9.5
2020-09-22,4,9.1
2020-09-22,5,9.7
2020-09-22,6,11.0
2020-09-22,7,14.0
2020-09-22,8,18.6
2020-09-22,9,20.3
2020-09-22,10,22.1
2020-09-22,11,23.2
2020-09-22,12,23.5
2020-09-22,13,23.6
2020-09-22,14,23.4
2020-09-22,15,22.2
2020-09-22,16,18.8
2020-09-22,17,17.4
2020-09-22,18,17.3
2020-09-22,19,17.0
2020-09-22,20,16.3
2020-09-22,21,15.3
2020-09-22,22,15.2
2020-09-23,23,15.2
2020-09-23,0,14.8
2020-09-23,1,14.8
2020-09-23,2,14.6
2020-09-23,3,14.3
2020-09-23,4,14.2
2020-09-23,5,14.2
2020-09-23,6,14.3
2020-09-23,7,14.5
2020-09-23,8,14.9
2020-09-23,9,15.9
2020-09-23,10,15.9
2020-09-23,11,16.1
2020-09-23,12,17.1
2020-09-23,13,16.8
2020-09-23,14,16.3
2020-09-23,15,13.4
2020-09-23,16,13.6
2020-09-23,17,13.4
2020-09-23,18,13.2
2020-09-23,19,11.7
2020-09-23,20,11.3
2020-09-23,21,8.9
2020-09-23,22,8.6
2020-09-24,23,8.2
2020-09-24,0,7.3
2020-09-24,1,6.8
2020-09-24,2,7.0
2020-09-24,3,7.8
2020-09-24,4,8.4
2020-09-24,5,8.6
2020-09-24,6,9.3
2020-09-24,7,10.6
2020-09-24,8,12.2
2020-09-24,9,13.7
2020-09-24,10,14.3
2020-09-24,11,14.9
2020-09-24,12,14.2
2020-09-24,13,13.8
2020-09-24,14,13.5
2020-09-24,15,13.2
2020-09-24,16,12.2
2020-09-24,17,10.3
2020-09-24,18,10.0
2020-09-24,19,9.1
2020-09-24,20,7.5
2020-09-24,21,8.2
2020-09-24,22,7.3
2020-09-25,23,6.2
2020-09-25,0,6.0
2020-09-25,1,6.1
2020-09-25,2,6.4
2020-09-25,3,6.8
2020-09-25,4,7.5
2020-09-25,5,7.9
2020-09-25,6,7.9
2020-09-25,7,8.1
2020-09-25,8,9.7
2020-09-25,9,11.0
2020-09-25,10,11.1
2020-09-25,11,12.6
2020-09-25,12,12.8
2020-09-25,13,13.6
2020-09-25,14,13.7
2020-09-25,15,13.5
2020-09-25,16,13.1
2020-09-25,17,12.1
2020-09-25,18,11.3
2020-09-25,19,10.5
2020-09-25,20,10.4
2020-09-25,21,9.8
2020-09-25,22,9.5
2020-09-26,23,8.4
2020-09-26,0,7.9
2020-09-26,1,7.1
2020-09-26,2,6.7
2020-09-26,3,6.2
2020-09-26,4,5.8
2020-09-26,5,5.3
2020-09-26,6,5.6
2020-09-26,7,7.1
2020-09-26,8,8.8
2020-09-26,9,9.9
2020-09-26,10,11.4
2020-09-26,11,11.6
2020-09-26,12,11.9
2020-09-26,13,12.3
2020-09-26,14,12.4
2020-09-26,15,11.4
2020-09-26,16,11.1
2020-09-26,17,11.0
2020-09-26,18,11.0
2020-09-26,19,10.8
2020-09-26,20,10.7
2020-09-26,21,10.5
2020-09-26,22,10.3
2020-09-27,23,10.3
2020-09-27,0,10.0
2020-09-27,1,10.0
2020-09-27,2,9.9
2020-09-27,3,9.9
2020-09-27,4,9.8
2020-09-27,5,9.6
2020-09-27,6,9.5
2020-09-27,7,9.8
2020-09-27,8,10.5
2020-09-27,9,11.1
2020-09-27,10,11.9
2020-09-27,11,12.6
2020-09-27,12,13.1
2020-09-27,13,13.4
2020-09-27,14,13.5
2020-09-27,15,13.4
2020-09-27,16,13.1
2020-09-27,17,12.1
2020-09-27,18,10.3
2020-09-27,19,9.3
2020-09-27,20,8.5
2020-09-27,21,8.0
2020-09-27,22,7.4
2020-09-28,23,7.1
2020-09-28,0,7.0
2020-09-28,1,6.8
2020-09-28,2,6.1
2020-09-28,3,5.4
2020-09-28,4,4.4
2020-09-28,5,4.0
2020-09-28,6,4.0
2020-09-28,7,7.2
2020-09-28,8,9.5
2020-09-28,9,10.5
2020-09-28,10,   N/A
2020-09-28,11,   N/A
2020-09-28,12,   N/A
2020-09-28,13,   N/A
2020-09-28,14,   N/A
2020-09-28,15,   N/A
2020-09-28,16,   N/A
2020-09-28,17,   N/A
2020-09-28,18,   N/A
2020-09-28,19,   N/A
2020-09-28,20,   N/A
2020-09-28,21,   N/A
2020-09-28,22,   N/A
2020-09-29,23,   N/A
2020-09-29,0,   N/A
2020-09-29,1,   N/A
2020-09-29,2,   N/A
2020-09-29,3,   N/A
2020-09-29,4,   N/A
2020-09-29,5,   N/A
2020-09-29,6,   N/A
2020-09-29,7,   N/A
2020-09-29,8,13.1
2020-09-29,9,13.2
2020-09-29,10,13.2
2020-09-29,11,13.3
2020-09-29,12,15.2
2020-09-29,13,16.8
2020-09-29,14,16.8
2020-09-29,15,16.2
2020-09-29,16,15.4
2020-09-29,17,14.6
2020-09-29,18,12.5
2020-09-29,19,11.3
2020-09-29,20,10.4
2020-09-29,21,10.3
2020-09-29,22,9.9
2020-09-30,23,10.0
2020-09-30,0,9.9
2020-09-30,1,9.4
2020-09-30,2,9.2
2020-09-30,3,9.3
2020-09-30,4,9.2
2020-09-30,5,9.3
2020-09-30,6,9.9
2020-09-30,7,11.0
2020-09-30,8,12.4
2020-09-30,9,13.6
2020-09-30,10,14.4
2020-09-30,11,   N/A
2020-09-30,12,15.2
2020-09-30,13,15.3
2020-09-30,14,14.4
2020-09-30,15,13.5
2020-09-30,16,13.7
2020-09-30,17,13.8
2020-09-30,18,13.8
2020-09-30,19,13.8
2020-09-30,20,13.5
2020-09-30,21,12.7
2020-09-30,22,12.3
2020-10-01,23,12.2
2020-10-01,0,11.8
2020-10-01,1,11.6
2020-10-01,2,11.1
2020-10-01,3,10.2
2020-10-01,4,9.7
2020-10-01,5,9.4
2020-10-01,6,9.1
2020-10-01,7,9.3
2020-10-01,8,10.4
2020-10-01,9,10.9
2020-10-01,10,12.3
2020-10-01,11,13.8
2020-10-01,12,14.8
2020-10-01,13,14.7
2020-10-01,14,14.8
2020-10-01,15,14.4
2020-10-01,16,13.0
2020-10-01,17,12.3
2020-10-01,18,10.7
2020-10-01,19,10.3
2020-10-01,20,10.3
2020-10-01,21,10.0
2020-10-01,22,9.6
2020-10-02,23,9.2
2020-10-02,0,9.3
2020-10-02,1,9.2
2020-10-02,2,10.0
2020-10-02,3,10.1
2020-10-02,4,10.5
2020-10-02,5,10.5
2020-10-02,6,10.4
2020-10-02,7,10.5
2020-10-02,8,10.5
2020-10-02,9,10.7
2020-10-02,10,11.5
2020-10-02,11,11.6
2020-10-02,12,11.8
2020-10-02,13,11.8
2020-10-02,14,11.8
2020-10-02,15,11.8
2020-10-02,16,11.8
2020-10-02,17,11.8
2020-10-02,18,11.8
2020-10-02,19,11.8
2020-10-02,20,11.8
2020-10-02,21,11.8
2020-10-02,22,11.8
2020-10-03,23,11.8
2020-10-03,0,12.9
2020-10-03,1,12.9
2020-10-03,2,13.0
2020-10-03,3,13.1
2020-10-03,4,12.9
2020-10-03,5,13.2
2020-10-03,6,13.3
2020-10-03,7,13.4
2020-10-03,8,13.5
2020-10-03,9,13.7
2020-10-03,10,13.7
2020-10-03,11,13.8
2020-10-03,12,12.4
2020-10-03,13,12.1
2020-10-03,14,12.0
2020-10-03,15,11.9
2020-10-03,16,12.0
2020-10-03,17,11.9
2020-10-03,18,11.8
2020-10-03,19,11.2
2020-10-03,20,10.6
2020-10-03,21,9.7
2020-10-03,22,9.6
2020-10-04,23,9.1
2020-10-04,0,8.7
2020-10-04,1,8.3
2020-10-04,2,8.1
2020-10-04,3,8.3
2020-10-04,4,8.5
2020-10-04,5,8.5
2020-10-04,6,8.4
2020-10-04,7,8.5
2020-10-04,8,8.6
2020-10-04,9,8.9
2020-10-04,10,9.2
2020-10-04,11,9.5
2020-10-04,12,10.0
2020-10-04,13,10.3
2020-10-04,14,10.4
2020-10-04,15,10.7
2020-10-04,16,10.8
2020-10-04,17,10.7
2020-10-04,18,9.9
2020-10-04,19,8.9
2020-10-04,20,8.6
2020-10-04,21,9.2
2020-10-04,22,8.9
2020-10-05,23,8.1
2020-10-05,0,8.4
2020-10-05,1,8.2
2020-10-05,2,8.1
2020-10-05,3,7.8
2020-10-05,4,7.7
2020-10-05,5,7.7
2020-10-05,6,8.3
2020-10-05,7,9.5
2020-10-05,8,10.3
2020-10-05,9,11.5
2020-10-05,10,14.6
2020-10-05,11,15.7
2020-10-05,12,15.5
2020-10-05,13,16.0
2020-10-05,14,14.4
2020-10-05,15,13.6
2020-10-05,16,13.5
2020-10-05,17,13.4
2020-10-05,18,13.0
2020-10-05,19,12.4
2020-10-05,20,12.0
2020-10-05,21,11.5
2020-10-05,22,10.2
2020-10-06,23,10.7
2020-10-06,0,10.7
2020-10-06,1,10.4
2020-10-06,2,10.4
2020-10-06,3,10.7
2020-10-06,4,10.8
2020-10-06,5,10.3
2020-10-06,6,10.1
2020-10-06,7,10.9
2020-10-06,8,12.9
2020-10-06,9,13.9
2020-10-06,10,14.5
2020-10-06,11,16.1
2020-10-06,12,15.4
2020-10-06,13,15.7
2020-10-06,14,15.2
2020-10-06,15,14.4
2020-10-06,16,14.1
2020-10-06,17,12.9
2020-10-06,18,12.1
2020-10-06,19,11.3
2020-10-06,20,10.9
2020-10-06,21,10.1
2020-10-06,22,9.7
2020-10-07,23,9.5
2020-10-07,0,8.9
2020-10-07,1,9.9
2020-10-07,2,9.9
2020-10-07,3,8.6
2020-10-07,4,7.8
2020-10-07,5,7.5
2020-10-07,6,8.0
2020-10-07,7,8.9
2020-10-07,8,11.0
2020-10-07,9,12.4
2020-10-07,10,13.5
2020-10-07,11,13.7
2020-10-07,12,14.2
2020-10-07,13,15.3
2020-10-07,14,14.5
2020-10-07,15,14.0
2020-10-07,16,13.7
2020-10-07,17,12.6
2020-10-07,18,12.2
2020-10-07,19,11.9
2020-10-07,20,11.4
2020-10-07,21,11.6
2020-10-07,22,11.6
2020-10-08,23,11.8
2020-10-08,0,13.1
2020-10-08,1,14.1
2020-10-08,2,14.5
2020-10-08,3,14.6
2020-10-08,4,14.6
2020-10-08,5,14.9
2020-10-08,6,14.6
2020-10-08,7,15.0
2020-10-08,8,15.4
2020-10-08,9,15.8
2020-10-08,10,16.3
2020-10-08,11,15.7
2020-10-08,12,14.0
2020-10-08,13,14.0
2020-10-08,14,13.4
2020-10-08,15,13.1
2020-10-08,16,12.6
2020-10-08,17,11.7
2020-10-08,18,9.9
2020-10-08,19,9.1
2020-10-08,20,7.9
2020-10-08,21,7.2
2020-10-08,22,7.0
2020-10-09,23,6.0
2020-10-09,0,6.2
2020-10-09,1,6.1
2020-10-09,2,6.0
2020-10-09,3,5.8
2020-10-09,4,6.2
2020-10-09,5,5.8
2020-10-09,6,6.2
2020-10-09,7,7.4
2020-10-09,8,10.0
2020-10-09,9,11.6
2020-10-09,10,12.1
2020-10-09,11,12.2
2020-10-09,12,12.5
2020-10-09,13,12.8
2020-10-09,14,12.3
2020-10-09,15,12.0
2020-10-09,16,11.7
2020-10-09,17,10.4
2020-10-09,18,9.5
2020-10-09,19,7.3
2020-10-09,20,6.5
2020-10-09,21,5.9
2020-10-09,22,5.2
2020-10-10,23,4.7
2020-10-10,0,3.8
2020-10-10,1,3.6
2020-10-10,2,3.8
2020-10-10,3,3.6
2020-10-10,4,3.9
2020-10-10,5,4.0
2020-10-10,6,4.6
2020-10-10,7,5.7
2020-10-10,8,8.1
2020-10-10,9,10.0
2020-10-10,10,11.4
2020-10-10,11,11.8
2020-10-10,12,12.5
2020-10-10,13,12.8
2020-10-10,14,13.6
2020-10-10,15,12.2
2020-10-10,16,11.4
2020-10-10,17,10.7
2020-10-10,18,10.4
2020-10-10,19,10.1
2020-10-10,20,9.8
2020-10-10,21,9.2
2020-10-10,22,9.1
2020-10-11,23,9.0
2020-10-11,0,8.4
2020-10-11,1,7.8
2020-10-11,2,7.5
2020-10-11,3,7.5
2020-10-11,4,7.8
2020-10-11,5,7.2
2020-10-11,6,6.7
2020-10-11,7,8.2
2020-10-11,8,10.2
2020-10-11,9,11.3
2020-10-11,10,12.0
2020-10-11,11,12.3
2020-10-11,12,12.8
2020-10-11,13,13.1
2020-10-11,14,13.4
2020-10-11,15,12.3
2020-10-11,16,11.8
2020-10-11,17,10.6
2020-10-11,18,10.5
2020-10-11,19,8.9
2020-10-11,20,8.6
2020-10-11,21,8.3
2020-10-11,22,7.8
2020-10-12,23,7.5
2020-10-12,0,7.5
2020-10-12,1,6.8
2020-10-12,2,6.7
2020-10-12,3,6.7
2020-10-12,4,6.9
2020-10-12,5,6.8
2020-10-12,6,6.8
2020-10-12,7,7.3
2020-10-12,8,8.5
2020-10-12,9,9.4
2020-10-12,10,10.8
2020-10-12,11,11.3
2020-10-12,12,11.7
2020-10-12,13,11.4
2020-10-12,14,10.4
2020-10-12,15,10.2
2020-10-12,16,9.9
2020-10-12,17,9.9
2020-10-12,18,10.1
2020-10-12,19,10.2
2020-10-12,20,9.9
2020-10-12,21,9.4
2020-10-12,22,8.8
2020-10-13,23,7.7
2020-10-13,0,6.4
2020-10-13,1,6.3
2020-10-13,2,6.5
2020-10-13,3,6.5
2020-10-13,4,6.1
2020-10-13,5,5.7
2020-10-13,6,5.3
2020-10-13,7,7.3
2020-10-13,8,8.4
2020-10-13,9,8.8
2020-10-13,10,9.5
2020-10-13,11,10.1
2020-10-13,12,10.9
2020-10-13,13,11.3
2020-10-13,14,11.4
2020-10-13,15,11.5
2020-10-13,16,11.3
2020-10-13,17,10.3
2020-10-13,18,9.5
2020-10-13,19,9.3
2020-10-13,20,9.6
2020-10-13,21,9.7
2020-10-13,22,9.6
2020-10-14,23,9.3
2020-10-14,0,9.2
2020-10-14,1,9.0
2020-10-14,2,8.2
2020-10-14,3,7.6
2020-10-14,4,7.6
2020-10-14,5,7.6
2020-10-14,6,8.4
2020-10-14,7,9.1
2020-10-14,8,11.3
2020-10-14,9,12.0
2020-10-14,10,13.1
2020-10-14,11,13.3
2020-10-14,12,13.4
2020-10-14,13,   N/A
2020-10-14,14,   N/A
2020-10-14,15,11.4
2020-10-14,16,11.2
2020-10-14,17,9.9
2020-10-14,18,9.5
2020-10-14,19,8.9
2020-10-14,20,8.4
2020-10-14,21,8.8
2020-10-14,22,8.5
2020-10-15,23,8.2
2020-10-15,0,7.9
2020-10-15,1,7.9
2020-10-15,2,7.8
2020-10-15,3,7.2
2020-10-15,4,6.4
2020-10-15,5,7.0
2020-10-15,6,6.6
2020-10-15,7,7.0
2020-10-15,8,9.7
2020-10-15,9,9.5
2020-10-15,10,10.0
2020-10-15,11,11.8
2020-10-15,12,12.6
2020-10-15,13,10.4
2020-10-15,14,11.8
2020-10-15,15,10.4
2020-10-15,16,10.2
2020-10-15,17,9.5
2020-10-15,18,9.3
2020-10-15,19,8.9
2020-10-15,20,8.3
2020-10-15,21,7.9
2020-10-15,22,7.8
2020-10-16,23,7.8
2020-10-16,0,7.1
2020-10-16,1,7.6
2020-10-16,2,7.7
2020-10-16,3,7.6
2020-10-16,4,7.3
2020-10-16,5,7.1
2020-10-16,6,7.1
2020-10-16,7,7.4
2020-10-16,8,8.6
2020-10-16,9,9.6
2020-10-16,10,10.4
2020-10-16,11,11.0
2020-10-16,12,11.2
2020-10-16,13,11.4
2020-10-16,14,10.8
2020-10-16,15,10.9
2020-10-16,16,10.8
2020-10-16,17,10.2
2020-10-16,18,9.7
2020-10-16,19,9.9
2020-10-16,20,9.9
2020-10-16,21,9.7
2020-10-16,22,9.1
2020-10-17,23,8.8
2020-10-17,0,8.6
2020-10-17,1,8.6
2020-10-17,2,8.6
2020-10-17,3,8.6
2020-10-17,4,8.5
2020-10-17,5,8.3
2020-10-17,6,8.2
2020-10-17,7,8.5
2020-10-17,8,9.0
2020-10-17,9,10.4
2020-10-17,10,11.3
2020-10-17,11,11.9
2020-10-17,12,10.6
2020-10-17,13,10.1
2020-10-17,14,10.6
2020-10-17,15,10.6
2020-10-17,16,10.0
2020-10-17,17,9.1
2020-10-17,18,8.5
2020-10-17,19,8.3
2020-10-17,20,8.3
2020-10-17,21,8.2
2020-10-17,22,8.3
2020-10-18,23,8.3
2020-10-18,0,8.3
2020-10-18,1,8.3
2020-10-18,2,8.3
2020-10-18,3,8.0
2020-10-18,4,7.9
2020-10-18,5,8.0
2020-10-18,6,7.9
2020-10-18,7,8.4
2020-10-18,8,9.0
2020-10-18,9,9.8
2020-10-18,10,10.2
2020-10-18,11,10.9
2020-10-18,12,11.3
2020-10-18,13,11.5
2020-10-18,14,11.6
2020-10-18,15,11.2
2020-10-18,16,10.8
2020-10-18,17,10.4
2020-10-18,18,10.0
2020-10-18,19,10.0
2020-10-18,20,10.0
2020-10-18,21,10.0
2020-10-18,22,9.4
2020-10-19,23,9.4
2020-10-19,0,9.4
2020-10-19,1,9.1
2020-10-19,2,8.9
2020-10-19,3,9.0
2020-10-19,4,8.8
2020-10-19,5,8.5
2020-10-19,6,8.1
2020-10-19,7,9.3
2020-10-19,8,10.4
2020-10-19,9,11.7
2020-10-19,10,12.8
2020-10-19,11,14.2
2020-10-19,12,14.7
2020-10-19,13,15.1
2020-10-19,14,14.3
2020-10-19,15,14.0
2020-10-19,16,12.9
2020-10-19,17,12.0
2020-10-19,18,11.6
2020-10-19,19,11.6
2020-10-19,20,11.5
2020-10-19,21,11.5
2020-10-19,22,11.4
2020-10-20,23,11.6
2020-10-20,0,11.9
2020-10-20,1,11.9
2020-10-20,2,11.3
2020-10-20,3,12.0
2020-10-20,4,12.6
2020-10-20,5,13.1
2020-10-20,6,13.4
2020-10-20,7,13.5
2020-10-20,8,14.0
2020-10-20,9,15.4
2020-10-20,10,16.0
2020-10-20,11,16.1
2020-10-20,12,16.2
2020-10-20,13,16.8
2020-10-20,14,17.2
2020-10-20,15,16.5
2020-10-20,16,15.9
2020-10-20,17,15.3
2020-10-20,18,14.9
2020-10-20,19,14.7
2020-10-20,20,14.5
2020-10-20,21,14.5
2020-10-20,22,14.5
2020-10-21,23,14.6
2020-10-21,0,14.6
2020-10-21,1,14.4
2020-10-21,2,14.5
2020-10-21,3,14.4
2020-10-21,4,14.4
2020-10-21,5,14.0
2020-10-21,6,13.6
2020-10-21,7,13.5
2020-10-21,8,13.6
2020-10-21,9,13.6
2020-10-21,10,13.8
2020-10-21,11,14.0
2020-10-21,12,13.9
2020-10-21,13,13.5
2020-10-21,14,14.0
2020-10-21,15,14.0
2020-10-21,16,13.6
2020-10-21,17,12.9
2020-10-21,18,12.9
2020-10-21,19,13.0
2020-10-21,20,13.2
2020-10-21,21,13.1
2020-10-21,22,12.9
2020-10-22,23,12.2
2020-10-22,0,12.1
2020-10-22,1,12.0
2020-10-22,2,11.5
2020-10-22,3,11.5
2020-10-22,4,11.9
2020-10-22,5,11.9
2020-10-22,6,11.4
2020-10-22,7,11.4
2020-10-22,8,11.1
2020-10-22,9,11.6
2020-10-22,10,13.5
2020-10-22,11,13.5
2020-10-22,12,14.1
2020-10-22,13,13.7
2020-10-22,14,13.8
2020-10-22,15,12.7
2020-10-22,16,11.9
2020-10-22,17,10.9
2020-10-22,18,10.4
2020-10-22,19,10.3
2020-10-22,20,10.1
2020-10-22,21,9.4
2020-10-22,22,9.8
2020-10-23,23,9.8
2020-10-23,0,9.9
2020-10-23,1,10.5
2020-10-23,2,10.9
2020-10-23,3,11.4
2020-10-23,4,11.5
2020-10-23,5,11.8
2020-10-23,6,11.5
2020-10-23,7,11.6
2020-10-23,8,12.5
2020-10-23,9,12.9
2020-10-23,10,13.1
2020-10-23,11,13.4
2020-10-23,12,14.3
2020-10-23,13,13.7
2020-10-23,14,13.2
2020-10-23,15,13.1
2020-10-23,16,11.7
2020-10-23,17,10.4
2020-10-23,18,8.9
2020-10-23,19,8.1
2020-10-23,20,7.4
2020-10-23,21,6.7
2020-10-23,22,6.4
2020-10-24,23,6.3
2020-10-24,0,6.4
2020-10-24,1,7.7
2020-10-24,2,8.0
2020-10-24,3,8.2
2020-10-24,4,9.0
2020-10-24,5,9.4
2020-10-24,6,10.1
2020-10-24,7,11.3
2020-10-24,8,12.3
2020-10-24,9,12.9
2020-10-24,10,13.6
2020-10-24,11,14.0
2020-10-24,12,14.2
2020-10-24,13,14.5
2020-10-24,14,15.0
2020-10-24,15,14.9
2020-10-24,16,13.8
2020-10-24,17,13.2
2020-10-24,18,12.2
2020-10-24,19,11.8
2020-10-24,20,11.0
2020-10-24,21,10.5
2020-10-24,22,10.3
2020-10-25,23,10.2
2020-10-25,0,9.6
2020-10-25,1,9.3
2020-10-25,2,8.2
2020-10-25,3,7.6
2020-10-25,4,7.3
2020-10-25,5,7.5
2020-10-25,6,7.4
2020-10-25,7,7.4
2020-10-25,8,9.5
2020-10-25,9,10.4
2020-10-25,10,11.3
2020-10-25,11,12.6
2020-10-25,12,13.4
2020-10-25,13,13.5
2020-10-25,14,12.0
2020-10-25,15,10.4
2020-10-25,16,8.5
2020-10-25,17,7.7
2020-10-25,18,7.2
2020-10-25,19,7.2
2020-10-25,20,7.2
2020-10-25,21,7.1
2020-10-25,22,7.1
2020-10-25,23,6.6
2020-10-26,0,6.2
2020-10-26,1,6.0
2020-10-26,2,5.3
2020-10-26,3,5.2
2020-10-26,4,5.6
2020-10-26,5,5.8
2020-10-26,6,5.7
2020-10-26,7,6.7
2020-10-26,8,8.6
2020-10-26,9,10.3
2020-10-26,10,10.9
2020-10-26,11,12.2
2020-10-26,12,12.6
2020-10-26,13,13.4
2020-10-26,14,13.1
2020-10-26,15,12.6
2020-10-26,16,10.9
2020-10-26,17,9.6
2020-10-26,18,8.6
2020-10-26,19,7.6
2020-10-26,20,7.5
2020-10-26,21,7.2
2020-10-26,22,6.8
2020-10-26,23,6.3
2020-10-27,0,6.3
2020-10-27,1,6.1
2020-10-27,2,5.8
2020-10-27,3,6.4
2020-10-27,4,7.0
2020-10-27,5,7.3
2020-10-27,6,7.7
2020-10-27,7,8.1
2020-10-27,8,8.6
2020-10-27,9,9.1
2020-10-27,10,9.9
2020-10-27,11,11.1
2020-10-27,12,11.5
2020-10-27,13,12.2
2020-10-27,14,12.9
2020-10-27,15,12.8
2020-10-27,16,12.4
2020-10-27,17,10.7
2020-10-27,18,9.4
2020-10-27,19,8.8
2020-10-27,20,8.1
2020-10-27,21,7.5
2020-10-27,22,7.7
2020-10-27,23,7.3
2020-10-28,0,7.4
2020-10-28,1,7.2
2020-10-28,2,7.0
2020-10-28,3,7.0
2020-10-28,4,7.1
2020-10-28,5,6.7
2020-10-28,6,6.7
2020-10-28,7,7.9
2020-10-28,8,9.8
2020-10-28,9,11.6
2020-10-28,10,11.8
2020-10-28,11,12.6
2020-10-28,12,11.7
2020-10-28,13,9.9
2020-10-28,14,9.3
2020-10-28,15,7.3
2020-10-28,16,7.7
2020-10-28,17,7.7
2020-10-28,18,7.7
2020-10-28,19,7.6
2020-10-28,20,7.4
2020-10-28,21,6.9
2020-10-28,22,6.5
2020-10-28,23,6.5
2020-10-29,0,6.4
2020-10-29,1,6.5
2020-10-29,2,6.7
2020-10-29,3,6.9
2020-10-29,4,7.3
2020-10-29,5,7.9
2020-10-29,6,8.6
2020-10-29,7,9.0
2020-10-29,8,9.4
2020-10-29,9,10.3
2020-10-29,10,11.2
2020-10-29,11,12.1
2020-10-29,12,12.8
2020-10-29,13,13.6
2020-10-29,14,13.6
2020-10-29,15,13.7
2020-10-29,16,13.8
2020-10-29,17,13.8
2020-10-29,18,13.7
2020-10-29,19,13.7
2020-10-29,20,13.9
2020-10-29,21,13.9
2020-10-29,22,14.1
2020-10-29,23,14.1
2020-10-30,0,14.3
2020-10-30,1,14.1
2020-10-30,2,   N/A
2020-10-30,3,   N/A
2020-10-30,4,   N/A
2020-10-30,5,   N/A
2020-10-30,6,   N/A
2020-10-30,7,14.5
2020-10-30,8,14.9
2020-10-30,9,15.0
2020-10-30,10,15.7
2020-10-30,11,15.6
2020-10-30,12,15.9
2020-10-30,13,15.9
2020-10-30,14,15.7
2020-10-30,15,15.3
2020-10-30,16,14.9
2020-10-30,17,14.3
2020-10-30,18,14.2
2020-10-30,19,14.2
2020-10-30,20,13.9
2020-10-30,21,13.9
2020-10-30,22,13.9
2020-10-30,23,13.9
2020-10-31,0,13.9
2020-10-31,1,13.9
2020-10-31,2,13.9
2020-10-31,3,13.8
2020-10-31,4,13.4
2020-10-31,5,13.4
2020-10-31,6,13.1
2020-10-31,7,13.2
2020-10-31,8,13.4
2020-10-31,9,14.4
2020-10-31,10,14.5
2020-10-31,11,13.6
2020-10-31,12,14.2
2020-10-31,13,14.1
2020-10-31,14,12.7
2020-10-31,15,12.5
2020-10-31,16,11.4
2020-10-31,17,10.9
2020-10-31,18,10.4
2020-10-31,19,9.6
2020-10-31,20,9.6
2020-10-31,21,9.8
2020-10-31,22,9.9
2020-10-31,23,9.6
2020-11-01,0,9.5
2020-11-01,1,10.0
2020-11-01,2,10.3
2020-11-01,3,10.7
2020-11-01,4,11.0
2020-11-01,5,11.0
2020-11-01,6,11.1
2020-11-01,7,12.4
2020-11-01,8,13.6
2020-11-01,9,15.0
2020-11-01,10,15.6
2020-11-01,11,15.2
2020-11-01,12,16.0
2020-11-01,13,14.9
2020-11-01,14,15.0
2020-11-01,15,14.8
2020-11-01,16,15.2
2020-11-01,17,16.3
2020-11-01,18,16.3
2020-11-01,19,16.5
2020-11-01,20,16.7
2020-11-01,21,16.7
2020-11-01,22,16.4
2020-11-01,23,16.3
2020-11-02,0,16.6
2020-11-02,1,16.4
2020-11-02,2,16.1
2020-11-02,3,16.4
2020-11-02,4,16.6
2020-11-02,5,15.7
2020-11-02,6,15.7
2020-11-02,7,15.9
2020-11-02,8,14.2
2020-11-02,9,13.3
2020-11-02,10,13.5
2020-11-02,11,13.7
2020-11-02,12,13.0
2020-11-02,13,12.5
2020-11-02,14,12.5
2020-11-02,15,10.6
2020-11-02,16,9.9
2020-11-02,17,9.2
2020-11-02,18,9.2
2020-11-02,19,8.7
2020-11-02,20,8.5
2020-11-02,21,7.5
2020-11-02,22,6.4
2020-11-02,23,5.6
2020-11-03,0,5.5
2020-11-03,1,5.6
2020-11-03,2,5.9
2020-11-03,3,6.3
2020-11-03,4,6.5
2020-11-03,5,6.6
2020-11-03,6,6.0
2020-11-03,7,5.5
2020-11-03,8,5.2
2020-11-03,9,6.8
2020-11-03,10,8.8
2020-11-03,11,8.8
2020-11-03,12,9.5
2020-11-03,13,10.0
2020-11-03,14,9.9
2020-11-03,15,10.0
2020-11-03,16,8.5
2020-11-03,17,6.6
2020-11-03,18,6.1
2020-11-03,19,4.8
2020-11-03,20,3.9
2020-11-03,21,3.8
2020-11-03,22,3.2
2020-11-03,23,2.6
2020-11-04,0,2.3
2020-11-04,1,1.9
2020-11-04,2,1.4
2020-11-04,3,1.1
2020-11-04,4,0.3
2020-11-04,5,0.3
2020-11-04,6,-0.3
2020-11-04,7,0.5
2020-11-04,8,2.3
2020-11-04,9,4.9
2020-11-04,10,7.1
2020-11-04,11,9.6
2020-11-04,12,10.9
2020-11-04,13,11.1
2020-11-04,14,11.1
2020-11-04,15,10.4
2020-11-04,16,9.0
2020-11-04,17,7.4
2020-11-04,18,6.5
2020-11-04,19,4.3
2020-11-04,20,2.8
2020-11-04,21,1.9
2020-11-04,22,2.2
2020-11-04,23,1.4
2020-11-05,0,0.5
2020-11-05,1,1.4
2020-11-05,2,0.9
2020-11-05,3,1.5
2020-11-05,4,1.5
2020-11-05,5,1.6
2020-11-05,6,1.2
2020-11-05,7,1.5
2020-11-05,8,2.6
2020-11-05,9,3.7
2020-11-05,10,5.1
2020-11-05,11,7.1
2020-11-05,12,8.5
2020-11-05,13,9.7
2020-11-05,14,10.8
2020-11-05,15,10.9
2020-11-05,16,8.7
2020-11-05,17,7.4
2020-11-05,18,4.9
2020-11-05,19,4.0
2020-11-05,20,3.5
2020-11-05,21,2.9
2020-11-05,22,2.4
2020-11-05,23,2.1
2020-11-06,0,1.7
2020-11-06,1,1.5
2020-11-06,2,1.3
2020-11-06,3,0.4
2020-11-06,4,-0.2
2020-11-06,5,0.2
2020-11-06,6,0.5
2020-11-06,7,0.0
2020-11-06,8,0.8
2020-11-06,9,3.0
2020-11-06,10,4.5
2020-11-06,11,9.3
2020-11-06,12,10.1
2020-11-06,13,10.7
2020-11-06,14,11.2
2020-11-06,15,11.3
2020-11-06,16,9.8
2020-11-06,17,7.1
2020-11-06,18,5.6
2020-11-06,19,6.9
2020-11-06,20,7.3
2020-11-06,21,6.4
2020-11-06,22,6.0
2020-11-06,23,5.9
2020-11-07,0,4.9
2020-11-07,1,4.0
2020-11-07,2,2.8
2020-11-07,3,2.5
2020-11-07,4,2.0
2020-11-07,5,2.1
2020-11-07,6,2.3
2020-11-07,7,2.7
2020-11-07,8,4.8
2020-11-07,9,8.8
2020-11-07,10,11.5
2020-11-07,11,12.6
2020-11-07,12,13.5
2020-11-07,13,13.5
2020-11-07,14,13.9
2020-11-07,15,13.4
2020-11-07,16,11.2
2020-11-07,17,9.6
2020-11-07,18,9.7
2020-11-07,19,9.2
2020-11-07,20,9.5
2020-11-07,21,9.0
2020-11-07,22,9.5
2020-11-07,23,9.3
2020-11-08,0,9.5
2020-11-08,1,9.5
2020-11-08,2,9.2
2020-11-08,3,9.3
2020-11-08,4,9.4
2020-11-08,5,9.3
2020-11-08,6,9.2
2020-11-08,7,9.5
2020-11-08,8,10.7
2020-11-08,9,11.8
2020-11-08,10,12.9
2020-11-08,11,13.1
2020-11-08,12,13.1
2020-11-08,13,14.0
2020-11-08,14,14.0
2020-11-08,15,13.8
2020-11-08,16,13.2
2020-11-08,17,12.6
2020-11-08,18,12.7
2020-11-08,19,12.7
2020-11-08,20,12.7
2020-11-08,21,12.5
2020-11-08,22,12.2
2020-11-08,23,12.0
2020-11-09,0,11.9
2020-11-09,1,11.5
2020-11-09,2,11.1
2020-11-09,3,11.3
2020-11-09,4,11.6
2020-11-09,5,11.4
2020-11-09,6,11.1
2020-11-09,7,10.6
2020-11-09,8,11.8
2020-11-09,9,12.7
2020-11-09,10,12.9
2020-11-09,11,14.0
2020-11-09,12,14.7
2020-11-09,13,15.0
2020-11-09,14,14.8
2020-11-09,15,14.6
2020-11-09,16,14.2
2020-11-09,17,14.0
2020-11-09,18,13.2
2020-11-09,19,13.1
2020-11-09,20,13.1
2020-11-09,21,12.9
2020-11-09,22,12.9
2020-11-09,23,12.5
2020-11-10,0,12.0
2020-11-10,1,11.4
2020-11-10,2,11.0
2020-11-10,3,10.7
2020-11-10,4,11.0
2020-11-10,5,11.0
2020-11-10,6,10.8
2020-11-10,7,10.9
2020-11-10,8,11.7
2020-11-10,9,12.5
2020-11-10,10,13.0
2020-11-10,11,13.6
2020-11-10,12,13.8
2020-11-10,13,15.0
2020-11-10,14,15.0
2020-11-10,15,14.7
2020-11-10,16,12.1
2020-11-10,17,11.7
2020-11-10,18,11.7
2020-11-10,19,11.4
2020-11-10,20,10.8
2020-11-10,21,10.0
2020-11-10,22,23.9
2020-11-10,23,20.3
2020-11-11,0,19.2
2020-11-11,1,8.7
2020-11-11,2,9.0
2020-11-11,3,9.3
2020-11-11,4,9.5
2020-11-11,5,10.0
2020-11-11,6,10.1
2020-11-11,7,10.2
2020-11-11,8,10.8
2020-11-11,9,11.7
2020-11-11,10,12.0
2020-11-11,11,12.4
2020-11-11,12,12.7
2020-11-11,13,12.9
2020-11-11,14,12.6
2020-11-11,15,12.3
2020-11-11,16,12.1
2020-11-11,17,12.2
2020-11-11,18,12.4
2020-11-11,19,12.5
2020-11-11,20,12.5
2020-11-11,21,12.4
2020-11-11,22,11.8
2020-11-11,23,11.0
2020-11-12,0,9.6
2020-11-12,1,9.3
2020-11-12,2,8.9
2020-11-12,3,8.9
2020-11-12,4,9.0
2020-11-12,5,9.0
2020-11-12,6,8.0
2020-11-12,7,7.8
2020-11-12,8,8.8
2020-11-12,9,10.0
2020-11-12,10,10.9
2020-11-12,11,11.4
2020-11-12,12,12.5
2020-11-12,13,12.9
2020-11-12,14,12.5
2020-11-12,15,12.2
2020-11-12,16,11.3
2020-11-12,17,10.0
2020-11-12,18,10.6
2020-11-12,19,10.6
2020-11-12,20,10.0
2020-11-12,21,10.2
2020-11-12,22,10.3
2020-11-12,23,10.5
2020-11-13,0,10.4
2020-11-13,1,11.0
2020-11-13,2,11.1
2020-11-13,3,11.1
2020-11-13,4,11.0
2020-11-13,5,11.0
2020-11-13,6,11.0
2020-11-13,7,11.4
2020-11-13,8,10.3
2020-11-13,9,10.7
2020-11-13,10,12.0
2020-11-13,11,11.8
2020-11-13,12,12.0
2020-11-13,13,11.8
2020-11-13,14,11.1
2020-11-13,15,10.5
2020-11-13,16,9.8
2020-11-13,17,9.5
2020-11-13,18,9.1
2020-11-13,19,8.8
2020-11-13,20,8.7
2020-11-13,21,8.1
2020-11-13,22,8.5
2020-11-13,23,8.9
2020-11-14,0,8.9
2020-11-14,1,8.8
2020-11-14,2,9.0
2020-11-14,3,8.6
2020-11-14,4,8.8
2020-11-14,5,9.9
2020-11-14,6,10.9
2020-11-14,7,11.8
2020-11-14,8,13.0
2020-11-14,9,13.2
2020-11-14,10,13.3
2020-11-14,11,13.2
2020-11-14,12,13.1
2020-11-14,13,13.0
2020-11-14,14,13.0
2020-11-14,15,12.9
2020-11-14,16,13.1
2020-11-14,17,13.2
2020-11-14,18,13.3
2020-11-14,19,12.9
2020-11-14,20,12.8
2020-11-14,21,12.5
2020-11-14,22,11.4
2020-11-14,23,11.3
2020-11-15,0,11.6
2020-11-15,1,   N/A
2020-11-15,2,   N/A
2020-11-15,3,   N/A
2020-11-15,4,   N/A
2020-11-15,5,   N/A
2020-11-15,6,   N/A
2020-11-15,7,   N/A
2020-11-15,8,   N/A
2020-11-15,9,11.1
2020-11-15,10,9.9
2020-11-15,11,10.7
2020-11-15,12,11.2
2020-11-15,13,11.3
2020-11-15,14,10.9
2020-11-15,15,9.9
2020-11-15,16,8.3
2020-11-15,17,7.7
2020-11-15,18,7.0
2020-11-15,19,7.5
2020-11-15,20,7.6
2020-11-15,21,7.3
2020-11-15,22,7.1
2020-11-15,23,7.3
2020-11-16,0,7.4
2020-11-16,1,8.6
2020-11-16,2,9.0
2020-11-16,3,9.5
2020-11-16,4,9.6
2020-11-16,5,9.5
2020-11-16,6,9.6
2020-11-16,7,9.1
2020-11-16,8,8.7
2020-11-16,9,8.8
2020-11-16,10,9.9
2020-11-16,11,12.1
2020-11-16,12,12.2
2020-11-16,13,11.0
2020-11-16,14,10.6
2020-11-16,15,10.6
2020-11-16,16,10.3
2020-11-16,17,10.2
2020-11-16,18,10.5
2020-11-16,19,10.9
2020-11-16,20,11.6
2020-11-16,21,12.0
2020-11-16,22,11.9
2020-11-16,23,12.1
2020-11-17,0,12.2
2020-11-17,1,12.4
2020-11-17,2,12.3
2020-11-17,3,12.0
2020-11-17,4,11.5
2020-11-17,5,11.6
2020-11-17,6,11.6
2020-11-17,7,11.6
2020-11-17,8,12.1
2020-11-17,9,12.3
2020-11-17,10,12.8
2020-11-17,11,13.4
2020-11-17,12,13.6
2020-11-17,13,13.6
2020-11-17,14,13.4
2020-11-17,15,13.1
2020-11-17,16,12.6
2020-11-17,17,12.7
2020-11-17,18,12.6
2020-11-17,19,12.5
2020-11-17,20,12.2
2020-11-17,21,12.0
2020-11-17,22,12.2
2020-11-17,23,12.3
2020-11-18,0,12.4
2020-11-18,1,12.4
2020-11-18,2,11.9
2020-11-18,3,11.8
2020-11-18,4,12.1
2020-11-18,5,12.0
2020-11-18,6,11.9
2020-11-18,7,11.7
2020-11-18,8,11.8
2020-11-18,9,12.3
2020-11-18,10,12.6
2020-11-18,11,13.1
2020-11-18,12,13.1
2020-11-18,13,13.1
2020-11-18,14,13.0
2020-11-18,15,13.4
2020-11-18,16,12.1
2020-11-18,17,10.6
2020-11-18,18,9.4
2020-11-18,19,8.2
2020-11-18,20,7.2
2020-11-18,21,6.6
2020-11-18,22,7.0
2020-11-18,23,7.3
2020-11-19,0,7.8
2020-11-19,1,7.6
2020-11-19,2,8.2
2020-11-19,3,9.0
2020-11-19,4,9.0
2020-11-19,5,8.8
2020-11-19,6,8.7
2020-11-19,7,8.7
2020-11-19,8,9.0
2020-11-19,9,9.5
2020-11-19,10,8.5
2020-11-19,11,7.7
2020-11-19,12,8.3
2020-11-19,13,8.6
2020-11-19,14,8.5
2020-11-19,15,7.8
2020-11-19,16,7.6
2020-11-19,17,6.2
2020-11-19,18,5.5
2020-11-19,19,3.1
2020-11-19,20,2.4
2020-11-19,21,2.0
2020-11-19,22,2.7
2020-11-19,23,2.5
2020-11-20,0,1.5
2020-11-20,1,1.3
2020-11-20,2,1.5
2020-11-20,3,1.4
2020-11-20,4,1.6
2020-11-20,5,1.6
2020-11-20,6,1.7
2020-11-20,7,2.8
2020-11-20,8,4.4
2020-11-20,9,5.7
2020-11-20,10,6.4
2020-11-20,11,7.1
2020-11-20,12,7.7
2020-11-20,13,8.1
2020-11-20,14,8.3
2020-11-20,15,8.5
2020-11-20,16,8.8
2020-11-20,17,9.1
2020-11-20,18,9.4
2020-11-20,19,9.6
2020-11-20,20,9.8
2020-11-20,21,9.9
2020-11-20,22,10.0
2020-11-20,23,9.9
2020-11-21,0,9.8
2020-11-21,1,9.7
2020-11-21,2,9.7
2020-11-21,3,9.6
2020-11-21,4,9.5
2020-11-21,5,9.3
2020-11-21,6,9.3
2020-11-21,7,9.7
2020-11-21,8,10.0
2020-11-21,9,10.5
2020-11-21,10,10.8
2020-11-21,11,11.1
2020-11-21,12,11.4
2020-11-21,13,11.2
2020-11-21,14,11.2
2020-11-21,15,10.9
2020-11-21,16,10.8
2020-11-21,17,10.9
2020-11-21,18,10.7
2020-11-21,19,10.7
2020-11-21,20,10.7
2020-11-21,21,10.7
2020-11-21,22,10.7
2020-11-21,23,10.8
2020-11-22,0,10.4
2020-11-22,1,9.9
2020-11-22,2,9.4
2020-11-22,3,8.3
2020-11-22,4,7.4
2020-11-22,5,6.6
2020-11-22,6,5.4
2020-11-22,7,5.6
2020-11-22,8,5.3
2020-11-22,9,7.4
2020-11-22,10,8.9
2020-11-22,11,9.8
2020-11-22,12,10.6
2020-11-22,13,10.3
2020-11-22,14,10.3
2020-11-22,15,8.4
2020-11-22,16,5.6
2020-11-22,17,5.7
2020-11-22,18,4.2
2020-11-22,19,3.3
2020-11-22,20,2.6
2020-11-22,21,2.8
2020-11-22,22,1.5
2020-11-22,23,1.6
2020-11-23,0,1.9
2020-11-23,1,1.5
2020-11-23,2,1.5
2020-11-23,3,1.5
2020-11-23,4,-0.3
2020-11-23,5,1.1
2020-11-23,6,0.3
2020-11-23,7,-0.3
2020-11-23,8,-0.3
2020-11-23,9,3.9
2020-11-23,10,6.1
2020-11-23,11,7.4
2020-11-23,12,9.0
2020-11-23,13,9.4
2020-11-23,14,9.3
2020-11-23,15,9.2
2020-11-23,16,8.1
2020-11-23,17,7.9
2020-11-23,18,8.1
2020-11-23,19,8.0
2020-11-23,20,7.7
2020-11-23,21,8.2
2020-11-23,22,9.0
2020-11-23,23,9.1
2020-11-24,0,9.7
2020-11-24,1,9.8
2020-11-24,2,9.8
2020-11-24,3,9.9
2020-11-24,4,10.0
2020-11-24,5,10.0
2020-11-24,6,9.7
2020-11-24,7,9.5
2020-11-24,8,9.9
2020-11-24,9,10.1
2020-11-24,10,10.1
2020-11-24,11,10.6
2020-11-24,12,12.0
2020-11-24,13,12.4
2020-11-24,14,10.5
2020-11-24,15,10.2
2020-11-24,16,9.8
2020-11-24,17,9.3
2020-11-24,18,8.9
2020-11-24,19,8.6
2020-11-24,20,8.7
2020-11-24,21,8.9
2020-11-24,22,9.0
2020-11-24,23,9.2
2020-11-25,0,9.2
2020-11-25,1,9.2
2020-11-25,2,9.9
2020-11-25,3,9.9
2020-11-25,4,9.9
2020-11-25,5,10.0
2020-11-25,6,10.4
2020-11-25,7,10.5
2020-11-25,8,10.8
2020-11-25,9,11.1
2020-11-25,10,11.4
2020-11-25,11,11.5
2020-11-25,12,8.1
2020-11-25,13,8.1
2020-11-25,14,8.1
2020-11-25,15,7.9
2020-11-25,16,7.4
2020-11-25,17,5.9
2020-11-25,18,5.5
2020-11-25,19,4.5
2020-11-25,20,4.1
2020-11-25,21,1.6
2020-11-25,22,1.2
2020-11-25,23,0.0
2020-11-26,0,-0.2
2020-11-26,1,-0.8
2020-11-26,2,-1.1
2020-11-26,3,-1.1
2020-11-26,4,-0.9
2020-11-26,5,-1.4
2020-11-26,6,-1.8
2020-11-26,7,-1.9
2020-11-26,8,-0.9
2020-11-26,9,0.6
2020-11-26,10,1.4
2020-11-26,11,3.0
2020-11-26,12,5.1
2020-11-26,13,6.9
2020-11-26,14,7.7
2020-11-26,15,5.9
2020-11-26,16,4.3
2020-11-26,17,2.1
2020-11-26,18,0.4
2020-11-26,19,-0.5
2020-11-26,20,-1.0
2020-11-26,21,-1.2
2020-11-26,22,-1.5
2020-11-26,23,-1.8
2020-11-27,0,-2.4
2020-11-27,1,-2.2
2020-11-27,2,-2.3
2020-11-27,3,-2.0
2020-11-27,4,-2.0
2020-11-27,5,-2.3
2020-11-27,6,-1.7
2020-11-27,7,-1.3
2020-11-27,8,-0.5
2020-11-27,9,0.5
2020-11-27,10,3.4
2020-11-27,11,3.7
2020-11-27,12,3.9
2020-11-27,13,4.2
2020-11-27,14,4.2
2020-11-27,15,3.9
2020-11-27,16,3.7
2020-11-27,17,3.8
2020-11-27,18,3.7
2020-11-27,19,3.7
2020-11-27,20,3.7
2020-11-27,21,3.0
2020-11-27,22,3.0
2020-11-27,23,3.0
2020-11-28,0,3.3
2020-11-28,1,3.4
2020-11-28,2,3.5
2020-11-28,3,3.8
2020-11-28,4,4.2
2020-11-28,5,4.6
2020-11-28,6,5.2
2020-11-28,7,6.1
2020-11-28,8,6.9
2020-11-28,9,7.3
2020-11-28,10,7.9
2020-11-28,11,8.2
2020-11-28,12,8.3
2020-11-28,13,8.3
2020-11-28,14,8.6
2020-11-28,15,8.7
2020-11-28,16,8.8
2020-11-28,17,8.9
2020-11-28,18,9.0
2020-11-28,19,9.1
2020-11-28,20,9.0
2020-11-28,21,8.8
2020-11-28,22,8.5
2020-11-28,23,8.2
2020-11-29,0,7.9
2020-11-29,1,7.6
2020-11-29,2,7.0
2020-11-29,3,6.9
2020-11-29,4,6.8
2020-11-29,5,6.5
2020-11-29,6,6.4
2020-11-29,7,6.1
2020-11-29,8,6.3
2020-11-29,9,6.4
2020-11-29,10,6.5
2020-11-29,11,6.6
2020-11-29,12,6.5
2020-11-29,13,6.5
2020-11-29,14,6.4
2020-11-29,15,6.1
2020-11-29,16,6.1
2020-11-29,17,6.1
2020-11-29,18,5.9
2020-11-29,19,5.7
2020-11-29,20,5.6
2020-11-29,21,5.7
2020-11-29,22,5.8
2020-11-29,23,5.9
2020-11-30,0,5.7
2020-11-30,1,5.7
2020-11-30,2,5.7
2020-11-30,3,5.6
2020-11-30,4,5.4
2020-11-30,5,5.5
2020-11-30,6,5.7
2020-11-30,7,5.9
2020-11-30,8,5.8
2020-11-30,9,5.9
2020-11-30,10,6.0
2020-11-30,11,6.5
2020-11-30,12,7.6
2020-11-30,13,8.2
2020-11-30,14,8.2
2020-11-30,15,8.4
2020-11-30,16,9.5
2020-11-30,17,9.6
2020-11-30,18,9.9
2020-11-30,19,9.9
2020-11-30,20,8.7
2020-11-30,21,7.0
2020-11-30,22,6.2
2020-11-30,23,5.3
2020-12-01,0,4.8
2020-12-01,1,4.1
2020-12-01,2,4.1
2020-12-01,3,3.7
2020-12-01,4,3.4
2020-12-01,5,3.1
2020-12-01,6,2.8
2020-12-01,7,2.7
2020-12-01,8,2.7
2020-12-01,9,3.9
2020-12-01,10,5.4
2020-12-01,11,6.4
2020-12-01,12,7.0
2020-12-01,13,7.1
2020-12-01,14,6.9
2020-12-01,15,5.9
2020-12-01,16,4.7
2020-12-01,17,3.6
2020-12-01,18,3.5
2020-12-01,19,3.1
2020-12-01,20,2.9
2020-12-01,21,2.4
2020-12-01,22,1.6
2020-12-01,23,1.2
2020-12-02,0,1.3
2020-12-02,1,1.5
2020-12-02,2,2.1
2020-12-02,3,2.2
2020-12-02,4,2.0
2020-12-02,5,1.8
2020-12-02,6,1.2
2020-12-02,7,1.5
2020-12-02,8,2.3
2020-12-02,9,3.2
2020-12-02,10,4.0
2020-12-02,11,4.7
2020-12-02,12,5.4
2020-12-02,13,5.5
2020-12-02,14,5.6
2020-12-02,15,5.6
2020-12-02,16,5.8
2020-12-02,17,5.8
2020-12-02,18,5.3
2020-12-02,19,4.9
2020-12-02,20,4.4
2020-12-02,21,3.3
2020-12-02,22,3.0
2020-12-02,23,2.7
2020-12-03,0,2.9
2020-12-03,1,3.5
2020-12-03,2,3.6
2020-12-03,3,3.7
2020-12-03,4,3.7
2020-12-03,5,3.8
2020-12-03,6,4.0
2020-12-03,7,4.3
2020-12-03,8,4.6
2020-12-03,9,4.9
2020-12-03,10,5.0
2020-12-03,11,5.2
2020-12-03,12,5.6
2020-12-03,13,5.7
2020-12-03,14,5.7
2020-12-03,15,5.5
2020-12-03,16,5.5
2020-12-03,17,5.4
2020-12-03,18,4.8
2020-12-03,19,4.8
2020-12-03,20,3.9
2020-12-03,21,3.4
2020-12-03,22,3.7
2020-12-03,23,3.4
2020-12-04,0,3.1
2020-12-04,1,2.9
2020-12-04,2,2.9
2020-12-04,3,2.6
2020-12-04,4,2.6
2020-12-04,5,2.5
2020-12-04,6,2.7
2020-12-04,7,2.9
2020-12-04,8,3.1
2020-12-04,9,4.0
2020-12-04,10,4.5
2020-12-04,11,4.7
2020-12-04,12,4.7
2020-12-04,13,   N/A
2020-12-04,14,   N/A
2020-12-04,15,5.0
2020-12-04,16,4.8
2020-12-04,17,4.6
2020-12-04,18,4.4
2020-12-04,19,3.9
2020-12-04,20,3.5
2020-12-04,21,3.5
2020-12-04,22,4.0
2020-12-04,23,4.1
2020-12-05,0,4.3
2020-12-05,1,4.5
2020-12-05,2,4.6
2020-12-05,3,4.8
2020-12-05,4,4.9
2020-12-05,5,4.9
2020-12-05,6,4.6
2020-12-05,7,3.9
2020-12-05,8,3.6
2020-12-05,9,5.4
2020-12-05,10,6.9
2020-12-05,11,7.1
2020-12-05,12,7.7
2020-12-05,13,7.3
2020-12-05,14,7.1
2020-12-05,15,5.7
2020-12-05,16,4.2
2020-12-05,17,3.8
2020-12-05,18,3.9
2020-12-05,19,3.7
2020-12-05,20,3.6
2020-12-05,21,2.6
2020-12-05,22,1.6
2020-12-05,23,2.3
2020-12-06,0,2.3
2020-12-06,1,1.9
2020-12-06,2,1.8
2020-12-06,3,1.7
2020-12-06,4,1.7
2020-12-06,5,1.7
2020-12-06,6,1.7
2020-12-06,7,-0.7
2020-12-06,8,-0.3
2020-12-06,9,-0.2
2020-12-06,10,0.4
2020-12-06,11,0.7
2020-12-06,12,1.2
2020-12-06,13,1.8
2020-12-06,14,1.9
2020-12-06,15,1.9
2020-12-06,16,1.4
2020-12-06,17,1.0
2020-12-06,18,0.5
2020-12-06,19,0.2
2020-12-06,20,-0.2
2020-12-06,21,-0.5
2020-12-06,22,-0.3
2020-12-06,23,-0.3
2020-12-07,0,-0.3
2020-12-07,1,-0.4
2020-12-07,2,-0.2
2020-12-07,3,0.4
2020-12-07,4,0.4
2020-12-07,5,0.3
2020-12-07,6,0.1
2020-12-07,7,-0.1
2020-12-07,8,-0.3
2020-12-07,9,-0.1
2020-12-07,10,0.1
2020-12-07,11,0.5
2020-12-07,12,0.7
2020-12-07,13,0.9
2020-12-07,14,1.1
2020-12-07,15,1.0
2020-12-07,16,0.5
2020-12-07,17,0.4
2020-12-07,18,0.5
2020-12-07,19,0.4
2020-12-07,20,0.3
2020-12-07,21,-0.1
2020-12-07,22,-0.3
2020-12-07,23,-0.5
2020-12-08,0,-0.5
2020-12-08,1,-0.8
2020-12-08,2,-1.1
2020-12-08,3,-1.2
2020-12-08,4,-1.5
2020-12-08,5,-1.4
2020-12-08,6,-1.4
2020-12-08,7,-1.4
2020-12-08,8,-1.4
2020-12-08,9,-0.9
2020-12-08,10,-0.5
2020-12-08,11,0.9
2020-12-08,12,2.1
2020-12-08,13,2.9
2020-12-08,14,2.9
2020-12-08,15,2.9
2020-12-08,16,2.8
2020-12-08,17,2.6
2020-12-08,18,2.9
2020-12-08,19,3.6
2020-12-08,20,3.9
2020-12-08,21,4.2
2020-12-08,22,4.4
2020-12-08,23,4.5
2020-12-09,0,4.4
2020-12-09,1,4.3
2020-12-09,2,4.2
2020-12-09,3,4.2
2020-12-09,4,3.9
2020-12-09,5,3.9
2020-12-09,6,4.1
2020-12-09,7,4.1
2020-12-09,8,4.1
2020-12-09,9,4.5
2020-12-09,10,4.8
2020-12-09,11,4.7
2020-12-09,12,4.7
2020-12-09,13,4.7
2020-12-09,14,4.5
2020-12-09,15,4.2
2020-12-09,16,4.2
2020-12-09,17,4.1
2020-12-09,18,4.1
2020-12-09,19,4.0
2020-12-09,20,4.3
2020-12-09,21,4.5
2020-12-09,22,5.2
2020-12-09,23,5.5
2020-12-10,0,5.5
2020-12-10,1,5.9
2020-12-10,2,6.1
2020-12-10,3,6.1
2020-12-10,4,6.1
2020-12-10,5,6.0
2020-12-10,6,5.7
2020-12-10,7,5.9
2020-12-10,8,6.0
2020-12-10,9,6.4
2020-12-10,10,6.5
2020-12-10,11,6.8
2020-12-10,12,7.1
2020-12-10,13,7.0
2020-12-10,14,6.8
2020-12-10,15,6.5
2020-12-10,16,6.5
2020-12-10,17,6.5
2020-12-10,18,6.4
2020-12-10,19,6.4
2020-12-10,20,6.6
2020-12-10,21,6.8
2020-12-10,22,6.8
2020-12-10,23,7.1
2020-12-11,0,7.3
2020-12-11,1,7.8
2020-12-11,2,8.0
2020-12-11,3,8.2
2020-12-11,4,8.3
2020-12-11,5,8.3
2020-12-11,6,7.9
2020-12-11,7,7.9
2020-12-11,8,7.3
2020-12-11,9,8.0
2020-12-11,10,8.8
2020-12-11,11,9.5
2020-12-11,12,9.7
2020-12-11,13,10.3
2020-12-11,14,10.8
2020-12-11,15,9.6
2020-12-11,16,8.4
2020-12-11,17,7.1
2020-12-11,18,6.1
2020-12-11,19,5.9
2020-12-11,20,5.3
2020-12-11,21,6.1
2020-12-11,22,6.2
2020-12-11,23,6.6
2020-12-12,0,6.6
2020-12-12,1,6.4
2020-12-12,2,6.1
2020-12-12,3,5.9
2020-12-12,4,6.4
2020-12-12,5,6.4
2020-12-12,6,6.4
2020-12-12,7,7.0
2020-12-12,8,7.5
2020-12-12,9,7.9
2020-12-12,10,8.0
2020-12-12,11,8.4
2020-12-12,12,8.4
2020-12-12,13,8.8
2020-12-12,14,9.3
2020-12-12,15,8.1
2020-12-12,16,6.4
2020-12-12,17,5.0
2020-12-12,18,4.6
2020-12-12,19,3.7
2020-12-12,20,2.9
2020-12-12,21,2.3
2020-12-12,22,2.2
2020-12-12,23,1.8
2020-12-13,0,1.7
2020-12-13,1,1.9
2020-12-13,2,0.7
2020-12-13,3,1.8
2020-12-13,4,2.6
2020-12-13,5,2.9
2020-12-13,6,3.8
2020-12-13,7,4.6
2020-12-13,8,5.0
2020-12-13,9,5.8
2020-12-13,10,6.4
2020-12-13,11,7.1
2020-12-13,12,7.8
2020-12-13,13,8.4
2020-12-13,14,8.9
2020-12-13,15,9.7
2020-12-13,16,10.3
2020-12-13,17,10.6
2020-12-13,18,11.2
2020-12-13,19,11.2
2020-12-13,20,10.7
2020-12-13,21,10.5
2020-12-13,22,10.5
2020-12-13,23,10.6
2020-12-14,0,10.5
2020-12-14,1,10.6
2020-12-14,2,10.8
2020-12-14,3,9.4
2020-12-14,4,9.2
2020-12-14,5,9.3
2020-12-14,6,9.1
2020-12-14,7,8.9
2020-12-14,8,9.2
2020-12-14,9,10.0
2020-12-14,10,11.1
2020-12-14,11,11.3
2020-12-14,12,11.3
2020-12-14,13,11.5
2020-12-14,14,10.9
2020-12-14,15,10.5
2020-12-14,16,10.3
2020-12-14,17,10.1
2020-12-14,18,10.1
2020-12-14,19,9.6
2020-12-14,20,9.1
2020-12-14,21,9.0
2020-12-14,22,8.9
2020-12-14,23,8.5
2020-12-15,0,8.0
2020-12-15,1,7.6
2020-12-15,2,7.8
2020-12-15,3,7.7
2020-12-15,4,7.4
2020-12-15,5,7.0
2020-12-15,6,6.9
2020-12-15,7,6.5
2020-12-15,8,6.9
2020-12-15,9,7.6
2020-12-15,10,9.2
2020-12-15,11,10.2
2020-12-15,12,10.6
2020-12-15,13,10.6
2020-12-15,14,10.6
2020-12-15,15,9.5
2020-12-15,16,8.7
2020-12-15,17,8.5
2020-12-15,18,8.0
2020-12-15,19,7.5
2020-12-15,20,7.3
2020-12-15,21,7.3
2020-12-15,22,7.4
2020-12-15,23,7.7
2020-12-16,0,7.6
2020-12-16,1,7.6
2020-12-16,2,8.2
2020-12-16,3,8.7
2020-12-16,4,8.7
2020-12-16,5,8.7
2020-12-16,6,8.9
2020-12-16,7,9.3
2020-12-16,8,9.4
2020-12-16,9,9.9
2020-12-16,10,9.7
2020-12-16,11,9.8
2020-12-16,12,9.9
2020-12-16,13,9.7
2020-12-16,14,9.4
2020-12-16,15,7.8
2020-12-16,16,7.5
2020-12-16,17,7.5
2020-12-16,18,7.0
2020-12-16,19,6.8
2020-12-16,20,6.9
2020-12-16,21,6.5
2020-12-16,22,6.7
2020-12-16,23,6.3
2020-12-17,0,6.8
2020-12-17,1,6.8
2020-12-17,2,6.4
2020-12-17,3,5.9
2020-12-17,4,5.8
2020-12-17,5,5.1
2020-12-17,6,4.8
2020-12-17,7,5.1
2020-12-17,8,4.9
2020-12-17,9,6.7
2020-12-17,10,8.1
2020-12-17,11,9.5
2020-12-17,12,10.4
2020-12-17,13,10.5
2020-12-17,14,10.0
2020-12-17,15,   N/A
2020-12-17,16,9.2
2020-12-17,17,9.3
2020-12-17,18,9.8
2020-12-17,19,9.9
2020-12-17,20,10.3
2020-12-17,21,10.4
2020-12-17,22,10.6
2020-12-17,23,10.9
2020-12-18,0,11.0
2020-12-18,1,10.9
2020-12-18,2,11.0
2020-12-18,3,10.8
2020-12-18,4,11.0
2020-12-18,5,10.8
2020-12-18,6,10.9
2020-12-18,7,11.0
2020-12-18,8,11.1
2020-12-18,9,11.4
2020-12-18,10,11.5
2020-12-18,11,11.7
2020-12-18,12,11.7
2020-12-18,13,11.6
2020-12-18,14,11.5
2020-12-18,15,11.8
2020-12-18,16,11.8
2020-12-18,17,12.0
2020-12-18,18,11.9
2020-12-18,19,12.1
2020-12-18,20,12.1
2020-12-18,21,12.1
2020-12-18,22,11.9
2020-12-18,23,11.6
2020-12-19,0,11.6
2020-12-19,1,11.6
2020-12-19,2,11.1
2020-12-19,3,10.0
2020-12-19,4,9.6
2020-12-19,5,9.9
2020-12-19,6,10.1
2020-12-19,7,10.0
2020-12-19,8,10.0
2020-12-19,9,9.6
2020-12-19,10,   N/A
2020-12-19,11,   N/A
2020-12-19,12,   N/A
2020-12-19,13,   N/A
2020-12-19,14,   N/A
2020-12-19,15,   N/A
2020-12-19,16,   N/A
2020-12-19,17,   N/A
2020-12-19,18,   N/A
2020-12-19,19,   N/A
2020-12-19,20,   N/A
2020-12-19,21,   N/A
2020-12-19,22,   N/A
2020-12-19,23,   N/A
2020-12-20,0,   N/A
2020-12-20,1,   N/A
2020-12-20,2,   N/A
2020-12-20,3,   N/A
2020-12-20,4,   N/A
2020-12-20,5,   N/A
2020-12-20,6,   N/A
2020-12-20,7,   N/A
2020-12-20,8,   N/A
2020-12-20,9,   N/A
2020-12-20,10,   N/A
2020-12-20,11,   N/A
2020-12-20,12,   N/A
2020-12-20,13,9.4
2020-12-20,14,8.8
2020-12-20,15,8.3
2020-12-20,16,6.7
2020-12-20,17,6.2
2020-12-20,18,5.6
2020-12-20,19,4.8
2020-12-20,20,4.7
2020-12-20,21,4.2
2020-12-20,22,4.2
2020-12-20,23,4.2
2020-12-21,0,5.6
2020-12-21,1,6.4
2020-12-21,2,7.0
2020-12-21,3,7.2
2020-12-21,4,7.5
2020-12-21,5,7.7
2020-12-21,6,8.1
2020-12-21,7,8.3
2020-12-21,8,9.0
2020-12-21,9,9.8
2020-12-21,10,10.8
2020-12-21,11,11.7
2020-12-21,12,12.5
2020-12-21,13,13.0
2020-12-21,14,13.1
2020-12-21,15,13.1
2020-12-21,16,13.0
2020-12-21,17,12.6
2020-12-21,18,12.6
2020-12-21,19,12.6
2020-12-21,20,12.5
2020-12-21,21,12.5
2020-12-21,22,12.4
2020-12-21,23,12.5
2020-12-22,0,12.4
2020-12-22,1,12.3
2020-12-22,2,12.0
2020-12-22,3,11.7
2020-12-22,4,9.2
2020-12-22,5,8.3
2020-12-22,6,8.3
2020-12-22,7,7.9
2020-12-22,8,7.5
2020-12-22,9,7.5
2020-12-22,10,7.8
2020-12-22,11,7.8
2020-12-22,12,7.9
2020-12-22,13,7.6
2020-12-22,14,7.4
2020-12-22,15,7.0
2020-12-22,16,6.7
2020-12-22,17,6.3
2020-12-22,18,6.4
2020-12-22,19,6.8
2020-12-22,20,7.3
2020-12-22,21,7.8
2020-12-22,22,8.0
2020-12-22,23,8.1
2020-12-23,0,9.7
2020-12-23,1,10.0
2020-12-23,2,10.1
2020-12-23,3,10.2
2020-12-23,4,10.3
2020-12-23,5,10.3
2020-12-23,6,10.4
2020-12-23,7,10.5
2020-12-23,8,10.6
2020-12-23,9,10.7
2020-12-23,10,10.8
2020-12-23,11,11.6
2020-12-23,12,11.7
2020-12-23,13,10.0
2020-12-23,14,9.5
2020-12-23,15,9.3
2020-12-23,16,8.9
2020-12-23,17,9.1
2020-12-23,18,9.0
2020-12-23,19,8.3
2020-12-23,20,7.0
2020-12-23,21,6.5
2020-12-23,22,6.0
2020-12-23,23,5.9
2020-12-24,0,5.9
2020-12-24,1,6.1
2020-12-24,2,6.0
2020-12-24,3,5.9
2020-12-24,4,5.5
2020-12-24,5,4.7
2020-12-24,6,3.8
2020-12-24,7,3.1
2020-12-24,8,2.0
2020-12-24,9,3.0
2020-12-24,10,3.7
2020-12-24,11,4.1
2020-12-24,12,4.2
2020-12-24,13,4.4
2020-12-24,14,4.1
2020-12-24,15,3.7
2020-12-24,16,3.1
2020-12-24,17,3.1
2020-12-24,18,2.7
2020-12-24,19,2.1
2020-12-24,20,2.2
2020-12-24,21,2.0
2020-12-24,22,1.6
2020-12-24,23,1.2
2020-12-25,0,0.9
2020-12-25,1,0.7
2020-12-25,2,0.3
2020-12-25,3,0.2
2020-12-25,4,-0.0
2020-12-25,5,-0.3
2020-12-25,6,-0.6
2020-12-25,7,-1.1
2020-12-25,8,-0.6
2020-12-25,9,1.0
2020-12-25,10,2.1
2020-12-25,11,2.6
2020-12-25,12,2.9
2020-12-25,13,3.1
2020-12-25,14,3.4
2020-12-25,15,2.3
2020-12-25,16,0.9
2020-12-25,17,0.7
2020-12-25,18,0.9
2020-12-25,19,0.8
2020-12-25,20,0.1
2020-12-25,21,0.2
2020-12-25,22,0.3
2020-12-25,23,0.4
2020-12-26,0,1.2
2020-12-26,1,2.0
2020-12-26,2,2.3
2020-12-26,3,2.9
2020-12-26,4,3.4
2020-12-26,5,4.0
2020-12-26,6,4.5
2020-12-26,7,4.8
2020-12-26,8,5.6
2020-12-26,9,5.7
2020-12-26,10,7.0
2020-12-26,11,7.9
2020-12-26,12,8.2
2020-12-26,13,8.3
2020-12-26,14,8.1
2020-12-26,15,8.3
2020-12-26,16,8.6
2020-12-26,17,8.8
2020-12-26,18,9.1
2020-12-26,19,9.2
2020-12-26,20,9.1
2020-12-26,21,8.8
2020-12-26,22,9.5
2020-12-26,23,9.4
2020-12-27,0,9.1
2020-12-27,1,8.9
2020-12-27,2,7.9
2020-12-27,3,7.7
2020-12-27,4,7.4
2020-12-27,5,6.8
2020-12-27,6,5.9
2020-12-27,7,5.6
2020-12-27,8,4.9
2020-12-27,9,5.1
2020-12-27,10,6.0
2020-12-27,11,6.2
2020-12-27,12,6.2
2020-12-27,13,6.4
2020-12-27,14,   N/A
2020-12-27,15,   N/A
2020-12-27,16,   N/A
2020-12-27,17,   N/A
2020-12-27,18,   N/A
2020-12-27,19,   N/A
2020-12-27,20,   N/A
2020-12-27,21,   N/A
2020-12-27,22,   N/A
2020-12-27,23,   N/A
2020-12-28,0,   N/A
2020-12-28,1,   N/A
2020-12-28,2,   N/A
2020-12-28,3,   N/A
2020-12-28,4,   N/A
2020-12-28,5,   N/A
2020-12-28,6,   N/A
2020-12-28,7,   N/A
2020-12-28,8,   N/A
2020-12-28,9,   N/A
2020-12-28,10,0.2
2020-12-28,11,0.1
2020-12-28,12,0.1
2020-12-28,13,0.9
2020-12-28,14,1.1
2020-12-28,15,1.4
2020-12-28,16,1.6
2020-12-28,17,1.5
2020-12-28,18,1.5
2020-12-28,19,1.6
2020-12-28,20,1.7
2020-12-28,21,1.7
2020-12-28,22,1.4
2020-12-28,23,0.9
2020-12-29,0,0.6
2020-12-29,1,0.3
2020-12-29,2,0.4
2020-12-29,3,0.9
2020-12-29,4,1.2
2020-12-29,5,1.2
2020-12-29,6,1.2
2020-12-29,7,1.1
2020-12-29,8,0.9
2020-12-29,9,1.0
2020-12-29,10,2.5
2020-12-29,11,2.7
2020-12-29,12,2.6
2020-12-29,13,2.5
2020-12-29,14,2.0
2020-12-29,15,2.8
2020-12-29,16,3.1
2020-12-29,17,3.0
2020-12-29,18,3.0
2020-12-29,19,2.3
2020-12-29,20,1.3
2020-12-29,21,0.9
2020-12-29,22,0.6
2020-12-29,23,0.0
2020-12-30,0,-0.1
2020-12-30,1,   N/A
2020-12-30,2,   N/A
2020-12-30,3,   N/A
2020-12-30,4,   N/A
2020-12-30,5,   N/A
2020-12-30,6,   N/A
2020-12-30,7,-0.3
2020-12-30,8,0.2
2020-12-30,9,0.6
2020-12-30,10,1.4
2020-12-30,11,2.0
2020-12-30,12,2.8
2020-12-30,13,3.0
2020-12-30,14,2.9
2020-12-30,15,2.5
2020-12-30,16,1.8
2020-12-30,17,0.7
2020-12-30,18,0.2
2020-12-30,19,-0.4
2020-12-30,20,-1.6
2020-12-30,21,-1.6
2020-12-30,22,-2.4
2020-12-30,23,-2.6
2020-12-31,0,-0.8
2020-12-31,1,-0.2
2020-12-31,2,-0.2
2020-12-31,3,-0.8
2020-12-31,4,-1.3
2020-12-31,5,-1.0
2020-12-31,6,-0.8
2020-12-31,7,-0.6
2020-12-31,8,-0.6
2020-12-31,9,-0.6
2020-12-31,10,-0.4
2020-12-31,11,-0.4
2020-12-31,12,0.3
2020-12-31,13,0.4
2020-12-31,14,0.5
2020-12-31,15,0.5
2020-12-31,16,-0.4
2020-12-31,17,-0.8
2020-12-31,18,-0.9
2020-12-31,19,-1.5
2020-12-31,20,-1.7
2020-12-31,21,-2.3
2020-12-31,22,-2.6
2020-12-31,23,-3.1
</pre>

<!-- partial -->
  <script>
Highcharts.chart('container', {

    data: {
        csv: document.getElementById('csv').innerHTML
    },

    chart: {
        type: 'heatmap',
        height: 500,
        backgroundColor: 'transparent'
    },
    legend: {
        enabled: true
    },
    plotOptions: {
        backgroundColor: 'transparent',
        area: {
            lineWidth: 1,
            fillOpacity: 1
        }
    },
    boost: {
        useGPUTranslations: true
    },

    title: {
        text: 'Steeple Claydon, UK',
        y: 5
    },

    subtitle: {
        text: 'Temperature variation by day and hour through 2020',
        y: 20
    },

    xAxis: {
        type: 'datetime',
        min: Date.UTC(2020, 0, 1),
        max: Date.UTC(2020, 11, 31, 23, 59, 59),
        labels: {
            y: 18,
            format: '{value:%b}'
        },
        showLastLabel: false,
        tickLength: 5,
        startOnTick: true,
    },

    yAxis: [{
        title: {
            text: null
        },
        labels: {
            format: '{value}:00'
        },
        minPadding: 0,
        maxPadding: 0,
        startOnTick: false,
        endOnTick: false,
        tickPositions: [0, 6, 12, 18, 24],
        lineColor: '#555',
        lineWidth: 1,       
        tickColor: '#555',
        tickWidth: 1,
        min: 0,
        max: 23,
        reversed: true
      }, {
        lineWidth: 1,
        opposite: true,
        title: {
            text: ''
        }
    }], 

    colorAxis: {
        stops: [
            [0, '#3060cf'],
            [0.5, '#fffbbc'],
            [0.9, '#c4463a'],
            [1, '#c4463a']
        ],
        min: -15,
        max: 25,
        startOnTick: false,
        endOnTick: false,
        tickWidth: 1,
        tickColor: '#555',
        tickLength: 5,
        labels: {
        y: 18,
        style: {
        color: 'silver'
      },
            format: '{value}'
        }
    },

    series: [{
        boostThreshold: 100,
        borderWidth: 0,
        nullColor: '#EFEFEF',
        colsize: 24 * 36e5, // one day
        tooltip: {
            headerFormat: 'Temperature<br/>',
            pointFormat: '{point.x:%e %b, %Y} {point.y}:00: <b>{point.value} ' +
                '</b>'
        },
        turboThreshold: Number.MAX_VALUE // #3404, remove after 4.0.5 release
    }],
    accessibility: {
    enabled: false
  },
  exporting: {
    enabled: false
  }

});

</script>

</body>
</html>
