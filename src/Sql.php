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
    function getRow($selectStatement, array $params = []): ?array {
        if (!$this->connected) {
            return array();
        }
        $result = $this->query($selectStatement, $params);
        if (!$result instanceof mysqli_result) {
            return null;
        }
        return $result->fetch_assoc();
    }

    /**
     * @param $selectStatement
     * @return array
     */
    function getRows($selectStatement, array $params = []): array {
        $rows = array();
        if (!$this->connected) {
            return $rows;
        }
        $result = $this->query($selectStatement, $params);
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
    function getRowCount($selectStatement, array $params = []): int {
        if (!$this->connected) {
            return 0;
        }
        $rows = $this->query($selectStatement, $params);
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
    function executeStatement($statement, array $params = []): string {
        if (!$this->connected) {
            throw new SqlException("Not connected, unable to execute statement: '$statement'");
        }
        $this->query($statement, $params);
        return $this->mysqli->insert_id;
    }

    /**
     * Executes SQL, using a prepared statement whenever values are supplied.
     * Keeping values separate from the statement ensures they can never be
     * interpreted as SQL syntax.
     *
     * @param string $statement
     * @param array $params
     * @return mysqli_result|bool
     */
    private function query(string $statement, array $params = []): mysqli_result|bool {
        $prepared = $this->mysqli->prepare($statement);
        $prepared->execute(array_values($params));
        $result = $prepared->get_result();
        return $result === false ? true : $result;
    }

    /**
     * Validates and quotes a table or column identifier. Identifiers cannot be
     * represented by SQL parameter placeholders, so they require an allowlist.
     */
    function quoteIdentifier(string $identifier): string {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $identifier)) {
            throw new InvalidArgumentException('Invalid SQL identifier');
        }
        return "`$identifier`";
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
        $table = $this->quoteIdentifier($table);
        $type = $this->query("SHOW COLUMNS FROM {$table} WHERE Field = ?", [$field])->fetch_assoc()['Type'];
        preg_match("/^enum\(\'(.*)\'\)$/", $type, $matches);
        return explode("','", $matches[1]);
    }
}
