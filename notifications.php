<?php
require_once 'config/session.php';
requireLogin();
require_once 'config/database.php';
require_once 'classes/Notification.php';

$database = new Database();
$db = $database->getConnection();
$notification = new Notification($db);

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'mark_read':
                if ($notification->markAsRead($_POST['notification_id'], $_SESSION['user_id'])) {
                    $success = "Notification marked as read.";
                } else {
                    $error = "Failed to mark notification as read.";
                }
                break;
                
            case 'mark_all_read':
                if ($notification->markAllAsRead($_SESSION['user_id'])) {
                    $success = "All notifications marked as read.";
                } else {
                    $error = "Failed to mark all notifications as read.";
                }
                break;
                
            case 'delete':
                if ($notification->delete($_POST['notification_id'], $_SESSION['user_id'])) {
                    $success = "Notification deleted successfully.";
                } else {
                    $error = "Failed to delete notification.";
                }
                break;
                
            case 'send_broadcast':
                if (hasAnyRole(['admin', 'supervisor'])) {
                    if ($notification->broadcastToRole($_POST['target_role'], $_POST['title'], $_POST['message'], $_POST['type'])) {
                        $success = "Broadcast notification sent successfully!";
                    } else {
                        $error = "Failed to send broadcast notification.";
                    }
                }
                break;
        }
    }
}

$notifications = $notification->getUserNotifications($_SESSION['user_id']);
$unread_count = $notification->getUnreadCount($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Railway Shift Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .notification-item {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        .notification-item:hover {
            background-color: #f8f9fa;
        }
        .notification-item.unread {
            background-color: #fff3cd;
            border-left-color: #ffc107;
        }
        .notification-item.type-info { border-left-color: #17a2b8; }
        .notification-item.type-success { border-left-color: #28a745; }
        .notification-item.type-warning { border-left-color: #ffc107; }
        .notification-item.type-danger { border-left-color: #dc3545; }
        .notification-actions {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .notification-item:hover .notification-actions {
            opacity: 1;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-bell"></i> Notifications 
                        <?php if ($unread_count > 0): ?>
                        <span class="badge bg-danger"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <?php if ($unread_count > 0): ?>
                        <button type="button" class="btn btn-outline-primary me-2" onclick="markAllRead()">
                            <i class="fas fa-check-double"></i> Mark All Read
                        </button>
                        <?php endif; ?>
                        <?php if (hasAnyRole(['admin', 'supervisor'])): ?>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#broadcastModal">
                            <i class="fas fa-bullhorn"></i> Send Broadcast
                        </button>
                        <?php endif; ?>
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

                <!-- Notifications List -->
                <div class="card shadow">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-inbox"></i> Your Notifications
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <?php
                        $notifications_data = $notifications->fetchAll(PDO::FETCH_ASSOC);
                        if (count($notifications_data) > 0) {
                            foreach ($notifications_data as $notif) {
                                $unread_class = !$notif['is_read'] ? 'unread' : '';
                                $type_class = 'type-' . $notif['type'];
                                $icon_class = '';
                                
                                switch ($notif['type']) {
                                    case 'info': $icon_class = 'fas fa-info-circle text-info'; break;
                                    case 'success': $icon_class = 'fas fa-check-circle text-success'; break;
                                    case 'warning': $icon_class = 'fas fa-exclamation-triangle text-warning'; break;
                                    case 'danger': $icon_class = 'fas fa-exclamation-circle text-danger'; break;
                                    default: $icon_class = 'fas fa-bell text-primary';
                                }
                                
                                echo "<div class='notification-item p-3 border-bottom {$unread_class} {$type_class}' id='notification-{$notif['id']}'>";
                                echo "<div class='d-flex justify-content-between align-items-start'>";
                                echo "<div class='flex-grow-1'>";
                                echo "<div class='d-flex align-items-center mb-2'>";
                                echo "<i class='{$icon_class} me-2'></i>";
                                echo "<h6 class='mb-0'>" . htmlspecialchars($notif['title']) . "</h6>";
                                if (!$notif['is_read']) {
                                    echo "<span class='badge bg-warning ms-2'>New</span>";
                                }
                                echo "</div>";
                                echo "<p class='mb-2 text-muted'>" . htmlspecialchars($notif['message']) . "</p>";
                                echo "<small class='text-muted'>";
                                echo "<i class='fas fa-clock me-1'></i>";
                                echo date('M d, Y H:i', strtotime($notif['created_at']));
                                echo "</small>";
                                echo "</div>";
                                echo "<div class='notification-actions ms-3'>";
                                if (!$notif['is_read']) {
                                    echo "<button class='btn btn-sm btn-outline-primary me-1' onclick='markAsRead({$notif['id']})' title='Mark as Read'>";
                                    echo "<i class='fas fa-check'></i>";
                                    echo "</button>";
                                }
                                echo "<button class='btn btn-sm btn-outline-danger' onclick='deleteNotification({$notif['id']})' title='Delete'>";
                                echo "<i class='fas fa-trash'></i>";
                                echo "</button>";
                                echo "</div>";
                                echo "</div>";
                                echo "</div>";
                            }
                        } else {
                            echo "<div class='p-4 text-center text-muted'>";
                            echo "<i class='fas fa-bell-slash fa-3x mb-3'></i>";
                            echo "<h5>No Notifications</h5>";
                            echo "<p>You don't have any notifications at this time.</p>";
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Broadcast Modal -->
    <?php if (hasAnyRole(['admin', 'supervisor'])): ?>
    <div class="modal fade" id="broadcastModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-bullhorn"></i> Send Broadcast Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="send_broadcast">
                        
                        <div class="mb-3">
                            <label for="target_role" class="form-label">Target Audience</label>
                            <select class="form-select" name="target_role" required>
                                <option value="">Select Target Role</option>
                                <option value="staff">All Staff</option>
                                <option value="supervisor">All Supervisors</option>
                                <?php if (hasRole('admin')): ?>
                                <option value="admin">All Admins</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="type" class="form-label">Notification Type</label>
                            <select class="form-select" name="type" required>
                                <option value="info">Information</option>
                                <option value="success">Success</option>
                                <option value="warning">Warning</option>
                                <option value="danger">Important/Urgent</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" required maxlength="255" placeholder="Notification title...">
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" name="message" rows="4" required placeholder="Notification message..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Send Broadcast</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        function markAsRead(notificationId) {
            const formData = new FormData();
            formData.append('action', 'mark_read');
            formData.append('notification_id', notificationId);

            fetch('notifications.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(() => {
                const notificationElement = document.getElementById(`notification-${notificationId}`);
                notificationElement.classList.remove('unread');
                notificationElement.querySelector('.badge')?.remove();
                notificationElement.querySelector('.btn-outline-primary')?.remove();
                updateNotificationBadge();
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
        
        function markAllRead() {
            if (confirm('Mark all notifications as read?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="mark_all_read">';
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function deleteNotification(notificationId) {
            if (confirm('Are you sure you want to delete this notification?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="notification_id" value="${notificationId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function updateNotificationBadge() {
            const badge = document.querySelector('.navbar .badge');
            if (badge) {
                const currentCount = parseInt(badge.textContent) || 0;
                const newCount = Math.max(0, currentCount - 1);
                if (newCount === 0) {
                    badge.style.display = 'none';
                } else {
                    badge.textContent = newCount;
                }
            }
        }
    </script>
</body>
</html>
