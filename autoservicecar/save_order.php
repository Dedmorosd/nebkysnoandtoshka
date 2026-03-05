 <?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';
require_once 'includes/auth.php';

checkAuth();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $car_id = intval($_POST['car_id'] ?? 0);
    $preferred_date = $_POST['preferred_date'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $services = $_POST['services'] ?? [];
    
    // Получаем client_id
    $stmt = $pdo->prepare("SELECT id FROM clients WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $client = $stmt->fetch();
    
    if (!$client || !$car_id || empty($services)) {
        $_SESSION['error_message'] = "Заполните все обязательные поля";
        header('Location: create_order.php');
        exit();
    }
    
    try {
        $pdo->beginTransaction();
        
        // Рассчитываем общую стоимость
        $total_price = 0;
        $placeholders = str_repeat('?,', count($services) - 1) . '?';
        $stmt = $pdo->prepare("SELECT id, price FROM services WHERE id IN ($placeholders)");
        $stmt->execute($services);
        $selected_services = $stmt->fetchAll();
        
        foreach ($selected_services as $service) {
            $total_price += $service['price'];
        }
        
        // Создаем заказ
        $stmt = $pdo->prepare("
            INSERT INTO orders (client_id, car_id, description, preferred_date, total_price, status) 
            VALUES (?, ?, ?, ?, ?, 'new')
        ");
        $stmt->execute([$client['id'], $car_id, $description, $preferred_date, $total_price]);
        $order_id = $pdo->lastInsertId();
        
        // Добавляем услуги
        $stmt = $pdo->prepare("INSERT INTO order_services (order_id, service_id, price_at_time) VALUES (?, ?, ?)");
        foreach ($selected_services as $service) {
            $stmt->execute([$order_id, $service['id'], $service['price']]);
        }
        
        $pdo->commit();
        
        $_SESSION['success_message'] = "Заказ #$order_id успешно создан!";
        header('Location: orders.php');
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Ошибка: " . $e->getMessage();
        header('Location: create_order.php');
    }
} else {
    header('Location: create_order.php');
}
?>
