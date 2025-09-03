<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/users_database.php';
require_once '../classes/Meeting.php';

// Verificar se é uma requisição GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Verificar se meeting_id foi fornecido
if (!isset($_GET['meeting_id']) || empty($_GET['meeting_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID da reunião é obrigatório']);
    exit;
}

try {
    // Conectar ao banco
    $usersDb = new UsersDatabase();
    $db = $usersDb->getConnection();
    $meeting = new Meeting($db);
    
    // Buscar participantes da reunião
    $participants = $meeting->getMeetingParticipants($_GET['meeting_id']);
    
    echo json_encode([
        'success' => true,
        'participants' => $participants
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar participantes: ' . $e->getMessage()
    ]);
}
?>
