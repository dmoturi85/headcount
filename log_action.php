<?php
require_once 'db_connect.php';

function logAction($username, $role, $action, $details = null) {
    global $conn;

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

    $stmt = $conn->prepare("
        INSERT INTO audit_log (username, role, action, details, ip_address)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssss", $username, $role, $action, $details, $ip);
    $stmt->execute();
}
?>
