<?php
require_once 'config/database.php';

class ShiftAssignment {
    private $conn;
    private $table_name = "shift_assignments";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET shift_id=:shift_id, staff_id=:staff_id, assignment_date=:assignment_date, 
                      status=:status, notes=:notes";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":shift_id", $data['shift_id']);
        $stmt->bindParam(":staff_id", $data['staff_id']);
        $stmt->bindParam(":assignment_date", $data['assignment_date']);
        $stmt->bindParam(":status", $data['status']);
        $stmt->bindParam(":notes", $data['notes']);

        return $stmt->execute();
    }

    public function getTodayAssignments() {
        $today = date('Y-m-d');
        $query = "SELECT sa.*, s.shift_name, s.shift_type, s.start_time, s.end_time,
                         sp.full_name, sp.employee_id, sp.designation
                  FROM " . $this->table_name . " sa
                  JOIN shifts s ON sa.shift_id = s.id
                  JOIN staff_profiles sp ON sa.staff_id = sp.id
                  WHERE sa.assignment_date = ?
                  ORDER BY s.start_time, sp.full_name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $today);
        $stmt->execute();
        return $stmt;
    }

    public function getAssignmentsByDateRange($start_date, $end_date) {
        $query = "SELECT sa.*, s.shift_name, s.shift_type, s.start_time, s.end_time,
                         sp.full_name, sp.employee_id, sp.designation
                  FROM " . $this->table_name . " sa
                  JOIN shifts s ON sa.shift_id = s.id
                  JOIN staff_profiles sp ON sa.staff_id = sp.id
                  WHERE sa.assignment_date BETWEEN ? AND ?
                  ORDER BY sa.assignment_date, s.start_time, sp.full_name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $start_date);
        $stmt->bindParam(2, $end_date);
        $stmt->execute();
        return $stmt;
    }

    public function updateStatus($id, $status, $notes = '') {
        $query = "UPDATE " . $this->table_name . " 
                  SET status=:status, notes=:notes 
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":notes", $notes);

        return $stmt->execute();
    }

    public function getStaffSchedule($staff_id, $start_date, $end_date) {
        $query = "SELECT sa.*, s.shift_name, s.shift_type, s.start_time, s.end_time
              FROM " . $this->table_name . " sa
              JOIN shifts s ON sa.shift_id = s.id
              WHERE sa.staff_id = ? AND sa.assignment_date BETWEEN ? AND ?
              ORDER BY sa.assignment_date, s.start_time";
    
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $staff_id);
        $stmt->bindParam(2, $start_date);
        $stmt->bindParam(3, $end_date);
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
