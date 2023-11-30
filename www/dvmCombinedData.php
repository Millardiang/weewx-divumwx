 	
          

    
            


  

<?php

include ('fixedSettings.php');
include ('dvmShared.php');
include('common.php');

error_reporting(0);

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
$rain["alltime_total"] = "2914.5mm";
$sundial_time = date("M d Y H:i:s",filemtime('serverdata/dvmRealtime.txt'));        
$stationlocation = "Steeple Claydon, UK";
$hardware = "GW1000";
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
$divum["swversion"] = "5.0.0b16";
$divum["build"] = $weewxrt[38];
$divum["since"] = "20 May 2020 23:10";
$stationUptime = "1 day, 21 hours, 59 minutes";

//almanac
$alm["sun_altitude"] = round(-45.155388055856875,2);
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
$alm["sun_azimuth"] = 295.6886153592301;
$alm["moon_azimuth"] = 73.13372461334544;
$alm["sunrise"] = "07:46";
$alm["sunset"] = "15:57";
$alm["sunrise_date"] = "Nov 30 2023 15:57:57";
$alm["sunset_date"] = "Nov 30 2023 15:57:57";
$alm["sun_right_ascension"] = 246.64070689052846;
$alm["next_equinox"] = "20/03/24 03:06:22";
$alm["next_solstice"] = "22/12/23 03:27:09";
$alm["sidereal_time"] = 23.485511000834727;
$alm["civil_twilight_begin"] = "07:06";
$alm["civil_twilight_end"] = "16:37";
$alm["nautical_twilight_begin"] = "06:25";
$alm["nautical_twilight_end"] = "17:19";
$alm["astronomical_twilight_begin"] = "05:44";
$alm["astronomical_twilight_end"] = "17:59";
$alm["sun_meridian_transit"] = "11:52";
$alm["moon_meridian_transit"] = "02:15";
$alm["moonphase"] = "Waning gibbous";
$alm["moonphase_number"] =  5;
$alm["moon_age"] = 17.4809916282252;
$alm["hour_sun"] = 2.3883923959715143;
$alm["hour_moon"] = 4.714994740122915;
$alm["luminance"] = round(87.15122243598374,2);
$alm["fullmoon"] = "27 Dec 00:33";
$alm["newmoon"] = "12 Dec 23:31";   
$alm["daylight"] = "08:11";
$alm["moonrise"] = "18:06";
$alm["moonset"] = "11:23";
$alm["mercury_hlongitude"] = 333.1592790327302;
$alm["venus_hlongitude"] = 135.92942516312132;
$alm["earth_hlongitude"] = 68.39233023492766;
$alm["mars_hlongitude"] = 242.17034026851502;
$alm["jupiter_hlongitude"] = 43.01984403448776;
$alm["saturn_hlongitude"] = 336.8909392253883;
$alm["uranus_hlongitude"] = 51.253951267639955;
$alm["neptune_hlongitude"] = 356.7116017816317;
$alm["pluto_hlongitude"] = 299.74968191517956;

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

//air quality
$air["pm_units"] = "g/m<sup><b>3</b></sup>";
$air["current.pm2_5"] = 1.9;
$air["24h.rollingavg.pm2_5"] = 1.9;
$air["current.pm10_0"] = 2.8;
$air["24h.rollingavg.pm10_0"] = 2.8;

//barometer

$barom["units"] = "hPa";
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
$barom["now"] = $weewxrt[10];
$barom["max"] = 1003.4;
$barom["maxtime"] = date('H:i',"1701216561");
$barom["min"] = 999.7;
$barom["mintime"] = date('H:i',"1701302067");
$barom["trend_code"] = round(0.8708333333323708,0);
if($barom["trend_code"]>3){$barom["trend_desc"]="Rising Very Rapidly";$barom["trend_code"]=4;}
else if($barom["trend_code"]>2){$barom["trend_desc"]="Rising Quickly";$barom["trend_code"]=3;}
else if($barom["trend_code"]>1){$barom["trend_desc"]="Rising";$barom["trend_code"]=2;}
else if($barom["trend_code"]>0){$barom["trend_desc"]="Rising Slowly";$barom["trend_code"]=1;}
else if($barom["trend_code"]==0){$barom["trend_desc"]="Steady";$barom["trend_code"]=0;}
else if($barom["trend_code"]<0){$barom["trend_desc"]="Falling Slowly";$barom["trend_code"]=-1;}
else if($barom["trend_code"]<-1){$barom["trend_desc"]="Falling";$barom["trend_code"]=-2;}
else if($barom["trend_code"]<-2){$barom["trend_desc"]="Falling Quickly";$barom["trend_code"]=-3;}
else if($barom["trend_code"]<-3){$barom["trend_desc"]="Falling Very Rapidly";$barom["trend_code"]=-4;}

$barom["24h_max"] = 1005.3;
$barom["24h_maxtime"] = date('H:i', "1701216561");
$barom["24h_min"] = 1000.3;
$barom["24h_mintime"] = date('H:i', "1701302067");
$barom["month_max"] =  1028.7;
$barom["month_maxtime"] ="21/11/23 23:59:24";
$barom["month_min"] = 955.7;
$barom["month_mintime"] = "02/11/23 07:26:23";
$barom["year_max"] = 1044.5;
$barom["year_maxtime"] = "05/02/23 08:40:00";
$barom["year_min"] = 955.7;
$barom["year_mintime"] = "02/11/23 07:26:23";
$barom["alltime_max"] = 1044.5;
$barom["alltime_maxtime"] = "05/02/23 08:40:00";
$barom["alltime_min"] = 955.7;
$barom["alltime_mintime"] = "02/11/23 07:26:23";

//colors
//temperature almanac
//day
$t[0] = "#369cac";
$t[1] = "#487ea9";
$t[2] = "#369cac";
$t[3] = "#487ea9";
//yesterday
$d[0] = "#369cac";
$d[1] = "#487ea9";
$d[2] = "#369cac";
$d[3] = "#487ea9";
//month
$m[0] = "#eba141";
$m[1] = "#487ea9";
$m[2] = "#eba141";
$m[3] = "#487ea9";
//year
$y[0] = "#d65b4a";
$y[1] = "#487ea9";
$y[2] = "#ec5a34";
$y[3] = "#487ea9";
//alltime
$a[0] = "#dc4953";
$a[1] = "#8781bd";
$a[2] = "#ec5a34";
$a[3] = "#8781bd";
//current conditions
$color["outTemp_60min_avg"] = "#487ea9";
$color["windSpeed_10min_avg"] = "#7e98bb";
$color["windGust_10min_max"] = "#0f94a7";

        //dewpoint
$dew["now"] = $weewxrt[4];
$dew["trend"] = -0.7;
$dew["day_max"] = 2.2;
$dew["day_maxtime"] = date('H:i', 1701346978);
$dew["day_min"] = -4.4;
$dew["day_mintime"] = date('H:i',1701316090);
$dew["24h_max"] = 2.0;
$dew["24h_maxtime"] = date('H:i',1701257280);
$dew["24h_min"] = -2.7;
$dew["24h_mintime"] = date('H:i',1701300411);
$dew["month_max"] = 13.7;
$dew["month_maxtime"] = date('D j H:i', 1700307077);
$dew["month_min"] = -4.4;
$dew["month_mintime"] = date('D j H:i',1701316090);
$dew["year_max"] = 20.4;
$dew["year_maxtime"] = date('D j H:i', 1694349339);
$dew["year_min"] = -8.7; 
$dew["year_mintime"] = date('D j H:i',1674372300); 
$dew["alltime_max"] = 20.7;
$dew["alltime_maxtime"] = date('j M Y H:i', 1597068300);
$dew["alltime_min"] = -13.1;
$dew["alltime_mintime"] = date('j M Y H:i',1671093000);
    
    //humidity
$humid["now"] = $weewxrt[3];
$humid["trend"] =  3;
$humid["day_max"] = 99;
$humid["day_maxtime"] = date('H:i', 1701302401);
$humid["day_min"] = 81;
$humid["day_mintime"] = date('H:i', 1701350237);
$humid["24h_max"] = 99;
$humid["24h_maxtime"] = date('H:i',1701216004);
$humid["24h_min"] = 83;
$humid["24h_mintime"] =date('H:i',  1701265165);
$humid["month_max"] = 99;
$humid["month_maxtime"] = date('D j H:i',1698796802);
$humid["month_min"] = 57;
$humid["month_mintime"] = date('D j H:i',1700833634);
$humid["year_max"] = 99;
$humid["year_maxtime"] = date('D j M H:i',1672620600);
$humid["year_min"] = 29;
$humid["year_mintime"] = date('D j H:i',1682001300);
$humid["alltime_max"] = 99;
$humid["alltime_maxtime"] = date('j M Y H:i',1597530000);
$humid["alltime_min"] = 18;
$humid["alltime_mintime"] =date('j M Y H:i',1658235900);
$humid["indoors_now"] = $weewxrt[23];
$humid["indoors_trend"] = round(0,1);
$humid["indoors_day_max"] = 62;
$humid["indoors_day_maxtime"] = date('H:i', 1701302401);
$humid["indoors_day_min"] = 61;
$humid["indoors_day_mintime"] = date('H:i', 1701316587);
$humid["indoors_24h_max"] = 64;
$humid["indoors_24h_maxtime"] = date('H:i',1701216004);
$humid["indoors_24h_min"] = 44;
$humid["indoors_24h_mintime"] =date('H:i',  1701242901);
$humid["indoors_month_max"] = 78;
$humid["indoors_month_maxtime"] = date('D j H:i',1700422850);
$humid["indoors_month_min"] = 42;
$humid["indoors_month_mintime"] = date('D j H:i',1700985610);
$humid["indoors_year_max"] = 82;
$humid["indoors_year_maxtime"] = date('D j M H:i',1694205998);
$humid["indoors_year_min"] = 38;
$humid["indoors_year_mintime"] = date('D j H:i',1676049900);
$humid["indoors_alltime_max"] = 89;
$humid["indoors_alltime_maxtime"] = date('j M Y H:i',1597605300);
$humid["indoors_alltime_min"] = 24;
$humid["indoors_alltime_mintime"] =date('j M Y H:i',1598722200);

    //lightning
$lightning["last_strike_time"] = "1698854728";
$lightning["current_strike_count"] = "0"; 
$lightning["hour_strike_count"] ="0";  
$lightning["day_strike_count"] = "0";
$lightning["month_strike_count"] = "3";
$lightning["year_strike_count"] = "1842";
$lightning["last_time"] = "1698854728";
$lightning["alltime_strike_count"] = "2300";
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
$rain["units"] = "mm";
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
$rain["rate"] = $weewxrt[8];
$rain["current"] = $weewxrt[9];
$rain["total"] = $weewxrt[9];
$rain["last_hour"] = 0.0;
$rain["last_10min"] = 0.0;
$rain["last_24hour"] = 0.0;
$rain["24h_rate_max"] = 0.0;
$rain["24h_rate_maxtime"] = 1701216004;
$rain["24h_total"] = 0.0;
$rain["day"] = round(0.3000000000000682, 1);
$rain["day_rate_max"] = 1.7999999981639998;
$rain["day_rate_maxtime"] = 1701337821;
$rain["day_total"] = 0.3000000000000682;
$rain["month_rate_max"] = 15.599999984087997;
$rain["month_rate_maxtime"] = 1699113160;
$rain["month_total"] = 85.70000000000002;
$rain["year_rate_max"] = 111.59999988616799;
$rain["year_rate_maxtime"] = 1686499495;
$rain["year_total"] = 738.4;
$rain["alltime_rate_max"] = 111.59999988616799;
$rain["alltime_rate_maxtime"] = 1686499495;
$rain["alltime_total"] = round(2914.4789999999985,1);
$rain["storm_rain"] = 0.0;

//sky
$sky["lux"] = round($weewxrt[45] / 0.00809399477, 0 ,PHP_ROUND_HALF_UP);
$sky["day_lux_max"] = round(155/ 0.00809399477, 0 ,PHP_ROUND_HALF_UP);
$sky["cloud_base"] = $weewxrt[52];
$sky["cloud_cover"] = round(14.500000,0);

    //solar
$solar["now"] = 0;
$solar["day_max"] = 155;
$solar["day_maxtime"] = "11:47";
$solar["24h_max"] =  110;
$solar["24h_maxtime"] =  "13:19";
$solar["month_max"] = 361;
$solar["month_maxtime"] = "11:26";
$solar["year_max"] = 2368;
$solar["year_maxtime"] = "10:42";
$solar["alltime_max"] = 2368;
$solar["alltime_maxtime"] = "10:42";

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
$temp["indoor_trend"] = round(-0.2999999999999847,1);
$temp["indoor_day_max"] = 11.9;
$temp["indoor_day_min"] = 9.7;
$temp["outside_now"] = $weewxrt[2];
$temp["outside_trend"] = round(-1.1497652582159725,1);
$temp["apptemp"] = $weewxrt[54];
$temp["heatindex"] = $weewxrt[41];
$temp["windchill"] = round($weewxrt[24],1);
$temp["humidex"] = $weewxrt[42];
$temp["outside_24h_max"] = 3.0;
$temp["outside_day_avg_60mn"] = -0.6;
$temp["outside_24h_maxtime"] =  date('H:i',1701265127);
$temp["outside_24h_min"] = -2.6;
$temp["outside_24h_mintime"] =  date('H:i',1701300411);
$temp["outside_day_avg"] = -0.5;
$temp["outside_day_max"] = 4.0;
$temp["outside_day_maxtime"] = date('H:i',1701349843);
$temp["outside_day_min"] = round(-4.0);
$temp["outside_day_mintime"] = date('H:i',1701316090);
$temp["outside_month_max"] = 14.3;
$temp["outside_month_maxtime"] = date('D j H:i',1699865302);
$temp["outside_month_min"] = -4.0;
$temp["outside_month_mintime"] = date('D j H:i',1701316090);
$temp["outside_year_max"] = 31.5;
$temp["outside_year_maxtime"] = date('D j M H:i',1694098233);
$temp["outside_year_min"] = -7.8;
$temp["outside_year_mintime"] = date('D j M H:i',1674372300);
$temp["outside_year_mintime2"] = date('D j M H:i',1674372300);
$temp["outside_alltime_max"] = 38.7;
$temp["outside_alltime_maxtime"] = date('j M Y H:i',1658239200);
$temp["outside_alltime_min"] = -11.5;
$temp["outside_alltime_mintime"] = date('j M Y H:i',1671093000);
$Temp = $temp["indoor_now"];
$Humidity = $humid["indoors_now"];
$T = $Temp*9/5+32;
$RH = $Humidity;
$HI = 0;
if($T <= 40.0) {$HI = $T;}
else {$HI = -42.379 + (2.04901523*$T) + (10.14333127*$RH) - (0.22475541*$T*$RH) - (0.00683783*$T*$T) - (0.05481717*$RH*$RH) + (0.00122874*$T*$T*$RH) + (0.00085282*$T*$RH*$RH) - (0.00000199*$T*$T*$RH*$RH);}
if ($RH < 13 && $T >= 80 && $T <= 112){
        $adjust = ((13 - $RH)/4)  * sqrt(17 - abs($T - 95) / 17);
        $HI = $HI - $adjust;
    }
else if ($RH > 85 && $T >= 80 && $T <= 87) {$adjust = (($RH-85)/10) * ((87-$T)/5); $HI = $HI+$adjust;}
else if ($T < 80){$HI = 0.5 * ($T + 61.0 + (($T-68.0)*1.2) + ($RH*0.094));}

    //$temp["indoor_now_feels"] = number_format(($HI - 32) * 5/9, 1);
$temp["indoor_now_feels"] = number_format($HI, 1);


   //uv
$uv["now"] = $weewxrt[43];
$uv["day_max"] = 0.8;
$uv["day_maxtime"] = date('H:i',1701343200);
$uv["24h_max"] = 0.8;
$uv["24h_maxtime"] = date('H:i',1701264000);
$uv["month_max"] = 2.5;
$uv["month_maxtime"] = date('D j H:i',1698842248);
$uv["year_max"] = 12.4;
$uv["year_maxtime"] = date('D j M H:i',1687167751);
$uv["alltime_max"] = 12.4;
$uv["alltime_maxtime"] = date('j M Y H:i',1687167751);

    //wind
$wind["units"] = "m/s"; // m/s or mph or km/h or kts
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
$wind["direction"] = $weewxrt[7];
$wind["direction_and_symbol"] = $wind["direction"]."\u00B0"; 
$wind["direction_10m_avg"] = $adata["wind"]["10min windDir avg"]["value"];
$wind["cardinal"] = $weewxrt[11];
$wind["direction_avg"] = "027";    
$wind["speed"] = $weewxrt[6];
$wind["gust"] = $weewxrt[64];
$wind["speed_bft"] = $weewxrt[12];
$wind["speed_max"] =  3.6;
$wind["speed_maxtime"] = "13:40:00";
$wind["gust_max"] = 7.6;
$wind["gust_maxtime"] = "13:39:12";
$wind["wind_run"] = $weewxrt[17];
$wind["speed_10m_avg"] = "027";
$wind["speed_10m_max"] = 1.9;
$wind["gust_10m_max"] =   3.2;
$wind["speed_24h_max"] = 1.5;
$wind["speed_24h_maxtime"] = "13:25:00";
$wind["gust_24h_max"] =  2.9;
$wind["gust_24h_maxtime"] = "11:55:46";
$wind["speed_month_max"] = 5.6;
$wind["speed_month_maxtime"] = "21/11/23 12:20:00";
$wind["speed_month_maxtime2"] = "21/11/23 12:20:00";
$wind["gust_month_max"] = 11.2;
$wind["gust_month_maxtime"] = "24/11/23 12:17:37";
$wind["speed_year_max"] = 7.2;
$wind["speed_year_maxtime"] = "25/02/23 14:30:00";
$wind["gust_year_max"] = 15.3;
$wind["gust_year_maxtime"] = "15/07/23 14:23:04";
$wind["gust_year_maxtime2"] = "15/07/23 14:23:04";
$wind["speed_alltime_max"] = 9.6;
$wind["speed_alltime_maxtime"] =  "31/03/22 13:05:00";
$wind["gust_alltime_max"] = 21.4;
$wind["gust_alltime_maxtime"] = "18/02/22 14:50:00";
$wind["direction_trend"] = "-09";
$wind["direction_trend_ordinal"] = "N";

// Convert temperatures if necessary
include('dvmUnitConversions.php');

include('dvmMeteorShowers.php');

$firerisk = number_format((((110 - 1.373 * $divum["humidity"]) - 0.54 * (10.20 - $divum["temp"])) * (124 * pow(10, (-0.0142 * $divum["humidity"])))) / 60, 0);

//wetbulb
$Tc = ($divum['temp']);
$P = $divum['barometer'];
$RH = $divum['humidity'];
$Tdc = (($Tc - (14.55 + 0.114 * $Tc) * (1 - (0.01 * $RH)) - pow((2.5 + 0.007 * $Tc) * (1 - (0.01 * $RH)) , 3) - (15.9 + 0.117 * $Tc) * pow(1 - (0.01 * $RH) , 14)));
$E = (6.11 * pow(10, (7.5 * $Tdc / (237.7 + $Tdc))));
$wetbulbcalc = (((0.00066 * $P) * $Tc) + ((4098 * $E) / pow(($Tdc + 237.7) , 2) * $Tdc)) / ((0.00066 * $P) + (4098 * $E) / pow(($Tdc + 237.7) , 2));
$wetbulbx = number_format($wetbulbcalc, 1);


?>