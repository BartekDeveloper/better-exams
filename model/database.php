<?php
include_once "tables.php";

define("_CONFIG", [
    "DB_HOST" => "localhost",
    "DB_USER" => "root",
    "DB_PASS" => "",
    "DB_NAME" => "exams"
]);

class Database {
    static private ?mysqli $connection = null;

    static public function Connect() {
        if(self::$connection == null) {
            self::$connection = mysqli_connect(_CONFIG["DB_HOST"], _CONFIG["DB_USER"], _CONFIG["DB_PASS"], _CONFIG["DB_NAME"]);
        }

        return self::$connection;
    }

    static public function Disconnect() {
        if(self::$connection != null) {
            self::$connection->close();
            self::$connection = null;
        }
    }

    static public function Query(string $sql) {
        return self::$connection->query($sql);
    }

    static public function FetchNext_Array(mysqli_result $result): array|null|false {
        return $array = mysqli_fetch_array($result);
    } 

    static public function FetchNext_Assoc(mysqli_result $result): array|null|false {
        return $array = mysqli_fetch_assoc($result);
    }

    static public function FetchNext_Numeric(mysqli_result $result): array|null|false {
        return $array = mysqli_fetch_row($result);
    }

    public function __construct() {
        self::Connect();
    }

    public function __destruct() {
        self::Disconnect();
    }

    static public function Prepare(string $sql) {
        return self::$connection->prepare($sql);
    }

    static public function Assign(mysqli_stmt $stmt, string $types, ...$args) {
        return $stmt->bind_param($types, ...$args);
    }

    static public function Execute(string $sql) {
        // Use multi_query to execute multiple statements
        $success = self::$connection->multi_query($sql);
        
        if ($success) {
            // Clear all results
            while (self::$connection->next_result()) {
                if ($result = self::$connection->store_result()) {
                    $result->free();
                }
            }
        }
        
        return $success;
    }

    static public function Get(string $sql) {
        $result = self::$connection->query($sql);
        return $result->fetch_assoc();
    }

    static public function GetAll(string $sql) {
        $result = self::$connection->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    static public function GetCount(string $sql) {
        $result = self::$connection->query($sql);
        return $result->num_rows;
    }

    static public function GetLastId() {
        return self::$connection->insert_id;
    }

    static public function GetAffectedRows() {
        return self::$connection->affected_rows;
    }
};