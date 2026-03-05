 <?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';
require_once 'includes/auth.php';

checkAuth();

$pageTitle = "Услуги автосервиса";

// Получение списка услуг
$services = [];
try {
    $stmt = $pdo->query("SELECT * FROM services ORDER BY category, name");
    $services = $stmt->fetchAll();
    
    // Группировка по категориям
    $grouped_services = [];
    foreach ($services as $service) {
        $grouped_services[$service['category']][] = $service;
    }
} catch (PDOException $e) {
    $error = "Ошибка загрузки услуг: " . $e->getMessage();
}

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-tools"></i> Наши услуги</h1>
        <a href="dashboard.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Назад
        </a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (empty($services)): ?>
        <div class="text-center py-5">
            <i class="fas fa-tools fa-4x text-muted mb-3"></i>
            <h3 class="text-muted">Список услуг пуст</h3>
        </div>
    <?php else: ?>
        <?php foreach ($grouped_services as $category => $category_services): ?>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><?php echo htmlspecialchars($category); ?></h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($category_services as $service): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($service['name']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars($service['description']); ?></p>
                                        <p class="mb-1"><strong>Цена:</strong> <?php echo number_format($service['price'], 2); ?> ₽</p>
                                        <p><strong>Время:</strong> <?php echo $service['duration']; ?> мин.</p>
                                        <a href="create_order.php?service_id=<?php echo $service['id']; ?>" 
                                           class="btn btn-primary btn-sm">
                                            Заказать
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
