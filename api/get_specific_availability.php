<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

try {
    require_once '../config/database.php';
    require_once '../classes/AvailabilitySlot.php';

    // Mock user info for direct access
    $user_info = ['id' => 1];

    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Falha na conexão com o banco de dados");
    }
    
    $availability = new AvailabilitySlot($db);

    $specific_availability = $availability->getSpecificAvailabilityByHRManager($user_info['id']);
    
    // Debug: Log the query result
    error_log("HR Manager ID: " . $user_info['id']);
    error_log("Specific availability count: " . count($specific_availability));
    error_log("Specific availability data: " . json_encode($specific_availability));
    
    echo json_encode([
        'success' => true, 
        'availability' => $specific_availability,
        'debug' => [
            'hr_manager_id' => $user_info['id'],
            'count' => count($specific_availability),
            'database_connected' => true
        ]
    ]);

} catch (Exception $e) {
    error_log("Error in get_specific_availability: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'error_details' => [
            'file' => __FILE__,
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}
?>
