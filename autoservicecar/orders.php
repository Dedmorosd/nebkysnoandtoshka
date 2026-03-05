<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';
require_once 'includes/auth.php';

checkAuth();

$pageTitle = "Мои заказы";
$user_id = $_SESSION['user_id'];

// Получаем client_id
$client = null;
try {
    $stmt = $pdo->prepare("SELECT id FROM clients WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $client = $stmt->fetch();
} catch (PDOException $e) {
    $error = "Ошибка базы данных: " . $e->getMessage();
}

// Получение заказов
$orders = [];
if ($client) {
    try {
        $stmt = $pdo->prepare("
            SELECT o.*, c.brand, c.model, c.license_plate,
                   (SELECT COUNT(*) FROM order_services WHERE order_id = o.id) as services_count
            FROM orders o
            JOIN cars c ON o.car_id = c.id
            WHERE o.client_id = ?
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$client['id']]);
        $orders = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = "Ошибка загрузки заказов: " . $e->getMessage();
    }
}

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-clipboard-list"></i> Мои заказы</h1>
        <div>
            <a href="create_order.php" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Новый заказ
            </a>
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Назад
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <div class="text-center py-5">
            <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
            <h3 class="text-muted">У вас пока нет заказов</h3>
            <p class="mb-3">Создайте первый заказ</p>
            <a href="create_order.php" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Создать заказ
            </a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>№</th>
                        <th>Автомобиль</th>
                        <th>Услуг</th>
                        <th>Статус</th>
                        <th>Дата</th>
                        <th>Сумма</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td>
                            <?php echo htmlspecialchars($order['brand'] . ' ' . $order['model']); ?>
                            <br>
                            <small class="text-muted"><?php echo htmlspecialchars($order['license_plate']); ?></small>
                        </td>
                        <td><?php echo $order['services_count']; ?></td>
                        <td>
                            <?php
                            $status_class = [
                                'new' => 'bg-primary',
                                'in_progress' => 'bg-warning',
                                'completed' => 'bg-success',
                                'cancelled' => 'bg-secondary'
                            ][$order['status']] ?? 'bg-secondary';
                            ?>
                            <span class="badge <?php echo $status_class; ?>">
                                <?php echo $order['status']; ?>
                            </span>
                        </td>
                        <td><?php echo date('d.m.Y', strtotime($order['created_at'])); ?></td>
                        <td><?php echo number_format($order['total_price'] ?? 0, 2); ?> ₽</td>
                        <td>
                            <a href="order_details.php?id=<?php echo $order['id']; ?>" 
                               class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>