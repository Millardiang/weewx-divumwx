<?php
##############################################################################################
#        ________   __  ___      ___  ____  ____  ___      ___    __   __  ___  ___  ___     #
#       |"      "\ |" \|"  \    /"  |("  _||_ " ||"  \    /"  |  |"  |/  \|  "||"  \/"  |    #
#       (.  ___  :)||  |\   \  //  / |   (  ) : | \   \  //   |  |'  /    \:  | \   \  /     #
#       |: \   ) |||:  | \\  \/. ./  (:  |  | . ) /\\  \/.    |  |: /'        |  \\  \/      #
#       (| (___\ |||.  |  \.    //    \\ \__/ // |: \.        |   \//  /\'    |  /\.  \      #
#       |:       :)/\  |\  \\   /     /\\ __ //\ |.  \    /:  |   /   /  \\   | /  \   \     #
#       (________/(__\_|_)  \__/     (__________)|___|\__/|___|  |___/    \___||___/\___|    #
#                                                                                            #
#     Copyright (C) 2023 Ian Millard, Steven Sheeley, Sean Balfour. All rights reserved      #
#      Distributed under terms of the GPLv3.  See the file LICENSE.txt for your rights.      #
#    Issues for weewx-divumwx skin template are only addressed via the issues register at    #
#                    https://github.com/Millardiang/weewx-divumwx/issues                     #
##############################################################################################

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}
session_start();
if (!isset($_SESSION['username'])) {
	header("Location: index.php");
	exit;
}
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 3600)) {
	session_unset();
	session_destroy();
	header("Location: index.php");
	exit;
}
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$_SESSION['login_time'] = time();
require_once './admCommon.php';
require_once './admVersion.php';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
	<meta charset="utf-8">
	<title>DvM | Module File Information</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">
	<link href="assets/css/vendor.min.css" rel="stylesheet">
	<link href="assets/css/app.min.css" rel="stylesheet">
</head>
<body class="theme-blue">
	<div id="app" class="app">
		<div id="header" class="app-header">
			<div class="brand">
				<a href="#" class="brand-logo">
					<span class="brand-img">
						<span class="brand-img-text text-theme">DvM</span>
					</span>
					<span class="brand-text">Admin Dashboard</span>
				</a>
			</div>
			<div class="menu"></div>
		<?php displaySidebar('modinfo'); ?>
		<!-- BEGIN #content -->
		<div id="content" class="app-content">
			<div class="row">
				<div class="col-xl-11 col-lg-6">
					<table class="table table-bordered mb-4">
						<thead>
							<tr>
								<th>Module Name</th>
								<th>File Name</th>
								<th>Description</th>
								<th>Image</th>
							</tr>
						</thead>
						<tbody class="tbody">
							<tr>
								<td><small>Air Quality</small></td>
								<td><small>dvmAirqualityModule.php</small></td>
								<td><small>This module displays the current Air Quality readings for PM2.5 and PM10 in ug/m3 as well as links to the Air Quality Charts and Air Quality Info. The module also displays, in the upper left corner, the last time the data was refreshed.</small></td>
								<td class="align-middle text-center"><a href="#" data-bs-toggle="modal" data-bs-target="#modalMODImage" data-image-name="aqiMod.png"><img src="./assets/img/aqiMod.png" width="25" height="25"></a></small></td>
							</tr>
							<tr>
								<td><small>Anemometer</small></td>
								<td><small>dvmAnemometerModule.php</small></td>
								<td><small>This module displays several data points related to wind. Current wind speed and direction with the speed displayed on a dial, bearing and ordinal direction as well as several other datapoints and a link to the wind almanac and charts. The module also displays, in the upper left corner, the last time the data was refreshed.</small></td>
								<td class="align-middle text-center"><a href="#" data-bs-toggle="modal" data-bs-target="#modalMODImage" data-image-name="aqiMod.png"><img src="./assets/img/anemometerMod.png" width="25" height="25"></a></small></td>
							</tr>
							<tr>
								<td><small>Barometer</small></td>
								<td><small>dvmBarometerModule.php</small></td>
								<td><small> The module also displays, in the upper left corner, the last time the data was refreshed.</small></td>
								<td class="align-middle text-center"><a href="#" data-bs-toggle="modal" data-bs-target="#modalMODImage" data-image-name="aqiMod.png"><img src="./assets/img/barometerMod.png" width="25" height="25"></a></small></td>
							</tr>
							<tr>
								<td><small>Current Conditions</small></td>
								<td><small>dvmCurrentModule.php</small></td>
								<td><small> The module also displays, in the upper left corner, the last time the data was refreshed.</small></td>
								<td class="align-middle text-center"><a href="#" data-bs-toggle="modal" data-bs-target="#modalMODImage" data-image-name="aqiMod.png"><img src="./assets/img/currentMod.png" width="25" height="25"></a></small></td>
							</tr>
							<tr>
								<td><small>Earth Image</small></td>
								<td><small>dvmEarthDaylightModule.php</small></td>
								<td><small> The module also displays, in the upper left corner, the last time the data was refreshed.</small></td>
								<td class="align-middle text-center"><a href="#" data-bs-toggle="modal" data-bs-target="#modalMODImage" data-image-name="aqiMod.png"><img src="./assets/img/earthdayMod.png" width="25" height="25"></a></small></td>
							</tr>
							<tr>
								<td><small>Forecast</small></td>
								<td><small>dvmForecastModule.php</small></td>
								<td><small> The module also displays, in the upper left corner, the last time the data was refreshed.</small></td>
								<td class="align-middle text-center"><a href="#" data-bs-toggle="modal" data-bs-target="#modalMODImage" data-image-name="aqiMod.png"><img src="./assets/img/forecastMod.png" width="25" height="25"></a></small></td>
							</tr>
							<tr>
								<td><small>Indoor Conditions</small></td>
								<td><small>dvmIndoorTemperatureModule.php</small></td>
								<td><small> The module also displays, in the upper left corner, the last time the data was refreshed.</small></td>
								<td class="align-middle text-center"><a href="#" data-bs-toggle="modal" data-bs-target="#modalMODImage" data-image-name="aqiMod.png"><img src="./assets/img/indoorTempMod.png" width="25" height="25"></a></small></td>
							</tr>
							<tr>
								<td><small>Lightning Strikes</small></td>
								<td><small>dvmLightningModule.php</small></td>
								<td><small> The module also displays, in the upper left corner, the last time the data was refreshed.</small></td>
								<td class="align-middle text-center"><a href="#" data-bs-toggle="modal" data-bs-target="#modalMODImage" data-image-name="aqiMod.png"><img src="./assets/img/lightningMod.png" width="25" height="25"></a></small></td>
							</tr>
							<tr>
								<td><small>Moon Phase Image</small></td>
								<td><small>dvmMoonPhaseModule.php</small></td>
								<td><small> The module also displays, in the upper left corner, the last time the data was refreshed.</small></td>
								<td class="align-middle text-center"><a href="#" data-bs-toggle="modal" data-bs-target="#modalMODImage" data-image-name="aqiMod.png"><img src="./assets/img/moonphaseMod.png" width="25" height="25"></a></small></td>
							</tr>
							<tr>
								<td><small>Outdoor Temperature</small></td>
								<td><small>dvmTemperatureModule.php</small></td>
								<td><small> The module also displays, in the upper left corner, the last time the data was refreshed.</small></td>
								<td class="align-middle text-center"><a href="#" data-bs-toggle="modal" data-bs-target="#modalMODImage" data-image-name="aqiMod.png"><img src="./assets/img/temperatureMod.png" width="25" height="25"></a></small></td>
							</tr>
							<tr>
								<td><small>Pollen Risk</small></td>
								<td><small>dvmPollenModule.php</small></td>
								<td><small> The module also displays, in the upper left corner, the last time the data was refreshed.</small></td>
								<td class="align-middle text-center"><a href="#" data-bs-toggle="modal" data-bs-target="#modalMODImage" data-image-name="aqiMod.png"><img src="./assets/img/pollenMod.png" width="25" height="25"></a></small></td>
							</tr>
							<tr>
								<td><small>Rainfall</small></td>
								<td><small>dvmRainfallModule.php</small></td>
								<td><small> The module also displays, in the upper left corner, the last time the data was refreshed.</small></td>
								<td class="align-middle text-center"><a href="#" data-bs-toggle="modal" data-bs-target="#modalMODImage" data-image-name="aqiMod.png"><img src="./assets/img/rainfallMod.png" width="25" height="25"></a></small></td>
							</tr>
							<tr>
								<td><small>Solar | UV | Lux</small></td>
								<td><small>dvmSolarUvLuxModule.php</small></td>
								<td><small> The module also displays, in the upper left corner, the last time the data was refreshed.</small></td>
								<td class="align-middle text-center"><a href="#" data-bs-toggle="modal" data-bs-target="#modalMODImage" data-image-name="aqiMod.png"><img src="./assets/img/solarUVMod.png" width="25" height="25"></a></small></td>
							</tr>
							<tr>
								<td><small>Solar Dial</small></td>
								<td><small>dvmSolarDialModule.php</small></td>
								<td><small> The module also displays, in the upper left corner, the last time the data was refreshed.</small></td>
								<td class="align-middle text-center"><a href="#" data-bs-toggle="modal" data-bs-target="#modalMODImage" data-image-name="aqiMod.png"><img src="./assets/img/solardialMod.png" width="25" height="25"></a></small></td>
							</tr>
							<tr>
								<td><small>Weather Webcam</small></td>
								<td><small>dvmWebcamModule.php</small></td>
								<td><small> The module also displays, in the upper left corner, the last time the data was refreshed.</small></td>
								<td class="align-middle text-center"><a href="#" data-bs-toggle="modal" data-bs-target="#modalMODImage" data-image-name="aqiMod.png"><img src="./assets/img/webcamMod.png" width="25" height="25"></a></small></td>
							</tr>
							<tr>
								<td><small>Wind Compass</small></td>
								<td><small>dvmWindModule.php</small></td>
								<td><small> The module also displays, in the upper left corner, the last time the data was refreshed.</small></td>
								<td class="align-middle text-center"><a href="#" data-bs-toggle="modal" data-bs-target="#modalMODImage" data-image-name="aqiMod.png"><img src="./assets/img/windMod.png" width="25" height="25"></a></small></td>
							</tr>
							<tr>
								<td><small>World Earthquakes</small></td>
								<td><small>dvmEarthquakeModule.php</small></td>
								<td><small> The module also displays, in the upper left corner, the last time the data was refreshed.</small></td>
								<td class="align-middle text-center"><a href="#" data-bs-toggle="modal" data-bs-target="#modalMODImage" data-image-name="aqiMod.png"><img src="./assets/img/earthquakeMod.png" width="25" height="25"></a></small></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>DivumWX Admin Dashboard - v<?php echo $admVersion?>;
		</div>
		<!-- END #content -->
		<!-- BEGIN #modalModImage -->
		<div class="modal fade" id="modalMODImage">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Module Image</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
					</div>
					<div class="modal-body text-center">
						<img id="modalImage" src="" alt="Image">
					</div>
				</div>
			</div>
		</div>
		<!-- END #modalModImage -->
		<!-- BEGIN btn-scroll-top -->
		<a href="#" data-toggle="scroll-to-top" class="btn-scroll-top fade"><i class="fa fa-arrow-up"></i></a>
		<!-- END btn-scroll-top -->
	</div>
	<!-- END #app -->
	<script src="assets/js/vendor.min.js"></script>
	<script src="assets/js/app.min.js"></script>
	<script>
		$(document).ready(function() {
			$('#modalMODImage').on('show.bs.modal', function(event) {
			var triggerLink = $(event.relatedTarget);
			var imageName = triggerLink.data('image-name');
			var modalImage = $(this).find('#modalImage');
			modalImage.attr('src', './assets/img/' + imageName);
			});
		});
	</script>

</body>
</html>
