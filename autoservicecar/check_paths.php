 <?php
echo "<h2>Проверка файлов</h2>";

$files = [
    'dashboard.php',
    'config/database.php',
    'includes/auth.php',
    'includes/header.php',
    'includes/footer.php'
];

echo "<table border='1'>";
echo "<tr><th>Файл</th><th>Существует</th><th>Размер</th></tr>";
foreach ($files as $file) {
    $exists = file_exists($file) ? '✅' : '❌';
    $size = file_exists($file) ? filesize($file) . ' байт' : '-';
    echo "<tr><td>$file</td><td>$exists</td><td>$size</td></tr>";
}
echo "</table>";

if (!file_exists('includes/auth.php')) {
    echo "<p style='color:red'>❌ Файл auth.php не существует! Создайте его.</p>";
}
?>
