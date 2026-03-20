-- ============================================
-- DATABASE
-- ============================================

CREATE DATABASE IF NOT EXISTS learn4kids_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE learn4kids_db;

-- ============================================
-- DROP TABLES (REVERSE FK ORDER)
-- ============================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS learn4kids_visited_courses;
DROP TABLE IF EXISTS learn4kids_child_courses;
DROP TABLE IF EXISTS learn4kids_audios;
DROP TABLE IF EXISTS learn4kids_payments;
DROP TABLE IF EXISTS learn4kids_children;
DROP TABLE IF EXISTS learn4kids_courses;
DROP TABLE IF EXISTS learn4kids_news;
DROP TABLE IF EXISTS learn4kids_parents;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- PARENTS
-- ============================================

CREATE TABLE learn4kids_parents (
    id VARCHAR(50) PRIMARY KEY,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    login VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    email VARCHAR(150) UNIQUE,
    is_active BOOLEAN DEFAULT 0,
    activation_code VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- CHILDREN
-- ============================================

CREATE TABLE learn4kids_children (
    id VARCHAR(50) PRIMARY KEY,
    parent_id VARCHAR(50),
    name VARCHAR(100),
    login VARCHAR(100),
    password VARCHAR(255),
    parent_responsible BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_parent_id (parent_id),

    CONSTRAINT fk_children_parent
        FOREIGN KEY (parent_id)
        REFERENCES learn4kids_parents(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- COURSES
-- ============================================

CREATE TABLE learn4kids_courses (
    code VARCHAR(50) PRIMARY KEY,
    name VARCHAR(150),
    amount DECIMAL(10,2),
    validity VARCHAR(50),
    description TEXT
) ENGINE=InnoDB;

-- ============================================
-- CHILD COURSES
-- ============================================

CREATE TABLE learn4kids_child_courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    child_id VARCHAR(50),
    course_code VARCHAR(50),
    is_paid BOOLEAN DEFAULT 0,
    picked_date DATETIME,
    expiry_date DATETIME,

    INDEX idx_child_id (child_id),
    INDEX idx_course_code (course_code),

    CONSTRAINT fk_child_courses_child
        FOREIGN KEY (child_id)
        REFERENCES learn4kids_children(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_child_courses_course
        FOREIGN KEY (course_code)
        REFERENCES learn4kids_courses(code)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- AUDIOS
-- ============================================

CREATE TABLE learn4kids_audios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    child_id VARCHAR(50),
    title VARCHAR(200),
    description TEXT,
    audio_url VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_audio_child (child_id),

    CONSTRAINT fk_audio_child
        FOREIGN KEY (child_id)
        REFERENCES learn4kids_children(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- VISITED COURSES
-- ============================================

CREATE TABLE learn4kids_visited_courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    child_id VARCHAR(50),
    course_code VARCHAR(50),
    time_spent INT,
    last_connection DATETIME,

    INDEX idx_visit_child (child_id),
    INDEX idx_visit_course (course_code),

    CONSTRAINT fk_visit_child
        FOREIGN KEY (child_id)
        REFERENCES learn4kids_children(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_visit_course
        FOREIGN KEY (course_code)
        REFERENCES learn4kids_courses(code)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- PAYMENTS
-- ============================================

CREATE TABLE learn4kids_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id VARCHAR(50),
    child_id VARCHAR(50),
    course_code VARCHAR(50),
    amount DECIMAL(10,2),
    is_paid BOOLEAN,
    payment_date DATETIME,

    INDEX idx_payment_parent (parent_id),
    INDEX idx_payment_child (child_id),

    CONSTRAINT fk_payment_parent
        FOREIGN KEY (parent_id)
        REFERENCES learn4kids_parents(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_payment_child
        FOREIGN KEY (child_id)
        REFERENCES learn4kids_children(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_payment_course
        FOREIGN KEY (course_code)
        REFERENCES learn4kids_courses(code)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- NEWS
-- ============================================

CREATE TABLE learn4kids_news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    description TEXT,
    date DATETIME
) ENGINE=InnoDB;

-- ============================================
-- SAMPLE DATA
-- ============================================

-- Parent (password = 123456 hashed example)
INSERT INTO learn4kids_parents
(id, first_name, last_name, login, password, email, is_active, activation_code)
VALUES
('parent01', 'John', 'Doe', 'parent_login', 
'$2y$10$LFosItlIB32nU6li3hduyeGrBA.Wn36MY4BbE.M/594mWrINnswWO', 
'parent@email.com', 1, NULL);

-- Children
INSERT INTO learn4kids_children
(id, parent_id, name, login, password, parent_responsible)
VALUES
('child01', 'parent01', 'Emma', 'emma01', '$2y$10$LFosItlIB32nU6li3hduyeGrBA.Wn36MY4BbE.M/594mWrINnswWO', 1),
('child02', 'parent01', 'Lucas', 'lucas01', '$2y$10$LFosItlIB32nU6li3hduyeGrBA.Wn36MY4BbE.M/594mWrINnswWO', 0);

-- Courses
INSERT INTO learn4kids_courses
(code, name, amount, validity, description)
VALUES
('C001', 'Mathematics Basic', 50.00, '1 Month', 'Basic math skills'),
('C002', 'Reading Skills', 30.00, '1 Term', 'Improve reading comprehension'),
('C003', 'Science Explorer', 40.00, '1 Month', 'Fun science learning');

-- Child Courses
INSERT INTO learn4kids_child_courses
(child_id, course_code, is_paid, picked_date, expiry_date)
VALUES
('child01', 'C001', 1, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
('child01', 'C002', 0, NOW(), NULL);

-- Audio
INSERT INTO learn4kids_audios
(child_id, title, description, audio_url)
VALUES
('child01', 'My First Reading', 'Practice reading audio', '/uploads/audios/audio1.mp3');

-- Visited Courses
INSERT INTO learn4kids_visited_courses
(child_id, course_code, time_spent, last_connection)
VALUES
('child01', 'C001', 120, NOW());

-- Payments
INSERT INTO learn4kids_payments
(parent_id, child_id, course_code, amount, is_paid, payment_date)
VALUES
('parent01', 'child01', 'C001', 50.00, 1, NOW());

-- News
INSERT INTO learn4kids_news
(title, description, date)
VALUES
('Welcome to Learn4Kids', 'New courses available now!', NOW());