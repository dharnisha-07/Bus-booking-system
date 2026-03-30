<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die("Access Denied. Admins only.");
}

// Fetch Stats
$totalBookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$revenue = $pdo->query("SELECT SUM(amount) FROM payments WHERE status='success'")->fetchColumn() ?: 0;
$activeBuses = $pdo->query("SELECT COUNT(*) FROM buses")->fetchColumn();
$totalRoutes = $pdo->query("SELECT COUNT(*) FROM routes")->fetchColumn();

// Fetch Revenue Data for Chart (last 7 days grouped by date)
$chartData = $pdo->query("
    SELECT DATE(paid_at) as pay_date, SUM(amount) as daily_revenue 
    FROM payments 
    WHERE status='success' AND paid_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(paid_at)
    ORDER BY pay_date ASC
")->fetchAll();

$dates = [];
$revenues = [];
foreach ($chartData as $row) {
    $dates[] = date('M d', strtotime($row['pay_date']));
    $revenues[] = (float)$row['daily_revenue'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Journey</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { display: flex; background: #f4f6f9; color: #333; min-height: 100vh; }
        
        .sidebar {
            width: 250px;
            background: #111827; /* Dark sidebar */
            color: white;
            padding: 20px 0;
            display: flex;
            flex-direction: column;
        }
        .sidebar-header {
            padding: 0 20px 20px;
            font-size: 24px;
            font-weight: bold;
            border-bottom: 1px solid #1f2937;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #ff9100;
        }
        .nav-links { list-style: none; }
        .nav-links li a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 20px;
            color: #9ca3af;
            text-decoration: none;
            transition: 0.3s;
        }
        .nav-links li a:hover, .nav-links li a.active {
            background: #1f2937;
            color: white;
            border-left: 4px solid #ff9100;
        }
        
        .main-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .header h1 { font-size: 24px; color: #111827; }
        .btn-export {
            background: #10b981;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .stat-card:nth-child(1) .stat-icon { background: #e0e7ff; color: #4f46e5; }
        .stat-card:nth-child(2) .stat-icon { background: #dcfce7; color: #16a34a; }
        .stat-card:nth-child(3) .stat-icon { background: #fef3c7; color: #d97706; }
        .stat-card:nth-child(4) .stat-icon { background: #fee2e2; color: #dc2626; }
        
        .stat-info h3 { font-size: 24px; color: #111827; }
        .stat-info p { font-size: 14px; color: #6b7280; }
        
        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <i class="fa-solid fa-bus"></i> Admin
    </div>
    <ul class="nav-links">
        <li><a href="index.php" class="active"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
        <li><a href="buses.php"><i class="fa-solid fa-van-shuttle"></i> Buses</a></li>
        <li><a href="routes.php"><i class="fa-solid fa-route"></i> Routes</a></li>
        <li><a href="schedules.php"><i class="fa-solid fa-calendar-days"></i> Schedules</a></li>
        <li><a href="bookings.php"><i class="fa-solid fa-ticket"></i> Bookings</a></li>
        <li><a href="../auth.php?action=logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="header">
        <h1>Dashboard Overview</h1>
        <a href="export.php" class="btn-export"><i class="fa-solid fa-download"></i> Export Bookings CSV</a>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fa-solid fa-ticket"></i></div>
            <div class="stat-info">
                <h3><?= number_format($totalBookings) ?></h3>
                <p>Total Bookings</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fa-solid fa-dollar-sign"></i></div>
            <div class="stat-info">
                <h3>₹<?= number_format($revenue, 2) ?></h3>
                <p>Total Revenue</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fa-solid fa-bus"></i></div>
            <div class="stat-info">
                <h3><?= number_format($activeBuses) ?></h3>
                <p>Active Buses</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fa-solid fa-map-location-dot"></i></div>
            <div class="stat-info">
                <h3><?= number_format($totalRoutes) ?></h3>
                <p>Total Routes</p>
            </div>
        </div>
    </div>
    
    <div class="chart-container">
        <h2 style="margin-bottom: 20px; font-size: 18px;">Revenue Over Last 7 Days</h2>
        <canvas id="revenueChart" height="100"></canvas>
    </div>
</div>

<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($dates) ?>,
        datasets: [{
            label: 'Daily Revenue (₹)',
            data: <?= json_encode($revenues) ?>,
            borderColor: '#ff9100',
            backgroundColor: 'rgba(255, 145, 0, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

</body>
</html>
