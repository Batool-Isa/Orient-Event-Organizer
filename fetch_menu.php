<?php
// Include the Database class
include 'Database.php';
$db = Database::getInstance();

// Get menuId from the AJAX request
$menuId = isset($_POST['menuId']) ? $_POST['menuId'] : null;

if ($menuId) {
    // Fetch menu details from the database
    $sql = "SELECT menuList, price FROM dbProj_Menu WHERE menuId = ?";
    $menu = $db->singleFetch($sql, [$menuId]);

    if ($menu) {
        echo '<h3>Menu Details</h3>';
        echo '<p>' . htmlspecialchars($menu->menuList) . '</p>';
        echo '<p>Price: ' . htmlspecialchars($menu->price) . ' BD</p>';
    } else {
        echo '<p>Menu details not found.</p>';
    }
} else {
    echo '<p>No menu ID received.</p>';
}
?>
