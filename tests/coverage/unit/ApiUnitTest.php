<?php
use PHPUnit\Framework\TestCase;

$_SERVER ['DOCUMENT_ROOT'] = dirname( dirname ( __DIR__ ) );
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/api.php";

class ApiUnitTest extends TestCase {

    private $api;

    public function setUp() {
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("disconnect")->willReturn(NULL);
        $mockSql->method("escapeString")->with('foo')->willReturn('foo');
        $this->api = new Api($mockSql, null);
    }

    public function testRetrievePostIntNotSet() {
        $this->assertEquals( array('error' => 'Foo is required' ), $this->api->retrievePostInt('bar', 'Foo', 'err') );
    }

    public function testRetrievePostIntBlank() {
        $_POST['bar'] = "";
        $this->assertEquals( array('error' => 'Foo can not be blank' ), $this->api->retrievePostInt('bar', 'Foo', 'err') );
        unset( $_POST );
    }

    public function testRetrievePostInt() {
        $_POST['bar'] = "5";
        $this->assertEquals( 5, $this->api->retrievePostInt('bar', 'Foo') );
        unset( $_POST );
    }

    public function testRetrievePostIntNotInt() {
        $_POST['bar'] = "abc";
        $this->assertEquals( 0, $this->api->retrievePostInt('bar', 'Foo') );
        unset( $_POST );
    }

    public function testRetrievePostIntMixed() {
        $_POST['bar'] = "3c";
        $this->assertEquals( 3, $this->api->retrievePostInt('bar', 'Foo') );
        unset( $_POST );
    }

    public function testRetrievePostFloat() {
        $_POST['bar'] = "5";
        $this->assertEquals( 5, $this->api->retrievePostFloat('bar', 'Foo') );
        unset( $_POST );
    }

    public function testRetrievePostFloatNotFloat() {
        $_POST['bar'] = "abc";
        $this->assertEquals( 0, $this->api->retrievePostFloat('bar', 'Foo') );
        unset( $_POST );
    }

    public function testRetrievePostFloatMixed() {
        $_POST['bar'] = "3c";
        $this->assertEquals( 3, $this->api->retrievePostFloat('bar', 'Foo') );
        unset( $_POST );
    }

    public function testRetrievePostFloatMoney() {
        $_POST['bar'] = "$12.245";
        $this->assertEquals( 12.245, $this->api->retrievePostFloat('bar', 'Foo') );
        unset( $_POST );
    }

    public function testRetrievePostString() {
        $_POST['bar'] = "foo";
        $this->assertEquals( 'foo', $this->api->retrievePostString('bar', 'Foo') );
        unset( $_POST );
    }
}
