<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "newsletter";
$password = "35kwJILTx4IXYeNB2Oqs";
$dbname = "Newsletter_Automation";

$dbStatus = null;

// PMA DB Access
# URL: https://pma.sd-lab.nl/
# Username: newsletter
# Password: 35kwJILTx4IXYeNB2Oqs

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname",$username,$password,$options);
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $dbStatus = true;
    
    // Start Session (If necessary) & Get User ID
    if (session_status() === PHP_SESSION_NONE) session_start();
    //echo "SQLite database is connected! ";
} catch (PDOException $e){
    echo "Connection Failed: " . $e->getMessage();
    $dbStatus = false;
}