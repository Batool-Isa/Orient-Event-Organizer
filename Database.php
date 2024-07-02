<?php
class Database {
    public static $instance = null;
    public $dblink = null;

    public static function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    function __construct() {
        if (is_null($this->dblink)) {
            $this->connect();
        }
    }
     public function getConnection() {
        return $this->dblink;
    }

    function connect() {
        $this->dblink = new mysqli('localhost', 'u202001753', 'u202001753', 'db202001753');
        if ($this->dblink->connect_error) {
            die('CAN NOT CONNECT: ' . $this->dblink->connect_error);
        } /* else {
            echo 'Connected successfully to the database.<br>'; // Debugging line
        }*/
    }

    function __destruct() {
        if (!is_null($this->dblink)) {
            $this->close();
        }
    }

    function close() {
        $this->dblink->close();
    }
    
    

    function querySQL($sql, $params = []) {
        if ($sql != null && $sql != '') {
            $stmt = $this->dblink->prepare($sql);
            if ($params) {
                $types = str_repeat('s', count($params));
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            return $stmt;
        }
        return null;
    }

    function singleFetch($sql, $params = []) {
        $fet = null;
        if ($sql != null && $sql != '') {
            $stmt = $this->querySQL($sql, $params);
            $result = $stmt->get_result();
            $fet = $result->fetch_object();
        }
        return $fet;
    }

    function multiFetch($sql, $params = []) {
        $result = [];
        if ($sql != null && $sql != '') {
            $stmt = $this->querySQL($sql, $params);
            $res = $stmt->get_result();
            while ($fet = $res->fetch_object()) {
                $result[] = $fet;
            }
            //echo 'Fetched ' . count($result) . ' rows.<br>'; // Debugging line
        }
        return $result;
    }

    function mkSafe($string) {
        return $this->dblink->real_escape_string($string);
    }

    function getRows($sql, $params = []) {
        $rows = 0;
        if ($sql != null && $sql != '') {
            $stmt = $this->querySQL($sql, $params);
            $res = $stmt->get_result();
            $rows = $res->num_rows;
        }
        return $rows;
    }

    public function getLastInsertId() {
        return $this->dblink->insert_id;
    } 
  public function prepare($query) {
        return $this->dblink->prepare($query);
    }
    
    /*
         function prepare($query) {
        return mysqli_prepare($this->dblink, $query);
    }
     */
}
?>
