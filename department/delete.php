<?php

include "../includes/database.php";

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header("Location: index.php");
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: index.php");
    exit;
}

// Check department exists
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

// Check linked employees
$employeeStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM employees
    WHERE department_id = ?
");

$employeeStmt->execute([$id]);

$employeeCount = (int) $employeeStmt->fetchColumn();

// Check linked designations
$designationStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM designations
    WHERE department_id = ?
");

$designationStmt->execute([$id]);

$designationCount = (int) $designationStmt->fetchColumn();

if ($employeeCount > 0 || $designationCount > 0) {
    die("Department cannot be deleted because it is linked to employees or designations.");
}

try {

    $stmt = $pdo->prepare("
        DELETE FROM departments
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    header("Location: index.php?deleted=1");
    exit;

} catch (PDOException $e) {

    die("Failed to delete department.");

}