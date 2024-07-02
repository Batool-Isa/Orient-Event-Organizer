<!-- Shooq -->
<?php
session_start();
include 'Database.php'; // Your database connection file

// Retrieve the reservation code from the query string
$reservation_code = isset($_GET['code']) ? $_GET['code'] : '';
if (!$reservation_code) {
    die("Reservation code is required.");
}

// Fetch reservation details
$stmt = $pdo->prepare("SELECT * FROM dbProj_Reservation WHERE reservationId = :code");
$stmt->execute(['code' => $reservation_code]);
$reservation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reservation) {
    die("Reservation not found.");
}

// Fetch invoice details
$stmt = $pdo->prepare("SELECT * FROM dbProj_Inovice WHERE reservationId = :code");
$stmt->execute(['code' => $reservation_code]);
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$invoice) {
    die("Invoice not found.");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reservation Summary</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Reservation Summary</h1>
        <p>Reservation Code: <?= htmlspecialchars($reservation_code) ?></p>
        <p>Total Cost: $<?= htmlspecialchars($reservation['totalCost']) ?></p>
        <h3>Invoice Details</h3>
        <ul>
            <li>Payment Type: <?= htmlspecialchars($invoice['paymentType']) ?></li>
            <li>Amount Paid: $<?= htmlspecialchars($invoice['amountPaid']) ?></li>
            <li>Payment Date: <?= htmlspecialchars($invoice['paymentDate']) ?></li>
        </ul>
        <a href="index.php" class="btn btn-primary">Return to Main Page</a>
    </div>
</body>
</html>
