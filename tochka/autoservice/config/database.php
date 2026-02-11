<?php
session_start();

define('DB_HOST', '134.90.167.42:10306');
define('DB_USER', 'Karachkin');
define('DB_PASS', '*iNPsB2D[4NO!Hfx');
define('DB_NAME', 'project_Karachkin'); // Укажите имя вашей базы данных

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch(PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

function isMechanic() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'mechanic';
}

function isClient() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'client';
}
?> 
