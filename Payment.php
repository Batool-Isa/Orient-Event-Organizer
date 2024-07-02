<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'Database.php';

// Function to send confirmation email
function sendConfirmationEmail($email, $reservationId, $reservationSummary) {
    $subject = "Reservation Confirmation";
    $message = "Thank you for your reservation. Your reservation ID is: $reservationId\n\n$reservationSummary";
    $headers = "From: no-reply@orienteventorganizer.com";

    mail($email, $subject, $message, $headers);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    $db = Database::getInstance()->getConnection();

    // Retrieve reservation details
    $hallId = $_POST['hallId'];
    $hallName = $_POST['hallName'];
    $startDate = $_POST['start_date'];
    $duration = $_POST['duration'];
    $endDate = $_POST['end_date'];
    $audience = $_POST['audience'];
    $time = $_POST['time'];
    $hallImage = $_POST['hallImage'];
    $rentalDetails = $_POST['rentalDetails'];
    $totalPrice = $_POST['totalPrice'];
    $discountedPrice = $_POST['discountedPrice'];
    $discountRate = $_POST['discountRate']; // Get discountRate from POST
    $clientId = isset($_POST['clientId']) ? $_POST['clientId'] : null;
    $clientStatus = isset($_POST['clientStatus']) ? $_POST['clientStatus'] : null;
    $companyName = $_POST['companyName'];
    $email = $_POST['email'];

    // Payment details
    $paymentType = $_POST['paymentType'];
    $cardDetails = $_POST['cardDetails'];
    $cardHolderName = $_POST['cardHolderName'];
    $billingAddress = $_POST['billingAddress'];

    // Check if clientId exists in dbProj_Client
    $clientExistsQuery = "SELECT COUNT(*) FROM dbProj_Client WHERE clientId = ?";
    $stmt = $db->prepare($clientExistsQuery);
    if ($stmt === false) {
        die("Prepare failed: " . $db->error);
    }

    $stmt->bind_param("i", $clientId);
    $stmt->execute();
    $stmt->bind_result($clientExists);
    $stmt->fetch();
    $stmt->close();

    if ($clientExists == 0) {
        die("Client ID does not exist in dbProj_Client.");
    }

    // Insert event
    $eventType = '';
    if (strpos($hallName, 'Seminar Hall') !== false) {
        $eventType = 'Seminar';
    } elseif (strpos($hallName, 'Small Hall') !== false) {
        $eventType = 'Workshop';
    } elseif (strpos($hallName, 'Lab') !== false) {
        $eventType = 'Training';
    }
    $numberOfAudiance = $audience;
    $numberOfDays = $duration;

    // Insert event details into the database
    $stmt = $db->prepare("INSERT INTO dbProj_event (eventType, numberOfAudiance, numberOFDays) VALUES (?, ?, ?)");
    if ($stmt === false) {
        die("Prepare failed: " . $db->error);
    }

    if (!$stmt->bind_param("sii", $eventType, $numberOfAudiance, $numberOfDays)) {
        die("Bind failed: " . $stmt->error);
    }

    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }

    $eventId = $stmt->insert_id; // Get the inserted event ID
    $stmt->close();

    $reservationDate = date('Y-m-d');
    // Get timing ID based on hallId and time
    $timingQuery = "SELECT timingID FROM dpProj_HallsTimingSlots WHERE hallId = ? AND timingSlotStart <= ? AND timingSlotEnd >= ?";
    $stmt = $db->prepare($timingQuery);
    if ($stmt === false) {
        die("Prepare failed: " . $db->error);
    }

    $stmt->bind_param("iss", $hallId, $time, $time);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $timingID = $row['timingID'];
    } else {
        die('Error: Invalid timing ID.');
    }
    $stmt->close();

    // Insert reservation details into the database
    $stmt = $db->prepare("INSERT INTO dbProj_Reservation (reservationDate, startDate, endDate, timingID, totalCost, discountRate, clientId, hallId, eventId) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        die("Prepare failed: " . $db->error);
    }

    if (!$stmt->bind_param("sssiddiii", $reservationDate, $startDate, $endDate, $timingID, $discountedPrice, $discountRate, $clientId, $hallId, $eventId)) {
        die("Bind failed: " . $stmt->error);
    }

    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }

    $reservationId = $stmt->insert_id; // Get the inserted reservation ID
    $stmt->close();

    // Insert catering details into the database if menus or services were selected
    $stmt = $db->prepare("INSERT INTO dbProj_Catering (reservationId, menuId, packageId) VALUES (?, ?, ?)");
    if ($stmt === false) {
        die("Prepare failed: " . $db->error);
    }

    foreach ($_POST['selectedMenus'] as $menuId) {
        if ($menuId != '') {
            $packageId = null; // No service package in this case

            if (!$stmt->bind_param("iii", $reservationId, $menuId, $packageId)) {
                die("Bind failed: " . $stmt->error);
            }

            if (!$stmt->execute()) {
                die("Execute failed: " . $stmt->error);
            }
        }
    }

    foreach ($_POST['selectedServices'] as $packageId) {
        if ($packageId != '') {
            $menuId = null; // No menu in this case

            if (!$stmt->bind_param("iii", $reservationId, $menuId, $packageId)) {
                die("Bind failed: " . $stmt->error);
            }

            if (!$stmt->execute()) {
                die("Execute failed: " . $stmt->error);
            }
        }
    }

    $stmt->close();

    // Insert payment details into the database
    $stmt = $db->prepare("INSERT INTO dpProj_Payment (paymentType, cardDetails, cardHolderName, billingAddress, reservationId) VALUES (?, ?, ?, ?, ?)");
    if ($stmt === false) {
        die("Prepare failed: " . $db->error);
    }

    if (!$stmt->bind_param("ssssi", $paymentType, $cardDetails, $cardHolderName, $billingAddress, $reservationId)) {
        die("Bind failed: " . $stmt->error);
    }

    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }

    $paymentId = $stmt->insert_id;
    $stmt->close();

    // Create invoice
    $stmt = $db->prepare("INSERT INTO dpProj_Inovice (amount, date, paymentId) VALUES (?, ?, ?)");
    if ($stmt === false) {
        die("Prepare failed: " . $db->error);
    }

    $amount = $discountedPrice;
    $date = date('Y-m-d');

    if (!$stmt->bind_param("dsi", $amount, $date, $paymentId)) {
        die("Bind failed: " . $stmt->error);
    }

    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }
    $invoiceId = $stmt->insert_id;
    $stmt->close();

    // Send confirmation email
    $reservationSummary = "Hall Name: $hallName\nStart Date: $startDate\nEnd Date: $endDate\nDuration: $duration days\nNumber of Audience: $audience\nTime: $time\nRental Details: $rentalDetails BD\nTotal Price: $totalPrice BD\nDiscounted Price: $discountedPrice BD\nCompany Name: $companyName";
    sendConfirmationEmail($email, $reservationId, $reservationSummary);

    
    header('Location: finalize_reservation.php?reservationId=' . $reservationId . '&invoiceId=' . $invoiceId);

    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0;
            min-height: 100vh;
            background-color: white;
            color: black;
        }
        .container {
            text-align: center;
            max-width: 800px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .form-buttons input {
            padding: 10px 20px;
            margin-right: 10px;
            background-color: black;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-transform: uppercase;
        }
        .form-buttons input:hover {
            background-color: red;
            color: white;
        }
        .form-buttons {
            margin-top: 20px;
        }
        .form-buttons form {
            display: inline-block;
        }
        header, footer {
            width: 100%;
            background-color: #f8f8f8;
            padding: 10px 0;
        }
        footer {
            margin-top: auto;
        }
        h1 {
            font-size: 2em;
            text-align: center;
            margin-bottom: 20px;
        }
        p, h2, h3 {
            color: black; /* Ensure all text remains black */
        }
    </style>
</head>
<body>
    <header>
        <?php include 'header.html'; ?>
    </header>

    <div class="container">
        <h1>Payment</h1>
        <h2>Reservation Summary</h2>
        <p>Total Price: <?php echo htmlspecialchars($_POST['discountedPrice']); ?> BD</p>

        <form action="Payment.php" method="post">
            <label for="paymentType">Payment Type:</label>
            <input type="text" name="paymentType" required><br>

            <label for="cardDetails">Card Details:</label>
            <input type="text" name="cardDetails" required><br>

            <label for="cardHolderName">Card Holder Name:</label>
            <input type="text" name="cardHolderName" required><br>

            <label for="billingAddress">Billing Address:</label>
            <input type="text" name="billingAddress" required><br>

            <!-- Pass reservation details -->
            <input type="hidden" name="hallId" value="<?php echo htmlspecialchars($_POST['hallId']); ?>">
            <input type="hidden" name="hallName" value="<?php echo htmlspecialchars($_POST['hallName']); ?>">
            <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($_POST['start_date']); ?>">
            <input type="hidden" name="duration" value="<?php echo htmlspecialchars($_POST['duration']); ?>">
            <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($_POST['end_date']); ?>">
            <input type="hidden" name="audience" value="<?php echo htmlspecialchars($_POST['audience']); ?>">
            <input type="hidden" name="time" value="<?php echo htmlspecialchars($_POST['time']); ?>">
            <input type="hidden" name="hallImage" value="<?php echo htmlspecialchars($_POST['hallImage']); ?>">
            <input type="hidden" name="rentalDetails" value="<?php echo htmlspecialchars($_POST['rentalDetails']); ?>">
            <input type="hidden" name="totalPrice" value="<?php echo htmlspecialchars($_POST['totalPrice']); ?>">
            <input type="hidden" name="discountedPrice" value="<?php echo htmlspecialchars($_POST['discountedPrice']); ?>">
            <input type="hidden" name="discountRate" value="<?php echo htmlspecialchars($_POST['discountRate']); ?>"> <!-- Add discountRate -->
            <input type="hidden" name="clientId" value="<?php echo htmlspecialchars($_POST['clientId']); ?>">
            <input type="hidden" name="clientStatus" value="<?php echo htmlspecialchars($_POST['clientStatus']); ?>">
            <input type="hidden" name="companyName" value="<?php echo htmlspecialchars($_POST['companyName']); ?>">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($_POST['email']); ?>">
            <input type="hidden" name="selectedMenus" value="<?php echo htmlspecialchars($_POST['selectedMenus']); ?>">
            <input type="hidden" name="selectedServices" value="<?php echo htmlspecialchars($_POST['selectedServices']); ?>">

            <div class="form-buttons">
                <input type="submit" name="confirm_payment" value="Confirm Payment">
                <input type="button" value="Cancel" onclick="window.location.href='index.php';">
            </div>
        </form>
    </div>

    <footer>
        <?php include 'footer.html'; ?>
    </footer>
</body>
</html>
