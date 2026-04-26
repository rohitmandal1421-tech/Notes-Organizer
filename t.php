<?php
/**
 * Landing Page / Home Page
 * Welcome page for visitors with login/register options
 */

session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard/index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Notes Organizer - Share & Download Academic Notes</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: white;
            padding: 4rem 2rem;
            text-align: center;
        }

        .hero-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.95;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }

        .feature-card {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            font-size: 3.5rem;
            margin-bottom: 1rem;
        }

        .feature-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #1f2937;
        }

        .feature-description {
            color: #6b7280;
            line-height: 1.6;
        }

        .stats-showcase {
            background: #f3f4f6;
            padding: 3rem 2rem;
            text-align: center;
        }

        .stats-container {
            display: flex;
            justify-content: center;
            gap: 3rem;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 3rem;
            font-weight: 700;
            color: #2563eb;
            display: block;
        }

        .stat-label {
            font-size: 1.1rem;
            color: #6b7280;
            margin-top: 0.5rem;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }

            .hero-subtitle {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <div class="hero-section">
        <div style="max-width: 1200px; margin: 0 auto;">
            <h1 class="hero-title">📚Notes Organizer</h1>
            <p class="hero-subtitle">
                Your one-stop platform to upload, organize, and share academic notes with fellow students
            </p>
            <div class="hero-buttons">
                <a href="auth/register.php" class="btn" style="background: white; color: #2563eb; padding: 1rem 2rem; font-size: 1.1rem;">
                    Get Started Free
                </a>
                <a href="auth/login.php" class="btn" style="background: transparent; border: 2px solid white; color: white; padding: 1rem 2rem; font-size: 1.1rem;">
                    Login
                </a>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="container">
        <div style="text-align: center; margin: 3rem 0 2rem;">
            <h2 style="font-size: 2.5rem; color: #1f2937; margin-bottom: 1rem;">Why Choose Us?</h2>
            <p style="font-size: 1.1rem; color: #6b7280;">Everything you need to manage and share your academic notes</p>
        </div>

        <div class="feature-grid">
            <div class="feature-card">
                <div class="feature-icon">📤</div>
                <h3 class="feature-title">Easy Upload</h3>
                <p class="feature-description">
                    Upload your notes in multiple formats (PDF, DOC, PPT) with just a few clicks. Organize by course, semester, and subject.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🔍</div>
                <h3 class="feature-title">Smart Search</h3>
                <p class="feature-description">
                    Find notes quickly with powerful search and filter options. Search by title, subject, semester, or contributor.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🌍</div>
                <h3 class="feature-title">Share & Collaborate</h3>
                <p class="feature-description">
                    Share your notes publicly with the community or keep them private. Help fellow students succeed together.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3 class="feature-title">Track Performance</h3>
                <p class="feature-description">
                    View detailed statistics about your uploads, downloads, and contributions. Monitor your academic impact.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <h3 class="feature-title">Secure & Private</h3>
                <p class="feature-description">
                    Your data is protected with industry-standard security. Control visibility of your notes with privacy settings.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">💡</div>
                <h3 class="feature-title">Organized Library</h3>
                <p class="feature-description">
                    Keep all your notes organized in one place. Access them anytime, anywhere, from any device.
                </p>
            </div>
        </div>
    </div>

    <!-- Stats Showcase -->
    <div class="stats-showcase">
        <div style="max-width: 1200px; margin: 0 auto;">
            <h2 style="font-size: 2.5rem; color: #1f2937; margin-bottom: 0.5rem;">Join Our Growing Community</h2>
            <p style="font-size: 1.1rem; color: #6b7280; margin-bottom: 2rem;">
                Students helping students succeed
            </p>
            
            <div class="stats-container">
                <div class="stat-item">
                    <span class="stat-value">500+</span>
                    <span class="stat-label">Active Students</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">2,000+</span>
                    <span class="stat-label">Notes Shared</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">50+</span>
                    <span class="stat-label">Subjects Covered</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">10,000+</span>
                    <span class="stat-label">Downloads</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="container" style="text-align: center; padding: 4rem 2rem;">
        <h2 style="font-size: 2.5rem; color: #1f2937; margin-bottom: 1rem;">Ready to Get Started?</h2>
        <p style="font-size: 1.1rem; color: #6b7280; margin-bottom: 2rem;">
            Join thousands of students who are already sharing and accessing quality notes
        </p>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="auth/register.php" class="btn btn-primary" style="padding: 1rem 2.5rem; font-size: 1.1rem;">
                Create Free Account
            </a>
            <a href="auth/login.php" class="btn btn-outline" style="padding: 1rem 2.5rem; font-size: 1.1rem;">
                Sign In
            </a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div style="max-width: 1200px; margin: 0 auto;">
            <p style="margin-bottom: 0.5rem;">&copy; 2024 Student Notes Organizer. All rights reserved.</p>
            <p style="font-size: 0.9rem; opacity: 0.8;">Academic Micro-Project | Built for students, by students</p>
        </div>
    </footer>
</body>
</html>
