<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'Database.php';

// Function to send confirmation email
function sendConfirmationEmail($email, $reservationId, $invoiceId, $reservationSummary) {
    $subject = "Reservation Confirmation";
    $message = "Thank you for your reservation. Here are your details:\n\n" . 
               "Reservation ID: $reservationId\n" . 
               "Invoice ID: $invoiceId\n" . 
               "$reservationSummary";
    $headers = "From: no-reply@orienteventorganizer.com";

    mail($email, $subject, $message, $headers);
}

// Fetch user details if logged in
if (isset($_SESSION['userId'])) {
    $userId = $_SESSION['userId'];
    $db = Database::getInstance();

    // Fetch the email of the logged-in user
    $userQuery = "SELECT email FROM dbProj_User WHERE userId = ?";
    $userDetails = $db->singleFetch($userQuery, [$userId]);
    if ($userDetails) {
        $userEmail = $userDetails->email;
    } else {
        die('Error: Could not fetch user email.');
    }
} else {
    die('Error: User not logged in.');
}

// Retrieve payment details
$reservationId = $_GET['reservationId'];
$invoiceId = $_GET['invoiceId'];

$sql = "SELECT * FROM dpProj_Inovice WHERE invoiceId = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param('i', $invoiceId);
$stmt->execute();
$result = $stmt->get_result();
$invoice = $result->fetch_object();

$sql_payment = "SELECT * FROM dpProj_Payment WHERE paymentId = ?";
$stmt_payment = $db->prepare($sql_payment);
$stmt_payment->bind_param('i', $invoice->paymentId);
$stmt_payment->execute();
$result_payment = $stmt_payment->get_result();
$payment = $result_payment->fetch_object();

$sql_reservation = "SELECT * FROM dbProj_Reservation WHERE reservationId = ?";
$stmt_reservation = $db->prepare($sql_reservation);
$stmt_reservation->bind_param('i', $payment->reservationId);
$stmt_reservation->execute();
$result_reservation = $stmt_reservation->get_result();
$reservation = $result_reservation->fetch_object();

// Prepare reservation summary
$reservationSummary = "Amount: {$invoice->amount} BD\n" . 
                      "Date: {$invoice->date}\n" . 
                      "Payment Type: {$payment->paymentType}\n" . 
                      "Card Holder Name: {$payment->cardHolderName}\n" . 
                      "Billing Address: {$payment->billingAddress}\n";

// Send confirmation email
sendConfirmationEmail($userEmail, $reservationId, $invoiceId, $reservationSummary);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reservation Summary</title>
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
        <h1>Invoice</h1>
        <p>Thank you for your reservation. Here are your details:</p>
        <p>Invoice ID: <?php echo htmlspecialchars($invoice->invoiceId); ?></p>
        <p>Amount: <?php echo htmlspecialchars($invoice->amount); ?> BD</p>
        <p>Date: <?php echo htmlspecialchars($invoice->date); ?></p>
        <p>Reservation ID: <?php echo htmlspecialchars($reservation->reservationId); ?></p>
        <p>Payment Type: <?php echo htmlspecialchars($payment->paymentType); ?></p>
        <p>Card Holder Name: <?php echo htmlspecialchars($payment->cardHolderName); ?></p>
        <p>Billing Address: <?php echo htmlspecialchars($payment->billingAddress); ?></p>
        <div class="form-buttons">
            <input type="button" value="Back to Home" onclick="window.location.href='index.php';">
        </div>
    </div>

    <footer>
        <?php include 'footer.html'; ?>
    </footer>
</body>
</html>


