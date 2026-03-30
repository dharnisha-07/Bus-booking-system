<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die("Access Denied.");
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=bookings_export.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['Booking ID', 'Ref', 'User Name', 'Email', 'Source', 'Destination', 'Date', 'Amount', 'Payment Status']);

$stmt = $pdo->query("
    SELECT b.id, b.booking_ref, u.name, u.email, r.source_city, r.destination_city, sch.travel_date, b.total_amount, b.payment_status
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN schedules sch ON b.schedule_id = sch.id
    JOIN routes r ON sch.route_id = r.id
    ORDER BY b.id DESC
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, $row);
}

fclose($output);
exit;
?>
