<?php
/**
 * Practical 10: Secure Logout Script
 * StudentHub Portal - CHARUSAT FTE
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

if (is_logged_in()) {
    $userId = $_SESSION['user_id'];
    $userRole = $_SESSION['user_role'] ?? 'guest';
    $userName = $_SESSION['user_name'] ?? 'User';

    // 1. Remove remember_token from database
    try {
        db_query("UPDATE users SET remember_token = NULL WHERE id = ?", [$userId]);
    } catch (Exception $e) {
        error_log("Failed to clear remember token: " . $e->getMessage());
    }

    // 2. Clear remember-me cookie
    if (isset($_COOKIE['studenthub_remember'])) {
        setcookie('studenthub_remember', '', time() - 3600, '/');
    }

    // 3. Log audit entry
    log_audit_action('USER_LOGOUT', "User $userName logged out successfully", $userId, $userRole);
}

// 4. Destroy PHP Session
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

set_flash('success', 'You have been safely logged out. See you soon!');
header('Location: login.php');
exit;
