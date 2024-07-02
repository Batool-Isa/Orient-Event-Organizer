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

// Function to cancel reservation
function cancelReservation($reservationId, $clientId) {
    $db = Database::getInstance();
    $sql = "CALL CancelReservation(?, ?, @message)";
    $stmt = $db->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ii", $reservationId, $clientId);
        if (!$stmt->execute()) {
            return "Execution failed: " . $stmt->error;
        }
        $stmt->close();

        // Fetch the output message
        $result = $db->querySQL("SELECT @message AS message");
        if ($result) {
            $row = $result->get_result()->fetch_assoc();
            return $row['message'];
        } else {
            return "Failed to fetch message: " . $db->dblink->error;
        }
    } else {
        return "Failed to prepare statement: " . $db->dblink->error;
    }
}

// Check if the reservation can be canceled
function canCancelReservation($reservationId, $clientId) {
    $db = Database::getInstance();
    $sql = "SELECT startDate FROM dbProj_Reservation WHERE reservationId = ? AND clientId = ?";
    $reservation = $db->singleFetch($sql, [$reservationId, $clientId]);

    if ($reservation) {
        $startDate = $reservation->startDate;
        $today = date('Y-m-d');
        $twoDaysBeforeStart = date('Y-m-d', strtotime($startDate . ' -2 days'));

        return $today <= $twoDaysBeforeStart;
    }

    return false;
}

$successMessage = "";
if ($loggedIn && isset($_POST['confirm_cancel'])) {
    $reservationId = $_POST['reservationId'];
    if (canCancelReservation($reservationId, $clientId)) {
        $message = cancelReservation($reservationId, $clientId);
        if ($message) {
            $successMessage = $message;
        } else {
            $successMessage = "Error canceling reservation.";
        }
    } else {
        $successMessage = "Cannot cancel reservation within 2 days of the event start date.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cancel Reservation</title>
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

        .message {
            color: #b22222;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include 'header.html'; ?>
    <div class="container-custom">
        <h2>Cancel Reservation</h2>

        <?php if ($successMessage) { ?>
            <div class='message'><?php echo $successMessage; ?></div>
            <a href="manage_reservation.php" class="btn-custom">Back to Manage Reservation</a>
        <?php } elseif ($loggedIn && isset($_POST['reservationId'])) { ?>
            <form action="" method="post" class="form-container">
                <input type="hidden" name="reservationId" value="<?php echo $_POST['reservationId']; ?>">
                <p>Are you sure you want to cancel the reservation?</p>
                <input type="submit" name="confirm_cancel" value="Yes, Cancel Reservation" class="btn-custom">
                <a href="manage_reservation.php" class="btn-custom">No, Go Back</a>
            </form>
        <?php } else { ?>
            <div class='message'>Please log in to cancel your reservations.</div>
        <?php } ?>
    </div>
    <?php include 'footer.html'; ?>
</body>
</html>

