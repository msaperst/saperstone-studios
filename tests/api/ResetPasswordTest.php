<?php

namespace api;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class ResetPasswordTest extends TestCase {
    /**
     * @var Client
     */
    private $http;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
    }

    public function tearDown() {
        $this->http = NULL;
    }

    /**
     * @throws GuzzleException
     */
    public function testNoEmail() {
        $response = $this->http->request('POST', 'api/reset-password.php');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankEmail() {
        $response = $this->http->request('POST', 'api/reset-password.php', [
            'form_params' => [
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
        $response = $this->http->request('POST', 'api/reset-password.php', [
            'form_params' => [
                'email' => 'max@max'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email is not valid", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testNoCode() {
        $response = $this->http->request('POST', 'api/reset-password.php', [
            'form_params' => [
                'email' => 'saperstonestudios@mailinator.com'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Code is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankCode() {
        $response = $this->http->request('POST', 'api/reset-password.php', [
            'form_params' => [
                'email' => 'saperstonestudios@mailinator.com',
                'code' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Code can not be blank", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testNoPassword() {
        $response = $this->http->request('POST', 'api/reset-password.php', [
            'form_params' => [
                'email' => 'saperstonestudios@mailinator.com',
                'code' => '12345'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Password is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankPassword() {
        $response = $this->http->request('POST', 'api/reset-password.php', [
            'form_params' => [
                'email' => 'saperstonestudios@mailinator.com',
                'code' => '12345',
                'password' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Password can not be blank", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testNoConfirmPassword() {
        $response = $this->http->request('POST', 'api/reset-password.php', [
            'form_params' => [
                'email' => 'saperstonestudios@mailinator.com',
                'code' => '12345',
                'password' => 'password'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Password confirmation is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankConfirmPassword() {
        $response = $this->http->request('POST', 'api/reset-password.php', [
            'form_params' => [
                'email' => 'saperstonestudios@mailinator.com',
                'code' => '12345',
                'password' => 'password',
                'passwordConfirm' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Password confirmation can not be blank", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testPasswordsDontMatch() {
        $response = $this->http->request('POST', 'api/reset-password.php', [
            'form_params' => [
                'email' => 'saperstonestudios@mailinator.com',
                'code' => '12345',
                'password' => 'password',
                'passwordConfirm' => 'p@ssW0rd'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Password and confirmation do not match", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testNoLookup() {
        $response = $this->http->request('POST', 'api/reset-password.php', [
            'form_params' => [
                'email' => 'saperstonestudios@mailinator.com',
                'code' => '12345',
                'password' => 'password',
                'passwordConfirm' => 'password'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Credentials do not match our records", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function testSuccessfulReset() {
        $sql = new Sql();
        try {
            $sql->executeStatement("UPDATE users SET resetKey = '12345' WHERE id = 4");
            $response = $this->http->request('POST', 'api/reset-password.php', [
                'form_params' => [
                    'email' => 'uploader@example.org',
                    'code' => '12345',
                    'password' => 'password1',
                    'passwordConfirm' => 'password1'
                ]
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals("", (string)$response->getBody());
            $userInfo = $sql->getRow("SELECT * FROM users WHERE id = 4");
            $this->assertEquals(md5('password1'), $userInfo['pass']);
            $this->assertNull($userInfo['resetKey']);
            $this->assertEquals('Reset Password', $sql->getRow("SELECT * FROM `user_logs` WHERE `user` = 4 ORDER BY time DESC LIMIT 1;")['action']);
        } finally {
            $sql->executeStatement("UPDATE users SET pass = '5f4dcc3b5aa765d61d8327deb882cf99' WHERE id = 4");
        }
    }
}
