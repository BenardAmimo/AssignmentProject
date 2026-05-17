<?php
/**
 * api/auth/login.php
 * Business Logic Layer — User Login
 * Greenfield Institute Course Registration System
 *
 * Accepts:  POST JSON { email, password }
 * Returns:  JSON { success, message, user: { id, full_name, email, role } }
 */

declare(strict_types=1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
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

$email    = strtolower(trim($data['email']    ?? ''));
$password = $data['password'] ?? '';

// ── Basic validation ───────────────────────────────────────────────────────────
if ($email === '' || $password === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

// ── Database lookup ────────────────────────────────────────────────────────────
try {
    $pdo  = getDB();
    $stmt = $pdo->prepare(
        'SELECT id, full_name, email, password, role FROM users WHERE email = ? LIMIT 1'
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Verify password — use generic message so attackers can't enumerate emails
    if (!$user || !password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
        exit;
    }

    // ── Start session ──────────────────────────────────────────────────────────
    session_regenerate_id(true); // Prevent session fixation

    $_SESSION['user_id']   = (int) $user['id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role']      = $user['role'];

    // Rehash password if cost factor has changed
    if (password_needs_rehash($user['password'], PASSWORD_BCRYPT, ['cost' => 12])) {
        $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')
            ->execute([$newHash, $user['id']]);
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Login successful.',
        'user'    => [
            'id'        => (int) $user['id'],
            'full_name' => $user['full_name'],
            'email'     => $user['email'],
            'role'      => $user['role'],
        ],
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A server error occurred. Please try again.']);
}