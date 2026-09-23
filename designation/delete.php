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

// Check designation exists
$designationStmt = $pdo->prepare("
    SELECT id
    FROM designations
    WHERE id = ?
    LIMIT 1
");

$designationStmt->execute([$id]);

if (!$designationStmt->fetch()) {
    die("Designation not found.");
}

// Check employees linked to this designation
$employeeStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM employees
    WHERE designation_id = ?
");

$employeeStmt->execute([$id]);

$employeeCount = (int) $employeeStmt->fetchColumn();

if ($employeeCount > 0) {
    die("Designation cannot be deleted because it is assigned to employees.");
}

try {

    $stmt = $pdo->prepare("
        DELETE FROM designations
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    header("Location: index.php?deleted=1");
    exit;

} catch (PDOException $e) {

    die("Failed to delete designation.");

}