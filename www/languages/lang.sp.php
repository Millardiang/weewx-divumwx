<?php
/* 
-----------------
Language Translation File for HOMEWEATHERSTATION Template
Language: Spanish
Translated by : Santgenismeteo TxWeather.org/ & EA5ZF MeteoLaVall
Developed By: Lightmaster//Erik M Madsen
October/November 2016
Revised: 2022
-----------------
*/
# -----------------------------------------------------
# Day / Months do not edit
# -----------------------------------------------------
$day        = date("l");
$day2       = date("l", time() + 86400);
$daynum     = date("j");
$monthtrans = date("F");
$year       = date("Y");
# -----------------------------------------------------
# -----------------------------------------------------
setlocale(LC_TIME, "es_ES.UTF-8");
$lang                           = array();
// Menu
$lang['Settings']               = 'Ajustes';
$lang['Layout']                 = 'Cambiar de tema';
$lang['Lighttheme']             = 'Tema Claro';
$lang['Darktheme']              = 'Tema oscuro';
$lang['Nonmetric']              = 'US (F) ';
$lang['Metric']                 = 'Métrico (C)';
$lang['UKmetric']               = 'UK (MPH - Metrico) ';
$lang['Scandinavia']            = 'Scandinavia(M/S)';
$lang['Worldwideearthquakes']   = 'Terremotos en el mundo';
$lang['Toggle']                 = 'Pantalla entera ';
$lang['Contactinfo']            = 'Estación & Información de contacto';
$lang['Templateinfo']           = 'Colaboradores';
$lang['language']               = 'Seleccione el idioma';
$lang['Weatherstationinfo']     = 'Información de estación';
$lang['Webdesigninfo']          = 'Información de plantilla';
$lang['Contact']                = 'Contacto';
//days
$lang['Monday']                 = 'Lunes';
$lang['Tuesday']                = 'Martes';
$lang['Wednesday']              = 'Miercoles';
$lang['Thursday']               = 'Jueves';
$lang['Friday']                 = 'Viernes ';
$lang['Saturday']               = 'Sábado';
$lang['Sunday']                 = 'Domingo';
//months
$lang['January']                = 'Enero';
$lang['Febuary']                = 'Febrero';
$lang['March']                  = 'Marzo';
$lang['April']                  = 'Abril';
$lang['May']                    = 'Mayo';
$lang['June']                   = 'Junio';
$lang['July']                   = 'Julio';
$lang['August']                 = 'Agosto';
$lang['September']              = 'Septiembre';
$lang['October']                = 'Octubre';
$lang['November']               = 'Noviembre';
$lang['December']               = 'Diciembre';
//temperature
$lang['Temperature']            = 'Temperatura';
$lang['Feelslike']              = 'Sensación';
$lang['Humidity']               = 'Humedad';
$lang['Dewpoint']               = 'Punto de rocío';
$lang['Trend']                  = 'Tendencia ';
$lang['Heatindex']              = 'Índice de calor';
$lang['Windchill']              = 'Sensación ';
$lang['Tempfactors']            = 'Factor de temp';
$lang['Nocautions']             = 'Sin precauciones';
$lang['Wetbulb']                = 'T. condensación';
$lang['dry']                    = '& Seco';
$lang['verydry']                = '& Muy seco';
//new feature temperature feels
$lang['FreezingCold']           = 'Helada';
$lang['FeelingVeryCold']        = 'Mucho frío';
$lang['FeelingCold']            = 'Frio';
$lang['FeelingCool']            = 'Sensación de frío';
$lang['FeelingComfortable']     = 'Agradable ';
$lang['FeelingWarm']            = 'Calor';
$lang['FeelingHot']             = 'Sensación de Calor';
$lang['FeelingUncomfortable']   = 'Sensación incómoda';
$lang['FeelingVeryHot']         = 'Calor elevado';
$lang['FeelingExtremelyHot']    = 'Calor extremo';
//wind
$lang['Windspeed']              = 'Velocidad';
$lang['Gust']                   = 'Ráfaga';
$lang['Direction']              = 'Dirección';
$lang['Gusting']                = 'Racheado a';
$lang['Blowing']                = 'Soplando a';
$lang['Wind']                   = 'Viento';
$lang['Wind Run']               = 'Dist. viento';
// Wind phrases for Beaufort scale for windspeed area
$lang['Calm']                   = 'Calma';
$lang['Lightair']               = 'Viento ligero';
$lang['Lightbreeze']            = 'Brisa ligera ';
$lang['Gentelbreeze']           = 'Brisa suave';
$lang['Moderatebreeze']         = 'Brisa moderada';
$lang['Freshbreeze']            = 'Brisa fresca';
$lang['Strongbreeze']           = 'Brisa fuerte';
$lang['Neargale']               = 'Casi vendaval';
$lang['Galeforce']              = 'Vendaval';
$lang['Stronggale']             = 'Fuerte vendaval';
$lang['Storm']                  = 'Tormenta';
$lang['Violentstorm']           = 'Tormenta violenta';
$lang['Hurricane']              = 'Huracán';
// Wind phrases from Beaufort scale for current conditions area
$lang['CalmConditions']         = 'Calma';
$lang['LightBreezeattimes']     = 'Viento ligero ';
$lang['MildBreezeattimes']      = 'Brisa suave ';
$lang['ModerateBreezeattimes']  = 'Brisa moderada';
$lang['FreshBreezeattimes']     = 'Brisa fresca';
$lang['StrongBreezeattimes']    = 'Brisa fuerte';
$lang['NearGaleattimes']        = 'Frescachón';
$lang['GaleForceattimes']       = 'Temporal';
$lang['StrongGaleattimes']      = 'Temporal fuerte';
$lang['StormConditions']        = 'Temporal duro';
$lang['ViolentStormConditions'] = 'Temporal muy duro';
$lang['HurricaneConditions']    = 'Temporal huracanado';
$lang['Avg']                    = '<span2> Media: </span2>';
//wind direction compass
$lang['Northdir']               = 'Del <span>Norte<br></span>';
$lang['NNEdir']                 = 'Norte Norte <br><span>Este</span>';
$lang['NEdir']                  = 'Norte <span> Este<br></span>';
$lang['ENEdir']                 = 'Este Norte<br><span>Este</span>';
$lang['Eastdir']                = "Del <span> Este<br></span>";
$lang['ESEdir']                 = 'Este Sur<br><span>Este</span>';
$lang['SEdir']                  = 'Sur <span> Este</span>';
$lang['SSEdir']                 = 'Sur Sur<br><span>Este</span>';
$lang['Southdir']               = 'Del <span> Sur</span>';
$lang['SSWdir']                 = 'Sur Sur<br><span>Oeste</span>';
$lang['SWdir']                  = 'Sur <span> Oeste</span>';
$lang['WSWdir']                 = 'Del <span> Oeste</span>';
$lang['Westdir']                = 'Oeste Norte<br><span>Oeste</span>';
$lang['WNWdir']                 = 'Oeste Norte<br><span>Oeste</span>';
$lang['NWdir']                  = 'Norte <span> Oeste</span>';
$lang['NWNdir']                 = 'Norte Norte<br><span>Oeste</span>';
//wind direction avg
$lang['North']                  = 'Norte';
$lang['NNE']                    = 'NNE';
$lang['NE']                     = 'NE';
$lang['ENE']                    = 'ENE';
$lang['East']                   = 'Este ';
$lang['ESE']                    = 'ESE';
$lang['SE']                     = 'SE';
$lang['SSE']                    = 'SSE';
$lang['South']                  = 'Sur';
$lang['SSW']                    = 'SSO';
$lang['SW']                     = 'SO';
$lang['WSW']                    = 'OSO';
$lang['West']                   = 'Oeste';
$lang['WNW']                    = 'ONO';
$lang['NW']                     = 'NO';
$lang['NWN']                    = 'NON';
//rain
$lang['raintoday']              = 'Lluvia hoy';
$lang['Rate']                   = 'Velocidad';
$lang['Rainfall']               = 'Lluvia';
$lang['Precip']                 = 'precip'; // must be short name do not use full precipatation !!!! ///
$lang['Rain']                   = 'Luvia';
$lang['Heavyrain']              = 'Lluvia intensa';
$lang['Flooding']               = 'Posible inundación';
$lang['Rainbow']                = 'Arcoiris';
$lang['Windy']                  = 'Ventoso';
//sun -moon-daylight-darkness
$lang['Sun']                    = 'Sol';
$lang['Moon']                   = 'Luna';
$lang['Sunrise']                = 'Amanecer';
$lang['Sunset']                 = 'Puesta de sol';
$lang['Moonrise']               = 'Salida de luna ';
$lang['Moonset']                = 'Puesta de luna';
$lang['Night']                  = 'Noche ';
$lang['Day']                    = 'Dia';
$lang['Nextnewmoon']            = 'Luna nueva';
$lang['Nextfullmoon']           = 'Luna llena';
$lang['Luminance']              = 'Luminosidad';
$lang['Moonphase']              = 'Fase lunar';
$lang['Estimated']              = 'Estimada';
$lang['Daylight']               = 'Luz de dia';
$lang['Darkness']               = 'Oscuridad';
$lang['Daysold']                = 'días de antigüedad';
$lang['Belowhorizon']           = 'debajo del<br>horizonte';
$lang['Mintill']                = '<br>Mins hasta';
$lang['Till']                   = 'Para ';
$lang['Minago']                 = 'Hace mins';
$lang['Hrs']                    = ' hrs';
$lang['Min']                    = ' min';
$lang['TotalDarkness']          = 'Oscuridad total';
$lang['TotalDaylight']          = 'Luz del día total';
$lang['Below']                  = 'Está por debajo del horizonte';
$lang['Newmoon']                = 'Luna nueva';
$lang['Waxingcrescent']         = 'Luna creciente';
$lang['Firstquarter']           = 'Cuarto creciente';
$lang['Waxinggibbous']          = 'Luna menguante';
$lang['Fullmoon']               = 'Luna llena';
$lang['Waninggibbous']          = 'Gibosa menguante';
$lang['Lastquarter']            = 'Cuarto menguante';
$lang['Waningcrescent']         = 'Creciente menguante';
//trends
$lang['Falling']                = 'Bajando';
$lang['Rising']                 = 'Subiendo';
$lang['Steady']                 = 'Estable';
$lang['Rapidly']                = 'Rápidamente';
$lang['Temp']                   = 'Temp';
//Solar-UV
//uv
$lang['Nocaution']              = 'Sin <color>precaucion</color> requerida';
$lang['Wearsunglasses']         = 'Use <color>gafas de sol</color> en días soleados';
$lang['Stayinshade']            = 'Permanecer en la sombra cerca del medio día cuando el <color>sol</color> es fuerte';
$lang['Reducetime']             = 'Reducir el tiempo en el <color>s0l</color> entre 10am-4pm ';
$lang['Minimize']               = 'Reducir al mínimo <color>sol</color> exposición entre 10am-4pm ';
$lang['Trytoavoid']             = 'Tratar de evitar el <color>sol</color> exposición entre 10am-4pm ';
//solar
$lang['Poor']                   = 'Radiación<color> <br>Pobre</color>';
$lang['Low']                    = 'Radiación<br><color>Baja</color>';
$lang['Moderate']               = 'Radiación<br><color>Moderada</color>';
$lang['Good']                   = 'Radiación<br><color>Fuerte</color>';
$lang['Solarradiation']         = 'Radiación Solar';
//current sky
$lang['Currentsky']             = 'Con. actuales';
$lang['Currently']              = 'Actualmente';
$lang['Cloudcover']             = 'Cubierto de nubes';
//Notifications
$lang['Nocurrentalert']         = 'No hay alertas meteorológicas';
$lang['Windalert']              = 'Ráfagas de viento en';
$lang['Tempalert']              = 'Alta temperatura';
$lang['Heatindexalert']         = 'Precaución de calor ';
$lang['Windchillalert']         = 'Precaución sensación térmica';
$lang['Dewpointalert']          = 'Humedad incómoda';
$lang['Dewpointcolderalert']    = 'Humedad incómoda';
$lang['Feelslikecolderalert']   = 'Sensación de Frío';
$lang['Feelslikewarmeralert']   = 'Sensación de Calor';
$lang['Rainratealert']          = 'por/hr<span> Lluvia ';
//Earthquake Notifications
$lang['Regional']               = 'Terremoto regional';
$lang['Significant']            = 'Terremoto importante';
$lang['Nosignificant']          = 'Terremoto no significativo';
//Main page
$lang['Barometer']              = 'Barómetro';
$lang['UVSOLAR']                = 'UV | Datos Sol';
$lang['Earthquake']             = ' Terremoto';
$lang['Daynight']               = 'Día & Noche Info';
$lang['SunPosition']            = 'Position sol';
$lang['Location']               = 'Ubicación';
$lang['Hardware']               = 'Hardware';
$lang['Rainfalltoday']          = 'Lluvia hoy';
$lang['Windspeed']              = ' Viento';
$lang['Winddirection']          = 'Dirección del Viento';
$lang['Measured']               = 'Medido hoy';
$lang['Forecast']               = 'Previsión';
$lang['Forecastahead']          = 'Pronóstico próximo';
$lang['Forecastsummary']        = 'Resumen previsión';
$lang['WindGust']               = 'Vel. Viento | Rachas';
$lang['Hourlyforecast']         = 'Previsión horaria';
$lang['Significantearthquake']  = 'Terremoto importante';
$lang['Regionalearthquake']     = 'Terremoto regional';
$lang['Average']                = 'Avisos';
$lang['Notifications']          = 'Notificación de Alerta';
$lang['Indoor']                 = 'Interior';
$lang['Today']                  = 'Hoy';
$lang['Tonight']                = 'Noche';
$lang['Tomorrow']               = 'Mañana';
$lang['Tomorrownight']          = 'Mañana noche';
$lang['Updated']                = 'Actualizado';
$lang['Meteor']                 = 'Meteorito Información';
$lang['Firerisk']               = 'Riesgo de incendio';
$lang['Localtime']              = 'Tiempo local';
$lang['Nometeor']               = 'Sin meteoritos';
$lang['LiveWebCam']             = 'Webcam en vivo';
$lang['Online']                 = 'En linea';
$lang['Offline']                = 'fuera de linea';
$lang['Weatherstation']         = 'Estacion';
$lang['Cloudbase']              = 'Base Nubes';
$lang['uvalert']                = 'Precaucion UVINDEX';
$lang['Max']                    = 'Max';
$lang['Min']                    = 'Min';
//earthquake TOP MODULE 10 July 2017
$lang['MicroE']                 = 'Micro Terremoto';
$lang['MinorE']                 = 'Pequeño Terremoto';
$lang['LightE']                 = 'Ligero Terremoto';
$lang['ModerateE']              = 'Moderado Terremoto';
$lang['StrongE']                = 'Fuerte terremoto';
$lang['MajorE']                 = 'Gran Terremoto';
$lang['GreatE']                 = 'Enorme Terremoto';
$lang['RegionalE']              = 'Regional';
$lang['Conditions']             = 'Condiciones';
$lang['Cloudbase Height']       = 'Base de nubes';
$lang['Station']                = 'Estación';
$lang['Detailed Forecast']      = 'Previsión detallada';
$lang['Summary Outlook']        = 'Resumen';
//Air Quality
$lang['Hazordous']              = 'Condiciones peligrosas';
$lang['VeryUnhealthy']          = 'Muy insalubre ';
$lang['Unhealthy']              = 'Aire Insalubre';
$lang['UnhealthyFS']            = 'Insalubre';
$lang['Moderate']               = 'Calidad moderada del Aire ';
$lang['Good']                   = 'Buena calidad del Aire ';
#notification additions
$lang['notifyTitle']             = 'Notificaciones';
$lang['notifyAlert']             = "Alerta";
$lang['notifyLowBatteryAlert']   = "Alerta por baja batería";
$lang['notifyConsoleLowBattery'] = "Batería consola baja";
$lang['notifyStationLowBattery'] = "Batería estación baja";
$lang['notifyUVIndex']           = "Precaución índice UV";
$lang['notifyUVExposure']        = "Reducir exposición al Sol";
$lang['notifyHeatExaustion']     = "Agotamiento por calor";
$lang['notifyExtremeWind']       = "Aviso por vientos extremos";
$lang['notifyGustUpTo']          = "Ráfagas hasta";
$lang['notifySeekShelter']       = "Busque refugio <notifyred><b>immediatamente</b></notifyred>";
$lang['notifyHighWindWarning']   = "Aviso por viento fuerte";
$lang['notifySustainedAvg']      = "Media sostenida";
$lang['notifyWindAdvisory']      = "Aviso por viento";
$lang['notifyFreezing']          = "Aviso por temperatura bajo cero";
//Top Row Modules
$lang['timeTop']                 = 'Hora <ored>Estación';
$lang['airQualityTop']           = 'Calidad del Aire <ored>Actual PM<sub>2.5</sub> Concentración';
$lang['lightningTop']            = 'Impactos <ored>Rayos';
$lang['advisoriesTop']           = 'Meteo <ored>Pronóstico y avisos';
//Main Modules
$lang['temperatureModule']       = 'Temperatura';
$lang['forecastModule']          = 'Pronóstico';
$lang['currentModule']           = 'Condiciones actuales';
$lang['windModule']              = 'Velocidad viento | Dirección';
$lang['barometerModule']         = 'Barómetro';
$lang['solarDialModule']         = 'Dial Solar';
$lang['rainfallModule']          = 'Lluvia';
$lang['solarUvLuxModule']        = 'Solar | UVI | Lux';
$lang['lightningModule']         = 'Rayos';
$lang['airqualityModule']        = 'Calidad del Aire | AQI';
$lang['webcamModule']            = 'MeteoCámara';
$lang['earthDaylightModule']     = 'Earth Daylight';
$lang['moonPhaseModule']         = 'Fase lunar actual';
$lang['earthquakeModule']        = 'Terremotos';
$lang['indoorTempModule']        = 'Temperatura Interior';
?>
