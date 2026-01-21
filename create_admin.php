<?php
$conn = new mysqli("localhost", "root", "", "employee_census");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$username = 'admin';
$email = 'admin@gmail.com';
$password = 'admin123';
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$role = 'Admin';

// delete existing admin to avoid duplicates
$conn->query("DELETE FROM users WHERE username='admin'");

// insert new one
$stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $username, $email, $hashedPassword, $role);

if ($stmt->execute()) {
    echo "✅ Admin user created successfully.<br>";
    echo "Username: admin<br>Password: admin123<br>Role: Admin";
} else {
    echo "❌ Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
