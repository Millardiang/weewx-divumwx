<?php
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

include ('dvmCombinedData.php');
$iconset = "icon2";
if ($theme === "dark")
{
    echo '<style>.demo{border:0 solid #aaa;border-collapse:collapse;padding:50px;font-family:arial,helvetica,verdana,sans-serif;font-size:10px;margin-bottom:50px;margin-top:50px margin-left:50%;margin-right:-50%;width:100%;color:silver}.demo th{border-bottom:.5px solid #aaa;/*! border-top:1px solid #aaa; */
 padding:5px;color:silver}.demo td{border:0 solid #aaa;padding:0;background:0;text-align:center}.demo td#CELL1{background-color:transparent;color:#000}.demo td#CELL2{background-color:#9cff9c}.demo td#CELL3{background-color:#31ff00}.demo td#CELL4{background-color:#31cf00}.demo td#CELL5{background-color:#ff0}.demo td#CELL6{background-color:#ffcf00}.demo td#CELL7{background-color:#ff9a00}.demo td#CELL8{background-color:#ff6464}.demo td#CELL9{background-color:red;color:#fff}.demo td#CELL10{background-color:#900;color:#fff}.demo td#CELL11{background-color:#ce30ff;color:#fff}img{margin-left:10px;margin-right:10px;width:60px;background-color:transparent}.alert-row{display:flex;flex-direction:row;height:120px;padding:10px 0;background-color:whitesmoke}.alert-row-narrow{display:flex;flex-direction:row;height:60px;padding:10px 0;background-color:whitesmoke;font-size:12px}.alert-row-info{display:flex;flex-direction:row;height:120px;padding:10px 0;background-color:whitesmoke}.alert-text-container{font-family:Arial,Helvetica,sans-serif;font-size:11px;display:flex;flex-direction:column;justify-content:center;margin-right:10px;color:#000}.alert-text-container-narrow{font-family:Arial,Helvetica,sans-serif;font-size:14px;display:flex;flex-direction:column;justify-content:center;margin-right:10px}body{background-image:url();margin-left:8px;margin-top:8px;margin-right:8px;margin-bottom:8px;font-family:Verdana,Arial,Helvetica,sans-serif;font-size:11px;color:#fff;font-weight:400;background-color:rgba(33,34,39,.8)}html{margin:0;padding:0}a:link{color:#fff}a:visited{color:#fff}a:hover{color:#fff}a:active{color:#fff}.LegendText2{font-family:Verdana,Arial,Helvetica,sans-serif;font-size:11px;color:#fff;font-weight:400}.Ebene3Header{font-family:Verdana,Arial,Helvetica,sans-serif;font-size:11px}table{font-size:11px;vertical-align:bottom;width:auto}.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));grid-gap:5px;align-items:stretch;color:#f5f7fc;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.grid>article{border:1px solid #212428;box-shadow:2px 2px 6px 0 rgba(0,0,0,.3);padding:5px;font-size:.8em;-webkit-border-radius:4px;border-radius:4px;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.grid1{display:grid;grid-template-columns:repeat(auto-fill,minmax(100%,1fr));grid-gap:5px;color:#000}.grid2{display:grid;grid-template-columns:repeat(auto-fill,minmax(100%,1fr));grid-gap:0;color:#000}.grid3{display:grid;grid-template-columns:repeat(auto-fill,minmax(100%,1fr));grid-gap:20px;color:#000}.grid1>articlegraph{border:1px solid rgba(245,247,252,.02);box-shadow:2px 2px 6px 0 rgba(0,0,0,.6);padding:5px;font-size:.8em;-webkit-border-radius:4px;border-radius:4px;background:#fff;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;width:790px;height:100%}.grid2>articlegraph2{border:1px solid rgba(245,247,252,.02);box-shadow:2px 2px 6px 0 rgba(0,0,0,.6);padding:5px;font-size:.8em;-webkit-border-radius:4px;border-radius:4px;background:#fff;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;width:790px;height:100%}.grid3>articlegraph3{border:1px solid rgba(245,247,252,.02);box-shadow:2px 2px 6px 0 rgba(0,0,0,.6);padding:5px;font-size:.8em;-webkit-border-radius:4px;border-radius:4px;background:#fff;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;width:790px;height:90%}.grid1>articlegraph_lg{border:1px solid rgba(245,247,252,.02);box-shadow:2px 2px 6px 0 rgba(0,0,0,.6);padding:5px;font-size:.8em;-webkit-border-radius:4px;border-radius:4px;background:lightgreen;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;width:790px;height:80px}.grid3>articlegraph3{border:1px solid rgba(245,247,252,.02);box-shadow:2px 2px 6px 0 rgba(0,0,0,.6);padding:5px;font-size:.8em;-webkit-border-radius:4px;border-radius:4px;background:0;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;width:790px;height:90%}.grid3>articlegraphText{border:1px solid rgba(245,247,252,.02);box-shadow:2px 2px 6px 0 rgba(0,0,0,.6);padding:5px;font-size:.8em;-webkit-border-radius:4px;border-radius:4px;background:#f5f7fc;color:#fff;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;width:790px;height:90%}.grid1>articlegraph_te{border:1px solid rgba(245,247,252,.02);box-shadow:2px 2px 6px 0 rgba(0,0,0,.6);padding:5px;font-size:.8em;-webkit-border-radius:4px;border-radius:4px;background-color:teal;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;width:790px;height:15px}.grid1>articlegraph_ye{border:1px solid rgba(245,247,252,.02);box-shadow:2px 2px 6px 0 rgba(0,0,0,.6);padding:5px;font-size:.8em;-webkit-border-radius:4px;border-radius:4px;background-color:yellow;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;width:790px;height:80px}.grid1>articlegraph_or{border:1px solid rgba(245,247,252,.02);box-shadow:2px 2px 6px 0 rgba(0,0,0,.6);padding:5px;font-size:.8em;-webkit-border-radius:4px;border-radius:4px;background-color:orange;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;width:790px;height:90px}.grid1>articlegraph_re{border:1px solid rgba(245,247,252,.02);box-shadow:2px 2px 6px 0 rgba(0,0,0,.6);padding:5px;font-size:.8em;-webkit-border-radius:4px;border-radius:4px;background-color:red;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;width:790px;height:100px}.grid_FT{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));grid-gap:1px;align-items:stretch;color:#f5f7fc;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.grid_FT>articlegraph_FT{border:1px solid rgba(245,247,252,.02);box-shadow:2px 2px 6px 0 rgba(0,0,0,.6);padding:5px;font-size:.8em;-webkit-border-radius:4px;border-radius:4px;color:#fff;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;width:790px;height:15px}.grid_MET{display:grid;grid-template-columns:repeat(auto-fill,minmax(100%,1fr));grid-gap:5px;color:#000}.grid_MET>articlegraph_MET{border:1px solid rgba(245,247,252,.02);box-shadow:2px 2px 6px 0 rgba(0,0,0,.6);padding:5px;font-size:.8em;-webkit-border-radius:4px;border-radius:4px;background:#fff;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;width:790px;height:90%}.divumwxdarkbrowser{position:relative;width:97%;height:30px;margin:auto;margin-top:-5px;margin-left:0;border-top-left-radius:5px;border-top-right-radius:5px;padding-top:10px;color:#fff}.divumwxdarkbrowser[url]:after{content:attr(url);font-size:14px;text-align:center;position:absolute;left:0;right:0;top:0;padding:4px 15px;margin:11px 10px 0 auto;font-family:arial;height:20px}.actualt{position:relative;left:5px;-webkit-border-radius:3px;-moz-border-radius:3px;-o-border-radius:3px;border-radius:3px;background:teal;padding:5px;font-family:Arial,Helvetica,sans-serif;width:790px;height:.8em;font-size:.8rem;padding-top:2px;color:#fff;border-bottom:2px solid rgba(56,56,60,1);align-items:center;justify-content:center;margin-bottom:0;top:0}blue{color:#01a4b4}orange{color:#009bb4}orange1{color:rgba(255,131,47,1)}green{color:#aaa}red{color:#f37867}red6{color:#d65b4a}value{color:#fff}yellow{color:#cc0}purple{color:#916392}.roundcornerframe{position:relative;width:790px;border-radius:5px;overflow:hidden;margin:0 auto 0 auto}.ol_time{margin-top:-15px;margin-right:6px;color:#d65b4a;font:700 10px arial,sans-serif;line-height:10px;float:right}.left{float:left;width:80px;padding-left:2px;height:160px;border:none}.right{float:left;width:80px;padding-right:2px;height:160px;border:none}.middle{float:left;width:140px;position:relative;height:160px;border:none}.2_high{height:80px;vertical-align:middle}.3_high{height:53px;vertical-align:middle}.4_high{height:40px;vertical-align:middle}.uv{color:#000}.webcamlarge{-webkit-border-radius:4px;-moz-border-radius:4px;-o-border-radius:4px;-ms-border-radius:4px;border-radius:4px;border:solid RGBA(84,85,86,1) 2px;width:98%;height:380px}
 </style>';
}
else if ($theme === "light")
{
    echo '<style>.demo{border:0 solid #aaa;border-collapse:collapse;padding:50px;font-family:arial,helvetica,verdana,sans-serif;font-size:10px;margin-bottom:50px;margin-top:50px margin-left:50%;margin-right:-50%;width:100%;color:#000}.demo th{border-bottom:1px solid #aaa;/*! border-top:1px solid #aaa; */
 padding:5px;color:#000}.demo td{border:0 solid #aaa;padding:0;background:#FFF;text-align:center}.demo td#CELL1{background-color:transparent;color:#000}.demo td#CELL2{background-color:#9cff9c}.demo td#CELL3{background-color:#31ff00}.demo td#CELL4{background-color:#31cf00}.demo td#CELL5{background-color:#ff0}.demo td#CELL6{background-color:#ffcf00}.demo td#CELL7{background-color:#ff9a00}.demo td#CELL8{background-color:#ff6464}.demo td#CELL9{background-color:red;color:#fff}.demo td#CELL10{background-color:#900;color:#fff}.demo td#CELL11{background-color:#ce30ff;color:#fff}img{margin-left:10px;margin-right:10px;width:60px;background-color:transparent}.alert-row{display:flex;flex-direction:row;height:120px;padding:10px 0;background-color:whitesmoke}.alert-row-narrow{display:flex;flex-direction:row;height:60px;padding:10px 0;background-color:whitesmoke;font-size:12px}.alert-row-info{display:flex;flex-direction:row;height:120px;padding:10px 0;background-color:whitesmoke}.alert-text-container{font-family:Arial,Helvetica,sans-serif;font-size:11px;display:flex;flex-direction:column;justify-content:center;margin-right:10px;color:#000}.alert-text-container-narrow{font-family:Arial,Helvetica,sans-serif;font-size:14px;display:flex;flex-direction:column;justify-content:center;margin-right:10px}body{background-image:url();margin-left:8px;margin-top:8px;margin-right:8px;margin-bottom:8px;font-family:Verdana,Arial,Helvetica,sans-serif;font-size:11px;color:#fff;font-weight:400;background-color:#fff}html{margin:0;padding:0}a:link{color:#000}a:visited{color:#000}a:hover{color:#000}a:active{color:#000}.LegendText2{font-family:Verdana,Arial,Helvetica,sans-serif;font-size:11px;color:#000;font-weight:400}.Ebene3Header{font-family:Verdana,Arial,Helvetica,sans-serif;font-size:11px}table{font-size:11px;vertical-align:bottom;width:auto}.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));grid-gap:5px;align-items:stretch;color:#f5f7fc;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.grid>article{border:1px solid #212428;box-shadow:2px 2px 6px 0 rgba(0,0,0,.3);padding:5px;font-size:.8em;-webkit-border-radius:4px;border-radius:4px;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.grid1{display:grid;grid-template-columns:repeat(auto-fill,minmax(100%,1fr));grid-gap:5px;color:#000}.grid2{display:grid;grid-template-columns:repeat(auto-fill,minmax(100%,1fr));grid-gap:0;color:#000}.grid3{display:grid;grid-template-columns:repeat(auto-fill,minmax(100%,1fr));grid-gap:20px;color:#000}.grid1>articlegraph{border:1px solid rgba(245,247,252,.02);box-shadow:2px 2px 6px 0 rgba(0,0,0,.6);padding:5px;font-size:.8em;-webkit-border-radius:4px;border-radius:4px;background:#fff;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;width:790px;height:100%}.grid2>articlegraph2{border:1px solid rgba(245,247,252,.02);box-shadow:2px 2px 6px 0 rgba(0,0,0,.6);padding:5px;font-size:.8em;-webkit-border-radius:4px;border-radius:4px;background:#fff;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;width:790px;height:100%}.grid3>articlegraph3{border:1px solid rgba(245,247,252,.02);box-shadow:2px 2px 6px 0 rgba(0,0,0,.6);padding:5px;font-size:.8em;-webkit-border-radius:4px;border-radius:4px;background:#fff;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;width:790px;height:90%}.grid3>articlegraph4{border:1px solid rgba(245,247,252,.02);box-shadow:2px 2px 6px 0 rgba(0,0,0,.6);padding:5px;font-size:.8em;-webkit-border-radius:4px;border-radius:4px;background:#fff;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;width:790px;height:90%}.grid1>articlegraph_lg{border:1px solid rgba(245,247,252,.02);box-shadow:2px 2px 6px 0 rgba(0,0,0,.6);padding:5px;font-size:.8em;-webkit-border-radius:4px;border-radius:4px;background:lightgreen;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;width:790px;height:80px}.grid1>articlegraph_te{border:1px solid rgba(245,247,252,.02);box-shadow:2px 2px 6px 0 rgba(0,0,0,.6);padding:5px;font-size:.8em;-webkit-border-radius:4px;border-radius:4px;background-color:teal;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;width:790px;height:15px}.grid1>articlegraph_ye{border:1px solid rgba(245,247,252,.02);box-shadow:2px 2px 6px 0 rgba(0,0,0,.6);padding:5px;font-size:.8em;-webkit-border-radius:4px;border-radius:4px;background-color:yellow;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;width:790px;height:80px}.grid1>articlegraph_or{border:1px solid rgba(245,247,252,.02);box-shadow:2px 2px 6px 0 rgba(0,0,0,.6);padding:5px;font-size:.8em;-webkit-border-radius:4px;border-radius:4px;background-color:orange;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;width:790px;height:90px}.grid1>articlegraph_re{border:1px solid rgba(245,247,252,.02);box-shadow:2px 2px 6px 0 rgba(0,0,0,.6);padding:5px;font-size:.8em;-webkit-border-radius:4px;border-radius:4px;background-color:red;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;width:790px;height:100px}.grid_FT{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));grid-gap:1px;align-items:stretch;color:#f5f7fc;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.grid_FT>articlegraph_FT{border:1px solid rgba(245,247,252,.02);box-shadow:2px 2px 6px 0 rgba(0,0,0,.6);padding:5px;font-size:.8em;-webkit-border-radius:4px;border-radius:4px;color:#000;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;width:790px;height:15px}.grid_MET{display:grid;grid-template-columns:repeat(auto-fill,minmax(100%,1fr));grid-gap:5px;color:#000}.grid_MET>articlegraph_MET{border:1px solid rgba(245,247,252,.02);box-shadow:2px 2px 6px 0 rgba(0,0,0,.6);padding:5px;font-size:.8em;-webkit-border-radius:4px;border-radius:4px;background:#fff;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;width:790px;height:90%}.divumwxdarkbrowser{position:relative;width:97%;height:30px;margin:auto;margin-top:-5px;margin-left:0;border-top-left-radius:5px;border-top-right-radius:5px;padding-top:10px;color:#000}.divumwxdarkbrowser[url]:after{content:attr(url);font-size:14px;text-align:center;position:absolute;left:0;right:0;top:0;padding:4px 15px;margin:11px 10px 0 auto;font-family:arial;height:20px}.actualt{position:relative;left:5px;-webkit-border-radius:3px;-moz-border-radius:3px;-o-border-radius:3px;border-radius:3px;background:teal;padding:5px;font-family:Arial,Helvetica,sans-serif;width:790px;height:.8em;font-size:.8rem;padding-top:2px;color:#fff;border-bottom:2px solid rgba(56,56,60,1);align-items:center;justify-content:center;margin-bottom:0;top:0}blue{color:#01a4b4}orange{color:#009bb4}orange1{color:rgba(255,131,47,1)}green{color:#aaa}red{color:#f37867}red6{color:#d65b4a}value{color:#fff}yellow{color:#cc0}purple{color:#916392}.roundcornerframe{position:relative;width:790px;border-radius:5px;overflow:hidden;margin:0 auto 0 auto}.webcamlarge{-webkit-border-radius:4px;-moz-border-radius:4px;-o-border-radius:4px;-ms-border-radius:4px;border-radius:4px;border:solid RGBA(84,85,86,1) 2px;width:97%;height:480px}
 </style>';
}
$forecastTime[0] = "0";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Day Night Forecast</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">


<?php
$forecastime = filemtime('jsondata/awd.txt');
$jsonIcon = 'jsondata/lookupTable.json';
$jsonIcon = file_get_contents($jsonIcon);
$parsed_icon = json_decode($jsonIcon, true);
$json = 'jsondata/awd.txt';
$json = file_get_contents($json);
$parsed_json = json_decode($json, true);
include('forecastSelect.php');
for ($k = 0;$k < 14;$k++)
{
    $pngicon[$k] = $parsed_json['response'][0]['periods'][$k]['icon'];
    $forecastIcon[$k] = $parsed_icon[$pngicon[$k]][$iconset];
    $Time[$k] = date("H", $parsed_json['response'][0]['periods'][$k]['timestamp']);
    $nameDay[$k] = date("l", $parsed_json['response'][0]['periods'][$k]['timestamp']);

        if ($Time[0] === "07")
    {
        $forecastTime[0] = "Today";
        $forecastTime[1] = "Tonight";
        $forecastTime[2] = "Tomorrow";
        $forecastTime[3] = "Tomorrow Night";
        $forecastTime[4] = $nameDay[4];
        $forecastTime[5] = $nameDay[5] . " Night";
        $forecastTime[6] = $nameDay[6];
        $forecastTime[7] = $nameDay[7] . " Night";
        $forecastTime[8] = $nameDay[8];
        $forecastTime[9] = $nameDay[9] . " Night";
        $forecastTime[10] = $nameDay[10];
        $forecastTime[11] = $nameDay[11] . " Night";
        $forecastTime[12] = $nameDay[12];
        $forecastTime[13] = $nameDay[13] . " Night";
        $forecastTime[14] = $nameDay[14];
        $forecastTime[15] = $nameDay[15] . " Night";
        $forecastTime[16] = $nameDay[16];
        $forecastTime[17] = $nameDay[17] . " Night";
        $forecastTime[18] = $nameDay[18];
        $forecastTime[19] = $nameDay[19] . " Night";
        $forecastTime[20] = $nameDay[20];
        $forecastTime[21] = $nameDay[21] . " Night";
        $forecastTime[22] = $nameDay[22];
    }
    else if ($Time[0] === "19")
    {
        $forecastTime[0] = "Tonight";
        $forecastTime[1] = "Tomorrow";
        $forecastTime[2] = "Tomorrow Night";
        $forecastTime[3] = $nameDay[3];
        $forecastTime[4] = $nameDay[4] . " Night";
        $forecastTime[5] = $nameDay[5];
        $forecastTime[6] = $nameDay[6] . " Night";
        $forecastTime[7] = $nameDay[7];
        $forecastTime[8] = $nameDay[8] . " Night";
        $forecastTime[9] = $nameDay[9];
        $forecastTime[10] = $nameDay[10] . " Night";
        $forecastTime[11] = $nameDay[11];
        $forecastTime[12] = $nameDay[12] . " Night";
        $forecastTime[13] = $nameDay[13];
        $forecastTime[14] = $nameDay[14] . " Night";
    }

    $forecastTempHigh[$k] = $parsed_json['response'][0]['periods'][$k]['maxTempC'];
    $forecastTempLow[$k] = $parsed_json['response'][0]['periods'][$k]['minTempC'];
    if ($forecastTempHigh[$k] === null)
    {
        $forecastTempHigh[$k] = $forecastTempLow[$k];
    }
    if ($windunit == 'kts') {
    $forecastWindSpeedMax[$k] = $parsed_json['response'][0]['periods'][$k]['windSpeedMaxKTS'];
    $forecastWindSpeedMin[$k] = $parsed_json['response'][0]['periods'][$k]['windSpeedMinKTS'];
    } else {
    $forecastWindSpeedMax[$k] = $parsed_json['response'][0]['periods'][$k]['windSpeedMaxKPH'];
    $forecastWindSpeedMin[$k] = $parsed_json['response'][0]['periods'][$k]['windSpeedMinKPH'];
    }
    $forecastWinddircardinal[$k] = $parsed_json['response'][0]['periods'][$k]['windDir'];
    $forecastprecipIntensity[$k] = $parsed_json['response'][0]['periods'][$k]['precipMM'];
    $forecastPrecipProb[$k] = $parsed_json['response'][0]['periods'][$k]['pop'];
    $forecastUV[$k] = $parsed_json['response'][0]['periods'][$k]['uvi'];
    $forecastsnow[$k] = $parsed_json['response'][0]['periods'][$k]['snowCM'];
    $forecastsummary[$k] = $parsed_json['response'][0]['periods'][$k]['weather'];
    $forecastnight[$k] = $parsed_json['response'][0]['periods'][$k]['isDay'];
    $forecastdesc[$k] = $parsed_json['response'][0]['periods'][$k]['weather'];
    $forecastheatindex[$k] = $parsed_json['response'][0]['periods'][$k]['avgFeelslikeC'];
    $forecasthumidity[$k] = $parsed_json['response'][0]['periods'][$k]['humidity'];
    if ($forecastUV[$k] === 0 or $forecastUV[$k] === null)
    {
        $forecastUV[$k] = "-";
        $colorUV[$k] = "<greyt>";
    }

    if ($tempunit == 'F')
    {
        $forecastTempHigh[$k] = round(($forecastTempHigh[$k] * 9 / 5) + 32, 0);
    }

    //heatindex
    if ($tempunit == 'F')
    {
        $forecastheatindex[$k] = round(($forecastheatindex[$k] * 9 / 5) + 32, 0);
    }

    //rain mm to in
    if ($forecastprecipIntensity[$k] === 0)
    {
        $forecastPrecip[$k] = "-";
    }
    else if ($rainunit == 'in')
    {
        $forecastprecipIntensity[$k] = round(($forecastprecipIntensity[$k] * 0.0393701) , 2);
    }
    else if ($rainunit == 'mm')
    {

        $forecastPrecip[$k] = "<bluet>" . $forecastprecipIntensity[$k] . $rainunit . " " . $forecastPrecipProb[$k] . "%";
    }

    //kmh to ms
    if ($windunit == 'm/s')
    {
        $forecastWindSpeedMax[$k] = round((number_format($forecastWindSpeedMax[$k], 1) * 0.277778) , 0);
        $forecastWindSpeedMin[$k] = round((number_format($forecastWindSpeedMin[$k], 1) * 0.277778) , 0);
    }
    //kmh to mph
    if ($windunit == 'mph')
    {
        $forecastWindSpeedMax[$k] = round((number_format($forecastWindSpeedMax[$k], 1) * 0.621371) , 0);
        $forecastWindSpeedMin[$k] = round((number_format($forecastWindSpeedMin[$k], 1) * 0.621371) , 0);
    }

    if ($forecastnight[$k] !== false)
    {
        $forecastnight[$k] = "D";
    }
    else $forecastnight[$k] = "N";

    if ($forecastUV[$k] > 10)
    {
        $colorUV[$k] = "<purplet>";
    }
    else if ($forecastUV[$k] > 7)
    {
        $colorUV[$k] = "<redt>";
    }
    else if ($forecastUV[$k] > 5)
    {
        $colorUV[$k] = "<oranget>";
    }
    else if ($forecastUV[$k] > 2)
    {
        $colorUV[$k] = "<yellowt>";
    }
    else if ($forecastUV[$k] > 0)
    {
        $colorUV[$k] = "<greent>";
    }

    if ($forecasthumidity[$k] >= 70)
    {
        $colorHumidity[$k] = "<bluet>";
    }
    else if ($forecasthumidity[$k] > 50)
    {
        $colorHumidity[$k] = "<yellowt>";
    }
    else if ($forecasthumidity[$k] > 40)
    {
        $colorHumidity[$k] = "<greent>";
    }
    else if ($forecasthumidity[$k] > 0)
    {
        $colorHumidity[$k] = "<redt>";
    }
    if ($tempunit == 'F' && $forecastTempHigh[$k] < 44.6)
    {
        $colorTempHigh[$k] = "<bluet>";
    }
    else if ($tempunit == 'F' && $forecastTempHigh[$k] > 80.6)
    {
        $colorTempHigh[$k] = "<redt>";
    }
    else if ($tempunit == 'F' && $forecastTempHigh[$k] > 64.4)
    {
        $colorTempHigh[$k] = "<oranget>";
    }
    else if ($tempunit == 'F' && $forecastTempHigh[$k] > 55)
    {
        $colorTempHigh[$k] = "<yellowt>";
    }
    else if ($tempunit == 'F' && $forecastTempHigh[$k] >= 44.6)
    {
        $colorTempHigh[$k] = "<greent>";
    }
    else if ($forecastTempHigh[$k] < 7)
    {
        $colorTempHigh[$k] = "<bluet>";
    }
    else if ($forecastTempHigh[$k] > 27)
    {
        $colorTempHigh[$k] = "<redt>";
    }
    else if ($forecastTempHigh[$k] > 19)
    {
        $colorTempHigh[$k] = "<oranget>";
    }
    else if ($forecastTempHigh[$k] > 12.7)
    {
        $colorTempHigh[$k] = "<yellowt>";
    }
    else if ($forecastTempHigh[$k] >= 7)
    {
        $colorTempHigh[$k] = "<greent>";
    }

    if ($forecastnight[$k] !== false)
    {
        $forecastnight[$k] = "D";
    }
    else $forecastnight[$k] = "N";

?>



 
<?php
} ?>



<main class="grid3" style="height:400px">
  <articlegraph3> 
<table class="demo" style="width: 100%; height: 100%; text-align: left; overflow: auto;">
    

<th style="font-size: 12px;">Period</th>
<th colspan="2" style="font-size: 12px;">Conditions</th>
<th style="font-size: 12px;">Temperature</th>
<th style="font-size: 12px;">Precipitation</th>
<th style="font-size: 12px;">Wind Speed</th>
<th style="font-size: 12px;">Direction</th>
<th style="font-size: 12px;">UV Index</th>
<th style="font-size: 12px;">Humidity</th>
</tr>
<?php for ($k = 0;$k < 14;$k++)
{ ?>
<tr style="border-top: 0.125px grey solid; ">
<td><span style="font-size: 11px;";><?php echo $forecastTime[$k]; ?></span></td>
<td><img src="img/meteocons/<?php echo $forecastIcon[$k]; ?>" width="45" height="30" alt="mc_day" style="display: block; margin-left: auto; margin-right: auto;"></td>
<td><span style="font-size: 11px;";><?php echo $forecastsummary[$k]; ?></span></td>
<td><span style="font-size: 11px;";><?php echo $colorTempHigh[$k]; ?><?php echo $forecastTempHigh[$k] . "&deg" . $tempunit; ?></span></td>
<td><span style="font-size: 11px; ";><?php echo $forecastPrecip[$k]; ?>
</td>
<td><span style="font-size: 11px; ";><redt><?php echo $forecastWindSpeedMin[$k] . "-" . $forecastWindSpeedMax[$k]; ?> <small><?php echo $windunit; ?></small></span></td>
<td><?php echo $forecastWinddircardinal[$k]; ?></td>
<td><span style="font-size: 11px; ";><?php echo $colorUV[$k]; ?><?php echo $forecastUV[$k]; ?></span></td>
<td><span style="font-size: 11px;";><?php echo $colorHumidity[$k]; ?><?php echo $forecasthumidity[$k]; ?><small> %</small></span></td>
</tr>
  <?php
} ?>


</table>
    </articlegraph3>

          
  
   </main>
