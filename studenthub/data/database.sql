-- ============================================================
-- StudentHub Portal Database Schema & Seed Data
-- Subject: ITUE203 - Web Development Frameworks (CHARUSAT FTE)
-- Practicals 8, 9, 10, 11, 12, 14, 15
-- ============================================================

CREATE DATABASE IF NOT EXISTS `studenthub_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `studenthub_db`;

-- Drop existing tables to ensure idempotent re-runs
DROP TABLE IF EXISTS `registrations`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `notices`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `contact_messages`;
DROP TABLE IF EXISTS `users`;

-- ------------------------------------------------------------
-- Table: users (Authentication, RBAC & Remember-Me)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) UNIQUE NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'student') NOT NULL DEFAULT 'student',
    `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    `remember_token` VARCHAR(255) DEFAULT NULL,
    `last_login` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_email` (`email`),
    INDEX `idx_users_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: students (Student Profile Management & CRUD)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL,
    `student_id_number` VARCHAR(30) UNIQUE NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) UNIQUE NOT NULL,
    `mobile` VARCHAR(20) NOT NULL,
    `course` VARCHAR(50) NOT NULL,
    `year_of_study` VARCHAR(20) NOT NULL,
    `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
    `address` TEXT DEFAULT NULL,
    `bio` TEXT DEFAULT NULL,
    `profile_pic` VARCHAR(255) DEFAULT 'default_avatar.svg',
    `status` ENUM('active', 'inactive', 'deleted') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_students_course` (`course`),
    INDEX `idx_students_status` (`status`),
    INDEX `idx_students_user` (`user_id`),
    CONSTRAINT `fk_students_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: events (Event Management with Posters & CRUD)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `event_date` DATE NOT NULL,
    `event_time` TIME NOT NULL,
    `venue` VARCHAR(150) NOT NULL,
    `description` TEXT NOT NULL,
    `poster_url` VARCHAR(255) DEFAULT 'default_poster.svg',
    `max_seats` INT NOT NULL DEFAULT 100,
    `registered_count` INT NOT NULL DEFAULT 0,
    `status` ENUM('upcoming', 'ongoing', 'completed', 'cancelled') NOT NULL DEFAULT 'upcoming',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_events_category` (`category`),
    INDEX `idx_events_date` (`event_date`),
    INDEX `idx_events_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: registrations (Event Registrations Mapping)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `registrations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `student_id` INT DEFAULT NULL,
    `event_id` INT NOT NULL,
    `student_name` VARCHAR(100) NOT NULL,
    `student_email` VARCHAR(150) NOT NULL,
    `status` ENUM('registered', 'attended', 'cancelled') NOT NULL DEFAULT 'registered',
    `registration_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `remarks` VARCHAR(255) DEFAULT NULL,
    UNIQUE KEY `unique_user_event` (`user_id`, `event_id`),
    INDEX `idx_reg_event` (`event_id`),
    INDEX `idx_reg_user` (`user_id`),
    CONSTRAINT `fk_reg_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_reg_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: notices (Campus Announcements & Notices)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notices` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `category` VARCHAR(50) NOT NULL DEFAULT 'General',
    `content` TEXT NOT NULL,
    `priority` ENUM('normal', 'urgent') NOT NULL DEFAULT 'normal',
    `posted_date` DATE NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: audit_logs (Traceability & Action Tracking)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL,
    `user_role` VARCHAR(20) DEFAULT 'guest',
    `action` VARCHAR(100) NOT NULL,
    `details` TEXT NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_audit_user` (`user_id`),
    INDEX `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: contact_messages (Feedback & Contact Form Submissions)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `subject` VARCHAR(200) NOT NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('new', 'read', 'replied') DEFAULT 'new',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- STORED PROCEDURES (Practical 8 Advanced Extension)
-- ============================================================
DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_GetStudentRegistrationSummary`$$
CREATE PROCEDURE `sp_GetStudentRegistrationSummary`(IN p_user_id INT)
BEGIN
    SELECT 
        r.id AS registration_id,
        r.registration_date,
        r.status AS registration_status,
        e.id AS event_id,
        e.title AS event_title,
        e.category,
        e.event_date,
        e.event_time,
        e.venue,
        e.poster_url
    FROM registrations r
    INNER JOIN events e ON r.event_id = e.id
    WHERE r.user_id = p_user_id
    ORDER BY r.registration_date DESC;
END$$

DROP PROCEDURE IF EXISTS `sp_RegisterStudentForEvent`$$
CREATE PROCEDURE `sp_RegisterStudentForEvent`(
    IN p_user_id INT,
    IN p_event_id INT,
    IN p_student_name VARCHAR(100),
    IN p_student_email VARCHAR(150),
    OUT p_status_code INT,
    OUT p_message VARCHAR(255)
)
BEGIN
    DECLARE v_exists INT DEFAULT 0;
    DECLARE v_seats_left INT DEFAULT 0;
    
    -- Check if user is already registered
    SELECT COUNT(*) INTO v_exists FROM registrations 
    WHERE user_id = p_user_id AND event_id = p_event_id;
    
    IF v_exists > 0 THEN
        SET p_status_code = 409;
        SET p_message = 'You are already registered for this event.';
    ELSE
        -- Check seat availability
        SELECT (max_seats - registered_count) INTO v_seats_left 
        FROM events WHERE id = p_event_id;
        
        IF v_seats_left <= 0 THEN
            SET p_status_code = 400;
            SET p_message = 'This event is currently full.';
        ELSE
            -- Insert registration
            INSERT INTO registrations (user_id, event_id, student_name, student_email, status)
            VALUES (p_user_id, p_event_id, p_student_name, p_student_email, 'registered');
            
            -- Increment event registration count
            UPDATE events SET registered_count = registered_count + 1 WHERE id = p_event_id;
            
            SET p_status_code = 200;
            SET p_message = 'Registration completed successfully!';
        END IF;
    END IF;
END$$

DELIMITER ;

-- ============================================================
-- SEED DATA
-- Default Passwords:
-- Admin: Admin@123
-- Student: Student@123
-- ============================================================

-- 1. Users Seed
INSERT INTO `users` (`id`, `full_name`, `email`, `password_hash`, `role`, `status`) VALUES
(1, 'System Administrator', 'admin@charusat.edu.in', '$2y$10$s8QWvVYOU/64IBHAkiMAbeY.nG8ZdwyJpwQD0.UoDQsQhlK/Knh1O', 'admin', 'active'),
(2, 'Aarav Sharma', 'aarav.sharma@charusat.edu.in', '$2y$10$Vzu5Rlfo9KsPyNx.PruDde8fxT00m3OcdtT5r9E5VzhvDpycZH7Gu', 'student', 'active'),
(3, 'Diya Patel', 'diya.patel@charusat.edu.in', '$2y$10$Vzu5Rlfo9KsPyNx.PruDde8fxT00m3OcdtT5r9E5VzhvDpycZH7Gu', 'student', 'active'),
(4, 'Rohan Mehta', 'rohan.mehta@charusat.edu.in', '$2y$10$Vzu5Rlfo9KsPyNx.PruDde8fxT00m3OcdtT5r9E5VzhvDpycZH7Gu', 'student', 'active'),
(5, 'Ananya Desai', 'ananya.desai@charusat.edu.in', '$2y$10$Vzu5Rlfo9KsPyNx.PruDde8fxT00m3OcdtT5r9E5VzhvDpycZH7Gu', 'student', 'active');

-- 2. Students Seed (20+ records for Practical 11)
INSERT INTO `students` (`id`, `user_id`, `student_id_number`, `full_name`, `email`, `mobile`, `course`, `year_of_study`, `gender`, `address`, `bio`, `status`) VALUES
(1, 2, '23IT001', 'Aarav Sharma', 'aarav.sharma@charusat.edu.in', '9876543210', 'B.Tech IT', '3rd Year', 'Male', 'Anand, Gujarat', 'Passionate about Web Development and Cloud Computing.', 'active'),
(2, 3, '23IT002', 'Diya Patel', 'diya.patel@charusat.edu.in', '9876543211', 'B.Tech IT', '3rd Year', 'Female', 'Nadiad, Gujarat', 'UI/UX enthusiast and competitive coder.', 'active'),
(3, 4, '23CE003', 'Rohan Mehta', 'rohan.mehta@charusat.edu.in', '9876543212', 'B.Tech CE', '2nd Year', 'Male', 'Vadodara, Gujarat', 'Machine learning and robotics hobbyist.', 'active'),
(4, 5, '23CS004', 'Ananya Desai', 'ananya.desai@charusat.edu.in', '9876543213', 'B.Tech CSE', '3rd Year', 'Female', 'Ahmedabad, Gujarat', 'Open-source contributor & hackathon enthusiast.', 'active'),
(5, NULL, '23IT005', 'Kabir Joshi', 'kabir.joshi@charusat.edu.in', '9876543214', 'B.Tech IT', '1st Year', 'Male', 'Surat, Gujarat', 'Exploring frontend engineering and cybersecurity.', 'active'),
(6, NULL, '23CE006', 'Isha Trivedi', 'isha.trivedi@charusat.edu.in', '9876543215', 'B.Tech CE', '4th Year', 'Female', 'Rajkot, Gujarat', 'Full-stack developer working on final year capstone.', 'active'),
(7, NULL, '23CS007', 'Devansh Shah', 'devansh.shah@charusat.edu.in', '9876543216', 'B.Tech CSE', '2nd Year', 'Male', 'Gandhinagar, Gujarat', 'Competitive programming lead at Google Developer Group.', 'active'),
(8, NULL, '23IT008', 'Pooja Bhatt', 'pooja.bhatt@charusat.edu.in', '9876543217', 'B.Tech IT', '3rd Year', 'Female', 'Anand, Gujarat', 'Interested in database systems and cloud infrastructure.', 'active'),
(9, NULL, '23EC009', 'Siddharth Rao', 'siddharth.rao@charusat.edu.in', '9876543218', 'B.Tech EC', '2nd Year', 'Male', 'Vadodara, Gujarat', 'IoT hardware tinkerer and embedded systems lover.', 'active'),
(10, NULL, '23CS010', 'Tanvi Shah', 'tanvi.shah@charusat.edu.in', '9876543219', 'B.Tech CSE', '3rd Year', 'Female', 'Nadiad, Gujarat', 'AI research intern and tech blogger.', 'active'),
(11, NULL, '23IT011', 'Yash Parmar', 'yash.parmar@charusat.edu.in', '9876543220', 'B.Tech IT', '4th Year', 'Male', 'Ahmedabad, Gujarat', 'Cybersecurity researcher and CTF player.', 'active'),
(12, NULL, '23CE012', 'Rhea Kothari', 'rhea.kothari@charusat.edu.in', '9876543221', 'B.Tech CE', '1st Year', 'Female', 'Surat, Gujarat', 'Freshman eager to learn web development and Python.', 'active'),
(13, NULL, '23CS013', 'Manan Dave', 'manan.dave@charusat.edu.in', '9876543222', 'B.Tech CSE', '2nd Year', 'Male', 'Rajkot, Gujarat', 'App developer building cross-platform Flutter tools.', 'active'),
(14, NULL, '23IT014', 'Kavya Varma', 'kavya.varma@charusat.edu.in', '9876543223', 'B.Tech IT', '3rd Year', 'Female', 'Vadodara, Gujarat', 'Data visualization engineer and statistics nerd.', 'active'),
(15, NULL, '23EC015', 'Varun Solanki', 'varun.solanki@charusat.edu.in', '9876543224', 'B.Tech EC', '3rd Year', 'Male', 'Anand, Gujarat', 'Smart campus automation project team member.', 'active'),
(16, NULL, '23CS016', 'Sneha Nair', 'sneha.nair@charusat.edu.in', '9876543225', 'B.Tech CSE', '4th Year', 'Female', 'Gandhinagar, Gujarat', 'Campus placement coordinator and web lead.', 'active'),
(17, NULL, '23IT017', 'Aditya Prajapati', 'aditya.prajapati@charusat.edu.in', '9876543226', 'B.Tech IT', '2nd Year', 'Male', 'Nadiad, Gujarat', 'Passionate about backend microservices and Docker.', 'active'),
(18, NULL, '23CE018', 'Meera Soni', 'meera.soni@charusat.edu.in', '9876543227', 'B.Tech CE', '3rd Year', 'Female', 'Ahmedabad, Gujarat', 'Graphic designer and frontend web enthusiast.', 'active'),
(19, NULL, '23CS019', 'Kunal Trivedi', 'kunal.trivedi@charusat.edu.in', '9876543228', 'B.Tech CSE', '1st Year', 'Male', 'Vadodara, Gujarat', 'Exploring C++, DSA, and web development frameworks.', 'active'),
(20, NULL, '23IT020', 'Priya Panchal', 'priya.panchal@charusat.edu.in', '9876543229', 'B.Tech IT', '4th Year', 'Female', 'Anand, Gujarat', 'Aspiring Cloud DevOps Architect.', 'active'),
(21, NULL, '23CE021', 'Harsh Chavda', 'harsh.chavda@charusat.edu.in', '9876543230', 'B.Tech CE', '2nd Year', 'Male', 'Surat, Gujarat', 'Mobile app developer and blockchain student.', 'active');

-- 3. Events Seed (15+ events for Practical 6 & 12)
INSERT INTO `events` (`id`, `title`, `category`, `event_date`, `event_time`, `venue`, `description`, `poster_url`, `max_seats`, `registered_count`, `status`) VALUES
(1, 'TechFest Spoural 2026', 'Technical', '2026-11-15', '09:00:00', 'FTE Auditorium & Central Lawns', 'Annual national-level technical festival featuring coding sprints, robotics, hackathons, and web development challenges with attractive cash prizes.', 'event_techfest.svg', 250, 48, 'upcoming'),
(2, 'Web Dev Frameworks Hackathon', 'Hackathon', '2026-11-20', '10:00:00', 'Computer Lab 301, FTE Block B', 'Intense 24-hour sprint building full-stack web applications using modern semantic HTML5, CSS Grid, PHP, and AJAX Fetch APIs.', 'event_hackathon.svg', 80, 42, 'upcoming'),
(3, 'AI & Generative Deep Learning Seminar', 'Seminar', '2026-11-25', '14:30:00', 'Seminar Hall 1, Admin Block', 'Distinguished lecture by leading industry researchers on Foundation Models, Autonomous Agents, and Real-world AI applications.', 'event_ai_seminar.svg', 150, 65, 'upcoming'),
(4, 'Annual Cultural Night - Tarang', 'Cultural', '2026-12-05', '18:00:00', 'CHARUSAT Open Air Amphitheatre', 'Grand showcase of music, classical & folk dance, drama, and artistic talent by university students and cultural clubs.', 'event_cultural.svg', 500, 180, 'upcoming'),
(5, 'Campus Sports Meet - Aaveg', 'Sports', '2026-12-10', '08:00:00', 'University Sports Complex', 'Inter-departmental cricket, football, basketball, badminton, and athletics tournaments celebrating sportsmanship.', 'event_sports.svg', 300, 110, 'upcoming'),
(6, 'Cybersecurity & Ethical Hacking Bootcamp', 'Workshop', '2026-11-28', '10:00:00', 'Network Security Lab 204', 'Hands-on practical session covering web security, SQL injection defense, CSRF mitigation, and password hashing protocols.', 'event_cybersecurity.svg', 60, 58, 'upcoming'),
(7, 'Cloud Computing & DevOps Workshop', 'Workshop', '2026-12-02', '11:00:00', 'Cloud Computing Center', 'Learn containerization with Docker, CI/CD automated deployment pipelines, and cloud hosting architecture.', 'event_cloud.svg', 70, 35, 'upcoming'),
(8, 'Resume Building & Mock Interview Session', 'Career', '2026-12-08', '14:00:00', 'Training & Placement Auditorium', 'Expert guidance on portfolio preparation, GitHub showcase, technical mock interviews, and HR negotiation skills.', 'event_career.svg', 120, 89, 'upcoming'),
(9, 'RoboWars & Drone Racing Challenge', 'Technical', '2026-12-14', '09:30:00', 'Robotics Arena, Mech Block', 'Exciting combat between student-designed autonomous robots and obstacle course drone piloting contest.', 'event_robowars.svg', 100, 45, 'upcoming'),
(10, 'Entrepreneurship & Startup Pitch Day', 'Seminar', '2026-12-18', '10:30:00', 'CHARUSAT Innovation Incubation Hub', 'Pitch innovative tech startups directly to angel investors, venture capitalists, and alumni entrepreneurs.', 'event_startup.svg', 90, 30, 'upcoming'),
(11, 'Google Developer Student Clubs Info Session', 'Club', '2026-11-12', '16:00:00', 'FTE Seminar Hall 2', 'Introduction to upcoming open-source projects, solution challenge 2027, and peer learning community circles.', 'event_gdsc.svg', 100, 75, 'upcoming'),
(12, 'Photography & Digital Art Exhibition', 'Cultural', '2026-12-20', '10:00:00', 'FTE Art Gallery Walkway', 'Showcasing breathtaking wildlife, architecture, and campus life photography created by photography club members.', 'event_photography.svg', 80, 24, 'upcoming'),
(13, 'Open Source Day & Git/GitHub Workshop', 'Workshop', '2026-11-10', '14:00:00', 'Computing Center Lab 1', 'Master Git version control, branching strategies, pull requests, and collaborative repository management.', 'event_git.svg', 90, 80, 'upcoming'),
(14, 'Competitive Programming Blitz - CodeCraft', 'Technical', '2026-12-22', '18:00:00', 'Online / Virtual Platform', 'Fast-paced algorithmic challenge testing speed, data structures, dynamic programming, and optimization.', 'event_codecraft.svg', 200, 95, 'upcoming'),
(15, 'Alumni Mentorship Round Table', 'Career', '2026-12-28', '15:00:00', 'Council Room, FTE Block A', 'Direct networking with CHARUSAT alumni working in top tech companies across the globe.', 'event_alumni.svg', 60, 40, 'upcoming');

-- 4. Registrations Seed
INSERT INTO `registrations` (`id`, `user_id`, `student_id`, `event_id`, `student_name`, `student_email`, `status`) VALUES
(1, 2, 1, 1, 'Aarav Sharma', 'aarav.sharma@charusat.edu.in', 'registered'),
(2, 2, 1, 2, 'Aarav Sharma', 'aarav.sharma@charusat.edu.in', 'registered'),
(3, 2, 1, 6, 'Aarav Sharma', 'aarav.sharma@charusat.edu.in', 'attended'),
(4, 3, 2, 1, 'Diya Patel', 'diya.patel@charusat.edu.in', 'registered'),
(5, 3, 2, 3, 'Diya Patel', 'diya.patel@charusat.edu.in', 'registered'),
(6, 4, 3, 2, 'Rohan Mehta', 'rohan.mehta@charusat.edu.in', 'registered'),
(7, 4, 3, 5, 'Rohan Mehta', 'rohan.mehta@charusat.edu.in', 'attended'),
(8, 5, 4, 1, 'Ananya Desai', 'ananya.desai@charusat.edu.in', 'registered');

-- 5. Notices Seed
INSERT INTO `notices` (`id`, `title`, `category`, `content`, `priority`, `posted_date`) VALUES
(1, 'Mid-Semester Examination Schedule Announced', 'Academic', 'The 3rd Semester examination schedule for ITUE203 and all other subjects has been published on the notice board.', 'urgent', '2026-10-20'),
(2, 'Submission Deadline for Practical Lab Records', 'Academic', 'All students must complete and submit Practicals 2 through 15 on GitHub and local deployment by next week.', 'urgent', '2026-10-22'),
(3, 'Registration Open for Spoural 2026 Annual TechFest', 'Events', 'Students can register for technical competitions, workshops, and robotics challenges through the StudentHub portal.', 'normal', '2026-10-18'),
(4, 'Library Hours Extended for Semester Preparations', 'Facilities', 'Central University Library will remain open until 10:00 PM on weekdays and 6:00 PM on weekends.', 'normal', '2026-10-15'),
(5, 'Campus WiFi Security & Certificate Update', 'IT Services', 'Please update the network security root certificate on personal devices to maintain uninterrupted campus WiFi access.', 'normal', '2026-10-12');

-- 6. Audit Logs Seed (Practical 14)
INSERT INTO `audit_logs` (`id`, `user_id`, `user_role`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 1, 'admin', 'SYSTEM_INITIALIZATION', 'StudentHub portal database initialized with schema and seed records', '127.0.0.1', '2026-10-22 09:00:00'),
(2, 1, 'admin', 'USER_LOGIN', 'Administrator logged in successfully', '127.0.0.1', '2026-10-22 09:05:00'),
(3, 2, 'student', 'EVENT_REGISTRATION', 'Student Aarav Sharma registered for TechFest Spoural 2026 (Event ID: 1)', '127.0.0.1', '2026-10-22 10:15:00'),
(4, 3, 'student', 'EVENT_REGISTRATION', 'Student Diya Patel registered for AI Seminar (Event ID: 3)', '127.0.0.1', '2026-10-22 11:20:00'),
(5, 1, 'admin', 'EVENT_CREATED', 'Admin created new event: Web Dev Frameworks Hackathon', '127.0.0.1', '2026-10-22 14:00:00'),
(6, 2, 'student', 'PROFILE_UPDATE', 'Aarav Sharma updated contact details and bio', '127.0.0.1', '2026-10-22 16:45:00');
