<?php
// get_ai_response.php (New file)
// Handles non-streaming OpenRouter API call in PHP

require_once 'functions.php';
require_once 'config.php';

if (!is_logged_in()) {
    http_response_code(401);
    exit;
}

$chat_id = $_GET['chat_id'] ?? null;

if (!$chat_id) {
    http_response_code(400);
    exit;
}

$user_id = get_user_id();
$conn = db_connect();

// Verify chat belongs to user
$stmt = $conn->prepare("SELECT id FROM chats WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $chat_id, $user_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    http_response_code(403);
    exit;
}

// Fetch chat history
$stmt = $conn->prepare("SELECT role, content FROM messages WHERE chat_id = ? ORDER BY time ASC");
$stmt->bind_param("i", $chat_id);
$stmt->execute();
$result = $stmt->get_result();
$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = ['role' => $row['role'], 'content' => $row['content']];
}
$conn->close();

// Prepare API request (non-streaming)
$api_data = [
    'model' => OPENROUTER_MODEL,
    'messages' => $messages,
    'stream' => false  // Changed to false for full response
];

$ch = curl_init(OPENROUTER_API_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . OPENROUTER_API_KEY,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    http_response_code($http_code);
    echo json_encode(['error' => 'API request failed']);
    exit;
}

$data = json_decode($response, true);
$content = $data['choices'][0]['message']['content'] ?? '';

if ($content) {
    // Save to DB
    $conn = db_connect();
    $stmt = $conn->prepare("INSERT INTO messages (chat_id, role, content, time) VALUES (?, 'assistant', ?, NOW())");
    $stmt->bind_param("is", $chat_id, $content);
    $stmt->execute();
    $conn->close();
}

header('Content-Type: application/json');
echo json_encode(['content' => $content]);
?>