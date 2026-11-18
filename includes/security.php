<?php
require_once __DIR__ . '/functions.php';

// Call this before sending HTML to the browser.
function csrf()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
}
