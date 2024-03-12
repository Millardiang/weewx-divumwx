<?php
if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

$dbPath = './db/dvmAdmin.db3';

if (!file_exists($dbPath)) {
    die("Database file does not exist at: $dbPath\n");
}

if (!is_readable($dbPath) || !is_writable($dbPath)) {
    die("Database file is not readable/writable. Please check the file permissions: $dbPath\n");
}

echo "Enter the username whose password needs to be reset: ";
$username = trim(fgets(STDIN));
echo "Enter the new password: ";
$newPassword = trim(fgets(STDIN));

$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $checkUser = $db->prepare("SELECT * FROM users WHERE username = :username");
    $checkUser->execute([':username' => $username]);
    $userExists = $checkUser->fetch();

    if (!$userExists) {
        die("User not found.\n");
    }

    $stmt = $db->prepare("UPDATE users SET password = :password, initialLogin = 1 WHERE username = :username");
    $stmt->execute([':password' => $hashedPassword, ':username' => $username]);

    if ($stmt->rowCount()) {
        echo "Password reset successfully.\n";
    } else {
        echo "Password unchanged.\n";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

