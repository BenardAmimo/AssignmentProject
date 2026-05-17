 <?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../service/CourseService.php';
require_once __DIR__ . '/../service/EnrollmentService.php';
require_once __DIR__ . '/../utils/XmlExporter.php';

session_start();

header('Content-Type: application/json');

$courseService = new CourseService(getDB());

/*
|--------------------------------------------------------------------------
| Accept JSON payloads
|--------------------------------------------------------------------------
*/

$input = file_get_contents('php://input');
$payload = json_decode($input, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_array($payload)) {
    $_POST = array_merge($_POST, $payload);
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

/*
|--------------------------------------------------------------------------
| ADD COURSE
|--------------------------------------------------------------------------
*/

if ($action === 'add') {

    if (strtolower($_SESSION['role'] ?? '') !== 'admin') {

        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' => 'Admin access required.'
        ]);

        exit;
    }

    $data = [
        'code' => trim($_POST['code'] ?? ''),
        'title' => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'instructor' => trim($_POST['instructor'] ?? ''),
        'credits' => (int) ($_POST['credits'] ?? 0),
        'capacity' => (int) ($_POST['capacity'] ?? 0),
        'schedule' => trim($_POST['schedule'] ?? ''),
        'category' => trim($_POST['category'] ?? '')
    ];

    $success = $courseService->createCourse($data);

    if ($success) {

        try {
            XmlExporter::export(getDB());
        } catch (Exception $e) {
            error_log($e->getMessage());
        }

        echo json_encode([
            'success' => true,
            'message' => 'Course added successfully.'
        ]);

    } else {

        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => 'Could not create course.'
        ]);
    }

    exit;
}

/*
|--------------------------------------------------------------------------
| UPDATE COURSE
|--------------------------------------------------------------------------
*/

if ($action === 'update') {

    if (strtolower($_SESSION['role'] ?? '') !== 'admin') {

        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' => 'Admin access required.'
        ]);

        exit;
    }

    $data = [
        'id' => (int) ($_POST['id'] ?? 0),
        'code' => trim($_POST['code'] ?? ''),
        'title' => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'instructor' => trim($_POST['instructor'] ?? ''),
        'credits' => (int) ($_POST['credits'] ?? 0),
        'capacity' => (int) ($_POST['capacity'] ?? 0),
        'schedule' => trim($_POST['schedule'] ?? ''),
        'category' => trim($_POST['category'] ?? '')
    ];

    if ($data['id'] === 0) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid course ID.'
        ]);

        exit;
    }

    $success = $courseService->updateCourse($data);

    if ($success) {

        try {
            XmlExporter::export(getDB());
        } catch (Exception $e) {
            error_log($e->getMessage());
        }

        echo json_encode([
            'success' => true,
            'message' => 'Course updated successfully.'
        ]);

    } else {

        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => 'Could not update course.'
        ]);
    }

    exit;
}

/*
|--------------------------------------------------------------------------
| DELETE COURSE
|--------------------------------------------------------------------------
*/

if ($action === 'delete') {

    if (strtolower($_SESSION['role'] ?? '') !== 'admin') {

        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' => 'Admin access required.'
        ]);

        exit;
    }

    $courseId = (int) ($_POST['id'] ?? 0);

    if ($courseId === 0) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid course ID.'
        ]);

        exit;
    }

    $success = $courseService->deleteCourse($courseId);

    if ($success) {

        try {
            XmlExporter::export(getDB());
        } catch (Exception $e) {
            error_log($e->getMessage());
        }

        echo json_encode([
            'success' => true,
            'message' => 'Course deleted successfully.'
        ]);

    } else {

        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => 'Could not delete course.'
        ]);
    }

    exit;
}

/*
|--------------------------------------------------------------------------
| LIST COURSES
|--------------------------------------------------------------------------
*/

if ($action === 'list') {

    echo json_encode([
        'success' => true,
        'data' => $courseService->getAllCourses()
    ]);

    exit;
}