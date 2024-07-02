<?php
session_start();
include 'Database.php';
//include 'Client.php';

// Check if the user is logged in
$loggedIn = isset($_SESSION['userId']);
$userEmail = '';
$clientId = '';
$clientStatus = '';



// Fetch user details if logged in
if ($loggedIn) {
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

    // Fetch client details
    $clientQuery = "SELECT clientId, clientStatus FROM dbProj_Client WHERE userId = ?";
    $clientDetails = $db->singleFetch($clientQuery, [$userId]);
    if ($clientDetails) {
        $clientId = $clientDetails->clientId;
        $clientStatus = $clientDetails->clientStatus;
    } else {
        die('Error: Could not fetch client details.');
    }
}

// Get selected services and menus from POST
$selectedMenus = isset($_POST['selectedMenus']) ? $_POST['selectedMenus'] : [];
$menuPrices = isset($_POST['menuPrices']) ? $_POST['menuPrices'] : [];
$menuNames = isset($_POST['menuNames']) ? $_POST['menuNames'] : [];
$menuLists = isset($_POST['menuLists']) ? $_POST['menuLists'] : [];

$selectedServices = isset($_POST['selectedServices']) ? $_POST['selectedServices'] : [];
$servicePrices = isset($_POST['servicePrices']) ? $_POST['servicePrices'] : [];
$serviceNames = isset($_POST['serviceNames']) ? $_POST['serviceNames'] : [];
$serviceLists = isset($_POST['serviceLists']) ? $_POST['serviceLists'] : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm Reservation</title>
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
        <h1>Confirm Reservation</h1>
        <form action="checkout.php" method="post">
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

            <!-- Pass selected menus and services -->
            <?php foreach ($selectedMenus as $index => $menuId): ?>
                <input type="hidden" name="selectedMenus[]" value="<?php echo htmlspecialchars($menuId); ?>">
                <input type="hidden" name="menuPrices[]" value="<?php echo htmlspecialchars($menuPrices[$index]); ?>">
                <input type="hidden" name="menuNames[]" value="<?php echo htmlspecialchars($menuNames[$index]); ?>">
                <input type="hidden" name="menuLists[]" value="<?php echo htmlspecialchars($menuLists[$index]); ?>">
            <?php endforeach; ?>
            <?php foreach ($selectedServices as $index => $serviceId): ?>
                <input type="hidden" name="selectedServices[]" value="<?php echo htmlspecialchars($serviceId); ?>">
                <input type="hidden" name="servicePrices[]" value="<?php echo htmlspecialchars($servicePrices[$index]); ?>">
                <input type="hidden" name="serviceNames[]" value="<?php echo htmlspecialchars($serviceNames[$index]); ?>">
                <input type="hidden" name="serviceLists[]" value="<?php echo htmlspecialchars($serviceLists[$index]); ?>">
            <?php endforeach; ?>

            <!-- Include user and client details if logged in -->
            <?php if ($loggedIn): ?>
                <h2>Client Details</h2>
                <p>Email: <?php echo htmlspecialchars($userEmail); ?></p>
                <input type="hidden" name="userEmail" value="<?php echo htmlspecialchars($userEmail); ?>">
                <input type="hidden" name="clientId" value="<?php echo htmlspecialchars($clientId); ?>">
                <input type="hidden" name="clientStatus" value="<?php echo htmlspecialchars($clientStatus); ?>">
                <label for="companyName">Company Name:</label>
                <input type="text" id="companyName" name="companyName"><br><br>
            <?php else: ?>
                <h2>Enter Personal/Business Details to Confirm Reservation</h2>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required><br><br>
                <label for="companyName">Company Name:</label>
                <input type="text" id="companyName" name="companyName"><br><br>
            <?php endif; ?>
		 <input type="hidden" name="email" value="<?php echo htmlspecialchars($_POST['email']); ?>">

            <div class="form-buttons">
                <input type="submit" value="Proceed to Checkout">
                <input type="button" value="Cancel" onclick="window.location.href='index.php';">
            </div>
        </form>
    </div>

    <footer>
        <?php include 'footer.html'; ?>
    </footer>
</body>
</html>

