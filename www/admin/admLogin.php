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

    $username = $_POST['username'];
    $password = $_POST['password'];
    $db = new PDO('sqlite:./db/dvmAdmin.db3');
    $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(array(':username' => $username));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if(password_verify($password, $result['password'])){
        session_start();
        $_SESSION['username'] = $username;
        $_SESSION['initialLogin'] = $result['initialLogin'];
        $_SESSION['login_time'] = time();
        echo 'Success';
    } else {
        echo 'Fail';
    }
?>