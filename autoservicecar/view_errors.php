 <?php
// Просмотр логов ошибок
$error_log = ini_get('error_log');
echo "<h2>PHP Error Log</h2>";
if ($error_log && file_exists($error_log)) {
    echo "<p>Log file: $error_log</p>";
    $lines = file($error_log);
    $last_lines = array_slice($lines, -50);
    echo "<pre style='background:#f8f9fa; padding:10px;'>";
    foreach ($last_lines as $line) {
        if (strpos($line, 'cars.php') !== false || 
            strpos($line, 'orders.php') !== false || 
            strpos($line, 'services.php') !== false) {
            echo htmlspecialchars($line);
        }
    }
    echo "</pre>";
} else {
    echo "<p>Error log not found</p>";
}
?>
