<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/users_database.php';

try {
    $usersDb = new UsersDatabase();
    
    // Verificar se foi solicitado um role específico
    $role = isset($_GET['role']) ? $_GET['role'] : null;
    
    if ($role) {
        $users = $usersDb->getUsersByRole($role);
    } else {
        $users = $usersDb->getAllUsers();
    }
    
    echo json_encode([
        'success' => true,
        'data' => $users
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar usuários: ' . $e->getMessage()
    ]);
}
?>
