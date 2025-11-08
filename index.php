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
        body {
            background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
            color: #e0e0e0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 400px;
            width: 100%;
            margin: 120px auto;
            padding: 32px 24px;
            background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
            border: 1px solid #2a2a2a;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }

        h2 {
            text-align: center;
            margin-bottom: 32px;
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        input {
            width: 100%;
            padding: 12px 16px;
            margin: 0;
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 8px;
            color: #e0e0e0;
            font-size: 15px;
            font-family: inherit;
            outline: none;
            transition: all 0.2s ease-out;
            box-sizing: border-box;
        }

        input:focus {
            border-color: #667eea;
            background: #1f1f1f;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        input::placeholder {
            color: #666;
        }

        button {
            width: 100%;
            padding: 12px 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease-out;
            outline: none;
            margin-top: 8px;
        }

        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        button:active {
            transform: translateY(0);
        }

        .error {
            color: #ff4757;
            text-align: center;
            margin-bottom: 16px;
            font-size: 14px;
            padding: 8px;
            background: rgba(255, 71, 87, 0.1);
            border-radius: 6px;
            border: 1px solid rgba(255, 71, 87, 0.2);
        }

        .toggle {
            text-align: center;
            margin-top: 24px;
        }

        .toggle a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s ease-out;
        }

        .toggle a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .container {
                margin: 20px auto;
                padding: 24px 20px;
            }

            body {
                padding: 10px;
            }
        }
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