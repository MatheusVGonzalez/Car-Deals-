<?php
namespace CarDeals;

class User {
    private $conn;
    private $table = 'users';
    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function create($data) {
        try {
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $data['email']);
            $stmt->execute();
            if($stmt->get_result()->num_rows > 0) {
                throw new \Exception("Email already exists");
            }
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $stmt = $this->conn->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
            
            $stmt->bind_param("ssss", 
                $data['name'],
                $data['email'],
                $hashedPassword,
                $data['role']
            );

            if($stmt->execute()) {
                $id = $this->conn->insert_id;
                \CarDeals\Audit::log(
                    $this->conn, 
                    $id,
                    'create',
                    'users',
                    $id,
                    "Created user {$data['name']} with role {$data['role']}"
                );
                return $id;
            }
            return false;
        } catch (\Exception $e) {
            error_log("Error creating user: " . $e->getMessage());
            throw new \Exception("Error creating user: " . $e->getMessage());
        }
    }   

    public function authenticate($email, $password) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if(password_verify($password, $user['password'])) {
                    return $user;
                }
            }
            return false;
        } catch (\Exception $e) {
            error_log("Authentication error: " . $e->getMessage());
            throw new \Exception("Authentication failed");
        }
    }

    public function getAll() {
        try {
            return $this->conn->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
        } catch (\Exception $e) {
            error_log("Error getting users: " . $e->getMessage());
            throw new \Exception("Error retrieving users list");
        }
    }

    public function getById($id) {
        try {
            $stmt = $this->conn->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } catch (\Exception $e) {
            error_log("Error getting user: " . $e->getMessage());
            throw new \Exception("Error retrieving user data");
        }
    }

    public function update($id, $data) {
        try {
            $updates = [];
            $types = "";
            $values = [];

            if(isset($data['name'])) {
                $updates[] = "name = ?";
                $types .= "s";
                $values[] = $data['name'];
            }

            if(isset($data['email'])) {
                $updates[] = "email = ?";
                $types .= "s";
                $values[] = $data['email'];
            }

            if(isset($data['password'])) {
                $updates[] = "password = ?";
                $types .= "s";
                $values[] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            if(isset($data['role'])) {
                $updates[] = "role = ?";
                $types .= "s";
                $values[] = $data['role'];
            }

            $values[] = $id;
            $types .= "i";

            $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param($types, ...$values);
            
            if($stmt->execute()) {
                \CarDeals\Audit::log(
                    $this->conn, 
                    $data['user_id'], 
                    'update', 
                    'users', 
                    $id, 
                    "Updated user information"
                );
                return true;
            }
            return false;
        } catch (\Exception $e) {
            error_log("Error updating user: " . $e->getMessage());
            throw new \Exception("Error updating user");
        }
    }

   public function delete($id, $userId) {
        $user = $this->getById($id);
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if($stmt->execute()) {
            Audit::log($this->conn, $userId, 'delete', 'users', $id, "Deleted user {$user['name']} {$user['email']}");
            return true;
        }
        return false;
    }
}
?>