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
| Profile Photo Configuration
|--------------------------------------------------------------------------
*/

$uploadDirectory = dirname(__DIR__) . DIRECTORY_SEPARATOR . "uploads";

$allowedMimeTypes = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp'
];

$maxFileSize = 2 * 1024 * 1024; // 2 MB

$newPhotoName = null;
$newPhotoPath = null;
$oldPhotoPath = null;

/*
|--------------------------------------------------------------------------
| Start Transaction
|--------------------------------------------------------------------------
*/

try {

    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | Profile Photo Upload
    |--------------------------------------------------------------------------
    */

    if (
        isset($_FILES['profile_photo']) &&
        $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        $photo = $_FILES['profile_photo'];

        /*
        |----------------------------------------------------------------------
        | Upload Error
        |----------------------------------------------------------------------
        */

        if ($photo['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Profile photo upload failed.");
        }

        /*
        |----------------------------------------------------------------------
        | File Size
        |----------------------------------------------------------------------
        */

        if ($photo['size'] > $maxFileSize) {
            throw new Exception("Profile photo must be 2 MB or smaller.");
        }

        /*
        |----------------------------------------------------------------------
        | Validate Actual MIME Type
        |----------------------------------------------------------------------
        */

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($photo['tmp_name']);

        if (!isset($allowedMimeTypes[$mimeType])) {
            throw new Exception(
                "Invalid profile photo. Only JPG, PNG and WebP images are allowed."
            );
        }

        /*
        |----------------------------------------------------------------------
        | Verify Image Content
        |----------------------------------------------------------------------
        */

        if (@getimagesize($photo['tmp_name']) === false) {
            throw new Exception("Uploaded file is not a valid image.");
        }

        /*
        |----------------------------------------------------------------------
        | Create Upload Directory
        |----------------------------------------------------------------------
        */

        if (!is_dir($uploadDirectory)) {
            if (!mkdir($uploadDirectory, 0755, true)) {
                throw new Exception("Unable to create upload directory.");
            }
        }

        if (!is_writable($uploadDirectory)) {
            throw new Exception("Upload directory is not writable.");
        }

        /*
        |----------------------------------------------------------------------
        | Generate Secure Unique Filename
        |----------------------------------------------------------------------
        */

        $extension = $allowedMimeTypes[$mimeType];

        $newPhotoName = bin2hex(random_bytes(16)) . "." . $extension;

        $newPhotoPath = $uploadDirectory . DIRECTORY_SEPARATOR . $newPhotoName;

        /*
        |----------------------------------------------------------------------
        | Move Uploaded File
        |----------------------------------------------------------------------
        */

        if (!move_uploaded_file($photo['tmp_name'], $newPhotoPath)) {
            throw new Exception("Unable to save profile photo.");
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Determine Photo Value
    |--------------------------------------------------------------------------
    */

    $photoName = $employee['profile_photo'];

    if ($newPhotoName !== null) {
        $photoName = $newPhotoName;

        if (!empty($employee['profile_photo'])) {
            $oldPhotoPath = $uploadDirectory
                . DIRECTORY_SEPARATOR
                . basename($employee['profile_photo']);
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
    | Commit Database Changes
    |--------------------------------------------------------------------------
    */

    $pdo->commit();

    /*
    |--------------------------------------------------------------------------
    | Delete Old Photo After Successful Database Update
    |--------------------------------------------------------------------------
    */

    if (
        $oldPhotoPath !== null &&
        file_exists($oldPhotoPath) &&
        is_file($oldPhotoPath)
    ) {
        unlink($oldPhotoPath);
    }

    /*
    |--------------------------------------------------------------------------
    | Redirect
    |--------------------------------------------------------------------------
    */

    header("Location: view.php?id=" . $employee_id . "&success=1");
    exit;

} catch (Throwable $e) {

    /*
    |--------------------------------------------------------------------------
    | Rollback Database
    |--------------------------------------------------------------------------
    */

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    /*
    |--------------------------------------------------------------------------
    | Remove Newly Uploaded File If Database Update Failed
    |--------------------------------------------------------------------------
    */

    if (
        $newPhotoPath !== null &&
        file_exists($newPhotoPath) &&
        is_file($newPhotoPath)
    ) {
        unlink($newPhotoPath);
    }

    die($e->getMessage());
}