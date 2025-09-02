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
    // Verify that the appointment belongs to the current user and get details
    $query = "SELECT * FROM appointments WHERE id = :id AND user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $appointment_id);
    $stmt->bindParam(':user_id', $user_info['id']);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'Reunião não encontrada ou sem permissão']);
        exit();
    }
    
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if the appointment is scheduled and in the future
    if ($appointment['status'] !== 'scheduled') {
        echo json_encode(['success' => false, 'message' => 'Apenas reuniões agendadas podem ser excluídas']);
        exit();
    }
    
    if (strtotime($appointment['appointment_date']) < strtotime(date('Y-m-d'))) {
        echo json_encode(['success' => false, 'message' => 'Não é possível excluir reuniões passadas']);
        exit();
    }
    
    // Delete the appointment
    $query = "DELETE FROM appointments WHERE id = :id AND user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $appointment_id);
    $stmt->bindParam(':user_id', $user_info['id']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Reunião excluída com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir reunião']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
}
?>
