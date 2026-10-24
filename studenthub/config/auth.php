<?php
/**
 * Practical 10: Secure Login/Logout with Sessions, Role-Based Access, Timeout, and Remember-Me Token
 * StudentHub Portal - CHARUSAT FTE
 */

require_once __DIR__ . '/db.php';

// Safe session startup with secure cookie parameters
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();
}

// Session timeout duration: 15 minutes (900 seconds)
define('SESSION_TIMEOUT_SECONDS', 900);

/**
 * Check and enforce session inactivity timeout.
 */
function enforce_session_timeout(): void {
    if (isset($_SESSION['user_id'])) {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT_SECONDS)) {
            // Log timeout
            $uid = $_SESSION['user_id'];
            $role = $_SESSION['user_role'] ?? 'guest';
            log_audit_action('SESSION_TIMEOUT', "Session expired after 15 minutes of inactivity for user ID: $uid", $uid, $role);

            // Destroy session
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            session_destroy();
            session_start();
            set_flash('warning', 'Your session has timed out due to inactivity. Please log in again.');
            header("Location: login.php?reason=timeout");
            exit;
        }
        $_SESSION['last_activity'] = time();
    }
}

// Check session timeout on every authenticated page load
enforce_session_timeout();

/**
 * Check if the current client is logged in.
 */
function is_logged_in(): bool {
    // If session is empty, try validating remember-me cookie
    if (empty($_SESSION['user_id']) && !empty($_COOKIE['studenthub_remember'])) {
        attempt_cookie_login($_COOKIE['studenthub_remember']);
    }
    return !empty($_SESSION['user_id']);
}

/**
 * Attempt auto-login using secure persistent remember-me cookie.
 */
function attempt_cookie_login(string $cookieValue): bool {
    $parts = explode(':', $cookieValue);
    if (count($parts) !== 2) return false;

    [$userId, $token] = $parts;
    $user = db_fetch_one("SELECT * FROM users WHERE id = ? AND status = 'active'", [(int)$userId]);
    if ($user && !empty($user['remember_token'])) {
        if (hash_equals($user['remember_token'], hash('sha256', $token))) {
            // Token matches - log user in and regenerate session
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['last_activity'] = time();

            log_audit_action('REMEMBER_ME_LOGIN', "Automatic login via persistent remember token for user: {$user['email']}", $user['id'], $user['role']);
            return true;
        }
    }
    return false;
}

/**
 * Returns current authenticated user record, or null.
 */
function current_user(): ?array {
    if (!is_logged_in()) return null;
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? 'User',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'student',
    ];
}

/**
 * Require authentication. Redirects to login page if unauthenticated.
 */
function require_auth(array $allowedRoles = []): void {
    if (!is_logged_in()) {
        set_flash('error', 'Please log in to access this page.');
        header('Location: login.php');
        exit;
    }
    if (!empty($allowedRoles) && !in_array($_SESSION['user_role'], $allowedRoles, true)) {
        set_flash('error', 'Unauthorized access! You do not have permission for this section.');
        if ($_SESSION['user_role'] === 'admin') {
            header('Location: admin_dashboard.php');
        } else {
            header('Location: student_dashboard.php');
        }
        exit;
    }
}

/**
 * Require admin role.
 */
function require_admin(): void {
    require_auth(['admin']);
}

/**
 * Require student role.
 */
function require_student(): void {
    require_auth(['student']);
}

/**
 * Set flash message (success, error, warning, info).
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Retrieve and clear current flash message.
 */
function get_flash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
