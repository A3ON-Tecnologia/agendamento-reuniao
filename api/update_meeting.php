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
    $required_fields = ['meeting_id', 'date', 'start_time', 'end_time', 'subject'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            throw new Exception("Campo obrigatório: $field");
        }
    }
    
    // Conectar ao banco
    $database = new Database();
    $db = $database->getConnection();
    $meeting = new Meeting($db);
    
    // Atualizar reunião
    $success = $meeting->updateMeeting(
        $input['meeting_id'],
        $input['date'],
        $input['start_time'],
        $input['end_time'],
        $input['subject'],
        $input['description'] ?? '',
        $input['status'] ?? 'agendada',
        $input['participants'] ?? []
    );
    
    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Reunião atualizada com sucesso!'
        ]);
    } else {
        throw new Exception('Falha ao atualizar reunião');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao atualizar reunião: ' . $e->getMessage()
    ]);
}
?>
