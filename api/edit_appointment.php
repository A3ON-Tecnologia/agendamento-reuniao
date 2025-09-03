<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../classes/Appointment.php';

// Mock user info for direct access
$user_info = ['id' => 1, 'role' => 'hr_manager'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$appointment_id = $_POST['appointment_id'] ?? null;
$hr_manager_id = $_POST['hr_manager_id'] ?? null;
$appointment_date = $_POST['appointment_date'] ?? null;
$start_time = $_POST['start_time'] ?? null;
$end_time = $_POST['end_time'] ?? null;
$description = $_POST['description'] ?? '';

if (!$appointment_id || !$hr_manager_id || !$appointment_date || !$start_time || !$end_time) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$database = new Database();
$db = $database->getConnection();
$appointment = new Appointment($db);

try {
    // Verify that the appointment belongs to the current user
    $query = "SELECT * FROM appointments WHERE id = :id AND user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $appointment_id);
    $stmt->bindParam(':user_id', $user_info['id']);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'Reunião não encontrada ou sem permissão']);
        exit();
    }
    
    $current_appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if the appointment is scheduled and in the future
    if ($current_appointment['status'] !== 'scheduled') {
        echo json_encode(['success' => false, 'message' => 'Apenas reuniões agendadas podem ser editadas']);
        exit();
    }
    
    if (strtotime($current_appointment['appointment_date']) < strtotime(date('Y-m-d'))) {
        echo json_encode(['success' => false, 'message' => 'Não é possível editar reuniões passadas']);
        exit();
    }
    
    // Check if the new time slot is available (excluding current appointment)
    $query = "SELECT id FROM appointments 
              WHERE hr_manager_id = :hr_manager_id 
              AND appointment_date = :date 
              AND start_time = :start_time 
              AND status = 'scheduled'
              AND id != :appointment_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':hr_manager_id', $hr_manager_id);
    $stmt->bindParam(':date', $appointment_date);
    $stmt->bindParam(':start_time', $start_time);
    $stmt->bindParam(':appointment_id', $appointment_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Este horário já está ocupado']);
        exit();
    }
    
    // Update the appointment
    $query = "UPDATE appointments 
              SET hr_manager_id = :hr_manager_id, 
                  appointment_date = :appointment_date, 
                  start_time = :start_time, 
                  end_time = :end_time, 
                  description = :description 
              WHERE id = :id AND user_id = :user_id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':hr_manager_id', $hr_manager_id);
    $stmt->bindParam(':appointment_date', $appointment_date);
    $stmt->bindParam(':start_time', $start_time);
    $stmt->bindParam(':end_time', $end_time);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':id', $appointment_id);
    $stmt->bindParam(':user_id', $user_info['id']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Reunião atualizada com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar reunião']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
}
?>
