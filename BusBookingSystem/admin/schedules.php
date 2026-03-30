<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die("Access Denied.");
}

// Handle Add Schedule
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    // Fetch total seats from the selected bus to populate available_seats initially
    $busStmt = $pdo->prepare("SELECT total_seats FROM buses WHERE id = ?");
    $busStmt->execute([$_POST['bus_id']]);
    $bus = $busStmt->fetch();
    $available_seats = $bus ? $bus['total_seats'] : 40;

    $stmt = $pdo->prepare("INSERT INTO schedules (bus_id, route_id, departure_time, arrival_time, travel_date, available_seats, price) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['bus_id'], 
        $_POST['route_id'], 
        $_POST['departure_time'], 
        $_POST['arrival_time'], 
        $_POST['travel_date'], 
        $available_seats, 
        $_POST['price']
    ]);
    header("Location: schedules.php");
    exit;
}

// Handle Delete Schedule
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: schedules.php");
    exit;
}

// Fetch lookups for form
$busesList = $pdo->query("SELECT id, bus_name, bus_type FROM buses ORDER BY bus_name")->fetchAll();
$routesList = $pdo->query("SELECT id, source_city, destination_city FROM routes ORDER BY source_city")->fetchAll();

// Fetch schedules
$schedules = $pdo->query("
    SELECT s.*, b.bus_name, b.total_seats, r.source_city, r.destination_city 
    FROM schedules s 
    JOIN buses b ON s.bus_id = b.id 
    JOIN routes r ON s.route_id = r.id 
    ORDER BY s.travel_date DESC, s.departure_time ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Schedules - Admin</title>
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
        .header { margin-bottom: 30px; }
        .header h1 { font-size: 24px; color: #111827; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #f3f4f6; }
        th { background: #f9fafb; font-weight: 600; color: #6b7280; text-transform: uppercase; font-size: 12px; }
        tr:hover { background: #f9fafb; }
        .btn-delete { color: #dc2626; text-decoration: none; font-weight: bold; }
        .form-container { background: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 15px; display: inline-block; width: 32%; padding-right: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 14px; font-weight: 600; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 5px; }
        .btn-add { background: #10b981; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; width:100%; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; background: #e0e7ff; color: #4f46e5; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header"><i class="fa-solid fa-bus"></i> Admin</div>
    <ul class="nav-links">
        <li><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
        <li><a href="buses.php"><i class="fa-solid fa-van-shuttle"></i> Buses</a></li>
        <li><a href="routes.php"><i class="fa-solid fa-route"></i> Routes</a></li>
        <li><a href="schedules.php" class="active"><i class="fa-solid fa-calendar-days"></i> Schedules</a></li>
        <li><a href="bookings.php"><i class="fa-solid fa-ticket"></i> Bookings</a></li>
        <li><a href="../auth.php?action=logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="header"><h1>Manage Schedules</h1></div>
    
    <div class="form-container">
        <h3 style="margin-bottom:15px;">Add New Schedule</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label>Select Bus</label>
                <select name="bus_id" required>
                    <?php foreach($busesList as $b): ?>
                        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['bus_name']) ?> (<?= htmlspecialchars($b['bus_type']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Select Route</label>
                <select name="route_id" required>
                    <?php foreach($routesList as $r): ?>
                        <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['source_city']) ?> → <?= htmlspecialchars($r['destination_city']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Travel Date</label>
                <input type="date" name="travel_date" required>
            </div>
            
            <div class="form-group">
                <label>Departure Time</label>
                <input type="datetime-local" name="departure_time" required>
            </div>
            
            <div class="form-group">
                <label>Arrival Time</label>
                <input type="datetime-local" name="arrival_time" required>
            </div>
            
            <div class="form-group">
                <label>Ticket Price (₹)</label>
                <input type="number" step="0.01" name="price" required>
            </div>
            
            <div class="form-group" style="vertical-align: bottom;">
                <button type="submit" class="btn-add">Add Schedule</button>
            </div>
        </form>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Date</th><th>Route</th><th>Bus</th><th>Departs</th><th>Arrives</th><th>Seats</th><th>Price</th><th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($schedules as $s): ?>
            <tr>
                <td><strong><?= htmlspecialchars($s['travel_date']) ?></strong></td>
                <td><?= htmlspecialchars($s['source_city']) ?> → <?= htmlspecialchars($s['destination_city']) ?></td>
                <td><span class="badge"><?= htmlspecialchars($s['bus_name']) ?></span></td>
                <td><?= date('h:i A', strtotime($s['departure_time'])) ?></td>
                <td><?= date('h:i A', strtotime($s['arrival_time'])) ?></td>
                <td><?= $s['available_seats'] ?> / <?= $s['total_seats'] ?></td>
                <td>₹<?= htmlspecialchars($s['price']) ?></td>
                <td><a href="?delete=<?= $s['id'] ?>" class="btn-delete" onclick="return confirm('Delete this schedule?')">Delete</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
