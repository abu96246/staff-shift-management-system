<?php
require_once 'config/session.php';
requireLogin();
require_once 'config/database.php';
require_once 'classes/EmergencyCoverage.php';
require_once 'classes/Shift.php';
require_once 'classes/Staff.php';
require_once 'classes/Notification.php';

$database = new Database();
$db = $database->getConnection();
$emergencyCoverage = new EmergencyCoverage($db);
$shift = new Shift($db);
$staff = new Staff($db);
$notification = new Notification($db);

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

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'request_coverage':
                $coverage_data = [
                    'shift_id' => $_POST['shift_id'],
                    'original_staff_id' => $_POST['original_staff_id'] ?? null,
                    'coverage_date' => $_POST['coverage_date'],
                    'reason' => $_POST['reason'],
                    'priority' => $_POST['priority'],
                    'requested_by' => $_SESSION['user_id'],
                    'notes' => $_POST['notes'] ?? ''
                ];
                
                if ($emergencyCoverage->create($coverage_data)) {
                    // Send notification to supervisors and admins
                    $notification->broadcastToRole('admin', 'Emergency Coverage Request', 
                        'New emergency coverage request for ' . $_POST['coverage_date'], 'warning');
                    $notification->broadcastToRole('supervisor', 'Emergency Coverage Request', 
                        'New emergency coverage request for ' . $_POST['coverage_date'], 'warning');
                    
                    $success = "Emergency coverage request submitted successfully!";
                } else {
                    $error = "Failed to submit coverage request.";
                }
                break;
                
            case 'assign_coverage':
                if (hasAnyRole(['admin', 'supervisor'])) {
                    if ($emergencyCoverage->assignCoverage($_POST['coverage_id'], $_POST['covered_by'], $_POST['notes'])) {
                        // Notify the staff member who will cover
                        $notification->create([
                            'user_id' => $_POST['covered_by_user_id'],
                            'title' => 'Emergency Coverage Assignment',
                            'message' => 'You have been assigned emergency coverage for ' . $_POST['coverage_date'],
                            'type' => 'info'
                        ]);
                        
                        $success = "Coverage assigned successfully!";
                    } else {
                        $error = "Failed to assign coverage.";
                    }
                }
                break;
                
            case 'volunteer_coverage':
                if (hasRole('staff') && $current_staff) {
                    if ($emergencyCoverage->assignCoverage($_POST['coverage_id'], $current_staff['id'], 'Volunteered for coverage')) {
                        $success = "Thank you for volunteering! Coverage assigned.";
                    } else {
                        $error = "Failed to assign coverage.";
                    }
                }
                break;
                
            case 'cancel_request':
                if ($emergencyCoverage->updateStatus($_POST['coverage_id'], 'cancelled')) {
                    $success = "Coverage request cancelled successfully!";
                } else {
                    $error = "Failed to cancel request.";
                }
                break;
        }
    }
}

$coverage_requests = $emergencyCoverage->readAll();
$open_requests = $emergencyCoverage->getOpenRequests();
$shifts = $shift->readAll();
$staff_list = $staff->readAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Coverage - Railway Shift Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .priority-critical { border-left: 4px solid #dc3545; }
        .priority-high { border-left: 4px solid #fd7e14; }
        .priority-medium { border-left: 4px solid #ffc107; }
        .priority-low { border-left: 4px solid #28a745; }
        .coverage-card { transition: all 0.3s ease; }
        .coverage-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-exclamation-triangle text-warning"></i> Emergency Coverage</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#requestCoverageModal">
                            <i class="fas fa-plus"></i> Request Emergency Coverage
                        </button>
                    </div>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Open Requests Cards -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h4 class="mb-3"><i class="fas fa-clock text-warning"></i> Open Coverage Requests</h4>
                        <?php 
                        $open_data = $open_requests->fetchAll(PDO::FETCH_ASSOC);
                        if (count($open_data) > 0): 
                        ?>
                        <div class="row">
                            <?php foreach ($open_data as $request): ?>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card coverage-card priority-<?php echo $request['priority']; ?>">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0"><?php echo htmlspecialchars($request['shift_name']); ?></h6>
                                        <span class="badge bg-<?php 
                                            echo $request['priority'] === 'critical' ? 'danger' : 
                                                ($request['priority'] === 'high' ? 'warning' : 
                                                ($request['priority'] === 'medium' ? 'info' : 'success')); 
                                        ?>">
                                            <?php echo ucfirst($request['priority']); ?>
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text">
                                            <strong>Date:</strong> <?php echo date('M d, Y', strtotime($request['coverage_date'])); ?><br>
                                            <strong>Time:</strong> <?php echo date('H:i', strtotime($request['start_time'])) . ' - ' . date('H:i', strtotime($request['end_time'])); ?><br>
                                            <strong>Reason:</strong> <?php echo htmlspecialchars($request['reason']); ?>
                                        </p>
                                        
                                        <?php if (hasRole('staff') && $current_staff): ?>
                                        <button class="btn btn-success btn-sm w-100" onclick="volunteerCoverage(<?php echo $request['id']; ?>, '<?php echo $request['coverage_date']; ?>')">
                                            <i class="fas fa-hand-paper"></i> Volunteer for Coverage
                                        </button>
                                        <?php elseif (hasAnyRole(['admin', 'supervisor'])): ?>
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignCoverageModal" 
                                                onclick="prepareAssignment(<?php echo htmlspecialchars(json_encode($request)); ?>)">
                                            <i class="fas fa-user-plus"></i> Assign Coverage
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-footer text-muted">
                                        <small>Requested by: <?php echo htmlspecialchars($request['requested_by_name']); ?></small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No open emergency coverage requests at this time.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- All Coverage Requests -->
                <div class="card shadow">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-list"></i> All Coverage Requests
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
                                        <th>Original Staff</th>
                                        <th>Covered By</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Reason</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $coverage_data = $coverage_requests->fetchAll(PDO::FETCH_ASSOC);
                                    if (count($coverage_data) > 0) {
                                        foreach ($coverage_data as $row) {
                                            $statusClass = '';
                                            switch($row['status']) {
                                                case 'open': $statusClass = 'badge-warning'; break;
                                                case 'covered': $statusClass = 'badge-success'; break;
                                                case 'cancelled': $statusClass = 'badge-secondary'; break;
                                            }
                                            
                                            $priorityClass = '';
                                            switch($row['priority']) {
                                                case 'critical': $priorityClass = 'badge-danger'; break;
                                                case 'high': $priorityClass = 'badge-warning'; break;
                                                case 'medium': $priorityClass = 'badge-info'; break;
                                                case 'low': $priorityClass = 'badge-success'; break;
                                            }
                                            
                                            echo "<tr>";
                                            echo "<td>" . date('M d, Y', strtotime($row['coverage_date'])) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['shift_name']) . "</td>";
                                            echo "<td>" . date('H:i', strtotime($row['start_time'])) . " - " . date('H:i', strtotime($row['end_time'])) . "</td>";
                                            echo "<td>" . ($row['original_staff_name'] ? htmlspecialchars($row['original_staff_name']) . " (" . htmlspecialchars($row['original_emp_id']) . ")" : 'N/A') . "</td>";
                                            echo "<td>" . ($row['covered_by_name'] ? htmlspecialchars($row['covered_by_name']) . " (" . htmlspecialchars($row['covered_emp_id']) . ")" : 'Not Assigned') . "</td>";
                                            echo "<td><span class='badge " . $priorityClass . "'>" . ucfirst($row['priority']) . "</span></td>";
                                            echo "<td><span class='badge " . $statusClass . "'>" . ucfirst($row['status']) . "</span></td>";
                                            echo "<td>" . htmlspecialchars($row['reason']) . "</td>";
                                            echo "<td>";
                                            
                                            if ($row['status'] === 'open') {
                                                if (hasAnyRole(['admin', 'supervisor'])) {
                                                    echo "<button class='btn btn-sm btn-primary me-1' data-bs-toggle='modal' data-bs-target='#assignCoverageModal' onclick='prepareAssignment(" . htmlspecialchars(json_encode($row)) . ")' title='Assign Coverage'><i class='fas fa-user-plus'></i></button>";
                                                    echo "<button class='btn btn-sm btn-secondary' onclick='cancelRequest(" . $row['id'] . ")' title='Cancel'><i class='fas fa-ban'></i></button>";
                                                }
                                            }
                                            
                                            echo "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='9' class='text-center'>No coverage requests found.</td></tr>";
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

    <!-- Request Coverage Modal -->
    <div class="modal fade" id="requestCoverageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Request Emergency Coverage</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="request_coverage">
                        
                        <div class="mb-3">
                            <label for="coverage_date" class="form-label">Coverage Date</label>
                            <input type="date" class="form-control" name="coverage_date" required min="<?php echo date('Y-m-d'); ?>">
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
                        
                        <?php if (hasAnyRole(['admin', 'supervisor'])): ?>
                        <div class="mb-3">
                            <label for="original_staff_id" class="form-label">Original Staff (Optional)</label>
                            <select class="form-select" name="original_staff_id">
                                <option value="">Select Original Staff</option>
                                <?php
                                $staff_list->execute();
                                while ($staff_row = $staff_list->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='" . $staff_row['id'] . "'>";
                                    echo htmlspecialchars($staff_row['full_name']) . " (" . htmlspecialchars($staff_row['employee_id']) . ")";
                                    echo "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="priority" class="form-label">Priority Level</label>
                            <select class="form-select" name="priority" required>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason for Emergency Coverage</label>
                            <textarea class="form-control" name="reason" rows="3" required placeholder="Please provide details about why emergency coverage is needed..."></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Additional Notes (Optional)</label>
                            <textarea class="form-control" name="notes" rows="2" placeholder="Any additional information..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Submit Emergency Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Assign Coverage Modal -->
    <div class="modal fade" id="assignCoverageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Coverage</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="assign_coverage">
                        <input type="hidden" name="coverage_id" id="assign_coverage_id">
                        <input type="hidden" name="coverage_date" id="assign_coverage_date">
                        <input type="hidden" name="covered_by_user_id" id="covered_by_user_id">
                        
                        <div class="alert alert-info">
                            <strong>Coverage Details:</strong>
                            <div id="coverage_details"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="covered_by" class="form-label">Assign to Staff Member</label>
                            <select class="form-select" name="covered_by" id="covered_by" required onchange="updateUserId()">
                                <option value="">Select Staff Member</option>
                                <?php
                                $staff_list->execute();
                                while ($staff_row = $staff_list->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='" . $staff_row['id'] . "' data-user-id='" . $staff_row['user_id'] . "'>";
                                    echo htmlspecialchars($staff_row['full_name']) . " (" . 
                                         htmlspecialchars($staff_row['employee_id']) . " - " . 
                                         htmlspecialchars($staff_row['designation']) . ")";
                                    echo "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="assign_notes" class="form-label">Assignment Notes</label>
                            <textarea class="form-control" name="notes" id="assign_notes" rows="3" placeholder="Any special instructions or notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Assign Coverage</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        function volunteerCoverage(coverageId, coverageDate) {
            if (confirm(`Are you sure you want to volunteer for coverage on ${coverageDate}?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="volunteer_coverage">
                    <input type="hidden" name="coverage_id" value="${coverageId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function prepareAssignment(request) {
            document.getElementById('assign_coverage_id').value = request.id;
            document.getElementById('assign_coverage_date').value = request.coverage_date;
            
            const details = `
                <strong>Date:</strong> ${new Date(request.coverage_date).toLocaleDateString()}<br>
                <strong>Shift:</strong> ${request.shift_name}<br>
                <strong>Time:</strong> ${request.start_time} - ${request.end_time}<br>
                <strong>Priority:</strong> ${request.priority.charAt(0).toUpperCase() + request.priority.slice(1)}<br>
                <strong>Reason:</strong> ${request.reason}
            `;
            document.getElementById('coverage_details').innerHTML = details;
        }
        
        function updateUserId() {
            const select = document.getElementById('covered_by');
            const selectedOption = select.options[select.selectedIndex];
            const userId = selectedOption.getAttribute('data-user-id');
            document.getElementById('covered_by_user_id').value = userId || '';
        }
        
        function cancelRequest(requestId) {
            if (confirm('Are you sure you want to cancel this coverage request?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="cancel_request">
                    <input type="hidden" name="coverage_id" value="${requestId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
