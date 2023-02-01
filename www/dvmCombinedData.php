<?php
include ('fixedSettings.php');
include ('dvmShared.php');
include('common.php');
error_reporting(0);

$jsonS = 'jsondata/dvmSensorData.json';
$jsonS = file_get_contents($jsonS);
$sdata = json_decode($jsonS, true);

$jsonA = 'jsondata/dvmSkyData.json';
$jsonA = file_get_contents($jsonA);
$adata = json_decode($jsonA, true);


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
    $sundial_time = date("M d Y H:i:s",filemtime('serverdata/dvmRealtime.txt'));    
        
    $recordDate = mktime(substr($weewxrt[1], 0, 2) , substr($weewxrt[1], 3, 2) , substr($weewxrt[1], 6, 2) , substr($weewxrt[0], 3, 2) , substr($weewxrt[0], 0, 2) , $year);
    $stationlocation = $adata["info"]["location"];
    $lat = $adata["info"]["latitude"];
    $lon = $adata["info"]["longitude"];
    if ($lat >= "0" && $lat <= "180"){$NS = "North";}
    else {$NS = "South";}
    if ($lon >= "0" && $lat <= "180"){$EW = "East";}
    else {$EW = "West";}

    $elevation = $adata["info"]["altitude meters"];
    $meteogramURL = $adata["info"]["metgramlink"];
    $divum["datetime"] = $recordDate;
    $divum["date"] = date($dateFormat, $recordDate);
    $divum["time"] = date($timeFormat, $recordDate);
    $divum["swversion"] = $weewxrt[38];
    $divum["build"] = $weewxrt[39];
    $convertuptimemb34 = $divum["uptime"];
    $uptimedays = floor($convertuptimemb34 / 86400);
    $uptimehours = floor(($convertuptimemb34 - ($uptimedays * 86400)) / 3600);

    //almanac
    $alm["sun_altitude"] = round($adata["almanac"]["sun altitude"]["value"],2);
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
    $alm["sun_azimuth"] = $adata["almanac"]["sun azimuth"]["value"];
    $alm["moon_azimuth"] = $adata["almanac"]["moon azimuth"]["value"];
    $alm["sunrise"] = $adata["almanac"]["sun rise"]["at"];
    $alm["sunset"] = $adata["almanac"]["sun set"]["at"];
    $alm["sunrise_date"] = $adata["almanac"]["sun rise date"]["at"];
    $alm["sunset_date"] = $adata["almanac"]["sun set date"]["at"];
    $alm["sun_right_ascension"] = $adata["almanac"]["sun right ascension"]["value"];
    $alm["next_equinox"] = $adata["almanac"]["equinox"]["at"];
    $alm["next_solstice"] = $adata["almanac"]["solstice"]["at"];
    $alm["sidereal_time"] = $adata["almanac"]["sidereal time"]["at"];
    $alm["civil_twilight_begin"] = $adata["almanac"]["civil twighlight begin"]["at"];
    $alm["civil_twilight_end"] = $adata["almanac"]["civil twighlight end"]["at"];
    $alm["nautical_twilight_begin"] = $adata["almanac"]["nautical twighlight begin"]["at"];
    $alm["nautical_twilight_end"] = $adata["almanac"]["nautical twighlight end"]["at"];
    $alm["astronomical_twilight_begin"] = $adata["almanac"]["astronomical twighlight begin"]["at"];
    $alm["astronomical_twilight_end"] = $adata["almanac"]["astronomical twighlight end"]["at"];
    $alm["sun_meridian_transit"] = $adata["almanac"]["sun meridian transit"]["at"];
    $alm["moon_meridian_transit"] = $adata["almanac"]["moon meridian transit"]["at"];
    $alm["moonphase"] = $adata["almanac"]["moon phase"]["value"];
    $alm["moon_age"] = $adata["almanac"]["moon age"]["value"];
    $alm["hour_sun"] = $adata["almanac"]["hour sun"]["value"];
    $alm["hour_moon"] = $adata["almanac"]["hour moon"]["value"];
    $alm["luminance"] = round($adata["almanac"]["moon fullness"]["value"],2);
    $alm["fullmoon"] = $adata["almanac"]["full moon"]["at"];
    $alm["newmoon"] = $adata["almanac"]["new moon"]["at"];   
    $alm["daylight"] = $adata["info"]["daylight"];
    $alm["moonrise"] = $adata["almanac"]["moon rise"]["at"];
    $alm["moonset"] = $adata["almanac"]["moon set"]["at"];
    $alm["mercury_hlongitude"] = $adata["almanac"]["mercury hlongitude"]["value"];
    $alm["venus_hlongitude"] = $adata["almanac"]["venus hlongitude"]["value"];
    $alm["earth_hlongitude"] = $adata["almanac"]["earth hlongitude"]["value"];
    $alm["mars_hlongitude"] = $adata["almanac"]["mars hlongitude"]["value"];
    $alm["jupiter_hlongitude"] = $adata["almanac"]["jupiter hlongitude"]["value"];
    $alm["saturn_hlongitude"] = $adata["almanac"]["saturn hlongitude"]["value"];
    $alm["uranus_hlongitude"] = $adata["almanac"]["uranus hlongitude"]["value"];
    $alm["neptune_hlongitude"] = $adata["almanac"]["neptune hlongitude"]["value"];
    $alm["pluto_hlongitude"] = $adata["almanac"]["pluto hlongitude"]["value"];

    if ($adata["almanac"]["moon rise"]["at"] == '--')
    {
        $alm["moonrise"] = 'In Transit';
    }
    
    if ($alm['luminance'] > 99.9)
    {
        $alm['luminance'] = 100;
    }

    if (strtotime(date("G.i")) >= strtotime($alm["civil_twilight_begin"]) && strtotime(date("G.i")) < strtotime($alm["civil_twilight_end"]))
    {
        $dayPartCivil = "day";
    }
    else
    {
        $dayPartCivil = "night";
    }

    if (strtotime(date("G.i")) >= strtotime($alm["nautical_twilight_begin"]) && strtotime(date("G.i")) < strtotime($alm["nautical_twilight_end"]))
    {
        $dayPartNautical = "day";
    }
    else
    {
        $dayPartNautical = "night";
    }

    if (strtotime(date("G.i")) >= strtotime($alm["sunrise"]) && strtotime(date("G.i")) < strtotime($alm["sunset"]))
    {
        $dayPartNatural = "day";
    }
    else
    {
        $dayPartNatural = "night";
    }

    //air quality
    $air["pm_units"] = "μg/m<sup><b>3</b></sup>";
    $air["current.pm2_5"] = $adata["airquality"]["pm25 current"]["value"];
    $air["current.pm10_0"] = $adata["airquality"]["pm10 current"]["value"];
    $air["24h.rollingavg.pm2_5"] = $adata ["airquality"]["pm25 rolling 24hr"]["value"];
    $air["24h.rollingavg.pm10_0"] = $adata["airquality"]["pm10 rolling 24hr"]["value"];

    //barometer
    $barom["units"] = $sdata["unit.label.barometer"];
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
    else if ($barom["units"] == " mb")
    {
        $barom["units"] = "mb";
    }
    $barom["now"] = $sdata["current.barometer.formatted"];
    $barom["max"] = $sdata["day.barometer.max.formatted"];
    $barom["maxtime"] = date('H:i', $sdata["day.barometer.maxtime.raw"]); 
    $barom["min"] = $sdata["day.barometer.min.formatted"];
    $barom["mintime"] = date('H:i',$sdata["day.barometer.mintime.raw"]); 
    $barom["trend_code"] = $sdata["trend.barometer.code"];
    $barom["trend_desc"] = $sdata["trend.barometer.desc"];
    $barom["24h_max"] = $sdata["24h.barometer.max.formatted"];
    $barom["24h_maxtime"] = date('D j H:i:s',$sdata["24h.barometer.maxtime.raw"]);
    $barom["24h_min"] = $sdata["24h.barometer.min.formatted"];
    $barom["24h_mintime"] = date('D j H:i:s',$sdata["24h.barometer.mintime.raw"]);
    $barom["day_max"] = $sdata["day.barometer.max.formatted"];
    $barom["day_maxtime"] = date('H:i:s',$sdata["day.barometer.maxtime.raw"]);
    $barom["day_min"] = $sdata["day.barometer.min.formatted"];
    $barom["day_mintime"] = date('H:i:s',$sdata["day.barometer.mintime.raw"]);
    $barom["month_max"] = $sdata["month.barometer.max.formatted"];
    $barom["month_maxtime"] = date('D j H:i:s',$sdata["month.barometer.maxtime.raw"]);
    $barom["month_min"] = $sdata["month.barometer.min.formatted"];
    $barom["month_mintime"] = date('D j H:i:s',$sdata["month.barometer.mintime.raw"]);
    $barom["year_max"] = $sdata["year.barometer.max.formatted"];
    $barom["year_maxtime"] = date('j M H:i:s',$sdata["year.barometer.maxtime.raw"]);
    $barom["year_min"] = $sdata["year.barometer.min.formatted"];
    $barom["year_mintime"] = date('j M H:i:s',$sdata["year.barometer.mintime.raw"]);
    $barom["alltime_max"] = $sdata["alltime.barometer.max.formatted"];
    $barom["alltime_maxtime"] = date('j M Y',$sdata["alltime.barometer.maxtime.raw"]);
    $barom["alltime_min"] = $sdata["alltime.barometer.min.formatted"];
    $barom["alltime_mintime"] = date('j M Y',$sdata["alltime.barometer.mintime.raw"]);
    

    //dewpoint
    $dew["now"] = $sdata["current.dewpoint.formatted"];
    $dew["trend"] = $sdata["trend.dewpoint.formatted"];
    $dew["day_max"] = $sdata["day.dewpoint.max.formatted"];
    $dew["day_maxtime"] = date('H:i:s', $sdata["day.dewpoint.maxtime.raw"]);
    $dew["day_min"] = $sdata["day.dewpoint.min.formatted"];
    $dew["day_mintime"] = date('H:i:s', $sdata["day.dewpoint.min.formatted"]);
    $dew["24h_max"] = $sdata["24h.dewpoint.max.formatted"];
    $dew["24h_maxtime"] = date('D j H:i:s', $sdata["24h.dewpoint.maxtime.raw"]);
    $dew["24h_min"] = $sdata["24h.dewpoint.min.formatted"];
    $dew["24h_mintime"] = date('D j H:i:s', $sdata["24h.dewpoint.mintime.raw"]);
    $dew["month_max"] = $sdata["month.dewpoint.max.formatted"];
    $dew["month_maxtime"] = date('D j H:i:s', $sdata["month.dewpoint.maxtime.raw"]);
    $dew["month_min"] = $sdata["month.dewpoint.min.formatted"];
    $dew["month_mintime"] = date('D j H:i:s', $sdata["month.dewpoint.mintime.raw"]);
    $dew["year_max"] = $sdata["year.dewpoint.max.formatted"];
    $dew["year_maxtime"] = date('j M H:i:s', $sdata["year.dewpoint.maxtime.raw"]);
    $dew["year_min"] = $sdata["alltime.dewpoint.min.formatted"];
    $dew["year_mintime"] = date('j M H:i:s', $sdata["alltime.dewpoint.mintime.raw"]);
    $dew["alltime_max"] = $sdata["alltime.dewpoint.max.formatted"];
    $dew["alltime_maxtime"] = date('j M Y', $sdata["alltime.dewpoint.maxtime.raw"]);
    $dew["alltime_min"] = $sdata["alltime.dewpoint.min.formatted"];
    $dew["alltime_mintime"] = date('j M Y', $sdata["alltime.dewpoint.mintime.raw"]);

    //humidity
    $humid["now"] = $sdata ["current.outHumidity.formatted"];
    $humid["trend"] =  $sdata ["trend.outHumidity.formatted"];
    $humid["day_max"] = $sdata["day.outHumidity.max.formatted"];
    $humid["day_maxtime"] = date('H:i:s', $sdata["day.outHumidity.maxtime.raw"]);
    $humid["day_min"] = $sdata["day.outHumidity.min.formatted"];
    $humid["day_mintime"] = date('H:i:s', $sdata["day.outHumidity.mintime.raw"]);
    $humid["24h_max"] = $sdata["24h.outHumidity.max.formatted"];
    $humid["24h_maxtime"] = date('D j H:i:s', $sdata["24h.outHumidity.maxtime.raw"]);
    $humid["24h_min"] = $sdata["24h.outHumidity.min.formatted"];
    $humid["24h_mintime"] = date('D j H:i:s', $sdata["24h.outHumidity.mintime.raw"]);
    $humid["month_max"] = $sdata["month.outHumidity.max.formatted"];
    $humid["month_maxtime"] = date('D j H:i:s', $sdata["month.outHumidity.maxtime.raw"]);
    $humid["month_min"] = $sdata["month.outHumidity.min.formatted"];
    $humid["month_mintime"] = date('D j H:i:s', $sdata["month.outHumidity.mintime.raw"]);
    $humid["year_max"] = $sdata['year.outHumidity.max.formatted'];
    $humid["year_maxtime"] = date('j M H:i:s',$sdata['year.outHumidity.maxtime.raw']);
    $humid["year_min"] = $sdata['year.outHumidity.min.formatted'];
    $humid["year_mintime"] = date('j M H:i:s',$sdata['year.outHumidity.mintime.raw']);
    $humid["alltime_max"] = $sdata['alltime.outHumidity.max.formatted'];
    $humid["alltime_maxtime"] = date('j M Y' ,$sdata['alltime.outHumidity.maxtime.raw']);
    $humid["alltime_min"] = $sdata['alltime.outHumidity.min.formatted'];
    $humid["alltime_mintime"] = date('j M Y', $sdata['alltime.outHumidity.mintime.raw']);
    $humid["indoors_trend"] = $sdata ["trend.inHumidity.formatted"];
    $humid["indoors_now"] = $sdata ["current.inHumidity.formatted"];
    $humid["indoors_day_max"] = $sdata["day.inHumidity.max.formatted"];
    $humid["indoors_day_maxtime"] = date('H:i:s', $sdata["day.inHumidity.maxtime.raw"]);
    $humid["indoors_day_min"] = $sdata["day.inHumidity.min.formatted"];
    $humid["indoors_day_mintime"] = date('H:i:s', $sdata["day.inHumidity.mintime.raw"]);
    $humid["indoors_24h_max"] = $sdata["24h.inHumidity.max.formatted"];
    $humid["indoors_24h_maxtime"] = date('D j H:i:s', $sdata["24h.inHumidity.maxtime.raw"]);
    $humid["indoors_24h_min"] = $sdata["24h.inHumidity.min.formatted"];
    $humid["indoors_24h_mintime"] = date('D j H:i:s', $sdata["24h.inHumidity.mintime.raw"]);
    $humid["indoors_month_max"] = $sdata["month.inHumidity.max.formatted"];
    $humid["indoors_month_maxtime"] = date('D j H:i:s', $sdata["month.inHumidity.maxtime.raw"]);
    $humid["indoors_month_min"] = $sdata["month.inHumidity.min.formatted"];
    $humid["indoors_month_mintime"] = date('D j H:i:s', $sdata["month.inHumidity.mintime.raw"]);
    $humid["indoors_year_max"] = $sdata["year.inHumidity.max.formatted"];
    $humid["indoors_year_maxtime"] = date('j M H:i:s',$sdata['year.inHumidity.maxtime.raw']);
    $humid["indoors_year_min"] = $sdata["year.inHumidity.min.formatted"];
    $humid["indoors_year_mintime"] = date('j M H:i:s', $sdata['year.inHumidity.mintime.raw']);
    $humid["indoors_alltime_max"] = $sdata["alltime.inHumidity.max.formatted"];
    $humid["indoors_alltime_maxtime"] = date('j M Y', $sdata["alltime.inHumidity.maxtime.raw"]);
    $humid["indoors_alltime_min"] = $sdata["alltime.inHumidity.min.formatted"];
    $humid["indoors_alltime_mintime"] = date('j M Y', $sdata ["alltime.outHumidity.mintime.raw"]);

    //lightning
    $lightning["last_strike_time"] = $adata["lightning"]["lightning strike last time"]["value"];
    $lightning["current_strike_count"] = $sdata["current.lightning_strike_count"]; 
    $lightning["2m_strike_count"] = $sdata["2m.lightning_strike_count.sum"]; 
    $lightning["10m_strike_count"] = $sdata["10m.lightning_strike_count.sum"]; 
    $lightning["hour_strike_count"] = $sdata["hour.lightning_strike_count.sum"]; 
    $lightning["24h_strike_count"] = $sdata["24h.lightning_strike_count.sum"]; 
    $lightning["day_strike_count"] = $sdata["day.lightning_strike_count.sum"];
    $lightning["month_strike_count"] = $sdata["month.lightning_strike_count.sum"];
    $lightning["year_strike_count"] = $sdata["year.lightning_strike_count.sum"];
    $lightning["last_time"] = $adata["lightning"]["lightning last time"]["at"];
    $lightning["alltime_strike_count"] = $sdata["alltime.lightning_strike_count.sum"];
    $lightning["last_distance"] = $sdata["current.lightning_distance.formatted"];
    $lightning["now_energy"] = $weewxrt[59];
    $lightning["now_strike_count"] = $weewxrt[60];
    $lightning["now_noise_count"] = $weewxrt[61];
    $lightning["now_disturber_count"] = $weewxrt[62];
    if (trim($lightning["last_time"]) == 'N/A' || $lightning["last_time"] == '0')
    {
        $lightning["time_ago"] = 0;
    }
    else
    {
        $parts = explode(" ", $lightning["last_time"]);
        $parts1 = explode("/", $parts[0]);
        $lightning["time_ago"] = time() - strtotime("20" . $parts1[2] . $parts1[1] . $parts1[0] . " " . $parts[1]);
    }

    //rainfall
    $rain["units"] = $sdata["unit.label.rain"];
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
    $rain["rate"] = $sdata["current.rainRate.formatted"];
    $rain["total"] = $sdata["day.rain.sum.formatted"];
    $rain["last_hour"] = $sdata["hour.rain.sum.formatted"];
    $rain["last_10min"] = $sdata["10m.rain.sum.formatted"];
    $rain["last_24hour"] = $sdata["24h.rain.sum.formatted"];
    $rain["day"] = $sdata["day.rain.sum.formatted"];
    $rain["24h_rate_max"] = $sdata["24h.rainRate.max.formatted"];
    $rain["24h_rate_maxtime"] = date('D j H:i:s', $sdata["24h.rainRate.maxtime.raw"]);
    $rain["24h_total"] = $sdata["24h.rain.sum.formatted"];
    $rain["day_rate_max"] = $sdata["day.rainRate.max.formatted"];
    $rain["day_rate_maxtime"] = date('H:i:s', $sdata["day.rainRate.maxtime.raw"]);
    $rain["day_total"] = $sdata["day.rain.sum.formatted"];
    $rain["month_rate_max"] = $sdata["month.rainRate.max.formatted"];
    $rain["month_rate_maxtime"] = date('D j H:i:s', $sdata["month.rainRate.maxtime.raw"]);
    $rain["month_total"] = $sdata["month.rain.sum.formatted"];
    $rain["year_rate_max"] = $sdata["year.rainRate.max.formatted"];
    $rain["year_rate_maxtime"] = date('j M H:i:s', $sdata["year.rainRate.maxtime.raw"]);
    $rain["year_total"] = $sdata["year.rain.sum.formatted"];
    $rain["alltime_rate_max"] = $sdata["alltime.rainRate.max.formatted"];
    $rain["alltime_rate_maxtime"] = date('j M Y', $sdata["alltime.rainRate.maxtime.raw"]);
    $rain["alltime_total"] = $sdata["alltime.rain.sum.formatted"];

    //sky
    $sky["lux"] = round($sdata["current.maxSolarRad.formatted"] / 0.00809399477, 0 ,PHP_ROUND_HALF_UP);
    $sky["day_lux_max"] = round($sdata["day.maxSolarRad.formatted"] / 0.00809399477, 0 ,PHP_ROUND_HALF_UP);
    $sky["cloud_base"] = $sdata["current.cloudbase.formatted"];
    $sky["cloud_cover"] = round($adata['cloudcover']['cloud cover']['value'],0);

    //solar
    $solar["now"] = $adata["solar"]["max solar current"]["value"];
    $solar["day_max"] = $adata["solar"]["max solar day"]["value"];
    $solar["day_maxtime"] = $adata["solar"]["max solar day"]["at"];
    $solar["24h_max"] = $adata["solar"]["max solar 24h"]["value"];
    $solar["24h_maxtime"] = $adata["solar"]["max solar 24h"]["at"];
    $solar["month_max"] = $adata["solar"]["max solar month"]["value"];
    $solar["month_maxtime"] = $adata["solar"]["max solar month"]["at"];
    $solar["year_max"] = $adata["solar"]["max solar year"]["value"];
    $solar["year_maxtime"] = $adata["solar"]["max solar year"]["at"];
    $solar["alltime_max"] = $adata["solar"]["max solar alltime"]["value"];
    $solar["alltime_maxtime"] = $adata["solar"]["max solar alltime"]["at"];

    //temperature
    $temp["units"] = $sdata["unit.label.outTemp"];
    if($temp["units"] == "°C"){
        $temp["units"] = "C";
    }
    else if($temp["units"] == "°F")
    {
        $temp["units"] = "F";
    }
    $temp["indoor_now"] = $sdata["current.inTemp.formatted"];
    $temp["indoor_trend"] = $sdata["trend.inTemp.formatted"];
    $temp["indoor_day_max"] = $sdata["day.inTemp.max.formatted"];
    $temp["indoor_day_min"] = $sdata["day.inTemp.min.formatted"];
    $temp["outside_now"] = $sdata["current.outTemp.formatted"];
    $temp["outside_trend"] = $sdata["trend.outTemp.formatted"];
    $temp["apptemp"] = $sdata["current.appTemp.formatted"];
    $temp["heatindex"] = $sdata["current.appTemp.formatted"];
    $temp["windchill"] = $sdata["current.windchill.formatted"];
    $temp["humidex"] = $sdata["current.windchill.formatted"];
    $temp["outside_24h_max"] = $sdata["24h.outTemp.max.formatted"];
    $temp["outside_24h_maxtime"] = date('D j H:i:s',  $sdata["24h.outTemp.maxtime.raw"]);
    $temp["outside_24h_min"] = $sdata["24h.outTemp.min.formatted"];
    $temp["outside_24h_mintime"] = date('D j H:i:s',  $sdata["24h.outTemp.mintime.raw"]);
    $temp["outside_day_max"] = $sdata["day.outTemp.max.formatted"];
    $temp["outside_day_maxtime"] = date('H:i:s', $sdata["day.outTemp.maxtime.raw"]);
    $temp["outside_day_min"] = $sdata["day.outTemp.min.formatted"];
    $temp["outside_day_mintime"] = date('H:i:s', $sdata["month.outTemp.mintime.raw"]);
    $temp["outside_day_avg"] = $sdata["day.outTemp.avg.formatted"];
    $temp["outside_day_avg_60mn"] = $sdata["hour.outTemp.avg.formatted"];
    $temp["outside_month_max"] = $sdata["month.outTemp.max.formatted"];
    $temp["outside_month_maxtime"] = date('D j H:i:s', $sdata["month.outTemp.maxtime.raw"]);
    $temp["outside_month_min"] = $sdata["month.outTemp.min.formatted"];
    $temp["outside_month_mintime"] = date('D j H:i:s', $sdata["month.outTemp.mintime.raw"]);
    $temp["outside_year_max"] = $sdata["year.outTemp.max.formatted"];
    $temp["outside_year_maxtime"] = date('j M H:i:s', $sdata["year.outTemp.maxtime.raw"]);
    $temp["outside_year_maxtime2"] = date('j M', $sdata["year.outTemp.maxtime.raw"]);
    $temp["outside_year_min"] = $sdata["year.outTemp.min.formatted"];
    $temp["outside_year_mintime"] = date('j M H:i:s', $sdata["year.outTemp.mintime.raw"]);
    $temp["outside_year_mintime2"] = date('j M', $sdata["year.outTemp.mintime.raw"]);
    $temp["outside_alltime_max"] = $sdata["alltime.outTemp.max.formatted"];
    $temp["outside_alltime_maxtime"] = date('j M Y',  $sdata["alltime.outTemp.maxtime.raw"]);
    $temp["outside_alltime_min"] = $sdata["alltime.outTemp.min.formatted"];
    $temp["outside_alltime_mintime"] = date('j M Y',  $sdata["alltime.outTemp.mintime.raw"]);
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
    $uv["now"] = $sdata["current.UV.formatted"];
    $uv["day_max"] = $sdata["day.UV.max.formatted"];
    $uv["day_maxtime"] = date('H:i:s', $sdata["day.UV.maxtime.raw"]);
    $uv["24h_max"] = $sdata["24h.UV.max.formatted"];
    $uv["24h_maxtime"] = date('D j H:i:s',  $sdata["24h.UV.maxtime.raw"]);
    $uv["month_max"] = $sdata["month.UV.max.formatted"];
    $uv["month_maxtime"] = date('D j H:i:s', $sdata["month.UV.maxtime.raw"]);
    $uv["year_max"] = $sdata["year.UV.max.formatted"];
    $uv["year_maxtime"] = date('j M H:i:s', $sdata["year.UV.maxtime.raw"]);
    $uv["alltime_max"] = $sdata["alltime.UV.max.formatted"];
    $uv["alltime_maxtime"] = date('j M Y',  $sdata["alltime.UV.maxtime.raw"]);

    //wind
    $wind["units"] = $sdata["unit.label.windSpeed"]; // m/s or mph or km/h or kts
    if ($wind["units"] == " m/s"){
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
    $wind["speed_avg"] = $sdata["day.windSpeed.avg.formatted"];
    $wind["direction"] = $sdata["current.windDir.formatted"];
    $wind["direction_10m_avg"] = $sdata["10m.windDir.avg.formatted"];
    $wind["cardinal"] = $sdata["current.windDir.ordinal_compass"];
    $wind["direction_avg"] = $sdata["day.windDir.avg.formatted"];    
    $wind["speed"] = $sdata["current.windSpeed.formatted"];
    $wind["gust"] = $sdata["current.windGust.formatted"];
    $wind["speed_bft"] = $sdata["current.beaufort.formatted"];
    $wind["speed_max"] = $sdata["day.windSpeed.max.formatted"];
    $wind["speed_maxtime"] = date('H:i:s', $sdata["day.windSpeed.maxtime.raw"]);
    $wind["gust_max"] = $sdata["day.windGust.max.formatted"];
    $wind["gust_maxtime"] = date('H:i:s', $sdata["day.windGust.maxtime.raw"]);
    $wind["wind_run"] = $sdata["day.windrun.sum.formatted"];
    $wind["speed_10m_avg"] = $sdata["10m.windSpeed.avg.formatted"];
    $wind["speed_10m_max"] = $sdata["10m.windSpeed.max.formatted"];
    $wind["speed_10m_max"] =   $sdata["24h.windSpeed.max.formatted"];
    $wind["gust_10m_max"] =   $sdata["24h.windGust.max.formatted"];
    $wind["speed_24h_maxtime"] = date('D j H:i:s',  $sdata["24h.windSpeed.maxtime.raw"]);
    $wind["gust_24h_max"] = $sdata["24h.windGust.max.formatted"];
    $wind["gust_24h_maxtime"] = date('D j H:i:s',  $sdata["24h.windGust.maxtime.raw"]);
    $wind["speed_month_max"] = $sdata["month.windSpeed.max.formatted"];
    $wind["speed_month_maxtime"] = date('D j H:i:s', $sdata["month.windSpeed.maxtime.raw"]);
    $wind["gust_month_maxtime2"] = date('D j', $sdata["month.windGust.maxtime.raw"]);
    $wind["gust_month_max"] = $sdata["month.windGust.max.formatted"];
    $wind["gust_month_maxtime"] = date('D j H:i:s', $sdata["month.windGust.maxtime.raw"]);
    $wind["speed_year_max"] = $sdata["year.windSpeed.max.formatted"];
    $wind["speed_year_maxtime"] = date('j M H:i:s', $sdata["year.windSpeed.maxtime.raw"]);
    $wind["gust_year_max"] = $sdata["year.windGust.max.formatted"];
    $wind["gust_year_maxtime"] = date('j M H:i:s',  $sdata["year.windGust.maxtime.raw"]);
    $wind["gust_year_maxtime2"] = date('j M', $sdata["year.windGust.maxtime.raw"]);
    $wind["speed_alltime_max"] = $sdata["alltime.windSpeed.max.formatted"];
    $wind["speed_alltime_maxtime"] = date('j M Y',  $sdata["alltime.windSpeed.maxtime.raw"]);
    $wind["gust_alltime_max"] = $sdata["alltime.windGust.max.formatted"];
    $wind["gust_alltime_maxtime"] = date('j M Y',  $sdata["alltime.windGust.maxtime.raw"]);
    $wind["direction_trend"] = $sdata["trend.windDir.formatted"];


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
        fToC($temp, "outside_day_max");
        fToC($temp, "outside_day_min");
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
        cToF($dew, "day_max");
        cToF($dew, "day_min");
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
        inTomm($rain, "rate");
        inTomm($rain, "total");
        inTomm($rain, "last_hour");
        inTomm($rain, "last_3hour");
        inTomm($rain, "last_24hour");
        inTomm($rain, "day");
        inTomm($rain, "yesterday_rate_max");
        inTomm($rain, "yesterday_total");
        inTomm($rain, "month_rate_max");
        inTomm($rain, "month_total");
        inTomm($rain, "year_rate_max");
        inTomm($rain, "year_total");
        inTomm($rain, "alltime_rate_max");
        inTomm($rain, "alltime_total");
        $rain["units"] = $rainunit;
    }
    else if ($rainunit == "in")
    {
        mmToin($rain, "rate");
        mmToin($rain, "total");
        mmToin($rain, "last_hour");
        mmToin($rain, "last_3hour");
        mmToin($rain, "last_24hour");
        mmToin($rain, "day");
        mmToin($rain, "yesterday_rate_max");
        mmToin($rain, "yesterday_total");
        mmToin($rain, "month_rate_max");
        mmToin($rain, "month_total");
        mmToin($rain, "year_rate_max");
        mmToin($rain, "year_total");
        mmToin($rain, "alltime_rate_max");
        mmToin($rain, "alltime_total");
        $rain["units"] = $rainunit;
    }
}
// Convert pressure units if necessary
if ($pressureunit != $barom["units"])
{
    if (($pressureunit == 'hPa' && $barom["units"] == 'mb') || ($pressureunit == 'mb' && $barom["units"] == 'hPa') || ($pressureunit == 'kPa' && $barom["units"] == 'mb') || ($pressureunit == 'mb' && $barom["units"] == 'kPa') || ($pressureunit == 'kPa' && $barom["units"] == 'hPa') || ($pressureunit == 'hPa' && $barom["units"] == 'kPa'))
    {
        // 1 mb = 1 hPa so just change the unit being displayed
        $barom["units"] = $pressureunit;
    }
    else if ($pressureunit == "inHg" && ($barom["units"] == 'mb' || $barom["units"] == 'hPa' || $barom["units"] == 'kPa'))
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
    else if (($pressureunit == "mb" || $pressureunit == 'hPa' || $pressureunit == 'kPa') && $barom["units"] == 'inHg')
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
        ktsTomph($wind, "gust_10min");     
        ktsTomph($wind, "gust");
        ktsTomph($wind, "gust_60min");
        ktsTomph($wind, "speed_max");
        ktsTomph($wind, "gust_max");
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
        kmhTomph($wind, "gust_10min");     
        kmhTomph($wind, "gust");
        kmhTomph($wind, "gust_60min");
        kmhTomph($wind, "speed_max");
        kmhTomph($wind, "gust_max");
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
        msTomph($wind, "gust_10min");     
        msTomph($wind, "gust");
        msTomph($wind, "gust_60min");
        msTomph($wind, "speed_max");
        msTomph($wind, "gust_max");
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
        ktsTokmh($wind, "gust_10min");     
        ktsTokmh($wind, "gust");
        ktsTokmh($wind, "gust_60min");
        ktsTokmh($wind, "speed_max");
        ktsTokmh($wind, "gust_max");
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
        mphTokmh($wind, "gust_10min");     
        mphTokmh($wind, "gust");
        mphTokmh($wind, "gust_60min");
        mphTokmh($wind, "speed_max");
        mphTokmh($wind, "gust_max");
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
        msTokmh($wind, "gust_10min");     
        msTokmh($wind, "gust");
        msTokmh($wind, "gust_60min");
        msTokmh($wind, "speed_max");
        msTokmh($wind, "gust_max");
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
        ktsToms($wind, "gust_10min");     
        ktsToms($wind, "gust");
        ktsToms($wind, "gust_60min");
        ktsToms($wind, "speed_max");
        ktsToms($wind, "gust_max");
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
        mphToms($wind, "gust_10min");     
        mphToms($wind, "gust");
        mphToms($wind, "gust_60min");
        mphToms($wind, "speed_max");
        mphToms($wind, "gust_max");
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
        kmhToms($wind, "gust_10min");     
        kmhToms($wind, "gust");
        kmhToms($wind, "gust_60min");
        kmhToms($wind, "speed_max");
        kmhToms($wind, "gust_max");
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
        msTokts($wind, "gust_10min");     
        msTokts($wind, "gust");
        msTokts($wind, "gust_60min");
        msTokts($wind, "speed_max");
        msTokts($wind, "gust_max");
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
        mphTokts($wind, "gust_10min");     
        mphTokts($wind, "gust");
        mphTokts($wind, "gust_60min");
        mphTokts($wind, "speed_max");
        mphTokts($wind, "gust_max");
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
        kmhTokts($wind, "gust_10min");     
        kmhTokts($wind, "gust");
        kmhTokts($wind, "gust_60min");
        kmhTokts($wind, "speed_max");
        kmhTokts($wind, "gust_max");
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

$o = "Designed by divumwx.com";
date_default_timezone_set($TZ);
// meteor shower alternative by betejuice cumulus forum
$meteor_default = "No Meteor";
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 1, 3) ,
    "event_title" => "Quadrantids Meteor",
    "event_end" => mktime(23, 59, 59, 1, 4) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 1, 5) ,
    "event_title" => "Quadrantids Meteor",
    "event_end" => mktime(23, 59, 59, 1, 12) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 12, 28, 2019) ,
    "event_title" => "Quadrantids Meteor",
    "event_end" => mktime(23, 59, 59, 1, 2, 2020) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 12, 28, 2020) ,
    "event_title" => "Quadrantids Meteor",
    "event_end" => mktime(23, 59, 59, 1, 2, 2021) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 12, 28, 2021) ,
    "event_title" => "Quadrantids Meteor",
    "event_end" => mktime(23, 59, 59, 1, 2, 2022) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 12, 28, 2022) ,
    "event_title" => "Quadrantids Meteor",
    "event_end" => mktime(23, 59, 59, 1, 2, 2023) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 12, 28, 2023) ,
    "event_title" => "Quadrantids Meteor",
    "event_end" => mktime(23, 59, 59, 1, 2, 2024) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 12, 28, 2024) ,
    "event_title" => "Quadrantids Meteor",
    "event_end" => mktime(23, 59, 59, 1, 2, 2025) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 4, 9) ,
    "event_title" => "Lyrids Meteor",
    "event_end" => mktime(20, 59, 59, 4, 20) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 4, 21) ,
    "event_title" => "Lyrids Meteor",
    "event_end" => mktime(23, 59, 59, 4, 22) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 5, 5) ,
    "event_title" => "ETA Aquarids",
    "event_end" => mktime(23, 59, 59, 5, 6) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 7, 20) ,
    "event_title" => "Delta Aquarids",
    "event_end" => mktime(23, 59, 59, 7, 28) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 7, 29) ,
    "event_title" => "Delta Aquarids",
    "event_end" => mktime(23, 59, 59, 7, 30) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 8, 1) ,
    "event_title" => "Perseids Meteor",
    "event_end" => mktime(23, 59, 59, 8, 10) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 8, 11) ,
    "event_title" => "Perseids Meteor",
    "event_end" => mktime(23, 59, 59, 8, 13) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 8, 14) ,
    "event_title" => "Perseids Meteor",
    "event_end" => mktime(23, 59, 59, 8, 18) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 10, 6) ,
    "event_title" => "Draconids",
    "event_end" => mktime(23, 59, 59, 10, 7) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 10, 20) ,
    "event_title" => "Orionids Meteor",
    "event_end" => mktime(23, 59, 59, 10, 21) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 11, 4) ,
    "event_title" => "South Taurids",
    "event_end" => mktime(23, 59, 59, 11, 5) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 11, 11) ,
    "event_title" => "North Taurids",
    "event_end" => mktime(23, 59, 59, 11, 11) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 11, 13) ,
    "event_title" => "Leonids Meteor",
    "event_end" => mktime(23, 59, 59, 11, 16) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 11, 17) ,
    "event_title" => "Leonids Meteor",
    "event_end" => mktime(23, 59, 59, 11, 18) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 11, 19) ,
    "event_title" => "Leonids Meteor",
    "event_end" => mktime(23, 59, 59, 11, 29) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 11, 30) ,
    "event_title" => "Geminids Meteor",
    "event_end" => mktime(23, 59, 59, 12, 12) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 12, 13) ,
    "event_title" => "Geminids Meteor",
    "event_end" => mktime(23, 59, 59, 12, 14) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 12, 16) ,
    "event_title" => "Ursids Meteor",
    "event_end" => mktime(23, 59, 59, 12, 20) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 12, 21) ,
    "event_title" => "Ursids Meteor",
    "event_end" => mktime(23, 59, 59, 12, 22) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 12, 23) ,
    "event_title" => "Ursids Meteor",
    "event_end" => mktime(23, 59, 59, 12, 25) ,
);
$meteorNow = time();
$meteorOP = false;
foreach ($meteor_events as $meteor_check)
{
    if ($meteor_check["event_start"] <= $meteorNow && $meteorNow <= $meteor_check["event_end"])
    {
        $meteorOP = true;
        $meteor_default = $meteor_check["event_title"];
    }
};
//end meteor
$uptime = $divum["uptime"];
function convert_uptime($uptime)
{
    $dt1 = new DateTime("@0");
    $dt2 = new DateTime("@$uptime");
    return $dt1->diff($dt2)->format('%a day(s) %h hrs %i min');
}





//
//  Why are these in here still? Check necessity and validitity of this area
//
//lunar and solar eclipse /meteor shpwers advisory 2018-2019-2020
$eclipse_default = " <noalert>No Current divum <spanyellow><ored>Alerts " . $alert . "</ored></spanyellow></noalert>";
//2 jul solar 2019
$eclipse_events[] = array(
    "event_start" => mktime(0, 0, 0, 7, 2, 2019) ,
    "event_title" => "<div style ='margin-top:5px;'>" . $solareclipsesvg . " <alert>Total Solar <spanyellow>Eclipse</spanyellow></alert>  </div>
",
    "event_end" => mktime(23, 59, 59, 7, 2, 2019) ,
);
//16/17 jul solar 2019
$eclipse_events[] = array(
    "event_start" => mktime(0, 0, 0, 7, 16, 2019) ,
    "event_title" => "<div style ='margin-top:5px;'>" . $solareclipsesvg . "  <alert>Partial Lunar <spanyellow>Eclipse</spanyellow></alert>  </div>
",
    "event_end" => mktime(23, 59, 59, 7, 17, 2019) ,
);
//persieds 2019
$eclipse_events[] = array(
    "event_start" => mktime(0, 0, 0, 8, 12, 2019) ,
    "event_title" => "<div style ='margin-top:5px;'>" . $meteorsvg . " <alert>Perseids <spanyellow>Meteor Shower</spanyellow></alert>  </div>
",
    "event_end" => mktime(23, 59, 59, 8, 13, 2019) ,
);
//leonids 2019
$eclipse_events[] = array(
    "event_start" => mktime(0, 0, 0, 11, 17, 2019) ,
    "event_title" => "<div style ='margin-top:5px;'>" . $meteorsvg . " <alert>Leonids <spanyellow>Meteor Shower</spanyellow></alert>  </div>
",
    "event_end" => mktime(23, 59, 59, 11, 18, 2018) ,
);
//geminids 2019
$eclipse_events[] = array(
    "event_start" => mktime(0, 0, 0, 12, 13, 2019) ,
    "event_title" => "<div style ='margin-top:5px;'>" . $meteorsvg . " <alert>Geminids <spanyellow>Meteor Shower</spanyellow></alert>  </div>
",
    "event_end" => mktime(23, 59, 59, 12, 14, 2019) ,
);
//5/6 dec solar 2019
$eclipse_events[] = array(
    "event_start" => mktime(0, 0, 0, 12, 26, 2019) ,
    "event_title" => "<div style ='margin-top:5px;'>" . $solareclipsesvg . "  <alert>Annular Solar <spanyellow>Eclipse</spanyellow></alert>  </div>
",
    "event_end" => mktime(23, 59, 59, 12, 26, 2019) ,
);
//Quadrantids 2020
$eclipse_events[] = array(
    "event_start" => mktime(0, 0, 0, 1, 3, 2020) ,
    "event_title" => "<div style ='margin-top:5px;'>" . $meteorsvg . "  <alert>Quadrantids <spanyellow>Meteor Shower</spanyellow></alert>  </div>
",
    "event_end" => mktime(23, 59, 59, 1, 4, 2020) ,
);
//output eclipse events
$eclipseNow = time();
$eclipseOP = false;
foreach ($eclipse_events as $eclipse_check)
{
    if ($eclipse_check["event_start"] <= $eclipseNow && $eclipseNow <= $eclipse_check["event_end"])
    {
        $eclipseOP = true;
        $eclipse_default = $eclipse_check["event_title"];
    }
};
//end lunar and solar eclipse /meteor shpwers advisory 2018-2019-2020
// This is not used in Australia, where is it used?
// firerisk based on cumulus forum thread http://sandaysoft.com/forum/viewtopic.php?f=14&t=2789&sid=77ffab8f6f2359e09e6c58d8b13a0c3c&start=30
$firerisk = number_format((((110 - 1.373 * $divum["humidity"]) - 0.54 * (10.20 - $divum["temp"])) * (124 * pow(10, (-0.0142 * $divum["humidity"])))) / 60, 0);

//wetbulb
$Tc = ($divum['temp']);
$P = $divum['barometer'];
$RH = $divum['humidity'];
$Tdc = (($Tc - (14.55 + 0.114 * $Tc) * (1 - (0.01 * $RH)) - pow((2.5 + 0.007 * $Tc) * (1 - (0.01 * $RH)) , 3) - (15.9 + 0.117 * $Tc) * pow(1 - (0.01 * $RH) , 14)));
$E = (6.11 * pow(10, (7.5 * $Tdc / (237.7 + $Tdc))));
$wetbulbcalc = (((0.00066 * $P) * $Tc) + ((4098 * $E) / pow(($Tdc + 237.7) , 2) * $Tdc)) / ((0.00066 * $P) + (4098 * $E) / pow(($Tdc + 237.7) , 2));
$wetbulbx = number_format($wetbulbcalc, 1);
// K-INDEX & SOLAR DATA FOR divumwx HOMEWEATHERSTATION TEMPLATE RADIO HAMS REJOICE :-) //
$str = file_get_contents('jsondata/ki.txt');
$json = array_reverse(json_decode($str, false));
$kp = $json[1][1];
$file = $_SERVER["SCRIPT_NAME"];
$break = Explode('/', $file);
$mod34file = $break[count($break) - 1];

//Can this be removed, it appears to be Metobridge specific
//Convert Start times for Pro and Nano SD, Other MBs unforunately don't provide this data
if (is_numeric($weewxapi[186]) && $weewxapi[186] != '--')
{
    $divum['tempStartTime'] = date('M jS Y', strtotime($weewxapi[186]));
    $divum['windStartTime'] = date('M jS Y', strtotime($weewxapi[187]));
    $divum['pressStartTime'] = date('M jS Y', strtotime($weewxapi[188]));
    $divum['rainStartSec'] = strtotime($weewxapi[189]);
    $divum['rainStartTime'] = date('M jS Y', $divum['rainStartSec']);
}
else
{
    $divum['tempStartTime'] = 'All Time';
    $divum['windStartTime'] = 'All Time';
    $divum['pressStartTime'] = 'All Time';
    $divum['rainStartTime'] = 'All Time';
}

$divum['consoleLowBattery'] = intval($weewxapi[171]); # Console battery, 0 when battery is good, 1 when battery is low
$divum['stationLowBattery'] = intval($weewxapi[172]); # Station battery, 0 when battery is good, 1 when battery is low

?>
