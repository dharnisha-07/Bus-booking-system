<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die("Access Denied.");
}

// Handle Add Route
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $stmt = $pdo->prepare("INSERT INTO routes (source_city, destination_city, distance_km, base_fare) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['source_city'], $_POST['destination_city'], $_POST['distance_km'], $_POST['base_fare']]);
    header("Location: routes.php");
    exit;
}

// Handle Delete Route
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM routes WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: routes.php");
    exit;
}

$routes = $pdo->query("SELECT * FROM routes ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Routes - Admin</title>
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
        .form-group input { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 5px; }
        .btn-add { background: #10b981; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header"><i class="fa-solid fa-bus"></i> Admin</div>
    <ul class="nav-links">
        <li><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
        <li><a href="buses.php"><i class="fa-solid fa-van-shuttle"></i> Buses</a></li>
        <li><a href="routes.php" class="active"><i class="fa-solid fa-route"></i> Routes</a></li>
        <li><a href="schedules.php"><i class="fa-solid fa-calendar-days"></i> Schedules</a></li>
        <li><a href="bookings.php"><i class="fa-solid fa-ticket"></i> Bookings</a></li>
        <li><a href="../auth.php?action=logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="header"><h1>Manage Routes</h1></div>
    
    <div class="form-container">
        <h3 style="margin-bottom:15px;">Add New Route</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group"><label>Source City</label><input type="text" name="source_city" required></div>
            <div class="form-group"><label>Destination City</label><input type="text" name="destination_city" required></div>
            <div class="form-group"><label>Distance (km)</label><input type="number" step="0.01" name="distance_km" required></div>
            <div class="form-group"><label>Base Fare (₹)</label><input type="number" step="0.01" name="base_fare" required></div>
            <div class="form-group" style="vertical-align: bottom;"><button type="submit" class="btn-add">Add Route</button></div>
        </form>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th><th>Source</th><th>Destination</th><th>Distance</th><th>Base Fare</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($routes as $route): ?>
            <tr>
                <td><?= $route['id'] ?></td>
                <td><strong><?= htmlspecialchars($route['source_city']) ?></strong></td>
                <td><strong><?= htmlspecialchars($route['destination_city']) ?></strong></td>
                <td><?= htmlspecialchars($route['distance_km']) ?> km</td>
                <td>₹<?= htmlspecialchars($route['base_fare']) ?></td>
                <td><a href="?delete=<?= $route['id'] ?>" class="btn-delete" onclick="return confirm('Delete this route?')">Delete</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
