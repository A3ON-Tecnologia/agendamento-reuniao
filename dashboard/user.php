<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../classes/User.php';
require_once '../classes/Appointment.php';
require_once '../classes/AvailabilitySlot.php';

checkAuth();
$user_info = getUserInfo();

$database = new Database();
$db = $database->getConnection();
$appointment = new Appointment($db);
$availability = new AvailabilitySlot($db);
$user = new User($db);

// Get user's appointments
$user_appointments = $appointment->getAppointmentsByUser($user_info['id']);
$hr_managers = $user->getHRManagers();

// Get current month for calendar
$current_month = date('Y-m');
$current_date = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Reunião - Sistema de Agendamentos</title>
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
                        <h1 class="text-xl font-semibold text-gray-900">Agendar Reunião</h1>
                        <p class="text-sm text-gray-600">Bem-vindo, <?php echo htmlspecialchars($user_info['name']); ?></p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="showBookingModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-plus mr-2"></i>Nova Reunião
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
                                <dt class="text-sm font-medium text-gray-500 truncate">Próximas Reuniões</dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    <?php 
                                    $upcoming_count = 0;
                                    foreach($user_appointments as $apt) {
                                        if($apt['appointment_date'] >= $current_date && $apt['status'] == 'scheduled') {
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
                            <i class="fas fa-clock text-blue-600 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Reuniões Este Mês</dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    <?php 
                                    $month_count = 0;
                                    foreach($user_appointments as $apt) {
                                        if(substr($apt['appointment_date'], 0, 7) == $current_month) {
                                            $month_count++;
                                        }
                                    }
                                    echo $month_count;
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
                                <dd class="text-lg font-medium text-gray-900"><?php echo count($user_appointments); ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendar and Appointments -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Calendar -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-calendar mr-2"></i>Calendário
                    </h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <button onclick="previousMonth()" class="p-2 hover:bg-gray-100 rounded-lg">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <h4 id="currentMonth" class="text-lg font-semibold text-gray-900"></h4>
                        <button onclick="nextMonth()" class="p-2 hover:bg-gray-100 rounded-lg">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    <div class="grid grid-cols-7 gap-1 mb-2">
                        <div class="text-center text-xs font-medium text-gray-500 py-2">Dom</div>
                        <div class="text-center text-xs font-medium text-gray-500 py-2">Seg</div>
                        <div class="text-center text-xs font-medium text-gray-500 py-2">Ter</div>
                        <div class="text-center text-xs font-medium text-gray-500 py-2">Qua</div>
                        <div class="text-center text-xs font-medium text-gray-500 py-2">Qui</div>
                        <div class="text-center text-xs font-medium text-gray-500 py-2">Sex</div>
                        <div class="text-center text-xs font-medium text-gray-500 py-2">Sáb</div>
                    </div>
                    <div id="calendarGrid" class="grid grid-cols-7 gap-1">
                        <!-- Calendar days will be generated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- My Appointments -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-list mr-2"></i>Minhas Reuniões
                    </h3>
                </div>
                <div class="p-6">
                    <?php if (empty($user_appointments)): ?>
                        <div class="text-center py-8">
                            <i class="fas fa-calendar-times text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-600 mb-4">Você ainda não tem reuniões agendadas.</p>
                            <button onclick="showBookingModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200">
                                Agendar Primeira Reunião
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4 max-h-96 overflow-y-auto">
                            <?php foreach (array_slice($user_appointments, 0, 5) as $apt): ?>
                                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition duration-200">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center mb-2">
                                                <i class="fas fa-calendar text-indigo-600 mr-2"></i>
                                                <span class="font-medium text-gray-900">
                                                    <?php echo date('d/m/Y', strtotime($apt['appointment_date'])); ?>
                                                </span>
                                            </div>
                                            <div class="flex items-center mb-2">
                                                <i class="fas fa-clock text-gray-500 mr-2"></i>
                                                <span class="text-sm text-gray-600">
                                                    <?php echo date('H:i', strtotime($apt['start_time'])); ?> - 
                                                    <?php echo date('H:i', strtotime($apt['end_time'])); ?>
                                                </span>
                                            </div>
                                            <div class="flex items-center mb-2">
                                                <i class="fas fa-user text-gray-500 mr-2"></i>
                                                <span class="text-sm text-gray-600"><?php echo htmlspecialchars($apt['hr_manager_name']); ?></span>
                                            </div>
                                            <?php if ($apt['description']): ?>
                                                <div class="text-sm text-gray-600 mt-2">
                                                    <i class="fas fa-comment text-gray-400 mr-2"></i>
                                                    <?php echo htmlspecialchars($apt['description']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ml-4">
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
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $status_classes[$apt['status']]; ?>">
                                                <?php echo $status_labels[$apt['status']]; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Modal -->
    <div id="bookingModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Agendar Nova Reunião</h3>
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
                                <?php foreach ($hr_managers as $hr): ?>
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

    <script>
        let currentDate = new Date();
        let selectedTimeSlot = null;
        let availableDays = {}; // Store available days for current month

        function showBookingModal() {
            document.getElementById('bookingModal').classList.remove('hidden');
        }

        function hideBookingModal() {
            document.getElementById('bookingModal').classList.add('hidden');
            document.getElementById('bookingForm').reset();
            document.getElementById('timeSlotsContainer').classList.add('hidden');
            selectedTimeSlot = null;
        }

        // Load available days for the current month
        function loadAvailableDays() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            
            // Get all HR managers to check availability
            const hrManagers = <?php echo json_encode(array_column($hr_managers, 'id')); ?>;
            
            availableDays = {};
            
            // Check each HR manager for available days in this month
            hrManagers.forEach(hrManagerId => {
                for (let day = 1; day <= lastDay.getDate(); day++) {
                    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                    const dayDate = new Date(year, month, day);
                    
                    // Skip past dates
                    if (dayDate < new Date().setHours(0,0,0,0)) {
                        continue;
                    }
                    
                    fetch('../api/get_available_slots.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            hr_manager_id: hrManagerId,
                            date: dateStr
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.slots.length > 0) {
                            if (!availableDays[day]) {
                                availableDays[day] = true;
                                // Update the day element if calendar is already generated
                                const dayElement = document.querySelector(`[data-day="${day}"]`);
                                if (dayElement) {
                                    dayElement.classList.add('bg-green-100', 'text-green-800', 'font-semibold');
                                    dayElement.classList.remove('hover:bg-gray-100');
                                    dayElement.classList.add('hover:bg-green-200');
                                }
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error loading available days:', error);
                    });
                }
            });
        }

        function generateCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            
            // Update month display
            const monthNames = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
                              'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
            document.getElementById('currentMonth').textContent = `${monthNames[month]} ${year}`;
            
            // Get first day of month and number of days
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            
            const calendarGrid = document.getElementById('calendarGrid');
            calendarGrid.innerHTML = '';
            
            // Add empty cells for days before month starts
            for (let i = 0; i < firstDay; i++) {
                const emptyDay = document.createElement('div');
                emptyDay.className = 'h-8';
                calendarGrid.appendChild(emptyDay);
            }
            
            // Add days of the month
            for (let day = 1; day <= daysInMonth; day++) {
                const dayElement = document.createElement('div');
                dayElement.className = 'h-8 flex items-center justify-center text-sm cursor-pointer hover:bg-gray-100 rounded relative';
                dayElement.textContent = day;
                dayElement.setAttribute('data-day', day);
                
                const dayDate = new Date(year, month, day);
                const today = new Date();
                
                if (dayDate < today) {
                    dayElement.className += ' text-gray-400 cursor-not-allowed';
                    dayElement.classList.remove('hover:bg-gray-100');
                } else {
                    dayElement.onclick = () => selectDate(year, month, day);
                    
                    // Check if this day has available slots
                    if (availableDays[day]) {
                        dayElement.classList.add('bg-green-100', 'text-green-800', 'font-semibold');
                        dayElement.classList.remove('hover:bg-gray-100');
                        dayElement.classList.add('hover:bg-green-200');
                    }
                }
                
                calendarGrid.appendChild(dayElement);
            }
            
            // Load available days for this month
            loadAvailableDays();
        }

        function selectDate(year, month, day) {
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            document.getElementById('appointmentDate').value = dateStr;
            showBookingModal();
        }

        function previousMonth() {
            currentDate.setMonth(currentDate.getMonth() - 1);
            availableDays = {}; // Reset available days
            generateCalendar();
        }

        function nextMonth() {
            currentDate.setMonth(currentDate.getMonth() + 1);
            availableDays = {}; // Reset available days
            generateCalendar();
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

        // Initialize calendar
        generateCalendar();
    </script>
</body>
</html>
