<?php

namespace coverage\unit;

use PHPUnit\Framework\TestCase;
use Session;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class SessionUnitTest extends TestCase {

    private $session;

    public function setUp() {
        $this->session = new Session();
    }

    public function tearDown() {
        $this->session = NULL;
    }

    public function testNoClientIp() {
        $this->assertEquals("", $this->session->getClientIP());
    }

    public function testHttpXForwardedForSet() {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = "12";
        $result = $this->session->getClientIP();
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        $this->assertEquals("12", $result);
    }

    public function testHttpXForwardedForAndRemoteAddressSet() {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = "12";
        $_SERVER['REMOTE_ADDR'] = "13";
        $result = $this->session->getClientIP();
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        unset($_SERVER['REMOTE_ADDR']);
        $this->assertEquals("12", $result);
    }

    public function testRemoteAddressSet() {
        $_SERVER['REMOTE_ADDR'] = "13";
        $result = $this->session->getClientIP();
        unset($_SERVER['REMOTE_ADDR']);
        $this->assertEquals("13", $result);
    }

    public function testClientIpSet() {
        $_SERVER['HTTP_CLIENT_IP'] = "14";
        $result = $this->session->getClientIP();
        unset($_SERVER['HTTP_CLIENT_IP']);
        $this->assertEquals("14", $result);
    }

    public function testServerNoHttp() {
        $_SERVER['SERVER_NAME'] = "www.examples.com";
        $result = $this->session->getServer();
        unset($_SERVER['SERVER_NAME']);
        $this->assertEquals("www.examples.com", $result);
    }

    public function testServerHttp() {
        $_SERVER['SERVER_NAME'] = "www.examples.com";
        $_SERVER['HTTP_X_FORWARDED_SERVER'] = "www.example.com";
        $result = $this->session->getServer();
        unset($_SERVER['SERVER_NAME']);
        unset($_SERVER['HTTP_X_FORWARDED_SERVER']);
        $this->assertEquals("www.example.com", $result);
    }

    public function testHostNoHttp() {
        $_SERVER['HTTP_HOST'] = "www.examples.com";
        $result = $this->session->getHost();
        unset($_SERVER['HTTP_HOST']);
        $this->assertEquals("www.examples.com", $result);
    }

    public function testHostHttp() {
        $_SERVER['HTTP_HOST'] = "www.examples.com";
        $_SERVER['HTTP_X_FORWARDED_HOST'] = "www.example.com";
        $result = $this->session->getHost();
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['HTTP_X_FORWARDED_HOST']);
        $this->assertEquals("www.example.com", $result);
    }

    public function testBaseUrlNotSecure() {
        $_SERVER['SERVER_NAME'] = "www.examples.com";
        $_SERVER['SERVER_PORT'] = "90";
        $result = $this->session->getBaseUrl();
        unset($_SERVER['SERVER_NAME']);
        unset($_SERVER['SERVER_PORT']);
        $this->assertEquals("http://www.examples.com", $result);
    }

    public function testBaseUrlSecure() {
        $_SERVER['SERVER_NAME'] = "www.examples.com";
        $_SERVER['SERVER_PORT'] = "9443";
        $result = $this->session->getBaseUrl();
        unset($_SERVER['SERVER_NAME']);
        unset($_SERVER['SERVER_PORT']);
        $this->assertEquals("https://www.examples.com", $result);
    }

    public function testBaseUrlAlternatePort() {
        $_SERVER['SERVER_NAME'] = "www.examples.com";
        $_SERVER['SERVER_PORT'] = "80";
        $result = $this->session->getBaseUrl();
        unset($_SERVER['SERVER_NAME']);
        unset($_SERVER['SERVER_PORT']);
        $this->assertEquals("http://www.examples.com:80", $result);
    }

    public function testCurrentPage() {
        $_SERVER['SERVER_NAME'] = "www.examples.com";
        $_SERVER['SERVER_PORT'] = "9443";
        $_SERVER['REQUEST_URI'] = "/here";
        $result = $this->session->getCurrentPage();
        unset($_SERVER['SERVER_NAME']);
        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['REQUEST_URI']);
        $this->assertEquals("https://www.examples.com/here", $result);
    }
}