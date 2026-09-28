<?php

include "../includes/auth.php";
include "../includes/database.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['error'] = "Invalid attendance record.";
    header("Location: index.php");
    exit;
}

try {

    /*
    |--------------------------------------------------------------------------
    | Check Attendance Record
    |--------------------------------------------------------------------------
    */

    $checkStmt = $pdo->prepare("
        SELECT id
        FROM attendance
        WHERE id = ?
        LIMIT 1
    ");

    $checkStmt->execute([$id]);

    $attendance = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$attendance) {

        $_SESSION['error'] =
            "Attendance record not found.";

        header("Location: index.php");
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Attendance
    |--------------------------------------------------------------------------
    */

    $deleteStmt = $pdo->prepare("
        DELETE FROM attendance
        WHERE id = ?
    ");

    $deleteStmt->execute([$id]);


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    $_SESSION['success'] =
        "Attendance deleted successfully.";

    header("Location: index.php");
    exit;


} catch (PDOException $e) {

    $_SESSION['error'] =
        "Something went wrong while deleting attendance.";

    header("Location: index.php");
    exit;
}