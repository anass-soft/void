<?php
// api_proxy.php (Updated to add system prompt for better model compatibility, e.g., Qwen)

require_once 'functions.php';
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$chat_id = $data['chat_id'] ?? null;

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

// Add system prompt if not present (improves response for models like Qwen)
if (empty($messages) || $messages[0]['role'] !== 'system') {
    array_unshift($messages, ['role' => 'system', 'content' => 'You are a helpful AI assistant.']);
}

// Prepare API request (streaming with optimized parameters)
$api_data = [
    'model' => OPENROUTER_MODEL,
    'messages' => $messages,
    'stream' => true,
    'temperature' => 0.7,  // More focused responses
    'top_p' => 0.9,        // Nucleus sampling for faster generation
    'max_tokens' => 2000   // Reasonable limit for faster responses
];

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

$ch = curl_init(OPENROUTER_API_URL);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . OPENROUTER_API_KEY,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false); // Stream directly
curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($curl, $data) {
    echo $data;
    flush();
    return strlen($data);
});

curl_exec($ch);
if (curl_errno($ch)) {
    echo "data: " . json_encode(['error' => curl_error($ch)]) . "\n\n";
}
curl_close($ch);
?>