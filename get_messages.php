<?php
// get_messages.php
// Fetch messages for a chat

require_once 'functions.php';

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
$stmt = $conn->prepare("SELECT role, content FROM messages WHERE chat_id = ? ORDER BY time ASC");
$stmt->bind_param("i", $chat_id);
$stmt->execute();
$result = $stmt->get_result();
$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
$conn->close();

header('Content-Type: application/json');
echo json_encode($messages);
?>