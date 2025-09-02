<?php
header('Content-Type: application/json');
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../classes/AvailabilitySlot.php';

checkRole('hr_manager');
$user_info = getUserInfo();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$database = new Database();
$db = $database->getConnection();
$availability = new AvailabilitySlot($db);

try {
    $specific_date = $_POST['specific_date'] ?? null;
    $time_slots = $_POST['time_slots'] ?? [];

    if (!$specific_date || empty($time_slots)) {
        throw new Exception('Data específica e horários são obrigatórios');
    }

    // Delete existing availability for this date
    $availability->deleteSpecificAvailability($user_info['id'], $specific_date);

    // Insert new time slots for this specific date
    foreach ($time_slots as $slot) {
        $availability->hr_manager_id = $user_info['id'];
        $availability->specific_date = $specific_date;
        $availability->start_time = $slot['start_time'];
        $availability->end_time = $slot['end_time'];
        $availability->slot_duration = $slot['duration'];
        $availability->is_active = 1;

        if (!$availability->createSpecificAvailability()) {
            throw new Exception('Falha ao salvar horário específico');
        }
    }

    echo json_encode(['success' => true, 'message' => 'Horários salvos com sucesso']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
