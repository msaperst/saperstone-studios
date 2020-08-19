<?php

namespace coverage\unit;

use Api;
use Exception;
use PHPUnit\Framework\TestCase;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class ApiUnitTest extends TestCase {

    private $api;

    public function setUp() {
        $this->api = new Api();
    }

    public function testRetrievePostIntNotSet() {
        try {
            $this->api->retrievePostInt('bar', 'Foo');
        } catch (Exception $e) {
            $this->assertEquals('Foo is required', $e->getMessage());
        }
    }

    public function testRetrievePostIntBlank() {
        $_POST['bar'] = "";
        try {
            $this->api->retrievePostInt('bar', 'Foo');
        } catch (Exception $e) {
            $this->assertEquals('Foo can not be blank', $e->getMessage());
        }
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

    public function testRetrieveValidatedPostNotSet() {
        try {
            $this->api->retrieveValidatedPost('bar', 'Foo', NULL);
        } catch (Exception $e) {
            $this->assertEquals('Foo is required', $e->getMessage());
        }
    }

    public function testRetrieveValidatedPostBlank() {
        $_POST['bar'] = "";
        try {
            $this->api->retrieveValidatedPost('bar', 'Foo', NULL);
        } catch (Exception $e) {
            $this->assertEquals("Foo can not be blank", $e->getMessage());
        }
        unset($_POST);
    }

    public function testRetrieveValidatedPostNullValidation() {
        $_POST['bar'] = "foo";
        try {
            $this->api->retrieveValidatedPost('bar', 'Foo', NULL);
        } catch (Exception $e) {
            $this->assertEquals("Foo is not valid", $e->getMessage());
        }
        unset($_POST);
    }

    public function testRetrieveValidatedPostBadFormat() {
        $_POST['bar'] = "foo";
        try {
            $this->api->retrieveValidatedPost('bar', 'Foo', FILTER_VALIDATE_BOOLEAN);
        } catch (Exception $e) {
            $this->assertEquals("Foo is not valid", $e->getMessage());
        }
        unset($_POST);
    }

    public function testRetrievePostDateTimeNotSet() {
        try {
            $this->api->retrievePostDateTime('bar', 'Foo', NULL);
        } catch (Exception $e) {
            $this->assertEquals("Foo is required", $e->getMessage());
        }
    }

    public function testRetrievePostDateTimeBlank() {
        $_POST['bar'] = "";
        try {
            $this->api->retrievePostDateTime('bar', 'Foo', NULL);
        } catch (Exception $e) {
            $this->assertEquals("Foo can not be blank", $e->getMessage());
        }
        unset($_POST);
    }

    public function testRetrieveGetIntNotSet() {
        try {
            $this->api->retrieveGetInt('bar', 'Foo');
        } catch (Exception $e) {
            $this->assertEquals("Foo is required", $e->getMessage());
        }
    }

    public function testRetrieveGetIntBlank() {
        $_GET['bar'] = "";
        try {
            $this->api->retrieveGetInt('bar', 'Foo');
        } catch (Exception $e) {
            $this->assertEquals("Foo can not be blank", $e->getMessage());
        }
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
}
