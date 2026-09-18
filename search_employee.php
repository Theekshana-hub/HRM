<?php
// search_employee.php
include 'db_connect.php';

header('Content-Type: application/json');

$emp_no = trim($_GET['emp_no'] ?? $_POST['emp_no'] ?? '');

if (empty($emp_no)) {
    echo json_encode(['success' => false, 'message' => 'Employee Number required']);
    exit;
}

// Get main employee data
$sql = "SELECT * FROM employees WHERE emp_no = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $emp_no);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Employee not found']);
    exit;
}

$emp = $result->fetch_assoc();
$employee_id = $emp['id'];

// Official
$official = [];
$sql2 = "SELECT * FROM employee_official WHERE employee_id = ? LIMIT 1";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $employee_id);
$stmt2->execute();
$res2 = $stmt2->get_result();
if ($res2->num_rows > 0) $official = $res2->fetch_assoc();

// Address
$address = [];
$sql3 = "SELECT * FROM employee_address WHERE employee_id = ? LIMIT 1";
$stmt3 = $conn->prepare($sql3);
$stmt3->bind_param("i", $employee_id);
$stmt3->execute();
$res3 = $stmt3->get_result();
if ($res3->num_rows > 0) $address = $res3->fetch_assoc();

// Job
$job = [];
$sql4 = "SELECT * FROM employee_job WHERE employee_id = ? LIMIT 1";
$stmt4 = $conn->prepare($sql4);
$stmt4->bind_param("i", $employee_id);
$stmt4->execute();
$res4 = $stmt4->get_result();
if ($res4->num_rows > 0) $job = $res4->fetch_assoc();

// Attendance
$attendance = [];
$sql5 = "SELECT * FROM employee_attendance_rules WHERE employee_id = ? LIMIT 1";
$stmt5 = $conn->prepare($sql5);
$stmt5->bind_param("i", $employee_id);
$stmt5->execute();
$res5 = $stmt5->get_result();
if ($res5->num_rows > 0) $attendance = $res5->fetch_assoc();

echo json_encode([
    'success' => true,
    'employee' => $emp,
    'official' => $official,
    'address' => $address,
    'job' => $job,
    'attendance' => $attendance
]);

$stmt->close();
$conn->close();
?>