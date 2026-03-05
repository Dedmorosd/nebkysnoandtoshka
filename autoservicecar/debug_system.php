 <?php
// Максимальный уровень отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>Диагностика системы</h1>";

// Проверка PHP
echo "<h2>PHP Информация</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";

// Проверка подключения к БД
echo "<h2>Подключение к БД</h2>";
try {
    require_once 'config/database.php';
    echo "✅ database.php подключен<br>";
    
    if (isset($pdo)) {
        echo "✅ PDO объект создан<br>";
        
        // Проверка таблиц
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "📊 Таблицы в БД: " . implode(', ', $tables) . "<br>";
        
        // Проверка структуры таблицы users
        if (in_array('users', $tables)) {
            $users_cols = $pdo->query("DESCRIBE users")->fetchAll();
            echo "✅ Таблица users существует<br>";
        }
        
        // Проверка структуры таблицы clients
        if (in_array('clients', $tables)) {
            $clients_cols = $pdo->query("DESCRIBE clients")->fetchAll();
            echo "✅ Таблица clients существует<br>";
        }
        
        // Проверка структуры таблицы cars
        if (in_array('cars', $tables)) {
            $cars_cols = $pdo->query("DESCRIBE cars")->fetchAll();
            echo "✅ Таблица cars существует<br>";
        }
        
        // Проверка структуры таблицы services
        if (in_array('services', $tables)) {
            $services_cols = $pdo->query("DESCRIBE services")->fetchAll();
            echo "✅ Таблица services существует<br>";
        }
        
        // Проверка структуры таблицы orders
        if (in_array('orders', $tables)) {
            $orders_cols = $pdo->query("DESCRIBE orders")->fetchAll();
            echo "✅ Таблица orders существует<br>";
        }
    } else {
        echo "❌ PDO объект НЕ создан<br>";
    }
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "<br>";
}

// Проверка сессии
echo "<h2>Сессия</h2>";
session_start();
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Проверка файлов
echo "<h2>Проверка файлов</h2>";
$files_to_check = [
    'config/database.php',
    'includes/auth.php',
    'includes/header.php',
    'includes/footer.php',
    'cars.php',
    'orders.php',
    'services.php',
    'create_order.php',
    'view_services.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        $size = filesize($file);
        $perms = substr(sprintf('%o', fileperms($file)), -4);
        echo "✅ $file - размер: $size байт, права: $perms<br>";
    } else {
        echo "❌ $file - НЕ НАЙДЕН<br>";
    }
}
?>
