<?php
// Database connection
$host = "localhost";
$dbname = "elvia";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Function to get the nearest charging station
function getNearestStation($userLat, $userLon, $pdo) {
    // SQL query to get all charging stations
    $query = "SELECT id, name, latitude, longitude, address,
                     (6371 * acos(cos(radians(:lat)) * cos(radians(latitude)) * 
                     cos(radians(longitude) - radians(:lon)) + 
                     sin(radians(:lat)) * sin(radians(latitude)))) AS distance
              FROM charging_stations
              HAVING distance < 50 
              ORDER BY distance 
              LIMIT 1"; // Finding within 50 km radius

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':lat', $userLat);
    $stmt->bindParam(':lon', $userLon);
    $stmt->execute();

    // Fetch result
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result ? $result : null;
}

// Assume $userLat and $userLon are obtained from the user's device
$userLat = $_GET['lat']; // Example: latitude from frontend input
$userLon = $_GET['lon']; // Example: longitude from frontend input

if ($userLat && $userLon) {
    $station = getNearestStation($userLat, $userLon, $pdo);

    if ($station) {
        echo json_encode([
            'status' => 'success',
            'station' => $station
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No nearby charging stations found.'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'User location not provided.'
    ]);
}
?>
