<?php
use PHPUnit\Framework\TestCase;

$_SERVER ['DOCUMENT_ROOT'] = dirname( dirname ( __DIR__ ) );
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/api.php";

class ApiIntegrationTest extends TestCase {

    private $api;

    public function setUp() {
        $sql = new Sql();
        $this->api = new Api($sql, null);
    }

    public function testRetrievePostString() {
        $_POST['bar'] = "foo";
        $this->assertEquals( 'foo', $this->api->retrievePostString('bar', 'Foo') );
        unset( $_POST );
    }

    public function testRetrievePostStringApos() {
        $_POST['bar'] = "foo'";
        $this->assertEquals( 'foo\\\'', $this->api->retrievePostString('bar', 'Foo') );
        unset( $_POST );
    }

    public function testRetrievePostStringQuote() {
        $_POST['bar'] = "foo\"";
        $this->assertEquals( 'foo\\"', $this->api->retrievePostString('bar', 'Foo') );
        unset( $_POST );
    }

    public function testRetrievePostStringSlash() {
        $_POST['bar'] = "foo\\";
        $this->assertEquals( 'foo\\\\', $this->api->retrievePostString('bar', 'Foo') );
        unset( $_POST );
    }


}
