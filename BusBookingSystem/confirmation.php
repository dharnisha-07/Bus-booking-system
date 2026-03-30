<?php
include 'includes/header.php';

$booking_ref = $_GET['booking_ref'] ?? '';
?>

<style>
/* Peak Level Animation Styles */
body {
    overflow-x: hidden;
}

.confirmation-screen {
    position: relative;
    width: 100%;
    height: 80vh;
    background: linear-gradient(to bottom, #87CEEB 0%, #E0F7FA 100%);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    overflow: hidden;
}

.road {
    position: absolute;
    bottom: 0;
    width: 100%;
    height: 120px;
    background: #333;
    border-top: 5px solid #666;
}

.road::after {
    content: '';
    position: absolute;
    top: 50%;
    width: 200%;
    height: 10px;
    background: repeating-linear-gradient(90deg, transparent 0, transparent 40px, #fff 40px, #fff 80px);
    animation: roadMove 1s linear infinite;
}

@keyframes roadMove {
    0% { transform: translateX(0); }
    100% { transform: translateX(-80px); }
}

/* Bus Animation */
.bus-animation-container {
    position: absolute;
    bottom: 20px;
    left: -300px;
    animation: busArrive 3s cubic-bezier(0.25, 1, 0.5, 1) forwards;
    z-index: 10;
}

.bus-sprite {
    font-size: 150px;
    color: var(--secondary-color);
    text-shadow: 2px 5px 15px rgba(0,0,0,0.3);
}

@keyframes busArrive {
    0% { left: -300px; transform: rotate(-2deg); }
    60% { left: 40%; transform: rotate(1deg); }
    80% { left: 50%; transform: rotate(-1deg); }
    90% { left: 50%; transform: rotate(0.5deg); }
    100% { left: 50%; transform: rotate(0) translateX(-50%); }
}

/* Shake effect for screen */
.shake-screen {
    animation: screenShake 0.4s ease-in-out 3s forwards;
}

@keyframes screenShake {
    0% { transform: translate(1px, 1px) rotate(0deg); }
    20% { transform: translate(-3px, 0px) rotate(1deg); }
    40% { transform: translate(1px, -2px) rotate(-1deg); }
    60% { transform: translate(-3px, 1px) rotate(0deg); }
    80% { transform: translate(3px, 1px) rotate(-1deg); }
    100% { transform: translate(0, 0) rotate(0deg); }
}

/* Passenger Animation */
.passenger {
    position: absolute;
    bottom: 40px;
    right: -100px;
    font-size: 60px;
    color: #333;
    animation: passengerWalk 2s ease-out 3.5s forwards;
    opacity: 0;
}

@keyframes passengerWalk {
    0% { right: -100px; opacity: 1; }
    100% { right: calc(50% - 120px); opacity: 1; }
}

/* Success Message Float Up */
.success-message {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 30px 50px;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
    z-index: 20;
    opacity: 0;
    animation: messageReveal 1s cubic-bezier(0.175, 0.885, 0.32, 1.275) 5s forwards;
}

@keyframes messageReveal {
    0% { opacity: 0; margin-top: 50px; transform: translate(-50%, -50%) scale(0.8); }
    100% { opacity: 1; margin-top: 0; transform: translate(-50%, -50%) scale(1); }
}

.success-icon {
    font-size: 60px;
    color: #4caf50;
    margin-bottom: 20px;
}
</style>

<div class="confirmation-screen" id="mainScreen">
    <div class="road" id="roadElement"></div>
    
    <div class="bus-animation-container">
        <i class="fa-solid fa-bus bus-sprite"></i>
    </div>
    
    <div class="passenger">
        <i class="fa-solid fa-person-walking-luggage"></i>
    </div>

    <div class="success-message">
        <i class="fa-solid fa-circle-check success-icon"></i>
        <h1 style="color:var(--primary-color); margin-bottom:10px;">Booking Confirmed!</h1>
        <p style="color:#666; margin-bottom: 20px;">Your bus is arriving soon. Ref: <strong><?= htmlspecialchars($booking_ref) ?></strong></p>
        <div style="display:flex; gap:15px; justify-content:center;">
            <a href="ticket.php?booking_ref=<?= urlencode($booking_ref) ?>" class="btn-search" style="text-decoration:none;">View Ticket</a>
            <a href="dashboard.php" class="btn-book" style="padding: 12px 30px;">Go to Dashboard</a>
        </div>
    </div>
</div>

<script>
// Stop road movement after bus arrives
setTimeout(() => {
    document.getElementById('roadElement').style.animation = 'none';
    document.getElementById('mainScreen').classList.add('shake-screen');
}, 3000); // 3s matches bus arrival
</script>

<?php include 'includes/footer.php'; ?>
