<?php

include "../includes/database.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$department_name = trim($_POST['department_name'] ?? '');
$status = isset($_POST['status']) ? (int) $_POST['status'] : 1;

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

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
| Duplicate Department Check
|--------------------------------------------------------------------------
*/

$checkStmt = $pdo->prepare("
    SELECT id
    FROM departments
    WHERE department_name = ?
    LIMIT 1
");

$checkStmt->execute([$department_name]);

if ($checkStmt->fetch()) {
    die("Department already exists.");
}

/*
|--------------------------------------------------------------------------
| Insert Department
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("
        INSERT INTO departments (
            department_name,
            status
        )
        VALUES (?, ?)
    ");

    $stmt->execute([
        $department_name,
        $status
    ]);

    header("Location: index.php?success=1");
    exit;

} catch (PDOException $e) {

    die("Failed to add department.");

}