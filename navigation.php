<?php
// Database connection
$host = 'localhost';
$dbname = 'elvia_db'; // Replace with your database name
$username = 'root'; // Replace with your username
$password = ''; // Replace with your password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Function to calculate nearest charging station
function getNearestStation($userLat, $userLon, $pdo) {
    // Haversine formula to calculate distance
    $query = "
        SELECT id, name, latitude, longitude, address,
        (6371 * acos(
            cos(radians(:userLat)) *
            cos(radians(latitude)) *
            cos(radians(longitude) - radians(:userLon)) +
            sin(radians(:userLat)) *
            sin(radians(latitude))
        )) AS distance
        FROM charging_stations
        HAVING distance <= 50 -- Limit to stations within 50 km
        ORDER BY distance ASC
        LIMIT 1"; // Return the nearest station

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':userLat', $userLat, PDO::PARAM_STR);
    $stmt->bindParam(':userLon', $userLon, PDO::PARAM_STR);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Check for user latitude and longitude from GET request
if (isset($_GET['lat']) && isset($_GET['lon'])) {
    $userLat = $_GET['lat'];
    $userLon = $_GET['lon'];

    $nearestStation = getNearestStation($userLat, $userLon, $pdo);

    if ($nearestStation) {
        echo json_encode([
            'status' => 'success',
            'station' => $nearestStation
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No charging stations found nearby.'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Latitude and longitude are required.'
    ]);
}
?>
