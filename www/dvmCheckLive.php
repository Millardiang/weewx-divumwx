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

$livedata = "serverdata/dvmRealtime.txt"; 
$weewxrt = array_map(function ($v)
{
    if ($v == 'NULL')
    {
        return null;
    }
    return $v;
}
,
explode(" ", file_get_contents($livedata)));
    

    //general
    $rain["alltime_total"] = $adata['alltime']['rain total']['value'];
    $year = substr($weewxrt[0], 6);
                if (isset($weewxrt[23]))
        {
            $weewxrt[23] = (float)(1 * $weewxrt[23]);
            $weewxrt[23] = number_format((float)$weewxrt[23], 0, '.', '');
        }

?>

<html>
<style>
table.darkTable {
  font-family: Arial, Helvetica, sans-serif;
  border: 2px solid #1F2225;
  background-color: #4A4A4A;
  width: 100%;
  height: 200px;
  text-align: left;
  border-collapse: collapse;
}
table.darkTable td, table.darkTable th {
  border: 1px solid #4A4A4A;
  padding: 2px 1px;
}
table.darkTable tbody td {
  font-size: 11px;
  color: silver;
}
table.darkTable tr:nth-child(even) {
  background: #5D5D5D;
}
table.darkTable thead {
  background: #1F2225;
  border-bottom: 3px solid #1F2225;
}
table.darkTable thead th {
  font-size: 14px;
  font-weight: normal;
  color: silver;
  text-align: center;
  border-left: 2px solid #4A4A4A;
}
table.darkTable thead th:first-child {
  border-left: none;
}

table.darkTable tfoot td {
  font-size: 12px;
}
</style>
                              
<table class="darkTable">
<thead><tr><th colspan="3">Check List of dvmRealtime.txt Generated PHP Variables</th></tr></thead>                              
<thead><tr><th>Variable Description</th><th>Variable Name</th><th>Current Value</th></tr></thead>
<tbody>
<tr><td>Date</td><td>$weewxrt[0]</td><td><?php echo $weewxrt[0];?></td></tr>
<tr><td>Time</td><td>$weewxrt[1]</td><td><?php echo $weewxrt[1];?></td></tr>
<tr><td>Outside temperature</td><td>$weewxrt[2]</td><td><?php echo $weewxrt[2];?></td></tr>
<tr><td>Outside humidity</td><td>$weewxrt[3]</td><td><?php echo $weewxrt[3];?></td></tr>
<tr><td>Dewpoint</td><td>$weewxrt[4]</td><td><?php echo $weewxrt[4];?></td></tr>
<tr><td>Wind speed average</td><td>$weewxrt[5]</td><td><?php echo $weewxrt[5];?></td></tr>
<tr><td>Wind speed</td><td>$weewxrt[6]</td><td><?php echo $weewxrt[6];?></td></tr>
<tr><td>Wind direction</td><td>$weewxrt[7]</td><td><?php echo $weewxrt[7];?></td></tr>
<tr><td>Rain rate</td><td>$weewxrt[8]</td><td><?php echo $weewxrt[8];?></td></tr>
<tr><td>Rain day total</td><td>$weewxrt[9]</td><td><?php echo $weewxrt[9];?></td></tr>
<tr><td>Barometer</td><td>$weewxrt[10]</td><td><?php echo $weewxrt[10];?></td></tr>
<tr><td>Wind direction ordinates</td><td>$weewxrt[11]</td><td><?php echo $weewxrt[11];?></td></tr>
<tr><td>Wind Beaufort scale</td><td>$weewxrt[12]</td><td><?php echo $weewxrt[12];?></td></tr>
<tr><td>Wind unit</td><td>$weewxrt[13]</td><td><?php echo $weewxrt[13];?></td></tr>
<tr><td>Temperature unit</td><td>$weewxrt[14]</td><td><?php echo $weewxrt[14];?></td></tr>
<tr><td>Barometer unit</td><td>$weewxrt[15]</td><td><?php echo $weewxrt[15];?></td></tr>
<tr><td>Rain unit</td><td>$weewxrt[16]</td><td><?php echo $weewxrt[16];?></td></tr>
<tr><td>Windrun day</td><td>$weewxrt[17]</td><td><?php echo $weewxrt[17];?></td></tr>
<tr><td>Barometer trend</td><td>$weewxrt[18]</td><td><?php echo $weewxrt[18];?></td></tr>
<tr><td>Rain month total</td><td>$weewxrt[19]</td><td><?php echo $weewxrt[19];?></td></tr>
<tr><td>Rain year total</td><td>$weewxrt[20]</td><td><?php echo $weewxrt[20];?></td></tr>
<tr><td>Rain yesterday total</td><td>$weewxrt[21]</td><td><?php echo $weewxrt[21];?></td></tr>
<tr><td>Indoor temperature</td><td>$weewxrt[22]</td><td><?php echo $weewxrt[22];?></td></tr>
<tr><td>Indoor humidity</td><td>$weewxrt[23]</td><td><?php echo $weewxrt[23];?></td></tr>
<tr><td>Windchill</td><td>$weewxrt[24]</td><td><?php echo $weewxrt[24];?></td></tr>
<tr><td>Temperature trend</td><td>$weewxrt[25]</td><td><?php echo $weewxrt[25];?></td></tr>
<tr><td>Outside temperature day maximum</td><td>$weewxrt[26]</td><td><?php echo $weewxrt[26];?></td></tr>
<tr><td>Outside temperature day maximum time</td><td>$weewxrt[27]</td><td><?php echo $weewxrt[27];?></td></tr>
<tr><td>Outside temperature day minimum</td><td>$weewxrt[28]</td><td><?php echo $weewxrt[28];?></td></tr>
<tr><td>Outside temperature day minimum time</td><td>$weewxrt[29]</td><td><?php echo $weewxrt[29];?></td></tr>
<tr><td>Wind speed day maximum</td><td>$weewxrt[30]</td><td><?php echo $weewxrt[30];?></td></tr>
<tr><td>Wind speed day maximum time</td><td>$weewxrt[31]</td><td><?php echo $weewxrt[31];?></td></tr>
<tr><td>Wind gust day maximum</td><td>$weewxrt[32]</td><td><?php echo $weewxrt[32];?></td></tr>
<tr><td>Wind gust day maximum time</td><td>$weewxrt[33]</td><td><?php echo $weewxrt[33];?></td></tr>
<tr><td>Barometer day maximum</td><td>$weewxrt[34]</td><td><?php echo $weewxrt[34];?></td></tr>
<tr><td>Barometer day maximum time</td><td>$weewxrt[35]</td><td><?php echo $weewxrt[35];?></td></tr>
<tr><td>Barometer day minimum</td><td>$weewxrt[36]</td><td><?php echo $weewxrt[36];?></td></tr>
<tr><td>Barometer day minimum time</td><td>$weewxrt[37]</td><td><?php echo $weewxrt[37];?></td></tr>
<tr><td>WeeWX version</td><td>$weewxrt[38]</td><td><?php echo $weewxrt[38];?></td></tr>
<tr><td>---</td><td>$weewxrt[39]</td><td><?php echo $weewxrt[39];?></td></tr>
<tr><td>Wind gust 10min maximum</td><td>$weewxrt[40]</td><td><?php echo $weewxrt[40];?></td></tr>
<tr><td>Heatindex</td><td>$weewxrt[41]</td><td><?php echo $weewxrt[41];?></td></tr>
<tr><td>Humidex</td><td>$weewxrt[42]</td><td><?php echo $weewxrt[42];?></td></tr>
<tr><td>UVI</td><td>$weewxrt[43]</td><td><?php echo $weewxrt[43];?></td></tr>
<tr><td>Evapotranspiration day</td><td>$weewxrt[44]</td><td><?php echo $weewxrt[44];?></td></tr>
<tr><td>Solar radiation</td><td>$weewxrt[45]</td><td><?php echo $weewxrt[45];?></td></tr>
<tr><td>Wind direction 10min average</td><td>$weewxrt[46]</td><td><?php echo $weewxrt[46];?></td></tr>
<tr><td>Rain hour total</td><td>$weewxrt[47]</td><td><?php echo $weewxrt[47];?></td></tr>
<tr><td>Zambretti code</td><td>$weewxrt[48]</td><td><?php echo $weewxrt[48];?></td></tr>
<tr><td>Is it daylight</td><td>$weewxrt[49]</td><td><?php echo $weewxrt[49];?></td></tr>
<tr><td>Lost sensor contact</td><td>$weewxrt[50]</td><td><?php echo $weewxrt[50];?></td></tr>
<tr><td>Wind direction day average</td><td>$weewxrt[51]</td><td><?php echo $weewxrt[51];?></td></tr>
<tr><td>Cloud base</td><td>$weewxrt[52]</td><td><?php echo $weewxrt[52];?></td></tr>
<tr><td>Cloud base units</td><td>$weewxrt[53]</td><td><?php echo $weewxrt[53];?></td></tr>
<tr><td>Apparent temperature</td><td>$weewxrt[54]</td><td><?php echo $weewxrt[54];?></td></tr>
<tr><td>Sunshine hours</td><td>$weewxrt[55]</td><td><?php echo $weewxrt[55];?></td></tr>
<tr><td>Solar radiation day maximum</td><td>$weewxrt[56]</td><td><?php echo $weewxrt[56];?></td></tr>
<tr><td>Lightning last distance</td><td>$weewxrt[57]</td><td><?php echo $weewxrt[57];?></td></tr>
<tr><td>Lightning energy</td><td>$weewxrt[58]</td><td><?php echo $weewxrt[58];?></td></tr>
<tr><td>Lightning strike count</td><td>$weewxrt[59]</td><td><?php echo $weewxrt[59];?></td></tr>
<tr><td>Lightning noise count</td><td>$weewxrt[60]</td><td><?php echo $weewxrt[60];?></td></tr>
<tr><td>Lightning disturbance count</td><td>$weewxrt[61]</td><td><?php echo $weewxrt[61];?></td></tr>
<tr><td>Wind gust 10min average</td><td>$weewxrt[62]</td><td><?php echo $weewxrt[62];?></td></tr>
<tr><td>Storm rain</td><td>$weewxrt[63]</td><td><?php echo $weewxrt[63];?></td></tr>
<tr><td>Wind gust</td><td>$weewxrt[64]</td><td><?php echo $weewxrt[64];?></td></tr>
</table>
</html>
