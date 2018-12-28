<?php
class Sql {
    private $params;
    private $connected = false;
    public $db;
    function connect() {
        $this->db = mysqli_connect ( getenv('DB_HOST') . ":" . getenv('DB_PORT'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'), getenv('DB_DATABASE') );
        if (! $this->db) {
            echo "Error: Unable to connect to MySQL." . PHP_EOL;
            echo "Debugging errno: " . mysqli_connect_errno () . PHP_EOL;
            echo "Debugging error: " . mysqli_connect_error () . PHP_EOL;
            exit ( 1 );
        }
        $this->connected = true;
    }
    function disconnect() {
        mysqli_close ( $this->db );
        $this->connected = false;
    }
    function isConnected() {
        return $this->connected;
    }
}
?>