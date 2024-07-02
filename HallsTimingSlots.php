<?php

class HallsTimingSlots {
    private $timingID;
    private $timingSlotStart;
    private $timingSlotEnd;
    private $hallId;

    public function __construct() {
        $this->timingID = null;
        $this->timingSlotStart = null;
        $this->timingSlotEnd = null;
        $this->hallId = null;
    }

    // Getters and Setters
    public function getTimingID() {
        return $this->timingID;
    }

    public function getTimingSlotStart() {
        return $this->timingSlotStart;
    }

    public function getTimingSlotEnd() {
        return $this->timingSlotEnd;
    }

    public function getHallId() {
        return $this->hallId;
    }

    public function setTimingID($timingID): void {
        $this->timingID = $timingID;
    }

    public function setTimingSlotStart($timingSlotStart): void {
        $this->timingSlotStart = $timingSlotStart;
    }

    public function setTimingSlotEnd($timingSlotEnd): void {
        $this->timingSlotEnd = $timingSlotEnd;
    }

    public function setHallId($hallId): void {
        $this->hallId = $hallId;
    }

    // Database methods
    public function registerTimingSlot() {
        if ($this->isValid()) {
            try {
                $db = Database::getInstance();
                $query = "INSERT INTO dpProj_HallsTimingSlots (timingSlotStart, timingSlotEnd, hallId) VALUES (?, ?, ?)";
                $stmt = $db->prepare($query);
                if ($stmt === false) {
                    throw new Exception('Prepare failed: ' . mysqli_error($db->dblink));
                }
                mysqli_stmt_bind_param($stmt, 'ssi', $this->timingSlotStart, $this->timingSlotEnd, $this->hallId);
                $result = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                return $result;
            } catch (Exception $e) {
                echo 'Exception: ' . $e->getMessage();
                return false;
            }
        } else {
            return false;
        }
    }

    public function initWithTimingID($timingID) {
        $db = Database::getInstance();
        $query = "SELECT * FROM dpProj_HallsTimingSlots WHERE timingID = ?";
        $stmt = $db->prepare($query);
        if ($stmt === false) {
            echo 'Prepare failed: ' . mysqli_error($db->dblink);
            return false;
        }
        mysqli_stmt_bind_param($stmt, 'i', $timingID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);
        if ($data) {
            $this->initWith($data->timingID, $data->timingSlotStart, $data->timingSlotEnd, $data->hallId);
            return true;
        } else {
            return false;
        }
    }

    private function initWith($timingID, $timingSlotStart, $timingSlotEnd, $hallId) {
        $this->timingID = $timingID;
        $this->timingSlotStart = $timingSlotStart;
        $this->timingSlotEnd = $timingSlotEnd;
        $this->hallId = $hallId;
    }

    public function updateDB() {
        if ($this->isValid()) {
            $db = Database::getInstance();
            $query = "UPDATE dpProj_HallsTimingSlots SET timingSlotStart=?, timingSlotEnd=?, hallId=? WHERE timingID=?";
            $stmt = $db->prepare($query);
            if ($stmt === false) {
                die('Prepare failed: ' . mysqli_error($db->dblink)); // Debugging line
            }

            // Bind the parameters to the query
            mysqli_stmt_bind_param($stmt, 'ssii', $this->timingSlotStart, $this->timingSlotEnd, $this->hallId, $this->timingID);

            // Execute the statement
            $result = mysqli_stmt_execute($stmt);
            if ($result) {
                echo 'Timing slot updated successfully.<br>';
                return true; // Return true if the update was successful
            } else {
                echo 'Error updating timing slot: ' . mysqli_error($db->dblink) . '<br>'; // Debugging line
                return false; // Return false if there was an error
            }

            // Close the statement
            mysqli_stmt_close($stmt);
        }
        return false; // Return false if the validation fails
    }

    public function deleteTimingSlot() {
        try {
            $db = Database::getInstance();
            $data = $db->querySQL("DELETE FROM dpProj_HallsTimingSlots WHERE timingID='$this->timingID'");
            if ($data) {
                return true;
            } else {
                echo 'Error deleting the timing slot: ' . mysqli_error($db->dblink); // Debugging line
                return false;
            }
        } catch (Exception $e) {
            echo 'Exception: ' . $e->getMessage();
            return false;
        }
    }

    public function getAllTimingSlots() {
        $db = Database::getInstance();
        $data = $db->multiFetch("SELECT * FROM dpProj_HallsTimingSlots");
        return $data;
    }

    public function isValid() {
        return !empty($this->timingSlotStart) && !empty($this->timingSlotEnd) && !empty($this->hallId);
    }

}
