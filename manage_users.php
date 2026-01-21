   <?php
   session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "employee_census");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// --- Handle Add User ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $role = trim($_POST['role']);
    $department = !empty($_POST['department']) ? trim($_POST['department']) : null;
    $subcounty = !empty($_POST['subcounty']) ? trim($_POST['subcounty']) : null;
    $ward = !empty($_POST['ward']) ? trim($_POST['ward']) : null;

    // Check duplicate email
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $message = "<div class='alert alert-danger'>Email already exists!</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, department, subcounty, ward) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $username, $email, $password, $role, $department, $subcounty, $ward);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>User added successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error adding user: " . htmlspecialchars($conn->error) . "</div>";
        }
        $stmt->close();
    }
    $check->close();
}

// --- Handle Delete User ---
if (isset($_POST['delete_user'])) {
    $user_id = intval($_POST['user_id']);
    $delete = $conn->prepare("DELETE FROM users WHERE id = ?");
    $delete->bind_param("i", $user_id);
    if ($delete->execute()) {
        $message = "<div class='alert alert-success'>User deleted successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error deleting user.</div>";
    }
    $delete->close();
}

// --- Handle Edit User ---
if (isset($_POST['edit_user'])) {
    $id = intval($_POST['user_id']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $department = !empty($_POST['department']) ? trim($_POST['department']) : null;
    $subcounty = !empty($_POST['subcounty']) ? trim($_POST['subcounty']) : null;
    $ward = !empty($_POST['ward']) ? trim($_POST['ward']) : null;

    $stmt = $conn->prepare("UPDATE users SET username=?, email=?, role=?, department=?, subcounty=?, ward=? WHERE id=?");
    $stmt->bind_param("ssssssi", $username, $email, $role, $department, $subcounty, $ward, $id);
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>User updated successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error updating user.</div>";
    }
    $stmt->close();
}

// --- Fetch dropdown data (must be re-queried each time because mysqli resultsets are exhausted) ---
$departments = $conn->query("SELECT id, name FROM departments");
$subcounties = $conn->query("SELECT id, name FROM subcounties");
$wards = $conn->query("SELECT id, name FROM wards");

// --- Fetch all users ---
$users = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h3 class="text-center mb-4">Manage Users</h3>
    <?= $message ?>

<div class="text-end mb-3">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">+ Add User</button>
</div>

<table class="table table-bordered table-striped align-middle">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Department</th>
            <th>Subcounty</th>
            <th>Ward</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $users->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['role']) ?></td>
            <td><?= htmlspecialchars($row['department']) ?></td>
            <td><?= htmlspecialchars($row['subcounty']) ?></td>
            <td><?= htmlspecialchars($row['ward']) ?></td>
            <td>
                <button class="btn btn-sm btn-warning editBtn"
                    data-id="<?= $row['id'] ?>"
                    data-username="<?= htmlspecialchars($row['username']) ?>"
                    data-email="<?= htmlspecialchars($row['email']) ?>"
                    data-role="<?= htmlspecialchars($row['role']) ?>"
                    data-department="<?= htmlspecialchars($row['department']) ?>"
                    data-subcounty="<?= htmlspecialchars($row['subcounty']) ?>"
                    data-ward="<?= htmlspecialchars($row['ward']) ?>"
                    data-bs-toggle="modal" data-bs-target="#editUserModal">Edit</button>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                    <button type="submit" name="delete_user" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</div>

<!-- Add User Modal -->

<div class="modal fade" id="addUserModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-primary text-white"><h5 class="modal-title">Add User</h5></div>
      <div class="modal-body">
        <div class="mb-2"><label>Username</label><input type="text" name="username" class="form-control" required></div>
        <div class="mb-2"><label>Email</label><input type="email" name="email" class="form-control" required></div>
        <div class="mb-2"><label>Password</label><input type="password" name="password" class="form-control" required></div>
        <div class="mb-2">
            <label>Role</label>
            <select name="role" id="role" class="form-select" required onchange="toggleFields()">
                <option value="">Select Role</option>
                <option value="Head of Department">Head of Department</option>
                <option value="Subcounty Head">Subcounty Head</option>
                <option value="Ward Head">Ward Head</option>
            </select>
        </div>
        <div class="mb-2" id="deptField">
            <label>Department</label>
            <select name="department" class="form-select">
                <option value="">Select Department</option>
                <?php if ($departments): while($d = $departments->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($d['name']) ?>"><?= htmlspecialchars($d['name']) ?></option>
                <?php endwhile; endif; ?>
            </select>
        </div>
        <div class="mb-2" id="subcountyField">
            <label>Subcounty</label>
            <select name="subcounty" class="form-select">
                <option value="">Select Subcounty</option>
                <?php if ($subcounties): while($s = $subcounties->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($s['name']) ?>"><?= htmlspecialchars($s['name']) ?></option>
                <?php endwhile; endif; ?>
            </select>
        </div>
        <div class="mb-2" id="wardField">
            <label>Ward</label>
            <select name="ward" class="form-select">
                <option value="">Select Ward</option>
                <?php if ($wards): while($w = $wards->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($w['name']) ?>"><?= htmlspecialchars($w['name']) ?></option>
                <?php endwhile; endif; ?>
            </select>
        </div>
      </div>
      <div class="modal-footer"><button type="submit" name="add_user" class="btn btn-success">Add</button></div>
    </form>
  </div>
</div>

<!-- Edit User Modal -->

<div class="modal fade" id="editUserModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-warning"><h5 class="modal-title">Edit User</h5></div>
      <div class="modal-body">
        <input type="hidden" name="user_id" id="editUserId">
        <div class="mb-2"><label>Username</label><input type="text" name="username" id="editUsername" class="form-control" required></div>
        <div class="mb-2"><label>Email</label><input type="email" name="email" id="editEmail" class="form-control" required></div>
        <div class="mb-2"><label>Role</label>
            <select name="role" id="editRole" class="form-select">
                <option value="Head of Department">Head of Department</option>
                <option value="Subcounty Head">Subcounty Head</option>
                <option value="Ward Head">Ward Head</option>
            </select>
        </div>
        <div class="mb-2"><label>Department</label><input type="text" name="department" id="editDepartment" class="form-control"></div>
        <div class="mb-2"><label>Subcounty</label><input type="text" name="subcounty" id="editSubcounty" class="form-control"></div>
        <div class="mb-2"><label>Ward</label><input type="text" name="ward" id="editWard" class="form-control"></div>
      </div>
      <div class="modal-footer"><button type="submit" name="edit_user" class="btn btn-success">Save Changes</button></div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function toggleFields() {
    const role = document.getElementById("role").value;
    document.getElementById("deptField").style.display = (role === "Head of Department") ? "block" : "none";
    document.getElementById("subcountyField").style.display = (role === "Head of Department" || role === "Subcounty Head") ? "block" : "none";
    document.getElementById("wardField").style.display = (role === "Ward Head") ? "block" : "none";
}
document.querySelectorAll('.editBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('editUserId').value = btn.dataset.id;
        document.getElementById('editUsername').value = btn.dataset.username;
        document.getElementById('editEmail').value = btn.dataset.email;
        document.getElementById('editRole').value = btn.dataset.role;
        document.getElementById('editDepartment').value = btn.dataset.department;
        document.getElementById('editSubcounty').value = btn.dataset.subcounty;
        document.getElementById('editWard').value = btn.dataset.ward;
    });
});
</script>
<footer class="text-center my-3">
    <a href="admin_dashboard.php">← Back to Dashboard</a>
</footer>
</body>
</html>

