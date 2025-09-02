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
    // First, delete existing availability for this HR manager
    $delete_query = "DELETE FROM availability_slots WHERE hr_manager_id = :hr_manager_id";
    $delete_stmt = $db->prepare($delete_query);
    $delete_stmt->bindParam(':hr_manager_id', $user_info['id']);
    $delete_stmt->execute();

    // Insert new availability slots
    if (isset($_POST['active_days']) && is_array($_POST['active_days'])) {
        foreach ($_POST['active_days'] as $day) {
            $start_time = $_POST['start_time'][$day] ?? '09:00';
            $end_time = $_POST['end_time'][$day] ?? '17:00';
            $duration = $_POST['duration'][$day] ?? 60;

            $availability->hr_manager_id = $user_info['id'];
            $availability->day_of_week = $day;
            $availability->start_time = $start_time;
            $availability->end_time = $end_time;
            $availability->slot_duration = $duration;
            $availability->is_active = 1;

            if (!$availability->create()) {
                throw new Exception('Failed to save availability slot');
            }
        }
    }

    echo json_encode(['success' => true, 'message' => 'Availability saved successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
