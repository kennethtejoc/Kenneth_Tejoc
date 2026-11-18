<?php
require_once __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/db.php';

try {
    $profileQuery = $pdo->prepare('SELECT full_name, resume_intro, why_hire FROM profile WHERE id = ? LIMIT 1');
    $profileQuery->execute([1]);
    $profile = $profileQuery->fetch();

    $education = $pdo->query('SELECT label, title, description FROM education ORDER BY sort_order ASC, id ASC')->fetchAll();
    $experience = $pdo->query('SELECT label, title, organization, description FROM experience ORDER BY sort_order ASC, id ASC')->fetchAll();
    $achievements = $pdo->query('SELECT title, issuer, date_awarded, description FROM achievements ORDER BY sort_order ASC, id ASC')->fetchAll();
} catch (PDOException $error) {
    error_log($error->getMessage());
    http_response_code(500);
    exit('Resume information could not be loaded.');
}

if (!$profile) {
    http_response_code(503);
    exit('The portfolio profile has not been set up yet.');
}

$fullName = !empty($profile['full_name']) ? $profile['full_name'] : 'Portfolio';
$resumeIntro = !empty($profile['resume_intro'])
    ? $profile['resume_intro']
    : 'I use this page to show my education, technical practice, and student project experience.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($fullName) ?> | Resume</title>
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
                <a href="contact.html">Contact</a>
            </nav>
            <a class="btn header-btn" href="contact.html">Hire Me</a>
            <button class="hamburger" id="hamburger" aria-label="Open menu" aria-expanded="false">☰</button>
        </div>
    </header>

    <main>
        <section class="page-section container">
            <div class="page-heading-card">
                <h1>Resume</h1>
                <p class="page-intro"><?= e($resumeIntro) ?></p>
            </div>

            <?php if ($education): ?>
                <section class="resume-section">
                    <h2>Education</h2>
                    <div class="resume-grid">
                        <?php foreach ($education as $item): ?>
                            <div class="resume-card card">
                                <p class="resume-label"><?= e($item['label']) ?></p>
                                <h3><?= e($item['title']) ?></h3>
                                <p><?= e($item['description'] ?? '') ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ($experience): ?>
                <section class="resume-section">
                    <h2>Experience</h2>
                    <div class="resume-grid">
                        <?php foreach ($experience as $item): ?>
                            <div class="resume-card card">
                                <p class="resume-label"><?= e($item['label']) ?></p>
                                <h3><?= e($item['title']) ?></h3>
                                <?php if (!empty($item['organization'])): ?>
                                    <p><strong><?= e($item['organization']) ?></strong></p>
                                <?php endif; ?>
                                <p><?= e($item['description'] ?? '') ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ($achievements): ?>
                <section class="resume-section">
                    <h2>Achievements</h2>
                    <div class="resume-grid">
                        <?php foreach ($achievements as $item): ?>
                            <div class="resume-card card">
                                <?php if (!empty($item['issuer'])): ?>
                                    <p class="resume-label"><?= e($item['issuer']) ?></p>
                                <?php endif; ?>
                                <h3><?= e($item['title']) ?></h3>
                                <?php if (!empty($item['date_awarded'])): ?>
                                    <p class="resume-label"><?= e(date('F Y', strtotime($item['date_awarded']))) ?></p>
                                <?php endif; ?>
                                <p><?= e($item['description'] ?? '') ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if (!empty($profile['why_hire'])): ?>
                <div class="resume-card card">
                    <p class="resume-label">WHY HIRE ME</p>
                    <h2>I Bring a Willingness to Learn</h2>
                    <p><?= e($profile['why_hire']) ?></p>
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
