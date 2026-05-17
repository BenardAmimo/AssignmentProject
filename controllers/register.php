<?php
/**
 * api/auth/register.php
 * Business Logic Layer — Student Registration
 * Greenfield Institute Course Registration System
 *
 * Accepts:  POST JSON { full_name, email, password, confirm }
 * Returns:  JSON { success, message } | { success, errors[] }
 */ 
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
 
// ── Allow requests from the UI layer ──────────────────────────────────────────
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');           // Tighten this in production
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle pre-flight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

require_once __DIR__ . '/../config/config.php';
session_start();

// ── Read & decode JSON body ────────────────────────────────────────────────────
$body = file_get_contents('php://input');
$data = json_decode($body, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON payload.']);
    exit;
}

$full_name = trim($data['full_name'] ?? '');
$email     = strtolower(trim($data['email'] ?? ''));
$role      = strtolower(trim($data['role'] ?? 'student'));
$password  = $data['password'] ?? '';
$confirm   = $data['confirm']  ?? '';

if (!in_array($role, ['student', 'admin'], true)) {
    $role = 'student';
}

// ── Server-side Validation (always validate on the server) ────────────────────
$errors = [];

if (strlen($full_name) < 3) {
    $errors[] = 'Full name must be at least 3 characters.';
}

if ($email === '') {
    $errors[] = 'Email address is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}

if ($role === 'admin' && strpos($email, 'admin@') === false) {
    $errors[] = 'Admin registration requires your email address to contain admin@ (e.g., benardadmin@gmail.com).';
}

if (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters.';
} elseif (!preg_match('/[A-Z]/', $password)) {
    $errors[] = 'Password must contain at least one uppercase letter.';
} elseif (!preg_match('/[0-9]/', $password)) {
    $errors[] = 'Password must contain at least one number.';
}

if ($password !== $confirm) {
    $errors[] = 'Passwords do not match.';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// ── Database Operations ────────────────────────────────────────────────────────
try {
    $pdo = getDB();

    // Check for duplicate email
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'errors' => ['An account with that email already exists.']]);
        exit;
    }

    // Hash password with bcrypt (cost 12)
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    // Insert new user
    $stmt = $pdo->prepare(
        'INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$full_name, $email, $hash, $role]);
    $new_id = (int) $pdo->lastInsertId();

    // Start session for the new user
    session_regenerate_id(true);
    $_SESSION['user_id']   = $new_id;
    $_SESSION['full_name'] = $full_name;
    $_SESSION['role']      = $role;

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Account created successfully.',
        'user'    => [
            'id'        => $new_id,
            'full_name' => $full_name,
            'email'     => $email,
            'role'      => $role,
        ],
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A server error occurred. Please try again.']);
}