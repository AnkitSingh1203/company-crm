<?php

$page_title = "Attendance";

include "../includes/auth.php";
include "../includes/database.php";
include "../components/header.php";

$search = trim($_GET['search'] ?? '');
$attendance_date = trim($_GET['attendance_date'] ?? '');
$status = trim($_GET['status'] ?? '');

$allowedStatuses = [
    'Present',
    'Absent',
    'Late',
    'Half Day',
    'Leave'
];

if (!in_array($status, $allowedStatuses, true)) {
    $status = '';
}

$where = [];
$params = [];

/*
|--------------------------------------------------------------------------
| Base Query
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        a.id,
        a.employee_id,
        a.attendance_date,
        a.check_in,
        a.check_out,
        a.status,
        a.remarks,
        e.employee_code,
        e.first_name,
        e.last_name,
        d.department_name
    FROM attendance a
    INNER JOIN employees e
        ON a.employee_id = e.id
    LEFT JOIN departments d
        ON e.department_id = d.id
";

/*
|--------------------------------------------------------------------------
| Search Filter
|--------------------------------------------------------------------------
*/

if ($search !== '') {

    $where[] = "(
        e.employee_code LIKE ?
        OR e.first_name LIKE ?
        OR e.last_name LIKE ?
        OR CONCAT(e.first_name, ' ', e.last_name) LIKE ?
        OR d.department_name LIKE ?
    )";

    $keyword = '%' . $search . '%';

    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
}

/*
|--------------------------------------------------------------------------
| Date Filter
|--------------------------------------------------------------------------
*/

if ($attendance_date !== '') {

    $where[] = "a.attendance_date = ?";
    $params[] = $attendance_date;
}

/*
|--------------------------------------------------------------------------
| Status Filter
|--------------------------------------------------------------------------
*/

if ($status !== '') {

    $where[] = "a.status = ?";
    $params[] = $status;
}

/*
|--------------------------------------------------------------------------
| WHERE Clause
|--------------------------------------------------------------------------
*/

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

$limit = 10;

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

if ($page < 1) {
    $page = 1;
}

$countSql = "
    SELECT COUNT(*)
    FROM attendance a
    INNER JOIN employees e
        ON a.employee_id = e.id
    LEFT JOIN departments d
        ON e.department_id = d.id
";

if (!empty($where)) {
    $countSql .= " WHERE " . implode(" AND ", $where);
}

$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);

$totalRecords = (int) $countStmt->fetchColumn();

$totalPages = (int) ceil($totalRecords / $limit);

$offset = ($page - 1) * $limit;

/*
|--------------------------------------------------------------------------
| Fetch Attendance
|--------------------------------------------------------------------------
*/

$sql .= "
    ORDER BY
        a.attendance_date DESC,
        a.id DESC
    LIMIT $limit OFFSET $offset
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$attendanceRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="dashboard">

    <?php include "../components/sidebar.php"; ?>

    <div class="main">

        <?php include "../components/navbar.php"; ?>

        <div class="content">

            <div class="page-header">

                <h1>Attendance</h1>

                <a href="add.php" class="add-btn">
                    <i class="fa-solid fa-plus"></i>
                    Add Attendance
                </a>

            </div>


            <!-- Filters -->

            <div class="search-bar">

                <form method="GET">

                    <input
                        type="text"
                        name="search"
                        placeholder="Search employee..."
                        value="<?= htmlspecialchars($search) ?>">

                    <input
                        type="date"
                        name="attendance_date"
                        value="<?= htmlspecialchars($attendance_date) ?>">

                    <select name="status">

                        <option value="">All Status</option>

                        <?php foreach ($allowedStatuses as $attendanceStatus): ?>

                            <option
                                value="<?= htmlspecialchars($attendanceStatus) ?>"
                                <?= $status === $attendanceStatus ? 'selected' : '' ?>>
                                <?= htmlspecialchars($attendanceStatus) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                    <button type="submit">
                        <i class="fa-solid fa-search"></i>
                    </button>

                    <?php if ($search !== '' || $attendance_date !== '' || $status !== ''): ?>

                        <a
                            href="index.php"
                            class="action-btn"
                            title="Clear Filters">
                            <i class="fa-solid fa-xmark"></i>
                        </a>

                    <?php endif; ?>

                </form>

            </div>


            <!-- Attendance Table -->

            <div class="table-container">

                <table class="employee-table">

                    <thead>

                        <tr>

                            <th>Date</th>
                            <th>Employee ID</th>
                            <th>Employee Name</th>
                            <th>Department</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Status</th>
                            <th>Remarks</th>
                            <th>Action</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php if (!empty($attendanceRecords)): ?>

                            <?php foreach ($attendanceRecords as $record): ?>

                                <tr>

                                    <td>
                                        <?= htmlspecialchars(
                                            date(
                                                'd M Y',
                                                strtotime($record['attendance_date'])
                                            )
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $record['employee_code']
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $record['first_name'] . ' ' . $record['last_name']
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $record['department_name'] ?? 'N/A'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?php if (!empty($record['check_in'])): ?>

                                            <?= htmlspecialchars(
                                                date(
                                                    'h:i A',
                                                    strtotime($record['check_in'])
                                                )
                                            ) ?>

                                        <?php else: ?>

                                            -

                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if (!empty($record['check_out'])): ?>

                                            <?= htmlspecialchars(
                                                date(
                                                    'h:i A',
                                                    strtotime($record['check_out'])
                                                )
                                            ) ?>

                                        <?php else: ?>

                                            -

                                        <?php endif; ?>
                                    </td>

                                    <td>

                                        <?php
                                        $statusClass = strtolower(
                                            str_replace(
                                                ' ',
                                                '-',
                                                $record['status']
                                            )
                                        );
                                        ?>

                                        <span class="status <?= htmlspecialchars($statusClass) ?>">
                                            <?= htmlspecialchars($record['status']) ?>
                                        </span>

                                    </td>

                                    <td>
                                        <?= !empty($record['remarks'])
                                            ? htmlspecialchars($record['remarks'])
                                            : '-' ?>
                                    </td>

                                    <td>

                                        <a
                                            href="edit.php?id=<?= (int) $record['id'] ?>"
                                            class="action-btn edit"
                                            title="Edit Attendance">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>

                                        <form
                                            action="delete.php"
                                            method="POST"
                                            class="delete-form"
                                            style="display: inline;">
                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int) $record['id'] ?>">

                                            <button
                                                type="submit"
                                                class="action-btn delete delete-btn"
                                                title="Delete Attendance">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="9"
                                    style="text-align: center; padding: 30px;">
                                    No Attendance Records Found
                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>


            <!-- Pagination -->

            <?php if ($totalPages > 1): ?>

                <div class="pagination">

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                        <a
                            href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&attendance_date=<?= urlencode($attendance_date) ?>&status=<?= urlencode($status) ?>"
                            class="<?= $page === $i ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>

                    <?php endfor; ?>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>


<?php include "../components/footer.php"; ?>


<script>

document.querySelectorAll('.delete-form').forEach(function (form) {

    form.addEventListener('submit', function (event) {

        event.preventDefault();

        Swal.fire({

            title: 'Delete Attendance?',
            text: 'This action cannot be undone.',
            icon: 'warning',

            showCancelButton: true,

            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',

            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel'

        }).then(function (result) {

            if (result.isConfirmed) {
                form.submit();
            }

        });

    });

});

</script>