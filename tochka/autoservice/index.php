 <?php
require_once 'config/database.php';
$pageTitle = "Главная";

require_once 'includes/header.php';
?>

<div class="jumbotron bg-light p-5 rounded">
    <h1 class="display-4">
        <i class="fas fa-car"></i> Добро пожаловать в Автосервис
    </h1>
    <p class="lead">
        Профессиональное обслуживание и ремонт автомобилей любой сложности.
        Мы заботимся о вашем автомобиле как о своем собственном.
    </p>
    <hr class="my-4">
    
    <?php if(!isset($_SESSION['user_id'])): ?>
        <p>Для доступа к полному функционалу системы, пожалуйста, войдите или зарегистрируйтесь.</p>
        <div class="mt-4">
            <a class="btn btn-primary btn-lg" href="login.php" role="button">
                <i class="fas fa-sign-in-alt"></i> Войти
            </a>
            <a class="btn btn-success btn-lg" href="register.php" role="button">
                <i class="fas fa-user-plus"></i> Регистрация
            </a>
        </div>
    <?php else: ?>
        <p>Вы вошли как <strong><?php echo $_SESSION['username']; ?></strong> 
        (<?php echo $_SESSION['role']; ?>). Перейдите в панель управления для работы с системой.</p>
        <div class="mt-4">
            <a class="btn btn-primary btn-lg" href="dashboard.php" role="button">
                <i class="fas fa-tachometer-alt"></i> Панель управления
            </a>
        </div>
    <?php endif; ?>
</div>

<div class="row mt-5">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-tools fa-3x text-primary mb-3"></i>
                <h4>Качественный ремонт</h4>
                <p>Используем только оригинальные запчасти и современное оборудование</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-user-tie fa-3x text-success mb-3"></i>
                <h4>Опытные мастера</h4>
                <p>Работают специалисты с многолетним опытом</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-clock fa-3x text-info mb-3"></i>
                <h4>Быстрое обслуживание</h4>
                <p>Минимальные сроки ремонта при сохранении качества</p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
