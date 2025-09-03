<?php
require_once '../config/users_database.php';

// Get user info from URL parameters
$user_id = $_GET['id'] ?? null;
$username = $_GET['username'] ?? null;

if ($user_id && $username) {
    // Buscar dados reais do usuário no banco cadastro_empresas
    $usersDb = new UsersDatabase();
    $user_data = $usersDb->getUserById($user_id);
    
    // Debug: verificar se encontrou o usuário
    if (!$user_data) {
        http_response_code(404);
        echo "Usuário com ID $user_id não encontrado no banco cadastro_empresas.";
        exit;
    }
    
    if ($user_data['username'] !== $username) {
        http_response_code(401);
        echo "Username não confere. Esperado: $username, Encontrado: " . $user_data['username'];
        exit;
    }
    
    $user_info = [
        'id' => $user_data['id'],
        'name' => $user_data['name'],
        'username' => $user_data['username'],
        'email' => $user_data['email'],
        'role' => $user_data['role']
    ];
} else {
    // Mock user info for direct access
    $user_info = [
        'id' => 1,
        'name' => 'Gestor RH',
        'username' => 'gestor_rh',
        'email' => 'hr@empresa.com',
        'role' => 'hr_manager'
    ];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgendaRH - Dashboard Simplificado</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">
                <i class="fas fa-calendar-alt mr-2"></i>
                AgendaRH - Dashboard
            </h1>
            
            <!-- User Info Display -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h2 class="text-lg font-semibold text-blue-800 mb-2">
                    <i class="fas fa-user mr-2"></i>
                    Usuário Logado
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <strong>Nome:</strong> <?php echo htmlspecialchars($user_info['name']); ?>
                    </div>
                    <div>
                        <strong>Username:</strong> <?php echo htmlspecialchars($user_info['username']); ?>
                    </div>
                    <div>
                        <strong>Email:</strong> <?php echo htmlspecialchars($user_info['email']); ?>
                    </div>
                    <div>
                        <strong>Role:</strong> <?php echo htmlspecialchars($user_info['role']); ?>
                    </div>
                </div>
            </div>
            
            <!-- Integration Status -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <h2 class="text-lg font-semibold text-green-800 mb-2">
                    <i class="fas fa-check-circle mr-2"></i>
                    Status da Integração
                </h2>
                <div class="space-y-2">
                    <?php if ($user_id && $username): ?>
                        <div class="flex items-center text-green-700">
                            <i class="fas fa-check mr-2"></i>
                            Integração com sistema Python: <strong>ATIVA</strong>
                        </div>
                        <div class="flex items-center text-green-700">
                            <i class="fas fa-check mr-2"></i>
                            Usuário autenticado via parâmetros URL
                        </div>
                        <div class="flex items-center text-green-700">
                            <i class="fas fa-check mr-2"></i>
                            Dados carregados do banco cadastro_empresas
                        </div>
                    <?php else: ?>
                        <div class="flex items-center text-orange-700">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Modo de teste: usando dados mock
                        </div>
                        <div class="text-sm text-gray-600 mt-2">
                            Para integração com Python, use: 
                            <code class="bg-gray-200 px-2 py-1 rounded">?id=[USER_ID]&username=[USERNAME]</code>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Meeting Creation Form -->
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-plus mr-2"></i>
                    Criar Nova Reunião
                </h2>
                
                <form id="meetingForm" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data</label>
                            <input type="date" name="date" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hora de Início</label>
                            <input type="time" name="start_time" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hora de Fim</label>
                            <input type="time" name="end_time" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Criador</label>
                            <input type="text" value="<?php echo htmlspecialchars($user_info['name']); ?>" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100" readonly>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Assunto</label>
                        <input type="text" name="subject" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                        <textarea name="description" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Participantes</label>
                        <div class="relative">
                            <!-- Input field that opens dropdown -->
                            <div id="participantsInput" 
                                 class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white cursor-pointer min-h-[42px] flex flex-wrap items-center gap-1"
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
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                           onclick="event.stopPropagation()"
                                           oninput="filterParticipants()">
                                </div>
                                <!-- Users list -->
                                <div id="participantsList" class="py-1">
                                    <div class="px-3 py-2 text-gray-500 text-sm">Carregando usuários...</div>
                                </div>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Clique no campo acima para selecionar múltiplos participantes
                        </p>
                    </div>
                    
                    <div>
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <i class="fas fa-save mr-2"></i>
                            Criar Reunião
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let allUsers = [];
        let selectedParticipants = [];

        // Load users when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadUsers();
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('#participantsInput') && !e.target.closest('#participantsDropdown')) {
                    closeParticipantsDropdown();
                }
            });
        });

        // Function to load users from API
        function loadUsers() {
            fetch('../api/get_users.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        allUsers = data.data;
                        renderParticipantsList();
                    } else {
                        console.error('Error loading users:', data.message);
                        document.getElementById('participantsList').innerHTML = 
                            '<div class="px-3 py-2 text-red-500 text-sm">Erro ao carregar usuários</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('participantsList').innerHTML = 
                        '<div class="px-3 py-2 text-red-500 text-sm">Erro ao carregar usuários</div>';
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
                user.username.toLowerCase().includes(searchTerm)
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
                    <div class="px-3 py-2 hover:bg-gray-100 cursor-pointer flex items-center justify-between ${isSelected ? 'bg-blue-50' : ''}"
                         onclick="toggleParticipant(${user.id}, '${user.name.replace(/'/g, "\\'")}', '${user.username}')">
                        <div>
                            <div class="font-medium text-sm">${user.name}</div>
                            <div class="text-xs text-gray-500">${user.username}</div>
                        </div>
                        ${isSelected ? '<i class="fas fa-check text-blue-600"></i>' : ''}
                    </div>
                `;
            }).join('');
        }

        // Toggle participant selection
        function toggleParticipant(id, name, username) {
            const existingIndex = selectedParticipants.findIndex(p => p.id === id);
            
            if (existingIndex >= 0) {
                // Remove participant
                selectedParticipants.splice(existingIndex, 1);
            } else {
                // Add participant
                selectedParticipants.push({ id, name, username });
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
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        ${participant.name}
                        <button type="button" class="ml-1 text-blue-600 hover:text-blue-800" onclick="removeParticipant(${participant.id})">
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

        // Form submission
        document.getElementById('meetingForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            if (selectedParticipants.length === 0) {
                alert('Por favor, selecione pelo menos um participante para a reunião.');
                return;
            }
            
            // Prepare meeting data
            const meetingData = {
                creator_id: <?php echo $user_info['id']; ?>,
                creator_name: '<?php echo htmlspecialchars($user_info['name']); ?>',
                date: formData.get('date'),
                start_time: formData.get('start_time'),
                end_time: formData.get('end_time'),
                subject: formData.get('subject'),
                description: formData.get('description'),
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
                    alert('Reunião criada com sucesso!\n\n' +
                          'Criador: ' + meetingData.creator_name + '\n' +
                          'Data: ' + meetingData.date + '\n' +
                          'Horário: ' + meetingData.start_time + ' - ' + meetingData.end_time + '\n' +
                          'Participantes: ' + selectedParticipants.length + ' selecionados');
                    
                    // Reset form
                    document.getElementById('meetingForm').reset();
                    selectedParticipants = [];
                    updateSelectedParticipantsDisplay();
                    closeParticipantsDropdown();
                } else {
                    alert('Erro ao criar reunião: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao criar reunião. Tente novamente.');
            });
        });
    </script>
</body>
</html>
