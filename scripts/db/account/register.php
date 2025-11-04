<?php
session_start();
require '../config.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("../../../pages/register_view.php?err=Accessed page wihout POST");
}

$name = $_POST['username'];
$email = $_POST['email'];
$password = sha1($_POST['password']);
$passwordck = sha1($_POST['passwordCheck']);
$dob = $_POST['birthdate'];
$address = "unkown";
$auth = "0";

if($password !== $passwordck){
    header('location: ../../../pages/register_view.php?err="passwords did not match"');
    exit();
}

$date = date("Y-m-d");

//if(strlen($password) <= 0 || strlen($name <= 0)){
    //header("location: ../pages/register_view.php");
//}

try {
    $query = "SELECT * FROM tab_users WHERE username = :username";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $name);
    $stmt->execute();
    $result_name = $stmt->fetch();

    $query = "SELECT * FROM tab_users WHERE email = :email";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $result_mail = $stmt->fetch();

    if($result_name){
        header('location: ../../../pages/register_view.php?err="User already exists."');
        exit();
    }
    if($result_mail){
        header('location: ../../../pages/register_view.php?err="Mail address has already been used"');
        exit();
    }

    $query = "INSERT INTO tab_users (username, password, email, dob, role, creation_date)";
    $query .= "VALUES (:username, :password, :email, :dob, :role, :creation_date)";

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'username' => $name,
        'password' => $password,
        'email' => $email,
        'dob' => $dob,
        'role' => "user",
        'creation_date' => $date
    ]);

    if ($stmt->rowCount()) {
        $id = $pdo->lastInsertId();
        $_SESSION['user'] = $id;
        header("location: profile.php");
    } else {
        echo "FOUT bij toevoegen";
    }
} catch (PDOException $e) {
    echo $e->getMessage();
    exit;
}
