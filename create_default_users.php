<?php
$conn = new mysqli("localhost", "root", "", "employee_census");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$users = [
    ['admin', 'admin@gmail.com', 'admin123', 'Admin'],
    ['hod', 'hod@gmail.com', 'hod123', 'HoD'],
    ['subcounty', 'subcounty@gmail.com', 'subcounty123', 'Subcounty'],
    ['ward', 'ward@gmail.com', 'ward123', 'Ward']
];

foreach ($users as $u) {
    [$username, $email, $password, $role] = $u;

    // Generate bcrypt hash for each password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Delete if already exists (to avoid duplicate key issues)
    $stmt = $conn->prepare("DELETE FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $hashedPassword, $role);
    if ($stmt->execute()) {
        echo "✅ User '{$username}' ({$role}) created successfully.<br>";
    } else {
        echo "❌ Error creating {$username}: " . $stmt->error . "<br>";
    }
    $stmt->close();
}

$conn->close();

echo "<br>All default users have been added successfully!";
?>
