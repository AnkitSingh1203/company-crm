<?php

$page_title = "View Employee";

include "../components/header.php";
?>
<link rel="stylesheet" href="../assets/css/employee-view.css">

<?php
include "../includes/database.php";


// Get employee ID
$employee_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$employee_id) {
    header("Location: index.php");
    exit;
}


// Fetch employee details
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

$stmt->execute([$employee_id]);

$employee = $stmt->fetch();


// Employee not found
if (!$employee) {
    header("Location: index.php");
    exit;
}


// Employee full name
$full_name = trim(
    $employee['first_name'] . ' ' . $employee['last_name']
);


// Profile photo
$profile_photo = !empty($employee['profile_photo'])
    ? "../uploads/" . $employee['profile_photo']
    : "https://ui-avatars.com/api/?name=" . urlencode($full_name) . "&background=0271C4&color=ffffff&size=160";


// Format dates
$dob = !empty($employee['dob'])
    ? date("d M Y", strtotime($employee['dob']))
    : "Not provided";

$joining_date = !empty($employee['joining_date'])
    ? date("d M Y", strtotime($employee['joining_date']))
    : "Not provided";


// Salary
$salary = $employee['salary'] !== null && $employee['salary'] !== ''
    ? "₹" . number_format((float) $employee['salary'], 2)
    : "Not provided";

?>

<div class="dashboard">

    <?php include "../components/sidebar.php"; ?>

    <div class="main">

        <?php include "../components/navbar.php"; ?>

        <div class="content">

            <!-- Page Header -->
            <div class="page-header">

                <div>
                    <h1>Employee Details</h1>
                    <p>View employee information</p>
                </div>

                <div class="page-actions">

                    <a href="index.php" class="secondary-btn">
                        <i class="fa-solid fa-arrow-left"></i>
                        Back
                    </a>

                    <a href="edit.php?id=<?= (int) $employee['id']; ?>" class="add-btn">
                        <i class="fa-solid fa-pen"></i>
                        Edit Employee
                    </a>

                </div>

            </div>


            <!-- Employee Profile -->
            <div class="employee-profile-card">

                <div class="employee-profile-header">

                    <div class="employee-profile-info">

                        <img
                            src="<?= htmlspecialchars($profile_photo); ?>"
                            alt="<?= htmlspecialchars($full_name); ?>"
                            class="employee-profile-photo"
                        >

                        <div>

                            <h2>
                                <?= htmlspecialchars($full_name); ?>
                            </h2>

                            <p>
                                <?= htmlspecialchars($employee['designation_name'] ?? 'Not assigned'); ?>
                            </p>

                            <span class="employee-code">
                                <?= htmlspecialchars($employee['employee_code']); ?>
                            </span>

                        </div>

                    </div>


                    <div>

                        <span class="status <?= strtolower(htmlspecialchars($employee['status'])); ?>">
                            <?= htmlspecialchars($employee['status']); ?>
                        </span>

                    </div>

                </div>


                <!-- Personal Information -->
                <div class="employee-section">

                    <div class="employee-section-title">

                        <i class="fa-solid fa-user"></i>

                        <h3>Personal Information</h3>

                    </div>


                    <div class="employee-details-grid">

                        <div class="employee-detail-item">

                            <span>First Name</span>

                            <strong>
                                <?= htmlspecialchars($employee['first_name']); ?>
                            </strong>

                        </div>


                        <div class="employee-detail-item">

                            <span>Last Name</span>

                            <strong>
                                <?= !empty($employee['last_name'])
                                    ? htmlspecialchars($employee['last_name'])
                                    : "Not provided"; ?>
                            </strong>

                        </div>


                        <div class="employee-detail-item">

                            <span>Gender</span>

                            <strong>
                                <?= !empty($employee['gender'])
                                    ? htmlspecialchars($employee['gender'])
                                    : "Not provided"; ?>
                            </strong>

                        </div>


                        <div class="employee-detail-item">

                            <span>Date of Birth</span>

                            <strong>
                                <?= htmlspecialchars($dob); ?>
                            </strong>

                        </div>

                    </div>

                </div>


                <!-- Contact Information -->
                <div class="employee-section">

                    <div class="employee-section-title">

                        <i class="fa-solid fa-address-book"></i>

                        <h3>Contact Information</h3>

                    </div>


                    <div class="employee-details-grid">

                        <div class="employee-detail-item">

                            <span>Email</span>

                            <strong>
                                <?= htmlspecialchars($employee['email'] ?? 'Not provided'); ?>
                            </strong>

                        </div>


                        <div class="employee-detail-item">

                            <span>Phone</span>

                            <strong>
                                <?= !empty($employee['phone'])
                                    ? htmlspecialchars($employee['phone'])
                                    : "Not provided"; ?>
                            </strong>

                        </div>


                        <div class="employee-detail-item full-width">

                            <span>Address</span>

                            <strong>
                                <?= !empty($employee['address'])
                                    ? nl2br(htmlspecialchars($employee['address']))
                                    : "Not provided"; ?>
                            </strong>

                        </div>

                    </div>

                </div>


                <!-- Employment Information -->
                <div class="employee-section">

                    <div class="employee-section-title">

                        <i class="fa-solid fa-briefcase"></i>

                        <h3>Employment Information</h3>

                    </div>


                    <div class="employee-details-grid">

                        <div class="employee-detail-item">

                            <span>Employee Code</span>

                            <strong>
                                <?= htmlspecialchars($employee['employee_code']); ?>
                            </strong>

                        </div>


                        <div class="employee-detail-item">

                            <span>Department</span>

                            <strong>
                                <?= !empty($employee['department_name'])
                                    ? htmlspecialchars($employee['department_name'])
                                    : "Not assigned"; ?>
                            </strong>

                        </div>


                        <div class="employee-detail-item">

                            <span>Designation</span>

                            <strong>
                                <?= !empty($employee['designation_name'])
                                    ? htmlspecialchars($employee['designation_name'])
                                    : "Not assigned"; ?>
                            </strong>

                        </div>


                        <div class="employee-detail-item">

                            <span>Joining Date</span>

                            <strong>
                                <?= htmlspecialchars($joining_date); ?>
                            </strong>

                        </div>


                        <div class="employee-detail-item">

                            <span>Role</span>

                            <strong>
                                <?= !empty($employee['role'])
                                    ? htmlspecialchars($employee['role'])
                                    : "Not assigned"; ?>
                            </strong>

                        </div>


                        <div class="employee-detail-item">

                            <span>Salary</span>

                            <strong>
                                <?= htmlspecialchars($salary); ?>
                            </strong>

                        </div>


                        <div class="employee-detail-item">

                            <span>Status</span>

                            <strong>
                                <?= htmlspecialchars($employee['status']); ?>
                            </strong>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<?php include "../components/footer.php"; ?>