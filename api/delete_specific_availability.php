<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
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
    
    if (!$db) {
        throw new Exception('Falha na conexão com o banco de dados');
    }
    
    $availability = new AvailabilitySlot($db);

    $input = json_decode(file_get_contents('php://input'), true);
    $specific_date = $input['specific_date'] ?? null;

    error_log("Delete request - HR Manager ID: " . $user_info['id'] . ", Date: " . $specific_date);

    if (!$specific_date) {
        throw new Exception('Data específica é obrigatória');
    }

    $result = $availability->deleteSpecificAvailability($user_info['id'], $specific_date);
    error_log("Delete result: " . ($result ? 'true' : 'false'));

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Horários removidos com sucesso']);
    } else {
        throw new Exception('Falha ao remover horários - nenhum registro foi afetado');
    }

} catch (Exception $e) {
    error_log("Delete error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'debug' => [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]]);
} catch (Error $e) {
    error_log("Delete fatal error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    echo json_encode(['success' => false, 'message' => 'Erro fatal: ' . $e->getMessage(), 'debug' => [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]]);
}
?>
