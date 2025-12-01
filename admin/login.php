<?php
session_start();
require_once '../config/db_config.php';

// Redirect if already logged in
if (is_admin_logged_in()) {
    redirect(SITE_URL . '/admin/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id, username, password, email FROM admin_users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password'])) {
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);
                
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['last_activity'] = time();
                
                // Update last login
                $update = $db->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                $update->execute([$admin['id']]);
                
                redirect(SITE_URL . '/admin/dashboard.php');
            } else {
                $error = 'Invalid username or password';
            }
        } catch(PDOException $e) {
            $error = 'Login failed. Please try again.';
        }
    } else {
        $error = 'Please enter both username and password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Anab Driving School</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #0f1013 0%, #1b1d24 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            width: 100%;
            max-width: 420px;
            padding: 40px;
        }
        .logo-area {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo-mark {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 20%, #fff 0, #ffdd80 20%, #ffb300 50%, #b77400 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 28px;
            color: #111;
            margin-bottom: 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }
        h1 {
            font-size: 24px;
            color: #111;
            margin-bottom: 8px;
        }
        .subtitle {
            color: #666;
            font-size: 14px;
        }
        .error {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid #fcc;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            font-size: 14px;
            color: #333;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s;
        }
        input:focus {
            outline: none;
            border-color: #ffb300;
        }
        .btn-login {
            width: 100%;
            background: #ffb300;
            color: #111;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 179, 0, 0.4);
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
        }
        .back-link a:hover {
            color: #ffb300;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <?php
        // Get logo from database if available
        try {
            $db = Database::getInstance()->getConnection();
            $logo_query = $db->query("SELECT setting_value FROM site_settings WHERE setting_key = 'logo_path' LIMIT 1");
            $logo_result = $logo_query->fetch();
            $logo_path = $logo_result ? '../' . $logo_result['setting_value'] : '';
        } catch (Exception $e) {
            $logo_path = '';
        }
        ?>
        
        <div class="logo-area">
            <?php if (!empty($logo_path) && file_exists($logo_path)): ?>
                <img src="<?php echo htmlspecialchars($logo_path); ?>" alt="Logo" style="max-width: 150px; max-height: 80px; margin-bottom: 16px; object-fit: contain;">
            <?php else: ?>
                <div class="logo-mark">A</div>
            <?php endif; ?>
            <h1>Admin Login</h1>
            <p class="subtitle">Anab Driving School</p>
        </div>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-login">Login to Dashboard</button>
        </form>

        <div class="back-link">
            <a href="../index.php">← Back to Website</a>
        </div>
    </div>
</body>
</html>