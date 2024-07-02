<?php

$page_title = 'Delete Client';
include 'header.html';

echo '<h1>Delete Client</h1>';

include "debugging.php";
require_once 'Client.php';
require_once 'Database.php';

$id = 0;

// Check if an ID is provided via GET or POST request
if (isset($_GET['id'])) {
    $id = $_GET['id'];
} elseif (isset($_POST['id'])) {
    $id = $_POST['id'];
} else {
    // If no ID is provided, display an error message and exit
    echo '<p class="error">Error has occurred</p>';
    include 'footer.html';
    exit();
}

$client = new Client();
$user = null;
if ($client->initWithClientId($id)) {
    // If the client is found, get the associated user
    $user = $client->getUserId();
} else {
    // If the client is not found, display an error message and exit
    echo '<p class="error">Client not found.</p>';
    include 'footer.html';
    exit();
}

$db = Database::getInstance();

if (isset($_POST['submitted']) && $_POST['submitted'] === 'TRUE') {
    // Delete the client
    $query = "DELETE FROM dbProj_Client WHERE clientId = ?";
    $stmt = $db->prepare($query);
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        echo '<p>Client deleted successfully.</p>';
    } else {
        echo '<p class="error">Error preparing statement for deleting client: ' . $db->getConnection()->error . '</p>';
    }

    // Delete the associated user
    if ($user !== null) {
        $query = "DELETE FROM dbProj_User WHERE userId = ?";
        $stmt = $db->prepare($query);
        if ($stmt) {
            $stmt->bind_param('i', $user);
            $stmt->execute();
            $stmt->close();
            echo '<p>User associated with the client deleted successfully.</p>';
        } else {
            echo '<p class="error">Error preparing statement for deleting user: ' . $db->getConnection()->error . '</p>';
        }
    }

    include 'footer.html';
    exit();
}

?>
<!-- Styling for the form container, buttons, and header -->
<style>
    .form-container {
        width: 50%;
        margin: 50px auto;
        padding: 20px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        font-size: 18px;
    }
    h1 {
        text-align: center;
        font-size: 36px;
        margin-top: 30px;
        margin-bottom: 30px;
    }
    h3 {
        text-align: center;
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
        margin-top: 20px;
    }
    .form-container .fancy-button:hover {
        background-color: #ff4d4d;
    }
    .form-container .cancel-button {
        background-color: #ccc;
        color: black;
        padding: 15px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 20px;
        transition: background-color 0.3s;
        text-align: center;
        display: block;
        width: 100%;
        margin-top: 20px;
    }
    .form-container .cancel-button:hover {
        background-color: #bbb;
    }
</style>
<div class="form-container">
    <form action="delete_client.php" method="post">
        <!-- Confirmation message -->
        <h3>Are you sure you want to delete the client: <?php echo $client->getClientId(); ?>?</h3>
        <p>This action cannot be undone.</p>
        <!-- Hidden fields to pass the form submission status and client ID -->
        <input type="hidden" name="submitted" value="TRUE">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <!-- Submit button for deletion -->
        <input type="submit" class="fancy-button" value="Yes, Delete Client">
        <!-- Cancel button to redirect back to the view clients page -->
        <a href="view_clients.php" class="cancel-button">Cancel</a>
    </form>
</div>

<?php include 'footer.html'; ?>
