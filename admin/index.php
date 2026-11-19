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

require __DIR__ . '/../includes/db.php';

$message = '';
$username = '';
$displayName = '';

try {
    $adminCount = (int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
    $isFirstAdmin = $adminCount === 0;
    $isLoggedIn = !empty($_SESSION['admin_id']);
    $stats = [];
    if ($isLoggedIn) {
        foreach (['projects' => 'Projects', 'skills' => 'Skills', 'messages' => 'Messages', 'experience' => 'Experiences'] as $table => $label) {
            $stats[$label] = (int) $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $submittedToken = $_POST['csrf'] ?? '';
        $sessionToken = $_SESSION['csrf'] ?? '';

        if (!is_string($submittedToken) || !is_string($sessionToken) || $sessionToken === '' || !hash_equals($sessionToken, $submittedToken)) {
            $message = 'The form could not be verified. Please try again.';
        } elseif (isset($_POST['logout']) && $isLoggedIn) {
            $_SESSION = [];
            session_regenerate_id(true);
            header('Location: index.php');
            exit;
        } elseif (!$isLoggedIn && $isFirstAdmin && isset($_POST['create_admin'])) {
            $postedUsername = $_POST['username'] ?? '';
            $postedDisplayName = $_POST['display_name'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $username = is_string($postedUsername) ? trim($postedUsername) : '';
            $displayName = is_string($postedDisplayName) ? trim($postedDisplayName) : '';

            if (!preg_match('/^[A-Za-z0-9_.-]{3,30}$/', $username)) {
                $message = 'Use 3 to 30 letters, numbers, dots, dashes, or underscores for the username.';
            } elseif (!is_string($password) || strlen($password) < 12) {
                $message = 'Choose a password with at least 12 characters.';
            } elseif (!is_string($confirmPassword) || $password !== $confirmPassword) {
                $message = 'The passwords do not match.';
            } else {
                $pdo->beginTransaction();
                $existingAdmin = $pdo->query('SELECT id FROM admins LIMIT 1 FOR UPDATE')->fetch();

                if ($existingAdmin) {
                    $pdo->rollBack();
                    $isFirstAdmin = false;
                    $message = 'An admin account has already been created. Please sign in.';
                } else {
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $createAdmin = $pdo->prepare('INSERT INTO admins (username, password_hash, display_name, created_at) VALUES (?, ?, ?, NOW())');
                    $createAdmin->execute([$username, $passwordHash, $displayName]);
                    $adminId = (int) $pdo->lastInsertId();
                    $pdo->commit();

                    session_regenerate_id(true);
                    $_SESSION['admin_id'] = $adminId;
                    $_SESSION['admin_name'] = $displayName !== '' ? $displayName : $username;
                    header('Location: index.php');
                    exit;
                }
            }
        } elseif (!$isLoggedIn && !$isFirstAdmin && isset($_POST['login'])) {
            $postedUsername = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $username = is_string($postedUsername) ? trim($postedUsername) : '';

            if (!preg_match('/^[A-Za-z0-9_.-]{3,30}$/', $username) || !is_string($password)) {
                $message = 'Invalid username or password.';
            } else {
                $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
                if (!is_string($ipAddress) || filter_var($ipAddress, FILTER_VALIDATE_IP) === false) {
                    $ipAddress = '';
                }
                $recentAttempts = $pdo->prepare('SELECT COUNT(*) FROM login_attempts WHERE (username = ? OR ip_address = ?) AND success = 0 AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)');
                $recentAttempts->execute([$username, $ipAddress]);
                if ((int) $recentAttempts->fetchColumn() >= 5) {
                    $message = 'Too many sign-in attempts. Please try again in 15 minutes.';
                    $admin = false;
                } else {
                $findAdmin = $pdo->prepare('SELECT id, username, password_hash, display_name FROM admins WHERE username = ? LIMIT 1');
                $findAdmin->execute([$username]);
                $admin = $findAdmin->fetch();

                if ($admin && password_verify($password, $admin['password_hash'])) {
                    $recordAttempt = $pdo->prepare('INSERT INTO login_attempts (username, ip_address, success, attempted_at) VALUES (?, ?, 1, NOW())');
                    $recordAttempt->execute([$username, $ipAddress]);
                    $updateLogin = $pdo->prepare('UPDATE admins SET last_login = NOW() WHERE id = ?');
                    $updateLogin->execute([$admin['id']]);

                    session_regenerate_id(true);
                    $_SESSION['admin_id'] = (int) $admin['id'];
                    $_SESSION['admin_name'] = $admin['display_name'] !== '' ? $admin['display_name'] : $admin['username'];
                    header('Location: index.php');
                    exit;
                }

                $recordAttempt = $pdo->prepare('INSERT INTO login_attempts (username, ip_address, success, attempted_at) VALUES (?, ?, 0, NOW())');
                $recordAttempt->execute([$username, $ipAddress]);
                $message = 'Invalid username or password.';
                }
            }
        }
    }
} catch (PDOException $error) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($error->getMessage());
    http_response_code(500);
    exit('The admin page could not be loaded.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin | Portfolio</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-row">
            <a class="logo" href="../index.php">Kenneth<span>.</span></a>
            <a class="btn header-btn" href="../index.php">View Portfolio</a>
        </div>
    </header>

    <main>
        <section class="contact-page container">
            <div class="contact-title">
                <span class="section-label">ADMIN</span>
                <h1><?= $isLoggedIn ? 'Admin Area' : ($isFirstAdmin ? 'Create Admin' : 'Sign In') ?></h1>
                <p><?= $isLoggedIn ? 'Manage your homepage and About page content here.' : ($isFirstAdmin ? 'Create the first admin account to secure this area.' : 'Sign in to the portfolio admin area.') ?></p>
            </div>

            <?php if ($message !== ''): ?>
                <p class="error" role="alert"><?= e($message) ?></p>
            <?php endif; ?>

            <?php if ($isLoggedIn): ?>
                <div class="card contact-form">
                    <h2>Welcome, <?= e($_SESSION['admin_name'] ?? 'Administrator') ?>.</h2>
                    <div class="grid grid-2">
                        <?php foreach ($stats as $label => $count): ?><div class="card"><strong><?= (int) $count ?></strong><p><?= e($label) ?></p></div><?php endforeach; ?>
                    </div>
                    <p><a class="btn btn-outline" href="manage.php?module=skills">Manage Content</a></p>
                    <p><a class="btn btn-outline" href="settings.php">Website Settings</a></p>
                    <p><a class="btn btn-outline" href="profile.php">Edit Profile</a></p>
                    <p><a class="btn btn-outline" href="messages.php">View Messages</a></p>
                    <form method="post" action="index.php">
                        <?= csrf() ?>
                        <button class="btn" type="submit" name="logout" value="1">Log Out</button>
                    </form>
                </div>
            <?php elseif ($isFirstAdmin): ?>
                <form class="card contact-form" method="post" action="index.php">
                    <h2>Create the first admin account</h2>
                    <label for="display_name">Display name</label>
                    <input id="display_name" name="display_name" type="text" maxlength="100" value="<?= e($displayName) ?>" autocomplete="name">

                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" required minlength="3" maxlength="30" pattern="[A-Za-z0-9_.-]{3,30}" value="<?= e($username) ?>" autocomplete="username">

                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" required minlength="12" autocomplete="new-password">

                    <label for="confirm_password">Confirm password</label>
                    <input id="confirm_password" name="confirm_password" type="password" required minlength="12" autocomplete="new-password">

                    <?= csrf() ?>
                    <button class="btn" type="submit" name="create_admin" value="1">Create Admin Account</button>
                </form>
            <?php else: ?>
                <form class="card contact-form" method="post" action="index.php">
                    <h2>Admin sign in</h2>
                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" required minlength="3" maxlength="30" pattern="[A-Za-z0-9_.-]{3,30}" value="<?= e($username) ?>" autocomplete="username">

                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password">

                    <?= csrf() ?>
                    <button class="btn" type="submit" name="login" value="1">Sign In</button>
                </form>
            <?php endif; ?>
        </section>
    </main>

    <footer class="site-footer">
        <p>&copy; <?= e(date('Y')) ?> Kenneth Tejoc · Portfolio Admin</p>
    </footer>
</body>
</html>
