 <?php
$file = 'cars.php';
if (file_exists($file)) {
    $content = file_get_contents($file);
    // Заменяем session_start(); на // session_start();
    $new_content = preg_replace('/^\s*session_start\s*\(\s*\)\s*;\s*$/m', '// session_start();', $content);
    
    if ($content !== $new_content) {
        file_put_contents($file, $new_content);
        echo "✅ Файл cars.php исправлен! session_start() закомментирован.";
    } else {
        echo "❌ В файле cars.php не найдено session_start()";
    }
} else {
    echo "❌ Файл cars.php не найден";
}
?>
