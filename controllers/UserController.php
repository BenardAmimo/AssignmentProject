<?php
/**
 * controllers/UserController.php
 * Business Logic Layer — Admin User Views
 * Greenfield Institute Course Registration System
 */

declare(strict_types=1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

session_start();
require_once __DIR__ . '/../config/config.php';

$input = file_get_contents('php://input');
$payload = json_decode($input, true);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_array($payload)) {
    $_POST = array_merge($_POST, $payload);
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Profile action is available for all logged-in users
if ($action === 'profile') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Not logged in.']);
        exit;
    }

    try {
        $pdo = getDB();
        $stmt = $pdo->prepare(
            'SELECT id, full_name, email, role, created_at FROM users WHERE id = ?'
        );
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'User not found.']);
            exit;
        }

        echo json_encode(['success' => true, 'data' => $user]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Unable to load profile.']);
    }
    exit;
}

// Admin-only actions below
$role = strtolower($_SESSION['role'] ?? '');
if ($role !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Admin access required.']);
    exit;
}

if ($action === 'list') {
    try {
        $pdo = getDB();
        $stmt = $pdo->query(
            'SELECT id, full_name, email, role, created_at
             FROM users
             ORDER BY created_at DESC'
        );

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $data]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Unable to load users.']);
    }
    exit;
}

if ($action === 'delete') {
    $userId = (int) ($_POST['id'] ?? 0);

    if ($userId === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
        exit;
    }

    if ($userId === (int)($_SESSION['user_id'] ?? 0)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'You cannot delete your own account.']);
        exit;
    }

    try {
        $pdo = getDB();
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $deleted = $stmt->execute([$userId]);

        if ($deleted) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Unable to delete user.']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Unable to delete user.']);
    }
    exit;
}

http_response_code(404);
echo json_encode(['success' => false, 'message' => 'Action not found.']);
