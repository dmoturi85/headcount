<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Head of Department') {
    header("Location: admin_login.php");
    exit();
}

include 'db_connect.php';
$department = $_SESSION['department'] ?? '';

// Fetch all employees in this department
$sql = "SELECT * FROM employees WHERE department = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $department);
$stmt->execute();
$result = $stmt->get_result();
$employees = $result->fetch_all(MYSQLI_ASSOC);
$totalEmployees = count($employees);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Department Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body { background: #f5f1f1ff; }
.sidebar { width: 230px; height: 100vh; background: #2b425cff; position: fixed; top: 0; left: 0; padding: 20px; color: white; }
.sidebar a { color: white; display: block; padding: 10px 0; text-decoration: none; }
.sidebar a:hover { background: #0d78e4ff; border-radius: 6px; }
.content { margin-left: 250px; padding: 30px; }
.card { margin-bottom: 20px; border-radius: 12px; }
</style>
</head>
<body>
<div class="sidebar">
    <h4>Department Head</h4><hr>
    <a href="#" id="viewEmployees">Employees</a>
    <div class="dropdown">
        <a class="dropdown-toggle" href="fetch_report.php" data-bs-toggle="dropdown">Reports</a>
        <ul class="dropdown-menu" style="background: #395168ff; border-radius: 8px;">
            <li><a class="dropdown-item report-filter" data-filter="work_station">By Work Station</a></li>
            <li><a class="dropdown-item report-filter" data-filter="job_group">By Job Group</a></li>
            <li><a class="dropdown-item report-filter" data-filter="gender">By Gender</a></li>
        </ul>
    </div>
    <a href="logout.php">Logout</a>
</div>

<div class="content">
    <h3>Department Dashboard - <?= htmlspecialchars($department) ?></h3>

    <!-- ===== Top Cards and Charts ===== -->
    <div class="row my-4">
        <div class="col-md-4">
            <div class="card shadow-sm text-center p-3 bg-success text-white">
                <h5>Total Employees</h5>
                <h2 id="totalEmployees"><?= $totalEmployees ?></h2>
            </div>
        </div>
        <div class="col-md-2"><div class="card p-1"><canvas id="genderChart" height="100"></canvas></div></div>
        <div class="col-md-2"><div class="card p-1"><canvas id="disabilityChart" height="100"></canvas></div></div>
    </div>

    <!-- ===== Buttons ===== -->
    <div class="d-flex my-3 gap-2">
        <button class="btn btn-success" onclick="exportCSV()">Download CSV</button>
        <button class="btn btn-secondary" onclick="window.print()">Print</button>
    </div>

    <!-- ===== Employee Table ===== -->
    <div class="card p-3">
        <h5>Employee List</h5>
        <table class="table table-striped" id="employeeTable">
            <thead class="table-dark">
                <tr>
                    <th>Name</th><th>Gender</th><th>Phone</th><th>Personal #</th>
                    <th>ID #</th><th>Job Group</th><th>Disability</th><th>Work Station</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $e): ?>
                    <tr>
                        <td><?= htmlspecialchars($e['full_name']) ?></td>
                        <td><?= htmlspecialchars($e['gender']) ?></td>
                        <td><?= htmlspecialchars($e['phone_number']) ?></td>
                        <td><?= htmlspecialchars($e['personal_number']) ?></td>
                        <td><?= htmlspecialchars($e['id_number']) ?></td>
                        <td><?= htmlspecialchars($e['job_group']) ?></td>
                        <td><?= htmlspecialchars($e['disability_status']) ?></td>
                        <td><?= htmlspecialchars($e['work_station']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
let employees = <?= json_encode($employees) ?>;

// Draw charts
function drawCharts(data) {
    const genderCounts = { Male: 0, Female: 0 };
    const disabilityCounts = { Disabled: 0, 'Not Disabled': 0 };

    data.forEach(e => {
        genderCounts[e.gender] = (genderCounts[e.gender] || 0) + 1;
        const dis = (e.disability_status?.toLowerCase() === 'yes' || e.disability_status?.toLowerCase() === 'disabled') ? 'Disabled' : 'Not Disabled';
        disabilityCounts[dis]++;
    });

    new Chart(document.getElementById('genderChart'), {
        type: 'pie',
        data: {
            labels: Object.keys(genderCounts),
            datasets: [{ data: Object.values(genderCounts), backgroundColor: ['#3b82f6', '#ef4444'] }]
        },
        options: { plugins: { legend: { position: 'bottom' } }, responsive: true }
    });

    new Chart(document.getElementById('disabilityChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(disabilityCounts),
            datasets: [{ data: Object.values(disabilityCounts), backgroundColor: ['#22c55e', '#facc15'] }]
        },
        options: { plugins: { legend: { position: 'bottom' } }, responsive: true }
    });
}
drawCharts(employees);

// CSV Export
function exportCSV() {
    let csv = 'Name,Gender,Phone,Personal Number,ID Number,Job Group,Disability,Work Station\n';
    employees.forEach(e => {
        csv += `${e.name},${e.gender},${e.phone_number},${e.personal_number},${e.id_number},${e.job_group},${e.disability_status},${e.work_station}\n`;
    });
    const blob = new Blob([csv], { type: 'text/csv' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'employees_report.csv';
    a.click();
}

// Report filter AJAX
document.querySelectorAll('.report-filter').forEach(btn => {
    btn.addEventListener('click', () => {
        const filter = btn.dataset.filter;
        const value = prompt(`Enter ${filter.replace('_', ' ')} to filter:`);
        if (!value) return;
        fetch(`fetch_report.php?filter=${filter}&value=${value}`)
            .then(res => res.json())
            .then(data => {
                employees = data;
                document.querySelector('#totalEmployees').textContent = data.length;
                const tbody = document.querySelector('#employeeTable tbody');
                tbody.innerHTML = '';
                data.forEach(e => {
                    tbody.innerHTML += `<tr>
                        <td>${e.name}</td><td>${e.gender}</td><td>${e.phone_number}</td>
                        <td>${e.personal_number}</td><td>${e.id_number}</td>
                        <td>${e.job_group}</td><td>${e.disability_status}</td><td>${e.work_station}</td>
                    </tr>`;
                });
                drawCharts(data);
            });
    });
});
</script>
</body>
</html>
