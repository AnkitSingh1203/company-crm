<?php

include "../includes/auth.php";
include "../includes/database.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get POST Data
|--------------------------------------------------------------------------
*/

$id = (int) ($_POST['id'] ?? 0);

$employee_id = (int) ($_POST['employee_id'] ?? 0);

$attendance_date = trim(
    $_POST['attendance_date'] ?? ''
);

$check_in = trim(
    $_POST['check_in'] ?? ''
);

$check_out = trim(
    $_POST['check_out'] ?? ''
);

$status = trim(
    $_POST['status'] ?? ''
);

$remarks = trim(
    $_POST['remarks'] ?? ''
);


/*
|--------------------------------------------------------------------------
| Basic Validation
|--------------------------------------------------------------------------
*/

if ($id <= 0) {

    $_SESSION['error'] = "Invalid attendance record.";

    header("Location: index.php");
    exit;
}


if ($employee_id <= 0 || $attendance_date === '') {

    $_SESSION['error'] =
        "Employee and attendance date are required.";

    header("Location: edit.php?id=" . $id);
    exit;
}


/*
|--------------------------------------------------------------------------
| Allowed Attendance Statuses
|--------------------------------------------------------------------------
*/

$allowedStatuses = [
    'Present',
    'Absent',
    'Late',
    'Half Day',
    'Leave'
];

if (!in_array($status, $allowedStatuses, true)) {

    $_SESSION['error'] =
        "Invalid attendance status.";

    header("Location: edit.php?id=" . $id);
    exit;
}


/*
|--------------------------------------------------------------------------
| Validate Attendance Date
|--------------------------------------------------------------------------
*/

$dateObject = DateTime::createFromFormat(
    'Y-m-d',
    $attendance_date
);

if (
    !$dateObject ||
    $dateObject->format('Y-m-d') !== $attendance_date
) {

    $_SESSION['error'] =
        "Invalid attendance date.";

    header("Location: edit.php?id=" . $id);
    exit;
}


/*
|--------------------------------------------------------------------------
| Validate Employee
|--------------------------------------------------------------------------
*/

$employeeStmt = $pdo->prepare("
    SELECT
        e.id
    FROM employees e
    INNER JOIN users u
        ON e.user_id = u.id
    WHERE e.id = ?
        AND u.status = 'active'
    LIMIT 1
");

$employeeStmt->execute([
    $employee_id
]);

$employee = $employeeStmt->fetch(
    PDO::FETCH_ASSOC
);

if (!$employee) {

    $_SESSION['error'] =
        "Selected employee is not active or does not exist.";

    header("Location: edit.php?id=" . $id);
    exit;
}


/*
|--------------------------------------------------------------------------
| Validate Check-In Time
|--------------------------------------------------------------------------
*/

if ($check_in !== '') {

    $checkInObject = DateTime::createFromFormat(
        'H:i',
        $check_in
    );

    if (
        !$checkInObject ||
        $checkInObject->format('H:i') !== $check_in
    ) {

        $_SESSION['error'] =
            "Invalid check-in time.";

        header("Location: edit.php?id=" . $id);
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| Validate Check-Out Time
|--------------------------------------------------------------------------
*/

if ($check_out !== '') {

    $checkOutObject = DateTime::createFromFormat(
        'H:i',
        $check_out
    );

    if (
        !$checkOutObject ||
        $checkOutObject->format('H:i') !== $check_out
    ) {

        $_SESSION['error'] =
            "Invalid check-out time.";

        header("Location: edit.php?id=" . $id);
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| Check-Out Cannot Be Earlier Than Check-In
|--------------------------------------------------------------------------
*/

if (
    $check_in !== '' &&
    $check_out !== '' &&
    $check_out < $check_in
) {

    $_SESSION['error'] =
        "Check-out time cannot be earlier than check-in time.";

    header("Location: edit.php?id=" . $id);
    exit;
}


/*
|--------------------------------------------------------------------------
| Validate Remarks
|--------------------------------------------------------------------------
*/

if (strlen($remarks) > 255) {

    $_SESSION['error'] =
        "Remarks cannot exceed 255 characters.";

    header("Location: edit.php?id=" . $id);
    exit;
}


/*
|--------------------------------------------------------------------------
| Check Attendance Record Exists
|--------------------------------------------------------------------------
*/

$recordStmt = $pdo->prepare("
    SELECT id
    FROM attendance
    WHERE id = ?
    LIMIT 1
");

$recordStmt->execute([
    $id
]);

$record = $recordStmt->fetch(
    PDO::FETCH_ASSOC
);

if (!$record) {

    $_SESSION['error'] =
        "Attendance record not found.";

    header("Location: index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Duplicate Attendance Check
|--------------------------------------------------------------------------
|
| Exclude the current attendance ID.
|
*/

$duplicateStmt = $pdo->prepare("
    SELECT id
    FROM attendance
    WHERE employee_id = ?
        AND attendance_date = ?
        AND id != ?
    LIMIT 1
");

$duplicateStmt->execute([
    $employee_id,
    $attendance_date,
    $id
]);

if ($duplicateStmt->fetch()) {

    $_SESSION['error'] =
        "Attendance already exists for this employee on this date.";

    header("Location: edit.php?id=" . $id);
    exit;
}


/*
|--------------------------------------------------------------------------
| Update Attendance
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("
        UPDATE attendance
        SET
            employee_id = ?,
            attendance_date = ?,
            check_in = NULLIF(?, ''),
            check_out = NULLIF(?, ''),
            status = ?,
            remarks = NULLIF(?, '')
        WHERE id = ?
    ");

    $stmt->execute([
        $employee_id,
        $attendance_date,
        $check_in,
        $check_out,
        $status,
        $remarks,
        $id
    ]);


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    $_SESSION['success'] =
        "Attendance updated successfully.";

    header("Location: index.php");
    exit;


} catch (PDOException $e) {

    /*
    |--------------------------------------------------------------------------
    | Duplicate / Foreign Key Error
    |--------------------------------------------------------------------------
    */

    if ($e->getCode() === '23000') {

        $_SESSION['error'] =
            "Attendance already exists for this employee on this date.";

    } else {

        $_SESSION['error'] =
            "Something went wrong while updating attendance.";
    }

    header("Location: edit.php?id=" . $id);
    exit;
}