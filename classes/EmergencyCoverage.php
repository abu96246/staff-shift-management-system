<?php
require_once 'config/database.php';

class EmergencyCoverage {
    private $conn;
    private $table_name = "emergency_coverage";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET shift_id=:shift_id, original_staff_id=:original_staff_id, 
                      coverage_date=:coverage_date, reason=:reason, priority=:priority, 
                      requested_by=:requested_by, notes=:notes";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":shift_id", $data['shift_id']);
        $stmt->bindParam(":original_staff_id", $data['original_staff_id']);
        $stmt->bindParam(":coverage_date", $data['coverage_date']);
        $stmt->bindParam(":reason", $data['reason']);
        $stmt->bindParam(":priority", $data['priority']);
        $stmt->bindParam(":requested_by", $data['requested_by']);
        $stmt->bindParam(":notes", $data['notes']);

        return $stmt->execute();
    }

    public function readAll() {
        $query = "SELECT ec.*, 
                         s.shift_name, s.start_time, s.end_time,
                         sp1.full_name as original_staff_name, sp1.employee_id as original_emp_id,
                         sp2.full_name as covered_by_name, sp2.employee_id as covered_emp_id,
                         u.username as requested_by_name
                  FROM " . $this->table_name . " ec
                  JOIN shifts s ON ec.shift_id = s.id
                  LEFT JOIN staff_profiles sp1 ON ec.original_staff_id = sp1.id
                  LEFT JOIN staff_profiles sp2 ON ec.covered_by = sp2.id
                  JOIN users u ON ec.requested_by = u.id
                  ORDER BY ec.priority DESC, ec.coverage_date ASC, ec.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getOpenRequests() {
        $query = "SELECT ec.*, 
                         s.shift_name, s.start_time, s.end_time,
                         sp1.full_name as original_staff_name, sp1.employee_id as original_emp_id,
                         u.username as requested_by_name
                  FROM " . $this->table_name . " ec
                  JOIN shifts s ON ec.shift_id = s.id
                  LEFT JOIN staff_profiles sp1 ON ec.original_staff_id = sp1.id
                  JOIN users u ON ec.requested_by = u.id
                  WHERE ec.status = 'open' AND ec.coverage_date >= CURDATE()
                  ORDER BY ec.priority DESC, ec.coverage_date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function assignCoverage($id, $covered_by, $notes = '') {
        $query = "UPDATE " . $this->table_name . " 
                  SET covered_by=:covered_by, status='covered', 
                      covered_at=NOW(), notes=:notes
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":covered_by", $covered_by);
        $stmt->bindParam(":notes", $notes);

        return $stmt->execute();
    }

    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status=:status 
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":status", $status);

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
