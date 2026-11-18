<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/security.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

require __DIR__ . '/includes/db.php';

$message = '';
$senderName = '';
$senderEmail = '';
$subject = '';
$body = '';
$messageIsSuccess = false;

try {
    $profileQuery = $pdo->prepare('SELECT full_name, contact_intro, email, facebook_url, github_url FROM profile WHERE id = ? LIMIT 1');
    $profileQuery->execute([1]);
    $profile = $profileQuery->fetch();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $postedName = $_POST['name'] ?? '';
        $postedEmail = $_POST['email'] ?? '';
        $postedSubject = $_POST['subject'] ?? '';
        $postedMessage = $_POST['message'] ?? '';
        $submittedToken = $_POST['csrf'] ?? '';
        $sessionToken = $_SESSION['csrf'] ?? '';

        $senderName = is_string($postedName) ? trim($postedName) : '';
        $senderEmail = is_string($postedEmail) ? trim($postedEmail) : '';
        $subject = is_string($postedSubject) ? trim($postedSubject) : '';
        $body = is_string($postedMessage) ? trim($postedMessage) : '';
        $nameLength = preg_match_all('/./us', $senderName);
        $emailLength = preg_match_all('/./us', $senderEmail);
        $subjectLength = preg_match_all('/./us', $subject);
        $bodyLength = preg_match_all('/./us', $body);

        if (!is_string($submittedToken) || !is_string($sessionToken) || $sessionToken === '' || !hash_equals($sessionToken, $submittedToken)) {
            $message = 'The form could not be verified. Please try again.';
        } elseif (!is_string($postedName) || !is_string($postedEmail) || !is_string($postedSubject) || !is_string($postedMessage)) {
            $message = 'Enter text in each field and try again.';
        } elseif ($nameLength === false || $senderName === '' || $nameLength > 100) {
            $message = 'Please enter a name of 100 characters or fewer.';
        } elseif ($emailLength === false || !filter_var($senderEmail, FILTER_VALIDATE_EMAIL) || $emailLength > 150) {
            $message = 'Please enter a valid email address of 150 characters or fewer.';
        } elseif ($subjectLength === false || $subject === '' || $subjectLength > 150) {
            $message = 'Please enter a subject of 150 characters or fewer.';
        } elseif ($bodyLength === false || $bodyLength < 10 || $bodyLength > 10000) {
            $message = 'Your message must be between 10 and 10,000 characters.';
        } else {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
            if (!is_string($ipAddress) || filter_var($ipAddress, FILTER_VALIDATE_IP) === false) {
                $ipAddress = '';
            }

            $recentMessages = $pdo->prepare('SELECT COUNT(*) FROM messages WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)');
            $recentMessages->execute([$ipAddress]);

            if ((int) $recentMessages->fetchColumn() >= 5) {
                $message = 'Too many messages have been sent recently. Please try again later.';
            } else {
                $saveMessage = $pdo->prepare('INSERT INTO messages (name, email, subject, message, ip_address, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
                $saveMessage->execute([$senderName, $senderEmail, $subject, $body, $ipAddress]);

                $_SESSION['contact_notice'] = 'Your message was sent successfully. Thank you for getting in touch.';
                header('Location: contact.php');
                exit;
            }
        }
    }
} catch (PDOException $error) {
    error_log($error->getMessage());
    http_response_code(500);
    exit('The contact form could not be processed.');
}

if (!empty($_SESSION['contact_notice'])) {
    $message = $_SESSION['contact_notice'];
    $messageIsSuccess = true;
    unset($_SESSION['contact_notice']);
}

if (!$profile) {
    http_response_code(503);
    exit('The portfolio profile has not been set up yet.');
}

$fullName = !empty($profile['full_name']) ? $profile['full_name'] : 'Portfolio';
$contactIntro = !empty($profile['contact_intro'])
    ? $profile['contact_intro']
    : 'I’m open to project ideas, questions, and collaboration. Send me a message using the form below.';
$email = trim((string) ($profile['email'] ?? ''));
$facebookUrl = trim((string) ($profile['facebook_url'] ?? ''));
$githubUrl = trim((string) ($profile['github_url'] ?? ''));

function contactExternalUrl($url)
{
    if (filter_var($url, FILTER_VALIDATE_URL) === false) {
        return '';
    }

    $parts = parse_url($url);
    if (!is_array($parts) || !in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)) {
        return '';
    }

    return $url;
}

$facebookUrl = contactExternalUrl($facebookUrl);
$githubUrl = contactExternalUrl($githubUrl);
$emailIsValid = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($fullName) ?> | Contact</title>
    <link rel="icon" href="assets/images/favicon.png">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <script>
        if (localStorage.getItem("theme") === "light") {
            document.body.classList.add("light");
        }
    </script>

    <header class="site-header">
        <div class="container header-row">
            <a class="logo" href="index.php"><?= e($fullName) ?><span>.</span></a>
            <nav class="menu" id="menu" aria-label="Main navigation">
                <a href="index.php">Home</a>
                <a href="about.php">About</a>
                <a href="skills.php">Skills</a>
                <a href="projects.php">Projects</a>
                <a href="resume.php">Resume</a>
                <a href="contact.php">Contact</a>
            </nav>
            <a class="btn header-btn" href="contact.php">Hire Me</a>
            <button class="hamburger" id="hamburger" aria-label="Open menu" aria-expanded="false">☰</button>
        </div>
    </header>

    <main>
        <section class="contact-page container">
            <div class="contact-title">
                <span class="section-label">CONTACT</span>
                <h1>Let’s Connect</h1>
                <p><?= e($contactIntro) ?></p>
            </div>

            <div class="contact-links">
                <?php if ($facebookUrl !== ''): ?>
                    <a class="contact-link facebook" href="<?= e($facebookUrl) ?>" target="_blank" rel="noopener noreferrer">
                        <span class="contact-icon">f</span>
                        <span><small>FACEBOOK</small><?= e($fullName) ?></span>
                    </a>
                <?php endif; ?>

                <?php if ($githubUrl !== ''): ?>
                    <a class="contact-link github" href="<?= e($githubUrl) ?>" target="_blank" rel="noopener noreferrer">
                        <span class="contact-icon">g</span>
                        <span><small>GITHUB</small><?= e(parse_url($githubUrl, PHP_URL_PATH) ?: $githubUrl) ?></span>
                    </a>
                <?php endif; ?>

                <?php if ($emailIsValid): ?>
                    <a class="contact-link email-link" href="mailto:<?= e($email) ?>">
                        <span class="contact-icon">@</span>
                        <span><small>EMAIL</small><?= e($email) ?></span>
                    </a>
                <?php endif; ?>
            </div>

            <?php if ($message !== ''): ?>
                <p class="<?= $messageIsSuccess ? 'form-success' : 'error' ?>" role="status"><?= e($message) ?></p>
            <?php endif; ?>

            <form class="card contact-form" id="serverContactForm" method="post" action="contact.php">
                <h2>Send a Message</h2>

                <label for="name">Name</label>
                <input id="name" name="name" placeholder="Your name" type="text" required maxlength="100" autocomplete="name" value="<?= e($senderName) ?>">

                <label for="email">Email</label>
                <input id="email" name="email" placeholder="you@example.com" type="email" required maxlength="150" autocomplete="email" value="<?= e($senderEmail) ?>">

                <label for="subject">Subject</label>
                <input id="subject" name="subject" type="text" required maxlength="150" value="<?= e($subject) ?>">

                <label for="message">Message</label>
                <textarea id="message" name="message" placeholder="Write your message here" rows="5" required minlength="10" maxlength="10000"><?= e($body) ?></textarea>
                <span class="counter">Your message must be at least 10 characters.</span>

                <?= csrf() ?>
                <button class="btn" type="submit">Send Message</button>
            </form>
        </section>
    </main>

    <footer class="site-footer">
        <p>&copy; <?= e(date('Y')) ?> <?= e($fullName) ?> · Personal Portfolio</p>
        <button class="theme-button" id="themeButton" aria-label="Switch dark or light mode">Dark | Light</button>
    </footer>

    <div class="theme-circle" id="themeCircle"></div>
    <script src="assets/js/script.js"></script>
</body>
</html>
