<?php

include "debugging.php";
include 'header.html';

echo '<h1>Clients</h1>';

$client = new Client();
$rows = $client->getAllClientsWithUserDetails();

if (!empty($rows)) {
    echo '<br />';
    // Display a table of results
    echo '<table align="center" cellspacing="2" cellpadding="4" width="75%">';
    echo '<tr bgcolor="#87CEEB">
          <td><b>Edit</b></td>
          <td><b>Delete</b></td>
          <td><b>First Name</b></td>
          <td><b>Last Name</b></td>
          <td><b>Username</b></td>
          <td><b>Email</b></td>
          <td><b>Phone Number</b></td>
          <td><b>Company Name</b></td>
          <td><b>Royalty Points</b></td></tr>';

    // Use the following to set alternate backgrounds
    $bg = '#eeeeee';

    foreach ($rows as $row) {
        $bg = ($bg == '#eeeeee' ? '#ffffff' : '#eeeeee');
        echo '<tr bgcolor="' . $bg . '">
            <td><a href="edit_client.php?id=' . $row->clientId . '">Edit</a></td>
            <td><a href="delete_client.php?id=' . $row->clientId . '">Delete</a></td>
            <td>' . $row->firstName . '</td>
            <td>' . $row->lastName . '</td>
            <td>' . $row->username . '</td>
            <td>' . $row->email . '</td>
            <td>' . $row->phoneNumber . '</td>
            <td>' . $row->companyName . '</td>
            <td>' . $row->royaltyPoints . '</td>
          </tr>';
    }
    echo '</table>';
} else {
    echo '<p class="error">No clients found.</p>';
}

include 'footer.html';

?>
