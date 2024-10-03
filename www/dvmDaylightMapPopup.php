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
#####################################################################################################################                                                                                                        #
#                                                                                                                   #
# Copyright (c) 2023 by Paul Noble  (https://codepen.io/paulnoble/pen/qZaNRB)                                       #
#                                                                                                                   #
# Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated      #
# documentation files (the "Software"), to deal in the Software without restriction, including without limitation   #
# the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and  #
# to permit persons to whom the Software is furnished to do so, subject to the following conditions:                #
#                                                                                                                   #
# The above copyright notice and this permission notice shall be included in all copies or substantial portions of  #
# the Software.                                                                                                     #
#                                                                                                                   #
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO  #
# THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE    #
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF         #
# CONTRACT,                                                                                                         #
# TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE    #
# SOFTWARE.                                                                                                         #
#                                                                                                                   #
#####################################################################################################################

include('dvmCombinedData.php');
error_reporting(0);
date_default_timezone_set($TZ);
header('Content-type: text/html; charset=utf-8');
if($theme === "light"){ echo "<body style='background-color:#FFFFFF'>";}
else if($theme === "dark"){ echo "<body style='background-color:#292E35'>";}
?>

<!DOCTYPE html>
<html>
  <head>
  <meta charset="UTF-8">
  <title>World daylight map</title>
  <script src="https://use.typekit.net/vff2oqo.js"></script>
<script>try{Typekit.load({ async: true });}catch(e){}</script><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
<link rel="stylesheet" href="css/daylightmap.css">

</head>
<body>
<div style="position:relative; top: 30px; left: 0px;">
<?php
if($theme === "light"){echo "<font color='black'>"."World Daylight Map"."</font>";}
else if($theme === "dark"){echo "<font color='silver'>"."World Daylight Map"."</font>";}
?>
</div>
<!-- partial:index.partial.html -->
<!--h1>World daylight map</h1>
<p>Equirectangular projection rendered with SVG</p-->
<div class="container" style="top: 80px;">

    <svg id="daylight-map"></svg>
    <div class="controls">
        <!--p class="curr-time">
            <a class="js-skip" data-skip="-60" href="#">
                &lsaquo;
            </a>
            <span></span>
            <a class="js-skip" data-skip="60" href="#">
                &rsaquo;
            </a>
        </p-->
        <!--p class="curr-date">
            <a class="js-skip big-jump" data-skip="-43200" href="#">
                &laquo;
            </a>
            <a class="js-skip" data-skip="-1440" href="#">
                &lsaquo;
            </a>
            <span></span>
            <a class="js-skip" data-skip="1440" href="#">
                &rsaquo;
            </a>
            <a class="js-skip big-jump" data-skip="43200" href="#">
                &raquo;
            </a>
        </p-->
    </div>
</div>

<!--p class="animate"><a href="#" class="js-animate"></a></p-->

<!--p class="credit">Using
    <a href="https://d3js.org/">D3.js</a>,
    <a href="https://github.com/mbostock/d3/wiki/Geo-Projections">Geocode Projections</a>,
    <a href="https://github.com/mbostock/topojson">Topojson</a>,
    <a href="https://github.com/mourner/suncalc">Suncalc</a>,
    <a href="https://www.maxmind.com/en/free-world-cities-database">MaxMind cities</a>,
    <a href="https://momentjs.com/">Moment.js</a>
</p-->
<!-- partial -->
<script src='https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.5/d3.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/topojson/1.6.20/topojson.min.js '></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/d3-geo-projection/0.2.16/d3.geo.projection.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.12.0/moment.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.1/moment-timezone.min.js'></script>
<script src='https://s3-us-west-2.amazonaws.com/s.cdpn.io/215059/suncalc.js'></script><script  src="js/daylightMap.js"></script>

</body>
</html>
