<?php
/**
 * Practical 13: PHP JSON API Endpoint for Events CRUD
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

// Helper to send standardized JSON response
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
        // ----------------------------------------------------
        // 1. GET: Fetch list of events, search or single event
        // ----------------------------------------------------
        case 'GET':
            if (isset($_GET['id'])) {
                $id = (int)$_GET['id'];
                $event = db_fetch_one("SELECT * FROM events WHERE id = ?", [$id]);
                if (!$event) {
                    json_response(false, "Event with ID $id not found.", 404);
                }
                json_response(true, $event, 200, "Event retrieved successfully.");
            }

            $query = trim($_GET['search'] ?? '');
            $category = trim($_GET['category'] ?? '');

            $sql = "SELECT * FROM events WHERE 1=1";
            $params = [];

            if (!empty($query)) {
                $sql .= " AND (title LIKE ? OR description LIKE ? OR venue LIKE ?)";
                $params[] = "%$query%";
                $params[] = "%$query%";
                $params[] = "%$query%";
            }

            if (!empty($category) && $category !== 'all') {
                $sql .= " AND category = ?";
                $params[] = $category;
            }

            $sql .= " ORDER BY event_date ASC";
            $events = db_fetch_all($sql, $params);
            json_response(true, $events, 200, "Retrieved " . count($events) . " events.");
            break;

        // ----------------------------------------------------
        // 2. POST: Add a new event or Register a student
        // ----------------------------------------------------
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            // Check if this is an event registration action
            if (isset($input['action']) && $input['action'] === 'register') {
                if (!is_logged_in()) {
                    json_response(false, "Authentication required. Please log in first.", 401);
                }
                $eventId = (int)($input['event_id'] ?? 0);
                $userId = $_SESSION['user_id'];
                $userName = $_SESSION['user_name'];
                $userEmail = $_SESSION['user_email'];

                if ($eventId <= 0) {
                    json_response(false, "Invalid event ID provided.", 400);
                }

                // Check duplicate
                $existing = db_fetch_one("SELECT id FROM registrations WHERE user_id = ? AND event_id = ?", [$userId, $eventId]);
                if ($existing) {
                    json_response(false, "You are already registered for this event.", 409);
                }

                // Check seats
                $ev = db_fetch_one("SELECT max_seats, registered_count, title FROM events WHERE id = ?", [$eventId]);
                if (!$ev) {
                    json_response(false, "Event does not exist.", 404);
                }
                if ($ev['registered_count'] >= $ev['max_seats']) {
                    json_response(false, "Sorry, this event is already full.", 400);
                }

                // Insert registration
                db_query("INSERT INTO registrations (user_id, event_id, student_name, student_email, status) VALUES (?, ?, ?, ?, 'registered')",
                    [$userId, $eventId, $userName, $userEmail]);
                db_query("UPDATE events SET registered_count = registered_count + 1 WHERE id = ?", [$eventId]);

                log_audit_action('EVENT_REGISTRATION', "User $userName registered for {$ev['title']}", $userId, $_SESSION['user_role'] ?? 'student');
                json_response(true, ['event_id' => $eventId, 'title' => $ev['title']], 201, "Successfully registered for {$ev['title']}!");
            }

            // Normal Event Creation (Admin action)
            $title = trim($input['title'] ?? '');
            $category = trim($input['category'] ?? '');
            $date = trim($input['event_date'] ?? '');
            $time = trim($input['event_time'] ?? '09:00:00');
            $venue = trim($input['venue'] ?? '');
            $desc = trim($input['description'] ?? '');
            $seats = (int)($input['max_seats'] ?? 100);
            $poster = trim($input['poster_url'] ?? 'default_poster.svg');

            if (empty($title) || empty($category) || empty($date) || empty($venue)) {
                json_response(false, "Title, category, date, and venue are required fields.", 400);
            }

            $sql = "INSERT INTO events (title, category, event_date, event_time, venue, description, poster_url, max_seats) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            db_query($sql, [$title, $category, $date, $time, $venue, $desc, $poster, $seats]);
            $newId = (int)get_db_connection()->lastInsertId();

            log_audit_action('API_EVENT_CREATE', "Created event: $title (ID: $newId)", $_SESSION['user_id'] ?? null, $_SESSION['user_role'] ?? 'admin');
            json_response(true, ['id' => $newId, 'title' => $title], 201, "Event created successfully.");
            break;

        // ----------------------------------------------------
        // 3. PUT: Update an event
        // ----------------------------------------------------
        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            $id = (int)($input['id'] ?? 0);

            if ($id <= 0) {
                json_response(false, "Valid Event ID is required for updating.", 400);
            }

            $event = db_fetch_one("SELECT id, title FROM events WHERE id = ?", [$id]);
            if (!$event) {
                json_response(false, "Event not found.", 404);
            }

            $title = trim($input['title'] ?? '');
            $category = trim($input['category'] ?? '');
            $date = trim($input['event_date'] ?? '');
            $venue = trim($input['venue'] ?? '');
            $desc = trim($input['description'] ?? '');
            $status = trim($input['status'] ?? 'upcoming');

            if (empty($title) || empty($category) || empty($date) || empty($venue)) {
                json_response(false, "Title, category, date, and venue cannot be empty.", 400);
            }

            db_query("UPDATE events SET title = ?, category = ?, event_date = ?, venue = ?, description = ?, status = ? WHERE id = ?",
                [$title, $category, $date, $venue, $desc, $status, $id]);

            log_audit_action('API_EVENT_UPDATE', "Updated event ID: $id ($title)", $_SESSION['user_id'] ?? null, $_SESSION['user_role'] ?? 'admin');
            json_response(true, ['id' => $id, 'title' => $title], 200, "Event updated successfully.");
            break;

        // ----------------------------------------------------
        // 4. DELETE: Remove an event
        // ----------------------------------------------------
        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true);
            $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));

            if ($id <= 0) {
                json_response(false, "Valid Event ID is required for deletion.", 400);
            }

            $event = db_fetch_one("SELECT id, title FROM events WHERE id = ?", [$id]);
            if (!$event) {
                json_response(false, "Event not found.", 404);
            }

            db_query("DELETE FROM events WHERE id = ?", [$id]);
            log_audit_action('API_EVENT_DELETE', "Deleted event ID: $id ({$event['title']})", $_SESSION['user_id'] ?? null, $_SESSION['user_role'] ?? 'admin');
            json_response(true, ['deleted_id' => $id], 200, "Event deleted successfully.");
            break;

        default:
            json_response(false, "Method $method not allowed.", 405);
            break;
    }
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    json_response(false, "Server Error: " . $e->getMessage(), 500);
}
