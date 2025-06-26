<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="index.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            
            <?php if (hasAnyRole(['admin', 'supervisor'])): ?>
            <li class="nav-item">
                <a class="nav-link" href="staff.php">
                    <i class="fas fa-users"></i> Staff Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="shifts.php">
                    <i class="fas fa-clock"></i> Shift Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="assignments.php">
                    <i class="fas fa-calendar-alt"></i> Shift Assignments
                </a>
            </li>
            <?php endif; ?>
            
            <li class="nav-item">
                <a class="nav-link" href="schedule.php">
                    <i class="fas fa-calendar"></i> My Schedule
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="availability.php">
                    <i class="fas fa-check-circle"></i> Availability
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="swap_requests.php">
                    <i class="fas fa-exchange-alt"></i> Shift Swaps
                </a>
            </li>
            
            <?php if (hasAnyRole(['admin', 'supervisor'])): ?>
            <li class="nav-item">
                <a class="nav-link" href="reports.php">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
            </li>
            <?php endif; ?>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Quick Actions</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link" href="emergency.php">
                    <i class="fas fa-exclamation-triangle text-warning"></i> Emergency Coverage
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="notifications.php">
                    <i class="fas fa-bell"></i> Notifications
                    <span class="badge bg-danger">3</span>
                </a>
            </li>
        </ul>
    </div>
</nav>
