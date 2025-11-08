<?php
// send_message.php
// Store message (user or assistant)

require_once 'functions.php';

if (!is_logged_in() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(401);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$chat_id = $data['chat_id'] ?? null;
$content = trim($data['content'] ?? '');
$role = $data['role'] ?? 'user';

if (!$chat_id || !$content) {
    http_response_code(200); // Return OK to avoid console error; log if needed
    exit;
}

$conn = db_connect();

// Update title if first message and user
if ($role === 'user') {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM messages WHERE chat_id = ?");
    $stmt->bind_param("i", $chat_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count === 0) {
        $title = substr($content, 0, 50) . (strlen($content) > 50 ? '...' : '');
        $stmt = $conn->prepare("UPDATE chats SET title = ? WHERE id = ?");
        $stmt->bind_param("si", $title, $chat_id);
        $stmt->execute();
        $stmt->close();
    }
}

$stmt = $conn->prepare("INSERT INTO messages (chat_id, role, content, time) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iss", $chat_id, $role, $content);
$stmt->execute();
$conn->close();
?>