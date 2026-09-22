<?php

$page_title = "Add Department";

include "../components/header.php";

?>

<div class="dashboard">

    <?php include "../components/sidebar.php"; ?>

    <div class="main">

        <?php include "../components/navbar.php"; ?>

        <div class="content">

            <div class="page-header">

                <h1>Add Department</h1>

                <a href="index.php" class="add-btn">
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to Departments
                </a>

            </div>

            <div class="form-container">

                <form action="insert.php" method="POST">

                    <div class="form-group">

                        <label for="department_name">
                            Department Name
                        </label>

                        <input
                            type="text"
                            id="department_name"
                            name="department_name"
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

                            <option value="1">Active</option>
                            <option value="0">Inactive</option>

                        </select>

                    </div>

                    <button type="submit" class="add-btn">
                        <i class="fa-solid fa-plus"></i>
                        Add Department
                    </button>

                </form>

            </div>

        </div>

    </div>

</div>

<?php include "../components/footer.php"; ?>