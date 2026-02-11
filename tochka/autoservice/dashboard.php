 <?php
require_once 'config/database.php';
checkAuth();
$pageTitle = "Панель управления";

// Статистика для админа/механика
if (isAdmin() || isMechanic()) {
    $orders_count = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $clients_count = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
    $pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
    $total_revenue = $pdo->query("SELECT SUM(total_price) FROM orders WHERE status = 'completed'")->fetchColumn() ?? 0;
} elseif (isClient()) {
    $user_id = $_SESSION['user_id'];
    $client_orders = $pdo->prepare("
        SELECT COUNT(*) 
        FROM orders o 
        JOIN clients c ON o.client_id = c.id 
        WHERE c.user_id = ?
    ");
    $client_orders->execute([$user_id]);
    $my_orders = $client_orders->fetchColumn();
    
    $client_cars = $pdo->prepare("
        SELECT COUNT(*) 
        FROM cars c 
        JOIN clients cl ON c.client_id = cl.id 
        WHERE cl.user_id = ?
    ");
    $client_cars->execute([$user_id]);
    $my_cars = $client_cars->fetchColumn();
}

require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4">
            <i class="fas fa-tachometer-alt"></i> Панель управления
        </h1>
        <div class="row">
            <?php if(isAdmin() || isMechanic()): ?>
                <div class="col-md-3 mb-4">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Все заказы</h6>
                                    <h2><?php echo $orders_count; ?></h2>
                                </div>
                                <i class="fas fa-clipboard-list fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Клиенты</h6>
                                    <h2><?php echo $clients_count; ?></h2>
                                </div>
                                <i class="fas fa-users fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Ожидают</h6>
                                    <h2><?php echo $pending_orders; ?></h2>
                                </div>
                                <i class="fas fa-clock fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Выручка</h6>
                                    <h2><?php echo number_format($total_revenue, 2); ?> ₽</h2>
                                </div>
                                <i class="fas fa-ruble-sign fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Последние заказы -->
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-history"></i> Последние заказы</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $stmt = $pdo->query("
                                SELECT o.*, c.full_name as client_name, s.name as service_name, 
                                       car.brand, car.model, car.license_plate
                                FROM orders o
                                JOIN clients c ON o.client_id = c.id
                                JOIN services s ON o.service_id = s.id
                                JOIN cars car ON o.car_id = car.id
                                ORDER BY o.created_at DESC LIMIT 10
                            ");
                            $orders = $stmt->fetchAll();
                            ?>
                            
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Клиент</th>
                                            <th>Автомобиль</th>
                                            <th>Услуга</th>
                                            <th>Статус</th>
                                            <th>Дата</th>
                                            <th>Стоимость</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo htmlspecialchars($order['client_name']); ?></td>
                                            <td><?php echo htmlspecialchars($order['brand'] . ' ' . $order['model'] . ' (' . $order['license_plate'] . ')'); ?></td>
                                            <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                                            <td>
                                                <?php
                                                $status_badges = [
                                                    'pending' => 'warning',
                                                    'in_progress' => 'info',
                                                    'completed' => 'success',
                                                    'cancelled' => 'danger'
                                                ];
                                                $badge_class = $status_badges[$order['status']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?php echo $badge_class; ?>">
                                                    <?php 
                                                    $status_text = [
                                                        'pending' => 'Ожидает',
                                                        'in_progress' => 'В работе',
                                                        'completed' => 'Завершен',
                                                        'cancelled' => 'Отменен'
                                                    ];
                                                    echo $status_text[$order['status']] ?? $order['status'];
                                                    ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d.m.Y', strtotime($order['created_at'])); ?></td>
                                            <td><?php echo number_format($order['total_price'], 2); ?> ₽</td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
            <?php elseif(isClient()): ?>
                <div class="col-md-6 mb-4">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Мои заказы</h6>
                                    <h2><?php echo $my_orders; ?></h2>
                                </div>
                                <i class="fas fa-clipboard-list fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Мои автомобили</h6>
                                    <h2><?php echo $my_cars; ?></h2>
                                </div>
                                <i class="fas fa-car fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Быстрые действия для клиента -->
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-bolt"></i> Быстрые действия</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <a href="cars.php" class="btn btn-primary btn-lg w-100">
                                        <i class="fas fa-plus"></i> Добавить автомобиль
                                    </a>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <a href="orders.php" class="btn btn-success btn-lg w-100">
                                        <i class="fas fa-clipboard-list"></i> Создать заказ
                                    </a>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <a href="services.php" class="btn btn-info btn-lg w-100">
                                        <i class="fas fa-search"></i> Посмотреть услуги
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
