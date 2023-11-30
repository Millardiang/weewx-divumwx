<?php
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


echo "$"; echo "weewxrt[0] = "; echo $weewxrt[0]; echo " = date </br>";
echo "$"; echo "weewxrt[1] = "; echo $weewxrt[1]; echo " = time </br>";
echo "$"; echo "weewxrt[2] = "; echo $weewxrt[2]; echo " = outside temperature </br>";
echo "$"; echo "weewxrt[3] = "; echo $weewxrt[3]; echo " = outside humidity </br>";
echo "$"; echo "weewxrt[4] = "; echo $weewxrt[4]; echo " = dewpoint </br>";
echo "$"; echo "weewxrt[5] = "; echo $weewxrt[5]; echo " = wind speed average </br>";
echo "$"; echo "weewxrt[6] = "; echo $weewxrt[6]; echo " = wind speed </br>";
echo "$"; echo "weewxrt[7] = "; echo $weewxrt[7]; echo " = wind direction </br>";
echo "$"; echo "weewxrt[8] = "; echo $weewxrt[8]; echo " = rain rate </br>";
echo "$"; echo "weewxrt[9] = "; echo $weewxrt[9]; echo " = day rain total </br>";
echo "$"; echo "weewxrt[10] = "; echo $weewxrt[10]; echo " = barometer </br>";
echo "$"; echo "weewxrt[11] = "; echo $weewxrt[11]; echo " = wind direction ordinates </br>";
echo "$"; echo "weewxrt[12] = "; echo $weewxrt[12]; echo " = wind Beaufort scale</br>";
echo "$"; echo "weewxrt[13] = "; echo $weewxrt[13]; echo " = wind unit</br>";
echo "$"; echo "weewxrt[14] = "; echo $weewxrt[14]; echo " = temperature unit</br>";
echo "$"; echo "weewxrt[15] = "; echo $weewxrt[15]; echo " = barometer unit</br>";
echo "$"; echo "weewxrt[16] = "; echo $weewxrt[16]; echo " = rain unit</br>";
echo "$"; echo "weewxrt[17] = "; echo $weewxrt[17]; echo " = day wind run </br>";
echo "$"; echo "weewxrt[18] = "; echo $weewxrt[18]; echo " = barometer trend </br>";
echo "$"; echo "weewxrt[19] = "; echo $weewxrt[19]; echo " = month rain total </br>";
echo "$"; echo "weewxrt[20] = "; echo $weewxrt[20]; echo " = year rain total</br>";
echo "$"; echo "weewxrt[21] = "; echo $weewxrt[21]; echo " = yesterday rain total</br>";
echo "$"; echo "weewxrt[22] = "; echo $weewxrt[22]; echo " = inndoor temperature </br>";
echo "$"; echo "weewxrt[23] = "; echo $weewxrt[23]; echo " = indoor humidity </br>";
echo "$"; echo "weewxrt[24] = "; echo $weewxrt[24]; echo " = windchill </br>";
echo "$"; echo "weewxrt[25] = "; echo $weewxrt[25]; echo " = temperature trend </br>";
echo "$"; echo "weewxrt[26] = "; echo $weewxrt[26]; echo " = day outside temperature maximum </br>";
echo "$"; echo "weewxrt[27] = "; echo $weewxrt[27]; echo " = day outside temperature maximum time </br>";
echo "$"; echo "weewxrt[28] = "; echo $weewxrt[28]; echo " = day outside temperature minimum </br>";
echo "$"; echo "weewxrt[29] = "; echo $weewxrt[29]; echo " = day outside temperature time </br>";
echo "$"; echo "weewxrt[30] = "; echo $weewxrt[30]; echo " = day wind speed maximum </br>";
echo "$"; echo "weewxrt[31] = "; echo $weewxrt[31]; echo " = day wind speed maximum time </br>";
echo "$"; echo "weewxrt[32] = "; echo $weewxrt[32]; echo " = day wind gust maximum </br>";
echo "$"; echo "weewxrt[33] = "; echo $weewxrt[33]; echo " = day wind gust maximum time </br>";
echo "$"; echo "weewxrt[34] = "; echo $weewxrt[34]; echo " = day barometer maximum </br>";
echo "$"; echo "weewxrt[35] = "; echo $weewxrt[35]; echo " = day barometer maximum time </br>";
echo "$"; echo "weewxrt[36] = "; echo $weewxrt[36]; echo " = day barometer minimum </br>";
echo "$"; echo "weewxrt[37] = "; echo $weewxrt[37]; echo " = day barometer minimum time </br>";
echo "$"; echo "weewxrt[38] = "; echo $weewxrt[38]; echo " = WeeWX version </br>";
echo "$"; echo "weewxrt[39] = "; echo $weewxrt[39]; echo " = 0 </br>";
echo "$"; echo "weewxrt[40] = "; echo $weewxrt[40]; echo " = 10minute wind gust maximum </br>";
echo "$"; echo "weewxrt[41] = "; echo $weewxrt[41]; echo " = heatindex </br>";
echo "$"; echo "weewxrt[42] = "; echo $weewxrt[42]; echo " = humidex </br>";
echo "$"; echo "weewxrt[43] = "; echo $weewxrt[43]; echo " = UV </br>";
echo "$"; echo "weewxrt[44] = "; echo $weewxrt[44]; echo " = day evapotranspiration </br>";
echo "$"; echo "weewxrt[45] = "; echo $weewxrt[45]; echo " = radiation </br>";
echo "$"; echo "weewxrt[46] = "; echo $weewxrt[46]; echo " = 10minute wind direction average</br>";
echo "$"; echo "weewxrt[47] = "; echo $weewxrt[47]; echo " = hour rain total</br>";
echo "$"; echo "weewxrt[48] = "; echo $weewxrt[48]; echo " = Zambretti code </br>";
echo "$"; echo "weewxrt[49] = "; echo $weewxrt[49]; echo " = is it daylight </br>";
echo "$"; echo "weewxrt[50] = "; echo $weewxrt[50]; echo " = lost sensor contact </br>";
echo "$"; echo "weewxrt[51] = "; echo $weewxrt[51]; echo " = day wind direction average </br>";
echo "$"; echo "weewxrt[52] = "; echo $weewxrt[52]; echo " = cloudbase </br>";
echo "$"; echo "weewxrt[53] = "; echo $weewxrt[53]; echo " = cloudbase units </br>";
echo "$"; echo "weewxrt[54] = "; echo $weewxrt[54]; echo " = apparent temperature </br>";
echo "$"; echo "weewxrt[55] = "; echo $weewxrt[55]; echo " = sunshine_hours </br>";
echo "$"; echo "weewxrt[56] = "; echo $weewxrt[56]; echo " = day solar radiation maximum </br>";
echo "$"; echo "weewxrt[57] = "; echo $weewxrt[57]; echo " = last lightning distance </br>";
echo "$"; echo "weewxrt[58] = "; echo $weewxrt[58]; echo " = lightning energy </br>";
echo "$"; echo "weewxrt[59] = "; echo $weewxrt[59]; echo " = lightning strike count </br>";
echo "$"; echo "weewxrt[60] = "; echo $weewxrt[60]; echo " = lightning noise count </br>";
echo "$"; echo "weewxrt[61] = "; echo $weewxrt[61]; echo " = lightning disturbance count </br>";
echo "$"; echo "weewxrt[62] = "; echo $weewxrt[62]; echo " = 10minute wind gust average </br>";
echo "$"; echo "weewxrt[63] = "; echo $weewxrt[63]; echo " = storm rain </br>";
echo "$"; echo "weewxrt[64] = "; echo $weewxrt[64]; echo " = wind gust </br>"

?>