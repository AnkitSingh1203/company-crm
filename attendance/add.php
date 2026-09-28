<?php

$page_title = "Add Attendance";

include "../includes/auth.php";
include "../includes/database.php";
include "../components/header.php";

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
    LEFT JOIN departments d
        ON e.department_id = d.id
    INNER JOIN users u
        ON e.user_id = u.id
    WHERE u.status = 'active'    
ORDER BY e.first_name ASC, e.last_name ASC
");

$employees = $employeeStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="dashboard">

    <?php include "../components/sidebar.php"; ?>

    <div class="main">

        <?php include "../components/navbar.php"; ?>

        <div class="content">

            <div class="page-header">

                <h1>Add Attendance</h1>

                <a href="index.php" class="add-btn">
                    <i class="fa-solid fa-arrow-left"></i>
                    Back
                </a>

            </div>


            <div class="form-container">

                <form
                    action="insert.php"
                    method="POST"
                >

                    <!-- Employee -->

                    <div class="form-group">

                        <label for="employee_id">
                            Employee <span>*</span>
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
                                >

                                    <?= htmlspecialchars(
                                        $employee['employee_code']
                                    ) ?>

                                    -
                                    <?= htmlspecialchars(
                                        $employee['first_name'] . ' ' . $employee['last_name']
                                    ) ?>

                                    <?php if (!empty($employee['department_name'])): ?>

                                        (
                                        <?= htmlspecialchars(
                                            $employee['department_name']
                                        ) ?>
                                        )

                                    <?php endif; ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- Attendance Date -->

                    <div class="form-group">

                        <label for="attendance_date">
                            Attendance Date <span>*</span>
                        </label>

                        <input
                            type="date"
                            name="attendance_date"
                            id="attendance_date"
                            value="<?= date('Y-m-d') ?>"
                            required
                        >

                    </div>


                    <!-- Status -->

                    <div class="form-group">

                        <label for="status">
                            Status <span>*</span>
                        </label>

                        <select
                            name="status"
                            id="status"
                            required
                        >

                            <option value="Present">
                                Present
                            </option>

                            <option value="Absent">
                                Absent
                            </option>

                            <option value="Late">
                                Late
                            </option>

                            <option value="Half Day">
                                Half Day
                            </option>

                            <option value="Leave">
                                Leave
                            </option>

                        </select>

                    </div>


                    <!-- Check In -->

                    <div class="form-group">

                        <label for="check_in">
                            Check In
                        </label>

                        <input
                            type="time"
                            name="check_in"
                            id="check_in"
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
                        >

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
                            placeholder="Enter remarks..."
                        ></textarea>

                    </div>


                    <!-- Buttons -->

                    <div class="form-actions">

                        <button
                            type="submit"
                            class="btn-primary"
                        >
                            <i class="fa-solid fa-save"></i>
                            Save Attendance
                        </button>

                        <a
                            href="index.php"
                            class="btn-secondary"
                        >
                            Cancel
                        </a>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

<?php include "../components/footer.php"; ?>