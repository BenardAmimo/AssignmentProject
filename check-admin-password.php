<?php
require_once __DIR__ . '/config/config.php';

$pdo = getDB();
$stmt = $pdo->query('SELECT id, full_name, email, password FROM users WHERE role = "admin" LIMIT 3');
$admins = $stmt->fetchAll();

echo "<h1>Admin Users and Their Passwords (hashed)</h1>\n";
echo "<table border='1' cellpadding='10'>\n";
echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Password Hash</th></tr>\n";

foreach ($admins as $admin) {
    echo "<tr>";
    echo "<td>{$admin['id']}</td>";
    echo "<td>{$admin['full_name']}</td>";
    echo "<td>{$admin['email']}</td>";
    echo "<td style='font-family: monospace; font-size: 11px;'>{$admin['password']}</td>";
    echo "</tr>\n";
}
echo "</table>\n";

echo "<p>Try logging in with any of these emails using password: <strong>Student@1234</strong></p>\n";

// Verify the hash
$test_password = 'Student@1234';
$hash = '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
echo "<p><strong>Test:</strong> Does 'Student@1234' match the hash? " . (password_verify($test_password, $hash) ? "YES" : "NO") . "</p>\n";
?>
