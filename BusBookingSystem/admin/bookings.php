<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die("Access Denied.");
}

$bookings = $pdo->query("
    SELECT b.id, b.booking_ref, b.booked_at, b.total_amount, b.payment_status, b.seat_numbers,
           u.name as passenger_name, u.email, 
           r.source_city, r.destination_city, 
           sch.travel_date, sch.departure_time
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN schedules sch ON b.schedule_id = sch.id
    JOIN routes r ON sch.route_id = r.id
    ORDER BY b.booked_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Bookings - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { display: flex; background: #f4f6f9; color: #333; min-height: 100vh; }
        .sidebar { width: 250px; background: #111827; color: white; padding: 20px 0; display: flex; flex-direction: column; }
        .sidebar-header { padding: 0 20px 20px; font-size: 24px; font-weight: bold; border-bottom: 1px solid #1f2937; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; color: #ff9100; }
        .nav-links { list-style: none; }
        .nav-links li a { display: flex; align-items: center; gap: 15px; padding: 15px 20px; color: #9ca3af; text-decoration: none; transition: 0.3s; }
        .nav-links li a:hover, .nav-links li a.active { background: #1f2937; color: white; border-left: 4px solid #ff9100; }
        .main-content { flex: 1; padding: 30px; overflow-y: auto; }
        .header { display:flex; justify-content:space-between; align-items:center; margin-bottom: 30px; }
        .header h1 { font-size: 24px; color: #111827; }
        .btn-export { background: #10b981; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #f3f4f6; }
        th { background: #f9fafb; font-weight: 600; color: #6b7280; text-transform: uppercase; font-size: 12px; }
        tr:hover { background: #f9fafb; }
        .status { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .status.completed { background: #dcfce7; color: #16a34a; }
        .status.pending { background: #fef3c7; color: #d97706; }
        .status.failed { background: #fee2e2; color: #dc2626; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header"><i class="fa-solid fa-bus"></i> Admin</div>
    <ul class="nav-links">
        <li><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
        <li><a href="buses.php"><i class="fa-solid fa-van-shuttle"></i> Buses</a></li>
        <li><a href="routes.php"><i class="fa-solid fa-route"></i> Routes</a></li>
        <li><a href="schedules.php"><i class="fa-solid fa-calendar-days"></i> Schedules</a></li>
        <li><a href="bookings.php" class="active"><i class="fa-solid fa-ticket"></i> Bookings</a></li>
        <li><a href="../auth.php?action=logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="header">
        <h1>All Bookings</h1>
        <a href="export.php" class="btn-export"><i class="fa-solid fa-download"></i> Export CSV</a>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Reference</th><th>Passenger</th><th>Route</th><th>Travel Date</th><th>Seats</th><th>Amount</th><th>Status</th><th>Booked At</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($bookings as $b): ?>
            <tr>
                <td><strong><?= htmlspecialchars($b['booking_ref']) ?></strong></td>
                <td><?= htmlspecialchars($b['passenger_name']) ?><br><span style="font-size:11px;color:#666;"><?= htmlspecialchars($b['email']) ?></span></td>
                <td><?= htmlspecialchars($b['source_city']) ?> → <?= htmlspecialchars($b['destination_city']) ?></td>
                <td><?= htmlspecialchars($b['travel_date']) ?> <br><span style="font-size:11px;color:#666;"><?= date('h:i A', strtotime($b['departure_time'])) ?></span></td>
                <td><?php 
                    $seats = json_decode($b['seat_numbers'], true); 
                    echo is_array($seats) ? implode(', ', $seats) : '';
                ?></td>
                <td><strong>₹<?= htmlspecialchars($b['total_amount']) ?></strong></td>
                <td><span class="status <?= $b['payment_status'] ?>"><?= $b['payment_status'] ?></span></td>
                <td><?= date('M d, Y H:i', strtotime($b['booked_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
