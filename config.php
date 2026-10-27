<?php
// Update these values only if your MySQL settings are different.
$host = 'localhost';
$dbname = 'webdev_business_db';
$dbuser = 'root';
$dbpass = '';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $dbuser,
        $dbpass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $error) {
    exit('Database connection failed. Check config.php and make sure MySQL is running.');
}
?>
