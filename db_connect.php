<?php
// db_connect.php

$host   = 'localhost';
$dbname = 'openlib';  // ← Changed from 'openlib_db' to match your existing database
$user   = 'root';
$pass   = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("<p style='color:red;padding:20px;font-family:sans-serif;'>
        ❌ Database connection failed: " . htmlspecialchars($e->getMessage()) . "
        <br>Check your db_connect.php settings.
    </p>");
}
?>