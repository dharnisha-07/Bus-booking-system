<?php 
require_once 'db.php';
include 'includes/header.php';

$featured_stmt = $pdo->query("
    SELECT s.id, s.travel_date, b.bus_name, b.bus_type, r.source_city, r.destination_city, s.price
    FROM schedules s
    JOIN buses b ON s.bus_id = b.id
    JOIN routes r ON s.route_id = r.id
    ORDER BY b.id ASC
    LIMIT 3
");
$featuredSchedules = $featured_stmt->fetchAll();
?>

<!-- Hero Section -->
<section class="hero">
    <h1>Book Your Journey with Ease</h1>
    <p>Comfortable buses, reliable schedules, and the best prices.</p>
    
    <div class="search-container">
        <form class="search-form" action="search_results.php" method="GET">
            <div class="form-group">
                <label>From</label>
                <input type="text" name="source" placeholder="Source City" required>
            </div>
            <div class="form-group">
                <label>To</label>
                <input type="text" name="destination" placeholder="Destination City" required>
            </div>
            <div class="form-group">
                <label>Date of Journey</label>
                <input type="date" name="date" required min="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group">
                <label>Passengers</label>
                <input type="number" name="passengers" min="1" max="6" value="1" required>
            </div>
            <button type="submit" class="btn-search">Search Buses</button>
        </form>
    </div>
</section>

<!-- Featured Routes -->
<section id="routes" class="routes-section">
    <h2>Featured Routes</h2>
    <div class="routes-grid">
        <?php if(count($featuredSchedules) > 0): ?>
            <?php foreach($featuredSchedules as $fs): ?>
            <div class="route-card">
                <div class="route-image" style="background-color: var(--primary-color);">
                    <i class="fa-solid fa-bus"></i>
                </div>
                <div class="route-info">
                    <h3><?= htmlspecialchars($fs['source_city']) ?> <span><i class="fa-solid fa-arrow-right"></i></span> <?= htmlspecialchars($fs['destination_city']) ?></h3>
                    <p><?= htmlspecialchars($fs['bus_name']) ?> (<?= htmlspecialchars($fs['bus_type']) ?>)</p>
                    <p style="margin-top:5px; font-weight:bold; color:var(--secondary-color);">Starts from ₹<?= htmlspecialchars($fs['price']) ?></p>
                    <a href="search_results.php?source=<?= urlencode($fs['source_city']) ?>&destination=<?= urlencode($fs['destination_city']) ?>&date=<?= urlencode($fs['travel_date']) ?>" class="btn-book">Book Now</a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center; grid-column: 1/-1;">No featured routes available at the moment.</p>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
