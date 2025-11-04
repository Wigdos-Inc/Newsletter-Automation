<?php
session_start();
require '../config.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("location: ../../../pages/profile_view.php?err=Accessed page wihout POST");
}

$username = $_POST['username'];
$id = $_SESSION['user'];

try{
    $query = "SELECT * FROM tab_users WHERE username = :username";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $result_name = $stmt->fetch();

    if($result_name){
        header('location: ../../../pages/profile_view.php?err="User already exists."');
        exit();
    }

    $query = "SELECT * FROM tab_users WHERE user_ID = :ID";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':ID', $id);
    $stmt->execute();
    $result_usr = $stmt->fetch();

    $query = "UPDATE tab_users SET username = :username, password = :password, email = :email, dob = :dob, role = :role, creation_date = :creation_date WHERE user_ID = :ID";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'ID' => $id,
        'username' => $username,
        'password' => $result_usr['password'],
        'email' => $result_usr['email'],
        'dob' => $result_usr['dob'],
        'role' => $result_usr['role'],
        'creation_date' => $result_usr['creation_date']
    ]);

    $query = "SELECT * FROM tab_users WHERE user_ID = :ID";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':ID', $id);
    $stmt->execute();
    $result_usr = $stmt->fetch();

    $_SESSION['user_data'] = $result_usr;
    header('location: profile.php');
} catch (PDOException $e){
    echo $e->getMessage();
}
