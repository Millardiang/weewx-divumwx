<?php
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
$_SESSION['login_time'] = time();
require_once './admCommon.php';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
	<meta charset="utf-8">
	<title>DvM | template</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">
	<link href="assets/css/vendor.min.css" rel="stylesheet">
	<link href="assets/css/app.min.css" rel="stylesheet">
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
					<span class="brand-text">Divum template</span>
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
							<span class="menu-text">Module Information</span>
						</a>
					</div>
					<div class="menu-item">
						<a href="./dvmFileManager.php?logout=true" class="menu-link">
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
								<span class="flex-grow-1">BOX One</span>
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
					<!-- BEGIN card -->
					<div class="card mb-3">
						<!-- BEGIN card-body -->
						<div class="card-body">
							<!-- BEGIN title -->
							<div class="d-flex fw-bold small mb-3">
								<span class="flex-grow-1">SERVER STATS</span>
								<a href="#" data-toggle="card-expand" class="text-inverse text-opacity-50 text-decoration-none"><i class="bi bi-fullscreen"></i></a>
							</div>
							<!-- END title -->
							<!-- BEGIN chart -->
							<div class="ratio ratio-21x9 mb-3">
								<div id="chart-server"></div>
							</div>
							<!-- END chart -->
							<!-- BEGIN row -->
							<div class="row">
								<!-- BEGIN col-6 -->
								<div class="col-lg-6 mb-3 mb-lg-0">
									<div class="d-flex align-items-center">
										<!-- BEGIN chart -->
										<div class="w-50px h-50px">
											<div data-render="apexchart" data-type="donut" data-title="Visitors" data-height="50"></div>
										</div>
										<!-- END chart -->
									</div>
								</div>
								<!-- END col-6 -->
								<!-- BEGIN col-6 -->
								<div class="col-lg-6">
									<div class="d-flex">
										<!-- BEGIN chart -->
										<div class="w-50px pt-3">
											<div data-render="apexchart" data-type="donut" data-title="Visitors" data-height="50"></div>
										</div>
										<!-- END chart -->
									</div>
								</div>
								<!-- END col-6 -->
							</div>
							<!-- END row -->
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
	<script src="assets/js/vendor.min.js"></script>
	<script src="assets/js/app.min.js"></script>

</body>
</html>
