<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "employee_census");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$admin_id = $_SESSION['admin_id'];
$admin_username = $_SESSION['admin_username'] ?? 'Administrator';

// Fetch admin data (with role and assigned area)
$stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

$role = $admin_data['role'] ?? 'admin';
$department_filter = "";
$filter_label = "";

// 🧠 Role-based filter
switch ($role) {
    case 'department_head':
        if (!empty($admin_data['department'])) {
            $department_filter = "WHERE department = '" . $conn->real_escape_string($admin_data['department']) . "'";
            $filter_label = " (Department: " . htmlspecialchars($admin_data['department']) . ")";
        }
        break;

    case 'subcounty_head':
        if (!empty($admin_data['sub_county'])) {
            $department_filter = "WHERE sub_county = '" . $conn->real_escape_string($admin_data['sub_county']) . "'";
            $filter_label = " (Subcounty: " . htmlspecialchars($admin_data['sub_county']) . ")";
        }
        break;

    case 'ward_head':
        if (!empty($admin_data['ward'])) {
            $department_filter = "WHERE ward = '" . $conn->real_escape_string($admin_data['ward']) . "'";
            $filter_label = " (Ward: " . htmlspecialchars($admin_data['ward']) . ")";
        }
        break;

    default:
        // Admin or super_admin can see all data
        $department_filter = "";
        $filter_label = "";
        break;
}

// Overview cards
$totalEmployees = $conn->query("SELECT COUNT(*) AS total FROM employees $department_filter")->fetch_assoc()['total'] ?? 0;
$totalDepartments = $conn->query("SELECT COUNT(DISTINCT department) AS total FROM employees")->fetch_assoc()['total'] ?? 0;
$totalSubcounties = $conn->query("SELECT COUNT(DISTINCT subcounty) AS total FROM employees $department_filter")->fetch_assoc()['total'] ?? 0;
$totalWards = $conn->query("SELECT COUNT(DISTINCT ward) AS total FROM employees $department_filter")->fetch_assoc()['total'] ?? 0;

// Charts (filtered by role)
$departmentQuery = $role === 'department_head'
    ? "SELECT department, COUNT(*) AS total FROM employees WHERE department='" . $conn->real_escape_string($admin_data['department']) . "' GROUP BY department"
    : ($role === 'subcounty_head'
        ? "SELECT department, COUNT(*) AS total FROM employees WHERE subcounty='" . $conn->real_escape_string($admin_data['subcounty']) . "' GROUP BY department"
        : ($role === 'ward_head'
            ? "SELECT department, COUNT(*) AS total FROM employees WHERE ward='" . $conn->real_escape_string($admin_data['ward']) . "' GROUP BY department"
            : "SELECT department, COUNT(*) AS total FROM employees GROUP BY department"
        )
    );

$departmentData = $conn->query($departmentQuery);
$departments = [];
$deptCounts = [];
while ($row = $departmentData->fetch_assoc()) {
    $departments[] = $row['department'];
    $deptCounts[] = $row['total'];
}

$genderData = $conn->query("SELECT gender, COUNT(*) AS total FROM employees $department_filter GROUP BY gender");
$genders = [];
$genderCounts = [];
while ($row = $genderData->fetch_assoc()) {
    $genders[] = $row['gender'];
    $genderCounts[] = $row['total'];
}

$disabilityData = $conn->query("SELECT disability_status, COUNT(*) AS total FROM employees $department_filter GROUP BY disability_status");
$disabilities = [];
$disabilityCounts = [];
while ($row = $disabilityData->fetch_assoc()) {
    $disabilities[] = $row['disability_status'];
    $disabilityCounts[] = $row['total'];
}
$jobGroups = [];
$jobGroupCounts = [];

$result = $conn->query("SELECT job_group, COUNT(*) AS total FROM employees GROUP BY job_group");
while ($row = $result->fetch_assoc()) {
    $jobGroups[] = $row['job_group'] ?: 'Not Specified';
    $jobGroupCounts[] = (int)$row['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard - Employee Census</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body {
  margin: 0;
  font-family: 'Segoe UI', sans-serif;
  background: #f3f4f6;
  display: flex;
  min-height: 100vh;
}
.sidebar {
  width: 230px;
  background: #1e3a8a;
  color: white;
  display: flex;
  flex-direction: column;
  padding: 20px;
}
.sidebar h2 {
  text-align: center;
  color: #fff;
  margin-bottom: 30px;
}
.sidebar a {
  color: white;
  text-decoration: none;
  margin: 10px 0;
  padding: 5px;
  display: block;
  border-radius: 5px;
}
.sidebar a:hover {
  background: #2563eb;
}
.main-content {
  flex: 1;
  padding: 20px;
}
header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-top: 20px;
}
.card {
  background: white;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  text-align: center;
}
.card h3 {
  color: #1e3a8a;
  margin-bottom: 10px;
}
.charts {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 10px;
  margin-top: 30px;
}
.chart-container {
  background: white;
  padding: 15px;
  border-radius: 10px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  height: 300px;
}
.chart-container canvas {
  width: 100% !important;
  height: 220px !important;
}
</style>
</head>
<body>
<div class="sidebar">
  <center><img src = "img/log.png" width="80"></center>
  <h2>Admin Panel</h2>
  <a href="admin_dashboard.php">Dashboard</a>
  <a href="view_employees.php">Employees</a>
  <a href="departments.php">Departments</a>
  <a href="Subcounties.php">Subcounties</a>
  <a href="wards.php">Wards</a>
  <a href="jobgroups.php">Job Groups</a>
  <a href="manage_users.php">Manage Users</a>
  <a href="reports.php">Reports</a>
  <a href="audit_log.php">Audit Logs</a>
  <a href="logout.php">Logout</a><br><br>
  <div class="footer">
        &copy; <?= date("Y") ?> denosoft tech solutions
    </div>
</div>

<div class="main-content">
  <header>
    <h1>Welcome, <?php echo htmlspecialchars($admin_username); ?><?php echo $filter_label; ?></h1>
  </header>
 <h2><marquee><font color ="red">Nyamira County Employees Head Count Dashboard</font></marquee></h2>
  <div class="cards">
    <div class="card">
      <h3>Total Employees</h3>
      <p><?php echo $totalEmployees; ?></p>
    </div>
    <div class="card">
      <h3>Departments</h3>
      <p><?php echo $totalDepartments; ?></p>
    </div>
    <div class="card">
      <h3>Subcounties</h3>
      <p><?php echo $totalSubcounties; ?></p>
    </div>
    <div class="card">
      <h3>Wards</h3>
      <p><?php echo $totalWards; ?></p>
    </div>
    <div class="card">
      <h3>job groups</h3>
      <p><?php echo $totalWards; ?></p>
    </div>
  </div>

  <div class="charts">
    <div class="chart-container">
      <h3>Employees per Department</h3>
      <canvas id="departmentChart"></canvas>
    </div>
    <div class="card mb-4">
  <div class="card-header bg-primary text-white">
    <h3>Gender Distribution</h3>
  </div>
  <div class="card-body" style="height: 200px;">  
      
      <canvas id="genderChart"></canvas>
    </div>
    </div>
    <div class="card mb-4">
  <div class="card-header bg-primary text-white">
     <h3>Disability Status</h3>
  </div>
  <div class="card-body" style="height: 200px;">   
      <canvas id="disabilityChart"></canvas>
    </div>
    </div>
    <div class="card mb-4">
  <div class="card-header bg-primary text-white">
    <h3>Job Group Distribution</h3>
  </div>
  <div class="card-body" style="height: 230px;">
    <canvas id="jobGroupChart"></canvas>
  </div>
</div>
</div>

<script>
const deptCtx = document.getElementById('departmentChart').getContext('2d');
new Chart(deptCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($departments); ?>,
        datasets: [{
            label: 'Employees per Department',
            data: <?php echo json_encode($deptCounts); ?>,
            backgroundColor: 'rgba(37,99,235,0.6)'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});

const genderCtx = document.getElementById('genderChart').getContext('2d');
new Chart(genderCtx, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($genders); ?>,
        datasets: [{
            label: 'Gender',
            data: <?php echo json_encode($genderCounts); ?>,
            backgroundColor: ['#2563eb','#f59e0b','#10b981']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
    }
});

const disabilityCtx = document.getElementById('disabilityChart').getContext('2d');
new Chart(disabilityCtx, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($disabilities); ?>,
        datasets: [{
            label: 'Disability',
            data: <?php echo json_encode($disabilityCounts); ?>,
            backgroundColor: ['#ef4444','#22c55e','#facc15']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
    }
});
const jobGroupCtx = document.getElementById('jobGroupChart').getContext('2d');
new Chart(jobGroupCtx, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($jobGroups); ?>,
        datasets: [{
            label: 'Job Groups',
            data: <?php echo json_encode($jobGroupCounts); ?>,
            backgroundColor: [
                '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4',
                '#84cc16', '#ec4899', '#f97316', '#a855f7', '#22c55e', '#eab308'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' },
            title: { display: true, text: '' }
        }
    }
});
</script>

</body>
</html>
