<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die("Access Denied.");
}

// Handle Add Bus
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $stmt = $pdo->prepare("INSERT INTO buses (bus_name, bus_number, total_seats, bus_type, amenities) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['bus_name'], $_POST['bus_number'], $_POST['total_seats'], $_POST['bus_type'], $_POST['amenities']]);
    header("Location: buses.php");
    exit;
}

// Handle Delete Bus
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM buses WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: buses.php");
    exit;
}

$buses = $pdo->query("SELECT * FROM buses ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Buses - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Shared Admin Styles */
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
        
        /* Table Styles */
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #f3f4f6; }
        th { background: #f9fafb; font-weight: 600; color: #6b7280; text-transform: uppercase; font-size: 12px; }
        tr:hover { background: #f9fafb; }
        .btn-delete { color: #dc2626; text-decoration: none; font-weight: bold; }
        
        /* Form Styles */
        .form-container { background: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 15px; display: inline-block; width: 32%; padding-right: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 14px; font-weight: 600; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 5px; }
        .btn-add { background: #10b981; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header"><i class="fa-solid fa-bus"></i> Admin</div>
    <ul class="nav-links">
        <li><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
        <li><a href="buses.php" class="active"><i class="fa-solid fa-van-shuttle"></i> Buses</a></li>
        <li><a href="routes.php"><i class="fa-solid fa-route"></i> Routes</a></li>
        <li><a href="schedules.php"><i class="fa-solid fa-calendar-days"></i> Schedules</a></li>
        <li><a href="bookings.php"><i class="fa-solid fa-ticket"></i> Bookings</a></li>
        <li><a href="../auth.php?action=logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="header"><h1>Manage Buses</h1></div>
    
    <div class="form-container">
        <h3 style="margin-bottom:15px;">Add New Bus</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group"><label>Bus Name</label><input type="text" name="bus_name" required></div>
            <div class="form-group"><label>Bus Number</label><input type="text" name="bus_number" required></div>
            <div class="form-group"><label>Total Seats</label><input type="number" name="total_seats" value="40" required></div>
            <div class="form-group"><label>Type</label><select name="bus_type"><option value="AC">AC</option><option value="Non-AC">Non-AC</option><option value="Sleeper">Sleeper</option></select></div>
            <div class="form-group"><label>Amenities</label><input type="text" name="amenities"></div>
            <div class="form-group" style="vertical-align: bottom;"><button type="submit" class="btn-add">Add Bus</button></div>
        </form>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th><th>Bus Name</th><th>Number</th><th>Type</th><th>Seats</th><th>Amenities</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($buses as $bus): ?>
            <tr>
                <td><?= $bus['id'] ?></td>
                <td><strong><?= htmlspecialchars($bus['bus_name']) ?></strong></td>
                <td><?= htmlspecialchars($bus['bus_number']) ?></td>
                <td><?= htmlspecialchars($bus['bus_type']) ?></td>
                <td><?= htmlspecialchars($bus['total_seats']) ?></td>
                <td><?= htmlspecialchars($bus['amenities']) ?></td>
                <td><a href="?delete=<?= $bus['id'] ?>" class="btn-delete" onclick="return confirm('Delete this bus?')">Delete</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
