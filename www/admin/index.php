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

session_start();
$inactive = 3600;
if(isset($_SESSION['login_time']) && time() - $_SESSION['login_time'] > $inactive){
    session_unset();
    session_destroy();
}
$_SESSION['login_time'] = time();
require_once './admCommon.php';
# Database updates as needed
$pdo = new PDO('sqlite:./db/dvmAdmin.db3');
if ($pdo) {
    $query = $pdo->query('SELECT dbVer FROM dvmDBver LIMIT 1');

	function executeSqlFile($pdo, $filePath) {
		$sql = file_get_contents($filePath);
		if ($sql === false) {
			throw new Exception("Could not read the SQL file: $filePath");
		}
		$pdo->beginTransaction();
		$statements = array_filter(array_map('trim', explode(';', $sql)));
		foreach ($statements as $statement) {
			if (!empty($statement)) {
				$result = $pdo->exec($statement);
				if ($result === false) {
					$pdo->rollBack();
					$errorInfo = $pdo->errorInfo();
					throw new Exception("Failed to execute SQL statement: $statement Error: " . $errorInfo[2]);
				}
			}
		}
		$pdo->commit();
	}

    if ($query) {
        $row = $query->fetch(PDO::FETCH_ASSOC);
        if ($row && isset($row['dbVer'])) {
            $curDBVer = $row['dbVer'];

            function versionCompare($ver1, $ver2) {
                $a = explode('.', $ver1);
                $b = explode('.', $ver2);
                for ($i = 0; $i < max(count($a), count($b)); $i++) {
                    $valA = $i < count($a) ? (int)$a[$i] : 0;
                    $valB = $i < count($b) ? (int)$b[$i] : 0;
                    if ($valA > $valB) return 1;
                    if ($valA < $valB) return -1;
                }
                return 0;
            }
            $jsonString = file_get_contents('./db/dbVermap.json');
            if ($jsonString === false) {
                echo "Error: Unable to read version mapping file.\n";
                exit;
            }
            $versionMap = json_decode($jsonString, true);
            if ($versionMap === null) {
                echo "Error: Unable to decode version mapping JSON.\n";
                exit;
            }
            $comparisonResult = versionCompare($curDBVer, $locDBVer);
            if ($comparisonResult == 1 && array_key_exists($locDBVer, $versionMap)) {
                $sqlFilePath = $versionMap[$locDBVer];
                if (file_exists($sqlFilePath)) {
                    executeSqlFile($pdo, $sqlFilePath);
                    echo "Database has been updated with the script for version $locDBVer.\n";
                } else {
                    echo "Error: SQL file for version $locDBVer does not exist.\n";
                }
            } else {
                echo "No database update required.\n";
            }
        } else {
            echo "Error: No valid data found in dvmDBver table.\n";
			executeSqlFile($pdo, './db/dbmigration_1_0_1.sql');
        }
    } else {
        echo "Error: Unable to execute query.\n";
    }
} else {
    echo "Error: Unable to connect to the database.\n";
    exit;
}
$logger = new Logger('./db/dvmAdmin.db3', 'dvmAdmLog');
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
	<meta charset="utf-8">
	<title>DivumWX Admin | Login Page</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">
	<link href="assets/css/vendor.min.css" rel="stylesheet">
	<link href="assets/css/app.min.css" rel="stylesheet">
    <style>
		.centered {
			display: flex;
			justify-content: center;
			align-items: center;
			height: 100vh;
			width: 100vw;
			position: absolute;
			top: 0;
			left: 0;
		}
        #loginForm {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
            width: 500px;
            height: 440px;
            position: relative;
        }
		#loginForm .logo-text {
			display: flex;
			align-items: center;
			gap: 20px;
			margin-bottom: 20px;
		}
		#loginForm .text {
			color: #000;
		}
		#loginForm .logo {
			width: 100px;
			height: 100px;
		}
        #loginForm .form-control {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            margin-bottom: 10px;
			color: black;
        }
		.login-input {
            background: #ffffff;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            width: 100%;
		}
        #loginForm .btn {
            background: #007BFF;
            color: #fff;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 5px;
            width: 100%;
        }
		#loginForm label {
			color: #000; /* sets the label color to black */
		}
		.password-container {
			position: relative;
		}
		.password-container i {
			position: absolute;
			right: 10px; /* Adjust as needed */
			top: 50%;
			transform: translateY(-50%);
		}
		#togglePassword {
			color: black;
		}
		.alert-danger {
			color: #721c24;
			background-color: #f8d7da;
			border-color: #f5c6cb;
		}
		.alert {
			position: relative;
			padding: 0.75rem 1.25rem;
			margin-bottom: 1rem;
			border: 1px solid transparent;
			border-radius: 0.25rem;
		}
	</style>
</head>
	<?php $logger->info('DvM Login Page Entered'); ?>
	<body class="theme-blue">
		<div id="app" class="app">
			<div id="header" class="app-header">
				<div class="brand">
					<a href="#" class="brand-logo">
						<span class="brand-img">
							<span class="brand-img-text text-theme">DA</span>
						</span>
						<span class="brand-text">DIVUMWX ADMIN</span>
					</a>
				</div>
			</div>
			<div id="content" class="app-content">
				<div class="centered">
					<form id="loginForm" action="" method="post">
						<div class="logo-text">
							<img src="./assets/img/divumLogo.svg" alt="Logo" class="logo">
							<p class="text">Welcome to your Divum Administration Center. Before proceeding, you must first login with your administrative credentials. After successful login, you will be directed to you dashboard.  This session will be terminated after 5 minutes of inactivity.</p>
						</div>
						<label for="username">Username:</label>
						<input type="text" class="form-control login-input" name="username" id="username" placeholder="Enter your username">
						<label for="password">Password:</label>
						<div class="password-container">
							<input type="password" class="form-control login-input" name="password" id="password" placeholder="Enter your password">
    						<i class="far fa-lg fa-fw me-2 fa-eye-slash" id="togglePassword" style="cursor: pointer;"></i>
						</div>
						<button type="submit" class="btn">Login</button>
						<div id="errorMessage" class="alert alert-danger" role="alert" style="margin-top: 20px; text-align: center; display:none;">
							You've entered the wrong login or password. Please try again in 10 seconds.
						</div>
					</form>
				</div>
			</div>
		</div>
		<script src="assets/js/vendor.min.js"></script>
		<script src="assets/js/app.min.js"></script>
		<script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
		<script>
			$(document).ready(function() {
				$('#loginForm').on('submit', function(event) {
					event.preventDefault();

					$.ajax({
						url: 'admLogin.php',
						type: 'post',
						data: $(this).serialize(),
						success: function(response) {
							if(response == 'Success'){
								window.location = 'dvmDashboard.php';
							} else {
								$('#errorMessage').show();
								setTimeout(function() {
									$('#errorMessage').hide();
									$('#username').val('');
									$('#password').val('');
								}, 10000);
							}
						}
					});
				});
				$('#togglePassword').click(function() {
					const password = $('#password');
					const type = password.attr('type') === 'password' ? 'text' : 'password';
					password.attr('type', type);

					$(this).toggleClass('fa-eye fa-eye-slash');
				});
			});
		</script>
	</body>
</html>
