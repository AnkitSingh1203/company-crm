<?php

include "../includes/auth.php";
include "../includes/database.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Form Data
|--------------------------------------------------------------------------
*/

$employee_id = (int) ($_POST['employee_id'] ?? 0);
$attendance_date = trim($_POST['attendance_date'] ?? '');
$check_in = trim($_POST['check_in'] ?? '');
$check_out = trim($_POST['check_out'] ?? '');
$status = trim($_POST['status'] ?? '');
$remarks = trim($_POST['remarks'] ?? '');


/*
|--------------------------------------------------------------------------
| Allowed Statuses
|--------------------------------------------------------------------------
*/

$allowedStatuses = [
    'Present',
    'Absent',
    'Late',
    'Half Day',
    'Leave'
];


/*
|--------------------------------------------------------------------------
| Basic Validation
|--------------------------------------------------------------------------
*/

if ($employee_id <= 0 || $attendance_date === '') {

    $_SESSION['error'] =
        "Employee and attendance date are required.";

    header("Location: add.php");
    exit;
}


if (!in_array($status, $allowedStatuses, true)) {

    $_SESSION['error'] =
        "Invalid attendance status.";

    header("Location: add.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Validate Date
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

    header("Location: add.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Validate Employee
|--------------------------------------------------------------------------
*/

$employeeStmt = $pdo->prepare("
    SELECT e.id
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

$employee = $employeeStmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {

    $_SESSION['error'] =
        "Selected employee is not active or does not exist.";

    header("Location: add.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Validate Check-In
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

        header("Location: add.php");
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| Validate Check-Out
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

        header("Location: add.php");
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| Check-Out Cannot Be Before Check-In
|--------------------------------------------------------------------------
*/

if (
    $check_in !== '' &&
    $check_out !== '' &&
    $check_out < $check_in
) {

    $_SESSION['error'] =
        "Check-out time cannot be earlier than check-in time.";

    header("Location: add.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Remarks Length
|--------------------------------------------------------------------------
*/

if (strlen($remarks) > 255) {

    $_SESSION['error'] =
        "Remarks cannot exceed 255 characters.";

    header("Location: add.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Convert Empty Values To NULL
|--------------------------------------------------------------------------
*/

$checkInValue = $check_in !== ''
    ? $check_in
    : null;

$checkOutValue = $check_out !== ''
    ? $check_out
    : null;

$remarksValue = $remarks !== ''
    ? $remarks
    : null;


/*
|--------------------------------------------------------------------------
| Duplicate Attendance Check
|--------------------------------------------------------------------------
*/

$duplicateStmt = $pdo->prepare("
    SELECT id
    FROM attendance
    WHERE employee_id = ?
      AND attendance_date = ?
    LIMIT 1
");

$duplicateStmt->execute([
    $employee_id,
    $attendance_date
]);

if ($duplicateStmt->fetch()) {

    $_SESSION['error'] =
        "Attendance already exists for this employee on this date.";

    header("Location: add.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Insert Attendance
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("
        INSERT INTO attendance (
            employee_id,
            attendance_date,
            check_in,
            check_out,
            status,
            remarks
        )
        VALUES (
            :employee_id,
            :attendance_date,
            :check_in,
            :check_out,
            :status,
            :remarks
        )
    ");

    $stmt->execute([
        ':employee_id' => $employee_id,
        ':attendance_date' => $attendance_date,
        ':check_in' => $checkInValue,
        ':check_out' => $checkOutValue,
        ':status' => $status,
        ':remarks' => $remarksValue
    ]);

    $_SESSION['success'] =
        "Attendance added successfully.";

    header("Location: index.php");
    exit;

} catch (PDOException $e) {

    if ($e->getCode() === '23000') {

        $_SESSION['error'] =
            "Attendance already exists for this employee on this date.";

    } else {

        error_log(
            "Attendance Insert Error: " . $e->getMessage()
        );

        $_SESSION['error'] =
            "Something went wrong while saving attendance.";
    }

    header("Location: add.php");
    exit;
}