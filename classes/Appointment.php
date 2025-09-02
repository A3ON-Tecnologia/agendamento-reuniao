<?php
require_once '../config/database.php';

class Appointment {
    private $conn;
    private $table_name = "appointments";

    public $id;
    public $hr_manager_id;
    public $user_id;
    public $appointment_date;
    public $start_time;
    public $end_time;
    public $description;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (hr_manager_id, user_id, appointment_date, start_time, end_time, description, status) 
                  VALUES (:hr_manager_id, :user_id, :appointment_date, :start_time, :end_time, :description, :status)";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':hr_manager_id', $this->hr_manager_id);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':appointment_date', $this->appointment_date);
        $stmt->bindParam(':start_time', $this->start_time);
        $stmt->bindParam(':end_time', $this->end_time);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':status', $this->status);

        return $stmt->execute();
    }

    public function getAppointmentsByHRManager($hr_manager_id) {
        $query = "SELECT a.*, u.name as user_name, u.email as user_email 
                  FROM " . $this->table_name . " a 
                  JOIN users u ON a.user_id = u.id 
                  WHERE a.hr_manager_id = :hr_manager_id 
                  ORDER BY a.appointment_date DESC, a.start_time DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hr_manager_id', $hr_manager_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAppointmentsByUser($user_id) {
        $query = "SELECT a.*, u.name as hr_manager_name 
                  FROM " . $this->table_name . " a 
                  JOIN users u ON a.hr_manager_id = u.id 
                  WHERE a.user_id = :user_id 
                  ORDER BY a.appointment_date DESC, a.start_time DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAppointmentsForAdmin() {
        $query = "SELECT a.appointment_date, a.start_time, a.end_time, a.status,
                         u1.name as user_name, u2.name as hr_manager_name
                  FROM " . $this->table_name . " a 
                  JOIN users u1 ON a.user_id = u1.id 
                  JOIN users u2 ON a.hr_manager_id = u2.id 
                  ORDER BY a.appointment_date DESC, a.start_time DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBookedSlots($hr_manager_id, $date) {
        $query = "SELECT start_time, end_time FROM " . $this->table_name . " 
                  WHERE hr_manager_id = :hr_manager_id 
                  AND appointment_date = :date 
                  AND status != 'cancelled'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hr_manager_id', $hr_manager_id);
        $stmt->bindParam(':date', $date);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
}
?>
