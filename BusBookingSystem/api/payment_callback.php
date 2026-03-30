<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

$booking_ref = $_POST['booking_ref'] ?? '';
$user_id = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT id, schedule_id, seat_numbers, total_amount FROM bookings WHERE booking_ref = ? AND user_id = ? AND payment_status = 'pending' FOR UPDATE");
    $stmt->execute([$booking_ref, $user_id]);
    $booking = $stmt->fetch();

    if (!$booking) {
        throw new Exception("Booking not found or already paid.");
    }

    $transaction_id = 'TXN' . strtoupper(uniqid());

    // Insert payment
    $payStmt = $pdo->prepare("INSERT INTO payments (booking_id, transaction_id, method, amount, status, paid_at) VALUES (?, ?, 'Card', ?, 'success', NOW())");
    $payStmt->execute([$booking['id'], $transaction_id, $booking['total_amount']]);

    // Update booking status
    $updBook = $pdo->prepare("UPDATE bookings SET payment_status = 'completed' WHERE id = ?");
    $updBook->execute([$booking['id']]);

    // Update seats status to 'booked'
    $seats = json_decode($booking['seat_numbers'], true);
    $placeholders = implode(',', array_fill(0, count($seats), '?'));
    
    // Note: They were marked 'locked', now change to 'booked'
    $updSeats = $pdo->prepare("UPDATE seats SET status = 'booked' WHERE schedule_id = ? AND seat_number IN ($placeholders)");
    $params = array_merge([$booking['schedule_id']], $seats);
    $updSeats->execute($params);

    $pdo->commit();

    // Force sleep slightly to simulate real payment processing delay
    sleep(1);

    header("Location: ../confirmation.php?booking_ref=$booking_ref");
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Payment failed: " . $e->getMessage());
}
?>
