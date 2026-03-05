 <?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Проверяем права администратора
if (!isAdmin()) {
    header('Location: dashboard.php');
    exit();
}

$pageTitle = "Админ панель";

// Получаем статистику
$stats = [
    'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'clients' => $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn(),
    'cars' => $pdo->query("SELECT COUNT(*) FROM cars")->fetchColumn(),
    'orders' => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'new_orders' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'new'")->fetchColumn(),
    'in_progress' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'in_progress'")->fetchColumn(),
    'completed' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'completed'")->fetchColumn(),
    'cancelled' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'cancelled'")->fetchColumn(),
    'revenue' => $pdo->query("SELECT SUM(total_price) FROM orders WHERE status = 'completed'")->fetchColumn() ?? 0,
    'services' => $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn(),
    'mechanics' => $pdo->query("SELECT COUNT(*) FROM employees")->fetchColumn(),
];

// Последние заказы
$recent_orders = $pdo->query("
    SELECT o.*, u.full_name as client_name, c.brand, c.model, c.license_plate
    FROM orders o
    JOIN clients cl ON o.client_id = cl.id
    JOIN users u ON cl.user_id = u.id
    JOIN cars c ON o.car_id = c.id
    ORDER BY o.created_at DESC
    LIMIT 10
")->fetchAll();

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <!-- Заголовок -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-cog"></i> Панель администратора</h1>
        <div>
            <span class="badge bg-primary">Администратор</span>
        </div>
    </div>

    <!-- Статистика -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Пользователи</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['users']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Клиенты</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['clients']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-tie fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Автомобили</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['cars']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-car fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Заказы всего</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['orders']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Детальная статистика -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-primary text-white shadow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Новые заказы</div>
                            <div class="h3"><?php echo $stats['new_orders']; ?></div>
                        </div>
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-warning text-white shadow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">В работе</div>
                            <div class="h3"><?php echo $stats['in_progress']; ?></div>
                        </div>
                        <i class="fas fa-tools fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-success text-white shadow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Завершено</div>
                            <div class="h3"><?php echo $stats['completed']; ?></div>
                        </div>
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-info text-white shadow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Выручка</div>
                            <div class="h3"><?php echo number_format($stats['revenue'], 0, ',', ' '); ?> ₽</div>
                        </div>
                        <i class="fas fa-ruble-sign fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Навигация по разделам -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-cog"></i> Управление системой</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="admin_users.php" class="btn btn-outline-primary btn-lg w-100 py-4">
                                <i class="fas fa-users fa-3x mb-2"></i><br>
                                Пользователи
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="admin_clients.php" class="btn btn-outline-success btn-lg w-100 py-4">
                                <i class="fas fa-user-tie fa-3x mb-2"></i><br>
                                Клиенты
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="admin_orders.php" class="btn btn-outline-warning btn-lg w-100 py-4">
                                <i class="fas fa-clipboard-list fa-3x mb-2"></i><br>
                                Все заказы
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="admin_services.php" class="btn btn-outline-info btn-lg w-100 py-4">
                                <i class="fas fa-tools fa-3x mb-2"></i><br>
                                Услуги
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="admin_employees.php" class="btn btn-outline-secondary btn-lg w-100 py-4">
                                <i class="fas fa-user-cog fa-3x mb-2"></i><br>
                                Сотрудники
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="admin_reports.php" class="btn btn-outline-danger btn-lg w-100 py-4">
                                <i class="fas fa-chart-bar fa-3x mb-2"></i><br>
                                Отчеты
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="admin_settings.php" class="btn btn-outline-dark btn-lg w-100 py-4">
                                <i class="fas fa-cog fa-3x mb-2"></i><br>
                                Настройки
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="admin_backup.php" class="btn btn-outline-primary btn-lg w-100 py-4">
                                <i class="fas fa-database fa-3x mb-2"></i><br>
                                Резервное копирование
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Последние заказы -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history"></i> Последние заказы
                    </h6>
                    <a href="admin_orders.php" class="btn btn-sm btn-primary">Все заказы</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>№</th>
                                    <th>Клиент</th>
                                    <th>Автомобиль</th>
                                    <th>Статус</th>
                                    <th>Дата</th>
                                    <th>Сумма</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['client_name']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($order['brand'] . ' ' . $order['model']); ?>
                                        <br>
                                        <small><?php echo htmlspecialchars($order['license_plate']); ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $status_classes = [
                                            'new' => 'primary',
                                            'in_progress' => 'warning',
                                            'completed' => 'success',
                                            'cancelled' => 'secondary'
                                        ];
                                        $status_texts = [
                                            'new' => 'Новый',
                                            'in_progress' => 'В работе',
                                            'completed' => 'Завершен',
                                            'cancelled' => 'Отменен'
                                        ];
                                        $class = $status_classes[$order['status']] ?? 'secondary';
                                        $text = $status_texts[$order['status']] ?? $order['status'];
                                        ?>
                                        <span class="badge bg-<?php echo $class; ?>"><?php echo $text; ?></span>
                                    </td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></td>
                                    <td class="text-end"><?php echo number_format($order['total_price'] ?? 0, 2, ',', ' '); ?> ₽</td>
                                    <td>
                                        <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 4px solid #4e73df !important;
}
.border-left-success {
    border-left: 4px solid #1cc88a !important;
}
.border-left-info {
    border-left: 4px solid #36b9cc !important;
}
.border-left-warning {
    border-left: 4px solid #f6c23e !important;
}
.card {
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
}
</style>

<?php require_once 'includes/footer.php'; ?>
