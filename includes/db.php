<?php
$configFile = __DIR__ . '/../config.php';

if (!file_exists($configFile)) {
    die('Database settings are missing. Copy config.sample.php to config.php and enter your hosting details.');
}

$config = require $configFile;
$dsn = "mysql:host={$config['host']};dbname={$config['name']};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $config['user'], $config['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $error) {
    error_log($error->getMessage());
    die('Database connection failed. Check config.php and your database settings.');
}
