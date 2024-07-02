<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'Database.php';
    $db = Database::getInstance();

    $startDate = isset($_POST['start_date']) ? $_POST['start_date'] : null;
    $duration = isset($_POST['duration']) ? $_POST['duration'] : null;
    $audience = isset($_POST['audience']) ? $_POST['audience'] : null;
    $time = isset($_POST['time']) ? $_POST['time'] : null;

    $errors = [];

    if (empty($startDate)) {
        $errors['startDate'] = "Start date is required.";
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
            $errors['availability'] = "No halls are available for the selected date, time, and audience. Please try changing the date, time, or reducing the number of audience.";
        }
    }

    echo json_encode($errors);
}
?>
