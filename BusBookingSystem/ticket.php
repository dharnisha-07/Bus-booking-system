<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || empty($_GET['booking_ref'])) {
    die("Invalid Access.");
}

$booking_ref = $_GET['booking_ref'];
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT b.*, u.name as passenger_name, s.bus_name, s.bus_number, s.bus_type, 
           r.source_city, r.destination_city, sch.departure_time, sch.arrival_time
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN schedules sch ON b.schedule_id = sch.id
    JOIN buses s ON sch.bus_id = s.id
    JOIN routes r ON sch.route_id = r.id
    WHERE b.booking_ref = ? AND b.user_id = ? AND b.payment_status = 'completed'
");
$stmt->execute([$booking_ref, $user_id]);
$ticket = $stmt->fetch();

if (!$ticket) {
    die("Ticket not found or payment not completed.");
}

$seats = json_decode($ticket['seat_numbers'], true);
$seatStr = implode(', ', $seats);
$qrData = urlencode("Booking: " . $booking_ref . " | Name: " . $ticket['passenger_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ticket - <?= htmlspecialchars($booking_ref) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Courier Prime', monospace;
            padding: 40px;
        }
        .ticket-wrapper {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .ticket {
            border: 2px dashed #333;
            padding: 30px;
            position: relative;
        }
        .ticket::before, .ticket::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 30px;
            height: 30px;
            background: #fff;
            border-radius: 50%;
            border-right: 2px dashed #333;
            transform: translateY(-50%);
        }
        .ticket::before {
            left: -15px;
        }
        .ticket::after {
            right: -15px;
            border-left: 2px dashed #333;
            border-right: none;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            color: #0d47a1;
        }
        .ref {
            font-size: 18px;
            font-weight: bold;
        }
        .content {
            display: flex;
            gap: 40px;
        }
        .details {
            flex: 2;
        }
        .details-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            border-bottom: 1px dotted #ccc;
            padding-bottom: 5px;
        }
        .label {
            color: #666;
            font-size: 14px;
        }
        .val {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        .qr-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-left: 2px dashed #ccc;
            padding-left: 40px;
        }
        .qr-code {
            width: 150px;
            height: 150px;
            margin-bottom: 10px;
        }
        .print-btn {
            display: block;
            width: 200px;
            margin: 30px auto 0;
            padding: 10px 20px;
            background: #ff6f00;
            color: #fff;
            text-align: center;
            text-decoration: none;
            font-family: inherit;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        @media print {
            body { background: none; padding: 0; }
            .ticket-wrapper { box-shadow: none; margin: 0; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

<div class="ticket-wrapper">
    <div class="ticket">
        <div class="header">
            <h1><i class="fa-solid fa-bus"></i> G-JOURNEY EX</h1>
            <div class="ref">REF: <?= htmlspecialchars($booking_ref) ?></div>
        </div>
        
        <div class="content">
            <div class="details">
                <div class="details-row">
                    <span class="label">Passenger Name</span>
                    <span class="val"><?= htmlspecialchars($ticket['passenger_name']) ?> + <?= count($seats) - 1 ?></span>
                </div>
                <div class="details-row">
                    <span class="label">Journey</span>
                    <span class="val"><?= htmlspecialchars($ticket['source_city']) ?> <i class="fa-solid fa-arrow-right"></i> <?= htmlspecialchars($ticket['destination_city']) ?></span>
                </div>
                <div class="details-row">
                    <span class="label">Date & Time</span>
                    <span class="val"><?= date('D, M d Y h:i A', strtotime($ticket['departure_time'])) ?></span>
                </div>
                <div class="details-row">
                    <span class="label">Bus Info</span>
                    <span class="val"><?= htmlspecialchars($ticket['bus_name']) ?> (<?= htmlspecialchars($ticket['bus_type']) ?>) - <?= htmlspecialchars($ticket['bus_number']) ?></span>
                </div>
                <div class="details-row">
                    <span class="label">Seat Number(s)</span>
                    <span class="val"><?= htmlspecialchars($seatStr) ?></span>
                </div>
                <div class="details-row" style="border:none;">
                    <span class="label">Total Fare Paid</span>
                    <span class="val" style="font-size:20px;">₹<?= htmlspecialchars($ticket['total_amount']) ?></span>
                </div>
            </div>
            
            <div class="qr-section">
                <!-- Using a simple public API for demo purposes -->
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= $qrData ?>" alt="QR Code" class="qr-code">
                <span class="label">Scan for Boarding Pass</span>
            </div>
        </div>
    </div>
    
    <button class="print-btn" onclick="window.print()"><i class="fa-solid fa-print"></i> Print Ticket</button>
</div>

</body>
</html>
