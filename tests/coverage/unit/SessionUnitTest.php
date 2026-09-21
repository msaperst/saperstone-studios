<?php

namespace coverage\unit;

use PHPUnit\Framework\TestCase;
use Session;

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class SessionUnitTest extends TestCase {

    private ?Session $session;

    public function setUp(): void {
        $this->session = new Session();
    }

    public function tearDown(): void {
        unset($_SESSION['csrf_token']);
        unset($_COOKIE['CookiePreferences'], $_COOKIE['searched']);
        $this->session = NULL;
    }

    public function testCsrfTokenIsGeneratedAndReused() {
        $token = $this->session->getCsrfToken();

        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
        $this->assertSame($token, $this->session->getCsrfToken());
    }

    public function testCsrfTokenValidation() {
        $token = $this->session->getCsrfToken();

        $this->assertTrue($this->session->isCsrfTokenValid($token));
        $this->assertFalse($this->session->isCsrfTokenValid(null));
        $this->assertFalse($this->session->isCsrfTokenValid('invalid-token'));
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

    public function testUseAnalyticsNoCookie() {
        $this->assertFalse(Session::useAnalytics());
    }

    public function testUseAnalyticsNoHost() {
        $_COOKIE['CookiePreferences'] = json_encode(['analytics']);
        $this->assertFalse(Session::useAnalytics());
        unset($_COOKIE ['CookiePreferences']);
    }

    public function testUseAnalyticsBadHost() {
        $_COOKIE['CookiePreferences'] = json_encode(['analytics']);
        $_SERVER ['HTTP_X_FORWARDED_HOST'] = '12345';
        $this->assertFalse(Session::useAnalytics());
        unset($_SERVER ['HTTP_X_FORWARDED_HOST']);
        unset($_COOKIE ['CookiePreferences']);
    }

    public function testUseAnalyticsWrongCookie() {
        $_SERVER ['HTTP_X_FORWARDED_HOST'] = 'http://www.saperstonestudios.com';
        $_COOKIE['CookiePreferences'] = json_encode(['preferences']);
        $this->assertFalse(Session::useAnalytics());
        unset($_SERVER ['HTTP_X_FORWARDED_HOST']);
        unset($_COOKIE ['CookiePreferences']);
    }

    public function testUseAnalyticsGood() {
        $_SERVER ['HTTP_HOST'] = 'saperstonestudios.com';
        $_COOKIE['CookiePreferences'] = json_encode(['preferences', 'analytics']);
        $this->assertTrue(Session::useAnalytics());
        unset($_SERVER ['HTTP_HOST']);
        unset($_COOKIE ['CookiePreferences']);
    }

    public function testUseAnalyticsGood1() {
        $_SERVER ['HTTP_HOST'] = 'www.saperstonestudios.com';
        $_COOKIE['CookiePreferences'] = json_encode(['preferences', 'analytics']);
        $this->assertTrue(Session::useAnalytics());
        unset($_SERVER ['HTTP_HOST']);
        unset($_COOKIE ['CookiePreferences']);
    }

    public function testAnalyticsCookiesDeniedBeforeChoice() {
        unset($_COOKIE['CookiePreferences']);
        $this->assertFalse(Session::allowsAnalyticsCookies());
    }

    public function testAnalyticsCookiesDeniedWhenRejected() {
        $_COOKIE['CookiePreferences'] = json_encode(['preferences']);
        $this->assertFalse(Session::allowsAnalyticsCookies());
        unset($_COOKIE['CookiePreferences']);
    }

    public function testAnalyticsCookiesDeniedForInvalidPreferenceCookie() {
        $_COOKIE['CookiePreferences'] = 'invalid';
        $this->assertFalse(Session::allowsAnalyticsCookies());
        unset($_COOKIE['CookiePreferences']);
    }

    public function testAnalyticsCookiesAllowedWhenAccepted() {
        $_COOKIE['CookiePreferences'] = json_encode(['analytics']);
        $this->assertTrue(Session::allowsAnalyticsCookies());
        unset($_COOKIE['CookiePreferences']);
    }

    public function testPreferenceCookiesAllowedBeforeChoice() {
        unset($_COOKIE['CookiePreferences']);
        $this->assertTrue(Session::allowsPreferenceCookies());
    }

    public function testPreferenceCookiesAllowedWhenAccepted() {
        $_COOKIE['CookiePreferences'] = json_encode(['preferences']);
        $this->assertTrue(Session::allowsPreferenceCookies());
        unset($_COOKIE['CookiePreferences']);
    }

    public function testPreferenceCookiesDeniedWhenRejected() {
        $_COOKIE['CookiePreferences'] = '[]';
        $this->assertFalse(Session::allowsPreferenceCookies());
        unset($_COOKIE['CookiePreferences']);
    }

    public function testPreferenceCookiesDeniedForInvalidPreferenceCookie() {
        $_COOKIE['CookiePreferences'] = 'invalid';
        $this->assertFalse(Session::allowsPreferenceCookies());
        unset($_COOKIE['CookiePreferences']);
    }

    public function testInitializeKeepsSearchedCookieWhenPreferenceCookiesAreAllowed(): void {
        $_COOKIE['CookiePreferences'] = json_encode(['preferences']);
        $_COOKIE['searched'] = 'album-access';

        $this->session->initialize();

        $this->assertSame('album-access', $_COOKIE['searched']);
    }

    public function testInitializeDeletesSearchedCookieWhenPreferenceCookiesAreRejected(): void {
        $_COOKIE['CookiePreferences'] = '[]';
        $_COOKIE['searched'] = 'album-access';

        $this->session->initialize();

        $this->assertArrayNotHasKey('searched', $_COOKIE);
    }

}
