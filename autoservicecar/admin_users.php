 <?php
require_once 'config/database.php';
require_once 'includes/auth.php';

if (!isAdmin()) {
    header('Location: dashboard.php');
    exit();
}

$pageTitle = "Управление пользователями";
$message = '';
$error = '';

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_user'])) {
        // Добавление пользователя
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $full_name = trim($_POST['full_name']);
        $phone = trim($_POST['phone']);
        $role = $_POST['role'];
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, phone, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $password, $email, $full_name, $phone, $role]);
            $message = "Пользователь успешно добавлен";
        } catch(PDOException $e) {
            $error = "Ошибка: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
            $stmt->execute([$user_id]);
            $message = "Пользователь удален";
        } catch(PDOException $e) {
            $error = "Ошибка удаления: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['update_role'])) {
        $user_id = $_POST['user_id'];
        $role = $_POST['role'];
        try {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$role, $user_id]);
            $message = "Роль пользователя обновлена";
        } catch(PDOException $e) {
            $error = "Ошибка: " . $e->getMessage();
        }
    }
}

// Получение списка пользователей
$users = $pdo->query("
    SELECT u.*, 
           (SELECT COUNT(*) FROM clients WHERE user_id = u.id) as has_client,
           (SELECT COUNT(*) FROM employees WHERE user_id = u.id) as has_employee
    FROM users u 
    ORDER BY u.created_at DESC
")->fetchAll();

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-users"></i> Управление пользователями</h1>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-plus"></i> Добавить пользователя
            </button>
            <a href="admin.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Назад
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="usersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Логин</th>
                            <th>ФИО</th>
                            <th>Email</th>
                            <th>Телефон</th>
                            <th>Роль</th>
                            <th>Статус</th>
                            <th>Дата регистрации</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td>#<?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <select name="role" class="form-select form-select-sm" onchange="this.form.submit()" <?php echo $user['role'] === 'admin' ? 'disabled' : ''; ?>>
                                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Админ</option>
                                        <option value="mechanic" <?php echo $user['role'] === 'mechanic' ? 'selected' : ''; ?>>Механик</option>
                                        <option value="client" <?php echo $user['role'] === 'client' ? 'selected' : ''; ?>>Клиент</option>
                                    </select>
                                    <input type="hidden" name="update_role" value="1">
                                </form>
                            </td>
                            <td>
                                <?php if ($user['has_client']): ?>
                                    <span class="badge bg-success">Клиент</span>
                                <?php endif; ?>
                                <?php if ($user['has_employee']): ?>
                                    <span class="badge bg-info">Сотрудник</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <?php if ($user['role'] !== 'admin'): ?>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Удалить пользователя?')">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="delete_user" value="1">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <a href="user_profile.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info">
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

<!-- Модальное окно добавления пользователя -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Добавить пользователя</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Логин *</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Пароль *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ФИО *</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Телефон</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Роль</label>
                        <select name="role" class="form-control">
                            <option value="client">Клиент</option>
                            <option value="mechanic">Механик</option>
                            <option value="admin">Администратор</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" name="add_user" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#usersTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Russian.json'
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
