<?php
session_start();
require '../config.php';

if (!isset($_COOKIES['user_ID'])) {
    header("location: ../../../pages/login.php");
}

$id = $_COOKIES['user_ID'];
try {
    $query = "DELETE FROM tab_users WHERE user_ID = :ID";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':ID', $id);
    $stmt->execute();

    session_start();
    session_unset();
    session_destroy();
    header("location: ../../../pages/login_view.php");
} catch (PDOException $e) {
    echo $e->getMessage();
}
