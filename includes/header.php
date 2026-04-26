<?php
/**
 * Common Header Include File
 * This file contains the navigation bar that appears on all authenticated pages
 * 
 * Usage: include_once '../includes/header.php';
 */

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get user information from session
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Guest';
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';
$is_logged_in = isset($_SESSION['user_id']);

// Determine current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Student Notes Organizer - Upload, Share, and Download Academic Notes">
    <meta name="author" content="Student Notes Organizer Team">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Student Notes Organizer</title>
    
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>css/style.css">
    
    <!-- Favicon (optional) -->
    <link rel="icon" type="image/png" href="<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>assets/favicon.png">
    
    <!-- Additional CSS if needed -->
    <?php if (isset($additional_css)): ?>
        <?php echo $additional_css; ?>
    <?php endif; ?>
</head>
<body>
    
<?php if ($is_logged_in): ?>
    <!-- Navigation Bar for Authenticated Users -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>dashboard/index.php" class="nav-brand">
                📚 Notes Organizer
            </a>
            
            <ul class="nav-menu">
                <li>
                    <a href="<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>dashboard/index.php" 
                       class="<?php echo ($current_page == 'index.php' && strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false) ? 'active' : ''; ?>">
                        🏠 Dashboard
                    </a>
                </li>
                <li>
                    <a href="<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>dashboard/upload.php" 
                       class="<?php echo $current_page == 'upload.php' ? 'active' : ''; ?>">
                        📤 Upload Notes
                    </a>
                </li>
                <li>
                    <a href="<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>dashboard/my-notes.php" 
                       class="<?php echo $current_page == 'my-notes.php' ? 'active' : ''; ?>">
                        📝 My Notes
                    </a>
                </li>
                <li>
                    <a href="<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>dashboard/all-notes.php" 
                       class="<?php echo $current_page == 'all-notes.php' ? 'active' : ''; ?>">
                        📚 All Notes
                    </a>
                </li>
                <li>
                    <a href="<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>dashboard/browse.php" 
                       class="<?php echo $current_page == 'browse.php' ? 'active' : ''; ?>">
                        🔍 Browse Notes
                    </a>
                </li>
            </ul>
            
            <div class="nav-user">
                <span class="user-info" title="<?php echo htmlspecialchars($user_email); ?>">
                    👤 <?php echo htmlspecialchars($user_name); ?>
                </span>
                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>auth/logout.php" 
                   class="btn btn-danger" 
                   style="padding: 0.5rem 1rem; font-size: 0.9rem;"
                   onclick="return confirm('Are you sure you want to logout?');">
                    🚪 Logout
                </a>
            </div>
        </div>
    </nav>
<?php else: ?>
    <!-- Navigation Bar for Guest Users -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>index.php" class="nav-brand">
                📚 Notes Organizer
            </a>
            
            <ul class="nav-menu">
                <li>
                    <a href="<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>index.php">
                        🏠 Home
                    </a>
                </li>
                <li>
                    <a href="<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>auth/login.php">
                        🔐 Login
                    </a>
                </li>
                <li>
                    <a href="<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>auth/register.php" 
                       class="btn btn-primary" 
                       style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                        ✨ Register
                    </a>
                </li>
            </ul>
        </div>
    </nav>
<?php endif; ?>

<!-- Main Content Wrapper Starts Here -->
<div class="main-content">