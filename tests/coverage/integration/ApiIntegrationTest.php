<?php

namespace coverage\integration;

use Api;
use Exception;
use PHPUnit\Framework\TestCase;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class ApiIntegrationTest extends TestCase {

    private $api;

    public function setUp() {
        $this->api = new Api();
    }

    public function testRetrievePostString() {
        $_POST['bar'] = "foo";
        $this->assertEquals('foo', $this->api->retrievePostString('bar', 'Foo'));
        unset($_POST);
    }

    public function testRetrievePostStringApos() {
        $_POST['bar'] = "foo'";
        $this->assertEquals('foo\\\'', $this->api->retrievePostString('bar', 'Foo'));
        unset($_POST);
    }

    public function testRetrievePostStringQuote() {
        $_POST['bar'] = "foo\"";
        $this->assertEquals('foo\\"', $this->api->retrievePostString('bar', 'Foo'));
        unset($_POST);
    }

    public function testRetrievePostStringSlash() {
        $_POST['bar'] = "foo\\";
        $this->assertEquals('foo\\\\', $this->api->retrievePostString('bar', 'Foo'));
        unset($_POST);
    }

    public function testRetrieveValidatedPostNullFormat() {
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

    public function testRetrieveValidatedPost() {
        $_POST['bar'] = "msaperst+sstest@gmail.com";
        $this->assertEquals("msaperst+sstest@gmail.com", $this->api->retrieveValidatedPost('bar', 'Foo', FILTER_VALIDATE_EMAIL));
        unset($_POST);
    }

    public function testRetrievePostDateTimeNullFormat() {
        $_POST['bar'] = "foo";
        try {
            $this->api->retrievePostDateTime('bar', 'Foo', NULL);
        } catch (Exception $e) {
            $this->assertEquals("Foo is not the correct format", $e->getMessage());
        }
        unset($_POST);
    }

    public function testRetrievePostDateTimeBadFormat() {
        $_POST['bar'] = "foo";
        try {
            $this->api->retrievePostDateTime('bar', 'Foo', 'Y-m-d');
        } catch (Exception $e) {
            $this->assertEquals("Foo is not the correct format", $e->getMessage());
        }
        unset($_POST);
    }

    public function testRetrievePostDateTime() {
        $_POST['bar'] = "2020-01-01";
        $this->assertEquals("2020-01-01", $this->api->retrievePostDateTime('bar', 'Foo', 'Y-m-d'));
        unset($_POST);
    }

    public function testRetrieveGetString() {
        $_GET['bar'] = "foo";
        $this->assertEquals('foo', $this->api->retrieveGetString('bar', 'Foo'));
        unset($_GET);
    }

    public function testRetrieveGetStringApos() {
        $_GET['bar'] = "foo'";
        $this->assertEquals('foo\\\'', $this->api->retrieveGetString('bar', 'Foo'));
        unset($_GET);
    }

    public function testRetrieveGetStringQuote() {
        $_GET['bar'] = "foo\"";
        $this->assertEquals('foo\\"', $this->api->retrieveGetString('bar', 'Foo'));
        unset($_GET);
    }

    public function testRetrieveGetStringSlash() {
        $_GET['bar'] = "foo\\";
        $this->assertEquals('foo\\\\', $this->api->retrieveGetString('bar', 'Foo'));
        unset($_GET);
    }
}
