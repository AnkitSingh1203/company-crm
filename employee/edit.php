<?php

$page_title = "Edit Employee";

include "../components/header.php";
include "../includes/database.php";

/*
|--------------------------------------------------------------------------
| Get Employee ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$employeeId = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Fetch Employee
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT 
        e.*,
        u.email,
        u.role,
        u.status,
        d.department_name,
        dg.designation_name
    FROM employees e
    LEFT JOIN users u 
        ON e.user_id = u.id
    LEFT JOIN departments d 
        ON e.department_id = d.id
    LEFT JOIN designations dg 
        ON e.designation_id = dg.id
    WHERE e.id = ?
    LIMIT 1
");

$stmt->execute([$employeeId]);

$employee = $stmt->fetch();

if (!$employee) {
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Fetch Departments
|--------------------------------------------------------------------------
*/

$departments = $pdo->query("
    SELECT *
    FROM departments
    ORDER BY department_name
")->fetchAll();

/*
|--------------------------------------------------------------------------
| Fetch Designations
|--------------------------------------------------------------------------
*/

$designations = $pdo->query("
    SELECT *
    FROM designations
    ORDER BY designation_name
")->fetchAll();

?>

<div class="dashboard">

    <?php include "../components/sidebar.php"; ?>

    <div class="main">

        <?php include "../components/navbar.php"; ?>

        <div class="content">

            <div class="page-header">

                <h1>Edit Employee</h1>

            </div>

            <form
                class="form-card"
                action="update.php"
                method="POST"
                enctype="multipart/form-data"
            >

                <input
                    type="hidden"
                    name="id"
                    value="<?= (int) $employee['id']; ?>"
                >

                <div class="form-grid">

                    <!-- Employee Code -->

                    <div class="form-group">

                        <label>Employee Code</label>

                        <input
                            type="text"
                            value="<?= htmlspecialchars($employee['employee_code'] ?? ''); ?>"
                            readonly
                        >

                    </div>


                    <!-- First Name -->

                    <div class="form-group">

                        <label>First Name *</label>

                        <input
                            type="text"
                            name="first_name"
                            value="<?= htmlspecialchars($employee['first_name'] ?? ''); ?>"
                            required
                        >

                    </div>


                    <!-- Last Name -->

                    <div class="form-group">

                        <label>Last Name</label>

                        <input
                            type="text"
                            name="last_name"
                            value="<?= htmlspecialchars($employee['last_name'] ?? ''); ?>"
                        >

                    </div>


                    <!-- Email -->

                    <div class="form-group">

                        <label>Email *</label>

                        <input
                            type="email"
                            name="email"
                            value="<?= htmlspecialchars($employee['email'] ?? ''); ?>"
                            required
                        >

                    </div>


                    <!-- Phone -->

                    <div class="form-group">

                        <label>Phone *</label>

                        <input
                            type="text"
                            name="phone"
                            value="<?= htmlspecialchars($employee['phone'] ?? ''); ?>"
                            required
                        >

                    </div>


                    <!-- Gender -->

                    <div class="form-group">

                        <label>Gender</label>

                        <select name="gender">

                            <option value="">Select</option>

                            <option
                                value="Male"
                                <?= (($employee['gender'] ?? '') === 'Male') ? 'selected' : ''; ?>
                            >
                                Male
                            </option>

                            <option
                                value="Female"
                                <?= (($employee['gender'] ?? '') === 'Female') ? 'selected' : ''; ?>
                            >
                                Female
                            </option>

                            <option
                                value="Other"
                                <?= (($employee['gender'] ?? '') === 'Other') ? 'selected' : ''; ?>
                            >
                                Other
                            </option>

                        </select>

                    </div>


                    <!-- Date of Birth -->

                    <div class="form-group">

                        <label>Date of Birth</label>

                        <input
                            type="date"
                            name="dob"
                            value="<?= htmlspecialchars($employee['dob'] ?? ''); ?>"
                        >

                    </div>


                    <!-- Department -->

                    <div class="form-group">

                        <label>Department</label>

                        <select name="department_id">

                            <option value="">Select Department</option>

                            <?php foreach ($departments as $department): ?>

                                <option
                                    value="<?= (int) $department['id']; ?>"
                                    <?= ((string)($employee['department_id'] ?? '') === (string)$department['id']) ? 'selected' : ''; ?>
                                >
                                    <?= htmlspecialchars($department['department_name']); ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- Designation -->

                    <div class="form-group">

                        <label>Designation</label>

                        <select name="designation_id">

                            <option value="">Select Designation</option>

                            <?php foreach ($designations as $designation): ?>

                                <option
                                    value="<?= (int) $designation['id']; ?>"
                                    <?= ((string)($employee['designation_id'] ?? '') === (string)$designation['id']) ? 'selected' : ''; ?>
                                >
                                    <?= htmlspecialchars($designation['designation_name']); ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- Joining Date -->

                    <div class="form-group">

                        <label>Joining Date</label>

                        <input
                            type="date"
                            name="joining_date"
                            value="<?= htmlspecialchars($employee['joining_date'] ?? ''); ?>"
                        >

                    </div>


                    <!-- Salary -->

                    <div class="form-group">

                        <label>Salary</label>

                        <input
                            type="number"
                            name="salary"
                            value="<?= htmlspecialchars($employee['salary'] ?? ''); ?>"
                        >

                    </div>


                    <!-- Role -->

                    <div class="form-group">

                        <label>Role</label>

                        <select name="role">

                            <option
                                value="employee"
                                <?= (($employee['role'] ?? '') === 'employee') ? 'selected' : ''; ?>
                            >
                                Employee
                            </option>

                            <option
                                value="hr"
                                <?= (($employee['role'] ?? '') === 'hr') ? 'selected' : ''; ?>
                            >
                                HR
                            </option>

                            <option
                                value="manager"
                                <?= (($employee['role'] ?? '') === 'manager') ? 'selected' : ''; ?>
                            >
                                Manager
                            </option>

                            <option
                                value="admin"
                                <?= (($employee['role'] ?? '') === 'admin') ? 'selected' : ''; ?>
                            >
                                Admin
                            </option>

                        </select>

                    </div>


                    <!-- Status -->

                    <div class="form-group">

                        <label>Status</label>

                        <select name="status">

                            <option
                                value="active"
                                <?= (($employee['status'] ?? '') === 'active') ? 'selected' : ''; ?>
                            >
                                Active
                            </option>

                            <option
                                value="inactive"
                                <?= (($employee['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>
                            >
                                Inactive
                            </option>

                        </select>

                    </div>


                    <!-- Address -->

                    <div class="form-group full-width">

                        <label>Address</label>

                        <textarea name="address"><?= htmlspecialchars($employee['address'] ?? ''); ?></textarea>

                    </div>


                    <!-- Existing Profile Photo -->

                    <div class="form-group full-width">

                        <label>Current Profile Photo</label>

                        <?php if (!empty($employee['profile_photo'])): ?>

                            <div style="margin-bottom: 10px;">

                                <img
                                    src="../<?= htmlspecialchars($employee['profile_photo']); ?>"
                                    alt="Profile Photo"
                                    style="
                                        width: 90px;
                                        height: 90px;
                                        object-fit: cover;
                                        border-radius: 8px;
                                        border: 1px solid #ddd;
                                    "
                                >

                            </div>

                        <?php else: ?>

                            <p>No profile photo uploaded.</p>

                        <?php endif; ?>

                    </div>


                    <!-- New Profile Photo -->

                    <div class="form-group full-width">

                        <label>Change Profile Photo</label>

                        <input
                            type="file"
                            name="profile_photo"
                            accept="image/*"
                        >

                    </div>

                </div>


                <!-- Submit -->

                <button type="submit" class="submit-btn">

                    <i class="fa-solid fa-floppy-disk"></i>

                    Update Employee

                </button>

            </form>

        </div>

    </div>

</div>

<?php include "../components/footer.php"; ?>