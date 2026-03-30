<?php 
include 'includes/header.php'; 
require_once 'db.php';

$schedule_id = $_GET['schedule_id'] ?? null;

if (!$schedule_id) {
    echo "<div style='text-align:center; padding: 100px;'><h3>Invalid Schedule ID.</h3></div>";
    include 'includes/footer.php';
    exit;
}

// Fetch schedule details
$stmt = $pdo->prepare("
    SELECT s.*, b.bus_name, b.bus_type, r.source_city, r.destination_city 
    FROM schedules s
    JOIN buses b ON s.bus_id = b.id
    JOIN routes r ON s.route_id = r.id
    WHERE s.id = ?
");
$stmt->execute([$schedule_id]);
$schedule = $stmt->fetch();

if (!$schedule) {
    echo "<div style='text-align:center; padding: 100px;'><h3>Schedule not found.</h3></div>";
    include 'includes/footer.php';
    exit;
}

// Fetch booked seats
$stmt = $pdo->prepare("SELECT seat_number FROM seats WHERE schedule_id = ? AND status IN ('booked', 'locked')");
$stmt->execute([$schedule_id]);
$bookedSeats = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Total seats is fixed at 40 in 2-2 layout as per requirement
$total_seats = 40;
?>

<style>
.seat-selection-container {
    max-width: 1000px;
    margin: 100px auto 50px;
    padding: 0 20px;
    display: flex;
    gap: 40px;
    align-items: flex-start;
}

.bus-layout {
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    width: fit-content;
    position: relative;
    border: 3px solid #ddd;
    border-top-left-radius: 40px;
    border-top-right-radius: 40px;
}

.driver-area {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 2px dashed #eee;
}

.steering-wheel {
    width: 40px;
    height: 40px;
    border: 4px solid #666;
    border-radius: 50%;
    position: relative;
}
.steering-wheel::after {
    content: '';
    position: absolute;
    top: 50%; left: 0; right: 0;
    height: 4px;
    background: #666;
    transform: translateY(-50%);
}

.seats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr) 40px repeat(2, 1fr);
    gap: 10px;
}

.seat {
    width: 35px;
    height: 40px;
    background: #4caf50; /* Green = available */
    border-radius: 5px 5px 2px 2px;
    cursor: pointer;
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    color: white;
    font-size: 11px;
    font-weight: bold;
    transition: all 0.2s;
    user-select: none;
}

.seat::before {
    content: '';
    position: absolute;
    bottom: -5px;
    width: 80%;
    height: 5px;
    background: rgba(0,0,0,0.1);
    border-radius: 0 0 5px 5px;
}

.seat.booked {
    background: #f44336; /* Red = booked */
    cursor: not-allowed;
    color: transparent; /* hide number or show an icon */
}
.seat.booked::after {
    content: '\f00d'; /* FontAwesome times icon */
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    color: white;
    position: absolute;
}

.seat.selected {
    background: #ffb300; /* Yellow = selected */
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.seat:hover:not(.booked) {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    filter: brightness(1.1);
}

.summary-panel {
    flex: 1;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    position: sticky;
    top: 100px;
}

.summary-header {
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.summary-header h3 { color: var(--primary-color); }
.summary-header p { color: #666; font-size: 14px; margin-top: 5px; }

.legend {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    font-size: 13px;
}
.legend-item { display: flex; align-items: center; gap: 5px; }
.legend-box { width: 15px; height: 15px; border-radius: 3px; }

.selected-seats-container {
    min-height: 50px;
    margin-bottom: 20px;
}
.seat-badge {
    display: inline-block;
    background: #ffb300;
    color: white;
    padding: 5px 10px;
    border-radius: 5px;
    margin: 0 5px 5px 0;
    font-weight: bold;
    font-size: 12px;
}

.fare-total {
    font-size: 24px;
    font-weight: bold;
    color: var(--secondary-color);
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
}

.btn-proceed {
    width: 100%;
    padding: 15px;
    background-color: var(--primary-color);
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s;
    text-align: center;
    text-decoration: none;
    display: inline-block;
}
.btn-proceed:hover { background-color: #08306b; }
.btn-proceed:disabled { background-color: #ccc; cursor: not-allowed; }

@media (max-width: 768px) {
    .seat-selection-container { flex-direction: column; align-items: center; }
    .summary-panel { width: 100%; position: static; }
}
</style>

<div class="seat-selection-container">
    <div class="bus-layout">
        <div class="driver-area">
            <div class="steering-wheel"></div>
        </div>
        
        <div class="seats-grid" id="seatsGrid">
            <?php 
            $seatRowLabels = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
            $colConfig = [1, 2, 'aisle', 3, 4]; // 2-2 with aisle
            $seatIndex = 0;
            
            for ($row = 0; $row < 10; $row++) { // 10 rows * 4 seats = 40 seats
                foreach ($colConfig as $col) {
                    if ($col === 'aisle') {
                        echo "<div></div>"; // Aisle space
                    } else {
                        $seatNumber = $seatRowLabels[$row] . $col;
                        $isBooked = in_array($seatNumber, $bookedSeats);
                        $class = $isBooked ? 'seat booked' : 'seat';
                        echo "<div class='$class' data-seat='$seatNumber'>$seatNumber</div>";
                    }
                }
            }
            ?>
        </div>
    </div>

    <div class="summary-panel">
        <div class="summary-header">
            <h3><?= htmlspecialchars($schedule['source_city']) ?> <i class="fa-solid fa-arrow-right"></i> <?= htmlspecialchars($schedule['destination_city']) ?></h3>
            <p><?= htmlspecialchars($schedule['bus_name']) ?> (<?= htmlspecialchars($schedule['bus_type']) ?>)</p>
            <p><i class="fa-regular fa-calendar"></i> <?= htmlspecialchars($schedule['travel_date']) ?> at <?= date('h:i A', strtotime($schedule['departure_time'])) ?></p>
        </div>

        <div class="legend">
            <div class="legend-item"><div class="legend-box" style="background:#4caf50;"></div> Available</div>
            <div class="legend-item"><div class="legend-box" style="background:#f44336;"></div> Booked</div>
            <div class="legend-item"><div class="legend-box" style="background:#ffb300;"></div> Selected</div>
        </div>

        <h4>Selected Seats:</h4>
        <div class="selected-seats-container" id="selectedSeatsList">
            <span style="color:#999;font-size:14px;">No seats selected.</span>
        </div>

        <div class="fare-total">
            <span>Total Fare:</span>
            <span id="totalFare">₹0.00</span>
        </div>

        <form action="booking_form.php" method="POST" id="seatForm">
            <input type="hidden" name="schedule_id" value="<?= htmlspecialchars($schedule_id) ?>">
            <input type="hidden" name="seats" id="seatsInput" value="">
            <input type="hidden" name="total_amount" id="totalAmountInput" value="">
            <button type="submit" class="btn-proceed" id="btnProceed" disabled>Proceed to Book</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const seats = document.querySelectorAll('.seat:not(.booked)');
    const selectedSeatsList = document.getElementById('selectedSeatsList');
    const totalFareEl = document.getElementById('totalFare');
    const seatsInput = document.getElementById('seatsInput');
    const totalAmountInput = document.getElementById('totalAmountInput');
    const btnProceed = document.getElementById('btnProceed');
    
    // PHP variables provided to JS
    const maxSeats = <?= isset($_GET['passengers']) ? (int)$_GET['passengers'] : 6 ?>; 
    const pricePerSeat = <?= (float)$schedule['price'] ?>;
    let selectedSeats = [];

    seats.forEach(seat => {
        seat.addEventListener('click', () => {
            const seatNum = seat.dataset.seat;
            
            if (seat.classList.contains('selected')) {
                // Deselect
                seat.classList.remove('selected');
                selectedSeats = selectedSeats.filter(s => s !== seatNum);
            } else {
                // Select
                if (selectedSeats.length >= maxSeats) {
                    alert(`You can only select up to ${maxSeats} seats.`);
                    return;
                }
                seat.classList.add('selected');
                selectedSeats.push(seatNum);
            }
            updateSummary();
        });
    });

    function updateSummary() {
        if (selectedSeats.length === 0) {
            selectedSeatsList.innerHTML = '<span style="color:#999;font-size:14px;">No seats selected.</span>';
            totalFareEl.innerText = '₹0.00';
            btnProceed.disabled = true;
            seatsInput.value = '';
            totalAmountInput.value = '';
            return;
        }

        // Sort seats nicely
        selectedSeats.sort();
        
        selectedSeatsList.innerHTML = selectedSeats.map(s => `<span class="seat-badge">${s}</span>`).join('');
        
        const total = (selectedSeats.length * pricePerSeat).toFixed(2);
        totalFareEl.innerText = `₹${total}`;
        
        seatsInput.value = JSON.stringify(selectedSeats);
        totalAmountInput.value = total;
        btnProceed.disabled = false;
        
        // If user is not logged in, change button text to "Login to Proceed"
        const isLoggedIn = <?= $user_logged_in ? 'true' : 'false' ?>;
        if (!isLoggedIn) {
            btnProceed.innerText = 'Login to Proceed';
        } else {
            btnProceed.innerText = 'Proceed to Book';
        }
    }

    // Intercept form submission if not logged in
    document.getElementById('seatForm').addEventListener('submit', (e) => {
        const isLoggedIn = <?= $user_logged_in ? 'true' : 'false' ?>;
        if (!isLoggedIn) {
            e.preventDefault();
            document.getElementById('authModal').classList.add('active');
        }
    });

});
</script>

<?php include 'includes/footer.php'; ?>
