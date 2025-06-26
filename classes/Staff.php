<?php
require_once 'config/database.php';

class Staff {
    private $conn;
    private $table_name = "staff_profiles";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET user_id=:user_id, full_name=:full_name, employee_id=:employee_id, 
                      department=:department, designation=:designation, contact_phone=:contact_phone, 
                      shift_preference=:shift_preference";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":user_id", $data['user_id']);
        $stmt->bindParam(":full_name", $data['full_name']);
        $stmt->bindParam(":employee_id", $data['employee_id']);
        $stmt->bindParam(":department", $data['department']);
        $stmt->bindParam(":designation", $data['designation']);
        $stmt->bindParam(":contact_phone", $data['contact_phone']);
        $stmt->bindParam(":shift_preference", $data['shift_preference']);

        return $stmt->execute();
    }

    public function readAll() {
        $query = "SELECT sp.*, u.username, u.email, u.role 
                  FROM " . $this->table_name . " sp
                  JOIN users u ON sp.user_id = u.id
                  WHERE sp.status = 'active'
                  ORDER BY sp.full_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne($id) {
        $query = "SELECT sp.*, u.username, u.email, u.role 
                  FROM " . $this->table_name . " sp
                  JOIN users u ON sp.user_id = u.id
                  WHERE sp.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET full_name=:full_name, department=:department, designation=:designation, 
                      contact_phone=:contact_phone, shift_preference=:shift_preference
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":full_name", $data['full_name']);
        $stmt->bindParam(":department", $data['department']);
        $stmt->bindParam(":designation", $data['designation']);
        $stmt->bindParam(":contact_phone", $data['contact_phone']);
        $stmt->bindParam(":shift_preference", $data['shift_preference']);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "UPDATE " . $this->table_name . " SET status='inactive' WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }
}
?>
