<?php
include ('fixedSettings.php');
include ('dvmShared.php');
include('common.php');
include ('userSettings.php');
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
$stationlocation = "Steeple Claydon, UK";
$hardware = "GW2000";
$lat = 51.94;
$lon = -0.987;
$absLat = abs($lat);
$absLon = abs($lon);
if ($lat >= "0"){$NS = "North";}
else {$NS = "South";}
if ($lon >= "0") {$EW = "East";}
else {$EW = "West";}
$elevation = 88.0;
$url = "https://claydonsweather.org.uk";
$divum["date"] = date($dateFormat, $divum["datetime"]);
$divum["time"] = date($timeFormat, $divum["datetime"]);
$divum["swversion"] = "5.1.0";
$divum["build"] = $weewxrt[38];
$divum["since"] = "2020";
$divum["heatmap_year"] = date("Y");
$stationUptime = "12 days, 18 hours, 56 minutes";
$TS = time();

if($theme == 'dark') {$convertStyle = "background: 0; border-color: #393d40; color:";}
else {$convertStyle = "background:";}

$phpVersion = phpversion();

//air density
$air_density["now"] = 1.19016;
if(is_numeric($air_density["now"]) != 1){$air_density["now"] = 0;}
$air_density["day_max"] = number_format(1.20135,4);
$air_density["day_min"] = number_format(1.18871,4);
$air_density["day_avg"] = number_format(1.19551,4);
$air_density["day_maxtime"] = date('H:i',1724407165);
$air_density["day_mintime"] = date('H:i',1724416723);
$air_density["yesterday_max"] = number_format(1.21664,4);
$air_density["yesterday_min"] = number_format(1.17282,4);
$air_density["yesterday_avg"] = number_format(1.19788,4);
$air_density["yesterday_maxtime"] = date('H:i',1724281201);
$air_density["yesterday_mintime"] = date('H:i',1724342064);
$air_density["month_max"] = number_format(1.26169,4);
$air_density["month_min"] = number_format(1.14247,4);
$air_density["month_avg"] = number_format(1.20539,4);
$air_density["month_maxtime"] = date('j M H:i',1724043297);
$air_density["month_mintime"] = date('j M H:i',1723469192);
$air_density["year_max"] = number_format(1.31891,4);
$air_density["year_min"] = number_format(1.14247,4);
$air_density["year_avg"] = number_format(1.23236,4);
$air_density["year_maxtime"] = date('j M H:i',1705646700);
$air_density["year_mintime"] = date('j M H:i',1723469192);
$air_density["alltime_max"] = number_format(1.35583,4);
$air_density["alltime_min"] = number_format(1.12072,4);
$air_density["alltime_avg"] = number_format(1.23795,4);
$air_density["alltime_maxtime"] = date('j M Y H:i',1674546300);
$air_density["alltime_mintime"] = date('j M Y H:i',1658239200);

//air quality
$air["pm_units"] = "g/m<sup><b>3</b></sup>";
$air["current.pm2_5"] = 2.4;
$air["24h.rollingavg.pm2_5"] = 2.4;
$air["current.pm10_0"] = 4.1;
$air["24h.rollingavg.pm10_0"] = 4.1;


//almanac
$alm["sun_altitude"] = round(48.49095874835679,2);
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
$alm["sun_azimuth"] = 194.34990460607295;
$alm["moon_azimuth"] = 319.64367338235223;
$alm["sunrise"] = "06:01";
$alm["sunset"] = "20:10";
$alm["sunrise_date"] = "Aug 23 2024 06:01:34";
$alm["sunset_date"] = "Aug 23 2024 20:10:12";
$alm["sun_right_ascension"] = 152.92540862393258;
$alm["next_equinox"] = "22/09/24 13:43:32";
$alm["next_solstice"] = "21/12/24 09:20:20";
$alm["sidereal_time"] = 162.5659719669887;
$alm["civil_twilight_begin"] = "05:24";
$alm["civil_twilight_end"] = "20:47";
$alm["nautical_twilight_begin"] = "04:39";
$alm["nautical_twilight_end"] = "21:31";
$alm["astronomical_twilight_begin"] = "03:46";
$alm["astronomical_twilight_end"] = "22:24";
$alm["sun_meridian_transit"] = "13:06";
$alm["moon_meridian_transit"] = "03:58";
$alm["moonphase"] = "Waning gibbous";
$alm["moonphase_number"] = 5;
$alm["moon_age"] = 19.063901874376118;
$alm["hour_sun"] = 0.16825957208340103;
$alm["hour_moon"] = 2.4789783269576815;
$alm["luminance"] = round(80.6916075580523,2);
$alm["fullmoon"] = "18 Sep 03:34";
$alm["newmoon"] = "03 Sep 02:55";   
$alm["daylight"] = "14:08";
$alm["moonrise"] = "21:29";
$alm["moonset"] = "11:02";
$alm["mercury_hlongitude"] = 344.45693101430817;
$alm["venus_hlongitude"] = 203.9326736719792;
$alm["earth_hlongitude"] = 330.8825584163115;
$alm["mars_hlongitude"] = 42.08435058991155;
$alm["jupiter_hlongitude"] = 66.91474858158962;
$alm["saturn_hlongitude"] = 345.51454482614963;
$alm["uranus_hlongitude"] = 54.26766295588136;
$alm["neptune_hlongitude"] = 358.3381430305785;
$alm["pluto_hlongitude"] = 301.01160034547445;
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
$barom["max"] = 1006.2;
$barom["maxtime"] = date('H:i',"1724416740");
$barom["min"] = 995.6;
$barom["mintime"] = date('H:i',"1724388300");
$barom["trend_code"] = round(2.158333333332166,0);
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
$barom["yesterday_max"] = 1012.3;
$barom["yesterday_maxtime"] = date('H:i',1724281437);
$barom["yesterday_min"] = 1001.6;
$barom["yesterday_mintime"] = date('H:i',1724367482);
$barom["month_max"] = 1022.8;
$barom["month_maxtime"] = date('j M H:i',1723356822);
$barom["month_min"] = 995.6;
$barom["month_mintime"] = date('j M H:i',1724388300);
$barom["year_max"] = 1036.4;
$barom["year_maxtime"] = date('j M H:i',1705007100);
$barom["year_min"] = 971.6;
$barom["year_mintime"] = date('j M H:i',1704208200);
$barom["alltime_max"] = 1044.5;
$barom["alltime_maxtime"] = date('j M Y H:i',1675586400);
$barom["alltime_min"] = 955.9;
$barom["alltime_mintime"] = date('j M Y H:i',1698909300);


//dewpoint
$dew["now"] = $weewxrt[4];
$dewTrend = "-1.0";
if(is_numeric($dewTrend) != 1 or $dewTrend == '   N/A'){$dew["trend"] = 0;}
else {$dew["trend"] = intval($dewTrend);}
$dew["day_max"] = 16.3;
$dew["day_maxtime"] = date('H:i',1724391600);
$dew["day_min"] = 8.7;
$dew["day_mintime"] = date('H:i',1724415107);
$dew["yesterday_max"] = 18.3;
$dew["yesterday_maxtime"] = date('H:i',1724334663);
$dew["yesterday_min"] = 12.0;
$dew["yesterday_mintime"] = date('H:i',1724281201);
$dew["month_max"] = 22.8;
$dew["month_maxtime"] = date('D j H:i',1723470472);
$dew["month_min"] = 6.7;
$dew["month_mintime"] = date('D j H:i',1724043289);
$dew["year_max"] = 22.8;
$dew["year_maxtime"] = date('D j M H:i',1723470472);
$dew["year_min"] = -8.3; 
$dew["year_mintime"] = date('D j M H:i',1705551600); 
$dew["alltime_max"] = 22.8;
$dew["alltime_maxtime"] = date('j M Y H:i',1723470472);
$dew["alltime_min"] = -13.1;
$dew["alltime_mintime"] = date('j M Y H:i',1671093000);
    
//humidity
$humid["now"] = $weewxrt[3];
$humid["trend"] = -16;
if(is_numeric($humid["trend"]) != 1){$humid["trend"] = 0;}
$humid["day_max"] = 99;
$humid["day_maxtime"] = date('H:i',1724391151);
$humid["day_min"] = 50;
$humid["day_mintime"] = date('H:i',1724416611);
$humid["yesterday_max"] = 96;
$humid["yesterday_maxtime"] = date('H:i',1724334148);
$humid["yesterday_min"] = 66;
$humid["yesterday_mintime"] = date('H:i',1724342256);
$humid["month_max"] = 99;
$humid["month_maxtime"] = date('D j H:i',1722555812);
$humid["month_min"] = 37;
$humid["month_mintime"] = date('D j H:i',1723828943);
$humid["year_max"] = 99;
$humid["year_maxtime"] = date('D j M H:i',1704130500);
$humid["year_min"] = 34;
$humid["year_mintime"] = date('D j M H:i',1722267423);
$humid["alltime_max"] = 99;
$humid["alltime_maxtime"] = date('j M Y H:i',1597530000);
$humid["alltime_min"] = 18;
$humid["alltime_mintime"] = date('j M Y H:i',1658235900);
$humid["indoors_now"] = $weewxrt[23];
$humid["indoors_trend"] = round(-2,1);
if(is_numeric($humid["indoors_trend"]) != 1){$humid["indoors_trend"] = 0;}
$humid["indoors_day_max"] = 64;
$humid["indoors_day_maxtime"] = date('H:i',1724384698);
$humid["indoors_day_min"] = 58;
$humid["indoors_day_mintime"] = date('H:i',1724414340);
$humid["indoors_yesterday_max"] = 63;
$humid["indoors_yesterday_maxtime"] = date('H:i',1724326619);
$humid["indoors_yesterday_min"] = 59;
$humid["indoors_yesterday_mintime"] = date('H:i',1724281319);
$humid["indoors_month_max"] = 72;
$humid["indoors_month_maxtime"] = date('D j H:i',1723471548);
$humid["indoors_month_min"] = 43;
$humid["indoors_month_mintime"] = date('D j H:i',1722704198);
$humid["indoors_year_max"] = 77;
$humid["indoors_year_maxtime"] = date('D j M H:i',1709199300);
$humid["indoors_year_min"] = 35;
$humid["indoors_year_mintime"] = date('D j H:i',1705601700);
$humid["indoors_alltime_max"] = 89;
$humid["indoors_alltime_maxtime"] = date('j M Y H:i',1597605300);
$humid["indoors_alltime_min"] = 24;
$humid["indoors_alltime_mintime"] = date('j M Y H:i',1598722200);

//lightning
$lightning["last_strike_time"] = "1722532384";
$lightning["current_strike_count"] = "0"; 
$lightning["hour_strike_count"] ="0";  
$lightning["today_strike_count"] = "0";
$lightning["yesterday_strike_count"] = "0";
$lightning["month_strike_count"] = "28";
$lightning["year_strike_count"] = "121";
$lightning["last_time"] = "1722532384";
$lightning["alltime_strike_count"] = "2421";
$lightning["last_distance"] = "37.0";
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
$rain["total"] = round(3.5,1);
$rain["last_hour"] = round(0.0,1);
$rain["last_10min"] = 0.0;
$rain["last_24hour"] = round(3.5,1);
$rain["24h_rate_max"] = 0.0;
$rain["24h_rate_maxtime"] = 1724281201;
$rain["yesterday_rate_max"] = 0.0;
$rain["yesterday_rate_maxtime"] = 1724281201;
$rain["yesterday"] = round(0.0,1);
$rain["24h_total"] = round(3.5,1);
$rain["day"] = round(3.5,1);
$rain["day_rate_max"] = 5.999999993879999;
$rain["day_rate_maxtime"] = 1724391389;
$rain["day_total"] = 3.5;
$rain["month_rate_max"] = 5.999999993879999;
$rain["month_rate_maxtime"] = 1724391389;
$rain["month_total"] = round(12.699999999999989,1);
$rain["year_rate_max"] = 70.199999928396;
$rain["year_rate_maxtime"] = 1721719553;
$rain["year_total"] = round(572.9999999999999,1);
$rain["alltime_rate_max"] = 97.86666656684262;
$rain["alltime_rate_maxtime"] = 1686499800;
$rain["alltime_total"] = round(3588.8789999999963,1);

$rainStorm = 3.5;
if(is_numeric($rainStorm) != 1){$rain["storm_rain"] = 0;}
else{$rain["storm_rain"] = $rainStorm;}
if($rain["storm_rain"] > 0 ){$rain["storm_rain_start"] = date('D j M H:i',($TS)-(39600));}
$rain["last_rain"] = date('D j M H:i',1724391900);
if($weewxrt[8] < 2.5){$rain["intensity"]="Slight";}
else if($weewxrt[8] < 10){$rain["intensity"]="Moderate";}
else if($weewxrt[8] < 50){$rain["intensity"]="Heavy";}
else if($weewxrt[8] >= 50){$rain["intensity"]="Violent";}

//sky
$sky["lux"] = round($weewxrt[45]/0.00809399477,0,PHP_ROUND_HALF_UP);
$sky["day_rad_max"] = "663";
if($sky["day_rad_max"] == "   N/A"){$sky["day_rad_max"]=0;}
else{$sky["day_rad_max"] = 663;}
$sky["day_lux_max"] = round($sky["day_rad_max"]/0.00809399477,0,PHP_ROUND_HALF_UP);
$sky["cloud_base"] = $weewxrt[52];
$sky["cloud_cover"] = round(97.0,2,PHP_ROUND_HALF_UP);
if(is_numeric($sky["cloud_cover"]) != 1){$sky["cloud_cover"] = 0;}

//solar
$solar["threshold"] = round(522.9725177370117,0,PHP_ROUND_HALF_UP);
$solar["sun_duration_minutes"] = 260.09508927963896;
$solar["sun_duration_hours_minutes"] = intdiv($solar["sun_duration_minutes"], 60).'hr:'. ($solar["sun_duration_minutes"] % 60).'min';
$solar["sunshine_hours_yesterday"] = 0.0;
$solar["sunshine_minutes_yesterday"] = $solar["sunshine_hours_yesterday"] * 60;
$solar["sunshine_hours_minutes_yesterday"] = intdiv($solar["sunshine_minutes_yesterday"], 60).'hr:'. ($solar["sunshine_minutes_yesterday"] % 60).'min';
$solar["now"] = 555;
$solar["day_max"] = 663;
$solar["day_maxtime"] = date('H:i',1724417066);
$solar["yesterday_max"] = 789;
$solar["yesterday_maxtime"] = date('H:i',1724335959);
$solar["month_max"] = 959;
$solar["month_maxtime"] = date('D j H:i',1723708977);
$solar["year_max"] = 2148;
$solar["year_maxtime"] = date('D j M H:i',1720350455);
$solar["alltime_max"] = 2148;
$solar["alltime_maxtime"] = date('j M Y H:i',1720350455);

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
$temp["indoor_trend"] = round(0.28055555555550526,1);
if(is_numeric($temp["indoor_trend"]) != 1){$temp["indoor_trend"] = 0;}
$temp["indoor_day_max"] = 20.9;
$temp["indoor_day_min"] = 20.6;
$temp["outside_now"] = $weewxrt[2];
$temp["outside_trend"] = round(3.0750000000000064,1);
if(is_numeric($temp["outside_trend"]) != 1){$temp["outside_trend"] = 0;}
$temp["apptemp"] = $weewxrt[54];
$temp["heatindex"] = $weewxrt[41];
$temp["windchill"] = round($weewxrt[24],1);
$temp["humidex"] = $weewxrt[42];
$temp["outside_24h_max"] = 22.7;
$temp["outside_day_avg_60mn"] = 19.3;
$temp["outside_24h_maxtime"] = date('H:i',1724342036);
$temp["outside_24h_min"] = 15.1;
$temp["outside_24h_mintime"] = date('H:i',1724281201);
$temp["outside_yesterday_max"] = 22.7;
$temp["outside_yesterday_maxtime"] = date('H:i',1724342036);
$temp["outside_yesterday_min"] = 15.1;
$temp["outside_yesterday_mintime"] = date('H:i',1724281201);
$temp["outside_day_avg"] = 16.7;
$temp["outside_day_max"] = 20.3;
$temp["outside_day_maxtime"] = date('H:i',1724416690);
$temp["outside_day_min"] = 15.4;
$temp["outside_day_mintime"] = date('H:i',1724381586);
$temp["outside_month_max"] = 30.7;
$temp["outside_month_maxtime"] = date('D j H:i',1723469136);
$temp["outside_month_min"] = 6.8;
$temp["outside_month_mintime"] = date('D j H:i',1724043289);
$temp["outside_year_max"] = 31.5;
$temp["outside_year_maxtime"] = date('D j M H:i',1721402645);
$temp["outside_year_maxtime2"] = date('M',1721402645);
$temp["outside_year_min"] = -6.1;
$temp["outside_year_mintime"] = date('D j M H:i',1705547400);
$temp["outside_year_mintime2"] = date('M',1705547400);
$temp["outside_alltime_max"] = 38.7;
$temp["outside_alltime_maxtime"] = date('j M Y H:i',1658239200);
$temp["outside_alltime_min"] = -11.5;
$temp["outside_alltime_mintime"] = date('j M Y H:i',1671093000);
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
$uv["day_max"] = 5.0;
$uv["day_maxtime"] = date('H:i',1724406802);
$uv["yesterday_max"] = 5.8;
$uv["yesterday_maxtime"] = date('H:i',1724335921);
$uv["month_max"] = 7.4;
$uv["month_maxtime"] = date('D j H:i',1722690373);
$uv["year_max"] = 12.4;
$uv["year_maxtime"] = date('D j M H:i',1720350450);
$uv["alltime_max"] = 12.4;
$uv["alltime_maxtime"] = date('j M Y H:i',1720350450);

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
$wind["direction_10m_avg"] = "271";
$wind["cardinal"] = $weewxrt[11];    
$wind["speed"] = $weewxrt[6];
$wind["gust"] = $weewxrt[57];
$wind["speed_bft"] = $weewxrt[12];
$wind["speed_max"] = 3.5;
$wind["speed_maxtime"] = date('H:i',1724405100);
$wind["gust_max"] = 9.0;
$wind["gust_maxtime"] = date('H:i',1724388506);
$wind["wind_run"] = $weewxrt[17];
$wind["speed_10m_avg"] = 1.5;
$wind["speed_10m_max"] = 1.5;
$wind["gust_10m_max"] = 5.0;
$wind["speed_yesterday_max"] = 3.1;
$wind["speed_yesterday_maxtime"] = date('H:i',1724330400);
$wind["gust_yesterday_max"] = 9.8;
$wind["gust_yesterday_maxtime"] = date('H:i',1724322707);
$wind["speed_month_max"] = 4.3;
$wind["speed_month_maxtime"] = date('M',1724151300);
$wind["gust_month_max"] = 10.8;
$wind["gust_month_maxtime"] = date('D j M H:i',1724151293);
$wind["speed_year_max"] = 6.7;
$wind["speed_year_maxtime"] = "15/04/24 11:50:00";
$wind["gust_year_max"] = 18.4;
$wind["gust_year_maxtime"] = date('D j M H:i',1705868400);
$wind["speed_alltime_max"] = 9.6;
$wind["speed_alltime_maxtime"] = "31/03/22 13:05:00";
$wind["gust_alltime_max"] = 22.0;
$wind["gust_alltime_maxtime"] = date('D j M Y H:i',1703715600);


//colors

//air density
$colorAirDensity = "#007fff";

//barometer almanac
$colorBarometerCurrent = "#FF0000";
$colorBarometerDayMax = "#FF0000";
$colorBarometerDayMin = "#FF0000";
$colorBarometerYesterdayMax = "#E90076";
$colorBarometerYesterdayMin = "#FF0000";
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

$colorOutTempDayAvg = "#eba141";

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
$colorOutTempDayMax = "#ec5a34";
$colorOutTempDayMin = "#eba141";
$colorDewpointDayMax = "#eba141";
$colorDewpointDayMin = "#9aba2f";

//yesterday
$colorOutTempYesterdayMax = "#ec5a34";
$colorOutTempYesterdayMin = "#eba141";
$colorDewpointYesterdayMax = "#eba141";
$colorDewpointYesterdayMin = "#eba141";

//month
$colorOutTempMonthMax = "#d65b4a";
$colorOutTempMonthMin = "#9aba2f";
$colorDewpointMonthMax = "#ec5a34";
$colorDewpointMonthMin = "#9aba2f";

//year
$colorOutTempYearMax = "#d65b4a";
$colorOutTempYearMin = "#487ea9";
$colorDewpointYearMax = "#ec5a34";
$colorDewpointYearMin = "#487ea9";

//alltime
$colorOutTempAlltimeMax = "#dc4953";
$colorOutTempAlltimeMin = "#8781bd";
$colorDewpointAlltimeMax = "#ec5a34";
$colorDewpointAlltimeMin = "#8781bd";

//60min
$color["outTemp_60min_avg"] = "#eba141";

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
$color["windSpeed_max"] = "#0f94a7";
$color["windGust_max"] = "#c2863e";
$color["windSpeed_avg"] = "#6e90d0";
$color["windSpeed_10min_avg"] = "#7e98bb";
$color["windGust_10min_max"] = "#39a239";
$color["windSpeed_yesterday_max"] = "#0f94a7";
$color["windGust_yesterday_max"] = "#c2863e";
$color["windSpeed_month_max"] = "#0f94a7";
$color["windGust_month_max"] = "#c2863e";
$color["windSpeed_year_max"] = "#39a239";
$color["windGust_year_max"] = "#af5088";
$color["windSpeed_alltime_max"] = "#c2863e";
$color["windGust_alltime_max"] = "#754a92";

//rain color
$colorStormRain ="#615884";

if($rain["total"] == 0 && $theme == 'dark'){$colorRainDaySum ="#C0C0C0";}
else if($rain["total"] == 0){$colorRainDaySum ="#3A3D40";}
else {$colorRainDaySum = "#615884";}
$colorRainYesterdaySum = "#3A3D40";
$colorRain1hrSum = "#3A3D40";
$colorRain24hrSum = "#615884"; 
$colorRainMonthSum = "#0b8c88";
$colorRainYearSum = "#af5088";
$colorRainAlltimeSum = "#eba141";

if($rain["rate"] == 0 && $theme == 'dark'){$colorRainRate ="#C0C0C0";}
else if($rain["rate"] == 0){$colorRainRate ="#3A3D40";}
else {$colorRainRate = "#3A3D40";}
$colorRainRateDayMax = "#34758e";
$colorRainRateYesterdayMax = "#3A3D40";
$colorRainRate24hrMax = "#34758e"; 
$colorRainRateMonthMax = "#34758e";
$colorRainRateYearMax = "#a79d51";
$colorRainRateAlltimeMax = "#9f7f3a";


$colorUVCurrent = "#fed42d";
$colorUVDayMax = "#fed42d";
$colorUVYesterdayMax = "#fed42d";
$colorUVMonthMax = "#fd8620";
$colorUVYearMax = "#de257b";
$colorUVAlltimeMax = "#de257b";

$colorSolarCurrent = "#f8d747";
$colorSolarDayMax = "#f36633";
$colorSolarYesterdayMax = "#f36633";
$colorSolarMonthMax = "#b7161b";
$colorSolarYearMax = "grey";
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
