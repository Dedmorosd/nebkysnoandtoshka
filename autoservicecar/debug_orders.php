 <?php
// Максимальный уровень отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<!-- Начало отладки -->\n";

try {
    echo "<!-- Проверка 1: Начало файла -->\n";
    
    // Проверяем существование файлов
    $required_files = [
        'config/database.php',
        'includes/auth.php',
        'includes/header.php',
        'includes/footer.php'
    ];
    
    foreach ($required_files as $file) {
        echo "<!-- Проверка файла: $file - " . (file_exists($file) ? 'НАЙДЕН' : 'НЕ НАЙДЕН') . " -->\n";
        if (!file_exists($file)) {
            throw new Exception("Файл не найден: $file");
        }
    }
    
    echo "<!-- Проверка 2: Подключение файлов -->\n";
    require_once 'config/database.php';
    echo "<!-- database.php подключен успешно -->\n";
    
    require_once 'includes/auth.php';
    echo "<!-- auth.php подключен успешно -->\n";
    
    // Проверка подключения к БД
    if (isset($pdo)) {
        echo "<!-- PDO объект существует -->\n";
        $tables = $pdo->query("SHOW TABLES")->fetchAll();
        echo "<!-- Таблицы в БД: " . count($tables) . " -->\n";
    } else {
        echo "<!-- PDO объект НЕ существует -->\n";
    }
    
    // Проверка сессии
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        echo "<!-- Сессия запущена -->\n";
    }
    
    // Простой вывод
    echo "<h1>Отладка работает!</h1>";
    echo "<p>Если вы видите это сообщение, PHP выполняется нормально.</p>";
    
    // Информация о сервере
    echo "<h2>Информация о сервере:</h2>";
    echo "<pre>";
    echo "PHP Version: " . phpversion() . "\n";
    echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
    echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
    echo "Script Filename: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
    echo "</pre>";
    
    // Проверка сессии
    echo "<h2>Сессия:</h2>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<div style='color: red; border: 2px solid red; padding: 10px; margin: 10px;'>";
    echo "<h3>ОШИБКА:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Файл: " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "</div>";
}

// Если дошли до сюда, покажем все ошибки
echo "<!-- Конец отладки -->\n";
?>
