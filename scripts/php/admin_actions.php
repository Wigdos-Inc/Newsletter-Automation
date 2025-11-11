<?php
/**
 * Admin Actions Handler
 * Handles administrative actions like hiding, banning, and moderating newsletters
 */

header('Content-Type: application/json');
session_start();

require_once '../db/config.php';
require_once '../db/notifications.php';

// Check if user is logged in and is admin
$userID = isset($_COOKIE['userID']) ? intval($_COOKIE['userID']) : null;

if (!$userID) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

// Check if user is admin
$stmt = $pdo->prepare("SELECT role FROM tab_users WHERE user_ID = ?");
$stmt->execute([$userID]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized - Admin access required']);
    exit;
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$newsletterID = $input['newsletter_ID'] ?? null;
$reason = $input['reason'] ?? null;

// Handle hide newsletter action
if ($action === 'hide') {
    if (!$newsletterID) {
        echo json_encode(['success' => false, 'error' => 'Newsletter ID is required']);
        exit;
    }
    
    try {
        // Update newsletter status to hidden
        $stmt = $pdo->prepare("UPDATE tab_newsletters SET status = 'hidden' WHERE newsletter_ID = ?");
        $stmt->execute([$newsletterID]);
        
        // Send notifications to users who favorited this newsletter
        $notificationCount = notifyNewsletterHidden($pdo, $newsletterID, $reason);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Newsletter hidden successfully',
            'notifications_sent' => $notificationCount
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Handle unhide newsletter action
if ($action === 'unhide') {
    if (!$newsletterID) {
        echo json_encode(['success' => false, 'error' => 'Newsletter ID is required']);
        exit;
    }
    
    try {
        // Update newsletter status back to published
        $stmt = $pdo->prepare("UPDATE tab_newsletters SET status = 'published' WHERE newsletter_ID = ?");
        $stmt->execute([$newsletterID]);
        
        // Send notifications that newsletter is back online
        $notificationCount = notifyNewsletterRepublished($pdo, $newsletterID);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Newsletter unhidden successfully',
            'notifications_sent' => $notificationCount
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Handle ban newsletter action (permanent removal)
if ($action === 'ban') {
    if (!$newsletterID) {
        echo json_encode(['success' => false, 'error' => 'Newsletter ID is required']);
        exit;
    }
    
    try {
        // First notify users before deletion
        $notificationCount = notifyNewsletterHidden($pdo, $newsletterID, $reason ?: 'This newsletter violates our community guidelines');
        
        // Update newsletter status to banned
        $stmt = $pdo->prepare("UPDATE tab_newsletters SET status = 'banned' WHERE newsletter_ID = ?");
        $stmt->execute([$newsletterID]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Newsletter banned successfully',
            'notifications_sent' => $notificationCount
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Handle bulk hide action
if ($action === 'bulk_hide') {
    $newsletterIDs = $input['newsletter_IDs'] ?? [];
    
    if (empty($newsletterIDs)) {
        echo json_encode(['success' => false, 'error' => 'No newsletters selected']);
        exit;
    }
    
    try {
        $totalNotifications = 0;
        
        foreach ($newsletterIDs as $id) {
            // Update status
            $stmt = $pdo->prepare("UPDATE tab_newsletters SET status = 'hidden' WHERE newsletter_ID = ?");
            $stmt->execute([$id]);
            
            // Send notifications
            $totalNotifications += notifyNewsletterHidden($pdo, $id, $reason);
        }
        
        echo json_encode([
            'success' => true, 
            'message' => count($newsletterIDs) . ' newsletters hidden successfully',
            'notifications_sent' => $totalNotifications
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Handle bulk ban action
if ($action === 'bulk_ban') {
    $newsletterIDs = $input['newsletter_IDs'] ?? [];
    
    if (empty($newsletterIDs)) {
        echo json_encode(['success' => false, 'error' => 'No newsletters selected']);
        exit;
    }
    
    try {
        $totalNotifications = 0;
        
        foreach ($newsletterIDs as $id) {
            // Send notifications first
            $totalNotifications += notifyNewsletterHidden($pdo, $id, $reason ?: 'This newsletter violates our community guidelines');
            
            // Update status
            $stmt = $pdo->prepare("UPDATE tab_newsletters SET status = 'banned' WHERE newsletter_ID = ?");
            $stmt->execute([$id]);
        }
        
        echo json_encode([
            'success' => true, 
            'message' => count($newsletterIDs) . ' newsletters banned successfully',
            'notifications_sent' => $totalNotifications
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Invalid action
echo json_encode(['success' => false, 'error' => 'Invalid action']);
exit;
