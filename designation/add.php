<?php

$page_title = "Add Designation";

include "../components/header.php";
include "../includes/database.php";

$departmentStmt = $pdo->prepare("
    SELECT
        id,
        department_name
    FROM departments
    WHERE status = 1
    ORDER BY department_name ASC
");

$departmentStmt->execute();

$departments = $departmentStmt->fetchAll();

?>

<div class="dashboard">

    <?php include "../components/sidebar.php"; ?>

    <div class="main">

        <?php include "../components/navbar.php"; ?>

        <div class="content">

            <div class="page-header">

                <h1>Add Designation</h1>

                <a href="index.php" class="add-btn">
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to Designations
                </a>

            </div>

            <div class="form-container">

                <form action="insert.php" method="POST">

                    <div class="form-group">

                        <label for="designation_name">
                            Designation Name
                        </label>

                        <input
                            type="text"
                            id="designation_name"
                            name="designation_name"
                            placeholder="Enter designation name"
                            maxlength="100"
                            required
                        >

                    </div>

                    <div class="form-group">

                        <label for="department_id">
                            Department
                        </label>

                        <select
                            id="department_id"
                            name="department_id"
                            required
                        >

                            <option value="">
                                Select Department
                            </option>

                            <?php foreach ($departments as $department): ?>

                                <option value="<?= htmlspecialchars($department['id']) ?>">

                                    <?= htmlspecialchars($department['department_name']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="form-group">

                        <label for="status">
                            Status
                        </label>

                        <select id="status" name="status">

                            <option value="1">
                                Active
                            </option>

                            <option value="0">
                                Inactive
                            </option>

                        </select>

                    </div>

                    <button type="submit" class="add-btn">

                        <i class="fa-solid fa-plus"></i>

                        Add Designation

                    </button>

                </form>

            </div>

        </div>

    </div>

</div>

<?php include "../components/footer.php"; ?>
