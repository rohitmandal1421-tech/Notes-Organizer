<?php
/**
 * Login Action Handler
 * Processes student login and creates session
 */

session_start();
require_once '../config/database.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../auth/login.php");
    exit();
}

// Get and sanitize form data
$email = sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember_me = isset($_POST['remember_me']);

// Validate inputs
if (empty($email) || !validateEmail($email)) {
    $_SESSION['error'] = "Please provide a valid email address";
    header("Location: ../auth/login.php");
    exit();
}

if (empty($password)) {
    $_SESSION['error'] = "Please provide your password";
    header("Location: ../auth/login.php");
    exit();
}

// Get database connection
$conn = getDatabaseConnection();

if (!$conn) {
    $_SESSION['error'] = "Database connection failed. Please try again later.";
    header("Location: ../auth/login.php");
    exit();
}

// Fetch user from database
$stmt = $conn->prepare("SELECT user_id, full_name, email, password, enrollment_no, department, semester FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Invalid email or password";
    $stmt->close();
    closeDatabaseConnection($conn);
    header("Location: ../auth/login.php");
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

// Verify password
if (!password_verify($password, $user['password'])) {
    $_SESSION['error'] = "Invalid email or password";
    closeDatabaseConnection($conn);
    header("Location: ../auth/login.php");
    exit();
}

// Password is correct - create session
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['user_name'] = $user['full_name'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['enrollment_no'] = $user['enrollment_no'];
$_SESSION['department'] = $user['department'];
$_SESSION['semester'] = $user['semester'];

// Update last login timestamp
$update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
$update_stmt->bind_param("i", $user['user_id']);
$update_stmt->execute();
$update_stmt->close();

// Log the login activity
$activity_stmt = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, activity_description, activity_date) VALUES (?, 'login', ?, NOW())");
$activity_description = "User logged in: " . $user['full_name'];
$activity_stmt->bind_param("is", $user['user_id'], $activity_description);
$activity_stmt->execute();
$activity_stmt->close();

closeDatabaseConnection($conn);

// Redirect to dashboard
header("Location: ../dashboard/index.php");
exit();
?>
