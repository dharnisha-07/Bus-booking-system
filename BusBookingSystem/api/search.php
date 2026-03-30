<?php
require_once '../db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$source = isset($input['source']) ? trim($input['source']) : '';
$destination = isset($input['destination']) ? trim($input['destination']) : '';
$date = isset($input['date']) ? trim($input['date']) : '';
$types = isset($input['types']) ? $input['types'] : [];
$times = isset($input['times']) ? $input['times'] : [];

if (empty($source) || empty($destination) || empty($date)) {
    echo json_encode(['success' => false, 'message' => 'Source, destination, and date are required.']);
    exit;
}

$query = "
    SELECT 
        s.id as schedule_id,
        b.bus_name,
        b.bus_type,
        b.amenities,
        r.source_city,
        r.destination_city,
        s.departure_time,
        s.arrival_time,
        DATE_FORMAT(s.departure_time, '%h:%i %p') as dep_time,
        DATE_FORMAT(s.arrival_time, '%h:%i %p') as arr_time,
        s.available_seats,
        s.price
    FROM schedules s
    JOIN buses b ON s.bus_id = b.id
    JOIN routes r ON s.route_id = r.id
    WHERE r.source_city LIKE :source 
      AND r.destination_city LIKE :destination
      AND s.travel_date = :date
";

$params = [
    ':source' => "%$source%",
    ':destination' => "%$destination%",
    ':date' => $date
];

// Add Type Filters
if (!empty($types)) {
    $placeholders = [];
    foreach ($types as $i => $type) {
        $p = ":type$i";
        $placeholders[] = $p;
        $params[$p] = $type;
    }
    $query .= " AND b.bus_type IN (" . implode(',', $placeholders) . ")";
}

// Add Time Filters
if (!empty($times)) {
    $timeConditions = [];
    foreach ($times as $time) {
        if ($time === 'Morning') {
            $timeConditions[] = "(HOUR(s.departure_time) >= 6 AND HOUR(s.departure_time) < 12)";
        } elseif ($time === 'Afternoon') {
            $timeConditions[] = "(HOUR(s.departure_time) >= 12 AND HOUR(s.departure_time) < 18)";
        } elseif ($time === 'Night') {
            $timeConditions[] = "(HOUR(s.departure_time) >= 18 OR HOUR(s.departure_time) < 6)";
        }
    }
    if (!empty($timeConditions)) {
        $query .= " AND (" . implode(' OR ', $timeConditions) . ")";
    }
}

$query .= " ORDER BY s.departure_time ASC";

try {
    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->execute();
    $results = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $results
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
