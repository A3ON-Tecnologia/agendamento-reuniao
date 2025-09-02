<?php
session_start();

echo "<h2>Debug da Sessão do Gestor RH</h2>";

echo "<h3>Dados da Sessão Atual:</h3>";
echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>Verificações:</h3>";

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    echo "<p style='color: green;'>✅ Usuário logado - ID: " . $_SESSION['user_id'] . "</p>";
} else {
    echo "<p style='color: red;'>❌ Usuário NÃO logado</p>";
}

// Check role
if (isset($_SESSION['user_role'])) {
    echo "<p style='color: green;'>✅ Role definida: " . $_SESSION['user_role'] . "</p>";
    
    if ($_SESSION['user_role'] === 'hr_manager') {
        echo "<p style='color: green;'>✅ É gestor de RH</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ NÃO é gestor de RH (role: " . $_SESSION['user_role'] . ")</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Role NÃO definida</p>";
}

// Test getUserInfo function
echo "<h3>Teste da função getUserInfo():</h3>";
try {
    require_once 'includes/auth_check.php';
    $user_info = getUserInfo();
    echo "<pre style='background: #e8f5e8; padding: 10px; border-radius: 5px;'>";
    print_r($user_info);
    echo "</pre>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro ao obter info do usuário: " . $e->getMessage() . "</p>";
}

// Test database query with current user
echo "<h3>Teste de consulta com usuário atual:</h3>";
if (isset($_SESSION['user_id'])) {
    try {
        require_once 'config/database.php';
        
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT * FROM specific_availability_slots WHERE hr_manager_id = ? AND is_active = 1";
        $stmt = $db->prepare($query);
        $stmt->execute([$_SESSION['user_id']]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>Horários encontrados para user_id " . $_SESSION['user_id'] . ": <strong>" . count($results) . "</strong></p>";
        
        if (count($results) > 0) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Data</th><th>Início</th><th>Fim</th><th>Duração</th></tr>";
            foreach ($results as $row) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['specific_date'] . "</td>";
                echo "<td>" . $row['start_time'] . "</td>";
                echo "<td>" . $row['end_time'] . "</td>";
                echo "<td>" . $row['slot_duration'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erro na consulta: " . $e->getMessage() . "</p>";
    }
}

// Show all users for reference
echo "<h3>Todos os usuários cadastrados:</h3>";
try {
    require_once 'config/database.php';
    
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT id, name, email, role FROM users";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Role</th></tr>";
    foreach ($users as $user) {
        $highlight = (isset($_SESSION['user_id']) && $user['id'] == $_SESSION['user_id']) ? 'background: yellow;' : '';
        echo "<tr style='$highlight'>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . $user['name'] . "</td>";
        echo "<td>" . $user['email'] . "</td>";
        echo "<td>" . $user['role'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro ao listar usuários: " . $e->getMessage() . "</p>";
}
?>
