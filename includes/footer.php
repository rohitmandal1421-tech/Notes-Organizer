<?php
/**
 * Common Footer Include File
 * This file contains the footer that appears on all pages
 * 
 * Usage: include_once '../includes/footer.php';
 */
?>

</div>
<!-- Main Content Wrapper Ends Here -->

<!-- Footer Section -->
<footer class="footer">
    <div class="footer-content" style="max-width: 1200px; margin: 0 auto;">
        <div class="footer-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
            
            <!-- About Section -->
            <div class="footer-section">
                <h3 style="margin-bottom: 1rem; font-size: 1.2rem;">📚 About Notes Organizer</h3>
                <p style="font-size: 0.9rem; line-height: 1.6; opacity: 0.9;">
                    A collaborative platform for students to upload, share, and download academic notes. 
                    Building a knowledge base for better learning.
                </p>
            </div>
            
            <!-- Quick Links -->
            <div class="footer-section">
                <h3 style="margin-bottom: 1rem; font-size: 1.2rem;">🔗 Quick Links</h3>
                <ul style="list-style: none; font-size: 0.9rem; line-height: 2;">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>dashboard/index.php" style="color: #93c5fd; text-decoration: none;">Dashboard</a></li>
                        <li><a href="<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>dashboard/upload.php" style="color: #93c5fd; text-decoration: none;">Upload Notes</a></li>
                        <li><a href="<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>dashboard/browse.php" style="color: #93c5fd; text-decoration: none;">Browse Notes</a></li>
                        <li><a href="<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>dashboard/my-notes.php" style="color: #93c5fd; text-decoration: none;">My Notes</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>index.php" style="color: #93c5fd; text-decoration: none;">Home</a></li>
                        <li><a href="<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>auth/login.php" style="color: #93c5fd; text-decoration: none;">Login</a></li>
                        <li><a href="<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>auth/register.php" style="color: #93c5fd; text-decoration: none;">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <!-- Statistics -->
            <div class="footer-section">
                <h3 style="margin-bottom: 1rem; font-size: 1.2rem;">📊 Statistics</h3>
                <ul style="list-style: none; font-size: 0.9rem; line-height: 2;">
                    <?php
                    // Fetch quick statistics if database is available
                    if (function_exists('getDatabaseConnection')) {
                        $conn = getDatabaseConnection();
                        if ($conn) {
                            // Total users
                            $result = $conn->query("SELECT COUNT(*) as count FROM users");
                            $total_users = $result ? $result->fetch_assoc()['count'] : 0;
                            
                            // Total notes
                            $result = $conn->query("SELECT COUNT(*) as count FROM notes");
                            $total_notes = $result ? $result->fetch_assoc()['count'] : 0;
                            
                            // Total downloads
                            $result = $conn->query("SELECT COUNT(*) as count FROM downloads");
                            $total_downloads = $result ? $result->fetch_assoc()['count'] : 0;
                            
                            closeDatabaseConnection($conn);
                            
                            echo "<li>👥 " . number_format($total_users) . " Students</li>";
                            echo "<li>📄 " . number_format($total_notes) . " Notes Shared</li>";
                            echo "<li>📥 " . number_format($total_downloads) . " Downloads</li>";
                        }
                    }
                    ?>
                    <li>🌍 Growing Community</li>
                </ul>
            </div>
            
            <!-- Contact Info -->
            <div class="footer-section">
                <h3 style="margin-bottom: 1rem; font-size: 1.2rem;">📧 Support</h3>
                <ul style="list-style: none; font-size: 0.9rem; line-height: 2;">
                    <li>📧 Email: support@notesorganizer.edu</li>
                    <li>📞 Phone: +91-XXX-XXX-XXXX</li>
                    <li>🏫 College Campus</li>
                    <li>⏰ Mon-Fri: 9 AM - 5 PM</li>
                </ul>
            </div>
            
        </div>
        
        <!-- Divider -->
        <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.1); margin: 2rem 0;">
        
        <!-- Copyright Section -->
        <div class="footer-bottom" style="text-align: center;">
            <p style="margin-bottom: 0.5rem; font-size: 0.95rem;">
                &copy; <?php echo date('Y'); ?> Student Notes Organizer. All rights reserved.
            </p>
            <p style="font-size: 0.85rem; opacity: 0.8;">
                Academic Micro-Project | Built with ❤️ for students, by students
            </p>
            <p style="font-size: 0.8rem; opacity: 0.7; margin-top: 0.5rem;">
                Made with HTML5, CSS3, JavaScript, PHP & MySQL
            </p>
        </div>
        
        <!-- Social Links (Optional) -->
        <div class="footer-social" style="text-align: center; margin-top: 1.5rem;">
            <a href="#" style="color: white; margin: 0 0.5rem; text-decoration: none; font-size: 1.2rem;" title="Facebook">📘</a>
            <a href="#" style="color: white; margin: 0 0.5rem; text-decoration: none; font-size: 1.2rem;" title="Twitter">🐦</a>
            <a href="#" style="color: white; margin: 0 0.5rem; text-decoration: none; font-size: 1.2rem;" title="Instagram">📷</a>
            <a href="#" style="color: white; margin: 0 0.5rem; text-decoration: none; font-size: 1.2rem;" title="LinkedIn">💼</a>
            <a href="#" style="color: white; margin: 0 0.5rem; text-decoration: none; font-size: 1.2rem;" title="GitHub">💻</a>
        </div>
        
    </div>
</footer>

<!-- Additional JavaScript if needed -->
<?php if (isset($additional_js)): ?>
    <?php echo $additional_js; ?>
<?php endif; ?>

<!-- Back to Top Button (Optional) -->
<button id="backToTop" style="display: none; position: fixed; bottom: 30px; right: 30px; background: #2563eb; color: white; border: none; padding: 15px 20px; border-radius: 50%; cursor: pointer; font-size: 1.2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.3); z-index: 1000; transition: all 0.3s;" title="Back to top">
    ⬆️
</button>

<script>
    // Back to Top Button Functionality
    const backToTopButton = document.getElementById('backToTop');
    
    if (backToTopButton) {
        // Show button when scrolled down
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopButton.style.display = 'block';
            } else {
                backToTopButton.style.display = 'none';
            }
        });
        
        // Scroll to top when clicked
        backToTopButton.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Add hover effect to back to top button
    if (backToTopButton) {
        backToTopButton.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1)';
            this.style.background = '#1e40af';
        });
        
        backToTopButton.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
            this.style.background = '#2563eb';
        });
    }
</script>

</body>
</html>
