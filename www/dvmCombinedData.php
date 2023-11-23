<?php
#####################################################################################################################                                                                                                        #
#                                                                                                                   #
# weewx-divumwx Skin Template maintained by The DivumWX Team                                                        #
#                                                                                                                   #
# Copyright (C) 2023 Ian Millard, Steven Sheeley, Sean Balfour. All rights reserved                                 #
#                                                                                                                   #
# Distributed under terms of the GPLv3. See the file LICENSE.txt for your rights.                                   #
#                                                                                                                   #
# Issues for weewx-divumwx skin template should be addressed to https://github.com/Millardiang/weewx-divumwx/issues # 
#                                                                                                                   #
#####################################################################################################################
# WeeWX Runtime Live data array description
#    $weewxrt[0] = date
#    $weewxrt[1] = time
#    $weewxrt[2] = outTemp
#    $weewxrt[3] = outHumidity
#    $weewxrt[4] = dewpoint
#    $weewxrt[5] = windSpeed_avg
#    $weewxrt[6] = windSpeed
#    $weewxrt[7] = windDir
#    $weewxrt[8] = rainRate
#    $weewxrt[9] = dayRain
#    $weewxrt[10] = barometer
#    $weewxrt[11] = windDir_compass
#    $weewxrt[12] = windDir_beaufort
#    $weewxrt[13] = units_wind
#    $weewxrt[14] = units_temperature
#    $weewxrt[15] = units_pressure
#    $weewxrt[16] = units_rain
#    $weewxrt[17] = windrun
#    $weewxrt[18] = pressure_trend
#    $weewxrt[19] = rain_month
#    $weewxrt[20] = rain_year
#    $weewxrt[21] = rain_yesterday
#    $weewxrt[22] = inTemp
#    $weewxrt[23] = inHumidity
#    $weewxrt[24] = windchill
#    $weewxrt[25] = temperature_trend
#    $weewxrt[26] = outTemp_max
#    $weewxrt[27] = outTemp_max_time
#    $weewxrt[28] = outTemp_min
#    $weewxrt[29] = outTemp_min_time
#    $weewxrt[30] = windSpeed_max
#    $weewxrt[31] = windSpeed_max_time
#    $weewxrt[32] = windGust_max
#    $weewxrt[33] = windGust_max_time
#    $weewxrt[34] = pressure_max
#    $weewxrt[35] = pressure_max_time
#    $weewxrt[36] = pressure_min
#    $weewxrt[37] = pressure_min_time
#    $weewxrt[38] = weewx_version
#    $weewxrt[39] = 0
#    $weewxrt[40] = 10min_high_gust
#    $weewxrt[41] = heatindex
#    $weewxrt[42] = humidex
#    $weewxrt[43] = UV
#    $weewxrt[44] = ET_today
#    $weewxrt[45] = radiation
#    $weewxrt[46] = 10min_avg_wind_bearing
#    $weewxrt[47] = rain_hour
#    $weewxrt[48] = zambretti_code
#    $weewxrt[49] = is_daylight
#    $weewxrt[50] = lost_sensor_contact
#    $weewxrt[51] = avh_wind_dir
#    $weewxrt[52] = cloudbase
#    $weewxrt[53] = units_cloudbase
#    $weewxrt[54] = appTemp
#    $weewxrt[55] = sunshine_hours
#    $weewxrt[56] = maxSolarRad
#    $weewxrt[57] = lightning_distance
#    $weewxrt[58] = lightning_energy
#    $weewxrt[59] = lightning_strike_count
#    $weewxrt[60] = lightning_noise_count
#    $weewxrt[61] = lightning_disturbance_count
#    $weewxrt[62] = 10min_avg_gust
#    $weewxrt[63] = stormRain
#.   $weewxrt[64] = windGust
################################################

include ('fixedSettings.php');
include ('dvmShared.php');
include('common.php');
//include('dvmPlanets.php');
error_reporting(0);
$os = shell_exec('lsb_release -d');
$os_version = str_replace('Description:',' ',$os);
//$jsonS = 'jsondata/dvmSensorData.json';
//$jsonS = file_get_contents($jsonS);
//$sdata = json_decode($jsonS, true);

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
    $hardware = $adata["info"]["hardware"];
    $lat = $adata["info"]["latitude"];
    $lon = $adata["info"]["longitude"];
    $absLat = abs($lat);
    $absLon = abs($lon);
    
    if ($lat >= "0"){$NS = "North";}
    else {$NS = "South";}
    if ($lon >= "0") {$EW = "East";}
    else {$EW = "West";}

    $elevation = $adata["info"]["altitude meters"];
    $url = $adata["info"]["metgramlink"];
    $divum["datetime"] = $recordDate;
    $divum["date"] = date($dateFormat, $recordDate);
    $divum["time"] = date($timeFormat, $recordDate);
    $divum["swversion"] = $adata["generation"]["generator"];
    $divum["build"] = $weewxrt[39];
    $convertuptimemb34 = $divum["uptime"];
    $uptimedays = floor($convertuptimemb34 / 86400);
    $uptimehours = floor(($convertuptimemb34 - ($uptimedays * 86400)) / 3600);
    $divum["since"] = $adata["info"]["start data"];

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

    if ($adata["almanac"]["moon rise"]["at"] == '--'){
        $alm["moonrise"] = 'In Transit';
    }
    if ($alm['luminance'] > 99.9){
        $alm['luminance'] = 100;
    }
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
    $air["pm_units"] = "μg/m<sup><b>3</b></sup>";
    $air["current.pm2_5"] = $adata["airquality"]["pm25 current"]["value"];
    $air["current.pm10_0"] = $adata["airquality"]["pm10 current"]["value"];
    $air["24h.rollingavg.pm2_5"] = $adata ["airquality"]["pm25 rolling 24hr"]["value"];
    $air["24h.rollingavg.pm10_0"] = $adata["airquality"]["pm10 rolling 24hr"]["value"];

//barometer
    $barom["units"] = $adata["barometer"]["barometer units"]["value"];
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
    $barom["max"] = $adata["barometer"]["day barometer max"]["value"];
    $barom["maxtime"] = $adata["barometer"]["day barometer maxtime"]["at"]; 
    $barom["min"] = $adata["barometer"]["day barometer min"]["value"];
    $barom["mintime"] = $adata["barometer"]["day barometer mintime"]["at"]; 
    $barom["trend_code"] = round($adata["barometer"]["trend barometer code"]["value"],0);
    if($barom["trend_code"]>3){$barom["trend_desc"]="Rising Very Rapidly";$barom["trend_code"]=4;}
    else if($barom["trend_code"]>2){$barom["trend_desc"]="Rising Quickly";$barom["trend_code"]=3;}
    else if($barom["trend_code"]>1){$barom["trend_desc"]="Rising";$barom["trend_code"]=2;}
    else if($barom["trend_code"]>0){$barom["trend_desc"]="Rising Slowly";$barom["trend_code"]=1;}
    else if($barom["trend_code"]==0){$barom["trend_desc"]="Steady";$barom["trend_code"]=0;}
    else if($barom["trend_code"]<0){$barom["trend_desc"]="Falling Slowly";$barom["trend_code"]=-1;}
    else if($barom["trend_code"]<-1){$barom["trend_desc"]="Falling";$barom["trend_code"]=-2;}
    else if($barom["trend_code"]<-2){$barom["trend_desc"]="Falling Quickly";$barom["trend_code"]=-3;}
    else if($barom["trend_code"]<-3){$barom["trend_desc"]="Falling Very Rapidly";$barom["trend_code"]=-4;}
    $barom["24h_max"] = $adata["barometer"]["yesterday barometer max"]["value"];
    $barom["24h_maxtime"] = $adata["barometer"]["yesterday barometer maxtime"]["at"];
    $barom["24h_min"] = $adata["barometer"]["yesterday barometer min"]["value"];
    $barom["24h_mintime"] = $adata["barometer"]["yesterday barometer mintime"]["at"];
    $barom["month_maxtime"] =$adata["barometer"]["month barometer maxtime"]["at"];
    $barom["month_min"] = $adata["barometer"]["month barometer min"]["value"];
    $barom["month_mintime"] = $adata["barometer"]["month barometer mintime"]["at"];
    $barom["year_max"] = $adata["barometer"]["year barometer max"]["value"];
    $barom["year_maxtime"] = $adata["barometer"]["year barometer maxtime"]["at"];
    $barom["year_min"] = $adata["barometer"]["year barometer min"]["value"];
    $barom["year_mintime"] = $adata["barometer"]["year barometer mintime"]["at"];
    $barom["alltime_max"] = $adata["barometer"]["alltime barometer max"]["value"];
    $barom["alltime_maxtime"] = $adata["barometer"]["alltime barometer maxtime"]["at"];
    $barom["alltime_min"] = $adata["barometer"]["alltime barometer min"]["value"];
    $barom["alltime_mintime"] = $adata["barometer"]["alltime barometer mintime"]["at"];

        //dewpoint
    $dew["now"] = $weewxrt[4];
    $dew["trend"] = $adata["dewpoint"]["trend dewpoint"]["value"];
    $dew["day_max"] = $adata["dewpoint"]["day dewpoint max"]["value"];
    $dew["day_maxtime"] = $adata["dewpoint"]["day dewpoint maxtime"]["value"];
    $dew["day_min"] = $adata["dewpoint"]["day dewpoint min"]["value"];
    $dew["day_mintime"] = $adata["dewpoint"]["day dewpoint mintime"]["value"];
    $dew["24h_max"] = $adata["dewpoint"]["yesterday dewpoint max"]["value"];
    $dew["24h_maxtime"] = $adata["dewpoint"]["yesterday dewpoint maxtime"]["value"];
    $dew["24h_min"] = $adata["dewpoint"]["yesterday dewpoint min"]["value"];
    $dew["24h_mintime"] = $adata["dewpoint"]["yesterday dewpoint mintime"]["value"];
    $dew["month_max"] = $adata["dewpoint"]["month dewpoint max"]["value"];
    $dew["month_maxtime"] = $adata["dewpoint"]["month dewpoint maxtime"]["value"];
    $dew["month_min"] = $adata["dewpoint"]["month dewpoint min"]["value"];
    $dew["month_mintime"] = $adata["dewpoint"]["month dewpoint mintime"]["value"];
    $dew["year_max"] = $adata["dewpoint"]["year dewpoint max"]["value"];
    $dew["year_maxtime"] = $adata["dewpoint"]["year dewpoint maxtime"]["value"];
    $dew["year_min"] = $adata["dewpoint"]["year dewpoint min"]["value"];
    $dew["year_mintime"] = $adata["dewpoint"]["year dewpoint min"]["value"];
    $dew["alltime_max"] = $adata["dewpoint"]["alltime dewpoint max"]["value"];
    $dew["alltime_maxtime"] = $adata["dewpoint"]["alltime dewpoint maxtime"]["value"];
    $dew["alltime_min"] = $adata["dewpoint"]["alltime dewpoint min"]["value"];
    $dew["alltime_mintime"] = $adata["dewpoint"]["alltime dewpoint mintime"]["value"];

    //humidity
    $humid["now"] = $weewxrt[3];
    $humid["trend"] =  $adata["humidity"]["trend outHumidity"]["value"];
    $humid["day_max"] = $adata["humidity"]["day outHumidity max"]["value"];
    $humid["day_maxtime"] = $adata["humidity"]["day outHumidity maxtime"]["value"];
    $humid["day_min"] = $adata["humidity"]["day outHumidity min"]["value"];
    $humid["day_mintime"] = $adata["humidity"]["day outHumidity mintime"]["value"];
    $humid["24h_max"] = $adata["humidity"]["day outHumidity mintime"]["value"];
    $humid["24h_maxtime"] = $adata["humidity"]["yesterday outHumidity maxtime"]["value"];
    $humid["24h_min"] = $adata["humidity"]["yesterday outHumidity min"]["value"];
    $humid["24h_mintime"] = $adata["humidity"]["yesterday outHumidity mintime"]["value"];
    $humid["month_max"] = $adata["humidity"]["month outHumidity max"]["value"];
    $humid["month_maxtime"] = $adata["humidity"]["month outHumidity maxtime"]["value"];
    $humid["month_min"] = $adata["humidity"]["month outHumidity min"]["value"];
    $humid["month_mintime"] = $adata["humidity"]["month outHumidity mintime"]["value"];
    $humid["year_max"] = $adata["humidity"]["year outHumidity max"]["value"];
    $humid["year_maxtime"] = $adata["humidity"]["year outHumidity maxtime"]["value"];
    $humid["year_min"] = $adata["humidity"]["year outHumidity min"]["value"];
    $humid["year_mintime"] = $adata["humidity"]["year outHumidity mintime"]["value"];
    $humid["alltime_max"] = $adata["humidity"]["alltime outHumidity max"]["value"];
    $humid["alltime_maxtime"] = $adata["humidity"]["alltime outHumidity maxtime"]["value"];
    $humid["alltime_min"] = $adata["humidity"]["alltime outHumidity min"]["value"];
    $humid["alltime_mintime"] = $adata["humidity"]["alltime outHumidity mintime"]["value"];
    $humid["indoors_trend"] = round($adata["humidity"]["trend inHumidity"]["value"],1);
    $humid["indoors_now"] = $weewxrt[23];
    $humid["indoors_day_max"] = $adata["humidity"]["day inHumidity max"]["value"];
    $humid["indoors_day_maxtime"] = $adata["humidity"]["day inHumidity maxtime"]["value"];
    $humid["indoors_day_min"] = $adata["humidity"]["day inHumidity min"]["value"];
    $humid["indoors_day_mintime"] = $adata["humidity"]["day inHumidity mintime"]["value"];
    $humid["indoors_24h_max"] = $adata["humidity"]["yesterday inHumidity max"]["value"];
    $humid["indoors_24h_maxtime"] = $adata["humidity"]["yesterday inHumidity maxtime"]["value"];
    $humid["indoors_24h_min"] = $adata["humidity"]["yesterday inHumidity min"]["value"];
    $humid["indoors_24h_mintime"] = $adata["humidity"]["yesterday inHumidity mintime"]["value"];
    $humid["indoors_month_max"] = $adata["humidity"]["month inHumidity max"]["value"];
    $humid["indoors_month_maxtime"] = $adata["humidity"]["month inHumidity maxtime"]["value"];
    $humid["indoors_month_min"] = $adata["humidity"]["month inHumidity min"]["value"];
    $humid["indoors_month_mintime"] = $adata["humidity"]["month inHumidity mintime"]["value"];
    $humid["indoors_year_max"] = $adata["humidity"]["year inHumidity max"]["value"];
    $humid["indoors_year_maxtime"] = $adata["humidity"]["year inHumidity maxtime"]["value"];
    $humid["indoors_year_min"] = $adata["humidity"]["year inHumidity min"]["value"];
    $humid["indoors_year_mintime"] = $adata["humidity"]["year inHumidity mintime"]["value"];
    $humid["indoors_year_mintime"] = $adata["humidity"]["alltime inHumidity mintime"]["value"];
    $humid["indoors_alltime_max"] = $adata["humidity"]["alltime inHumidity max"]["value"];
    $humid["indoors_alltime_maxtime"] = $adata["humidity"]["alltime inHumidity maxtime"]["value"];
    $humid["indoors_alltime_min"] = $adata["humidity"]["alltime inHumidity min"]["value"];
    $humid["indoors_alltime_mintime"] = $adata["humidity"]["alltime inHumidity mintime"]["value"];




    //lightning
    $lightning["last_strike_time"] = $adata["lightning"]["lightning strike last time"]["value"];
    $lightning["current_strike_count"] = $adata["lightning"]["lightning current"]["value"]; 
    $lightning["hour_strike_count"] = $adata["lightning"]["lightning last hour"]["value"]; 
    //$lightning["24h_strike_count"] = $sdata["24h.lightning_strike_count.sum"]; 
    $lightning["day_strike_count"] = $adata["lightning"]["lightning day"]["value"];
    $lightning["month_strike_count"] = $adata["lightning"]["lightning month"]["value"];
    $lightning["year_strike_count"] = $adata["lightning"]["lightning year"]["value"];
    $lightning["last_time"] = $adata["lightning"]["lightning last time"]["at"];
    $lightning["alltime_strike_count"] = $adata["lightning"]["lightning alltime"]["value"];
    $lightning["last_distance"] = $adata["lightning"]["lightning last distance"]["value"];
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
    $rain["units"] = $weewxrt[16];
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
    $rain["last_hour"] = $adata["rain"]["hour rain"]["value"];
    $rain["last_10min"] = $adata["rain"]["10min rain sum"]["value"];
    $rain["last_24hour"] = $adata["rain"]["24hr rain"]["value"];
    $rain["day"] = $adata["rain"]["day rain"]["value"];
    $rain["24h_rate_max"] = $adata["rain"]["24hr rainRate Max"]["value"];
    $rain["24h_rate_maxtime"] = $adata["rain"]["24hr rainRate Max Time"]["value"];
    $rain["24h_total"] = $adata["rain"]["24hr rain"]["value"];
    $rain["day_rate_max"] = $adata["rain"]["day rainRate Max"]["value"];
    $rain["day_rate_maxtime"] = $adata["rain"]["day rainRate Max Time"]["value"];
    $rain["day_total"] = $adata["rain"]["day rain"]["value"];
    $rain["month_rate_max"] = $adata["rain"]["month rainRate Max"]["value"];
    $rain["month_rate_maxtime"] = $adata["rain"]["month rainRate Max Time"]["value"];
    $rain["month_total"] = $adata["rain"]["month rain"]["value"];
    $rain["year_rate_max"] = $adata["rain"]["year rainRate Max"]["value"];
    $rain["year_rate_maxtime"] = $adata["rain"]["year rainRate Max Time"]["value"];
    $rain["year_total"] = $adata["rain"]["year rain"]["value"];
    $rain["alltime_rate_max"] = $adata["rain"]["alltime rainRate Max"]["value"];
    $rain["alltime_rate_maxtime"] = $adata["rain"]["alltime rainRate Max Time"]["value"];
    $rain["alltime_total"] = round($adata["rain"]["alltime rain"]["value"],1);
    $rain["storm_rain"] = $adata["rain"]["storm rain"]["value"];

    //sky
    $sky["lux"] = round($weewxrt[45] / 0.00809399477, 0 ,PHP_ROUND_HALF_UP);
    $sky["day_lux_max"] = round($adata["solar"]["max solar day"]["value"]/ 0.00809399477, 0 ,PHP_ROUND_HALF_UP);
    $sky["cloud_base"] = $weewxrt[52];
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
    $temp["units"] = $adata["temp"]["temp units"]["value"];
    if($temp["units"] == "°C"){
        $temp["units"] = "C";
    }
    else if($temp["units"] == "°F")
    {
        $temp["units"] = "F";
    }
    $temp["indoor_now"] = round($weewxrt[22],1);
    $temp["indoor_trend"] = $adata["temp"]["trend inTemp"]["value"];
    //$temp["indoor_day_max"] = $sdata["day.inTemp.max.formatted"];
    //$temp["indoor_day_min"] = $sdata["day.inTemp.min.formatted"];
    $temp["outside_now"] = round($weewxrt[2],1);
    $temp["outside_trend"] = round($adata["temp"]["trend outTemp"]["value"],1);
    $temp["apptemp"] = $weewxrt[54];
    $temp["heatindex"] = $weewxrt[41];
    $temp["windchill"] = round($adata["temp"]["windchill"]["value"],1);
    $temp["humidex"] = $weewxrt[42];
    $temp["outside_24h_max"] = $adata["temp"]["24hr outTemp max"]["value"];
    $temp["outside_day_avg_60mn"] = $adata["temp"]["60min outTemp avg"]["value"];
    $temp["outside_24h_maxtime"] =  $adata["temp"]["60min outTemp avg"]["value"];
    $temp["outside_24h_min"] = $adata["temp"]["24hr outTemp min"]["value"];
    $temp["outside_24h_mintime"] =  $adata["temp"]["24hr outTemp mintime"]["value"];
    $temp["outside_day_avg"] = $adata["temp"]["day outTemp avg"]["value"];
    $temp["outside_day_max"] = $adata["temp"]["day outTemp max"]["value"];
    $temp["outside_day_maxtime"] = $adata["day.outTemp.maxtime.raw"];
    $temp["outside_day_min"] = round($adata["temp"]["day outTemp min"]["value"],1);
    $temp["outside_day_mintime"] = $adata["temp"]["day outTemp mintime"]["value"];
    $temp["outside_month_max"] = $adata["month.outTemp.max.formatted"];
    $temp["outside_month_maxtime"] = $adata["temp"]["month outTemp max"]["value"];
    $temp["outside_month_min"] = $adata["temp"]["day outTemp min"]["value"];
    $temp["outside_month_mintime"] = $adata["temp"]["month outTemp mintime"]["value"];
    $temp["outside_year_max"] = $adata["temp"]["year outTemp max"]["value"];
    $temp["outside_year_maxtime"] = $adata["temp"]["year outTemp maxtime"]["value"];
    $temp["outside_year_maxtime2"] = $adata["year.outTemp.maxtime.raw"];
    $temp["outside_year_min"] = $adata["temp"]["year outTemp min"]["value"];
    $temp["outside_year_mintime"] = $adata["temp"]["year outTemp mintime"]["value"];
    $temp["outside_year_mintime2"] = $adata["temp"]["year outTemp mintime"]["value"];
    $temp["outside_alltime_max"] = $adata["temp"]["alltime outTemp max"]["value"];
    $temp["outside_alltime_maxtime"] = $adata["temp"]["alltime outTemp maxtime"]["value"];
    $temp["outside_alltime_min"] = $adata["temp"]["alltime outTemp min"]["value"];
    $temp["outside_alltime_mintime"] = $adata["temp"]["alltime outTemp mintime"]["value"];
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
    $uv["day_max"] = $adata["uv"]["day uv max"]["value"];
    $uv["day_maxtime"] = $adata["uv"]["day uv maxtime"]["value"];
    $uv["24h_max"] = $adata["uv"]["yesterday uv max"]["value"];
    $uv["24h_maxtime"] = $adata["uv"]["yesterday uv maxtime"]["value"];
    $uv["month_max"] = $adata["uv"]["month uv max"]["value"];
    $uv["month_maxtime"] = $adata["uv"]["month uv maxtime"]["value"];
    $uv["year_max"] = $adata["uv"]["year uv max"]["value"];
    $uv["year_maxtime"] = $adata["uv"]["year uv maxtime"]["value"];
    $uv["alltime_max"] = $adata["uv"]["alltime uv max"]["value"];
    $uv["alltime_maxtime"] = $adata["uv"]["alltime uv maxtime"]["value"];

    //wind
    $wind["units"] = $adata["wind"]["wind units"]["value"]; // m/s or mph or km/h or kts
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
    $wind["direction"] = $weewxrt[7];
    $wind["direction_and_symbol"] = $wind["direction"]."°"; 
    $wind["direction_10m_avg"] = $adata["wind"]["10min windDir avg"]["value"];
    $wind["cardinal"] = $weewxrt[11];
    $wind["direction_avg"] = $adata["wind"]["10min windDir avg"]["value"];    
    $wind["speed"] = $weewxrt[6];
    $wind["gust"] = $weewxrt[64];
    $wind["speed_bft"] = $weewxrt[12];
    $wind["speed_max"] =  $adata["wind"]["day windSpeed max"]["value"];
    $wind["speed_maxtime"] = $adata["wind"]["day windSpeed maxtime"]["at"];
    $wind["gust_max"] = $adata["wind"]["day windGust max"]["value"];
    $wind["gust_maxtime"] = $adata["wind"]["day windGust maxtime"]["at"];
    $wind["wind_run"] = $weewxrt[17];
    $wind["speed_10m_avg"] = $adata["wind"]["10min windSpeed avg"]["value"];
    $wind["speed_10m_max"] = $adata["wind"]["10min windSpeed max"]["value"];
    $wind["gust_10m_max"] =   $adata["wind"]["10min windGust max"]["value"];
    $wind["speed_24h_max"] = $adata["wind"]["yesterday windSpeed max"]["value"];
    $wind["speed_24h_maxtime"] = $adata["wind"]["yesterday windSpeed maxtime"]["at"];
    $wind["gust_24h_max"] = $adata["wind"]["yesterday windGust max"]["value"];
    $wind["gust_24h_maxtime"] = $adata["wind"]["yesterday windGust maxtime"]["at"];
    $wind["speed_month_max"] = $adata["wind"]["month windSpeed max"]["value"];
    $wind["speed_month_maxtime"] = $adata["wind"]["month windSpeed maxtime"]["at"];
    $wind["speed_month_maxtime2"] = $adata["wind"]["month windSpeed maxtime"]["at"];
    $wind["gust_month_max"] = $adata["wind"]["month windGust max"]["value"];
    $wind["gust_month_maxtime"] = $adata["wind"]["month windGust maxtime"]["at"];
    $wind["speed_year_max"] = $adata["wind"]["year windSpeed max"]["value"];
    $wind["speed_year_maxtime"] = $adata["wind"]["year windSpeed maxtime"]["at"];
    $wind["gust_year_max"] = $adata["wind"]["year windGust max"]["value"];
    $wind["gust_year_maxtime"] = $adata["wind"]["year windGust maxtime"]["at"];
    $wind["gust_year_maxtime2"] = $adata["year.windGust.maxtime.raw"];
    $wind["speed_alltime_max"] = $adata["wind"]["alltime windSpeed max"]["value"];
    $wind["speed_alltime_maxtime"] = $adata["wind"]["alltime windSpeed maxtime"]["at"];
    $wind["gust_alltime_max"] = $adata["wind"]["alltime windGust max"]["value"];
    $wind["gust_alltime_maxtime"] = $adata["wind"]["alltime windGust maxtime"]["at"];
    $wind["direction_trend"] = $adata["wind"]["wind direction trend"]["value"];
    $wind["direction_trend_ordinal"] = $adata["wind"]["wind direction trend ordinal"]["value"];


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
        mmToin($rain, "last_10min");
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

date_default_timezone_set($TZ);

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
//lunar and solar eclipse /meteor shpwers advisory 2018-2019-2020
$eclipse_default = " <noalert>No Current Weather <spanyellow><ored>Alerts " . $alert . "</ored></spanyellow></noalert>";
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

$firerisk = number_format((((110 - 1.373 * $divum["humidity"]) - 0.54 * (10.20 - $divum["temp"])) * (124 * pow(10, (-0.0142 * $divum["humidity"])))) / 60, 0);

//wetbulb
$Tc = ($divum['temp']);
$P = $divum['barometer'];
$RH = $divum['humidity'];
$Tdc = (($Tc - (14.55 + 0.114 * $Tc) * (1 - (0.01 * $RH)) - pow((2.5 + 0.007 * $Tc) * (1 - (0.01 * $RH)) , 3) - (15.9 + 0.117 * $Tc) * pow(1 - (0.01 * $RH) , 14)));
$E = (6.11 * pow(10, (7.5 * $Tdc / (237.7 + $Tdc))));
$wetbulbcalc = (((0.00066 * $P) * $Tc) + ((4098 * $E) / pow(($Tdc + 237.7) , 2) * $Tdc)) / ((0.00066 * $P) + (4098 * $E) / pow(($Tdc + 237.7) , 2));
$wetbulbx = number_format($wetbulbcalc, 1);
// K-INDEX & SOLAR DATA
$str = file_get_contents('jsondata/ki.txt');
$json = array_reverse(json_decode($str, false));
$kp = $json[1][1];
$file = $_SERVER["SCRIPT_NAME"];
$break = Explode('/', $file);
$moddvmfile = $break[count($break) - 1];


?>