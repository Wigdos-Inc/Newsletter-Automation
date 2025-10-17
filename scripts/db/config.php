<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$servername = "localhost:3306";
$username = "newsletter";
$password = "35kwJILTx4IXYeNB2Oqs";
$dbname = "program_table_db";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname",$username,$password,$options);
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    //echo "SQLite database is connected! ";
} catch (PDOException $e){
    echo "Connection Failed: " . $e->getMessage();
}
