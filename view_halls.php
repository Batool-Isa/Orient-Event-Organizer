<?php

include "debugging.php";
include 'header.html';

echo '<h1> Halls </h1>';

$hall = new Hall();
$rows = $hall->getAllHalls();

if (!empty($rows)) {
    echo '<br />';
    // Display a table of results
    echo '<table align="center" cellspacing="2" cellpadding="4" width="75%" style="font-size: 18px;">';
    echo '<tr bgcolor="#ff6666" style="color: white;">
          <td><b>Edit</b></td>
          <td><b>Delete</b></td>
          <td><b>Hall Name</b></td>
          <td><b>Location</b></td>
          <td><b>Rental Charge</b></td>
          <td><b>Capacity</b></td>
          <td><b>Image</b></td>
          <td><b>Timing Slots</b></td></tr>';

    // Use the following to set alternate backgrounds 
    $bg = '#ffffff';

    foreach ($rows as $row) {
        $bg = ($bg == '#ffffff' ? '#eeeeee' : '#ffffff');
        
        // Get timing slots for the current hall
        $timingSlots = $hall->getTimingSlotsByHallId($row->hallId);
        $timingSlotsStr = '';
        foreach ($timingSlots as $slot) {
            $timingSlotsStr .= $slot->timingSlotStart . ' - ' . $slot->timingSlotEnd . '<br>';
        }

        echo '<tr bgcolor="' . $bg . '" style="color: black; border: 1px solid black;">';
        echo '<td><a href="edit_hall.php?id=' . $row->hallId . '" style="text-decoration: underline;">Edit</a></td>';
        echo '<td><a href="delete_hall.php?id=' . $row->hallId . '" style="text-decoration: underline;">Delete</a></td>';
        echo '<td>' . $row->hallName . '</td>';
        echo '<td>' . $row->location . '</td>';
        echo '<td>' . $row->rentalCharge . '</td>';
        echo '<td>' . $row->capacity . '</td>';
        echo '<td><img src="' . $row->image . '" alt="' . $row->hallName . '" style="width:200px;height:200px;"></td>';
        echo '<td>' . $timingSlotsStr . '</td>';
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo '<p class="error">Oh dear. There was an error</p>';
}

include 'footer.html';

?>
