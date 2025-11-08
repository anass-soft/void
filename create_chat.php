<?php
// create_chat.php
// Create new chat

require_once 'functions.php';

if (!is_logged_in() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(401);
    exit;
}

$user_id = get_user_id();
$conn = db_connect();
$stmt = $conn->prepare("INSERT INTO chats (user_id, title, created_at) VALUES (?, 'New Chat', NOW())");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$chat_id = $stmt->insert_id;
$conn->close();

header('Content-Type: application/json');
echo json_encode(['chat_id' => $chat_id]);
?>