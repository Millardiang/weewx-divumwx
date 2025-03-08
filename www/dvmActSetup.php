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
$_SESSION['setup_allowed'] = true;
error_log("setup_allowed set in session");

if (!isset($_SESSION['canAccessSetup']) || $_SESSION['canAccessSetup'] !== true) {
    die('Unauthorized access detected. This incident will be reported.');
}
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(length: 32));
}
echo "<script>const csrfToken = '" . $_SESSION['csrf_token'] . "';</script>";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DivumWX - Initial Setup</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        body, html {
            font-family: 'Arial', sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
        }
        .background-container {
            background-image: url('./img/dvmActbg.png');
            background-position: center top;
            background-repeat: no-repeat;
            background-size: cover;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }
        .content {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            text-align: center;
            max-width: 800px;
            width: 100%;
            padding: 20px;
            background-color: #E1EFF6;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            z-index: 1;
        }
        header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            align-items: center;
        }
        img.logo {
            width: 150px;
            height: 150px;
            margin-right: 100px;
        }
        .banner {
            text-align: left;
            flex-grow: 1;
        }
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ccc;
        }
        .tab-button {
            padding: 10px 20px;
            background-color: #ccc;
            border: 1px solid #ccc;
            border-bottom: none;
            cursor: default;
            border-radius: 8px 8px 0 0;
            margin-right: 2px;
        }
        .tab-button.active {
            background-color: #488FCC;
            color: white;
        }
        .tab {
            display: none;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 0 8px 8px 8px;
        }
        .tab.active {
            display: block;
        }
        .status-container {
            width: 100%;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-top: 20px;
        }
        .status-header {
            background-color: #FFA500;
            color: white;
            text-align: center;
            padding: 15px;
            font-size: 18px;
            font-weight: bold;
        }
        .status-header.success {
            background-color: #4CAF50;
        }
        .status-header.error {
            background-color: #FF0000;
        }
        .status-list {
            padding: 15px;
        }
        .status-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .status-item:last-child {
            border-bottom: none;
        }
        .status-label {
            font-size: 16px;
            color: #333;
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        .status-label .icon {
            margin-right: 10px;
        }
        .status-state {
            font-size: 16px;
            color: #FFA500;
            display: flex;
            align-items: center;
        }
        .status-state.success {
            color: #4CAF50;
        }
        .status-state.error {
            color: #FF0000;
        }
        .status-state svg {
            margin-left: 5px;
        }
        .footer {
            font-size: 12px;
            color: #780000;
            margin-top: 20px;
        }
        .hidden {
            display: none;
        }
        .sub-list {
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            margin-top: 10px;
            text-align: left;
        }
        .sub-list table {
            width: 100%;
            border-collapse: collapse;
        }
        .sub-list table th,
        .sub-list table td {
            padding: 8px;
            border: 1px solid #e0e0e0;
            text-align: left;
        }
        .status-item.active + .sub-list {
            display: block;
        }
        .info-box {
            background-color: #e7f3fe;
            border: 1px solid #b3d4fc;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            color: #31708f;
        }

        .info-box h3 {
            margin-top: 0;
        }

        .info-box ul {
            padding-left: 20px;
        }
        .button-container {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .button-container button {
            margin-left: 10px;
        }
        .next-button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .next-button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        .retry-button {
            padding: 10px 20px;
            background-color: #FF0000;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .retry-button.hidden {
            display: none;
        }
        #file-info-status {
            white-space: pre-line;
        }
    </style>
</head>
<body>
    <div class="background-container"></div>
    <div class="content">
        <div class="container">
            <header>
                <img src="./img/divumLogo.svg" alt="DivumWX Logo" class="logo">
                <div class="banner"><h2>Welcome to the DivumWX Skin Initial Setup.</h2>DivumWX is a skin for the weewx personal weather station software. As this is the first time that you are running this software (as is evidenced by the lack of a userSettings.php file), we are going to run a few checks and set up a few things for you before we get started.</div>
            </header>
            <div class="tabs">
                <button class="tab-button active" data-tab="tab1">Status Check</button>
                <button class="tab-button" data-tab="tab2">Settings File</button>
                <button class="tab-button" data-tab="tab3">Admin Password</button>
            </div>
            <div class="status-container">
                <div id="tab1" class="tab active">
                    <div id="status-header" class="status-header">Verifying System Status</div>
                    <div class="status-list">
                        <div class="status-item" id="php-version-item">
                            <div class="status-label"><i class="fas fa-plus icon"></i>PHP Version:</div>
                            <div class="status-state" id="php-version-status">Checking...</div>
                        </div>
                        <div class="sub-list hidden" id="php-version-details">
                        </div>
                        <div class="status-item" id="weewx-item">
                            <div class="status-label"><i class="fas fa-plus icon"></i>WeeWX Version:</div>
                            <div class="status-state" id="weewx-status">Checking...</div>
                        </div>
                        <div class="sub-list hidden" id="weewx-details">
                        </div>
                        <div class="status-item" id="php-modules-item">
                            <div class="status-label"><i class="fas fa-plus icon"></i>PHP Modules:</div>
                            <div class="status-state" id="php-modules-status">Checking...</div>
                        </div>
                        <div class="sub-list hidden" id="php-modules-details">
                        </div>
                        <div class="status-item" id="directories-item">
                            <div class="status-label"><i class="fas fa-plus icon"></i>Directories:</div>
                            <div class="status-state" id="directory-status">Checking...</div>
                        </div>
                        <div class="sub-list hidden" id="directory-details">
                        </div>
                        <div class="status-item" id="db-check-item">
                            <div class="status-label"><i class="fas fa-plus icon"></i>Database Check:</div>
                            <div class="status-state" id="db-check-status">Checking...</div>
                        </div>
                        <div class="sub-list hidden" id="db-check-details">
                        </div>
                        <div class="sub-list hidden" id="php-install-details">
                        </div>
                    </div>
                </div>
                <div id="tab2" class="tab">
                    <p id="file-writing-status">Writing userSettings.php ..........</p>
                    <p id="file-info-status" class="hidden"></p>
                </div>
                <div id="tab3" class="tab">
                <div id="password-tab">
                    <div id="info-box" class="info-box">
                        <h3>Create Admin Account</h3>
                        <p>Please enter an admin username and a strong password following the guidelines below:</p>
                        <ul>
                            <li>At least 8 characters long</li>
                            <li>Contains both uppercase and lowercase letters</li>
                            <li>Includes at least one numeric digit</li>
                            <li>Has at least one special character</li>
                        </ul>
                        <h4>After entering your admin username and new password, click submit. The credentials will be updated in the database, and the next button will be enabled. Clicking on the next button will take you to the admin login page.</h4>
                        <p>In the event of a lost password, there is a CLI script that can be run locally, in your admin directory, called `resetPassword.php`. This script can only be run from the command line and not from a web server.</p>
                    </div>
                    <form id="password-form">
                        <label for="admin-username">Admin Username:</label>
                        <input type="text" id="admin-username" name="admin-username" required>
                        <label for="password">Enter Password:</label>
                        <input type="password" id="password" name="password" required>
                        <button type="button" id="password-submit-button" class="next-button">Submit</button>
                    </form>
                    <p id="password-message" class="hidden"></p>
                </div>
            </div>
            <div class="button-container">
                <button id="retry-button" class="retry-button hidden">Retry</button>
                <button id="next-button" class="next-button" disabled>Next</button>
            </div>
            <footer class="footer">
                © 2024 Team DivumWX. All rights reserved.
            </footer>
        </div>
    </div>
    <script src="./js/dvmSetup.js"></script>
</body>
</html>
