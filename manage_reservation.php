<?php
session_start();
include 'Database.php'; // Include your Database class

// Check if the user is logged in
$loggedIn = isset($_SESSION['userId']);
$userEmail = '';
$clientId = '';
$clientStatus = '';

// Fetch user details if logged in
if ($loggedIn) {
    $userId = $_SESSION['userId'];
    $db = Database::getInstance();
    $userQuery = "SELECT email FROM dbProj_User WHERE userId = ?";
    $userDetails = $db->singleFetch($userQuery, [$userId]);
    $userEmail = $userDetails->email;

    // Fetch client details
    $clientQuery = "SELECT clientId, clientStatus FROM dbProj_Client WHERE userId = ?";
    $clientDetails = $db->singleFetch($clientQuery, [$userId]);
    $clientId = $clientDetails->clientId;
    $clientStatus = $clientDetails->clientStatus;
}

// Function to fetch reservation details
function fetchReservationDetails($reservationId, $clientId) {
    $db = Database::getInstance();
    $sql = "SELECT * FROM dbProj_Reservation WHERE reservationId = ? AND clientId = ?";
    return $db->singleFetch($sql, [$reservationId, $clientId]);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Reservation</title>
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
        input[type="password"] {
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
            color: #ADD8E6;
            background-color: #333;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-custom:hover {
            background-color: #b22222;
        }

        .reservation-details {
            text-align: left;
            margin-top: 20px;
        }

        .message {
            color: #b22222;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include 'header.html'; ?>
    <div class="container-custom">
        <h2>Manage Reservation</h2>
        <form action="" method="post" class="form-container">
            <label for="reservationId">Reservation ID:</label>
            <input type="number" id="reservationId" name="reservationId" required><br><br>
            <input type="submit" name="fetch_details" value="Get Details" class="btn-custom">
        </form>

        <?php
        if (isset($_POST['fetch_details'])) {
            $reservationId = $_POST['reservationId'];
            if ($loggedIn) {
                $reservation = fetchReservationDetails($reservationId, $clientId);

                if ($reservation) {
                    echo "<div class='reservation-details'>";
                    echo "<h3>Reservation Details</h3>";
                    echo "Reservation ID: " . $reservation->reservationId . "<br>";
                    echo "Start Date: " . $reservation->startDate . "<br>";
                    echo "End Date: " . $reservation->endDate . "<br>";
                    echo "Total Cost: " . $reservation->totalCost . "<br>";
                    
                    echo '<form action="update_reservation.php" method="post" style="display:inline-block">';
                    echo '<input type="hidden" name="reservationId" value="' . $reservationId . '">';
                    echo '<input type="submit" value="Update Reservation" class="btn-custom">';
                    echo '</form>';
                    
                    echo '<form action="cancel_reservation.php" method="post" style="display:inline-block">';
                    echo '<input type="hidden" name="reservationId" value="' . $reservationId . '">';
                    echo '<input type="submit" value="Cancel Reservation" class="btn-custom">';
                    echo '</form>';
                    echo "</div>";
                } else {
                    echo "<div class='message'>No reservation found with ID " . $reservationId . " for the logged-in user.</div>";
                }
            } else {
                echo "<div class='message'>Please log in to manage your reservations.</div>";
            }
        }
        ?>
    </div>
    <?php include 'footer.html'; ?>
</body>
</html>

