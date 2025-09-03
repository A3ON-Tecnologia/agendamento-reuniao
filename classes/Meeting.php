<?php
require_once '../config/users_database.php';

class Meeting {
    private $conn;
    private $table_name = "reunioes";
    private $participants_table = "reuniao_participantes";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Criar nova reunião
    public function createMeeting($date, $start_time, $end_time, $subject, $description, $participants = []) {
        try {
            $this->conn->beginTransaction();
            
            // Inserir reunião
            $query = "INSERT INTO " . $this->table_name . " 
                     (data_reuniao, hora_inicio, hora_fim, assunto, descricao, status, data_criacao) 
                     VALUES (:date, :start_time, :end_time, :subject, :description, 'agendada', NOW())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':start_time', $start_time);
            $stmt->bindParam(':end_time', $end_time);
            $stmt->bindParam(':subject', $subject);
            $stmt->bindParam(':description', $description);
            
            $stmt->execute();
            $meeting_id = $this->conn->lastInsertId();
            
            // Inserir participantes
            if (!empty($participants)) {
                $participant_query = "INSERT INTO " . $this->participants_table . " 
                                    (reuniao_id, id_usuario, username_usuario, status_participacao, data_criacao) 
                                    VALUES (:meeting_id, :user_id, :username, 'confirmado', NOW())";
                
                $participant_stmt = $this->conn->prepare($participant_query);
                
                foreach ($participants as $participant) {
                    $participant_stmt->bindParam(':meeting_id', $meeting_id);
                    $participant_stmt->bindParam(':user_id', $participant['id']);
                    $participant_stmt->bindParam(':username', $participant['username']);
                    $participant_stmt->execute();
                }
            }
            
            $this->conn->commit();
            return $meeting_id;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    // Buscar reuniões por criador
    public function getMeetingsByCreator($creator_id) {
        $query = "SELECT r.*, u.name as creator_name 
                  FROM " . $this->table_name . " r 
                  JOIN users u ON r.id = u.id 
                  WHERE r.id = :creator_id 
                  ORDER BY r.data_reuniao DESC, r.hora_inicio DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':creator_id', $creator_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar participantes de uma reunião
    public function getMeetingParticipants($meeting_id) {
        $query = "SELECT rp.*, u.name as participant_name 
                  FROM " . $this->participants_table . " rp 
                  JOIN users u ON rp.usuario_id = u.id 
                  WHERE rp.reuniao_id = :meeting_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':meeting_id', $meeting_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar todas as reuniões (para admin)
    public function getAllMeetings() {
        $query = "SELECT r.*, u.name as creator_name,
                         COUNT(rp.id) as participant_count
                  FROM " . $this->table_name . " r 
                  JOIN users u ON r.id = u.id 
                  LEFT JOIN " . $this->participants_table . " rp ON r.id = rp.reuniao_id
                  GROUP BY r.id
                  ORDER BY r.data_reuniao DESC, r.hora_inicio DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
