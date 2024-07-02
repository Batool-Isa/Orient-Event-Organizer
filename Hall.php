<?php

class Hall {
  
    private $hallId;
    private $hallName;
    private $image;
    private $location;
    private $description;
    private $rentalCharge;
    private $capacity;

    public function __construct() {
        $this->hallId = null;
        $this->hallName = null;
        $this->image = null;
        $this->location = null;
        $this->description = null;
        $this->rentalCharge = null;
        $this->capacity = null;
    }

    // Getters and Setters
    public function getHallId() {
        return $this->hallId;
    }

    public function getHallName() {
        return $this->hallName;
    }

    public function getImage() {
        return $this->image;
    }

    public function getLocation() {
        return $this->location;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getRentalCharge() {
        return $this->rentalCharge;
    }

    public function getCapacity() {
        return $this->capacity;
    }

    public function setHallId($hallId): void {
        $this->hallId = $hallId;
    }

    public function setHallName($hallName): void {
        $this->hallName = $hallName;
    }

    public function setImage($image): void {
        $this->image = $image;
    }

    public function setLocation($location): void {
        $this->location = $location;
    }

    public function setDescription($description): void {
        $this->description = $description;
    }

    public function setRentalCharge($rentalCharge): void {
        $this->rentalCharge = $rentalCharge;
    }

    public function setCapacity($capacity): void {
        $this->capacity = $capacity;
    }

    // Database methods
     public function registerHall() {
        if ($this->isValid()) {
            try {
                $db = Database::getInstance();
                $sql = "INSERT INTO dbProj_Hall (hallName, image, location, description, rentalCharge, capacity) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $db->prepare($sql);
                if ($stmt === false) {
                    throw new Exception('Prepare failed: ' . $db->error);
                }
                $stmt->bind_param('ssssdi', $this->hallName, $this->image, $this->location, $this->description, $this->rentalCharge, $this->capacity);
                $result = $stmt->execute();
                if ($result) {
                    echo 'Hall registered successfully.<br>';
                    return true;
                } else {
                    echo 'Error registering hall: ' . $stmt->error . '<br>';
                    return false;
                }
            } catch (Exception $e) {
                echo 'Exception registering hall: ' . $e->getMessage() . '<br>';
                return false;
            }
        } else {
            echo 'Invalid data for registering hall.<br>';
            return false;
        }
    }

    public function initWithHallName() {
        $db = Database::getInstance();
        $query = "SELECT * FROM dbProj_Hall WHERE hallName = ?";
        $stmt = $db->prepare($query);
        if ($stmt === false) {
            echo 'Prepare failed: ' . mysqli_error($db->dblink);
            return false;
        }
        mysqli_stmt_bind_param($stmt, 's', $this->hallName);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);
        if ($data) {
            $this->initWith($data->hallId, $data->hallName, $data->image, $data->location, $data->description, $data->rentalCharge, $data->capacity);
            return true;
        } else {
            return false;
        }
    }

    public function initWithHallId($hallId) {
        $db = Database::getInstance();
        $query = "SELECT * FROM dbProj_Hall WHERE hallId = ?";
        $stmt = $db->prepare($query);
        if ($stmt === false) {
            echo 'Prepare failed: ' . mysqli_error($db->dblink);
            return false;
        }
        mysqli_stmt_bind_param($stmt, 'i', $hallId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);
        if ($data) {
            $this->initWith($data->hallId, $data->hallName, $data->image, $data->location, $data->description, $data->rentalCharge, $data->capacity);
            return true;
        } else {
            return false;
        }
    }

    private function initWith($hallId, $hallName, $image, $location, $description, $rentalCharge, $capacity) {
        $this->hallId = $hallId;
        $this->hallName = $hallName;
        $this->image = $image;
        $this->location = $location;
        $this->description = $description;
        $this->rentalCharge = $rentalCharge;
        $this->capacity = $capacity;
    }

    public function updateDB() {
        if ($this->isValid()) {
            $db = Database::getInstance();
            $query = "UPDATE dbProj_Hall SET hallName=?, image=?, location=?, description=?, rentalCharge=?, capacity=? WHERE hallId=?";
            $stmt = $db->prepare($query);
            if ($stmt === false) {
                die('Prepare failed: ' . mysqli_error($db->dblink)); // Debugging line
            }

            // Bind the parameters to the query
            mysqli_stmt_bind_param($stmt, 'ssssdii', $this->hallName, $this->image, $this->location, $this->description, $this->rentalCharge, $this->capacity, $this->hallId);

            // Execute the statement
            $result = mysqli_stmt_execute($stmt);
            if ($result) {
                echo 'Hall updated successfully.<br>';
                return true; // Return true if the update was successful
            } else {
                echo 'Error updating hall: ' . mysqli_error($db->dblink) . '<br>'; // Debugging line
                return false; // Return false if there was an error
            }

            // Close the statement
            mysqli_stmt_close($stmt);
        }
        return false; // Return false if the validation fails
    }

    public function deleteHall() {
        try {
            $db = Database::getInstance();
            $data = $db->querySQL("DELETE FROM dbProj_Hall WHERE hallId='$this->hallId'");
            if ($data) {
                return true;
            } else {
                echo 'Error deleting the hall: ' . mysqli_error($db->dblink); // Debugging line
                return false;
            }
        } catch (Exception $e) {
            echo 'Exception: ' . $e->getMessage();
            return false;
        }
    }

    public function getAllHalls() {
        $db = Database::getInstance();
        $data = $db->multiFetch("SELECT * FROM dbProj_Hall");
        return $data;
    }

    public function getTimingSlotsByHallId($hallId) {
        $db = Database::getInstance();
        $query = "SELECT * FROM dpProj_HallsTimingSlots WHERE hallId = ?";
        $stmt = $db->prepare($query);
        if ($stmt === false) {
            echo 'Prepare failed: ' . mysqli_error($db->dblink);
            return false;
        }
        mysqli_stmt_bind_param($stmt, 'i', $hallId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $timingSlots = [];
        while ($slot = mysqli_fetch_object($result)) {
            $timingSlots[] = $slot;
        }
        mysqli_stmt_close($stmt);
        return $timingSlots;
    }

    public function isValid() {
        return !empty($this->hallName) && !empty($this->image) && !empty($this->location) && !empty($this->rentalCharge) && !empty($this->capacity);
    }

}
?>
