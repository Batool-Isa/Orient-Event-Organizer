<?php
include 'Database.php';
$db = Database::getInstance();

// Get the reservationId and selected options from the form submission
$reservationId = isset($_POST['reservationId']) ? $_POST['reservationId'] : null;
$selectedMenuId = isset($_POST['selectedMenuId']) ? $_POST['selectedMenuId'] : null;
$selectedPackageId = isset($_POST['selectedPackageId']) ? $_POST['selectedPackageId'] : null;
$skip = isset($_POST['skip']) ? $_POST['skip'] : null;

if ($reservationId) {
    if ($skip) {
        // Skip the catering services step
        header("Location: payment_page.php?reservationId=" . urlencode($reservationId));
        exit();
    } else {
        if ($selectedMenuId) {
            // Insert selected menu into dbProj_Catering table
            $sql = "INSERT INTO dbProj_Catering (reservationId, menuId) VALUES (?, ?)";
            $db->querySQL($sql, [$reservationId, $selectedMenuId]);
        }
        if ($selectedPackageId) {
            // Insert selected service package into dbProj_Catering table
            $sql = "UPDATE dbProj_Catering SET packageId = ? WHERE reservationId = ? AND menuId = ?";
            $db->querySQL($sql, [$selectedPackageId, $reservationId, $selectedMenuId]);
        }

        // Redirect to the payment page or confirmation page
        header("Location: payment_page.php?reservationId=" . urlencode($reservationId));
        exit();
    }
} else {
    echo '<p>Reservation ID is missing.</p>';
}
?>
