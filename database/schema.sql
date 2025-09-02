-- Database schema for meeting scheduling application
CREATE DATABASE IF NOT EXISTS agendamento_reunioes;
USE agendamento_reunioes;

-- Users table with role-based permissions
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('hr_manager', 'admin', 'common_user') NOT NULL DEFAULT 'common_user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- HR Manager availability slots
CREATE TABLE availability_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hr_manager_id INT NOT NULL,
    day_of_week TINYINT NOT NULL, -- 0=Sunday, 1=Monday, etc.
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    slot_duration INT NOT NULL DEFAULT 30, -- Duration in minutes
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hr_manager_id) REFERENCES users(id) ON DELETE CASCADE
);

-- HR Manager availability slots for specific dates
CREATE TABLE specific_availability_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hr_manager_id INT NOT NULL,
    specific_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    slot_duration INT NOT NULL DEFAULT 60, -- Duration in minutes
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hr_manager_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_specific_slot (hr_manager_id, specific_date, start_time)
);

-- Meeting appointments
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hr_manager_id INT NOT NULL,
    user_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    description TEXT,
    status ENUM('scheduled', 'completed', 'cancelled') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hr_manager_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_appointment (hr_manager_id, appointment_date, start_time)
);

-- Insert default HR Manager
INSERT INTO users (name, email, password, role) VALUES 
('Gestor RH', 'rh@empresa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hr_manager'),
('Admin Empresa', 'admin@empresa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Usuário Teste', 'usuario@empresa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'common_user');

-- Default availability (Monday to Friday, 9AM to 5PM)
INSERT INTO availability_slots (hr_manager_id, day_of_week, start_time, end_time, slot_duration) VALUES
(1, 1, '09:00:00', '17:00:00', 60), -- Monday
(1, 2, '09:00:00', '17:00:00', 60), -- Tuesday
(1, 3, '09:00:00', '17:00:00', 60), -- Wednesday
(1, 4, '09:00:00', '17:00:00', 60), -- Thursday
(1, 5, '09:00:00', '17:00:00', 60); -- Friday
