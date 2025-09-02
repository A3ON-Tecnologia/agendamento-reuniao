<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../classes/User.php';
require_once '../classes/Appointment.php';
require_once '../classes/AvailabilitySlot.php';

checkRole('hr_manager');
$user_info = getUserInfo();

$database = new Database();
$db = $database->getConnection();
$appointment = new Appointment($db);
$availability = new AvailabilitySlot($db);

// Get appointments for this HR manager
$appointments = $appointment->getAppointmentsByHRManager($user_info['id']);
$availability_slots = $availability->getAvailabilityByHRManager($user_info['id']);

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
                        <i class="fas fa-calendar-alt text-indigo-600 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-xl font-semibold text-gray-900">Dashboard RH</h1>
                        <p class="text-sm text-gray-600">Bem-vindo, <?php echo htmlspecialchars($user_info['name']); ?></p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="showSpecificDateModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200 text-sm">
                        <i class="fas fa-calendar-plus mr-2"></i>Configurar Horários
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
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
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
                                <dt class="text-sm font-medium text-gray-500 truncate">Próximas Reuniões</dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    <?php 
                                    $upcoming_count = 0;
                                    foreach($appointments as $apt) {
                                        if($apt['appointment_date'] >= date('Y-m-d') && $apt['status'] == 'scheduled') {
                                            $upcoming_count++;
                                        }
                                    }
                                    echo $upcoming_count;
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
                            <i class="fas fa-history text-purple-600 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total de Reuniões</dt>
                                <dd class="text-lg font-medium text-gray-900"><?php echo count($appointments); ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Availability -->
        <div class="bg-white shadow rounded-lg mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-clock mr-2"></i>Horários Disponíveis Configurados
                    </h3>
                    <div class="flex space-x-2">
                        <button onclick="showSpecificDateModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200 text-sm">
                            <i class="fas fa-calendar-plus mr-2"></i>Configurar Horários
                        </button>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <!-- Specific dates availability -->
                <div id="specificAvailabilityList">
                    <!-- Specific availability will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Recent Appointments -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-calendar-alt mr-2"></i>Reuniões Recentes
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data/Hora</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participante</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($appointments)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    <i class="fas fa-calendar-times text-4xl mb-2 block"></i>
                                    Nenhuma reunião agendada ainda.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach (array_slice($appointments, 0, 10) as $apt): ?>
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
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($apt['user_name']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($apt['user_email']); ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 max-w-xs truncate">
                                            <?php echo htmlspecialchars($apt['description'] ?: 'Sem descrição'); ?>
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <?php if ($apt['status'] == 'scheduled'): ?>
                                            <button onclick="updateAppointmentStatus(<?php echo $apt['id']; ?>, 'completed')" 
                                                    class="text-green-600 hover:text-green-900 mr-3">
                                                <i class="fas fa-check"></i> Concluir
                                            </button>
                                            <button onclick="updateAppointmentStatus(<?php echo $apt['id']; ?>, 'cancelled')" 
                                                    class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-times"></i> Cancelar
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Specific Date Availability Modal -->
    <div id="specificDateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Configurar Horários para Data Específica</h3>
                    <button onclick="hideSpecificDateModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form id="specificDateForm">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Data</label>
                            <input type="date" id="specificDate" name="specific_date" required 
                                   min="<?php echo date('Y-m-d'); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div id="timeSlotsList" class="space-y-3">
                            <div class="flex justify-between items-center">
                                <label class="block text-sm font-medium text-gray-700">Horários Disponíveis</label>
                                <button type="button" onclick="addTimeSlot()" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                                    <i class="fas fa-plus mr-1"></i>Adicionar Horário
                                </button>
                            </div>
                            <div class="time-slot-item border border-gray-200 rounded-lg p-3">
                                <div class="grid grid-cols-4 gap-3 items-end">
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Início</label>
                                        <input type="time" name="start_time[]" required 
                                               class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Fim</label>
                                        <input type="time" name="end_time[]" required 
                                               class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Duração (min)</label>
                                        <select name="duration[]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                            <option value="30">30 min</option>
                                            <option value="60" selected>60 min</option>
                                            <option value="90">90 min</option>
                                            <option value="120">120 min</option>
                                        </select>
                                    </div>
                                    <div>
                                        <button type="button" onclick="removeTimeSlot(this)" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="hideSpecificDateModal()" 
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-green-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700">
                            Salvar Horários
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

    <script>
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
                        alert('Erro ao atualizar status da reunião');
                    }
                });
            }
        }

        function editSpecificDate(date) {
            // Load existing data for this date
            fetch('../api/get_specific_availability.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.availability) {
                        const dateSlots = data.availability.filter(slot => slot.specific_date === date);
                        if (dateSlots.length > 0) {
                            // Populate modal with existing data
                            document.getElementById('specificDate').value = date;
                            
                            // Clear existing time slots
                            const timeSlotsList = document.getElementById('timeSlotsList');
                            const existingSlots = timeSlotsList.querySelectorAll('.time-slot-item');
                            existingSlots.forEach((slot, index) => {
                                if (index > 0) slot.remove(); // Keep first slot, remove others
                            });
                            
                            // Populate first slot
                            const firstSlot = timeSlotsList.querySelector('.time-slot-item');
                            if (dateSlots[0]) {
                                firstSlot.querySelector('input[name="start_time[]"]').value = dateSlots[0].start_time.substring(0,5);
                                firstSlot.querySelector('input[name="end_time[]"]').value = dateSlots[0].end_time.substring(0,5);
                                firstSlot.querySelector('select[name="duration[]"]').value = dateSlots[0].slot_duration;
                            }
                            
                            // Add additional slots if needed
                            for (let i = 1; i < dateSlots.length; i++) {
                                addTimeSlot();
                                const newSlot = timeSlotsList.lastElementChild;
                                newSlot.querySelector('input[name="start_time[]"]').value = dateSlots[i].start_time.substring(0,5);
                                newSlot.querySelector('input[name="end_time[]"]').value = dateSlots[i].end_time.substring(0,5);
                                newSlot.querySelector('select[name="duration[]"]').value = dateSlots[i].slot_duration;
                            }
                            
                            showSpecificDateModal();
                        }
                    }
                });
        }

        function deleteSpecificDate(date) {
            if (confirm('Tem certeza que deseja remover os horários desta data?')) {
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
                        alert('Horários removidos com sucesso!');
                        loadSpecificAvailability();
                    } else {
                        alert('Erro ao remover horários: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erro ao remover horários. Tente novamente.');
                });
            }
        }

        function loadSpecificAvailability() {
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
                                            <button onclick="deleteSpecificDate('${date}')" class="text-red-600 hover:text-red-800 text-sm" title="Excluir">
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
                    alert('Erro ao salvar configurações');
                }
            });
        });

        // Handle specific date form submission
        document.getElementById('specificDateForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../api/save_specific_availability.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    hideSpecificDateModal();
                    // Clear form
                    document.getElementById('specificDateForm').reset();
                    // Show success message
                    alert('Horário disponível cadastrado com sucesso!');
                    // Reload the list to show the new availability
                    loadSpecificAvailability();
                } else {
                    alert('Erro ao salvar horários: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao salvar horários. Tente novamente.');
            });
        });

        function showSpecificDateModal() {
            document.getElementById('specificDateModal').classList.remove('hidden');
        }

        function hideSpecificDateModal() {
            document.getElementById('specificDateModal').classList.add('hidden');
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

        // Load specific availability on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadSpecificAvailability();
        });

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
                    alert('Perfil atualizado com sucesso!');
                    hideProfileModal();
                    location.reload();
                } else {
                    alert('Erro ao atualizar perfil: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao atualizar perfil. Tente novamente.');
            });
        });
    </script>
</body>
</html>
