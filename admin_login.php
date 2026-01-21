<?php
session_start();
include("db_connect.php"); // ensure this file connects correctly to your DB

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        // allow login via username or email
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            // check hashed password
            if (password_verify($password, $user['password'])) {
                // store session data
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['department'] = $user['department'];
                $_SESSION['subcounty'] = $user['subcounty'];
                $_SESSION['ward'] = $user['ward'];

                // redirect user by role
                switch ($user['role']) {
                    case 'Admin':
                        header("Location: admin_dashboard.php");
                        break;
                    case 'Head of Department':
                        header("Location: department_dashboard.php");
                        break;
                    case 'Subcounty Head':
                        header("Location: subcounty_dashboard.php");
                        break;
                    case 'Ward Head':
                        header("Location: ward_dashboard.php");
                        break;
                    default:
                        $error = "Role not recognized for this user.";
                        session_destroy();
                        break;
                }
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "Invalid username or email.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>System Login</title>
<style>
body {
    background: #f0f2f5;
    font-family: 'Segoe UI', sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}
.login-container {
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    width: 350px;
}
.login-container h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #1e3a8a;
}
.login-container input[type="text"],
.login-container input[type="password"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
}
.login-container button {
    width: 100%;
    background: #1e3a8a;
    color: white;
    padding: 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
.login-container button:hover {
    background: #2563eb;
}
.error {
    color: red;
    text-align: center;
    margin-bottom: 10px;
}
.footer {
    text-align: center;
    margin-top: 10px;
    font-size: 13px;
    color: #555;
}
</style>
</head>
<body>

<div class="login-container">
    <h2>System Login</h2>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <input type="text" name="username" placeholder="Enter username or email" required>
        <input type="password" name="password" placeholder="Enter password" required>
        <button type="submit">Login</button>
    </form>
    <div class="footer">
        &copy; <?= date("Y") ?> Employee Census System<br>
        Powered by Denosoft Tech Solutions
    </div>
</div>

</body>
</html>
