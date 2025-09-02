<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../classes/User.php';
require_once '../classes/Appointment.php';

checkRole('admin');
$user_info = getUserInfo();

$database = new Database();
$db = $database->getConnection();
$appointment = new Appointment($db);
$user = new User($db);

// Get appointments for admin view (limited information)
$appointments = $appointment->getAppointmentsForAdmin();
$all_users = $user->getAllUsers();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistema de Agendamentos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-shield-alt text-indigo-600 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-xl font-semibold text-gray-900">Dashboard Admin</h1>
                        <p class="text-sm text-gray-600">Bem-vindo, <?php echo htmlspecialchars($user_info['name']); ?></p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="showBookingModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-plus mr-2"></i>Agendar Reunião
                    </button>
                    <button onclick="showProfileModal()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-user mr-2"></i>Conta
                    </button>
                    <a href="../auth/logout.php" class="text-gray-600 hover:text-gray-900 transition duration-200">
                        <i class="fas fa-sign-out-alt mr-2"></i>Sair
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-calendar-check text-green-600 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Reuniões Hoje</dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    <?php 
                                    $today_count = 0;
                                    foreach($appointments as $apt) {
                                        if($apt['appointment_date'] == date('Y-m-d') && $apt['status'] == 'scheduled') {
                                            $today_count++;
                                        }
                                    }
                                    echo $today_count;
                                    ?>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock text-blue-600 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Esta Semana</dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    <?php 
                                    $week_start = date('Y-m-d', strtotime('monday this week'));
                                    $week_end = date('Y-m-d', strtotime('sunday this week'));
                                    $week_count = 0;
                                    foreach($appointments as $apt) {
                                        if($apt['appointment_date'] >= $week_start && $apt['appointment_date'] <= $week_end) {
                                            $week_count++;
                                        }
                                    }
                                    echo $week_count;
                                    ?>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-users text-purple-600 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Usuários</dt>
                                <dd class="text-lg font-medium text-gray-900"><?php echo count($all_users); ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-history text-orange-600 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Reuniões</dt>
                                <dd class="text-lg font-medium text-gray-900"><?php echo count($appointments); ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Appointments Overview -->
        <div class="bg-white shadow rounded-lg mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-calendar-alt mr-2"></i>Visão Geral das Reuniões
                </h3>
                <p class="text-sm text-gray-600 mt-1">Como admin, você pode ver quem está fazendo reuniões, mas não o motivo ou descrição.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data/Hora</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participante</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gestor RH</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($appointments)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                    <i class="fas fa-calendar-times text-4xl mb-2 block"></i>
                                    Nenhuma reunião registrada ainda.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($appointments as $apt): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo date('d/m/Y', strtotime($apt['appointment_date'])); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo date('H:i', strtotime($apt['start_time'])); ?> - 
                                            <?php echo date('H:i', strtotime($apt['end_time'])); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                                                <i class="fas fa-user text-indigo-600 text-sm"></i>
                                            </div>
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($apt['user_name']); ?></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                                <i class="fas fa-user-tie text-green-600 text-sm"></i>
                                            </div>
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($apt['hr_manager_name']); ?></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $status_classes = [
                                            'scheduled' => 'bg-blue-100 text-blue-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800'
                                        ];
                                        $status_labels = [
                                            'scheduled' => 'Agendada',
                                            'completed' => 'Concluída',
                                            'cancelled' => 'Cancelada'
                                        ];
                                        ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $status_classes[$apt['status']]; ?>">
                                            <?php echo $status_labels[$apt['status']]; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Users Overview -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-users mr-2"></i>Usuários do Sistema
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Função</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cadastrado em</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($all_users as $user): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 bg-gray-100 rounded-full flex items-center justify-center mr-3">
                                            <?php
                                            $role_icons = [
                                                'hr_manager' => 'fas fa-user-tie text-green-600',
                                                'admin' => 'fas fa-shield-alt text-blue-600',
                                                'common_user' => 'fas fa-user text-gray-600'
                                            ];
                                            ?>
                                            <i class="<?php echo $role_icons[$user['role']]; ?> text-sm"></i>
                                        </div>
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['name']); ?></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($user['email']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $role_classes = [
                                        'hr_manager' => 'bg-green-100 text-green-800',
                                        'admin' => 'bg-blue-100 text-blue-800',
                                        'common_user' => 'bg-gray-100 text-gray-800'
                                    ];
                                    $role_labels = [
                                        'hr_manager' => 'Gestor RH',
                                        'admin' => 'Admin',
                                        'common_user' => 'Usuário'
                                    ];
                                    ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $role_classes[$user['role']]; ?>">
                                        <?php echo $role_labels[$user['role']]; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Booking Modal (Admin can also book meetings) -->
    <div id="bookingModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Agendar Reunião (Admin)</h3>
                    <button onclick="hideBookingModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form id="bookingForm">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gestor de RH</label>
                            <select id="hrManager" name="hr_manager_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Selecione um gestor</option>
                                <?php 
                                $hr_managers = $user->getHRManagers();
                                foreach ($hr_managers as $hr): ?>
                                    <option value="<?php echo $hr['id']; ?>"><?php echo htmlspecialchars($hr['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Data</label>
                            <input type="date" id="appointmentDate" name="appointment_date" required 
                                   min="<?php echo date('Y-m-d'); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div id="timeSlotsContainer" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Horários Disponíveis</label>
                            <div id="timeSlots" class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                <!-- Time slots will be loaded here -->
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Descrição da Reunião</label>
                            <textarea name="description" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                      placeholder="Descreva brevemente o motivo da reunião..."></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="hideBookingModal()" 
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700">
                            Agendar Reunião
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Profile Modal -->
    <div id="profileModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Conta</h3>
                    <button onclick="hideProfileModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form id="profileForm">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nome</label>
                            <input type="text" id="name" name="name" required 
                                   value="<?php echo htmlspecialchars($user_info['name']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" id="email" name="email" required 
                                   value="<?php echo htmlspecialchars($user_info['email']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Senha</label>
                            <div class="relative">
                                <input type="password" id="password" name="password" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="Digite uma nova senha...">
                                <button type="button" onclick="togglePasswordVisibility()" 
                                        class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
                                    <i id="passwordVisibilityIcon" class="fas fa-eye-slash text-xl"></i>
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

    <script>
        let selectedTimeSlot = null;

        function showBookingModal() {
            document.getElementById('bookingModal').classList.remove('hidden');
        }

        function hideBookingModal() {
            document.getElementById('bookingModal').classList.add('hidden');
            document.getElementById('bookingForm').reset();
            document.getElementById('timeSlotsContainer').classList.add('hidden');
            selectedTimeSlot = null;
        }

        function showProfileModal() {
            document.getElementById('profileModal').classList.remove('hidden');
        }

        function hideProfileModal() {
            document.getElementById('profileModal').classList.add('hidden');
            document.getElementById('profileForm').reset();
        }

        // Load available time slots when date or HR manager changes
        function loadTimeSlots() {
            const hrManagerId = document.getElementById('hrManager').value;
            const appointmentDate = document.getElementById('appointmentDate').value;
            
            if (!hrManagerId || !appointmentDate) {
                document.getElementById('timeSlotsContainer').classList.add('hidden');
                return;
            }
            
            fetch('../api/get_available_slots.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    hr_manager_id: hrManagerId,
                    date: appointmentDate
                })
            })
            .then(response => response.json())
            .then(data => {
                const timeSlotsContainer = document.getElementById('timeSlotsContainer');
                const timeSlots = document.getElementById('timeSlots');
                
                if (data.success && data.slots.length > 0) {
                    timeSlots.innerHTML = '';
                    data.slots.forEach(slot => {
                        const slotButton = document.createElement('button');
                        slotButton.type = 'button';
                        slotButton.className = 'p-2 border border-gray-300 rounded-md text-sm hover:bg-indigo-50 hover:border-indigo-300 transition duration-200';
                        slotButton.textContent = slot.display_time;
                        slotButton.onclick = () => selectTimeSlot(slotButton, slot);
                        timeSlots.appendChild(slotButton);
                    });
                    timeSlotsContainer.classList.remove('hidden');
                } else {
                    timeSlots.innerHTML = '<p class="text-gray-500 text-sm col-span-full text-center py-4">Nenhum horário disponível para esta data.</p>';
                    timeSlotsContainer.classList.remove('hidden');
                }
            });
        }

        function selectTimeSlot(button, slot) {
            // Remove selection from other buttons
            document.querySelectorAll('#timeSlots button').forEach(btn => {
                btn.classList.remove('bg-indigo-600', 'text-white');
                btn.classList.add('border-gray-300');
            });
            
            // Select this button
            button.classList.add('bg-indigo-600', 'text-white');
            button.classList.remove('border-gray-300');
            
            selectedTimeSlot = slot;
        }

        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const passwordVisibilityIcon = document.getElementById('passwordVisibilityIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordVisibilityIcon.className = 'fas fa-eye text-xl';
            } else {
                passwordInput.type = 'password';
                passwordVisibilityIcon.className = 'fas fa-eye-slash text-xl';
            }
        }

        // Event listeners
        document.getElementById('hrManager').addEventListener('change', loadTimeSlots);
        document.getElementById('appointmentDate').addEventListener('change', loadTimeSlots);

        // Handle form submission
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!selectedTimeSlot) {
                alert('Por favor, selecione um horário.');
                return;
            }
            
            const formData = new FormData(this);
            formData.append('start_time', selectedTimeSlot.start_time);
            formData.append('end_time', selectedTimeSlot.end_time);
            
            fetch('../api/book_appointment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Reunião agendada com sucesso!');
                    location.reload();
                } else {
                    alert('Erro ao agendar reunião: ' + data.message);
                }
            });
        });

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
                    alert('Perfil atualizado com sucesso!');
                    location.reload();
                } else {
                    alert('Erro ao atualizar perfil: ' + data.message);
                }
            });
        });
    </script>
</body>
</html>
