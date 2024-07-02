<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include necessary files
require_once 'Database.php';
require_once 'Search.php';

// Start session to get client ID
// session_start();

// Debugging: Check if session is working and client ID is set
// if (!isset($_SESSION['client_id'])) {
//     die('Client ID not set in session.');
// }

// Check if the form is submitted via POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the input values
    $startDate = $_POST['start_date'];
    $duration = $_POST['duration'];
    $endDate = $_POST['end_date'];
    $audience = $_POST['audience'];
    $time = $_POST['time'];
    $hallId = $_POST['hall'];

    // Debugging: Print form data
    echo '<pre>';
    print_r($_POST);
    echo '</pre>';

    // Hardcode the client ID for testing
    $clientId = 1;

    // Validate input
    if (empty($startDate) || empty($duration) || empty($endDate) || empty($audience) || empty($time) || empty($hallId)) {
        die("All fields are required.");
    }

    // Create an instance of the Search class
    $searchClass = new Search();

    try {
        // Check availability
        $availability = $searchClass->searchHalls($startDate, $duration, $audience, $time, null);
        if (empty($availability)) {
            // Hall is available, proceed with booking
            $db = Database::getInstance();
            $dblink = $db->dblink;

            // Start transaction
            $dblink->begin_transaction();

            // Insert new event
            $eventSql = "INSERT INTO dbProj_event (eventType, numberOfAudiance, numberOFDays) VALUES ('Custom Event', ?, ?)";
            $eventStmt = $dblink->prepare($eventSql);
            if (!$eventStmt) {
                throw new Exception("Event prepare failed: (" . $dblink->errno . ") " . $dblink->error);
            }
            if (!$eventStmt->bind_param('ii', $audience, $duration)) {
                throw new Exception("Event binding parameters failed: (" . $eventStmt->errno . ") " . $eventStmt->error);
            }
            if (!$eventStmt->execute()) {
                throw new Exception("Event execute failed: (" . $eventStmt->errno . ") " . $eventStmt->error);
            } 
            $eventId = $eventStmt->insert_id;
            $eventStmt->close();

            // Insert new reservation
            $reservationSql = "INSERT INTO dbProj_Reservation (reservationDate, startDate, endDate, timingID, totalCost, discountRate, clientId, hallId, eventId)
                               VALUES (NOW(), ?, ?, ?, NULL, NULL, ?, ?, ?)";
            $reservationStmt = $dblink->prepare($reservationSql);
            if (!$reservationStmt) {
                throw new Exception("Reservation prepare failed: (" . $dblink->errno . ") " . $dblink->error);
            }
            if (!$reservationStmt->bind_param('sssiii', $startDate, $endDate, $time, $clientId, $hallId, $eventId)) {
                throw new Exception("Reservation binding parameters failed: (" . $reservationStmt->errno . ") " . $reservationStmt->error);
            }
            if (!$reservationStmt->execute()) {
                throw new Exception("Reservation execute failed: (" . $reservationStmt->errno . ") " . $reservationStmt->error);
            }
            $reservationStmt->close();

            // Commit transaction
            $dblink->commit();

            echo "Booking successful.";
        } else {
            echo "The hall is not available for the selected date and time.";
        }
    } catch (Exception $e) {
        // Rollback transaction in case of error
        $dblink->rollback();
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}
?>
