<?php
require_once 'config/database.php';

class SwapRequest {
    private $conn;
    private $table_name = "shift_swap_requests";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET requester_id=:requester_id, requested_staff_id=:requested_staff_id, 
                      original_shift_id=:original_shift_id, target_shift_id=:target_shift_id,
                      swap_date=:swap_date, reason=:reason";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":requester_id", $data['requester_id']);
        $stmt->bindParam(":requested_staff_id", $data['requested_staff_id']);
        $stmt->bindParam(":original_shift_id", $data['original_shift_id']);
        $stmt->bindParam(":target_shift_id", $data['target_shift_id']);
        $stmt->bindParam(":swap_date", $data['swap_date']);
        $stmt->bindParam(":reason", $data['reason']);

        return $stmt->execute();
    }

    public function readAll() {
        $query = "SELECT sr.*, 
                         sp1.full_name as requester_name, sp1.employee_id as requester_emp_id,
                         sp2.full_name as requested_name, sp2.employee_id as requested_emp_id,
                         s1.shift_name as original_shift_name, s1.start_time as original_start_time,
                         s2.shift_name as target_shift_name, s2.start_time as target_start_time
                  FROM " . $this->table_name . " sr
                  JOIN staff_profiles sp1 ON sr.requester_id = sp1.id
                  JOIN staff_profiles sp2 ON sr.requested_staff_id = sp2.id
                  JOIN shifts s1 ON sr.original_shift_id = s1.id
                  JOIN shifts s2 ON sr.target_shift_id = s2.id
                  ORDER BY sr.date_submitted DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getByStaff($staff_id) {
        $query = "SELECT sr.*, 
                         sp1.full_name as requester_name, sp1.employee_id as requester_emp_id,
                         sp2.full_name as requested_name, sp2.employee_id as requested_emp_id,
                         s1.shift_name as original_shift_name, s1.start_time as original_start_time,
                         s2.shift_name as target_shift_name, s2.start_time as target_start_time
                  FROM " . $this->table_name . " sr
                  JOIN staff_profiles sp1 ON sr.requester_id = sp1.id
                  JOIN staff_profiles sp2 ON sr.requested_staff_id = sp2.id
                  JOIN shifts s1 ON sr.original_shift_id = s1.id
                  JOIN shifts s2 ON sr.target_shift_id = s2.id
                  WHERE sr.requester_id = ? OR sr.requested_staff_id = ?
                  ORDER BY sr.date_submitted DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $staff_id);
        $stmt->bindParam(2, $staff_id);
        $stmt->execute();
        return $stmt;
    }

    public function updateStatus($id, $status, $approved_by) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status=:status, approved_by=:approved_by, date_processed=NOW()
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":approved_by", $approved_by);

        return $stmt->execute();
    }
}
?>
