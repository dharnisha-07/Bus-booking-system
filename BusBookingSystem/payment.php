<?php
include 'includes/header.php';
require_once 'db.php';

if (!isset($_SESSION['user_id']) || empty($_GET['booking_ref'])) {
    echo "<div style='text-align:center; padding: 100px;'><h3>Invalid Access.</h3></div>";
    include 'includes/footer.php';
    exit;
}

$booking_ref = $_GET['booking_ref'];

$stmt = $pdo->prepare("SELECT b.*, s.bus_name, r.source_city, r.destination_city 
                       FROM bookings b
                       JOIN schedules sch ON b.schedule_id = sch.id
                       JOIN buses s ON sch.bus_id = s.id
                       JOIN routes r ON sch.route_id = r.id
                       WHERE b.booking_ref = ? AND b.user_id = ? AND b.payment_status = 'pending'");
$stmt->execute([$booking_ref, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    echo "<div style='text-align:center; padding: 100px;'><h3>Booking not found or already paid.</h3></div>";
    include 'includes/footer.php';
    exit;
}
?>

<div style="max-width: 600px; margin: 100px auto 50px; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center;">
    <h2 style="color: var(--primary-color); margin-bottom: 10px;">Payment Gateway</h2>
    <p style="color: #666; margin-bottom: 30px;">Complete your booking for <strong><?= htmlspecialchars($booking['source_city']) ?> to <?= htmlspecialchars($booking['destination_city']) ?></strong></p>

    <div style="background: #f4f6f9; padding: 20px; border-radius: 10px; margin-bottom: 30px; font-size: 18px;">
        <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
            <span>Booking Ref:</span>
            <strong><?= htmlspecialchars($booking_ref) ?></strong>
        </div>
        <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
            <span>Bus:</span>
            <strong><?= htmlspecialchars($booking['bus_name']) ?></strong>
        </div>
        <div style="display:flex; justify-content:space-between; border-top: 1px dashed #ccc; padding-top: 10px;">
            <span>Amount to Pay:</span>
            <strong style="color: var(--secondary-color); font-size: 24px;">₹<?= htmlspecialchars($booking['total_amount']) ?></strong>
        </div>
    </div>

    <!-- Simulated Payment Form -->
    <form action="api/payment_callback.php" method="POST" id="paymentForm">
        <input type="hidden" name="booking_ref" value="<?= htmlspecialchars($booking_ref) ?>">
        
        <div style="text-align:left; margin-bottom: 15px;">
            <label style="display:block; margin-bottom:5px; font-size:14px; color:#666;">Card Number</label>
            <input type="text" value="**** **** **** 4242" disabled style="width:100%; padding:12px; border:1px solid #ddd; background:#e9e9e9; border-radius:5px;">
        </div>
        
        <div style="display:flex; gap:15px; margin-bottom:30px;">
            <div style="flex:1; text-align:left;">
                <label style="display:block; margin-bottom:5px; font-size:14px; color:#666;">Expiry</label>
                <input type="text" value="12/25" disabled style="width:100%; padding:12px; border:1px solid #ddd; background:#e9e9e9; border-radius:5px;">
            </div>
            <div style="flex:1; text-align:left;">
                <label style="display:block; margin-bottom:5px; font-size:14px; color:#666;">CVV</label>
                <input type="text" value="***" disabled style="width:100%; padding:12px; border:1px solid #ddd; background:#e9e9e9; border-radius:5px;">
            </div>
        </div>

        <button type="submit" class="btn-search" style="width:100%; padding: 15px; font-size: 18px;" id="payBtn">
            Pay ₹<?= htmlspecialchars($booking['total_amount']) ?> Now
        </button>
    </form>

    <div style="text-align:center; margin-top:20px; font-size:12px; color:#aaa;">
        <i class="fa-solid fa-lock"></i> Secure 256-bit encryption
    </div>
</div>

<script>
document.getElementById('paymentForm').addEventListener('submit', (e) => {
    const btn = document.getElementById('payBtn');
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
    btn.disabled = true;
});
</script>

<?php include 'includes/footer.php'; ?>
