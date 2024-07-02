<?php

$page_title = 'Edit Client';
include 'header.html';

echo '<h1>Edit Client</h1>';

include "debugging.php";
require_once 'Client.php';
require_once 'Users.php';

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

$client = new Client();
if (!$client->initWithClientId($id)) {
    echo '<p class="error">Client not found.</p>';
    include 'footer.html';
    exit();
}

$user = new Users();
if (!$user->initWithUserId($client->getUserId())) {
    echo '<p class="error">User not found.</p>';
    include 'footer.html';
    exit();
}

if (isset($_POST['submitted'])) {
    $user->setFirstName($_POST['firstName']);
    $user->setLastName($_POST['lastName']);
    $user->setUsername($_POST['username']);
    $user->setEmail($_POST['email']);
    $user->setPhoneNumber($_POST['phoneNumber']);
    $client->setCompanyName($_POST['companyName']);
    $client->setRoyaltyPoints($_POST['royaltyPoints']);

    $userUpdateSuccess = $user->updateDB();
    $clientUpdateSuccess = $client->updateClientDB();

    if ($userUpdateSuccess && $clientUpdateSuccess) {
        echo '<p>Update Successful</p>';
    } else {
        echo '<p class="error">Update Not Successful</p>';
        error_log("User update success: " . ($userUpdateSuccess ? "true" : "false"));
        error_log("Client update success: " . ($clientUpdateSuccess ? "true" : "false"));
    }
}

// Always show the form
echo '<form action="edit_client.php" method="post">
    <br />
    <h3>Edit Client: ' . $client->getClientId() . ' ' . $user->getFirstName() . ' ' . $user->getLastName() . '</h3>
    <p><br />
       <p>First Name: <input type="text" name="firstName" value="' . $user->getFirstName() . '" /></p>
       <p>Last Name: <input type="text" name="lastName" value="' . $user->getLastName() . '"/></p>
       <p>Username: <input type="text" name="username" value="' . $user->getUsername() . '"/></p>
       <p>Email: <input type="email" name="email" value="' . $user->getEmail() . '"/></p>
       <p>Phone Number: <input type="text" name="phoneNumber" value="' . $user->getPhoneNumber() . '"/></p>
       <p>Company Name: <input type="text" name="companyName" value="' . $client->getCompanyName() . '"/></p>
       <p>Royalty Points: <input type="number" name="royaltyPoints" value="' . $client->getRoyaltyPoints() . '"/></p>
    </p>
    <p><input type="submit" class="DB4Button" name="submit" value="update" /></p>
    <input type="hidden" name="submitted" value="TRUE">
    <input type="hidden" name="id" value="' . $id . '"/>
    </form>';

include 'footer.html';

?>
