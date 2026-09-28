<?php

include "../includes/auth.php";
include "../includes/database.php";


/*
|--------------------------------------------------------------------------
| Get Attendance ID
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);


if (!$id || $id <= 0) {

    $_SESSION['error'] = "Invalid attendance record.";

    header("Location: index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Fetch Attendance Record
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
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
    WHERE a.id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$attendance = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$attendance) {

    $_SESSION['error'] = "Attendance record not found.";

    header("Location: index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Fetch Active Employees
|--------------------------------------------------------------------------
*/

$employeeStmt = $pdo->query("
    SELECT
        e.id,
        e.employee_code,
        e.first_name,
        e.last_name,
        d.department_name
    FROM employees e
    INNER JOIN users u
        ON e.user_id = u.id
    LEFT JOIN departments d
        ON e.department_id = d.id
    WHERE u.status = 'active'
    ORDER BY e.first_name ASC, e.last_name ASC
");

$employees = $employeeStmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Allowed Statuses
|--------------------------------------------------------------------------
*/

$allowedStatuses = [
    'Present',
    'Absent',
    'Late',
    'Half Day',
    'Leave'
];

?>


<?php include "../components/header.php"; ?>

<div class="dashboard">

    <?php include "../components/sidebar.php"; ?>

    <div class="main">

        <?php include "../components/navbar.php"; ?>

        <div class="content">


        <div class="content">

            <!-- Page Header -->

            <div class="page-header">

                <div>

                    <h1>Edit Attendance</h1>

                    <p>
                        Update employee attendance details.
                    </p>

                </div>

                <div>

                    <a
                        href="index.php"
                        class="btn btn-secondary"
                    >
                        ← Back
                    </a>

                </div>

            </div>


            <!-- Error Message -->

            <?php if (!empty($_SESSION['error'])): ?>

                <div class="alert alert-danger">

                    <?= htmlspecialchars($_SESSION['error']) ?>

                </div>

                <?php unset($_SESSION['error']); ?>

            <?php endif; ?>


            <!-- Edit Attendance Form -->

            <div class="card">

                <form
                    action="update.php"
                    method="POST"
                >

                    <!-- Attendance ID -->

                    <input
                        type="hidden"
                        name="id"
                        value="<?= (int) $attendance['id'] ?>"
                    >


                    <div class="form-row">


                        <!-- Employee -->

                        <div class="form-group">

                            <label for="employee_id">
                                Employee
                            </label>

                            <select
                                name="employee_id"
                                id="employee_id"
                                required
                            >

                                <option value="">
                                    Select Employee
                                </option>


                                <?php foreach ($employees as $employee): ?>

                                    <option
                                        value="<?= (int) $employee['id'] ?>"
                                        <?= (
                                            (int) $employee['id']
                                            === (int) $attendance['employee_id']
                                        ) ? 'selected' : '' ?>
                                    >

                                        <?= htmlspecialchars(
                                            $employee['employee_code']
                                        ) ?>

                                        -
                                        
                                        <?= htmlspecialchars(
                                            $employee['first_name']
                                        ) ?>

                                        <?= htmlspecialchars(
                                            $employee['last_name']
                                        ) ?>

                                        <?php if (!empty($employee['department_name'])): ?>

                                            -
                                            <?= htmlspecialchars(
                                                $employee['department_name']
                                            ) ?>

                                        <?php endif; ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- Attendance Date -->

                        <div class="form-group">

                            <label for="attendance_date">
                                Attendance Date
                            </label>

                            <input
                                type="date"
                                name="attendance_date"
                                id="attendance_date"
                                value="<?= htmlspecialchars(
                                    $attendance['attendance_date']
                                ) ?>"
                                required
                            >

                        </div>

                    </div>


                    <div class="form-row">


                        <!-- Check In -->

                        <div class="form-group">

                            <label for="check_in">
                                Check In
                            </label>

                            <input
                                type="time"
                                name="check_in"
                                id="check_in"
                                value="<?= !empty($attendance['check_in'])
                                    ? htmlspecialchars(
                                        substr(
                                            $attendance['check_in'],
                                            0,
                                            5
                                        )
                                    )
                                    : '' ?>"
                            >

                        </div>


                        <!-- Check Out -->

                        <div class="form-group">

                            <label for="check_out">
                                Check Out
                            </label>

                            <input
                                type="time"
                                name="check_out"
                                id="check_out"
                                value="<?= !empty($attendance['check_out'])
                                    ? htmlspecialchars(
                                        substr(
                                            $attendance['check_out'],
                                            0,
                                            5
                                        )
                                    )
                                    : '' ?>"
                            >

                        </div>


                        <!-- Status -->

                        <div class="form-group">

                            <label for="status">
                                Status
                            </label>

                            <select
                                name="status"
                                id="status"
                                required
                            >

                                <?php foreach ($allowedStatuses as $status): ?>

                                    <option
                                        value="<?= htmlspecialchars($status) ?>"
                                        <?= $attendance['status'] === $status
                                            ? 'selected'
                                            : '' ?>
                                    >

                                        <?= htmlspecialchars($status) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                    </div>


                    <!-- Remarks -->

                    <div class="form-group">

                        <label for="remarks">
                            Remarks
                        </label>

                        <textarea
                            name="remarks"
                            id="remarks"
                            rows="4"
                            maxlength="255"
                            placeholder="Enter attendance remarks..."
                        ><?= htmlspecialchars(
                            $attendance['remarks'] ?? ''
                        ) ?></textarea>

                    </div>


                    <!-- Current Record Info -->

                    <div class="attendance-info">

                        <strong>Current Record:</strong>

                        <?= htmlspecialchars(
                            $attendance['employee_code']
                        ) ?>

                        -

                        <?= htmlspecialchars(
                            $attendance['first_name']
                        ) ?>

                        <?= htmlspecialchars(
                            $attendance['last_name']
                        ) ?>

                    </div>


                    <!-- Buttons -->

                    <div class="form-actions">

                        <a
                            href="index.php"
                            class="btn btn-secondary"
                        >
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Update Attendance
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>


<?php include "../components/footer.php"; ?>