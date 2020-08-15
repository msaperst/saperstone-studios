<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class LoginTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
        $this->sql = new Sql();
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->disconnect();
    }

    public function testNoAction() {
        $response = $this->http->request('POST', 'api/login.php');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
    }

    public function testNoActionLoggedIn() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/login.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
    }

    public function testLogoutNotLoggedIn() {
        $response = $this->http->request('POST', 'api/login.php', [
            'form_params' => [
                'submit' => 'Logout'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
    }

    public function testLogoutLoggedIn() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/login.php', [
            'form_params' => [
                'submit' => 'Logout'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        $log = $this->sql->getRow("SELECT * FROM `user_logs` WHERE `user` = 1 ORDER BY time DESC LIMIT 1;");
        $this->assertEquals('Logged Out', $log['action']);
        //TODO - assert cookie doesn't exist anymore
    }

    public function testLoginNoUsername() {
        $response = $this->http->request('POST', 'api/login.php', [
            'form_params' => [
                'submit' => 'Login'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Username is required", (string)$response->getBody());
    }

    public function testLoginBlankUsername() {
        $response = $this->http->request('POST', 'api/login.php', [
            'form_params' => [
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
                'submit' => 'Login',
                'username' => 'msaperst',
                'password' => 'bar'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Credentials do not match our records", (string)$response->getBody());
    }

    public function testLoginNotActive() {
        $this->sql->executeStatement("UPDATE `users` SET `active` = '0' WHERE `users`.`id` = 1;");
        $response = $this->http->request('POST', 'api/login.php', [
            'form_params' => [
                'submit' => 'Login',
                'username' => 'msaperst',
                'password' => 'MaxAvr0m'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Sorry, you account has been deactivated. Please <a target="_blank" href="mailto:webmaster@saperstonestudios.com">contact our webmaster</a> to get this resolved.', (string)$response->getBody());
        $this->sql->executeStatement("UPDATE `users` SET `active` = '1' WHERE `users`.`id` = 1;");
    }

    public function testLoginSuccessfully() {
        date_default_timezone_set("America/New_York");
        $response = $this->http->request('POST', 'api/login.php', [
            'form_params' => [
                'submit' => 'Login',
                'username' => 'lsaperst',
                'password' => 'idontkno1'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('', (string)$response->getBody());
        $log = $this->sql->getRow("SELECT * FROM `user_logs` WHERE `user` = 2 ORDER BY time DESC LIMIT 1;");
        $this->assertEquals('Logged In', $log['action']);
        $userInfo = $this->sql->getRow("SELECT * FROM `users` WHERE `id` = 2;");
        $this->assertStringStartsWith(date("Y-m-d H:i"), $userInfo['lastLogin']);
        //TODO - cookie not set
    }

    public function testLoginRememberMeNoCookies() {
        date_default_timezone_set("America/New_York");
        $response = $this->http->request('POST', 'api/login.php', [
            'form_params' => [
                'submit' => 'Login',
                'username' => 'downloader',
                'password' => 'password',
                'rememberMe' => 1
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('', (string)$response->getBody());
        $log = $this->sql->getRow("SELECT * FROM `user_logs` WHERE `user` = 3 ORDER BY time DESC LIMIT 1;");
        $this->assertEquals('Logged In', $log['action']);
        $userInfo = $this->sql->getRow("SELECT * FROM `users` WHERE `id` = 3;");
        $this->assertStringStartsWith(date("Y-m-d H:i"), $userInfo['lastLogin']);
        //TODO - cookie not set
    }

    public function testLoginRememberMeCookies() {
        date_default_timezone_set("America/New_York");
        $cookieJar = CookieJar::fromArray([
            'CookiePreferences' => '["preferences","analytics"]'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/login.php', [
            'form_params' => [
                'submit' => 'Login',
                'username' => 'uploader',
                'password' => 'password',
                'rememberMe' => 1
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('', (string)$response->getBody());
        $log = $this->sql->getRow("SELECT * FROM `user_logs` WHERE `user` = 4 ORDER BY time DESC LIMIT 1;");
        $this->assertEquals('Logged In', $log['action']);
        $userInfo = $this->sql->getRow("SELECT * FROM `users` WHERE `id` = 4;");
        $this->assertStringStartsWith(date("Y-m-d H:i"), $userInfo['lastLogin']);
        //TODO - cookie set
    }
}