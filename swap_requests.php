<?php
require_once 'config/session.php';
requireLogin();
require_once 'config/database.php';
require_once 'classes/SwapRequest.php';
require_once 'classes/Staff.php';
require_once 'classes/Shift.php';
require_once 'classes/ShiftAssignment.php';

$database = new Database();
$db = $database->getConnection();
$swapRequest = new SwapRequest($db);
$staff = new Staff($db);
$shift = new Shift($db);
$shiftAssignment = new ShiftAssignment($db);

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
            case 'request_swap':
                $swap_data = [
                    'requester_id' => $current_staff['id'],
                    'requested_staff_id' => $_POST['requested_staff_id'],
                    'original_shift_id' => $_POST['original_shift_id'],
                    'target_shift_id' => $_POST['target_shift_id'],
                    'swap_date' => $_POST['swap_date'],
                    'reason' => $_POST['reason']
                ];
                
                if ($swapRequest->create($swap_data)) {
                    $success = "Shift swap request submitted successfully!";
                } else {
                    $error = "Failed to submit swap request.";
                }
                break;
                
            case 'process_swap':
                if (hasAnyRole(['admin', 'supervisor'])) {
                    if ($swapRequest->updateStatus($_POST['request_id'], $_POST['decision'], $_SESSION['user_id'])) {
                        $success = "Swap request " . $_POST['decision'] . " successfully!";
                    } else {
                        $error = "Failed to process swap request.";
                    }
                }
                break;
        }
    }
}

// Get swap requests based on user role
if (hasRole('staff') && $current_staff) {
    $swap_requests = $swapRequest->getByStaff($current_staff['id']);
} else {
    $swap_requests = $swapRequest->readAll();
}

$staff_list = $staff->readAll();
$shifts = $shift->readAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shift Swap Requests - Railway Shift Management</title>
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
                    <h1 class="h2">Shift Swap Requests</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <?php if (hasRole('staff')): ?>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#requestSwapModal">
                            <i class="fas fa-exchange-alt"></i> Request Swap
                        </button>
                        <?php endif; ?>
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

                <!-- Swap Requests List -->
                <div class="card shadow">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <?php echo hasRole('staff') ? 'My Swap Requests' : 'All Swap Requests'; ?>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Date Submitted</th>
                                        <th>Swap Date</th>
                                        <th>Requester</th>
                                        <th>Requested Staff</th>
                                        <th>Original Shift</th>
                                        <th>Target Shift</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $swap_data = $swap_requests->fetchAll(PDO::FETCH_ASSOC);
                                    if (count($swap_data) > 0) {
                                        foreach ($swap_data as $row) {
                                            $statusClass = '';
                                            switch($row['status']) {
                                                case 'pending': $statusClass = 'badge-warning'; break;
                                                case 'approved': $statusClass = 'badge-success'; break;
                                                case 'rejected': $statusClass = 'badge-danger'; break;
                                                case 'cancelled': $statusClass = 'badge-secondary'; break;
                                            }
                                            echo "<tr>";
                                            echo "<td>" . date('M d, Y H:i', strtotime($row['date_submitted'])) . "</td>";
                                            echo "<td>" . date('M d, Y', strtotime($row['swap_date'])) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['requester_name']) . "<br><small>(" . htmlspecialchars($row['requester_emp_id']) . ")</small></td>";
                                            echo "<td>" . htmlspecialchars($row['requested_name']) . "<br><small>(" . htmlspecialchars($row['requested_emp_id']) . ")</small></td>";
                                            echo "<td>" . htmlspecialchars($row['original_shift_name']) . "<br><small>" . date('H:i', strtotime($row['original_start_time'])) . "</small></td>";
                                            echo "<td>" . htmlspecialchars($row['target_shift_name']) . "<br><small>" . date('H:i', strtotime($row['target_start_time'])) . "</small></td>";
                                            echo "<td>" . htmlspecialchars($row['reason']) . "</td>";
                                            echo "<td><span class='badge " . $statusClass . "'>" . ucfirst($row['status']) . "</span></td>";
                                            echo "<td>";
                                            
                                            if ($row['status'] === 'pending') {
                                                if (hasAnyRole(['admin', 'supervisor'])) {
                                                    echo "<button class='btn btn-sm btn-success me-1' onclick='processSwap(" . $row['id'] . ", \"approved\")' title='Approve'><i class='fas fa-check'></i></button>";
                                                    echo "<button class='btn btn-sm btn-danger' onclick='processSwap(" . $row['id'] . ", \"rejected\")' title='Reject'><i class='fas fa-times'></i></button>";
                                                } elseif (hasRole('staff') && $current_staff && $row['requester_id'] == $current_staff['id']) {
                                                    echo "<button class='btn btn-sm btn-secondary' onclick='processSwap(" . $row['id'] . ", \"cancelled\")' title='Cancel'><i class='fas fa-ban'></i></button>";
                                                }
                                            }
                                            
                                            echo "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='9' class='text-center'>No swap requests found.</td></tr>";
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

    <!-- Request Swap Modal -->
    <?php if (hasRole('staff')): ?>
    <div class="modal fade" id="requestSwapModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Request Shift Swap</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="request_swap">
                        
                        <div class="mb-3">
                            <label for="swap_date" class="form-label">Swap Date</label>
                            <input type="date" class="form-control" name="swap_date" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="original_shift_id" class="form-label">Your Current Shift</label>
                                    <select class="form-select" name="original_shift_id" required>
                                        <option value="">Select Your Shift</option>
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
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="target_shift_id" class="form-label">Desired Shift</label>
                                    <select class="form-select" name="target_shift_id" required>
                                        <option value="">Select Desired Shift</option>
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
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="requested_staff_id" class="form-label">Staff Member to Swap With</label>
                            <select class="form-select" name="requested_staff_id" required>
                                <option value="">Select Staff Member</option>
                                <?php
                                $staff_list->execute();
                                while ($staff_row = $staff_list->fetch(PDO::FETCH_ASSOC)) {
                                    if ($current_staff && $staff_row['id'] != $current_staff['id']) {
                                        echo "<option value='" . $staff_row['id'] . "'>";
                                        echo htmlspecialchars($staff_row['full_name']) . " (" . 
                                             htmlspecialchars($staff_row['employee_id']) . " - " . 
                                             htmlspecialchars($staff_row['designation']) . ")";
                                        echo "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason for Swap</label>
                            <textarea class="form-control" name="reason" rows="3" required placeholder="Please provide a reason for the shift swap request..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        function processSwap(requestId, decision) {
            const actionText = decision === 'approved' ? 'approve' : 
                              decision === 'rejected' ? 'reject' : 'cancel';
            
            if (confirm(`Are you sure you want to ${actionText} this swap request?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="process_swap">
                    <input type="hidden" name="request_id" value="${requestId}">
                    <input type="hidden" name="decision" value="${decision}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
