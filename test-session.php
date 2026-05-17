<?php
header('Content-Type: application/json');

session_start();

echo json_encode([
    'session_id' => session_id(),
    'session_data' => $_SESSION,
    'user_id' => $_SESSION['user_id'] ?? null,
    'role' => $_SESSION['role'] ?? null,
    'full_name' => $_SESSION['full_name'] ?? null,
]);
?>
