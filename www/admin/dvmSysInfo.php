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
$usrSettingsFile = '../userSettings.php';
$usrSettings = ldusrSettings($usrSettingsFile);
extract($usrSettings);
$topRowOptions = [
	'' => 'None',
	'dvmAirqualityTop.php' => 'Air Quality Summary',
	'dvmLightningTop.php' => 'Lightning Summary',
	'dvmRainfallMonthYearTop.php' => 'Rainfall Stats',
	'dvmTemperatureYearTop.php' => 'Temperature Stats',
	'dvmWindGustYearTop.php' => 'Wind Stats'
];
$otherRowOptions = [
	'' => 'None',
	'dvmAirqualityModule.php' => 'Air Quality',
	'dvmAnemometerModule.php' => 'Anemometer',
	'dvmBarometerModule.php' => 'Barometer',
	'dvmCurrentModule.php' => 'Current Conditions',
	'dvmEarthDaylightModule.php' => 'Earth Image',
	'dvmEarthquakeModule.php' => 'World Earthquakes',
	'dvmForecastModule.php' => 'Forecast',
	'dvmIndoorTemperatureModule.php' => 'Indoor Conditions',
	'dvmLightningModule.php' => 'Lightning Strikes',
	'dvmMoonPhaseModule.php' => 'Moon Phase Image',
	'dvmPollenModule.php' => 'Pollen Risk',
	'dvmRainfallModule.php' => 'Rainfall',
	'dvmSolarDialModule.php' => 'Solar Dial',
	'dvmSolarUvLuxModule.php' => 'Solar | UV | Lux',
	'dvmTemperatureModule.php' => 'Outdoor Temperature',
	'dvmWebcamModule.php' => 'Weather Web Cam',
	'dvmWindModule.php' => 'Wind Compass'
];
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
	<head>
		<meta charset="utf-8">
		<title>DivumWX | Settings</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="">
		<meta name="author" content="">
		<link href="assets/css/vendor.min.css" rel="stylesheet">
		<link href="assets/css/app.min.css" rel="stylesheet">
		<style>
			.input-group>input.someInput {
				flex: 0 1 300px;
				height: 35px;
			}
			#notificationContainer {
				max-height: 30px;
				overflow: hidden;
				transition: max-height 0.3s ease;
				display: flex;
				justify-content: center;
				align-items: center;
			}
		</style>
		<script src="assets/js/bootstrap-validate.js"></script>
		<script>
			function addOptionsToSelect(selectId, flag) {
				fetch('assets/countries.json')
				.then(response => response.json())
				.then(jsonData => {
					const selectElement = document.getElementById(selectId);
					for (const item of jsonData) {
					const optionElement = document.createElement("option");
					optionElement.value = item.code;
					optionElement.text = item.name;

					if (item.code === flag) {
						optionElement.selected = true; // Set the option as selected
					}

					selectElement.appendChild(optionElement);
					}
				})
				.catch(error => {
					console.log('Error reading countries.json:', error);
				});
			}
		</script>
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
			<?php displaySidebar('sysinfo'); ?>
				<!-- BEGIN container -->
				<div class="container">
					<div class="row justify-content-center">
						<div class="col-xl-12">
							<div class="row">

							</div>DivumWX Admin Dashboard - v<?php echo $admVersion?>
						</div>
					</div>
				</div>
			</div>
			<a href="#" data-toggle="scroll-to-top" class="btn-scroll-top fade"><i class="fa fa-arrow-up"></i></a>
		</div>
		<script src="assets/js/vendor.min.js"></script>
		<script src="assets/js/app.min.js"></script>
	</body>
</html>