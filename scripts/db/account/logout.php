<?php
if (!isset($_SESSION['user'])) {
    header("location: ../../../index.php");
}

session_start();
session_unset();
session_destroy();
header("location: ../../../pages/login_view.php");
