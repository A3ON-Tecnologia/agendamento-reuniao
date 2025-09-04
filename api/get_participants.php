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
    
    // Get all unique participants from meetings
    $stmt = $db->prepare("
        SELECT DISTINCT rp.username_usuario as nome
        FROM reuniao_participantes rp
        WHERE rp.username_usuario IS NOT NULL 
        AND rp.username_usuario != ''
        ORDER BY rp.username_usuario ASC
    ");
    
    $stmt->execute();
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'participants' => $participants
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar participantes: ' . $e->getMessage()
    ]);
}
?>
