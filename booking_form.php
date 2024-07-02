<?php
// Example code for receiving data from searchAndBooking.php
$hallId = $_POST['hallId'];
$hallName = $_POST['hallName'];
$startDate = $_POST['start_date'];
$duration = $_POST['duration'];
$endDate = $_POST['end_date'];
$audience = $_POST['audience'];
$hallImage = $_POST['hallImage'];
$rentalDetails = $_POST['rentalDetails'];
$availableTime = $_POST['time'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Hall</title>
    <style>
        body { font-family: Arial, sans-serif; color: black; }
        .form-section { margin-bottom: 20px; }
        label { margin-right: 10px; color: black; }
        input, select { margin-right: 20px; color: black; }
        .form-buttons { margin-top: 20px; }
        .form-buttons input {
            padding: 10px 20px;
            margin-right: 10px;
            background-color: black;
            color: white;
            border: none;
            border-radius: 5px;
            text-transform: uppercase;
            cursor: pointer;
        }
        .form-buttons input:hover {
            background-color: red;
            color: white;
        }
    </style>
</head>
<body>
    <?php include 'header.html'; ?>

    <div class="booking_section layout_padding">
        <div class="container">
            <h1>Book a Hall</h1>
            <?php
            // Include the Database class
            include 'Database.php';
            $db = Database::getInstance();

            // Get hallId from query string
            $hallId = isset($_GET['hallId']) ? $_GET['hallId'] : null;

            // Fetch hall name and image based on hallId
            $hallName = '';
            $hallImage = '';
            if ($hallId) {
                $sql = "SELECT hallName, image FROM dbProj_Hall WHERE hallId = $hallId";
                $hall = $db->singleFetch($sql);
                if ($hall) {
                    $hallName = $hall->hallName;
                    $hallImage = $hall->image;
                }
            }

            // Get rental details and available time from query string
            $rentalDetails = isset($_GET['rentalDetails']) ? $_GET['rentalDetails'] : '';
            $availableTime = isset($_GET['availableTime']) ? $_GET['availableTime'] : '';
            ?>
            <form id="bookingForm" method="post" action="Confirm_Booking.php">
                <div class="form-section">
                    <label for="audience">Number of Audience:</label>
                    <input type="number" id="audience" name="audience" value="<?php echo isset($_GET['audience']) ? htmlspecialchars($_GET['audience']) : ''; ?>" required>
                </div>
                <div class="form-section">
                    <select id="duration" name="duration" required>
                        <option value="">Select Duration</option>
                        <option value="1" <?php echo (isset($_GET['duration']) && $_GET['duration'] == '1') ? 'selected' : ''; ?>>1 Day</option>
                        <option value="7" <?php echo (isset($_GET['duration']) && $_GET['duration'] == '7') ? 'selected' : ''; ?>>1 Week</option>
                        <option value="15" <?php echo (isset($_GET['duration']) && $_GET['duration'] == '15') ? 'selected' : ''; ?>>15 Days</option>
                    </select>
                </div>
                <div class="form-section">
                    <label for="start_date">Start Date:</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo isset($_GET['date']) ? htmlspecialchars($_GET['date']) : ''; ?>" required>
                    <label for="end_date">End Date:</label>
                    <input type="text" id="end_date" name="end_date" readonly>
                </div>
                <div class="form-section">
                    <label for="hall">Hall:</label>
                    <input type="hidden" id="hallId" name="hallId" value="<?php echo htmlspecialchars($hallId); ?>">
                    <input type="hidden" id="hallImage" name="hallImage" value="<?php echo htmlspecialchars($hallImage); ?>">
                    <input type="text" id="hallName" name="hallName" value="<?php echo htmlspecialchars($hallName); ?>" readonly>
                    <label for="rentalDetails">Rental Details:</label>
                    <input type="text" id="rentalDetails" name="rentalDetails" value="<?php echo htmlspecialchars($rentalDetails); ?>" readonly>
                    <label for="time">Time:</label>
                    <input type="text" id="availableTime" name="availableTime" value="<?php echo htmlspecialchars($availableTime); ?>" readonly>
                </div>
                <div class="form-buttons">
                    <input type="submit" id="proceedButton" value="Book an event">
                    <input type="button" value="Cancel" onclick="window.history.back();">
                </div>
            </form>
        </div>
    </div>

    <script>
        function calculateEndDate() {
            const startDate = document.getElementById('start_date').value;
            const duration = document.getElementById('duration').value;
            if (startDate && duration) {
                const endDate = new Date(startDate);
                endDate.setDate(endDate.getDate() + parseInt(duration));
                document.getElementById('end_date').value = endDate.toISOString().split('T')[0];
            }
        }

        document.getElementById('duration').addEventListener('change', calculateEndDate);
        document.getElementById('start_date').addEventListener('change', calculateEndDate);

        // Calculate the end date on page load if start date and duration are already set
        window.onload = function() {
            calculateEndDate();
        }
    </script>
    <?php include 'footer.html'; ?>
</body>
</html>