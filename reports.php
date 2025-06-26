<?php
require_once 'config/session.php';
requireAnyRole(['admin', 'supervisor']);
require_once 'config/database.php';
require_once 'classes/ShiftAssignment.php';
require_once 'classes/Staff.php';
require_once 'classes/Shift.php';

$database = new Database();
$db = $database->getConnection();
$shiftAssignment = new ShiftAssignment($db);
$staff = new Staff($db);
$shift = new Shift($db);

// Get date range for reports
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$end_date = $_GET['end_date'] ?? date('Y-m-t'); // Last day of current month
$report_type = $_GET['report_type'] ?? 'summary';

// Get report data
$assignments = $shiftAssignment->getAssignmentsByDateRange($start_date, $end_date);
$staff_list = $staff->readAll();
$shifts_list = $shift->readAll();

// Calculate statistics
$total_assignments = 0;
$completed_assignments = 0;
$absent_count = 0;
$cancelled_count = 0;
$staff_stats = [];
$shift_stats = [];

$assignments_data = $assignments->fetchAll(PDO::FETCH_ASSOC);
foreach ($assignments_data as $assignment) {
    $total_assignments++;
    
    switch ($assignment['status']) {
        case 'completed':
            $completed_assignments++;
            break;
        case 'absent':
            $absent_count++;
            break;
        case 'cancelled':
            $cancelled_count++;
            break;
    }
    
    // Staff statistics
    $staff_id = $assignment['staff_id'];
    if (!isset($staff_stats[$staff_id])) {
        $staff_stats[$staff_id] = [
            'name' => $assignment['full_name'],
            'employee_id' => $assignment['employee_id'],
            'total' => 0,
            'completed' => 0,
            'absent' => 0
        ];
    }
    $staff_stats[$staff_id]['total']++;
    if ($assignment['status'] === 'completed') {
        $staff_stats[$staff_id]['completed']++;
    } elseif ($assignment['status'] === 'absent') {
        $staff_stats[$staff_id]['absent']++;
    }
    
    // Shift statistics
    $shift_id = $assignment['shift_id'];
    if (!isset($shift_stats[$shift_id])) {
        $shift_stats[$shift_id] = [
            'name' => $assignment['shift_name'],
            'type' => $assignment['shift_type'],
            'total' => 0,
            'completed' => 0
        ];
    }
    $shift_stats[$shift_id]['total']++;
    if ($assignment['status'] === 'completed') {
        $shift_stats[$shift_id]['completed']++;
    }
}

$attendance_rate = $total_assignments > 0 ? round(($completed_assignments / $total_assignments) * 100, 2) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Railway Shift Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Reports & Analytics</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-outline-secondary" onclick="exportReport('excel')">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="exportReport('pdf')">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </button>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="window.print()">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>
                </div>

                <!-- Report Filters -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="report_type" class="form-label">Report Type</label>
                                <select class="form-select" name="report_type">
                                    <option value="summary" <?php echo $report_type === 'summary' ? 'selected' : ''; ?>>Summary</option>
                                    <option value="detailed" <?php echo $report_type === 'detailed' ? 'selected' : ''; ?>>Detailed</option>
                                    <option value="attendance" <?php echo $report_type === 'attendance' ? 'selected' : ''; ?>>Attendance</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block w-100">
                                    <i class="fas fa-search"></i> Generate Report
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Summary Statistics -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Assignments</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_assignments; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Completed</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $completed_assignments; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Attendance Rate</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $attendance_rate; ?>%</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-percentage fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-danger shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                            Absences</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $absent_count; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-times fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <div class="col-xl-8 col-lg-7">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Shift Distribution</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="shiftChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-5">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Status Overview</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Staff Performance Table -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Staff Performance Report</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Employee ID</th>
                                        <th>Staff Name</th>
                                        <th>Total Assignments</th>
                                        <th>Completed</th>
                                        <th>Absent</th>
                                        <th>Attendance Rate</th>
                                        <th>Performance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($staff_stats as $staff_stat): ?>
                                    <?php 
                                    $staff_attendance_rate = $staff_stat['total'] > 0 ? 
                                        round(($staff_stat['completed'] / $staff_stat['total']) * 100, 2) : 0;
                                    $performance_class = $staff_attendance_rate >= 90 ? 'success' : 
                                                        ($staff_attendance_rate >= 75 ? 'warning' : 'danger');
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($staff_stat['employee_id']); ?></td>
                                        <td><?php echo htmlspecialchars($staff_stat['name']); ?></td>
                                        <td><?php echo $staff_stat['total']; ?></td>
                                        <td><?php echo $staff_stat['completed']; ?></td>
                                        <td><?php echo $staff_stat['absent']; ?></td>
                                        <td><?php echo $staff_attendance_rate; ?>%</td>
                                        <td>
                                            <span class="badge bg-<?php echo $performance_class; ?>">
                                                <?php 
                                                echo $staff_attendance_rate >= 90 ? 'Excellent' : 
                                                    ($staff_attendance_rate >= 75 ? 'Good' : 'Needs Improvement');
                                                ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Detailed Assignments Table -->
                <?php if ($report_type === 'detailed'): ?>
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Detailed Assignment Report</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Shift</th>
                                        <th>Time</th>
                                        <th>Staff Member</th>
                                        <th>Employee ID</th>
                                        <th>Status</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assignments_data as $assignment): ?>
                                    <?php
                                    $statusClass = '';
                                    switch($assignment['status']) {
                                        case 'scheduled': $statusClass = 'badge-primary'; break;
                                        case 'completed': $statusClass = 'badge-success'; break;
                                        case 'absent': $statusClass = 'badge-danger'; break;
                                        case 'cancelled': $statusClass = 'badge-warning'; break;
                                    }
                                    ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($assignment['assignment_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($assignment['shift_name']); ?></td>
                                        <td><?php echo date('H:i', strtotime($assignment['start_time'])) . ' - ' . date('H:i', strtotime($assignment['end_time'])); ?></td>
                                        <td><?php echo htmlspecialchars($assignment['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($assignment['employee_id']); ?></td>
                                        <td><span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($assignment['status']); ?></span></td>
                                        <td><?php echo htmlspecialchars($assignment['notes']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Shift Distribution Chart
        const shiftCtx = document.getElementById('shiftChart').getContext('2d');
        const shiftChart = new Chart(shiftCtx, {
            type: 'bar',
            data: {
                labels: [<?php 
                    $shift_labels = [];
                    foreach ($shift_stats as $shift_stat) {
                        $shift_labels[] = "'" . $shift_stat['name'] . "'";
                    }
                    echo implode(',', $shift_labels);
                ?>],
                datasets: [{
                    label: 'Total Assignments',
                    data: [<?php 
                        $shift_totals = [];
                        foreach ($shift_stats as $shift_stat) {
                            $shift_totals[] = $shift_stat['total'];
                        }
                        echo implode(',', $shift_totals);
                    ?>],
                    backgroundColor: 'rgba(78, 115, 223, 0.8)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    borderWidth: 1
                }, {
                    label: 'Completed',
                    data: [<?php 
                        $shift_completed = [];
                        foreach ($shift_stats as $shift_stat) {
                            $shift_completed[] = $shift_stat['completed'];
                        }
                        echo implode(',', $shift_completed);
                    ?>],
                    backgroundColor: 'rgba(28, 200, 138, 0.8)',
                    borderColor: 'rgba(28, 200, 138, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Status Overview Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Absent', 'Cancelled', 'Scheduled'],
                datasets: [{
                    data: [
                        <?php echo $completed_assignments; ?>,
                        <?php echo $absent_count; ?>,
                        <?php echo $cancelled_count; ?>,
                        <?php echo $total_assignments - $completed_assignments - $absent_count - $cancelled_count; ?>
                    ],
                    backgroundColor: [
                        'rgba(28, 200, 138, 0.8)',
                        'rgba(231, 74, 59, 0.8)',
                        'rgba(246, 194, 62, 0.8)',
                        'rgba(78, 115, 223, 0.8)'
                    ],
                    borderColor: [
                        'rgba(28, 200, 138, 1)',
                        'rgba(231, 74, 59, 1)',
                        'rgba(246, 194, 62, 1)',
                        'rgba(78, 115, 223, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        function exportReport(format) {
            const params = new URLSearchParams({
                start_date: '<?php echo $start_date; ?>',
                end_date: '<?php echo $end_date; ?>',
                report_type: '<?php echo $report_type; ?>',
                format: format
            });
            
            window.open(`export_report.php?${params.toString()}`, '_blank');
        }
    </script>
</body>
</html>
