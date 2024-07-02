<?php
ob_start(); // Start output buffering

// Include the Database class
include 'Database.php';
$db = Database::getInstance();

// Get current booking details from POST data
$hallId = isset($_POST['hallId']) ? $_POST['hallId'] : null;
$hallName = isset($_POST['hallName']) ? $_POST['hallName'] : null;
$startDate = isset($_POST['start_date']) ? $_POST['start_date'] : null;
$duration = isset($_POST['duration']) ? $_POST['duration'] : null;
$endDate = isset($_POST['end_date']) ? $_POST['end_date'] : null;
$audience = isset($_POST['audience']) ? $_POST['audience'] : null;
$time = isset($_POST['time']) ? $_POST['time'] : null;
$hallImage = isset($_POST['hallImage']) ? $_POST['hallImage'] : null;
$rentalDetails = isset($_POST['rentalDetails']) ? $_POST['rentalDetails'] : null;

// Calculate end date if not provided
if ($startDate && $duration) {
    switch ($duration) {
        case '1':
            $endDate = $startDate; // Same day
            break;
        case '7':
            $endDate = date('Y-m-d', strtotime($startDate . ' + 6 days')); // 7 days, same day + 6 days
            break;
        case '15':
            $endDate = date('Y-m-d', strtotime($startDate . ' + 14 days')); // 15 days, same day + 14 days
            break;
    }
}

$errors = [];

// Check availability on form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_changes'])) {
    error_log("Form submitted with POST data: " . print_r($_POST, true));

    if (empty($startDate)) {
        $errors['startDate'] = "Start date is required.";
    } elseif ($startDate < date('Y-m-d')) {
        $errors['startDate'] = "Selected date cannot be in the past.";
    }
    if (empty($duration)) {
        $errors['duration'] = "Duration is required.";
    }
    if (empty($audience) || !is_numeric($audience) || $audience <= 0) {
        $errors['audience'] = "Number of audience must be a positive number.";
    }
    if (empty($time)) {
        $errors['time'] = "Time is required.";
    }

    // Capacity check
    if (!empty($hallId)) {
        $capacitySql = "SELECT capacity FROM dbProj_Hall WHERE hallId = $hallId";
        $hallCapacity = $db->singleFetch($capacitySql)->capacity;
        if ($audience > $hallCapacity) {
            $errors['capacity'] = "The selected hall can only accommodate up to $hallCapacity audiences. Please reduce the number of audiences.";
        }
    }

    if (empty($errors)) {
        $time24 = date("H:i:s", strtotime($time));

        $sql = "SELECT h.hallId
                FROM dbProj_Hall h
                JOIN dpProj_HallsTimingSlots t ON h.hallId = t.hallId
                WHERE h.capacity >= $audience
                AND '$time24' BETWEEN t.timingSlotStart AND t.timingSlotEnd
                AND NOT EXISTS (
                    SELECT 1
                    FROM dbProj_Reservation r
                    WHERE r.hallId = h.hallId
                    AND r.timingID = t.timingID
                    AND r.startDate <= DATE_ADD('$startDate', INTERVAL $duration DAY)
                    AND r.endDate >= '$startDate'
                )";

        $availableHalls = $db->multiFetch($sql);

        if (empty($availableHalls)) {
            if ($duration == '1') {
                $errors['availability'] = "No halls are available for the selected date, time, and audience. Please try changing the date or reducing the number of audience.";
            } elseif ($duration == '7') {
                $errors['availability'] = "No halls are available for the selected week, time, and audience. Please try changing the date or reducing the number of audience.";
            } else {
                $errors['availability'] = "No halls are available for the selected duration, time, and audience. Please try changing the date or reducing the number of audience.";
            }
        } else {
            // Redirect to confirmation page with updated details
            header("Location: Confirm_Booking.php?hallId=$hallId&hallName=$hallName&start_date=$startDate&duration=$duration&end_date=$endDate&audience=$audience&time=$time&hallImage=$hallImage&rentalDetails=$rentalDetails");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Booking Details</title>
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
        .form-section {
            margin-bottom: 20px;
        }
        label {
            margin-right: 10px;
        }
        input, select {
            margin-right: 20px;
            padding: 5px 10px;
        }
        .form-buttons {
            margin-top: 20px;
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
        .error-message {
            color: red;
            margin-top: 10px;
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
            color: black;
        }
    </style>
</head>
<body>
    <header>
        <?php include 'header.html'; ?>
    </header>

    <div class="container">
        <h1>Update Booking Details</h1>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <p><?php echo htmlspecialchars(reset($errors)); ?></p>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-section">
                <label for="hallName">Hall Name:</label>
                <input type="text" id="hallName" name="hallName" value="<?php echo htmlspecialchars($hallName); ?>" readonly>
                <label for="startDate">Start Date:</label>
                <input type="date" id="startDate" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>" required>
                <label for="duration">Duration:</label>
                <select id="duration" name="duration" required>
                    <option value="1" <?php echo ($duration == '1') ? 'selected' : ''; ?>>1 Day</option>
                    <option value="7" <?php echo ($duration == '7') ? 'selected' : ''; ?>>1 Week</option>
                    <option value="15" <?php echo ($duration == '15') ? 'selected' : ''; ?>>15 Days</option>
                </select>
                <label for="audience">Number of Audience:</label>
                <input type="number" id="audience" name="audience" value="<?php echo htmlspecialchars($audience); ?>" required>
                <label for="time">Time:</label>
                <input type="time" id="time" name="time" value="<?php echo htmlspecialchars($time); ?>" required>
                <input type="hidden" name="hallId" value="<?php echo htmlspecialchars($hallId); ?>">
                <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>">
                <input type="hidden" name="hallImage" value="<?php echo htmlspecialchars($hallImage); ?>">
                <input type="hidden" name="rentalDetails" value="<?php echo htmlspecialchars($rentalDetails); ?>">
            </div>
            <div class="form-buttons">
                <input type="submit" name="save_changes" value="Save Changes">
            </div>
        </form>
    </div>

    <footer>
        <?php include 'footer.html'; ?>
    </footer>
</body>
</html>

<?php
ob_end_flush(); // Send the output to the browser
?>