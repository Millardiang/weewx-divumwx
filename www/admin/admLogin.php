<?php
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