<?php
require_once __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/db.php';

try {
    $profileQuery = $pdo->prepare('SELECT full_name, skills_intro FROM profile WHERE id = ? LIMIT 1');
    $profileQuery->execute([1]);
    $profile = $profileQuery->fetch();

    $skillsQuery = $pdo->query('SELECT name, category, level, description FROM skills ORDER BY sort_order ASC, id ASC');
    $skills = $skillsQuery->fetchAll();
} catch (PDOException $error) {
    error_log($error->getMessage());
    http_response_code(500);
    exit('Skills information could not be loaded.');
}

if (!$profile) {
    http_response_code(503);
    exit('The portfolio profile has not been set up yet.');
}

$fullName = !empty($profile['full_name']) ? $profile['full_name'] : 'Portfolio';
$skillsIntro = !empty($profile['skills_intro'])
    ? $profile['skills_intro']
    : 'Technical skills developed through coursework, practice, and school projects.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($fullName) ?> | Skills</title>
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
                <h1>Tools &amp; Skills</h1>
                <p class="page-intro"><?= e($skillsIntro) ?></p>
            </div>

            <?php if ($skills): ?>
                <div class="grid grid-3 skill-list">
                    <?php foreach ($skills as $skill): ?>
                        <?php $level = max(0, min(100, (int) $skill['level'])); ?>
                        <button class="card skill-card" type="button">
                            <span>
                                <h3><?= e($skill['name']) ?></h3>
                                <p><?= e($level) ?>%</p>
                            </span>
                            <span class="skill-description"><?= e($skill['description'] ?? '') ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No skills have been added yet.</p>
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
