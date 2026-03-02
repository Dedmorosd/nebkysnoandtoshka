 <?php
// Временный файл для проверки подключения к БД
// Удалить после использования!

require_once 'database.php';

echo "<h2>Проверка подключения к базе данных</h2>";

try {
    // Проверяем подключение простым запросом
    $result = $pdo->query("SELECT 1");
    echo "✅ Подключение к БД успешно!<br>";
    
    // Получаем информацию о сервере
    $server_info = $pdo->query("SELECT VERSION() as version")->fetch();
    echo "Версия MySQL: " . $server_info['version'] . "<br>";
    
    // Проверяем наличие таблиц
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Таблицы в базе данных '" . $GLOBALS['dbname'] . "':<br>";
    echo "<ul>";
    if (count($tables) > 0) {
        foreach ($tables as $table) {
            echo "<li>" . $table . "</li>";
        }
    } else {
        echo "<li>Нет таблиц</li>";
    }
    echo "</ul>";
    
    // Проверяем таблицу users если она существует
    if (in_array('users', $tables)) {
        $users = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch();
        echo "Количество пользователей: " . $users['count'] . "<br>";
        
        if ($users['count'] > 0) {
            $user_list = $pdo->query("SELECT id, username, email, role FROM users LIMIT 5")->fetchAll();
            echo "Первые 5 пользователей:<br>";
            echo "<pre>";
            print_r($user_list);
            echo "</pre>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка подключения: " . $e->getMessage() . "<br>";
    echo "Проверьте параметры подключения:<br>";
    echo "<ul>";
    echo "<li>Хост: " . $GLOBALS['host'] . "</li>";
    echo "<li>Порт: " . $GLOBALS['port'] . "</li>";
    echo "<li>Имя БД: " . $GLOBALS['dbname'] . "</li>";
    echo "<li>Пользователь: " . $GLOBALS['username'] . "</li>";
    echo "<li>Пароль: " . (empty($GLOBALS['password']) ? 'пустой' : 'указан') . "</li>";
    echo "</ul>";
}

echo "<p><a href='../index.php'>Вернуться на главную</a></p>";
?>
