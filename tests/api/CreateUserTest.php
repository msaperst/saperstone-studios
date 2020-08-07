<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'sql.php';

class CreateUserTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://localhost:90/']);
        $this->sql = new Sql();
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->disconnect();
    }

    public function testNotLoggedIn() {
        try {
            $this->http->request('POST', 'api/create-user.php');
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody());
        }
    }

    public function testLoggedInAsDownloader() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], 'localhost');
        try {
            $this->http->request('POST', 'api/create-user.php', [
                'cookies' => $cookieJar
            ]);
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You do not have appropriate rights to perform this action", $e->getResponse()->getBody());
        }
    }

    public function testNoUsername() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/create-user.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Username is required", (string)$response->getBody());
    }

    public function testBlankUsername() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/create-user.php', [
            'form_params' => [
                'username' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Username can not be blank", (string)$response->getBody());
    }

    public function testDuplicateUsername() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/create-user.php', [
            'form_params' => [
                'username' => 'msaperst'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("That username already exists in the system", (string)$response->getBody());
    }

    public function testNoEmail() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/create-user.php', [
            'form_params' => [
                'username' => 'Max'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email is required", (string)$response->getBody());
    }

    public function testBlankEmail() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/create-user.php', [
            'form_params' => [
                'username' => 'Max',
                'email' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email can not be blank", (string)$response->getBody());
    }

    public function testBadEmail() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/create-user.php', [
            'form_params' => [
                'username' => 'Max',
                'email' => 'max@max'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email is not valid", (string)$response->getBody());
    }

    public function testDuplicateEmail() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/create-user.php', [
            'form_params' => [
                'username' => 'Max',
                'email' => 'msaperst@gmail.com'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("We already have an account on file for that email address", (string)$response->getBody());
    }

    public function testNoRole() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/create-user.php', [
            'form_params' => [
                'username' => 'Max',
                'email' => 'msaperst+sstest@gmail.com'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Role is required", (string)$response->getBody());
    }

    public function testBlankRole() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/create-user.php', [
            'form_params' => [
                'username' => 'Max',
                'email' => 'msaperst+sstest@gmail.com',
                'role' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Role can not be blank", (string)$response->getBody());
    }

    public function testBadRole() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/create-user.php', [
            'form_params' => [
                'username' => 'Max',
                'email' => 'msaperst+sstest@gmail.com',
                'role' => 'awesome'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Role is not valid", (string)$response->getBody());
    }

    public function testNoExtras() {
        $date = date("Y-m-d H:i:s");
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], 'localhost');

            $response = $this->http->request('POST', 'api/create-user.php', [
                'form_params' => [
                    'username' => 'Max',
                    'email' => 'msaperst+sstest@gmail.com',
                    'role' => 'downloader'
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $userId = $response->getBody();
            $userDetails = $this->sql->getRow("SELECT * FROM `users` WHERE `users`.`id` = $userId;");
            $this->assertEquals($userId, $userDetails['id']);
            $this->assertEquals('Max', $userDetails['usr']);
            $this->assertNotEquals('', $userDetails['pass']);
            $this->assertEquals('', $userDetails['firstName']);
            $this->assertEquals('', $userDetails['lastName']);
            $this->assertEquals('msaperst+sstest@gmail.com', $userDetails['email']);
            $this->assertEquals('downloader', $userDetails['role']);
            $this->assertEquals(md5('Maxdownloader'), $userDetails['hash']);
            $this->assertEquals(0, $userDetails['active']);
            $this->assertEquals($date, $userDetails['created']);
            $this->assertNull($userDetails['lastLogin']);
            $this->assertNull($userDetails['resetKey']);
        } finally {
            $this->sql->executeStatement("DELETE FROM `users` WHERE `users`.`id` = $userId;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `users`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `users` AUTO_INCREMENT = $count;");
        }
    }

    public function testAllData() {
        $date = date("Y-m-d H:i:s");
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], 'localhost');

            $response = $this->http->request('POST', 'api/create-user.php', [
                'form_params' => [
                    'username' => 'Max',
                    'email' => 'msaperst+sstest@gmail.com',
                    'role' => 'downloader',
                    'firstName' => 'Max',
                    'lastName' => 'Saperstone',
                    'active' => 1,
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $userId = $response->getBody();
            $userDetails = $this->sql->getRow("SELECT * FROM `users` WHERE `users`.`id` = $userId;");
            $this->assertEquals($userId, $userDetails['id']);
            $this->assertEquals('Max', $userDetails['usr']);
            $this->assertNotEquals('', $userDetails['pass']);
            $this->assertEquals('Max', $userDetails['firstName']);
            $this->assertEquals('Saperstone', $userDetails['lastName']);
            $this->assertEquals('msaperst+sstest@gmail.com', $userDetails['email']);
            $this->assertEquals('downloader', $userDetails['role']);
            $this->assertEquals(md5('Maxdownloader'), $userDetails['hash']);
            $this->assertEquals(1, $userDetails['active']);
            $this->assertEquals($date, $userDetails['created']);
            $this->assertNull($userDetails['lastLogin']);
            $this->assertNull($userDetails['resetKey']);
        } finally {
            $this->sql->executeStatement("DELETE FROM `users` WHERE `users`.`id` = $userId;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `users`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `users` AUTO_INCREMENT = $count;");
        }
    }
}

?>