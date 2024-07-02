<?php

$page_title = 'Edit Hall';
include 'header.html';

echo '<h1>Edit Hall</h1>';

include "debugging.php";
require_once 'Hall.php';
require_once 'Database.php';

$id = 0;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
} elseif (isset($_POST['id'])) {
    $id = $_POST['id'];
} else {
    echo '<p class="error">Error has occurred</p>';
    include 'footer.html';
    exit();
}

$hall = new Hall();
if (!$hall->initWithHallId($id)) {
    echo '<p class="error">Hall not found.</p>';
    include 'footer.html';
    exit();
}

$db = Database::getInstance();
$errors = [];

if (isset($_POST['submitted'])) {
    $hallName = trim($_POST['hallName']);
    $image = trim($_POST['image']);
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    $rentalCharge = trim($_POST['rentalCharge']);
    $capacity = trim($_POST['capacity']);
    $valid = true;

    if (empty($hallName)) {
        $errors[] = "Hall Name is required.";
        $valid = false;
    }
    if (empty($image)) {
        $errors[] = "Image is required.";
        $valid = false;
    }
    if (empty($location)) {
        $errors[] = "Location is required.";
        $valid = false;
    }
    if (empty($description)) {
        $errors[] = "Description is required.";
        $valid = false;
    }
    if (empty($rentalCharge) || !is_numeric($rentalCharge)) {
        $errors[] = "Valid Rental Charge is required.";
        $valid = false;
    }
    if (empty($capacity) || !is_numeric($capacity)) {
        $errors[] = "Valid Capacity is required.";
        $valid = false;
    }

    if ($valid) {
        $hall->setHallName($hallName);
        $hall->setImage($image);
        $hall->setLocation($location);
        $hall->setDescription($description);
        $hall->setRentalCharge($rentalCharge);
        $hall->setCapacity($capacity);

        $hallUpdateSuccess = $hall->updateDB();

        if ($hallUpdateSuccess) {
            echo '<p>Update Successful</p>';
        } else {
            echo '<p class="error">Update Not Successful</p>';
        }

        // Update existing timing slots
        if (isset($_POST['timingIDs'])) {
            foreach ($_POST['timingIDs'] as $slotId) {
                $startTime = $_POST['startTime_' . $slotId];
                $endTime = $_POST['endTime_' . $slotId];
                if (empty($startTime) || empty($endTime)) {
                    $errors[] = "Both start and end times are required for all slots.";
                    continue;
                }
                $query = "UPDATE dpProj_HallsTimingSlots SET timingSlotStart=?, timingSlotEnd=? WHERE timingID=?";
                $stmt = $db->prepare($query);
                $stmt->bind_param('ssi', $startTime, $endTime, $slotId);
                $stmt->execute();
                $stmt->close();
            }
        }

        // Add new timing slots
        if (isset($_POST['newStartTime']) && isset($_POST['newEndTime'])) {
            foreach ($_POST['newStartTime'] as $index => $newStartTime) {
                $newEndTime = $_POST['newEndTime'][$index];
                if (empty($newStartTime) || empty($newEndTime)) {
                    $errors[] = "Both start and end times are required for new slots.";
                    continue;
                }
                $query = "INSERT INTO dpProj_HallsTimingSlots (timingSlotStart, timingSlotEnd, hallId) VALUES (?, ?, ?)";
                $stmt = $db->prepare($query);
                $stmt->bind_param('ssi', $newStartTime, $newEndTime, $id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
}

// Display errors if there are any
if (!empty($errors)) {
    echo '<p class="error">' . implode('<br>', $errors) . '</p>';
}

?>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
    }
    .form-container {
        width: 50%;
        margin: 50px auto;
        padding: 20px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        font-size: 18px;
    }
    h1, h3 {
        text-align: center;
    }
    .form-container p {
        margin-bottom: 15px;
    }
    .form-container input[type="text"],
    .form-container input[type="number"],
    .form-container input[type="time"],
    .form-container input[type="email"],
    .form-container textarea {
        width: calc(100% - 22px);
        padding: 10px;
        margin-bottom: 20px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 16px;
    }
    .form-container .fancy-button {
        background-color: #ff6666;
        color: white;
        padding: 15px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 20px;
        transition: background-color 0.3s;
        text-align: center;
        display: block;
        width: 100%;
    }
    .form-container .fancy-button:hover {
        background-color: #ff4d4d;
    }
</style>
<div class="form-container">
    <?php
    // Always show the form
    echo '<form action="edit_hall.php" method="post">
        <h3>Edit Hall: ' . $hall->getHallName() . '</h3>
        <p>Hall Name: <input type="text" name="hallName" value="' . $hall->getHallName() . '" /></p>
        <p>Image: <input type="text" name="image" value="' . $hall->getImage() . '"/></p>
        <p>Location: <input type="text" name="location" value="' . $hall->getLocation() . '"/></p>
        <p>Description: <textarea name="description">' . $hall->getDescription() . '</textarea></p>
        <p>Rental Charge: <input type="number" step="0.01" name="rentalCharge" value="' . $hall->getRentalCharge() . '"/></p>
        <p>Capacity: <input type="number" name="capacity" value="' . $hall->getCapacity() . '"/></p>';

    echo '<h3>Timing Slots</h3>';
    $timingSlots = $hall->getTimingSlotsByHallId($id);
    foreach ($timingSlots as $slot) {
        echo '<input type="hidden" name="timingIDs[]" value="' . $slot->timingID . '">';
        echo '<p>Start Time: <input type="time" name="startTime_' . $slot->timingID . '" value="' . $slot->timingSlotStart . '"/></p>';
        echo '<p>End Time: <input type="time" name="endTime_' . $slot->timingID . '" value="' . $slot->timingSlotEnd . '"/></p>';
    }

    echo '<h3>Add New Timing Slots</h3>';
    echo '<div id="newTimingSlots"></div>';
    echo '<button type="button" onclick="addNewTimingSlot()">Add New Slot</button>';

    echo '<p><input type="submit" class="fancy-button" name="submit" value="Update" /></p>
        <input type="hidden" name="submitted" value="TRUE">
        <input type="hidden" name="id" value="' . $id . '"/>
        </form>';
    ?>
</div>
<script>
function addNewTimingSlot() {
    var container = document.getElementById("newTimingSlots");
    var newSlotHTML = `
        <div>
            <p>Start Time: <input type="time" name="newStartTime[]" required /></p>
            <p>End Time: <input type="time" name="newEndTime[]" required /></p>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', newSlotHTML);
}
</script>
<?php include 'footer.html'; ?>
