 <?php
require_once 'config/database.php';
require_once 'includes/auth.php';

if (!isAdmin()) {
    header('Location: dashboard.php');
    exit();
}

$pageTitle = "Отчеты";

// Получаем параметры отчета
$period = $_GET['period'] ?? 'month';
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Статистика по заказам за период
$stmt = $pdo->prepare("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
        SUM(total_price) as revenue
    FROM orders
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY date DESC
");
$stmt->execute([$start_date, $end_date]);
$daily_stats = $stmt->fetchAll();

// Общая статистика
$total_stats = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
        SUM(total_price) as revenue,
        AVG(total_price) as avg_order
    FROM orders
    WHERE DATE(created_at) BETWEEN ? AND ?
");
$total_stats->execute([$start_date, $end_date]);
$stats = $total_stats->fetch();

// Популярные услуги
$popular_services = $pdo->prepare("
    SELECT s.name, COUNT(*) as count, SUM(os.price_at_time) as total
    FROM order_services os
    JOIN services s ON os.service_id = s.id
    JOIN orders o ON os.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY s.id
    ORDER BY count DESC
    LIMIT 10
");
$popular_services->execute([$start_date, $end_date]);
$popular = $popular_services->fetchAll();

// Активность клиентов
$top_clients = $pdo->prepare("
    SELECT u.full_name, COUNT(*) as orders_count, SUM(o.total_price) as total_spent
    FROM orders o
    JOIN clients cl ON o.client_id = cl.id
    JOIN users u ON cl.user_id = u.id
    WHERE DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY cl.id
    ORDER BY total_spent DESC
    LIMIT 10
");
$top_clients->execute([$start_date, $end_date]);
$clients = $top_clients->fetchAll();

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-chart-bar"></i> Отчеты и аналитика</h1>
        <a href="admin.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Назад
        </a>
    </div>

    <!-- Фильтр периода -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-3">
                    <label class="form-label">Период</label>
                    <select name="period" class="form-control">
                        <option value="week" <?php echo $period == 'week' ? 'selected' : ''; ?>>Неделя</option>
                        <option value="month" <?php echo $period == 'month' ? 'selected' : ''; ?>>Месяц</option>
                        <option value="quarter" <?php echo $period == 'quarter' ? 'selected' : ''; ?>>Квартал</option>
                        <option value="year" <?php echo $period == 'year' ? 'selected' : ''; ?>>Год</option>
                        <option value="custom" <?php echo $period == 'custom' ? 'selected' : ''; ?>>Произвольный</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Начало</label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Конец</label>
                    <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary form-control">Применить</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Ключевые показатели -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">Всего заказов</div>
                            <div class="h3"><?php echo $stats['total'] ?? 0; ?></div>
                        </div>
                        <i class="fas fa-clipboard-list fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">Выполнено</div>
                            <div class="h3"><?php echo $stats['completed'] ?? 0; ?></div>
                        </div>
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">Отменено</div>
                            <div class="h3"><?php echo $stats['cancelled'] ?? 0; ?></div>
                        </div>
                        <i class="fas fa-times-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">Выручка</div>
                            <div class="h3"><?php echo number_format($stats['revenue'] ?? 0, 0, ',', ' '); ?> ₽</div>
                        </div>
                        <i class="fas fa-ruble-sign fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- График заказов -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>Динамика заказов</h5>
                </div>
                <div class="card-body">
                    <canvas id="ordersChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Распределение по статусам -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>Статусы заказов</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Популярные услуги -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>Популярные услуги</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Услуга</th>
                                <th>Количество</th>
                                <th>Выручка</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($popular as $service): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($service['name']); ?></td>
                                <td><?php echo $service['count']; ?></td>
                                <td><?php echo number_format($service['total'], 2, ',', ' '); ?> ₽</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Топ клиентов -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>Топ клиентов</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Клиент</th>
                                <th>Заказов</th>
                                <th>Сумма</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clients as $client): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($client['full_name']); ?></td>
                                <td><?php echo $client['orders_count']; ?></td>
                                <td><?php echo number_format($client['total_spent'], 2, ',', ' '); ?> ₽</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// График заказов
const ordersData = <?php echo json_encode($daily_stats); ?>;
const dates = ordersData.map(d => d.date);
const completed = ordersData.map(d => d.completed);
const cancelled = ordersData.map(d => d.cancelled);

new Chart(document.getElementById('ordersChart'), {
    type: 'line',
    data: {
        labels: dates,
        datasets: [{
            label: 'Выполнено',
            data: completed,
            borderColor: 'green',
            tension: 0.1
        }, {
            label: 'Отменено',
            data: cancelled,
            borderColor: 'red',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Круговая диаграмма статусов
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: ['Выполнено', 'Отменено', 'В работе', 'Новые'],
        datasets: [{
            data: [
                <?php echo $stats['completed'] ?? 0; ?>,
                <?php echo $stats['cancelled'] ?? 0; ?>,
                <?php echo ($stats['total'] - $stats['completed'] - $stats['cancelled']) ?? 0; ?>,
                0
            ],
            backgroundColor: ['#28a745', '#dc3545', '#ffc107', '#17a2b8']
        }]
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
