 <?php
require_once 'config/database.php';
require_once 'includes/auth.php';

checkAuth();

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_role = $_SESSION['role'] ?? 'client';
$user_id = $_SESSION['user_id'];

// Получаем информацию о заказе
if ($user_role === 'admin' || $user_role === 'mechanic') {
    $stmt = $pdo->prepare("
        SELECT o.*, 
               u.full_name as client_name,
               u.phone as client_phone,
               u.email as client_email,
               c.brand, c.model, c.year, c.license_plate, c.vin,
               cl.address
        FROM orders o
        JOIN clients cl ON o.client_id = cl.id
        JOIN users u ON cl.user_id = u.id
        JOIN cars c ON o.car_id = c.id
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
} else {
    // Клиент видит только свои заказы
    $stmt = $pdo->prepare("
        SELECT o.*, 
               c.brand, c.model, c.year, c.license_plate, c.vin
        FROM orders o
        JOIN cars c ON o.car_id = c.id
        JOIN clients cl ON o.client_id = cl.id
        WHERE o.id = ? AND cl.user_id = ?
    ");
    $stmt->execute([$order_id, $user_id]);
}

$order = $stmt->fetch();

if (!$order) {
    $_SESSION['error_message'] = "Заказ не найден";
    header('Location: orders.php');
    exit();
}

// Получаем услуги заказа
$services_stmt = $pdo->prepare("
    SELECT s.*, os.price_at_time
    FROM order_services os
    JOIN services s ON os.service_id = s.id
    WHERE os.order_id = ?
");
$services_stmt->execute([$order_id]);
$services = $services_stmt->fetchAll();

$pageTitle = "Заказ #" . $order_id;
require_once 'includes/header.php';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            <i class="fas fa-clipboard-list"></i> Заказ #<?php echo $order_id; ?>
        </h1>
        <div>
            <a href="orders.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Назад к списку
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Информация о клиенте</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>ФИО:</th>
                            <td><?php echo htmlspecialchars($order['client_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Телефон:</th>
                            <td><?php echo htmlspecialchars($order['client_phone']); ?></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><?php echo htmlspecialchars($order['client_email']); ?></td>
                        </tr>
                        <tr>
                            <th>Адрес:</th>
                            <td><?php echo htmlspecialchars($order['address'] ?? 'Не указан'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Информация об автомобиле</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Марка/Модель:</th>
                            <td><?php echo htmlspecialchars($order['brand'] . ' ' . $order['model']); ?></td>
                        </tr>
                        <tr>
                            <th>Год выпуска:</th>
                            <td><?php echo $order['year']; ?></td>
                        </tr>
                        <tr>
                            <th>Госномер:</th>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($order['license_plate']); ?></span></td>
                        </tr>
                        <?php if ($order['vin']): ?>
                        <tr>
                            <th>VIN:</th>
                            <td><small><?php echo htmlspecialchars($order['vin']); ?></small></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Детали заказа</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <strong>Статус:</strong>
                </div>
                <div class="col-md-9">
                    <?php
                    $status_classes = [
                        'new' => 'bg-primary',
                        'in_progress' => 'bg-warning text-dark',
                        'completed' => 'bg-success',
                        'cancelled' => 'bg-secondary'
                    ];
                    $status_texts = [
                        'new' => 'Новый',
                        'in_progress' => 'В работе',
                        'completed' => 'Завершен',
                        'cancelled' => 'Отменен'
                    ];
                    $class = $status_classes[$order['status']] ?? 'bg-secondary';
                    $text = $status_texts[$order['status']] ?? $order['status'];
                    ?>
                    <span class="badge <?php echo $class; ?>"><?php echo $text; ?></span>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-3">
                    <strong>Предпочтительная дата:</strong>
                </div>
                <div class="col-md-9">
                    <?php echo date('d.m.Y', strtotime($order['preferred_date'])); ?>
                </div>
            </div>
            
            <?php if ($order['description']): ?>
            <div class="row mb-3">
                <div class="col-md-3">
                    <strong>Описание проблемы:</strong>
                </div>
                <div class="col-md-9">
                    <?php echo nl2br(htmlspecialchars($order['description'])); ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="row mb-3">
                <div class="col-md-3">
                    <strong>Дата создания:</strong>
                </div>
                <div class="col-md-9">
                    <?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-warning">
            <h5 class="mb-0">Услуги</h5>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Услуга</th>
                        <th>Описание</th>
                        <th class="text-end">Цена</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $service): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($service['name']); ?></td>
                        <td><?php echo htmlspecialchars($service['description']); ?></td>
                        <td class="text-end"><?php echo number_format($service['price_at_time'], 2, ',', ' '); ?> ₽</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2" class="text-end">Итого:</th>
                        <th class="text-end"><?php echo number_format($order['total_price'], 2, ',', ' '); ?> ₽</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
