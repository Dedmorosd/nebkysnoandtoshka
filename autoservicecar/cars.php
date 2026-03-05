<?php
// АБСОЛЮТНЫЙ МИНИМУМ - только для теста
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "1. Начало файла<br>";

require_once 'config/database.php';
echo "2. database.php подключен<br>";

require_once 'includes/auth.php';
echo "3. auth.php подключен<br>";

session_start();
echo "4. Сессия запущена<br>";

if (!isset($_SESSION['user_id'])) {
    echo "5. Нет авторизации, редирект на login.php<br>";
    header('Location: login.php');
    exit();
}
echo "5. Пользователь авторизован: " . $_SESSION['user_id'] . "<br>";

echo "6. Подключаем header.php<br>";
require_once 'includes/header.php';

echo "7. После header.php<br>";
?>

<div class="container">
    <h1>Тестовая страница автомобилей</h1>
    <p>Если вы видите это - файл работает!</p>
    <a href="dashboard.php">Вернуться</a>
</div>

<?php
echo "8. Подключаем footer.php<br>";
require_once 'includes/footer.php';
echo "9. Конец файла<br>";
?>