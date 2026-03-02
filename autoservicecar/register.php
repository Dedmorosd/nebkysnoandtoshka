<?php
require_once 'config/database.php';
$pageTitle = "Регистрация";
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    // Валидация
    if (empty($username) || empty($password) || empty($email) || empty($full_name) || empty($phone)) {
        $error = 'Все поля обязательны для заполнения';
    } elseif ($password !== $confirm_password) {
        $error = 'Пароли не совпадают';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль должен содержать не менее 6 символов';
    } else {
        try {
            // Проверка существования пользователя
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetch()) {
                $error = 'Пользователь с таким именем или email уже существует';
            } else {
                // Хешируем пароль
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Начинаем транзакцию
                $pdo->beginTransaction();
                
                // 1. Создаем пользователя в таблице users
                $stmt = $pdo->prepare("
                    INSERT INTO users (username, password, email, full_name, phone, role) 
                    VALUES (?, ?, ?, ?, ?, 'client')
                ");
                $stmt->execute([$username, $hashed_password, $email, $full_name, $phone]);
                $user_id = $pdo->lastInsertId();
                
                // 2. Создаем запись в таблице clients
                $stmt = $pdo->prepare("
                    INSERT INTO clients (user_id, full_name, phone, email) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$user_id, $full_name, $phone, $email]);
                
                // Подтверждаем транзакцию
                $pdo->commit();
                
                $success = 'Регистрация успешна! Теперь вы можете войти в систему.';
                
                // Очищаем POST данные
                $_POST = [];
            }
        } catch(PDOException $e) {
            // Откатываем транзакцию в случае ошибки
            $pdo->rollBack();
            $error = 'Ошибка при регистрации: ' . $e->getMessage();
            
            // Логируем ошибку для отладки
            error_log("Registration error: " . $e->getMessage());
        }
    }
}

require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-user-plus"></i> Регистрация нового пользователя</h4>
            </div>
            <div class="card-body">
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Имя пользователя *</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                   required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                   required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Пароль *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <small class="text-muted">Минимум 6 символов</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Подтвердите пароль *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">ФИО *</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" 
                                   required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Телефон *</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" 
                                   placeholder="+7 (999) 123-45-67" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Зарегистрироваться
                    </button>
                    <a href="login.php" class="btn btn-link">Уже есть аккаунт? Войти</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>