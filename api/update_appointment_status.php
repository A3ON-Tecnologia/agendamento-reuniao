<?php
header('Content-Type: application/json');
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../classes/Appointment.php';

checkRoles(['hr_manager', 'admin']);
$user_info = getUserInfo();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$appointment_id = $input['appointment_id'] ?? null;
$status = $input['status'] ?? null;

if (!$appointment_id || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$database = new Database();
$db = $database->getConnection();
$appointment = new Appointment($db);

try {
    if ($appointment->updateStatus($appointment_id, $status)) {
        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
