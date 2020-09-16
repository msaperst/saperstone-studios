<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class UpdateProfileTest extends TestCase {
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
            $this->http->request('POST', 'api/update-profile.php');
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You must be logged in to perform this action", (string)$e->getResponse()->getBody());
        }
    }

    public function testNoEmail() {
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-profile.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email is required", (string)$response->getBody());
    }

    public function testBlankEmail() {
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-profile.php', [
            'form_params' => [
                'email' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email can not be blank", (string)$response->getBody());
    }

    public function testBadEmail() {
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-profile.php', [
            'form_params' => [
                'email' => 'max@max'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email is not valid", (string)$response->getBody());
    }

    public function testDuplicateEmail() {
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-profile.php', [
            'form_params' => [
                'email' => 'msaperst@gmail.com'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("That email already exists in the system: try logging in with it", (string)$response->getBody());
    }

    public function testNoExtras() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/update-profile.php', [
                'form_params' => [
                    'email' => 'uploader@example.org',
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals('', (string)$response->getBody());
            $userDetails = $this->sql->getRow("SELECT * FROM `users` WHERE `users`.`id` = 4;");
            $this->assertEquals(4, $userDetails['id']);
            $this->assertEquals('uploader', $userDetails['usr']);
            $this->assertEquals(md5('password'), $userDetails['pass']);
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

    public function testUpdateUserAllValues() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/update-profile.php', [
                'form_params' => [
                    'email' => 'upload@example.org',
                    'active' => 0,
                    'firstName' => 'u',
                    'lastName' => 't',
                    'role' => 'admin'
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals('', (string)$response->getBody());
            $userDetails = $this->sql->getRow("SELECT * FROM `users` WHERE `users`.`id` = 4;");
            $this->assertEquals(4, $userDetails['id']);
            $this->assertEquals('uploader', $userDetails['usr']);
            $this->assertEquals(md5('password'), $userDetails['pass']);
            $this->assertEquals('u', $userDetails['firstName']);
            $this->assertEquals('t', $userDetails['lastName']);
            $this->assertEquals('upload@example.org', $userDetails['email']);
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

    public function testNoCurrPassword() {
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-profile.php', [
            'form_params' => [
                'email' => 'uploader@example.org',
                'password' => '1234'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Current password is required", (string)$response->getBody());
    }

    public function testBlankCurrPassword() {
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-profile.php', [
            'form_params' => [
                'email' => 'uploader@example.org',
                'curPass' => '',
                'password' => '1234'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Current password can not be blank", (string)$response->getBody());
    }

    public function testBadCurrPassword() {
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-profile.php', [
            'form_params' => [
                'email' => 'uploader@example.org',
                'curPass' => '1234',
                'password' => '1234'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Current password does not match our records", (string)$response->getBody());
    }

    public function testNoPasswordConfirm() {
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-profile.php', [
            'form_params' => [
                'email' => 'uploader@example.org',
                'curPass' => 'password',
                'password' => '1234'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Password confirmation is required", (string)$response->getBody());
    }

    public function testBlankPasswordConfirm() {
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-profile.php', [
            'form_params' => [
                'email' => 'uploader@example.org',
                'curPass' => 'password',
                'password' => '1234',
                'passwordConfirm' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Password confirmation can not be blank", (string)$response->getBody());
    }

    public function testBadPasswordConfirm() {
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-profile.php', [
            'form_params' => [
                'email' => 'uploader@example.org',
                'curPass' => 'password',
                'password' => '1234',
                'passwordConfirm' => '123'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Password does not match password confirmation", (string)$response->getBody());
    }

    public function testUpdateUserPassword() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/update-profile.php', [
                'form_params' => [
                    'email' => 'uploader@example.org',
                    'curPass' => 'password',
                    'password' => 'newpassword',
                    'passwordConfirm' => 'newpassword'
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals('', (string)$response->getBody());
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