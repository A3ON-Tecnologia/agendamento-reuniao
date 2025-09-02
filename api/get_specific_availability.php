<?php
header('Content-Type: application/json');
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../classes/AvailabilitySlot.php';

checkRole('hr_manager');
$user_info = getUserInfo();

$database = new Database();
$db = $database->getConnection();
$availability = new AvailabilitySlot($db);

try {
    $specific_availability = $availability->getSpecificAvailabilityByHRManager($user_info['id']);
    
    echo json_encode([
        'success' => true, 
        'availability' => $specific_availability
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
