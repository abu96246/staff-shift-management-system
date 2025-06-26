<?php
require_once 'config/session.php';
requireAnyRole(['admin', 'supervisor']);
require_once 'config/database.php';
require_once 'classes/Shift.php';

$database = new Database();
$db = $database->getConnection();
$shift = new Shift($db);

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $shift_data = [
                    'shift_name' => $_POST['shift_name'],
                    'shift_type' => $_POST['shift_type'],
                    'start_time' => $_POST['start_time'],
                    'end_time' => $_POST['end_time'],
                    'description' => $_POST['description'],
                    'max_staff' => $_POST['max_staff']
                ];
                
                if ($shift->create($shift_data)) {
                    $success = "Shift created successfully!";
                } else {
                    $error = "Failed to create shift.";
                }
                break;
                
            case 'edit':
                $shift_data = [
                    'shift_name' => $_POST['shift_name'],
                    'shift_type' => $_POST['shift_type'],
                    'start_time' => $_POST['start_time'],
                    'end_time' => $_POST['end_time'],
                    'description' => $_POST['description'],
                    'max_staff' => $_POST['max_staff']
                ];
                
                if ($shift->update($_POST['shift_id'], $shift_data)) {
                    $success = "Shift updated successfully!";
                } else {
                    $error = "Failed to update shift.";
                }
                break;
                
            case 'delete':
                if ($shift->delete($_POST['shift_id'])) {
                    $success = "Shift deleted successfully!";
                } else {
                    $error = "Failed to delete shift.";
                }
                break;
        }
    }
}

$shifts = $shift->readAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shift Management - Railway Shift Management</title>
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
                    <h1 class="h2">Shift Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addShiftModal">
                            <i class="fas fa-plus"></i> Add New Shift
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

                <!-- Shifts List -->
                <div class="card shadow">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Available Shifts</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Shift Name</th>
                                        <th>Type</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Duration</th>
                                        <th>Max Staff</th>
                                        <th>Description</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $shifts->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['shift_name']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $row['shift_type'] === 'morning' ? 'success' : 
                                                    ($row['shift_type'] === 'evening' ? 'warning' : 'info'); 
                                            ?>">
                                                <?php echo ucfirst($row['shift_type']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('H:i', strtotime($row['start_time'])); ?></td>
                                        <td><?php echo date('H:i', strtotime($row['end_time'])); ?></td>
                                        <td>
                                            <?php 
                                            $start = new DateTime($row['start_time']);
                                            $end = new DateTime($row['end_time']);
                                            if ($end < $start) $end->add(new DateInterval('P1D'));
                                            $duration = $start->diff($end);
                                            echo $duration->format('%h hours %i minutes');
                                            ?>
                                        </td>
                                        <td><?php echo $row['max_staff']; ?></td>
                                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="editShift(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if (hasRole('admin')): ?>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteShift(<?php echo $row['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Shift Modal -->
    <div class="modal fade" id="addShiftModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Shift</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label for="shift_name" class="form-label">Shift Name</label>
                            <input type="text" class="form-control" name="shift_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="shift_type" class="form-label">Shift Type</label>
                            <select class="form-select" name="shift_type" required>
                                <option value="">Select Type</option>
                                <option value="morning">Morning</option>
                                <option value="evening">Evening</option>
                                <option value="night">Night</option>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_time" class="form-label">Start Time</label>
                                    <input type="time" class="form-control" name="start_time" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_time" class="form-label">End Time</label>
                                    <input type="time" class="form-control" name="end_time" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="max_staff" class="form-label">Maximum Staff</label>
                            <input type="number" class="form-control" name="max_staff" min="1" value="1" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Shift</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Shift Modal -->
    <div class="modal fade" id="editShiftModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Shift</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="shift_id" id="edit_shift_id">
                        
                        <div class="mb-3">
                            <label for="edit_shift_name" class="form-label">Shift Name</label>
                            <input type="text" class="form-control" name="shift_name" id="edit_shift_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_shift_type" class="form-label">Shift Type</label>
                            <select class="form-select" name="shift_type" id="edit_shift_type" required>
                                <option value="morning">Morning</option>
                                <option value="evening">Evening</option>
                                <option value="night">Night</option>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_start_time" class="form-label">Start Time</label>
                                    <input type="time" class="form-control" name="start_time" id="edit_start_time" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_end_time" class="form-label">End Time</label>
                                    <input type="time" class="form-control" name="end_time" id="edit_end_time" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_max_staff" class="form-label">Maximum Staff</label>
                            <input type="number" class="form-control" name="max_staff" id="edit_max_staff" min="1" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Shift</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        function editShift(shift) {
            document.getElementById('edit_shift_id').value = shift.id;
            document.getElementById('edit_shift_name').value = shift.shift_name;
            document.getElementById('edit_shift_type').value = shift.shift_type;
            document.getElementById('edit_start_time').value = shift.start_time;
            document.getElementById('edit_end_time').value = shift.end_time;
            document.getElementById('edit_max_staff').value = shift.max_staff;
            document.getElementById('edit_description').value = shift.description;
            
            new bootstrap.Modal(document.getElementById('editShiftModal')).show();
        }
        
        function deleteShift(id) {
            if (confirm('Are you sure you want to delete this shift? This will also remove all associated assignments.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="shift_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
