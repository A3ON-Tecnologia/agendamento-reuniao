<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/users_database.php';
require_once '../classes/Meeting.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar dados obrigatórios
    $required_fields = ['creator_id', 'date', 'start_time', 'end_time', 'subject'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            throw new Exception("Campo obrigatório: $field");
        }
    }
    
    // Conectar ao banco
    $usersDb = new UsersDatabase();
    $db = $usersDb->getConnection();
    $meeting = new Meeting($db);
    
    // Criar reunião
    $meeting_id = $meeting->createMeeting(
        $input['creator_id'],
        $input['date'],
        $input['start_time'],
        $input['end_time'],
        $input['subject'],
        $input['description'] ?? '',
        $input['participants'] ?? []
    );
    
    echo json_encode([
        'success' => true,
        'message' => 'Reunião criada com sucesso',
        'meeting_id' => $meeting_id
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao criar reunião: ' . $e->getMessage()
    ]);
}
?>
