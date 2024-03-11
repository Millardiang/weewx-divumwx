<?php
###############################################################################################
#       _______   __  ___      ___  ____  ____  ___      ___    __   __  ___  ___  ___        #
#      |"      "\ |" \|"  \    /"  |("  _||_ " ||"  \    /"  |  |"  |/  \|  "||"  \/"  |      #
#      (.  ___  :)||  |\   \  //  / |   (  ) : | \   \  //   |  |'  /    \:  | \   \  /       #
#      |: \   ) |||:  | \\  \/. ./  (:  |  | . ) /\\  \/.    |  |: /'        |  \\  \/        #
#      (| (___\ |||.  |  \.    //    \\ \__/ // |: \.        |   \//  /\'    |  /\.  \        #
#      |:       :)/\  |\  \\   /     /\\ __ //\ |.  \    /:  |   /   /  \\   | /  \   \       #
#      (________/(__\_|_)  \__/     (__________)|___|\__/|___|  |___/    \___||___/\___|      #
#                                                                                             #
#      Copyright (C) 2023 Ian Millard, Steven Sheeley, Sean Balfour. All rights reserved      #
#       Distributed under terms of the GPLv3.  See the file LICENSE.txt for your rights.      #
#     Issues for weewx-divumwx skin template are only addressed via the issues register at    #
#                     https://github.com/Millardiang/weewx-divumwx/issues                     #
###############################################################################################

session_start();
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}
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
	<title>DivumWX | Dashboard</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">
	<link href="assets/css/vendor.min.css" rel="stylesheet" />
	<link href="assets/css/app.min.css" rel="stylesheet" />
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.css" crossorigin="anonymous"/>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.Default.css" crossorigin="anonymous"/>
	<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/leaflet.markercluster.js" crossorigin="anonymous"></script>
	<script src="assets/js/vendor.min.js"></script>
	<script src="assets/js/app.min.js"></script>
	<script src="assets/js/bootstrap-validate.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/apexcharts" crossorigin="anonymous"></script>
	<style>
		.legend {
			background-color: #7a7a7a;
			color: #fff;
			font-size: 0.9em;
		}
        #passwdForm {
            padding: 20px;
            width: 500px;
            position: relative;
        }
		#passwdForm .logo {
			width: 100px;
			height: 100px;
		}
		#passwdForm .logo-text {
			display: flex;
			align-items: center;
			gap: 20px;
			margin-bottom: 20px;
		}
		.form-group {
			display: flex;
			flex-direction: column;
			gap: 5px;
		}
		.label-input-wrapper {
			display: flex;
			flex-direction: column;
		}
		.form-group input.someInput {
			height: 35px;
			width: 300px;
		}
		.wrong .fa-check
		{
			display: none;
		}
		.good .fa-times
		{
			display: none;
		}
		.valid-feedback,.invalid-feedback {
			margin-left: calc(2em + 0.25rem + 1.5rem);
		}
		.custom-modal-width {
			max-width: 80%;
		}
		.custom-modal-content {
			max-height: 400px;
			overflow-y: auto;
			overflow-x: auto;
		}
		#syslogContent {
			max-height: 400px;
			overflow-y: auto;
			overflow-x: auto;
			white-space: pre;
			word-wrap: normal;
		}
		.memSmTxt, .memSmTxt a {
			font-size: 0.9em;
			color: inherit;
		    text-decoration: none;
		}
		.procSmTxt {
			font-size: 0.75em;
			color: inherit;
		    text-decoration: none;
		}
		.procSmlbl {
			font-size: 0.8em;
			color: inherit;
		    text-decoration: none;
		}

		td:nth-child(2), td:nth-child(3) {
			text-align: center;
		}
		#alertSignal {
			display: inline-flex;
			align-items: center;
			vertical-align: middle;
			padding-left: 5px;
			padding-right: 5px;
			padding-bottom: 3px;
		}

	</style>
</head>
<body class="theme-blue">
	<div id="app" class="app">
		<div id="header" class="app-header">
		<div class="desktop-toggler">
			<button type="button" class="menu-toggler" data-toggle-class="app-sidebar-collapsed" data-dismiss-class="app-sidebar-toggled" data-toggle-target=".app">
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
				<span class="brand-text">Admin Dashboard</span>
			</a>
		</div>
		<div class="menu"></div>
			<?php displaySidebar('home'); ?>
		<div id="content" class="app-content">
			<div class="row"><!-- Row 1 -->
				<div class="col-lg-6"><!-- Device Info -->
					<div class="card mb-3">
						<div class="card-body">
							<div class="row">
								<div class="col-xl-6 col-lg-6">
									<div class="card border-theme bg-opacity-25 mb-4">
										<div class="card-body">
												<!-- CPU Usage Chart -->
												<div id="cpuUsageChart"></div>
											</div>
											<div class="card-arrow">
												<div class="card-arrow-top-left"></div>
												<div class="card-arrow-top-right"></div>
												<div class="card-arrow-bottom-left"></div>
												<div class="card-arrow-bottom-right"></div>
											</div>
										</div>
										<div class="card border-theme bg-opacity-25 mb-3">
											<div class="card-body">
												<!-- Memory Usage Chart-->
												<div id="memUsageChart"></div>
											<div class="card-arrow">
												<div class="card-arrow-top-left"></div>
												<div class="card-arrow-top-right"></div>
												<div class="card-arrow-bottom-left"></div>
												<div class="card-arrow-bottom-right"></div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-xl-6 col-lg-6">
									<div class="card border-theme bg-opacity-25 mb-4">
										<div class="card-body">
											<div class="card border-theme bg-opacity-25 mb-2">
												<div class="card-header border-theme fw-bold small text-inverse"><span id="dispLogs">Top 10 Running Processes</span></div>
												<div class="card-body">
													<!-- Processes -->
												</div>
												<div class="card-arrow">
													<div class="card-arrow-top-left"></div>
													<div class="card-arrow-top-right"></div>
													<div class="card-arrow-bottom-left"></div>
													<div class="card-arrow-bottom-right"></div>
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
						</div>
						<div class="card-arrow">
							<div class="card-arrow-top-left"></div>
							<div class="card-arrow-top-right"></div>
							<div class="card-arrow-bottom-left"></div>
							<div class="card-arrow-bottom-right"></div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-lg-6"><!-- Log Files Box-->
					<div class="card mb-3">
						<div class="card-body">
							<div class="card border-theme bg-opacity-25 mb-3">
								<div class="card-header border-theme fw-bold small text-inverse"><span id="dispLogs">Log Files - Click to View</span></div>
								<div class="card-body">
									<div class="memSmTxt"><a href="#" id="btnDisplaywebSrvrlog">Web Server Log</a></div>
									<div class="memSmTxt"><a href="#" id="btnDisplaywebSrvrlog">Web Server Error Log</a></div>
									<div class="memSmTxt"><a href="#" id="btnDisplayPHPlog">PHP Log</a></div>
									<div class="memSmTxt"><a href="#" id="btnDisplaySyslog">Display Syslog</a></div>
								</div>
								<div class="card-arrow">
									<div class="card-arrow-top-left"></div>
									<div class="card-arrow-top-right"></div>
									<div class="card-arrow-bottom-left"></div>
									<div class="card-arrow-bottom-right"></div>
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
				<div class="col-xl-3 col-lg-6"><!-- Software Info Box-->
					<?php
						$fileContents = file_get_contents('../dvmVersion.php');
						$pattern = '/<maxblue>(.*?)<\/maxblue>/';
						if (preg_match($pattern, $fileContents, $matches)) {
							$locDvmVer = rtrim($matches[1], "-");
						} else {
							$locDvmVer = "Version not found";
						}
					?>
					<div class="card mb-3">
						<div class="card-body">
							<div class="col">
								<div class="card border-theme bg-opacity-25 mb-3">
									<div class="card-header border-theme fw-bold small text-inverse">Software Info</div>
									<div class="card-body">
										<div>
										<div><span class="text-white" id="locDvmVer">DivumWX Version: <?php echo $locDvmVer; ?></span><span class="text-white" id="remDvmVer"> | Latest Release: Loading......</span>&nbsp;&nbsp;<span class="text-info" id="dvmAlertSignal"></span></div>
										<div><span class="text-White" id="locWeewxVer">Weewx Version: </span><span class="text-white" id="remWeewxVer"> | Latest Release: Loading......</span>&nbsp;&nbsp;<span class="text-info" id="wewxAlertSignal"></span></div>
										<hr class="hr hr-blurry" />
										<div class="memSmTxt"><span class="text-info" id="osSystem">Operating System: </span></div>
										<div class="memSmTxt"><span class="text-info" id="upTime">Server Uptime: </span></div>
										<div class="memSmTxt"><span class="text-info" id="webSrvr">Web Server: </span></div>
										<div class="memSmTxt"><span class="text-info" id="phpVer">PHP Version: </span></div>
										<div class="memSmTxt"><span class="text-info" id="pyVer">Python Version: </span></div>
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
						<div class="card-arrow">
							<div class="card-arrow-top-left"></div>
							<div class="card-arrow-top-right"></div>
							<div class="card-arrow-bottom-left"></div>
							<div class="card-arrow-bottom-right"></div>
						</div>
					</div>
				</div>
				</div>
			</div>
			<div class="row"><!-- Row 2 -->
				<div class="col-xl-6"><!-- Site Visitors -->
					<div class="card mb-3">
						<div class="card-body">
							<div class="d-flex fw-bold small mb-3">
								<span class="flex-grow-1">Site Visitors</span>
							</div>
							<div id="visitor-map" style="height: 600px"></div>
							<script>
								fetch("./mapData.php")
								.then(response => response.json())
								.then(visitsData => {
									totalVisits = visitsData.total_visits;
									var countryData = visitsData.data;
									document.getElementById("total-visits").textContent = totalVisits;
									var mapData = {};
									function addMarkersWithClustering(data) {
										var markers = L.markerClusterGroup({
											showCoverageOnHover: false
										});
										for (var key in data) {
											if (data.hasOwnProperty(key)) {
												var item = data[key];
												if (item.latLng && Array.isArray(item.latLng) && item.latLng.length === 2) {
													var customIcon = L.icon({
														iconUrl: 'assets/img/bd.png',
														iconSize: [30, 30],
														iconAnchor: [16, 16],
													});
													var marker = L.marker(item.latLng, { icon: customIcon });
													marker.on('mouseover', function(e) {
														this.openPopup();
													});
													marker.on('mouseout', function(e) {
														this.closePopup();
													});
													marker.bindPopup(item.name + " (" + (item.visit_count ? item.visit_count : 0) + ")");
													markers.addLayer(marker);
												}
												if (item.regions) {
													markers.addLayer(addMarkersWithClustering(item.regions));
												}
												if (item.cities) {
													markers.addLayer(addMarkersWithClustering(item.cities));
												}
											}
										}
										return markers;
									}

									var map = L.map("visitor-map", {
										center: [0, 0],
										zoom: 2
									});
									L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png").addTo(map);
									var clusterLayer = addMarkersWithClustering(countryData);
									map.addLayer(clusterLayer);
								});
							</script>
							<div class="card-arrow">
								<div class="card-arrow-top-left"></div>
								<div class="card-arrow-top-right"></div>
								<div class="card-arrow-bottom-left"></div>
								<div class="card-arrow-bottom-right"></div>
							</div>
							<div class="hljs-container">
								<div>Total Site Visits: <span id="total-visits"></span></div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-6"><!-- Traffic Analytics -->
					<div class="card mb-3">
						<div class="card-body">
							<div class="d-flex fw-bold small mb-3">
								<span class="flex-grow-1">TRAFFIC ANALYTICS</span>
							</div>
							<div class="ratio ratio-21x9 mb-3">

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
			DivumWX Admin Dashboard - v<?php echo $admVersion;?>
		</div>
		<!-- Toasts -->
		<div class="toasts-container" role="alert" aria-live="assertive" aria-atomic="true">
			<div class="toast fade mb-3 hide" data-autohide="false" data-bs-delay="5000" id="success">
					<div class="toast-header">
						<i class="fas fa-info-circle text-muted me-2"></i>
						<strong class="me-auto">Success!</strong>
						<button type="button" class="btn-close" data-bs-dismiss="toast"></button>
					</div>
					<div class="toast-body">
						Your password has been updated successfully.
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade" id="sysLogmodal">
			<div class="modal-dialog modal-xl">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Current sysLog</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
					</div>
					<div class="modal-body">
						<div class="col-mb-6">
							<span class="close">&times;</span>
							<pre id="syslogContent">Syslog content will be displayed here...</pre>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-outline-default" data-bs-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
		<!-- Change Password Modal  -->
		<div class="modal fade" id="updateDefaultPassword" data-bs-backdrop="static" data-bs-keyboard="false">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Change Default Password</h5>
					</div>
					<div class="modal-body">
						<div class="col-mb-6">
							<form id="passwdForm">
								<div class="logo-text">
									<img src="./assets/img/divumLogo.svg" alt="Logo" class="logo">
									<p class="text">This appears to be your first login to the DvM Dashboard, which indicates that you used the default password to login. <span class="text-warning">Retaining the default password is a huge security risk.</span> You must use the form below to change the password to your DvM Dashboard now.</p>
								</div>
								<div class="form-group justify-content-end">
									<label for="newPassword">New Password:</label>
									<input type="password" class="form-control someInput" name="newPassword" id="newPassword" placeholder="Enter new password"  autocomplete="off" required>
									<div  class="alert px-4 py-3 mb-0 d-none" role="alert" data-mdb-color="warning" id="password-alert">
										<ul class="list-unstyled mb-0">
											<li class="requirements leng">
											<i class="fas fa-check text-success me-2"></i>
											<i class="fas fa-times text-danger me-3"></i>
											Your password must have at least 12 chars</li>
											<li class="requirements big-letter">
											<i class="fas fa-check text-success me-2"></i>
											<i class="fas fa-times text-danger me-3"></i>
											Your password must have at least 1 Uppercase letter.</li>
											<li class="requirements num">
											<i class="fas fa-check text-success me-2"></i>
											<i class="fas fa-times text-danger me-3"></i>
											Your password must have at least 1 number.</li>
											<li class="requirements special-char">
											<i class="fas fa-check text-success me-2"></i>
											<i class="fas fa-times text-danger me-3"></i>
											Your password must have at least 1 special char.</li>
										</ul>
									</div>
								</div>
								<div class="form-group justify-content-end">
									<label for="verifyPassword">Verify Password:</label>
									<input type="password" class="form-control someInput" name="verifyPassword" id="verifyPassword" placeholder="Verify new password" autocomplete="off">
								</div>
								<input type="hidden" name="username" value="<?php echo $_SESSION['username']; ?>">
								<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>"><br />
								<div class="text-md-center">
									<button type="submit" id="submitBtn" disabled>Submit</button>
								</div>
							</form>
							<div class="alert alert-danger mt-3 d-none" role="alert" id="password-error"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- Display System News Modal  -->
		<div class="modal fade" id="displaysysNewsModal" data-bs-backdrop="static" data-bs-keyboard="false">
			<div class="modal-dialog modal-xl">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">System News</h4>
					</div>
					<div class="modal-body">
						<div class="col-mb-6">
							<pre id="sysNewsContent" class="custom-modal-content">Syslog content will be displayed here...</pre>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- Display Syslog Modal  -->
		<div class="modal fade" id="displaysysLogModal" data-bs-backdrop="static" data-bs-keyboard="false">
			<div class="modal-dialog modal-xl">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">Current sysLog</h4>
					</div>
					<div class="modal-body">
						<div class="col-mb-6">
							<pre id="syslogContent" class="custom-modal-content">Syslog content will be displayed here...</pre>
						</div>
					</div>
				</div>
			</div>
		</div>
		<a href="#" data-toggle="scroll-to-top" class="btn-scroll-top fade"><i class="fa fa-arrow-up"></i></a>
	</div>
	<!--Force password change on use of default password-->
	<?php
		if ($_SESSION['initialLogin'] == 0){
			echo '<script>
				$(document).ready(function() {
					$("#updateDefaultPassword").modal("show");
				});
			</script>';
		};
	?>
	<script>// New Password change script
		addEventListener("DOMContentLoaded", (event) => {
			const passwordAlert = document.getElementById("password-alert");
			const requirements = document.querySelectorAll(".requirements");
			let lengBoolean, bigLetterBoolean, numBoolean, specialCharBoolean;
			let leng = document.querySelector(".leng");
			let bigLetter = document.querySelector(".big-letter");
			let num = document.querySelector(".num");
			let specialChar = document.querySelector(".special-char");
			const specialChars = "!@#$%^&*()-_=+[{]}\\|;:'\",<.>/?`~";
			const numbers = "0123456789";
			const newPassword = document.getElementById("newPassword");
			const verifyPassword = document.getElementById("verifyPassword");
			const submitBtn = document.getElementById("submitBtn");

			requirements.forEach((element) => element.classList.add("wrong"));

			newPassword.addEventListener("focus", () => {
				passwordAlert.classList.remove("d-none");
				if (!newPassword.classList.contains("is-valid")) {
					newPassword.classList.add("is-invalid");
				}
			});

			newPassword.addEventListener("input", () => {
				let value = newPassword.value;
				if (value.length < 12) {
					lengBoolean = false;
				} else if (value.length > 11) {
					lengBoolean = true;
				}

				if (value.toLowerCase() == value) {
					bigLetterBoolean = false;
				} else {
					bigLetterBoolean = true;
				}

				numBoolean = false;
				for (let i = 0; i < value.length; i++) {
					for (let j = 0; j < numbers.length; j++) {
						if (value[i] == numbers[j]) {
							numBoolean = true;
						}
					}
				}

				specialCharBoolean = false;
				for (let i = 0; i < value.length; i++) {
					for (let j = 0; j < specialChars.length; j++) {
						if (value[i] == specialChars[j]) {
							specialCharBoolean = true;
						}
					}
				}

				if (lengBoolean == true && bigLetterBoolean == true && numBoolean == true && specialCharBoolean == true) {
					newPassword.classList.remove("is-invalid");
					newPassword.classList.add("is-valid");

					requirements.forEach((element) => {
						element.classList.remove("wrong");
						element.classList.add("good");
					});
					passwordAlert.classList.remove("alert-warning");
					passwordAlert.classList.add("alert-success");
				} else {
					newPassword.classList.remove("is-valid");
					newPassword.classList.add("is-invalid");

					passwordAlert.classList.add("alert-warning");
					passwordAlert.classList.remove("alert-success");

					if (lengBoolean == false) {
						leng.classList.add("wrong");
						leng.classList.remove("good");
					} else {
						leng.classList.add("good");
						leng.classList.remove("wrong");
					}

					if (bigLetterBoolean == false) {
						bigLetter.classList.add("wrong");
						bigLetter.classList.remove("good");
					} else {
						bigLetter.classList.add("good");
						bigLetter.classList.remove("wrong");
					}

					if (numBoolean == false) {
						num.classList.add("wrong");
						num.classList.remove("good");
					} else {
						num.classList.add("good");
						num.classList.remove("wrong");
					}

					if (specialCharBoolean == false) {
						specialChar.classList.add("wrong");
						specialChar.classList.remove("good");
					} else {
						specialChar.classList.add("good");
						specialChar.classList.remove("wrong");
					}
				}
			});

			verifyPassword.addEventListener("focus", () => {
				if (verifyPassword.value === newPassword.value) {
					verifyPassword.classList.add("is-valid");
					verifyPassword.classList.remove("is-invalid");
					submitBtn.disabled = false;
				} else {
					verifyPassword.classList.remove("is-valid");
					verifyPassword.classList.add("is-invalid");
					submitBtn.disabled = true;
				}
			});

			verifyPassword.addEventListener("input", () => {
				if (verifyPassword.value === newPassword.value) {
					verifyPassword.classList.add("is-valid");
					verifyPassword.classList.remove("is-invalid");
					submitBtn.disabled = false;
				} else {
					verifyPassword.classList.remove("is-valid");
					verifyPassword.classList.add("is-invalid");
					submitBtn.disabled = true;
				}
			});

			newPassword.addEventListener("blur", () => {
				passwordAlert.classList.add("d-none");
			});
		});
	</script>
	<script>// New Password change form
		$(document).ready(function() {
			$("#passwdForm").submit(function(event) {
				event.preventDefault();
				var formData = $(this).serialize();
				$.ajax({
					type: "POST",
					url: "updatePwd.php",
					data: formData,
					success: function(response) {
						if (response.includes("successfully")) {
							$("#updateDefaultPassword").modal("hide");
							$("#success").toast("show");
						} else {
							$("#password-error").text(response);
						}
					},
					error: function(jqXHR, textStatus, errorThrown) {
						$("#password-error").text("An error occurred while updating the password.");
						console.error(errorThrown);
					}
				});
			});
		});
	</script>
	<script>//Display log files
	$(document).ready(function(){
		$('.memSmTxt a').on('click', function(event) {
			event.preventDefault();
			var linkId = $(this).attr('id');
			switch(linkId) {
				case 'btnDisplaySyslog':
					fetchSyslog();
					$('#sysLogmodal').modal('show');
					break;
				// Add cases for other links with their respective IDs and functions
				default:
					console.log('Link clicked with no specific action defined.');
			}
		});

		function fetchSyslog() {
			$.ajax({
				url: 'dispSysLog.php',
				method: 'GET',
				success: function(data) {
					$('#syslogContent').text(data);
				},
				error: function() {
					$('#syslogContent').text('Error fetching syslog.');
				}
			});
		}
	});
	</script>
	<script>
		 var locDvmVersion = "<?php echo $locDvmVer; ?>";
	</script>
	<script>//Fetch Software versions
		let locWeewxVersion = "";
		document.addEventListener('DOMContentLoaded', (event) => {
			getSoftwareVersions();
		});

		function getSoftwareVersions() {
			var xhr = new XMLHttpRequest();
			xhr.onreadystatechange = function() {
				if (xhr.readyState === XMLHttpRequest.DONE) {
					if (xhr.status === 200) {
						var data = JSON.parse(xhr.responseText);
						document.getElementById("osSystem").textContent = "Operating System: " + data.osSystem;
						document.getElementById("upTime").textContent = "System Uptime: " + data.sysUptime;
						document.getElementById("webSrvr").textContent = "Web Server: " + data.webserver_name + " (" + data.webserver_version + ")";
						document.getElementById("phpVer").textContent = "PHP Version; " + data.php_version;
						document.getElementById("pyVer").textContent = "Python Version: " + data.python_version;
						document.getElementById("locWeewxVer").textContent = "WeeWX Version: " + data.weewx_version;
						locWeewxVersion = data.weewx_version;
						LatestReleaseVersion(data.divumRemote, data.weewxRemote)
					} else {
						console.error("Error fetching software versions: " + xhr.statusText);
					}
				}
			};
			xhr.open("GET", "./softVer.php", true);
			xhr.send();
		}

		function compareVersions(v1, v2) {
			const versionArray1 = v1.split('.').map(Number);
			const versionArray2 = v2.split('.').map(Number);

			for (let i = 0; i < Math.max(versionArray1.length, versionArray2.length); i++) {
				const num1 = versionArray1[i] || 0;
				const num2 = versionArray2[i] || 0;
				if (num1 < num2) return -1;
				if (num1 > num2) return 1;
			}
			return 0;
		}

		function LatestReleaseVersion(divumRemote, weewxRemote) {
			document.getElementById('remDvmVer').textContent = " | Latest Released: " + divumRemote;
			document.getElementById('remWeewxVer').textContent = " | Latest Released: " + weewxRemote;

			const comparisonResult = compareVersions(locDvmVersion, divumRemote);
			if (comparisonResult === 0) {
				document.getElementById('dvmAlertSignal').innerHTML = '<i class="fa fa-circle fs-10px fa-fw text-success me-1"></i>';
			} else if (comparisonResult < 0) {
				document.getElementById('dvmAlertSignal').innerHTML = '<i class="fa fa-circle fs-10px fa-fw text-danger me-1"></i>';
			} else {
				document.getElementById('dvmAlertSignal').innerHTML = '<i class="fa fa-circle fs-10px fa-fw text-muted me-1"></i>';
			}
			const comparisonResult2 = compareVersions(locWeewxVersion, weewxRemote);
			if (comparisonResult2 === 0) {
				document.getElementById('wewxAlertSignal').innerHTML = '<i class="fa fa-circle fs-10px fa-fw text-success me-1"></i>';
			} else if (comparisonResult < 0) {
				document.getElementById('wewxAlertSignal').innerHTML = '<i class="fa fa-circle fs-10px fa-fw text-danger me-1"></i>';
			} else {
				document.getElementById('wewxAlertSignal').innerHTML = '<i class="fa fa-circle fs-10px fa-fw text-muted me-1"></i>';
			}
		}

	</script>
	<script>// Initialize the CPU Chart as a Radial Bar Chart
		var options = {
			series: [44, 55, 67], // Initial dummy values, replace with actual data
			chart: {
				id: 'cpuUsageChart',
				height: 175,
				type: 'radialBar',
				animations: {
					enabled: true,
					easing: 'linear',
					dynamicAnimation: {
						speed: 1000
					}
				},
				toolbar: {
					show: false
				},
			},
			plotOptions: {
				radialBar: {
					hollow: {
						size: '40%',
					},
					track: {
						background: '#f2f2f2',
						strokeWidth: '97%',
					},
					dataLabels: {
						name: {
							show: true,
						},
						value: {
							show: true,
						}
					}
				}
			},
			labels: ['us', 'sy', 'id'],
			legend: {
				show: true,
				floating: true,
				position: 'left',
				offsetX: 0,
				offsetY: 0,
				labels: {
					useSeriesColors: true,
				},
				markers: {
					size: 0
				},
				formatter: function(seriesName, opts) {
					return seriesName + ": " + opts.w.globals.series[opts.seriesIndex] + '%';
				},
				itemMargin: {
					vertical: 3
				}
			},
		};

		var chart = new ApexCharts(document.querySelector("#cpuUsageChart"), options);
		chart.render();
	</script>
	<script>// Initialize the Memory Chart as a Radial Bar Chart
		var memoryOptions = {
			series: [0, 0],
			chart: {
				id: 'memUsageChart',
				height: 175,
				type: 'radialBar',
				animations: {
					enabled: true,
					easing: 'linear',
					dynamicAnimation: {
						speed: 1000
					}
				},
				toolbar: {
					show: false
				},
			},
			plotOptions: {
				radialBar: {
					hollow: {
						size: '40%',
					},
					track: {
						background: '#f2f2f2',
						strokeWidth: '97%',
					},
					dataLabels: {
						name: {
							show: true,
						},
						value: {
							show: true,
						}
					}
				}
			},
			labels: ['Used', 'Free'],
			legend: {
				show: true,
				position: 'bottom',
				horizontalAlign: 'center',
				fontSize: '14px',
				fontFamily: 'Helvetica, Arial',
				fontWeight: 400,
				formatter: function(seriesName, opts) {
					return seriesName + ": " + opts.w.globals.series[opts.seriesIndex] + '%';
				},
				itemMargin: {
					vertical: 5
				}
			},
		};

		var memoryChart = new ApexCharts(document.querySelector("#memUsageChart"), memoryOptions);
		memoryChart.render();
	</script>
	<script>// Fetch server stats and update the charts
		$(document).ready(function() {
			function fetchServerStats() {
				$.ajax({
					url: './srvStats.php',
					type: 'GET',
					dataType: 'json',
					success: function(data) {
						chart.updateSeries([
							data.cpu.us,
							data.cpu.sy,
							data.cpu.id
						]);

						// Calculate and update memory usage percentages
						let totalMemory = parseFloat(data.memory.total);
						let usedMemory = parseFloat(data.memory.used);
						let freeMemory = parseFloat(data.memory.free);
						let usedPercentage = (usedMemory / totalMemory) * 100;
						let freePercentage = (freeMemory / totalMemory) * 100;

						// Update Memory chart
						memoryChart.updateSeries([
							usedPercentage.toFixed(1),
							freePercentage.toFixed(1)
						]);
					},
					error: function(xhr, status, error) {
						console.error('Error:', status, error);
					}
				});
			}
			setInterval(fetchServerStats, 1500); // Fetch every 1500 milliseconds
		});
	</script>
</body>
</html>