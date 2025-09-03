<?php
header('Content-Type: application/json');
// Auth removed for direct access
require_once '../config/database.php';
require_once '../classes/AvailabilitySlot.php';

// Mock user info for direct access
$user_info = ['id' => 1, 'role' => 'hr_manager'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$database = new Database();
$db = $database->getConnection();
$availability = new AvailabilitySlot($db);

try {
    $specific_date = $_POST['specific_date'] ?? null;
    $time_slots_json = $_POST['time_slots'] ?? null;
    
    if (!$specific_date) {
        throw new Exception('Data específica é obrigatória');
    }

    // Parse time slots from JSON or form data
    $time_slots = [];
    if ($time_slots_json) {
        $time_slots = json_decode($time_slots_json, true);
    } else {
        // Handle form data directly
        $start_times = $_POST['start_time'] ?? [];
        $end_times = $_POST['end_time'] ?? [];
        $durations = $_POST['duration'] ?? [];
        
        for ($i = 0; $i < count($start_times); $i++) {
            if (!empty($start_times[$i]) && !empty($end_times[$i])) {
                $time_slots[] = [
                    'start_time' => $start_times[$i],
                    'end_time' => $end_times[$i],
                    'duration' => $durations[$i] ?? 60
                ];
            }
        }
    }

    if (empty($time_slots)) {
        throw new Exception('Pelo menos um horário deve ser configurado');
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

        if (!$availability->create()) {
            throw new Exception('Falha ao salvar horário específico');
        }
    }

    echo json_encode(['success' => true, 'message' => 'Horários salvos com sucesso']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
