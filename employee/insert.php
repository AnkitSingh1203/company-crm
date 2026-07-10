<?php

require_once "../includes/database.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location:add.php");
    exit;
}

$employee_code = trim($_POST['employee_code']);
$first_name = trim($_POST['first_name']);
$last_name = trim($_POST['last_name']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$phone = trim($_POST['phone']);
$gender = $_POST['gender'];
$dob = $_POST['dob'];
$department_id = $_POST['department_id'];
$designation_id = $_POST['designation_id'];
$joining_date = $_POST['joining_date'];
$salary = $_POST['salary'];
$role = $_POST['role'];
$status = $_POST['status'];
$address = trim($_POST['address']);

if (
    empty($first_name) ||
    empty($email) ||
    empty($password)
) {

    die("Required fields missing.");

}

$check = $pdo->prepare("
SELECT id
FROM users
WHERE email=?
");

$check->execute([$email]);

if($check->rowCount()>0){

    die("Email already exists.");

}

$password = password_hash($password, PASSWORD_DEFAULT);
try{

    $pdo->beginTransaction();
$photoName = "";

if(isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0){

    $extension = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);

    $photoName = time() . "_" . rand(1000,9999) . "." . $extension;

    move_uploaded_file(
        $_FILES['profile_photo']['tmp_name'],
        "../uploads/" . $photoName
    );

}
$user = $pdo->prepare("

INSERT INTO users
(
employee_code,
email,
password,
role,
status
)

VALUES
(
?,?,?,?,?
)

");

$user->execute([

$employee_code,
$email,
$password,
$role,
$status

]);

$user_id = $pdo->lastInsertId();

$employee = $pdo->prepare("

INSERT INTO employees
(

user_id,
employee_code,
first_name,
last_name,
phone,
gender,
dob,
joining_date,
department_id,
designation_id,
salary,
profile_photo,
address

)

VALUES
(

?,?,?,?,?,?,?,?,?,?,?,?,?

)

");

$employee->execute([

$user_id,
$employee_code,
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
$address

]);

$pdo->commit();

header("Location:index.php?success=1");

exit;

}catch(Exception $e){

    if($pdo->inTransaction()){
        $pdo->rollBack();
    }

    die($e->getMessage());

}