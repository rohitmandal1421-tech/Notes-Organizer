<?php
/**
 * Logout Handler
 * Destroys user session and redirects to login page
 */

session_start();

// Store user name for goodbye message (optional)
$user_name = $_SESSION['user_name'] ?? 'Student';

// Unset all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Start a new session for the success message
session_start();
$_SESSION['success'] = "You have been successfully logged out. See you soon, " . htmlspecialchars($user_name) . "!";

// Redirect to login page
header("Location: login.php");
exit();
?>
