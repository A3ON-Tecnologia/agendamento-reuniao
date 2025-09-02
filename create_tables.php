<?php
// Script to create database and tables if they don't exist
require_once 'config/database.php';

echo "<h2>Criação de Banco de Dados e Tabelas</h2>";

try {
    // First, connect without specifying database to create it
    $conn = new PDO("mysql:host=localhost", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $conn->exec("CREATE DATABASE IF NOT EXISTS agendamento_reunioes");
    echo "<p style='color: green;'>✅ Banco de dados 'agendamento_reunioes' criado/verificado!</p>";
    
    // Now connect to the specific database
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Falha na conexão com o banco de dados");
    }
    
    // Create users table
    $sql_users = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('hr_manager', 'admin', 'common_user') NOT NULL DEFAULT 'common_user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $db->exec($sql_users);
    echo "<p style='color: green;'>✅ Tabela 'users' criada/verificada!</p>";
    
    // Create availability_slots table
    $sql_availability = "CREATE TABLE IF NOT EXISTS availability_slots (
        id INT AUTO_INCREMENT PRIMARY KEY,
        hr_manager_id INT NOT NULL,
        day_of_week TINYINT NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        slot_duration INT NOT NULL DEFAULT 30,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (hr_manager_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    $db->exec($sql_availability);
    echo "<p style='color: green;'>✅ Tabela 'availability_slots' criada/verificada!</p>";
    
    // Create specific_availability_slots table
    $sql_specific = "CREATE TABLE IF NOT EXISTS specific_availability_slots (
        id INT AUTO_INCREMENT PRIMARY KEY,
        hr_manager_id INT NOT NULL,
        specific_date DATE NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        slot_duration INT NOT NULL DEFAULT 60,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (hr_manager_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_specific_slot (hr_manager_id, specific_date, start_time)
    )";
    
    $db->exec($sql_specific);
    echo "<p style='color: green;'>✅ Tabela 'specific_availability_slots' criada/verificada!</p>";
    
    // Create appointments table
    $sql_appointments = "CREATE TABLE IF NOT EXISTS appointments (
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
    )";
    
    $db->exec($sql_appointments);
    echo "<p style='color: green;'>✅ Tabela 'appointments' criada/verificada!</p>";
    
    // Check if default users exist, if not create them
    $check_users = $db->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'hr_manager'");
    $check_users->execute();
    $user_count = $check_users->fetch();
    
    if ($user_count['count'] == 0) {
        // Insert default users
        $default_password = password_hash('123456', PASSWORD_DEFAULT);
        
        $insert_users = "INSERT INTO users (name, email, password, role) VALUES 
            ('Gestor RH', 'rh@empresa.com', ?, 'hr_manager'),
            ('Admin Empresa', 'admin@empresa.com', ?, 'admin'),
            ('Usuário Teste', 'usuario@empresa.com', ?, 'common_user')";
        
        $stmt = $db->prepare($insert_users);
        $stmt->execute([$default_password, $default_password, $default_password]);
        
        echo "<p style='color: green;'>✅ Usuários padrão criados!</p>";
        echo "<p><strong>Login padrão:</strong></p>";
        echo "<ul>";
        echo "<li>Gestor RH: rh@empresa.com / 123456</li>";
        echo "<li>Admin: admin@empresa.com / 123456</li>";
        echo "<li>Usuário: usuario@empresa.com / 123456</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Usuários já existem no sistema.</p>";
    }
    
    echo "<h3>✅ Todas as tabelas foram criadas com sucesso!</h3>";
    echo "<p><a href='dashboard/hr_manager.php'>Acessar Dashboard do Gestor RH</a></p>";
    echo "<p><a href='test_db.php'>Executar Teste de Diagnóstico</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}
?>
