<?php

include "debugging.php";
include 'header.html';

echo '<h1>Employees</h1>';

$employee = new Employee();
$rows = $employee->getAllEmployeesWithUserDetails();

if (!empty($rows)) {
    echo '<br />';
    // Display a table of results
    echo '<table align="center" cellspacing="2" cellpadding="4" width="75%">';
    echo '<tr bgcolor="#87CEEB">
          <td><b>Edit</b></td>
          <td><b>Delete</b></td>
          <td><b>First Name</b></td>
          <td><b>Last Name</b></td>
          <td><b>Email</b></td>
          <td><b>Department</b></td>
          <td><b>Position</b></td>
          <td><b>Join Date</b></td></tr>';

    // Use the following to set alternate backgrounds
    $bg = '#eeeeee';

    foreach ($rows as $row) {
        $bg = ($bg == '#eeeeee' ? '#ffffff' : '#eeeeee');
        echo '<tr bgcolor="' . $bg . '">
            <td><a href="edit_employee.php?id=' . $row->employeeId . '">Edit</a></td>
            <td><a href="delete_employee.php?id=' . $row->employeeId . '">Delete</a></td>
            <td>' . $row->firstName . '</td>
            <td>' . $row->lastName . '</td>
            <td>' . $row->email . '</td>
            <td>' . $row->department . '</td>
            <td>' . $row->position . '</td>
            <td>' . $row->joinDate . '</td>
          </tr>';
    }
    echo '</table>';
} else {
    echo '<p class="error">No employees found.</p>';
}

include 'footer.html';

?>
