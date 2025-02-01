if(password_verify($password, $result['password'])){<?php
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    $oldPassword = $_POST['oldPassword'];
    $newPassword = $_POST['newPassword'];
    $verifyPassword = $_POST['verifyPassword'];
    $db = new PDO('sqlite:./db/dvmAdmin.db3');
    $stmt = $db->prepare("SELECT * FROM users WHERE id = 1");
    $stmt->execute(array(':id' => $id));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if(password_verify($oldPassword, $result['password'])){
        if ($newPassword === $verifyPassword) {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $databasePath = "./db/dvmAdmin.db3";
            $pdo = new PDO("sqlite:$databasePath");
            $sql = "UPDATE users SET password = :password WHERE id = 1";
            $query = $pdo->prepare($sql);
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
    } else {
        echo "Existing password submitted did not match existing password in database.";
    }
}