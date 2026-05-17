<?php
/**
 * debug.php - Diagnostic page to check system status
 * Visit: http://localhost/AssignmentProject/debug.php
 */

header('Content-Type: text/html; charset=utf-8');

echo "<h1>System Diagnostics</h1>\n";
echo "<hr>\n";

// Check config file
echo "<h2>1. Config File</h2>\n";
if (file_exists(__DIR__ . '/config/config.php')) {
    require_once __DIR__ . '/config/config.php';
    echo "<p style='color: green;'>✓ config.php loaded</p>\n";
} else {
    echo "<p style='color: red;'>✗ config.php NOT found</p>\n";
    exit;
}

// Check database connection
echo "<h2>2. Database Connection</h2>\n";
try {
    $pdo = getDB();
    echo "<p style='color: green;'>✓ Database connected to: " . DB_NAME . "</p>\n";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>\n";
    exit;
}

// Check users table
echo "<h2>3. Users Table</h2>\n";
try {
    $result = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch();
    $count = $result['count'];
    echo "<p>Total users: <strong>$count</strong></p>\n";
    
    if ($count > 0) {
        echo "<p style='color: green;'>✓ Users exist</p>\n";
        $users = $pdo->query("SELECT id, full_name, email, role FROM users")->fetchAll();
        echo "<pre style='background: #f0f0f0; padding: 10px;'>\n";
        foreach ($users as $user) {
            echo "ID: {$user['id']}, Name: {$user['full_name']}, Email: {$user['email']}, Role: {$user['role']}\n";
        }
        echo "</pre>\n";
    } else {
        echo "<p style='color: orange;'>⚠ No users found - database may not be seeded</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error checking users: " . $e->getMessage() . "</p>\n";
}

// Check courses table
echo "<h2>4. Courses Table</h2>\n";
try {
    $result = $pdo->query("SELECT COUNT(*) as count FROM courses")->fetch();
    $count = $result['count'];
    echo "<p>Total courses: <strong>$count</strong></p>\n";
    
    if ($count > 0) {
        echo "<p style='color: green;'>✓ Courses exist</p>\n";
        $courses = $pdo->query("SELECT id, code, title, instructor FROM courses LIMIT 5")->fetchAll();
        echo "<pre style='background: #f0f0f0; padding: 10px;'>\n";
        foreach ($courses as $course) {
            echo "ID: {$course['id']}, Code: {$course['code']}, Title: {$course['title']}, Instructor: {$course['instructor']}\n";
        }
        echo "</pre>\n";
    } else {
        echo "<p style='color: orange;'>⚠ No courses found - database may not be seeded</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error checking courses: " . $e->getMessage() . "</p>\n";
}

// Check registrations table
echo "<h2>5. Registrations Table</h2>\n";
try {
    $result = $pdo->query("SELECT COUNT(*) as count FROM registrations")->fetch();
    $count = $result['count'];
    echo "<p>Total registrations: <strong>$count</strong></p>\n";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error checking registrations: " . $e->getMessage() . "</p>\n";
}

// Test UserController API
echo "<h2>6. UserController API Test</h2>\n";
echo "<p>Endpoint: /controllers/UserController.php?action=list</p>\n";
echo "<p><a href='controllers/UserController.php?action=list' target='_blank'>Test API (requires admin login)</a></p>\n";

// Test CourseController API
echo "<h2>7. CourseController API Test</h2>\n";
echo "<p>Endpoint: /controllers/CourseController.php?action=list</p>\n";
echo "<p><a href='controllers/CourseController.php?action=list' target='_blank'>Test API (public)</a></p>\n";

echo "<hr>\n";
echo "<p><strong>Note:</strong> If users and courses are empty, you need to run the SQL file:</p>\n";
echo "<pre>database/greenField.sql</pre>\n";
?>
