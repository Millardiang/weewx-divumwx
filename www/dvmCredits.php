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

include('dvmCombinedData.php');
if ($theme === "dark")
{
    echo '<style>@font-face{font-family:weathertext2;src:url(css/fonts/verbatim-regular.woff) format("woff"),url(css/fonts/verbatim-regular.woff2) format("woff2"),url(css/fonts/verbatim-regular.ttf) format("truetype")}html,body{font-size:13px;font-family:"weathertext2",Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));grid-gap:5px;align-items:stretch;color:#f5f7fc}.grid>article{border:1px solid rgba(245,247,252,.02);box-shadow:2px 2px 6px 0 rgba(0,0,0,.6);padding:5px;font-size:.8em;-webkit-border-radius:4px;border-radius:4px;background:hsla(233,12%,13%,.5)}.grid>article img{max-width:100%}.grid>article rainsnow{vertical-align:bottom;float:right}.grid>article actualt{vertical-align:top;-webkit-border-radius:2px;border-radius:2px;background:0;font-family:Arial,Helvetica,sans-serif;padding:1px 3px 1px 3px;width:max-content;font-size:.9rem;color:#fff;align-items:center;justify-content:center;margin-bottom:10px;top:-2px;display:flex}.grid>article actualtn{vertical-align:top;-webkit-border-radius:2px;border-radius:2px;background:0;font-family:Arial,Helvetica,sans-serif;padding:1px 3px 1px 3px;width:max-content;font-size:.9rem;color:#fff;align-items:center;justify-content:center;margin-bottom:10px;top:-2px;display:flex}.grid>article tempicon{vertical-align:top;float:right;font-size:1.1em;margin-top:-20px;margin-right:20px}.grid>article .summarytext{font-size:.9em;color:#aaa;margin-bottom:0;height:50px;line-height:10px;font-family:Arial,Helvetica,sans-serif}a{color:#aaa;text-decoration:none}.divumwxdarkbrowser{position:relative;background:0;width:97%;height:30px;margin:auto;margin-top:-5px;margin-left:0;border-top-left-radius:5px;border-top-right-radius:5px;padding-top:10px}.divumwxdarkbrowser[url]:after{content:attr(url);color:#fff;font-size:14px;text-align:center;position:absolute;left:0;right:0;top:0;padding:4px 15px;margin:11px 10px 0 auto;font-family:arial;height:20px}blue{color:#01a4b4}orange{color:#009bb4}orange1{color:rgba(255,131,47,1)}green{color:#aaa}red{color:#f37867}red6{color:#d65b4a}value{color:#fff}yellow{color:#cc0}purple{color:#916392}smalluvunit{font-size:.6rem;font-family:Arial,Helvetica,system}.hitempy{position:relative;background:rgba(61,64,66,.5);color:#fff;font-size:12px;width:110px;padding:1px;-webit-border-radius:2px;border-radius:2px;margin-top:-44px;margin-left:72px;padding:2px;line-height:10px;font-size:9px}.svgimage{background:rgba(0,155,171,1);-webit-border-radius:2px;border-radius:2px}orange1{color:#aaa}.greydesc{color:#fff;margin-left:60px;margin-top:-35px;position:absolute;font-size:1em}.none{float:none;margin-top:10px;position:absolute}spantemp{font-size:.75em;color:#fff;font-family:weathertext2}.tempicon{position:relative;font-family:weathertext2;margin-top:4px;margin-left:125px}.uvforecast{font-size:.8rem;color:#fff;font-family:Arial,Helvetica;line-height:auto;margin-top:-15px;margin-bottom:5px}.storm{font-size:.8rem;color:silver;font-family:Arial,Helvetica;line-height:auto;margin-top:5px;margin-bottom:2px}.iconpos{margin-top:-4px;margin-bottom:0}bluer{color:#fff;border-radius:2px;padding:0 2px 0 2px;align-items:center;justify-content:center;background:rgba(0,155,180,.6)}bluet,blueu{background:#01a4b5}yellowt,yellowu{background:#e6a141}oranget,orangeu{background:#d05f2d}greent{background:#90b12a}greenu{background:#565f67}redt,redu{background:#cd5245}purplet,purpleu{background:rgba(151,88,190,.8)}bluet,yellowt,oranget,greent,redt,purplet{-webkit-border-radius:2px;border-radius:2px;padding:2px;height:.9rem}blueu,yellowu,orangeu,greenu,redu,purpleu{color:#fff;border-radius:2px;padding:0 3px 0 3px;align-items:center;justify-content:center}summary{font-size:.9em;color:#aaa;display:none}blue1{color:#009bb4}value{font-size:.95em;color:#aaa}valuer{color:#fff;font-size:.9em}thunder{font-size:.9em;color:#aaa}wind{color:#fff;font-size:.9em}.grid>article_small{border:1px solid rgba(245,247,252,.02);box-shadow:2px 2px 6px 0 rgba(0,0,0,.6);padding:5px;font-size:.8em;-webkit-border-radius:4px;border-radius:4px;background:hsla(233,12%,13%,.5)}
    </style>';
}
else if ($theme === "light")
{
    echo '<style>@font-face{font-family:weathertext2;src:url(css/fonts/verbatim-regular.woff) format("woff"),url(css/fonts/verbatim-regular.woff2) format("woff2"),url(css/fonts/verbatim-regular.ttf) format("truetype")}html,body{background-color:#fff;font-size:13px;font-family:"weathertext2",Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));grid-gap:5px;align-items:stretch;color:#f5f7fc}.grid>article{border:1px solid rgba(245,247,252,.02);box-shadow:2px 2px 6px 0 rgba(0,0,0,.6);padding:5px;font-size:.8em;-webkit-border-radius:4px;border-radius:4px;background:hsla(233,12%,13%,.5);background-color:#fff;color:#000}.grid>article img{max-width:100%}.grid>article rainsnow{vertical-align:bottom;float:right}.grid>article actualt{vertical-align:top;-webkit-border-radius:2px;border-radius:2px;background:0;font-family:Arial,Helvetica,sans-serif;padding:1px 3px 1px 3px;width:max-content;font-size:.9rem;color:#fff;align-items:center;justify-content:center;margin-bottom:10px;top:-2px;display:flex}.grid>article actualtn{vertical-align:top;-webkit-border-radius:2px;border-radius:2px;background:0;font-family:Arial,Helvetica,sans-serif;padding:1px 3px 1px 3px;width:max-content;font-size:.9rem;color:#fff;align-items:center;justify-content:center;margin-bottom:10px;top:-2px;display:flex}.grid>article tempicon{vertical-align:top;float:right;font-size:1.1em;margin-top:-20px;margin-right:20px}.grid>article .summarytext{font-size:.9em;color:#aaa;margin-bottom:0;height:50px;line-height:10px;font-family:Arial,Helvetica,sans-serif}a{color:#aaa;text-decoration:none}.divumwxdarkbrowser{position:relative;background:0;width:97%;height:30px;margin:auto;margin-top:-5px;margin-left:0;border-top-left-radius:5px;border-top-right-radius:5px;padding-top:10px}.divumwxdarkbrowser[url]:after{content:attr(url);color:#000;font-size:14px;text-align:center;position:absolute;left:0;right:0;top:0;padding:4px 15px;margin:11px 10px 0 auto;font-family:arial;height:20px}blue{color:#01a4b4}orange{color:#009bb4}orange1{color:rgba(255,131,47,1)}green{color:#aaa}red{color:#f37867}red6{color:#d65b4a}value{color:#fff}yellow{color:#cc0}purple{color:#916392}smalluvunit{font-size:.6rem;font-family:Arial,Helvetica,system}.hitempy{position:relative;background:rgba(61,64,66,.5);color:#fff;font-size:12px;width:110px;padding:1px;-webit-border-radius:2px;border-radius:2px;margin-top:-44px;margin-left:72px;padding:2px;line-height:10px;font-size:9px}.svgimage{background:rgba(0,155,171,1);-webit-border-radius:2px;border-radius:2px}orange1{color:#aaa}.greydesc{color:#000;margin-left:60px;margin-top:-35px;position:absolute;font-size:1em}.none{float:none;margin-top:10px;position:absolute}spantemp{font-size:.75em;color:#fff;font-family:weathertext2}.tempicon{position:relative;font-family:weathertext2;margin-top:4px;margin-left:125px}.uvforecast{font-size:.8rem;color:#000;font-family:Arial,Helvetica;line-height:auto;margin-top:-15px;margin-bottom:5px}.storm{font-size:.8rem;color:silver;font-family:Arial,Helvetica;line-height:auto;margin-top:5px;margin-bottom:2px}.iconpos{margin-top:-4px;margin-bottom:0}bluer{color:#fff;border-radius:2px;padding:0 2px 0 2px;align-items:center;justify-content:center;background:rgba(0,155,180,.6)}bluet,blueu{background:#01a4b5}yellowt,yellowu{background:#e6a141}oranget,orangeu{background:#d05f2d}greent{background:#90b12a}greenu{background:#565f67}redt,redu{background:#cd5245}purplet,purpleu{background:rgba(151,88,190,.8)}bluet,yellowt,oranget,greent,redt,purplet{-webkit-border-radius:2px;border-radius:2px;padding:2px;height:.9rem}blueu,yellowu,orangeu,greenu,redu,purpleu{color:#fff;border-radius:2px;padding:0 3px 0 3px;align-items:center;justify-content:center}summary{font-size:.9em;color:#aaa;display:none}blue1{color:#009bb4}value{font-size:.95em;color:#aaa}valuer{color:#000;font-size:.9em}thunder{font-size:.9em;color:#aaa}wind{color:#000;font-size:.9em}.gridw{display:grid;grid-template-columns:repeat(auto-fill,minmax(600px,1fr));grid-gap:5px;align-items:stretch;color:#f5f7fc}.gridw>articlew{border:1px solid rgba(245,247,252,.02);box-shadow:2px 2px 6px 0 rgba(0,0,0,.6);padding:5px;font-size:.8em;-webkit-border-radius:4px;border-radius:4px;background:hsla(233,12%,13%,.5);background-color:#fff;color:#000}
    </style>';
}

date_default_timezone_set($TZ);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Credits</title>
<style>
div.dvmCredits {
  font-family: Arial, Helvetica, sans-serif;
  width: 100%;
  text-align: left;
  border-collapse: collapse;
}
.divTable.dvmCredits .divTableCell, .divTable.dvmCredits .divTableHead {
  padding: 3px 2px;
}
.divTable.dvmCredits .divTableBody .divTableCell {
  font-size: 13px;
}
.divTable.dvmCredits .divTableHeading .divTableHead {
  font-size: 15px;
  font-weight: bold;
  color: var(--col-6);
}
.divTable.dvmCredits .divTableHeading .divTableHead:first-child {
  /*border-left: none;*/
}

.dvmCredits .tableFootStyle {
  font-size: 14px;
  font-weight: bold;
  color: var(--col-6);
}
.dvmCredits .tableFootStyle {
  font-size: 14px;
}
.dvmCredits .tableFootStyle .links {
	 text-align: right;
}
.dvmCredits .tableFootStyle .links a{
  display: inline-block;
  color: var(--col-6);
  padding: 2px 8px;
  border-radius: 5px;
}
.dvmCredits.outerTableFooter {
  border-top: none;
}
.dvmCredits.outerTableFooter .tableFootStyle {
  padding: 3px 5px; 
}
.divTable{ display: table; }
.divTableRow { display: table-row; }
.divTableHeading { display: table-header-group;}
.divTableCell, .divTableHead { display: table-cell;}
.divTableHeading { display: table-header-group;}
.divTableFoot { display: table-footer-group;}
.divTableBody { display: table-row-group;}
</style>
</head>
<body>
<div class="divTable dvmCredits">
<div class="divTableBody">
<div class="divTableRow">
<div class="divTableCell">Tom Keffer, Matthew Wall, Gary Roderick</div>
<div class="divTableCell">Thank you for WeeWX and the many extensions it has spawned, no other application quite like it, all time given freely and with utmost patience</div>
</div>
<div class="divTableRow">
<div class="divTableCell">Sean Balfour and Steven Sheeley</div>
<div class="divTableCell">Thank you for everything guys, without you both this would not have happened</div>
</div>
<div class="divTableRow">
<div class="divTableCell">Jerry Dietrich</div>
<div class="divTableCell">Your help in the earlier days of weewx-weather34. Your Highchart and Web Services work is alive and kicking in DivumWX</div>
</div>
<div class="divTableRow">
<div class="divTableCell">Ken True of saratoga-weather.org</div>
<div class="divTableCell">Words of wisdom and an inspiration to us all</div>
</div>
<div class="divTableRow">
<div class="divTableCell">Vaisala XWeather</div>
<div class="divTableCell">Weather forecasts and alerts</div>
</div>
<div class="divTableRow">
<div class="divTableCell">Bas Milius</div>
<div class="divTableCell">Meteocons animated weather icons</div>
</div>
<div class="divTableRow">
<div class="divTableCell">YR</div>
<div class="divTableCell">yr.no forecast data for the meteogram</div>
</div>
<div class="divTableRow">
<div class="divTableCell">Highcharts</div>
<div class="divTableCell">Non commercial and development use of their excellent charting application</div>
</div>
<div class="divTableRow">
<div class="divTableCell">Mike</div>
<div class="divTableCell">Setting up test servers allowing us to work through some ideas</div>
</div>
<div class="divTableRow">
<div class="divTableCell">Mike, Chris, Ray and many others</div>
<div class="divTableCell">Willingness to test early versions although sometimes they crashed and burned</div>
</div>
<div class="divTableRow">
<div class="divTableCell">Brian Underdown</div>
<div class="divTableCell">The originator of Weather34 who donated the original code which has now evolved into DivumWX</div>
</div>
<div class="divTableRow">
<div class="divTableCell">Everyone else who I have met along the way</div>
<div class="divTableCell">Too many other people to mention in this community who have helped me along the way. Thank you all</div>
</div>
</div>
</div>


</body>
</html> 