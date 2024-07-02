<?php
session_start();  // Start the session

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include necessary files
include 'header.html';
require_once 'Hall.php';
require_once 'HallsTimingSlots.php';
require_once 'Database.php';
require_once 'Upload.php';

// Ensure the uid is set in the session for demonstration purposes
if (!isset($_SESSION['uid'])) {
    $_SESSION['uid'] = 'test_user';  // Set a temporary uid for the session
}

// Test database connection
$db = Database::getInstance()->getConnection();
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
} else {
    echo "Connected successfully to the database.<br>";
}

// Check if the form is submitted
if (isset($_POST['submit'])) {
    $hall = new Hall();
    $hall->setHallName($_POST['hallName']);
    $hall->setLocation($_POST['location']);
    $hall->setDescription($_POST['description']);
    $hall->setRentalCharge($_POST['rentalCharge']);
    $hall->setCapacity($_POST['capacity']);

    // Handle image upload using Upload class
    $upload = new Upload();
    $upload->setUploadDir('images/'); // Use the existing images directory

    if ($upload->uploadDir($upload->getUploadDir())) {
        $errors = $upload->upload('image');

        if (empty($errors)) {
            $hall->setImage($upload->getUploadDir() . $upload->getFilepath());
        } else {
            echo '<p class="error">Image upload failed: ' . implode(', ', $errors) . '</p>';
            include 'footer.html';
            exit();
        }
    } else {
        echo '<p class="error">Upload directory is not writable.</p>';
        include 'footer.html';
        exit();
    }

    // Attempt to register the hall
    if ($hall->registerHall()) {
        echo '<p>Hall added successfully.</p>';
        $hallId = Database::getInstance()->getLastInsertId();
        $timingSlots = [];
        for ($i = 0; $i < count($_POST['timingSlotStart']); $i++) {
            $timingSlot = new HallsTimingSlots();
            $timingSlot->setTimingSlotStart($_POST['timingSlotStart'][$i]);
            $timingSlot->setTimingSlotEnd($_POST['timingSlotEnd'][$i]);
            $timingSlot->setHallId($hallId);
            $timingSlots[] = $timingSlot;
        }

        $allSlotsRegistered = true;
        foreach ($timingSlots as $slot) {
            if (!$slot->registerTimingSlot()) {
                $allSlotsRegistered = false;
                break;
            }
        }

        if ($allSlotsRegistered) {
            echo '<p>Hall and timing slots added successfully.</p>';
        } else {
            echo '<p class="error">Failed to add one or more timing slots.</p>';
        }
    } else {
        echo '<p class="error">Failed to add hall.</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Hall</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <style>
        .container {
            max-width: 600px;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Add Hall</h2>
        <form action="add_hall.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="hallName">Hall Name:</label>
                <input type="text" class="form-control" id="hallName" name="hallName" required>
            </div>
            <div class="form-group">
                <label for="image">Image:</label>
                <input type="file" class="form-control" id="image" name="image" required>
            </div>
            <div class="form-group">
                <label for="location">Location:</label>
                <input type="text" class="form-control" id="location" name="location" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea class="form-control" id="description" name="description" required></textarea>
            </div>
            <div class="form-group">
                <label for="rentalCharge">Rental Charge:</label>
                <input type="number" class="form-control" id="rentalCharge" name="rentalCharge" required>
            </div>
            <div class="form-group">
                <label for="capacity">Capacity:</label>
                <input type="number" class="form-control" id="capacity" name="capacity" required>
            </div>
            <div id="timingSlots">
                <h4>Timing Slots</h4>
                <div class="form-group">
                    <label for="timingSlotStart[]">Start Time:</label>
                    <input type="time" class="form-control" name="timingSlotStart[]" required>
                </div>
                <div class="form-group">
                    <label for="timingSlotEnd[]">End Time:</label>
                    <input type="time" class="form-control" name="timingSlotEnd[]" required>
                </div>
            </div>
            <button type="button" onclick="addTimingSlot()">Add Another Timing Slot</button>
            <br><br>
            <button type="submit" class="btn btn-primary" name="submit">Add Hall</button>
        </form>
    </div>
    <script>
        function addTimingSlot() {
            const timingSlots = document.getElementById('timingSlots');
            const newSlot = `
                <div class="form-group">
                    <label for="timingSlotStart[]">Start Time:</label>
                    <input type="time" class="form-control" name="timingSlotStart[]" required>
                </div>
                <div class="form-group">
                    <label for="timingSlotEnd[]">End Time:</label>
                    <input type="time" class="form-control" name="timingSlotEnd[]" required>
                </div>
            `;
            timingSlots.insertAdjacentHTML('beforeend', newSlot);
        }
    </script>
</body>
</html>

<?php include 'footer.html'; ?>
