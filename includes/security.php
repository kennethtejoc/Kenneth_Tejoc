<?php
require_once __DIR__ . '/functions.php';

/**
 * Start a session if needed and return a hidden field with a session CSRF token.
 */
function csrf()
{
    if (session_status() !== PHP_SESSION_ACTIVE && !session_start()) {
        throw new RuntimeException('Unable to start a session for CSRF protection.');
    }

    if (!isset($_SESSION['csrf']) || !is_string($_SESSION['csrf']) || !preg_match('/\A[a-f0-9]{64}\z/', $_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
}
