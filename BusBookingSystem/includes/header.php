<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user_logged_in = isset($_SESSION['user_id']);
$user_name = $user_logged_in ? $_SESSION['user_name'] : '';
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'user';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journey Bus Booking</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div id="spinnerOverlay" class="spinner-overlay">
    <div class="spinner"></div>
</div>

<nav class="navbar">
    <a href="index.php" class="logo">
        <i class="fa-solid fa-bus-simple"></i> Journey
    </a>
    <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="#routes">Routes</a></li>
        <?php if($user_logged_in): ?>
            <li><a href="dashboard.php">Dashboard</a></li>
            <?php if($user_role === 'admin'): ?>
                <li><a href="admin/index.php">Admin Panel</a></li>
            <?php endif; ?>
            <li><a href="auth.php?action=logout" class="btn-login" style="background-color:#d32f2f;">Logout (<?= htmlspecialchars($user_name) ?>)</a></li>
        <?php else: ?>
            <li><a href="#" id="loginBtn" class="btn-login">Login / Register</a></li>
        <?php endif; ?>
    </ul>
</nav>

<!-- Auth Modal -->
<div class="modal-overlay" id="authModal">
    <div class="auth-modal">
        <span class="close-modal">&times;</span>
        <div class="auth-tabs">
            <div class="auth-tab active" data-target="loginTab">Login</div>
            <div class="auth-tab" data-target="registerTab">Register</div>
        </div>
        
        <!-- Login Form -->
        <div class="auth-form-content active" id="loginTab">
            <form id="loginForm" class="auth-form">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn-submit">Login</button>
            </form>
        </div>

        <!-- Register Form -->
        <div class="auth-form-content" id="registerTab">
            <form id="registerForm" class="auth-form">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone">
                </div>
                <button type="submit" class="btn-submit">Register</button>
            </form>
        </div>
    </div>
</div>
