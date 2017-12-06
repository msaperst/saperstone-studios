<?php
class Sql {
    private $params;
    private $connected = false;
    public $db;
    function __construct() {
        $this->params = parse_ini_file ( dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "config/env.ini" );
    }
    function connect() {
        $this->db = mysqli_connect ( $this->params ['db.host'], $this->params ['db.username'], $this->params ['db.password'], $this->params ['db.database'] );
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