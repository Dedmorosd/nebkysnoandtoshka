<?php
// НЕ вызываем session_start() здесь - он уже есть в database.php

require_once 'config/database.php';
require_once 'includes/auth.php';

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$pageTitle = "Мои автомобили";
$user_id = $_SESSION['user_id'];

// Получаем client_id пользователя
$client = null;
try {
    $stmt = $pdo->prepare("SELECT id FROM clients WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $client = $stmt->fetch();
} catch (Exception $e) {
    // Обработка ошибки
}

// Обработка добавления автомобиля
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_car'])) {
    if ($client) {
        $brand = trim($_POST['brand'] ?? '');
        $model = trim($_POST['model'] ?? '');
        $year = intval($_POST['year'] ?? 0);
        $license_plate = strtoupper(trim($_POST['license_plate'] ?? ''));
        $vin = strtoupper(trim($_POST['vin'] ?? ''));
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO cars (client_id, brand, model, year, license_plate, vin) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            if ($stmt->execute([$client['id'], $brand, $model, $year, $license_plate, $vin])) {
                $_SESSION['success_message'] = "Автомобиль успешно добавлен!";
                header('Location: cars.php');
                exit();
            }
        } catch(PDOException $e) {
            $error = "Ошибка: " . $e->getMessage();
        }
    }
}

// Получаем список автомобилей
$cars = [];
if ($client) {
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE client_id = ? ORDER BY created_at DESC");
    $stmt->execute([$client['id']]);
    $cars = $stmt->fetchAll();
}

require_once 'includes/header.php';
?>

<div class="container">
    <h1 class="mb-4">Мои автомобили</h1>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success_message']; ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <!-- Кнопка добавления -->
    <button type="button" class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addCarModal">
        + Добавить автомобиль
    </button>
    
    <!-- Список автомобилей -->
    <div class="row">
        <?php if (count($cars) > 0): ?>
            <?php foreach ($cars as $car): ?>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?>
                            </h5>
                            <p class="card-text">
                                <strong>Госномер:</strong> <?php echo htmlspecialchars($car['license_plate']); ?><br>
                                <strong>Год:</strong> <?php echo $car['year']; ?><br>
                                <?php if ($car['vin']): ?>
                                    <strong>VIN:</strong> <?php echo htmlspecialchars($car['vin']); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <p class="text-center text-muted">У вас пока нет автомобилей</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Модальное окно добавления -->
<div class="modal fade" id="addCarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Добавить автомобиль</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Марка</label>
                        <input type="text" name="brand" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Модель</label>
                        <input type="text" name="model" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Год</label>
                        <input type="number" name="year" class="form-control" min="1900" max="2026" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Госномер</label>
                        <input type="text" name="license_plate" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">VIN номер</label>
                        <input type="text" name="vin" class="form-control" maxlength="17">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" name="add_car" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>