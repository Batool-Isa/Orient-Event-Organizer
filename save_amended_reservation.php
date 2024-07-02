<!-- Shooq -->
<?php
// save_amended_reservation.php
session_start();
include 'Database.php'; // Your database connection file

// Retrieve posted data
$reservation_code = $_POST['reservation_code'];
$name = $_POST['name'];
$email = $_POST['email'];
$company = $_POST['company'];
$total_cost = $_POST['total_cost'];

// Update the reservation details in the database
$stmt = $pdo->prepare("UPDATE reservations SET name = :name, email = :email, company = :company, total_cost = :total_cost WHERE reservation_code = :code");
$stmt->execute([
    'name' => $name,
    'email' => $email,
    'company' => $company,
    'total_cost' => $total_cost,
    'code' => $reservation_code
]);

// Placeholder for sending confirmation email
mail($email, "Amended Reservation Confirmation", "Your reservation has been successfully updated. Your new total is: $total_cost");

// Redirect or confirm the amendment was successful
header("Location: confirmation.php?status=success");
exit();
?>

