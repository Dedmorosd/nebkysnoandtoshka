<?php
// Включаем отображение ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Подключаем базу данных
require_once 'config/database.php';

// Простая проверка авторизации (без внешних функций)
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Определяем простые функции для проверки ролей
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isMechanic() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'mechanic';
}

function isClient() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'client';
}

$pageTitle = "Панель управления";
$user_id = $_SESSION['user_id'];

// Статистика для админа/механика
if (isAdmin() || isMechanic()) {
    // Ваш существующий код для админа...
    $orders_count = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $clients_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'client'")->fetchColumn();
    $pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'new'")->fetchColumn();
    $total_revenue = $pdo->query("SELECT SUM(total_price) FROM orders WHERE status = 'completed'")->fetchColumn() ?? 0;
} elseif (isClient()) {
    // Ваш существующий код для клиента...
    $stmt = $pdo->prepare("SELECT id FROM clients WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $client = $stmt->fetch();
    
    if ($client) {
        $client_id = $client['id'];
        $my_orders = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE client_id = ?");
        $my_orders->execute([$client_id]);
        $my_orders_count = $my_orders->fetchColumn();
        
        $my_cars = $pdo->prepare("SELECT COUNT(*) FROM cars WHERE client_id = ?");
        $my_cars->execute([$client_id]);
        $my_cars_count = $my_cars->fetchColumn();
    } else {
        $my_orders_count = 0;
        $my_cars_count = 0;
    }
}

// Подключаем шапку сайта
require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">
                <i class="fas fa-tachometer-alt"></i> Панель управления
            </h1>
            
            <div class="row">
                <?php if(isAdmin() || isMechanic()): ?>
                    <!-- Карточки для админа/механика -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6>Все заказы</h6>
                                        <h3><?php echo $orders_count ?? 0; ?></h3>
                                    </div>
                                    <i class="fas fa-clipboard-list fa-3x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6>Клиенты</h6>
                                        <h3><?php echo $clients_count ?? 0; ?></h3>
                                    </div>
                                    <i class="fas fa-users fa-3x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6>Ожидают</h6>
                                        <h3><?php echo $pending_orders ?? 0; ?></h3>
                                    </div>
                                    <i class="fas fa-clock fa-3x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6>Выручка</h6>
                                        <h3><?php echo number_format($total_revenue ?? 0, 0, ',', ' '); ?> ₽</h3>
                                    </div>
                                    <i class="fas fa-ruble-sign fa-3x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                <?php elseif(isClient()): ?>
                    <!-- Карточки для клиента -->
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6>Мои заказы</h6>
                                        <h3><?php echo $my_orders_count ?? 0; ?></h3>
                                    </div>
                                    <i class="fas fa-clipboard-list fa-3x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6>Мои автомобили</h6>
                                        <h3><?php echo $my_cars_count ?? 0; ?></h3>
                                    </div>
                                    <i class="fas fa-car fa-3x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Быстрые действия -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Быстрые действия</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <a href="cars.php" class="btn btn-primary btn-lg w-100">
                                            <i class="fas fa-plus"></i> Добавить автомобиль
                                        </a>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <a href="create_order.php" class="btn btn-success btn-lg w-100">
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
</div>

<?php require_once 'includes/footer.php'; ?>