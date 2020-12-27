<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class UpdateUserTest extends TestCase {
    /**
     * @var Client
     */
    private $http;
    /**
     * @var Sql
     */
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
            $this->http->request('POST', 'api/update-user.php');
        } catch (GuzzleException | ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody());
        }
    }

    public function testLoggedInAsDownloader() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        try {
            $this->http->request('POST', 'api/update-user.php', [
                'cookies' => $cookieJar
            ]);
        } catch (GuzzleException | ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You do not have appropriate rights to perform this action", $e->getResponse()->getBody());
        }
    }

    /**
     * @throws GuzzleException
     */
    public function testNoUserId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-user.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("User id is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankUserId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-user.php', [
            'form_params' => [
                'id' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("User id can not be blank", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBadUserId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-user.php', [
            'form_params' => [
                'id' => '999'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("User id does not match any users", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testNoEmail() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-user.php', [
            'form_params' => [
                'id' => '4'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankEmail() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-user.php', [
            'form_params' => [
                'id' => '4',
                'email' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email can not be blank", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBadEmail() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-user.php', [
            'form_params' => [
                'id' => '4',
                'email' => 'max@max'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email is not valid", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testDuplicateEmail() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-user.php', [
            'form_params' => [
                'id' => '4',
                'email' => 'msaperst@gmail.com'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("That email already exists in the system: try logging in with it", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBadRole() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-user.php', [
            'form_params' => [
                'id' => '4',
                'email' => 'saperstonestudios@mailinator.com',
                'role' => 'awesome'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Role is not valid", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testNoPassConfirmation() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-user.php', [
            'form_params' => [
                'id' => '4',
                'email' => 'saperstonestudios@mailinator.com',
                'password' => 'awesome'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Password confirmation is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankPassConfirmation() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-user.php', [
            'form_params' => [
                'id' => '4',
                'email' => 'saperstonestudios@mailinator.com',
                'password' => 'awesome',
                'passwordConfirm' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Password confirmation can not be blank", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBadPassConfirmation() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-user.php', [
            'form_params' => [
                'id' => '4',
                'email' => 'saperstonestudios@mailinator.com',
                'password' => 'awesome',
                'passwordConfirm' => 'awsome'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Password does not match password confirmation", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testNoExtras() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/update-user.php', [
                'form_params' => [
                    'id' => '4',
                    'email' => 'saperstonestudios@mailinator.com',
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
            $this->assertEquals('saperstonestudios@mailinator.com', $userDetails['email']);
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

    /**
     * @throws GuzzleException
     */
    public function testAllData() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/update-user.php', [
                'form_params' => [
                    'id' => '4',
                    'email' => 'uploader@example.org',
                    'role' => 'downloader',
                    'firstName' => 'Max',
                    'lastName' => 'Saperstone',
                    'active' => 0,
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
            $this->assertEquals('Max', $userDetails['firstName']);
            $this->assertEquals('Saperstone', $userDetails['lastName']);
            $this->assertEquals('uploader@example.org', $userDetails['email']);
            $this->assertEquals('downloader', $userDetails['role']);
            $this->assertEquals('c90788c0e409eac6a95f6c6360d8dbf7', $userDetails['hash']);
            $this->assertEquals(0, $userDetails['active']);
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