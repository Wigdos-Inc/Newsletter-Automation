<?php
session_start();
require '../config.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("location: ../../../pages/profile_view.php?err=Accessed page without POST");
    exit();
}

if(!isset($_SESSION['userID'])){
    header("location: ../../../pages/login_view.php");
    exit();
}

$username = trim($_POST['username']);
$email = trim($_POST['email']);
$dob = $_POST['dob'];
$id = $_SESSION['userID'];

// Validate required fields
if(empty($username) || empty($email) || empty($dob)){
    header('location: ../../../pages/profile_view.php?err=All fields are required');
    exit();
}

// Validate email format
if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    header('location: ../../../pages/profile_view.php?err=Invalid email format');
    exit();
}

try{
    // Check if username is taken by another user
    $query = "SELECT * FROM tab_users WHERE username = :username AND user_ID != :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $result_name = $stmt->fetch();

    if($result_name){
        header('location: ../../../pages/profile_view.php?err=Username already exists');
        exit();
    }

    // Check if email is taken by another user
    $query = "SELECT * FROM tab_users WHERE email = :email AND user_ID != :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $result_email = $stmt->fetch();

    if($result_email){
        header('location: ../../../pages/profile_view.php?err=Email already in use');
        exit();
    }

    // Update user information
    $query = "UPDATE tab_users SET username = :username, email = :email, dob = :dob WHERE user_ID = :ID";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'ID' => $id,
        'username' => $username,
        'email' => $email,
        'dob' => $dob
    ]);

    // Fetch updated user data
    $query = "SELECT * FROM tab_users WHERE user_ID = :ID";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':ID', $id, PDO::PARAM_INT);
    $stmt->execute();
    $result_usr = $stmt->fetch();

    $_SESSION['user_data'] = $result_usr;
    header('location: ../../../pages/profile_view.php?success=Profile updated successfully');
    exit();
} catch (PDOException $e){
    error_log($e->getMessage());
    header('location: ../../../pages/profile_view.php?err=An error occurred. Please try again.');
    exit();
}
