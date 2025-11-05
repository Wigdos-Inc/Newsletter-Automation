<?php
session_start();

// Clear the tutorial flag from session
if (isset($_SESSION['show_tutorial'])) {
    unset($_SESSION['show_tutorial']);
}

// Return success response
header('Content-Type: application/json');
echo json_encode(['success' => true]);
exit();
