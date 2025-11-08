<?php
// config.php (Update model as specified)

define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Change to your MySQL username
define('DB_PASS', ''); // Change to your MySQL password
define('DB_NAME', 'chatbot');

// OpenRouter API details
define('OPENROUTER_API_KEY', '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');
define('OPENROUTER_MODEL', 'qwen/qwen-2.5-72b-instruct:free');
define('OPENROUTER_API_URL', 'https://openrouter.ai/api/v1/chat/completions');

// API Ninjas key - User must sign up at https://api-ninjas.com/ and replace with their own key
define('API_NINJAS_KEY', 'YOUR_API_NINJAS_KEY_HERE');
define('API_NINJAS_QUOTES_URL', 'https://api.api-ninjas.com/v2/randomquotes?categories=success,wisdom');
?>