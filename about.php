<?php
require_once __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/db.php';

try {
    $profileQuery = $pdo->prepare('SELECT full_name, about_heading, about_intro, background, education_text, career_goals, interests FROM profile WHERE id = ? LIMIT 1');
    $profileQuery->execute([1]);
    $profile = $profileQuery->fetch();
} catch (PDOException $error) {
    error_log($error->getMessage());
    http_response_code(500);
    exit('About information could not be loaded.');
}

if (!$profile) {
    http_response_code(503);
    exit('The portfolio profile has not been set up yet.');
}

$fullName = !empty($profile['full_name']) ? $profile['full_name'] : 'Portfolio';
$aboutHeading = !empty($profile['about_heading']) ? $profile['about_heading'] : 'About Me';
$aboutIntro = $profile['about_intro'] ?? '';
$interests = array_filter(array_map('trim', explode(',', (string) ($profile['interests'] ?? ''))));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($fullName) ?> | About</title>
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
                <a href="skills.html">Skills</a>
                <a href="projects.html">Projects</a>
                <a href="resume.html">Resume</a>
                <a href="contact.html">Contact</a>
            </nav>
            <a class="btn header-btn" href="contact.html">Hire Me</a>
            <button class="hamburger" id="hamburger" aria-label="Open menu" aria-expanded="false">☰</button>
        </div>
    </header>

    <main>
        <section class="about-page container">
            <div class="about-title">
                <span class="section-label">ABOUT ME</span>
                <h1><?= nl2br(e($aboutHeading)) ?></h1>
                <p><?= e($aboutIntro) ?></p>
            </div>

            <div class="about-cards">
                <div class="card about-card">
                    <h3>Background</h3>
                    <p><?= e($profile['background'] ?? '') ?></p>
                </div>

                <div class="card about-card">
                    <h3>Education</h3>
                    <p><?= e($profile['education_text'] ?? '') ?></p>
                </div>

                <div class="card about-card">
                    <h3>Career Goals</h3>
                    <p><?= e($profile['career_goals'] ?? '') ?></p>
                </div>
            </div>

            <?php if ($interests): ?>
                <div class="interest-section">
                    <h2>Interests</h2>
                    <div class="interest-list">
                        <?php foreach ($interests as $interest): ?>
                            <span><?= e($interest) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
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
