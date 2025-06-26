<?php
require_once '../config/session.php';
requireLogin();
require_once '../config/database.php';
require_once '../classes/ShiftAssignment.php';
require_once '../classes/Staff.php';
require_once '../classes/SwapRequest.php';
require_once '../classes/EmergencyCoverage.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

try {
    // Get today's shifts
    $shiftAssignment = new ShiftAssignment($db);
    $todayAssignments = $shiftAssignment->getTodayAssignments();
    $todayShifts = $todayAssignments->rowCount();

    // Get active staff count
    $staff = new Staff($db);
    $activeStaff = $staff->readAll();
    $activeStaffCount = $activeStaff->rowCount();

    // Get pending swap requests
    $swapRequest = new SwapRequest($db);
    $query = "SELECT COUNT(*) as count FROM shift_swap_requests WHERE status = 'pending'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $pendingRequests = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Get open emergency coverage requests
    $emergencyCoverage = new EmergencyCoverage($db);
    $query = "SELECT COUNT(*) as count FROM emergency_coverage WHERE status = 'open'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $alerts = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    echo json_encode([
        'success' => true,
        'stats' => [
            'todayShifts' => $todayShifts,
            'activeStaff' => $activeStaffCount,
            'pendingRequests' => $pendingRequests,
            'alerts' => $alerts
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error loading dashboard stats: ' . $e->getMessage()
    ]);
}
?>
