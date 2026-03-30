<?php
include 'includes/header.php';
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<div style='text-align:center; padding: 100px;'><h3>Access Denied. Please login.</h3></div>";
    include 'includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['schedule_id']) || empty($_POST['seats'])) {
    echo "<div style='text-align:center; padding: 100px;'><h3>Invalid Request.</h3></div>";
    include 'includes/footer.php';
    exit;
}

$schedule_id = $_POST['schedule_id'];
$seatsJson = $_POST['seats'];
$seats = json_decode($seatsJson, true);
$total_amount = $_POST['total_amount'] ?? 0;

if (!$seats || count($seats) === 0) {
    echo "<div style='text-align:center; padding: 100px;'><h3>No seats selected.</h3></div>";
    include 'includes/footer.php';
    exit;
}

// Fetch schedule
$stmt = $pdo->prepare("
    SELECT s.*, b.bus_name, b.bus_type, r.source_city, r.destination_city 
    FROM schedules s
    JOIN buses b ON s.bus_id = b.id
    JOIN routes r ON s.route_id = r.id
    WHERE s.id = ?
");
$stmt->execute([$schedule_id]);
$schedule = $stmt->fetch();
?>

<div style="max-width: 1000px; margin: 100px auto 50px; padding: 0 20px; display: flex; gap: 40px; flex-wrap:wrap;">
    <div style="flex: 2; min-width:300px; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
        <h2 style="color: var(--primary-color); margin-bottom: 20px;">Passenger Details</h2>
        <form action="api/book.php" method="POST" id="confirmBookingForm">
            <input type="hidden" name="schedule_id" value="<?= htmlspecialchars($schedule_id) ?>">
            <input type="hidden" name="seats" value="<?= htmlspecialchars($seatsJson) ?>">
            <input type="hidden" name="total_amount" value="<?= htmlspecialchars($total_amount) ?>">
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Primary Contact Email</label>
                <input type="email" name="contact_email" value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>" required>
            </div>
            <div class="form-group" style="margin-bottom: 30px;">
                <label>Primary Contact Phone</label>
                <input type="text" name="contact_phone" required>
            </div>

            <h3 style="margin-bottom: 15px; color: #555;">Passenger Names</h3>
            <?php foreach ($seats as $i => $seat): ?>
            <div class="form-group" style="margin-bottom: 15px; background: #f9f9f9; padding:15px; border-radius:5px;">
                <label>Passenger <?= $i + 1 ?> (Seat <?= htmlspecialchars($seat) ?>)</label>
                <input type="text" name="passenger[]" placeholder="Full Name" required>
            </div>
            <?php endforeach; ?>

            <button type="submit" class="btn-search" style="width:100%; margin-top: 20px;" id="btnConfirmBook">Proceed to Payment</button>
        </form>
    </div>

    <div style="flex: 1; min-width:300px;">
        <div class="summary-panel" style="position: sticky; top: 100px;">
            <div class="summary-header">
                <h3><?= htmlspecialchars($schedule['source_city']) ?> <i class="fa-solid fa-arrow-right"></i> <?= htmlspecialchars($schedule['destination_city']) ?></h3>
                <p><?= htmlspecialchars($schedule['bus_name']) ?> (<?= htmlspecialchars($schedule['bus_type']) ?>)</p>
                <p><i class="fa-regular fa-calendar"></i> <?= htmlspecialchars($schedule['travel_date']) ?> at <?= date('h:i A', strtotime($schedule['departure_time'])) ?></p>
            </div>
            
            <h4 style="margin:20px 0 10px;">Selected Seats:</h4>
            <div>
                <?php foreach ($seats as $s): ?>
                    <span class="seat-badge" style="display:inline-block; background:#ffb300; color:white; padding:5px 10px; border-radius:5px; margin:0 5px 5px 0; font-size:12px; font-weight:bold;"><?= htmlspecialchars($s) ?></span>
                <?php endforeach; ?>
            </div>

            <div style="border-top: 1px solid #ddd; margin-top: 20px; padding-top: 20px; font-size: 24px; font-weight: bold; color: var(--secondary-color); display: flex; justify-content: space-between;">
                <span>Total Fare:</span>
                <span>$<?= htmlspecialchars($total_amount) ?></span>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
