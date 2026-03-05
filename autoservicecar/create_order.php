 <?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';
require_once 'includes/auth.php';

checkAuth();

$pageTitle = "Создание заказа";
$user_id = $_SESSION['user_id'];

// Получаем client_id
$stmt = $pdo->prepare("SELECT id FROM clients WHERE user_id = ?");
$stmt->execute([$user_id]);
$client = $stmt->fetch();

if (!$client) {
    $_SESSION['error_message'] = "Сначала заполните профиль";
    header('Location: profile.php');
    exit();
}

// Получаем автомобили пользователя
$cars = $pdo->prepare("SELECT * FROM cars WHERE client_id = ?");
$cars->execute([$client['id']]);
$cars = $cars->fetchAll();

// Получаем услуги
$services = $pdo->query("SELECT * FROM services ORDER BY name")->fetchAll();

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <h1>Создание заказа</h1>
    
    <form method="POST" action="save_order.php">
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Выберите автомобиль</h5>
                    </div>
                    <div class="card-body">
                        <select name="car_id" class="form-control" required>
                            <option value="">-- Выберите автомобиль --</option>
                            <?php foreach ($cars as $car): ?>
                                <option value="<?php echo $car['id']; ?>">
                                    <?php echo htmlspecialchars($car['brand'] . ' ' . $car['model'] . ' (' . $car['license_plate'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($cars)): ?>
                            <p class="text-danger mt-2">Нет автомобилей. <a href="cars.php">Добавить</a></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Дата и время</h5>
                    </div>
                    <div class="card-body">
                        <label>Предпочтительная дата</label>
                        <input type="date" name="preferred_date" class="form-control" 
                               min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Выберите услуги</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($services as $service): ?>
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input type="checkbox" name="services[]" 
                                       value="<?php echo $service['id']; ?>" 
                                       class="form-check-input service-checkbox"
                                       data-price="<?php echo $service['price']; ?>">
                                <label class="form-check-label">
                                    <?php echo htmlspecialchars($service['name']); ?> - 
                                    <?php echo number_format($service['price'], 2); ?> ₽
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-3 text-end">
                    <h4>Итого: <span id="totalPrice">0.00</span> ₽</h4>
                </div>
            </div>
        </div>
        
        <div class="card mb-3">
            <div class="card-header bg-warning">
                <h5 class="mb-0">Описание проблемы</h5>
            </div>
            <div class="card-body">
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
        </div>
        
        <button type="submit" class="btn btn-success btn-lg">
            <i class="fas fa-save"></i> Сохранить заказ
        </button>
        <a href="orders.php" class="btn btn-secondary btn-lg">Отмена</a>
    </form>
</div>

<script>
document.querySelectorAll('.service-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateTotal);
});

function updateTotal() {
    let total = 0;
    document.querySelectorAll('.service-checkbox:checked').forEach(cb => {
        total += parseFloat(cb.dataset.price);
    });
    document.getElementById('totalPrice').textContent = total.toFixed(2);
}
</script>

<?php require_once 'includes/footer.php'; ?>
