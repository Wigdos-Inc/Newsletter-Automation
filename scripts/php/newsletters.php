<?php
header('Content-Type: application/json');

// Include database connection
require_once '../db/config.php';

// Get the user's query
$input = json_decode(file_get_contents('php://input'), true);
$query = $input['query'] ?? '';
$action = $input['action'] ?? 'generate'; // 'generate' or 'save'

// Handle save action
if ($action === 'save') {
    $title = $input['title'] ?? '';
    $content = $input['content'] ?? '';
    $userID = isset($_COOKIE['userID']) ? intval($_COOKIE['userID']) : null;
    
    if (empty($title) || empty($content)) {
        echo json_encode(['success' => false, 'error' => 'Title and content are required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO tab_newsletters (title, content, user_ID, creation_date, status) VALUES (?, ?, ?, NOW(), 'draft')");
        $stmt->execute([$title, $content, $userID]);
        $newsletterID = $pdo->lastInsertId();
        
        echo json_encode(['success' => true, 'newsletter_ID' => $newsletterID, 'message' => 'Newsletter saved successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Generate newsletter (existing code)
if (empty($query)) {
    echo json_encode(['success' => false, 'error' => 'Query is required']);
    exit;
}

$webhookUrl = 'https://harveygrowthproperties.app.n8n.cloud/webhook/066f9c41-8e73-499a-9f94-b58b806ba922';

$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => $query]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 120);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo $response;
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to generate newsletter']);
}
?>