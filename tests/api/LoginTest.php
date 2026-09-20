<?php

namespace api;

use CustomAsserts;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class LoginTest extends TestCase {
    private $http;
    private $sql;
    private $cookieJar;
    private $csrfToken;

    public function setUp(): void {
        $this->cookieJar = new CookieJar();
        $this->http = new Client([
            'base_uri' => 'http://' . getenv('DB_HOST') . ':' . getenv('HTTP_PORT') . '/',
            'cookies' => $this->cookieJar
        ]);
        $response = $this->http->request('GET', '/');
        $this->assertSame(1, preg_match('/name="csrf_token"[^>]*value="([a-f0-9]{64})"/', (string)$response->getBody(), $matches));
        $this->csrfToken = $matches[1];
        $this->sql = new Sql();
    }

    public function tearDown(): void {
        $this->http = NULL;
        $this->cookieJar = NULL;
        $this->sql->disconnect();
    }

    private function addCookie(string $name, string $value): void {
        $this->cookieJar->setCookie(new SetCookie([
            'Name' => $name,
            'Value' => $value,
            'Domain' => getenv('DB_HOST'),
            'Path' => '/'
        ]));
    }

    public function testNoAction() {
        $response = $this->http->request('POST', 'api/login.php');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
    }

    public function testNoActionLoggedIn() {
        $this->addCookie('hash', '1d7505e7f434a7713e84ba399e937191');
        $response = $this->http->request('POST', 'api/login.php');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
    }

    public function testLogoutNotLoggedIn() {
        $response = $this->http->request('POST', 'api/login.php', [
            'form_params' => [
                'csrf_token' => $this->csrfToken,
                'submit' => 'Logout'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
    }

    public function testLogoutLoggedIn() {
        $this->addCookie('hash', '1d7505e7f434a7713e84ba399e937191');
        $response = $this->http->request('POST', 'api/login.php', [
            'form_params' => [
                'csrf_token' => $this->csrfToken,
                'submit' => 'Logout'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        $log = $this->sql->getRow("SELECT * FROM `user_logs` WHERE `user` = 1 ORDER BY time DESC, id DESC LIMIT 1;");
        $this->assertEquals('Logged Out', $log['action']);
        //TODO - assert cookie doesn't exist anymore
    }

    public function testLoginNoUsername() {
        $response = $this->http->request('POST', 'api/login.php', [
            'form_params' => [
                'csrf_token' => $this->csrfToken,
                'submit' => 'Login'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Username is required", (string)$response->getBody());
    }

    public function testLoginBlankUsername() {
        $response = $this->http->request('POST', 'api/login.php', [
            'form_params' => [
                'csrf_token' => $this->csrfToken,
                'submit' => 'Login',
                'username' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Username can not be blank", (string)$response->getBody());
    }

    public function testLoginNoPassword() {
        $response = $this->http->request('POST', 'api/login.php', [
            'form_params' => [
                'csrf_token' => $this->csrfToken,
                'submit' => 'Login',
                'username' => 'foo'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Password is required", (string)$response->getBody());
    }

    public function testLoginBlankPassword() {
        $response = $this->http->request('POST', 'api/login.php', [
            'form_params' => [
                'csrf_token' => $this->csrfToken,
                'submit' => 'Login',
                'username' => 'foo',
                'password' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Password can not be blank", (string)$response->getBody());
    }

    public function testLoginBadUsername() {
        $response = $this->http->request('POST', 'api/login.php', [
            'form_params' => [
                'csrf_token' => $this->csrfToken,
                'submit' => 'Login',
                'username' => 'foo',
                'password' => 'bar'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Credentials do not match our records", (string)$response->getBody());
    }

    public function testLoginBadPassword() {
        $response = $this->http->request('POST', 'api/login.php', [
            'form_params' => [
                'csrf_token' => $this->csrfToken,
                'submit' => 'Login',
                'username' => 'msaperst',
                'password' => 'bar'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Credentials do not match our records", (string)$response->getBody());
    }

    public function testLoginNotActive() {
        $this->sql->executeStatement("UPDATE `users` SET `active` = '0' WHERE `users`.`id` = 3;");
        $response = $this->http->request('POST', 'api/login.php', [
            'form_params' => [
                'csrf_token' => $this->csrfToken,
                'submit' => 'Login',
                'username' => 'downloader',
                'password' => 'password'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Sorry, your account has been deactivated. Please <a target="_blank" href="mailto:webmaster@saperstonestudios.com">contact our webmaster</a> to get this resolved.', (string)$response->getBody());
        $this->sql->executeStatement("UPDATE `users` SET `active` = '1' WHERE `users`.`id` = 3;");
    }

    public function testLoginSuccessfully() {
        $response = $this->http->request('POST', 'api/login.php', [
            'form_params' => [
                'csrf_token' => $this->csrfToken,
                'submit' => 'Login',
                'username' => 'downloader',
                'password' => 'password'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('', (string)$response->getBody());
        $log = $this->sql->getRow("SELECT * FROM `user_logs` WHERE `user` = 3 ORDER BY time DESC, id DESC LIMIT 1;");
        $this->assertEquals('Logged In', $log['action']);
        $userInfo = $this->sql->getRow("SELECT * FROM `users` WHERE `id` = 3;");
        CustomAsserts::timeWithin(2, $userInfo['lastLogin']);
        //TODO - cookie not set
    }

    public function testLoginRememberMeNoCookies() {
        date_default_timezone_set("America/New_York");
        $response = $this->http->request('POST', 'api/login.php', [
            'form_params' => [
                'csrf_token' => $this->csrfToken,
                'submit' => 'Login',
                'username' => 'downloader',
                'password' => 'password',
                'rememberMe' => 1
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('', (string)$response->getBody());
        $log = $this->sql->getRow("SELECT * FROM `user_logs` WHERE `user` = 3 ORDER BY time DESC, id DESC LIMIT 1;");
        $this->assertEquals('Logged In', $log['action']);
        $userInfo = $this->sql->getRow("SELECT * FROM `users` WHERE `id` = 3;");
        CustomAsserts::timeWithin(2, $userInfo['lastLogin']);
        //TODO - cookie not set
    }

    public function testLoginRememberMeCookies() {
        date_default_timezone_set("America/New_York");
        $this->addCookie('CookiePreferences', '["preferences","analytics"]');
        $response = $this->http->request('POST', 'api/login.php', [
            'form_params' => [
                'csrf_token' => $this->csrfToken,
                'submit' => 'Login',
                'username' => 'uploader',
                'password' => 'password',
                'rememberMe' => 1
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('', (string)$response->getBody());
        $log = $this->sql->getRow("SELECT * FROM `user_logs` WHERE `user` = 4 ORDER BY time DESC, id DESC LIMIT 1;");
        $this->assertEquals('Logged In', $log['action']);
        $userInfo = $this->sql->getRow("SELECT * FROM `users` WHERE `id` = 4;");
        CustomAsserts::timeWithin(2, $userInfo['lastLogin']);
        //TODO - cookie set
    }
    public function testLoginMissingCsrfToken() {
        $response = $this->http->request('POST', 'api/login.php', [
            'http_errors' => false,
            'form_params' => [
                'submit' => 'Login',
                'username' => 'downloader',
                'password' => 'password'
            ]
        ]);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('Invalid CSRF token', (string)$response->getBody());
    }

    public function testLoginInvalidCsrfToken() {
        $response = $this->http->request('POST', 'api/login.php', [
            'http_errors' => false,
            'form_params' => [
                'csrf_token' => str_repeat('0', 64),
                'submit' => 'Login',
                'username' => 'downloader',
                'password' => 'password'
            ]
        ]);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('Invalid CSRF token', (string)$response->getBody());
    }
}
