<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'Database.php';

// Calculate discount and check royalty points using stored procedure
function calculateDiscountAndRoyalty($clientId, $totalCost) {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("CALL CalculateDiscountAndRoyalty(?, ?, @discountRate, @discountedPrice, @royaltyPoints)");
    if ($stmt === false) {
        die("Prepare failed: " . $db->error);
    }

    if (!$stmt->bind_param("id", $clientId, $totalCost)) {
        die("Bind failed: " . $stmt->error);
    }

    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }

    $stmt->close();

    $result = $db->query("SELECT @discountRate AS discountRate, @discountedPrice AS discountedPrice, @royaltyPoints AS royaltyPoints");
    if ($result === false) {
        die("Query failed: " . $db->error);
    }

    $row = $result->fetch_assoc();
    return $row;
}

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
$companyName = isset($_POST['companyName']) ? $_POST['companyName'] : '';

// Check if the user is logged in
$loggedIn = isset($_SESSION['userId']);
$clientStatus = '';
if ($loggedIn) {
    $clientStatus = $_POST['clientStatus'];
    $clientId = $_POST['clientId'];
    // Calculate discount and royalty points
    $calcResults = calculateDiscountAndRoyalty($clientId, $totalPrice);
    $discountRate = $calcResults['discountRate'];
    $discountedPrice = $calcResults['discountedPrice'];
    $royaltyPoints = $calcResults['royaltyPoints'];
} else {
    $clientId = '';
    $discountedPrice = $totalPrice;
}

// Retrieve selected menus and services
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
    <title>Checkout</title>
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
        <h1>Checkout</h1>
        <h2>Reservation Summary</h2>
        <p>Hall Name: <?php echo htmlspecialchars($hallName); ?></p>
        <p>Start Date: <?php echo htmlspecialchars($startDate); ?></p>
        <p>End Date: <?php echo htmlspecialchars($endDate); ?></p>
        <p>Duration: <?php echo htmlspecialchars($duration); ?> days</p>
        <p>Number of Audience: <?php echo htmlspecialchars($audience); ?></p>
        <p>Time: <?php echo htmlspecialchars($time); ?></p>
        <p>Rental Details: <?php echo htmlspecialchars($rentalDetails); ?> BD</p>
        <p>Total Price: <?php echo htmlspecialchars($totalPrice); ?> BD</p>
        <?php if ($loggedIn): ?>
            <p>Discounted Price: <?php echo htmlspecialchars($discountedPrice); ?> BD</p>
        <?php endif; ?>
        <p>Company Name: <?php echo htmlspecialchars($companyName); ?></p>

        <h3>Selected Menus</h3>
        <?php foreach ($menuNames as $index => $menuName): ?>
            <p><?php echo htmlspecialchars($menuName); ?> - <?php echo htmlspecialchars($menuPrices[$index]); ?> BD</p>
            <p><?php echo htmlspecialchars($menuLists[$index]); ?></p>
        <?php endforeach; ?>

        <h3>Selected Services</h3>
        <?php foreach ($serviceNames as $index => $serviceName): ?>
            <p><?php echo htmlspecialchars($serviceName); ?> - <?php echo htmlspecialchars($servicePrices[$index]); ?> BD</p>
            <p><?php echo htmlspecialchars($serviceLists[$index]); ?></p>
        <?php endforeach; ?>

        <div class="form-buttons">
            <form action="Payment.php" method="post">
                <!-- Pass reservation details -->
                <input type="hidden" name="hallId" value="<?php echo htmlspecialchars($hallId); ?>">
                <input type="hidden" name="hallName" value="<?php echo htmlspecialchars($hallName); ?>">
                <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>">
                <input type="hidden" name="duration" value="<?php echo htmlspecialchars($duration); ?>">
                <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>">
                <input type="hidden" name="audience" value="<?php echo htmlspecialchars($audience); ?>">
                <input type="hidden" name="time" value="<?php echo htmlspecialchars($time); ?>">
                <input type="hidden" name="hallImage" value="<?php echo htmlspecialchars($hallImage); ?>">
                <input type="hidden" name="rentalDetails" value="<?php echo htmlspecialchars($rentalDetails); ?>">
                <input type="hidden" name="totalPrice" value="<?php echo htmlspecialchars($totalPrice); ?>">
                <input type="hidden" name="discountedPrice" value="<?php echo htmlspecialchars($discountedPrice); ?>">
                <input type="hidden" name="discountRate" value="<?php echo htmlspecialchars($discountRate); ?>"> <!-- Add discountRate -->
                <input type="hidden" name="clientId" value="<?php echo htmlspecialchars($clientId); ?>">
                <input type="hidden" name="clientStatus" value="<?php echo htmlspecialchars($clientStatus); ?>">
                <input type="hidden" name="companyName" value="<?php echo htmlspecialchars($companyName); ?>">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($userEmail); ?>">

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
                <div class="form-buttons">
                    <input type="submit" value="Proceed to Payment">
                    <input type="button" value="Cancel" onclick="window.location.href='index.php';">
                </div>
            </form>
        </div>
    </div>

    <footer>
        <?php include 'footer.html'; ?>
    </footer>
</body>
</html>
