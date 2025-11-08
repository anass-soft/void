<?php
// get_quote.php
// Fetch random quote via proxy

require_once 'config.php';

$ch = curl_init(API_NINJAS_QUOTES_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-Api-Key: ' . API_NINJAS_KEY
]);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true)[0] ?? ['quote' => 'No quote available', 'author' => 'Unknown'];

header('Content-Type: application/json');
echo json_encode($data);
?>