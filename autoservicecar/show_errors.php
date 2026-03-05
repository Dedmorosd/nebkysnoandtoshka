 <?php
// Показывает последние ошибки PHP
$error_log = ini_get('error_log');
echo "<h2>PHP Error Log</h2>";
if ($error_log && file_exists($error_log)) {
    echo "<p>Log file: $error_log</p>";
    $lines = file($error_log);
    $last_lines = array_slice($lines, -20);
    echo "<pre>";
    foreach ($last_lines as $line) {
        echo htmlspecialchars($line);
    }
    echo "</pre>";
} else {
    echo "<p>Error log not found or not configured.</p>";
}

// Показывает ошибки из системного лога
echo "<h2>System Error Log</h2>";
$syslog = '/var/log/apache2/error.log';
if (file_exists($syslog)) {
    $lines = file($syslog);
    $last_lines = array_slice($lines, -20);
    echo "<pre>";
    foreach ($last_lines as $line) {
        if (strpos($line, 'orders.php') !== false) {
            echo htmlspecialchars($line);
        }
    }
    echo "</pre>";
}
?>
