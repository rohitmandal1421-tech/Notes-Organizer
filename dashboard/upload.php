<?php
/**
 * Upload Notes Page
 * Allows students to upload and share their notes
 */

session_start();
require_once '../config/database.php';

// Check if user is logged in
requireLogin();

$user_id = getLoggedInUserId();
$user_name = getLoggedInUserName();

// Get database connection
$conn = getDatabaseConnection();

// Fetch courses for dropdown
$courses = [];
$query = "SELECT course_id, course_name FROM courses ORDER BY course_name";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}

// Fetch subjects for dropdown (initially all subjects, will be filtered by JS)
$subjects = [];
$query = "SELECT subject_id, subject_name, course_id, semester FROM subjects ORDER BY subject_name";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
}

closeDatabaseConnection($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Notes - Student Notes Organizer</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-brand">📚 Notes Organizer</a>
            <ul class="nav-menu">
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="upload.php">Upload Notes</a></li>
                <li><a href="my-notes.php">My Notes</a></li>
                <li><a href="browse.php">Browse Notes</a></li>
            </ul>
            <div class="nav-user">
                <span>👤 <?php echo htmlspecialchars($user_name); ?></span>
                <a href="../auth/logout.php" class="btn btn-danger" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">📤 Upload Notes</h1>
            <p class="page-subtitle">Share your knowledge with fellow students</p>
        </div>

        <?php
        // Display messages
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['error']) . '</div>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
            unset($_SESSION['success']);
        }
        ?>

        <!-- Upload Form -->
        <div class="card">
            <div class="card-header">📝 Note Details</div>
            <div class="card-body">
                <form action="../actions/upload_action.php" method="POST" enctype="multipart/form-data" id="uploadForm">
                    
                    <div class="form-group">
                        <label for="title" class="form-label">Note Title *</label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            class="form-input" 
                            required
                            placeholder="e.g., Data Structures - Complete Notes"
                            maxlength="200"
                        >
                        <span class="form-error" id="title-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea 
                            id="description" 
                            name="description" 
                            class="form-textarea" 
                            placeholder="Provide a brief description of the notes content..."
                            rows="4"
                        ></textarea>
                    </div>

                    <div class="form-group">
                        <label for="course" class="form-label">Course *</label>
                        <select id="course" name="course" class="form-select" required>
                            <option value="">Select Course</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course['course_id']; ?>">
                                    <?php echo htmlspecialchars($course['course_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="form-error" id="course-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="semester" class="form-label">Semester *</label>
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
                        <label for="subject" class="form-label">Subject *</label>
                        <select id="subject" name="subject" class="form-select" required disabled>
                            <option value="">Select Course and Semester first</option>
                        </select>
                        <span class="form-error" id="subject-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="visibility" class="form-label">Visibility *</label>
                        <select id="visibility" name="visibility" class="form-select" required>
                            <option value="public">Public - Anyone can view and download</option>
                            <option value="private">Private - Only visible to you</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="file" class="form-label">Upload File *</label>
                        <input 
                            type="file" 
                            id="file" 
                            name="file" 
                            class="form-input" 
                            required
                            accept=".pdf,.doc,.docx,.txt,.ppt,.pptx"
                        >
                        <small style="color: #6b7280; display: block; margin-top: 0.5rem;">
                            Allowed formats: PDF, DOC, DOCX, TXT, PPT, PPTX (Max size: 10MB)
                        </small>
                        <span class="form-error" id="file-error"></span>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">
                            📤 Upload Notes
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <!-- Upload Guidelines -->
        <div class="card">
            <div class="card-header">📋 Upload Guidelines</div>
            <div class="card-body">
                <ul style="line-height: 1.8; color: #374151;">
                    <li>✅ Ensure notes are well-organized and readable</li>
                    <li>✅ Use descriptive titles for easy searching</li>
                    <li>✅ Upload only your own work or properly attributed materials</li>
                    <li>✅ Check file size before uploading (max 10MB)</li>
                    <li>✅ Supported formats: PDF, Word, PowerPoint, Text files</li>
                    <li>❌ Do not upload copyrighted materials without permission</li>
                    <li>❌ Avoid uploading incomplete or draft notes</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2024 Student Notes Organizer. Academic Micro-Project.</p>
    </footer>

    <script>
        // Store all subjects data
        const allSubjects = <?php echo json_encode($subjects); ?>;
        
        // Subject filtering based on course and semester
        const courseSelect = document.getElementById('course');
        const semesterSelect = document.getElementById('semester');
        const subjectSelect = document.getElementById('subject');
        
        function updateSubjects() {
            const courseId = courseSelect.value;
            const semester = semesterSelect.value;
            
            // Clear current options
            subjectSelect.innerHTML = '<option value="">Select Subject</option>';
            
            if (courseId && semester) {
                // Filter subjects
                const filteredSubjects = allSubjects.filter(subject => 
                    subject.course_id == courseId && subject.semester == semester
                );
                
                if (filteredSubjects.length > 0) {
                    filteredSubjects.forEach(subject => {
                        const option = document.createElement('option');
                        option.value = subject.subject_id;
                        option.textContent = subject.subject_name;
                        subjectSelect.appendChild(option);
                    });
                    subjectSelect.disabled = false;
                } else {
                    subjectSelect.innerHTML = '<option value="">No subjects available</option>';
                    subjectSelect.disabled = true;
                }
            } else {
                subjectSelect.innerHTML = '<option value="">Select Course and Semester first</option>';
                subjectSelect.disabled = true;
            }
        }
        
        courseSelect.addEventListener('change', updateSubjects);
        semesterSelect.addEventListener('change', updateSubjects);
        
        // Form validation
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            let isValid = true;
            
            // Clear previous errors
            document.querySelectorAll('.form-error').forEach(el => el.textContent = '');
            
            // Validate title
            const title = document.getElementById('title').value.trim();
            if (title.length < 5) {
                document.getElementById('title-error').textContent = 'Title must be at least 5 characters';
                isValid = false;
            }
            
            // Validate file
            const fileInput = document.getElementById('file');
            if (fileInput.files.length === 0) {
                document.getElementById('file-error').textContent = 'Please select a file to upload';
                isValid = false;
            } else {
                const file = fileInput.files[0];
                const maxSize = 10 * 1024 * 1024; // 10MB
                
                if (file.size > maxSize) {
                    document.getElementById('file-error').textContent = 'File size must not exceed 10MB';
                    isValid = false;
                }
                
                const allowedExtensions = ['pdf', 'doc', 'docx', 'txt', 'ppt', 'pptx'];
                const fileName = file.name.toLowerCase();
                const fileExtension = fileName.split('.').pop();
                
                if (!allowedExtensions.includes(fileExtension)) {
                    document.getElementById('file-error').textContent = 'Invalid file format. Allowed: PDF, DOC, DOCX, TXT, PPT, PPTX';
                    isValid = false;
                }
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
