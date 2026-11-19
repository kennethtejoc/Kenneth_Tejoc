<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params(['httponly' => true, 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite' => 'Lax']);
    session_start();
}
if (empty($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
require __DIR__ . '/../includes/db.php';
$keys = ['site_title' => 'Website title', 'logo_text' => 'Logo text', 'footer_text' => 'Footer text', 'default_theme' => 'Default theme', 'accent_color' => 'Accent color'];
$values = array_fill_keys(array_keys($keys), '');
$message = '';
$success = false;
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf'] ?? '';
        if (!is_string($token) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) {
            $message = 'The form could not be verified. Please try again.';
        } else {
            $update = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
            foreach ($keys as $key => $label) {
                $value = $_POST[$key] ?? '';
                $values[$key] = is_string($value) ? trim($value) : '';
                $update->execute([$key, $values[$key]]);
            }
            $message = 'Website settings saved.';
            $success = true;
        }
    }
    $settings = $pdo->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
    foreach ($settings as $setting) { if (array_key_exists($setting['setting_key'], $values)) { $values[$setting['setting_key']] = $setting['setting_value']; } }
} catch (PDOException $error) {
    error_log($error->getMessage());
    http_response_code(500);
    exit('The settings could not be loaded.');
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Settings | Portfolio Admin</title><link rel="stylesheet" href="../assets/css/style.css"></head>
<body><header class="site-header"><div class="container header-row"><a class="logo" href="../index.php">Kenneth<span>.</span></a><a class="btn header-btn" href="index.php">Dashboard</a></div></header>
<main><section class="contact-page container"><div class="contact-title"><span class="section-label">ADMIN</span><h1>Website Settings</h1><p>Change the public website title, logo, footer, and appearance values.</p></div><nav class="admin-nav" aria-label="Content management"><a href="manage.php?module=skills">Skills</a><a href="manage.php?module=projects">Projects</a><a href="manage.php?module=education">Education</a><a href="manage.php?module=experience">Experience</a><a href="manage.php?module=achievements">Achievements</a></nav><?php if ($message !== ''): ?><p class="<?= $success ? 'form-success' : 'error' ?>" role="status"><?= e($message) ?></p><?php endif; ?><form class="card contact-form" method="post" action="settings.php"><?php foreach ($keys as $key => $label): ?><label for="<?= e($key) ?>"><?= e($label) ?></label><input id="<?= e($key) ?>" name="<?= e($key) ?>" type="text" maxlength="255" value="<?= e($values[$key]) ?>"><?php endforeach; ?><?= csrf() ?><button class="btn" type="submit">Save Settings</button></form></section></main><footer class="site-footer"><p>&copy; <?= e(date('Y')) ?> Kenneth Tejoc · Portfolio Admin</p></footer></body></html>
