<?php
require_once 'config/session.php';
requireLogin();
require_once 'config/database.php';
require_once 'classes/ShiftAssignment.php';
require_once 'classes/Staff.php';

$database = new Database();
$db = $database->getConnection();
$shiftAssignment = new ShiftAssignment($db);
$staff = new Staff($db);

// Get current user's staff profile
$current_staff = null;
if (hasRole('staff')) {
    $staff_profiles = $staff->readAll();
    while ($profile = $staff_profiles->fetch(PDO::FETCH_ASSOC)) {
        if ($profile['user_id'] == $_SESSION['user_id']) {
            $current_staff = $profile;
            break;
        }
    }
}

// Get date range (default to current week)
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('monday this week'));
$end_date = $_GET['end_date'] ?? date('Y-m-d', strtotime('sunday this week'));

// Get schedule based on user role
if (hasRole('staff') && $current_staff) {
    $schedule = $shiftAssignment->getStaffSchedule($current_staff['id'], $start_date, $end_date);
} else {
    $schedule = $shiftAssignment->getAssignmentsByDateRange($start_date, $end_date);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo hasRole('staff') ? 'My Schedule' : 'Staff Schedule'; ?> - Railway Shift Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background-color: #dee2e6;
            border: 1px solid #dee2e6;
        }
        .calendar-day {
            background-color: white;
            min-height: 120px;
            padding: 8px;
            position: relative;
        }
        .calendar-day-header {
            font-weight: bold;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        .shift-item {
            background-color: #e3f2fd;
            border-left: 3px solid #2196f3;
            padding: 2px 6px;
            margin: 2px 0;
            font-size: 0.75rem;
            border-radius: 2px;
        }
        .shift-morning { border-left-color: #4caf50; background-color: #e8f5e8; }
        .shift-evening { border-left-color: #ff9800; background-color: #fff3e0; }
        .shift-night { border-left-color: #3f51b5; background-color: #e8eaf6; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?php echo hasRole('staff') ? 'My Schedule' : 'Staff Schedule'; ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-outline-secondary" onclick="changeWeek(-1)">
                                <i class="fas fa-chevron-left"></i> Previous Week
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="goToCurrentWeek()">
                                <i class="fas fa-calendar-day"></i> This Week
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="changeWeek(1)">
                                Next Week <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-primary" onclick="toggleView()">
                                <i class="fas fa-list"></i> List View
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Date Range Display -->
                <div class="alert alert-info">
                    <i class="fas fa-calendar"></i>
                    <strong>Week of:</strong> <?php echo date('M d', strtotime($start_date)); ?> - <?php echo date('M d, Y', strtotime($end_date)); ?>
                </div>

                <!-- Calendar View -->
                <div id="calendar-view" class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Weekly Schedule</h6>
                    </div>
                    <div class="card-body">
                        <div class="calendar-grid">
                            <?php
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            $current_date = new DateTime($start_date);
                            
                            // Group assignments by date
                            $assignments_by_date = [];
                            $schedule_data = $schedule->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($schedule_data as $assignment) {
                                $date = $assignment['assignment_date'];
                                if (!isset($assignments_by_date[$date])) {
                                    $assignments_by_date[$date] = [];
                                }
                                $assignments_by_date[$date][] = $assignment;
                            }
                            
                            for ($i = 0; $i < 7; $i++) {
                                $date_str = $current_date->format('Y-m-d');
                                $day_assignments = $assignments_by_date[$date_str] ?? [];
                                
                                echo '<div class="calendar-day">';
                                echo '<div class="calendar-day-header">';
                                echo $days[$i] . '<br>';
                                echo '<small class="text-muted">' . $current_date->format('M d') . '</small>';
                                echo '</div>';
                                
                                foreach ($day_assignments as $assignment) {
                                    $shift_class = 'shift-' . $assignment['shift_type'];
                                    echo '<div class="shift-item ' . $shift_class . '">';
                                    echo '<strong>' . htmlspecialchars($assignment['shift_name']) . '</strong><br>';
                                    echo '<small>' . date('H:i', strtotime($assignment['start_time'])) . '-' . date('H:i', strtotime($assignment['end_time'])) . '</small>';
                                    if (!hasRole('staff')) {
                                        echo '<br><small>' . htmlspecialchars($assignment['full_name']) . '</small>';
                                    }
                                    echo '</div>';
                                }
                                
                                echo '</div>';
                                $current_date->add(new DateInterval('P1D'));
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- List View -->
                <div id="list-view" class="card shadow" style="display: none;">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Schedule List</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Day</th>
                                        <th>Shift</th>
                                        <th>Time</th>
                                        <?php if (!hasRole('staff')): ?>
                                        <th>Staff Member</th>
                                        <th>Employee ID</th>
                                        <?php endif; ?>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (count($schedule_data) > 0) {
                                        foreach ($schedule_data as $row) {
                                            $statusClass = '';
                                            switch($row['status']) {
                                                case 'scheduled': $statusClass = 'badge-primary'; break;
                                                case 'completed': $statusClass = 'badge-success'; break;
                                                case 'absent': $statusClass = 'badge-danger'; break;
                                                case 'cancelled': $statusClass = 'badge-warning'; break;
                                            }
                                            echo "<tr>";
                                            echo "<td>" . date('M d, Y', strtotime($row['assignment_date'])) . "</td>";
                                            echo "<td>" . date('l', strtotime($row['assignment_date'])) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['shift_name']) . "</td>";
                                            echo "<td>" . date('H:i', strtotime($row['start_time'])) . " - " . date('H:i', strtotime($row['end_time'])) . "</td>";
                                            if (!hasRole('staff')) {
                                                echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['employee_id']) . "</td>";
                                            }
                                            echo "<td><span class='badge " . $statusClass . "'>" . ucfirst($row['status']) . "</span></td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        $colspan = hasRole('staff') ? 5 : 7;
                                        echo "<tr><td colspan='" . $colspan . "' class='text-center'>No shifts scheduled for this week.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Legend -->
                <div class="card shadow mt-4">
                    <div class="card-body">
                        <h6 class="card-title">Legend</h6>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="shift-item shift-morning">Morning Shift</div>
                            </div>
                            <div class="col-md-3">
                                <div class="shift-item shift-evening">Evening Shift</div>
                            </div>
                            <div class="col-md-3">
                                <div class="shift-item shift-night">Night Shift</div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        function changeWeek(direction) {
            const currentStart = new Date('<?php echo $start_date; ?>');
            currentStart.setDate(currentStart.getDate() + (direction * 7));
            
            const currentEnd = new Date(currentStart);
            currentEnd.setDate(currentEnd.getDate() + 6);
            
            const startStr = currentStart.toISOString().split('T')[0];
            const endStr = currentEnd.toISOString().split('T')[0];
            
            window.location.href = `schedule.php?start_date=${startStr}&end_date=${endStr}`;
        }
        
        function goToCurrentWeek() {
            window.location.href = 'schedule.php';
        }
        
        function toggleView() {
            const calendarView = document.getElementById('calendar-view');
            const listView = document.getElementById('list-view');
            
            if (calendarView.style.display === 'none') {
                calendarView.style.display = 'block';
                listView.style.display = 'none';
            } else {
                calendarView.style.display = 'none';
                listView.style.display = 'block';
            }
        }
    </script>
</body>
</html>
