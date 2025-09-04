<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

try {
    // Conectar ao banco usando a classe Database
    $database = new Database();
    $db = $database->getConnection();
    
    // Get all meetings with participants using correct table names
    $stmt = $db->prepare("
        SELECT 
            r.id,
            r.data_reuniao,
            r.hora_inicio,
            r.hora_fim,
            r.assunto,
            r.descricao,
            r.status,
            GROUP_CONCAT(DISTINCT rp.username_usuario SEPARATOR ', ') as participantes
        FROM reunioes r
        LEFT JOIN reuniao_participantes rp ON r.id = rp.reuniao_id
        WHERE r.status != 'cancelada'
        GROUP BY r.id, r.data_reuniao, r.hora_inicio, r.hora_fim, r.assunto, r.descricao, r.status
        ORDER BY r.data_reuniao DESC, r.hora_inicio DESC
    ");
    
    $stmt->execute();
    $meetings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'meetings' => $meetings,
        'total' => count($meetings)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar reuniões: ' . $e->getMessage()
    ]);
}
?>
