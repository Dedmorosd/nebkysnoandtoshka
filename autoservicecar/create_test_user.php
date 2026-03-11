 <?php
require_once 'config/database.php';

echo "<h2>Создание тестового пользователя</h2>";

try {
    // Проверяем, есть ли уже пользователи
    $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "Текущее количество пользователей: $count<br>";
    
    if ($count == 0) {
        // Создаем администратора
        $username = 'admin';
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $email = 'admin@autoservice.ru';
        $full_name = 'Администратор';
        $role = 'admin';
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $password, $email, $full_name, $role]);
        
        echo "✅ Администратор создан!<br>";
        echo "Логин: admin<br>";
        echo "Пароль: admin123<br>";
    } else {
        // Показываем существующих пользователей
        $users = $pdo->query("SELECT id, username, email, role FROM users")->fetchAll();
        echo "<h3>Существующие пользователи:</h3>";
        echo "<ul>";
        foreach ($users as $user) {
            echo "<li>{$user['username']} ({$user['email']}) - {$user['role']}</li>";
        }
        echo "</ul>";
        
        // Создаем дополнительного тестового пользователя если нужно
        $test_user = 'test';
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$test_user]);
        
        if (!$check->fetch()) {
            $test_password = password_hash('test123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$test_user, $test_password, 'test@example.com', 'Тестовый Пользователь', 'client']);
            echo "✅ Тестовый пользователь создан: test / test123<br>";
        }
    }
    
    echo "<p><a href='login.php'>Перейти на страницу входа</a></p>";
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "<br>";
}
?>
