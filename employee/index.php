<?php

$page_title = "Employees";

include "../components/header.php";

?>

<?php

include "../includes/database.php";

$employees = $pdo->query("
SELECT
e.*,
u.email,
u.status,
d.department_name,
dg.designation_name
FROM employees e
LEFT JOIN users u ON e.user_id = u.id
LEFT JOIN departments d ON e.department_id = d.id
LEFT JOIN designations dg ON e.designation_id = dg.id
ORDER BY e.id DESC
")->fetchAll();

?>

<div class="dashboard">


    <?php include "../components/sidebar.php"; ?>


    <div class="main">


        <?php include "../components/navbar.php"; ?>


        <div class="content">


            <div class="page-header">

                <h1>Employees</h1>

                <a href="add.php" class="add-btn">

                    <i class="fa-solid fa-plus"></i>

                    Add Employee

                </a>

            </div>


            <div class="table-container">

                <table class="employee-table">

                    <thead>

                        <tr>
                            <th>Photo</th>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>

                    </thead>


                    <tbody>

                        <?php if (count($employees) > 0): ?>

                            <?php foreach ($employees as $employee): ?>

                                <tr>

                                    <td>
                                        <?php if (!empty($employee['profile_photo'])): ?>

                                            <img src="../uploads/<?= htmlspecialchars($employee['profile_photo']) ?>" class="emp-photo">

                                        <?php else: ?>

                                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($employee['first_name']) ?>" class="emp-photo">

                                        <?php endif; ?>
                                    </td>

                                    <td><?= htmlspecialchars($employee['employee_code']) ?></td>

                                    <td><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?></td>

                                    <td><?= htmlspecialchars($employee['department_name']) ?></td>

                                    <td><?= htmlspecialchars($employee['designation_name']) ?></td>

                                    <td><?= htmlspecialchars($employee['email']) ?></td>

                                    <td>
                                        <span class="status <?= strtolower($employee['status']) ?>">
                                            <?= htmlspecialchars($employee['status']) ?>
                                        </span>
                                    </td>

                                    <td>

                                        <a href="view.php?id=<?= $employee['id'] ?>" class="action-btn view">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                        <a href="edit.php?id=<?= $employee['id'] ?>" class="action-btn edit">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>

                                        <a href="delete.php?id=<?= $employee['id'] ?>"
                                            class="action-btn delete"
                                            onclick="return confirm('Are you sure you want to delete this employee?')">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="8" style="text-align:center;padding:30px;">
                                    No Employee Found
                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>


                </table>

            </div>

        </div> <!-- content -->

    </div> <!-- main -->

</div> <!-- dashboard -->


<?php include "../components/footer.php"; ?>