<!-- Shooq -->
<?php
// amend_reservation.php
session_start();
include 'Database.php'; // Your database connection file

// Retrieve the reservation details based on the reservation code sent via GET or POST
$reservation_code = isset($_GET['code']) ? $_GET['code'] : '';
if (!$reservation_code) {
    die("Reservation code is required.");
}

// Fetch reservation data from the database
$stmt = $pdo->prepare("SELECT * FROM dbProj_Reservation WHERE reservation_code = :code");
$stmt->execute(['code' => $reservation_code]);
$reservation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reservation) {
    die("Reservation not found.");
}

// Calculate any changes with the amendment fee
$original_cost = $reservation['totalCost'];
$amendment_fee = $original_cost * 0.05;
$total_cost = $original_cost + $amendment_fee;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Amend Reservation</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Amend Reservation</h1>
        <form action="save_amended_reservation.php" method="post">
            <input type="hidden" name="reservation_code" value="<?= htmlspecialchars($reservation_code) ?>">
            
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" class="form-control" required value="<?= htmlspecialchars($reservation['name']) ?>">
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="form-control" required value="<?= htmlspecialchars($reservation['email']) ?>">
            </div>
            <div class="form-group">
                <label for="company">Company Name:</label>
                <input type="text" id="company" name="company" class="form-control" value="<?= htmlspecialchars($reservation['company']) ?>">
            </div>
            <div class="form-group">
                <label for="total_cost">Total Cost (With Amendment Fee):</label>
                <input type="text" id="total_cost" name="total_cost" class="form-control" value="<?= $total_cost ?>" readonly>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.4.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
