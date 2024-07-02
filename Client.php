<?php

class Client {
    private $clientId;
    private $userId;
    private $companyName;
    private $royaltyPoints;

    public function __construct() {
        $this->clientId = null;
        $this->userId = null;
        $this->companyName = null;
        $this->royaltyPoints = 0; // Default value
    }

    // Getters and Setters
    public function getClientId() {
        return $this->clientId;
    }

    public function setClientId($clientId) {
        $this->clientId = $clientId;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function setUserId($userId) {
        $this->userId = $userId;
    }

    public function getCompanyName() {
        return $this->companyName;
    }

    public function setCompanyName($companyName) {
        $this->companyName = $companyName;
    }

    public function getRoyaltyPoints() {
        return $this->royaltyPoints;
    }

    public function setRoyaltyPoints($royaltyPoints) {
        $this->royaltyPoints = $royaltyPoints;
    }

    // Database methods
    public function registerClient($user) {
        if ($user->isValid() && $this->companyName !== null) {
            try {
                if ($user->registerUser()) {
                    $this->userId = Database::getInstance()->getLastInsertId();

                    $db = Database::getInstance();
                    $sql = "INSERT INTO dbProj_Client (userId, companyName, royaltyPoints) VALUES (?, ?, ?)";
                    $params = [
                        $this->userId,
                        $this->companyName,
                        $this->royaltyPoints
                    ];
                    $stmt = $db->querySQL($sql, $params);
                    if ($stmt) {
                        $this->clientId = $db->getLastInsertId();
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            } catch (Exception $e) {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getAllClientsWithUserDetails() {
        $db = Database::getInstance();
        $sql = "
            SELECT c.clientId, c.companyName, c.royaltyPoints, 
                   u.userId, u.firstName, u.lastName, u.username, u.email, u.phoneNumber
            FROM dbProj_Client c
            JOIN dbProj_User u ON c.userId = u.userId
        ";
        return $db->multiFetch($sql);
    }

 public function updateClientDB() {
        if (!empty($this->clientId) && $this->companyName !== null) {
            $db = Database::getInstance();
            $sql = "UPDATE dbProj_Client SET companyName = ?, royaltyPoints = ? WHERE clientId = ?";
            $params = [
                $this->companyName,
                $this->royaltyPoints,
                $this->clientId
            ];
            $result = $db->querySQL($sql, $params);
            if (!$result) {
                error_log("Error updating client: " . $db->error);
            }
            return $result;
        }
        error_log("Invalid data for updating client");
        return false;
    }

    public function initWithClientId($clientId) {
        $db = Database::getInstance();
        $sql = "SELECT * FROM dbProj_Client WHERE clientId = ?";
        $params = [$clientId];
        $clientData = $db->singleFetch($sql, $params);

        if ($clientData) {
            $this->clientId = $clientData->clientId;
            $this->userId = $clientData->userId;
            $this->companyName = $clientData->companyName;
            $this->royaltyPoints = $clientData->royaltyPoints;

            // Fetch user details
            $user = new Users();
            if ($user->initWithUserId($this->userId)) {
                return $user;
            } else {
                error_log("User data not found for userId: " . $this->userId);
            }
        } else {
            error_log("Client data not found for clientId: " . $clientId);
        }
        return false;
    }
}

?>
