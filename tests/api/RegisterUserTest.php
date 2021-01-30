<?php

namespace api;

use CustomAsserts;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class RegisterUserTest extends TestCase {
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

    /**
     * @throws GuzzleException
     */
    public function testNoUsername() {
        $response = $this->http->request('POST', 'api/register-user.php');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Username is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankUsername() {
        $response = $this->http->request('POST', 'api/register-user.php', [
            'form_params' => [
                'username' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Username can not be blank", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testDuplicateUsername() {
        $response = $this->http->request('POST', 'api/register-user.php', [
            'form_params' => [
                'username' => 'msaperst'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("That username already exists in the system", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testNoEmail() {
        $response = $this->http->request('POST', 'api/register-user.php', [
            'form_params' => [
                'username' => 'MaxMax'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankEmail() {
        $response = $this->http->request('POST', 'api/register-user.php', [
            'form_params' => [
                'username' => 'MaxMax',
                'email' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email can not be blank", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBadEmail() {
        $response = $this->http->request('POST', 'api/register-user.php', [
            'form_params' => [
                'username' => 'MaxMax',
                'email' => 'max@max'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email is not valid", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testDuplicateEmail() {
        $response = $this->http->request('POST', 'api/register-user.php', [
            'form_params' => [
                'username' => 'MaxMax',
                'email' => 'msaperst@gmail.com'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("That email already exists in the system: try logging in with it", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testNoPassword() {
        $response = $this->http->request('POST', 'api/register-user.php', [
            'form_params' => [
                'username' => 'MaxMax',
                'email' => 'msaperst+sstest@gmail.com'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Password is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankPassword() {
        $response = $this->http->request('POST', 'api/register-user.php', [
            'form_params' => [
                'username' => 'MaxMax',
                'email' => 'msaperst+sstest@gmail.com',
                'password' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Password can not be blank", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function testNoExtras() {
        try {
            $response = $this->http->request('POST', 'api/register-user.php', [
                'form_params' => [
                    'username' => 'MaxMax',
                    'email' => 'msaperst+sstest@gmail.com',
                    'password' => '12345'
                ]
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $userId = $response->getBody();
            $userDetails = $this->sql->getRow("SELECT * FROM `users` WHERE `users`.`id` = $userId;");
            $this->assertEquals($userId, $userDetails['id']);
            $this->assertEquals('MaxMax', $userDetails['usr']);
            $this->assertEquals('827ccb0eea8a706c4c34a16891f84e7b', $userDetails['pass']);
            $this->assertEquals('', $userDetails['firstName']);
            $this->assertEquals('', $userDetails['lastName']);
            $this->assertEquals('msaperst+sstest@gmail.com', $userDetails['email']);
            $this->assertEquals('downloader', $userDetails['role']);
            $this->assertEquals('cf0339a5bc2feeee0aef1c553834276a', $userDetails['hash']);
            $this->assertEquals(1, $userDetails['active']);
            CustomAsserts::timeWithin(5, $userDetails['created']);
            CustomAsserts::timeWithin(5, $userDetails['lastLogin']);
            $this->assertNull($userDetails['resetKey']);
            $log = $this->sql->getRows("SELECT * FROM `user_logs` WHERE `user` = $userId ORDER BY time DESC LIMIT 2;");
            $this->assertEquals('Logged In', $log[0]['action']);
            $this->assertEquals('Registered', $log[1]['action']);
            CustomAsserts::assertEmailEquals('Thank you for Registering with Saperstone Studios',
                'Congratulations for registering an account with Saperstone Studios. You can login and access the site at https://saperstonestudios.com.',
                "<html><body>Congratulations for registering an account with Saperstone Studios. You can login and access the site at <a href='https://saperstonestudios.com'>saperstonestudios.com</a>.</body></html>");
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
            $response = $this->http->request('POST', 'api/register-user.php', [
                'form_params' => [
                    'username' => 'MaxMax',
                    'email' => 'msaperst+sstest@gmail.com',
                    'password' => 'password',
                    'firstName' => 'Max',
                    'lastName' => 'Saperstone',
                ]
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $userId = (string)$response->getBody();
            $userDetails = $this->sql->getRow("SELECT * FROM `users` WHERE `users`.`id` = $userId;");
            $this->assertEquals($userId, $userDetails['id']);
            $this->assertEquals('MaxMax', $userDetails['usr']);
            $this->assertEquals('5f4dcc3b5aa765d61d8327deb882cf99', $userDetails['pass']);
            $this->assertEquals('Max', $userDetails['firstName']);
            $this->assertEquals('Saperstone', $userDetails['lastName']);
            $this->assertEquals('msaperst+sstest@gmail.com', $userDetails['email']);
            $this->assertEquals('downloader', $userDetails['role']);
            $this->assertEquals('cd1b3237a8d22938f69578a8bef680c5', $userDetails['hash']);
            $this->assertEquals(1, $userDetails['active']);
            CustomAsserts::timeWithin(5, $userDetails['created']);
            CustomAsserts::timeWithin(5, $userDetails['lastLogin']);
            $this->assertNull($userDetails['resetKey']);
            $log = $this->sql->getRows("SELECT * FROM `user_logs` WHERE `user` = $userId ORDER BY time DESC LIMIT 2;");
            $this->assertEquals('Logged In', $log[0]['action']);
            $this->assertEquals('Registered', $log[1]['action']);
            CustomAsserts::assertEmailEquals('Thank you for Registering with Saperstone Studios',
                'Congratulations for registering an account with Saperstone Studios. You can login and access the site at https://saperstonestudios.com.',
                "<html><body>Congratulations for registering an account with Saperstone Studios. You can login and access the site at <a href='https://saperstonestudios.com'>saperstonestudios.com</a>.</body></html>");
        } finally {
            $this->sql->executeStatement("DELETE FROM `users` WHERE `users`.`id` = $userId;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `users`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `users` AUTO_INCREMENT = $count;");
        }
    }
}