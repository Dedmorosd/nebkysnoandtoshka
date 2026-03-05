<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = '134.90.167.42';
$port = 10306;
$dbname = 'project_Karachkin';
$username = 'Karachkin';
$password = '*iNPsB2D[4NO!Hfx';

try {
    echo "<!-- Подключение к БД... -->\n";
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    echo "<!-- Подключение к БД успешно -->\n";
} catch(PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}
?>