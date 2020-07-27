<?php
use PHPUnit\Framework\TestCase;

$_SERVER ['DOCUMENT_ROOT'] = dirname ( __DIR__ );
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";

class SessionTest extends TestCase {
    private $session;

    public function testNoClientIp() {
        $this->assertEquals ( "", getClientIP () );
    }

    public function testHttpXForwardedForSet() {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = "12";
        $result = getClientIP ();
        unset( $_SERVER['HTTP_X_FORWARDED_FOR'] );
        $this->assertEquals ( "12",  $result);
    }

    public function testHttpXForwardedForAndRemoteAddressSet() {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = "12";
        $_SERVER['REMOTE_ADDR'] = "13";
        $result = getClientIP ();
        unset( $_SERVER['HTTP_X_FORWARDED_FOR'] );
        unset( $_SERVER['REMOTE_ADDR'] );
        $this->assertEquals ( "12",  $result);
    }

    public function testRemoteAddressSet() {
        $_SERVER['REMOTE_ADDR'] = "13";
        $result = getClientIP ();
        unset( $_SERVER['REMOTE_ADDR'] );
        $this->assertEquals ( "13", $result );
    }

    public function testClientIpSet() {
        $_SERVER['HTTP_CLIENT_IP'] = "14";
        $result = getClientIP ();
        unset( $_SERVER['HTTP_CLIENT_IP'] );
        $this->assertEquals ( "14", $result );
    }

    public function testServerNoHttp() {
        $_SERVER['SERVER_NAME'] = "www.examples.com";
        $result = getServer ();
        unset( $_SERVER['SERVER_NAME'] );
        $this->assertEquals ( "www.examples.com", $result );
    }

    public function testServerHttp() {
        $_SERVER['SERVER_NAME'] = "www.examples.com";
        $_SERVER['HTTP_X_FORWARDED_SERVER'] = "www.example.com";
        $result = getServer ();
        unset( $_SERVER['SERVER_NAME'] );
        unset( $_SERVER['HTTP_X_FORWARDED_SERVER'] );
        $this->assertEquals ( "www.example.com", $result );
    }

    public function testHostNoHttp() {
        $_SERVER['HTTP_HOST'] = "www.examples.com";
        $result = getHost ();
        unset( $_SERVER['HTTP_HOST'] );
        $this->assertEquals ( "www.examples.com", $result );
    }

    public function testHostHttp() {
        $_SERVER['HTTP_HOST'] = "www.examples.com";
        $_SERVER['HTTP_X_FORWARDED_HOST'] = "www.example.com";
        $result = getHost ();
        unset( $_SERVER['HTTP_HOST'] );
        unset( $_SERVER['HTTP_X_FORWARDED_HOST'] );
        $this->assertEquals ( "www.example.com", $result );
    }

    public function testBaseUrlNotSecure() {
        $_SERVER['SERVER_NAME'] = "www.examples.com";
        $_SERVER['SERVER_PORT'] = "90";
        $result = getBaseUrl ();
        unset( $_SERVER['SERVER_NAME'] );
        unset( $_SERVER['SERVER_PORT'] );
        $this->assertEquals ( "http://www.examples.com", $result );
    }

    public function testBaseUrlSecure() {
        $_SERVER['SERVER_NAME'] = "www.examples.com";
        $_SERVER['SERVER_PORT'] = "9443";
        $result = getBaseUrl ();
        unset( $_SERVER['SERVER_NAME'] );
        unset( $_SERVER['SERVER_PORT'] );
        $this->assertEquals ( "https://www.examples.com", $result );
    }

    public function testBaseUrlAlternatePort() {
        $_SERVER['SERVER_NAME'] = "www.examples.com";
        $_SERVER['SERVER_PORT'] = "80";
        $result = getBaseUrl ();
        unset( $_SERVER['SERVER_NAME'] );
        unset( $_SERVER['SERVER_PORT'] );
        $this->assertEquals ( "http://www.examples.com:80", $result );
    }

    public function testCurrentPage() {
        $_SERVER['SERVER_NAME'] = "www.examples.com";
        $_SERVER['SERVER_PORT'] = "9443";
        $_SERVER['REQUEST_URI'] = "/here";
        $result = getCurrentPage ();
        unset( $_SERVER['SERVER_NAME'] );
        unset( $_SERVER['SERVER_PORT'] );
        unset( $_SERVER['REQUEST_URI'] );
        $this->assertEquals ( "https://www.examples.com/here", $result );
    }
}

?>