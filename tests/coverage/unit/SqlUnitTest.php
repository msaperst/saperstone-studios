<?php

namespace coverage\unit;

use Exception;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class SqlUnitTest extends TestCase {

    public function testBadHost() {
        $DB_HOST = getenv('DB_HOST');
        putenv('DB_HOST=badhost');
        try {
            new Sql();
        } catch (Exception $e) {
            $this->assertEquals('Failed to connect to MySQL: mysqli::__construct(): php_network_getaddresses: getaddrinfo failed: Name or service not known

/home/max/workspace/saperstone-studios/src/Sql.php:9
/home/max/workspace/saperstone-studios/tests/coverage/unit/SqlUnitTest.php:17
', $e->getMessage());
        } finally {
            putenv("DB_HOST=$DB_HOST");
        }
    }
}