<?php
// index.php
// Handles login and registration

require_once 'functions.php';

start_session();

if (is_logged_in()) {
    header('Location: chat.php');
    exit;
}

$action = $_POST['action'] ?? '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = db_connect();

    if ($action === 'register') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email';
        } elseif (strlen($password) < 6) {
            $error = 'Password too short';
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = 'Email already registered';
            } else {
                $hashed = hash_password($password);
                $stmt = $conn->prepare("INSERT INTO users (email, password, created_at) VALUES (?, ?, NOW())");
                $stmt->bind_param("ss", $email, $hashed);
                if ($stmt->execute()) {
                    $_SESSION['user_id'] = $stmt->insert_id;
                    header('Location: chat.php');
                    exit;
                } else {
                    $error = 'Registration failed';
                }
            }
        }
    } elseif ($action === 'login') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $hash);
            $stmt->fetch();
            if (verify_password($password, $hash)) {
                $_SESSION['user_id'] = $id;
                header('Location: chat.php');
                exit;
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'Email not found';
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot Login/Register</title>
    <style>
        body { background: #121212; color: #e0e0e0; font-family: Arial, sans-serif; }
        .container { max-width: 400px; margin: 100px auto; padding: 20px; background: #1e1e1e; border-radius: 8px; }
        input { width: 100%; padding: 10px; margin: 10px 0; background: #333; border: none; color: #e0e0e0; }
        button { width: 100%; padding: 10px; background: #4caf50; border: none; color: white; cursor: pointer; }
        .error { color: red; }
        .toggle { text-align: center; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h2 id="title">Login</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form id="form" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="hidden" name="action" value="login">
            <button type="submit">Login</button>
        </form>
        <div class="toggle">
            <a href="#" id="toggle-link">Don't have an account? Register</a>
        </div>
    </div>
    <script>
        const title = document.getElementById('title');
        const form = document.getElementById('form');
        const toggleLink = document.getElementById('toggle-link');

        let isLogin = true;

        toggleLink.addEventListener('click', (e) => {
            e.preventDefault();
            isLogin = !isLogin;
            title.textContent = isLogin ? 'Login' : 'Register';
            form.action.value = isLogin ? 'login' : 'register';
            toggleLink.textContent = isLogin ? "Don't have an account? Register" : 'Already have an account? Login';
        });
    </script>
</body>
</html>