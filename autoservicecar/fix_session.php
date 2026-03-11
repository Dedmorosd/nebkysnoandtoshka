 <?php
echo "<h2>Диагностика и исправление</h2>";

// Находим все файлы с session_start()
$files = [
    'cars.php',
    'dashboard.php',
    'login.php',
    'register.php',
    'config/database.php',
    'includes/auth.php',
    'includes/header.php'
];

echo "<h3>Поиск session_start() в файлах:</h3>";
echo "<table border='1'>";
echo "<tr><th>Файл</th><th>Содержит session_start()</th><th>Строка</th></tr>";

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $lines = file($file);
        $found = false;
        $line_num = 0;
        
        foreach ($lines as $num => $line) {
            if (strpos($line, 'session_start') !== false) {
                $found = true;
                $line_num = $num + 1;
                break;
            }
        }
        
        echo "<tr>";
        echo "<td>$file</td>";
        echo "<td>" . ($found ? '✅ ДА' : '❌ НЕТ') . "</td>";
        echo "<td>" . ($found ? $line_num : '-') . "</td>";
        echo "</tr>";
        
        // Если нашли session_start в cars.php, покажем эту строку
        if ($file == 'cars.php' && $found) {
            echo "<tr><td colspan='3' style='color:red'>";
            echo "В файле cars.php на строке $line_num есть: " . htmlspecialchars($lines[$line_num-1]);
            echo "</td></tr>";
        }
    } else {
        echo "<tr><td>$file</td><td colspan='2'>Файл не найден</td></tr>";
    }
}
echo "</table>";

echo "<h3>Инструкция по исправлению:</h3>";
echo "<ol>";
echo "<li>Откройте файл <strong>cars.php</strong></li>";
echo "<li>Найдите строку с <strong>session_start()</strong></li>";
echo "<li>Удалите или закомментируйте эту строку (добавьте // в начале)</li>";
echo "<li>Сохраните файл</li>";
echo "<li>Обновите эту страницу</li>";
echo "</ol>";

echo "<p><a href='cars.php'>Проверить cars.php после исправления</a></p>";
?>
