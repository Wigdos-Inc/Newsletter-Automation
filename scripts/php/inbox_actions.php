<?php
session_start();
require_once '../db/config.php';

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$userID = intval($_SESSION['userID']);

// Check if DB is up
if (!$dbStatus) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get action
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'mark_read':
            // Mark a single message as read
            $messageId = intval($_POST['message_id'] ?? 0);
            
            if ($messageId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid message ID']);
                exit;
            }

            // Verify message belongs to user
            $stmt = $pdo->prepare("SELECT user_ID FROM tab_inbox WHERE inbox_ID = ?");
            $stmt->execute([$messageId]);
            $message = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$message || $message['user_ID'] != $userID) {
                echo json_encode(['success' => false, 'message' => 'Message not found or access denied']);
                exit;
            }

            // Update status
            $stmt = $pdo->prepare("UPDATE tab_inbox SET status = 'read', updated_at = NOW() WHERE inbox_ID = ?");
            $stmt->execute([$messageId]);

            echo json_encode(['success' => true, 'message' => 'Message marked as read']);
            break;

        case 'delete':
            // Delete a single message
            $messageId = intval($_POST['message_id'] ?? 0);
            
            if ($messageId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid message ID']);
                exit;
            }

            // Verify message belongs to user
            $stmt = $pdo->prepare("SELECT user_ID FROM tab_inbox WHERE inbox_ID = ?");
            $stmt->execute([$messageId]);
            $message = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$message || $message['user_ID'] != $userID) {
                echo json_encode(['success' => false, 'message' => 'Message not found or access denied']);
                exit;
            }

            // Delete message
            $stmt = $pdo->prepare("DELETE FROM tab_inbox WHERE inbox_ID = ?");
            $stmt->execute([$messageId]);

            echo json_encode(['success' => true, 'message' => 'Message deleted']);
            break;

        case 'mark_all_read':
            // Mark all messages as read for this user
            $stmt = $pdo->prepare("UPDATE tab_inbox SET status = 'read', updated_at = NOW() WHERE user_ID = ? AND status = 'unread'");
            $stmt->execute([$userID]);

            $count = $stmt->rowCount();
            echo json_encode(['success' => true, 'message' => "$count messages marked as read"]);
            break;

        case 'delete_all_read':
            // Delete all read messages for this user
            $stmt = $pdo->prepare("DELETE FROM tab_inbox WHERE user_ID = ? AND status = 'read'");
            $stmt->execute([$userID]);

            $count = $stmt->rowCount();
            echo json_encode(['success' => true, 'message' => "$count messages deleted"]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (PDOException $e) {
    error_log("Database error in inbox_actions.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
