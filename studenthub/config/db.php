<?php
/**
 * Practical 8: MySQL Schema Design, ER Model, PDO Connectivity, and Prepared Statements
 * StudentHub Portal - CHARUSAT FTE (ITUE203)
 */

if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_PORT')) define('DB_PORT', '3306');
if (!defined('DB_NAME')) define('DB_NAME', 'studenthub_db');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

/**
 * Returns a shared PDO instance with robust error handling and prepared statements.
 * @return PDO
 */
function get_db_connection(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Graceful error display
            error_log("Database Connection Failed: " . $e->getMessage());
            die("<div style='font-family:sans-serif;padding:20px;margin:20px;background:#fee2e2;border:1px solid #ef4444;border-radius:8px;'>
                <h3 style='color:#b91c1c;margin:0 0 10px 0;'>Database Connection Error</h3>
                <p>Could not connect to MySQL database <code>" . htmlspecialchars(DB_NAME) . "</code>.</p>
                <p><strong>Technical details:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
                <p>Please make sure MySQL is running in XAMPP or run <code>setup_database.php</code>.</p>
            </div>");
        }
    }
    return $pdo;
}

/**
 * Returns a MySQLi connection for practicals explicitly requiring MySQLi (Practical 8, 9, 11).
 * @return mysqli
 */
function get_mysqli_connection(): mysqli {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int)DB_PORT);
        if ($conn->connect_error) {
            error_log("MySQLi Connection Error: " . $conn->connect_error);
            die("MySQLi Connection Failed: " . $conn->connect_error);
        }
        $conn->set_charset("utf8mb4");
    }
    return $conn;
}

/**
 * Execute parameterized query using PDO prepared statement.
 */
function db_query(string $sql, array $params = []): PDOStatement {
    $stmt = get_db_connection()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Fetch all rows from parameterized query.
 */
function db_fetch_all(string $sql, array $params = []): array {
    return db_query($sql, $params)->fetchAll();
}

/**
 * Fetch single row from parameterized query.
 */
function db_fetch_one(string $sql, array $params = []): ?array {
    $result = db_query($sql, $params)->fetch();
    return $result ?: null;
}

/**
 * Insert an audit log record into audit_logs table (Practical 9, 12, 14).
 */
function log_audit_action(string $action, string $details, ?int $userId = null, string $userRole = 'guest'): bool {
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $sql = "INSERT INTO audit_logs (user_id, user_role, action, details, ip_address) VALUES (?, ?, ?, ?, ?)";
        db_query($sql, [$userId, $userRole, $action, $details, $ip]);
        return true;
    } catch (Exception $e) {
        error_log("Audit log failed: " . $e->getMessage());
        return false;
    }
}
