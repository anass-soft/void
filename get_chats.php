<?php
// get_chats.php
// Fetch user's chats

require_once 'functions.php';

if (!is_logged_in()) {
    http_response_code(401);
    exit;
}

$user_id = get_user_id();
$conn = db_connect();
$stmt = $conn->prepare("SELECT id, title FROM chats WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$chats = [];
while ($row = $result->fetch_assoc()) {
    $chats[] = $row;
}
$conn->close();

header('Content-Type: application/json');
echo json_encode($chats);
?>