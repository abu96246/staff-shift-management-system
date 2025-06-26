<?php
require_once 'config/database.php';

class Notification {
    private $conn;
    private $table_name = "notifications";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET user_id=:user_id, title=:title, message=:message, type=:type";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":user_id", $data['user_id']);
        $stmt->bindParam(":title", $data['title']);
        $stmt->bindParam(":message", $data['message']);
        $stmt->bindParam(":type", $data['type']);

        return $stmt->execute();
    }

    public function getUserNotifications($user_id, $limit = 50) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = ? 
                  ORDER BY created_at DESC 
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->bindParam(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    public function getUnreadCount($user_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE user_id = ? AND is_read = FALSE";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    public function markAsRead($id, $user_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET is_read = TRUE 
                  WHERE id = ? AND user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->bindParam(2, $user_id);
        return $stmt->execute();
    }

    public function markAllAsRead($user_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET is_read = TRUE 
                  WHERE user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        return $stmt->execute();
    }

    public function delete($id, $user_id) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE id = ? AND user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->bindParam(2, $user_id);
        return $stmt->execute();
    }

    public function broadcastToRole($role, $title, $message, $type = 'info') {
        $query = "INSERT INTO " . $this->table_name . " (user_id, title, message, type)
                  SELECT u.id, :title, :message, :type
                  FROM users u
                  WHERE u.role = :role";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":message", $message);
        $stmt->bindParam(":type", $type);
        $stmt->bindParam(":role", $role);
        
        return $stmt->execute();
    }
}
?>
