<?php
require_once 'config/database.php';

class Shift {
    private $conn;
    private $table_name = "shifts";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET shift_name=:shift_name, shift_type=:shift_type, start_time=:start_time, 
                      end_time=:end_time, description=:description, max_staff=:max_staff";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":shift_name", $data['shift_name']);
        $stmt->bindParam(":shift_type", $data['shift_type']);
        $stmt->bindParam(":start_time", $data['start_time']);
        $stmt->bindParam(":end_time", $data['end_time']);
        $stmt->bindParam(":description", $data['description']);
        $stmt->bindParam(":max_staff", $data['max_staff']);

        return $stmt->execute();
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY start_time";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET shift_name=:shift_name, shift_type=:shift_type, start_time=:start_time, 
                      end_time=:end_time, description=:description, max_staff=:max_staff
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":shift_name", $data['shift_name']);
        $stmt->bindParam(":shift_type", $data['shift_type']);
        $stmt->bindParam(":start_time", $data['start_time']);
        $stmt->bindParam(":end_time", $data['end_time']);
        $stmt->bindParam(":description", $data['description']);
        $stmt->bindParam(":max_staff", $data['max_staff']);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }
}
?>
