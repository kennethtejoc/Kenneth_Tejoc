<?php
require_once __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/db.php';

try {
    $profileQuery = $pdo->prepare('SELECT full_name, projects_intro FROM profile WHERE id = ? LIMIT 1');
    $profileQuery->execute([1]);
    $profile = $profileQuery->fetch();

    $projectsQuery = $pdo->query('SELECT title, category, status, description, tags, image, link_url, link_label FROM projects ORDER BY sort_order ASC, id ASC');
    $projects = $projectsQuery->fetchAll();
} catch (PDOException $error) {
    error_log($error->getMessage());
    http_response_code(500);
    exit('Projects information could not be loaded.');
}

if (!$profile) {
    http_response_code(503);
    exit('The portfolio profile has not been set up yet.');
}

$fullName = !empty($profile['full_name']) ? $profile['full_name'] : 'Portfolio';
$projectsIntro = !empty($profile['projects_intro'])
    ? $profile['projects_intro']
    : 'School and personal projects showing my experience in development, design, networking, and problem-solving.';
$categories = [];

foreach ($projects as $project) {
    $category = trim((string) ($project['category'] ?? ''));

    if ($category !== '' && !in_array($category, $categories, true)) {
        $categories[] = $category;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($fullName) ?> | Projects</title>
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
        <section class="projects-page container">
            <div class="projects-title">
                <span class="section-label">PROJECTS</span>
                <h1>Selected Work</h1>
                <p><?= e($projectsIntro) ?></p>
            </div>

            <?php if ($projects): ?>
                <div class="project-tools project-tools-new">
                    <input class="search-box" id="projectSearch" type="search" aria-label="Search projects" placeholder="Search projects or technologies...">
                    <div class="filters">
                        <button class="filter-btn" type="button" data-filter="All">All</button>
                        <?php foreach ($categories as $category): ?>
                            <button class="filter-btn" type="button" data-filter="<?= e($category) ?>"><?= e($category) ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <p class="result-count" id="resultCount" aria-live="polite"></p>

                <div class="project-list">
                    <?php foreach ($projects as $project): ?>
                        <?php
                        $category = (string) ($project['category'] ?? '');
                        $image = trim(str_replace('\\', '/', (string) ($project['image'] ?? '')));
                        $imageUrl = portfolio_image_url($image);
                        $tags = array_filter(array_map('trim', explode(',', (string) ($project['tags'] ?? ''))));
                        $linkUrl = trim((string) ($project['link_url'] ?? ''));
                        $linkLabel = trim((string) ($project['link_label'] ?? ''));
                        $linkParts = $linkUrl !== '' ? parse_url($linkUrl) : false;
                        $showLink = $linkLabel !== ''
                            && filter_var($linkUrl, FILTER_VALIDATE_URL) !== false
                            && is_array($linkParts)
                            && in_array(strtolower($linkParts['scheme'] ?? ''), ['http', 'https'], true);
                        ?>
                        <section class="card project" data-category="<?= e($category) ?>">
                            <?php if ($imageUrl !== ''): ?>
                                <img src="<?= e($imageUrl) ?>" alt="<?= e($project['title']) ?> preview">
                            <?php endif; ?>
                            <span class="status"><?= e($project['status']) ?></span>
                            <h3><?= e($project['title']) ?></h3>
                            <p><?= e($project['description'] ?? '') ?></p>
                            <?php if ($tags): ?>
                                <div class="tags">
                                    <?php foreach ($tags as $tag): ?>
                                        <span><?= e($tag) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($showLink): ?>
                                <a class="btn" href="<?= e($linkUrl) ?>" target="_blank" rel="noopener noreferrer"><?= e($linkLabel) ?></a>
                            <?php endif; ?>
                        </section>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No projects have been added yet.</p>
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
