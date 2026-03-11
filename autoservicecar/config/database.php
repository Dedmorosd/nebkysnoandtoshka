<?php
// Файл: config/database.php

$host = '134.90.167.42';
$port = 10306;
$dbname = 'project_Karachkin';
$username = 'Karachkin';
$password = '*iNPsB2D[4NO!Hfx';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // НИКАКИХ session_start() ЗДЕСЬ!
    
} catch(PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>