<?php
/**
 * Practical 7 & 9 Extension: Cross-Site Request Forgery (CSRF) Protection
 * StudentHub Portal - CHARUSAT FTE
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate or get existing CSRF token for current session.
 * @return string
 */
function get_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Render a hidden CSRF input field.
 * @return string HTML input tag
 */
function csrf_field(): string {
    $token = htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Verify submitted CSRF token matches session token.
 * @param string|null $submittedToken
 * @return bool
 */
function verify_csrf_token(?string $submittedToken): bool {
    if (empty($submittedToken) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $submittedToken);
}
