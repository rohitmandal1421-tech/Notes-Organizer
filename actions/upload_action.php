<?php
/**
 * Upload Action Handler
 * Processes file upload and saves note information to database
 */

session_start();
require_once '../config/database.php';

// Check if user is logged in
requireLogin();

$user_id = getLoggedInUserId();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../dashboard/upload.php");
    exit();
}

// Get and sanitize form data
$title = sanitizeInput($_POST['title'] ?? '');
$description = sanitizeInput($_POST['description'] ?? '');
$subject_id = isset($_POST['subject']) ? (int)$_POST['subject'] : 0;
$semester = isset($_POST['semester']) ? (int)$_POST['semester'] : 0;
$visibility = sanitizeInput($_POST['visibility'] ?? 'public');

// Validation array
$errors = [];

// Validate title
if (empty($title) || strlen($title) < 5) {
    $errors[] = "Title must be at least 5 characters long";
}

// Validate subject
if ($subject_id <= 0) {
    $errors[] = "Please select a valid subject";
}

// Validate semester
if ($semester < 1 || $semester > 8) {
    $errors[] = "Please select a valid semester";
}

// Validate visibility
if (!in_array($visibility, ['public', 'private'])) {
    $errors[] = "Invalid visibility option";
}

// Validate file upload
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $errors[] = "Please upload a valid file";
} else {
    $file = $_FILES['file'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];
    
    // Get file extension
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Validate file extension
    if (!in_array($file_ext, ALLOWED_FILE_TYPES)) {
        $errors[] = "Invalid file format. Allowed: " . implode(', ', ALLOWED_FILE_TYPES);
    }
    
    // Validate file size (10MB max)
    if ($file_size > MAX_FILE_SIZE) {
        $errors[] = "File size must not exceed " . formatFileSize(MAX_FILE_SIZE);
    }
}

// If there are validation errors, return to upload page
if (!empty($errors)) {
    $_SESSION['error'] = implode(', ', $errors);
    header("Location: ../dashboard/upload.php");
    exit();
}

// Generate unique file name to avoid conflicts
$unique_file_name = time() . '_' . uniqid() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file_name);
$upload_path = UPLOAD_DIR . $unique_file_name;

// Move uploaded file to destination
if (!move_uploaded_file($file_tmp, $upload_path)) {
    $_SESSION['error'] = "Failed to upload file. Please try again.";
    header("Location: ../dashboard/upload.php");
    exit();
}

// Get database connection
$conn = getDatabaseConnection();

if (!$conn) {
    // Delete uploaded file if database connection fails
    unlink($upload_path);
    $_SESSION['error'] = "Database connection failed. Please try again.";
    header("Location: ../dashboard/upload.php");
    exit();
}

// Insert note record into database
$stmt = $conn->prepare("INSERT INTO notes (user_id, subject_id, title, description, file_name, file_path, file_size, file_type, visibility, semester, upload_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

$stmt->bind_param(
    "iissssissi",
    $user_id,
    $subject_id,
    $title,
    $description,
    $file_name,
    $upload_path,
    $file_size,
    $file_ext,
    $visibility,
    $semester
);

if ($stmt->execute()) {
    $note_id = $stmt->insert_id;
    
    // Log the upload activity
    $activity_stmt = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, activity_description, activity_date) VALUES (?, 'upload', ?, NOW())");
    $activity_description = "Uploaded note: " . $title;
    $activity_stmt->bind_param("is", $user_id, $activity_description);
    $activity_stmt->execute();
    $activity_stmt->close();
    
    $_SESSION['success'] = "Notes uploaded successfully! Title: " . htmlspecialchars($title);
    $stmt->close();
    closeDatabaseConnection($conn);
    
    // Redirect to my-notes page
    header("Location: ../dashboard/my-notes.php");
    exit();
} else {
    // Delete uploaded file if database insert fails
    unlink($upload_path);
    $_SESSION['error'] = "Failed to save note information. Please try again.";
    $stmt->close();
    closeDatabaseConnection($conn);
    header("Location: ../dashboard/upload.php");
    exit();
}
?>
