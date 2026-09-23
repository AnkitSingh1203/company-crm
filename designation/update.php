<?php

include "../includes/database.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

$designation_name = trim($_POST['designation_name'] ?? '');

$department_id = filter_input(
    INPUT_POST,
    'department_id',
    FILTER_VALIDATE_INT
);

$status = isset($_POST['status']) ? (int) $_POST['status'] : 1;

if (!$id || $id < 1) {
    die("Invalid designation ID.");
}

if ($designation_name === '') {
    die("Designation name is required.");
}

if (strlen($designation_name) > 100) {
    die("Designation name cannot exceed 100 characters.");
}

if (!$department_id || $department_id < 1) {
    die("Please select a valid department.");
}

if (!in_array($status, [0, 1], true)) {
    $status = 1;
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

// Check department exists and is active
$departmentStmt = $pdo->prepare("
    SELECT id
    FROM departments
    WHERE id = ?
    AND status = 1
    LIMIT 1
");

$departmentStmt->execute([$department_id]);

if (!$departmentStmt->fetch()) {
    die("Selected department is invalid or inactive.");
}

// Check duplicate designation within same department
$checkStmt = $pdo->prepare("
    SELECT id
    FROM designations
    WHERE designation_name = ?
    AND department_id = ?
    AND id != ?
    LIMIT 1
");

$checkStmt->execute([
    $designation_name,
    $department_id,
    $id
]);

if ($checkStmt->fetch()) {
    die("Designation already exists in this department.");
}

try {

    $stmt = $pdo->prepare("
        UPDATE designations
        SET
            designation_name = ?,
            department_id = ?,
            status = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $designation_name,
        $department_id,
        $status,
        $id
    ]);

    header("Location: index.php?updated=1");
    exit;

} catch (PDOException $e) {

    die("Failed to update designation.");

}