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
if (!empty($_SESSION['profile_saved'])) {
    $message = 'Profile changes were saved.';
    unset($_SESSION['profile_saved']);
}
$profile = null;

try {
    $checkAdmin = $pdo->prepare('SELECT id FROM admins WHERE id = ? LIMIT 1');
    $checkAdmin->execute([(int) $_SESSION['admin_id']]);

    if (!$checkAdmin->fetch()) {
        $_SESSION = [];
        session_regenerate_id(true);
        header('Location: index.php');
        exit;
    }

    $profileQuery = $pdo->prepare('SELECT full_name, headline, marquee_text, intro FROM profile WHERE id = ? LIMIT 1');
    $profileQuery->execute([1]);
    $profile = $profileQuery->fetch();
} catch (PDOException $error) {
    error_log($error->getMessage());
    http_response_code(500);
    exit('The profile could not be loaded.');
}

if (!$profile) {
    http_response_code(503);
    exit('The portfolio profile has not been set up yet.');
}

$fullName = $profile['full_name'] ?? '';
$headline = $profile['headline'] ?? '';
$marqueeText = $profile['marquee_text'] ?? '';
$intro = $profile['intro'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf'] ?? '';
    $sessionToken = $_SESSION['csrf'] ?? '';

    if (!is_string($submittedToken) || !is_string($sessionToken) || $sessionToken === '' || !hash_equals($sessionToken, $submittedToken)) {
        $message = 'The form could not be verified. Please try again.';
    } else {
        $postedFullName = $_POST['full_name'] ?? '';
        $postedHeadline = $_POST['headline'] ?? '';
        $postedMarqueeText = $_POST['marquee_text'] ?? '';
        $postedIntro = $_POST['intro'] ?? '';

        if (!is_string($postedFullName) || !is_string($postedHeadline) || !is_string($postedMarqueeText) || !is_string($postedIntro)) {
            $message = 'Enter text in each field and try again.';
        } else {
            $fullName = trim($postedFullName);
            $headline = trim($postedHeadline);
            $marqueeText = trim($postedMarqueeText);
            $intro = trim($postedIntro);
            $introLength = preg_match_all('/./us', $intro);

            if ($fullName === '') {
                $message = 'Please enter a name.';
            } elseif ($introLength === false || $introLength > 5000) {
                $message = 'The introduction must be 5,000 characters or less.';
            } else {
                try {
                    $updateProfile = $pdo->prepare('UPDATE profile SET full_name = ?, headline = ?, marquee_text = ?, intro = ? WHERE id = ?');
                    $updateProfile->execute([$fullName, $headline, $marqueeText, $intro, 1]);
                    $_SESSION['profile_saved'] = true;
                    header('Location: profile.php');
                    exit;
                } catch (PDOException $error) {
                    error_log($error->getMessage());
                    $message = 'The profile could not be saved. Please check the field lengths and try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Profile | Portfolio Admin</title>
    <link rel="stylesheet" href="../style.css">
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
                <h1>Edit Homepage Profile</h1>
                <p>Update the name, headline, scrolling text, and introduction shown on the homepage.</p>
            </div>

            <?php if ($message !== ''): ?>
                <p class="form-success" role="status"><?= e($message) ?></p>
            <?php endif; ?>

            <form class="card contact-form" method="post" action="profile.php">
                <label for="full_name">Name</label>
                <input id="full_name" name="full_name" type="text" required maxlength="100" value="<?= e($fullName) ?>">

                <label for="headline">Headline</label>
                <input id="headline" name="headline" type="text" maxlength="150" value="<?= e($headline) ?>">

                <label for="marquee_text">Scrolling text</label>
                <input id="marquee_text" name="marquee_text" type="text" maxlength="150" value="<?= e($marqueeText) ?>">

                <label for="intro">Introduction</label>
                <textarea id="intro" name="intro" rows="6" maxlength="5000"><?= e($intro) ?></textarea>

                <?= csrf() ?>
                <button class="btn" type="submit">Save Profile</button>
            </form>
        </section>
    </main>

    <footer class="site-footer">
        <p>&copy; <?= e(date('Y')) ?> Kenneth Tejoc · Portfolio Admin</p>
    </footer>
</body>
</html>
