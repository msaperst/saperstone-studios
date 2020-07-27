<?php
class Sql {
    private $connected = false;
    private $mysqli;

    function __construct() {
        $this->mysqli = new mysqli ( getenv('DB_HOST') . ":" . getenv('DB_PORT'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'), getenv('DB_DATABASE') );
        if ($this->mysqli -> connect_errno) {
            throw new Exception( "Failed to connect to MySQL: " . $mysqli -> connect_error );
            exit();
        }
        $this->connected = true;
    }

    function disconnect() {
        $this->mysqli->close();
        $this->connected = false;
    }

    function isConnected() {
        return $this->connected;
    }

    function escapeString($string) {
        if( ! $this->connected ) {
            return $string;
        }
        return $this->mysqli->real_escape_string($string);
    }

    function getRow($selectStatement) {
        if ( ! $this->connected) {
            return array();
        }
        return $this->mysqli->query($selectStatement)->fetch_assoc();
    }

    function getRows($selectStatement) {
        $rows = array();
        if ( ! $this->connected) {
            return $rows;
        }
        $result = $this->mysqli->query($selectStatement);
        while( $row = $result->fetch_assoc() ) {
            array_push($rows, $row);
        }
        return $rows;
    }

    function getRowCount($selectStatement) {
        if ( ! $this->connected) {
            return 0;
        }
        return $this->mysqli->query($selectStatement)->num_rows;
    }

    function executeStatement($statement) {
        if ( ! $this->connected) {
            throw new Exception("Not connected, unable to execute statement: '" + $statement + "'");
        }
        $this->mysqli->query($statement);
        return $this->mysqli->insert_id;
    }
}
?>