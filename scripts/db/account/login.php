<?php
session_start();
require '../config.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("location: ../../../pages/login_view.php?err=Accessed page without POST");
    exit();
}

$username = trim($_POST['username']);
$password = trim($_POST['password']);

if(empty($username) || empty($password)){
    header("location: ../../../pages/login_view.php?err=Username and password are required");
    exit();
}

try {
    $query = "SELECT * FROM tab_users WHERE username = :username";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    $result = $stmt->fetch();
    
    if(!$result){
        header('location: ../../../pages/login_view.php?err=Login details did not match');
        exit();
    }
    
    // Check if this is a legacy SHA1 password or new bcrypt password
    $isLegacyPassword = false;
    $passwordValid = false;
    
    // Check if password_legacy column exists and is set
    if(isset($result['password_legacy']) && $result['password_legacy'] == 1){
        // Try SHA1 verification for legacy passwords
        if(sha1($password) === $result['password']){
            $passwordValid = true;
            $isLegacyPassword = true;
        }
    } else {
        // Try bcrypt verification for new passwords
        if(password_verify($password, $result['password'])){
            $passwordValid = true;
        }
    }
    
    if(!$passwordValid){
        header('location: ../../../pages/login_view.php?err=Login details did not match');
        exit();
    }
    
    // If this was a legacy password, upgrade it to bcrypt
    if($isLegacyPassword){
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $updateQuery = "UPDATE tab_users SET password = :password, password_legacy = 0 WHERE user_ID = :userID";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute([
            'password' => $newHash,
            'userID' => $result['user_ID']
        ]);
    }
    
    // Set cookies
    $expiry = time() + (30 * 24 * 60 * 60); // 30 days
    setcookie('userID', $result['user_ID'], $expiry, '/', '', false, true);
    setcookie('user_data', json_encode($result), $expiry, '/', '', false, true);
    
    // Redirect to the page user was on before login, or profile if none
    echo "<script>
        var pageBeforeLogin = localStorage.getItem('pageBeforeLogin') || 'pages/profile_view.php';
        if(window.self !== window.top){
            window.top.location.href = '../../../index.php?load=' + encodeURIComponent(pageBeforeLogin);
        } else {
            window.location.href = '../../../index.php?load=' + encodeURIComponent(pageBeforeLogin);
        }
    </script>";
    exit();
    
} catch (PDOException $e){
    error_log($e->getMessage());
    header("location: ../../../pages/login_view.php?err=An error occurred. Please try again.");
    exit();
}
