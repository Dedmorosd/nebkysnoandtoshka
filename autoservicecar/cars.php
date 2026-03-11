<?php
// Включаем отображение ошибок для отладки (можно отключить позже)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Подключаем необходимые файлы
require_once 'config/database.php';
require_once 'includes/auth.php';

// НЕ вызываем session_start() - он уже в auth.php

// Проверяем авторизацию
if (!isLoggedIn()) {
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
    $error = "Ошибка базы данных: " . $e->getMessage();
}

// Обработка добавления автомобиля
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_car'])) {
    if ($client) {
        $brand = trim($_POST['brand'] ?? '');
        $model = trim($_POST['model'] ?? '');
        $year = intval($_POST['year'] ?? 0);
        $license_plate = strtoupper(trim($_POST['license_plate'] ?? ''));
        $vin = strtoupper(trim($_POST['vin'] ?? ''));
        
        // Валидация
        $errors = [];
        if (empty($brand)) $errors[] = "Укажите марку";
        if (empty($model)) $errors[] = "Укажите модель";
        if ($year < 1900 || $year > date('Y') + 1) $errors[] = "Укажите корректный год";
        if (empty($license_plate)) $errors[] = "Укажите госномер";
        
        if (empty($errors)) {
            try {
                // Проверяем уникальность госномера
                $check = $pdo->prepare("SELECT id FROM cars WHERE license_plate = ?");
                $check->execute([$license_plate]);
                
                if ($check->fetch()) {
                    $error = "Автомобиль с таким госномером уже существует";
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO cars (client_id, brand, model, year, license_plate, vin) 
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    if ($stmt->execute([$client['id'], $brand, $model, $year, $license_plate, $vin])) {
                        $_SESSION['success_message'] = "Автомобиль успешно добавлен!";
                        header('Location: cars.php');
                        exit();
                    }
                }
            } catch(PDOException $e) {
                $error = "Ошибка: " . $e->getMessage();
            }
        } else {
            $error = implode("<br>", $errors);
        }
    } else {
        $error = "Сначала заполните профиль";
    }
}

// Обработка удаления
if (isset($_GET['delete'])) {
    $car_id = intval($_GET['delete']);
    if ($client) {
        try {
            // Проверяем, есть ли заказы у автомобиля
            $check = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE car_id = ?");
            $check->execute([$car_id]);
            $orders_count = $check->fetchColumn();
            
            if ($orders_count > 0) {
                $error = "Нельзя удалить автомобиль с заказами";
            } else {
                $stmt = $pdo->prepare("DELETE FROM cars WHERE id = ? AND client_id = ?");
                $stmt->execute([$car_id, $client['id']]);
                $_SESSION['success_message'] = "Автомобиль удален";
                header('Location: cars.php');
                exit();
            }
        } catch(PDOException $e) {
            $error = "Ошибка удаления: " . $e->getMessage();
        }
    }
}

// Получаем список автомобилей
$cars = [];
if ($client) {
    $stmt = $pdo->prepare("
        SELECT * FROM cars 
        WHERE client_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$client['id']]);
    $cars = $stmt->fetchAll();
}

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-car"></i> Мои автомобили</h1>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCarModal">
                <i class="fas fa-plus"></i> Добавить автомобиль
            </button>
            <a href="dashboard.php" class="btn btn-outline-secondary">
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

    <?php if (!$client): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> 
            Для добавления автомобилей заполните профиль
            <a href="profile.php" class="alert-link">Перейти в профиль</a>
        </div>
    <?php endif; ?>

    <?php if (count($cars) > 0): ?>
        <div class="row">
            <?php foreach ($cars as $car): ?>
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-1">
                                <strong>Госномер:</strong> 
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($car['license_plate']); ?></span>
                            </p>
                            <p class="mb-1"><strong>Год:</strong> <?php echo $car['year']; ?></p>
                            <?php if ($car['vin']): ?>
                                <p class="mb-1"><strong>VIN:</strong> <small><?php echo htmlspecialchars($car['vin']); ?></small></p>
                            <?php endif; ?>
                            <p class="mb-0"><strong>Добавлен:</strong> <?php echo date('d.m.Y', strtotime($car['created_at'])); ?></p>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="btn-group w-100">
                                <a href="create_order.php?car_id=<?php echo $car['id']; ?>" class="btn btn-success btn-sm">
                                    <i class="fas fa-clipboard-list"></i> Заказ
                                </a>
                                <a href="?delete=<?php echo $car['id']; ?>" class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Удалить автомобиль?')">
                                    <i class="fas fa-trash"></i> Удалить
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-car fa-4x text-muted mb-3"></i>
            <h3 class="text-muted">У вас пока нет автомобилей</h3>
            <button class="btn btn-primary btn-lg mt-3" data-bs-toggle="modal" data-bs-target="#addCarModal">
                <i class="fas fa-plus"></i> Добавить первый автомобиль
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Модальное окно добавления -->
<div class="modal fade" id="addCarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Добавить автомобиль</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Марка *</label>
                        <input type="text" name="brand" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Модель *</label>
                        <input type="text" name="model" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Год выпуска *</label>
                        <select name="year" class="form-control" required>
                            <option value="">Выберите год</option>
                            <?php for ($y = date('Y'); $y >= 1990; $y--): ?>
                                <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Госномер *</label>
                        <input type="text" name="license_plate" class="form-control" 
                               placeholder="А123ВС777" required>
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