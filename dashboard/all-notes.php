<?php
/**
 * All Notes Page
 * Displays all uploaded notes with download and share functionality
 */

session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$conn = getDatabaseConnection();
$user_id = $_SESSION['user_id'];

// Get filter parameters
$filter_course = isset($_GET['course']) ? $_GET['course'] : '';
$filter_semester = isset($_GET['semester']) ? $_GET['semester'] : '';
$filter_subject = isset($_GET['subject']) ? $_GET['subject'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query
$query = "SELECT n.*, s.subject_name, s.subject_code, c.course_name, c.course_code, 
          u.full_name as uploader_name, u.email as uploader_email
          FROM notes n 
          JOIN subjects s ON n.subject_id = s.subject_id 
          JOIN courses c ON s.course_id = c.course_id
          JOIN users u ON n.user_id = u.user_id
          WHERE n.visibility = 'public'";

// Apply filters
if (!empty($filter_course)) {
    $query .= " AND c.course_id = " . intval($filter_course);
}
if (!empty($filter_semester)) {
    $query .= " AND n.semester = " . intval($filter_semester);
}
if (!empty($filter_subject)) {
    $query .= " AND n.subject_id = " . intval($filter_subject);
}
if (!empty($search)) {
    $search_term = $conn->real_escape_string($search);
    $query .= " AND (n.title LIKE '%$search_term%' OR n.description LIKE '%$search_term%' OR s.subject_name LIKE '%$search_term%')";
}

$query .= " ORDER BY n.upload_date DESC";

$result = $conn->query($query);

// Get all courses for filter
$courses_result = $conn->query("SELECT * FROM courses ORDER BY course_name");

// Get all subjects for filter
$subjects_result = $conn->query("SELECT s.*, c.course_name FROM subjects s JOIN courses c ON s.course_id = c.course_id ORDER BY c.course_name, s.subject_name");

// Page title
$page_title = "All Notes";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Student Notes Organizer</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .all-notes-container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 20px;
        }

        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }

        .page-header h1 {
            margin: 0 0 10px 0;
            font-size: 2.5em;
        }

        .page-header p {
            margin: 0;
            opacity: 0.9;
        }

        .filters-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }

        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-filter {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-apply {
            background: #667eea;
            color: white;
        }

        .btn-apply:hover {
            background: #5568d3;
        }

        .btn-reset {
            background: #e0e0e0;
            color: #333;
        }

        .btn-reset:hover {
            background: #d0d0d0;
        }

        .notes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        .note-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .note-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 25px rgba(0,0,0,0.15);
        }

        .note-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
        }

        .note-title {
            font-size: 1.3em;
            font-weight: 700;
            margin: 0 0 10px 0;
        }

        .note-meta {
            display: flex;
            gap: 15px;
            font-size: 0.85em;
            opacity: 0.9;
        }

        .note-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .note-body {
            padding: 20px;
        }

        .note-description {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .note-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 15px;
            font-size: 0.9em;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #555;
        }

        .info-item i {
            color: #667eea;
            width: 20px;
        }

        .note-footer {
            border-top: 1px solid #e0e0e0;
            padding: 15px 20px;
            display: flex;
            gap: 10px;
        }

        .btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-download {
            background: #10b981;
            color: white;
        }

        .btn-download:hover {
            background: #059669;
        }

        .btn-share {
            background: #3b82f6;
            color: white;
        }

        .btn-share:hover {
            background: #2563eb;
        }

        .stats-bar {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stats-info {
            font-weight: 600;
            color: #333;
        }

        .no-notes {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .no-notes i {
            font-size: 80px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        /* Share Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.3);
        }

        .modal-header {
            margin-bottom: 20px;
        }

        .modal-header h2 {
            margin: 0 0 10px 0;
            color: #333;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }

        .share-link-container {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .share-link {
            word-break: break-all;
            color: #667eea;
            font-size: 14px;
        }

        .copy-btn {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .copy-btn:hover {
            background: #5568d3;
        }

        .share-options {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }

        .share-option {
            padding: 10px;
            text-align: center;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: white;
        }

        .share-whatsapp { background: #25D366; }
        .share-telegram { background: #0088cc; }
        .share-email { background: #EA4335; }
        .share-copy { background: #667eea; }

        .share-option:hover {
            opacity: 0.8;
            transform: scale(1.05);
        }

        .share-option i {
            font-size: 24px;
            display: block;
            margin-bottom: 5px;
        }

        .share-option span {
            font-size: 11px;
            display: block;
        }

        @media (max-width: 768px) {
            .notes-grid {
                grid-template-columns: 1fr;
            }
            
            .filters-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="all-notes-container">
        <div class="page-header">
            <h1><i class="fas fa-book-open"></i> All Uploaded Notes</h1>
            <p>Browse, download, and share notes uploaded by students</p>
        </div>

        <!-- Filters Section -->
        <div class="filters-section">
            <form method="GET" action="">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label><i class="fas fa-graduation-cap"></i> Course</label>
                        <select name="course" id="courseFilter">
                            <option value="">All Courses</option>
                            <?php while ($course = $courses_result->fetch_assoc()): ?>
                                <option value="<?php echo $course['course_id']; ?>" 
                                    <?php echo ($filter_course == $course['course_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['course_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label><i class="fas fa-calendar"></i> Semester</label>
                        <select name="semester">
                            <option value="">All Semesters</option>
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($filter_semester == $i) ? 'selected' : ''; ?>>
                                    Semester <?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label><i class="fas fa-book"></i> Subject</label>
                        <select name="subject">
                            <option value="">All Subjects</option>
                            <?php while ($subject = $subjects_result->fetch_assoc()): ?>
                                <option value="<?php echo $subject['subject_id']; ?>"
                                    <?php echo ($filter_subject == $subject['subject_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($subject['course_name'] . ' - ' . $subject['subject_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label><i class="fas fa-search"></i> Search</label>
                        <input type="text" name="search" placeholder="Search notes..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>

                <div class="filter-buttons">
                    <button type="submit" class="btn-filter btn-apply">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    <a href="all-notes.php" class="btn-filter btn-reset">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Stats Bar -->
        <?php
        $total_notes = $result->num_rows;
        ?>
        <div class="stats-bar">
            <div class="stats-info">
                <i class="fas fa-file-alt"></i> 
                Showing <?php echo $total_notes; ?> note<?php echo ($total_notes != 1) ? 's' : ''; ?>
            </div>
        </div>

        <!-- Notes Grid -->
        <div class="notes-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($note = $result->fetch_assoc()): ?>
                    <div class="note-card">
                        <div class="note-header">
                            <h3 class="note-title"><?php echo htmlspecialchars($note['title']); ?></h3>
                            <div class="note-meta">
                                <span>
                                    <i class="fas fa-book"></i>
                                    <?php echo htmlspecialchars($note['subject_name']); ?>
                                </span>
                                <span>
                                    <i class="fas fa-calendar"></i>
                                    Sem <?php echo $note['semester']; ?>
                                </span>
                            </div>
                        </div>

                        <div class="note-body">
                            <p class="note-description">
                                <?php echo htmlspecialchars(substr($note['description'], 0, 150)); ?>
                                <?php echo (strlen($note['description']) > 150) ? '...' : ''; ?>
                            </p>

                            <div class="note-info">
                                <div class="info-item">
                                    <i class="fas fa-graduation-cap"></i>
                                    <span><?php echo htmlspecialchars($note['course_name']); ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-file"></i>
                                    <span><?php echo strtoupper($note['file_type']); ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-user"></i>
                                    <span><?php echo htmlspecialchars($note['uploader_name']); ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-download"></i>
                                    <span><?php echo $note['download_count']; ?> downloads</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo date('M d, Y', strtotime($note['upload_date'])); ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-hdd"></i>
                                    <span><?php echo number_format($note['file_size'] / 1024, 2); ?> KB</span>
                                </div>
                            </div>
                        </div>

                        <div class="note-footer">
                            <a href="../actions/download_action.php?note_id=<?php echo $note['note_id']; ?>" 
                               class="btn btn-download">
                                <i class="fas fa-download"></i> Download
                            </a>
                            <button class="btn btn-share" 
                                    onclick="openShareModal(<?php echo $note['note_id']; ?>, '<?php echo addslashes($note['title']); ?>')">
                                <i class="fas fa-share-alt"></i> Share
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-notes">
                    <i class="fas fa-folder-open"></i>
                    <h2>No Notes Found</h2>
                    <p>Try adjusting your filters or search terms</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Share Modal -->
    <div id="shareModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeShareModal()">&times;</span>
            <div class="modal-header">
                <h2><i class="fas fa-share-alt"></i> Share Note</h2>
                <p id="shareNoteTitle"></p>
            </div>
            
            <div class="share-link-container">
                <strong>Share Link:</strong>
                <div class="share-link" id="shareLink"></div>
            </div>

            <button class="copy-btn" onclick="copyShareLink()">
                <i class="fas fa-copy"></i> Copy Link
            </button>

            <div class="share-options">
                <a href="#" id="shareWhatsApp" class="share-option share-whatsapp" target="_blank">
                    <i class="fab fa-whatsapp"></i>
                    <span>WhatsApp</span>
                </a>
                <a href="#" id="shareTelegram" class="share-option share-telegram" target="_blank">
                    <i class="fab fa-telegram"></i>
                    <span>Telegram</span>
                </a>
                <a href="#" id="shareEmail" class="share-option share-email">
                    <i class="fas fa-envelope"></i>
                    <span>Email</span>
                </a>
                <button class="share-option share-copy" onclick="copyShareLink()">
                    <i class="fas fa-copy"></i>
                    <span>Copy</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentShareLink = '';
        let currentShareTitle = '';

        function openShareModal(noteId, noteTitle) {
            // Create share link
            const baseUrl = window.location.origin + window.location.pathname.replace('all-notes.php', '');
            currentShareLink = baseUrl + '../actions/download_action.php?note_id=' + noteId;
            currentShareTitle = noteTitle;

            // Update modal
            document.getElementById('shareNoteTitle').textContent = noteTitle;
            document.getElementById('shareLink').textContent = currentShareLink;

            // Update share buttons
            const whatsappText = encodeURIComponent(`Check out this note: ${noteTitle}\n${currentShareLink}`);
            document.getElementById('shareWhatsApp').href = `https://wa.me/?text=${whatsappText}`;

            const telegramText = encodeURIComponent(`Check out this note: ${noteTitle}\n${currentShareLink}`);
            document.getElementById('shareTelegram').href = `https://t.me/share/url?url=${encodeURIComponent(currentShareLink)}&text=${encodeURIComponent(noteTitle)}`;

            const emailSubject = encodeURIComponent(`Shared Note: ${noteTitle}`);
            const emailBody = encodeURIComponent(`I wanted to share this note with you:\n\n${noteTitle}\n\nDownload here: ${currentShareLink}`);
            document.getElementById('shareEmail').href = `mailto:?subject=${emailSubject}&body=${emailBody}`;

            // Show modal
            document.getElementById('shareModal').style.display = 'block';
        }

        function closeShareModal() {
            document.getElementById('shareModal').style.display = 'none';
        }

        function copyShareLink() {
            navigator.clipboard.writeText(currentShareLink).then(() => {
                alert('Link copied to clipboard!');
            }).catch(err => {
                // Fallback for older browsers
                const textarea = document.createElement('textarea');
                textarea.value = currentShareLink;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                alert('Link copied to clipboard!');
            });
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('shareModal');
            if (event.target == modal) {
                closeShareModal();
            }
        }
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
<?php $conn->close(); ?>