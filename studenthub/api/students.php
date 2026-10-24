<?php
/**
 * Practical 13: PHP JSON API Endpoint for Students CRUD
 * StudentHub Portal - CHARUSAT FTE
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

$method = $_SERVER['REQUEST_METHOD'];

function json_response(bool $success, $dataOrError, int $statusCode = 200, string $message = ''): void {
    http_response_code($statusCode);
    $payload = ['success' => $success];
    if ($message) $payload['message'] = $message;
    if ($success) {
        $payload['data'] = $dataOrError;
    } else {
        $payload['error'] = $dataOrError;
    }
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    switch ($method) {
        case 'GET':
            $search = trim($_GET['search'] ?? '');
            $course = trim($_GET['course'] ?? '');
            $status = trim($_GET['status'] ?? '');

            $sql = "SELECT id, student_id_number, full_name, email, mobile, course, year_of_study, gender, status, created_at FROM students WHERE status != 'deleted'";
            $params = [];

            if (!empty($search)) {
                $sql .= " AND (full_name LIKE ? OR email LIKE ? OR student_id_number LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            if (!empty($course) && $course !== 'all') {
                $sql .= " AND course = ?";
                $params[] = $course;
            }

            if (!empty($status) && $status !== 'all') {
                $sql .= " AND status = ?";
                $params[] = $status;
            }

            $sql .= " ORDER BY id DESC";
            $students = db_fetch_all($sql, $params);
            json_response(true, $students, 200, "Found " . count($students) . " students.");
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            $studentId = trim($input['student_id_number'] ?? '');
            $name = trim($input['full_name'] ?? '');
            $email = trim($input['email'] ?? '');
            $mobile = trim($input['mobile'] ?? '');
            $course = trim($input['course'] ?? '');
            $year = trim($input['year_of_study'] ?? '');
            $gender = trim($input['gender'] ?? 'Male');

            if (empty($studentId) || empty($name) || empty($email) || empty($mobile) || empty($course)) {
                json_response(false, "Student ID, Name, Email, Mobile and Course are required.", 400);
            }

            // Check duplicate student_id or email
            $duplicate = db_fetch_one("SELECT id FROM students WHERE (student_id_number = ? OR email = ?) AND status != 'deleted'", [$studentId, $email]);
            if ($duplicate) {
                json_response(false, "Student ID or Email already exists in the system.", 409);
            }

            $sql = "INSERT INTO students (student_id_number, full_name, email, mobile, course, year_of_study, gender, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')";
            db_query($sql, [$studentId, $name, $email, $mobile, $course, $year, $gender]);
            $newId = (int)get_db_connection()->lastInsertId();

            log_audit_action('API_STUDENT_ADD', "Added student: $name ($studentId)", $_SESSION['user_id'] ?? null, $_SESSION['user_role'] ?? 'admin');
            json_response(true, ['id' => $newId, 'name' => $name, 'student_id' => $studentId], 201, "Student record created successfully.");
            break;

        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true);
            $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));

            if ($id <= 0) {
                json_response(false, "Valid Student ID is required.", 400);
            }

            // Soft-delete by setting status = 'deleted' (Practical 11 Advanced)
            db_query("UPDATE students SET status = 'deleted' WHERE id = ?", [$id]);
            log_audit_action('API_STUDENT_DELETE', "Soft-deleted student ID: $id", $_SESSION['user_id'] ?? null, $_SESSION['user_role'] ?? 'admin');
            json_response(true, ['deleted_id' => $id], 200, "Student soft-deleted successfully.");
            break;

        default:
            json_response(false, "Method $method not supported.", 405);
            break;
    }
} catch (Exception $e) {
    error_log("Student API Error: " . $e->getMessage());
    json_response(false, "Server Error: " . $e->getMessage(), 500);
}
