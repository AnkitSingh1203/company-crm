<?php

require_once "../includes/database.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Get Employee ID
|--------------------------------------------------------------------------
*/

$employee_id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($employee_id <= 0) {
    die("Invalid employee ID.");
}

/*
|--------------------------------------------------------------------------
| Get Employee + User Information
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        e.id,
        e.user_id,
        e.profile_photo,
        e.employee_code,
        u.email AS current_email
    FROM employees e
    LEFT JOIN users u
        ON e.user_id = u.id
    WHERE e.id = ?
    LIMIT 1
");

$stmt->execute([$employee_id]);

$employee = $stmt->fetch();

if (!$employee) {
    die("Employee not found.");
}

/*
|--------------------------------------------------------------------------
| Get Form Data
|--------------------------------------------------------------------------
*/

$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$gender = $_POST['gender'] ?? '';
$dob = $_POST['dob'] ?? '';
$department_id = $_POST['department_id'] ?? '';
$designation_id = $_POST['designation_id'] ?? '';
$joining_date = $_POST['joining_date'] ?? '';
$salary = $_POST['salary'] ?? '';
$role = $_POST['role'] ?? '';
$status = $_POST['status'] ?? '';
$address = trim($_POST['address'] ?? '');

/*
|--------------------------------------------------------------------------
| Required Field Validation
|--------------------------------------------------------------------------
*/

if (empty($first_name)) {
    die("First name is required.");
}

if (empty($email)) {
    die("Email is required.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email address.");
}

if (empty($phone)) {
    die("Phone number is required.");
}

/*
|--------------------------------------------------------------------------
| Check Email Already Exists
|--------------------------------------------------------------------------
|
| Same employee's current email is allowed.
| Another employee cannot use this email.
|
*/

$checkEmail = $pdo->prepare("
    SELECT id
    FROM users
    WHERE email = ?
    AND id != ?
    LIMIT 1
");

$checkEmail->execute([
    $email,
    $employee['user_id']
]);

if ($checkEmail->fetch()) {
    die("Email already exists.");
}

/*
|--------------------------------------------------------------------------
| Validate Role
|--------------------------------------------------------------------------
*/

$allowedRoles = [
    'employee',
    'hr',
    'manager',
    'admin'
];

if (!in_array($role, $allowedRoles, true)) {
    die("Invalid role selected.");
}

/*
|--------------------------------------------------------------------------
| Validate Status
|--------------------------------------------------------------------------
*/

$allowedStatuses = [
    'active',
    'inactive'
];

if (!in_array($status, $allowedStatuses, true)) {
    die("Invalid status selected.");
}

/*
|--------------------------------------------------------------------------
| Start Transaction
|--------------------------------------------------------------------------
*/

try {

    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | Profile Photo
    |--------------------------------------------------------------------------
    */

    $photoName = $employee['profile_photo'];

    if (
        isset($_FILES['profile_photo']) &&
        $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Profile photo upload failed.");
        }

        $extension = strtolower(
            pathinfo(
                $_FILES['profile_photo']['name'],
                PATHINFO_EXTENSION
            )
        );

        $allowedExtensions = [
            'jpg',
            'jpeg',
            'png',
            'gif',
            'webp'
        ];

        if (!in_array($extension, $allowedExtensions, true)) {
            throw new Exception("Invalid profile photo format.");
        }

        $photoName = time() . "_" . rand(1000, 9999) . "." . $extension;

        $uploadPath = "../uploads/" . $photoName;

        if (!move_uploaded_file(
            $_FILES['profile_photo']['tmp_name'],
            $uploadPath
        )) {
            throw new Exception("Unable to upload profile photo.");
        }

        /*
        |--------------------------------------------------------------------------
        | Delete Old Photo
        |--------------------------------------------------------------------------
        */

        if (
            !empty($employee['profile_photo']) &&
            file_exists("../uploads/" . $employee['profile_photo'])
        ) {
            unlink("../uploads/" . $employee['profile_photo']);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Update Users Table
    |--------------------------------------------------------------------------
    */

    $user = $pdo->prepare("
        UPDATE users
        SET
            email = ?,
            role = ?,
            status = ?
        WHERE id = ?
    ");

    $user->execute([
        $email,
        $role,
        $status,
        $employee['user_id']
    ]);

    /*
    |--------------------------------------------------------------------------
    | Update Employees Table
    |--------------------------------------------------------------------------
    */

    $employeeUpdate = $pdo->prepare("
        UPDATE employees
        SET
            first_name = ?,
            last_name = ?,
            phone = ?,
            gender = ?,
            dob = ?,
            joining_date = ?,
            department_id = ?,
            designation_id = ?,
            salary = ?,
            profile_photo = ?,
            address = ?
        WHERE id = ?
    ");

    $employeeUpdate->execute([
        $first_name,
        $last_name,
        $phone,
        $gender,
        $dob,
        $joining_date,
        $department_id,
        $designation_id,
        $salary,
        $photoName,
        $address,
        $employee_id
    ]);

    /*
    |--------------------------------------------------------------------------
    | Commit
    |--------------------------------------------------------------------------
    */

    $pdo->commit();

    header("Location: view.php?id=" . $employee_id . "&success=1");
    exit;

} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die($e->getMessage());
}