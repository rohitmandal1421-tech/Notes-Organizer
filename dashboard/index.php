<?php
/**
 * Student Dashboard
 * Main page showing statistics, recent activity, and quick actions
 */

session_start();
require_once '../config/database.php';

// Check if user is logged in
requireLogin();

$user_id = getLoggedInUserId();
$user_name = getLoggedInUserName();

// Get database connection
$conn = getDatabaseConnection();

// Initialize statistics
$stats = [
    'total_uploads' => 0,
    'total_downloads' => 0,
    'public_notes' => 0,
    'private_notes' => 0,
    'subjects_count' => 0,
    'total_views' => 0
];

// Get total uploads by user
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM notes WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stats['total_uploads'] = $result->fetch_assoc()['count'];
$stmt->close();

// Get total downloads by user
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM downloads WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stats['total_downloads'] = $result->fetch_assoc()['count'];
$stmt->close();

// Get public vs private notes count
$stmt = $conn->prepare("SELECT 
    SUM(CASE WHEN visibility = 'public' THEN 1 ELSE 0 END) as public_count,
    SUM(CASE WHEN visibility = 'private' THEN 1 ELSE 0 END) as private_count
    FROM notes WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$visibility_data = $result->fetch_assoc();
$stats['public_notes'] = $visibility_data['public_count'] ?? 0;
$stats['private_notes'] = $visibility_data['private_count'] ?? 0;
$stmt->close();

// Get unique subjects contributed
$stmt = $conn->prepare("SELECT COUNT(DISTINCT subject_id) as count FROM notes WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stats['subjects_count'] = $result->fetch_assoc()['count'];
$stmt->close();

// Get total views (downloads) on user's notes
$stmt = $conn->prepare("SELECT SUM(download_count) as total FROM notes WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stats['total_views'] = $result->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Get recent uploads
$recent_uploads = [];
$stmt = $conn->prepare("SELECT n.*, s.subject_name FROM notes n 
    JOIN subjects s ON n.subject_id = s.subject_id 
    WHERE n.user_id = ? 
    ORDER BY n.upload_date DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recent_uploads[] = $row;
}
$stmt->close();

// Get recent downloads
$recent_downloads = [];
$stmt = $conn->prepare("SELECT n.title, s.subject_name, d.download_date, u.full_name as uploader 
    FROM downloads d 
    JOIN notes n ON d.note_id = n.note_id 
    JOIN subjects s ON n.subject_id = s.subject_id 
    JOIN users u ON n.user_id = u.user_id
    WHERE d.user_id = ? 
    ORDER BY d.download_date DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recent_downloads[] = $row;
}
$stmt->close();

// Get subject-wise upload statistics for chart
$subject_stats = [];
$stmt = $conn->prepare("SELECT s.subject_name, COUNT(n.note_id) as count 
    FROM notes n 
    JOIN subjects s ON n.subject_id = s.subject_id 
    WHERE n.user_id = ? 
    GROUP BY s.subject_id, s.subject_name 
    ORDER BY count DESC LIMIT 10");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $subject_stats[] = $row;
}
$stmt->close();

closeDatabaseConnection($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Student Notes Organizer</title>
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
            <h1 class="page-title">Welcome back, <?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?>! 👋</h1>
            <p class="page-subtitle">Here's your academic activity overview</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_uploads']; ?></div>
                <div class="stat-label">📤 Total Uploads</div>
            </div>
            
            <div class="stat-card secondary">
                <div class="stat-number"><?php echo $stats['total_downloads']; ?></div>
                <div class="stat-label">📥 Downloads Made</div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-number"><?php echo $stats['total_views']; ?></div>
                <div class="stat-label">👁️ Total Views</div>
            </div>
            
            <div class="stat-card" style="background: linear-gradient(135deg, #8b5cf6, #a78bfa);">
                <div class="stat-number"><?php echo $stats['subjects_count']; ?></div>
                <div class="stat-label">📚 Subjects Covered</div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="card">
            <div class="card-header">
                📊 Subject-wise Contributions
            </div>
            <div class="card-body">
                <?php if (empty($subject_stats)): ?>
                    <p style="text-align: center; color: #6b7280; padding: 2rem;">
                        No data available yet. Start uploading notes to see your statistics!
                    </p>
                <?php else: ?>
                    <canvas id="subjectChart" style="max-height: 300px;"></canvas>
                <?php endif; ?>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 1.5rem;">
            <!-- Recent Uploads -->
            <div class="card">
                <div class="card-header">
                    📤 Recent Uploads
                    <a href="my-notes.php" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem;">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_uploads)): ?>
                        <p style="text-align: center; color: #6b7280; padding: 1rem;">No uploads yet</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Subject</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_uploads as $upload): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($upload['title']); ?></td>
                                            <td><span class="badge badge-primary"><?php echo htmlspecialchars($upload['subject_name']); ?></span></td>
                                            <td><?php echo timeAgo($upload['upload_date']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Downloads -->
            <div class="card">
                <div class="card-header">
                    📥 Recent Downloads
                </div>
                <div class="card-body">
                    <?php if (empty($recent_downloads)): ?>
                        <p style="text-align: center; color: #6b7280; padding: 1rem;">No downloads yet</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Subject</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_downloads as $download): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($download['title']); ?></td>
                                            <td><span class="badge badge-success"><?php echo htmlspecialchars($download['subject_name']); ?></span></td>
                                            <td><?php echo timeAgo($download['download_date']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">⚡ Quick Actions</div>
            <div class="card-body">
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <a href="upload.php" class="btn btn-primary">📤 Upload New Notes</a>
                    <a href="browse.php" class="btn btn-secondary">🔍 Browse All Notes</a>
                    <a href="my-notes.php" class="btn btn-outline">📝 Manage My Notes</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2024 Student Notes Organizer. Academic Micro-Project.</p>
    </footer>

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <script>
        // Subject-wise contribution chart
        <?php if (!empty($subject_stats)): ?>
        const subjectData = {
            labels: <?php echo json_encode(array_column($subject_stats, 'subject_name')); ?>,
            datasets: [{
                label: 'Notes Uploaded',
                data: <?php echo json_encode(array_column($subject_stats, 'count')); ?>,
                backgroundColor: [
                    'rgba(37, 99, 235, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(139, 92, 246, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(251, 146, 60, 0.8)',
                    'rgba(168, 85, 247, 0.8)',
                    'rgba(236, 72, 153, 0.8)'
                ],
                borderWidth: 0
            }]
        };

        const config = {
            type: 'bar',
            data: subjectData,
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        };

        const ctx = document.getElementById('subjectChart');
        new Chart(ctx, config);
        <?php endif; ?>
    </script>
</body>
</html>
