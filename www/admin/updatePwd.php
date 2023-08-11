<?php
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