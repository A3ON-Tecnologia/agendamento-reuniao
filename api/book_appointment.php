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

$hr_manager_id = $_POST['hr_manager_id'] ?? null;
$appointment_date = $_POST['appointment_date'] ?? null;
$start_time = $_POST['start_time'] ?? null;
$end_time = $_POST['end_time'] ?? null;
$description = $_POST['description'] ?? '';

if (!$hr_manager_id || !$appointment_date || !$start_time || !$end_time) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$database = new Database();
$db = $database->getConnection();
$appointment = new Appointment($db);

try {
    // Check if slot is still available
    $booked_slots = $appointment->getBookedSlots($hr_manager_id, $appointment_date);
    foreach ($booked_slots as $booked) {
        if ($booked['start_time'] == $start_time) {
            echo json_encode(['success' => false, 'message' => 'Este horário já foi reservado por outro usuário']);
            exit();
        }
    }

    // Create the appointment
    $appointment->hr_manager_id = $hr_manager_id;
    $appointment->user_id = $user_info['id'];
    $appointment->appointment_date = $appointment_date;
    $appointment->start_time = $start_time;
    $appointment->end_time = $end_time;
    $appointment->description = $description;
    $appointment->status = 'scheduled';

    if ($appointment->create()) {
        echo json_encode(['success' => true, 'message' => 'Appointment booked successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to book appointment']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
