<?php
/**
 * Registration Action Handler
 * Processes student registration form submission
 */

session_start();
require_once '../config/database.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../auth/register.php");
    exit();
}

// Get and sanitize form data
$full_name = sanitizeInput($_POST['full_name'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$enrollment_no = sanitizeInput($_POST['enrollment_no'] ?? '');
$department = sanitizeInput($_POST['department'] ?? '');
$semester = isset($_POST['semester']) ? (int)$_POST['semester'] : 0;
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation array to store errors
$errors = [];

// Validate full name
if (empty($full_name) || strlen($full_name) < 3) {
    $errors[] = "Full name must be at least 3 characters long";
}

// Validate email
if (empty($email) || !validateEmail($email)) {
    $errors[] = "Please provide a valid email address";
}

// Validate enrollment number
if (empty($enrollment_no) || strlen($enrollment_no) < 5) {
    $errors[] = "Please provide a valid enrollment number";
}

// Validate department
if (empty($department)) {
    $errors[] = "Please select a department";
}

// Validate semester
if ($semester < 1 || $semester > 8) {
    $errors[] = "Please select a valid semester";
}

// Validate password
if (empty($password) || strlen($password) < 6) {
    $errors[] = "Password must be at least 6 characters long";
}

// Validate password confirmation
if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match";
}

// If there are validation errors, return to registration page
if (!empty($errors)) {
    $_SESSION['error'] = implode(', ', $errors);
    header("Location: ../auth/register.php");
    exit();
}

// Get database connection
$conn = getDatabaseConnection();

if (!$conn) {
    $_SESSION['error'] = "Database connection failed. Please try again later.";
    header("Location: ../auth/register.php");
    exit();
}

// Check if email already exists
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['error'] = "Email address is already registered";
    $stmt->close();
    closeDatabaseConnection($conn);
    header("Location: ../auth/register.php");
    exit();
}
$stmt->close();

// Check if enrollment number already exists
$stmt = $conn->prepare("SELECT user_id FROM users WHERE enrollment_no = ?");
$stmt->bind_param("s", $enrollment_no);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['error'] = "Enrollment number is already registered";
    $stmt->close();
    closeDatabaseConnection($conn);
    header("Location: ../auth/register.php");
    exit();
}
$stmt->close();

// Hash the password using PHP's password_hash function
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert new user into database
$stmt = $conn->prepare("INSERT INTO users (full_name, email, password, enrollment_no, department, semester, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("sssssi", $full_name, $email, $hashed_password, $enrollment_no, $department, $semester);

if ($stmt->execute()) {
    $user_id = $stmt->insert_id;
    
    // Log the registration activity
    $activity_stmt = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, activity_description, activity_date) VALUES (?, 'register', ?, NOW())");
    $activity_description = "New user registration: " . $full_name;
    $activity_stmt->bind_param("is", $user_id, $activity_description);
    $activity_stmt->execute();
    $activity_stmt->close();
    
    // Set success message
    $_SESSION['success'] = "Registration successful! Please login to continue.";
    
    $stmt->close();
    closeDatabaseConnection($conn);
    
    // Redirect to login page
    header("Location: ../auth/login.php");
    exit();
} else {
    $_SESSION['error'] = "Registration failed. Please try again.";
    $stmt->close();
    closeDatabaseConnection($conn);
    header("Location: ../auth/register.php");
    exit();
}
?>
