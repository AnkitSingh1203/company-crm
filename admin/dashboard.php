<?php

require_once __DIR__ . '/../includes/auth.php';
require_super_admin();

$user = current_user();

require_once __DIR__ . '/../includes/database.php';

$stats = [
    'employees' => 0,
    'active_employees' => 0,
    'departments' => 0,
    'designations' => 0,
    'today_attendance' => 0,
    'today_late' => 0,
    'today_leave' => 0,
];

$stats['employees'] = (int) $pdo->query('SELECT COUNT(*) FROM employees')->fetchColumn();
$stats['active_employees'] = (int) $pdo->query("SELECT COUNT(*) FROM employees e INNER JOIN users u ON u.id = e.user_id WHERE LOWER(u.status) = 'active'")->fetchColumn();
$stats['departments'] = (int) $pdo->query("SELECT COUNT(*) FROM departments WHERE status = 1")->fetchColumn();
$stats['designations'] = (int) $pdo->query("SELECT COUNT(*) FROM designations WHERE status = 1")->fetchColumn();

$today = date('Y-m-d');

$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE attendance_date = ?");
$stmt->execute([$today]);
$stats['today_attendance'] = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE attendance_date = ? AND LOWER(status) = 'late'");
$stmt->execute([$today]);
$stats['today_late'] = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE attendance_date = ? AND LOWER(status) = 'leave'");
$stmt->execute([$today]);
$stats['today_leave'] = (int) $stmt->fetchColumn();

$recentEmployees = $pdo->query("
    SELECT e.employee_code, e.first_name, e.last_name, d.department_name, u.status
    FROM employees e
    LEFT JOIN departments d ON d.id = e.department_id
    LEFT JOIN users u ON u.id = e.user_id
    ORDER BY e.id DESC
    LIMIT 5
")->fetchAll();

$recentAttendanceStmt = $pdo->prepare("
    SELECT a.attendance_date, a.check_in, a.check_out, a.status,
           e.employee_code, e.first_name, e.last_name
    FROM attendance a
    INNER JOIN employees e ON e.id = a.employee_id
    WHERE a.attendance_date = ?
    ORDER BY COALESCE(a.check_in, '23:59:59') ASC
    LIMIT 8
");
$recentAttendanceStmt->execute([$today]);
$recentAttendance = $recentAttendanceStmt->fetchAll();

include __DIR__ . '/../components/header.php';
?>

<div class="dashboard">
    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <div class="main">
        <?php include __DIR__ . '/../components/navbar.php'; ?>

        <div class="content admin-command-center">
            <div class="page-header">
                <div>
                    <span class="page-eyebrow">SUPER ADMIN · COMMAND CENTER</span>
                    <h1>Good <?= date('H') < 12 ? 'Morning' : (date('H') < 17 ? 'Afternoon' : 'Evening'); ?>, <?= e($user['email']); ?></h1>
                    <p>Monitor people, attendance and the operational health of your company from one place.</p>
                </div>
                <div class="page-date">
                    <strong><?= date('D, d M Y'); ?></strong>
                    <span><?= date('h:i A'); ?></span>
                </div>
            </div>

            <div class="kpi-grid">
                <div class="kpi-card"><span>Employees</span><strong><?= $stats['employees']; ?></strong><small>Total workforce</small></div>
                <div class="kpi-card"><span>Active Employees</span><strong><?= $stats['active_employees']; ?></strong><small>Currently active</small></div>
                <div class="kpi-card"><span>Departments</span><strong><?= $stats['departments']; ?></strong><small>Active departments</small></div>
                <div class="kpi-card"><span>Designations</span><strong><?= $stats['designations']; ?></strong><small>Active designations</small></div>
                <div class="kpi-card"><span>Today Attendance</span><strong><?= $stats['today_attendance']; ?></strong><small>Attendance records</small></div>
                <div class="kpi-card"><span>Late Today</span><strong><?= $stats['today_late']; ?></strong><small>Late marked</small></div>
                <div class="kpi-card"><span>Leave Today</span><strong><?= $stats['today_leave']; ?></strong><small>Leave marked</small></div>
            </div>

            <div class="dashboard-grid">
                <section class="dashboard-panel">
                    <div class="panel-heading"><div><span class="panel-eyebrow">PEOPLE</span><h2>Recent Employees</h2></div><a href="../employee/index.php">View all</a></div>
                    <div class="data-list">
                        <?php if (!$recentEmployees): ?>
                            <div class="empty-state">No employees found.</div>
                        <?php else: ?>
                            <?php foreach ($recentEmployees as $employee): ?>
                                <div class="data-row">
                                    <div class="avatar"><?= e(strtoupper(substr($employee['first_name'], 0, 1))); ?></div>
                                    <div class="row-main"><strong><?= e(trim($employee['first_name'] . ' ' . $employee['last_name'])); ?></strong><span><?= e($employee['employee_code']); ?> · <?= e($employee['department_name'] ?? 'Unassigned'); ?></span></div>
                                    <span class="status-pill <?= normalize_role($employee['status']) === 'active' ? 'active' : 'inactive'; ?>"><?= e(ucfirst($employee['status'] ?? 'Unknown')); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="dashboard-panel">
                    <div class="panel-heading"><div><span class="panel-eyebrow">TODAY</span><h2>Attendance Activity</h2></div><a href="../attendance/index.php">Manage</a></div>
                    <div class="data-list">
                        <?php if (!$recentAttendance): ?>
                            <div class="empty-state">No attendance records for today.</div>
                        <?php else: ?>
                            <?php foreach ($recentAttendance as $attendance): ?>
                                <div class="data-row">
                                    <div class="avatar muted-avatar"><?= e(strtoupper(substr($attendance['first_name'], 0, 1))); ?></div>
                                    <div class="row-main"><strong><?= e(trim($attendance['first_name'] . ' ' . $attendance['last_name'])); ?></strong><span><?= e($attendance['check_in'] ?? '--'); ?> → <?= e($attendance['check_out'] ?? '--'); ?></span></div>
                                    <span class="status-pill"><?= e($attendance['status']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
            </div>

            <section class="quick-actions">
                <div><span class="panel-eyebrow">QUICK ACTIONS</span><h2>Manage Operations</h2></div>
                <div class="action-grid">
                    <a href="../employee/add.php"><i class="fa-solid fa-user-plus"></i><span>Add Employee</span></a>
                    <a href="../employee/index.php"><i class="fa-solid fa-users"></i><span>Employees</span></a>
                    <a href="../attendance/add.php"><i class="fa-solid fa-calendar-check"></i><span>Mark Attendance</span></a>
                    <a href="../department/index.php"><i class="fa-solid fa-building"></i><span>Departments</span></a>
                    <a href="../designation/index.php"><i class="fa-solid fa-id-badge"></i><span>Designations</span></a>
                </div>
            </section>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>
