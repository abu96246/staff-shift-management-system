<?php
require_once '../config/session.php';
requireAnyRole(['admin', 'supervisor']);
require_once '../config/database.php';
require_once '../classes/ShiftAssignment.php';

header('Content-Type: application/json');

if ($_POST && isset($_POST['action'])) {
    $database = new Database();
    $db = $database->getConnection();
    $shiftAssignment = new ShiftAssignment($db);
    
    if ($_POST['action'] === 'update_status') {
        $assignment_id = $_POST['assignment_id'];
        $status = $_POST['status'];
        $notes = $_POST['notes'] ?? '';
        
        if ($shiftAssignment->updateStatus($assignment_id, $status, $notes)) {
            echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update status']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
