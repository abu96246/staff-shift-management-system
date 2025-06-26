<?php
require_once 'config/session.php';
requireAnyRole(['admin', 'supervisor']);
require_once 'config/database.php';
require_once 'classes/ShiftAssignment.php';

$database = new Database();
$db = $database->getConnection();
$shiftAssignment = new ShiftAssignment($db);

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');
$report_type = $_GET['report_type'] ?? 'summary';
$format = $_GET['format'] ?? 'excel';

$assignments = $shiftAssignment->getAssignmentsByDateRange($start_date, $end_date);
$assignments_data = $assignments->fetchAll(PDO::FETCH_ASSOC);

if ($format === 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="shift_report_' . date('Y-m-d') . '.xls"');
    
    echo "<table border='1'>";
    echo "<tr>";
    echo "<th>Date</th>";
    echo "<th>Shift</th>";
    echo "<th>Time</th>";
    echo "<th>Staff Member</th>";
    echo "<th>Employee ID</th>";
    echo "<th>Status</th>";
    echo "<th>Notes</th>";
    echo "</tr>";
    
    foreach ($assignments_data as $assignment) {
        echo "<tr>";
        echo "<td>" . date('M d, Y', strtotime($assignment['assignment_date'])) . "</td>";
        echo "<td>" . htmlspecialchars($assignment['shift_name']) . "</td>";
        echo "<td>" . date('H:i', strtotime($assignment['start_time'])) . ' - ' . date('H:i', strtotime($assignment['end_time'])) . "</td>";
        echo "<td>" . htmlspecialchars($assignment['full_name']) . "</td>";
        echo "<td>" . htmlspecialchars($assignment['employee_id']) . "</td>";
        echo "<td>" . ucfirst($assignment['status']) . "</td>";
        echo "<td>" . htmlspecialchars($assignment['notes']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    // For PDF export, you would typically use a library like TCPDF or FPDF
    // For now, we'll just show a simple HTML version
    echo "<h1>Shift Report</h1>";
    echo "<p>Period: " . date('M d, Y', strtotime($start_date)) . " to " . date('M d, Y', strtotime($end_date)) . "</p>";
    echo "<p>Generated on: " . date('M d, Y H:i:s') . "</p>";
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f2f2f2;'>";
    echo "<th style='padding: 8px;'>Date</th>";
    echo "<th style='padding: 8px;'>Shift</th>";
    echo "<th style='padding: 8px;'>Time</th>";
    echo "<th style='padding: 8px;'>Staff Member</th>";
    echo "<th style='padding: 8px;'>Employee ID</th>";
    echo "<th style='padding: 8px;'>Status</th>";
    echo "</tr>";
    
    foreach ($assignments_data as $assignment) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . date('M d, Y', strtotime($assignment['assignment_date'])) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($assignment['shift_name']) . "</td>";
        echo "<td style='padding: 8px;'>" . date('H:i', strtotime($assignment['start_time'])) . ' - ' . date('H:i', strtotime($assignment['end_time'])) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($assignment['full_name']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($assignment['employee_id']) . "</td>";
        echo "<td style='padding: 8px;'>" . ucfirst($assignment['status']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}
?>
