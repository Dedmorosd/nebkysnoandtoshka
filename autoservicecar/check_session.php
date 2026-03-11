 <?php
echo "<h2>Проверка сессии</h2>";

// Проверяем статус сессии до любых действий
echo "Статус сессии до: " . session_status() . "<br>";
echo "Константы: PHP_SESSION_DISABLED = " . PHP_SESSION_DISABLED . ", ";
echo "PHP_SESSION_NONE = " . PHP_SESSION_NONE . ", ";
echo "PHP_SESSION_ACTIVE = " . PHP_SESSION_ACTIVE . "<br><br>";

// Подключаем файлы
require_once 'config/database.php';
echo "database.php подключен<br>";

require_once 'includes/auth.php';
echo "auth.php подключен<br>";

// Проверяем статус после подключения
echo "Статус сессии после: " . session_status() . "<br>";

if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✅ Сессия активна<br>";
    echo "ID сессии: " . session_id() . "<br>";
    echo "Данные сессии: <pre>";
    print_r($_SESSION);
    echo "</pre>";
} else {
    echo "❌ Сессия не активна<br>";
}

// Проверяем наличие функций
echo "<br>Проверка функций:<br>";
echo "function_exists('checkAuth'): " . (function_exists('checkAuth') ? '✅' : '❌') . "<br>";
echo "function_exists('isLoggedIn'): " . (function_exists('isLoggedIn') ? '✅' : '❌') . "<br>";
?>
