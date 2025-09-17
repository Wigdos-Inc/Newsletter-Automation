<?php
$db = 'databases/identifier.sqlite';
try {
    $pdo = new PDO("sqlite:$db");
    //echo "SQLite database is connected! ";
} catch (PDOException $e){
    echo $e->getMessage();
}
