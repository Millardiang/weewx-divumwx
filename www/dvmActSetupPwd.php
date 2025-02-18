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
header('Content-Type: application/json');

error_log("Session ID: " . session_id());
error_log("Session Data: " . print_r($_SESSION, true));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

if (!isset($_SESSION['setup_allowed']) || $_SESSION['setup_allowed'] !== true) {
    error_log("Unauthorized script access: setup_allowed not set!");
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized script access.']);
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($username) || strlen($username) < 3) {
    echo json_encode(['status' => 'error', 'message' => 'Username must be at least 3 characters long.']);
    exit;
}
if (empty($password) || strlen($password) < 8) {
    echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters long.']);
    exit;
}
$hash = password_hash($password, PASSWORD_DEFAULT);
try {
    $db = new PDO('sqlite:./admin/db/dvmAdmin.db3');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare("SELECT id FROM users WHERE id = 1");
    $stmt->execute();
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($existingUser) {
        echo json_encode(['status' => 'error', 'message' => 'Admin user already exists. Setup cannot proceed.']);
        exit;
    }

    $sql = "INSERT INTO users (id, username, password) VALUES (1, :username, :password)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':password', $hash, PDO::PARAM_STR);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Admin user created successfully.']);
        unset($_SESSION['setup_allowed']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to create admin user.']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
