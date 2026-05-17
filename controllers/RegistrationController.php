<?php
/**
 * controllers/RegistrationController.php
 * Business Logic Layer — Admin Registration Views
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

$role = strtolower($_SESSION['role'] ?? '');
if ($role !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Admin access required.']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'list') {
    try {
        $pdo = getDB();
        $stmt = $pdo->query(
            'SELECT r.id, u.full_name AS student_name, u.email AS student_email, c.title AS course_title, c.code AS course_code, r.registered_at
             FROM registrations r
             JOIN users u ON r.student_id = u.id
             JOIN courses c ON r.course_id = c.id
             ORDER BY r.registered_at DESC'
        );

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $data]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Unable to load registrations.']);
    }
    exit;
}

if ($action === 'delete') {
    $registrationId = (int) ($_POST['id'] ?? 0);

    if ($registrationId === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid registration ID.']);
        exit;
    }

    try {
        $pdo = getDB();
        $stmt = $pdo->prepare('DELETE FROM registrations WHERE id = ?');
        $deleted = $stmt->execute([$registrationId]);

        if ($deleted) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Unable to delete registration.']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Unable to delete registration.']);
    }
    exit;
}

http_response_code(404);
echo json_encode(['success' => false, 'message' => 'Action not found.']);
