<?php
class UsersDatabase {
    private $host = 'localhost';
    private $db_name = 'cadastro_empresas'; // Schema onde está a tabela users
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            echo "Users Database Connection error: " . $exception->getMessage();
        }
        
        return $this->conn;
    }

    // Método específico para buscar todos os usuários
    public function getAllUsers() {
        try {
            $conn = $this->getConnection();
            $query = "SELECT id, name, email, role FROM users ORDER BY name";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $exception) {
            echo "Error fetching users: " . $exception->getMessage();
            return [];
        }
    }

    // Método para buscar usuário por ID
    public function getUserById($id) {
        try {
            $conn = $this->getConnection();
            $query = "SELECT id, name, email, role FROM users WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch();
        } catch(PDOException $exception) {
            echo "Error fetching user: " . $exception->getMessage();
            return null;
        }
    }

    // Método para buscar usuários por role
    public function getUsersByRole($role) {
        try {
            $conn = $this->getConnection();
            $query = "SELECT id, name, email, role FROM users WHERE role = :role ORDER BY name";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':role', $role);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $exception) {
            echo "Error fetching users by role: " . $exception->getMessage();
            return [];
        }
    }

    // Método para validar login (se necessário)
    public function validateUser($email, $password) {
        try {
            $conn = $this->getConnection();
            $query = "SELECT id, name, email, password, role FROM users WHERE email = :email";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['password'])) {
                unset($user['password']); // Remove password from return
                return $user;
            }
            return null;
        } catch(PDOException $exception) {
            echo "Error validating user: " . $exception->getMessage();
            return null;
        }
    }
}
?>
