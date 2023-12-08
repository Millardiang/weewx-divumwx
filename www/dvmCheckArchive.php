<?php
include ('dvmCombinedData.php');
if($humid["day_max"]<30){$t[4]="Blue";}
else if($humid["day_max"]<60){$t[4]="Green";}
else if($humid["day_max"]<100){$t[4]="Red";}
if($humid["day_min"]<30){$t[5]="Blue";}
else if($humid["day_min"]<60){$t[5]="Green";}
else if($humid["day_min"]<100){$t[5]="Red";}

if($humid["24h_max"]<30){$d[4]="Blue";}
else if($humid["24h_max"]<60){$d[4]="Green";}
else if($humid["24h_max"]<100){$d[4]="Red";}
if($humid["24h_min"]<30){$d[5]="Blue";}
else if($humid["24h_min"]<60){$d[5]="Green";}
else if($humid["24h_min"]<100){$d[5]="Red";}

if($humid["month_max"]<30){$m[4]="Blue";}
else if($humid["month_max"]<60){$m[4]="Green";}
else if($humid["month_max"]<100){$m[4]="Red";}
if($humid["month_min"]<30){$m[5]="Blue";}
else if($humid["month_min"]<60){$m[5]="Green";}
else if($humid["month_min"]<100){$m[5]="Red";}

if($humid["year_max"]<30){$y[4]="Blue";}
else if($humid["year_max"]<60){$y[4]="Green";}
else if($humid["year_max"]<100){$y[4]="Red";}
if($humid["year_min"]<30){$y[5]="Blue";}
else if($humid["year_min"]<60){$y[5]="Green";}
else if($humid["year_min"]<100){$y[5]="Red";}

if($humid["alltime_max"]<30){$a[4]="Blue";}
else if($humid["alltime_max"]<60){$a[4]="Green";}
else if($humid["alltime_max"]<100){$a[4]="Red";}
if($humid["alltime_min"]<30){$a[5]="Blue";}
else if($humid["alltime_min"]<60){$a[5]="Green";}
else if($humid["alltime_min"]<100){$a[5]="Red";}

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
  text-align: left;
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
<thead><tr><th colspan="3">Check List of dvmCombinedData.php.tmpl Generated PHP Variables</th></tr></thead>                              
<thead><tr><th>Variable Description</th><th>Variable Name</th><th>Current Value</th></tr></thead>
<thead><tr><th colspan="3">General Information Data Variables</th></tr></thead>
<tbody>
<tr><td>date/time dvmRealtime.txt file</td><td>$sundial_time</td><td><?php echo $sundial_time;?></td></tr>
<tr><td>Station Location</td><td>$stationlocation</td><td><?php echo $stationlocation;?></td></tr>
<tr><td>Weather Station Hardware</td><td>$hardware</td><td><?php echo $hardware;?></td></tr>
<tr><td>Latitude</td><td>$lat</td><td><?php echo $lat;?></td></tr>
<tr><td>Longitude</td><td>$lon</td><td><?php echo $lon;?></td></tr>
<tr><td>Absolute latitude</td><td>abs($lat)</td><td><?php echo abs($lat);?></td></tr>
<tr><td>Absolute longitude</td><td>abs($lon)</td><td><?php echo abs($lon);?></td></tr>
<tr><td>Northern or Southern Hemisphere</td><td>$NS</td><td><?php echo $NS;?></td></tr>
<tr><td>Eastern or Western Hemisphere</td><td>$EW</td><td><?php echo $EW;?></td></tr>
<tr><td>Elevation (meters)</td><td>$elevation</td><td><?php echo $elevation;?></td></tr>
<tr><td>Website URL</td><td>$url</td><td><?php echo $url;?></td></tr>
<tr><td>DivumWX Date</td><td>$divum["date"]</td><td><?php echo $divum["date"];?></td></tr>
<tr><td>DivumWX Time</td><td>$divum["time"]</td><td><?php echo $divum["time"];?></td></tr>
<tr><td>WeeWX version</td><td>$divum["swversion"]</td><td><?php echo $divum["swversion"];?></td></tr>
<tr><td>DivumWX version</td><td>$divum["build"]</td><td><?php echo $divum["build"];?></td></tr>
<tr><td>Operational since</td><td>$divum["since"]</td><td><?php echo $divum["since"];?></td></tr>
<tr><td>Station uptime</td><td>$stationUptime</td><td><?php echo $stationUptime;?></td></tr>
                              
<thead><tr><th colspan="3">Air Quality Variables</th></tr></thead>                              
<tr><td>Air quality units</td><td>$air["pm_units"]</td><td><?php echo $air["pm_units"];?></td></tr>
<tr><td>Current 2.5micron particles</td><td>$air["current.pm2_5"]</td><td><?php echo $air["current.pm2_5"];?></td></tr>
<tr><td>Rolling 24hour 2.5micron particle average</td><td>$air["24h.rollingavg.pm2_5"]</td><td><?php echo $air["24h.rollingavg.pm2_5"];?></td></tr>
<tr><td>Current 10.0micron particles</td><td>$air["current.pm10_0"]</td><td><?php echo $air["current.pm10_0"];?></td></tr>
<tr><td>Rolling 24hour 10_0micron particle average</td><td>$air["24h.rollingavg.pm10_0"]</td><td><?php echo $air["24h.rollingavg.pm10_0"];?></td></tr>
                                                            
<thead><tr><th colspan="3">Almanac Variables</th></tr></thead>
<tr><td>Sun altitude</td><td>$alm["sun_altitude"]</td><td><?php echo $alm["sun_altitude"];?></td></tr>
<tr><td>Sun always up or always down</td><td>$alm["sun_None"]</td><td><?php echo $alm["sun_None"];?></td></tr>
<tr><td>00:00 or 24:00</td><td>$alm["daylight_str"]</td><td><?php echo $alm["daylight_str"];?></td></tr>
<tr><td>Sun azimuth</td><td>$alm["sun_azimuth"]</td><td><?php echo $alm["sun_azimuth"];?></td></tr>
<tr><td>Moon azimuth</td><td>$alm["moon_azimuth"]</td><td><?php echo $alm["moon_azimuth"];?></td></tr>
<tr><td>Sunrise</td><td>$alm["sunrise"]</td><td><?php echo $alm["sunrise"];?></td></tr>
<tr><td>Sunset</td><td>$alm["sunset"]</td><td><?php echo $alm["sunset"];?></td></tr>
<tr><td>Sunrise date time</td><td>$alm["sunrise_date"]</td><td><?php echo $alm["sunrise_date"];?></td></tr>
<tr><td>Sunset date time</td><td>$alm["sunset_date"]</td><td><?php echo $alm["sunset_date"];?></td></tr>
<tr><td>Natural night or day</td><td>$dayPartNatural</td><td><?php echo $dayPartNatural;?></td></tr>
<tr><td>Sun right ascension</td><td>$alm["sun_right_ascension"]</td><td><?php echo $alm["sun_right_ascension"];?></td></tr>
<tr><td>Next Equinox</td><td>$alm["next_equinox"]</td><td><?php echo $alm["next_equinox"];?></td></tr>
<tr><td>Next Solstice</td><td>$alm["next_solstice"]</td><td><?php echo $alm["next_solstice"];?></td></tr>
<tr><td>Sidereal time</td><td>$alm["sidereal_time"]</td><td><?php echo $alm["sidereal_time"];?></td></tr>
<tr><td>Civil twilight begin</td><td>$alm["civil_twilight_begin"]</td><td><?php echo $alm["civil_twilight_begin"];?></td></tr>
<tr><td>Civil twilight end</td><td>$alm["civil_twilight_end"]</td><td><?php echo $alm["civil_twilight_end"];?></td></tr>
<tr><td>Civil night or day</td><td>$dayPartCivil</td><td><?php echo $dayPartCivil;?></td></tr>
<tr><td>Nautical twilight begin</td><td>$alm["nautical_twilight_begin"]</td><td><?php echo $alm["civil_twilight_begin"];?></td></tr>
<tr><td>Nautical twilight end</td><td>$alm["nautical_twilight_end"]</td><td><?php echo $alm["nautical_twilight_end"];?></td></tr>
<tr><td>Nautical night or day</td><td>$dayPartNautical</td><td><?php echo $dayPartNautical;?></td></tr>
<tr><td>Astronomical twilight begin</td><td>$alm["astronomical_twilight_begin"]</td><td><?php echo $alm["astronomical_twilight_begin"];?></td></tr>
<tr><td>Astronomical twilight end</td><td>$alm["astronomical_twilight_end"]</td><td><?php echo $alm["astronomical_twilight_end"];?></td></tr>
<tr><td>Sun meridian transit</td><td>$alm["sun_meridian_transit"]</td><td><?php echo $alm["sun_meridian_transit"];?></td></tr>
<tr><td>Moon meridian transit</td><td>$alm["moon_meridian_transit"]</td><td><?php echo $alm["moon_meridian_transit"];?></td></tr>
<tr><td>Moon phase</td><td>$alm["moonphase"]</td><td><?php echo $alm["moonphase"];?></td></tr>
<tr><td>Moon phase number</td><td>$alm["moonphase_number"]</td><td><?php echo $alm["moonphase_number"];?></td></tr>
<tr><td>Moon age</td><td>$alm["moon_age"]</td><td><?php echo $alm["moon_age"];?></td></tr>
<tr><td>Sun hour</td><td>$alm["hour_sun"]</td><td><?php echo $alm["hour_sun"];?></td></tr>
<tr><td>Moon hour</td><td>$alm["hour_moon"]</td><td><?php echo $alm["hour_moon"];?></td></tr>
<tr><td>Luminance</td><td>$alm["luminance"]</td><td><?php echo $alm["luminance"];?></td></tr>
<tr><td>Full Moon</td><td>$alm["fullmoon"]</td><td><?php echo $alm["fullmoon"];?></td></tr>
<tr><td>New Moon</td><td>$alm["newmoon"]</td><td><?php echo $alm["newmoon"];?></td></tr>
<tr><td>Daylight</td><td>$alm["daylight"]</td><td><?php echo $alm["daylight"];?></td></tr>
<tr><td>Moonrise</td><td>$alm["moonrise"]</td><td><?php echo $alm["moonrise"];?></td></tr>
<tr><td>Moonset</td><td>$alm["moonset"]</td><td><?php echo $alm["moonset"];?></td></tr>
<tr><td>Mercury heliocentric mean longitude</td><td>$alm["mercury_hlongitude"]</td><td><?php echo $alm["mercury_hlongitude"];?></td></tr>
<tr><td>Venus heliocentric mean longitude</td><td>$alm["venus_hlongitude"]</td><td><?php echo $alm["venus_hlongitude"];?></td></tr>
<tr><td>Earth heliocentric mean longitude</td><td>$alm["earth_hlongitude"]</td><td><?php echo $alm["earth_hlongitude"];?></td></tr>
<tr><td>Mars heliocentric mean longitude</td><td>$alm["mars_hlongitude"]</td><td><?php echo $alm["mars_hlongitude"];?></td></tr>
<tr><td>Jupiter heliocentric mean longitude</td><td>$alm["jupiter_hlongitude"]</td><td><?php echo $alm["jupiter_hlongitude"];?></td></tr>
<tr><td>Saturn heliocentric mean longitude</td><td>$alm["saturn_hlongitude"]</td><td><?php echo $alm["saturn_hlongitude"];?></td></tr>
<tr><td>Uranus heliocentric mean longitude</td><td>$alm["uranus_hlongitude"]</td><td><?php echo $alm["uranus_hlongitude"];?></td></tr>
<tr><td>Neptune heliocentric mean longitude</td><td>$alm["neptune_hlongitude"]</td><td><?php echo $alm["neptune_hlongitude"];?></td></tr>
<tr><td>Pluto heliocentric mean longitude</td><td>$alm["pluto_hlongitude"]</td><td><?php echo $alm["pluto_hlongitude"];?></td></tr>
                              
<thead><tr><th colspan="3">Barometric Variables</th></tr></thead>                              
<tr><td>Barometric units</td><td>$barom["units"]</td><td><?php echo $barom["units"];?></td></tr>
<tr><td>Realtime barometer</td><td>$barom["now"]</td><td><?php echo $barom["now"];?></td></tr>
<tr><td>Day maximum barometer</td><td>$barom["max"]</td><td><?php echo $barom["max"];?></td></tr>
<tr><td>Day maximum barometer time</td><td>$barom["maxtime"]</td><td><?php echo $barom["maxtime"];?></td></tr>
<tr><td>Day minimum barometer</td><td>$barom["min"]</td><td><?php echo $barom["min"];?></td></tr>
<tr><td>Day minimum barometer time</td><td>$barom["mintime"]</td><td><?php echo $barom["mintime"];?></td></tr>
<tr><td>Day trend code</td><td>$barom["tred_code"]</td><td><?php echo $barom["trend_code"];?></td></tr>                              
<tr><td>Day trend description</td><td>$barom["tred_desc"]</td><td><?php echo $barom["trend_desc"];?></td></tr>                              
<tr><td>Yesterday maximum barometer</td><td>$barom["yesterday_max"]</td><td><?php echo $barom["yesterday_max"];?></td></tr>
<tr><td>Yesterday maximum barometer time</td><td>$barom["yesterday_maxtime"]</td><td><?php echo $barom["yesterday_maxtime"];?></td></tr>
<tr><td>Yesterday minimum barometer</td><td>$barom["yesterday_min"]</td><td><?php echo $barom["yesterday_min"];?></td></tr>
<tr><td>Yesterday minimum barometer time</td><td>$barom["yesterday_mintime"]</td><td><?php echo $barom["yesterday_mintime"];?></td></tr>
<tr><td>Month maximum barometer</td><td>$barom["month_max"]</td><td><?php echo $barom["month_max"];?></td></tr>
<tr><td>Month maximum barometer time</td><td>$barom["month_maxtime"]</td><td><?php echo $barom["month_maxtime"];?></td></tr>
<tr><td>Month minimum barometer</td><td>$barom["month_min"]</td><td><?php echo $barom["month_min"];?></td></tr>
<tr><td>Month minimum barometer time</td><td>$barom["month_mintime"]</td><td><?php echo $barom["month_mintime"];?></td></tr>
<tr><td>Year maximum barometer</td><td>$barom["year_max"]</td><td><?php echo $barom["year_max"];?></td></tr>
<tr><td>Year maximum barometer time</td><td>$barom["year_maxtime"]</td><td><?php echo $barom["year_maxtime"];?></td></tr>
<tr><td>Year minimum barometer</td><td>$barom["year_min"]</td><td><?php echo $barom["year_min"];?></td></tr>
<tr><td>Year minimum barometer time</td><td>$barom["year_mintime"]</td><td><?php echo $barom["year_mintime"];?></td></tr>
<tr><td>Alltime maximum barometer</td><td>$barom["alltime_max"]</td><td><?php echo $barom["alltime_max"];?></td></tr>
<tr><td>Alltime maximum barometer time</td><td>$barom["alltime_maxtime"]</td><td><?php echo $barom["alltime_maxtime"];?></td></tr>
<tr><td>Alltime minimum barometer</td><td>$barom["alltime_min"]</td><td><?php echo $barom["alltime_min"];?></td></tr>
<tr><td>Alltime minimum barometer time</td><td>$barom["alltime_mintime"]</td><td><?php echo $barom["alltime_mintime"];?></td></tr>
                              
<thead><tr><th colspan="3">Color Variables</th></tr></thead>                             
<tr><td>Temperature 60min average</td><td>$color["outTemp_60min_avg"]</td><td><?php echo $color["outTemp_60min_avg"];?></td></tr>                                 
<tr><td>Temperature day maximum</td><td>$t[0]</td><td><?php echo $t[0];?></td></tr>
<tr><td>Temperature day minimum</td><td>$t[1]</td><td><?php echo $t[1];?></td></tr>
<tr><td>Dew day maximum</td><td>$t[2]</td><td><?php echo $t[2];?></td></tr>
<tr><td>Dew day minimum</td><td>$t[3]</td><td><?php echo $t[3];?></td></tr>
<tr><td>Humidity day maximum</td><td>$t[4]</td><td><?php echo $t[4];?></td></tr>
<tr><td>Humidity day minimum</td><td>$t[5]</td><td><?php echo $t[5];?></td></tr>
<tr><td>Temperature yesterday maximum</td><td>$d[0]</td><td><?php echo $d[0];?></td></tr>
<tr><td>Temperature yesterday minimum</td><td>$d[1]</td><td><?php echo $d[1];?></td></tr>
<tr><td>Dew yesterday maximum</td><td>$d[2]</td><td><?php echo $d[2];?></td></tr>
<tr><td>Dew yesterday minimum</td><td>$d[3]</td><td><?php echo $d[3];?></td></tr>
<tr><td>Humidity yesterday maximum</td><td>$d[4]</td><td><?php echo $d[4];?></td></tr>
<tr><td>Humidity yesterday minimum</td><td>$d[5]</td><td><?php echo $d[5];?></td></tr>
<tr><td>Temperature month maximum</td><td>$m[0]</td><td><?php echo $m[0];?></td></tr>
<tr><td>Temperature month minimum</td><td>$m[1]</td><td><?php echo $m[1];?></td></tr>
<tr><td>Dew month maximum</td><td>$m[2]</td><td><?php echo $m[2];?></td></tr>
<tr><td>Dew month minimum</td><td>$m[3]</td><td><?php echo $m[3];?></td></tr>
<tr><td>Humidity month maximum</td><td>$m[4]</td><td><?php echo $m[4];?></td></tr>
<tr><td>Humidity month minimum</td><td>$m[5]</td><td><?php echo $m[5];?></td></tr>
<tr><td>Temperature year maximum</td><td>$y[0]</td><td><?php echo $y[0];?></td></tr>
<tr><td>Temperature year minimum</td><td>$y[1]</td><td><?php echo $y[1];?></td></tr>
<tr><td>Dew year maximum</td><td>$y[2]</td><td><?php echo $y[2];?></td></tr>
<tr><td>Dew year minimum</td><td>$y[3]</td><td><?php echo $y[3];?></td></tr>
<tr><td>Humidity year maximum</td><td>$y[4]</td><td><?php echo $y[4];?></td></tr>
<tr><td>Humidity year minimum</td><td>$y[5]</td><td><?php echo $y[5];?></td></tr>
<tr><td>Temperature alltime maximum</td><td>$a[0]</td><td><?php echo $a[0];?></td></tr>
<tr><td>Temperature alltime minimum</td><td>$a[1]</td><td><?php echo $a[1];?></td></tr>
<tr><td>Dew alltime maximum</td><td>$a[2]</td><td><?php echo $a[2];?></td></tr>
<tr><td>Dew alltime minimum</td><td>$a[3]</td><td><?php echo $a[3];?></td></tr>
<tr><td>Humidity alltime maximum</td><td>$a[4]</td><td><?php echo $a[4];?></td></tr>
<tr><td>Humidity alltime minimum</td><td>$a[5]</td><td><?php echo $a[5];?></td></tr>
<tr><td>Wind speed 10min average</td><td>$color["windSpeed_10min_avg"]</td><td><?php echo $color["windSpeed_10min_avg"];?></td></tr>                                 
<tr><td>Wind gust 10min maximum</td><td>$color["windGust_10min_max"]</td><td><?php echo $color["windGust_10min_max"];?></td></tr>                                 

<thead><tr><th colspan="3">Dewpoint Variables</th></tr></thead>                              
<tr><td>Dewpoint realtime</td><td>$dew["now"]</td><td><?php echo $dew["now"];?></td></tr>                                 
<tr><td>Dewpoint day trend</td><td>$dew["trend"]</td><td><?php echo $dew["trend"];?></td></tr>                                 
<tr><td>Dewpoint day maximum</td><td>$dew["day_max"]</td><td><?php echo $dew["day_max"];?></td></tr>                                 
<tr><td>Dewpoint day maximum time</td><td>$dew["day_maxtime"]</td><td><?php echo $dew["day_maxtime"];?></td></tr>                                 
<tr><td>Dewpoint day minimum</td><td>$dew["day_min"]</td><td><?php echo $dew["day_min"];?></td></tr>                                 
<tr><td>Dewpoint day minimum time</td><td>$dew["day_mintime"]</td><td><?php echo $dew["day_mintime"];?></td></tr>                                 
<tr><td>Dewpoint yesterday maximum</td><td>$dew["yesterday_max"]</td><td><?php echo $dew["yesterday_max"];?></td></tr>                                 
<tr><td>Dewpoint yesterday maximum time</td><td>$dew["yesterday_maxtime"]</td><td><?php echo $dew["yesterday_maxtime"];?></td></tr>                                 
<tr><td>Dewpoint yesterday minimum</td><td>$dew["yesterday_min"]</td><td><?php echo $dew["yesterday_min"];?></td></tr>                                 
<tr><td>Dewpoint yesterday minimum time</td><td>$dew["yesterday_mintime"]</td><td><?php echo $dew["yesterday_mintime"];?></td></tr>                                 
<tr><td>Dewpoint month maximum</td><td>$dew["month_max"]</td><td><?php echo $dew["month_max"];?></td></tr>                                 
<tr><td>Dewpoint month maximum time</td><td>$dew["month_maxtime"]</td><td><?php echo $dew["month_maxtime"];?></td></tr>                                 
<tr><td>Dewpoint month minimum</td><td>$dew["month_min"]</td><td><?php echo $dew["month_min"];?></td></tr>                                 
<tr><td>Dewpoint month minimum time</td><td>$dew["month_mintime"]</td><td><?php echo $dew["month_mintime"];?></td></tr>                                 
<tr><td>Dewpoint year maximum</td><td>$dew["year_max"]</td><td><?php echo $dew["year_max"];?></td></tr>                                 
<tr><td>Dewpoint year maximum time</td><td>$dew["year_maxtime"]</td><td><?php echo $dew["year_maxtime"];?></td></tr>                                 
<tr><td>Dewpoint year minimum</td><td>$dew["year_min"]</td><td><?php echo $dew["year_min"];?></td></tr>                                 
<tr><td>Dewpoint year minimum time</td><td>$dew["year_mintime"]</td><td><?php echo $dew["year_mintime"];?></td></tr>                                 
<tr><td>Dewpoint alltime maximum</td><td>$dew["alltime_max"]</td><td><?php echo $dew["alltime_max"];?></td></tr>                                 
<tr><td>Dewpoint alltime maximum time</td><td>$dew["alltime_maxtime"]</td><td><?php echo $dew["alltime_maxtime"];?></td></tr>                                 
<tr><td>Dewpoint alltime minimum</td><td>$dew["alltime_min"]</td><td><?php echo $dew["alltime_min"];?></td></tr>                                 
<tr><td>Dewpoint alltime minimum time</td><td>$dew["alltime_mintime"]</td><td><?php echo $dew["alltime_mintime"];?></td></tr>                                 

<thead><tr><th colspan="3">Humidity Variables</th></tr></thead> 
<tr><td>Humidity realtime</td><td>$humid["now"]</td><td><?php echo $humid["now"];?></td></tr>                                 
<tr><td>Humidity day trend</td><td>$humid["trend"]</td><td><?php echo $humid["trend"];?></td></tr>                                 
<tr><td>Humidity day maximum</td><td>$humid["day_max"]</td><td><?php echo $humid["day_max"];?></td></tr>                                 
<tr><td>Humidity day maximum time</td><td>$humid["day_maxtime"]</td><td><?php echo $humid["day_maxtime"];?></td></tr>                                 
<tr><td>Humidity day minimum</td><td>$humid["day_min"]</td><td><?php echo $humid["day_min"];?></td></tr>                                 
<tr><td>Humidity day minimum time</td><td>$humid["day_mintime"]</td><td><?php echo $humid["day_mintime"];?></td></tr>                                 
<tr><td>Humidity yesterday maximum</td><td>$humid["yesterday_max"]</td><td><?php echo $humid["yesterday_max"];?></td></tr>                                 
<tr><td>Humidity yesterday maximum time</td><td>$humid["yesterday_maxtime"]</td><td><?php echo $humid["yesterday_maxtime"];?></td></tr>                                 
<tr><td>Humidity yesterday minimum</td><td>$humid["yesterday_min"]</td><td><?php echo $humid["yesterday_min"];?></td></tr>                                 
<tr><td>Humidity yesterday minimum time</td><td>$humid["yesterday_mintime"]</td><td><?php echo $humid["yesterday_mintime"];?></td></tr>                                 
<tr><td>Humidity month maximum</td><td>$humid["month_max"]</td><td><?php echo $humid["month_max"];?></td></tr>                                 
<tr><td>Humidity month maximum time</td><td>$humid["month_maxtime"]</td><td><?php echo $humid["month_maxtime"];?></td></tr>                                 
<tr><td>Humidity month minimum</td><td>$humid["month_min"]</td><td><?php echo $humid["month_min"];?></td></tr>                                 
<tr><td>Humidity month minimum time</td><td>$humid["month_mintime"]</td><td><?php echo $humid["month_mintime"];?></td></tr>                                 
<tr><td>Humidity year maximum</td><td>$humid["year_max"]</td><td><?php echo $humid["year_max"];?></td></tr>                                 
<tr><td>Humidity year maximum time</td><td>$humid["year_maxtime"]</td><td><?php echo $humid["year_maxtime"];?></td></tr>                                 
<tr><td>Humidity year minimum</td><td>$humid["year_min"]</td><td><?php echo $humid["year_min"];?></td></tr>                                 
<tr><td>Humidity year minimum time</td><td>$humid["year_mintime"]</td><td><?php echo $humid["year_mintime"];?></td></tr>                                 
<tr><td>Humidity alltime maximum</td><td>$humid["alltime_max"]</td><td><?php echo $humid["alltime_max"];?></td></tr>                                 
<tr><td>Humidity alltime maximum time</td><td>$humid["alltime_maxtime"]</td><td><?php echo $humid["alltime_maxtime"];?></td></tr>                                 
<tr><td>Humidity alltime minimum</td><td>$humid["alltime_min"]</td><td><?php echo $humid["alltime_min"];?></td></tr>                                 
<tr><td>Humidity alltime minimum time</td><td>$humid["alltime_mintime"]</td><td><?php echo $humid["alltime_mintime"];?></td></tr>                                 
<tr><td>Humidity indoors  realtime</td><td>$humid["indoors_now"]</td><td><?php echo $humid["indoors_now"];?></td></tr>                                 
<tr><td>Humidity indoors  day trend</td><td>$humid["indoors_trend"]</td><td><?php echo $humid["indoors_trend"];?></td></tr>                                 
<tr><td>Humidity indoors  day maximum</td><td>$humid["indoors_day_max"]</td><td><?php echo $humid["indoors_day_max"];?></td></tr>                                 
<tr><td>Humidity indoors  day maximum time</td><td>$humid["indoors_day_maxtime"]</td><td><?php echo $humid["indoors_day_maxtime"];?></td></tr>                                 
<tr><td>Humidity indoors  day minimum</td><td>$humid["indoors_day_min"]</td><td><?php echo $humid["indoors_day_min"];?></td></tr>                                 
<tr><td>Humidity indoors  day minimum time</td><td>$humid["indoors_day_mintime"]</td><td><?php echo $humid["indoors_day_mintime"];?></td></tr>                                 
<tr><td>Humidity indoors  yesterday maximum</td><td>$humid["indoors_yesterday_max"]</td><td><?php echo $humid["indoors_yesterday_max"];?></td></tr>                                 
<tr><td>Humidity indoors  yesterday maximum time</td><td>$humid["indoors_yesterday_maxtime"]</td><td><?php echo $humid["indoors_yesterday_maxtime"];?></td></tr>                                 
<tr><td>Humidity indoors  yesterday minimum</td><td>$humid["indoors_yesterday_min"]</td><td><?php echo $humid["indoors_yesterday_min"];?></td></tr>                                 
<tr><td>Humidity indoors  yesterday minimum time</td><td>$humid["indoors_yesterday_mintime"]</td><td><?php echo $humid["indoors_yesterday_mintime"];?></td></tr>                                 
<tr><td>Humidity indoors  month maximum</td><td>$humid["indoors_month_max"]</td><td><?php echo $humid["indoors_month_max"];?></td></tr>                                 
<tr><td>Humidity indoors  month maximum time</td><td>$humid["indoors_month_maxtime"]</td><td><?php echo $humid["indoors_month_maxtime"];?></td></tr>                                 
<tr><td>Humidity indoors  month minimum</td><td>$humid["indoors_month_min"]</td><td><?php echo $humid["indoors_month_min"];?></td></tr>                                 
<tr><td>Humidity indoors  month minimum time</td><td>$humid["indoors_month_mintime"]</td><td><?php echo $humid["indoors_month_mintime"];?></td></tr>                                 
<tr><td>Humidity indoors  year maximum</td><td>$humid["indoors_year_max"]</td><td><?php echo $humid["indoors_year_max"];?></td></tr>                                 
<tr><td>Humidity indoors  year maximum time</td><td>$humid["indoors_year_maxtime"]</td><td><?php echo $humid["indoors_year_maxtime"];?></td></tr>                                 
<tr><td>Humidity indoors  year minimum</td><td>$humid["indoors_year_min"]</td><td><?php echo $humid["indoors_year_min"];?></td></tr>                                 
<tr><td>Humidity indoors  year minimum time</td><td>$humid["indoors_year_mintime"]</td><td><?php echo $humid["indoors_year_mintime"];?></td></tr>                                 
<tr><td>Humidity indoors  alltime maximum</td><td>$humid["indoors_alltime_max"]</td><td><?php echo $humid["indoors_alltime_max"];?></td></tr>                                 
<tr><td>Humidity indoors  alltime maximum time</td><td>$humid["indoors_alltime_maxtime"]</td><td><?php echo $humid["indoors_alltime_maxtime"];?></td></tr>                                 
<tr><td>Humidity indoors  alltime minimum</td><td>$humid["indoors_alltime_min"]</td><td><?php echo $humid["indoors_alltime_min"];?></td></tr>                                 
<tr><td>Humidity indoors  alltime minimum time</td><td>$humid["indoors_alltime_mintime"]</td><td><?php echo $humid["indoors_alltime_mintime"];?></td></tr>                                 

<thead><tr><th colspan="3">Lightning Variables</th></tr></thead>
<tr><td>Lightning last strike time</td><td>$lightning["last_strike_time"]</td><td><?php echo $lightning["last_strike_time"];?></td></tr>                                 
<tr><td>Lightning last strike time</td><td>$lightning["last_time"]</td><td><?php echo $lightning["last_time"];?></td></tr>                                 
<tr><td>Lightning last strike time ago</td><td>$lightning["time_ago"]</td><td><?php echo $lightning["time_ago"];?></td></tr>                                 
<tr><td>Lightning last distance</td><td>$lightning["last_distance"]</td><td><?php echo $lightning["last_distance"];?></td></tr>                                 
<tr><td>Lightning current strike count</td><td>$lightning["current_strike_count"]</td><td><?php echo $lightning["current_strike_count"];?></td></tr>                                 
<tr><td>Lightning hour strike count</td><td>$lightning["hour_strike_count"]</td><td><?php echo $lightning["hour_strike_count"];?></td></tr>                                 
<tr><td>Lightning day strike count</td><td>$lightning["today_strike_count"]</td><td><?php echo $lightning["today_strike_count"];?></td></tr>                                 
<tr><td>Lightning month strike count</td><td>$lightning["month_strike_count"]</td><td><?php echo $lightning["month_strike_count"];?></td></tr>                                 
<tr><td>Lightning year strike count</td><td>$lightning["year_strike_count"]</td><td><?php echo $lightning["year_strike_count"];?></td></tr>                                 
<tr><td>Lightning alltime strike count</td><td>$lightning["alltime_strike_count"]</td><td><?php echo $lightning["alltime_strike_count"];?></td></tr>                                 

<thead><tr><th colspan="3">Rain Variables</th></tr></thead> 
<tr><td>Rain units</td><td>$rain["units"]</td><td><?php echo $rain["units"];?></td></tr>                                 
<tr><td>Rain realtime</td><td>$rain["current"]</td><td><?php echo $rain["current"];?></td></tr>                                 
<tr><td>Rain rate</td><td>$rain["rate"]</td><td><?php echo $rain["rate"];?></td></tr>                                 
<tr><td>Rain total</td><td>$rain["total"]</td><td><?php echo $rain["total"];?></td></tr>                                 
<tr><td>Rain last 10min</td><td>$rain["last_10min"]</td><td><?php echo $rain["last_10min"];?></td></tr>                                 
<tr><td>Rain last hour</td><td>$rain["last_hour"]</td><td><?php echo $rain["last_hour"];?></td></tr>                                 
<tr><td>Rain last 24 hours</td><td>$rain["last_24hour"]</td><td><?php echo $rain["last_24hour"];?></td></tr>                                 
<tr><td>Rain last 24 hours maximum rate</td><td>$rain["24h_rate_max"]</td><td><?php echo $rain["24h_rate_max"];?></td></tr>                                 
<tr><td>Rain last 24 hours maximum rate time</td><td>$rain["24h_rate_maxtime"]</td><td><?php echo $rain["24h_rate_maxtime"];?></td></tr>                                 
<tr><td>Rain last 24 hours total</td><td>$rain["24h_total"]</td><td><?php echo $rain["24h_total"];?></td></tr>                                 
<tr><td>Rain day maximum rate</td><td>$rain["day_rate_max"]</td><td><?php echo $rain["day_rate_max"];?></td></tr>                                 
<tr><td>Rain day maximum rate time</td><td>$rain["day_rate_maxtime"]</td><td><?php echo $rain["day_rate_maxtime"];?></td></tr>                                 
<tr><td>Rain day total</td><td>$rain["day_total"]</td><td><?php echo $rain["day_total"];?></td></tr>                                 
<tr><td>Rain yesterday maximum rate</td><td>$rain["yesterday_rate_max"]</td><td><?php echo $rain["yesterday_rate_max"];?></td></tr>                                 
<tr><td>Rain yesterday maximum rate time</td><td>$rain["yesterday_rate_maxtime"]</td><td><?php echo $rain["yesterday_rate_maxtime"];?></td></tr>                                 
<tr><td>Rain yesterday</td><td>$rain["yesterday"]</td><td><?php echo $rain["yesterday"];?></td></tr>                                 
<tr><td>Rain month maximum rate</td><td>$rain["month_rate_max"]</td><td><?php echo $rain["month_rate_max"];?></td></tr>                                 
<tr><td>Rain month maximum rate time</td><td>$rain["month_rate_maxtime"]</td><td><?php echo $rain["month_rate_maxtime"];?></td></tr>                                 
<tr><td>Rain month total</td><td>$rain["month_total"]</td><td><?php echo $rain["month_total"];?></td></tr>                                 
<tr><td>Rain year maximum rate</td><td>$rain["year_rate_max"]</td><td><?php echo $rain["year_rate_max"];?></td></tr>                                 
<tr><td>Rain year maximum rate time</td><td>$rain["year_rate_maxtime"]</td><td><?php echo $rain["year_rate_maxtime"];?></td></tr>                                 
<tr><td>Rain year total</td><td>$rain["year_total"]</td><td><?php echo $rain["year_total"];?></td></tr>                                 
<tr><td>Rain alltime maximum rate</td><td>$rain["alltime_rate_max"]</td><td><?php echo $rain["alltime_rate_max"];?></td></tr>                                 
<tr><td>Rain alltime maximum rate time</td><td>$rain["alltime_rate_maxtime"]</td><td><?php echo $rain["alltime_rate_maxtime"];?></td></tr>                                 
<tr><td>Storm Rain</td><td>$rain["storm_rain"]</td><td><?php echo $rain["storm_rain"];?></td></tr>                                 

<thead><tr><th colspan="3">Sky Variables</th></tr></thead>
<tr><td>Lux</td><td>$sky["lux"]</td><td><?php echo $sky["lux"];?></td></tr>                                 
<tr><td>Lux day maximum</td><td>$sky["day_lux_max"]</td><td><?php echo $sky["day_lux_max"];?></td></tr>                                 
<tr><td>Cloud base</td><td>$sky["cloud_base"]</td><td><?php echo $sky["cloud_base"];?></td></tr>                                 
<tr><td>Cloud cover</td><td>$sky["cloud_cover"]</td><td><?php echo $sky["cloud_cover"];?></td></tr>                                 

<thead><tr><th colspan="3">Solar Radiation Variables</th></tr></thead>
<tr><td>Solar radiation realtime</td><td>$solar["now"]</td><td><?php echo $solar["now"];?></td></tr>                                 
<tr><td>Solar radiation day maximum</td><td>$solar["day_max"]</td><td><?php echo $solar["day_max"];?></td></tr>                                 
<tr><td>Solar radiation day maximum</td><td>$solar["day_maxtime"]</td><td><?php echo $solar["day_maxtime"];?></td></tr>                                 
<tr><td>Solar radiation yesterday maximum</td><td>$solar["yesterday_max"]</td><td><?php echo $solar["yesterday_max"];?></td></tr>                                 
<tr><td>Solar radiation yesterday maximum</td><td>$solar["yesterday_maxtime"]</td><td><?php echo $solar["yesterday_maxtime"];?></td></tr>                                 
<tr><td>Solar radiation month maximum</td><td>$solar["month_max"]</td><td><?php echo $solar["month_max"];?></td></tr>                                 
<tr><td>Solar radiation month maximum</td><td>$solar["month_maxtime"]</td><td><?php echo $solar["month_maxtime"];?></td></tr>                                 
<tr><td>Solar radiation year maximum</td><td>$solar["year_max"]</td><td><?php echo $solar["year_max"];?></td></tr>                                 
<tr><td>Solar radiation year maximum</td><td>$solar["year_maxtime"]</td><td><?php echo $solar["year_maxtime"];?></td></tr>                                 
<tr><td>Solar radiation alltime maximum</td><td>$solar["alltime_max"]</td><td><?php echo $solar["alltime_max"];?></td></tr>                                 
<tr><td>Solar radiation alltime maximum</td><td>$solar["alltime_maxtime"]</td><td><?php echo $solar["alltime_maxtime"];?></td></tr>                                 

<thead><tr><th colspan="3">Temperature Variables</th></tr></thead> 
<tr><td>Temperature units</td><td>$temp["units"]</td><td><?php echo $temp["units"];?></td></tr>                                 
<tr><td>Indoor temperature realtime</td><td>$temp["indoor_now"]</td><td><?php echo $temp["indoor_now"];?></td></tr>                                 
<tr><td>Indoor temperature day trend</td><td>$temp["indoor_trend"]</td><td><?php echo $temp["indoor_trend"];?></td></tr>                                 
<tr><td>Indoor temperature day maximum</td><td>$temp["indoor_day_max"]</td><td><?php echo $temp["indoor_day_max"];?></td></tr>                                 
<tr><td>Indoor temperature day minimum time</td><td>$temp["indoor_day_min"]</td><td><?php echo $temp["indoor_day_min"];?></td></tr>                                 
<tr><td>Indoor temperature feels like (Farenheit)</td><td>$temp["indoor_now_feels"]</td><td><?php echo $temp["indoor_now_feels"];?></td></tr>                                 
<tr><td>Outside temperature realtime</td><td>$temp["outside_now"]</td><td><?php echo $temp["outside_now"];?></td></tr>                                 
<tr><td>Outside temperature day trend</td><td>$temp["outside_trend"]</td><td><?php echo $temp["outside_trend"];?></td></tr>                                 
<tr><td>Apparent temperature realtime</td><td>$temp["apptemp"]</td><td><?php echo $temp["apptemp"];?></td></tr>                                 
<tr><td>Heat Index realtime</td><td>$temp["heatindex"]</td><td><?php echo $temp["heatindex"];?></td></tr>                                 
<tr><td>Wind chill realtime</td><td>$temp["windchill"]</td><td><?php echo $temp["windchill"];?></td></tr>                                 
<tr><td>Humidex realtime</td><td>$temp["humidex"]</td><td><?php echo $temp["humidex"];?></td></tr>                                 
<tr><td>Outside temperature 60min average</td><td>$temp["outside_day_avg_60mn"]</td><td><?php echo $temp["outside_day_avg_60mn"];?></td></tr>                                 
<tr><td>Outside temperature day average</td><td>$temp["outside_day_avg"]</td><td><?php echo $temp["outside_day_avg"];?></td></tr>                                 
<tr><td>Outside temperature day maximum</td><td>$temp["outside_day_max"]</td><td><?php echo $temp["outside_day_max"];?></td></tr>                                 
<tr><td>Outside temperature day maximum time</td><td>$temp["outside_day_maxtime"]</td><td><?php echo $temp["outside_day_maxtime"];?></td></tr>                                 
<tr><td>Outside temperature day minimum</td><td>$temp["outside_day_min"]</td><td><?php echo $temp["outside_day_min"];?></td></tr>                                 
<tr><td>Outside temperature day minimum time</td><td>$temp["outside_day_mintime"]</td><td><?php echo $temp["outside_day_mintime"];?></td></tr>                                 
<tr><td>Outside temperature yesterday maximum</td><td>$temp["outside_yesterday_max"]</td><td><?php echo $temp["outside_yesterday_max"];?></td></tr>                                 
<tr><td>Outside temperature yesterday maximum time</td><td>$temp["outside_yesterday_maxtime"]</td><td><?php echo $temp["outside_yesterday_maxtime"];?></td></tr>                                 
<tr><td>Outside temperature yesterday minimum</td><td>$temp["outside_yesterday_min"]</td><td><?php echo $temp["outside_yesterday_min"];?></td></tr>                                 
<tr><td>Outside temperature yesterday minimum time</td><td>$temp["outside_yesterday_mintime"]</td><td><?php echo $temp["outside_yesterday_mintime"];?></td></tr>                                 
<tr><td>Outside temperature month maximum</td><td>$temp["outside_month_max"]</td><td><?php echo $temp["outside_month_max"];?></td></tr>                                 
<tr><td>Outside temperature month maximum time</td><td>$temp["outside_month_maxtime"]</td><td><?php echo $temp["outside_month_maxtime"];?></td></tr>                                 
<tr><td>Outside temperature month minimum</td><td>$temp["outside_month_min"]</td><td><?php echo $temp["outside_month_min"];?></td></tr>                                 
<tr><td>Outside temperature month minimum time</td><td>$temp["outside_month_mintime"]</td><td><?php echo $temp["outside_month_mintime"];?></td></tr>                                 
<tr><td>Outside temperature year maximum</td><td>$temp["outside_year_max"]</td><td><?php echo $temp["outside_year_max"];?></td></tr>                                 
<tr><td>Outside temperature year maximum time</td><td>$temp["outside_year_maxtime"]</td><td><?php echo $temp["outside_year_maxtime"];?></td></tr>                                 
<tr><td>Outside temperature year minimum</td><td>$temp["outside_year_min"]</td><td><?php echo $temp["outside_year_min"];?></td></tr>                                 
<tr><td>Outside temperature year minimum time</td><td>$temp["outside_year_mintime"]</td><td><?php echo $temp["outside_year_mintime"];?></td></tr>                                 
<tr><td>Outside temperature alltime maximum</td><td>$temp["outside_alltime_max"]</td><td><?php echo $temp["outside_alltime_max"];?></td></tr>                                 
<tr><td>Outside temperature alltime maximum time</td><td>$temp["outside_alltime_maxtime"]</td><td><?php echo $temp["outside_alltime_maxtime"];?></td></tr>                                 
<tr><td>Outside temperature alltime minimum</td><td>$temp["outside_alltime_min"]</td><td><?php echo $temp["outside_alltime_min"];?></td></tr>                                 
<tr><td>Outside temperature alltime minimum time</td><td>$temp["outside_alltime_mintime"]</td><td><?php echo $temp["outside_alltime_mintime"];?></td></tr>                                 

<thead><tr><th colspan="3">UV Index Variables</th></tr></thead>
<tr><td>UVI realtime</td><td>$uv["now"]</td><td><?php echo $uv["now"];?></td></tr>                                 
<tr><td>UVI day maximum</td><td>$uv["day_max"]</td><td><?php echo $uv["day_max"];?></td></tr>                                 
<tr><td>UVI day maximum time</td><td>$uv["day_maxtime"]</td><td><?php echo $uv["day_maxtime"];?></td></tr>                                 
<tr><td>UVI yesterday maximum</td><td>$uv["yesterday_max"]</td><td><?php echo $uv["yesterday_max"];?></td></tr>                                 
<tr><td>UVI yesterday maximum time</td><td>$uv["yesterday_maxtime"]</td><td><?php echo $uv["yesterday_maxtime"];?></td></tr>                                 
<tr><td>UVI month maximum</td><td>$uv["month_max"]</td><td><?php echo $uv["month_max"];?></td></tr>                                 
<tr><td>UVI month maximum time</td><td>$uv["month_maxtime"]</td><td><?php echo $uv["month_maxtime"];?></td></tr>                                 
<tr><td>UVI year maximum</td><td>$uv["year_max"]</td><td><?php echo $uv["year_max"];?></td></tr>                                 
<tr><td>UVI year maximum time</td><td>$uv["year_maxtime"]</td><td><?php echo $uv["year_maxtime"];?></td></tr>                                 
<tr><td>UVI alltime maximum</td><td>$uv["alltime_max"]</td><td><?php echo $uv["alltime_max"];?></td></tr>                                 
<tr><td>UVI alltime maximum time</td><td>$uv["alltime_maxtime"]</td><td><?php echo $uv["alltime_maxtime"];?></td></tr>                                 
                                
<thead><tr><th colspan="3">Wind Variables</th></tr></thead> 
<tr><td>Wind units</td><td>$wind["units"]</td><td><?php echo $wind["units"];?></td></tr>                                 
<tr><td>Wind direction realtime</td><td>$wind["direction"]</td><td><?php echo $wind["direction"];?></td></tr>                                 
<tr><td>Wind ordinal compass realtime</td><td>$wind["cardinal"]</td><td><?php echo $wind["cardinal"];?></td></tr>                                 
<tr><td>Wind direction 10min average</td><td>$wind["direction_10m_avg"]</td><td><?php echo $wind["direction_10m_avg"];?></td></tr>                                 
<tr><td>Wind direction trend</td><td>$wind["direction_trend"]</td><td><?php echo $wind["direction_trend"];?></td></tr>                                 
<tr><td>Wind direction trend ordinal compass</td><td>$wind["direction_trend_ordinal"]</td><td><?php echo $wind["direction_trend_ordinal"];?></td></tr>                                 
<tr><td>Wind speed realtime</td><td>$wind["speed"]</td><td><?php echo $wind["speed"];?></td></tr>                                 
<tr><td>Wind gust realtime</td><td>$wind["gust"]</td><td><?php echo $wind["gust"];?></td></tr>
<tr><td>Wind Beaufort Scale</td><td>$wind["speed_bft"]</td><td><?php echo $wind["speed_bft"];?></td></tr>                                 
<tr><td>Wind speed 10min average</td><td>$wind["speed_10m_avg"]</td><td><?php echo $wind["speed_10m_avg"];?></td></tr>                                 
<tr><td>Wind speed 10min maximum</td><td>$wind["speed_10m_max"]</td><td><?php echo $wind["speed_10m_max"];?></td></tr>                                 
<tr><td>Wind gust 10min maximum</td><td>$wind["gust_10m_max"]</td><td><?php echo $wind["gust_10m_max"];?></td></tr>                                 
<tr><td>Wind run</td><td>$wind["wind_run"]</td><td><?php echo $wind["wind_run"];?></td></tr>                                 
<tr><td>Wind speed day maximum</td><td>$wind["speed_max"]</td><td><?php echo $wind["speed_max"];?></td></tr>                                 
<tr><td>Wind speed day maximum time</td><td>$wind["speed_maxtime"]</td><td><?php echo $wind["speed_maxtime"];?></td></tr>                                 
<tr><td>Wind gust day maximum</td><td>$wind["gust_max"]</td><td><?php echo $wind["gust_max"];?></td></tr>                                 
<tr><td>Wind gust day maximum time</td><td>$wind["gust_maxtime"]</td><td><?php echo $wind["gust_maxtime"];?></td></tr>                                 
<tr><td>Wind speed yesterday maximum</td><td>$wind["speed_yesterday_max"]</td><td><?php echo $wind["speed_yesterday_max"];?></td></tr>                                 
<tr><td>Wind speed yesterday maximum time</td><td>$wind["speed_yesterday_maxtime"]</td><td><?php echo $wind["speed_yesterday_maxtime"];?></td></tr>                                 
<tr><td>Wind gust yesterday maximum</td><td>$wind["gust_yesterday_max"]</td><td><?php echo $wind["gust_yesterday_max"];?></td></tr>                                 
<tr><td>Wind gust yesterday maximum time</td><td>$wind["gust_yesterday_maxtime"]</td><td><?php echo $wind["gust_yesterday_maxtime"];?></td></tr>                                 
<tr><td>Wind speed month maximum</td><td>$wind["speed_month_max"]</td><td><?php echo $wind["speed_month_max"];?></td></tr>                                 
<tr><td>Wind speed month maximum time</td><td>$wind["speed_month_maxtime"]</td><td><?php echo $wind["speed_month_maxtime"];?></td></tr>                                 
<tr><td>Wind gust month maximum</td><td>$wind["gust_month_max"]</td><td><?php echo $wind["gust_month_max"];?></td></tr>                                 
<tr><td>Wind gust month maximum time</td><td>$wind["gust_month_maxtime"]</td><td><?php echo $wind["gust_month_maxtime"];?></td></tr>                                 
<tr><td>Wind speed year maximum</td><td>$wind["speed_year_max"]</td><td><?php echo $wind["speed_year_max"];?></td></tr>                                 
<tr><td>Wind speed year maximum time</td><td>$wind["speed_year_maxtime"]</td><td><?php echo $wind["speed_year_maxtime"];?></td></tr>                                 
<tr><td>Wind gust year maximum</td><td>$wind["gust_year_max"]</td><td><?php echo $wind["gust_year_max"];?></td></tr>                                 
<tr><td>Wind gust year maximum time</td><td>$wind["gust_year_maxtime"]</td><td><?php echo $wind["gust_year_maxtime"];?></td></tr>                                 
<tr><td>Wind speed alltime maximum</td><td>$wind["speed_alltime_max"]</td><td><?php echo $wind["speed_alltime_max"];?></td></tr>                                 
<tr><td>Wind speed alltime maximum time</td><td>$wind["speed_alltime_maxtime"]</td><td><?php echo $wind["speed_alltime_maxtime"];?></td></tr>                                 
<tr><td>Wind gust alltime maximum</td><td>$wind["gust_alltime_max"]</td><td><?php echo $wind["gust_alltime_max"];?></td></tr>                                 
<tr><td>Wind gust alltime maximum time</td><td>$wind["gust_alltime_maxtime"]</td><td><?php echo $wind["gust_alltime_maxtime"];?></td></tr>                                 

</table>
</html>

<?php

$wind["direction_trend"] = "-06";
$wind["direction_trend_ordinal"] = "N";
?>