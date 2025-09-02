<?php
session_start();

// Redirect to appropriate dashboard if already logged in
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['user_role']) {
        case 'hr_manager':
            header('Location: dashboard/hr_manager.php');
            break;
        case 'admin':
            header('Location: dashboard/admin.php');
            break;
        case 'common_user':
            header('Location: dashboard/user.php');
            break;
        default:
            header('Location: auth/login.php');
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Agendamento de Reuniões</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white/80 backdrop-blur-md shadow-lg border-b border-white/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-xl font-bold text-gray-900">AgendaRH</h1>
                        <p class="text-sm text-gray-600">Sistema de Agendamento de Reuniões</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="auth/login.php" class="text-gray-600 hover:text-indigo-600 font-medium transition duration-200">
                        <i class="fas fa-sign-in-alt mr-2"></i>Entrar
                    </a>
                    <a href="auth/register.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition duration-200 transform hover:scale-105">
                        <i class="fas fa-user-plus mr-2"></i>Registrar
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold text-gray-900 mb-6">
                    Simplifique seus
                    <span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        Agendamentos
                    </span>
                </h1>
                <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                    Sistema completo para agendamento de reuniões com o RH. 
                    Interface intuitiva, calendário integrado e controle total de disponibilidade.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="auth/login.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-4 rounded-xl font-semibold text-lg transition duration-200 transform hover:scale-105 shadow-lg">
                        <i class="fas fa-rocket mr-2"></i>Começar Agora
                    </a>
                    <a href="#features" class="border-2 border-indigo-600 text-indigo-600 hover:bg-indigo-600 hover:text-white px-8 py-4 rounded-xl font-semibold text-lg transition duration-200">
                        <i class="fas fa-info-circle mr-2"></i>Saiba Mais
                    </a>
                </div>
            </div>
        </div>

        <!-- Floating Elements -->
        <div class="absolute top-20 left-10 w-20 h-20 bg-indigo-200 rounded-full opacity-20 animate-pulse"></div>
        <div class="absolute top-40 right-20 w-16 h-16 bg-purple-200 rounded-full opacity-20 animate-pulse delay-1000"></div>
        <div class="absolute bottom-20 left-1/4 w-12 h-12 bg-blue-200 rounded-full opacity-20 animate-pulse delay-2000"></div>
    </div>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white/50 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Recursos Principais
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Tudo que você precisa para gerenciar reuniões de forma eficiente
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white rounded-2xl p-8 shadow-xl hover:shadow-2xl transition duration-300 transform hover:-translate-y-2">
                    <div class="h-16 w-16 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-calendar-check text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Agendamento Inteligente</h3>
                    <p class="text-gray-600">
                        Calendário interativo com visualização de horários disponíveis em tempo real. 
                        Evite conflitos e otimize sua agenda.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white rounded-2xl p-8 shadow-xl hover:shadow-2xl transition duration-300 transform hover:-translate-y-2">
                    <div class="h-16 w-16 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-users-cog text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Controle de Acesso</h3>
                    <p class="text-gray-600">
                        Sistema com 3 níveis de permissão: Gestor RH, Admin e Usuário Comum. 
                        Cada um com acesso específico às informações.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white rounded-2xl p-8 shadow-xl hover:shadow-2xl transition duration-300 transform hover:-translate-y-2">
                    <div class="h-16 w-16 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-chart-line text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Relatórios e Histórico</h3>
                    <p class="text-gray-600">
                        Acompanhe o histórico completo de reuniões, estatísticas de uso e 
                        relatórios detalhados para melhor gestão.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Como Funciona
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Processo simples e intuitivo em poucos passos
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="text-center">
                    <div class="h-20 w-20 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-white text-2xl font-bold">1</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Faça Login</h3>
                    <p class="text-gray-600">
                        Acesse sua conta ou registre-se no sistema. 
                        Cada tipo de usuário tem acesso personalizado.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="text-center">
                    <div class="h-20 w-20 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-white text-2xl font-bold">2</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Escolha o Horário</h3>
                    <p class="text-gray-600">
                        Visualize o calendário com horários disponíveis e 
                        selecione o melhor momento para sua reunião.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="text-center">
                    <div class="h-20 w-20 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-white text-2xl font-bold">3</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Confirme a Reunião</h3>
                    <p class="text-gray-600">
                        Adicione uma descrição e confirme o agendamento. 
                        Receba confirmação instantânea.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- User Types Section -->
    <section class="py-20 bg-white/50 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Tipos de Usuário
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Sistema com diferentes níveis de acesso para atender todas as necessidades
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- HR Manager -->
                <div class="bg-white rounded-2xl p-8 shadow-xl border-t-4 border-green-500">
                    <div class="text-center mb-6">
                        <div class="h-16 w-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-user-tie text-green-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Gestor de RH</h3>
                    </div>
                    <ul class="space-y-3 text-gray-600">
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Ver todas as reuniões agendadas
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Configurar horários disponíveis
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Acesso completo às descrições
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Histórico completo de reuniões
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Gerenciar status das reuniões
                        </li>
                    </ul>
                </div>

                <!-- Admin -->
                <div class="bg-white rounded-2xl p-8 shadow-xl border-t-4 border-blue-500">
                    <div class="text-center mb-6">
                        <div class="h-16 w-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-shield-alt text-blue-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Admin</h3>
                    </div>
                    <ul class="space-y-3 text-gray-600">
                        <li class="flex items-center">
                            <i class="fas fa-check text-blue-500 mr-3"></i>
                            Ver quem marcou reuniões
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-blue-500 mr-3"></i>
                            Agendar próprias reuniões
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-blue-500 mr-3"></i>
                            Visão geral do sistema
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-blue-500 mr-3"></i>
                            Gerenciar usuários
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-times text-red-500 mr-3"></i>
                            Sem acesso às descrições
                        </li>
                    </ul>
                </div>

                <!-- Common User -->
                <div class="bg-white rounded-2xl p-8 shadow-xl border-t-4 border-purple-500">
                    <div class="text-center mb-6">
                        <div class="h-16 w-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-user text-purple-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Usuário Comum</h3>
                    </div>
                    <ul class="space-y-3 text-gray-600">
                        <li class="flex items-center">
                            <i class="fas fa-check text-purple-500 mr-3"></i>
                            Ver calendário disponível
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-purple-500 mr-3"></i>
                            Agendar reuniões
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-purple-500 mr-3"></i>
                            Ver próprias reuniões
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-purple-500 mr-3"></i>
                            Adicionar descrições
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-times text-red-500 mr-3"></i>
                            Não vê outras reuniões
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-indigo-600 to-purple-600">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
                Pronto para Começar?
            </h2>
            <p class="text-xl text-indigo-100 mb-8">
                Simplifique o agendamento de reuniões na sua empresa hoje mesmo
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="auth/register.php" class="bg-white text-indigo-600 hover:bg-gray-100 px-8 py-4 rounded-xl font-semibold text-lg transition duration-200 transform hover:scale-105">
                    <i class="fas fa-user-plus mr-2"></i>Criar Conta Gratuita
                </a>
                <a href="auth/login.php" class="border-2 border-white text-white hover:bg-white hover:text-indigo-600 px-8 py-4 rounded-xl font-semibold text-lg transition duration-200">
                    <i class="fas fa-sign-in-alt mr-2"></i>Fazer Login
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <div class="h-8 w-8 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-calendar-alt text-white"></i>
                        </div>
                        <h3 class="text-xl font-bold">AgendaRH</h3>
                    </div>
                    <p class="text-gray-400">
                        Sistema completo para agendamento de reuniões com interface moderna e intuitiva.
                    </p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Contas de Teste</h4>
                    <div class="space-y-2 text-sm text-gray-400">
                        <p><strong>RH:</strong> rh@empresa.com</p>
                        <p><strong>Admin:</strong> admin@empresa.com</p>
                        <p><strong>Usuário:</strong> usuario@empresa.com</p>
                        <p><strong>Senha:</strong> password (para todas)</p>
                    </div>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Tecnologias</h4>
                    <div class="flex flex-wrap gap-2">
                        <span class="bg-gray-800 px-3 py-1 rounded-full text-sm">PHP</span>
                        <span class="bg-gray-800 px-3 py-1 rounded-full text-sm">MySQL</span>
                        <span class="bg-gray-800 px-3 py-1 rounded-full text-sm">Tailwind CSS</span>
                        <span class="bg-gray-800 px-3 py-1 rounded-full text-sm">JavaScript</span>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2024 AgendaRH. Sistema de Agendamento de Reuniões.</p>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>
