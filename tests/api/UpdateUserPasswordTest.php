<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class UpdateUserPasswordTest extends TestCase {
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

    public function testNotLoggedIn() {
        try {
            $this->http->request('POST', 'api/update-user-password.php');
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody());
        }
    }

    public function testLoggedInAsDownloader() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        try {
            $this->http->request('POST', 'api/update-user-password.php', [
                'cookies' => $cookieJar
            ]);
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You do not have appropriate rights to perform this action", $e->getResponse()->getBody());
        }
    }

    public function testNoUserId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-user-password.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("User id is required", (string)$response->getBody());
    }

    public function testBlankUserId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-user-password.php', [
            'form_params' => [
                'id' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("User id can not be blank", (string)$response->getBody());
    }

    public function testBadUserId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-user-password.php', [
            'form_params' => [
                'id' => '999'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("User id does not match any users", (string)$response->getBody());
    }

    public function testNoPassword() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-user-password.php', [
            'form_params' => [
                'id' => '4'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Password is required", (string)$response->getBody());
    }

    public function testBlankPassword() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-user-password.php', [
            'form_params' => [
                'id' => '4',
                'password' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Password can not be blank", (string)$response->getBody());
    }

    public function testNoPasswordConfirm() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-user-password.php', [
            'form_params' => [
                'id' => '4',
                'password' => 'newpassword'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Password confirmation is required", (string)$response->getBody());
    }

    public function testBlankPasswordConfirmation() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-user-password.php', [
            'form_params' => [
                'id' => '4',
                'password' => 'newpassword',
                'passwordConfirm' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Password confirmation can not be blank", (string)$response->getBody());
    }

    public function testBadPasswordConfirmation() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-user-password.php', [
            'form_params' => [
                'id' => '4',
                'password' => 'newpassword',
                'passwordConfirm' => 'newpasword'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Password does not match password confirmation", (string)$response->getBody());
    }

    public function testReset() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/update-user-password.php', [
                'form_params' => [
                    'id' => '4',
                    'password' => 'newpassword',
                    'passwordConfirm' => 'newpassword'
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals('', (string) $response->getBody());
            $userDetails = $this->sql->getRow("SELECT * FROM `users` WHERE `users`.`id` = 4;");
            $this->assertEquals(4, $userDetails['id']);
            $this->assertEquals('uploader', $userDetails['usr']);
            $this->assertEquals(md5('newpassword'), $userDetails['pass']);
            $this->assertEquals('Upload', $userDetails['firstName']);
            $this->assertEquals('User', $userDetails['lastName']);
            $this->assertEquals('uploader@example.org', $userDetails['email']);
            $this->assertEquals('uploader', $userDetails['role']);
            $this->assertEquals('c90788c0e409eac6a95f6c6360d8dbf7', $userDetails['hash']);
            $this->assertEquals(1, $userDetails['active']);
            $this->assertNull($userDetails['resetKey']);
            $userLogs = $this->sql->getRow("SELECT * FROM `user_logs` WHERE user = 4 ORDER BY time DESC");
            $this->assertEquals(4, $userLogs['user']);
            $this->assertEquals('Updated User', $userLogs['action']);
            $this->assertNull($userLogs['what']);
            $this->assertNull($userLogs['album']);
        } finally {
            $this->sql->executeStatement("UPDATE users SET pass = '5f4dcc3b5aa765d61d8327deb882cf99', firstName = 'Upload', lastName = 'User', email = 'uploader@example.org', role = 'uploader', hash = 'c90788c0e409eac6a95f6c6360d8dbf7', active = 1 WHERE id = 4;");
        }
    }
}