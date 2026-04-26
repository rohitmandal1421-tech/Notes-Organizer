<?php
/**
 * Student Login Page
 * Allows registered students to access their account
 */

session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard/index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - Notes Organizer</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-title">📚 Student Login</h1>
                <p class="auth-subtitle">Access your notes library</p>
            </div>

            <?php
            // Display error or success messages
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['error']) . '</div>';
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
                unset($_SESSION['success']);
            }
            ?>

            <form action="../actions/login_action.php" method="POST" id="loginForm">
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        required
                        placeholder="student@university.edu"
                        autofocus
                    >
                    <span class="form-error" id="email-error"></span>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        required
                        placeholder="Enter your password"
                    >
                    <span class="form-error" id="password-error"></span>
                </div>

                <div class="form-group">
                    <label class="flex" style="align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="remember_me" style="cursor: pointer;">
                        <span>Remember me</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Login to Dashboard
                </button>
            </form>

            <div class="auth-footer">
                Don't have an account? 
                <a href="register.php">Register here</a>
            </div>

            <div class="auth-footer mt-2">
                <small style="color: #6b7280;">
                    Demo Credentials: john.doe@student.edu / student123
                </small>
            </div>
        </div>
    </div>

    <script>
        // Client-side validation for login form
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            let isValid = true;
            
            // Clear previous errors
            document.querySelectorAll('.form-error').forEach(el => el.textContent = '');
            
            // Validate email
            const email = document.getElementById('email').value.trim();
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                document.getElementById('email-error').textContent = 'Please enter a valid email';
                isValid = false;
            }
            
            // Validate password
            const password = document.getElementById('password').value;
            if (password.length < 1) {
                document.getElementById('password-error').textContent = 'Please enter your password';
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
