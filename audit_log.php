<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['role'])) {
    header("Location: admin_login.php");
    exit();
}

$result = $conn->query("SELECT * FROM audit_log ORDER BY timestamp DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Audit Log</title>
<link rel="stylesheet" href="styles.css">
<a href="export_audit_log.php" class="btn btn-success">Export CSV</a>
<style>
body { font-family: Arial; background: #f9f9f9; }
.container { margin: 20px; }
table { width: 100%; border-collapse: collapse; background: #fff; }
th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
th { background: #333; color: white; }
</style>
</head>
<body>
<div class="container">
    <h2>System Audit Log</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Role</th>
            <th>Action</th>
            <th>Details</th>
            <th>IP Address</th>
            <th>Timestamp</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['role']) ?></td>
            <td><?= htmlspecialchars($row['action']) ?></td>
            <td><?= htmlspecialchars($row['details']) ?></td>
            <td><?= htmlspecialchars($row['ip_address']) ?></td>
            <td><?= htmlspecialchars($row['timestamp']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
<footer>
 <center> <a href="admin_dashboard.php">← back to dashboard</a></center>
</footer>
</body>
</html>
