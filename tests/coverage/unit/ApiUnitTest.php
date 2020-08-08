<?php

namespace coverage\unit;

use Api;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class ApiUnitTest extends TestCase {

    private $api;

    public function setUp() {
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("disconnect")->willReturn(NULL);
        $mockSql->method("escapeString")->with($this->anything())->will($this->returnCallback(
            function ($entityName) {
                return $entityName;
            }
        ));
        $this->api = new Api($mockSql, null);
    }

    public function testRetrievePostIntNotSet() {
        $this->assertEquals(array('error' => 'Foo is required'), $this->api->retrievePostInt('bar', 'Foo'));
    }

    public function testRetrievePostIntBlank() {
        $_POST['bar'] = "";
        $this->assertEquals(array('error' => 'Foo can not be blank'), $this->api->retrievePostInt('bar', 'Foo'));
        unset($_POST);
    }

    public function testRetrievePostInt() {
        $_POST['bar'] = "5";
        $this->assertEquals(5, $this->api->retrievePostInt('bar', 'Foo'));
        unset($_POST);
    }

    public function testRetrievePostIntNotInt() {
        $_POST['bar'] = "abc";
        $this->assertEquals(0, $this->api->retrievePostInt('bar', 'Foo'));
        unset($_POST);
    }

    public function testRetrievePostIntMixed() {
        $_POST['bar'] = "3c";
        $this->assertEquals(3, $this->api->retrievePostInt('bar', 'Foo'));
        unset($_POST);
    }

    public function testRetrievePostFloat() {
        $_POST['bar'] = "5";
        $this->assertEquals(5, $this->api->retrievePostFloat('bar', 'Foo'));
        unset($_POST);
    }

    public function testRetrievePostFloatNotFloat() {
        $_POST['bar'] = "abc";
        $this->assertEquals(0, $this->api->retrievePostFloat('bar', 'Foo'));
        unset($_POST);
    }

    public function testRetrievePostFloatMixed() {
        $_POST['bar'] = "3c";
        $this->assertEquals(3, $this->api->retrievePostFloat('bar', 'Foo'));
        unset($_POST);
    }

    public function testRetrievePostFloatMoney() {
        $_POST['bar'] = "$12.245";
        $this->assertEquals(12.245, $this->api->retrievePostFloat('bar', 'Foo'));
        unset($_POST);
    }

    public function testRetrievePostString() {
        $_POST['bar'] = "foo";
        $this->assertEquals('foo', $this->api->retrievePostString('bar', 'Foo'));
        unset($_POST);
    }

    public function testRetrieveValidatedPostNotSet() {
        $this->assertEquals(array('error' => "Foo is required"), $this->api->retrieveValidatedPost('bar', 'Foo', NULL));
    }

    public function testRetrieveValidatedPostBlank() {
        $_POST['bar'] = "";
        $this->assertEquals(array('error' => "Foo can not be blank"), $this->api->retrieveValidatedPost('bar', 'Foo', NULL));
        unset($_POST);
    }

    public function testRetrieveValidatedPostNullValidation() {
        $_POST['bar'] = "foo";
        $this->assertEquals(array('error' => "Foo is not valid"), $this->api->retrieveValidatedPost('bar', 'Foo', NULL));
        unset($_POST);
    }

    public function testRetrieveValidatedPostBadFormat() {
        $_POST['bar'] = "foo";
        $this->assertEquals(array('error' => "Foo is not valid"), $this->api->retrieveValidatedPost('bar', 'Foo', FILTER_VALIDATE_BOOLEAN));
        unset($_POST);
    }

    public function testRetrieveValidatedPost() {
        $_POST['bar'] = "msaperst+sstest@gmail.com";
        $this->assertEquals("msaperst+sstest@gmail.com", $this->api->retrieveValidatedPost('bar', 'Foo', FILTER_VALIDATE_EMAIL));
        unset($_POST);
    }

    public function testRetrievePostDateTimeNotSet() {
        $this->assertEquals(array('error' => "Foo is required"), $this->api->retrievePostDateTime('bar', 'Foo', NULL));
    }

    public function testRetrievePostDateTimeBlank() {
        $_POST['bar'] = "";
        $this->assertEquals(array('error' => "Foo can not be blank"), $this->api->retrievePostDateTime('bar', 'Foo', NULL));
        unset($_POST);
    }

    public function testRetrievePostDateTimeNullFormat() {
        $_POST['bar'] = "foo";
        $this->assertEquals(array('error' => "Foo is not the correct format"), $this->api->retrievePostDateTime('bar', 'Foo', NULL));
        unset($_POST);
    }

    public function testRetrievePostDateTimeBadFormat() {
        $_POST['bar'] = "foo";
        $this->assertEquals(array('error' => "Foo is not the correct format"), $this->api->retrievePostDateTime('bar', 'Foo', 'Y-m-d'));
        unset($_POST);
    }

    public function testRetrievePostDateTime() {
        $_POST['bar'] = "2020-01-01";
        $this->assertEquals("2020-01-01", $this->api->retrievePostDateTime('bar', 'Foo', 'Y-m-d'));
        unset($_POST);
    }

    public function testRetrieveGetIntNotSet() {
        $this->assertEquals(array('error' => 'Foo is required'), $this->api->retrieveGetInt('bar', 'Foo'));
    }

    public function testRetrieveGetIntBlank() {
        $_GET['bar'] = "";
        $this->assertEquals(array('error' => 'Foo can not be blank'), $this->api->retrieveGetInt('bar', 'Foo'));
        unset($_GET);
    }

    public function testRetrieveGetInt() {
        $_GET['bar'] = "5";
        $this->assertEquals(5, $this->api->retrieveGetInt('bar', 'Foo'));
        unset($_GET);
    }

    public function testRetrieveGetIntNotInt() {
        $_GET['bar'] = "abc";
        $this->assertEquals(0, $this->api->retrieveGetInt('bar', 'Foo'));
        unset($_GET);
    }

    public function testRetrieveGetIntMixed() {
        $_GET['bar'] = "3c";
        $this->assertEquals(3, $this->api->retrieveGetInt('bar', 'Foo'));
        unset($_GET);
    }

    public function testRetrieveGetFloat() {
        $_GET['bar'] = "5";
        $this->assertEquals(5, $this->api->retrieveGetFloat('bar', 'Foo'));
        unset($_GET);
    }

    public function testRetrieveGetFloatNotFloat() {
        $_GET['bar'] = "abc";
        $this->assertEquals(0, $this->api->retrieveGetFloat('bar', 'Foo'));
        unset($_GET);
    }

    public function testRetrieveGetFloatMixed() {
        $_GET['bar'] = "3c";
        $this->assertEquals(3, $this->api->retrieveGetFloat('bar', 'Foo'));
        unset($_GET);
    }

    public function testRetrieveGetFloatMoney() {
        $_GET['bar'] = "$12.245";
        $this->assertEquals(12.245, $this->api->retrieveGetFloat('bar', 'Foo'));
        unset($_GET);
    }

    public function testRetrieveGetString() {
        $_GET['bar'] = "foo";
        $this->assertEquals('foo', $this->api->retrieveGetString('bar', 'Foo'));
        unset($_GET);
    }
}
