<?php
/**
 * index.php — Main Entry Point
 * Greenfield Institute Course Registration System
 */

session_start();

// ─────────────────────────────────────────────────────────────
// UI PATH CONSTANTS
// ─────────────────────────────────────────────────────────────

define('UI_AUTH',     'ui/auth/');
define('UI_STUDENT',  'ui/students/');
define('UI_ADMIN',    'ui/admin/');

// ─────────────────────────────────────────────────────────────
// AUTH PAGES
// ─────────────────────────────────────────────────────────────

define('LOGIN_PAGE',      UI_AUTH . 'login.html');
define('REGISTER_PAGE',   UI_AUTH . 'register.html');

// ─────────────────────────────────────────────────────────────
// STUDENT PAGES
// ─────────────────────────────────────────────────────────────

define('STUDENT_DASHBOARD', UI_STUDENT . 'dashboard.html');
define('COURSES_PAGE',      UI_STUDENT . 'courses.html');
define('MY_COURSES_PAGE',   UI_STUDENT . 'my-courses.html');
define('PROFILE_PAGE',      UI_STUDENT . 'profile.html');

// ─────────────────────────────────────────────────────────────
// ADMIN PAGES
// ─────────────────────────────────────────────────────────────

define('ADMIN_DASHBOARD',    UI_ADMIN . 'dashboard.html');
define('ADD_COURSE_PAGE',    UI_ADMIN . 'add-course.html');
define('MANAGE_COURSES_PAGE',UI_ADMIN . 'manage-courses.html');
define('REGISTRATIONS_PAGE', UI_ADMIN . 'registrations.html');
define('USERS_PAGE',         UI_ADMIN . 'users.html');

// ─────────────────────────────────────────────────────────────
// PAGE REQUEST
// ─────────────────────────────────────────────────────────────

$page = $_GET['page'] ?? '';

// ─────────────────────────────────────────────────────────────
// PUBLIC ROUTES (NOT LOGGED IN)
// ─────────────────────────────────────────────────────────────

if (!isset($_SESSION['user_id'])) {

    switch ($page) {

        case 'register':
            header('Location: ' . REGISTER_PAGE);
            break;

        case 'login':
        default:
            header('Location: ' . LOGIN_PAGE);
            break;
    }

    exit;
}

// ─────────────────────────────────────────────────────────────
// AUTHENTICATED USER
// ─────────────────────────────────────────────────────────────

$role = strtolower($_SESSION['role'] ?? 'student');

// ─────────────────────────────────────────────────────────────
// PREVENT ACCESS TO LOGIN/REGISTER AFTER LOGIN
// ─────────────────────────────────────────────────────────────

if ($page === 'login' || $page === 'register') {

    if ($role === 'admin') {
        header('Location: ' . ADMIN_DASHBOARD);
    } else {
        header('Location: ' . STUDENT_DASHBOARD);
    }

    exit;
}

// ─────────────────────────────────────────────────────────────
// ADMIN ROUTES
// ─────────────────────────────────────────────────────────────

if ($role === 'admin') {

    switch ($page) {

        case 'add-course':
            header('Location: ' . ADD_COURSE_PAGE);
            break;

        case 'manage-courses':
            header('Location: ' . MANAGE_COURSES_PAGE);
            break;

        case 'registrations':
            header('Location: ' . REGISTRATIONS_PAGE);
            break;

        case 'users':
            header('Location: ' . USERS_PAGE);
            break;

        case 'dashboard':
        default:
            header('Location: ' . ADMIN_DASHBOARD);
            break;
    }

    exit;
}

// ─────────────────────────────────────────────────────────────
// STUDENT ROUTES
// ─────────────────────────────────────────────────────────────

switch ($page) {

    case 'courses':
        header('Location: ' . COURSES_PAGE);
        break;

    case 'my-courses':
        header('Location: ' . MY_COURSES_PAGE);
        break;

    case 'profile':
        header('Location: ' . PROFILE_PAGE);
        break;

    case 'dashboard':
    default:
        header('Location: ' . STUDENT_DASHBOARD);
        break;
}

exit;