<?php
// require_once '../config/database.php'; // Removido para evitar conflito quando chamado de diferentes diretórios

class Meeting {
    private $conn;
    private $table_name = "reunioes";
    private $participants_table = "reuniao_participantes";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Verificar conflito de horários
    public function checkTimeConflict($date, $start_time, $end_time, $exclude_meeting_id = null) {
        $query = "SELECT id, assunto, hora_inicio, hora_fim 
                 FROM " . $this->table_name . " 
                 WHERE data_reuniao = :date 
                 AND status != 'cancelada'
                 AND (
                     (hora_inicio < :end_time AND hora_fim > :start_time)
                 )";
        
        if ($exclude_meeting_id) {
            $query .= " AND id != :exclude_meeting_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':start_time', $start_time);
        $stmt->bindParam(':end_time', $end_time);
        
        if ($exclude_meeting_id) {
            $stmt->bindParam(':exclude_meeting_id', $exclude_meeting_id);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Criar nova reunião
    public function createMeeting($date, $start_time, $end_time, $subject, $description, $participants = []) {
        try {
            // Verificar se a data/hora não está no passado
            // Definir timezone para São Paulo (UTC-3)
            $timezone = new DateTimeZone('America/Sao_Paulo');
            $meeting_datetime = DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $start_time, $timezone);
            $current_datetime = new DateTime('now', $timezone);
            
            // Debug: mostrar datetime atual do sistema
            error_log("DEBUG - DateTime atual do sistema (SP): " . $current_datetime->format('Y-m-d H:i:s'));
            error_log("DEBUG - DateTime da reunião (SP): " . $meeting_datetime->format('Y-m-d H:i:s'));
            error_log("DEBUG - Comparação: reunião < atual? " . ($meeting_datetime < $current_datetime ? 'SIM' : 'NÃO'));
            
            if ($meeting_datetime < $current_datetime) {
                throw new Exception("Não é possível agendar reunião no passado!\nData/Hora atual: " . $current_datetime->format('d/m/Y H:i') . "\nData/Hora da reunião: " . $meeting_datetime->format('d/m/Y H:i'));
            }
            
            // Verificar conflitos de horário
            $conflicts = $this->checkTimeConflict($date, $start_time, $end_time);
            if (!empty($conflicts)) {
                $conflict_info = $conflicts[0];
                throw new Exception("Conflito de horário detectado! Já existe uma reunião agendada:\n" .
                                  "Assunto: " . $conflict_info['assunto'] . "\n" .
                                  "Horário: " . substr($conflict_info['hora_inicio'], 0, 5) . " - " . substr($conflict_info['hora_fim'], 0, 5));
            }
            
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

    // Buscar todas as reuniões para exibir no calendário
    public function getAllMeetings() {
        $query = "SELECT id, data_reuniao, hora_inicio, hora_fim, assunto, descricao, status 
                 FROM " . $this->table_name . " 
                 WHERE status != 'cancelada'
                 ORDER BY data_reuniao, hora_inicio";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                  JOIN users u ON rp.id_usuario = u.id 
                  WHERE rp.reuniao_id = :meeting_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':meeting_id', $meeting_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Atualizar reunião existente
    public function updateMeeting($meeting_id, $date, $start_time, $end_time, $subject, $description, $status, $participants = []) {
        try {
            // Verificar se a data/hora não está no passado (apenas para reuniões não concluídas)
            if ($status !== 'concluida' && $status !== 'cancelada') {
                $meeting_datetime = DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $start_time);
                $current_datetime = new DateTime();
                
                if ($meeting_datetime <= $current_datetime) {
                    throw new Exception("Não é possível agendar reunião no passado!\nData/Hora atual: " . $current_datetime->format('d/m/Y H:i') . "\nData/Hora da reunião: " . $meeting_datetime->format('d/m/Y H:i'));
                }
            }
            
            // Verificar conflitos de horário (excluindo a própria reunião)
            $conflicts = $this->checkTimeConflict($date, $start_time, $end_time, $meeting_id);
            if (!empty($conflicts)) {
                $conflict_info = $conflicts[0];
                throw new Exception("Conflito de horário detectado! Já existe uma reunião agendada:\n" .
                                  "Assunto: " . $conflict_info['assunto'] . "\n" .
                                  "Horário: " . substr($conflict_info['hora_inicio'], 0, 5) . " - " . substr($conflict_info['hora_fim'], 0, 5));
            }
            
            $this->conn->beginTransaction();
            
            // Atualizar dados da reunião
            $query = "UPDATE " . $this->table_name . " 
                     SET data_reuniao = :date, 
                         hora_inicio = :start_time, 
                         hora_fim = :end_time, 
                         assunto = :subject, 
                         descricao = :description, 
                         status = :status,
                         data_atualizacao = NOW()
                     WHERE id = :meeting_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':meeting_id', $meeting_id);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':start_time', $start_time);
            $stmt->bindParam(':end_time', $end_time);
            $stmt->bindParam(':subject', $subject);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':status', $status);
            
            $stmt->execute();
            
            // Remover participantes existentes
            $delete_query = "DELETE FROM " . $this->participants_table . " WHERE reuniao_id = :meeting_id";
            $delete_stmt = $this->conn->prepare($delete_query);
            $delete_stmt->bindParam(':meeting_id', $meeting_id);
            $delete_stmt->execute();
            
            // Inserir novos participantes
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
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    // Excluir reunião
    public function deleteMeeting($meeting_id) {
        try {
            $this->conn->beginTransaction();
            
            // Remover participantes
            $delete_participants = "DELETE FROM " . $this->participants_table . " WHERE reuniao_id = :meeting_id";
            $stmt1 = $this->conn->prepare($delete_participants);
            $stmt1->bindParam(':meeting_id', $meeting_id);
            $stmt1->execute();
            
            // Remover reunião
            $delete_meeting = "DELETE FROM " . $this->table_name . " WHERE id = :meeting_id";
            $stmt2 = $this->conn->prepare($delete_meeting);
            $stmt2->bindParam(':meeting_id', $meeting_id);
            $stmt2->execute();
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    // Buscar todas as reuniões (para admin)
    public function getAllMeetingsAdmin() {
        $query = "SELECT r.*, COUNT(rp.id) as participant_count
                  FROM " . $this->table_name . " r 
                  LEFT JOIN " . $this->participants_table . " rp ON r.id = rp.reuniao_id
                  GROUP BY r.id
                  ORDER BY r.data_reuniao DESC, r.hora_inicio DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
