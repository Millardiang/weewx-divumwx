<?php
/* 
-----------------
Language Translation File for HOMEWEATHERSTATION Template
Language: Catalan
Translated by: Santgenismeteo & EA5ZF MeteoLaVall
Developed By: Lightmaster//Erik M Madsen
October/November 2016
Revised: 2022
-----------------
*/
# -----------------------------------------------------
# Day / Months do not edit
# -----------------------------------------------------
$day        = date('l');
$day2       = date('l', time() + 86400);
$daynum     = date('j');
$monthtrans = date('F');
$year       = date('Y');
# -----------------------------------------------------
# -----------------------------------------------------
setlocale(LC_TIME, 'ca_ES');
$lang                           = array();
// Menu
$lang['Settings']               = 'Ajustos';
$lang['Layout']                 = 'Canviar tema';
$lang['Lighttheme']             = 'Tema clar';
$lang['Darktheme']              = 'Tema fosc';
$lang['Nonmetric']              = 'US (F) ';
$lang['Metric']                 = 'Metric (C)';
$lang['UKmetric']               = 'UK (MPH - Metric) ';
$lang['Scandinavia']            = 'Scandinavia(M/S)';
$lang['Worldwideearthquakes']   = 'Terratrèmols al mon';
$lang['Toggle']                 = 'Canviar a pantalla completa';
$lang['Contactinfo']            = 'Estació & Info Contacte';
$lang['Templateinfo']           = 'Col·laboradors';
$lang['language']               = 'Selecció idioma';
$lang['Weatherstationinfo']     = 'Informació estació';
$lang['Webdesigninfo']          = 'plantilla Informació';
$lang['Contact']                = 'Contacte';
//days
$lang['Monday']                 = 'Dilluns';
$lang['Tuesday']                = 'Dimarts';
$lang['Wednesday']              = 'Dimecres';
$lang['Thursday']               = 'Dijous';
$lang['Friday']                 = 'Divendres';
$lang['Saturday']               = 'Disabte';
$lang['Sunday']                 = 'Diumenge';
//months
$lang['January']                = 'Gener';
$lang['Febuary']                = 'Febrer';
$lang['March']                  = 'Març';
$lang['April']                  = 'Abril';
$lang['May']                    = 'Maig';
$lang['June']                   = 'Juny';
$lang['July']                   = 'Juliol';
$lang['August']                 = 'Agost';
$lang['September']              = 'Setembre';
$lang['October']                = 'Octobre';
$lang['November']               = 'Novembre';
$lang['December']               = 'Decembre';
//temperature
$lang['Temperature']            = 'Temperatura';
$lang['Feelslike']              = 'Sensació';
$lang['Humidity']               = 'humitat';
$lang['Dewpoint']               = 'Punt de rosada';
$lang['Trend']                  = 'Tend';
$lang['Heatindex']              = 'índex de calor';
$lang['Windchill']              = 'Sensació';
$lang['Tempfactors']            = 'Factor temp.';
$lang['Nocautions']             = 'no hi ha precaucions';
$lang['Wetbulb']                = 'T. condensació';
$lang['dry']                    = '& Sec';
$lang['verydry']                = '& Molt sec';
//new feature temperature feels
$lang['FreezingCold']           = 'Fred gelat';
$lang['FeelingVeryCold']        = 'Sensació molt freda';
$lang['FeelingCold']            = 'Sensació de gelor';
$lang['FeelingCool']            = 'Sensació de fred';
$lang['FeelingComfortable']     = 'Sensació còmoda ';
$lang['FeelingWarm']            = 'Sensació de calor';
$lang['FeelingHot']             = 'Sensació de cremor';
$lang['FeelingUncomfortable']   = 'Sensació incòmoda';
$lang['FeelingVeryHot']         = 'Sensació molta calor';
$lang['FeelingExtremelyHot']    = 'Molta calor';
//wind
$lang['Windspeed']              = 'Velocitat';
$lang['Gust']                   = 'Ràfega';
$lang['Direction']              = 'Direcció';
$lang['Gusting']                = 'Ratxes a';
$lang['Blowing']                = 'Bufant a';
$lang['Wind']                   = 'Vel.';
$lang['Wind Run']               = 'Vel. Vent';
// Wind phrases for Beaufort scale for windspeed area
$lang['Calm']                   = 'Calma';
$lang['Lightair']               = 'Vent lleuger';
$lang['Lightbreeze']            = 'Brisa lleugera';
$lang['Gentelbreeze']           = 'Brisa suau';
$lang['Moderatebreeze']         = 'Brisa moderada';
$lang['Freshbreeze']            = 'Brisa fresca';
$lang['Strongbreeze']           = 'Brisa forta';
$lang['Neargale']               = 'Vent fort';
$lang['Galeforce']              = 'Vendaval';
$lang['Stronggale']             = 'Vendaval fort';
$lang['Storm']                  = 'Tempesta';
$lang['Violentstorm']           = 'Tempesta violenta';
$lang['Hurricane']              = 'Huracà';
// Wind phrases from Beaufort scale for current conditions area
$lang['CalmConditions']         = 'Calma';
$lang['LightBreezeattimes']     = 'Ventolina ';
$lang['MildBreezeattimes']      = 'Vent fluixet ';
$lang['ModerateBreezeattimes']  = 'Vent moderat';
$lang['FreshBreezeattimes']     = 'Vent fresquet';
$lang['StrongBreezeattimes']    = 'Vent fort';
$lang['NearGaleattimes']        = 'Temporal';
$lang['GaleForceattimes']       = 'Temporal fort';
$lang['StrongGaleattimes']      = 'Temporal molt fort';
$lang['StormConditions']        = 'Tempesta';
$lang['ViolentStormConditions'] = 'Tempesta violenta';
$lang['HurricaneConditions']    = 'Huracà';
$lang['Avg']                    = '<span2> Mitjana: </span2>';
//wind direction compass
$lang['Northdir']               = 'Tramuntana';
$lang['NNEdir']                 = 'Tramuntana-Gregal';
$lang['NEdir']                  = 'Gregal';
$lang['ENEdir']                 = 'Llevant-Gregal';
$lang['Eastdir']                = 'Llevant';
$lang['ESEdir']                 = 'Llevant-Xaloc';
$lang['SEdir']                  = 'Xaloc';
$lang['SSEdir']                 = 'Migjorn-Xaloc';
$lang['Southdir']               = 'Migjorn';
$lang['SSWdir']                 = 'Migjorn-Garbí/Llebeig';
$lang['SWdir']                  = 'Garbí/Llebeig';
$lang['WSWdir']                 = 'Ponent-Garbí/Llebeig';
$lang['Westdir']                = 'Ponent';
$lang['WNWdir']                 = 'Ponent-Mestral';
$lang['NWdir']                  = 'Mestral';
$lang['NWNdir']                 = 'Tramuntana-Mestral';
//wind direction avg
$lang['North']                  = 'Nord';
$lang['NNE']                    = 'NNE';
$lang['NE']                     = 'NE';
$lang['ENE']                    = 'ENE';
$lang['East']                   = 'Est';
$lang['ESE']                    = 'ESE';
$lang['SE']                     = 'SE';
$lang['SSE']                    = 'SSE';
$lang['South']                  = 'Sud';
$lang['SSW']                    = 'SSO';
$lang['SW']                     = 'SO';
$lang['WSW']                    = 'OSO';
$lang['West']                   = 'Oest';
$lang['WNW']                    = 'ONO';
$lang['NW']                     = 'NO';
$lang['NWN']                    = 'NON';
//rain
$lang['raintoday']              = 'Pluja avui';
$lang['Rate']                   = 'Intensitat';
$lang['Rainfall']               = 'Pluja';
$lang['Precip']                 = 'Precip'; // must be short name do not use full precipitation !!!! ///
$lang['Rain']                   = 'Pluja';
$lang['Heavyrain']              = 'Pluja forta';
$lang['Flooding']               = 'Possibles inundacions';
$lang['Rainbow']                = 'Rainbow';
$lang['Windy']                  = 'Ventós';
//sun -moon-daylight-darkness
$lang['Sun']                    = 'Sol';
$lang['Moon']                   = 'Lluna';
$lang['Sunrise']                = 'Sortida del sol';
$lang['Sunset']                 = 'Posta del sol';
$lang['Moonrise']               = 'Sortida lluna';
$lang['Moonset']                = 'Posta de lluna';
$lang['Night']                  = 'Nit ';
$lang['Day']                    = 'Dia';
$lang['Nextnewmoon']            = 'Lluna nova';
$lang['Nextfullmoon']           = 'Lluna plena';
$lang['Luminance']              = 'Lluminositat';
$lang['Moonphase']              = 'Fase lunar';
$lang['Estimated']              = 'Estimat';
$lang['Daylight']               = 'Llum de dia';
$lang['Darkness']               = 'Foscor';
$lang['Daysold']                = 'Fa Dies';
$lang['Belowhorizon']           = 'Sota de<br>horitzó';
$lang['Mintill']                = ' mins fins';
$lang['Till']                   = 'Per ';
$lang['Minago']                 = ' fa minuts';
$lang['Hrs']                    = ' hrs';
$lang['Min']                    = ' min';
$lang['TotalDarkness']          = 'Total Nit';
$lang['TotalDaylight']          = 'Total Dia';
$lang['Below']                  = 'Sota horitzó';
$lang['Newmoon']                = 'Lluna nova';
$lang['Waxingcrescent']         = 'Creixent';
$lang['Firstquarter']           = 'Quart creixent';
$lang['Waxinggibbous']          = 'Creixent';
$lang['Fullmoon']               = 'Lluna plena';
$lang['Waninggibbous']          = 'Minvant';
$lang['Lastquarter']            = 'Quart minvant';
$lang['Waningcrescent']         = 'Creixent';
//trends
$lang['Falling']                = 'Baixant';
$lang['Rising']                 = 'Pujant';
$lang['Steady']                 = 'Estable';
$lang['Rapidly']                = 'Ràpidament';
$lang['Temp']                   = 'Temp';
//Solar-UV
//uv
$lang['Nocaution']              = 'Sense <color>precaució</color> requerida';
$lang['Wearsunglasses']         = 'Utilitza <color>ulleres de sol</color> en dies assolellats';
$lang['Stayinshade']            = 'Romandre a la ombra prop del migdia quan el <color>sol</color> és fort';
$lang['Reducetime']             = 'Redueix el temps de <color>sol</color> entre 10am-4pm ';
$lang['Minimize']               = 'Redueix exposició al <color>sol</color> entre 10am-4pm ';
$lang['Trytoavoid']             = 'Tractar de evitar el <color>sol</color> entre 10am-4pm ';
//solar
$lang['Poor']                   = 'Radiació solar<color> <br>Pobre</color>';
$lang['Low']                    = 'Radiació solar<br><color>Baixa</color>';
$lang['Moderate']               = 'Radiació solar<br><color>Moderada</color>';
$lang['Good']                   = 'Radiació solar<br><color>Bona</color>';
$lang['Solarradiation']         = 'Radiacio Solar';
//current sky
$lang['Currentsky']             = 'Condicions actuals';
$lang['Currently']              = 'Actualment';
$lang['Cloudcover']             = 'Coberta de nuvols';
//Notifications
$lang['Nocurrentalert']         = 'No hi ha alertes ';
$lang['Windalert']              = 'Ràfegues de vent a';
$lang['Tempalert']              = 'Temperatura alta';
$lang['Heatindexalert']         = 'Precaució index de calor ';
$lang['Windchillalert']         = 'Precaució sensació tèrmica';
$lang['Dewpointalert']          = 'Humitat incòmoda';
$lang['Dewpointcolderalert']    = 'Sensació molt freda';
$lang['Feelslikecolderalert']   = 'Sensació de Fred';
$lang['Feelslikewarmeralert']   = 'Sensació de Calor';
$lang['Rainratealert']          = 'per/hr<span> Pluja ';
//Earthquake Notifications
$lang['Regional']               = 'Terratrèmol Regionals';
$lang['Significant']            = 'Terratrèmols significants';
$lang['Nosignificant']          = 'Sense terratrèmols significatius';
//Main page
$lang['Barometer']              = 'Baròmetre';
$lang['UVSOLAR']                = 'UV | Dades Solars';
$lang['Earthquake']             = 'Terratrèmols';
$lang['Daynight']               = 'Dia & Nit Info';
$lang['SunPosition']            = 'Sun Position';
$lang['Location']               = 'Ubicació ';
$lang['Hardware']               = 'Hardware';
$lang['Rainfalltoday']          = 'Pluja avui';
$lang['Windspeed']              = 'Vent';
$lang['Winddirection']          = 'Direccio Vent';
$lang['Measured']               = 'Mesurat avui';
$lang['Forecast']               = 'Pronòstic';
$lang['Forecastahead']          = 'Pronòstic pròxim';
$lang['Forecastsummary']        = 'Resum Pronòstic';
$lang['WindGust']               = 'Velocitat Vent | Ràfegues';
$lang['Hourlyforecast']         = 'Pronòstic horari ';
$lang['Significantearthquake']  = 'Terratrèmols significants';
$lang['Regionalearthquake']     = 'Terratrèmols regionals';
$lang['Average']                = 'Mitjana';
$lang['Notifications']          = 'Notificació Alerta';
$lang['Indoor']                 = 'Interior';
$lang['Today']                  = 'Avui';
$lang['Tonight']                = 'Esta Nit';
$lang['Tomorrow']               = 'Dema';
$lang['Tomorrownight']          = 'Demà a la nit';
$lang['Updated']                = 'Actualitzat';
$lang['Meteor']                 = 'Informació de Meteors';
$lang['Firerisk']               = 'Risc d incendis';
$lang['Localtime']              = 'Temps local';
$lang['Nometeor']               = 'Sense meteors';
$lang['LiveWebCam']             = 'WebCam en directe';
$lang['Online']                 = 'En línia';
$lang['Offline']                = 'Desconnectat';
$lang['Weatherstation']         = 'Estació';
$lang['Cloudbase']              = 'Base Nubols';
$lang['uvalert']                = 'Precaucion UVINDEX';
$lang['Max']                    = 'Màx';
$lang['Min']                    = 'Min';
//earthquake TOP MODULE 10 July 2017
$lang['MicroE']                 = 'Micro Terratrèmol';
$lang['MinorE']                 = 'Terratrèmol petit';
$lang['LightE']                 = 'Terratrèmol lleuger';
$lang['ModerateE']              = 'Terratrèmol moderat';
$lang['StrongE']                = 'Terratrèmol fort';
$lang['MajorE']                 = 'Major Terratrèmol';
$lang['GreatE']                 = 'Gran Terratrèmol';
$lang['RegionalE']              = 'Regional';
$lang['Conditions']             = 'Condicions';
$lang['Cloudbase Height']       = 'Altura base de núvols ';
$lang['Station']                = 'Estació';
$lang['Detailed Forecast']      = 'Pronòstic detallat';
$lang['Summary Outlook']        = 'Resum';
//Air Quality
$lang['Hazordous']              = 'Condicions perilloses';
$lang['VeryUnhealthy']          = 'Molt insalobre ';
$lang['Unhealthy']              = 'Aire Insalobre';
$lang['UnhealthyFS']            = 'Insalobre';
$lang['Moderate']               = 'Moderada qualitat del Aire ';
$lang['Good']                   = 'Bona qualitat del Aire ';
#notification additions
$lang['notifyTitle']             = 'Notifications';
$lang['notifyAlert']             = 'Alerta';
$lang['notifyLowBatteryAlert']   = 'Alerta Batería baixa';
$lang['notifyConsoleLowBattery'] = 'Pila Consola baixa';
$lang['notifyStationLowBattery'] = 'Pila Estació baixa';
$lang['notifyUVIndex']           = 'UV-Index Precaució';
$lang['notifyUVExposure']        = 'Reduir Exposició Solar';
$lang['notifyHeatExaustion']     = 'Agotament per Calor';
$lang['notifyExtremeWind']       = 'Alerta Vent Extrem';
$lang['notifyGustUpTo']          = 'Ràfegues fins a';
$lang['notifySeekShelter']       = 'Buscar refugi <notifyred><b>inmediatament</b></notifyred>';
$lang['notifyHighWindWarning']   = 'Alerta Vent Fort';
$lang['notifySustainedAvg']      = 'Mitjana sustinguda';
$lang['notifyWindAdvisory']      = 'Avís Vent';
$lang['notifyFreezing']          = 'Avís temp baix cèro';
//Top Row Modules
$lang['timeTop']                 = 'Hora <ored>Estació';
$lang['airQualityTop']           = 'Qualitat aire <ored>Actual PM<sub>2.5</sub> Concentració';
$lang['lightningTop']            = 'Impactes <ored>Llamps';
$lang['advisoriesTop']           = 'Meteo <ored>Pronòstic i avisos';
//Main Modules
$lang['temperatureModule']       = 'Temperatura';
$lang['forecastModule']          = 'Pronòstic';
$lang['currentModule']           = 'Condicions actuals';
$lang['windModule']              = 'Velocitat vent | Direcció';
$lang['barometerModule']         = 'Baròmetre';
$lang['solarDialModule']         = 'Dial Solar';
$lang['rainfallModule']          = 'Pluja';
$lang['solarUvLuxModule']        = 'Solar | UVI | Lux';
$lang['lightningModule']         = 'Llamps';
$lang['airqualityModule']        = 'Qualitat aire | AQI';
$lang['webcamModule']            = 'MeteoCamera';
$lang['earthDaylightModule']     = 'Earth Daylight';
$lang['moonPhaseModule']         = 'Fase lunar actual';
$lang['earthquakeModule']        = 'Terretrèmols';
$lang['indoorTempModule']        = 'Temperatura Interior';
$lang['Anemometer']              = 'Anemometer';
?>
