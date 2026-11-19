<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require __DIR__ . '/../includes/db.php';

$message = '';
$noticeIsSuccess = false;
$messages = [];

try {
    $checkAdmin = $pdo->prepare('SELECT id FROM admins WHERE id = ? LIMIT 1');
    $checkAdmin->execute([(int) $_SESSION['admin_id']]);

    if (!$checkAdmin->fetch()) {
        $_SESSION = [];
        session_regenerate_id(true);
        header('Location: index.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $submittedToken = $_POST['csrf'] ?? '';
        $sessionToken = $_SESSION['csrf'] ?? '';
        $messageId = filter_var($_POST['message_id'] ?? null, FILTER_VALIDATE_INT);
        $action = $_POST['action'] ?? '';

        if (!is_string($submittedToken) || !is_string($sessionToken) || $sessionToken === '' || !hash_equals($sessionToken, $submittedToken)) {
            $message = 'The form could not be verified. Please try again.';
        } elseif ($messageId === false || $messageId < 1) {
            $message = 'The selected message could not be found.';
        } elseif (!in_array($action, ['read', 'unread', 'delete'], true)) {
            $message = 'The requested message action is invalid.';
        } else {
            if ($action === 'delete') {
                $statement = $pdo->prepare('DELETE FROM messages WHERE id = ?');
                $successText = 'Message deleted.';
            } else {
                $statement = $pdo->prepare('UPDATE messages SET is_read = ? WHERE id = ?');
                $successText = $action === 'read' ? 'Message marked as read.' : 'Message marked as unread.';
            }
            $action === 'delete' ? $statement->execute([$messageId]) : $statement->execute([$action === 'read' ? 1 : 0, $messageId]);
            if ($statement->rowCount() > 0) {
                $message = $successText;
                $noticeIsSuccess = true;
            } else {
                $message = 'The message was already changed or no longer exists.';
            }
        }
    }

    $messages = $pdo->query('SELECT id, name, email, subject, message, is_read, created_at FROM messages ORDER BY created_at DESC, id DESC LIMIT 100')->fetchAll();
} catch (PDOException $error) {
    error_log($error->getMessage());
    http_response_code(500);
    exit('The message inbox could not be loaded.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Messages | Portfolio Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-row">
            <a class="logo" href="../index.php">Kenneth<span>.</span></a>
            <a class="btn header-btn" href="index.php">Back to Admin</a>
        </div>
    </header>

    <main>
        <section class="contact-page container">
            <div class="contact-title">
                <span class="section-label">ADMIN</span>
                <h1>Messages</h1>
                <p>Contact form messages are stored here. The inbox shows up to the latest 100 messages.</p>
            </div>

            <?php if ($message !== ''): ?>
                <p class="<?= $noticeIsSuccess ? 'form-success' : 'error' ?>" role="status"><?= e($message) ?></p>
            <?php endif; ?>

            <?php if (!$messages): ?>
                <p>No messages have been received yet.</p>
            <?php else: ?>
                <div class="resume-grid">
                    <?php foreach ($messages as $item): ?>
                        <article class="resume-card card">
                            <p class="resume-label"><?= (int) $item['is_read'] === 1 ? 'Read' : 'New' ?> · <?= e($item['created_at']) ?></p>
                            <h2><?= e($item['subject']) ?></h2>
                            <p><strong><?= e($item['name']) ?></strong></p>
                            <?php if (filter_var($item['email'], FILTER_VALIDATE_EMAIL)): ?>
                                <p><a href="mailto:<?= e($item['email']) ?>"><?= e($item['email']) ?></a></p>
                            <?php else: ?>
                                <p><?= e($item['email']) ?></p>
                            <?php endif; ?>
                            <p><?= nl2br(e($item['message'])) ?></p>
                            <form method="post" action="messages.php" style="display:inline-block">
                                <input type="hidden" name="message_id" value="<?= (int) $item['id'] ?>">
                                <input type="hidden" name="action" value="<?= (int) $item['is_read'] === 1 ? 'unread' : 'read' ?>">
                                <?= csrf() ?>
                                <button class="btn btn-outline" type="submit"><?= (int) $item['is_read'] === 1 ? 'Mark as Unread' : 'Mark as Read' ?></button>
                            </form>
                            <form method="post" action="messages.php" style="display:inline-block" onsubmit="return confirm('Delete this message?');">
                                <input type="hidden" name="message_id" value="<?= (int) $item['id'] ?>">
                                <input type="hidden" name="action" value="delete">
                                <?= csrf() ?>
                                <button class="btn" type="submit">Delete</button>
                            </form>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer class="site-footer">
        <p>&copy; <?= e(date('Y')) ?> Kenneth Tejoc · Portfolio Admin</p>
    </footer>
</body>
</html>
