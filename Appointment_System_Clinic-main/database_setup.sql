-- Medi Clinic Appointment Management System - Database Setup
-- MariaDB Database Schema

-- Create database
CREATE DATABASE IF NOT EXISTS clinic_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Grant privileges to clinic_user (not root)
GRANT ALL PRIVILEGES ON clinic_db.* TO 'clinic_user'@'localhost' IDENTIFIED BY 'clinic_password';
FLUSH PRIVILEGES;

USE clinic_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('patient', 'doctor', 'clerk') NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    specialization VARCHAR(100) DEFAULT NULL,
    email_notifications BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_email (email)
);

-- Doctors table (extends users)
CREATE TABLE IF NOT EXISTS doctors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE NOT NULL,
    bio TEXT DEFAULT NULL,
    photo VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Appointments table
CREATE TABLE IF NOT EXISTS appointments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reference_no VARCHAR(20) UNIQUE NOT NULL,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    time_slot VARCHAR(50) NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed', 'no_show') DEFAULT 'pending',
    arrived BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id),
    FOREIGN KEY (doctor_id) REFERENCES users(id),
    INDEX idx_patient (patient_id),
    INDEX idx_doctor (doctor_id),
    INDEX idx_date (appointment_date),
    INDEX idx_status (status),
    UNIQUE KEY unique_booking (patient_id, doctor_id, appointment_date, time_slot)
);

-- Doctor Schedules table
CREATE TABLE IF NOT EXISTS doctor_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    doctor_id INT NOT NULL,
    day_of_week TINYINT NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_schedule (doctor_id, day_of_week),
    INDEX idx_doctor (doctor_id)
);

-- Blocked Slots table
CREATE TABLE IF NOT EXISTS blocked_slots (
    id INT PRIMARY KEY AUTO_INCREMENT,
    doctor_id INT DEFAULT NULL,
    blocked_date DATE NOT NULL,
    start_time TIME DEFAULT NULL,
    end_time TIME DEFAULT NULL,
    is_all_day BOOLEAN DEFAULT FALSE,
    reason VARCHAR(255) DEFAULT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_doctor (doctor_id),
    INDEX idx_date (blocked_date)
);

-- Clinic Settings table
CREATE TABLE IF NOT EXISTS clinic_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL
);

-- Holidays table
CREATE TABLE IF NOT EXISTS holidays (
    id INT PRIMARY KEY AUTO_INCREMENT,
    holiday_date DATE UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_holiday_date (holiday_date)
);

-- Notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type ENUM('arrival', 'reminder', 'confirmation', 'cancellation', 'new_appointment') NOT NULL,
    title VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    related_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created (created_at)
);

-- Email Queue table
CREATE TABLE IF NOT EXISTS email_queue (
    id INT PRIMARY KEY AUTO_INCREMENT,
    to_email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    body TEXT NOT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    scheduled_at DATETIME NOT NULL,
    sent_at DATETIME DEFAULT NULL,
    attempts INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_scheduled (scheduled_at)
);

-- Insert default clinic settings
INSERT INTO clinic_settings (setting_key, setting_value) VALUES
('clinic_name', 'Medi Clinic'),
('clinic_address', '123 Medical Center Drive, City, State 12345'),
('clinic_phone', '(555) 123-4567'),
('clinic_email', 'info@mediclinic.com'),
('clinic_operating_start', '08:00:00'),
('clinic_operating_end', '17:00:00'),
('clinic_operating_days', '1,2,3,4,5'),
('timezone', 'Asia/Manila')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- Insert demo users (passwords: clerk123, doctor123, patient123 - using bcrypt)
INSERT INTO users (username, password, email, full_name, role, phone, specialization) VALUES
('clerk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'clerk@mediclinic.com', 'Clerk User', 'clerk', '555-0001', NULL),
('doctor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor@mediclinic.com', 'Dr. Beethoven N. Bongon', 'doctor', '555-0002', 'Internal Medicine'),
('doctor2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor2@mediclinic.com', 'Dr. Hiroshi A. Nakaegawa', 'doctor', '555-0003', 'Cardiology'),
('doctor3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor3@mediclinic.com', 'Dr. Maria L. Dacumos', 'doctor', '555-0004', 'Pediatrics'),
('patient', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient@clinic.com', 'John Doe', 'patient', '555-0100', NULL)
ON DUPLICATE KEY UPDATE password = VALUES(password);

-- Insert doctor details
INSERT INTO doctors (user_id, bio, photo) VALUES
(2, 'Dr. Beethoven Bongon is in the field of Internal Medicine. Our doctor treats patients at St. Vincent General Hospital in Cebu City, Cebu. Patients are accepted by appointment.', 'imgs/dr-bongon.png'),
(3, 'Dr. Hiroshi Nakaegawa is a specialist in Cardiology with over 10 years of experience diagnosing and treating heart conditions.', 'imgs/dr-nakaegawa.png'),
(4, 'Dr. Maria Dacumos is a dedicated Pediatrician with extensive experience in child and adolescent healthcare.', 'imgs/dr-dacumos.png')
ON DUPLICATE KEY UPDATE bio = VALUES(bio);

-- Insert doctor schedules (Monday-Friday, 8am-4pm for Dr. Bongon)
INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time) VALUES
(2, 1, '08:00:00', '16:00:00'),
(2, 2, '08:00:00', '16:00:00'),
(2, 3, '08:00:00', '16:00:00'),
(2, 4, '08:00:00', '16:00:00'),
(2, 5, '08:00:00', '16:00:00'),
-- Dr. Nakaegawa (10am-5pm)
(3, 1, '10:00:00', '17:00:00'),
(3, 2, '10:00:00', '17:00:00'),
(3, 3, '10:00:00', '17:00:00'),
(3, 4, '08:00:00', '16:00:00'),
(3, 5, '08:00:00', '16:00:00'),
-- Dr. Dacumos (8am-4pm, Tuesday/Thursday 10am-6pm)
(4, 1, '08:00:00', '16:00:00'),
(4, 2, '10:00:00', '18:00:00'),
(4, 3, '08:00:00', '16:00:00'),
(4, 4, '10:00:00', '18:00:00'),
(4, 5, '08:00:00', '16:00:00')
ON DUPLICATE KEY UPDATE start_time = VALUES(start_time), end_time = VALUES(end_time);