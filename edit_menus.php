<?php

$page_title = 'Edit Menu';
include 'header.html';

echo '<h1>Edit Menu</h1>';

include "debugging.php";

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

$menu = new Menu();
$menu->initWithmenuId($id);

if (isset($_POST['submitted'])) {
    $menu->setMenuName($_POST['menuName']);
    $menu->setMenuList($_POST['menuList']);
    $menu->setPrice($_POST['price']);

    if ($menu->updateDB()) {
        echo '<p>Update Successful</p>';
    } else {
        echo '<p class="error">Not Successful</p>';
    }
}

// Always show the form
$db = Database::getInstance();
$q = "SELECT * FROM dbProj_Menu WHERE menuId = $id"; 
$row = $db->singleFetch($q);

if ($row) {
    echo '<form action="edit_menus.php" method="post">
        <br />
        <h3>Edit Menu: ' . $row->menuId . ' ' . $row->menuName . '</h3>
        <p><br />
           <p>Menu Name: <input type="text" name="menuName" value="' . $row->menuName . '" /></p>
           <p>Menu List: <input type="text" name="menuList" value="' . $row->menuList . '"/></p>
           <p>Price: <input type="number" name="price" value="' . $row->price . '"/></p>
        </p>
        <p><input type="submit" class="DB4Button" name="submit" value="update" /></p>
        <input type="hidden" name="submitted" value="TRUE">
        <input type="hidden" name="id" value="' . $id . '"/>
        </form>';
} else {
    echo '<p class="error">Menu not found.</p>';
}

include 'footer.html';
?>
