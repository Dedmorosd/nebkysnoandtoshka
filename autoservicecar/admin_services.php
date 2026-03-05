 <?php
require_once 'config/database.php';
require_once 'includes/auth.php';

if (!isAdmin()) {
    header('Location: dashboard.php');
    exit();
}

$pageTitle = "Управление услугами";
$message = '';
$error = '';

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_service'])) {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = floatval($_POST['price']);
        $duration = intval($_POST['duration']);
        $category = trim($_POST['category']);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO services (name, description, price, duration, category) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $duration, $category]);
            $message = "Услуга успешно добавлена";
        } catch(PDOException $e) {
            $error = "Ошибка: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['delete_service'])) {
        $service_id = $_POST['service_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
            $stmt->execute([$service_id]);
            $message = "Услуга удалена";
        } catch(PDOException $e) {
            $error = "Ошибка удаления: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['edit_service'])) {
        $service_id = $_POST['service_id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = floatval($_POST['price']);
        $duration = intval($_POST['duration']);
        $category = trim($_POST['category']);
        
        try {
            $stmt = $pdo->prepare("UPDATE services SET name=?, description=?, price=?, duration=?, category=? WHERE id=?");
            $stmt->execute([$name, $description, $price, $duration, $category, $service_id]);
            $message = "Услуга обновлена";
        } catch(PDOException $e) {
            $error = "Ошибка: " . $e->getMessage();
        }
    }
}

// Получение списка услуг
$services = $pdo->query("SELECT * FROM services ORDER BY category, name")->fetchAll();

// Группировка по категориям
$categories = $pdo->query("SELECT DISTINCT category FROM services ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-tools"></i> Управление услугами</h1>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                <i class="fas fa-plus"></i> Добавить услугу
            </button>
            <a href="admin.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Назад
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="row">
        <?php foreach ($categories as $category): ?>
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><?php echo htmlspecialchars($category); ?></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Название</th>
                                    <th>Описание</th>
                                    <th>Цена</th>
                                    <th>Длительность</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($services as $service): ?>
                                <?php if ($service['category'] == $category): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($service['name']); ?></td>
                                    <td><?php echo htmlspecialchars($service['description']); ?></td>
                                    <td><?php echo number_format($service['price'], 2, ',', ' '); ?> ₽</td>
                                    <td><?php echo $service['duration']; ?> мин.</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editService(<?php echo $service['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Удалить услугу?')">
                                            <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                            <input type="hidden" name="delete_service" value="1">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Модальное окно добавления услуги -->
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Добавить услугу</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Название *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Описание</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Цена *</label>
                        <input type="number" name="price" class="form-control" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Длительность (минуты) *</label>
                        <input type="number" name="duration" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Категория *</label>
                        <input type="text" name="category" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" name="add_service" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editService(id) {
    // Заглушка для редактирования
    alert('Редактирование услуги #' + id);
}
</script>

<?php require_once 'includes/footer.php'; ?>
