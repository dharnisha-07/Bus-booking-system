<?php
include 'includes/header.php';
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all bookings for user
$stmt = $pdo->prepare("
    SELECT b.*, s.bus_name, s.bus_type, r.source_city, r.destination_city, sch.departure_time, sch.arrival_time
    FROM bookings b
    JOIN schedules sch ON b.schedule_id = sch.id
    JOIN buses s ON sch.bus_id = s.id
    JOIN routes r ON sch.route_id = r.id
    WHERE b.user_id = ?
    ORDER BY b.booked_at DESC
");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();
?>

<div style="max-width: 1200px; margin: 100px auto 50px; padding: 0 20px;">
    <h1 style="color: var(--primary-color); margin-bottom: 30px;">My Dashboard</h1>

    <div style="display: flex; gap: 30px; flex-wrap: wrap;">
        
        <!-- Sidebar -->
        <div style="flex: 0 0 250px; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); height: fit-content;">
            <div style="text-align:center; margin-bottom:20px;">
                <div style="width:80px; height:80px; background:var(--primary-color); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:30px; margin:0 auto 10px;">
                    <i class="fa-solid fa-user"></i>
                </div>
                <h3><?= htmlspecialchars($_SESSION['user_name']) ?></h3>
                <p style="color:#666; font-size:14px;">User Account</p>
            </div>
            <ul style="list-style:none; border-top:1px solid #eee; padding-top:20px;">
                <li style="margin-bottom:15px;"><a href="#" style="text-decoration:none; color:var(--primary-color); font-weight:bold;"><i class="fa-solid fa-ticket"></i> My Bookings</a></li>
                <li><a href="auth.php?action=logout" style="text-decoration:none; color:#d32f2f;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div style="flex: 1;">
            <h2 style="margin-bottom: 20px; color: #333;">My Bookings</h2>

            <?php if(count($bookings) === 0): ?>
                <div style="background: white; padding: 40px; border-radius: 10px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
                    <i class="fa-solid fa-ticket fa-3x" style="color:#ccc; margin-bottom:15px;"></i>
                    <h3>You have no bookings yet.</h3>
                    <a href="index.php" class="btn-search" style="display:inline-block; margin-top:15px; text-decoration:none;">Book a Bus</a>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <?php foreach ($bookings as $b): 
                        $isUpcoming = strtotime($b['departure_time']) > time();
                        $statusColor = $b['payment_status'] === 'completed' ? '#4caf50' : ($b['payment_status'] === 'pending' ? '#ff9800' : '#f44336');
                    ?>
                    <div style="background: white; border-radius: 10px; padding: 20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; box-shadow: 0 5px 15px rgba(0,0,0,0.05); border-left: 5px solid <?= $statusColor ?>;">
                        
                        <div style="flex:2; min-width:300px;">
                            <div style="font-size:12px; color:#999; margin-bottom:5px;">REF: <?= htmlspecialchars($b['booking_ref']) ?> &bull; Booked on <?= date('M d, Y', strtotime($b['booked_at'])) ?></div>
                            <h3 style="color:var(--primary-color); margin-bottom:5px;">
                                <?= htmlspecialchars($b['source_city']) ?> <i class="fa-solid fa-arrow-right"></i> <?= htmlspecialchars($b['destination_city']) ?>
                            </h3>
                            <p style="color:#555; font-size:14px; margin-bottom:10px;">
                                <i class="fa-regular fa-calendar" style="color:var(--secondary-color);"></i> <?= date('D, M d Y h:i A', strtotime($b['departure_time'])) ?>
                            </p>
                            <p style="font-size:14px; color:#555;"><i class="fa-solid fa-bus"></i> <?= htmlspecialchars($b['bus_name']) ?> (<?= htmlspecialchars($b['bus_type']) ?>)</p>
                            
                            <div style="margin-top:10px;">
                                <span style="display:inline-block; background:#eee; padding:3px 8px; border-radius:3px; font-size:12px; margin-right:10px;">Fare: <strong>₹<?= htmlspecialchars($b['total_amount']) ?></strong></span>
                                <span style="display:inline-block; background:<?= $statusColor ?>20; color:<?= $statusColor ?>; padding:3px 8px; border-radius:3px; font-size:12px; font-weight:bold; text-transform:uppercase;">
                                    <?= htmlspecialchars($b['payment_status']) ?>
                                </span>
                            </div>
                        </div>
                        
                        <div style="flex:1; min-width:150px; text-align:right; display:flex; flex-direction:column; gap:10px; margin-top:20px;">
                            <?php if ($b['payment_status'] === 'completed'): ?>
                                <a href="ticket.php?booking_ref=<?= urlencode($b['booking_ref']) ?>" target="_blank" class="btn-book" style="text-align:center;"><i class="fa-solid fa-print"></i> View Ticket</a>
                            <?php elseif ($b['payment_status'] === 'pending'): ?>
                                <a href="payment.php?booking_ref=<?= urlencode($b['booking_ref']) ?>" class="btn-search" style="text-decoration:none; text-align:center;">Pay Now</a>
                            <?php endif; ?>

                            <?php if ($isUpcoming && $b['payment_status'] !== 'failed'): ?>
                                <button onclick="cancelBooking('<?= htmlspecialchars($b['booking_ref']) ?>')" style="background:transparent; border:1px solid #d32f2f; color:#d32f2f; padding:8px; border-radius:5px; cursor:pointer; font-weight:bold; transition:all 0.3s;" onmouseover="this.style.background='#d32f2f';this.style.color='white';" onmouseout="this.style.background='transparent';this.style.color='#d32f2f';">Cancel Booking</button>
                            <?php endif; ?>
                        </div>

                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
function cancelBooking(ref) {
    if (confirm('Are you sure you want to cancel this booking? Refund will be processed automatically based on policy.')) {
        fetch('api/cancel.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ booking_ref: ref })
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                alert('Booking cancelled successfully.');
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(e => {
            alert('An error occurred.');
            console.error(e);
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>
