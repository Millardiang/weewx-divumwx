<?php
include ('fixedSettings.php');
include ('dvmShared.php');
include('common.php');
/* for future developments
$jsonR = 'jsondata/gauge-data.txt';
$real = file_get_contents($jsonR);
$realData = json_decode($real, true);
*/
error_reporting(0);
$weewxrt = array_map(function ($v)
{
if ($v == 'NULL')
{
    return null;
}
    return $v;
},
explode(" ", file_get_contents($livedata)));
    
//general
$region_city = explode("/", $TZ);
$region = $region_city[0];
$city = $region_city[1];
$ukWeatherHealth = "https://app.powerbi.com/view?r=eyJrIjoiZGI5MTA1NTEtZmE4NC00NTk3LTg5NjQtZjMyNDQ5YTgyMjI2IiwidCI6ImVlNGUxNDk5LTRhMzUtNGIyZS1hZDQ3LTVmM2NmOWRlODY2NiIsImMiOjh9";
$sundial_time = date("M d Y H:i:s",filemtime('serverdata/dvmRealtime.txt'));        
$stationlocation = "Queens Drive, Taunton";
$hardware = "Vantage Pro2";
$lat = 51.0;
$lon = -3.112;
$absLat = abs($lat);
$absLon = abs($lon);
if ($lat >= "0"){$NS = "North";}
else {$NS = "South";}
if ($lon >= "0") {$EW = "East";}
else {$EW = "West";}
$elevation = 36.88070833333333;
$url = "https://weather.mdmi.co.uk";
$divum["date"] = date($dateFormat, $divum["datetime"]);
$divum["time"] = date($timeFormat, $divum["datetime"]);
$divum["swversion"] = "5.0.2";
$divum["build"] = $weewxrt[38];
$divum["since"] = "1556346270";
$stationUptime = "15 days, 3 hours, 8 minutes";
$TS = time();
if($theme == 'dark') {$convertStyle = "color:";}
else {$convertStyle = "background:";}
$phpVersion = phpversion();

//air density
$air_density["now"] = 1.25250;
if(is_numeric($air_density["now"]) != 1){$air_density["now"] = 0;}
$air_density["day_max"] = number_format(1.25972,4);
$air_density["day_min"] = number_format(1.23002,4);
$air_density["day_avg"] = number_format(1.24434,4);
$air_density["day_maxtime"] = date('H:i',1713135900);
$air_density["day_mintime"] = date('H:i',1713187500);
$air_density["yesterday_max"] = number_format(1.27404,4);
$air_density["yesterday_min"] = number_format(1.23558,4);
$air_density["yesterday_avg"] = number_format(1.25660,4);
$air_density["yesterday_maxtime"] = date('H:i',1713073800);
$air_density["yesterday_mintime"] = date('H:i',1713100800);
$air_density["month_max"] = number_format(1.28418,4);
$air_density["month_min"] = number_format(1.19557,4);
$air_density["month_avg"] = number_format(1.23181,4);
$air_density["month_maxtime"] = date('j M H:i',1712901300);
$air_density["month_mintime"] = date('j M H:i',1712317800);
$air_density["year_max"] = number_format(1.33632,4);
$air_density["year_min"] = number_format(1.17625,4);
$air_density["year_avg"] = number_format(1.24954,4);
$air_density["year_maxtime"] = date('j M H:i',1705651500);
$air_density["year_mintime"] = date('j M H:i',1707481800);
$air_density["alltime_max"] = number_format(1.33632,4);
$air_density["alltime_min"] = number_format(1.17625,4);
$air_density["alltime_avg"] = number_format(1.24954,4);
$air_density["alltime_maxtime"] = date('j M Y H:i',1705651500);
$air_density["alltime_mintime"] = date('j M Y H:i',1707481800);

//air quality
$air["pm_units"] = "g/m<sup><b>3</b></sup>";
$air["current.pm2_5"] = 0.2;
$air["24h.rollingavg.pm2_5"] = 0.2;
$air["current.pm10_0"] = 1.6;
$air["24h.rollingavg.pm10_0"] = 1.6;


//almanac
$alm["sun_altitude"] = round(-22.431930960851194,2);
if ($alm["sun_altitude"] < 0)
{
$alm["sun_None"] = "<i>(Always down)</i>";
$alm["daylight_str"] = "00:00";
}
else
{
$alm["sun_None"] = "<i>(Always up)</i>";
$alm["daylight_str"] = "24:00";
}
$alm["sun_azimuth"] = 324.4832722206565;
$alm["moon_azimuth"] = 256.0313895663555;
$alm["sunrise"] = "06:16";
$alm["sunset"] = "20:09";
$alm["sunrise_date"] = "Apr 15 2024 06:16:25";
$alm["sunset_date"] = "Apr 15 2024 20:09:22";
$alm["sun_right_ascension"] = 24.500819344556234;
$alm["next_equinox"] = "22-Sep-2024 13:43";
$alm["next_solstice"] = "20-Jun-2024 21:51";
$alm["sidereal_time"] = 171.43583107791628;
$alm["civil_twilight_begin"] = "05:40";
$alm["civil_twilight_end"] = "20:45";
$alm["nautical_twilight_begin"] = "04:58";
$alm["nautical_twilight_end"] = "21:27";
$alm["astronomical_twilight_begin"] = "04:10";
$alm["astronomical_twilight_end"] = "22:16";
$alm["sun_meridian_transit"] = "13:12";
$alm["moon_meridian_transit"] = "19:30";
$alm["moonphase"] = "First quarter";
$alm["moonphase_number"] = 2;
$alm["moon_age"] = 7.152216238227707;
$alm["hour_sun"] = 2.5644997412036328;
$alm["hour_moon"] = 0.8898561329246748;
$alm["luminance"] = round(51.414295913974286,2);
$alm["fullmoon"] = "24 Apr 00:48";
$alm["newmoon"] = "08 May 04:21";   
$alm["daylight"] = "13:52";
$alm["moonrise"] = "10:36";
$alm["moonset"] = "03:36";
$alm["mercury_hlongitude"] = 215.41364793088036;
$alm["venus_hlongitude"] = 354.97553161915454;
$alm["earth_hlongitude"] = 206.42302894525753;
$alm["mars_hlongitude"] = 322.23704156869275;
$alm["jupiter_hlongitude"] = 55.376103620431465;
$alm["saturn_hlongitude"] = 341.3059188633712;
$alm["uranus_hlongitude"] = 52.80111547314749;
$alm["neptune_hlongitude"] = 357.54758961528455;
$alm["pluto_hlongitude"] = 300.3998886034081;

//night or day?
if (strtotime(date("G.i")) >= strtotime($alm["civil_twilight_begin"]) && strtotime(date("G.i")) < strtotime($alm["civil_twilight_end"])){
    $dayPartCivil = "day";
}else{
    $dayPartCivil = "night";
}
if (strtotime(date("G.i")) >= strtotime($alm["nautical_twilight_begin"]) && strtotime(date("G.i")) < strtotime($alm["nautical_twilight_end"])){
    $dayPartNautical = "day";
}else{
    $dayPartNautical = "night";
}
if (strtotime(date("G.i")) >= strtotime($alm["sunrise"]) && strtotime(date("G.i")) < strtotime($alm["sunset"])){
    $dayPartNatural = "day";
}else{
    $dayPartNatural = "night";
}



//barometer
$barom["units"] = " hPa";
if ($barom["units"] == " inHg"){
    $barom["units"] = "inHg";
}
else if ($barom["units"] == " hPa")
{
    $barom["units"] = "hPa";
}
else if ($barom["units"] == " kPa")
{
    $barom["units"] = "kPa";
}
else if ($barom["units"] == " mmHg")
{
    $barom["units"] = "mmHg";
}
else if ($barom["units"] == " mbar")
{
    $barom["units"] = "mbar";
}
$barom["now"] = $weewxrt[10]; //$realData["barometer"]; Removed for future.
$barom["max"] = 1018.9;
$barom["maxtime"] = date('H:i',"1713135661");
$barom["min"] = 1007.9;
$barom["mintime"] = date('H:i',"1713165122");
$barom["trend_code"] = round(0.8465970397040792,0);
if(is_numeric($barom["trend_code"]) != 1){$barom["trend_code"] = 0;}
if($barom["trend_code"]>3){$barom["trend_desc"]="Rising Very Rapidly";$barom["trend_code"]=4;}
else if($barom["trend_code"]>2){$barom["trend_desc"]="Rising Quickly";$barom["trend_code"]=3;}
else if($barom["trend_code"]>1){$barom["trend_desc"]="Rising";$barom["trend_code"]=2;}
else if($barom["trend_code"]>0){$barom["trend_desc"]="Rising Slowly";$barom["trend_code"]=1;}
else if($barom["trend_code"]==0){$barom["trend_desc"]="Steady";$barom["trend_code"]=0;}
else if($barom["trend_code"]<0){$barom["trend_desc"]="Falling Slowly";$barom["trend_code"]=-1;}
else if($barom["trend_code"]<-1){$barom["trend_desc"]="Falling";$barom["trend_code"]=-2;}
else if($barom["trend_code"]<-2){$barom["trend_desc"]="Falling Quickly";$barom["trend_code"]=-3;}
else if($barom["trend_code"]<-3){$barom["trend_desc"]="Falling Very Rapidly";$barom["trend_code"]=-4;}
else if($barom["trend_code"]=="N/A"||$barom["trend_code"]==" N/A"||$barom["trend_code"]=="  N/A"||$barom["trend_code"]==    N/A){$barom["trend_desc"]="Steady";$barom["trend_code"]=0;}
$barom["yesterday_max"] = 1027.2;
$barom["yesterday_maxtime"] = date('H:i',1713079682);
$barom["yesterday_min"] = 1018.8;
$barom["yesterday_mintime"] = date('H:i',1713135483);
$barom["month_max"] = 1031.7;
$barom["month_maxtime"] = date('j M H:i',1712836741);
$barom["month_min"] = 990.9;
$barom["month_mintime"] = date('j M H:i',1711932898);
$barom["year_max"] = 1040.6;
$barom["year_maxtime"] = date('j M H:i',1705009200);
$barom["year_min"] = 969.9;
$barom["year_mintime"] = date('j M H:i',1711630800);
$barom["alltime_max"] = 1053.2;
$barom["alltime_maxtime"] = date('j M Y H:i',1597242900);
$barom["alltime_min"] = 956.1;
$barom["alltime_mintime"] = date('j M Y H:i',1698897900);


//dewpoint
$dew["now"] = $weewxrt[4];
$dew["trend"] = -0.0;
if(is_numeric($dew["trend"]) != 1){$dew["trend"] = 0;}
$dew["day_max"] = 8.3;
$dew["day_maxtime"] = date('H:i',1713166644);
$dew["day_min"] = 2.7;
$dew["day_mintime"] = date('H:i',1713184728);
$dew["yesterday_max"] = 8.3;
$dew["yesterday_maxtime"] = date('H:i',1713081694);
$dew["yesterday_min"] = 4.6;
$dew["yesterday_mintime"] = date('H:i',1713115571);
$dew["month_max"] = 15.4;
$dew["month_maxtime"] = date('D j H:i',1712844868);
$dew["month_min"] = 1.3;
$dew["month_mintime"] = date('D j H:i',1711950220);
$dew["year_max"] = 15.4;
$dew["year_maxtime"] = date('D j M H:i',1712844868);
$dew["year_min"] = -7.9; 
$dew["year_mintime"] = date('D j M H:i',1705558200); 
$dew["alltime_max"] = 23.4;
$dew["alltime_maxtime"] = date('j M Y H:i',1694085000);
$dew["alltime_min"] = -8.7;
$dew["alltime_mintime"] = date('j M Y H:i',1671091800);
    
//humidity
$humid["now"] = $weewxrt[3];
$humid["trend"] = 4;
if(is_numeric($humid["trend"]) != 1){$humid["trend"] = 0;}
$humid["day_max"] = 91;
$humid["day_maxtime"] = date('H:i',1713155062);
$humid["day_min"] = 57;
$humid["day_mintime"] = date('H:i',1713184632);
$humid["yesterday_max"] = 95;
$humid["yesterday_maxtime"] = date('H:i',1713075932);
$humid["yesterday_min"] = 55;
$humid["yesterday_mintime"] = date('H:i',1713109911);
$humid["month_max"] = 97;
$humid["month_maxtime"] = date('D j H:i',1711956061);
$humid["month_min"] = 55;
$humid["month_mintime"] = date('D j H:i',1713109911);
$humid["year_max"] = 99;
$humid["year_maxtime"] = date('D j M H:i',1707377400);
$humid["year_min"] = 55;
$humid["year_mintime"] = date('D j M H:i',1711123200);
$humid["alltime_max"] = 99;
$humid["alltime_maxtime"] = date('j M Y H:i',1557349798);
$humid["alltime_min"] = 18;
$humid["alltime_mintime"] = date('j M Y H:i',1589985898);
$humid["indoors_now"] = $weewxrt[23];
$humid["indoors_trend"] = round(10,1);
if(is_numeric($humid["indoors_trend"]) != 1){$humid["indoors_trend"] = 0;}
$humid["indoors_day_max"] = 72;
$humid["indoors_day_maxtime"] = date('H:i',1713156602);
$humid["indoors_day_min"] = 23;
$humid["indoors_day_mintime"] = date('H:i',1713178442);
$humid["indoors_yesterday_max"] = 72;
$humid["indoors_yesterday_maxtime"] = date('H:i',1713069181);
$humid["indoors_yesterday_min"] = 31;
$humid["indoors_yesterday_mintime"] = date('H:i',1713090542);
$humid["indoors_month_max"] = 79;
$humid["indoors_month_maxtime"] = date('D j H:i',1712458920);
$humid["indoors_month_min"] = 23;
$humid["indoors_month_mintime"] = date('D j H:i',1713178442);
$humid["indoors_year_max"] = 86;
$humid["indoors_year_maxtime"] = date('D j M H:i',1704349500);
$humid["indoors_year_min"] = 23;
$humid["indoors_year_mintime"] = date('D j H:i',1713178442);
$humid["indoors_alltime_max"] = 87;
$humid["indoors_alltime_maxtime"] = date('j M Y H:i',1673163600);
$humid["indoors_alltime_min"] = 11;
$humid["indoors_alltime_mintime"] = date('j M Y H:i',1685101200);

//lightning
$lightning["last_strike_time"] = "1711907137.0";
$lightning["current_strike_count"] = "0"; 
$lightning["hour_strike_count"] ="0";  
$lightning["today_strike_count"] = "0";
$lightning["yesterday_strike_count"] = "0";
$lightning["month_strike_count"] = "0";
$lightning["year_strike_count"] = "5";
$lightning["last_time"] = "1711907137.0";
$lightning["alltime_strike_count"] = "166";
$lightning["last_distance"] = "17.0";
$lightning["now_energy"] = $weewxrt[59];
$lightning["now_strike_count"] = $weewxrt[60];
$lightning["now_noise_count"] = $weewxrt[61];
$lightning["now_disturber_count"] = $weewxrt[62];
if (trim($lightning["last_time"]) == 'N/A' || $lightning["last_time"] == '0' || $lightning["last_time"] == 'NULL')
{
$lightning["time_ago"] = 0;
$lightning['last_time'] = time();
$lightning["last_distance"] = "0";
}
else
{
$parts = explode(" ", $lightning["last_time"]);
$parts1 = explode("/", $parts[0]);
$lightning["time_ago"] = time() - strtotime("20" . $parts1[2] . $parts1[1] . $parts1[0] . " " . $parts[1]);
}

//rainfall
$rain["units"] = " mm";
if($rain["units"] == " mm")
{
$rain["units"] = "mm";
}
else if($rain["units"] == " cm")
{
$rain["units"] = "cm";
}
else if($rain["units"] == " in")
{
$rain["units"] = "in";
}
$rain["rate"] = round($weewxrt[8],1);
$rain["current"] = round($weewxrt[9],1);
$rain["total"] = round(3.3999999792600004,1);
$rain["last_hour"] = round(0.0,1);
$rain["last_10min"] = 0.0;
$rain["last_24hour"] = round(3.3999999792600004,1);
$rain["24h_rate_max"] = 0.0;
$rain["24h_rate_maxtime"] = 1713049201;
$rain["yesterday_rate_max"] = 0.0;
$rain["yesterday_rate_maxtime"] = 1713049201;
$rain["yesterday"] = round(0.0,1);
$rain["24h_total"] = round(3.3999999792600004,1);
$rain["day"] = round(3.3999999792600004,1);
$rain["day_rate_max"] = 30.19999981578;
$rain["day_rate_maxtime"] = 1713166764;
$rain["day_total"] = 3.3999999792600004;
$rain["month_rate_max"] = 41.19999974868;
$rain["month_rate_maxtime"] = 1712447241;
$rain["month_total"] = round(41.99999974380001,1);
$rain["year_rate_max"] = 116.39999928996;
$rain["year_rate_maxtime"] = 1708586700;
$rain["year_total"] = round(390.5999976173402,1);
$rain["alltime_rate_max"] = 1645.79998996062;
$rain["alltime_rate_maxtime"] = 1597863300;
$rain["alltime_total"] = round(4446.725979024533,1);
$rain["storm_rain"] = 3.3999999792600004;
if(is_numeric($rain["storm_rain"]) != 1){$rain["storm_rain"] = 0;}
if($rain["storm_rain"] > 0 ){$rain["storm_rain_start"] = date('D j M H:i',($TS)-(64200));}
$rain["last_rain"] = date('D j M H:i',1713167700);
if($weewxrt[8] < 2.5){$rain["intensity"]="Slight";}
else if($weewxrt[8] < 10){$rain["intensity"]="Moderate";}
else if($weewxrt[8] < 50){$rain["intensity"]="Heavy";}
else if($weewxrt[8] >= 50){$rain["intensity"]="Violent";}

//sky
$sky["lux"] = round($weewxrt[45]/0.00809399477,0,PHP_ROUND_HALF_UP);
$sky["day_rad_max"] = "986";
if($sky["day_rad_max"] == "   N/A"){$sky["day_rad_max"]=0;}
else{$sky["day_rad_max"] = 986;}
$sky["day_lux_max"] = round($sky["day_rad_max"]/0.00809399477,0,PHP_ROUND_HALF_UP);
$sky["cloud_base"] = $weewxrt[52];
$sky["cloud_cover"] = 30.0;
if(is_numeric($sky["cloud_cover"]) != 1){$sky["cloud_cover"] = 0;}

//solar
$solar["now"] = 0;
$solar["day_max"] = 986;
$solar["day_maxtime"] = date('H:i',1713180758);
$solar["yesterday_max"] = 905;
$solar["yesterday_maxtime"] = date('H:i',1713094658);
$solar["month_max"] = 998;
$solar["month_maxtime"] = date('D j H:i',1712661747);
$solar["year_max"] = 998;
$solar["year_maxtime"] = date('D j M H:i',1712661747);
$solar["alltime_max"] = 2368;
$solar["alltime_maxtime"] = date('j M Y H:i',1556966087);

//temperature
$temp["units"] = "C";
if($temp["units"] == "C")
{
$temp["units"] = "C";
}
else if($temp["units"] == "F")
{
$temp["units"] = "F";
}
$temp["indoor_now"] = $weewxrt[22];
$temp["indoor_trend"] = round(-4.333333333333332,1);
if(is_numeric($temp["indoor_trend"]) != 1){$temp["indoor_trend"] = 0;}
$temp["indoor_day_max"] = 35.3;
$temp["indoor_day_min"] = 11.7;
$temp["outside_now"] = $weewxrt[2];
$temp["outside_trend"] = round(-0.8333333333333339,1);
if(is_numeric($temp["outside_trend"]) != 1){$temp["outside_trend"] = 0;}
$temp["apptemp"] = $weewxrt[54];
$temp["heatindex"] = $weewxrt[41];
$temp["windchill"] = round($weewxrt[24],1);
$temp["humidex"] = $weewxrt[42];
$temp["outside_24h_max"] = 14.7;
$temp["outside_day_avg_60mn"] = 7.8;
$temp["outside_24h_maxtime"] = date('H:i',1713100708);
$temp["outside_24h_min"] = 6.6;
$temp["outside_24h_mintime"] = date('H:i',1713073494);
$temp["outside_yesterday_max"] = 14.7;
$temp["outside_yesterday_maxtime"] = date('H:i',1713100708);
$temp["outside_yesterday_min"] = 6.6;
$temp["outside_yesterday_mintime"] = date('H:i',1713073494);
$temp["outside_day_avg"] = 9.5;
$temp["outside_day_max"] = 12.7;
$temp["outside_day_maxtime"] = date('H:i',1713187445);
$temp["outside_day_min"] = 7.4;
$temp["outside_day_mintime"] = date('H:i',1713215939);
$temp["outside_month_max"] = 21.7;
$temp["outside_month_maxtime"] = date('D j H:i',1712931060);
$temp["outside_month_min"] = 2.2;
$temp["outside_month_mintime"] = date('D j H:i',1711950220);
$temp["outside_year_max"] = 21.7;
$temp["outside_year_maxtime"] = date('D j M H:i',1712931060);
$temp["outside_year_maxtime2"] = date('M',1712931060);
$temp["outside_year_min"] = -6.6;
$temp["outside_year_mintime"] = date('D j M H:i',1705651500);
$temp["outside_year_mintime2"] = date('M',1705651500);
$temp["outside_alltime_max"] = 35.7;
$temp["outside_alltime_maxtime"] = date('j M Y H:i',1660488000);
$temp["outside_alltime_min"] = -7.6;
$temp["outside_alltime_mintime"] = date('j M Y H:i',1671091800);
$Temp = $temp["indoor_now"];
$Humidity = $humid["indoors_now"];
$T = $Temp*9/5+32;
$RH = $Humidity;
$HI = 0;
if($T<=40.0){$HI=$T;}
else {$HI=-42.379+(2.04901523*$T)+(10.14333127*$RH)-(0.22475541*$T*$RH)-(0.00683783*$T*$T)-(0.05481717*$RH*$RH)+(0.00122874*$T*$T*$RH)+(0.00085282*$T*$RH*$RH)-(0.00000199*$T*$T*$RH*$RH);}
if ($RH<13&&$T>=80&&$T<=112){$adjust=((13-$RH)/4)*sqrt(17-abs($T-95)/17);$HI=$HI-$adjust;}
else if ($RH>85&&$T>=80&&$T<=87){$adjust=(($RH-85)/10)*((87-$T)/5);$HI=$HI+$adjust;}
else if ($T<80){$HI=0.5*($T+61.0+(($T-68.0)*1.2)+($RH*0.094));}
$temp["indoor_now_feels"] = number_format($HI, 1);
$temp["indoor_now_feels_degC"] = (number_format($HI, 1)-32)*5/9;


//uv
$uv["now"] = $weewxrt[43];
$uv["day_max"] = 3.6;
$uv["day_maxtime"] = date('H:i',1713181588);
$uv["yesterday_max"] = 3.9;
$uv["yesterday_maxtime"] = date('H:i',1713094668);
$uv["month_max"] = 4.3;
$uv["month_maxtime"] = date('D j H:i',1712843440);
$uv["year_max"] = 4.3;
$uv["year_maxtime"] = date('D j M H:i',1712843440);
$uv["alltime_max"] = 15.0;
$uv["alltime_maxtime"] = date('j M Y H:i',1556966391);

//wind
$wind["units"] = " m/s"; // m/s or mph or km/h or kts
if ($wind["units"] == " m/s")
{
$wind["units"] = "m/s";
}
else if ($wind["units"] == " mph")
{
$wind["units"] = "mph";
}
else if ($wind["units"] == " km/h")
{
$wind["units"] = "km/h";
}
else if ($wind["units"] == " kts")
{
$wind["units"] = "kts";
}
$wind["direction"] = str_pad($weewxrt[7], 3, '0', STR_PAD_LEFT);
$wind["direction_10m_avg"] = "248";
$wind["cardinal"] = $weewxrt[11];    
$wind["speed"] = $weewxrt[6];
$wind["gust"] = $weewxrt[57];
$wind["speed_bft"] = $weewxrt[12];
$wind["speed_max"] = 9.4;
$wind["speed_maxtime"] = date('H:i',1713178200);
$wind["gust_max"] = 16.5;
$wind["gust_maxtime"] = date('H:i',1713165670);
$wind["wind_run"] = $weewxrt[17];
$wind["speed_10m_avg"] = 3.6;
$wind["speed_10m_max"] = 3.6;
$wind["gust_10m_max"] = 8.5;
$wind["speed_yesterday_max"] = 4.9;
$wind["speed_yesterday_maxtime"] = date('H:i',1713120900);
$wind["gust_yesterday_max"] = 8.5;
$wind["gust_yesterday_maxtime"] = date('H:i',1713119107);
$wind["speed_month_max"] = 9.4;
$wind["speed_month_maxtime"] = date('M',1713178200);
$wind["gust_month_max"] = 17.4;
$wind["gust_month_maxtime"] = date('D j M H:i',1712634566);
$wind["speed_year_max"] = 13.0;
$wind["speed_year_maxtime"] = "21-Jan-2024 18:35";
$wind["gust_year_max"] = 24.1;
$wind["gust_year_maxtime"] = date('D j M H:i',1705864200);
$wind["speed_alltime_max"] = 14.8;
$wind["speed_alltime_maxtime"] = "18-Feb-2022 09:50";
$wind["gust_alltime_max"] = 25.5;
$wind["gust_alltime_maxtime"] = date('D j M Y H:i',1667859300);


//colors

//air density
$colorAirDensity = "#007fff";

//barometer almanac
$colorBarometerCurrent = "#E90076";
$colorBarometerDayMax = "#E90076";
$colorBarometerDayMin = "#FF0000";
$colorBarometerYesterdayMax = "#377EF7";
$colorBarometerYesterdayMin = "#E90076";
$colorBarometerMonthMax = "#377EF7";
$colorBarometerMonthMin = "#FF0000";
$colorBarometerYearMax = "#377EF7";
$colorBarometerYearMin = "#FF0000";
$colorBarometerAlltimeMax = "#377EF7";
$colorBarometerAlltimeMin = "#FF0000";
//temperature etc current

if($weewxrt[2]<= -10){$colorOutTemp = "#8781bd";}
else if($weewxrt[2]<=0){$colorOutTemp = "#487ea9";}
else if($weewxrt[2]<=5){$colorOutTemp = "#3b9cac";}
else if($weewxrt[2]<10){$colorOutTemp = "#9aba2f";}
else if($weewxrt[2]<20){$colorOutTemp = "#e6a141";}
else if($weewxrt[2]<25){$colorOutTemp = "#ec5a34";}
else if($weewxrt[2]<30){$colorOutTemp = "#d05f2d";}
else if($weewxrt[2]<35){$colorOutTemp = "#d65b4a";}
else if($weewxrt[2]<40){$colorOutTemp = "#dc4953";}
else if($weewxrt[2]<100){$colorOutTemp = "#e26870";}

if($weewxrt[4]<= -10){$colorDewpoint = "#8781bd";}
else if($weewxrt[4]<=0){$colorDewpoint = "#487ea9";}
else if($weewxrt[4]<=5){$colorDewpoint = "#3b9cac";}
else if($weewxrt[4]<10){$colorDewpoint = "#9aba2f";}
else if($weewxrt[4]<20){$colorDewpoint = "#e6a141";}
else if($weewxrt[4]<25){$colorDewpoint = "#ec5a34";}
else if($weewxrt[4]<30){$colorDewpoint = "#d05f2d";}
else if($weewxrt[4]<35){$colorDewpoint = "#d65b4a";}
else if($weewxrt[4]<40){$colorDewpoint = "#dc4953";}
else if($weewxrt[4]<100){$colorDewpoint = "#e26870";}

if($weewxrt[24]<= -10){$colorWindchill = "#8781bd";}
else if($weewxrt[24]<=0){$colorWindchill = "#487ea9";}
else if($weewxrt[24]<=5){$colorWindchill = "#3b9cac";}
else if($weewxrt[24]<10){$colorWindchill = "#9aba2f";}
else if($weewxrt[24]<20){$colorWindchill = "#e6a141";}
else if($weewxrt[24]<25){$colorWindchill = "#ec5a34";}
else if($weewxrt[24]<30){$colorWindchill = "#d05f2d";}
else if($weewxrt[24]<35){$colorWindchill = "#d65b4a";}
else if($weewxrt[24]<40){$colorWindchill = "#dc4953";}
else if($weewxrt[24]<100){$colorWindchill = "#e26870";}

if($weewxrt[41]<= -10){$colorHeatindex = "#8781bd";}
else if($weewxrt[41]<=0){$colorHeatindex = "#487ea9";}
else if($weewxrt[41]<=5){$colorHeatindex = "#3b9cac";}
else if($weewxrt[41]<10){$colorHeatindex = "#9aba2f";}
else if($weewxrt[41]<20){$colorHeatindex = "#e6a141";}
else if($weewxrt[41]<25){$colorHeatindex = "#ec5a34";}
else if($weewxrt[41]<30){$colorHeatindex = "#d05f2d";}
else if($weewxrt[41]<35){$colorHeatindex = "#d65b4a";}
else if($weewxrt[41]<40){$colorHeatindex = "#dc4953";}
else if($weewxrt[41]<100){$colorHeatindex = "#e26870";}

if($weewxrt[42]<= -10){$colorHumidex = "#8781bd";}
else if($weewxrt[42]<=0){$colorHumidex = "#487ea9";}
else if($weewxrt[42]<=5){$colorHumidex = "#3b9cac";}
else if($weewxrt[42]<10){$colorHumidex = "#9aba2f";}
else if($weewxrt[42]<20){$colorHumidex = "#e6a141";}
else if($weewxrt[42]<25){$colorHumidex = "#ec5a34";}
else if($weewxrt[42]<30){$colorHumidex = "#d05f2d";}
else if($weewxrt[42]<35){$colorHumidex = "#d65b4a";}
else if($weewxrt[42]<40){$colorHumidex = "#dc4953";}
else if($weewxrt[42]<100){$colorHumidex = "#e26870";}

if($weewxrt[54]<= -10){$colorAppTemp = "#8781bd";}
else if($weewxrt[54]<=0){$colorAppTemp = "#487ea9";}
else if($weewxrt[54]<=5){$colorAppTemp = "#3b9cac";}
else if($weewxrt[54]<10){$colorAppTemp = "#9aba2f";}
else if($weewxrt[54]<20){$colorAppTemp = "#e6a141";}
else if($weewxrt[54]<25){$colorAppTemp = "#ec5a34";}
else if($weewxrt[54]<30){$colorAppTemp = "#d05f2d";}
else if($weewxrt[54]<35){$colorAppTemp = "#d65b4a";}
else if($weewxrt[54]<40){$colorAppTemp = "#dc4953";}
else if($weewxrt[54]<100){$colorAppTemp = "#e26870";}

$colorOutTempDayAvg = "#9aba2f";

//humidity
if($humid["now"]<30){$colorHumidity="Blue";}
else if($humid["now"]<60){$colorHumidity="Green";}
else if($humid["now"]<100){$colorHumidity="Red";}

if($humid["day_max"]<30){$colorHumidityDayMax="Blue";}
else if($humid["day_max"]<60){$colorHumidityDayMax="Green";}
else if($humid["day_max"]<100){$colorHumidityDayMax="Red";}
if($humid["day_min"]<30){$colorHumidityDayMin="Blue";}
else if($humid["day_min"]<60){$colorHumidityDayMin="Green";}
else if($humid["day_min"]<100){$colorHumidityDayMin="Red";}

if($humid["yesterday_max"]<30){$colorHumidityYesterdayMax="Blue";}
else if($humid["yesterday_max"]<60){$colorHumidityYesterdayMax="Green";}
else if($humid["yesterday_max"]<100){$colorHumidityYesterdayMax="Red";}
if($humid["yesterday_min"]<30){$colorHumidityYesterdayMin="Blue";}
else if($humid["yesterday_min"]<60){$colorHumidityYesterdayMin="Green";}
else if($humid["yesterday_min"]<100){$colorHumidityYesterdayMin="Red";}

if($humid["month_max"]<30){$colorHumidityMonthMax="Blue";}
else if($humid["month_max"]<60){$colorHumidityMonthMax="Green";}
else if($humid["month_max"]<100){$colorHumidityMonthMax="Red";}
if($humid["month_min"]<30){$colorHumidityMonthMin="Blue";}
else if($humid["month_min"]<60){$colorHumidityMonthMin="Green";}
else if($humid["month_min"]<100){$colorHumidityMonthMin="Red";}

if($humid["year_max"]<30){$colorHumidityYearMax="Blue";}
else if($humid["year_max"]<60){$colorHumidityYearMax="Green";}
else if($humid["year_max"]<100){$colorHumidityYearMax="Red";}
if($humid["year_min"]<30){$colorHumidityYearMin="Blue";}
else if($humid["year_min"]<60){$colorHumidityYearMin="Green";}
else if($humid["year_min"]<100){$colorHumidityYearMin="Red";}

if($humid["alltime_max"]<30){$colorHumidityAlltimeMax="Blue";}
else if($humid["alltime_max"]<60){$colorHumidityAlltimeMax="Green";}
else if($humid["alltime_max"]<100){$colorHumidityAlltimeMax="Red";}
if($humid["alltime_min"]<30){$colorHumidityAlltimeMin="Blue";}
else if($humid["alltime_min"]<60){$colorHumidityAlltimeMin="Green";}
else if($humid["alltime_min"]<100){$colorHumidityAlltimeMin="Red";}

//temperature almanac
//day
$colorOutTempDayMax = "#eba141";
$colorOutTempDayMin = "#9aba2f";
$colorDewpointDayMax = "#9aba2f";
$colorDewpointDayMin = "#369cac";

//yesterday
$colorOutTempYesterdayMax = "#eba141";
$colorOutTempYesterdayMin = "#9aba2f";
$colorDewpointYesterdayMax = "#9aba2f";
$colorDewpointYesterdayMin = "#369cac";

//month
$colorOutTempMonthMax = "#ec5a34";
$colorOutTempMonthMin = "#369cac";
$colorDewpointMonthMax = "#eba141";
$colorDewpointMonthMin = "#369cac";

//year
$colorOutTempYearMax = "#ec5a34";
$colorOutTempYearMin = "#487ea9";
$colorDewpointYearMax = "#eba141";
$colorDewpointYearMin = "#487ea9";

//alltime
$colorOutTempAlltimeMax = "#dc4953";
$colorOutTempAlltimeMin = "#487ea9";
$colorDewpointAlltimeMax = "#ec5a34";
$colorDewpointAlltimeMin = "#487ea9";

//60min
$color["outTemp_60min_avg"] = "#9aba2f";

//indoors
if($weewxrt[22]<= -10){$colorInTemp = "#8781bd";}
else if($weewxrt[22]<=0){$colorInTemp = "#487ea9";}
else if($weewxrt[22]<=5){$colorInTemp = "#3b9cac";}
else if($weewxrt[22]<10){$colorInTemp = "#9aba2f";}
else if($weewxrt[22]<20){$colorInTemp = "#e6a141";}
else if($weewxrt[22]<25){$colorInTemp = "#ec5a34";}
else if($weewxrt[22]<30){$colorInTemp = "#d05f2d";}
else if($weewxrt[22]<35){$colorInTemp = "#d65b4a";}
else if($weewxrt[22]<40){$colorInTemp = "#dc4953";}
else if($weewxrt[22]<100){$colorInTemp = "#e26870";}


// humidity in
if($humid["indoors_now"]<30){$colorInHumidity="Blue";}
else if($humid["indoors_now"]<60){$colorInHumidity="Green";}
else if($humid["indoors_now"]<100){$colorInHumidity="Red";}

// feels
if($temp["indoor_now_feels_degC"]<= -10){$colorFeels = "#8781bd";}
else if($temp["indoor_now_feels_degC"]<=0){$colorFeels = "#487ea9";}
else if($temp["indoor_now_feels_degC"]<=5){$colorFeels = "#3b9cac";}
else if($temp["indoor_now_feels_degC"]<10){$colorFeels = "#9aba2f";}
else if($temp["indoor_now_feels_degC"]<20){$colorFeels = "#e6a141";}
else if($temp["indoor_now_feels_degC"]<25){$colorFeels = "#ec5a34";}
else if($temp["indoor_now_feels_degC"]<30){$colorFeels = "#d05f2d";}
else if($temp["indoor_now_feels_degC"]<35){$colorFeels = "#d65b4a";}
else if($temp["indoor_now_feels_degC"]<40){$colorFeels = "#dc4953";}
else if($temp["indoor_now_feels_degC"]<100){$colorFeels = "#e26870";}


//wind speed color
if($weewxrt[12] == 0){$color["windSpeed"]="#85a3aa";}
else if($weewxrt[12] == 1){$color["windSpeed"]="#7e98bb";}
else if($weewxrt[12] == 2){$color["windSpeed"]="#6e90d0";}
else if($weewxrt[12] == 3){$color["windSpeed"]="#0f94a7";}
else if($weewxrt[12] == 4){$color["windSpeed"]="#39a239";}
else if($weewxrt[12] == 5){$color["windSpeed"]="#c2863e";}
else if($weewxrt[12] == 6){$color["windSpeed"]="#c8420d";}
else if($weewxrt[12] == 7){$color["windSpeed"]="#d20032";}
else if($weewxrt[12] == 8){$color["windSpeed"]="#af5088";}
else if($weewxrt[12] == 9){$color["windSpeed"]="#754a92";}
else if($weewxrt[12] == 10){$color["windSpeed"]="#45698d";}
else if($weewxrt[12] == 11){$color["windSpeed"]="#c1fc77";}
else if($weewxrt[12] == 12){$color["windSpeed"]="#f1ff6c";}
//wind gust color
if($weewxrt[57] <= 1){$color["windGust"]="#85a3aa";}
else if($weewxrt[57] <= 2){$color["windGust"]="#7e98bb";}
else if($weewxrt[57] <= 3){$color["windGust"]="#6e90d0";}
else if($weewxrt[57] <= 5){$color["windGust"]="#0f94a7";}
else if($weewxrt[57] <= 8){$color["windGust"]="#39a239";}
else if($weewxrt[57] <= 11){$color["windGust"]="#c2863e";}
else if($weewxrt[57] <= 14){$color["windGust"]="#c8420d";}
else if($weewxrt[57] <= 17){$color["windGust"]="#d20032";}
else if($weewxrt[57] <= 21){$color["windGust"]="#af5088";}
else if($weewxrt[57] <= 24){$color["windGust"]="#754a92";}
else if($weewxrt[57] <= 28){$color["windGust"]="#45698d";}
else if($weewxrt[57] <= 32){$color["windGust"]="#c1fc77";}
else if($weewxrt[57] <= 100){$color["windGust"]="#f1ff6c";}
$color["windSpeed_max"] = "#c2863e";
$color["windGust_max"] = "#d20032";
$color["windSpeed_avg"] = "#0f94a7";
$color["windSpeed_10min_avg"] = "#0f94a7";
$color["windGust_10min_max"] = "#c2863e";
$color["windSpeed_yesterday_max"] = "#0f94a7";
$color["windGust_yesterday_max"] = "#c2863e";
$color["windSpeed_month_max"] = "#c2863e";
$color["windGust_month_max"] = "#af5088";
$color["windSpeed_year_max"] = "#c8420d";
$color["windGust_year_max"] = "#45698d";
$color["windSpeed_alltime_max"] = "#d20032";
$color["windGust_alltime_max"] = "#45698d";

//rain color
$colorStormRain ="#615884";

if($rain["total"] == 0 && $theme == 'dark'){$colorRainDaySum ="#C0C0C0";}
else if($rain["total"] == 0){$colorRainDaySum ="#3A3D40";}
else {$colorRainDaySum = "#615884";}
$colorRainYesterdaySum = "#3A3D40";
$colorRain1hrSum = "#3A3D40";
$colorRain24hrSum = "#615884"; 
$colorRainMonthSum = "#a79d51";
$colorRainYearSum = "#cf2848";
$colorRainAlltimeSum = "#eba141";

if($rain["rate"] == 0 && $theme == 'dark'){$colorRainRate ="#C0C0C0";}
else if($rain["rate"] == 0){$colorRainRate ="#3A3D40";}
else {$colorRainRate = "#3A3D40";}
$colorRainRateDayMax = "#359f35";
$colorRainRateYesterdayMax = "#3A3D40";
$colorRainRate24hrMax = "#359f35"; 
$colorRainRateMonthMax = "#a79d51";
$colorRainRateYearMax = "#9f7f3a";
$colorRainRateAlltimeMax = "#dcdcdc";


$colorUVCurrent = "grey";
$colorUVDayMax = "#fed42d";
$colorUVYesterdayMax = "#fed42d";
$colorUVMonthMax = "#fed42d";
$colorUVYearMax = "#fed42d";
$colorUVAlltimeMax = "#de257b";

$colorSolarCurrent = "grey";
$colorSolarDayMax = "#b7161b";
$colorSolarYesterdayMax = "#b7161b";
$colorSolarMonthMax = "#b7161b";
$colorSolarYearMax = "#b7161b";
$colorSolarAlltimeMax = "grey";

if($sky["lux"] == 0){$colorLuxCurrent='grey';}
else if($sky["lux"] < 16000){$colorLuxCurrent='#89C7E7';}
else if($sky["lux"] < 32000){$colorLuxCurrent='#ADD8E5';}
else if($sky["lux"] < 48000){$colorLuxCurrent='#C4EBF1';}
else if($sky["lux"] < 64000){$colorLuxCurrent='#FFFFC2';}
else if($sky["lux"] < 80000){$colorLuxCurrent='#FFF684';}
else if($sky["lux"] > 80000){$colorLuxCurrent='#FFD301';}

if($sky["day_lux_max"] == 0){$colorLuxDay='grey';}
else if($sky["day_lux_max"] < 16000){$colorLuxDay='#89C7E7';}
else if($sky["day_lux_max"] < 32000){$colorLuxDay='#ADD8E5';}
else if($sky["day_lux_max"] < 48000){$colorLuxDay='#C4EBF1';}
else if($sky["day_lux_max"] < 64000){$colorLuxDay='#FFFFC2';}
else if($sky["day_lux_max"] < 80000){$colorLuxDay='#FFF684';}
else if($sky["day_lux_max"] > 80000){$colorLuxDay='#FFD301';}

//Convert temperatures if necessary
include('dvmUnitConversions.php');
include('dvmMeteorShowers.php');

$firerisk = number_format((((110-1.373*$divum["humidity"])-0.54*(10.20-$divum["temp"]))*(124*pow(10,(-0.0142*$divum["humidity"]))))/60,0);

//wetbulb
$Tc = ($divum['temp']);
$P = $divum['barometer'];
$RH = $divum['humidity'];
$Tdc = (($Tc-(14.55+0.114*$Tc)*(1-(0.01*$RH))-pow((2.5+0.007*$Tc)*(1-(0.01*$RH)),3)-(15.9+0.117*$Tc)*pow(1-(0.01*$RH),14)));
$E = (6.11*pow(10,(7.5*$Tdc/(237.7+$Tdc))));
$wetbulbcalc = (((0.00066*$P)*$Tc)+((4098*$E)/pow(($Tdc+237.7),2)*$Tdc))/((0.00066*$P)+(4098*$E)/pow(($Tdc+237.7),2));
$wetbulbx = number_format($wetbulbcalc,1);
?>