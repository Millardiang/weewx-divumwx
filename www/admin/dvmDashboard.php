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
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.css" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.Default.css" />
	<link href="assets/css/full.min.css" rel="stylesheet" />
	<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/leaflet.markercluster.js"></script>
	<script src="assets/js/vendor.min.js"></script>
	<script src="assets/js/app.min.js"></script>
	<script src="assets/js/bootstrap-validate.js"></script>
	<script src="assets/js/statsUpdate.js"></script>
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
		.memSmTxt {
			font-size: 0.8em; /* You can adjust the em value as needed */
		}
		.mem-label {
			display: inline-block;
			width: 90px; /* Adjust this width as needed */
			font-weight: bold;
		}
		.mem-value {
			display: inline-block;
			width: 70px; /* Adjust this width as needed */
		}

	</style>
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
					<span class="brand-text">DivumWX Admin Dashboard</span>
				</a>
			</div>
			<div class="menu"></div>
		</div>
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
						<a href="./dvmDashboard.php" class="menu-link">
							<span class="menu-icon"><i class="bi bi-house-door"></i></span>
							<span class="menu-text">Home</span>
						</a>
					</div>
					<div class="menu-item">
						<a href="./dvmFilemanager.php" class="menu-link">
							<span class="menu-icon"><i class="bi bi-folder"></i></span>
							<span class="menu-text">File Manager</span>
						</a>
					</div>
					<div class="menu-item">
						<a href="./dvmSettings.php" class="menu-link">
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
						<a href="./dvmDashboard.php?logout=true" class="menu-link">
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
		<!-- BEGIN #content -->
		<div id="content" class="app-content">
			<!-- BEGIN row -->
			<div class="row">
				<!-- BEGIN col-3 -->
				<div class="col-xl-3 col-lg-6">
					<!-- BEGIN card -->
					<div class="card mb-3">
						<!-- BEGIN card-body -->
						<div class="card-body">
							<div class="row">
								<div class="col-xl-6">
									<div class="small text-inverse text-opacity-50 mb-3"><b class="fw-bold">CPU Stats</b></div>
									<div class="card border-theme bg-theme bg-opacity-25 mb-3">
										<div class="card-header border-theme fw-bold small text-inverse"><span id="cpuUpdate">Updated: </span></div>
										<div class="card-body">
											<div class="memSmTxt" id="cpuContainer"></div>
										</div>
										<div class="card-arrow">
											<div class="card-arrow-top-left"></div>
											<div class="card-arrow-top-right"></div>
											<div class="card-arrow-bottom-left"></div>
											<div class="card-arrow-bottom-right"></div>
										</div>
									</div>
								</div>
								<div class="col-xl-6">
									<div class="small text-inverse text-opacity-50 mb-3"><b class="fw-bold">Memory Stats</b></div>
									<div class="card border-theme bg-theme bg-opacity-25 mb-3">
										<div class="card-header border-theme fw-bold small text-inverse"><span id="memUpdate">Updated: </span></div>
										<div class="card-body">
											<div class="memSmTxt" id="memContainer"></div>
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
						<div class="hljs-container">
							<pre><code class="xml hljs language-xml" data-url="assets/data/ui-card/code-7.json"></code></pre>
						</div>
					</div>
					<!-- END card -->
				</div>
				<!-- END col-3 -->

				<!-- BEGIN col-3 -->
				<div class="col-xl-3 col-lg-6">
					<!-- BEGIN card -->
					<div class="card mb-3">
						<!-- BEGIN card-body -->
						<div class="card-body">
							<!-- BEGIN title -->
							<div class="d-flex fw-bold small mb-3">
								<span class="flex-grow-1">BOX Two</span>
								<a href="#" data-toggle="card-expand" class="text-inverse text-opacity-50 text-decoration-none"><i class="bi bi-fullscreen"></i></a>
							</div>
							<!-- END title -->
							<div id="memoryChart" style="height: 200px;"></div>
						</div>
						<!-- END card-body -->

						<!-- BEGIN card-arrow -->
						<div class="card-arrow">
							<div class="card-arrow-top-left"></div>
							<div class="card-arrow-top-right"></div>
							<div class="card-arrow-bottom-left"></div>
							<div class="card-arrow-bottom-right"></div>
						</div>
						<!-- END card-arrow -->
					</div>
					<!-- END card -->
				</div>
				<!-- END col-3 -->

				<!-- BEGIN col-3 -->
				<div class="col-xl-3 col-lg-6">
					<!-- BEGIN card -->
					<div class="card mb-3">
						<!-- BEGIN card-body -->
						<div class="card-body">
							<!-- BEGIN title -->
							<div class="d-flex fw-bold small mb-3">
								<span class="flex-grow-1">BOX Three</span>
								<a href="#" data-toggle="card-expand" class="text-inverse text-opacity-50 text-decoration-none"><i class="bi bi-fullscreen"></i></a>
							</div>
							<!-- END title -->
							<div id="otherStats" style="height: 200px;"></div>
						</div>
						<!-- END card-body -->

						<!-- BEGIN card-arrow -->
						<div class="card-arrow">
							<div class="card-arrow-top-left"></div>
							<div class="card-arrow-top-right"></div>
							<div class="card-arrow-bottom-left"></div>
							<div class="card-arrow-bottom-right"></div>
						</div>
						<!-- END card-arrow -->
					</div>
					<!-- END card -->
				</div>
				<!-- END col-3 -->
				<?php
					$fileContents = file_get_contents('../dvmVersion.php');
					$pattern = '/<maxblue>(.*?)<\/maxblue>/';
					if (preg_match($pattern, $fileContents, $matches)) {
						$curVersion = $matches[1];
					} else {
						$curVersion = "Version not found";
					}
				?>
				<!-- BEGIN col-3 -->
				<div class="col-xl-3 col-lg-6">
					<!-- BEGIN card -->
					<div class="card mb-3">
						<!-- BEGIN card-body -->
						<div class="card-body">
							<div class="col">
								<div class="card border-theme bg-theme bg-opacity-25 mb-3">
									<div class="card-header border-theme fw-bold small text-inverse">System Info</div>
									<div class="card-body">
										<div class="d-flex align-items-center">Current Divum Version: <?php echo $curVersion; ?>&nbsp;&nbsp;<i class="fa fa-circle fs-8px fa-fw text-success me-1"></i></div>
										<div>Current Released Version: 0.6.6</div>
										<div class="memSmTxt" id="osSystem"></div>
										<div class="memSmTxt" id="upTime"></div>
										<div class="memSmTxt" id="rebootTime"></div>
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
						<!-- END card-arrow -->
					</div>
					<!-- END card -->
				</div>
				<!-- END col-3 -->

				<!-- BEGIN col-6 -->
				<div class="col-xl-6">
					<div class="card mb-3">
						<div class="card-body">
							<div class="d-flex fw-bold small mb-3">
								<span class="flex-grow-1">Site Visitors</span>
								<a href="#" data-toggle="card-expand" class="text-inverse text-opacity-50 text-decoration-none"><i class="bi bi-fullscreen"></i></a>
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
				<!-- END col-6 -->

				<!-- BEGIN col-6 -->
				<div class="col-xl-6">
					<!-- BEGIN card -->
					<div class="card mb-3">
						<!-- BEGIN card-body -->
						<div class="card-body">
							<!-- BEGIN title -->
							<div class="d-flex fw-bold small mb-3">
								<span class="flex-grow-1">TRAFFIC ANALYTICS</span>
								<a href="#" data-toggle="card-expand" class="text-inverse text-opacity-50 text-decoration-none"><i class="bi bi-fullscreen"></i></a>
							</div>
							<!-- END title -->
							<!-- BEGIN map -->
							<div class="ratio ratio-21x9 mb-3">
								<div id="world-map" class="jvectormap-without-padding"></div>
							</div>
							<!-- END map -->
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
		<a href="#" data-toggle="scroll-to-top" class="btn-scroll-top fade"><i class="fa fa-arrow-up"></i></a>
	</div>
	<?php
		if ($_SESSION['initialLogin'] == 0){
			echo '<script>
				$(document).ready(function() {
					$("#updateDefaultPassword").modal("show");
				});
			</script>';
		};
	?>
	<script>
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
	<script>
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
</body>
</html>