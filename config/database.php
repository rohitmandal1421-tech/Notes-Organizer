<?php


// Database Configuration Constants
define('DB_HOST', 'localhost');        // Database host (usually 'localhost')
define('DB_USER', 'root');             // MySQL username (default: 'root' for XAMPP/WAMP)
define('DB_PASS', '');                 // MySQL password (default: empty for XAMPP/WAMP)
define('DB_NAME', 'student_notes_organizer');  // Database name

// Application Configuration
define('BASE_URL', 'http://localhost/student-notes-organizer/');  // Update this to your project URL
define('UPLOAD_DIR', __DIR__ . '/../uploads/notes/');  // Directory for uploaded files
define('MAX_FILE_SIZE', 10485760);  // 10MB in bytes
define('ALLOWED_FILE_TYPES', ['pdf', 'doc', 'docx', 'txt', 'ppt', 'pptx']);

// Timezone Configuration
date_default_timezone_set('Asia/Kolkata');  // Change according to your timezone

/**
 * Database Connection Function
 * Creates and returns a MySQLi connection object
 * 
 * @return mysqli|false Database connection object or false on failure
 */
function getDatabaseConnection() {
    // Create connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        // Log error (in production, log to file instead of displaying)
        error_log("Database Connection Failed: " . $conn->connect_error);
        return false;
    }
    
    // Set charset to utf8mb4 for proper character support
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

/**
 * Close Database Connection
 * 
 * @param mysqli $conn Database connection object
 */
function closeDatabaseConnection($conn) {
    if ($conn && !$conn->connect_error) {
        $conn->close();
    }
}

/**
 * Sanitize Input Data
 * Prevents XSS attacks by cleaning user input
 * 
 * @param string $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate Email Format
 * 
 * @param string $email Email address to validate
 * @return bool True if valid, false otherwise
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Create Upload Directory if it doesn't exist
 */
function ensureUploadDirectory() {
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
}

// Create upload directory on initialization
ensureUploadDirectory();

/**
 * Display Error Message
 * 
 * @param string $message Error message to display
 */
function showError($message) {
    echo '<div class="alert alert-error">' . htmlspecialchars($message) . '</div>';
}

/**
 * Display Success Message
 * 
 * @param string $message Success message to display
 */
function showSuccess($message) {
    echo '<div class="alert alert-success">' . htmlspecialchars($message) . '</div>';
}

/**
 * Redirect to a page
 * 
 * @param string $url URL to redirect to
 */
function redirectTo($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Check if user is logged in
 * 
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Require Login - Redirect to login page if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirectTo(BASE_URL . 'auth/login.php');
    }
}

/**
 * Get Logged-in User ID
 * 
 * @return int|null User ID or null if not logged in
 */
function getLoggedInUserId() {
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

/**
 * Get Logged-in User Name
 * 
 * @return string|null User name or null if not logged in
 */
function getLoggedInUserName() {
    return isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;
}

/**
 * Format File Size
 * Converts bytes to human-readable format
 * 
 * @param int $bytes File size in bytes
 * @return string Formatted file size
 */
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

/**
 * Time Ago Function
 * Converts timestamp to "time ago" format
 * 
 * @param string $datetime MySQL datetime string
 * @return string Time ago string
 */
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if ($difference < 60) {
        return 'just now';
    } elseif ($difference < 3600) {
        $minutes = floor($difference / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 86400) {
        $hours = floor($difference / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 604800) {
        $days = floor($difference / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M d, Y', $timestamp);
    }
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 
?>
