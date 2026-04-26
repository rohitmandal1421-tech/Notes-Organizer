<?php
/**
 * Browse Notes Page
 * Allows users to view and download public notes from other students
 */

session_start();
require_once '../config/database.php';

// Check if user is logged in
requireLogin();

$user_id = getLoggedInUserId();
$user_name = getLoggedInUserName();

// Get database connection
$conn = getDatabaseConnection();

// Fetch all public notes (excluding user's own notes)
$all_notes = [];
$stmt = $conn->prepare("SELECT n.*, s.subject_name, s.subject_code, u.full_name as uploader_name, u.enrollment_no
    FROM notes n 
    JOIN subjects s ON n.subject_id = s.subject_id 
    JOIN users u ON n.user_id = u.user_id
    WHERE n.visibility = 'public' AND n.user_id != ?
    ORDER BY n.upload_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $all_notes[] = $row;
}
$stmt->close();

// Get available subjects for filter
$subjects = [];
$query = "SELECT DISTINCT s.subject_id, s.subject_name FROM subjects s 
    JOIN notes n ON s.subject_id = n.subject_id 
    WHERE n.visibility = 'public' 
    ORDER BY s.subject_name";
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
    <title>Browse Notes - Student Notes Organizer</title>
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
            <h1 class="page-title">🔍 Browse Notes</h1>
            <p class="page-subtitle">Discover and download notes shared by fellow students</p>
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

        <!-- Search and Filter -->
        <div class="card">
            <div class="card-body">
                <div class="search-filter-bar">
                    <div class="search-box">
                        <input 
                            type="text" 
                            id="searchInput" 
                            class="search-input" 
                            placeholder="🔍 Search by title, subject, or uploader..."
                        >
                    </div>
                    <select id="filterSubject" class="form-select" style="max-width: 250px;">
                        <option value="">All Subjects</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?php echo htmlspecialchars($subject['subject_name']); ?>">
                                <?php echo htmlspecialchars($subject['subject_name']); ?>
                            </option>
                        <?php endforeach; ?>
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
                    <select id="sortBy" class="form-select" style="max-width: 200px;">
                        <option value="recent">Most Recent</option>
                        <option value="popular">Most Popular</option>
                        <option value="title">Title A-Z</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Notes Grid -->
        <?php if (empty($all_notes)): ?>
            <div class="card">
                <div class="card-body" style="text-align: center; padding: 3rem; color: #6b7280;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📚</div>
                    <h3 style="margin-bottom: 0.5rem;">No public notes available yet</h3>
                    <p>Be the first to share your notes with the community!</p>
                    <a href="upload.php" class="btn btn-primary" style="margin-top: 1rem;">
                        Upload Notes
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="notes-grid" id="notesGrid">
                <?php foreach ($all_notes as $note): ?>
                    <div class="note-card" 
                         data-title="<?php echo htmlspecialchars(strtolower($note['title'])); ?>"
                         data-subject="<?php echo htmlspecialchars(strtolower($note['subject_name'])); ?>"
                         data-semester="<?php echo $note['semester']; ?>"
                         data-downloads="<?php echo $note['download_count']; ?>"
                         data-date="<?php echo strtotime($note['upload_date']); ?>"
                         data-uploader="<?php echo htmlspecialchars(strtolower($note['uploader_name'])); ?>">
                        
                        <div class="note-title"><?php echo htmlspecialchars($note['title']); ?></div>
                        
                        <div class="note-meta">
                            <span class="badge badge-primary"><?php echo htmlspecialchars($note['subject_name']); ?></span>
                            <span>📅 Sem <?php echo $note['semester']; ?></span>
                            <span>📥 <?php echo $note['download_count']; ?> downloads</span>
                        </div>
                        
                        <?php if (!empty($note['description'])): ?>
                            <div class="note-description">
                                <?php 
                                $description = $note['description'];
                                echo htmlspecialchars(strlen($description) > 150 ? substr($description, 0, 150) . '...' : $description); 
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="note-footer">
                            <div>
                                <small style="color: #6b7280;">
                                    👤 <?php echo htmlspecialchars($note['uploader_name']); ?>
                                    <br>
                                    ⏰ <?php echo timeAgo($note['upload_date']); ?>
                                </small>
                            </div>
                            <div>
                                <small style="color: #6b7280; display: block; margin-bottom: 0.5rem;">
                                    📦 <?php echo formatFileSize($note['file_size']); ?> 
                                    • <?php echo strtoupper($note['file_type']); ?>
                                </small>
                                <a 
                                    href="../actions/download_action.php?id=<?php echo $note['note_id']; ?>" 
                                    class="btn btn-primary" 
                                    style="padding: 0.5rem 1rem; font-size: 0.9rem; width: 100%;"
                                >
                                    📥 Download
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="noResultsMessage" style="display: none; text-align: center; padding: 2rem; color: #6b7280;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
                <h3>No notes found</h3>
                <p>Try adjusting your search or filter criteria</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2024 Student Notes Organizer. Academic Micro-Project.</p>
    </footer>

    <script>
        // Get all filter elements
        const searchInput = document.getElementById('searchInput');
        const subjectFilter = document.getElementById('filterSubject');
        const semesterFilter = document.getElementById('filterSemester');
        const sortBy = document.getElementById('sortBy');
        const notesGrid = document.getElementById('notesGrid');
        const noResultsMessage = document.getElementById('noResultsMessage');
        const noteCards = notesGrid ? Array.from(notesGrid.getElementsByClassName('note-card')) : [];

        function filterAndSortNotes() {
            const searchTerm = searchInput.value.toLowerCase();
            const subjectValue = subjectFilter.value.toLowerCase();
            const semesterValue = semesterFilter.value;
            const sortValue = sortBy.value;

            let visibleCount = 0;

            // Filter notes
            noteCards.forEach(card => {
                const title = card.dataset.title;
                const subject = card.dataset.subject;
                const semester = card.dataset.semester;
                const uploader = card.dataset.uploader;

                const matchesSearch = !searchTerm || 
                    title.includes(searchTerm) || 
                    subject.includes(searchTerm) || 
                    uploader.includes(searchTerm);
                
                const matchesSubject = !subjectValue || subject === subjectValue;
                const matchesSemester = !semesterValue || semester === semesterValue;

                if (matchesSearch && matchesSubject && matchesSemester) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Sort visible notes
            const visibleCards = noteCards.filter(card => card.style.display !== 'none');
            
            visibleCards.sort((a, b) => {
                if (sortValue === 'popular') {
                    return parseInt(b.dataset.downloads) - parseInt(a.dataset.downloads);
                } else if (sortValue === 'title') {
                    return a.dataset.title.localeCompare(b.dataset.title);
                } else { // recent
                    return parseInt(b.dataset.date) - parseInt(a.dataset.date);
                }
            });

            // Re-append sorted cards
            visibleCards.forEach(card => notesGrid.appendChild(card));

            // Show/hide no results message
            if (visibleCount === 0) {
                notesGrid.style.display = 'none';
                noResultsMessage.style.display = 'block';
            } else {
                notesGrid.style.display = 'grid';
                noResultsMessage.style.display = 'none';
            }
        }

        // Add event listeners
        if (searchInput) {
            searchInput.addEventListener('input', filterAndSortNotes);
            subjectFilter.addEventListener('change', filterAndSortNotes);
            semesterFilter.addEventListener('change', filterAndSortNotes);
            sortBy.addEventListener('change', filterAndSortNotes);
        }
    </script>
</body>
</html>
