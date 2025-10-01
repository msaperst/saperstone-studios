<?php

namespace coverage\unit;

use Exception;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class SqlUnitTest extends TestCase {

    public function testBadHost() {
        $DB_HOST = getenv('DB_HOST');
        putenv('DB_HOST=badhost');
        try {
            new Sql();
        } catch (Exception $e) {
            $this->assertStringStartsWith('Failed to connect to MySQL: mysqli_sql_exception: php_network_getaddresses: getaddrinfo for badhost failed:', $e->getMessage());
        } finally {
            putenv("DB_HOST=$DB_HOST");
        }
    }
}