<?php
$conn = new mysqli("localhost", "root", "", "employee_census");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fetch all employees
$result = $conn->query("SELECT * FROM employees ORDER BY id DESC");

// Fetch unique dropdown filter values
$departments = $conn->query("SELECT DISTINCT department FROM employees WHERE department <> '' ORDER BY department ASC");
$genders = $conn->query("SELECT DISTINCT gender FROM employees WHERE gender <> '' ORDER BY gender ASC");
$disabilities = $conn->query("SELECT DISTINCT disability_status FROM employees WHERE disability_status <> '' ORDER BY disability_status ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Employees - Employee Census</title>
<style>
body {
  font-family: Arial, sans-serif;
  background-color: #f5f7fa;
  margin: 0;
}
header {
  background-color: #163eb9;
  color: white;
  padding: 15px;
  text-align: center;
  font-size: 24px;
  font-weight: bold;
}
.filter-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 2%;
  flex-wrap: wrap;
  gap: 10px;
}
.filter-bar input, .filter-bar select {
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 14px;
}
.filter-bar input {
  flex: 1;
  min-width: 250px;
}
.table-container {
  width: 98%;
  margin: 10px auto;
  background: white;
  padding: 15px;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  overflow-x: auto;
  max-height: 80vh;
  overflow-y: auto;
}
table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}
th, td {
  border: 1px solid #ccc;
  padding: 8px;
  text-align: center;
}
th {
  background-color: #2563eb;
  color: white;
  position: sticky;
  top: 0;
  z-index: 2;
}
img {
  width: 60px;
  height: 60px;
  border-radius: 6px;
  object-fit: cover;
}
a.map-link {
  color: #2563eb;
  text-decoration: none;
}
a.map-link:hover {
  text-decoration: underline;
}
footer {
  text-align: center;
  padding: 15px;
}
</style>
</head>
<body>
<header>Employee Census - Registered Employees</header>
<h2><marquee><font color ="red">Nyamira County Registerd Employees </font></marquee></h2>
<div class="filter-bar">
  <input type="text" id="searchInput" placeholder="🔍 Search by Name, ID, Department, County..." onkeyup="filterTable()">

  <select id="deptFilter" onchange="filterTable()">
    <option value="">All Departments</option>
    <?php while ($d = $departments->fetch_assoc()): ?>
      <option value="<?= htmlspecialchars($d['department']) ?>"><?= htmlspecialchars($d['department']) ?></option>
    <?php endwhile; ?>
  </select>

  <select id="genderFilter" onchange="filterTable()">
    <option value="">All Genders</option>
    <?php while ($g = $genders->fetch_assoc()): ?>
      <option value="<?= htmlspecialchars($g['gender']) ?>"><?= htmlspecialchars($g['gender']) ?></option>
    <?php endwhile; ?>
  </select>

  <select id="disabilityFilter" onchange="filterTable()">
    <option value="">All Disability Status</option>
    <?php while ($ds = $disabilities->fetch_assoc()): ?>
      <option value="<?= htmlspecialchars($ds['disability_status']) ?>"><?= htmlspecialchars($ds['disability_status']) ?></option>
    <?php endwhile; ?>
  </select>
</div>

<div class="table-container">
<table id="employeeTable">
  <thead>
    <tr>
      <th>#</th>
      <th>Photo</th>
      <th>Full Name</th>
      <th>ID Number</th>
      <th>Personal No.</th>
      <th>Gender</th>
      <th>Disability Status</th>
      <th>Disability Type</th>
      <th>Date of Appointment</th>
      <th>Job Group</th>
      <th>Academic Qualification</th>
      <th>Professional Qualification</th>
      <th>Department</th>
      <th>Section</th>
      <th>Home County</th>
      <th>Sub-County</th>
      <th>Ward</th>
      <th>Work Station</th>
      <th>Phone</th>
      <th>Email</th>
      <th>Location</th>
      <th>Registered</th>
    </tr>
  </thead>
  <tbody>
    <?php if ($result->num_rows > 0): 
      $i = 1;
      while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td>
            <?php if ($row['photo']): ?>
              <img src="<?= $row['photo'] ?>" alt="Employee Photo">
            <?php else: ?>N/A<?php endif; ?>
          </td>
          <td><?= htmlspecialchars($row['full_name']) ?></td>
          <td><?= htmlspecialchars($row['id_number']) ?></td>
          <td><?= htmlspecialchars($row['personal_number']) ?></td>
          <td><?= htmlspecialchars($row['gender']) ?></td>
          <td><?= htmlspecialchars($row['disability_status']) ?></td>
          <td><?= htmlspecialchars($row['disability_type']) ?></td>
          <td><?= htmlspecialchars($row['date_of_appointment']) ?></td>
          <td><?= htmlspecialchars($row['job_group']) ?></td>
          <td><?= htmlspecialchars($row['highest_academic_qualification']) ?></td>
          <td><?= htmlspecialchars($row['professional_qualification']) ?></td>
          <td><?= htmlspecialchars($row['department']) ?></td>
          <td><?= htmlspecialchars($row['section']) ?></td>
          <td><?= htmlspecialchars($row['home_county']) ?></td>
          <td><?= htmlspecialchars($row['subcounty']) ?></td>
          <td><?= htmlspecialchars($row['ward']) ?></td>
          <td><?= htmlspecialchars($row['work_station']) ?></td>
          <td><?= htmlspecialchars($row['phone_number']) ?></td>
          <td><?= htmlspecialchars($row['email']) ?></td>
          <td>
            <?php if (!empty($row['latitude']) && !empty($row['longitude'])): ?>
              <a class="map-link" target="_blank"
                 href="https://www.google.com/maps?q=<?= $row['latitude'] ?>,<?= $row['longitude'] ?>">
                 📍 View Map
              </a>
            <?php else: ?>N/A<?php endif; ?>
          </td>
          <td><?= htmlspecialchars($row['created_at']) ?></td>
        </tr>
      <?php endwhile;
    else: ?>
      <tr><td colspan="22" style="text-align:center;">No employees registered yet.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
</div>

<footer>
  <a href="admin_dashboard.php">← back to dashboard</a>
</footer>

<script>
// 🔍 Client-side filtering with multiple conditions
function filterTable() {
  const searchValue = document.getElementById('searchInput').value.toLowerCase();
  const deptValue = document.getElementById('deptFilter').value.toLowerCase();
  const genderValue = document.getElementById('genderFilter').value.toLowerCase();
  const disabilityValue = document.getElementById('disabilityFilter').value.toLowerCase();

  const table = document.getElementById('employeeTable');
  const trs = table.getElementsByTagName('tr');

  for (let i = 1; i < trs.length; i++) {
    const tds = trs[i].getElementsByTagName('td');
    if (!tds.length) continue;

    const rowText = trs[i].textContent.toLowerCase();
    const deptText = tds[12].textContent.toLowerCase();
    const genderText = tds[5].textContent.toLowerCase();
    const disabilityText = tds[6].textContent.toLowerCase();

    const matchesSearch = rowText.includes(searchValue);
    const matchesDept = deptValue === "" || deptText === deptValue;
    const matchesGender = genderValue === "" || genderText === genderValue;
    const matchesDisability = disabilityValue === "" || disabilityText === disabilityValue;

    trs[i].style.display = (matchesSearch && matchesDept && matchesGender && matchesDisability) ? "" : "none";
  }
}
</script>
</body>
</html>
<?php $conn->close(); ?>
