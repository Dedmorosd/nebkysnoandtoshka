 <!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Автосервис - <?php echo $pageTitle ?? 'Главная'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-car"></i> Автосервис
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Панель управления</a>
                        </li>
                        <?php if(isAdmin() || isMechanic()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="clients.php"><i class="fas fa-users"></i> Клиенты</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="cars.php"><i class="fas fa-car"></i> Автомобили</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="orders.php"><i class="fas fa-clipboard-list"></i> Заказы</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="services.php"><i class="fas fa-tools"></i> Услуги</a>
                            </li>
                        <?php endif; ?>
                        <?php if(isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="employees.php"><i class="fas fa-user-tie"></i> Сотрудники</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <span class="nav-link text-white">
                                <i class="fas fa-user"></i> <?php echo $_SESSION['username']; ?>
                                (<?php echo $_SESSION['role']; ?>)
                            </span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Выход</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt"></i> Вход</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php"><i class="fas fa-user-plus"></i> Регистрация</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4"></div>
