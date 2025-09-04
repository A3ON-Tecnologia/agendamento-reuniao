<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../classes/Meeting.php';

// Log de debug
error_log("=== UPDATE MEETING API CALLED ===");

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    error_log("Dados recebidos: " . json_encode($input));
    
    // Verificar se o JSON foi decodificado corretamente
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Dados JSON inválidos: ' . json_last_error_msg());
    }
    
    // Validar dados obrigatórios
    $required_fields = ['meeting_id', 'date', 'start_time', 'end_time', 'subject'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || trim($input[$field]) === '') {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        throw new Exception("Campos obrigatórios ausentes: " . implode(', ', $missing_fields));
    }
    
    // Conectar ao banco
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception('Falha na conexão com o banco de dados');
    }
    
    error_log("Conexão com banco estabelecida");
    
    $meeting = new Meeting($db);
    
    // Atualizar reunião
    error_log("Iniciando atualização da reunião ID: " . $input['meeting_id']);
    
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
        error_log("Reunião atualizada com sucesso!");
        echo json_encode([
            'success' => true,
            'message' => 'Reunião atualizada com sucesso!',
            'data' => [
                'meeting_id' => $input['meeting_id'],
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        throw new Exception('Falha ao atualizar reunião - método retornou false');
    }
    
} catch (Exception $e) {
    error_log("Erro na API update_meeting: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'file' => basename($e->getFile()),
            'line' => $e->getLine(),
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);
}
?>
