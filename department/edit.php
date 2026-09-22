<?php

$page_title = "Edit Department";

include "../components/header.php";
include "../includes/database.php";

/*
|--------------------------------------------------------------------------
| Validate Department ID
|--------------------------------------------------------------------------
*/

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Fetch Department
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        department_name,
        status
    FROM departments
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$department = $stmt->fetch();

if (!$department) {
    header("Location: index.php");
    exit;
}

?>

<div class="dashboard">

    <?php include "../components/sidebar.php"; ?>

    <div class="main">

        <?php include "../components/navbar.php"; ?>

        <div class="content">

            <div class="page-header">

                <h1>Edit Department</h1>

                <a href="index.php" class="add-btn">
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to Departments
                </a>

            </div>

            <div class="form-container">

                <form action="update.php" method="POST">

                    <input
                        type="hidden"
                        name="id"
                        value="<?= htmlspecialchars($department['id']) ?>"
                    >

                    <div class="form-group">

                        <label for="department_name">
                            Department Name
                        </label>

                        <input
                            type="text"
                            id="department_name"
                            name="department_name"
                            value="<?= htmlspecialchars($department['department_name']) ?>"
                            placeholder="Enter department name"
                            maxlength="100"
                            required
                        >

                    </div>

                    <div class="form-group">

                        <label for="status">
                            Status
                        </label>

                        <select id="status" name="status">

                            <option
                                value="1"
                                <?= (int)$department['status'] === 1 ? 'selected' : '' ?>
                            >
                                Active
                            </option>

                            <option
                                value="0"
                                <?= (int)$department['status'] === 0 ? 'selected' : '' ?>
                            >
                                Inactive
                            </option>

                        </select>

                    </div>

                    <button type="submit" class="add-btn">
                        <i class="fa-solid fa-save"></i>
                        Update Department
                    </button>

                </form>

            </div>

        </div>

    </div>

</div>

<?php include "../components/footer.php"; ?>