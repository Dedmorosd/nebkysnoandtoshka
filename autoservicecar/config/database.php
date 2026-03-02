<?php
// Запускаем сессию только если она еще не запущена
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = '134.90.167.42';
$port = 10306;
$dbname = 'project_Karachkin';
$username = 'Karachkin';
$password = '*iNPsB2D[4NO!Hfx';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
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
