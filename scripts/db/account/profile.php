<?php
session_start();
require '../config.php';

$userid = $_SESSION['user'];
echo "<p>User ID: ".$userid."</p>";

try{
    if (!isset($_SESSION['user'])) {
        header("Location: ../../../pages/login_view.php");
    }
    else{
        $query = "SELECT * FROM tab_users WHERE user_ID = :ID";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':ID', $userid);
        $stmt->execute();

        $result = $stmt->fetch();

        $name = htmlspecialchars($result['username']);
        header('location: ../../../pages/profile_view.php?name=' . urlencode($name));
        //echo "<h1>Ingelogd in als '" . $result['username'] . "'</h1><a href='../../../index.php'>Terug naar overzicht</a><p>Uw Wachtwoord: " . $result['password'] . "</p>";
    }
} catch (PDOException $e){
    echo $e->getMessage();
}
