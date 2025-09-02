<?php
session_start();
require_once '../config/database.php';
require_once '../classes/User.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);

    $email = $_POST['email'];
    $password = $_POST['password'];

    if ($user->login($email, $password)) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_name'] = $user->name;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_role'] = $user->role;

        // Redirect based on role
        switch ($user->role) {
            case 'hr_manager':
                header('Location: ../dashboard/hr_manager.php');
                break;
            case 'admin':
                header('Location: ../dashboard/admin.php');
                break;
            case 'common_user':
                header('Location: ../dashboard/user.php');
                break;
            default:
                header('Location: ../dashboard/user.php');
        }
        exit();
    } else {
        $error = "Email ou senha inválidos!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Agendamentos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full space-y-8 p-8">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center">
                <div class="mx-auto h-12 w-12 bg-indigo-600 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-calendar-alt text-white text-xl"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Bem-vindo</h2>
                <p class="text-gray-600">Sistema de Agendamento de Reuniões</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form class="mt-8 space-y-6" method="POST">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2"></i>Email
                    </label>
                    <input id="email" name="email" type="email" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200"
                           placeholder="seu@email.com">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2"></i>Senha
                    </label>
                    <input id="password" name="password" type="password" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200"
                           placeholder="••••••••">
                </div>

                <button type="submit" 
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <i class="fas fa-sign-in-alt mr-2"></i>Entrar
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Não tem uma conta? 
                    <a href="register.php" class="font-medium text-indigo-600 hover:text-indigo-500 transition duration-200">
                        Registre-se aqui
                    </a>
                </p>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-200">
                <div class="text-xs text-gray-500 space-y-1">
                    <p><strong>Contas de teste:</strong></p>
                    <p>RH: rh@empresa.com | Admin: admin@empresa.com | Usuário: usuario@empresa.com</p>
                    <p>Senha para todas: password</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
