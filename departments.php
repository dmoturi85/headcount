<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}
$conn = new mysqli("localhost", "root", "", "employee_census");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Add new department
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['new_department'])) {
    $newDept = trim($_POST['new_department']);
    $stmt = $conn->prepare("INSERT INTO employees (department) VALUES (?)");
    $stmt->bind_param("s", $newDept);
    $stmt->execute();
    $stmt->close();
}

// Fetch departments
$result = $conn->query("SELECT department, COUNT(*) AS total FROM employees GROUP BY department ORDER BY department ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Departments | Employee Census</title>
<style>
body {font-family: 'Segoe UI'; background:#f8fafc; margin:0;}
.container {margin:40px auto; width:90%; background:white; padding:20px; border-radius:10px; box-shadow:0 3px 8px rgba(0,0,0,0.1);}
h1 {color:#1e3a8a;}
table {width:100%; border-collapse:collapse; margin-top:20px;}
th, td {border:1px solid #e5e7eb; padding:10px; text-align:left;}
th {background:#eff6ff; color:#1e3a8a;}
form {margin-top:15px;}
input[type=text], button {padding:8px; border-radius:6px; border:1px solid #ccc;}
button {background:#2563eb; color:white; border:none; cursor:pointer;}
button:hover {background:#1e40af;}
</style>
</head>
<body>
<div class="container">
<h1>🏢 Departments</h1>
<h2><marquee><font color ="red">Nyamira County Employees Head Count per Department</font></marquee></h2>
<form method="post">
    <input type="text" name="new_department" placeholder="Add new department" required>
    <button type="submit">Add</button>
</form>

<table>
<tr><th>Department</th><th>Total Employees</th></tr>
<?php while ($row = $result->fetch_assoc()): ?>
<tr><td><?= htmlspecialchars($row['department']) ?></td><td><?= $row['total'] ?></td></tr>
<?php endwhile; ?>
</table>
</div>
<footer>
 <center> <a href="admin_dashboard.php">← back to dashboard</a></center>
</footer>
</body>
</html>
