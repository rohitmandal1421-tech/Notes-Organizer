<?php
/**
 * Student Registration Page
 * Allows new students to create an account
 */

// Start session
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard/index.php");
    exit();
}

// Include database configuration
require_once '../config/database.php';

// Fetch courses for dropdown
$conn = getDatabaseConnection();
$courses = [];
if ($conn) {
    $query = "SELECT course_id, course_name FROM courses ORDER BY course_name";
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - Notes Organizer</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-title">📚 Create Account</h1>
                <p class="auth-subtitle">Join the student notes community</p>
            </div>

            <?php
            // Display error or success messages from session
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['error']) . '</div>';
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
                unset($_SESSION['success']);
            }
            ?>

            <form action="../actions/register_action.php" method="POST" id="registerForm">
                <div class="form-group">
                    <label for="full_name" class="form-label">Full Name *</label>
                    <input 
                        type="text" 
                        id="full_name" 
                        name="full_name" 
                        class="form-input" 
                        required
                        placeholder="Enter your full name"
                    >
                    <span class="form-error" id="name-error"></span>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email Address *</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        required
                        placeholder="student@university.edu"
                    >
                    <span class="form-error" id="email-error"></span>
                </div>

                <div class="form-group">
                    <label for="enrollment_no" class="form-label">Enrollment Number *</label>
                    <input 
                        type="text" 
                        id="enrollment_no" 
                        name="enrollment_no" 
                        class="form-input" 
                        required
                        placeholder="e.g., CSE2024001"
                    >
                    <span class="form-error" id="enrollment-error"></span>
                </div>

                <div class="form-group">
                    <label for="department" class="form-label">Department/Course *</label>
                    <select id="department" name="department" class="form-select" required>
                        <option value="">Select Department</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo htmlspecialchars($course['course_name']); ?>">
                                <?php echo htmlspecialchars($course['course_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="form-error" id="department-error"></span>
                </div>

                <div class="form-group">
                    <label for="semester" class="form-label">Current Semester *</label>
                    <select id="semester" name="semester" class="form-select" required>
                        <option value="">Select Semester</option>
                        <option value="1">Semester 1</option>
                        <option value="2">Semester 2</option>
                        <option value="3">Semester 3</option>
                        <option value="4">Semester 4</option>
                        <option value="5">Semester 5</option>
                        <option value="6">Semester 6</option>
                        <option value="7">Semester 7</option>
                        <option value="8">Semester 8</option>
                    </select>
                    <span class="form-error" id="semester-error"></span>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password *</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        required
                        placeholder="Minimum 6 characters"
                    >
                    <span class="form-error" id="password-error"></span>
                </div>

                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm Password *</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        class="form-input" 
                        required
                        placeholder="Re-enter password"
                    >
                    <span class="form-error" id="confirm-password-error"></span>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Register Account
                </button>
            </form>

            <div class="auth-footer">
                Already have an account? 
                <a href="login.php">Login here</a>
            </div>
        </div>
    </div>

    <script src="../js/validation.js"></script>
    <script>
        // Client-side validation for registration form
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            let isValid = true;
            
            // Clear previous errors
            document.querySelectorAll('.form-error').forEach(el => el.textContent = '');
            
            // Validate full name
            const fullName = document.getElementById('full_name').value.trim();
            if (fullName.length < 3) {
                document.getElementById('name-error').textContent = 'Name must be at least 3 characters';
                isValid = false;
            }
            
            // Validate email
            const email = document.getElementById('email').value.trim();
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                document.getElementById('email-error').textContent = 'Please enter a valid email';
                isValid = false;
            }
            
            // Validate enrollment number
            const enrollmentNo = document.getElementById('enrollment_no').value.trim();
            if (enrollmentNo.length < 5) {
                document.getElementById('enrollment-error').textContent = 'Invalid enrollment number';
                isValid = false;
            }
            
            // Validate password
            const password = document.getElementById('password').value;
            if (password.length < 6) {
                document.getElementById('password-error').textContent = 'Password must be at least 6 characters';
                isValid = false;
            }
            
            // Validate password confirmation
            const confirmPassword = document.getElementById('confirm_password').value;
            if (password !== confirmPassword) {
                document.getElementById('confirm-password-error').textContent = 'Passwords do not match';
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>

<?php
// Close database connection
if ($conn) {
    closeDatabaseConnection($conn);
}
?>
