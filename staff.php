<?php
require_once 'config/session.php';
requireAnyRole(['admin', 'supervisor']);
require_once 'config/database.php';
require_once 'classes/Staff.php';
require_once 'classes/User.php';

$database = new Database();
$db = $database->getConnection();
$staff = new Staff($db);
$user = new User($db);

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                // Create user first
                $user->username = $_POST['username'];
                $user->email = $_POST['email'];
                $user->password = $_POST['password'];
                $user->role = $_POST['role'];
                
                $user_id = $user->create();
                if ($user_id) {
                    // Create staff profile
                    $staff_data = [
                        'user_id' => $user_id,
                        'full_name' => $_POST['full_name'],
                        'employee_id' => $_POST['employee_id'],
                        'department' => $_POST['department'],
                        'designation' => $_POST['designation'],
                        'contact_phone' => $_POST['contact_phone'],
                        'shift_preference' => $_POST['shift_preference']
                    ];
                    
                    if ($staff->create($staff_data)) {
                        $success = "Staff member added successfully!";
                    } else {
                        $error = "Failed to create staff profile.";
                    }
                } else {
                    $error = "Failed to create user account.";
                }
                break;
                
            case 'edit':
                $staff_data = [
                    'full_name' => $_POST['full_name'],
                    'department' => $_POST['department'],
                    'designation' => $_POST['designation'],
                    'contact_phone' => $_POST['contact_phone'],
                    'shift_preference' => $_POST['shift_preference']
                ];
                
                if ($staff->update($_POST['staff_id'], $staff_data)) {
                    $success = "Staff member updated successfully!";
                } else {
                    $error = "Failed to update staff member.";
                }
                break;
                
            case 'delete':
                if ($staff->delete($_POST['staff_id'])) {
                    $success = "Staff member deactivated successfully!";
                } else {
                    $error = "Failed to deactivate staff member.";
                }
                break;
        }
    }
}

$staff_list = $staff->readAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management - Railway Shift Management</title>
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
                    <h1 class="h2">Staff Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                            <i class="fas fa-plus"></i> Add New Staff
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

                <!-- Staff List -->
                <div class="card shadow">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Staff Members</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Employee ID</th>
                                        <th>Full Name</th>
                                        <th>Department</th>
                                        <th>Designation</th>
                                        <th>Contact</th>
                                        <th>Shift Preference</th>
                                        <th>Role</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $staff_list->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['employee_id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['department']); ?></td>
                                        <td><?php echo htmlspecialchars($row['designation']); ?></td>
                                        <td><?php echo htmlspecialchars($row['contact_phone']); ?></td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo ucfirst($row['shift_preference']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo ucfirst($row['role']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="editStaff(<?php echo $row['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if (hasRole('admin')): ?>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteStaff(<?php echo $row['id']; ?>)">
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

    <!-- Add Staff Modal -->
    <div class="modal fade" id="addStaffModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Staff Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" name="username" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" name="password" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="role" class="form-label">Role</label>
                                    <select class="form-select" name="role" required>
                                        <option value="staff">Staff</option>
                                        <option value="supervisor">Supervisor</option>
                                        <?php if (hasRole('admin')): ?>
                                        <option value="admin">Admin</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" name="full_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="employee_id" class="form-label">Employee ID</label>
                                    <input type="text" class="form-control" name="employee_id" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="department" class="form-label">Department</label>
                                    <select class="form-select" name="department" required>
                                        <option value="">Select Department</option>
                                        <option value="Locomotive Operations">Locomotive Operations</option>
                                        <option value="Maintenance">Maintenance</option>
                                        <option value="Safety">Safety</option>
                                        <option value="Administration">Administration</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="designation" class="form-label">Designation</label>
                                    <select class="form-select" name="designation" required>
                                        <option value="">Select Designation</option>
                                        <option value="Train Captain">Train Captain</option>
                                        <option value="Assistant Driver">Assistant Driver</option>
                                        <option value="Conductor">Conductor</option>
                                        <option value="Mechanic">Mechanic</option>
                                        <option value="Safety Officer">Safety Officer</option>
                                        <option value="Supervisor">Supervisor</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contact_phone" class="form-label">Contact Phone</label>
                                    <input type="tel" class="form-control" name="contact_phone">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="shift_preference" class="form-label">Shift Preference</label>
                                    <select class="form-select" name="shift_preference">
                                        <option value="any">Any</option>
                                        <option value="morning">Morning</option>
                                        <option value="evening">Evening</option>
                                        <option value="night">Night</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Staff Member</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        function editStaff(id) {
            // Implementation for edit functionality
            alert('Edit functionality - Staff ID: ' + id);
        }
        
        function deleteStaff(id) {
            if (confirm('Are you sure you want to deactivate this staff member?')) {
                // Create form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="staff_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
