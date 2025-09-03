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

// This API is deprecated since we only use specific date availability now
// Redirect to save_specific_availability.php or return appropriate message
echo json_encode([
    'success' => false, 
    'message' => 'Esta funcionalidade foi removida. Use apenas datas específicas.',
    'redirect' => 'save_specific_availability.php'
]);
?>
