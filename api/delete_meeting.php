<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../classes/Meeting.php';

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar dados obrigatórios
    if (empty($input['meeting_id'])) {
        throw new Exception("ID da reunião é obrigatório");
    }
    
    // Conectar ao banco
    $database = new Database();
    $db = $database->getConnection();
    $meeting = new Meeting($db);
    
    // Excluir reunião
    $success = $meeting->deleteMeeting($input['meeting_id']);
    
    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Reunião excluída com sucesso!'
        ]);
    } else {
        throw new Exception('Falha ao excluir reunião');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao excluir reunião: ' . $e->getMessage()
    ]);
}
?>
