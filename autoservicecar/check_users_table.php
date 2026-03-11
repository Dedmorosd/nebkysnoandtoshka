 <?php
require_once 'config/database.php';

echo "<h2>Проверка таблицы users</h2>";

try {
    // Проверяем существует ли таблица
    $tables = $pdo->query("SHOW TABLES LIKE 'users'")->rowCount();
    
    if ($tables > 0) {
        echo "✅ Таблица users существует<br><br>";
        
        // Показываем структуру
        echo "<h3>Структура таблицы:</h3>";
        $columns = $pdo->query("DESCRIBE users")->fetchAll();
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Поле</th><th>Тип</th><th>Null</th><th>Ключ</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>{$col['Field']}</td>";
            echo "<td>{$col['Type']}</td>";
            echo "<td>{$col['Null']}</td>";
            echo "<td>{$col['Key']}</td>";
            echo "</tr>";
        }
        echo "</table><br>";
        
        // Показываем пользователей
        echo "<h3>Пользователи:</h3>";
        $users = $pdo->query("SELECT id, username, email, role, password FROM users")->fetchAll();
        
        if (count($users) > 0) {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>Логин</th><th>Email</th><th>Роль</th><th>Хеш пароля</th></tr>";
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td>{$user['id']}</td>";
                echo "<td>{$user['username']}</td>";
                echo "<td>{$user['email']}</td>";
                echo "<td>{$user['role']}</td>";
                echo "<td style='font-size:12px;'>{$user['password']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>❌ Нет пользователей в таблице!</p>";
            echo "<p><a href='create_test_user.php'>Создать тестового пользователя</a></p>";
        }
    } else {
        echo "❌ Таблица users не существует!<br>";
        echo "<p>Создайте таблицу выполнив SQL:</p>";
        echo "<pre>
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'mechanic', 'client') DEFAULT 'client',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
        </pre>";
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "<br>";
}
?>
