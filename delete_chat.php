<?php
// delete_chat.php
// Delete a chat and its messages

require_once 'functions.php';

if (!is_logged_in() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
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
$stmt = $conn->prepare("DELETE FROM messages WHERE chat_id = ?");
$stmt->bind_param("i", $chat_id);
$stmt->execute();
$stmt = $conn->prepare("DELETE FROM chats WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $chat_id, $user_id);
$stmt->execute();
$conn->close();
?>