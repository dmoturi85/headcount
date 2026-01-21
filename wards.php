<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}
$conn = new mysqli("localhost", "root", "", "employee_census");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$result = $conn->query("SELECT ward, COUNT(*) AS total FROM employees GROUP BY ward ORDER BY ward ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Wards | Employee Census</title>
<style>
body {font-family:'Segoe UI'; background:#f8fafc; margin:0;}
.container {margin:40px auto; width:90%; background:white; padding:20px; border-radius:10px; box-shadow:0 3px 8px rgba(0,0,0,0.1);}
h1 {color:#1e3a8a;}
table {width:100%; border-collapse:collapse; margin-top:20px;}
th, td {border:1px solid #e5e7eb; padding:10px; text-align:left;}
th {background:#eff6ff; color:#1e3a8a;}
</style>
</head>
<body>
<div class="container">
<h1>🌍 Wards</h1>
<h2><marquee><font color ="red">Nyamira County Employees Head Count per ward</font></marquee></h2>
<table>
<tr><th>Ward</th><th>Total Employees</th></tr>
<?php while ($row = $result->fetch_assoc()): ?>
<tr><td><?= htmlspecialchars($row['ward']) ?></td><td><?= $row['total'] ?></td></tr>
<?php endwhile; ?>
</table>
</div>
<footer>
 <center> <a href="admin_dashboard.php">← back to dashboard</a></center>
</footer>
</body>
</html>
