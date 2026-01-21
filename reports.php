<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "employee_census");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Determine grouping type
$type = $_GET['type'] ?? 'department';
$validTypes = ['department', 'subcounty', 'ward', 'job_group', 'disability_status'];
if (!in_array($type, $validTypes)) $type = 'department';

// Fetch grouped data
$stmt = $conn->prepare("SELECT $type AS category, COUNT(*) AS total FROM employees GROUP BY $type ORDER BY total DESC");
$stmt->execute();
$result = $stmt->get_result();

// If a specific category is selected
$selectedCategory = $_GET['category'] ?? null;
$employees = [];
if ($selectedCategory) {
    $stmt2 = $conn->prepare("SELECT * FROM employees WHERE $type = ?");
    $stmt2->bind_param("s", $selectedCategory);
    $stmt2->execute();
    $employees = $stmt2->get_result();
}

// CSV export
if (isset($_GET['export']) && $selectedCategory) {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=report_{$type}_{$selectedCategory}.csv");
    $output = fopen("php://output", "w");
    fputcsv($output, ["Full Name", "ID Number", "Gender", "Department", "Job Group", "subcounty", "Ward", "Disability Status"]);
    while ($row = $employees->fetch_assoc()) {
        fputcsv($output, [$row['full_name'], $row['id_number'], $row['gender'], $row['department'], $row['job_group'], $row['subcounty'], $row['ward'], $row['disability_status']]);
    }
    fclose($output);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reports | Employee Census</title>
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f8fafc;
    margin: 0;
    padding: 0;
}
.container {
    margin: 40px auto;
    width: 90%;
    background: white;
    padding: 20px 30px;
    border-radius: 10px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.1);
}
h1 {
    color: #1e3a8a;
}
select, button {
    padding: 8px 12px;
    font-size: 15px;
    border-radius: 6px;
    border: 1px solid #ccc;
}
button {
    background: #2563eb;
    color: white;
    border: none;
    cursor: pointer;
}
button:hover {
    background: #1e40af;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
th, td {
    border: 1px solid #e5e7eb;
    padding: 10px;
    text-align: left;
}
th {
    background-color: #eff6ff;
    color: #1e3a8a;
}
tr:hover {
    background-color: #f9fafb;
}
a {
    color: #2563eb;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
.back {
    margin-top: 20px;
    display: inline-block;
}
</style>
</head>
<body>

<div class="container">
<h1>📊 Employee Reports</h1>
<h2><marquee><font color ="red">Nyamira County Employees Head Count reports</font></marquee></h2>
<form method="get" action="">
    <label for="type">Select Category:</label>
    <select name="type" id="type" onchange="this.form.submit()">
        <option value="department" <?= $type=='department'?'selected':'' ?>>Department</option>
        <option value="job_group" <?= $type=='job_group'?'selected':'' ?>>Job Group</option>
        <option value="disability_status" <?= $type=='disability_status'?'selected':'' ?>>Disability Status</option>
        <option value="subcounty" <?= $type=='subcounty'?'selected':'' ?>>Sub County</option>
        <option value="ward" <?= $type=='ward'?'selected':'' ?>>Ward</option>
    </select>
</form>

<?php if (!$selectedCategory): ?>
    <h2 style="margin-top:20px;">Grouped by <?= ucfirst(str_replace('_',' ',$type)) ?></h2>
    <table>
        <tr><th><?= ucfirst(str_replace('_',' ',$type)) ?></th><th>Total Employees</th></tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><a href="?type=<?= $type ?>&category=<?= urlencode($row['category']) ?>"><?= htmlspecialchars($row['category'] ?: 'N/A') ?></a></td>
            <td><?= $row['total'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

<?php else: ?>
    <h2>Employees in <?= ucfirst(str_replace('_',' ',$type)) ?>: <span style="color:#2563eb"><?= htmlspecialchars($selectedCategory) ?></span></h2>
    <a href="?type=<?= $type ?>" class="back">⬅ Back to Summary</a>
    <a href="?type=<?= $type ?>&category=<?= urlencode($selectedCategory) ?>&export=1" style="float:right; margin-bottom:10px;" class="export">⬇ Export CSV</a>

    <table>
        <tr>
            <th>#</th><th>Full Name</th><th>ID</th><th>Gender</th><th>Department</th>
            <th>Job Group</th><th>Sub County</th><th>Ward</th><th>Disability</th>
        </tr>
        <?php 
        $count=1;
        $employees->data_seek(0);
        while ($row = $employees->fetch_assoc()): ?>
        <tr>
            <td><?= $count++ ?></td>
            <td><?= htmlspecialchars($row['full_name']) ?></td>
            <td><?= htmlspecialchars($row['id_number']) ?></td>
            <td><?= htmlspecialchars($row['gender']) ?></td>
            <td><?= htmlspecialchars($row['department']) ?></td>
            <td><?= htmlspecialchars($row['job_group']) ?></td>
            <td><?= htmlspecialchars($row['subcounty']) ?></td>
            <td><?= htmlspecialchars($row['ward']) ?></td>
            <td><?= htmlspecialchars($row['disability_status']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
<?php endif; ?>
</div>
<footer>
  <center> <a href="admin_dashboard.php">← back to dashboard</a></center>
</footer>
</body>
</html>
