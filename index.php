<?php
require_once __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/db.php';

try {
    $profileQuery = $pdo->prepare('SELECT full_name, headline, marquee_text, intro FROM profile WHERE id = ? LIMIT 1');
    $profileQuery->execute([1]);
    $profile = $profileQuery->fetch();

    $settingsQuery = $pdo->query('SELECT setting_key, setting_value FROM settings');
    $settings = [];

    foreach ($settingsQuery as $setting) {
        $settings[$setting['setting_key']] = $setting['setting_value'];
    }
} catch (PDOException $error) {
    error_log($error->getMessage());
    http_response_code(500);
    exit('Homepage information could not be loaded.');
}

if (!$profile) {
    http_response_code(503);
    exit('The portfolio profile has not been set up yet.');
}

$siteTitle = !empty($settings['site_title']) ? $settings['site_title'] : 'Portfolio';
$logoText = !empty($settings['logo_text']) ? $settings['logo_text'] : $profile['full_name'];
$marqueeText = !empty($profile['marquee_text']) ? $profile['marquee_text'] : $profile['full_name'];
$footerText = !empty($settings['footer_text']) ? $settings['footer_text'] : 'Personal Portfolio';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($siteTitle) ?> | Home</title>
    <link href="assets/images/favicon.png" rel="icon">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <script>
        if (localStorage.getItem("theme") === "light") {
            document.body.classList.add("light");
        }
    </script>

    <header class="site-header">
        <div class="container header-row">
            <a class="logo" href="index.php">
                <?= e($logoText) ?> <span>.</span>
            </a>
            <nav class="menu" id="menu" aria-label="Main navigation">
                <a href="index.php">Home</a>
                <a href="about.php">About</a>
                <a href="skills.php">Skills</a>
                <a href="projects.php">Projects</a>
                <a href="resume.php">Resume</a>
                <a href="contact.html">Contact</a>
            </nav>
            <a class="btn header-btn" href="contact.html">Hire Me</a>
            <button aria-expanded="false" aria-label="Open menu" class="hamburger" id="hamburger">☰</button>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="hero-stage">
                <div class="marquee">
                    <div class="marquee-track">
                        <span class="home-marquee"><?= e($marqueeText) ?> • </span>
                        <span class="home-marquee"><?= e($marqueeText) ?> • </span>
                        <span class="home-marquee"><?= e($marqueeText) ?> • </span>
                    </div>
                </div>
                <img class="hero-photo" src="assets/images/Profile.jpeg" alt="Photo of <?= e($profile['full_name']) ?>">
            </div>
            <div class="container hero-info">
                <div class="content-card">
                    <p class="hero-role" id="homeRole" data-text="<?= e(!empty($profile['headline']) ? $profile['headline'] : 'Welcome') ?>"></p>
                    <p><strong>Hello, I'm <span class="personal-name"><?= e($profile['full_name']) ?></span>.</strong> <?= e($profile['intro']) ?></p>
                </div>
                <div class="hero-buttons">
                    <a class="btn" id="homeProjectButton" href="projects.php">View My Projects</a>
                    <a class="btn btn-outline" id="homeContactButton" href="contact.html">Contact Me</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <p><?= e($footerText) ?></p>
        <button aria-label="Switch dark or light mode" class="theme-button" id="themeButton">Dark | Light</button>
    </footer>
    <div class="theme-circle" id="themeCircle"></div>
    <script src="assets/js/script.js"></script>
</body>
</html>
