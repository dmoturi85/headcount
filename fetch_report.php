<?php
session_start();
include 'db_connect.php';

// Ensure logged in
if (!isset($_SESSION['role'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get filter parameters
$filter = $_GET['filter'] ?? '';
$value = trim($_GET['value'] ?? '');
$role = $_SESSION['role'];

// Determine column for filtering
switch ($filter) {
    case 'gender':
        $column = 'gender';
        break;
    case 'work_station':
        $column = 'work_station';
        break;
    case 'disability':
    case 'disability_status':
        $column = 'disability_status';
        break;
    case 'job_group':
        $column = 'job_group';
        break;
    default:
        echo json_encode([]);
        exit();
}

// Base query — restrict by user role
if ($role === 'Head of Department' || $role === 'Department Head') {
    $dept = $_SESSION['department'] ?? '';
    $sql = "SELECT * FROM employees WHERE department = ? AND $column = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $dept, $value);
} elseif ($role === 'Subcounty Head') {
    $subcounty = $_SESSION['subcounty'] ?? '';
    $sql = "SELECT * FROM employees WHERE subcounty = ? AND $column = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $subcounty, $value);
} elseif ($role === 'Ward Head') {
    $ward = $_SESSION['ward'] ?? '';
    $sql = "SELECT * FROM employees WHERE ward = ? AND $column = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $ward, $value);
} else {
    // For admin or others, show all
    $sql = "SELECT * FROM employees WHERE $column = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $value);
}

// Execute query
$stmt->execute();
$result = $stmt->get_result();

// Prepare result set
$employees = [];
while ($row = $result->fetch_assoc()) {
    $employees[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'gender' => $row['gender'],
        'phone_number' => $row['phone_number'] ?? '',
        'personal_number' => $row['personal_number'] ?? '',
        'id_number' => $row['id_number'] ?? '',
        'job_group' => $row['job_group'] ?? '',
        'disability_status' => $row['disability_status'] ?? '',
        'work_station' => $row['work_station'] ?? '',
        'department' => $row['department'] ?? '',
        'subcounty' => $row['subcounty'] ?? '',
        'ward' => $row['ward'] ?? ''
    ];
}

// Output JSON
header('Content-Type: application/json');
echo json_encode($employees);
?>
