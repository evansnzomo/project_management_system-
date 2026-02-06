<?php
// db.php
// Database configuration
$DB_HOST = 'localhost';
$DB_NAME = 'project_tracking';
$DB_USER = 'evans';
$DB_PASS = 'YES';

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fetch rows as associative arrays
    PDO::ATTR_EMULATE_PREPARES => false, // Use native prepared statements
];

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        $options
    );
} catch (PDOException $e) {
    // In production, log this instead of echo
    die("Database connection failed: " . $e->getMessage());
}
