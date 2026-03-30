<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    die("Access Denied.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

$user_id = $_SESSION['user_id'];
$schedule_id = $_POST['schedule_id'];
$seatsJson = $_POST['seats'];
$seats = json_decode($seatsJson, true);
$total_amount = $_POST['total_amount'];

if (!$seats || empty($schedule_id)) {
    die("Invalid data.");
}

// Start transaction to lock seats
try {
    $pdo->beginTransaction();

    // Check if seats are already booked
    $placeholders = implode(',', array_fill(0, count($seats), '?'));
    $checkStmt = $pdo->prepare("SELECT seat_number FROM seats WHERE schedule_id = ? AND seat_number IN ($placeholders) AND status IN ('booked', 'locked') FOR UPDATE");
    
    $params = array_merge([$schedule_id], $seats);
    $checkStmt->execute($params);
    $alreadyBooked = $checkStmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($alreadyBooked) > 0) {
        $pdo->rollBack();
        die("Sorry, one or more selected seats (" . implode(',', $alreadyBooked) . ") were just booked by someone else. Please go back and select different seats.");
    }

    // Insert Booking
    $booking_ref = 'BKG' . strtoupper(uniqid());
    $bookStmt = $pdo->prepare("INSERT INTO bookings (user_id, schedule_id, seat_numbers, total_amount, payment_status, booking_ref) VALUES (?, ?, ?, ?, 'pending', ?)");
    $bookStmt->execute([$user_id, $schedule_id, $seatsJson, $total_amount, $booking_ref]);
    $booking_id = $pdo->lastInsertId();

    // Insert Seats
    $seatStmt = $pdo->prepare("INSERT INTO seats (schedule_id, seat_number, status, booked_by) VALUES (?, ?, 'locked', ?)");
    foreach ($seats as $seat) {
        $seatStmt->execute([$schedule_id, $seat, $user_id]);
    }

    // Also deduct available seats in schedule logic, assuming it's managed, but it will be done on payment success. Wait, can do it now or later.
    // Let's deduct now and if cancelled, add back.
    $updSched = $pdo->prepare("UPDATE schedules SET available_seats = available_seats - ? WHERE id = ?");
    $updSched->execute([count($seats), $schedule_id]);

    $pdo->commit();

    // Redirect to payment page
    header("Location: ../payment.php?booking_ref=$booking_ref");
    exit;

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Booking Failed: " . $e->getMessage());
}
?>
