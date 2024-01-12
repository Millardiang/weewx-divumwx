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
		<!-- BEGIN #app -->
		<div id="app" class="app">
			<div id="header" class="app-header">
				<div class="desktop-toggler">
					<button type="button" class="menu-toggler" data-toggle-class="app-sidebar-collapsed" data-dismiss-class="app-sidebar-toggled" data-toggle-target=".app">
						<span class="bar"></span>
						<span class="bar"></span>
						<span class="bar"></span>
					</button>
				</div>
				<div class="mobile-toggler">
					<button type="button" class="menu-toggler" data-toggle-class="app-sidebar-mobile-toggled" data-toggle-target=".app">
						<span class="bar"></span>
						<span class="bar"></span>
						<span class="bar"></span>
					</button>
				</div>
				<div class="brand">
					<a href="#" class="brand-logo">
						<span class="brand-img">
							<span class="brand-img-text text-theme">DvM</span>
						</span>
						<span class="brand-text">System Settings</span>
					</a>
				</div>
				<div class="menu"></div>
			</div>
			<!-- END #header -->
			<!-- BEGIN #sidebar -->
			<div id="sidebar" class="app-sidebar">
				<!-- BEGIN scrollbar -->
				<div class="app-sidebar-content" data-scrollbar="true" data-height="100%">
					<!-- BEGIN menu -->
					<div class="menu">
						<div class="menu-header">Navigation</div>
						<div class="menu-item">
							<a href="../index.php" class="menu-link">
								<span class="menu-icon"><i class="bi bi-rocket-takeoff"></i></span>
								<span class="menu-text">Return to Website</span>
							</a>
						</div>
						<div class="menu-item">
							<a href="dvmDashboard.php" class="menu-link">
								<span class="menu-icon"><i class="bi bi-house-door"></i></span>
								<span class="menu-text">Home</span>
							</a>
						</div>
						<div class="menu-item">
							<a href="dvmFilemanager.php" class="menu-link">
								<span class="menu-icon"><i class="bi bi-folder"></i></span>
								<span class="menu-text">File Manager</span>
							</a>
						</div>
						<div class="menu-item">
							<a href="dvmSettings.php" class="menu-link">
								<span class="menu-icon"><i class="bi bi-gear"></i></span>
								<span class="menu-text">Settings</span>
							</a>
						</div>
						<div class="menu-item">
							<a href="./dvmModFiles.php" class="menu-link">
								<span class="menu-icon"><i class="bi bi-gear"></i></span>
								<span class="menu-text">Module Info</span>
							</a>
						</div>
						<div class="menu-item">
							<a href="./dvmSettings.php?logout=true" class="menu-link">
								<span class="menu-icon"><i class="bi bi-box-arrow-right"></i></span>
								<span class="menu-text">Logout</span>
							</a>
						</div>
					</div>
					<!-- END menu -->
				</div>
				<!-- END scrollbar -->
			</div>
			<!-- END #sidebar -->
			<!-- BEGIN mobile-sidebar-backdrop -->
			<button class="app-sidebar-mobile-backdrop" data-toggle-target=".app" data-toggle-class="app-sidebar-mobile-toggled"></button>
			<!-- END mobile-sidebar-backdrop -->
			<div id="content" class="app-content">
				<!-- BEGIN container -->
				<div class="container">
					<div class="row justify-content-center">
						<div class="col-xl-12">
							<div class="row">
								<div class="col-xl-10">
									<!-- BEGIN #general -->
									<div id="modules" class="mb-5">
										<h4><i class="fas fa-grip fa-fw text-theme"></i> Module Placement</h4>
										<p>Main Page Module Placement</p>
										<div class="card">
											<div class="list-group list-group-flush">
												<div class="list-group-item d-flex align-items-center">
													<table class="table table-bordered mb-0">
														<thead>
															<tr>
																<td colspan="12"><figure class="text-center">Use this area to determine which modules are positioned where on your site. As you can see below, the system uses a 12 column grid, divided into 6 rows. The top row consists of 4 boxes, or 'positions' each spanning 3 columns of the grid. The next 5 rows each consist of 3 boxes, or 'positions', each spanning 4 columns of the grid, as you can see in the layout below. The numbering starts from the upper left position, labeled 'Position 1' and proceeds to the right, first row positions 1 thru 4, second row positions 5 thru 7, third row, positions 8 thru 10, fourth row, positions 11 thru 13, fifth row, positions 14 thru 16, sixth row, positions 17 thru 19.<br />Position #1 and Position #4 are hardcoded and can not be changed. All the erst can be. Each position below lists the existing selected module and offers a dropdown selector for you to choose what you want displayed there. Click <a href="#modalEdit" data-bs-toggle="modal">here</a> for a list of the top modules and <a href="#modalEdit" data-bs-toggle="modal">here</a> for a list of the main modules.</figure></td>
															</tr>
														</thead>
														<tr>
															<td colspan="3"><p class="text-center">Position 1<br />Station Time<br /><span class="badge bg-danger">Unchangeable</span></td>
															<td colspan="3"><p class="text-center">Position 2<br /><?php echo createDropdown('position2', $topRowOptions, $position2, 2); ?></td>
															<td colspan="3"><p class="text-center">Position 3<br /><?php echo createDropdown('position3', $topRowOptions, $position3, 3); ?></td>
															<td colspan="3"><p class="text-center">Position 4<br />Weather Advisories<br /><span class="badge bg-danger">Unchangeable</span></td>
														</tr>
														<tr>
															<td colspan="4"><p class="text-center">Position 5<br /><?php echo createDropdown('position5', $otherRowOptions, $position5, 5); ?></td>
															<td colspan="4"><p class="text-center">Position 6<br /><?php echo createDropdown('position6', $otherRowOptions, $position6, 6); ?></td>
															<td colspan="4"><p class="text-center">Position 7<br /><?php echo createDropdown('position7', $otherRowOptions, $position7, 7); ?></td>
														</tr>
														<tr>
															<td colspan="4"><p class="text-center">Position 8<br /><?php echo createDropdown('position8', $otherRowOptions, $position8, 8); ?></td>
															<td colspan="4"><p class="text-center">Position 9<br /><?php echo createDropdown('position9', $otherRowOptions, $position9, 9); ?></td>
															<td colspan="4"><p class="text-center">Position 10<br /><?php echo createDropdown('position10', $otherRowOptions, $position10, 10); ?></td>
														</tr>
														<tr>
														<tr>
															<td colspan="4"><p class="text-center">Position 11<br /><?php echo createDropdown('position11', $otherRowOptions, $position11, 11); ?></td>
															<td colspan="4"><p class="text-center">Position 12<br /><?php echo createDropdown('position12', $otherRowOptions, $position12, 12); ?></td>
															<td colspan="4"><p class="text-center">Position 13<br /><?php echo createDropdown('position13', $otherRowOptions, $position13, 13); ?></td>
														</tr>
														<tr>
															<td colspan="4"><p class="text-center">Position 14<br /><?php echo createDropdown('position14', $otherRowOptions, $position14, 14); ?></td>
															<td colspan="4"><p class="text-center">Position 15<br /><?php echo createDropdown('position15', $otherRowOptions, $position15, 15); ?></td>
															<td colspan="4"><p class="text-center">Position 16<br /><?php echo createDropdown('position16', $otherRowOptions, $position16, 16); ?></td>
														<tr>
															<td colspan="4"><p class="text-center">Position 17<br /><?php echo createDropdown('position17', $otherRowOptions, $position17, 17); ?></td>
															<td colspan="4"><p class="text-center">Position 18<br /><?php echo createDropdown('position18', $otherRowOptions, $position18, 18); ?></td>
															<td colspan="4"><p class="text-center">Position 19<br /><?php echo createDropdown('position19', $otherRowOptions, $position19, 19); ?></td>
														</tr>
													</table>
												</div>
											</div>
											<div class="card-arrow">
												<div class="card-arrow-top-left"></div>
												<div class="card-arrow-top-right"></div>
												<div class="card-arrow-bottom-left"></div>
												<div class="card-arrow-bottom-right"></div>
											</div>
										</div>
									</div>
									<!-- END #modules -->
									<!-- BEGIN #misc -->
									<div id="misc" class="mb-5">
										<h4><i class="fas fa-sun fa-fw text-theme"></i> Miscellaneous</h4>
										<p>Misc System Settings</p>
										<div class="card">
											<div class="list-group list-group-flush">
												<div class="list-group-item d-flex align-items-center">
													<div class="flex-1 text-break">
														<div>Allow anonymous site visitation mapping by City/Country?</div>
														<div class="text-inverse text-opacity-50 d-flex align-items-center">
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php $trkVisitstxt = ($trkVisits == 1) ? "Yes" : "No"; echo $trkVisitstxt; ?></span>
														</div>
													</div>
													<div>
														<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="newtrkVisits" id="newtrkVisitsYes"<?php echo ($trkVisits == '1') ? 'checked' : ''; ?> value="1">
															<label class="form-check-label" for="newtrkVisitsYes">
																Yes
															</label>
														</div>
														<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="newtrkVisits" id="newtrkVisitsNo"<?php echo ($trkVisits == '0') ? 'checked' : ''; ?> value="0">
															<label class="form-check-label" for="newtrkVisitsNo">
																No
															</label>
														</div>
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="flex-1 text-break">
														<div>Which web server are you running?</div>
														<div class="row mb-n3">
															<div class="col-xl-6">
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary">
																<?php
																	switch ($webSrvr) {
																		case '0':
																			$webSrvrType = 'Apache';
																			break;
																		case '1':
																			$webSrvrType = 'Nginx';
																			break;
																		case '2':
																			$webSrvrType = 'All others';
																			break;
																	}
																echo $webSrvrType;?></span>
															</div>
															<div class="col-xl-6">
																<select name="webSrvr" id="newwebSrvr" name="newwebSrvr" class="newElement form-select form-select-sm">
																	<option value=""> Select Web Server type </option>
																	<option value="0" <?php if($webSrvr === '0') echo 'selected'; ?>>Apache</option>
																	<option value="1" <?php if($webSrvr === '1') echo 'selected'; ?>>Nginx</option>
																	<option value="2" <?php if($webSrvr === '2') echo 'selected'; ?>>All others</option>
																</select><br />
															</div>
														</div>
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="flex-1 text-break">
														<div>Block Your IP from Site Visits count?</div>
														<div class="text-inverse text-opacity-50 d-flex align-items-center">
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php $stripLocaltxt = ($stripLocal == 1) ? "Yes" : "No"; echo $stripLocaltxt; ?></span>
														</div>
													</div>
													<div>
														<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="newstripLocal" id="newstripLocalYes"<?php echo ($stripLocal == '1') ? 'checked' : ''; ?> value="1">
															<label class="form-check-label" for="newstripLocalYes">
																Yes
															</label>
														</div>
														<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="newstripLocal" id="newstripLocalNo"<?php echo ($stripLocal == '0') ? 'checked' : ''; ?> value="0">
															<label class="form-check-label" for="newstripLocalNo">
																No
															</label>
														</div>
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="col-4">
														<div>Your Local IP</div>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $localIP; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
														<input type="text" id="newlocalIP" name="newlocalIP" class="newElement form-control someInput" placeholder="Enter your Local IP Address" onblur="validateAndAlert(this.value)">
														&nbsp;<div id="notificationContainer"></div>
													</div>
												</div>

												<div class="list-group-item d-flex align-items-center">
													<div class="col-4">
														<div>Date Format</div>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $dateFormat; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
														<input type="text" id="newdateFormat" name="newdateFormat" class="newElement form-control someInput" placeholder="Enter Date Format">
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="col-4">
														<div>Time Format</div>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $timeFormat; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
														<input type="text" id="newtimeFormat" name="newtimeFormat" class="newElement form-control someInput"  placeholder="Enter Time Format">
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="col-4">
														<div>Short Time Format</div>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $timeFormatShort; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
														<input type="text" id="newtimeFormatShort" name="newtimeFormatShort" class="newElement form-control someInput" placeholder="Enter Short Time Format">
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="flex-1 text-break">
														<div>System Time Format (12hr/24hr)</div>
														<div class="text-inverse text-opacity-50 d-flex align-items-center">
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $clockformat; ?></span>
														</div>
													</div>
													<div>
														<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="newclockformat" id="newClockFormat12"<?php echo ($clockformat == '12') ? 'checked' : ''; ?> value="12">
															<label class="form-check-label" for="newClockFormat12">
																12hr
															</label>
														</div>
														<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="newclockformat" id="newClockFormat24"<?php echo ($clockformat == '24') ? 'checked' : ''; ?> value="24">
															<label class="form-check-label" for="newClockFormat24">
																24hr
															</label>
														</div>
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="flex-1 text-break">
														<div>System Default Language</div>
														<div class="row mb-n3">
															<div class="col-xl-6">
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Default Language:&nbsp;&nbsp;<span class="badge bg-secondary">
																<?php
																	switch ($defaultlanguage) {
																		case 'cat':
																			$dfLang = 'Catalan';
																			break;
																		case 'dk':
																			$dfLang = 'Danish';
																			break;
																		case 'dl':
																			$dfLang = 'German';
																			break;
																		case 'en':
																			$dfLang = 'English';
																			break;
																		case 'fr':
																			$dfLang = 'French';
																			break;
																		case 'gr':
																			$dfLang = 'Greek';
																			break;
																		case 'hu':
																			$dfLang = 'Hungarian';
																			break;
																		case 'it':
																			$dfLang = 'Italian';
																			break;
																		case 'nl':
																			$dfLang = 'Dutch';
																			break;
																		case 'no':
																			$dfLang = 'Norwegian';
																			break;
																		case 'pl':
																			$dfLang = 'Polish';
																			break;
																		case 'sp':
																			$dfLang = 'Spanish';
																			break;
																		case 'sw':
																			$dfLang = 'Swedish';
																			break;
																		case 'tr':
																			$dfLang = 'Turkish';
																			break;
																	}
																echo $dfLang;?></span>
															</div>
															<div class="col-xl-6">
																<select class="newElement form-select form-select-sm" name="newdefaultlanguage" id="newdefaultlanguage">
																	<option value="">Select New System Language</option>
																	<option value="cat" <?php if($defaultlanguage == 'cat') echo 'selected'; ?>>Catalan</option>
																	<option value="dk" <?php if($defaultlanguage == 'dk') echo 'selected'; ?>>Danish</option>
																	<option value="dl" <?php if($defaultlanguage == 'dl') echo 'selected'; ?>>German</option>
																	<option value="en" <?php if($defaultlanguage == 'en') echo 'selected'; ?>>English</option>
																	<option value="fr" <?php if($defaultlanguage == 'fr') echo 'selected'; ?>>French</option>
																	<option value="gr" <?php if($defaultlanguage == 'gr') echo 'selected'; ?>>Greek</option>
																	<option value="hu" <?php if($defaultlanguage == 'hu') echo 'selected'; ?>>Hungarian</option>
																	<option value="it" <?php if($defaultlanguage == 'it') echo 'selected'; ?>>Italian</option>
																	<option value="nl" <?php if($defaultlanguage == 'nl') echo 'selected'; ?>>Dutch</option>
																	<option value="no" <?php if($defaultlanguage == 'no') echo 'selected'; ?>>Norwegian</option>
																	<option value="pl" <?php if($defaultlanguage == 'pl') echo 'selected'; ?>>Polish</option>
																	<option value="sp" <?php if($defaultlanguage == 'sp') echo 'selected'; ?>>Spanish</option>
																	<option value="sw" <?php if($defaultlanguage == 'sw') echo 'selected'; ?>>Swedish</option>
																	<option value="tr" <?php if($defaultlanguage == 'tr') echo 'selected'; ?>>Turkish</option>
																</select><br />
															</div>
														</div>
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="flex-1 text-break">
														<div>Enable Sidebar Extra Links</div>
														<div class="text-inverse text-opacity-50 d-flex align-items-center">
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $extralinks; ?></span>
														</div>
													</div>
													<div>
														<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="extralinks" id="enableSBlinksYes"<?php echo ($extralinks == 'yes') ? 'checked' : ''; ?> value="yes">
															<label class="form-check-label" for="enableSBlinksYes">
																Yes
															</label>
														</div>
														<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="extralinks" id="enableSBlinksNo"<?php echo ($extralinks == 'no') ? 'checked' : ''; ?> value="no">
															<label class="form-check-label" for="enableSBlinksNo">
																No
															</label>
														</div>
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="flex-1 text-break">
														<div>Enable Sidebar Language Selection</div>
														<div class="text-inverse text-opacity-50 d-flex align-items-center">
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $sbLang; ?></span>
														</div>
													</div>
													<div>
														<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="sbLang" id="enableSBlangYes"<?php echo ($sbLang == 'yes') ? 'checked' : ''; ?> value="yes">
															<label class="form-check-label" for="enableSBlangYes">
																Yes
															</label>
														</div>
														<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="sbLang" id="enableSBlangNo"<?php echo ($sbLang == 'no') ? 'checked' : ''; ?> value="no">
															<label class="form-check-label" for="enableSBlangNo">
																No
															</label>
														</div>
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="flex-1 text-break">
														<div>Weather Advisory Zones</div>
														<div class="row mb-n3">
															<div class="col-xl-6">
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Advisory Zone:&nbsp;&nbsp;<span class="badge bg-secondary">
																<?php
																	switch ($advisoryzone) {
																		case 'uk':
																			$azName = 'United Kingdom';
																			break;
																		case 'na':
																			$azName = 'North America';
																			break;
																		case 'eu':
																			$azName = 'Europe';
																			break;
																		case 'au':
																			$azName = 'Australia';
																			break;
																		case 'rw':
																			$azName = 'Rest of the World';
																			break;
																	}
																echo $azName;?></span>
															</div>
															<div class="col-xl-6">
																<select class="newElement form-select form-select-sm" name="advisoryzone" id="advisoryzone">
																	<option value="">Select New Advisory Zone</option>
																	<option value="uk" <?php if($advisoryzone == 'uk') echo 'selected'; ?>>United Kingdom</option>
																	<option value="na" <?php if($advisoryzone == 'na') echo 'selected'; ?>>North America</option>
																	<option value="eu" <?php if($advisoryzone == 'eu') echo 'selected'; ?>>Europe</option>
																	<option value="au" <?php if($advisoryzone == 'au') echo 'selected'; ?>>Australia</option>
																	<option value="rw" <?php if($advisoryzone == 'rw') echo 'selected'; ?>>Rest of the World</option>
																</select><br />
															</div>
														</div>
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
												<div class="flex-1 text-break">
														<div>Enable Air Quality System</div>
														<div class="text-inverse text-opacity-50 d-flex align-items-center">
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $aqInUse; ?></span>
														</div>
													</div>
													<div>
														<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="aqInUse" id="enableSBLangYes"<?php echo ($aqInUse == 'yes') ? 'checked' : ''; ?> value="yes">
															<label class="form-check-label" for="enableSBLangYes">
																Yes
															</label>&nbsp;&nbsp;
														</div>
														<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="aqInUse" id="enableSBLangNo"<?php echo ($aqInUse == 'no') ? 'checked' : ''; ?> value="no">
															<label class="form-check-label" for="enableSBLangNo">
																No
															</label>
														</div>
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="flex-1 text-break">
														<div>Air Quality Zone</div>
														<div class="row mb-n3">
															<div class="col-xl-6">
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary">
																<?php
																	switch ($aqZone) {
																		case 'uk':
																			$aqDisplayZone = 'United Kingdom DAQI';
																			break;
																		case 'us':
																			$aqDisplayZone = 'United States EPS';
																			break;
																		case 'ei':
																			$aqDisplayZone = 'Europe EAQI';
																			break;
																		case 'ci':
																			$aqDisplayZone = 'Europe CAQI';
																			break;
																		case 'au':
																			$aqDisplayZone = 'Australia';
																			break;
																	}
																echo $aqDisplayZone;?></span>
															</div>
															<div class="col-xl-6">
																<select name="aqZone" id="aqZone" class="newElement form-select form-select-sm">
																	<option> Select New Air Quality Zone </option>
																	<option value="uk" <?php if($aqZone == 'uk') echo 'selected'; ?>>United Kingdom DAQI</option>
																	<option value="us" <?php if($aqZone == 'us') echo 'selected'; ?>>United States EPS</option>
																	<option value="ei" <?php if($aqZone == 'ei') echo 'selected'; ?>>Europe EAQI</option>
																	<option value="ci" <?php if($aqZone == 'ci') echo 'selected'; ?>>Europe CAQI</option>
																	<option value="au" <?php if($aqZone == 'au') echo 'selected'; ?>>Australia</option>
																</select><br />
															</div>
														</div>
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="flex-1 text-break">
														<div>Air Quality Source</div>
														<div class="text-inverse text-opacity-50 d-flex align-items-center">
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $aqSource; ?></span>
														</div>
													</div>
													<div>
														<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="newaqSource" id="newAQSourcePurple"<?php echo ($aqSource == 'purple') ? 'checked' : ''; ?> value="purple">
															<label class="form-check-label" for="newAQSourcePurple">
																Purple Air
															</label>&nbsp;&nbsp;
														</div>
														<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="newaqSource" id="newAQSourceWeewx"<?php echo ($aqSource == 'weewx') ? 'checked' : ''; ?> value="weewx">
															<label class="form-check-label" for="newAQSourceWeewx">
																Weewx
															</label>
														</div>
														<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="newaqSource" id="newAQSourceSds"<?php echo ($aqSource == 'sds') ? 'checked' : ''; ?> value="sds">
															<label class="form-check-label" for="newAQSourceSds">
																SDS
															</label>
														</div>
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="flex-1 text-break">
														<div>Lightning Detector Source</div>
														<div class="row mb-n3">
															<div class="col-xl-6">
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary">
																<?php
																	switch ($lightningSource) {
																		case '0':
																			$lSource = 'Boltek';
																			break;
																		case '1':
																			$lSource = 'All others';
																			break;
																	}
																echo $lSource;?></span>
															</div>
															<div class="col-xl-6">
																<select name="lightningSource" id="newlightningSource" name="newlightningSource" class="newElement form-select form-select-sm">
																	<option value=""> Select New Lightning Detector </option>
																	<option value="0" <?php if($lightningSource === '0') echo 'selected'; ?>>Boltek Detector</option>
																	<option value="1" <?php if($lightningSource === '1') echo 'selected'; ?>>All others</option>
																</select><br />
															</div>
														</div>
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="col-4">
														<div>Web Camera URL</div>
														<?php
															$curWebCamURL = (empty($videoWeatherCamURL)) ? "None" : $videoWeatherCamURL;
														?>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $curWebCamURL; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
														<input type="text" name="newvideoWeatherCamURL" id="newvideoWeatherCamURL" class="newElement form-control someInput"  placeholder="Enter Web Cam URL">
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="col-4">
														<div>Email address</div>
														<?php
															$curEmail = (empty($email)) ? "None" : $email;
														?>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $curEmail; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
														<input type="text" name="newemail" id="newemail" class="newElement form-control someInput"  placeholder="Enter Email Address">
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="col-4">
														<div>Twitter Account</div>
														<?php
															$curTwitter= (empty($twitter)) ? "None" : $twitter;
														?>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $curTwitter; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
														<input type="text" id="newTwitter" name="newTwitter" class="newElement form-control someInput"  placeholder="Enter Twitter Account">
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="col-4">
														<div>What year did your Weather Station Start Operating?</div>
														<?php
															$curSince = (empty($since)) ? "Empty" : $since;
														?>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $curSince; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
														<input type="text" name="newsince" id="newsince" class="newElement form-control someInput"  placeholder="Enter New System Since Date">
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
												<div class="flex-1 text-break">
													<div>System Location Country Flag</div>
														<div class="row mb-n3">
															<div class="col-xl-6">
																<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Country Flag:&nbsp;&nbsp;<span class="badge bg-secondary">
																<?php
																	$jsonData = file_get_contents('assets/countries.json');
																	$countries = json_decode($jsonData, true);
																	foreach ($countries as $country) {
																		if ($country['code'] === $flag) {
																			$curFlag = $country['name'];
																			break;
																		}
																	}
																	echo $curFlag;
																?></span>&nbsp;&nbsp;<img src="../../img/flags/<?php echo $country['code']?>.svg" width="50" height="20">
															</div>
															<div class="col-xl-6">
																<select name="newflag" id="newFlag" class="newElement form-select form-select-sm">
																	<option> Select New Country Flag </option>
																</select><br />
															</div>
														</div>
													</div>
												</div>
												<script>
													var flagValue = "<?php echo $flag; ?>";
													addOptionsToSelect("newFlag", flagValue);
												</script>
												<div class="list-group-item d-flex align-items-center">
													<div class="col-4">
														<div>Enter a short abbreviation for the name of your weather station.</div>
														<?php
															$curShortName = (empty($manifestShortName)) ? "Empty" : $manifestShortName;
														?>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $curShortName; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
														<input type="text" name="newmanifestShortName" id="newmanifestShortName" class="newElement form-control someInput"  placeholder="Enter New System Short Name">
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="flex-1 text-break">
														<div>Enable System Notifications</div>
														<div class="text-inverse text-opacity-50 d-flex align-items-center">
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $notifications; ?></span>
														</div>
													</div>
													<div>
														<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="newnotifications" id="enableNotifyYes"<?php echo ($notifications == 'yes') ? 'checked' : ''; ?> value="yes">
															<label class="form-check-label" for="enableNotifyYes">
																Yes
															</label>&nbsp;&nbsp;
														</div>
														<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="newnotifications" id="enableNotifyNo"<?php echo ($notifications == 'no') ? 'checked' : ''; ?> value="no">
															<label class="form-check-label" for="enableNotifyNo">
																No
															</label>
														</div>
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="flex-1 text-break">
														<div>Enable Wind Notifications</div>
														<div class="text-inverse text-opacity-50 d-flex align-items-center">
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $notifyWind; ?></span>
														</div>
													</div>
													<div>
														<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="newnotifyWind" id="enableNotifyWYes"<?php echo ($notifyWind == 'yes') ? 'checked' : ''; ?> value="yes">
															<label class="form-check-label" for="enableNotifyWYes">
																Yes
															</label>&nbsp;&nbsp;
														</div>
														<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="newnotifyWind" id="enableNotifyWNo"<?php echo ($notifyWind == 'no') ? 'checked' : ''; ?> value="no">
															<label class="form-check-label" for="enableNotifyWNo">
																No
															</label>
														</div>
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="flex-1 text-break">
														<div>Enable Earthquake Notifications</div>
														<div class="text-inverse text-opacity-50 d-flex align-items-center">
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $notifyEarthquake; ?></span>
														</div>
													</div>
													<div>
														<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="newnotifyEarthquake" id="enableNotifyEQYes"<?php echo ($notifyEarthquake == 'yes') ? 'checked' : ''; ?> value="yes">
															<label class="form-check-label" for="enableSBLangYes">
																Yes
															</label>&nbsp;&nbsp;
														</div>
														<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="newnotifyEarthquake" id="enableNotifyEQNo"<?php echo ($notifyEarthquake == 'no') ? 'checked' : ''; ?> value="no">
															<label class="form-check-label" for="enableNotifyEQNo">
																No
															</label>
														</div>
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="flex-1 text-break">
														<div>Earthquake Notification Magnitude Selection</div>
														<div class="text-inverse text-opacity-50 d-flex align-items-center">
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $notifyMagnitude; ?></span>
														</div>
													</div>
													<div>
														<div class="form-control-range">
															<?php $curMagSetting = $notifyMagnitude; ?>
															<label for="newNotifyMagnitude" class="form-label" id="rangeLabel">New Earthquake Magnitude Notification Setting: <?php echo $curMagSetting; ?></label>
															<input type="range" class="newElement form-range" min="0" max="9" step="1" value="<?php echo $notifyMagnitude; ?>" name="newnotifyMagnitude" id="newNotifyMagnitude">
														</div>
													</div>
												</div>
											</div>
											<div class="card-arrow">
												<div class="card-arrow-top-left"></div>
												<div class="card-arrow-top-right"></div>
												<div class="card-arrow-bottom-left"></div>
												<div class="card-arrow-bottom-right"></div>
											</div>
										</div>
									</div>
									<!-- Begin links -->
									<div id="links" class="mb-5">
										<h4><i class="fas fa-anchor fa-fw text-theme"></i> External Links</h4>
										<p>Additional External Sidebar Links.</p>
										<div class="card">
											<div class="list-group list-group-flush">
												<div class="list-group-item d-flex align-items-center">
													<div class="col-4">
														<div>Link to Weather Underground</div>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $linkWU; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
													<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="newlinkWU" id="newlinkWUYes"<?php echo ($linkWU == 'yes') ? 'checked' : ''; ?> value="yes">
															<label class="form-check-label" for="newlinkWUYes">
																Yes
															</label>&nbsp;&nbsp;
														</div>
														<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="newlinkWU" id="newlinkWUNo"<?php echo ($linkWU == 'no') ? 'checked' : ''; ?> value="no">
															<label class="form-check-label" for="newlinkWUNo">
																No
															</label>
														</div>
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="col-4">
														<div>New Weather Underground Dashboard?</div>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $linkWUNewDash; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
													<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="newlinkWUNewDash" id="linkWUNewDashYes" <?php echo ($linkWUNewDash == 'yes') ? 'checked' : ''; ?> value="yes">
															<label class="form-check-label" for="linkWUNewDashYes">
																Yes
															</label>&nbsp;&nbsp;
														</div>
														<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="newlinkWUNewDash" id="linkWUNewDashNo" <?php echo ($linkWUNewDash == 'no') ? 'checked' : ''; ?> value="no">
															<label class="form-check-label" for="linkWUNewDashNo">
																No
															</label>
														</div>
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="col-4">
														<div>Weather Underground Station ID</div>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $WUid; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
														<input type="text" id="newWUid" name="newWUid" class="newElement form-control someInput"  placeholder="Enter new Weather Underground ID">
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="col-4">
														<div>CWOP ID</div>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $linkCWOPID; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
														<input type="text" id="newCWOPID" name="newCWOPID" class="newElement form-control someInput"  placeholder="Enter new CWOPID">
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="col-4">
														<div>FindU ID</div>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $linkFindUID; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
														<input type="text" id="newlinkFindUID" name="newlinkFindUID" class="newElement form-control someInput"  placeholder="Enter new FindU ID">
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="col-4">
														<div>Enable Link to NOAA?</div>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $linkNOAA; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
													<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="newlinkNOAA" id="enableNOAAYes" <?php echo ($linkNOAA == 'yes') ? 'checked' : ''; ?> value="yes">
															<label class="form-check-label" for="enableNOAAYes">
																Yes
															</label>&nbsp;&nbsp;
														</div>
														<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="newlinkNOAA" id="enableNOAANo" <?php echo ($linkNOAA == 'no') ? 'checked' : ''; ?> value="no">
															<label class="form-check-label" for="enableNOAANo">
																No
															</label>
														</div>
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="col-4">
														<div>Enable link to MADIS?</div>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $linkMADIS; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
													<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="newlinkMADIS" id="enableMADISYes" <?php echo ($linkMADIS == 'yes') ? 'checked' : ''; ?> value="yes">
															<label class="form-check-label" for="enableMADISYes">
																Yes
															</label>&nbsp;&nbsp;
														</div>
														<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="newlinkMADIS" id="enableMADISNo" <?php echo ($linkMADIS == 'no') ? 'checked' : ''; ?> value="no">
															<label class="form-check-label" for="enableMADISNo">
																No
															</label>
														</div>
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="col-4">
														<div>Enable Link to MesoWest?</div>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $linkMesoWest; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
													<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="newlinkMesoWest" id="enableMesoWestYes" <?php echo ($linkMesoWest == 'yes') ? 'checked' : ''; ?> value="yes">
															<label class="form-check-label" for="enableMesoWestYes">
																Yes
															</label>&nbsp;&nbsp;
														</div>
														<div class="form-check">
															<input class="newElement form-check-input" type="radio" name="newlinkMesoWest" id="enableMesoWestNo" <?php echo ($linkMesoWest == 'no') ? 'checked' : ''; ?> value="no">
															<label class="form-check-label" for="enableMesoWestNo">
																No
															</label>
														</div>
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="col-4">
														<div>Weather Cloud ID</div>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $linkWeatherCloudID; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
														<input type="text" id="newlinkWeatherCloudID" name="newlinkWeatherCloudID" class="newElement form-control someInput"  placeholder="Enter new Weathercloud ID">
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="col-4">
														<div>Windy ID</div>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $linkWindyID; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
														<input type="text" id="newlinkWindyID" name="newlinkWindyID" class="newElement form-control someInput"  placeholder="Enter new Windy ID">
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="col-4">
														<div>AWEKAS ID</div>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $linkAWEKASID; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
														<input type="text" id="newlinkAWEKASID" name="newlinkAWEKASID" class="newElement form-control someInput"  placeholder="Enter new AWEKAS ID">
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="col-4">
														<div>Ambient Weather ID</div>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $linkAmbientWeatherID; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
														<input type="text" id="newlinkAmbientWeatherID" name="newlinkAmbientWeatherID" class="newElement form-control someInput"  placeholder="Enter new Ambient Weather ID">
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="col-4">
														<div>PWS Weather ID</div>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $linkPWSWeatherID; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
														<input type="text" id="newlinkPWSWeatherID" name="newlinkPWSWeatherID" class="newElement form-control someInput"  placeholder="Enter new PWS Weather ID">
													</div>
												</div>
												<div class="list-group-item d-flex align-items-center">
													<div class="col-4">
														<div>MET Office ID</div>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $linkMetOfficeID; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
														<input type="text" id="newlinkMetID" name="newlinkMetID" class="newElement form-control someInput"  placeholder="Enter new MET Office ID">
													</div>
												</div>
												<div class="list-group-item d-flex align-top">
													<div class="col-4 align-top">
														<div>Custom Link #1 Title</div>
														<?php
															$curCustom1Title = (empty($linkCustom1Title)) ? "None" : $linkCustom1Title;
														?>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:<br /><span class="badge bg-secondary"><?php echo $curCustom1Title; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
														<input type="text" id="newlinkCustom1Title" name="newlinkCustom1Title" class="newElement form-control someInput"  placeholder="Enter new Custom Link #1 Title">
													</div>
												</div>
												<div class="list-group-item d-flex">
													<div class="col-4">
														<div>Custom Link #1 URL</div>
														<?php
															$curCustom1URL = (empty($linkCustom1URL)) ? "None" : $linkCustom1URL;
														?>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:<br /><span class="badge bg-secondary"><?php echo $curCustom1URL; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
														<input type="text" id="newlinkCustom1URL" name="newlinkCustom1URL" class="newElement form-control someInput"  placeholder="Enter new Custom Link #1 URL">
													</div>
												</div>
												<div class="list-group-item d-flex">
													<div class="col-4">
														<div>Custom Link #2 Title</div>
														<?php
															$curCustom2Title = (empty($linkCustom2Title)) ? "None" : $linkCustom2Title;
														?>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:<br /><span class="badge bg-secondary"><?php echo $curCustom2Title; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
														<input type="text" id="newlinkCustom2Title" name="newlinkCustom2Title" class="newElement form-control someInput"  placeholder="Enter new Custom Link #2 Title">
													</div>
												</div>
												<div class="list-group-item d-flex">
													<div class="col-4">
														<div>Custom Link #2 URL</div>
														<?php
															$curCustom2URL = (empty($linkCustom2URL)) ? "None" : $linkCustom2URL;
														?>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:<br /><span class="badge bg-secondary"><?php echo $curCustom2URL; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
														<input type="text" id="newlinkCustom2URL" name="newlinkCustom2URL" class="newElement form-control someInput"  placeholder="Enter new Custom Link #2 URL">
													</div>
												</div>
												<div class="list-group-item d-flex">
													<div class="col-4">
														<div>USA Weather Finder</div>
														<?php
															$curUSAWeatherFinder = (empty($USAWeatherFinder)) ? "None" : $USAWeatherFinder;
														?>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $curUSAWeatherFinder; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
														<input type="text" id="newUSAWeatherFinder" name="newUSAWeatherFinder" class="newElement form-control someInput"  placeholder="Enter USA Weather Finder Username">
													</div>
												</div>
												<div class="list-group-item d-flex">
													<div class="col-4">
														<div>Extra Link Title?</div>
														<?php
															$curextraLinkTitle = (empty($extraLinkTitle)) ? "None" : $extraLinkTitle;
														?>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $curextraLinkTitle; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
														<input type="text" id="newextraLinkTitle" name="newextraLinkTitle" class="newElement form-control someInput"  placeholder="Enter new Extra Link Title">
													</div>
												</div>
												<div class="list-group-item d-flex">
													<div class="col-4">
														<div>Extra Link URL</div>
														<?php
															$curextraLinkURL = (empty($extraLinkURL)) ? "None" : $extraLinkURL;
														?>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;<span class="badge bg-secondary"><?php echo $curextraLinkURL; ?></span>
														</div>
													</div>
													<div class="input-group justify-content-end">
														<input type="text" id="newextraLinkURL" name="newextraLinkURL" class="newElement form-control someInput"  placeholder="Enter new Extra Link URL">
													</div>
												</div>
												<div class="list-group-item d-flex">
													<div class="col-4">
														<div>Extra Link Color</div>
														<?php
															$curextraLinkColor = (empty($extraLinkColor)) ? "None" : $extraLinkColor;
														?>
														<div>
															<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i> Current Setting:&nbsp;&nbsp;
															<?php
																switch($curextraLinkColor){
																	case 'white':
																		echo '<span class="badge bg-white text-dark">White</span>';
																		break;
																	case 'red':
																		echo '<span class="badge bg-danger\">Red</span>';
																		break;
																	case 'grey':
																		echo '<span class="badge bg-secondary\">Grey</span>';
																		break;
																	case 'green':
																		echo '<span class="badge bg-success\">Green</span>';
																		break;
																	case 'yellow':
																		echo '<span class="badge bg-warning\">Yellow</span>';
																		break;
																	case 'blue':
																		echo
																		'<span class="badge bg-primary\">Blue</span>';
																		break;
																	case 'orange':
																		echo '<span class="badge bg-orange\">Orange</span>';
																		break;
																	default:
																	echo '<span class="badge bg-dark\">None</span>';
																}?>
														</div>
													</div>
													<div class="input-group justify-content-end">
													<select class="newElement form-select form-select-sm" mb-3 id="newextraLinkColor" name="newextraLinkColor">
														<option selected>Select New Extra Link Color</option>
														<option value="white" class="text-white bg-black">White</option>
														<option value="red" class="text-red">Red</option>
														<option value="grey" class="text-gray-100">Grey</option>
														<option value="green" class="text-green">Green</option>
														<option value="yellow" class="text-yellow">Yellow</option>
														<option value="blue" class="text-blue">Blue</option>
														<option value="Orange" class="text-orange">Orange</option>
													</select><br />
													</div>
												</div>
											</div>
											<div class="card-arrow">
												<div class="card-arrow-top-left"></div>
												<div class="card-arrow-top-right"></div>
												<div class="card-arrow-bottom-left"></div>
												<div class="card-arrow-bottom-right"></div>
											</div>
										</div>
									</div>
									<div id="submitChanges" class="mb-5">
										<h4><i class="far fa-save fa-fw text-theme"></i>Submit Changes</h4>
										<p>Submit your changes to the userSettings file.</p>
										<div class="card">
											<div class="list-group list-group-flush">
												<div class="list-group-item d-flex align-items-center">
													<div class="flex-1 text-break">
														<div>Submit Changes</div>
														<div class="text-inverse text-opacity-50">
															This action will save your changes.
														</div>
													</div>
													<div>
														<a href="#" id="saveChangesLink" class="btn btn-outline-default w-175px">Save Changes</a>
													</div>
												</div>
											</div>
											<div class="card-arrow">
												<div class="card-arrow-top-left"></div>
												<div class="card-arrow-top-right"></div>
												<div class="card-arrow-bottom-left"></div>
												<div class="card-arrow-bottom-right"></div>
											</div>
										</div>
									</div>
									<div id="resetSettings" class="mb-5">
										<h4><i class="fa fa-redo fa-fw text-theme"></i> Reset settings</h4>
										<p>Reset all website setting to factory default setting.</p>
										<div class="card">
											<div class="list-group list-group-flush">
												<div class="list-group-item d-flex align-items-center">
													<div class="flex-1 text-break">
														<div>Reset Settings</div>
														<div class="text-inverse text-opacity-50">
															This action will clear and reset all the current website setting.
														</div>
													</div>
													<div>
														<a href="#" class="btn btn-outline-default w-100px">Reset</a>
													</div>
												</div>
											</div>
											<div class="card-arrow">
												<div class="card-arrow-top-left"></div>
												<div class="card-arrow-top-right"></div>
												<div class="card-arrow-bottom-left"></div>
												<div class="card-arrow-bottom-right"></div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-xl-2">
									<nav id="sidebar-bootstrap" class="navbar navbar-sticky d-none d-xl-block">
										<nav class="nav">
											<a href="#timeFormatModal" data-bs-toggle="modal" class="nav-link">PHP Time Strings</a>
											<a class="nav-link" href="#modules" data-toggle="scroll-to">Modules</a>
											<a class="nav-link" href="#misc" data-toggle="scroll-to">Miscellaneous</a>
											<a class="nav-link" href="#links" data-toggle="scroll-to">Links</a>
											<a class="nav-link" href="#submitChanges" data-toggle="scroll-to">Submit Changes</a>
											<a class="nav-link" href="#resetSettings" data-toggle="scroll-to">Reset Form</a>
										</nav>
									</nav>
								</div>
							</div>DivumWX Admin Dashboard - v<?php echo $admVersion?>;
						</div>
					</div>
				</div>
			</div>
			<div class="toasts-container" role="alert" aria-live="assertive" aria-atomic="true">
				<div class="toast fade mb-3 hide" data-autohide="false" data-bs-delay="5000" id="not-needed">
						<div class="toast-header">
							<i class="fas fa-exclamation-circle text-muted me-2"></i>
							<strong class="me-auto">Settings Notification</strong>
							<button type="button" class="btn-close" data-bs-dismiss="toast"></button>
						</div>
						<div class="toast-body">
							There have been no changes to the elements on this page, no save of the userSettings file will be done.
						</div>
				</div>
				<div class="toast fade mb-3 hide" data-autohide="false" data-bs-delay="5000" id="fail">
					<div class="toast-header">
						<i class="fas fa-info-circle text-muted me-2"></i>
						<strong class="me-auto">Another Toast</strong>
						<button type="button" class="btn-close" data-bs-dismiss="toast"></button>
					</div>
					<div class="toast-body">
						There has been an error creating the userSettings.php file. Please check the log file.
					</div>
				</div>
				<div class="toast fade mb-3 hide" data-autohide="false" data-bs-delay="5000" id="success">
						<div class="toast-header">
							<i class="fas fa-info-circle text-muted me-2"></i>
							<strong class="me-auto">Another Toast</strong>
							<button type="button" class="btn-close" data-bs-dismiss="toast"></button>
						</div>
						<div class="toast-body">
							New userSettings.php successfully created and an archive file of the previous version created.
						</div>
					</div>
				</div>
			</div>
			<div class="modal fade" id="timeFormatModal">
				<div class="modal-dialog modal-xl">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">PHP Time Format Characters</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
						</div>
						<div class="modal-body">
							<div class="col-mb-6">
								<table class="table table-bordered mb-0">
									<thead>
										<tr>
											<th><code class="parameter">format</code> character</th>
											<th>Description</th>
											<th>Example returned values</th>
										</tr>
									</thead>
									<tbody class="tbody">
										<tr>
											<td style="text-align: center;"><em class="emphasis">Day</em></td>
											<td>---</td>
										<td>---</td>
										</tr>
										<tr>
											<td><code class="literal">d</code></td>
											<td>Day of the month, 2 digits with leading zeros</td>
											<td><code class="literal">01</code> to <code class="literal">31</code></td>
										</tr>
										<tr>
											<td><code class="literal">D</code></td>
											<td>A textual representation of a day, three letters</td>
											<td><code class="literal">Mon</code> through <code class="literal">Sun</code></td>
										</tr>
										<tr>
											<td><code class="literal">j</code></td>
											<td>Day of the month without leading zeros</td>
											<td><code class="literal">1</code> to <code class="literal">31</code></td>
										</tr>
										<tr>
											<td><code class="literal">l</code> (lowercase &#039;L&#039;)</td>
											<td>A full textual representation of the day of the week</td>
											<td><code class="literal">Sunday</code> through <code class="literal">Saturday</code></td>
										</tr>
										<tr>
											<td><code class="literal">S</code></td>
											<td>English ordinal suffix for the day of the month, 2 characters</td>
											<td>
												<code class="literal">st</code>, <code class="literal">nd</code>, <code class="literal">rd</code> or
												<code class="literal">th</code>.  Works well with <code class="literal">j</code>
											</td>
										</tr>
										<tr>
											<td style="text-align: center;"><em class="emphasis">Month</em></td>
											<td>---</td>
											<td>---</td>
										</tr>
										<tr>
											<td><code class="literal">F</code></td>
											<td>A full textual representation of a month, such as January or March</td>
											<td><code class="literal">January</code> through <code class="literal">December</code></td>
										</tr>
										<tr>
											<td><code class="literal">m</code></td>
											<td>Numeric representation of a month, with leading zeros</td>
											<td><code class="literal">01</code> through <code class="literal">12</code></td>
										</tr>
										<tr>
											<td><code class="literal">M</code></td>
											<td>A short textual representation of a month, three letters</td>
											<td><code class="literal">Jan</code> through <code class="literal">Dec</code></td>
										</tr>
										<tr>
											<td><code class="literal">n</code></td>
											<td>Numeric representation of a month, without leading zeros</td>
											<td><code class="literal">1</code> through <code class="literal">12</code></td>
										</tr>
										<tr>
											<td><code class="literal">t</code></td>
											<td>Number of days in the given month</td>
											<td><code class="literal">28</code> through <code class="literal">31</code></td>
										</tr>
										<tr>
											<td style="text-align: center;"><em class="emphasis">Year</em></td>
											<td>---</td>
											<td>---</td>
										</tr>
										<tr>
											<td><code class="literal">Y</code></td>
											<td>A full numeric representation of a year, at least 4 digits,
												with <code class="literal">-</code> for years BCE.</td>
											<td>Examples: <code class="literal">-0055</code>, <code class="literal">0787</code>,
												<code class="literal">1999</code>, <code class="literal">2003</code>,
												<code class="literal">10191</code></td>
										</tr>
										<tr>
											<td><code class="literal">y</code></td>
											<td>A two digit representation of a year</td>
											<td>Examples: <code class="literal">99</code> or <code class="literal">03</code></td>
										</tr>
										<tr>
											<td style="text-align: center;"><em class="emphasis">Time</em></td>
											<td>---</td>
											<td>---</td>
										</tr>
										<tr>
											<td><code class="literal">a</code></td>
											<td>Lowercase Ante meridiem and Post meridiem</td>
											<td><code class="literal">am</code> or <code class="literal">pm</code></td>
										</tr>
										<tr>
											<td><code class="literal">A</code></td>
											<td>Uppercase Ante meridiem and Post meridiem</td>
											<td><code class="literal">AM</code> or <code class="literal">PM</code></td>
										</tr>
										<tr>
											<td><code class="literal">g</code></td>
											<td>12-hour format of an hour without leading zeros</td>
											<td><code class="literal">1</code> through <code class="literal">12</code></td>
										</tr>
										<tr>
											<td><code class="literal">G</code></td>
											<td>24-hour format of an hour without leading zeros</td>
											<td><code class="literal">0</code> through <code class="literal">23</code></td>
										</tr>
										<tr>
											<td><code class="literal">h</code></td>
											<td>12-hour format of an hour with leading zeros</td>
											<td><code class="literal">01</code> through <code class="literal">12</code></td>
										</tr>
										<tr>
											<td><code class="literal">H</code></td>
											<td>24-hour format of an hour with leading zeros</td>
											<td><code class="literal">00</code> through <code class="literal">23</code></td>
										</tr>
										<tr>
											<td><code class="literal">i</code></td>
											<td>Minutes with leading zeros</td>
											<td><code class="literal">00</code> to <code class="literal">59</code></td>
										</tr>
										<tr>
											<td><code class="literal">s</code></td>
											<td>Seconds with leading zeros</td>
											<td><code class="literal">00</code> through <code class="literal">59</code></td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-outline-default" data-bs-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
			<a href="#" data-toggle="scroll-to-top" class="btn-scroll-top fade"><i class="fa fa-arrow-up"></i></a>
		</div>
		<script src="assets/js/vendor.min.js"></script>
		<script src="assets/js/app.min.js"></script>
		<script>
			const range = document.getElementById('newNotifyMagnitude');
			const rangeLabel = document.getElementById('rangeLabel');
			range.addEventListener('input', () => {
				rangeLabel.innerText = `New Earthquake Magnitude Notification Setting: ${range.value}`;
			});
		</script>
		<script>
			document.addEventListener("DOMContentLoaded", function() {
				var saveChangesLink = document.querySelector('a#saveChangesLink');
				saveChangesLink.addEventListener("click", function(event) {
					event.preventDefault();
					var elements = document.getElementsByClassName("newElement");
					var originalVariables = <?php echo json_encode($usrSettings); ?>;
					var updatedSettings = {};
					var changesCount = 0;
					for (var i = 0; i < elements.length; i++) {
						var element = elements[i];
						var elementName = element.name;
						var elementValue = null;

						if (element.type === "radio") {
							if (element.checked) {
								elementValue = element.value;
								if (elementValue === originalVariables[elementName.replace("new", "")]) {
									elementValue = null;
								}
							} else {
								elementValue = originalVariables[elementName.replace("new", "")];
							}
						} else if (element.type === "range") {
							elementValue = element.value;
							var variableName = elementName.replace("new", "");
							var originalValue = originalVariables[variableName];
							if (elementValue === originalValue.toString()) {
								elementValue = null;
							}
						}else if (element.type === "select") {
							var selectedIndex = element.selectedIndex;
							if (selectedIndex !== -1) {
								elementValue = element.options[selectedIndex].value;
								if (elementValue === originalVariables[elementName.replace("new", "")]) {
								elementValue = null;
								}
							} else {
								elementValue = originalVariables[elementName.replace("new", "")];
							}
						} else if (element.type === "text") {
							// Assuming the input field is a text input, change "text" to the appropriate type if needed
							elementValue = element.value;
							var variableName = elementName.replace("new", "");
							var originalValue = originalVariables[variableName];
							if (elementValue !== null && elementValue.trim() !== "" && elementValue !== originalValue) {
								updatedSettings[variableName] = elementValue;
								changesCount++;

								console.log("Variable '" + variableName + "' changed: New value = " + elementValue);
							}
						} else if (element.type === "checkbox") {
							elementValue = element.checked ? "checked" : "unchecked";
							var variableName = elementName.replace("new", "");
							var originalValue = originalVariables[variableName];

							if (elementValue !== originalValue) {
								updatedSettings[variableName] = elementValue;
								changesCount++;
								console.log("Variable '" + variableName + "' changed: New value = " + elementValue);
							}
						}

						var variableName = elementName.replace("new", "");
						var originalValue = originalVariables[variableName];

						// Display variables being compared
						console.log("Comparing '" + variableName + "' - Element value: " + elementValue + ", Original value: " + originalValue);

						var variableName = elementName.replace("new", "");
						var originalValue = originalVariables[variableName];
						if (elementValue !== null && elementValue.trim() !== "" && elementValue !== originalValue) {
							updatedSettings[variableName] = elementValue;
							changesCount++;
							console.log("Variable '" + variableName + "' changed: New value = " + elementValue);
						}
					}
					console.log("Changes count = " + changesCount);

					if (changesCount === 0) {
						$('#toast-error').toast('show');
					} else {
						var mergedSettings = Object.assign({}, originalVariables, updatedSettings);
						var xhr = new XMLHttpRequest();
						xhr.open("POST", "saveDataFile.php");
						xhr.setRequestHeader("Content-Type", "application/json");
						xhr.onload = function() {
							if (xhr.status === 200) {
								var response = JSON.parse(xhr.responseText);
								console.log(response.status);
								$('#toast-success').toast('show');
							} else {
								console.log("Failed to save changes");
								$('#toast-fail').toast('show');
							}
						};
						xhr.send(JSON.stringify({ updatedSettings: mergedSettings }));
					}
				});
			});
		</script>
		<script>
			function ValidateIPaddress(ip) {
			    const pattern = /^(((1?[1-9]?|10|2[0-4])\d|25[0-5])($|\.(?!$))){4}$/;
				return pattern.test(ip)
			}
			function validateAndAlert(ip) {
				var container = document.getElementById('notificationContainer');
				container.innerHTML = '';
				if (ip === '') {
                	return;
            	}
				if (ValidateIPaddress(ip)) {
					console.log("IP Address valid")
				} else {
					var alertDiv = document.createElement('div');
					alertDiv.classList.add('alert', 'alert-dark', 'mt-3');
					alertDiv.textContent = 'Invalid IP address';
					container.appendChild(alertDiv);
				}
			}
			document.getElementById('newlocalIP').addEventListener('input', function() {
            	var container = document.getElementById('notificationContainer');
            	container.innerHTML = '';
        	});
		</script>
	</body>
</html>
