<?php
include 'debugging.php';

if (isset($_POST['submitted'])) {
    $menu = new Menu();
    $menu->setMenuName($_POST['menuName']);
    $menu->setMenuList($_POST['menuList']);
    $menu->setPrice($_POST['price']);

    if ($menu->registerMenu()) {
        echo 'Menu added successfully';
    } else {
        echo '<p class="error">Error adding menu</p>';
    }
}

include 'header.html';
?>

<h1>Add Menu</h1>
<div id="stylized" class="myform"> 
    <form action="add_menu.php" method="post">
        <fieldset>
            <label>Menu Name</label>
            <input type="text" name="menuName" size="20" value="" />
            <label>Menu List</label>
            <textarea name="menuList" rows="4" cols="50"></textarea>
            <label>Price</label>
            <input type="text" name="price" size="20" value="" />
            <div align="center">
                <input type="submit" value="Add Menu" />
            </div>  
            <input type="hidden" name="submitted" value="1" />
        </fieldset>
    </form>    
    <div class="spacer"></div>    
</div>    

<?php
include 'footer.html';
?>
