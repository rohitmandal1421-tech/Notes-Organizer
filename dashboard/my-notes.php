<?php
/**
 * My Notes Page
 * Allows users to view, edit, and delete their uploaded notes
 */

session_start();
require_once '../config/database.php';

// Check if user is logged in
requireLogin();

$user_id = getLoggedInUserId();
$user_name = getLoggedInUserName();

// Get database connection
$conn = getDatabaseConnection();

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $note_id = (int)$_GET['delete'];
    
    // Verify note belongs to user
    $stmt = $conn->prepare("SELECT file_path FROM notes WHERE note_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $note_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $note = $result->fetch_assoc();
        
        // Delete file from server
        if (file_exists($note['file_path'])) {
            unlink($note['file_path']);
        }
        
        // Delete from database (downloads will be deleted due to CASCADE)
        $delete_stmt = $conn->prepare("DELETE FROM notes WHERE note_id = ? AND user_id = ?");
        $delete_stmt->bind_param("ii", $note_id, $user_id);
        
        if ($delete_stmt->execute()) {
            $_SESSION['success'] = "Note deleted successfully";
        } else {
            $_SESSION['error'] = "Failed to delete note";
        }
        $delete_stmt->close();
    }
    $stmt->close();
    
    header("Location: my-notes.php");
    exit();
}

// Fetch user's notes
$my_notes = [];
$stmt = $conn->prepare("SELECT n.*, s.subject_name, s.subject_code 
    FROM notes n 
    JOIN subjects s ON n.subject_id = s.subject_id 
    WHERE n.user_id = ? 
    ORDER BY n.upload_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $my_notes[] = $row;
}
$stmt->close();
closeDatabaseConnection($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Notes - Student Notes Organizer</title>
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
            <h1 class="page-title">📝 My Notes</h1>
            <p class="page-subtitle">Manage your uploaded notes</p>
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

        <!-- Filter and Search -->
        <div class="card">
            <div class="card-body">
                <div class="search-filter-bar">
                    <div class="search-box">
                        <input 
                            type="text" 
                            id="searchInput" 
                            class="search-input" 
                            placeholder="🔍 Search notes by title or subject..."
                        >
                    </div>
                    <select id="filterVisibility" class="form-select" style="max-width: 200px;">
                        <option value="">All Visibility</option>
                        <option value="public">Public Only</option>
                        <option value="private">Private Only</option>
                    </select>
                    <select id="filterSemester" class="form-select" style="max-width: 200px;">
                        <option value="">All Semesters</option>
                        <option value="1">Semester 1</option>
                        <option value="2">Semester 2</option>
                        <option value="3">Semester 3</option>
                        <option value="4">Semester 4</option>
                        <option value="5">Semester 5</option>
                        <option value="6">Semester 6</option>
                        <option value="7">Semester 7</option>
                        <option value="8">Semester 8</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Notes Table -->
        <div class="card">
            <div class="card-header flex-between">
                <span>📚 Your Notes (<?php echo count($my_notes); ?>)</span>
                <a href="upload.php" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                    + Upload New
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($my_notes)): ?>
                    <div style="text-align: center; padding: 3rem; color: #6b7280;">
                        <div style="font-size: 4rem; margin-bottom: 1rem;">📭</div>
                        <h3 style="margin-bottom: 0.5rem;">No notes yet</h3>
                        <p>Start sharing your knowledge by uploading your first note!</p>
                        <a href="upload.php" class="btn btn-primary" style="margin-top: 1rem;">
                            Upload Your First Note
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="table" id="notesTable">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Subject</th>
                                    <th>Semester</th>
                                    <th>Visibility</th>
                                    <th>Downloads</th>
                                    <th>Size</th>
                                    <th>Uploaded</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($my_notes as $note): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($note['title']); ?></strong>
                                            <?php if (!empty($note['description'])): ?>
                                                <br><small style="color: #6b7280;">
                                                    <?php echo htmlspecialchars(substr($note['description'], 0, 100)); ?>
                                                    <?php echo strlen($note['description']) > 100 ? '...' : ''; ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary">
                                                <?php echo htmlspecialchars($note['subject_name']); ?>
                                            </span>
                                        </td>
                                        <td>Sem <?php echo $note['semester']; ?></td>
                                        <td>
                                            <?php if ($note['visibility'] === 'public'): ?>
                                                <span class="badge badge-success">🌍 Public</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">🔒 Private</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo $note['download_count']; ?></strong> downloads
                                        </td>
                                        <td><?php echo formatFileSize($note['file_size']); ?></td>
                                        <td><?php echo timeAgo($note['upload_date']); ?></td>
                                        <td>
                                            <div class="table-actions">
                                                <a 
                                                    href="../actions/download_action.php?id=<?php echo $note['note_id']; ?>" 
                                                    class="btn btn-outline" 
                                                    style="padding: 0.35rem 0.75rem; font-size: 0.85rem;"
                                                    title="Download"
                                                >
                                                    📥
                                                </a>
                                                <a 
                                                    href="?delete=<?php echo $note['note_id']; ?>" 
                                                    class="btn btn-danger" 
                                                    style="padding: 0.35rem 0.75rem; font-size: 0.85rem;"
                                                    onclick="return confirm('Are you sure you want to delete this note? This action cannot be undone.');"
                                                    title="Delete"
                                                >
                                                    🗑️
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2024 Student Notes Organizer. Academic Micro-Project.</p>
    </footer>

    <script>
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const visibilityFilter = document.getElementById('filterVisibility');
        const semesterFilter = document.getElementById('filterSemester');
        const tableBody = document.querySelector('#notesTable tbody');
        const rows = tableBody ? Array.from(tableBody.getElementsByTagName('tr')) : [];

        function filterTable() {
            const searchTerm = searchInput.value.toLowerCase();
            const visibilityValue = visibilityFilter.value.toLowerCase();
            const semesterValue = semesterFilter.value;

            rows.forEach(row => {
                const title = row.cells[0].textContent.toLowerCase();
                const subject = row.cells[1].textContent.toLowerCase();
                const semester = row.cells[2].textContent;
                const visibility = row.cells[3].textContent.toLowerCase();

                const matchesSearch = title.includes(searchTerm) || subject.includes(searchTerm);
                const matchesVisibility = !visibilityValue || visibility.includes(visibilityValue);
                const matchesSemester = !semesterValue || semester.includes(semesterValue);

                if (matchesSearch && matchesVisibility && matchesSemester) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', filterTable);
            visibilityFilter.addEventListener('change', filterTable);
            semesterFilter.addEventListener('change', filterTable);
        }
    </script>
</body>
</html>
