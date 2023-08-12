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
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            die("CSRF token validation failed.");
        }
        $newPassword = $_POST['newPassword'];
        $verifyPassword = $_POST['verifyPassword'];
        if ($newPassword === $verifyPassword) {
            $username = $_SESSION['username'];
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $databasePath = "./db/dvmAdmin.db3";
            $pdo = new PDO("sqlite:$databasePath");
            $sql = "UPDATE users SET password = :password, initialLogin = 1 WHERE username = :username";
            $query = $pdo->prepare($sql);
            $query->bindParam(':username', $username);
            $query->bindParam(':password', $hash);

            if ($query->execute()) {
                echo "Password updated successfully.";
            } else {
                $errorInfo = $query->errorInfo();
                echo "An error occurred while updating the password. Error: " . $errorInfo[2];
            }
        } else {
            echo "Passwords do not match.";
        }
    }
?>