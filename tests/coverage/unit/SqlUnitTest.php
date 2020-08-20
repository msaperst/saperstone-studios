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
            $this->assertStringStartsWith('Failed to connect to MySQL: mysqli::__construct(): php_network_getaddresses: getaddrinfo failed: Name or service not known', $e->getMessage());
        } finally {
            putenv("DB_HOST=$DB_HOST");
        }
    }
}