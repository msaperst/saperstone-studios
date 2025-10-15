<?php

namespace coverage\integration;

use Api;
use Exception;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class ApiIntegrationTest extends TestCase {

    private API $api;

    private string $post;

    private string $get;

    public function setUp(): void {
        $this->api = new Api();
        if (isset($_POST['bar'])) {
            $this->post = $_POST['bar'];
        }
        if (isset($_GET['bar'])) {
            $this->get = $_GET['bar'];
        }
    }

    public function tearDown(): void {
        if (isset($this->post)) {
            $_POST['bar'] = $this->post;
        } else {
            unset($_POST['bar']);
        }
        if (isset($this->get)) {
            $_GET['bar'] = $this->get;
        } else {
            unset($_GET['bar']);
        }
    }

    public function testRetrievePostString() {
        $_POST['bar'] = "foo";
        $this->assertEquals('foo', $this->api->retrievePostString('bar', 'Foo'));
    }

    public function testRetrievePostStringApos() {
        $_POST['bar'] = "foo'";
        $this->assertEquals('foo\\\'', $this->api->retrievePostString('bar', 'Foo'));
    }

    public function testRetrievePostStringQuote() {
        $_POST['bar'] = "foo\"";
        $this->assertEquals('foo\\"', $this->api->retrievePostString('bar', 'Foo'));
    }

    public function testRetrievePostStringSlash() {
        $_POST['bar'] = "foo\\";
        $this->assertEquals('foo\\\\', $this->api->retrievePostString('bar', 'Foo'));
    }

    public function testRetrieveValidatedPostNullFormat() {
        $_POST['bar'] = "foo";
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Foo is not valid');
        $this->api->retrieveValidatedPost('bar', 'Foo', NULL);
    }

    public function testRetrieveValidatedPostBadFormat() {
        $_POST['bar'] = "foo";
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Foo is not valid');
        $this->api->retrieveValidatedPost('bar', 'Foo', FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @throws Exception
     */
    public function testRetrieveValidatedPost() {
        $_POST['bar'] = "msaperst+sstest@gmail.com";
        $this->assertEquals("msaperst+sstest@gmail.com", $this->api->retrieveValidatedPost('bar', 'Foo', FILTER_VALIDATE_EMAIL));
    }

    public function testRetrievePostDateTimeNullFormat() {
        $_POST['bar'] = "foo";
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Foo is not the correct format');
        $this->api->retrievePostDateTime('bar', 'Foo', NULL);
    }

    public function testRetrievePostDateTimeBadFormat() {
        $_POST['bar'] = "foo";
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Foo is not the correct format');
        $this->api->retrievePostDateTime('bar', 'Foo', 'Y-m-d');
    }

    /**
     * @throws Exception
     */
    public function testRetrievePostDateTime() {
        $_POST['bar'] = "2020-01-01";
        $this->assertEquals("2020-01-01", $this->api->retrievePostDateTime('bar', 'Foo', 'Y-m-d'));
    }

    public function testRetrieveGetString() {
        $_GET['bar'] = "foo";
        $this->assertEquals('foo', $this->api->retrieveGetString('bar', 'Foo'));
    }

    public function testRetrieveGetStringApos() {
        $_GET['bar'] = "foo'";
        $this->assertEquals('foo\\\'', $this->api->retrieveGetString('bar', 'Foo'));
    }

    public function testRetrieveGetStringQuote() {
        $_GET['bar'] = "foo\"";
        $this->assertEquals('foo\\"', $this->api->retrieveGetString('bar', 'Foo'));
    }

    public function testRetrieveGetStringSlash() {
        $_GET['bar'] = "foo\\";
        $this->assertEquals('foo\\\\', $this->api->retrieveGetString('bar', 'Foo'));
    }
}
