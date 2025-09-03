<?php
header('Content-Type: application/json');
// Auth removed for direct access
require_once '../config/database.php';
require_once '../classes/User.php';

// Mock user info for direct access
$user_info = ['id' => 1, 'role' => 'hr_manager'];

// Debug logging
error_log("Profile update attempt - User ID: " . $user_info['id']);
error_log("POST data: " . json_encode($_POST));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

error_log("Parsed data - Name: $name, Email: $email, Password length: " . strlen($password));

if (empty($name) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Nome e email são obrigatórios']);
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Formato de email inválido']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    error_log("Database connection failed");
    echo json_encode(['success' => false, 'message' => 'Erro de conexão com banco de dados']);
    exit();
}

try {
    // Check if email already exists for another user
    $query = "SELECT id FROM users WHERE email = :email AND id != :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':user_id', $user_info['id']);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        error_log("Email already exists for another user");
        echo json_encode(['success' => false, 'message' => 'Este email já está sendo usado por outro usuário']);
        exit();
    }

    // Build update query
    if (!empty($password)) {
        $query = "UPDATE users SET name = :name, email = :email, password = :password WHERE id = :user_id";
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        error_log("Updating with password");
    } else {
        $query = "UPDATE users SET name = :name, email = :email WHERE id = :user_id";
        error_log("Updating without password");
    }
    
    error_log("SQL Query: $query");
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':user_id', $user_info['id']);
    
    if (!empty($password)) {
        $stmt->bindParam(':password', $hashed_password);
    }
    
    $result = $stmt->execute();
    error_log("Query execution result: " . ($result ? 'true' : 'false'));
    error_log("Affected rows: " . $stmt->rowCount());
    
    if ($result && $stmt->rowCount() > 0) {
        // Update session data
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        
        error_log("Profile updated successfully");
        echo json_encode(['success' => true, 'message' => 'Perfil atualizado com sucesso']);
    } else {
        error_log("No rows affected or execution failed");
        echo json_encode(['success' => false, 'message' => 'Nenhuma alteração foi feita ou erro na execução']);
    }

} catch (Exception $e) {
    error_log("Exception in profile update: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
}
?>
