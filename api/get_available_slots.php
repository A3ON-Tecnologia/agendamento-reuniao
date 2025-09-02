<?php
header('Content-Type: application/json');
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../classes/AvailabilitySlot.php';
require_once '../classes/Appointment.php';

checkAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$hr_manager_id = $input['hr_manager_id'] ?? null;
$date = $input['date'] ?? null;

if (!$hr_manager_id || !$date) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$database = new Database();
$db = $database->getConnection();
$availability = new AvailabilitySlot($db);
$appointment = new Appointment($db);

try {
    // Generate available time slots for the date
    $all_slots = $availability->generateTimeSlots($hr_manager_id, $date);
    
    // Get booked slots for this date
    $booked_slots = $appointment->getBookedSlots($hr_manager_id, $date);
    
    // Filter out booked slots
    $available_slots = [];
    foreach ($all_slots as $slot) {
        $is_booked = false;
        foreach ($booked_slots as $booked) {
            if ($slot['start_time'] == $booked['start_time']) {
                $is_booked = true;
                break;
            }
        }
        
        if (!$is_booked) {
            $available_slots[] = $slot;
        }
    }
    
    echo json_encode([
        'success' => true, 
        'slots' => $available_slots
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
