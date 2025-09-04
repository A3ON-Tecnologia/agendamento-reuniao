<?php
// Teste direto da API get_meetings.php
require_once 'config/database.php';
require_once 'classes/Meeting.php';

echo "<h2>Teste da API get_meetings.php</h2>";

try {
    // Conectar ao banco
    $database = new Database();
    $db = $database->getConnection();
    echo "<p>✅ Conexão com banco OK</p>";
    
    $meeting = new Meeting($db);
    echo "<p>✅ Classe Meeting carregada</p>";
    
    // Buscar todas as reuniões
    $meetings = $meeting->getAllMeetings();
    echo "<p>✅ Método getAllMeetings() executado</p>";
    
    echo "<h3>Reuniões encontradas: " . count($meetings) . "</h3>";
    
    if (count($meetings) > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Data</th><th>Hora Início</th><th>Hora Fim</th><th>Assunto</th><th>Status</th></tr>";
        foreach ($meetings as $meeting) {
            echo "<tr>";
            echo "<td>" . $meeting['id'] . "</td>";
            echo "<td>" . $meeting['data_reuniao'] . "</td>";
            echo "<td>" . $meeting['hora_inicio'] . "</td>";
            echo "<td>" . $meeting['hora_fim'] . "</td>";
            echo "<td>" . $meeting['assunto'] . "</td>";
            echo "<td>" . $meeting['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ Nenhuma reunião encontrada</p>";
    }
    
    // Teste JSON
    echo "<h3>JSON Response:</h3>";
    echo "<pre>" . json_encode([
        'success' => true,
        'meetings' => $meetings
    ], JSON_PRETTY_PRINT) . "</pre>";
    
} catch (Exception $e) {
    echo "<p>❌ ERRO: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace: " . $e->getTraceAsString() . "</p>";
}
?>
