<?php
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] != 'Ward Head') {
    header("Location: admin_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "employee_census");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$ward = $_SESSION['ward'];

// Handle CSV export
if (isset($_GET['export'])) {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=ward_report.csv");
    $out = fopen("php://output", "w");
    fputcsv($out, ["ID", "Name", "Gender", "Job Group", "Disability", "Work Station"]);

    $export = $conn->prepare("SELECT id, name, gender, job_group, disability_status, work_station FROM employees WHERE ward = ?");
    $export->bind_param("s", $ward);
    $export->execute();
    $result = $export->get_result();

    while ($row = $result->fetch_assoc()) {
        fputcsv($out, $row);
    }
    fclose($out);
    exit();
}

// Fetch employees
$stmt = $conn->prepare("SELECT * FROM employees WHERE ward = ?");
$stmt->bind_param("s", $ward);
$stmt->execute();
$employees = $stmt->get_result();

// Summary
$total = $conn->query("SELECT COUNT(*) AS c FROM employees WHERE ward='$ward'")->fetch_assoc()['c'];
$male = $conn->query("SELECT COUNT(*) AS c FROM employees WHERE gender='Male' AND ward='$ward'")->fetch_assoc()['c'];
$female = $conn->query("SELECT COUNT(*) AS c FROM employees WHERE gender='Female' AND ward='$ward'")->fetch_assoc()['c'];
$disabled = $conn->query("SELECT COUNT(*) AS c FROM employees WHERE disability_status='Yes' AND ward='$ward'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Ward Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
    <h3>Ward Head Dashboard</h3>
    <p>Welcome, <b><?= htmlspecialchars($_SESSION['admin_username']) ?></b> (<?= htmlspecialchars($ward) ?> Ward)</p>
    <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    <a href="?export=1" class="btn btn-success btn-sm float-end">📥 Download CSV Report</a>
    <hr>

    <div class="row text-center mb-4">
        <div class="col"><div class="card p-3"><h5>Total Employees</h5><h3><?= $total ?></h3></div></div>
        <div class="col"><div class="card p-3"><h5>Male</h5><h3><?= $male ?></h3></div></div>
        <div class="col"><div class="card p-3"><h5>Female</h5><h3><?= $female ?></h3></div></div>
        <div class="col"><div class="card p-3"><h5>Disabled</h5><h3><?= $disabled ?></h3></div></div>
    </div>

    <h5>Employee List (<?= htmlspecialchars($ward) ?>)</h5>
    <table class="table table-bordered table-striped">
        <thead class="table-dark"><tr>
            <th>ID</th><th>Name</th><th>Gender</th><th>Job Group</th><th>Disability</th><th>Work Station</th>
        </tr></thead>
        <tbody>
            <?php while($row = $employees->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['gender']) ?></td>
                <td><?= htmlspecialchars($row['job_group']) ?></td>
                <td><?= htmlspecialchars($row['disability_status']) ?></td>
                <td><?= htmlspecialchars($row['work_station']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
