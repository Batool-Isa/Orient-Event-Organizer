<?php

include "debugging.php";
include 'header.html';

echo '<h1> Menus </h1>';

$menu = new Menu();
$row = $menu->getAllMenus();


if (!empty($row)) {
    echo '<br />';
    //display a table of results
    echo '<table align="center" cellspacing="2" cellpadding="4" width="75%">';
    echo '<tr bgcolor="#87CEEB">
          <td><b>Edit</b></td>
          <td><b>Delete</b></td>
          <td><b><a href="view_menus.php">Menu Name</a></b></td>
          <td><b><a href="view_menus.php">Menu List</a></b></td>
          <td><b><a href="view_menus.php">Price</a></b></td></tr>';

    //use the following to set alternate backgrounds 
    $bg = '#eeeeee';

    for ($i = 0; $i < count($row); $i++) {
        $bg = ($bg == '#eeeeee' ? '#ffffff' : '#eeeeee');
        echo '<tr bgcolor="' . $bg . '">
            <td><a href="edit_menus.php?id=' . $row[$i]->menuId . '">Edit</a></td>
            <td><a href="delete_menu.php?id=' . $row[$i]->menuId . '">Delete</a></td>
            <td>' . $row[$i]->menuName . '</td>
            <td>' . $row[$i]->menuList . '</td>
            <td>' . $row[$i]->price . '</td>
          </tr>';
    }
    echo '</table>';
} else {
    echo '<p class="error">Oh dear. There was an error</p>';
}

include 'footer.html';

?>
