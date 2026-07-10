<?php

$page_title = "Add Employee";

include "../components/header.php";
include "../includes/database.php";

$departments = $pdo->query("SELECT * FROM departments ORDER BY department_name")->fetchAll();

$designations = $pdo->query("SELECT * FROM designations ORDER BY designation_name")->fetchAll();

?>
<?php

$lastEmployee = $pdo->query("
SELECT employee_code
FROM employees
ORDER BY id DESC
LIMIT 1
")->fetch();

if ($lastEmployee) {

    $number = (int) substr($lastEmployee['employee_code'], 3);

    $employeeCode = "EMP" . str_pad($number + 1, 4, "0", STR_PAD_LEFT);
} else {

    $employeeCode = "EMP0001";
}

?>
<div class="dashboard">

    <?php include "../components/sidebar.php"; ?>

    <div class="main">

        <?php include "../components/navbar.php"; ?>

        <div class="content">

            <div class="page-header">

                <h1>Add Employee</h1>

            </div>

           
                <form class="form-card" action="insert.php" method="POST" enctype="multipart/form-data">



                    <div class="form-grid">

                        <div class="form-group">
                            <label>Employee Code</label>
                            <input type="text" name="employee_code" value="<?= $employeeCode ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label>First Name *</label>
                            <input type="text" name="first_name" required>
                        </div>

                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name">
                        </div>
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" required>
                        </div>

                        <div class="form-group">
                            <label>Password *</label>
                            <input type="password" name="password" required>

                            <small>Minimum 8 Characters</small>

                        </div>

                        <div class="form-group">
                            <label>Phone *</label>
                            <input type="text" name="phone" required>
                        </div>
                        <div class="form-group">

                            <label>Gender</label>

                            <select name="gender">

                                <option value="">Select</option>

                                <option value="Male">Male</option>

                                <option value="Female">Female</option>

                                <option value="Other">Other</option>

                            </select>

                        </div>

                        <div class="form-group">
                            <label>Date of Birth</label>
                            <input type="date" name="dob">
                        </div>

                        <div class="form-group">

                            <label>Department</label>

                            <select name="department_id">

                                <option value="">Select Department</option>

                                <?php foreach ($departments as $department): ?>

                                    <option value="<?= $department['id']; ?>">

                                        <?= htmlspecialchars($department['department_name']); ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="form-group">

                            <label>Designation</label>

                            <select name="designation_id">

                                <option value="">Select Designation</option>

                                <?php foreach ($designations as $designation): ?>

                                    <option value="<?= $designation['id']; ?>">

                                        <?= htmlspecialchars($designation['designation_name']); ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="form-group">
                            <label>Joining Date</label>
                            <input type="date" name="joining_date">
                        </div>

                        <div class="form-group">
                            <label>Salary</label>
                            <input type="number" name="salary">
                        </div>

                        <div class="form-group">

                            <label>Role</label>

                            <select name="role">

                                <option value="Employee">Employee</option>

                                <option value="HR">HR</option>

                                <option value="Manager">Manager</option>

                                <option value="Admin">Admin</option>

                            </select>

                        </div>

                        <div class="form-group">

                            <label>Status</label>

                            <select name="status">

                                <option value="Active">Active</option>

                                <option value="Inactive">Inactive</option>

                            </select>

                        </div>

                        <div class="form-group full-width">

                            <label>Address</label>

                            <textarea name="address"></textarea>

                        </div>

                        <div class="form-group full-width">

                            <label>Profile Photo</label>

                            <input type="file" name="profile_photo">

                        </div>
                        </div> <!-- form-grid -->

                        <button type="submit" class="submit-btn">

                            <i class="fa-solid fa-floppy-disk"></i>

                            Save Employee

                        </button>

                </form>

        </div>

    </div>

</div>

<?php include "../components/footer.php"; ?>