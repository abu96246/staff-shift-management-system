<?php
require_once 'config/database.php';

class Availability {
    private $conn;
    private $table_name = "staff_availability";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET staff_id=:staff_id, available_date=:available_date, 
                      status=:status, shift_preference=:shift_preference, notes=:notes
                  ON DUPLICATE KEY UPDATE 
                      status=:status, shift_preference=:shift_preference, notes=:notes";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":staff_id", $data['staff_id']);
        $stmt->bindParam(":available_date", $data['available_date']);
        $stmt->bindParam(":status", $data['status']);
        $stmt->bindParam(":shift_preference", $data['shift_preference']);
        $stmt->bindParam(":notes", $data['notes']);

        return $stmt->execute();
    }

    public function getStaffAvailability($staff_id, $start_date, $end_date) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE staff_id = ? AND available_date BETWEEN ? AND ?
                  ORDER BY available_date";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $staff_id);
        $stmt->bindParam(2, $start_date);
        $stmt->bindParam(3, $end_date);
        $stmt->execute();
        return $stmt;
    }

    public function getAllAvailability($start_date, $end_date) {
        $query = "SELECT sa.*, sp.full_name, sp.employee_id, sp.designation
                  FROM " . $this->table_name . " sa
                  JOIN staff_profiles sp ON sa.staff_id = sp.id
                  WHERE sa.available_date BETWEEN ? AND ?
                  ORDER BY sa.available_date, sp.full_name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $start_date);
        $stmt->bindParam(2, $end_date);
        $stmt->execute();
        return $stmt;
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }
}
?>
