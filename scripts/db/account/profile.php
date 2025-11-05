<?php
session_start();
require '../config.php';

try{
    if (!isset($_COOKIES['userID'])) {
        header("Location: ../../../pages/login_view.php");
        exit();
    }
    else{
        $userid = $_SESSION['userID'];
        
        $query = "SELECT * FROM tab_users WHERE user_ID = :ID";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':ID', $userid, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch();
        $_SESSION['user_data'] = $result;

        header('location: ../../../pages/profile_view.php');
        exit();
    }
} catch (PDOException $e){
    error_log($e->getMessage());
    header("Location: ../../../pages/login_view.php?err=An error occurred");
    exit();
}
