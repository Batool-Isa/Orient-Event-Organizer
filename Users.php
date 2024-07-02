<?php
include 'Database.php';
  class Users {
    private $userId;
    private $username;
    private $userType;
    private $firstName;
    private $lastName;
    private $password;
    private $email;
    private $phoneNumber;
    
    public function __construct() {
        $this->userId = null;
        $this->username = null;
        $this->userType = null;
        $this->firstName = null;
        $this->lastName = null;
        $this->password = null;
        $this->email = null;
        $this->phoneNumber = null;
    }
    
    public function getUserId() {
        return $this->userId;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getUserType() {
        return $this->userType;
    }

    public function getFirstName() {
        return $this->firstName;
    }

    public function getLastName() {
        return $this->lastName;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getPhoneNumber() {
        return $this->phoneNumber;
    }

    public function setUserId($userId): void {
        $this->userId = $userId;
    }

    public function setUsername($username): void {
        $this->username = $username;
    }

    public function setUserType($userType): void {
        $this->userType = $userType;
    }

    public function setFirstName($firstName): void {
        $this->firstName = $firstName;
    }

    public function setLastName($lastName): void {
        $this->lastName = $lastName;
    }

    public function setPassword($password): void {
        $this->password = $password;
    }

    public function setEmail($email): void {
        $this->email = $email;
    }

    public function setPhoneNumber($phoneNumber): void {
        $this->phoneNumber = $phoneNumber;
    }

    public function deleteUser() {
        try {
            $db = Database::getInstance();
            $data = $db->querySQL('DELETE FROM dpProj_User WHERE userId = '.$this->userId);
            return true;
        } catch (Exception $e) {
            echo 'Exception: ' . $e;
            return false;
        }
    }

    public function initWithUid($userId) {
        $db = Database::getInstance();
        $data = $db->singleFetch('SELECT * FROM dbProj_User WHERE userId = '.$userId);
        $this->initWith($data->userId, $data->username, $data->userType, $data->firstName, $data->lastName, $data->password, $data->email, $data->phoneNumber);
    }
    
       public function initWithUserId($userId) {
        $db = Database::getInstance();
        $sql = "SELECT * FROM dbProj_User WHERE userId = ?";
        $params = [$userId];
        $userData = $db->singleFetch($sql, $params);
        if ($userData) {
            $this->userId = $userData->userId;
            $this->firstName = $userData->firstName;
            $this->lastName = $userData->lastName;
            $this->username = $userData->username;
            $this->userType = $userData->userType;
            $this->password = $userData->password;
            $this->email = $userData->email;
            $this->phoneNumber = $userData->phoneNumber;
            return true;
        } else {
            error_log("User data not found for userId: " . $userId);
        }
        return false;
    }


    public function checkUser($username, $password) {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM dbProj_User WHERE username = ? AND password = AES_ENCRYPT(?, "p0ly")');
        $stmt->bind_param('ss', $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_object();
        $decrypted_password = $db->singleFetch('SELECT AES_DECRYPT(password, "p0ly") as password FROM dbProj_User WHERE userId = ' . $data->userId)->password;
        $data->password = $decrypted_password;
        $this->initWith($data->userId, $data->username, $data->userType, $data->firstName, $data->lastName, $data->password, $data->email, $data->phoneNumber);
    }

    function initWithUsername() {

        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM dbProj_User WHERE username = ?');
        $stmt->bind_param('s', $this->username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return false;
        }
        return true;
    }
    
    public function registerUser() {
         if ($this->isValid()) {
            try {
                $db = Database::getInstance();
                $stmt = $db->prepare("INSERT INTO dbProj_User (username, userType, firstName, lastName, password, email, phoneNumber) 
                        VALUES (?, ?, ?, ?, AES_ENCRYPT(?, 'p0ly'), ?, ?)");
                $stmt->bind_param("sssssss", $this->username, $this->userType, $this->firstName, $this->lastName, $this->password, $this->email, $this->phoneNumber);
                $stmt->execute();
                return true;
            } catch (Exception $e) {
                echo 'Exception: ' . $e;
                return false;
            }
        } else {
            return false;
        }
    }

    private function initWith($userId, $username, $userType, $firstName, $lastName, $password, $email, $phoneNumber) {
        $this->userId = $userId;
        $this->username = $username;
        $this->userType = $userType;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->password = $password;
        $this->email = $email;
        $this->phoneNumber = $phoneNumber;
    }

    public function updateDB() {
        if ($this->isValid()) {
            $db = Database::getInstance();
            $data = $db->querySQL("UPDATE dbProj_User SET username='$this->username', firstName='$this->firstName', lastName='$this->lastName', password='$this->password', email='$this->email', phoneNumber='$this->phoneNumber' WHERE userId='$this->userId'");
        }
    }

    public function getAllUsers() {
        $db = Database::getInstance();
        $data = $db->multiFetch('SELECT * FROM dbProj_User');
        return $data;
    }

    public function isValid() {
        $errors = true;

        if (empty($this->username))
            $errors = false;

        if (empty($this->email))
            $errors = false;
        
        if (empty($this->password))
            $errors = false;

        return $errors;
    }
    
    
    public function updateUserDB() {
        if ($this->isValid()) {
            $db = Database::getInstance();
            $sql = "UPDATE dbProj_User SET firstName = ?, lastName = ?, username = ?, email = ?, phoneNumber = ? WHERE userId = ?";
            $params = [
                $this->firstName,
                $this->lastName,
                $this->username,
                $this->email,
                $this->phoneNumber,
                $this->userId
            ];
            return $db->querySQL($sql, $params);
        }
        return false;
    }
    function displayUname() {

        if (!empty($_SESSION['userId'])) {
            echo '<h5> ' . $_SESSION['username'] . ' : ' . $_SESSION['userType'] . ' </h5>';
        }
    }
}
