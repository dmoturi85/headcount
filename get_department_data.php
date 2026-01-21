<?php
$conn = new mysqli("localhost", "root", "", "employee_census");
$data = $conn->query("SELECT department, COUNT(*) AS total FROM employees GROUP BY department");
$labels = [];
$counts = [];
while ($row = $data->fetch_assoc()) {
    $labels[] = $row['department'];
    $counts[] = $row['total'];
}
echo json_encode(['labels' => $labels, 'counts' => $counts]);
?>
