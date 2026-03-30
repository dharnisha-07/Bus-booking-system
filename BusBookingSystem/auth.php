<?php
// auth.php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Fallback to $_POST if not JSON
    if (!$input) {
        $input = $_POST;
    }

    if ($action === 'register') {
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';
        $phone = trim($input['phone'] ?? '');

        if (empty($name) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Name, email, and password are required.']);
            exit;
        }

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email is already registered.']);
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$name, $email, $hashedPassword, $phone])) {
            $userId = $pdo->lastInsertId();
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_role'] = 'user';
            
            echo json_encode(['success' => true, 'message' => 'Registration successful.', 'redirect' => 'index.php']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Registration failed.']);
        }
        exit;
    }

    if ($action === 'login') {
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';

        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            $redirect = $user['role'] === 'admin' ? 'admin/index.php' : 'index.php';
            echo json_encode(['success' => true, 'message' => 'Login successful.', 'redirect' => $redirect]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
        }
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'logout') {
        session_destroy();
        header("Location: index.php");
        exit;
    }
    
    // current user info
    if ($action === 'check') {
        if (isset($_SESSION['user_id'])) {
            echo json_encode([
                'logged_in' => true, 
                'user' => [
                    'id' => $_SESSION['user_id'],
                    'name' => $_SESSION['user_name'],
                    'role' => $_SESSION['user_role']
                ]
            ]);
        } else {
            echo json_encode(['logged_in' => false]);
        }
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
?>
