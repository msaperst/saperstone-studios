<?php

require_once "autoloader.php";

class Sql {
    /**
     * @var bool
     */
    private $connected = false;
    /**
     * @var mysqli
     */
    private $mysqli;

    /**
     * Sql constructor.
     * @throws SqlException
     */
    function __construct() {
        try {
            $this->mysqli = new mysqli (getenv('DB_HOST') . ":" . getenv('DB_PORT'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));
        } catch (Exception $e) {
            throw new SqlException("Failed to connect to MySQL: " . $e);
        }
        $this->connected = true;
    }

    function disconnect() {
        if ($this->connected) {
            $this->mysqli->close();
            $this->connected = false;
        }
    }

    /**
     * @return bool
     */
    function isConnected(): bool {
        return $this->connected;
    }

    /**
     * @param $string
     * @return string
     */
    function escapeString($string): string {
        if (!$this->connected) {
            return $string;
        }
        return $this->mysqli->real_escape_string($string);
    }

    /**
     * @param $selectStatement
     * @return array|null
     */
    function getRow($selectStatement): ?array {
        if (!$this->connected) {
            return array();
        }
        return $this->mysqli->query($selectStatement)->fetch_assoc();
    }

    /**
     * @param $selectStatement
     * @return array
     */
    function getRows($selectStatement): array {
        $rows = array();
        if (!$this->connected) {
            return $rows;
        }
        $result = $this->mysqli->query($selectStatement);
        if ($result == NULL) {
            return $rows;
        }
        while ($row = $result->fetch_assoc()) {
            array_push($rows, $row);
        }
        return $rows;
    }

    /**
     * @param $selectStatement
     * @return int
     */
    function getRowCount($selectStatement): int {
        if (!$this->connected) {
            return 0;
        }
        $rows = $this->mysqli->query($selectStatement);
        if ($rows == NULL) {
            return 0;
        }
        return $rows->num_rows;
    }

    /**
     * @param $statement
     * @return string
     * @throws SqlException
     */
    function executeStatement($statement): string {
        if (!$this->connected) {
            throw new SqlException("Not connected, unable to execute statement: '$statement'");
        }
        $this->mysqli->query($statement);
        return $this->mysqli->insert_id;
    }

    /**
     * @param $table
     * @param $field
     * @return string[]
     */
    function getEnumValues($table, $field): array {
        if (!$this->connected) {
            return array();
        }
        $type = $this->mysqli->query("SHOW COLUMNS FROM {$table} WHERE Field = '{$field}'")->fetch_assoc()['Type'];
        preg_match("/^enum\(\'(.*)\'\)$/", $type, $matches);
        return explode("','", $matches[1]);
    }
}