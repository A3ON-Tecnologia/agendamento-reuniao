<?php
session_start();
require_once '../config/database.php';
require_once '../classes/User.php';
require_once '../classes/Meeting.php';

// Simular usuário HR Manager para acesso direto
if (!isset($_SESSION['user_info'])) {
    $_SESSION['user_info'] = [
        'id' => 1,
        'name' => 'HR Manager',
        'role' => 'hr_manager'
    ];
}

$user_info = $_SESSION['user_info'];

$database = new Database();
$db = $database->getConnection();
$meeting = new Meeting($db);

$days_of_week = [
    0 => 'Domingo',
    1 => 'Segunda-feira',
    2 => 'Terça-feira',
    3 => 'Quarta-feira',
    4 => 'Quinta-feira',
    5 => 'Sexta-feira',
    6 => 'Sábado'
];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard RH - Sistema de Agendamentos</title>
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Custom tooltip styles */
        .custom-tooltip {
            position: fixed;
            background: white;
            border-radius: 8px;
            padding: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            z-index: 9999;
            font-size: 13px;
            line-height: 1.4;
            max-width: 250px;
            width: 250px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-5px);
            transition: all 0.2s ease-in-out;
            pointer-events: none;
            border-left: 4px solid;
            display: none;
        }
        
        .custom-tooltip.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
            display: block !important;
        }
        
        .custom-tooltip.status-happening {
            border-left-color: #10b981; /* green-500 */
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        }
        
        .custom-tooltip.status-past {
            border-left-color: #ef4444; /* red-500 */
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        }
        
        .custom-tooltip.status-future {
            border-left-color: #f97316; /* orange-500 */
            background: linear-gradient(135deg, #fff7ed 0%, #fed7aa 100%);
        }
        
        .custom-tooltip .tooltip-title {
            font-weight: 600;
            margin-bottom: 6px;
            color: #1f2937;
        }
        
        .custom-tooltip .tooltip-time {
            color: #6b7280;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
        }
        
        .custom-tooltip .tooltip-status {
            font-size: 12px;
            font-weight: 500;
            padding: 2px 8px;
            border-radius: 12px;
            display: inline-block;
            margin-top: 4px;
        }
        
        .custom-tooltip.status-happening .tooltip-status {
            background: #10b981;
            color: white;
        }
        
        .custom-tooltip.status-past .tooltip-status {
            background: #ef4444;
            color: white;
        }
        
        .custom-tooltip.status-future .tooltip-status {
            background: #f97316;
            color: white;
        }
        
        /* Pulsing animation for meetings in progress */
        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.2);
                opacity: 0.7;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .meeting-happening {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-full h-screen flex flex-col">
        <!-- Calendar View -->
        <div class="bg-white shadow rounded-lg flex-1 flex flex-col">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-6">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-calendar-alt mr-2"></i>Calendário de Agendamentos
                        </h3>
                        
                        <!-- Status Legend -->
                        <div class="flex items-center space-x-4 text-sm">
                            <div class="flex items-center space-x-1">
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                <span class="text-gray-600">Em andamento</span>
                            </div>
                            <div class="flex items-center space-x-1">
                                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                <span class="text-gray-600">Finalizada</span>
                            </div>
                            <div class="flex items-center space-x-1">
                                <div class="w-3 h-3 bg-orange-500 rounded-full"></div>
                                <span class="text-gray-600">Agendada</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="showSpecificDateModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200 text-sm">
                            <i class="fas fa-calendar-plus mr-2"></i>Agendar Reunião
                        </button>
                        <button onclick="showReportsModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200 text-sm">
                            <i class="fas fa-chart-bar mr-2"></i>Relatórios
                        </button>
                    </div>
                </div>
            </div>
            <div class="p-6 flex-1 overflow-y-auto">
                <!-- Calendar Container -->
                <div id="calendar" class="w-full">
                    <!-- Calendar Header -->
                    <div class="flex justify-between items-center mb-6">
                        <button id="prevMonth" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition duration-200">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <h2 id="currentMonth" class="text-2xl font-bold text-gray-900"></h2>
                        <button id="nextMonth" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition duration-200">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    
                    <!-- Calendar Grid -->
                    <div class="grid grid-cols-7 gap-1 mb-2">
                        <div class="text-center font-semibold text-gray-600 py-3">Dom</div>
                        <div class="text-center font-semibold text-gray-600 py-3">Seg</div>
                        <div class="text-center font-semibold text-gray-600 py-3">Ter</div>
                        <div class="text-center font-semibold text-gray-600 py-3">Qua</div>
                        <div class="text-center font-semibold text-gray-600 py-3">Qui</div>
                        <div class="text-center font-semibold text-gray-600 py-3">Sex</div>
                        <div class="text-center font-semibold text-gray-600 py-3">Sáb</div>
                    </div>
                    
                    
                    <!-- Calendar Days -->
                    <div id="calendarDays" class="grid grid-cols-7 gap-1 h-[400px]">
                        <!-- Days will be generated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Reports Modal -->
    <div id="reportsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative mx-auto p-5 border w-11/12 md:w-5/6 lg:w-4/5 shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Relatório de Reuniões</h3>
                    <button onclick="hideReportsModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- Filter Section -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <!-- Status Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status:</label>
                            <select id="statusFilter" onchange="applyFilters()" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="all">Todas</option>
                                <option value="future">Agendadas</option>
                                <option value="happening">Em Andamento</option>
                                <option value="past">Finalizadas</option>
                            </select>
                        </div>
                        
                        <!-- Specific Date Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Data Específica:</label>
                            <input type="date" id="specificDateFilter" onchange="applyFilters()" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <!-- Participant Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Participante:</label>
                            <select id="participantFilter" onchange="applyFilters()" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="all">Todos os participantes</option>
                                <!-- Options will be populated by JavaScript -->
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-600">Total:</span>
                            <span id="totalMeetings" class="font-semibold text-blue-600">0</span>
                        </div>
                        <button onclick="clearAllFilters()" class="text-sm text-gray-500 hover:text-gray-700 underline">
                            <i class="fas fa-times mr-1"></i>Limpar Filtros
                        </button>
                    </div>
                </div>
                
                <!-- Meetings Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horário</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assunto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participantes</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="meetingsTableBody" class="bg-white divide-y divide-gray-200">
                            <!-- Meetings will be populated here -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Loading State -->
                <div id="reportsLoading" class="hidden text-center py-8">
                    <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
                    <p class="text-gray-500 mt-2">Carregando reuniões...</p>
                </div>
                
                <!-- Empty State -->
                <div id="reportsEmpty" class="hidden text-center py-8">
                    <i class="fas fa-calendar-times text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">Nenhuma reunião encontrada</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Meeting Details/Edit Modal -->
    <div id="meetingDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-[60] flex items-center justify-center">
        <div class="relative mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Detalhes da Reunião</h3>
                    <button onclick="hideMeetingDetailsModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form id="editMeetingForm">
                    <input type="hidden" id="editMeetingId" name="meeting_id">
                    <div class="space-y-4">
                        <!-- Data -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Data da Reunião</label>
                            <input type="date" id="editMeetingDate" name="date" required 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <!-- Horários -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Hora de Início</label>
                                <input type="time" id="editMeetingStartTime" name="start_time" required 
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Hora de Fim</label>
                                <input type="time" id="editMeetingEndTime" name="end_time" required 
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <!-- Assunto -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Assunto da Reunião</label>
                            <input type="text" id="editMeetingSubject" name="subject" required 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                   placeholder="Digite o assunto da reunião">
                        </div>

                        <!-- Descrição -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Descrição (Opcional)</label>
                            <textarea id="editMeetingDescription" name="description" rows="3"
                                      class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                      placeholder="Descrição adicional da reunião"></textarea>
                        </div>

                        <!-- Participantes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Participantes</label>
                            <div class="relative">
                                <div id="editParticipantsInput" 
                                     class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white cursor-pointer min-h-[42px] flex flex-wrap items-center gap-1"
                                     onclick="toggleEditParticipantsDropdown()">
                                    <span id="editParticipantsPlaceholder" class="text-gray-500">Clique para selecionar participantes...</span>
                                    <div id="editSelectedParticipants" class="flex flex-wrap gap-1"></div>
                                    <i class="fas fa-chevron-down ml-auto text-gray-400" id="editDropdownIcon"></i>
                                </div>
                                
                                <div id="editParticipantsDropdown" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto hidden">
                                    <div class="p-2 border-b border-gray-200">
                                        <input type="text" 
                                               id="editParticipantsSearch" 
                                               placeholder="Buscar usuários..." 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                                               onclick="event.stopPropagation()"
                                               oninput="filterEditParticipants()">
                                    </div>
                                    <div id="editParticipantsList" class="py-1">
                                        <div class="px-3 py-2 text-gray-500 text-sm">Carregando usuários...</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select id="editMeetingStatus" name="status" disabled
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100 text-gray-600 cursor-not-allowed">
                                <option value="agendada">Agendada</option>
                                <option value="em_andamento">Em andamento</option>
                                <option value="concluida">Finalizada</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">O status é calculado automaticamente baseado no horário da reunião</p>
                        </div>
                    </div>

                    <div class="flex justify-between mt-6">
                        <button type="button" onclick="deleteMeeting()" 
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition duration-200">
                            <i class="fas fa-trash mr-2"></i>Excluir Reunião
                        </button>
                        <div class="space-x-2">
                            <button type="button" onclick="hideMeetingDetailsModal()" 
                                    class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg transition duration-200">
                                Cancelar
                            </button>
                            <button type="submit" 
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200">
                                <i class="fas fa-save mr-2"></i>Salvar Alterações
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Meeting Scheduling Modal -->
    <div id="specificDateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Agendar Reunião</h3>
                    <button onclick="hideSpecificDateModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form id="meetingForm">
                    <div class="space-y-4">
                        <!-- Usuários -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Usuários Participantes</label>
                            <div class="relative">
                                <!-- Input field that opens dropdown -->
                                <div id="participantsInput" 
                                     class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white cursor-pointer min-h-[42px] flex flex-wrap items-center gap-1"
                                     onclick="toggleParticipantsDropdown()">
                                    <span id="participantsPlaceholder" class="text-gray-500">Clique para selecionar participantes...</span>
                                    <div id="selectedParticipants" class="flex flex-wrap gap-1"></div>
                                    <i class="fas fa-chevron-down ml-auto text-gray-400" id="dropdownIcon"></i>
                                </div>
                                
                                <!-- Dropdown list -->
                                <div id="participantsDropdown" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto hidden">
                                    <!-- Search input -->
                                    <div class="p-2 border-b border-gray-200">
                                        <input type="text" 
                                               id="participantsSearch" 
                                               placeholder="Buscar usuários..." 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                                               onclick="event.stopPropagation()"
                                               oninput="filterParticipants()">
                                    </div>
                                    <!-- Users list -->
                                    <div id="participantsList" class="py-1">
                                        <div class="px-3 py-2 text-gray-500 text-sm">Carregando usuários...</div>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Clique no campo acima para selecionar múltiplos participantes</p>
                        </div>

                        <!-- Data -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Data</label>
                            <input type="date" id="meetingDate" name="meeting_date" required 
                                   min="<?php echo date('Y-m-d'); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <!-- Horários -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Hora de Início</label>
                                <input type="time" id="meetingStartTime" name="meeting_start_time" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Hora de Fim</label>
                                <input type="time" id="meetingEndTime" name="meeting_end_time" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>

                        <!-- Assunto -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Assunto</label>
                            <textarea id="meetingSubject" name="meeting_subject" required rows="3"
                                      placeholder="Digite o assunto da reunião..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 resize-none"></textarea>
                        </div>

                        <!-- Error message container -->
                        <div id="meetingErrorMessage" class="hidden mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <span id="meetingErrorText"></span>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" onclick="hideSpecificDateModal()" 
                                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition duration-200">
                                Cancelar
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition duration-200">
                                <i class="fas fa-calendar-plus mr-2"></i>Agendar Reunião
                            </button>
                        </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Availability Configuration Modal -->
    <div id="availabilityModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Configurar Horários Disponíveis</h3>
                    <button onclick="hideAvailabilityModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form id="availabilityForm" action="../api/save_availability.php" method="POST">
                    <div class="space-y-4">
                        <?php for ($day = 1; $day <= 5; $day++): ?>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <label class="text-sm font-medium text-gray-700"><?php echo $days_of_week[$day]; ?></label>
                                    <input type="checkbox" name="active_days[]" value="<?php echo $day; ?>" 
                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                           <?php 
                                           foreach($availability_slots as $slot) {
                                               if($slot['day_of_week'] == $day) echo 'checked';
                                           }
                                           ?>>
                                </div>
                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Início</label>
                                        <input type="time" name="start_time[<?php echo $day; ?>]" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                               value="<?php 
                                               foreach($availability_slots as $slot) {
                                                   if($slot['day_of_week'] == $day) echo date('H:i', strtotime($slot['start_time']));
                                               }
                                               ?>">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Fim</label>
                                        <input type="time" name="end_time[<?php echo $day; ?>]" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                               value="<?php 
                                               foreach($availability_slots as $slot) {
                                                   if($slot['day_of_week'] == $day) echo date('H:i', strtotime($slot['end_time']));
                                               }
                                               ?>">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Duração (min)</label>
                                        <select name="duration[<?php echo $day; ?>]" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                                            <option value="30">30 min</option>
                                            <option value="60" selected>60 min</option>
                                            <option value="90">90 min</option>
                                            <option value="120">120 min</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="hideAvailabilityModal()" 
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700">
                            Salvar Configurações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- User Profile Modal -->
    <div id="profileModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Perfil do Usuário</h3>
                    <button onclick="hideProfileModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form id="profileForm">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nome</label>
                            <input type="text" id="profileName" name="name" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                   value="<?php echo htmlspecialchars($user_info['name']); ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">E-mail</label>
                            <input type="email" id="profileEmail" name="email" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                   value="<?php echo htmlspecialchars($user_info['email']); ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Senha</label>
                            <div class="relative">
                                <input type="password" id="profilePassword" name="password" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="Digite a nova senha">
                                <button type="button" onclick="togglePasswordVisibility('profilePassword')" 
                                        class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
                                    <i id="profilePasswordIcon" class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="hideProfileModal()" 
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700">
                            Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2">
        <!-- Toast notifications will be inserted here -->
    </div>

    <!-- Custom Tooltip Container -->
    <div id="customTooltip" class="custom-tooltip">
        <div class="tooltip-title"></div>
        <div class="tooltip-time">
            <i class="fas fa-clock mr-1"></i>
            <span></span>
        </div>
        <div class="tooltip-status"></div>
    </div>

    <script>
        // Toast notification system
        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toastContainer');
            
            // Create toast element
            const toast = document.createElement('div');
            toast.className = `
                transform transition-all duration-300 ease-in-out translate-x-full opacity-0
                max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto flex ring-1 ring-black ring-opacity-5
            `;
            
            // Set colors based on type
            let iconClass, bgClass, textClass;
            switch(type) {
                case 'success':
                    iconClass = 'fas fa-check-circle text-green-400';
                    bgClass = 'bg-green-50';
                    textClass = 'text-green-800';
                    break;
                case 'error':
                    iconClass = 'fas fa-exclamation-circle text-red-400';
                    bgClass = 'bg-red-50';
                    textClass = 'text-red-800';
                    break;
                case 'warning':
                    iconClass = 'fas fa-exclamation-triangle text-yellow-400';
                    bgClass = 'bg-yellow-50';
                    textClass = 'text-yellow-800';
                    break;
                default:
                    iconClass = 'fas fa-info-circle text-blue-400';
                    bgClass = 'bg-blue-50';
                    textClass = 'text-blue-800';
            }
            
            toast.innerHTML = `
                <div class="flex-1 w-0 p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="${iconClass}"></i>
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-medium ${textClass}">${message}</p>
                        </div>
                    </div>
                </div>
                <div class="flex border-l border-gray-200">
                    <button onclick="removeToast(this.parentElement.parentElement)" 
                            class="w-full border border-transparent rounded-none rounded-r-lg p-4 flex items-center justify-center text-sm font-medium text-gray-600 hover:text-gray-500 focus:outline-none">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            // Add to container
            toastContainer.appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
                toast.classList.add('translate-x-0', 'opacity-100');
            }, 100);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                removeToast(toast);
            }, 3000);
        }
        
        function removeToast(toast) {
            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.parentElement.removeChild(toast);
                }
            }, 300);
        }

        // Custom tooltip functions
        function showCustomTooltip(event, meeting, status) {
            console.log('showCustomTooltip called with:', meeting.assunto, status);
            
            // Remove any existing tooltip first
            hideCustomTooltip();
            
            // Create tooltip dynamically
            const tooltip = document.createElement('div');
            tooltip.id = 'activeTooltip';
            tooltip.style.cssText = `
                position: fixed;
                background: white;
                border-radius: 8px;
                padding: 12px;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
                z-index: 99999;
                font-size: 13px;
                line-height: 1.4;
                width: 250px;
                border-left: 4px solid;
                font-family: Inter, sans-serif;
            `;
            
            // Set status-specific styling
            let statusText, borderColor, bgGradient;
            switch(status) {
                case 'happening':
                    statusText = 'Em andamento';
                    borderColor = '#10b981';
                    bgGradient = 'linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%)';
                    break;
                case 'past':
                    statusText = 'Finalizada';
                    borderColor = '#ef4444';
                    bgGradient = 'linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%)';
                    break;
                default:
                    statusText = 'Agendada';
                    borderColor = '#f97316';
                    bgGradient = 'linear-gradient(135deg, #fff7ed 0%, #fed7aa 100%)';
            }
            
            tooltip.style.borderLeftColor = borderColor;
            tooltip.style.background = bgGradient;
            
            // Set tooltip content
            tooltip.innerHTML = `
                <div style="font-weight: 600; margin-bottom: 6px; color: #1f2937;">${meeting.assunto}</div>
                <div style="color: #6b7280; margin-bottom: 4px; display: flex; align-items: center;">
                    <i class="fas fa-clock" style="margin-right: 4px;"></i>
                    <span>${meeting.hora_inicio.substring(0,5)} - ${meeting.hora_fim.substring(0,5)}</span>
                </div>
                <div style="font-size: 12px; font-weight: 500; padding: 2px 8px; border-radius: 12px; display: inline-block; margin-top: 4px; background: ${borderColor}; color: white;">
                    ${statusText}
                </div>
            `;
            
            // Position tooltip
            const rect = event.target.getBoundingClientRect();
            let left = rect.left + (rect.width / 2) - 125;
            let top = rect.bottom + 10;
            
            // Adjust if tooltip goes off screen
            if (left < 10) left = 10;
            if (left + 250 > window.innerWidth - 10) {
                left = window.innerWidth - 260;
            }
            if (top + 80 > window.innerHeight - 10) {
                top = rect.top - 90;
                if (top < 10) top = rect.bottom + 10;
            }
            
            tooltip.style.left = left + 'px';
            tooltip.style.top = top + 'px';
            
            // Add to body
            document.body.appendChild(tooltip);
            
            console.log('Dynamic tooltip created and positioned at:', left, top);
        }
        
        function hideCustomTooltip() {
            // Remove static tooltip
            const tooltip = document.getElementById('customTooltip');
            if (tooltip) {
                tooltip.classList.remove('show');
                tooltip.style.display = 'none';
                tooltip.style.visibility = 'hidden';
                tooltip.style.opacity = '0';
            }
            
            // Remove dynamic tooltip
            const activeTooltip = document.getElementById('activeTooltip');
            if (activeTooltip) {
                activeTooltip.remove();
            }
        }

        function showAvailabilityModal() {
            document.getElementById('availabilityModal').classList.remove('hidden');
        }

        function hideAvailabilityModal() {
            document.getElementById('availabilityModal').classList.add('hidden');
        }

        function updateAppointmentStatus(appointmentId, status) {
            if (confirm('Tem certeza que deseja alterar o status desta reunião?')) {
                fetch('../api/update_appointment_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        appointment_id: appointmentId,
                        status: status
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        showToast('Erro ao atualizar status da reunião', 'error');
                    }
                });
            }
        }

        function loadSpecificAvailability() {
            console.log('Loading specific availability...');
            fetch('../api/get_specific_availability.php')
                .then(response => response.json())
                .then(data => {
                    console.log('API Response:', data); // Debug log
                    const container = document.getElementById('specificAvailabilityList');
                    
                    if (data.success && data.availability && data.availability.length > 0) {
                        // Group by date
                        const groupedByDate = {};
                        data.availability.forEach(item => {
                            if (!groupedByDate[item.specific_date]) {
                                groupedByDate[item.specific_date] = [];
                            }
                            groupedByDate[item.specific_date].push(item);
                        });

                        let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
                        Object.keys(groupedByDate).sort().forEach(date => {
                            const slots = groupedByDate[date];
                            const dayOfWeek = getDayOfWeek(date);
                            
                            html += `
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <h4 class="font-medium text-gray-900">${formatDate(date)}</h4>
                                            <p class="text-xs text-gray-500">${dayOfWeek}</p>
                                        </div>
                                        <div class="flex space-x-2">
                                            <button onclick="editSpecificDate('${date}')" class="text-blue-600 hover:text-blue-800 text-sm" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="confirmDeleteDate('${date}')" class="text-red-600 hover:text-red-800 text-sm" title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                            `;
                            
                            slots.forEach(slot => {
                                html += `
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-clock mr-1"></i>
                                        ${slot.start_time.substring(0,5)} - ${slot.end_time.substring(0,5)}
                                        <span class="text-xs text-gray-500 ml-2">(${slot.slot_duration} min)</span>
                                    </p>
                                `;
                            });
                            
                            html += `
                                    </div>
                                </div>
                            `;
                        });
                        html += '</div>';
                        container.innerHTML = html;
                        console.log('Availability loaded successfully with onclick handlers');
                    } else {
                        console.log('No availability data found or empty array'); // Debug log
                        container.innerHTML = `
                            <div class="text-center py-8">
                                <i class="fas fa-calendar-times text-gray-400 text-4xl mb-4"></i>
                                <p class="text-gray-600">Nenhuma data específica configurada ainda.</p>
                                <button onclick="showSpecificDateModal()" class="mt-4 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200">
                                    Configurar Data Específica
                                </button>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading availability:', error);
                    const container = document.getElementById('specificAvailabilityList');
                    container.innerHTML = `
                        <div class="text-center py-8">
                            <i class="fas fa-exclamation-triangle text-red-400 text-4xl mb-4"></i>
                            <p class="text-red-600">Erro ao carregar horários disponíveis.</p>
                            <button onclick="loadSpecificAvailability()" class="mt-4 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition duration-200">
                                Tentar Novamente
                            </button>
                        </div>
                    `;
                });
        }

        // Simple onclick delete function
        function confirmDeleteDate(date) {
            console.log('confirmDeleteDate called with date:', date);
            if (confirm('Tem certeza que deseja remover os horários desta data?')) {
                console.log('User confirmed deletion for date:', date);
                
                fetch('../api/delete_specific_availability.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ specific_date: date })
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Delete response:', data);
                    if (data.success) {
                        showToast('Horários removidos com sucesso!', 'success');
                        loadSpecificAvailability();
                    } else {
                        showToast('Erro ao remover horários: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Erro ao remover horários. Tente novamente.', 'error');
                });
            } else {
                console.log('User cancelled deletion for date:', date);
            }
        }

        function getDayOfWeek(dateString) {
            const days = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
            const date = new Date(dateString + 'T00:00:00');
            return days[date.getDay()];
        }

        function formatDate(dateString) {
            const date = new Date(dateString + 'T00:00:00');
            const day = date.getDate().toString().padStart(2, '0');
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }

        // Handle form submission
        document.getElementById('availabilityForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../api/save_availability.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    showToast('Erro ao salvar configurações', 'error');
                }
            });
        });

        // Handle meeting form submission
        document.getElementById('meetingForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Check if participants are selected
            if (selectedParticipants.length === 0) {
                showToast('Por favor, selecione pelo menos um participante para a reunião.', 'warning');
                return;
            }
            
            // Get form data
            const meetingDate = document.getElementById('meetingDate').value;
            const meetingStartTime = document.getElementById('meetingStartTime').value;
            const meetingEndTime = document.getElementById('meetingEndTime').value;
            const meetingSubject = document.getElementById('meetingSubject').value;
            
            // Validate time range
            if (meetingStartTime >= meetingEndTime) {
                showMeetingError('A hora de início deve ser anterior à hora de fim.');
                return;
            }

            // Validate if meeting is not in the past
            const meetingDateTime = new Date(meetingDate + 'T' + meetingStartTime);
            const currentDateTime = new Date();
            
            if (meetingDateTime < currentDateTime) {
                const currentDateStr = currentDateTime.toLocaleDateString('pt-BR');
                const currentTimeStr = currentDateTime.toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});
                const meetingDateStr = meetingDateTime.toLocaleDateString('pt-BR');
                const meetingTimeStr = meetingDateTime.toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});
                
                showMeetingError(`Não é possível agendar reunião no passado!\nData/Hora atual: ${currentDateStr} ${currentTimeStr}\nData/Hora da reunião: ${meetingDateStr} ${meetingTimeStr}`);
                return;
            }
            
            // Prepare meeting data for API
            const meetingData = {
                date: meetingDate,
                start_time: meetingStartTime,
                end_time: meetingEndTime,
                subject: meetingSubject,
                description: '', // Pode ser expandido no futuro
                participants: selectedParticipants
            };
            
            // Send to API
            fetch('../api/create_meeting.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(meetingData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    hideSpecificDateModal();
                    // Clear form and reset participants
                    document.getElementById('meetingForm').reset();
                    selectedParticipants = [];
                    updateSelectedParticipantsDisplay();
                    closeParticipantsDropdown();
                    reloadMeetingsOnCalendar();
                    showToast('Reunião criada com sucesso!', 'success');
                } else {
                    showToast('Erro ao criar reunião: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Erro ao conectar com o servidor. Tente novamente.', 'error');
            });
        });

        function showSpecificDateModal() {
            document.getElementById('specificDateModal').classList.remove('hidden');
            loadUsers(); // Carregar usuários ao abrir modal
        }

        function hideSpecificDateModal() {
            document.getElementById('specificDateModal').classList.add('hidden');
        }

        // Variables for new interactive participants selector
        let allUsers = [];
        let selectedParticipants = [];

        // Function to load users from API
        function loadUsers() {
            fetch('../api/get_users.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        allUsers = data.data;
                        renderParticipantsList();
                    } else {
                        console.error('Erro ao carregar usuários:', data.message);
                        document.getElementById('participantsList').innerHTML = 
                            '<div class="px-3 py-2 text-red-500 text-sm">Erro ao carregar usuários</div>';
                    }
                })
                .catch(error => {
                    console.error('Erro na requisição:', error);
                    document.getElementById('participantsList').innerHTML = 
                        '<div class="px-3 py-2 text-red-500 text-sm">Erro ao conectar com o servidor</div>';
                });
        }

        // Toggle dropdown visibility
        function toggleParticipantsDropdown() {
            const dropdown = document.getElementById('participantsDropdown');
            const icon = document.getElementById('dropdownIcon');
            
            if (dropdown.classList.contains('hidden')) {
                dropdown.classList.remove('hidden');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
                document.getElementById('participantsSearch').focus();
            } else {
                closeParticipantsDropdown();
            }
        }

        // Close dropdown
        function closeParticipantsDropdown() {
            const dropdown = document.getElementById('participantsDropdown');
            const icon = document.getElementById('dropdownIcon');
            dropdown.classList.add('hidden');
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
            document.getElementById('participantsSearch').value = '';
            renderParticipantsList();
        }

        // Filter participants based on search
        function filterParticipants() {
            const searchTerm = document.getElementById('participantsSearch').value.toLowerCase();
            const filteredUsers = allUsers.filter(user => 
                user.name.toLowerCase().includes(searchTerm) || 
                user.username.toLowerCase().includes(searchTerm) ||
                user.email.toLowerCase().includes(searchTerm)
            );
            renderParticipantsList(filteredUsers);
        }

        // Render participants list
        function renderParticipantsList(users = allUsers) {
            const listContainer = document.getElementById('participantsList');
            
            if (users.length === 0) {
                listContainer.innerHTML = '<div class="px-3 py-2 text-gray-500 text-sm">Nenhum usuário encontrado</div>';
                return;
            }

            listContainer.innerHTML = users.map(user => {
                const isSelected = selectedParticipants.some(p => p.id === user.id);
                
                return `
                    <div class="px-3 py-2 hover:bg-gray-100 cursor-pointer flex items-center justify-between ${isSelected ? 'bg-indigo-50' : ''}"
                         onclick="toggleParticipant(${user.id}, '${user.username || user.name}')">
                        <div>
                            <div class="font-medium text-sm">${user.username || user.name}</div>
                        </div>
                        ${isSelected ? '<i class="fas fa-check text-indigo-600"></i>' : ''}
                    </div>
                `;
            }).join('');
        }

        // Toggle participant selection
        function toggleParticipant(id, username) {
            const existingIndex = selectedParticipants.findIndex(p => p.id === id);
            
            if (existingIndex >= 0) {
                // Remove participant
                selectedParticipants.splice(existingIndex, 1);
            } else {
                // Add participant
                selectedParticipants.push({ id, username });
            }
            
            updateSelectedParticipantsDisplay();
            renderParticipantsList();
        }

        // Update the display of selected participants
        function updateSelectedParticipantsDisplay() {
            const placeholder = document.getElementById('participantsPlaceholder');
            const selectedContainer = document.getElementById('selectedParticipants');
            
            if (selectedParticipants.length === 0) {
                placeholder.style.display = 'block';
                selectedContainer.innerHTML = '';
            } else {
                placeholder.style.display = 'none';
                selectedContainer.innerHTML = selectedParticipants.map(participant => `
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                        ${participant.username}
                        <button type="button" class="ml-1 text-indigo-600 hover:text-indigo-800" onclick="removeParticipant(${participant.id})">
                            <i class="fas fa-times"></i>
                        </button>
                    </span>
                `).join('');
            }
        }

        // Remove participant
        function removeParticipant(id) {
            selectedParticipants = selectedParticipants.filter(p => p.id !== id);
            updateSelectedParticipantsDisplay();
            renderParticipantsList();
        }

        function addTimeSlot() {
            const timeSlotsList = document.getElementById('timeSlotsList');
            const timeSlotItem = document.querySelector('.time-slot-item');
            const newTimeSlot = timeSlotItem.cloneNode(true);
            timeSlotsList.appendChild(newTimeSlot);
        }

        function removeTimeSlot(button) {
            const timeSlotItem = button.parentNode.parentNode;
            timeSlotItem.remove();
        }

        function showTab(tabId) {
            const tabs = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');

            tabs.forEach(tab => tab.classList.remove('active'));
            tabContents.forEach(content => content.classList.add('hidden'));

            document.getElementById(tabId + 'Tab').classList.add('active');
            document.getElementById(tabId + 'Content').classList.remove('hidden');
        }

        // Calendar variables
        let currentDate = new Date();
        const monthNames = [
            'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
            'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
        ];

        // Load calendar on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, starting initialization...');
            initializeCalendar();
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('#participantsInput') && !e.target.closest('#participantsDropdown')) {
                    closeParticipantsDropdown();
                }
            });
        });

        function initializeCalendar() {
            renderCalendar();
            
            // Event listeners for navigation
            document.getElementById('prevMonth').addEventListener('click', () => {
                currentDate.setMonth(currentDate.getMonth() - 1);
                renderCalendar();
            });
            
            document.getElementById('nextMonth').addEventListener('click', () => {
                currentDate.setMonth(currentDate.getMonth() + 1);
                renderCalendar();
            });
        }

        function renderCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            
            // Update month header
            document.getElementById('currentMonth').textContent = `${monthNames[month]} ${year}`;
            
            // Get first day of month and number of days
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const startDate = new Date(firstDay);
            startDate.setDate(startDate.getDate() - firstDay.getDay());
            
            const calendarDays = document.getElementById('calendarDays');
            calendarDays.innerHTML = '';
            
            // Generate calendar with proper alignment
            const daysInMonth = lastDay.getDate();
            const firstDayOfWeek = firstDay.getDay(); // 0 = Sunday, 1 = Monday, etc.
            
            // Add empty cells for days before the first day of the month
            for (let i = 0; i < firstDayOfWeek; i++) {
                const emptyElement = document.createElement('div');
                emptyElement.className = 'h-full p-2 border border-gray-100 bg-gray-50';
                calendarDays.appendChild(emptyElement);
            }
            
            // Generate days of current month
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(year, month, day);
                
                const dayElement = document.createElement('div');
                dayElement.className = `
                    h-full p-2 border border-gray-200 cursor-pointer transition-colors duration-200
                    bg-white hover:bg-blue-50 flex flex-col
                    ${isToday(date) ? 'bg-blue-100 border-blue-300' : ''}
                `;
                
                dayElement.innerHTML = `
                    <div class="flex items-center justify-between mb-1">
                        <div class="font-medium text-sm">${day}</div>
                        <div class="flex space-x-1" id="events-${date.getFullYear()}-${date.getMonth()}-${date.getDate()}">
                            <!-- Events will be loaded here -->
                        </div>
                    </div>
                `;
                
                // Add click event
                dayElement.addEventListener('click', () => {
                    selectDate(date);
                });
                
                calendarDays.appendChild(dayElement);
            }
            
            // Load appointments for this month
            loadMonthAppointments(year, month);
        }

        function isToday(date) {
            const today = new Date();
            return date.toDateString() === today.toDateString();
        }

        function selectDate(date) {
            const dateStr = date.toISOString().split('T')[0];
            console.log('Selected date:', dateStr);
            
            // Pre-fill the meeting date and open modal
            document.getElementById('meetingDate').value = dateStr;
            showSpecificDateModal();
        }

        function loadMonthAppointments(year, month) {
            console.log('Loading meetings for month:', year, month);
            // Load meetings from database
            fetch('../api/get_meetings.php')
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Meetings data received:', data);
                    if (data.success && data.meetings) {
                        console.log('Number of meetings found:', data.meetings.length);
                        displayMeetingsOnCalendar(data.meetings);
                    } else {
                        console.log('No meetings found or API error');
                    }
                })
                .catch(error => {
                    console.error('Error loading meetings:', error);
                });
        }

        function getMeetingStatus(meeting) {
            const now = new Date();
            const meetingDate = new Date(meeting.data_reuniao + 'T' + meeting.hora_inicio);
            const meetingEndDate = new Date(meeting.data_reuniao + 'T' + meeting.hora_fim);
            const oneMinuteAfterEnd = new Date(meetingEndDate.getTime() + 1 * 60 * 1000); // 1 minuto após o fim
            
            console.log('Checking meeting status for:', meeting.assunto);
            console.log('Current time:', now.toLocaleString('pt-BR'));
            console.log('Meeting start:', meetingDate.toLocaleString('pt-BR'));
            console.log('Meeting end:', meetingEndDate.toLocaleString('pt-BR'));
            console.log('1 minute after end:', oneMinuteAfterEnd.toLocaleString('pt-BR'));
            
            if (now >= meetingDate && now < meetingEndDate) {
                console.log('Status: happening (em andamento)');
                return 'happening'; // Verde - acontecendo agora
            } else if (now >= meetingEndDate && now <= oneMinuteAfterEnd) {
                console.log('Status: happening (ainda em andamento até o fim exato)');
                return 'happening'; // Verde - ainda em andamento até o minuto exato do fim
            } else if (now > oneMinuteAfterEnd) {
                console.log('Status: past (finalizada)');
                return 'past'; // Vermelho - finalizada (1+ min após o fim)
            } else {
                console.log('Status: future (agendada)');
                return 'future'; // Laranja - futuro
            }
        }

        function displayMeetingsOnCalendar(meetings) {
            console.log('Displaying meetings on calendar:', meetings);
            // Clear existing events
            document.querySelectorAll('[id^="events-"]').forEach(container => {
                container.innerHTML = '';
            });

            meetings.forEach(meeting => {
                console.log('Processing meeting:', meeting);
                const meetingDate = new Date(meeting.data_reuniao + 'T00:00:00');
                console.log('Meeting date object:', meetingDate);
                const eventId = `events-${meetingDate.getFullYear()}-${meetingDate.getMonth()}-${meetingDate.getDate()}`;
                console.log('Looking for container with ID:', eventId);
                const eventContainer = document.getElementById(eventId);
                
                if (eventContainer) {
                    console.log('Container found, adding event');
                    
                    // Determine meeting status and color
                    const status = getMeetingStatus(meeting);
                    let dotColor, hoverColor;
                    
                    switch(status) {
                        case 'happening':
                            dotColor = 'bg-green-500';
                            hoverColor = 'hover:bg-green-600';
                            break;
                        case 'past':
                            dotColor = 'bg-red-500';
                            hoverColor = 'hover:bg-red-600';
                            break;
                        default: // future
                            dotColor = 'bg-orange-500';
                            hoverColor = 'hover:bg-orange-600';
                    }
                    
                    const eventElement = document.createElement('div');
                    let className = `w-6 h-6 ${dotColor} ${hoverColor} rounded-full cursor-pointer transition-colors`;
                    
                    // Add pulsing animation for meetings in progress
                    if (status === 'happening') {
                        className += ' meeting-happening';
                    }
                    
                    eventElement.className = className;
                    eventElement.dataset.meetingId = meeting.id;
                    
                    // Add custom tooltip events
                    eventElement.addEventListener('mouseenter', (e) => {
                        console.log('Mouse enter event triggered', meeting.assunto);
                        showCustomTooltip(e, meeting, status);
                    });
                    
                    eventElement.addEventListener('mouseleave', () => {
                        console.log('Mouse leave event triggered');
                        hideCustomTooltip();
                    });
                    
                    // Add click event to show meeting details
                    eventElement.addEventListener('click', (e) => {
                        e.stopPropagation();
                        hideCustomTooltip(); // Hide tooltip when clicking
                        showMeetingDetails(meeting);
                    });
                    
                    eventContainer.appendChild(eventElement);
                } else {
                    console.log('Container not found for ID:', eventId);
                }
            });
        }

        function showMeetingDetails(meeting) {
            // Calculate dynamic status based on current time
            const dynamicStatus = getMeetingStatus(meeting);
            let statusValue;
            
            switch(dynamicStatus) {
                case 'happening':
                    statusValue = 'em_andamento';
                    break;
                case 'past':
                    statusValue = 'concluida';
                    break;
                default: // future
                    statusValue = 'agendada';
            }
            
            // Populate the edit form with meeting data
            document.getElementById('editMeetingId').value = meeting.id;
            document.getElementById('editMeetingDate').value = meeting.data_reuniao;
            document.getElementById('editMeetingStartTime').value = meeting.hora_inicio;
            document.getElementById('editMeetingEndTime').value = meeting.hora_fim;
            document.getElementById('editMeetingSubject').value = meeting.assunto;
            document.getElementById('editMeetingDescription').value = meeting.descricao || '';
            document.getElementById('editMeetingStatus').value = statusValue;
            
            // Check if meeting is finished (past) and disable fields accordingly
            const isFinished = statusValue === 'concluida';
            
            // Disable all fields except description if meeting is finished
            document.getElementById('editMeetingDate').disabled = isFinished;
            document.getElementById('editMeetingStartTime').disabled = isFinished;
            document.getElementById('editMeetingEndTime').disabled = isFinished;
            document.getElementById('editMeetingSubject').disabled = isFinished;
            document.getElementById('editMeetingDescription').disabled = false; // Always editable
            
            // Disable participants dropdown if meeting is finished
            const participantsInput = document.getElementById('editParticipantsInput');
            if (isFinished) {
                participantsInput.style.pointerEvents = 'none';
                participantsInput.style.opacity = '0.6';
                participantsInput.style.cursor = 'not-allowed';
            } else {
                participantsInput.style.pointerEvents = 'auto';
                participantsInput.style.opacity = '1';
                participantsInput.style.cursor = 'pointer';
            }
            
            // Update field styles for disabled state
            const fieldsToStyle = ['editMeetingDate', 'editMeetingStartTime', 'editMeetingEndTime', 'editMeetingSubject'];
            fieldsToStyle.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (isFinished) {
                    field.classList.add('bg-gray-100', 'text-gray-600', 'cursor-not-allowed');
                    field.classList.remove('focus:ring-2', 'focus:ring-indigo-500');
                } else {
                    field.classList.remove('bg-gray-100', 'text-gray-600', 'cursor-not-allowed');
                    field.classList.add('focus:ring-2', 'focus:ring-indigo-500');
                }
            });
            
            // Update modal title to indicate if meeting is finished
            const modalTitle = document.querySelector('#meetingDetailsModal h3');
            if (isFinished) {
                modalTitle.innerHTML = '<i class="fas fa-check-circle text-green-600 mr-2"></i>Reunião Finalizada - Apenas Descrição Editável';
            } else {
                modalTitle.textContent = 'Detalhes da Reunião';
            }
            
            // Load meeting participants
            loadMeetingParticipants(meeting.id);
            
            // Show the modal
            document.getElementById('meetingDetailsModal').classList.remove('hidden');
        }

        function hideMeetingDetailsModal() {
            document.getElementById('meetingDetailsModal').classList.add('hidden');
            editSelectedParticipants = [];
            updateEditSelectedParticipantsDisplay();
        }

        // Variables for edit participants
        let editSelectedParticipants = [];
        let editAllUsers = [];

        function loadMeetingParticipants(meetingId) {
            fetch(`../api/get_meeting_participants.php?meeting_id=${meetingId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.participants) {
                        editSelectedParticipants = data.participants.map(p => ({
                            id: p.id_usuario,
                            username: p.username_usuario
                        }));
                        updateEditSelectedParticipantsDisplay();
                    }
                })
                .catch(error => {
                    console.error('Error loading meeting participants:', error);
                });
        }

        function toggleEditParticipantsDropdown() {
            const dropdown = document.getElementById('editParticipantsDropdown');
            const icon = document.getElementById('editDropdownIcon');
            
            if (dropdown.classList.contains('hidden')) {
                dropdown.classList.remove('hidden');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
                
                if (editAllUsers.length === 0) {
                    loadEditUsers();
                }
            } else {
                closeEditParticipantsDropdown();
            }
        }

        function closeEditParticipantsDropdown() {
            const dropdown = document.getElementById('editParticipantsDropdown');
            const icon = document.getElementById('editDropdownIcon');
            
            dropdown.classList.add('hidden');
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
        }

        function loadEditUsers() {
            fetch('../api/get_users.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.users) {
                        editAllUsers = data.users;
                        renderEditParticipantsList();
                    }
                })
                .catch(error => {
                    console.error('Error loading users for edit:', error);
                });
        }

        function renderEditParticipantsList() {
            const container = document.getElementById('editParticipantsList');
            const searchTerm = document.getElementById('editParticipantsSearch').value.toLowerCase();
            
            const filteredUsers = editAllUsers.filter(user => 
                user.name.toLowerCase().includes(searchTerm) ||
                user.username.toLowerCase().includes(searchTerm) ||
                user.email.toLowerCase().includes(searchTerm)
            );
            
            container.innerHTML = filteredUsers.map(user => {
                const isSelected = editSelectedParticipants.some(p => p.id === user.id);
                return `
                    <div class="px-3 py-2 hover:bg-gray-100 cursor-pointer flex items-center justify-between ${isSelected ? 'bg-blue-50' : ''}"
                         onclick="toggleEditParticipant(${user.id}, '${user.username}')">
                        <span class="text-sm">${user.username}</span>
                        ${isSelected ? '<i class="fas fa-check text-blue-600"></i>' : ''}
                    </div>
                `;
            }).join('');
        }

        function toggleEditParticipant(id, username) {
            const existingIndex = editSelectedParticipants.findIndex(p => p.id === id);
            
            if (existingIndex > -1) {
                editSelectedParticipants.splice(existingIndex, 1);
            } else {
                editSelectedParticipants.push({ id, username });
            }
            
            updateEditSelectedParticipantsDisplay();
            renderEditParticipantsList();
        }

        function updateEditSelectedParticipantsDisplay() {
            const placeholder = document.getElementById('editParticipantsPlaceholder');
            const selectedContainer = document.getElementById('editSelectedParticipants');
            
            if (editSelectedParticipants.length === 0) {
                placeholder.style.display = 'block';
                selectedContainer.innerHTML = '';
            } else {
                placeholder.style.display = 'none';
                selectedContainer.innerHTML = editSelectedParticipants.map(participant => `
                    <span class="bg-indigo-100 text-indigo-800 text-xs px-2 py-1 rounded-full flex items-center">
                        ${participant.username}
                        <button type="button" class="ml-1 text-indigo-600 hover:text-indigo-800" onclick="removeEditParticipant(${participant.id})">
                            <i class="fas fa-times"></i>
                        </button>
                    </span>
                `).join('');
            }
        }

        function removeEditParticipant(id) {
            editSelectedParticipants = editSelectedParticipants.filter(p => p.id !== id);
            updateEditSelectedParticipantsDisplay();
            renderEditParticipantsList();
        }

        function filterEditParticipants() {
            renderEditParticipantsList();
        }

        // Error message functions
        function showMeetingError(message) {
            const errorContainer = document.getElementById('meetingErrorMessage');
            const errorText = document.getElementById('meetingErrorText');
            
            errorText.textContent = message;
            errorContainer.classList.remove('hidden');
            
            // Auto-hide after 10 seconds
            setTimeout(() => {
                hideMeetingError();
            }, 10000);
        }

        function hideMeetingError() {
            const errorContainer = document.getElementById('meetingErrorMessage');
            errorContainer.classList.add('hidden');
        }

        // Handle edit meeting form submission
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('editMeetingForm').addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('🔄 Formulário de edição submetido');
                
                const meetingId = document.getElementById('editMeetingId').value;
                const meetingDate = document.getElementById('editMeetingDate').value;
                const meetingStartTime = document.getElementById('editMeetingStartTime').value;
                const meetingEndTime = document.getElementById('editMeetingEndTime').value;
                const meetingSubject = document.getElementById('editMeetingSubject').value;
                const meetingDescription = document.getElementById('editMeetingDescription').value;
                const meetingStatus = document.getElementById('editMeetingStatus').value;
                
                console.log('📝 Dados coletados:', {
                    meetingId, meetingDate, meetingStartTime, meetingEndTime, 
                    meetingSubject, meetingDescription, meetingStatus
                });
                
                // Validate required fields
                if (!meetingId || !meetingDate || !meetingStartTime || !meetingEndTime || !meetingSubject) {
                    showToast('Por favor, preencha todos os campos obrigatórios.', 'warning');
                    return;
                }
                
                // Validate time range
                if (meetingStartTime >= meetingEndTime) {
                    showToast('A hora de início deve ser anterior à hora de fim.', 'warning');
                    return;
                }
                
                // Prepare meeting data for API
                const meetingData = {
                    meeting_id: meetingId,
                    date: meetingDate,
                    start_time: meetingStartTime,
                    end_time: meetingEndTime,
                    subject: meetingSubject,
                    description: meetingDescription,
                    status: meetingStatus,
                    participants: editSelectedParticipants || []
                };
                
                console.log('📤 Enviando dados para API:', meetingData);
                
                // Show loading state
                const submitButton = e.target.querySelector('button[type="submit"]');
                const originalText = submitButton.innerHTML;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Salvando...';
                submitButton.disabled = true;
                
                // Send to API
                fetch('../api/update_meeting.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(meetingData)
                })
                .then(response => {
                    console.log('📥 Resposta recebida:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('📊 Dados da resposta:', data);
                    
                    // Restore button state
                    submitButton.innerHTML = originalText;
                    submitButton.disabled = false;
                    
                    if (data.success) {
                        hideMeetingDetailsModal();
                        // Clear form - CORRIGIDO: usar editMeetingForm ao invés de meetingForm
                        document.getElementById('editMeetingForm').reset();
                        editSelectedParticipants = [];
                        updateEditSelectedParticipantsDisplay();
                        // Hide error message if visible
                        hideMeetingError();
                        // Reload calendar to show updated meeting
                        reloadMeetingsOnCalendar();
                        showToast('Reunião atualizada com sucesso!', 'success');
                    } else {
                        showMeetingError(data.message || 'Erro desconhecido ao atualizar reunião');
                    }
                })
                .catch(error => {
                    console.error('❌ Erro na requisição:', error);
                    
                    // Restore button state
                    submitButton.innerHTML = originalText;
                    submitButton.disabled = false;
                    
                    showToast('Erro ao atualizar reunião. Verifique sua conexão e tente novamente.', 'error');
                });
            });
        });

        function deleteMeeting() {
            const meetingId = document.getElementById('editMeetingId').value;
            const meetingSubject = document.getElementById('editMeetingSubject').value;
            
            if (confirm(`Tem certeza que deseja excluir a reunião "${meetingSubject}"?\nEsta ação não pode ser desfeita.`)) {
                fetch('../api/delete_meeting.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ meeting_id: meetingId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Reunião excluída com sucesso!', 'success');
                        hideMeetingDetailsModal();
                        // Reload calendar to remove deleted meeting
                        reloadMeetingsOnCalendar();
                    } else {
                        showToast('Erro ao excluir reunião: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Erro ao excluir reunião. Tente novamente.', 'error');
                });
            }
        }

        // New delegated delete function to handle event properly
        function deleteSpecificDateDelegated(date, button) {
            if (confirm('Tem certeza que deseja remover os horários desta data?')) {
                // Disable the button to prevent multiple clicks
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                fetch('../api/delete_specific_availability.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ specific_date: date })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Horários removidos com sucesso!', 'success');
                        loadSpecificAvailability();
                    } else {
                        showToast('Erro ao remover horários: ' + data.message, 'error');
                        // Re-enable button on error
                        button.disabled = false;
                        button.innerHTML = '<i class="fas fa-trash"></i>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Erro ao remover horários. Tente novamente.', 'error');
                    // Re-enable button on error
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-trash"></i>';
                });
            }
        }

        function showProfileModal() {
            document.getElementById('profileModal').classList.remove('hidden');
        }

        function hideProfileModal() {
            document.getElementById('profileModal').classList.add('hidden');
        }

        function togglePasswordVisibility(inputId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(inputId + 'Icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Handle profile form submission
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../api/update_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Perfil atualizado com sucesso!', 'success');
                    hideProfileModal();
                    location.reload();
                } else {
                    showToast('Erro ao atualizar perfil: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Erro ao atualizar perfil. Tente novamente.', 'error');
            });
        });

        // Function to reload meetings on calendar
        function reloadMeetingsOnCalendar() {
            console.log('Recarregando reuniões no calendário...');
            loadMonthAppointments(currentDate.getFullYear(), currentDate.getMonth());
        }

        // Auto-update meeting colors every 30 seconds
        setInterval(() => {
            console.log('Auto-updating meeting colors...');
            reloadMeetingsOnCalendar();
        }, 30000); // Update every 30 seconds
        
        // Force update on page load after 2 seconds
        setTimeout(() => {
            console.log('Initial force update of meeting colors...');
            reloadMeetingsOnCalendar();
        }, 2000);

        // Reports Modal Functions
        let allMeetingsData = [];

        function showReportsModal() {
            document.getElementById('reportsModal').classList.remove('hidden');
            loadAllMeetings();
            loadParticipants();
        }

        function hideReportsModal() {
            document.getElementById('reportsModal').classList.add('hidden');
        }

        function loadAllMeetings() {
            const loading = document.getElementById('reportsLoading');
            const empty = document.getElementById('reportsEmpty');
            const table = document.querySelector('.overflow-x-auto');
            
            loading.classList.remove('hidden');
            empty.classList.add('hidden');
            table.classList.add('hidden');

            fetch('../api/get_all_meetings.php')
                .then(response => response.json())
                .then(data => {
                    console.log('All meetings data:', data);
                    if (data.success && data.meetings) {
                        allMeetingsData = data.meetings;
                        displayMeetingsInTable(allMeetingsData);
                    } else {
                        showEmptyState();
                    }
                })
                .catch(error => {
                    console.error('Error loading all meetings:', error);
                    showEmptyState();
                })
                .finally(() => {
                    loading.classList.add('hidden');
                });
        }

        function displayMeetingsInTable(meetings) {
            const tbody = document.getElementById('meetingsTableBody');
            const table = document.querySelector('.overflow-x-auto');
            const empty = document.getElementById('reportsEmpty');
            
            if (meetings.length === 0) {
                showEmptyState();
                return;
            }

            tbody.innerHTML = '';
            table.classList.remove('hidden');
            empty.classList.add('hidden');

            meetings.forEach(meeting => {
                const status = getMeetingStatus(meeting);
                const statusInfo = getStatusInfo(status);
                
                const row = document.createElement('tr');
                const statusColors = getStatusRowColors(status);
                row.className = `hover:opacity-90 transition-opacity ${statusColors.bgClass} ${statusColors.borderClass}`;
                
                // Format date - fix timezone issue
                const dateParts = meeting.data_reuniao.split('-');
                const date = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]); // Year, Month (0-based), Day
                const formattedDate = date.toLocaleDateString('pt-BR');
                
                // Format participants
                const participants = meeting.participantes ? meeting.participantes.split(',').map(p => p.trim()).join(', ') : 'Não informado';
                
                // Truncate description if too long
                const description = meeting.descricao ? 
                    (meeting.descricao.length > 50 ? meeting.descricao.substring(0, 50) + '...' : meeting.descricao) : 
                    'Sem descrição';

                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${formattedDate}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${meeting.hora_inicio} - ${meeting.hora_fim}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">${meeting.assunto}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">${participants}</td>
                    <td class="px-6 py-4 text-sm text-gray-500" title="${meeting.descricao || 'Sem descrição'}">${description}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusInfo.bgColor} ${statusInfo.textColor}">
                            <i class="${statusInfo.icon} mr-1"></i>
                            ${statusInfo.label}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <button onclick="viewMeetingFromReport(${meeting.id})" class="text-blue-600 hover:text-blue-800 transition-colors" title="Visualizar detalhes">
                            <i class="fas fa-eye text-lg"></i>
                        </button>
                    </td>
                `;
                
                tbody.appendChild(row);
            });

            updateTotalCount(meetings.length);
        }

        function getStatusInfo(status) {
            switch(status) {
                case 'happening':
                    return {
                        label: 'Em Andamento',
                        bgColor: 'bg-green-100',
                        textColor: 'text-green-800',
                        icon: 'fas fa-play-circle'
                    };
                case 'past':
                    return {
                        label: 'Finalizada',
                        bgColor: 'bg-red-100',
                        textColor: 'text-red-800',
                        icon: 'fas fa-check-circle'
                    };
                default: // future
                    return {
                        label: 'Agendada',
                        bgColor: 'bg-orange-100',
                        textColor: 'text-orange-800',
                        icon: 'fas fa-clock'
                    };
            }
        }

        function loadParticipants() {
            fetch('../api/get_participants.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.participants) {
                        const participantSelect = document.getElementById('participantFilter');
                        
                        // Clear existing options except the first one
                        participantSelect.innerHTML = '<option value="all">Todos os participantes</option>';
                        
                        // Add participants to dropdown
                        data.participants.forEach(participant => {
                            const option = document.createElement('option');
                            option.value = participant.nome;
                            option.textContent = participant.nome;
                            participantSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading participants:', error);
                });
        }

        function applyFilters() {
            const statusFilter = document.getElementById('statusFilter').value;
            const specificDateFilter = document.getElementById('specificDateFilter').value;
            const participantFilter = document.getElementById('participantFilter').value;
            
            let filteredMeetings = allMeetingsData.filter(meeting => {
                // Status filter
                if (statusFilter !== 'all') {
                    const status = getMeetingStatus(meeting);
                    if (status !== statusFilter) return false;
                }
                
                // Specific date filter
                if (specificDateFilter) {
                    const meetingDate = meeting.data_reuniao;
                    if (meetingDate !== specificDateFilter) return false;
                }
                
                // Participant filter
                if (participantFilter !== 'all') {
                    const participants = meeting.participantes ? meeting.participantes : '';
                    if (!participants.includes(participantFilter)) return false;
                }
                
                return true;
            });
            
            displayMeetingsInTable(filteredMeetings);
        }

        function clearAllFilters() {
            document.getElementById('statusFilter').value = 'all';
            document.getElementById('specificDateFilter').value = '';
            document.getElementById('participantFilter').value = 'all';
            displayMeetingsInTable(allMeetingsData);
        }

        // Keep old function for backward compatibility
        function filterMeetings() {
            applyFilters();
        }

        function updateTotalCount(count) {
            document.getElementById('totalMeetings').textContent = count;
        }

        function getStatusRowColors(status) {
            switch(status) {
                case 'happening':
                    return {
                        bgClass: 'bg-green-50',
                        borderClass: 'border-l-4 border-green-500'
                    };
                case 'past':
                    return {
                        bgClass: 'bg-red-50',
                        borderClass: 'border-l-4 border-red-500'
                    };
                default: // future
                    return {
                        bgClass: 'bg-orange-50',
                        borderClass: 'border-l-4 border-orange-500'
                    };
            }
        }

        function viewMeetingFromReport(meetingId) {
            // Find the meeting data
            const meeting = allMeetingsData.find(m => m.id == meetingId);
            if (meeting) {
                // Use the existing showMeetingDetails function
                showMeetingDetails(meeting);
            }
        }

        function showEmptyState() {
            const table = document.querySelector('.overflow-x-auto');
            const empty = document.getElementById('reportsEmpty');
            
            table.classList.add('hidden');
            empty.classList.remove('hidden');
            updateTotalCount(0);
        }
    </script>
</body>
</html>
