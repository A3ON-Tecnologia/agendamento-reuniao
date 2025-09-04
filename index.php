<?php
session_start();
// Redirect to dashboard immediately
header('Location: dashboard/hr_manager.php');
exit();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Agendamento de Reuniões</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'Noto Sans', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"></noscript>
</head>
<body class="bg-gradient-to-br from-orange-50 via-orange-100 to-blue-900 min-h-screen">

    <!-- Hero Section -->
    <div class="relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold text-gray-900 mb-6">
                    Simplifique seus
                    <span class="bg-gradient-to-r from-orange-500 to-blue-900 bg-clip-text text-transparent">
                        Agendamentos
                    </span>
                </h1>
                <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                    Sistema completo para agendamento de reuniões da JP Contábil.<br>
                    Interface intuitiva, calendário integrado e controle total de disponibilidade.
                </p>
                <div class="flex justify-center">
                    <a href="dashboard/hr_manager.php" class="bg-orange-500 hover:bg-blue-900 text-white px-8 py-4 rounded-xl font-semibold text-lg transition-all duration-300 transform hover:scale-110 shadow-lg">
                        <i class="fas fa-rocket mr-2"></i>Acessar Sistema
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white/90">
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
                <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
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
                <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <div class="h-16 w-16 bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-users-cog text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Controle de Acesso</h3>
                    <p class="text-gray-600">
                        Sistema integrado com o portal da JP Contábil. 
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <div class="h-16 w-16 bg-gradient-to-r from-blue-800 to-blue-900 rounded-xl flex items-center justify-center mb-6">
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

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-orange-500 to-blue-900">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
                Pronto para Começar?
            </h2>
            <p class="text-xl text-orange-100 mb-8">
                Simplifique o agendamento de reuniões na sua empresa hoje mesmo.
            </p>
            <div class="flex justify-center">
                <a href="dashboard/hr_manager.php" class="bg-orange-500 hover:bg-blue-900 text-white px-8 py-4 rounded-xl font-semibold text-lg transition-all duration-300 transform hover:scale-110 shadow-lg">
                    <i class="fas fa-calendar-alt mr-2"></i>Acessar Sistema
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <div class="h-8 w-8 bg-gradient-to-r from-orange-500 to-blue-900 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-calendar-alt text-white"></i>
                        </div>
                        <h3 class="text-xl font-bold">AgendaJP</h3>
                    </div>
                    <p class="text-gray-400">
                        Sistema completo para agendamento de reuniões com interface moderna e intuitiva.
                    </p>
                </div>
        
                <div>
                    <h4 class="text-lg font-semibold mb-4">Tecnologias</h4>
                    <div class="flex flex-wrap gap-2">
                        <span class="bg-gray-800 px-3 py-1 rounded-full text-sm">PHP</span>
                        <span class="bg-gray-800 px-3 py-1 rounded-full text-sm">MySQL</span>
                        <span class="bg-gray-800 px-3 py-1 rounded-full text-sm">Tailwind CSS</span>
                        <span class="bg-gray-800 px-3 py-1 rounded-full text-sm">JavaScript</span>
                        <span class="bg-gray-800 px-3 py-1 rounded-full text-sm">HTML</span>
                        <span class="bg-gray-800 px-3 py-1 rounded-full text-sm">CSS</span>
                        <span class="bg-gray-800 px-3 py-1 rounded-full text-sm">PYTHON</span>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2025 AgendaJP. Sistema de Agendamento de Reuniões.</p>
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
