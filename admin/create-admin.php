<?php
/**
 * Admin Account Generator
 * 
 * SECURITY WARNING: Delete this file after creating your admin account!
 * This file should NEVER be accessible in production.
 */

// Uncomment this line to enable the generator (security measure)
 define('ADMIN_GENERATOR_ENABLED', true);

if (!defined('ADMIN_GENERATOR_ENABLED')) {
    die('
    <!DOCTYPE html>
    <html>
    <head>
        <title>Admin Generator - Disabled</title>
        <style>
            body { font-family: system-ui; background: #f3f4f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
            .box { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); max-width: 500px; text-align: center; }
            h1 { color: #dc2626; margin-bottom: 20px; }
            code { background: #f3f4f6; padding: 2px 6px; border-radius: 4px; }
            .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; text-align: left; }
        </style>
    </head>
    <body>
        <div class="box">
            <h1>🔒 Admin Generator Disabled</h1>
            <div class="warning">
                <strong>⚠️ Security Notice:</strong><br>
                This page is disabled by default for security.
            </div>
            <p>To enable this page temporarily, edit <code>create-admin.php</code> and uncomment line 9:</p>
            <pre style="background: #f3f4f6; padding: 15px; border-radius: 6px; text-align: left; overflow-x: auto;">define(\'ADMIN_GENERATOR_ENABLED\', true);</pre>
            <p style="margin-top: 20px; color: #dc2626; font-weight: bold;">⚠️ DELETE this file after creating your admin account!</p>
        </div>
    </body>
    </html>
    ');
}

require_once '../config/db_config.php';

$message = '';
$message_type = '';
$generated_hash = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['generate_hash'])) {
        // Generate password hash only
        $password = $_POST['password'];
        $generated_hash = password_hash($password, PASSWORD_DEFAULT);
        $message = 'Password hash generated! Copy it below.';
        $message_type = 'success';
        
    } elseif (isset($_POST['create_admin'])) {
        // Create admin account
        $username = sanitize_input($_POST['username']);
        $password = $_POST['password'];
        $email = sanitize_input($_POST['email']);
        
        if (empty($username) || empty($password) || empty($email)) {
            $message = 'All fields are required!';
            $message_type = 'error';
        } else {
            try {
                $db = Database::getInstance()->getConnection();
                
                // Check if username exists
                $check = $db->prepare("SELECT id FROM admin_users WHERE username = ?");
                $check->execute([$username]);
                
                if ($check->fetch()) {
                    $message = 'Username already exists!';
                    $message_type = 'error';
                } else {
                    // Create new admin
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("INSERT INTO admin_users (username, password, email) VALUES (?, ?, ?)");
                    
                    if ($stmt->execute([$username, $hashed_password, $email])) {
                        $message = "Admin account created successfully!<br><br>
                                   <strong>Username:</strong> $username<br>
                                   <strong>Password:</strong> $password<br>
                                   <strong>Email:</strong> $email<br><br>
                                   <span style='color: #dc2626;'>⚠️ DELETE this file NOW for security!</span>";
                        $message_type = 'success';
                    } else {
                        $message = 'Failed to create admin account!';
                        $message_type = 'error';
                    }
                }
            } catch (Exception $e) {
                $message = 'Database error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif (isset($_POST['update_password'])) {
        // Update existing admin password
        $username = sanitize_input($_POST['update_username']);
        $new_password = $_POST['new_password'];
        
        if (empty($username) || empty($new_password)) {
            $message = 'Username and password are required!';
            $message_type = 'error';
        } else {
            try {
                $db = Database::getInstance()->getConnection();
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE admin_users SET password = ? WHERE username = ?");
                
                if ($stmt->execute([$hashed_password, $username])) {
                    if ($stmt->rowCount() > 0) {
                        $message = "Password updated successfully!<br><br>
                                   <strong>Username:</strong> $username<br>
                                   <strong>New Password:</strong> $new_password";
                        $message_type = 'success';
                    } else {
                        $message = 'Username not found!';
                        $message_type = 'error';
                    }
                }
            } catch (Exception $e) {
                $message = 'Database error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    }
}

// Get all admins
try {
    $db = Database::getInstance()->getConnection();
    $admins = $db->query("SELECT id, username, email, created_at, last_login FROM admin_users ORDER BY id ASC")->fetchAll();
} catch (Exception $e) {
    $admins = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Account Generator</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 30px;
            border-radius: 12px 12px 0 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .header h1 {
            color: #111;
            margin-bottom: 10px;
        }
        .warning-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin-top: 15px;
            border-radius: 6px;
        }
        .danger-box {
            background: #fee2e2;
            border-left: 4px solid #dc2626;
            padding: 15px;
            margin-top: 15px;
            border-radius: 6px;
            color: #991b1b;
            font-weight: 600;
        }
        .content {
            background: white;
            padding: 30px;
            border-radius: 0 0 12px 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .section {
            background: #f9fafb;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            border: 1px solid #e5e7eb;
        }
        .section h2 {
            color: #111;
            margin-bottom: 20px;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            font-size: 14px;
            color: #374151;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }
        .btn-success {
            background: #10b981;
            color: white;
        }
        .btn-success:hover {
            background: #059669;
        }
        .btn-warning {
            background: #f59e0b;
            color: white;
        }
        .btn-warning:hover {
            background: #d97706;
        }
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        .btn-danger:hover {
            background: #dc2626;
        }
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .message-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .message-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .hash-output {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            word-break: break-all;
            margin-top: 10px;
            border: 1px solid #d1d5db;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th {
            background: #f3f4f6;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            border-bottom: 2px solid #e5e7eb;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13px;
        }
        tr:hover {
            background: #f9fafb;
        }
        .note {
            background: #e0e7ff;
            border-left: 4px solid #667eea;
            padding: 12px;
            margin-top: 15px;
            border-radius: 6px;
            font-size: 13px;
        }
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        @media (max-width: 768px) {
            .grid-2 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Admin Account Generator</h1>
            <p style="color: #666; margin-top: 10px;">Create and manage admin accounts for your driving school website</p>
            
            <div class="danger-box">
                ⚠️ <strong>SECURITY WARNING:</strong> DELETE this file (create-admin.php) after creating your admin account! This file should NEVER exist on a production server!
            </div>
        </div>

        <div class="content">
            <?php if ($message): ?>
                <div class="message message-<?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Create New Admin -->
            <div class="section">
                <h2>➕ Create New Admin Account</h2>
                <form method="POST">
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Username *</label>
                            <input type="text" name="username" required placeholder="admin">
                        </div>
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" required placeholder="admin@example.com">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Password *</label>
                        <input type="text" name="password" required placeholder="Enter a strong password">
                    </div>
                    <button type="submit" name="create_admin" class="btn btn-primary">
                        Create Admin Account
                    </button>
                </form>
                <div class="note">
                    💡 <strong>Tip:</strong> Use a strong password with uppercase, lowercase, numbers, and symbols.
                </div>
            </div>

            <!-- Update Password -->
            <div class="section">
                <h2>🔄 Update Existing Admin Password</h2>
                <form method="POST">
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Username *</label>
                            <input type="text" name="update_username" required placeholder="admin">
                        </div>
                        <div class="form-group">
                            <label>New Password *</label>
                            <input type="text" name="new_password" required placeholder="Enter new password">
                        </div>
                    </div>
                    <button type="submit" name="update_password" class="btn btn-success">
                        Update Password
                    </button>
                </form>
            </div>

            <!-- Generate Hash Only -->
            <div class="section">
                <h2>🔒 Generate Password Hash Only</h2>
                <p style="margin-bottom: 15px; font-size: 14px; color: #666;">
                    Generate a bcrypt hash to manually insert into the database
                </p>
                <form method="POST">
                    <div class="form-group">
                        <label>Password</label>
                        <input type="text" name="password" required placeholder="Enter password to hash">
                    </div>
                    <button type="submit" name="generate_hash" class="btn btn-warning">
                        Generate Hash
                    </button>
                </form>
                
                <?php if ($generated_hash): ?>
                    <div class="hash-output">
                        <strong>Generated Hash:</strong><br>
                        <?php echo $generated_hash; ?>
                    </div>
                    <div class="note" style="margin-top: 10px;">
                        Copy this hash and update it in phpMyAdmin:<br>
                        <code style="background: white; padding: 4px 8px; border-radius: 4px;">
                            UPDATE admin_users SET password = '<?php echo $generated_hash; ?>' WHERE username = 'admin';
                        </code>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Existing Admins -->
            <?php if (count($admins) > 0): ?>
                <div class="section">
                    <h2>👥 Existing Admin Accounts</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Created</th>
                                <th>Last Login</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $admin): ?>
                                <tr>
                                    <td><?php echo $admin['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($admin['username']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($admin['created_at'])); ?></td>
                                    <td><?php echo $admin['last_login'] ? date('d M Y H:i', strtotime($admin['last_login'])) : 'Never'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Instructions -->
            <div class="section" style="background: #fef3c7; border-color: #f59e0b;">
                <h2>📋 Instructions</h2>
                <ol style="margin-left: 20px; line-height: 2;">
                    <li><strong>Create Admin:</strong> Use the form above to create your admin account</li>
                    <li><strong>Save Credentials:</strong> Write down your username and password somewhere safe</li>
                    <li><strong>Test Login:</strong> Go to <code style="background: white; padding: 2px 6px; border-radius: 4px;">/admin/login.php</code> and test login</li>
                    <li><strong>DELETE THIS FILE:</strong> Once you've created your admin account, DELETE this file immediately!</li>
                </ol>
            </div>

            <!-- Security Reminder -->
            <div class="section" style="background: #fee2e2; border-color: #dc2626;">
                <h2 style="color: #dc2626;">🚨 FINAL SECURITY REMINDER</h2>
                <p style="font-size: 16px; font-weight: 600; color: #991b1b; margin-bottom: 15px;">
                    This file is extremely dangerous on a production server!
                </p>
                <p style="margin-bottom: 10px; color: #991b1b;">
                    Anyone who can access this page can create admin accounts or see existing usernames.
                </p>
                <p style="font-weight: 600; color: #dc2626;">
                    ⚠️ DELETE create-admin.php NOW after creating your admin account!
                </p>
            </div>

            <!-- Quick Actions -->
            <div style="display: flex; gap: 15px; margin-top: 25px; flex-wrap: wrap;">
                <a href="login.php" class="btn btn-primary" style="text-decoration: none;">
                    Go to Admin Login
                </a>
                <a href="../index.php" class="btn btn-success" style="text-decoration: none;">
                    View Website
                </a>
                <button onclick="if(confirm('Are you sure you want to delete this file?')) alert('You need to manually delete create-admin.php from your server via FTP or File Manager');" class="btn btn-danger">
                    Delete This File
                </button>
            </div>
        </div>
    </div>

    <script>
        // Auto-generate strong password
        function generatePassword() {
            const length = 16;
            const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
            let password = "";
            for (let i = 0; i < length; i++) {
                password += charset.charAt(Math.floor(Math.random() * charset.length));
            }
            return password;
        }

        // Add generate button to password fields
        document.querySelectorAll('input[type="text"][name*="password"]').forEach(input => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = '🎲 Generate';
            btn.className = 'btn btn-warning';
            btn.style.marginTop = '8px';
            btn.onclick = () => {
                input.value = generatePassword();
            };
            input.parentElement.appendChild(btn);
        });
    </script>
</body>
</html>