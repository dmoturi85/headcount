<?php
require_once 'db_connect.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="audit_log.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Username', 'Role', 'Action', 'Details', 'IP Address', 'Timestamp']);

$result = $conn->query("SELECT * FROM audit_log ORDER BY timestamp DESC");
while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}
fclose($output);
exit;
?>
