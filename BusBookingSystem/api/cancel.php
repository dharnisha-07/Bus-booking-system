<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$booking_ref = $input['booking_ref'] ?? '';
$user_id = $_SESSION['user_id'];

if (empty($booking_ref)) {
    echo json_encode(['success' => false, 'message' => 'Invalid booking reference.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Fetch booking
    $stmt = $pdo->prepare("SELECT id, schedule_id, seat_numbers, payment_status FROM bookings WHERE booking_ref = ? AND user_id = ? FOR UPDATE");
    $stmt->execute([$booking_ref, $user_id]);
    $booking = $stmt->fetch();

    if (!$booking) {
        throw new Exception("Booking not found.");
    }

    if ($booking['payment_status'] === 'failed') {
        throw new Exception("Booking is already failed/cancelled.");
    }

    // Update booking status
    $updBook = $pdo->prepare("UPDATE bookings SET payment_status = 'failed' WHERE id = ?");
    $updBook->execute([$booking['id']]);

    // Release seats
    $seats = json_decode($booking['seat_numbers'], true);
    if (!empty($seats)) {
        $placeholders = implode(',', array_fill(0, count($seats), '?'));
        $delSeats = $pdo->prepare("DELETE FROM seats WHERE schedule_id = ? AND seat_number IN ($placeholders)");
        $params = array_merge([$booking['schedule_id']], $seats);
        $delSeats->execute($params);

        // Add back to available_seats
        $updSched = $pdo->prepare("UPDATE schedules SET available_seats = available_seats + ? WHERE id = ?");
        $updSched->execute([count($seats), $booking['schedule_id']]);
    }

    // Add refund logic to payments table if it was completed
    if ($booking['payment_status'] === 'completed') {
        $payStmt = $pdo->prepare("UPDATE payments SET status = 'failed' WHERE booking_id = ?"); // or create a refund record
        $payStmt->execute([$booking['id']]);
    }

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Booking cancelled securely.']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
