<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

/**
 * Description of Menu
 *
 * @author DELL
 */
class Menu {
     private $menuId;
    private $menuName;
    private $menuList;
    private $price;
  

    public function __construct() {
        $this->menuId = null;
        $this->menuName = null;
        $this->menuList = null;
        $this->price = null;
      
    }

    // Getters and Setters
    public function getMenuId() {
        return $this->menuId;
    }

    public function getMenuName() {
        return $this->menuName;
    }

    public function getMenuList() {
        return $this->menuList;
    }

    public function getPrice() {
        return $this->price;
    }

    public function setMenuId($menuId): void {
        $this->menuId = $menuId;
    }

    public function setMenuName($menuName): void {
        $this->menuName = $menuName;
    }

    public function setMenuList($manuList): void {
        $this->menuList = $manuList;
    }

    public function setPrice($price): void {
        $this->price = $price;
    }

    
    
   //Database methods
    public function registerMenu() {
        if ($this->isValid()) {
            try {
                $db = Database::getInstance();
                $data = $db->querySql("INSERT INTO dbProj_Menu (menuName, menuList, price) VALUES ('$this->menuName', '$this->menuList', '$this->price')");
                return true;
            } catch (Exception $e) {
                echo 'Exception: ' . $e;
                return false;
            }
        } else {
            return false;
        }
    }

    public function initWithMenuName() {
        $db = Database::getInstance();
        $data = $db->singleFetch('SELECT * FROM dbProj_Menu WHERE menuName = \'' . $this->menuName . '\'');
        if ($data) {
            $this->initWith($data->menuId, $data->menuName, $data->menuList, $data->price);
            return true;
        } else {
            return false;
        }
    }

  public function initWithmenuId($menuId) {
  $db = Database::getInstance();
        $query = "SELECT * FROM dbProj_Menu WHERE menuId = ?";
        $stmt = $db->prepare($query);
        if ($stmt === false) {
            echo 'Prepare failed: ' . mysqli_error($db->getConnection());
            return false;
        }
        $stmt->bind_param('i', $menuId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_object();
        $stmt->close();

        if ($data) {
            $this->initWith($data->menuId, $data->menuName, $data->menuList, $data->price);
            return true;
        } else {
            return false;
        }
}

private function initWith($menuId, $menuName, $menuList, $price) {
    $this->menuId = $menuId;
    $this->menuName = $menuName;
    $this->menuList = $menuList;
    $this->price = $price;
}

       
    

    public function updateDB() {
       /* if ($this->isValid()) {
            $db = Database::getInstance();
            $data = $db->querySql("UPDATE dbProj_Menu SET menuName='$this->menuName', menuList='$this->menuList', price='$this->price'WHERE menuId='$this->menuId'");
        }*/
 
        if ($this->isValid()) {
            $db = Database::getInstance();
            $query = "UPDATE dbProj_Menu SET menuName=?, menuList=?, price=? WHERE menuId=?";
            
            $stmt = $db->prepare($query);
            if ($stmt === false) {
                die('Prepare failed: ' . mysqli_error($db->dblink)); // Debugging line
            }

            // Bind the parameters to the query
            mysqli_stmt_bind_param($stmt, 'ssdi', $this->menuName, $this->menuList, $this->price, $this->menuId);

            // Execute the statement
            $result = mysqli_stmt_execute($stmt);
            if ($result) {
                echo 'Menu updated successfully.<br>';
                return true; // Return true if the update was successful
            } else {
                echo 'Error updating menu: ' . mysqli_error($db->dblink) . '<br>'; // Debugging line
                return false; // Return false if there was an error
            }

            // Close the statement
            mysqli_stmt_close($stmt);
        }
        return false; // Return false if the validation fails
    
    }

    public function deleteMenu() {
      try {
          
        $db = Database::getInstance();
        $data = $db->querySQL("DELETE FROM dbProj_Menu WHERE menuId='$this->menuId'");
        if ($data) {
            return true;
        } else {
            echo 'Error deleting the menu: ' . mysqli_error($db->dblink); // Debugging line
            return false;
        }
    } catch (Exception $e) {
        echo 'Exception: ' . $e->getMessage();
        return false;
    }
    }

    public function getAllMenus() {
        $db = Database::getInstance();
        $data = $db->multiFetch("SELECT * FROM dbProj_Menu");
        return $data;
    }

    public function isValid() {
        return !empty($this->menuName) && !empty($this->menuList) && !empty($this->price);
    }
    
}
