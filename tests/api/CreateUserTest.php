<?php

namespace api;

use CustomAsserts;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class CreateUserTest extends TestCase {
    /**
     * @var Client
     */
    private $http;
    /**
     * @var Sql
     */
    private $sql;

    /**
     *
     */
    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
        $this->sql = new Sql();
    }

    /**
     *
     */
    public function tearDown() {
        $this->http = NULL;
        $this->sql->disconnect();
    }

    /**
     * @throws GuzzleException
     */
    public function testNotLoggedIn() {
        try {
            $this->http->request('POST', 'api/create-user.php');
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody());
        }
    }

    /**
     * @throws GuzzleException
     */
    public function testLoggedInAsDownloader() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        try {
            $this->http->request('POST', 'api/create-user.php', [
                'cookies' => $cookieJar
            ]);
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You do not have appropriate rights to perform this action", $e->getResponse()->getBody());
        }
    }

    /**
     * @throws GuzzleException
     */
    public function testNoUsername() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/create-user.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Username is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankUsername() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/create-user.php', [
            'form_params' => [
                'username' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Username can not be blank", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testDuplicateUsername() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/create-user.php', [
            'form_params' => [
                'username' => 'msaperst'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("That username already exists in the system", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testNoEmail() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/create-user.php', [
            'form_params' => [
                'username' => 'MaxMax'
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
        $response = $this->http->request('POST', 'api/create-user.php', [
            'form_params' => [
                'username' => 'MaxMax',
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
        $response = $this->http->request('POST', 'api/create-user.php', [
            'form_params' => [
                'username' => 'MaxMax',
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
        $response = $this->http->request('POST', 'api/create-user.php', [
            'form_params' => [
                'username' => 'MaxMax',
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
        $response = $this->http->request('POST', 'api/create-user.php', [
            'form_params' => [
                'username' => 'MaxMax',
                'email' => 'msaperst+sstest@gmail.com',
                'role' => 'awesome'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Role is not valid", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function testNoExtras() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));

            $response = $this->http->request('POST', 'api/create-user.php', [
                'form_params' => [
                    'username' => 'MaxMax',
                    'email' => 'msaperst+sstest@gmail.com',
                    'role' => 'downloader'
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $userId = (string)$response->getBody();
            $userDetails = $this->sql->getRow("SELECT * FROM `users` WHERE `users`.`id` = $userId;");
            $this->assertEquals($userId, $userDetails['id']);
            $this->assertEquals('MaxMax', $userDetails['usr']);
            $this->assertNotEquals('', $userDetails['pass']);
            $this->assertEquals('', $userDetails['firstName']);
            $this->assertEquals('', $userDetails['lastName']);
            $this->assertEquals('msaperst+sstest@gmail.com', $userDetails['email']);
            $this->assertEquals('downloader', $userDetails['role']);
            $this->assertEquals(32, strlen($userDetails['hash']));
            $this->assertEquals(1, $userDetails['active']);
            CustomAsserts::timeWithin(10, $userDetails['created']);
            $this->assertNull($userDetails['lastLogin']);
            $this->assertNull($userDetails['resetKey']);
            CustomAsserts::assertEmailMatches('New User Created at Saperstone Studios', "Someone has setup a new user for you at Saperstone Studios. You can login and access the site at https://saperstonestudios.com. Initial credentials have been setup for you as: \r
    Username: MaxMax\r
    Password: %s\r
For security reasons, once logged in, we recommend you reset your password at https://saperstonestudios.com/user/profile.php",
                "<html><body>Someone has setup a new user for you at Saperstone Studios. You can login and access the site at <a href='https://saperstonestudios.com'>saperstonestudios.com</a>. Initial credentials have been setup for you as: <p>&nbsp;&nbsp;&nbsp;&nbsp;Username: MaxMax<br/>&nbsp;&nbsp;&nbsp;&nbsp;Password: %s</p>For security reasons, once logged in, we recommend you <a href='https://saperstonestudios.com/user/profile.php'>reset your password</a>.</html></body>");
        } finally {
            $this->sql->executeStatement("DELETE FROM `users` WHERE `users`.`id` = $userId;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `users`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `users` AUTO_INCREMENT = $count;");
        }
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function testAllData() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));

            $response = $this->http->request('POST', 'api/create-user.php', [
                'form_params' => [
                    'username' => 'MaxMax',
                    'email' => 'msaperst+sstest@gmail.com',
                    'role' => 'downloader',
                    'firstName' => 'Max',
                    'lastName' => 'Saperstone',
                    'active' => 0,
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $userId = $response->getBody();
            $userDetails = $this->sql->getRow("SELECT * FROM `users` WHERE `users`.`id` = $userId;");
            $this->assertEquals($userId, $userDetails['id']);
            $this->assertEquals('MaxMax', $userDetails['usr']);
            $this->assertNotEquals('', $userDetails['pass']);
            $this->assertEquals('Max', $userDetails['firstName']);
            $this->assertEquals('Saperstone', $userDetails['lastName']);
            $this->assertEquals('msaperst+sstest@gmail.com', $userDetails['email']);
            $this->assertEquals('downloader', $userDetails['role']);
            $this->assertEquals(32, strlen($userDetails['hash']));
            $this->assertEquals(0, $userDetails['active']);
            CustomAsserts::timeWithin(10, $userDetails['created']);
            $this->assertNull($userDetails['lastLogin']);
            $this->assertNull($userDetails['resetKey']);
            CustomAsserts::assertEmailMatches('New User Created at Saperstone Studios', "Someone has setup a new user for you at Saperstone Studios. You can login and access the site at https://saperstonestudios.com. Initial credentials have been setup for you as: \r
    Username: MaxMax\r
    Password: %s\r
For security reasons, once logged in, we recommend you reset your password at https://saperstonestudios.com/user/profile.php",
                "<html><body>Someone has setup a new user for you at Saperstone Studios. You can login and access the site at <a href='https://saperstonestudios.com'>saperstonestudios.com</a>. Initial credentials have been setup for you as: <p>&nbsp;&nbsp;&nbsp;&nbsp;Username: MaxMax<br/>&nbsp;&nbsp;&nbsp;&nbsp;Password: %s</p>For security reasons, once logged in, we recommend you <a href='https://saperstonestudios.com/user/profile.php'>reset your password</a>.</html></body>");
        } finally {
            $this->sql->executeStatement("DELETE FROM `users` WHERE `users`.`id` = $userId;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `users`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `users` AUTO_INCREMENT = $count;");
        }
    }
}