<?php
class Sql {
    var $params;
    public $db;
    function __construct() {
        $this->params = parse_ini_file ( "env.ini" );
    }
    function connect() {
        $this->db = mysqli_connect ( $this->params ['db.host'], $this->params ['db.username'], $this->params ['db.password'], $this->params ['db.database'] );
        if (! $this->db) {
            echo "Error: Unable to connect to MySQL." . PHP_EOL;
            echo "Debugging errno: " . mysqli_connect_errno () . PHP_EOL;
            echo "Debugging error: " . mysqli_connect_error () . PHP_EOL;
            exit ( 1 );
        }
    }
    function disconnect() {
        mysqli_close ( $this->db );
    }
}
?>