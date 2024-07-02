<?php
// Include the Database class
include 'Database.php';
$db = Database::getInstance();

// Get serviceId from the AJAX request
$serviceId = isset($_POST['serviceId']) ? $_POST['serviceId'] : null;

if ($serviceId) {
    // Fetch service details from the database
    $sql = "SELECT serviceList, price FROM dpProj_servicePackage WHERE packageId = ?";
    $service = $db->singleFetch($sql, [$serviceId]);

    if ($service) {
        echo '<h3>Service Details</h3>';
        echo '<p>' . htmlspecialchars($service->serviceList) . '</p>';
        echo '<p>Price: ' . htmlspecialchars($service->price) . ' BD</p>';
    } else {
        echo '<p>Service details not found.</p>';
    }
} else {
    echo '<p>No service ID received.</p>';
}
?>
