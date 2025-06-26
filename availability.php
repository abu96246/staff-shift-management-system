<?php
require_once 'config/session.php';
requireLogin();
require_once 'config/database.php';
require_once 'classes/Availability.php';
require_once 'classes/Staff.php';

$database = new Database();
$db = $database->getConnection();
$availability = new Availability($db);
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

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'set_availability':
                $staff_id = hasRole('staff') ? $current_staff['id'] : $_POST['staff_id'];
                $availability_data = [
                    'staff_id' => $staff_id,
                    'available_date' => $_POST['available_date'],
                    'status' => $_POST['status'],
                    'shift_preference' => $_POST['shift_preference'],
                    'notes' => $_POST['notes'] ?? ''
                ];
                
                if ($availability->create($availability_data)) {
                    $success = "Availability updated successfully!";
                } else {
                    $error = "Failed to update availability.";
                }
                break;
                
            case 'delete':
                if ($availability->delete($_POST['availability_id'])) {
                    $success = "Availability record deleted successfully!";
                } else {
                    $error = "Failed to delete availability record.";
                }
                break;
        }
    }
}

// Get date range
$start_date = $_GET['start_date'] ?? date('Y-m-d');
$end_date = $_GET['end_date'] ?? date('Y-m-d', strtotime('+30 days'));

// Get availability data
if (hasRole('staff') && $current_staff) {
    $availability_data = $availability->getStaffAvailability($current_staff['id'], $start_date, $end_date);
} else {
    $availability_data = $availability->getAllAvailability($start_date, $end_date);
}

$staff_list = $staff->readAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Availability Management - Railway Shift Management</title>
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
                    <h1 class="h2">Availability Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#setAvailabilityModal">
                            <i class="fas fa-plus"></i> Set Availability
                        </button>
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

                <!-- Availability List -->
                <div class="card shadow">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <?php echo hasRole('staff') ? 'My Availability' : 'Staff Availability'; ?>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Day</th>
                                        <?php if (!hasRole('staff')): ?>
                                        <th>Staff Member</th>
                                        <th>Employee ID</th>
                                        <?php endif; ?>
                                        <th>Status</th>
                                        <th>Shift Preference</th>
                                        <th>Notes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $availability_records = $availability_data->fetchAll(PDO::FETCH_ASSOC);
                                    if (count($availability_records) > 0) {
                                        foreach ($availability_records as $row) {
                                            $statusClass = '';
                                            switch($row['status']) {
                                                case 'available': $statusClass = 'badge-success'; break;
                                                case 'unavailable': $statusClass = 'badge-danger'; break;
                                                case 'partial': $statusClass = 'badge-warning'; break;
                                            }
                                            echo "<tr>";
                                            echo "<td>" . date('M d, Y', strtotime($row['available_date'])) . "</td>";
                                            echo "<td>" . date('l', strtotime($row['available_date'])) . "</td>";
                                            if (!hasRole('staff')) {
                                                echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['employee_id']) . "</td>";
                                            }
                                            echo "<td><span class='badge " . $statusClass . "'>" . ucfirst($row['status']) . "</span></td>";
                                            echo "<td><span class='badge bg-info'>" . ucfirst($row['shift_preference']) . "</span></td>";
                                            echo "<td>" . htmlspecialchars($row['notes']) . "</td>";
                                            echo "<td>";
                                            echo "<button class='btn btn-sm btn-outline-primary' onclick='editAvailability(" . htmlspecialchars(json_encode($row)) . ")' title='Edit'><i class='fas fa-edit'></i></button> ";
                                            echo "<button class='btn btn-sm btn-outline-danger' onclick='deleteAvailability(" . $row['id'] . ")' title='Delete'><i class='fas fa-trash'></i></button>";
                                            echo "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        $colspan = hasRole('staff') ? 6 : 8;
                                        echo "<tr><td colspan='" . $colspan . "' class='text-center'>No availability records found for the selected date range.</td></tr>";
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

    <!-- Set Availability Modal -->
    <div class="modal fade" id="setAvailabilityModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Set Availability</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="set_availability">
                        
                        <?php if (!hasRole('staff')): ?>
                        <div class="mb-3">
                            <label for="staff_id" class="form-label">Staff Member</label>
                            <select class="form-select" name="staff_id" required>
                                <option value="">Select Staff Member</option>
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
                            <label for="available_date" class="form-label">Date</label>
                            <input type="date" class="form-control" name="available_date" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Availability Status</label>
                            <select class="form-select" name="status" required>
                                <option value="">Select Status</option>
                                <option value="available">Available</option>
                                <option value="unavailable">Unavailable</option>
                                <option value="partial">Partially Available</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="shift_preference" class="form-label">Shift Preference</label>
                            <select class="form-select" name="shift_preference">
                                <option value="any">Any Shift</option>
                                <option value="morning">Morning Only</option>
                                <option value="evening">Evening Only</option>
                                <option value="night">Night Only</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Any additional information..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Set Availability</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Availability Modal -->
    <div class="modal fade" id="editAvailabilityModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Availability</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="set_availability">
                        <input type="hidden" name="staff_id" id="edit_staff_id">
                        
                        <div class="mb-3">
                            <label for="edit_available_date" class="form-label">Date</label>
                            <input type="date" class="form-control" name="available_date" id="edit_available_date" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_status" class="form-label">Availability Status</label>
                            <select class="form-select" name="status" id="edit_status" required>
                                <option value="available">Available</option>
                                <option value="unavailable">Unavailable</option>
                                <option value="partial">Partially Available</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_shift_preference" class="form-label">Shift Preference</label>
                            <select class="form-select" name="shift_preference" id="edit_shift_preference">
                                <option value="any">Any Shift</option>
                                <option value="morning">Morning Only</option>
                                <option value="evening">Evening Only</option>
                                <option value="night">Night Only</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" name="notes" id="edit_notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Availability</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        function editAvailability(availability) {
            document.getElementById('edit_staff_id').value = availability.staff_id;
            document.getElementById('edit_available_date').value = availability.available_date;
            document.getElementById('edit_status').value = availability.status;
            document.getElementById('edit_shift_preference').value = availability.shift_preference;
            document.getElementById('edit_notes').value = availability.notes;
            
            new bootstrap.Modal(document.getElementById('editAvailabilityModal')).show();
        }
        
        function deleteAvailability(id) {
            if (confirm('Are you sure you want to delete this availability record?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="availability_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
