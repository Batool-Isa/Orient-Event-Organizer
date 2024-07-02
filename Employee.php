<?php

class Employee {
    private $employeeId;
    private $userId;
    private $department;
    private $position;
    private $joinDate;

    public function __construct() {
        $this->employeeId = null;
        $this->userId = null;
        $this->department = null;
        $this->position = null;
        $this->joinDate = null;
    }

    // Getters and Setters
    public function getEmployeeId() {
        return $this->employeeId;
    }

    public function setEmployeeId($employeeId) {
        $this->employeeId = $employeeId;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function setUserId($userId) {
        $this->userId = $userId;
    }

    public function getDepartment() {
        return $this->department;
    }

    public function setDepartment($department) {
        $this->department = $department;
    }

    public function getPosition() {
        return $this->position;
    }

    public function setPosition($position) {
        $this->position = $position;
    }

    public function getJoinDate() {
        return $this->joinDate;
    }

    public function setJoinDate($joinDate) {
        $this->joinDate = $joinDate;
    }

    // Database methods
    public function registerEmployee() {
        if ($this->isValid()) {
            try {
                $db = Database::getInstance();
                $sql = "INSERT INTO dbProj_Employee (userId, department, position, joinDate) VALUES (?, ?, ?, ?)";
                $params = [
                    $this->userId,
                    $this->department,
                    $this->position,
                    $this->joinDate
                ];
                $stmt = $db->querySQL($sql, $params);
                if ($stmt) {
                    $this->employeeId = $db->getLastInsertId();
                    return true;
                } else {
                    error_log("Error registering employee: " . $db->error);
                    return false;
                }
            } catch (Exception $e) {
                error_log("Exception registering employee: " . $e->getMessage());
                return false;
            }
        } else {
            error_log("Invalid data for registering employee");
            return false;
        }
    }

    public function getAllEmployeesWithUserDetails() {
        $db = Database::getInstance();
        $sql = "
            SELECT e.employeeId, e.department, e.position, e.joinDate,
                   u.userId, u.firstName, u.lastName, u.email
            FROM dbProj_Employee e
            JOIN dbProj_User u ON e.userId = u.userId
        ";
        return $db->multiFetch($sql);
    }

    public function updateEmployeeDB() {
        if (!empty($this->employeeId) && $this->department !== null && $this->position !== null && $this->joinDate !== null) {
            $db = Database::getInstance();
            $sql = "UPDATE dbProj_Employee SET department = ?, position = ?, joinDate = ? WHERE employeeId = ?";
            $params = [
                $this->department,
                $this->position,
                $this->joinDate,
                $this->employeeId
            ];
            $result = $db->querySQL($sql, $params);
            if (!$result) {
                error_log("Error updating employee: " . $db->error);
            }
            return $result;
        }
        error_log("Invalid data for updating employee");
        return false;
    }

    public function initWithEmployeeId($employeeId) {
        $db = Database::getInstance();
        $sql = "SELECT * FROM dbProj_Employee WHERE employeeId = ?";
        $params = [$employeeId];
        $employeeData = $db->singleFetch($sql, $params);

        if ($employeeData) {
            $this->employeeId = $employeeData->employeeId;
            $this->userId = $employeeData->userId;
            $this->department = $employeeData->department;
            $this->position = $employeeData->position;
            $this->joinDate = $employeeData->joinDate;

            // Fetch user details
            $user = new Users();
            if ($user->initWithUserId($this->userId)) {
                return $user;
            } else {
                error_log("User data not found for userId: " . $this->userId);
            }
        } else {
            error_log("Employee data not found for employeeId: " . $employeeId);
        }
        return false;
    }

    public function isValid() {
        return !empty($this->userId) && !empty($this->department) && !empty($this->position) && !empty($this->joinDate);
    }
}

?>
