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

$modules = [
    'skills' => ['title' => 'Skills', 'table' => 'skills', 'fields' => ['name' => 'Name', 'category' => 'Category', 'level' => 'Level (0-100)', 'description' => 'Description', 'sort_order' => 'Sort order']],
    'projects' => ['title' => 'Projects', 'table' => 'projects', 'fields' => ['title' => 'Title', 'category' => 'Category', 'status' => 'Status', 'description' => 'Description', 'tags' => 'Tags (comma-separated)', 'image' => 'Image filename', 'link_url' => 'Project URL', 'link_label' => 'Link label', 'sort_order' => 'Sort order']],
    'education' => ['title' => 'Education', 'table' => 'education', 'fields' => ['label' => 'Period / label', 'title' => 'School or program', 'description' => 'Description', 'sort_order' => 'Sort order']],
    'experience' => ['title' => 'Experience', 'table' => 'experience', 'fields' => ['label' => 'Type / period', 'title' => 'Role or title', 'organization' => 'Organization', 'description' => 'Description', 'sort_order' => 'Sort order']],
    'achievements' => ['title' => 'Achievements', 'table' => 'achievements', 'fields' => ['title' => 'Title', 'issuer' => 'Issuer', 'date_awarded' => 'Date awarded (YYYY-MM-DD)', 'description' => 'Description', 'image' => 'Image filename', 'sort_order' => 'Sort order']],
];
$moduleKey = $_GET['module'] ?? 'skills';
if (!isset($modules[$moduleKey])) { $moduleKey = 'skills'; }
$module = $modules[$moduleKey];
$message = '';
$success = false;
$editId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$record = array_fill_keys(array_keys($module['fields']), '');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf'] ?? '';
        if (!is_string($token) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) {
            $message = 'The form could not be verified. Please try again.';
        } else {
            $action = $_POST['action'] ?? '';
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            if ($action === 'delete' && $id) {
                $stmt = $pdo->prepare("DELETE FROM {$module['table']} WHERE id = ?");
                $stmt->execute([$id]);
                $message = $stmt->rowCount() ? 'Item deleted.' : 'Item was not found.';
                $success = $stmt->rowCount() > 0;
            } elseif ($action === 'save') {
                $values = [];
                foreach ($module['fields'] as $field => $label) {
                    $value = $_POST[$field] ?? '';
                    if (!is_string($value)) { $value = ''; }
                    $value = trim($value);
                    if (in_array($field, ['level', 'sort_order'], true)) { $value = max(0, (int) $value); }
                    if ($field === 'date_awarded' && $value === '') { $value = null; }
                    $values[$field] = $value;
                }
                if ($moduleKey === 'projects' && isset($_FILES['project_image'])) {
                    try {
                        $uploadedImage = upload_image($_FILES['project_image'], __DIR__ . '/../assets/images/uploads');
                        if ($uploadedImage !== '') { $values['image'] = $uploadedImage; }
                    } catch (RuntimeException $uploadException) {
                        $message = $uploadException->getMessage();
                    }
                }
                if ($message !== '') {
                    $success = false;
                } elseif (($values['title'] ?? $values['name'] ?? '') === '') {
                    $message = 'Please complete the required title or name field.';
                } else {
                    if ($id) {
                        $sets = implode(', ', array_map(fn($field) => "$field = ?", array_keys($values)));
                        $stmt = $pdo->prepare("UPDATE {$module['table']} SET {$sets} WHERE id = ?");
                        $stmt->execute(array_merge(array_values($values), [$id]));
                        $message = 'Item updated successfully.';
                    } else {
                        $columns = implode(', ', array_keys($values));
                        $placeholders = implode(', ', array_fill(0, count($values), '?'));
                        $stmt = $pdo->prepare("INSERT INTO {$module['table']} ({$columns}) VALUES ({$placeholders})");
                        $stmt->execute(array_values($values));
                        $message = 'Item added successfully.';
                    }
                    $success = true;
                    $record = array_fill_keys(array_keys($module['fields']), '');
                    $editId = null;
                }
            }
        }
    }
    if ($editId) {
        $stmt = $pdo->prepare("SELECT * FROM {$module['table']} WHERE id = ? LIMIT 1");
        $stmt->execute([$editId]);
        $record = $stmt->fetch() ?: $record;
    }
    $items = $pdo->query("SELECT * FROM {$module['table']} ORDER BY sort_order ASC, id DESC")->fetchAll();
} catch (PDOException $error) {
    error_log($error->getMessage());
    http_response_code(500);
    exit('The content manager could not be loaded.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= e($module['title']) ?> | Portfolio Admin</title><link rel="stylesheet" href="../assets/css/style.css"></head>
<body>
<header class="site-header"><div class="container header-row"><a class="logo" href="../index.php">Kenneth<span>.</span></a><a class="btn header-btn" href="index.php">Dashboard</a></div></header>
<main><section class="contact-page container">
<div class="contact-title"><span class="section-label">ADMIN</span><h1><?= e($module['title']) ?></h1><p>Add, edit, and delete database-backed portfolio content.</p></div>
<nav class="admin-nav" aria-label="Content management"><a href="manage.php?module=skills">Skills</a><a href="manage.php?module=projects">Projects</a><a href="manage.php?module=education">Education</a><a href="manage.php?module=experience">Experience</a><a href="manage.php?module=achievements">Achievements</a><a href="settings.php">Settings</a></nav>
<?php if ($message !== ''): ?><p class="<?= $success ? 'form-success' : 'error' ?>" role="status"><?= e($message) ?></p><?php endif; ?>
<form class="card contact-form" method="post" enctype="multipart/form-data" action="manage.php?module=<?= e($moduleKey) ?><?= $editId ? '&id=' . (int) $editId : '' ?>"><h2><?= $editId ? 'Edit item' : 'Add item' ?></h2>
<?php foreach ($module['fields'] as $field => $label): ?><label for="<?= e($field) ?>"><?= e($label) ?></label><?php if (in_array($field, ['description'], true)): ?><textarea id="<?= e($field) ?>" name="<?= e($field) ?>" rows="4"><?= e($record[$field] ?? '') ?></textarea><?php else: ?><input id="<?= e($field) ?>" name="<?= e($field) ?>" type="<?= $field === 'date_awarded' ? 'date' : 'text' ?>" value="<?= e($record[$field] ?? '') ?>" maxlength="255"><?php endif; ?><?php endforeach; ?>
<?php if ($moduleKey === 'projects'): ?><label for="project_image">Upload project image (optional, max 5 MB)</label><input id="project_image" name="project_image" type="file" accept="image/jpeg,image/png,image/gif,image/webp"><?php endif; ?>
<?= csrf() ?><input type="hidden" name="action" value="save"><?php if ($editId): ?><input type="hidden" name="id" value="<?= (int) $editId ?>"><?php endif; ?><button class="btn" type="submit"><?= $editId ? 'Update item' : 'Add item' ?></button><?php if ($editId): ?><a class="btn btn-outline" href="manage.php?module=<?= e($moduleKey) ?>">Cancel</a><?php endif; ?></form>
<div class="resume-grid"><h2>Current <?= e($module['title']) ?></h2><?php foreach ($items as $item): ?><article class="resume-card card"><h3><?= e($item['title'] ?? $item['name'] ?? '') ?></h3><p><?= e($item['description'] ?? $item['category'] ?? $item['label'] ?? '') ?></p><p><a class="btn btn-outline" href="manage.php?module=<?= e($moduleKey) ?>&id=<?= (int) $item['id'] ?>">Edit</a></p><form method="post" action="manage.php?module=<?= e($moduleKey) ?>" onsubmit="return confirm('Delete this item?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $item['id'] ?>"><?= csrf() ?><button class="btn" type="submit">Delete</button></form></article><?php endforeach; ?></div>
</section></main><footer class="site-footer"><p>&copy; <?= e(date('Y')) ?> Kenneth Tejoc · Portfolio Admin</p></footer></body></html>
