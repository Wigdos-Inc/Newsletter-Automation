<?php
session_start();

if (!isset($_COOKIES['userID'])) {
    header("location: ../../../index.php");
    exit();
}

session_unset();
session_destroy();
header("location: ../../../pages/login_view.php");
exit();
