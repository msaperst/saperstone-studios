<?php
class Sql {
    private $connected = false;
    private $mysqli;

    function __construct() {
        $this->mysqli = new mysqli ( getenv('DB_HOST') . ":" . getenv('DB_PORT'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME') );
        if ($this->mysqli -> connect_errno) {
            throw new Exception( "Failed to connect to MySQL: " . $mysqli -> connect_error );
            exit();
        }
        $this->connected = true;
    }

    function disconnect() {
        if( $this->connected ) {
            $this->mysqli->close();
            $this->connected = false;
        }
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
        if( $result == NULL ) {
            return $rows;
        }
        while( $row = $result->fetch_assoc() ) {
            array_push($rows, $row);
        }
        return $rows;
    }

    function getRowCount($selectStatement) {
        if ( ! $this->connected) {
            return 0;
        }
        $rows = $this->mysqli->query($selectStatement);
        if( $rows == NULL ) {
            return 0;
        }
        return $rows->num_rows;
    }

    function executeStatement($statement) {
        if ( ! $this->connected) {
            throw new Exception("Not connected, unable to execute statement: '" + $statement + "'");
        }
        $this->mysqli->query($statement);
        return $this->mysqli->insert_id;
    }

    function getEnumValues( $table, $field ) {
        $type = $this->mysqli->query( "SHOW COLUMNS FROM {$table} WHERE Field = '{$field}'" )->fetch_assoc()['Type'];
        preg_match("/^enum\(\'(.*)\'\)$/", $type, $matches);
        $enum = explode("','", $matches[1]);
        return $enum;
    }
}
?>