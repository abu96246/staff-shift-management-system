-- Create database
CREATE DATABASE IF NOT EXISTS railway_shift_system;
USE railway_shift_system;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'supervisor', 'staff') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Staff profiles table
CREATE TABLE staff_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    employee_id VARCHAR(20) UNIQUE NOT NULL,
    department VARCHAR(50) NOT NULL,
    designation VARCHAR(50) NOT NULL,
    contact_phone VARCHAR(20),
    shift_preference ENUM('morning', 'evening', 'night', 'any') DEFAULT 'any',
    status ENUM('active', 'inactive') DEFAULT 'active',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Shifts table
CREATE TABLE shifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shift_name VARCHAR(50) NOT NULL,
    shift_type ENUM('morning', 'evening', 'night') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    description TEXT,
    max_staff INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Shift assignments table
CREATE TABLE shift_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shift_id INT NOT NULL,
    staff_id INT NOT NULL,
    assignment_date DATE NOT NULL,
    status ENUM('scheduled', 'completed', 'absent', 'cancelled') DEFAULT 'scheduled',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shift_id) REFERENCES shifts(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES staff_profiles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (shift_id, staff_id, assignment_date)
);

-- Staff availability table
CREATE TABLE staff_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id INT NOT NULL,
    available_date DATE NOT NULL,
    status ENUM('available', 'unavailable', 'partial') NOT NULL,
    shift_preference ENUM('morning', 'evening', 'night', 'any') DEFAULT 'any',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_id) REFERENCES staff_profiles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_availability (staff_id, available_date)
);

-- Shift swap requests table
CREATE TABLE shift_swap_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requester_id INT NOT NULL,
    requested_staff_id INT NOT NULL,
    original_shift_id INT NOT NULL,
    target_shift_id INT NOT NULL,
    swap_date DATE NOT NULL,
    reason TEXT,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    approved_by INT NULL,
    date_submitted TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_processed TIMESTAMP NULL,
    FOREIGN KEY (requester_id) REFERENCES staff_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (requested_staff_id) REFERENCES staff_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (original_shift_id) REFERENCES shifts(id) ON DELETE CASCADE,
    FOREIGN KEY (target_shift_id) REFERENCES shifts(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Notifications table
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'warning', 'success', 'danger') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Emergency coverage requests table
CREATE TABLE emergency_coverage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shift_id INT NOT NULL,
    original_staff_id INT NULL,
    coverage_date DATE NOT NULL,
    reason TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    status ENUM('open', 'covered', 'cancelled') DEFAULT 'open',
    requested_by INT NOT NULL,
    covered_by INT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    covered_at TIMESTAMP NULL,
    FOREIGN KEY (shift_id) REFERENCES shifts(id) ON DELETE CASCADE,
    FOREIGN KEY (original_staff_id) REFERENCES staff_profiles(id) ON DELETE SET NULL,
    FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (covered_by) REFERENCES staff_profiles(id) ON DELETE SET NULL
);

-- Attendance tracking table
CREATE TABLE attendance_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    check_in_time TIMESTAMP NULL,
    check_out_time TIMESTAMP NULL,
    break_start_time TIMESTAMP NULL,
    break_end_time TIMESTAMP NULL,
    total_hours DECIMAL(4,2) NULL,
    overtime_hours DECIMAL(4,2) DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES shift_assignments(id) ON DELETE CASCADE
);

-- System settings table
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Audit log table
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50) NOT NULL,
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default admin user
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@railway.et', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert default shifts
INSERT INTO shifts (shift_name, shift_type, start_time, end_time, description, max_staff) VALUES
('Morning Shift', 'morning', '06:00:00', '14:00:00', 'Morning locomotive operations', 5),
('Evening Shift', 'evening', '14:00:00', '22:00:00', 'Evening locomotive operations', 4),
('Night Shift', 'night', '22:00:00', '06:00:00', 'Night locomotive operations', 3);

-- Insert sample staff profiles
INSERT INTO staff_profiles (user_id, full_name, employee_id, department, designation, contact_phone) VALUES
(1, 'System Administrator', 'EMP001', 'IT', 'System Admin', '+251911234567');

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('notification_email', 'admin@railway.et', 'Default email for system notifications'),
('shift_reminder_hours', '2', 'Hours before shift to send reminder'),
('max_overtime_hours', '4', 'Maximum overtime hours per shift'),
('emergency_contact', '+251911234567', 'Emergency contact number'),
('system_timezone', 'Africa/Addis_Ababa', 'System timezone');

-- Insert sample notifications
INSERT INTO notifications (user_id, title, message, type) VALUES
(1, 'Welcome to the System', 'Welcome to the Railway Shift Management System. Please update your profile.', 'info'),
(1, 'Shift Assignment', 'You have been assigned to the morning shift tomorrow.', 'success'),
(1, 'Emergency Coverage Needed', 'Emergency coverage needed for night shift on Dec 25.', 'warning');
