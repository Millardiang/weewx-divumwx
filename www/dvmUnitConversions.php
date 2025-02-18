<?php 

//error_reporting(0);
//$copyYear = 2015;
//$curYear = date('Y');
//$copyrightcredit='© divumwx.com original CSS/SVG/PHP '.$copyYear . (($copyYear != $curYear) ? '-' . $curYear : 'Copyright');


// Convert temperatures if necessary
if ($tempunit != $temp["units"])
{
    if ($tempunit == "C")
    {
        fToC($temp, "indoor_now");
        fToC($temp, "outside_now");
        fToC($temp, "outside_day_avg");
        fToC($temp, "apptemp");
        fToC($temp, "windchill");
        fToC($temp, "heatindex");
        fToC($dew, "now");
        fToC($temp, "outside_day_avg_60mn");
        fToC($temp, "outside_day_max");
        fToC($temp, "outside_day_min");
        fToC($temp, "indoor_day_max");
        fToC($temp, "indoor_day_min");
        fToC($temp, "outside_24h_max");
        fToC($temp, "outside_24h_min");
        fToC($temp, "outside_month_max");
        fToC($temp, "outside_month_min");
        fToC($temp, "outside_year_max");
        fToC($temp, "outside_year_min");
        fToC($temp, "outside_yesterday_max");
        fToC($temp, "outside_yesterday_min");
        fToC($temp, "outside_alltime_max");
        fToC($temp, "outside_alltime_min");
        fToC($dew, "day_max");
        fToC($dew, "day_min");
        fToC($dew, "24h_max");
        fToC($dew, "24h_min");
        fToC($dew, "alltime_max");
        fToC($dew, "alltime_min");
        fToC($dew, "month_max");
        fToC($dew, "month_min");
        fToC($dew, "year_max");
        fToC($dew, "year_min");
        fToC($dew, "yesterday_max");
        fToC($dew, "yesterday_min");
        fToCrel($temp, "outside_trend");
        fToCrel($dew, "trend");
        fToCrel($temp, "humidex");
        $temp["units"] = $tempunit;
    }
    else if ($tempunit == "F")
    {
        cToF($temp, "indoor_now");
        cToF($temp, "outside_now");
        cToF($temp, "outside_day_avg");
        cToF($temp, "apptemp");
        cToF($temp, "windchill");
        cToF($temp, "heatindex");
        cToF($dew, "now");
        cToF($temp, "outside_day_avg_60mn");
        cToF($temp, "outside_yesterday_max");
        cToF($temp, "outside_yesterday_min");
        cToF($temp, "outside_alltime_max");
        cToF($temp, "outside_alltime_min");
        cToF($temp, "outside_month_max");
        cToF($temp, "outside_month_min");
        cToF($temp, "outside_year_max");
        cToF($temp, "outside_year_min");
        cToF($temp, "outside_day_max");
        cToF($temp, "outside_day_min");
        cToF($temp, "indoor_day_max");
        cToF($temp, "indoor_day_min");
        cToF($temp, "outside_24h_max");
        cToF($temp, "outside_24h_min");
        cToF($dew, "day_max");
        cToF($dew, "day_min");
        cToF($dew, "24h_max");
        cToF($dew, "24h_min");
        cToF($dew, "alltime_max");
        cToF($dew, "alltime_min");
        cToF($dew, "month_max");
        cToF($dew, "month_min");
        cToF($dew, "year_max");
        cToF($dew, "year_min");
        cToF($dew, "yesterday_max");
        cToF($dew, "yesterday_min");
        cToFrel($temp, "outside_trend");
        cToFrel($dew, "trend");
        cToFrel($temp, "humidex");
        $temp["units"] = $tempunit;
    }
}
//  convert rain

if ($rainunit != $rain["units"])
{
    if ($rainunit == "mm")
    {
        inTomm($rain, "dayRain");
        inTomm($rain, "current");
        inTomm($rain, "rate");
        inTomm($rain, "total");
        inTomm($rain, "last_10min");
        inTomm($rain, "last_hour");
        inTomm($rain, "last_3hour");
        inTomm($rain, "last_24hour");
        inTomm($rain, "day");
        inTomm($rain, "yesterday_rate_max");
        inTomm($rain, "yesterday_total");
        inTomm($rain, "month_rate_max");
        inTomm($rain, "month_total");
        inTomm($rain, "monthRain");
        inTomm($rain, "year_rate_max");
        inTomm($rain, "year_total");
        inTomm($rain, "yearRain");
        inTomm($rain, "alltime_rate_max");
        inTomm($rain, "alltime_total");
        inTomm($rain, "t_dayRain");
        inTomm($rain, "t_rate");
        inTomm($rain, "t_total");
        inTomm($rain, "t_last_10min");
        inTomm($rain, "t_last_hour");
        inTomm($rain, "t_last_3hour");
        inTomm($rain, "t_last_24hour");
        inTomm($rain, "t_day");
        inTomm($rain, "t_yesterday_rate_max");
        inTomm($rain, "t_yesterday_total");
        inTomm($rain, "t_month_rate_max");
        inTomm($rain, "t_month_total");
        inTomm($rain, "t_monthRain");
        inTomm($rain, "t_year_rate_max");
        inTomm($rain, "t_year_total");
        inTomm($rain, "t_yearRain");
        inTomm($rain, "t_alltime_rate_max");
        inTomm($rain, "t_alltime_total");
        $rain["units"] = $rainunit;
    }
    else if ($rainunit == "in")
    {
        mmToin($rain, "dayRain");
        mmToin($rain, "rate");
        mmToin($rain, "total");
        mmToin($rain, "last_10min");
        mmToin($rain, "last_hour");
        mmToin($rain, "last_3hour");
        mmToin($rain, "last_24hour");
        mmToin($rain, "day");
        mmToin($rain, "yesterday_rate_max");
        mmToin($rain, "yesterday_total");
        mmToin($rain, "month_rate_max");
        mmToin($rain, "month_total");
        mmToin($rain, "monthRain");
        mmToin($rain, "year_rate_max");
        mmToin($rain, "year_total");
        mmToin($rain, "yearRain");
        mmToin($rain, "alltime_rate_max");
        mmToin($rain, "alltime_total");
        mmToin($rain, "t_dayRain");
        mmToin($rain, "t_rate");
        mmToin($rain, "t_total");
        mmToin($rain, "t_last_10min");
        mmToin($rain, "t_last_hour");
        mmToin($rain, "t_last_3hour");
        mmToin($rain, "t_last_24hour");
        mmToin($rain, "t_day");
        mmToin($rain, "t_yesterday_rate_max");
        mmToin($rain, "t_yesterday_total");
        mmToin($rain, "t_month_rate_max");
        mmToin($rain, "t_month_total");
        mmToin($rain, "t_monthRain");
        mmToin($rain, "t_year_rate_max");
        mmToin($rain, "t_year_total");
        mmToin($rain, "t_yearRain");
        mmToin($rain, "t_alltime_rate_max");
        mmToin($rain, "t_alltime_total");
        $rain["units"] = $rainunit;
    }
}
// Convert pressure units if necessary
if ($pressureunit != $barom["units"])
{
    if (($pressureunit == 'hPa' && $barom["units"] == 'mbar') || ($pressureunit == 'mbar' && $barom["units"] == 'hPa') || ($pressureunit == 'kPa' && $barom["units"] == 'mbar') || ($pressureunit == 'mbar' && $barom["units"] == 'kPa') || ($pressureunit == 'kPa' && $barom["units"] == 'hPa') || ($pressureunit == 'hPa' && $barom["units"] == 'kPa'))
    {
        // 1 mbar = 1 hPa so just change the unit being displayed
        $barom["units"] = $pressureunit;
    }
    else if ($pressureunit == "inHg" && ($barom["units"] == 'mbar' || $barom["units"] == 'hPa' || $barom["units"] == 'kPa'))
    {
        mbToin($barom, "now", ($barom["units"] == 'kPa' ? 1000 : 1));
        mbToin($barom, "max", ($barom["units"] == 'kPa' ? 1000 : 1));
        mbToin($barom, "min", ($barom["units"] == 'kPa' ? 1000 : 1));
        mbToin($barom, "trend", ($barom["units"] == 'kPa' ? 1000 : 1));
        mbToin($barom, "24h_max", ($barom["units"] == 'kPa' ? 1000 : 1));
        mbToin($barom, "24h_min", ($barom["units"] == 'kPa' ? 1000 : 1));
        mbToin($barom, "day_max", ($barom["units"] == 'kPa' ? 1000 : 1));
        mbToin($barom, "day_min", ($barom["units"] == 'kPa' ? 1000 : 1));
        mbToin($barom, "month_max", ($barom["units"] == 'kPa' ? 1000 : 1));
        mbToin($barom, "month_min", ($barom["units"] == 'kPa' ? 1000 : 1));
        mbToin($barom, "year_max", ($barom["units"] == 'kPa' ? 1000 : 1));
        mbToin($barom, "year_min", ($barom["units"] == 'kPa' ? 1000 : 1));
        mbToin($barom, "alltime_max", ($barom["units"] == 'kPa' ? 1000 : 1));
        mbToin($barom, "alltime_min", ($barom["units"] == 'kPa' ? 1000 : 1));
        $barom["units"] = $pressureunit;

    }
    else if (($pressureunit == "mbar" || $pressureunit == 'hPa' || $pressureunit == 'kPa') && $barom["units"] == 'inHg')
    {
        inTomb($barom, "now", ($pressureunit == 'kPa' ? 1000 : 1));
        inTomb($barom, "max", ($pressureunit == 'kPa' ? 1000 : 1));
        inTomb($barom, "min", ($pressureunit == 'kPa' ? 1000 : 1));
        inTomb($barom, "trend", ($pressureunit == 'kPa' ? 1000 : 1));
        inTomb($barom, "24h_max", ($pressureunit == 'kPa' ? 1000 : 1));
        inTomb($barom, "24h_min", ($pressureunit == 'kPa' ? 1000 : 1));
        inTomb($barom, "day_max", ($pressureunit == 'kPa' ? 1000 : 1));
        inTomb($barom, "day_min", ($pressureunit == 'kPa' ? 1000 : 1));
        inTomb($barom, "month_max", ($pressureunit == 'kPa' ? 1000 : 1));
        inTomb($barom, "month_min", ($pressureunit == 'kPa' ? 1000 : 1));
        inTomb($barom, "year_max", ($pressureunit == 'kPa' ? 1000 : 1));
        inTomb($barom, "year_min", ($pressureunit == 'kPa' ? 1000 : 1));
        inTomb($barom, "alltime_max", ($pressureunit == 'kPa' ? 1000 : 1));
        inTomb($barom, "alltime_min", ($pressureunit == 'kPa' ? 1000 : 1));
        $barom["units"] = $pressureunit;
    }
}
//Convert cloudbase
if ($windunit != $wind["units"])
{
    if (($windunit == 'mph' || $windunit == 'kts') && ($wind["units"] == 'm/s' || $wind["units"] == 'km/h'))
    {
        $divum["cloudbase3"] = round($divum["cloudbase3"] * 3.281, 0);
    }
    else if (($windunit == 'm/s' || $windunit == 'km/h') && ($wind["units"] == 'mph' || $wind["units"] == 'kts'))
    {
        $divum["cloudbase3"] = round($divum["cloudbase3"] / 3.281, 0);
    }
}
// Convert wind speed units if necessary
if ($windunit != $wind["units"])
{
    if ($windunit == 'mph' && $divum["wind_units"] == 'kts')
    {
        ktsTomph($wind, "speed_avg");
        ktsTomph($wind, "speed");
        ktsTomph($wind, "gust_10m_max");     
        ktsTomph($wind, "speed_10m_avg");
        ktsTomph($wind, "gust");
        ktsTomph($wind, "gust_60min");
        ktsTomph($wind, "speed_max");
        ktsTomph($wind, "gust_max");
        ktsTomph($wind, "gust_yesterday_max");
        ktsTomph($wind, "gust_month_max");
        ktsTomph($wind, "gust_year_max");       
        ktsTomph($wind, "gust_alltime_max");
        ktsTomph($wind, "speed_15min_avg");
        ktsTomph($wind, "speed_30min_avg");
        ktsTomph($wind, "speed_maxtime");
        ktsTomph($wind, "speed_day_avg");
        ktsTomph($wind, "speed_alltime_max");
        ktsTomph($wind, "speed_yesterday_max");
        ktsTomph($wind, "speed_month_max");
        ktsTomph($wind, "speed_year_max");
        ktsTomph($wind, "speed_day_max");
        $wind["units"] = $windunit;
    }
    else if ($windunit == 'mph' && $wind["units"] == 'km/h')
    {
        kmhTomph($wind, "speed_avg");
        kmhTomph($wind, "speed");
        kmhTomph($wind, "gust_10m_max");
        kmhTomph($wind, "speed_10m_avg");
        kmhTomph($wind, "gust");
        kmhTomph($wind, "gust_60min");
        kmhTomph($wind, "speed_max");
        kmhTomph($wind, "gust_max");
        kmhTomph($wind, "gust_yesterday_max");                
        kmhTomph($wind, "gust_month_max");
        kmhTomph($wind, "gust_year_max");
        kmhTomph($wind, "gust_alltime_max");
        kmhTomph($wind, "speed_15min_avg");
        kmhTomph($wind, "speed_30min_avg");
        kmhTomph($wind, "speed_maxtime");
        kmhTomph($wind, "speed_day_avg");
        kmhTomph($wind, "speed_alltime_max");
        kmhTomph($wind, "speed_yesterday_max");
        kmhTomph($wind, "speed_month_max");
        kmhTomph($wind, "speed_year_max");
        kmhTomph($wind, "speed_day_max");
        $wind["units"] = $windunit;
    }
    else if ($windunit == 'mph' && $wind["units"] == 'm/s')
    {
        msTomph($wind, "speed_avg");
        msTomph($wind, "speed");
        msTomph($wind, "gust_10m_max");
        msTomph($wind, "speed_10m_avg");
        msTomph($wind, "gust");
        msTomph($wind, "gust_60min");
        msTomph($wind, "speed_max");
        msTomph($wind, "gust_max");
        msTomph($wind, "gust_yesterday_max");        
        msTomph($wind, "gust_month_max");
        msTomph($wind, "gust_year_max");
        msTomph($wind, "gust_alltime_max");
        msTomph($wind, "speed_15min_avg");
        msTomph($wind, "speed_30min_avg");
        msTomph($wind, "speed_maxtime");
        msTomph($wind, "speed_day_avg");
        msTomph($wind, "speed_alltime_max");
        msTomph($wind, "speed_yesterday_max");
        msTomph($wind, "speed_month_max");
        msTomph($wind, "speed_year_max");
        msTomph($wind, "speed_day_max");
        $wind["units"] = $windunit;
    }
    else if ($windunit == 'km/h' && $wind["units"] == 'kts')
    {
        ktsTokmh($wind, "speed_avg");
        ktsTokmh($wind, "speed");
        ktsTokmh($wind, "gust_10m_max");
        ktsTokmh($wind, "speed_10m_avg");
        ktsTokmh($wind, "gust");
        ktsTokmh($wind, "gust_60min");
        ktsTokmh($wind, "speed_max");
        ktsTokmh($wind, "gust_max");
        ktsTokmh($wind, "gust_yesterday_max");        
        ktsTokmh($wind, "gust_month_max");
        ktsTokmh($wind, "gust_year_max");
        ktsTokmh($wind, "gust_alltime_max");
        ktsTokmh($wind, "speed_15min_avg");
        ktsTokmh($wind, "speed_30min_avg");
        ktsTokmh($wind, "speed_maxtime");
        ktsTokmh($wind, "speed_day_avg");
        ktsTokmh($wind, "speed_alltime_max");
        ktsTokmh($wind, "speed_yesterday_max");
        ktsTokmh($wind, "speed_month_max");
        ktsTokmh($wind, "speed_year_max");
        ktsTokmh($wind, "speed_day_max");
        $wind["units"] = $windunit;
    }
    else if ($windunit == 'km/h' && $wind["units"] == 'mph')
    {
        mphTokmh($wind, "speed_avg");
        mphTokmh($wind, "speed");
        mphTokmh($wind, "gust_10m_max");
        mphTokmh($wind, "speed_10m_avg");
        mphTokmh($wind, "gust");
        mphTokmh($wind, "gust_60min");
        mphTokmh($wind, "speed_max");
        mphTokmh($wind, "gust_max");
        mphTokmh($wind, "gust_yesterday_max");        
        mphTokmh($wind, "gust_month_max");
        mphTokmh($wind, "gust_year_max");
        mphTokmh($wind, "gust_alltime_max");
        mphTokmh($wind, "speed_15min_avg");
        mphTokmh($wind, "speed_30min_avg");
        mphTokmh($wind, "speed_maxtime");
        mphTokmh($wind, "speed_day_avg");
        mphTokmh($wind, "speed_alltime_max");
        mphTokmh($wind, "speed_yesterday_max");
        mphTokmh($wind, "speed_month_max");
        mphTokmh($wind, "speed_year_max");
        mphTokmh($wind, "speed_day_max");
        mphTokmh($lightning, "last_distance");
        $wind["units"] = $windunit;
    }
    else if ($windunit == 'km/h' && $wind["units"] == 'm/s')
    {
        msTokmh($wind, "speed_avg");
        msTokmh($wind, "speed");
        msTokmh($wind, "gust_10m_max");
        msTokmh($wind, "speed_10m_avg");
        msTokmh($wind, "gust");
        msTokmh($wind, "gust_60min");
        msTokmh($wind, "speed_max");
        msTokmh($wind, "gust_max");
        msTokmh($wind, "gust_yesterday_max");        
        msTokmh($wind, "gust_month_max");
        msTokmh($wind, "gust_year_max");
        msTokmh($wind, "gust_alltime_max");
        msTokmh($wind, "speed_15min_avg");
        msTokmh($wind, "speed_30min_avg");
        msTokmh($wind, "speed_maxtime");
        msTokmh($wind, "speed_day_avg");
        msTokmh($wind, "speed_alltime_max");
        msTokmh($wind, "speed_yesterday_max");
        msTokmh($wind, "speed_month_max");
        msTokmh($wind, "speed_year_max");
        msTokmh($wind, "speed_day_max");
        $wind["units"] = $windunit;
    }
    else if ($windunit == 'm/s' && $wind["units"] == 'kts')
    {
        ktsToms($wind, "speed_avg");
        ktsToms($wind, "speed");
        ktsToms($wind, "gust_10m_max");
        ktsToms($wind, "speed_10m_avg");
        ktsToms($wind, "gust");
        ktsToms($wind, "gust_60min");
        ktsToms($wind, "speed_max");
        ktsToms($wind, "gust_max");
        ktsToms($wind, "gust_yesterday_max");        
        ktsToms($wind, "gust_month_max");
        ktsToms($wind, "gust_year_max");
        ktsToms($wind, "gust_alltime_max");
        ktsToms($wind, "speed_15min_avg");
        ktsToms($wind, "speed_30min_avg");
        ktsToms($wind, "speed_maxtime");
        ktsToms($wind, "speed_day_avg");
        ktsToms($wind, "speed_alltime_max");
        ktsToms($wind, "speed_yesterday_max");
        ktsToms($wind, "speed_month_max");
        ktsToms($wind, "speed_year_max");
        ktsToms($wind, "speed_day_max");
        $wind["units"] = $windunit;
    }
    else if ($windunit == 'm/s' && $wind["units"] == 'mph')
    {
        mphToms($wind, "speed_avg");
        mphToms($wind, "speed");
        mphToms($wind, "gust_10m_max");
        mphToms($wind, "speed_10m_avg");
        mphToms($wind, "gust");
        mphToms($wind, "gust_60min");
        mphToms($wind, "speed_max");
        mphToms($wind, "gust_max");
        mphToms($wind, "gust_yesterday_max");        
        mphToms($wind, "gust_month_max");
        mphToms($wind, "gust_year_max");
        mphToms($wind, "gust_alltime_max");
        mphToms($wind, "speed_15min_avg");
        mphToms($wind, "speed_30min_avg");
        mphToms($wind, "speed_maxtime");
        mphToms($wind, "speed_day_avg");
        mphToms($wind, "speed_alltime_max");
        mphToms($wind, "speed_yesterday_max");
        mphToms($wind, "speed_month_max");
        mphToms($wind, "speed_year_max");
        mphToms($wind, "speed_day_max");
        $wind["units"] = $windunit;
    }
    else if ($windunit == 'm/s' && $wind["units"] == 'km/h')
    {
        kmhToms($wind, "speed_avg");
        kmhToms($wind, "speed");
        kmhToms($wind, "gust_10m_max");
        kmhToms($wind, "speed_10m_avg");
        kmhToms($wind, "gust");
        kmhToms($wind, "gust_60min");
        kmhToms($wind, "speed_max");
        kmhToms($wind, "gust_max");
        kmhToms($wind, "gust_yesterday_max");               
        kmhToms($wind, "gust_month_max");
        kmhToms($wind, "gust_year_max");
        kmhToms($wind, "gust_alltime_max");
        kmhToms($wind, "speed_15min_avg");
        kmhToms($wind, "speed_30min_avg");
        kmhToms($wind, "speed_maxtime");
        kmhToms($wind, "speed_day_avg");
        kmhToms($wind, "speed_alltime_max");
        kmhToms($wind, "speed_yesterday_max");
        kmhToms($wind, "speed_month_max");
        kmhToms($wind, "speed_year_max");
        kmhToms($wind, "speed_day_max");
        $wind["units"] = $windunit;
    }
    else if ($windunit == 'kts' && $wind["units"] == 'm/s')
    {
        msTokts($wind, "speed_avg");
        msTokts($wind, "speed");
        msTokts($wind, "gust_10m_max");
        msTokts($wind, "speed_10m_avg");
        msTokts($wind, "gust");
        msTokts($wind, "gust_60min");
        msTokts($wind, "speed_max");
        msTokts($wind, "gust_max");
        msTokts($wind, "gust_yesterday_max");        
        msTokts($wind, "gust_month_max");
        msTokts($wind, "gust_year_max");
        msTokts($wind, "gust_alltime_max");
        msTokts($wind, "speed_15min_avg");
        msTokts($wind, "speed_30min_avg");
        msTokts($wind, "speed_maxtime");
        msTokts($wind, "speed_day_avg");
        msTokts($wind, "speed_alltime_max");
        msTokts($wind, "speed_yesterday_max");
        msTokts($wind, "speed_month_max");
        msTokts($wind, "speed_year_max");
        msTokts($wind, "speed_day_max");
        msTokts($lightning, "last_distance");
        $wind["units"] = $windunit;
    }
    else if ($windunit == 'kts' && $wind["units"] == 'mph')
    {
        mphTokts($wind, "speed_avg");
        mphTokts($wind, "speed");
        mphTokts($wind, "gust_10m_max");
        mphTokts($wind, "speed_10m_avg");
        mphTokts($wind, "gust");
        mphTokts($wind, "gust_60min");
        mphTokts($wind, "speed_max");
        mphTokts($wind, "gust_max");
        mphTokts($wind, "gust_yesterday_max");        
        mphTokts($wind, "gust_month_max");
        mphTokts($wind, "gust_year_max");
        mphTokts($wind, "gust_alltime_max");
        mphTokts($wind, "speed_15min_avg");
        mphTokts($wind, "speed_30min_avg");
        mphTokts($wind, "speed_maxtime");
        mphTokts($wind, "speed_day_avg");
        mphTokts($wind, "speed_alltime_max");
        mphTokts($wind, "speed_yesterday_max");
        mphTokts($wind, "speed_month_max");
        mphTokts($wind, "speed_year_max");
        mphTokts($wind, "speed_day_max");
        $wind["units"] = $windunit;
    }
    else if ($windunit == 'kts' && $wind["units"] == 'km/h')
    {
        kmhTokts($wind, "speed_avg");
        kmhTokts($wind, "speed");
        kmhTokts($wind, "gust_10m_max");
        kmhTokts($wind, "speed_10m_avg");
        kmhTokts($wind, "gust");
        kmhTokts($wind, "gust_60min");
        kmhTokts($wind, "speed_max");
        kmhTokts($wind, "gust_max");
        kmhTokts($wind, "gust_yesterday_max");       
        kmhTokts($wind, "gust_month_max");
        kmhTokts($wind, "gust_year_max");
        kmhTokts($wind, "gust_alltime_max");
        kmhTokts($wind, "speed_15min_avg");
        kmhTokts($wind, "speed_30min_avg");
        kmhTokts($wind, "speed_maxtime");
        kmhTokts($wind, "speed_day_avg");
        kmhTokts($wind, "speed_alltime_max");
        kmhTokts($wind, "speed_yesterday_max");
        kmhTokts($wind, "speed_month_max");
        kmhTokts($wind, "speed_year_max");
        kmhTokts($wind, "speed_day_max");
        $wind["units"] = $windunit;
    }
}
// Keep track of the conversion factor for windspeed to knots because it is useful in multiple places
if ($wind["units"] == 'mph')
{
    $toKnots = 0.868976;
}
else if ($wind["units"] == 'km/h')
{
    $toKnots = 0.5399568;
}
else if ($wind["units"] == 'm/s')
{
    $toKnots = 1.943844;
}
else
{
    $toKnots = 1;
}
?>