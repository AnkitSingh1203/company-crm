<?php

require_once "../includes/database.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

/*
|--------------------------------------------------------------------------
| Validate Employee ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$employeeId = (int) $_GET['id'];

if ($employeeId <= 0) {
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Get Employee Information
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        e.id,
        e.user_id,
        e.profile_photo
    FROM employees e
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
| Delete Employee
|--------------------------------------------------------------------------
*/

try {

    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | Delete Employee Record
    |--------------------------------------------------------------------------
    */

    $deleteEmployee = $pdo->prepare("
        DELETE FROM employees
        WHERE id = ?
    ");

    $deleteEmployee->execute([
        $employeeId
    ]);

    /*
    |--------------------------------------------------------------------------
    | Delete User Record
    |--------------------------------------------------------------------------
    */

    if (!empty($employee['user_id'])) {

        $deleteUser = $pdo->prepare("
            DELETE FROM users
            WHERE id = ?
        ");

        $deleteUser->execute([
            $employee['user_id']
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Commit Database Changes
    |--------------------------------------------------------------------------
    */

    $pdo->commit();

    /*
    |--------------------------------------------------------------------------
    | Delete Profile Photo
    |--------------------------------------------------------------------------
    */

    if (
        !empty($employee['profile_photo']) &&
        file_exists("../uploads/" . $employee['profile_photo'])
    ) {

        unlink("../uploads/" . $employee['profile_photo']);
    }

    /*
    |--------------------------------------------------------------------------
    | Redirect
    |--------------------------------------------------------------------------
    */

    header("Location: index.php?deleted=1");
    exit;

} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die($e->getMessage());
}