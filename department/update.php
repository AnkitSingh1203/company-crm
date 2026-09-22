<?php

include "../includes/database.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Get Input
|--------------------------------------------------------------------------
*/

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

$department_name = trim($_POST['department_name'] ?? '');

$status = isset($_POST['status']) ? (int) $_POST['status'] : 1;

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

if (!$id || $id < 1) {
    die("Invalid department ID.");
}

if ($department_name === '') {
    die("Department name is required.");
}

if (strlen($department_name) > 100) {
    die("Department name cannot exceed 100 characters.");
}

if (!in_array($status, [0, 1], true)) {
    $status = 1;
}

/*
|--------------------------------------------------------------------------
| Check Department Exists
|--------------------------------------------------------------------------
*/

$departmentStmt = $pdo->prepare("
    SELECT id
    FROM departments
    WHERE id = ?
    LIMIT 1
");

$departmentStmt->execute([$id]);

if (!$departmentStmt->fetch()) {
    die("Department not found.");
}

/*
|--------------------------------------------------------------------------
| Duplicate Department Check
|--------------------------------------------------------------------------
*/

$checkStmt = $pdo->prepare("
    SELECT id
    FROM departments
    WHERE department_name = ?
    AND id != ?
    LIMIT 1
");

$checkStmt->execute([
    $department_name,
    $id
]);

if ($checkStmt->fetch()) {
    die("Department already exists.");
}

/*
|--------------------------------------------------------------------------
| Update Department
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("
        UPDATE departments
        SET
            department_name = ?,
            status = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $department_name,
        $status,
        $id
    ]);

    header("Location: index.php?updated=1");
    exit;

} catch (PDOException $e) {

    die("Failed to update department.");

}