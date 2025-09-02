<?php
require_once '../config/database.php';

class AvailabilitySlot {
    private $conn;
    private $table_name = "availability_slots";
    private $specific_table_name = "specific_availability_slots";

    public $id;
    public $hr_manager_id;
    public $day_of_week;
    public $specific_date;
    public $start_time;
    public $end_time;
    public $slot_duration;
    public $is_active;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (hr_manager_id, day_of_week, start_time, end_time, slot_duration, is_active) 
                  VALUES (:hr_manager_id, :day_of_week, :start_time, :end_time, :slot_duration, :is_active)";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':hr_manager_id', $this->hr_manager_id);
        $stmt->bindParam(':day_of_week', $this->day_of_week);
        $stmt->bindParam(':start_time', $this->start_time);
        $stmt->bindParam(':end_time', $this->end_time);
        $stmt->bindParam(':slot_duration', $this->slot_duration);
        $stmt->bindParam(':is_active', $this->is_active);

        return $stmt->execute();
    }

    public function getAvailabilityByHRManager($hr_manager_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE hr_manager_id = :hr_manager_id AND is_active = 1 
                  ORDER BY day_of_week, start_time";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hr_manager_id', $hr_manager_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAvailabilityByDay($hr_manager_id, $day_of_week) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE hr_manager_id = :hr_manager_id 
                  AND day_of_week = :day_of_week 
                  AND is_active = 1 
                  ORDER BY start_time";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hr_manager_id', $hr_manager_id);
        $stmt->bindParam(':day_of_week', $day_of_week);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET day_of_week = :day_of_week, 
                      start_time = :start_time, 
                      end_time = :end_time, 
                      slot_duration = :slot_duration, 
                      is_active = :is_active 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':day_of_week', $this->day_of_week);
        $stmt->bindParam(':start_time', $this->start_time);
        $stmt->bindParam(':end_time', $this->end_time);
        $stmt->bindParam(':slot_duration', $this->slot_duration);
        $stmt->bindParam(':is_active', $this->is_active);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    // Methods for specific date availability
    public function createSpecificAvailability() {
        $query = "INSERT INTO " . $this->specific_table_name . " 
                  (hr_manager_id, specific_date, start_time, end_time, slot_duration, is_active) 
                  VALUES (:hr_manager_id, :specific_date, :start_time, :end_time, :slot_duration, :is_active)";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':hr_manager_id', $this->hr_manager_id);
        $stmt->bindParam(':specific_date', $this->specific_date);
        $stmt->bindParam(':start_time', $this->start_time);
        $stmt->bindParam(':end_time', $this->end_time);
        $stmt->bindParam(':slot_duration', $this->slot_duration);
        $stmt->bindParam(':is_active', $this->is_active);

        return $stmt->execute();
    }

    public function getSpecificAvailabilityByHRManager($hr_manager_id) {
        $query = "SELECT * FROM " . $this->specific_table_name . " 
                  WHERE hr_manager_id = :hr_manager_id AND is_active = 1 
                  ORDER BY specific_date, start_time";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hr_manager_id', $hr_manager_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSpecificAvailabilityByDate($hr_manager_id, $specific_date) {
        $query = "SELECT * FROM " . $this->specific_table_name . " 
                  WHERE hr_manager_id = :hr_manager_id 
                  AND specific_date = :specific_date 
                  AND is_active = 1 
                  ORDER BY start_time";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hr_manager_id', $hr_manager_id);
        $stmt->bindParam(':specific_date', $specific_date);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteSpecificAvailability($hr_manager_id, $specific_date) {
        $query = "DELETE FROM " . $this->specific_table_name . " 
                  WHERE hr_manager_id = :hr_manager_id AND specific_date = :specific_date";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hr_manager_id', $hr_manager_id);
        $stmt->bindParam(':specific_date', $specific_date);
        
        return $stmt->execute();
    }

    public function generateTimeSlots($hr_manager_id, $date) {
        // First check for specific date availability
        $specific_availability = $this->getSpecificAvailabilityByDate($hr_manager_id, $date);
        
        if (!empty($specific_availability)) {
            // Use specific date availability
            $slots = [];
            foreach ($specific_availability as $avail) {
                $start = new DateTime($date . ' ' . $avail['start_time']);
                $end = new DateTime($date . ' ' . $avail['end_time']);
                $duration = $avail['slot_duration'];

                while ($start < $end) {
                    $slot_end = clone $start;
                    $slot_end->add(new DateInterval('PT' . $duration . 'M'));
                    
                    if ($slot_end <= $end) {
                        $slots[] = [
                            'start_time' => $start->format('H:i:s'),
                            'end_time' => $slot_end->format('H:i:s'),
                            'display_time' => $start->format('H:i') . ' - ' . $slot_end->format('H:i')
                        ];
                    }
                    
                    $start->add(new DateInterval('PT' . $duration . 'M'));
                }
            }
            return $slots;
        }
        
        // Fallback to day of week availability
        $day_of_week = date('w', strtotime($date));
        $availability = $this->getAvailabilityByDay($hr_manager_id, $day_of_week);
        
        if (empty($availability)) {
            return [];
        }

        $slots = [];
        foreach ($availability as $avail) {
            $start = new DateTime($date . ' ' . $avail['start_time']);
            $end = new DateTime($date . ' ' . $avail['end_time']);
            $duration = $avail['slot_duration'];

            while ($start < $end) {
                $slot_end = clone $start;
                $slot_end->add(new DateInterval('PT' . $duration . 'M'));
                
                if ($slot_end <= $end) {
                    $slots[] = [
                        'start_time' => $start->format('H:i:s'),
                        'end_time' => $slot_end->format('H:i:s'),
                        'display_time' => $start->format('H:i') . ' - ' . $slot_end->format('H:i')
                    ];
                }
                
                $start->add(new DateInterval('PT' . $duration . 'M'));
            }
        }

        return $slots;
    }
}
?>
