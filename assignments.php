<?php
require_once 'config/session.php';
requireAnyRole(['admin', 'supervisor']);
require_once 'config/database.php';
require_once 'classes/ShiftAssignment.php';
require_once 'classes/Shift.php';
require_once 'classes/Staff.php';

$database = new Database();
$db = $database->getConnection();
$shiftAssignment = new ShiftAssignment($db);
$shift = new Shift($db);
$staff = new Staff($db);

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action']) && $_POST['action'] === 'assign') {
        $assignment_data = [
            'shift_id' => $_POST['shift_id'],
            'staff_id' => $_POST['staff_id'],
            'assignment_date' => $_POST['assignment_date'],
            'status' => 'scheduled',
            'notes' => $_POST['notes'] ?? ''
        ];
        
        if ($shiftAssignment->create($assignment_data)) {
            $success = "Shift assigned successfully!";
        } else {
            $error = "Failed to assign shift. Staff member may already be assigned to another shift on this date.";
        }
    }
}

// Get date range for assignments (default to current week)
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('monday this week'));
$end_date = $_GET['end_date'] ?? date('Y-m-d', strtotime('sunday this week'));

$assignments = $shiftAssignment->getAssignmentsByDateRange($start_date, $end_date);
$shifts = $shift->readAll();
$staff_list = $staff->readAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shift Assignments -  Shift Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Shift Assignments</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#assignShiftModal">
                            <i class="fas fa-plus"></i> New Assignment
                        </button>
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-calendar"></i> Date Range
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="?start_date=<?php echo date('Y-m-d'); ?>&end_date=<?php echo date('Y-m-d'); ?>">Today</a></li>
                                <li><a class="dropdown-item" href="?start_date=<?php echo date('Y-m-d', strtotime('monday this week')); ?>&end_date=<?php echo date('Y-m-d', strtotime('sunday this week')); ?>">This Week</a></li>
                                <li><a class="dropdown-item" href="?start_date=<?php echo date('Y-m-01'); ?>&end_date=<?php echo date('Y-m-t'); ?>">This Month</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Date Range Filter -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Assignments List -->
                <div class="card shadow">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            Shift Assignments (<?php echo date('M d', strtotime($start_date)); ?> - <?php echo date('M d, Y', strtotime($end_date)); ?>)
                        </h6>
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
                                        <th>Designation</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($assignments->rowCount() > 0) {
                                        while ($row = $assignments->fetch(PDO::FETCH_ASSOC)) {
                                            $statusClass = '';
                                            switch($row['status']) {
                                                case 'scheduled': $statusClass = 'badge-primary'; break;
                                                case 'completed': $statusClass = 'badge-success'; break;
                                                case 'absent': $statusClass = 'badge-danger'; break;
                                                case 'cancelled': $statusClass = 'badge-warning'; break;
                                            }
                                            echo "<tr>";
                                            echo "<td>" . date('M d, Y', strtotime($row['assignment_date'])) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['shift_name']) . "</td>";
                                            echo "<td>" . date('H:i', strtotime($row['start_time'])) . " - " . date('H:i', strtotime($row['end_time'])) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['employee_id']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['designation']) . "</td>";
                                            echo "<td><span class='badge " . $statusClass . "'>" . ucfirst($row['status']) . "</span></td>";
                                            echo "<td>";
                                            echo "<div class='btn-group' role='group'>";
                                            echo "<button class='btn btn-sm btn-outline-primary' onclick='updateAssignmentStatus(" . $row['id'] . ", \"completed\")' title='Mark Complete'><i class='fas fa-check'></i></button>";
                                            echo "<button class='btn btn-sm btn-outline-warning' onclick='updateAssignmentStatus(" . $row['id'] . ", \"absent\")' title='Mark Absent'><i class='fas fa-times'></i></button>";
                                            echo "<button class='btn btn-sm btn-outline-danger' onclick='updateAssignmentStatus(" . $row['id'] . ", \"cancelled\")' title='Cancel'><i class='fas fa-ban'></i></button>";
                                            echo "</div>";
                                            echo "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='8' class='text-center'>No assignments found for the selected date range.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Assign Shift Modal -->
    <div class="modal fade" id="assignShiftModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Shift</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="assign">
                        
                        <div class="mb-3">
                            <label for="assignment_date" class="form-label">Assignment Date</label>
                            <input type="date" class="form-control" name="assignment_date" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="shift_id" class="form-label">Shift</label>
                            <select class="form-select" name="shift_id" required>
                                <option value="">Select Shift</option>
                                <?php
                                $shifts->execute();
                                while ($shift_row = $shifts->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='" . $shift_row['id'] . "'>";
                                    echo htmlspecialchars($shift_row['shift_name']) . " (" . 
                                         date('H:i', strtotime($shift_row['start_time'])) . " - " . 
                                         date('H:i', strtotime($shift_row['end_time'])) . ")";
                                    echo "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="staff_id" class="form-label">Staff Member</label>
                            <select class="form-select" name="staff_id" required>
                                <option value="">Select Staff Member</option>
                                <?php
                                $staff_list->execute();
                                while ($staff_row = $staff_list->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='" . $staff_row['id'] . "'>";
                                    echo htmlspecialchars($staff_row['full_name']) . " (" . 
                                         htmlspecialchars($staff_row['employee_id']) . " - " . 
                                         htmlspecialchars($staff_row['designation']) . ")";
                                    echo "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Assign Shift</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        function updateAssignmentStatus(assignmentId, status) {
            if (confirm(`Are you sure you want to mark this assignment as ${status}?`)) {
                const formData = new FormData();
                formData.append('action', 'update_status');
                formData.append('assignment_id', assignmentId);
                formData.append('status', status);

                fetch('ajax/update_assignment.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Assignment status updated successfully!', 'success');
                        location.reload();
                    } else {
                        showAlert('Failed to update assignment status.', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred while updating the assignment.', 'danger');
                });
            }
        }
    </script>
</body>
</html>
