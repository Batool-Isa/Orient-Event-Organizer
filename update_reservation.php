<?php
session_start();
ob_start(); // Start output buffering

include 'Database.php'; // Include your Database class

// Check if the user is logged in
$loggedIn = isset($_SESSION['userId']);
$userEmail = '';
$clientId = '';
$clientStatus = '';

if ($loggedIn) {
    $userId = $_SESSION['userId'];
    $db = Database::getInstance();
    $userQuery = "SELECT email FROM dbProj_User WHERE userId = ?";
    $userDetails = $db->singleFetch($userQuery, [$userId]);
    $userEmail = $userDetails->email;

    $clientQuery = "SELECT clientId, clientStatus FROM dbProj_Client WHERE userId = ?";
    $clientDetails = $db->singleFetch($clientQuery, [$userId]);
    $clientId = $clientDetails->clientId;
    $clientStatus = $clientDetails->clientStatus;
}

// Function to fetch reservation details
function fetchReservationDetails($reservationId, $clientId) {
    $db = Database::getInstance();
    $sql = "SELECT r.*, e.eventType, e.numberOfAudiance, e.numberOfDays, 
                   h.hallName, h.hallId
            FROM dbProj_Reservation r
            LEFT JOIN dbProj_event e ON r.eventId = e.eventId
            LEFT JOIN dbProj_Hall h ON r.hallId = h.hallId
            WHERE r.reservationId = ? AND r.clientId = ?";
    return $db->singleFetch($sql, [$reservationId, $clientId]);
}

// Function to fetch all event types
function fetchAllEventTypes() {
    $db = Database::getInstance();
    $sql = "SELECT eventId, eventType FROM dbProj_event";
    return $db->multiFetch($sql);
}

// Function to fetch all menus
function fetchAllMenus() {
    $db = Database::getInstance();
    $sql = "SELECT * FROM dbProj_Menu";
    return $db->multiFetch($sql);
}

// Function to fetch all service packages
function fetchAllServices() {
    $db = Database::getInstance();
    $sql = "SELECT * FROM dpProj_servicePackage";
    return $db->multiFetch($sql);
}

// Function to fetch selected catering details
function fetchCateringDetails($reservationId) {
    $db = Database::getInstance();
    $sql = "SELECT menuId, packageId FROM dbProj_Catering WHERE reservationId = ?";
    return $db->multiFetch($sql, [$reservationId]);
}

// Function to update reservation
function updateReservation($reservationId, $startDate, $endDate, $eventId, $hallId, $selectedMenus, $selectedServices) {
    $db = Database::getInstance();
    $db->querySQL("DELETE FROM dbProj_Catering WHERE reservationId = ?", [$reservationId]); // Clear existing catering details

    // Insert new catering details
    foreach ($selectedMenus as $menuId) {
        $db->querySQL("INSERT INTO dbProj_Catering (reservationId, menuId) VALUES (?, ?)", [$reservationId, $menuId]);
    }
    foreach ($selectedServices as $packageId) {
        $db->querySQL("INSERT INTO dbProj_Catering (reservationId, packageId) VALUES (?, ?)", [$reservationId, $packageId]);
    }

    $sql = "CALL UpdateReservation13(?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("issii", $reservationId, $startDate, $endDate, $eventId, $hallId);
        $stmt->execute();
        $stmt->bind_result($updatedTotalCost);
        $stmt->fetch();
        $stmt->close();
        return $updatedTotalCost;
    }
    return false;
}

// Function to check availability
function checkAvailability($reservationId, $startDate, $endDate, $hallId) {
    $db = Database::getInstance();
    $sql = "SELECT * FROM dbProj_Reservation WHERE hallId = ? AND reservationId != ? AND 
            ((startDate <= ? AND endDate >= ?) OR (startDate <= ? AND endDate >= ?))";
    $result = $db->multiFetch($sql, [$hallId, $reservationId, $startDate, $startDate, $endDate, $endDate]);

    return count($result) === 0;
}

// Calculate the end date based on the start date and duration
function calculateEndDate($startDate, $duration) {
    switch ($duration) {
        case '1':
            return $startDate;
        case '7':
            return date('Y-m-d', strtotime($startDate . ' + 6 days'));
        case '15':
            return date('Y-m-d', strtotime($startDate . ' + 14 days'));
    }
    return $startDate;
}

$reservation = null;
$successMessage = "";
$errors = [];

if (isset($_POST['fetch_details'])) {
    $reservationId = $_POST['reservationId'];
    $reservation = fetchReservationDetails($reservationId, $clientId);
    $allEventTypes = fetchAllEventTypes();
    $allMenus = fetchAllMenus();
    $allServices = fetchAllServices();
    $selectedCatering = fetchCateringDetails($reservationId);
    $selectedMenus = array_column($selectedCatering, 'menuId');
    $selectedServices = array_column($selectedCatering, 'packageId');
}

if (isset($_POST['update_reservation'])) {
    $reservationId = $_POST['reservationId'];
    $startDate = $_POST['startDate'];
    $duration = $_POST['duration'];
    $endDate = calculateEndDate($startDate, $duration);
    $eventId = $_POST['eventId'];
    $hallId = $_POST['hallId']; // Assuming you pass the hall ID as a hidden field
    $selectedMenus = isset($_POST['menuId']) ? $_POST['menuId'] : [];
    $selectedServices = isset($_POST['packageId']) ? $_POST['packageId'] : [];

    // Check availability
    if (checkAvailability($reservationId, $startDate, $endDate, $hallId)) {
        $updatedTotalCost = updateReservation($reservationId, $startDate, $endDate, $eventId, $hallId, $selectedMenus, $selectedServices);
        if ($updatedTotalCost !== false) {
            $successMessage = "Reservation updated successfully. New total cost: " . $updatedTotalCost;
        } else {
            $successMessage = "Error updating reservation.";
        }
    } else {
        $errors['availability'] = "The selected hall is not available for the chosen dates.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Reservation</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/jquery.mCustomScrollbar.min.css">
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container-custom {
            width: 50%;
            margin: 0 auto;
            text-align: center;
            padding: 20px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        h2 {
            color: #b22222;
        }

        .form-container {
            margin: 20px 0;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }

        input[type="number"],
        input[type="date"],
        input[type="text"],
        input[type="time"],
        select {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btn-custom {
            display: inline-block;
            padding: 10px 20px;
            margin: 5px;
            font-size: 16px;
            color: red;
            background-color: #333;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-custom:hover {
            background-color: #b22222;
        }

        .message {
            color: #b22222;
            font-weight: bold;
        }

        .error-message {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php include 'header.html'; ?>
    <div class="container-custom">
        <h2>Update Reservation</h2>
        <?php if ($successMessage): ?>
            <div class="message"><?php echo $successMessage; ?></div>
            <a href="manage_reservation.php" class="btn-custom">Back to Manage Reservation</a>
        <?php elseif ($reservation): ?>
            <form method="post" action="" class="form-container">
                <input type="hidden" name="reservationId" value="<?php echo $reservation->reservationId; ?>">
                <input type="hidden" name="hallId" value="<?php echo $reservation->hallId; ?>"> <!-- Pass the hall ID -->

                <label for="startDate">Start Date:</label>
                <input type="date" id="startDate" name="startDate" value="<?php echo $reservation->startDate; ?>" required><br><br>

                <label for="duration">Duration:</label>
                <input type="radio" id="duration1" name="duration" value="1" <?php echo ($reservation->numberOfDays == 1) ? 'checked' : ''; ?>>
                <label for="duration1">1 Day</label><br>
                <input type="radio" id="duration7" name="duration" value="7" <?php echo ($reservation->numberOfDays == 7) ? 'checked' : ''; ?>>
                <label for="duration7">1 Week</label><br>
                <input type="radio" id="duration15" name="duration" value="15" <?php echo ($reservation->numberOfDays == 15) ? 'checked' : ''; ?>>
                <label for="duration15">15 Days</label><br><br>

                <label for="eventId">Event Type:</label>
                <select id="eventId" name="eventId" required>
                    <?php foreach ($allEventTypes as $event) { ?>
                        <option value="<?php echo $event->eventId; ?>" <?php echo ($reservation->eventId == $event->eventId) ? 'selected' : ''; ?>><?php echo $event->eventType; ?></option>
                    <?php } ?>
                </select><br><br>

                <h3>Select Menus</h3>
                <?php foreach ($allMenus as $menu) { ?>
                    <input type="checkbox" id="menu_<?php echo $menu->menuId; ?>" name="menuId[]" value="<?php echo $menu->menuId; ?>"
                        <?php echo in_array($menu->menuId, $selectedMenus) ? 'checked' : ''; ?>>
                    <label for="menu_<?php echo $menu->menuId; ?>"><?php echo $menu->menuName . " - $" . $menu->price; ?></label><br>
                <?php } ?>

                <h3>Select Services</h3>
                <?php foreach ($allServices as $service) { ?>
                    <input type="checkbox" id="service_<?php echo $service->packageId; ?>" name="packageId[]" value="<?php echo $service->packageId; ?>"
                        <?php echo in_array($service->packageId, $selectedServices) ? 'checked' : ''; ?>>
                    <label for="service_<?php echo $service->packageId; ?>"><?php echo $service->packageName . " - $" . $service->price; ?></label><br>
                <?php } ?>

                <input type="submit" name="update_reservation" value="Update Reservation" class="btn-custom">
            </form>
        <?php else: ?>
            <form method="post" action="" class="form-container">
                <label for="reservationId">Reservation ID:</label>
                <input type="number" id="reservationId" name="reservationId" required><br><br>
                <input type="submit" name="fetch_details" value="Get Details" class="btn-custom">
            </form>
        <?php endif; ?>
    </div>
    <?php include 'footer.html'; ?>
</body>
</html>

<?php
ob_end_flush(); // Send the output to the browser
?>

