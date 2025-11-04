<?php
session_start();
require '../config.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("location: ../../../pages/login_view.php?err=Accessed page wihout POST");
}

$login_mail = filter_var(trim($_POST['username']), FILTER_SANITIZE_EMAIL);
$password = sha1(trim($_POST['password']));

if(!$login_mail){
    echo "Error no mail found";
}

try {
    $query = "SELECT * FROM tab_users WHERE username = '$login_mail'";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $result = $stmt->fetch();
    if(!$result || $password !== $result['password']){
        header( 'location: ../../../pages/login_view.php?err=Login details did not match');
    }
    else{
        $_SESSION['user'] = $result['user_ID'];
        header("location: profile.php");
    }
} catch (PDOException $e){
    echo $e->getMessage();
}
