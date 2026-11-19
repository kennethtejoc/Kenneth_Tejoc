<?php
// e() makes text safe to display in HTML.
function e($text)
{
    return htmlspecialchars((string) $text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function upload_image(array $file, string $directory): string
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !isset($file['tmp_name'], $file['size']) || $file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('The image must be a valid file no larger than 5 MB.');
    }
    $info = @getimagesize($file['tmp_name']);
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    if ($info === false || !isset($allowed[$mime])) {
        throw new RuntimeException('Only valid JPEG, PNG, GIF, and WebP images are allowed.');
    }
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('The image directory could not be created.');
    }
    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename)) {
        throw new RuntimeException('The image could not be saved.');
    }
    return 'uploads/' . $filename;
}

function portfolio_image_url(string $storedName, string $fallback = ''): string
{
    $storedName = str_replace('\\', '/', $storedName);
    $storedName = ltrim($storedName, '/');
    if ($storedName !== '' && $storedName === preg_replace('#\.\./#', '', $storedName) && preg_match('#^(?:uploads/)?[A-Za-z0-9._/-]+$#', $storedName)) {
        $candidate = __DIR__ . '/../assets/images/' . $storedName;
        if (is_file($candidate)) {
            return 'assets/images/' . $storedName;
        }
    }
    return $fallback;
}
