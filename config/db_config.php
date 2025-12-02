<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Change to your MySQL username
define('DB_PASS', ''); // Change to your MySQL password
define('DB_NAME', 'driving_school');

// Site Configuration
define('SITE_URL', 'http://localhost/anab_driving'); // Change to your domain
define('ADMIN_EMAIL', 'anabdrivingschool@gmail.com');

// Security
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds

// File Upload Settings
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Email Settings (for booking notifications)
//define('SMTP_HOST', 'smtp.gmail.com');
//define('SMTP_PORT', 587);
//define('SMTP_USER', 'anabdrivingschool@gmail.com');
//define('SMTP_PASS', ''); // App-specific password for Gmail
//define('SMTP_FROM', 'anabdrivingschool@gmail.com');
//define('SMTP_FROM_NAME', 'Anab Driving School');

// Email Settings - SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'sagorahamed995@gmail.com'); // Your Gmail
define('SMTP_PASS', 'yhmvrvcgubifwuxp'); // Your 16-char App Password
define('SMTP_FROM', 'sagorahamed995@gmail.com');
define('SMTP_FROM_NAME', 'Anab Driving School');
define('SMTP_ENCRYPTION', 'tls');

// Twilio SMS Settings (Optional - for SMS notifications)
define('TWILIO_SID', ''); // Your Twilio Account SID
define('TWILIO_TOKEN', ''); // Your Twilio Auth Token
define('TWILIO_FROM', ''); // Your Twilio phone number

// Create uploads directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Database Connection Class
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            die("Database Connection Failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    // Prevent cloning of the instance
    private function __clone() {}

    // Prevent unserialization of the instance
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Helper Functions
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function is_admin_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function require_admin_login() {
    if (!is_admin_logged_in()) {
        redirect(SITE_URL . '/admin/login.php');
    }
}

// Check session timeout
function check_session_timeout() {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        return false;
    }
    $_SESSION['last_activity'] = time();
    return true;
}
?>