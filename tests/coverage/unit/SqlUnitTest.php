<?php

namespace coverage\unit;

use PHPUnit\Framework\TestCase;
use Sql;
use SqlException;

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class SqlUnitTest extends TestCase {

    private string $originalHost;

    protected function setUp(): void {
        parent::setUp();
        $this->originalHost = getenv('DB_HOST') ?: '';
    }

    protected function tearDown(): void {
        putenv("DB_HOST=$this->originalHost");
        parent::tearDown();
    }

    public function testBadHost(): void {
        putenv('DB_HOST=badhost');

        $this->expectException(SqlException::class);
        $this->expectExceptionMessageMatches('/php_network_getaddresses:/');

        new Sql();
    }
}