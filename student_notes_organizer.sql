-- =====================================================
-- STUDENT NOTES ORGANIZER - DATABASE SCHEMA
-- Academic Micro-Project
-- =====================================================

-- Create Database
CREATE DATABASE IF NOT EXISTS student_notes_organizer;
USE student_notes_organizer;

-- =====================================================
-- TABLE 1: Users (Students)
-- Stores student authentication and profile data
-- =====================================================
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,  -- Hashed password
    enrollment_no VARCHAR(50) UNIQUE NOT NULL,
    department VARCHAR(100),
    semester INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_enrollment (enrollment_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 2: Courses
-- Stores academic courses
-- =====================================================
CREATE TABLE courses (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) UNIQUE NOT NULL,
    course_name VARCHAR(150) NOT NULL,
    department VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_course_code (course_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 3: Subjects
-- Stores subjects under courses
-- =====================================================
CREATE TABLE subjects (
    subject_id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    subject_code VARCHAR(20) UNIQUE NOT NULL,
    subject_name VARCHAR(150) NOT NULL,
    semester INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    INDEX idx_subject_code (subject_code),
    INDEX idx_semester (semester)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 4: Notes
-- Stores uploaded notes information
-- =====================================================
CREATE TABLE notes (
    note_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT,  -- In bytes
    file_type VARCHAR(50),  -- pdf, docx, txt, etc.
    visibility ENUM('public', 'private') DEFAULT 'public',
    semester INT NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    download_count INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    INDEX idx_visibility (visibility),
    INDEX idx_semester (semester),
    INDEX idx_upload_date (upload_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 5: Downloads
-- Tracks who downloaded which notes
-- =====================================================
CREATE TABLE downloads (
    download_id INT AUTO_INCREMENT PRIMARY KEY,
    note_id INT NOT NULL,
    user_id INT NOT NULL,
    download_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (note_id) REFERENCES notes(note_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_note_id (note_id),
    INDEX idx_user_id (user_id),
    INDEX idx_download_date (download_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 6: Activity Log
-- Tracks user activities for dashboard metrics
-- =====================================================
CREATE TABLE activity_log (
    activity_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_type ENUM('upload', 'download', 'login', 'register') NOT NULL,
    activity_description VARCHAR(255),
    activity_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_activity (user_id, activity_type),
    INDEX idx_activity_date (activity_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- SAMPLE DATA INSERTION
-- For testing and demonstration purposes
-- =====================================================

-- Insert Sample Courses
INSERT INTO courses (course_code, course_name, department) VALUES
('CSE', 'Computer Science Engineering', 'Engineering'),
('ECE', 'Electronics & Communication', 'Engineering'),
('MECH', 'Mechanical Engineering', 'Engineering'),
('BBA', 'Bachelor of Business Administration', 'Management');

-- Insert Sample Subjects (for CSE Course)
INSERT INTO subjects (course_id, subject_code, subject_name, semester) VALUES
(1, 'CS101', 'Programming in C', 1),
(1, 'CS102', 'Data Structures', 2),
(1, 'CS201', 'Database Management Systems', 3),
(1, 'CS202', 'Operating Systems', 4),
(1, 'CS301', 'Computer Networks', 5),
(1, 'CS302', 'Software Engineering', 6),
(1, 'MATH101', 'Engineering Mathematics-I', 1),
(1, 'MATH102', 'Engineering Mathematics-II', 2);

-- Insert Sample Subjects (for ECE Course)
INSERT INTO subjects (course_id, subject_code, subject_name, semester) VALUES
(2, 'EC101', 'Basic Electronics', 1),
(2, 'EC102', 'Digital Electronics', 2),
(2, 'EC201', 'Analog Communication', 3),
(2, 'EC202', 'Microprocessors', 4);

-- Insert Sample Student (Password: "student123")
-- Note: In actual application, passwords should be hashed using password_hash()
INSERT INTO users (full_name, email, password, enrollment_no, department, semester) VALUES
('John Doe', 'john.doe@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CSE2024001', 'Computer Science', 3),
('Jane Smith', 'jane.smith@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CSE2024002', 'Computer Science', 3),
('Mike Johnson', 'mike.j@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ECE2024001', 'Electronics', 2);

-- =====================================================
-- USEFUL QUERIES FOR VIVA PREPARATION
-- =====================================================

-- Query 1: Get all notes uploaded by a specific user
-- SELECT n.*, s.subject_name, u.full_name 
-- FROM notes n 
-- JOIN subjects s ON n.subject_id = s.subject_id 
-- JOIN users u ON n.user_id = u.user_id 
-- WHERE n.user_id = 1;

-- Query 2: Get most downloaded notes
-- SELECT n.*, s.subject_name, u.full_name 
-- FROM notes n 
-- JOIN subjects s ON n.subject_id = s.subject_id 
-- JOIN users u ON n.user_id = u.user_id 
-- ORDER BY n.download_count DESC 
-- LIMIT 10;

-- Query 3: Get user dashboard statistics
-- SELECT 
--     (SELECT COUNT(*) FROM notes WHERE user_id = 1) as total_uploads,
--     (SELECT COUNT(*) FROM downloads WHERE user_id = 1) as total_downloads,
--     (SELECT COUNT(DISTINCT subject_id) FROM notes WHERE user_id = 1) as subjects_contributed;

-- Query 4: Get subject-wise note count
-- SELECT s.subject_name, COUNT(n.note_id) as note_count 
-- FROM subjects s 
-- LEFT JOIN notes n ON s.subject_id = n.subject_id 
-- GROUP BY s.subject_id, s.subject_name 
-- ORDER BY note_count DESC;

-- Query 5: Get recent activity for a user
-- SELECT * FROM activity_log 
-- WHERE user_id = 1 
-- ORDER BY activity_date DESC 
-- LIMIT 20;

-- =====================================================
-- END OF DATABASE SCHEMA
-- =====================================================