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

    $profileQuery = $pdo->prepare('SELECT full_name, headline, marquee_text, intro, profile_image, about_heading, about_intro, background, education_text, career_goals, interests, email, phone, location, facebook_url, github_url FROM profile WHERE id = ? LIMIT 1');
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
$aboutHeading = $profile['about_heading'] ?? '';
$aboutIntro = $profile['about_intro'] ?? '';
$background = $profile['background'] ?? '';
$educationText = $profile['education_text'] ?? '';
$careerGoals = $profile['career_goals'] ?? '';
$interests = $profile['interests'] ?? '';
$profileImage = $profile['profile_image'] ?? '';
$email = $profile['email'] ?? '';
$phone = $profile['phone'] ?? '';
$location = $profile['location'] ?? '';
$facebookUrl = $profile['facebook_url'] ?? '';
$githubUrl = $profile['github_url'] ?? '';

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
        $postedAboutHeading = $_POST['about_heading'] ?? '';
        $postedAboutIntro = $_POST['about_intro'] ?? '';
        $postedBackground = $_POST['background'] ?? '';
        $postedEducationText = $_POST['education_text'] ?? '';
        $postedCareerGoals = $_POST['career_goals'] ?? '';
        $postedInterests = $_POST['interests'] ?? '';
        $postedEmail = $_POST['email'] ?? '';
        $postedPhone = $_POST['phone'] ?? '';
        $postedLocation = $_POST['location'] ?? '';
        $postedFacebookUrl = $_POST['facebook_url'] ?? '';
        $postedGithubUrl = $_POST['github_url'] ?? '';

        if (
            !is_string($postedFullName) ||
            !is_string($postedHeadline) ||
            !is_string($postedMarqueeText) ||
            !is_string($postedIntro) ||
            !is_string($postedAboutHeading) ||
            !is_string($postedAboutIntro) ||
            !is_string($postedBackground) ||
            !is_string($postedEducationText) ||
            !is_string($postedCareerGoals) ||
            !is_string($postedInterests) ||
            !is_string($postedEmail) ||
            !is_string($postedPhone) ||
            !is_string($postedLocation) ||
            !is_string($postedFacebookUrl) ||
            !is_string($postedGithubUrl)
        ) {
            $message = 'Enter text in each field and try again.';
        } else {
            $fullName = trim($postedFullName);
            $headline = trim($postedHeadline);
            $marqueeText = trim($postedMarqueeText);
            $intro = trim($postedIntro);
            $aboutHeading = trim($postedAboutHeading);
            $aboutIntro = trim($postedAboutIntro);
            $background = trim($postedBackground);
            $educationText = trim($postedEducationText);
            $careerGoals = trim($postedCareerGoals);
            $interests = trim($postedInterests);
            $email = trim($postedEmail);
            $phone = trim($postedPhone);
            $location = trim($postedLocation);
            $facebookUrl = trim($postedFacebookUrl);
            $githubUrl = trim($postedGithubUrl);
            $uploadError = '';
            try {
                $uploadedImage = upload_image($_FILES['profile_image'] ?? [], __DIR__ . '/../assets/images/uploads');
                if ($uploadedImage !== '') {
                    $profileImage = $uploadedImage;
                }
            } catch (RuntimeException $uploadException) {
                $uploadError = $uploadException->getMessage();
            }

            $fullNameLength = preg_match_all('/./us', $fullName);
            $headlineLength = preg_match_all('/./us', $headline);
            $marqueeLength = preg_match_all('/./us', $marqueeText);
            $introLength = preg_match_all('/./us', $intro);
            $aboutHeadingLength = preg_match_all('/./us', $aboutHeading);
            $aboutIntroLength = preg_match_all('/./us', $aboutIntro);
            $backgroundLength = preg_match_all('/./us', $background);
            $educationLength = preg_match_all('/./us', $educationText);
            $careerGoalsLength = preg_match_all('/./us', $careerGoals);
            $interestsLength = preg_match_all('/./us', $interests);
            $emailLength = preg_match_all('/./us', $email);
            $phoneLength = preg_match_all('/./us', $phone);
            $locationLength = preg_match_all('/./us', $location);
            $facebookLength = preg_match_all('/./us', $facebookUrl);
            $githubLength = preg_match_all('/./us', $githubUrl);

            if ($uploadError !== '') {
                $message = $uploadError;
            } elseif ($fullName === '') {
                $message = 'Please enter a name.';
            } elseif (
                $fullNameLength === false || $fullNameLength > 100 ||
                $headlineLength === false || $headlineLength > 150 ||
                $marqueeLength === false || $marqueeLength > 150 ||
                $introLength === false || $introLength > 5000 ||
                $aboutHeadingLength === false || $aboutHeadingLength > 80 ||
                $aboutIntroLength === false || $aboutIntroLength > 5000 ||
                $backgroundLength === false || $backgroundLength > 5000 ||
                $educationLength === false || $educationLength > 5000 ||
                $careerGoalsLength === false || $careerGoalsLength > 5000 ||
                $interestsLength === false || $interestsLength > 500 ||
                $emailLength === false || $emailLength > 150 ||
                $phoneLength === false || $phoneLength > 40 ||
                $locationLength === false || $locationLength > 150 ||
                $facebookLength === false || $facebookLength > 255 ||
                $githubLength === false || $githubLength > 255
            ) {
                $message = 'One or more fields are too long or contain invalid text.';
            } else {
                try {
                    $updateProfile = $pdo->prepare('UPDATE profile SET full_name = ?, headline = ?, marquee_text = ?, intro = ?, profile_image = ?, about_heading = ?, about_intro = ?, background = ?, education_text = ?, career_goals = ?, interests = ?, email = ?, phone = ?, location = ?, facebook_url = ?, github_url = ? WHERE id = ?');
                    $updateProfile->execute([
                        $fullName,
                        $headline,
                        $marqueeText,
                        $intro,
                        $profileImage,
                        $aboutHeading,
                        $aboutIntro,
                        $background,
                        $educationText,
                        $careerGoals,
                        $interests,
                        $email,
                        $phone,
                        $location,
                        $facebookUrl,
                        $githubUrl,
                        1,
                    ]);
                    $_SESSION['profile_saved'] = true;
                    header('Location: profile.php');
                    exit;
                } catch (PDOException $error) {
                    error_log($error->getMessage());
                    $message = 'The profile could not be saved. Please try again.';
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
                <h1>Edit Profile</h1>
                <p>Update the content shown on the homepage and About page.</p>
            </div>

            <?php if ($message !== ''): ?>
                <p class="form-success" role="status"><?= e($message) ?></p>
            <?php endif; ?>

            <form class="card contact-form" method="post" action="profile.php" enctype="multipart/form-data">
                <h2>Homepage</h2>
                <label for="full_name">Name</label>
                <input id="full_name" name="full_name" type="text" required maxlength="100" value="<?= e($fullName) ?>">

                <label for="headline">Headline</label>
                <input id="headline" name="headline" type="text" maxlength="150" value="<?= e($headline) ?>">

                <label for="marquee_text">Scrolling text</label>
                <input id="marquee_text" name="marquee_text" type="text" maxlength="150" value="<?= e($marqueeText) ?>">

                <label for="intro">Introduction</label>
                <textarea id="intro" name="intro" rows="5" maxlength="5000"><?= e($intro) ?></textarea>

                <label for="profile_image">Profile image (JPEG, PNG, GIF, or WebP; max 5 MB)</label>
                <input id="profile_image" name="profile_image" type="file" accept="image/jpeg,image/png,image/gif,image/webp">

                <h2>About page</h2>
                <label for="about_heading">About heading</label>
                <textarea id="about_heading" name="about_heading" rows="2" maxlength="80"><?= e($aboutHeading) ?></textarea>

                <label for="about_intro">About introduction</label>
                <textarea id="about_intro" name="about_intro" rows="5" maxlength="5000"><?= e($aboutIntro) ?></textarea>

                <label for="background">Background</label>
                <textarea id="background" name="background" rows="5" maxlength="5000"><?= e($background) ?></textarea>

                <label for="education_text">Education</label>
                <textarea id="education_text" name="education_text" rows="5" maxlength="5000"><?= e($educationText) ?></textarea>

                <label for="career_goals">Career goals</label>
                <textarea id="career_goals" name="career_goals" rows="5" maxlength="5000"><?= e($careerGoals) ?></textarea>

                <label for="interests">Interests (comma-separated)</label>
                <input id="interests" name="interests" type="text" maxlength="500" value="<?= e($interests) ?>">

                <h2>Contact details</h2>
                <label for="email">Email</label>
                <input id="email" name="email" type="email" maxlength="150" value="<?= e($email) ?>">
                <label for="phone">Phone</label>
                <input id="phone" name="phone" type="text" maxlength="40" value="<?= e($phone) ?>">
                <label for="location">Location</label>
                <input id="location" name="location" type="text" maxlength="150" value="<?= e($location) ?>">
                <label for="facebook_url">Facebook URL</label>
                <input id="facebook_url" name="facebook_url" type="url" maxlength="255" value="<?= e($facebookUrl) ?>">
                <label for="github_url">GitHub URL</label>
                <input id="github_url" name="github_url" type="url" maxlength="255" value="<?= e($githubUrl) ?>">

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
