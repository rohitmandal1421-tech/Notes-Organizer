<?php
/**
 * Download Action Handler
 * Handles file downloads and tracks download statistics
 */

session_start();
require_once '../config/database.php';

// Check if user is logged in
requireLogin();

$user_id = getLoggedInUserId();

// Check if note ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid note ID";
    header("Location: ../dashboard/browse.php");
    exit();
}

$note_id = (int)$_GET['id'];

// Get database connection
$conn = getDatabaseConnection();

if (!$conn) {
    $_SESSION['error'] = "Database connection failed";
    header("Location: ../dashboard/browse.php");
    exit();
}

// Fetch note information
$stmt = $conn->prepare("SELECT n.*, u.full_name as uploader_name 
    FROM notes n 
    JOIN users u ON n.user_id = u.user_id 
    WHERE n.note_id = ?");
$stmt->bind_param("i", $note_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Note not found";
    $stmt->close();
    closeDatabaseConnection($conn);
    header("Location: ../dashboard/browse.php");
    exit();
}

$note = $result->fetch_assoc();
$stmt->close();

// Check if file exists
if (!file_exists($note['file_path'])) {
    $_SESSION['error'] = "File not found on server";
    closeDatabaseConnection($conn);
    header("Location: ../dashboard/browse.php");
    exit();
}

// Check if note is private and user is not the owner
if ($note['visibility'] === 'private' && $note['user_id'] != $user_id) {
    $_SESSION['error'] = "You don't have permission to download this note";
    closeDatabaseConnection($conn);
    header("Location: ../dashboard/browse.php");
    exit();
}

// Record download in downloads table (only if not downloading own note)
if ($note['user_id'] != $user_id) {
    $download_stmt = $conn->prepare("INSERT INTO downloads (note_id, user_id, download_date) VALUES (?, ?, NOW())");
    $download_stmt->bind_param("ii", $note_id, $user_id);
    $download_stmt->execute();
    $download_stmt->close();
    
    // Increment download count in notes table
    $update_stmt = $conn->prepare("UPDATE notes SET download_count = download_count + 1 WHERE note_id = ?");
    $update_stmt->bind_param("i", $note_id);
    $update_stmt->execute();
    $update_stmt->close();
    
    // Log the download activity
    $activity_stmt = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, activity_description, activity_date) VALUES (?, 'download', ?, NOW())");
    $activity_description = "Downloaded note: " . $note['title'];
    $activity_stmt->bind_param("is", $user_id, $activity_description);
    $activity_stmt->execute();
    $activity_stmt->close();
}

closeDatabaseConnection($conn);

// Serve the file for download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $note['file_name'] . '"');
header('Content-Length: ' . filesize($note['file_path']));
header('Cache-Control: must-revalidate');
header('Pragma: public');

// Clear output buffer
ob_clean();
flush();

// Read and output file
readfile($note['file_path']);
exit();
?>
