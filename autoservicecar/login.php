<?php
// Включаем отображение ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Подключаем базу данных
require_once 'config/database.php';

// Запускаем сессию
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Если уже авторизован, перенаправляем на dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Введите логин и пароль';
    } else {
        try {
            // Ищем пользователя по username или email
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Проверяем пароль
                if (password_verify($password, $user['password'])) {
                    // Успешная авторизация
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['full_name'] = $user['full_name'];
                    
                    header('Location: dashboard.php');
                    exit();
                } else {
                    $error = 'Неверный пароль';
                }
            } else {
                $error = 'Пользователь не найден';
            }
        } catch (PDOException $e) {
            $error = 'Ошибка базы данных: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в систему - Автосервис</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            max-width: 400px;
            width: 90%;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            color: #333;
            font-size: 28px;
            font-weight: 600;
            margin: 10px 0 5px;
        }
        .login-header p {
            color: #666;
            margin: 0;
        }
        .form-control {
            height: 50px;
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 10px 15px;
            font-size: 16px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            height: 50px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102,126,234,0.4);
        }
        .btn-register {
            background: white;
            border: 2px solid #667eea;
            color: #667eea;
            height: 50px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-register:hover {
            background: #667eea;
            color: white;
            text-decoration: none;
        }
        .alert {
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 20px;
        }
        .progress-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            animation: progress 2s ease-in-out infinite;
        }
        @keyframes progress {
            0% { width: 0%; }
            50% { width: 100%; }
            100% { width: 0%; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="logo.png" alt="Logo" height="60" onerror="this.style.display='none'">
                <h1>Автосервис</h1>
                <p>Вход в систему</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> 
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder="Имя пользователя или Email" 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                           required autofocus>
                </div>
                
                <div class="mb-4">
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Пароль" required>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Войти
                    </button>
                    <a href="register.php" class="btn-register">
                        <i class="fas fa-user-plus"></i> Регистрация
                    </a>
                </div>
            </form>
            
            <!-- Тестовые данные для отладки (можно удалить после исправления) -->
            <div class="mt-4 p-3 bg-light rounded" style="font-size: 14px;">
                <p class="mb-1"><strong>Тестовые данные:</strong></p>
                <p class="mb-1">Логин: admin</p>
                <p class="mb-1">Пароль: admin123</p>
                <p class="mb-0 text-muted">или создайте нового пользователя через регистрацию</p>
            </div>
        </div>
    </div>
    
    <div class="progress-bar"></div>
    
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
    <!-- Fallback для Font Awesome -->
    <script>
        (function() {
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css';
            document.head.appendChild(link);
        })();
    </script>
</body>
</html>