<?php
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
	<link href="assets/css/vendor.min.css" rel="stylesheet">
	<link href="assets/css/app.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.css" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.Default.css" />
	<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/leaflet.markercluster.js"></script>
	<script src="assets/js/vendor.min.js"></script>
	<script src="assets/js/app.min.js"></script>
	<style>
		.legend {
			background-color: #7a7a7a;
			color: #fff;
			font-size: 0.9em;
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
						<a href="../../index.php" class="menu-link">
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
							<span class="menu-text">Module Information</span>
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
							<div class="d-flex fw-bold small mb-3">
								<span class="flex-grow-1">Server Stats</span>
								<a href="#" data-toggle="card-expand" class="text-inverse text-opacity-50 text-decoration-none"><i class="bi bi-fullscreen"></i></a>
							</div>
							<div class="col-xl-12">
								<div class="card-body d-flex align-items-center text-inverse m-5px bg-inverse bg-opacity-10">
									<div class="flex-fill">
										<pre id="cpuStats"></pre>
										<pre id="memStats"></pre>
										<pre id="taskStats"></pre>
										<pre id="tempStats"></pre>
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

				<!-- BEGIN col-3 -->
				<div class="col-xl-3 col-lg-6">
					<!-- BEGIN card -->
					<div class="card mb-3">
						<!-- BEGIN card-body -->
						<div class="card-body">
							<!-- BEGIN title -->
							<div class="d-flex fw-bold small mb-3">
								<span class="flex-grow-1">BOX Four</span>
								<a href="#" data-toggle="card-expand" class="text-inverse text-opacity-50 text-decoration-none"><i class="bi bi-fullscreen"></i></a>
							</div>
							<!-- END title -->
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
									console.log(JSON.stringify(visitsData, null, 2));
									var countryData = visitsData.data;
									var mapData = {};
									function addMarkersWithClustering(data) {
										var markers = L.markerClusterGroup();
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
													marker.bindPopup(item.name + " (Visits: " + (item.visit_count ? item.visit_count : 0) + ")");
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

									var map = L.map("visitor-map").setView([0, 0], 2);
									L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png").addTo(map);
									var clusterLayer = addMarkersWithClustering(countryData);
									map.addLayer(clusterLayer);
									var legend = L.control({ position: "bottomleft" });
									legend.onAdd = function (map) {
										var div = L.DomUtil.create("div", "legend");
										div.innerHTML += '<h5 style="text-align: center;">Top 10 Countries</h5>';
										visitsData.legend.forEach(function (item) {
											div.innerHTML +=
												'<p style="margin: 0; color: black;"><i style="background:' + getRandomColor() + '; display: inline-block; height: 10px; width: 10px;"></i> ' +
												item.name + " (Visits: " + item.visit_count + ")</p>";
										});
										return div;
									};
									legend.addTo(map);
									function getRandomColor() {
										var letters = "0123456789ABCDEF";
										var color = "#";
										for (var i = 0; i < 6; i++) {
											color += letters[Math.floor(Math.random() * 16)];
										}
										return color;
									}
								});
							</script>
							<div class="card-arrow">
								<div class="card-arrow-top-left"></div>
								<div class="card-arrow-top-right"></div>
								<div class="card-arrow-bottom-left"></div>
								<div class="card-arrow-bottom-right"></div>
							</div>
							<div class="hljs-container">
								<pre><code class="xml hljs language-xml" data-url="assets/data/map/code-1.json"></code></pre>
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
				<!-- END col-6 -->

			</div>
			<!-- END row -->
		</div>
		<!-- END #content -->

		<!-- BEGIN btn-scroll-top -->
		<a href="#" data-toggle="scroll-to-top" class="btn-scroll-top fade"><i class="fa fa-arrow-up"></i></a>
		<!-- END btn-scroll-top -->
	</div>
	<!-- END #app -->
</body>
</html>
