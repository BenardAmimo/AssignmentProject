<?php
/**
 * api/auth/logout.php
 * Business Logic Layer — User Logout
 * Greenfield Institute Course Registration System
 *
 * Accepts:  POST (no body required)
 * Returns:  JSON { success, message, redirect }
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

session_start();

// ── Check there is actually a session to destroy ───────────────────────────────
if (!isset($_SESSION['user_id'])) {
    http_response_code(200);
    echo json_encode([
        'success'  => true,
        'message'  => 'No active session.',
        'redirect' => '../../ui/auth/login.html',
    ]);
    exit;
}

// ── Destroy session completely ─────────────────────────────────────────────────

// 1. Clear all session variables
$_SESSION = [];

// 2. Expire the session cookie in the browser
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// 3. Destroy the session on the server
session_destroy();

// ── Respond to the UI layer ────────────────────────────────────────────────────
http_response_code(200);
echo json_encode([
    'success'  => true,
    'message'  => 'You have been logged out successfully.',
    'redirect' => '../../ui/auth/login.html',
]);