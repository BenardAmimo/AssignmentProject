-- ============================================================
-- Greenfield Institute - Course Registration System
-- Database Schema & Seed Data
-- ============================================================

CREATE DATABASE IF NOT EXISTS greenfield_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE greenfield_db;

-- ----------------------------------------------------------------
-- Table: users
-- Stores both students and administrators
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(120)        NOT NULL,
    email       VARCHAR(180)        NOT NULL UNIQUE,
    password    VARCHAR(255)        NOT NULL,          -- bcrypt hash
    role        ENUM('student','admin') NOT NULL DEFAULT 'student',
    created_at  TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- Table: courses
-- Stores all course information
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS courses (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    code         VARCHAR(20)   NOT NULL UNIQUE,
    title        VARCHAR(200)  NOT NULL,
    description  TEXT,
    instructor   VARCHAR(120)  NOT NULL,
    credits      TINYINT       NOT NULL DEFAULT 3,
    capacity     INT           NOT NULL DEFAULT 30,
    enrolled     INT           NOT NULL DEFAULT 0,
    schedule     VARCHAR(100),
    category     VARCHAR(80),
    created_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- Table: registrations
-- Maps students to courses (prevents duplicates via UNIQUE key)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS registrations (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    student_id    INT       NOT NULL,
    course_id     INT       NOT NULL,
    registered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_student_course (student_id, course_id),
    FOREIGN KEY (student_id) REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (course_id)  REFERENCES courses(id)  ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- Seed: Administrator account
-- Password: Admin@1234  (bcrypt hash below)
-- ----------------------------------------------------------------
INSERT INTO users (full_name, email, password, role) VALUES
('System Administrator', 'admin@greenfield.edu',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- ----------------------------------------------------------------
-- Seed: Sample student accounts
-- Password for all sample students: Student@1234
-- ----------------------------------------------------------------
INSERT INTO users (full_name, email, password, role) VALUES
('Alice Mwangi',   'alice@student.greenfield.edu',  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('Brian Ochieng',  'brian@student.greenfield.edu',  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('Carol Njeri',    'carol@student.greenfield.edu',  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student');

-- ----------------------------------------------------------------
-- Seed: Sample courses
-- ----------------------------------------------------------------
INSERT INTO courses (code, title, description, instructor, credits, capacity, schedule, category) VALUES
('CS101', 'Introduction to Computer Science',
 'Foundations of computing, algorithms, and problem solving using Python.',
 'Dr. James Kariuki', 3, 40, 'Mon/Wed 08:00-09:30', 'Computer Science'),

('CS201', 'Data Structures and Algorithms',
 'Arrays, linked lists, trees, graphs, sorting and searching algorithms.',
 'Dr. Esther Wanjiru', 3, 35, 'Tue/Thu 10:00-11:30', 'Computer Science'),

('CS301', 'Database Management Systems',
 'Relational databases, SQL, normalization, and transaction management.',
 'Prof. Samuel Otieno', 3, 30, 'Mon/Wed/Fri 11:00-12:00', 'Computer Science'),

('CS401', 'Web Development Fundamentals',
 'HTML, CSS, JavaScript, PHP and MySQL for building dynamic web applications.',
 'Ms. Grace Achieng', 3, 30, 'Tue/Thu 13:00-14:30', 'Computer Science'),

('MATH101', 'Calculus I',
 'Limits, derivatives, integrals and their applications.',
 'Dr. Peter Muthoni', 4, 50, 'Mon/Wed/Fri 08:00-09:00', 'Mathematics'),

('MATH201', 'Linear Algebra',
 'Vectors, matrices, eigenvalues and linear transformations.',
 'Dr. Susan Kamau', 3, 35, 'Tue/Thu 08:00-09:30', 'Mathematics'),

('BUS101', 'Introduction to Business',
 'Overview of business functions including management, marketing, and finance.',
 'Mr. Daniel Mutua', 3, 45, 'Mon/Wed 14:00-15:30', 'Business'),

('BUS201', 'Entrepreneurship & Innovation',
 'Principles of starting and running a business venture in the modern economy.',
 'Ms. Faith Wambua', 3, 40, 'Fri 09:00-12:00', 'Business'),

('ENG101', 'Technical Writing',
 'Clear and effective writing for technical and professional contexts.',
 'Dr. Lilian Omondi', 2, 30, 'Mon/Thu 15:00-16:00', 'English'),

('SCI201', 'Research Methods',
 'Quantitative and qualitative research methodologies and data analysis.',
 'Prof. Mark Ndungu', 3, 25, 'Wed/Fri 13:00-14:30', 'Science');