 <?php
// Включаем максимальное отображение ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Проверка системы</h1>";

// Проверка версии PHP
echo "<h2>PHP Version: " . phpversion() . "</h2>";

// Проверка подключения к БД
echo "<h2>Проверка подключения к БД</h2>";
try {
    require_once 'config/database.php';
    echo "✅ database.php подключен<br>";
    
    if (isset($pdo)) {
        echo "✅ PDO объект создан<br>";
        
        // Простой запрос
        $result = $pdo->query("SELECT 1");
        echo "✅ Запрос выполнен<br>";
    } else {
        echo "❌ PDO не создан<br>";
    }
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "<br>";
}

// Проверка файлов
echo "<h2>Проверка файлов</h2>";
$files = [
    'config/database.php',
    'includes/auth.php',
    'includes/header.php',
    'includes/footer.php',
    'cars.php',
    'orders.php',
    'services.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $size = filesize($file);
        echo "✅ $file - размер: $size байт<br>";
        
        // Проверка на пустой файл
        if ($size < 10) {
            echo "⚠️ ВНИМАНИЕ: Файл $file почти пустой!<br>";
        }
    } else {
        echo "❌ $file - НЕ НАЙДЕН<br>";
    }
}

// Проверка сессии
echo "<h2>Проверка сессии</h2>";
session_start();
echo "session_id(): " . session_id() . "<br>";
echo "session_status(): " . session_status() . "<br>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Проверка прав доступа
echo "<h2>Проверка прав доступа</h2>";
echo "Текущая директория: " . getcwd() . "<br>";
echo "Права на cars.php: " . substr(sprintf('%o', fileperms('cars.php')), -4) . "<br>";
?>
