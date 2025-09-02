<?php
header('Content-Type: application/json');
require_once '../includes/auth_check.php';
require_once '../config/database.php';

checkAuth();
$user_info = getUserInfo();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$appointment_id = $input['appointment_id'] ?? null;

if (!$appointment_id) {
    echo json_encode(['success' => false, 'message' => 'Missing appointment ID']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

try {
    // Get appointment details - only for the current user
    $query = "SELECT a.*, u.name as hr_manager_name 
              FROM appointments a 
              JOIN users u ON a.hr_manager_id = u.id 
              WHERE a.id = :id AND a.user_id = :user_id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $appointment_id);
    $stmt->bindParam(':user_id', $user_info['id']);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'Reunião não encontrada ou sem permissão']);
        exit();
    }
    
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true, 
        'appointment' => $appointment
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
}
?>
