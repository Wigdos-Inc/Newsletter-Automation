<?php
header('Content-Type: application/json');

// Get the user's query
$input = json_decode(file_get_contents('php://input'), true);
$query = $input['query'] ?? '';

if (empty($query)) {
    echo json_encode(['success' => false, 'error' => 'Query is required']);
    exit;
}

// Your n8n webhook URL
$webhookUrl = 'https://harveygrowthproperties.app.n8n.cloud/webhook/066f9c41-8e73-499a-9f94-b58b806ba922';

// Send to n8n
$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => $query]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 120); // 2 minute timeout

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Return result
if ($httpCode == 200) {
    echo $response;
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to generate newsletter']);
}
?>