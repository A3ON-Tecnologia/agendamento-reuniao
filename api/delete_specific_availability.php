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
    $input = json_decode(file_get_contents('php://input'), true);
    $specific_date = $input['specific_date'] ?? null;

    if (!$specific_date) {
        throw new Exception('Data específica é obrigatória');
    }

    if ($availability->deleteSpecificAvailability($user_info['id'], $specific_date)) {
        echo json_encode(['success' => true, 'message' => 'Horários removidos com sucesso']);
    } else {
        throw new Exception('Falha ao remover horários');
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
